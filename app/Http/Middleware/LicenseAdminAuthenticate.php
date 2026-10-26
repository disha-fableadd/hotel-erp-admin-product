<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LicenseAdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('license.admin_enabled')) {
            abort(404);
        }

        if (! session('license_admin_authenticated')) {
            return redirect()->route('license-admin.login');
        }

        return $next($request);
    }
}

