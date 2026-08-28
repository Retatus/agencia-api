<?php

namespace App\Quotation\Calculation\Services;

use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\Exceptions\AmbiguousPriceException;
use App\Pricing\Price\Exceptions\PriceNotFoundException;
use App\Pricing\Price\Services\PricingService;
use App\Pricing\PriceType\Enums\QuantityBasis;
use App\Pricing\PriceType\Models\PriceType;
use Carbon\CarbonImmutable;

final readonly class TransportRecommendationPricer
{
    public function __construct(
        private PassengerDistributionService $passengerDistribution,
        private PricingService $pricingService,
    ) {
    }

    public function price(
        array $recommendation,
        int $passengerCount,
        int $currencyId,
        string $serviceDate,
    ): ?array {
        $units = $this->passengerDistribution->distribute(
            $recommendation['vehicles'],
            $passengerCount,
        );

        if ($units === null) {
            return null;
        }

        $priceType = PriceType::query()
            ->where('code', 'GROUP')
            ->where('active', true)
            ->first();

        if (
            $priceType === null
            || $priceType->quantity_basis !== QuantityBasis::PASSENGERS
        ) {
            return null;
        }

        $priceTypeId = (int) $priceType->getKey();

        $totalCost = 0.0;
        $totalSale = 0.0;
        $pricedUnits = [];

        try {
            foreach ($units as $unit) {
                $allocation = $recommendation['vehicles'][$unit['allocation_index']];

                $resolved = $this->pricingService->resolve(
                    new PriceContext(
                        serviceVariantId: (int) $allocation['service_variant_id'],
                        priceTypeId: $priceTypeId,
                        currencyId: $currencyId,
                        serviceDate: CarbonImmutable::parse($serviceDate),
                        quantity: $unit['passenger_count'],
                    )
                );

                $cost = (float) $resolved->finalCost;
                $salePrice = (float) $resolved->finalSalePrice;

                $totalCost += $cost;
                $totalSale += $salePrice;

                $pricedUnits[] = [
                    'service_variant_id' => (int) $allocation['service_variant_id'],
                    'name' => $allocation['name'],
                    'unit_index' => $unit['unit_index'],
                    'passenger_count' => $unit['passenger_count'],
                    'price_id' => $resolved->priceId,
                    'pricing_quantity' => $unit['passenger_count'],
                    'unit_cost' => $cost,
                    'unit_price' => $salePrice,
                ];
            }
        } catch (PriceNotFoundException|AmbiguousPriceException) {
            return null;
        }

        $recommendation['priced_units'] = $pricedUnits;
        $recommendation['total_cost'] = $totalCost;
        $recommendation['total_sale'] = $totalSale;

        foreach ($recommendation['vehicles'] as $index => $allocation) {
            $allocationUnits = array_values(array_filter(
                $pricedUnits,
                fn (array $unit): bool =>
                    $unit['service_variant_id'] === (int) $allocation['service_variant_id'],
            ));

            $recommendation['vehicles'][$index]['pricing_quantities'] =
                array_column($allocationUnits, 'pricing_quantity');
            $recommendation['vehicles'][$index]['price_ids'] =
                array_column($allocationUnits, 'price_id');
            $recommendation['vehicles'][$index]['unit_cost'] =
                count(array_unique(array_column($allocationUnits, 'unit_cost'))) === 1
                    ? $allocationUnits[0]['unit_cost']
                    : null;
            $recommendation['vehicles'][$index]['unit_price'] =
                count(array_unique(array_column($allocationUnits, 'unit_price'))) === 1
                    ? $allocationUnits[0]['unit_price']
                    : null;
            $recommendation['vehicles'][$index]['subtotal_cost'] =
                array_sum(array_column($allocationUnits, 'unit_cost'));
            $recommendation['vehicles'][$index]['subtotal_sale'] =
                array_sum(array_column($allocationUnits, 'unit_price'));
        }

        return $recommendation;
    }
}