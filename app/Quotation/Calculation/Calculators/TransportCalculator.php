<?php

namespace App\Quotation\Calculation\Calculators;

use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\DTOs\CalculationResult;
use App\Quotation\Calculation\Services\VehicleAllocator;

class TransportCalculator implements CalculatorInterface
{
    public function __construct(
        protected VehicleAllocator $vehicleAllocator
    ) {
    }

    /**
     * Calcular transporte.
     *
     * Responsabilidades:
     *
     * - Obtener pasajeros.
     * - Obtener vehículos candidatos.
     * - Solicitar recomendaciones al VehicleAllocator.
     * - Seleccionar la mejor recomendación.
     * - Construir CalculationResult.
     *
     * El cálculo de precios NO pertenece aquí.
     * VehicleAllocator utilizará PriceResolver para valorizar
     * cada combinación.
     */
    public function calculate(
        array $item
    ): CalculationResult {

        /*
        |--------------------------------------------------------------------------
        | Entrada
        |--------------------------------------------------------------------------
        */

        $passengers =
            $item['passengers'] ?? [];

        $vehicleTypes =
            $item['vehicle_types'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Fecha del servicio
        |--------------------------------------------------------------------------
        |
        | PriceResolver necesita conocer la fecha para determinar
        | la PriceList correspondiente a TRANSPORT.
        |
        */

        $serviceDate =
            $this->resolveServiceDate($item);

        /*
        |--------------------------------------------------------------------------
        | Recomendaciones
        |--------------------------------------------------------------------------
        */

        $recommendations =
            $this->vehicleAllocator->recommend(
                passengers: $passengers,
                vehicleTypes: $vehicleTypes,
                serviceDate: $serviceDate,
                limit: 5
            );

        /*
        |--------------------------------------------------------------------------
        | Sin recomendaciones
        |--------------------------------------------------------------------------
        */

        if (empty($recommendations)) {

            return $this->emptyResult(
                item: $item,
                passengers: $passengers
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Recomendación seleccionada
        |--------------------------------------------------------------------------
        |
        | VehicleAllocator debe devolver las recomendaciones ordenadas
        | según el criterio que ya tienes implementado.
        |
        */

        $selected =
            $recommendations[0];

        /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

        return new CalculationResult(
            item: $item,

            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            |
            | Para transporte representa cantidad total de vehículos.
            |
            */

            quantity:
                $selected['total_vehicles'],

            /*
            |--------------------------------------------------------------------------
            | Unitarios
            |--------------------------------------------------------------------------
            |
            | Una recomendación puede estar formada por distintos vehículos.
            | Por eso no existe necesariamente un único precio unitario.
            |
            */

            unitCost: 0,

            unitPrice: 0,

            /*
            |--------------------------------------------------------------------------
            | Totales
            |--------------------------------------------------------------------------
            |
            | Estos valores ya vienen valorizados por VehicleAllocator
            | utilizando PriceResolver.
            |
            */

            subtotalCost:
                $selected['total_cost'],

            subtotalSale:
                $selected['total_sale'],

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */

            metadata: [

                'recommendations' =>
                    $recommendations,

                'selected_recommendation' =>
                    $selected['rank'],

                'passenger_count' =>
                    count($passengers),

                'vehicle_count' =>
                    $selected['total_vehicles'],

                'total_capacity' =>
                    $selected['total_capacity'],

                'unused_capacity' =>
                    $selected['unused_capacity'],

                /*
                |--------------------------------------------------------------------------
                | Pricing context
                |--------------------------------------------------------------------------
                */

                'service_date' =>
                    $serviceDate,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Service Date
    |--------------------------------------------------------------------------
    |
    | Prioridad:
    |
    | 1. Fecha específica enviada al item.
    | 2. Fecha del itinerario.
    | 3. Fecha general disponible en el contexto.
    |
    */

    protected function resolveServiceDate(
        array $item
    ): ?string {

        return
            $item['service_date']
            ?? $item['travel_date']
            ?? $item['itinerary_date']
            ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | Empty Result
    |--------------------------------------------------------------------------
    |
    | Extraemos este bloque para mantener calculate() más limpio.
    |
    */

    protected function emptyResult(
        array $item,
        array $passengers
    ): CalculationResult {

        return new CalculationResult(
            item: $item,

            quantity: 0,

            unitCost: 0,

            unitPrice: 0,

            subtotalCost: 0,

            subtotalSale: 0,

            metadata: [
                'recommendations' => [],

                'selected_recommendation' =>
                    null,

                'passenger_count' =>
                    count($passengers),

                'vehicle_count' =>
                    0,

                'total_capacity' =>
                    0,

                'unused_capacity' =>
                    0,
            ]
        );
    }
}