<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAddon extends Model
{
    protected $fillable = [
        'name',
        'price',
        'branch_id',
        'status',
        'isDeleted',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'isDeleted' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_addon_product', 'addon_id', 'product_id')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('isDeleted', 0)->where('status', 'active');
    }
}
