<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\CustomInvoice;
use App\Models\Table_no;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    private function resolveBranchId(Request $request): ?int
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return null;
        }

        $role = strtolower((string) ($user->role ?? ''));

        return match ($role) {
            'staff'     => (int) ($user->branch_id ?? $user->id),
            'admin'     => (int) ($request->input('selectedSubAdminId')
                ?: $request->input('sub_admin_id')
                ?: $user->id),
            'sub-admin' => (int) ($user->branch_id ?? $user->id),
            default     => (int) ($user->branch_id ?? $user->id),
        };
    }

    // public function getAllCustomer(Request $request)
    // {
    //     $user         = Auth::guard('api')->user();
    //     $userBranchId = $user->id ?? null;

    //     // if (!empty($request->selectedSubAdminId)) {
    //     //     $userBranchId = $request->selectedSubAdminId;
    //     // }

    //     if ($user->role === 'staff' && $user->branch_id) {
    //         $userBranchId = $user->branch_id;
    //     } elseif (! empty($request->selectedSubAdminId)) {
    //         $userBranchId = $request->selectedSubAdminId;
    //     } else {
    //         $userBranchId = $user->id;
    //     }
    //     // $customers = User::select('id', 'name', 'email', 'phone', 'profile_image', 'gst_number', 'pan_number')
    //     //     ->where('role', 'customer')
    //     //     ->where('branch_id', $userBranchId)
    //     //     ->where('isDeleted', 0)
    //     //     ->with('details:country,user_id,city')
    //     //     ->orderBy('id', 'desc')
    //     //     ->get()
    //     //     ->map(function ($customer) {
    //     $query = User::select('id', 'name', 'email', 'phone', 'profile_image', 'gst_number', 'pan_number')
    //         ->where('role', 'customer')
    //         ->where('isDeleted', 0)
    //         ->with('details:country,user_id,city');

    //     // 🔹 Apply condition based on role
    //     if ($user->role === 'staff') {
    //         // Staff sees only their created customers
    //         $query->where('created_by', $user->id);
    //     } else {
    //         // Admin/Sub-admin sees all customers for branch
    //         $query->where('branch_id', $userBranchId);
    //     }

    //     $customers = $query->orderBy('id', 'desc')
    //         ->get()
    //         ->map(function ($customer) {
    //             return [
    //                 'id'                => $customer->id,
    //                 'name'              => $customer->name,
    //                 'email'             => $customer->email,
    //                 'phone'             => $customer->phone,
    //                 'gst_number'        => $customer->gst_number,
    //                 'pan_number'        => $customer->pan_number,
    //                 'profile_image'     => $customer->profile_image,
    //                 'profile_image_url' => $customer->profile_image_url,
    //                 'country'           => $customer->details ? $customer->details->country : '',
    //                 'city'              => $customer->details ? $customer->details->city : '',
    //             ];
    //         });

    //     return response()->json(['status' => true, 'data' => $customers]);
    // }
    public function getAllTables(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $tables = Table_no::select('id', 'table_name', 'table_type', 'table_status', 'status', 'created_at', 'updated_at')
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($table) {
                return [
                    'id'         => $table->id,
                    'table_name' => $table->table_name,
                    'table_type' => $table->table_type,
                    'status'     => $table->resolved_status,
                    'created_at' => $table->created_at,
                    'updated_at' => $table->updated_at,
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $tables
        ]);
    }


    public function getCustomerById($id)
    {
        $customer = User::find($id); // Assuming you have a User model
        if ($customer) {
            return response()->json(['status' => true, 'customer' => $customer], 200);
        } else {
            return response()->json(['status' => false, 'error' => 'Customer not found'], 404);
        }
    }
    // public function createCustomer(Request $request)
    // {
    //     $authUser = Auth::guard('api')->user();
    //     $userId   = $authUser->id;

    //     // 🔹 Decide branch ID properly
    //     if ($authUser->role == 'staff' && $authUser->branch_id) {
    //         $userBranchId = $authUser->branch_id; // staff uses their branch_id
    //     } elseif ($authUser->role == 'sub-admin') {
    //         $userBranchId = $authUser->id; // sub-admin uses own id
    //     } elseif ($authUser->role == 'admin' && ! empty($request->selectedSubAdminId)) {
    //         $userBranchId = (int) $request->selectedSubAdminId; // admin chooses sub-admin
    //     } else {
    //         $userBranchId = $authUser->id; // fallback to logged in user's id
    //     }

    //     // $validator = Validator::make($request->all(), [
    //     //     'customer_name' => 'required|string|max:255',
    //     //     'email'         => 'required|string',
    //     //     'phone'         => 'nullable|numeric|unique:users,phone',
    //     //     'gst_number'    => 'nullable|string|max:15',
    //     //     'pan_number'    => 'nullable|string|max:15',
    //     //     'country'       => 'nullable|string|max:100',
    //     //     'city'          => 'nullable|string|max:100',
    //     //     'address'       => 'nullable|string|max:500',
    //     //     'avatar'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
    //     // ]);

    //     // if ($validator->fails()) {
    //     //     return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    //     // }

    //     // ✅ Create user
    //     $customer             = new Table_no();
    //     $customer->table_name       = $request->customer_name;
    //     $customer->table_type      = $request->email;
    //     // $customer->phone      = $request->phone;
    //     // $customer->gst_number = $request->gst_number;
    //     // $customer->pan_number = $request->pan_number;

    //     // if ($request->hasFile('avatar')) {
    //     //     $path                    = $request->file('avatar')->store('customer', 'public');
    //     //     $customer->profile_image = $path;
    //     // }

    //     // $customer->role       = 'customer';
    //     // $customer->branch_id  = $userBranchId ?? $userId; // ✅ never null
    //     // $customer->created_by = $authUser->id;
    //     // $customer->status     = 1;
    //     $customer->save();

    //     // ✅ Save details
    //     // $customerDetail          = new UserDetail();
    //     // $customerDetail->user_id = $customer->id;
    //     // $customerDetail->country = $request->country;
    //     // $customerDetail->city    = $request->city;
    //     // $customerDetail->address = $request->address;
    //     // $customerDetail->save();

    //     return response()->json(['status' => true, 'message' => 'Table created successfully!']);
    // }

    public function createCustomer(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $validator = Validator::make(
            $request->all(),
                [
                    'table_name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('table_no', 'table_name')
                        ->where(function ($query) use ($branchId) {
                            return $query->where('branch_id', $branchId)
                                ->where('isDeleted', 0);
                        }),
                ],
                'table_type' => 'required|string|max:50',
            ],
            [
                'table_name.unique' => 'This table name already exists in this branch.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $table = new Table_no();
        $table->table_name  = $request->table_name;
        $table->table_type  = $request->table_type;
        $table->table_status = 0; // Default status when creating table
        $table->status = Table_no::STATUS_BLANK;
        $table->order_id = null;            // No order assigned initially
        $table->branch_id = $branchId;
        $table->isDeleted = 0;
        $table->save();

        return response()->json([
            'status' => true,
            'message' => 'Table created successfully!',
            'data' => $table
        ], 200);
    }

    public function deleteTable($id)
    {
        $table = Table_no::withoutGlobalScope('active')->find($id);

        if (!$table) {
            return response()->json(['status' => false, 'message' => 'Table not found']);
        }

        if ((string) strtolower(trim((string) $table->status)) === Table_no::STATUS_RUNNING || (int) $table->table_status === 1) {
            return response()->json([
                'status' => false,
                'message' => 'This table cannot be deleted because it is currently running.',
            ]);
        }

        $table->isDeleted = 1;
        $table->save();

        return response()->json(['status' => true, 'message' => 'Table deleted successfully.']);
    }
    public function getById($id)
    {
        $table = Table_no::withoutGlobalScope('active')->find($id);

        if (! $table) {
            return response()->json([
                'status' => false,
                'message' => 'Table not found',
            ], 404);
        }

        if ((int) ($table->isDeleted ?? 0) === 1) {
            return response()->json([
                'status' => false,
                'message' => 'This table has been deleted.',
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $table
        ]);
    }

    public function update(Request $request, $id)
    {
        $table = Table_no::withoutGlobalScope('active')->find($id);

        if (!$table) {
            return response()->json(['status' => false, 'message' => 'Table not found']);
        }

        if ((int) ($table->isDeleted ?? 0) === 1) {
            return response()->json([
                'status' => false,
                'message' => 'This table has been deleted.',
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'table_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('table_no', 'table_name')
                        ->ignore($table->id)
                        ->where(function ($query) use ($table) {
                            return $query->where('branch_id', $table->branch_id)
                                ->where('isDeleted', 0);
                        }),
                ],
                'table_type' => 'required|string|max:50',
            ],
            [
                'table_name.unique' => 'This table name already exists in this branch.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = [
            'table_name' => $request->table_name,
            'table_type' => $request->table_type,
        ];

        if ($request->filled('status')) {
            $updateData['status'] = $request->status;
        }

        if ($request->has('table_status')) {
            $updateData['table_status'] = $request->table_status;
        }

        if ($request->has('order_id')) {
            $updateData['order_id'] = $request->order_id ?: null;
        }

        $table->update($updateData);

        return response()->json([
            'status' => true,
            'message' => 'Table updated successfully!'
        ]);
    }



    public function getCustomer($id)
    {
        // Find customer and include details
        $customer = User::with('details')->find($id);

        if (! $customer) {
            return response()->json(['status' => false, 'error' => 'Customer not found'], 404);
        }

        return response()->json([
            'status'   => true,
            'customer' => $customer,
        ]);
    }

    public function updateCustomer(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'email'         => "required|email|unique:users,email,$id",                        // Unique but ignores current user
            'phone'         => "required|numeric|digits_between:10,15|unique:users,phone,$id", // Unique & min 10, max 15 digits
            'gst_number'    => 'nullable|string|max:15',
            'pan_number'    => 'nullable|string|max:15',
            'country'       => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:500',
            'avatar'        => 'nullable|image|mimes:jpeg,png,webp,jpg,gif|max:2048', // Optional profile image
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Find the user
        $customer = User::find($id);
        if (! $customer) {
            return response()->json(['status' => false, 'error' => 'Customer not found'], 404);
        }

        // Update user details
        $customer->name       = $request->customer_name;
        $customer->email      = $request->email;
        $customer->phone      = $request->phone;
        $customer->gst_number = $request->gst_number;
        $customer->pan_number = $request->pan_number;

        // Handle profile image update
        if ($request->hasFile('avatar')) {
            $path                    = $request->file('avatar')->store('customer', 'public');
            $customer->profile_image = $path;
        }

        $customer->save();

        // Update related user details
        $customerDetail = UserDetail::where('user_id', $id)->first();
        if ($customerDetail) {
            $customerDetail->country = $request->country;
            $customerDetail->city    = $request->city;
            $customerDetail->address = $request->address;
            $customerDetail->save();
        }

        return response()->json(['status' => true, 'message' => 'Customer updated successfully!']);
    }
    public function getCustomerProfile($id)
    {
        $user = User::with('userDetail')->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'gst_number'    => $user->gst_number,
            'pan_number'    => $user->pan_number,
            'role'          => $user->role,
            'profile_image' => $user->profile_image,
            'address'       => $user->userDetail->address ?? 'N/A',
            'city'          => $user->userDetail->city ?? 'N/A',
            'country'       => $user->userDetail->country ?? 'N/A',
        ]);
    }
}
