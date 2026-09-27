<?php

namespace App\Tourism\Destination\Controllers;

use App\Http\Controllers\Controller;
use App\Tourism\Destination\Models\TouristDestination;
use App\Tourism\Destination\Requests\SaveTouristDestinationRequest;
use App\Tourism\Destination\Requests\ConvertTouristDestinationRequest;
use App\Tourism\Destination\Resources\TouristDestinationResource;
use App\Pricing\ExchangeRate\Services\CurrencyConverter;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TouristDestinationController extends Controller
{
    public function convert(
        ConvertTouristDestinationRequest $request,
        TouristDestination $touristDestination,
        CurrencyConverter $converter,
    ): JsonResponse {
        $destination = $this->loadTemplate($touristDestination);
        $targetCurrencyId = $request->integer('target_currency_id');
        $date = $request->filled('exchange_rate_date')
            ? CarbonImmutable::parse($request->string('exchange_rate_date')->toString())
            : CarbonImmutable::today();
        $data = (new TouristDestinationResource($destination))->resolve($request);
        $exchangeRate = $converter->convert(
            '0',
            (int) $destination->currency_id,
            $targetCurrencyId,
            $date,
        )['exchange_rate'];

        $data['target_currency_id'] = $targetCurrencyId;
        $data['days'] = collect($data['days'])->map(function (array $day) use (
            $converter,
            $destination,
            $exchangeRate,
        ): array {
            $day['items'] = collect($day['items'])->map(function (array $item) use (
                $converter,
                $destination,
                $exchangeRate,
            ): array {
                return array_merge($item, [
                    'source_currency_id' => (int) $destination->currency_id,
                    'source_unit_cost' => $item['estimated_cost'],
                    'source_unit_price' => $item['estimated_price'],
                    'estimated_cost' => $converter->apply($item['estimated_cost'], $exchangeRate),
                    'estimated_price' => $converter->apply($item['estimated_price'], $exchangeRate),
                    'exchange_rate' => $exchangeRate->rate,
                    'exchange_rate_date' => $exchangeRate->effectiveDate->toDateString(),
                ]);
            })->values()->all();

            return $day;
        })->values()->all();

        return response()->json(['data' => $data]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'active' => ['nullable', 'boolean'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = TouristDestination::query()->with('currency');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(fn ($builder) => $builder
                ->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->integer('currency_id'));
        }

        return TouristDestinationResource::collection(
            $query->orderBy('name')->paginate($request->integer('per_page', 15))
        );
    }

    public function store(SaveTouristDestinationRequest $request): JsonResponse
    {
        $destination = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $destination = TouristDestination::create(collect($data)->except('days')->all());
            $this->syncDays($destination, $data['days']);

            return $destination;
        });

        return (new TouristDestinationResource($this->loadTemplate($destination)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(TouristDestination $touristDestination): TouristDestinationResource
    {
        return new TouristDestinationResource($this->loadTemplate($touristDestination));
    }

    public function update(
        SaveTouristDestinationRequest $request,
        TouristDestination $touristDestination
    ): TouristDestinationResource {
        DB::transaction(function () use ($request, $touristDestination) {
            $data = $request->validated();
            $touristDestination->update(collect($data)->except('days')->all());
            $this->syncDays($touristDestination, $data['days']);
        });

        return new TouristDestinationResource($this->loadTemplate($touristDestination));
    }

    public function destroy(TouristDestination $touristDestination)
    {
        $touristDestination->delete();

        return response()->json(['message' => 'Destino turístico eliminado correctamente.']);
    }

    private function syncDays(TouristDestination $destination, array $days): void
    {
        $destination->days()->delete();

        foreach ($days as $dayData) {
            $day = $destination->days()->create(collect($dayData)->except('items')->all());
            $day->items()->createMany($dayData['items']);
        }
    }

    private function loadTemplate(TouristDestination $destination): TouristDestination
    {
        return $destination->fresh([
            'currency',
            'days.items',
        ]);
    }
}
