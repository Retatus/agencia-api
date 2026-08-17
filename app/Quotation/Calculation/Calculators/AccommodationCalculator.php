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

        /*
        |--------------------------------------------------------------------------
        | Entrada
        |--------------------------------------------------------------------------
        */

        $passengers =
            $item['passengers'] ?? [];

        $roomTypes =
            $item['room_types'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Noches
        |--------------------------------------------------------------------------
        */

        $nights = max(
            1,
            (int) ($item['duration'] ?? 1)
        );

        /*
        |--------------------------------------------------------------------------
        | Fecha del servicio
        |--------------------------------------------------------------------------
        |
        | Se pasa al RoomAllocator para que PriceResolver determine
        | la temporada correspondiente.
        |
        */

        $serviceDate =
            $this->resolveServiceDate(
                $item
            );

        /*
        |--------------------------------------------------------------------------
        | Obtener recomendaciones
        |--------------------------------------------------------------------------
        */

        $recommendations =
            $this->roomAllocator->recommend(
                passengers:
                    $passengers,

                roomTypes:
                    $roomTypes,

                serviceDate:
                    $serviceDate,

                limit:
                    5
            );

        /*
        |--------------------------------------------------------------------------
        | Sin recomendaciones
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $recommendations
            )
        ) {
            return $this->emptyResult(
                item:
                    $item,

                passengers:
                    $passengers,

                nights:
                    $nights,

                serviceDate:
                    $serviceDate
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Aplicar noches
        |--------------------------------------------------------------------------
        |
        | RoomAllocator devuelve el precio unitario por habitación.
        |
        | Aquí seguimos aplicando:
        |
        | cantidad habitaciones
        | × precio habitación
        | × noches
        |
        */

        $recommendations =
            collect(
                $recommendations
            )
                ->map(
                    function (
                        $recommendation
                    ) use ($nights) {

                        $totalCost =
                            0;

                        $totalSale =
                            0;

                        $recommendation['rooms'] =
                            collect(
                                $recommendation['rooms']
                            )
                                ->map(
                                    function (
                                        $room
                                    ) use (
                                        $nights,
                                        &$totalCost,
                                        &$totalSale
                                    ) {

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Nights
                                        |--------------------------------------------------------------------------
                                        */

                                        $room['nights'] =
                                            $nights;

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Subtotal Cost
                                        |--------------------------------------------------------------------------
                                        */

                                        $room['subtotal_cost'] =
                                            $room['quantity']
                                            * $room['unit_cost']
                                            * $nights;

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Subtotal Sale
                                        |--------------------------------------------------------------------------
                                        */

                                        $room['subtotal_sale'] =
                                            $room['quantity']
                                            * $room['unit_price']
                                            * $nights;

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Acumular
                                        |--------------------------------------------------------------------------
                                        */

                                        $totalCost +=
                                            $room[
                                                'subtotal_cost'
                                            ];

                                        $totalSale +=
                                            $room[
                                                'subtotal_sale'
                                            ];

                                        return $room;
                                    }
                                )
                                ->values()
                                ->all();

                        /*
                        |--------------------------------------------------------------------------
                        | Totales
                        |--------------------------------------------------------------------------
                        */

                        $recommendation[
                            'total_cost'
                        ] = $totalCost;

                        $recommendation[
                            'total_sale'
                        ] = $totalSale;

                        return $recommendation;
                    }
                )
                ->values()
                ->all();

        /*
        |--------------------------------------------------------------------------
        | Selección automática
        |--------------------------------------------------------------------------
        |
        | RoomAllocator devuelve las recomendaciones ordenadas.
        |
        | Por ahora mantenemos la recomendación #1.
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
            item:
                $item,

            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            |
            | Cantidad total de habitaciones.
            |
            */

            quantity:
                $selected['total_rooms'],

            /*
            |--------------------------------------------------------------------------
            | Unitarios
            |--------------------------------------------------------------------------
            |
            | Puede existir una combinación:
            |
            | 2 dobles + 1 triple
            |
            | por eso no existe un único precio unitario.
            |
            */

            unitCost:
                0,

            unitPrice:
                0,

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
                    count(
                        $passengers
                    ),

                'room_count' =>
                    $selected['total_rooms'],

                'nights' =>
                    $nights,

                /*
                |--------------------------------------------------------------------------
                | Pricing Context
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
    | 1. service_date
    | 2. travel_date
    | 3. itinerary_date
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
    */

    protected function emptyResult(
        array $item,
        array $passengers,
        int $nights,
        ?string $serviceDate
    ): CalculationResult {

        return new CalculationResult(
            item:
                $item,

            quantity:
                0,

            unitCost:
                0,

            unitPrice:
                0,

            subtotalCost:
                0,

            subtotalSale:
                0,

            metadata: [

                'recommendations' =>
                    [],

                'selected_recommendation' =>
                    null,

                'passenger_count' =>
                    count(
                        $passengers
                    ),

                'room_count' =>
                    0,

                'nights' =>
                    $nights,

                'service_date' =>
                    $serviceDate,
            ]
        );
    }
}