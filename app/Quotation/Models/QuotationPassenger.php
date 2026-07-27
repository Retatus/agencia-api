<?php

namespace App\Quotation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\PassengerType;

use App\Traits\HasHistory;

class QuotationPassenger extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasHistory;

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
        'birth_date' => 'date:Y-m-d',
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