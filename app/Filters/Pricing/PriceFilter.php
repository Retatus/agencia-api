<?php

namespace App\Filters\Pricing;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class PriceFilter extends BaseFilter
{
    public function apply(
        Builder $query
    ): Builder {
        $this->query = $query;

        /*
        |--------------------------------------------------------------------------
        | Direct filters
        |--------------------------------------------------------------------------
        */

        $this->serviceVariant();

        $this->priceType();

        $this->passengerType();

        $this->currency();

        $this->active();

        /*
        |--------------------------------------------------------------------------
        | Catalog relations
        |--------------------------------------------------------------------------
        */

        $this->service();

        $this->provider();

        $this->serviceCategory();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $this->search();

        /*
        |--------------------------------------------------------------------------
        | Ranges
        |--------------------------------------------------------------------------
        */

        $this->costRange();

        $this->salePriceRange();

        $this->quantityRange();

        $this->validity();

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $this->sorting();

        return $this->query;
    }

    /*
    |--------------------------------------------------------------------------
    | SERVICE VARIANT
    |--------------------------------------------------------------------------
    */

    protected function serviceVariant(): void
    {
        if (
            ! $this->request->filled(
                'service_variant_id'
            )
        ) {
            return;
        }

        $this->query->where(
            'service_variant_id',
            $this->request->integer(
                'service_variant_id'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PRICE TYPE
    |--------------------------------------------------------------------------
    */

    protected function priceType(): void
    {
        if (
            ! $this->request->filled(
                'price_type_id'
            )
        ) {
            return;
        }

        $this->query->where(
            'price_type_id',
            $this->request->integer(
                'price_type_id'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PASSENGER TYPE
    |--------------------------------------------------------------------------
    */

    protected function passengerType(): void
    {
        if (
            ! $this->request->filled(
                'passenger_type_id'
            )
        ) {
            return;
        }

        $this->query->where(
            'passenger_type_id',
            $this->request->integer(
                'passenger_type_id'
            )
        );
    }

    protected function currency(): void
    {
        if (! $this->request->filled('currency_id')) {
            return;
        }

        $this->query->where(
            'currency_id',
            $this->request->integer('currency_id')
        );
    }

    protected function validity(): void
    {
        if (! $this->request->filled('date')) {
            return;
        }

        $date = $this->request->date('date')->toDateString();

        $this->query
            ->where(fn (Builder $query) => $query
                ->whereNull('valid_from')
                ->orWhereDate('valid_from', '<=', $date))
            ->where(fn (Builder $query) => $query
                ->whereNull('valid_to')
                ->orWhereDate('valid_to', '>=', $date));
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVE
    |--------------------------------------------------------------------------
    */

    protected function active(): void
    {
        if (
            ! $this->request->has(
                'active'
            )
        ) {
            return;
        }

        $this->query->where(
            'active',
            $this->request->boolean(
                'active'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SERVICE
    |--------------------------------------------------------------------------
    |
    | El frontend trabaja Service mediante UUID.
    |
    | Price:
    |
    | price
    |   ↓
    | serviceVariant
    |   ↓
    | service
    |
    */

    protected function service(): void
    {
        if (
            ! $this->request->filled(
                'service_uuid'
            )
        ) {
            return;
        }

        $uuid =
            $this->request->input(
                'service_uuid'
            );

        $this->query->whereHas(
            'serviceVariant.service',
            function (
                Builder $query
            ) use ($uuid) {
                $query->where(
                    'uuid',
                    $uuid
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PROVIDER
    |--------------------------------------------------------------------------
    */

    protected function provider(): void
    {
        if (
            ! $this->request->filled(
                'provider_id'
            )
        ) {
            return;
        }

        $providerId =
            $this->request->integer(
                'provider_id'
            );

        $this->query->whereHas(
            'serviceVariant.service',
            function (
                Builder $query
            ) use ($providerId) {
                $query->where(
                    'provider_id',
                    $providerId
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SERVICE CATEGORY
    |--------------------------------------------------------------------------
    */

    protected function serviceCategory(): void
    {
        if (
            ! $this->request->filled(
                'service_category_id'
            )
        ) {
            return;
        }

        $categoryId =
            $this->request->integer(
                'service_category_id'
            );

        $this->query->whereHas(
            'serviceVariant.service',
            function (
                Builder $query
            ) use ($categoryId) {
                $query->where(
                    'service_category_id',
                    $categoryId
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    protected function search(): void
    {
        if (
            ! $this->request->filled(
                'search'
            )
        ) {
            return;
        }

        $search =
            trim(
                $this->request->input(
                    'search'
                )
            );

        $this->query->where(
            function (
                Builder $query
            ) use ($search) {

                /*
                |--------------------------------------------------------------------------
                | Variant
                |--------------------------------------------------------------------------
                */

                $query->whereHas(
                    'serviceVariant',
                    function (
                        Builder $variant
                    ) use ($search) {
                        $variant
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'code',
                                'like',
                                "%{$search}%"
                            );
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Service
                |--------------------------------------------------------------------------
                */

                $query->orWhereHas(
                    'serviceVariant.service',
                    function (
                        Builder $service
                    ) use ($search) {
                        $service
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'code',
                                'like',
                                "%{$search}%"
                            );
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Provider
                |--------------------------------------------------------------------------
                */

                $query->orWhereHas(
                    'serviceVariant.service.provider',
                    function (
                        Builder $provider
                    ) use ($search) {
                        $provider->where(
                            'business_name',
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
    | COST RANGE
    |--------------------------------------------------------------------------
    */

    protected function costRange(): void
    {
        if (
            $this->request->filled(
                'cost_from'
            )
        ) {
            $this->query->where(
                'cost',
                '>=',
                $this->request->input(
                    'cost_from'
                )
            );
        }

        if (
            $this->request->filled(
                'cost_to'
            )
        ) {
            $this->query->where(
                'cost',
                '<=',
                $this->request->input(
                    'cost_to'
                )
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SALE RANGE
    |--------------------------------------------------------------------------
    */

    protected function salePriceRange(): void
    {
        if (
            $this->request->filled(
                'sale_from'
            )
        ) {
            $this->query->where(
                'sale_price',
                '>=',
                $this->request->input(
                    'sale_from'
                )
            );
        }

        if (
            $this->request->filled(
                'sale_to'
            )
        ) {
            $this->query->where(
                'sale_price',
                '<=',
                $this->request->input(
                    'sale_to'
                )
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | QUANTITY RANGE
    |--------------------------------------------------------------------------
    */

    protected function quantityRange(): void
    {
        if (
            $this->request->filled(
                'min_quantity'
            )
        ) {
            $this->query->where(
                'min_quantity',
                '>=',
                $this->request->integer(
                    'min_quantity'
                )
            );
        }

        if (
            $this->request->filled(
                'max_quantity'
            )
        ) {
            $this->query->where(
                'max_quantity',
                '<=',
                $this->request->integer(
                    'max_quantity'
                )
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SORTING
    |--------------------------------------------------------------------------
    */

    protected function sorting(): void
    {
        $allowed = [
            'cost',
            'sale_price',
            'min_quantity',
            'max_quantity',
            'created_at',
        ];

        $sort =
            $this->request->input(
                'sort',
                'created_at'
            );

        if (
            ! in_array(
                $sort,
                $allowed,
                true
            )
        ) {
            $sort = 'created_at';
        }

        $direction =
            strtolower(
                $this->request->input(
                    'direction',
                    'desc'
                )
            );

        if (
            ! in_array(
                $direction,
                [
                    'asc',
                    'desc',
                ],
                true
            )
        ) {
            $direction = 'desc';
        }

        $this->query->orderBy(
            $sort,
            $direction
        );
    }
}
