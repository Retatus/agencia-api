<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\QuotationItinerary;
use App\Quotation\Models\QuotationItem;

use Illuminate\Support\Str;

class CreateQuotationItemsAction
{
    public function execute(QuotationItinerary $itinerary, array $items): void 
    {
        foreach ($items as $item) {

            $quantity = (float) ($item['quantity'] ?? 1);
            $unitCost = (float) ($item['unit_cost'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);

            QuotationItem::create([
                'uuid' => $item['uuid'] ?? Str::uuid(),
                'quotation_itinerary_id' => $itinerary->id,

                'service_id'         => $item['service_id'] ?? null,
                'service_variant_id' => $item['service_variant_id'] ?? null,
                'price_id'           => $item['price_id'] ?? null,

                'item_type'          => $item['item_type'] ?? 'CUSTOM',

                'name'               => $item['name'] ?? null,
                'variant_name'       => $item['variant_name'] ?? null,
                'description'        => $item['description'] ?? null,
                'duration'           => $item['duration'] ?? null,

                'quantity'           => $quantity,

                'unit_cost'          => $unitCost,
                'unit_price'         => $unitPrice,

                'subtotal'           => $quantity * $unitPrice,

                'sort_order'         => $item['sort_order'] ?? 1,

                'notes'              => $item['notes'] ?? null,

                'active'             => $item['active'] ?? true,

            ]);
        }
    }
}