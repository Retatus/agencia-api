<?php

namespace App\Pricing\PriceList\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Models\Currency;
use App\Pricing\Price\Models\Price;
use App\Models\ServiceCategory;
use App\Pricing\PriceListItem\Models\PriceListItem;

class PriceList extends Model
{
    use HasActiveScope;
    use HasUuids;

    protected $table = 'price_lists';

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'currency_id',
        'valid_from',
        'valid_to',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Indica qué columnas deben generarse automáticamente como UUID.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class,'price_list_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCurrent($query)
    {
        return $query
            ->whereDate('valid_from', '<=', now())
            ->whereDate('valid_to', '>=', now());
    }
}