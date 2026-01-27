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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('landing_id')->nullable()->constrained()->onDelete('set null');
            $table->string('slug', 50)->unique();
            $table->string('title', 200)->default('¿Quieres ser mi San Valentín?');
            $table->string('yes_message', 100)->default('Sí');
            $table->json('no_messages')->default('["No", "Tal vez", "No te arrepentirás", "Piénsalo mejor"]');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes(); // Para soft delete

            $table->index('slug');
            $table->index('user_id');
            $table->index('landing_id');
            $table->index('is_published');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
