<?php

namespace App\Pricing\Price\Contracts;

use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\Models\Price;

interface PriceResolverInterface
{
    public function resolve(PriceContext $context): Price;
}
