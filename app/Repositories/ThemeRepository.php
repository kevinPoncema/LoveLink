<?php

namespace App\Repositories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Collection;

class ThemeRepository
{
    /**
     * Find themes belonging to the system (user_id = null)
     * 
     * @return Collection
     */
    public function findSystemThemes(): Collection
    {
        return Theme::system()->get();
    }

    /**
     * Find themes belonging to a specific user
     * 
     * @param int $userId
     * @return Collection
     */
    public function findUserThemes(int $userId): Collection
    {
        return Theme::forUser($userId)->get();
    }

    /**
     * Get system themes plus user themes combined
     * 
     * @param int $userId
     * @return Collection
     */
    public function getSystemAndUserThemes(int $userId): Collection
    {
        return Theme::where(function ($query) use ($userId) {
            $query->whereNull('user_id')
                  ->orWhere('user_id', $userId);
        })->orderBy('name')->get();
    }

    /**
     * Create a new theme
     * 
     * @param array $data
     * @return Theme
     */
    public function create(array $data): Theme
    {
        return Theme::create($data);
    }

    /**
     * Update an existing theme
     * 
     * @param int $id
     * @param array $data
     * @return Theme
     */
    public function update(int $id, array $data): Theme
    {
        $theme = $this->findById($id);
        $theme->update($data);
        return $theme->fresh();
    }

    /**
     * Find a theme by ID
     * 
     * @param int $id
     * @return Theme|null
     */
    public function findById(int $id): ?Theme
    {
        return Theme::find($id);
    }

    /**
     * Delete a theme
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $theme = $this->findById($id);
        
        if (!$theme) {
            return false;
        }

        return $theme->delete();
    }
}