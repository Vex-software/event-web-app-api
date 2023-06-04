<?php

namespace App\Providers;

use App\Auth\PasswordBroker;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->bind('auth.password.broker', function ($app) {
            return new PasswordBroker($app['auth.password.tokens']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
