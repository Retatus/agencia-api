<?php

namespace App\Tourism\Destination\Models;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TouristDestination extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'currency_id',
        'duration_days',
        'active',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'active' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function days()
    {
        return $this->hasMany(TouristDestinationDay::class)
            ->orderBy('sort_order')
            ->orderBy('day_number');
    }
}
