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
     * Toda la operación comparte:
     *
     * - batch_uuid
     * - root_entity_type
     * - root_entity_uuid
     *
     * Esto permite agrupar todos los cambios generados
     * durante una misma operación de actualización.
     *
     * Ejemplo:
     *
     * Quotation
     * ├── updated
     * ├── QuotationItinerary updated
     * ├── QuotationItem updated
     * ├── QuotationItem deleted
     * ├── QuotationItem created
     * └── QuotationPassenger updated
     *
     * Todos tendrán:
     *
     * batch_uuid = mismo UUID
     *
     * root_entity_type = Quotation
     *
     * root_entity_uuid = UUID de la cotización
     */
    public function execute( Quotation $quotation, array $data): Quotation
    {
        return DB::transaction(function () use ($quotation, $data) {

            /*
            |--------------------------------------------------------------------------
            | 1. Crear identificador de la operación
            |--------------------------------------------------------------------------
            |
            | Este UUID identifica exclusivamente esta operación
            | de actualización.
            |
            */

            $batchUuid = (string) Str::uuid();


            /*
            |--------------------------------------------------------------------------
            | 2. Registrar contexto de auditoría
            |--------------------------------------------------------------------------
            |
            | root_entity_uuid ya existe porque estamos actualizando
            | una cotización existente.
            |
            */

            app()->instance(
                'history.batch_uuid',
                $batchUuid
            );

            try {

                /*
                |--------------------------------------------------------------------------
                | 3. Actualizar cabecera
                |--------------------------------------------------------------------------
                */

                $quotation = $this->headerAction->execute(
                    $quotation,
                    $data
                );


                /*
                |--------------------------------------------------------------------------
                | 4. Sincronizar itinerarios + items
                |--------------------------------------------------------------------------
                |
                | Esta acción debe:
                |
                | - Actualizar registros existentes.
                | - Crear únicamente registros realmente nuevos.
                | - Aplicar SoftDelete a registros eliminados.
                |
                | Todos los cambios utilizarán el mismo:
                |
                | batch_uuid
                | root_entity_uuid
                |
                */

                $this->itineraryAction->execute(
                    $quotation,
                    $data['itineraries'] ?? []
                );


                /*
                |--------------------------------------------------------------------------
                | 5. Sincronizar pasajeros
                |--------------------------------------------------------------------------
                */

                $this->passengerAction->execute(
                    $quotation,
                    $data['passengers'] ?? []
                );


                /*
                |--------------------------------------------------------------------------
                | 6. Recalcular totales
                |--------------------------------------------------------------------------
                |
                | Si los totales cambian, HasHistory registrará:
                |
                | action = updated
                |
                | old_value = valor anterior
                | new_value = valor nuevo
                |
                */

                $quotation = $this->totalsAction->execute(
                    $quotation
                );


                /*
                |--------------------------------------------------------------------------
                | 7. Recargar cotización completa
                |--------------------------------------------------------------------------
                */

                return $quotation
                    ->fresh()
                    ->load([
                        'customer',
                        'currency',
                        //'priceList',
                        'status',

                        'itineraries',
                        'itineraries.items',

                        'passengers',
                        'passengers.passengerType',
                    ]);

            } finally {

                /*
                |--------------------------------------------------------------------------
                | 8. Limpiar contexto de auditoría
                |--------------------------------------------------------------------------
                |
                | Evita que otra operación reutilice accidentalmente
                | el mismo contexto.
                |
                */

                app()->forgetInstance(
                    'history.batch_uuid'
                );
            }
        });
    }
}