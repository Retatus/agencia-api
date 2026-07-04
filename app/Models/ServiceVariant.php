<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceVariant extends Model
{
    protected $table = 'service_variants';

    protected $fillable = [
        'service_id',
        'code',
        'name',
        'min_capacity',
        'max_capacity',
        'optimal_capacity',
        'unit_type',
        'duration',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
