<?php

namespace App\Quotation\Calculation\Calculators;

use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\DTOs\CalculationResult;
use App\Quotation\Calculation\Services\TransportRecommendationPricer;
use App\Quotation\Calculation\Services\VehicleAllocator;

class TransportCalculator implements CalculatorInterface
{
    public function __construct(
        protected VehicleAllocator $vehicleAllocator,
        protected TransportRecommendationPricer $recommendationPricer,
    ) {
    }

    /**
     * Calcular transporte.
     *
     * Por ahora:
     *
     * - Genera recomendaciones de vehículos.
     * - Usa la recomendación #1 como selección automática.
     * - Devuelve las demás recomendaciones en metadata.
     *
     * Más adelante podremos considerar:
     *
     * - equipaje,
     * - carga,
     * - cantidad de trayectos,
     * - duración,
     * - vehículo seleccionado manualmente.
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
        | Recomendaciones
        |--------------------------------------------------------------------------
        */

        $recommendations =
            $this->vehicleAllocator->recommend(
                passengers: $passengers,
                vehicleTypes: $vehicleTypes,
                limit: 100
            );

        $currencyId = (int) ($item['currency_id'] ?? 0);
        $serviceDate = $item['service_date'] ?? null;

        if ($currencyId <= 0 || $serviceDate === null) {
            $recommendations = [];
        } else {
            $recommendations = collect($recommendations)
                ->map(fn (array $recommendation) =>
                    $this->recommendationPricer->price(
                        recommendation: $recommendation,
                        passengerCount: count($passengers),
                        currencyId: $currencyId,
                        serviceDate: $serviceDate,
                    )
                )
                ->filter()
                ->sort(function (array $left, array $right): int {
                    if ($left['total_cost'] !== $right['total_cost']) {
                        return $left['total_cost'] <=> $right['total_cost'];
                    }

                    if ($left['total_vehicles'] !== $right['total_vehicles']) {
                        return $left['total_vehicles'] <=> $right['total_vehicles'];
                    }

                    return $left['unused_capacity'] <=> $right['unused_capacity'];
                })
                ->take(5)
                ->values()
                ->map(function (array $recommendation, int $index): array {
                    $recommendation['rank'] = $index + 1;
                    $recommendation['recommended'] = $index === 0;

                    return $recommendation;
                })
                ->all();
        }

        /*
        |--------------------------------------------------------------------------
        | Sin recomendaciones
        |--------------------------------------------------------------------------
        */

        if (empty($recommendations)) {

            return new CalculationResult(
                item: $item,

                quantity: 0,

                unitCost: 0,

                unitPrice: 0,

                subtotalCost: 0,

                subtotalSale: 0,

                metadata: [
                    'recommendations' => [],
                    'selected_recommendation' => null,
                    'passenger_count' => count($passengers),
                    'vehicle_count' => 0,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Recomendación seleccionada
        |--------------------------------------------------------------------------
        |
        | Por ahora seleccionamos automáticamente la primera.
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
            | No existe un único costo/precio unitario porque puede haber
            | diferentes tipos de vehículos dentro de la misma combinación.
            |
            */

            unitCost: 0,

            unitPrice: 0,

            /*
            |--------------------------------------------------------------------------
            | Totales
            |--------------------------------------------------------------------------
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
            ]
        );
    }
}