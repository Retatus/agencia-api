<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'providers';

    protected $fillable = [
        'code',
        'business_name',
        'commercial_name',
        'document_type_id',
        'document_number',
        'tax_name',
        'email',
        'phone',
        'website',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Tipo de documento del proveedor.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Servicios del proveedor.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Solo activos.
     */
    public function scopeActive(
        Builder $query,
        bool $active = true
    ): Builder {

        return $query->where('active', $active);

    }

    /**
     * Búsqueda rápida.
     */
    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {

        if (blank($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {

            $q  ->where('business_name', 'like', "%{$search}%")
                ->orWhere('commercial_name', 'like', "%{$search}%")
                ->orWhere('document_number', 'like', "%{$search}%")
                ->orWhere('tax_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });

    }

    /*
    |--------------------------------------------------------------------------
    | ROUTE MODEL BINDING
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}