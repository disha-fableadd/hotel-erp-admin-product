<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Settings: e-Invoice API credentials ───────────────────────────
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'einvoice_enabled')) {
                $table->tinyInteger('einvoice_enabled')->default(0)->after('eway_app_key');
            }
            if (!Schema::hasColumn('settings', 'einvoice_api_url')) {
                $table->string('einvoice_api_url')->nullable()->after('einvoice_enabled');
            }
            if (!Schema::hasColumn('settings', 'einvoice_username')) {
                $table->string('einvoice_username')->nullable()->after('einvoice_api_url');
            }
            if (!Schema::hasColumn('settings', 'einvoice_password')) {
                $table->string('einvoice_password')->nullable()->after('einvoice_username');
            }
            if (!Schema::hasColumn('settings', 'einvoice_gstin')) {
                $table->string('einvoice_gstin', 20)->nullable()->after('einvoice_password');
            }
            if (!Schema::hasColumn('settings', 'einvoice_client_id')) {
                $table->string('einvoice_client_id')->nullable()->after('einvoice_gstin');
            }
            if (!Schema::hasColumn('settings', 'einvoice_client_secret')) {
                $table->string('einvoice_client_secret')->nullable()->after('einvoice_client_id');
            }
        });

        // ── Orders: IRN / QR code storage ─────────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'irn')) {
                $table->string('irn', 128)->nullable()->after('einvoice_generated_at');
            }
            if (!Schema::hasColumn('orders', 'irn_ack_no')) {
                $table->string('irn_ack_no', 64)->nullable()->after('irn');
            }
            if (!Schema::hasColumn('orders', 'irn_ack_date')) {
                $table->string('irn_ack_date', 32)->nullable()->after('irn_ack_no');
            }
            if (!Schema::hasColumn('orders', 'signed_qr_code')) {
                $table->longText('signed_qr_code')->nullable()->after('irn_ack_date');
            }
            if (!Schema::hasColumn('orders', 'einvoice_response')) {
                $table->longText('einvoice_response')->nullable()->after('signed_qr_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $cols = ['einvoice_enabled','einvoice_api_url','einvoice_username',
                     'einvoice_password','einvoice_gstin','einvoice_client_id','einvoice_client_secret'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('settings', $col)) $table->dropColumn($col);
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $cols = ['irn','irn_ack_no','irn_ack_date','signed_qr_code','einvoice_response'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) $table->dropColumn($col);
            }
        });
    }
};
