<?php

namespace Tests\Feature\Landing;

use App\Models\Landing;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LandingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Theme $theme;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->theme = Theme::factory()->create();
    }

    /** @test */
    public function test_user_can_list_own_landings()
    {
        $otherUser = User::factory()->create();
        
        // Crear landings para el usuario autenticado
        Landing::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id
        ]);
        
        // Crear landing para otro usuario (no debería aparecer)
        Landing::factory()->create([
            'user_id' => $otherUser->id,
            'theme_id' => $this->theme->id
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/landings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'theme_id',
                        'slug',
                        'couple_names',
                        'anniversary_date',
                        'bio_text',
                        'created_at',
                        'updated_at',
                        'theme',
                        'media'
                    ]
                ],
                'message'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function test_user_can_create_landing_with_auto_slug()
    {
        Sanctum::actingAs($this->user);

        $landingData = [
            'couple_names' => 'Juan & María',
            'anniversary_date' => '2020-06-15',
            'theme_id' => $this->theme->id,
            'bio_text' => 'Una historia de amor única'
        ];

        $response = $this->postJson('/api/landings', $landingData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'slug',
                    'couple_names',
                    'anniversary_date',
                    'theme_id',
                    'bio_text',
                    'theme',
                    'media'
                ],
                'message'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.couple_names', 'Juan & María')
            ->assertJsonPath('data.user_id', $this->user->id);

        $this->assertDatabaseHas('landings', [
            'couple_names' => 'Juan & María',
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id
        ]);
    }

    /** @test */
    public function test_user_can_create_landing_with_custom_slug()
    {
        Sanctum::actingAs($this->user);

        $landingData = [
            'couple_names' => 'Ana & Carlos',
            'slug' => 'ana-carlos-2024',
            'anniversary_date' => '2021-12-25',
            'theme_id' => $this->theme->id
        ];

        $response = $this->postJson('/api/landings', $landingData);

        $response->assertStatus(201)
            ->assertJsonPath('data.slug', 'ana-carlos-2024');

        $this->assertDatabaseHas('landings', [
            'slug' => 'ana-carlos-2024',
            'couple_names' => 'Ana & Carlos'
        ]);
    }

    /** @test */
    public function test_anyone_can_view_landing_by_id()
    {
        $landing = Landing::factory()->create([
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id
        ]);

        // Sin autenticación
        $response = $this->getJson("/api/landings/{$landing->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'slug',
                    'couple_names',
                    'anniversary_date',
                    'bio_text',
                    'theme',
                    'media',
                    'user'
                ],
                'message'
            ])
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_anyone_can_view_landing_by_slug()
    {
        $landing = Landing::factory()->create([
            'slug' => 'maria-pedro-bodas',
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id
        ]);

        // Sin autenticación
        $response = $this->getJson('/api/landings/maria-pedro-bodas');

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'maria-pedro-bodas');
    }

    /** @test */
    public function test_user_can_update_own_landing()
    {
        $landing = Landing::factory()->create([
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id
        ]);

        Sanctum::actingAs($this->user);

        $updateData = [
            'couple_names' => 'Nuevos Nombres',
            'bio_text' => 'Nueva biografía actualizada'
        ];

        $response = $this->putJson("/api/landings/{$landing->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.couple_names', 'Nuevos Nombres')
            ->assertJsonPath('data.bio_text', 'Nueva biografía actualizada');

        $this->assertDatabaseHas('landings', [
            'id' => $landing->id,
            'couple_names' => 'Nuevos Nombres'
        ]);
    }

    /** @test */
    public function test_user_can_delete_own_landing()
    {
        $landing = Landing::factory()->create([
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/landings/{$landing->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('landings', [
            'id' => $landing->id
        ]);
    }

    /** @test */
    public function test_landing_creation_requires_authentication()
    {
        $landingData = [
            'couple_names' => 'Test Couple',
            'anniversary_date' => '2020-01-01',
            'theme_id' => $this->theme->id
        ];

        $response = $this->postJson('/api/landings', $landingData);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_landing_creation_validates_required_fields()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/landings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'couple_names',
                'anniversary_date', 
                'theme_id'
            ]);
    }

    /** @test */
    public function test_landing_creation_validates_theme_exists()
    {
        Sanctum::actingAs($this->user);

        $landingData = [
            'couple_names' => 'Test Couple',
            'anniversary_date' => '2020-01-01',
            'theme_id' => 999999 // ID inexistente
        ];

        $response = $this->postJson('/api/landings', $landingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['theme_id']);
    }

    /** @test */
    public function test_landing_creation_validates_unique_slug_per_user()
    {
        // Crear landing con slug específico
        Landing::factory()->create([
            'slug' => 'duplicate-slug',
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id
        ]);

        Sanctum::actingAs($this->user);

        $landingData = [
            'couple_names' => 'Different Couple',
            'slug' => 'duplicate-slug', // Mismo slug
            'anniversary_date' => '2021-01-01',
            'theme_id' => $this->theme->id
        ];

        $response = $this->postJson('/api/landings', $landingData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message'
            ]);
    }

    /** @test */
    public function test_user_cannot_update_other_user_landing()
    {
        $otherUser = User::factory()->create();
        $landing = Landing::factory()->create([
            'user_id' => $otherUser->id,
            'theme_id' => $this->theme->id
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/landings/{$landing->id}", [
            'couple_names' => 'Hacked Names'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_user_cannot_delete_other_user_landing()
    {
        $otherUser = User::factory()->create();
        $landing = Landing::factory()->create([
            'user_id' => $otherUser->id,
            'theme_id' => $this->theme->id
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/landings/{$landing->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function test_landing_not_found_returns_404()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/landings/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_slug_not_found_returns_404()
    {
        $response = $this->getJson('/api/landings/nonexistent-slug');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_landing_creation_validates_slug_format()
    {
        Sanctum::actingAs($this->user);

        $landingData = [
            'couple_names' => 'Test Couple',
            'slug' => 'Invalid Slug With Spaces!', // Formato inválido
            'anniversary_date' => '2020-01-01',
            'theme_id' => $this->theme->id
        ];

        $response = $this->postJson('/api/landings', $landingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function test_landing_creation_validates_date_format()
    {
        Sanctum::actingAs($this->user);

        $landingData = [
            'couple_names' => 'Test Couple',
            'anniversary_date' => 'invalid-date',
            'theme_id' => $this->theme->id
        ];

        $response = $this->postJson('/api/landings', $landingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['anniversary_date']);
    }
}