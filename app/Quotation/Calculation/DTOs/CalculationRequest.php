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
    public static function fromArray(array $data): self
    {
        return new self(
            quotation: $data
        );
    }

    /**
     * Obtener itinerarios.
     */
    public function itineraries(): array
    {
        return $this->quotation['itineraries'] ?? [];
    }

    /**
     * Obtener pasajeros.
     */
    public function passengers(): array
    {
        return $this->quotation['passengers'] ?? [];
    }

    /**
     * Obtener moneda.
     */
    public function currencyId(): ?int
    {
        return $this->quotation['currency_id'] ?? null;
    }

    public function commercialPolicyId(): ?int
    {
        $value = $this->quotation['commercial_policy_id'] ?? null;

        return $value === null || $value === '' ? null : (int) $value;
    }

    /**
     * Obtener todo el payload.
     */
    public function toArray(): array
    {
        return $this->quotation;
    }
}
