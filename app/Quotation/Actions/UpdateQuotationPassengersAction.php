<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationPassenger;
use Illuminate\Support\Facades\Log;

class UpdateQuotationPassengersAction
{
    /**
     * Sincronizar los pasajeros de una cotización.
     *
     * Estrategia:
     *
     * - Passenger existente recibido:
     *   UPDATE sobre el mismo registro.
     *
     * - Passenger nuevo:
     *   INSERT.
     *
     * - Passenger existente que ya no viene:
     *   SOFT DELETE.
     *
     * Cada operación es procesada individualmente para que
     * HasHistory pueda registrar correctamente:
     *
     * - created
     * - updated
     * - deleted
     *
     * Además, al conservar los IDs y UUIDs existentes,
     * se mantiene la trazabilidad histórica de la entidad.
     */
    public function execute(
        Quotation $quotation,
        array $passengers
    ): void {

        Log::debug('receivedPassengerIds initial', $passengers);
        /*
        |--------------------------------------------------------------------------
        | 1. Obtener IDs recibidos
        |--------------------------------------------------------------------------
        |
        | Los pasajeros existentes vienen con ID.
        |
        | Los pasajeros nuevos no tienen ID.
        |
        */

        $receivedPassengerIds = collect($passengers)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();


        /*
        |--------------------------------------------------------------------------
        | 2. Detectar pasajeros eliminados
        |--------------------------------------------------------------------------
        |
        | Si un pasajero existe actualmente en BD pero su ID
        | ya no viene desde el frontend, significa que fue eliminado
        | de la cotización.
        |
        | NO hacemos DELETE físico.
        |
        | Usamos SoftDelete.
        |
        | Cada pasajero se elimina individualmente para disparar:
        |
        | - evento deleted de Eloquent
        | - HasHistory::recordDeletedHistory()
        |
        */

        Log::debug('receivedPassengerIds', $receivedPassengerIds);

        $passengersToDelete = $quotation
            ->passengers()
            ->when(
                !empty($receivedPassengerIds),
                fn ($query) =>
                    $query->whereNotIn(
                        'id',
                        $receivedPassengerIds
                    )
            )
            ->when(
                empty($receivedPassengerIds),
                fn ($query) =>
                    $query
            )
            ->get();


        foreach ($passengersToDelete as $passenger) {

            /*
            |--------------------------------------------------------------------------
            | Soft Delete
            |--------------------------------------------------------------------------
            |
            | Conserva:
            |
            | - id
            | - uuid
            | - datos originales
            |
            | Y establece:
            |
            | deleted_at
            |
            | Además HasHistory registra:
            |
            | old_value = snapshot antes de eliminar
            | new_value = null
            | action = deleted
            |
            */

            $passenger->delete();
        }


        /*
        |--------------------------------------------------------------------------
        | 3. Crear / actualizar pasajeros
        |--------------------------------------------------------------------------
        */

        foreach ($passengers as $passengerData) {

            /*
            |--------------------------------------------------------------------------
            | Obtener ID
            |--------------------------------------------------------------------------
            */

            $id = $passengerData['id'] ?? null;


            /*
            |--------------------------------------------------------------------------
            | Limpiar campos técnicos
            |--------------------------------------------------------------------------
            |
            | No permitimos modificar la identidad
            | ni la relación con la cotización.
            |
            */

            unset(
                //$passengerData['id'],
                $passengerData['uuid'],
                $passengerData['quotation_id']
            );


            /*
            |--------------------------------------------------------------------------
            | 4. Actualizar pasajero existente
            |--------------------------------------------------------------------------
            */

            if ($id) {

                /*
                |--------------------------------------------------------------------------
                | Buscar únicamente dentro de esta cotización
                |--------------------------------------------------------------------------
                |
                | Esto evita que un usuario pueda enviar
                | el ID de un pasajero perteneciente a otra
                | cotización y modificarlo.
                |
                */

                $passenger = $quotation
                    ->passengers()
                    ->where('id', $id)
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | El pasajero no pertenece a la cotización
                |--------------------------------------------------------------------------
                */

                if (!$passenger) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE
                |--------------------------------------------------------------------------
                |
                | Se conserva:
                |
                | - id
                | - uuid
                | - created_at
                |
                | Solo se modifican los campos recibidos.
                |
                | Eloquent ejecutará automáticamente:
                |
                | updated
                |
                | HasHistory registrará:
                |
                | field
                | old_value
                | new_value
                | action = updated
                |
                */

                $passenger->update(
                    $passengerData
                );

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | 5. Crear nuevo pasajero
            |--------------------------------------------------------------------------
            |
            | Solo se ejecuta cuando el frontend
            | no envía ID.
            |
            | Laravel genera:
            |
            | - id
            | - uuid (si el modelo utiliza HasUuids)
            |
            | HasHistory registra:
            |
            | action = created
            |
            */

            $quotation
                ->passengers()
                ->create(
                    $passengerData
                );
        }
    }
}