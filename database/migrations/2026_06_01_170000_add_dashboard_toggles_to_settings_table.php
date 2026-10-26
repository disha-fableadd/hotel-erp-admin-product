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
        Schema::table('settings', function (Blueprint $table) {
            // CRM Dashboard main toggle
            if (! Schema::hasColumn('settings', 'show_crm_dashboard')) {
                $table->boolean('show_crm_dashboard')->default(true)->after('ifsc_code');
            }
            
            // CRM Dashboard subsections
            if (! Schema::hasColumn('settings', 'show_crm_lead_pipeline')) {
                $table->boolean('show_crm_lead_pipeline')->default(true)->after('show_crm_dashboard');
            }
            if (! Schema::hasColumn('settings', 'show_crm_conversion')) {
                $table->boolean('show_crm_conversion')->default(true)->after('show_crm_lead_pipeline');
            }
            if (! Schema::hasColumn('settings', 'show_crm_followup_load')) {
                $table->boolean('show_crm_followup_load')->default(true)->after('show_crm_conversion');
            }
            if (! Schema::hasColumn('settings', 'show_crm_meeting_momentum')) {
                $table->boolean('show_crm_meeting_momentum')->default(true)->after('show_crm_followup_load');
            }
            if (! Schema::hasColumn('settings', 'show_crm_lead_status_mix')) {
                $table->boolean('show_crm_lead_status_mix')->default(true)->after('show_crm_meeting_momentum');
            }
            if (! Schema::hasColumn('settings', 'show_crm_activity_trend')) {
                $table->boolean('show_crm_activity_trend')->default(true)->after('show_crm_lead_status_mix');
            }
            if (! Schema::hasColumn('settings', 'show_crm_pipeline_quality')) {
                $table->boolean('show_crm_pipeline_quality')->default(true)->after('show_crm_activity_trend');
            }
            if (! Schema::hasColumn('settings', 'show_crm_recent_leads')) {
                $table->boolean('show_crm_recent_leads')->default(true)->after('show_crm_pipeline_quality');
            }
            if (! Schema::hasColumn('settings', 'show_crm_next_7_days')) {
                $table->boolean('show_crm_next_7_days')->default(true)->after('show_crm_recent_leads');
            }

            // HR Dashboard main toggle
            if (! Schema::hasColumn('settings', 'show_hr_dashboard')) {
                $table->boolean('show_hr_dashboard')->default(true)->after('show_crm_next_7_days');
            }
            
            // HR Dashboard subsections
            if (! Schema::hasColumn('settings', 'show_hr_staff_strength')) {
                $table->boolean('show_hr_staff_strength')->default(true)->after('show_hr_dashboard');
            }
            if (! Schema::hasColumn('settings', 'show_hr_active_staff')) {
                $table->boolean('show_hr_active_staff')->default(true)->after('show_hr_staff_strength');
            }
            if (! Schema::hasColumn('settings', 'show_hr_monthly_attendance')) {
                $table->boolean('show_hr_monthly_attendance')->default(true)->after('show_hr_active_staff');
            }
            if (! Schema::hasColumn('settings', 'show_hr_personal_progress')) {
                $table->boolean('show_hr_personal_progress')->default(true)->after('show_hr_monthly_attendance');
            }
            if (! Schema::hasColumn('settings', 'show_hr_attendance_pattern')) {
                $table->boolean('show_hr_attendance_pattern')->default(true)->after('show_hr_personal_progress');
            }
            if (! Schema::hasColumn('settings', 'show_hr_salary_payroll_trend')) {
                $table->boolean('show_hr_salary_payroll_trend')->default(true)->after('show_hr_attendance_pattern');
            }
            if (! Schema::hasColumn('settings', 'show_hr_payroll_snapshot')) {
                $table->boolean('show_hr_payroll_snapshot')->default(true)->after('show_hr_salary_payroll_trend');
            }
            if (! Schema::hasColumn('settings', 'show_hr_attendance_watch')) {
                $table->boolean('show_hr_attendance_watch')->default(true)->after('show_hr_payroll_snapshot');
            }
            if (! Schema::hasColumn('settings', 'show_hr_payroll_status')) {
                $table->boolean('show_hr_payroll_status')->default(true)->after('show_hr_attendance_watch');
            }

            // ERP Dashboard main toggle
            if (! Schema::hasColumn('settings', 'show_erp_dashboard')) {
                $table->boolean('show_erp_dashboard')->default(true)->after('show_hr_payroll_status');
            }
            
            // ERP Dashboard subsections - Top Metric Boxes
            if (! Schema::hasColumn('settings', 'show_erp_total_sales')) {
                $table->boolean('show_erp_total_sales')->default(true)->after('show_erp_dashboard');
            }
            if (! Schema::hasColumn('settings', 'show_erp_total_purchase')) {
                $table->boolean('show_erp_total_purchase')->default(true)->after('show_erp_total_sales');
            }
            if (! Schema::hasColumn('settings', 'show_erp_total_expense')) {
                $table->boolean('show_erp_total_expense')->default(true)->after('show_erp_total_purchase');
            }
            
            // ERP Dashboard subsections - Count Boxes
            if (! Schema::hasColumn('settings', 'show_erp_sales_invoice_count')) {
                $table->boolean('show_erp_sales_invoice_count')->default(true)->after('show_erp_total_expense');
            }
            if (! Schema::hasColumn('settings', 'show_erp_purchase_invoice_count')) {
                $table->boolean('show_erp_purchase_invoice_count')->default(true)->after('show_erp_sales_invoice_count');
            }
            if (! Schema::hasColumn('settings', 'show_erp_customers_count')) {
                $table->boolean('show_erp_customers_count')->default(true)->after('show_erp_purchase_invoice_count');
            }
            if (! Schema::hasColumn('settings', 'show_erp_vendors_count')) {
                $table->boolean('show_erp_vendors_count')->default(true)->after('show_erp_customers_count');
            }
            
            // ERP Dashboard subsections - Charts
            if (! Schema::hasColumn('settings', 'show_erp_sales_chart')) {
                $table->boolean('show_erp_sales_chart')->default(true)->after('show_erp_vendors_count');
            }
            if (! Schema::hasColumn('settings', 'show_erp_purchase_chart')) {
                $table->boolean('show_erp_purchase_chart')->default(true)->after('show_erp_sales_chart');
            }
            
            // ERP Dashboard subsections - Tables
            if (! Schema::hasColumn('settings', 'show_erp_recent_sales')) {
                $table->boolean('show_erp_recent_sales')->default(true)->after('show_erp_purchase_chart');
            }
            if (! Schema::hasColumn('settings', 'show_erp_recent_purchases')) {
                $table->boolean('show_erp_recent_purchases')->default(true)->after('show_erp_recent_sales');
            }
            if (! Schema::hasColumn('settings', 'show_erp_recent_products')) {
                $table->boolean('show_erp_recent_products')->default(true)->after('show_erp_recent_purchases');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'show_crm_dashboard',
                'show_crm_lead_pipeline',
                'show_crm_conversion',
                'show_crm_followup_load',
                'show_crm_meeting_momentum',
                'show_crm_lead_status_mix',
                'show_crm_activity_trend',
                'show_crm_pipeline_quality',
                'show_crm_recent_leads',
                'show_crm_next_7_days',
                'show_hr_dashboard',
                'show_hr_staff_strength',
                'show_hr_active_staff',
                'show_hr_monthly_attendance',
                'show_hr_personal_progress',
                'show_hr_attendance_pattern',
                'show_hr_salary_payroll_trend',
                'show_hr_payroll_snapshot',
                'show_hr_attendance_watch',
                'show_hr_payroll_status',
                'show_erp_dashboard',
                'show_erp_total_sales',
                'show_erp_total_purchase',
                'show_erp_total_expense',
                'show_erp_sales_invoice_count',
                'show_erp_purchase_invoice_count',
                'show_erp_customers_count',
                'show_erp_vendors_count',
                'show_erp_sales_chart',
                'show_erp_purchase_chart',
                'show_erp_recent_sales',
                'show_erp_recent_purchases',
                'show_erp_recent_products',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
