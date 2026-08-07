<?php

namespace App\Quotation\Calculation\Services;

class RoomAllocator
{
    /**
     * Obtener las mejores distribuciones posibles.
     *
     * @param array $passengers
     * @param array $roomTypes
     * @param int $limit
     *
     * @return array
     */
    public function recommend(
        array $passengers,
        array $roomTypes,
        int $limit = 5
    ): array {

        $passengerCount = count($passengers);

        if ($passengerCount === 0 || empty($roomTypes)) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Normalizar habitaciones
        |--------------------------------------------------------------------------
        */

        $rooms = collect($roomTypes)
            ->map(function ($room) {
                return [
                    'id' =>
                        $room['id'] ?? null,

                    'service_variant_id' =>
                        $room['service_variant_id']
                        ?? $room['id']
                        ?? null,

                    'name' =>
                        $room['name']
                        ?? 'Habitación',

                    'min_capacity' =>
                        (int) ($room['min_capacity'] ?? 1),

                    'max_capacity' =>
                        (int) ($room['max_capacity'] ?? 1),

                    'unit_cost' =>
                        (float) (
                            $room['unit_cost']
                            ?? $room['cost']
                            ?? 0
                        ),

                    'unit_price' =>
                        (float) (
                            $room['unit_price']
                            ?? $room['sale_price']
                            ?? 0
                        ),
                ];
            })
            ->filter(
                fn ($room) => $room['max_capacity'] > 0
            )
            ->sortByDesc('max_capacity')
            ->values()
            ->all();

        if (empty($rooms)) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Generar combinaciones
        |--------------------------------------------------------------------------
        */

        $combinations = [];

        $this->search(
            rooms: $rooms,
            remainingPassengers: $passengerCount,
            passengerCount: $passengerCount,
            index: 0,
            currentCombination: [],
            combinations: $combinations
        );

        /*
        |--------------------------------------------------------------------------
        | Eliminar combinaciones duplicadas
        |--------------------------------------------------------------------------
        */

        $combinations = $this->uniqueCombinations(
            $combinations
        );

        /*
        |--------------------------------------------------------------------------
        | Ordenar de mejor a peor
        |--------------------------------------------------------------------------
        */

        usort(
            $combinations,
            fn ($a, $b) => $this->compare($a, $b)
        );

        /*
        |--------------------------------------------------------------------------
        | Limitar cantidad de recomendaciones
        |--------------------------------------------------------------------------
        */

        $combinations = array_slice(
            $combinations,
            0,
            $limit
        );

        /*
        |--------------------------------------------------------------------------
        | Ranking
        |--------------------------------------------------------------------------
        */

        return collect($combinations)
            ->values()
            ->map(function ($combination, $index) {

                $combination['rank'] =
                    $index + 1;

                $combination['recommended'] =
                    $index === 0;

                return $combination;
            })
            ->all();
    }

    /**
     * Buscar recursivamente combinaciones posibles.
     */
    protected function search(
        array $rooms,
        int $remainingPassengers,
        int $passengerCount,
        int $index,
        array $currentCombination,
        array &$combinations
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Combinación válida
        |--------------------------------------------------------------------------
        */

        if ($remainingPassengers <= 0) {

            $totalCapacity = collect($currentCombination)
                ->sum(
                    fn ($allocation) =>
                        $allocation['quantity']
                        * $allocation['room']['max_capacity']
                );

            $totalRooms = collect($currentCombination)
                ->sum('quantity');

            $totalCost = collect($currentCombination)
                ->sum(
                    fn ($allocation) =>
                        $allocation['quantity']
                        * $allocation['room']['unit_cost']
                );

            $totalSale = collect($currentCombination)
                ->sum(
                    fn ($allocation) =>
                        $allocation['quantity']
                        * $allocation['room']['unit_price']
                );

            $unusedCapacity =
                $totalCapacity - $passengerCount;

            /*
            |--------------------------------------------------------------------------
            | Detalle de habitaciones
            |--------------------------------------------------------------------------
            */

            $roomAllocation = collect($currentCombination)
                ->map(function ($allocation) {

                    $room = $allocation['room'];

                    $quantity =
                        (int) $allocation['quantity'];

                    return [
                        'service_variant_id' =>
                            $room['service_variant_id'],

                        'name' =>
                            $room['name'],

                        'quantity' =>
                            $quantity,

                        'capacity_per_room' =>
                            $room['max_capacity'],

                        'total_capacity' =>
                            $quantity
                            * $room['max_capacity'],

                        'unit_cost' =>
                            $room['unit_cost'],

                        'unit_price' =>
                            $room['unit_price'],

                        'subtotal_cost' =>
                            $quantity
                            * $room['unit_cost'],

                        'subtotal_sale' =>
                            $quantity
                            * $room['unit_price'],
                    ];
                })
                ->values()
                ->all();

            $combinations[] = [
                'rooms' =>
                    $roomAllocation,

                'total_capacity' =>
                    $totalCapacity,

                'unused_capacity' =>
                    $unusedCapacity,

                'total_rooms' =>
                    $totalRooms,

                'total_cost' =>
                    $totalCost,

                'total_sale' =>
                    $totalSale,
            ];

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Fin de tipos disponibles
        |--------------------------------------------------------------------------
        */

        if ($index >= count($rooms)) {
            return;
        }

        $room = $rooms[$index];

        $capacity = $room['max_capacity'];

        /*
        |--------------------------------------------------------------------------
        | Máximo necesario de este tipo
        |--------------------------------------------------------------------------
        */

        $maxQuantity = (int) ceil(
            $remainingPassengers / $capacity
        );

        /*
        |--------------------------------------------------------------------------
        | Explorar cantidades
        |--------------------------------------------------------------------------
        */

        for (
            $quantity = $maxQuantity;
            $quantity >= 0;
            $quantity--
        ) {

            $nextCombination =
                $currentCombination;

            if ($quantity > 0) {
                $nextCombination[] = [
                    'room' => $room,
                    'quantity' => $quantity,
                ];
            }

            $this->search(
                rooms: $rooms,

                remainingPassengers:
                    $remainingPassengers
                    - ($quantity * $capacity),

                passengerCount:
                    $passengerCount,

                index:
                    $index + 1,

                currentCombination:
                    $nextCombination,

                combinations:
                    $combinations
            );
        }
    }

    /**
     * Ordenar de mejor a peor.
     */
    protected function compare(
        array $a,
        array $b
    ): int {

        /*
        |--------------------------------------------------------------------------
        | 1. Menor capacidad sobrante
        |--------------------------------------------------------------------------
        */

        if (
            $a['unused_capacity']
            !== $b['unused_capacity']
        ) {
            return
                $a['unused_capacity']
                <=> $b['unused_capacity'];
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Menor costo
        |--------------------------------------------------------------------------
        */

        if (
            $a['total_cost']
            !== $b['total_cost']
        ) {
            return
                $a['total_cost']
                <=> $b['total_cost'];
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Menor cantidad de habitaciones
        |--------------------------------------------------------------------------
        */

        return
            $a['total_rooms']
            <=> $b['total_rooms'];
    }

    /**
     * Eliminar distribuciones equivalentes.
     */
    protected function uniqueCombinations(
        array $combinations
    ): array {

        $unique = [];

        foreach ($combinations as $combination) {

            $parts = collect($combination['rooms'])
                ->sortBy('service_variant_id')
                ->map(
                    fn ($room) =>
                        $room['service_variant_id']
                        . ':'
                        . $room['quantity']
                )
                ->values()
                ->all();

            $key = implode('|', $parts);

            $unique[$key] = $combination;
        }

        return array_values($unique);
    }
}