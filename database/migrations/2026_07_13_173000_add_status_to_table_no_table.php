<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a real string status column for table cards.
     *
     * We keep the legacy integer table_status column for backwards
     * compatibility, but the UI should now read the human-friendly
     * status value from the database.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('table_no', 'status')) {
            Schema::table('table_no', function (Blueprint $table) {
                $table->string('status', 20)->default('blank')->index()->after('table_status');
            });
        }

        DB::table('table_no')
            ->update([
                'status' => DB::raw(
                    "CASE
                        WHEN table_status = 1 THEN 'running'
                        WHEN table_status = 2 THEN 'reserved'
                        ELSE 'blank'
                    END"
                ),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('table_no', 'status')) {
            Schema::table('table_no', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
