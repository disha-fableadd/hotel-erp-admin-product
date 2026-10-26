<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

try {
    $columns = Schema::getColumnListing('facebook_app_configurations');
    echo "Columns in facebook_app_configurations:\n";
    print_r($columns);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
