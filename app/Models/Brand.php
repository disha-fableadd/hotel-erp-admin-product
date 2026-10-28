<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'logo',
        'branch_id',
        'isDeleted',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['logo_url'];

     // Accessor for full logo URL
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return tenant_image_url($this->logo);
        }

        // fallback logo image
        return image_path('admin/assets/img/product/noimage.png');
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
