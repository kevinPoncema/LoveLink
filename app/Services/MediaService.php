<?php

namespace App\Services;

use App\Models\Media;
use App\Repositories\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function __construct(
        protected MediaRepository $mediaRepository
    ) {}

    /**
     * Sube un archivo multimedia
     */
    public function uploadMedia(UploadedFile $file, int $userId): Media
    {
        $this->validateFile($file);
        
        $path = $this->generateFilePath($file);
        
        $storedPath = Storage::disk('public')->putFileAs(
            "media/users/{$userId}", 
            $file, 
            $path
        );
        
        $url = Storage::disk('public')->url($storedPath);
        
        return $this->mediaRepository->create([
            'user_id' => $userId,
            'filename' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'url' => $url,
        ]);
    }

    /**
     * Elimina un archivo multimedia
     */
    public function deleteMedia(int $mediaId, int $userId): bool
    {
        $media = $this->mediaRepository->findById($mediaId);
        
        if (!$media || $media->user_id !== $userId) {
            return false;
        }
        
        if ($this->isMediaInUse($mediaId)) {
            return false;
        }
        
        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }
        
        return $this->mediaRepository->delete($mediaId);
    }

    /**
     * Elimina media forzosamente (por el sistema)
     * Utilizado cuando se actualiza o elimina un tema que usa el media
     */
    public function forceDeleteMedia(int $mediaId): bool
    {
        $media = $this->mediaRepository->findById($mediaId);
        
        if (!$media) {
            return false;
        }
        
        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }
        
        return $this->mediaRepository->delete($mediaId);
    }

    /**
     * Valida un archivo
     */
    public function validateFile(UploadedFile $file): bool
    {
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new \InvalidArgumentException('Tipo de archivo no permitido');
        }
        
        if ($file->getSize() > 10485760) { // 10MB
            throw new \InvalidArgumentException('El archivo es demasiado grande (máximo 10MB)');
        }
        
        return true;
    }

    /**
     * Genera una ruta única para el archivo
     */
    public function generateFilePath(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        return $filename;
    }

    /**
     * Verifica si el media está en uso
     */
    public function isMediaInUse(int $mediaId): bool
    {
        return $this->mediaRepository->isLinkedToAnyEntity($mediaId);
    }

    /**
     * Valida que el usuario sea propietario del media
     */
    public function validateUserOwnership(int $mediaId, int $userId): bool
    {
        $media = $this->mediaRepository->findById($mediaId);
        return $media && $media->user_id === $userId;
    }

    /**
     * Sube una imagen específicamente para tema de fondo
     */
    public function uploadThemeBackgroundImage(UploadedFile $file, int $userId): Media
    {
        $this->validateFile($file);
        
        $path = $this->generateFilePath($file);
        
        $storedPath = Storage::disk('public')->putFileAs(
            "media/themes/{$userId}", 
            $file, 
            $path
        );
        
        $url = Storage::disk('public')->url($storedPath);
        
        return $this->mediaRepository->create([
            'user_id' => $userId,
            'filename' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'url' => $url,
        ]);
    }

    /**
     * Obtiene media accesible por el usuario
     */
    public function getUserAccessibleMedia(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->mediaRepository->findUserAccessible($userId);
    }

    /**
     * Vincula media a un tema (para imagen de fondo)
     */
    public function attachToTheme(int $themeId, int $mediaId, int $userId): void
    {
        if (!$this->validateUserOwnership($mediaId, $userId)) {
            throw new \InvalidArgumentException('No tienes permisos sobre este media');
        }
    }

    /**
     * Valida límite de media por entidad
     */
    public function validateMediaLimit(string $entityType, int $entityId, int $limit): bool
    {
        $currentCount = $this->mediaRepository->countMediaByEntity($entityType, $entityId);
        return $currentCount < $limit;
    }

    /**
     * Vincula media a una landing (método específico para Landing)
     */
    public function attachToLanding(int $landingId, int $mediaId, int $userId, int $sortOrder): void
    {
        if (!$this->validateUserOwnership($mediaId, $userId)) {
            throw new \InvalidArgumentException('No tienes permisos sobre este media');
        }

        $this->mediaRepository->attachToLanding($landingId, $mediaId, $sortOrder);
    }

    /**
     * Desvincula media de una landing
     */
    public function detachFromLanding(int $landingId, int $mediaId, int $userId): void
    {
        if (!$this->validateUserOwnership($mediaId, $userId)) {
            throw new \InvalidArgumentException('No tienes permisos sobre este media');
        }

        $this->mediaRepository->detachFromLanding($landingId, $mediaId);
    }

    /**
     * Vincula media a una invitation
     */
    public function attachToInvitation(int $invitationId, int $mediaId, int $userId): void
    {
        if (!$this->validateUserOwnership($mediaId, $userId)) {
            throw new \InvalidArgumentException('No tienes permisos sobre este media');
        }

        $this->mediaRepository->attachToInvitation($invitationId, $mediaId);
    }

    /**
     * Desvincula media de una invitation
     */
    public function detachFromInvitation(int $invitationId, int $mediaId, int $userId): void
    {
        if (!$this->validateUserOwnership($mediaId, $userId)) {
            throw new \InvalidArgumentException('No tienes permisos sobre este media');
        }

        $this->mediaRepository->detachFromInvitation($invitationId, $mediaId);
    }
}