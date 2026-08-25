<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationStatus;

class CreateQuotationHeaderAction
{
    /**
     * Crear la cabecera de la cotización.
     */
    public function execute(array $data): Quotation
    {
        return Quotation::create([

            /*
            |--------------------------------------------------------------------------
            | Identificación
            |--------------------------------------------------------------------------
            */

            'code' => $this->generateCode(),

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            'customer_id'          => $data['customer_id'],
            'currency_id'          => $data['currency_id'],
            'quotation_status_id'  => $data['quotation_status_id'] ?? $this->draftStatus(),

            /*
            |--------------------------------------------------------------------------
            | Fechas
            |--------------------------------------------------------------------------
            */

            'travel_date' => $data['travel_date'],
            'valid_until' => $data['valid_until'],

            /*
            |--------------------------------------------------------------------------
            | Tipo de cambio
            |--------------------------------------------------------------------------
            */

            'exchange_rate' => $data['exchange_rate'] ?? 1,

            /*
            |--------------------------------------------------------------------------
            | Observaciones
            |--------------------------------------------------------------------------
            */

            'notes' => $data['notes'] ?? null,

            /*
            |--------------------------------------------------------------------------
            | Totales
            |--------------------------------------------------------------------------
            | Siempre inician en cero.
            | El CalculateQuotationTotalsAction será el único responsable
            | CalculateQuotationTotalsAction será responsable
            | de calcular los valores definitivos.
            |
            */

            'subtotal' => 0,
            'discount' => 0,
            'tax'       => 0,
            'total'     => 0,

            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            */

            'active'  => $data['active'] ?? true,
        ]);
    }

    /**
     * Estado inicial de la cotización.
     */
    protected function draftStatus(): int
    {
        return QuotationStatus::where('code', 'DRAFT')->value('id');
    }

    /**
     * Generar código correlativo.
     */
    protected function generateCode(): string
    {
        $year = now()->year;

        $last = Quotation::withTrashed()
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $sequence = 1;

        if ($last && preg_match('/(\d+)$/', $last->code, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf(
            'COT-%s-%05d',
            $year,
            $sequence
        );
    }
}
