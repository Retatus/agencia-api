<?php

namespace App\Audit\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryResource extends JsonResource
{
    /**
     * Transformar el recurso en un arreglo.
     */
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Identificación
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,

            'batch_uuid' => $this->batch_uuid,

            'operation_uuid' => $this->operation_uuid,

            /*
            |--------------------------------------------------------------------------
            | Entidad raíz
            |--------------------------------------------------------------------------
            */

            'root_entity_type' => $this->root_entity_type,

            'root_entity_uuid' => $this->root_entity_uuid,

            /*
            |--------------------------------------------------------------------------
            | Entidad modificada
            |--------------------------------------------------------------------------
            */

            'entity_type' => $this->entity_type,

            'entity_id' => $this->entity_id,

            'entity_uuid' => $this->entity_uuid,

            /*
            |--------------------------------------------------------------------------
            | Cambio realizado
            |--------------------------------------------------------------------------
            */

            'action' => $this->action,

            'field' => $this->field,

            'path' => $this->path,

            'old_value' => $this->formatHistoryValue(
                $this->old_value
            ),

            'new_value' => $this->formatHistoryValue(
                $this->new_value
            ),

            /*
            |--------------------------------------------------------------------------
            | Usuario
            |--------------------------------------------------------------------------
            */

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),

            /*
            |--------------------------------------------------------------------------
            | Información adicional
            |--------------------------------------------------------------------------
            */

            'description' => $this->description,

            'ip_address' => $this->ip_address,

            /*
            |--------------------------------------------------------------------------
            | Fechas
            |--------------------------------------------------------------------------
            */

            'created_at' => $this->created_at?->toDateTimeString(),

        ];
    }

    private function formatHistoryValue(
        mixed $value
    ): mixed {
        if (is_array($value)) {
            return collect($value)
                ->map(
                    fn (mixed $item) =>
                        $this->formatHistoryValue($item)
                )
                ->all();
        }

        if (
            is_string($value) &&
            preg_match(
                '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})/',
                $value,
                $parts
            )
        ) {
            return sprintf(
                '%s-%s-%s %s:%s:%s',
                $parts[1],
                $parts[2],
                $parts[3],
                $parts[4],
                $parts[5],
                $parts[6]
            );
        }

        return $value;
    }
}
