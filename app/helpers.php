<?php

if (! function_exists('image_path_base')) {
    /**
     * Base URL for public assets (always ends with /).
     */
    function image_path_base(): string
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        try {
            if (function_exists('app') && app()->bound('config')) {
                $configured = config('app.image_path');
                if (is_string($configured) && $configured !== '') {
                    return $cached = rtrim($configured, '/').'/';
                }

                $appUrl = config('app.url');
                if (is_string($appUrl) && $appUrl !== '') {
                    return $cached = rtrim($appUrl, '/').'/';
                }
            }
        } catch (\Throwable) {
            // Application not booted yet.
        }

        $raw = env('ImagePath') ?: env('IMAGE_PATH') ?: env('APP_URL', '');

        return $cached = ($raw !== '' ? rtrim((string) $raw, '/').'/' : '/');
    }
}

if (! function_exists('image_path')) {
    /**
     * Build a full URL to a file under public/ (e.g. admin/assets/... or storage/...).
     */
    function image_path(?string $path = ''): string
    {
        $base = image_path_base();

        if ($path === null || $path === '') {
            return $base;
        }

        return $base.ltrim($path, '/');
    }
}

if (! function_exists('safe_has_column')) {
    /**
     * Safely check if a table has a column, falling back to a SELECT LIMIT 0 query
     * if Schema::hasColumn() fails due to restricted database permissions.
     */
    function safe_has_column(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT `{$column}` FROM `{$table}` LIMIT 0");
                return true;
            } catch (\Throwable $fallbackException) {
                return false;
            }
        }
    }
}

if (! function_exists('safe_has_table')) {
    /**
     * Safely check if a table exists, falling back to a SELECT LIMIT 0 query
     * if Schema::hasTable() fails.
     */
    function safe_has_table(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable $e) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT 1 FROM `{$table}` LIMIT 0");
                return true;
            } catch (\Throwable $fallbackException) {
                return false;
            }
        }
    }
}

if (! function_exists('tenant')) {
    /**
     * Current client (tenant) details from admin_erp, or null when tenancy is off.
     *
     * @return mixed
     */
    function tenant(?string $key = null, $default = null)
    {
        $tenant = app(\App\Services\Tenancy\TenantManager::class)->current();

        return $key === null ? $tenant : data_get($tenant, $key, $default);
    }
}

if (! function_exists('tenant_has_module')) {
    /**
     * Check if current tenant has access to given module name(s).
     * If tenancy is disabled or not active, returns true.
     * Automatically handles common module aliases for Hotel ERP.
     *
     * @param string|array $moduleNames
     * @return bool
     */
    function tenant_has_module($moduleNames): bool
    {
        if (! config('tenant.enabled') || ! tenant()) {
            return true;
        }

        $assigned = (array) tenant('modules', []);

        if (empty($assigned)) {
            try {
                $dbPlanFeatures = null;
                if (auth()->check() && auth()->user()->plan && !empty(auth()->user()->plan->features)) {
                    $dbPlanFeatures = auth()->user()->plan->features;
                } else {
                    $activePlan = \Illuminate\Support\Facades\DB::table('plans')->where('is_active', 1)->latest('id')->first();
                    if ($activePlan && !empty($activePlan->features)) {
                        $dbPlanFeatures = $activePlan->features;
                    }
                }
                if (!empty($dbPlanFeatures)) {
                    $assigned = is_array($dbPlanFeatures) ? $dbPlanFeatures : (json_decode($dbPlanFeatures, true) ?: []);
                }
                if (empty($assigned) && \Illuminate\Support\Facades\Schema::hasTable('modules')) {
                    $assigned = \Illuminate\Support\Facades\DB::table('modules')->pluck('module')->all();
                }
            } catch (\Throwable $e) {}
        }

        if (empty($assigned)) {
            return false;
        }

        $assignedNormalized = array_map(function ($m) {
            return strtolower(trim((string) $m));
        }, $assigned);

        static $aliasGroups = [
            ['dashboard'],
            ['products', 'product', 'all products', 'new product', 'import products', 'products delivery'],
            ['orders', 'order', 'all orders', 'new orders', 'sales', 'sales & bills', 'sales and bills', 'sales and orders', 'products delivery'],
            ['purchases', 'purchase', 'all purchases', 'new purchase', 'purchase orders'],
            ['invoices', 'invoice', 'custom invoice', 'custom invoices', 'billing', 'all invoices'],
            ['expenses', 'expense', 'all expenses', 'new expense', 'all expense type', 'expense types', 'manage expense', 'manage expenses'],
            ['categories & brands', 'category', 'categories', 'brand', 'brands', 'all categories', 'new category', 'all brands', 'new brand', 'quantity type', 'catalog setup', 'catalog'],
            ['tables', 'table', 'all tables', 'tables views', 'table booking', 'dining tables', 'table reservation', 'table reservations'],
            ['vendors', 'vendor', 'all vendors', 'new vendor', 'import vendor', 'suppliers', 'supplier'],
            ['staff', 'all staff', 'new staff', 'employees', 'employee', 'users', 'hr', 'human resources'],
            ['general settings', 'settings', 'setting', 'shop settings', 'smtp settings', 'change password', 'my plan details', 'plans'],
            ['tax rates', 'tax rate', 'tax', 'taxes', 'gst'],
            ['accounting', 'manage accounting', 'accounts', 'account ledger', 'income statement', 'balance sheet', 'banks', 'bank', 'receipt and payment'],
            ['inventory', 'manage inventory', 'product inventory', 'stock transfer', 'row material', 'stock'],
            ['advance pay', 'advance payment', 'advance payments', 'all advance pay', 'new advance pay'],
            ['attendance', 'manage attendance', 'biometric', 'attendance logs'],
            ['cash & bank', 'cash and bank', 'cash and bank book', 'cash book', 'bank book', 'banks', 'transactions', 'transaction'],
            ['leaves', 'leave', 'all leaves', 'leave request', 'add leave', 'manage leaves'],
            ['payroll', 'salary', 'manage payroll', 'add payroll', 'group salary', 'salary details'],
            ['credit/debit notes', 'credit and debit notes', 'credit notes', 'debit notes', 'credit debit notes'],
        ];

        $checkList = is_array($moduleNames) ? $moduleNames : [$moduleNames];
        $expandedCheckList = [];

        foreach ($checkList as $name) {
            $norm = strtolower(trim((string) $name));
            if ($norm === 'dashboard') {
                return true;
            }
            $expandedCheckList[$norm] = true;
            foreach ($aliasGroups as $group) {
                if (in_array($norm, $group, true)) {
                    foreach ($group as $alias) {
                        $expandedCheckList[$alias] = true;
                    }
                }
            }
        }

        foreach (array_keys($expandedCheckList) as $name) {
            if (in_array($name, $assignedNormalized, true)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('tenant_has_sub_module')) {
    /**
     * Check if current tenant has access to given sub-module name(s) / slug(s).
     * If $parentModuleName is given, checks if tenant has the parent module first.
     * If tenancy is disabled or not active, returns true.
     *
     * @param string|array $subModuleNames
     * @param string|array|null $parentModuleName
     * @return bool
     */
    function tenant_has_sub_module($subModuleNames, $parentModuleName = null): bool
    {
        if (! config('tenant.enabled') || ! tenant()) {
            return true;
        }

        if ($parentModuleName !== null && ! tenant_has_module($parentModuleName)) {
            return false;
        }

        $rawNames = tenant('sub_module_names') ?: tenant('sub_modules') ?: [];
        try {
            $dbPlanSub = null;
            if (auth()->check() && auth()->user()->plan && !empty(auth()->user()->plan->sub_modules)) {
                $dbPlanSub = auth()->user()->plan->sub_modules;
            } else {
                $activePlan = \Illuminate\Support\Facades\DB::table('plans')->where('is_active', 1)->latest('id')->first();
                if ($activePlan && !empty($activePlan->sub_modules)) {
                    $dbPlanSub = $activePlan->sub_modules;
                }
            }
            if (!empty($dbPlanSub)) {
                $rawNames = is_array($dbPlanSub) ? $dbPlanSub : (json_decode($dbPlanSub, true) ?: []);
            }
        } catch (\Throwable $e) {}

        if (empty($rawNames)) {
            return true;
        }

        $assignedNormalized = array_map(function ($m) {
            return strtolower(trim((string) $m));
        }, (array) $rawNames);

        $checkList = is_array($subModuleNames) ? $subModuleNames : [$subModuleNames];

        foreach ($checkList as $name) {
            $norm = strtolower(trim((string) $name));
            if (in_array($norm, $assignedNormalized, true)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('plan_user_limit')) {
    /**
     * Get the maximum allowed users for the current tenant / active plan.
     */
    function plan_user_limit(): ?int
    {
        $limit = tenant('user_limit') ?? tenant('max_user');
        if ($limit !== null && is_numeric($limit)) {
            return (int) $limit;
        }

        try {
            if (auth()->check() && auth()->user()->plan && auth()->user()->plan->user_limit !== null) {
                return (int) auth()->user()->plan->user_limit;
            }

            $plan = \Illuminate\Support\Facades\DB::table('plans')->where('is_active', 1)->latest('id')->first()
                ?: \Illuminate\Support\Facades\DB::table('plans')->orderBy('id', 'desc')->first();
            if ($plan && $plan->user_limit !== null) {
                return (int) $plan->user_limit;
            }
        } catch (\Throwable) {}

        return null;
    }
}

if (! function_exists('plan_branch_limit')) {
    /**
     * Get the maximum allowed branches for the current tenant / active plan.
     */
    function plan_branch_limit(): ?int
    {
        $limit = tenant('branch_limit') ?? tenant('max_branch');
        if ($limit !== null && is_numeric($limit)) {
            return (int) $limit;
        }

        try {
            if (auth()->check() && auth()->user()->plan && auth()->user()->plan->branch_limit !== null) {
                return (int) auth()->user()->plan->branch_limit;
            }

            $plan = \Illuminate\Support\Facades\DB::table('plans')->where('is_active', 1)->latest('id')->first()
                ?: \Illuminate\Support\Facades\DB::table('plans')->orderBy('id', 'desc')->first();
            if ($plan && $plan->branch_limit !== null) {
                return (int) $plan->branch_limit;
            }
        } catch (\Throwable) {}

        return null;
    }
}

if (! function_exists('can_create_user')) {
    /**
     * Check if a new user (staff / sub-admin) can be created under the current plan.
     */
    function can_create_user(): bool
    {
        $limit = plan_user_limit();
        if ($limit === null || $limit <= 0) {
            return true;
        }

        $currentCount = \App\Models\User::where('isDeleted', 0)->count();
        return $currentCount < $limit;
    }
}

if (! function_exists('can_create_branch')) {
    /**
     * Check if a new branch / sub-branch can be created under the current plan.
     */
    function can_create_branch(): bool
    {
        $limit = plan_branch_limit();
        if ($limit === null || $limit <= 0) {
            return true;
        }

        $subAdminCount = \App\Models\User::where('role', 'sub-admin')->where('isDeleted', 0)->count();
        $totalBranches = 1 + $subAdminCount;

        return $totalBranches < $limit;
    }
}



