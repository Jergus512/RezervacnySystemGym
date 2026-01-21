<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserCreditsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_credits_when_creating_regular_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Regular',
                'email' => 'regular@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                // regular user => neither admin nor trainer
                'credits' => 42,
            ])
            ->assertRedirect(route('admin.users.index'));

        $created = User::query()->where('email', 'regular@example.com')->firstOrFail();
        $this->assertFalse($created->isAdmin());
        $this->assertFalse($created->isTrainer());
        $this->assertSame(42, $created->credits);
    }

    public function test_credits_are_forced_to_zero_when_creating_admin_or_trainer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Admin2',
                'email' => 'admin2@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'is_admin' => 1,
                'credits' => 999,
            ])
            ->assertRedirect(route('admin.users.index'));

        $createdAdmin = User::query()->where('email', 'admin2@example.com')->firstOrFail();
        $this->assertTrue($createdAdmin->isAdmin());
        $this->assertNull($createdAdmin->credits);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Trainer2',
                'email' => 'trainer2@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'is_trainer' => 1,
                'credits' => 999,
            ])
            ->assertRedirect(route('admin.users.index'));

        $createdTrainer = User::query()->where('email', 'trainer2@example.com')->firstOrFail();
        $this->assertTrue($createdTrainer->isTrainer());
        $this->assertNull($createdTrainer->credits);
    }

    public function test_admin_can_update_credits_for_regular_user_only(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $regular = User::factory()->create(['is_admin' => false, 'is_trainer' => false, 'credits' => 10]);
        $trainer = User::factory()->create(['is_admin' => false, 'is_trainer' => true, 'credits' => null]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $regular), [
                'name' => $regular->name,
                'email' => $regular->email,
                'credits' => 77,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(77, $regular->refresh()->credits);

        // If admin flips regular user to trainer/admin, credits must become null regardless of submitted credits
        $this->actingAs($admin)
            ->put(route('admin.users.update', $regular), [
                'name' => $regular->name,
                'email' => $regular->email,
                'is_trainer' => 1,
                'credits' => 88,
            ])
            ->assertRedirect(route('admin.users.index'));

        $regular->refresh();
        $this->assertTrue($regular->isTrainer());
        $this->assertNull($regular->credits);

        // trainer stays at null even if credits are submitted
        $this->actingAs($admin)
            ->put(route('admin.users.update', $trainer), [
                'name' => $trainer->name,
                'email' => $trainer->email,
                'is_trainer' => 1,
                'credits' => 123,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertNull($trainer->refresh()->credits);
    }
}
