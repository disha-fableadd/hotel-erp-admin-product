<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Table_no extends Model
{
    use HasFactory;

    protected $table = 'table_no';

    public const STATUS_BLANK = 'blank';
    public const STATUS_RUNNING = 'running';
    public const STATUS_RESERVED = 'reserved';
    /** Bill printed / awaiting settlement (Petpooja-style yellow) */
    public const STATUS_PRINTED = 'printed';

    protected $fillable = [
        'table_name',
        'table_type',
        'table_status',
        'status',
        'order_id',
        'branch_id',
        'isDeleted',
    ];

    protected $casts = [
        'table_status' => 'integer',
        'isDeleted' => 'integer',
        'order_id' => 'integer',
        'branch_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('isDeleted', 0);
        });
    }

    public function getResolvedStatusAttribute(): string
    {
        $status = strtolower(trim((string) ($this->status ?? '')));

        if (in_array($status, [self::STATUS_BLANK, self::STATUS_RUNNING, self::STATUS_RESERVED, self::STATUS_PRINTED], true)) {
            return $status;
        }

        return match ((int) ($this->table_status ?? 0)) {
            1 => self::STATUS_RUNNING,
            2 => self::STATUS_RESERVED,
            3 => self::STATUS_PRINTED,
            default => self::STATUS_BLANK,
        };
    }

    public static function statusToInt(string $status): int
    {
        return match ($status) {
            self::STATUS_RUNNING => 1,
            self::STATUS_RESERVED => 2,
            self::STATUS_PRINTED => 3,
            default => 0,
        };
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
