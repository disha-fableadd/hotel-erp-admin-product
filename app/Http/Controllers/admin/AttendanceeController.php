<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyRulesModel;
use App\Models\HolidayCalendarModel;
use App\Models\LeaveModel;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Services\AttendanceLocationService;


class AttendanceeController extends Controller
{
    public function display()
    {
        return view('attendence.attendence', $this->legacyViewData('Attendance Summary'));
    }

    public function view()
    {
        return view('attendence.view', $this->legacyViewData('Attendance'));
    }

    public function viewCalendar()
    {
        return view('attendence.view_calendar', $this->legacyViewData('Attendance Calendar'));
    }

    public function getEmployees(): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->attendanceUsersQuery($user)->get(['id', 'name', 'profile_image']),
        ]);
    }

    public function getStatus(): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $record = DB::table('attendances')
            ->where('user_id', $user->id)
            ->whereDate('date', $this->today()->toDateString())
            ->orderByDesc('id')
            ->first();

        $details = DB::table('user_details')->where('user_id', $user->id)->first();
        $attendanceStatus = !$record
            ? 'not_checked_in'
            : (!empty($record->check_in_time) && empty($record->check_out_time) ? 'checked_in' : 'checked_out');
        $requiresFaceScan = in_array($user->role, ['staff', 'hr'], true);
        $hasFacePhoto = !empty($details?->face_photo);

        return response()->json([
            'status' => 'success',
            'role' => $user->role,
            'has_face_photo' => $hasFacePhoto,
            'requires_face_scan' => $requiresFaceScan,
            'must_face_check_in' => $requiresFaceScan && $attendanceStatus !== 'checked_in',
            'data' => $attendanceStatus,
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!$this->canSelfMarkAttendance($user)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied for this role'], 403);
        }

        return $this->performCheckIn($user, 'manual', $this->extractLocationFromRequest($request, 'check_in'));
    }

    /**
     * GET /staff/check-status
     * Returns whether the staff member is currently checked in.
     */
    public function staffCheckStatus(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $attendance = \App\Models\Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // If they have a check-in time but no check-out time, they are currently checked in
        if ($attendance && !empty($attendance->check_in_time) && empty($attendance->check_out_time)) {
            return response()->json([
                'status'        => 'checked_in',
                'check_in_time' => $attendance->check_in_time,
            ]);
        }

        return response()->json(['status' => 'not_checked_in']);
    }

    /**
     * POST /staff/check-in
     * Captures GPS, validates location if assigned, creates Attendance record.
     */
    public function staffCheckIn(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $today       = Carbon::now('Asia/Kolkata')->toDateString();
        $now         = Carbon::now('Asia/Kolkata');
        $currentTime = $now->format('H:i:s');

        $latitude  = $request->input('check_in_latitude') ?? $request->input('latitude');
        $longitude = $request->input('check_in_longitude') ?? $request->input('longitude');
        $locationName = $request->input('check_in_location_name');

        // Location Validation via Settings
        $settings = \App\Models\Setting::where('branch_id', $user->branch_id ?? 0)->first() 
                 ?? \App\Models\Setting::first();

        if ($settings && $settings->office_latitude && $settings->office_longitude) {
            if ($latitude === null || $longitude === null) {
                return response()->json([
                    'error' => 'Location access is required for check-in. Please allow location permission and try again.',
                ], 403);
            }

            $allowedRadius = $settings->office_radius ?: 200;
            $locationLabel = $settings->address ?: 'the shop address';

            // Haversine distance calculation
            $earthRadius  = 6371000;
            $latFrom      = (float) $settings->office_latitude;
            $lonFrom      = (float) $settings->office_longitude;
            $latTo        = (float) $latitude;
            $lonTo        = (float) $longitude;

            $dLat = deg2rad($latTo - $latFrom);
            $dLon = deg2rad($lonTo - $lonFrom);

            $a = sin($dLat/2) * sin($dLat/2) +
                 cos(deg2rad($latFrom)) * cos(deg2rad($latTo)) *
                 sin($dLon/2) * sin($dLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $distanceInMeters = $earthRadius * $c;

            if ($distanceInMeters > $allowedRadius) {
                $distanceRounded = round($distanceInMeters);
                return response()->json([
                    'error' => "You are not within office premises. Check-in is only allowed within {$allowedRadius} meters of {$locationLabel}. (Current distance: {$distanceRounded}m)",
                ], 403);
            }
        }

        $attendance = \App\Models\Attendance::where('user_id', $user->id)->where('date', $today)->orderBy('id', 'desc')->first();

        // Block if already checked in (has check_in but no check_out)
        if ($attendance && !empty($attendance->check_in_time) && empty($attendance->check_out_time)) {
            return response()->json(['error' => 'Already checked in. Please check out first.'], 400);
        }

        // Determine initial status (Half Day if checking in at/after 14:00)
        $initStatus = $now->gte(Carbon::today('Asia/Kolkata')->setTime(14, 0, 0)) ? 'H' : 'P';

        // Calculate late minutes
        $openTimeStr = $settings->open_time ?? '09:00:00';
        $openTime = Carbon::createFromFormat('H:i:s', $openTimeStr, 'Asia/Kolkata')->setDateFrom($now);
        $graceTimeStr = $settings->grace_period ?? null;
        if ($graceTimeStr) {
            if (is_numeric($graceTimeStr)) {
                $openTime->addMinutes((int) $graceTimeStr);
            } else {
                try {
                    if (strpos($graceTimeStr, ':') !== false) {
                        $parts = explode(':', $graceTimeStr);
                        $graceMinutes = ((int) ($parts[0] ?? 0)) * 60 + ((int) ($parts[1] ?? 0));
                        $openTime->addMinutes($graceMinutes);
                    } else {
                        $openTime->addMinutes((int) $graceTimeStr);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed parsing grace_period in staffCheckIn: ' . $graceTimeStr);
                }
            }
        }

        $isLate = 0;
        $lateMinutes = 0;
        if ($now->gt($openTime)) {
            $isLate = 1;
            $lateMinutes = $now->diffInMinutes($openTime);
        }

        // Always insert a new row for a new session
        \App\Models\Attendance::create([
            'user_id'       => $user->id,
            'branch_id'     => $user->branch_id ?? 0,
            'date'          => $today,
            'check_in_time' => $currentTime,
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'check_in_location_name' => $locationName,
            'status'        => $initStatus === 'H' ? 'half-day' : 'present',
            'checkin_method'=> 'manual',
            'is_late'       => $isLate,
            'late_minutes'  => $lateMinutes
        ]);

        try {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'attendance',
                'title' => 'Staff Checked In',
                'message' => $user->name . ' has checked in.',
                'link' => '/staff/attendance/summary',
                'branch_id' => $user->branch_id ?? '0',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Web Check-in notification failed: ' . $e->getMessage());
        }

        $statusLabel = $initStatus === 'H' ? 'Half Day' : 'Present';

        return response()->json([
            'success'       => true,
            'message'       => '✅ Checked In at ' . $now->format('h:i A') . ' | ' . $statusLabel,
            'check_in_time' => $currentTime,
            'status'        => $initStatus,
        ]);
    }

    /**
     * POST /staff/check-out
     * Updates attendance with check-out time and calculates work hours.
     */
    public function staffCheckOut(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $today       = Carbon::now('Asia/Kolkata')->toDateString();
        $now         = Carbon::now('Asia/Kolkata');
        $currentTime = $now->format('H:i:s');

        $latitude  = $request->input('check_out_latitude') ?? $request->input('latitude');
        $longitude = $request->input('check_out_longitude') ?? $request->input('longitude');
        $locationName = $request->input('check_out_location_name');

        // Location Validation via Settings
        $settings = \App\Models\Setting::where('branch_id', $user->branch_id ?? 0)->first()
                 ?? \App\Models\Setting::first();

        if ($settings && $settings->office_latitude && $settings->office_longitude) {
            if ($latitude === null || $longitude === null) {
                return response()->json([
                    'error' => 'Location access is required for check-out. Please allow location permission and try again.',
                ], 403);
            }

            $allowedRadius = $settings->office_radius ?: 200;
            $locationLabel = $settings->address ?: 'the shop address';

            // Haversine distance calculation
            $earthRadius  = 6371000;
            $latFrom      = (float) $settings->office_latitude;
            $lonFrom      = (float) $settings->office_longitude;
            $latTo        = (float) $latitude;
            $lonTo        = (float) $longitude;

            $dLat = deg2rad($latTo - $latFrom);
            $dLon = deg2rad($lonTo - $lonFrom);

            $a = sin($dLat/2) * sin($dLat/2) +
                 cos(deg2rad($latFrom)) * cos(deg2rad($latTo)) *
                 sin($dLon/2) * sin($dLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $distanceInMeters = $earthRadius * $c;

            if ($distanceInMeters > $allowedRadius) {
                $distanceRounded = round($distanceInMeters);
                return response()->json([
                    'error' => "You are not within office premises. Check-out is only allowed within {$allowedRadius} meters of {$locationLabel}. (Current distance: {$distanceRounded}m)",
                ], 403);
            }
        }

        // Find the active session (where check_out_time is null)
        $attendance = \App\Models\Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNull('check_out_time')
            ->orderBy('id', 'desc')
            ->first();

        if (!$attendance || empty($attendance->check_in_time)) {
            return response()->json(['error' => 'Not checked in. Cannot check out.'], 400);
        }

        // Calculate total hours for THIS session
        $checkInTime = Carbon::createFromFormat('H:i:s', $attendance->check_in_time, 'Asia/Kolkata')->setDateFrom($now);
        $sessionMinutes = max(0, $checkInTime->diffInMinutes($now));
        
        // Also compute total day hours
        $allSessions = \App\Models\Attendance::where('user_id', $user->id)->where('date', $today)->get();
        $totalDaySeconds = 0;
        foreach ($allSessions as $session) {
            if ($session->id === $attendance->id) {
                $totalDaySeconds += max(0, $checkInTime->diffInSeconds($now));
            } else if (!empty($session->check_out_time)) {
                $in = Carbon::createFromFormat('H:i:s', $session->check_in_time, 'Asia/Kolkata')->setDateFrom($now);
                $out = Carbon::createFromFormat('H:i:s', $session->check_out_time, 'Asia/Kolkata')->setDateFrom($now);
                $totalDaySeconds += max(0, $out->diffInSeconds($in));
            }
        }
        
        $totalHours = round(($totalDaySeconds / 60) / 60, 2);

        // Determine status
        $requiredHours = 8;
        $graceLimitTime = Carbon::today('Asia/Kolkata')->setTime(9, 45, 0);
        if ($checkInTime->lte($graceLimitTime)) {
            $status = 'P';
        } elseif ($totalHours >= ($requiredHours / 2)) {
            $status = 'H';
        } else {
            $status = 'A';
        }

        // Update Attendance summary row
        $statusMap = ['P' => 'present', 'H' => 'half-day', 'A' => 'absent'];
        $attendance->update([
            'check_out_time' => $currentTime,
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'check_out_location_name' => $locationName,
            'work_hours'     => sprintf('%02d:%02d:00', (int) floor($sessionMinutes / 60), $sessionMinutes % 60),
            'status'         => $statusMap[$status] ?? 'present',
        ]);

        try {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'attendance',
                'title' => 'Staff Checked Out',
                'message' => $user->name . ' has checked out.',
                'link' => '/staff/attendance/summary',
                'branch_id' => $user->branch_id ?? '0',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Web Check-out notification failed: ' . $e->getMessage());
        }

        $statusLabel = ['P' => 'Present', 'H' => 'Half Day', 'A' => 'Absent'][$status] ?? $status;

        return response()->json([
            'success'     => true,
            'message'     => '✅ Checked Out | ' . $totalHours . ' hrs | ' . $statusLabel,
            'status'      => $status,
            'total_hours' => $totalHours,
        ]);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!$this->canSelfMarkAttendance($user)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied for this role'], 403);
        }

        $today = $this->today();
        $openRecord = DB::table('attendances')
            ->where('user_id', $user->id)
            ->whereDate('date', $today->toDateString())
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->orderByDesc('id')
            ->first();

        if (!$openRecord) {
            return response()->json(['status' => 'error', 'message' => 'No active check-in found'], 422);
        }

        $rules = $this->companyRules();
        $checkOutTime = $today->format('H:i:s');
        $mealBreak = $openRecord->meal_break ?: ($rules->lunch_break ?? '00:30:00');
        $calculation = $this->calculateWorkHours($today->toDateString(), $mealBreak, $openRecord->check_in_time, $checkOutTime, $rules);

        $updateValues = [
            'check_out_time' => $checkOutTime,
            'work_hours' => $calculation['work_hours'],
            'overtime' => $calculation['overtime'],
            'status' => $calculation['status'],
            'updated_at' => $today->toDateTimeString(),
        ];
        $this->appendLocationColumns($updateValues, $this->extractLocationFromRequest($request, 'check_out'), 'check_out');

        DB::table('attendances')
            ->where('id', $openRecord->id)
            ->update($updateValues);

        try {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'attendance',
                'title' => 'Staff Checked Out',
                'message' => $user->name . ' has checked out.',
                'link' => '/staff/attendance/summary',
                'branch_id' => $user->branch_id ?? '0',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Check-out notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Checked out successfully',
            'data' => [
                'check_out_time' => $checkOutTime,
                'work_hours' => $calculation['work_hours'],
                'overtime' => $calculation['overtime'],
                'status' => $calculation['status'],
            ],
        ]);
    }

    public function getAttendanceData(Request $request): JsonResponse
    {
        return $this->getAttendance(
            (int) $request->input('month', $this->today()->month),
            (int) $request->input('year', $this->today()->year),
        );
    }

    public function getAttendance(int $month, int $year): JsonResponse
    {
        $user = $this->apiUser();

        info($user);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $month = max(1, min(12, $month));
        $year = max(2000, min(2100, $year));
        $start = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata');
        $end = $start->copy()->endOfMonth();    
        $users = $this->attendanceUsersQuery($user)
            ->whereHas('details', function ($q) use ($end) {
                $q->where(function ($sub) use ($end) {
                    $sub->whereNull('joining_date')
                        ->orWhere('joining_date', '<=', $end->toDateString());
                });
            })
            ->with('details:user_id,joining_date')
            ->get(['id', 'name', 'profile_image', 'role']);
        $userIds = $users->pluck('id');

        $attendanceRows = $this->applyAttendanceLocationSelects(DB::table('attendances'))
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('user_id')
            ->orderBy('date')
            ->orderBy('check_in_time')
            ->get();

        $leaveRows = LeaveModel::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('status', ['approved', 'Approved'])
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->get(['user_id', 'start_date', 'end_date']);

        $holidays = HolidayCalendarModel::query()
            ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('holiday_date')
            ->get(['id', 'title', 'holiday_date']);

        $rules = $this->companyRules();
        $leaveMap = $this->buildLeaveMap($leaveRows, $start, $end);
        $attendanceMap = $this->buildAttendanceMap($attendanceRows);

        $payloadUsers = $users->map(function ($employee) use ($attendanceMap, $leaveMap) {
            $records = collect($attendanceMap[$employee->id] ?? []);

            foreach (($leaveMap[$employee->id] ?? []) as $leaveDate) {
                if (!$records->firstWhere('date', $leaveDate)) {
                    $records->push([
                        'date' => $leaveDate,
                        'check_in_time' => null,
                        'check_in_location_name' => null,
                        'check_out_time' => null,
                        'check_out_location_name' => null,
                        'work_hours' => '00:00:00',
                        'overtime' => '00:00:00',
                        'status' => 'leave',
                        'is_late' => 0,
                        'late_minutes' => 0,
                    ]);
                }
            }

            $joiningDate = $employee->details->joining_date ?? null;
            if ($joiningDate instanceof \Carbon\Carbon || $joiningDate instanceof \DateTimeInterface) {
                $joiningDate = $joiningDate->toDateString();
            }

            return [
                'user_id' => $employee->id,
                'employee_name' => $employee->name,
                'profile_image' => $employee->profile_image,
                'role' => $employee->role,
                'joining_date' => $joiningDate,
                'attendance' => $records->sortBy('date')->values()->all(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => $payloadUsers,
                'holidays' => $holidays,
                'saturdayOffDates' => $this->getSaturdayOffDates($start, $end, $rules),
                'isIncludedHoliday' => !empty($rules->include_holidays_in_working_days) ? '1' : '0',
                'serverToday' => $this->today()->toDateString(),
            ],
        ]);
    }

    public function getDashboardStats(): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->todayStats($user),
        ]);
    }

    public function getLeaveDetails(int $id): JsonResponse
    {
        $leave = LeaveModel::query()->with(['user', 'leaveType'])->find($id);

        if (!$leave) {
            return response()->json(['status' => 'error', 'message' => 'Leave not found'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $leave]);
    }

    public function getAbsentReason(Request $request): JsonResponse
    {
        $user = $this->apiUser();

        $query = DB::table('attendances')
            ->whereDate('date', $request->query('date'));

        if ($user->role === 'staff') {
            // staff → only own
            $query->where('user_id', $user->id);
        } else {
            // hr → any user
            $query->where('user_id', (int) $request->query('user_id'));
        }

        $record = $query->orderByDesc('id')->first(['reason', 'status']);

        return response()->json(['status' => 'success', 'data' => $record]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!$this->canManageAttendance($user)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'status' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
        ]);

        DB::table('attendances')
            ->where('user_id', $validated['user_id'])
            ->whereDate('date', $validated['date'])
            ->update([
                'status' => $validated['status'],
                'reason' => $validated['reason'] ?? null,
                'updated_at' => $this->today()->toDateTimeString(),
            ]);

        return response()->json(['status' => 'success', 'message' => 'Attendance updated successfully']);
    }

    public function getFacePhoto(): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $details = DB::table('user_details')->where('user_id', $user->id)->first();
        if (!$details || empty($details->face_photo)) {
            return response()->json([
                'status' => 'success',
                'has_face_photo' => false,
                'face_photo' => null,
                'face_photo_base64' => null,
            ]);
        }

        $path = $details->face_photo;

        // Try to read the file and return as base64 to avoid CORS issues on the frontend
        $base64 = null;
        try {
            if (str_starts_with($path, 'http')) {
                // For external URLs, try to read via HTTP
                $contents = @file_get_contents($path);
            } else {
                // For local storage files
                if (Storage::disk('public')->exists($path)) {
                    $contents = Storage::disk('public')->get($path);
                } else {
                    $contents = false;
                }
            }

            if ($contents !== false) {
                $mimeType = 'image/jpeg'; // default
                try {
                    // Detect mime type from content
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $detectedMime = $finfo->buffer($contents);
                    if ($detectedMime) {
                        $mimeType = $detectedMime;
                    }
                } catch (\Exception $e) {}

                $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
            }
        } catch (\Exception $e) {
            Log::warning('getFacePhoto base64 conversion failed: ' . $e->getMessage());
        }

        // Build the public URL as fallback
        if (str_starts_with($path, 'http')) {
            $url = $path;
        } else {
            $url = Storage::disk('public')->url($path);
            if (!str_starts_with($url, 'http')) {
                $url = asset($url);
            }
        }

        return response()->json([
            'status' => 'success',
            'has_face_photo' => true,
            'face_photo' => $url,
            'face_photo_base64' => $base64,
        ]);
    }

    public function faceCheckIn(Request $request): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!$this->canSelfMarkAttendance($user)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied for this role'], 403);
        }

        $details = DB::table('user_details')->where('user_id', $user->id)->first();
        if (!$details || empty($details->face_photo)) {
            return response()->json(['status' => 'error', 'message' => 'Face photo not found'], 422);
        }

        return $this->performCheckIn($user, 'face_recognition', $this->extractLocationFromRequest($request, 'check_in'));
    }

    public function deleteTodayAttendance(): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        if ($user->role === 'staff') {
            return response()->json(['status' => 'error', 'message' => 'Access denied.'], 403);
        }

        $query = DB::table('attendances')->whereDate('date', $this->today()->toDateString());

        if ($this->canManageAttendance($user)) {
            $query->whereIn('user_id', $this->attendanceUsersQuery($user)->pluck('id'));
        } else {
            $query->where('user_id', $user->id);
        }

        $deleted = $query->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted attendance record(s) successfully',
            'deleted_count' => $deleted,
        ]);
    }

    public function getDayAttendanceRecords(Request $request): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user || !$this->canManageAttendance($user)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
        ]);

        $records = DB::table('attendances')
            ->where('user_id', $validated['user_id'])
            ->whereDate('date', $validated['date'])
            ->orderBy('check_in_time')
            ->get()
            ->map(function ($record) {
                $duration = null;
                if (!empty($record->check_in_time) && !empty($record->check_out_time)) {
                    $duration = $this->formatSeconds(
                        max(0, $this->timeToSeconds($record->check_out_time) - $this->timeToSeconds($record->check_in_time))
                    );
                }

                return [
                    'id' => $record->id,
                    'check_in_time' => $record->check_in_time,
                    'check_out_time' => $record->check_out_time,
                    'check_in_location_name' => $record->check_in_location_name ?? $record->check_in_location ?? null,
                    'check_out_location_name' => $record->check_out_location_name ?? $record->check_out_location ?? null,
                    'work_hours' => $record->work_hours,
                    'overtime' => $record->overtime,
                    'status' => $record->status,
                    'duration' => $duration,
                ];
            })
            ->values();

        return response()->json(['status' => 'success', 'data' => $records]);
    }

    public function updateDayAttendanceRecords(Request $request): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user || !$this->canManageAttendance($user)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $payload = $request->validate([
            'user_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'status' => ['nullable', 'string'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.id' => ['nullable'],
            'records.*.check_in_time' => ['nullable'],
            'records.*.check_out_time' => ['nullable'],
        ]);

        $date = Carbon::parse($payload['date'])->toDateString();
        $mealBreak = $this->companyRules()->lunch_break ?? '00:30:00';
        $now = $this->today()->toDateTimeString();

        $targetUser = \App\Models\User::find($payload['user_id']);
        $branchId = $targetUser ? ($targetUser->branch_id ?? 0) : 0;

        DB::transaction(function () use ($payload, $date, $mealBreak, $now, $branchId) {
            foreach ($payload['records'] as $row) {
                $values = [
                    'user_id' => $payload['user_id'],
                    'branch_id' => $branchId,
                    'date' => $date,
                    'check_in_time' => $this->normalizeTime($row['check_in_time'] ?? null),
                    'check_out_time' => $this->normalizeTime($row['check_out_time'] ?? null),
                    'meal_break' => $mealBreak,
                    'updated_at' => $now,
                ];

                if ($row['id'] === 'new') {
                    $values['created_at'] = $now;
                    DB::table('attendances')->insert($values);
                } else {
                    DB::table('attendances')->where('id', $row['id'])->update($values);
                }
            }

            $this->recalculateDay($payload['user_id'], $date, $payload['status'] ?? null);
        });

        return response()->json(['status' => 'success', 'message' => 'Attendance updated successfully']);
    }

    public function bulkUpdateAttendanceRecords(Request $request): JsonResponse
    {
        $user = $this->apiUser();
        if (!$user || !$this->canManageAttendance($user)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $payload = $request->validate([
            'user_id' => ['required', 'integer'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'status' => ['required', 'string'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
        ]);

        $holidays = HolidayCalendarModel::query()
            ->whereBetween('holiday_date', [$payload['from_date'], $payload['to_date']])
            ->pluck('holiday_date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->all();
        $holidayMap = array_fill_keys($holidays, true);
        $mealBreak = $this->companyRules()->lunch_break ?? '00:30:00';
        $now = $this->today()->toDateTimeString();

        $targetUser = \App\Models\User::find($payload['user_id']);
        $branchId = $targetUser ? ($targetUser->branch_id ?? 0) : 0;

        foreach (CarbonPeriod::create($payload['from_date'], $payload['to_date']) as $day) {
            $date = $day->toDateString();
            if ($day->isSunday() || isset($holidayMap[$date])) {
                continue;
            }

            $record = DB::table('attendances')
                ->where('user_id', $payload['user_id'])
                ->whereDate('date', $date)
                ->orderBy('id')
                ->first();

            $values = [
                'user_id' => $payload['user_id'],
                'branch_id' => $branchId,
                'date' => $date,
                'check_in_time' => $payload['status'] === 'absent' ? null : $this->normalizeTime($payload['check_in_time'] ?? null),
                'check_out_time' => $payload['status'] === 'absent' ? null : $this->normalizeTime($payload['check_out_time'] ?? null),
                'meal_break' => $mealBreak,
                'status' => $payload['status'],
                'updated_at' => $now,
            ];

            if ($record) {
                DB::table('attendances')->where('id', $record->id)->update($values);
                DB::table('attendances')
                    ->where('user_id', $payload['user_id'])
                    ->whereDate('date', $date)
                    ->where('id', '!=', $record->id)
                    ->delete();
            } else {
                $values['created_at'] = $now;
                DB::table('attendances')->insert($values);
            }

            $this->recalculateDay($payload['user_id'], $date, $payload['status']);
        }

        return response()->json(['status' => 'success', 'message' => 'Bulk attendance updated successfully']);
    }

    protected function legacyViewData(string $title): array
    {
        $user = Auth::user();

        return array_merge(
            [
                'title' => $title,
                'role' => $user?->role,
            ],
            $user ? $this->todayStats($user) : []
        );
    }

    protected function todayStats(User $user): array
    {
        $users = $this->attendanceUsersQuery($user)->get(['id']);
        $today = $this->today()->toDateString();
        $presentCount = DB::table('attendances')
            ->whereIn('user_id', $users->pluck('id'))
            ->whereDate('date', $today)
            ->whereIn('status', ['present', 'half-day'])
            ->distinct()
            ->count('user_id');

        return [
            'totalEmployees' => $users->count(),
            'presentEmployees' => $presentCount,
            'absentEmployees' => max(0, $users->count() - $presentCount),
            'workingDays' => now('Asia/Kolkata')->day,
        ];
    }

    protected function attendanceUsersQuery(User $user)
    {
        $query = User::query()
            ->where('isDeleted', 0)
            // Include all staff-level roles — must match dashboard's query
            ->whereIn('role', ['staff', 'hr', 'employee']);

        if (in_array($user->role, ['staff', 'employee'], true)) {
            // Staff / Employee: only see their own record
            $query->where('id', $user->id);
        } elseif ($branchId = $this->branchIdFor($user)) {
            // HR / Admin viewing a branch: filter by branch, also always include the viewer
            $query->where(function ($q) use ($branchId, $user) {
                $q->where('branch_id', $branchId)
                  ->orWhere('id', $user->id);
            });
        }

        return $query->orderBy('name');
    }

    protected function branchIdFor(User $user): ?int
    {
        // API routes are stateless — session is always null for Passport-authenticated requests.
        // The client passes selectedSubAdminId as a query parameter in those cases.
        $selectedSubAdminId = request()->query('selectedSubAdminId')
            ?? request()->input('selectedSubAdminId')
            ?? session('selectedSubAdminId');

        // Treat the string "null" as absent (sent by JS when no branch is selected)
        if ($selectedSubAdminId === 'null' || $selectedSubAdminId === '') {
            $selectedSubAdminId = null;
        }

        if (in_array($user->role, ['admin', 'sub-admin'], true) && !empty($selectedSubAdminId)) {
            return (int) $selectedSubAdminId;
        }

        // Staff, HR, and Employee all belong to a branch; scope to their own branch
        if (in_array($user->role, ['staff', 'hr', 'employee'], true)) {
            return $user->branch_id ?: $user->id;
        }

        // Admin without a specific branch selected — no branch filter (see all)
        return null;
    }

    protected function performCheckIn(User $user, string $method, array $location = []): JsonResponse
    {
        $now = $this->today();
        $today = $now->toDateString();
        $rules = $this->companyRules();
        $checkInTime = $now->format('H:i:s');
        if ($method !== 'face_recognition') {
            $locationCheck = $this->validateCheckInLocation($user, $location);

            if ($locationCheck instanceof JsonResponse) {
                return $locationCheck;
            }
        }

        $openRecord = DB::table('attendances')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->orderByDesc('id')
            ->first();

        if ($openRecord) {
            $startTime = $rules->start_time ?? $rules->open_time ?? '09:00:00';
            $gracePeriod = (int) ($rules->grace_period ?? 0);
            $allowed = $this->timeToSeconds($startTime) + ($gracePeriod * 60);
            $isLate = $this->timeToSeconds($checkInTime) > $allowed ? 1 : 0;
            $lateMinutes = $isLate ? (int) ceil(($this->timeToSeconds($checkInTime) - $allowed) / 60) : 0;

            $updateValues = [
                'check_in_time' => $checkInTime,
                'status' => 'present',
                'checkin_method' => $method,
                'meal_break' => $rules->lunch_break ?? '00:30:00',
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'updated_at' => $now->toDateTimeString(),
            ];
            $this->appendLocationColumns($updateValues, $location, 'check_in');
            DB::table('attendances')->where('id', $openRecord->id)->update($updateValues);

            try {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type' => 'attendance',
                    'title' => 'Staff Checked In',
                    'message' => $user->name . ' has checked in (' . $method . ').',
                    'link' => '/staff/attendance/summary',
                    'branch_id' => $user->branch_id ?? '0',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Check-in notification failed: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Check-in time updated successfully',
                'data' => ['check_in_time' => $checkInTime],
            ]);
        }

        $startTime = $rules->start_time ?? $rules->open_time ?? '09:00:00';
        $gracePeriod = (int) ($rules->grace_period ?? 0);
        $allowed = $this->timeToSeconds($startTime) + ($gracePeriod * 60);
        $isLate = $this->timeToSeconds($checkInTime) > $allowed ? 1 : 0;
        $lateMinutes = $isLate ? (int) ceil(($this->timeToSeconds($checkInTime) - $allowed) / 60) : 0;

        // Determine branch
        $branchId = $user->branch_id ?: $user->id;

        $insertValues = [
            'user_id'        => $user->id,
            'branch_id'      => $branchId,
            'date'           => $today,
            'check_in_time'  => $checkInTime,
            'meal_break'     => $rules->lunch_break ?? '00:30:00',
            'work_hours'     => '00:00:00',
            'overtime'       => '00:00:00',
            'status'         => 'present',
            'checkin_method' => $method,
            'is_late'        => $isLate,
            'late_minutes'   => $lateMinutes,
            'created_at'     => $now->toDateTimeString(),
            'updated_at'     => $now->toDateTimeString(),
        ];
        $this->appendLocationColumns($insertValues, $location, 'check_in');

        DB::table('attendances')->insert($insertValues);

        // Cancel any pending leave for today (same as normal check-in)
        LeaveModel::query()
            ->where('user_id', $user->id)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->delete();

        try {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'attendance',
                'title' => 'Staff Checked In',
                'message' => $user->name . ' has checked in (' . $method . ').',
                'link' => '/staff/attendance/summary',
                'branch_id' => $user->branch_id ?? '0',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Check-in notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => $method === 'face_recognition'
                ? 'Face verified! Checked in successfully via face recognition.'
                : 'Checked in successfully',
        ]);
    }

    protected function recalculateDay(int $userId, string $date, ?string $manualStatus = null): void
    {
        $rules = $this->companyRules();
        $records = DB::table('attendances')
            ->where('user_id', $userId)
            ->whereDate('date', $date)
            ->orderBy('check_in_time')
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        $mealBreakSeconds = $this->timeToSeconds($rules->lunch_break ?? '00:30:00');
        $startTime = $rules->start_time ?? $rules->open_time ?? '09:00:00';
        $gracePeriod = (int) ($rules->grace_period ?? 0);
        $earliestCheckIn = null;
        $totalSeconds = 0;
        $overtimeSeconds = 0;

        foreach ($records as $record) {
            if (!empty($record->check_in_time) && ($earliestCheckIn === null || $record->check_in_time < $earliestCheckIn)) {
                $earliestCheckIn = $record->check_in_time;
            }

            if (!empty($record->check_in_time) && !empty($record->check_out_time)) {
                $calc = $this->calculateWorkHours(
                    $date,
                    $record->meal_break ?: ($rules->lunch_break ?? '00:30:00'),
                    $record->check_in_time,
                    $record->check_out_time,
                    $rules
                );
                $totalSeconds += $calc['work_hours_seconds'];
                $overtimeSeconds += $calc['overtime_seconds'];
            }
        }

        if ($records->count() > 1 && $totalSeconds > $mealBreakSeconds) {
            $totalSeconds -= $mealBreakSeconds;
        }

        $isLate = 0;
        $lateMinutes = 0;
        if ($earliestCheckIn) {
            $allowed = $this->timeToSeconds($startTime) + ($gracePeriod * 60);
            if ($this->timeToSeconds($earliestCheckIn) > $allowed) {
                $isLate = 1;
                $lateMinutes = (int) ceil(($this->timeToSeconds($earliestCheckIn) - $allowed) / 60);
            }
        }

        $hasOpenSession = collect($records)->contains(fn($r) => !empty($r->check_in_time) && empty($r->check_out_time));
        $calculatedStatus = $this->calculateAttendanceStatus($totalSeconds, $rules);
        
        // If they are currently working, don't let them be 'absent'
        if ($hasOpenSession) {
            $calculatedStatus = 'present';
        }

        $finalStatus = $manualStatus ?: $calculatedStatus;
        if ($manualStatus === 'absent') {
            $totalSeconds = 0;
            $overtimeSeconds = 0;
        }

        DB::table('attendances')
            ->where('user_id', $userId)
            ->whereDate('date', $date)
            ->update([
                'work_hours' => $this->formatSeconds($totalSeconds),
                'overtime' => $this->formatSeconds($overtimeSeconds),
                'status' => $finalStatus,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'updated_at' => $this->today()->toDateTimeString(),
            ]);

        LeaveModel::query()
            ->where('user_id', $userId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->delete();
    }

    protected function buildLeaveMap(Collection $leaveRows, Carbon $start, Carbon $end): array
    {
        $map = [];

        foreach ($leaveRows as $leave) {
            $period = CarbonPeriod::create(
                Carbon::parse($leave->start_date)->max($start),
                Carbon::parse($leave->end_date)->min($end)
            );

            foreach ($period as $day) {
                $map[$leave->user_id][] = $day->toDateString();
            }
        }

        return $map;
    }

    protected function buildAttendanceMap(Collection $rows): array
    {
        $map = [];

        foreach ($rows as $row) {
            $userId = (int) $row->user_id;
            $date = Carbon::parse($row->date)->toDateString();

            if (!isset($map[$userId][$date])) {
                $map[$userId][$date] = [
                    'date' => $date,
                    'check_in_time' => $row->check_in_time,
                    'check_in_location_name' => $this->extractLocationName($row, [
                        'check_in_location_name',
                        'check_in_location',
                        'location_name',
                        'location',
                    ]),
                    'check_out_time' => $row->check_out_time,
                    'check_out_location_name' => $this->extractLocationName($row, [
                        'check_out_location_name',
                        'check_out_location',
                        'location_name',
                        'location',
                    ]),
                    'work_hours' => '00:00:00',
                    'overtime' => '00:00:00',
                    'status' => $row->status ?: 'absent',
                    'is_late' => (int) ($row->is_late ?? 0),
                    'late_minutes' => (int) ($row->late_minutes ?? 0),
                    '_session_seconds' => 0,
                    '_stored_work_seconds' => 0,
                    '_stored_overtime_seconds' => 0,
                    'last_session_check_in_time' => $row->check_in_time,
                    'last_session_has_no_checkout' => empty($row->check_out_time),
                ];
            }

            $bucket = $map[$userId][$date];

            if (!empty($row->check_in_time) && (empty($bucket['check_in_time']) || $row->check_in_time < $bucket['check_in_time'])) {
                $bucket['check_in_time'] = $row->check_in_time;
                $bucket['check_in_location_name'] = $this->extractLocationName($row, [
                    'check_in_location_name',
                    'check_in_location',
                    'location_name',
                    'location',
                ]);
            }

            $sessionCheckIn = $row->check_in_time;
            $isOpenSession = empty($row->check_out_time) && !empty($sessionCheckIn);

            if (!empty($row->check_out_time) && (empty($bucket['check_out_time']) || $row->check_out_time > $bucket['check_out_time'])) {
                $bucket['check_out_time'] = $row->check_out_time;
                $bucket['check_out_location_name'] = $this->extractLocationName($row, [
                    'check_out_location_name',
                    'check_out_location',
                    'location_name',
                    'location',
                ]);
            }

            if (!empty($sessionCheckIn) && (
                empty($bucket['last_session_check_in_time']) || $sessionCheckIn >= $bucket['last_session_check_in_time']
            )) {
                $bucket['last_session_check_in_time'] = $sessionCheckIn;
                $bucket['last_session_has_no_checkout'] = $isOpenSession;
            }

            if (!empty($row->check_in_time) && !empty($row->check_out_time)) {
                $bucket['_session_seconds'] += max(0, $this->timeToSeconds($row->check_out_time) - $this->timeToSeconds($row->check_in_time));
            }

            $bucket['_stored_work_seconds'] = max($bucket['_stored_work_seconds'], $this->timeToSeconds($row->work_hours ?? null));
            $bucket['_stored_overtime_seconds'] = max($bucket['_stored_overtime_seconds'], $this->timeToSeconds($row->overtime ?? null));
            $bucket['is_late'] = max($bucket['is_late'], (int) ($row->is_late ?? 0));
            $bucket['late_minutes'] = max($bucket['late_minutes'], (int) ($row->late_minutes ?? 0));

            if ($this->statusRank($row->status) > $this->statusRank($bucket['status'])) {
                $bucket['status'] = $row->status;
            }

            $map[$userId][$date] = $bucket;
        }

        foreach ($map as $userId => $dates) {
            foreach ($dates as $date => $bucket) {
                $workSeconds = $bucket['_stored_work_seconds'] > 0 ? $bucket['_stored_work_seconds'] : $bucket['_session_seconds'];
                $map[$userId][$date]['work_hours'] = $this->formatSeconds($workSeconds);
                $map[$userId][$date]['overtime'] = $this->formatSeconds($bucket['_stored_overtime_seconds']);
                $map[$userId][$date]['check_out_time'] = $bucket['last_session_has_no_checkout'] ? null : $bucket['check_out_time'];
                unset(
                    $map[$userId][$date]['_session_seconds'],
                    $map[$userId][$date]['_stored_work_seconds'],
                    $map[$userId][$date]['_stored_overtime_seconds'],
                    $map[$userId][$date]['last_session_check_in_time'],
                    $map[$userId][$date]['last_session_has_no_checkout']
                );
            }
        }

        return $map;
    }

    protected function calculateWorkHours(string $date, ?string $mealBreakTime, ?string $checkInTime, ?string $checkOutTime, ?CompanyRulesModel $rules = null): array
    {
        if (empty($checkInTime) || empty($checkOutTime)) {
            return [
                'work_hours' => '00:00:00',
                'work_hours_seconds' => 0,
                'overtime' => '00:00:00',
                'overtime_seconds' => 0,
                'status' => 'absent',
            ];
        }

        $in = Carbon::parse($date . ' ' . $checkInTime, 'Asia/Kolkata');
        $out = Carbon::parse($date . ' ' . $checkOutTime, 'Asia/Kolkata');
        if ($out->lte($in)) {
            return [
                'work_hours' => '00:00:00',
                'work_hours_seconds' => 0,
                'overtime' => '00:00:00',
                'overtime_seconds' => 0,
                'status' => 'absent',
            ];
        }

        $rules ??= $this->companyRules();
        $grossSeconds = $out->diffInSeconds($in);
        $mealBreakSeconds = $this->timeToSeconds($mealBreakTime);
        $workSeconds = max(0, $grossSeconds - $mealBreakSeconds);
        $requiredSeconds = (int) round(((float) ($rules->working_hours_per_day ?? 8)) * 3600);
        $overtimeSeconds = 0;

        if (!empty($rules->enable_overtime) && $grossSeconds > $requiredSeconds) {
            $overtimeSeconds = $grossSeconds - $requiredSeconds;
            $minimum = (int) (($rules->min_overtime_count_in_minutes ?? 0) * 60);
            if ($overtimeSeconds < $minimum) {
                $overtimeSeconds = 0;
            }
        }

        return [
            'work_hours' => $this->formatSeconds($workSeconds),
            'work_hours_seconds' => $workSeconds,
            'overtime' => $this->formatSeconds($overtimeSeconds),
            'overtime_seconds' => $overtimeSeconds,
            'status' => $this->calculateAttendanceStatus($workSeconds, $rules),
        ];
    }

    protected function calculateAttendanceStatus(int $workedSeconds, ?CompanyRulesModel $rules): string
    {
        if (($rules->payroll_type ?? 'monthly') === 'hourly') {
            return $workedSeconds > 0 ? 'present' : 'absent';
        }

        $fullDaySeconds = (int) round(((float) ($rules->working_hours_per_day ?? 8)) * 3600);
        $halfDaySeconds = (int) round(((float) ($rules->half_day_hours ?? (($rules->working_hours_per_day ?? 8) / 2))) * 3600);

        if ($workedSeconds >= $fullDaySeconds) {
            return 'present';
        }

        if ($halfDaySeconds > 0 && $workedSeconds >= $halfDaySeconds) {
            return 'half-day';
        }

        return 'absent';
    }

    protected function getSaturdayOffDates(Carbon $start, Carbon $end, ?CompanyRulesModel $rules): array
    {
        if (empty($rules->saturday_off_enabled) || empty($rules->saturday_off_pattern)) {
            return [];
        }

        $pattern = collect(explode(',', (string) $rules->saturday_off_pattern))
            ->map(fn($value) => (int) trim($value))
            ->filter()
            ->values()
            ->all();

        if ($pattern === []) {
            return [];
        }

        $dates = [];
        $cursor = $start->copy()->startOfMonth();

        while ($cursor->lte($end)) {
            $saturdayIndex = 0;
            foreach (CarbonPeriod::create($cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()) as $day) {
                if (!$day->isSaturday()) {
                    continue;
                }

                $saturdayIndex++;
                if (in_array($saturdayIndex, $pattern, true) && $day->betweenIncluded($start, $end)) {
                    $dates[] = $day->toDateString();
                }
            }

            $cursor->addMonth()->startOfMonth();
        }

        return array_values(array_unique($dates));
    }

    protected function companyRules(): ?CompanyRulesModel
    {
        $rule = CompanyRulesModel::query()->latest('id')->first();
        if ($rule) {
            return $rule;
        }

        $defaultRule = new CompanyRulesModel();
        $defaultRule->forceFill([
            'payroll_type' => 'monthly',
            'working_hours_per_day' => 8,
            'half_day_hours' => 4,
            'lunch_break' => '00:30:00',
            'start_time' => '09:00:00',
            'grace_period' => 0,
            'enable_overtime' => true,
            'min_overtime_count_in_minutes' => 0,
        ]);
        $defaultRule->open_time = '09:00:00';
        return $defaultRule;
    }

    protected function canManageAttendance(User $user): bool
    {
        return in_array($user->role, ['admin', 'sub-admin', 'hr'], true);
    }

    protected function canSelfMarkAttendance(User $user): bool
    {
        return in_array($user->role, ['hr', 'admin', 'sub-admin'], true);
    }

    protected function apiUser(): ?User
    {
        return Auth::guard('api')->user();
    }

    protected function validateCheckInLocation(User $user, array $location): ?JsonResponse
    {
        $result = app(AttendanceLocationService::class)->validateForUser(
            $user,
            $location['latitude'] ?? null,
            $location['longitude'] ?? null
        );

        if (! $result['allowed']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 422);
        }

        return null;
    }

    protected function today(): Carbon
    {
        return Carbon::now('Asia/Kolkata');
    }

    protected function normalizeTime(?string $time): ?string
    {
        if (!$time) {
            return null;
        }

        return strlen($time) === 5 ? $time . ':00' : $time;
    }

    protected function timeToSeconds(?string $time): int
    {
        if (empty($time)) {
            return 0;
        }

        [$hours, $minutes, $seconds] = array_pad(array_map('intval', explode(':', $time)), 3, 0);

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    protected function formatSeconds(int $seconds): string
    {
        $seconds = max(0, $seconds);

        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    protected function statusRank(?string $status): int
    {
        return match (strtolower((string) $status)) {
            'present' => 3,
            'half-day' => 2,
            'leave' => 1,
            default => 0,
        };
    }

    protected function applyAttendanceLocationSelects($query)
    {
        $query->select('attendances.*');

        $locationTable = $this->detectLocationTable();
        $locationNameColumn = $locationTable ? $this->detectLocationNameColumn($locationTable) : null;

        if (safe_has_column('attendances', 'check_in_location_name')) {
            $query->addSelect('attendances.check_in_location_name');
        } elseif ($locationTable && $locationNameColumn && safe_has_column('attendances', 'check_in_location_id')) {
            $query->leftJoin($locationTable . ' as check_in_locations', 'attendances.check_in_location_id', '=', 'check_in_locations.id')
                ->addSelect(DB::raw("check_in_locations.{$locationNameColumn} as check_in_location_name"));
        }

        if (safe_has_column('attendances', 'check_out_location_name')) {
            $query->addSelect('attendances.check_out_location_name');
        } elseif ($locationTable && $locationNameColumn && safe_has_column('attendances', 'check_out_location_id')) {
            $query->leftJoin($locationTable . ' as check_out_locations', 'attendances.check_out_location_id', '=', 'check_out_locations.id')
                ->addSelect(DB::raw("check_out_locations.{$locationNameColumn} as check_out_location_name"));
        }

        // Fallback: single location id/name columns in attendances.
        if (safe_has_column('attendances', 'location_name')) {
            $query->addSelect('attendances.location_name');
        } elseif ($locationTable && $locationNameColumn && safe_has_column('attendances', 'location_id')) {
            $query->leftJoin($locationTable . ' as attendance_locations', 'attendances.location_id', '=', 'attendance_locations.id')
                ->addSelect(DB::raw("attendance_locations.{$locationNameColumn} as location_name"));
        }

        if (safe_has_column('attendances', 'check_in_location')) {
            $query->addSelect('attendances.check_in_location');
        }

        if (safe_has_column('attendances', 'check_out_location')) {
            $query->addSelect('attendances.check_out_location');
        }

        if (safe_has_column('attendances', 'location')) {
            $query->addSelect('attendances.location');
        }

        return $query;
    }

    protected function detectLocationTable(): ?string
    {
        foreach (['locations', 'attendance_locations', 'location_master', 'office_locations'] as $table) {
            if (safe_has_table($table)) {
                return $table;
            }
        }

        return null;
    }

    protected function detectLocationNameColumn(string $table): ?string
    {
        foreach (['location_name', 'name', 'title'] as $column) {
            if (safe_has_column($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    protected function extractLocationName(object $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($row->{$key}) && trim((string) $row->{$key}) !== '') {
                return trim((string) $row->{$key});
            }
        }

        return null;
    }

    protected function extractLocationFromRequest(Request $request, string $prefix): array
    {
        $latitude = $request->input($prefix . '_latitude', $request->input('latitude'));
        $longitude = $request->input($prefix . '_longitude', $request->input('longitude'));
        $locationName = $request->input($prefix . '_location_name', $request->input('location_name'));
        $ipAddress = $request->ip();

        $lat = is_numeric($latitude) ? round((float) $latitude, 7) : null;
        $lng = is_numeric($longitude) ? round((float) $longitude, 7) : null;

        $name = trim((string) ($locationName ?? ''));
        if ($lat !== null && $lng !== null) {
            $resolvedAddress = $this->resolveAddressFromCoordinates($lat, $lng);
            if ($resolvedAddress !== null) {
                $name = $resolvedAddress;
            }
        }

        if ($name === '' && !empty($ipAddress) && !$this->isLocalIp($ipAddress)) {
            $ipResolvedAddress = $this->resolveAddressFromIp($ipAddress);
            if ($ipResolvedAddress !== null) {
                $name = $ipResolvedAddress;
            }
        }

        if ($name === '' && $lat !== null && $lng !== null) {
            $name = 'Lat ' . $lat . ', Lng ' . $lng; // final fallback if geocoder fails
        }

        return [
            'latitude' => $lat,
            'longitude' => $lng,
            'location_name' => $name !== '' ? $name : null,
            'ip_address' => $ipAddress,
        ];
    }

    protected function appendLocationColumns(array &$values, array $location, string $prefix): void
    {
        $latColumn = $prefix . '_latitude';
        $lngColumn = $prefix . '_longitude';
        $nameColumn = $prefix . '_location_name';

        if (safe_has_column('attendance', $latColumn)) {
            $values[$latColumn] = $location['latitude'] ?? null;
        }

        if (safe_has_column('attendance', $lngColumn)) {
            $values[$lngColumn] = $location['longitude'] ?? null;
        }

        if (safe_has_column('attendance', $nameColumn)) {
            $values[$nameColumn] = $location['location_name'] ?? null;
        }

        $ipColumn = $prefix . '_ip_address';
        if (safe_has_column('attendance', $ipColumn)) {
            $values[$ipColumn] = $location['ip_address'] ?? null;
        }
    }

    protected function resolveAddressFromCoordinates(float $latitude, float $longitude): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'rajinfra-erp-attendance/1.0',
                    'Accept-Language' => 'en',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'zoom' => 18,
                    'addressdetails' => 1,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $payload = $response->json();
            $displayName = trim((string) ($payload['display_name'] ?? ''));

            return $displayName !== '' ? $displayName : null;
        } catch (\Exception $e) {
            Log::warning('Reverse geocoding failed: ' . $e->getMessage(), [
                'lat' => $latitude,
                'lng' => $longitude,
            ]);
            return null;
        }
    }

    protected function resolveAddressFromIp(string $ipAddress): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'rajinfra-erp-attendance/1.0',
                    'Accept' => 'application/json',
                ])
                ->get('http://ip-api.com/json/' . urlencode($ipAddress), [
                    'fields' => 'status,message,city,regionName,zip,country,query',
                ]);

            if (!$response->successful()) {
                return null;
            }

            $payload = $response->json();
            if (($payload['status'] ?? '') !== 'success') {
                return null;
            }

            $parts = array_filter([
                trim((string) ($payload['city'] ?? '')),
                trim((string) ($payload['regionName'] ?? '')),
                trim((string) ($payload['zip'] ?? '')),
                trim((string) ($payload['country'] ?? '')),
            ]);

            if ($parts === []) {
                return null;
            }

            return implode(', ', $parts);
        } catch (\Exception $e) {
            Log::warning('IP geocoding failed: ' . $e->getMessage(), [
                'ip' => $ipAddress,
            ]);
            return null;
        }
    }

    protected function isLocalIp(string $ipAddress): bool
    {
        return in_array($ipAddress, ['127.0.0.1', '::1'], true)
            || str_starts_with($ipAddress, '192.168.')
            || str_starts_with($ipAddress, '10.')
            || preg_match('/^172\.(1[6-9]|2\d|3[0-1])\./', $ipAddress) === 1;
    }
}
