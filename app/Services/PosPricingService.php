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

        $product = Product::with(['variants', 'addons', 'tax'])->findOrFail($productId);

        $unitPrice = (float) $product->price;
        $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
        $variantName = null;

        if ($variantId) {
            $variant = ProductVariant::where('product_id', $productId)
                ->where('id', $variantId)
                ->where('isDeleted', 0)
                ->first();
            if ($variant) {
                $unitPrice = (float) $variant->price;
                $variantName = (string) $variant->name;
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
                ->where(function ($q) {
                    $q->where('status', 'active')->orWhereNull('status');
                })
                ->get()
                ->keyBy('id');

            // Keep client selection order; apply every explicitly chosen add-on
            foreach ($addonIds as $aid) {
                $addon = $addons->get($aid);
                if (!$addon) {
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

        $qtyTypeId = $item['quantity_type_id'] ?? $item['quantity_type123'] ?? $product->quantity_type_id;
        if ($qtyTypeId !== null && $qtyTypeId !== '') {
            $qtyTypeId = is_numeric($qtyTypeId) ? (int) $qtyTypeId : null;
        } else {
            $qtyTypeId = is_numeric($product->quantity_type_id) ? (int) $product->quantity_type_id : null;
        }

        return [
            'product' => $product,
            'variant_id' => $variantId,
            'variant_name' => $variantName,
            'addons' => $addonPayload,
            'unit_price' => round($unitPrice, 2),
            'item_discount_percent' => $itemDiscPct,
            'item_discount_amount' => $itemDiscAmt,
            'line_total' => $lineTotal,
            'category_id' => $item['categoryId'] ?? $item['category_id'] ?? $product->category_id,
            'quantity_type_id' => $qtyTypeId,
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
        $taxBreakdown = [];

        // With GST: only product.tax_id rates. No product tax → 0% (do not apply all tax master rows).
        if ($withGst && $subtotal > 0) {
            foreach ($resolvedLines as $line) {
                $product = $line['product'] ?? null;
                if (! $product) {
                    continue;
                }
                $taxId = $product->tax_id ?? null;
                if (empty($taxId)) {
                    continue;
                }

                $tax = $product->relationLoaded('tax') ? $product->tax : TaxRate::find($taxId);
                if (! $tax || (int) ($tax->isDeleted ?? 0) === 1) {
                    continue;
                }

                $lineTotal = (float) ($line['line_total'] ?? 0);
                $lineShare = round($lineTotal * (1 - ($discountPercent / 100)), 2);
                $rate = (float) $tax->tax_rate;
                $inclusive = (bool) ($product->tax_inclusive ?? false);

                if ($inclusive && $rate > 0) {
                    $base = round($lineShare / (1 + ($rate / 100)), 2);
                    $lineTax = round($lineShare - $base, 2);
                } else {
                    $lineTax = round($lineShare * ($rate / 100), 2);
                }

                if ($lineTax <= 0 && $rate <= 0) {
                    continue;
                }

                $tid = (int) $tax->id;
                if (! isset($taxBreakdown[$tid])) {
                    $taxBreakdown[$tid] = [
                        'id' => $tid,
                        'name' => (string) ($tax->tax_name ?? 'Tax'),
                        'rate' => $rate,
                        'amount' => 0.0,
                    ];
                    $taxIds[] = $tid;
                }
                $taxBreakdown[$tid]['amount'] = round($taxBreakdown[$tid]['amount'] + $lineTax, 2);
                $taxTotal += $lineTax;
            }
        }

        $grand = round($afterDiscount + $taxTotal, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discountPercent,
            'discount_amount' => $discountAmount,
            'tax_total' => round($taxTotal, 2),
            'tax_ids' => array_values(array_unique($taxIds)),
            'tax_breakdown' => array_values($taxBreakdown),
            'total' => $grand,
        ];
    }
}
