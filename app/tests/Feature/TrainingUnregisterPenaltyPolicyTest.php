<?php

namespace Tests\Feature;

use App\Models\PenaltySetting;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingUnregisterPenaltyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_early_cancellation_respects_full_refund_window(): void
    {
        // Ensure settings exist and set to 12 hours (720 minutes) window
        $settings = PenaltySetting::getSingleton();
        $settings->update(['refund_window_minutes' => 720, 'penalty_policy' => 'half']);

        $training = Training::query()->create([
            'title' => 'Early Refund',
            'description' => null,
            // 24 hours in future -> early
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 10,
        ]);

        $user = User::factory()->create(['credits' => 20]);

        // register (charge)
        $this->actingAs($user)
            ->postJson(route('trainings.register', $training))
            ->assertNoContent();

        $this->assertSame(10, $user->refresh()->credits);

        // unregister (should be full refund because it's earlier than the window)
        $this->actingAs($user)
            ->deleteJson(route('trainings.unregister', $training))
            ->assertNoContent();

        $this->assertSame(20, $user->refresh()->credits);
    }

    public function test_late_cancellation_with_half_policy_returns_half(): void
    {
        $settings = PenaltySetting::getSingleton();
        $settings->update(['refund_window_minutes' => 720, 'penalty_policy' => 'half']);

        $training = Training::query()->create([
            'title' => 'Late Half',
            'description' => null,
            // 10 hours in future -> late (less than 12h)
            'start_at' => now()->addHours(10),
            'end_at' => now()->addHours(11),
            'capacity' => 10,
            'price' => 5,
        ]);

        $user = User::factory()->create(['credits' => 10]);

        $this->actingAs($user)
            ->postJson(route('trainings.register', $training))
            ->assertNoContent();

        // charged 5 -> credits 5
        $this->assertSame(5, $user->refresh()->credits);

        // unregister -> half refund intdiv(5,2) == 2
        $this->actingAs($user)
            ->deleteJson(route('trainings.unregister', $training))
            ->assertNoContent();

        $this->assertSame(7, $user->refresh()->credits);
    }

    public function test_late_cancellation_with_none_policy_returns_nothing(): void
    {
        $settings = PenaltySetting::getSingleton();
        $settings->update(['refund_window_minutes' => 720, 'penalty_policy' => 'none']);

        $training = Training::query()->create([
            'title' => 'Late None',
            'description' => null,
            // 6 hours in future -> late
            'start_at' => now()->addHours(6),
            'end_at' => now()->addHours(7),
            'capacity' => 10,
            'price' => 4,
        ]);

        $user = User::factory()->create(['credits' => 10]);

        $this->actingAs($user)
            ->postJson(route('trainings.register', $training))
            ->assertNoContent();

        $this->assertSame(6, $user->refresh()->credits);

        $this->actingAs($user)
            ->deleteJson(route('trainings.unregister', $training))
            ->assertNoContent();

        // no refund -> stays 6
        $this->assertSame(6, $user->refresh()->credits);
    }
}

