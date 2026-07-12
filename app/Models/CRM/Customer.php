<?php

namespace App\Models\CRM;

use App\Models\DocumentType;
use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasUuids;
    use HasActiveScope;
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'uuid',
        'document_type_id',
        'document_number',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'nationality',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'notes',
        'active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
        'active'     => 'boolean',
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

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}