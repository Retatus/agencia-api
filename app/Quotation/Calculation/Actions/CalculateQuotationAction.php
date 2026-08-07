<?php

namespace App\Quotation\Calculation\Actions;

use App\Quotation\Calculation\DTOs\CalculationRequest;
use App\Quotation\Calculation\Engines\QuotationCalculationEngine;

class CalculateQuotationAction
{
    public function __construct(
        protected QuotationCalculationEngine $engine,
        protected CalculateSummaryAction $summaryAction
    ) {
    }

    public function execute(array $quotation): array
    {
        $request = CalculationRequest::fromArray($quotation);

        $results = $this->engine->calculate($request);

        return [

            'items' => array_map(
                fn ($item) => $item->toArray(),
                $results
            ),

            'summary' => $this->summaryAction->execute($results),

        ];
    }
}