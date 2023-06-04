<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Schema::defaultStringLength(191);

        if (class_exists(MustVerifyEmail::class)) {
            $this->app['auth']->viaRequest('web', function ($request) {
                return $request->user();
            });
        }

        // Passport::routes();
        // Passport::tokensExpireIn(now()->addDays(15));
        // Passport::refreshTokensExpireIn(now()->addDays(30));
        // Passport::personalAccessTokensExpireIn(now()->addMonths(6));

    }
}
