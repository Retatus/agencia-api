<?php

namespace App\Pricing\Price\Services;

use App\Pricing\Price\Contracts\PriceAdjustmentPolicy;
use App\Pricing\Price\Contracts\PriceResolverInterface;
use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\DTOs\ResolvedPrice;
use App\Pricing\ExchangeRate\Services\CurrencyConverter;

final readonly class PricingService
{
    public function __construct(
        private PriceResolverInterface $priceResolver,
        private PriceAdjustmentPolicy $adjustmentPolicy,
        private CurrencyConverter $currencyConverter,
    ) {
    }

    public function resolve(PriceContext $context): ResolvedPrice
    {
        $price = $this->priceResolver->resolve($context);

        $resolved = $this->adjustmentPolicy->apply($price, $context);

        if ($resolved->currencyId === $context->currencyId) {
            return $resolved->converted(
                targetCurrencyId: $context->currencyId,
                baseCost: $resolved->baseCost,
                baseSalePrice: $resolved->baseSalePrice,
                finalCost: $resolved->finalCost,
                finalSalePrice: $resolved->finalSalePrice,
                exchangeRate: '1.00000000',
                exchangeRateDate: $context->conversionDate(),
            );
        }

        $date = $context->conversionDate();
        $exchangeRate = $this->currencyConverter->convert(
            '0',
            $resolved->currencyId,
            $context->currencyId,
            $date,
        )['exchange_rate'];

        return $resolved->converted(
            targetCurrencyId: $context->currencyId,
            baseCost: $this->currencyConverter->apply($resolved->baseCost, $exchangeRate),
            baseSalePrice: $this->currencyConverter->apply($resolved->baseSalePrice, $exchangeRate),
            finalCost: $this->currencyConverter->apply($resolved->finalCost, $exchangeRate),
            finalSalePrice: $this->currencyConverter->apply($resolved->finalSalePrice, $exchangeRate),
            exchangeRate: $exchangeRate->rate,
            exchangeRateDate: $exchangeRate->effectiveDate,
        );
    }
}
