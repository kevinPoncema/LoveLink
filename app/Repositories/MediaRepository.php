<?php

namespace App\Repositories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;

class MediaRepository
{
    /**
     * Encuentra media accesible por el usuario
     */
    public function findUserAccessible(int $userId): Collection
    {
        return Media::where('user_id', $userId)->get();
    }

    /**
     * Crea un nuevo media
     */
    public function create(array $data): Media
    {
        return Media::create($data);
    }

    /**
     * Encuentra media por ID
     */
    public function findById(int $id): ?Media
    {
        return Media::find($id);
    }

    /**
     * Elimina media
     */
    public function delete(int $id): bool
    {
        return Media::destroy($id) > 0;
    }

    /**
     * Verifica si el media está vinculado a alguna entidad
     */
    public function isLinkedToAnyEntity(int $mediaId): bool
    {
        $media = Media::find($mediaId);
        
        if (!$media) {
            return false;
        }

        // Verificar si está vinculado a themes como imagen de fondo
        $linkedToTheme = $media->themes()->exists();

        if ($linkedToTheme) {
            return true;
        }

        // Verificar si está vinculado a landings
        $linkedToLanding = $media->landings()->exists();

        if ($linkedToLanding) {
            return true;
        }

        // Verificar si está vinculado a invitations
        $linkedToInvitation = $media->invitations()->exists();

        return $linkedToInvitation;
    }

    /**
     * Obtiene media por usuario con relaciones
     */
    public function findUserAccessibleWithRelations(int $userId): Collection
    {
        return Media::where('user_id', $userId)
            ->with(['themes', 'landings', 'invitations'])
            ->get();
    }

    /**
     * Cuenta el total de media por usuario
     */
    public function countByUser(int $userId): int
    {
        return Media::where('user_id', $userId)->count();
    }

    /**
     * Encuentra media por tipo MIME
     */
    public function findByMimeType(string $mimeType, int $userId): Collection
    {
        return Media::where('user_id', $userId)
            ->where('mime_type', $mimeType)
            ->get();
    }

    /**
     * Encuentra solo imágenes del usuario
     */
    public function findUserImages(int $userId): Collection
    {
        return Media::where('user_id', $userId)
            ->where('mime_type', 'like', 'image/%')
            ->get();
    }

    /**
     * Vincula media a una landing con orden
     */
    public function attachToLanding(int $landingId, int $mediaId, int $sortOrder): void
    {
        $media = Media::findOrFail($mediaId);
        
        // Si ya existe, actualizar orden, si no, crear nueva vinculación
        if ($media->landings()->where('landing_id', $landingId)->exists()) {
            $media->landings()->updateExistingPivot($landingId, ['sort_order' => $sortOrder]);
        } else {
            $media->landings()->attach($landingId, ['sort_order' => $sortOrder]);
        }
    }

    /**
     * Desvincula media de una landing
     */
    public function detachFromLanding(int $landingId, int $mediaId): void
    {
        $media = Media::findOrFail($mediaId);
        $media->landings()->detach($landingId);
    }

    /**
     * Vincula media a una invitation
     */
    public function attachToInvitation(int $invitationId, int $mediaId): void
    {
        $media = Media::findOrFail($mediaId);
        
        if (!$media->invitations()->where('invitation_id', $invitationId)->exists()) {
            $media->invitations()->attach($invitationId);
        }
    }

    /**
     * Desvincula media de una invitation
     */
    public function detachFromInvitation(int $invitationId, int $mediaId): void
    {
        $media = Media::findOrFail($mediaId);
        $media->invitations()->detach($invitationId);
    }

    /**
     * Actualiza el orden de media en una landing
     */
    public function updateLandingMediaOrder(int $landingId, array $orders): void
    {
        foreach ($orders as $item) {
            if (isset($item['media_id']) && isset($item['sort_order'])) {
                $media = Media::findOrFail($item['media_id']);
                $media->landings()->updateExistingPivot(
                    $landingId, 
                    ['sort_order' => $item['sort_order']]
                );
            }
        }
    }

    /**
     * Cuenta media vinculado a una entidad específica
     */
    public function countMediaByEntity(string $entityType, int $entityId): int
    {
        switch ($entityType) {
            case 'landing':
                $media = Media::query();
                return $media->whereHas('landings', function($query) use ($entityId) {
                    $query->where('landing_id', $entityId);
                })->count();
                
            case 'invitation':
                $media = Media::query();
                return $media->whereHas('invitations', function($query) use ($entityId) {
                    $query->where('invitation_id', $entityId);
                })->count();
                
            case 'theme':
                return Media::whereHas('themes', function($query) use ($entityId) {
                    $query->where('theme_id', $entityId);
                })->count();
                
            default:
                return 0;
        }
    }
}