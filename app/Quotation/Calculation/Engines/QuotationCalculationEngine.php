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

                $item['currency_id'] ??=
                    $request->currencyId();

                $item['service_date'] ??=
                    $itinerary['travel_date']
                    ?? $request->toArray()['travel_date']
                    ?? null;

                $item['passengers'] ??=
                    $request->passengers();

                $item['commercial_policy_id'] ??=
                    $request->commercialPolicyId();

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
