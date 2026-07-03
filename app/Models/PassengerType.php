<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PassengerType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}