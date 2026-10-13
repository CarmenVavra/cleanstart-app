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
        Schema::table('users', function (Blueprint $table) {
            // Ersparnis-Rechner: frühere Ausgaben in Cent (verschlüsselt, Model-Cast) und Bezugszeitraum
            $table->text('spending_cents')->nullable()->after('country');
            $table->string('spending_period', 10)->nullable()->after('spending_cents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['spending_cents', 'spending_period']);
        });
    }
};
