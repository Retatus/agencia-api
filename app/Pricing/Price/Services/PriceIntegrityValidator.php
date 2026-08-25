<?php

namespace App\Pricing\Price\Services;

use App\Pricing\Price\Models\Price;
use Illuminate\Validation\ValidationException;

final class PriceIntegrityValidator
{
    public function validate(array $data, ?int $ignorePriceId = null): void
    {
        $query = Price::query()
            ->where('service_variant_id', $data['service_variant_id'])
            ->where('price_type_id', $data['price_type_id'])
            ->where('currency_id', $data['currency_id'])
            ->where('priority', $data['priority'] ?? 1)
            ->when(
                ($data['passenger_type_id'] ?? null) === null,
                fn ($builder) => $builder->whereNull('passenger_type_id'),
                fn ($builder) => $builder->where(
                    'passenger_type_id',
                    $data['passenger_type_id']
                )
            );

        if ($ignorePriceId !== null) {
            $query->whereKeyNot($ignorePriceId);
        }

        $incomingMin = $data['min_quantity'] ?? null;
        $incomingMax = $data['max_quantity'] ?? null;

        if ($incomingMax !== null) {
            $query->where(function ($builder) use ($incomingMax) {
                $builder
                    ->whereNull('min_quantity')
                    ->orWhere('min_quantity', '<=', $incomingMax);
            });
        }

        if ($incomingMin !== null) {
            $query->where(function ($builder) use ($incomingMin) {
                $builder
                    ->whereNull('max_quantity')
                    ->orWhere('max_quantity', '>=', $incomingMin);
            });
        }

        $incomingFrom = $data['valid_from'] ?? null;
        $incomingTo = $data['valid_to'] ?? null;

        if ($incomingTo !== null) {
            $query->where(function ($builder) use ($incomingTo) {
                $builder
                    ->whereNull('valid_from')
                    ->orWhereDate('valid_from', '<=', $incomingTo);
            });
        }

        if ($incomingFrom !== null) {
            $query->where(function ($builder) use ($incomingFrom) {
                $builder
                    ->whereNull('valid_to')
                    ->orWhereDate('valid_to', '>=', $incomingFrom);
            });
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'price' => [
                    'Existe otra tarifa con la misma prioridad cuyos rangos de cantidad y vigencia se superponen.',
                ],
            ]);
        }
    }
}
