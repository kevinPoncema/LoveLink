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
        Schema::create('landings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('theme_id')->constrained()->onDelete('restrict');
            $table->string('slug', 50)->unique();
            $table->string('couple_names', 200);
            $table->date('anniversary_date');
            $table->longText('bio_text')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landings');
    }
};
