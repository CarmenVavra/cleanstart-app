<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(BadgeSeeder::class);

        // Demo-Zugang für die lokale Entwicklung (Zugangsdaten siehe README.md).
        if (! app()->isProduction()) {
            User::updateOrCreate(
                ['username' => 'Alex'],
                [
                    'email' => 'alex@cleanstart.test',
                    'password' => 'cleanstart-demo',
                    'sobriety_start_at' => Carbon::parse('2026-08-29 08:00'),
                    'trusted_contact_name' => 'Sabine',
                    'trusted_contact_phone' => '+49 170 1234567',
                ],
            );
        }
    }
}
