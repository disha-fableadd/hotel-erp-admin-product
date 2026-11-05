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
        'kot_pending_cancels',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'kot_pending_cancels' => 'array',
        'tax_breakdown' => 'array',
    ];

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
