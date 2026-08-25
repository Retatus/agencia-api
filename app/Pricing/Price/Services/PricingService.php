<?php

namespace App\Pricing\Price\Services;

use App\Pricing\Price\Contracts\PriceAdjustmentPolicy;
use App\Pricing\Price\Contracts\PriceResolverInterface;
use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\DTOs\ResolvedPrice;

final readonly class PricingService
{
    public function __construct(
        private PriceResolverInterface $priceResolver,
        private PriceAdjustmentPolicy $adjustmentPolicy,
    ) {
    }

    public function resolve(PriceContext $context): ResolvedPrice
    {
        $price = $this->priceResolver->resolve($context);

        return $this->adjustmentPolicy->apply($price, $context);
    }
}
