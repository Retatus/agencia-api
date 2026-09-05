<?php

namespace App\Pricing\PriceListItem\Actions;

use App\Pricing\PriceListItem\Models\PriceListItem;
use App\Pricing\PriceListItem\Services\PriceListItemIntegrityValidator;

final readonly class CreatePriceListItemAction
{
    public function __construct(
        private PriceListItemIntegrityValidator $validator,
    ) {
    }

    public function execute(array $data): PriceListItem
    {
        $this->validator->validate($data);

        return PriceListItem::query()->create($data);
    }
}
