<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AnnouncementsJsonEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_json_returns_only_active_announcements(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-21 12:00:00'));

        $user = User::factory()->create();

        Announcement::create([
            'title' => 'A1',
            'content' => 'Active',
            'is_active' => true,
            'active_from' => '2026-01-20 00:00:00',
            'active_to' => '2026-01-22 00:00:00',
        ]);

        Announcement::create([
            'title' => 'A2',
            'content' => 'Inactive flag',
            'is_active' => false,
            'active_from' => '2026-01-20 00:00:00',
            'active_to' => '2026-01-22 00:00:00',
        ]);

        $resp = $this->actingAs($user)->get(route('announcements.current'));

        $resp->assertOk();
        $resp->assertJsonStructure(['server_now', 'data']);
        $resp->assertJsonCount(1, 'data');
        $resp->assertJsonFragment(['title' => 'A1']);
        $resp->assertJsonMissing(['title' => 'A2']);
    }
}

