<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainerRepeatingCreationAndCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_trainer_can_create_weekly_repeating_trainings(): void
    {
        $trainer = User::factory()->create(['is_trainer' => true]);

        $payload = [
            'title' => 'Opakovany',
            'description' => 'Popis',
            'start_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            'capacity' => 10,
            'price' => 2,
            'is_active' => 1,
            'repeat_weekly' => 1,
            'repeat_weeks' => 3,
        ];

        $this->actingAs($trainer)
            ->post(route('trainer.trainings.store'), $payload)
            ->assertRedirect(route('trainer.trainings.create'));

        $this->assertSame(3, Training::query()->count());

        $this->assertSame(3, Training::query()->where('created_by_user_id', $trainer->id)->count());

        $first = Training::query()->orderBy('start_at')->first();
        $this->assertNotNull($first);
    }

    public function test_trainer_sees_only_their_created_trainings_on_index(): void
    {
        $t1 = User::factory()->create(['is_trainer' => true]);
        $t2 = User::factory()->create(['is_trainer' => true]);

        $a = Training::query()->create([
            'created_by_user_id' => $t1->id,
            'title' => 'TRAINING_A_UNIQUE',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 1,
            'price' => 0,
            'is_active' => true,
        ]);

        $b = Training::query()->create([
            'created_by_user_id' => $t2->id,
            'title' => 'TRAINING_B_UNIQUE',
            'description' => null,
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHour(),
            'capacity' => 1,
            'price' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($t1)
            ->get(route('trainer.trainings.index'))
            ->assertOk()
            ->assertSee('TRAINING_A_UNIQUE')
            ->assertDontSee('TRAINING_B_UNIQUE');

        $this->actingAs($t2)
            ->get(route('trainer.trainings.index'))
            ->assertOk()
            ->assertSee('TRAINING_B_UNIQUE')
            ->assertDontSee('TRAINING_A_UNIQUE');
    }

    public function test_trainer_cannot_edit_someone_elses_training(): void
    {
        $t1 = User::factory()->create(['is_trainer' => true]);
        $t2 = User::factory()->create(['is_trainer' => true]);

        $training = Training::query()->create([
            'created_by_user_id' => $t1->id,
            'title' => 'A',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 1,
            'price' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($t2)
            ->get(route('trainer.trainings.edit', $training))
            ->assertForbidden();
    }
}
