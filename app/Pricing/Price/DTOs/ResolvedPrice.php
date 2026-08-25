<?php

namespace App\Pricing\Price\DTOs;

use App\Pricing\Price\Models\Price;

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
        public ?string $adjustmentValue = null,
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
            'adjustment_value' => $this->adjustmentValue,
        ];
    }
}
