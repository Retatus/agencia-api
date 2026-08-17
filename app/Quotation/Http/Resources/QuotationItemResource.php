<?php

namespace App\Quotation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Identificación
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,
            'uuid' => $this->uuid,

            'quotation_id' => $this->quotation_id,
            'quotation_itinerary_id' => $this->quotation_itinerary_id,

            /*
            |--------------------------------------------------------------------------
            | Servicio
            |--------------------------------------------------------------------------
            */

            'service_id' => $this->service_id,
            'service_variant_id' => $this->service_variant_id,

            'item_type' => $this->item_type,

            'name' => $this->name,
            'variant_name' => $this->variant_name,
            'description' => $this->description,

            'duration' => $this->duration,

            /*
            |--------------------------------------------------------------------------
            | Pricing Resolution
            |--------------------------------------------------------------------------
            */

            'base_price_id' => $this->base_price_id,

            'price_list_id' => $this->price_list_id,

            'price_list_item_id' => $this->price_list_item_id,

            'pricing_source' => $this->pricing_source,

            /*
            |--------------------------------------------------------------------------
            | Pricing Snapshot
            |--------------------------------------------------------------------------
            */

            'base_cost' => $this->base_cost,

            'base_price' => $this->base_price,

            'adjustment_type' => $this->adjustment_type,

            'adjustment_value' => $this->adjustment_value,

            /*
            |--------------------------------------------------------------------------
            | Precio aplicado
            |--------------------------------------------------------------------------
            */

            'unit_cost' => $this->unit_cost,

            'unit_price' => $this->unit_price,

            'quantity' => $this->quantity,

            'subtotal' => $this->subtotal,

            /*
            |--------------------------------------------------------------------------
            | Orden / estado
            |--------------------------------------------------------------------------
            */

            'sort_order' => $this->sort_order,

            'notes' => $this->notes,

            'active' => $this->active,

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            'service' => $this->whenLoaded(
                'service',
                fn () => [
                    'id' => $this->service->id,
                    'uuid' => $this->service->uuid,
                    'code' => $this->service->code,
                    'name' => $this->service->name,
                ]
            ),

            'service_variant' => $this->whenLoaded(
                'serviceVariant',
                fn () => [
                    'id' => $this->serviceVariant->id,
                    'code' => $this->serviceVariant->code,
                    'name' => $this->serviceVariant->name,

                    'min_capacity' => $this->serviceVariant->min_capacity,
                    'max_capacity' => $this->serviceVariant->max_capacity,
                    'optimal_capacity' => $this->serviceVariant->optimal_capacity,

                    'unit_type' => $this->serviceVariant->unit_type,
                    'duration' => $this->serviceVariant->duration,
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

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}