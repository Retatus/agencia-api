<?php

namespace App\Http\Controllers\Pricing;

use App\Filters\Pricing\PriceFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pricing\Price\StorePriceRequest;
use App\Http\Requests\Pricing\Price\UpdatePriceRequest;
use App\Http\Resources\Pricing\PriceResource;
use App\Models\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceController extends Controller
{
    /**
     * Relaciones que siempre cargaremos.
     */
    private array $relations = [
        'priceList',
        'serviceVariant.service',
        'priceType',
        'passengerType',
    ];

    /**
     * Listado de precios.
     */
    public function index(
        Request $request,
        PriceFilter $filter
    )
    {
        $prices = $filter
            ->apply(
                Price::query()->with($this->relations)
            )
            ->paginate(
                $request->integer('per_page', 20)
            );

        return PriceResource::collection($prices);
    }

    /**
     * Crear precio.
     */
    public function store(
        StorePriceRequest $request
    ): PriceResource {

        $price = Price::create(
            $request->validated()
        );

        $price->load($this->relations);

        return new PriceResource($price);
    }

    /**
     * Mostrar un precio.
     */
    public function show(
        Price $price
    ): PriceResource {

        $price->load($this->relations);

        return new PriceResource($price);
    }

    /**
     * Actualizar un precio.
     */
    public function update(
        UpdatePriceRequest $request,
        Price $price
    ): PriceResource {

        $price->update(
            $request->validated()
        );

        $price->load($this->relations);

        return new PriceResource($price);
    }

    /**
     * Eliminar un precio.
     */
    public function destroy(
        Price $price
    ): JsonResponse {

        $price->delete();

        return response()->json([
            'success' => true,
            'message' => 'Precio eliminado correctamente.'
        ], Response::HTTP_OK);
    }
}