<?php

use App\Http\Controllers\admin\AdvancePaymentController;
use App\Http\Controllers\admin\AppointmentController;
use App\Http\Controllers\admin\AttendanceController;
use App\Http\Controllers\admin\SalaryController;
use App\Http\Controllers\admin\TablesshowController;
use App\Http\Controllers\admin\ReservationController as AdminReservationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\QuotationController;
use App\Http\Controllers\admin\CustomerController;
use App\Http\Controllers\admin\PlanController;
use App\Http\Controllers\admin\StaffController;
use App\Http\Controllers\admin\SalesController;
use App\Http\Controllers\admin\ProductsDeliveryController;
use App\Http\Controllers\admin\VendorController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\SalesReturnController;
use App\Http\Controllers\admin\PurchaseReturnController;
use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\ExpenseController;
use App\Http\Controllers\admin\ExpenseTypeController;
use App\Http\Controllers\admin\InvoiceController;
use App\Http\Controllers\admin\PurchaseController;
use App\Http\Controllers\admin\CustomInvoiceController;
use App\Http\Controllers\admin\SubBranchController;
use App\Http\Controllers\admin\AccountLedgerController;
use App\Http\Controllers\admin\InventoryController;
use App\Http\Controllers\admin\IncomeSheetController;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\api\GstController;
use App\Http\Controllers\admin\CompanyRulesController;
use App\Http\Controllers\admin\DepartmentController;
use App\Http\Controllers\admin\DesignationController;
use App\Http\Controllers\admin\HolidaysController;
use App\Http\Controllers\admin\LeaveController;
use App\Http\Controllers\admin\LeaveTypeController;
use App\Http\Controllers\admin\AttendanceeController;
use App\Http\Controllers\api\PayrollController as ApiPayrollController;
use App\Http\Controllers\api\FaceLoginController;
use App\Http\Controllers\admin\NotificationController;

use App\Http\Controllers\admin\BankMasterController;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\api\LoginController;

use App\Http\Controllers\Gstr1ExportController;
use App\Http\Controllers\Gstr2ExportController;
use App\Http\Controllers\Gstr3bExportController;
use App\Http\Controllers\SalesGstr1ExportController;

use App\Http\Controllers\GSTR2gstController;
use App\Http\Controllers\SalesGstr3bExportController;
use App\Http\Controllers\SalesGSTR9CController;
use App\Http\Controllers\SalesGSTR9Controller;
use App\Http\Controllers\PurchaseGSTR9CController;
use App\Http\Controllers\admin\BalanceSheetController;
use App\Http\Controllers\admin\QuantityTypeController;
use App\Http\Controllers\admin\TransactionController;
use App\Http\Controllers\admin\CreditNoteItemController;
use App\Http\Controllers\admin\CreditNotesTypeController;
use App\Http\Controllers\admin\DebitNoteItemController;
use App\Http\Controllers\SmtpSettingController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




Route::get('/migrate/users', function () {
    Artisan::call('migrate --path=/database/migrations/2025_08_19_092634_add_gst_number_and_pan_number_to_users_table.php');
    return "Migration for users table run successfully.";
});

Route::post('/clear-subadmin-session', function () {
    session()->forget('selectedSubAdminId');
    return response()->json(['status' => 'cleared']);
});
Route::post('/set-subadmin-session', [AuthController::class, 'subadmin_session'])->name('subadmin_session');


// Tenant storage route to handle both /storage/{path} and /public/storage/{path}
Route::get('storage/{path}', \App\Http\Controllers\TenantStorageController::class)
    ->where('path', '.*')
    ->name('tenant.storage');

Route::get('public/storage/{path}', \App\Http\Controllers\TenantStorageController::class)
    ->where('path', '.*')
    ->name('tenant.storage.public');

Route::middleware(['guest:web'])->group(function () {
    Route::get('/', [AuthController::class, 'signin'])->name('auth.signin');
    // Route::post('/loginweb', [LoginController::class, 'login'])->name('login');
});


Route::post('/', [LoginController::class, 'login'])->name('login');
Route::post('/face-login', [FaceLoginController::class, 'login'])->name('face-login');
Route::get('/api/face-login-data', [FaceLoginController::class, 'getFaceData']);
Route::post('/auth/face-login', [LoginController::class, 'faceLogin'])->name('auth.face-login');

// Serve face photos directly from storage — bypasses storage:link symlink issues on shared hosting.
Route::get('/face-photo/{filename}', function (string $filename) {
    // Sanitize: only allow safe filenames (no directory traversal)
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        abort(400, 'Invalid filename');
    }

    $path = storage_path('app/public/faces/' . $filename);

    if (!file_exists($path)) {
        abort(404, 'Face photo not found');
    }

    $mime = mime_content_type($path) ?: 'image/jpeg';

    return response()->file($path, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=86400',
        'Access-Control-Allow-Origin' => '*',   // ← allow face-api canvas reads
    ]);
})->where('filename', '[^/]+\\.(?:jpg|jpeg|png|webp)');


Route::middleware(['auth:web', 'permission'])->group(function () {
    Route::get('sales/report/view-page', [SalesController::class, 'show_sales_report_page'])->name('sales.report.view_page');
    Route::get('purchase/report/view-page', [PurchaseController::class, 'show_purchase_report_page'])->name('purchase.report.view_page');
    Route::get('/expense/report/view-page', [ExpenseController::class, 'show_expense_report_page'])->name('expense.report.view_page');

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    // Search bar
    Route::get('/search-users', [AuthController::class, 'ajaxSearch'])->name('users.ajaxSearch');

    //authcontroller

    Route::get('/forgetpassword', [AuthController::class, 'forgetpassword'])->name('auth.forgetpassword');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('auth.dashboard');
    Route::get('/staff-dashboard', [AuthController::class, 'staffDashboard'])->name('auth.staff-dashboard');
    Route::get('/hr/dashboard', function () {
        return view('hr.dashboard');
    })->name('hr.dashboard');
    Route::get('/profile', [AuthController::class, 'profile'])->name('auth.profile');
    Route::get('/change-password', function () {
        return view('change-password');
    })->name('auth.change-password');
    Route::get('/taxrates', [AuthController::class, 'taxrates'])->name('auth.taxrates');
    Route::get('/currency', [AuthController::class, 'currency'])->name('auth.currency');

    // Income Statement
    Route::get('/income-statement', [IncomeSheetController::class, 'index'])->name('income-statement.index');
    Route::get('/profit-loss-statement', [IncomeSheetController::class, 'index'])->name('profit-loss-statement.index');
    Route::get('/income-statement/pdf', [IncomeSheetController::class, 'generatePdf'])->name('income-statement.pdf');
    Route::get('/income-statement/excel', [IncomeSheetController::class, 'generateExcel'])->name('income-statement.excel');

    // Category
    Route::get('/categories', [CategoryController::class, 'category_list'])->name('category.list');
    Route::get('/add-category', [CategoryController::class, 'add_category'])->name('category.add');
    Route::get('/edit-category/{id}', [CategoryController::class, 'edit_category'])->name('category.edit');
    // Brand
    Route::get('/brands', [BrandController::class, 'brand_list'])->name('brand.list');
    Route::get('/add-brand', [BrandController::class, 'add_brand'])->name('brand.add');
    Route::get('/edit-brand/{id}', [BrandController::class, 'edit_brand'])->name('brand.edit');
    // Product
    Route::get('/products', [ProductController::class, 'product_list'])->name('product.list');
    Route::get('/add-product', [ProductController::class, 'add_product'])->name('product.add');
    Route::get('/edit-product/{id}', [ProductController::class, 'edit_product'])->name('product.edit');
    // Route::get('/product-detail', [ProductController::class, 'product_detail'])->name('product.detail');
    Route::get('/product-detail/{id}', [ProductController::class, 'product_detail'])->name('product.detail');
    Route::get('/import-product', [ProductController::class, 'product_import'])->name('product.import');
    Route::get('/product-view/{id}', [ProductController::class, 'product_view'])->name('product.view');

    // purchase
    // Route::get('/purchase', [ProductController::class, 'product_list'])->name('Product.list');
    // Route::get('/add-product', [ProductController::class, 'add_product'])->name('Product.add');
    // Route::get('/edit-product', [ProductController::class, 'edit_product'])->name('Product.edit');
    // Route::get('/product-detail', [ProductController::class, 'product_detail'])->name('Product.detail');
    // Route::get('/import-product', [ProductController::class, 'product_import'])->name('Product.import');

    // quotation
    Route::get('/quotation', [QuotationController::class, 'quotation_list'])->name('quotation.list');
    Route::get('/add-quotation', [QuotationController::class, 'add_quotation'])->name('quotation.add');
    Route::get('/edit-quotation', [QuotationController::class, 'edit_quotation'])->name('quotation.edit');
    // Customer
    Route::get('/tables', [CustomerController::class, 'customer_list'])->name('customer.list');
    Route::get('/add-table', [CustomerController::class, 'add_customer'])->name('customer.add');
    Route::get('/edit-table/{num}', [CustomerController::class, 'edit_customer'])->name('customer.edit');
    Route::get('/customer-report', [CustomerController::class, 'customer_report'])->name('customer.report');
    // Route::get('/customer-view/{id}', [CustomerController::class, 'customer_view'])->name('customer.view');
    Route::get('/get-tables-list', [CustomerController::class, 'getTables'])->name('tables.list');


    // Sales
    Route::get('/tables-view', [TablesshowController::class, 'create'])->name('tables.create');

    // Reservations list page
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');



    Route::get('/sales/products-delivery', [ProductsDeliveryController::class, 'index'])->name('sales.products-delivery');
    Route::patch('/sales/products-delivery/{order}/status', [ProductsDeliveryController::class, 'updateStatus'])->name('sales.products-delivery.status');
    Route::post('/sales/{order}/type', [SalesController::class, 'updateOrderType'])->name('sales.updateOrderType');
    Route::patch('/sales/{order}/assign-staff', [SalesController::class, 'assignStaff'])->name('sales.assignStaff');

    Route::get('/sales/delivery/{order}', [SalesController::class, 'deliveryDetails'])->name('sales.delivery');
    Route::post('/sales/delivery/{order}', [SalesController::class, 'submitDelivery'])->name('sales.submitDelivery');

    Route::get('/sales', [SalesController::class, 'sales_list'])->name('sales.list');
    // Route::get('/add-sales', [SalesController::class, 'add_sales'])->name('sales.add');
    Route::get('/edit-sales/{num}', [SalesController::class, 'edit_sales'])->name('sales.edit');
    Route::get('/sales-report', [SalesController::class, 'sales_report'])->name('sales.report');
    Route::get('/sales-pool-report', [SalesController::class, 'sales_report'])->name('sales.pool_report');
    Route::get('/sales-details/{num}', [SalesController::class, 'sales_details'])->name('sales.details');
    Route::get('/sales-invoice/{num}', [SalesController::class, 'salse_invoice'])->name('sales.invoice');
    Route::get('/add-sales', [SalesController::class, 'pos'])->name('sales.add');
    Route::get('/sales/invoice/pdf/{id}', [SalesController::class, 'salse_invoice_pdf'])->name('sales.invoice.pdf');
    Route::get('/table/bill/{id}', [SalesController::class, 'tableBillPdf'])->name('table.bill.pdf');


    // Vendor
    Route::get('/vendors', [VendorController::class, 'vendor_list'])->name('vendor.list');
    Route::get('/add-vendor', [VendorController::class, 'add_vendor'])->name('vendor.add');
    Route::get('/edit-vendor/{num}', [VendorController::class, 'edit_vendor'])->name('vendor.edit');
    Route::get('/vendor-report', [VendorController::class, 'vendor_report'])->name('vendor.report');
    Route::get('/vendor-view/{id}', [VendorController::class, 'vendor_view'])->name('vendor.view');
    Route::get('/import-vendor', [VendorController::class, 'import_vendor'])->name('vendor.import');

    //settings
    Route::get('/setting', [SettingController::class, 'generalsettings'])->name('setting.generalsettings');
    Route::get('/settings/smtp', [SmtpSettingController::class, 'index'])
        ->name('setting.smtpsettings');
    Route::get('/setting/facebook-app-configuration', [SettingController::class, 'facebookappconfiguration'])->name('setting.facebookappconfiguration');

    // GST Export
    //Route::get('/gst/gstr1/export-excel', [Gstr1ExportController::class, 'export'])->name('exports.gstr1.excel');
    Route::get('/gst/gstr1/export-excel', [Gstr1ExportController::class, 'export'])->name('exports.gstr1.excel');
    Route::get('/gst/gstr2/export-excel', [Gstr2ExportController::class, 'export'])->name('exports.gstr2.excel');
    Route::get('/gst/gstr3b/export-excel', [Gstr3bExportController::class, 'export'])->name('exports.gstr3b.excel');
    Route::get('/gstr1/export', [SalesGstr1ExportController::class, 'export'])->name('sales.gstr1.export');

    Route::get('/gstr2/export', [GSTR2gstController::class, 'export'])
        ->name('sales.gstr2.export');
    Route::get('/gstr3b/export', [SalesGstr3bExportController::class, 'export'])->name('sales.gstr3b.export');

    // Test route for debugging (remove in production)
    // Route::get('/gstr3b/test', function() {
    //     return 'GSTR3B route is working!';
    // });

    // Test route for GSTR2 debugging (remove in production)
    Route::get('/gstr2/test', function () {
        return 'GSTR2 route is working!';
    });

    Route::get('/gstr9c/export', [SalesGSTR9CController::class, 'export'])
        ->name('sales.gstr9c.export');

    Route::get('/gstr9/export', [SalesGSTR9Controller::class, 'export'])
        ->name('sales.gstr9.export');

    Route::get('/gstr9c/purchase/export', [PurchaseGSTR9CController::class, 'export'])
        ->name('purchase.gstr9c.export');



    Route::get('/account-ledger-list', [AccountLedgerController::class, 'index'])->name('account_ledger.list');
    Route::get('/account-ledger', [AccountLedgerController::class, 'add'])->name('account_ledger.add');

    //salesreturn

    Route::get('/create-sales-return', [SalesReturnController::class, 'createsales_return'])->name('salesreturn.add');
    Route::get('/create-sales-returns', [SalesReturnController::class, 'createsales_returns'])->name('salesreturn.adds');

    Route::get('/edit-sales-return', [SalesReturnController::class, 'editsales_return'])->name('salesreturn.edit');
    Route::get('/edit-sales-returns', [SalesReturnController::class, 'editsales_returns'])->name('salesreturn.edits');

    Route::get('/sales-return-list', [SalesReturnController::class, 'salesreturn_list'])->name('salesreturn.list');
    Route::get('/sales-return-lists', [SalesReturnController::class, 'salesreturn_lists'])->name('salesreturn.lists');

    // Purchase
    Route::get('/purchase-list', [PurchaseController::class, 'purchase_list'])->name('purchase.lists');
    Route::get('/add-purchase', [PurchaseController::class, 'add_purchase'])->name('purchase.add');
    Route::get('/edit-purchase/{num}', [PurchaseController::class, 'edit_purchase'])->name('purchase.edit');
    Route::get('/purchase-invoice', [PurchaseController::class, 'purchase_invoice'])->name('purchase.invoice');
    Route::get('/print-purchase/{id}', [PurchaseController::class, 'printPurchase'])->name('purchase.print');
    Route::get('/purchase-order-report', [PurchaseController::class, 'purchase_order_report'])->name('purchase.orderreport');
    Route::get('/import-purchase', [PurchaseController::class, 'import_purchase'])->name('purchase.import');
    Route::get('/ai-purchase-bill', [\App\Http\Controllers\admin\PurchaseAiBillController::class, 'index'])->name('purchase.ai-bill');
    Route::post('/ai-purchase-bill/parse', [\App\Http\Controllers\admin\PurchaseAiBillController::class, 'parse'])->name('purchase.ai-bill.parse');
    Route::get('/purchase-report', [PurchaseController::class, 'purchase_report'])->name('purchase.report');
    // Route::get('/purchase-details', [PurchaseController::class, 'purchase_details'])->name('purchase.details');
    Route::get('/purchase-details', [PurchaseController::class, 'purchaseDetails'])->name('purchase.details');
    Route::get('/purchase-view/{id}', [PurchaseController::class, 'getPurchaseDetails'])->name('purchase.viewData');
    Route::get('/purchase/invoice/pdf/{id}', [PurchaseController::class, 'purchase_invoice_pdf'])->name('purchase.invoice.pdf');
    // Route::get('/get-quantity-types/{productId}', [ProductController::class, 'getTypes']);

    // Route::get('/purchases/pdf', [PurchaseController::class, 'purchases_report']);
    // Custom Invoice
    Route::get('/custom-invoice-list', [CustomInvoiceController::class, 'custom_invoice_list'])->name('custom_invoice.lists');
    Route::get('/add-custom-invoice', [CustomInvoiceController::class, 'add_custom_invoice'])->name('custom_invoice.add');
    Route::get('/edit-custom-invoice/{num}', [CustomInvoiceController::class, 'edit_custom_invoice'])->name('custom_invoice.edit');
    Route::get('/custom-invoice-print/{num}', [CustomInvoiceController::class, 'custom_invoice_print'])->name('custom_invoice.print');
    Route::get('/invoice-view/{id}', [CustomInvoiceController::class, 'custom_invoice_view'])->name('custom_invoice.view');
    Route::get('/custom-invoice/pdf/{id}', [CustomInvoiceController::class, 'custom_invoice_pdf'])->name('custom_invoice.pdf');
    Route::get('/custom-invoice/payment-history/{id}', [CustomInvoiceController::class, 'getHistory'])->name('custom_invoice.payment_history');
    // Route::post('/custom-invoice/make-payment', [CustomInvoiceController::class, 'makePaymentSubmit'])->name('custom_invoice.make_payment_submit');
    Route::get('/export-invoice', [CustomInvoiceController::class, 'exportCustom_invoice'])->name('custom_invoice.export');
    Route::get('/export-invoice-pdf', [CustomInvoiceController::class, 'exportInvoicePDF'])->name('custom_invoice.pdf.export');


    // Purchase Reports
    Route::get('/purchases/report/{ids}', [PurchaseController::class, 'purchases_report']);
    Route::get('/purchases/report/{ids}/export-pdf', [PurchaseController::class, 'export_purchases_report_pdf'])->name('purchase.report.exportPdf');

    // Sales Reports
    Route::get('/sales/report/{ids}', [SalesController::class, 'sale_report']);
    Route::get('/sales/report/{ids}/export-pdf', [SalesController::class, 'export_sales_report_pdf'])->name('sale.report.exportPdf');
    Route::get('/sales/report/{ids}/export-pdf', [SalesController::class, 'export_sales_report_pdf'])->name('sale.report.exportPdf');


    // //Purchase Return
    // Route::get('admin/get-vendor-name/{invoiceId}', [PurchaseReturnController::class, 'getVendorName']);
    // Route::get('admin/get-invoice-products/{invoiceId}', [PurchaseReturnController::class, 'getInvoiceProducts']);
    // Route::post('/admin/update-quantity', [PurchaseReturnController::class, 'updateQuantity']);



    //purchasereturn
    Route::get('/create-purchase-return', [PurchaseReturnController::class, 'create_purchase_return'])->name('purchasereturn.add');
    Route::get('/edit-purchase-return', [PurchaseReturnController::class, 'edit_purchase_return'])->name('purchase.edit');
    Route::get('/purchase-return-list', [PurchaseReturnController::class, 'purchase_return_list'])->name('purchasereturn.list');
    //expense
    Route::get('/create-expense', [ExpenseController::class, 'create_expense'])->name('expense.add');
    // Route::get('/edit-purchase/{num}', [PurchaseController::class, 'edit_purchase'])->name('purchase.edit');
    Route::get('/edit-expense/{num}', [ExpenseController::class, 'edit_expense'])->name('expense.edit');
    Route::get('/expense-category', [ExpenseController::class, 'expense_category'])->name('expense.category');
    Route::get('/expense-list', [ExpenseController::class, 'expense_list'])->name('expense.list');
    Route::get('/expense-report', [ExpenseController::class, 'expense_report'])->name('expense.report');
    Route::get('/expense/report/{ids}', [ExpenseController::class, 'expense_report_view']);
    Route::get('/expense/report/{ids}/export-pdf', [ExpenseController::class, 'expense_report_pdf'])->name('expense.report.exportPdf');

    // Expense Type
    Route::get('/create-expense-type', [ExpenseTypeController::class, 'create_expense_type'])->name('expensetype.add');
    Route::get('/expense-type-list', [ExpenseTypeController::class, 'expense_type_list'])->name('expensetype.list');
    Route::get('/expense-type/edit/{id}', [ExpenseTypeController::class, 'edit'])->name('expensetype.edit');
    Route::get('/edit-expense-type/{num}', [ExpenseTypeController::class, 'edit_expense_type'])->name('expensetype.edit');


    // Bank
    Route::get('/banks', [BankMasterController::class, 'bank_list'])->name('banks.index');
    Route::get('/banks/create', [BankMasterController::class, 'bank_create'])->name('banks.create');
    Route::get('/edit-bank/{num}', [BankMasterController::class, 'edit_bank'])->name('banks.edit');
    Route::get('/bank-report', [BankMasterController::class, 'bank_report'])->name('banks.report');
    Route::get('/bank-view/{id}', [BankMasterController::class, 'bank_view'])->name('banks.view');

    //transactions
    Route::get('/bankbook', [TransactionController::class, 'bankBook'])->name('transaction.bankbook');
    Route::get('/cashbook', [TransactionController::class, 'cashBook'])->name('transaction.cashbook');

    // credit note items
    Route::get('/credit-note-items', [CreditNoteItemController::class, 'index'])->name('credit-notes-items.index');
    Route::get('/credit-note-items/create', [CreditNoteItemController::class, 'create'])->name('credit-notes-items.create');
    Route::get('/view-credit-note-items/{id}', [CreditNoteItemController::class, 'show'])->name('credit-notes-items.show');
    Route::get('/edit-credit-note-items/{id}', [CreditNoteItemController::class, 'edit'])->name('credit-notes-items.edit');
    Route::post('/update-credit-note-items/{id}', [CreditNoteItemController::class, 'update'])->name('credit-notes-items.update');

    // credit notes type
    Route::get('/credit-notes', [CreditNotesTypeController::class, 'creditnotes_list'])->name('credit-notes.index');
    Route::get('credit-notes/create', [CreditNotesTypeController::class, 'create'])->name('credit-notes.create');
    Route::get('/edit-credit-notes/{id}', [CreditNotesTypeController::class, 'edit'])->name('credit-notes.edit');

    // Debit notes item type
    Route::get('/debit-note-items', [DebitNoteItemController::class, 'index'])->name('debit-notes-items.index');
    Route::get('/debit-note-items/create', [DebitNoteItemController::class, 'create'])->name('debit-notes-items.create');
    Route::get('/debit-note-items/edit/{id}', [DebitNoteItemController::class, 'edit'])->name('debit-notes-items.edit');
    Route::get('/debit-note-items/view/{id}', [DebitNoteItemController::class, 'show'])->name('debit-notes-items.show');
    Route::post('/debit-note-items/store', [DebitNoteItemController::class, 'store'])->name('debit-notes-items.store');

    //invoice
    Route::get('/inventory-report', [InvoiceController::class, 'inventory_report'])->name('inventory.report');
    Route::get('/invoice-report', [InvoiceController::class, 'invoice_report'])->name('invoice.report');

    // GST 3B report page (frontend view only)
    Route::get('/gst/gstr-3b', function () {
        return view('gst.gstr3b');
    })->name('gst.gstr3b');

    // GST 3B data endpoint for web (uses session auth, no API token required)
    Route::get('/gst/gstr-3b/data', [GstController::class, 'gstr3b']);
    Route::get('/gst/gstr-3b/export', [GstController::class, 'exportGstr3b'])->name('gst.gstr3b.export');
    Route::get('/gst/gstr-3b/export-excel', [GstController::class, 'exportGstr3bExcel'])->name('gst.gstr3b.exportExcel');

    // GST listing pages (purchase/sales) for sidebar
    Route::get('/gst/reports/purchase-list', function () {
        return view('gst.purchase_list');
    })->name('gst.reports.purchase');
    Route::get('/gst/reports/sales-list', function () {
        return view('gst.sales_list');
    })->name('gst.reports.sales');


    // GSTR-1 page and data
    Route::get('/gst/gstr-1', function () {
        return view('gst.gstr1');
    })->name('gst.gstr1');
    Route::get('/gst/gstr-1/data', [GstController::class, 'gstr1']);
    Route::get('/gst/gstr-1/export', [GstController::class, 'exportGstr1'])->name('gst.gstr1.export');
    Route::get('/gst/gstr-1/export-excel', [GstController::class, 'exportGstr1Excel'])->name('gst.gstr1.exportExcel');
    // routes/web.php
    Route::get('/exports/gstr1', [\App\Http\Controllers\ReportController::class, 'exportGstr1Excel'])
        ->name('exports.gstr1');

    //today
    // routes/web.php


    //today



    // GSTR-2 (purchase) page and data
    Route::get('/gst/gstr-2', function () {
        return view('gst.gstr2');
    })->name('gst.gstr2');
    Route::get('/gst/gstr-2/data', [GstController::class, 'gstr2']);
    Route::get('/gst/gstr-2/pdf', [App\Http\Controllers\api\GstController::class, 'gstr2Pdf'])->name('gst.gstr2.pdf');
    Route::get('/gst/gstr-2/export-excel', [GstController::class, 'exportGstr2Excel'])->name('gst.gstr2.exportExcel');
    Route::get('/export-gstr2', [Gstr2ExportController::class, 'export'])->name('exports.gstr2.excel');



    // GSTR-9C page and data
    Route::get('/gst/gstr-9c', function () {
        return view('gst.gstr9c');
    })->name('gst.gstr9c');
    Route::get('/gst/gstr-9c/data', [GstController::class, 'gstr9c']);
    Route::get('/gst/gstr-9c/export', [GstController::class, 'exportGstr9c'])->name('gst.gstr9c.export');
    Route::get('/gst/gstr-9c/export-excel', [GstController::class, 'exportGstr9cExcel'])->name('gst.gstr9c.exportExcel');


    // GSTR-9 page and data
    Route::get('/gst/gstr-9', function () {
        return view('gst.gstr9');
    })->name('gst.gstr9');
    Route::get('/gst/gstr-9/data', [GstController::class, 'gstr9']);
    Route::get('/gst/gstr-9/export', [GstController::class, 'exportGstr9'])->name('gst.gstr9.export');
    Route::get('/gst/gstr-9/export-excel', [GstController::class, 'exportGstr9Excel'])->name('gst.gstr9.exportExcel');


    // TDS page (UI)
    Route::get('/tds', function () {
        return view('tds.index');
    })->name('tds.report');
    Route::get('/tds-report', function () {
        return view('tds.index');
    });


    Route::get('/custom-invoice/{id}', [CustomInvoiceController::class, 'show'])->name('custom-invoice.view');
    // Route::post('/get_user_data', [SalesController::class, 'get_user_data'])->name('sales.get_user_data');

    Route::get('/staff', [StaffController::class, 'staff_list'])->name('staff.list');
    Route::get('/add-staff', [StaffController::class, 'add_staff'])->name('staff.add');
    Route::get('/edit-staff/{num}', [StaffController::class, 'edit_staff'])->name('staff.edit');
    Route::get('/staff-report', [StaffController::class, 'staff_report'])->name('staff.report');
    Route::get('/staff-view/{id}', [StaffController::class, 'staff_view'])->name('staff.view');


    Route::get('/sub-branch', [SubBranchController::class, 'subbranch_list'])->name('subbranch.list');
    Route::get('/add-subbranch', [SubBranchController::class, 'add_subbranch'])->name('subbranch.add');
    Route::get('/edit-subbranch/{id}', [SubBranchController::class, 'edit_subbranch'])->name('subbranch.edit');
    Route::get('/delete-subbranch/{id}', [SubBranchController::class, 'delete_subbranch'])->name('subbranch.delete');
    Route::get('/view-subbranch/{id}', [SubBranchController::class, 'view_subbranch'])->name('subbranch.view');

    // Plans
    Route::get('/plan', [PlanController::class, 'index'])->name('plans.planlist');
    Route::get('/add-plan', [PlanController::class, 'create'])->name('plans.addplan');
    Route::post('/add-plan', [PlanController::class, 'store'])->name('plans.store');
    Route::get('/edit-plan/{plan}', [PlanController::class, 'edit'])->name('plans.edit');
    Route::put('/edit-plan/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::delete('/delete-plan/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
    Route::get('/my-plan', [PlanController::class, 'myplan'])->name('plans.myplan');
    Route::get('/plan-details', [PlanController::class, 'myplan'])->name('plans.details');
    Route::get('/plans/upgrade-options', [PlanController::class, 'getUpgradePlans'])->name('plans.upgrade.options');
    Route::post('/plans/razorpay/create-order', [PlanController::class, 'createRazorpayOrder'])->name('plans.razorpay.createOrder');
    Route::post('/plans/razorpay/verify', [PlanController::class, 'verifyRazorpayPayment'])->name('plans.razorpay.verify');
    Route::get('/plans/invoice/pdf/{id?}', [PlanController::class, 'downloadInvoicePdf'])->name('plans.invoice.pdf');


    Route::get('/inventory-list', [InventoryController::class, 'index'])->name('inventory.list');
    Route::get('/inventory-View/{id}', [InventoryController::class, 'View'])->name('inventory.View');

    // Accounting balancesheet =========
    Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])->name('accounting.balance-sheet');


    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::get('/appointments/edit/{id}', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::get('/appointments/view/{id}', [AppointmentController::class, 'view'])->name('appointments.view');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/add', [AttendanceController::class, 'add'])->name('attendance.create');
    Route::get('/attendance/manage', [AttendanceController::class, 'manage'])->name('attendance.manage');
    Route::get('/attendance/manage/summary', [AttendanceController::class, 'manageSummary'])->name('attendance.manage.summary');
    Route::get('/attendance/manage/history/{employee_id}', [AttendanceController::class, 'manageHistory'])->name('attendance.manage.history');

    Route::get('/advance_pay', [AdvancePaymentController::class, 'index'])->name('advance_pay.index');
    Route::get('/advance_pay/create', [AdvancePaymentController::class, 'create'])->name('advance_pay.create');
    Route::post('/advance_pay/store', [AdvancePaymentController::class, 'store'])->name('advance_pay.store');
    Route::get('/advance_pay/{id}', [AdvancePaymentController::class, 'show'])->name('advance_pay.show');
    Route::get('/advance_pay/{id}/edit', [AdvancePaymentController::class, 'edit'])->name('advance_pay.edit');
    Route::put('/advance_pay/{id}/update', [AdvancePaymentController::class, 'update'])->name('advance_pay.update');
    Route::delete('/advance_pay/{id}/delete', [AdvancePaymentController::class, 'destroy'])->name('advance_pay.destroy');

    // Salary
    Route::get('/salary-list', [SalaryController::class, 'List'])->name('salary.list');
    Route::get('/salary/view', [SalaryController::class, 'viewList'])->name('salary.view');

    //Quantity Type
    Route::get('/quantity-type', [QuantityTypeController::class, 'index'])->name('quantity_type.quantity');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notifications');
    Route::get('/notifications/view', [NotificationController::class, 'getAllNotifications'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}/delete', [NotificationController::class, 'delete']);
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll']);

    // Company Rules pages
    Route::get('/view-rules', [CompanyRulesController::class, 'display_rules'])->name('company-rules.view-rules');
    Route::get('/creates-rules', [CompanyRulesController::class, 'create_rules'])->name('company-rules.create-rules');
    Route::get('/company-rules', [CompanyRulesController::class, 'company_rules'])->name('company-rules.show');
    Route::get('/company-rules/create', [CompanyRulesController::class, 'create'])->name('company-rules.create');

    // Department pages
    Route::get('/department', [DepartmentController::class, 'createPage'])->name('department.create');
    Route::get('/department/{id}', [DepartmentController::class, 'createPage'])->name('department.edit');
    Route::get('/departmentview', [DepartmentController::class, 'indexPage'])->name('department.view');

    // Designation pages
    Route::get('/designation', [DesignationController::class, 'createPage'])->name('designation.create');
    Route::post('/designation/edit', [DesignationController::class, 'openEditPage'])->name('designation.edit.open');
    Route::get('/designationview', [DesignationController::class, 'indexPage'])->name('designation.view');

    // Holiday pages
    Route::get('/company-holidays', [HolidaysController::class, 'calendarPage'])->name('holidays.calendar');
    Route::get('/holidays', [HolidaysController::class, 'indexPage'])->name('holidays.index');
    Route::get('/holidays/add', [HolidaysController::class, 'createPage'])->name('holidays.create');
    Route::get('/holidays/edit/{id}', [HolidaysController::class, 'editPage'])->name('holidays.edit');

    // Leave type pages
    Route::get('/leave_type', [LeaveTypeController::class, 'createPage'])->name('leave-type.create');
    Route::post('/leave_type/edit', [LeaveTypeController::class, 'openEditPage'])->name('leave-type.edit.open');
    Route::get('/leavetypeview', [LeaveTypeController::class, 'indexPage'])->name('leave-type.view');
    Route::get('/leaveview', [LeaveController::class, 'indexPage'])->name('leave.view');
    Route::get('/leave-request', [LeaveController::class, 'leaveRequest'])->name('leave.request');
    Route::post('/leave-request/{leaveId}/status', [LeaveController::class, 'updateStatusWeb'])->name('leave.request.status');
    Route::get('/leaveviews', [LeaveController::class, 'legacyPage'])->name('leave.views');
    Route::get('/addleave', [LeaveController::class, 'createPage'])->name('leave.add');

    // Converted attendance module from legacy CodeIgniter screens
    Route::prefix('staff/attendance')->name('attendence.')->group(function () {
        Route::get('/summary', [AttendanceeController::class, 'display'])->name('summary');
        Route::get('/list', [AttendanceeController::class, 'view'])->name('list');
        Route::get('/calendar', [AttendanceeController::class, 'viewCalendar'])->name('calendar');
        Route::post('/checkin', [AttendanceeController::class, 'checkIn'])->name('checkin.web');
        Route::post('/checkout', [AttendanceeController::class, 'checkOut'])->name('checkout.web');
    });

    // omsai-ERP style staff check-in / check-out (web session, GPS capture)
    Route::get('/staff/check-status', [AttendanceeController::class, 'staffCheckStatus'])->name('staff.checkstatus');
    Route::post('/staff/check-in', [AttendanceeController::class, 'staffCheckIn'])->name('staff.checkin');
    Route::post('/staff/check-out', [AttendanceeController::class, 'staffCheckOut'])->name('staff.checkout');

    // Backward-compatible URLs used inside the legacy attendance UI
    Route::get('/attendence', [AttendanceeController::class, 'display'])->name('attendence.manage');
    Route::get('/view', [AttendanceeController::class, 'view'])->name('attendence.view');
    Route::get('/view-calendar', [AttendanceeController::class, 'viewCalendar'])->name('attendence.view_calendar');

    // Payroll pages
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [ApiPayrollController::class, 'page'])->name('create');
        Route::get('/{id}', [ApiPayrollController::class, 'page'])->whereNumber('id')->name('edit');
        Route::get('/salary', [ApiPayrollController::class, 'groupsalaryPage'])->name('salary');
        Route::get('/salary-details', [ApiPayrollController::class, 'salaryDetails'])->name('salary-details');
        Route::get('/profile/{id}', [ApiPayrollController::class, 'profilePage'])->whereNumber('id')->name('profile');
        Route::get('/details/{id}', [ApiPayrollController::class, 'getProfile'])->whereNumber('id')->name('details');
        Route::get('/download-slip/{id}', [ApiPayrollController::class, 'downloadSlip'])->whereNumber('id')->name('download-slip');
    });
    Route::get('/payrollview', [ApiPayrollController::class, 'display'])->name('payroll.list');
});
