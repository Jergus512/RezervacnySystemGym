<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AnnouncementsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_currently_active_announcements_are_visible(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-21 12:00:00'));

        $user = User::factory()->create();

        Announcement::create([
            'content' => 'A1',
            'is_active' => true,
            'active_from' => '2026-01-20 00:00:00',
            'active_to' => '2026-01-22 00:00:00',
        ]);

        // inactive flag
        Announcement::create([
            'content' => 'A2',
            'is_active' => false,
            'active_from' => '2026-01-20 00:00:00',
            'active_to' => '2026-01-22 00:00:00',
        ]);

        // expired
        Announcement::create([
            'content' => 'A3',
            'is_active' => true,
            'active_from' => '2026-01-10 00:00:00',
            'active_to' => '2026-01-15 00:00:00',
        ]);

        // starts in future
        Announcement::create([
            'content' => 'A4',
            'is_active' => true,
            'active_from' => '2026-01-23 00:00:00',
            'active_to' => '2026-01-30 00:00:00',
        ]);

        // open-ended
        Announcement::create([
            'content' => 'A5',
            'is_active' => true,
            'active_from' => null,
            'active_to' => null,
        ]);

        $resp = $this->actingAs($user)->get(route('announcements.index'));

        $resp->assertOk();
        $resp->assertSee('A1');
        $resp->assertSee('A5');
        $resp->assertDontSee('A2');
        $resp->assertDontSee('A3');
        $resp->assertDontSee('A4');
    }

    public function test_admin_can_access_admin_announcements_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.announcements.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.announcements.create'))->assertOk();
    }

    public function test_non_admin_gets_403_on_admin_announcements_pages(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.announcements.index'))->assertStatus(403);
    }
}

