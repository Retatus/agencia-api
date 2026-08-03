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
     * Toda la creación se ejecuta dentro de una única transacción
     * y todos los registros creados durante la operación comparten
     * el mismo batch_uuid.
     *
     * Esto permite agrupar en auditoría:
     *
     * - Quotation
     * - QuotationItinerary
     * - QuotationItem
     * - QuotationPassenger
     *
     * como una única operación de negocio.
     */
    public function execute(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | 0. Crear batch de auditoría
            |--------------------------------------------------------------------------
            |
            | Este UUID identifica toda la operación de creación.
            |
            | Todos los eventos HasHistory generados durante esta transacción
            | utilizarán este mismo batch_uuid.
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
                | 1. Crear cabecera
                |--------------------------------------------------------------------------
                |
                | HasHistory:
                |
                | Quotation
                | action = created
                | batch_uuid = $batchUuid
                |
                */

                $quotation = $this->headerAction->execute(
                    $data
                );


                /*
                |--------------------------------------------------------------------------
                | 2. Crear itinerarios + items
                |--------------------------------------------------------------------------
                |
                | HasHistory registrará:
                |
                | QuotationItinerary
                | action = created
                | batch_uuid = $batchUuid
                |
                | QuotationItem
                | action = created
                | batch_uuid = $batchUuid
                |
                */

                $this->itineraryAction->execute(
                    $quotation,
                    $data['itineraries'] ?? []
                );


                /*
                |--------------------------------------------------------------------------
                | 3. Crear pasajeros
                |--------------------------------------------------------------------------
                |
                | HasHistory registrará:
                |
                | QuotationPassenger
                | action = created
                | batch_uuid = $batchUuid
                |
                */

                $this->passengerAction->execute(
                    $quotation,
                    $data['passengers'] ?? []
                );


                /*
                |--------------------------------------------------------------------------
                | 4. Calcular totales
                |--------------------------------------------------------------------------
                |
                | Si el cálculo modifica los totales de la cotización,
                | HasHistory registrará esos cambios utilizando el mismo
                | batch_uuid.
                |
                */

                $quotation = $this->totalsAction->execute(
                    $quotation
                );


                /*
                |--------------------------------------------------------------------------
                | 5. Recargar cotización completa
                |--------------------------------------------------------------------------
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
                | Evita que el batch_uuid se reutilice accidentalmente
                | en una operación posterior dentro del mismo request.
                |
                */

                app()->forgetInstance(
                    'history.batch_uuid'
                );
            }
        });
    }
}