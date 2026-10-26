<?php

namespace App\Console\Commands;

use App\Models\ProductLicense;
use Illuminate\Console\Command;

class LicenseRegisterPurchaseCommand extends Command
{
    protected $signature = 'license:register-purchase
        {purchase_code : CodeCanyon purchase code}
        {buyer : Envato username}
        {--type=regular : regular, extended, or development}
        {--max-domains= : Override max domains}
        {--item-id= : Envato item ID}';

    protected $description = 'Author: register a purchase code in the license server database';

    public function handle(): int
    {
        $code = strtoupper(trim($this->argument('purchase_code')));
        $buyer = trim($this->argument('buyer'));
        $type = $this->option('type');

        if (! in_array($type, ['regular', 'extended', 'development'], true)) {
            $this->error('Invalid license type.');

            return self::FAILURE;
        }

        $maxDomains = $this->option('max-domains')
            ? (int) $this->option('max-domains')
            : (int) config('license.domain_limits.'.$type, 1);

        $license = ProductLicense::updateOrCreate(
            ['purchase_code' => $code],
            [
                'purchase_code_hash' => hash('sha256', $code),
                'buyer' => $buyer,
                'license_type' => $type,
                'max_domains' => $maxDomains,
                'status' => 'active',
                'envato_item_id' => $this->option('item-id'),
            ]
        );

        $this->info("Registered license #{$license->id} ({$type}, max {$maxDomains} domain(s)).");

        return self::SUCCESS;
    }
}

