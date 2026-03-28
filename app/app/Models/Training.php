<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Notifications\TrainingCancelledNotification;
use Illuminate\Support\Facades\Notification;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_type_id',
        'created_by_user_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'capacity',
        'price',
        'is_active',
        'canceled_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'capacity' => 'integer',
        'price' => 'integer',
        'is_active' => 'boolean',
        'canceled_at' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'training_registrations')
            ->wherePivot('status', 'active')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function trainingType(): BelongsTo
    {
        return $this->belongsTo(TrainingType::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TrainingRegistration::class);
    }

    public function cancelTraining(): void
    {
        \Log::info("cancelTraining() called for training ID: " . $this->id);

        // Skontrolovať, či je tréning aktívny
        if (!$this->is_active) {
            throw new \Exception('Len aktívne tréningy je možné zrušiť.');
        }

        // Označenie tréningu ako zrušeného
        $this->update([
            'is_active' => false,
            'canceled_at' => now(),
        ]);

        \Log::info("Training ID " . $this->id . " marked as canceled");

        // Vrátenie kreditov používateľom a zmena statusu registrácie
        foreach ($this->users as $user) {
            \Log::info("Processing user: " . $user->email . " for training: " . $this->id);

            // Vrátenie plných kreditov bez časového limitu
            $user->increment('credits', $this->price);

            // Zmena statusu registrácie na 'canceled' namiesto vymazania
            $this->users()->updateExistingPivot($user->id, ['status' => 'canceled']);

            // Odoslanie notifikácie o zrušení tréningu
            \Log::info("Sending notification to user: " . $user->email);
            $user->notify(new TrainingCancelledNotification($this));
        }

        \Log::info("cancelTraining() completed for training ID: " . $this->id);
    }
}
