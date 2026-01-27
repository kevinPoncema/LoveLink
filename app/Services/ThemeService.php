<?php

namespace App\Services;

use App\Models\Theme;
use App\Models\User;
use App\Repositories\ThemeRepository;
use Illuminate\Database\Eloquent\Collection;

class ThemeService
{
    public function __construct(
        protected ThemeRepository $themeRepository
    ) {}

    /**
     * Get available themes for a user (system themes + user's own themes)
     * 
     * @param User $user
     * @return Collection
     */
    public function getAvailableThemes(User $user): Collection
    {
        return $this->themeRepository->getSystemAndUserThemes($user->id);
    }

    /**
     * Create a new theme for a user
     * 
     * @param User $user
     * @param array $data
     * @return Theme
     */
    public function createUserTheme(User $user, array $data): Theme
    {
        $data['user_id'] = $user->id;
        
        return $this->themeRepository->create($data);
    }

    /**
     * Update a theme if the user has permission to modify it
     * 
     * @param int $themeId
     * @param array $data
     * @param User $user
     * @return Theme
     * @throws \Exception
     */
    public function updateTheme(int $themeId, array $data, User $user): Theme
    {
        $theme = $this->themeRepository->findById($themeId);

        if (!$theme) {
            throw new \Exception('Theme not found', 404);
        }

        if (!$this->canUserModify($user, $theme)) {
            throw new \Exception('Unauthorized to modify this theme', 403);
        }

        // Remove user_id from update data to prevent modification
        unset($data['user_id']);

        return $this->themeRepository->update($themeId, $data);
    }

    /**
     * Delete a theme if the user has permission
     * 
     * @param int $themeId
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function deleteTheme(int $themeId, User $user): bool
    {
        $theme = $this->themeRepository->findById($themeId);

        if (!$theme) {
            throw new \Exception('Theme not found', 404);
        }

        if (!$this->canUserModify($user, $theme)) {
            throw new \Exception('Unauthorized to delete this theme', 403);
        }

        // Cannot delete system themes
        if ($theme->isSystemTheme()) {
            throw new \Exception('Cannot delete system themes', 403);
        }

        return $this->themeRepository->delete($themeId);
    }

    /**
     * Check if a user can modify a given theme
     * 
     * @param User $user
     * @param Theme $theme
     * @return bool
     */
    public function canUserModify(User $user, Theme $theme): bool
    {
        // Users can only modify their own themes, not system themes
        return !$theme->isSystemTheme() && $theme->user_id === $user->id;
    }

    /**
     * Find a theme that the user can access (system or own)
     * 
     * @param int $themeId
     * @param User $user
     * @return Theme|null
     */
    public function findAccessibleTheme(int $themeId, User $user): ?Theme
    {
        $theme = $this->themeRepository->findById($themeId);

        if (!$theme) {
            return null;
        }

        // User can access system themes or their own themes
        if ($theme->isSystemTheme() || $theme->user_id === $user->id) {
            return $theme;
        }

        return null;
    }
}