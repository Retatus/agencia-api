<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateQuotationAction
{
    public function __construct(
        protected CreateQuotationHeaderAction $headerAction,
        protected CreateQuotationItinerariesAction $itineraryAction,
        protected CreateQuotationPassengersAction $passengerAction,
        protected CalculateQuotationTotalsAction $totalsAction,
    ) {}

    /**
     * Crear una nueva cotización.
     *
     * Toda la operación comparte:
     *
     * - batch_uuid
     * - root_entity_type
     * - root_entity_uuid
     *
     * Esto permite agrupar todos los registros de auditoría
     * *
     * - Quotation
     * - QuotationItinerary
     * - QuotationItem
     * - QuotationPassenger
     *
     * generados durante la creación de la cotización.
     */
    public function execute(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | 1. Identificadores de la operación
            |--------------------------------------------------------------------------
            */
            $batchUuid = (string) Str::uuid();


            /*
            |--------------------------------------------------------------------------
            | 2. Registrar contexto de auditoría
            |--------------------------------------------------------------------------
            */

            app()->instance(
                'history.batch_uuid',
                $batchUuid
            );

            try {

                /*
                |--------------------------------------------------------------------------
                | 3. Crear cabecera
                |--------------------------------------------------------------------------
                |
                | La cotización ya tiene UUID antes de ser creada.
                |
                | Por tanto, el evento "created" de HasHistory tendrá:
                |
                | batch_uuid
                | root_entity_type
                | root_entity_uuid
                |
                */

                $quotation = $this->headerAction->execute(
                    $data,
                );


                /*
                |--------------------------------------------------------------------------
                | 4. Crear itinerarios + items
                |--------------------------------------------------------------------------
                */

                $this->itineraryAction->execute(
                    $quotation,
                    $data['itineraries'] ?? []
                );


                /*
                |--------------------------------------------------------------------------
                | 5. Crear pasajeros
                |--------------------------------------------------------------------------
                */

                $this->passengerAction->execute(
                    $quotation,
                    $data['passengers'] ?? []
                );


                /*
                |--------------------------------------------------------------------------
                | 6. Calcular totales
                |--------------------------------------------------------------------------
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
                */

                app()->forgetInstance(
                    'history.batch_uuid'
                );
            }
        });
    }
}