<?php

namespace App\Pricing\BasePrice\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasePriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'service_variant_id' => $this->service_variant_id,

            'currency_id' => $this->currency_id,

            'cost' => $this->cost,

            'sale_price' => $this->sale_price,

            'active' => $this->active,

            'currency' => $this->whenLoaded(
                'currency',
                fn () => [
                    'id' => $this->currency->id,
                    'code' => $this->currency->code,
                    'name' => $this->currency->name,
                ]
            ),

            'service_variant' => $this->whenLoaded(
                'serviceVariant',
                fn () => [
                    'id' => $this->serviceVariant->id,
                    'code' => $this->serviceVariant->code,
                    'name' => $this->serviceVariant->name,
                ]
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}