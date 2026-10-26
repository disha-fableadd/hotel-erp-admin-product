<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProductsDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'creator'])
            ->where('isDeleted', 0)
            ->where('order_type', 'delivery');

        // Apply filters
        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->order_number . '%');
        }

        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('delivery_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Branch / Role filtering similar to SalesController
        $user = Auth::user();
        $subAdminId = session('selectedSubAdminId');

        if ($user->role === 'staff' && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
            $query->where('assigned_staff_id', $user->id);
        } elseif ($user->role === 'admin' && !empty($subAdminId)) {
            $query->where('branch_id', $subAdminId);
        } elseif ($user->role !== 'admin') {
            $query->where('branch_id', $user->id);
        }

        $orders = $query->orderBy('id', 'desc')->get();

        // Get staff members for the current user's branch
        $staffList = collect();

        if ($user->role === 'admin') {
            // Admin can see all staff
            $staffList = User::where('role', 'staff')
                ->where('isDeleted', 0)
                ->get();
        } elseif ($user->role === 'sub-admin') {
            // Sub-admin can see staff from their branch
            $staffList = User::where('branch_id', $user->id)
                ->where('role', 'staff')
                ->where('isDeleted', 0)
                ->get();
        }

        return view('sales.products-delivery', compact('orders', 'staffList'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,delivered,partially_delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->delivery_status = $request->status;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Delivery status updated successfully!']);
    }
}
