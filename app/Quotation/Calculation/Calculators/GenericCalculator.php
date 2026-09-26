<?php

namespace App\Quotation\Calculation\Calculators;

use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\DTOs\CalculationResult;
use App\Quotation\Calculation\Services\GenericGroupPricer;

class GenericCalculator implements CalculatorInterface
{
    public function __construct(
        private GenericGroupPricer $groupPricer,
    ) {
    }

    /**
     * Calcular un item genérico.
     */
    public function calculate(array $item): CalculationResult
    {
        if (
            ($item['item_type'] ?? 'CATALOG') === 'CATALOG'
            && ($item['pricing_mode'] ?? null) === 'AUTO_GROUP'
        ) {
            return $this->calculateAutomaticGroup($item);
        }

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

    private function calculateAutomaticGroup(array $item): CalculationResult
    {
        $pricing = $this->groupPricer->price($item);
        $quantity = (float) $pricing['billing_quantity'];
        $unitCost = (float) $pricing['unit_cost'];
        $unitPrice = (float) $pricing['unit_price'];

        $resolvedItem = array_merge($item, [
            'quantity' => $quantity,
            'price_id' => $pricing['price_id'],
            'price_list_id' => $pricing['price_list_id'],
            'price_list_item_id' => $pricing['price_list_item_id'],
            'base_cost' => $pricing['base_cost'],
            'base_price' => $pricing['base_price'],
            'unit_cost' => $unitCost,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
            'subtotal_cost' => $quantity * $unitCost,
            'subtotal_sale' => $quantity * $unitPrice,
        ]);

        return new CalculationResult(
            item: $resolvedItem,
            quantity: $quantity,
            unitCost: $unitCost,
            unitPrice: $unitPrice,
            subtotalCost: $quantity * $unitCost,
            subtotalSale: $quantity * $unitPrice,
            metadata: [
                'pricing_mode' => 'AUTO_GROUP',
                'price_type_id' => $pricing['price_type_id'],
                'price_type_code' => $pricing['price_type_code'],
                'quantity_basis' => $pricing['quantity_basis'],
                'passenger_count' => $pricing['pricing_quantity'],
                'pricing_quantity' => $pricing['pricing_quantity'],
                'billing_quantity' => $pricing['billing_quantity'],
                'price_id' => $pricing['price_id'],
                'price_list_id' => $pricing['price_list_id'],
                'price_list_item_id' => $pricing['price_list_item_id'],
                'adjustment_type' => $pricing['adjustment_type'],
                'cost_adjustment' => $pricing['cost_adjustment'],
                'sale_adjustment' => $pricing['sale_adjustment'],
            ],
        );
    }
}
