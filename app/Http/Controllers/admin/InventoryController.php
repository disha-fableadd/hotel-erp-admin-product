<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockLedgerEntry;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryController extends Controller
{
    protected function branchIdToUse()
    {
        $user = auth()->user();
        $role = $user->role ?? '';
        $subAdminId = session('selectedSubAdminId');

        if ($role === 'sub-admin') {
            return $user->id;
        }
        if ($role === 'admin' && !empty($subAdminId)) {
            return $subAdminId;
        }
        if ($role === 'staff') {
            return $user->branch_id;
        }
        return $user->id;
    }

    public function index()
    {
       $user = auth()->user();
        $userRole = $user->role ?? '';
        $userId = $user->id ?? null;
        $BranchId = $user->branch_id ?? null;
        $subAdminId = session('selectedSubAdminId');
        $brands = Brand::where('isDeleted', 0)->get();
        $categories = Category::where('isDeleted', 0)->get();
        
        return view('inventory/index', compact('categories', 'brands'));
    }
    public function View(Request $request ,$id)
    {
        return view('inventory/view');
    }

    public function stockLedger(Request $request)
    {
        $branchId = $this->branchIdToUse();
        $productId = $request->get('product_id');

        $entries = StockLedgerEntry::with('product')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $products = Product::where('isDeleted', 0)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name']);

        return view('inventory.stock_ledger', compact('entries', 'products', 'productId'));
    }
}