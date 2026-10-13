<?php

namespace App\Actions;

use App\Enums\Trigger;
use App\Models\SobrietyPeriod;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Neustart nach einem Rückfall: Die bisherige Serie wird archiviert (inkl. optionaler
 * Reflexion und Trigger), danach beginnt die Zählung ab dem angegebenen Zeitpunkt neu.
 */
class RecordRelapse
{
    public function __construct(private readonly SyncUnlockedBadges $syncUnlockedBadges) {}

    /**
     * @param  list<Trigger>  $triggers
     */
    public function handle(User $user, Carbon $relapsedAt, ?string $reflection = null, array $triggers = []): SobrietyPeriod
    {
        return DB::transaction(function () use ($user, $relapsedAt, $reflection, $triggers) {
            $period = $user->sobrietyPeriods()->create([
                'started_at' => $user->sobriety_start_at,
                'ended_at' => $relapsedAt,
                'reflection_encrypted' => filled($reflection) ? trim($reflection) : null,
                'triggers' => array_values(array_unique(array_map(fn (Trigger $trigger) => $trigger->value, $triggers))),
            ]);

            $user->update(['sobriety_start_at' => $relapsedAt]);

            $this->syncUnlockedBadges->handle($user);

            return $period;
        });
    }
}
