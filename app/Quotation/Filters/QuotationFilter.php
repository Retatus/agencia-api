<?php

namespace App\Quotation\Filters;

use App\Filters\BaseFilter;

class QuotationFilter extends BaseFilter
{
    /**
     * Lógica adicional del filtro.
     */
    protected function boot(): void
    {
        $this->search();
        $this->travelDate();
        $this->validUntil();
        $this->sorting();
    }

    /**
     * Búsqueda general.
     *
     * ?search=COT-0001
     */
    protected function search(): void
    {
        $search = $this->request->input('search');

        if (blank($search)) {
            return;
        }

        $this->query->where(function ($query) use ($search) {

            $query->where('code', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    /**
     * Cliente.
     *
     * ?customer_id=5
     */
    public function customer_id($value): void
    {
        $this->query->where('customer_id', $value);
    }

    /**
     * Estado.
     *
     * ?quotation_status_id=2
     */
    public function quotation_status_id($value): void
    {
        $this->query->where('quotation_status_id', $value);
    }

    /**
     * Lista de precios.
     *
     * ?price_list_id=1
     */
    public function price_list_id($value): void
    {
        $this->query->where('price_list_id', $value);
    }

    /**
     * Moneda.
     *
     * ?currency_id=1
     */
    public function currency_id($value): void
    {
        $this->query->where('currency_id', $value);
    }

    /**
     * Activo.
     *
     * ?active=true
     */
    public function active($value): void
    {
        $this->query->where(
            'active',
            filter_var($value, FILTER_VALIDATE_BOOLEAN)
        );
    }

    /**
     * Rango de fecha de viaje.
     *
     * ?travel_date_from=2026-01-01
     * ?travel_date_to=2026-12-31
     */
    protected function travelDate(): void
    {
        if ($this->request->filled('travel_date_from')) {
            $this->query->whereDate(
                'travel_date',
                '>=',
                $this->request->travel_date_from
            );
        }

        if ($this->request->filled('travel_date_to')) {
            $this->query->whereDate(
                'travel_date',
                '<=',
                $this->request->travel_date_to
            );
        }
    }

    /**
     * Rango de vigencia.
     *
     * ?valid_until_from=2026-01-01
     * ?valid_until_to=2026-12-31
     */
    protected function validUntil(): void
    {
        if ($this->request->filled('valid_until_from')) {
            $this->query->whereDate(
                'valid_until',
                '>=',
                $this->request->valid_until_from
            );
        }

        if ($this->request->filled('valid_until_to')) {
            $this->query->whereDate(
                'valid_until',
                '<=',
                $this->request->valid_until_to
            );
        }
    }

    /**
     * Ordenamiento.
     */
    protected function sorting(): void
    {
        $allowed = [
            'code',
            'travel_date',
            'valid_until',
            'total',
            'created_at',
        ];

        $sort = $this->request->input('sort', 'created_at');

        if (! in_array($sort, $allowed)) {
            $sort = 'created_at';
        }

        $direction = strtolower(
            $this->request->input('direction', 'desc')
        );

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $this->query->orderBy($sort, $direction);
    }
}