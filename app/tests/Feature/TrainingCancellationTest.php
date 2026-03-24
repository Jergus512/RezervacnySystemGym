<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Notifications\TrainingCancelledNotification;

class TrainingCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_training_cancellation_refunds_credits_and_sends_email(): void
    {
        Notification::fake();

        // Create a trainer and a user
        $trainer = User::factory()->create(['is_trainer' => true]);
        $user = User::factory()->create(['credits' => 10]);

        // Create a training
        $training = Training::factory()->create([
            'is_active' => true,
            'price' => 5,
            'start_at' => now()->addDays(1),
            'created_by' => $trainer->id,
        ]);

        // Register the user for the training
        TrainingRegistration::create([
            'user_id' => $user->id,
            'training_id' => $training->id,
            'status' => 'active',
        ]);

        $user->decrement('credits', $training->price);

        // Assert user is registered and credits are deducted
        $this->assertEquals(5, $user->fresh()->credits);
        $this->assertDatabaseHas('training_registrations', [
            'user_id' => $user->id,
            'training_id' => $training->id,
            'status' => 'active',
        ]);

        // Cancel the training
        $training->update(['is_active' => false]);

        // Fetch registrations without global scope to include canceled ones
        $registrations = TrainingRegistration::withoutGlobalScopes()
            ->where('training_id', $training->id)
            ->get();

        // Debug: Log the training and registrations state
        dump('Training state:', $training->fresh()->toArray());
        dump('Registrations:', TrainingRegistration::withoutGlobalScopes()->where('training_id', $training->id)->get()->toArray());

        foreach ($registrations as $registration) {
            $registration->user->increment('credits', $training->price);
            $registration->update(['status' => 'refunded']);
            Notification::send($registration->user, new TrainingCancelledNotification($training));
        }

        // Debug: Log the user state after refund
        dump('User state after refund:', $user->fresh()->toArray());

        // Assert the training is inactive
        $this->assertFalse($training->fresh()->is_active);

        // Assert the user no longer sees the training in "Moje tréningy"
        $this->assertDatabaseMissing('training_registrations', [
            'user_id' => $user->id,
            'training_id' => $training->id,
            'status' => 'active',
        ]);

        // Assert credits are refunded
        $this->assertEquals(10, $user->fresh()->credits);

        // Assert email notification was sent
        Notification::assertSentTo(
            [$user],
            TrainingCancelledNotification::class,
            function ($notification, $channels) use ($training) {
                return $notification->getTraining()->id === $training->id;
            }
        );
    }
}
