<?php

namespace Database\Factories;

use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'start_at' => $this->faker->dateTimeBetween('+1 days', '+1 month'),
            'end_at' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'is_active' => true,
            'price' => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}
