<?php

namespace App\Audit\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class History extends Model
{
    protected $table = 'histories';

    protected $fillable = [
        'batch_uuid',

        'entity_type',
        'entity_id',
        'entity_uuid',

        'field',
        'path',

        'old_value',
        'new_value',

        'action',

        'user_id',

        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_value' => 'json',
        'new_value' => 'json',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * User who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}