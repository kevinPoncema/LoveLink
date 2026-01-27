<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemThemes = [
            [
                'user_id' => null, // Sistema
                'name' => 'Elegante Clásico',
                'description' => 'Un tema elegante y clásico con tonos dorados y blancos',
                'primary_color' => '#D4AF37',
                'secondary_color' => '#F5F5DC',
                'bg_color' => '#FFFFFF',
                'css_class' => 'theme-elegant-classic',
            ],
            [
                'user_id' => null, // Sistema
                'name' => 'Romance Rosa',
                'description' => 'Tema romántico con tonos rosados y suaves',
                'primary_color' => '#FF69B4',
                'secondary_color' => '#FFB6C1',
                'bg_color' => '#FFF0F5',
                'css_class' => 'theme-romance-pink',
            ],
            [
                'user_id' => null, // Sistema
                'name' => 'Natureza Verde',
                'description' => 'Tema inspirado en la naturaleza con verdes frescos',
                'primary_color' => '#228B22',
                'secondary_color' => '#90EE90',
                'bg_color' => '#F0FFF0',
                'css_class' => 'theme-nature-green',
            ],
            [
                'user_id' => null, // Sistema
                'name' => 'Océano Azul',
                'description' => 'Tema marino con tonos azules y celestes',
                'primary_color' => '#1E90FF',
                'secondary_color' => '#87CEEB',
                'bg_color' => '#F0F8FF',
                'css_class' => 'theme-ocean-blue',
            ],
            [
                'user_id' => null, // Sistema
                'name' => 'Atardecer Cálido',
                'description' => 'Tema cálido con tonos naranjas y amarillos',
                'primary_color' => '#FF8C00',
                'secondary_color' => '#FFD700',
                'bg_color' => '#FFFACD',
                'css_class' => 'theme-sunset-warm',
            ]
        ];

        foreach ($systemThemes as $theme) {
            \App\Models\Theme::create($theme);
        }
    }
}
