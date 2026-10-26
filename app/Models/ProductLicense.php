<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductLicense extends Model
{
    protected $fillable = [
        'purchase_code',
        'purchase_code_hash',
        'buyer',
        'license_type',
        'max_domains',
        'status',
        'envato_item_id',
        'notes',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(ProductLicenseDomain::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

