<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'is_sound',
        'branch_id'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_sound' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['date', 'formatted_date', 'time_ago'];

    public function getDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d') : null;
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('d M Y, h:i A') : null;
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at ? $this->created_at->diffForHumans() : null;
    }
}
