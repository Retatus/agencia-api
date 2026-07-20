<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\ServiceVariant;
use App\Models\Price;

class QuotationItem extends Model
{
    use HasFactory;

    protected $table = 'quotation_items';

    protected $fillable = [
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