<?php

namespace App\Pricing\PriceListItem\Models;

use App\Models\ServiceVariant;
use App\Pricing\PriceList\Models\PriceList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListItem extends Model
{
    public const TYPE_OVERRIDE = 'OVERRIDE';
    public const TYPE_FIXED = 'FIXED';
    public const TYPE_PERCENTAGE = 'PERCENTAGE';

    protected $fillable = [
        'price_list_id',
        'service_variant_id',
        'adjustment_type',
        'adjustment_value',
        'override_cost',
        'override_sale_price',
        'active',
    ];

    protected $casts = [
        'adjustment_value' => 'decimal:4',
        'override_cost' => 'decimal:2',
        'override_sale_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(
            PriceList::class,
            'price_list_id'
        );
    }

    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(
            ServiceVariant::class,
            'service_variant_id'
        );
    }
}