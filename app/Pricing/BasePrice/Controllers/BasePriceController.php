<?php

namespace App\Pricing\BasePrice\Controllers;

use App\Http\Controllers\Controller;
use App\Pricing\BasePrice\Models\BasePrice;
use App\Pricing\BasePrice\Requests\StoreBasePriceRequest;
use App\Pricing\BasePrice\Requests\UpdateBasePriceRequest;
use App\Pricing\BasePrice\Resources\BasePriceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BasePriceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    protected array $relations = [
        'serviceVariant.service',
        'currency',
    ];

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): AnonymousResourceCollection {
        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'service_variant_id' => [
                'nullable',
                'integer',
                'exists:service_variants,id',
            ],

            'currency_id' => [
                'nullable',
                'integer',
                'exists:currencies,id',
            ],

            'active' => [
                'nullable',
                'boolean',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $query = BasePrice::query()
            ->with(
                $this->relations
            );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        |
        | Busca por servicio o variante.
        |
        */

        if ($request->filled('search')) {
            $search =
                $request->input(
                    'search'
                );

            $query->where(
                function ($query) use ($search) {
                    $query
                        ->whereHas(
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
                                    );
                            }
                        )
                        ->orWhereHas(
                            'serviceVariant.service',
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
        | Service Variant
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'service_variant_id'
            )
        ) {
            $query->where(
                'service_variant_id',
                $request->integer(
                    'service_variant_id'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'currency_id'
            )
        ) {
            $query->where(
                'currency_id',
                $request->integer(
                    'currency_id'
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
                $request->boolean(
                    'active'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $basePrices =
            $query
                ->orderByDesc('id')
                ->paginate(
                    $request->integer(
                        'per_page',
                        20
                    )
                );

        return BasePriceResource::collection(
            $basePrices
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreBasePriceRequest $request
    ): BasePriceResource {
        $basePrice =
            BasePrice::create(
                $request->validated()
            );

        $basePrice->load(
            $this->relations
        );

        return new BasePriceResource(
            $basePrice
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        BasePrice $basePrice
    ): BasePriceResource {
        $basePrice->load(
            $this->relations
        );

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
        BasePrice $basePrice
    ): BasePriceResource {
        $basePrice->update(
            $request->validated()
        );

        $basePrice->load(
            $this->relations
        );

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
        BasePrice $basePrice
    ): Response {
        $basePrice->delete();

        return response()
            ->noContent();
    }
}