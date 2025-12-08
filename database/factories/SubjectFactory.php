<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $subjects = ['Mathematics', 'Science', 'English', 'History', 'Geography', 'Physics', 'Chemistry', 'Biology'];
        
        return [
            'name' => $this->faker->randomElement($subjects),
            'code' => strtoupper($this->faker->lexify('???')),
            'description' => $this->faker->sentence(),
        ];
    }
}
