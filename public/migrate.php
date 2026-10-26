<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

try {
    Artisan::call('migrate', ['--force' => true]);
    echo "Migration output: \n" . Artisan::output();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
