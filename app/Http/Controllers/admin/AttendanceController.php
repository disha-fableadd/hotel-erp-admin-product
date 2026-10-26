<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = User::where('role', 'Staff')->where('isDeleted', 0);

    //     if ($request->has('search')) {
    //         $query->where('name', 'like', '%' . $request->search . '%');
    //     }

    //     $staffUsers = $query->get();

    //     $monthInput = $request->input('month', now()->format('Y-m')); // e.g., "2025-08"
    //     $month = \Carbon\Carbon::parse($monthInput)->format('m'); // "08"
    //     $year = \Carbon\Carbon::parse($monthInput)->format('Y');  // "2025"

    //     $currentMonth = $monthInput; // pass "2025-08" back to view

    //     $attendances = Attendance::whereMonth('date', $month)
    //         ->whereYear('date', $year)
    //         ->get()
    //         ->groupBy(function ($item) {
    //             return $item->user_id . '_' . $item->date;
    //         });

    //     return view('attendance.view', compact('staffUsers', 'attendances', 'currentMonth', 'year', 'month'));
    // }

    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     $role = $user->role;
    //     $userBranchId = $user->id;
    //     $subAdminId = session('selectedSubAdminId');

    //     // Date range
    //     $monthInput = $request->input('month', now()->format('Y-m'));
    //     $month = \Carbon\Carbon::parse($monthInput)->format('m');
    //     $year = \Carbon\Carbon::parse($monthInput)->format('Y');
    //     $currentMonth = $monthInput;

    //     // Attendance query with month/year filter
    //     $attendanceQuery = Attendance::whereMonth('date', $month)
    //         ->whereYear('date', $year);

    //     $attendances = $attendanceQuery->get()->groupBy(function ($item) {
    //         return $item->user_id . '_' . $item->date;
    //     });

    //     // Staff query (default → all Staff)
    //     $staffQuery = User::where('role', 'Staff')
    //         ->where('isDeleted', 0);

    //     // Search filter
    //     if ($request->has('search') && !empty($request->search)) {
    //         $staffQuery->where('name', 'like', '%' . $request->search . '%');
    //     }

    //     // Branch filter logic
    //     if (!empty($subAdminId)) {
    //         $staffQuery->where('branch_id', $subAdminId);
    //     } elseif ($role == 'sub-admin') {
    //         $staffQuery->where('branch_id', $userBranchId);
    //     } else {
    //         $staffQuery->where('branch_id', $userBranchId);
    //     }

    //     $staffUsers = $staffQuery->get();

    //     return view('attendance.view', compact('staffUsers', 'attendances', 'currentMonth', 'year', 'month'));
    // }
    public function index(Request $request)
    {
        $user         = Auth::user();
        $role         = $user->role;
        $userBranchId = $user->id;
        $subAdminId   = session('selectedSubAdminId');

        // Date range
        // $monthInput = $request->input('month', now()->format('Y-m'));
        // $month = Carbon::parse($monthInput)->format('m');
        // $year = Carbon::parse($monthInput)->format('Y');
        // $currentMonth = $monthInput;

        // // ✅ Get total days in selected month
        // $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $monthInput  = $request->input('month', now()->format('Y-m'));
        $month       = Carbon::parse($monthInput)->format('m');
        $year        = Carbon::parse($monthInput)->format('Y');
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $currentMonth = $monthInput;

        // ✅ Fetch sunday_off setting for the branch
        if ($role === 'admin' && ! empty($subAdminId)) {
            $settings = Setting::where('branch_id', $subAdminId)->first();
        } else {
            $settings = Setting::where('branch_id', $userBranchId)->first();
        }

        $sundayOff = $settings->sunday_off ?? 'no'; // default 'no'

        // Attendance query with month/year filter
        $attendanceQuery = Attendance::whereMonth('date', $month)
            ->whereYear('date', $year);

        $attendances = $attendanceQuery->get()->groupBy(function ($item) {
            return $item->user_id . '_' . $item->date;
        });

        // Staff query
        $staffQuery = User::query();

        if ($role == 'staff') {
            $staffQuery->where('id', $user->id);
        } else {
            $staffQuery->where('role', 'staff')->where('isDeleted', 0);

            if ($request->has('search') && ! empty($request->search)) {
                $staffQuery->where('name', 'like', '%' . $request->search . '%');
            }

            if (! empty($subAdminId)) {
                $staffQuery->where('branch_id', $subAdminId);
            } elseif ($role == 'sub-admin') {
                $staffQuery->where('branch_id', $userBranchId);
            } else {
                $staffQuery->where('branch_id', $userBranchId);
            }
        }

        $staffUsers = $staffQuery->get();

        return view('attendance.view', compact('staffUsers', 'attendances', 'currentMonth', 'year', 'month', 'daysInMonth', 'sundayOff'));
    }

    public function add(Request $request)
    {
        $user         = Auth::user();
        $role         = $user->role;
        $userBranchId = $user->id;
        $subAdminId   = session('selectedSubAdminId');
        if (! empty($subAdminId)) {
            $query      = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $subAdminId);
            $staffUsers = $query->get();
        } elseif ($role == 'sub-admin') {
            $query      = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userBranchId);
            $staffUsers = $query->get();
        } else {
            $query      = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userBranchId);
            $staffUsers = $query->get();
        }
        return view('attendance.add', compact('staffUsers'));
    }

    public function manage()
    {
        return view('attendance.manage');
    }

    public function manageSummary(Request $request)
    {
        $user     = Auth::user();
        $role     = $user->role;
        $branchId = $user->id;
        $subAdminId = session('selectedSubAdminId');

        if ($role === 'admin' && !empty($subAdminId)) {
            $branchId = (int) $subAdminId;
        }

        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year',  date('Y'));

        $startDate        = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate          = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $totalDaysInMonth = $startDate->daysInMonth;

        $staffList = User::where('role', 'staff')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->get(['id', 'name', 'profile_image']);

        $imagePath = env('ImagePath', '/');

        $summary = [];
        foreach ($staffList as $staff) {
            $attendances = Attendance::where('user_id', $staff->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $presentDays  = 0;
            $absentDays   = 0;
            $totalWorkMin = 0;
            $totalOtMin   = 0;
            $totalLateMin = 0;

            foreach ($attendances as $att) {
                $st = $att->status ?? 'A';
                if ($st === 'P')     $presentDays += 1;
                elseif ($st === 'H') $presentDays += 0.5;
                elseif ($st === 'A') $absentDays  += 1;

                $totalWorkMin += (int) round((float)($att->work_hours    ?? 0) * 60);
                $totalOtMin   += (int) round((float)($att->overtime_hours ?? 0) * 60);
                $totalLateMin += (int) round((float)($att->late_hours    ?? 0) * 60);
            }

            $fmt = fn(int $m) => floor($m / 60) . 'h ' . ($m % 60) . 'm';

            $photoUrl = $staff->profile_image
                ? url($imagePath . 'storage/' . $staff->profile_image)
                : url($imagePath . 'admin/assets/img/profiles/avatar-02.jpg');

            $summary[] = [
                'id'           => $staff->id,
                'name'         => ucwords($staff->name),
                'photo'        => $photoUrl,
                'total_days'   => $totalDaysInMonth,
                'present_days' => $presentDays,
                'work_hours'   => $fmt($totalWorkMin),
                'overtime'     => $fmt($totalOtMin),
                'late_hours'   => $fmt($totalLateMin),
                'leaves'       => 0,
                'absent'       => $absentDays,
            ];
        }

        return response()->json(['status' => true, 'summary' => $summary]);
    }

    public function manageHistory(Request $request, $employee_id)
    {
        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year',  date('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $employee = User::find($employee_id);
        if (!$employee) {
            return response()->json(['status' => false, 'message' => 'Employee not found.'], 404);
        }

        $allAttendances = Attendance::where('user_id', $employee_id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()->keyBy('date');

        $records = [];
        for ($day = clone $startDate; $day->lte($endDate); $day->addDay()) {
            $dateStr = $day->toDateString();
            $att     = $allAttendances->get($dateStr);
            $status  = $att->status ?? null;

            $isSunday = $day->dayOfWeek === Carbon::SUNDAY;
            $isFuture = $day->isAfter(Carbon::today());

            if ($isSunday && !$att)       $statusLabel = 'Week Off';
            elseif (!$att && $isFuture)   $statusLabel = '-';
            elseif (!$att)                $statusLabel = 'absent';
            elseif ($status === 'P')      $statusLabel = 'present';
            elseif ($status === 'H')      $statusLabel = 'Half Day';
            elseif ($status === 'A')      $statusLabel = 'absent';
            else                          $statusLabel = $status ?? '-';

            $fmtHrs = function($val) {
                if (!$val || $val <= 0) return '-';
                $h = (int) floor($val);
                $m = (int) round(fmod($val, 1) * 60);
                return "{$h}h {$m}m";
            };

            $records[] = [
                'date'       => $day->format('M d, Y'),
                'check_in'   => $att->check_in_time  ?? '-',
                'check_out'  => $att->check_out_time ?? '-',
                'work_hours' => $fmtHrs($att->work_hours    ?? 0),
                'overtime'   => $fmtHrs($att->overtime_hours ?? 0),
                'late'       => $fmtHrs($att->late_hours     ?? 0),
                'status'     => $statusLabel,
            ];
        }

        return response()->json([
            'status'   => true,
            'employee' => ucwords($employee->name),
            'records'  => array_reverse($records),
        ]);
    }
}
