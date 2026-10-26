<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $templates = \App\Models\WhatsAppMessageTemplate::all(['id', 'name', 'use_for_template', 'on_off', 'components']);
    echo "Templates:\n";
    foreach ($templates as $t) {
        echo "ID: {$t->id}, Name: {$t->name}, Use For: {$t->use_for_template}, On: {$t->on_off}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
