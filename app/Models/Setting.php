<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'phone',
        'gst_num',
        'low_stock',
        'address',
        'logo',
        'currency_position',
        'currency_symbol',
        'favicon',
        'working_hours',
        'sunday_off',
        'grace_period',
        'lunch_break',
        'open_time',
        'close_time',
        'location_check_on_login',
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
        'send_mail',
        'customer_whatsapp_message',
        'admin_whatsapp_message',
        'created_at',
        'updated_at',
    ];

    // Automatically append extra fields
    protected $appends = ['logo_url', 'favicon_url', 'qr_code_url'];

    // ✅ Full logo path
    public function getLogoUrlAttribute()
    {
        $basePath = env('ImagePath', '/');
        if ($this->logo) {
            return url($basePath . 'storage/' . $this->logo);
        }
        return url($basePath . 'admin/assets/img/no-logo.png'); // fallback
    }

    // ✅ Favicon URL
    public function getFaviconUrlAttribute()
    {
        $basePath = env('ImagePath', '/');
        if ($this->favicon) {
            return url($basePath . 'storage/' . $this->favicon);
        }
        return url($basePath . 'admin/assets/img/no-favicon.png'); // fallback
    }

    public function getQrCodeUrlAttribute()
    {
        $basePath = env('ImagePath', '/');
        if ($this->qr_code) {
            return url($basePath . 'storage/' . $this->qr_code);
        }
        return url($basePath . 'admin/assets/img/no-favicon.png'); // fallback
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Kolkata');
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }
}
