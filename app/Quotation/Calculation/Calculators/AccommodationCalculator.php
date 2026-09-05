<?php

namespace App\Quotation\Calculation\Calculators;

use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\DTOs\CalculationResult;
use App\Quotation\Calculation\Services\AccommodationRecommendationPricer;
use App\Quotation\Calculation\Services\RoomAllocator;

class AccommodationCalculator implements CalculatorInterface
{
    public function __construct(
        protected RoomAllocator $roomAllocator,
        protected AccommodationRecommendationPricer $recommendationPricer,
    ) {
    }

    public function calculate(
        array $item
    ): CalculationResult {

        $passengers =
            $item['passengers'] ?? [];

        $roomTypes =
            $item['room_types'] ?? [];

        $nights = max(
            1,
            (int) ($item['duration'] ?? 1)
        );

        /*
        |--------------------------------------------------------------------------
        | Obtener recomendaciones
        |--------------------------------------------------------------------------
        */

        $recommendations =
            $this->roomAllocator->recommend(
                passengers: $passengers,
                roomTypes: $roomTypes,
                limit: 100
            );

        $currencyId = (int) ($item['currency_id'] ?? 0);
        $serviceDate = $item['service_date'] ?? null;
        $pricingError = null;

        if ($currencyId <= 0 || $serviceDate === null) {
            $recommendations = [];
            $pricingError = 'El alojamiento requiere una fecha de servicio y una moneda válidas.';
        } else {
            $recommendations = collect($recommendations)
                ->map(fn (array $recommendation) =>
                    $this->recommendationPricer->price(
                        recommendation: $recommendation,
                        currencyId: $currencyId,
                        serviceDate: $serviceDate,
                        nights: $nights,
                        commercialPolicyId: isset($item['commercial_policy_id'])
                            ? (int) $item['commercial_policy_id']
                            : null,
                    )
                )
                ->filter()
                ->sort(function (array $left, array $right): int {
                    if ($left['unused_capacity'] !== $right['unused_capacity']) {
                        return $left['unused_capacity'] <=> $right['unused_capacity'];
                    }

                    if ($left['total_cost'] !== $right['total_cost']) {
                        return $left['total_cost'] <=> $right['total_cost'];
                    }

                    return $left['total_rooms'] <=> $right['total_rooms'];
                })
                ->take(5)
                ->values()
                ->map(function (array $recommendation, int $index): array {
                    $recommendation['rank'] = $index + 1;
                    $recommendation['recommended'] = $index === 0;

                    return $recommendation;
                })
                ->all();

            if (empty($recommendations)) {
                $pricingError = 'No existen tarifas de alojamiento compatibles con la fecha, moneda y cantidad de habitaciones.';
            }
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
                    'room_count' => 0,
                    'nights' => $nights,
                    'pricing_error' => $pricingError,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Selección automática
        |--------------------------------------------------------------------------
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

            quantity:
                $selected['total_rooms'],

            unitCost: 0,

            unitPrice: 0,

            subtotalCost:
                $selected['total_cost'],

            subtotalSale:
                $selected['total_sale'],

            metadata: [

                'recommendations' =>
                    $recommendations,

                'selected_recommendation' =>
                    $selected['rank'],

                  'passenger_count' =>
                    count($passengers),

                'room_count' =>
                    $selected['total_rooms'],

                'nights' =>
                    $nights,
            ]
        );
    }
}
