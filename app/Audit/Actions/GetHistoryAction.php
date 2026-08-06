<?php

namespace App\Audit\Actions;

use App\Audit\Models\History;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetHistoryAction
{
    /**
     * Obtener el historial completo de una entidad.
     *
     * Recupera todos los registros asociados a una entidad raíz,
     * incluyendo los cambios realizados sobre sus entidades hijas.
     */
    public function execute(
        string $entityType,
        string $entityUuid,
        int $perPage = 50
    ): LengthAwarePaginator {

        return History::query()

            ->where('root_entity_type', $entityType)
            ->where('root_entity_uuid', $entityUuid)

            ->orderByDesc('created_at')
            ->orderByDesc('id')

            ->paginate($perPage);
    }
}