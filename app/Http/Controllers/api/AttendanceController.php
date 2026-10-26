<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use App\Models\LogAttendance;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        // dd($request->all());
        $today = Carbon::today()->toDateString();

        $request->validate([
            'id' => 'nullable|exists:attendances,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:P,A,H',
            'extraday' => 'required|boolean',

            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',

            'reason' => $request->status === 'A'
                ? 'required|string|max:255'
                : 'nullable|string|max:255',
        ], [
            'user_id.required' => 'Staff name is required.',
            'user_id.exists' => 'Selected staff does not exist.',
            'date.before_or_equal' => 'You cannot mark attendance for a future date.',
            'reason.required' => 'Reason is required for Absent status.',
            'extraday.required' => 'Extraday field is required.',
        ]);


        $user = Auth::guard('api')->user();
        if ($user->role === 'staff') {
            return response()->json(['message' => 'Access denied.'], 403);
        }
        $userId = $user->id;

        if ($request->filled('selectedSubAdminId') && is_numeric($request->input('selectedSubAdminId'))) {
            $userId = (int) $request->input('selectedSubAdminId');
        }

        Attendance::updateOrCreate(
            ['user_id' => $request->user_id, 'date' => $request->date],
            [
                'branch_id' => $userId,
                'status' => $request->status,
                'check_in_time' => $request->check_in_time,
                'check_out_time' => $request->check_out_time,
                'reason' => $request->reason,
                'extraday' => $request->extraday,
            ]
        );

        return response()->json(['message' => 'Attendance saved successfully']);
    }

    public function history(Request $request)
    {
        $userId = $request->user_id;
        $date = $request->date;

        $logs = Attendance::where('user_id', $userId)
            ->whereDate('date', $date)
            ->orderBy('created_at', 'desc')
            ->get(['check_in_time as check_in', 'check_out_time as checkout_out', 'created_at']);

        return response()->json(['status'  => true,'logs' => $logs]);
    }

public function index(Request $request)
{
    $user = Auth::guard('api')->user();
    $role = $user->role;
    $userBranchId = $user->id;

    // ✅ Override branch_id if selectedSubAdminId is provided
    if ($request->filled('selectedSubAdminId') && is_numeric($request->input('selectedSubAdminId'))) {
        $userBranchId = (int) $request->input('selectedSubAdminId');
    }

    // ✅ Month setup
    $monthInput   = $request->input('month', now()->format('Y-m'));
    $month        = Carbon::parse($monthInput)->format('m');
    $year         = Carbon::parse($monthInput)->format('Y');
    $daysInMonth  = Carbon::create($year, $month)->daysInMonth;
    $currentMonth = $monthInput;

    // ✅ Fetch sunday_off setting
    $settings = Setting::where('branch_id', $userBranchId)->first();
    $sundayOff = $settings->sunday_off ?? 'no';

    // ✅ Attendance records
    $attendanceQuery = Attendance::whereMonth('date', $month)
        ->whereYear('date', $year);

    // If attendance has branch_id, uncomment:
    // $attendanceQuery->where('branch_id', $userBranchId);

    $attendances = $attendanceQuery->get();

    // ✅ Staff query
    $staffQuery = User::query();

    if ($role == 'staff') {
        $staffQuery->where('id', $user->id);
    } else {
        $staffQuery->where('role', 'staff')
                   ->where('isDeleted', 0)
                   ->where('branch_id', $userBranchId);

        if ($request->filled('search')) {
            $staffQuery->where('name', 'like', '%' . $request->search . '%');
        }
    }

    $staffUsers = $staffQuery->get();

    // ✅ Return clean JSON for Flutter/Dart
    return response()->json([
        'status'  => true,
        'message' => 'Attendance data fetched successfully.',
        'data'    => [
            'staffUsers'   => $staffUsers->values(),              // Array format
            'attendances'  => $attendances->values(),             // Array format
            'currentMonth' => $currentMonth,
            'year'         => $year,
            'month'        => $month,
            'daysInMonth'  => $daysInMonth,
            'sundayOff'    => $sundayOff,
        ],
    ]);
}

}
