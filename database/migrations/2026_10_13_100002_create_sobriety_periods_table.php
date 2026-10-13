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
        // Abgeschlossene Clean-Serien – bleiben nach einem Neustart (Rückfall) erhalten.
        Schema::create('sobriety_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->text('reflection_encrypted')->nullable(); // Verschlüsselte Reflexion „Was ist passiert?“
            $table->json('triggers'); // Trigger-Slugs als JSON-Array
            $table->timestamps();

            $table->index(['user_id', 'ended_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sobriety_periods');
    }
};
