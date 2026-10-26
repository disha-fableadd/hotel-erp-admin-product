<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DepartmentModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    /* -------------------------------------------------
     | 🔹 Helper: Resolve Branch ID  (same as BrandController)
     -------------------------------------------------*/
    private function resolveBranchId(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = strtolower($user->role);

        return match ($role) {
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            'admin'     => $request->selectedSubAdminId ?: $user->id,
            default     => $user->id,
        };
    }

    /* -------------------------------------------------
     | Pages
     -------------------------------------------------*/
    public function createPage(?int $id = null)
    {
        return view('department.department', [
            'departmentId' => $id,
        ]);
    }

    public function indexPage()
    {
        return view('department.view');
    }

    /* -------------------------------------------------
     | 🔹 List (paginated / all)
     -------------------------------------------------*/
    public function index(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $branchId   = $this->resolveBranchId($request);
        $perPage    = max(1, (int) $request->input('per_page', 10));
        $page       = max(1, (int) $request->input('page', 1));
        $search     = trim((string) $request->input('search', ''));
        $shouldPaginate = $request->has('page') || $request->has('per_page') || $request->filled('search');

        $query = DepartmentModel::query()
            ->where('branch_id', $branchId)
            ->when($search !== '', static function ($q) use ($search) {
                $q->where('department_name', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at');

        $pagination = null;

        if ($shouldPaginate) {
            $paginatedDepartments = $query->paginate($perPage, ['*'], 'page', $page);
            $departments = $paginatedDepartments->items();
            $pagination  = [
                'current_page'  => $paginatedDepartments->currentPage(),
                'last_page'     => $paginatedDepartments->lastPage(),
                'per_page'      => $paginatedDepartments->perPage(),
                'total'         => $paginatedDepartments->total(),
                'next_page_url' => $paginatedDepartments->nextPageUrl(),
                'prev_page_url' => $paginatedDepartments->previousPageUrl(),
            ];
        } else {
            $departments = $query->get();
        }

        return response()->json([
            'status'     => 'success',
            'departments' => $departments,
            'pagination' => $pagination,
        ]);
    }

    /* -------------------------------------------------
     | 🔹 Show single
     -------------------------------------------------*/
    public function show(int $id): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $department = DepartmentModel::find($id);
        if (!$department) {
            return response()->json(['status' => 'error', 'message' => 'Department not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $department]);
    }

    /* -------------------------------------------------
     | 🔹 Create
     -------------------------------------------------*/
    public function store(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $branchId = $this->resolveBranchId($request);

        $validated = $request->validate([
            'department_name'              => ['required', 'string', 'min:2', 'max:255'],
            'enable_overtime'              => ['nullable', 'boolean'],
            'overtime_rate_type'           => ['nullable', 'in:multiplier,fixed'],
            'overtime_multiplier'          => ['nullable', 'numeric'],
            'min_overtime_count_in_minutes' => ['nullable', 'integer'],
        ]);

        $normalizedName = $this->normalizeName($validated['department_name']);
        if ($this->departmentNameExists($normalizedName, null, $branchId)) {
            return response()->json(['status' => 'error', 'message' => 'Department already exists.'], 409);
        }

        $department = DepartmentModel::create([
            'branch_id'                    => $branchId,
            'department_name'              => $normalizedName,
            'enable_overtime'              => $request->boolean('enable_overtime'),
            'overtime_rate_type'           => $request->input('overtime_rate_type', 'multiplier'),
            'overtime_multiplier'          => $request->input('overtime_multiplier', 1.00),
            'min_overtime_count_in_minutes' => $request->input('min_overtime_count_in_minutes', 0),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Department created successfully!',
            'data'    => $department,
        ], 201);
    }

    /* -------------------------------------------------
     | 🔹 Update
     -------------------------------------------------*/
    public function update(Request $request, int $id): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $department = DepartmentModel::find($id);
        if (!$department) {
            return response()->json(['status' => 'error', 'message' => 'Department not found.'], 404);
        }

        $branchId = $department->branch_id ?? $this->resolveBranchId($request);

        $validated = $request->validate([
            'department_name'              => ['required', 'string', 'min:2', 'max:255'],
            'enable_overtime'              => ['nullable', 'boolean'],
            'overtime_rate_type'           => ['nullable', 'in:multiplier,fixed'],
            'overtime_multiplier'          => ['nullable', 'numeric'],
            'min_overtime_count_in_minutes' => ['nullable', 'integer'],
        ]);

        $normalizedName = $this->normalizeName($validated['department_name']);
        if ($this->departmentNameExists($normalizedName, $id, $branchId)) {
            return response()->json(['status' => 'error', 'message' => 'Department already exists.'], 409);
        }

        $department->update([
            'department_name'              => $normalizedName,
            'enable_overtime'              => $request->boolean('enable_overtime'),
            'overtime_rate_type'           => $request->input('overtime_rate_type', 'multiplier'),
            'overtime_multiplier'          => $request->input('overtime_multiplier', 1.00),
            'min_overtime_count_in_minutes' => $request->input('min_overtime_count_in_minutes', 0),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Department updated successfully!',
            'data'    => $department->fresh(),
        ]);
    }

    /* -------------------------------------------------
     | 🔹 Delete
     -------------------------------------------------*/
    public function destroy(int $id): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $department = DepartmentModel::find($id);
        if (!$department) {
            return response()->json(['status' => 'error', 'message' => 'Department not found.'], 404);
        }

        $hasDependency = false;
        if (Schema::hasTable('designation')) {
            $hasDependency = $hasDependency || DB::table('designation')->where('department_id', $id)->exists();
        }
        if (Schema::hasTable('user_info')) {
            $hasDependency = $hasDependency || DB::table('user_info')->where('department_id', $id)->exists();
        }
        if (Schema::hasTable('jobs')) {
            $hasDependency = $hasDependency || DB::table('jobs')->where('department_id', $id)->exists();
        }

        if ($hasDependency) {
            return response()->json([
                'status'  => 'error',
                'message' => 'This department is associated with other records and cannot be deleted.',
            ], 400);
        }

        $department->delete();

        return response()->json(['status' => 'success', 'message' => 'Department deleted successfully!']);
    }

    /* -------------------------------------------------
     | 🔹 All (dropdown / no pagination)
     -------------------------------------------------*/
    public function all(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $branchId    = $this->resolveBranchId($request);
        $departments = DepartmentModel::query()
            ->where('branch_id', $branchId)
            ->orderBy('department_name')
            ->get();

        return response()->json([
            'status'      => 'success',
            'data'        => $departments,
            'departments' => $departments,
        ]);
    }

    /* -------------------------------------------------
     | 🔹 Quick Store (inline add from other forms)
     -------------------------------------------------*/
    public function quickStore(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        try {
            $validated = $request->validate([
                'department_name' => ['required', 'string', 'min:2', 'max:255'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
                'message' => 'Department Name is required.',
            ], 422);
        }

        $branchId       = $this->resolveBranchId($request);
        $normalizedName = $this->normalizeName($validated['department_name']);

        if ($this->departmentNameExists($normalizedName, null, $branchId)) {
            return response()->json([
                'success' => false,
                'message' => 'This department already exists.',
            ], 409);
        }

        $department = DepartmentModel::create([
            'branch_id'       => $branchId,
            'department_name' => $normalizedName,
        ]);

        return response()->json([
            'success'    => true,
            'department' => $department,
        ]);
    }

    /* -------------------------------------------------
     | 🔹 Private helpers
     -------------------------------------------------*/
    private function ensureManagementAccess(): ?JsonResponse
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Token missing or invalid.',
            ], 401);
        }

        $role = strtolower((string) ($user->role ?? ''));
        if (!in_array($role, ['admin', 'hr', 'sub-admin'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden: You do not have access to this resource.',
            ], 403);
        }

        return null;
    }

    private function normalizeName(string $name): string
    {
        return Str::title(Str::lower(trim($name)));
    }

    private function departmentNameExists(string $name, ?int $ignoreId = null, int|null $branchId = null): bool
    {
        return DepartmentModel::query()
            ->when($ignoreId, static fn ($q) => $q->where('id', '!=', $ignoreId))
            ->when($branchId, static fn ($q) => $q->where('branch_id', $branchId))
            ->whereRaw('LOWER(department_name) = ?', [Str::lower($name)])
            ->exists();
    }
}
