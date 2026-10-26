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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'gst_number')) {
                    $table->string('gst_number', 20)->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'pan_number')) {
                    $table->string('pan_number', 10)->nullable()->after('gst_number');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('users', 'gst_number')) {
                    $columns[] = 'gst_number';
                }
                if (Schema::hasColumn('users', 'pan_number')) {
                    $columns[] = 'pan_number';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
