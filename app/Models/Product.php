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
        'quantity_type_id',
        'images',
        'quantity',
        'isDeleted',
        'availablility',
        'status',
        'created_at',
        'updated_at',
        'create_by',
    ];

    protected $appends = ['image_url'];

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
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
