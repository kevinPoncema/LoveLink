<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use App\Repositories\InvitationRepository;
use App\Repositories\MediaRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class InvitationService
{
    const MAX_MEDIA_PER_INVITATION = 20;

    public function __construct(
        protected InvitationRepository $invitationRepository,
        protected MediaRepository $mediaRepository
    ) {}

    /**
     * Obtiene las invitations de un usuario autenticado (incluyendo soft deleted)
     */
    public function getUserInvitations(int $userId): Collection
    {
        return $this->invitationRepository->findByUser($userId);
    }

    /**
     * Crea una nueva invitation con slug generado si no se proporciona
     */
    public function createInvitation(User $user, array $data): Invitation
    {
        // Generar slug si no se proporciona
        if (! isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $user->id);
        } else {
            // Validar que el slug sea único para el usuario
            if (! $this->validateSlugUniqueness($data['slug'], $user->id)) {
                throw new \InvalidArgumentException('El slug ya existe para este usuario');
            }
        }

        $data['user_id'] = $user->id;

        // Establecer valores por defecto si no están definidos
        if (! isset($data['yes_message'])) {
            $data['yes_message'] = 'Sí';
        }

        if (! isset($data['no_messages']) || empty($data['no_messages'])) {
            $data['no_messages'] = ['No', 'Tal vez', 'No te arrepentirás', 'Piénsalo mejor'];
        }

        if (! isset($data['is_published'])) {
            $data['is_published'] = false;
        }

        return $this->invitationRepository->create($data);
    }

    /**
     * Actualiza una invitation existente
     */
    public function updateInvitation(int $id, array $data, User $user): Invitation
    {
        $invitation = $this->invitationRepository->findByIdWithTrashed($id);

        if (! $invitation) {
            throw new ModelNotFoundException('Invitation no encontrada');
        }

        if ($invitation->user_id !== $user->id) {
            throw new \Exception('No tienes permisos para actualizar esta invitation', 403);
        }

        // Si se actualiza el slug, verificar unicidad
        if (isset($data['slug']) && $data['slug'] !== $invitation->slug) {
            if (! $this->validateSlugUniqueness($data['slug'], $user->id, $id)) {
                throw new \InvalidArgumentException('El slug ya existe para este usuario');
            }
        }

        return $this->invitationRepository->update($id, $data);
    }

    /**
     * Elimina una invitation del usuario (soft delete)
     */
    public function deleteInvitation(int $id, User $user): bool
    {
        $invitation = $this->invitationRepository->findById($id);

        if (! $invitation) {
            throw new ModelNotFoundException('Invitation no encontrada');
        }

        if ($invitation->user_id !== $user->id) {
            throw new \Exception('No tienes permisos para eliminar esta invitation', 403);
        }

        return $this->invitationRepository->delete($id);
    }

    /**
     * Busca una invitation por slug (público)
     */
    public function findPublicInvitationBySlug(string $slug): ?Invitation
    {
        return $this->invitationRepository->findBySlug($slug);
    }

    /**
     * Busca una invitation por ID
     */
    public function findInvitationById(int $id, ?User $user = null): ?Invitation
    {
        $invitation = $this->invitationRepository->findById($id);

        // Si hay usuario, verificar permisos
        if ($user && $invitation && $invitation->user_id !== $user->id) {
            throw new \Exception('No tienes permisos para ver esta invitation', 403);
        }

        return $invitation;
    }

    /**
     * Valida si un media puede ser vinculado a la invitation
     */
    public function validateMediaLimit(int $invitationId): bool
    {
        return $this->invitationRepository->countMedia($invitationId) < self::MAX_MEDIA_PER_INVITATION;
    }

    /**
     * Vincula media a una invitation
     */
    public function attachMediaToInvitation(int $invitationId, int $mediaId, int $userId): void
    {
        // Verificar que la invitation pertenezca al usuario
        $invitation = $this->invitationRepository->findById($invitationId);
        if (! $invitation || $invitation->user_id !== $userId) {
            throw new \Exception('No tienes permisos para modificar esta invitation', 403);
        }

        // Verificar que el media pertenezca al usuario
        $media = $this->mediaRepository->findById($mediaId);
        if (! $media || $media->user_id !== $userId) {
            throw new \Exception('No tienes permisos para usar este media', 403);
        }

        // Verificar límite de media
        if (! $this->validateMediaLimit($invitationId)) {
            throw new \Exception('Has alcanzado el límite máximo de archivos multimedia para esta invitation', 422);
        }

        $this->invitationRepository->attachMedia($invitationId, $mediaId);
    }

    /**
     * Desvincula media de una invitation
     */
    public function detachMediaFromInvitation(int $invitationId, int $mediaId, int $userId): void
    {
        // Verificar que la invitation pertenezca al usuario
        $invitation = $this->invitationRepository->findById($invitationId);
        if (! $invitation || $invitation->user_id !== $userId) {
            throw new \Exception('No tienes permisos para modificar esta invitation', 403);
        }

        $this->invitationRepository->detachMedia($invitationId, $mediaId);
    }

    /**
     * Genera un slug único basado en el título
     */
    public function generateUniqueSlug(string $title, int $userId): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (! $this->validateSlugUniqueness($slug, $userId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Valida que un slug sea único para el usuario
     */
    public function validateSlugUniqueness(string $slug, int $userId, ?int $excludeId = null): bool
    {
        return $this->invitationRepository->isSlugAvailable($slug, $userId, $excludeId);
    }

    /**
     * Busca invitations por criterios
     */
    public function searchInvitations(array $criteria): Collection
    {
        return $this->invitationRepository->findByCriteria($criteria);
    }

    /**
     * Verifica si una invitation es visible públicamente
     */
    public function isPubliclyVisible(Invitation $invitation): bool
    {
        return $invitation->isPubliclyVisible();
    }
}
