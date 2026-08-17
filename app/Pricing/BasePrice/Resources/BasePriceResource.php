<?php

namespace App\Pricing\BasePrice\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasePriceResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {
        return [
            'id' =>
                $this->id,

            'service_variant_id' =>
                $this->service_variant_id,

            'currency_id' =>
                $this->currency_id,

            'cost' =>
                $this->cost,

            'sale_price' =>
                $this->sale_price,

            'active' =>
                $this->active,

            /*
            |--------------------------------------------------------------------------
            | Service Variant
            |--------------------------------------------------------------------------
            */

            'service_variant' =>
                $this->whenLoaded(
                    'serviceVariant',
                    function () {
                        return [
                            'id' =>
                                $this
                                    ->serviceVariant
                                    ->id,

                            'code' =>
                                $this
                                    ->serviceVariant
                                    ->code,

                            'name' =>
                                $this
                                    ->serviceVariant
                                    ->name,

                            'service' =>
                                $this
                                    ->serviceVariant
                                    ->relationLoaded(
                                        'service'
                                    )
                                    ? [
                                        'id' =>
                                            $this
                                                ->serviceVariant
                                                ->service
                                                ?->id,

                                        'uuid' =>
                                            $this
                                                ->serviceVariant
                                                ->service
                                                ?->uuid,

                                        'code' =>
                                            $this
                                                ->serviceVariant
                                                ->service
                                                ?->code,

                                        'name' =>
                                            $this
                                                ->serviceVariant
                                                ->service
                                                ?->name,
                                    ]
                                    : null,
                        ];
                    }
                ),

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            'currency' =>
                $this->whenLoaded(
                    'currency',
                    function () {
                        return [
                            'id' =>
                                $this
                                    ->currency
                                    ->id,

                            'code' =>
                                $this
                                    ->currency
                                    ->code,

                            'name' =>
                                $this
                                    ->currency
                                    ->name,
                        ];
                    }
                ),

            'created_at' =>
                $this->created_at,

            'updated_at' =>
                $this->updated_at,
        ];
    }
}