<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveModel;
use App\Models\LeaveTypeModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function indexPage()
    {
        return view('leave.leaveview');
    }

    public function legacyPage()
    {
        return view('leave.leaveviews');
    }

    public function leaveRequest()
    {
        $authUser = auth()->user();
        $role = $this->normalizeRole((string) ($authUser->role ?? 'staff'));

        $selectedSubAdminId = session('selectedSubAdminId');
        if ($selectedSubAdminId === 'null' || $selectedSubAdminId === '') {
            $selectedSubAdminId = null;
        }

        $branchId = $this->resolveBranchId($authUser, $selectedSubAdminId);

        $query = LeaveModel::with(['user', 'leaveType'])
            ->orderBy('start_date', 'desc');

        if (!$this->isManagementRole($role)) {
            $query->where('user_id', $authUser->id);
        } else {
            // Always scope to the resolved branch — covers admin, sub-admin, and HR
            if ($branchId > 0) {
                $query->where('branch_id', $branchId);
            }
        }

        $leaves = $query->get();

        return view('leave.leave-request', compact('leaves'));
    }

    public function createPage()
    {
        $authUser = auth()->user();
        $role = $this->normalizeRole((string) ($authUser->role ?? 'staff'));
        $nameColumn = $this->userDisplayColumn();

        // Branch filtering using web session selectedSubAdminId
        $selectedSubAdminId = session('selectedSubAdminId');
        if ($selectedSubAdminId === 'null' || $selectedSubAdminId === '') {
            $selectedSubAdminId = null;
        }
        $branchId = $this->resolveBranchId($authUser, $selectedSubAdminId);

        $leaveTypes = LeaveTypeModel::query()
            ->where('branch_id', $branchId)
            ->orderBy('leave_type')
            ->get(['id', 'leave_type', 'branch_id']);

        $usersQuery = User::query()->select(['id', 'role', 'branch_id']);

        if ($nameColumn === 'username') {
            $usersQuery->addSelect('username as display_name');
        } else {
            $usersQuery->addSelect('name as display_name');
        }

        $this->applyNotDeletedFilter($usersQuery);

        if ($this->isManagementRole($role)) {
            $usersQuery->whereRaw('LOWER(role) in (?, ?, ?)', ['hr', 'staff', 'employee']);

            // Branch scope for management roles.
            // Admin on main branch should still be limited to their own branch users.
            if ($role === 'hr') {
                $usersQuery->where('branch_id', $authUser->branch_id);
            } else {
                $usersQuery->where('branch_id', $branchId);
            }
        } elseif ($authUser) {
            $usersQuery->where('id', (int) $authUser->id);
        }

        $users = $usersQuery
            ->orderBy('display_name')
            ->get()
            ->map(static function ($user) {
                return [
                    'id' => (int) $user->id,
                    'username' => (string) ($user->display_name ?? ''),
                    'role' => $user->role,
                ];
            })
            ->values()
            ->all();

        return view('leave.addleave', [
            'leaveTypes' => $leaveTypes,
            'users' => $users,
            'role' => $role,
            'branchId' => $branchId,
            'currentUserId' => $authUser?->id,
        ]);
    }

    // Compatibility wrappers (old CI method names)
    public function view()
    {
        return $this->indexPage();
    }

    public function display()
    {
        return $this->createPage();
    }

    public function getAll(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function create(Request $request): JsonResponse
    {
        return $this->store($request);
    }

    public function index(Request $request): JsonResponse
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Token missing or invalid.',
            ], 401);
        }

        $role = $this->normalizeRole((string) $authUser->role);
        $requestedUserId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $nameColumn = $this->userDisplayColumn();

        // Resolve selectedSubAdminId from query params (API requests are stateless, no session)
        $selectedSubAdminId = $request->query('selectedSubAdminId')
            ?? $request->input('selectedSubAdminId')
            ?? session('selectedSubAdminId');
        if ($selectedSubAdminId === 'null' || $selectedSubAdminId === '') {
            $selectedSubAdminId = null;
        }

        $branchId = $this->resolveBranchId($authUser, $selectedSubAdminId);

        $usersQuery = User::query()->select(['id', 'profile_image', 'role', 'branch_id']);
        if ($nameColumn === 'username') {
            $usersQuery->addSelect('username as firstname');
        } else {
            $usersQuery->addSelect('name as firstname');
        }

        $this->applyNotDeletedFilter($usersQuery);

        if ($this->isManagementRole($role)) {
            $usersQuery->whereRaw('LOWER(role) in (?, ?, ?)', ['hr', 'staff', 'employee']);

            // Always scope to the resolved branch — covers admin, sub-admin, and HR
            if ($branchId > 0) {
                $usersQuery->where('branch_id', $branchId);
            }
        } else {
            $usersQuery->where('id', (int) $authUser->id);
        }

        if ($requestedUserId) {
            if (!$this->isManagementRole($role) && (int) $authUser->id !== $requestedUserId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden: You can only view your own leave records.',
                ], 403);
            }
            $usersQuery->where('id', $requestedUserId);
        }

        $users = $usersQuery->orderBy('firstname')->get();
        $userIds = $users->pluck('id')->map(static fn ($id) => (int) $id)->all();

        if (empty($userIds)) {
            return response()->json([
                'status' => 'success',
                'data' => [],
                'role' => $role,
            ]);
        }

        $leaveQuery = LeaveModel::query()
            ->from('leaves')
            ->leftJoin('leave_type', 'leave_type.id', '=', 'leaves.leave_id')
            ->leftJoin('users as creators', 'creators.id', '=', 'leaves.created_by')
            ->select([
                'leaves.id',
                'leaves.user_id',
                'leaves.leave_id',
                'leaves.start_date',
                'leaves.end_date',
                'leaves.no_of_day',
                'leaves.duration',
                'leaves.half_day_type',
                'leaves.reason',
                'leaves.status',
                'leave_type.leave_type',
                DB::raw('creators.' . $nameColumn . ' as created_by_username'),
            ])
            ->whereIn('leaves.user_id', $userIds)
            ->orderByDesc('leaves.start_date')
            ->orderByDesc('leaves.id');

        if ($this->isManagementRole($role)) {
            // Always scope to the resolved branch — covers admin, sub-admin, and HR
            if ($branchId > 0) {
                $leaveQuery->where('leaves.branch_id', $branchId);
            }
        } else {
            $leaveQuery->where('leaves.user_id', (int) $authUser->id);
        }

        $leaveRows = $leaveQuery->get();

        $groupedLeaveRows = [];
        foreach ($leaveRows as $leaveRow) {
            $userId = (int) $leaveRow->user_id;
            $groupedLeaveRows[$userId][] = [
                'id' => (int) $leaveRow->id,
                'user_id' => $userId,
                'leave_id' => (int) $leaveRow->leave_id,
                'start_date' => $leaveRow->start_date,
                'end_date' => $leaveRow->end_date,
                'no_of_day' => (float) ($leaveRow->no_of_day ?? 0),
                'duration' => $leaveRow->duration,
                'half_day_type' => $leaveRow->half_day_type,
                'reason' => $leaveRow->reason,
                'status' => $this->normalizeStatus((string) ($leaveRow->status ?? 'pending')),
                'leave_type' => $leaveRow->leave_type,
                'created_by_username' => $leaveRow->created_by_username,
            ];
        }

        $data = $users->map(static function ($user) use ($groupedLeaveRows) {
            $userId = (int) $user->id;
            return [
                'id'            => $userId,
                'user_id'       => $userId,
                'firstname'     => $user->firstname,
                'profile_image' => $user->profile_image,
                'profile_image_url' => $user->profile_image_url, // full resolved URL via accessor
                'role'          => $user->role,
                'leaves'        => $groupedLeaveRows[$userId] ?? [],
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'role' => $role,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Token missing or invalid.',
            ], 401);
        }

        $payload = $request->isJson() ? $request->json()->all() : $request->all();

        $validator = Validator::make($payload, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'leave_id' => ['required', 'integer', 'exists:leave_type,id'],
            'reason' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'duration' => ['nullable', 'string', 'in:Full Day,Half Day'],
            'half_day_type' => ['nullable', 'required_if:duration,Half Day', 'string', 'in:First Half,Second Half'],
            'status' => ['nullable', 'string', 'in:pending,approved,rejected,Pending,Approved,Rejected'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $validated = $validator->validated();
        $targetUserId = (int) $validated['user_id'];
        $nameColumn = $this->userDisplayColumn();
        $targetUser = User::query()
            ->select(['id', 'role', 'branch_id'])
            ->addSelect($nameColumn . ' as firstname')
            ->find($targetUserId);

        if (!$targetUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected user not found.',
            ], 404);
        }

        $role = $this->normalizeRole((string) $authUser->role);
        $targetRole = $this->normalizeRole((string) $targetUser->role);
        $targetBranchId = (int) ($targetUser->branch_id ?? $authUser->branch_id ?? $authUser->id ?? 0);

        $leaveType = LeaveTypeModel::query()
            ->select(['id', 'branch_id', 'leave_type'])
            ->find((int) $validated['leave_id']);

        if (!$leaveType) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected leave type not found.',
            ], 404);
        }

        if ((int) ($leaveType->branch_id ?? 0) !== $targetBranchId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected leave type does not belong to the employee branch.',
            ], 422);
        }

        if ($this->isManagementRole($role)) {
            if ($role === 'hr' && !in_array($targetRole, ['hr', 'staff', 'employee'], true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden: HR can only add leave for HR or staff users.',
                ], 403);
            }
        } elseif ((int) $authUser->id !== $targetUserId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You can only add leave for yourself.',
                ], 403);
        }

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $duration = $validated['duration'] ?? 'Full Day';
        $halfDayType = $validated['half_day_type'] ?? null;

        if ($duration === 'Half Day') {
            $endDate = $startDate->copy();
            $noOfDays = 0.5;
        } else {
            $endDate = Carbon::parse($validated['end_date'])->startOfDay();
            $noOfDays = $startDate->diffInDays($endDate) + 1;
        }
        $employeeFirstName = trim((string) ($targetUser->firstname ?? ''));

        $status = $this->normalizeStatus((string) ($validated['status'] ?? 'pending'));
        if (!$this->isManagementRole($role)) {
            $status = 'pending';
        }

        $leave = LeaveModel::create([
            'firstname' => $employeeFirstName,
            'user_id' => $targetUserId,
            'branch_id' => $targetBranchId,
            'leave_id' => (int) $validated['leave_id'],
            'reason' => trim((string) $validated['reason']),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'no_of_day' => $noOfDays,
            'duration' => $duration,
            'half_day_type' => $halfDayType,
            'status' => $status,
            'created_by' => (int) $authUser->id,
        ]);

        try {
            \App\Models\Notification::create([
                'user_id' => $targetUserId,
                'type' => 'leave',
                'title' => 'New Leave Request',
                'message' => $employeeFirstName . ' requested leave for ' . $noOfDays . ' day(s).',
                'link' => '/leave-request',
                'branch_id' => $targetBranchId,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Leave notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Leave record added successfully',
            'id' => (int) $leave->id,
        ], 201);
    }

    public function updateStatus(Request $request, int $leaveId): JsonResponse|RedirectResponse
    {
        $authUser = auth()->user() ?? auth('api')->user();
        if (!$authUser) {
            return $this->statusUpdateResponse($request, [
                'status' => 'error',
                'message' => 'Unauthorized: Token missing or invalid.',
            ], 401);
        }

        $role = $this->normalizeRole((string) $authUser->role);
        if (!$this->isManagementRole($role)) {
            return $this->statusUpdateResponse($request, [
                'status' => 'error',
                'message' => 'Forbidden: Employees cannot update leave status.',
            ], 403);
        }

        $validator = Validator::make($request->isJson() ? $request->json()->all() : $request->all(), [
            'status' => ['required', 'string', 'in:pending,approve,approved,rejected,Pending,Approve,Approved,Rejected'],
        ]);

        if ($validator->fails()) {
            return $this->statusUpdateResponse($request, [
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $leave = LeaveModel::find($leaveId);
        if (!$leave) {
            return $this->statusUpdateResponse($request, [
                'status' => 'error',
                'message' => 'Leave request not found.',
            ], 404);
        }

        if ($role === 'hr') {
            $targetUser = User::query()->select(['id', 'role'])->find((int) $leave->user_id);
            $targetRole = $this->normalizeRole((string) ($targetUser->role ?? ''));

            if (!in_array($targetRole, ['staff', 'employee'], true)) {
                return $this->statusUpdateResponse($request, [
                    'status' => 'error',
                    'message' => 'Forbidden: HR can only update employee leave requests.',
                ], 403);
            }
        }

        $status = $this->normalizeStatus((string) $validator->validated()['status']);

        $leave->update([
            'status' => $status,
            'created_by' => (int) $authUser->id,
        ]);

        return $this->statusUpdateResponse($request, [
            'status' => 'success',
            'message' => 'Leave status updated successfully',
        ]);
    }

    public function updateStatusWeb(Request $request, int $leaveId)
    {
        $authUser = auth()->user();
        if (!$authUser) {
            abort(401);
        }

        $role = $this->normalizeRole((string) $authUser->role);
        if (!$this->isManagementRole($role)) {
            abort(403, 'Employees cannot update leave status.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approve,approved,rejected,Pending,Approve,Approved,Rejected'],
        ]);

        $leave = LeaveModel::findOrFail($leaveId);

        if ($role === 'hr') {
            $targetUser = User::query()->select(['id', 'role'])->find((int) $leave->user_id);
            $targetRole = $this->normalizeRole((string) ($targetUser->role ?? ''));

            if (!in_array($targetRole, ['staff', 'employee'], true)) {
                abort(403, 'HR can only update employee leave requests.');
            }
        }

        $leave->update([
            'status' => $this->normalizeStatus((string) $validated['status']),
            'created_by' => (int) $authUser->id,
        ]);

        return redirect()
            ->route('leave.request')
            ->with('success', 'Leave status updated successfully.');
    }

    public function getEmployees(Request $request): JsonResponse
    {
        $response = $this->index($request);
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $payload = $response->getData(true);
        $employees = collect($payload['data'] ?? [])->map(static function (array $row) {
            return [
                'id' => $row['id'] ?? null,
                'firstname' => $row['firstname'] ?? null,
                'profile_image' => $row['profile_image'] ?? null,
                'role' => $row['role'] ?? null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $employees,
            'role' => $payload['role'] ?? null,
        ]);
    }

    private function normalizeRole(string $role): string
    {
        return strtolower(trim($role));
    }

    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        if ($normalized === 'approve') {
            $normalized = 'approved';
        }

        return in_array($normalized, ['approved', 'rejected', 'pending'], true)
            ? $normalized
            : 'pending';
    }

    private function isManagementRole(string $role): bool
    {
        return in_array($role, ['admin', 'hr', 'sub-admin', 'subadmin'], true);
    }

    private function resolveBranchId(?\App\Models\User $user, mixed $selectedSubAdminId = null): int
    {
        $role = $this->normalizeRole((string) ($user->role ?? ''));

        return match ($role) {
            'admin' => (int) (!empty($selectedSubAdminId) ? $selectedSubAdminId : ($user->branch_id ?? $user->id ?? 0)),
            'hr', 'staff', 'employee', 'sub-admin', 'subadmin' => (int) ($user->branch_id ?? $user->id ?? 0),
            default => (int) ($user->branch_id ?? $user->id ?? 0),
        };
    }

    private function userDisplayColumn(): string
    {
        if (safe_has_column('users', 'username')) {
            return 'username';
        }

        return 'name';
    }

    private function applyNotDeletedFilter(Builder $query): void
    {
        if (safe_has_column('users', 'isDeleted')) {
            $query->where('isDeleted', 0);
            return;
        }

        if (safe_has_column('users', 'is_deleted')) {
            $query->where('is_deleted', 0);
        }
    }

    private function statusUpdateResponse(Request $request, array $payload, int $statusCode = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json($payload, $statusCode);
        }

        $flashKey = ($payload['status'] ?? 'error') === 'success' ? 'success' : 'error';

        return redirect()
            ->route('leave.request')
            ->with($flashKey, $payload['message'] ?? 'Leave status update failed.');
    }
}
