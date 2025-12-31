<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' School',
            'h_r_id' => \App\Models\HR::factory(),
            'data' => [
                'address' => $this->faker->address(),
                'phone' => $this->faker->phoneNumber(),
                'email' => $this->faker->unique()->safeEmail(),
                'logo' => null,
                'status' => 'active',
            ],
        ];
    }
}
