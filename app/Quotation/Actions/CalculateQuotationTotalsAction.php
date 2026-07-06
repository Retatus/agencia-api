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
        // Asegurar que los items estén cargados
        $quotation->loadMissing('items');

        /*
        |--------------------------------------------------------------------------
        | Totales
        |--------------------------------------------------------------------------
        */

        $subtotalCost = $quotation->items->sum('subtotal_cost');

        $subtotalSale = $quotation->items->sum('subtotal_price');

        /*
        |--------------------------------------------------------------------------
        | Descuento
        |--------------------------------------------------------------------------
        */

        $discount = $quotation->discount ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Impuestos
        |--------------------------------------------------------------------------
        */

        $tax = $quotation->tax ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        $total = $subtotalSale - $discount + $tax;

        /*
        |--------------------------------------------------------------------------
        | Actualizar cabecera
        |--------------------------------------------------------------------------
        */

        $quotation->update([

            'subtotal_cost' => $subtotalCost,

            'subtotal' => $subtotalSale,

            'total' => $total,

        ]);

        return $quotation->fresh();
    }
}