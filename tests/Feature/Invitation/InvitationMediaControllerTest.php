<?php

namespace Tests\Feature\Invitation;

use App\Models\Invitation;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvitationMediaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->invitation = Invitation::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Storage::fake('public');
    }

    /** @test */
    public function test_user_can_attach_media_to_own_invitation()
    {
        $media = Media::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/invitations/{$this->invitation->id}/media", [
            'media_id' => $media->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Media vinculado a la invitation exitosamente.',
            ]);

        $this->assertDatabaseHas('invitation_media', [
            'invitation_id' => $this->invitation->id,
            'media_id' => $media->id,
        ]);
    }

    /** @test */
    public function test_user_can_detach_media_from_own_invitation()
    {
        $media = Media::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Attach media first
        $this->invitation->media()->attach($media->id);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/invitations/{$this->invitation->id}/media/{$media->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Media desvinculado de la invitation exitosamente.',
            ]);

        $this->assertDatabaseMissing('invitation_media', [
            'invitation_id' => $this->invitation->id,
            'media_id' => $media->id,
        ]);
    }

    /** @test */
    public function test_media_attachment_requires_authentication()
    {
        $media = Media::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson("/api/invitations/{$this->invitation->id}/media", [
            'media_id' => $media->id,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_user_cannot_attach_media_to_other_user_invitation()
    {
        $otherUser = User::factory()->create();
        $otherInvitation = Invitation::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $media = Media::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/invitations/{$otherInvitation->id}/media", [
            'media_id' => $media->id,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_cannot_attach_non_existent_media()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/invitations/{$this->invitation->id}/media", [
            'media_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('media_id');
    }

    /** @test */
    public function test_cannot_attach_media_beyond_limit()
    {
        // Create 20 media files (the limit)
        $mediaFiles = Media::factory()->count(20)->create([
            'user_id' => $this->user->id,
        ]);

        // Attach all 20 media files
        foreach ($mediaFiles as $media) {
            $this->invitation->media()->attach($media->id);
        }

        // Try to attach one more
        $extraMedia = Media::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/invitations/{$this->invitation->id}/media", [
            'media_id' => $extraMedia->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_cannot_attach_other_user_media()
    {
        $otherUser = User::factory()->create();
        $media = Media::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/invitations/{$this->invitation->id}/media", [
            'media_id' => $media->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('media_id');
    }

    /** @test */
    public function test_media_attachment_validates_media_belongs_to_user()
    {
        $otherUser = User::factory()->create();
        $media = Media::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/invitations/{$this->invitation->id}/media", [
            'media_id' => $media->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('media_id')
            ->assertJsonFragment([
                'media_id' => ['El archivo multimedia seleccionado no te pertenece.'],
            ]);
    }

    /** @test */
    public function test_user_cannot_detach_media_from_other_user_invitation()
    {
        $otherUser = User::factory()->create();
        $otherInvitation = Invitation::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $media = Media::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/invitations/{$otherInvitation->id}/media/{$media->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function test_can_attach_same_media_to_different_invitations()
    {
        $invitation2 = Invitation::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $media = Media::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user);

        // Attach to first invitation
        $response1 = $this->postJson("/api/invitations/{$this->invitation->id}/media", [
            'media_id' => $media->id,
        ]);

        // Attach to second invitation
        $response2 = $this->postJson("/api/invitations/{$invitation2->id}/media", [
            'media_id' => $media->id,
        ]);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $this->assertDatabaseHas('invitation_media', [
            'invitation_id' => $this->invitation->id,
            'media_id' => $media->id,
        ]);

        $this->assertDatabaseHas('invitation_media', [
            'invitation_id' => $invitation2->id,
            'media_id' => $media->id,
        ]);
    }

    /** @test */
    public function test_cannot_attach_same_media_twice_to_same_invitation()
    {
        $media = Media::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Attach media first time
        $this->invitation->media()->attach($media->id);

        Sanctum::actingAs($this->user);

        // Try to attach same media again
        $response = $this->postJson("/api/invitations/{$this->invitation->id}/media", [
            'media_id' => $media->id,
        ]);

        $response->assertStatus(201); // Service handles duplicate silently

        // Should still have only one relation
        $this->assertEquals(1, $this->invitation->media()->count());
    }
}
