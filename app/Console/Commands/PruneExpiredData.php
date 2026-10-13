<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Löscht abgelaufene technische Daten: Sitzungen, Cache-Einträge (z. B. Login-Versuche)
 * und Passwort-Reset-Links. Läuft stündlich über den Scheduler (routes/console.php).
 */
class PruneExpiredData extends Command
{
    /**
     * @var string
     */
    protected $signature = 'cleanstart:prune';

    /**
     * @var string
     */
    protected $description = 'Löscht abgelaufene Sitzungen, Cache-Einträge und Passwort-Reset-Links';

    public function handle(): int
    {
        $now = now()->getTimestamp();

        $sessions = $this->pruneTable(
            (string) config('session.table', 'sessions'),
            'last_activity',
            $now - (int) config('session.lifetime') * 60,
        );
        $cache = $this->pruneTable((string) config('cache.stores.database.table', 'cache'), 'expiration', $now);
        $locks = $this->pruneTable((string) config('cache.stores.database.lock_table', 'cache_locks'), 'expiration', $now);

        $this->call('auth:clear-resets');

        $this->info("Gelöscht: {$sessions} Sitzungen, {$cache} Cache-Einträge, {$locks} Sperren.");

        return self::SUCCESS;
    }

    /**
     * Löscht alle Zeilen, deren Zeitstempel-Spalte nicht nach $before liegt.
     */
    private function pruneTable(string $table, string $column, int $before): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->where($column, '<=', $before)->delete();
    }
}
