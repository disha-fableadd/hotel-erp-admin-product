<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TableReservation;
use App\Models\User;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $user       = auth()->user();
        $role       = $user->role ?? '';
        $userId     = $user->id ?? null;
        $subAdminId = session('selectedSubAdminId');

        // Resolve branch
        if ($role === 'staff' && $user->branch_id) {
            $branchId = $user->branch_id;
        } elseif ($role === 'sub-admin') {
            $branchId = $userId;
        } elseif (!empty($subAdminId)) {
            $branchId = $subAdminId;
        } else {
            $branchId = $userId;
        }

        $reservations = TableReservation::with('table')
            ->where('branch_id', $branchId)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->date,   fn($q, $d) => $q->whereDate('reservation_date', $d))
            ->orderByDesc('reservation_date')
            ->orderByDesc('reservation_time')
            ->paginate(20)
            ->withQueryString();

        return view('reservations.index', compact('reservations'));
    }
}
