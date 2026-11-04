<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_uuid',
        'order_id',
        'user_id',
        'product_id',
        'variant_id',
        'addons',
        'category_id',
        'quantity',
        'kot_printed_qty',
        'quantity_type_id',
        'price',
        'unit_price',
        'item_discount_percent',
        'item_discount_amount',
        'total_amount',
        'branch_id',
        'created_by',
        'isDeleted',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'order_client_uuid',
        'product_client_uuid',
    ];

    public function getOrderClientUuidAttribute()
    {
        return $this->attributes['order_client_uuid'] ?? $this->order?->client_uuid ?? null;
    }

    public function getProductClientUuidAttribute()
    {
        return $this->attributes['product_client_uuid'] ?? $this->product?->client_uuid ?? null;
    }

    protected $casts = [
        'addons' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Bill / KOT display helpers for size + add-ons.
     */
    public function variantLabel(): ?string
    {
        $name = optional($this->variant)->name;
        if ($name) {
            return (string) $name;
        }
        return null;
    }

    public function addonLines(): array
    {
        $raw = $this->addons;
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        if (!is_array($raw)) {
            return [];
        }
        $lines = [];
        foreach ($raw as $a) {
            if (is_array($a)) {
                $n = trim((string) ($a['name'] ?? ''));
                if ($n !== '') {
                    $lines[] = $n;
                }
            } elseif (is_string($a) && trim($a) !== '') {
                $lines[] = trim($a);
            }
        }
        return $lines;
    }

    public function displayName(): string
    {
        $name = (string) (optional($this->product)->name ?? 'Item');
        $variant = $this->variantLabel();
        if ($variant) {
            $name .= ' (' . $variant . ')';
        }
        $addons = $this->addonLines();
        if ($addons) {
            $name .= ' + ' . implode(', ', $addons);
        }
        return $name;
    }
    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }

    public function invoice()
    {
        return $this->belongsTo(Order::class, 'order_id'); // Assuming invoice means the order
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
