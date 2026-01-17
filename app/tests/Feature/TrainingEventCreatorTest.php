<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingEventCreatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_include_creator_name_when_present(): void
    {
        $trainer = User::factory()->create(['is_trainer' => true]);

        $training = Training::query()->create([
            'created_by_user_id' => $trainer->id,
            'title' => 'With creator',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 0,
            'is_active' => true,
        ]);

        $events = $this->get(route('training-calendar.events'))
            ->assertOk()
            ->json();

        $e = collect($events)->firstWhere('id', $training->id);
        $this->assertNotNull($e);
        $this->assertSame($trainer->id, $e['creator']['id']);
        $this->assertSame($trainer->name, $e['creator']['name']);
    }
}

