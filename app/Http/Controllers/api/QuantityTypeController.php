<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\QuantityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class QuantityTypeController extends Controller
{
    public function getAllQuantityTypes(Request $request)
    {
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role;

        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif (
            (!empty($request->sub_admin_id) || !empty($request->selectedSubAdminId))
            && $userRole == 'admin'
        ) {
            $branchIdToUse = $request->sub_admin_id
                ?? $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $userData->id;
        }

        $quantityTypes = QuantityType::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Quantity Type List',
            'data' => $quantityTypes
        ]);
    }
    public function addQuantityType(Request $request)
    {
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role;

        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif (
            (!empty($request->sub_admin_id) || !empty($request->selectedSubAdminId))
            && $userRole == 'admin'
        ) {
            $branchIdToUse = $request->sub_admin_id
                ?? $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $userData->id;
        }

        $validator = Validator::make($request->all(), [
            'quantity_type' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($branchIdToUse) {
                    $exists = QuantityType::where('quantity_type', $value)
                        ->where('isDeleted', 0)
                        ->where('branch_id', $branchIdToUse)
                        ->exists();

                    if ($exists) {
                        $fail('This quantity type already exists.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $quantityType = QuantityType::create([
            'quantity_type' => $request->quantity_type,
            'created_by' => $userData->id,
            'branch_id' => $branchIdToUse,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Quantity Type added successfully.',
            'data' => $quantityType
        ]);
    }
    public function updateQuantityType(Request $request, $id)
{
    $userData = Auth::guard('api')->user();
    $userRole = $userData->role;

    if ($userRole == 'staff') {
        $branchIdToUse = $userData->branch_id;
    } elseif (
        (!empty($request->sub_admin_id) || !empty($request->selectedSubAdminId))
        && $userRole == 'admin'
    ) {
        $branchIdToUse = $request->sub_admin_id
            ?? $request->selectedSubAdminId;
    } else {
        $branchIdToUse = $userData->id;
    }

    $quantityType = QuantityType::where('id', $id)
        ->where('branch_id', $branchIdToUse)
        ->first();

    if (!$quantityType) {
        return response()->json([
            'status' => false,
            'message' => 'Not Found'
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'quantity_type' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) use ($id, $branchIdToUse) {
                $exists = QuantityType::where('quantity_type', $value)
                    ->where('id', '!=', $id)
                    ->where('isDeleted', 0)
                    ->where('branch_id', $branchIdToUse)
                    ->exists();

                if ($exists) {
                    $fail('This quantity type already exists.');
                }
            }
        ]
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $quantityType->update([
        'quantity_type' => $request->quantity_type
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Quantity Type updated successfully.'
    ]);
}

    // Soft Delete
    public function deleteQuantityType($id)
    {
        $quantityType = QuantityType::find($id);

        if (!$quantityType) {
            return response()->json(['status' => false, 'message' => 'Not Found'], 404);
        }

        $quantityType->update(['isDeleted' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Quantity Type deleted successfully.'
        ]);
    }
}
