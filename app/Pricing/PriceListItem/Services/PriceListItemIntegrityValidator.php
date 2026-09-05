<?php

namespace App\Pricing\PriceListItem\Services;

use App\Pricing\Price\Models\Price;
use App\Pricing\PriceList\Enums\AdjustmentType;
use App\Pricing\PriceList\Models\PriceList;
use App\Pricing\PriceListItem\Models\PriceListItem;
use Illuminate\Validation\ValidationException;

final class PriceListItemIntegrityValidator
{
    public function validate(array $data, ?PriceListItem $current = null): void
    {
        $priceList = PriceList::query()->findOrFail($data['price_list_id']);
        $price = Price::query()->findOrFail($data['price_id']);

        if ((int) $priceList->currency_id !== (int) $price->currency_id) {
            throw ValidationException::withMessages([
                'price_id' => 'La moneda del precio base debe coincidir con la moneda de la lista.',
            ]);
        }

        $duplicate = PriceListItem::query()
            ->where('price_list_id', $priceList->getKey())
            ->where('price_id', $price->getKey())
            ->when(
                $current !== null,
                fn ($query) => $query->where('id', '!=', $current->getKey())
            )
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'price_id' => 'Este precio base ya tiene un ajuste dentro de la lista.',
            ]);
        }

        if (
            ($data['cost_adjustment'] ?? null) === null
            && ($data['sale_adjustment'] ?? null) === null
        ) {
            throw ValidationException::withMessages([
                'sale_adjustment' => 'Debe indicar al menos un ajuste de costo o venta.',
            ]);
        }

        $type = $data['adjustment_type'] instanceof AdjustmentType
            ? $data['adjustment_type']
            : AdjustmentType::from($data['adjustment_type']);

        foreach (['cost_adjustment', 'sale_adjustment'] as $field) {
            $value = $data[$field] ?? null;

            if ($value === null) {
                continue;
            }

            if ($type === AdjustmentType::PERCENTAGE && (float) $value < -100) {
                throw ValidationException::withMessages([
                    $field => 'Un porcentaje no puede ser menor que -100%.',
                ]);
            }

            if ($type === AdjustmentType::OVERRIDE && (float) $value < 0) {
                throw ValidationException::withMessages([
                    $field => 'Una sobrescritura no puede ser negativa.',
                ]);
            }
        }
    }
}
