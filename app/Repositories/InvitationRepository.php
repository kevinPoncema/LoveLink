<?php

namespace App\Repositories;

use App\Models\Invitation;
use Illuminate\Database\Eloquent\Collection;

class InvitationRepository
{
    /**
     * Obtiene las invitations de un usuario (incluyendo soft deleted)
     */
    public function findByUser(int $userId): Collection
    {
        return Invitation::where('user_id', $userId)
            ->withTrashed()
            ->with(['media'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Busca una invitation por slug (solo publicadas y no eliminadas)
     */
    public function findBySlug(string $slug): ?Invitation
    {
        return Invitation::where('slug', $slug)
            ->where('is_published', true)
            ->with(['media', 'user'])
            ->first();
    }

    /**
     * Busca una invitation por ID
     */
    public function findById(int $id): ?Invitation
    {
        return Invitation::with(['media', 'user'])->find($id);
    }

    /**
     * Busca una invitation por ID incluyendo soft deleted
     */
    public function findByIdWithTrashed(int $id): ?Invitation
    {
        return Invitation::withTrashed()
            ->with(['media', 'user'])
            ->find($id);
    }

    /**
     * Crea una nueva invitation
     */
    public function create(array $data): Invitation
    {
        return Invitation::create($data);
    }

    /**
     * Actualiza una invitation existente
     */
    public function update(int $id, array $data): Invitation
    {
        $invitation = Invitation::findOrFail($id);
        $invitation->update($data);

        return $invitation->fresh(['media']);
    }

    /**
     * Elimina una invitation (soft delete)
     */
    public function delete(int $id): bool
    {
        $invitation = Invitation::find($id);

        return $invitation ? $invitation->delete() : false;
    }

    /**
     * Verifica si un slug está disponible para un usuario
     */
    public function isSlugAvailable(string $slug, int $userId, ?int $excludeId = null): bool
    {
        $query = Invitation::where('slug', $slug)
            ->where('user_id', $userId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return ! $query->exists();
    }

    /**
     * Vincula media a una invitation
     */
    public function attachMedia(int $invitationId, int $mediaId): void
    {
        $invitation = Invitation::find($invitationId);
        if ($invitation && ! $invitation->media()->where('media_id', $mediaId)->exists()) {
            $invitation->media()->attach($mediaId);
        }
    }

    /**
     * Desvincula media de una invitation
     */
    public function detachMedia(int $invitationId, int $mediaId): void
    {
        $invitation = Invitation::find($invitationId);
        if ($invitation) {
            $invitation->media()->detach($mediaId);
        }
    }

    /**
     * Cuenta cuántos media tiene una invitation
     */
    public function countMedia(int $invitationId): int
    {
        $invitation = Invitation::find($invitationId);

        return $invitation ? $invitation->media()->count() : 0;
    }

    /**
     * Busca invitations por criterios de filtro
     */
    public function findByCriteria(array $criteria): Collection
    {
        $query = Invitation::query();

        if (isset($criteria['user_id'])) {
            $query->where('user_id', $criteria['user_id']);
        }

        if (isset($criteria['is_published'])) {
            $query->where('is_published', $criteria['is_published']);
        }

        if (isset($criteria['include_trashed']) && $criteria['include_trashed']) {
            $query->withTrashed();
        }

        if (isset($criteria['search'])) {
            $query->where(function ($q) use ($criteria) {
                $q->where('title', 'like', '%'.$criteria['search'].'%')
                    ->orWhere('slug', 'like', '%'.$criteria['search'].'%');
            });
        }

        return $query->with(['media', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
