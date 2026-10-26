<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function getProfile()
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized - No user found'], 401);
        }

        return response()->json(['status' => true, 'data' => $user], 200);
    }


    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15|unique:users,phone,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $oldEmail = $user->email;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $imagePath;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // If updating admin user profile, also synchronize changes to master admin_erp
        if ($user->role === 'admin' || (int) ($user->role_id ?? 0) === 1 || (int) $user->id === 1) {
            try {
                $tenantManager = app(\App\Services\Tenancy\TenantManager::class);
                if ($tenantManager->enabled()) {
                    $tenant = $tenantManager->current();
                    $tenantManager->syncProfileToAdmin([
                        'client_id' => $tenant['client_id'] ?? null,
                        'tenant_id' => $tenant['id'] ?? null,
                        'domain' => request()->getHost(),
                        'current_email' => $oldEmail,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'password' => $request->filled('password') ? $request->password : null,
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Profile update sync to admin_erp failed: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => true, 'message' => 'Profile updated successfully'], 200);
    }
    public function get_subadmin(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role ?? '';

        if (!$user || $role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized - Only admin allowed'], 403);
        }

        $subadmin = User::whereIn('role', ['admin', 'sub-admin'])
            ->where('isDeleted', 0)
            ->select('id', 'name', 'profile_image')
            ->get();

        if ($subadmin->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No subadmin found'], 404);
        }
        // dd($subadmin);
        return response()->json([
            'status' => true,
            'data' => $subadmin,
            'role' => $role
        ], 200);
    }

}