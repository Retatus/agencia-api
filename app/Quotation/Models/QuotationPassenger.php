<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Models\PassengerType;
use App\Models\Country;

use App\Traits\HasHistory;

class QuotationPassenger extends Model
{
    use HasUuids;
    use HasFactory;
    use SoftDeletes;
    use HasHistory;

    protected $table = 'quotation_passengers';

    protected $fillable = [
        'uuid',
        'quotation_id',
        'passenger_type_id',
        'first_name',
        'last_name',
        'birth_date',
        'document_number',
        'nationality',
        'email',
        'phone',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
    ];

    /**
     * Columnas UUID que Laravel debe generar automáticamente.
     */
    public function uniqueIds(): array
    {
        return [
            'uuid',
        ];
    }

    /**
     * Route Model Binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function passengerType()
    {
        return $this->belongsTo(PassengerType::class);
    }

    public function country()
    {
        return $this->belongsTo(
            Country::class,
            'nationality', // campo local en customers
            'iso'          // campo en countries
        );
    }

    /*
    |--------------------------------------------------------------------------
    | History, Audit devuelve el root entity quotation
    |--------------------------------------------------------------------------
    */

    protected function getHistoryRootEntityType(): string
    {
        return 'Quotation';
    }

    protected function getHistoryRootEntityUuid(): ?string
    {
        return $this->quotation?->uuid;
    }
}