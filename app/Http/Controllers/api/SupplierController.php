<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Purchases;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function getAllSupplier(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $userBranchId = $user->id ?? null;

        // if (!empty($request->selectedSubAdminId)) {
        //     $userBranchId = $request->selectedSubAdminId;
        // }
        if ($user->role === 'staff' && $user->branch_id) {
            $userBranchId = $user->branch_id;
        } elseif (! empty($request->selectedSubAdminId)) {
            $userBranchId = $request->selectedSubAdminId;
        } else {
            $userBranchId = $user->id;
        }

        // $suppliers = User::select('id', 'name', 'email', 'phone', 'profile_image', 'gst_number', 'pan_number')
        //     ->where('role', 'vendor')
        //     ->where('branch_id',$userBranchId)
        //     ->where('isDeleted', 0)
        //     ->with('details:country,user_id,city')
        //     ->orderBy('id', 'desc')
        $query = User::select('id', 'name', 'email', 'phone', 'profile_image', 'gst_number', 'pan_number')
            ->where('role', 'vendor')
            ->where('isDeleted', 0)
            ->with('details:country,user_id,city');

        // 🔹 Apply role-based condition
        if ($user->role === 'staff') {
            // Staff → see only vendors created by them
            $query->where('created_by', $user->id);
        } else {
            // Admin/Sub-admin → see vendors under their branch
            $query->where('branch_id', $userBranchId);
        }
        
        $suppliers = $query->orderBy('id', 'desc')
            ->get()
            ->map(function ($supplier) {
                return [
                    'id'                => $supplier->id,
                    'name'              => $supplier->name,
                    'email'             => $supplier->email,
                    'phone'             => $supplier->phone,
                    'profile_image'     => $supplier->profile_image,
                    'profile_image_url' => $supplier->profile_image_url,
                    'country'           => $supplier->details ? $supplier->details->country : '',
                    'city'              => $supplier->details ? $supplier->details->city : '',
                    'gst_number'        => $supplier->gst_number,
                    'pan_number'        => $supplier->pan_number,
                ];
            });

        return response()->json(['status' => true, 'data' => $suppliers]);
    }

    public function getSupplierById($id)
    {
        $customer = User::find($id); // Assuming you have a User model
        if ($customer) {
            return response()->json(['status' => true, 'customer' => $customer], 200);
        } else {
            return response()->json(['status' => false, 'error' => 'Customer not found'], 404);
        }
    }
    public function createSupplier(Request $request)
    {
        $authUser = Auth::guard('api')->user();
        $userId   = $authUser->id;
        // 🔹 Decide branch ID properly
        if ($authUser->role == 'staff' && $authUser->branch_id) {
            $userBranchId = $authUser->branch_id; // staff uses their branch_id
        } elseif ($authUser->role == 'sub-admin') {
            $userBranchId = $authUser->id; // sub-admin uses own id
        } elseif ($authUser->role == 'admin' && $request->filled('selectedSubAdminId') && is_numeric($request->selectedSubAdminId)) {
            $userBranchId = (int) $request->selectedSubAdminId; // admin chooses sub-admin
        } else {
            $userBranchId = $authUser->branch_id ?? $authUser->id; // fallback to admin's branch_id or own id
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'required|numeric|unique:users,phone',
            'country'    => 'nullable|string|max:100',
            'city'       => 'nullable|string|max:100',
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|max:15',
            'address'    => 'nullable|string|max:500',
            'avatar'     => 'nullable|image|mimes:jpeg,png,webp,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Create user
        $customer             = new User();
        $customer->name       = $request->name;
        $customer->email      = $request->email;
        $customer->phone      = $request->phone;
        $customer->gst_number = $request->gst_number;
        $customer->pan_number = $request->pan_number;
        if ($request->hasFile('avatar')) {
            $path                    = $request->file('avatar')->store('vendor', 'public');
            $customer->profile_image = $path;
        }
        $customer->role       = 'vendor';
        $customer->branch_id  = $userBranchId ?? $userId;
        $customer->created_by = $authUser->id;
        $customer->status     = 1;
        $customer->save(); // Save user first to get its ID

        // Now create user details using the saved user's ID
        $customerDetail          = new UserDetail();
        $customerDetail->user_id = $customer->id; // Use the newly created user's ID
        $customerDetail->country = $request->country;
        $customerDetail->city    = $request->city;
        $customerDetail->address = $request->address;
        $customerDetail->save();

        return response()->json(['status' => true, 'message' => 'Vendor created successfully!']);
    }

    public function deleteSupplier(Request $request, $id)
    {
        $supplier = User::find($id);

        if (! $supplier || $supplier->role !== 'vendor') {
            return response()->json(['status' => false, 'error' => 'Vendor not found'], 404);
        }

        // Check if vendor is associated with any purchase invoices
        $hasPurchases = Purchases::where('vendor_id', $id)->where('isDeleted', 0)->exists();

        if ($hasPurchases) {
            return response()->json([
                'status' => false,
                'error'  => 'This vendor cannot be deleted because they are associated with purchase.',
            ], 400);
        }

        try {
                                                                           // Delete related user details (soft delete or hard delete as per your logic)
            UserDetail::where('user_id', $id)->update(['isDeleted' => 1]); // Optional if UserDetail has isDeleted

            // Soft delete the supplier by setting isDeleted = 1
            $supplier->isDeleted = 1;
            $supplier->save();

            return response()->json([
                'status'  => true,
                'message' => 'Vendor deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error'  => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSupplier($id)
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

    public function updateSupplier(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'email'         => "required|email|unique:users,email,$id",                        // Unique but ignores current user
            'phone'         => "required|numeric|digits_between:10,15|unique:users,phone,$id", // Unique & min 10, max 15 digits
            'country'       => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'gst_number'    => 'nullable|string|max:15',
            'pan_number'    => 'nullable|string|max:15',
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
            $path                    = $request->file('avatar')->store('vendor', 'public');
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

    public function getVendorProfile($id)
    {
        $user = User::with('userDetail')->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'gst_number'    => $user->gst_number ?? '',
            'pan_number'    => $user->pan_number ?? '',
            'role'          => $user->role,
            'profile_image' => $user->profile_image,
            'address'       => $user->userDetail->address ?? '',
            'city'          => $user->userDetail->city ?? '',
            'country'       => $user->userDetail->country ?? '',
        ]);
    }
}
