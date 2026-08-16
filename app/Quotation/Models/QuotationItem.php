<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\ServiceVariant;
use App\Pricing\Price\Models\Price;
use App\Pricing\BasePrice\Models\BasePrice;
use App\Pricing\PriceList\Models\PriceList;
use App\Pricing\PriceListItem\Models\PriceListItem;

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

        'calculation_type',
        'group_uuid',
        'group_index',

        'name',
        'variant_name',
        'description',
        'duration',
        'quantity',
        'price_id',

        'base_price_id',
        'price_list_id',
        'price_list_item_id',

        'pricing_source',

        'base_cost',
        'base_price',

        'adjustment_type',
        'adjustment_value',

        'unit_cost',
        'unit_price',
        'subtotal',
        'sort_order',
        'notes',
        'active',
    ];  

    protected $casts = [
        'duration' => 'integer',

        'quantity' => 'decimal:2',

        'unit_cost' => 'decimal:2',

        'unit_price' => 'decimal:2',

        'subtotal' => 'decimal:2',

        'group_index' => 'integer',

        'sort_order' => 'integer',

        'active' => 'boolean',

        'calculated_at' => 'datetime',

        'base_cost' => 'decimal:2',
        'base_price' => 'decimal:2',
        'adjustment_value' => 'decimal:4',
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

    public function itinerary()
    {
        return $this->belongsTo(
            QuotationItinerary::class,
            'quotation_itinerary_id'
        );
    }

    public function basePrice(): BelongsTo
    {
        return $this->belongsTo(BasePrice::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function priceListItem(): BelongsTo
    {
        return $this->belongsTo(PriceListItem::class);
    }
    
     /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isGrouped(): bool
    {
        return ! empty(
            $this->group_uuid
        );
    }

    public function isAccommodation(): bool
    {
        return $this->calculation_type ===
            'accommodation';
    }

    public function isTransport(): bool
    {
        return $this->calculation_type ===
            'transport';
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
        return $this->resolveRootEntity()?->uuid;
    }

    protected function resolveRootEntity(): ?Quotation
    {
        return $this->quotationRoot ??=
            $this->itinerary()
                ->with('quotation:id,uuid')
                ->first()
                ?->quotation;
    }
}