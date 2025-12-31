<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\School;
use App\Models\Stage;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word() . ' Class',
            'capacity' => $this->faker->numberBetween(20, 40),
            'school_id' => School::factory(),
            'stage_id' => Stage::factory(),
            'grade_id' => Grade::factory(),
        ];
    }
}