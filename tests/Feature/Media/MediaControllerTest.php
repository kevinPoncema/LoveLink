<?php

namespace Tests\Feature\Media;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function test_user_can_list_accessible_media()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        // Crear media para el usuario autenticado
        $userMedia = Media::factory()->create(['user_id' => $user->id]);
        
        // Crear media para otro usuario (no debería aparecer)
        Media::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/media');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function test_user_can_upload_valid_image()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(1024); // 1MB

        $response = $this->postJson('/api/media', [
            'file' => $file
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user_id',
                    'filename',
                    'path',
                    'mime_type',
                    'size',
                    'url',
                    'created_at',
                    'updated_at'
                ],
                'message'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.filename', 'test.jpg');

        $this->assertDatabaseHas('media', [
            'user_id' => $user->id,
            'filename' => 'test.jpg',
            'mime_type' => 'image/jpeg'
        ]);

        // Verificar que el archivo se guardó
        $media = Media::where('user_id', $user->id)->first();
        Storage::disk('public')->assertExists($media->path);
    }

    /** @test */
    public function test_user_can_delete_unused_media()
    {
        $user = User::factory()->create();
        $media = Media::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/media/{$media->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    /** @test */
    public function test_media_operations_require_authentication()
    {
        $response = $this->getJson('/api/media');
        $response->assertStatus(401);

        $response = $this->postJson('/api/media', [
            'file' => UploadedFile::fake()->image('test.jpg')
        ]);
        $response->assertStatus(401);

        $media = Media::factory()->create();
        $response = $this->deleteJson("/api/media/{$media->id}");
        $response->assertStatus(401);
    }

    /** @test */
    public function test_media_upload_validates_file_type()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->postJson('/api/media', [
            'file' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function test_media_upload_validates_file_size()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Crear archivo de 15MB (mayor al límite de 10MB)
        $file = UploadedFile::fake()->image('large.jpg')->size(15360);

        $response = $this->postJson('/api/media', [
            'file' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function test_media_upload_requires_file()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/media', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function test_user_cannot_delete_other_user_media()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $media = Media::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/media/{$media->id}");

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    /** @test */
    public function test_media_not_found_returns_error_on_delete()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/media/999');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function test_cannot_delete_media_in_use_by_theme()
    {
        $user = User::factory()->create();
        $media = Media::factory()->create(['user_id' => $user->id]);
        
        // Crear tema que usa este media como imagen de fondo
        $theme = \App\Models\Theme::factory()->create([
            'user_id' => $user->id,
            'bg_image_media_id' => $media->id
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/media/{$media->id}");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }
}