<?php

namespace App\Services\License;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

class LicenseManager
{
    public function __construct(
        private readonly LicenseStorage $storage,
        private readonly LicenseSigner $signer,
        private readonly RemoteLicenseClient $remote
    ) {}

    public function isEnforcementEnabled(): bool
    {
        return (bool) config('license.enabled');
    }

    public function shouldEnforce(): bool
    {
        if (! $this->isEnforcementEnabled()) {
            return false;
        }

        if (app()->runningInConsole()) {
            $command = implode(' ', $_SERVER['argv'] ?? []);
            foreach (config('license.except_commands', []) as $pattern) {
                if ($this->commandMatches($command, $pattern)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function isLicensed(): bool
    {
        return $this->allowsAccess();
    }

    /**
     * Fast gate for HTTP middleware — stored license only, no remote server call.
     */
    public function allowsAccess(): bool
    {
        return $this->validateLocal()->valid;
    }

    /**
     * Middleware: local file check + remote verify so removed/suspended domains stop working promptly.
     *
     * @return object{valid: bool, message: string, payload: ?array}
     */
    public function validateForMiddleware(): object
    {
        $local = $this->validateLocal();
        if (! $local->valid) {
            return $local;
        }

        if (! $this->mustVerifyForMiddleware()) {
            return $local;
        }

        $remote = $this->remoteRevalidate($local->payload);
        if (! $remote->valid) {
            return $remote;
        }

        $afterRemote = $this->validateLocal();

        return $afterRemote->valid
            ? $afterRemote
            : (object) [
                'valid' => false,
                'message' => $afterRemote->message,
                'payload' => null,
            ];
    }

    /**
     * Page loads always ask the license server (so removed domains stop on next click).
     */
    private function mustVerifyForMiddleware(): bool
    {
        return config('license.mode') === 'remote' && $this->remote->isConfigured();
    }

    /**
     * @return object{valid: bool, message: string, payload: ?array}
     */
    public function validateLocal(): object
    {
        if (! $this->isEnforcementEnabled()) {
            return (object) ['valid' => true, 'message' => 'License enforcement disabled.', 'payload' => null];
        }

        $payload = $this->storage->read();
        if ($payload === null) {
            $message = match ($this->storage->readFailure()) {
                'corrupt' => 'License file is invalid or APP_KEY changed. Please activate again at /license.',
                default => 'No license found. Please activate your purchase at /license.',
            };

            return (object) ['valid' => false, 'message' => $message, 'payload' => null];
        }

        $integrity = $this->verifyStoredPayload($payload);
        if (! $integrity->valid) {
            return $integrity;
        }

        $localStatus = (string) ($payload['license_status'] ?? 'active');
        if ($localStatus !== 'active') {
            return (object) [
                'valid' => false,
                'message' => $this->statusMessage($localStatus),
                'payload' => $payload,
            ];
        }

        try {
            $serverStatus = Cache::get('license.server_status.'.($payload['purchase_code_hash'] ?? ''));
        } catch (\Throwable) {
            $serverStatus = null;
        }

        if (in_array($serverStatus, ['suspended', 'revoked'], true)) {
            return (object) [
                'valid' => false,
                'message' => $this->statusMessage($serverStatus),
                'payload' => $payload,
            ];
        }

        $domain = DomainNormalizer::fromRequest();
        if (! $this->domainAllowed($payload, $domain)) {
            return (object) [
                'valid' => false,
                'message' => 'This license is not valid for domain: '.($domain ?? 'unknown').'. Re-activate at /license for this domain.',
                'payload' => $payload,
            ];
        }

        return (object) ['valid' => true, 'message' => 'License valid.', 'payload' => $payload];
    }

    /**
     * Full validation including optional remote license-server check.
     *
     * @return object{valid: bool, message: string, payload: ?array}
     */
    public function validate(bool $forceRemote = false): object
    {
        $local = $this->validateLocal();
        if (! $local->valid) {
            return $local;
        }

        $payload = $local->payload;

        if ($this->mustVerifyWithLicenseServer($forceRemote)) {
            $remote = $this->remoteRevalidate($payload);
            if (! $remote->valid) {
                return $remote;
            }
            $payload = $remote->payload ?? $payload;
        }

        return (object) ['valid' => true, 'message' => 'License valid.', 'payload' => $payload];
    }

    /**
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $result = $this->validateLocal();
        $payload = $result->payload ?? [];

        return [
            'valid' => $result->valid,
            'message' => $result->message,
            'domain' => DomainNormalizer::fromRequest(),
            'license_type' => $payload['license_type'] ?? null,
            'domains' => $payload['domains'] ?? [],
            'buyer' => $payload['buyer'] ?? null,
            'activated_at' => $payload['activated_at'] ?? null,
            'mode' => config('license.mode'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function activateRemote(string $purchaseCode, string $buyer, ?string $domain = null): array
    {
        $domain = DomainNormalizer::normalize($domain ?? DomainNormalizer::fromRequest());
        if ($domain === null) {
            throw new RuntimeException('Could not detect installation domain.');
        }

        $response = $this->remote->activate([
            'product_code' => $this->remoteProductCode(),
            'purchase_code' => $purchaseCode,
            'buyer' => $buyer,
            'domain' => $domain,
            'installation_id' => $this->storage->installationHash(),
        ]);

        if (empty($response['success'])) {
            throw new RuntimeException($response['message'] ?? 'Activation failed.');
        }

        $payload = $this->payloadFromToken((string) ($response['license_token'] ?? ''));
        $this->persistPayload($payload);

        Cache::put($this->cacheKey(), time(), now()->addDays(30));

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function activateOffline(string $licenseToken): array
    {
        $payload = $this->payloadFromToken($licenseToken);

        $this->assertProductCode($payload);
        $this->persistPayload($payload);
        Cache::put($this->cacheKey(), time(), now()->addDays(30));

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function addDomainRemote(string $purchaseCode, string $buyer, string $newDomain): array
    {
        $newDomain = DomainNormalizer::normalize($newDomain);
        if ($newDomain === null) {
            throw new RuntimeException('Invalid domain.');
        }

        $response = $this->remote->addDomain([
            'product_code' => $this->remoteProductCode(),
            'purchase_code' => $purchaseCode,
            'buyer' => $buyer,
            'domain' => $newDomain,
            'installation_id' => $this->storage->installationHash(),
        ]);

        if (empty($response['success'])) {
            throw new RuntimeException($response['message'] ?? 'Could not add domain.');
        }

        $payload = $this->payloadFromToken((string) ($response['license_token'] ?? ''));
        $this->persistPayload($payload);
        Cache::put($this->cacheKey(), time(), now()->addDays(30));

        return $payload;
    }

    public function deactivate(): void
    {
        $this->storage->delete();
        Cache::forget($this->cacheKey());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function buildPayload(
        string $purchaseCode,
        string $buyer,
        string $licenseType,
        array $domains,
        ?int $expiresAt = null
    ): array {
        $normalizedDomains = array_values(array_unique(array_filter(array_map(
            fn ($d) => DomainNormalizer::normalize((string) $d),
            $domains
        ))));

        return [
            'product_code' => config('license.product_code'),
            'purchase_code_hash' => hash('sha256', strtoupper(trim($purchaseCode))),
            'buyer' => trim($buyer),
            'license_type' => $licenseType,
            'license_status' => 'active',
            'domains' => $normalizedDomains,
            'max_domains' => config('license.domain_limits.'.$licenseType, 1),
            'activated_at' => time(),
            'expires_at' => $expiresAt,
            'signature' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function issueToken(
        string $purchaseCode,
        string $buyer,
        string $licenseType,
        array $domains,
        ?int $expiresAt = null
    ): array {
        $payload = $this->buildPayload($purchaseCode, $buyer, $licenseType, $domains, $expiresAt);
        $payload['signature'] = $this->signer->sign($this->signingPayload($payload));

        return $payload;
    }

    /**
     * @return object{valid: bool, message: string, payload: ?array}
     */
    private function verifyStoredPayload(array $payload): object
    {
        if (($payload['installation_hash'] ?? '') !== $this->storage->installationHash()) {
            return (object) [
                'valid' => false,
                'message' => 'License file does not belong to this installation.',
                'payload' => null,
            ];
        }

        try {
            $this->assertProductCode($payload);
        } catch (RuntimeException $e) {
            return (object) ['valid' => false, 'message' => $e->getMessage(), 'payload' => null];
        }

        $signature = (string) ($payload['signature'] ?? '');
        if (! $this->signer->verify($this->signingPayload($payload), $signature)) {
            return (object) [
                'valid' => false,
                'message' => 'License signature is invalid or tampered.',
                'payload' => null,
            ];
        }

        if (! empty($payload['expires_at']) && time() > (int) $payload['expires_at']) {
            return (object) [
                'valid' => false,
                'message' => 'License has expired.',
                'payload' => $payload,
            ];
        }

        return (object) ['valid' => true, 'message' => 'OK', 'payload' => $payload];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function domainAllowed(array $payload, ?string $domain): bool
    {
        if ($domain === null) {
            return false;
        }

        $licenseType = (string) ($payload['license_type'] ?? 'regular');

        if ($licenseType === 'development' && config('license.allow_localhost') && DomainNormalizer::isLocalhost($domain)) {
            return true;
        }

        $domains = $payload['domains'] ?? [];
        foreach ($domains as $allowed) {
            if (DomainNormalizer::domainsMatch((string) $allowed, $domain)) {
                return true;
            }
        }

        return false;
    }

    private function mustVerifyWithLicenseServer(bool $forceRemote): bool
    {
        if (config('license.mode') !== 'remote' || ! $this->remote->isConfigured()) {
            return false;
        }

        if ($forceRemote) {
            return true;
        }

        $minutes = (int) config('license.verify_interval_minutes', 0);
        if ($minutes <= 0) {
            return true;
        }

        $hours = (int) config('license.verify_interval_hours', 0);
        if ($hours > 0) {
            $minutes = $hours * 60;
        }

        $last = Cache::get($this->cacheKey());

        return $last === null || (time() - (int) $last) >= ($minutes * 60);
    }

    public function purgeLocalLicenseIfMatches(string $purchaseCodeHash): void
    {
        $payload = $this->storage->read();
        if ($payload === null) {
            return;
        }

        if (($payload['purchase_code_hash'] ?? '') === $purchaseCodeHash) {
            $this->deactivate();
        }
    }

    public function purgeLocalLicenseForRemovedDomain(string $purchaseCodeHash, string $removedDomain): void
    {
        $payload = $this->storage->read();
        if ($payload === null || ($payload['purchase_code_hash'] ?? '') !== $purchaseCodeHash) {
            return;
        }

        $current = DomainNormalizer::fromRequest();
        if ($current !== null && DomainNormalizer::domainsMatch($removedDomain, $current)) {
            $this->deactivate();

            return;
        }

        $allowed = $payload['domains'] ?? [];
        $stillAllowed = array_filter($allowed, fn ($d) => ! DomainNormalizer::domainsMatch($removedDomain, (string) $d));

        if ($stillAllowed === []) {
            $this->deactivate();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return object{valid: bool, message: string, payload: ?array}
     */
    private function remoteRevalidate(array $payload): object
    {
        try {
            $domain = DomainNormalizer::fromRequest();
            $response = $this->remote->verify([
                'product_code' => $this->remoteProductCode(),
                'purchase_code_hash' => $payload['purchase_code_hash'] ?? '',
                'buyer' => $payload['buyer'] ?? '',
                'domain' => $domain,
                'installation_id' => $this->storage->installationHash(),
            ]);

            if (empty($response['success'])) {
                $message = (string) ($response['message'] ?? 'License revoked or invalid.');

                if ($this->shouldPurgeLocalLicenseOnRemoteFailure($response, $message)) {
                    $this->deactivate();
                    Cache::forget($this->cacheKey());

                    return (object) [
                        'valid' => false,
                        'message' => $message,
                        'payload' => null,
                    ];
                }

                if ($this->isDomainAccessDeniedMessage($message)) {
                    $this->stripDomainFromLocalLicense($payload, DomainNormalizer::fromRequest());

                    return (object) [
                        'valid' => false,
                        'message' => $message,
                        'payload' => null,
                    ];
                }

                return (object) [
                    'valid' => true,
                    'message' => 'Using locally stored license (remote check: '.$message.').',
                    'payload' => $payload,
                ];
            }

            if (! empty($response['license_token'])) {
                $fresh = $this->payloadFromToken((string) $response['license_token']);
                $this->persistPayload($fresh);
                $payload = $fresh;
            }

            Cache::put($this->cacheKey(), time(), now()->addDays(30));

            $check = $this->validateLocal();

            return $check->valid
                ? (object) ['valid' => true, 'message' => 'Remote verification OK.', 'payload' => $check->payload]
                : (object) ['valid' => false, 'message' => $check->message, 'payload' => null];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $graceHours = (int) config('license.grace_hours', 0);
            $storedAt = (int) ($payload['stored_at'] ?? 0);
            if ($graceHours > 0 && $storedAt > 0 && (time() - $storedAt) < ($graceHours * 3600)) {
                return (object) [
                    'valid' => true,
                    'message' => 'Remote server unreachable; grace period active.',
                    'payload' => $payload,
                ];
            }

            return (object) [
                'valid' => true,
                'message' => 'Could not verify license with server; using local license.',
                'payload' => $payload,
            ];
        } catch (\Throwable $e) {
            return (object) [
                'valid' => true,
                'message' => 'Remote verification error; using local license.',
                'payload' => $payload,
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function shouldPurgeLocalLicenseOnRemoteFailure(array $response, string $message): bool
    {
        $status = strtolower((string) ($response['license_status'] ?? $response['status'] ?? ''));

        if (in_array($status, ['suspended', 'revoked'], true)) {
            return true;
        }

        return (bool) preg_match('/\b(suspended|revoked)\b/i', $message);
    }

    private function isDomainAccessDeniedMessage(string $message): bool
    {
        return (bool) preg_match(
            '/not licensed|was removed|domain mismatch|maximum number of domains|invalid domain/i',
            $message
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function stripDomainFromLocalLicense(array $payload, ?string $domain): void
    {
        if ($domain === null) {
            $this->deactivate();

            return;
        }

        $allowed = $payload['domains'] ?? [];
        $remaining = array_values(array_filter(
            $allowed,
            fn ($d) => ! DomainNormalizer::domainsMatch((string) $d, $domain)
        ));

        if ($remaining === []) {
            $this->deactivate();

            return;
        }

        $payload['domains'] = $remaining;
        $this->persistPayload($payload);
        Cache::forget($this->cacheKey());
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            'suspended' => 'This license has been suspended. Contact the seller.',
            'revoked' => 'This license has been revoked.',
            default => 'License is not active.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromToken(string $token): array
    {
        if ($token === '') {
            throw new RuntimeException('Empty license token.');
        }

        [$payload, $signature] = $this->signer->decodeToken($token);

        if (! $this->signer->verify($this->signer->signingPayload($payload), $signature)) {
            throw new RuntimeException('License token signature is invalid.');
        }

        $this->assertProductCode($payload);

        $payload = $this->signer->signingPayload($payload);
        $payload['signature'] = $this->signer->sign($payload);

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function persistPayload(array $payload): void
    {
        if (! isset($payload['signature'])) {
            $payload['signature'] = $this->signer->sign($this->signingPayload($payload));
        }

        $this->storage->write($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function signingPayload(array $payload): array
    {
        return $this->signer->signingPayload($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertProductCode(array $payload): void
    {
        $code = (string) ($payload['product_code'] ?? '');
        $allowed = config('license.allowed_product_codes', [config('license.product_code')]);
        if (! is_array($allowed)) {
            $allowed = [config('license.product_code')];
        }
        if (! in_array($code, $allowed, true)) {
            throw new RuntimeException('License is for a different product.');
        }
    }

    private function remoteProductCode(): string
    {
        $remote = config('license.remote_product_code');

        return is_string($remote) && $remote !== ''
            ? $remote
            : (string) config('license.product_code');
    }

    private function cacheKey(): string
    {
        return 'license.last_remote_verify.'.$this->storage->installationHash();
    }

    private function commandMatches(string $command, string $pattern): bool
    {
        $pattern = str_replace('*', '.*', preg_quote($pattern, '#'));

        return (bool) preg_match('#'.$pattern.'#i', $command);
    }
}

