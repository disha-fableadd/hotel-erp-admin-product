<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'date',
        'check_in_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_location_name',
        'check_in_ip_address',
        'check_out_time',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_location_name',
        'check_out_ip_address',
        'meal_break',
        'work_hours',
        'overtime',
        'status',
        'reason',
        'description',
        'extraday',
        'checkin_method',
        'is_late',
        'late_minutes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
