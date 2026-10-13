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
            // Nachweis der ausdrücklichen Einwilligung (Art. 9 Abs. 2 lit. a DSGVO – Gesundheitsdaten)
            $table->timestamp('privacy_accepted_at')->nullable()->after('trusted_contact_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('privacy_accepted_at');
        });
    }
};
