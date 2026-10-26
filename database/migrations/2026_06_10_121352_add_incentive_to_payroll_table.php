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
        Schema::table('payroll', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll', 'incentive')) {
                $table->decimal('incentive', 8, 2)->default(0.00)->nullable()->after('tax_deduction');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $table->dropColumn('incentive');
        });
    }
};
