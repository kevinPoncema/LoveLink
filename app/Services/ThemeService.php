<?php

namespace App\Services;

use App\Models\Theme;
use App\Models\User;
use App\Repositories\ThemeRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class ThemeService
{
    public function __construct(
        protected ThemeRepository $themeRepository,
        protected MediaService $mediaService
    ) {}

    /**
     * Get available themes for a user (system themes + user's own themes)
     */
    public function getAvailableThemes(User $user): Collection
    {
        return $this->themeRepository->getSystemAndUserThemes($user->id);
    }

    /**
     * Create a new theme for a user
     */
    public function createUserTheme(User $user, array $data, ?UploadedFile $backgroundImage = null): Theme
    {
        $data['user_id'] = $user->id;

        // Si hay imagen de fondo como archivo, subirla primero
        if ($backgroundImage) {
            $media = $this->mediaService->uploadThemeBackgroundImage($backgroundImage, $user->id);
            $data['bg_image_media_id'] = $media->id;
            $data['bg_image_url'] = $media->url;
        }
        // Si viene bg_image_media_id, obtener la URL del media
        elseif (isset($data['bg_image_media_id']) && $data['bg_image_media_id']) {
            $media = $this->mediaService->getMediaById($data['bg_image_media_id']);
            if ($media) {
                $data['bg_image_url'] = $media->url;
            }
        }

        return $this->themeRepository->create($data);
    }

    /**
     * Update a theme if the user has permission to modify it
     *
     * @throws \Exception
     */
    public function updateTheme(int $themeId, array $data, User $user, ?UploadedFile $backgroundImage = null): Theme
    {
        $theme = $this->themeRepository->findById($themeId);

        if (! $theme) {
            throw new \Exception('Theme not found', 404);
        }

        if (! $this->canUserModify($user, $theme)) {
            throw new \Exception('Unauthorized to modify this theme', 403);
        }

        // Si hay imagen de fondo como archivo, subirla
        if ($backgroundImage) {
            if ($theme->bg_image_media_id) {
                $this->deleteOldBackgroundImage($theme->bg_image_media_id);
            }

            // Subir nueva imagen
            $media = $this->mediaService->uploadThemeBackgroundImage($backgroundImage, $user->id);
            $data['bg_image_media_id'] = $media->id;
            $data['bg_image_url'] = $media->url;
        }
        // Si viene bg_image_media_id, obtener la URL del media
        elseif (isset($data['bg_image_media_id'])) {
            if ($data['bg_image_media_id']) {
                // Eliminar imagen anterior si existe
                if ($theme->bg_image_media_id && $theme->bg_image_media_id !== $data['bg_image_media_id']) {
                    $this->deleteOldBackgroundImage($theme->bg_image_media_id);
                }

                $media = $this->mediaService->getMediaById($data['bg_image_media_id']);
                if ($media) {
                    $data['bg_image_url'] = $media->url;
                }
            } else {
                // Si bg_image_media_id es null, eliminar imagen anterior
                if ($theme->bg_image_media_id) {
                    $this->deleteOldBackgroundImage($theme->bg_image_media_id);
                }
                $data['bg_image_url'] = null;
            }
        }

        unset($data['user_id']);

        return $this->themeRepository->update($themeId, $data);
    }

    /**
     * Delete a theme if the user has permission
     *
     * @throws \Exception
     */
    public function deleteTheme(int $themeId, User $user): bool
    {
        $theme = $this->themeRepository->findById($themeId);

        if (! $theme) {
            throw new \Exception('Theme not found', 404);
        }

        if (! $this->canUserModify($user, $theme)) {
            throw new \Exception('Unauthorized to delete this theme', 403);
        }

        // Cannot delete system themes
        if ($theme->isSystemTheme()) {
            throw new \Exception('Cannot delete system themes', 403);
        }

        if ($theme->bg_image_media_id) {
            $this->deleteOldBackgroundImage($theme->bg_image_media_id);
        }

        return $this->themeRepository->delete($themeId);
    }

    /**
     * Check if a user can modify a given theme
     */
    public function canUserModify(User $user, Theme $theme): bool
    {
        // Users can only modify their own themes, not system themes
        return ! $theme->isSystemTheme() && $theme->user_id === $user->id;
    }

    /**
     * Find a theme that the user can access (system or own)
     */
    public function findAccessibleTheme(int $themeId, User $user): ?Theme
    {
        $theme = $this->themeRepository->findById($themeId);

        if (! $theme) {
            return null;
        }

        // User can access system themes or their own themes
        if ($theme->isSystemTheme() || $theme->user_id === $user->id) {
            return $theme;
        }

        return null;
    }

    /**
     * Elimina la imagen de fondo anterior del tema
     */
    public function deleteOldBackgroundImage(int $mediaId): void
    {
        $this->mediaService->forceDeleteMedia($mediaId);
    }

    /**
     * Sube una imagen de fondo para el tema
     */
    public function uploadBackgroundImage(UploadedFile $file, int $userId): \App\Models\Media
    {
        return $this->mediaService->uploadThemeBackgroundImage($file, $userId);
    }
}
