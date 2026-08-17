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

        /*
        |--------------------------------------------------------------------------
        | Itinerarios
        |--------------------------------------------------------------------------
        */

        foreach (
            $request->itineraries()
            as $itinerary
        ) {

            /*
            |--------------------------------------------------------------------------
            | Fecha del itinerario
            |--------------------------------------------------------------------------
            |
            | Esta fecha será utilizada por PriceResolver para determinar
            | la PriceList correspondiente.
            |
            */

            $itineraryDate =
                $itinerary['travel_date']
                ?? $request->travelDate();

            /*
            |--------------------------------------------------------------------------
            | Items
            |--------------------------------------------------------------------------
            */

            foreach (
                $itinerary['items'] ?? []
                as $item
            ) {

                /*
                |--------------------------------------------------------------------------
                | Contexto del servicio
                |--------------------------------------------------------------------------
                |
                | No sobrescribimos service_date si el item ya trae
                | una fecha específica.
                |
                */

                $item['service_date'] =
                    $item['service_date']
                    ?? $item['travel_date']
                    ?? $itineraryDate;

                /*
                |--------------------------------------------------------------------------
                | Itinerary Date
                |--------------------------------------------------------------------------
                |
                | También la dejamos disponible explícitamente por
                | compatibilidad con los calculators actuales.
                |
                */

                $item['itinerary_date'] =
                    $itineraryDate;

                /*
                |--------------------------------------------------------------------------
                | Calculator
                |--------------------------------------------------------------------------
                */

                $calculator =
                    $this->calculatorFactory->make(
                        $item
                    );

                /*
                |--------------------------------------------------------------------------
                | Calculation
                |--------------------------------------------------------------------------
                */

                $results[] =
                    $calculator->calculate(
                        $item
                    );
            }
        }

        return $results;
    }
}