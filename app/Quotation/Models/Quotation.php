<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Models\CRM\Customer;
use App\Models\Currency;

use App\Traits\HasHistory;

class Quotation extends Model
{
    public const CALCULATION_CURRENT = 'CURRENT';

    public const CALCULATION_DIRTY = 'DIRTY';

    use HasUuids;
    use HasFactory;
    use SoftDeletes;
    use HasHistory;

    protected $table = 'quotations';

    protected $fillable = [
        'uuid',
        'code',
        'customer_id',
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
        'calculation_status',
        'calculation_dirty_reasons',
        'pending_calculation_items',
        'calculated_at',
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
        'calculation_dirty_reasons' => 'array',
        'pending_calculation_items' => 'array',
        'calculated_at' => 'datetime:Y-m-d H:i:s',
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

    public function markCalculationDirty(string $reason): void
    {
        $keys = $this->itineraries()
            ->with(['items' => fn ($query) => $query
                ->where('item_type', 'CATALOG')
                ->where('active', true)])
            ->get()
            ->flatMap(fn ($itinerary) => $itinerary->items)
            ->map(fn ($item) => $item->group_uuid ?: $item->uuid)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($keys)) {
            return;
        }

        $this->update([
            'calculation_status' => self::CALCULATION_DIRTY,
            'calculation_dirty_reasons' => array_values(array_unique([
                ...($this->calculation_dirty_reasons ?? []),
                $reason,
            ])),
            'pending_calculation_items' => array_values(array_unique([
                ...($this->pending_calculation_items ?? []),
                ...$keys,
            ])),
            'calculated_at' => null,
        ]);
    }
}
