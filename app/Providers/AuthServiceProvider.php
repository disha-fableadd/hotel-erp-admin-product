<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

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

        // Keep API tokens valid for long admin/POS sessions (localStorage Bearer).
        // Web session fallback in AuthApiMiddleware still covers expired tokens.
        if (class_exists(Passport::class)) {
            Passport::tokensExpireIn(now()->addYear());
            Passport::refreshTokensExpireIn(now()->addYear());
            Passport::personalAccessTokensExpireIn(now()->addYear());
        }
    }
}
