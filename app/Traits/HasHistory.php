<?php

namespace App\Traits;

use App\Audit\Models\History;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

trait HasHistory
{
    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    protected static function bootHasHistory(): void
    {
        static::created(function (Model $model) {
            $model->recordCreatedHistory();
        });

        static::updated(function (Model $model) {
            $model->recordUpdatedHistory();
        });

        static::deleted(function (Model $model) {
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
            'batch_uuid' => $this->getHistoryBatchUuid(),

            'entity_type' => $this->getHistoryEntityType(),
            'entity_id' => $this->getHistoryEntityId(),
            'entity_uuid' => $this->getHistoryEntityUuid(),

            'field' => null,
            'path' => null,

            'old_value' => null,
            'new_value' => $this->getHistorySnapshot(),

            'action' => 'created',

            'user_id' => Auth::id(),

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
        $changes = $this->getDirty();

        if (empty($changes)) {
            return;
        }

        foreach ($changes as $field => $newValue) {

            if ($this->shouldIgnoreHistoryField($field)) {
                continue;
            }

            $oldValue = $this->getOriginal($field);

            // Evitar registrar cambios que realmente no representan una modificación.
            if (
                $this->historyValuesAreEqual(
                    $oldValue,
                    $newValue
                )
            ) {
                continue;
            }

            History::create([
                'batch_uuid' => $this->getHistoryBatchUuid(),

                'entity_type' => $this->getHistoryEntityType(),
                'entity_id' => $this->getHistoryEntityId(),
                'entity_uuid' => $this->getHistoryEntityUuid(),

                'field' => $field,
                'path' => $this->getHistoryPath($field),

                'old_value' => $this->normalizeHistoryValue($oldValue),
                'new_value' => $this->normalizeHistoryValue($newValue),

                'action' => 'updated',

                'user_id' => Auth::id(),

                'description' => $this->getHistoryDescription(
                    'updated',
                    $field
                ),

                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETED
    |--------------------------------------------------------------------------
    */

    protected function recordDeletedHistory(): void
    {
        History::create([
            'batch_uuid' => $this->getHistoryBatchUuid(),

            'entity_type' => $this->getHistoryEntityType(),
            'entity_id' => $this->getHistoryEntityId(),
            'entity_uuid' => $this->getHistoryEntityUuid(),

            'field' => null,
            'path' => null,

            'old_value' => $this->getHistorySnapshot(),
            'new_value' => null,

            'action' => 'deleted',

            'user_id' => Auth::id(),

            'description' => $this->getHistoryDescription(
                'deleted'
            ),

            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
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

    protected function getHistoryEntityId(): mixed
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
    |
    | Permite agrupar múltiples cambios realizados dentro de
    | una misma operación.
    |
    | Ejemplo:
    |
    | Actualizar Cotización
    |
    | batch_uuid: ABC-123
    |
    |   Quotation
    |       -> updated
    |
    |   QuotationItinerary
    |       -> created
    |
    |   QuotationItem
    |       -> updated
    |
    |   QuotationItem
    |       -> deleted
    |
    |   QuotationPassenger
    |       -> created
    |
    | Todos compartirán el mismo batch_uuid.
    |
    */

    protected function getHistoryBatchUuid(): string
    {
        if (app()->bound('history.batch_uuid')) {
            return app('history.batch_uuid');
        }

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
    | NORMALIZE VALUE
    |--------------------------------------------------------------------------
    |
    | Normaliza valores complejos antes de almacenarlos.
    |
    | Si old_value/new_value son TEXT:
    |
    | array/object -> JSON
    |
    */

    protected function normalizeHistoryValue(
        mixed $value
    ): mixed {
        if ($value === null) {
            return null;
        }

        if (
            is_array($value) ||
            is_object($value)
        ) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );
        }

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | COMPARE VALUES
    |--------------------------------------------------------------------------
    |
    | Evita registrar cambios falsos.
    |
    | Ejemplo:
    |
    | "100" y 100
    |
    | Se consideran equivalentes.
    |
    */

    protected function historyValuesAreEqual(
        mixed $oldValue,
        mixed $newValue
    ): bool {
        
        // Valores numéricos.
        if (is_numeric($oldValue) && is_numeric($newValue)
        ) {
            return (float) $oldValue === (float) $newValue;
        }

        // Arrays.
        if (is_array($oldValue) && is_array($newValue)
        ) {
            return $oldValue === $newValue;
        }

        // Comparación normal.
        return $oldValue === $newValue;
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