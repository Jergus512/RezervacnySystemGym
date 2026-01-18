<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTrainingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_training_edit_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
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
            ->get(route('admin.trainings.edit', $training))
            ->assertOk()
            ->assertSee('Admin: Upraviť tréning');
    }

    public function test_non_admin_cannot_open_training_edit_page(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'is_trainer' => false]);
        $training = Training::query()->create([
            'title' => 'T',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.trainings.edit', $training))
            ->assertForbidden();
    }
}
