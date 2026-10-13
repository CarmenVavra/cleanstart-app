<?php

namespace App\Session;

use Illuminate\Session\DatabaseSessionHandler;

/**
 * Datenbank-Sitzungen ohne IP-Adresse und User-Agent (Datenminimierung, Art. 5 Abs. 1 lit. c DSGVO).
 *
 * Laravel speichert diese Angaben standardmäßig zu jeder Sitzung; CleanStart braucht sie nicht.
 */
class PrivacyDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * @param  array<string, mixed>  $payload
     * @return $this
     */
    protected function addRequestInformation(&$payload)
    {
        // Explizit leeren, damit auch früher gespeicherte Werte beim nächsten Schreiben verschwinden.
        $payload = array_merge($payload, ['ip_address' => null, 'user_agent' => null]);

        return $this;
    }
}
