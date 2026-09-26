<?php

namespace App\Pricing\PriceList\Services;

use App\Pricing\PriceList\Enums\AdjustmentType;
use DomainException;

final class PriceAdjustmentCalculator
{
    public function calculate(
        string|float|int $base,
        AdjustmentType $type,
        string|float|int|null $adjustment,
    ): string {
        if ($adjustment === null) {
            return number_format((float) $base, 2, '.', '');
        }

        $baseAmount = (float) $base;
        $adjustmentAmount = (float) $adjustment;

        $result = match ($type) {
            AdjustmentType::PERCENTAGE =>
                $baseAmount + ($baseAmount * $adjustmentAmount / 100),
            AdjustmentType::FIXED =>
                $baseAmount + $adjustmentAmount,
            AdjustmentType::OVERRIDE =>
                $adjustmentAmount,
        };

        if ($result < 0) {
            throw new DomainException(
                'El ajuste no puede producir un importe negativo.'
            );
        }

        return number_format(round($result, 2), 2, '.', '');
    }
}
