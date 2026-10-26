<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Auth;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
        \Illuminate\Support\Facades\View::share('imagePath', config('app.image_path'));

        if (!File::exists(public_path('storage'))) {
            try {
                Artisan::call('storage:link');
            } catch (\Exception $e) {
                // Optional: Log error or ignore silently
            }
        }
        app()->singleton('hasPermission', function () {
            return function ($moduleId, $action) {
                $user = Auth::user(); // Use default auth guard for web

                if (!$user) return false;

                // Grant all permissions to admin users
                if (strtolower($user->role) === 'admin' || $user->role_id == 1) {
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
