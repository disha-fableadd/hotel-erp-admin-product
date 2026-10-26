<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TableReservation extends Model
{
    use SoftDeletes;

    protected $table = 'table_reservations';

    protected $fillable = [
        'table_id',
        'branch_id',
        'customer_name',
        'customer_phone',
        'reservation_date',
        'reservation_time',
        'guests',
        'status',
        'order_id',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'guests'           => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────────

    public function table()
    {
        return $this->belongsTo(Table_no::class, 'table_id');
    }

    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────────

    /** Only active (not cancelled / completed) reservations */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
