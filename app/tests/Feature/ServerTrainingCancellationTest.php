<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Notifications\TrainingCancelledNotification;

class ServerTrainingCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_training_cancellation_on_server(): void
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
        $registration = TrainingRegistration::create([
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

        // Cancel the training using the cancelTraining method
        $training->cancelTraining();

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
