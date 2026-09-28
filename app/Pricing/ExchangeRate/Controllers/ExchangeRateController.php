<?php

namespace App\Pricing\ExchangeRate\Controllers;

use App\Http\Controllers\Controller;
use App\Pricing\ExchangeRate\Models\ExchangeRate;
use App\Pricing\ExchangeRate\Requests\StoreExchangeRateRequest;
use App\Pricing\ExchangeRate\Requests\UpdateExchangeRateRequest;
use App\Pricing\ExchangeRate\Resources\ExchangeRateResource;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from_currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'to_currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $items = ExchangeRate::query()
            ->with(['fromCurrency', 'toCurrency'])
            ->when($request->filled('from_currency_id'), fn ($query) =>
                $query->where('from_currency_id', $request->integer('from_currency_id')))
            ->when($request->filled('to_currency_id'), fn ($query) =>
                $query->where('to_currency_id', $request->integer('to_currency_id')))
            ->when($request->has('active'), fn ($query) =>
                $query->where('active', $request->boolean('active')))
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 20));

        return ExchangeRateResource::collection($items);
    }

    public function store(StoreExchangeRateRequest $request): ExchangeRateResource
    {
        $item = ExchangeRate::create($request->validated());

        return new ExchangeRateResource($item->load(['fromCurrency', 'toCurrency']));
    }

    public function show(ExchangeRate $exchangeRate): ExchangeRateResource
    {
        return new ExchangeRateResource($exchangeRate->load(['fromCurrency', 'toCurrency']));
    }

    public function update(
        UpdateExchangeRateRequest $request,
        ExchangeRate $exchangeRate,
    ): ExchangeRateResource {
        $exchangeRate->update($request->validated());

        return new ExchangeRateResource($exchangeRate->load(['fromCurrency', 'toCurrency']));
    }

    public function destroy(ExchangeRate $exchangeRate)
    {
        $exchangeRate->delete();

        return response()->json(['message' => 'Tipo de cambio eliminado correctamente.']);
    }
}
