<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_for_training(): void
    {
        $user = User::factory()->create();
        $training = Training::query()->create([
            'title' => 'Test tréning',
            'description' => 'Popis',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
        ]);

        $this->actingAs($user)
            ->post(route('trainings.register', $training))
            ->assertRedirect();

        $this->assertDatabaseHas('training_registrations', [
            'training_id' => $training->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_register_twice(): void
    {
        $user = User::factory()->create();
        $training = Training::query()->create([
            'title' => 'Test tréning',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
        ]);

        $training->users()->attach($user->id);

        $this->actingAs($user)
            ->post(route('trainings.register', $training))
            ->assertRedirect();

        $this->assertSame(1, $training->users()->where('users.id', $user->id)->count());
    }

    public function test_user_cannot_register_when_training_is_full(): void
    {
        $training = Training::query()->create([
            'title' => 'Plný tréning',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 1,
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $training->users()->attach($user1->id);

        $this->actingAs($user2)
            ->post(route('trainings.register', $training))
            ->assertStatus(422);

        $this->assertDatabaseMissing('training_registrations', [
            'training_id' => $training->id,
            'user_id' => $user2->id,
        ]);
    }

    public function test_user_cannot_register_when_training_is_inactive(): void
    {
        $user = User::factory()->create(['credits' => 10]);
        $training = Training::query()->create([
            'title' => 'Neaktuálny tréning',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 2,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->postJson(route('trainings.register', $training))
            ->assertStatus(422);

        $this->assertDatabaseMissing('training_registrations', [
            'training_id' => $training->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_register_when_training_already_started(): void
    {
        $user = User::factory()->create(['credits' => 10]);
        $training = Training::query()->create([
            'title' => 'Minulý tréning',
            'description' => null,
            'start_at' => now()->subMinute(),
            'end_at' => now()->addHour(),
            'capacity' => 10,
            'price' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('trainings.register', $training))
            ->assertStatus(422);

        $this->assertDatabaseMissing('training_registrations', [
            'training_id' => $training->id,
            'user_id' => $user->id,
        ]);
    }
}
