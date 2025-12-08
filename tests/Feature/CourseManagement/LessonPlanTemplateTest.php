<?php

namespace Tests\Feature\CourseManagement;

use App\Models\CourseManagement\LessonPlanTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonPlanTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_a_lesson_plan_template()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/course-management/lesson-plan-templates', [
            'name' => 'Test Template',
            'structure' => [
                ['label' => 'Objective', 'type' => 'textarea'],
                ['label' => 'Materials', 'type' => 'text'],
                ['label' => 'Procedure', 'type' => 'textarea']
            ]
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Template created successfully'
            ]);

        $this->assertDatabaseHas('lesson_plan_templates', [
            'name' => 'Test Template',
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_requires_name_and_structure()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/course-management/lesson-plan-templates', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'structure']);
    }

    /** @test */
    public function it_can_list_templates()
    {
        $this->actingAs($this->user);

        LessonPlanTemplate::factory()->count(3)->create(['created_by' => $this->user->id]);

        $response = $this->getJson('/api/course-management/lesson-plan-templates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'structure', 'created_by', 'created_at']
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_update_a_template()
    {
        $this->actingAs($this->user);

        $template = LessonPlanTemplate::factory()->create(['created_by' => $this->user->id]);

        $response = $this->putJson("/api/course-management/lesson-plan-templates/{$template->id}", [
            'name' => 'Updated Template Name',
            'structure' => [
                ['label' => 'New Objective', 'type' => 'text']
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Template updated successfully'
            ]);

        $this->assertDatabaseHas('lesson_plan_templates', [
            'id' => $template->id,
            'name' => 'Updated Template Name'
        ]);
    }

    /** @test */
    public function it_can_delete_a_template()
    {
        $this->actingAs($this->user);

        $template = LessonPlanTemplate::factory()->create(['created_by' => $this->user->id]);

        $response = $this->deleteJson("/api/course-management/lesson-plan-templates/{$template->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);

        $this->assertDatabaseMissing('lesson_plan_templates', [
            'id' => $template->id
        ]);
    }
}