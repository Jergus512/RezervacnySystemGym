<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingEventColorTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_green_only_for_registered_user(): void
    {
        $training = Training::query()->create([
            'title' => 'Color',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 0,
        ]);

        $u1 = User::factory()->create(['credits' => 10]);
        $u2 = User::factory()->create(['credits' => 10]);

        $training->users()->attach($u1->id);

        $eventsForU1 = $this->actingAs($u1)->get(route('training-calendar.events'))
            ->assertOk()
            ->json();

        $eventsForU2 = $this->actingAs($u2)->get(route('training-calendar.events'))
            ->assertOk()
            ->json();

        $e1 = collect($eventsForU1)->firstWhere('id', $training->id);
        $e2 = collect($eventsForU2)->firstWhere('id', $training->id);

        $this->assertNotNull($e1, 'Expected training event missing for u1. Events: '.json_encode($eventsForU1));
        $this->assertNotNull($e2, 'Expected training event missing for u2. Events: '.json_encode($eventsForU2));

        $this->assertSame('#198754', $e1['backgroundColor']);
        $this->assertNull($e2['backgroundColor']);

        // after unregistering, the event should no longer be green for u1
        $this->actingAs($u1)
            ->deleteJson(route('trainings.unregister', $training))
            ->assertNoContent();

        $eventsForU1After = $this->actingAs($u1)->get(route('training-calendar.events'))
            ->assertOk()
            ->json();

        $e1After = collect($eventsForU1After)->firstWhere('id', $training->id);
        $this->assertNotNull($e1After, 'Expected training event missing for u1 after unregister. Events: '.json_encode($eventsForU1After));
        $this->assertNull($e1After['backgroundColor']);
    }

    public function test_past_training_is_rendered_light_grey(): void
    {
        $past = Training::query()->create([
            'title' => 'Past',
            'description' => null,
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDays(2)->addHour(),
            'capacity' => 10,
            'price' => 0,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $events = $this->actingAs($user)->get(route('training-calendar.events'))
            ->assertOk()
            ->json();

        $e = collect($events)->firstWhere('id', $past->id);
        $this->assertNotNull($e);
        $this->assertSame('#e9ecef', $e['backgroundColor']);
    }
}
