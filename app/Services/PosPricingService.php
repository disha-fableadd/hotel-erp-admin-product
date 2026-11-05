<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductVariant;
use App\Models\TaxRate;
use Illuminate\Support\Facades\Schema;

class PosPricingService
{
    /**
     * Resolve trusted unit price and line total from DB for one cart item.
     *
     * @param  array  $item  Request item: product_id, quantity, variant_id?, addon_ids?|addons?
     * @return array{product: Product, variant_id: ?int, addons: array, unit_price: float, line_total: float, category_id: mixed, quantity_type_id: mixed}
     */
    /**
     * FK-safe category id for order_items (null when missing / deleted).
     */
    public static function safeCategoryId($categoryId): ?int
    {
        if ($categoryId === null || $categoryId === '' || !is_numeric($categoryId)) {
            return null;
        }
        $id = (int) $categoryId;
        if ($id <= 0) {
            return null;
        }

        return Category::where('id', $id)->exists() ? $id : null;
    }

    public function resolveLine(array $item): array
    {
        $productId = (int) ($item['product_id'] ?? 0);
        $qty = max(1, (int) ($item['quantity'] ?? 1));

        // Caller must only pass product_ids that exist (FK on order_items.product_id).
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

        $snap = self::taxSnapshotFromProduct($product);

        return [
            'product' => $product,
            'product_name' => (string) ($product->name ?? 'Item'),
            'variant_id' => $variantId,
            'variant_name' => $variantName,
            'addons' => $addonPayload,
            'unit_price' => round($unitPrice, 2),
            'item_discount_percent' => $itemDiscPct,
            'item_discount_amount' => $itemDiscAmt,
            'line_total' => $lineTotal,
            'category_id' => self::safeCategoryId(
                $item['categoryId'] ?? $item['category_id'] ?? $product->category_id
            ),
            'quantity_type_id' => $qtyTypeId,
            'quantity' => $qty,
            'tax_id' => $snap['tax_id'],
            'tax_name' => $snap['tax_name'],
            'tax_rate' => $snap['tax_rate'],
            'tax_inclusive' => $snap['tax_inclusive'],
        ];
    }

    /**
     * Freeze product tax fields as they were at sale time.
     *
     * @return array{tax_id: ?int, tax_name: ?string, tax_rate: ?float, tax_inclusive: bool}
     */
    public static function taxSnapshotFromProduct($product): array
    {
        if (! $product) {
            return [
                'tax_id' => null,
                'tax_name' => null,
                'tax_rate' => null,
                'tax_inclusive' => false,
            ];
        }

        if (method_exists($product, 'loadMissing')) {
            $product->loadMissing('tax');
        }

        $tax = $product->tax ?? null;
        $taxId = $product->tax_id ?? ($tax->id ?? null);

        return [
            'tax_id' => $taxId ? (int) $taxId : null,
            'tax_name' => $tax ? (string) ($tax->tax_name ?? 'GST') : null,
            'tax_rate' => $tax ? (float) ($tax->tax_rate ?? 0) : (empty($taxId) ? null : 0.0),
            'tax_inclusive' => (bool) ($product->tax_inclusive ?? false),
        ];
    }

    /**
     * Columns to persist on order_items so later product edits never change old bills.
     */
    public static function orderItemSnapshotFields(array $resolvedLine): array
    {
        $product = $resolvedLine['product'] ?? null;
        $fields = [
            'product_name' => $resolvedLine['product_name']
                ?? (is_object($product) ? (string) ($product->name ?? 'Item') : 'Item'),
            'tax_id' => $resolvedLine['tax_id'] ?? null,
            'tax_name' => $resolvedLine['tax_name'] ?? null,
            'tax_rate' => array_key_exists('tax_rate', $resolvedLine)
                ? $resolvedLine['tax_rate']
                : null,
            'tax_inclusive' => (bool) ($resolvedLine['tax_inclusive'] ?? false),
        ];

        // Drop unknown columns when migration has not run yet.
        if (! Schema::hasColumn('order_items', 'product_name')) {
            unset($fields['product_name']);
        }
        if (! Schema::hasColumn('order_items', 'tax_id')) {
            unset($fields['tax_id'], $fields['tax_name'], $fields['tax_rate'], $fields['tax_inclusive']);
        }

        return $fields;
    }

    /**
     * GST + service-tax options from shop settings (or order snapshot).
     *
     * @return array{
     *   gst_apply_mode: string,
     *   overall_gst_rate: float,
     *   overall_gst_name: string,
     *   service_tax_enabled: bool,
     *   service_tax_rate: float,
     *   service_tax_name: string
     * }
     */
    public static function gstOptionsFromSetting($setting): array
    {
        $mode = is_object($setting) ? (string) ($setting->gst_apply_mode ?? 'product') : 'product';
        $rate = is_object($setting) ? (float) ($setting->overall_gst_rate ?? 0) : 0.0;
        $name = is_object($setting) ? trim((string) ($setting->overall_gst_name ?? 'GST')) : 'GST';
        $svcEnabled = is_object($setting) ? (bool) ($setting->service_tax_enabled ?? false) : false;
        $svcRate = is_object($setting) ? (float) ($setting->service_tax_rate ?? 0) : 0.0;
        $svcName = is_object($setting) ? trim((string) ($setting->service_tax_name ?? 'Service Tax')) : 'Service Tax';

        return [
            'gst_apply_mode' => $mode === 'overall' ? 'overall' : 'product',
            'overall_gst_rate' => max(0, $rate),
            'overall_gst_name' => $name !== '' ? $name : 'GST',
            'service_tax_enabled' => $svcEnabled,
            'service_tax_rate' => max(0, $svcRate),
            'service_tax_name' => $svcName !== '' ? $svcName : 'Service Tax',
        ];
    }

    /**
     * Recalculate order money from trusted lines + discount % + optional GST + whole-bill service tax.
     *
     * @param  array<int, array>  $resolvedLines
     * @param  array{
     *   gst_apply_mode?: string,
     *   overall_gst_rate?: float,
     *   overall_gst_name?: string,
     *   service_tax_enabled?: bool,
     *   service_tax_rate?: float,
     *   service_tax_name?: string
     * }  $options
     */
    public function calculateOrderTotals(array $resolvedLines, float $discountPercent, bool $withGst, $branchId = null, array $options = []): array
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

        $mode = (($options['gst_apply_mode'] ?? 'product') === 'overall') ? 'overall' : 'product';
        $overallRate = max(0, (float) ($options['overall_gst_rate'] ?? 0));
        $overallName = trim((string) ($options['overall_gst_name'] ?? 'GST')) ?: 'GST';

        if ($withGst && $subtotal > 0 && $mode === 'overall') {
            // One rate on the whole bill (after discount)
            if ($overallRate > 0) {
                $taxTotal = round($afterDiscount * ($overallRate / 100), 2);
                $taxBreakdown[] = [
                    'id' => null,
                    'name' => $overallName,
                    'rate' => $overallRate,
                    'amount' => $taxTotal,
                ];
            }
        } elseif ($withGst && $subtotal > 0) {
            // Product-wise: prefer line snapshot (sale-time), else live product.tax_id.
            foreach ($resolvedLines as $line) {
                $snap = self::resolveLineTax($line);
                if ($snap['tax_id'] === null && $snap['tax_rate'] === null) {
                    continue;
                }

                $lineTotal = (float) ($line['line_total'] ?? 0);
                $lineShare = round($lineTotal * (1 - ($discountPercent / 100)), 2);
                $rate = (float) ($snap['tax_rate'] ?? 0);
                $inclusive = (bool) ($snap['tax_inclusive'] ?? false);

                if ($inclusive && $rate > 0) {
                    $base = round($lineShare / (1 + ($rate / 100)), 2);
                    $lineTax = round($lineShare - $base, 2);
                } else {
                    $lineTax = round($lineShare * ($rate / 100), 2);
                }

                if ($lineTax <= 0 && $rate <= 0) {
                    continue;
                }

                $tid = (int) ($snap['tax_id'] ?? 0);
                $key = $tid > 0 ? $tid : ('r'.str_replace('.', '_', (string) $rate));
                if (! isset($taxBreakdown[$key])) {
                    $taxBreakdown[$key] = [
                        'id' => $tid > 0 ? $tid : null,
                        'name' => (string) ($snap['tax_name'] ?? 'Tax'),
                        'rate' => $rate,
                        'amount' => 0.0,
                    ];
                    if ($tid > 0) {
                        $taxIds[] = $tid;
                    }
                }
                $taxBreakdown[$key]['amount'] = round($taxBreakdown[$key]['amount'] + $lineTax, 2);
                $taxTotal += $lineTax;
            }
            $taxBreakdown = array_values($taxBreakdown);
        }

        // Bill display: split combined GST into CGST + SGST (half each)
        $taxBreakdown = self::expandCgstSgstBreakdown(array_values($taxBreakdown));

        // Whole-bill service tax (settings % on amount after discount) — not split into CGST/SGST
        $svcEnabled = (bool) ($options['service_tax_enabled'] ?? false);
        $svcRate = max(0, (float) ($options['service_tax_rate'] ?? 0));
        $svcName = trim((string) ($options['service_tax_name'] ?? 'Service Tax')) ?: 'Service Tax';
        $serviceTaxAmount = 0.0;
        if ($svcEnabled && $svcRate > 0 && $afterDiscount > 0) {
            $serviceTaxAmount = round($afterDiscount * ($svcRate / 100), 2);
            $taxBreakdown[] = [
                'id' => 'service_tax',
                'name' => $svcName,
                'rate' => $svcRate,
                'amount' => $serviceTaxAmount,
                'kind' => 'service',
            ];
        }

        $grand = round($afterDiscount + $taxTotal + $serviceTaxAmount, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discountPercent,
            'discount_amount' => $discountAmount,
            'tax_total' => round($taxTotal + $serviceTaxAmount, 2),
            'gst_total' => round($taxTotal, 2),
            'service_tax_amount' => $serviceTaxAmount,
            'service_tax_rate' => ($svcEnabled && $svcRate > 0) ? $svcRate : null,
            'service_tax_name' => ($svcEnabled && $svcRate > 0) ? $svcName : null,
            'tax_ids' => array_values(array_unique($taxIds)),
            'tax_breakdown' => $taxBreakdown,
            'gst_apply_mode' => $mode,
            'overall_gst_rate' => $mode === 'overall' ? $overallRate : null,
            'total' => $grand,
        ];
    }

    /**
     * Split a combined GST row into CGST + SGST (equal halves).
     * Rows already named CGST / SGST / IGST are left as-is.
     *
     * @param  array<int, array{id?: mixed, name?: string, rate?: float, amount?: float}>  $taxBreakdown
     * @return array<int, array{id: mixed, name: string, rate: float, amount: float}>
     */
    public static function expandCgstSgstBreakdown(array $taxBreakdown): array
    {
        $expanded = [];

        foreach ($taxBreakdown as $row) {
            $name = strtoupper(trim((string) ($row['name'] ?? 'GST')));
            $amount = round((float) ($row['amount'] ?? 0), 2);
            $rate = (float) ($row['rate'] ?? 0);

            if ($amount <= 0 && $rate <= 0) {
                continue;
            }

            // Already component taxes — keep
            if (str_contains($name, 'CGST') || str_contains($name, 'SGST') || str_contains($name, 'IGST')) {
                $expanded[] = [
                    'id' => $row['id'] ?? null,
                    'name' => (string) ($row['name'] ?? $name),
                    'rate' => $rate,
                    'amount' => $amount,
                ];
                continue;
            }

            // Whole-bill service tax / service charge — never split into CGST/SGST
            $kind = strtolower((string) ($row['kind'] ?? ''));
            if ($kind === 'service'
                || str_contains($name, 'SERVICE TAX')
                || str_contains($name, 'SERVICE CHARGE')
                || ($row['id'] ?? null) === 'service_tax'
            ) {
                $expanded[] = [
                    'id' => $row['id'] ?? 'service_tax',
                    'name' => (string) ($row['name'] ?? 'Service Tax'),
                    'rate' => $rate,
                    'amount' => $amount,
                    'kind' => 'service',
                ];
                continue;
            }

            $halfRate = round($rate / 2, 4);
            $cgstAmt = round($amount / 2, 2);
            $sgstAmt = round($amount - $cgstAmt, 2);

            $expanded[] = [
                'id' => null,
                'name' => 'CGST',
                'rate' => $halfRate,
                'amount' => $cgstAmt,
            ];
            $expanded[] = [
                'id' => null,
                'name' => 'SGST',
                'rate' => $halfRate,
                'amount' => $sgstAmt,
            ];
        }

        // Merge identical name + rate lines (e.g. multiple 5% products → one CGST 2.5%)
        $merged = [];
        foreach ($expanded as $row) {
            $key = strtoupper((string) $row['name']).'|'.rtrim(rtrim(number_format((float) $row['rate'], 4, '.', ''), '0'), '.');
            if (! isset($merged[$key])) {
                $merged[$key] = $row;
            } else {
                $merged[$key]['amount'] = round((float) $merged[$key]['amount'] + (float) $row['amount'], 2);
            }
        }

        return array_values($merged);
    }

    /**
     * @param  array  $line
     * @return array{tax_id: ?int, tax_name: ?string, tax_rate: ?float, tax_inclusive: bool}
     */
    protected static function resolveLineTax(array $line): array
    {
        // Explicit snapshot on the resolved line (new sales)
        if (array_key_exists('tax_rate', $line) || ! empty($line['tax_id'])) {
            return [
                'tax_id' => ! empty($line['tax_id']) ? (int) $line['tax_id'] : null,
                'tax_name' => $line['tax_name'] ?? 'GST',
                'tax_rate' => array_key_exists('tax_rate', $line) && $line['tax_rate'] !== null
                    ? (float) $line['tax_rate']
                    : null,
                'tax_inclusive' => (bool) ($line['tax_inclusive'] ?? false),
            ];
        }

        $product = $line['product'] ?? null;
        if (! $product) {
            return [
                'tax_id' => null,
                'tax_name' => null,
                'tax_rate' => null,
                'tax_inclusive' => false,
            ];
        }

        return self::taxSnapshotFromProduct($product);
    }

    /**
     * Recalculate money from saved order items.
     * Prefers sale-time tax snapshot on order_items; does not invent rates from
     * today's product master when a snapshot column exists but is empty (old bills).
     *
     * @param  iterable  $orderItems
     * @param  array{gst_apply_mode?: string, overall_gst_rate?: float, overall_gst_name?: string}  $options
     */
    public function calculateFromOrderItems($orderItems, float $discountPercent, bool $withGst, array $options = []): array
    {
        $hasSnapCol = Schema::hasColumn('order_items', 'tax_rate');
        $resolvedLines = [];

        foreach ($orderItems as $item) {
            $line = [
                'line_total' => (float) ($item->total_amount ?? 0),
            ];

            $hasItemSnap = $hasSnapCol && (
                $item->tax_rate !== null
                || $item->tax_id !== null
                || ($item->tax_name !== null && $item->tax_name !== '')
            );

            if ($hasItemSnap) {
                $line['tax_id'] = $item->tax_id !== null ? (int) $item->tax_id : null;
                $line['tax_name'] = $item->tax_name ?: 'GST';
                $line['tax_rate'] = $item->tax_rate !== null ? (float) $item->tax_rate : 0.0;
                $line['tax_inclusive'] = (bool) ($item->tax_inclusive ?? false);
            } elseif (! $hasSnapCol) {
                // Migration not applied yet — fall back to live product (legacy behaviour).
                $product = $item->product ?? null;
                if ($product && method_exists($product, 'loadMissing')) {
                    $product->loadMissing('tax');
                }
                $line['product'] = $product;
            }
            // else: snapshot columns exist but this old row has none → do not use live product tax

            $resolvedLines[] = $line;
        }

        return $this->calculateOrderTotals($resolvedLines, $discountPercent, $withGst, null, $options);
    }

    /**
     * Override line tax snapshot when shop uses overall-bill GST.
     */
    public static function applyOverallSnapshotToLineFields(array $fields, array $gstOpts): array
    {
        if (($gstOpts['gst_apply_mode'] ?? 'product') !== 'overall') {
            return $fields;
        }
        if (array_key_exists('tax_id', $fields)) {
            $fields['tax_id'] = null;
        }
        if (array_key_exists('tax_name', $fields)) {
            $fields['tax_name'] = $gstOpts['overall_gst_name'] ?? 'GST';
        }
        if (array_key_exists('tax_rate', $fields)) {
            $fields['tax_rate'] = (float) ($gstOpts['overall_gst_rate'] ?? 0);
        }
        if (array_key_exists('tax_inclusive', $fields)) {
            $fields['tax_inclusive'] = false;
        }

        return $fields;
    }

    /**
     * Short GST label for a line item, e.g. "GST 28%" or "GST 0%".
     * Uses sale-time snapshot when present so product edits do not rewrite old bills.
     */
    public static function itemGstLabel($item, bool $withGst = true): string
    {
        if (! $withGst) {
            return '';
        }

        $hasSnapCol = Schema::hasColumn('order_items', 'tax_rate');
        $rate = null;
        $name = 'GST';

        if (is_object($item) && $hasSnapCol && (
            $item->tax_rate !== null
            || $item->tax_id !== null
            || (! empty($item->tax_name))
        )) {
            $rate = (float) ($item->tax_rate ?? 0);
            $name = trim((string) ($item->tax_name ?? 'GST')) ?: 'GST';
        } elseif (is_object($item) && ! $hasSnapCol) {
            $product = $item->product ?? null;
            if ($product) {
                if (method_exists($product, 'loadMissing')) {
                    $product->loadMissing('tax');
                }
                $tax = $product->tax ?? null;
                $rate = (float) ($tax->tax_rate ?? 0);
                $name = trim((string) ($tax->tax_name ?? 'GST')) ?: 'GST';
            } else {
                return '';
            }
        } else {
            // Snapshot columns exist but this historical line has no frozen tax → omit label
            // so live product edits cannot rewrite the printed bill.
            return '';
        }

        $rateStr = rtrim(rtrim(number_format((float) $rate, 2, '.', ''), '0'), '.');
        if ($rateStr === '') {
            $rateStr = '0';
        }

        return $name.' '.$rateStr.'%';
    }
}
