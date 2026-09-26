<?php

namespace App\Pricing\Price\Policies;

use App\Pricing\Price\Contracts\PriceAdjustmentPolicy;
use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\DTOs\ResolvedPrice;
use App\Pricing\Price\Models\Price;
use App\Pricing\PriceList\Exceptions\InvalidPriceListException;
use App\Pricing\PriceList\Models\PriceList;
use App\Pricing\PriceList\Services\PriceAdjustmentCalculator;

final readonly class PriceListAdjustmentPolicy implements PriceAdjustmentPolicy
{
    public function __construct(
        private PriceAdjustmentCalculator $calculator,
    ) {
    }

    public function apply(
        Price $price,
        PriceContext $context
    ): ResolvedPrice {
        if ($context->commercialPolicyId === null) {
            return ResolvedPrice::fromPrice($price);
        }

        $date = $context->serviceDate->toDateString();

        $priceList = PriceList::query()
            ->whereKey($context->commercialPolicyId)
            ->where('active', true)
            ->where('currency_id', $context->currencyId)
            ->whereDate('valid_from', '<=', $date)
            ->whereDate('valid_to', '>=', $date)
            ->first();

        if ($priceList === null) {
            throw new InvalidPriceListException(
                'La lista de precios seleccionada no está activa, vigente o no corresponde a la moneda solicitada.'
            );
        }

        $item = $priceList->items()
            ->where('price_id', $price->getKey())
            ->where('active', true)
            ->first();

        if ($item === null) {
            return ResolvedPrice::fromPrice($price);
        }

        $finalCost = $this->calculator->calculate(
            $price->cost,
            $item->adjustment_type,
            $item->cost_adjustment,
        );
        $finalSalePrice = $this->calculator->calculate(
            $price->sale_price,
            $item->adjustment_type,
            $item->sale_adjustment,
        );

        return new ResolvedPrice(
            priceId: (int) $price->getKey(),
            currencyId: (int) $price->currency_id,
            baseCost: $price->cost,
            baseSalePrice: $price->sale_price,
            finalCost: $finalCost,
            finalSalePrice: $finalSalePrice,
            priceListId: (int) $priceList->getKey(),
            priceListItemId: (int) $item->getKey(),
            adjustmentType: $item->adjustment_type->value,
            costAdjustment: $item->cost_adjustment,
            saleAdjustment: $item->sale_adjustment,
        );
    }
}
