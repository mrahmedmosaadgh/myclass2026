<?php

namespace Database\Factories;

use App\Models\Stage;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageFactory extends Factory
{
    protected $model = Stage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Primary', 'Middle', 'High School']),
            'school_id' => School::factory(),
        ];
    }
}