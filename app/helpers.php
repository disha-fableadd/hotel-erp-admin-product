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
            return false;
        }

        $assignedNormalized = array_map(function ($m) {
            return strtolower(trim((string) $m));
        }, $assigned);

        static $aliasGroups = [
            ['dashboard'],
            ['products', 'product', 'all products'],
            ['orders', 'order', 'sales', 'sales & bills', 'sales and bills', 'sales and orders'],
            ['purchases', 'purchase', 'purchase orders'],
            ['invoices', 'invoice', 'custom invoice', 'custom invoices', 'billing'],
            ['expenses', 'expense', 'manage expense', 'manage expenses'],
            ['categories & brands', 'category', 'categories', 'brand', 'brands', 'catalog setup', 'catalog'],
            ['tables', 'table', 'table reservation', 'table reservations'],
            ['vendors', 'vendor', 'suppliers', 'supplier'],
            ['staff', 'employees', 'employee', 'users'],
            ['general settings', 'settings', 'setting'],
            ['tax rates', 'tax rate', 'tax', 'taxes', 'gst'],
            ['accounting', 'manage accounting', 'accounts', 'account ledger'],
            ['inventory', 'manage inventory', 'stock'],
            ['advance pay', 'advance payment', 'advance payments'],
            ['attendance', 'manage attendance', 'biometric'],
            ['cash & bank', 'cash and bank', 'cash and bank book', 'cash book', 'bank book', 'banks'],
            ['leaves', 'leave', 'leave request'],
            ['payroll', 'salary', 'salary details'],
            ['credit/debit notes', 'credit and debit notes', 'credit notes', 'debit notes', 'credit debit notes'],
        ];

        $checkList = is_array($moduleNames) ? $moduleNames : [$moduleNames];
        $expandedCheckList = [];

        foreach ($checkList as $name) {
            $norm = strtolower(trim((string) $name));
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
            return false;
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


