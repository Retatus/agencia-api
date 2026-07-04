<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasActiveScope;

    protected $table = 'prices';

    protected $fillable = [
        'price_list_id',
        'service_variant_id',
        'price_type_id',
        'passenger_type_id',
        'min_quantity',
        'max_quantity',
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

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(ServiceVariant::class);
    }

    public function priceType(): BelongsTo
    {
        return $this->belongsTo(PriceType::class);
    }

    public function passengerType(): BelongsTo
    {
        return $this->belongsTo(PassengerType::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByPriceList($query, int $priceListId)
    {
        return $query->where('price_list_id', $priceListId);
    }

    public function scopeByVariant($query, int $variantId)
    {
        return $query->where('service_variant_id', $variantId);
    }

    public function scopeByPassengerType($query, ?int $passengerTypeId)
    {
        if ($passengerTypeId === null) {
            return $query;
        }

        return $query->where(
            'passenger_type_id',
            $passengerTypeId
        );
    }

    public function scopeByPriceType($query, int $priceTypeId)
    {
        return $query->where(
            'price_type_id',
            $priceTypeId
        );
    }
}