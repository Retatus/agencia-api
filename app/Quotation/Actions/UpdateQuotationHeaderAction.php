<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;

class UpdateQuotationHeaderAction
{
    /**
     * Actualizar los datos principales de una cotización.
     *
     * Este Action:
     *
     * - Actualiza únicamente los campos permitidos.
     * - Mantiene el mismo ID y UUID de la cotización.
     * - No crea una nueva cotización.
     * - No elimina ni recrea registros.
     * - El modelo HasHistory registra automáticamente
     *   los campos modificados.
     */
    public function execute(
        Quotation $quotation,
        array $data
    ): Quotation {

        /*
        |--------------------------------------------------------------------------
        | Campos permitidos para actualizar
        |--------------------------------------------------------------------------
        */

        $fields = [
            'customer_id',
            //'price_list_id',
            'currency_id',
            'quotation_status_id',
            'exchange_rate',
            'travel_date',
            'valid_until',
            'notes',
            'discount',
            'tax',
            'active',
        ];


        /*
        |--------------------------------------------------------------------------
        | Filtrar datos recibidos
        |--------------------------------------------------------------------------
        |
        | Evitamos que cualquier campo adicional enviado desde el frontend
        | pueda ser actualizado directamente.
        |
        */

        $quotationData = collect($data)
            ->only($fields)
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Actualizar cotización
        |--------------------------------------------------------------------------
        |
        | Eloquent ejecutará:
        |
        | UPDATE quotations
        |
        | No se crea una nueva entidad.
        |
        | Si HasHistory está implementado en Quotation:
        |
        | - updating
        | - updated
        |
        | permitirán registrar:
        |
        | old_value
        | new_value
        | field
        | action = updated
        |
        */

        if (!empty($quotationData)) {
            $quotation->update(
                $quotationData
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Retornar entidad actualizada
        |--------------------------------------------------------------------------
        */

        return $quotation;
    }
}