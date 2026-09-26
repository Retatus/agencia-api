<?php

namespace App\Pricing\Price\Contracts;

use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\DTOs\ResolvedPrice;
use App\Pricing\Price\Models\Price;

interface PriceAdjustmentPolicy
{
    public function apply(
        Price $price,
        PriceContext $context
    ): ResolvedPrice;
}
