<?php

namespace Database\Seeders;

use App\Models\SystemControl;
use Illuminate\Database\Seeder;

class SystemControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemControl::create([
            'max_images_per_landing' => 50,
            'max_file_size_mb' => 5,
            'allowed_mime' => [
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
            'thumbnails_enabled' => false,
            'gif_enabled' => false, // Se habilitará cuando se implemente la funcionalidad
        ]);
    }
}
