<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Category extends Model
{
    use HasFactory;
    public const KOT_TARGET_KITCHEN = 'kitchen';
    public const KOT_TARGET_BILLING = 'billing';

    protected $fillable = [
        'name',
        'gst_applicable',
        'item_type',
        'kot_print_target',
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

    protected $appends = ['image_url', 'is_liquor'];

    /**
     * food | beverage | liquor | tobacco | general — always present for Flutter sync.
     */
    public function resolveItemType(): string
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'item_type')) {
            $raw = strtolower(trim((string) ($this->attributes['item_type'] ?? $this->item_type ?? '')));
            if (in_array($raw, ['food', 'beverage', 'liquor', 'tobacco', 'general', 'bar'], true)) {
                return $raw === 'bar' ? 'liquor' : $raw;
            }
        }

        // Legacy: Apply GST OFF → liquor share on Web KPI cards
        if (! $this->appliesGst()) {
            return 'liquor';
        }

        return 'food';
    }

    public function getItemTypeAttribute($value): string
    {
        if ($value !== null && $value !== '') {
            $raw = strtolower(trim((string) $value));
            if (in_array($raw, ['food', 'beverage', 'liquor', 'tobacco', 'general', 'bar'], true)) {
                return $raw === 'bar' ? 'liquor' : $raw;
            }
        }

        return $this->resolveItemType();
    }

    public function getIsLiquorAttribute(): int
    {
        return in_array($this->resolveItemType(), ['liquor', 'beverage', 'tobacco', 'bar'], true) ? 1 : 0;
    }

    /** Whether products in this category should receive GST when billing With GST. */
    public function appliesGst(): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'gst_applicable')) {
            return true;
        }

        return $this->gst_applicable !== false;
    }

    /**
     * Where KOT for this category should print.
     * kitchen (default) = kitchen printer; billing = same printer as bill/counter.
     */
    public function kotPrintTarget(): string
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'kot_print_target')) {
            return self::KOT_TARGET_KITCHEN;
        }

        $raw = strtolower(trim((string) ($this->kot_print_target ?? self::KOT_TARGET_KITCHEN)));
        if (in_array($raw, ['billing', 'bill', 'counter', 'billing_counter'], true)) {
            return self::KOT_TARGET_BILLING;
        }

        return self::KOT_TARGET_KITCHEN;
    }

    public function printsKotOnBillingPrinter(): bool
    {
        return $this->kotPrintTarget() === self::KOT_TARGET_BILLING;
    }

    /**
     * Resolve print target for a product (via its category). Default = kitchen.
     */
    public static function resolveKotPrintTarget($productOrCategoryId = null): string
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasColumn('categories', 'kot_print_target')) {
                return self::KOT_TARGET_KITCHEN;
            }

            $category = null;
            if ($productOrCategoryId instanceof self) {
                $category = $productOrCategoryId;
            } elseif (is_object($productOrCategoryId) && isset($productOrCategoryId->category)) {
                $category = $productOrCategoryId->category;
                if (! $category && ! empty($productOrCategoryId->category_id)) {
                    $category = static::find($productOrCategoryId->category_id);
                }
            } elseif (is_numeric($productOrCategoryId)) {
                $category = static::find((int) $productOrCategoryId);
            }

            if ($category instanceof self) {
                return $category->kotPrintTarget();
            }
        } catch (\Throwable $e) {
            // fall through to default
        }

        return self::KOT_TARGET_KITCHEN;
    }

    /** Station label used on KOT board / PDF for billing-target items. */
    public static function billingKotStationLabel(): string
    {
        return 'Counter';
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
