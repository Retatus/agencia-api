<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\ServiceVariant;

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

        /*
        |--------------------------------------------------------------------------
        | Calculation Engine
        |--------------------------------------------------------------------------
        */

        'calculation_type',
        'group_uuid',
        'group_index',

        /*
        |--------------------------------------------------------------------------
        | Descripción
        |--------------------------------------------------------------------------
        */

        'name',
        'variant_name',
        'description',

        'duration',
        'quantity',

        /*
        |--------------------------------------------------------------------------
        | Pricing Traceability
        |--------------------------------------------------------------------------
        */

        'base_price_id',
        'price_list_id',
        'price_list_item_id',

        'pricing_source',

        /*
        |--------------------------------------------------------------------------
        | Pricing Snapshot
        |--------------------------------------------------------------------------
        */

        'base_cost',
        'base_price',

        'adjustment_type',
        'adjustment_value',

        /*
        |--------------------------------------------------------------------------
        | Precio final
        |--------------------------------------------------------------------------
        */

        'unit_cost',
        'unit_price',
        'subtotal',

        /*
        |--------------------------------------------------------------------------
        | Otros
        |--------------------------------------------------------------------------
        */

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

        'base_cost' => 'decimal:2',
        'base_price' => 'decimal:2',

        'adjustment_value' => 'decimal:4',

        'group_index' => 'integer',
        'sort_order' => 'integer',

        'active' => 'boolean',

        'calculated_at' => 'datetime',
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

    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(
            ServiceVariant::class,
            'service_variant_id'
        );
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(
            QuotationItinerary::class,
            'quotation_itinerary_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pricing Relationships
    |--------------------------------------------------------------------------
    */

    public function basePrice(): BelongsTo
    {
        return $this->belongsTo(
            BasePrice::class,
            'base_price_id'
        );
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(
            PriceList::class,
            'price_list_id'
        );
    }

    public function priceListItem(): BelongsTo
    {
        return $this->belongsTo(
            PriceListItem::class,
            'price_list_item_id'
        );
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

    public function isManual(): bool
    {
        return $this->pricing_source ===
            'MANUAL';
    }

    public function usesPriceList(): bool
    {
        return $this->pricing_source ===
            'PRICE_LIST';
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