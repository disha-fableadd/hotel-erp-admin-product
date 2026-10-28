<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Files in the shared public/storage link are served directly by the web
 * server; anything else under /storage reaches this controller and is read
 * from the current tenant's storage/tenants/{id}/app/public directory.
 */
class TenantStorageController extends Controller
{
    public function __invoke(Request $request, string $path): BinaryFileResponse
    {
        $normalizedPath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

        // Pattern 1: Direct tenant storage path: tenants/{id}/app/public/{subpath}
        if (preg_match('#^tenants[\\/](\d+)[\\/]app[\\/]public[\\/](.*)$#i', $normalizedPath, $matches)) {
            $tenantId = (int) $matches[1];
            $relativePath = $matches[2];
            $base = base_path('storage'.DIRECTORY_SEPARATOR.'tenants'.DIRECTORY_SEPARATOR.$tenantId.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public');
            $file = realpath($base.DIRECTORY_SEPARATOR.$relativePath);

            if ($file && str_starts_with($file, realpath($base).DIRECTORY_SEPARATOR) && is_file($file)) {
                return response()->file($file, [
                    'Cache-Control' => 'private, max-age=86400',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        // Pattern 2: Current tenant's isolated storage
        $root = realpath(storage_path('app/public'));
        $file = $root ? realpath($root.DIRECTORY_SEPARATOR.$normalizedPath) : false;

        if ($file && str_starts_with($file, $root.DIRECTORY_SEPARATOR) && is_file($file)) {
            return response()->file($file, [
                'Cache-Control' => 'private, max-age=86400',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        // Pattern 2b: Tenant resolved by tenant('id') or session('tenant_id')
        $tenantId = null;
        try {
            if (function_exists('tenant')) {
                $tenantId = tenant('id');
            }
            if (!$tenantId && function_exists('session') && session()->has('tenant_id')) {
                $tenantId = (int) session('tenant_id');
            }
        } catch (\Throwable) {
            $tenantId = null;
        }

        if ($tenantId) {
            $base = base_path('storage'.DIRECTORY_SEPARATOR.'tenants'.DIRECTORY_SEPARATOR.$tenantId.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public');
            $file = realpath($base.DIRECTORY_SEPARATOR.$normalizedPath);
            if ($file && str_starts_with($file, realpath($base).DIRECTORY_SEPARATOR) && is_file($file)) {
                return response()->file($file, [
                    'Cache-Control' => 'private, max-age=86400',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        // Pattern 3: Shared base storage fallback (storage/app/public)
        $sharedRoot = realpath(base_path('storage/app/public'));
        if ($sharedRoot) {
            $sharedFile = realpath($sharedRoot.DIRECTORY_SEPARATOR.$normalizedPath);
            if ($sharedFile && str_starts_with($sharedFile, $sharedRoot.DIRECTORY_SEPARATOR) && is_file($sharedFile)) {
                return response()->file($sharedFile, [
                    'Cache-Control' => 'private, max-age=86400',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        abort(404);
    }
}
