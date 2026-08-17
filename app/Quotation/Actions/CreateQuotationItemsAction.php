<?php

namespace App\Quotation\Actions;

use App\Pricing\Resolution\DTOs\PriceContext;
use App\Pricing\Resolution\Services\PriceResolver;
use App\Quotation\Models\QuotationItinerary;
use Carbon\Carbon;

class CreateQuotationItemsAction
{
    public function __construct(
        private readonly PriceResolver $priceResolver,
    ) {
    }

    public function execute(
        QuotationItinerary $itinerary,
        array $items
    ): void {

        foreach ($items as $itemData) {

            if (
                ($itemData['item_type'] ?? null)
                === 'CUSTOM'
            ) {
                $itinerary
                    ->items()
                    ->create(
                        $this->buildManualItem(
                            $itemData
                        )
                    );

                continue;
            }

            $serviceDate =
                $itemData['travel_date']
                ?? $itinerary->travel_date
                ?? $itinerary->quotation->travel_date;

            $resolved =
                $this->priceResolver->resolve(
                    new PriceContext(
                        serviceVariantId:
                            $itemData['service_variant_id'],

                        date:
                            Carbon::parse(
                                $serviceDate
                            )
                    )
                );

            $quantity =
                (float) (
                    $itemData['quantity']
                    ?? 1
                );

            $itinerary
                ->items()
                ->create([
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
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MANUAL ITEM
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

        return [
            ...$itemData,

            'base_price_id' => null,
            //'price_list_id' => null,
            'price_list_item_id' => null,

            'pricing_source' =>
                'MANUAL',

            'base_cost' => null,
            'base_price' => null,

            'adjustment_type' => null,
            'adjustment_value' => null,

            'unit_price' =>
                $unitPrice,

            'subtotal' =>
                $quantity
                * $unitPrice,
        ];
    }
}