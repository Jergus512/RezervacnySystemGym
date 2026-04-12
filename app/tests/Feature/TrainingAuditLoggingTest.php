<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\TrainingAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingAuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_training_creation_is_logged_in_audit(): void
    {
        $trainer = User::factory()->create(['is_trainer' => true]);

        $this->actingAs($trainer)
            ->post('/trainer/vytvorenie-treningu', [
                'title' => 'Nový tréning',
                'description' => 'Popis',
                'start_at' => now()->addDay()->format('Y-m-d'),
                'end_at' => now()->addDay()->addHour()->format('Y-m-d H:i'),
                'capacity' => 10,
                'price' => 50,
                'is_active' => true,
            ]);

        // Skontroluj, či bol vytvorený audit záznam pre create akciu
        $this->assertDatabaseHas('training_audits', [
            'action' => 'create',
            'performed_by_user_id' => $trainer->id,
        ]);

        $audit = TrainingAudit::where('action', 'create')
            ->where('performed_by_user_id', $trainer->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertArrayHasKey('title', $audit->meta);
        $this->assertEquals('Nový tréning', $audit->meta['title']);
    }

    public function test_training_update_is_logged_in_audit(): void
    {
        $trainer = User::factory()->create(['is_trainer' => true]);
        $training = Training::factory()->create([
            'created_by_user_id' => $trainer->id,
            'title' => 'Pôvodný názov',
        ]);

        // Vymaž existujúce audity (z vytvorenia)
        TrainingAudit::where('training_id', $training->id)->delete();

        $this->actingAs($trainer)
            ->put("/trainer/treningy/{$training->id}", [
                'title' => 'Nový názov',
                'description' => $training->description,
                'start_at' => $training->start_at->format('Y-m-d'),
                'end_at' => $training->end_at->format('Y-m-d H:i'),
                'capacity' => $training->capacity,
                'price' => $training->price,
                'is_active' => $training->is_active,
            ]);

        // Skontroluj, či bol vytvorený audit záznam pre update akciu
        $audit = TrainingAudit::where('action', 'update')
            ->where('training_id', $training->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertArrayHasKey('title', $audit->meta);
        $this->assertEquals('Pôvodný názov', $audit->meta['title']['old']);
        $this->assertEquals('Nový názov', $audit->meta['title']['new']);
    }

    public function test_training_cancellation_logs_detailed_audit(): void
    {
        $trainer = User::factory()->create(['is_trainer' => true]);
        $user1 = User::factory()->create(['is_trainer' => false, 'credits' => 100]);
        $user2 = User::factory()->create(['is_trainer' => false, 'credits' => 100]);

        $training = Training::factory()->create([
            'created_by_user_id' => $trainer->id,
            'price' => 50,
        ]);

        // Zaregistruj používateľov na tréning
        $training->users()->attach([$user1->id, $user2->id], ['status' => 'active']);

        // Vymaž existujúce audity
        TrainingAudit::where('training_id', $training->id)->delete();

        // Zruš tréning
        $training->cancelTraining();

        // Skontroluj audit záznam pre cancel akciu
        $audit = TrainingAudit::where('action', 'cancel')
            ->where('training_id', $training->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals(2, $audit->meta['registrations_refunded']);
        $this->assertEquals(50, $audit->meta['refund_amount_per_user']);
        $this->assertEquals(100, $audit->meta['total_refunded']);
    }

    public function test_reception_deactivation_logs_audit(): void
    {
        $reception = User::factory()->create(['is_reception' => true]);
        $training = Training::factory()->create(['is_active' => true]);

        // Vymaž existujúce audity
        TrainingAudit::where('training_id', $training->id)->delete();

        $this->actingAs($reception)
            ->post("/reception/treningy/{$training->id}/toggle-active", [
                'action' => 'deactivate',
            ]);

        // Skontroluj audit záznam
        $audit = TrainingAudit::where('action', 'deactivate')
            ->where('training_id', $training->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals($reception->id, $audit->performed_by_user_id);
        $this->assertTrue($audit->meta['old_is_active']);
        $this->assertFalse($audit->meta['new_is_active']);
    }
}
