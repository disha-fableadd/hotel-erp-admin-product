<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('auth.signin');
        }

        $routeName = $request->route() ? $request->route()->getName() : null;
        $normalizedRouteName = $routeName ? strtolower($routeName) : null;

        // Enforce tenant module restrictions across ALL roles (admin, sub-admin, staff)
        // If the tenant/client does NOT have access to the module in admin_erp,
        // block access immediately so users cannot access unassigned modules via direct URL.
        $requiredTenantModule = \App\Services\ModuleAccessService::resolveRequiredModule($request, $normalizedRouteName);
        if ($requiredTenantModule !== null && ! tenant_has_module($requiredTenantModule)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to access this module.',
                ], 403);
            }

            return redirect()->route('auth.profile')
                ->with('error', 'You do not have permission to access this page or module.');
        }

        // 1. Admin always has full access to permitted tenant modules
        if ($user->role === 'admin' || $user->role_id === 1) {
            return $next($request);
        }

        // 2. Protect Sub-Branch/Admin modules exclusively
        $adminOnlyRoutes = [
            'subbranch.list',
            'subbranch.add',
            'subbranch.edit',
            'subbranch.view',
            'subbranch.delete'
        ];
        if (in_array($routeName, $adminOnlyRoutes) && $user->role !== 'admin') {
            abort(403, 'Unauthorized access to admin management.');
        }

        // 3. Branch users (sub-admin) have full access to all other modules
        if ($user->role === 'sub-admin') {
            return $next($request);
        }

        // 3. Define Route to Module ID Map
        $routeMap = [
            // Module 1: Products
            'product.list' => [1, 'view'],
            'product.view' => [1, 'view'],
            'product.detail' => [1, 'view'],
            'product.add' => [1, 'add'],
            'product.edit' => [1, 'edit'],
            'product.import' => [1, 'add'],
            
            // Module 8: Categories & Brands (Checks category/brand pages)
            'category.list' => [8, 'view'],
            'category.add' => [8, 'add'],
            'category.edit' => [8, 'edit'],
            'brand.list' => [8, 'view'],
            'brand.add' => [8, 'add'],
            'brand.edit' => [8, 'edit'],
            'quantity_type.quantity' => [8, 'view'],

            // Module 2: Orders
            'sales.list' => [2, 'view'],
            'sales.add' => [2, 'add'],
            'sales.edit' => [2, 'edit'],
            'sales.report' => [2, 'view'],
            'sales.details' => [2, 'view'],
            'sales.invoice' => [2, 'view'],
            'sales.invoice.pdf' => [2, 'view'],
            'tables.create' => [2, 'view'],

            // Module 3: Purchases
            'purchase.lists' => [3, 'view'],
            'purchase.add' => [3, 'add'],
            'purchase.edit' => [3, 'edit'],
            'purchase.invoice' => [3, 'view'],
            'purchase.print' => [3, 'view'],
            'purchase.orderreport' => [3, 'view'],
            'purchase.import' => [3, 'add'],
            'purchase.report' => [3, 'view'],
            'purchase.details' => [3, 'view'],
            'purchase.viewData' => [3, 'view'],
            'purchase.invoice.pdf' => [3, 'view'],

            // Module 4: Invoices
            'custom_invoice.lists' => [4, 'view'],
            'custom_invoice.add' => [4, 'add'],
            'custom_invoice.edit' => [4, 'edit'],
            'custom_invoice.print' => [4, 'view'],
            'custom_invoice.view' => [4, 'view'],
            'custom_invoice.pdf' => [4, 'view'],
            'custom-invoice.view' => [4, 'view'],
            'custom_invoice.payment_history' => [4, 'view'],

            // Module 5: Expenses
            'expense.list' => [5, 'view'],
            'expense.add' => [5, 'add'],
            'expense.edit' => [5, 'edit'],
            'expense.category' => [5, 'view'],
            'expense.report' => [5, 'view'],
            'expensetype.list' => [5, 'view'],
            'expensetype.add' => [5, 'add'],
            'expensetype.edit' => [5, 'edit'],

            // Module 9: Tables (Customers)
            'customer.list' => [9, 'view'],
            'customer.add' => [9, 'add'],
            'customer.edit' => [9, 'edit'],
            'customer.report' => [9, 'view'],

            // Module 10: Vendors
            'vendor.list' => [10, 'view'],
            'vendor.add' => [10, 'add'],
            'vendor.edit' => [10, 'edit'],
            'vendor.report' => [10, 'view'],
            'vendor.view' => [10, 'view'],

            // Module 11: Staff
            'staff.list' => [11, 'view'],
            'staff.add' => [11, 'add'],
            'staff.edit' => [11, 'edit'],
            'staff.report' => [11, 'view'],
            'staff.view' => [11, 'view'],
            'department.create' => [11, 'add'],
            'department.edit' => [11, 'edit'],
            'department.view' => [11, 'view'],
            'designation.create' => [11, 'add'],
            'designation.edit.open' => [11, 'edit'],
            'designation.view' => [11, 'view'],
            'holidays.calendar' => [11, 'view'],
            'holidays.index' => [11, 'view'],
            'holidays.create' => [11, 'add'],
            'holidays.edit' => [11, 'edit'],

            // Module 14: General Settings
            'setting.generalsettings' => [14, 'view'],
            'company-rules.view-rules' => [14, 'view'],
            'company-rules.create-rules' => [14, 'add'],
            'company-rules.show' => [14, 'view'],
            'company-rules.create' => [14, 'add'],

            // Module 15: Tax Rates
            'auth.taxrates' => [15, 'view'],

            // Module 16: Accounting
            'account_ledger.list' => [16, 'view'],
            'account_ledger.add' => [16, 'view'],
            'income-statement.index' => [16, 'view'],
            'income-statement.pdf' => [16, 'view'],
            'income-statement.excel' => [16, 'view'],
            'accounting.balance-sheet' => [16, 'view'],

            // Module 17: Inventory
            'inventory.list' => [17, 'view'],
            'inventory.View' => [17, 'view'],
            'appointments.index' => [17, 'view'],
            'appointments.create' => [17, 'add'],
            'appointments.edit' => [17, 'edit'],
            'appointments.view' => [17, 'view'],

            // Module 23: Advance Pay
            'advance_pay.index' => [23, 'view'],
            'advance_pay.create' => [23, 'add'],
            'advance_pay.show' => [23, 'view'],
            'advance_pay.edit' => [23, 'edit'],

            // Module 26: Attendance
            'attendance.list' => [26, 'view'],
            'attendance.create' => [26, 'add'],
            'salary.list' => [26, 'view'],
            'salary.view' => [26, 'view'],
            'attendence.summary' => [26, 'view'],
            'attendence.list' => [26, 'view'],
            'attendence.calendar' => [26, 'view'],
            'attendence.manage' => [26, 'view'],
            'attendence.view' => [26, 'view'],
            'attendence.view_calendar' => [26, 'view'],

            // Module 28: Leaves
            'leave.view' => [28, 'view'],
            'leave.request' => [28, 'view'],
            'leave.request.status' => [28, 'edit'],
            'leave.views' => [28, 'view'],
            'leave.add' => [28, 'add'],
            'leave-type.create' => [28, 'add'],
            'leave-type.edit.open' => [28, 'edit'],
            'leave-type.view' => [28, 'view'],

            // Module 29: Payroll
            'payroll.create' => [29, 'add'],
            'payroll.edit' => [29, 'edit'],
            'payroll.salary' => [29, 'view'],
            'payroll.salary-details' => [29, 'view'],
            'payroll.profile' => [29, 'view'],
            'payroll.details' => [29, 'view'],
            'payroll.download-slip' => [29, 'view'],
            'payroll.list' => [29, 'view'],
        ];

        // 4. Perform check if route name matches
        if ($routeName && isset($routeMap[$routeName])) {
            [$moduleId, $action] = $routeMap[$routeName];
            if (!app('hasPermission')($moduleId, $action)) {
                return redirect()->route('auth.dashboard')->with('error', 'Unauthorized access to this module.');
            }
        }

        return $next($request);
    }
}
