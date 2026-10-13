<?php

/*
|--------------------------------------------------------------------------
| Angaben für Impressum & Datenschutzerklärung
|--------------------------------------------------------------------------
|
| Vor dem Go-live in der .env ausfüllen. Fehlende Angaben werden auf den
| Seiten als deutlich markierter Platzhalter angezeigt.
|
*/

return [
    'operator' => env('LEGAL_OPERATOR'),          // Name bzw. Firma des Betreibers
    'street' => env('LEGAL_STREET'),              // Straße und Hausnummer
    'city' => env('LEGAL_CITY'),                  // PLZ und Ort
    'country' => env('LEGAL_COUNTRY'),            // Land
    'email' => env('LEGAL_EMAIL'),                // Kontakt-E-Mail
    'phone' => env('LEGAL_PHONE'),                // optional
    'register' => env('LEGAL_REGISTER'),          // optional: Firmenbuch/Handelsregister, UID
    'hoster' => env('LEGAL_HOSTER'),              // Hosting-Anbieter (Auftragsverarbeiter)
    'supervisory_authority' => env('LEGAL_SUPERVISORY_AUTHORITY'), // zuständige Datenschutzbehörde
];
