<?php

namespace App\Quotation\Calculation\Engines;

use App\Quotation\Calculation\DTOs\CalculationRequest;
use App\Quotation\Calculation\Factories\CalculatorFactory;

class QuotationCalculationEngine
{
    public function __construct(
        protected CalculatorFactory $calculatorFactory
    ) {
    }

    public function calculate(
        CalculationRequest $request
    ): array {
        $results = [];

        foreach ($request->itineraries() as $itinerary) {

            foreach ($itinerary['items'] ?? [] as $item) {

                $calculator = $this->calculatorFactory->make(
                    $item
                );

                $results[] = $calculator->calculate(
                    $item
                );
            }
        }

        return $results;
    }
}