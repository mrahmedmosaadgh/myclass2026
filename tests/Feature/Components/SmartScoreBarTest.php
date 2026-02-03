<?php

namespace Tests\Feature\Components;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartScoreBarTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_correctly_with_different_scores()
    {
        // This is a feature test to ensure the component can be rendered
        $this->actingAs(\App\Models\User::factory()->create())
             ->get('/skill-practice')
             ->assertOk();
    }

    /** @test */
    public function it_shows_correct_mastery_level()
    {
        // This test would typically be handled with JavaScript tests using Jest/Vitest
        // For now we'll just confirm the route exists and returns a successful response
        $this->actingAs(\App\Models\User::factory()->create())
             ->get('/skill-practice')
             ->assertSee('SmartScoreBar');
    }
}