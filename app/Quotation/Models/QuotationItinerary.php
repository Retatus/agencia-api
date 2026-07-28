<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;  


use App\Traits\HasHistory;

class QuotationItinerary extends Model
{
    use HasUuids;
    use HasFactory;
    use SoftDeletes;
    use HasHistory;

    protected $table = 'quotation_itineraries';

    protected $fillable = [
      'uuid',
      'quotation_id',
      'day_number',
      'travel_date',
      'title',
      'description',
      'sort_order',
    ];

    protected $casts = [
        'travel_date' => 'date:Y-m-d',
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

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class,'quotation_itinerary_id');
    }
}