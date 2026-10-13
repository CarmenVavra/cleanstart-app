# Profi-Tipp für Laravel-Entwickler: "Laravel Boost MCP"

Um Claude Code noch mächtiger im Umgang mit Ihrer Datenbank zu machen, empfiehlt sich die Installation des Open-Source-Pakets `laravel/boost` via Composer im Projekt:

```bash
composer require laravel/boost --dev
```

## Dieses Paket registriert sich als sogenannter Model Context Protocol (MCP) Server. Dadurch kann Claude Code direkt mit den Internals Ihrer Laravel-App interagieren. Claude kann dann

- Live Ihre echte lokale Datenbank-Struktur abfragen (ohne die Dateien lesen zu müssen).
- Artisan Tinker-Befehle im Hintergrund ausführen, um Daten direkt zu prüfen.

## Server start and stop script

- Mach im Projektordner eine start.bat, um den Server zu starten
- Mach im Projektordner eine stop.bat, um den Server zu soppen

---

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

- **Notfall-Button** mit pulsierender Animation → Bottom-Sheet mit Telefonseelsorge, Vertrauensperson (aus dem Profil), geführter 4-7-8-Atemübung (4 s ein · 7 s halten · 8 s aus, mit Countdown) und Notruf 112 (Fokus-Trap, Escape schließt).
- **Clean-Counter** (Tage/Stunden/Minuten) mit sekündlichem Live-Tick und Fortschritt zum nächsten Meilenstein.
- **8 Meilenstein-Badges** (24 h … 1 Jahr) – werden in `badge_user` mit Erreichungszeitpunkt gespeichert; nach einem Neustart (Rückfall) neu berechnet.
- **Daily Check-in**: Stimmung 1–5, Trigger-Tags, optionale Notiz (verschlüsselt) – ein Check-in pro Tag, nachträglich bearbeitbar.
- **Anonyme Registrierung** mit Pseudonym; Login per Benutzername (Rate-Limit: 5 Versuche).
- **Profil**: Pseudonym, Startdatum, Vertrauensperson (Name & Nummer verschlüsselt gespeichert).

## Architektur

| Bereich | Datei(en) |
|---|---|
| Dashboard (Livewire) | `app/Livewire/Dashboard.php`, `app/Livewire/Forms/CheckinForm.php`, `resources/views/livewire/dashboard.blade.php` |
| UI-Bausteine | `resources/views/components/dashboard/*.blade.php` |
| Clientseitige Logik | `resources/js/dashboard.js` (Alpine: Counter, Sheet, Atemübung, Check-in-UI) |
| Geschäftslogik | `app/Services/SobrietyProgress.php`, `app/Actions/SaveDailyCheckin.php`, `app/Actions/SyncUnlockedBadges.php` |
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
