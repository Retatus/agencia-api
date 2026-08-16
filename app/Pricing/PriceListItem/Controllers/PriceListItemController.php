<?php

namespace App\Pricing\PriceListItem\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceVariant;
use App\Pricing\PriceList\Models\PriceList;
use App\Pricing\PriceListItem\Models\PriceListItem;
use App\Pricing\PriceListItem\Requests\StorePriceListItemRequest;
use App\Pricing\PriceListItem\Requests\UpdatePriceListItemRequest;
use App\Pricing\PriceListItem\Resources\PriceListItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PriceListItemController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(
        PriceList $priceList
    ): AnonymousResourceCollection {

        $items = $priceList
            ->items()
            ->with([
                'serviceVariant',
                'priceList',
            ])
            ->orderBy('service_variant_id')
            ->get();

        return PriceListItemResource::collection(
            $items
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        StorePriceListItemRequest $request,
        PriceList $priceList
    ): PriceListItemResource|JsonResponse {
        $data = $request->validated();

        $variant = ServiceVariant::query()
            ->with([
                'service',
                'basePrice',
            ])
            ->findOrFail(
                $data['service_variant_id']
            );

        /*
        |--------------------------------------------------------------------------
        | Validar categoría
        |--------------------------------------------------------------------------
        */

        if (
            (int) $variant->service->service_category_id !==
            (int) $priceList->service_category_id
        ) {
            return response()->json([
                'message' => 'La variante no pertenece a la categoría de esta lista de precios.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar BasePrice
        |--------------------------------------------------------------------------
        */

        if (! $variant->basePrice) {
            return response()->json([
                'message' => 'La variante debe tener un precio base antes de agregar un ajuste.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Evitar duplicado
        |--------------------------------------------------------------------------
        */

        $exists = $priceList
            ->priceListItems()
            ->where(
                'service_variant_id',
                $variant->id
            )
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Esta variante ya tiene una regla dentro de la lista de precios.',
            ], 422);
        }

        $item = $priceList
            ->priceListItems()
            ->create($data);

        $item->load([
            'serviceVariant',
            'priceList',
        ]);

        return new PriceListItemResource(
            $item
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdatePriceListItemRequest $request,
        PriceList $priceList,
        PriceListItem $item
    ): PriceListItemResource|JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Validar pertenencia
        |--------------------------------------------------------------------------
        */

        if (
            (int) $item->price_list_id !==
            (int) $priceList->id
        ) {
            return response()->json([
                'message' => 'El item no pertenece a esta lista de precios.',
            ], 404);
        }

        $data = $request->validated();

        $variant = ServiceVariant::query()
            ->with([
                'service',
                'basePrice',
            ])
            ->findOrFail(
                $data['service_variant_id']
            );

        /*
        |--------------------------------------------------------------------------
        | Categoría
        |--------------------------------------------------------------------------
        */

        if (
            (int) $variant->service->service_category_id !==
            (int) $priceList->service_category_id
        ) {
            return response()->json([
                'message' => 'La variante no pertenece a la categoría de esta lista de precios.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | BasePrice
        |--------------------------------------------------------------------------
        */

        if (! $variant->basePrice) {
            return response()->json([
                'message' => 'La variante debe tener un precio base.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicado
        |--------------------------------------------------------------------------
        */

        $exists = $priceList
            ->priceListItems()
            ->where(
                'service_variant_id',
                $variant->id
            )
            ->where(
                'id',
                '<>',
                $item->id
            )
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Esta variante ya tiene otra regla en la lista.',
            ], 422);
        }

        $item->update($data);

        $item->load([
            'serviceVariant',
            'priceList',
        ]);

        return new PriceListItemResource(
            $item
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        PriceList $priceList,
        PriceListItem $item
    ): JsonResponse {
        if (
            (int) $item->price_list_id !==
            (int) $priceList->id
        ) {
            return response()->json([
                'message' => 'El item no pertenece a esta lista de precios.',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Regla de precio eliminada correctamente.',
        ]);
    }
}