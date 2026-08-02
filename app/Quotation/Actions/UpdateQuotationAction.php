<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateQuotationAction
{
    public function __construct(
        protected UpdateQuotationHeaderAction $headerAction,
        protected UpdateQuotationItinerariesAction $itineraryAction,
        protected UpdateQuotationPassengersAction $passengerAction,
        protected CalculateQuotationTotalsAction $totalsAction,
    ) {}

    /**
     * Actualizar una cotización existente.
     *
     * La actualización se realiza dentro de una única transacción.
     *
     * Todas las modificaciones realizadas sobre:
     *
     * - Quotation
     * - QuotationItinerary
     * - QuotationItem
     * - QuotationPassenger
     *
     * quedan asociadas al mismo batch_uuid para poder reconstruir
     * una operación completa en el historial.
     *
     * Los registros existentes se actualizan utilizando su ID.
     * Los registros que desaparecen de la petición se eliminan
     * mediante SoftDelete.
     *
     * No se eliminan físicamente ni se recrean registros existentes.
     */
    public function execute(
        Quotation $quotation,
        array $data
    ): Quotation {

        return DB::transaction(function () use ($quotation, $data) {

            /*
            |--------------------------------------------------------------------------
            | 0. Crear batch de auditoría
            |--------------------------------------------------------------------------
            |
            | Todas las acciones ejecutadas durante esta actualización
            | utilizarán el mismo UUID.
            |
            | Ejemplo:
            |
            | batch_uuid = abc-123
            |
            | Quotation          updated
            | QuotationItinerary  updated
            | QuotationItem       deleted
            | QuotationItem       updated
            | QuotationPassenger  created
            |
            | Esto permite agrupar todos los cambios de una misma operación.
            |
            */

            $batchUuid = (string) Str::uuid();

            app()->instance(
                'history.batch_uuid',
                $batchUuid
            );

            try {

                /*
                |--------------------------------------------------------------------------
                | 1. Actualizar cabecera
                |--------------------------------------------------------------------------
                |
                | Solo se actualizan los campos que hayan cambiado.
                |
                | El modelo Quotation con HasHistory registrará:
                |
                | old_value
                | new_value
                | field
                | action = updated
                |
                */

                $quotation = $this->headerAction->execute(
                    $quotation,
                    $data
                );

                /*
                |--------------------------------------------------------------------------
                | 2. Sincronizar itinerarios
                |--------------------------------------------------------------------------
                |
                | La acción debe encargarse de:
                |
                | - Actualizar itinerarios existentes por ID.
                | - Crear únicamente itinerarios realmente nuevos.
                | - Aplicar SoftDelete a itinerarios eliminados.
                | - Registrar eliminación en History.
                | - Sincronizar los items de cada itinerario.
                |
                */

                $this->itineraryAction->execute(
                    $quotation,
                    $data['itineraries'] ?? []
                );

                /*
                |--------------------------------------------------------------------------
                | 3. Sincronizar pasajeros
                |--------------------------------------------------------------------------
                |
                | La acción debe:
                |
                | - Actualizar pasajeros existentes por ID.
                | - Crear únicamente pasajeros nuevos.
                | - Aplicar SoftDelete a pasajeros eliminados.
                | - Registrar cambios en History.
                |
                */

                $this->passengerAction->execute(
                    $quotation,
                    $data['passengers'] ?? []
                );

                /*
                |--------------------------------------------------------------------------
                | 4. Recalcular totales
                |--------------------------------------------------------------------------
                |
                | El cálculo debe ejecutarse después de sincronizar
                | itinerarios e items.
                |
                | Si subtotal, discount, tax o total cambian,
                | HasHistory registrará también la modificación.
                |
                */

                $quotation = $this->totalsAction->execute(
                    $quotation
                );

                /*
                |--------------------------------------------------------------------------
                | 5. Recargar cotización completa
                |--------------------------------------------------------------------------
                |
                | Se devuelve el estado final de la cotización.
                |
                | Los registros eliminados mediante SoftDelete no aparecerán
                | en las relaciones normales.
                |
                */

                return $quotation
                    ->fresh()
                    ->load([
                        'customer',
                        'currency',
                        'priceList',
                        'status',

                        'itineraries',
                        'itineraries.items',

                        'passengers',
                        'passengers.passengerType',
                    ]);

            } finally {

                /*
                |--------------------------------------------------------------------------
                | 6. Limpiar contexto de auditoría
                |--------------------------------------------------------------------------
                |
                | Evita que una operación posterior reutilice accidentalmente
                | el mismo batch_uuid.
                |
                */

                app()->forgetInstance(
                    'history.batch_uuid'
                );
            }
        });
    }
}