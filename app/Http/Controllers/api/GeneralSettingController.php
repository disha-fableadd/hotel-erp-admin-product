<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\LocationSettingsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GeneralSettingController extends Controller
{

    public function show(Request $request)
    {
        $this->cleanRequestInputs($request);
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->id;
        $BranchId     = $user->branch_id;

        $selectedSubAdminId = $this->resolveSubAdminId($request, $userBranchId, $role);
        // dd($selectedSubAdminId);
        // Common: fetch settings (filter by branch if needed)
        // Use firstOrNew so we always return a usable object even if no row exists yet
        $settings = Setting::firstOrNew(['branch_id' => $selectedSubAdminId]);

        if (! $settings->exists) {
            $settings->name              = 'Branch Name';
            $settings->email             = 'branch@example.com';
            $settings->phone             = '0000000000';
            $settings->address           = 'Branch Address';
            $settings->currency_symbol   = '₹';
            $settings->currency_position = 'left';
            $settings->low_stock         = 10;
            $settings->show_hr_dashboard = 1;
            $settings->show_erp_dashboard = 1;
        }

        // 🔹 Load latest GPS coordinates dynamically onto the settings object
        $location = LocationSettingsModel::query()->latest('id')->first();
        $settings->office_latitude  = $location?->latitude;
        $settings->office_longitude = $location?->longitude;
        $settings->office_radius    = $location?->radius;

        return response()->json([
            'status'   => true,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $this->cleanRequestInputs($request);
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->id;

        $selectedSubAdminId = $this->resolveSubAdminId($request, $userBranchId, $role);
        // dd($selectedSubAdminId);
        $request->validate([
            'shop_name'         => 'required|string',
            'gst_num'           => 'nullable',
            'low_stock'         => 'nullable',
            'state_code'        => 'nullable',
            'email'             => 'required|email',
            'phone'             => 'required',
            'person_contact1' => 'nullable|string',
            'person_contact2' => 'nullable|string',
            'person_name1' => 'nullable|string',
            'person_name2' => 'nullable|string',
            'pancard' => 'nullable|string',
            'address'           => 'required|string',
            'currency_symbol'   => 'required|string',
            'currency_position' => 'required|string',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'favicon'           => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'qr_code'           => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'bank_name' => 'required|string',
            'branch' => 'required|string',
            'ac_no' => 'required|string',
            'ifsc_code' => 'required|string',
            // ✅ New Rules validation
            'working_hours'     => 'nullable|numeric|min:0',
            'sunday_off'        => 'nullable|in:yes,no',
            'grace_period'      => 'nullable|numeric|min:0',
            'lunch_break'       => 'nullable|numeric|min:0',
            'open_time'         => 'nullable|date_format:H:i',
            'close_time'        => 'nullable|date_format:H:i',
            'location_check_on_login' => 'nullable|boolean',
            'invoice_size' => 'nullable|in:small,big',
            'send_mail'         => 'nullable|boolean',
            'cin_no' => 'nullable|string',
            // ✅ E-Way Bill settings
            'eway_gstin' => 'nullable|string',
            'eway_business_name' => 'nullable|string',
            'eway_pan' => 'nullable|string',
            'eway_state' => 'nullable|string',
            'eway_state_code' => 'nullable|string',
            'eway_address1' => 'nullable|string',
            'eway_address2' => 'nullable|string',
            'eway_city' => 'nullable|string',
            'eway_pincode' => 'nullable|string',
            'eway_username' => 'nullable|string',
            'eway_password' => 'nullable|string',
            'eway_app_key' => 'nullable|string',
            // ✅ E-Invoice (IRP) API credentials
            'einvoice_enabled'       => 'nullable|boolean',
            'einvoice_api_url'       => 'nullable|url',
            'einvoice_username'      => 'nullable|string',
            'einvoice_password'      => 'nullable|string',
            'einvoice_gstin'         => 'nullable|string|max:20',
            'einvoice_client_id'     => 'nullable|string',
            'einvoice_client_secret' => 'nullable|string',
        ]);

        // Fetch or create branch-specific settings
        $settings = Setting::where('branch_id', $selectedSubAdminId)->first();

        if (! $settings) {
            $settings            = new Setting();
            $settings->branch_id = $selectedSubAdminId; // assign branch
        }

        $settings->gst_num           = $request->gst_num;
        $settings->low_stock         = $request->low_stock;
        $settings->name              = $request->shop_name;
        $settings->email             = $request->email;
        $settings->phone             = $request->phone;
        $settings->contact_person1 = $request->person_contact1;
        $settings->contact_person2 = $request->person_contact2;
        $settings->pan_no = $request->pancard;
        $settings->person_name1 = $request->person_name1;
        $settings->person_name2 = $request->person_name2;
        $settings->state_code        = $request->state_code;
        $settings->address           = $request->address;
        $settings->currency_position = $request->currency_position;
        $settings->currency_symbol   = $request->currency_symbol;
        $settings->bank_name = $request->bank_name;
        $settings->branch = $request->branch;
        $settings->ac_no = $request->ac_no;
        $settings->ifsc_code = $request->ifsc_code;
        $settings->send_mail = $request->has('send_mail') ? $request->send_mail : $settings->send_mail;
        $settings->customer_whatsapp_message = $request->has('customer_whatsapp_message') ? $request->customer_whatsapp_message : $settings->customer_whatsapp_message;
        $settings->admin_whatsapp_message = $request->has('admin_whatsapp_message') ? $request->admin_whatsapp_message : $settings->admin_whatsapp_message;
        // 🕒 Company Rules
        $settings->working_hours = $request->working_hours;
        $settings->sunday_off    = $request->sunday_off;
        $settings->grace_period  = $request->grace_period;
        $settings->lunch_break   = $request->lunch_break;
        $settings->open_time     = $request->open_time;
        $settings->close_time    = $request->close_time;
        $settings->location_check_on_login = $request->has('location_check_on_login')
            ? (int) $request->location_check_on_login
            : ($settings->location_check_on_login ?? 0);
        $settings->invoice_size      = $request->invoice_size;
        $settings->send_mail     = $request->has('send_mail')
            ? (int) $request->send_mail
            : ($settings->send_mail ?? 1);
        $settings->cin_no = $request->cin_no;

        // 🚛 E-Way Bill Settings
        $settings->eway_gstin = $request->eway_gstin;
        $settings->eway_business_name = $request->eway_business_name;
        $settings->eway_pan = $request->eway_pan;
        $settings->eway_state = $request->eway_state;
        $settings->eway_state_code = $request->eway_state_code;
        $settings->eway_address1 = $request->eway_address1;
        $settings->eway_address2 = $request->eway_address2;
        $settings->eway_city = $request->eway_city;
        $settings->eway_pincode = $request->eway_pincode;
        $settings->eway_username = $request->eway_username;
        $settings->eway_password = $request->eway_password;
        $settings->eway_app_key = $request->eway_app_key;

        // 🧾 E-Invoice (IRP) Settings
        $settings->einvoice_enabled       = $request->has('einvoice_enabled') ? (int) $request->einvoice_enabled : ($settings->einvoice_enabled ?? 0);
        $settings->einvoice_api_url       = $request->einvoice_api_url;
        $settings->einvoice_username      = $request->einvoice_username;
        $settings->einvoice_password      = $request->einvoice_password;
        $settings->einvoice_gstin         = $request->einvoice_gstin;
        $settings->einvoice_client_id     = $request->einvoice_client_id;
        $settings->einvoice_client_secret = $request->einvoice_client_secret;

        if ($request->hasFile('logo')) {
            $logoPath       = $request->file('logo')->store('logos', 'public');
            $settings->logo = $logoPath;
        }

        if ($request->hasFile('favicon')) {
            $faviconPath       = $request->file('favicon')->store('favicons', 'public');
            $settings->favicon = $faviconPath;
        }

        if ($request->hasFile('qr_code')) {
            $qr_code           = $request->file('qr_code')->store('qr_codes', 'public');
            $settings->qr_code = $qr_code;
        }

        $settings->save();

        return response()->json([
            'status'   => true,
            'message'  => 'Settings updated successfully',
            'settings' => $settings,
        ]);
    }

    public function updateEInvoiceSettings(Request $request)
    {
        $this->cleanRequestInputs($request);
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->id;

        $selectedSubAdminId = $this->resolveSubAdminId($request, $userBranchId, $role);

        $request->validate([
            'einvoice_enabled'       => 'nullable|boolean',
            'einvoice_username'      => 'nullable|string|max:255',
            'einvoice_password'      => 'nullable|string|max:255',
            'einvoice_client_id'     => 'nullable|string|max:255',
            'einvoice_client_secret' => 'nullable|string|max:500',
            'einvoice_api_url'       => 'nullable|string|max:500',
        ]);

        // Fetch or create settings row for this branch
        $settings = Setting::where('branch_id', $selectedSubAdminId)->first();

        if (! $settings) {
            $settings            = new Setting();
            $settings->branch_id = $selectedSubAdminId;
        }

        $settings->einvoice_enabled       = $request->has('einvoice_enabled') ? (int) $request->einvoice_enabled : ($settings->einvoice_enabled ?? 0);
        $settings->einvoice_username      = $request->einvoice_username;
        $settings->einvoice_password      = $request->einvoice_password;
        $settings->einvoice_client_id     = $request->einvoice_client_id;
        $settings->einvoice_client_secret = $request->einvoice_client_secret;
        $settings->einvoice_api_url       = $request->einvoice_api_url;

        $settings->save();

        return response()->json([
            'status'  => true,
            'message' => 'E-Invoice settings saved successfully.',
            'data'    => [
                'einvoice_enabled'       => $settings->einvoice_enabled,
                'einvoice_username'      => $settings->einvoice_username,
                'einvoice_client_id'     => $settings->einvoice_client_id,
                'einvoice_client_secret' => $settings->einvoice_client_secret,
                'einvoice_api_url'       => $settings->einvoice_api_url,
            ],
        ]);
    }

    public function updateCompanyRules(Request $request)
    {
        $this->cleanRequestInputs($request);
        $user               = Auth::guard('api')->user();
        $role               = $user->role;
        $userBranchId       = $user->id;
        $selectedSubAdminId = $this->resolveSubAdminId($request, $userBranchId, $role);

        // 🔹 Validation
        $validator = Validator::make($request->all(), [
            'working_hours' => 'nullable',
            'sunday_off'    => 'nullable|in:yes,no',
            'grace_period'  => 'nullable',
            'lunch_break'   => 'nullable',
            'open_time'     => 'nullable',
            'close_time'    => 'nullable',
            'location_check_on_login' => 'nullable|boolean',
            'office_latitude'  => 'nullable|numeric',
            'office_longitude' => 'nullable|numeric',
            'office_radius'    => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // 🔹 Fetch or auto-create Setting for Branch
        $settings = Setting::firstOrNew(['branch_id' => $selectedSubAdminId]);

        // If new record, set safe defaults so it can be saved
        if (! $settings->exists) {
            $settings->name             = 'Branch';
            $settings->email            = '';
            $settings->phone            = '';
            $settings->address          = '';
            $settings->currency_symbol  = '₹';
            $settings->currency_position = 'before';
        }

        // 🔹 Format times safely if provided, otherwise default to null
        $openTime = $request->open_time;
        if (!empty($openTime)) {
            $openTime = date('H:i', strtotime($openTime));
        } else {
            $openTime = null;
        }

        $closeTime = $request->close_time;
        if (!empty($closeTime)) {
            $closeTime = date('H:i', strtotime($closeTime));
        } else {
            $closeTime = null;
        }

        // 🔹 Update Company Rules (fill+save works for both new and existing records)
        $settings->fill([
            'working_hours' => $request->working_hours,
            'sunday_off'    => $request->sunday_off ?? 'no',
            'grace_period'  => $request->grace_period,
            'lunch_break'   => $request->lunch_break,
            'open_time'     => $openTime,
            'close_time'    => $closeTime,
            'location_check_on_login' => $request->has('location_check_on_login')
                ? (int) $request->location_check_on_login
                : ($settings->location_check_on_login ?? 0),
        ]);
        $settings->save();


        // 🔹 Update or Create Location Settings
        if ($request->has('office_latitude') || $request->has('office_longitude') || $request->has('office_radius')) {
            $location = LocationSettingsModel::first() ?: new LocationSettingsModel();
            $location->latitude = $request->office_latitude;
            $location->longitude = $request->office_longitude;
            $location->radius = $request->office_radius;
            $location->save();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Company rules updated successfully',
            'data'    => $settings,
        ]);
    }

    public function updateDashboardSettings(Request $request)
    {
        $this->cleanRequestInputs($request);
        $user               = Auth::guard('api')->user();
        $role               = $user->role;
        $userBranchId       = $user->id;
        $selectedSubAdminId = $this->resolveSubAdminId($request, $userBranchId, $role);

        $validator = Validator::make($request->all(), [
            'show_crm_dashboard'         => 'nullable|boolean',
            'show_hr_dashboard'          => 'nullable|boolean',
            'show_erp_dashboard'         => 'nullable|boolean',
            'show_crm_lead_pipeline'     => 'nullable|boolean',
            'show_crm_conversion'        => 'nullable|boolean',
            'show_crm_followup_load'     => 'nullable|boolean',
            'show_crm_meeting_momentum'  => 'nullable|boolean',
            'show_crm_lead_status_mix'   => 'nullable|boolean',
            'show_crm_activity_trend'    => 'nullable|boolean',
            'show_crm_pipeline_quality'  => 'nullable|boolean',
            'show_crm_recent_leads'      => 'nullable|boolean',
            'show_crm_next_7_days'       => 'nullable|boolean',
            'show_hr_staff_strength'     => 'nullable|boolean',
            'show_hr_active_staff'       => 'nullable|boolean',
            'show_hr_monthly_attendance' => 'nullable|boolean',
            'show_hr_personal_progress'  => 'nullable|boolean',
            'show_hr_attendance_pattern' => 'nullable|boolean',
            'show_hr_salary_payroll_trend' => 'nullable|boolean',
            'show_hr_payroll_snapshot'   => 'nullable|boolean',
            'show_hr_attendance_watch'   => 'nullable|boolean',
            'show_hr_payroll_status'     => 'nullable|boolean',
            'show_erp_total_sales'       => 'nullable|boolean',
            'show_erp_total_purchase'    => 'nullable|boolean',
            'show_erp_total_expense'     => 'nullable|boolean',
            'show_erp_sales_invoice_count' => 'nullable|boolean',
            'show_erp_purchase_invoice_count' => 'nullable|boolean',
            'show_erp_customers_count'   => 'nullable|boolean',
            'show_erp_vendors_count'     => 'nullable|boolean',
            'show_erp_sales_chart'       => 'nullable|boolean',
            'show_erp_purchase_chart'    => 'nullable|boolean',
            'show_erp_recent_sales'      => 'nullable|boolean',
            'show_erp_recent_purchases'  => 'nullable|boolean',
            'show_erp_recent_products'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings = Setting::firstOrNew(['branch_id' => $selectedSubAdminId]);
        
        // Main CRM dashboard toggle
        if ($request->has('show_crm_dashboard')) {
            $settings->show_crm_dashboard = (int) $request->show_crm_dashboard;
        }
        
        // Main HR dashboard toggle
        if ($request->has('show_hr_dashboard')) {
            $settings->show_hr_dashboard = (int) $request->show_hr_dashboard;
        }
        
        // Main ERP dashboard toggle
        if ($request->has('show_erp_dashboard')) {
            $settings->show_erp_dashboard = (int) $request->show_erp_dashboard;
        }
        
        // CRM subsection toggles
        if ($request->has('show_crm_lead_pipeline')) {
            $settings->show_crm_lead_pipeline = (int) $request->show_crm_lead_pipeline;
        }
        if ($request->has('show_crm_conversion')) {
            $settings->show_crm_conversion = (int) $request->show_crm_conversion;
        }
        if ($request->has('show_crm_followup_load')) {
            $settings->show_crm_followup_load = (int) $request->show_crm_followup_load;
        }
        if ($request->has('show_crm_meeting_momentum')) {
            $settings->show_crm_meeting_momentum = (int) $request->show_crm_meeting_momentum;
        }
        if ($request->has('show_crm_lead_status_mix')) {
            $settings->show_crm_lead_status_mix = (int) $request->show_crm_lead_status_mix;
        }
        if ($request->has('show_crm_activity_trend')) {
            $settings->show_crm_activity_trend = (int) $request->show_crm_activity_trend;
        }
        if ($request->has('show_crm_pipeline_quality')) {
            $settings->show_crm_pipeline_quality = (int) $request->show_crm_pipeline_quality;
        }
        if ($request->has('show_crm_recent_leads')) {
            $settings->show_crm_recent_leads = (int) $request->show_crm_recent_leads;
        }
        if ($request->has('show_crm_next_7_days')) {
            $settings->show_crm_next_7_days = (int) $request->show_crm_next_7_days;
        }
        
        // HR subsection toggles
        if ($request->has('show_hr_staff_strength')) {
            $settings->show_hr_staff_strength = (int) $request->show_hr_staff_strength;
        }
        if ($request->has('show_hr_active_staff')) {
            $settings->show_hr_active_staff = (int) $request->show_hr_active_staff;
        }
        if ($request->has('show_hr_monthly_attendance')) {
            $settings->show_hr_monthly_attendance = (int) $request->show_hr_monthly_attendance;
        }
        if ($request->has('show_hr_personal_progress')) {
            $settings->show_hr_personal_progress = (int) $request->show_hr_personal_progress;
        }
        if ($request->has('show_hr_attendance_pattern')) {
            $settings->show_hr_attendance_pattern = (int) $request->show_hr_attendance_pattern;
        }
        if ($request->has('show_hr_salary_payroll_trend')) {
            $settings->show_hr_salary_payroll_trend = (int) $request->show_hr_salary_payroll_trend;
        }
        if ($request->has('show_hr_payroll_snapshot')) {
            $settings->show_hr_payroll_snapshot = (int) $request->show_hr_payroll_snapshot;
        }
        if ($request->has('show_hr_attendance_watch')) {
            $settings->show_hr_attendance_watch = (int) $request->show_hr_attendance_watch;
        }
        if ($request->has('show_hr_payroll_status')) {
            $settings->show_hr_payroll_status = (int) $request->show_hr_payroll_status;
        }
        
        // ERP subsection toggles
        if ($request->has('show_erp_total_sales')) {
            $settings->show_erp_total_sales = (int) $request->show_erp_total_sales;
        }
        if ($request->has('show_erp_total_purchase')) {
            $settings->show_erp_total_purchase = (int) $request->show_erp_total_purchase;
        }
        if ($request->has('show_erp_total_expense')) {
            $settings->show_erp_total_expense = (int) $request->show_erp_total_expense;
        }
        if ($request->has('show_erp_sales_invoice_count')) {
            $settings->show_erp_sales_invoice_count = (int) $request->show_erp_sales_invoice_count;
        }
        if ($request->has('show_erp_purchase_invoice_count')) {
            $settings->show_erp_purchase_invoice_count = (int) $request->show_erp_purchase_invoice_count;
        }
        if ($request->has('show_erp_customers_count')) {
            $settings->show_erp_customers_count = (int) $request->show_erp_customers_count;
        }
        if ($request->has('show_erp_vendors_count')) {
            $settings->show_erp_vendors_count = (int) $request->show_erp_vendors_count;
        }
        if ($request->has('show_erp_sales_chart')) {
            $settings->show_erp_sales_chart = (int) $request->show_erp_sales_chart;
        }
        if ($request->has('show_erp_purchase_chart')) {
            $settings->show_erp_purchase_chart = (int) $request->show_erp_purchase_chart;
        }
        if ($request->has('show_erp_recent_sales')) {
            $settings->show_erp_recent_sales = (int) $request->show_erp_recent_sales;
        }
        if ($request->has('show_erp_recent_purchases')) {
            $settings->show_erp_recent_purchases = (int) $request->show_erp_recent_purchases;
        }
        if ($request->has('show_erp_recent_products')) {
            $settings->show_erp_recent_products = (int) $request->show_erp_recent_products;
        }
        
        $settings->save();

        return response()->json([
            'status'   => true,
            'message'  => 'Dashboard settings updated successfully',
            'settings' => $settings,
        ]);
    }

    private function resolveSubAdminId(Request $request, $userBranchId, $role)
    {
        $selectedSubAdminId = $request->input('selectedSubAdminId') ?? $request->query('selectedSubAdminId');

        if ($selectedSubAdminId === 'null' || $selectedSubAdminId === 'undefined' || empty($selectedSubAdminId)) {
            return $userBranchId;
        }

        if ($role === 'admin') {
            $subAdmin = User::where('id', $selectedSubAdminId)->first();
            if ($subAdmin) {
                return $subAdmin->id;
            }
        }

        return $userBranchId;
    }

    private function cleanRequestInputs(Request $request)
    {
        $input = $request->all();
        $cleaned = [];
        foreach ($input as $key => $value) {
            if ($value === 'undefined' || $value === 'null') {
                continue;
            }
            $cleaned[$key] = $value;
        }
        $request->replace($cleaned);

        if ($request->isMethod('get')) {
            $request->query->replace($cleaned);
        }
    }
}
