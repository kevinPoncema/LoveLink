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
            'name' => 'Noche Estrellada',
            'description' => 'Elegante tema oscuro con acentos dorados',
            'primary_color' => '#FFD700',
            'secondary_color' => '#F5F5F5',
            'bg_color' => '#0C0A09',
            'css_class' => 'theme-starry-night',
            'user_id' => null,
        ]);

        Theme::create([
            'name' => 'Pasión Nocturna',
            'description' => 'Tema oscuro profundo con acentos rojos intensos',
            'primary_color' => '#E11D48',
            'secondary_color' => '#F8FAFC',
            'bg_color' => '#0F172A',
            'css_class' => 'theme-midnight-passion',
            'user_id' => null,
        ]);

        Theme::create([
            'name' => 'Bosque Neón',
            'description' => 'Tema oscuro inspirado en la naturaleza con acentos esmeralda',
            'primary_color' => '#10B981',
            'secondary_color' => '#ECFDF5',
            'bg_color' => '#064E3B',
            'css_class' => 'theme-neon-forest',
            'user_id' => null,
        ]);
    }
}
