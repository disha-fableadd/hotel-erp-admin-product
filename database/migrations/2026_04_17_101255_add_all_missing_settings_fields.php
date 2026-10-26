<?php
/*
 * Created by Antigravity
 * Date: 2026-04-17
 * Purpose: Add missing base columns to settings table that were causing 'Column not found' errors.
 */

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
        Schema::table('settings', function (Blueprint $table) {
            // Base columns often missing
            if (!Schema::hasColumn('settings', 'branch_id')) $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            if (!Schema::hasColumn('settings', 'gst_num')) $table->string('gst_num')->nullable();
            if (!Schema::hasColumn('settings', 'low_stock')) $table->string('low_stock', 50)->nullable();
            if (!Schema::hasColumn('settings', 'state_code')) $table->string('state_code', 20)->nullable();
            if (!Schema::hasColumn('settings', 'contact_person1')) $table->string('contact_person1')->nullable();
            if (!Schema::hasColumn('settings', 'contact_person2')) $table->string('contact_person2')->nullable();
            if (!Schema::hasColumn('settings', 'person_name1')) $table->string('person_name1')->nullable();
            if (!Schema::hasColumn('settings', 'person_name2')) $table->string('person_name2')->nullable();
            if (!Schema::hasColumn('settings', 'pan_no')) $table->string('pan_no', 20)->nullable();
            if (!Schema::hasColumn('settings', 'bank_name')) $table->string('bank_name')->nullable();
            if (!Schema::hasColumn('settings', 'branch')) $table->string('branch')->nullable();
            if (!Schema::hasColumn('settings', 'ac_no')) $table->string('ac_no')->nullable();
            if (!Schema::hasColumn('settings', 'ifsc_code')) $table->string('ifsc_code')->nullable();
            if (!Schema::hasColumn('settings', 'cin_no')) $table->string('cin_no')->nullable();
            if (!Schema::hasColumn('settings', 'invoice_size')) $table->string('invoice_size', 20)->nullable();
            
            // Rules
            if (!Schema::hasColumn('settings', 'working_hours')) $table->string('working_hours')->nullable();
            if (!Schema::hasColumn('settings', 'sunday_off')) $table->string('sunday_off')->nullable();
            if (!Schema::hasColumn('settings', 'grace_period')) $table->string('grace_period')->nullable();
            if (!Schema::hasColumn('settings', 'lunch_break')) $table->string('lunch_break')->nullable();
            if (!Schema::hasColumn('settings', 'open_time')) $table->time('open_time')->nullable();
            if (!Schema::hasColumn('settings', 'close_time')) $table->time('close_time')->nullable();
            if (!Schema::hasColumn('settings', 'qr_code')) $table->string('qr_code')->nullable();

            // E-Way Bill columns
            if (!Schema::hasColumn('settings', 'eway_gstin')) $table->string('eway_gstin', 20)->nullable();
            if (!Schema::hasColumn('settings', 'eway_business_name')) $table->string('eway_business_name')->nullable();
            if (!Schema::hasColumn('settings', 'eway_pan')) $table->string('eway_pan', 20)->nullable();
            if (!Schema::hasColumn('settings', 'eway_state')) $table->string('eway_state')->nullable();
            if (!Schema::hasColumn('settings', 'eway_state_code')) $table->string('eway_state_code', 10)->nullable();
            if (!Schema::hasColumn('settings', 'eway_address1')) $table->text('eway_address1')->nullable();
            if (!Schema::hasColumn('settings', 'eway_address2')) $table->text('eway_address2')->nullable();
            if (!Schema::hasColumn('settings', 'eway_city')) $table->string('eway_city')->nullable();
            if (!Schema::hasColumn('settings', 'eway_pincode')) $table->string('eway_pincode', 10)->nullable();
            if (!Schema::hasColumn('settings', 'eway_username')) $table->string('eway_username')->nullable();
            if (!Schema::hasColumn('settings', 'eway_password')) $table->string('eway_password')->nullable();
            if (!Schema::hasColumn('settings', 'eway_app_key')) $table->string('eway_app_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback for safety as these are often production columns
    }
};
