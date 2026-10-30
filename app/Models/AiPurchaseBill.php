<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPurchaseBill extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['extraction' => 'array', 'reviewed_data' => 'array'];
}
