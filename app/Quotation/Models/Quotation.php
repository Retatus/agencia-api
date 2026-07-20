<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Models\CRM\Customer;
use App\Models\PriceList;
use App\Models\Currency;

class Quotation extends Model
{
    use HasUuids;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'quotations';

    protected $fillable = [
        'uuid',
        'code',
        'customer_id',
        'price_list_id',
        'currency_id',
        'quotation_status_id',
        'exchange_rate',
        'travel_date',
        'valid_until',
        'notes',
        'subtotal',
        'discount',
        'tax',
        'total',
        'active',
    ];

    protected $casts = [
        'travel_date'   => 'date:Y-m-d',
        'valid_until'   => 'date:Y-m-d',
        'exchange_rate' => 'decimal:6',
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'total'         => 'decimal:2',
        'active'        => 'boolean',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function status()
    {
        return $this->belongsTo(QuotationStatus::class, 'quotation_status_id');
    }

    public function passengers()
    {
        return $this->hasMany(QuotationPassenger::class);
    }

    public function itineraries()
    {
        return $this->hasMany(QuotationItinerary::class);
    }
}