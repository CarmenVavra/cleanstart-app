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
        Schema::create('daily_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('mood_rating'); // Skala 1–5
            $table->json('triggers'); // Trigger-Slugs als JSON-Array, z. B. ["stress", "boredom"]
            $table->text('notes_encrypted')->nullable(); // Verschlüsselter Freitext
            $table->date('checkin_date');
            $table->timestamps();

            $table->unique(['user_id', 'checkin_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_checkins');
    }
};
