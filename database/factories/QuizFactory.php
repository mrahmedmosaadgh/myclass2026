<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\School;
use App\Models\User;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'school_id' => School::factory(),
            'subject_id' => Subject::factory(),
            'grade_id' => Grade::factory(),
            'created_by_id' => User::factory(),
            'status' => 'active',
            'time_limit_minutes' => $this->faker->randomElement([null, 30, 45, 60]),
            'shuffle_questions' => $this->faker->boolean(),
            'shuffle_options' => $this->faker->boolean(),
            'allow_review' => $this->faker->boolean(80), // 80% true
            'metadata' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
