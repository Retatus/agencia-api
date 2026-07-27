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
        $changes = $this->getChanges();

        if (empty($changes)) {
            return;
        }

        foreach ($changes as $field => $newValue) {

            if ($this->shouldIgnoreHistoryField($field)) {
                continue;
            }

            $oldValue = $this->getOriginal($field);

            History::create([
                'batch_uuid' => $this->getHistoryBatchUuid(),

                'entity_type' => $this->getHistoryEntityType(),
                'entity_id' => $this->getHistoryEntityId(),
                'entity_uuid' => $this->getHistoryEntityUuid(),

                'field' => $field,
                'path' => $this->getHistoryPath($field),

                'old_value' => $oldValue,
                'new_value' => $newValue,

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