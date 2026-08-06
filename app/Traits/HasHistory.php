<?php

namespace App\Traits;

use App\Audit\Models\History;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

trait HasHistory
{
    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    protected static function bootHasHistory(): void
    {
        static::created(function ($model) {
            $model->recordCreatedHistory();
        });

        static::updated(function ($model) {
            $model->recordUpdatedHistory();
        });

        static::deleted(function ($model) {
            $model->recordDeletedHistory();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CREATED
    |--------------------------------------------------------------------------
    */

    protected function recordCreatedHistory(): void
    {
        History::create([
            /*
            |------------------------------------------------------------------
            | Operation
            |------------------------------------------------------------------
            */

            'batch_uuid' => $this->getHistoryBatchUuid(),

            /*
            |------------------------------------------------------------------
            | Root Entity
            |------------------------------------------------------------------
            */

            'root_entity_type' => $this->getHistoryRootEntityType(),
            'root_entity_uuid' => $this->getHistoryRootEntityUuid(),

            /*
            |------------------------------------------------------------------
            | Entity
            |------------------------------------------------------------------
            */

            'entity_type' => $this->getHistoryEntityType(),
            'entity_id' => $this->getHistoryEntityId(),
            'entity_uuid' => $this->getHistoryEntityUuid(),

            /*
            |------------------------------------------------------------------
            | Change
            |------------------------------------------------------------------
            */

            'field' => null,
            'path' => null,

            'old_value' => null,
            'new_value' => $this->getHistorySnapshot(),

            'action' => 'created',

            /*
            |------------------------------------------------------------------
            | User
            |------------------------------------------------------------------
            */

            'user_id' => Auth::id(),

            /*
            |------------------------------------------------------------------
            | Additional Information
            |------------------------------------------------------------------
            */

            'description' => $this->getHistoryDescription(
                'created'
            ),

            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATED
    |--------------------------------------------------------------------------
    */

    
    protected function recordUpdatedHistory(): void
    {
        $changes = $this->getChanges();

        if (empty($changes)) {
            return;
        }

        foreach ($changes as $field => $newValue) {

            if ($this->shouldIgnoreHistoryField($field)) {
                continue;
            }

            $oldValue = $this->getOriginal($field);

            /*
            |--------------------------------------------------------------------------
            | Comparación normalizada
            |--------------------------------------------------------------------------
            |
            | Evita registrar falsos cambios como:
            |
            | "0.00" vs 0
            | "2.00"  vs 2
            | "10.50" vs 10.5
            |
            */

            if (
                $this->historyValuesAreEqual(
                    $oldValue,
                    $newValue,
                    $field
                )
            ) {
                continue;
            }

            History::create([
                /*
                |--------------------------------------------------------------------------
                | Operation
                |--------------------------------------------------------------------------
                */

                'batch_uuid' => $this->getHistoryBatchUuid(),

                /*
                |--------------------------------------------------------------------------
                | Root Entity
                |--------------------------------------------------------------------------
                */

                'root_entity_type' => $this->getHistoryRootEntityType(),
                'root_entity_uuid' => $this->getHistoryRootEntityUuid(),

                /*
                |--------------------------------------------------------------------------
                | Entity
                |--------------------------------------------------------------------------
                */

                'entity_type' => $this->getHistoryEntityType(),
                'entity_id' => $this->getHistoryEntityId(),
                'entity_uuid' => $this->getHistoryEntityUuid(),

                /*
                |--------------------------------------------------------------------------
                | Change
                |--------------------------------------------------------------------------
                */

                'field' => $field,
                'path' => $this->getHistoryPath($field),

                'old_value' => $this->normalizeHistoryValue(
                    $oldValue,
                    $field
                ),

                'new_value' => $this->normalizeHistoryValue(
                    $newValue,
                    $field
                ),

                'action' => 'updated',

                /*
                |--------------------------------------------------------------------------
                | User
                |--------------------------------------------------------------------------
                */

                'user_id' => Auth::id(),

                /*
                |--------------------------------------------------------------------------
                | Additional Information
                |--------------------------------------------------------------------------
                */

                'description' => $this->getHistoryDescription(
                    'updated',
                    $field
                ),

                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        }
    }

    protected function historyValuesAreEqual(
        mixed $oldValue,
        mixed $newValue,
        string $field
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | Obtener tipo de cast del modelo
        |--------------------------------------------------------------------------
        */

        $castType = $this->getCasts()[$field] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Campos decimales
        |--------------------------------------------------------------------------
        |
        | Laravel puede tener:
        |
        | "2.00"
        | 2
        | 2.0
        |
        | Todos representan el mismo valor.
        |
        */

        if (
            $castType &&
            str_starts_with($castType, 'decimal')
        ) {
            return bccomp(
                (string) $oldValue,
                (string) $newValue,
                $this->getDecimalScale($castType)
            ) === 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Valores numéricos
        |--------------------------------------------------------------------------
        */

        if (
            is_numeric($oldValue) &&
            is_numeric($newValue)
        ) {
            return (float) $oldValue === (float) $newValue;
        }

        /*
        |--------------------------------------------------------------------------
        | Valores normales
        |--------------------------------------------------------------------------
        */

        return $oldValue === $newValue;
    }

    protected function normalizeHistoryValue(
        mixed $value,
        string $field
    ): mixed {
        $castType = $this->getCasts()[$field] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Decimal
        |--------------------------------------------------------------------------
        */

        if (
            $castType &&
            str_starts_with($castType, 'decimal')
        ) {
            return number_format(
                (float) $value,
                $this->getDecimalScale($castType),
                '.',
                ''
            );
        }

        return $value;
    }

    protected function getDecimalScale(
        string $castType
    ): int {
        /*
        | Ejemplo:
        |
        | decimal:2
        |         ↑
        |         escala
        */

        if (
            !str_contains($castType, ':')
        ) {
            return 2;
        }

        return (int) explode(
            ':',
            $castType
        )[1];
    }

    /*
    |--------------------------------------------------------------------------
    | DELETED
    |--------------------------------------------------------------------------
    */

    protected function recordDeletedHistory(): void
    {
        History::create([
            /*
            |------------------------------------------------------------------
            | Operation
            |------------------------------------------------------------------
            */

            'batch_uuid' => $this->getHistoryBatchUuid(),

            /*
            |------------------------------------------------------------------
            | Root Entity
            |------------------------------------------------------------------
            */

            'root_entity_type' => $this->getHistoryRootEntityType(),
            'root_entity_uuid' => $this->getHistoryRootEntityUuid(),

            /*
            |------------------------------------------------------------------
            | Entity
            |------------------------------------------------------------------
            */

            'entity_type' => $this->getHistoryEntityType(),
            'entity_id' => $this->getHistoryEntityId(),
            'entity_uuid' => $this->getHistoryEntityUuid(),

            /*
            |------------------------------------------------------------------
            | Change
            |------------------------------------------------------------------
            */

            'field' => null,
            'path' => null,

            'old_value' => $this->getHistorySnapshot(),
            'new_value' => null,

            'action' => 'deleted',

            /*
            |------------------------------------------------------------------
            | User
            |------------------------------------------------------------------
            */

            'user_id' => Auth::id(),

            /*
            |------------------------------------------------------------------
            | Additional Information
            |------------------------------------------------------------------
            */

            'description' => $this->getHistoryDescription(
                'deleted'
            ),

            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ROOT ENTITY
    |--------------------------------------------------------------------------
    */

    protected function getHistoryRootEntityType(): string
    {
        return $this->getHistoryEntityType();
    }

    protected function getHistoryRootEntityUuid(): ?string
    {
        return $this->getHistoryEntityUuid();
    }

    /*
    |--------------------------------------------------------------------------
    | ENTITY
    |--------------------------------------------------------------------------
    */

    protected function getHistoryEntityType(): string
    {
        return class_basename($this);
    }

    protected function getHistoryEntityId(): ?int
    {
        return $this->getKey();
    }

    protected function getHistoryEntityUuid(): ?string
    {
        return $this->uuid ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | BATCH
    |--------------------------------------------------------------------------
    */

    protected function getHistoryBatchUuid(): string
    {
        if (
            app()->bound('history.batch_uuid')
        ) {
            return app('history.batch_uuid');
        }

        /*
        |----------------------------------------------------------------------
        | Operación independiente
        |----------------------------------------------------------------------
        |
        | Si no existe un batch definido explícitamente, se genera uno nuevo.
        | Esto permite que operaciones simples sigan teniendo su propio
        | identificador de operación.
        |
        */

        return (string) Str::uuid();
    }

    /*
    |--------------------------------------------------------------------------
    | PATH
    |--------------------------------------------------------------------------
    */

    protected function getHistoryPath(
        string $field
    ): string {
        return strtolower(
            $this->getHistoryEntityType()
        ) . '.' . $field;
    }

    /*
    |--------------------------------------------------------------------------
    | SNAPSHOT
    |--------------------------------------------------------------------------
    */

    protected function getHistorySnapshot(): array
    {
        return $this->attributesToArray();
    }

    /*
    |--------------------------------------------------------------------------
    | IGNORED FIELDS
    |--------------------------------------------------------------------------
    */

    protected function shouldIgnoreHistoryField(
        string $field
    ): bool {
        return in_array(
            $field,
            [
                'created_at',
                'updated_at',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESCRIPTION
    |--------------------------------------------------------------------------
    */

    protected function getHistoryDescription(
        string $action,
        ?string $field = null
    ): string {

        $entity = $this->getHistoryEntityType();

        return match ($action) {

            'created' =>
                "Created {$entity}",

            'updated' =>
                "Updated {$field} in {$entity}",

            'deleted' =>
                "Deleted {$entity}",

            default =>
                "Action {$action} on {$entity}",
        };
    }
}