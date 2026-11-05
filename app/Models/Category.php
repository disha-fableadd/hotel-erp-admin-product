<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'gst_applicable',
        'image',
        'branch_id',
        'isDeleted',
        'client_uuid',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'gst_applicable' => 'boolean',
        'isDeleted' => 'integer',
    ];

    protected $appends = ['image_url'];

    /** Whether products in this category should receive GST when billing With GST. */
    public function appliesGst(): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'gst_applicable')) {
            return true;
        }

        return $this->gst_applicable !== false;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return tenant_image_url($this->image);
        }

        // fallback category image
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
