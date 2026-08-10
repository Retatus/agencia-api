<?php

namespace App\Pricing\PriceList\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Currency;
use App\Pricing\Price\Models\Price;

class PriceList extends Model
{
    use HasActiveScope;

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