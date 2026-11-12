<?php

namespace App\Services\Tenancy;

use App\Exceptions\TenantResolutionException;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves the incoming request to a client record managed by the central
 * admin_erp panel and points the database, storage, cache and
 * session at that client's own resources.
 */
class TenantManager
{
    private ?array $tenant = null;

    private string $sharedStoragePath;

    private ?string $baseSessionCookie = null;

    private ?Repository $lookupCache = null;

    public function __construct()
    {
        $this->sharedStoragePath = storage_path();
    }

    public function enabled(): bool
    {
        return (bool) config('tenant.enabled');
    }

    public function current(): ?array
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return isset($this->tenant['id']) ? (int) $this->tenant['id'] : null;
    }

    public function sharedStoragePath(string $path = ''): string
    {
        return $this->sharedStoragePath . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : '');
    }

    public static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('#^[a-z][a-z0-9+.-]*://#', '', $host);
        $host = preg_replace('#[/?\#].*$#', '', $host);
        $host = preg_replace('#:\d+$#', '', $host);

        return preg_replace('#^www\.#', '', $host);
    }

    /**
     * @param bool $fresh skip the short-lived cache (used at login so suspensions apply immediately)
     */
    public function resolveByHost(string $host, bool $fresh = false): array
    {
        $host = static::normalizeHost($host);
        $key = 'host:' . sha1($host . '|' . config('tenant.product_code') . '|' . config('tenant.project_code'));
        $cache = $this->lookupCache();

        if (! $fresh && is_array($cached = $cache->get($key))) {
            return $this->activate($cached);
        }

        try {
            $response = $this->client()->post('/resolve', [
                'domain' => $host,
                'product_code' => config('tenant.product_code'),
                'project_code' => config('tenant.project_code') ?: null,
            ]);
        } catch (ConnectionException $e) {
            return $this->activateStaleOrFail($key, $e->getMessage());
        }

        if ($response->serverError()) {
            return $this->activateStaleOrFail($key, 'HTTP ' . $response->status());
        }

        $tenant = $this->tenantFromResponse($response, $key);

        $cache->put($key, $tenant, max(1, (int) config('tenant.cache_seconds')));
        $cache->put($key . ':stale', $tenant, max(1, (int) config('tenant.grace_seconds')));

        return $this->activate($tenant);
    }

    /**
     * Resolve tenant by user email / username (for central login).
     */
    public function resolveByEmail(string $email, bool $fresh = false): array
    {
        $email = trim(strtolower($email));
        $key = 'user_email:' . sha1($email . '|' . config('tenant.product_code') . '|' . config('tenant.project_code'));
        $cache = $this->lookupCache();

        if (! $fresh && is_array($cached = $cache->get($key))) {
            return $this->activate($cached);
        }

        try {
            $response = $this->client()->post('/resolve-user', array_filter([
                'email' => $email,
                'username' => $email,
                'login' => $email,
                'product_code' => config('tenant.product_code'),
                'project_code' => config('tenant.project_code') ?: null,
            ]));
        } catch (ConnectionException $e) {
            return $this->activateStaleOrFail($key, $e->getMessage());
        }

        if ($response->serverError()) {
            return $this->activateStaleOrFail($key, 'HTTP ' . $response->status());
        }

        $tenant = $this->tenantFromResponse($response, $key);

        $cache->put($key, $tenant, max(1, (int) config('tenant.cache_seconds')));
        $cache->put($key . ':stale', $tenant, max(1, (int) config('tenant.grace_seconds')));

        return $this->activate($tenant);
    }

    /**
     * Resolve tenant by license key / unique_id (used by Flutter Desktop Sync & POS).
     * Uses admin /resolve-license when available; otherwise falls back to
     * GET /api/setup/search (unique_id) + /resolve by domain — no admin deploy required.
     */
    public function resolveByLicenseKey(string $licenseKey, bool $fresh = false): array
    {
        $licenseKey = trim($licenseKey);
        $normalizedKey = strtoupper($licenseKey);
        $key = 'license_key:' . sha1($normalizedKey . '|' . config('tenant.product_code') . '|' . config('tenant.project_code'));
        $cache = $this->lookupCache();

        if (! $fresh && is_array($cached = $cache->get($key))) {
            return $this->activate($cached);
        }

        // 1) Prefer dedicated admin endpoint when deployed
        try {
            $response = $this->client()->post('/resolve-license', array_filter([
                'license_key' => $licenseKey,
                'unique_id' => $licenseKey,
                'purchase_code' => $licenseKey,
                'product_code' => config('tenant.product_code'),
                'project_code' => config('tenant.project_code') ?: null,
            ]));

            if ($response->successful() && ! empty($response->json('success')) && ! empty($response->json('tenant'))) {
                $tenant = $this->tenantFromResponse($response, $key);
                $tenant['unique_id'] = $tenant['unique_id'] ?? $normalizedKey;
                $tenant['license_key'] = $tenant['license_key'] ?? $normalizedKey;
                $cache->put($key, $tenant, max(1, (int) config('tenant.cache_seconds')));
                $cache->put($key . ':stale', $tenant, max(1, (int) config('tenant.grace_seconds')));

                return $this->activate($tenant);
            }

            // 404 / missing route → fall through to setup/search fallback
            $msg = strtolower((string) ($response->json('message') ?? $response->body()));
            $canFallback = $response->status() === 404
                || str_contains($msg, 'resolve-license')
                || str_contains($msg, 'could not be found');

            if (! $canFallback && $response->serverError()) {
                return $this->activateStaleOrFail($key, 'HTTP ' . $response->status());
            }

            if (! $canFallback && $response->clientError()) {
                // Definitive rejection from resolve-license (expired/inactive/not found)
                try {
                    return $this->tenantFromResponse($response, $key);
                } catch (TenantResolutionException $e) {
                    // Still try setup/search fallback for NOT_FOUND / missing route style errors
                    if (! in_array($e->reason, ['NOT_FOUND', 'UNKNOWN', ''], true) && $e->status !== 404) {
                        throw $e;
                    }
                }
            }
        } catch (ConnectionException $e) {
            return $this->activateStaleOrFail($key, $e->getMessage());
        } catch (TenantResolutionException $e) {
            if (! in_array($e->reason, ['NOT_FOUND', 'UNKNOWN', ''], true) && $e->status !== 404) {
                throw $e;
            }
        }

        // 2) Fallback: public setup search by unique_id (already on admin-client)
        $tenant = $this->resolveTenantViaSetupSearchLicense($normalizedKey, $key);

        $cache->put($key, $tenant, max(1, (int) config('tenant.cache_seconds')));
        $cache->put($key . ':stale', $tenant, max(1, (int) config('tenant.grace_seconds')));

        return $this->activate($tenant);
    }

    /**
     * Locate setup by unique_id via /api/setup/search, then activate using /resolve(domain)
     * or a tenant array built from setup search details.
     */
    protected function resolveTenantViaSetupSearchLicense(string $licenseKey, string $cacheKey): array
    {
        $apiBase = rtrim((string) config('tenant.api_url'), '/');
        $setupSearchUrl = preg_replace('#/tenant$#', '/setup/search', $apiBase);
        if (! is_string($setupSearchUrl) || $setupSearchUrl === $apiBase) {
            $setupSearchUrl = preg_replace('#/api/tenant$#', '/api/setup/search', $apiBase) ?: ($apiBase . '/../setup/search');
        }

        try {
            $search = Http::acceptJson()
                ->timeout(max(1, (int) config('tenant.http_timeout', 8)))
                ->withHeaders(['X-Tenant-Secret' => (string) config('tenant.secret')])
                ->get($setupSearchUrl, [
                    'unique_id' => $licenseKey,
                    'type' => config('tenant.product_code') ?: 'HOTEL',
                ]);
        } catch (ConnectionException $e) {
            return $this->activateStaleOrFail($cacheKey, $e->getMessage());
        }

        if (! $search->successful() || str_contains(strtolower((string) $search->header('Content-Type')), 'text/html')) {
            // Retry with generic search=
            try {
                $search = Http::acceptJson()
                    ->timeout(max(1, (int) config('tenant.http_timeout', 8)))
                    ->withHeaders(['X-Tenant-Secret' => (string) config('tenant.secret')])
                    ->get($setupSearchUrl, [
                        'search' => $licenseKey,
                        'type' => config('tenant.product_code') ?: 'HOTEL',
                    ]);
            } catch (ConnectionException $e) {
                return $this->activateStaleOrFail($cacheKey, $e->getMessage());
            }
        }

        $rows = $search->json('data') ?? [];
        if (! is_array($rows) || empty($rows)) {
            $one = $search->json('setup');
            $rows = is_array($one) ? [$one] : [];
        }

        $setup = null;
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $uid = strtoupper(trim((string) ($row['unique_id'] ?? '')));
            if ($uid === $licenseKey || $uid === '' && count($rows) === 1) {
                $setup = $row;
                break;
            }
        }
        if (! $setup && count($rows) === 1 && is_array($rows[0])) {
            $setup = $rows[0];
        }

        if (! $setup) {
            throw new TenantResolutionException(
                'NOT_FOUND',
                'No client found for this license key.',
                404
            );
        }

        $domain = trim((string) ($setup['domain'] ?? ''));

        // Prefer full tenant payload via existing /resolve (modules, membership, etc.)
        if ($domain !== '') {
            try {
                $response = $this->client()->post('/resolve', array_filter([
                    'domain' => $domain,
                    'product_code' => config('tenant.product_code'),
                    'project_code' => config('tenant.project_code') ?: null,
                ]));

                if ($response->successful() && ! empty($response->json('success')) && ! empty($response->json('tenant'))) {
                    $tenant = $this->tenantFromResponse($response, $cacheKey);
                    $tenant['unique_id'] = $licenseKey;
                    $tenant['license_key'] = $licenseKey;

                    return $tenant;
                }
            } catch (\Throwable $e) {
                Log::warning('License fallback /resolve failed: ' . $e->getMessage());
            }
        }

        // Last resort: build tenant from setup/search row (includes DB credentials)
        $tenant = $this->tenantFromSetupSearchRow($setup, $licenseKey);
        if (! $this->isUsable($tenant)) {
            $reason = ($tenant['membership_status'] ?? '') === 'Expired' ? 'EXPIRED' : 'INACTIVE';
            throw new TenantResolutionException(
                $reason,
                'This license is inactive or the subscription has expired.',
                403,
                ['expiry_date' => $tenant['expiry_date'] ?? null]
            );
        }

        return $tenant;
    }

    /**
     * Map admin GET /api/setup/search row into TenantManager tenant array.
     */
    protected function tenantFromSetupSearchRow(array $setup, string $licenseKey): array
    {
        $db = $setup['db_details'] ?? $setup['db'] ?? [];

        return [
            'id' => $setup['id'] ?? null,
            'client_id' => data_get($setup, 'client.id'),
            'client_name' => data_get($setup, 'client.client_name'),
            'brand_name' => data_get($setup, 'client.brand_name'),
            'unique_id' => $licenseKey,
            'license_key' => $licenseKey,
            'domain' => $setup['domain'] ?? null,
            'product_code' => data_get($setup, 'product.code') ?: config('tenant.product_code'),
            'project_code' => data_get($setup, 'project.code') ?: config('tenant.project_code'),
            'status' => $setup['status'] ?? 'Active',
            'client_status' => data_get($setup, 'client.status', 'Active'),
            'membership_status' => data_get($setup, 'client.membership_status', 'Active'),
            'expiry_date' => data_get($setup, 'client.expiry_date'),
            'plan_name' => $setup['plan_name'] ?? null,
            'max_user' => $setup['max_user'] ?? data_get($setup, 'client.max_user'),
            'max_branch' => $setup['max_branch'] ?? data_get($setup, 'client.max_branch'),
            'email' => data_get($setup, 'client.work_email'),
            'modules' => $setup['modules'] ?? [],
            'sub_modules' => $setup['sub_modules'] ?? [],
            'db' => [
                'host' => $db['host'] ?? 'localhost',
                'port' => (int) ($db['port'] ?? 3306),
                'database' => $db['database'] ?? null,
                'username' => $db['username'] ?? null,
                'password' => (string) ($db['password'] ?? ''),
            ],
        ];
    }

    /**
     * Every client database of this product/project (for console commands).
     *
     * @return array<int, array>
     */
    public function all(?int $tenantId = null): array
    {
        try {
            $response = $this->client()->get('/list', array_filter([
                'product_code' => config('tenant.product_code'),
                'project_code' => config('tenant.project_code') ?: null,
                'tenant_id' => $tenantId,
            ]));
        } catch (ConnectionException $e) {
            throw new TenantResolutionException('UNREACHABLE', 'Unable to reach the admin_erp panel: ' . $e->getMessage(), 503);
        }

        if (! $response->successful()) {
            throw new TenantResolutionException(
                'LIST_FAILED',
                (string) ($response->json('message') ?? 'Tenant list request failed (HTTP ' . $response->status() . ').'),
                $response->status()
            );
        }

        return array_values(array_filter(
            (array) $response->json('tenants', []),
            fn ($tenant) => $this->belongsToThisApp($tenant)
        ));
    }

    public function activateById(int $tenantId, bool $fresh = false): array
    {
        return $this->resolveById($tenantId, $fresh);
    }

    /**
     * Resolve and activate tenant by setup/tenant ID.
     */
    public function resolveById(int $tenantId, bool $fresh = false): array
    {
        $key = 'tenant_id:' . (int) $tenantId . '|' . config('tenant.product_code') . '|' . config('tenant.project_code');
        $cache = $this->lookupCache();

        if (! $fresh && is_array($cached = $cache->get($key))) {
            return $this->activate($cached);
        }

        try {
            $tenants = $this->all($tenantId);
            $tenant = $tenants[0] ?? null;
        } catch (ConnectionException $e) {
            return $this->activateStaleOrFail($key, $e->getMessage());
        }

        if (! $tenant) {
            $this->lookupCache()->forget($key);
            $this->lookupCache()->forget($key . ':stale');
            throw new TenantResolutionException('NOT_FOUND', "Tenant #{$tenantId} was not found for this product.", 404);
        }

        if (! $this->isUsable($tenant)) {
            $this->lookupCache()->forget($key);
            $this->lookupCache()->forget($key . ':stale');
            $reason = ($tenant['membership_status'] ?? '') === 'Expired' ? 'EXPIRED' : 'INACTIVE';
            throw new TenantResolutionException($reason, "Tenant #{$tenantId} is currently not active or subscription has expired.", 403, [
                'expiry_date' => $tenant['expiry_date'] ?? null,
                'plan_name' => $tenant['plan_name'] ?? 'Starter',
            ]);
        }

        $cache->put($key, $tenant, max(1, (int) config('tenant.cache_seconds')));
        $cache->put($key . ':stale', $tenant, max(1, (int) config('tenant.grace_seconds')));

        return $this->activate($tenant);
    }

    /**
     * Synchronize client admin profile modifications to master admin_erp database.
     */
    public function syncProfileToAdmin(array $payload): ?Response
    {
        try {
            $response = $this->client()->post('/update-profile', array_filter([
                'client_id' => $payload['client_id'] ?? null,
                'tenant_id' => $payload['tenant_id'] ?? null,
                'domain' => $payload['domain'] ?? null,
                'current_email' => $payload['current_email'] ?? null,
                'name' => $payload['name'] ?? null,
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'password' => $payload['password'] ?? null,
            ]));

            // Invalidate local tenant lookup cache so fresh details are picked up immediately
            $this->forgetCurrentCache();

            return $response;
        } catch (\Throwable $e) {
            Log::warning('TenantManager syncProfileToAdmin error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clear all cached lookup entries for the current tenant.
     */
    public function forgetCurrentCache(): void
    {
        try {
            $cache = $this->lookupCache();
            if ($this->tenant) {
                if (!empty($this->tenant['domain'])) {
                    $host = static::normalizeHost($this->tenant['domain']);
                    $key = 'host:' . sha1($host . '|' . config('tenant.product_code') . '|' . config('tenant.project_code'));
                    $cache->forget($key);
                    $cache->forget($key . ':stale');
                }
                if (!empty($this->tenant['email'])) {
                    $email = trim(strtolower($this->tenant['email']));
                    $key = 'user_email:' . sha1($email . '|' . config('tenant.product_code') . '|' . config('tenant.project_code'));
                    $cache->forget($key);
                    $cache->forget($key . ':stale');
                }
                if (!empty($this->tenant['id'])) {
                    $key = 'tenant_id:' . (int) $this->tenant['id'] . '|' . config('tenant.product_code') . '|' . config('tenant.project_code');
                    $cache->forget($key);
                    $cache->forget($key . ':stale');
                    $enrichKey = 'license_enrich:' . (int) $this->tenant['id'];
                    $cache->forget($enrichKey);
                    $cache->forget($enrichKey . ':miss');
                }
            }
        } catch (\Throwable) {
            // Ignore cache forget errors
        }
    }

    public function isUsable(array $tenant): bool
    {
        return strcasecmp((string) ($tenant['status'] ?? ''), 'Active') === 0
            && strcasecmp((string) ($tenant['client_status'] ?? 'Active'), 'Active') === 0
            && ! in_array($tenant['membership_status'] ?? null, ['Expired', 'Pending'], true);
    }

    public function activate(array $tenant): array
    {
        $db = $tenant['db'] ?? [];
        if (empty($db['database'])) {
            throw new TenantResolutionException('NOT_PROVISIONED', 'The database for this account has not been configured yet.', 409);
        }

        $connection = config('database.default', 'mysql');
        $dbHost = $db['host'] ?? '127.0.0.1';
        if (PHP_OS_FAMILY !== 'Windows' && in_array($dbHost, ['127.0.0.1', 'localhost', ''], true)) {
            $dbHost = 'localhost';
        }

        $dbUsername = $db['username'] ?? 'root';
        $dbPassword = (string) ($db['password'] ?? '');
        // Prefer sticky/session-resolved name when present so product load and Place Order
        // never flip between u123_hotelerpdb (empty) and hotelerpdb (real data).
        $databaseName = (string) ($db['resolved_database'] ?? $db['database']);

        // On local development, allow using local MySQL credentials (e.g. XAMPP root with blank password)
        // Try connecting with tenant's specific DB credentials first
        $pdo = null;
        if (!empty($dbUsername)) {
            try {
                $pdo = new \PDO("mysql:host={$dbHost};port=" . (int) ($db['port'] ?? 3306), $dbUsername, $dbPassword, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT,
                    \PDO::ATTR_TIMEOUT => 2,
                ]);
            } catch (\Throwable) {
                $pdo = null;
            }
        }

        // On local development, if tenant specific credentials cannot connect (e.g. remote cPanel user), fallback to local default
        if (!$pdo && app()->environment('local')) {
            $defaultUsername = config("database.connections.{$connection}.username") ?: 'root';
            $defaultPassword = config("database.connections.{$connection}.password") ?? '';
            try {
                $pdo = new \PDO("mysql:host={$dbHost};port=" . (int) ($db['port'] ?? 3306), $defaultUsername, $defaultPassword, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT,
                    \PDO::ATTR_TIMEOUT => 2,
                ]);
                $dbUsername = $defaultUsername;
                $dbPassword = $defaultPassword;
            } catch (\Throwable) {
                $pdo = null;
            }
        }

        if ($pdo) {
            try {
                $preferred = (string) ($db['database'] ?? $databaseName);
                $databaseName = $this->pickBestExistingDatabase($pdo, $preferred, $databaseName);
            } catch (\Throwable $e) {
                Log::warning('Tenant DB pick failed: ' . $e->getMessage(), [
                    'tenant_id' => (int) ($tenant['id'] ?? 0),
                    'preferred' => $databaseName,
                ]);
            }
        }

        config([
            "database.connections.{$connection}.host" => $dbHost,
            "database.connections.{$connection}.port" => (int) ($db['port'] ?? 3306),
            "database.connections.{$connection}.database" => $databaseName,
            "database.connections.{$connection}.username" => $dbUsername,
            "database.connections.{$connection}.password" => $dbPassword,
        ]);
        DB::purge($connection);
        try {
            DB::connection($connection)->getPdo();
        } catch (\Throwable) {
            // First query path will reconnect; purge already cleared stale PDO.
        }

        $this->isolateFilesystem((int) $tenant['id']);

        // Persist the actual schema we connected to so later requests stay on the same DB.
        $tenant['db'] = array_merge($db, [
            'host' => $dbHost,
            'port' => (int) ($db['port'] ?? 3306),
            'database' => $db['database'] ?? $databaseName,
            'resolved_database' => $databaseName,
            'username' => $dbUsername,
            'password' => $dbPassword,
        ]);

        Log::shareContext([
            'tenant_id' => (int) $tenant['id'],
            'tenant_db' => $databaseName,
        ]);

        // Ensure Flutter / Profile license key is present (admin-client setups.unique_id)
        $tenant = $this->ensureLicenseKey($tenant);

        $this->tenant = $tenant;
        app()->instance('tenant', $tenant);

        return $tenant;
    }

    /**
     * Ensure tenant has unique_id / license_key for Profile + Flutter sync.
     * Admin Edit Setup already stores this as setups.unique_id (e.g. M8F6Z).
     *
     * @param  array<string, mixed>  $extraHints  optional name/email/domain overrides
     */
    public function ensureLicenseKey(array $tenant, array $extraHints = [], bool $force = false): array
    {
        $existing = trim((string) ($tenant['unique_id'] ?? $tenant['license_key'] ?? ''));
        if ($existing !== '') {
            $code = strtoupper($existing);
            $tenant['unique_id'] = $code;
            $tenant['license_key'] = $code;
            $this->persistLicenseKeyToSettings($code);

            return $tenant;
        }

        // Hotel settings fallback (saved after a successful lookup)
        $fromSettings = $this->readLicenseKeyFromSettings();
        if ($fromSettings !== null) {
            $tenant['unique_id'] = $fromSettings;
            $tenant['license_key'] = $fromSettings;

            return $tenant;
        }

        $apiBase = rtrim((string) config('tenant.api_url'), '/');
        if ($apiBase === '') {
            return $tenant;
        }

        $enrichCacheKey = 'license_enrich:' . (int) ($tenant['id'] ?? 0);
        $lookupCache = $this->lookupCache();

        if ($force) {
            $lookupCache->forget($enrichCacheKey);
            $lookupCache->forget($enrichCacheKey . ':miss');
        }

        if (! $force && $lookupCache->has($enrichCacheKey . ':miss')) {
            return $tenant;
        }
        if (! $force && is_string($cachedCode = $lookupCache->get($enrichCacheKey))) {
            $tenant['unique_id'] = strtoupper($cachedCode);
            $tenant['license_key'] = strtoupper($cachedCode);

            return $tenant;
        }

        // TENANT_API_URL = .../api/tenant  →  setup search = .../api/setup/search
        $setupSearchUrl = preg_replace('#/tenant$#', '/setup/search', $apiBase);
        if (! is_string($setupSearchUrl) || $setupSearchUrl === $apiBase) {
            $setupSearchUrl = preg_replace('#/api/tenant$#', '/api/setup/search', $apiBase) ?: ($apiBase . '/../setup/search');
        }

        $clientName = trim((string) (
            $extraHints['client_name']
            ?? $tenant['client_name']
            ?? $tenant['brand_name']
            ?? $extraHints['name']
            ?? ''
        ));
        $brandName = trim((string) ($extraHints['brand_name'] ?? $tenant['brand_name'] ?? $clientName));
        $domain = trim((string) ($extraHints['domain'] ?? $tenant['domain'] ?? ''));
        $email = trim((string) ($extraHints['email'] ?? $tenant['email'] ?? ''));
        $wantDb = strtolower((string) ($tenant['db']['database'] ?? $tenant['db']['resolved_database'] ?? ''));

        // Prefer brand/client name — admin setup/search matches these (DB name does not)
        $querySets = [];
        if ($brandName !== '') {
            $querySets[] = ['brand_name' => $brandName, 'type' => config('tenant.product_code') ?: 'HOTEL'];
            $querySets[] = ['search' => $brandName, 'type' => config('tenant.product_code') ?: 'HOTEL'];
        }
        if ($clientName !== '' && strcasecmp($clientName, $brandName) !== 0) {
            $querySets[] = ['brand_name' => $clientName, 'type' => config('tenant.product_code') ?: 'HOTEL'];
            $querySets[] = ['search' => $clientName, 'type' => config('tenant.product_code') ?: 'HOTEL'];
        }
        if ($domain !== '') {
            $querySets[] = ['search' => $domain, 'type' => config('tenant.product_code') ?: 'HOTEL'];
            // Also try without first subdomain label (hotel-erp.x.com → x.com won't help; try erp. host variants)
            if (str_contains($domain, 'hotel-erp.')) {
                $querySets[] = ['search' => str_replace('hotel-erp.', 'erp.', $domain), 'type' => config('tenant.product_code') ?: 'HOTEL'];
            }
        }
        if ($email !== '' && str_contains($email, '@')) {
            // brand often matches email local-part for small hotels
            $local = strstr($email, '@', true);
            if (is_string($local) && strlen($local) >= 3) {
                $querySets[] = ['search' => $local, 'type' => config('tenant.product_code') ?: 'HOTEL'];
            }
        }

        foreach ($querySets as $params) {
            try {
                $response = Http::acceptJson()
                    ->timeout(max(1, (int) config('tenant.http_timeout', 8)))
                    ->get($setupSearchUrl, $params);

                if (! $response->successful()) {
                    continue;
                }

                // Ignore HTML SPA responses
                $contentType = strtolower((string) $response->header('Content-Type'));
                if (str_contains($contentType, 'text/html')) {
                    continue;
                }

                $rows = $response->json('data') ?? [];
                if (! is_array($rows) || empty($rows)) {
                    $one = $response->json('setup');
                    $rows = is_array($one) ? [$one] : [];
                }

                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $code = trim((string) ($row['unique_id'] ?? $row['license_key'] ?? ''));
                    if ($code === '') {
                        continue;
                    }

                    $rowDomain = strtolower((string) ($row['domain'] ?? ''));
                    $rowDb = strtolower((string) (
                        $row['database_name']
                        ?? data_get($row, 'db.database')
                        ?? data_get($row, 'db_details.database')
                        ?? ''
                    ));
                    $rowClient = strtolower((string) (
                        data_get($row, 'client.client_name')
                        ?? data_get($row, 'client.brand_name')
                        ?? $row['client_name']
                        ?? ''
                    ));
                    $wantDomain = strtolower($domain);
                    $wantClient = strtolower($clientName !== '' ? $clientName : $brandName);

                    $domainOk = $wantDomain === '' || $rowDomain === '' || $rowDomain === $wantDomain
                        || str_contains($rowDomain, explode('.', $wantDomain)[0] ?? '')
                        || str_contains($wantDomain, explode('.', $rowDomain)[0] ?? '')
                        || (str_contains($wantDomain, 'hotel-erp.') && str_contains($rowDomain, 'erp.'));
                    $dbOk = $wantDb === '' || $rowDb === '' || $rowDb === $wantDb
                        || str_contains($rowDb, $wantDb) || str_contains($wantDb, $rowDb);
                    $clientOk = $wantClient === '' || $rowClient === '' || $rowClient === $wantClient
                        || str_contains($rowClient, $wantClient) || str_contains($wantClient, $rowClient);

                    if (! $domainOk && ! $dbOk && ! $clientOk && count($rows) > 1) {
                        continue;
                    }

                    $code = strtoupper($code);
                    $tenant['unique_id'] = $code;
                    $tenant['license_key'] = $code;
                    $lookupCache->put($enrichCacheKey, $code, 86400);
                    $lookupCache->forget($enrichCacheKey . ':miss');
                    $this->persistLicenseKeyToSettings($code);

                    return $tenant;
                }
            } catch (\Throwable $e) {
                Log::warning('Tenant license enrich failed: ' . $e->getMessage(), [
                    'params' => $params,
                    'tenant_id' => $tenant['id'] ?? null,
                ]);
            }
        }

        $lookupCache->put($enrichCacheKey . ':miss', 1, 60);

        return $tenant;
    }

    protected function persistLicenseKeyToSettings(string $code): void
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return;
        }

        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('settings', 'client_license_key')) {
                \Illuminate\Support\Facades\Schema::table('settings', function ($table) {
                    $table->string('client_license_key', 32)->nullable();
                });
            }
            $row = \App\Models\Setting::query()->first();
            if ($row && (string) ($row->client_license_key ?? '') !== $code) {
                $row->client_license_key = $code;
                $row->saveQuietly();
            } elseif (! $row) {
                // ignore — settings row should exist
            }
        } catch (\Throwable $e) {
            Log::warning('persistLicenseKeyToSettings failed: ' . $e->getMessage());
        }
    }

    protected function readLicenseKeyFromSettings(): ?string
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return null;
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('settings', 'client_license_key')) {
                return null;
            }
            $val = \App\Models\Setting::query()->value('client_license_key');
            $val = is_string($val) ? strtoupper(trim($val)) : '';

            return $val !== '' ? $val : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Choose the live schema for this tenant.
     * Avoids empty auto-created "u123_xxx" DBs when a populated local "xxx" copy exists.
     */
    private function pickBestExistingDatabase(\PDO $pdo, string $preferred, string $sticky = ''): string
    {
        $candidates = [];
        foreach ([$sticky, $preferred, preg_replace('/^u\d+_/', '', $preferred)] as $name) {
            $name = trim((string) $name);
            if ($name !== '' && preg_match('/^[a-zA-Z0-9_]+$/', $name) && !in_array($name, $candidates, true)) {
                $candidates[] = $name;
            }
        }

        $existing = [];
        foreach ($candidates as $name) {
            $stmt = $pdo->query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ' . $pdo->quote($name));
            if ($stmt && $stmt->fetch()) {
                $existing[] = $name;
            }
        }

        if (!$existing) {
            // Do NOT auto-create empty DBs on HTTP requests — that caused product_id "invalid"
            // when Place Order hit a fresh empty schema while the UI loaded from another DB.
            return $preferred;
        }

        // Prefer sticky when it still exists (session consistency).
        if ($sticky !== '' && in_array($sticky, $existing, true)) {
            return $sticky;
        }

        $best = $existing[0];
        $bestScore = -1;
        foreach ($existing as $name) {
            $score = -1;
            try {
                $countStmt = $pdo->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $name) . '`.`products`');
                if ($countStmt) {
                    $score = (int) $countStmt->fetchColumn();
                }
            } catch (\Throwable) {
                $score = -1;
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $name;
            }
        }

        if ($best !== $preferred) {
            Log::info('Tenant DB resolved to alternate local schema', [
                'preferred' => $preferred,
                'resolved' => $best,
                'product_rows' => $bestScore,
            ]);
        }

        return $best;
    }

    public function forgetCachedHost(string $host): void
    {
        $host = static::normalizeHost($host);
        $key = 'host:' . sha1($host . '|' . config('tenant.product_code') . '|' . config('tenant.project_code'));
        $this->lookupCache()->forget($key);
        $this->lookupCache()->forget($key . ':stale');
    }

    /**
     * Uploads, generated PDFs, file cache and sessions live under
     * storage/tenants/{id}, so code calling storage_path() or the public
     * disk is isolated per client without modification.
     */
    private function isolateFilesystem(int $tenantId): void
    {
        $root = $this->sharedStoragePath('tenants' . DIRECTORY_SEPARATOR . $tenantId);

        foreach (['app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
            File::ensureDirectoryExists($root . DIRECTORY_SEPARATOR . $dir, 0755);
        }

        app()->useStoragePath($root);

        $this->baseSessionCookie ??= (string) config('session.cookie');

        config([
            'filesystems.disks.local.root' => $root . DIRECTORY_SEPARATOR . 'app',
            'filesystems.disks.public.root' => $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public',
            'cache.stores.file.path' => $root . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'data',
            'cache.prefix' => 'tenant_' . $tenantId . '_',
            'session.files' => $root . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions',
            'session.cookie' => $this->baseSessionCookie . '_t' . $tenantId,
        ]);

        Storage::forgetDisk(['local', 'public']);
        Cache::forgetDriver('file');
        Cache::forgetDriver(config('cache.default'));
    }

    private function tenantFromResponse(Response $response, string $key): array
    {
        $body = (array) $response->json();

        if ($response->successful() && ! empty($body['success']) && ! empty($body['tenant'])) {
            if (! $this->belongsToThisApp($body['tenant'])) {
                throw new TenantResolutionException('PRODUCT_MISMATCH', 'This account does not belong to this product.', 403);
            }

            $tenant = $body['tenant'];
            if (! empty($tenant['id'])) {
                $idKey = 'tenant_id:' . (int) $tenant['id'] . '|' . config('tenant.product_code') . '|' . config('tenant.project_code');
                $this->lookupCache()->put($idKey, $tenant, max(1, (int) config('tenant.cache_seconds')));
                $this->lookupCache()->put($idKey . ':stale', $tenant, max(1, (int) config('tenant.grace_seconds')));
            }

            return $tenant;
        }

        // A definitive "no" from the admin panel must not be overridden by the grace copy.
        $this->lookupCache()->forget($key);
        $this->lookupCache()->forget($key . ':stale');

        throw new TenantResolutionException(
            (string) ($body['code'] ?? 'UNKNOWN'),
            (string) ($body['message'] ?? 'Unable to load your account.'),
            $response->status() >= 400 ? $response->status() : 403,
            $body
        );
    }

    private function belongsToThisApp(array $tenant): bool
    {
        $product = strtoupper((string) config('tenant.product_code'));
        $project = strtoupper((string) config('tenant.project_code'));

        $tProduct = strtoupper((string) ($tenant['product_code'] ?? ''));
        $tProject = strtoupper((string) ($tenant['project_code'] ?? ''));

        // Allow flexible aliases for Hotel products (e.g. HOTEL, HOTEL_ERP, FABLEAD_HOTEL)
        $hotelProductAliases = ['HOTEL', 'HOTEL_ERP', 'HOTEL-ERP', 'FABLEAD_HOTEL'];
        $isThisHotelApp = in_array($product, $hotelProductAliases, true) || in_array($project, $hotelProductAliases, true);

        if ($isThisHotelApp) {
            // Check if tenant has any hotel product, project, domain, or db indicator
            $isHotelTenant = in_array($tProduct, $hotelProductAliases, true)
                || in_array($tProject, $hotelProductAliases, true)
                || str_contains(strtolower($tenant['domain'] ?? ''), 'hotel')
                || str_contains(strtolower($tenant['db']['database'] ?? ''), 'hotel');

            if ($isHotelTenant) {
                return true;
            }

            // In console commands targeting a specific tenant ID, allow it
            if (app()->runningInConsole() && !empty($tenant['id']) && (int) $tenant['id'] === (int) getenv('TENANT_ID')) {
                return true;
            }
        }

        $productMatches = ($tProduct === '' || $product === '' || $tProduct === $product || ($isThisHotelApp && in_array($tProduct, $hotelProductAliases, true)));
        $projectMatches = ($tProject === '' || $project === '' || $tProject === $project || ($isThisHotelApp && in_array($tProject, $hotelProductAliases, true)));

        return $productMatches && $projectMatches;
    }

    private function activateStaleOrFail(string $key, string $reason): array
    {
        $stale = $this->lookupCache()->get($key . ':stale');

        Log::warning('Tenant API unavailable', ['reason' => $reason, 'using_stale' => is_array($stale)]);

        if (is_array($stale)) {
            return $this->activate($stale);
        }

        throw new TenantResolutionException('UNREACHABLE', 'Unable to reach the account server. Please try again in a few minutes.', 503);
    }

    private function client(): PendingRequest
    {
        $url = (string) config('tenant.api_url');
        if ($url === '' || strlen((string) config('tenant.secret')) < 32 || config('tenant.product_code') === '') {
            throw new TenantResolutionException(
                'NOT_CONFIGURED',
                'Tenancy is enabled but TENANT_API_URL, TENANT_API_SECRET (min 32 chars) or TENANT_PRODUCT_CODE is missing.',
                500
            );
        }

        return Http::baseUrl($url)
            ->acceptJson()
            ->asForm()
            ->timeout(max(1, (int) config('tenant.http_timeout')))
            ->withHeaders(['X-Tenant-Secret' => (string) config('tenant.secret')]);
    }

    private function lookupCache(): Repository
    {
        if ($this->lookupCache === null) {
            $path = $this->sharedStoragePath('framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'tenant-lookup');
            File::ensureDirectoryExists($path, 0755);
            $this->lookupCache = Cache::repository(new FileStore(app('files'), $path));
        }

        return $this->lookupCache;
    }
}
