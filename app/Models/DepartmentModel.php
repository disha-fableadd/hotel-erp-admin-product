<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentModel extends Model
{
    use HasFactory;

    protected $table = 'department';
    protected $primaryKey = 'id';

    protected $fillable = [
        'branch_id',
        'department_name',
        'enable_overtime',
        'overtime_rate_type',
        'overtime_multiplier',
        'min_overtime_count_in_minutes',
    ];

    public $timestamps = true;
}
