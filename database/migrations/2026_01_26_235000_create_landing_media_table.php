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
        Schema::create('landing_media', function (Blueprint $table) {
            $table->foreignId('landing_id')->constrained()->onDelete('cascade');
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('sort_order')->default(1);

            $table->primary(['landing_id', 'media_id']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_media');
    }
};