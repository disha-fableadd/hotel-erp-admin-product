<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'send_mail')) {
                $table->boolean('send_mail')->default(1)->nullable();
            }
            if (!Schema::hasColumn('settings', 'customer_whatsapp_message')) {
                $table->boolean('customer_whatsapp_message')->default(1)->nullable();
            }
            if (!Schema::hasColumn('settings', 'admin_whatsapp_message')) {
                $table->boolean('admin_whatsapp_message')->default(1)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'send_mail',
                'customer_whatsapp_message',
                'admin_whatsapp_message'
            ]);
        });
    }
};
