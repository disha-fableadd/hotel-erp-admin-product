<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;

class PlanController extends Controller
{
    private function ensurePlanColumns()
    {
        try {
            if (!Schema::hasTable('plans')) {
                Schema::create('plans', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->decimal('price', 10, 2)->nullable();
                    $table->decimal('discount_percent', 5, 2)->nullable()->default(0.00);
                    $table->decimal('final_price', 10, 2)->nullable();
                    $table->decimal('total_amount', 10, 2)->nullable();
                    $table->decimal('total_price', 10, 2)->nullable();
                    $table->decimal('renewal_price', 10, 2)->nullable();
                    $table->string('duration')->nullable()->default('month');
                    $table->date('start_date')->nullable();
                    $table->date('end_date')->nullable();
                    $table->string('subtitle')->nullable();
                    $table->integer('user_limit')->nullable();
                    $table->integer('branch_limit')->nullable();
                    $table->integer('storage_limit')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->unsignedBigInteger('sub_branch_id')->nullable();
                    $table->json('features')->nullable();
                    $table->json('sub_modules')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('plan_features')) {
                Schema::create('plan_features', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('plan_id');
                    $table->string('feature');
                    $table->timestamps();
                });
            }

            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'plan_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unsignedBigInteger('plan_id')->nullable()->after('branch_id');
                });
            }

            if (!Schema::hasColumn('plans', 'discount_percent')) {
                Schema::table('plans', function (Blueprint $table) {
                    $table->decimal('discount_percent', 5, 2)->nullable()->default(0.00)->after('price');
                    $table->decimal('final_price', 10, 2)->nullable()->after('discount_percent');
                });
            }
            if (!Schema::hasColumn('plans', 'total_amount')) {
                Schema::table('plans', function (Blueprint $table) {
                    $table->decimal('total_amount', 10, 2)->nullable()->after('final_price');
                });
            }
            if (!Schema::hasColumn('plans', 'total_price')) {
                Schema::table('plans', function (Blueprint $table) {
                    $table->decimal('total_price', 10, 2)->nullable()->after('final_price');
                });
            }
            if (!Schema::hasColumn('plans', 'renewal_price')) {
                Schema::table('plans', function (Blueprint $table) {
                    $table->decimal('renewal_price', 10, 2)->nullable()->after('total_price');
                });
            }
            if (!Schema::hasColumn('plans', 'sub_branch_id')) {
                Schema::table('plans', function (Blueprint $table) {
                    $table->unsignedBigInteger('sub_branch_id')->nullable()->after('is_active');
                });
            }
            DB::statement("ALTER TABLE `plans` MODIFY COLUMN `duration` VARCHAR(255) NULL");
        } catch (\Throwable $e) {
            // Ignore if already modified
        }
    }

    /* ==========================================================
     | 1️⃣  LIST — GET /api/getAllPlans
     ========================================================== */
    public function getAllPlans(Request $request)
    {
        $this->ensurePlanColumns();

        $perPage        = (int) $request->input('per_page', 10);
        $page           = (int) $request->input('page', 1);
        $search         = trim((string) $request->input('search', ''));
        $shouldPaginate = $request->has('page') || $request->has('per_page') || $request->filled('search');

        $subBranchId = $request->input('sub_branch_id');

        $query = Plan::query()
            ->when($subBranchId, fn($q) => $q->where('sub_branch_id', $subBranchId))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('subtitle', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->filled('is_active'), fn($q) => $q->where('is_active', $request->is_active))
            ->when($request->filled('duration'),  fn($q) => $q->where('duration', $request->duration));

        $pagination = null;

        if ($shouldPaginate) {
            $paginated  = $query->latest('id')->paginate($perPage, ['*'], 'page', $page);
            $plans      = $paginated->items();
            $pagination = [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ];
        } else {
            $plans = $query->latest('id')->get();
        }

        return response()->json([
            'status'     => true,
            'data'       => $plans,
            'pagination' => $pagination,
        ]);
    }

    /* ==========================================================
     | 2️⃣  SHOW — GET /api/getPlanById/{id}
     ========================================================== */
    public function getPlanById($id)
    {
        $this->ensurePlanColumns();

        $plan = Plan::find($id);

        if (! $plan) {
            return response()->json(['status' => false, 'error' => 'Plan not found'], 404);
        }

        return response()->json(['status' => true, 'plan' => $plan], 200);
    }

    /* ==========================================================
     | 3️⃣  CREATE — POST /api/createPlan
     ========================================================== */
    public function createPlan(Request $request)
    {
        $this->ensurePlanColumns();

        $rules = [
            'name'             => 'required|string|max:255|unique:plans,name',
            'price'            => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'final_price'      => 'nullable|numeric|min:0',
            'total_amount'     => 'nullable|numeric|min:0',
            'total_price'      => 'nullable|numeric|min:0',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'duration'         => 'nullable|string',
            'subtitle'         => 'nullable|string',
            'user_limit'       => 'nullable|integer|min:0',
            'branch_limit'     => 'nullable',
            'storage_limit'    => 'nullable|integer|min:0',
            'is_active'        => 'required|boolean',
            'features'         => 'nullable|string',
            'sub_branch_id'    => 'nullable|integer',
            'renewal_price'    => 'nullable|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules, [], [
            'is_active'        => 'status',
            'user_limit'       => 'user limit',
            'branch_limit'     => 'branch limit',
            'storage_limit'    => 'storage limit',
            'discount_percent' => 'discount percentage',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        $years = $start->diffInYears($end);
        $months = $start->copy()->addYears($years)->diffInMonths($end);
        $days = $start->copy()->addYears($years)->addMonths($months)->diffInDays($end);

        $duration = '';

        if ($years > 0) {
            $duration .= $years . ' Year ';
        }

        if ($months > 0) {
            $duration .= $months . ' Month ';
        }

        if ($days > 0) {
            $duration .= $days . ' Day';
        }

        $duration = trim($duration);

        $price = isset($validated['price']) ? (float)$validated['price'] : null;
        $discountPercent = isset($validated['discount_percent']) && $validated['discount_percent'] !== '' ? (float)$validated['discount_percent'] : 0;
        if ($discountPercent < 0) { $discountPercent = 0; }
        if ($discountPercent > 100) { $discountPercent = 100; }
        $finalPrice = $price !== null ? round($price - ($price * ($discountPercent / 100)), 2) : null;
        $totalVal = $validated['total_amount'] ?? $validated['total_price'] ?? null;

        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $validated['features']))));
        }

        $plan = null;
        DB::transaction(function () use ($validated, &$plan, $duration, $featuresArray, $discountPercent, $finalPrice, $totalVal) {
            $plan = Plan::create([
                'name'             => $validated['name'],
                'price'            => $validated['price']         ?? null,
                'renewal_price'    => $validated['renewal_price'] ?? null,
                'discount_percent' => $discountPercent,
                'final_price'      => $finalPrice,
                'total_amount'     => $totalVal,
                'total_price'      => $totalVal,
                'duration'         => $duration,
                'start_date'       => $validated['start_date'],
                'end_date'         => $validated['end_date'],
                'subtitle'         => $validated['subtitle']      ?? null,
                'user_limit'       => $validated['user_limit']    ?? null,
                'branch_limit'     => $validated['branch_limit']  ?? null,
                'storage_limit'    => $validated['storage_limit'] ?? null,
                'is_active'        => $validated['is_active'],
                'sub_branch_id'    => $validated['sub_branch_id'] ?? null,
                'features'         => $featuresArray,
            ]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Plan created successfully',
            'plan'    => $plan,
        ], 200);
    }

    /* ==========================================================
     | 4️⃣  UPDATE — POST /api/updatePlan
     ========================================================== */
    public function updatePlan(Request $request)
    {
        $this->ensurePlanColumns();

        $rules = [
            'plan_id'          => 'required|exists:plans,id',
            'name'             => 'required|string|max:255|unique:plans,name,' . $request->plan_id,
            'price'            => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'final_price'      => 'nullable|numeric|min:0',
            'total_amount'     => 'nullable|numeric|min:0',
            'total_price'      => 'nullable|numeric|min:0',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'duration'         => 'nullable|string',
            'subtitle'         => 'nullable|string',
            'user_limit'       => 'nullable|integer|min:0',
            'branch_limit'     => 'nullable',
            'storage_limit'    => 'nullable|integer|min:0',
            'is_active'        => 'required|boolean',
            'features'         => 'nullable|string',
            'sub_branch_id'    => 'nullable|integer',
            'renewal_price'    => 'nullable|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules, [], [
            'plan_id'          => 'plan',
            'is_active'        => 'status',
            'user_limit'       => 'user limit',
            'branch_limit'     => 'branch limit',
            'storage_limit'    => 'storage limit',
            'discount_percent' => 'discount percentage',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        $years = $start->diffInYears($end);
        $months = $start->copy()->addYears($years)->diffInMonths($end);
        $days = $start->copy()->addYears($years)->addMonths($months)->diffInDays($end);

        $duration = '';

        if ($years > 0) {
            $duration .= $years . ' Year ';
        }

        if ($months > 0) {
            $duration .= $months . ' Month ';
        }

        if ($days > 0) {
            $duration .= $days . ' Day';
        }

        $duration = trim($duration);

        $price = isset($validated['price']) ? (float)$validated['price'] : null;
        $discountPercent = isset($validated['discount_percent']) && $validated['discount_percent'] !== '' ? (float)$validated['discount_percent'] : 0;
        if ($discountPercent < 0) { $discountPercent = 0; }
        if ($discountPercent > 100) { $discountPercent = 100; }
        $finalPrice = $price !== null ? round($price - ($price * ($discountPercent / 100)), 2) : null;
        $totalVal = $validated['total_amount'] ?? $validated['total_price'] ?? null;

        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $validated['features']))));
        }

        $plan = Plan::findOrFail($validated['plan_id']);

        DB::transaction(function () use ($plan, $validated, $duration, $featuresArray, $discountPercent, $finalPrice, $totalVal) {
            $plan->update([
                'name'             => $validated['name'],
                'price'            => $validated['price']         ?? null,
                'renewal_price'    => $validated['renewal_price'] ?? null,
                'discount_percent' => $discountPercent,
                'final_price'      => $finalPrice,
                'total_amount'     => $totalVal,
                'total_price'      => $totalVal,
                'duration'         => $duration,
                'start_date'       => $validated['start_date'],
                'end_date'         => $validated['end_date'],
                'subtitle'         => $validated['subtitle']      ?? null,
                'user_limit'       => $validated['user_limit']    ?? null,
                'branch_limit'     => $validated['branch_limit']  ?? null,
                'storage_limit'    => $validated['storage_limit'] ?? null,
                'is_active'        => $validated['is_active'],
                'sub_branch_id'    => $validated['sub_branch_id'] ?? null,
                'features'         => $featuresArray,
            ]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Plan updated successfully',
            'plan'    => $plan->fresh(),
        ], 200);
    }

    /* ==========================================================
     | 5️⃣  DELETE — POST /api/deletePlan/{id}
     ========================================================== */
    public function deletePlan($id)
    {
        $this->ensurePlanColumns();

        $plan = Plan::find($id);

        if (! $plan) {
            return response()->json(['status' => false, 'error' => 'Plan not found'], 404);
        }

        DB::transaction(function () use ($plan) {
            $plan->delete();
        });

        return response()->json([
            'status'  => true,
            'message' => 'Plan deleted successfully',
        ], 200);
    }

    /* ==========================================================
     | 6️⃣  TOGGLE STATUS — POST /api/togglePlanStatus/{id}
     ========================================================== */
    public function togglePlanStatus($id)
    {
        $this->ensurePlanColumns();

        $plan = Plan::find($id);

        if (! $plan) {
            return response()->json(['status' => false, 'error' => 'Plan not found'], 404);
        }

        $plan->is_active = ! $plan->is_active;
        $plan->save();

        return response()->json([
            'status'    => true,
            'message'   => 'Plan status updated successfully',
            'is_active' => $plan->is_active,
        ], 200);
    }
}
