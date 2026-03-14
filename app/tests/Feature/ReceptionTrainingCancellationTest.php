<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\TrainingAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceptionTrainingCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_can_deactivate_and_activate_training_and_audit_is_created(): void
    {
        // Create reception user
        $reception = User::factory()->create([
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => true,
        ]);

        // Create a trainer (owner) to be the creator of the training
        $trainer = User::factory()->create(['is_trainer' => true]);

        // Create an active training
        $training = Training::create([
            'created_by_user_id' => $trainer->id,
            'title' => 'Test tréning',
            'description' => 'Desc',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 1000,
            'is_active' => true,
        ]);

        // Deactivate
        $this->actingAs($reception)
            ->post('/reception/treningy/' . $training->id . '/toggle-active', [
                'action' => 'deactivate',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('trainings', [
            'id' => $training->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('training_audits', [
            'training_id' => $training->id,
            'action' => 'deactivate',
            'performed_by_user_id' => $reception->id,
        ]);

        $audit = TrainingAudit::where('training_id', $training->id)->orderByDesc('id')->first();
        $this->assertNotNull($audit);
        // training was initially active, so old value should be true and new value false after deactivation
        $this->assertEquals(true, $audit->meta['old_is_active']);
        $this->assertEquals(false, $audit->meta['new_is_active']);

        // Activate again
        $this->actingAs($reception)
            ->post('/reception/treningy/' . $training->id . '/toggle-active', [
                'action' => 'activate',
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('trainings', [
            'id' => $training->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('training_audits', [
            'training_id' => $training->id,
            'action' => 'activate',
            'performed_by_user_id' => $reception->id,
        ]);

        $audit2 = TrainingAudit::where('training_id', $training->id)->orderByDesc('id')->first();
        $this->assertEquals(false, $audit2->meta['old_is_active']);
        $this->assertEquals(true, $audit2->meta['new_is_active']);
    }
}
