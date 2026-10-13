<?php

namespace App\Providers;

use App\Session\PrivacyDatabaseSessionHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Lazy Loading (N+1), stille Mass-Assignment-Fehler & Zugriffe auf fehlende Attribute in Entwicklung sofort melden.
        Model::shouldBeStrict(! $this->app->isProduction());

        Carbon::setLocale($this->app->getLocale());

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(10)->letters()->numbers()->uncompromised()
            : Password::min(8));

        $this->configurePrivacy();
    }

    /**
     * Datenschutz-Voreinstellungen (siehe resources/views/legal/privacy.blade.php).
     */
    private function configurePrivacy(): void
    {
        // Sitzungen ohne IP-Adresse und User-Agent speichern.
        Session::extend('database', fn ($app) => new PrivacyDatabaseSessionHandler(
            DB::connection(config('session.connection')),
            (string) config('session.table'),
            (int) config('session.lifetime'),
            $app,
        ));

        // SQLite überschreibt gelöschte Inhalte, statt sie als freie Seiten in der Datei liegen zu lassen.
        Event::listen(function (ConnectionEstablished $event) {
            if ($event->connection->getDriverName() === 'sqlite') {
                $event->connection->statement('PRAGMA secure_delete = ON');
            }
        });
    }
}
