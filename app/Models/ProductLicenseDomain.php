<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLicenseDomain extends Model
{
    protected $fillable = [
        'product_license_id',
        'product_code',
        'domain',
        'installation_id',
        'activated_at',
        'last_verified_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(ProductLicense::class, 'product_license_id');
    }
}

