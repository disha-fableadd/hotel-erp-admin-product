<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\ProductRecipe;
use Illuminate\Support\Facades\DB;

class RecipeStockService
{
    /**
     * Consume recipe ingredients for a confirmed/paid order (idempotent per order item).
     */
    public function consumeForOrder(Order $order): void
    {
        if (!$this->shouldAffectStock($order)) {
            return;
        }

        $items = OrderItem::with(['product.recipe.items.ingredient'])
            ->where('order_id', $order->id)
            ->where(function ($q) {
                $q->where('isDeleted', 0)->orWhereNull('isDeleted');
            })
            ->get();

        foreach ($items as $item) {
            $this->consumeForOrderItem($order, $item);
        }
    }

    /**
     * Reverse recipe consumption for an order (idempotent per order item).
     */
    public function reverseForOrder(Order $order): void
    {
        $items = OrderItem::where('order_id', $order->id)
            ->where(function ($q) {
                $q->where('isDeleted', 0)->orWhereNull('isDeleted');
            })
            ->get();

        foreach ($items as $item) {
            $this->reverseForOrderItem($order, $item);
        }
    }

    protected function shouldAffectStock(Order $order): bool
    {
        $paymentStatus = strtolower((string) ($order->payment_status ?? ''));
        $method = strtolower((string) ($order->payment_method ?? ''));

        if (in_array($paymentStatus, ['paid', 'partial', 'partially_paid', 'completed'], true)) {
            return true;
        }

        if ($method && $method !== 'pending') {
            return true;
        }

        if ((int) ($order->table_status ?? 1) === 0 && $method !== 'pending') {
            return true;
        }

        return false;
    }

    protected function consumeForOrderItem(Order $order, OrderItem $item): void
    {
        $product = $item->product;
        if (!$product || !(int) ($product->recipe_required ?? 0)) {
            return;
        }

        $recipe = $product->recipe;
        if (!$recipe) {
            $recipe = ProductRecipe::with('items.ingredient')
                ->where('product_id', $product->id)
                ->where('isDeleted', 0)
                ->first();
        }

        if (!$recipe || $recipe->items->isEmpty()) {
            return;
        }

        $already = ProductInventory::where('reference_type', 'order_item')
            ->where('reference_id', $item->id)
            ->where('type', 'RecipeConsume')
            ->exists();

        if ($already) {
            return;
        }

        $soldQty = max(1, (float) $item->quantity);
        $yield = max(0.0001, (float) ($recipe->yield_qty ?: 1));

        DB::transaction(function () use ($order, $item, $product, $recipe, $soldQty, $yield) {
            foreach ($recipe->items as $ri) {
                if ((int) ($ri->isDeleted ?? 0) === 1) {
                    continue;
                }

                $ingredient = $ri->ingredient ?: Product::find($ri->ingredient_product_id);
                if (!$ingredient) {
                    continue;
                }

                $consumeQty = ((float) $ri->quantity / $yield) * $soldQty;
                if ($consumeQty <= 0) {
                    continue;
                }

                $before = (float) $ingredient->quantity;
                $after = max(0, $before - $consumeQty);
                $ingredient->quantity = (int) round($after);
                if ((int) $ingredient->quantity === 0) {
                    $ingredient->availablility = 'out_stock';
                }
                $ingredient->save();

                ProductInventory::create([
                    'product_id' => $ingredient->id,
                    'initial_stock' => $before,
                    'current_stock' => $after,
                    'type' => 'RecipeConsume',
                    'reference_type' => 'order_item',
                    'reference_id' => $item->id,
                    'branch_id' => $ingredient->branch_id ?: $order->branch_id,
                    'create_by' => $order->created_by,
                    'date' => now()->toDateString(),
                ]);

                try {
                    app(\App\Services\StockLedgerService::class)->record(
                        $ingredient,
                        'out',
                        $consumeQty,
                        'RecipeConsume',
                        'order_item',
                        $item->id,
                        $ingredient->branch_id ?: $order->branch_id,
                        $order->created_by
                    );
                } catch (\Throwable $e) {
                    // ledger optional if migration not run
                }
            }

            // Marker for idempotency (same reference as ingredient rows)
            ProductInventory::create([
                'product_id' => $product->id,
                'initial_stock' => $product->quantity,
                'current_stock' => $product->quantity,
                'type' => 'RecipeConsume',
                'reference_type' => 'order_item',
                'reference_id' => $item->id,
                'branch_id' => $product->branch_id ?: $order->branch_id,
                'create_by' => $order->created_by,
                'date' => now()->toDateString(),
            ]);
        });
    }

    protected function reverseForOrderItem(Order $order, OrderItem $item): void
    {
        $hasConsume = ProductInventory::where('reference_type', 'order_item')
            ->where('reference_id', $item->id)
            ->where('type', 'RecipeConsume')
            ->exists();

        if (!$hasConsume) {
            return;
        }

        $alreadyReversed = ProductInventory::where('reference_type', 'order_item')
            ->where('reference_id', $item->id)
            ->where('type', 'RecipeReverse')
            ->exists();

        if ($alreadyReversed) {
            return;
        }

        $rows = ProductInventory::where('reference_type', 'order_item')
            ->where('reference_id', $item->id)
            ->where('type', 'RecipeConsume')
            ->get();

        DB::transaction(function () use ($order, $item, $rows) {
            foreach ($rows as $row) {
                // Skip marker row on finished product (delta ~ 0)
                $delta = (float) $row->initial_stock - (float) $row->current_stock;
                if ($delta <= 0) {
                    continue;
                }

                $ingredient = Product::find($row->product_id);
                if (!$ingredient) {
                    continue;
                }

                $before = (float) $ingredient->quantity;
                $after = $before + $delta;
                $ingredient->quantity = (int) round($after);
                if ($ingredient->quantity > 0) {
                    $ingredient->availablility = 'in_stock';
                }
                $ingredient->save();

                ProductInventory::create([
                    'product_id' => $ingredient->id,
                    'initial_stock' => $before,
                    'current_stock' => $after,
                    'type' => 'RecipeReverse',
                    'reference_type' => 'order_item',
                    'reference_id' => $item->id,
                    'branch_id' => $ingredient->branch_id ?: $order->branch_id,
                    'create_by' => $order->created_by,
                    'date' => now()->toDateString(),
                ]);
                try {
                    app(\App\Services\StockLedgerService::class)->record(
                        $ingredient,
                        'in',
                        $delta,
                        'RecipeReverse',
                        'order_item',
                        $item->id,
                        $ingredient->branch_id ?: $order->branch_id,
                        $order->created_by
                    );
                } catch (\Throwable $e) {
                }
            }

            ProductInventory::create([
                'product_id' => $item->product_id,
                'initial_stock' => 0,
                'current_stock' => 0,
                'type' => 'RecipeReverse',
                'reference_type' => 'order_item',
                'reference_id' => $item->id,
                'branch_id' => $order->branch_id,
                'create_by' => $order->created_by,
                'date' => now()->toDateString(),
            ]);
        });
    }
}
