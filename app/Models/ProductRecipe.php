<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecipe extends Model
{
    protected $fillable = [
        'product_id',
        'yield_qty',
        'notes',
        'isDeleted',
    ];

    protected $casts = [
        'yield_qty' => 'decimal:4',
        'isDeleted' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function items()
    {
        return $this->hasMany(ProductRecipeItem::class, 'recipe_id')
            ->where('isDeleted', 0);
    }
}
