<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchases extends Model
{
    use HasFactory;
    protected $fillable = [
        'branch_id',
        'item',
        'quantity',
        'quantity_type_id',
        'price',
        'purchase_status',
        'payment_status',
        'amount_total',
        'vendor_id',
        'invoice_id',
        'isDeleted',
        'created_by',
        'created_at',
        'updated_at',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'item', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'invoice_id'); // Adjust foreign key as per your schema
    }
    public function quantityType()
    {
        return $this->belongsTo(QuantityType::class, 'quantity_type_id');
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
