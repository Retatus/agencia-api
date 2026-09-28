<?php

namespace App\Pricing\ExchangeRate\Services;

use App\Pricing\ExchangeRate\DTOs\ResolvedExchangeRate;
use App\Pricing\ExchangeRate\Exceptions\ExchangeRateNotFoundException;
use App\Pricing\ExchangeRate\Models\ExchangeRate;
use Carbon\CarbonImmutable;

final class ExchangeRateResolver
{
    public function resolve(
        int $fromCurrencyId,
        int $toCurrencyId,
        CarbonImmutable $date,
    ): ResolvedExchangeRate {
        if ($fromCurrencyId === $toCurrencyId) {
            return new ResolvedExchangeRate(
                $fromCurrencyId,
                $toCurrencyId,
                '1.00000000',
                $date,
            );
        }

        $direct = $this->find($fromCurrencyId, $toCurrencyId, $date);

        if ($direct !== null) {
            return new ResolvedExchangeRate(
                $fromCurrencyId,
                $toCurrencyId,
                (string) $direct->rate,
                CarbonImmutable::instance($direct->effective_date),
            );
        }

        $inverse = $this->find($toCurrencyId, $fromCurrencyId, $date);

        if ($inverse !== null) {
            $rate = number_format(
                1 / (float) $inverse->rate,
                8,
                '.',
                '',
            );

            return new ResolvedExchangeRate(
                $fromCurrencyId,
                $toCurrencyId,
                $rate,
                CarbonImmutable::instance($inverse->effective_date),
                true,
            );
        }

        throw new ExchangeRateNotFoundException(sprintf(
            'No existe tipo de cambio de la moneda %d a la moneda %d para la fecha %s.',
            $fromCurrencyId,
            $toCurrencyId,
            $date->toDateString(),
        ));
    }

    private function find(
        int $fromCurrencyId,
        int $toCurrencyId,
        CarbonImmutable $date,
    ): ?ExchangeRate {
        return ExchangeRate::query()
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->where('active', true)
            ->whereDate('effective_date', '<=', $date->toDateString())
            ->latest('effective_date')
            ->latest('id')
            ->first();
    }
}
