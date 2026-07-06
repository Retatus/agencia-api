<?php

namespace App\Quotation\Actions;

use App\Models\Price;
use App\Models\ServiceVariant;
use App\Quotation\Models\Quotation;

class CreateQuotationItemsAction
{
    /**
     * Crear los servicios de la cotización.
     */
    public function execute(Quotation $quotation, array $items): void {

        foreach ($items as $item) {

            /*
            |--------------------------------------------------------------------------
            | Obtener la variante
            |--------------------------------------------------------------------------
            */

            $variant = ServiceVariant::findOrFail(
                $item['service_variant_id']
            );

            /*
            |--------------------------------------------------------------------------
            | Buscar el precio
            |--------------------------------------------------------------------------
            */

            $price = Price::query()

                ->where('price_list_id', $quotation->price_list_id)

                ->where('service_variant_id', $variant->id)

                ->where('active', true)

                ->when(
                    isset($item['passenger_type_id']),
                    fn ($q) => $q->where(
                        'passenger_type_id',
                        $item['passenger_type_id']
                    )
                )

                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Cantidad
            |--------------------------------------------------------------------------
            */

            $quantity = $item['quantity'];

            /*
            |--------------------------------------------------------------------------
            | Importes
            |--------------------------------------------------------------------------
            */

            $unitCost = $price->cost;

            $unitPrice = $price->sale_price;

            $subtotalCost = $unitCost * $quantity;

            $subtotalSale = $unitPrice * $quantity;

            /*
            |--------------------------------------------------------------------------
            | Crear detalle
            |--------------------------------------------------------------------------
            */

            $quotation->items()->create([

                'service_variant_id' => $variant->id,

                'price_id' => $price->id,

                'service_date' => $item['service_date'],

                'quantity' => $quantity,

                'unit_cost' => $unitCost,

                'unit_price' => $unitPrice,

                'subtotal_cost' => $subtotalCost,

                'subtotal_price' => $subtotalSale,

                'notes' => $item['notes'] ?? null,

                'active' => true,

            ]);
        }
    }
}