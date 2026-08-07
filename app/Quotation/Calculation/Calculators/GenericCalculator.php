<?php

namespace App\Quotation\Calculation\Calculators;

use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\DTOs\CalculationResult;

class GenericCalculator implements CalculatorInterface
{
    /**
     * Calcular un item genérico.
     */
    public function calculate(array $item): CalculationResult
    {
        $quantity = (float) ($item['quantity'] ?? 0);

        $unitCost = (float) ($item['unit_cost'] ?? 0);

        $unitPrice = (float) ($item['unit_price'] ?? 0);

        return new CalculationResult(

            item: $item,

            quantity: $quantity,

            unitCost: $unitCost,

            unitPrice: $unitPrice,

            subtotalCost: $quantity * $unitCost,

            subtotalSale: $quantity * $unitPrice,

            metadata: []

        );
    }
}