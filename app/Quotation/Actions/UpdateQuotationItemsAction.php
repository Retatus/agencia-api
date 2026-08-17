<?php

namespace App\Quotation\Actions;

use App\Pricing\Resolution\DTOs\PriceContext;
use App\Pricing\Resolution\Services\PriceResolver;
use App\Quotation\Models\Quotation;
use Carbon\Carbon;

class UpdateQuotationItemsAction
{
    public function __construct(
        private readonly PriceResolver $priceResolver,
    ) {
    }

    public function execute(
        Quotation $quotation,
        array $items
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Estrategia actual
        |--------------------------------------------------------------------------
        |
        | Sincronizamos los items recibidos.
        | Si ya tienes una estrategia distinta de sync, conserva esa parte.
        |
        */

        foreach ($items as $itemData) {

            $item = $quotation
                ->items()
                ->where(
                    'uuid',
                    $itemData['uuid'] ?? null
                )
                ->first();

            /*
            |--------------------------------------------------------------------------
            | ITEM MANUAL
            |--------------------------------------------------------------------------
            */

            if (
                ($itemData['item_type'] ?? null)
                === 'CUSTOM'
            ) {
                $data =
                    $this->buildManualItem(
                        $itemData
                    );

                if ($item) {
                    $item->update($data);
                } else {
                    $quotation
                        ->items()
                        ->create($data);
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | FECHA DEL SERVICIO
            |--------------------------------------------------------------------------
            */

            $serviceDate =
                $itemData['travel_date']
                ?? $quotation->travel_date;

            /*
            |--------------------------------------------------------------------------
            | RESOLVER PRECIO
            |--------------------------------------------------------------------------
            */

            $resolved =
                $this->priceResolver->resolve(
                    new PriceContext(
                        serviceVariantId:
                            $itemData[
                                'service_variant_id'
                            ],

                        date:
                            Carbon::parse(
                                $serviceDate
                            ),
                    )
                );

            $quantity =
                (float) (
                    $itemData['quantity']
                    ?? 1
                );

            /*
            |--------------------------------------------------------------------------
            | PAYLOAD
            |--------------------------------------------------------------------------
            */

            $data = [
                ...$itemData,

                'base_price_id' =>
                    $resolved->basePriceId,

                'price_list_id' =>
                    $resolved->priceListId,

                'price_list_item_id' =>
                    $resolved->priceListItemId,

                'pricing_source' =>
                    $resolved->pricingSource,

                'base_cost' =>
                    $resolved->baseCost,

                'base_price' =>
                    $resolved->basePrice,

                'adjustment_type' =>
                    $resolved->adjustmentType,

                'adjustment_value' =>
                    $resolved->adjustmentValue,

                'unit_cost' =>
                    $resolved->finalCost,

                'unit_price' =>
                    $resolved->finalPrice,

                'subtotal' =>
                    $quantity
                    * $resolved->finalPrice,
            ];

            /*
            |--------------------------------------------------------------------------
            | UPDATE / CREATE
            |--------------------------------------------------------------------------
            */

            if ($item) {
                $item->update($data);
            } else {
                $quotation
                    ->items()
                    ->create($data);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MANUAL
    |--------------------------------------------------------------------------
    */

    private function buildManualItem(
        array $itemData
    ): array {
        $quantity =
            (float) (
                $itemData['quantity']
                ?? 1
            );

        $unitPrice =
            (float) (
                $itemData['unit_price']
                ?? 0
            );

        $unitCost =
            (float) (
                $itemData['unit_cost']
                ?? 0
            );

        return [
            ...$itemData,

            'base_price_id' => null,

            'price_list_id' => null,

            'price_list_item_id' => null,

            'pricing_source' => 'MANUAL',

            'base_cost' => null,

            'base_price' => null,

            'adjustment_type' => null,

            'adjustment_value' => null,

            'unit_cost' =>
                $unitCost,

            'unit_price' =>
                $unitPrice,

            'subtotal' =>
                $quantity
                * $unitPrice,
        ];
    }
}