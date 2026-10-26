<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\HolidayCalendarModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class HolidaysController extends Controller
{
    public function calendarPage()
    {
        return view('holidays.show');
    }

    public function indexPage()
    {
        return view('holidays.holidays');
    }

    public function createPage()
    {
        return view('holidays.holidays_add');
    }

    public function editPage(int $id)
    {
        return view('holidays.edit_holiday', ['holidayId' => $id]);
    }

    public function index(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $branchId = $this->resolveBranchId($request);
        $perPage = max(1, (int) $request->input('per_page', 10));
        $page = max(1, (int) $request->input('page', 1));
        $search = trim((string) $request->input('search', ''));
        $shouldPaginate = $request->has('page') || $request->has('per_page') || $request->filled('search');

        $query = HolidayCalendarModel::query()
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $branchId);
            })
            ->when($search !== '', static function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('holiday_date', 'like', "%{$search}%");
                });
            })
            ->orderBy('holiday_date');

        $pagination = null;

        if ($shouldPaginate) {
            $paginatedHolidays = $query->paginate($perPage, ['*'], 'page', $page);
            $holidays = $paginatedHolidays->items();
            $pagination = [
                'current_page' => $paginatedHolidays->currentPage(),
                'last_page' => $paginatedHolidays->lastPage(),
                'per_page' => $paginatedHolidays->perPage(),
                'total' => $paginatedHolidays->total(),
                'next_page_url' => $paginatedHolidays->nextPageUrl(),
                'prev_page_url' => $paginatedHolidays->previousPageUrl(),
            ];
        } else {
            $holidays = $query->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $holidays,
            'pagination' => $pagination,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'holiday_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $branchId = $this->resolveBranchId($request);
        $date = date('Y-m-d', strtotime((string) $request->input('holiday_date')));

        $duplicate = HolidayCalendarModel::query()
            ->where('branch_id', $branchId)
            ->whereDate('holiday_date', $date)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => 'error',
                'message' => 'A holiday already exists on this date for this branch.',
            ], 409);
        }

        $holiday = HolidayCalendarModel::create([
            'branch_id' => $branchId,
            'title' => trim((string) $request->input('title')),
            'holiday_date' => $date,
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Holiday created successfully.',
            'data' => $holiday,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $branchId = $this->resolveBranchId(request());
        $holiday = HolidayCalendarModel::query()
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $branchId);
            })
            ->find($id);
        if (!$holiday) {
            return response()->json([
                'status' => 'error',
                'message' => 'Holiday not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $holiday,
            'holiday' => $holiday,
        ]);
    }

    public function update(Request $request, ?int $id = null): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $branchId = $this->resolveBranchId($request);
        $holidayId = $id ?? (int) $request->input('id');
        if (!$holidayId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Holiday ID is required.',
            ], 422);
        }

        $holiday = HolidayCalendarModel::query()
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $branchId);
            })
            ->find($holidayId);
        if (!$holiday) {
            return response()->json([
                'status' => 'error',
                'message' => 'Holiday not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'holiday_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = date('Y-m-d', strtotime((string) $request->input('holiday_date')));
        $duplicate = HolidayCalendarModel::query()
            ->where('branch_id', $branchId)
            ->whereDate('holiday_date', $date)
            ->where('id', '!=', $holidayId)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => 'error',
                'message' => 'A holiday already exists on this date.',
            ], 409);
        }

        $holiday->update([
            'title' => trim((string) $request->input('title')),
            'holiday_date' => $date,
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Holiday updated successfully.',
            'data' => $holiday->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if ($error = $this->ensureManagementAccess()) {
            return $error;
        }

        $branchId = $this->resolveBranchId(request());
        $holiday = HolidayCalendarModel::query()
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $branchId);
            })
            ->find($id);
        if (!$holiday) {
            return response()->json([
                'status' => 'error',
                'message' => 'Holiday not found.',
            ], 404);
        }

        $holiday->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Holiday deleted successfully.',
        ]);
    }

    public function editHoliday(int $id): JsonResponse
    {
        $response = $this->show($id);
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $payload = $response->getData(true);
        return response()->json([
            'status' => 'success',
            'holiday' => $payload['data'],
        ]);
    }

    private function resolveBranchId(Request $request): int
    {
        $user = Auth::guard('api')->user();
        if (! $user) {
            return 0;
        }

        $selectedSubAdminId = $request->input('selectedSubAdminId')
            ?? $request->query('selectedSubAdminId')
            ?? session('selectedSubAdminId');

        if (($user->role ?? null) === 'staff' && ! empty($user->branch_id)) {
            return (int) $user->branch_id;
        }

        if (! empty($selectedSubAdminId)) {
            return (int) $selectedSubAdminId;
        }

        return (int) $user->id;
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
}
