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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('primary_color', 7)->default('#FF5733'); // HEX colors
            $table->string('secondary_color', 7)->default('#FFC300');
            $table->string('bg_color', 7)->default('#F5F5F5');
            $table->string('bg_image_url', 500)->nullable();
            $table->string('css_class', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
