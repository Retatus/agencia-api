<?php

namespace App\Filters\Pricing;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class PriceTypeFilter extends BaseFilter
{
    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        $this->search();
        $this->active();
        $this->sorting();

        return $this->query;
    }

    /**
     * Buscar por código o nombre.
     */
    protected function search(): void
    {
        $search = $this->request->input('search');

        if (blank($search)) {
            return;
        }

        $this->query->where(function ($q) use ($search) {

            $q->where('code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");

        });
    }

    /**
     * Filtrar por estado.
     */
    protected function active(): void
    {
        if (!$this->request->has('active')) {
            return;
        }

        $this->query->where(
            'active',
            $this->request->boolean('active')
        );
    }

    /**
     * Ordenamiento.
     */
    protected function sorting(): void
    {
        $allowed = [
            'code',
            'name',
            'created_at'
        ];

        $sort = $this->request->input('sort', 'name');

        if (!in_array($sort, $allowed)) {
            $sort = 'name';
        }

        $direction = strtolower(
            $this->request->input('direction', 'asc')
        );

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $this->query->orderBy(
            $sort,
            $direction
        );
    }
}