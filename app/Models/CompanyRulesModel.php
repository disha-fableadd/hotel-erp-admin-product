<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyRulesModel extends Model
{
    protected $table = 'company_rules';

    protected $fillable = [
        'enable_payroll',
        'payroll_type',
        'working_hours_per_day',
        'include_holidays_in_working_days',
        'half_day_hours',
        'sunday_off',
        'sunday_pay_type',
        'saturday_off_enabled',
        'saturday_off_type',
        'saturday_off_pattern',
        'saturday_half_day_enabled',
        'saturday_half_day_pattern',
        'saturday_pay_type',
        'yearly_holidays',
        'enable_tax',
        'tax_type',
        'tax',
        'salary_above_tax',
        'lunch_break',
        'start_time',
        'half_time',
        'end_time',
        'grace_period',
        'enable_overtime',
        'overtime_multiplier',
        'overtime_rate_type',
        'min_overtime_count_in_minutes',
        'enable_pf',
        'employee_pf',
        'employer_pf',
        'enable_esi',
        'employee_esi',
        'employer_esi',
    ];

    protected $casts = [
        'enable_payroll' => 'boolean',
        'include_holidays_in_working_days' => 'boolean',
        'sunday_off' => 'boolean',
        'saturday_off_enabled' => 'boolean',
        'saturday_half_day_enabled' => 'boolean',
        'enable_tax' => 'boolean',
        'enable_overtime' => 'boolean',
        'enable_pf' => 'boolean',
        'enable_esi' => 'boolean',
        'working_hours_per_day' => 'decimal:2',
        'half_day_hours' => 'decimal:2',
        'tax' => 'decimal:2',
        'salary_above_tax' => 'decimal:2',
        'overtime_multiplier' => 'decimal:2',
        'min_overtime_count_in_minutes' => 'integer',
        'grace_period' => 'integer',
        'yearly_holidays' => 'integer',
        'employee_pf' => 'decimal:2',
        'employer_pf' => 'decimal:2',
        'employee_esi' => 'decimal:2',
        'employer_esi' => 'decimal:2',
    ];
}
