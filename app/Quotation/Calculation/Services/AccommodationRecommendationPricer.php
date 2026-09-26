<?php

namespace App\Quotation\Calculation\Services;

use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\Exceptions\AmbiguousPriceException;
use App\Pricing\Price\Exceptions\PriceNotFoundException;
use App\Pricing\Price\Services\PricingService;
use App\Pricing\PriceType\Enums\QuantityBasis;
use App\Pricing\PriceType\Models\PriceType;
use Carbon\CarbonImmutable;

final readonly class AccommodationRecommendationPricer
{
    public function __construct(
        private PricingService $pricingService,
    ) {
    }

    public function price(
        array $recommendation,
        int $currencyId,
        string $serviceDate,
        int $nights,
        ?int $commercialPolicyId = null,
    ): ?array {
        $priceType = PriceType::query()
            ->where('code', 'ROOM')
            ->where('active', true)
            ->first();

        if (
            $priceType === null
            || $priceType->quantity_basis !== QuantityBasis::UNITS
        ) {
            return null;
        }

        $totalCost = 0.0;
        $totalSale = 0.0;

        try {
            foreach ($recommendation['rooms'] as $index => $room) {
                $quantity = (int) $room['quantity'];

                $resolved = $this->pricingService->resolve(
                    new PriceContext(
                        serviceVariantId: (int) $room['service_variant_id'],
                        priceTypeId: (int) $priceType->getKey(),
                        currencyId: $currencyId,
                        serviceDate: CarbonImmutable::parse($serviceDate),
                        quantity: $quantity,
                        commercialPolicyId: $commercialPolicyId,
                    )
                );

                $unitCost = (float) $resolved->finalCost;
                $unitPrice = (float) $resolved->finalSalePrice;
                $subtotalCost = $quantity * $unitCost * $nights;
                $subtotalSale = $quantity * $unitPrice * $nights;

                $recommendation['rooms'][$index] = array_merge($room, [
                    'price_id' => $resolved->priceId,
                    'price_list_id' => $resolved->priceListId,
                    'price_list_item_id' => $resolved->priceListItemId,
                    'base_cost' => (float) $resolved->baseCost,
                    'base_price' => (float) $resolved->baseSalePrice,
                    'pricing_quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'unit_price' => $unitPrice,
                    'nights' => $nights,
                    'subtotal_cost' => $subtotalCost,
                    'subtotal_sale' => $subtotalSale,
                    'adjustment_type' => $resolved->adjustmentType,
                    'cost_adjustment' => $resolved->costAdjustment,
                    'sale_adjustment' => $resolved->saleAdjustment,
                ]);

                $totalCost += $subtotalCost;
                $totalSale += $subtotalSale;
            }
        } catch (PriceNotFoundException|AmbiguousPriceException) {
            return null;
        }

        $recommendation['total_cost'] = $totalCost;
        $recommendation['total_sale'] = $totalSale;

        return $recommendation;
    }
}
