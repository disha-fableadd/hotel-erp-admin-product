<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_rules', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->boolean('enable_payroll')->default(1);
            $table->enum('payroll_type', ['monthly', 'daily', 'hourly'])->default('monthly');
            $table->decimal('working_hours_per_day', 10, 2)->default(0.15);
            $table->tinyInteger('include_holidays_in_working_days')->default(1);
            $table->integer('half_day_hours')->nullable();
            $table->boolean('sunday_off')->default(1);
            $table->enum('sunday_pay_type', ['unpaid', 'paid', 'double'])->default('unpaid');
            $table->boolean('saturday_off_enabled')->default(1);
            $table->enum('saturday_off_type', ['all', 'alternate', 'custom', 'alternate-even', 'alternate-odd'])->nullable();
            $table->string('saturday_off_pattern', 255)->nullable();
            $table->tinyInteger('saturday_half_day_enabled')->default(0);
            $table->string('saturday_half_day_pattern', 10)->default('0');
            $table->enum('saturday_pay_type', ['regular', 'overtime', 'double'])->nullable();
            $table->integer('yearly_holidays')->nullable();
            $table->boolean('enable_tax')->default(1);
            $table->enum('tax_type', ['fixed', 'percentage', 'slab'])->default('fixed');
            $table->string('salary_above_tax', 100)->default('12000');
            $table->integer('tax')->nullable();
            $table->time('lunch_break')->default('00:30:00');
            $table->time('start_time')->default('09:30:00');
            $table->time('half_time')->default('00:01:00')->comment('Half Day time');
            $table->time('end_time')->default('07:00:00');
            $table->integer('grace_period')->default(0);
            $table->boolean('enable_overtime')->default(1);
            $table->enum('overtime_rate_type', ['multiplier', 'fixed'])->default('fixed');
            $table->decimal('overtime_multiplier', 10, 2)->default(0.00);
            $table->integer('min_overtime_count_in_minutes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_rules');
    }
};
