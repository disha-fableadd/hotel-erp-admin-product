<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdvancePayment;


class AdvancePaymentController extends Controller
{
    private function resolveBranchId(?\App\Models\User $user = null): ?int
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        $selectedSubAdminId = session('selectedSubAdminId');

        if ($user->role === 'staff' && ! empty($user->branch_id)) {
            return (int) $user->branch_id;
        }

        if (($user->role === 'admin' || $user->role === 'sub-admin') && ! empty($selectedSubAdminId)) {
            return (int) $selectedSubAdminId;
        }

        return ! empty($user->branch_id) ? (int) $user->branch_id : (int) $user->id;
    }

    public function index()
    {
        return view('advance_pay.index');
    }

    public function create()
    {
        $user = auth()->user();
        $staff = collect();

        if ($user) {
            $branchId = $this->resolveBranchId($user);

            if (! empty($branchId)) {
                $staff = User::where('role', 'staff')
                    ->where('isDeleted', 0)
                    ->where('branch_id', $branchId)
                    ->get();
            }
        }

        return view('advance_pay.create', compact('staff'));
    }
    public function show($id)
    {
        return view('advance_pay.show', compact('id'));
    }

    public function edit($id)
    {
        $payment = AdvancePayment::findOrFail($id);
        $branchId = $this->resolveBranchId();
        $staff = collect();

        if (! empty($branchId)) {
            $staff = User::where('role', 'staff')
                ->where('isDeleted', 0)
                ->where('branch_id', $branchId)
                ->get();
        }
        return view('advance_pay.edit', compact('payment', 'staff'));
    }
}
