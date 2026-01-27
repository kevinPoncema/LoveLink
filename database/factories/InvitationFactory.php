<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'user_id' => User::factory(),
            'slug' => Str::slug($title.'-'.fake()->randomNumber(3)),
            'title' => $title,
            'yes_message' => 'Sí',
            'no_messages' => ['No', 'Tal vez', 'No te arrepentirás', 'Piénsalo mejor'],
            'is_published' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the invitation should be published.
     */
    public function published(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => true,
            ];
        });
    }

    /**
     * Indicate that the invitation should be unpublished.
     */
    public function unpublished(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => false,
            ];
        });
    }

    /**
     * Create a valentine's themed invitation.
     */
    public function valentine(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => '¿Quieres ser mi San Valentín?',
                'slug' => 'quieres-ser-mi-san-valentin-'.fake()->randomNumber(3),
                'yes_message' => '¡Sí, claro que sí!',
                'no_messages' => [
                    'No, gracias',
                    'Tal vez otro día',
                    'Déjame pensarlo',
                    'Mejor sigamos siendo amigos',
                ],
            ];
        });
    }

    /**
     * Create a unique slug for testing.
     */
    public function withUniqueSlug(string $slug): Factory
    {
        return $this->state(function (array $attributes) use ($slug) {
            return [
                'slug' => $slug,
            ];
        });
    }
}
