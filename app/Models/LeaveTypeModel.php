<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveTypeModel extends Model
{
    use HasFactory;

    protected $table = 'leave_type';
    protected $primaryKey = 'id';

    protected $fillable = [
        'branch_id',
        'leave_type',
        'number_of_leaves',
        'allow_half_day',
        'requires_approval',
        'created_by',
    ];

    public $timestamps = true;

    protected $casts = [
        'allow_half_day' => 'boolean',
        'requires_approval' => 'boolean',
        'number_of_leaves' => 'integer',
        'created_by' => 'integer',
    ];
}
