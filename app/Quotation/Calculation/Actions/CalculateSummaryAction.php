<?php

namespace App\Quotation\Calculation\Actions;

use App\Quotation\Calculation\DTOs\CalculationResult;

class CalculateSummaryAction
{
    /**
     * @param CalculationResult[] $results
     */
    public function execute(array $results): array
    {
        $totalCost = 0;
        $totalSale = 0;

        foreach ($results as $result) {

            $totalCost += $result->subtotalCost;
            $totalSale += $result->subtotalSale;

        }

        return [

            'total_cost' => round($totalCost, 2),

            'total_sale' => round($totalSale, 2),

            'profit' => round($totalSale - $totalCost, 2),

        ];
    }
}