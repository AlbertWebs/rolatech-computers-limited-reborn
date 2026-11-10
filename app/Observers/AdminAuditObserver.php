<?php

namespace App\Observers;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AdminAuditObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created', array_keys($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $this->record($model, 'updated', array_keys($model->getChanges()));
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', []);
    }

    /** @param list<string> $fields */
    private function record(Model $model, string $event, array $fields): void
    {
        $actor = Auth::user();
        if (! $actor?->is_admin || ! $actor->is_active) {
            return;
        }
        $fields = array_values(array_diff($fields, ['created_at', 'updated_at', 'remember_token', 'password']));

        AdminAuditLog::query()->create([
            'actor_id' => $actor->id,
            'event' => $event,
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'changed_fields' => $fields,
        ]);
    }
}
