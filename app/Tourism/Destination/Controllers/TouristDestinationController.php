<?php

namespace App\Tourism\Destination\Controllers;

use App\Http\Controllers\Controller;
use App\Tourism\Destination\Models\TouristDestination;
use App\Tourism\Destination\Requests\SaveTouristDestinationRequest;
use App\Tourism\Destination\Resources\TouristDestinationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TouristDestinationController extends Controller
{
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
