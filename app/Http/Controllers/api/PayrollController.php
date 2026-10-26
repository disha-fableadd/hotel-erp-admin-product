<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceModel;
use App\Models\CompanyRulesModel;
use App\Models\HolidayCalendarModel;
use App\Models\LeaveModel;
use App\Models\LeaveTypeModel;
use App\Models\PayrollModel;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function page(?int $id = null): View
    {
        return view('payroll.payroll', [
            'employees' => $this->payrollUsers()->map(fn($user) => [
                'id' => $user->id,
                'username' => $user->name,
            ])->values()->all(),
            'leaveTypes' => LeaveTypeModel::query()->orderBy('leave_type')->get()->map(fn($type) => [
                'id' => $type->id,
                'leave_type' => $type->leave_type,
            ])->all(),
            'payrollId' => $id,
        ]);
    }

    public function display(): View
    {
        return view('payroll.view');
    }

    public function index(Request $request): JsonResponse
    {
        return $this->getAll($request);
    }

    public function getAll(Request $request): JsonResponse
    {
        $month = $request->query('month');
        $query = PayrollModel::query()
            ->from('payroll')
            ->leftJoin('users', 'users.id', '=', 'payroll.user_id')
            ->leftJoin('user_details', 'user_details.user_id', '=', 'payroll.user_id')
            ->select([
                'payroll.*',
                DB::raw('payroll.user_id as employee_id'),
                DB::raw($this->userDisplayNameSql() . ' as username'),
                DB::raw('users.profile_image as profile_image'),
            ])
            ->orderByDesc('payroll.payment_date')
            ->orderByDesc('payroll.id');

        if ($month) {
            $query->where('payroll.month_year', $month);
        }

        $user = $this->currentUser();
        $role = strtolower($user->role ?? '');

        if ($role === 'staff') {
            // Staff can only see their own payroll
            $query->where('payroll.user_id', $user->id);
        } elseif (in_array($role, ['admin', 'hr', 'sub-admin'])) {
            // selectedSubAdminId comes from localStorage on the frontend, sent as a request param
            $selectedSubAdminId = $request->input('selectedSubAdminId') ?? session('selectedSubAdminId');
            $branchId = !empty($selectedSubAdminId)
                ? (int) $selectedSubAdminId
                : ($user->branch_id ?: $user->id);

            $query->where('payroll.branch_id', $branchId);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
        ]);
    }

    public function getByEmployee(int $employeeId): JsonResponse
    {
        $record = PayrollModel::query()->find($employeeId);

        if (! $record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payroll record not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [$record],
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $this->validatePayroll($request);

        $existing = PayrollModel::query()
            ->where('user_id', $data['user_id'])
            ->where('month_year', $data['month_year'])
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'This payroll record already exists for the selected month.',
            ], 422);
        }

        $payload = $this->buildPayrollPayload($request, $data);
        $record = PayrollModel::query()->create($payload);

        // Update advance payments table if deduction was made
        if ($record->bonuses > 0) {
            $this->deductFromAdvances($record->user_id, $record->bonuses);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payroll record added successfully.',
            'data' => $record,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = PayrollModel::query()->find($id);
        if (! $record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payroll record not found.',
            ], 404);
        }

        $data = $this->validatePayroll($request, $id);
        $oldAdvance = (float) $record->bonuses;
        $payload = $this->buildPayrollPayload($request, $data, $record);
        $newAdvance = (float) $payload['bonuses'];

        $record->fill($payload)->save();

        // If advance deduction changed, we need to adjust the advance_payments records
        if ($oldAdvance != $newAdvance) {
            // "Undo" the old deduction first by passing negative
            $this->deductFromAdvances($record->user_id, -$oldAdvance);
            // Apply the new deduction
            if ($newAdvance > 0) {
                $this->deductFromAdvances($record->user_id, $newAdvance);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payroll record updated successfully.',
            'data' => $record->fresh(),
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        $record = PayrollModel::query()->find($id);
        if (! $record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payroll record not found.',
            ], 404);
        }

        if ($record->bonuses > 0) {
            $this->deductFromAdvances($record->user_id, -$record->bonuses);
        }

        $record->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payroll record deleted successfully.',
        ]);
    }

    public function getSalary(Request $request): JsonResponse
    {
        $userId = (int) $request->query('user_id');
        $details = DB::table('user_details')->where('user_id', $userId)->first();
        $rules = CompanyRulesModel::query()->first();

        $overtimeMultiplier = 1;
        if ($details && $details->department_id) {
            $department = DB::table('department')->where('id', $details->department_id)->first();
            if ($department && $department->enable_overtime) {
                $overtimeMultiplier = (float) ($department->overtime_multiplier ?? 1);
            }
        }

        // Fetch total pending advance balance (Amount - Already Paid) for this staff member
        $advanceSummary = DB::table('advance_payments')
            ->where('staff_id', $userId)
            ->where('isDeleted', 0)
            ->whereIn('status', ['pending', 'Pending', 'approved', 'Approved'])
            ->select(DB::raw('SUM(amount) as total_amount'), DB::raw('SUM(paid_amount) as total_paid'))
            ->first();

        $advancePayment = max(0, (float)($advanceSummary->total_amount ?? 0) - (float)($advanceSummary->total_paid ?? 0));

        // Fetch tax deduction settings (from general settings page)
        $branchId = $this->currentUser()?->branch_id;
        $branchSettings = $branchId ? Setting::where('branch_id', $branchId)->first() : null;
        if (!$branchSettings) {
            $branchSettings = Setting::first();
        }
        $taxDeductionAmount  = (float) ($branchSettings?->tax_deduction_amount ?? 0);
        $salaryAmountExceeds = (float) ($branchSettings?->salary_amount_exceeds ?? 0);

        return response()->json([
            'salary' => (float) ($details->salary ?? 0),
            'tax' => (float) ($rules?->tax ?? 0),
            'salary_above_tax' => (float) ($rules?->salary_above_tax ?? 0),
            'acc_number' => '',
            'bank_name' => '',
            'ifsc_code' => '',
            'acc_in_name' => '',
            'branch_name' => '',
            'branch_code' => '',
            'company_rules' => $rules,
            'overtime_multiplier' => $overtimeMultiplier,
            'advance_payment' => $advancePayment,
            'tax_deduction_amount'  => $taxDeductionAmount,
            'salary_amount_exceeds' => $salaryAmountExceeds,
        ]);
    }

    public function profilePage(int $id): View
    {
        return view('payroll.profile', ['payrollId' => $id]);
    }

    public function getProfile(int $id): JsonResponse
    {
        $record = PayrollModel::query()
            ->from('payroll')
            ->leftJoin('users', 'users.id', '=', 'payroll.user_id')
            ->leftJoin('user_details', 'user_details.user_id', '=', 'payroll.user_id')
            ->leftJoin('designation', 'designation.id', '=', 'user_details.designation_id')
            ->leftJoin('leave_type', 'leave_type.id', '=', 'payroll.leave_type')
            ->select([
                'payroll.*',
                DB::raw('users.name as firstname'),
                DB::raw('\'\' as lastname'),
                DB::raw('users.email as email'),
                DB::raw('users.id as employee_id'),
                DB::raw('users.profile_image as profile_image'),
                DB::raw('COALESCE(designation.designation_name, \'\') as designation'),
                DB::raw('COALESCE(leave_type.leave_type, \'\') as leave_type'),
            ])
            ->where('payroll.id', $id)
            ->first();

        if (! $record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payroll record not found.',
            ], 404);
        }

        $rules = CompanyRulesModel::query()->first();
        $attendanceSummary = null;
        $calculationError = null;

        try {
            if (! empty($record->month_year) && ! empty($record->user_id)) {
                [$start, $end] = $this->monthBoundsFromString($record->month_year);
                $attendanceSummary = $this->buildMonthlySummary(
                    $record->user_id,
                    $start,
                    $end,
                    (float) ($record->salary_amount ?? 0),
                    (float) ($record->used_paid_leaves ?? 0),
                    $rules
                );
            }
        } catch (\Throwable $exception) {
            $calculationError = $exception->getMessage();
            \Log::warning('Payroll summary calculation failed for payroll ID ' . $id . ': ' . $calculationError);
            $attendanceSummary = null;
        }

        return response()->json([
            'status' => 'success',
            'data' => $record,
            'tax' => (float) ($rules?->tax ?? 0),
            'salary_above_tax' => (float) ($rules?->salary_above_tax ?? 0),
            'working_days' => $attendanceSummary['working_days'] ?? 0,
            'worked_hours' => $attendanceSummary['worked_hours'] ?? (float) ($record->worked_hours ?? 0),
            'debug' => ['calculation_error' => $calculationError],
        ]);
    }

    public function getMonthLeaves(Request $request): JsonResponse
    {
        [$start, $end] = $this->monthBounds((int) $request->input('year'), (int) $request->input('month'));
        $userId = (int) $request->input('user_id');

        $leaveDates = $this->approvedLeaveDates($userId, $start, $end);
        $attendanceTable = (new AttendanceModel())->getTable();
        $halfDays = DB::table($attendanceTable)
            ->where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where(function ($query) {
                $query->where('status', 'H')
                    ->orWhere('status', 'HP')
                    ->orWhere('status', 'half-day');
            })
            ->count();

        return response()->json([
            'status' => 'success',
            'total_leaves' => count($leaveDates),
            'total_half_day_leaves' => $halfDays,
        ]);
    }

    public function getWorkedHours(Request $request): JsonResponse
    {
        [$start, $end] = $this->monthBounds((int) $request->input('year'), (int) $request->input('month'));
        $userId = (int) $request->input('user_id');

        $rows = AttendanceModel::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get(['work_hours', 'overtime']);

        $worked = $rows->sum(fn($row) => $this->timeToHours($row->work_hours));
        $overtime = $rows->sum(fn($row) => $this->timeToHours($row->overtime));

        return response()->json([
            'status' => 'success',
            'total_worked_hours' => round($worked, 2),
            'total_overtime_hours' => round($overtime, 2),
        ]);
    }

    public function getWorkingDays(Request $request): JsonResponse
    {
        [$start, $end] = $this->monthBounds((int) $request->input('year'), (int) $request->input('month'));

        return response()->json([
            'status' => 'success',
            'working_days' => $this->calendarDays($start, $end),
        ]);
    }

    public function calculatePayroll(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id');
        [$start, $end] = $this->monthBounds((int) $request->input('year'), (int) $request->input('month'));
        $salaryAmount = (float) $request->input('salary_amount', 0);
        $usedPaidLeaves = (float) $request->input('usedPaidLeaves', 0);
        $rules = CompanyRulesModel::query()->first();
        $summary = $this->buildMonthlySummary($userId, $start, $end, $salaryAmount, $usedPaidLeaves, $rules);

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }

    public function getLeaveDetails(Request $request): JsonResponse
    {
        $leaveType = LeaveTypeModel::query()->find((int) $request->input('leave_id'));
        if (! $leaveType) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave type not found.',
            ], 404);
        }

        $usedLeaves = PayrollModel::query()
            ->where('user_id', (int) $request->input('user_id'))
            ->sum('used_paid_leaves');

        $totalLeaves = (float) ($leaveType->number_of_leaves ?? 0);
        $remainingLeaves = max($totalLeaves - $usedLeaves, 0);

        return response()->json([
            'status' => 'success',
            'total_leaves' => $totalLeaves,
            'used_leaves' => (float) $usedLeaves,
            'remaining_leaves' => $remainingLeaves,
            'allow_half_day' => (bool) $leaveType->allow_half_day,
        ]);
    }

    public function getRemainingPaidLeaves(Request $request, int $userId): JsonResponse
    {
        $leaveTypeId = (int) $request->input('leave_id');
        $leaveType = LeaveTypeModel::query()->find($leaveTypeId);
        $usedLeaves = PayrollModel::query()->where('user_id', $userId)->sum('used_paid_leaves');

        return response()->json([
            'status' => 'success',
            'remaining_paid_leaves' => max(((float) ($leaveType?->number_of_leaves ?? 0)) - $usedLeaves, 0),
        ]);
    }

    public function downloadMultiple(Request $request)
    {
        $payrollIds = array_filter((array) $request->input('employee_ids', []));

        if (empty($payrollIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No payroll IDs supplied.',
            ], 422);
        }

        return $this->downloadMultipleByIds(new Request(['payroll_ids' => $payrollIds]));
    }

    public function downloadMultipleByIds(Request $request)
    {
        $payrollIds = array_map('intval', (array) $request->input('payroll_ids', []));

        if (empty($payrollIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No payroll records selected.',
            ], 422);
        }

        $records = PayrollModel::query()->whereIn('id', $payrollIds)->orderBy('payment_date')->get();
        if ($records->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payroll records not found.',
            ], 404);
        }

        $html = '';
        $recordsCount = $records->count();

        foreach ($records as $index => $record) {
            $html .= $this->renderSlipHtml($record);
            if ($index < $recordsCount - 1) {
                $html .= '<div style="page-break-after: always; page-break-inside: avoid;"></div>';
            }
        }

        $pdf = Pdf::loadHTML($html)->setPaper('a4');

        return $pdf->download('salary-slips-' . now()->format('YmdHis') . '.pdf');
    }

    public function downloadSlip(int $id)
    {
        $record = PayrollModel::query()->find($id);
        if (! $record) {
            abort(404, 'Payroll record not found.');
        }

        return Pdf::loadHTML($this->renderSlipHtml($record))
            ->setPaper('a4')
            ->download('salary-slip-' . $record->id . '.pdf');
    }

    public function save(Request $request): JsonResponse
    {
        $record = $this->upsertMonthlyPayrollFromSummary([
            'user_id' => (int) $request->input('employee_id'),
            'month_year' => (string) $request->input('month'),
            'salary_amount' => (float) $request->input('salary'),
            'total_leaves' => (float) $request->input('leaves', 0),
            'total_half_day' => (float) $request->input('half_day', 0),
            'used_paid_leaves' => (float) $request->input('paid_leave', 0),
            'salary_deduction' => (float) $request->input('deduction', 0),
            'net_salary' => (float) $request->input('net_salary', 0),
            'overtime_pay' => (float) $request->input('overtime_pay', 0),
            'total_overtime_hours' => (float) $request->input('total_overtime_hours', 0),
            'bonuses' => (float) $request->input('advance_payment', 0),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payroll saved successfully.',
            'data' => $record,
        ]);
    }

    public function groupsalaryPage(): RedirectResponse
    {
        return redirect()->route('payroll.salary-details', [
            'month' => now()->subMonth()->format('Y-m'),
        ]);
    }

    public function salaryDetails(Request $request): View
    {
        $month = $request->query('month', now()->subMonth()->format('Y-m'));
        [$start, $end] = $this->monthBoundsFromString($month);
        $rules = CompanyRulesModel::query()->first();

        $employees = $this->payrollUsers()->map(function ($user) use ($start, $end, $month, $rules) {
            $salary = (float) ($user->detail_salary ?? 0);
            $summary = $this->buildMonthlySummary($user->id, $start, $end, $salary, 0, $rules);
            $existing = PayrollModel::query()
                ->where('user_id', $user->id)
                ->where('month_year', $month)
                ->first();

            // Fetch department overtime multiplier
            $overtimeMultiplier = 1.0;
            $userDetail = DB::table('user_details')->where('user_id', $user->id)->first();
            if ($userDetail && $userDetail->department_id) {
                $dept = DB::table('department')->where('id', $userDetail->department_id)->first();
                if ($dept && $dept->enable_overtime) {
                    $overtimeMultiplier = (float) ($dept->overtime_multiplier ?? 1);
                }
            }

            // Fetch total pending advance balance (Amount - Already Paid) for this staff member
            $advanceSummary = DB::table('advance_payments')
                ->where('staff_id', $user->id)
                ->where('isDeleted', 0)
                ->whereIn('status', ['pending', 'Pending', 'approved', 'Approved'])
                ->select(DB::raw('SUM(amount) as total_amount'), DB::raw('SUM(paid_amount) as total_paid'))
                ->first();

            $pendingAdvance = max(0, (float)($advanceSummary->total_amount ?? 0) - (float)($advanceSummary->total_paid ?? 0));

            return [
                'id' => $user->id,
                'user_id' => $user->id,
                'firstname' => $user->first_name ?: $user->name,
                'profile_image' => $user->profile_image,
                'salary' => $salary,
                'present_days' => $summary['present_day_count'],
                'leaves' => $summary['total_days'] - $summary['present_day_count'],
                'half_days' => $summary['half_days'],
                'used_paid_leaves' => $existing?->used_paid_leaves ?? 0,
                'salary_deduction' => $existing?->salary_deduction ?? $summary['salary_deduction'],
                'net_salary' => $existing?->net_salary ?? $summary['net_salary'],
                'tax' => number_format((float) ($existing?->tax_deduction ?? $summary['tax_deduction']), 2),
                'tax_amount' => (float) ($existing?->tax_deduction ?? $summary['tax_deduction']),
                'days_in_month' => $end->day,
                'per_day' => $summary['per_day_salary'],
                'per_hour' => $summary['per_hour_salary'],
                'overtime_pay' => $existing?->overtime_pay ?? $summary['overtime_pay'],
                'total_overtime_hours' => $existing?->total_overtime_hours ?? $summary['total_overtime_hours'],
                'overtime_multiplier' => $overtimeMultiplier,
                'late_deduction' => $summary['late_deduction'],
                'base_deduction' => $summary['salary_deduction'],
                'advance_payment' => $existing?->bonuses ?? $pendingAdvance,
                'is_saved' => (bool) $existing,
            ];
        })->values()->all();

        return view('payroll.salary-details', [
            'month' => $month,
            'employees' => $employees,
        ]);
    }

    public function saveAll(Request $request)
    {
        $employeeIds = (array) $request->input('employee_id', []);
        $salaries = (array) $request->input('salary', []);
        $leaves = (array) $request->input('leaves', []);
        $halfDays = (array) $request->input('half_day', []);
        $paidLeaves = (array) $request->input('paid_leave', []);
        $deductions = (array) $request->input('deduction', []);
        $netSalaries = (array) $request->input('net_salary', []);
        $overtimePays = (array) $request->input('overtime_pay', []);
        $totalOvertimeHours = (array) $request->input('total_overtime_hours', []);
        $month = (string) $request->input('month', now()->format('Y-m'));

        foreach ($employeeIds as $index => $employeeId) {
            $this->upsertMonthlyPayrollFromSummary([
                'user_id' => (int) $employeeId,
                'month_year' => $month,
                'salary_amount' => (float) ($salaries[$index] ?? 0),
                'total_leaves' => (float) ($leaves[$index] ?? 0),
                'total_half_day' => (float) ($halfDays[$index] ?? 0),
                'used_paid_leaves' => (float) ($paidLeaves[$index] ?? 0),
                'salary_deduction' => (float) ($deductions[$index] ?? 0),
                'net_salary' => (float) ($netSalaries[$index] ?? 0),
                'overtime_pay' => (float) ($overtimePays[$index] ?? 0),
                'total_overtime_hours' => (float) ($totalOvertimeHours[$index] ?? 0),
                'bonuses' => (float) ($request->input('advance_payment')[$index] ?? 0),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'All salaries saved successfully'
            ]);
        }
        return redirect()->route('payroll.list', ['month' => $month]);
    }

    public function getDeductionBreakdown(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id');
        $month = (string) $request->input('month');
        [$start, $end] = $this->monthBoundsFromString($month);

        $details = DB::table('user_details')->where('user_id', $userId)->first();
        $user = User::query()->find($userId);
        $salary = (float) ($details->salary ?? 0);
        $rules = CompanyRulesModel::query()->first();
        $summary = $this->buildMonthlySummary($userId, $start, $end, $salary, 0, $rules);

        return response()->json([
            'status' => 'success',
            'data' => [
                'employee_name' => $user?->name ?? 'Employee',
                'month_label' => $start->format('F Y'),
                'leaves' => [
                    'count' => count($summary['leave_dates']),
                    'dates' => $summary['leave_dates'],
                    'deduction_amount' => $summary['leave_deduction'],
                ],
                'absent' => [
                    'dates' => $summary['absent_dates'],
                    'deduction_amount' => $summary['absent_deduction'],
                ],
                'half_day' => [
                    'count' => count($summary['half_day_dates']),
                    'dates' => $summary['half_day_dates'],
                    'deduction_amount' => $summary['half_day_deduction'],
                ],
                'late' => [
                    'list' => $summary['late_dates'],
                    'deduction_amount' => $summary['late_deduction'],
                ],
                'overtime' => [
                    'list' => $summary['overtime_dates'],
                    'pay_amount' => $summary['overtime_pay'],
                ],
                'summary' => [
                    'per_day_salary' => $summary['per_day_salary'],
                    'total_deduction' => $summary['salary_deduction'],
                    'overtime_added' => $summary['overtime_pay'],
                ],
            ],
        ]);
    }

    public function savedata(Request $request): JsonResponse
    {
        return $this->save($request);
    }

    private function validatePayroll(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'user_id' => ['required', 'integer'],
            'salary_amount' => ['required', 'numeric'],
            'net_salary' => ['required', 'numeric'],
            'payment_date' => ['nullable', 'date'],
            'payment_status' => ['nullable', 'string'],
            'month_year' => ['required', 'date_format:Y-m'],
        ]);
    }

    private function buildPayrollPayload(Request $request, array $validated, ?PayrollModel $record = null): array
    {
        $currentUser = $this->currentUser();
        $staffBranchId = User::query()->where('id', (int) $validated['user_id'])->value('branch_id');

        return [
            'branch_id' => $staffBranchId ?? $currentUser?->branch_id,
            'user_id' => (int) $validated['user_id'],
            'leave_type' => $request->input('leave_type'),
            'remaining_paid_leaves' => (float) $request->input('remaining_paid_leaves', 0),
            'month_year' => $validated['month_year'],
            'total_leaves' => (float) $request->input('total_leaves', 0),
            'total_half_day' => (float) $request->input('total_halfday_leaves', $request->input('total_half_day', 0)),
            'total_paid_leaves' => (float) $request->input('total_paid_leaves', 0),
            'used_paid_leaves' => (float) $request->input('used_paid_leaves', 0),
            'salary_amount' => (float) $validated['salary_amount'],
            'acc_number' => $request->input('acc_number'),
            'bank_name' => $request->input('bank_name'),
            'ifsc_code' => $request->input('ifsc_code'),
            'acc_in_name' => $request->input('acc_in_name'),
            'branch_name' => $request->input('branch_name'),
            'branch_code' => $request->input('branch_code'),
            'tax_deduction' => (float) $request->input('tax_deduction', 0),
            'salary_deduction' => (float) $request->input('salary_deduction', 0),
            'bonuses' => (float) $request->input('advance_payment', $request->input('bonuses', 0)),
            'net_salary' => (float) $validated['net_salary'],
            'payment_date' => $validated['payment_date'] ?? ($record?->payment_date ?? now()->toDateString()),
            'payment_status' => $validated['payment_status'] ?? ($record?->payment_status ?? 'paid'),
            'created_by' => $record?->created_by ?? $currentUser?->id,
            'worked_hours' => (float) $request->input('worked_hours', 0),
            'overtime_pay' => (float) $request->input('overtime_pay', 0),
            'total_overtime_hours' => (float) $request->input('total_overtime_hours', 0),
        ];
    }

    private function upsertMonthlyPayrollFromSummary(array $data): PayrollModel
    {
        [$start, $end] = $this->monthBoundsFromString($data['month_year']);
        $rules = CompanyRulesModel::query()->first();
        $summary = $this->buildMonthlySummary(
            $data['user_id'],
            $start,
            $end,
            (float) $data['salary_amount'],
            (float) $data['used_paid_leaves'],
            $rules
        );

        $record = PayrollModel::query()->firstOrNew([
            'user_id' => $data['user_id'],
            'month_year' => $data['month_year'],
        ]);

        $staffBranchId = User::query()->where('id', (int) $data['user_id'])->value('branch_id');
        $oldAdvance = (float) $record->bonuses;
        $record->fill([
            'branch_id' => $staffBranchId ?? $this->currentUser()?->branch_id,
            'user_id' => $data['user_id'],
            'month_year' => $data['month_year'],
            'salary_amount' => $data['salary_amount'],
            'total_leaves' => $data['total_leaves'],
            'total_half_day' => $data['total_half_day'],
            'total_paid_leaves' => (float) ($summary['available_paid_leaves'] ?? 0),
            'used_paid_leaves' => $data['used_paid_leaves'],
            'remaining_paid_leaves' => max(($summary['available_paid_leaves'] ?? 0) - $data['used_paid_leaves'], 0),
            'salary_deduction' => $data['salary_deduction'],
            'tax_deduction' => $summary['tax_deduction'],
            'net_salary' => $data['net_salary'],
            'overtime_pay' => $data['overtime_pay'],
            'total_overtime_hours' => $data['total_overtime_hours'],
            'bonuses' => $data['bonuses'] ?? 0,
            'payment_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'worked_hours' => $summary['worked_hours'],
            'created_by' => $record->created_by ?: (Auth::id() ?: $data['user_id']),
        ]);

        $record->save();
        $newAdvance = (float) $record->bonuses;

        if ($oldAdvance != $newAdvance) {
            $this->deductFromAdvances($record->user_id, $newAdvance - $oldAdvance);
        }

        return $record->fresh();
    }

    private function deductFromAdvances(int $userId, float $amount): void
    {
        if ($amount > 0) {
            // Deduct from pending advances (oldest first)
            $advances = DB::table('advance_payments')
                ->where('staff_id', $userId)
                ->where('isDeleted', 0)
                ->whereIn('status', ['pending', 'Pending', 'approved', 'Approved'])
                ->orderBy('date', 'asc')
                ->get();

            $remainingToDeduct = $amount;
            foreach ($advances as $advance) {
                if ($remainingToDeduct <= 0) break;

                $pendingInThisRecord = (float) $advance->amount - (float) $advance->paid_amount;
                if ($pendingInThisRecord <= 0) continue;

                $deductNow = min($remainingToDeduct, $pendingInThisRecord);
                DB::table('advance_payments')
                    ->where('id', $advance->id)
                    ->update(['paid_amount' => (float) $advance->paid_amount + $deductNow]);

                $remainingToDeduct -= $deductNow;
            }
        } elseif ($amount < 0) {
            // "Undo" logic: Add back to paid_amount (newest first)
            $toRestore = abs($amount);
            $advances = DB::table('advance_payments')
                ->where('staff_id', $userId)
                ->where('isDeleted', 0)
                ->orderBy('date', 'desc')
                ->get();

            foreach ($advances as $advance) {
                if ($toRestore <= 0) break;

                $canRestore = (float) $advance->paid_amount;
                if ($canRestore <= 0) continue;

                $restoreNow = min($toRestore, $canRestore);
                DB::table('advance_payments')
                    ->where('id', $advance->id)
                    ->update(['paid_amount' => (float) $advance->paid_amount - $restoreNow]);

                $toRestore -= $restoreNow;
            }
        }
    }

    private function buildMonthlySummary(
        int $userId,
        Carbon $start,
        Carbon $end,
        float $salaryAmount,
        float $usedPaidLeaves,
        ?CompanyRulesModel $rules
    ): array {
        $workingDays = max($this->calendarDays($start, $end), 1);
        // $workingDays = 0;

        // foreach (CarbonPeriod::create($start, $end) as $date) {
        //     if ($this->isWorkingDay($date, null, $holidayDates->all())) {
        //         $workingDays++;
        //     }
        // }
        // $workingDays = max($workingDays, 1);
        $perDaySalary = $salaryAmount > 0 ? round($salaryAmount / $workingDays, 2) : 0;
        $perHourSalary = $perDaySalary > 0 ? round($perDaySalary / max((float) ($rules?->working_hours_per_day ?? 8), 1), 2) : 0;

        $attendanceRows = AttendanceModel::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $approvedLeaveDates = collect($this->approvedLeaveDates($userId, $start, $end));
        $holidayDates = collect(HolidayCalendarModel::query()
            ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->pluck('holiday_date')
            ->all());

        $attendanceByDate = $attendanceRows->keyBy(fn($row) => Carbon::parse($row->date)->toDateString());
        $absentDates = [];
        $halfDayDates = [];
        $lateDates = [];
        $overtimeDates = [];
        $presentDays = 0;
        $presentDayCount = 0;
        $workedHours = 0.0;
        $totalOvertimeHours = 0.0;

        // Fetch overtime_after_hours setting for recalculation
        $branchId = $this->currentUser()?->branch_id;
        $otBranchSettings = $branchId ? Setting::where('branch_id', $branchId)->first() : null;
        if (!$otBranchSettings) {
            $otBranchSettings = Setting::first();
        }
        $overtimeAfterHours = (float) ($otBranchSettings?->overtime_after_hours ?? 0);
        $overtimeThresholdHours = $overtimeAfterHours > 0 ? $overtimeAfterHours : (float) ($rules->working_hours_per_day ?? 8);

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $dateString = $date->toDateString();
            $attendance = $attendanceByDate->get($dateString);

            if ($attendance) {
                $workedHours += $this->timeToHours($attendance->work_hours);

                // Recalculate overtime from check-in/check-out if stored overtime is 0
                $storedOvertime = $this->timeToHours($attendance->overtime);
                $recalcOvertime = 0.0;
                if ($storedOvertime <= 0 && !empty($attendance->check_in_time) && !empty($attendance->check_out_time)) {
                    try {
                        $in = Carbon::parse($dateString . ' ' . $attendance->check_in_time, 'Asia/Kolkata');
                        $out = Carbon::parse($dateString . ' ' . $attendance->check_out_time, 'Asia/Kolkata');
                        if ($out->gt($in)) {
                            $grossHours = $out->diffInSeconds($in) / 3600;
                            if ($grossHours > $overtimeThresholdHours) {
                                $recalcOvertime = $grossHours - $overtimeThresholdHours;
                            }
                        }
                    } catch (\Throwable $e) {
                        // skip recalculation on parse error
                    }
                }
                $effectiveOvertime = max($storedOvertime, $recalcOvertime);
                $totalOvertimeHours += $effectiveOvertime;

                if ($this->isHalfDayStatus($attendance->status)) {
                    $halfDayDates[] = [
                        'date' => $dateString,
                        'label' => $date->format('d M Y'),
                        'worked_text' => $attendance->work_hours ?: '00:00:00',
                        'missing_text' => number_format(max(($rules?->half_day_hours ?? 4) - $this->timeToHours($attendance->work_hours), 0), 2) . ' hrs',
                    ];
                    $presentDays += 0.5;
                    $presentDayCount++;
                } elseif ($this->isPresentStatus($attendance->status)) {
                    $presentDays += 1;
                    $presentDayCount++;
                } elseif ($this->isAbsentStatus($attendance->status) && ! $approvedLeaveDates->contains($dateString)) {
                    $absentDates[] = [
                        'date' => $dateString,
                        'label' => $date->format('d M Y'),
                    ];
                }

                if ((int) ($attendance->late_minutes ?? 0) > 0) {
                    $lateDates[] = [
                        'date' => $dateString,
                        'label' => $date->format('d M Y'),
                        'late_minutes' => (int) $attendance->late_minutes,
                        'late_text' => (int) $attendance->late_minutes . ' min',
                    ];
                }

                if ($effectiveOvertime > 0) {
                    $overtimeDates[] = [
                        'date' => $dateString,
                        'label' => $date->format('d M Y'),
                        'overtime_hours' => round($effectiveOvertime, 2),
                        'overtime_text' => $attendance->overtime ?: sprintf('%02d:%02d:00', (int) floor($effectiveOvertime), (int) round(($effectiveOvertime - floor($effectiveOvertime)) * 60)),
                    ];
                }
                continue;
            }

            if ($approvedLeaveDates->contains($dateString)) {
                continue;
            }

            // if (! $this->isWorkingDay($date, $rules, $holidayDates->all())) {
            //     continue;
            // }
            // foreach (CarbonPeriod::create($start, $end) as $date) {
            //     $dateString = $date->toDateString();
            //     $attendance = $attendanceByDate->get($dateString);

            //     if ($attendance) {

            //         if ($this->isHalfDayStatus($attendance->status)) {
            //             $presentDays += 0.5;
            //         } elseif ($this->isPresentStatus($attendance->status)) {
            //             $presentDays += 1;
            //         }

            //         $workedHours += $this->timeToHours($attendance->work_hours);
            //         $totalOvertimeHours += $this->timeToHours($attendance->overtime);
            //     }
            // }

            $absentDates[] = [
                'date' => $dateString,
                'label' => $date->format('d M Y'),
            ];
        }

        $leaveDates = $approvedLeaveDates->map(fn($date) => [
            'date' => $date,
            'label' => Carbon::parse($date)->format('d M Y'),
        ])->values()->all();

        $leaveDeduction = count($leaveDates) * $perDaySalary;
        $absentDeduction = count($absentDates) * $perDaySalary;
        $halfDayDeduction = count($halfDayDates) * ($perDaySalary / 2);
        $lateDeduction = 0.0;
        $rawDeduction = $leaveDeduction + $absentDeduction + $halfDayDeduction + $lateDeduction;
        $salaryDeduction = max($rawDeduction - ($usedPaidLeaves * $perDaySalary), 0);

        $overtimeMultiplier = 1.0;
        $overtimeRateType = 'multiplier';
        $overtimeEnabled = false;

        $userDetails = \Illuminate\Support\Facades\DB::table('user_details')->where('user_id', $userId)->first();
        $department = null;
        if ($userDetails && !empty($userDetails->department_id)) {
            $department = \Illuminate\Support\Facades\DB::table('department')->where('id', $userDetails->department_id)->first();
        }

        if ($department && $department->enable_overtime) {
            $overtimeMultiplier = (float) $department->overtime_multiplier;
            $overtimeRateType = $department->overtime_rate_type;
            $overtimeEnabled = true;
        } elseif ($rules && $rules->enable_overtime) {
            $overtimeMultiplier = (float) $rules->overtime_multiplier;
            $overtimeRateType = $rules->overtime_rate_type;
            $overtimeEnabled = true;
        }

        if (!$overtimeEnabled) {
            // If overtime is entirely disabled, you might want to award 0.
            // But to maintain backward compatibility, we'll keep the base 1x logic if they disabled it but somehow still logged OT.
            $overtimePay = round($totalOvertimeHours * $perHourSalary, 2);
        } else {
            if ($overtimeRateType === 'fixed') {
                $overtimePay = round($totalOvertimeHours * $overtimeMultiplier, 2);
            } else {
                $overtimePay = round($totalOvertimeHours * ($perHourSalary * $overtimeMultiplier), 2);
            }
        }

        $taxDeduction = $this->taxDeduction($salaryAmount, $rules);
        // Net Salary = Monthly Salary + Overtime Pay - Salary Deduction - Tax Deduction
        $netSalary = round($salaryAmount + $overtimePay - $salaryDeduction - $taxDeduction, 2);
        return [
            'total_days' => $this->calendarDays($start, $end),
            'present_day_count' => $presentDayCount,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'total_leaves' => count($leaveDates),
            'half_days' => count($halfDayDates),
            'unpaid_leaves' => max((count($leaveDates) + count($absentDates) + (count($halfDayDates) * 0.5)) - $usedPaidLeaves, 0),
            'worked_hours' => round($workedHours, 2),
            'total_overtime_hours' => round($totalOvertimeHours, 2),
            'overtime_pay' => $overtimePay,
            'per_day_salary' => round($perDaySalary, 2),
            'per_hour_salary' => round($perHourSalary, 2),
            'leave_deduction' => round($leaveDeduction, 2),
            'absent_deduction' => round($absentDeduction, 2),
            'half_day_deduction' => round($halfDayDeduction, 2),
            'late_deduction' => round($lateDeduction, 2),
            'salary_deduction' => round($salaryDeduction, 2),
            'tax_deduction' => round($taxDeduction, 2),
            'net_salary' => $netSalary,
            'leave_dates' => $leaveDates,
            'absent_dates' => $absentDates,
            'half_day_dates' => $halfDayDates,
            'earned_salary' => round($presentDays * $perDaySalary, 2),
            'late_dates' => $lateDates,
            'overtime_dates' => $overtimeDates,
            'available_paid_leaves' => (float) LeaveTypeModel::query()->max('number_of_leaves'),
        ];
    }

    private function renderSlipHtml(PayrollModel $record): string
    {
        $user = User::query()->find($record->user_id);
        $details = DB::table('user_details')->where('user_id', $record->user_id)->first();
        $designationName = '';
        $departmentName = '';

        if ($details && ! empty($details->designation_id)) {
            $designationName = (string) DB::table('designation')->where('id', $details->designation_id)->value('designation_name');
        }
        if ($details && ! empty($details->department_id)) {
            $departmentName = (string) DB::table('department')->where('id', $details->department_id)->value('department_name');
        }

        $settings = Setting::query()->first();

        $company = [
            'company_name' => $settings->name ?? config('app.name', 'Rajinfra ERP'),
            'company_address' => $settings->address ?? config('app.url'),
            'logo' => $settings ? $settings->logo_url : null,
        ];

        $slipUser = [
            'firstname' => $user?->name ?? '',
            'lastname' => '',
            'employee_id' => $record->user_id,
            'joining_date' => $details->joining_date ?? '',
        ];

        [$start, $end] = $this->monthBoundsFromString($record->month_year ?? now()->format('Y-m'));
        $rules = CompanyRulesModel::query()->first();
        $attendanceSummary = $this->buildMonthlySummary(
            $record->user_id,
            $start,
            $end,
            (float) ($record->salary_amount ?? 0),
            (float) ($record->used_paid_leaves ?? 0),
            $rules
        );

        $summary = [
            'working_days' => $attendanceSummary['working_days'],
            'present_days' => $attendanceSummary['present_days'],
            'absent_days' => max(0, $attendanceSummary['working_days'] - $attendanceSummary['present_days']),
            'total_leaves' => $record->total_leaves ?? 0,
            'used_paid_leaves' => $record->used_paid_leaves ?? 0,
            'unpaid_leaves' => $attendanceSummary['unpaid_leaves'] ?? 0,
            'half_days' => $record->total_half_day ?? 0,
            'leave_deduction' => $attendanceSummary['leave_deduction'] ?? 0,
            'absent_deduction' => $attendanceSummary['absent_deduction'] ?? 0,
            'half_day_deduction' => $attendanceSummary['half_day_deduction'] ?? 0,
            'overtime_pay' => $attendanceSummary['overtime_pay'] ?? 0,
            'tax_deduction' => $attendanceSummary['tax_deduction'] ?? 0,
            'salary_deduction' => $record->salary_deduction ?? 0,
            'total_earnings' => ($record->salary_amount ?? 0) + ($attendanceSummary['overtime_pay'] ?? 0) + ($record->bonuses ?? 0),
            'total_deductions' => ($attendanceSummary['tax_deduction'] ?? 0) + ($record->salary_deduction ?? 0),
            'net_salary' => $attendanceSummary['net_salary'] ?? $record->net_salary ?? 0,
        ];

        return view('payroll.salary_slip', [
            'company' => $company,
            'payroll' => $record->toArray(),
            'user' => $slipUser,
            'designation' => ['designation_name' => $designationName],
            'department' => ['department_name' => $departmentName],
            'calculatedData' => $summary,
        ])->render();
    }

    // private function payrollUsers(): Collection
    // {
    //     $query = User::query()
    //         ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
    //         ->select([
    //             'users.id',
    //             'users.name',
    //             'users.role',
    //             DB::raw('users.name as first_name'),
    //             DB::raw('users.profile_image as profile_image'),
    //             DB::raw('COALESCE(user_details.salary, 0) as detail_salary'),
    //         ])
    //         ->where('users.isDeleted', 0)
    //         ->whereIn('users.role', ['staff', 'hr']);

    //     $currentUser = Auth::user();
    //     if ($currentUser && $currentUser->role === 'staff') {
    //         $query->where('users.id', $currentUser->id);
    //     }

    //     return $query->orderBy('users.name')->get();
    // }

    private function payrollUsers(?Request $request = null): Collection
    {
        $query = User::query()
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->select([
                'users.id',
                'users.name',
                'users.role',
                DB::raw('users.name as first_name'),
                DB::raw('users.profile_image as profile_image'),
                DB::raw('COALESCE(user_details.salary,0) as detail_salary'),
            ])
            ->where('users.isDeleted', 0);

        $currentUser = auth()->user();
        $role = strtolower($currentUser->role ?? '');

        // selectedSubAdminId is stored in localStorage and sent as a request param
        $selectedSubAdminId = ($request?->input('selectedSubAdminId')) ?? session('selectedSubAdminId');

        if (in_array($role, ['admin', 'hr', 'sub-admin'])) {

            $branchId = !empty($selectedSubAdminId)
                ? (int)$selectedSubAdminId
                : ($currentUser->branch_id ?: $currentUser->id);

            $query->whereRaw(
                'LOWER(users.role) in (?, ?, ?)',
                ['hr', 'staff', 'employee']
            );

            $query->where('users.branch_id', $branchId);
        } else {

            $query->where('users.id', $currentUser->id);
        }

        return $query->orderBy('users.name')->get();
    }

    private function approvedLeaveDates(int $userId, Carbon $start, Carbon $end): array
    {
        $leaves = LeaveModel::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'Approved'])
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->get(['start_date', 'end_date']);

        $dates = [];
        foreach ($leaves as $leave) {
            $from = Carbon::parse($leave->start_date)->startOfDay()->max($start->copy()->startOfDay());
            $to = Carbon::parse($leave->end_date)->startOfDay()->min($end->copy()->startOfDay());
            foreach (CarbonPeriod::create($from, $to) as $date) {
                $dates[] = $date->toDateString();
            }
        }

        return array_values(array_unique($dates));
    }

    private function workingDays(Carbon $start, Carbon $end, ?CompanyRulesModel $rules): int
    {
        $holidays = HolidayCalendarModel::query()
            ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->pluck('holiday_date')
            ->all();

        $count = 0;
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if ($this->isWorkingDay($date, $rules, $holidays)) {
                $count++;
            }
        }

        return $count;
    }

    private function calendarDays(Carbon $start, Carbon $end): int
    {
        return CarbonPeriod::create($start, $end)->count();
    }

    private function isWorkingDay(Carbon $date, ?CompanyRulesModel $rules, array $holidayDates): bool
    {
        if ($rules && $rules->sunday_off && $date->isSunday()) {
            return false;
        }

        if ($rules && $rules->saturday_off_enabled && $date->isSaturday()) {
            return false;
        }

        if (in_array($date->toDateString(), $holidayDates, true)) {
            return false;
        }

        return true;
    }

    private function taxDeduction(float $salaryAmount, ?CompanyRulesModel $rules): float
    {
        // Check new Setting-based tax deduction rule first (from General Settings page)
        $branchId = $this->currentUser()?->branch_id;
        $branchSettings = $branchId ? Setting::where('branch_id', $branchId)->first() : null;
        // Fallback to first available settings row if branch-specific settings not found
        if (!$branchSettings) {
            $branchSettings = Setting::first();
        }
        $salaryAmountExceeds = (float) ($branchSettings?->salary_amount_exceeds ?? 0);
        $taxDeductionAmount  = (float) ($branchSettings?->tax_deduction_amount ?? 0);

        if ($salaryAmountExceeds > 0 && $salaryAmount >= $salaryAmountExceeds) {
            return $taxDeductionAmount;
        }

        // Fallback to CompanyRulesModel-based tax logic
        if (! $rules || ! $rules->enable_tax) {
            return 0;
        }

        $threshold = (float) ($rules->salary_above_tax ?? 0);
        if ($salaryAmount <= $threshold) {
            return 0;
        }

        if (($rules->tax_type ?? 'percentage') === 'fixed') {
            return (float) ($rules->tax ?? 0);
        }

        return round($salaryAmount * ((float) ($rules->tax ?? 0) / 100), 2);
    }

    private function monthBounds(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return [$start, $end];
    }

    private function monthBoundsFromString(string $month): array
    {
        $date = Carbon::createFromFormat('Y-m', $month, 'Asia/Kolkata')->startOfMonth();

        return [$date->copy(), $date->copy()->endOfMonth()];
    }

    private function timeToHours($value): float
    {
        if (empty($value)) {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $parts = explode(':', (string) $value);
        if (count($parts) < 2) {
            return (float) $value;
        }

        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        $seconds = (int) ($parts[2] ?? 0);

        return $hours + ($minutes / 60) + ($seconds / 3600);
    }

    private function currentUser(): ?User
    {
        if (Auth::check()) {
            return Auth::user();
        }

        try {
            return Auth::guard('api')->user();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function isHalfDayStatus(?string $status): bool
    {
        return in_array(strtolower((string) $status), ['h', 'hp', 'half-day'], true);
    }

    private function isPresentStatus(?string $status): bool
    {
        return in_array(strtolower((string) $status), ['p', '2p', 'present', 'working'], true);
    }

    private function isAbsentStatus(?string $status): bool
    {
        return in_array(strtolower((string) $status), ['a', 'absent'], true);
    }

    private function userDisplayNameSql(): string
    {
        if (Schema::hasColumn('users', 'username')) {
            return 'COALESCE(users.username, users.name)';
        }

        return 'users.name';
    }
}
