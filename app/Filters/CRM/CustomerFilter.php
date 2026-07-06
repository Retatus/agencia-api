<?php

namespace App\Filters\CRM;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class CustomerFilter extends BaseFilter
{
    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        $this->search();
        $this->documentType();
        $this->active();
        $this->sorting();

        return $this->query;
    }

    /**
     * Buscar por múltiples campos.
     */
    protected function search(): void
    {
        $search = $this->request->input('search');

        if (blank($search)) {
            return;
        }

        $this->query->where(function ($q) use ($search) {
            $q->where('document_number', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
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
     * Filtrar por estado (activo/inactivo).
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
            'first_name',
            'last_name',
            'document_number',
            'email',
            'created_at'
        ];

        $sort = $this->request->input('sort', 'last_name');

        if (!in_array($sort, $allowed)) {
            $sort = 'last_name';
        }

        $direction = strtolower(
            $this->request->input('direction', 'asc')
        );

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $this->query->orderBy($sort, $direction);
    }
}
