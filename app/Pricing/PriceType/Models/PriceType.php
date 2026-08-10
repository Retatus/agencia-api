<?php

namespace App\Pricing\PriceType\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Pricing\Price\Models\Price;

class PriceType extends Model
{
    use HasActiveScope;

    protected $table = 'price_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'active',
    ];

    protected $casts = [
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

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }
}