<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'discount_percent',
        'final_price',
        'duration',
        'start_date',
        'end_date',
        'subtitle',
        'user_limit',
        'branch_limit',
        'storage_limit',
        'is_active',
        'sub_branch_id',
        'features',
        'sub_modules',
        'total_amount',
        'total_price',
        'renewal_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'final_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'renewal_price' => 'decimal:2',
        'user_limit' => 'integer',
        'branch_limit' => 'integer',
        'storage_limit' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'features' => 'array',
        'sub_modules' => 'array',
    ];

    /**
     * Determine whether the plan is a 1-month (monthly) plan.
     */
    public function isMonthly(): bool
    {
        $duration = strtolower(trim((string) ($this->duration ?? '')));
        if ($duration !== '') {
            if (str_contains($duration, 'month') || $duration === '1' || $duration === 'm') {
                return true;
            }
            if (str_contains($duration, 'year') || str_contains($duration, 'annual') || $duration === '12' || $duration === 'y') {
                return false;
            }
        }

        if (function_exists('tenant') && tenant('billing_cycle')) {
            $cycle = strtolower(trim((string) tenant('billing_cycle')));
            if (str_contains($cycle, 'month')) {
                return true;
            }
            if (str_contains($cycle, 'year') || str_contains($cycle, 'annual')) {
                return false;
            }
        }

        if (! empty($this->start_date) && ! empty($this->end_date)) {
            try {
                $start = \Carbon\Carbon::parse($this->start_date)->startOfDay();
                $end = \Carbon\Carbon::parse($this->end_date)->startOfDay();
                if ($start->diffInDays($end) <= 45) {
                    return true;
                }
                return false;
            } catch (\Throwable) {
            }
        }

        return false;
    }

    /**
     * Get the number of warning days before plan expiration.
     * 1-month plan: 10 days before expiry.
     * Yearly plan: 1 month (30 days) before expiry.
     */
    public function getWarningDays(): int
    {
        return $this->isMonthly() ? 10 : 30;
    }
}
