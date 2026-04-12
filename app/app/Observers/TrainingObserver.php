<?php

namespace App\Observers;

use App\Models\Training;
use App\Models\TrainingAudit;
use Illuminate\Support\Facades\Auth;

class TrainingObserver
{
    /**
     * Handle the Training "created" event.
     */
    public function created(Training $training): void
    {
        $this->logAudit($training, 'create', [
            'title' => $training->title,
            'start_at' => $training->start_at?->toIso8601String(),
            'end_at' => $training->end_at?->toIso8601String(),
            'capacity' => $training->capacity,
            'price' => $training->price,
            'is_active' => $training->is_active,
        ]);
    }

    /**
     * Handle the Training "updated" event.
     */
    public function updated(Training $training): void
    {
        // Zisti čo sa zmenilo
        $changes = [];

        foreach ($training->getChanges() as $key => $newValue) {
            $oldValue = $training->getOriginal($key);

            // Preskoči timestamps
            if (in_array($key, ['created_at', 'updated_at'])) {
                continue;
            }

            $changes[$key] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        if (!empty($changes)) {
            $this->logAudit($training, 'update', $changes);
        }
    }

    /**
     * Handle the Training "deleted" event.
     */
    public function deleted(Training $training): void
    {
        $this->logAudit($training, 'delete', [
            'title' => $training->title,
            'start_at' => $training->start_at?->toIso8601String(),
            'end_at' => $training->end_at?->toIso8601String(),
        ]);
    }

    /**
     * Handle the Training "force deleted" event.
     */
    public function forceDeleted(Training $training): void
    {
        $this->logAudit($training, 'force_delete', [
            'title' => $training->title,
        ]);
    }

    /**
     * Log an audit record
     */
    private function logAudit(Training $training, string $action, array $meta = []): void
    {
        try {
            TrainingAudit::create([
                'training_id' => $training->id,
                'performed_by_user_id' => Auth::id(),
                'action' => $action,
                'meta' => $meta,
            ]);
        } catch (\Throwable $e) {
            // Log the error but don't break the application flow
            \Log::error('Failed to create training audit', [
                'training_id' => $training->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
