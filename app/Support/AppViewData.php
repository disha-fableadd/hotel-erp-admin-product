<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

/**
 * Company settings and tenant information shared with every view. With tenancy
 * enabled, this must run after the tenant database is selected.
 */
class AppViewData
{
    public static function share(): void
    {
        $defaults = [
            'name'              => 'Fablead Hotel ERP',
            'email'             => 'info@fableadtech.com',
            'phone'             => '1234567890',
            'address'           => 'Surat, Gujarat',
            'logo'              => 'admin/assets/img/Fablead_ERP_2048x1024.png',
            'currency_symbol'   => '₹',
            'currency_position' => 'left',
        ];

        // Guard against fresh-database installs where `settings` table doesn't exist yet.
        try {
            $appSetting = Setting::first() ?? new Setting($defaults);
        } catch (\Throwable $e) {
            $appSetting = new Setting($defaults);
        }

        View::share('appSetting', $appSetting);
        View::share('settings', $appSetting);
        View::share('currentTenant', tenant());
        View::share('currentClientId', tenant('client_id'));
        // Flutter / Desktop app license reference (admin-client setups.unique_id)
        $licenseKey = tenant('unique_id')
            ?? tenant('license_key')
            ?? data_get(session('tenant'), 'unique_id')
            ?? data_get(session('tenant'), 'license_key');

        // If session is stale (no unique_id yet), refresh once from admin-client
        if (
            empty($licenseKey)
            && config('tenant.enabled')
            && tenant('id')
            && ! session('tenant_unique_id_synced')
        ) {
            try {
                $fresh = app(\App\Services\Tenancy\TenantManager::class)
                    ->resolveById((int) tenant('id'), true);
                $licenseKey = $fresh['unique_id'] ?? $fresh['license_key'] ?? null;
                if (function_exists('session') && session()->isStarted()) {
                    session(['tenant' => $fresh, 'tenant_unique_id_synced' => true]);
                }
                View::share('currentTenant', $fresh);
            } catch (\Throwable) {
                if (function_exists('session') && session()->isStarted()) {
                    session(['tenant_unique_id_synced' => true]);
                }
            }
        }

        View::share('currentLicenseKey', $licenseKey ? strtoupper(trim((string) $licenseKey)) : null);

        $planExpiryNotice = [
            'show' => false,
            'is_expired' => false,
            'plan_name' => tenant('plan_name') ?? 'Standard Plan',
            'end_date' => tenant('expiry_date') ?? null,
            'days_left' => null,
            'days_left_text' => null,
            'message' => null,
            'today' => Carbon::now('Asia/Kolkata')->toDateString(),
        ];

        if ($expiryDate = tenant('expiry_date')) {
            try {
                $now = Carbon::now('Asia/Kolkata')->startOfDay();
                $end = Carbon::parse($expiryDate, 'Asia/Kolkata')->startOfDay();
                $diffDays = (int) $now->diffInDays($end, false);

                if ($diffDays < 0) {
                    $planExpiryNotice['show'] = true;
                    $planExpiryNotice['is_expired'] = true;
                    $planExpiryNotice['days_left'] = 0;
                    $planExpiryNotice['days_left_text'] = 'Expired';
                    $planExpiryNotice['message'] = "Your subscription has expired on {$end->format('d-m-Y')}. Please renew to avoid interruption.";
                } elseif ($diffDays <= 30) {
                    $planExpiryNotice['show'] = true;
                    $planExpiryNotice['is_expired'] = false;
                    $planExpiryNotice['days_left'] = $diffDays;
                    $planExpiryNotice['days_left_text'] = $diffDays === 0 ? 'Expires today' : ($diffDays === 1 ? 'Expires tomorrow' : "{$diffDays} days left");
                    $planExpiryNotice['message'] = "⚠️ Your subscription plan will expire in {$planExpiryNotice['days_left_text']} ({$end->format('d-m-Y')}).";
                }
            } catch (\Throwable) {
                // Ignore parsing errors
            }
        }

        View::share('planExpiryNotice', $planExpiryNotice);
    }
}
