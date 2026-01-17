<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingUnregisterRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_unregister_and_get_credits_refunded(): void
    {
        $training = Training::query()->create([
            'title' => 'Refund',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 3,
        ]);

        $user = User::factory()->create(['credits' => 10]);

        // register (charge)
        $this->actingAs($user)
            ->postJson(route('trainings.register', $training))
            ->assertNoContent();

        $this->assertDatabaseHas('training_registrations', [
            'training_id' => $training->id,
            'user_id' => $user->id,
        ]);

        $this->assertSame(7, $user->refresh()->credits);

        // unregister (refund)
        $this->actingAs($user)
            ->deleteJson(route('trainings.unregister', $training))
            ->assertNoContent();

        $this->assertDatabaseMissing('training_registrations', [
            'training_id' => $training->id,
            'user_id' => $user->id,
        ]);

        $this->assertSame(10, $user->refresh()->credits);
    }
}

