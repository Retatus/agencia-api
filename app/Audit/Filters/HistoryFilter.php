<?php

namespace App\Audit\Filters;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class HistoryFilter extends BaseFilter
{
    /**
     * Aplicar filtros.
     */
    public function apply(Builder $query): Builder
    {
        return $query
            ->when(
                $this->request->filled('root_entity_type'),
                fn ($q) => $this->rootEntityType($q)
            )
            ->when(
                $this->request->filled('root_entity_uuid'),
                fn ($q) => $this->rootEntityUuid($q)
            )
            ->when(
                $this->request->filled('entity_type'),
                fn ($q) => $this->entityType($q)
            )
            ->when(
                $this->request->filled('entity_uuid'),
                fn ($q) => $this->entityUuid($q)
            )
            ->when(
                $this->request->filled('action'),
                fn ($q) => $this->action($q)
            )
            ->when(
                $this->request->filled('field'),
                fn ($q) => $this->field($q)
            )
            ->when(
                $this->request->filled('batch_uuid'),
                fn ($q) => $this->batch($q)
            )
            ->when(
                $this->request->filled('operation_uuid'),
                fn ($q) => $this->operation($q)
            )
            ->when(
                $this->request->filled('user_id'),
                fn ($q) => $this->user($q)
            )
            ->when(
                $this->request->filled('date_from'),
                fn ($q) => $this->dateFrom($q)
            )
            ->when(
                $this->request->filled('date_to'),
                fn ($q) => $this->dateTo($q)
            );
    }

    protected function rootEntityType(Builder $query): void
    {
        $query->where(
            'root_entity_type',
            $this->request->root_entity_type
        );
    }

    protected function rootEntityUuid(Builder $query): void
    {
        $query->where(
            'root_entity_uuid',
            $this->request->root_entity_uuid
        );
    }

    protected function entityType(Builder $query): void
    {
        $query->where(
            'entity_type',
            $this->request->entity_type
        );
    }

    protected function entityUuid(Builder $query): void
    {
        $query->where(
            'entity_uuid',
            $this->request->entity_uuid
        );
    }

    protected function action(Builder $query): void
    {
        $query->where(
            'action',
            $this->request->action
        );
    }

    protected function field(Builder $query): void
    {
        $query->where(
            'field',
            $this->request->field
        );
    }

    protected function batch(Builder $query): void
    {
        $query->where(
            'batch_uuid',
            $this->request->batch_uuid
        );
    }

    protected function operation(Builder $query): void
    {
        $query->where(
            'operation_uuid',
            $this->request->operation_uuid
        );
    }

    protected function user(Builder $query): void
    {
        $query->where(
            'user_id',
            $this->request->user_id
        );
    }

    protected function dateFrom(Builder $query): void
    {
        $query->whereDate(
            'created_at',
            '>=',
            $this->request->date_from
        );
    }

    protected function dateTo(Builder $query): void
    {
        $query->whereDate(
            'created_at',
            '<=',
            $this->request->date_to
        );
    }
}