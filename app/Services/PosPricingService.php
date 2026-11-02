<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductVariant;
use App\Models\TaxRate;

class PosPricingService
{
    /**
     * Resolve trusted unit price and line total from DB for one cart item.
     *
     * @param  array  $item  Request item: product_id, quantity, variant_id?, addon_ids?|addons?
     * @return array{product: Product, variant_id: ?int, addons: array, unit_price: float, line_total: float, category_id: mixed, quantity_type_id: mixed}
     */
    public function resolveLine(array $item): array
    {
        $productId = (int) ($item['product_id'] ?? 0);
        $qty = max(1, (int) ($item['quantity'] ?? 1));

        $product = Product::with(['variants', 'addons'])->findOrFail($productId);

        $unitPrice = (float) $product->price;
        $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;

        if ($variantId) {
            $variant = ProductVariant::where('product_id', $productId)
                ->where('id', $variantId)
                ->where('isDeleted', 0)
                ->first();
            if ($variant) {
                $unitPrice = (float) $variant->price;
            } else {
                $variantId = null;
            }
        }

        $addonPayload = [];
        $addonIds = $item['addon_ids'] ?? [];
        if (!empty($item['addons']) && is_array($item['addons'])) {
            // May be list of ids or list of objects
            foreach ($item['addons'] as $a) {
                if (is_array($a) && isset($a['id'])) {
                    $addonIds[] = $a['id'];
                } elseif (is_numeric($a)) {
                    $addonIds[] = $a;
                }
            }
        }
        $addonIds = array_values(array_unique(array_filter(array_map('intval', (array) $addonIds))));

        if ($addonIds) {
            $addons = ProductAddon::whereIn('id', $addonIds)
                ->where('isDeleted', 0)
                ->where('status', 'active')
                ->get();

            // Prefer product-linked addons when pivot exists; still allow matched catalog ids
            $linkedIds = $product->addons->pluck('id')->all();
            foreach ($addons as $addon) {
                if ($linkedIds && !in_array($addon->id, $linkedIds, true)) {
                    continue;
                }
                $unitPrice += (float) $addon->price;
                $addonPayload[] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => (float) $addon->price,
                    'qty' => 1,
                ];
            }
        }

        $gross = round($unitPrice * $qty, 2);
        $itemDiscPct = max(0, min(100, (float) ($item['item_discount_percent'] ?? 0)));
        $itemDiscAmt = round($gross * ($itemDiscPct / 100), 2);
        if (isset($item['item_discount_amount']) && (float) $item['item_discount_amount'] > 0 && $itemDiscPct <= 0) {
            $itemDiscAmt = min($gross, round((float) $item['item_discount_amount'], 2));
            $itemDiscPct = $gross > 0 ? round(($itemDiscAmt / $gross) * 100, 2) : 0;
        }
        $lineTotal = round(max(0, $gross - $itemDiscAmt), 2);

        return [
            'product' => $product,
            'variant_id' => $variantId,
            'addons' => $addonPayload,
            'unit_price' => round($unitPrice, 2),
            'item_discount_percent' => $itemDiscPct,
            'item_discount_amount' => $itemDiscAmt,
            'line_total' => $lineTotal,
            'category_id' => $item['categoryId'] ?? $item['category_id'] ?? $product->category_id,
            'quantity_type_id' => $item['quantity_type_id'] ?? $product->quantity_type_id,
            'quantity' => $qty,
        ];
    }

    /**
     * Recalculate order money from trusted lines + discount % + optional GST.
     *
     * @param  array<int, array>  $resolvedLines
     */
    public function calculateOrderTotals(array $resolvedLines, float $discountPercent, bool $withGst, $branchId = null): array
    {
        $subtotal = 0.0;
        foreach ($resolvedLines as $line) {
            $subtotal += (float) $line['line_total'];
        }

        $discountPercent = max(0, min(100, $discountPercent));
        $discountAmount = round($subtotal * ($discountPercent / 100), 2);
        $afterDiscount = round($subtotal - $discountAmount, 2);

        $taxTotal = 0.0;
        $taxIds = [];
        if ($withGst) {
            $taxes = TaxRate::where('isDeleted', 0)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->get();
            foreach ($taxes as $tax) {
                $taxTotal += round($afterDiscount * ((float) $tax->tax_rate / 100), 2);
                $taxIds[] = $tax->id;
            }
        }

        $grand = round($afterDiscount + $taxTotal, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discountPercent,
            'discount_amount' => $discountAmount,
            'tax_total' => round($taxTotal, 2),
            'tax_ids' => $taxIds,
            'total' => $grand,
        ];
    }
}
