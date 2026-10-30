<?php

namespace App\Http\Middleware;

use App\Exceptions\TenantResolutionException;
use App\Services\Tenancy\TenantManager;
use App\Support\AppViewData;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Must run before anything that touches the database, session or storage.
 */
class IdentifyTenant
{
    public function __construct(private readonly TenantManager $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenancy->enabled()) {
            return $next($request);
        }

        // Activation must be reachable before login or tenant resolution.
        // Otherwise / redirects to /license and tenancy redirects it back to /.
        if ($request->is('license', 'license/*')) {
            return $next($request);
        }

        // If a tenant session already exists, resolve and activate that tenant
        if ($request->hasSession() && $request->session()->has('tenant_id')) {
            try {
                $tenantId = (int) $request->session()->get('tenant_id');
                $forceFresh = $request->has('refresh_tenant') || $request->has('refresh_permissions');

                $tenant = $this->tenancy->resolveById($tenantId, $forceFresh);
                $request->session()->put('tenant', $tenant);

                AppViewData::share();
                return $next($request);
            } catch (TenantResolutionException $e) {
                // If remote admin_erp is temporarily unreachable, fallback to cached session tenant if valid
                $cachedTenant = $request->session()->get('tenant');
                if (is_array($cachedTenant) && !empty($cachedTenant['db']['database']) && $e->reason === 'UNREACHABLE') {
                    try {
                        $this->tenancy->activate($cachedTenant);
                        AppViewData::share();
                        return $next($request);
                    } catch (\Throwable) {
                        // proceed to deny
                    }
                }

                // If tenant is expired/inactive, logout and deny
                if ($request->hasSession()) {
                    $request->session()->forget(['tenant_id', 'tenant']);
                }
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                return $this->deny($request, $e);
            }
        }

        // If user is already authenticated via session cookie but tenant_id is missing from session,
        // recover tenant using authenticated user's email or cleanly log out to prevent redirect loops.
        if ($request->hasSession() && \Illuminate\Support\Facades\Auth::guard('web')->check()) {
            $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
            if ($user && !empty($user->email)) {
                try {
                    $tenant = $this->tenancy->resolveByEmail($user->email);
                    $request->session()->put('tenant_id', $tenant['id']);
                    $request->session()->put('tenant', $tenant);

                    AppViewData::share();
                    return $next($request);
                } catch (\Throwable) {
                    \Illuminate\Support\Facades\Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            } else {
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                $request->session()->invalidate();
            }
        }

        // If request is on central domain or localhost and not yet authenticated, handle public endpoints
        $centralHost = static::isCentralHost($request->getHost());

        // For public login, logout, face login and attendance endpoints, allow passing through
        if ($request->is('/') || $request->is('login') || $request->is('logout') || $request->is('auth/*') || $request->is('face-login*') || $request->is('api/face-login-data') || $request->is('iclock/*') || $request->is('attendance/biometric*') || $request->is('face-photo/*')) {
            if (! $centralHost) {
                // If on a client subdomain, resolve that client's branding/theme
                try {
                    $this->tenancy->resolveByHost($request->getHost());
                    AppViewData::share();
                } catch (TenantResolutionException $e) {
                    // Ignore on public signin
                }
            }
            return $next($request);
        }

        // On central domain without a tenant session, redirect unauthenticated users to login
        if ($centralHost) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['status' => false, 'error' => 'Unauthenticated'], 401);
            }
            if (! $request->is('/')) {
                return redirect()->route('auth.signin');
            }
            return $next($request);
        }

        // Otherwise attempt domain resolution (subdomain-based access)
        try {
            $this->tenancy->resolveByHost($request->getHost());
        } catch (TenantResolutionException $e) {
            return $this->deny($request, $e);
        }

        AppViewData::share();

        return $next($request);
    }

    private function deny(Request $request, TenantResolutionException $e): Response
    {
        $status = in_array($e->status, [401, 403, 404, 409, 500, 503], true) ? $e->status : 403;

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => false,
                'success' => false,
                'tenant_error' => $e->reason,
                'error' => $e->getMessage(),
                'message' => $e->getMessage(),
                'expiry_date' => $e->context['expiry_date'] ?? null,
            ], $status);
        }

        return response()->view('errors.tenant', [
            'reason' => $e->reason,
            'message' => $e->getMessage(),
            'expiryDate' => $e->context['expiry_date'] ?? null,
        ], $status);
    }

    public static function isCentralHost(string $host): bool
    {
        $host = strtolower(trim($host));
        $host = preg_replace('#:\d+$#', '', $host);

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($appHost && strtolower($appHost) === $host) {
            return true;
        }

        return in_array($host, [
            'localhost',
            '127.0.0.1',
            'hotel-erp.fableadtech.com',
            'hotel.fableadtech.com',
            'devhotel.fableadtech.com',
            'erp.fableadtech.com',
            'deverp.fableadtech.com',
        ], true);
    }
}
