<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
    }
}
