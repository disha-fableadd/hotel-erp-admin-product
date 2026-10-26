<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Purchases;
use App\Models\QuantityType;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Milon\Barcode\DNS1D;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductController extends Controller
{
    // public function getAllProduct(Request $request)
    // {
    //     $user              = Auth::guard('api')->user();
    //     $userId            = $user->id;
    //     $lowStockThreshold = DB::table('settings')->value('low_stock');

    //     $search       = trim((string) $request->query('search'));
    //     $categoryId   = $request->query('category_id');
    //     $brandId      = $request->query('brand_id');
    //     $userRole     = $user->role;
    //     $userBranchId = $user->branch_id;

    //     // Resolve sub_branch_id / selectedSubAdminId (same pattern as QuantityTypeController)
    //     $subBranchId = $request->sub_branch_id
    //         ?? $request->selectedSubAdminId
    //         ?? null;

    //     // Guard against JS localStorage sending the literal string "null"
    //     if ($subBranchId === 'null' || $subBranchId === '') {
    //         $subBranchId = null;
    //     }

    //     $query = Product::with(['category', 'brand', 'quantityType'])
    //         ->where('isDeleted', 0)
    //         ->orderBy('id', 'desc');

    //     // Apply branch filter
    //     if ($userRole === 'admin') {
    //         // Only filter by a specific branch when one is explicitly selected
    //         if (! empty($subBranchId)) {
    //             $query->where('branch_id', $subBranchId);
    //         }
    //         // else: no filter → show all branches for admin
    //     } elseif ($userRole === 'sub-admin') {
    //         $query->where('branch_id', $userId);
    //     } elseif ($userRole === 'staff') {
    //         $query->where('branch_id', $userBranchId);
    //     }
    //     // Apply category filters
    //     if (! empty($categoryId)) {
    //         $query->where('category_id', $categoryId);
    //     }

    //     if (! empty($brandId)) {
    //         $query->where('brand_id', $brandId);
    //     }

    //     if ($search !== '') {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%{$search}%")
    //                 ->orWhere('SKU', 'like', "%{$search}%")
    //                 ->orWhere('barcode', 'like', "%{$search}%")
    //                 ->orWhereHas('category', function ($categoryQuery) use ($search) {
    //                     $categoryQuery->where('name', 'like', "%{$search}%");
    //                 })
    //                 ->orWhereHas('brand', function ($brandQuery) use ($search) {
    //                     $brandQuery->where('name', 'like', "%{$search}%");
    //                 });
    //         });
    //     }

    //     $products = $query->get();
    //     // dd($products);
    //     $settings         = DB::table('settings')->first();
    //     $currencySymbol   = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     return response()->json([
    //         'status'            => true,
    //         'data'              => $products,
    //         'currencySymbol'    => $currencySymbol,
    //         'currencyPosition'  => $currencyPosition,
    //         'lowStockThreshold' => $lowStockThreshold,
    //     ], 200);
    // }
    public function getAllProduct(Request $request)
{
    $user = Auth::guard('api')->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    $userId = $user->id;
    $userRole = strtolower(trim($user->role ?? ''));
    $userBranchId = $user->branch_id;

    $lowStockThreshold = DB::table('settings')->value('low_stock');

    $search = trim((string) $request->query('search', ''));
    $categoryId = $request->query('category_id');
    $brandId = $request->query('brand_id');

    $subBranchId = $request->sub_branch_id
        ?? $request->selectedSubAdminId
        ?? null;

    if (
        $subBranchId === null ||
        $subBranchId === '' ||
        $subBranchId === 'null' ||
        $subBranchId === 'undefined'
    ) {
        $subBranchId = null;
    }

    if ($userRole === 'admin') {
        $branchIdToUse = !empty($subBranchId)
            ? $subBranchId
            : $userId;

    } elseif ($userRole === 'sub-admin') {
        $branchIdToUse = $userId;

    } elseif ($userRole === 'staff') {
        $branchIdToUse = $userBranchId;

    } else {
        $branchIdToUse = $userId;
    }

    $query = Product::with([
        'category',
        'brand',
        'quantityType'
    ])
    ->where('isDeleted', 0)
    ->where('branch_id', $branchIdToUse)
    ->orderBy('id', 'desc');

    if (!empty($categoryId)) {
        $query->where('category_id', $categoryId);
    }

    if (!empty($brandId)) {
        $query->where('brand_id', $brandId);
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('SKU', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhereHas('category', function ($categoryQuery) use ($search) {
                    $categoryQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('brand', function ($brandQuery) use ($search) {
                    $brandQuery->where('name', 'like', "%{$search}%");
                });
        });
    }

    $products = $query->get();

    $settings = DB::table('settings')
        ->where('branch_id', $branchIdToUse)
        ->first();

    $currencySymbol = $settings->currency_symbol ?? '₹';
    $currencyPosition = $settings->currency_position ?? 'left';

    return response()->json([
        'status' => true,
        'data' => $products,
        'branch_id_used' => $branchIdToUse,
        'currencySymbol' => $currencySymbol,
        'currencyPosition' => $currencyPosition,
        'lowStockThreshold' => $lowStockThreshold,
    ], 200);
}

    public function export_product(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->branch_id;
        $branch_id    = $user->id;

        if ($role === 'staff' && $userBranchId) {
            $branch_id = $userBranchId;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $branch_id = $request->selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        // ✅ Base query: products of the branch
        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('quantity_types', 'products.quantity_type_id', '=', 'quantity_types.id')
            ->select(
                'products.id',
                'products.name as product_name',
                'products.SKU',
                'products.barcode',
                'products.price',
                'products.quantity',
                'quantity_types.quantity_type as quantity_type',
                'products.description',
                'categories.name as category_name',
                'brands.name as brand_name',
                'products.created_at'
            )
            ->where('products.isDeleted', '=', 0)
            ->where('products.branch_id', '=', $branch_id);

        // ✅ Filters
        if ($request->has('category_id') && ! empty($request->category_id)) {
            $query->where('products.category_id', $request->category_id);
        }

        if ($request->has('brand_id') && ! empty($request->brand_id)) {
            $query->where('products.brand_id', $request->brand_id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.SKU', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%")
                    ->orWhere('categories.name', 'like', "%{$search}%")
                    ->orWhere('brands.name', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('products.id', 'desc')->get();

        // ✅ Currency settings
        $settings          = DB::table('settings')->where('branch_id', $branch_id)->first();
        $currencySymbolRaw = $settings->currency_symbol ?? '₹';
        $currencySymbol    = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $currencyPosition  = $settings->currency_position ?? 'left';

        // ✅ Create Excel
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Product Name');
        $sheet->setCellValue('B1', 'SKU');
        $sheet->setCellValue('C1', 'Barcode');
        $sheet->setCellValue('D1', 'Category');
        $sheet->setCellValue('E1', 'Brand');
        $sheet->setCellValue('F1', 'Quantity');
        $sheet->setCellValue('G1', 'Quantity Type');
        $sheet->setCellValue('H1', 'Price');
        $sheet->setCellValue('I1', 'Description');
        $sheet->setCellValue('J1', 'Created At');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        // Data rows
        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->product_name);
            $sheet->setCellValue('B' . $row, $product->SKU);
            $sheet->setCellValue('C' . $row, $product->barcode);
            $sheet->setCellValue('D' . $row, $product->category_name ?? '-');
            $sheet->setCellValue('E' . $row, $product->brand_name ?? '-');
            $sheet->setCellValue('F' . $row, $product->quantity);
            $sheet->setCellValue('G' . $row, $product->quantity_type);
            $sheet->setCellValue('H' . $row, $currencyPosition === 'left'
                ? $currencySymbol . ' ' . $product->price
                : $product->price . ' ' . $currencySymbol);
            $sheet->setCellValue('I' . $row, $product->description ?? '');
            $sheet->setCellValue('J' . $row, \Carbon\Carbon::parse($product->created_at)->format('Y-m-d'));
            $row++;
        }

        // ✅ Download response
        $writer   = new Xlsx($spreadsheet);
        // $fileName = 'Products_' . date('Ymd_His') . '.xlsx';

        // return response()->streamDownload(function () use ($writer) {
        //     $writer->save('php://output');
        // }, $fileName, [
        //     'Content-Type'                  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        //     'Access-Control-Expose-Headers' => 'Content-Disposition',
        // ]);
        $fileName = 'Products_' . date('Ymd_His') . '.xlsx';
        $relativePath = 'product-exports/' . $fileName;

        \Storage::disk('public')->makeDirectory('product-exports');
        $filePath = storage_path("app/public/{$relativePath}");
        $writer->save($filePath);

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status'    => true,
            'message'   => 'Product Excel exported successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function export_product_pdf(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->branch_id;
        $branch_id    = $user->id;

        if ($role === 'staff' && $userBranchId) {
            $branch_id = $userBranchId;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $branch_id = $request->selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        // ✅ Base query: products of the branch
        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('quantity_types', 'products.quantity_type_id', '=', 'quantity_types.id')
            ->select(
                'products.id',
                'products.name as product_name',
                'products.SKU',
                'products.barcode',
                'products.price',
                'products.quantity',
                'quantity_types.quantity_type as quantity_type',
                'products.description',
                'categories.name as category_name',
                'brands.name as brand_name',
                'products.created_at'
            )
            ->where('products.isDeleted', '=', 0)
            ->where('products.branch_id', '=', $branch_id);

        // ✅ Filters
        if ($request->has('category_id') && ! empty($request->category_id)) {
            $query->where('products.category_id', $request->category_id);
        }

        if ($request->has('brand_id') && ! empty($request->brand_id)) {
            $query->where('products.brand_id', $request->brand_id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.SKU', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%")
                    ->orWhere('categories.name', 'like', "%{$search}%")
                    ->orWhere('brands.name', 'like', "%{$search}%");
            });
        }

         if ($request->has('quantity_type_id') && ! empty($request->quantity_type_id)) {
            $query->where('products.quantity_type_id', $request->quantity_type_id);
        }

        $products = $query->orderBy('products.id', 'desc')->get();

        // ✅ Currency settings
        $settings          = DB::table('settings')->where('branch_id', $branch_id)->first();
        $currencySymbolRaw = $settings->currency_symbol ?? '₹';
        $currencySymbol    = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $currencyPosition  = $settings->currency_position ?? 'left';

        // Get user info for PDF
        $userName = $user->name ?? 'N/A';
        $gstNum   = $user->gst_number ?? 'N/A';

        // ✅ Generate PDF using a Blade view
        $pdf = Pdf::loadView('product.product_pdf', [
            'products'        => $products,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'settings'         => $settings,
            'userName'         => $userName,
            'gstNum'           => $gstNum,
        ])->setPaper('A4', 'portrait');

        // $fileName = 'Products_' . date('Ymd_His') . '.pdf';

        // return $pdf->download($fileName);


        // ✅ Generate PDF first
        // $pdf = PDF::loadView('salary.pdf', [
        //     'data'     => $data,
        //     'month'    => $month,
        //     'year'     => $year,
        //     'settings' => $settings,
        // ])->setPaper('A4', 'portrait');

        // ✅ Define file name and path
        $fileName     = 'Products_' . date('Ymd_His') . '.pdf';
        $relativePath = 'all-products/' . $fileName;

        // ✅ Ensure directory exists in storage/app/public/all-products
        \Storage::disk('public')->makeDirectory('all-products');

        // ✅ Save file to public disk
        \Storage::disk('public')->put($relativePath, $pdf->output());

        // ✅ Build file URL
        // $fileUrl = asset('storage/' . $relativePath);
        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        // or if you use custom image path env
        // $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        // ✅ Return proper JSON response
        return response()->json([
            'status'    => true,
            'message'   => 'Product PDF generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function getProductById($id)
    {
        $product = Product::with(['category', 'brand', 'quantityType'])->find($id);

        if ($product) {
            return response()->json(['status' => true, 'product' => $product], 200);
        } else {
            return response()->json(['status' => false, 'error' => 'Product not found'], 404);
        }
    }
    public function getProductsByCategory($categoryId)
{
    try {
        $user = Auth::guard('api')->user();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }

        // Branch ID logic
        $branchId = $user->id;
        if ($user->role === 'staff') {
            $branchId = $user->branch_id;
        }
        
        // Check if sub_branch_id is provided in request
        $request = request();
        if ($request->has('sub_branch_id') && !empty($request->sub_branch_id)) {
            $branchId = $request->sub_branch_id;
        }

        // Validate category exists
        $category = Category::where('id', $categoryId)
            ->where('isDeleted', 0)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Get products with quantityType relationship
        $products = Product::with(['category', 'quantityType'])
            ->where('category_id', $categoryId)
            ->where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->get();

        // Format response data
        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'price' => $product->price,
                'quantity_type_id' => $product->quantity_type_id,
                'quantity_type_name' => $product->quantityType ? $product->quantityType->quantity_type : null,
            ];
        });

        return response()->json([
            'status' => true,
            'category' => $category->name,
            'data' => $formattedProducts,
            'count' => $products->count()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // public function createProduct(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         // 'product_id' => 'required|numeric|exists:products,id',
    //         'vendor_id' => 'nullable|numeric',
    //         'category_id' => 'required|numeric',
    //         'brand_id' => 'nullable|numeric',
    //         'name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'price' => 'required|numeric',
    //         'SKU' => 'required|numeric|unique:products,SKU,' . $request->product_id,
    //         'barcode' => 'nullable|string|max:255|unique:products,barcode', // <-- Add this line
    //         'quantity' => 'required|integer',
    //         'status' => 'nullable|in:active,inactive',
    //         'availablility' => 'nullable|in:in_stock,out_stock',
    //         'images' => 'nullable|array',
    //         'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp',
    //     ], [], [
    //         // ATTRIBUTE CUSTOMIZATION HERE
    //         'category_id' => 'category',
    //         'brand_id' => 'brand',
    //         'SKU' => 'SKU',
    //         // 'product_id' => 'product',
    //         'availablility' => 'availability',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     // Handle image uploads
    //     $imagePaths = [];
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $image) {
    //             $path = $image->store('img/product', 'public'); // Save in storage/app/public/img/product
    //             $imagePaths[] = $path;
    //         }
    //     }
    //     // dd($request->quantity);
    //     // Get user role and ID for branch assignment
    //     $user = Auth::guard('api')->user();
    //     $userRole = $user->role ?? '';
    //     $userId = $user->id ?? null;
    //     $StaffBranchId = $user->branch_id ?? null;

    //     // Determine branch_id based on user role
    //     $branchId = null;
    //     if ($userRole === 'sub-admin') {
    //         $branchId = $userId; // Sub-admin's products belong to their branch
    //     } elseif ($userRole === 'admin') {
    //         $branchId = $request->branch_id ?? $userId; // Admin can assign to any branch
    //     } elseif ($userRole === 'Staff')
    //     {
    //         $branchId = $StaffBranchId;
    //     }
    //     // dd($branchId);
    //     if (!empty($request->sub_admin_id)) {
    //         $branchId = $request->sub_admin_id;
    //     }
    //     // Create product
    //     $product = Product::create([
    //         'vendor_id' => $request->vendor_id ?? 1,
    //         'category_id' => $request->category_id,
    //         'brand_id' => $request->brand_id,
    //         'branch_id' => $branchId,
    //         'name' => $request->name,
    //         'description' => $request->description,
    //         'price' => $request->price,
    //         'SKU' => $request->SKU,
    //         'barcode' => $request->barcode, // <-- Add this line
    //         'quantity' => $request->quantity,
    //         'status' => $request->status ?? 'active',
    //         // 'availablility' => $request->availablility,
    //         'availablility' => $request->quantity == 0 ? 'out_stock' : ($request->availablility ?? 'in_stock'),
    //         'images' => json_encode($imagePaths), // Store as JSON
    //     ]);
    //     // dd($product->id);
    //      $productInventory  = ProductInventory::create([
    //             'product_id' => $product->id,
    //             'initial_stock' => $request->quantity,
    //             'current_stock' => $request->quantity,
    //             'branch_id' => $branchId,
    //             'create_by' => $branchId ?? $userId,
    //             'type' => 'Create',
    //             'date'  => now(),
    //         ]);

    //     return response()->json(['status' => true, 'product' => $product], 200);
    // }
    public function createProduct(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'vendor_id'     => 'nullable|numeric',
            'category_id'   => 'required|numeric',
            'brand_id'      => 'nullable|numeric',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric',
            'SKU'           => 'required|string|max:255|unique:products,SKU', // ✅ removed $request->product_id (not needed for create)
            'barcode'       => 'nullable|string|max:255|unique:products,barcode',
            'quantity'      => 'required|integer|min:0',
            'status'        => 'nullable|in:active,inactive',
            'availablility' => 'nullable|in:in_stock,out_stock',
            'images'        => 'nullable|array',
            'images.*'      => 'image|mimes:jpeg,png,jpg,gif,webp',
        ], [], [
            'category_id'   => 'category',
            'brand_id'      => 'brand',
            'SKU'           => 'SKU',
            'availablility' => 'availability',
            // 'create_by'     => 'create_by'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path         = $image->store('img/product', 'public');
                $imagePaths[] = $path;
            }
        }

        // ✅ Get user role and branch logic
        $user          = Auth::guard('api')->user();
        $userRole      = $user->role ?? '';
        $userId        = $user->id ?? null;
        $StaffBranchId = $user->branch_id ?? null;
        $branchId = null;
        if ($userRole === 'sub-admin') {
            $branchId = $userId; // Sub-admin owns their branch
        } elseif ($userRole === 'admin') {
            $branchId = $request->branch_id ?? $userId;
        } elseif (strtolower($userRole) === 'staff') { // ✅ lowercase check
            $branchId = $StaffBranchId;
        }
        // dd($StaffBranchId);

        // if (! empty($request->sub_admin_id)) {
        //     $branchId = $request->sub_admin_id; // ✅ override if passed
        // }
        if (!empty($request->sub_admin_id) && $userRole !== 'staff') {
            $branchId = $request->sub_admin_id;
        }

        // ✅ Create Product
        $product = Product::create([
            'vendor_id'     => $request->vendor_id ?? 1,
            'category_id'   => $request->category_id,
            'brand_id'      => $request->brand_id,
            'branch_id'     => $branchId,
            'name'          => $request->name,
            'description'   => $request->description,
            'price'         => $request->price,
            'quantity_type_id' => $request->quantity_type_id,
            'SKU'           => $request->SKU,
            'barcode'       => $request->barcode,
            'quantity'      => $request->quantity,
            'status'        => $request->status ?? 'active',
            'availablility' => $request->quantity == 0
                ? 'out_stock'
                : ($request->availablility ?? 'in_stock'),
            'images'        => json_encode($imagePaths),
            // 'create_by'     => $userId,
        ]);

        // ✅ Create ProductInventory log
        ProductInventory::create([
            'product_id'    => $product->id,
            'initial_stock' => $request->quantity,
            'current_stock' => $request->quantity,
            'branch_id'     => $branchId,
            'create_by'     => $userId, // ✅ use userId (not branchId, which is confusing)
            'type'          => 'Create',
            'date'          => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Product created successfully',
            'product' => $product,
        ], 200);
    }

    public function updateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id'     => 'nullable|numeric',
            'category_id'   => 'required|numeric',
            'brand_id'      => 'nullable|numeric',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric',
            'SKU'           => 'required|string|max:255|unique:products,SKU,' . $request->product_id . ',id',
            'barcode'       => 'nullable|string|max:255|unique:products,barcode,' . $request->product_id . ',id',
            'quantity'      => 'required|integer|min:0',
            'quantity_type_id' => 'nullable',
            'status'        => 'nullable|in:active,inactive',
            'availablility' => 'nullable|in:in_stock,out_stock',
            'images'        => 'nullable|array',
            'images.*'      => 'image|mimes:jpeg,png,jpg,gif,webp',
        ], [], [
            'category_id'   => 'category',
            'brand_id'      => 'brand',
            'SKU'           => 'SKU',
            'availablility' => 'availability',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Find the product
        $product = Product::find($request->product_id);
        if (! $product) {
            return response()->json(['status' => false, 'message' => 'Product not found'], 404);
        }

        // Get existing images
        $existingImages = json_decode($product->images, true) ?? [];

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path             = $image->store('img/product', 'public');
                $existingImages[] = $path;
            }
        }

        // Get user role and ID for branch assignment
        $user     = auth()->user();
        $userRole = $user->role ?? '';
        $userId   = $user->id ?? null;

        // Determine branch_id
        $branchId = $product->branch_id;
        if ($userRole === 'sub-admin') {
            $branchId = $userId;
        } elseif ($userRole === 'admin' && $request->has('branch_id')) {
            $branchId = $request->branch_id;
        }

        // Update product
        $product->update([
            'vendor_id'     => $request->vendor_id ?? 1,
            'category_id'   => $request->category_id,
            'brand_id'      => $request->brand_id,
            'branch_id'     => $branchId,
            'name'          => $request->name,
            'description'   => $request->description,
            'price'         => $request->price,
            'quantity_type'         => $request->quantity_type,
            'SKU'           => $request->SKU,
            'barcode'       => $request->barcode,
            'quantity'      => $request->quantity,
            'quantity_type_id' => $request->quantity_type_id,
            'status'        => $request->status,
            'availablility' => $request->quantity == 0 ? 'out_stock' : ($request->availablility ?? 'in_stock'),
            'images'        => json_encode($existingImages),
            'capacity'      => $request->capacity,
            'voltage'       => $request->voltage,
            'warranty'      => $request->warranty,
            'expiry_date'   => $request->expiry_date,
        ]);

        // ✅ Insert into product inventory log
        ProductInventory::create([
            'product_id'    => $product->id,
            'initial_stock' => $request->quantity,
            'current_stock' => $request->quantity,
            'branch_id'     => $branchId,
            'create_by'     => $userId,
            'type'          => 'Edit',
            'date'          => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['status' => false, 'error' => 'Product not found'], 404);
        }

        $orderItemExists = OrderItem::where('product_id', $id)->exists();
        $purchaseExists  = Purchases::where('item', $id)->exists();

        if ($orderItemExists) {
            return response()->json([
                'status' => false,
                'error'  => 'Product is associated with existing orders and cannot be deleted.',
            ], 409);
        }

        if ($purchaseExists) {
            return response()->json([
                'status' => false,
                'error'  => 'Product is associated with existing purchases and cannot be deleted.',
            ], 409);
        }

        // Soft delete: mark isDeleted as 1
        $product->isDeleted = 1;
        $product->save();

        return response()->json(['status' => true, 'message' => 'Product deleted successfully'], 200);
    }

    public function removeProductImage(Request $request)
    {
        $product = Product::find($request->product_id);

        if (! $product) {
            return response()->json(["success" => false, "message" => "Product not found"]);
        }

        $images = json_decode($product->images, true);

        // Check if image exists
        if (($key = array_search($request->image, $images)) !== false) {
            // Remove image from storage
            Storage::delete("storage/img/product" . $request->image);

            // Remove image from array and update database
            unset($images[$key]);
            $product->images = json_encode(array_values($images)); // Re-index array
            $product->save();

            return response()->json(["success" => true, "message" => "Image deleted"]);
        }

        return response()->json(["success" => false, "message" => "Image not found"]);
    }

    public function importProducts(Request $request)
    {
        $user     = Auth::guard('api')->user();
        $branchId = $user->id; // User's branch ID

        $request->validate([
            'csv_file' => 'required|mimes:csv,txt,application/csv,text/csv|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));

        if (count($data) > 0) {
            $header = array_shift($data);

            $insertedCount = 0;
            $updatedSKUs   = [];
            $invalidSKUs   = [];

            foreach ($data as $row) {
                $name         = strtolower(trim($row[0] ?? ''));
                $categoryName = strtolower(trim($row[1] ?? ''));
                $sku          = trim($row[3] ?? '');
                $quantity     = (int) ($row[4] ?? 0);
                $price        = $row[5] ?? 0;
                $status       = strtolower(trim($row[6] ?? 'inactive'));
                $availability = strtolower(trim($row[7] ?? 'out of stock'));
                $description  = isset($row[8]) ? strtolower(trim($row[8])) : null;

                // ✅ New fields
                $capacity    = isset($row[9]) ? trim($row[9]) : null;
                $voltage     = isset($row[10]) ? trim($row[10]) : null;
                $warranty    = isset($row[11]) ? trim($row[11]) : null;
                $expiry_date = isset($row[12]) ? trim($row[12]) : null;

                if ($expiry_date) {
                    try {
                        $expiry_date = \Carbon\Carbon::createFromFormat('m/d/Y', $expiry_date)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $expiry_date = null; // invalid date
                    }
                }

                // ❌ Skip empty SKU
                if (empty($sku)) {
                    $invalidSKUs[] = 'empty SKU on row';
                    continue;
                }

                // ✅ Create or fetch category globally
                $category = Category::firstOrCreate(
                    ['name' => $categoryName],
                    ['branch_id' => null, 'isDeleted' => 0]
                );

                // ✅ Check if product with same SKU exists in the same branch
                $product = Product::where('SKU', $sku)
                    ->where('branch_id', $branchId)
                    ->first();

                if ($product) {
                    $product->quantity += $quantity;
                    // Update new fields if needed
                    // $product->capacity    = $capacity;
                    // $product->voltage     = $voltage;
                    // $product->warranty    = $warranty;
                    // $product->expiry_date = $expiry_date;
                    $product->save();

                    // ✅ Log inventory for stock update
                    ProductInventory::create([
                        'product_id'    => $product->id,
                        'initial_stock' => $quantity,
                        'current_stock' => $quantity,
                        'type'          => 'Create',
                        'branch_id'     => $branchId,
                        'date'          => now(),
                    ]);

                    $updatedSKUs[] = $sku;
                } else {
                    $newProduct = Product::create([
                        'name'          => $name,
                        'category_id'   => $category->id,
                        'SKU'           => $sku,
                        'quantity'      => $quantity,
                        'price'         => $price,
                        'status'        => $status,
                        'availablility' => $availability,
                        'description'   => $description,
                        'branch_id'     => $branchId,
                        // ✅ New fields
                        'capacity'      => $capacity,
                        'voltage'       => $voltage,
                        'warranty'      => $warranty,
                        'expiry_date'   => $expiry_date,
                    ]);

                    // ✅ Log inventory for new product import
                    ProductInventory::create([
                        'product_id'    => $newProduct->id,
                        'initial_stock' => $quantity,
                        'current_stock' => $quantity,
                        'type'          => 'Import',
                        'branch_id'     => $branchId,
                        'date'          => now(),
                    ]);

                    $insertedCount++;
                }
            }

            // ✅ Return response
            if ($insertedCount > 0 || count($updatedSKUs) > 0) {
                return response()->json([
                    "status"       => true,
                    "message"      => $insertedCount > 0
                        ? "$insertedCount product(s) imported successfully."
                        : "Existing product(s) updated with additional quantity.",
                    "updated_skus" => $updatedSKUs,
                    "invalid_skus" => $invalidSKUs,
                ]);
            } elseif (! empty($invalidSKUs)) {
                return response()->json([
                    "status"       => false,
                    "message"      => "No new products imported.",
                    "invalid_skus" => $invalidSKUs,
                ]);
            } else {
                return response()->json([
                    "status"  => false,
                    "message" => "CSV file is empty or contains invalid data.",
                ]);
            }
        }

        return response()->json([
            "status"  => false,
            "message" => "CSV file is empty!",
        ]);
    }

    public function getProductDetails($id)
    {
        $product = Product::with(['category', 'quantityType'])->findOrFail($id);

        // Fetch currency settings
        $settings         = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $barcodeGenerator = new DNS1D();
        $barcodeHtml      = null;

        if (! empty($product->barcode)) {
            $barcodeHtml = $barcodeGenerator->getBarcodeHTML($product->barcode, 'C128');
        }

        return response()->json([
            'product'          => $product,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'barcode_html'     => $barcodeHtml,
        ]);
    }

    public function getQuantityHistory($productId)
    {
        $product = Product::find($productId);
        if (! $product) {
            return response()->json(['status' => false, 'message' => 'Product not found.']);
        }

        $history = [];

        // Initial stock (e.g., when product added)
        $history[] = [
            'type'     => 'Added',
            'quantity' => $product->quantity,
            'note'     => 'Initial stock on product creation',
            'date'     => $product->created_at->format('d-M-Y h:i A'),
        ];

        // Sales data (reduce quantity)
        $orderItems = OrderItem::with('order')->where('product_id', $productId)->get();
        foreach ($orderItems as $item) {
            $orderNumber = $item->order ? $item->order->order_number : 'N/A';

            $history[] = [
                'type'     => 'Sold',
                'quantity' => $item->quantity,
                'note'     => 'Sold in order #' . $orderNumber,
                'date'     => $item->created_at->format('d-M-Y h:i A'),
            ];
        }

        return response()->json([
            'status'  => true,
            'product' => $product->name,
            'history' => $history,
        ]);
    }

    public function getCategory(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userRole = $user->role ?? '';
        
        $branchId = null;
        if ($userRole === 'sub-admin') {
            $branchId = $user->id;
        } elseif ($userRole === 'admin') {
            $branchId = $request->sub_admin_id ?? $user->id;
        } elseif (in_array(strtolower($userRole), ['staff', 'hr'])) {
            $branchId = $user->branch_id;
        }

        $categories = Category::where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $categories,
        ], 200);
    }

    public function getBrand(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userRole = $user->role ?? '';
        
        $branchId = null;
        if ($userRole === 'sub-admin') {
            $branchId = $user->id;
        } elseif ($userRole === 'admin') {
            $branchId = $request->sub_admin_id ?? $user->id;
        } elseif (in_array(strtolower($userRole), ['staff', 'hr'])) {
            $branchId = $user->branch_id;
        }

        $brands = Brand::where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $brands,
        ], 200);
    }



    public function edit_product(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        // dd($product);
        return response()->json([
            'status'  => true,
            'product' => $product,
        ]);
    }
    public function getQuantityTypes(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            $userRole = $user->role ?? '';
            $branchId = null;

            if ($userRole === 'sub-admin') {
                $branchId = $user->id;
            } elseif ($userRole === 'admin') {
                $branchId = $request->sub_admin_id
                    ?? $request->selectedSubAdminId
                    ?? $user->id;
            } elseif (in_array(strtolower($userRole), ['staff', 'hr'])) {
                $branchId = $user->branch_id;
            }

            $types = QuantityType::where('isDeleted', 0)
                ->when($branchId !== null, fn($query) => $query->where('branch_id', $branchId))
                ->orderBy('id', 'desc')
                ->get(['id', 'quantity_type']);

            return response()->json([
                "status"  => true,
                "message" => "Quantity Types fetched successfully",
                "data"    => $types
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status"  => false,
                "message" => "Something went wrong",
                "error"   => $e->getMessage()
            ], 500);
        }
    }
}
