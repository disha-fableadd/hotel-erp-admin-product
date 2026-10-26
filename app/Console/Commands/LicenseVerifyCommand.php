<?php

namespace App\Console\Commands;

use App\Services\License\LicenseManager;
use Illuminate\Console\Command;

class LicenseVerifyCommand extends Command
{
    protected $signature = 'license:verify {--force : Force remote re-validation}';

    protected $description = 'Verify stored license integrity and optional remote status';

    public function handle(LicenseManager $licenses): int
    {
        $result = $licenses->validate($this->option('force'));

        if ($result->valid) {
            $this->info($result->message);

            return self::SUCCESS;
        }

        $this->error($result->message);

        return self::FAILURE;
    }
}

