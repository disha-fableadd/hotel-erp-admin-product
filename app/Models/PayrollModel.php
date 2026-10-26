<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollModel extends Model
{
    protected $table = 'payroll';

    protected $primaryKey = 'id';

    protected $fillable = [
        'branch_id',
        'user_id',
        'leave_type',
        'remaining_paid_leaves',
        'month_year',
        'total_leaves',
        'total_half_day',
        'total_paid_leaves',
        'used_paid_leaves',
        'salary_amount',
        'acc_number',
        'bank_name',
        'ifsc_code',
        'acc_in_name',
        'branch_name',
        'branch_code',
        'tax_deduction',
        'incentive',
        'salary_deduction',
        'bonuses',
        'net_salary',
        'payment_date',
        'payment_status',
        'created_by',
        'worked_hours',
        'overtime_pay',
        'total_overtime_hours',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'leave_type' => 'integer',
        'remaining_paid_leaves' => 'float',
        'total_leaves' => 'float',
        'total_half_day' => 'float',
        'total_paid_leaves' => 'float',
        'used_paid_leaves' => 'float',
        'salary_amount' => 'float',
        'tax_deduction' => 'float',
        'incentive' => 'float',
        'salary_deduction' => 'float',
        'bonuses' => 'float',
        'net_salary' => 'float',
        'payment_date' => 'date:Y-m-d',
        'created_by' => 'integer',
        'worked_hours' => 'float',
        'overtime_pay' => 'float',
        'total_overtime_hours' => 'float',
    ];

    public $timestamps = true;
}
