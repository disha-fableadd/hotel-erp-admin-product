<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\{Attendance, LeaveModel, LogAttendance, Notification, User};

class HrDashboardController extends Controller
{
    public function getDashboardData(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Fix: Allow both 'admin' and 'hr'
        if (!in_array($user->role, ['admin', 'hr'], true)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Only admin and hr allowed',
            ], 403);
        }

        $branchId = $user->branch_id ?: $user->id;
        $today = Carbon::today('Asia/Kolkata');
        $todayDate = $today->toDateString();
        $standardHours = '08:30:00';

        $staffUsers = User::query()
            ->whereIn('role', ['staff', 'employee', 'hr'])
            ->where('isDeleted', '!=', 1)
            ->where(function ($query) use ($branchId, $user) {
                $query->where('branch_id', $branchId)
                    ->orWhere('id', $user->id);
            });

        $staffIds = $staffUsers->pluck('id');
        $staffCount = $staffIds->count();

        $announcements = Notification::query()
            ->where(function ($query) use ($branchId, $user) {
                $query->where('branch_id', $branchId)
                    ->orWhere('user_id', $user->id);
            })
            ->where('type', '!=', 'login')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title ?: 'Company Update',
                    'message' => $item->message ?: 'No message available.',
                    'link' => $item->link ?: route('notifications.index'),
                    'created_at' => optional($item->created_at)->toISOString(),
                    'formatted_date' => optional($item->created_at)->format('d M Y'),
                ];
            })
            ->values();

        $leaveCount = LeaveModel::query()
            ->whereIn('user_id', $staffIds)
            ->count();

        $staffCollection = User::query()
            ->whereIn('id', $staffIds)
            ->get()
            ->keyBy('id');

        $allAttendanceCount = Attendance::query()
            ->whereIn('user_id', $staffIds)
            ->count();

        // Fetch ALL attendance rows for today (including multiple check-in/out sessions)
        $todayAttendanceRows = Attendance::query()
            ->whereIn('user_id', $staffIds)
            ->whereDate('date', $todayDate)
            ->orderBy('check_in_time')
            ->get();

        // ── Consolidate multiple rows per employee into ONE entry per employee ──
        // Group by user_id, then for each employee:
        //   • first check-in  = MIN(check_in_time)
        //   • last  check-out = MAX(check_out_time)  (null if any session still open)
        //   • worked seconds  = SUM of every completed (in+out) session
        //                       + live elapsed for any still-open session
        //   • is_checked_out  = true only when ALL sessions are closed
        $nowIST          = Carbon::now('Asia/Kolkata');
        $lunchBreakSecs  = 30 * 60; // 30 min deducted once per day
        $grouped         = $todayAttendanceRows->groupBy('user_id');

        // Filter to employees who actually have a check-in or present/half-day status
        $groupedPresent = $grouped->filter(function ($rows) {
            return $rows->contains(function ($row) {
                return !empty($row->check_in_time)
                    || in_array(strtolower((string) $row->status), ['present', 'half-day'], true);
            });
        });

        $checkedInCount  = $groupedPresent->count();
        $checkedOutCount = $groupedPresent->filter(function ($rows) {
            return $rows->every(fn($r) => !empty($r->check_out_time));
        })->count();

        $todayLeaves = LeaveModel::query()
            ->whereIn('user_id', $staffIds)
            ->whereDate('start_date', '<=', $todayDate)
            ->whereDate('end_date', '>=', $todayDate)
            ->whereIn('status', ['approved', 'Approved'])
            ->get()
            ->keyBy('user_id');

        $presentStaff = $groupedPresent->map(function ($rows) use ($staffCollection, $nowIST, $lunchBreakSecs, $todayDate) {
            $userId = $rows->first()->user_id;
            $staff  = $staffCollection->get($userId);
            if (! $staff) return null;

            // First check-in of the day
            $firstCheckIn = $rows
                ->whereNotNull('check_in_time')
                ->sortBy('check_in_time')
                ->first()?->check_in_time;

            // Last check-out (null if any session is still open)
            $hasOpenSession = $rows->contains(fn($r) => !empty($r->check_in_time) && empty($r->check_out_time));
            $lastCheckOut   = $hasOpenSession
                ? null
                : $rows->whereNotNull('check_out_time')->sortByDesc('check_out_time')->first()?->check_out_time;

            // Sum up worked seconds across ALL completed sessions
            $totalWorkedSecs = 0;
            foreach ($rows as $row) {
                if (!empty($row->check_in_time) && !empty($row->check_out_time)) {
                    $inSecs  = strtotime($todayDate . ' ' . $row->check_in_time);
                    $outSecs = strtotime($todayDate . ' ' . $row->check_out_time);
                    if ($outSecs > $inSecs) {
                        $totalWorkedSecs += ($outSecs - $inSecs);
                    }
                }
            }

            // Add live elapsed time for any still-open session
            $openRow = $rows->first(fn($r) => !empty($r->check_in_time) && empty($r->check_out_time));
            $checkInCarbon = null;
            if ($openRow) {
                $checkInCarbon    = Carbon::parse($openRow->check_in_time)->setTimezone('Asia/Kolkata');
                $liveElapsed      = max(0, (int) $checkInCarbon->diffInSeconds($nowIST));
                $totalWorkedSecs += $liveElapsed;
            } elseif ($firstCheckIn) {
                // All sessions closed — use the first check-in Carbon for timestamp
                $checkInCarbon = Carbon::parse($firstCheckIn)->setTimezone('Asia/Kolkata');
            }

            // Deduct lunch break once (only after at least 30 min has elapsed total)
            if ($totalWorkedSecs >= $lunchBreakSecs) {
                $totalWorkedSecs -= $lunchBreakSecs;
            }
            $totalWorkedSecs = max(0, $totalWorkedSecs);

            $workingHours = sprintf(
                '%02d:%02d:%02d',
                intdiv($totalWorkedSecs, 3600),
                intdiv($totalWorkedSecs % 3600, 60),
                $totalWorkedSecs % 60
            );

            $isCheckedOut = !$hasOpenSession && $lastCheckOut !== null;

            return [
                'id'                 => $staff->id,
                'name'               => $staff->name ?: 'Staff',
                'profile_image_url'  => $staff->profile_image_url,
                'check_in_time'      => $firstCheckIn ? Carbon::parse($firstCheckIn)->format('H:i:s') : '--',
                'check_in_timestamp' => $checkInCarbon ? $checkInCarbon->timestamp : null,
                'is_checked_out'     => $isCheckedOut,
                'working_hours'      => $workingHours,
                'status'             => $isCheckedOut ? 'Checked Out' : 'Working',
            ];
        })->filter()->values();

        $presentIds = $presentStaff->pluck('id')->all();

        // Calculate missing / absent staff dynamically
        $absentOrLeaveStaff = collect($staffIds)->diff($presentIds)->map(function ($staffId) use ($staffCollection, $todayLeaves, $todayDate) {
            $staff = $staffCollection->get($staffId);
            if (! $staff) return null;
            $leave = $todayLeaves->get($staffId);
            return [
                'id' => $staff->id,
                'name' => $staff->name ?: 'Staff',
                'profile_image_url' => $staff->profile_image_url,
                'type' => $leave ? 'Leave' : 'Absent',
                'date_label' => $leave ? Carbon::parse($leave->start_date)->format('Y-m-d') . ' to ' . Carbon::parse($leave->end_date)->format('Y-m-d') : $todayDate,
            ];
        })->filter()->values();

        // Optional logic as per instruction
        $todayAbsent = Attendance::whereDate('date', Carbon::today())
            ->where('status', 'absent')
            ->count();

        return response()->json([
            'status' => true,
            'data' => [
                'announcements' => $announcements,
                'stats' => [
                    'staff_count' => $staffCount,
                    'attendance_count' => $allAttendanceCount,
                    'leave_count' => $leaveCount,
                    'task_count' => 0,
                ],
                'today_present_staff' => $presentStaff,
                'today_absent_or_leave_staff' => $absentOrLeaveStaff,
                'today_absent_count_db' => $todayAbsent
            ],
        ], 200);
    }
}
