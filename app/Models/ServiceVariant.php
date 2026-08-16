<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Pricing\Price\Models\Price;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Pricing\BasePrice\Models\BasePrice;
use App\Pricing\PriceListItem\Models\PriceListItem;

class ServiceVariant extends Model
{
    use HasActiveScope;

    protected $table = 'service_variants';

    protected $fillable = [
        'service_id',
        'code',
        'name',
        'min_capacity',
        'max_capacity',
        'optimal_capacity',
        'unit_type',
        'duration',
        'active',
    ];

    protected $casts = [
        'min_capacity'     => 'integer',
        'max_capacity'     => 'integer',
        'optimal_capacity' => 'integer',
        'duration'         => 'integer',
        'active'           => 'boolean',
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

    /**
     * Servicio al que pertenece la variante.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Precios definidos para esta variante.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function basePrice(): HasOne
    {
        return $this->hasOne(BasePrice::class);
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

    public function scopeByService($query, int $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeByUnitType($query, string $unitType)
    {
        return $query->where('unit_type', $unitType);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Nombre completo para mostrar en listas.
     * Ejemplo:
     * Hotel Libertador - Habitación Doble
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->service->name} - {$this->name}";
    }

    /**
     * Indica si la variante admite grupos.
     */
    public function getSupportsGroupsAttribute(): bool
    {
        return $this->max_capacity > 1;
    }
}