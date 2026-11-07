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
use Illuminate\Support\Facades\Schema;

class CategoryController extends Controller
{
    protected function ensureKotPrintTargetColumn(): void
    {
        try {
            if (! Schema::hasTable('categories') || Schema::hasColumn('categories', 'kot_print_target')) {
                return;
            }
            Schema::table('categories', function ($table) {
                $table->string('kot_print_target', 20)->default('kitchen');
            });
        } catch (\Throwable $e) {
            // ignore — migration may be applied separately
        }
    }

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
            ->where(function ($q) use ($branchIdToUse) {
                $q->where('branch_id', $branchIdToUse)->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->orderByDesc('id')
            ->get();

        if ($categories->isEmpty()) {
            $categories = Category::where('isDeleted', 0)
                ->orderBy('name')
                ->orderByDesc('id')
                ->get();
        }

        return response()->json(['status' => true, 'data' => $categories], 200);
    }



    public function addcategory(Request $request)
    {
        $this->ensureKotPrintTargetColumn();

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

        $gstApplicable = $request->has('gst_applicable')
            ? filter_var($request->input('gst_applicable'), FILTER_VALIDATE_BOOLEAN)
            : true;

        $kotTarget = strtolower(trim((string) $request->input('kot_print_target', Category::KOT_TARGET_KITCHEN)));
        if (! in_array($kotTarget, [Category::KOT_TARGET_KITCHEN, Category::KOT_TARGET_BILLING], true)) {
            $kotTarget = Category::KOT_TARGET_KITCHEN;
        }

        // Save category data
        $payload = [
            'name'  => $request->name,
            'image' => $imagePath, // Store image path in DB
            'branch_id' => $branchIdToUse, // Store branch_id
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'gst_applicable')) {
            $payload['gst_applicable'] = $gstApplicable;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'kot_print_target')) {
            $payload['kot_print_target'] = $kotTarget;
        }

        $category = Category::create($payload);

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
        $this->ensureKotPrintTargetColumn();

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
        $payload = [
            'name'  => $request->name,
            'image' => $imagePath,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'gst_applicable') && $request->has('gst_applicable')) {
            $payload['gst_applicable'] = filter_var($request->input('gst_applicable'), FILTER_VALIDATE_BOOLEAN);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'kot_print_target') && $request->has('kot_print_target')) {
            $kotTarget = strtolower(trim((string) $request->input('kot_print_target')));
            if (! in_array($kotTarget, [Category::KOT_TARGET_KITCHEN, Category::KOT_TARGET_BILLING], true)) {
                $kotTarget = Category::KOT_TARGET_KITCHEN;
            }
            $payload['kot_print_target'] = $kotTarget;
        }

        $category->update($payload);

        return response()->json(['status' => true, 'category' => $category], 200);
    }

    /**
     * Toggle GST applicable for a category (list switch).
     */
    public function toggleGstApplicable(Request $request, $id)
    {
        $category = Category::where('id', $id)->where('isDeleted', 0)->first();
        if (! $category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }

        if (! \Illuminate\Support\Facades\Schema::hasColumn('categories', 'gst_applicable')) {
            return response()->json([
                'status' => false,
                'message' => 'Please run migrations to enable category GST setting.',
            ], 422);
        }

        $value = $request->has('gst_applicable')
            ? filter_var($request->input('gst_applicable'), FILTER_VALIDATE_BOOLEAN)
            : ! (bool) $category->gst_applicable;

        $category->gst_applicable = $value;
        $category->save();

        return response()->json([
            'status' => true,
            'message' => $value
                ? 'GST will apply to products in this category.'
                : 'GST will not apply to products in this category.',
            'gst_applicable' => (bool) $category->gst_applicable,
            'category' => $category,
        ]);
    }
}
