<?php

namespace App\Services;

use App\Models\Product;

class AverageCostService
{
    /**
     * Weighted average cost after a purchase receipt.
     * Updates both cost_price and purchase_price for compatibility.
     */
    public function applyPurchase(Product $product, float $buyQty, float $buyUnitCost): Product
    {
        if ($buyQty <= 0) {
            return $product;
        }

        $oldQty = max(0, (float) $product->quantity);
        // quantity on product may already include the purchase; use qty before receipt when possible
        $qtyBefore = max(0, $oldQty - $buyQty);
        $oldCost = (float) ($product->cost_price ?? $product->purchase_price ?? 0);

        $newAvg = (($qtyBefore * $oldCost) + ($buyQty * $buyUnitCost)) / max(0.0001, ($qtyBefore + $buyQty));
        $newAvg = round($newAvg, 4);

        $product->cost_price = $newAvg;
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'purchase_price')) {
            $product->purchase_price = $newAvg;
        }
        $product->save();

        return $product;
    }
}
