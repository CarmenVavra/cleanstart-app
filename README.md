# CleanStart – Projektanleitung

Mobile-first Web-App zur Rückfallprävention (Laravel 12 · Livewire 3 · Alpine.js · Tailwind CSS 4 · SQLite).
Umgesetzt nach `Konzept.pdf` und dem Design-Handoff in `UI_MOCKUPS/design_handoff_cleanstart_dashboard/`.

## Starten & Stoppen

| Aktion | Befehl |
|---|---|
| Starten | `start.bat` (Doppelklick) → installiert fehlende Abhängigkeiten, migriert, baut Assets und öffnet http://127.0.0.1:8900 |
| Stoppen | `stop.bat` |
| Manuell | `php artisan serve --host=127.0.0.1 --port=8900` |

## Demo-Zugang (nur lokal, per `php artisan db:seed`)

- Benutzername: `Alex`
- Passwort: `cleanstart-demo`

## Funktionen

- **Notfall-Button** mit pulsierender Animation → Bottom-Sheet mit Telefonseelsorge des gewählten Landes (AT: 142 · DE: 0800 111 0 111), Vertrauensperson (aus dem Profil), geführter 4-7-8-Atemübung (4 s ein · 7 s halten · 8 s aus, mit Countdown) und Notruf 112 (Fokus-Trap, Escape schließt).
- **Craving-Timer** im Notfall-Sheet: 15 Minuten „Welle aussitzen“ mit Fortschrittsring und begleitenden Texten. Läuft weiter, wenn das Sheet geschlossen oder die Seite neu geladen wird (Hinweis auf dem Dashboard). Am Ende: „Überstanden“ wird gespeichert und auf dem Clean-Counter gezählt, „Diesmal nicht“ führt behutsam zu „Neu starten“.
- **Ersparnis-Rechner**: Im Profil frühere Ausgaben (pro Tag/Woche/Monat, verschlüsselt) eintragen; das Dashboard zeigt live, wie viel seit dem Start gespart wurde (Format je Land: AT „€ 70,00“, DE „70,00 €“).
- **Clean-Counter** (Tage/Stunden/Minuten) mit sekündlichem Live-Tick und Fortschritt zum nächsten Meilenstein.
- **8 Meilenstein-Badges** (24 h … 1 Jahr) – werden in `badge_user` mit Erreichungszeitpunkt gespeichert; nach einem Neustart (Rückfall) neu berechnet.
- **Daily Check-in**: Stimmung 1–5, Trigger-Tags, optionale Notiz (verschlüsselt) – ein Check-in pro Tag, nachträglich bearbeitbar.
- **Stimmungsverlauf** (`/verlauf`, 7/30/90 Tage): Balkendiagramm (serverseitiges SVG, mit Tabelle als Textalternative), Ø-Stimmung, häufigste Trigger mit Ø-Stimmung, Stimmung nach Wochentag, Neustarts als Markierung und behutsame Hinweise auf Muster (ab 5 Check-ins). Notizen werden dort nicht geladen. Lokal füllt `DemoCheckinSeeder` vier Wochen Beispieldaten für „Alex“.
- **Installierbare App (PWA)**: `public/manifest.json`, Icons in `public/app-icons/`, Service Worker `public/sw.js`. Ohne Netz erscheint `public/offline.html` mit Telefonseelsorge AT/DE, Notruf 112 und 4-7-8-Atemübung. Zwischengespeichert werden nur öffentliche Dateien (Build, Icons), nie Seiten mit persönlichen Daten. Im Profil gibt es eine Karte „Als App installieren“. Am Handy funktioniert die Installation nur über HTTPS.
- **Anonyme Registrierung** mit Pseudonym und ausdrücklicher Einwilligung (Art. 9 DSGVO, Zeitpunkt wird gespeichert); Login per Benutzername (Rate-Limit: 5 Versuche).
- **Passwort vergessen**: Reset-Link per E-Mail; die Antwort verrät nicht, ob eine Adresse registriert ist.
- **Profil**: Pseudonym, Startdatum (nur zur Korrektur), Land (Österreich voreingestellt oder Deutschland – bestimmt die Hilfsnummern), Vertrauensperson (Name & Nummer verschlüsselt gespeichert).
- **Neu starten** (`/neustart`): Nach einem Rückfall wird die bisherige Serie mit optionaler, verschlüsselter Reflexion und Triggern archiviert; „Bisher längste Serie“ erscheint auf dem Dashboard, alle Serien im Profil.
- **Mein Notfallplan** (`/notfallplan`): „Mein Warum“ und „Was mir hilft“ (je max. 10, verschlüsselt) – wird direkt im Notfall-Sheet angezeigt.
- **Datenexport** (Art. 20 DSGVO) als JSON und **Konto löschen** (Art. 17 DSGVO, mit Passwort-Bestätigung) im Profil.
- **Impressum & Datenschutzerklärung** (`/impressum`, `/datenschutz`), öffentlich erreichbar und überall verlinkt.

## Live-Betrieb (netcup / Plesk)

- URL: https://cleanstart.caryssa.at
- Server-Ordner: `~/cleanstart.caryssa.at/app` (Dokumentstamm in Plesk: `cleanstart.caryssa.at/app/public`), Composer unter `~/cleanstart.caryssa.at/bin/composer.phar`
- Der Server holt den Code direkt von GitHub; die Design-Dateien (`public/build`) werden lokal gebaut und hochgeladen.
- **Aktualisieren:** Änderungen committen, dann `deploy.bat` (Doppelklick). Das Skript testet, pusht zu GitHub, aktualisiert den Server im kurzen Wartungsmodus und lädt die Design-Dateien hoch.
- SSH-Zugang per Schlüssel `%USERPROFILE%\.ssh\cleanstart_netcup` (Benutzer `hosting171221`).
- ⚠️ Per SSH **kein** `config:cache`, `optimize` oder `view:cache` ausführen: SSH läuft in einer isolierten Umgebung (`/cleanstart.caryssa.at/app`), der Webserver unter `/var/www/vhosts/…/cleanstart.caryssa.at/app` – zwischengespeicherte Pfade führen zu Fehler 500.
- Der Pfad `/icons/` ist bei Apache reserviert; App-Icons liegen deshalb unter `public/app-icons/`.
- Die Server-`.env` (inkl. `APP_KEY`) liegt nur auf dem Server – unbedingt eine Sicherungskopie aufbewahren.

## Einrichtung auf einem neuen Server

Auf netcup ist das alles erledigt. Die Liste gilt nur, falls die App einmal umzieht:

- `.env` mit `APP_ENV=production`, `APP_DEBUG=false`, eigenem `APP_KEY`, `SESSION_SECURE_COOKIE=true` und `LOG_LEVEL=error`.
- `LEGAL_*`-Werte ausfüllen (Betreiber, Adresse, E-Mail, Hoster, Aufsichtsbehörde, `LEGAL_MAIL_PROVIDER`, `LEGAL_BACKUP_RETENTION_DAYS`). Fehlende Angaben erscheinen auf den Seiten als roter Platzhalter. Die Texte sind eine Vorlage und sollten rechtlich geprüft werden.
- `MAIL_*` auf einen echten SMTP-Server stellen (nie `MAIL_MAILER=log`); lokal landen Reset-Mails in `storage/logs/laravel.log`.
- Cronjob für den Scheduler: `* * * * * php artisan schedule:run`. Er startet stündlich `cleanstart:prune` (löscht abgelaufene Sitzungen, Login-Zähler und Passwort-Links, wie in der Datenschutzerklärung zugesagt).
- HTTPS-Zertifikat und Weiterleitung von HTTP auf HTTPS einrichten.

## Sicherheit

`app/Http/Middleware/SecurityHeaders.php` setzt für jede Antwort Sicherheits-Header (geprüft in `tests/Feature/SecurityHeadersTest.php`):

- `Strict-Transport-Security` (nur über HTTPS): Der Browser verwendet ein Jahr lang ausschließlich HTTPS.
- `X-Frame-Options: DENY` und `frame-ancestors 'none'`: Keine fremde Seite darf CleanStart einbetten.
- `Referrer-Policy: same-origin`: Externe Seiten erfahren nicht, dass jemand von CleanStart kommt.
- `X-Content-Type-Options`, `Permissions-Policy` (keine Kamera, kein Mikrofon, kein Standort) und `Cross-Origin-Opener-Policy`.
- Angemeldete Seiten werden mit `Cache-Control: no-store` nicht im Browser-Cache abgelegt (wichtig bei geteilten Geräten).

Die Content-Security-Policy schränkt Skripte bewusst nicht ein: Alpine.js und Livewire brauchen dynamisch erzeugten Code.

## Datenschutz im Code

- Sitzungen werden ohne IP-Adresse und User-Agent gespeichert (`app/Session/PrivacyDatabaseSessionHandler.php`).
- Login-Versuche werden nur unter einem SHA-256-Hash aus Benutzername und IP gezählt.
- „Angemeldet bleiben“ gilt 30 Tage (`AUTH_REMEMBER_MINUTES`).
- SQLite läuft mit `PRAGMA secure_delete = ON`, gelöschte Inhalte werden in der Datei überschrieben.
- `tests/Feature/PrivacyComplianceTest.php` prüft diese Zusagen der Datenschutzerklärung.

## Architektur

| Bereich | Datei(en) |
|---|---|
| Dashboard (Livewire) | `app/Livewire/Dashboard.php`, `app/Livewire/Forms/CheckinForm.php`, `resources/views/livewire/dashboard.blade.php` |
| UI-Bausteine | `resources/views/components/dashboard/*.blade.php` |
| Clientseitige Logik | `resources/js/dashboard.js` (Alpine: Counter, Sheet, Atemübung, Check-in-UI) |
| Geschäftslogik | `app/Services/SobrietyProgress.php`, `app/Actions/*` (Check-in, Abzeichen, Neustart, Export, Kontolöschung) |
| Rechtliches | `config/legal.php`, `resources/views/legal/*.blade.php` |
| Validierung | `app/Http/Requests/**` (Form Requests), `CheckinForm::rules()` |
| Design-Tokens | `resources/css/app.css` (`@theme`) |

## Qualitätssicherung

```bash
php artisan test
./vendor/bin/phpstan analyse --memory-limit=1G
./vendor/bin/pint --test
npm run build
```

Laravel Boost ist installiert und in `.mcp.json` als MCP-Server (`php artisan boost:mcp`) registriert.
