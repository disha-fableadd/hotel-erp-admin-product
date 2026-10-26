<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'einvoice_public_key')) {
                $table->longText('einvoice_public_key')->nullable()->after('einvoice_client_secret');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'einvoice_public_key')) {
                $table->dropColumn('einvoice_public_key');
            }
        });
    }
};
