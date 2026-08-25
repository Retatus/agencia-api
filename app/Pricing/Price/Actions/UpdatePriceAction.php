<?php

namespace App\Pricing\Price\Actions;

use App\Pricing\Price\Models\Price;
use App\Pricing\Price\Services\PriceIntegrityValidator;
use Illuminate\Support\Facades\DB;

final readonly class UpdatePriceAction
{
    public function __construct(
        private PriceIntegrityValidator $integrityValidator
    ) {
    }

    public function execute(Price $price, array $data): Price
    {
        return DB::transaction(function () use ($price, $data) {
            $values = array_merge($price->attributesToArray(), $data);

            $this->integrityValidator->validate(
                $values,
                $price->getKey()
            );

            $price->update($data);

            return $price->refresh();
        });
    }
}
