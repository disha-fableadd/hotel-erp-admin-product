<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'invoice_customization')) {
            return;
        }

        $rows = DB::table('settings')->select('id', 'invoice_customization')->get();
        foreach ($rows as $row) {
            $raw = $row->invoice_customization;
            if ($raw === null || $raw === '') {
                continue;
            }

            $inv = is_array($raw) ? $raw : json_decode((string) $raw, true);
            if (! is_array($inv)) {
                continue;
            }

            // Hide shop phone on bills (Ph: / Mob No) unless re-enabled in Customize Invoice.
            $inv['show_shop_phone'] = false;

            DB::table('settings')->where('id', $row->id)->update([
                'invoice_customization' => json_encode($inv),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Intentionally empty — do not re-enable shop phone on rollback.
    }
};
