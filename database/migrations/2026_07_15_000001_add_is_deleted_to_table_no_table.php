<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('table_no', 'isDeleted')) {
            Schema::table('table_no', function (Blueprint $table) {
                $table->boolean('isDeleted')->default(0)->index()->after('status');
            });
        }

        DB::table('table_no')->whereNull('isDeleted')->update(['isDeleted' => 0]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('table_no', 'isDeleted')) {
            Schema::table('table_no', function (Blueprint $table) {
                $table->dropColumn('isDeleted');
            });
        }
    }
};
