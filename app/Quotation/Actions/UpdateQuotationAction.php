<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use Illuminate\Support\Facades\DB;

class UpdateQuotationAction
{
    public function __construct(
        protected CreateQuotationPassengersAction $passengersAction,
        protected CreateQuotationItemsAction $itemsAction,
        protected CalculateQuotationTotalsAction $totalsAction
    ) {
    }

    /**
     * Actualizar una cotización completa.
     */
    public function execute(
        Quotation $quotation,
        array $data
    ): Quotation {

        return DB::transaction(function () use (
            $quotation,
            $data
        ) {

            /*
            |--------------------------------------------------------------------------
            | Cabecera
            |--------------------------------------------------------------------------
            */

            $quotation->update([

                'customer_id' => $data['customer_id'],

                'currency_id' => $data['currency_id'],

                'price_list_id' => $data['price_list_id'],

                'travel_date' => $data['travel_date'],

                'valid_until' => $data['valid_until'],

                'notes' => $data['notes'] ?? null,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Eliminar pasajeros
            |--------------------------------------------------------------------------
            */

            $quotation->passengers()->delete();

            /*
            |--------------------------------------------------------------------------
            | Crear pasajeros nuevamente
            |--------------------------------------------------------------------------
            */

            $this->passengersAction->execute(
                $quotation,
                $data['passengers']
            );

            /*
            |--------------------------------------------------------------------------
            | Eliminar servicios
            |--------------------------------------------------------------------------
            */

            $quotation->items()->delete();

            /*
            |--------------------------------------------------------------------------
            | Crear servicios nuevamente
            |--------------------------------------------------------------------------
            */

            $this->itemsAction->execute(
                $quotation,
                $data['items']
            );

            /*
            |--------------------------------------------------------------------------
            | Recalcular totales
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