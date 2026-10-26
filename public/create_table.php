<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    if (!Schema::hasTable('facebook_app_configurations')) {
        Schema::create('facebook_app_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('facebook_app_id');
            $table->text('facebook_app_secret');
            $table->string('phone_number_id');
            $table->string('whatsapp_business_account_id');
            $table->text('access_token');
            $table->string('webhook_url')->nullable();
            $table->boolean('isDeleted')->default(0);
            $table->timestamps();
        });
        echo "Table created successfully!";
    } else {
        echo "Table already exists!";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
