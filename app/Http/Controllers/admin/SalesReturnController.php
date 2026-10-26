<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Http\Request;

class SalesReturnController extends Controller
{
    public function createsales_return(Request $request)
    {
        $user           = auth()->user();
        $UserID         = $user->id;
        $role           = $user->role;
        $userBranch     = $user->branch_id;
        $selectedBranch = session('selectedSubAdminId');

        // ✅ Decide branch correctly
        if ($role == 'admin' && $selectedBranch) {
            $branchId = $selectedBranch;
        } elseif ($role == 'sub-admin') {
            $branchId = $UserID;
        } elseif ($role == 'staff') {
            $branchId = $userBranch;
        } else {
            $branchId = $UserID;
        }
        // dd($branchId);
        if ($role == 'staff') {
            $invoiceNumbers = Order::where('isDeleted', 0)
                ->where('created_by', $UserID)
                ->where('remaining_amount', '>', 0)
                ->whereDoesntHave('payments')
                ->whereHas('user', function ($query) {
                    $query->where('isDeleted', 0);
                })
                ->pluck('order_number');
        } else {
            $invoiceNumbers = Order::where('isDeleted', 0)
                ->where('branch_id', $branchId)
                ->where('remaining_amount', '>', 0)
                ->whereDoesntHave('payments')
                ->whereHas('user', function ($query) {
                    $query->where('isDeleted', 0);
                })
                ->pluck('order_number');
        }
        // dd($branchId);
        $settings         = \DB::table('settings')->where('branch_id', $branchId)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $TaxRate = TaxRate::where('status', 'active')->where('branch_id', $branchId)->where('isDeleted', 0)->get();

        return view('salesreturn/createsalesreturn', compact(
            'invoiceNumbers',
            'TaxRate',
            'currencySymbol',
            'currencyPosition'
        ));
    }

    public function createsales_returns(Request $request)
    {
        return view('salesreturn/createsalesreturns');
    }
    public function editsales_return(Request $request)
    {
        return view('salesreturn/editsalesreturn');
    }
    public function editsales_returns(Request $request)
    {
        return view('salesreturn/editsalesreturns');
    }
    public function salesreturn_list(Request $request)
    {
        return view('salesreturn/salesreturnlist');
    }
    public function salesreturn_lists(Request $request)
    {
        return view('salesreturn/salesreturnlists');
    }
}
