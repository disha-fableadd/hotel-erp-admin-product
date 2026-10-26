<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DesignationController extends Controller
{
    /* -------------------------------------------------
     | 🔹 Helper: Resolve Branch ID  (same as BrandController)
     -------------------------------------------------*/
    private function resolveBranchId(Request $request): int|null
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return null;
        }

        $role = strtolower((string) ($user->role ?? ''));

        return match ($role) {
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            'admin'     => $request->input('selectedSubAdminId')
                           ?? $request->input('sub_admin_id')
                           ?? $user->id,
            default     => $user->id,
        };
    }

    /* -------------------------------------------------
     | Pages
     -------------------------------------------------*/
    public function createPage(Request $request)
    {
        // Resolve branch ID from session for web (non-API) context
        $user = auth()->user();
        $role = strtolower((string) ($user->role ?? ''));
        $branchId = match ($role) {
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            default     => session('selectedSubAdminId') ?? $user->id,
        };

        $departments = DepartmentModel::query()
            ->where('branch_id', $branchId)
            ->orderBy('department_name')
            ->get();

        $designationId = $request->integer('id') ?: $request->session()->pull('designation_edit_id');

        return view('designation.designation', compact('departments', 'designationId'));
    }

    public function openEditPage(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:designation,id'],
        ]);

        return redirect()
            ->route('designation.create')
            ->with('designation_edit_id', (int) $validated['id']);
    }

    public function indexPage()
    {
        return view('designation.view');
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

        $query = DesignationModel::query()
            ->leftJoin('department', 'department.id', '=', 'designation.department_id')
            ->select('designation.*', 'department.department_name')
            ->where('designation.branch_id', $branchId)
            ->when($search !== '', static function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('designation.designation_name', 'like', "%{$search}%")
                        ->orWhere('department.department_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('designation.created_at');

        $pagination = null;

        if ($shouldPaginate) {
            $paginatedDesignations = $query->paginate($perPage, ['*'], 'page', $page);
            $designations = $paginatedDesignations->items();
            $pagination   = [
                'current_page'  => $paginatedDesignations->currentPage(),
                'last_page'     => $paginatedDesignations->lastPage(),
                'per_page'      => $paginatedDesignations->perPage(),
                'total'         => $paginatedDesignations->total(),
                'next_page_url' => $paginatedDesignations->nextPageUrl(),
                'prev_page_url' => $paginatedDesignations->previousPageUrl(),
            ];
        } else {
            $designations = $query->get();
        }

        return response()->json([
            'status'       => 'success',
            'designations' => $designations,
            'pagination'   => $pagination,
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

        $designation = DesignationModel::find($id);
        if (!$designation) {
            return response()->json(['status' => 'error', 'message' => 'Designation not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $designation]);
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
            'designation_name' => ['required', 'string', 'min:2', 'max:255'],
            'department_id'    => ['required', 'integer', 'exists:department,id'],
        ]);

        $normalizedName = $this->normalizeName($validated['designation_name']);
        if ($this->designationExists($normalizedName, (int) $validated['department_id'], null, $branchId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Designation already exists for the selected department.',
            ], 409);
        }

        $designation = DesignationModel::create([
            'branch_id'        => $branchId,
            'designation_name' => $normalizedName,
            'department_id'    => (int) $validated['department_id'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Designation created successfully!',
            'data'    => $designation,
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

        $designation = DesignationModel::find($id);
        if (!$designation) {
            return response()->json(['status' => 'error', 'message' => 'Designation not found.'], 404);
        }

        $branchId = $designation->branch_id ?? $this->resolveBranchId($request);

        $validated = $request->validate([
            'designation_name' => ['required', 'string', 'min:2', 'max:255'],
            'department_id'    => ['required', 'integer', 'exists:department,id'],
        ]);

        $normalizedName = $this->normalizeName($validated['designation_name']);
        $departmentId   = (int) $validated['department_id'];

        if ($this->designationExists($normalizedName, $departmentId, $id, $branchId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Designation already exists for the selected department.',
            ], 409);
        }

        $designation->update([
            'designation_name' => $normalizedName,
            'department_id'    => $departmentId,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Designation updated successfully!',
            'data'    => $designation->fresh(),
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

        $designation = DesignationModel::find($id);
        if (!$designation) {
            return response()->json(['status' => 'error', 'message' => 'Designation not found.'], 404);
        }

        $hasDependency = false;
        if (Schema::hasTable('user_info')) {
            $hasDependency = $hasDependency || DB::table('user_info')->where('designation_id', $id)->exists();
        }
        if (Schema::hasTable('performance')) {
            $hasDependency = $hasDependency || DB::table('performance')->where('designation_id', $id)->exists();
        }

        if ($hasDependency) {
            return response()->json([
                'status'  => 'error',
                'message' => 'This designation is associated with employees or performance records and cannot be deleted.',
            ], 400);
        }

        $designation->delete();

        return response()->json(['status' => 'success', 'message' => 'Designation deleted successfully!']);
    }

    /* -------------------------------------------------
     | 🔹 All (dropdown / no pagination)
     -------------------------------------------------*/
    public function all(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $branchId     = $this->resolveBranchId($request);
        $designations = DesignationModel::query()
            ->where('branch_id', $branchId)
            ->orderBy('designation_name')
            ->get();

        return response()->json([
            'status'       => 'success',
            'data'         => $designations,
            'designations' => $designations,
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
                'designation_name' => ['required', 'string', 'min:2', 'max:255'],
                'department_id'    => ['required', 'integer', 'exists:department,id'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }

        $branchId       = $this->resolveBranchId($request);
        $normalizedName = $this->normalizeName($validated['designation_name']);
        $departmentId   = (int) $validated['department_id'];

        if ($this->designationExists($normalizedName, $departmentId, null, $branchId)) {
            return response()->json([
                'success' => false,
                'errors'  => ['designation_name' => ['This designation already exists for the selected department.']],
            ], 409);
        }

        $designation = DesignationModel::create([
            'branch_id'        => $branchId,
            'department_id'    => $departmentId,
            'designation_name' => $normalizedName,
        ]);

        return response()->json(['success' => true, 'designation' => $designation]);
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

    private function designationExists(string $name, int $departmentId, ?int $ignoreId = null, int|null $branchId = null): bool
    {
        return DesignationModel::query()
            ->where('department_id', $departmentId)
            ->when($ignoreId, static fn ($q) => $q->where('id', '!=', $ignoreId))
            ->when($branchId, static fn ($q) => $q->where('branch_id', $branchId))
            ->whereRaw('LOWER(designation_name) = ?', [Str::lower($name)])
            ->exists();
    }
}
