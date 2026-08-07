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
            $calculationType = $itemData['calculation_type'] ?? 'generic';
            $duration =  max( 1, (int) ($itemData['duration'] ?? 1 ));

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
                'calculation_type'   => $calculationType,
                'group_uuid'         => $itemData['group_uuid'] ?? null,
                'group_index'        => $itemData['group_index'] ?? null,

                'name'               => $itemData['name'] ?? null,
                'variant_name'       => $itemData['variant_name'] ?? null,
                'description'        => $itemData['description'] ?? null,
                'duration'           => $duration,

                'quantity'           => $quantity,
                'unit_cost'          => $unitCost,
                'unit_price'         => $unitPrice,

                'subtotal'           => $this->calculateSubtotal(
                    calculationType:
                        $calculationType,

                    quantity:
                        $quantity,

                    unitPrice:
                        $unitPrice,

                    duration:
                        $duration
                ),

                'sort_order'         => $itemData['sort_order'] ?? 1,

                'notes'              => $itemData['notes'] ?? null,

                'active'             => $itemData['active'] ?? true,
            ]);
        }
    }

    protected function calculateSubtotal(
        string $calculationType,
        float $quantity,
        float $unitPrice,
        int $duration
    ): float {

        /*
        |--------------------------------------------------------------------------
        | Alojamiento
        |--------------------------------------------------------------------------
        */

        if (
            $calculationType ===
            'accommodation'
        ) {
            return
                $quantity
                * $unitPrice
                * $duration;
        }

        /*
        |--------------------------------------------------------------------------
        | Generic / Transport
        |--------------------------------------------------------------------------
        */

        return
            $quantity
            * $unitPrice;
    }
}