<?php

namespace App\Pricing\ExchangeRate\Services;

use App\Pricing\ExchangeRate\DTOs\ResolvedExchangeRate;
use Carbon\CarbonImmutable;

final readonly class CurrencyConverter
{
    public function __construct(private ExchangeRateResolver $resolver)
    {
    }

    /** @return array{amount: string, exchange_rate: ResolvedExchangeRate} */
    public function convert(
        string|int|float $amount,
        int $fromCurrencyId,
        int $toCurrencyId,
        CarbonImmutable $date,
    ): array {
        $exchangeRate = $this->resolver->resolve(
            $fromCurrencyId,
            $toCurrencyId,
            $date,
        );

        return [
            'amount' => $this->apply($amount, $exchangeRate),
            'exchange_rate' => $exchangeRate,
        ];
    }

    public function apply(
        string|int|float $amount,
        ResolvedExchangeRate $exchangeRate,
    ): string {
        return number_format(
            round((float) $amount * (float) $exchangeRate->rate, 2),
            2,
            '.',
            '',
        );
    }
}
