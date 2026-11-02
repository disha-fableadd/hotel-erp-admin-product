<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLedgerEntry;
use Illuminate\Support\Facades\Schema;

class StockLedgerService
{
    public function record(
        Product $product,
        string $direction,
        float $quantity,
        string $entryType,
        ?string $referenceType = null,
        $referenceId = null,
        $branchId = null,
        $createdBy = null,
        ?float $unitCost = null,
        ?string $notes = null
    ): ?StockLedgerEntry {
        if (!Schema::hasTable('stock_ledger_entries') || $quantity <= 0) {
            return null;
        }

        $direction = strtolower($direction) === 'in' ? 'in' : 'out';

        return StockLedgerEntry::create([
            'product_id' => $product->id,
            'branch_id' => $branchId ?: $product->branch_id,
            'direction' => $direction,
            'quantity' => $quantity,
            'unit_cost' => $unitCost ?? $product->cost_price ?? $product->purchase_price,
            'balance_after' => $product->quantity,
            'entry_type' => $entryType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);
    }
}
