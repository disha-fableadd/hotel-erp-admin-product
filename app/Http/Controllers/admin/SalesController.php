<?php

namespace App\Http\Controllers\admin;

use App\Models\Table_no;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\PaymentStore;
use App\Models\TaxRate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\QuantityType;
use App\Models\Setting;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{

    public function sales_list(Request $request)
    {
        $selectedSubAdminId = (session('selectedSubAdminId'));

        $years = Order::where('isDeleted', 0)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // Get staff members for the current user's branch
        $user = Auth::user();
        $staffList = collect();

        if ($user->role === 'admin') {
            // Admin can see all staff or filter by selected branch
            $staffQuery = User::where('role', 'staff')->where('isDeleted', 0);
            if (!empty($selectedSubAdminId)) {
                $staffQuery->where('branch_id', $selectedSubAdminId);
            }
            $staffList = $staffQuery->select('id', 'name', 'branch_id')->get();
        } elseif ($user->role === 'sub-admin') {
            // Sub-admin can see staff from their branch
            $staffList = User::where('branch_id', $user->id)
                ->where('role', 'staff')
                ->where('isDeleted', 0)
                ->select('id', 'name', 'branch_id')->get();
        }

        return view('sales/saleslist', compact('years', 'staffList'));
    }

    public function updateOrderType(Request $request, $id)
    {
        $request->validate([
            'order_type' => 'required|in:self_pickup,delivery'
        ]);

        $order = Order::findOrFail($id);
        $order->order_type = $request->order_type;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Order type updated successfully!']);
    }

    public function assignStaff(Request $request, $id)
    {
        $request->validate([
            'assigned_staff_id' => 'nullable|exists:users,id'
        ]);

        $order = Order::findOrFail($id);
        $order->assigned_staff_id = $request->assigned_staff_id;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Staff assigned successfully.']);
    }

    public function deliveryDetails($id)
    {
        $order = Order::with(['order_items.product', 'deliveries.items', 'deliveries.user', 'user'])->findOrFail($id);
        
        $setting = Setting::first();
        $currencySymbol = $setting->currency_symbol ?? '₹';

        $totalOrdered = 0;
        $totalDelivered = 0;

        foreach ($order->order_items as $item) {
            $delivered = $order->deliveries->flatMap->items->where('product_id', $item->product_id)->sum('delivered_qty');
            $item->delivered_qty = $delivered;
            $item->remaining_qty = $item->quantity - $delivered;
            
            $totalOrdered += $item->quantity;
            $totalDelivered += $delivered;
        }

        return view('sales.delivery', compact('order', 'setting', 'currencySymbol'));
    }

    public function submitDelivery(Request $request, $id)
    {
        $order = Order::with(['order_items'])->findOrFail($id);
        
        $request->validate([
            'deliveries' => 'required|array',
            'deliveries.*.product_id' => 'required|exists:products,id',
            'deliveries.*.qty' => 'required|integer|min:0',
        ]);

        $deliveryRecords = [];
        $hasItemsToDeliver = false;

        foreach ($request->deliveries as $del) {
            if ($del['qty'] > 0) {
                $hasItemsToDeliver = true;
                $deliveryRecords[] = [
                    'product_id' => $del['product_id'],
                    'delivered_qty' => $del['qty']
                ];
            }
        }

        if (!$hasItemsToDeliver) {
            return redirect()->back()->with('error', 'Please enter a delivery quantity for at least one item.');
        }

        // Create Delivery Record
        $delivery = Delivery::create([
            'order_id' => $order->id,
            'delivered_by' => Auth::id(),
            'branch_id' => Auth::user()->branch_id,
            'challan_number' => $order->order_invoice, // or a new challan number sequence
            'delivered_at' => now(),
        ]);

        foreach ($deliveryRecords as $rec) {
            DeliveryItem::create([
                'delivery_id' => $delivery->id,
                'product_id' => $rec['product_id'],
                'delivered_qty' => $rec['delivered_qty']
            ]);
        }

        // Recalculate delivery status
        $order->load(['deliveries.items']);
        $totalItems = $order->order_items->count();
        $fullyDeliveredItems = 0;
        $partiallyDeliveredItems = 0;

        foreach ($order->order_items as $item) {
            $delivered = $order->deliveries->flatMap->items->where('product_id', $item->product_id)->sum('delivered_qty');
            if ($delivered > 0) {
                if ($delivered >= $item->quantity) {
                    $fullyDeliveredItems++;
                } else {
                    $partiallyDeliveredItems++;
                }
            }
        }

        if ($fullyDeliveredItems == $totalItems) {
            $order->delivery_status = 'delivered';
        } elseif ($fullyDeliveredItems > 0 || $partiallyDeliveredItems > 0) {
            $order->delivery_status = 'partially_delivered';
        } else {
            $order->delivery_status = 'pending';
        }
        $order->save();

        return redirect()->route('sales.list')->with('success', 'Delivery updated successfully.');
    }

    public function add_sales(Request $request)
    {

        return view('sales/add-sales');
    }
    public function edit_sales($id)
    {
        $user = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // ðŸ”¹ Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && !empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        // ðŸ”¹ Load sales record
        $sales = Order::with(['order_items.product', 'order_items.product.quantityType' => function ($q) {
            $q->select('*'); // ensures all fields
        }])->find($id);
        if (!$sales) {
            return redirect()->route('sales.list')->with('error', 'Sales record not found.');
        }

        $update_id = $id;

        // ðŸ”¹ Load related data
        $usernames = User::where('role', 'customer')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();

        $category = Category::where('isDeleted', 0)
            ->get();

        $quantity_type = QuantityType::where('isDeleted', 0)
            ->get();

        $TaxRate = TaxRate::where('status', 'active')->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        $products = Product::where('status', 'active')
            ->where('availablility', 'in_stock')
            ->where('branch_id', $branchIdToUse)
            ->get();

        // ðŸ”¹ Create products array with quantity type information
        $productsArray = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'price' => $product->price,
                'quantity_type_id' => $product->quantity_type_id, // Add this
            ];
        });
        // dd($sales);
        // ðŸ”¹ Get staff for assigned staff dropdown based on branch
        $staffList = User::where('role', 'staff')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();

        $setting = Setting::where('branch_id', $branchIdToUse)->first();

        return view('sales/edit-sales', compact('sales', 'TaxRate', 'category', 'quantity_type', 'usernames', 'products', 'update_id', 'setting', 'productsArray', 'staffList'));
    }
    // public function sales_details($id)
    // {
    //     $setting = Setting::first();

    //     $currencySymbol = $setting->currency_symbol ?? 'â‚¹';
    //     $currencyPosition = $setting->currency_position ?? 'left';

    //     $sales = Order::find($id);

    //     if (!$sales) {
    //         return redirect()->route('sales.list')->with('error', 'Order not found.');
    //     }

    //     $compenyinfo = Setting::all();

    //     $taxIds = json_decode($sales->tax_id, true);
    //     $taxes = TaxRate::whereIn('id', $taxIds)->get();

    //     $orderItems = OrderItem::where('order_id', $id)->get();
    //     $totalAmount = $orderItems->sum('total_amount');

    //     $view_id = $id; // define view_id for blade

    //     return view('sales/sales-details', compact(
    //         'view_id',
    //         'sales',
    //         'taxes',
    //         'totalAmount',
    //         'compenyinfo',
    //         'setting',
    //         'currencySymbol',
    //         'currencyPosition'
    //     ));
    // }

    // public function sales_details($id)
    // {

    //     $setting = Setting::first();

    //     $currencySymbol = $setting->currency_symbol ?? 'â‚¹';
    //     $currencyPosition = $setting->currency_position ?? 'left';

    //     $sales = Order::find($id);

    //     if (!$sales) {
    //         return redirect()->route('sales.list')->with('error', 'Order not found.');
    //     }

    //     $compenyinfo = Setting::all();

    //     $taxIds = json_decode($sales->tax_id, true) ?? [];
    //     $taxes = TaxRate::whereIn('id', $taxIds)->get();

    //     $orderItems = OrderItem::where('order_id', $id)->get();
    //     $totalAmount = $orderItems->sum('total_amount');

    //     $view_id = $id; // define view_id for blade

    //     // âœ… Fetch customer/user details and push into $sales
    //     $user = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;

    //     if ($user) {
    //         $sales->customer_role       = ucfirst($user->role ?? 'Customer');
    //         $sales->customer_name       = $user->name ?? 'Walk-in Customer';
    //         $sales->customer_email      = $user->email ?? '';
    //         $sales->customer_phone      = $user->phone ?? '';
    //         $sales->customer_address    = optional($user->userDetail)->address ?? '';
    //         $sales->customer_city       = optional($user->userDetail)->city ?? '';
    //         $sales->customer_country    = optional($user->userDetail)->country ?? '';
    //         $sales->customer_gst_number = optional($user->userDetail)->gst_number ?? '';
    //         $sales->customer_pan_number = optional($user->userDetail)->pan_number ?? '';
    //     } else {
    //         // default values if no user is linked
    //         $sales->customer_role       = 'Customer';
    //         $sales->customer_name       = 'Walk-in Customer';
    //         $sales->customer_email      = '';
    //         $sales->customer_phone      = '';
    //         $sales->customer_address    = '';
    //         $sales->customer_city       = '';
    //         $sales->customer_country    = '';
    //         $sales->customer_gst_number = '';
    //         $sales->customer_pan_number = '';
    //     }

    //     // dd($sales);

    //     return view('sales/sales-details', compact(
    //         'view_id',
    //         'sales',
    //         'TaxRate',
    //         'category',
    //         'usernames',
    //         'products',
    //         'update_id',
    //         'setting'
    //     ));
    // }

    public function sales_details($id)
    {
        $user = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // ðŸ”¹ Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && !empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        // ðŸ”¹ Get branch-specific setting
        $setting = Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol = $setting->currency_symbol ?? 'â‚¹';
        $currencyPosition = $setting->currency_position ?? 'left';

        // ðŸ”¹ Load sales with order items + products
        $sales = Order::with(['order_items.product'])->find($id);

        if (!$sales) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }

        // ðŸ”¹ Company info (branch-specific setting)
        $compenyinfo = $setting;

        // ðŸ”¹ Get taxes (safely handle null/empty tax_id)
        $taxIds = !empty($sales->tax_id) ? json_decode($sales->tax_id, true) : [];
        $taxes = !empty($taxIds)
            ? TaxRate::where('branch_id', $branchIdToUse)
            ->whereIn('id', $taxIds)
            ->where('isDeleted', 0)
            ->get()
            : collect();

        // ðŸ”¹ Order items & totals
        $orderItems = OrderItem::where('order_id', $id)->get();
        $totalAmount = $orderItems->sum('total_amount');

        $view_id = $id; // define view_id for blade
        $user = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;

        if ($user) {
            $sales->customer_role       = ucfirst($user->role ?? 'Customer');
            $sales->customer_name       = $user->name ?? 'Walk-in Customer';
            $sales->customer_email      = $user->email ?? '';
            $sales->customer_phone      = $user->phone ?? '';
            $sales->customer_address    = optional($user->userDetail)->address ?? '';
            $sales->customer_city       = optional($user->userDetail)->city ?? '';
            $sales->customer_country    = optional($user->userDetail)->country ?? '';
            $sales->customer_gst_number = optional($user->userDetail)->gst_number ?? '';
            $sales->customer_pan_number = optional($user->userDetail)->pan_number ?? '';
        } else {
            // default values if no user is linked
            $sales->customer_role       = 'Customer';
            $sales->customer_name       = 'Walk-in Customer';
            $sales->customer_email      = '';
            $sales->customer_phone      = '';
            $sales->customer_address    = '';
            $sales->customer_city       = '';
            $sales->customer_country    = '';
            $sales->customer_gst_number = '';
            $sales->customer_pan_number = '';
        }
        // âœ… Check if payment already started for this order
        $hasPaymentStarted = PaymentStore::where('order_id', $view_id)
            ->where('isDeleted', 0)
            ->exists();
        return view('sales.sales-details', compact(
            'view_id',
            'sales',
            'taxes',
            'totalAmount',
            'compenyinfo',
            'setting',
            'currencySymbol',
            'currencyPosition',
            'hasPaymentStarted'
        ));
    }

    public function salse_invoice($id)
    {
        $user = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // ðŸ”¹ Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && !empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }
        $view_id = $id;
        $sales = Order::find($view_id);
        $setting = Setting::where('branch_id', $branchIdToUse)->first(); // Get currency info
        // dd($setting);
        if (!$sales) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }

        $taxIds = json_decode($sales->tax_id, true);
        $taxes = TaxRate::where('branch_id', $branchIdToUse)->whereIn('id', $taxIds)->where('isDeleted', 0)->get();
        $orderItems = OrderItem::where('order_id', $view_id)->get();
        $totalAmount = $orderItems->sum('total_amount');

        $user = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;

        $userAddress = $user && $user->userDetail ? $user->userDetail->address : null;

        // âœ… Check if payment already started for this order
        $hasPaymentStarted = PaymentStore::where('order_id', $view_id)
            ->where('isDeleted', 0)
            ->exists();

        return view('sales/salse-invoice', compact('view_id', 'sales', 'taxes', 'totalAmount', 'setting', 'userAddress', 'hasPaymentStarted'));
    }

    // public function salse_invoice_pdf($id)
    // {
    //     $view_id = $id;
    //     $sales = Order::find($view_id);
    //     $setting = Setting::first();

    //     if (!$sales) {
    //         return redirect()->route('sales.list')->with('error', 'Order not found.');
    //     }

    //     // Fetch user data (assuming 'user_id' in orders table)
    //     $user = $sales->user_id ? User::find($sales->user_id) : null;

    //     // Helper function for currency formatting
    //     $formatCurrency = function ($amount) use ($setting) {
    //         $amount = number_format($amount, 2);
    //         return $setting->currency_position === 'right'
    //             ? $amount . $setting->currency_symbol
    //             : $setting->currency_symbol . $amount;
    //     };

    //     // Fetch tax rates
    //     $taxIds = json_decode($sales->tax_id, true) ?? [];
    //     $taxes = TaxRate::whereIn('id', $taxIds)->get();

    //     // Fetch order items
    //     $orderItems = OrderItem::where('order_id', $view_id)->get();

    //     // Subtotal
    //     $subtotal = $orderItems->sum('total_amount');

    //     // Discount
    //     $discountPercent = $sales->discount ?? 0;
    //     $discountAmount = ($discountPercent / 100) * $subtotal;
    //     $afterDiscount = $subtotal - $discountAmount;

    //     // Calculate tax amounts
    //     $taxDetails = [];
    //     foreach ($taxes as $tax) {
    //         $taxAmount = ($tax->tax_rate / 100) * $afterDiscount;
    //         $taxDetails[] = [
    //             'name' => $tax->tax_name,
    //             'rate' => $tax->tax_rate,
    //             'amount' => $taxAmount,
    //             'formatted_amount' => $formatCurrency($taxAmount)
    //         ];
    //     }

    //     // Final total
    //     $finalTotal = $afterDiscount + collect($taxDetails)->sum('amount');

    //     // Prepare formatted values
    //     $formattedSubtotal = $formatCurrency($subtotal);
    //     $formattedDiscountAmount = $formatCurrency($discountAmount);
    //     $formattedAfterDiscount = $formatCurrency($afterDiscount);
    //     $formattedFinalTotal = $formatCurrency($finalTotal);

    //     // Retrieve customer data (assuming $sales has a customer_id relationship)
    //     $customer = $sales->customer ? $sales->customer->toArray() : [
    //         'name' => 'walk-in-customer',
    //         'email' => '',
    //         'phone' => '',
    //         'address' => optional($user->userDetail)->address ?? '',
    //     ];

    //     // Prepare data for view, now also including user data
    //     $pdfData = [
    //         'view_id' => $view_id,
    //         'sales' => $sales,
    //         'setting' => $setting,
    //         'orderItems' => $orderItems,
    //         'salesItems' => $orderItems,
    //         'customer' => [
    //             'name'    => $user->name ?? 'walk-in-customer',
    //             'email'   => $user->email ?? '',
    //             'phone'   => $user->phone ?? '',
    //             'address' => optional($user->userDetail)->address ?? 'arga',
    //         ],
    //         'user' => $user ? $user->toArray() : null,  // include user data
    //         'subtotal' => $formattedSubtotal,
    //         'discountAmount' => $formattedDiscountAmount,
    //         'afterDiscount' => $formattedAfterDiscount,
    //         'finalTotal' => $formattedFinalTotal,
    //         'taxDetails' => $taxDetails
    //     ];

    //     // Load and render PDF
    //     $pdf = PDF::loadView('sales.salse-invoice-pdf', $pdfData);

    //     return $pdf->stream('invoice_' . $view_id . '.pdf');
    // }

    public function salse_invoice_pdf(Request $request, $id)
    {
        $view_id = $id;
        $sales = Order::find($view_id);
        $user = Auth::user();
        $subAdminId = session('selectedSubAdminId') ?? $user->id;

        if ($user->role === 'staff' && $user->branch_id) {
            $setting = Setting::where('branch_id', $user->branch_id)->first();
        } else {
            $setting = Setting::where('branch_id', $subAdminId)->first();
        }

        if (!$sales) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }

        // Fetch user data (assuming 'user_id' in orders table)
        $user = $sales->user_id ? User::find($sales->user_id) : null;

        // Helper function for currency formatting
        $formatCurrency = function ($amount) use ($setting) {
            $amount = number_format($amount, 2);
            $symbol = $setting->currency_symbol ?? 'â‚¹';
            $position = $setting->currency_position ?? 'left';
            return $position === 'right'
                ? $amount . $symbol
                : $symbol . $amount;
        };

        // âœ… Subtotal
        $orderItems = OrderItem::with('product', 'quantityType')->where('order_id', $view_id)->get();
        $subtotal = $orderItems->sum('total_amount');

        // âœ… Discount
        $discountPercent = $sales->discount ?? 0;
        $discountAmount = ($discountPercent / 100) * $subtotal;
        $afterDiscount = $subtotal - $discountAmount;

        // âœ… Tax calculation only if gst_option = 'with'
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
                        'formatted_amount' => $formatCurrency($taxAmount)
                    ];
                }
            }
        }

        // âœ… Final total
        $finalTotal = $afterDiscount + collect($taxDetails)->sum('amount');

        // âœ… Prepare formatted values
        $formattedSubtotal = $formatCurrency($subtotal);
        $formattedDiscountAmount = $formatCurrency($discountAmount);
        $formattedAfterDiscount = $formatCurrency($afterDiscount);
        $formattedFinalTotal = $formatCurrency($finalTotal);

        $customer = [
            'name' => $sales->customer_name ?: ($user->name ?? 'Walk-in Customer'),
            'email' => $user->email ?? '',
            'phone' => $sales->customer_phone ?: ($user->phone ?? ''),
        ];
         $paidAmount = PaymentStore::where('order_id', $sales->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // âœ… Pending amount (single source of truth)
        $pendingAmount = $sales->remaining_amount ?? 0;

        $allPayments = PaymentStore::where('order_id', $sales->id)
            ->where('isDeleted', 0)
            ->get();

        // âœ… Prepare data for view
        $pdfData = [
            'view_id' => $view_id,
            'sales' => $sales,
            'setting' => $setting,
            'orderItems' => $orderItems,
            'salesItems' => $orderItems,
            'customer' => $customer,
            'user' => $user ? $user->toArray() : null,
            'subtotal' => $formattedSubtotal,
            'discountPercent' => $discountPercent,
            'discountAmount' => $formattedDiscountAmount,
            'afterDiscount' => (float) $afterDiscount,              // numeric
            'formattedAfterDiscount' => $formattedAfterDiscount,    // formatted
            'finalTotal' => $formattedFinalTotal,
            'taxDetails1' => $taxDetails,
            'paidAmount'             => $paidAmount,
            'pendingAmount'          => $pendingAmount,
            'allPayments'            => $allPayments,
        ];

        $useSmallInvoice = $request->query('size') === 'small'
            || (isset($setting->invoice_size) && $setting->invoice_size === 'small');

        $viewName = $useSmallInvoice
            ? 'sales.salse-invoice-small-pdf' 
            : 'sales.salse-invoice-pdf';

        if ($useSmallInvoice) {
            $pdf = make_thermal_invoice_pdf($viewName, $pdfData, $orderItems, 226.77);
        } else {
            $pdf = PDF::loadView($viewName, $pdfData);
            $pdf->setPaper('A4', 'portrait');
        }
        return $pdf->stream('invoice_' . $view_id . '.pdf');
    }






    /**
     * Generate and stream a table checkout bill PDF.
     * Route: GET /table/bill/{id}
     */
    public function tableBillPdf($id)
    {
        $view_id  = $id;
        $sales    = Order::find($view_id);
        $authUser = Auth::user();

        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branchIdToUse = $authUser->branch_id;
        } elseif ($authUser->role === 'admin' && !empty(session('selectedSubAdminId'))) {
            $branchIdToUse = session('selectedSubAdminId');
        } else {
            $branchIdToUse = $authUser->id;
        }

        $setting = Setting::where('branch_id', $branchIdToUse)->first();

        if (!$sales) {
            abort(404, 'Order not found.');
        }

        // Currency formatter
        $formatCurrency = function ($amount) use ($setting) {
            $amount   = number_format($amount, 2);
            $symbol   = $setting->currency_symbol   ?? 'Rs.';
            $position = $setting->currency_position ?? 'left';
            return $position === 'right' ? $amount . $symbol : $symbol . $amount;
        };

        // Order items
        $orderItems = OrderItem::with('product', 'quantityType')->where('order_id', $view_id)->get();
        $subtotal   = $orderItems->sum('total_amount');

        // Discount
        $discountPercent = $sales->discount ?? 0;
        $discountAmount  = ($discountPercent / 100) * $subtotal;
        $afterDiscount   = $subtotal - $discountAmount;

        // Tax (only if with_gst)
        $taxDetails = [];
        if ($sales->gst_option === 'with_gst') {
            $taxIds = json_decode($sales->tax_id, true) ?? [];
            if (!empty($taxIds)) {
                $taxes = TaxRate::whereIn('id', $taxIds)->get();
                foreach ($taxes as $tax) {
                    $taxAmount    = ($tax->tax_rate / 100) * $afterDiscount;
                    $taxDetails[] = [
                        'name'             => $tax->tax_name,
                        'rate'             => $tax->tax_rate,
                        'amount'           => $taxAmount,
                        'formatted_amount' => $formatCurrency($taxAmount),
                    ];
                }
            }
        }

        $finalTotal = $afterDiscount + collect($taxDetails)->sum('amount');

        // Table info
        $tableName = null;
        $tableNo   = null;
        if (!empty($sales->table_no)) {
            $tableRow  = \App\Models\Table_no::find($sales->table_no);
            $tableNo   = $sales->table_no;
            $tableName = $tableRow ? $tableRow->table_name : ('Table #' . $tableNo);
        }

        // Customer info
        $customerUser = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;
        $customer = [
            'name'    => $customerUser ? ($customerUser->name    ?? 'Walk-in Customer') : 'Walk-in Customer',
            'email'   => $customerUser ? ($customerUser->email   ?? '') : '',
            'phone'   => $customerUser ? ($customerUser->phone   ?? '') : '',
            'address' => ($customerUser && $customerUser->userDetail) ? ($customerUser->userDetail->address ?? '') : '',
        ];

        $paidAmount    = \App\Models\PaymentStore::where('order_id', $sales->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');
        $pendingAmount = $sales->remaining_amount ?? 0;

        $pdfData = [
            'view_id'                => $view_id,
            'sales'                  => $sales,
            'setting'                => $setting,
            'orderItems'             => $orderItems,
            'customer'               => $customer,
            'subtotal'               => $formatCurrency($subtotal),
            'discountPercent'        => $discountPercent,
            'discountAmount'         => $formatCurrency($discountAmount),
            'afterDiscount'          => (float) $afterDiscount,
            'formattedAfterDiscount' => $formatCurrency($afterDiscount),
            'finalTotal'             => $formatCurrency($finalTotal),
            'taxDetails1'            => $taxDetails,
            'paidAmount'             => $paidAmount,
            'pendingAmount'          => $pendingAmount,
            'tableName'              => $tableName,
            'tableNo'                => $tableNo,
        ];

        $pdf = Pdf::loadView('tables.salse-invoice-pdf', $pdfData);
        return $pdf->stream('table-bill-' . $view_id . '.pdf');
    }

    public function sales_report(Request $request)
    {
        $user         = Auth()->user();
        $branchId     = $user->id ?? null;
        $UserBranchId = $user->branch_id ?? null;
        $userRole     = $user->role ?? '';
        $subAdminId   = session('selectedSubAdminId');

        // Decide branch based on role
        if ($userRole === 'sub-admin') {
            $branchIdToUse = $branchId;
        } elseif ($userRole === 'admin' && $subAdminId) {
            $branchIdToUse = $subAdminId;
        } elseif ($userRole === 'staff') {
            $branchIdToUse = $UserBranchId;
        } else {
            $branchIdToUse = $branchId;
        }

        // Fetch customer
        if ($userRole === 'staff') {
            // Only customer created by this staff
            $customers = User::where('role', 'customer')
                ->where('created_by', $user->id)
                ->where('isDeleted', 0)
                ->orderBy('name')
                ->get();
        } else {
            // Admin / sub-admin sees all customer in branch
            $customers = User::where('role', 'customer')
                ->where('branch_id', $branchIdToUse)
                ->where('isDeleted', 0)
                ->orderBy('name')
                ->get();
        }

        $categories = Category::where('isDeleted', 0)
            ->orderBy('name')
            ->get();

        return view('sales/salesreport', compact('customers', 'categories'));
    }

    public function pos()
    {
        $user = auth()->user();
        $userRole = $user->role ?? '';
        $userId = $user->id ?? null;
        $branchId = $user->branch_id ?? null;
        $subAdminId = session('selectedSubAdminId');

        // ðŸ”¹ Decide which branch_id to use
        if ($userRole === 'sub-admin' && $userId) {
            $branchIdToUse = $userId;
        } elseif ($userRole === 'admin' && !empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } elseif ($userRole === 'staff' && $branchId) {
            $branchIdToUse = $branchId;
        } else {
            $branchIdToUse = $userId;
        }

        // ðŸ”¹ Get settings
        $setting = Setting::where('branch_id', $branchIdToUse)->first();
        $currency_symbol = $setting->currency_symbol ?? 'â‚¹';
        $currency_position = $setting->currency_position ?? 'left';

        $categories = Category::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        $quantityTypes = QuantityType::where('isDeleted', 0)
            ->get();

        $taxRates = TaxRate::where('status', 'active')->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();

        $customers = User::where('role', 'customer')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();
            
        // ðŸ”¹ Get staff for assigned staff dropdown based on branch
        $staffList = User::where('role', 'staff')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();

        // ðŸ”¹ Tables
        $tables = Table_no::where('branch_id', $branchIdToUse)->get();

        // ðŸ”¹ Vendors only for Staff or default case
        $vendors = collect(); // empty collection if not needed
        if ($userRole === 'staff' || $userRole === 'admin' || $userRole === 'sub-admin') {
            $vendors = User::where('role', 'vendor')
                ->where('branch_id', $branchIdToUse)
                ->where('isDeleted', 0)
                ->get();
        }

        // ðŸ”¹ Create products array with quantity type information
    $products = Product::where('status', 'active')
        ->where('availablility', 'in_stock')
        ->where('branch_id', $branchIdToUse)
        ->get();
    
    $productsArray = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category_id' => $product->category_id,
            'price' => $product->price,
            'quantity_type_id' => $product->quantity_type_id,
        ];
    })->toArray();

        // ðŸ”¹ Banks for Debit payment â€” loaded via API in the view
        $banks = collect();

        return view('sales.pos', compact(
            'categories',
            // 'quantityTypes',
            'taxRates',
            'customers',
            'tables',
            'vendors',
            'currency_symbol',
            'currency_position',
            'setting',
            'staffList',
            'productsArray',
            'banks'
        ));
    }
    // public function sale_report($ids)
    // {
    //     $idsArray = explode(',', $ids);
    //     $sales = OrderItem::with('product.category', 'invoice')->whereIn('id', $idsArray)->get();

    //     if ($sales->isEmpty()) {
    //         return redirect()->back()->with('error', 'No purchase data found.');
    //     }

    //     // Get settings
    //     $settings = Setting::first();
    //     $currencySymbol = $settings->currency_symbol ?? 'â‚¹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     $subtotal = $sales->sum('total_amount');

    //     // Get tax rates
    //     $taxRates = TaxRate::where('status', 'active')->get();
    //     $discountPercent = 0;
    //     $discountAmount = 0;

    //     // Get discount
    //     foreach ($sales as $sale) {
    //         if ($sale->invoice) {
    //             $discountPercent = $sale->invoice->discount ?? 0;
    //             $discountAmount += ($sale->total_amount * $discountPercent) / 100;
    //         }
    //     }

    //     $subtotalAfterDiscount = $subtotal - $discountAmount;

    //     // Calculate tax on discounted amount
    //     $taxDetails = [];
    //     $totalTaxAmount = 0;
    //     foreach ($taxRates as $tax) {
    //         $amount = $subtotalAfterDiscount * ($tax->tax_rate / 100);
    //         $taxDetails[] = [
    //             'name' => $tax->tax_name,
    //             'rate' => $tax->tax_rate,
    //             'amount' => $amount,
    //             'formatted_amount' => $currencyPosition === 'left'
    //                 ? $currencySymbol . number_format($amount, 2)
    //                 : number_format($amount, 2) . $currencySymbol,
    //         ];
    //         $totalTaxAmount += $amount;
    //     }

    //     $totalAmount = $subtotalAfterDiscount + $totalTaxAmount;

    //     // Format monetary values
    //     $formattedSubtotal = $currencyPosition === 'left'
    //         ? $currencySymbol . number_format($subtotal, 2)
    //         : number_format($subtotal, 2) . $currencySymbol;

    //     $formattedDiscountAmount = $currencyPosition === 'left'
    //         ? $currencySymbol . number_format($discountAmount, 2)
    //         : number_format($discountAmount, 2) . $currencySymbol;

    //     $formattedTotalAmount = $currencyPosition === 'left'
    //         ? $currencySymbol . number_format($totalAmount, 2)
    //         : number_format($totalAmount, 2) . $currencySymbol;

    //     return view('sales.sale_report', compact(
    //         'sales',
    //         'settings',
    //         'subtotal',
    //         'formattedSubtotal',
    //         'taxDetails',
    //         'discountAmount',
    //         'formattedDiscountAmount',
    //         'discountPercent',
    //         'totalTaxAmount',
    //         'totalAmount',
    //         'formattedTotalAmount',
    //         'currencySymbol',
    //         'currencyPosition'
    //     ));
    // }

    public function sale_report($ids)
    {
        $authUser = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // Decide branch_id based on role
        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branchIdToUse = $authUser->branch_id;
        } elseif ($authUser->role === 'admin' && !empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $authUser->id;
        }

        $idsArray = explode(',', $ids);

        // Eager load related models
        $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail', 'quantityType')
            ->whereIn('id', $idsArray)
            ->get();

            // dd($sales);

        if ($sales->isEmpty()) {
            return redirect()->route('sales.index')->with('error', 'No sales data found.');
        }

        // Get settings
        $settings = Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol = $settings->currency_symbol ?? 'â‚¹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Process each sale
        $totalAmount = 0;
        $discountAmount = 0;
        $taxDetails = [];
        // ðŸ”¹ GST / Tax Calculation
        // $taxDetails     = [];
        $totalTaxAmount = 0;

        foreach ($sales as $sale) {
            $rowTaxes     = [];
            $rowTaxAmount = 0;

            $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
            $rowTaxIds    = $sale->invoice->tax_id ?? '[]';

            // Decode JSON tax array
            $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

            $rowTaxRates = collect();
            if ($rowGSTOption === 'with_gst' && ! empty($rowTaxIdsArray)) {
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

            // Calculate row taxes
            foreach ($rowTaxRates as $tax) {
                $taxBase = $unitPrice * $sale->quantity;
                $amount  = $taxBase * ($tax->tax_rate / 100);

                $rowTaxes[] = [
                    'name'   => $tax->tax_name,
                    'rate'   => $tax->tax_rate,
                    'amount' => $amount,
                ];

                $rowTaxAmount += $amount;

                // accumulate overall tax totals
                if (! isset($taxDetails[$tax->id])) {
                    $taxDetails[$tax->id] = [
                        'name'   => $tax->tax_name,
                        'rate'   => $tax->tax_rate,
                        'amount' => 0,
                    ];
                }
                $taxDetails[$tax->id]['amount'] += $amount;
            }

            // Attach to sale row
            $sale->rowGSTOption = $rowGSTOption;
            $sale->rowTaxes     = $rowTaxes;
            $sale->rowTaxAmount = $rowTaxAmount;

            // Final total per row = (unit price after discount Ã— qty) + row taxes
            $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;

            // $totalAmount += $rowTaxAmount;
            $totalAmount = $sales->sum('rowFinalTotal');
        }

        // Customer info (from first sale)
        $customer = $sales->first()->user ?? null;
        $userDetails = $customer ? $customer->userDetail : null;

        return view('sales.sale_report', compact(
            'sales',
            'settings',
            'discountAmount',
            'totalAmount',
            'taxDetails',
            'currencySymbol',
            'currencyPosition',
            'customer',
            'userDetails',
            'ids'
        ));
    }


    public function export_sales_report_pdf($ids)
    {
        $authUser   = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // ðŸ”¹ Decide branch_id based on role
        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branchIdToUse = $authUser->branch_id;
        } elseif ($authUser->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $authUser->id;
        }

        $idsArray = explode(',', $ids);

        // ðŸ”¹ Eager load related models
        $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail', 'quantityType')
            ->whereIn('id', $idsArray)
            ->get();

        // dd($sales);

        if ($sales->isEmpty()) {
            return redirect()->route('sales.index')->with('error', 'No sales data found.');
        }

        // ðŸ”¹ Get settings
        $setting         = Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol   = $setting->currency_symbol ?? 'â‚¹';
        $currencyPosition = $setting->currency_position ?? 'left';

        $subtotal = $sales->sum('total_amount');

        // ðŸ”¹ Discount calculation
        $discountAmount = 0;
        foreach ($sales as $sale) {
            if ($sale->invoice && $sale->invoice->discount) {
                $discountPercent = $sale->invoice->discount;
                $discountAmount += ($sale->total_amount * $discountPercent) / 100;
            }
        }

        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // ðŸ”¹ GST / Tax Calculation
        $taxDetails     = [];
        $totalTaxAmount = 0;

        foreach ($sales as $sale) {
            $rowTaxes     = [];
            $rowTaxAmount = 0;

            $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
            $rowTaxIds    = $sale->invoice->tax_id ?? '[]';

            // Decode JSON tax array
            $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

            $rowTaxRates = collect();
            if ($rowGSTOption === 'with_gst' && ! empty($rowTaxIdsArray)) {
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

            // Calculate row taxes
            foreach ($rowTaxRates as $tax) {
                $taxBase = $unitPrice * $sale->quantity;
                $amount  = $taxBase * ($tax->tax_rate / 100);

                $rowTaxes[] = [
                    'name'   => $tax->tax_name,
                    'rate'   => $tax->tax_rate,
                    'amount' => $amount,
                ];

                $rowTaxAmount += $amount;

                // accumulate overall tax totals
                if (! isset($taxDetails[$tax->id])) {
                    $taxDetails[$tax->id] = [
                        'name'   => $tax->tax_name,
                        'rate'   => $tax->tax_rate,
                        'amount' => 0,
                    ];
                }
                $taxDetails[$tax->id]['amount'] += $amount;
            }

            // Attach to sale row
            $sale->rowGSTOption = $rowGSTOption;
            $sale->rowTaxes     = $rowTaxes;
            $sale->rowTaxAmount = $rowTaxAmount;

            // Final total per row = (unit price after discount Ã— qty) + row taxes
            $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;

            $totalTaxAmount += $rowTaxAmount;
        }

        // ðŸ”¹ Total after discount + taxes
        // Total after discount + taxes
        $totalAmount = $sales->sum('rowFinalTotal');

        $pdfData = [
            'sales'            => $sales,
            'setting'         => $setting,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'subtotal'         => $subtotal,
            'discountAmount'   => $discountAmount,
            // 'afterDiscount'    => $afterDiscount,
            'taxDetails'       => $taxDetails,
            'totalTaxAmount'   => $totalTaxAmount,
            'totalAmount'       => $totalAmount,
            // 'ids' => $ids
        ];

        // Load PDF
        $pdf = PDF::loadView('sales.sales-invoice-report-pdf', $pdfData)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        return $pdf->download('sales_report.pdf');
    }


    //  public function makePaymentSubmit(Request $request)
    //     {
    //         // $user_id = Auth::id();
    //         $user_id = Auth::guard('api')->user();

    //         $request->validate([

    //             'order_id' => 'nullable|integer',
    //             'payment_amount' => 'nullable|numeric',
    //             'payment_date' => 'nullable|date',
    //             'payment_type' => 'nullable|string',
    //             'emi_month' => 'nullable|integer',
    //             'pending_date' => 'nullable|date',
    //             'new_emi_value' => 'nullable',
    //             'emi_paid_value' => 'nullable|numeric',
    //         ]);

    //         if ($request->filled('order_id')) {
    //             // Determine payment amount
    //             $paymentAmount = $request->emi_total_new ?? $request->emi_total ?? $request->amount ?? $request->upi_online_amount ?? 0;

    //             if ($request->filled('cash_amount') && $request->filled('online_amount')) {
    //                 $paymentAmount = (float) $request->cash_amount + (float) $request->online_amount;
    //             } elseif ($request->filled('fully_cash_amount') && $request->filled('full_online_amount')) {
    //                 $paymentAmount = (float) $request->fully_cash_amount + (float) $request->full_online_amount;
    //             } elseif ($request->cashAmount) {
    //                 $paymentAmount = $request->cashAmount;
    //             } elseif ($request->upi_online_amount) {
    //                 $paymentAmount = $request->upi_online_amount;
    //             } elseif ($request->emi_monthly) {
    //                 $paymentAmount = $request->emi_monthly;
    //             }

    //             // Determine payment type
    //             if (
    //                 in_array($request->paid_type, ['cash_partially']) ||
    //                 in_array($request->online_type, ['online_partially']) ||
    //                 in_array($request->cash_online_type, ['cash_online_partially'])
    //             ) {
    //                 $type = 'partially';
    //             } elseif (
    //                 in_array($request->paid_type, ['cash_fully']) ||
    //                 in_array($request->online_type, ['online_fully']) ||
    //                 in_array($request->cash_online_type, ['cash_online_fully']) ||
    //                 $request->payment_type === 'fully'

    //             ) {
    //                 $type = 'fully';
    //             } elseif (
    //                 in_array($request->emi_type, ['emi'])
    //             ) {
    //                 $type = 'emi';
    //             } else {
    //                 $type = 'fully';
    //             }


    //             // Fetch Invoice if exists
    //             $invoice = $request->order_id ? Order::find($request->order_id) : null;

    //             // Calculate remaining amount
    //             $currentRemaining = $invoice->remaining_amount ?? 0;
    //             $newRemaining = max(0, $currentRemaining - $paymentAmount);
    //             // dd($currentRemaining,$newRemaining);

    //             $payment = PaymentStore::create([
    //                 'user_id' => $user_id,
    //                 'order_id' =>  $request->order_id,
    //                 'custom_invoice_id' => 0,
    //                 'payment_amount' => $paymentAmount,
    //                 'remaining_amount' => $newRemaining,
    //                 'payment_method' => $request->payment_method ?? $request->payment_type ?? '',
    //                 'payment_date' => \Carbon\Carbon::now(),
    //                 'payment_type' => $type,
    //                 'cash_amount' => $request->cash_amount,
    //                 'upi_amount' => $request->online_amount,
    //                 'emi_month' => $request->emi_month ?? 1,
    //                 'isDeleted' => 0,
    //             ]);

    //             // Update invoice
    //             if ($invoice) {
    //                 $updateData = ['remaining_amount' => $newRemaining];

    //                 // If new EMI is being set
    //                 if ($request->filled('new_emi_value') && $request->payment_method == 'emi' || $request->filled('emi_paid_value')) {
    //                     //  dd($request->all());
    //                     $updateData['payment_method'] = 'EMI';
    //                     $updateData['emi_duration'] = $request->emi_month_new ?? $request->emi_month;
    //                     $updateData['emi_months'] = $request->emi_total_new ?? 1;
    //                 } else {
    //                     // dd($request->filled('emi_paid_value'));
    //                     // Otherwise, keep the actual payment method (cash, online, cash_online)
    //                     $method = $request->payment_method ?? $request->payment_type ?? $updateData['payment_method'] ?? 'Cash';

    //                     // Normalize to proper values
    //                     if (strtolower($method) === 'online' || strtolower($method) === 'upi') {
    //                         $method = 'Online';
    //                     } elseif (strtolower($method) === 'cash') {
    //                         $method = 'Cash';
    //                     } elseif (strtolower($method) === 'emi') {
    //                         $method = 'EMI';
    //                     } else {
    //                         $method = 'Cash';
    //                     }

    //                     $updateData['payment_method'] = $method;
    //                 }

    //                 if ($request->emi_duration) {
    //                     $updateData['emi_duration'] = $request->emi_duration;
    //                 }

    //                 // Update next pending date if provided
    //                 if ($request->pending_date) {
    //                     $updateData['next_pending_date'] = $request->pending_date;
    //                 }

    //                 $invoice->update($updateData);
    //             }
    //         }
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Payment submitted successfully.',
    //             'data' => $payment
    //         ]);
    //     }

    // public function getHistory($order_id)
    // {
    //     // $history = PaymentStore::where('order_id', $job  _card_id)
    //     $history = PaymentStore::where('order_id', $order_id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $history
    //     ]);
    // }


    // public function show_sales_report_page(Request $request)
    // {
    //     try {
    //         // $authUser = Auth::guard('api')->user();
    //         $ids = $request->input('ids');
    //         $branchId = $request->input('branch');

    //         if (empty($ids)) {
    //             abort(404, 'No sales selected.');
    //         }

    //         $idsArray = explode(',', $ids);

    //         // ðŸ”¹ Fetch sales data with relations
    //         $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
    //             ->whereIn('id', $idsArray)
    //             ->get();

    //         if ($sales->isEmpty()) {
    //             abort(404, 'No sales found.');
    //         }

    //         // ðŸ”¹ Settings
    //         $settings = Setting::where('branch_id', $branchId)->first();
    //         $currencySymbol   = $settings->currency_symbol ?? 'â‚¹';
    //         $currencyPosition = $settings->currency_position ?? 'left';

    //         // ðŸ”¹ Tax / Discount calculations
    //         $taxDetails = [];
    //         $totalAmount = 0;
    //         $discountAmount = 0;

    //         foreach ($sales as $sale) {
    //             $rowTaxes     = [];
    //             $rowTaxAmount = 0;

    //             $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
    //             $rowTaxIds    = $sale->invoice->tax_id ?? '[]';
    //             $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

    //             // Fetch applicable tax rates
    //             $rowTaxRates = collect();
    //             if ($rowGSTOption === 'with_gst' && !empty($rowTaxIdsArray)) {
    //                 $rowTaxRates = TaxRate::where('status', 'active')
    //                     ->where('branch_id', $branchId)
    //                     ->where('isDeleted', 0)
    //                     ->whereIn('id', $rowTaxIdsArray)
    //                     ->get();
    //             }

    //             $unitPrice = $sale->price;
    //             // Apply invoice-level discount
    //             if ($sale->invoice && $sale->invoice->discount) {
    //                 $discountPercent = $sale->invoice->discount;
    //                 $unitPrice -= ($unitPrice * $discountPercent) / 100;
    //             }

    //             // Calculate taxes per row
    //             foreach ($rowTaxRates as $tax) {
    //                 $taxBase = $unitPrice * $sale->quantity;
    //                 $amount  = $taxBase * ($tax->tax_rate / 100);

    //                 $rowTaxes[] = [
    //                     'name'   => $tax->tax_name,
    //                     'rate'   => $tax->tax_rate,
    //                     'amount' => $amount,
    //                 ];

    //                 $rowTaxAmount += $amount;

    //                 // accumulate overall tax totals
    //                 if (!isset($taxDetails[$tax->id])) {
    //                     $taxDetails[$tax->id] = [
    //                         'name'   => $tax->tax_name,
    //                         'rate'   => $tax->tax_rate,
    //                         'amount' => 0,
    //                     ];
    //                 }
    //                 $taxDetails[$tax->id]['amount'] += $amount;
    //             }

    //             // Attach row-level data for display
    //             $sale->rowGSTOption = $rowGSTOption;
    //             $sale->rowTaxes     = $rowTaxes;
    //             $sale->rowTaxAmount = $rowTaxAmount;

    //             // Final total per item (price Ã— qty + tax)
    //             $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;
    //         }

    //         // ðŸ”¹ Calculate grand total
    //         $totalAmount = $sales->sum('rowFinalTotal');

    //         // ðŸ”¹ Customer info
    //         $customer = $sales->first()->user ?? null;
    //         $userDetails = $customer ? $customer->userDetail : null;

    //         // ðŸ”¹ Data for blade view
    //         $data = [
    //             'sales'            => $sales,
    //             'settings'         => $settings,
    //             'discountAmount'   => $discountAmount,
    //             'totalAmount'      => $totalAmount,
    //             'taxDetails'       => $taxDetails,
    //             'currencySymbol'   => $currencySymbol,
    //             'currencyPosition' => $currencyPosition,
    //             'customer'         => $customer,
    //             'userDetails'      => $userDetails,
    //             'ids'              => $ids,
    //         ];

    //         return view('sales.web_sale_report', $data);

    //     } catch (\Throwable $e) {
    //         abort(500, 'Error loading sales report: ' . $e->getMessage());
    //     }
    // }
    public function show_sales_report_page(Request $request)
    {
        try {
            // ðŸ”¹ Get inputs directly from the request
            $ids = $request->input('ids');
            $branchId = $request->input('branch');

            // ðŸ”¹ Validate required parameters
            if (empty($ids)) {
                abort(404, 'No sales selected.');
            }
            if (empty($branchId)) {
                abort(404, 'Branch ID is missing.');
            }

            // ðŸ”¹ Convert to array if comma-separated
            $idsArray = explode(',', $ids);

            // ðŸ”¹ Fetch sales data with relationships
            $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
                ->whereIn('id', $idsArray)
                ->get();

            if ($sales->isEmpty()) {
                abort(404, 'No sales found.');
            }

            // ðŸ”¹ Fetch settings for branch
            $settings = Setting::where('branch_id', $branchId)->first();
            $currencySymbol   = $settings->currency_symbol ?? 'â‚¹';
            $currencyPosition = $settings->currency_position ?? 'left';

            // ðŸ”¹ Initialize totals
            $taxDetails = [];
            $totalAmount = 0;
            $discountAmount = 0;

            foreach ($sales as $sale) {
                $rowTaxes     = [];
                $rowTaxAmount = 0;

                $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
                $rowTaxIds    = $sale->invoice->tax_id ?? '[]';
                $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

                // ðŸ”¹ Fetch applicable tax rates
                $rowTaxRates = collect();
                if ($rowGSTOption === 'with_gst' && !empty($rowTaxIdsArray)) {
                    $rowTaxRates = TaxRate::where('status', 'active')
                        ->where('branch_id', $branchId)
                        ->where('isDeleted', 0)
                        ->whereIn('id', $rowTaxIdsArray)
                        ->get();
                }

                // ðŸ”¹ Apply discount (if any)
                $unitPrice = $sale->price;
                if ($sale->invoice && $sale->invoice->discount) {
                    $discountPercent = $sale->invoice->discount;
                    $discountPerUnit = ($unitPrice * $discountPercent) / 100;
                    $unitPrice -= $discountPerUnit;
                    $discountAmount += $discountPerUnit * $sale->quantity;
                }

                // ðŸ”¹ Calculate taxes per item
                foreach ($rowTaxRates as $tax) {
                    $taxBase = $unitPrice * $sale->quantity;
                    $amount  = $taxBase * ($tax->tax_rate / 100);

                    $rowTaxes[] = [
                        'name'   => $tax->tax_name,
                        'rate'   => $tax->tax_rate,
                        'amount' => $amount,
                    ];

                    $rowTaxAmount += $amount;

                    // Accumulate total tax details
                    if (!isset($taxDetails[$tax->id])) {
                        $taxDetails[$tax->id] = [
                            'name'   => $tax->tax_name,
                            'rate'   => $tax->tax_rate,
                            'amount' => 0,
                        ];
                    }
                    $taxDetails[$tax->id]['amount'] += $amount;
                }

                // ðŸ”¹ Attach row-level summary
                $sale->rowGSTOption = $rowGSTOption;
                $sale->rowTaxes     = $rowTaxes;
                $sale->rowTaxAmount = $rowTaxAmount;

                // ðŸ”¹ Final total per item
                $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;
            }

            // ðŸ”¹ Calculate final grand total
            $totalAmount = $sales->sum('rowFinalTotal');

            // ðŸ”¹ Customer info (first sale user)
            $customer = $sales->first()->user ?? null;
            $userDetails = $customer ? $customer->userDetail : null;

            // ðŸ”¹ Prepare data for Blade view
            $data = [
                'sales'            => $sales,
                'settings'         => $settings,
                'discountAmount'   => $discountAmount,
                'totalAmount'      => $totalAmount,
                'taxDetails'       => $taxDetails,
                'currencySymbol'   => $currencySymbol,
                'currencyPosition' => $currencyPosition,
                'customer'         => $customer,
                'userDetails'      => $userDetails,
                'ids'              => $ids,
            ];

            // âœ… Return view without requiring authentication
            return view('sales.web_sale_report', $data);
        } catch (\Throwable $e) {
            abort(500, 'Error loading sales report: ' . $e->getMessage());
        }
    }
    // public function getTypes($productId)
    // {
    //     $product = Product::find($productId);
    //     $types = QuantityType::where('id', $product->quantity_type_id)->get();
    //     return response()->json($types);
    // }

}
