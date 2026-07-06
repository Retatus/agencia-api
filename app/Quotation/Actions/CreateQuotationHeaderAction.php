<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationStatus;
use Illuminate\Support\Facades\Auth;

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

            'customer_id' => $data['customer_id'],

            'currency_id' => $data['currency_id'],

            'price_list_id' => $data['price_list_id'],

            'quotation_status_id' => $this->draftStatus(),

            /*
            |--------------------------------------------------------------------------
            | Fechas
            |--------------------------------------------------------------------------
            */

            'travel_date' => $data['travel_date'],

            'valid_until' => $data['valid_until'],

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
            */

            'subtotal' => 0,

            'discount' => 0,

            'tax' => 0,

            'total' => 0,

            /*
            |--------------------------------------------------------------------------
            | Auditoría
            |--------------------------------------------------------------------------
            */

            'user_id' => Auth::id(),

            'active' => true,

        ]);
    }

    /**
     * Estado inicial.
     */
    protected function draftStatus(): int
    {
        return QuotationStatus::where('code', 'DRAFT')
            ->value('id');
    }

    /**
     * Generar código correlativo.
     */
    protected function generateCode(): string
    {
        $year = now()->year;

        $lastQuotation = Quotation::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $next = $lastQuotation
            ? ((int) substr($lastQuotation->code, -5)) + 1
            : 1;

        return sprintf(
            'COT-%s-%05d',
            $year,
            $next
        );
    }
}