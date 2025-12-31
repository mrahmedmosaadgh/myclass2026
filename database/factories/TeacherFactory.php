<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'national_id' => $this->faker->unique()->numerify('##########'),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'date_of_birth' => $this->faker->date('Y-m-d', '2000-01-01'),
            'active' => true,
            'school_id' => School::factory(),
            'user_id' => User::factory(),
        ];
    }
}