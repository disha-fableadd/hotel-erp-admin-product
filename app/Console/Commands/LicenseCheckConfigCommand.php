<?php

namespace App\Console\Commands;

use App\Services\License\LicenseSigner;
use Illuminate\Console\Command;

class LicenseCheckConfigCommand extends Command
{
    protected $signature = 'license:check-config';

    protected $description = 'Show license env settings (secret length only) and test local HMAC round-trip';

    public function handle(LicenseSigner $signer): int
    {
        $this->line('License enforcement: '.(config('license.enabled') ? 'ON (required)' : 'off'));
        $this->line('LICENSE_PRODUCT_CODE: '.config('license.product_code'));
        $this->line('LICENSE_SERVER_URL: '.config('license.server_url'));

        try {
            $secret = $signer->secret();
            $this->info('LICENSE_SECRET: set ('.strlen($secret).' characters)');
        } catch (\Throwable $e) {
            $this->error('LICENSE_SECRET: '.$e->getMessage());

            return self::FAILURE;
        }

        $payload = [
            'product_code' => config('license.product_code'),
            'purchase_code_hash' => hash('sha256', 'TEST'),
            'buyer' => 'test',
            'license_type' => 'regular',
            'license_status' => 'active',
            'domains' => ['example.com'],
            'max_domains' => 1,
            'activated_at' => time(),
            'expires_at' => null,
        ];

        $token = $signer->encodeToken($payload);
        [$decoded, $sig] = $signer->decodeToken($token);

        if ($signer->verify($signer->signingPayload($decoded), $sig)) {
            $this->info('Local token sign/verify: OK');
        } else {
            $this->error('Local token sign/verify: FAILED');

            return self::FAILURE;
        }

        $this->newLine();
        $this->comment('Buyer ERP and license server must use the same LICENSE_SECRET and LICENSE_PRODUCT_CODE.');
        $this->comment('After changing .env on production, run: php artisan config:clear');

        return self::SUCCESS;
    }
}

