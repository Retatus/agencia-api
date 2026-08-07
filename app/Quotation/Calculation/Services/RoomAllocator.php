<?php

namespace App\Quotation\Calculation\Services;

class RoomAllocator
{
    /**
     * Sugerir una distribución de habitaciones.
     *
     * Criterios actuales:
     *
     * 1. Cubrir a todos los pasajeros.
     * 2. Minimizar capacidad sobrante.
     * 3. En caso de empate, minimizar cantidad de habitaciones.
     *
     * Esta clase NO consulta base de datos.
     * Trabaja únicamente con los tipos de habitación recibidos.
     *
     * @param array $passengers
     * @param array $roomTypes
     *
     * @return array
     */
    public function allocate(
        array $passengers,
        array $roomTypes
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Cantidad de pasajeros
        |--------------------------------------------------------------------------
        */

        $passengerCount = count($passengers);

        if ($passengerCount === 0) {
            return [];
        }

        if (empty($roomTypes)) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Normalizar tipos de habitación
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
                        (float) (
                            $room['unit_cost']
                            ?? 0
                        ),

                    'unit_price' =>
                        (float) (
                            $room['unit_price']
                            ?? 0
                        ),
                ];
            })

            /*
            |--------------------------------------------------------------------------
            | Eliminar habitaciones inválidas
            |--------------------------------------------------------------------------
            */

            ->filter(
                fn ($room) =>
                    $room['max_capacity'] > 0
            )

            /*
            |--------------------------------------------------------------------------
            | Ordenar por mayor capacidad
            |--------------------------------------------------------------------------
            |
            | Esto ayuda a encontrar rápidamente combinaciones razonables,
            | aunque igualmente evaluamos todas las combinaciones posibles.
            |
            */

            ->sortByDesc('max_capacity')

            ->values()

            ->all();

        if (empty($rooms)) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Buscar mejor combinación
        |--------------------------------------------------------------------------
        */

        $bestCombination = null;

        $this->search(
            rooms: $rooms,
            remainingPassengers: $passengerCount,
            index: 0,
            currentCombination: [],
            bestCombination: $bestCombination
        );

        /*
        |--------------------------------------------------------------------------
        | Sin combinación posible
        |--------------------------------------------------------------------------
        */

        if ($bestCombination === null) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Formatear resultado
        |--------------------------------------------------------------------------
        */

        return collect(
            $bestCombination['rooms']
        )
            ->map(function ($allocation) {

                $room = $allocation['room'];

                $quantity =
                    (int) $allocation['quantity'];

                $unitCost =
                    (float) $room['unit_cost'];

                $unitPrice =
                    (float) $room['unit_price'];

                return [

                    'service_variant_id' =>
                        $room['service_variant_id'],

                    'name' =>
                        $room['name'],

                    /*
                    |--------------------------------------------------------------------------
                    | Habitaciones
                    |--------------------------------------------------------------------------
                    */

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
                    | Precio unitario
                    |--------------------------------------------------------------------------
                    */

                    'unit_cost' =>
                        $unitCost,

                    'unit_price' =>
                        $unitPrice,

                    /*
                    |--------------------------------------------------------------------------
                    | Subtotales
                    |--------------------------------------------------------------------------
                    */

                    'subtotal_cost' =>
                        $quantity
                        * $unitCost,

                    'subtotal_sale' =>
                        $quantity
                        * $unitPrice,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Buscar recursivamente todas las combinaciones posibles.
     */
    protected function search(
        array $rooms,
        int $remainingPassengers,
        int $index,
        array $currentCombination,
        ?array &$bestCombination
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Todos los pasajeros están cubiertos
        |--------------------------------------------------------------------------
        |
        | remainingPassengers puede ser:
        |
        |  0 = capacidad exacta
        | -1 = sobra 1 plaza
        | -2 = sobran 2 plazas
        |
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
                            * $allocation['room']['max_capacity']
                    );

            /*
            |--------------------------------------------------------------------------
            | Cantidad total de habitaciones
            |--------------------------------------------------------------------------
            */

            $totalRooms =
                collect($currentCombination)
                    ->sum('quantity');

            /*
            |--------------------------------------------------------------------------
            | Capacidad sobrante
            |--------------------------------------------------------------------------
            */

            $unusedCapacity =
                abs($remainingPassengers);

            /*
            |--------------------------------------------------------------------------
            | Costo total
            |--------------------------------------------------------------------------
            |
            | Todavía NO lo usamos para elegir la mejor combinación,
            | pero lo calculamos porque nos servirá en el siguiente paso.
            |
            */

            $totalCost =
                collect($currentCombination)
                    ->sum(
                        fn ($allocation) =>
                            $allocation['quantity']
                            * $allocation['room']['unit_cost']
                    );

            /*
            |--------------------------------------------------------------------------
            | Venta total
            |--------------------------------------------------------------------------
            */

            $totalSale =
                collect($currentCombination)
                    ->sum(
                        fn ($allocation) =>
                            $allocation['quantity']
                            * $allocation['room']['unit_price']
                    );

            /*
            |--------------------------------------------------------------------------
            | Candidato
            |--------------------------------------------------------------------------
            */

            $candidate = [

                'rooms' =>
                    $currentCombination,

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

            /*
            |--------------------------------------------------------------------------
            | Comparar con mejor combinación
            |--------------------------------------------------------------------------
            */

            if (
                $bestCombination === null
                || $this->isBetter(
                    $candidate,
                    $bestCombination
                )
            ) {

                $bestCombination =
                    $candidate;
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Ya no quedan tipos de habitación
        |--------------------------------------------------------------------------
        */

        if ($index >= count($rooms)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Habitación actual
        |--------------------------------------------------------------------------
        */

        $room = $rooms[$index];

        $capacity =
            $room['max_capacity'];

        /*
        |--------------------------------------------------------------------------
        | Máxima cantidad necesaria
        |--------------------------------------------------------------------------
        |
        | Ejemplo:
        |
        | 7 pasajeros
        | habitación triple
        |
        | ceil(7 / 3) = 3
        |
        */

        $maxQuantity =
            (int) ceil(
                $remainingPassengers
                / $capacity
            );

        /*
        |--------------------------------------------------------------------------
        | Probar todas las cantidades posibles
        |--------------------------------------------------------------------------
        */

        for (
            $quantity = $maxQuantity;
            $quantity >= 0;
            $quantity--
        ) {

            $nextCombination =
                $currentCombination;

            /*
            |--------------------------------------------------------------------------
            | Agregar habitación
            |--------------------------------------------------------------------------
            */

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

                rooms:
                    $rooms,

                remainingPassengers:
                    $remainingPassengers
                    - $covered,

                index:
                    $index + 1,

                currentCombination:
                    $nextCombination,

                bestCombination:
                    $bestCombination
            );
        }
    }

    /**
     * Determinar si una combinación es mejor que otra.
     *
     * Criterios actuales:
     *
     * 1. Menor capacidad sobrante.
     * 2. Menor cantidad de habitaciones.
     *
     * En el siguiente paso podremos agregar:
     *
     * 3. Menor costo.
     */
    /**
   * Determinar si una combinación es mejor que otra.
   *
   * Prioridades:
   *
   * 1. Menor capacidad sobrante.
   * 2. Menor costo total.
   * 3. Menor cantidad de habitaciones.
   */
  protected function isBetter(
      array $candidate,
      array $currentBest
  ): bool {

      /*
      |--------------------------------------------------------------------------
      | 1. Menor capacidad sobrante
      |--------------------------------------------------------------------------
      */

      if (
          $candidate['unused_capacity']
          < $currentBest['unused_capacity']
      ) {
          return true;
      }

      if (
          $candidate['unused_capacity']
          > $currentBest['unused_capacity']
      ) {
          return false;
      }

      /*
      |--------------------------------------------------------------------------
      | 2. Menor costo
      |--------------------------------------------------------------------------
      |
      | Llegamos aquí cuando ambas combinaciones tienen
      | exactamente la misma capacidad sobrante.
      |
      */

      if (
          $candidate['total_cost']
          < $currentBest['total_cost']
      ) {
          return true;
      }

      if (
          $candidate['total_cost']
          > $currentBest['total_cost']
      ) {
          return false;
      }

      /*
      |--------------------------------------------------------------------------
      | 3. Menor cantidad de habitaciones
      |--------------------------------------------------------------------------
      */

      if (
          $candidate['total_rooms']
          < $currentBest['total_rooms']
      ) {
          return true;
      }

      return false;
  }
}