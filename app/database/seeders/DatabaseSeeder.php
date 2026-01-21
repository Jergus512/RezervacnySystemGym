<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure one admin exists (idempotent)
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'credits' => 0,
                'email_verified_at' => now(),
            ]
        );

        // Create several regular users if they don't already exist.
        // We use the factory which sets a hashed password.
        $regularCount = 8;

        // Count existing non-admin users to avoid duplicating when re-seeding
        $existingRegular = User::where('is_admin', false)->count();
        $toCreate = max(0, $regularCount - $existingRegular);

        if ($toCreate > 0) {
            User::factory()->count($toCreate)->create([
                'is_admin' => false,
                'credits' => 10,
            ]);
        }

        $this->call([
            TrainingTypeSeeder::class,
            TrainingSeeder::class,
        ]);
    }
}
