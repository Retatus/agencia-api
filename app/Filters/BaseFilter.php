<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseFilter
{
    protected Builder $query;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        // Ejecuta métodos que coincidan con los parámetros de la URL
        foreach ($this->request->query() as $key => $value) {

            if (
                method_exists($this, $key) &&
                $value !== null &&
                $value !== ''
            ) {
                $this->{$key}($value);
            }
        }

        // Permite lógica adicional del filtro
        $this->boot();

        return $this->query;
    }

    /**
     * Cada filtro puede sobrescribir este método
     * para ejecutar lógica adicional.
     */
    protected function boot(): void
    {
        //
    }
}