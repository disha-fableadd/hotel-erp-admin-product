<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $t = \App\Models\WhatsAppMessageTemplate::where('name', 'order_complete_notification')->first();
    if($t) {
        echo "order_complete_notification:\n";
        echo json_encode($t->components, JSON_PRETTY_PRINT) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
