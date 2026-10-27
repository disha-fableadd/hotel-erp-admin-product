<?php

namespace App\Providers;

use App\Models\UserPermission;
use App\Services\Tenancy\TenantManager;
use App\Support\AppViewData;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantManager::class);

        // After config:cache, env() is empty in providers — use config (filled from .env at cache time).
        $imagePath = config('app.image_path');
        if (! is_string($imagePath) || $imagePath === '') {
            $raw = config('app.url', 'http://localhost');
            $imagePath = rtrim((string) $raw, '/').'/';
            config(['app.image_path' => $imagePath]);
        }

        // Legacy Blade env('ImagePath') — must match config when .env is not read at runtime.
        $_ENV['ImagePath'] = $imagePath;
        $_ENV['IMAGE_PATH'] = $imagePath;
        putenv('ImagePath='.$imagePath);
        putenv('IMAGE_PATH='.$imagePath);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share('imagePath', config('app.image_path'));

        // Passport keys are shared by all tenants; pin the path before the
        // tenant switch moves storage_path() to storage/tenants/{id}.
        if (class_exists(Passport::class)) {
            Passport::loadKeysFrom(storage_path());
        }

        $tenancy = $this->app->make(TenantManager::class);

        if ($tenancy->enabled() && $this->app->runningInConsole() && ($tenantId = (int) getenv('TENANT_ID')) > 0) {
            try {
                $tenancy->activateById($tenantId);
            } catch (\Throwable $e) {
                if (!empty(getenv('DB_DATABASE'))) {
                    \Illuminate\Support\Facades\Log::warning("Tenant #{$tenantId} activation failed during console boot, using direct environment credentials: " . $e->getMessage());
                } else {
                    throw $e;
                }
            }
        }

        // Over HTTP with tenancy on, IdentifyTenant shares this after the client DB is selected.
        if (! $tenancy->enabled() || $this->app->runningInConsole()) {
            AppViewData::share();
        }

        if (!File::exists(public_path('storage'))) {
            try {
                Artisan::call('storage:link');
            } catch (\Exception $e) {
                // Optional: Log error or ignore silently
            }
        }

        app()->singleton('hasPermission', function () {
            return function ($moduleId, $action) {
                // Map module IDs to names for tenant module access validation
                static $idToNameMap = [
                    0  => 'Dashboard',
                    1  => 'Products',
                    2  => 'Orders',
                    3  => 'Purchases',
                    4  => 'Invoices',
                    5  => 'Expenses',
                    8  => 'Categories & Brands',
                    9  => 'Tables',
                    10 => 'Vendors',
                    11 => 'Staff',
                    14 => 'General Settings',
                    15 => 'Tax Rates',
                    16 => 'Accounting',
                    17 => 'Inventory',
                    23 => 'Advance Pay',
                    26 => 'Attendance',
                    27 => 'Cash & Bank',
                    28 => 'Leaves',
                    29 => 'Payroll',
                    30 => 'Credit/Debit Notes',
                ];

                if (isset($idToNameMap[$moduleId]) && ! tenant_has_module($idToNameMap[$moduleId])) {
                    return false;
                }

                $user = Auth::user(); // Use default auth guard for web

                if (!$user) return false;

                // Grant all permissions to admin and sub-admin users for tenant-allowed modules
                if (in_array(strtolower((string)$user->role), ['admin', 'sub-admin'], true) || (int)$user->role_id === 1) {
                    return true;
                }

                // If staff or hr member is "Without Permission" (haspermission == 0), they get no access
                if (in_array(strtolower($user->role), ['staff', 'hr']) && !$user->haspermission) {
                    return false;
                }

                // If sub-admin or staff/hr has no explicit permissions defined in DB, grant access by default (if staff/hr is "With Permission")
                $hasExplicit = UserPermission::where('user_id', $user->id)->exists();
                if (!$hasExplicit && (strtolower($user->role) === 'sub-admin' || (in_array(strtolower($user->role), ['staff', 'hr']) && $user->haspermission))) {
                    return true;
                }

                // Check permission from DB
                return UserPermission::where('user_id', $user->id)
                    ->where('module_id', $moduleId)
                    ->where("{$action}", true)
                    ->exists();
            };
        });
    }
}
