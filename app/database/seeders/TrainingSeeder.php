<?php

namespace Database\Seeders;

use App\Models\Training;
use App\Models\TrainingType;
use Illuminate\Database\Seeder;

class TrainingSeeder extends Seeder
{
    public function run(): void
    {
        // Keep it idempotent (safe to re-run)
        Training::query()->delete();

        $typesByName = TrainingType::query()->pluck('id', 'name');

        $base = now()->startOfWeek();

        $trainings = [
            [
                'title' => 'Ranný kruhový tréning',
                'description' => 'Intenzívny kruhový tréning pre všetkých.',
                'start_at' => $base->copy()->addDays(0)->setTime(7, 0),
                'end_at' => $base->copy()->addDays(0)->setTime(8, 0),
                'capacity' => 10,
                'price' => 2,
                'is_active' => true,
                'training_type_id' => $typesByName['Kruhový tréning'] ?? null,
            ],
            [
                'title' => 'Silový tréning',
                'description' => 'Silový tréning pre mierne pokročilých.',
                'start_at' => $base->copy()->addDays(1)->setTime(17, 30),
                'end_at' => $base->copy()->addDays(1)->setTime(18, 30),
                'capacity' => 8,
                'price' => 3,
                'is_active' => true,
                'training_type_id' => $typesByName['Silový tréning'] ?? null,
            ],
            [
                'title' => 'Jóga',
                'description' => 'Uvoľňujúca joga po práci.',
                'start_at' => $base->copy()->addDays(2)->setTime(18, 0),
                'end_at' => $base->copy()->addDays(2)->setTime(19, 0),
                'capacity' => 15,
                'price' => 1,
                'is_active' => true,
                'training_type_id' => $typesByName['Jóga'] ?? null,
            ],
            [
                'title' => 'HIIT',
                'description' => 'Rýchly a intenzívny HIIT tréning.',
                'start_at' => $base->copy()->addDays(4)->setTime(19, 0),
                'end_at' => $base->copy()->addDays(4)->setTime(19, 45),
                'capacity' => 12,
                'price' => 4,
                'is_active' => true,
                'training_type_id' => $typesByName['HIIT'] ?? null,
            ],
        ];

        foreach ($trainings as $t) {
            Training::create($t);
        }
    }
}
