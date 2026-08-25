<?php

namespace App\Quotation\Calculation\Services;

class VehicleAllocator
{
    /**
     * Obtener las mejores combinaciones de vehículos.
     *
     * Criterios actuales:
     *
     * 1. Cubrir a todos los pasajeros.
     * 2. Menor capacidad sobrante.
     * 3. Menor costo total.
     * 4. Menor cantidad de vehículos.
     *
     * No consulta base de datos.
     *
     * @param array $passengers
     * @param array $vehicleTypes
     * @param int   $limit
     *
     * @return array
     */
    public function recommend(
        array $passengers,
        array $vehicleTypes,
        int $limit = 5
    ): array {

        $passengerCount = count($passengers);

        if (
            $passengerCount === 0
            || empty($vehicleTypes)
        ) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Normalizar vehículos
        |--------------------------------------------------------------------------
        */

        $vehicles = collect($vehicleTypes)
            ->map(function ($vehicle) {

                return [

                    'id' =>
                        $vehicle['id'] ?? null,

                    'service_variant_id' =>
                        $vehicle['service_variant_id']
                        ?? $vehicle['id']
                        ?? null,

                    'name' =>
                        $vehicle['name']
                        ?? 'Vehículo',

                    /*
                    |--------------------------------------------------------------------------
                    | Capacidad
                    |--------------------------------------------------------------------------
                    */

                    'min_capacity' =>
                        (int) (
                            $vehicle['min_capacity']
                            ?? 1
                        ),

                    'max_capacity' =>
                        (int) (
                            $vehicle['max_capacity']
                            ?? 1
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | Valores económicos
                    |--------------------------------------------------------------------------
                    */

                    'unit_cost' =>
                        (float) (
                            $vehicle['unit_cost']
                            ?? $vehicle['cost']
                            ?? 0
                        ),

                    'unit_price' =>
                        (float) (
                            $vehicle['unit_price']
                            ?? $vehicle['sale_price']
                            ?? 0
                        ),
                ];
            })
            ->filter(
                fn ($vehicle) =>
                    $vehicle['max_capacity'] > 0
            )
            ->sortByDesc('max_capacity')
            ->values()
            ->all();

        if (empty($vehicles)) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Generar combinaciones
        |--------------------------------------------------------------------------
        */

        $combinations = [];

        $this->search(
            vehicles: $vehicles,
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
        | Ordenar recomendaciones
        |--------------------------------------------------------------------------
        */

        usort(
            $combinations,
            fn ($a, $b) =>
                $this->compare(
                    $a,
                    $b
                )
        );

        /*
        |--------------------------------------------------------------------------
        | Limitar recomendaciones
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
            ->map(function (
                $combination,
                $index
            ) {

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
        array $vehicles,
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

            /*
            |--------------------------------------------------------------------------
            | Capacidad total
            |--------------------------------------------------------------------------
            */

            $totalCapacity =
                collect($currentCombination)
                    ->sum(
                        fn ($allocation) =>
                            $allocation['quantity']
                            * $allocation['vehicle']['max_capacity']
                    );

            /*
            |--------------------------------------------------------------------------
            | Cantidad total de vehículos
            |--------------------------------------------------------------------------
            */

            $totalVehicles =
                collect($currentCombination)
                    ->sum('quantity');

            /*
            |--------------------------------------------------------------------------
            | Capacidad sobrante
            |--------------------------------------------------------------------------
            */

            $unusedCapacity =
                $totalCapacity
                - $passengerCount;

            /*
            |--------------------------------------------------------------------------
            | Totales económicos
            |--------------------------------------------------------------------------
            */

            $totalCost =
                collect($currentCombination)
                    ->sum(
                        fn ($allocation) =>
                            $allocation['quantity']
                            * $allocation['vehicle']['unit_cost']
                    );

            $totalSale =
                collect($currentCombination)
                    ->sum(
                        fn ($allocation) =>
                            $allocation['quantity']
                            * $allocation['vehicle']['unit_price']
                    );

            /*
            |--------------------------------------------------------------------------
            | Detalle de vehículos
            |--------------------------------------------------------------------------
            */

            $vehicleAllocation =
                collect($currentCombination)
                    ->map(function ($allocation) {

                        $vehicle =
                            $allocation['vehicle'];

                        $quantity =
                            (int) $allocation['quantity'];

                        return [

                            'service_variant_id' =>
                                $vehicle['service_variant_id'],

                            'name' =>
                                $vehicle['name'],

                            'quantity' =>
                                $quantity,

                            /*
                            |--------------------------------------------------------------------------
                            | Capacidad
                            |--------------------------------------------------------------------------
                            */

                            'capacity_per_vehicle' =>
                                $vehicle['max_capacity'],

                            'total_capacity' =>
                                $quantity
                                * $vehicle['max_capacity'],

                            /*
                            |--------------------------------------------------------------------------
                            | Valores unitarios
                            |--------------------------------------------------------------------------
                            */

                            'unit_cost' =>
                                $vehicle['unit_cost'],

                            'unit_price' =>
                                $vehicle['unit_price'],

                            /*
                            |--------------------------------------------------------------------------
                            | Subtotales
                            |--------------------------------------------------------------------------
                            */

                            'subtotal_cost' =>
                                $quantity
                                * $vehicle['unit_cost'],

                            'subtotal_sale' =>
                                $quantity
                                * $vehicle['unit_price'],
                        ];
                    })
                    ->values()
                    ->all();

            /*
            |--------------------------------------------------------------------------
            | Registrar combinación
            |--------------------------------------------------------------------------
            */

            $combinations[] = [

                'vehicles' =>
                    $vehicleAllocation,

                'total_capacity' =>
                    $totalCapacity,

                'unused_capacity' =>
                    $unusedCapacity,

                'total_vehicles' =>
                    $totalVehicles,

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

        if ($index >= count($vehicles)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Vehículo actual
        |--------------------------------------------------------------------------
        */

        $vehicle =
            $vehicles[$index];

        $capacity =
            $vehicle['max_capacity'];

        /*
        |--------------------------------------------------------------------------
        | Máxima cantidad necesaria
        |--------------------------------------------------------------------------
        |
        | Ejemplo:
        |
        | 27 pasajeros
        | vehículo capacidad 15
        |
        | ceil(27 / 15) = 2
        |
        */

        $maxQuantity =
            (int) ceil(
                $remainingPassengers
                / $capacity
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

                    'vehicle' =>
                        $vehicle,

                    'quantity' =>
                        $quantity,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Pasajeros cubiertos
            |--------------------------------------------------------------------------
            */

            $covered =
                $quantity
                * $capacity;

            /*
            |--------------------------------------------------------------------------
            | Continuar búsqueda
            |--------------------------------------------------------------------------
            */

            $this->search(

                vehicles:
                    $vehicles,

                remainingPassengers:
                    $remainingPassengers
                    - $covered,

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
     * Ordenar una combinación respecto a otra.
     *
     * Prioridades:
     *
     * 1. Menor costo total.
     * 2. Menor cantidad de vehículos.
     * 3. Menor capacidad sobrante.
     */
    protected function compare(
        array $a,
        array $b
    ): int {

        /*
        |--------------------------------------------------------------------------
        | 1. Costo total
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
        | 2. Cantidad de vehículos
        |--------------------------------------------------------------------------
        */

        if (
            $a['total_vehicles']
            !== $b['total_vehicles']
        ) {
            return
                $a['total_vehicles']
                <=> $b['total_vehicles'];
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Capacidad sobrante
        |--------------------------------------------------------------------------
        */

        return
            $a['unused_capacity']
            <=> $b['unused_capacity'];
    }

    /**
     * Eliminar combinaciones equivalentes.
     */
    protected function uniqueCombinations(
        array $combinations
    ): array {

        $unique = [];

        foreach ($combinations as $combination) {

            $parts =
                collect($combination['vehicles'])
                    ->sortBy('service_variant_id')
                    ->map(
                        fn ($vehicle) =>
                            $vehicle['service_variant_id']
                            . ':'
                            . $vehicle['quantity']
                    )
                    ->values()
                    ->all();

            $key =
                implode('|', $parts);

            $unique[$key] =
                $combination;
        }

        return array_values(
            $unique
        );
    }
}