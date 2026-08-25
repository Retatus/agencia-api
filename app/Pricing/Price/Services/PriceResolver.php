<?php

namespace App\Pricing\Price\Services;

use App\Pricing\Price\Contracts\PriceResolverInterface;
use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\Exceptions\AmbiguousPriceException;
use App\Pricing\Price\Exceptions\PriceNotFoundException;
use App\Pricing\Price\Models\Price;
use Illuminate\Support\Collection;

final class PriceResolver implements PriceResolverInterface
{
    public function resolve(PriceContext $context): Price
    {
        $date = $context->serviceDate->toDateString();

        $candidates = Price::query()
            ->where('active', true)
            ->where('service_variant_id', $context->serviceVariantId)
            ->where('price_type_id', $context->priceTypeId)
            ->where('currency_id', $context->currencyId)
            ->when(
                $context->passengerTypeId === null,
                fn ($query) => $query->whereNull('passenger_type_id'),
                fn ($query) => $query->where(
                    'passenger_type_id',
                    $context->passengerTypeId
                )
            )
            ->where(function ($query) use ($context) {
                $query
                    ->whereNull('min_quantity')
                    ->orWhere('min_quantity', '<=', $context->quantity);
            })
            ->where(function ($query) use ($context) {
                $query
                    ->whereNull('max_quantity')
                    ->orWhere('max_quantity', '>=', $context->quantity);
            })
            ->where(function ($query) use ($date) {
                $query
                    ->whereNull('valid_from')
                    ->orWhereDate('valid_from', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query
                    ->whereNull('valid_to')
                    ->orWhereDate('valid_to', '>=', $date);
            })
            ->get();

        if ($candidates->isEmpty()) {
            throw new PriceNotFoundException(sprintf(
                'No existe una tarifa para la variante %d, tipo %d, moneda %d, cantidad %d y fecha %s.',
                $context->serviceVariantId,
                $context->priceTypeId,
                $context->currencyId,
                $context->quantity,
                $date,
            ));
        }

        $ordered = $this->orderBySpecificity($candidates);
        $selected = $ordered->first();
        $second = $ordered->get(1);

        if (
            $second !== null
            && $this->specificityKey($selected) === $this->specificityKey($second)
        ) {
            throw new AmbiguousPriceException(sprintf(
                'Las tarifas %d y %d tienen la misma prioridad y especificidad.',
                $selected->getKey(),
                $second->getKey(),
            ));
        }

        return $selected;
    }

    private function orderBySpecificity(Collection $prices): Collection
    {
        return $prices
            ->sort(function (Price $left, Price $right) {
                return $this->specificityKey($left)
                    <=> $this->specificityKey($right);
            })
            ->values();
    }

    private function specificityKey(Price $price): array
    {
        return [
            -$price->priority,
            $this->quantitySpan($price),
            $this->validitySpan($price),
        ];
    }

    private function quantitySpan(Price $price): int
    {
        if ($price->min_quantity === null || $price->max_quantity === null) {
            return PHP_INT_MAX;
        }

        return $price->max_quantity - $price->min_quantity;
    }

    private function validitySpan(Price $price): int
    {
        if ($price->valid_from === null || $price->valid_to === null) {
            return PHP_INT_MAX;
        }

        return (int) $price->valid_from->diffInDays($price->valid_to);
    }
}
