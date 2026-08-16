<?php

namespace App\Pricing\BasePrice\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceVariant;
use App\Pricing\BasePrice\Models\BasePrice;
use App\Pricing\BasePrice\Requests\StoreBasePriceRequest;
use App\Pricing\BasePrice\Requests\UpdateBasePriceRequest;
use App\Pricing\BasePrice\Resources\BasePriceResource;
use Illuminate\Http\JsonResponse;

class BasePriceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        ServiceVariant $variant
    ): BasePriceResource|JsonResponse {
        $basePrice = $variant
            ->basePrice()
            ->with([
                'currency',
                'serviceVariant',
            ])
            ->first();

        if (! $basePrice) {
            return response()->json([
                'message' => 'La variante no tiene precio base registrado.',
                'data' => null,
            ], 404);
        }

        return new BasePriceResource(
            $basePrice
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreBasePriceRequest $request,
        ServiceVariant $variant
    ): BasePriceResource|JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Una variante solo puede tener un BasePrice
        |--------------------------------------------------------------------------
        */

        if ($variant->basePrice()->exists()) {
            return response()->json([
                'message' => 'La variante ya tiene un precio base registrado.',
            ], 422);
        }

        $basePrice = $variant
            ->basePrice()
            ->create(
                $request->validated()
            );

        $basePrice->load([
            'currency',
            'serviceVariant',
        ]);

        return new BasePriceResource(
            $basePrice
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateBasePriceRequest $request,
        ServiceVariant $variant
    ): BasePriceResource|JsonResponse {
        $basePrice = $variant
            ->basePrice()
            ->first();

        if (! $basePrice) {
            return response()->json([
                'message' => 'La variante no tiene precio base registrado.',
            ], 404);
        }

        $basePrice->update(
            $request->validated()
        );

        $basePrice->load([
            'currency',
            'serviceVariant',
        ]);

        return new BasePriceResource(
            $basePrice
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ServiceVariant $variant
    ): JsonResponse {
        $basePrice = $variant
            ->basePrice()
            ->first();

        if (! $basePrice) {
            return response()->json([
                'message' => 'La variante no tiene precio base registrado.',
            ], 404);
        }

        $basePrice->delete();

        return response()->json([
            'message' => 'Precio base eliminado correctamente.',
        ]);
    }
}