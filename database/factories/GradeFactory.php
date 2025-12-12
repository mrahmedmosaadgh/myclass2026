<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'name' => 'Grade ' . $this->faker->numberBetween(1, 12),
            'school_id' => School::factory(),
            'stage_id' => null,
        ];
    }
}
