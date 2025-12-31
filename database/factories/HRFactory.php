<?php

namespace Database\Factories;

use App\Models\HR;
use Illuminate\Database\Eloquent\Factories\Factory;

class HRFactory extends Factory
{
    protected $model = HR::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'user_id' => \App\Models\User::factory(),
            'data' => [],
            'active' => true,
        ];
    }
}