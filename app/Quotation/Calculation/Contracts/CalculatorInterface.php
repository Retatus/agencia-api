<?php

namespace App\Quotation\Calculation\Contracts;

use App\Quotation\Calculation\DTOs\CalculationResult;

interface CalculatorInterface
{
    public function calculate(
        array $item
    ): CalculationResult;
}