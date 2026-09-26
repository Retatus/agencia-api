<?php

namespace App\Pricing\Price\Policies;

use App\Pricing\Price\Contracts\PriceAdjustmentPolicy;
use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\DTOs\ResolvedPrice;
use App\Pricing\Price\Models\Price;

final class NoPriceAdjustmentPolicy implements PriceAdjustmentPolicy
{
    public function apply(
        Price $price,
        PriceContext $context
    ): ResolvedPrice {
        return ResolvedPrice::fromPrice($price);
    }
}
