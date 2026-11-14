<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductRecipe;
use App\Models\ProductRecipeItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductMasterService
{
    public function restaurantFieldRules(bool $isUpdate = false): array
    {
        return [
            'product_kind' => 'nullable|in:general,prepared_food,raw_material,packaged',
            'food_type' => 'nullable|in:veg,non_veg,egg,vegan,na',
            'cost_price' => 'nullable|numeric|min:0',
            'tax_id' => 'nullable|numeric',
            'tax_inclusive' => 'nullable|in:0,1,true,false',
            'is_pos_available' => 'nullable|in:0,1,true,false',
            'track_inventory' => 'nullable|in:0,1,true,false',
            'recipe_required' => 'nullable|in:0,1,true,false',
            'kitchen_station' => 'nullable|string|max:100',
            'prep_time_minutes' => 'nullable|integer|min:0|max:9999',
            'display_order' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'variants' => 'nullable',
            'addons' => 'nullable',
            'recipe_items' => 'nullable',
            'recipe_yield_qty' => 'nullable|numeric|min:0.0001',
            'recipe_notes' => 'nullable|string',
        ];
    }

    public function boolFromRequest($value, $default = false): bool
    {
        if ($value === null || $value === '') {
            return (bool) $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
    }

    public function extractRestaurantAttributes(Request $request): array
    {
        $kind = $request->input('product_kind', 'general') ?: 'general';
        // Prepared food is recipe-driven — never treat finished-good qty as a hard stock limit.
        $trackInventory = $kind === 'prepared_food'
            ? false
            : $this->boolFromRequest($request->input('track_inventory'), true);

        return [
            'product_kind' => $kind,
            'food_type' => $request->input('food_type', 'na') ?: 'na',
            'cost_price' => $request->filled('cost_price') ? $request->cost_price : null,
            'tax_id' => $request->filled('tax_id') ? $request->tax_id : null,
            'tax_inclusive' => $this->boolFromRequest($request->input('tax_inclusive'), false),
            'is_pos_available' => $this->boolFromRequest($request->input('is_pos_available'), true),
            'track_inventory' => $trackInventory,
            'recipe_required' => $this->boolFromRequest($request->input('recipe_required'), false),
            'kitchen_station' => $request->input('kitchen_station'),
            'prep_time_minutes' => $request->filled('prep_time_minutes') ? (int) $request->prep_time_minutes : null,
            'display_order' => (int) ($request->input('display_order', 0) ?: 0),
            'min_stock' => $request->filled('min_stock') ? $request->min_stock : null,
        ];
    }

    public function syncRelations(Product $product, Request $request): void
    {
        DB::transaction(function () use ($product, $request) {
            $this->syncVariants($product, $request);
            $this->syncAddons($product, $request);
            $this->syncRecipe($product, $request);
        });
    }

    protected function decodeList($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    protected function syncVariants(Product $product, Request $request): void
    {
        if (!$request->has('variants') && !$request->has('variants_json')) {
            return;
        }

        $rows = $this->decodeList($request->input('variants', $request->input('variants_json')));
        $keepIds = [];

        foreach ($rows as $index => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $payload = [
                'product_id' => $product->id,
                'name' => $name,
                'sku' => $row['sku'] ?? null,
                'price' => (float) ($row['price'] ?? 0),
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'isDeleted' => 0,
            ];

            if (!empty($row['id'])) {
                $variant = ProductVariant::where('product_id', $product->id)->where('id', $row['id'])->first();
                if ($variant) {
                    $variant->update($payload);
                    $keepIds[] = $variant->id;
                    continue;
                }
            }

            $variant = ProductVariant::create($payload);
            $keepIds[] = $variant->id;
        }

        if (!count($keepIds)) {
            ProductVariant::where('product_id', $product->id)->update(['isDeleted' => 1]);
        } else {
            ProductVariant::where('product_id', $product->id)->whereNotIn('id', $keepIds)->update(['isDeleted' => 1]);
        }
    }

    protected function syncAddons(Product $product, Request $request): void
    {
        if (!$request->has('addons') && !$request->has('addons_json')) {
            return;
        }

        $rows = $this->decodeList($request->input('addons', $request->input('addons_json')));
        $addonIds = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            if (!empty($row['id'])) {
                $addon = ProductAddon::find($row['id']);
                if ($addon) {
                    $addon->update([
                        'name' => $name,
                        'price' => (float) ($row['price'] ?? 0),
                        'status' => 'active',
                        'isDeleted' => 0,
                        'branch_id' => $addon->branch_id ?: $product->branch_id,
                    ]);
                    $addonIds[] = $addon->id;
                    continue;
                }
            }

            $addon = ProductAddon::create([
                'name' => $name,
                'price' => (float) ($row['price'] ?? 0),
                'branch_id' => $product->branch_id,
                'status' => 'active',
                'isDeleted' => 0,
            ]);
            $addonIds[] = $addon->id;
        }

        $product->addons()->sync($addonIds);
    }

    protected function syncRecipe(Product $product, Request $request): void
    {
        $recipeRequired = $this->boolFromRequest($request->input('recipe_required'), (bool) $product->recipe_required);
        $items = $this->decodeList($request->input('recipe_items', $request->input('recipe_items_json')));

        if (!$recipeRequired && !$request->has('recipe_items') && !$request->has('recipe_items_json')) {
            return;
        }

        if (!$recipeRequired || empty($items)) {
            $existing = ProductRecipe::where('product_id', $product->id)->first();
            if ($existing) {
                $existing->update(['isDeleted' => 1]);
                ProductRecipeItem::where('recipe_id', $existing->id)->update(['isDeleted' => 1]);
            }
            return;
        }

        $recipe = ProductRecipe::firstOrNew(['product_id' => $product->id]);
        $recipe->fill([
            'yield_qty' => $request->input('recipe_yield_qty', $recipe->yield_qty ?: 1),
            'notes' => $request->input('recipe_notes'),
            'isDeleted' => 0,
        ]);
        $recipe->save();

        $keepIds = [];
        foreach ($items as $row) {
            $ingredientId = (int) ($row['ingredient_product_id'] ?? $row['product_id'] ?? 0);
            $qty = (float) ($row['quantity'] ?? 0);
            if ($ingredientId <= 0 || $qty <= 0 || $ingredientId === (int) $product->id) {
                continue;
            }

            $payload = [
                'recipe_id' => $recipe->id,
                'ingredient_product_id' => $ingredientId,
                'quantity' => $qty,
                'quantity_type_id' => $row['quantity_type_id'] ?? null,
                'isDeleted' => 0,
            ];

            if (!empty($row['id'])) {
                $item = ProductRecipeItem::where('recipe_id', $recipe->id)->where('id', $row['id'])->first();
                if ($item) {
                    $item->update($payload);
                    $keepIds[] = $item->id;
                    continue;
                }
            }

            $item = ProductRecipeItem::create($payload);
            $keepIds[] = $item->id;
        }

        ProductRecipeItem::where('recipe_id', $recipe->id)
            ->when(count($keepIds), fn ($q) => $q->whereNotIn('id', $keepIds))
            ->update(['isDeleted' => 1]);
    }

    public function detailPayload(Product $product): array
    {
        $product->load([
            'variants',
            'addons',
            'recipe.items.ingredient',
            'recipe.items.quantityType',
            'tax',
            'category',
            'brand',
            'quantityType',
        ]);

        return [
            'product' => $product,
            'variants' => $product->variants,
            'addons' => $product->addons,
            'recipe' => $product->recipe,
        ];
    }

    /**
     * Normalize Desktop / Web product_kind to DB enum values.
     */
    public function normalizeProductKind(?string $raw, ?string $fallback = 'general'): string
    {
        $raw = strtolower(trim((string) $raw));
        $raw = str_replace(['-', ' '], '_', $raw);

        return match ($raw) {
            'prepared_food', 'preparedfood', 'food' => 'prepared_food',
            'packaged', 'packaged_product', 'packagedproduct' => 'packaged',
            'raw_material', 'rawmaterial', 'raw' => 'raw_material',
            'general', 'inventory', 'general_inventory' => 'general',
            default => in_array((string) $fallback, ['prepared_food', 'packaged', 'raw_material', 'general'], true)
                ? (string) $fallback
                : 'general',
        };
    }

    public function normalizeFoodType(?string $raw, ?string $fallback = 'na'): string
    {
        $raw = strtolower(trim((string) $raw));
        $raw = str_replace(['-', ' '], '_', $raw);

        return match ($raw) {
            'veg', 'vegetarian' => 'veg',
            'non_veg', 'nonveg', 'non-veg' => 'non_veg',
            'egg' => 'egg',
            'vegan' => 'vegan',
            'na', 'n_a', 'not_applicable', '' => 'na',
            default => in_array((string) $fallback, ['veg', 'non_veg', 'egg', 'vegan', 'na'], true)
                ? (string) $fallback
                : 'na',
        };
    }

    /**
     * Apply restaurant fields + nested variants/addons from Flutter sync push payload.
     *
     * @param  array<string, mixed>  $data
     */
    public function applyDesktopProductPayload(Product $product, array $data): void
    {
        $kindRaw = $data['product_kind'] ?? $data['product_type'] ?? null;
        $hasVariantRows = ! empty($data['variants']) && is_array($data['variants']);
        $kind = $this->normalizeProductKind(
            is_string($kindRaw) ? $kindRaw : null,
            $product->product_kind ?? 'general'
        );

        // Desktop may send variants while forgetting product_kind — keep Variants UI open on Web/other PCs
        if ($hasVariantRows && $kind === 'general') {
            $kind = 'prepared_food';
        }

        $foodType = $this->normalizeFoodType(
            isset($data['food_type']) ? (string) $data['food_type'] : null,
            $product->food_type ?? 'na'
        );

        $product->product_kind = $kind;
        $product->food_type = $foodType;

        if (array_key_exists('kitchen_station', $data)) {
            $station = trim((string) ($data['kitchen_station'] ?? ''));
            $product->kitchen_station = $station !== '' ? $station : null;
        }
        if (array_key_exists('prep_time_minutes', $data)) {
            $product->prep_time_minutes = $data['prep_time_minutes'] !== null && $data['prep_time_minutes'] !== ''
                ? (int) $data['prep_time_minutes']
                : null;
        }
        if (array_key_exists('display_order', $data)) {
            $product->display_order = (int) ($data['display_order'] ?? 0);
        }
        if (array_key_exists('recipe_required', $data)) {
            $product->recipe_required = $this->boolFromRequest($data['recipe_required'], false);
        }
        if (array_key_exists('is_pos_available', $data)) {
            $product->is_pos_available = $this->boolFromRequest($data['is_pos_available'], true);
        }
        if (array_key_exists('tax_inclusive', $data)) {
            $product->tax_inclusive = $this->boolFromRequest($data['tax_inclusive'], false);
        }
        if (array_key_exists('tax_id', $data) && $data['tax_id'] !== '' && $data['tax_id'] !== null) {
            $product->tax_id = (int) $data['tax_id'];
        }
        if (array_key_exists('track_inventory', $data)) {
            $product->track_inventory = $kind === 'prepared_food'
                ? false
                : $this->boolFromRequest($data['track_inventory'], true);
        } elseif ($kind === 'prepared_food') {
            $product->track_inventory = false;
        }

        $product->save();

        $variantRows = [];
        $addonRows = [];

        if ($hasVariantRows) {
            foreach ($data['variants'] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $color = strtolower(trim((string) ($row['color'] ?? $row['type'] ?? 'variant')));
                $name = trim((string) ($row['name'] ?? $row['size'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $normalized = [
                    'id' => $row['remote_id'] ?? $row['id'] ?? null,
                    'client_uuid' => $row['client_uuid'] ?? null,
                    'name' => $name,
                    'sku' => $row['sku'] ?? $row['barcode'] ?? null,
                    'price' => $row['price'] ?? $row['salePrice'] ?? $row['sale_price'] ?? 0,
                    'sort_order' => $row['sort_order'] ?? null,
                ];
                if (in_array($color, ['addon', 'add_on', 'add-on', 'extra'], true)) {
                    $addonRows[] = $normalized;
                } else {
                    $variantRows[] = $normalized;
                }
            }
        }

        if (! empty($data['addons']) && is_array($data['addons'])) {
            foreach ($data['addons'] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $name = trim((string) ($row['name'] ?? $row['size'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $addonRows[] = [
                    'id' => $row['remote_id'] ?? $row['id'] ?? null,
                    'client_uuid' => $row['client_uuid'] ?? null,
                    'name' => $name,
                    'price' => $row['price'] ?? 0,
                ];
            }
        }

        // Only touch relations when Desktop explicitly sent variants/addons keys
        if (array_key_exists('variants', $data) || array_key_exists('addons', $data)) {
            $this->syncVariantRows($product, $variantRows, array_key_exists('variants', $data));
            $this->syncAddonRows($product, $addonRows, array_key_exists('variants', $data) || array_key_exists('addons', $data));
        }

        if (! empty($data['recipe_items']) && is_array($data['recipe_items'])) {
            $req = new Request([
                'recipe_required' => $data['recipe_required'] ?? true,
                'recipe_items' => $data['recipe_items'],
                'recipe_yield_qty' => $data['recipe_yield_qty'] ?? 1,
                'recipe_notes' => $data['recipe_notes'] ?? null,
            ]);
            $this->syncRecipe($product, $req);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function syncVariantRows(Product $product, array $rows, bool $replaceMissing): void
    {
        $keepIds = [];
        foreach ($rows as $index => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $variant = null;
            if (! empty($row['id']) && is_numeric($row['id'])) {
                $variant = ProductVariant::where('product_id', $product->id)->where('id', (int) $row['id'])->first();
            }
            if (! $variant && ! empty($row['client_uuid']) && \Illuminate\Support\Facades\Schema::hasColumn('product_variants', 'client_uuid')) {
                $variant = ProductVariant::where('client_uuid', $row['client_uuid'])->first();
                if ($variant && (int) $variant->product_id !== (int) $product->id) {
                    $variant = null;
                }
            }
            if (! $variant) {
                $variant = ProductVariant::where('product_id', $product->id)
                    ->where('name', $name)
                    ->where('isDeleted', 0)
                    ->first();
            }

            $payload = [
                'product_id' => $product->id,
                'name' => $name,
                'sku' => $row['sku'] ?? null,
                'price' => (float) ($row['price'] ?? 0),
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'isDeleted' => 0,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('product_variants', 'client_uuid') && ! empty($row['client_uuid'])) {
                $payload['client_uuid'] = $row['client_uuid'];
            }

            if ($variant) {
                $variant->update($payload);
            } else {
                $variant = ProductVariant::create($payload);
            }
            $keepIds[] = $variant->id;
        }

        if ($replaceMissing) {
            if (! count($keepIds)) {
                ProductVariant::where('product_id', $product->id)->update(['isDeleted' => 1]);
            } else {
                ProductVariant::where('product_id', $product->id)->whereNotIn('id', $keepIds)->update(['isDeleted' => 1]);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function syncAddonRows(Product $product, array $rows, bool $replaceMissing): void
    {
        $addonIds = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $addon = null;
            if (! empty($row['id']) && is_numeric($row['id'])) {
                $addon = ProductAddon::find((int) $row['id']);
            }
            if (! $addon) {
                $addon = ProductAddon::where('name', $name)
                    ->where('branch_id', $product->branch_id)
                    ->where('isDeleted', 0)
                    ->first();
            }

            if ($addon) {
                $addon->update([
                    'name' => $name,
                    'price' => (float) ($row['price'] ?? 0),
                    'status' => 'active',
                    'isDeleted' => 0,
                    'branch_id' => $addon->branch_id ?: $product->branch_id,
                ]);
            } else {
                $addon = ProductAddon::create([
                    'name' => $name,
                    'price' => (float) ($row['price'] ?? 0),
                    'branch_id' => $product->branch_id,
                    'status' => 'active',
                    'isDeleted' => 0,
                ]);
            }
            $addonIds[] = $addon->id;
        }

        if ($replaceMissing) {
            $product->addons()->sync($addonIds);
        } elseif (count($addonIds)) {
            $product->addons()->syncWithoutDetaching($addonIds);
        }
    }

    /**
     * Serialize variants/addons for Flutter pull (includes size/color aliases).
     *
     * @return array{variants: list<array<string,mixed>>, addons: list<array<string,mixed>>, variants_with_addons: list<array<string,mixed>>}
     */
    public function serializeVariantsForSync(Product $product): array
    {
        $product->loadMissing(['variants', 'addons']);

        $variants = ($product->variants ?? collect())->map(function ($v) {
            return [
                'id' => (int) $v->id,
                'remote_id' => (int) $v->id,
                'client_uuid' => $v->client_uuid ?? null,
                'product_id' => (int) $v->product_id,
                'name' => (string) $v->name,
                'size' => (string) $v->name, // Flutter alias
                'color' => 'Variant',
                'sku' => $v->sku,
                'barcode' => $v->sku,
                'price' => (float) $v->price,
                'purchase_price' => 0,
                'qty' => 0,
                'quantity' => 0,
                'sort_order' => (int) ($v->sort_order ?? 0),
            ];
        })->values()->all();

        $addons = ($product->addons ?? collect())->map(function ($a) {
            return [
                'id' => (int) $a->id,
                'remote_id' => (int) $a->id,
                'name' => (string) $a->name,
                'size' => (string) $a->name,
                'color' => 'Addon',
                'price' => (float) $a->price,
                'barcode' => null,
                'qty' => 0,
            ];
        })->values()->all();

        return [
            'variants' => $variants,
            'addons' => $addons,
            // Combined list for Desktop clients that store addons inside variants[]
            'variants_with_addons' => array_values(array_merge($variants, $addons)),
        ];
    }
}
