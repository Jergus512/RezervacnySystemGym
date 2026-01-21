<?php

namespace Database\Seeders;

use App\Models\TrainingType;
use Illuminate\Database\Seeder;

class TrainingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Kruhový tréning',
            'Silový tréning',
            'Jóga',
            'HIIT',
            'Pilates',
        ];

        foreach ($names as $name) {
            TrainingType::firstOrCreate(['name' => $name]);
        }
    }
}
