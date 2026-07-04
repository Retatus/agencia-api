<?php

namespace App\Filters\Pricing;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class PriceFilter extends BaseFilter
{
    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        $this->priceList();
        $this->serviceVariant();
        $this->priceType();
        $this->passengerType();
        $this->active();
        $this->costRange();
        $this->salePriceRange();
        $this->quantityRange();
        $this->sorting();

        return $this->query;
    }

    protected function priceList(): void
    {
        if ($this->request->filled('price_list_id')) {
            $this->query->where(
                'price_list_id',
                $this->request->integer('price_list_id')
            );
        }
    }

    protected function serviceVariant(): void
    {
        if ($this->request->filled('service_variant_id')) {
            $this->query->where(
                'service_variant_id',
                $this->request->integer('service_variant_id')
            );
        }
    }

    protected function priceType(): void
    {
        if ($this->request->filled('price_type_id')) {
            $this->query->where(
                'price_type_id',
                $this->request->integer('price_type_id')
            );
        }
    }

    protected function passengerType(): void
    {
        if ($this->request->filled('passenger_type_id')) {
            $this->query->where(
                'passenger_type_id',
                $this->request->integer('passenger_type_id')
            );
        }
    }

    protected function active(): void
    {
        if ($this->request->has('active')) {
            $this->query->where(
                'active',
                $this->request->boolean('active')
            );
        }
    }

    protected function costRange(): void
    {
        if ($this->request->filled('cost_from')) {
            $this->query->where(
                'cost',
                '>=',
                $this->request->input('cost_from')
            );
        }

        if ($this->request->filled('cost_to')) {
            $this->query->where(
                'cost',
                '<=',
                $this->request->input('cost_to')
            );
        }
    }

    protected function salePriceRange(): void
    {
        if ($this->request->filled('sale_from')) {
            $this->query->where(
                'sale_price',
                '>=',
                $this->request->input('sale_from')
            );
        }

        if ($this->request->filled('sale_to')) {
            $this->query->where(
                'sale_price',
                '<=',
                $this->request->input('sale_to')
            );
        }
    }

    protected function quantityRange(): void
    {
        if ($this->request->filled('min_quantity')) {
            $this->query->where(
                'min_quantity',
                '>=',
                $this->request->integer('min_quantity')
            );
        }

        if ($this->request->filled('max_quantity')) {
            $this->query->where(
                'max_quantity',
                '<=',
                $this->request->integer('max_quantity')
            );
        }
    }

    protected function sorting(): void
    {
        $allowed = [
            'cost',
            'sale_price',
            'min_quantity',
            'max_quantity',
            'created_at',
        ];

        $sort = $this->request->input('sort', 'created_at');

        if (! in_array($sort, $allowed)) {
            $sort = 'created_at';
        }

        $direction = strtolower(
            $this->request->input('direction', 'desc')
        );

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $this->query->orderBy($sort, $direction);
    }
}