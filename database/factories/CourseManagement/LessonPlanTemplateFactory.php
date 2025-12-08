<?php

namespace Database\Factories\CourseManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseManagement\LessonPlanTemplate>
 */
class LessonPlanTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true) . ' Template',
            'structure' => [
                [
                    'label' => 'Objective',
                    'type' => 'textarea'
                ],
                [
                    'label' => 'Materials',
                    'type' => 'text'
                ],
                [
                    'label' => 'Procedure',
                    'type' => 'textarea'
                ],
                [
                    'label' => 'Assessment',
                    'type' => 'select'
                ]
            ],
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}