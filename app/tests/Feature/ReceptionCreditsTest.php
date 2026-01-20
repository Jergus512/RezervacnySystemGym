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
}

