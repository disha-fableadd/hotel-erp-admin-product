<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\PurchaseInvoice;
use App\Models\Purchases;
use App\Models\QuantityType;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use App\Models\UserDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PurchaseController extends Controller
{
    // public function purchase_order(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     $userId = $user->id;
    //     $userBranchId = $user->branch_id;
    //     $role = $user->role;

    //     if ($role === 'staff' && $user->branch_id) {
    //         $userBranchId = $user->branch_id;
    //     } elseif ($role === 'admin' && !empty($request->selectedSubAdminId)) {
    //         $userBranchId = $request->selectedSubAdminId;
    //     } else {
    //         $userBranchId = $user->id;
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'vendor_id' => 'required',
    //         'status' => 'required',
    //         'vendor_phone' => 'nullable|numeric',
    //         'discount' => 'nullable|numeric',
    //         'shipping' => 'required|numeric',
    //         'grand_total' => 'required|numeric',
    //         'products' => 'required|array|min:1',
    //         'products.*.id' => 'required',
    //         'products.*.category_id' => 'required',
    //         'products.*.price' => 'required|numeric|min:0',
    //         'products.*.quantity' => 'required|numeric|min:1',
    //         'products.*.total' => 'required|numeric|min:0',
    //         'taxes' => 'nullable|array',
    //         'taxes.*.id' => 'required|numeric',
    //         'taxes.*.name' => 'required|string',
    //         'taxes.*.rate' => 'required|numeric',
    //         'taxes.*.amount' => 'required|numeric',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // ✅ Generate a unique invoice number
    //         do {
    //             $invoice_number = 'INV-' . mt_rand(10000000, 99999999);
    //         } while (PurchaseInvoice::where('invoice_number', $invoice_number)->exists());

    //         // ✅ Check and insert vendor
    //         if (!is_numeric($request->vendor_id)) {
    //             // Check if phone already exists
    //             $vendorPhone = $request->vendor_phone;

    //             if ($vendorPhone !== null && $vendorPhone !== '') {
    //                 // Check if phone already exists
    //                 $existingVendor = User::where('phone', $vendorPhone)->first();

    //                 if ($existingVendor) {
    //                     return response()->json([
    //                         'success' => false,
    //                         'message' => 'A vendor with this phone number already exists.',
    //                     ], 409);
    //                 }
    //             }
    //             $vendor = User::create([
    //                 'name' => $request->vendor_id,
    //                 'phone' => $request->vendor_phone ?? null,
    //                 'role' => 'vendor',
    //                 'status' => $request->status,
    //                 'branch_id' => $userBranchId ?? $userId,
    //             ]);
    //             $vendor_id = $vendor->id;

    //             // ✅ Create UserDetail record after user is created
    //             UserDetail::create([
    //                 'user_id' => $vendor_id,
    //                 'address' => '', // adjust if you collect address info
    //                 'city' => '',
    //                 'state' => '',
    //                 'country' => '',
    //                 'pincode' => '',
    //                 'branch_id' => $userBranchId ?? $userId,
    //             ]);
    //         } else {
    //             $vendor_id = $request->vendor_id;
    //         }

    //         // ✅ Process Products
    //         $processedProducts = [];
    //         foreach ($request->products as $product) {
    //             if (!is_numeric($product['category_id'])) {
    //                 $category = Category::create(['name' => $product['category_id']]);
    //                 $category_id = $category->id;
    //             } else {
    //                 $category_id = $product['category_id'];
    //             }

    //             if (!is_numeric($product['id'])) {
    //                 do {
    //                     $sku = mt_rand(10000000, 99999999);
    //                 } while (Product::where('SKU', $sku)->exists());

    //                 $newProduct = Product::create([
    //                     'name' => $product['id'],
    //                     'category_id' => $category_id,
    //                     'price' => $product['price'],
    //                     'quantity' => $product['quantity'],
    //                     'vendor_id' => $vendor_id,
    //                     'availablility' => 'in_stock',
    //                     'status' => 'active',
    //                     'SKU' => $sku,
    //                     'branch_id' => $userBranchId ?? $userId,
    //                 ]);
    //                 $product_id = $newProduct->id;
    //             } else {
    //                 $existingProduct = Product::find($product['id']);
    //                 if ($existingProduct) {
    //                     $existingProduct->update([
    //                         'price' => $product['price'],
    //                         'quantity' => $existingProduct->quantity + $product['quantity'],
    //                         'category_id' => $category_id,
    //                     ]);
    //                     $product_id = $existingProduct->id;
    //                 }
    //             }

    //             $processedProducts[] = [
    //                 'product_id' => $product_id,
    //                 'price' => $product['price'],
    //                 'quantity' => $product['quantity'],
    //                 'total' => $product['total'],
    //             ];
    //         }

    //         // ✅ Store purchase invoice
    //         $purchaseInvoice = PurchaseInvoice::create([
    //             'invoice_number' => $invoice_number,
    //             'vendor_id' => $vendor_id,
    //             'products' => json_encode($processedProducts),
    //             'total_amount' => collect($processedProducts)->sum('total'),
    //             'discount' => $request->discount ?? 0,
    //             'shipping' => $request->shipping,
    //             'grand_total' => $request->grand_total,
    //             'status' => $request->status,
    //             'taxes' => json_encode($request->taxes),
    //             'branch_id' => $userBranchId ?? $userId,
    //             'created_by' => $$userId,
    //         ]);

    //         // ✅ Store each product in the purchases table
    //         foreach ($processedProducts as $item) {
    //             Purchases::create([
    //                 'invoice_id' => $purchaseInvoice->id, // Link to purchase invoice
    //                 'item' => $item['product_id'],
    //                 'quantity' => $item['quantity'],
    //                 'price' => $item['price'],
    //                 'amount_total' => $item['total'],
    //                 'vendor_id' => $vendor_id,
    //                 'purchase_status' => $request->status,
    //                 'payment_status' => 'unpaid',
    //                 'branch_id' => $userBranchId ?? $userId,
    //                 'created_by' => $$userId,
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Purchase invoice and purchase records created successfully!',
    //             'invoice_number' => $invoice_number,
    //             'purchase_id' => $purchaseInvoice->id, // ✅ Add this line
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function purchase_order(Request $request)
    {
        // dd($request->all());
        $user         = Auth::guard('api')->user();
        $userId       = $user->id;
        $userBranchId = $user->branch_id;
        $role         = $user->role;

        if ($role === 'staff' && $user->branch_id) {
            $userBranchId = $user->branch_id;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $userBranchId = $request->selectedSubAdminId;
        } else {
            $userBranchId = $user->id;
        }

        $validator = Validator::make($request->all(), [
            'vendor_id'              => 'required',
            'status'                 => 'required',
            'vendor_phone'           => 'nullable|numeric',
            'discount'               => 'nullable|numeric',
            'shipping'               => 'required|numeric',
            'grand_total'            => 'required|numeric',
            'products'               => 'required|array|min:1',
            'products.*.id'          => 'required',
            'products.*.category_id' => 'nullable',
            'products.*.price'       => 'required|numeric|min:0',
            'products.*.quantity'    => 'required|numeric|min:1',
            'products.*.total'       => 'required|numeric|min:0',
            'taxes'                  => 'nullable|array',
            'taxes.*.id'             => 'required|numeric',
            'taxes.*.name'           => 'required|string',
            'taxes.*.rate'           => 'required|numeric',
            'taxes.*.amount'         => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // ✅ Generate a unique invoice number
            do {
                $invoice_number = 'INV-' . mt_rand(10000000, 99999999);
            } while (PurchaseInvoice::where('invoice_number', $invoice_number)->exists());

            // ✅ Check and insert vendor
            if (! is_numeric($request->vendor_id)) {
                // Check if phone already exists
                $vendorPhone = $request->vendor_phone;

                if ($vendorPhone !== null && $vendorPhone !== '') {
                    // Check if phone already exists
                    $existingVendor = User::where('phone', $vendorPhone)->first();

                    if ($existingVendor) {
                        return response()->json([
                            'success' => false,
                            'message' => 'A vendor with this phone number already exists.',
                        ], 409);
                    }
                }
                $vendor = User::create([
                    'name'       => $request->vendor_id,
                    'phone'      => $request->vendor_phone ?? null,
                    'role'       => 'vendor',
                    'status'     => $request->status,
                    'branch_id'  => $userBranchId ?? $userId,
                    'created_by' => $userId,
                ]);
                $vendor_id = $vendor->id;

                // ✅ Create UserDetail record after user is created
                UserDetail::create([
                    'user_id'   => $vendor_id,
                    'address'   => '', // adjust if you collect address info
                    'city'      => '',
                    'state'     => '',
                    'country'   => '',
                    'pincode'   => '',
                    'branch_id' => $userBranchId ?? $userId,
                ]);
            } else {
                $vendor_id = $request->vendor_id;
            }

            // ✅ Process Products
            $processedProducts = [];
            foreach ($request->products as $product) {
                // if (! is_numeric($product['category_id'])) {
                //     $category    = Category::create(['name' => $product['category_id']]);
                //     $category_id = $category->id;
                // } else {
                //     $category_id = $product['category_id'];
                // }

                if (! is_numeric($product['id'])) {
                    do {
                        $sku = mt_rand(10000000, 99999999);
                    } while (Product::where('SKU', $sku)->exists());

                    $newProduct = Product::create([
                        'name'          => $product['id'],
                        // 'category_id'   => $category_id,
                        'price'         => $product['price'],
                        'quantity'      => $product['quantity'],
                        'quantity_type_id'      => $product['quantity_type_id'],
                        'vendor_id'     => $vendor_id,
                        'availablility' => 'in_stock',
                        'status'        => 'active',
                        'SKU'           => $sku,
                        'branch_id'     => $userBranchId ?? $userId,
                    ]);
                    $product_id = $newProduct->id;
                } else {
                    $existingProduct = Product::find($product['id']);
                    if ($existingProduct) {
                        $existingProduct->update([
                            // 'price'       => $product['price'],
                            'quantity'    => $existingProduct->quantity + $product['quantity'],
                            'quantity_type_id'      => $product['quantity_type_id'],
                            // 'category_id' => $category_id,
                        ]);
                        $product_id = $existingProduct->id;
                    }
                }

                $processedProducts[] = [
                    'product_id' => $product_id,
                    'price'      => $product['price'],
                    'quantity'   => $product['quantity'],
                    'quantity_type_id'      => $product['quantity_type_id'],
                    'total'      => $product['total'],
                ];
            }
            // Determine remaining amount
            $remainingAmount = 0;

            if (empty($request->payment_mode)) {
                // No payment made yet
                $remainingAmount = $request->grand_total;
            } elseif (($request->paid_type ?? 'full') === 'full') {
                // Full payment done → no remaining amount
                $remainingAmount = 0;
            } else {
                // Partial payment → remaining = grand_total - paid_amount
                $paidAmount = $request->amount
                    ?? (($request->cash_amount ?? 0) + ($request->upi_amount ?? 0));

                $remainingAmount = $request->grand_total - $paidAmount;
            }

            // dd($processedProducts);
            // ✅ Store purchase invoice
            $purchaseInvoice = PurchaseInvoice::create([
                'invoice_number'   => $invoice_number,
                'vendor_id'        => $vendor_id,
                'products'         => json_encode($processedProducts),
                'total_amount'     => collect($processedProducts)->sum('total'),
                'discount'         => $request->discount ?? 0,
                'shipping'         => $request->shipping,
                'grand_total'      => $request->grand_total,
                'remaining_amount' => $remainingAmount,
                'gst_option'       => $request->gst_option === 'with' ? 'with_gst' : 'without_gst',
                'status'           => $request->status,
                'taxes'            => json_encode($request->taxes),
                'branch_id'        => $userBranchId ?? $userId,
                'created_by'       => $userId,
                'bill_no'          => $request->bill_no,
            ]);
            // dd($purchaseInvoice);

            // ✅ Determine overall payment status dynamically
            $paymentStatus = 'unpaid';

            if (!empty($request->payment_mode)) {

                if (($request->paid_type ?? 'full') === 'full') {
                    $paymentStatus = 'paid';
                } else {
                    $paidAmount = $request->amount
                        ?? (($request->upi_amount ?? 0) + ($request->cash_amount ?? 0));

                    if ($paidAmount > 0 && $paidAmount < $request->grand_total) {
                        $paymentStatus = 'partial';
                    }
                }
            }
            // dd($paymentStatus);
            // ✅ Store each product in the purchases table
            foreach ($processedProducts as $item) {
                Purchases::create([
                    'invoice_id'      => $purchaseInvoice->id, // Link to purchase invoice
                    'item'            => $item['product_id'],
                    'quantity'        => $item['quantity'],
                    'quantity_type_id' => $item['quantity_type_id'],
                    'price'           => $item['price'],
                    'amount_total'    => $item['total'],
                    'vendor_id'       => $vendor_id,
                    'purchase_status' => $request->status,
                    'payment_status'  => $paymentStatus,
                    'branch_id'       => $userBranchId ?? $userId,
                    'created_by'      => $userId,
                    'bill_no'         => $request->bill_no,
                ]);
                $lastInventory = ProductInventory::where('product_id', $item['product_id'])
                    ->orderBy('id', 'desc')
                    ->first();

                ProductInventory::create([
                    'product_id'    => $item['product_id'],
                    'initial_stock' => $lastInventory->initial_stock ?? $item['quantity'],
                    'current_stock' => ($lastInventory->current_stock ?? 0) + $item['quantity'],
                    'branch_id'     => $userBranchId ?? $userId,
                    'create_by'     => $userId,
                    'type'          => 'Purchase',
                    'date'          => now(),
                ]);
            }
            // Payment details from request
            // if (! empty($request->payment_mode)) {
            //     PaymentStore::create([
            //         'user_id'          => $vendor_id,
            //         'purchase_id'      => $purchaseInvoice->id,
            //         'payment_amount'   => $request->amount ?? (($request->upi_amount ?? 0) + ($request->cash_amount ?? 0)),
            //         'payment_date'     => now(),
            //         'payment_method'   => $request->payment_mode,
            //         'payment_type'     => $request->paid_type ?? 'full',
            //         'cash_amount'      => $request->cash_amount ?? 0,
            //         'upi_amount'       => $request->upi_amount ?? 0,
            //         'remaining_amount' => $request->pending_amount ?? 0,
            //         'emi_month'        => null,
            //         'order_id'         => null,
            //         'jobcard_id'       => 0,
            //         'isDeleted'        => 0,
            //     ]);
            // }
            // ✅ Payment handling
            if (! empty($request->payment_mode)) {
                $paymentMode = strtolower($request->payment_mode);
                $paidType    = strtolower($request->paid_type ?? 'full');
                $cashAmount  = (float) ($request->cash_amount ?? 0);
                // $upiAmount   = (float) ($request->upi_amount ?? 0);
                $upiAmount  = (float) ($request->upi_amount ?? ($request->amount ?? 0)); // ✅ FIXED HERE
                $grandTotal = (float) ($request->grand_total ?? 0);

                // ✅ Calculate total paid and pending
                $totalPaid = $cashAmount + $upiAmount;
                $pending   = max(0, $grandTotal - $totalPaid); // prevents negative

                // ✅ CASE 1: Cash + Online (both)
                if ($paymentMode === 'cashonline') {
                    $payments = [];

                    // 1️⃣ Insert Cash Payment
                    if ($cashAmount > 0) {
                        $payments[] = PaymentStore::create([
                            'user_id'          => $vendor_id,
                            'purchase_id'      => $purchaseInvoice->id,
                            'payment_amount'   => $cashAmount,
                            'payment_date'     => now(),
                            'payment_method'   => 'Cash',
                            'payment_type'     => $paidType,
                            'cash_amount'      => $cashAmount,
                            'upi_amount'       => 0,
                            'remaining_amount' => $pending,
                            'emi_month'        => null,
                            'order_id'         => null,
                            'jobcard_id'       => 0,
                            'isDeleted'        => 0,
                        ]);
                    }

                    // 2️⃣ Insert Online Payment
                    if ($upiAmount > 0) {
                        $payments[] = PaymentStore::create([
                            'user_id'          => $vendor_id,
                            'purchase_id'      => $purchaseInvoice->id,
                            'payment_amount'   => $upiAmount,
                            'payment_date'     => now(),
                            'payment_method'   => 'Online',
                            'payment_type'     => $paidType,
                            'cash_amount'      => 0,
                            'upi_amount'       => $upiAmount,
                            'remaining_amount' => $pending,
                            'emi_month'        => null,
                            'order_id'         => null,
                            'jobcard_id'       => 0,
                            'isDeleted'        => 0,
                        ]);
                    }
                }

                // ✅ CASE 2: Cash Only
                elseif ($paymentMode === 'cash') {
                    PaymentStore::create([
                        'user_id'          => $vendor_id,
                        'purchase_id'      => $purchaseInvoice->id,
                        'payment_amount'   => $cashAmount ?: ($request->amount ?? 0),
                        'payment_date'     => now(),
                        'payment_method'   => 'Cash',
                        'payment_type'     => $paidType,
                        'cash_amount'      => $cashAmount ?: ($request->amount ?? 0),
                        'upi_amount'       => 0,
                        'remaining_amount' => $pending,
                        'emi_month'        => null,
                        'order_id'         => null,
                        'jobcard_id'       => 0,
                        'isDeleted'        => 0,
                    ]);
                }

                // ✅ CASE 3: Online Only
                elseif ($paymentMode === 'online') {
                    PaymentStore::create([
                        'user_id'          => $vendor_id,
                        'purchase_id'      => $purchaseInvoice->id,
                        'payment_amount'   => $upiAmount ?: ($request->amount ?? 0),
                        'payment_date'     => now(),
                        'payment_method'   => 'online',
                        'payment_type'     => $paidType,
                        'cash_amount'      => 0,
                        'upi_amount'       => $upiAmount ?: ($request->amount ?? 0),
                        'remaining_amount' => $pending,
                        'emi_month'        => null,
                        'order_id'         => null,
                        'jobcard_id'       => 0,
                        'isDeleted'        => 0,
                    ]);
                }

                // ✅ CASE 4: Pending (no immediate payment)
                elseif ($paymentMode === 'pending') {
                    PaymentStore::create([
                        'user_id'          => $vendor_id,
                        'purchase_id'      => $purchaseInvoice->id,
                        'payment_amount'   => 0,
                        'payment_date'     => now(),
                        'payment_method'   => 'Pending',
                        'payment_type'     => 'none',
                        'cash_amount'      => 0,
                        'upi_amount'       => 0,
                        'remaining_amount' => $request->grand_total ?? 0,
                        'emi_month'        => null,
                        'order_id'         => null,
                        'jobcard_id'       => 0,
                        'isDeleted'        => 0,
                    ]);
                }
            }
            // ✅ After payment handling and before DB::commit()
            if (! empty($request->payment_mode)) {
                $grandTotal = (float) ($request->grand_total ?? 0);
                $cashAmount = (float) ($request->cash_amount ?? 0);
                // $upiAmount   = (float) ($request->upi_amount ?? 0);
                $upiAmount = (float) ($request->upi_amount ?? ($request->amount ?? 0));
                $totalPaid = $cashAmount + $upiAmount;
                $remaining = max(0, $grandTotal - $totalPaid);

                // ✅ Update remaining_amount in purchase invoice
                $purchaseInvoice->update([
                    'remaining_amount' => $remaining,
                ]);
            }

            DB::commit();

            // Fetch latest payment info for this purchase
            $payment = PaymentStore::where('purchase_id', $purchaseInvoice->id)
                ->orderBy('id', 'desc')
                ->first();

            return response()->json([
                'success'        => true,
                'message'        => 'Purchase invoice and purchase records created successfully!',
                'invoice_number' => $invoice_number,
                'purchase_id'    => $purchaseInvoice->id, // ✅ Add this line
                'payment'        => $payment,             // <-- add payment info for frontend
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function purchase_list(Request $request)
    {
        $user = Auth::guard('api')->user();
        // dd($user);
        $branch_id = $user->id ?? null;

        if ($user->role === 'staff' && $user->id) {
            $branch_id = $user->id;
        } elseif ($user->role === 'sub-admin') {
            $branch_id = $user->id;
        } elseif ($user->role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $branch_id = (int) $request->selectedSubAdminId;
        }

        // if (!empty($request->selectedSubAdminId)) {
        //     $branch_id = $request->selectedSubAdminId;
        // }
        if ($user->role === 'staff') {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($branch_id) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.created_by', '=', $branch_id);
                });
        } else {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($branch_id) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.branch_id', '=', $branch_id);
                });
        }

        // ✅ Continue common joins + select for both cases
        $query = $query
            ->join('users', 'purchases.vendor_id', '=', 'users.id')
            ->join('products', 'purchases.item', '=', 'products.id')
            ->select(
                'purchase_invoice.id',
                'users.name as vendor_name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at as date',
                DB::raw("GROUP_CONCAT(products.name SEPARATOR ', ') as product_names"),
                DB::raw("GROUP_CONCAT(purchases.price SEPARATOR ', ') as product_prices"),
                DB::raw("GROUP_CONCAT(purchases.quantity SEPARATOR ', ') as product_quantities"),
                DB::raw("(SELECT COUNT(*) FROM payment_store
                  WHERE payment_store.purchase_id = purchase_invoice.id
                  AND payment_store.isDeleted = 0) as has_payment")
            )
            ->where('purchase_invoice.isDeleted', '=', 0);
        // ✅ Only non-deleted invoices

        if ($request->has('date') && ! empty($request->date)) {
            // 🟩 Only apply date filter — ignore month & year
            $query->whereDate('purchases.created_at', $request->date);
        } else {
            // 🟦 If date is not set, apply month & year
            if ($request->has('month') && ! empty($request->month)) {
                $query->whereMonth('purchases.created_at', $request->month);
            }

            if ($request->has('year') && ! empty($request->year)) {
                $query->whereYear('purchases.created_at', $request->year);
            }
        }

        $purchases = $query
            ->groupBy(
                'purchase_invoice.id',
                'users.name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at'
            )
            ->orderBy('purchase_invoice.id', 'desc')
            ->get();

        // After fetching $purchases (as a collection or array)
        // foreach ($purchases as $purchase) {
        //     $payment = \App\Models\PaymentStore::where('purchase_id', $purchase->id)
        //         ->orderBy('id', 'desc')
        //         ->first();
        //     $purchase->pending_amount = $payment ? $payment->remaining_amount : 0;
        // }

        // Fetch currency settings from DB
        $settings          = DB::table('settings')->first();
        $currencySymbolRaw = $settings->currency_symbol ?? '₹';
        $currencySymbol    = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $currencyPosition  = $settings->currency_position ?? 'left';

        return response()->json([
            'success'           => true,
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data'              => $purchases,
        ]);
    }

    public function showPurchase($id, Request $request)
    {
        // dd($request->all());
        $user = Auth::guard('api')->user();
        // dd($user);
        // $branch_id = $user->id;
        // if (! empty($request->selectedSubAdminId)) {
        //     $branch_id = $request->selectedSubAdminId;
        // }
        //  Determine branch_id based on role
        if ($user->role === 'staff') {
            // Staff can only access their own branch
            $branch_id = $user->branch_id;
        } elseif (! empty($request->selectedSubAdminId)) {
            // Admin viewing specific sub-admin’s branch
            $branch_id = $request->selectedSubAdminId;
            // dd($branch_id);
        } else {
            // Default to user's branch (for admin or superadmin)
            $branch_id = $user->branch_id ?? $user->id;
        }
        // Check invoice exists first
        $invoice = PurchaseInvoice::where('id', $id)
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found!',
            ], 404);
        }

        // dd($invoice);
        $rawPurchases = DB::table('purchases')
            ->where('invoice_id', $id)
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0)
            ->get();

        // dd('Raw purchases count:', $rawPurchases->count(), 'Raw purchases:', $rawPurchases);
        try {
            $purchase = DB::table('purchases')
                ->join('products', 'purchases.item', '=', 'products.id')
                ->join('quantity_types', 'purchases.quantity_type_id', '=', 'quantity_types.id')
                ->join('users', 'purchases.vendor_id', '=', 'users.id')
                ->join('purchase_invoice', 'purchases.invoice_id', '=', 'purchase_invoice.id')
                ->where('purchases.invoice_id', $id)
                ->where('purchases.branch_id', $branch_id)
                ->where('purchases.isDeleted', 0)
                ->select(
                    'purchases.vendor_id',
                    'purchases.payment_status',
                    'users.phone as vendor_phone',
                    'purchase_invoice.shipping',
                    'purchase_invoice.status',
                    'purchase_invoice.taxes',
                    'purchase_invoice.gst_option',
                    'purchase_invoice.grand_total',
                    DB::raw("GROUP_CONCAT(products.id ORDER BY products.id SEPARATOR ', ') as product_ids"),
                    DB::raw("GROUP_CONCAT(products.name ORDER BY products.id SEPARATOR ', ') as product_names"),
                    DB::raw("GROUP_CONCAT(products.price ORDER BY products.id SEPARATOR ', ') as product_prices"),
                    DB::raw("GROUP_CONCAT(purchases.quantity ORDER BY products.id SEPARATOR ', ') as product_quantities"),
                    DB::raw("GROUP_CONCAT(quantity_types.quantity_type ORDER BY products.id SEPARATOR ', ') as quantity_type_names"),
                    DB::raw("GROUP_CONCAT(quantity_types.id ORDER BY products.id SEPARATOR ', ') as quantity_type_ids"),
                    DB::raw("SUM(purchases.price * purchases.quantity) as total_amount")
                )
                ->groupBy(
                    'purchases.invoice_id',
                    'purchases.vendor_id',
                    'purchases.payment_status',
                    'users.phone',
                    'purchase_invoice.shipping',
                    'purchase_invoice.status',
                    'purchase_invoice.taxes',
                    'purchase_invoice.gst_option',
                    'purchase_invoice.grand_total'
                )
                ->first();
            // dd($purchase);

            $invoice = PurchaseInvoice::where('id', $id)
                ->where('branch_id', $branch_id)
                ->where('isDeleted', 0)
                ->first();

            $vendor = User::find($invoice->vendor_id);

            $productImages = explode(',', $purchase->product_images ?? '');
            $basePath      = env('ImagePath', '/');

            $productImageUrls = [];

            if (! empty($productImages)) {
                $productImageUrls = array_map(function ($img) use ($basePath) {
                    // Remove extra brackets, quotes, or spaces
                    $img = trim($img, " []\"'");
                    return url(rtrim($basePath, '/') . '/storage/' . ltrim($img, '/'));
                }, $productImages);

                // Remove any empty values
                $productImageUrls = array_filter($productImageUrls);
            }

            if (empty($productImageUrls)) {
                $productImageUrls = [url(rtrim($basePath, '/') . '/admin/assets/img/product/noimage.png')];
            }

            // dd($productImageUrls);
            if (! $purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase not found',
                ], 404);
            }

            // ✅ Decode taxes from JSON format
            $taxes    = json_decode($purchase->taxes, true) ?? [];
            $settings = DB::table('settings')->where('branch_id', $branch_id)->first();

            return response()->json([
                'success'            => true,
                'data'               => $purchase,
                'taxes'              => $taxes, // ✅ Send taxes to the frontend
                'companyInfo'        => $settings,
                'invoice'            => $invoice,
                'vendor'             => $vendor,
                'product_image_urls' => array_values($productImageUrls),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function purchase_invoice_pdf_download($id)
    {
        dd($id);
        $authUser   = Auth::guard('api')->user();
        $subAdminId = session('selectedSubAdminId') ?? $authUser->id;
        $setting    = Setting::where('branch_id', $subAdminId)->first();
        $invoice    = PurchaseInvoice::find($id);

        if (! $invoice) {
            return response()->json([
                'status'  => false,
                'message' => 'Purchase invoice not found.',
            ], 404);
        }

        // Vendor info
        $vendor = $invoice->vendor ?? [
            'name'    => 'Unknown Vendor',
            'email'   => '',
            'phone'   => '',
            'address' => '',
        ];

        // Purchase items
        $purchaseItems = Purchases::where('invoice_id', $invoice->id)->get();
        $paymentStatus = $purchaseItems->first()->payment_status ?? 'Pending';
        $paymentMethod = $purchaseItems->first()->payment_method ?? 'N/A';

        // Subtotal
        $subtotal = $purchaseItems->sum(fn($item) => $item->price * $item->quantity);

        // Discount
        $discountPercent = $invoice->discount ?? 0;
        $discountAmount  = ($discountPercent / 100) * $subtotal;
        $afterDiscount   = $subtotal - $discountAmount;

        // Shipping
        $shipping = $invoice->shipping ?? 0;

        // Taxes
        $taxRates   = json_decode($invoice->taxes, true) ?? [];
        $taxDetails = [];
        foreach ($taxRates as $tax) {
            $taxAmount    = ($tax['rate'] / 100) * $afterDiscount;
            $taxDetails[] = [
                'name'   => $tax['name'],
                'rate'   => $tax['rate'],
                'amount' => $taxAmount,
            ];
        }

        // Grand Total
        $grandTotal = $afterDiscount + $shipping + collect($taxDetails)->sum('amount');

        // Format currency
        $formatCurrency = fn($amt) => $setting->currency_position === 'right'
            ? number_format($amt, 2) . $setting->currency_symbol
            : $setting->currency_symbol . number_format($amt, 2);

        // Prepare data for PDF
        $pdfData = [
            'invoice'        => $invoice,
            'vendor'         => $vendor,
            'purchaseItems'  => $purchaseItems,
            'setting'        => $setting,
            'subtotal'       => $formatCurrency($subtotal),
            'discount'       => $invoice->discount,
            'discountAmount' => $formatCurrency($discountAmount),
            'afterDiscount'  => $formatCurrency($afterDiscount),
            'shipping'       => $formatCurrency($shipping),
            'taxDetails'     => $taxDetails,
            'grandTotal'     => $formatCurrency($grandTotal),
            'payment_status' => ucfirst($paymentStatus),
            'payment_method' => ucfirst($paymentMethod),
        ];

        // Generate PDF
        $pdf = PDF::loadView('purchase.purchase-invoice-pdf', $pdfData);

        // Save PDF to storage
        $fileName = 'purchase_invoice_' . $id . '.pdf';
        // $filePath = 'public/storage/purchase-invoices/' . $fileName;

        // if (! file_exists(storage_path('app/public/purchase-invoices'))) {
        //     mkdir(storage_path('app/public/purchase-invoices'), 0777, true);
        // }
        $relativePath = 'purchase-invoices/' . $fileName;

        \Storage::disk('public')->put($relativePath, $pdf->output());

        // Generate public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        // Return JSON response
        return response()->json([
            'status'    => true,
            'message'   => 'Purchase Invoice PDF generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function purchase_update(Request $request, $invoice_id)
    {
        $user         = Auth::guard('api')->user();
        $userId       = $user->id;
        $userBranchId = $user->branch_id;
        $role         = $user->role;

        if ($role === 'staff' && $user->branch_id) {
            $userBranchId = $user->branch_id;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $userBranchId = $request->selectedSubAdminId;
        } else {
            $userBranchId = $user->id;
        }
        $validator = Validator::make($request->all(), [
            'vendor_id'              => 'required',
            'status'                 => 'required',
            'payment_status'         => 'nullable',
            'vendor_phone'           => 'nullable|numeric',
            'discount'               => 'nullable|numeric',
            'shipping'               => 'required|numeric',
            'grand_total'            => 'required|numeric',
            'taxes'                  => 'nullable|array',
            'taxes.*.id'             => 'nullable',
            'taxes.*.rate'           => 'nullable|numeric|min:0',
            'products'               => 'required|array|min:1',
            'products.*.id'          => 'required',
            'products.*.category_id' => 'nullable',
            'products.*.price'       => 'required|numeric|min:0',
            'products.*.quantity_type_id'  => 'required|numeric',
            'products.*.quantity'    => 'required|numeric|min:1',
            'products.*.total'       => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseInvoice = PurchaseInvoice::where('id', $invoice_id)->first();
            if (! $purchaseInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase invoice not found',
                ], 404);
            }

            $vendor_id     = $request->vendor_id;
            $oldPurchases  = Purchases::where('invoice_id', $invoice_id)->get();
            $oldQuantities = $oldPurchases->pluck('quantity', 'item')->toArray();

            $processedProducts = [];
            foreach ($request->products as $product) {
                // Handle Category
                // if (! is_numeric($product['category_id'])) {
                //     $category    = Category::create(['name' => $product['category_id']]);
                //     $category_id = $category->id;
                // } else {
                //     $category_id = $product['category_id'];
                // }

                // Handle Product
                if (! is_numeric($product['id'])) {
                    do {
                        $sku = mt_rand(10000000, 99999999);
                    } while (Product::where('SKU', $sku)->exists());

                    $newProduct = Product::create([
                        'name'          => $product['id'],
                        // 'category_id'   => $category_id,
                        'price'         => $product['price'],
                        'quantity'      => $product['quantity'],
                        'vendor_id'     => $vendor_id,
                        'availablility' => 'in_stock',
                        'status'        => 'active',
                        'SKU'           => $sku,
                    ]);

                    $product_id = $newProduct->id;
                } else {
                    $existingProduct = Product::find($product['id']);
                    if ($existingProduct) {
                        $oldQuantity    = $oldQuantities[$product['id']] ?? 0;
                        $newQuantity    = $product['quantity'];
                        $quantityChange = $newQuantity - $oldQuantity;

                        if ($quantityChange > 0) {
                            $existingProduct->increment('quantity', $quantityChange);
                        } elseif ($quantityChange < 0) {
                            $existingProduct->decrement('quantity', abs($quantityChange));
                        }

                        $existingProduct->update([
                            'price'       => $product['price'],
                            // 'category_id' => $category_id,
                        ]);

                        $product_id    = $existingProduct->id;
                        $lastInventory = ProductInventory::where('product_id', $product_id)
                            ->orderBy('id', 'desc')
                            ->first();

                        ProductInventory::create([
                            'product_id'    => $product_id,
                            'initial_stock' => $lastInventory->current_stock,
                            'current_stock' => $lastInventory->current_stock + $quantityChange,
                            'branch_id'     => $purchaseInvoice->branch_id,
                            'create_by'     => auth()->id(),
                            'type'          => 'Purchase Update',
                            'date'          => now(),
                        ]);
                    }
                }

                Purchases::updateOrCreate(
                    ['invoice_id' => $invoice_id, 'item' => $product_id],
                    [
                        'quantity'        => $product['quantity'],
                        'quantity_type_id' => $product['quantity_type_id'],
                        'price'           => $product['price'],
                        'amount_total'    => $product['total'],
                        'vendor_id'       => $vendor_id,
                        'purchase_status' => $request->status,
                        'payment_status'  => 'Unpaid',
                        'branch_id'       => $userBranchId ?? $userId,
                        'created_by'      => $userId,
                    ]
                );

                // dd($processedProducts);
                $processedProducts[] = [
                    'product_id' => $product_id,
                    'price'      => $product['price'],
                    'quantity'   => $product['quantity'],
                    'total'      => $product['total'],
                ];
            }

            // Handle Taxes (safely if nullable)
            $totalTaxAmount = 0;
            $taxData        = [];
            if ($request->gst_option === 'with_gst') {
                if ($request->has('taxes') && is_array($request->taxes)) {
                    foreach ($request->taxes as $tax) {
                        $rate      = $tax['rate'] ?? 0;
                        $taxAmount = (collect($processedProducts)->sum('total') * $rate) / 100;
                        $totalTaxAmount += $taxAmount;
                        $taxData[] = [
                            'id'     => $tax['id'] ?? null, // keep ID
                            'name'   => $tax['name'] ?? '', // ✅ add tax name
                            'rate'   => $rate,
                            'amount' => $taxAmount,
                        ];
                    }
                }
            } else {
                $taxData        = [];
                $totalTaxAmount = 0;
            }
            // dd($request->all());
            $totalAmount = collect($processedProducts)->sum('total');
            $discount    = $request->discount ?? 0;
            $shipping    = $request->shipping;
            $grandTotal  = $totalAmount - $discount + $shipping + $totalTaxAmount;

            // keep already paid value
            $alreadyPaid = $purchaseInvoice->paid ?? 0;

            // recalc remaining
            $remaining = max($grandTotal - $alreadyPaid, 0);

            $purchaseInvoice->update([
                'products'         => json_encode($processedProducts),
                'total_amount'     => $totalAmount,
                'discount'         => $discount,
                'shipping'         => $shipping,
                'taxes'            => json_encode($taxData),
                'gst_option'       => $request->gst_option === 'with_gst' ? 'with_gst' : 'without_gst',
                'grand_total'      => $grandTotal,
                'remaining_amount' => $remaining,
                'status'           => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function fetch_purchase_report(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     $role = $user->role;
    //     $userBranchId = $user->branch_id;
    //     $userId = $user->id;
    //     if ($role === 'staff' && $userBranchId) {
    //         $branchIdToUse = $userBranchId;
    //     } elseif ($role === 'admin' && !empty($request->selectedSubAdminId)) {
    //         $branchIdToUse = $request->selectedSubAdminId;
    //     } else {
    //         $branchIdToUse = $user->id;
    //     }

    //     $query = Purchases::with(['vendor', 'product'])
    //         ->where('purchase_status', 'completed')
    //         ->where('branch_id', $branchIdToUse)
    //         ->where('isDeleted', 0);

    //     // Month / Year filter
    //     $monthParam = $request->month;
    //     $yearParam = $request->year;

    //     if (!empty($monthParam)) {
    //         $query->whereMonth('created_at', (int) $monthParam);
    //     }
    //     if (!empty($yearParam)) {
    //         $query->whereYear('created_at', (int) $yearParam);
    //     }

    //     // Date / Preset filter (only if month/year not provided)
    //     if (empty($monthParam) && empty($yearParam)) {
    //         $now = Carbon::now();
    //         switch ($request->filter) {
    //             case 'this_week':
    //                 $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
    //                 break;
    //             case 'this_month':
    //                 $query->whereMonth('created_at', $now->month)
    //                     ->whereYear('created_at', $now->year);
    //                 break;
    //             case 'last_6_months':
    //                 $query->whereBetween('created_at', [$now->copy()->subMonths(6)->startOfMonth(), $now->endOfMonth()]);
    //                 break;
    //             case 'this_year':
    //                 $query->whereYear('created_at', $now->year);
    //                 break;
    //             case 'previous_year':
    //                 $query->whereYear('created_at', $now->year - 1);
    //                 break;
    //             default:
    //                 if ($request->from_date && $request->to_date) {
    //                     $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
    //                 }
    //                 break;
    //         }
    //     }

    //     // Optional vendor filter
    //     if ($request->filled('vendor_id')) {
    //         $vendorId = (int) $request->vendor_id;
    //         $query->where('vendor_id', $vendorId);
    //     }

    //     $purchases = $query->latest()->get();

    //     // Process purchases for extra fields
    //     $processedPurchases = $purchases->map(function ($purchase) {
    //         $cgstAmount = 0;
    //         $sgstAmount = 0;
    //         $discount = 0;
    //         $shipping = 0;
    //         $grandTotal = 0;

    //         if (!empty($purchase->invoice_id)) {
    //             try {
    //                 $invoiceId = is_numeric($purchase->invoice_id) ? (int)$purchase->invoice_id : $purchase->invoice_id;
    //                 $invoice = PurchaseInvoice::find($invoiceId);
    //                 if ($invoice) {
    //                     $discount = $invoice->discount ?? 0;
    //                     $shipping = $invoice->shipping ?? 0;
    //                     $grandTotal = $invoice->grand_total ?? 0;

    //                     if ($invoice->taxes) {
    //                         $taxes = json_decode($invoice->taxes, true);
    //                         if (is_array($taxes)) {
    //                             foreach ($taxes as $tax) {
    //                                 if (isset($tax['name'], $tax['amount'])) {
    //                                     if (strtoupper($tax['name']) === 'CGST') {
    //                                         $cgstAmount = $tax['amount'];
    //                                     } elseif (strtoupper($tax['name']) === 'SGST') {
    //                                         $sgstAmount = $tax['amount'];
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             } catch (\Exception $e) {
    //                 Log::error('Error processing invoice for purchase ' . $purchase->id . ': ' . $e->getMessage());
    //             }
    //         }

    //         // Attach extra fields
    //         $purchase->cgst_amount = $cgstAmount;
    //         $purchase->sgst_amount = $sgstAmount;
    //         $purchase->discount = $discount;
    //         $purchase->shipping = $shipping;
    //         $purchase->grand_total = $grandTotal;

    //         return $purchase;
    //     });

    //     // Get currency settings
    //     $settings = DB::table('settings')->where('branch_id', $branchIdToUse)->first();
    //     $currencySymbol = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     return response()->json([
    //         'purchases' => $processedPurchases,
    //         'currencySymbol' => $currencySymbol,
    //         'currencyPosition' => $currencyPosition,
    //     ]);
    // }

    public function fetch_purchase_report(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->branch_id;
        $userId       = $user->id;
        if ($role === 'staff' && $userBranchId) {
            $branchIdToUse = $userBranchId;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $branchIdToUse = $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        $query = Purchases::with(['vendor', 'product', 'product.quantityType'])
            ->where('purchase_status', 'completed')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0);

        // 🔹 If staff, filter using created_by (from PurchaseInvoice)
        if ($role === 'staff') {
            $query->whereHas('invoice', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        } else {
            $query->where('branch_id', $branchIdToUse);
        }

        // Month / Year filter
        $monthParam = $request->month;
        $yearParam  = $request->year;

        if (! empty($monthParam)) {
            $query->whereMonth('created_at', (int) $monthParam);
        }
        if (! empty($yearParam)) {
            $query->whereYear('created_at', (int) $yearParam);
        }

        // Date / Preset filter (only if month/year not provided)
        if (empty($monthParam) && empty($yearParam)) {
            $now = Carbon::now();
            switch ($request->filter) {
                case 'this_week':
                    $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);
                    break;
                case 'last_6_months':
                    $query->whereBetween('created_at', [$now->copy()->subMonths(6)->startOfMonth(), $now->endOfMonth()]);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $now->year);
                    break;
                case 'previous_year':
                    $query->whereYear('created_at', $now->year - 1);
                    break;
                default:
                    if ($request->from_date && $request->to_date) {
                        $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
                    }
                    break;
            }
        }

        // Optional vendor filter
        if ($request->filled('vendor_id')) {
            $vendorId = (int) $request->vendor_id;
            $query->where('vendor_id', $vendorId);
        }

        if ($request->filled('category_id')) {
            $categoryId = (int) $request->category_id;
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $purchases = $query->latest()->get();
        $purchases->map(function ($item) {
            $item->quantity_type = $item->product->quantityType->quantity_type ?? null;
            return $item;
        });
        // dd($purchases);

        // Process purchases for extra fields
        $processedPurchases = $purchases->map(function ($purchase) {
            $cgstAmount = 0;
            $sgstAmount = 0;
            $discount   = 0;
            $shipping   = 0;
            $grandTotal = 0;

            if (! empty($purchase->invoice_id)) {
                try {
                    $invoiceId = is_numeric($purchase->invoice_id) ? (int) $purchase->invoice_id : $purchase->invoice_id;
                    $invoice   = PurchaseInvoice::find($invoiceId);
                    if ($invoice) {
                        $discount   = $invoice->discount ?? 0;
                        $shipping   = $invoice->shipping ?? 0;
                        $grandTotal = $invoice->grand_total ?? 0;

                        if ($invoice->taxes) {
                            $taxes = json_decode($invoice->taxes, true);
                            if (is_array($taxes)) {
                                foreach ($taxes as $tax) {
                                    if (isset($tax['name'], $tax['amount'])) {
                                        if (strtoupper($tax['name']) === 'CGST') {
                                            $cgstAmount = $tax['amount'];
                                        } elseif (strtoupper($tax['name']) === 'SGST') {
                                            $sgstAmount = $tax['amount'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing invoice for purchase ' . $purchase->id . ': ' . $e->getMessage());
                }
            }

            // Attach extra fields
            $purchase->cgst_amount = $cgstAmount;
            $purchase->sgst_amount = $sgstAmount;
            $purchase->discount    = $discount;
            $purchase->shipping    = $shipping;
            $purchase->grand_total = $grandTotal;

            return $purchase;
        });

        // Get currency settings
        $settings         = DB::table('settings')->where('branch_id', $branchIdToUse)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';
        // dd($processedPurchases);
        return response()->json([
            'status'           => true,
            'data'             => $processedPurchases,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
        ]);
    }

    public function export_purchase_excel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'purchase_ids'   => 'required|array|min:1',
                'purchase_ids.*' => 'required|integer|exists:purchases,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $purchases = Purchases::with(['vendor', 'product'])
                ->whereIn('id', $request->purchase_ids)
                ->where('purchase_status', 'completed')
                ->where('isDeleted', 0)
                ->get();

            if ($purchases->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid purchases found for export',
                ], 404);
            }

            // Process purchases to add CGST, SGST, and other fields
            $processedPurchases = $purchases->map(function ($purchase) {
                $cgstAmount = 0;
                $sgstAmount = 0;
                $discount   = 0;
                $shipping   = 0;
                $grandTotal = 0;

                // Try to get invoice data directly if relationship fails
                if ($purchase->invoice_id && ! empty($purchase->invoice_id)) {
                    try {
                        // Convert to integer if it's a string
                        $invoiceId = is_numeric($purchase->invoice_id) ? (int) $purchase->invoice_id : $purchase->invoice_id;
                        $invoice   = PurchaseInvoice::find($invoiceId);
                        if ($invoice) {
                            $discount   = $invoice->discount ?? 0;
                            $shipping   = $invoice->shipping ?? 0;
                            $grandTotal = $invoice->grand_total ?? 0;

                            // Parse taxes JSON to get CGST and SGST amounts
                            if ($invoice->taxes) {
                                $taxes = json_decode($invoice->taxes, true);
                                if (is_array($taxes)) {
                                    foreach ($taxes as $tax) {
                                        if (isset($tax['name']) && isset($tax['amount'])) {
                                            if (strtoupper($tax['name']) === 'CGST') {
                                                $cgstAmount = $tax['amount'];
                                            } elseif (strtoupper($tax['name']) === 'SGST') {
                                                $sgstAmount = $tax['amount'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing invoice for purchase ' . $purchase->id . ': ' . $e->getMessage());
                    }
                }

                // Add the new fields to the purchase object
                $purchase->cgst_amount = $cgstAmount;
                $purchase->sgst_amount = $sgstAmount;
                $purchase->discount    = $discount;
                $purchase->shipping    = $shipping;
                $purchase->grand_total = $grandTotal;

                return $purchase;
            });

            // Get currency settings
            $settings          = DB::table('settings')->first();
            $currencySymbolRaw = $settings->currency_symbol ?? '₹';
            // Decode HTML entities and trim
            $currencySymbol = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            // Fix common mojibake cases (UTF-8 bytes read as Windows-1252)
            $currencySymbol = strtr($currencySymbol, [
                'â‚¬' => '€', // euro
                'Â£'  => '£',    // pound
                'â‚¹' => '₹', // rupee
                'Â¥'  => '¥',    // yen
            ]);
            // As a fallback, try interpreting original as Windows-1252 and converting to UTF-8
            if (! preg_match('/[€£₹¥$]/u', $currencySymbol)) {
                $maybeUtf8 = @mb_convert_encoding($currencySymbolRaw, 'UTF-8', 'Windows-1252');
                if ($maybeUtf8 && preg_match('/[€£₹¥$]/u', $maybeUtf8)) {
                    $currencySymbol = $maybeUtf8;
                }
            }
            $currencyPosition = $settings->currency_position ?? 'left';

            // Map common currency symbols to Unicode codepoints for robust Excel rendering via UNICHAR()
            $symbolToCodepoint = [
                '€' => 8364,
                '£' => 163,
                '₹' => 8377,
                '¥' => 165,
                '$' => 36,
            ];
            $currencyCodepoint = $symbolToCodepoint[$currencySymbol] ?? null;

            // Generate CSV file
            $filename = 'purchase_report_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/public/exports/' . $filename);

            // Create exports directory if it doesn't exist
            if (! file_exists(storage_path('app/public/exports'))) {
                mkdir(storage_path('app/public/exports'), 0755, true);
            }

            // Create CSV file (UTF-16LE with BOM; include delimiter hint for Excel)
            $file = fopen($filepath, 'w');
            // Write UTF-16LE BOM and delimiter hint (converted)
            fwrite($file, "\xFF\xFE");
            fwrite($file, mb_convert_encoding("sep=,\r\n", 'UTF-16LE', 'UTF-8'));

            // Set headers
            $headers = [
                'Product Name',
                'Vendor Name',
                'Currency',
                'Purchased Amount',
                'Purchased QTY',
                'CGST',
                'SGST',
                'Discount',
                'Shipping',
                'Grand Total',
                'Purchase Date',
            ];

            // Write header row (converted)
            $escapedHeaders = array_map(function ($v) {
                $v = (string) $v;
                return '"' . str_replace('"', '""', $v) . '"';
            }, $headers);
            $headerLine = implode(',', $escapedHeaders) . "\r\n";
            fwrite($file, mb_convert_encoding($headerLine, 'UTF-16LE', 'UTF-8'));

            // Add data rows
            foreach ($processedPurchases as $purchase) {
                // Numeric values (no thousands separator) so Excel parses as numbers
                $amountFormatted     = number_format((float) $purchase->amount_total, 2, '.', '');
                $cgstFormatted       = number_format((float) $purchase->cgst_amount, 2, '.', '');
                $sgstFormatted       = number_format((float) $purchase->sgst_amount, 2, '.', '');
                $discountFormatted   = number_format((float) $purchase->discount, 2, '.', '');
                $shippingFormatted   = number_format((float) $purchase->shipping, 2, '.', '');
                $grandTotalFormatted = number_format((float) $purchase->grand_total, 2, '.', '');

                $row = [
                    $purchase->product ? $purchase->product->name : 'N/A',
                    $purchase->vendor ? $purchase->vendor->name : 'N/A',
                    $currencySymbol,
                    $amountFormatted,
                    (string) $purchase->quantity,
                    $cgstFormatted,
                    $sgstFormatted,
                    $discountFormatted,
                    $shippingFormatted,
                    $grandTotalFormatted,
                    $purchase->created_at ? $purchase->created_at->format('Y-m-d H:i:s') : '',
                ];
                // Escape and write row (converted)
                $escaped = array_map(function ($v) {
                    $v = (string) $v;
                    return '"' . str_replace('"', '""', $v) . '"';
                }, $row);
                $line = implode(',', $escaped) . "\r\n";
                fwrite($file, mb_convert_encoding($line, 'UTF-16LE', 'UTF-8'));
            }

            // Close CSV file
            fclose($file);

            // Generate download URL
            $downloadUrl = url('storage/exports/' . $filename);

            return response()->json([
                'success'      => true,
                'message'      => 'CSV file generated successfully',
                'filename'     => $filename,
                'download_url' => $downloadUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting purchase CSV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate CSV file: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Delete purchase order
    public function purchase_delete(Request $request)
    {
        try {
            DB::beginTransaction(); // Start transaction

            // Find the purchase invoice
            $purchase = PurchaseInvoice::find($request->id);
            if (! $purchase) {
                return response()->json(['status' => false, 'message' => 'Purchase order not found.'], 404);
            }

            // Check if all related purchases have status 'completed'
            $nonCompletedCount = Purchases::where('invoice_id', $request->id)
                ->where('purchase_status', '!=', 'completed')
                ->where('isDeleted', 0)
                ->count();

            if ($nonCompletedCount > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete. This purchases are not completed.',
                ], 422);
            }

            // Soft delete purchases
            Purchases::where('invoice_id', $request->id)
                ->where('purchase_status', 'completed')
                ->update(['isDeleted' => 1]);

            // Soft delete the purchase invoice
            $purchase->isDeleted = 1;
            $purchase->save();

            DB::commit(); // Commit transaction

            return response()->json([
                'status' => true,
                'message' => 'Purchase order deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Purchases::with(['vendor', 'product']);

        // Filters
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }

        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $purchases = $query->latest()->get();
        $vendors   = \App\Models\User::where('role', 'vendor')->get();

        return view('purchase.purchasereport', compact('purchases', 'vendors'));
    }

    public function getHistory1($job_card_id)
    {
        $history = PaymentStore::where('purchase_id', $job_card_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $history,
        ]);
    }

    // public function make_payment(Request $request)
    // {

    //     $user_id = Auth::guard('api')->user()->id;

    //     $request->validate([
    //         'purchase_id'    => 'nullable|integer',
    //         'payment_amount' => 'nullable|numeric',
    //         'payment_date'   => 'nullable|date',
    //         'payment_type'   => 'nullable|string',
    //         'emi_month'      => 'nullable|integer',
    //         'pending_date'   => 'nullable|date',
    //         'new_emi_value'  => 'nullable',
    //         'emi_paid_value' => 'nullable|numeric',
    //     ]);

    //     if ($request->filled('purchase_id')) {
    //         // Determine payment amount
    //         $paymentAmount = $request->emi_total_new ?? $request->emi_total ?? $request->amount ?? $request->upi_online_amount ?? 0;

    //         if ($request->filled('cash_amount') && $request->filled('online_amount')) {
    //             $paymentAmount = (float) $request->cash_amount + (float) $request->online_amount;
    //         } elseif ($request->filled('fully_cash_amount') && $request->filled('full_online_amount')) {
    //             $paymentAmount = (float) $request->fully_cash_amount + (float) $request->full_online_amount;
    //         } elseif ($request->cashAmount) {
    //             $paymentAmount = $request->cashAmount;
    //         } elseif ($request->upi_online_amount) {
    //             $paymentAmount = $request->upi_online_amount;
    //         } elseif ($request->emi_monthly) {
    //             $paymentAmount = $request->emi_monthly;
    //         }

    //         // Determine payment type
    //         if (
    //             in_array($request->paid_type, ['cash_partially']) ||
    //             in_array($request->online_type, ['online_partially']) ||
    //             in_array($request->cash_online_type, ['cash_online_partially'])
    //         ) {
    //             $type = 'partially';
    //         } elseif (
    //             in_array($request->paid_type, ['cash_fully']) ||
    //             in_array($request->online_type, ['online_fully']) ||
    //             in_array($request->cash_online_type, ['cash_online_fully']) ||
    //             $request->payment_type === 'fully'

    //         ) {
    //             $type = 'fully';
    //         } elseif (
    //             in_array($request->emi_type, ['emi'])
    //         ) {
    //             $type = 'emi';
    //         } else {
    //             $type = 'fully';
    //         }

    //         // Fetch JobCard if exists

    //         $jobCard          = $request->purchase_id ? PurchaseInvoice::find($request->purchase_id) : null;
    //         $currentRemaining = $jobCard->remaining_amount ?? 0;
    //         $newRemaining     = max(0, $currentRemaining - $paymentAmount);

    //         // Create payment

    //         $payment = PaymentStore::create([
    //             'user_id'          => $user_id,
    //             'jobcard_id'       => 0,
    //             'purchase_id'      => $request->purchase_id,
    //             'payment_amount'   => $paymentAmount,
    //             'remaining_amount' => $newRemaining,
    //             'payment_method'   => $request->payment_method ?? $request->payment_type ?? '',
    //             'payment_date'     => \Carbon\Carbon::today(),
    //             'payment_type'     => $type,
    //             'cash_amount'      => $request->cash_amount,
    //             'upi_amount'       => $request->online_amount,
    //             'emi_month'        => $request->emi_month ?? 1,
    //             'isDeleted'        => 0,
    //         ]);

    //         // Update JobCard
    //         if ($jobCard) {
    //             $updateData = ['remaining_amount' => $newRemaining];

    //             // If new EMI is being set
    //             if ($request->filled('new_emi_value') && $request->payment_method == 'emi' || $request->filled('emi_paid_value')) {
    //                 //  dd($request->all());
    //                 $updateData['payment_method'] = 'EMI';
    //                 $updateData['emi_duration']   = $request->emi_month_new ?? $request->emi_month;
    //                 $updateData['emi_months']     = $request->emi_total_new ?? 1;
    //             } else {
    //                 // dd($request->filled('emi_paid_value'));
    //                 // Otherwise, keep the actual payment method (cash, online, cash_online)
    //                 $method = $request->payment_method ?? $request->payment_type ?? $updateData['payment_method'] ?? 'Cash';

    //                 // Normalize to proper values
    //                 if (strtolower($method) === 'online' || strtolower($method) === 'upi') {
    //                     $method = 'Online';
    //                 } elseif (strtolower($method) === 'cash') {
    //                     $method = 'Cash';
    //                 } elseif (strtolower($method) === 'emi') {
    //                     $method = 'EMI';
    //                 } else {
    //                     $method = 'Cash';
    //                 }

    //                 $updateData['payment_method'] = $method;
    //             }

    //             if ($request->emi_duration) {
    //                 $updateData['emi_duration'] = $request->emi_duration;
    //             }

    //             // Update next pending date if provided
    //             if ($request->pending_date) {
    //                 $updateData['next_pending_date'] = $request->pending_date;
    //             }

    //             $jobCard->update($updateData);

    //             // ✅ Update invoice items dynamically
    //             if ($jobCard) {
    //                 // Update remaining in PurchaseInvoice
    //                 $jobCard->update([
    //                     'remaining_amount' => $newRemaining,
    //                     'updated_at'       => now(),
    //                 ]);

    //                 // Determine payment status
    //                 $paymentStatus  = 'unpaid';
    //                 $purchaseStatus = 'pending';

    //                 if ($newRemaining <= 0) {
    //                     $paymentStatus  = 'paid';
    //                     $purchaseStatus = 'completed';
    //                 } elseif ($newRemaining > 0 && $newRemaining < ($jobCard->grand_total ?? 0)) {
    //                     $paymentStatus = 'partial';
    //                 }

    //                 // Update statuses in Purchases table
    //                 Purchases::where('invoice_id', $jobCard->id)->update([
    //                     'payment_status'  => $paymentStatus,
    //                     'purchase_status' => $purchaseStatus,
    //                     'updated_at'      => now(),
    //                 ]);
    //             }
    //         }
    //     }
    //     return response()->json([
    //         'status'  => 'success',
    //         'message' => 'Payment submitted successfully.',
    //         'data'    => $payment,
    //     ]);
    // }
    public function make_payment(Request $request)
    {
        $user_id = Auth::guard('api')->user()->id;

        $request->validate([
            'purchase_id'    => 'nullable|integer',
            'payment_amount' => 'nullable|numeric',
            'payment_date'   => 'nullable|date',
            'payment_type'   => 'nullable|string',
            'emi_month'      => 'nullable|integer',
            'pending_date'   => 'nullable|date',
            'new_emi_value'  => 'nullable',
            'emi_paid_value' => 'nullable|numeric',
        ]);

        if ($request->filled('purchase_id')) {
            // ✅ Find purchase (JobCard)
            $jobCard = PurchaseInvoice::find($request->purchase_id);
            if (! $jobCard) {
                return response()->json(['status' => 'error', 'message' => 'Purchase not found'], 404);
            }

            $currentRemaining = $jobCard->remaining_amount ?? 0;

            // ✅ Calculate total payment amount
            $paymentAmount = $request->emi_total_new ?? $request->emi_total ?? $request->amount ?? $request->upi_online_amount ?? 0;

            if ($request->filled('cash_amount') && $request->filled('online_amount')) {
                $paymentAmount = (float) $request->cash_amount + (float) $request->online_amount;
            } elseif ($request->filled('fully_cash_amount') && $request->filled('full_online_amount')) {
                $paymentAmount = (float) $request->fully_cash_amount + (float) $request->full_online_amount;
            } elseif ($request->cashAmount) {
                $paymentAmount = $request->cashAmount;
            } elseif ($request->upi_online_amount) {
                $paymentAmount = $request->upi_online_amount;
            } elseif ($request->emi_monthly) {
                $paymentAmount = $request->emi_monthly;
            }

            $newRemaining = max(0, $currentRemaining - $paymentAmount);

            // ✅ Determine type
            if (
                in_array($request->paid_type, ['cash_partially']) ||
                in_array($request->online_type, ['online_partially']) ||
                in_array($request->cash_online_type, ['cash_online_partially'])
            ) {
                $type = 'partially';
            } elseif (
                in_array($request->paid_type, ['cash_fully']) ||
                in_array($request->online_type, ['online_fully']) ||
                in_array($request->cash_online_type, ['cash_online_fully']) ||
                $request->payment_type === 'fully'
            ) {
                $type = 'fully';
            } elseif (in_array($request->emi_type, ['emi'])) {
                $type = 'emi';
            } else {
                $type = 'fully';
            }

            $payments = [];

            // ✅ Handle cash_online separate entries
            if (in_array($request->cash_online_type, ['cash_online_partially', 'cash_online_fully'])) {
                // 1️⃣ Cash entry
                $cashValue = $request->cash_online_type === 'cash_online_partially'
                    ? ($request->cash_amount ?? 0)
                    : ($request->fully_cash_amount ?? 0);

                if ($cashValue > 0) {
                    $payments[] = PaymentStore::create([
                        'user_id'          => $user_id,
                        'purchase_id'      => $jobCard->id,
                        'payment_amount'   => $cashValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method'   => 'cash',
                        'payment_date'     => now(),
                        'payment_type'     => $type,
                        'cash_amount'      => $cashValue,
                        'upi_amount'       => 0,
                        'emi_month'        => $request->emi_month ?? 1,
                        'isDeleted'        => 0,
                    ]);
                }

                // 2️⃣ Online entry
                $onlineValue = $request->cash_online_type === 'cash_online_partially'
                    ? ($request->online_amount ?? 0)
                    : ($request->full_online_amount ?? 0);

                if ($onlineValue > 0) {
                    $payments[] = PaymentStore::create([
                        'user_id'          => $user_id,
                        'purchase_id'      => $jobCard->id,
                        'payment_amount'   => $onlineValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method'   => 'online',
                        'payment_date'     => now(),
                        'payment_type'     => $type,
                        'cash_amount'      => 0,
                        'upi_amount'       => $onlineValue,
                        'emi_month'        => $request->emi_month ?? 1,
                        'isDeleted'        => 0,
                    ]);
                }
            } else {
                // ✅ Default single payment record
                $payments[] = PaymentStore::create([
                    'user_id'          => $user_id,
                    'purchase_id'      => $jobCard->id,
                    'payment_amount'   => $paymentAmount,
                    'remaining_amount' => $newRemaining,
                    'payment_method'   => $request->payment_method ?? $request->payment_type ?? '',
                    'payment_date'     => now(),
                    'payment_type'     => $type,
                    'cash_amount'      => $request->cash_amount ?? 0,
                    'upi_amount'       => $request->online_amount ?? 0,
                    'emi_month'        => $request->emi_month ?? 1,
                    'isDeleted'        => 0,
                ]);
            }

            // ✅ Determine and update status
            $paymentStatus  = 'unpaid';
            $purchaseStatus = 'pending';

            if ($newRemaining <= 0) {
                $paymentStatus  = 'paid';
                $purchaseStatus = 'completed';
            } elseif ($newRemaining > 0 && $newRemaining < ($jobCard->grand_total ?? 0)) {
                $paymentStatus = 'partial';
                $purchaseStatus = 'partially';
            }

            // ✅ Update JobCard remaining and status
            $jobCard->update([
                'remaining_amount' => $newRemaining,
                'status'           => $purchaseStatus
            ]);

            Purchases::where('invoice_id', $jobCard->id)->update([
                'payment_status'  => $paymentStatus,
                'purchase_status' => $purchaseStatus,
                'updated_at'      => now(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment submitted successfully.',
                'data'    => $payments,
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Purchase ID is required'], 400);
    }

    public function paymentHistory($purchaseId)
    {
        $history = PaymentStore::where('purchase_id', $purchaseId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $history,
        ]);
    }

    public function export_purchase(Request $request)
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

        // ✅ Staff vs others
        if ($user->role === 'staff') {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($user, $userBranchId) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.created_by', '=', $user->id)     // staff's own records
                        ->where('purchases.branch_id', '=', $userBranchId); // same branch
                });
        } else {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($branch_id) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.branch_id', '=', $branch_id);
                });
        }

        // ✅ Common joins
        $query = $query
            ->join('users', 'purchases.vendor_id', '=', 'users.id')
            ->join('products', 'purchases.item', '=', 'products.id')
            ->join('quantity_types', 'purchases.quantity_type_id', '=', 'quantity_types.id')
            ->select(
                'purchase_invoice.id',
                'users.name as vendor_name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at as date',
                DB::raw("GROUP_CONCAT(products.name SEPARATOR ', ') as product_names"),
                DB::raw("GROUP_CONCAT(purchases.price SEPARATOR ', ') as product_prices"),
                DB::raw("GROUP_CONCAT(purchases.quantity SEPARATOR ', ') as product_quantities"),
                DB::raw("GROUP_CONCAT(quantity_types.quantity_type SEPARATOR ', ') as product_quantity_types"),
                DB::raw("(SELECT COUNT(*) FROM payment_store
                  WHERE payment_store.purchase_id = purchase_invoice.id
                  AND payment_store.isDeleted = 0) as has_payment")
            )
            ->where('purchase_invoice.isDeleted', '=', 0);
        // dd($query);
        // ✅ Date filter
        if ($request->has('date') && ! empty($request->date)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
                $query->whereDate('purchases.created_at', $date);
            } catch (\Exception $e) {
                $query->whereDate('purchases.created_at', $request->date);
            }
        }

        // ✅ Month filter
        if ($request->has('month') && ! empty($request->month)) {
            $query->whereMonth('purchases.created_at', $request->month);
        }

        // ✅ Year filter
        if ($request->has('year') && ! empty($request->year)) {
            $query->whereYear('purchases.created_at', $request->year);
        }

        // ✅ Vendor filter
        if ($request->has('customer_id') && ! empty($request->customer_id)) {
            $query->where('users.name', $request->customer_id);
        }

        $purchases = $query
            ->groupBy(
                'purchase_invoice.id',
                'users.name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at'
            )
            ->orderBy('purchase_invoice.id', 'desc')
            ->get();

        // ✅ Add pending amount
        foreach ($purchases as $purchase) {
            $payment = \App\Models\PaymentStore::where('purchase_id', $purchase->id)
                ->orderBy('id', 'desc')
                ->first();
            $purchase->pending_amount = $payment ? $payment->remaining_amount : 0;
        }

        // ✅ Currency settings
        $settings          = DB::table('settings')->where('branch_id', $branch_id)->first();
        $currencySymbolRaw = $settings->currency_symbol ?? '₹';
        $currencySymbol    = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $currencyPosition  = $settings->currency_position ?? 'left';

        // ✅ Create Excel
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Invoice Number');
        $sheet->setCellValue('B1', 'Vendor');
        $sheet->setCellValue('C1', 'Date');
        $sheet->setCellValue('D1', 'Products');
        $sheet->setCellValue('E1', 'Quantities');
        $sheet->setCellValue('F1', 'Quantity Types');
        $sheet->setCellValue('g1', 'Prices');
        $sheet->setCellValue('h1', 'Grand Total');
        $sheet->setCellValue('i1', 'Purchase Status');
        $sheet->setCellValue('j1', 'Payment Status');
        $sheet->setCellValue('k1', 'Pending Amount');
        $sheet->getStyle('A1:k1')->getFont()->setBold(true);

        $row = 2;

        foreach ($purchases as $purchase) {
            $sheet->setCellValue('A' . $row, $purchase->invoice_number);
            $sheet->setCellValue('B' . $row, $purchase->vendor_name);
            $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($purchase->date)->format('Y-m-d'));
            $sheet->setCellValue('D' . $row, $purchase->product_names);
            $sheet->setCellValue('E' . $row, $purchase->product_quantities);
            $sheet->setCellValue('F' . $row, $purchase->product_quantity_types);
            $sheet->setCellValue('G' . $row, $purchase->product_prices);
            $sheet->setCellValue('H' . $row, $currencyPosition === 'left'
                ? $currencySymbol . ' ' . $purchase->grand_total
                : $purchase->grand_total . ' ' . $currencySymbol);
            $sheet->setCellValue('I' . $row, ucfirst($purchase->purchase_status));
            $sheet->setCellValue('J' . $row, ucfirst($purchase->payment_status));
            $sheet->setCellValue('K' . $row, $currencyPosition === 'left'
                ? $currencySymbol . ' ' . $purchase->pending_amount
                : $purchase->pending_amount . ' ' . $currencySymbol);
            $row++;
        }

        // $writer   = new Xlsx($spreadsheet);
        // $fileName = 'Purchases_' . date('Ymd_His') . '.xlsx';

        // return response()->streamDownload(function () use ($writer) {
        //     $writer->save('php://output');
        // }, $fileName, [
        //     'Content-Type'                  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        //     'Access-Control-Expose-Headers' => 'Content-Disposition',
        // ]);
        $writer       = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename     = 'Purchases_' . date('Ymd_His') . '.xlsx';
        $relativePath = 'exports/' . $filename;

        // Save temporary file
        $temp_file = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($temp_file);
        \Storage::disk('public')->put($relativePath, file_get_contents($temp_file));
        unlink($temp_file);

        // Generate public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status'    => true,
            'message'   => 'Purchases Excel generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $filename,
        ]);
    }

    public function export_purchase_pdf(Request $request)
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
        // dd($branch_id);

        if ($user->role === 'staff') {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($userBranchId, $user) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.created_by', '=', $user->id)     // ✅ staff’s own user ID
                        ->where('purchases.branch_id', '=', $userBranchId); // ✅ ensure same branch
                });
        } else {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($branch_id) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.branch_id', '=', $branch_id);
                });
        }

        $query = $query
            ->join('users', 'purchases.vendor_id', '=', 'users.id')
            ->join('products', 'purchases.item', '=', 'products.id')
            ->join('quantity_types', 'purchases.quantity_type_id', '=', 'quantity_types.id')
            ->select(
                'purchase_invoice.id',
                'users.name as vendor_name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at as date',
                DB::raw("GROUP_CONCAT(products.name SEPARATOR ', ') as product_names"),
                DB::raw("GROUP_CONCAT(purchases.price SEPARATOR ', ') as product_prices"),
                DB::raw("GROUP_CONCAT(purchases.quantity SEPARATOR ', ') as product_quantities"),
                DB::raw("GROUP_CONCAT(quantity_types.quantity_type SEPARATOR ', ') as product_quantity_types"),
                DB::raw("(SELECT COUNT(*) FROM payment_store
                  WHERE payment_store.purchase_id = purchase_invoice.id
                  AND payment_store.isDeleted = 0) as has_payment")
            )
            ->where('purchase_invoice.isDeleted', '=', 0);
        // ✅ Date filters
        // if ($request->has('date') && !empty($request->date)) {
        //     try {
        //         $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
        //         $query->whereDate('purchases.created_at', $date);
        //     } catch (\Exception $e) {
        //         $query->whereDate('purchases.created_at', $request->date);
        //     }
        // } else {
        //     if ($request->has('month') && !empty($request->month)) {
        //         $query->whereMonth('purchases.created_at', $request->month);
        //     }

        //     if ($request->has('year') && !empty($request->year)) {
        //         $query->whereYear('purchases.created_at', $request->year);
        //     }
        // }
        // // if ($request->has('customer_id') && !empty($request->customer_id)) {
        // //     $query->where('purchases.vendor_id', $request->customer_id);
        // // }

        // // ✅ Vendor filter (match by vendor name instead of id)
        // if ($request->has('customer_id') && !empty($request->customer_id)) {
        //     $query->where('users.name', $request->customer_id);
        // }
        // ✅ Date filter
        if ($request->has('date') && ! empty($request->date)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
                $query->whereDate('purchases.created_at', $date);
            } catch (\Exception $e) {
                $query->whereDate('purchases.created_at', $request->date);
            }
        }

        // ✅ Month filter
        if ($request->has('month') && ! empty($request->month)) {
            $query->whereMonth('purchases.created_at', $request->month);
        }

        // ✅ Year filter
        if ($request->has('year') && ! empty($request->year)) {
            $query->whereYear('purchases.created_at', $request->year);
        }

        // ✅ Vendor filter
        if ($request->has('customer_id') && ! empty($request->customer_id)) {
            $query->where('users.name', $request->customer_id);
        }

        $purchases = $query
            ->groupBy(
                'purchase_invoice.id',
                'users.name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at'
            )
            ->orderBy('purchase_invoice.id', 'desc')
            ->get();

        // Add pending amount and calculate totals
        $totalPending     = 0;
        $totalGrandAmount = 0;
        foreach ($purchases as $purchase) {
            $payment = \App\Models\PaymentStore::where('purchase_id', $purchase->id)
                ->orderBy('id', 'desc')
                ->first();
            $purchase->pending_amount = $payment ? $payment->remaining_amount : 0;
            $totalPending += $purchase->pending_amount;
            $totalGrandAmount += $purchase->grand_total;
        }

        // Currency settings
        $settings          = DB::table('settings')->where('branch_id', $branch_id)->first();
        $currencySymbolRaw = $settings->currency_symbol ?? '₹';
        $currencySymbol    = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $currencyPosition  = $settings->currency_position ?? 'left';

        // Get user info for PDF
        $userName = $user->name ?? 'N/A';
        $gstNum   = $user->gst_number ?? 'N/A';

        // ✅ Generate PDF using a Blade view
        $pdf = Pdf::loadView('purchase.purchase_pdf', [
            'purchases'        => $purchases,
            'totalPending'     => $totalPending,
            'totalGrandAmount' => $totalGrandAmount,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'settings'         => $settings,
            'userName'         => $userName,
            'gstNum'           => $gstNum,
        ]);

        // 🔹 Save PDF to storage
        $fileName     = 'Purchases_' . now()->format('Ymd_His') . '.pdf';
        $relativePath = 'purchase-reports/' . $fileName;
        \Storage::disk('public')->put($relativePath, $pdf->output());

        // 🔹 Generate full public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        // 🔹 Return JSON response with PDF info
        return response()->json([
            'status'    => true,
            'message'   => 'Purchase PDF generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function export_purchases_report_pdf_api(Request $request)
    {
        try {
            $user               = Auth::guard('api')->user();
            $branchId           = $user->id ?? null;
            $UserBranchId       = $user->branch_id ?? null;
            $userRole           = $user->role ?? '';
            $selectedSubAdminId = $request->selectedSubAdminId ?? null;

            // ✅ Determine branch_id based on role
            if ($userRole === 'sub-admin') {
                $branchId = $branchId;
            } elseif ($userRole === 'admin' && $selectedSubAdminId) {
                $branchId = $selectedSubAdminId;
            } elseif ($userRole === 'staff') {
                $branchId = $UserBranchId;
            }

            // ✅ Collect IDs from form-data (ids[] = 1, ids[] = 2, etc.)
            $idsArray = $request->input('ids', []); // ensures an array, even if empty
            // ✅ Store all IDs into one variable (as comma-separated string, if needed)
            $idsString = implode(',', $idsArray);
            // dd($idsArray);

            if (empty($idsArray)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No purchase IDs provided.',
                ]);
            }

            // ✅ Fetch purchase data
            $purchases = Purchases::with('product', 'invoice', 'vendor')
                ->whereIn('id', $idsArray)
                ->where('branch_id', $branchId)
                ->get();

            if ($purchases->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No purchase data found.',
                ]);
            }

            $setting     = Setting::where('branch_id', $branchId)->first();
            $subtotalRaw = (float) $purchases->sum('amount_total');

            $discountPercent   = 0.0;
            $discountAmountRaw = ($discountPercent / 100.0) * $subtotalRaw;
            $afterDiscountRaw  = $subtotalRaw - $discountAmountRaw;

            $invoiceIds  = [];
            $shippingRaw = 0.0;
            foreach ($purchases as $purchase) {
                if ($purchase->invoice && ! in_array($purchase->invoice->id, $invoiceIds)) {
                    $invoiceIds[] = $purchase->invoice->id;
                    $shippingRaw += (float) ($purchase->invoice->shipping ?? 0);
                }
            }

            $taxRates = TaxRate::where('status', 'active')
                ->where('branch_id', $branchId)
                ->where('isDeleted', 0)
                ->get();

            $taxDetails     = [];
            $formatCurrency = function ($amt) use ($setting) {
                return $setting->currency_position === 'right'
                    ? number_format($amt, 2) . $setting->currency_symbol
                    : $setting->currency_symbol . number_format($amt, 2);
            };

            foreach ($taxRates as $tax) {
                $amount       = ($tax->tax_rate / 100.0) * $afterDiscountRaw;
                $taxDetails[] = [
                    'name'             => $tax->tax_name,
                    'rate'             => $tax->tax_rate,
                    'amount'           => $amount,
                    'formatted_amount' => $formatCurrency($amount),
                ];
            }

            $grandTotalRaw = $afterDiscountRaw + $shippingRaw + collect($taxDetails)->sum('amount');

            $invoiceRecord = PurchaseInvoice::whereIn('id', $idsArray)
                ->where('branch_id', $branchId)
                ->first();

            $invoice = (object) [
                'invoice_number'   => $invoiceRecord->invoice_number ?? 'PR-' . now()->format('YmdHis'),
                'created_at'       => $invoiceRecord->created_at ?? now()->format('Y-m-d H:i:s'),
                'paid'             => $invoiceRecord->paid ?? false,
                'status'           => $invoiceRecord->status ?? 'completed',
                'remaining_amount' => $invoiceRecord->remaining_amount ?? 0,
                'gst_option'       => $invoiceRecord->gst_option ?? 'without_gst',
            ];

            $compenyinfo      = $setting;
            $currencySymbol   = $compenyinfo->currency_symbol ?? '₹';
            $currencyPosition = $compenyinfo->currency_position ?? 'left';
            $vendor           = $purchases->first()->vendor ?? null;

            $pdfData = [
                'invoice'          => $invoice,
                'vendor'           => [
                    'name'    => $vendor->name ?? 'Walk-in Vendor',
                    'email'   => $vendor->email ?? '',
                    'phone'   => $vendor->phone ?? '',
                    'address' => $vendor->address ?? '',
                ],
                'purchases'        => $purchases,
                'currencySymbol'   => $currencySymbol,
                'currencyPosition' => $currencyPosition,
                'setting'          => $setting,
                'subtotal'         => $formatCurrency($subtotalRaw),
                'discount'         => $discountPercent,
                'discountAmount'   => $formatCurrency($discountAmountRaw),
                'afterDiscount'    => $formatCurrency($afterDiscountRaw),
                'shipping'         => $formatCurrency($shippingRaw),
                'taxDetails'       => $taxDetails,
                'grandTotal'       => $formatCurrency($grandTotalRaw),
                'payment_status'   => ucfirst($purchases->first()->payment_status ?? 'Pending'),
                'payment_method'   => ucfirst($purchases->first()->payment_method ?? 'N/A'),
            ];

            // ✅ Generate the PDF
            $pdf = PDF::loadView('purchase.purchase-report-pdf', $pdfData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont'          => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                ]);

            // ✅ Generate a unique filename
            $filename     = 'purchase_report_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'purchase-reports/' . $filename;

            // ✅ Save to storage/public/purchase-reports/
            \Storage::disk('public')->put($relativePath, $pdf->output());

            // ✅ Build public URL
            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status'    => true,
                'message'   => 'Purchase report PDF generated successfully.',
                'file_url'  => $fileUrl,
                'file_name' => $filename,
                'ids_used'  => $idsString, // ✅ shows all IDs used
            ]);
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate Purchase Report PDF.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function purchaseProductChart(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->branch_id;
        $userId       = $user->id;
        $subAdminId   = $request->selectedSubAdminId;

        // 🔹 Determine branch logic
        if ($role === 'staff' && $userBranchId) {
            $branchIdToUse = $userBranchId;
        } elseif ($role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        $query = Purchases::with('product', 'quantityType:id,quantity_type')
            ->where('purchase_status', 'completed')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0);

        // dd($query);

        if ($role === 'staff') {
            $query->whereHas('invoice', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        }

        // 🔹 Apply filters
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }
        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        // dd($request->category_id);
        // 🔹 Aggregate totals
        $data = $query->selectRaw('item, SUM(quantity) as total_qty, SUM(amount_total) as total_amount')
            ->groupBy('item')
            ->with('product:id,name', 'quantityType:id,quantity_type')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // dd($data);

        $labels     = $data->pluck('product.name')->toArray();
        $totals     = $data->pluck('total_qty')->toArray();
        $grandTotal = $data->sum('total_amount');
        $quantityTypes = $data->pluck('quantityType.quantity_type')->toArray();


        return response()->json([
            'status'      => true,
            'labels'      => $labels,
            'totals'      => $totals,
            'grand_total' => $grandTotal,
            'qty_types'   => $quantityTypes
        ]);
    }

    public function view_purchase_report(Request $request)
    {
        try {
            $ids                = $request->input('ids');
            $selectedSubAdminId = $request->input('selectedSubAdminId');
            $branchIdToUse      = $request->input('branch');

            if (empty($ids)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No purchase IDs provided.',
                ], 400);
            }

            // Convert IDs array to string if needed
            $idsString = is_array($ids) ? implode(',', $ids) : $ids;

            $authUser = Auth::guard('api')->user();

            // 🔹 Determine branch ID logic
            if ($authUser) {
                if ($authUser->role === 'staff' && $authUser->branch_id) {
                    $branchIdToUse = $authUser->branch_id;
                } elseif ($authUser->role === 'admin' && ! empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } else {
                    $branchIdToUse = $authUser->id;
                }
            } else {
                // 🔹 No authentication — require branch ID from frontend
                if (! empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } elseif (! empty($branchIdToUse)) {
                    $branchIdToUse = $branchIdToUse;
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Branch ID missing (unauthenticated request).',
                    ], 400);
                }
            }

            // 🔹 Generate report view URL
            $reportUrl = url('purchase/report/view-page?ids=' . $idsString . '&branch=' . $branchIdToUse);

            return response()->json([
                'status'    => true,
                'message'   => 'Purchase report link generated successfully.',
                'view_link' => $reportUrl,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate purchase report link.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
    public function getQuantityTypes(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $query = QuantityType::where('isDeleted', 0);

            // Optional filter: if sub_branch_id is provided
            if ($request->filled('sub_branch_id')) {
                $query->where('created_by', $request->sub_branch_id);
            }

            $types = $query->orderBy('id', 'desc')->get(['id', 'quantity_type']);
            // dd($types);

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
