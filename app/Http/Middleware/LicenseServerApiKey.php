<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LicenseServerApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('license.server_api_key');

        if ($expected === '') {
            return $next($request);
        }

        $provided = (string) $request->header('X-License-Api-Key', '');

        if (! hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized license API request.',
            ], 401);
        }

        return $next($request);
    }
}

