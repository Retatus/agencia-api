<?php

namespace App\Pricing\BasePrice\Models;

use App\Models\Currency;
use App\Models\ServiceVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasePrice extends Model
{
    protected $fillable = [
        'service_variant_id',
        'currency_id',
        'cost',
        'sale_price',
        'active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(
            ServiceVariant::class,
            'service_variant_id'
        );
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(
            Currency::class,
            'currency_id'
        );
    }
}