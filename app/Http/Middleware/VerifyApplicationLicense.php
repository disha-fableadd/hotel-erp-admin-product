<?php

namespace App\Http\Middleware;

use App\Services\License\LicenseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApplicationLicense
{
    public function __construct(private readonly LicenseManager $licenses) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->licenses->shouldEnforce()) {
            return $next($request);
        }

        if ($this->isExcepted($request)) {
            return $next($request);
        }

        $result = $this->licenses->validateForMiddleware();

        if ($result->valid) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $result->message,
                'license_required' => true,
            ], 403);
        }

        if (! $request->routeIs('license.*')) {
            $redirect = redirect()
                ->route('license.activate')
                ->with('url.intended', $request->fullUrl());

            if (! str_contains($result->message, 'No license found')) {
                $redirect->with('license_error', $result->message);
            }

            return $redirect;
        }

        return $next($request);
    }

    private function isExcepted(Request $request): bool
    {
        foreach (config('license.except_paths', []) as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
