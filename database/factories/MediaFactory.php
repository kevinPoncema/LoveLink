<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->word() . '.jpg';
        $path = 'media/users/' . fake()->numberBetween(1, 10) . '/' . fake()->uuid() . '.jpg';

        return [
            'user_id' => User::factory(),
            'filename' => $filename,
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(100000, 5000000), // 100KB a 5MB
            'url' => '/storage/' . $path,
        ];
    }

    /**
     * Indicate that the media is a PNG image.
     */
    public function png(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/png',
            'filename' => fake()->word() . '.png',
        ]);
    }

    /**
     * Indicate that the media is a WebP image.
     */
    public function webp(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/webp',
            'filename' => fake()->word() . '.webp',
        ]);
    }

    /**
     * Indicate that the media is a GIF.
     */
    public function gif(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/gif',
            'filename' => fake()->word() . '.gif',
        ]);
    }

    /**
     * Indicate that the media is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'path' => 'media/users/' . $user->id . '/' . fake()->uuid() . '.jpg',
        ]);
    }
}
