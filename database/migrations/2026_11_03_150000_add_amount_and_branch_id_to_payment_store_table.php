<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('payment_store')) {
            Schema::table('payment_store', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_store', 'client_uuid')) {
                    $table->string('client_uuid', 100)->nullable()->after('id')->index('idx_payment_store_client_uuid');
                }

                if (!Schema::hasColumn('payment_store', 'amount')) {
                    $table->decimal('amount', 12, 2)->nullable()->default(0.00)->after('payment_amount');
                }

                if (!Schema::hasColumn('payment_store', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->default(1)->after('status');
                }
            });

            // Backfill existing rows: sync amount with payment_amount
            try {
                if (Schema::hasColumn('payment_store', 'amount') && Schema::hasColumn('payment_store', 'payment_amount')) {
                    DB::table('payment_store')
                        ->where(function ($q) {
                            $q->whereNull('amount')->orWhere('amount', 0.00);
                        })
                        ->where('payment_amount', '>', 0)
                        ->update(['amount' => DB::raw('payment_amount')]);
                }
            } catch (\Throwable $e) {
                // Ignore backfill errors
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payment_store')) {
            Schema::table('payment_store', function (Blueprint $table) {
                if (Schema::hasColumn('payment_store', 'amount')) {
                    $table->dropColumn('amount');
                }
                if (Schema::hasColumn('payment_store', 'branch_id')) {
                    $table->dropColumn('branch_id');
                }
            });
        }
    }
};

