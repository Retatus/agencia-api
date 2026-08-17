<?php

namespace App\Pricing\PriceListItem\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceListItemResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {
        $variant = $this->serviceVariant;

        $service = $variant?->service;

        $basePrice = $variant?->basePrice;

        return [
            'id' => $this->id,

            'service_variant_id' =>
                $this->service_variant_id,

            'adjustment_type' =>
                $this->adjustment_type,

            'adjustment_value' =>
                $this->adjustment_value,

            'active' =>
                $this->active,

            /*
            |--------------------------------------------------------------------------
            | Price List
            |--------------------------------------------------------------------------
            */

            'price_list' => $this->whenLoaded(
                'priceList',
                fn () => [
                    'id' =>
                        $this->priceList->id,

                    'uuid' =>
                        $this->priceList->uuid,

                    'code' =>
                        $this->priceList->code,

                    'name' =>
                        $this->priceList->name,
                ]
            ),

            /*
            |--------------------------------------------------------------------------
            | Service Variant
            |--------------------------------------------------------------------------
            */

            'service_variant' => $variant
                ? [
                    'id' =>
                        $variant->id,

                    'code' =>
                        $variant->code,

                    'name' =>
                        $variant->name,

                    'service' => $service
                        ? [
                            'id' =>
                                $service->id,

                            'uuid' =>
                                $service->uuid,

                            'code' =>
                                $service->code,

                            'name' =>
                                $service->name,
                        ]
                        : null,

                    /*
                    |--------------------------------------------------------------------------
                    | BasePrice
                    |--------------------------------------------------------------------------
                    */

                    'base_price' => $basePrice
                        ? [
                            'id' =>
                                $basePrice->id,

                            'currency_id' =>
                                $basePrice->currency_id,

                            'cost' =>
                                $basePrice->cost,

                            'sale_price' =>
                                $basePrice->sale_price,

                            'active' =>
                                $basePrice->active,

                            'currency' =>
                                $basePrice->currency
                                    ? [
                                        'id' =>
                                            $basePrice->currency->id,

                                        'code' =>
                                            $basePrice->currency->code,

                                        'name' =>
                                            $basePrice->currency->name,
                                    ]
                                    : null,
                        ]
                        : null,
                ]
                : null,

            'created_at' =>
                $this->created_at,

            'updated_at' =>
                $this->updated_at,
        ];
    }
}