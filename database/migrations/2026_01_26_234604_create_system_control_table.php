<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_control', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('max_images_per_landing')->default(50);
            $table->unsignedTinyInteger('max_file_size_mb')->default(5);
            $table->json('allowed_mime')->default('["image/jpeg", "image/png", "image/webp"]');
            $table->boolean('thumbnails_enabled')->default(false);
            $table->boolean('gif_enabled')->default(false); // Para futuras invitaciones
            $table->timestamp('updated_at');

            // Solo debe existir un registro de configuración
            // Se insertará via seeder
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_control');
    }
};
