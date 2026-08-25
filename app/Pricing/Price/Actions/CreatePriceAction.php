<?php

namespace App\Pricing\Price\Actions;

use App\Pricing\Price\Models\Price;
use App\Pricing\Price\Services\PriceIntegrityValidator;
use Illuminate\Support\Facades\DB;

final readonly class CreatePriceAction
{
    public function __construct(
        private PriceIntegrityValidator $integrityValidator
    ) {
    }

    public function execute(array $data): Price
    {
        return DB::transaction(function () use ($data) {
            $this->integrityValidator->validate($data);

            return Price::create($data);
        });
    }
}
