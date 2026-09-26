<?php

namespace App\Pricing\PriceListItem\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price_list_id' => $this->price_list_id,
            'price_id' => $this->price_id,
            'adjustment_type' => $this->adjustment_type?->value,
            'cost_adjustment' => $this->cost_adjustment,
            'sale_adjustment' => $this->sale_adjustment,
            'active' => $this->active,
            'price_list' => $this->whenLoaded('priceList'),
            'price' => $this->whenLoaded('price'),
        ];
    }
}
