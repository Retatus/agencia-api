<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuotationStatus extends Model
{
    use HasFactory;

    protected $table = 'quotation_statuses';

    protected $fillable = [
        'code',
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }
}