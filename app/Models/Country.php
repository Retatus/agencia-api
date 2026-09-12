<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'iso',
        'phone_code',
        'flag',
        'active',
    ];
    
    protected $casts = [
        'active' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function customers()
    {
        return $this->hasMany(
            \App\Models\CRM\Customer::class,
            'nationality',
            'iso'
        );
    }    
}
