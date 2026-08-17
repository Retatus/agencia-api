<?php

namespace App\Quotation\Calculation\Services;

use App\Pricing\Resolution\DTOs\PriceContext;
use App\Pricing\Resolution\Services\PriceResolver;
use Carbon\Carbon;

class VehicleAllocator
{
    public function __construct(
        protected PriceResolver $priceResolver
    ) {
    }

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
     * @param array       $passengers
     * @param array       $vehicleTypes
     * @param string|null $serviceDate
     * @param int         $limit
     *
     * @return array
     */
    public function recommend(
        array $passengers,
        array $vehicleTypes,
        ?string $serviceDate = null,
        int $limit = 5
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Cantidad de pasajeros
        |--------------------------------------------------------------------------
        */

        $passengerCount =
            count($passengers);

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
        |
        | Aquí resolvemos el precio aplicable por:
        |
        | - variante
        | - categoría
        | - fecha
        | - temporada
        |
        | mediante PriceResolver.
        |
        */

        $vehicles = collect($vehicleTypes)
            ->map(function ($vehicle) use ($serviceDate) {

                /*
                |--------------------------------------------------------------------------
                | Service Variant
                |--------------------------------------------------------------------------
                */

                $serviceVariantId =
                    $vehicle['service_variant_id']
                    ?? $vehicle['id']
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | Pricing
                |--------------------------------------------------------------------------
                */

                $pricing =
                    $this->resolveVehiclePrice(
                        serviceVariantId:
                            $serviceVariantId,

                        serviceDate:
                            $serviceDate,

                        vehicle:
                            $vehicle
                    );

                return [

                    /*
                    |--------------------------------------------------------------------------
                    | Identificación
                    |--------------------------------------------------------------------------
                    */

                    'id' =>
                        $vehicle['id']
                        ?? null,

                    'service_variant_id' =>
                        $serviceVariantId,

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
                        $pricing['unit_cost'],

                    'unit_price' =>
                        $pricing['unit_price'],

                    /*
                    |--------------------------------------------------------------------------
                    | Pricing Metadata
                    |--------------------------------------------------------------------------
                    */

                    'pricing_source' =>
                        $pricing['pricing_source'],

                    'base_price_id' =>
                        $pricing['base_price_id'],

                    'price_list_id' =>
                        $pricing['price_list_id'],

                    'price_list_item_id' =>
                        $pricing['price_list_item_id'],

                    'adjustment_type' =>
                        $pricing['adjustment_type'],

                    'adjustment_value' =>
                        $pricing['adjustment_value'],
                ];
            })

            /*
            |--------------------------------------------------------------------------
            | Variantes válidas
            |--------------------------------------------------------------------------
            */

            ->filter(
                fn ($vehicle) =>
                    $vehicle['service_variant_id'] !== null
                    && $vehicle['max_capacity'] > 0
            )

            /*
            |--------------------------------------------------------------------------
            | Primero vehículos de mayor capacidad
            |--------------------------------------------------------------------------
            */

            ->sortByDesc(
                'max_capacity'
            )

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
            vehicles:
                $vehicles,

            remainingPassengers:
                $passengerCount,

            passengerCount:
                $passengerCount,

            index:
                0,

            currentCombination:
                [],

            combinations:
                $combinations
        );

        /*
        |--------------------------------------------------------------------------
        | Eliminar combinaciones duplicadas
        |--------------------------------------------------------------------------
        */

        $combinations =
            $this->uniqueCombinations(
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

        $combinations =
            array_slice(
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
            ->map(
                function (
                    $combination,
                    $index
                ) {

                    $combination['rank'] =
                        $index + 1;

                    $combination['recommended'] =
                        $index === 0;

                    return $combination;
                }
            )
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Vehicle Price
    |--------------------------------------------------------------------------
    |
    | Nuevo Pricing:
    |
    | ServiceVariant
    |      ↓
    | BasePrice
    |      ↓
    | PriceList por categoría + fecha
    |      ↓
    | PriceListItem
    |      ↓
    | precio final
    |
    | Mientras terminamos la migración dejamos compatibilidad con
    | unit_cost / cost / unit_price / sale_price anteriores.
    |
    */

    protected function resolveVehiclePrice(
        ?int $serviceVariantId,
        ?string $serviceDate,
        array $vehicle
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Nuevo PriceResolver
        |--------------------------------------------------------------------------
        */

        if (
            $serviceVariantId !== null
            && $serviceDate !== null
        ) {
            $resolved =
                $this->priceResolver->resolve(
                    new PriceContext(
                        serviceVariantId:
                            $serviceVariantId,

                        date:
                            Carbon::parse(
                                $serviceDate
                            )
                    )
                );

            return [
                'unit_cost' =>
                    (float) $resolved->finalCost,

                'unit_price' =>
                    (float) $resolved->finalPrice,

                'pricing_source' =>
                    $resolved->pricingSource,

                'base_price_id' =>
                    $resolved->basePriceId,

                'price_list_id' =>
                    $resolved->priceListId,

                'price_list_item_id' =>
                    $resolved->priceListItemId,

                'adjustment_type' =>
                    $resolved->adjustmentType,

                'adjustment_value' =>
                    $resolved->adjustmentValue,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Legacy fallback
        |--------------------------------------------------------------------------
        |
        | Esto permite que durante la transición siga funcionando código
        | que todavía envíe precios precalculados.
        |
        */

        return [
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

            'pricing_source' =>
                'LEGACY',

            'base_price_id' =>
                null,

            'price_list_id' =>
                null,

            'price_list_item_id' =>
                null,

            'adjustment_type' =>
                null,

            'adjustment_value' =>
                null,
        ];
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
                    ->sum(
                        'quantity'
                    );

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
                    ->map(
                        function ($allocation) {

                            $vehicle =
                                $allocation['vehicle'];

                            $quantity =
                                (int) $allocation['quantity'];

                            return [

                                /*
                                |--------------------------------------------------------------------------
                                | Identificación
                                |--------------------------------------------------------------------------
                                */

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
                                | Pricing
                                |--------------------------------------------------------------------------
                                */

                                'pricing_source' =>
                                    $vehicle['pricing_source'],

                                'base_price_id' =>
                                    $vehicle['base_price_id'],

                                'price_list_id' =>
                                    $vehicle['price_list_id'],

                                'price_list_item_id' =>
                                    $vehicle['price_list_item_id'],

                                'adjustment_type' =>
                                    $vehicle['adjustment_type'],

                                'adjustment_value' =>
                                    $vehicle['adjustment_value'],

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
                        }
                    )
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

        if (
            $index >= count(
                $vehicles
            )
        ) {
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
     * 1. Menor capacidad sobrante.
     * 2. Menor costo.
     * 3. Menor cantidad de vehículos.
     */
    protected function compare(
        array $a,
        array $b
    ): int {

        /*
        |--------------------------------------------------------------------------
        | 1. Capacidad sobrante
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
        | 2. Costo
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
        | 3. Cantidad de vehículos
        |--------------------------------------------------------------------------
        */

        return
            $a['total_vehicles']
            <=> $b['total_vehicles'];
    }

    /**
     * Eliminar combinaciones equivalentes.
     */
    protected function uniqueCombinations(
        array $combinations
    ): array {

        $unique = [];

        foreach (
            $combinations
            as $combination
        ) {

            $parts =
                collect(
                    $combination['vehicles']
                )
                    ->sortBy(
                        'service_variant_id'
                    )
                    ->map(
                        fn ($vehicle) =>
                            $vehicle['service_variant_id']
                            . ':'
                            . $vehicle['quantity']
                    )
                    ->values()
                    ->all();

            $key =
                implode(
                    '|',
                    $parts
                );

            $unique[$key] =
                $combination;
        }

        return array_values(
            $unique
        );
    }
}