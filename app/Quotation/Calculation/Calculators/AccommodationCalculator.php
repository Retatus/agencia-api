<?php

namespace App\Quotation\Calculation\Calculators;

use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\DTOs\CalculationResult;
use App\Quotation\Calculation\Services\RoomAllocator;

class AccommodationCalculator implements CalculatorInterface
{
    public function __construct(
        protected RoomAllocator $roomAllocator
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
                limit: 5
            );

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
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Aplicar noches a cada recomendación
        |--------------------------------------------------------------------------
        */

        $recommendations = collect($recommendations)
            ->map(function ($recommendation) use ($nights) {

                $totalCost = 0;
                $totalSale = 0;

                $recommendation['rooms'] =
                    collect($recommendation['rooms'])
                        ->map(function ($room) use (
                            $nights,
                            &$totalCost,
                            &$totalSale
                        ) {

                            $room['nights'] =
                                $nights;

                            $room['subtotal_cost'] =
                                $room['quantity']
                                * $room['unit_cost']
                                * $nights;

                            $room['subtotal_sale'] =
                                $room['quantity']
                                * $room['unit_price']
                                * $nights;

                            $totalCost +=
                                $room['subtotal_cost'];

                            $totalSale +=
                                $room['subtotal_sale'];

                            return $room;
                        })
                        ->values()
                        ->all();

                $recommendation['total_cost'] =
                    $totalCost;

                $recommendation['total_sale'] =
                    $totalSale;

                return $recommendation;
            })
            ->values()
            ->all();

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