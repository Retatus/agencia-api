<?php

namespace App\Tourism\Destination\Models;

use Illuminate\Database\Eloquent\Model;

class TouristDestinationItem extends Model
{
    protected $fillable = [
        'tourist_destination_day_id',
        'name',
        'description',
        'duration',
        'quantity',
        'estimated_cost',
        'estimated_price',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'duration' => 'integer',
        'quantity' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'sort_order' => 'integer',
        'active' => 'boolean',
    ];

    public function day()
    {
        return $this->belongsTo(TouristDestinationDay::class, 'tourist_destination_day_id');
    }
}
