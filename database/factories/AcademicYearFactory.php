<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->year . '-' . ($this->faker->year + 1),
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'school_id' => School::factory(),
            'active' => true,
        ];
    }
}
