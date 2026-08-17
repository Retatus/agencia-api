<?php

namespace App\Quotation\Calculation\Calculators;

use App\Pricing\Resolution\DTOs\PriceContext;
use App\Pricing\Resolution\Services\PriceResolver;
use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\DTOs\CalculationResult;
use Carbon\Carbon;

class GenericCalculator implements CalculatorInterface
{
    public function __construct(
        protected PriceResolver $priceResolver
    ) {
    }

    /**
     * Calcular un item genérico.
     *
     * Casos:
     *
     * CATALOG
     *   → PriceResolver
     *
     * CUSTOM
     *   → precio manual enviado en el item
     */
    public function calculate(
        array $item
    ): CalculationResult {

        /*
        |--------------------------------------------------------------------------
        | Cantidad
        |--------------------------------------------------------------------------
        */

        $quantity =
            (float) (
                $item['quantity']
                ?? 0
            );

        /*
        |--------------------------------------------------------------------------
        | Tipo de item
        |--------------------------------------------------------------------------
        */

        $itemType =
            $item['item_type']
            ?? 'CATALOG';

        /*
        |--------------------------------------------------------------------------
        | CUSTOM
        |--------------------------------------------------------------------------
        |
        | Los servicios manuales no pertenecen al catálogo y por tanto
        | no pasan por PriceResolver.
        |
        */

        if ($itemType === 'CUSTOM') {

            return $this->calculateCustom(
                item: $item,
                quantity: $quantity
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CATALOG
        |--------------------------------------------------------------------------
        */

        return $this->calculateCatalog(
            item: $item,
            quantity: $quantity
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Catalog
    |--------------------------------------------------------------------------
    */

    protected function calculateCatalog(
        array $item,
        float $quantity
    ): CalculationResult {

        /*
        |--------------------------------------------------------------------------
        | Service Variant
        |--------------------------------------------------------------------------
        */

        $serviceVariantId =
            $item['service_variant_id']
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Fecha del servicio
        |--------------------------------------------------------------------------
        */

        $serviceDate =
            $this->resolveServiceDate(
                $item
            );

        /*
        |--------------------------------------------------------------------------
        | Compatibilidad temporal
        |--------------------------------------------------------------------------
        |
        | Si todavía existe algún flujo antiguo que no envía variante o fecha,
        | mantenemos el comportamiento anterior.
        |
        */

        if (
            $serviceVariantId === null
            || $serviceDate === null
        ) {
            return $this->calculateLegacy(
                item: $item,
                quantity: $quantity
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Resolver precio
        |--------------------------------------------------------------------------
        */

        $resolved =
            $this->priceResolver->resolve(
                new PriceContext(
                    serviceVariantId:
                        (int) $serviceVariantId,

                    date:
                        Carbon::parse(
                            $serviceDate
                        )
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Precio final
        |--------------------------------------------------------------------------
        */

        $unitCost =
            (float) $resolved->finalCost;

        $unitPrice =
            (float) $resolved->finalPrice;

        /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

        return new CalculationResult(

            item:
                $item,

            quantity:
                $quantity,

            unitCost:
                $unitCost,

            unitPrice:
                $unitPrice,

            subtotalCost:
                $quantity
                * $unitCost,

            subtotalSale:
                $quantity
                * $unitPrice,

            /*
            |--------------------------------------------------------------------------
            | Pricing Metadata
            |--------------------------------------------------------------------------
            */

            metadata: [

                'service_date' =>
                    $serviceDate,

                'pricing_source' =>
                    $resolved->pricingSource,

                'base_price_id' =>
                    $resolved->basePriceId,

                'price_list_id' =>
                    $resolved->priceListId,

                'price_list_item_id' =>
                    $resolved->priceListItemId,

                'base_cost' =>
                    $resolved->baseCost,

                'base_price' =>
                    $resolved->basePrice,

                'adjustment_type' =>
                    $resolved->adjustmentType,

                'adjustment_value' =>
                    $resolved->adjustmentValue,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Custom
    |--------------------------------------------------------------------------
    |
    | Mantiene exactamente la filosofía del GenericCalculator anterior.
    |
    */

    protected function calculateCustom(
        array $item,
        float $quantity
    ): CalculationResult {

        $unitCost =
            (float) (
                $item['unit_cost']
                ?? 0
            );

        $unitPrice =
            (float) (
                $item['unit_price']
                ?? 0
            );

        return new CalculationResult(

            item:
                $item,

            quantity:
                $quantity,

            unitCost:
                $unitCost,

            unitPrice:
                $unitPrice,

            subtotalCost:
                $quantity
                * $unitCost,

            subtotalSale:
                $quantity
                * $unitPrice,

            metadata: [
                'pricing_source' =>
                    'MANUAL',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy
    |--------------------------------------------------------------------------
    |
    | Lo dejamos temporalmente mientras terminamos de migrar todos los
    | consumidores del engine.
    |
    */

    protected function calculateLegacy(
        array $item,
        float $quantity
    ): CalculationResult {

        $unitCost =
            (float) (
                $item['unit_cost']
                ?? 0
            );

        $unitPrice =
            (float) (
                $item['unit_price']
                ?? 0
            );

        return new CalculationResult(

            item:
                $item,

            quantity:
                $quantity,

            unitCost:
                $unitCost,

            unitPrice:
                $unitPrice,

            subtotalCost:
                $quantity
                * $unitCost,

            subtotalSale:
                $quantity
                * $unitPrice,

            metadata: [
                'pricing_source' =>
                    'LEGACY',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Service Date
    |--------------------------------------------------------------------------
    */

    protected function resolveServiceDate(
        array $item
    ): ?string {

        return
            $item['service_date']
            ?? $item['travel_date']
            ?? $item['itinerary_date']
            ?? null;
    }
}