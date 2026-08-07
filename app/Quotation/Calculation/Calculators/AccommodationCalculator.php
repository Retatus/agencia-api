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

    public function calculate(array $item): CalculationResult
    {
        /*
        |--------------------------------------------------------------------------
        | Entrada
        |--------------------------------------------------------------------------
        */

        $passengers = $item['passengers'] ?? [];

        $roomTypes = $item['room_types'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Noches
        |--------------------------------------------------------------------------
        |
        | Para alojamiento utilizamos duration como cantidad de noches.
        |
        */

        $nights = max(
            1,
            (int) ($item['duration'] ?? 1)
        );

        /*
        |--------------------------------------------------------------------------
        | Obtener distribución óptima
        |--------------------------------------------------------------------------
        */

        $allocation = $this->roomAllocator->allocate(
            $passengers,
            $roomTypes
        );

        /*
        |--------------------------------------------------------------------------
        | Calcular totales
        |--------------------------------------------------------------------------
        */

        $totalRooms = 0;

        $totalCost = 0;

        $totalSale = 0;

        /*
        |--------------------------------------------------------------------------
        | Aplicar cantidad de noches
        |--------------------------------------------------------------------------
        */

        $allocation = collect($allocation)
            ->map(function ($room) use (
                $nights,
                &$totalRooms,
                &$totalCost,
                &$totalSale
            ) {

                $quantity = (int) $room['quantity'];

                $unitCost = (float) $room['unit_cost'];

                $unitPrice = (float) $room['unit_price'];

                /*
                |--------------------------------------------------------------------------
                | Subtotales por estadía
                |--------------------------------------------------------------------------
                */

                $subtotalCost =
                    $quantity
                    * $unitCost
                    * $nights;

                $subtotalSale =
                    $quantity
                    * $unitPrice
                    * $nights;

                $totalRooms += $quantity;

                $totalCost += $subtotalCost;

                $totalSale += $subtotalSale;

                /*
                |--------------------------------------------------------------------------
                | Actualizar detalle
                |--------------------------------------------------------------------------
                */

                $room['nights'] = $nights;

                $room['subtotal_cost'] = $subtotalCost;

                $room['subtotal_sale'] = $subtotalSale;

                return $room;
            })
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

        return new CalculationResult(
            item: $item,

            quantity: $totalRooms,

            unitCost: 0,

            unitPrice: 0,

            subtotalCost: $totalCost,

            subtotalSale: $totalSale,

            metadata: [

                'room_allocation' =>
                    $allocation,

                'passenger_count' =>
                    count($passengers),

                'room_count' =>
                    $totalRooms,

                'nights' =>
                    $nights,
            ]
        );
    }
}