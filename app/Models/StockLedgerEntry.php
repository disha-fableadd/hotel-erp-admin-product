<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedgerEntry extends Model
{
    protected $fillable = [
        'product_id',
        'branch_id',
        'direction',
        'quantity',
        'unit_cost',
        'balance_after',
        'entry_type',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'balance_after' => 'decimal:4',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
