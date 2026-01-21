<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceptionCreditsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_reception_can_access_reception_pages(): void
    {
        $regular = User::factory()->create([
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => false,
        ]);

        $this->actingAs($regular)
            ->get('/reception')
            ->assertStatus(403);
    }

    public function test_reception_can_add_credits_to_user(): void
    {
        $reception = User::factory()->create([
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => true,
            'credits' => null,
        ]);

        $target = User::factory()->create([
            'credits' => 5,
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => false,
        ]);

        $this->actingAs($reception)
            ->post('/reception/pridanie-kreditov', [
                'user_id' => $target->id,
                'credits_to_add' => 7,
            ])
            ->assertSessionHas('status');

        $this->assertSame(12, $target->fresh()->credits);
    }

    public function test_reception_cannot_add_credits_to_admin_trainer_or_reception(): void
    {
        $reception = User::factory()->create([
            'is_reception' => true,
            'credits' => null,
        ]);

        $admin = User::factory()->create(['is_admin' => true, 'credits' => null]);
        $trainer = User::factory()->create(['is_trainer' => true, 'credits' => null]);
        $otherReception = User::factory()->create(['is_reception' => true, 'credits' => null]);

        foreach ([$admin, $trainer, $otherReception] as $target) {
            $this->actingAs($reception)
                ->post('/reception/pridanie-kreditov', [
                    'user_id' => $target->id,
                    'credits_to_add' => 5,
                ])
                ->assertStatus(422);

            $this->assertNull($target->fresh()->credits);
        }
    }

    public function test_reception_search_returns_only_regular_users(): void
    {
        $reception = User::factory()->create([
            'is_reception' => true,
            'credits' => null,
        ]);

        $regular = User::factory()->create([
            'email' => 'regular.search@example.com',
            'credits' => 1,
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => false,
        ]);

        $admin = User::factory()->create([
            'email' => 'admin.search@example.com',
            'is_admin' => true,
            'credits' => null,
        ]);

        $res = $this->actingAs($reception)
            ->get('/reception/pridanie-kreditov/search?q=reg')
            ->assertOk()
            ->json();

        $ids = collect($res)->pluck('id')->all();

        $this->assertContains($regular->id, $ids);
        $this->assertNotContains($admin->id, $ids);
    }

    public function test_reception_can_add_credits_via_ajax_and_get_updated_credits(): void
    {
        $reception = User::factory()->create([
            'is_reception' => true,
            'credits' => null,
        ]);

        $target = User::factory()->create([
            'credits' => 5,
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => false,
        ]);

        $this->actingAs($reception)
            ->postJson('/reception/pridanie-kreditov', [
                'user_id' => $target->id,
                'credits_to_add' => 20,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('user.id', $target->id)
            ->assertJsonPath('user.credits', 25);

        $this->assertSame(25, $target->fresh()->credits);
    }

    public function test_reception_can_poll_current_credits_for_regular_user(): void
    {
        $reception = User::factory()->create([
            'is_reception' => true,
            'credits' => null,
        ]);

        $regular = User::factory()->create([
            'credits' => 33,
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => false,
        ]);

        $this->actingAs($reception)
            ->getJson("/reception/pridanie-kreditov/{$regular->id}/credits")
            ->assertOk()
            ->assertJson([
                'id' => $regular->id,
                'credits' => 33,
            ]);
    }

    public function test_reception_poll_endpoint_returns_404_for_role_accounts(): void
    {
        $reception = User::factory()->create([
            'is_reception' => true,
            'credits' => null,
        ]);

        $trainer = User::factory()->create([
            'is_trainer' => true,
            'credits' => null,
        ]);

        $this->actingAs($reception)
            ->getJson("/reception/pridanie-kreditov/{$trainer->id}/credits")
            ->assertNotFound();
    }
}
