<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Theme::create([
            'name' => 'Elegante Dorado',
            'slug' => 'elegante-dorado',
            'description' => 'Tema elegante con tonos dorados y blancos',
            'primary_color' => '#FFD700',
            'secondary_color' => '#FFFFFF',
            'bg_color' => '#F5F5F5',
            'css_class' => 'theme-elegant-gold',
            'is_active' => true,
        ]);

        Theme::create([
            'name' => 'Rosa Romántico',
            'slug' => 'rosa-romantico',
            'description' => 'Tema romántico con tonos rosados suaves',
            'primary_color' => '#FF69B4',
            'secondary_color' => '#FFC0CB',
            'bg_color' => '#FFF0F5',
            'css_class' => 'theme-romantic-pink',
            'is_active' => true,
        ]);

        Theme::create([
            'name' => 'Azul Sereno',
            'slug' => 'azul-sereno',
            'description' => 'Tema sereno con tonos azules y blancos',
            'primary_color' => '#4169E1',
            'secondary_color' => '#87CEEB',
            'bg_color' => '#F0F8FF',
            'css_class' => 'theme-serene-blue',
            'is_active' => true,
        ]);
    }
}
