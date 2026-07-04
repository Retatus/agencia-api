<?php

namespace App\Filters\Catalog;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class ProviderFilter extends BaseFilter
{
    public function apply(Builder $query): Builder
    {
        $this->query = $query;
        $this->search();
        $this->active();
        $this->documentType();
        $this->sorting();
        return $this->query;
    }

    /**
     * Buscar por texto.
     */
    protected function search(): void
    {
        $search = $this->request->input('search');

        if (blank($search)) {
            return;
        }

        $this->query->where(function ($q) use ($search) {
            $q->where('business_name', 'like', "%{$search}%")
              ->orWhere('commercial_name', 'like', "%{$search}%")
              ->orWhere('document_number', 'like', "%{$search}%")
              ->orWhere('tax_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
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
     * Filtrar por tipo de documento.
     */
    protected function documentType(): void
    {
        if (!$this->request->filled('document_type_id')) {
            return;
        }

        $this->query->where(
            'document_type_id',
            $this->request->integer('document_type_id')
        );
    }

    /**
     * Ordenamiento.
     */
    protected function sorting(): void
    {
        $allowed = [
            'business_name',
            'commercial_name',
            'document_number',
            'created_at'
        ];

        $sort = $this->request->input(
            'sort',
            'business_name'
        );

        if (!in_array($sort, $allowed)) {
            $sort = 'business_name';
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