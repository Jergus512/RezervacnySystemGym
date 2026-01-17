<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyTrainingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_trainings_page_lists_only_upcoming_registered_trainings(): void
    {
        $user = User::factory()->create();

        $past = Training::query()->create([
            'title' => 'Past',
            'description' => 'old',
            'start_at' => now()->subDay(),
            'end_at' => now()->subDay()->addHour(),
            'capacity' => 10,
            'price' => 1,
        ]);

        $upcoming = Training::query()->create([
            'title' => 'Upcoming',
            'description' => 'new',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 2,
        ]);

        $notMine = Training::query()->create([
            'title' => 'Not mine',
            'description' => 'nope',
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHour(),
            'capacity' => 10,
            'price' => 0,
        ]);

        $user->trainings()->attach([$past->id, $upcoming->id]);

        $this->actingAs($user)
            ->get(route('my-trainings.index'))
            ->assertOk()
            ->assertSee('Moje tréningy')
            ->assertSee('Upcoming')
            ->assertDontSee('Past')
            ->assertDontSee('Not mine');
    }
}

