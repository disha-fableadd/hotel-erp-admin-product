<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\Table_no;
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

class SalesController extends Controller
{
    private function normalizeTableStatus(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        return in_array($status, [Table_no::STATUS_BLANK, Table_no::STATUS_RUNNING, Table_no::STATUS_RESERVED], true)
            ? $status
            : Table_no::STATUS_BLANK;
    }

    private function tableStatusClass(?string $status): string
    {
        return match ($this->normalizeTableStatus($status)) {
            Table_no::STATUS_RUNNING => 'running-table',
            Table_no::STATUS_RESERVED => 'reserved-table',
            default => 'blank-table',
        };
    }

    private function syncTableStatus(?int $tableId): array
    {
        if (! $tableId) {
            return [
                'status' => Table_no::STATUS_BLANK,
                'class' => 'blank-table',
            ];
        }

        $activeOrderId = Order::where('table_no', $tableId)
            ->where('isDeleted', 0)
            ->where('table_status', 1)
            ->orderByDesc('id')
            ->value('id');

        $status = $activeOrderId ? Table_no::STATUS_RUNNING : Table_no::STATUS_BLANK;

        Table_no::where('id', $tableId)->update([
            'status' => $status,
            'table_status' => $status === Table_no::STATUS_RUNNING ? 1 : 0,
            'order_id' => $activeOrderId,
        ]);

        return [
            'status' => $status,
            'class' => $this->tableStatusClass($status),
        ];
    }

    // public function getAllSales(Request $request)
    // {
    //     $settings = DB::table('settings')->first();
    //     $currencySymbol = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     $query = Auth::guard('api')->user()
    //         ->sales()
    //         ->where('isDeleted', 0)
    //         ->with('customer');

    //     if ($request->has('date') && !empty($request->date)) {
    //         // 🟩 Only filter by date
    //         $query->whereDate('created_at', $request->date);
    //     } else {
    //         // 🟦 If date is not selected, apply month/year
    //         if ($request->has('year') && !empty($request->year)) {
    //             $query->whereYear('created_at', $request->year);
    //         }

    //         if ($request->has('month') && !empty($request->month)) {
    //             $query->whereMonth('created_at', $request->month);
    //         }
    //     }

    //     $sales = $query->get();

    //     if ($sales->isEmpty()) {
    //         return response()->json(['status' => false, 'message' => 'No sales found'], 404);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'data' => $sales,
    //         'currency_symbol' => $currencySymbol,
    //         'currency_position' => $currencyPosition,
    //     ], 200);
    // }

    public function getHistory($order_id)
    {
        // $history = PaymentStore::where('order_id', $job_card_id)
        $history = PaymentStore::where('order_id', $order_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history,
        ]);
    }
    public function makePaymentSubmit(Request $request)
    {
        // dd($request->all());
        // $user_id = Auth::id();
        $user_id = Auth::guard('api')->user()->id;

        $request->validate([

            'order_id' => 'nullable|integer',
            'payment_amount' => 'nullable|numeric',
            'payment_date' => 'nullable|date',
            'payment_type' => 'nullable|string',
            'emi_month' => 'nullable|integer',
            'pending_date' => 'nullable|date',
            'new_emi_value' => 'nullable',
            'emi_paid_value' => 'nullable|numeric',
        ]);

        if ($request->filled('order_id')) {
            // Determine payment amount
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

            // Determine payment type
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
            } elseif (
                in_array($request->emi_type, ['emi'])
            ) {
                $type = 'emi';
            } else {
                $type = 'fully';
            }

            // Fetch Invoice if exists
            $invoice = $request->order_id ? Order::find($request->order_id) : null;

            // Calculate remaining amount
            $currentRemaining = $invoice->remaining_amount ?? 0;
            $newRemaining = max(0, $currentRemaining - $paymentAmount);
            // dd($currentRemaining,$newRemaining);

            // $payment = PaymentStore::create([
            //     'user_id'           => $user_id,
            //     'order_id'          => $request->order_id,
            //     'custom_invoice_id' => null,
            //     'payment_amount'    => $paymentAmount,
            //     'remaining_amount'  => $newRemaining,
            //     'payment_method'    => $request->payment_method ?? $request->payment_type ?? '',
            //     'payment_date'      => \Carbon\Carbon::now(),
            //     'payment_type'      => $type,
            //     'cash_amount'       => $request->cash_amount,
            //     'upi_amount'        => $request->online_amount,
            //     'emi_month'         => $request->emi_month ?? 1,
            //     'isDeleted'         => 0,
            // ]);
            // Handle cash_online_partially separately
            // ✅ Handle cash_online cases separately
            if ($request->cash_online_type === 'cash_online_partially' || $request->cash_online_type === 'cash_online_fully') {
                $payments = [];

                // 1️⃣ Handle Cash entry (partially or fully)
                $cashValue = $request->cash_online_type === 'cash_online_partially'
                    ? $request->cash_amount
                    : $request->fully_cash_amount;

                if (!empty($cashValue) && $cashValue > 0) {
                    $cashPayment = PaymentStore::create([
                        'user_id' => $user_id,
                        'order_id' => $request->order_id,
                        'custom_invoice_id' => null,
                        'payment_amount' => $cashValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method' => 'cash',
                        'payment_date' => \Carbon\Carbon::now(),
                        'payment_type' => ($request->cash_online_type === 'cash_online_partially') ? 'partially' : 'fully',
                        'cash_amount' => $cashValue,
                        'upi_amount' => 0,
                        'emi_month' => $request->emi_month ?? 1,
                        'isDeleted' => 0,
                        'status' => 'credit'
                    ]);
                    $payments[] = $cashPayment;
                }

                // 2️⃣ Handle Online entry (partially or fully)
                $onlineValue = $request->cash_online_type === 'cash_online_partially'
                    ? $request->online_amount
                    : $request->full_online_amount;

                if (!empty($onlineValue) && $onlineValue > 0) {
                    $onlinePayment = PaymentStore::create([
                        'user_id' => $user_id,
                        'order_id' => $request->order_id,
                        'custom_invoice_id' => null,
                        'payment_amount' => $onlineValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method' => 'online',
                        'payment_date' => \Carbon\Carbon::now(),
                        'payment_type' => ($request->cash_online_type === 'cash_online_partially') ? 'partially' : 'fully',
                        'cash_amount' => 0,
                        'upi_amount' => $onlineValue,
                        'emi_month' => $request->emi_month ?? 1,
                        'isDeleted' => 0,
                        'status' => 'credit'
                    ]);
                    $payments[] = $onlinePayment;
                }

                $payment = $payments; // return both records as array
            } else {
                // 🟢 Default single payment record for all other cases
                $payment = PaymentStore::create([
                    'user_id' => $user_id,
                    'order_id' => $request->order_id,
                    'custom_invoice_id' => null,
                    'payment_amount' => $paymentAmount,
                    'remaining_amount' => $newRemaining,
                    'payment_method' => $request->payment_method ?? $request->payment_type ?? '',
                    'payment_date' => \Carbon\Carbon::now(),
                    'payment_type' => $type,
                    'cash_amount' => $request->cash_amount ?? 0,
                    'upi_amount' => $request->online_amount ?? 0,
                    'emi_month' => $request->emi_month ?? 1,
                    'isDeleted' => 0,
                    'status' => 'credit'
                ]);
            }

            // Update invoice
            if ($invoice) {
                $updateData = ['remaining_amount' => $newRemaining];

                // ✅ Auto set payment_status when fully paid or partially paid
                if ($newRemaining <= 0) {
                    $updateData['payment_status'] = 'completed';
                } elseif ($newRemaining > 0 && $newRemaining < ($invoice->total_amount ?? 0)) {
                    $updateData['payment_status'] = 'partially';
                } else {
                    $updateData['payment_status'] = 'Pending';
                }
                // If new EMI is being set
                if ($request->filled('new_emi_value') && $request->payment_method == 'emi' || $request->filled('emi_paid_value')) {
                    //  dd($request->all());
                    $updateData['payment_method'] = 'EMI';
                    $updateData['emi_duration'] = $request->emi_month_new ?? $request->emi_month;
                    $updateData['emi_months'] = $request->emi_total_new ?? 1;
                } else {
                    // dd($request->filled('emi_paid_value'));
                    // Otherwise, keep the actual payment method (cash, online, cash_online)
                    $method = $request->payment_method ?? $request->payment_type ?? $updateData['payment_method'] ?? 'Cash';

                    // Normalize to proper values
                    if (strtolower($method) === 'online' || strtolower($method) === 'upi') {
                        $method = 'Online';
                    } elseif (strtolower($method) === 'cash') {
                        $method = 'Cash';
                    } elseif (strtolower($method) === 'emi') {
                        $method = 'EMI';
                    } else {
                        $method = 'Cash';
                    }

                    $updateData['payment_method'] = $method;
                }

                if ($request->emi_duration) {
                    $updateData['emi_duration'] = $request->emi_duration;
                }

                // Update next pending date if provided
                if ($request->pending_date) {
                    $updateData['next_pending_date'] = $request->pending_date;
                }

                $invoice->update($updateData);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Payment submitted successfully.',
            'data' => $payment,
        ]);
    }

    // In your controller
    public function getProductsByCategory($categoryId)
    {
        $settings = DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $products = Product::with('category', 'quantityType')
            ->where('category_id', $categoryId)
            ->where('isDeleted', 0)
            ->where('status', 'active') // ✅ Only active products
            ->get();
        return response()->json([
            'status' => true,
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'quantity_type_id' => $product->quantity_type_id,
                    'quantity_type' => $product->quantityType ? $product->quantityType->quantity_type : null, // ✅ Add name
                    'image' => $product->images,
                    'image_url' => $product->image_url,
                    'category_name' => $product->category->name,
                    'categoryId' => $product->category_id,
                    'category_name' => $product->category ? $product->category->name : null,
                ];
            }),
        ]);
    }

    public function order_sale(Request $request)
    {
        // dd($request->all());

        // Validate the request data
        $request->validate([
            'customer_id' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'payment_method' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|array',
            'tax.*.tax_name' => 'nullable|string',
            'tax.*.rate' => 'nullable|numeric|min:0',
            'tax.*.amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'items.*.categoryId' => 'nullable|integer|exists:categories,id',
            'total' => 'required|numeric|min:0',
        ]);

        // Safely cast table_id before starting a transaction
        $tableNoInt = null;
        if (!empty($request->table_id) && is_numeric($request->table_id)) {
            $tableNoInt = (int) $request->table_id;
        }

        // If this table already has an active running order, merge new items into it
        // instead of creating a second order (which would hide the first items on the table).
        if ($tableNoInt) {
            $existingActiveOrderId = Order::where('table_no', $tableNoInt)
                ->where('isDeleted', 0)
                ->where('table_status', 1)
                ->orderByDesc('id')
                ->value('id');

            if ($existingActiveOrderId) {
                $mergedByProduct = [];

                foreach (OrderItem::where('order_id', $existingActiveOrderId)->get() as $old) {
                    $mergedByProduct[$old->product_id] = [
                        'product_id' => $old->product_id,
                        'quantity' => (int) $old->quantity,
                        'quantity_type_id' => $old->quantity_type_id,
                        'quantity_type123' => $old->quantity_type_id,
                        'categoryId' => $old->category_id,
                        'price' => (float) $old->price,
                        'total' => (float) $old->total_amount,
                    ];
                }

                foreach ($request->items ?? [] as $item) {
                    $pid = $item['product_id'] ?? null;
                    if (!$pid) {
                        continue;
                    }

                    if (isset($mergedByProduct[$pid])) {
                        $mergedByProduct[$pid]['quantity'] += (int) $item['quantity'];
                        $mergedByProduct[$pid]['total'] = $mergedByProduct[$pid]['quantity'] * $mergedByProduct[$pid]['price'];
                    } else {
                        $price = (float) ($item['price'] ?? 0);
                        $qty = (int) ($item['quantity'] ?? 0);
                        $mergedByProduct[$pid] = [
                            'product_id' => $pid,
                            'quantity' => $qty,
                            'quantity_type_id' => $item['quantity_type_id'] ?? null,
                            'quantity_type123' => $item['quantity_type123'] ?? null,
                            'categoryId' => $item['categoryId'] ?? null,
                            'price' => $price,
                            'total' => (float) ($item['total'] ?? ($price * $qty)),
                        ];
                    }
                }

                $mergedItems = array_values($mergedByProduct);
                $mergedSubtotal = array_sum(array_map(static fn ($row) => (float) $row['total'], $mergedItems));
                $discountPct = (float) ($request->discount ?? 0);
                $discountAmount = $mergedSubtotal * ($discountPct / 100);
                $mergedTotal = $mergedSubtotal - $discountAmount;

                // Keep tax contribution from the request totals when GST is applied
                $requestSubtotal = (float) ($request->subtotal ?? 0);
                $requestTotal = (float) ($request->total ?? 0);
                if ($requestSubtotal > 0 && $requestTotal > $requestSubtotal) {
                    $taxRatio = ($requestTotal - ($requestSubtotal * (1 - ($discountPct / 100)))) / max($requestSubtotal, 0.01);
                    $mergedTotal = ($mergedSubtotal - $discountAmount) + ($mergedSubtotal * max($taxRatio, 0));
                }

                $request->merge([
                    'update_id' => $existingActiveOrderId,
                    'items' => $mergedItems,
                    'subtotal' => $mergedSubtotal,
                    'total' => round($mergedTotal, 2),
                    'table_checkout' => $request->table_checkout ?? '',
                ]);

                return $this->update_sale_order($request);
            }
        }

        DB::beginTransaction();
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role;
        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif (!empty($request->selectedSubAdminId) && $userRole == 'admin') {
            $branchIdToUse = $request->selectedSubAdminId ?? null;
        } else {
            $branchIdToUse = $userData->id;
        }
        try {
            // Check if all items have enough stock before proceeding
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->quantity < $item['quantity']) {
                    return response()->json([
                        'status' => false,
                        'message' => "Insufficient stock for product ID: {$item['product_id']}",
                    ], 400);
                }
            }

            // Generate unique order number
            $orderNumber = now()->format('Ymd') . str_pad((Order::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);

            $taxJson = json_encode([]); // default blank array

            if ($request->has('tax') && !empty($request->tax)) {
                // Just get active tax IDs
                $TaxRateIds = TaxRate::where('status', 'active')->where('isDeleted', 0)->where('branch_id', $branchIdToUse)->pluck('id')->toArray();
                $taxJson = json_encode($TaxRateIds);
            }

            // Resolve customer ID
            $customer_id = null;
            $cust_name = trim($request->customer_id ?? '');
            $cust_phone = trim($request->customer_phone ?? '');

            if (!empty($cust_phone)) {
                // Try to find customer by phone first
                $existingCust = User::where('phone', $cust_phone)
                    ->where('isDeleted', 0)
                    ->first();
                if ($existingCust) {
                    $customer_id = $existingCust->id;
                    if (!empty($cust_name) && $existingCust->name === 'Default Customer') {
                        $existingCust->name = $cust_name;
                        $existingCust->save();
                    }
                }
            }

            if (!$customer_id && !empty($cust_name)) {
                if (is_numeric($cust_name)) {
                    $existingCust = User::where('id', $cust_name)->first();
                    if ($existingCust) {
                        $customer_id = $existingCust->id;
                    }
                }

                if (!$customer_id && !is_numeric($cust_name)) {
                    $newCust = User::create([
                        'name' => $cust_name,
                        'phone' => !empty($cust_phone) ? $cust_phone : null,
                        'role' => 'customer',
                        'status' => 1,
                        'branch_id' => $branchIdToUse,
                        'created_by' => $userData->id,
                    ]);
                    $customer_id = $newCust->id;

                    UserDetail::create([
                        'user_id' => $customer_id,
                        'address' => null,
                        'city' => null,
                        'country' => null,
                    ]);
                }
            }

            if (!$customer_id) {
                $defaultCustomer = User::where('role', 'customer')
                    ->where('name', 'Default Customer')
                    ->first();

                if (!$defaultCustomer) {
                    $defaultCustomer = User::create([
                        'name' => 'Default Customer',
                        'role' => 'customer',
                        'status' => 1,
                        'branch_id' => $branchIdToUse,
                        'created_by' => $userData->id,
                    ]);
                    UserDetail::create([
                        'user_id' => $defaultCustomer->id,
                        'address' => null,
                        'city' => null,
                        'country' => null,
                    ]);
                }
                $customer_id = $defaultCustomer->id;
            }

            if ($request->payment_method != 'pending') {
                // Check if partial payment
                if ($request->paid_type === 'partially' && isset($request->pending_amount) && $request->pending_amount > 0) {
                    $remain = $request->pending_amount;
                    $payment_status = 'partial';
                } else {
                    $remain = 0;
                    $payment_status = 'completed';
                }
            } else {
                $remain = $request->total;
                $payment_status = 'pending';
            }

            // Create order
            $order = Order::create([
                'order_number'    => $orderNumber,
                'user_id'         => $customer_id,
                'customer_name'   => $cust_name ?: null,
                'customer_phone'  => $cust_phone ?: null,
                'payment_method'  => $request->payment_method,
                'discount'        => $request->discount ?? 0,
                'tax_id'          => $taxJson,
                'gst_option'      => $request->gst_option === 'with' ? 'with_gst' : 'without_gst',
                'total_amount'    => $request->total,
                'remaining_amount' => $remain,
                'payment_status'  => $payment_status,
                'table_no'        => $tableNoInt,
                'type'            => $request->table_type ?? 'dinein',
                'order_type'      => $request->order_type ?? 'Delivery',
                'assigned_staff_id' => $request->assigned_staff_id,
                // Keep the order marked active while it is attached to a table.
                'table_status'    => $tableNoInt ? 1 : 0,
                'branch_id'       => $branchIdToUse,
                'created_by'      => $userData->id,
            ]);

            // Insert order items and reduce product stock
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $product->quantity -= $item['quantity'];
                $product->save();

                $resolved_qty_type_id = !empty($item['quantity_type123']) && is_numeric($item['quantity_type123'])
                    ? intval($item['quantity_type123'])
                    : (!empty($item['quantity_type123'])
                        ? (\App\Models\QuantityType::where('quantity_type', $item['quantity_type123'])->first()->id ?? null)
                        : null);

                OrderItem::create([
                    'order_id' => $order->id,
                    'user_id' => $customer_id ?? null,
                    'category_id' => $item['categoryId'] ?? null,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'quantity_type_id' => $resolved_qty_type_id,
                    'price' => $item['price'],
                    'total_amount' => $item['total'],
                    'branch_id' => $branchIdToUse,
                    'created_by' => $userData->id,
                ]);
                $lastInventory = ProductInventory::where('product_id', $product->id)
                    ->orderBy('id', 'desc') // ✅ latest record first
                    ->first();
                // ✅ Insert into ProductInventory
                ProductInventory::create([
                    'product_id' => $product->id,
                    'initial_stock' => $lastInventory->initial_stock, // stock before sale
                    'current_stock' => $product->quantity,            // stock after sale
                    'branch_id' => $branchIdToUse,
                    'create_by' => $userData->id,
                    'type' => 'Sale',
                    'date' => now(),
                ]);
            }
            if ($tableNoInt) {
                $this->syncTableStatus($tableNoInt);
            }
            // dd($request->type);
            if ($request->payment_method != 'pending') {

                if ($request->payment_method === 'cash+online') {

                    // Cash entry
                    if ($request->cash_amount > 0) {
                        PaymentStore::create([
                            'user_id' => $customer_id,
                            'order_id' => $order->id,
                            'payment_amount' => $request->cash_amount,
                            'payment_date' => now(),
                            'payment_method' => 'cash',
                            'payment_type' => $request->paid_type ?? 'full',
                            'cash_amount' => $request->cash_amount,
                            'upi_amount' => 0,
                            'remaining_amount' => $request->pending_amount ?? 0,
                            'bank_id' => $request->bank_id ?? null,
                            'remarks' => $request->debit_remark ?? null,
                            'emi_month' => null,
                            'jobcard_id' => 0,
                            'isDeleted' => 0,
                            'created_at' => now(),
                            'status' => 'credit'
                        ]);
                    }

                    // Online entry
                    if ($request->online_amount > 0) {
                        PaymentStore::create([
                            'user_id' => $customer_id,
                            'order_id' => $order->id,
                            'payment_amount' => $request->online_amount,
                            'payment_date' => now(),
                            'payment_method' => 'online',
                            'payment_type' => $request->paid_type ?? 'full',
                            'cash_amount' => 0,
                            'upi_amount' => $request->online_amount,
                            'remaining_amount' => $request->pending_amount ?? 0,
                            'bank_id' => $request->bank_id ?? null,
                            'remarks' => $request->debit_remark ?? null,
                            'emi_month' => null,
                            'jobcard_id' => 0,
                            'isDeleted' => 0,
                            'created_at' => now(),
                            'status' => 'credit'
                        ]);
                    }
                } else {
                    // Normal single payment entry
                    $payAmt = $request->paid_type === 'partially'
                        ? ($request->enter_amount ?? $request->total ?? 0)
                        : ($request->total ?? 0);

                    // For a plain cash payment, cash_amount should equal the payment amount
                    $cashAmt = ($request->payment_method === 'cash')
                        ? ($request->cash_amount > 0 ? $request->cash_amount : $payAmt)
                        : ($request->cash_amount ?? 0);

                    $bankIdForPayment = $request->bank_id ?? null;
                    if ($request->payment_method === 'scan') {
                        $shopSetting = \App\Models\Setting::where('branch_id', $branchIdToUse)->first();
                        if ($shopSetting) {
                            $matchingBank = \App\Models\BankMaster::where('branch_id', $branchIdToUse)
                                ->where(function($q) use ($shopSetting) {
                                    if ($shopSetting->ac_no) {
                                        $q->where('account_number', $shopSetting->ac_no);
                                    }
                                    if ($shopSetting->bank_name) {
                                        $q->orWhere('bank_name', $shopSetting->bank_name);
                                    }
                                })
                                ->first();
                            if ($matchingBank) {
                                $bankIdForPayment = $matchingBank->id;
                            }
                        }
                    }

                    PaymentStore::create([
                        'user_id' => $customer_id,
                        'order_id' => $order->id,
                        'payment_amount' => $payAmt,
                        'payment_date' => now(),
                        'payment_method' => $request->payment_method,
                        'payment_type' => $request->paid_type ?? 'fully',
                        'cash_amount' => $cashAmt,
                        'upi_amount' => $request->upi_amount ?? 0,
                        'remaining_amount' => $request->pending_amount ?? 0,
                        'bank_id' => $bankIdForPayment,
                        'remarks' => $request->debit_remark ?? null,
                        'emi_month' => null,
                        'jobcard_id' => 0,
                        'isDeleted' => 0,
                        'created_at' => now(),
                        'status' => 'credit'
                    ]);
                }
            }

            DB::commit();
            
            try {
                $shopSetting = \App\Models\Setting::where('branch_id', $branchIdToUse)->first();
                if ($shopSetting && $shopSetting->send_mail) {
                    if ($customer_id) {
                        $customer = \App\Models\User::find($customer_id);
                        if ($customer && $customer->email) {
                            $smtpSetting = \App\Models\SmtpSetting::where('branch_id', $branchIdToUse)->where('status', 1)->first();
                            if (!$smtpSetting) {
                                // fallback to main branch if sub-admin has none
                                $smtpSetting = \App\Models\SmtpSetting::where('branch_id', $userData->id)->where('status', 1)->first();
                            }
                            
                            if ($smtpSetting && $smtpSetting->password) {
                                config([
                                    'mail.mailers.smtp.host'       => $smtpSetting->host,
                                    'mail.mailers.smtp.port'       => $smtpSetting->port,
                                    'mail.mailers.smtp.username'   => $smtpSetting->username,
                                    'mail.mailers.smtp.password'   => decrypt($smtpSetting->password),
                                    'mail.mailers.smtp.encryption' => $smtpSetting->encryption,
                                    'mail.from.address'            => $smtpSetting->from_address ?? $smtpSetting->username,
                                    'mail.from.name'               => $smtpSetting->from_name ?? config('app.name'),
                                ]);
                            }

                            \Illuminate\Support\Facades\Mail::to($customer->email)->send(new \App\Mail\OrderCreatedMail($order, $customer->name));
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Mail sending error: ' . $e->getMessage());
            }

            try {
                // WhatsApp Message Logic
                $shopSetting = \App\Models\Setting::where('branch_id', $branchIdToUse)->first();
                if ($shopSetting && $shopSetting->customer_whatsapp_message) {
                    $phone = $customer_phone ?? ($customer_id ? \App\Models\User::find($customer_id)->phone ?? null : null);
                    if ($phone) {
                        $fbConfig = \App\Models\FacebookAppConfiguration::where('branch_id', $branchIdToUse)
                            ->where('isDeleted', 0)
                            ->first();
                        
                        if (!$fbConfig) {
                            $fbConfig = \App\Models\FacebookAppConfiguration::where('branch_id', $userData->id)
                                ->where('isDeleted', 0)
                                ->first();
                        }

                        if ($fbConfig && $fbConfig->phone_number_id && $fbConfig->access_token) {
                            $phoneNumber = preg_replace('/[^0-9]/', '', $phone);
                            if (strlen($phoneNumber) == 10) {
                                $phoneNumber = '91' . $phoneNumber; // default to India if 10 digits
                            }

                            $template = \App\Models\WhatsAppMessageTemplate::where('name', 'subscription_invoice_pdf')
                                ->where('on_off', 'active')
                                ->where('isDeleted', 0)
                                ->first();

                            if ($template) {
                                $businessName = $shopSetting->name ?? config('app.name');
                                $custName = $cust_name ?? ($customer_id ? \App\Models\User::find($customer_id)->name ?? 'Customer' : 'Customer');
                                // GENERATE PDF INTERNALLY FOR LOCALHOST UPLOAD
                                $orderItems = \App\Models\OrderItem::with('product', 'quantityType')->where('order_id', $order->id)->get();
                                $subtotal = $orderItems->sum('total_amount');
                                $discountPercent = $order->discount ?? 0;
                                $discountAmount = ($discountPercent / 100) * $subtotal;
                                $afterDiscount = $subtotal - $discountAmount;
                                
                                $taxDetails = [];
                                if ($order->gst_option === 'with_gst') {
                                    $taxIds = is_string($order->tax_id) ? json_decode($order->tax_id, true) : $order->tax_id;
                                    if (is_array($taxIds) && !empty($taxIds)) {
                                        $taxes = \App\Models\TaxRate::whereIn('id', $taxIds)->get();
                                        foreach ($taxes as $tax) {
                                            $taxAmount = ($tax->tax_rate / 100) * $afterDiscount;
                                            $taxDetails[] = [
                                                'name' => $tax->tax_name,
                                                'rate' => $tax->tax_rate,
                                                'amount' => $taxAmount,
                                                'formatted_amount' => number_format($taxAmount, 2)
                                            ];
                                        }
                                    }
                                }
                                $finalTotal = $afterDiscount + collect($taxDetails)->sum('amount');
                                $paidAmount = \App\Models\PaymentStore::where('order_id', $order->id)->where('isDeleted', 0)->sum('payment_amount');
                                $pendingAmount = $order->remaining_amount ?? 0;

                                $formatCurrency = function ($amount) use ($shopSetting) {
                                    $amount = number_format((float)$amount, 2);
                                    $symbol = $shopSetting->currency_symbol ?? '₹';
                                    $position = $shopSetting->currency_position ?? 'left';
                                    return $position === 'right' ? $amount . $symbol : $symbol . $amount;
                                };

                                $pdfData = [
                                    'view_id' => $order->id,
                                    'sales' => $order,
                                    'setting' => $shopSetting,
                                    'orderItems' => $orderItems,
                                    'salesItems' => $orderItems,
                                    'customer' => ['name' => $custName, 'email' => '', 'phone' => $phoneNumber, 'address' => 'arga'],
                                    'subtotal' => $formatCurrency($subtotal),
                                    'discountPercent' => $discountPercent,
                                    'discountAmount' => $formatCurrency($discountAmount),
                                    'afterDiscount' => (float) $afterDiscount,
                                    'formattedAfterDiscount' => $formatCurrency($afterDiscount),
                                    'finalTotal' => $formatCurrency($finalTotal),
                                    'taxDetails1' => $taxDetails,
                                    'paidAmount' => $paidAmount,
                                    'pendingAmount' => $pendingAmount,
                                ];

                                $viewName = (isset($shopSetting->invoice_size) && $shopSetting->invoice_size === 'small') ? 'sales.salse-invoice-small-pdf' : 'sales.salse-invoice-pdf';
                                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $pdfData);
                                if (isset($shopSetting->invoice_size) && $shopSetting->invoice_size === 'small') {
                                    $pdf->setPaper([0, 0, 249.45, 566.93], 'portrait');
                                } else {
                                    $pdf->setPaper('A4', 'portrait');
                                }
                                $pdfContent = $pdf->output();
                                $fileName = 'invoice_' . $order->id . '_' . date('Ymd_His') . '.pdf';

                                // Upload to WhatsApp Media API
                                $mediaUrl = "https://graph.facebook.com/v23.0/{$fbConfig->phone_number_id}/media";
                                $mediaResponse = \Illuminate\Support\Facades\Http::withToken($fbConfig->access_token)
                                    ->attach('file', $pdfContent, $fileName)
                                    ->post($mediaUrl, [
                                        'messaging_product' => 'whatsapp'
                                    ]);

                                if ($mediaResponse->successful()) {
                                    $mediaId = $mediaResponse->json('id');
                                    $pdfLink = url('/sales/invoice/pdf/' . $order->id); // For text body display

                                    $payload = [
                                        "messaging_product" => "whatsapp",
                                        "to" => $phoneNumber,
                                        "type" => "template",
                                        "template" => [
                                            "name" => $template->name,
                                            "language" => [
                                                "code" => $template->language ?? "en_US"
                                            ],
                                            "components" => [
                                                [
                                                    "type" => "header",
                                                    "parameters" => [
                                                        [
                                                            "type" => "document",
                                                            "document" => [
                                                                "id" => $mediaId,
                                                                "filename" => $fileName
                                                            ]
                                                        ]
                                                    ]
                                                ],
                                            [
                                                "type" => "body",
                                                "parameters" => [
                                                    [ "type" => "text", "text" => $custName ],
                                                    [ "type" => "text", "text" => $order->order_number ],
                                                    [ "type" => "text", "text" => $businessName ],
                                                    [ "type" => "text", "text" => $order->order_number ],
                                                    [ "type" => "text", "text" => strval($order->total_amount) ],
                                                    [ "type" => "text", "text" => ucfirst($order->payment_status) ],
                                                    [ "type" => "text", "text" => $pdfLink ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ];

                                $url = "https://graph.facebook.com/v23.0/{$fbConfig->phone_number_id}/messages";
                                $response = \Illuminate\Support\Facades\Http::withToken($fbConfig->access_token)->post($url, $payload);
                                
                                if ($response->failed()) {
                                    Log::error('WhatsApp API Error: ' . $response->body() . ' | Payload: ' . json_encode($payload));
                                } else {
                                    Log::info('WhatsApp API Success: ' . $response->body());
                                }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('WhatsApp sending error: ' . $e->getMessage());
            }

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order->id,
                'table_id' => $tableNoInt,
                'table_status' => $tableNoInt ? Table_no::STATUS_RUNNING : null,
                'table_status_class' => $tableNoInt ? 'running-table' : null,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order placement error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to place order. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update_sale_order(Request $request)
    {
        // dd($request->all());
        $order_id = $request->update_id;

        // Validate request
        $request->validate([
            'customer_id' => 'nullable|string|max:255',
            'payment_method' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|array',
            'tax.*.tax_name' => 'nullable|string',
            'tax.*.rate' => 'nullable|numeric|min:0',
            'tax.*.amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $userData = Auth::guard('api')->user();
            $userRole = $userData->role;

            if ($userRole == 'staff') {
                $branchIdToUse = $userData->branch_id;
            } elseif (!empty($request->selectedSubAdminId) && $userRole == 'admin') {
                $branchIdToUse = $request->selectedSubAdminId;
            } else {
                $branchIdToUse = $userData->id;
            }

            // Resolve customer ID
            $customer_id = null;
            $cust_name = trim($request->customer_id ?? '');
            $cust_phone = trim($request->customer_phone ?? '');

            if (!empty($cust_phone)) {
                // Try to find customer by phone first
                $existingCust = User::where('role', 'customer')
                    ->where('phone', $cust_phone)
                    ->where('isDeleted', 0)
                    ->first();
                if ($existingCust) {
                    $customer_id = $existingCust->id;
                    if (!empty($cust_name) && $existingCust->name === 'Default Customer') {
                        $existingCust->name = $cust_name;
                        $existingCust->save();
                    }
                }
            }

            if (!$customer_id && !empty($cust_name)) {
                if (is_numeric($cust_name)) {
                    $existingCust = User::where('id', $cust_name)->where('role', 'customer')->first();
                    if ($existingCust) {
                        $customer_id = $existingCust->id;
                    }
                }

                if (!$customer_id && !is_numeric($cust_name)) {
                    $newCust = User::create([
                        'name' => $cust_name,
                        'phone' => !empty($cust_phone) ? $cust_phone : null,
                        'role' => 'customer',
                        'status' => 1,
                        'branch_id' => $branchIdToUse,
                        'created_by' => $userData->id,
                    ]);
                    $customer_id = $newCust->id;

                    UserDetail::create([
                        'user_id' => $customer_id,
                        'address' => null,
                        'city' => null,
                        'country' => null,
                    ]);
                }
            }

            if (!$customer_id) {
                $defaultCustomer = User::where('role', 'customer')
                    ->where('name', 'Default Customer')
                    ->first();

                if (!$defaultCustomer) {
                    $defaultCustomer = User::create([
                        'name' => 'Default Customer',
                        'role' => 'customer',
                        'status' => 1,
                        'branch_id' => $branchIdToUse,
                        'created_by' => $userData->id,
                    ]);
                    UserDetail::create([
                        'user_id' => $defaultCustomer->id,
                        'address' => null,
                        'city' => null,
                        'country' => null,
                    ]);
                }
                $customer_id = $defaultCustomer->id;
            }

            $order = Order::findOrFail($order_id);

            // Restore stock first from OLD items
            $oldItems = OrderItem::where('order_id', $order_id)->get();

            foreach ($oldItems as $old) {
                $product = Product::find($old->product_id);
                if ($product) {
                    $product->quantity += $old->quantity;
                    $product->save();

                    ProductInventory::create([
                        'product_id' => $product->id,
                        'initial_stock' => $product->quantity + $old->quantity,
                        'current_stock' => $product->quantity,
                        'branch_id' => $branchIdToUse,
                        'create_by' => $userData->id,
                        'type' => 'Order Updated (Restore)',
                        'date' => now(),
                    ]);
                }
            }

            // Now delete old order items
            OrderItem::where('order_id', $order_id)->delete();

            // Check updated stock availability
            foreach ($request->items as $item) {
                $p = Product::find($item['product_id']);
                if ($p->quantity < $item['quantity']) {
                    return response()->json([
                        'status' => false,
                        'message' => "Insufficient stock for Product ID: {$item['product_id']}",
                    ], 400);
                }
            }

            // Prepare tax JSON like insert
            $taxJson = json_encode([]);
            if ($request->has('tax') && !empty($request->tax)) {
                $TaxRateIds = TaxRate::where('status', 'active')
                    ->where('isDeleted', 0)
                    ->where('branch_id', $branchIdToUse)
                    ->pluck('id')
                    ->toArray();
                $taxJson = json_encode($TaxRateIds);
            }

            if ($request->payment_method != 'pending') {
                $remain = 0;
                $payment_status = 'completed';
            } else {
                $remain = $request->total;
                $payment_status = 'pending';
            }

            $isCheckout = ($request->table_checkout ?? '') === 'checkout';

            // Update order
            $order->update([
                'user_id' => $customer_id,
                'payment_method' => $request->payment_method,
                'discount' => $request->discount ?? 0,
                'tax_id' => $taxJson,
                'gst_option' => $request->gst_option === 'with' ? 'with_gst' : 'without_gst',
                'total_amount' => $request->total,
                'remaining_amount' => $remain,
                'payment_status' => $payment_status,
                'table_no' => $request->table_id ?? null,
                'type' => $request->table_type ?? 'dinein',
                // Mark the order inactive only when the table is checked out.
                'table_status' => $isCheckout ? 0 : 1,
            ]);

            // Insert updated order items & reduce stock
            foreach ($request->items as $item) {

                $product = Product::find($item['product_id']);
                $product->quantity -= $item['quantity'];
                $product->save();

                $resolved_qty_type_id = !empty($item['quantity_type123']) && is_numeric($item['quantity_type123'])
                    ? intval($item['quantity_type123'])
                    : (!empty($item['quantity_type123'])
                        ? (\App\Models\QuantityType::where('quantity_type', $item['quantity_type123'])->first()->id ?? null)
                        : null);

                OrderItem::create([
                    'order_id' => $order->id,
                    'user_id' => $customer_id,
                    'category_id' => $item['categoryId'] ?? null,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'quantity_type_id' => $resolved_qty_type_id,
                    'price' => $item['price'],
                    'total_amount' => $item['total'],
                    'branch_id' => $branchIdToUse,
                    'created_by' => $userData->id,
                ]);

                ProductInventory::create([
                    'product_id' => $product->id,
                    'initial_stock' => $product->quantity + $item['quantity'],
                    'current_stock' => $product->quantity,
                    'branch_id' => $branchIdToUse,
                    'create_by' => $userData->id,
                    'type' => 'Order Updated (Sale)',
                    'date' => now(),
                ]);
            }

            // Update table status (dine-in only)
            if (!empty($request->table_id) && is_numeric($request->table_id)) {
                $this->syncTableStatus((int) $request->table_id);
            }

            // Delete old payment entries
            PaymentStore::where('order_id', $order->id)->delete();

            // Insert updated payment entries
            if ($isCheckout) {
                if (!empty($request->table_id) && is_numeric($request->table_id)) {
                    $this->syncTableStatus((int) $request->table_id);
                }
                $paymentType = $request->paid_type ?? (($request->payment_method === 'cash+online') ? 'partially' : 'fully');
                if ($request->payment_method != 'pending') {

                    if ($request->payment_method === 'cash+online') {

                        if ($request->cash_amount > 0) {
                            PaymentStore::create([
                                'user_id' => $customer_id,
                                'order_id' => $order->id,
                                'payment_amount' => $request->cash_amount,
                                'payment_method' => 'cash',
                                'payment_type' => $paymentType,
                                'cash_amount' => $request->cash_amount,
                                'upi_amount' => 0,
                                'payment_date' => now(),
                                'status' => 'credit'
                            ]);
                        }

                        if ($request->online_amount > 0) {
                            PaymentStore::create([
                                'user_id' => $customer_id,
                                'order_id' => $order->id,
                                'payment_amount' => $request->online_amount,
                                'payment_method' => 'online',
                                'payment_type' => $paymentType,
                                'cash_amount' => 0,
                                'upi_amount' => $request->online_amount,
                                'payment_date' => now(),
                                'status' => 'credit'
                            ]);
                        }

                    } else {
                        // For a plain cash payment, cash_amount should equal the payment amount
                        $updateCashAmt = ($request->payment_method === 'cash')
                            ? ($request->cash_amount > 0 ? $request->cash_amount : $request->total)
                            : ($request->cash_amount ?? 0);

                        PaymentStore::create([
                            'user_id' => $customer_id,
                            'order_id' => $order->id,
                            'payment_amount' => $request->total,
                            'payment_method' => $request->payment_method,
                            'payment_type' => $paymentType,
                            'cash_amount' => $updateCashAmt,
                            'upi_amount' => $request->upi_amount ?? 0,
                            'payment_date' => now(),
                            'status' => 'credit'
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully!',
                'order_id' => $order->id,
                'table_id' => $request->table_id ?? null,
                'table_status' => !empty($request->table_id) ? ($isCheckout ? Table_no::STATUS_BLANK : Table_no::STATUS_RUNNING) : null,
                'table_status_class' => !empty($request->table_id) ? ($isCheckout ? 'blank-table' : 'running-table') : null,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update order. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function get_orders(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;

        // ✅ Prefer request input over route param
        $selectedSubAdminID = $request->input('selectedSubAdminId');

        // ✅ Build the query
        $query = Order::with([
            'user:id,name,phone',
            'orderItems:id,order_id', // Only fetch id and order_id from orderItems (no created_at)
            'creator:id,name',
        ])
            ->where('isDeleted', 0)
            // ✅ Add subquery to check if payments exist
            ->withCount([
                'payments as has_payment' => function ($q) {
                    $q->where('isDeleted', 0);
                },
            ]);
        if ($role === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif ($role === 'admin' && $selectedSubAdminID) {
            $query->where('branch_id', $selectedSubAdminID);
        } elseif ($role === 'staff') {
            $query->where('created_by', $userId);
        } else {
            $query->where('branch_id', $userId);
        }
        // ✅ Apply date filter
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('created_at', $request->month);
            }
        }

        // ✅ Apply sorting
        $query->orderBy('created_at', 'desc');

        // ✅ Now get the results
        $get_orders = $query->get();

        // ✅ Add created_date (formatted date from orders.created_at)
        $get_orders->transform(function ($order) {
            $order->created_date = $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : null;
            return $order;
        });
        if ($role === 'staff') {
            $userId = $user->branch_id;
        }
        // ✅ Currency settings
        $settings = DB::table('settings')->where('branch_id', $userId)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // $get_orders->transform(function ($order) {
        //     $order->created_date    = $order->created_at ? $order->created_at->format('Y-m-d') : null;
        //     $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);
        //     return $order;
        // });

        $get_orders->transform(function ($order) {
            // Convert UTC timestamp to IST and format
            $order->created_date = $order->created_at
                ? $order->created_at->format('d-M-Y h:i A')
                : null;

            $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);
            return $order;
        });
        // dd($get_orders);
        return response()->json([
            'status' => true,
            'message' => 'Orders fetched successfully',
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data' => $get_orders,
        ]);
    }

    public function get_orders_invoice(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;

        // ✅ Prefer request input over route param
        $selectedSubAdminID = $request->input('selectedSubAdminId');

        // ✅ Build the query
        $query = Order::with([
            'user:id,name,phone',
            'orderItems:id,order_id', // Only fetch id and order_id from orderItems (no created_at)
            'creator:id,name',
        ])
            ->where('isDeleted', 0)
            ->where('table_no', $request->table_id ?? 0)
            // ✅ Add subquery to check if payments exist
            ->withCount([
                'payments as has_payment' => function ($q) {
                    $q->where('isDeleted', 0);
                },
            ]);
        if ($role === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif ($role === 'admin' && $selectedSubAdminID) {
            $query->where('branch_id', $selectedSubAdminID);
        } elseif ($role === 'staff') {
            $query->where('created_by', $userId);
        } else {
            $query->where('branch_id', $userId);
        }
        // ✅ Apply date filter
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('created_at', $request->month);
            }
        }

        // ✅ Apply sorting
        $query->orderBy('created_at', 'desc');

        // ✅ Now get the results
        $get_orders = $query->get();

        // ✅ Add created_date (formatted date from orders.created_at)
        $get_orders->transform(function ($order) {
            $order->created_date = $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : null;
            return $order;
        });
        if ($role === 'staff') {
            $userId = $user->branch_id;
        }
        // ✅ Currency settings
        $settings = DB::table('settings')->where('branch_id', $userId)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // $get_orders->transform(function ($order) {
        //     $order->created_date    = $order->created_at ? $order->created_at->format('Y-m-d') : null;
        //     $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);
        //     return $order;
        // });

        $get_orders->transform(function ($order) {
            // Convert UTC timestamp to IST and format
            $order->created_date = $order->created_at
                ? $order->created_at->format('d-M-Y h:i A')
                : null;

            $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);
            return $order;
        });

        return response()->json([
            'status' => true,
            'message' => 'Orders fetched successfully',
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data' => $get_orders,
        ]);
    }

    public function get_salse_detail(Request $request)
    {
    }

    public function getsalseById($id, Request $request)
    {

        $authUser = Auth::guard('api')->user();
        // $subAdminId = session('selectedSubAdminId') ?? $authUser->id;
        $selectedSubAdminID = $request->input('selectedSubAdminId');

        // $setting    = Setting::where('branch_id', $subAdminId)->first();

        $sales = Order::with(['order_items.product.quantityType', 'user.userDetail'])->find($id);

        if (!$sales) {
            return response()->json(['status' => false, 'error' => 'Sale not found'], 404);
        }

        $companyInfo = Setting::where('branch_id', $selectedSubAdminID)->first()
            ?? Setting::where('branch_id', $authUser->id)->first();
        $taxIds = json_decode($sales->tax_id, true) ?? [];
        $paidAmount = PaymentStore::where('order_id', $sales->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');
        // dd($paidAmount);

        // ✅ Pending amount comes directly from orders table
        $pendingAmount = $sales->remaining_amount ?? 0;
        $taxDetails = TaxRate::whereIn('id', $taxIds)
            ->where('isDeleted', 0)
            ->where('status', 'active')
            ->get(['id', 'tax_name', 'tax_rate']);
        // ✅ Format created_at to "2025-10-29 11:46 AM" (IST)
        $formattedCreatedAt = $sales->created_at
            ? $sales->created_at->format('Y-m-d h:i A')
            : null;
        // Fetch order items with product & include date
            $orderItems = OrderItem::where('order_id', $id)
            ->with('product', 'quantityType')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => $item->product,
                    'category_id' => $item->category_id,
                    'quantity' => $item->quantity,
                    'quantity_type' => $item->quantityType,
                    'price' => $item->price,
                    'total_amount' => $item->total_amount,
                    'date' => $item->created_at
                        ? $item->created_at->timezone('Asia/Kolkata')->format('Y-m-d h:i A')
                        : null,
                ];
            });
        // dd($orderItems);

        return response()->json([
            'status' => true,
            'sales' => [
                ...$sales->toArray(),
                'created_at' => $formattedCreatedAt,
                'user_name' => $sales->user->name ?? '',
                'user_phone' => $sales->user->phone ?? '',
                'user_email' => $sales->user->email ?? '',
                'user_address' => optional($sales->user->userDetail ?? null)->address ?? '',
                'taxes' => $taxDetails,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
            ],
            'order_items' => $orderItems,
            'company_info' => $companyInfo,
            'currency_symbol' => $companyInfo?->currency_symbol ?? '₹',
            'currency_position' => $companyInfo?->currency_position ?? 'left',
        ], 200);
    }

    public function delete($id)
    {
        // Find the order
        $order = Order::find($id);

        // If order not found, return error
        if (!$order) {
            return response()->json([
                'status' => false,
                'error' => 'Order not found',
            ], 404);
        }

        // ❌ Prevent deletion if payment is pending
        if ($order->payment_status === 'pending') {
            return response()->json([
                'status' => false,
                'error' => 'This order cannot be deleted because its payment status is pending.',
            ], 400);
        }
        // Soft delete: set isDeleted = 1
        $order->isDeleted = 1;
        $order->save();

        // Soft delete related order items
        OrderItem::where('order_id', $order->id)->update(['isDeleted' => 1]);

        if (!empty($order->table_no) && is_numeric($order->table_no)) {
            $this->syncTableStatus((int) $order->table_no);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order deleted successfully.',
        ]);
    }

    public function update_sale(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'update_id' => 'required|exists:orders,id',
            'customer_id' => 'nullable',
            'customer_phone' => 'nullable|string',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:0',
            'discount' => 'numeric|min:0|max:100',
            // 'payment_method' => 'required|in:cash,debit,scan',
            // 'status' => 'required|in:completed,pending,failed',
            'grand_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }
        // dd($request->all());
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($request->update_id);
            $products = Product::whereIn('id', $request->product_ids)->get();

            // Fetch old order items
            $oldItems = OrderItem::where('order_id', $order->id)->get();
            $oldQuantities = $oldItems->pluck('quantity', 'product_id')->toArray();
            // dd($oldQuantities);
            // Calculate subtotal & adjust stock
            $subtotal = 0;
            foreach ($request->product_ids as $productId) {
                $product = $products->firstWhere('id', $productId);
                if ($product) {
                    $quantity = $request->quantities[$productId] ?? 0;
                    $oldQty = $oldQuantities[$productId] ?? 0;

                    $difference = $quantity - $oldQty;

                    // Check if stock is available for increment
                    if ($difference > 0 && $product->quantity < $difference) {
                        // Abort early with error
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Stock Quantity Exceeded. Only {$product->quantity} quantity are available for '{$product->name}'.",
                        ], 422);
                    }

                    // Stock adjustment
                    if ($difference > 0) {
                        $product->decrement('quantity', $difference);
                    } elseif ($difference < 0) {
                        $product->increment('quantity', abs($difference));
                    }

                    $subtotal += $product->price * $quantity;
                }
            }

            $userData = Auth::guard('api')->user();
            $userRole = $userData->role;
            if ($userRole == 'staff') {
                $branchIdToUse = $userData->branch_id;
            } elseif (!empty($request->selectedSubAdminId) && $userRole == 'admin') {
                $branchIdToUse = $request->selectedSubAdminId ?? null;
            } else {
                $branchIdToUse = $userData->id;
            }
            // Tax & discount calculations
            $discountAmount = $subtotal * ($request->discount / 100);
            $taxableAmount = $subtotal - $discountAmount;

            $totalTax = 0;
            $taxJson = null;

            // ✅ Only calculate taxes if GST is enabled
            if ($request->gst_option === 'with_gst') {
                $taxRates = TaxRate::where('status', 'active')
                    ->where('isDeleted', 0)
                    ->where('branch_id', $branchIdToUse)
                    ->get();

                $taxIds = $taxRates->pluck('id')->toArray();
                $taxJson = json_encode($taxIds);

                $totalTax = 0;
                foreach ($taxRates as $tax) {
                    $totalTax += $taxableAmount * ($tax->tax_rate / 100);
                }
            } else {
                $taxJson = json_encode([]);
            }

            $grandTotal = $taxableAmount + $totalTax;

            // If you track payments, subtract paid_amount; if not, just set = grandTotal
            $remainingAmount = $grandTotal;

            // Update Order
            $order->update([
                'user_name' => $request->customer_id,
                'user_phone' => $request->customer_phone,
                'payment_method' => 'pending',
                'discount' => $request->discount,
                'discount_amount' => $discountAmount,
                'gst_option' => $request->gst_option === 'with_gst' ? 'with_gst' : 'without_gst',
                'tax_id' => $taxJson,
                'tax_amount' => $totalTax,
                'subtotal' => $subtotal,
                'total_amount' => $grandTotal,
                'remaining_amount' => $remainingAmount,
                'payment_status' => 'pending',
                'order_type' => $request->order_type ?? $order->order_type,
            ]);
            // dd($order);

            // Remove old order items
            OrderItem::where('order_id', $order->id)->delete();

            // Reinsert updated order items
            foreach ($request->product_ids as $productId) {
                $product = $products->firstWhere('id', $productId);
                if ($product) {
                    $quantity = $request->quantities[$productId] ?? 0;
                    $quantity_type_id = $request->quantity_type_id[$productId] ?? null;
                    $price = $product->price;
                    $total = $price * $quantity;

                    try {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'user_id' => $order->user_id ?? null,
                            'category_id' => $product->category_id ?? null,
                            'product_id' => $product->id, // use $product->id to ensure valid integer
                            'quantity' => $quantity,
                            'quantity_type_id' => $quantity_type_id,
                            'price' => $price,
                            'total_amount' => $total,
                            'branch_id' => $branchIdToUse,
                            'created_by' => $userData->id,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error("Failed to insert OrderItem for product ID: " . $productId . " error: " . $e->getMessage());
                        throw $e;
                    }

                    $lastInventory = ProductInventory::where('product_id', $product->id)
                        ->orderBy('id', 'desc')
                        ->first();

                    ProductInventory::create([
                        'product_id' => $product->id,
                        'initial_stock' => $lastInventory->initial_stock ?? 0,
                        'current_stock' => $product->quantity, // already adjusted above
                        'branch_id' => $order->branch_id,
                        'create_by' => Auth::id(),
                        'type' => 'Update Sale',
                        'date' => now(),
                    ]);
                }
            }

            $paymentMethod = $request->payment_method ?? $order->payment_method ?? 'cash';

            if ($paymentMethod != 'pending') {

                if ($paymentMethod === 'cash+online') {

                    // Cash entry
                    if ($request->cash_amount > 0) {
                        PaymentStore::create([
                            'user_id' => $request->customer_id ?? ($order->user_id ?? 1),
                            'order_id' => $order->id,
                            'payment_amount' => $request->cash_amount,
                            'payment_date' => now(),
                            'payment_method' => 'cash',
                            'payment_type' => $request->paid_type ?? 'full',
                            'cash_amount' => $request->cash_amount,
                            'upi_amount' => 0,
                            'remaining_amount' => $request->pending_amount ?? 0,
                            'emi_month' => null,
                            'jobcard_id' => 0,
                            'isDeleted' => 0,
                            'created_at' => now(),
                        ]);
                    }

                    // Online entry
                    if ($request->online_amount > 0) {
                        PaymentStore::create([
                            'user_id' => $request->customer_id ?? ($order->user_id ?? 1),
                            'order_id' => $order->id,
                            'payment_amount' => $request->online_amount,
                            'payment_date' => now(),
                            'payment_method' => 'online',
                            'payment_type' => $request->paid_type ?? 'full',
                            'cash_amount' => 0,
                            'upi_amount' => $request->online_amount,
                            'remaining_amount' => $request->pending_amount ?? 0,
                            'emi_month' => null,
                            'jobcard_id' => 0,
                            'isDeleted' => 0,
                            'created_at' => now(),
                        ]);
                    }
                } else {
                    // Normal single payment entry
                    // For a plain cash payment, cash_amount should equal the payment amount
                    $editCashAmt = ($paymentMethod === 'cash')
                        ? ($request->cash_amount > 0 ? $request->cash_amount : ($request->total ?? 0))
                        : ($request->cash_amount ?? 0);

                    PaymentStore::create([
                        'user_id' => $request->customer_id ?? ($order->user_id ?? 1),
                        'order_id' => $order->id,
                        'payment_amount' => $request->total ?? 0,
                        'payment_date' => now(),
                        'payment_method' => $paymentMethod,
                        'payment_type' => $request->paid_type ?? 'full',
                        'cash_amount' => $editCashAmt,
                        'upi_amount' => $request->upi_amount ?? 0,
                        'remaining_amount' => $request->pending_amount ?? 0,
                        'emi_month' => null,
                        'jobcard_id' => 0,
                        'isDeleted' => 0,
                        'created_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function orderReport(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated access'], 401);
        }

        $userId = $user->id;
        $role = $user->role;
        $branchId = $user->branch_id;

        $effectiveBranchId = ($role === 'staff')
            ? $branchId
            : ($request->input('selectedSubAdminId') ?? $userId);

        $filter = $request->query('filter');
        $month = $request->query('month');
        $year = $request->query('year');
        $customerId = $request->query('customer_id');
        $categoryId = $request->query('category_id');

        $query = OrderItem::with(['product.category', 'product.brand', 'order'])
            ->whereHas('order', function ($q) use ($filter, $month, $year, $customerId, $role, $effectiveBranchId, $userId) {
                $q->where('payment_status', 'completed')->where('isDeleted', 0);

                // ✅ Role-based filtering
                if ($role === 'staff') {
                    $q->where('created_by', $userId);
                } else {
                    $q->where('branch_id', $effectiveBranchId);
                }

                // ✅ Date filters
                if ($filter) {
                    $today = Carbon::today();
                    switch ($filter) {
                        case 'this_week':
                            $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                            break;
                        case 'this_month':
                            $q->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year);
                            break;
                        case 'last_6_months':
                            $q->whereBetween('created_at', [Carbon::now()->subMonths(6), Carbon::now()]);
                            break;
                        case 'this_year':
                            $q->whereYear('created_at', $today->year);
                            break;
                        case 'previous_year':
                            $q->whereYear('created_at', Carbon::now()->subYear()->year);
                            break;
                    }
                }

                // ✅ Month filter
                if ($month) {
                    $q->whereMonth('created_at', $month);
                }

                // ✅ Year filter
                if ($year) {
                    $q->whereYear('created_at', $year);
                }

                // ✅ Customer filter
                if ($customerId) {
                    $q->where('user_id', $customerId);
                }

            });

        // ✅ Category filter (via product relation)
        if ($categoryId) {
            $query->whereHas('product', function ($p) use ($categoryId) {
                $p->where('category_id', $categoryId);
            });
        }

        // $orderItems = $query->get()->map(function ($item) {
        //     $product    = $item->product;
        //     $soldQty    = $item->quantity;
        //     $soldAmount = $item->price * $soldQty;

        //     return [
        //         'id'          => $item->id,
        //         'product_id'  => $product->id,
        //         'name'        => $product->name,
        //         'SKU'         => $product->SKU,
        //         'category'    => $product->category->name ?? 'N/A',
        //         'brand'       => $product->brand->name ?? 'N/A',
        //         'sold_qty'    => $soldQty,
        //         'sold_amount' => number_format($soldAmount, 2),
        //         'image'       => 'storage/' . (json_decode($product->images)[0] ?? 'admin/assets/img/product/noimage.png'),
        //     ];
        // });
        $orderItems = $query->get()->map(function ($item) {
            $product = $item->product;
            $soldQty = $item->quantity;
            $soldAmount = $item->price * $soldQty;

            // Decode product images
            $decodedImages = json_decode($product->images, true);
            $firstImage = $decodedImages[0] ?? 'admin/assets/img/product/noimage.png';

            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'SKU' => $product->SKU,
                'category' => $product->category->name ?? 'N/A',
                'brand' => $product->brand->name ?? 'N/A',
                'sold_qty' => $soldQty,
                'sold_amount' => number_format($soldAmount, 2),
                // Simple image path
                'image' => 'storage/' . $firstImage,
                // Uses accessor (returns full URLs)
                'image_url' => $product->image_url,
            ];
        });
        return response()->json(['status' => true, 'data' => $orderItems]);
    }

    // public function orderReport(Request $request)
    // {
    //     $user = Auth::guard('api')->user();

    //     if (!$user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Unauthenticated access',
    //         ], 401);
    //     }

    //     $userId  = $user->id;
    //     $role    = $user->role;
    //     $branchId = $user->branch_id;

    //     // ✅ Determine effective branch/sub-admin
    //     if ($role === 'staff') {
    //         $effectiveBranchId = $branchId; // staff belongs to branch_id
    //     } else {
    //         $effectiveBranchId = $request->input('selectedSubAdminId') ?? $userId;
    //     }

    //     // ✅ Fetch branch-specific settings
    //     $settings = DB::table('settings')
    //         ->where('branch_id', $effectiveBranchId)
    //         ->first();

    //     $currencySymbol   = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     $filter = $request->query('filter');

    //     // ✅ Main Query
    //     $orderItems = OrderItem::with(['product.category', 'product.brand', 'order'])
    //         ->whereHas('order', function ($query) use ($filter, $role, $effectiveBranchId, $userId) {
    //             $query->where('payment_status', 'completed')
    //                 ->where('isDeleted', 0);

    //             // ✅ Role-based filtering
    //             if ($role === 'staff') {
    //                 $query->where('created_by', $userId);
    //             } else {
    //                 $query->where('branch_id', $effectiveBranchId);
    //             }

    //             // ✅ Date range filters
    //             if ($filter) {
    //                 $today = Carbon::today();
    //                 switch ($filter) {
    //                     case 'this_week':
    //                         $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    //                         break;
    //                     case 'this_month':
    //                         $query->whereMonth('created_at', $today->month)
    //                             ->whereYear('created_at', $today->year);
    //                         break;
    //                     case 'last_6_months':
    //                         $query->whereBetween('created_at', [Carbon::now()->subMonths(6), Carbon::now()]);
    //                         break;
    //                     case 'this_year':
    //                         $query->whereYear('created_at', $today->year);
    //                         break;
    //                     // case 'previous_year':
    //                     //     $query->whereYear('created_at', $today->subYear()->year);
    //                     //     break;
    //                     case 'previous_year':
    //                         $query->whereYear('created_at', Carbon::now()->subYear()->year);
    //                         break;
    //                 }
    //             }
    //         })
    //         ->select('id', 'product_id', 'quantity', 'price', 'order_id')
    //         ->get()
    //         ->map(function ($orderItem) use ($currencySymbol, $currencyPosition) {
    //             $product = $orderItem->product;

    //             $soldQty    = $orderItem->quantity;
    //             $soldAmount = $orderItem->price * $soldQty;
    //             $created_at = optional($orderItem->order)->created_at;

    //             $imageList = json_decode($product->images);
    //             $imagePath = $imageList[0] ?? null;
    //             $imageUrl  = $imagePath ? 'storage/' . $imagePath : 'admin/assets/img/product/noimage.png';

    //             $formattedAmount     = number_format($soldAmount, 2);
    //             $soldAmountFormatted = $currencyPosition === 'left'
    //                 ? $currencySymbol . $formattedAmount
    //                 : $formattedAmount . $currencySymbol;

    //             return [
    //                 'id'          => $orderItem->id,
    //                 'product_id'  => $product->id,
    //                 'name'        => $product->name,
    //                 'SKU'         => $product->SKU,
    //                 'category'    => $product->category->name ?? 'N/A',
    //                 'brand'       => $product->brand->name ?? 'N/D',
    //                 'sold_amount' => $soldAmountFormatted,
    //                 'sold_qty'    => $soldQty,
    //                 'instock_qty' => $product->quantity,
    //                 'image'       => $imageUrl,
    //                 'image_url'   => $product->image_url,
    //                 'created_at'  => $created_at,
    //             ];
    //         });

    //     return response()->json([
    //         'status'           => true,
    //         'data'             => $orderItems,
    //         'currencySymbol'   => $currencySymbol,
    //         'currencyPosition' => $currencyPosition,
    //     ]);
    // }

    // public function getFilteredOrders(Request $request)
    // {
    //     $user = Auth::guard('api')->user();

    //     if (! $user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Unauthenticated access',
    //         ], 401);
    //     }

    //     $role   = $user->role;
    //     $userId = $user->id;

    //     // If staff, use branch_id instead of own id
    //     if ($role === 'staff') {
    //         $branchId = $user->branch_id;
    //     } elseif ($role === 'admin' && $request->filled('selectedSubAdminId')) {
    //         $branchId = $request->query('selectedSubAdminId');
    //     } else {
    //         $branchId = $userId; // sub-admin or fallback
    //     }

    //     // ✅ Branch-specific settings
    //     $settings         = DB::table('settings')->where('branch_id', $branchId)->first();
    //     $currencySymbol   = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     try {
    //         $query = Order::with('user')
    //             ->withCount([
    //                 'payments as has_payment' => function ($q) {
    //                     $q->where('isDeleted', 0);
    //                 },
    //             ])
    //             ->where('isDeleted', 0);

    //         // ✅ Always filter by branch
    //         $query->where('branch_id', $branchId);

    //         // ✅ Filter by selected date (format: YYYY-MM-DD)
    //         if ($request->filled('date')) {
    //             $query->whereDate('created_at', $request->date);
    //         }

    //         // ✅ Apply sorting
    //         $orders = $query->orderBy('created_at', 'desc')->get();

    //         // ✅ Add formatted date
    //         $orders->transform(function ($order) {
    //             $order->created_date = optional($order->created_at)->format('Y-m-d');
    //             return $order;
    //         });

    //         return response()->json([
    //             'status'            => true,
    //             'data'              => $orders,
    //             'currency_symbol'   => $currencySymbol,
    //             'currency_position' => $currencyPosition,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'            => false,
    //             'currency_symbol'   => $currencySymbol,
    //             'currency_position' => $currencyPosition,
    //             'message'           => 'Something went wrong',
    //             'error'             => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function getFilteredOrders(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $role = $user->role;
        $userId = $user->id;

        // Decide branch / owner filter
        if ($role === 'staff') {
            // Staff: only orders they created
            $query = Order::with('user')
                ->withCount([
                    'payments as has_payment' => function ($q) {
                        $q->where('isDeleted', 0);
                    },
                ])
                ->where('isDeleted', 0)
                ->where('created_by', $userId); // ✅ staff-created orders only
        } else {
            // Admin / sub-admin: filter by branch
            $branchId = ($role === 'admin' && $request->filled('selectedSubAdminId'))
                ? $request->query('selectedSubAdminId')
                : $userId;

            $query = Order::with('user')
                ->withCount([
                    'payments as has_payment' => function ($q) {
                        $q->where('isDeleted', 0);
                    },
                ])
                ->where('isDeleted', 0)
                ->where('branch_id', $branchId);
        }

        // Filter by selected date if provided
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        try {
            $orders = $query->orderBy('created_at', 'desc')->get();

            // Add formatted date
            $orders->transform(function ($order) {
                $order->created_date = optional($order->created_at)->format('d-M-Y h:i A');
                return $order;
            });

            // Get branch-specific settings
            $settings = DB::table('settings')->where('branch_id', $user->branch_id ?? $userId)->first();
            $currencySymbol = $settings->currency_symbol ?? '₹';
            $currencyPosition = $settings->currency_position ?? 'left';

            return response()->json([
                'status' => true,
                'data' => $orders,
                'currency_symbol' => $currencySymbol,
                'currency_position' => $currencyPosition,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'currency_symbol' => $currencySymbol ?? '₹',
                'currency_position' => $currencyPosition ?? 'left',
            ], 500);
        }
    }

    public function getHistory1($order_id)
    {
        $history = PaymentStore::where('order_id', $order_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history,
        ]);
    }

    public function exportOrders(Request $request)
    {
        // $user = auth()->user();
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;
        // Sub-admin id (from dropdown or session fallback)
        $selectedSubAdminId = $request->query('selectedSubAdminId');
        if ($selectedSubAdminId === 'null' || trim($selectedSubAdminId) === '') {
            $selectedSubAdminId = null;
        }
        $selectedSubAdminId = $selectedSubAdminId ?? session('selectedSubAdminId') ?? $user->id;

        $year = $request->query('year');
        $month = $request->query('month');
        $date = $request->query('date');
        $customerId = $request->query('customerId');

        $settings = DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        try {
            $query = Order::with(['user:id,name,phone'])
                ->where('isDeleted', 0);
            // ->where('type', 'Sales');

            // 🔹 Year filter
            if (!empty($year)) {
                $query->whereYear('created_at', $year);
            }

            // 🔹 Month filter
            if (!empty($month)) {
                $query->whereMonth('created_at', $month);
            }

            // 🔹 Exact Date filter
            if (!empty($date)) {
                // Convert DD-MM-YYYY to YYYY-MM-DD
                $dateParts = explode('-', $date);
                if (count($dateParts) === 3) {
                    $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                    $query->whereDate('created_at', $formattedDate);
                }
            }

            // 🔹 Customer filter
            if ($request->filled('customerId')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', $request->customerId);
                });
            }

            // 🔹 Role wise filter
            if ($role === 'sub-admin') {
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin' && $selectedSubAdminId) {
                $query->where('branch_id', $selectedSubAdminId);
            } elseif ($role === 'staff') {
                $query->where('created_by', $userId);
            } else {
                $query->where('branch_id', $userId);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            // Generate Excel same as before...
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Order Number');
            $sheet->setCellValue('B1', 'Date');
            $sheet->setCellValue('C1', 'Customer Name');
            $sheet->setCellValue('D1', 'Total Amount');
            $sheet->setCellValue('E1', 'Remaining Amount');
            $sheet->setCellValue('F1', 'Payment Status');
            $sheet->setCellValue('G1', 'Payment Method');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);

            $row = 2;
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $order->order_number ?? 'N/A');
                $sheet->setCellValue('B' . $row, $order->created_at->format('Y-m-d'));
                $sheet->setCellValue('C' . $row, $order->user->name ?? 'N/A');
                $sheet->setCellValue('D' . $row, $order->total_amount ?? 0);
                $sheet->setCellValue('E' . $row, $order->remaining_amount ?? 0);
                $sheet->setCellValue('F' . $row, $order->payment_status ?? 'N/A');
                $sheet->setCellValue('G' . $row, $order->payment_method ?? 'N/A');
                $row++;
            }

            // $writer   = new Xlsx($spreadsheet);
            // $fileName = 'Sales_' . date('Ymd_His') . '.xlsx';

            // return response()->streamDownload(function () use ($writer) {
            //     $writer->save('php://output');
            // }, $fileName, [
            //     'Content-Type'                  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //     'Access-Control-Expose-Headers' => 'Content-Disposition',
            // ]);

            // Save to public storage
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'Sales_' . date('Ymd_His') . '.xlsx';
            $relativePath = 'exports/' . $filename;

            // Save temporary file
            $temp_file = tempnam(sys_get_temp_dir(), 'excel');
            $writer->save($temp_file);
            \Storage::disk('public')->put($relativePath, file_get_contents($temp_file));
            unlink($temp_file);

            // Generate public URL

            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status' => true,
                'message' => 'Sales Excel generated successfully.',
                'file_url' => $fileUrl,
                'file_name' => $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function salse_invoice_pdf_download($id)
    {
        $view_id = $id;
        $sales = Order::find($view_id);
        $authUser = Auth::guard('api')->user();
        $subAdminId = session('selectedSubAdminId') ?? $authUser->id;
        $setting = Setting::where('branch_id', $subAdminId)->first();

        if (!$sales) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Fetch user data (order belongs to which user)
        $user = $sales->user_id ? User::find($sales->user_id) : null;

        // Helper for currency formatting
        $formatCurrency = function ($amount) use ($setting) {
            $amount = number_format($amount, 2);
            return $setting->currency_position === 'right'
                ? $amount . $setting->currency_symbol
                : $setting->currency_symbol . $amount;
        };

        // ✅ Subtotal
        $orderItems = OrderItem::where('order_id', $view_id)->get();
        $subtotal = $orderItems->sum('total_amount');

        // ✅ Discount
        $discountPercent = $sales->discount ?? 0;
        $discountAmount = ($discountPercent / 100) * $subtotal;
        $afterDiscount = $subtotal - $discountAmount;

        // ✅ Tax calculation
        $taxDetails = [];
        if ($sales->gst_option === 'with_gst') {
            $taxIds = json_decode($sales->tax_id, true) ?? [];
            if (!empty($taxIds)) {
                $taxes = TaxRate::whereIn('id', $taxIds)->get();
                foreach ($taxes as $tax) {
                    $taxAmount = ($tax->tax_rate / 100) * $afterDiscount;
                    $taxDetails[] = [
                        'name' => $tax->tax_name,
                        'rate' => $tax->tax_rate,
                        'amount' => $taxAmount,
                        'formatted_amount' => $formatCurrency($taxAmount),
                    ];
                }
            }
        }

        // ✅ Final total
        $finalTotal = $afterDiscount + collect($taxDetails)->sum('amount');

        // ✅ Prepare formatted values
        $formattedSubtotal = $formatCurrency($subtotal);
        $formattedDiscountAmount = $formatCurrency($discountAmount);
        $formattedAfterDiscount = $formatCurrency($afterDiscount);
        $formattedFinalTotal = $formatCurrency($finalTotal);

        // ✅ Prepare data for PDF view
        $pdfData = [
            'view_id' => $view_id,
            'sales' => $sales,
            'setting' => $setting,
            'orderItems' => $orderItems,
            'salesItems' => $orderItems,
            'customer' => [
                'name' => $user->name ?? 'walk-in-customer',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'address' => optional($user->userDetail)->address ?? 'arga',
            ],
            'user' => $user ? $user->toArray() : null,
            'subtotal' => $formattedSubtotal,
            'discountPercent' => $discountPercent,
            'discountAmount' => $formattedDiscountAmount,
            'afterDiscount' => (float) $afterDiscount,
            'formattedAfterDiscount' => $formattedAfterDiscount,
            'finalTotal' => $formattedFinalTotal,
            'taxDetails1' => $taxDetails,
        ];

        // ✅ Choose PDF view based on setting
        $viewName = (isset($setting->invoice_size) && $setting->invoice_size === 'small') 
            ? 'sales.salse-invoice-small-pdf' 
            : 'sales.salse-invoice-pdf';

        // ✅ Generate PDF
        $pdf = PDF::loadView($viewName, $pdfData);

        // ✅ Adjust paper size for small thermal printing
        if (isset($setting->invoice_size) && $setting->invoice_size === 'small') {
            // 88mm width, 200mm height
            $pdf->setPaper([0, 0, 249.45, 566.93], 'portrait');
        } else {
            $pdf->setPaper('A4', 'portrait');
        }

        // ✅ Save PDF to storage (public folder)
        $fileName = 'invoice_' . $view_id . '.pdf';
        // $filePath = '/storage/app/public/sales-invoices/' . $fileName;

        // Ensure directory exists
        // if (!file_exists(storage_path('/storage/app/public/sales-invoices/'))) {
        //     mkdir(storage_path('/storage/app/public/sales-invoices/'), 0777, true);
        // }

        $relativePath = 'sales-invoices/' . $fileName;
        \Storage::disk('public')->put($relativePath, $pdf->output());

        // Public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status' => true,
            'message' => 'Sales Invoice PDF generated successfully.',
            'file_url' => $fileUrl,
            'file_name' => $fileName,
        ]);
    }
    // public function exportOrdersPDF(Request $request)
    // {
    //     // $user = auth()->user();
    //     $user = Auth::guard('api')->user();

    //     if (! $user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Unauthenticated access',
    //         ], 401);
    //     }

    //     $userId = $user->id;
    //     $role   = $user->role;

    //     // Sub-admin id (from dropdown or session fallback)
    //     $selectedSubAdminId = $request->query('selectedSubAdminId') ?? session('selectedSubAdminId') ?? $user->id;

    //     // ✅ branch-wise setting
    //     $setting = Setting::where('branch_id', $selectedSubAdminId)->first();

    //     try {
    //         $query = Order::with(['user:id,name,phone'])
    //             ->where('isDeleted', 0);
    //         // ->where('type', 'Sales');

    //         // 🔹 Year filter
    //         if ($request->filled('year')) {
    //             $query->whereYear('created_at', $request->year);
    //         }

    //         // 🔹 Month filter
    //         if ($request->filled('month')) {
    //             $query->whereMonth('created_at', $request->month);
    //         }

    //         // 🔹 Exact Date filter (DD-MM-YYYY or YYYY-MM-DD)
    //         if ($request->filled('date')) {
    //             $inputDate = trim($request->date);
    //             if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $inputDate)) {
    //                 [$day, $month, $year] = explode('-', $inputDate);
    //                 $formattedDate        = "$year-$month-$day";
    //             } else {
    //                 $formattedDate = $inputDate;
    //             }
    //             $query->whereDate('created_at', $formattedDate);
    //         }

    //         // 🔹 Customer filter
    //         if ($request->filled('customerId')) {
    //             $query->whereHas('user', function ($q) use ($request) {
    //                 $q->where('name', $request->customerId);
    //             });
    //         }

    //         if ($role === 'sub-admin') {
    //             $query->where('branch_id', $userId);
    //         } elseif ($role === 'admin' && $selectedSubAdminId) {
    //             $query->where('branch_id', $selectedSubAdminId);
    //         } elseif ($role === 'staff') {
    //             $query->where('created_by', $userId);
    //         } else {
    //             $query->where('branch_id', $userId);
    //         }
    //         // dd( $selectedSubAdminId);

    //         $orders = $query->orderBy('created_at', 'desc')->get();

    //         // ✅ load PDF view
    //         $pdf = Pdf::loadView('sales.orders_pdf', [
    //             'orders'  => $orders,
    //             'setting' => $setting,
    //         ])->setPaper('A4', 'landscape');

    //         return $pdf->download('Sales_' . date('Ymd_His') . '.pdf');
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Something went wrong',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function exportOrdersPDF(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;
        $userBranchId = $user->branch_id;
        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userId;

        // 🔹 Fetch branch-wise setting
        // $setting = Setting::where('branch_id', $selectedSubAdminId)->first();
        if ($role === 'staff' && $userBranchId) {
            $branchIdToUse = $userBranchId;
        } elseif ($role === 'admin' && !empty($selectedSubAdminId)) {
            $branchIdToUse = $selectedSubAdminId;
        } elseif ($role === 'sub-admin') {
            $branchIdToUse = $userId;
        } else {
            $branchIdToUse = $userId;
        }

        // 🔹 Fetch settings branch-wise
        $setting = Setting::where('branch_id', $branchIdToUse)->first();

        try {
            $query = Order::with(['user:id,name,phone'])
                ->where('isDeleted', 0);

            // 🔹 Apply filters
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            if ($request->filled('date')) {
                $inputDate = trim($request->date);
                $formattedDate = preg_match('/^\d{2}-\d{2}-\d{4}$/', $inputDate)
                    ? implode('-', array_reverse(explode('-', $inputDate)))
                    : $inputDate;
                $query->whereDate('created_at', $formattedDate);
            }
            if ($request->filled('customerId')) {
                $query->whereHas('user', fn($q) => $q->where('name', $request->customerId));
            }

            // 🔹 Role-based branch filtering
            if ($role === 'sub-admin') {
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin' && $selectedSubAdminId) {
                $query->where('branch_id', $selectedSubAdminId);
            } elseif ($role === 'staff') {
                $query->where('created_by', $userId);
            } else {
                $query->where('branch_id', $userId);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No orders found for the given filters.',
                ], 404);
            }

            // 🔹 Generate PDF
            $pdf = Pdf::loadView('sales.orders_pdf', [
                'orders' => $orders,
                'setting' => $setting,
            ])->setPaper('A4', 'landscape');

            // 🔹 Save PDF to storage
            $fileName = 'sales_report_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'sales-reports/' . $fileName;
            \Storage::disk('public')->put($relativePath, $pdf->output());

            // 🔹 Generate full URL

            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            // 🔹 Return JSON response
            return response()->json([
                'status' => true,
                'message' => 'Sales report PDF generated successfully.',
                'file_url' => $fileUrl,
                'file_name' => $fileName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while generating PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function export_sales_report_pdf_api(Request $request)
    {
        try {
            $authUser = Auth::guard('api')->user();
            $selectedSubAdminId = $request->selectedSubAdminId ?? null;

            // 🔹 Determine branch_id based on role
            if ($authUser->role === 'staff' && $authUser->branch_id) {
                $branchIdToUse = $authUser->branch_id;
            } elseif ($authUser->role === 'admin' && !empty($selectedSubAdminId)) {
                $branchIdToUse = $selectedSubAdminId;
            } else {
                $branchIdToUse = $authUser->id;
            }

            // 🔹 Get all IDs from form-data (ids[] = 1, ids[] = 2, etc.)
            $idsArray = $request->input('ids', []);

            // 🔹 Store all IDs as comma-separated string (for logging/debugging)
            $idsString = implode(',', $idsArray);

            if (empty($idsArray)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sales IDs provided.',
                ]);
            }

            // 🔹 Load related models
            $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
                ->whereIn('id', $idsArray)
                ->get();

            if ($sales->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sales data found.',
                ]);
            }

            // 🔹 Settings and currency info
            $setting = Setting::where('branch_id', $branchIdToUse)->first();
            $currencySymbol = $setting->currency_symbol ?? '₹';
            $currencyPosition = $setting->currency_position ?? 'left';

            $subtotal = $sales->sum('total_amount');
            $discountAmount = 0;

            // 🔹 Discount calculation
            foreach ($sales as $sale) {
                if ($sale->invoice && $sale->invoice->discount) {
                    $discountPercent = $sale->invoice->discount;
                    $discountAmount += ($sale->total_amount * $discountPercent) / 100;
                }
            }

            $subtotalAfterDiscount = $subtotal - $discountAmount;

            // 🔹 Tax calculation
            $taxDetails = [];
            $totalTaxAmount = 0;

            foreach ($sales as $sale) {
                $rowTaxes = [];
                $rowTaxAmount = 0;
                $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
                $rowTaxIds = $sale->invoice->tax_id ?? '[]';
                $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

                $rowTaxRates = collect();
                if ($rowGSTOption === 'with_gst' && !empty($rowTaxIdsArray)) {
                    $rowTaxRates = TaxRate::where('status', 'active')
                        ->where('branch_id', $branchIdToUse)
                        ->where('isDeleted', 0)
                        ->whereIn('id', $rowTaxIdsArray)
                        ->get();
                }

                $unitPrice = $sale->price;

                // Apply discount per unit
                if ($sale->invoice && $sale->invoice->discount) {
                    $discountPercent = $sale->invoice->discount;
                    $unitPrice -= ($unitPrice * $discountPercent) / 100;
                }

                foreach ($rowTaxRates as $tax) {
                    $taxBase = $unitPrice * $sale->quantity;
                    $amount = $taxBase * ($tax->tax_rate / 100);

                    $rowTaxes[] = [
                        'name' => $tax->tax_name,
                        'rate' => $tax->tax_rate,
                        'amount' => $amount,
                    ];

                    $rowTaxAmount += $amount;

                    if (!isset($taxDetails[$tax->id])) {
                        $taxDetails[$tax->id] = [
                            'name' => $tax->tax_name,
                            'rate' => $tax->tax_rate,
                            'amount' => 0,
                        ];
                    }
                    $taxDetails[$tax->id]['amount'] += $amount;
                }

                $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;
                $totalTaxAmount += $rowTaxAmount;
            }

            $totalAmount = $sales->sum('rowFinalTotal');

            // 🔹 Prepare PDF data
            $pdfData = [
                'sales' => $sales,
                'setting' => $setting,
                'currencySymbol' => $currencySymbol,
                'currencyPosition' => $currencyPosition,
                'subtotal' => $subtotal,
                'discountAmount' => $discountAmount,
                'taxDetails' => $taxDetails,
                'totalTaxAmount' => $totalTaxAmount,
                'totalAmount' => $totalAmount,
            ];

            // 🔹 Generate PDF
            $pdf = PDF::loadView('sales.sales-invoice-report-pdf', $pdfData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                ]);

            // 🔹 Save PDF file in storage
            $fileName = 'sales_report_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'sales-reports/' . $fileName;

            \Storage::disk('public')->put($relativePath, $pdf->output());

            // 🔹 Get full URL
            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status' => true,
                'message' => 'Sales Report PDF generated successfully.',
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'ids_used' => $idsString, // 👈 for debug
            ]);

        } catch (\Throwable $e) {
            \Log::error('Sales PDF Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate Sales Report PDF.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function view_sales_report(Request $request)
    {
        try {
            $ids = $request->input('ids'); // can be array or comma-separated string
            $selectedSubAdminId = $request->input('selectedSubAdminId');

            if (empty($ids)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sales selected for report view.',
                ], 400);
            }

            // Convert to array if comma-separated
            $idsArray = is_array($ids) ? $ids : explode(',', $ids);

            // 🔹 Store IDs in a comma-separated string for URL
            $idsString = implode(',', $idsArray);

            // 🔹 Determine branch_id (safe fallback)
            // If user is logged in, we can still read role info
            $authUser = Auth::guard('api')->user();
            if ($authUser) {
                if ($authUser->role === 'staff' && $authUser->branch_id) {
                    $branchIdToUse = $authUser->branch_id;
                } elseif ($authUser->role === 'admin' && !empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } else {
                    $branchIdToUse = $authUser->id;
                }
            } else {
                // 🔹 No authentication — require branch ID from frontend
                if (!empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Branch ID missing (unauthenticated request).',
                    ], 400);
                }
            }

            // ✅ Generate the report URL
            // $reportUrl = url('sales/report/view-page?ids=' . urlencode($idsString) . '&branch=' . $branchIdToUse);
            $reportUrl = url('sales/report/view-page?ids=' . $idsString . '&branch=' . $branchIdToUse);

            return response()->json([
                'status' => true,
                'message' => 'Sales report link generated successfully.',
                'view_link' => $reportUrl,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate sales report link.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update_order_type(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_type' => 'required|in:Delivery,Self Pickup'
        ]);
        
        $order = Order::find($request->order_id);
        if ($order) {
            $order->order_type = $request->order_type;
            $order->save();
            return response()->json(['status' => true, 'message' => 'Order type updated successfully.']);
        }
        return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
    }
}
