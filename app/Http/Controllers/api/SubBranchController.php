<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class SubBranchController extends Controller
{
    public function createSubbranch(Request $request)
    {
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function ($table) {
                $table->string('username', 100)->nullable()->unique()->after('name');
            });
        }

        $messages = [
            'email.unique'   => 'Already exist with this mail',
            'phone.unique'   => 'Already exist with this phone',
            'username.regex' => 'Username may only contain letters, numbers, and underscores.',
            'username.min'   => 'Username must be at least 3 characters.',
        ];

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'username'      => 'required|string|min:3|max:60|regex:/^[a-zA-Z0-9_]+$/',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'required|numeric|unique:users,phone',
            'country'       => 'nullable|string|max:100',
            'password'      => 'required|string|min:6',
            'city'          => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:500',
            'avatar'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Generate sub-branch username with admin prefix (same as staff)
        $authUser = auth()->user() ?? auth()->guard('api')->user();
        $adminPrefix = StaffController::getAdminPrefix($authUser);
        $rawUsername = trim($request->input('username', ''));
        $lowerUsername = strtolower($rawUsername);

        if (!empty($adminPrefix)) {
            $prefixWithUnder = $adminPrefix . '_';
            if (str_starts_with($lowerUsername, $prefixWithUnder)) {
                $finalUsername = $lowerUsername;
            } elseif (str_starts_with($lowerUsername, $adminPrefix)) {
                $remainder = substr($lowerUsername, strlen($adminPrefix));
                $finalUsername = $adminPrefix . '_' . ltrim($remainder, '_');
            } else {
                $finalUsername = $prefixWithUnder . ltrim($lowerUsername, '_');
            }
        } else {
            $finalUsername = $lowerUsername;
        }

        // Validate uniqueness of username across active users
        $duplicateUsername = User::where('isDeleted', 0)
            ->whereRaw('LOWER(TRIM(username)) = ?', [$finalUsername])
            ->exists();

        if ($duplicateUsername) {
            return response()->json([
                'status'  => false,
                'message' => 'The username has already been taken.',
                'errors'  => [
                    'username' => ['The username has already been taken.']
                ]
            ], 422);
        }

        // ✅ Create user
        $customer           = new User();
        $customer->name     = $request->customer_name;
        $customer->username = $finalUsername;
        $customer->email    = $request->email;
        $customer->phone    = $request->phone;
        $customer->password = Hash::make($request->password);

        if ($request->hasFile('avatar')) {
            $path                    = $request->file('avatar')->store('staff', 'public');
            $customer->profile_image = $path;
        }

        $customer->role   = 'sub-admin';
        $customer->status = 1;
        $customer->save();

        // ✅ Ensure a matching record exists in the branches table
        \Illuminate\Support\Facades\DB::table('branches')->insertOrIgnore([
            'id'         => $customer->id,
            'name'       => $customer->name,
            'code'       => 'BR' . str_pad($customer->id, 3, '0', STR_PAD_LEFT),
            'address'    => $request->address,
            'phone'      => $customer->phone,
            'email'      => $customer->email,
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customer->branch_id = $customer->id;
        $customer->save();

        // ✅ Create user details
        $customerDetail          = new UserDetail();
        $customerDetail->user_id = $customer->id;
        $customerDetail->country = $request->country;
        $customerDetail->city    = $request->city;
        $customerDetail->address = $request->address;
        $customerDetail->save();

        // ✅ Store dynamic permissions
        if ($request->has('modules') && is_array($request->modules)) {
            foreach ($request->modules as $moduleData) {
                UserPermission::create([
                    'user_id'   => $customer->id,
                    'module_id' => $moduleData['module_id'],
                    'view'      => isset($moduleData['view']),
                    'add'       => isset($moduleData['insert']),
                    'edit'      => isset($moduleData['edit']),
                    'delete'    => isset($moduleData['delete']),
                ]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Sub Branch created successfully',
        ]);
    }

    public function updateSubbranch(Request $request)
    {
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function ($table) {
                $table->string('username', 100)->nullable()->unique()->after('name');
            });
        }

        $messages = [
            'email.unique'   => 'Already exist with this mail',
            'phone.unique'   => 'Already exist with this phone',
            'username.regex' => 'Username may only contain letters, numbers, and underscores.',
            'username.min'   => 'Username must be at least 3 characters.',
        ];

        $validator = Validator::make($request->all(), [
            'id'            => 'required|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'username'      => 'nullable|string|min:3|max:60|regex:/^[a-zA-Z0-9_]+$/',
            'email'         => 'required|email|unique:users,email,' . $request->id,
            'phone'         => 'required|numeric|unique:users,phone,' . $request->id,
            'password'      => 'nullable|string|min:6',
            'country'       => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:500',
            'avatar'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $finalUsername = null;
        if ($request->filled('username')) {
            $authUser = auth()->user() ?? auth()->guard('api')->user();
            $adminPrefix = StaffController::getAdminPrefix($authUser);
            $rawUsername = trim($request->input('username', ''));
            $lowerUsername = strtolower($rawUsername);

            if (!empty($adminPrefix)) {
                $prefixWithUnder = $adminPrefix . '_';
                if (str_starts_with($lowerUsername, $prefixWithUnder)) {
                    $finalUsername = $lowerUsername;
                } elseif (str_starts_with($lowerUsername, $adminPrefix)) {
                    $remainder = substr($lowerUsername, strlen($adminPrefix));
                    $finalUsername = $adminPrefix . '_' . ltrim($remainder, '_');
                } else {
                    $finalUsername = $prefixWithUnder . ltrim($lowerUsername, '_');
                }
            } else {
                $finalUsername = $lowerUsername;
            }

            $duplicateUsername = User::where('id', '!=', $request->id)
                ->where('isDeleted', 0)
                ->whereRaw('LOWER(TRIM(username)) = ?', [$finalUsername])
                ->exists();

            if ($duplicateUsername) {
                return response()->json([
                    'status'  => false,
                    'message' => 'The username has already been taken.',
                    'errors'  => [
                        'username' => ['The username has already been taken.']
                    ]
                ], 422);
            }
        }

        // ✅ Fetch existing user
        $customer        = User::find($request->id);
        $customer->name  = $request->customer_name;
        if ($finalUsername !== null) {
            $customer->username = $finalUsername;
        }
        $customer->email = $request->email;
        $customer->phone = $request->phone;

        // ✅ Only update password if provided
        if (! empty($request->password)) {
            $customer->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            $path                    = $request->file('avatar')->store('staff', 'public');
            $customer->profile_image = $path;
        }

        // ✅ Do not change role if user is admin
        if ($customer->role !== 'admin') {
            $customer->role = 'sub-admin';
        }

        $customer->status = 1;
        $customer->save();

        // ✅ Ensure a matching record exists/is updated in the branches table
        \Illuminate\Support\Facades\DB::table('branches')->updateOrInsert(
            ['id' => $customer->id],
            [
                'name'       => $customer->name,
                'code'       => 'BR' . str_pad($customer->id, 3, '0', STR_PAD_LEFT),
                'address'    => $request->address,
                'phone'      => $customer->phone,
                'email'      => $customer->email,
                'status'     => 'active',
                'updated_at' => now(),
            ]
        );

        $customer->branch_id = $customer->id;
        $customer->save();

        // ✅ Update or create user details
        UserDetail::updateOrCreate(
            ['user_id' => $customer->id],
            [
                'country' => $request->country,
                'city'    => $request->city,
                'address' => $request->address,
            ]
        );

        // ✅ Remove old permissions if needed (optional)
        UserPermission::where('user_id', $customer->id)->delete();

        // ✅ Reinsert new permissions
        if ($request->has('modules') && is_array($request->modules)) {
            foreach ($request->modules as $moduleData) {
                UserPermission::create([
                    'user_id'   => $customer->id,
                    'module_id' => $moduleData['module_id'],
                    'view'      => isset($moduleData['view']),
                    'add'       => isset($moduleData['insert']),
                    'edit'      => isset($moduleData['edit']),
                    'delete'    => isset($moduleData['delete']),

                ]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Sub Branch updated successfully'], 200);
    }

    // public function deleteSubbranch($id)
    // {
    //     $subbranch = User::find($id);

    //     if (! $subbranch) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Sub Branch not found.',
    //         ], 404);
    //     }

    //     // Prevent deleting admin users
    //     if ($subbranch->role === 'admin') {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Admin accounts cannot be deleted.',
    //         ], 403);
    //     }

    //     // Soft delete user
    //     $subbranch->isDeleted = 1;
    //     $subbranch->save();

    //     // Soft delete related user details
    //     $userDetail = UserDetail::where('user_id', $id)->first();
    //     if ($userDetail) {
    //         $userDetail->isDeleted = 1;
    //         $userDetail->save();
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Sub Branch deleted successfully.',
    //     ], 200);
    // }
    public function deleteSubbranch($id)
    {
        $branch = User::find($id);

        if (! $branch) {
            return response()->json([
                'status'  => false,
                'message' => 'Branch not found.',
            ], 404);
        }

        // 🚫 Prevent deleting admin account
        if ($branch->role === 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'Admin accounts cannot be deleted.',
            ], 403);
        }

        // ✅ If sub-admin branch, dynamically check all tables
        if ($branch->role === 'sub-admin') {
            $tables       = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $keyName      = 'Tables_in_' . $databaseName;

            foreach ($tables as $table) {
                $tableName = $table->$keyName;

                // Skip system tables if any (optional)
                if (in_array($tableName, ['migrations', 'password_resets', 'personal_access_tokens'])) {
                    continue;
                }

                // Check if table has a branch_id column
                if (safe_has_column($tableName, 'branch_id')) {
                    $exists = DB::table($tableName)
                        ->where('branch_id', $id)
                        ->where(function ($query) use ($tableName) {
                            if (safe_has_column($tableName, 'isDeleted')) {
                                $query->where('isDeleted', 0);
                            }
                        })
                        ->exists();

                    if ($exists) {
                        return response()->json([
                            'status'  => false,
                            'message' => "This branch cannot be deleted. Records exist in the '{$tableName}' table.",
                        ], 403);
                    }
                }
            }
        }

        // ✅ Safe to delete
        $branch->isDeleted = 1;
        $branch->save();

        $userDetail = UserDetail::where('user_id', $id)->first();
        if ($userDetail) {
            $userDetail->isDeleted = 1;
            $userDetail->save();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Branch deleted successfully.',
        ], 200);
    }

    public function getSubbranch($id)
    {
        $subbranch = User::with('userDetail')
            ->where('isDeleted', 0)
            ->where('id', $id)
            ->first(); // ✅ changed from get() to first()

        return response()->json(['status' => true, 'data' => $subbranch], 200);
    }

    public function getAllSubbranch(Request $request)
    {
        $page    = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $query = User::with('userDetail')
            ->where('isDeleted', 0)
            ->whereIn('role', ['sub-admin', 'admin']);

        // ✅ SEARCH FILTER
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('role', 'LIKE', "%{$search}%")
                  ->orWhereHas('userDetail', function ($uq) use ($search) {
                        $uq->where('country', 'LIKE', "%{$search}%")
                           ->orWhere('city', 'LIKE', "%{$search}%");
                  });
            });
        }

        $total = $query->count();

        $subbranch = $query
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $subbranch,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => $total ? (($page - 1) * $perPage) + 1 : 0,
                'to' => (($page - 1) * $perPage) + $subbranch->count(),
            ]
        ]);
    }
}
