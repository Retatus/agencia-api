<?php

namespace App\Pricing\Price\Controllers;

use App\Filters\Pricing\PriceFilter;
use App\Http\Controllers\Controller;
use App\Pricing\Price\Actions\BulkUpdatePricesAction;
use App\Pricing\Price\Actions\CreatePriceAction;
use App\Pricing\Price\Actions\UpdatePriceAction;
use App\Pricing\Price\Requests\BulkUpdatePricesRequest;
use App\Pricing\Price\Requests\StorePriceRequest;
use App\Pricing\Price\Requests\UpdatePriceRequest;
use App\Pricing\Price\Resources\PriceResource;
use App\Pricing\Price\Models\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceController extends Controller
{
    /**
     * Relaciones que siempre cargaremos.
     */
    private array $relations = [
        'serviceVariant.service',
        'priceType',
        'passengerType',
        'currency',
    ];

    /**
     * Listado de precios.
     */
    public function index(Request $request, PriceFilter $filter)
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
        StorePriceRequest $request,
        CreatePriceAction $action
    ): PriceResource
    {
        $price = $action->execute(
            $request->validated()
        );

        $price->load($this->relations);

        return new PriceResource($price);
    }

    /**
     * Mostrar un precio.
     */
    public function show(Price $price): PriceResource 
    {
        $price->load($this->relations);

        return new PriceResource($price);
    }

    /**
     * Actualizar un precio.
     */
    public function update(
        UpdatePriceRequest $request,
        Price $price,
        UpdatePriceAction $action
    ): PriceResource
    {
        $price = $action->execute(
            $price,
            $request->validated()
        );

        $price->load($this->relations);

        return new PriceResource($price);
    }

    /**
     * Eliminar un precio.
     */
    public function destroy(Price $price): JsonResponse 
    {
        $price->delete();

        return response()->json([
            'success' => true,
            'message' => 'Precio eliminado correctamente.'
        ], Response::HTTP_OK);
    }

    public function bulkUpdate(BulkUpdatePricesRequest $request, BulkUpdatePricesAction $action)
    {
        $prices = $action->execute(
            $request->validated()['prices']
        );

        if (is_array($prices)) {
            $prices = new \Illuminate\Database\Eloquent\Collection($prices);
        }

        $prices->load($this->relations);

        return PriceResource::collection($prices);
    }
}
