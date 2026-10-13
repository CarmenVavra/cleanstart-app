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
        // Mit dem Craving-Timer überstandener Suchtdruck (nur der Zeitpunkt, keine Inhalte)
        Schema::create('craving_episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('overcome_at');
            $table->timestamps();

            $table->index(['user_id', 'overcome_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('craving_episodes');
    }
};
