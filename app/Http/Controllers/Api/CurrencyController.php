<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Currency\StoreCurrencyRequest;
use App\Http\Requests\Currency\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'active'   => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        
        $query = Currency::query();
        
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return CurrencyResource::collection(
            $query->orderBy('code')
                ->paginate($request->integer('per_page', 20))
        );
    }

    public function store(StoreCurrencyRequest $request): CurrencyResource
    {
        $currency = Currency::create($request->validated());

        return new CurrencyResource($currency);
    }

    public function show(Currency $currency): CurrencyResource
    {
        return new CurrencyResource($currency);
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency): CurrencyResource 
    {

        $currency->update($request->validated());

        return new CurrencyResource($currency);
    }

    public function destroy(Currency $currency): JsonResponse
    {
        $currency->delete();

        return response()->json([
            'message' => 'Currency deleted successfully.'
        ]);
    }
}