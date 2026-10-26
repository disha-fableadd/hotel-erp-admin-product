<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function product_list(Request $request)
    {
        $user       = auth()->user();
        $userRole   = $user->role ?? '';
        $userId     = $user->id ?? null;
        $BranchId   = $user->branch_id ?? null;
        $subAdminId = session('selectedSubAdminId');
        $categories = Category::where('isDeleted', 0)->get();
        $brands     = Brand::where('isDeleted', 0)->get();

        return view('product/productlist', compact('categories', 'brands'));
    }

    public function add_product(Request $request)
    {
        $user       = auth()->user();
        $userRole   = $user->role ?? '';
        $userId     = $user->id ?? null;
        $BranchId   = $user->branch_id ?? null;
       
        $subAdminId = session('selectedSubAdminId');
        $categories = Category::where('isDeleted', 0)->get();

        return view('product/addproduct', compact('categories'));
    }

    public function edit_product($id)
    {
        $product    = Product::with(['category', 'brand'])->find($id);
        $user       = auth()->user();
        $userRole   = $user->role ?? '';
        $userId     = $user->id ?? null;
        $subAdminId = session('selectedSubAdminId');

        $categories = Category::where('isDeleted', 0)->get();

        return view('product/editproduct', compact('product', 'categories'));
    }

    public function product_detail($id)
    {
        $view_id = $id;
        $product = Product::with(['category'])->find($view_id);

        return view('product/product-details', compact('view_id'));
    }

    public function product_import(Request $request)
    {
        return view('product/importproduct');
    }

    public function product_view($id)
    {
        $product = Product::with(['category', 'brand'])->find($id);

        if (! $product) {
            return redirect()->route('product.list')->with('error', 'Product not found.');
        }

        // Send both $product and $view_id to the view
        return view('product/product_view', [
            'product' => $product,
            'view_id' => $id,
        ]);
    }
}
