<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\User;
use App\Models\Product;
use PhpParser\Builder\Function_;
use PhpParser\Node\Expr\FuncCall;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{


    public function getAllCategory(Request $request)
    {
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role;
        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif ((!empty($request->sub_admin_id) || !empty($request->selectedSubAdminId)) && $userRole == 'admin') {
            $branchIdToUse = $request->sub_admin_id ?? $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $userData->id;
        }

        $categories = Category::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->orderBy('id', 'desc')->get();

        return response()->json(['status' => true, 'data' => $categories], 200);
    }



    public function addcategory(Request $request)
    {
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role;
        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif ((!empty($request->sub_admin_id) || !empty($request->selectedSubAdminId)) && $userRole == 'admin') {
            $branchIdToUse = $request->sub_admin_id ?? $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $userData->id;
        }

        $validator = Validator::make($request->all(), [
            'name'  => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('categories')->where(function ($query) use ($branchIdToUse) {
                    return $query->where('isDeleted', 0)->where('branch_id', $branchIdToUse);
                })
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp', // Ensure it's a single image file
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Store the image if it exists
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('img/category', 'public'); // Save in storage/app/public/img/category
        }

        // Save category data
        $category = Category::create([
            'name'  => $request->name,
            'image' => $imagePath, // Store image path in DB
            'branch_id' => $branchIdToUse, // Store branch_id
        ]);

        return response()->json(['status' => true, 'category' => $category], 200);
    }
    public function deleteCategory($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }

        $productCount = Product::where('category_id', $id)
            ->where('isDeleted', 0) // Only consider active products
            ->count();

        if ($productCount > 0) {
            return response()->json([
                'status' => false,
                'message' => 'This category is associated with products and cannot be deleted.'
            ], 400); // 400 Bad Request
        }

        // Soft delete category
        $category->isDeleted = 1;
        $category->save();

        return response()->json(['status' => true, 'message' => 'Category deleted successfully'], 200);
    }

    public function updatecategory(Request $request)
    {
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role;
        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif ((!empty($request->sub_admin_id) || !empty($request->selectedSubAdminId)) && $userRole == 'admin') {
            $branchIdToUse = $request->sub_admin_id ?? $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $userData->id;
        }

        // Find category first
        $category = Category::find($request->category_id);
        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|numeric|exists:categories,id',
            'name'        => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('categories')->ignore($request->category_id)->where(function ($query) use ($branchIdToUse) {
                    return $query->where('isDeleted', 0)->where('branch_id', $branchIdToUse);
                })
            ],
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp', // Image validation
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            // Store new image
            $imagePath = $request->file('image')->store('img/category', 'public');
        } else {
            $imagePath = $category->image; // Keep existing image
        }

        // Update category
        $category->update([
            'name'  => $request->name,
            'image' => $imagePath,
            // branch_id is typically not updated here, but it could be if needed.
        ]);

        return response()->json(['status' => true, 'category' => $category], 200);
    }
}
