<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuantityType extends Model
{
    use HasFactory;

    protected $table='quantity_types';

    protected $fillable = [
        'quantity_type',
        'created_by',
        'branch_id',
        'isDeleted'
    ];
}
