<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecipeItem extends Model
{
    protected $fillable = [
        'recipe_id',
        'ingredient_product_id',
        'quantity',
        'quantity_type_id',
        'isDeleted',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'isDeleted' => 'boolean',
    ];

    public function recipe()
    {
        return $this->belongsTo(ProductRecipe::class, 'recipe_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Product::class, 'ingredient_product_id');
    }

    public function quantityType()
    {
        return $this->belongsTo(QuantityType::class, 'quantity_type_id');
    }
}
