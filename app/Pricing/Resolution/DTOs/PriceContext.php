<?php

namespace App\Pricing\Resolution\DTOs;

use Carbon\Carbon;

final readonly class PriceContext
{
    public function __construct(
        public int $serviceVariantId,
        public Carbon $date,
    ) {
    }
}