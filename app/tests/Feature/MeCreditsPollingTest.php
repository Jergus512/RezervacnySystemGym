<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeCreditsPollingTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_can_fetch_their_credits_as_json(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'is_trainer' => false,
            'is_reception' => false,
            'credits' => 17,
        ]);

        $this->actingAs($user)
            ->getJson('/me/credits')
            ->assertOk()
            ->assertJson(['credits' => 17]);
    }

    public function test_role_user_gets_404_on_me_credits_endpoint(): void
    {
        $trainer = User::factory()->create([
            'is_admin' => false,
            'is_trainer' => true,
            'is_reception' => false,
            'credits' => null,
        ]);

        $this->actingAs($trainer)
            ->getJson('/me/credits')
            ->assertNotFound();
    }
}

