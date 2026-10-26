<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $templates = \App\Models\WhatsAppMessageTemplate::all();
    foreach ($templates as $t) {
        if (strpos(json_encode($t->components), 'Your subscription order') !== false) {
            echo "MATCHED TEMPLATE: " . $t->name . "\n";
            echo json_encode($t->components, JSON_PRETTY_PRINT) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
