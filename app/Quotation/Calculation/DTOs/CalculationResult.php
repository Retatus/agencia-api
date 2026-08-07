<?php

namespace App\Quotation\Calculation\DTOs;

class CalculationResult
{
    public function __construct(

        public array $item,

        public float $quantity,

        public float $unitCost,

        public float $unitPrice,

        public float $subtotalCost,

        public float $subtotalSale,

        public array $metadata = []

    ) {
    }

    public function toArray(): array
    {
        return [

            'item' => $this->item,

            'quantity' => $this->quantity,

            'unit_cost' => $this->unitCost,

            'unit_price' => $this->unitPrice,

            'subtotal_cost' => $this->subtotalCost,

            'subtotal_sale' => $this->subtotalSale,

            'metadata' => $this->metadata,

        ];
    }
}