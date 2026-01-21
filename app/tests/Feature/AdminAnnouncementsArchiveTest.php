<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminAnnouncementsArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_index_shows_only_currently_active_and_archive_shows_only_expired_or_inactive(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-21 12:00:00'));

        $admin = User::factory()->create(['is_admin' => true]);

        // Active now
        Announcement::create([
            'title' => 'ActiveNow',
            'content' => 'ActiveNow',
            'is_active' => true,
            'active_from' => '2026-01-20 00:00:00',
            'active_to' => '2026-01-22 00:00:00',
        ]);

        // Expired (active_to in past)
        Announcement::create([
            'title' => 'Expired',
            'content' => 'Expired',
            'is_active' => true,
            'active_from' => '2026-01-10 00:00:00',
            'active_to' => '2026-01-15 00:00:00',
        ]);

        // Inactive flag
        Announcement::create([
            'title' => 'InactiveFlag',
            'content' => 'InactiveFlag',
            'is_active' => false,
            'active_from' => '2026-01-20 00:00:00',
            'active_to' => '2026-01-22 00:00:00',
        ]);

        // Starts in future (not current, not archive per "po dátume" requirement)
        Announcement::create([
            'title' => 'Future',
            'content' => 'Future',
            'is_active' => true,
            'active_from' => '2026-01-23 00:00:00',
            'active_to' => '2026-01-30 00:00:00',
        ]);

        $index = $this->actingAs($admin)->get(route('admin.announcements.index'));
        $index->assertOk();
        $index->assertSee('ActiveNow');
        $index->assertDontSee('Expired');
        $index->assertDontSee('InactiveFlag');
        $index->assertDontSee('Future');

        $archive = $this->actingAs($admin)->get(route('admin.announcements.archive'));
        $archive->assertOk();
        $archive->assertDontSee('ActiveNow');
        $archive->assertSee('Expired');
        $archive->assertSee('InactiveFlag');
        $archive->assertDontSee('Future');
    }
}

