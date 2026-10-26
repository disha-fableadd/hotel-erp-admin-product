<?php

namespace App\Console\Commands;

use App\Models\ProductLicense;
use Illuminate\Console\Command;

class LicenseListCommand extends Command
{
    protected $signature = 'license:list';

    protected $description = 'List purchase codes registered on this server (author / local dev)';

    public function handle(): int
    {
        $licenses = ProductLicense::with('domains')->orderBy('id')->get();

        if ($licenses->isEmpty()) {
            $this->warn('No purchase codes in database.');
            $this->line('Register one with:');
            $this->line('  php artisan license:register-purchase "YOUR-CODE" "envato_username" --type=development');

            return self::SUCCESS;
        }

        $rows = $licenses->map(fn (ProductLicense $l) => [
            $l->purchase_code,
            $l->buyer,
            $l->license_type,
            $l->max_domains,
            $l->status,
            $l->domains->pluck('domain')->implode(', ') ?: '—',
        ]);

        $this->table(
            ['Purchase code', 'Envato user', 'Type', 'Max domains', 'Status', 'Activated domains'],
            $rows
        );

        return self::SUCCESS;
    }
}

