<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveScope
{
    /**
     * Filtra por estado activo.
     */
    public function scopeActive(
        Builder $query,
        bool $active = true
    ): Builder {
        return $query->where('active', $active);
    }
}