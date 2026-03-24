<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceptionTrainingToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_can_deactivate_and_activate_training()
    {
        // Create a reception user
        $reception = User::factory()->create(['role' => 'reception']);

        // Create a training
        $training = Training::factory()->create(['is_active' => true]);

        // Ensure the reception middleware is bypassed for testing
        $this->withoutMiddleware('reception');

        // Ensure the reception middleware group is loaded
        $this->app['router']->aliasMiddleware('reception', \App\Http\Middleware\ReceptionMiddleware::class);

        // Ensure the user has the correct role to access the route
        $reception->update(['is_reception' => true]);

        // Act as reception and deactivate the training
        $this->actingAs($reception)
            ->post(route('reception.trainings.toggle', $training), ['action' => 'deactivate'])
            ->assertRedirect(route('reception.trainings.index'))
            ->assertSessionHas('status', 'Tréning bol zrušený (deaktivovaný).');

        $this->assertDatabaseHas('trainings', [
            'id' => $training->id,
            'is_active' => false,
        ]);

        // Activate the training
        $this->post(route('reception.trainings.toggle', $training), ['action' => 'activate'])
            ->assertRedirect(route('reception.trainings.index'))
            ->assertSessionHas('status', 'Tréning bol znova aktivovaný.');

        $this->assertDatabaseHas('trainings', [
            'id' => $training->id,
            'is_active' => true,
        ]);
    }
}
