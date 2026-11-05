<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'default_payment_method')) {
                $after = Schema::hasColumn('settings', 'default_gst_option')
                    ? 'default_gst_option'
                    : (Schema::hasColumn('settings', 'allow_item_level_discount') ? 'allow_item_level_discount' : null);
                if ($after) {
                    $table->string('default_payment_method', 30)->default('cash')->after($after);
                } else {
                    $table->string('default_payment_method', 30)->default('cash');
                }
            }
            if (! Schema::hasColumn('settings', 'default_paid_type')) {
                $after = Schema::hasColumn('settings', 'default_payment_method')
                    ? 'default_payment_method'
                    : null;
                if ($after) {
                    $table->string('default_paid_type', 20)->default('fully')->after($after);
                } else {
                    $table->string('default_paid_type', 20)->default('fully');
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'default_paid_type')) {
                $table->dropColumn('default_paid_type');
            }
            if (Schema::hasColumn('settings', 'default_payment_method')) {
                $table->dropColumn('default_payment_method');
            }
        });
    }
};
