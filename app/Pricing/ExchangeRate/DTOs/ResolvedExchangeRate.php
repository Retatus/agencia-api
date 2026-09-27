<?php

namespace App\Pricing\ExchangeRate\DTOs;

use Carbon\CarbonImmutable;

final readonly class ResolvedExchangeRate
{
    public function __construct(
        public int $fromCurrencyId,
        public int $toCurrencyId,
        public string $rate,
        public CarbonImmutable $effectiveDate,
        public bool $inverted = false,
    ) {
    }
}
