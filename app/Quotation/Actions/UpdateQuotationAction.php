<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use Illuminate\Support\Facades\DB;

class UpdateQuotationAction
{
    public function __construct(
        protected UpdateQuotationHeaderAction $headerAction,
        protected UpdateQuotationItinerariesAction $itineraryAction,
        protected UpdateQuotationPassengersAction $passengerAction,
        protected CalculateQuotationTotalsAction $totalsAction,
    ) {}

    public function execute(
        Quotation $quotation,
        array $data
    ): Quotation {
        return DB::transaction(function () use ($quotation, $data) {

            /*
            |--------------------------------------------------------------------------
            | 1. Actualizar cabecera
            |--------------------------------------------------------------------------
            */

            $quotation = $this->headerAction->execute(
                $quotation,
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | 2. Actualizar itinerarios + items
            |--------------------------------------------------------------------------
            */

            $this->itineraryAction->execute(
                $quotation,
                $data['itineraries'] ?? []
            );

            /*
            |--------------------------------------------------------------------------
            | 3. Actualizar pasajeros
            |--------------------------------------------------------------------------
            */

            $this->passengerAction->execute(
                $quotation,
                $data['passengers'] ?? []
            );

            /*
            |--------------------------------------------------------------------------
            | 4. Recalcular totales
            |--------------------------------------------------------------------------
            */

            $quotation = $this->totalsAction->execute(
                $quotation->fresh()
            );

            /*
            |--------------------------------------------------------------------------
            | 5. Devolver cotización completa
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
        });
    }
}