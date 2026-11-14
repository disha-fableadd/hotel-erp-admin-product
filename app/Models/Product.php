<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'vendor_id',
        'category_id',
        'brand_id',
        'branch_id',
        'name',
        'SKU',
        'barcode',
        'description',
        'price',
        'cost_price',
        'quantity_type_id',
        'images',
        'quantity',
        'min_stock',
        'isDeleted',
        'availablility',
        'status',
        'product_kind',
        'item_type',
        'food_type',
        'tax_id',
        'tax_inclusive',
        'is_pos_available',
        'track_inventory',
        'recipe_required',
        'kitchen_station',
        'prep_time_minutes',
        'display_order',
        'created_at',
        'updated_at',
        'create_by',
    ];

    protected $casts = [
        'tax_inclusive' => 'boolean',
        'is_pos_available' => 'boolean',
        'track_inventory' => 'boolean',
        'recipe_required' => 'boolean',
        'cost_price' => 'decimal:2',
        'min_stock' => 'decimal:4',
    ];

    protected $appends = ['image_url', 'is_liquor', 'resolved_item_type'];

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Explicit product item_type, else inherit from category (Flutter food/liquor split).
     */
    public function resolveItemType(): string
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'item_type')) {
            $raw = strtolower(trim((string) ($this->attributes['item_type'] ?? '')));
            if (in_array($raw, ['food', 'beverage', 'liquor', 'tobacco', 'general', 'bar'], true)) {
                return $raw === 'bar' ? 'liquor' : $raw;
            }
        }

        $cat = $this->relationLoaded('category') ? $this->category : null;
        if (! $cat && ! empty($this->category_id)) {
            try {
                $cat = Category::find($this->category_id);
            } catch (\Throwable) {
                $cat = null;
            }
        }
        if ($cat instanceof Category) {
            return $cat->resolveItemType();
        }

        return 'food';
    }

    public function getResolvedItemTypeAttribute(): string
    {
        return $this->resolveItemType();
    }

    public function getIsLiquorAttribute(): int
    {
        return in_array($this->resolveItemType(), ['liquor', 'beverage', 'tobacco', 'bar'], true) ? 1 : 0;
    }
    
    // Relationship with Brand
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function quantityType()
    {
        return $this->belongsTo(QuantityType::class, 'quantity_type_id');
    }

    public function tax()
    {
        return $this->belongsTo(TaxRate::class, 'tax_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id')
            ->where('isDeleted', 0)
            ->orderBy('sort_order');
    }

    public function addons()
    {
        return $this->belongsToMany(ProductAddon::class, 'product_addon_product', 'product_id', 'addon_id')
            ->where('product_addons.isDeleted', 0)
            ->withTimestamps();
    }

    public function recipe()
    {
        return $this->hasOne(ProductRecipe::class, 'product_id')
            ->where('isDeleted', 0);
    }


    // protected static function booted()
    // {
    //     static::creating(function ($product) {
    //         do {
    //             $barcode = 'PRD' . mt_rand(1000000000, 9999999999); // 13-char barcode
    //         } while (Product::where('barcode', $barcode)->exists());

    //         $product->barcode = $barcode;
    //     });
    // }
    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->barcode)) {
                do {
                    $barcode = 'PRD' . mt_rand(1000000000, 9999999999);
                } while (Product::where('barcode', $barcode)->exists());

                $product->barcode = $barcode;
            }
        });
    }
    public function getImageUrlAttribute()
    {
        $images = $this->images;

        if ($images) {
            $decoded = json_decode($images, true);

            if (is_array($decoded)) {
                $flatImages = [];
                array_walk_recursive($decoded, function($a) use (&$flatImages) { 
                    if (is_string($a)) {
                        $flatImages[] = $a; 
                    }
                });
                
                return array_map(function ($img) {
                    return tenant_image_url($img);
                }, array_filter($flatImages));
            }

            if (is_string($images)) {
                return [tenant_image_url($images)];
            }
        }

        return [image_path('admin/assets/img/product/noimage.png')];
    }

    public function product_inventory()
    {
        return $this->hasOne(ProductInventory::class, 'product_id');
    }

    public function isPreparedFood(): bool
    {
        return ($this->product_kind ?? 'general') === 'prepared_food';
    }

    /**
     * Finished dishes / recipe items should not block POS on finished-good qty.
     */
    public function tracksInventory(): bool
    {
        if ($this->isPreparedFood()) {
            return false;
        }

        if (array_key_exists('track_inventory', $this->getAttributes())) {
            return (bool) $this->track_inventory;
        }

        return true;
    }

    /**
     * Hotel POS allows selling below zero stock (negative selling).
     */
    public function allowsOversell(): bool
    {
        return true;
    }

    public function shouldBlockSaleForQty($qtyNeeded): bool
    {
        if ($this->allowsOversell() || ! $this->tracksInventory()) {
            return false;
        }

        return (float) $this->quantity < (float) $qtyNeeded;
    }

    /** Show "Out of Stock" badge (informational). Prepared food never shows it. */
    public function showOutOfStockBadge(): bool
    {
        if ($this->isPreparedFood() || ! $this->tracksInventory()) {
            return false;
        }

        return (float) $this->quantity <= 0;
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
