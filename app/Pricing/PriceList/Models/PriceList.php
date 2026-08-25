<?php

namespace App\Pricing\PriceList\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Models\Currency;

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
