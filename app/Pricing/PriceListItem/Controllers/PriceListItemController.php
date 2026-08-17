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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class PriceListItemController extends Controller
{
    protected array $relations = [
        'priceList',
        'serviceVariant.service',
        'serviceVariant.basePrice.currency',
    ];

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        PriceList $priceList
    ): AnonymousResourceCollection {

        $request->validate([
            'search' =>
                'nullable|string|max:100',

            'active' =>
                'nullable|boolean',

            'adjustment_type' => [
                'nullable',
                'string',
                'in:PERCENT,FIXED_AMOUNT,FIXED_PRICE',
            ],

            'per_page' =>
                'nullable|integer|min:1|max:100',
        ]);

        $query = $priceList
            ->priceListItems()
            ->with(
                $this->relations
            );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search =
                $request->input('search');

            $query->whereHas(
                'serviceVariant',
                function ($variantQuery) use ($search) {

                    $variantQuery
                        ->where(
                            'code',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'service',
                            function ($serviceQuery) use ($search) {

                                $serviceQuery
                                    ->where(
                                        'code',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Adjustment Type
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'adjustment_type'
            )
        ) {
            $query->where(
                'adjustment_type',
                $request->input(
                    'adjustment_type'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Active
        |--------------------------------------------------------------------------
        */

        if ($request->has('active')) {
            $query->where(
                'active',
                $request->boolean('active')
            );
        }

        $items = $query
            ->orderBy('service_variant_id')
            ->paginate(
                $request->integer(
                    'per_page',
                    20
                )
            );

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
    ): PriceListItemResource {

        $data =
            $request->validated();

        $variant =
            ServiceVariant::query()
                ->with([
                    'service',
                    'basePrice.currency',
                ])
                ->findOrFail(
                    $data[
                        'service_variant_id'
                    ]
                );

        /*
        |--------------------------------------------------------------------------
        | BasePrice obligatorio
        |--------------------------------------------------------------------------
        */

        if (! $variant->basePrice) {
            throw ValidationException::withMessages([
                'service_variant_id' =>
                    'La variante debe tener un precio base antes de crear una regla comercial.',
            ]);
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
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'service_variant_id' =>
                    'Esta variante ya tiene una regla dentro de esta lista de precios.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        |
        | Aquí Laravel coloca automáticamente:
        |
        | price_list_id = $priceList->id
        |
        */

        $item = $priceList
            ->priceListItems()
            ->create([
                'service_variant_id' =>
                    $variant->id,

                'adjustment_type' =>
                    $data['adjustment_type'],

                'adjustment_value' =>
                    $data['adjustment_value'],

                'active' =>
                    $data['active'] ?? true,
            ]);

        $item->load(
            $this->relations
        );

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
    ): PriceListItemResource {

        /*
        |--------------------------------------------------------------------------
        | Pertenencia
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $item->price_list_id ===
            (int) $priceList->id,
            404
        );

        $data =
            $request->validated();

        $variant =
            ServiceVariant::query()
                ->with(
                    'basePrice.currency'
                )
                ->findOrFail(
                    $data[
                        'service_variant_id'
                    ]
                );

        /*
        |--------------------------------------------------------------------------
        | BasePrice
        |--------------------------------------------------------------------------
        */

        if (! $variant->basePrice) {
            throw ValidationException::withMessages([
                'service_variant_id' =>
                    'La variante debe tener un precio base.',
            ]);
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
            ->whereKeyNot(
                $item->id
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'service_variant_id' =>
                    'Esta variante ya tiene otra regla dentro de esta lista.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $item->update([
            'service_variant_id' =>
                $variant->id,

            'adjustment_type' =>
                $data['adjustment_type'],

            'adjustment_value' =>
                $data['adjustment_value'],

            'active' =>
                $data['active']
                ?? $item->active,
        ]);

        $item->load(
            $this->relations
        );

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

        abort_unless(
            (int) $item->price_list_id ===
            (int) $priceList->id,
            404
        );

        $item->delete();

        return response()->json([
            'message' =>
                'Regla de precio eliminada correctamente.',
        ]);
    }
}