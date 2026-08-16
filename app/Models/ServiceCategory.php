<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Pricing\PriceList\Models\PriceList;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class, 'service_category_id');
    }
}
