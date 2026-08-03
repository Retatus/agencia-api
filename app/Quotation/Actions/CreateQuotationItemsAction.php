<?php

namespace App\Quotation\Actions;

use Illuminate\Support\Facades\Log;

use App\Quotation\Models\QuotationItinerary;

class CreateQuotationItemsAction
{
    public function execute(
        QuotationItinerary $itinerary,
        array $items
    ): void {

        Log::debug('items', ($items));
        foreach ($items as $itemData) {

            $quantity = (float) ($itemData['quantity'] ?? 1);
            $unitCost = (float) ($itemData['unit_cost'] ?? 0);
            $unitPrice = (float) ($itemData['unit_price'] ?? 0);


            /*
            |--------------------------------------------------------------------------
            | Crear item
            |--------------------------------------------------------------------------
            |
            | El UUID definitivo lo genera Laravel mediante HasUuids.
            |
            | No utilizamos el UUID enviado por Vue.
            |
            */

            $itinerary->items()->create([
                'quotation_itinerary_id' => $itinerary->id,

                'service_id'         => $itemData['service_id'] ?? null,
                'service_variant_id' => $itemData['service_variant_id'] ?? null,
                'price_id'           => $itemData['price_id'] ?? null,

                'item_type'          => $itemData['item_type'] ?? 'CUSTOM',

                'name'               => $itemData['name'] ?? null,
                'variant_name'       => $itemData['variant_name'] ?? null,
                'description'        => $itemData['description'] ?? null,
                'duration'           => $itemData['duration'] ?? null,

                'quantity'           => $quantity,
                'unit_cost'          => $unitCost,
                'unit_price'         => $unitPrice,

                'subtotal'           => $quantity * $unitPrice,

                'sort_order'         => $itemData['sort_order'] ?? 1,

                'notes'              => $itemData['notes'] ?? null,

                'active'             => $itemData['active'] ?? true,
            ]);
        }
    }
}