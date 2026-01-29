<?php

namespace App\Repositories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Collection;

class ThemeRepository
{
    /**
     * Find themes belonging to the system (user_id = null)
     */
    public function findSystemThemes(): Collection
    {
        return Theme::system()->with('backgroundImage')->get();
    }

    /**
     * Find themes belonging to a specific user
     */
    public function findUserThemes(int $userId): Collection
    {
        return Theme::forUser($userId)->with('backgroundImage')->get();
    }

    /**
     * Get system themes plus user themes combined
     */
    public function getSystemAndUserThemes(int $userId): Collection
    {
        return Theme::where(function ($query) use ($userId) {
            $query->whereNull('user_id')
                ->orWhere('user_id', $userId);
        })->with('backgroundImage')->orderBy('name')->get();
    }

    /**
     * Create a new theme
     */
    public function create(array $data): Theme
    {
        return Theme::create($data);
    }

    /**
     * Update an existing theme
     */
    public function update(int $id, array $data): Theme
    {
        $theme = $this->findById($id);
        $theme->update($data);

        return $theme->fresh();
    }

    /**
     * Find a theme by ID
     */
    public function findById(int $id): ?Theme
    {
        return Theme::with('backgroundImage')->find($id);
    }

    /**
     * Delete a theme
     */
    public function delete(int $id): bool
    {
        $theme = $this->findById($id);

        if (! $theme) {
            return false;
        }

        return $theme->delete();
    }
}
