<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\V2MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and menus
        $this->seed(RoleSeeder::class);
        $this->seed(V2MenuSeeder::class);
    }

    /**
     * Test that the navigation API returns a successful response with correct data.
     */
    public function test_navigation_api_returns_menus_for_authenticated_user()
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');

        $response = $this->actingAs($user)
            ->getJson('/api/navigation?role=teacher&v2=true');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'label',
                        'route',
                        'icon',
                        'children'
                    ]
                ],
                'version'
            ]);
            
        // Assert we got teacher menus (Top level item)
        $response->assertJsonFragment(['label' => 'Teacher Portal']);
        
        // Assert children structure
        $data = $response->json('data');
        $this->assertNotEmpty($data, 'Menu data is empty');
        $this->assertEquals('Teacher Portal', $data[0]['label']);
        $this->assertNotEmpty($data[0]['children'], 'Teacher Portal has no children');
        $childrenLabels = collect($data[0]['children'])->pluck('label');
        $this->assertTrue($childrenLabels->contains('Dashboard'), 'Dashboard menu missing. found: ' . $childrenLabels->implode(', '));
        $this->assertTrue($childrenLabels->contains('My Schedule'), 'My Schedule menu missing');
    }

    /**
     * Test ETag caching (304 Not Modified).
     */
    public function test_navigation_api_returns_304_when_not_modified()
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');

        // First request
        $response = $this->actingAs($user)
            ->getJson('/api/navigation?role=teacher&v2=true');

        $response->assertStatus(200);
        $etag = $response->headers->get('ETag');
        $this->assertNotNull($etag, 'ETag header is missing');

        // Second request with ETag
        $response2 = $this->actingAs($user)
            ->withHeaders(['If-None-Match' => $etag])
            ->getJson('/api/navigation?role=teacher&v2=true');

        $response2->assertStatus(304);
    }
    
    /**
     * Test admin menu retrieval (verifying the specific fix).
     */
    public function test_admin_can_retrieve_menus()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->givePermissionTo('manage-menus'); // Ensure permission is there if needed

        $response = $this->actingAs($user)
            ->getJson('/api/navigation?role=admin&v2=true');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.label', fn($label) => !empty($label));
    }
}
