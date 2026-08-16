<?php

namespace App\Pricing\Resolution\Services;

use App\Models\ServiceVariant;
use App\Pricing\PriceList\Models\PriceList;
use App\Pricing\Resolution\DTOs\PriceContext;
use App\Pricing\Resolution\DTOs\ResolvedPrice;
use RuntimeException;

class PriceResolver
{
    public function resolve(
        PriceContext $context
    ): ResolvedPrice {
        /*
        |--------------------------------------------------------------------------
        | Variant
        |--------------------------------------------------------------------------
        */

        $variant = ServiceVariant::query()
            ->with([
                'service',
                'basePrice',
            ])
            ->findOrFail(
                $context->serviceVariantId
            );

        /*
        |--------------------------------------------------------------------------
        | Base Price
        |--------------------------------------------------------------------------
        */

        $basePrice = $variant->basePrice;

        if (! $basePrice || ! $basePrice->active) {
            throw new RuntimeException(
                'La variante no tiene un precio base activo.'
            );
        }

        $baseCost = (float) $basePrice->cost;
        $baseSalePrice = (float) $basePrice->sale_price;

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        $categoryId =
            $variant->service
                ->service_category_id;

        /*
        |--------------------------------------------------------------------------
        | Buscar PriceList activa para categoría + fecha
        |--------------------------------------------------------------------------
        */

        $priceList = PriceList::query()
            ->where(
                'service_category_id',
                $categoryId
            )
            ->where('active', true)

            ->where(function ($query) use ($context) {
                $query
                    ->whereNull('valid_from')
                    ->orWhereDate(
                        'valid_from',
                        '<=',
                        $context->date
                    );
            })

            ->where(function ($query) use ($context) {
                $query
                    ->whereNull('valid_to')
                    ->orWhereDate(
                        'valid_to',
                        '>=',
                        $context->date
                    );
            })

            ->orderByDesc('priority')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | No existe temporada
        |--------------------------------------------------------------------------
        */

        if (! $priceList) {
            return new ResolvedPrice(
                serviceVariantId:
                    $variant->id,

                basePriceId:
                    $basePrice->id,

                priceListId:
                    null,

                priceListItemId:
                    null,

                pricingSource:
                    'BASE',

                baseCost:
                    $baseCost,

                basePrice:
                    $baseSalePrice,

                adjustmentType:
                    null,

                adjustmentValue:
                    null,

                finalCost:
                    $baseCost,

                finalPrice:
                    $baseSalePrice,
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Buscar regla específica de la variante
        |--------------------------------------------------------------------------
        */

        $item = $priceList
            ->priceListItems()
            ->where(
                'service_variant_id',
                $variant->id
            )
            ->where('active', true)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Existe PriceList pero no regla para esta variante
        |--------------------------------------------------------------------------
        |
        | Se mantiene precio base.
        |
        */

        if (! $item) {
            return new ResolvedPrice(
                serviceVariantId:
                    $variant->id,

                basePriceId:
                    $basePrice->id,

                priceListId:
                    $priceList->id,

                priceListItemId:
                    null,

                pricingSource:
                    'BASE',

                baseCost:
                    $baseCost,

                basePrice:
                    $baseSalePrice,

                adjustmentType:
                    null,

                adjustmentValue:
                    null,

                finalCost:
                    $baseCost,

                finalPrice:
                    $baseSalePrice,
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Resolver ajuste
        |--------------------------------------------------------------------------
        */

        [$finalCost, $finalPrice] =
            $this->applyAdjustment(
                baseCost:
                    $baseCost,

                basePrice:
                    $baseSalePrice,

                item:
                    $item,
            );

        return new ResolvedPrice(
            serviceVariantId:
                $variant->id,

            basePriceId:
                $basePrice->id,

            priceListId:
                $priceList->id,

            priceListItemId:
                $item->id,

            pricingSource:
                'PRICE_LIST',

            baseCost:
                $baseCost,

            basePrice:
                $baseSalePrice,

            adjustmentType:
                $item->adjustment_type,

            adjustmentValue:
                $item->adjustment_value !== null
                    ? (float) $item->adjustment_value
                    : null,

            finalCost:
                $finalCost,

            finalPrice:
                $finalPrice,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Adjustment
    |--------------------------------------------------------------------------
    */

    private function applyAdjustment(
        float $baseCost,
        float $basePrice,
        $item,
    ): array {
        return match (
            $item->adjustment_type
        ) {
            'OVERRIDE' => [
                $item->override_cost !== null
                    ? (float) $item->override_cost
                    : $baseCost,

                $item->override_sale_price !== null
                    ? (float) $item->override_sale_price
                    : $basePrice,
            ],

            'FIXED' => [
                $baseCost,
                $basePrice +
                (float) $item->adjustment_value,
            ],

            'PERCENTAGE' => [
                $baseCost,

                $basePrice *
                (
                    1 +
                    (
                        (float) $item->adjustment_value
                        / 100
                    )
                ),
            ],

            default => [
                $baseCost,
                $basePrice,
            ],
        };
    }
}