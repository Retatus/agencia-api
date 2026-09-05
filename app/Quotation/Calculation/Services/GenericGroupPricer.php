<?php

namespace App\Quotation\Calculation\Services;

use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\Services\PricingService;
use App\Pricing\PriceType\Enums\QuantityBasis;
use App\Pricing\PriceType\Models\PriceType;
use Carbon\CarbonImmutable;
use DomainException;

final readonly class GenericGroupPricer
{
    public function __construct(
        private PricingService $pricingService,
    ) {
    }

    /**
     * Resuelve un servicio de catálogo cobrado una vez por grupo, usando el
     * número de pasajeros únicamente para seleccionar el rango tarifario.
     *
     * @return array<string, mixed>
     */
    public function price(array $item): array
    {
        $passengerCount = count($item['passengers'] ?? []);

        if ($passengerCount < 1) {
            throw new DomainException(
                'Se requiere al menos un pasajero para resolver una tarifa grupal.'
            );
        }

        $serviceVariantId = (int) ($item['service_variant_id'] ?? 0);
        $currencyId = (int) ($item['currency_id'] ?? 0);
        $serviceDate = $item['service_date'] ?? null;

        if ($serviceVariantId < 1 || $currencyId < 1 || empty($serviceDate)) {
            throw new DomainException(
                'La variante, la moneda y la fecha del servicio son obligatorias para resolver la tarifa.'
            );
        }

        $priceType = PriceType::query()
            ->where('code', 'GROUP')
            ->where('active', true)
            ->first();

        if (
            $priceType === null
            || $priceType->quantity_basis !== QuantityBasis::PASSENGERS
        ) {
            throw new DomainException(
                'El tipo tarifario GROUP debe estar activo y utilizar PASSENGERS como base tarifaria.'
            );
        }

        $resolved = $this->pricingService->resolve(
            new PriceContext(
                serviceVariantId: $serviceVariantId,
                priceTypeId: (int) $priceType->getKey(),
                currencyId: $currencyId,
                serviceDate: CarbonImmutable::parse($serviceDate),
                quantity: $passengerCount,
                commercialPolicyId: isset($item['commercial_policy_id'])
                    ? (int) $item['commercial_policy_id']
                    : null,
            )
        );

        return [
            'price_id' => $resolved->priceId,
            'price_type_id' => (int) $priceType->getKey(),
            'price_type_code' => $priceType->code,
            'quantity_basis' => $priceType->quantity_basis->value,
            'pricing_quantity' => $passengerCount,
            'billing_quantity' => 1,
            'base_cost' => (float) $resolved->baseCost,
            'base_price' => (float) $resolved->baseSalePrice,
            'unit_cost' => (float) $resolved->finalCost,
            'unit_price' => (float) $resolved->finalSalePrice,
            'price_list_id' => $resolved->priceListId,
            'price_list_item_id' => $resolved->priceListItemId,
            'adjustment_type' => $resolved->adjustmentType,
            'cost_adjustment' => $resolved->costAdjustment,
            'sale_adjustment' => $resolved->saleAdjustment,
        ];
    }
}
