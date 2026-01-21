<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_users_by_role_and_search_query(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'is_trainer' => false,
            'is_reception' => false,
            'credits' => null,
        ]);

        $trainer = User::factory()->create([
            'name' => 'Trainer One',
            'email' => 'trainer1@example.com',
            'is_admin' => false,
            'is_trainer' => true,
            'is_reception' => false,
            'credits' => null,
        ]);

        $regular = User::factory()->create([
            'name' => 'John Regular',
            'email' => 'john@example.com',
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => false,
            'credits' => 5,
        ]);

        $this->actingAs($admin)
            ->get('/admin/users?role=trainer')
            ->assertOk()
            ->assertSee($trainer->email)
            ->assertDontSee($regular->email);

        $this->actingAs($admin)
            ->get('/admin/users?q=john&role=regular')
            ->assertOk()
            ->assertSee($regular->email)
            ->assertDontSee($trainer->email);
    }

    public function test_admin_autocomplete_returns_json_and_respects_role_filter(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'is_trainer' => false,
            'is_reception' => false,
            'credits' => null,
        ]);

        $trainer = User::factory()->create([
            'name' => 'Trainer Two',
            'email' => 'trainer2@example.com',
            'is_admin' => false,
            'is_trainer' => true,
            'is_reception' => false,
            'credits' => null,
        ]);

        $regular = User::factory()->create([
            'name' => 'Jana Regular',
            'email' => 'jana@example.com',
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => false,
            'credits' => 1,
        ]);

        $this->actingAs($admin)
            ->getJson('/admin/users/autocomplete?q=tra&role=trainer')
            ->assertOk()
            ->assertJsonFragment(['id' => $trainer->id])
            ->assertJsonMissing(['id' => $regular->id]);
    }

    public function test_non_admin_gets_403_on_admin_autocomplete(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => false,
        ]);

        $this->actingAs($user)
            ->getJson('/admin/users/autocomplete?q=a')
            ->assertForbidden();
    }
}

