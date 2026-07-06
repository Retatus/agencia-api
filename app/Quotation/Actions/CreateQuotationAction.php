<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use Illuminate\Support\Facades\DB;

class CreateQuotationAction
{
    public function __construct(
        protected CreateQuotationHeaderAction $headerAction,
        protected CreateQuotationPassengersAction $passengersAction,
        protected CreateQuotationItemsAction $itemsAction,
        protected CalculateQuotationTotalsAction $totalsAction
    ) {
    }

    /**
     * Crear una cotización completa.
     */
    public function execute(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Cabecera
            |--------------------------------------------------------------------------
            */

            $quotation = $this->headerAction->execute($data);

            /*
            |--------------------------------------------------------------------------
            | Pasajeros
            |--------------------------------------------------------------------------
            */

            $this->passengersAction->execute(
                $quotation,
                $data['passengers']
            );

            /*
            |--------------------------------------------------------------------------
            | Servicios
            |--------------------------------------------------------------------------
            */

            $this->itemsAction->execute(
                $quotation,
                $data['items']
            );

            /*
            |--------------------------------------------------------------------------
            | Totales
            |--------------------------------------------------------------------------
            */

            $quotation = $this->totalsAction->execute(
                $quotation
            );

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            return $quotation->load([

                'customer',

                'currency',

                'priceList',

                'status',

                'passengers.passengerType',

                'items.serviceVariant.service',

                'items.price.priceType',

            ]);

        });
    }
}