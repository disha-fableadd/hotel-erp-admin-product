<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Table_no;
use App\Models\TableReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────────────

    /**
     * Resolve the branch ID for the authenticated user / session-selected sub-admin.
     */
    private function resolveBranchId(Request $request): ?int
    {
        $user    = auth()->user();
        $role    = $user->role ?? '';
        $userId  = $user->id ?? null;

        // Token-less web session passes selectedSubAdminId as a POST param
        $subAdminId = $request->input('selectedSubAdminId')
            ?? session('selectedSubAdminId');

        if ($role === 'staff' && $user->branch_id) {
            return (int) $user->branch_id;
        }

        if ($role === 'sub-admin') {
            return (int) $userId;
        }

        if (!empty($subAdminId)) {
            return (int) $subAdminId;
        }

        return $userId ? (int) $userId : null;
    }

    // ── Store (create reservation) ────────────────────────────────────────────

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table_id'         => 'required|exists:table_no,id',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'nullable|string|max:20',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'guests'           => 'required|integer|min:1',
            'notes'            => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        $branchId = $this->resolveBranchId($request);

        // Create the reservation record
        $reservation = TableReservation::create([
            'table_id'         => $request->table_id,
            'branch_id'        => $branchId,
            'customer_name'    => $request->customer_name,
            'customer_phone'   => $request->customer_phone,
            'reservation_date' => $request->reservation_date,
            'reservation_time' => $request->reservation_time,
            'guests'           => $request->guests,
            'status'           => 'pending',
            'notes'            => $request->notes,
        ]);

        // Mark the table as reserved
        Table_no::where('id', $request->table_id)->update([
            'table_status' => 2,   // 2 = reserved
            'status'       => 'reserved',
            'order_id'     => null,
        ]);

        return response()->json([
            'status'      => true,
            'message'     => 'Reservation saved successfully!',
            'reservation' => $reservation,
        ], 201);
    }

    // ── Index (list reservations for a branch) ────────────────────────────────

    public function index(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $reservations = TableReservation::with('table')
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->date,   fn($q, $d) => $q->whereDate('reservation_date', $d))
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get();

        return response()->json(['status' => true, 'data' => $reservations]);
    }

    // ── Show (single reservation) ─────────────────────────────────────────────

    public function show($id)
    {
        $reservation = TableReservation::with('table')->find($id);

        if (!$reservation) {
            return response()->json(['status' => false, 'message' => 'Reservation not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $reservation]);
    }

    // ── Update status ─────────────────────────────────────────────────────────

    /**
     * Allowed transitions:
     *   pending  → confirmed | cancelled
     *   confirmed → completed | cancelled
     *   completed | cancelled → (no change)
     *
     * When status = 'completed', pass order_id to link the created order.
     */
    public function updateStatus(Request $request, $id)
    {
        $reservation = TableReservation::find($id);

        if (!$reservation) {
            return response()->json(['status' => false, 'message' => 'Reservation not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status'   => 'required|in:pending,confirmed,completed,cancelled',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $newStatus = $request->status;
        $reservation->status = $newStatus;

        if ($newStatus === 'completed' && $request->filled('order_id')) {
            $reservation->order_id = $request->order_id;
        }

        $reservation->save();

        // Sync table_no status
        $tableStatus = match ($newStatus) {
            'confirmed'  => ['table_status' => 2, 'status' => 'reserved'],
            'completed'  => ['table_status' => 1, 'status' => 'running'],
            'cancelled'  => ['table_status' => 0, 'status' => 'blank', 'order_id' => null],
            default      => [],
        };

        if (!empty($tableStatus)) {
            Table_no::where('id', $reservation->table_id)->update($tableStatus);
        }

        return response()->json([
            'status'      => true,
            'message'     => 'Reservation status updated!',
            'reservation' => $reservation->fresh(),
        ]);
    }

    // ── Cancel shortcut ───────────────────────────────────────────────────────

    public function cancel($id)
    {
        $reservation = TableReservation::find($id);

        if (!$reservation) {
            return response()->json(['status' => false, 'message' => 'Reservation not found'], 404);
        }

        if (in_array($reservation->status, ['completed', 'cancelled'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Cannot cancel a ' . $reservation->status . ' reservation.',
            ], 422);
        }

        $reservation->update(['status' => 'cancelled']);

        // Free the table
        Table_no::where('id', $reservation->table_id)->update([
            'table_status' => 0,
            'status'       => 'blank',
            'order_id'     => null,
        ]);

        return response()->json(['status' => true, 'message' => 'Reservation cancelled.']);
    }

    // ── Get active reservation for a table ───────────────────────────────────

    public function getByTable($tableId)
    {
        $reservation = TableReservation::with('table')
            ->where('table_id', $tableId)
            ->active()
            ->latest()
            ->first();

        if (!$reservation) {
            return response()->json(['status' => false, 'message' => 'No active reservation for this table'], 404);
        }

        return response()->json(['status' => true, 'data' => $reservation]);
    }
}
