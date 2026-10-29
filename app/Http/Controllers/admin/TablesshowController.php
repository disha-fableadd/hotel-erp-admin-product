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

        $categories = Category::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();


        // ---- TABLE GROUPING LOGIC ----
        $tables = Table_no::where(function($q) use ($branchIdToUse) {
                if (! empty($branchIdToUse)) {
                    $q->where('branch_id', $branchIdToUse);
                }
            })
            ->where('isDeleted', 0)
            ->orderBy('table_type')
            ->orderBy('table_name')
            ->get()
            ->groupBy('table_type'); // Gives → ['ac'=>[], 'nonac'=>[], 'premium'=>[]]

        $tableCollection = $tables->flatten();
        $runningOrders = Order::whereIn('table_no', $tableCollection->pluck('id'))
            ->where('table_status', 1)
            ->where('isDeleted', 0)
            ->when(! empty($branchIdToUse), function ($query) use ($branchIdToUse) {
                $query->where('branch_id', $branchIdToUse);
            })
            ->orderByDesc('id')
            ->get(['id', 'table_no', 'total_amount'])
            ->unique('table_no')
            ->keyBy('table_no');

        $tableCollection->each(function ($table) use ($runningOrders) {
            $order = $runningOrders->get($table->id);
            if ($order) {
                $table->order_id = $order->id;
                $table->running_amount = $order->total_amount;
            }
        });

        return view('tables.tables_view', compact('staff', 'tables','currency_position','taxRates','currency_symbol','categories'));
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
