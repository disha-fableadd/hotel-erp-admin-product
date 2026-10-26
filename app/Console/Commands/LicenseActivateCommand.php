<?php

namespace App\Console\Commands;

use App\Services\License\DomainNormalizer;
use App\Services\License\LicenseManager;
use Illuminate\Console\Command;

class LicenseActivateCommand extends Command
{
    protected $signature = 'license:activate
        {--purchase-code= : Envato purchase code}
        {--buyer= : Envato username}
        {--domain= : Domain to bind (defaults from APP_URL)}
        {--key= : Offline license key token}';

    protected $description = 'Activate application license (remote or offline)';

    public function handle(LicenseManager $licenses): int
    {
        try {
            if (config('license.mode') === 'offline') {
                $key = $this->option('key') ?: $this->ask('License key');
                $licenses->activateOffline((string) $key);
            } else {
                $purchase = $this->option('purchase-code') ?: $this->ask('Purchase code');
                $buyer = $this->option('buyer') ?: $this->ask('Envato username');
                $domain = $this->option('domain')
                    ?: DomainNormalizer::normalize(parse_url(config('app.url'), PHP_URL_HOST));

                $licenses->activateRemote((string) $purchase, (string) $buyer, $domain);
            }

            $this->info('License activated successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}

