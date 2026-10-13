<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Abgelaufene Sitzungen, Login-Versuche und Passwort-Links zeitnah löschen (siehe Datenschutzerklärung).
// Auf dem Server braucht es dafür einen Cronjob: * * * * * php artisan schedule:run
Schedule::command('cleanstart:prune')->hourly();
