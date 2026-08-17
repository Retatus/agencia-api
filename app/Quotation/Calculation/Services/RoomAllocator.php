<?php

namespace App\Quotation\Calculation\Services;

use App\Pricing\Resolution\DTOs\PriceContext;
use App\Pricing\Resolution\Services\PriceResolver;
use Carbon\Carbon;

class RoomAllocator
{
    public function __construct(
        protected PriceResolver $priceResolver
    ) {
    }

    /**
     * Obtener las mejores distribuciones posibles.
     *
     * @param array $passengers
     * @param array $roomTypes
     * @param string|null $serviceDate
     * @param int $limit
     *
     * @return array
     */
    public function recommend(
        array $passengers,
        array $roomTypes,
        ?string $serviceDate = null,
        int $limit = 5
    ): array {

        $passengerCount =
            count($passengers);

        if (
            $passengerCount === 0
            || empty($roomTypes)
        ) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Normalizar habitaciones
        |--------------------------------------------------------------------------
        */

        $rooms = collect($roomTypes)
            ->map(function ($room) use ($serviceDate) {

                $serviceVariantId =
                    $room['service_variant_id']
                    ?? $room['id']
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | Resolver precio
                |--------------------------------------------------------------------------
                */

                $pricing =
                    $this->resolveRoomPrice(
                        serviceVariantId:
                            $serviceVariantId,

                        serviceDate:
                            $serviceDate,

                        room:
                            $room
                    );

                return [
                    'id' =>
                        $room['id']
                        ?? null,

                    'service_variant_id' =>
                        $serviceVariantId,

                    'name' =>
                        $room['name']
                        ?? 'Habitación',

                    /*
                    |--------------------------------------------------------------------------
                    | Capacidad
                    |--------------------------------------------------------------------------
                    */

                    'min_capacity' =>
                        (int) (
                            $room['min_capacity']
                            ?? 1
                        ),

                    'max_capacity' =>
                        (int) (
                            $room['max_capacity']
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
            ->filter(
                fn ($room) =>
                    $room['service_variant_id'] !== null
                    && $room['max_capacity'] > 0
            )
            ->sortByDesc(
                'max_capacity'
            )
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
            rooms:
                $rooms,

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
        | Ordenar de mejor a peor
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
    | Resolve Room Price
    |--------------------------------------------------------------------------
    */

    protected function resolveRoomPrice(
        ?int $serviceVariantId,
        ?string $serviceDate,
        array $room
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Nuevo Pricing
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
        */

        return [
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

            $totalCapacity =
                collect($currentCombination)
                    ->sum(
                        fn ($allocation) =>
                            $allocation['quantity']
                            * $allocation['room']['max_capacity']
                    );

            $totalRooms =
                collect($currentCombination)
                    ->sum(
                        'quantity'
                    );

            $totalCost =
                collect($currentCombination)
                    ->sum(
                        fn ($allocation) =>
                            $allocation['quantity']
                            * $allocation['room']['unit_cost']
                    );

            $totalSale =
                collect($currentCombination)
                    ->sum(
                        fn ($allocation) =>
                            $allocation['quantity']
                            * $allocation['room']['unit_price']
                    );

            $unusedCapacity =
                $totalCapacity
                - $passengerCount;

            /*
            |--------------------------------------------------------------------------
            | Detalle de habitaciones
            |--------------------------------------------------------------------------
            */

            $roomAllocation =
                collect($currentCombination)
                    ->map(
                        function ($allocation) {

                            $room =
                                $allocation['room'];

                            $quantity =
                                (int) $allocation['quantity'];

                            return [
                                'service_variant_id' =>
                                    $room['service_variant_id'],

                                'name' =>
                                    $room['name'],

                                'quantity' =>
                                    $quantity,

                                /*
                                |--------------------------------------------------------------------------
                                | Capacidad
                                |--------------------------------------------------------------------------
                                */

                                'capacity_per_room' =>
                                    $room['max_capacity'],

                                'total_capacity' =>
                                    $quantity
                                    * $room['max_capacity'],

                                /*
                                |--------------------------------------------------------------------------
                                | Valores económicos
                                |--------------------------------------------------------------------------
                                */

                                'unit_cost' =>
                                    $room['unit_cost'],

                                'unit_price' =>
                                    $room['unit_price'],

                                /*
                                |--------------------------------------------------------------------------
                                | Pricing
                                |--------------------------------------------------------------------------
                                */

                                'pricing_source' =>
                                    $room['pricing_source'],

                                'base_price_id' =>
                                    $room['base_price_id'],

                                'price_list_id' =>
                                    $room['price_list_id'],

                                'price_list_item_id' =>
                                    $room['price_list_item_id'],

                                'adjustment_type' =>
                                    $room['adjustment_type'],

                                'adjustment_value' =>
                                    $room['adjustment_value'],

                                /*
                                |--------------------------------------------------------------------------
                                | Subtotales
                                |--------------------------------------------------------------------------
                                */

                                'subtotal_cost' =>
                                    $quantity
                                    * $room['unit_cost'],

                                'subtotal_sale' =>
                                    $quantity
                                    * $room['unit_price'],
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

        if (
            $index >= count(
                $rooms
            )
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Habitación actual
        |--------------------------------------------------------------------------
        */

        $room =
            $rooms[$index];

        $capacity =
            $room['max_capacity'];

        /*
        |--------------------------------------------------------------------------
        | Máximo necesario de este tipo
        |--------------------------------------------------------------------------
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
                    'room' =>
                        $room,

                    'quantity' =>
                        $quantity,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Continuar búsqueda
            |--------------------------------------------------------------------------
            */

            $this->search(
                rooms:
                    $rooms,

                remainingPassengers:
                    $remainingPassengers
                    - (
                        $quantity
                        * $capacity
                    ),

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
     *
     * Prioridad:
     *
     * 1. Menor capacidad sobrante.
     * 2. Menor costo.
     * 3. Menor cantidad de habitaciones.
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

        foreach (
            $combinations
            as $combination
        ) {

            $parts =
                collect(
                    $combination['rooms']
                )
                    ->sortBy(
                        'service_variant_id'
                    )
                    ->map(
                        fn ($room) =>
                            $room['service_variant_id']
                            . ':'
                            . $room['quantity']
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