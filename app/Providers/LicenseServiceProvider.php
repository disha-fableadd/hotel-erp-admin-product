<?php

namespace App\Providers;

use App\Services\License\LicenseManager;
use App\Services\License\LicenseServerService;
use App\Services\License\LicenseSigner;
use App\Services\License\LicenseStorage;
use App\Services\License\RemoteLicenseClient;
use Illuminate\Support\ServiceProvider;

class LicenseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LicenseStorage::class);
        $this->app->singleton(LicenseSigner::class);
        $this->app->singleton(LicenseServerService::class);
        $this->app->singleton(RemoteLicenseClient::class);
        $this->app->singleton(LicenseManager::class);
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            if ($this->app->runningInConsole()) {
                return;
            }

            try {
                $manager = $this->app->make(LicenseManager::class);
                if ($manager->shouldEnforce() && ! $manager->isLicensed()) {
                    view()->share('licenseInvalid', true);
                }
            } catch (\Throwable) {
                // Do not break the whole app if cache/DB is temporarily unavailable.
            }
        });
    }
}
