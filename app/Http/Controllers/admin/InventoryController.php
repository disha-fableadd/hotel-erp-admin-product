<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryController extends Controller
{
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
}