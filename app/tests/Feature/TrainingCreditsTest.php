<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingCreditsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_for_paid_training_deducts_credits(): void
    {
        $user = User::factory()->create(['credits' => 10]);
        $training = Training::query()->create([
            'title' => 'Paid',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 3,
        ]);

        $this->actingAs($user)
            ->post(route('trainings.register', $training))
            ->assertRedirect();

        $user->refresh();
        $this->assertSame(7, (int) $user->credits);
    }

    public function test_registering_paid_training_twice_does_not_deduct_twice(): void
    {
        $user = User::factory()->create(['credits' => 10]);
        $training = Training::query()->create([
            'title' => 'Paid',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 3,
        ]);

        $this->actingAs($user)->post(route('trainings.register', $training))->assertRedirect();
        $user->refresh();
        $this->assertSame(7, (int) $user->credits);

        // second attempt should not charge again
        $this->actingAs($user)->post(route('trainings.register', $training))->assertRedirect();
        $user->refresh();
        $this->assertSame(7, (int) $user->credits);
    }

    public function test_registering_fails_when_user_has_not_enough_credits(): void
    {
        $user = User::factory()->create(['credits' => 1]);
        $training = Training::query()->create([
            'title' => 'Paid',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 3,
        ]);

        $this->actingAs($user)
            ->post(route('trainings.register', $training))
            ->assertStatus(422);

        $this->assertDatabaseMissing('training_registrations', [
            'training_id' => $training->id,
            'user_id' => $user->id,
        ]);
    }
}
