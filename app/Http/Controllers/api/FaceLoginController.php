<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\LeaveModel;
use App\Models\CompanyRulesModel;
use App\Services\AttendanceLocationService;
use Carbon\Carbon;

class FaceLoginController extends Controller
{
    /**
     * Return all users with a registered face_photo for client-side matching.
     */
    public function getFaceData(Request $request)
    {
        try {
            $users = DB::table('users')
                ->join('user_details', 'users.id', '=', 'user_details.user_id')
                ->whereNotNull('user_details.face_photo')
                ->where('user_details.face_photo', '!=', '')
                ->where('users.isDeleted', 0)
                ->whereIn('users.role', ['admin', 'staff', 'hr', 'manager'])
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.role',
                    'users.branch_id',
                    'user_details.face_photo'
                )
                ->get()
                ->map(function ($user) {
                    $photo = $user->face_photo;

                    // If already a full URL (e.g. previously stored as http://...) use as-is.
                    if (!empty($photo) && str_starts_with($photo, 'http')) {
                        return $user;
                    }

                    // Strip any "faces/" prefix and serve via the dedicated /face-photo/ route
                    // which reads directly from disk — no storage symlink required.
                    $filename = basename($photo);
                    $user->face_photo = url('/face-photo/' . $filename);

                    return $user;
                });

            return response()->json([
                'status' => true,
                'data'   => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch face data: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Handle face login: authenticate the matched user, record check-in, and return redirect URL.
     *
     * Root-cause fix: Previously this method only logged the user in but never
     * inserted/updated the attendance record. As a result check_in_time was always
     * NULL and the calendar showed "Absent" with In: - | Out: -.
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $userId = (int) $request->user_id;
        $user   = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'error'  => 'User not found. Please contact admin.'
            ], 404);
        }

        if ($user->isDeleted) {
            return response()->json([
                'status' => false,
                'error'  => 'This account has been deactivated.'
            ], 403);
        }

        $location = $this->extractLocationFromRequest($request);
        // Location check bypassed for face login as requested
        /*
        $locationCheck = app(AttendanceLocationService::class)->validateForUser(
            $user,
            $location['latitude'],
            $location['longitude']
        );

        if (! $locationCheck['allowed']) {
            return response()->json([
                'status' => false,
                'error'  => $locationCheck['message'],
            ], 422);
        }
        */

        // --- Duplicate Face Photo Check ---
        // If multiple users have uploaded the exact same face photo, prevent login.
        if ($user->details && $user->details->face_photo) {
            $userFacePath = storage_path('app/public/' . $user->details->face_photo);
            if (file_exists($userFacePath)) {
                $userFileSize = filesize($userFacePath);
                $userFaceHash = null;

                $otherUsers = DB::table('user_details')
                    ->where('user_id', '!=', $user->id)
                    ->whereNotNull('face_photo')
                    ->where('face_photo', '!=', '')
                    ->get();

                foreach ($otherUsers as $other) {
                    $otherFacePath = storage_path('app/public/' . $other->face_photo);
                    if (file_exists($otherFacePath) && filesize($otherFacePath) === $userFileSize) {
                        if ($userFaceHash === null) {
                            $userFaceHash = hash_file('sha256', $userFacePath);
                        }
                        if (hash_file('sha256', $otherFacePath) === $userFaceHash) {
                            return response()->json([
                                'status' => false,
                                'error'  => 'This face is associated with multiple users. Please log in using email and password, or update your face photo.'
                            ], 403);
                        }
                    }
                }
            }
        }
        // ----------------------------------

        // 1. Log the user in via session (web guard)
        Auth::guard('web')->login($user);

        // 2. Generate a Passport API token for localStorage use
        $token = $user->createToken('FaceLoginToken')->accessToken;

        // 3. ── Record attendance check-in ──────────────────────────────────────
        $checkInSaved = false;
        try {
            $checkInSaved = $this->recordFaceCheckIn($user, $location);
            Log::info('FaceLogin: check-in ' . ($checkInSaved ? 'saved' : 'skipped (already checked in)') . ' for user ' . $user->id . ' (' . $user->name . ')');
        } catch (\Throwable $e) {
            // Never block the login itself if attendance fails — just log it
            Log::error('FaceLogin: attendance check-in FAILED for user ' . $user->id . ' (' . $user->name . '): ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
        }
        // ─────────────────────────────────────────────────────────────────────

        // 4. Determine redirect based on role
        $redirect = route('auth.dashboard');
        if (strtolower($user->role) === 'staff') {
            $redirect = route('auth.staff-dashboard');
        } elseif (strtolower($user->role) === 'hr') {
            $redirect = route('hr.dashboard');
        }

        return response()->json([
            'status'         => true,
            'token'          => $token,
            'redirect'       => $redirect,
            'check_in_saved' => $checkInSaved,
            'login_method'   => 'face',
            'user'           => [
                'id'        => $user->id,
                'name'      => $user->name,
                'role'      => $user->role,
                'branch_id' => $user->branch_id,
            ]
        ]);
    }

    /**
     * Record a face-recognition check-in for the given user.
     * Returns true if a new / updated record was saved, false if already checked-in.
     */
    private function recordFaceCheckIn(User $user, array $location = []): bool
    {
        $now          = Carbon::now('Asia/Kolkata');
        $today        = $now->toDateString();
        $checkInTime  = $now->format('H:i:s');
        $rules = CompanyRulesModel::query()->latest('id')->first();
        if (! $rules) {
            $rules = new CompanyRulesModel();
            $rules->forceFill([
                'lunch_break' => '00:30:00',
                'start_time' => '09:00:00',
                'grace_period' => 0,
            ]);
            $rules->open_time = '09:00:00';
        }

        // Guard: if there is already an open check-in today, don't create a duplicate
        $openRecord = DB::table('attendances')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->orderByDesc('id')
            ->first();

        if ($openRecord) {
            // Already checked in — nothing to do
            return false;
        }

        // Check if there is already ANY attendance row for today (e.g. manually created absent)
        $existingRecord = DB::table('attendances')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->orderByDesc('id')
            ->first();

        if ($existingRecord && empty($existingRecord->check_in_time)) {
            // Update existing absent/blank row with check-in details
            DB::table('attendances')->where('id', $existingRecord->id)->update([
                'check_in_time'    => $checkInTime,
                'status'           => 'present',
                'checkin_method'   => 'face_recognition',
                'check_in_latitude' => $location['latitude'] ?? null,
                'check_in_longitude' => $location['longitude'] ?? null,
                'check_in_location_name' => $location['location_name'] ?? null,
                'check_in_ip_address' => $location['ip_address'] ?? null,
                'updated_at'       => $now->toDateTimeString(),
            ]);
            try {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type' => 'attendance',
                    'title' => 'Staff Checked In',
                    'message' => $user->name . ' has checked in via Face Recognition.',
                    'link' => '/staff/attendance/summary',
                    'branch_id' => $user->branch_id ?? '0',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Face check-in notification failed: ' . $e->getMessage());
            }
            return true;
        }

        // Calculate late status
        $startTime   = $rules->start_time ?? $rules->open_time ?? '09:00:00';
        $gracePeriod = (int) ($rules->grace_period ?? 0);
        $allowed     = $this->timeToSeconds($startTime) + ($gracePeriod * 60);
        $isLate      = $this->timeToSeconds($checkInTime) > $allowed ? 1 : 0;
        $lateMinutes = $isLate ? (int) ceil(($this->timeToSeconds($checkInTime) - $allowed) / 60) : 0;

        // Determine branch
        $branchId = $user->branch_id ?: $user->id;

        // Insert a fresh check-in record
        DB::table('attendances')->insert([
            'user_id'        => $user->id,
            'branch_id'      => $branchId,
            'date'           => $today,
            'check_in_time'  => $checkInTime,
            'meal_break'     => $rules->lunch_break ?? '00:30:00',
            'work_hours'     => '00:00:00',
            'overtime'       => '00:00:00',
            'status'         => 'present',
            'checkin_method' => 'face_recognition',
            'check_in_latitude' => $location['latitude'] ?? null,
            'check_in_longitude' => $location['longitude'] ?? null,
            'check_in_location_name' => $location['location_name'] ?? null,
            'check_in_ip_address' => $location['ip_address'] ?? null,
            'is_late'        => $isLate,
            'late_minutes'   => $lateMinutes,
            'created_at'     => $now->toDateTimeString(),
            'updated_at'     => $now->toDateTimeString(),
        ]);

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
                'message' => $user->name . ' has checked in via Face Recognition.',
                'link' => '/staff/attendance/summary',
                'branch_id' => $user->branch_id ?? '0',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Face check-in notification failed: ' . $e->getMessage());
        }

        return true;
    }

    private function extractLocationFromRequest(Request $request): array
    {
        $latitude = $request->input('latitude', $request->input('check_in_latitude'));
        $longitude = $request->input('longitude', $request->input('check_in_longitude'));
        $locationName = $request->input('location_name', $request->input('check_in_location_name'));

        return [
            'latitude' => is_numeric($latitude) ? round((float) $latitude, 7) : null,
            'longitude' => is_numeric($longitude) ? round((float) $longitude, 7) : null,
            'location_name' => is_string($locationName) && trim($locationName) !== '' ? trim($locationName) : null,
            'ip_address' => $request->ip(),
        ];
    }

    private function timeToSeconds(?string $time): int
    {
        if (empty($time)) return 0;
        [$h, $m, $s] = array_pad(array_map('intval', explode(':', $time)), 3, 0);
        return ($h * 3600) + ($m * 60) + $s;
    }
}
