<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseTypeController extends Controller
{
    public function store(Request $request)
    {
        // $user = Auth::guard('api')->user();
        // $userId = $user->id;
        // $userBranchId = $user->branch_id;
        // if (! empty($request->selectedSubAdminId)) {
        //     $userBranchId = $request->selectedSubAdminId;
        // }
        $authUser = Auth::guard('api')->user();
        $userId   = $authUser->id;
        if ($authUser->role === 'staff') {
            // dd(1);
            $userBranch = $authUser->branch_id;
        } elseif ($authUser->role === 'sub-admin') {
            // dd(2);
            $userBranch = $userId;
        } elseif ($authUser->role == "admin" && ! empty($request->selectedSubAdminId) || $request->selectedSubAdminId != null) {
            // dd(3);
            $userBranch = $request->selectedSubAdminId;
        } else {
            // dd(4);
            $userBranch = $userId;
        }
        $request->validate(
            [
                'type' => 'required|string|max:255|unique:expense_types,type',
            ],
            [], // Custom error messages (optional)
            [
                'type' => 'expense type', // Custom attribute name
            ]
        );

        $expenseType = ExpenseType::create([
            'type'       => $request->type,
            'isDeleted'  => 0,
            'branch_id'       => $userBranch ?? $userId,
            'created_by'       => $userId,
        ]);

        return response()->json(['message' => 'Expense type added successfully.']);
    }

    // Example: ExpenseTypeController.php
    // public function list(Request $request)
    // {
    //     $user     = Auth::guard('api')->user();
    //     $userRole = $user->role ?? null;

    //     $query = ExpenseType::where('isDeleted', 0);

    //     if ($request->has('selectedSubAdminId') && ! empty($request->selectedSubAdminId)) {
    //         $subAdmin = User::find($request->selectedSubAdminId);
    //         if ($subAdmin) {
    //             $query->where('branch_id', $subAdmin->id);
    //         }
    //     } elseif ($userRole === 'sub-admin') {
    //         $query->where('branch_id', $user->id);
    //     } elseif ($userRole === 'admin') {
    //         $query->where('branch_id', $user->id);
    //     } elseif ($userRole === 'staff') {
    //         $query->where('created_by', $user->id);
    //     }


    //     $data = $query->orderBy('id', 'desc')->get();
    //     // dd($data); 
    //     return response()->json([
    //         'status' => true,
    //         'data'   => $data,
    //     ]);
    // }

    public function list(Request $request)
    {
        $authUser = Auth::guard('api')->user();

        if (!$authUser) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $query = ExpenseType::where('isDeleted', 0);

        $userId       = $authUser->id;
        $userRole     = $authUser->role;
        $userBranchId = $authUser->branch_id;

        // 🔹 Apply filtering based on role and context
        if (!empty($request->selectedSubAdminId)) {
            // Admin viewing a specific sub-admin's data (priority: checked first)
            $query->where('branch_id', $request->selectedSubAdminId);
        } elseif ($userRole === 'staff') {
            // Staff sees only their branch's data
            $query->where('branch_id', $userBranchId);
        } elseif ($userRole === 'sub-admin') {
            // Sub-admin sees only their branch data
            $query->where('branch_id', $userId);
        } elseif ($userRole === 'admin') {
            // Admin sees their own branch data
            $query->where('branch_id', $userId);
        }

        $data = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }


    public function show($id)
    {
        $expenseType = ExpenseType::where('id', $id)->where('isDeleted', 0)->first();

        if (! $expenseType) {
            return response()->json([
                'message' => 'Expense type not found.',
            ], 404);
        }

        return response()->json([
            'data' => $expenseType,
        ], 200);
    }

    // PUT /api/expense-types/{id} - update expense type
    public function update(Request $request, $id)
    {
        $expenseType = ExpenseType::where('id', $id)->where('isDeleted', 0)->first();

        if (! $expenseType) {
            return response()->json([
                'message' => 'Expense type not found.',
            ], 404);
        }

        // Validate input
        $validator = Validator::make(
            $request->all(),
            [
                'type' => 'required|string|max:255|unique:expense_types,type,' . $id,
            ],
            [], // Custom error messages (optional)
            [
                'type' => 'expense type', // Custom attribute name
            ]

        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update and save
        $expenseType->type = $request->input('type');
        $expenseType->save();

        return response()->json([
            'message' => 'Expense type updated successfully.',
            'data'    => $expenseType,
        ], 200);
    }
    public function destroy($id)
    {
        $expense = ExpenseType::find($id);

        if (! $expense) {
            return response()->json(['error' => 'Expense type not found.'], 404);
        }

        // Soft delete by setting isDeleted = 1
        $expense->isDeleted = 1;
        $expense->save();

        return response()->json(['success' => 'Expense type deleted successfully.']);
    }
}
