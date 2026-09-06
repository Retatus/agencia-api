<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;

class CalculateQuotationTotalsAction
{
    /**
     * Recalcula los importes de la cotización.
     */
    public function execute(Quotation $quotation): Quotation
    {
        /*
        |--------------------------------------------------------------------------
        | Cargar itinerarios e items
        |--------------------------------------------------------------------------
        */

        $quotation->loadMissing('itineraries.items');

        /*
        |--------------------------------------------------------------------------
        | Subtotal
        |--------------------------------------------------------------------------
        */

        $subtotal = 0;

        foreach ($quotation->itineraries as $itinerary) {

            $subtotal += $itinerary->items->sum('subtotal');

        }

        /*
        |--------------------------------------------------------------------------
        | Descuento
        |--------------------------------------------------------------------------
        */

        $discount = (float) ($quotation->discount ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Impuesto
        |--------------------------------------------------------------------------
        */

        $tax = (float) ($quotation->tax ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        $total = $subtotal - $discount + $tax;

        /*
        |--------------------------------------------------------------------------
        | Actualizar
        |--------------------------------------------------------------------------
        */

        $quotation->update([

            'subtotal' => $subtotal,

            'total'    => $total,

        ]);

        return $quotation->fresh([
            'itineraries',
            'itineraries.items',
        ]);
    }
}