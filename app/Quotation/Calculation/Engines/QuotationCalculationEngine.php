<?php

namespace App\Quotation\Calculation\Engines;

use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\DTOs\CalculationRequest;

class QuotationCalculationEngine
{
    public function __construct(
        protected CalculatorInterface $calculator
    ) {
    }

    /**
     * Ejecuta el cálculo de todos los items de la cotización.
     */
    public function calculate(CalculationRequest $request): array
    {
        $results = [];

        foreach ($request->itineraries() as $itinerary) {

            foreach ($itinerary['items'] ?? [] as $item) {

                $results[] = $this->calculator->calculate(
                    $item
                );

            }

        }

        return $results;
    }
}