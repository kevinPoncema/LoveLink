<?php

namespace Tests\Feature\Themes;

use App\Models\Media;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ThemeBackgroundImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function test_user_can_create_theme_with_background_image()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('background.jpg', 1920, 1080);

        $response = $this->postJson('/api/themes', [
            'name' => 'Tema con Fondo',
            'description' => 'Tema personalizado con imagen de fondo',
            'primary_color' => '#FF0000',
            'secondary_color' => '#00FF00',
            'bg_color' => '#0000FF',
            'css_class' => 'theme-custom-bg',
            'bg_image_file' => $file
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'primary_color',
                    'secondary_color',
                    'bg_color',
                    'bg_image_url',
                    'bg_image_media_id',
                    'css_class',
                    'user_id',
                    'background_image'
                ],
                'message'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Tema con Fondo')
            ->assertJsonPath('data.user_id', $user->id);

        // Verificar que se creó el media
        $this->assertDatabaseHas('media', [
            'user_id' => $user->id,
            'filename' => 'background.jpg',
            'mime_type' => 'image/jpeg'
        ]);

        // Verificar que el tema tiene referencia al media
        $theme = Theme::where('user_id', $user->id)->first();
        $this->assertNotNull($theme->bg_image_media_id);
        $this->assertNotNull($theme->bg_image_url);
    }

    /** @test */
    public function test_user_can_update_theme_background_image()
    {
        $user = User::factory()->create();
        $theme = Theme::factory()->forUser($user)->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('new-background.png', 1920, 1080);

        $response = $this->putJson("/api/themes/{$theme->id}", [
            'name' => 'Tema Actualizado',
            'bg_image_file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Tema Actualizado');

        // Verificar que se creó el nuevo media
        $this->assertDatabaseHas('media', [
            'user_id' => $user->id,
            'filename' => 'new-background.png',
            'mime_type' => 'image/png'
        ]);

        // Verificar que el tema se actualizó
        $theme->refresh();
        $this->assertNotNull($theme->bg_image_media_id);
        $this->assertNotNull($theme->bg_image_url);
        $this->assertEquals('Tema Actualizado', $theme->name);
    }

    /** @test */
    public function test_theme_creation_validates_background_image_file()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->postJson('/api/themes', [
            'name' => 'Tema con Archivo Inválido',
            'primary_color' => '#FF0000',
            'secondary_color' => '#00FF00',
            'bg_color' => '#0000FF',
            'css_class' => 'theme-invalid',
            'bg_image_file' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bg_image_file']);
    }

    /** @test */
    public function test_theme_background_image_validates_file_size()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Crear archivo de 15MB (mayor al límite de 10MB)
        $file = UploadedFile::fake()->image('large.jpg')->size(15360);

        $response = $this->postJson('/api/themes', [
            'name' => 'Tema con Archivo Grande',
            'primary_color' => '#FF0000',
            'secondary_color' => '#00FF00',
            'bg_color' => '#0000FF',
            'css_class' => 'theme-large',
            'bg_image_file' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bg_image_file']);
    }

    /** @test */
    public function test_deleting_theme_with_background_image_removes_media()
    {
        $user = User::factory()->create();
        $media = Media::factory()->forUser($user)->create();
        $theme = Theme::factory()->forUser($user)->create([
            'bg_image_media_id' => $media->id,
            'bg_image_url' => $media->url
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/themes/{$theme->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verificar que el tema se eliminó
        $this->assertDatabaseMissing('themes', ['id' => $theme->id]);

        // Verificar que el media también se eliminó
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    /** @test */
    public function test_updating_theme_with_new_background_image_removes_old_media()
    {
        $user = User::factory()->create();
        $oldMedia = Media::factory()->forUser($user)->create();
        $theme = Theme::factory()->forUser($user)->create([
            'bg_image_media_id' => $oldMedia->id,
            'bg_image_url' => $oldMedia->url
        ]);

        Sanctum::actingAs($user);

        $newFile = UploadedFile::fake()->image('new-background.jpg', 1920, 1080);

        $response = $this->putJson("/api/themes/{$theme->id}", [
            'bg_image_file' => $newFile
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verificar que el media anterior se eliminó
        $this->assertDatabaseMissing('media', ['id' => $oldMedia->id]);

        // Verificar que se creó nuevo media
        $this->assertDatabaseHas('media', [
            'user_id' => $user->id,
            'filename' => 'new-background.jpg'
        ]);

        // Verificar que el tema se actualizó con el nuevo media
        $theme->refresh();
        $this->assertNotEquals($oldMedia->id, $theme->bg_image_media_id);
    }

    /** @test */
    public function test_cannot_delete_media_used_as_theme_background()
    {
        $user = User::factory()->create();
        $media = Media::factory()->forUser($user)->create();
        $theme = Theme::factory()->forUser($user)->create([
            'bg_image_media_id' => $media->id,
            'bg_image_url' => $media->url
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/media/{$media->id}");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        // Verificar que el media no se eliminó
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }
}