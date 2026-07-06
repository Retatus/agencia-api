<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\PassengerType;

class QuotationPassenger extends Model
{
    use HasFactory;

    protected $table = 'quotation_passengers';

    protected $fillable = [
        'quotation_id',
        'passenger_type_id',
        'first_name',
        'last_name',
        'birth_date',
        'document_number',
        'nationality',
        'email',
        'phone',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function passengerType()
    {
        return $this->belongsTo(PassengerType::class);
    }
}