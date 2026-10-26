<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        
        if (!Auth::guard('api')->check()) {
            return response()->json(['status' => false, 'error' => 'Unauthorized'], 401);
        }
        $user = Auth::guard('api')->user();

        // ✅ Safe defensive branch check for sub-admins
        $targetSubAdminIds = [];
        if ($user && $user->role === 'sub-admin') {
            $targetSubAdminIds[] = $user->id;
        }
        if ($request->has('sub_admin_id')) {
            $targetSubAdminIds[] = (int)$request->input('sub_admin_id');
        }
        if ($request->has('selectedSubAdminId')) {
            $targetSubAdminIds[] = (int)$request->input('selectedSubAdminId');
        }

        $targetSubAdminIds = array_unique(array_filter($targetSubAdminIds));
        if (!empty($targetSubAdminIds)) {
            foreach ($targetSubAdminIds as $subAdminId) {
                $subadmin = \App\Models\User::find($subAdminId);
                if ($subadmin && $subadmin->role === 'sub-admin') {
                    // Ensure matching branch exists
                    \Illuminate\Support\Facades\DB::table('branches')->insertOrIgnore([
                        'id'         => $subadmin->id,
                        'name'       => $subadmin->name,
                        'code'       => 'BR' . str_pad($subadmin->id, 3, '0', STR_PAD_LEFT),
                        'email'      => $subadmin->email,
                        'phone'      => $subadmin->phone,
                        'status'     => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (empty($subadmin->branch_id)) {
                        $subadmin->branch_id = $subadmin->id;
                        $subadmin->save();
                    }
                }
            }
        }

        return $next($request);
    }
}
