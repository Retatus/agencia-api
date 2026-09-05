<?php

namespace App\Pricing\PriceListItem\Models;

use App\Pricing\Price\Models\Price;
use App\Pricing\PriceList\Enums\AdjustmentType;
use App\Pricing\PriceList\Models\PriceList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListItem extends Model
{
    protected $table = 'price_list_items';

    protected $fillable = [
        'price_list_id',
        'price_id',
        'adjustment_type',
        'cost_adjustment',
        'sale_adjustment',
        'active',
    ];

    protected $casts = [
        'adjustment_type' => AdjustmentType::class,
        'cost_adjustment' => 'decimal:2',
        'sale_adjustment' => 'decimal:2',
        'active' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }
}
