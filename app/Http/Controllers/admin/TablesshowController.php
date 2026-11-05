<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Table_no;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdvancePayment;


class TablesshowController extends Controller
{
    public function index()
    {
        return view('advance_pay.index');
    }

    public function create()
    {
        $user = auth()->user();
        $userRole = $user->role ?? '';
        $userId = $user->id ?? null;
        $subAdminId = session('selectedSubAdminId');

        // Resolve branch id to use for loading branch-scoped data
        if ($userRole === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($userRole === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } elseif ($userRole === 'sub-admin') {
            $branchIdToUse = $userId;
        } else {
            $branchIdToUse = $userId;
        }

        // ---- STAFF FILTER LOGIC ----
        if ($userRole === 'sub-admin') {

            // Sub-admin sees only their staff
            $staff = User::where('role', 'staff')
                ->where('isDeleted', 0)
                ->where('branch_id', $userId)
                ->get();

        } elseif (!empty($subAdminId)) {

            // Admin selected a sub-admin → show staff of selected sub-admin
            $staff = User::where('role', 'staff')
                ->where('isDeleted', 0)
                ->where('branch_id', $subAdminId)
                ->get();

        } elseif ($userRole === 'admin') {

            // Admin sees their own branch staff
            $staff = User::where('role', 'staff')
                ->where('isDeleted', 0)
                ->where('branch_id', $userId)
                ->get();

        } else {
            // Fallback: No role matched
            $staff = collect();
        }
    $setting = Setting::where('branch_id', $branchIdToUse)->first();
    $currency_symbol = $setting->currency_symbol ?? '₹';
    $currency_position = $setting->currency_position ?? 'left';


        $taxRates = TaxRate::where('status', 'active')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        // Same category set as takeaway POS: active POS products (qty 0 allowed)
        $categories = Category::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->with(['products' => function ($q) use ($branchIdToUse) {
                $q->where('isDeleted', 0)
                    ->where('status', 'active')
                    ->where(function ($branch) use ($branchIdToUse) {
                        // Match takeaway API: admin may see branch products; also include null branch rows
                        $branch->where('branch_id', $branchIdToUse)->orWhereNull('branch_id');
                    })
                    ->where(function ($pos) {
                        $pos->where('is_pos_available', 1)->orWhereNull('is_pos_available');
                    });
            }])
            ->orderBy('name')
            ->get();


        // ---- TABLE GROUPING LOGIC ----
        $tables = Table_no::with(['order' => function ($q) {
                $q->select('id', 'total_amount', 'table_status', 'bill_printed', 'isDeleted');
            }])
            ->where(function($q) use ($branchIdToUse) {
                if (! empty($branchIdToUse)) {
                    $q->where('branch_id', $branchIdToUse);
                }
            })
            ->where('isDeleted', 0)
            ->orderBy('table_type')
            ->get()
            // Natural numeric order: Table 1, 2, 3… (not 1, 10, 11…)
            ->sortBy(function ($table) {
                $name = (string) ($table->table_name ?? '');
                preg_match('/(\d+)/', $name, $m);
                $num = isset($m[1]) ? (int) $m[1] : PHP_INT_MAX;

                return sprintf('%010d-%s', $num, mb_strtolower($name));
            })
            ->values()
            ->groupBy('table_type'); // Gives → ['AC'=>[], 'Non AC'=>[], 'Premium'=>[], 'Rooftop'=>[]]

        $tableCollection = $tables->flatten();
        $runningOrders = Order::whereIn('table_no', $tableCollection->pluck('id'))
            ->where('table_status', 1)
            ->where('isDeleted', 0)
            ->when(! empty($branchIdToUse), function ($query) use ($branchIdToUse) {
                $query->where('branch_id', $branchIdToUse);
            })
            ->orderByDesc('id')
            ->get(['id', 'table_no', 'total_amount', 'bill_printed'])
            ->unique('table_no')
            ->keyBy('table_no');

        // Latest closed/paid bill per table (for "Update Last" on available tables)
        $lastClosedOrders = Order::whereIn('table_no', $tableCollection->pluck('id'))
            ->where('isDeleted', 0)
            ->where(function ($q) {
                $q->where('table_status', 0)
                    ->orWhereNull('table_status');
            })
            ->when(! empty($branchIdToUse), function ($query) use ($branchIdToUse) {
                $query->where('branch_id', $branchIdToUse);
            })
            ->orderByDesc('id')
            ->get(['id', 'table_no', 'total_amount', 'payment_status'])
            ->unique('table_no')
            ->keyBy('table_no');

        $tableCollection->each(function ($table) use ($runningOrders, $lastClosedOrders) {
            $order = $runningOrders->get($table->id);
            if ($order) {
                $table->order_id = $order->id;
                $table->setRelation('order', $order);
                $table->running_amount = $order->total_amount;
                if (! empty($order->bill_printed)) {
                    $table->status = 'printed';
                    $table->table_status = 3;
                } elseif ((string) ($table->status ?? '') !== 'reserved') {
                    $table->status = 'running';
                    $table->table_status = 1;
                }
            }

            $lastClosed = $lastClosedOrders->get($table->id);
            $table->last_order_id = $lastClosed ? (int) $lastClosed->id : null;
        });

        $can_item_discount = (bool) ($setting->allow_item_level_discount ?? false);

        $customers = User::where('role', 'customer')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('tables.tables_view', compact(
            'staff',
            'tables',
            'currency_position',
            'taxRates',
            'currency_symbol',
            'categories',
            'can_item_discount',
            'setting',
            'customers'
        ));
    }
    public function show($id)
    {
        return view('advance_pay.show', compact('id'));
    }

    public function edit($id)
    {
        $payment = AdvancePayment::findOrFail($id);
        $user = auth()->user();
        $userRole = $user->role ?? '';
        $userId = $user->id ?? null;
        $subAdminId = session('selectedSubAdminId');
        if ($userRole === 'sub-admin') {
            // Sub-admin sees only their staff
            $staff = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userId)->get();
        } else if (!empty($subAdminId)) {
            // Admin sees all staff
            $staff = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $subAdminId)->get();
        } elseif ($userRole === 'admin') {
            // Admin sees all staff
            $staff = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userId)->get();
        }
        return view('advance_pay.edit', compact('payment', 'staff'));
    }
}
