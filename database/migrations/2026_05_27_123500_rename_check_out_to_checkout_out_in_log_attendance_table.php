<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('log_attendance') && Schema::hasColumn('log_attendance', 'check_out')) {
            DB::statement('ALTER TABLE log_attendance CHANGE COLUMN check_out checkout_out TIME NULL;');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('log_attendance') && Schema::hasColumn('log_attendance', 'checkout_out')) {
            DB::statement('ALTER TABLE log_attendance CHANGE COLUMN checkout_out check_out TIME NULL;');
        }
    }
};
