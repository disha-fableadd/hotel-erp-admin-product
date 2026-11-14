<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable = [
        'client_uuid',
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'discount',
        'tax_id',
        'tax_breakdown',
        'gst_option',
        'gst_apply_mode',
        'overall_gst_rate',
        'service_tax_rate',
        'service_tax_amount',
        'service_tax_name',
        'branch_id',
        'created_by',
        'total_amount',
        'remaining_amount',
        'payment_status',
        'payment_method',
        'order_invoice',
        'isDeleted',
        'table_no',
        'type',
        'order_type',
        'delivery_status',
        'delivered_by',
        'assigned_staff_id',
        'table_status',
        'bill_printed',
        'bill_printed_at',
        'kot_pending_cancels',
        'kot_remarks',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'kot_pending_cancels' => 'array',
        'tax_breakdown' => 'array',
        'bill_printed_at' => 'datetime',
        'overall_gst_rate' => 'decimal:2',
        'service_tax_rate' => 'decimal:2',
        'service_tax_amount' => 'decimal:2',
    ];

    protected $appends = [
        'invoice_generated',
        'invoice_number',
        'order_lifecycle_status',
    ];

    /**
     * 1 when this order counts as a sales invoice / settled or printed bill (not only an open table).
     */
    public function getInvoiceGeneratedAttribute(): int
    {
        $pay = strtolower(trim((string) ($this->payment_status ?? '')));
        if (in_array($pay, ['completed', 'paid', 'partially', 'partially paid'], true)) {
            return 1;
        }
        if ((int) ($this->bill_printed ?? 0) === 1) {
            return 1;
        }
        if (! empty($this->attributes['order_invoice'] ?? null)) {
            return 1;
        }

        return 0;
    }

    public function getInvoiceNumberAttribute(): ?string
    {
        $inv = $this->attributes['order_invoice'] ?? null;
        if (is_string($inv) && trim($inv) !== '') {
            return trim($inv);
        }

        return ! empty($this->order_number) ? (string) $this->order_number : null;
    }

    /**
     * active_table | bill_printed | settled_invoice | cancelled
     */
    public function getOrderLifecycleStatusAttribute(): string
    {
        $delivery = strtolower(trim((string) ($this->delivery_status ?? '')));
        if (in_array($delivery, ['cancelled', 'canceled', 'void', 'voided'], true)) {
            return 'cancelled';
        }

        $pay = strtolower(trim((string) ($this->payment_status ?? '')));
        if (in_array($pay, ['completed', 'paid'], true)) {
            return 'settled_invoice';
        }

        if ((int) ($this->bill_printed ?? 0) === 1 || in_array($pay, ['partially', 'partially paid'], true)) {
            return 'bill_printed';
        }

        if ((int) ($this->table_status ?? 0) === 1 && in_array($delivery, ['running', 'pending', ''], true)) {
            return 'active_table';
        }

        if (in_array($pay, ['pending', 'unpaid'], true) && (int) ($this->table_status ?? 0) !== 1) {
            return 'bill_printed';
        }

        return 'settled_invoice';
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function payments()
    {
        return $this->hasMany(PaymentStore::class, 'order_id', 'id');
    }
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'order_id', 'id');
    }
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function tableNo()
    {
        return $this->belongsTo(Table_no::class, 'table_no', 'id');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Kolkata');
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }
}
