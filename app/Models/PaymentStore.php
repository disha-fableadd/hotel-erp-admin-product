<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class PaymentStore extends Model
{
    use HasFactory;

    protected $table = 'payment_store';

    protected $fillable = [
        'client_uuid',
        'branch_id',
        'user_id',
        'order_id',
        'custom_invoice_id',
        'purchase_id',
        'amount',
        'payment_amount',
        'payment_date',
        'payment_method',
        'payment_type',
        'cash_amount',
        'upi_amount',
        'emi_month',
        'remaining_amount',
        'bank_id',
        'status',
        'remarks',
        'isDeleted',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'amount',
        'order_client_uuid',
    ];

    /**
     * Accessor for 'amount' to ensure backward compatibility and sync with Drift DB
     */
    public function getAmountAttribute()
    {
        return $this->attributes['amount'] ?? $this->attributes['payment_amount'] ?? 0.00;
    }

    /**
     * Accessor for 'order_client_uuid' to link with Flutter Drift order references
     */
    public function getOrderClientUuidAttribute()
    {
        if (isset($this->attributes['order_client_uuid'])) {
            return $this->attributes['order_client_uuid'];
        }
        return $this->order?->client_uuid ?? null;
    }



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(BankMaster::class, 'bank_id');
    }

    public function customInvoice()
    {
        return $this->belongsTo(CustomInvoice::class, 'custom_invoice_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchases::class, 'purchase_id');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_id');
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
