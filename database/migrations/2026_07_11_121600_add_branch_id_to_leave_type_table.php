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
        Schema::table('leave_type', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_type', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_type', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
