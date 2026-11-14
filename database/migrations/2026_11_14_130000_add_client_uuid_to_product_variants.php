<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        if (! Schema::hasColumn('product_variants', 'client_uuid')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->string('client_uuid', 100)->nullable()->after('id')->index();
            });

            $rows = DB::table('product_variants')->whereNull('client_uuid')->orWhere('client_uuid', '')->select('id')->get();
            foreach ($rows as $row) {
                DB::table('product_variants')->where('id', $row->id)->update([
                    'client_uuid' => (string) Str::uuid(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'client_uuid')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('client_uuid');
            });
        }
    }
};
