<?php

namespace App\Pricing\Price\DTOs;

use App\Pricing\Price\Models\Price;
use Carbon\CarbonImmutable;

final readonly class ResolvedPrice
{
    public function __construct(
        public int $priceId,
        public int $currencyId,
        public string $baseCost,
        public string $baseSalePrice,
        public string $finalCost,
        public string $finalSalePrice,
        public ?int $priceListId = null,
        public ?int $priceListItemId = null,
        public ?string $adjustmentType = null,
        public ?string $costAdjustment = null,
        public ?string $saleAdjustment = null,
        public ?int $sourceCurrencyId = null,
        public ?string $sourceBaseCost = null,
        public ?string $sourceBaseSalePrice = null,
        public ?string $sourceFinalCost = null,
        public ?string $sourceFinalSalePrice = null,
        public string $exchangeRate = '1.00000000',
        public ?CarbonImmutable $exchangeRateDate = null,
    ) {
    }

    public static function fromPrice(Price $price): self
    {
        return new self(
            priceId: $price->getKey(),
            currencyId: $price->currency_id,
            baseCost: $price->cost,
            baseSalePrice: $price->sale_price,
            finalCost: $price->cost,
            finalSalePrice: $price->sale_price,
            sourceCurrencyId: (int) $price->currency_id,
            sourceBaseCost: $price->cost,
            sourceBaseSalePrice: $price->sale_price,
            sourceFinalCost: $price->cost,
            sourceFinalSalePrice: $price->sale_price,
        );
    }

    public function converted(
        int $targetCurrencyId,
        string $baseCost,
        string $baseSalePrice,
        string $finalCost,
        string $finalSalePrice,
        string $exchangeRate,
        CarbonImmutable $exchangeRateDate,
    ): self {
        return new self(
            priceId: $this->priceId,
            currencyId: $targetCurrencyId,
            baseCost: $baseCost,
            baseSalePrice: $baseSalePrice,
            finalCost: $finalCost,
            finalSalePrice: $finalSalePrice,
            priceListId: $this->priceListId,
            priceListItemId: $this->priceListItemId,
            adjustmentType: $this->adjustmentType,
            costAdjustment: $this->costAdjustment,
            saleAdjustment: $this->saleAdjustment,
            sourceCurrencyId: $this->sourceCurrencyId ?? $this->currencyId,
            sourceBaseCost: $this->sourceBaseCost ?? $this->baseCost,
            sourceBaseSalePrice: $this->sourceBaseSalePrice ?? $this->baseSalePrice,
            sourceFinalCost: $this->sourceFinalCost ?? $this->finalCost,
            sourceFinalSalePrice: $this->sourceFinalSalePrice ?? $this->finalSalePrice,
            exchangeRate: $exchangeRate,
            exchangeRateDate: $exchangeRateDate,
        );
    }

    public function toArray(): array
    {
        return [
            'price_id' => $this->priceId,
            'currency_id' => $this->currencyId,
            'base_cost' => $this->baseCost,
            'base_sale_price' => $this->baseSalePrice,
            'final_cost' => $this->finalCost,
            'final_sale_price' => $this->finalSalePrice,
            'price_list_id' => $this->priceListId,
            'price_list_item_id' => $this->priceListItemId,
            'adjustment_type' => $this->adjustmentType,
            'cost_adjustment' => $this->costAdjustment,
            'sale_adjustment' => $this->saleAdjustment,
            'source_currency_id' => $this->sourceCurrencyId,
            'source_base_cost' => $this->sourceBaseCost,
            'source_base_sale_price' => $this->sourceBaseSalePrice,
            'source_final_cost' => $this->sourceFinalCost,
            'source_final_sale_price' => $this->sourceFinalSalePrice,
            'exchange_rate' => $this->exchangeRate,
            'exchange_rate_date' => $this->exchangeRateDate?->toDateString(),
        ];
    }
}
