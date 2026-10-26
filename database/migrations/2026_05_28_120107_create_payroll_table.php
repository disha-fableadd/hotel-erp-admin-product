<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->integer('leave_type')->nullable();
            $table->float('remaining_paid_leaves')->nullable();
            $table->float('used_paid_leaves')->nullable();
            $table->string('month_year', 7)->nullable();
            $table->integer('total_leaves')->nullable();
            $table->integer('total_half_day')->default(0);
            $table->integer('total_paid_leaves');
            $table->decimal('salary_amount', 10, 0);
            $table->decimal('tax_deduction', 10, 0)->nullable();
            $table->decimal('bonuses', 10, 0)->default(0);
            $table->decimal('net_salary', 10, 2)->nullable();
            $table->string('acc_number', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('ifsc_code', 20)->nullable();
            $table->string('acc_in_name', 100)->nullable();
            $table->string('branch_name', 100)->nullable();
            $table->string('branch_code', 50)->nullable();
            $table->date('payment_date');
            $table->string('payment_status', 100);
            $table->integer('created_by');
            $table->string('worked_hours', 20)->nullable();
            $table->string('overtime_pay', 20)->nullable();
            $table->string('total_overtime_hours', 20)->nullable();
            $table->string('advance_payment', 225)->nullable();
            $table->decimal('salary_deduction', 45, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
