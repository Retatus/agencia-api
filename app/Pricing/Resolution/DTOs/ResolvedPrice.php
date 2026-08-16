<?php

namespace App\Pricing\Resolution\DTOs;

final readonly class ResolvedPrice
{
    public function __construct(
        public int $serviceVariantId,

        public int $basePriceId,

        public ?int $priceListId,
        public ?int $priceListItemId,

        public string $pricingSource,

        public float $baseCost,
        public float $basePrice,

        public ?string $adjustmentType,
        public ?float $adjustmentValue,

        public float $finalCost,
        public float $finalPrice,
    ) {
    }

    public function toArray(): array
    {
        return [
            'service_variant_id' => $this->serviceVariantId,

            'base_price_id' => $this->basePriceId,

            'price_list_id' => $this->priceListId,

            'price_list_item_id' => $this->priceListItemId,

            'pricing_source' => $this->pricingSource,

            'base_cost' => $this->baseCost,

            'base_price' => $this->basePrice,

            'adjustment_type' => $this->adjustmentType,

            'adjustment_value' => $this->adjustmentValue,

            'final_cost' => $this->finalCost,

            'final_price' => $this->finalPrice,
        ];
    }
}