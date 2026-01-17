<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainerCreateTrainingTest extends TestCase
{
    use RefreshDatabase;

    public function test_trainer_can_view_create_training_page(): void
    {
        $trainer = User::factory()->create(['is_trainer' => true]);

        $this->actingAs($trainer)
            ->get(route('trainer.trainings.create'))
            ->assertOk()
            ->assertSee('Vytvorenie tréningu');
    }

    public function test_regular_user_cannot_view_create_training_page(): void
    {
        $user = User::factory()->create(['is_trainer' => false, 'is_admin' => false]);

        $this->actingAs($user)
            ->get(route('trainer.trainings.create'))
            ->assertForbidden();
    }

    public function test_trainer_can_create_training(): void
    {
        $trainer = User::factory()->create(['is_trainer' => true]);

        $payload = [
            'title' => 'Nový tréning',
            'description' => 'Popis',
            'start_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            'capacity' => 10,
            'price' => 2,
            'is_active' => 1,
        ];

        $this->actingAs($trainer)
            ->post(route('trainer.trainings.store'), $payload)
            ->assertRedirect(route('trainer.trainings.create'));

        $this->assertDatabaseHas('trainings', [
            'title' => 'Nový tréning',
            'capacity' => 10,
            'price' => 2,
        ]);
    }

    public function test_admin_cannot_register_for_training(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_trainer' => false, 'credits' => 0]);
        $training = Training::query()->create([
            'title' => 'T',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('trainings.register', $training))
            ->assertForbidden();
    }

    public function test_trainer_cannot_register_for_training(): void
    {
        $trainer = User::factory()->create(['is_trainer' => true, 'is_admin' => false, 'credits' => 0]);
        $training = Training::query()->create([
            'title' => 'T',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($trainer)
            ->post(route('trainings.register', $training))
            ->assertForbidden();
    }
}

