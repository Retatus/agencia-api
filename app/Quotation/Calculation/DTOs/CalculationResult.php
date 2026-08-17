<?php

namespace App\Quotation\Calculation\DTOs;

class CalculationResult
{
    public function __construct(

        /**
         * Item original utilizado para realizar el cálculo.
         */
        public readonly array $item,

        /**
         * Cantidad calculada.
         *
         * Generic:
         *   cantidad del servicio.
         *
         * Accommodation:
         *   cantidad total de habitaciones.
         *
         * Transport:
         *   cantidad total de vehículos.
         */
        public readonly float $quantity,

        /**
         * Costo unitario.
         *
         * Puede ser 0 cuando el resultado está compuesto
         * por múltiples variantes.
         */
        public readonly float $unitCost,

        /**
         * Precio de venta unitario.
         *
         * Puede ser 0 cuando el resultado está compuesto
         * por múltiples variantes.
         */
        public readonly float $unitPrice,

        /**
         * Costo total calculado.
         */
        public readonly float $subtotalCost,

        /**
         * Precio total de venta calculado.
         */
        public readonly float $subtotalSale,

        /**
         * Información adicional del cálculo.
         *
         * Aquí puede almacenarse:
         *
         * - recomendaciones
         * - habitaciones seleccionadas
         * - vehículos seleccionados
         * - pricing_source
         * - base_price_id
         * - price_list_id
         * - price_list_item_id
         * - adjustment_type
         * - adjustment_value
         */
        public readonly array $metadata = []

    ) {
    }

    public function toArray(): array
    {
        return [

            'item' =>
                $this->item,

            'quantity' =>
                $this->quantity,

            'unit_cost' =>
                $this->unitCost,

            'unit_price' =>
                $this->unitPrice,

            'subtotal_cost' =>
                $this->subtotalCost,

            'subtotal_sale' =>
                $this->subtotalSale,

            'metadata' =>
                $this->metadata,
        ];
    }
}