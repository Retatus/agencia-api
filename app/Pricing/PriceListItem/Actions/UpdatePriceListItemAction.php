<?php

namespace App\Pricing\PriceListItem\Actions;

use App\Pricing\PriceListItem\Models\PriceListItem;
use App\Pricing\PriceListItem\Services\PriceListItemIntegrityValidator;

final readonly class UpdatePriceListItemAction
{
    public function __construct(
        private PriceListItemIntegrityValidator $validator,
    ) {
    }

    public function execute(PriceListItem $item, array $data): PriceListItem
    {
        $merged = array_merge($item->only([
            'price_list_id',
            'price_id',
            'adjustment_type',
            'cost_adjustment',
            'sale_adjustment',
            'active',
        ]), $data);

        $this->validator->validate($merged, $item);
        $item->update($data);

        return $item->fresh();
    }
}
