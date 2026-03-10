<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingCalendarCanceledRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_canceled_registration_is_treated_as_not_registered_in_calendar(): void
    {
        $user = User::factory()->create(['credits' => 10]);

        $training = Training::query()->create([
            'title' => 'Cancelable',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 10,
            'price' => 0,
            'is_active' => true,
        ]);

        // Register via controller to use real flow
        $this->actingAs($user)
            ->postJson(route('trainings.register', $training))
            ->assertNoContent();

        // Ensure event is marked as registered / green
        $eventsAfterRegister = $this->actingAs($user)
            ->get(route('training-calendar.events'))
            ->assertOk()
            ->json();

        $eventAfterRegister = collect($eventsAfterRegister)->firstWhere('id', $training->id);
        $this->assertNotNull($eventAfterRegister, 'Expected training event after register. Events: '.json_encode($eventsAfterRegister));
        $this->assertTrue($eventAfterRegister['is_registered']);
        $this->assertSame('#198754', $eventAfterRegister['backgroundColor']);

        // Unregister (status -> canceled)
        $this->actingAs($user)
            ->deleteJson(route('trainings.unregister', $training))
            ->assertNoContent();

        // Now calendar should treat user as NOT registered anymore
        $eventsAfterCancel = $this->actingAs($user)
            ->get(route('training-calendar.events'))
            ->assertOk()
            ->json();

        $eventAfterCancel = collect($eventsAfterCancel)->firstWhere('id', $training->id);
        $this->assertNotNull($eventAfterCancel, 'Expected training event after cancel. Events: '.json_encode($eventsAfterCancel));
        $this->assertFalse($eventAfterCancel['is_registered']);
        $this->assertNull($eventAfterCancel['backgroundColor']);
    }

    public function test_user_can_register_again_after_cancellation_when_capacity_allows(): void
    {
        $user = User::factory()->create(['credits' => 10]);

        $training = Training::query()->create([
            'title' => 'Cancelable twice',
            'description' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'capacity' => 2,
            'price' => 0,
            'is_active' => true,
        ]);

        // First registration
        $this->actingAs($user)
            ->postJson(route('trainings.register', $training))
            ->assertNoContent();

        // Cancel
        $this->actingAs($user)
            ->deleteJson(route('trainings.unregister', $training))
            ->assertNoContent();

        // Re-register should succeed and mark as registered again in calendar
        $this->actingAs($user)
            ->postJson(route('trainings.register', $training))
            ->assertNoContent();

        $eventsAfterReregister = $this->actingAs($user)
            ->get(route('training-calendar.events'))
            ->assertOk()
            ->json();

        $event = collect($eventsAfterReregister)->firstWhere('id', $training->id);
        $this->assertNotNull($event, 'Expected training event after re-register. Events: '.json_encode($eventsAfterReregister));
        $this->assertTrue($event['is_registered']);
        $this->assertSame('#198754', $event['backgroundColor']);
    }
}

