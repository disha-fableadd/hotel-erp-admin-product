<?php

namespace App\Services\Purchase;

use App\Models\AiPurchaseBill;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\PurchaseInvoice;
use App\Models\Purchases;
use App\Models\QuantityType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveAiPurchaseBill
{
    public function __construct(private ProductMatcher $matcher) {}

    public function save(array $data, int $branchId, int $userId): PurchaseInvoice
    {
        return DB::transaction(function () use ($data, $branchId, $userId) {
            // Serialize catalog resolution for this branch, including new names
            // that do not yet have a product row to lock.
            User::whereKey($branchId)->lockForUpdate()->firstOrFail();
            $draft = AiPurchaseBill::where('draft_token', $data['draft_token'])
                ->where('branch_id', $branchId)->where('created_by', $userId)
                ->lockForUpdate()->firstOrFail();
            if ($draft->purchase_invoice_id) {
                return PurchaseInvoice::findOrFail($draft->purchase_invoice_id);
            }
            $header = $data['header'];
            if (in_array($header['document_type'] ?? '', ['credit_note', 'other'], true)) {
                throw ValidationException::withMessages(['header.document_type' => 'Review this document type. Credit notes must use the purchase return workflow.']);
            }
            $vendor = $this->vendor($header, $branchId, $userId);
            if (PurchaseInvoice::where('branch_id', $branchId)->where('vendor_id', $vendor->id)
                ->where('isDeleted', 0)->whereRaw('LOWER(TRIM(bill_no)) = ?', [mb_strtolower(trim($header['bill_no']))])->exists()) {
                throw ValidationException::withMessages(['header.bill_no' => 'This vendor bill number is already saved. Stock was not changed.']);
            }

            $lines = [];
            foreach ($data['items'] as $index => $item) {
                $catalog = Product::with('quantityType')->where('branch_id', $branchId)
                    ->where('isDeleted', 0)->lockForUpdate()->get();
                $candidates = $catalog->map(fn ($p) => [
                    'id' => $p->id, 'name' => $p->name, 'unit' => $p->quantityType?->quantity_type ?? '',
                ]);
                if (! empty($item['product_id'])) {
                    $product = $catalog->firstWhere('id', $item['product_id']);
                    if (! $product) {
                        throw ValidationException::withMessages(["items.$index.product_id" => 'Choose a product belonging to the current branch.']);
                    }
                    $existingUnit = $product->quantityType?->quantity_type;
                    if ($existingUnit && $this->matcher->unit($existingUnit) !== $this->matcher->unit($item['unit'])) {
                        throw ValidationException::withMessages(["items.$index.unit" => "Convert the quantity to $existingUnit before adding stock to {$product->name}."]);
                    }
                } else {
                    $matched = $this->matcher->match($item['name'], $item['unit'], $candidates);
                    $product = $matched ? $catalog->firstWhere('id', $matched['id']) : null;
                    $sameName = $candidates->filter(fn ($p) => $this->matcher->normalize($p['name']) === $this->matcher->normalize($item['name']));
                    if (! $product && $sameName->isNotEmpty()) {
                        throw ValidationException::withMessages(["items.$index.product_id" => 'This product name already exists. Select the correct product and convert its unit if needed.']);
                    }
                }

                $unitName = $this->matcher->unit($item['unit']);
                $unit = QuantityType::firstOrCreate([
                    'branch_id' => $branchId, 'quantity_type' => $unitName, 'isDeleted' => 0,
                ], ['created_by' => $userId]);
                $stockBefore = (float) ($product?->quantity ?? 0);
                $trackStock = (bool) $item['track_stock'];
                if (! $product) {
                    $category = $this->category($item, $branchId);
                    $product = new Product;
                    $product->forceFill([
                        'name' => trim($item['name']), 'category_id' => $category->id,
                        'branch_id' => $branchId, 'vendor_id' => $vendor->id,
                        'price' => $item['rate'], 'quantity' => 0,
                        'SKU' => 'AI-'.Str::upper(Str::random(12)),
                        'status' => 'active', 'isDeleted' => 0,
                    ]);
                }
                $product->quantity_type_id ??= $unit->id;
                $product->purchase_price = $item['quantity'] > 0 ? round($item['amount'] / $item['quantity'], 4) : $item['rate'];
                if (! $product->hsn_code && ! empty($item['hsn'])) {
                    $product->hsn_code = $item['hsn'];
                }
                if ($trackStock) {
                    $product->quantity = round($stockBefore + $item['quantity'], 4);
                    $product->availablility = $product->quantity > 0 ? 'in_stock' : 'out_stock';
                } elseif (! $product->exists) {
                    $product->availablility = 'out_stock';
                }
                $product->save();
                $lines[] = array_merge($item, [
                    'product_id' => $product->id, 'price' => $product->purchase_price,
                    'quantity_type_id' => $product->quantity_type_id,
                    'total' => $item['amount'], 'stock_before' => $stockBefore,
                    'stock_after' => (float) $product->quantity,
                ]);
            }

            $taxes = $header['extra_fields']['taxes'] ?? [];
            $invoice = PurchaseInvoice::create([
                'invoice_number' => 'AI-'.Str::upper(Str::random(16)),
                'bill_no' => trim($header['bill_no']), 'vendor_id' => $vendor->id,
                'branch_id' => $branchId, 'created_by' => $userId,
                'products' => json_encode($lines, JSON_UNESCAPED_UNICODE),
                'total_amount' => round(array_sum(array_column($lines, 'total')), 2),
                'discount' => $header['extra_fields']['discount_total'] ?? 0,
                'shipping' => $header['extra_fields']['shipping_total'] ?? 0,
                'grand_total' => $header['grand_total'],
                'paid' => 0, 'remaining_amount' => $header['grand_total'],
                'status' => $data['status'],
                'gst_option' => count($taxes) ? 'with_gst' : 'without_gst',
                'taxes' => json_encode($taxes, JSON_UNESCAPED_UNICODE),
            ]);
            foreach ($lines as $line) {
                Purchases::create([
                    'invoice_id' => $invoice->id, 'item' => $line['product_id'],
                    'quantity' => $line['quantity'], 'quantity_type_id' => $line['quantity_type_id'],
                    'price' => $line['price'], 'amount_total' => $line['total'],
                    'vendor_id' => $vendor->id, 'purchase_status' => $data['status'],
                    'payment_status' => 'unpaid', 'branch_id' => $branchId, 'created_by' => $userId,
                ]);
                if ($line['track_stock']) {
                    ProductInventory::create([
                        'product_id' => $line['product_id'], 'initial_stock' => $line['stock_before'],
                        'current_stock' => $line['stock_after'], 'branch_id' => $branchId,
                        'create_by' => $userId, 'date' => $header['bill_date'], 'type' => 'purchase',
                    ]);
                }
            }
            $data['items'] = $lines;
            $draft->update(['reviewed_data' => $data, 'purchase_invoice_id' => $invoice->id]);
            return $invoice;
        }, 3);
    }

    private function category(array $item, int $branchId): Category
    {
        if (! empty($item['category_id'])) {
            $category = Category::where('branch_id', $branchId)->where('isDeleted', 0)->find($item['category_id']);
            if (! $category) {
                throw ValidationException::withMessages(['items' => 'Choose a category from this branch.']);
            }
            return $category;
        }
        return Category::firstOrCreate([
            'branch_id' => $branchId, 'name' => trim($item['category'] ?? '') ?: 'Other', 'isDeleted' => 0,
        ]);
    }

    private function vendor(array $header, int $branchId, int $userId): User
    {
        $vendors = User::where('role', 'vendor')->where('branch_id', $branchId)->where('isDeleted', 0)->get();
        if (! empty($header['vendor_id'])) {
            $vendor = $vendors->firstWhere('id', $header['vendor_id']);
            if (! $vendor) {
                throw ValidationException::withMessages(['header.vendor_id' => 'Choose a vendor from this branch.']);
            }
            return $vendor;
        }
        $vendor = $vendors->first(fn ($v) => $this->matcher->normalize($v->name) === $this->matcher->normalize($header['vendor_name']));
        return $vendor ?? User::create([
            'name' => trim($header['vendor_name']), 'phone' => $header['vendor_phone'] ?? null,
            'role' => 'vendor', 'status' => 1, 'branch_id' => $branchId, 'created_by' => $userId,
        ]);
    }
}
