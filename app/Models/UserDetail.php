<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'designation_id',
        'department_id',
        'joining_date',
        'working_location',
        'shift_time',
        'salary',
        'face_photo',
        'address',
        'city',
        'gst_number',
        'pan_number',
        'country',
        'isDeleted',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'designation_id' => 'integer',
        'department_id' => 'integer',
        'joining_date' => 'date:Y-m-d',
        'salary' => 'decimal:2',
        'isDeleted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function designation()
    {
        return $this->belongsTo(DesignationModel::class, 'designation_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id', 'id');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Kolkata');
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }
}
