<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\User;
use App\Models\AdvancePayment;
use App\Models\Attendance;
use App\Models\LeaveModel;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function signin()
    {

        return view('signin');
    }
    public function forgetpassword()
    {

        return view('forgetpassword');
    }
    public function subadmin_session(Request $request)
    {
        // dd($request->all());
        $subAdminId = $request->input('subAdminId');
        session(['selectedSubAdminId' => $subAdminId]);
        return response()->json(['status' => 'success']);
    }

    public function dashboard()
    {
        $user = auth()->user();
        if ($user) {
            if (strtolower($user->role) === 'staff') {
                return redirect()->route('auth.staff-dashboard');
            } elseif (strtolower($user->role) === 'hr') {
                return redirect()->route('hr.dashboard');
            }
        }
        $selectedSubAdminId = session('selectedSubAdminId');

        // ✅ Decide BranchID based on role/session
        if (!empty($selectedSubAdminId)) {
            $BranchID = $selectedSubAdminId;
        } elseif (strtolower($user->role) === 'staff' && $user->branch_id) {
            $BranchID = $user->branch_id;
        } else {
            $BranchID = $user->id;
        }

        // ✅ Branch-specific settings
        $settings = DB::table('settings')->where('branch_id', $BranchID)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Toggles
        $showCrmDashboardSection = (bool) ($settings->show_crm_dashboard ?? true);
        $showHrDashboardSection = (bool) ($settings->show_hr_dashboard ?? true);

        // CRM subsection visibility settings
        $showCrmLeadPipeline = (bool) ($settings->show_crm_lead_pipeline ?? true);
        $showCrmConversion = (bool) ($settings->show_crm_conversion ?? true);
        $showCrmFollowupLoad = (bool) ($settings->show_crm_followup_load ?? true);
        $showCrmMeetingMomentum = (bool) ($settings->show_crm_meeting_momentum ?? true);
        $showCrmLeadStatusMix = (bool) ($settings->show_crm_lead_status_mix ?? true);
        $showCrmActivityTrend = (bool) ($settings->show_crm_activity_trend ?? true);
        $showCrmPipelineQuality = (bool) ($settings->show_crm_pipeline_quality ?? true);
        $showCrmRecentLeads = (bool) ($settings->show_crm_recent_leads ?? true);
        $showCrmNext7Days = (bool) ($settings->show_crm_next_7_days ?? true);

        // HR subsection visibility settings
        $showHrStaffStrength = (bool) ($settings->show_hr_staff_strength ?? true);
        $showHrActiveStaff = (bool) ($settings->show_hr_active_staff ?? true);
        $showHrMonthlyAttendance = (bool) ($settings->show_hr_monthly_attendance ?? true);
        $showHrPersonalProgress = (bool) ($settings->show_hr_personal_progress ?? true);
        $showHrAttendancePattern = (bool) ($settings->show_hr_attendance_pattern ?? true);
        $showHrSalaryPayrollTrend = (bool) ($settings->show_hr_salary_payroll_trend ?? true);
        $showHrPayrollSnapshot = (bool) ($settings->show_hr_payroll_snapshot ?? true);
        $showHrAttendanceWatch = (bool) ($settings->show_hr_attendance_watch ?? true);
        $showHrPayrollStatus = (bool) ($settings->show_hr_payroll_status ?? true);

        // ERP subsection visibility settings
        $showErpDashboard = (bool) ($settings->show_erp_dashboard ?? true);
        $showErpTotalSales = (bool) ($settings->show_erp_total_sales ?? true);
        $showErpTotalPurchase = (bool) ($settings->show_erp_total_purchase ?? true);
        $showErpTotalExpense = (bool) ($settings->show_erp_total_expense ?? true);
        $showErpSalesInvoiceCount = (bool) ($settings->show_erp_sales_invoice_count ?? true);
        $showErpPurchaseInvoiceCount = (bool) ($settings->show_erp_purchase_invoice_count ?? true);
        $showErpCustomersCount = (bool) ($settings->show_erp_customers_count ?? true);
        $showErpVendorsCount = (bool) ($settings->show_erp_vendors_count ?? true);
        $showErpSalesChart = (bool) ($settings->show_erp_sales_chart ?? true);
        $showErpPurchaseChart = (bool) ($settings->show_erp_purchase_chart ?? true);
        $showErpRecentSales = (bool) ($settings->show_erp_recent_sales ?? true);
        $showErpRecentPurchases = (bool) ($settings->show_erp_recent_purchases ?? true);
        $showErpRecentProducts = (bool) ($settings->show_erp_recent_products ?? true);

        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;
        $currentMonth = Carbon::now()->month;
        $now = Carbon::now('Asia/Kolkata');
        $today = $now->toDateString();

        $attendanceRecords = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get();

        $totalPresent = $attendanceRecords->whereIn('status', ['P', 'present'])->count()
            + ($attendanceRecords->whereIn('status', ['H', 'half-day'])->count() * 0.5);
        $totalLeave = $attendanceRecords->whereIn('status', ['A', 'absent', 'leave'])->count();

        $todayAttendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->orderByDesc('id')
            ->first();

        $todayLeave = LeaveModel::query()
            ->where('user_id', $user->id)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereIn('status', ['approved', 'Approved'])
            ->latest('id')
            ->first();

        $todayCheckInTime = $todayAttendance?->check_in_time;
        $todayCheckOutTime = $todayAttendance?->check_out_time;
        $todayStatus = $todayAttendance?->status ?? ($todayLeave ? 'leave' : 'absent');
        $todayStatusLabel = str_replace('-', ' ', ucfirst((string) $todayStatus));
        $todayCheckInTimestamp = $todayAttendance?->check_in_time
            ? Carbon::parse($today . ' ' . $todayAttendance->check_in_time, 'Asia/Kolkata')->timestamp
            : null;

        $todayWorkingSeconds = 0;
        if ($todayAttendance?->check_in_time) {
            $checkIn = Carbon::parse($today . ' ' . $todayAttendance->check_in_time, 'Asia/Kolkata');
            $checkOut = $todayAttendance->check_out_time
                ? Carbon::parse($today . ' ' . $todayAttendance->check_out_time, 'Asia/Kolkata')
                : $now;

            if ($checkOut->gt($checkIn)) {
                $todayWorkingSeconds = $checkOut->diffInSeconds($checkIn);
                if ($todayWorkingSeconds >= (30 * 60)) {
                    $todayWorkingSeconds -= (30 * 60);
                }
            }
        }

        $todayWorkingHours = sprintf(
            '%02d:%02d:%02d',
            intdiv(max(0, $todayWorkingSeconds), 3600),
            intdiv(max(0, $todayWorkingSeconds) % 3600, 60),
            max(0, $todayWorkingSeconds) % 60
        );
        $todayWorkingProgress = min(100, (int) round(($todayWorkingSeconds / (8.5 * 3600)) * 100));
        $todayIsWorking = (bool) ($todayAttendance && empty($todayAttendance->check_out_time) && !$todayLeave);

        // ✅ Charts
        $salesChartThisMonth = $this->getSalesDataByMonth($currentMonth, $BranchID);
        $purchaseChartThisMonth = $this->getPurchaseDataByMonth($currentMonth, $BranchID);

        $salesChartthisyear = $this->getSalesDataByYear($currentYear, $BranchID);
        $salesChartpreviousyear = $this->getSalesDataByYear($previousYear, $BranchID);

        $purchaseChartthisyear = $this->getPurchaseDataByYear($currentYear, $BranchID);
        $purchaseChartpreviousyear = $this->getPurchaseDataByYear($previousYear, $BranchID);

        // ✅ Totals (branch-wise)
        $totalPurchaseAmount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('grand_total');

        $totalSalesAmount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('total_amount');

        $totalExpenseAmount = Expense::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('amount');

        // ✅ Counts
        $runningTableCount = \App\Models\Table_no::where('branch_id', $BranchID)
            ->where('isDeleted', 0)
            ->get()
            ->filter(fn($table) => $table->resolved_status === 'running')
            ->count();

        $customerCount = User::where('role', 'customer')
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        $vendorCount = User::where('role', 'vendor')
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        $purchaseInvoiceCount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        $completedPurchaseCount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->whereIn('status', ['Completed', 'Paid', 'completed', 'paid'])
            ->count();

        $partiallyPaidPurchaseCount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->whereIn('status', ['Partially', 'partially', 'Partially Paid'])
            ->count();

        $pendingPurchaseCount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->whereIn('status', ['Pending', 'pending', 'Unpaid', 'unpaid'])
            ->count();

        $salesInvoiceCount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        $completedSalesCount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->whereIn('payment_status', ['Completed', 'Paid', 'completed', 'paid'])
            ->count();

        $partiallyPaidSalesCount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->whereIn('payment_status', ['Partially', 'partially', 'Partially Paid'])
            ->count();

        $pendingSalesCount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->whereIn('payment_status', ['Pending', 'pending', 'Unpaid', 'unpaid'])
            ->count();

        // ✅ Recent products (branch-wise)
        $recentProducts = Product::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->latest()
            ->take(4)
            ->get();

        // ✅ Latest Sales
        $latestSales = DB::table('orders')
            ->where('orders.isDeleted', '!=', 1)
            ->where('orders.branch_id', $BranchID)

            // join ONE order item per order
            ->joinSub(
                DB::table('order_items')
                    ->selectRaw('MIN(id) as id, order_id')
                    ->where('isDeleted', '!=', 1)
                    ->groupBy('order_id'),
                'oi',
                function ($join) {
                    $join->on('orders.id', '=', 'oi.order_id');
                }
            )

            ->join('order_items', 'order_items.id', '=', 'oi.id')

            ->join('products', function ($join) {
                $join->on('order_items.product_id', '=', 'products.id')
                    ->where('products.isDeleted', '!=', 1);
            })
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')

            ->select(
                'orders.id as order_id',
                'orders.order_number',
                'orders.created_at as order_date',
                'orders.total_amount',
                'orders.payment_method',
                'orders.payment_status',
                'users.name as customer_name',

                'products.id as product_id',
                'products.name as product_name',
                'products.SKU as product_code',
                'products.images',

                'categories.name as category_name'
            )
            ->orderBy('orders.created_at', 'desc')
            ->limit(4)
            ->get();

        $latestPurchases = DB::table('purchase_invoice as pi')
            ->where('pi.isDeleted', '!=', 1)
            ->where('pi.branch_id', '=', $BranchID)

            // join ONE purchase item per invoice
            ->joinSub(
                DB::table('purchases')
                    ->selectRaw('MIN(id) as id, invoice_id')
                    ->where('isDeleted', '!=', 1)
                    ->groupBy('invoice_id'),
                'p',
                function ($join) {
                    $join->on('pi.id', '=', 'p.invoice_id');
                }
            )

            ->join('purchases as pur', 'pur.id', '=', 'p.id')

            ->leftJoin('products as pr', function ($join) {
                $join->on('pr.id', '=', 'pur.item')
                    ->where('pr.isDeleted', '!=', 1);
            })
            ->leftJoin('categories as cat', 'pr.category_id', '=', 'cat.id')
            ->leftJoin('users as vendor_user', 'pi.vendor_id', '=', 'vendor_user.id')

            ->select(
                'pi.id as invoice_id',
                'pi.invoice_number',
                'pi.bill_no',
                'pi.grand_total',
                'pi.status as purchase_status',
                'pi.created_at as purchase_date',
                'vendor_user.name as vendor_name',

                'pr.id as product_id',
                'pr.name as product_name',
                'pr.SKU as product_code',
                'pr.images',

                'cat.name as category_name'
            )
            ->limit(4)
            ->get();

        $latestDeliveries = DB::table('orders')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('users as staff', 'orders.assigned_staff_id', '=', 'staff.id')
            ->where('orders.isDeleted', '!=', 1)
            ->where('orders.branch_id', $BranchID)
            ->where('orders.order_type', 'delivery')
            ->select(
                'orders.id as order_id',
                'orders.order_number',
                'orders.delivery_status',
                'orders.assigned_staff_id',
                'users.name as customer_name',
                'staff.name as staff_name'
            )
            ->orderBy('orders.id', 'desc')
            ->limit(4)
            ->get();

        // ✅ Monthly Sales Chart
        $salesData = DB::table('order_items')
            ->join('orders', function ($join) use ($BranchID) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.isDeleted', '!=', 1)
                    ->where('orders.branch_id', '=', $BranchID);
            })
            ->where('order_items.isDeleted', '!=', 1)
            ->select(DB::raw("MONTH(order_items.created_at) as month"), DB::raw("SUM(order_items.total_amount) as total"))
            ->groupBy(DB::raw("MONTH(order_items.created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        // ✅ Monthly Purchase Chart
        $purchasesData = DB::table('purchases')
            ->leftJoin('purchase_invoice', function ($join) use ($BranchID) {
                $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                    ->where('purchase_invoice.isDeleted', '!=', 1)
                    ->where('purchase_invoice.branch_id', '=', $BranchID);
            })
            ->where('purchases.isDeleted', '!=', 1)
            ->select(DB::raw("MONTH(purchases.created_at) as month"), DB::raw("SUM(purchases.amount_total) as total"))
            ->groupBy(DB::raw("MONTH(purchases.created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $salesChart = [];
        $purchaseChart = [];
        for ($m = 1; $m <= 12; $m++) {
            $salesChart[] = (float) ($salesData[$m] ?? 0);
            $purchaseChart[] = (float) ($purchasesData[$m] ?? 0);
        }

        // ============ HR WORKFORCE QUERIES ============
        $staffQuery = User::where('role', 'staff')
            ->where('isDeleted', 0)
            ->where('branch_id', $BranchID);

        $totalStaff = (clone $staffQuery)->count();
        $activeStaff = (clone $staffQuery)->where('status', 1)->count();
        $newStaffThisMonth = (clone $staffQuery)
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        $todayAttendance = Attendance::where('branch_id', $BranchID)
            ->whereDate('date', $today)
            ->get();
        $todayPresentStaff = $todayAttendance->whereIn('status', ['P', 'H', 'present', 'half-day'])->unique('user_id')->count();
        $todayAbsentStaff = $todayAttendance->whereIn('status', ['A', 'absent'])->unique('user_id')->count();
        $todayUnmarkedStaff = max(0, $totalStaff - $todayAttendance->unique('user_id')->count());

        $monthAttendance = Attendance::where('branch_id', $BranchID)
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->get();
        $presentEquivalent = $monthAttendance->whereIn('status', ['P', 'present'])->count()
            + ($monthAttendance->whereIn('status', ['H', 'half-day'])->count() * 0.5);
        $markedAttendance = $monthAttendance->whereIn('status', ['P', 'A', 'H', 'present', 'absent', 'half-day'])->count();
        $monthlyAttendanceRate = $markedAttendance > 0 ? round(($presentEquivalent / $markedAttendance) * 100, 1) : 0;

        $currentMonthSalary = \App\Models\PayrollModel::join('users', 'payroll.user_id', '=', 'users.id')
            ->where('users.branch_id', $BranchID)
            ->where('payroll.month_year', $now->format('Y-m'))
            ->select('payroll.*', 'users.name as staff_name')
            ->get();
        $salaryPaidCount = $currentMonthSalary->count();
        $salaryPendingCount = max(0, $totalStaff - $salaryPaidCount);
                            $salaryPaidAmount = (float) $currentMonthSalary->sum('net_salary');
        $advanceOutstanding = (float) AdvancePayment::where('branch_id', $BranchID)
            ->where('isDeleted', 0)
            ->where('status', '!=', 'cleared')
            ->get(['amount', 'paid_amount'])
            ->sum(fn($advance) => max(0, (float) $advance->amount - (float) $advance->paid_amount));

        $hrAttendanceLabels = [];
        $hrAttendancePresent = [];
        $hrAttendanceAbsent = [];
        $hrAttendanceHalfDay = [];
        
        // Populate last 7 days of attendance
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $dailyRows = Attendance::where('branch_id', $BranchID)
                ->whereDate('date', $day->toDateString())
                ->get();
            $hrAttendanceLabels[] = $day->format('d M');
            $hrAttendancePresent[] = (int) $dailyRows->whereIn('status', ['P', 'present'])->unique('user_id')->count();
            $hrAttendanceAbsent[] = (int) $dailyRows->whereIn('status', ['A', 'absent'])->unique('user_id')->count();
            $hrAttendanceHalfDay[] = (int) $dailyRows->whereIn('status', ['H', 'half-day'])->unique('user_id')->count();
        }

        $hrSalaryLabels = [];
        $hrSalaryPaidAmounts = [];
        $hrSalaryPaidCounts = [];
        
        // Populate last 6 months of salary data
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthlySalaryRows = \App\Models\PayrollModel::join('users', 'payroll.user_id', '=', 'users.id')
                ->where('users.branch_id', $BranchID)
                ->where('payroll.month_year', $month->format('Y-m'))
                ->get();
            $hrSalaryLabels[] = $month->format('M y');
            $hrSalaryPaidAmounts[] = (float) ($monthlySalaryRows->sum('net_salary') ?? 0);
            $hrSalaryPaidCounts[] = (int) ($monthlySalaryRows->count() ?? 0);
        }

        $attendanceWatch = DB::table('attendances')
            ->join('users', 'users.id', '=', 'attendances.user_id')
            ->where('attendances.branch_id', $BranchID)
            ->whereYear('attendances.date', $now->year)
            ->whereMonth('attendances.date', $now->month)
            ->where('users.role', 'staff')
            ->where('users.isDeleted', 0)
            ->select(
                'users.id',
                'users.name',
                DB::raw("SUM(CASE WHEN attendances.status = 'A' THEN 1 ELSE 0 END) as absent_days"),
                DB::raw("SUM(CASE WHEN attendances.status = 'H' THEN 1 ELSE 0 END) as half_days"),
                DB::raw("SUM(CASE WHEN attendances.status = 'P' THEN 1 ELSE 0 END) as present_days")
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('absent_days')
            ->orderByDesc('half_days')
            ->limit(5)
            ->get();

        $salaryStaffIds = $currentMonthSalary->pluck('user_id')->filter()->all();
        $payrollStatusRows = (clone $staffQuery)
            ->select('id', 'name', 'phone')
            ->orderBy('name')
            ->take(6)
            ->get()
            ->map(function ($staff) use ($currentMonthSalary, $salaryStaffIds) {
                $salary = $currentMonthSalary->firstWhere('user_id', $staff->id);
                $baseSalary = \DB::table('user_details')->where('user_id', $staff->id)->value('salary');
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'phone' => $staff->phone,
                    'status' => in_array($staff->id, $salaryStaffIds) ? 'Paid' : 'Pending',
                    'amount' => $salary ? (float) ($salary->net_salary > 0 ? $salary->net_salary : $baseSalary) : (float) $baseSalary,
                ];
            });

        return view('index', compact(
            'runningTableCount',
            'totalPurchaseAmount',
            'totalSalesAmount',
            'totalExpenseAmount',
            'customerCount',
            'vendorCount',
            'purchaseInvoiceCount',
            'completedPurchaseCount',
            'partiallyPaidPurchaseCount',
            'pendingPurchaseCount',
            'salesInvoiceCount',
            'completedSalesCount',
            'partiallyPaidSalesCount',
            'pendingSalesCount',
            'recentProducts',
            'latestSales',
            'latestPurchases',
            'latestDeliveries',
            'salesChart',
            'purchaseChart',
            'currencySymbol',
            'currencyPosition',
            'salesChartthisyear',
            'salesChartpreviousyear',
            'purchaseChartthisyear',
            'purchaseChartpreviousyear',
            'salesChartThisMonth',
            'purchaseChartThisMonth',

            // Pass dashboard toggles
            'showCrmDashboardSection',
            'showHrDashboardSection',
            'showCrmLeadPipeline',
            'showCrmConversion',
            'showCrmFollowupLoad',
            'showCrmMeetingMomentum',
            'showCrmLeadStatusMix',
            'showCrmActivityTrend',
            'showCrmPipelineQuality',
            'showCrmRecentLeads',
            'showCrmNext7Days',
            'showHrStaffStrength',
            'showHrActiveStaff',
            'showHrMonthlyAttendance',
            'showHrPersonalProgress',
            'showHrAttendancePattern',
            'showHrSalaryPayrollTrend',
            'showHrPayrollSnapshot',
            'showHrAttendanceWatch',
            'showHrPayrollStatus',
            'showErpDashboard',
            'showErpTotalSales',
            'showErpTotalPurchase',
            'showErpTotalExpense',
            'showErpSalesInvoiceCount',
            'showErpPurchaseInvoiceCount',
            'showErpCustomersCount',
            'showErpVendorsCount',
            'showErpSalesChart',
            'showErpPurchaseChart',
            'showErpRecentSales',
            'showErpRecentPurchases',
            'showErpRecentProducts',

            // HR data
            'totalStaff',
            'activeStaff',
            'newStaffThisMonth',
            'todayPresentStaff',
            'todayAbsentStaff',
            'todayUnmarkedStaff',
            'monthlyAttendanceRate',
            'salaryPaidCount',
            'salaryPendingCount',
            'salaryPaidAmount',
            'advanceOutstanding',
            'hrAttendanceLabels',
            'hrAttendancePresent',
            'hrAttendanceAbsent',
            'hrAttendanceHalfDay',
            'hrSalaryLabels',
            'hrSalaryPaidAmounts',
            'hrSalaryPaidCounts',
            'attendanceWatch',
            'payrollStatusRows',
            'currentYear',
            'previousYear'
        ));
    }

    private function getSalesDataByMonth($month, $BranchID, $userId = null)
    {
        $salesData = DB::table('orders')
            ->whereMonth('created_at', $month)
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->when($userId, function($q) use ($userId) {
                return $q->where('created_by', $userId);
            })
            ->select(
                DB::raw("DAY(created_at) as day"),
                DB::raw("SUM(total_amount) as total")
            )
            ->groupBy(DB::raw("DAY(created_at)"))
            ->pluck('total', 'day')
            ->toArray();

        $chartData = [];
        for ($d = 1; $d <= 31; $d++) {
            $chartData[] = (float) ($salesData[$d] ?? 0);
        }
        return $chartData;
    }

    private function getPurchaseDataByMonth($month, $BranchID, $userId = null)
    {
        $purchaseData = DB::table('purchase_invoice')
            ->whereMonth('created_at', $month)
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->when($userId, function($q) use ($userId) {
                return $q->where('created_by', $userId);
            })
            ->select(
                DB::raw("DAY(created_at) as day"),
                DB::raw("SUM(grand_total) as total")
            )
            ->groupBy(DB::raw("DAY(created_at)"))
            ->pluck('total', 'day')
            ->toArray();

        $chartData = [];
        for ($d = 1; $d <= 31; $d++) {
            $chartData[] = (float) ($purchaseData[$d] ?? 0);
        }
        return $chartData;
    }

    private function getSalesDataByYear($year, $BranchID, $userId = null)
    {
        $salesData = DB::table('orders')
            ->whereYear('created_at', $year)
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->when($userId, function($q) use ($userId) {
                return $q->where('created_by', $userId);
            })
            ->select(
                DB::raw("MONTH(created_at) as month"),
                DB::raw("SUM(total_amount) as total")
            )
            ->groupBy(DB::raw("MONTH(created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $chart = [];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = (float) ($salesData[$m] ?? 0);
        }
        return $chart;
    }

    private function getPurchaseDataByYear($year, $BranchID, $userId = null)
    {
        $purchaseData = DB::table('purchase_invoice')
            ->whereYear('created_at', $year)
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->when($userId, function($q) use ($userId) {
                return $q->where('created_by', $userId);
            })
            ->select(
                DB::raw("MONTH(created_at) as month"),
                DB::raw("SUM(grand_total) as total")
            )
            ->groupBy(DB::raw("MONTH(created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $chart = [];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = (float) ($purchaseData[$m] ?? 0);
        }
        return $chart;
    }

    public function profile()
    {
        $user = auth()->user();
        return view('profile', compact('user'));
    }
    public function taxrates()
    {

        return view('taxrates');
    }
    public function currency()
    {
        return view('currency');
    }
    public function ajaxSearch(Request $request)
    {
        $query = $request->get('query');

        $settings = DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Search users with role customer or vendor
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->whereIn('role', ['customer', 'vendor'])
            ->take(10)
            ->get(['id', 'name', 'email', 'profile_image', 'role']);

        // Search products by name, include category_id
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->take(10)
            ->get(['id', 'name', 'price', 'images', 'category_id']);

        // Search orders by order number or customer name
        $orders = Order::with('user:id,name')
            ->where('order_number', 'LIKE', "%{$query}%")
            ->orWhereHas('user', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'order_number', 'user_id', 'total_amount', 'payment_status']);

        // Search purchase invoices
        $purchaseInvoices = PurchaseInvoice::where('invoice_number', 'LIKE', "%{$query}%")
            ->take(10)
            ->get(['id', 'invoice_number', 'grand_total']);

        // Helper to format amount
        $formatCurrency = function ($amount) use ($currencySymbol, $currencyPosition) {
            return $currencyPosition === 'left'
                ? $currencySymbol . number_format($amount, 2)
                : number_format($amount, 2) . $currencySymbol;
        };

        $results = [
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_image' => $user->profile_image
                        ? asset(env('ImagePath') . 'storage/' . $user->profile_image)
                        : asset(env('ImagePath') . 'admin/assets/img/customer/default.jpg'),
                    'role' => $user->role,
                ];
            }),
            'products' => $products->map(function ($product) use ($formatCurrency) {
                $imageFileName = null;
                if (!empty($product->images)) {
                    $imagesArray = json_decode($product->images, true);
                    if (is_array($imagesArray) && count($imagesArray) > 0) {
                        $imageFileName = $imagesArray[0];
                    }
                }
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $formatCurrency($product->price), // formatted price
                    'category_id' => $product->category_id,
                    'image' => $imageFileName ? asset(env('ImagePath') . 'storage/' . $imageFileName) : asset(env('ImagePath') . 'admin/assets/img/product/noimage.png'),
                ];
            }),
            'orders' => $orders->map(function ($order) use ($formatCurrency) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $formatCurrency($order->total_amount), // formatted total
                    'payment_status' => $order->payment_status,
                    'user_name' => $order->user ? $order->user->name : 'N/A',
                ];
            }),
            'purchase_invoices' => $purchaseInvoices->map(function ($invoice) use ($formatCurrency) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'grand_total' => $formatCurrency($invoice->grand_total),
                ];
            }),
        ];

        return response()->json($results);
    }

    public function staffDashboard()
    {
        $user = auth()->user();

        // Only staff can access this page; others go to main dashboard
        if (strtolower($user->role) !== 'staff') {
            return redirect()->route('auth.dashboard');
        }

        $now = \Carbon\Carbon::now('Asia/Kolkata');
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $monthName = $now->format('F Y');

        // ── Attendance stats for current month ──────────────────────────────
        $attendanceRecords = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get();

        $totalPresent = $attendanceRecords->whereIn('status', ['P', 'present'])->count()
            + ($attendanceRecords->whereIn('status', ['H', 'half-day'])->count() * 0.5);
        $totalLeave = $attendanceRecords->whereIn('status', ['A', 'absent', 'leave'])->count();

        $todayDate = $now->toDateString();
        $todayAttendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->orderByDesc('id')
            ->first();

        $todayLeave = LeaveModel::query()
            ->where('user_id', $user->id)
            ->whereDate('start_date', '<=', $todayDate)
            ->whereDate('end_date', '>=', $todayDate)
            ->whereIn('status', ['approved', 'Approved'])
            ->latest('id')
            ->first();

        $todayCheckInTime = $todayAttendance?->check_in_time;
        $todayCheckOutTime = $todayAttendance?->check_out_time;
        $todayStatus = $todayAttendance?->status ?? ($todayLeave ? 'leave' : 'absent');
        $todayStatusLabel = str_replace('-', ' ', ucfirst((string) $todayStatus));
        $todayCheckInTimestamp = $todayAttendance?->check_in_time
            ? Carbon::parse($todayDate . ' ' . $todayAttendance->check_in_time, 'Asia/Kolkata')->timestamp
            : null;

        $todayWorkingSeconds = 0;
        if ($todayAttendance?->check_in_time) {
            $checkIn = Carbon::parse($todayDate . ' ' . $todayAttendance->check_in_time, 'Asia/Kolkata');
            $checkOut = $todayAttendance->check_out_time
                ? Carbon::parse($todayDate . ' ' . $todayAttendance->check_out_time, 'Asia/Kolkata')
                : $now;

            if ($checkOut->gt($checkIn)) {
                $todayWorkingSeconds = $checkOut->diffInSeconds($checkIn);
                if ($todayWorkingSeconds >= (30 * 60)) {
                    $todayWorkingSeconds -= (30 * 60);
                }
            }
        }

        $todayWorkingHours = sprintf(
            '%02d:%02d:%02d',
            intdiv(max(0, $todayWorkingSeconds), 3600),
            intdiv(max(0, $todayWorkingSeconds) % 3600, 60),
            max(0, $todayWorkingSeconds) % 60
        );
        $todayWorkingProgress = min(100, (int) round(($todayWorkingSeconds / (8.5 * 3600)) * 100));
        $todayIsWorking = (bool) ($todayAttendance && empty($todayAttendance->check_out_time) && !$todayLeave);

        // $hasFollowUpModel = class_exists(\App\Models\FollowUp::class) && safe_has_table('follow_ups');
        // $hasMeetingModel = class_exists(\App\Models\Meeting::class) && safe_has_table('meetings');

        // $totalFollowUps = 0;
        // $recentFollowUps = collect();
        // if ($hasFollowUpModel) {
        //     try {
        //         $totalFollowUps = \App\Models\FollowUp::active()
        //             ->where('branch_id', $user->branch_id)
        //             ->where(function ($q) use ($user) {
        //                 $q->where('assigned_to', $user->id)
        //                     ->orWhere('created_by', $user->id);
        //             })
        //             ->count();

        //         $recentFollowUps = \App\Models\FollowUp::with(['customer:id,name', 'lead:id,name', 'assignedUser:id,name'])
        //             ->active()
        //             ->where('branch_id', $user->branch_id)
        //             ->where(function ($q) use ($user) {
        //                 $q->where('assigned_to', $user->id)
        //                     ->orWhere('created_by', $user->id);
        //             })
        //             ->orderByDesc('follow_up_datetime')
        //             ->limit(10)
        //             ->get();
        //     } catch (\Throwable $e) {
        //     }
        // }

        // $totalMeetings = 0;
        // $recentMeetings = collect();
        // if ($hasMeetingModel) {
        //     try {
        //         $totalMeetings = \App\Models\Meeting::active()
        //             ->where('branch_id', $user->branch_id)
        //             ->where(function ($q) use ($user) {
        //                 $q->where('assigned_to', $user->id)
        //                     ->orWhere('created_by', $user->id);
        //             })
        //             ->count();

        //         $recentMeetings = \App\Models\Meeting::with(['customer:id,name', 'assignedUser:id,name'])
        //             ->active()
        //             ->where('branch_id', $user->branch_id)
        //             ->where(function ($q) use ($user) {
        //                 $q->where('assigned_to', $user->id)
        //                     ->orWhere('created_by', $user->id);
        //             })
        //             ->orderByDesc('scheduled_on')
        //             ->limit(10)
        //             ->get();
        //     } catch (\Throwable $e) {
        //     }
        // }

        $BranchID = $user->branch_id ?? $user->id;

        $settings = DB::table('settings')->where('branch_id', $BranchID)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $salesChartThisMonth = $this->getSalesDataByMonth($currentMonth, $BranchID, $user->id);
        $purchaseChartThisMonth = $this->getPurchaseDataByMonth($currentMonth, $BranchID, $user->id);

        $previousYear = $currentYear - 1;
        $salesChartthisyear = $this->getSalesDataByYear($currentYear, $BranchID, $user->id);
        $salesChartpreviousyear = $this->getSalesDataByYear($previousYear, $BranchID, $user->id);

        $purchaseChartthisyear = $this->getPurchaseDataByYear($currentYear, $BranchID, $user->id);
        $purchaseChartpreviousyear = $this->getPurchaseDataByYear($previousYear, $BranchID, $user->id);

        $latestSales = collect();
        $totalSalesAmount = 0;
        $completedSalesCount = 0;
        $partiallyPaidSalesCount = 0;
        $pendingSalesCount = 0;
        if (app('hasPermission')(2, 'view')) {
            $totalSalesAmount = \App\Models\Order::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->sum('total_amount');

            $salesInvoiceCount = \App\Models\Order::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->count();

            $completedSalesQuery = \App\Models\Order::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->whereIn('payment_status', ['Completed', 'Paid', 'completed', 'paid']);
            $completedSalesCount = $completedSalesQuery->count();
            $completedSalesAmount = $completedSalesQuery->sum('total_amount');

            $partiallyPaidSalesQuery = \App\Models\Order::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->whereIn('payment_status', ['Partially', 'partially', 'Partially Paid']);
            $partiallyPaidSalesCount = $partiallyPaidSalesQuery->count();
            $partiallyPaidSalesAmount = $partiallyPaidSalesQuery->sum('total_amount') - $partiallyPaidSalesQuery->sum('remaining_amount');

            $pendingSalesQuery = \App\Models\Order::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->whereIn('payment_status', ['Pending', 'pending', 'Unpaid', 'unpaid']);
            $pendingSalesCount = $pendingSalesQuery->count();
            
            // Total Pending Amount = sum of remaining_amount for ALL incomplete orders (Pending + Partially Paid)
            $pendingSalesAmount = \App\Models\Order::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->whereIn('payment_status', ['Pending', 'pending', 'Unpaid', 'unpaid', 'Partially', 'partially', 'Partially Paid'])
                ->sum('remaining_amount');

            $latestSales = DB::table('orders')
                ->where('orders.isDeleted', '!=', 1)
                ->where('orders.branch_id', $BranchID)
                ->where('orders.created_by', $user->id)
                ->joinSub(
                    DB::table('order_items')
                        ->selectRaw('MIN(id) as id, order_id')
                        ->where('isDeleted', '!=', 1)
                        ->groupBy('order_id'),
                    'oi',
                    function ($join) {
                        $join->on('orders.id', '=', 'oi.order_id');
                    }
                )
                ->join('order_items', 'order_items.id', '=', 'oi.id')
                ->join('products', function ($join) {
                    $join->on('order_items.product_id', '=', 'products.id')
                        ->where('products.isDeleted', '!=', 1);
                })
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'orders.id as order_id',
                    'orders.order_number',
                    'orders.created_at as order_date',
                    'orders.total_amount',
                    'orders.payment_method',
                    'orders.payment_status',
                    'users.name as customer_name',
                    'products.id as product_id',
                    'products.name as product_name',
                    'products.SKU as product_code',
                    'products.images',
                    'categories.name as category_name'
                )
                ->orderBy('orders.created_at', 'desc')
                ->limit(4)
                ->get();
        }

        $latestPurchases = collect();
        $totalPurchaseAmount = 0;
        $completedPurchaseCount = 0;
        $partiallyPaidPurchaseCount = 0;
        $pendingPurchaseCount = 0;
        if (app('hasPermission')(3, 'view')) {
            $totalPurchaseAmount = \App\Models\PurchaseInvoice::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->sum('grand_total');

            $purchaseInvoiceCount = \App\Models\PurchaseInvoice::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->count();

            $completedPurchaseQuery = \App\Models\PurchaseInvoice::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->whereIn('status', ['Completed', 'Paid', 'completed', 'paid']);
            $completedPurchaseCount = $completedPurchaseQuery->count();
            $completedPurchaseAmount = $completedPurchaseQuery->sum('grand_total');

            $partiallyPaidPurchaseQuery = \App\Models\PurchaseInvoice::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->whereIn('status', ['Partially', 'partially', 'Partially Paid']);
            $partiallyPaidPurchaseCount = $partiallyPaidPurchaseQuery->count();
            $partiallyPaidPurchaseAmount = $partiallyPaidPurchaseQuery->sum('grand_total') - $partiallyPaidPurchaseQuery->sum('remaining_amount');

            $pendingPurchaseQuery = \App\Models\PurchaseInvoice::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->whereIn('status', ['Pending', 'pending', 'Unpaid', 'unpaid']);
            $pendingPurchaseCount = $pendingPurchaseQuery->count();
            
            // Total Pending Amount = sum of remaining_amount for ALL incomplete purchases (Pending + Partially Paid)
            $pendingPurchaseAmount = \App\Models\PurchaseInvoice::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('created_by', $user->id)
                ->whereIn('status', ['Pending', 'pending', 'Unpaid', 'unpaid', 'Partially', 'partially', 'Partially Paid'])
                ->sum('remaining_amount');

            $latestPurchases = DB::table('purchase_invoice as pi')
                ->where('pi.isDeleted', '!=', 1)
                ->where('pi.branch_id', '=', $BranchID)
                ->where('pi.created_by', $user->id)
                ->joinSub(
                    DB::table('purchases')
                        ->selectRaw('MIN(id) as id, invoice_id')
                        ->where('isDeleted', '!=', 1)
                        ->groupBy('invoice_id'),
                    'p',
                    function ($join) {
                        $join->on('pi.id', '=', 'p.invoice_id');
                    }
                )
                ->join('purchases as pur', 'pur.id', '=', 'p.id')
                ->leftJoin('products as pr', function ($join) {
                    $join->on('pr.id', '=', 'pur.item')
                        ->where('pr.isDeleted', '!=', 1);
                })
                ->leftJoin('categories as cat', 'pr.category_id', '=', 'cat.id')
                ->leftJoin('users as vendor_user', 'pi.vendor_id', '=', 'vendor_user.id')
                ->select(
                    'pi.id as invoice_id',
                    'pi.invoice_number',
                    'pi.bill_no',
                    'pi.grand_total',
                    'pi.status as purchase_status',
                    'pi.created_at as purchase_date',
                    'vendor_user.name as vendor_name',
                    'pr.id as product_id',
                    'pr.name as product_name',
                    'pr.SKU as product_code',
                    'pr.images',
                    'cat.name as category_name'
                )
                ->limit(4)
                ->get();
        }

        $latestDeliveries = collect();
        if (app('hasPermission')(2, 'view')) {
            $latestDeliveries = DB::table('orders')
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->leftJoin('users as staff', 'orders.assigned_staff_id', '=', 'staff.id')
                ->where('orders.isDeleted', '!=', 1)
                ->where('orders.branch_id', $BranchID)
                ->where('orders.assigned_staff_id', $user->id)
                ->where('orders.order_type', 'delivery')
                ->select(
                    'orders.id as order_id',
                    'orders.order_number',
                    'orders.delivery_status',
                    'orders.assigned_staff_id',
                    'users.name as customer_name',
                    'staff.name as staff_name'
                )
                ->orderBy('orders.id', 'desc')
                ->limit(4)
                ->get();
        }

        return view('staff_dashboard', compact(
            'user',
            'totalPresent',
            'totalLeave',
            'todayAttendance',
            'todayLeave',
            'todayCheckInTime',
            'latestSales',
            'totalSalesAmount',
            'salesInvoiceCount',
            'completedSalesCount',
            'completedSalesAmount',
            'partiallyPaidSalesCount',
            'partiallyPaidSalesAmount',
            'pendingSalesCount',
            'pendingSalesAmount',
            'latestPurchases',
            'latestDeliveries',
            'totalPurchaseAmount',
            'purchaseInvoiceCount',
            'completedPurchaseCount',
            'completedPurchaseAmount',
            'partiallyPaidPurchaseCount',
            'partiallyPaidPurchaseAmount',
            'pendingPurchaseCount',
            'pendingPurchaseAmount',
            'currencySymbol',
            'currencyPosition',
            'salesChartThisMonth',
            'purchaseChartThisMonth',
            'salesChartthisyear',
            'salesChartpreviousyear',
            'purchaseChartthisyear',
            'purchaseChartpreviousyear',
            'todayCheckOutTime',
            'todayCheckInTimestamp',
            'todayStatus',
            'todayStatusLabel',
            'todayWorkingHours',
            'todayWorkingProgress',
            'todayIsWorking',
            // 'totalFollowUps',
            // 'totalMeetings',
            // 'recentFollowUps',
            // 'recentMeetings',
            'monthName',
            'currentMonth',
            'currentYear'
        ));
    }

}
