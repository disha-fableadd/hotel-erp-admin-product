<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Brand;
use App\Models\User;
use App\Models\Product;
use PhpParser\Builder\Function_;
use PhpParser\Node\Expr\FuncCall;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    // public function getAllBrand(Request $request)
    // {
    //     $brand = Brand::where('isDeleted', 0)->orderBy('id', 'desc')->get();
    //     return response()->json(['status' => true, 'data' => $brand], 200);
    // }
    public function getAllBrand(Request $request)
    {
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role;

        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif (
            (!empty($request->sub_admin_id) || !empty($request->selectedSubAdminId))
            && $userRole == 'admin'
        ) {
            $branchIdToUse = $request->sub_admin_id
                ?? $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $userData->id;
        }

        $brand = Brand::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $brand
        ], 200);
    }
   public function addBrand(Request $request)
    {
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role;

        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif (
            (!empty($request->sub_admin_id) || !empty($request->selectedSubAdminId))
            && $userRole == 'admin'
        ) {
            $branchIdToUse = $request->sub_admin_id
                ?? $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $userData->id;
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('brands', 'name')
                    ->where(function ($query) use ($branchIdToUse) {
                        return $query
                            ->where('isDeleted', 0)
                            ->where('branch_id', $branchIdToUse);
                    }),
            ],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif',
        ], [
            'name.required' => 'Brand name is required.',
            'name.unique' => 'This brand name already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = null;

        if ($request->hasFile('logo')) {
            $imagePath = $request->file('logo')
                ->store('brand', 'public');
        }

        $brand = Brand::create([
            'name' => $request->name,
            'logo' => $imagePath,
            'branch_id' => $branchIdToUse,
        ]);

        return response()->json([
            'status' => true,
            'brand' => $brand
        ], 200);
    }
    public function updateBrand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required|numeric|exists:brands,id',
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,webp,jpg,gif', // Ensure it's a single image file
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Find brand
        $brand = Brand::find($request->brand_id);
        if (!$brand) {
            return response()->json(['status' => false, 'message' => 'Brand not found'], 404);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }

            // Store new image
            $imagePath = $request->file('image')->store('img/brand', 'public');
        } else {
            $imagePath = $brand->logo; // Keep existing image
        }

        // Update brand
        $brand->update([
            'name'  => $request->name,
            'logo' => $imagePath,
        ]);
        return response()->json(['status' => true, 'category' => $brand], 200);
    }

    public function deleteBrand($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['status' => false, 'message' => 'Brand not found'], 404);
        }

        // Check if any product is using this brand
        $isUsedInProducts = Product::where('brand_id', $id)->where('isDeleted', 0)->exists();

        if ($isUsedInProducts) {
            return response()->json([
                'status' => false,
                'message' => 'This brand is associated with products and cannot be deleted.'
            ], 409); // 409 Conflict
        }

        // Soft delete: set isDeleted = 1
        $brand->isDeleted = 1;
        $brand->save();

        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully.'
        ], 200);
    }
    
}
