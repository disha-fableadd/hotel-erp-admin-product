<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserPermission;
use App\Services\ModuleAccessService;

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

        // Exempt routes that every authenticated user can always access without permissions
        $exemptRouteNames = [
            'auth.profile',
            'auth.change-password',
            'auth.forgetpassword',
            'auth.signin',
            'logout',
            'users.ajaxsearch',
            'tenant.storage',
            'tenant.storage.public',
            'notifications',
            'notifications.index',
            'plans.upgrade.options',
            'plans.razorpay.createorder',
            'plans.razorpay.verify',
            'plans.invoice.pdf',
        ];

        if ($normalizedRouteName && in_array($normalizedRouteName, $exemptRouteNames, true)) {
            return $next($request);
        }

        if ($request->is(
            'profile',
            'change-password',
            'forgetpassword',
            'logout',
            'storage/*',
            'public/storage/*',
            'search-users',
            'notifications*',
            'plans/upgrade-options',
            'plans/razorpay/*',
            'plans/invoice/pdf*'
        )) {
            return $next($request);
        }

        // 1. Enforce tenant module restrictions across ALL roles (admin, sub-admin, staff)
        // If the tenant/client does NOT have access to the module in admin_erp,
        // block access immediately and redirect directly to profile route.
        $requiredTenantModule = ModuleAccessService::resolveRequiredModule($request, $normalizedRouteName);
        if ($requiredTenantModule !== null && ! tenant_has_module($requiredTenantModule)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to access this module.',
                ], 403);
            }

            return redirect()->route('auth.profile')
                ->with('error', 'You do not have permission to access this module.')
                ->with('error_title', 'Access Denied');
        }

        // 1b. Enforce tenant sub-module restrictions
        $subModuleRouteMap = [
            'category.list' => [['All Categories', 'all-categories'], 'Categories & Brands'],
            'category.add' => [['New Category', 'new-category'], 'Categories & Brands'],
            'brand.list' => [['All Brands', 'all-brands'], 'Categories & Brands'],
            'brand.add' => [['New Brand', 'new-brand'], 'Categories & Brands'],
            'quantity_type.quantity' => [['Quantity Type', 'quantity-type'], 'Categories & Brands'],

            'account_ledger.list' => [['Account Ledger', 'account-ledger'], 'Accounting'],
            'account_ledger.add' => [['Account Ledger', 'account-ledger'], 'Accounting'],
            'income-statement.index' => [['Income Statement', 'income-statement'], 'Accounting'],
            'accounting.balance-sheet' => [['Balance Sheet', 'balance-sheet'], 'Accounting'],
            'banks.index' => [['Banks', 'banks'], 'Accounting'],

            'expense.list' => [['All Expenses', 'all-expenses'], 'Expenses'],
            'expense.add' => [['New Expense', 'new-expense'], 'Expenses'],
            'expensetype.list' => [['All Expense Type', 'all-expense-type', 'expense-types'], 'Expenses'],
            'expensetype.add' => [['All Expense Type', 'all-expense-type', 'expense-types'], 'Expenses'],

            'transaction.bankbook' => [['Bank Book', 'bank-book'], 'Cash & Bank'],
            'transaction.cashbook' => [['Cash Book', 'cash-book'], 'Cash & Bank'],

            'sales.report' => [['Sales Report', 'sales-report'], 'Reports'],
            'sales.pool_report' => [['Sales Pool Report', 'sales-pool-report'], 'Reports'],
            'tds.report' => [['TDS Report', 'tds-report'], 'Reports'],
            'purchase.report' => [['Purchase Report', 'purchase-report'], 'Reports'],
            'expense.report' => [['Expenses Report', 'expense-report', 'Expense Report'], 'Reports'],
            'profit-loss-statement.index' => [['Profit & Loss Statement', 'profit-loss-statement', 'Profit Loss Report'], 'Reports'],

            'attendance.manage' => [['Manage Attendance', 'manage-attendance', 'mark-attendance'], 'Attendance'],
            'attendance.manage.summary' => [['Manage Attendance', 'manage-attendance', 'mark-attendance'], 'Attendance'],
            'attendance.manage.history' => [['Manage Attendance', 'manage-attendance', 'mark-attendance'], 'Attendance'],
            'attendence.summary' => [['Attendance', 'attendance-summary', 'attendance-list'], 'Attendance'],
            'attendence.list' => [['Attendance', 'attendance-summary', 'attendance-list'], 'Attendance'],
            'attendence.calendar' => [['Attendance', 'attendance-summary', 'attendance-list'], 'Attendance'],

            'leave.view' => [['All Leaves', 'all-leaves'], 'Leaves'],
            'leave.views' => [['All Leaves', 'all-leaves'], 'Leaves'],
            'leave.request' => [['Leave Request', 'leave-request'], 'Leaves'],
            'leave.add' => [['Add Leave', 'add-leave'], 'Leaves'],

            'payroll.list' => [['View Payroll', 'view-payroll', 'payroll-list'], 'Payroll'],
            'payroll.salary' => [['Group Salary', 'group-salary', 'Manage Payroll'], 'Payroll'],
            'payroll.salary-details' => [['Manage Payroll', 'manage-payroll'], 'Payroll'],
            'payroll.create' => [['Add Payroll', 'add-payroll'], 'Payroll'],

            'advance_pay.index' => [['All Advance Pay', 'all-advance-pay'], 'Advance Pay'],
            'advance_pay.create' => [['New Advance Pay', 'new-advance-pay'], 'Advance Pay'],

            'plans.myplan' => [['My Plan Details', 'my-plan-details', 'Plan Details'], 'General Settings'],
            'plans.details' => [['My Plan Details', 'my-plan-details', 'Plan Details'], 'General Settings'],
        ];

        if ($normalizedRouteName && isset($subModuleRouteMap[$normalizedRouteName])) {
            [$subNames, $parentMod] = $subModuleRouteMap[$normalizedRouteName];
            if (!tenant_has_sub_module($subNames, $parentMod)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You do not have permission to access this sub-module.',
                    ], 403);
                }

                return redirect()->route('auth.profile')
                    ->with('error', 'You do not have permission to access this sub-module.')
                    ->with('error_title', 'Access Denied');
            }
        }

        // 2. Main Admin (role === 'admin' or role_id === 1) always has full access to permitted tenant modules
        if ($user->role === 'admin' || (int)$user->role_id === 1) {
            return $next($request);
        }

        // 3. Protect Sub-Branch/Admin and Plan creation modules exclusively for Main Admin
        $adminOnlyRoutes = [
            'subbranch.list',
            'subbranch.add',
            'subbranch.edit',
            'subbranch.view',
            'subbranch.delete',
            'plans.addplan',
            'plans.store',
            'plans.edit',
            'plans.update',
            'plans.destroy',
        ];
        if (
            ($normalizedRouteName && in_array($normalizedRouteName, $adminOnlyRoutes, true)) ||
            $request->is('sub-branch*', 'add-subbranch*', 'edit-subbranch*', 'delete-subbranch*', 'view-subbranch*', 'add-plan*', 'edit-plan*')
        ) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access to admin management.',
                ], 403);
            }
            return redirect()->route('auth.profile')
                ->with('error', 'Unauthorized access to admin management.')
                ->with('error_title', 'Access Denied');
        }

        // 4. Branch users (sub-admin): if they have no explicit permissions defined in user_permissions,
        // they have full access to all branch modules. If explicit permissions exist, check them.
        if ($user->role === 'sub-admin') {
            $hasExplicitPermissions = UserPermission::where('user_id', $user->id)->exists();
            if (!$hasExplicitPermissions) {
                return $next($request);
            }
        }

        // 5. If staff or hr member is "Without Permission" (haspermission == 0), deny all module routes
        if (in_array(strtolower($user->role), ['staff', 'hr'], true) && !$user->haspermission) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to access this module.',
                ], 403);
            }
            return redirect()->route('auth.profile')
                ->with('error', 'You do not have permission to access any modules. Please contact your administrator.')
                ->with('error_title', 'Access Denied');
        }

        // 6. Comprehensive Route-to-Module Mapping
        $routeMap = [
            // Module 0: Dashboard
            'auth.dashboard' => [0, 'view'],
            'auth.staff-dashboard' => [0, 'view'],
            'hr.dashboard' => [0, 'view'],

            // Module 1: Products
            'product.list' => [1, 'view'],
            'product.view' => [1, 'view'],
            'product.detail' => [1, 'view'],
            'product.add' => [1, 'add'],
            'product.edit' => [1, 'edit'],
            'product.import' => [1, 'add'],

            // Module 2: Orders / Sales
            'sales.list' => [2, 'view'],
            'sales.add' => [2, 'add'],
            'sales.edit' => [2, 'edit'],
            'sales.report' => [2, 'view'],
            'sales.pool_report' => [2, 'view'],
            'sales.details' => [2, 'view'],
            'sales.invoice' => [2, 'view'],
            'sales.invoice.pdf' => [2, 'view'],
            'sales.report.view_page' => [2, 'view'],
            'table.bill.pdf' => [2, 'view'],
            'tables.create' => [2, 'view'],
            'reservations.index' => [2, 'view'],
            'sales.products-delivery' => [2, 'view'],
            'sales.products-delivery.status' => [2, 'edit'],
            'sales.updateordertype' => [2, 'edit'],
            'sales.assignstaff' => [2, 'edit'],
            'sales.delivery' => [2, 'view'],
            'sales.submitdelivery' => [2, 'add'],
            'quotation.list' => [2, 'view'],
            'quotation.add' => [2, 'add'],
            'quotation.edit' => [2, 'edit'],
            'sale.report.exportpdf' => [2, 'view'],
            'salesreturn.add' => [2, 'add'],
            'salesreturn.adds' => [2, 'add'],
            'salesreturn.edit' => [2, 'edit'],
            'salesreturn.edits' => [2, 'edit'],
            'salesreturn.list' => [2, 'view'],
            'salesreturn.lists' => [2, 'view'],

            // Module 3: Purchases
            'purchase.lists' => [3, 'view'],
            'purchase.add' => [3, 'add'],
            'purchase.edit' => [3, 'edit'],
            'purchase.invoice' => [3, 'view'],
            'purchase.print' => [3, 'view'],
            'purchase.orderreport' => [3, 'view'],
            'purchase.import' => [3, 'add'],
            'purchase.report' => [3, 'view'],
            'purchase.report.view_page' => [3, 'view'],
            'purchase.details' => [3, 'view'],
            'purchase.viewdata' => [3, 'view'],
            'purchase.invoice.pdf' => [3, 'view'],
            'purchasereturn.add' => [3, 'add'],
            'purchasereturn.list' => [3, 'view'],
            'purchase.report.exportpdf' => [3, 'view'],

            // Module 4: Invoices
            'custom_invoice.lists' => [4, 'view'],
            'custom_invoice.add' => [4, 'add'],
            'custom_invoice.edit' => [4, 'edit'],
            'custom_invoice.print' => [4, 'view'],
            'custom_invoice.view' => [4, 'view'],
            'custom_invoice.pdf' => [4, 'view'],
            'custom-invoice.view' => [4, 'view'],
            'custom_invoice.payment_history' => [4, 'view'],
            'custom_invoice.export' => [4, 'view'],
            'custom_invoice.pdf.export' => [4, 'view'],
            'invoice.report' => [4, 'view'],

            // Module 5: Expenses
            'expense.list' => [5, 'view'],
            'expense.add' => [5, 'add'],
            'expense.edit' => [5, 'edit'],
            'expense.category' => [5, 'view'],
            'expense.report' => [5, 'view'],
            'expense.report.view_page' => [5, 'view'],
            'expense.report.exportpdf' => [5, 'view'],
            'expensetype.list' => [5, 'view'],
            'expensetype.add' => [5, 'add'],
            'expensetype.edit' => [5, 'edit'],

            // Module 8: Categories & Brands
            'category.list' => [8, 'view'],
            'category.add' => [8, 'add'],
            'category.edit' => [8, 'edit'],
            'brand.list' => [8, 'view'],
            'brand.add' => [8, 'add'],
            'brand.edit' => [8, 'edit'],
            'quantity_type.quantity' => [8, 'view'],

            // Module 9: Tables
            'customer.list' => [9, 'view'],
            'customer.add' => [9, 'add'],
            'customer.edit' => [9, 'edit'],
            'customer.report' => [9, 'view'],
            'tables.list' => [9, 'view'],

            // Module 10: Vendors
            'vendor.list' => [10, 'view'],
            'vendor.add' => [10, 'add'],
            'vendor.edit' => [10, 'edit'],
            'vendor.report' => [10, 'view'],
            'vendor.view' => [10, 'view'],
            'vendor.import' => [10, 'add'],

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
            'setting.smtpsettings' => [14, 'view'],
            'setting.facebookappconfiguration' => [14, 'view'],
            'company-rules.view-rules' => [14, 'view'],
            'company-rules.create-rules' => [14, 'add'],
            'company-rules.show' => [14, 'view'],
            'company-rules.create' => [14, 'add'],
            'plans.myplan' => [14, 'view'],
            'plans.details' => [14, 'view'],
            'plans.planlist' => [14, 'view'],

            // Module 15: Tax Rates & GST
            'auth.taxrates' => [15, 'view'],
            'auth.currency' => [15, 'view'],
            'exports.gstr1.excel' => [15, 'view'],
            'exports.gstr2.excel' => [15, 'view'],
            'exports.gstr3b.excel' => [15, 'view'],
            'sales.gstr1.export' => [15, 'view'],
            'sales.gstr2.export' => [15, 'view'],
            'sales.gstr3b.export' => [15, 'view'],
            'sales.gstr9c.export' => [15, 'view'],
            'sales.gstr9.export' => [15, 'view'],
            'purchase.gstr9c.export' => [15, 'view'],
            'gst.gstr3b' => [15, 'view'],
            'gst.gstr3b.export' => [15, 'view'],
            'gst.gstr3b.exportexcel' => [15, 'view'],
            'gst.reports.purchase' => [15, 'view'],
            'gst.reports.sales' => [15, 'view'],
            'gst.gstr1' => [15, 'view'],
            'gst.gstr1.export' => [15, 'view'],
            'gst.gstr1.exportexcel' => [15, 'view'],
            'exports.gstr1' => [15, 'view'],
            'gst.gstr2' => [15, 'view'],
            'gst.gstr2.pdf' => [15, 'view'],
            'gst.gstr2.exportexcel' => [15, 'view'],
            'gst.gstr9c' => [15, 'view'],
            'gst.gstr9c.export' => [15, 'view'],
            'gst.gstr9c.exportexcel' => [15, 'view'],
            'gst.gstr9' => [15, 'view'],
            'gst.gstr9.export' => [15, 'view'],
            'gst.gstr9.exportexcel' => [15, 'view'],
            'tds.report' => [15, 'view'],

            // Module 16: Accounting
            'account_ledger.list' => [16, 'view'],
            'account_ledger.add' => [16, 'add'],
            'income-statement.index' => [16, 'view'],
            'profit-loss-statement.index' => [16, 'view'],
            'income-statement.pdf' => [16, 'view'],
            'income-statement.excel' => [16, 'view'],
            'accounting.balance-sheet' => [16, 'view'],

            // Module 17: Inventory
            'inventory.list' => [17, 'view'],
            'inventory.view' => [17, 'view'],
            'inventory.report' => [17, 'view'],
            'appointments.index' => [17, 'view'],
            'appointments.create' => [17, 'add'],
            'appointments.edit' => [17, 'edit'],
            'appointments.view' => [17, 'view'],

            // Module 23: Advance Pay
            'advance_pay.index' => [23, 'view'],
            'advance_pay.create' => [23, 'add'],
            'advance_pay.store' => [23, 'add'],
            'advance_pay.show' => [23, 'view'],
            'advance_pay.edit' => [23, 'edit'],
            'advance_pay.update' => [23, 'edit'],
            'advance_pay.destroy' => [23, 'delete'],

            // Module 26: Attendance
            'attendance.list' => [26, 'view'],
            'attendance.create' => [26, 'add'],
            'attendance.manage' => [26, 'view'],
            'attendance.manage.summary' => [26, 'view'],
            'attendance.manage.history' => [26, 'view'],
            'attendence.summary' => [26, 'view'],
            'attendence.list' => [26, 'view'],
            'attendence.calendar' => [26, 'view'],
            'attendence.manage' => [26, 'view'],
            'attendence.view' => [26, 'view'],
            'attendence.view_calendar' => [26, 'view'],
            'salary.list' => [26, 'view'],
            'salary.view' => [26, 'view'],

            // Module 27: Cash & Bank
            'banks.index' => [27, 'view'],
            'banks.create' => [27, 'add'],
            'banks.edit' => [27, 'edit'],
            'banks.report' => [27, 'view'],
            'banks.view' => [27, 'view'],
            'transaction.bankbook' => [27, 'view'],
            'transaction.cashbook' => [27, 'view'],

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

            // Module 30: Credit/Debit Notes
            'credit-notes-items.index' => [30, 'view'],
            'credit-notes-items.create' => [30, 'add'],
            'credit-notes-items.show' => [30, 'view'],
            'credit-notes-items.edit' => [30, 'edit'],
            'credit-notes-items.update' => [30, 'edit'],
            'credit-notes.index' => [30, 'view'],
            'credit-notes.create' => [30, 'add'],
            'credit-notes.edit' => [30, 'edit'],
            'debit-notes-items.index' => [30, 'view'],
            'debit-notes-items.create' => [30, 'add'],
            'debit-notes-items.edit' => [30, 'edit'],
            'debit-notes-items.show' => [30, 'view'],
            'debit-notes-items.store' => [30, 'add'],
        ];

        $target = null;
        if ($normalizedRouteName && isset($routeMap[$normalizedRouteName])) {
            $target = $routeMap[$normalizedRouteName];
        }

        // 7. Dynamic module resolution if route not explicitly mapped
        if (!$target && $requiredTenantModule !== null) {
            $moduleNameToId = [
                'Dashboard'           => 0,
                'Products'            => 1,
                'Orders'              => 2,
                'Purchases'           => 3,
                'Invoices'            => 4,
                'Expenses'            => 5,
                'Categories & Brands' => 8,
                'Tables'              => 9,
                'Vendors'             => 10,
                'Staff'               => 11,
                'General Settings'    => 14,
                'Tax Rates'           => 15,
                'Accounting'          => 16,
                'Inventory'           => 17,
                'Advance Pay'         => 23,
                'Attendance'          => 26,
                'Cash & Bank'         => 27,
                'Leaves'              => 28,
                'Payroll'             => 29,
                'Credit/Debit Notes'  => 30,
            ];
            $modName = is_array($requiredTenantModule) ? $requiredTenantModule[0] : $requiredTenantModule;
            if (isset($moduleNameToId[$modName])) {
                $act = 'view';
                $uri = strtolower($request->path());
                if (str_contains($uri, 'add') || str_contains($uri, 'create') || str_contains($uri, 'store') || str_contains($uri, 'import')) {
                    $act = 'add';
                } elseif (str_contains($uri, 'edit') || str_contains($uri, 'update')) {
                    $act = 'edit';
                } elseif (str_contains($uri, 'delete') || str_contains($uri, 'destroy')) {
                    $act = 'delete';
                }
                $target = [$moduleNameToId[$modName], $act];
            }
        }

        // 8. Perform permission check and enforce direct redirect to profile
        if ($target) {
            [$moduleId, $action] = $target;

            // Check view permission first; if no view or no specific action permission, deny
            if (!app('hasPermission')($moduleId, $action) || ($action !== 'view' && !app('hasPermission')($moduleId, 'view'))) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You do not have permission to access this module.',
                    ], 403);
                }

                return redirect()->route('auth.profile')
                    ->with('error', 'You do not have permission to access this module or page.')
                    ->with('error_title', 'Access Denied');
            }
        }

        return $next($request);
    }
}

