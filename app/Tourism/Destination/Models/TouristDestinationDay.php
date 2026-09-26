<?php

namespace App\Tourism\Destination\Models;

use Illuminate\Database\Eloquent\Model;

class TouristDestinationDay extends Model
{
    protected $fillable = [
        'tourist_destination_id',
        'day_number',
        'title',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'sort_order' => 'integer',
    ];

    public function destination()
    {
        return $this->belongsTo(TouristDestination::class, 'tourist_destination_id');
    }

    public function items()
    {
        return $this->hasMany(TouristDestinationItem::class)
            ->orderBy('sort_order');
    }
}
