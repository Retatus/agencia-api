<?php

namespace App\Quotation\Calculation\Factories;

use App\Quotation\Calculation\Calculators\AccommodationCalculator;
use App\Quotation\Calculation\Calculators\GenericCalculator;
use App\Quotation\Calculation\Calculators\TransportCalculator;
use App\Quotation\Calculation\Contracts\CalculatorInterface;

class CalculatorFactory
{
    public function __construct(
        protected GenericCalculator $genericCalculator,
        protected AccommodationCalculator $accommodationCalculator,
        protected TransportCalculator $transportCalculator
    ) {
    }

    /**
     * Resolver el calculador correspondiente al item.
     */
    public function make(
        array $item
    ): CalculatorInterface {

        return match (
            $item['calculation_type']
            ?? 'generic'
        ) {

            'accommodation' =>
                $this->accommodationCalculator,

            'transport' =>
                $this->transportCalculator,

            default =>
                $this->genericCalculator,
        };
    }
}