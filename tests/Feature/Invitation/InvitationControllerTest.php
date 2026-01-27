<?php

namespace Tests\Feature\Invitation;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvitationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_user_can_list_own_invitations()
    {
        $otherUser = User::factory()->create();

        // Crear invitations para el usuario autenticado
        Invitation::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Crear invitation para otro usuario (no debería aparecer)
        Invitation::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/invitations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'slug',
                        'title',
                        'yes_message',
                        'no_messages',
                        'is_published',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function test_user_can_create_invitation_with_title()
    {
        Sanctum::actingAs($this->user);

        $data = [
            'title' => '¿Quieres ser mi San Valentín?',
        ];

        $response = $this->postJson('/api/invitations', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user_id',
                    'slug',
                    'title',
                    'yes_message',
                    'no_messages',
                    'is_published',
                    'media',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => '¿Quieres ser mi San Valentín?',
                    'user_id' => $this->user->id,
                    'yes_message' => 'Sí',
                    'is_published' => false,
                ],
            ]);

        $this->assertDatabaseHas('invitations', [
            'user_id' => $this->user->id,
            'title' => '¿Quieres ser mi San Valentín?',
            'slug' => 'quieres-ser-mi-san-valentin',
        ]);
    }

    /** @test */
    public function test_user_can_create_invitation_with_custom_slug()
    {
        Sanctum::actingAs($this->user);

        $data = [
            'title' => 'Mi invitación especial',
            'slug' => 'mi-slug-personalizado',
        ];

        $response = $this->postJson('/api/invitations', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Mi invitación especial',
                    'slug' => 'mi-slug-personalizado',
                ],
            ]);

        $this->assertDatabaseHas('invitations', [
            'user_id' => $this->user->id,
            'title' => 'Mi invitación especial',
            'slug' => 'mi-slug-personalizado',
        ]);
    }

    /** @test */
    public function test_anyone_can_view_published_invitation_by_slug()
    {
        $invitation = Invitation::factory()->create([
            'user_id' => $this->user->id,
            'slug' => 'mi-invitacion-publica',
            'is_published' => true,
        ]);

        $response = $this->getJson("/api/invitations/{$invitation->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'slug',
                    'title',
                    'yes_message',
                    'no_messages',
                    'is_published',
                    'media',
                    'user',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'slug' => 'mi-invitacion-publica',
                    'is_published' => true,
                ],
            ]);
    }

    /** @test */
    public function test_cannot_view_unpublished_invitation_by_slug()
    {
        $invitation = Invitation::factory()->create([
            'user_id' => $this->user->id,
            'slug' => 'mi-invitacion-privada',
            'is_published' => false,
        ]);

        $response = $this->getJson("/api/invitations/{$invitation->slug}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Invitation no encontrada.',
            ]);
    }

    /** @test */
    public function test_user_can_view_own_invitation_by_id()
    {
        $invitation = Invitation::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/invitations/{$invitation->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $invitation->id,
                    'user_id' => $this->user->id,
                ],
            ]);
    }

    /** @test */
    public function test_user_cannot_view_other_user_invitation_by_id()
    {
        $otherUser = User::factory()->create();
        $invitation = Invitation::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/invitations/{$invitation->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_user_can_update_own_invitation()
    {
        $invitation = Invitation::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Título original',
        ]);

        Sanctum::actingAs($this->user);

        $updateData = [
            'title' => 'Título actualizado',
            'is_published' => true,
        ];

        $response = $this->putJson("/api/invitations/{$invitation->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $invitation->id,
                    'title' => 'Título actualizado',
                    'is_published' => true,
                ],
            ]);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'title' => 'Título actualizado',
            'is_published' => true,
        ]);
    }

    /** @test */
    public function test_user_cannot_update_other_user_invitation()
    {
        $otherUser = User::factory()->create();
        $invitation = Invitation::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $updateData = [
            'title' => 'Título actualizado',
        ];

        $response = $this->putJson("/api/invitations/{$invitation->id}", $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_user_can_soft_delete_own_invitation()
    {
        $invitation = Invitation::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/invitations/{$invitation->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Invitation eliminada exitosamente.',
            ]);

        $this->assertSoftDeleted('invitations', [
            'id' => $invitation->id,
        ]);
    }

    /** @test */
    public function test_user_cannot_delete_other_user_invitation()
    {
        $otherUser = User::factory()->create();
        $invitation = Invitation::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/invitations/{$invitation->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function test_invitation_creation_requires_authentication()
    {
        $data = [
            'title' => 'Mi nueva invitación',
        ];

        $response = $this->postJson('/api/invitations', $data);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_invitation_creation_requires_title()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/invitations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    /** @test */
    public function test_invitation_creation_validates_slug_uniqueness()
    {
        // Crear invitation existente
        Invitation::factory()->create([
            'user_id' => $this->user->id,
            'slug' => 'slug-existente',
        ]);

        Sanctum::actingAs($this->user);

        $data = [
            'title' => 'Nueva invitación',
            'slug' => 'slug-existente',
        ];

        $response = $this->postJson('/api/invitations', $data);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_invitation_not_found_returns_404()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/invitations/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Invitation no encontrada.',
            ]);
    }

    /** @test */
    public function test_creates_invitation_with_default_values()
    {
        Sanctum::actingAs($this->user);

        $data = [
            'title' => 'Mi invitación',
        ];

        $response = $this->postJson('/api/invitations', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Mi invitación',
                    'yes_message' => 'Sí',
                    'no_messages' => ['No', 'Tal vez', 'No te arrepentirás', 'Piénsalo mejor'],
                    'is_published' => false,
                ],
            ]);
    }
}
