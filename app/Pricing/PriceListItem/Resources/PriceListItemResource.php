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

            'service_variant_id' => $this->service_variant_id,

            'adjustment_type' => $this->adjustment_type,

            'adjustment_value' => $this->adjustment_value,

            'override_cost' => $this->override_cost,

            'override_sale_price' => $this->override_sale_price,

            'active' => $this->active,

            'service_variant' => $this->whenLoaded(
                'serviceVariant',
                fn () => [
                    'id' => $this->serviceVariant->id,
                    'code' => $this->serviceVariant->code,
                    'name' => $this->serviceVariant->name,
                ]
            ),

            'price_list' => $this->whenLoaded(
                'priceList',
                fn () => [
                    'id' => $this->priceList->id,
                    'uuid' => $this->priceList->uuid,
                    'code' => $this->priceList->code,
                    'name' => $this->priceList->name,
                ]
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}