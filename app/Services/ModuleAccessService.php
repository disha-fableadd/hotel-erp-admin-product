<?php

namespace App\Services;

use Illuminate\Http\Request;

class ModuleAccessService
{
    /**
     * Determine if a route or URL is exempt from tenant module access checks.
     */
    public static function isExemptRoute(Request $request, ?string $normalizedRouteName = null): bool
    {
        if (!$normalizedRouteName) {
            $route = $request->route();
            $normalizedRouteName = $route && $route->getName() ? strtolower($route->getName()) : null;
        }

        // Authentication & basic profile / password routes
        $exemptRouteNames = [
            'login',
            'auth.signin',
            'auth.face-login',
            'face-login',
            'logout',
            'auth.profile',
            'auth.change-password',
            'auth.forgetpassword',
            'tenant.storage',
            'users.ajaxsearch',
            'subadmin_session',
            'clear-subadmin-session',
            'iclock',
        ];

        if ($normalizedRouteName && in_array($normalizedRouteName, $exemptRouteNames, true)) {
            return true;
        }

        // Path-based exemptions
        if ($request->is(
            '/',
            'login',
            'logout',
            'profile',
            'change-password',
            'forgetpassword',
            'storage/*',
            'public/storage/*',
            'search-users',
            'clear-subadmin-session',
            'set-subadmin-session',
            'face-login*',
            'api/face-login-data',
            'auth/face-login',
            'face-photo/*',
            'iclock/*',
            'attendance/biometric*'
        )) {
            return true;
        }

        return false;
    }

    /**
     * Resolve the required tenant module name(s) for a given request.
     * Returns null if no specific module restriction applies.
     *
     * @return string|array|null
     */
    public static function resolveRequiredModule(Request $request, ?string $normalizedRouteName = null): string|array|null
    {
        if (self::isExemptRoute($request, $normalizedRouteName)) {
            return null;
        }

        if (!$normalizedRouteName) {
            $route = $request->route();
            $normalizedRouteName = $route && $route->getName() ? strtolower($route->getName()) : null;
        }

        // 1. Dashboard
        if ($request->is('dashboard', 'staff-dashboard') ||
            ($normalizedRouteName && in_array($normalizedRouteName, ['auth.dashboard', 'auth.staff-dashboard'], true))) {
            return 'Dashboard';
        }

        // 2. Products Module
        if ($normalizedRouteName && str_starts_with($normalizedRouteName, 'product.')) {
            return 'Products';
        }
        if ($request->is('products*', 'add-product*', 'edit-product*', 'product-detail*', 'product-view*', 'import-product*')) {
            return 'Products';
        }

        // 3. Category & Brands (Catalog Setup)
        if ($normalizedRouteName && (
            str_starts_with($normalizedRouteName, 'category.') ||
            str_starts_with($normalizedRouteName, 'brand.') ||
            str_starts_with($normalizedRouteName, 'quantity_type.')
        )) {
            return 'Categories & Brands';
        }
        if ($request->is('categories*', 'brands*', 'quantity-type*')) {
            return 'Categories & Brands';
        }

        // 4. Orders / Sales
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'sales.') || str_starts_with($normalizedRouteName, 'order.'))) {
            return 'Orders';
        }
        if ($request->is('sales*', 'orders*')) {
            return 'Orders';
        }

        // 5. Purchases
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'purchase.') || str_starts_with($normalizedRouteName, 'purchases.'))) {
            return 'Purchases';
        }
        if ($request->is('purchases*', 'purchase*')) {
            return 'Purchases';
        }

        // 6. Invoices
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'custom_invoice.') || str_starts_with($normalizedRouteName, 'custom-invoice.'))) {
            return 'Invoices';
        }
        if ($request->is('custom-invoice*', 'custom_invoice*', 'invoices*')) {
            return 'Invoices';
        }

        // 7. Expenses
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'expense.') || str_starts_with($normalizedRouteName, 'expense_type.'))) {
            return 'Expenses';
        }
        if ($request->is('expense*', 'expenses*', 'expense-category*')) {
            return 'Expenses';
        }

        // 8. Tables / Table Reservations
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'tables.') || str_starts_with($normalizedRouteName, 'reservations.') || str_starts_with($normalizedRouteName, 'table.'))) {
            return 'Tables';
        }
        if ($request->is('tables*', 'reservations*', 'table-reservations*')) {
            return 'Tables';
        }

        // 9. Vendors
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'vendor.') || str_starts_with($normalizedRouteName, 'supplier.'))) {
            return 'Vendors';
        }
        if ($request->is('vendors*', 'suppliers*', 'supplier*')) {
            return 'Vendors';
        }

        // 10. Staff
        if ($normalizedRouteName && str_starts_with($normalizedRouteName, 'staff.')) {
            return 'Staff';
        }
        if ($request->is('staff*', 'employees*')) {
            return 'Staff';
        }

        // 11. General Settings
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'setting.') || str_starts_with($normalizedRouteName, 'settings.'))) {
            return 'General Settings';
        }
        if ($request->is('general-settings*', 'settings*', 'setting*')) {
            return 'General Settings';
        }

        // 12. Tax Rates
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'taxrates.') || str_starts_with($normalizedRouteName, 'tax.'))) {
            return 'Tax Rates';
        }
        if ($request->is('taxrates*', 'tax-rates*', 'tax*')) {
            return 'Tax Rates';
        }

        // 13. Accounting
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'accounting.') || str_starts_with($normalizedRouteName, 'income-statement.'))) {
            return 'Accounting';
        }
        if ($request->is('accounting*', 'income-statement*')) {
            return 'Accounting';
        }

        // 14. Inventory
        if ($normalizedRouteName && str_starts_with($normalizedRouteName, 'inventory.')) {
            return 'Inventory';
        }
        if ($request->is('inventory*', 'manage-inventory*')) {
            return 'Inventory';
        }

        // 15. Advance Pay
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'advance_pay.') || str_starts_with($normalizedRouteName, 'advance-pay.'))) {
            return 'Advance Pay';
        }
        if ($request->is('advance-pay*', 'advance_pay*')) {
            return 'Advance Pay';
        }

        // 16. Attendance
        if ($normalizedRouteName && str_starts_with($normalizedRouteName, 'attendance.')) {
            return 'Attendance';
        }
        if ($request->is('attendance*', 'attendance-report*')) {
            return 'Attendance';
        }

        // 17. Cash & Bank
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'transaction.') || str_starts_with($normalizedRouteName, 'banks.'))) {
            return 'Cash & Bank';
        }
        if ($request->is('transaction*', 'bank*', 'cashbook*', 'bankbook*')) {
            return 'Cash & Bank';
        }

        // 18. Leaves
        if ($normalizedRouteName && str_starts_with($normalizedRouteName, 'leave.')) {
            return 'Leaves';
        }
        if ($request->is('leave*', 'leaves*')) {
            return 'Leaves';
        }

        // 19. Payroll
        if ($normalizedRouteName && (str_starts_with($normalizedRouteName, 'payroll.') || str_starts_with($normalizedRouteName, 'salary.'))) {
            return 'Payroll';
        }
        if ($request->is('payroll*', 'salary*')) {
            return 'Payroll';
        }

        // 20. Credit/Debit Notes
        if ($normalizedRouteName && (
            str_starts_with($normalizedRouteName, 'credit-notes.') ||
            str_starts_with($normalizedRouteName, 'credit-notes-items.') ||
            str_starts_with($normalizedRouteName, 'debit-notes.') ||
            str_starts_with($normalizedRouteName, 'debit_notes_items.')
        )) {
            return 'Credit/Debit Notes';
        }
        if ($request->is('credit-notes*', 'debit-notes*')) {
            return 'Credit/Debit Notes';
        }

        return null;
    }
}
