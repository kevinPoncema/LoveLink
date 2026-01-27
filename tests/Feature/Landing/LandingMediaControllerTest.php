<?php

namespace Tests\Feature\Landing;

use App\Models\Landing;
use App\Models\Media;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LandingMediaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Landing $landing;
    protected Theme $theme;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->user = User::factory()->create();
        $this->theme = Theme::factory()->create();
        $this->landing = Landing::factory()->create([
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id
        ]);
    }

    /** @test */
    public function test_user_can_attach_media_to_own_landing()
    {
        $media = Media::factory()->create(['user_id' => $this->user->id]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/landings/{$this->landing->id}/media", [
            'media_id' => $media->id,
            'sort_order' => 1
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('landing_media', [
            'landing_id' => $this->landing->id,
            'media_id' => $media->id,
            'sort_order' => 1
        ]);
    }

    /** @test */
    public function test_user_can_detach_media_from_own_landing()
    {
        $media = Media::factory()->create(['user_id' => $this->user->id]);
        
        // Vincular media primero
        $this->landing->media()->attach($media->id, ['sort_order' => 1]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/landings/{$this->landing->id}/media/{$media->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('landing_media', [
            'landing_id' => $this->landing->id,
            'media_id' => $media->id
        ]);
    }

    /** @test */
    public function test_user_can_reorder_landing_media()
    {
        $media1 = Media::factory()->create(['user_id' => $this->user->id]);
        $media2 = Media::factory()->create(['user_id' => $this->user->id]);
        $media3 = Media::factory()->create(['user_id' => $this->user->id]);

        // Vincular media con orden inicial
        $this->landing->media()->attach($media1->id, ['sort_order' => 1]);
        $this->landing->media()->attach($media2->id, ['sort_order' => 2]);
        $this->landing->media()->attach($media3->id, ['sort_order' => 3]);

        Sanctum::actingAs($this->user);

        $newOrder = [
            ['media_id' => $media3->id, 'sort_order' => 1],
            ['media_id' => $media1->id, 'sort_order' => 2],
            ['media_id' => $media2->id, 'sort_order' => 3],
        ];

        $response = $this->putJson("/api/landings/{$this->landing->id}/media/reorder", [
            'media_order' => $newOrder
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verificar nuevo orden en base de datos
        $this->assertDatabaseHas('landing_media', [
            'landing_id' => $this->landing->id,
            'media_id' => $media3->id,
            'sort_order' => 1
        ]);

        $this->assertDatabaseHas('landing_media', [
            'landing_id' => $this->landing->id,
            'media_id' => $media1->id,
            'sort_order' => 2
        ]);
    }

    /** @test */
    public function test_media_attachment_requires_authentication()
    {
        $media = Media::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson("/api/landings/{$this->landing->id}/media", [
            'media_id' => $media->id
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_user_cannot_attach_media_to_other_user_landing()
    {
        $otherUser = User::factory()->create();
        $otherLanding = Landing::factory()->create([
            'user_id' => $otherUser->id,
            'theme_id' => $this->theme->id
        ]);
        $media = Media::factory()->create(['user_id' => $this->user->id]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/landings/{$otherLanding->id}/media", [
            'media_id' => $media->id
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_cannot_attach_non_existent_media()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/landings/{$this->landing->id}/media", [
            'media_id' => 999999 // Media inexistente
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['media_id']);
    }

    /** @test */
    public function test_cannot_attach_other_user_media()
    {
        $otherUser = User::factory()->create();
        $otherUserMedia = Media::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/landings/{$this->landing->id}/media", [
            'media_id' => $otherUserMedia->id
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_media_reorder_validates_media_belongs_to_landing()
    {
        $media1 = Media::factory()->create(['user_id' => $this->user->id]);
        $media2 = Media::factory()->create(['user_id' => $this->user->id]);
        $mediaNotInLanding = Media::factory()->create(['user_id' => $this->user->id]);

        // Solo vincular media1 y media2 a la landing
        $this->landing->media()->attach($media1->id, ['sort_order' => 1]);
        $this->landing->media()->attach($media2->id, ['sort_order' => 2]);

        Sanctum::actingAs($this->user);

        // Intentar reordenar incluyendo media que no pertenece a la landing
        $newOrder = [
            ['media_id' => $media1->id, 'sort_order' => 1],
            ['media_id' => $mediaNotInLanding->id, 'sort_order' => 2], // Media no vinculado
        ];

        $response = $this->putJson("/api/landings/{$this->landing->id}/media/reorder", [
            'media_order' => $newOrder
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_media_attachment_validates_sort_order()
    {
        $media = Media::factory()->create(['user_id' => $this->user->id]);

        Sanctum::actingAs($this->user);

        // Orden inválido (menor a 1)
        $response = $this->postJson("/api/landings/{$this->landing->id}/media", [
            'media_id' => $media->id,
            'sort_order' => 0
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort_order']);
    }

    /** @test */
    public function test_media_reorder_validates_required_fields()
    {
        Sanctum::actingAs($this->user);

        // Sin media_order
        $response = $this->putJson("/api/landings/{$this->landing->id}/media/reorder", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['media_order']);

        // media_order vacío
        $response = $this->putJson("/api/landings/{$this->landing->id}/media/reorder", [
            'media_order' => []
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['media_order']);
    }

    /** @test */
    public function test_media_reorder_validates_array_structure()
    {
        $media = Media::factory()->create(['user_id' => $this->user->id]);
        $this->landing->media()->attach($media->id, ['sort_order' => 1]);

        Sanctum::actingAs($this->user);

        // Estructura de array inválida
        $response = $this->putJson("/api/landings/{$this->landing->id}/media/reorder", [
            'media_order' => [
                ['media_id' => $media->id] // Falta sort_order
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['media_order.0.sort_order']);

        // Sort order inválido
        $response = $this->putJson("/api/landings/{$this->landing->id}/media/reorder", [
            'media_order' => [
                ['media_id' => $media->id, 'sort_order' => 'invalid'] // No numérico
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['media_order.0.sort_order']);
    }

    /** @test */
    public function test_media_attachment_without_sort_order_uses_next_available()
    {
        $existingMedia = Media::factory()->create(['user_id' => $this->user->id]);
        $newMedia = Media::factory()->create(['user_id' => $this->user->id]);

        // Vincular media existente con orden 1
        $this->landing->media()->attach($existingMedia->id, ['sort_order' => 1]);

        Sanctum::actingAs($this->user);

        // Vincular nuevo media sin especificar orden
        $response = $this->postJson("/api/landings/{$this->landing->id}/media", [
            'media_id' => $newMedia->id
        ]);

        $response->assertStatus(201);

        // Debería tener orden 2 (siguiente disponible)
        $this->assertDatabaseHas('landing_media', [
            'landing_id' => $this->landing->id,
            'media_id' => $newMedia->id,
            'sort_order' => 2
        ]);
    }

    /** @test */
    public function test_detaching_nonexistent_media_returns_success()
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/landings/{$this->landing->id}/media/999999");

        // Debería ser exitoso aunque el media no exista o no esté vinculado
        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_cannot_reorder_other_user_landing_media()
    {
        $otherUser = User::factory()->create();
        $otherLanding = Landing::factory()->create([
            'user_id' => $otherUser->id,
            'theme_id' => $this->theme->id
        ]);

        $media = Media::factory()->create(['user_id' => $otherUser->id]);
        $otherLanding->media()->attach($media->id, ['sort_order' => 1]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/landings/{$otherLanding->id}/media/reorder", [
            'media_order' => [
                ['media_id' => $media->id, 'sort_order' => 1]
            ]
        ]);

        $response->assertStatus(403);
    }
}