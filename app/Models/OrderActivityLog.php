<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderActivityLog extends Model
{
    protected $table = 'order_activity_logs';

    protected $fillable = [
        'branch_id',
        'order_id',
        'order_number',
        'action',
        'product_id',
        'product_name',
        'quantity',
        'old_quantity',
        'new_quantity',
        'table_no',
        'details',
        'meta',
        'actor_id',
        'actor_name',
        'actor_role',
    ];

    protected $casts = [
        'meta' => 'array',
        'quantity' => 'float',
        'old_quantity' => 'float',
        'new_quantity' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
