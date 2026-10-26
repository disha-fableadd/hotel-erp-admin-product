<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayCalendarModel extends Model
{
    use HasFactory;

    protected $table            = 'holiday_calendar';
    protected $primaryKey       = 'id';

    protected $fillable    = [
        'branch_id',
        'title',
        'holiday_date',
        'description',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;

    protected $casts = [
        'holiday_date' => 'date:Y-m-d',
    ];
}
