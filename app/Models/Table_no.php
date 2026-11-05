<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Table_no extends Model
{
    use HasFactory;

    protected $table = 'table_no';

    public const STATUS_BLANK = 'blank';
    public const STATUS_RUNNING = 'running';
    public const STATUS_RESERVED = 'reserved';
    /** Bill printed / awaiting settlement (Petpooja-style yellow) */
    public const STATUS_PRINTED = 'printed';

    /** Allowed seating types (web + Flutter sync / get-all-tables) */
    public const TABLE_TYPES = ['AC', 'Non AC', 'Premium', 'Rooftop'];

    protected $fillable = [
        'table_name',
        'table_type',
        'table_status',
        'status',
        'order_id',
        'branch_id',
        'isDeleted',
        'client_uuid',
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

        // Flutter Drift sync keys rows by client_uuid — always set on web/API create
        static::creating(function (Table_no $table) {
            if (empty($table->client_uuid)) {
                $table->client_uuid = (string) Str::uuid();
            }
            if ($table->status === null || $table->status === '') {
                $table->status = self::STATUS_BLANK;
            }
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
