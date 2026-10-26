<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveTypeModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LeaveTypeController extends Controller
{
    /* -------------------------------------------------
     | 🔹 Helper: Resolve Branch ID  (same as BrandController)
     -------------------------------------------------*/
    private function resolveBranchId(Request $request)
    {
        $user = Auth::user() ?? Auth::guard('api')->user();
        $role = strtolower((string) ($user->role ?? ''));
        $selectedSubAdminId = $request->input('selectedSubAdminId') ?? session('selectedSubAdminId');

        return match ($role) {
            'sub-admin' => (int) ($user->branch_id ?? $user->id ?? 0),
            'staff'     => (int) ($user->branch_id ?? $user->id ?? 0),
            'admin'     => (int) (! empty($selectedSubAdminId) ? $selectedSubAdminId : ($user->branch_id ?? $user->id ?? 0)),
            default     => (int) ($user->branch_id ?? $user->id ?? 0),
        };
    }

    public function createPage(Request $request)
    {
        $leaveTypeId = $request->integer('id') ?: $request->session()->pull('leave_type_edit_id');

        return view('leave_type.leave_type', [
            'leaveTypeId' => $leaveTypeId,
        ]);
    }

    public function openEditPage(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:leave_type,id'],
        ]);

        return redirect()
            ->route('leave-type.create')
            ->with('leave_type_edit_id', (int) $validated['id']);
    }

    public function indexPage()
    {
        return view('leave_type.view');
    }

    public function index(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $perPage = max(1, (int) $request->input('per_page', 10));
        $page = max(1, (int) $request->input('page', 1));
        $search = trim((string) $request->input('search', ''));
        $shouldPaginate = $request->has('page') || $request->has('per_page') || $request->filled('search');
        $branchId = $this->resolveBranchId($request);

        $query = LeaveTypeModel::query()
            ->where('branch_id', $branchId)
            ->when($search !== '', static function ($q) use ($search) {
                $normalizedSearch = Str::lower($search);
                $q->where(function ($subQuery) use ($search, $normalizedSearch) {
                    $subQuery->where('leave_type', 'like', "%{$search}%")
                        ->orWhere('number_of_leaves', 'like', "%{$search}%");

                    if (in_array($normalizedSearch, ['yes', 'half day', 'half-day'], true)) {
                        $subQuery->orWhere('allow_half_day', 1);
                    }

                    if (in_array($normalizedSearch, ['no', 'full day', 'full-day'], true)) {
                        $subQuery->orWhere('allow_half_day', 0);
                    }
                });
            })
            ->orderByDesc('created_at');

        $pagination = null;

        if ($shouldPaginate) {
            $paginatedLeaveTypes = $query->paginate($perPage, ['*'], 'page', $page);
            $records = $paginatedLeaveTypes->items();
            $pagination = [
                'current_page' => $paginatedLeaveTypes->currentPage(),
                'last_page' => $paginatedLeaveTypes->lastPage(),
                'per_page' => $paginatedLeaveTypes->perPage(),
                'total' => $paginatedLeaveTypes->total(),
                'next_page_url' => $paginatedLeaveTypes->nextPageUrl(),
                'prev_page_url' => $paginatedLeaveTypes->previousPageUrl(),
            ];
        } else {
            $records = $query->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $records,
            'pagination' => $pagination,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $record = LeaveTypeModel::find($id);
        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave type not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $record,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'max:255'],
            'number_of_leaves' => ['required', 'integer', 'min:0'],
            'allow_half_day' => ['required', 'in:0,1'],
        ]);

        $leaveTypeName = $this->normalizeName($validated['leave_type']);
        if ($this->leaveTypeExists($leaveTypeName)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This leave type already exists.',
            ], 409);
        }

        $record = LeaveTypeModel::create([
            'leave_type' => $leaveTypeName,
            'number_of_leaves' => (int) $validated['number_of_leaves'],
            'branch_id' => $this->resolveBranchId($request),
            'allow_half_day' => (int) $validated['allow_half_day'],
            'created_by' => Auth::id() ?? auth('api')->id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave type record added successfully.',
            'data' => $record,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $record = LeaveTypeModel::find($id);
        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave type not found.',
            ], 404);
        }

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'max:255'],
            'number_of_leaves' => ['required', 'integer', 'min:0'],
            'allow_half_day' => ['required', 'in:0,1'],
        ]);

        $leaveTypeName = $this->normalizeName($validated['leave_type']);
        if ($this->leaveTypeExists($leaveTypeName, $id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This leave type already exists.',
            ], 409);
        }

        $record->update([
            'leave_type' => $leaveTypeName,
            'number_of_leaves' => (int) $validated['number_of_leaves'],
            'branch_id' => $record->branch_id ?: $this->resolveBranchId($request),
            'allow_half_day' => (int) $validated['allow_half_day'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave type updated successfully.',
            'data' => $record->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $record = LeaveTypeModel::find($id);
        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave type not found.',
            ], 404);
        }

        if (Schema::hasTable('leaves')) {
            $hasAssignedLeaves = DB::table('leaves')->where('leave_id', $id)->exists();
            if ($hasAssignedLeaves) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This leave type is assigned to users and cannot be deleted.',
                ], 400);
            }
        }

        $record->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Leave type deleted successfully!',
        ]);
    }

    public function quickStore(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        try {
            $validated = $request->validate([
                'leave_type' => ['required', 'string', 'max:255'],
                'number_of_leaves' => ['required', 'integer', 'min:0'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }

        $leaveTypeName = $this->normalizeName($validated['leave_type']);
        if ($this->leaveTypeExists($leaveTypeName)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'leave_type' => ['This leave type already exists.'],
                ],
            ], 409);
        }

        $record = LeaveTypeModel::create([
            'leave_type' => $leaveTypeName,
            'number_of_leaves' => (int) $validated['number_of_leaves'],
            'branch_id' => $this->resolveBranchId($request),
            'allow_half_day' => (int) $request->input('allow_half_day', 0),
            'created_by' => Auth::id() ?? auth('api')->id(),
        ]);

        return response()->json([
            'success' => true,
            'leave_type' => $record,
            'message' => 'Leave type added successfully.',
        ]);
    }

    private function ensureManagementAccess(): ?JsonResponse
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Token missing or invalid.',
            ], 401);
        }

        $role = strtolower((string) ($user->role ?? ''));
        if (!in_array($role, ['admin', 'hr', 'sub-admin'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You do not have access to this resource.',
            ], 403);
        }

        return null;
    }

    private function normalizeName(string $name): string
    {
        return Str::title(Str::lower(trim($name)));
    }

    private function leaveTypeExists(string $name, ?int $ignoreId = null): bool
    {
        $branchId = $this->resolveBranchId(request());

        return LeaveTypeModel::query()
            ->when($ignoreId, static fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('branch_id', $branchId)
            ->whereRaw('LOWER(leave_type) = ?', [Str::lower($name)])
            ->exists();
    }
}
