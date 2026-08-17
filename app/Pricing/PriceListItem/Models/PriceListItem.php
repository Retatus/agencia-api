<?php

namespace App\Pricing\PriceListItem\Models;

use App\Models\ServiceVariant;
use App\Pricing\PriceList\Models\PriceList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListItem extends Model
{
    protected $fillable = [
        'price_list_id',
        'service_variant_id',
        'adjustment_type',
        'adjustment_value',
        'active',
    ];

    protected $casts = [
        'adjustment_value' => 'decimal:2',
        'active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Price List
    |--------------------------------------------------------------------------
    */

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(
            PriceList::class,
            'price_list_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Service Variant
    |--------------------------------------------------------------------------
    */

    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(
            ServiceVariant::class,
            'service_variant_id'
        );
    }
}