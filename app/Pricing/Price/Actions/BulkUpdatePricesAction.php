<?php

namespace App\Pricing\Price\Actions;

use App\Pricing\Price\Models\Price;
use App\Pricing\Price\Services\PriceIntegrityValidator;
use Illuminate\Support\Facades\DB;

class BulkUpdatePricesAction
{
    public function __construct(
        private PriceIntegrityValidator $integrityValidator
    ) {
    }

    public function execute(array $prices): array
    {
        return DB::transaction(function () use ($prices) {

            $updated = [];

            foreach ($prices as $data) {

                $price = Price::query()
                    ->findOrFail($data['id']);

                /*
                |--------------------------------------------------------------------------
                | Solo campos permitidos para edición masiva
                |--------------------------------------------------------------------------
                */

                $values = collect($data)
                    ->only([
                        'cost',
                        'sale_price',
                        'active',
                        'min_quantity',
                        'max_quantity',
                    ])
                    ->toArray();

                $candidate = array_merge(
                    $price->attributesToArray(),
                    $values
                );

                $this->integrityValidator->validate(
                    $candidate,
                    $price->getKey()
                );

                $price->update($values);

                $updated[] = $price->fresh();
            }

            return $updated;
        });
    }
}
