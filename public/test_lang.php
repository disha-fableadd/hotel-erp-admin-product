<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $template = \App\Models\WhatsAppMessageTemplate::where('name', 'subscription_invoice_pdf')->first();
    echo "Language: " . ($template->language ?? 'null') . "\n";
    echo "Name: " . $template->name . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
