<?php

namespace App\Services\License;

use App\Models\ProductLicense;
use App\Models\ProductLicenseDomain;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LicenseServerService
{
    public function __construct(
        private readonly LicenseSigner $signer
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function activate(array $data): array
    {
        $productCode = (string) ($data['product_code'] ?? '');
        $this->assertProductCode($productCode);

        $license = $this->findLicense((string) $data['purchase_code'], (string) $data['buyer']);
        if (! $license) {
            return $this->fail('Invalid purchase code or buyer username.');
        }

        if (! $license->isActive()) {
            return $this->fail('This license has been '.$license->status.'.');
        }

        $domain = DomainNormalizer::normalize($data['domain'] ?? null);
        if ($domain === null) {
            return $this->fail('Invalid domain.', 422);
        }

        if ($license->license_type === 'development' && ! DomainNormalizer::isLocalhost($domain)) {
            return $this->fail('Development licenses only work on localhost.');
        }

        return DB::transaction(function () use ($license, $domain, $data) {
            $installationId = (string) $data['installation_id'];
            $result = $this->registerDomainSlot($license, (string) ($data['product_code'] ?? ''), $domain, $installationId);

            if (is_array($result)) {
                return $result;
            }

            return $this->success('License activated.', $license->fresh('domains'));
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function verify(array $data): array
    {
        $this->assertProductCode($data['product_code'] ?? '');

        $license = ProductLicense::where('purchase_code_hash', $data['purchase_code_hash'] ?? '')
            ->where('buyer', $data['buyer'] ?? '')
            ->first();

        if (! $license) {
            return $this->fail('Invalid purchase code or buyer username.');
        }

        if ($license->status === 'suspended') {
            return $this->fail('This license has been suspended. Contact the seller.');
        }

        if ($license->status === 'revoked') {
            return $this->fail('This license has been revoked.');
        }

        if (! $license->isActive()) {
            return $this->fail('License is not active.');
        }

        $installationId = (string) ($data['installation_id'] ?? '');
        $domain = DomainNormalizer::normalize($data['domain'] ?? '');

        $binding = $this->findDomainBinding($license, $installationId, $domain);

        if (! $binding) {
            return $this->fail('This domain is not licensed or was removed. Please activate again.');
        }

        if ($domain !== null && ! DomainNormalizer::domainsMatch($binding->domain, $domain)) {
            return $this->fail('Domain mismatch.');
        }

        $binding->update([
            'product_code' => (string) ($data['product_code'] ?? ($binding->product_code ?? null)),
            'last_verified_at' => now(),
        ]);

        return $this->success('License valid.', $license->fresh('domains'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function addDomain(array $data): array
    {
        $productCode = (string) ($data['product_code'] ?? '');
        $this->assertProductCode($productCode);

        $license = $this->findLicense((string) $data['purchase_code'], (string) $data['buyer']);
        if (! $license || ! $license->isActive()) {
            return $this->fail('License invalid.');
        }

        if ($license->max_domains <= 1 && $license->license_type !== 'extended') {
            return $this->fail('This license allows only one domain. Increase max domains or use extended type.');
        }

        $domain = DomainNormalizer::normalize($data['domain'] ?? null);
        if ($domain === null) {
            return $this->fail('Invalid domain.', 422);
        }

        $installationId = (string) $data['installation_id'];

        if (! $license->domains()->where('installation_id', $installationId)->exists()
            && ! $license->domains()->where('installation_id', $this->domainInstallationId($license, $installationId, $domain))->exists()) {
            return $this->fail('Unknown installation. Activate the first domain before adding more.');
        }

        return DB::transaction(function () use ($license, $productCode, $domain, $installationId) {
            $result = $this->registerDomainSlot($license, $productCode, $domain, $installationId);

            if (is_array($result)) {
                return $result;
            }

            return $this->success('Domain added.', $license->fresh('domains'));
        });
    }

    /**
     * Register or refresh one domain slot. Returns fail array or null on success.
     *
     * @return array<string, mixed>|null
     */
    private function registerDomainSlot(ProductLicense $license, string $productCode, string $domain, string $installationId): ?array
    {
        $byDomain = $license->domains()->where('domain', $domain)->first();

        if ($byDomain) {
            $byDomain->update([
                'product_code' => $productCode !== '' ? $productCode : ($byDomain->product_code ?? null),
                'installation_id' => $this->domainInstallationId($license, $installationId, $domain),
                'last_verified_at' => now(),
            ]);

            return null;
        }

        if ($license->max_domains <= 1) {
            $existing = $license->domains()->where('installation_id', $installationId)->first();
            if ($existing) {
                $existing->update([
                    'product_code' => $productCode !== '' ? $productCode : ($existing->product_code ?? null),
                    'domain' => $domain,
                    'last_verified_at' => now(),
                ]);

                return null;
            }
        }

        if ($license->domains()->count() >= $license->max_domains) {
            return $this->fail('Maximum number of domains reached for this license.');
        }

        ProductLicenseDomain::create([
            'product_license_id' => $license->id,
            'product_code' => $productCode !== '' ? $productCode : null,
            'domain' => $domain,
            'installation_id' => $this->domainInstallationId($license, $installationId, $domain),
            'activated_at' => now(),
            'last_verified_at' => now(),
        ]);

        return null;
    }

    private function findDomainBinding(
        ProductLicense $license,
        string $installationId,
        ?string $domain
    ): ?ProductLicenseDomain {
        if ($domain !== null) {
            $byDomain = $license->domains()->where('domain', $domain)->first();
            if ($byDomain) {
                return $byDomain;
            }

            $hashed = $license->domains()
                ->where('installation_id', $this->domainInstallationId($license, $installationId, $domain))
                ->first();
            if ($hashed) {
                return $hashed;
            }
        }

        return $license->domains()->where('installation_id', $installationId)->first();
    }

    private function domainInstallationId(ProductLicense $license, string $baseInstallationId, string $domain): string
    {
        if ($license->max_domains <= 1) {
            return $baseInstallationId;
        }

        return hash('sha256', $baseInstallationId.'|'.$domain);
    }

    private function findLicense(string $purchaseCode, string $buyer): ?ProductLicense
    {
        $code = strtoupper(trim($purchaseCode));
        $hash = hash('sha256', $code);

        return ProductLicense::where(function ($query) use ($code, $hash) {
            $query->where('purchase_code', $code)->orWhere('purchase_code_hash', $hash);
        })
            ->where('buyer', trim($buyer))
            ->first();
    }

    private function tokenForLicense(ProductLicense $license): string
    {
        $license->loadMissing('domains');

        $domains = $license->domains->pluck('domain')->all();
        $payload = [
            'product_code' => config('license.product_code'),
            'purchase_code_hash' => hash('sha256', strtoupper(trim($license->purchase_code))),
            'buyer' => $license->buyer,
            'license_type' => $license->license_type,
            'license_status' => $license->status,
            'domains' => array_values(array_unique(array_filter($domains))),
            'max_domains' => $license->max_domains,
            'activated_at' => time(),
            'expires_at' => null,
        ];

        return $this->signer->encodeToken($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function success(string $message, ProductLicense $license): array
    {
        return [
            'success' => true,
            'message' => $message,
            'license_token' => $this->tokenForLicense($license),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fail(string $message, int $httpStatus = 403): array
    {
        return [
            'success' => false,
            'message' => $message,
            '_http_status' => $httpStatus,
        ];
    }

  /**
     * @return list<string>
     */
    private function allowedProductCodes(): array
    {
        $codes = config('license.allowed_product_codes', []);
        if (! is_array($codes) || $codes === []) {
            $codes = [config('license.product_code')];
        }

        return array_values(array_unique(array_filter(array_map('strval', $codes))));
    }

    private function assertProductCode(string $productCode): void
    {
        if (! in_array($productCode, $this->allowedProductCodes(), true)) {
            throw new RuntimeException('Unknown product.');
        }
    }
}

