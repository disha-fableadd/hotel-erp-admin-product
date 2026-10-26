<?php

namespace App\Console\Commands;

use App\Services\License\LicenseManager;
use App\Services\License\LicenseSigner;
use Illuminate\Console\Command;

class LicenseGenerateCommand extends Command
{
    protected $signature = 'license:generate
        {purchase_code : Purchase code}
        {buyer : Envato username}
        {--type=regular : regular, extended, or development}
        {--domains= : Comma-separated domains}
        {--expires= : Optional expiry date Y-m-d}';

    protected $description = 'Author: generate signed offline license key';

    public function handle(LicenseManager $licenses, LicenseSigner $signer): int
    {
        $type = $this->option('type');
        if (! in_array($type, ['regular', 'extended', 'development'], true)) {
            $this->error('Invalid license type.');

            return self::FAILURE;
        }

        $domains = array_filter(array_map('trim', explode(',', (string) $this->option('domains'))));
        if ($domains === []) {
            $domains = [$this->ask('Primary domain (e.g. erp.client.com)')];
        }

        $expires = null;
        if ($this->option('expires')) {
            $expires = strtotime($this->option('expires').' 23:59:59') ?: null;
        }

        try {
            $payload = $licenses->issueToken(
                $this->argument('purchase_code'),
                $this->argument('buyer'),
                $type,
                $domains,
                $expires
            );

            $token = $signer->encodeToken($payload);

            $this->newLine();
            $this->line('<fg=green>License key (give to buyer):</>');
            $this->line($token);
            $this->newLine();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}

