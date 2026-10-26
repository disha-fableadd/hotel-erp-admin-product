<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BasicSettingsSeeder extends Seeder
{
    /**
     * Seed basic settings, company rules, and location settings.
     */
    public function run(): void
    {
        // 1. Ensure Main Branch exists
        if (Schema::hasTable('branches')) {
            DB::table('branches')->updateOrInsert(
                ['id' => 1],
                [
                    'name' => 'Main Branch',
                    'code' => 'BR001',
                    'address' => '5001, Ascon Plaza , Adjana Surat',
                    'phone' => '1234567890',
                    'email' => 'fabinfo@gmail.com',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Seed Shop Settings & Dashboard Settings
        if (Schema::hasTable('settings')) {
            DB::table('settings')->updateOrInsert(
                ['id' => 1],
                [
                    'branch_id' => 1,
                    'name' => 'Fablead Developer & Technolab',
                    'email' => 'fabinfo@gmail.com',
                    'phone' => '1234567890',
                    'gst_num' => 'GST1234',
                    'bank_name' => 'SBI Bank',
                    'branch' => 'Adajan',
                    'ac_no' => '342234234',
                    'ifsc_code' => 'IC123452101',
                    'address' => '5001, Ascon Plaza , Adjana Surat',
                    'currency_position' => 'left',
                    'currency_symbol' => '₹',
                    'low_stock' => 10,
                    'invoice_size' => 'big',
                    'send_mail' => 0,
                    'customer_whatsapp_message' => 1,
                    'admin_whatsapp_message' => 1,
                    'working_hours' => '12.00',
                    'grace_period' => '15',
                    'lunch_break' => '00:00:00',
                    'open_time' => '10:00:00',
                    'close_time' => '23:59:00',
                    'location_check_on_login' => 1,

                    // Dashboard Toggles
                    'show_crm_dashboard' => 1,
                    'show_crm_lead_pipeline' => 1,
                    'show_crm_conversion' => 1,
                    'show_crm_followup_load' => 1,
                    'show_crm_meeting_momentum' => 1,
                    'show_crm_lead_status_mix' => 1,
                    'show_crm_activity_trend' => 1,
                    'show_crm_pipeline_quality' => 1,
                    'show_crm_recent_leads' => 1,
                    'show_crm_next_7_days' => 1,

                    // HR Dashboard
                    'show_hr_dashboard' => 1,
                    'show_hr_staff_strength' => 1,
                    'show_hr_active_staff' => 1,
                    'show_hr_monthly_attendance' => 1,
                    'show_hr_personal_progress' => 1,
                    'show_hr_attendance_pattern' => 1,
                    'show_hr_salary_payroll_trend' => 1,
                    'show_hr_payroll_snapshot' => 1,
                    'show_hr_attendance_watch' => 1,
                    'show_hr_payroll_status' => 1,

                    // ERP Dashboard
                    'show_erp_dashboard' => 1,
                    'show_erp_total_sales' => 1,
                    'show_erp_total_purchase' => 1,
                    'show_erp_total_expense' => 1,
                    'show_erp_sales_invoice_count' => 1,
                    'show_erp_purchase_invoice_count' => 1,
                    'show_erp_customers_count' => 1,
                    'show_erp_vendors_count' => 1,
                    'show_erp_sales_chart' => 1,
                    'show_erp_purchase_chart' => 1,
                    'show_erp_recent_sales' => 1,
                    'show_erp_recent_purchases' => 1,
                    'show_erp_recent_products' => 1,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 3. Seed Company Rules
        if (Schema::hasTable('company_rules')) {
            DB::table('company_rules')->updateOrInsert(
                ['id' => 1],
                [
                    'enable_payroll' => 1,
                    'payroll_type' => 'monthly',
                    'working_hours_per_day' => 12.00,
                    'include_holidays_in_working_days' => 1,
                    'half_day_hours' => 5,
                    'sunday_off' => 1,
                    'sunday_pay_type' => 'unpaid',
                    'saturday_off_enabled' => 1,
                    'saturday_off_type' => 'all',
                    'saturday_off_pattern' => '0,0',
                    'saturday_half_day_enabled' => 0,
                    'saturday_half_day_pattern' => '0',
                    'saturday_pay_type' => 'regular',
                    'yearly_holidays' => 0,
                    'enable_tax' => 1,
                    'tax_type' => 'fixed',
                    'salary_above_tax' => '0.00',
                    'tax' => 0,
                    'lunch_break' => '00:00:00',
                    'start_time' => '10:00:00',
                    'half_time' => '05:00:00',
                    'end_time' => '23:59:00',
                    'grace_period' => 15,
                    'enable_overtime' => 0,
                    'overtime_rate_type' => 'multiplier',
                    'overtime_multiplier' => 1.50,
                    'min_overtime_count_in_minutes' => 30,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 4. Seed Location Settings
        if (Schema::hasTable('location_settings')) {
            DB::table('location_settings')->updateOrInsert(
                ['id' => 1],
                [
                    'latitude' => 21.1976192,
                    'longitude' => 72.8465408,
                    'radius' => 99.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
