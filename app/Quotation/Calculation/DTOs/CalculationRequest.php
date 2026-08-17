<?php

namespace App\Quotation\Calculation\DTOs;

class CalculationRequest
{
    public function __construct(

        /**
         * Cotización enviada desde el frontend.
         */
        public readonly array $quotation

    ) {
    }

    /**
     * Crear desde array.
     */
    public static function fromArray(
        array $data
    ): self {
        return new self(
            quotation: $data
        );
    }

    /**
     * Obtener itinerarios.
     */
    public function itineraries(): array
    {
        return
            $this->quotation['itineraries']
            ?? [];
    }

    /**
     * Obtener pasajeros.
     */
    public function passengers(): array
    {
        return
            $this->quotation['passengers']
            ?? [];
    }

    /**
     * Obtener moneda.
     */
    public function currencyId(): ?int
    {
        return
            $this->quotation['currency_id']
            ?? null;
    }

    /**
     * Fecha general de viaje.
     *
     * Puede utilizarse como fallback cuando un itinerario
     * todavía no tenga una fecha específica.
     */
    public function travelDate(): ?string
    {
        return
            $this->quotation['travel_date']
            ?? null;
    }

    /**
     * Obtener todo el payload.
     */
    public function toArray(): array
    {
        return $this->quotation;
    }
}