<?php

namespace Database\Seeders;

use App\Enums\CopingType;
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
        // firstOrCreate: Änderungen am Demo-Konto bleiben bei jedem start.bat-Lauf erhalten.
        if (! app()->isProduction()) {
            $user = User::firstOrCreate(
                ['username' => 'Alex'],
                [
                    'email' => 'alex@cleanstart.test',
                    'password' => 'cleanstart-demo',
                    'sobriety_start_at' => Carbon::parse('2026-08-29 08:00'),
                    'trusted_contact_name' => 'Sabine',
                    'trusted_contact_phone' => '+49 170 1234567',
                    'privacy_accepted_at' => now(),
                ],
            );

            if ($user->wasRecentlyCreated) {
                $this->seedDemoHistory($user);
                $this->call(DemoCheckinSeeder::class);
            }
        }
    }

    /**
     * Beispiel-Notfallplan und eine frühere Serie für das Demo-Konto (Check-ins: DemoCheckinSeeder).
     */
    private function seedDemoHistory(User $user): void
    {
        $user->copingItems()->createMany([
            ['type' => CopingType::Reason, 'content' => 'Meine Tochter soll stolz auf mich sein'],
            ['type' => CopingType::Reason, 'content' => 'Wieder klar denken und arbeiten können'],
            ['type' => CopingType::Strategy, 'content' => '10 Minuten an die frische Luft gehen'],
            ['type' => CopingType::Strategy, 'content' => 'Ein großes Glas kaltes Wasser trinken'],
            ['type' => CopingType::Strategy, 'content' => 'Sabine schreiben, wie es mir geht'],
        ]);

        $user->sobrietyPeriods()->create([
            'started_at' => Carbon::parse('2026-06-02 09:00'),
            'ended_at' => Carbon::parse('2026-07-14 22:00'),
            'reflection_encrypted' => 'Allein zu Hause nach einem stressigen Tag.',
            'triggers' => ['stress', 'loneliness'],
        ]);
    }
}
