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
}
