<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Löscht ein Konto endgültig (Recht auf Löschung, Art. 17 DSGVO).
 *
 * Check-ins, Serien, Notfallplan, Craving-Einträge und Abzeichen werden per Fremdschlüssel (cascade) entfernt;
 * offene Passwort-Reset-Tokens und alle Sitzungen des Nutzers werden zusätzlich gelöscht.
 */
class DeleteAccount
{
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();

            $user->delete();
        });
    }
}
