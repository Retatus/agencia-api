<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use Illuminate\Support\Carbon;

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
            'currency_id',
            'quotation_status_id',
            'exchange_rate',
            'travel_date',
            'valid_until',
            'notes',
            'discount',
            'tax',
            'calculation_status',
            'calculation_dirty_reasons',
            'pending_calculation_items',
            'calculated_at',
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

        if (array_key_exists('pending_calculation_items', $quotationData)) {
            $pendingItems = array_values(array_unique(
                $quotationData['pending_calculation_items'] ?? []
            ));

            $quotationData['pending_calculation_items'] = $pendingItems;
            $quotationData['calculation_status'] = empty($pendingItems)
                ? Quotation::CALCULATION_CURRENT
                : Quotation::CALCULATION_DIRTY;

            if (empty($pendingItems)) {
                $quotationData['calculation_dirty_reasons'] = [];
                $quotationData['calculated_at'] = $this->normalizeDateTime(
                    $data['calculated_at'] ?? now()
                );
            } else {
                $quotationData['calculation_dirty_reasons'] = array_values(array_unique(
                    $data['calculation_dirty_reasons'] ?? []
                ));
                $quotationData['calculated_at'] = null;
            }
        }


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

    protected function normalizeDateTime(mixed $value): string
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
