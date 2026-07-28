<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Models\ServiceVariant;
use App\Models\Price;

use App\Traits\HasHistory;

class QuotationItem extends Model
{
    use HasUuids;
    use HasFactory;
    use SoftDeletes;
    use HasHistory;

    protected $table = 'quotation_items';

    protected $fillable = [
        'uuid',
        'quotation_itinerary_id',
        'service_id',
        'service_variant_id',
        'item_type',
        'name',
        'variant_name',
        'description',
        'duration',
        'quantity',
        'price_id',
        'unit_cost',
        'unit_price',
        'subtotal',
        'sort_order',
        'notes',
        'active',
    ];

    protected $casts = [
        'service_date' => 'date',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_price' => 'decimal:2',
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

    public function serviceVariant()
    {
        return $this->belongsTo(ServiceVariant::class);
    }

    public function price()
    {
        return $this->belongsTo(Price::class);
    }
}