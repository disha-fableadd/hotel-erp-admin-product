<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlanPermissionSyncService
{
    /**
     * Standard 20 modules for Hotel ERP.
     */
    public static function getStandardModules(): array
    {
        return [
            ['id' => 0,  'module' => 'Dashboard'],
            ['id' => 1,  'module' => 'Products'],
            ['id' => 2,  'module' => 'Orders'],
            ['id' => 3,  'module' => 'Purchases'],
            ['id' => 4,  'module' => 'Invoices'],
            ['id' => 5,  'module' => 'Expenses'],
            ['id' => 8,  'module' => 'Categories & Brands'],
            ['id' => 9,  'module' => 'Tables'],
            ['id' => 10, 'module' => 'Vendors'],
            ['id' => 11, 'module' => 'Staff'],
            ['id' => 14, 'module' => 'General Settings'],
            ['id' => 15, 'module' => 'Tax Rates'],
            ['id' => 16, 'module' => 'Accounting'],
            ['id' => 17, 'module' => 'Inventory'],
            ['id' => 23, 'module' => 'Advance Pay'],
            ['id' => 26, 'module' => 'Attendance'],
            ['id' => 27, 'module' => 'Cash & Bank'],
            ['id' => 28, 'module' => 'Leaves'],
            ['id' => 29, 'module' => 'Payroll'],
            ['id' => 30, 'module' => 'Credit/Debit Notes'],
        ];
    }

    /**
     * Mapping of Hotel ERP module IDs to their name aliases / keywords.
     */
    public static function getModuleAliasMap(): array
    {
        return [
            0  => ['dashboard'],
            1  => ['products', 'product', 'all products', 'new product', 'import products', 'products delivery'],
            2  => ['orders', 'order', 'all orders', 'new orders', 'sales', 'sales & bills', 'sales and orders', 'sales and bills', 'products delivery'],
            3  => ['purchases', 'purchase', 'all purchases', 'new purchase'],
            4  => ['invoices', 'invoice', 'custom invoice', 'custom invoices', 'all invoices'],
            5  => ['expenses', 'expense', 'all expenses', 'new expense', 'all expense type', 'expense types', 'manage expenses', 'manage expense'],
            8  => ['categories & brands', 'category', 'categories', 'all categories', 'new category', 'brand', 'brands', 'all brands', 'new brand', 'quantity type', 'catalog', 'catalog setup'],
            9  => ['tables', 'table', 'table booking', 'dining tables', 'table reservation', 'table reservations'],
            10 => ['vendors', 'vendor', 'all vendors', 'new vendor', 'import vendor', 'suppliers', 'supplier'],
            11 => ['staff', 'all staff', 'new staff', 'employees', 'employee', 'hr', 'human resources'],
            14 => ['general settings', 'settings', 'setting', 'shop settings', 'smtp settings', 'change password', 'my plan details'],
            15 => ['tax rates', 'tax rate', 'taxes', 'tax'],
            16 => ['accounting', 'manage accounting', 'account ledger', 'income statement', 'balance sheet', 'banks', 'bank', 'receipt and payment'],
            17 => ['inventory', 'manage inventory', 'product inventory', 'stock transfer', 'row material'],
            23 => ['advance pay', 'advance payment', 'advance payments'],
            26 => ['attendance', 'manage attendance', 'biometric', 'attendance logs'],
            27 => ['cash & bank', 'cash and bank', 'cash and bank book', 'transactions', 'transaction'],
            28 => ['leaves', 'leave', 'manage leaves'],
            29 => ['payroll', 'manage payroll', 'salary'],
            30 => ['credit/debit notes', 'credit and debit notes', 'credit debit notes', 'credit notes', 'debit notes'],
        ];
    }

    /**
     * Resolve allowed module IDs based on plan features and sub-modules.
     */
    public static function resolveAllowedModuleIds(mixed $features = null, mixed $subModules = null): array
    {
        $rawItems = [];

        // Parse features
        if (!empty($features)) {
            if (is_string($features)) {
                $decoded = json_decode($features, true);
                if (is_array($decoded)) {
                    $rawItems = array_merge($rawItems, $decoded);
                } else {
                    $rawItems = array_merge($rawItems, preg_split('/[\n,]+/', $features));
                }
            } elseif (is_array($features)) {
                $rawItems = array_merge($rawItems, $features);
            }
        }

        // Parse sub_modules
        if (!empty($subModules)) {
            if (is_string($subModules)) {
                $decoded = json_decode($subModules, true);
                if (is_array($decoded)) {
                    $rawItems = array_merge($rawItems, $decoded);
                } else {
                    $rawItems = array_merge($rawItems, preg_split('/[\n,]+/', $subModules));
                }
            } elseif (is_array($subModules)) {
                $rawItems = array_merge($rawItems, $subModules);
            }
        }

        $rawItems = array_values(array_filter(array_map(fn($i) => strtolower(trim((string) $i)), $rawItems)));

        // If no features or submodules are provided, default to all standard modules
        if (empty($rawItems)) {
            return array_column(self::getStandardModules(), 'id');
        }

        $aliasMap = self::getModuleAliasMap();
        $allowedIds = [0]; // Dashboard is always allowed

        foreach ($aliasMap as $modId => $aliases) {
            foreach ($aliases as $alias) {
                if (in_array($alias, $rawItems, true)) {
                    $allowedIds[] = $modId;
                    break;
                }
            }
        }

        return array_values(array_unique($allowedIds));
    }

    /**
     * Synchronize modules and user_permissions according to the active/specified plan.
     */
    public static function syncPlanModulesAndPermissions(?Plan $plan = null): array
    {
        if (!Schema::hasTable('modules') || !Schema::hasTable('user_permissions')) {
            return ['allowed_module_ids' => [], 'synced_modules_count' => 0];
        }

        if (!$plan) {
            $plan = Plan::where('is_active', 1)->latest('id')->first()
                ?: Plan::latest('id')->first();
        }

        $features = $plan ? $plan->features : null;
        $subModules = $plan ? $plan->sub_modules : null;

        $allowedModuleIds = self::resolveAllowedModuleIds($features, $subModules);
        $standardModules = self::getStandardModules();
        $allowedModules = array_filter(
            $standardModules,
            fn($m) => in_array($m['id'], $allowedModuleIds, true)
        );

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } catch (\Throwable) {}

        try {
            DB::statement("SET sql_mode='NO_AUTO_VALUE_ON_ZERO';");
        } catch (\Throwable) {}

        // 1. Sync modules table
        foreach ($allowedModules as $mod) {
            DB::table('modules')->updateOrInsert(
                ['id' => $mod['id']],
                ['module' => $mod['module'], 'updated_at' => now()]
            );
        }

        if (!empty($allowedModuleIds)) {
            DB::table('modules')->whereNotIn('id', $allowedModuleIds)->delete();
        }

        // 2. Clean up user_permissions table for unallowed modules
        if (!empty($allowedModuleIds)) {
            DB::table('user_permissions')->whereNotIn('module_id', $allowedModuleIds)->delete();
        }

        // 3. Upsert user_permissions for admin, sub-admin, staff users for allowed modules
        if (Schema::hasTable('users')) {
            $users = User::whereIn('role', ['admin', 'sub-admin', 'staff', 'hr'])
                ->orWhere('id', 1)
                ->get();

            foreach ($users as $user) {
                if ($user->role === 'sub-admin' || $user->role === 'staff' || $user->role === 'hr') {
                    $user->haspermission = 1;
                    $user->save();
                }

                foreach ($allowedModules as $mod) {
                    DB::table('user_permissions')->updateOrInsert(
                        [
                            'user_id'   => $user->id,
                            'module_id' => $mod['id'],
                        ],
                        [
                            'view'       => true,
                            'add'        => true,
                            'edit'       => true,
                            'delete'     => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Throwable) {}

        return [
            'allowed_module_ids'   => $allowedModuleIds,
            'synced_modules_count' => count($allowedModules),
        ];
    }
}
