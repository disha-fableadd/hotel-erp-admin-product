<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_id',
        'plan_id',
        'name',
        'username',
        'email',
        'phone',
        'role',
        'state_code',
        'gst_number',
        'pan_number',
        'password',
        'isDeleted',
        'profile_image',
        'haspermission',
        'created_by',
        'face_descriptor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['profile_image_url'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'face_descriptor'   => 'array',
    ];

    public function details()
    {
        return $this->hasOne(UserDetail::class, 'user_id', 'id');
    }
    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }
    public function designation()
    {
        return $this->hasOneThrough(
            DesignationModel::class,
            UserDetail::class,
            'user_id',
            'id',
            'id',
            'designation_id'
        );
    }
    public function department()
    {
        return $this->hasOneThrough(
            DepartmentModel::class,
            UserDetail::class,
            'user_id',
            'id',
            'id',
            'department_id'
        );
    }
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return tenant_image_url($this->profile_image);
        }

        // fallback image
        return image_path('admin/assets/img/customer/customer5.jpg');
    }

    public function permissions()
    {
        return $this->hasMany(\App\Models\UserPermission::class, 'user_id');
    }

    public function userPermissions()
    {
        return $this->hasMany(\App\Models\UserPermission::class, 'user_id');
    }

    public function plan()
    {
        return $this->belongsTo(\App\Models\Plan::class, 'plan_id');
    }

    /**
     * Get the active plan for the user/admin.
     */
    public function getActivePlan()
    {
        if ($this->plan_id) {
            $plan = $this->plan ?? \App\Models\Plan::find($this->plan_id);
            if ($plan && $plan->is_active) {
                return $plan;
            }
        }

        if (! empty($this->branch_id)) {
            $plan = \App\Models\Plan::where('sub_branch_id', $this->branch_id)->where('is_active', 1)->latest('id')->first();
            if ($plan) {
                return $plan;
            }
        }

        if (in_array($this->role, ['admin', 'sub-admin'], true)) {
            $plan = \App\Models\Plan::where('sub_branch_id', $this->id)->where('is_active', 1)->latest('id')->first();
            if ($plan) {
                return $plan;
            }
        }

        return \App\Models\Plan::where('is_active', 1)->latest('id')->first();
    }

    public function getLatestPlan()
    {
        if ($this->plan_id) {
            $plan = $this->plan ?? \App\Models\Plan::find($this->plan_id);
            if ($plan) {
                return $plan;
            }
        }

        if (! empty($this->branch_id)) {
            $plan = \App\Models\Plan::where('sub_branch_id', $this->branch_id)->latest('id')->first();
            if ($plan) {
                return $plan;
            }
        }

        if (in_array($this->role, ['admin', 'sub-admin'], true)) {
            $plan = \App\Models\Plan::where('sub_branch_id', $this->id)->latest('id')->first();
            if ($plan) {
                return $plan;
            }
        }

        return \App\Models\Plan::latest('id')->first();
    }

    /**
     * Check plan expiry status using Carbon date comparison.
     */
    public function checkPlanExpiryStatus(): array
    {
        $plan = $this->getLatestPlan();

        if (! $plan || empty($plan->end_date)) {
            return [
                'status' => 'active',
                'message' => null,
                'end_date' => null,
            ];
        }

        $today = Carbon::today('Asia/Kolkata');
        $endDate = Carbon::parse($plan->end_date, 'Asia/Kolkata')->startOfDay();
        $warningDays = method_exists($plan, 'getWarningDays') ? $plan->getWarningDays() : 30;
        $warningStartDate = $endDate->copy()->subDays($warningDays);

        if ($today->gt($endDate)) {
            if ($plan->is_active) {
                $plan->is_active = 0;
                $plan->save();
            }

            return [
                'status' => 'expired',
                'message' => 'Your subscription plan has expired. Please renew your plan to continue using the system.',
                'end_date' => $endDate->format('d-m-Y'),
            ];
        }

        // If subscription has been renewed/extended, auto-reactivate the plan
        if (! $plan->is_active && $today->lte($endDate)) {
            $plan->is_active = 1;
            $plan->save();
        }

        if ($today->gte($warningStartDate)) {
            $formattedDate = $endDate->format('d-m-Y');
            return [
                'status' => 'warning',
                'message' => "⚠️ Your subscription plan will expire on {$formattedDate}. Please renew your plan to avoid interruption of service.",
                'end_date' => $formattedDate,
            ];
        }

        return [
            'status' => 'active',
            'message' => null,
            'end_date' => $endDate->format('d-m-Y'),
        ];
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
