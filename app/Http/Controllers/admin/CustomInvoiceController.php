<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
// use PDF;
use App\Models\CustomInvoice;
use App\Models\CustomInvoiceItem;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomInvoiceController extends Controller
{
    public function custom_invoice_list(Request $request)
    {
        $years = CustomInvoice::where('isDeleted', 0)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('custom-invoice/custom_invoicelist', compact('years'));
    }
    // public function makePaymentSubmit(Request $request)
    // {
    //     $user_id = Auth::id();

    //     $request->validate([

    //         'custom_invoice_id' => 'nullable|integer',
    //         'payment_amount' => 'nullable|numeric',
    //         'payment_date' => 'nullable|date',
    //         'payment_type' => 'nullable|string',
    //         'emi_month' => 'nullable|integer',
    //         'pending_date' => 'nullable|date',
    //         'new_emi_value' => 'nullable',
    //         'emi_paid_value' => 'nullable|numeric',
    //     ]);

    //     if ($request->filled('custom_invoice_id')) {
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

    //         // Fetch Invoice if exists
    //         $invoice = $request->custom_invoice_id ? CustomInvoice::find($request->custom_invoice_id) : null;

    //         // Calculate remaining amount
    //         $currentRemaining = $invoice->remaining_amount ?? 0;
    //         $newRemaining = max(0, $currentRemaining - $paymentAmount);
    //         // dd($currentRemaining,$newRemaining);

    //         $payment = PaymentStore::create([
    //             'user_id' => $user_id,
    //             'order_id' => 0,
    //             'custom_invoice_id' => $request->custom_invoice_id,
    //             'payment_amount' => $paymentAmount,
    //             'remaining_amount' => $newRemaining,
    //             'payment_method' => $request->payment_method ?? $request->payment_type ?? '',
    //             'payment_date' => \Carbon\Carbon::now(),
    //             'payment_type' => $type,
    //             'cash_amount' => $request->cash_amount,
    //             'upi_amount' => $request->online_amount,
    //             'emi_month' => $request->emi_month ?? 1,
    //             'isDeleted' => 0,
    //         ]);

    //         // Update invoice
    //         if ($invoice) {
    //             $updateData = ['remaining_amount' => $newRemaining];

    //             // If new EMI is being set
    //             if ($request->filled('new_emi_value') && $request->payment_method == 'emi' || $request->filled('emi_paid_value')) {
    //                 //  dd($request->all());
    //                 $updateData['payment_method'] = 'EMI';
    //                 $updateData['emi_duration'] = $request->emi_month_new ?? $request->emi_month;
    //                 $updateData['emi_months'] = $request->emi_total_new ?? 1;
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

    //             $invoice->update($updateData);

    //             // ✅ Determine status
    //             $paymentStatus = 'unpaid';
    //             $purchaseStatus = 'pending';

    //             if ($newRemaining <= 0) {
    //                 $paymentStatus = 'paid';
    //                 $purchaseStatus = 'completed';
    //             } elseif ($newRemaining > 0 && $newRemaining < ($invoice->grand_total ?? 0)) {
    //                 $paymentStatus = 'partial';
    //                 $purchaseStatus = 'processing';
    //             }

    //             // ✅ Update invoice
    //             $invoice->update([
    //                 'payment_status'   => $paymentStatus,
    //                 'status'           => $purchaseStatus,
    //                 'remaining_amount' => $newRemaining,
    //                 'updated_at'       => now(),
    //             ]);

    //             // ✅ Update all invoice items too
    //             CustomInvoiceItem::where('invoice_id', $invoice->id)
    //                 ->update([
    //                     'payment_status'  => $paymentStatus,
    //                     'purchase_status' => $purchaseStatus,
    //                     'updated_at'      => now(),
    //                 ]);
    //         }
    //     }
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Payment submitted successfully.',
    //         'data' => $payment
    //     ]);
    // }

    public function getHistory($custom_invoice_id)
    {
        // $history = PaymentStore::where('order_id', $job  _card_id)
        $history = PaymentStore::where('custom_invoice_id', $custom_invoice_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $history,
        ]);
    }

    public function add_custom_invoice(Request $request)
    {
        $user      = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branchIdToUse = $selectedSubAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        $vendorQuery = User::where('role', 'vendor')
            ->where('isDeleted', 0);
        // ->where('branch_id', $branchIdToUse)
        // ->get();
        $customerQuery = User::where('role', 'customer')
            ->where('isDeleted', 0);
        // ->where('branch_id', $branchIdToUse)
        // ->get();
        if ($user->role === 'staff') {
            // Staff → Only vendors/customers created by this staff
            $vendorQuery->where('created_by', $user->id);
            $customerQuery->where('created_by', $user->id);
        } else {
            // Admin/Sub-admin → Filter by branch
            $vendorQuery->where('branch_id', $branchIdToUse);
            $customerQuery->where('branch_id', $branchIdToUse);
        }

        $vendors   = $vendorQuery->get();
        $customers = $customerQuery->get();
        $categories = Category::where('isDeleted', 0)->get();

        return view('custom-invoice/addcustom_invoice', compact('vendors', 'customers', 'categories'));
    }
    public function edit_custom_invoice(Request $request)
    {
        $user      = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        $vendors = User::where('role', 'vendor')
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0) // Only non-deleted vendors
            ->get();
        $customers = User::where('role', 'customer')
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0) // Only non-deleted vendors
            ->get();
        $taxes      = TaxRate::where('status', 'active')->where('isDeleted', 0)->where('branch_id', $branch_id)->get();
        $products   = Product::where('branch_id', $branch_id)->where('isDeleted', 0)->get();
        $categories = Category::where('isDeleted', 0)->get();

        return view('custom-invoice/editcustom_invoice', compact('vendors', 'customers', 'taxes', 'products', 'categories'));
    }

    public function custom_invoice_print(Request $request, $id)
    {
        $user = Auth::user();

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }
        // Fetch the invoice from the new table 'custom_invoices'
        $invoice = CustomInvoice::find($id);

        if (! $invoice) {
            return redirect()->route('custom-invoice.list')->with('error', 'Invoice not found.');
        }

        // Properly decode taxes (double decode if needed)
        if (! empty($invoice->taxes) && is_string($invoice->taxes)) {
            $firstDecode    = json_decode($invoice->taxes, true);
            $invoice->taxes = is_string($firstDecode) ? json_decode($firstDecode, true) : $firstDecode;
        }

        // Determine the vendor or customer (vendor_id can be null)
        $userId = $invoice->vendor_id ?? $invoice->customer_id;
        $user   = User::where('id', $userId)
            ->whereIn('role', ['vendor', 'customer'])
            ->first();

        // Decode products data
        $productsData = json_decode($invoice->products, true);
        $productIds   = collect($productsData)->pluck('product_id')->unique()->toArray();
        $products     = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Populate product names and images
        foreach ($productsData as &$item) {
            $product              = $products->get($item['product_id']);
            $item['product_name'] = $product ? $product->name : 'Unknown Product';

            $images                = $product && $product->images ? json_decode($product->images, true) : [];
            $item['product_image'] = ! empty($images)
                ? env('ImagePath') . 'storage/' . $images[0]
                : env('ImagePath') . '/admin/assets/img/product/noimage.png';
        }

        $invoice->products = $productsData;

        $companyInfo = Setting::where('branch_id', $branch_id)->first();

        // Get currency settings
        $currencySymbol   = $companyInfo->currency_symbol ?? '₹';
        $currencyPosition = $companyInfo->currency_position ?? 'left';

        return view('custom-invoice/download_invoice', compact(
            'invoice',
            'user',
            'companyInfo',
            'currencySymbol',
            'currencyPosition'
        ));
    }

    public function custom_invoice_view(Request $request, $id)
    {
        $user = Auth::user();

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        $invoice = CustomInvoice::findOrFail($id);

        // Properly decode taxes (double decode if needed)
        if (! empty($invoice->taxes) && is_string($invoice->taxes)) {
            $invoice->taxes = json_decode($invoice->taxes, true);
        }

        // Determine the vendor or customer (vendor_id can be null)
        $vendorId = $invoice->vendor_id ?? $invoice->customer_id;
        $vendor   = User::where('id', $vendorId)
            ->whereIn('role', ['vendor', 'customer'])
            ->first();

        $productsData = json_decode($invoice->products, true);
        $productIds   = collect($productsData)->pluck('product_id')->unique()->toArray();
        $products     = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($productsData as &$item) {
            $product              = $products->get($item['product_id']);
            $item['product_name'] = $product ? $product->name : 'Unknown Product';

            $images                = $product && $product->images ? json_decode($product->images, true) : [];
            $item['product_image'] = ! empty($images)
                ? env('ImagePath') . 'storage/' . $images[0]
                : env('ImagePath') . '/admin/assets/img/product/noimage.png';
        }

        $invoice->products = $productsData;
        $compenyinfo       = Setting::where('branch_id', $branch_id)->first();

        // Get currency settings
        $currencySymbol   = $compenyinfo->currency_symbol ?? '₹';
        $currencyPosition = $compenyinfo->currency_position ?? 'left';

        return view('custom-invoice/custom-invoice-details', compact('invoice', 'vendor', 'compenyinfo', 'currencySymbol', 'currencyPosition'));
    }

    public function exportCustom_invoice(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId             = $user->id;
        $role               = $user->role;
        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? session('selectedSubAdminId') ?? $userId;

        $year  = $request->query('year');
        $month = $request->query('month');
        $date  = $request->query('date');

        try {
            $query = CustomInvoice::query()
                ->with([
                    'vendor:id,name,phone',
                    'customer:id,name,phone',
                ])
                ->where('isDeleted', 0);

            // 🔹 Role-based filter
            // if ($role === 'sub-admin') {
            //     $query->where('branch_id', $userId);
            // } elseif ($role === 'admin' && $selectedSubAdminId) {
            //     $query->where('branch_id', $selectedSubAdminId);
            // } else {
            //     $query->where('branch_id', $userId);
            // }

            if ($role === 'staff') {
                // Staff sees only invoices they created
                $query->where('created_by', $userId);
            } elseif ($role === 'sub-admin') {
                // Sub-admin sees invoices for their branch
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin') {
                // Admin can select a sub-admin branch, else default to their own branch
                $branchIdToUse = $selectedSubAdminId ?? $userId;
                $query->where('branch_id', $branchIdToUse);
            } else {
                // Fallback: default to user's own branch
                $query->where('branch_id', $userId);
            }
            // dd($userId);

            // 🔹 Filters
            if (! empty($year)) {
                $query->whereYear('created_at', $year);
            }
            if (! empty($month)) {
                $query->whereMonth('created_at', $month);
            }
            if (! empty($date)) {
                $dateParts = explode('-', $date); // d-m-Y
                if (count($dateParts) === 3) {
                    $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                    $query->whereDate('created_at', $formattedDate);
                }
            }

            $invoices = $query->orderBy('created_at', 'desc')->get();

            // 🔹 Excel generation
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            // ✅ Headers
            $headers = [
                'A1' => 'Invoice Number',
                'B1' => 'Vendor Name',
                'C1' => 'Vendor Phone',
                'D1' => 'Customer Name',
                'E1' => 'Customer Phone',
                'F1' => 'Total Amount',
                'G1' => 'Paid',
                'G1' => 'Paid',
                'H1' => 'Discount',
                'I1' => 'Shipping',
                'J1' => 'Grand Total',
                'K1' => 'Remaining Amount',
                'L1' => 'Status',
                'M1' => 'Created At',
            ];
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            $sheet->getStyle('A1:M1')->getFont()->setBold(true);

            // ✅ Rows
            $row = 2;
            foreach ($invoices as $invoice) {
                $sheet->setCellValue('A' . $row, $invoice->invoice_number ?? 'N/A');
                $sheet->setCellValue('B' . $row, $invoice->vendor->name ?? 'N/A');
                $sheet->setCellValue('C' . $row, $invoice->vendor->phone ?? 'N/A');
                $sheet->setCellValue('D' . $row, $invoice->customer->name ?? 'N/A');
                $sheet->setCellValue('E' . $row, $invoice->customer->phone ?? 'N/A');
                $sheet->setCellValue('F' . $row, $invoice->total_amount ?? 0);
                $sheet->setCellValue('G' . $row, $invoice->paid ?? 0);
                $sheet->setCellValue('H' . $row, $invoice->discount ?? 0);
                $sheet->setCellValue('I' . $row, $invoice->shipping ?? 0);
                $sheet->setCellValue('J' . $row, $invoice->grand_total ?? 0);
                $sheet->setCellValue('K' . $row, $invoice->remaining_amount ?? 0);
                $sheet->setCellValue('L' . $row, $invoice->status ?? 'N/A');
                $sheet->setCellValue('M' . $row, $invoice->created_at->format('Y-m-d'));
                $row++;
            }

            $writer   = new Xlsx($spreadsheet);
            $fileName = 'CustomInvoices_' . date('Ymd_His') . '.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type'                  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function exportInvoicePDF(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role   = $user->role;

        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? session('selectedSubAdminId') ?? $user->id;

        if ($role === 'staff') {
            // Get the staff's branch ID from their user record
            $branchIdForSetting = $user->branch_id;
        } elseif ($role === 'sub-admin') {
            $branchIdForSetting = $userId; // sub-admin's own branch
        } elseif ($role === 'admin') {
            $branchIdForSetting = $selectedSubAdminId ?? $userId; // admin-selected branch
        } else {
            $branchIdForSetting = $userId; // fallback
        }
        $setting = Setting::where('branch_id', $branchIdForSetting)->first();

        try {
            $query = CustomInvoice::with(['vendor:id,name', 'customer:id,name', 'payments']);
            // ->where('branch_id', $selectedSubAdminId);

            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            if ($request->filled('date')) {
                $inputDate = trim($request->date);
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $inputDate)) {
                    [$day, $month, $year] = explode('-', $inputDate);
                    $formattedDate        = "$year-$month-$day";
                } else {
                    $formattedDate = $inputDate;
                }
                $query->whereDate('created_at', $formattedDate);
            }

            // role based
            // if ($role === 'sub-admin') {
            //     $query->where('branch_id', $userId);
            // } elseif ($role === 'admin' && $selectedSubAdminId) {
            //     $query->where('branch_id', $selectedSubAdminId);
            // } else {
            //     $query->where('branch_id', $userId);
            // }
            // Role-based filtering
            if ($role === 'staff') {
                // Staff sees only invoices they created
                $query->where('created_by', $userId);
            } elseif ($role === 'sub-admin') {
                // Sub-admin sees invoices for their branch
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin') {
                // Admin can select a sub-admin branch, else default to their own branch
                $branchIdToUse = $selectedSubAdminId ?? $userId;
                $query->where('branch_id', $branchIdToUse);
            } else {
                // Fallback: default to user's own branch
                $query->where('branch_id', $userId);
            }
            // dd($userId);

            $invoices = $query->orderBy('created_at', 'desc')->get();

            if ($invoices->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No invoices found for the given criteria.',
                ], 404);
            }

            $payments = PaymentStore::whereIn('custom_invoice_id', $invoices->pluck('id'))->get();

            $pdf = Pdf::loadView('custom-invoice.custom_invoice_pdf', [
                'invoices' => $invoices,
                'payments' => $payments,
                'setting'  => $setting,
            ])->setPaper('A4', 'portrait');

            $filename = 'CustomInvoices_' . date('Ymd_His') . '.pdf';

            return $pdf->download($filename, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // public function custom_invoice_pdf($id)
    // {
    //     $view_id = $id;
    //     $invoice = CustomInvoice::find($view_id);
    //     $setting = Setting::first();

    //     if (!$invoice) {
    //         return redirect()->route('sales.list')->with('error', 'Invoice not found.');
    //     }

    //     $vendor = $invoice->vendor_id ? User::find($invoice->vendor_id) : null;
    //     $customer = $invoice->customer_id ? User::find($invoice->customer_id) : null;

    //     $formatCurrency = function ($amount) use ($setting) {
    //         $amount = number_format($amount, 2);
    //         return $setting->currency_position === 'right'
    //             ? $amount . $setting->currency_symbol
    //             : $setting->currency_symbol . $amount;
    //     };

    //     $taxDetails = json_decode($invoice->taxes, true) ?? [];

    //     // Fetch invoice items with product data
    //     $invoiceItems = CustomInvoiceItem::where('invoice_id', $view_id)
    //         ->with('product')
    //         ->get();

    //     $subtotal = $invoiceItems->sum('amount_total');
    //     $discountPercentage = $invoice->discount ?? 0;
    //     $discountAmount = ($subtotal * $discountPercentage) / 100;
    //     $afterDiscount = $subtotal - $discountAmount;
    //     $shipping = $invoice->shipping ?? 0;
    //     $totalTaxAmount = collect($taxDetails)->sum('amount');
    //     $finalTotal = $afterDiscount + $totalTaxAmount + $shipping;

    //     $pdfData = [
    //         'view_id' => $view_id,
    //         'sales' => $invoice,
    //         'setting' => $setting,
    //         'orderItems' => $invoiceItems,
    //         'salesItems' => $invoiceItems,
    //         'customer' => $customer ? $customer->toArray() : null,
    //         'vendor' => $vendor ? $vendor->toArray() : null,
    //         'subtotal' => $formatCurrency($subtotal),
    //         'discountAmount' => $formatCurrency($discountAmount),
    //         'afterDiscount' => $formatCurrency($afterDiscount),
    //         'shipping' => $formatCurrency($shipping),
    //         'finalTotal' => $formatCurrency($finalTotal),
    //         'paymentStatus' => $invoiceItems->first()?->payment_status ?? 'unpaid',
    //         'taxDetails' => $taxDetails
    //     ];

    //     $pdf = PDF::loadView('custom-invoice.custom-invoice-pdf', $pdfData);
    //     return $pdf->stream('invoice_' . $view_id . '.pdf');
    // }

    public function custom_invoice_pdf($id)
    {
        $user = Auth::user();

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }
        $view_id = $id;
        $invoice = CustomInvoice::find($view_id);
        $setting = Setting::where('branch_id', $branch_id)->first();

        if (! $invoice) {
            return redirect()->route('sales.list')->with('error', 'Invoice not found.');
        }

        // Load vendor or customer
        $vendor   = $invoice->vendor_id ? User::with('details')->find($invoice->vendor_id) : null;
        $customer = $invoice->customer_id ? User::with('details')->find($invoice->customer_id) : null;

        // Decide party (customer or vendor)
        $party = $vendor ?: $customer;

        $formatCurrency = function ($amount) use ($setting) {
            $amount = number_format($amount, 2);
            return $setting->currency_position === 'right'
                ? $amount . $setting->currency_symbol
                : $setting->currency_symbol . $amount;
        };

        $taxDetails = json_decode($invoice->taxes, true) ?? [];

        $invoiceItems = CustomInvoiceItem::where('invoice_id', $view_id)
            ->with('product')
            ->get();

        $subtotal           = $invoiceItems->sum('amount_total');
        $discountPercentage = $invoice->discount ?? 0;
        $discountAmount     = ($subtotal * $discountPercentage) / 100;
        $afterDiscount      = $subtotal - $discountAmount;
        $shipping           = $invoice->shipping ?? 0;
        $totalTaxAmount     = collect($taxDetails)->sum('amount');
        $finalTotal         = $afterDiscount + $totalTaxAmount + $shipping;

        // Prepare party info (customer/vendor)
        $partyDetails = [
            'role'       => ucfirst($party->role ?? 'Party'),
            'name'       => $party->name ?? '--',
            'email'      => $party->email ?? '--',
            'phone'      => $party->phone ?? '--',
            'gst_number' => $party->gst_number ?? '--',
            'pan_number' => $party->pan_number ?? '--',
            'address'    => optional($party->details)->address ?? '--',
            'city'       => optional($party->details)->city ?? '--',
            'country'    => optional($party->details)->country ?? '--',
        ];

        $pdfData = [
            'view_id'        => $view_id,
            'sales'          => $invoice,
            'setting'        => $setting,
            'orderItems'     => $invoiceItems,
            'salesItems'     => $invoiceItems,
            'partyDetails'   => $partyDetails,
            'subtotal'       => $formatCurrency($subtotal),
            'discountAmount' => $formatCurrency($discountAmount),
            'afterDiscount'  => $formatCurrency($afterDiscount),
            'shipping'       => $formatCurrency($shipping),
            'finalTotal'     => $formatCurrency($finalTotal),
            'paymentStatus'  => $invoiceItems->first()?->payment_status ?? 'unpaid',
            'taxDetails'     => $taxDetails,
        ];

        $pdf = PDF::loadView('custom-invoice.custom-invoice-pdf', $pdfData);
        return $pdf->stream('invoice_' . $view_id . '.pdf');
    }

    public function show($id)
    {
        $invoice     = CustomInvoice::findOrFail($id);
        $user        = $invoice->vendor_id ? User::find($invoice->vendor_id) : User::find($invoice->customer_id);
        $companyInfo = Setting::first();

        // Get currency settings
        $currencySymbol   = $companyInfo->currency_symbol ?? '₹';
        $currencyPosition = $companyInfo->currency_position ?? 'left';

        return view('custom-invoice.view', compact('invoice', 'user', 'companyInfo', 'currencySymbol', 'currencyPosition'));
    }
}
