<?php

namespace App\Filters\CRM;

use App\Filters\BaseFilter;

class CustomerFilter extends BaseFilter
{
    /**
     * Lógica adicional que siempre se ejecuta.
     */
    protected function boot(): void
    {
        $this->search();
        $this->sorting();
    }

    /**
     * Búsqueda general.
     *
     * ?search=juan
     */
    protected function search(): void
    {
        $search = $this->request->input('search');

        if (blank($search)) {
            return;
        }

        $this->query->where(function ($query) use ($search) {
            $query->where('document_number', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Tipo de documento.
     *
     * ?document_type_id=1
     */
    public function document_type_id($value): void
    {
        $this->query->where(
            'document_type_id',
            $value
        );
    }

    /**
     * Documento.
     *
     * ?document_number=12345678
     */
    public function document_number($value): void
    {
        $this->query->where(
            'document_number',
            'like',
            "%{$value}%"
        );
    }

    /**
     * Nombre.
     *
     * ?first_name=juan
     */
    public function first_name($value): void
    {
        $this->query->where(
            'first_name',
            'like',
            "%{$value}%"
        );
    }

    /**
     * Apellidos.
     *
     * ?last_name=perez
     */
    public function last_name($value): void
    {
        $this->query->where(
            'last_name',
            'like',
            "%{$value}%"
        );
    }

    /**
     * Email.
     *
     * ?email=test@test.com
     */
    public function email($value): void
    {
        $this->query->where(
            'email',
            'like',
            "%{$value}%"
        );
    }

    /**
     * Ciudad.
     *
     * ?city=Lima
     */
    public function city($value): void
    {
        $this->query->where(
            'city',
            'like',
            "%{$value}%"
        );
    }

    /**
     * País.
     *
     * ?country=Perú
     */
    public function country($value): void
    {
        $this->query->where(
            'country',
            'like',
            "%{$value}%"
        );
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
     * Ordenamiento.
     *
     * ?sort=first_name&direction=desc
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

        if (! in_array($sort, $allowed)) {
            $sort = 'last_name';
        }

        $direction = strtolower(
            $this->request->input('direction', 'asc')
        );

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $this->query->orderBy($sort, $direction);
    }
}
