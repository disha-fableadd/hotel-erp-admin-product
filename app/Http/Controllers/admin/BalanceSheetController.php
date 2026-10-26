<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceSheetController extends Controller
{
//     public function index(Request $request)
// {
//     $user = Auth::user();

//     $subAdminId = session('selectedSubAdminId', $user->id);
//     $filter = $request->query('filter'); // e.g., this_month, this_year, previous_year, etc.

//     // Currency settings
//     $settings = DB::table('settings')->first();
//     $currencySymbol = $settings->currency_symbol ?? '₹';
//     $currencyPosition = $settings->currency_position ?? 'left';

//     // Accounts Receivable: Orders not fully paid (payment_status != 'completed')
//     $accountsReceivable = (float) Order::where('isDeleted', 0)
//         ->when($subAdminId, fn($q) => $q->where('branch_id', $subAdminId))
//         ->where(function ($q) {
//             $q->whereNull('payment_status')
//               ->orWhere('payment_status', '!=', 'completed');
//         })
//         ->sum('total_amount');

//     // Accounts Payable: Sum of (grand_total - paid) for purchase invoices
//     $purchaseInvoices = PurchaseInvoice::where('isDeleted', 0)
//         ->when($subAdminId, fn($q) => $q->where('branch_id', $subAdminId))
//         ->get();

//     $accountsPayable = 0.0;
//     foreach ($purchaseInvoices as $pi) {
//         $total = (float)($pi->grand_total ?? ($pi->total_amount ?? 0));
//         $paid = (float)($pi->paid ?? 0);
//         $due  = max(0, $total - $paid);
//         $accountsPayable += $due;
//     }

//     // Inventory valuation: current stock valued at average purchase cost per product (fallback to product price)
//     $avgCosts = Purchases::where('isDeleted', 0)
//             ->when($subAdminId, fn($q) => $q->where('branch_id', $subAdminId))
//             ->select('item', DB::raw('AVG(price) as avg_price'))
//             ->groupBy('item')
//             ->pluck('avg_price', 'item');

//     $inventoryValue = Product::where('isDeleted', 0)
//         ->where('status', 'active')
//         ->when($subAdminId, fn($q) => $q->where('branch_id', $subAdminId))
//         ->get()
//         ->sum(function ($p) use ($avgCosts) {
//             $qty  = (float)($p->quantity ?? 0);
//             $cost = (float)($avgCosts[$p->id] ?? $p->price ?? 0);
//             return $qty * $cost;
//         });

//     // GST Net (Tax Payable or ITC) using existing GST report service
//     $gstService = new GstReportService();
//     $gstr = $gstService->getGstr3bData($filter);

//     $salesTaxes = (
//         ($gstr['total_sales']['igst_payable'] ?? 0) +
//         ($gstr['total_sales']['cgst_payable'] ?? 0) +
//         ($gstr['total_sales']['sgst_payable'] ?? 0)
//     );
//     $purchaseTaxes = (
//         ($gstr['total_purchase']['igst_receivable'] ?? 0) +
//         ($gstr['total_purchase']['cgst_receivable'] ?? 0) +
//         ($gstr['total_purchase']['sgst_receivable'] ?? 0)
//     );
//     $netGst = $salesTaxes - $purchaseTaxes;
//     $gstPayable = $netGst > 0 ? $netGst : 0.0; // Liability
//     $gstCredit  = $netGst < 0 ? abs($netGst) : 0.0; // Asset (ITC)

//     // ✅ Cash & Bank: Get from completed sales payments (cash + online)
//     $salesBaseQuery = Order::where('orders.isDeleted', 0)
//     ->when($subAdminId, fn($q) => $q->where('orders.branch_id', $subAdminId))
//     ->where('orders.payment_status', 'completed');

//     $salesCash = (clone $salesBaseQuery)
//         ->leftJoin('payment_store', 'payment_store.order_id', '=', 'orders.id')
//         ->where('payment_store.payment_method', 'cash')
//         ->sum('orders.total_amount');

//     $salesOnline = (clone $salesBaseQuery)
//         ->leftJoin('payment_store', 'payment_store.order_id', '=', 'orders.id')
//         ->where('payment_store.payment_method', 'online')
//         ->sum('orders.total_amount');

//     $cashAndBank = $salesCash + $salesOnline;

//     // Build sections
//     $currentAssets = [
//         'cash_bank' => round($cashAndBank, 2),
//         'cash' => round($salesCash, 2),
//         'bank' => round($salesOnline, 2),
//         'accounts_receivable' => round($accountsReceivable, 2),
//         'inventory' => round($inventoryValue, 2),
//         'input_tax_credit' => round($gstCredit, 2),
//     ];
//     $totalAssets = array_sum($currentAssets);

//     $currentLiabilities = [
//         'accounts_payable' => round($accountsPayable, 2),
//         'gst_payable' => round($gstPayable, 2),
//     ];
//     $totalLiabilities = array_sum($currentLiabilities);

//     $equity = [
//         'retained_earnings' => round($totalAssets - $totalLiabilities, 2),
//     ];
//     $totalLiabilitiesAndEquity = $totalLiabilities + $equity['retained_earnings'];

//     return view('accounting.balance-sheet', [
//         'filter' => $filter,
//         'period' => [
//             'from' => $gstr['from_date'] ?? null,
//             'to' => $gstr['to_date'] ?? null,
//         ],
//         'currency_symbol' => $currencySymbol,
//         'currency_position' => $currencyPosition,
//         'assets' => $currentAssets,
//         'liabilities' => $currentLiabilities,
//         'equity' => $equity,
//         'totals' => [
//             'assets' => round($totalAssets, 2),
//             'liabilities' => round($totalLiabilities, 2),
//             'liabilities_equity' => round($totalLiabilitiesAndEquity, 2),
//         ],
//     ]);
// }

    public function index(Request $request)
    {
        $branchId = session('selectedSubAdminId'); // or Auth::user()->branch_id if single branch

        // ======================
        // 🏷️ Currency Settings
        // ======================
        $settings = DB::table('settings')->first();
        $sym      = $settings->currencySymbol ?? '₹';
        $pos      = $settings->currencyPosition ?? 'left';

        // ======================
        // 🧾 1️⃣ ASSETS
        // ======================

        // Cash (sum of all recorded cash payments)
        // Cash (only order payments)
        // Cash (only order payments)
        $cash = DB::table('payment_store')
            ->join('orders', 'orders.id', '=', 'payment_store.order_id')
            ->where('payment_store.isDeleted', 0)
            ->where('payment_store.payment_method', 'cash')
            ->where(function ($q) {
                $q->whereNotNull('payment_store.order_id')
                    ->where('payment_store.order_id', '<>', '')
                    ->where('payment_store.order_id', '>', 0);
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.purchase_id')
                    ->orWhere('payment_store.purchase_id', '=', 0)
                    ->orWhere('payment_store.purchase_id', '=', '');
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.custom_invoice_id')
                    ->orWhere('payment_store.custom_invoice_id', '=', 0)
                    ->orWhere('payment_store.custom_invoice_id', '=', '');
            })
            ->when($branchId, fn($q) => $q->where('orders.branch_id', $branchId))
            ->sum('payment_store.payment_amount');

            // Bank (UPI / Bank / Online)
        $bank = DB::table('payment_store')
            ->join('orders', 'orders.id', '=', 'payment_store.order_id')
            ->where('payment_store.isDeleted', 0)
            ->whereIn('payment_store.payment_method', ['upi', 'bank', 'online'])
            ->where(function ($q) {
                $q->whereNotNull('payment_store.order_id')
                    ->where('payment_store.order_id', '<>', '')
                    ->where('payment_store.order_id', '>', 0);
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.purchase_id')
                    ->orWhere('payment_store.purchase_id', '=', 0)
                    ->orWhere('payment_store.purchase_id', '=', '');
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.custom_invoice_id')
                    ->orWhere('payment_store.custom_invoice_id', '=', 0)
                    ->orWhere('payment_store.custom_invoice_id', '=', '');
            })
            ->when($branchId, fn($q) => $q->where('orders.branch_id', $branchId))
            ->sum('payment_store.payment_amount');

        // Inventory value (sum of product quantity * price)
        // Assuming your `products` table has `quantity` and `price` columns
        $inventory = DB::table('products')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum(DB::raw('quantity * price'));

        // Total Assets
        $totalAssets = $cash + $bank + $inventory;

        $assets = [
            'cash'      => $cash,
            'bank'      => $bank,
            'inventory' => $inventory,
        ];

        // ======================
        // 💸 2️⃣ LIABILITIES
        // ======================

        // Accounts Payable (Unpaid purchase invoices)
        $accountsPayable = DB::table('purchase_invoice')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('remaining_amount');

        // GST Payable (GST from Orders marked as with GST)
        $gstPayable = DB::table('orders')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('gst_option', 1)
            ->sum(DB::raw('(total_amount * 18) / 100')); // change 18 if dynamic tax_id system

        $totalLiabilities = $accountsPayable + $gstPayable;

        $liabilities = [
            'accounts_payable' => $accountsPayable,
            'gst_payable'      => $gstPayable,
        ];

        // ======================
        // 📊 3️⃣ EQUITY
        // ======================

        // Net Profit = Total Sales - (Purchases + Expenses)
        $totalSales = DB::table('orders')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $totalPurchases = DB::table('purchase_invoice')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('grand_total');

        $totalExpenses = DB::table('expenses')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        $netProfit = $totalSales - ($totalPurchases + $totalExpenses);

        // Retained Earnings = Total Assets - Total Liabilities + Net Profit
        $retainedEarnings = ($totalAssets - $totalLiabilities) + $netProfit;

        $equity = [
            'retained_earnings' => $retainedEarnings,
        ];

        // ======================
        // 📘 4️⃣ TOTALS
        // ======================
        $totals = [
            'assets'             => $totalAssets,
            'liabilities'        => $totalLiabilities,
            'liabilities_equity' => $totalLiabilities + $retainedEarnings,
        ];

        // ======================
        // 📄 5️⃣ RETURN VIEW
        // ======================
        return view('accounting.balance-sheet', compact(
            'assets',
            'liabilities',
            'equity',
            'totals',
            'sym',
            'pos'
        ));
    }
}
