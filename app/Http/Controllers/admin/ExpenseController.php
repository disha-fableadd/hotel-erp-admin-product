<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    // public function create_expense()
    // {
    //     $user      = Auth::user();
    //     $branch_id = $user->id;

    //     $selectedSubAdminId = (session('selectedSubAdminId'));

    //     if ($user->role === 'staff' && $user->branch_id) {
    //         $branch_id = $user->branch_id;
    //     } elseif (! empty($selectedSubAdminId)) {
    //         $branch_id = $selectedSubAdminId;
    //     } else {
    //         $branch_id = $user->id;
    //     }

    //     $expenseTypes = ExpenseType::where('branch_id', $branch_id)->where('isDeleted', 0)->get();
    //     return view('expense.createexpense', compact('expenseTypes'));
    // }
    public function create_expense()
    {
        $user               = Auth::user();
        $branch_id          = $user->id;
        $selectedSubAdminId = session('selectedSubAdminId');

        // 🔹 Determine correct branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif (! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        // 🔹 Fetch expense types based on role
        if ($user->role === 'staff') {
            // ✅ Staff sees only the expense types they created
            $expenseTypes = ExpenseType::where('created_by', $user->id)
                ->where('isDeleted', 0)
                ->get();
        } else {
            // ✅ Admin/Sub-admin see based on branch
            $expenseTypes = ExpenseType::where('branch_id', $branch_id)
                ->where('isDeleted', 0)
                ->get();
        }

        return view('expense.createexpense', compact('expenseTypes'));
    }

    public function edit_expense()
    {

        return view('expense/editexpense');
    }
    public function expense_category()
    {
        $user      = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = (session('selectedSubAdminId'));

        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif (! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        $expenseTypes = ExpenseType::where('branch_id', $branch_id)->where('isDeleted', 0)->get();

        return view('expense/expensecategory', compact('expenseTypes'));
    }
    public function expense_list()
    {
        $user      = Auth::user();
        $branch_id = $user->id;
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif (! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }
        // Get distinct years from expense_date for dropdown
        $years = Expense::where('branch_id', $branch_id)->where('isDeleted', 0)
            ->selectRaw('YEAR(expense_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // Pass years to your view
        return view('expense.expenselist', compact('years'));
    }

    public function expense_report(Request $request)
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

         if ($userRole === 'staff') {
            $expenseTypes = ExpenseType::where('isDeleted', 0)
                ->where('branch_id', $UserBranchId)
                ->orderBy('type')
                ->get();
        } else {
            $expenseTypes = ExpenseType::where('isDeleted', 0)
                ->where('branch_id', $branchIdToUse)
                ->orderBy('type')
                ->get();
        }
        return view('expense/expensereport',compact('expenseTypes'));
    }
    public function expense_report_view($ids)
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
        $idsArray = explode(',', $ids); // Convert "1,2,3" to [1,2,3]

        $expenses = Expense::whereIn('id', $idsArray)->where('branch_id', $branchId)->get();

        if ($expenses->isEmpty()) {
            return redirect()->route('expense.report')->with('error', 'No expense data found.');
        }

        $settings = Setting::where('branch_id', $branchId)->first();

        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return view('expense.expense_report', compact('expenses', 'currencySymbol', 'settings', 'currencyPosition', 'ids'));
    }

    public function expense_report_pdf($ids)
    {
        $user         = Auth()->user();
        $branchId     = $user->id ?? null;
        $UserBranchId = $user->branch_id ?? null;
        $userRole     = $user->role ?? '';
        $subAdminId   = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($userRole === 'sub-admin') {
            $branchId = $branchId;
        } elseif ($userRole === 'admin' && $subAdminId) {
            $branchId = $subAdminId;
        } elseif ($userRole === 'staff') {
            $branchId = $UserBranchId;
        } else {
            $branchId = $branchId;
        }

        // Convert "1,2,3" to [1,2,3]
        $idsArray = explode(',', $ids);

        // 🔹 Fetch expenses
        $expenses = Expense::whereIn('id', $idsArray)
            ->where('branch_id', $branchId)
            ->get();

        if ($expenses->isEmpty()) {
            return redirect()->route('expense.report')->with('error', 'No expense data found.');
        }

        // 🔹 Company settings
        $setting = Setting::where('branch_id', $branchId)->first();

        $currencySymbol   = $setting->currency_symbol ?? '₹';
        $currencyPosition = $setting->currency_position ?? 'left';

        // 🔹 Pass data to PDF
        $pdfData = [
            'expenses'         => $expenses,
            'setting'          => $setting,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
        ];

        $pdf = PDF::loadView('expense.expense-report-pdf', $pdfData)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->download('expense_report.pdf');
    }


    

public function show_expense_report_page(Request $request)
    {
        try {
            $ids = $request->query('ids');
            $branchId = $request->query('branch');

            if (empty($ids) || empty($branchId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Missing ids or branch parameters.'
                ], 400);
            }

            $idsArray = explode(',', $ids);

            // 🔹 Fetch expense data
            $expenses = Expense::whereIn('id', $idsArray)
                ->where('branch_id', $branchId)
                ->get();

            if ($expenses->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No expense data found for the provided IDs.'
                ], 404);
            }

            // 🔹 Get settings and currency info
            $settings = Setting::where('branch_id', $branchId)->first();
            $currencySymbol = $settings->currency_symbol ?? '₹';
            $currencyPosition = $settings->currency_position ?? 'left';

            // 🔹 Prepare data for Blade
            $data = [
                'expenses' => $expenses,
                'settings' => $settings,
                'currencySymbol' => $currencySymbol,
                'currencyPosition' => $currencyPosition,
            ];

            // 🔹 Return Blade view
            return view('expense.web_expense_report', $data);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to load expense report page.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
