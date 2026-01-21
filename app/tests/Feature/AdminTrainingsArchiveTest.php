<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminTrainingsArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_current_trainings_shows_only_upcoming_and_archive_shows_only_past(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-21 12:00:00'));

        $admin = User::factory()->create(['is_admin' => true]);

        // Upcoming
        Training::query()->create([
            'title' => 'Upcoming',
            'description' => 'Upcoming',
            'start_at' => '2026-01-21 13:00:00',
            'end_at' => '2026-01-21 14:00:00',
            'capacity' => 10,
            'price' => 0,
            'is_active' => true,
        ]);

        // Past
        Training::query()->create([
            'title' => 'Past',
            'description' => 'Past',
            'start_at' => '2026-01-21 10:00:00',
            'end_at' => '2026-01-21 11:00:00',
            'capacity' => 10,
            'price' => 0,
            'is_active' => true,
        ]);

        $current = $this->actingAs($admin)->get(route('admin.trainings.index'));
        $current->assertOk();
        $current->assertSee('Upcoming');
        $current->assertDontSee('Past');

        $archive = $this->actingAs($admin)->get(route('admin.trainings.archive'));
        $archive->assertOk();
        $archive->assertSee('Past');
        $archive->assertDontSee('Upcoming');
    }
}

