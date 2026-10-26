<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\OrderItem;
use App\Models\Purchases;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userId = $user->id;
        $userRole = $user->role;
        $userBranchId = $user->branch_id;

        $categoryId = $request->query('category_id');
        $subBranchId = $request->sub_branch_id ?? $userId;

        $query = Product::with(['category', 'product_inventory'])
            ->where('isDeleted', 0)
            ->orderBy('id', 'desc');

        // ✅ Branch filter
        if ($userRole === 'admin') {
            if (!empty($subBranchId)) {
                $query->where('branch_id', $subBranchId);
            }
        } elseif ($userRole === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif (in_array(strtolower($userRole), ['staff', 'hr'])) {
            $query->where('branch_id', $userBranchId);
        }

        // ✅ Category filter
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No products found.'
            ]);
        }

        $data = [];

        foreach ($products as $product) {
            // ✅ Pull stock info from product_inventory relation
            $initialStock = $product->product_inventory->initial_stock ?? 0;
            $currentStock = $product->product_inventory->current_stock ?? 0;

            $data[] = [
                'id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->SKU,
                'initial_stock' => $initialStock,
                'current_stock' => $product->quantity,
                'price' => $product->price,
                'created_at' => $product->created_at->format('Y-m-d'),
            ];
        }

        return response()->json([
            'status' => true,
            'inventory' => $data
        ]);
    }

    public function filter(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userId = $user->id;
        $userRole = $user->role;
        $userBranchId = $user->branch_id;

        $categoryId   = $request->query('category_id');
        $subBranchId  = $request->sub_branch_id ?? $userId;
        $startDate    = $request->query('start_date');
        $endDate      = $request->query('end_date');

        $query = Product::with(['category', 'product_inventory'])
            ->where('isDeleted', 0)
            ->orderBy('id', 'desc');

        // ✅ Branch filter
        if ($userRole === 'admin') {
            if (!empty($subBranchId)) {
                $query->where('branch_id', $subBranchId);
            }
        } elseif ($userRole === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif (in_array(strtolower($userRole), ['staff', 'hr'])) {
            $query->where('branch_id', $userBranchId);
        }

        // ✅ Category filter
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // ✅ Date filter
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('created_at', [
                $startDate . " 00:00:00",
                $endDate . " 23:59:59"
            ]);
        }
        
        $products = $query->get();
        // dd($products);

        if ($products->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No products found.'
            ]);
        }

        $data = [];

        foreach ($products as $product) {
            $initialStock = $product->product_inventory->initial_stock ?? 0;

            $data[] = [
                'id'            => $product->id,
                'product_name'  => $product->name,
                'sku'           => $product->SKU,
                'initial_stock' => $initialStock,
                'current_stock' => $product->quantity, // from products table
                'price'         => $product->price,
                'created_at'    => $product->created_at->format('Y-m-d'),
            ];
        }

        return response()->json([
            'status'    => true,
            'inventory' => $data
        ]);
    }
    public function products_edit_inventroy(Request $request, $id)
    {

        $product = Product::with('product_inventory')->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'product' => [
                'id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->SKU,
                'price' => $product->price,
                'initial_stock' => $product->product_inventory->initial_stock ?? 0,
                'current_stock' => $product->quantity ?? 0,
            ]
        ]);
    }
    public function inventory_update(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $userBranchId = $user->id ?? null;

        if (in_array(strtolower($user->role), ['staff', 'hr']) && $user->branch_id) {
            $userBranchId = $user->branch_id;
        } elseif (!empty($request->selectedSubAdminId)) {
            $userBranchId = $request->selectedSubAdminId;
        } else {
            $userBranchId = $user->id;
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // ✅ Calculate new stock based on add/minus
        $currentStock = (int) $product->quantity; // take from DB (latest)
        $adjustment = (int) $request->stock_quantity;

        if ($request->stock_action === 'add') {
            $newStock = $currentStock + $adjustment;
        } elseif ($request->stock_action === 'minus') {
            $newStock = max(0, $currentStock - $adjustment); // prevent negative
        } else {
            $newStock = $currentStock; // fallback
        }

        // ✅ Update product table
        $product->update([
            'price' => $request->price,
            'quantity' => $newStock,
        ]);

        // ✅ Create inventory log
        ProductInventory::create([
            'product_id' => $id,
            'initial_stock' => $request->initial_stock,
            'current_stock' => $newStock,
            'branch_id' => $userBranchId,
            'type' => $request->stock_action === 'add' ? 'Stock Added' : 'Stock Minus',
            'create_by' => $user->id,
            'date' => now(),
            'quantity' => $adjustment, // optional: track how much was added/removed
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Stock updated successfully',
            'new_stock' => $newStock
        ]);
    }
    public function getQuantityHistory_inventory($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ]);
        }

        $history = [];
        $productInventories = ProductInventory::where('product_id', $productId)->get();

        // foreach ($productInventories as $inventory) {
        //     $userId = $inventory->create_by ?? $inventory->branch_id;
        //     $user = User::find($userId);

        //     $history[] = [
        //         'type' => $inventory->type,
        //         'initial_stock' => $inventory->initial_stock,
        //         'current_stock' => $inventory->current_stock,
        //         'name' => $user ? $user->name : ($user->role ?? 'Unknown'),
        //         // ✅ Display only date (e.g. "29-Oct-2025")
        //         'date' => $inventory->date  
        //             ? \Carbon\Carbon::parse($inventory->date)->format('d-M-Y')
        //             : ($inventory->created_at ? $inventory->created_at->format('d-M-Y') : 'N/A'),
        //     ];
        // }
              foreach ($productInventories as $inventory) {

            // Get Created By Name
            $userId = $inventory->create_by ?? $inventory->branch_id;
            $user = User::find($userId);

            // --- Calculate Quantity ---
            $initial = $inventory->initial_stock;
            $current = $inventory->current_stock;

            // Default quantity (0)
            $qty = 0;

            // Calculate from initial - current if both exist
            if (!empty($initial) && !empty($current)) {
                $qty = abs($current - $initial);
            }

            // Set + or - sign based on type
            if (strtolower($inventory->type) == 'sale') {
                $signedQty = "-" . $qty;
            } elseif (strtolower($inventory->type) == 'purchase') {
                $signedQty = "+" . $qty;
            } else {
                // AUTO detect sign based on current > initial
                if ($current > $initial) {
                    $signedQty = "+" . $qty;
                } else {
                    $signedQty = "-" . $qty;
                }
            }

            // --- Push into history array ---
            $history[] = [
                'type'          => $inventory->type,
                'initial_stock' => $initial,
                'current_stock' => $current,
                'quantity'      => $signedQty,     // ✅ Direct quantity with + or - sign
                'name'          => $user ? $user->name : 'Unknown',
                'date'          => $inventory->date  
                                    ? \Carbon\Carbon::parse($inventory->date)->format('d-M-Y')
                                    : ($inventory->created_at ? $inventory->created_at->format('d-M-Y') : 'N/A'),
            ];
        }


        // ✅ Sort all history by date (latest first)
        // $history = collect($history)->sortByDesc(function ($item) {
        //     return \Carbon\Carbon::parse($item['date']);
        // })->values()->all();

        return response()->json([
            'status' => true,
            'product' => $product->name,
            'history' => $history,
        ]);
    }

}
