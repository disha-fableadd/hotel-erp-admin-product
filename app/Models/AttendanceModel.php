<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceModel extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'user_id', 'date', 'check_in_time', 'check_in_latitude', 'check_in_longitude', 'check_in_location_name', 'check_in_ip_address',
        'check_out_time', 'check_out_latitude', 'check_out_longitude', 'check_out_location_name', 'check_out_ip_address',
        'meal_break', 'work_hours', 'overtime',
        'created_at', 'updated_at', 'status',
        'checkin_method', 'is_late', 'late_minutes'
    ];

    public $timestamps = true;

    public static function totalCountForUsers($userIds): int
    {
        $ids = self::normalizeUserIds($userIds);

        if (empty($ids)) {
            return 0;
        }

        return self::query()
            ->whereIn('user_id', $ids)
            ->count();
    }

    public static function latestRowsByUsersForDate($userIds, string $date): Collection
    {
        $ids = self::normalizeUserIds($userIds);

        if (empty($ids)) {
            return collect();
        }

        return self::query()
            ->whereIn('user_id', $ids)
            ->whereDate('date', $date)
            ->latest('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');
    }

    public static function latestActivityTimeByUsersForDate($userIds, string $date)
    {
        $ids = self::normalizeUserIds($userIds);

        if (empty($ids)) {
            return null;
        }

        return self::query()
            ->whereIn('user_id', $ids)
            ->whereDate('date', $date)
            ->latest('updated_at')
            ->first()?->updated_at;
    }

    public static function isPresentRow(?self $row): bool
    {
        if (! $row) {
            return false;
        }

        $status = strtolower(trim((string) $row->status));
        $presentStatuses = ['present', 'half-day', 'half day', 'halfday', 'checked-in', 'checked in', 'checked-out', 'checked out', 'late'];
        $absentStatuses = ['absent', 'leave'];

        if (! empty($row->check_in_time) || ! empty($row->check_out_time)) {
            return true;
        }

        if (! empty($row->work_hours) && $row->work_hours !== '00:00:00') {
            return true;
        }

        if (in_array($status, $presentStatuses, true)) {
            return true;
        }

        return $status !== '' && ! in_array($status, $absentStatuses, true);
    }

    private static function normalizeUserIds($userIds): array
    {
        return collect($userIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    // Attendance Report
    public function getAttendanceReport($departmentId = null, $employeeId = null, $startDate = null, $endDate = null, $year = null, $month = null)
    {
        $query = DB::table('attendances')
            ->select(
                'attendance.user_id',
                'user_info.firstname',
                'user_info.lastname',
                'department.department_name',
                'attendance.date',
                'attendance.status'
            )
            ->leftJoin('user_info', 'user_info.user_id', '=', 'attendance.user_id')
            ->leftJoin('department', 'department.id', '=', 'user_info.department_id');

        if (! empty($employeeId)) {
            $query->where('attendance.user_id', (int) $employeeId);
        }

        if (! empty($departmentId)) {
            $query->where('user_info.department_id', (int) $departmentId);
        }

        if (! empty($startDate)) {
            $query->whereDate('attendance.date', '>=', $startDate);
        }

        if (! empty($endDate)) {
            $query->whereDate('attendance.date', '<=', $endDate);
        }

        if (! empty($year)) {
            $query->whereYear('attendance.date', $year);
        }

        if (! empty($month)) {
            $query->whereMonth('attendance.date', $month);
        }

        return $query->orderBy('attendance.date', 'DESC')->get();
    }

    // Hours by status
    public function getHoursByStatus()
    {
        return DB::table($this->table)
            ->select('status', DB::raw('SUM(work_hours) as total_hours'))
            ->groupBy('status')
            ->get();
    }
}
