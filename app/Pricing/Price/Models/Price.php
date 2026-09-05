<?php

namespace App\Pricing\Price\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Pricing\PriceType\Models\PriceType;
use App\Models\Currency;
use App\Models\PassengerType;
use App\Pricing\PriceListItem\Models\PriceListItem;
use App\Models\ServiceVariant;

class Price extends Model
{
    use HasActiveScope;

    protected $table = 'prices';

    protected $fillable = [
        'service_variant_id',
        'price_type_id',
        'passenger_type_id',
        'currency_id',
        'min_quantity',
        'max_quantity',
        'valid_from',
        'valid_to',
        'cost',
        'sale_price',
        'priority',
        'active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'valid_from' => 'date:Y-m-d',
        'valid_to' => 'date:Y-m-d',
        'priority' => 'integer',
        'active' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getServiceNameAttribute(): ?string
    {
        return $this->serviceVariant?->service?->name;
    }

    public function getVariantNameAttribute(): ?string
    {
        return $this->serviceVariant?->name;
    }

    public function getProviderNameAttribute(): ?string
    {
        return $this->serviceVariant?->service?->provider?->name;
    }

    public function getPassengerTypeNameAttribute(): ?string
    {
        return $this->passengerType?->name;
    }

    public function getPriceTypeNameAttribute(): ?string
    {
        return $this->priceType?->name;
    }
}
