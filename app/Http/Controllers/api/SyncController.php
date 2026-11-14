<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\Tenancy\TenantManager;
use App\Services\DashboardMetricsService;
use App\Exceptions\TenantResolutionException;

class SyncController extends Controller
{
    /**
     * Map payload table names to their respective Eloquent Models.
     */
    /**
     * Map payload table names to their respective Eloquent Models.
     * Covers 100% of all ERP & Hotel operational tables.
     */
    protected $modelMap = [
        // Masters & Config
        'categories' => \App\Models\Category::class,
        'brands' => \App\Models\Brand::class,
        'quantity_types' => \App\Models\QuantityType::class,
        'taxes' => \App\Models\TaxRate::class,
        'currency' => \App\Models\Currency::class,
        'discounts' => \App\Models\Discounts::class,
        'settings' => \App\Models\Setting::class,
        'smtp_settings' => \App\Models\SmtpSetting::class,
        'branches' => \App\Models\Branch::class,
        'location_settings' => \App\Models\LocationSettingsModel::class,

        // Hotel & Restaurant Tables & Reservations
        'table_no' => \App\Models\Table_no::class,
        'table_reservations' => \App\Models\TableReservation::class,

        // Products & Inventory
        'products' => \App\Models\Product::class,
        'product_inventory' => \App\Models\ProductInventory::class,

        // Users & Details (Staff, Customers, Waiters)
        'users' => \App\Models\User::class,
        'user_details' => \App\Models\UserDetail::class,

        // Orders & POS (Billing, KOT, Room Orders)
        'orders' => \App\Models\Order::class,
        'order_items' => \App\Models\OrderItem::class,
        'payment_store' => \App\Models\PaymentStore::class,

        // Purchases & Vendors
        'purchases' => \App\Models\Purchases::class,
        'purchase_invoice' => \App\Models\PurchaseInvoice::class,
        'ai_purchase_bills' => \App\Models\AiPurchaseBill::class,

        // Custom Invoices
        'custom_invoice' => \App\Models\CustomInvoice::class,
        'custom_invoice_item' => \App\Models\CustomInvoiceItem::class,

        // Accounting, Banking & Notes
        'expenses' => \App\Models\Expense::class,
        'expense_types' => \App\Models\ExpenseType::class,
        'bank_master' => \App\Models\BankMaster::class,
        'credit_notes_type' => \App\Models\CreditNotesType::class,
        'credit_note_items' => \App\Models\CreditNoteItem::class,
        'debit_notes_type' => \App\Models\DebitNotesType::class,
        'debit_note_items' => \App\Models\DebitNoteItem::class,

        // Deliveries
        'deliveries' => \App\Models\Delivery::class,
        'delivery_items' => \App\Models\DeliveryItem::class,

        // HR, Attendance & Payroll
        'department' => \App\Models\DepartmentModel::class,
        'designation' => \App\Models\DesignationModel::class,
        'attendance' => \App\Models\Attendance::class,
        'attendances' => \App\Models\AttendanceModel::class,
        'log_attendance' => \App\Models\LogAttendance::class,
        'advance_payments' => \App\Models\AdvancePayment::class,
        'leaves' => \App\Models\LeaveModel::class,
        'leave_type' => \App\Models\LeaveTypeModel::class,
        'holiday_calendar' => \App\Models\HolidayCalendarModel::class,
        'payroll' => \App\Models\PayrollModel::class,
        'salaries' => \App\Models\Salary::class,
        'company_rules' => \App\Models\CompanyRulesModel::class,
        'face_login_audits' => \App\Models\FaceLoginAudit::class,

        // CRM & Notifications
        'appointments' => \App\Models\Appointment::class,
        'notifications' => \App\Models\Notification::class,
        'whatsapp_message_templates' => \App\Models\WhatsAppMessageTemplate::class,
        'facebook_app_configurations' => \App\Models\FacebookAppConfiguration::class,

        // Plans & Licenses
        'plans' => \App\Models\Plan::class,
        'plan_features' => \App\Models\PlanFeature::class,
        'product_licenses' => \App\Models\ProductLicense::class,
        'product_license_domains' => \App\Models\ProductLicenseDomain::class,

        // RBAC Modules & Permissions
        'modules' => \App\Models\Module::class,
        'user_permissions' => \App\Models\UserPermission::class,
    ];

    /**
     * Read client license key from headers/body (admin_erp / admin-client unique_id).
     */
    protected function extractLicenseKey(Request $request): ?string
    {
        $raw = $request->header('X-License-Key')
            ?? $request->header('X-Tenant-Key')
            ?? $request->header('X-Purchase-Code')
            ?? $request->input('license_key')
            ?? $request->input('tenant_license')
            ?? $request->input('unique_id')
            ?? $request->input('purchase_code');

        $key = is_string($raw) ? trim($raw) : '';

        return $key !== '' ? $key : null;
    }

    /**
     * Public client summary for Flutter (from admin-client tenant resolve).
     */
    protected function clientPayloadFromTenant(?array $tenant): ?array
    {
        if (! is_array($tenant) || empty($tenant)) {
            return null;
        }

        return [
            'id' => $tenant['client_id'] ?? $tenant['id'] ?? null,
            'tenant_id' => $tenant['id'] ?? null,
            'client_id' => $tenant['client_id'] ?? $tenant['id'] ?? null,
            'client_name' => $tenant['client_name'] ?? $tenant['name'] ?? null,
            'brand_name' => $tenant['brand_name'] ?? null,
            'domain' => $tenant['domain'] ?? null,
            'email' => $tenant['email'] ?? null,
            'plan_name' => $tenant['plan_name'] ?? 'Standard',
            'status' => $tenant['status'] ?? null,
            'membership_status' => $tenant['membership_status'] ?? null,
            'expiry_date' => $tenant['expiry_date'] ?? null,
            'max_user' => $tenant['max_user'] ?? null,
            'max_branch' => $tenant['max_branch'] ?? null,
            'product_code' => $tenant['product_code'] ?? config('tenant.product_code'),
            'project_code' => $tenant['project_code'] ?? config('tenant.project_code'),
            'license_key' => $tenant['license_key'] ?? $tenant['unique_id'] ?? null,
            'database' => $tenant['db']['resolved_database'] ?? $tenant['db']['database'] ?? null,
        ];
    }

    /**
     * Resolve and activate the specific client DB via admin-client license.
     * When TENANCY_ENABLED=true, license_key is REQUIRED for sync APIs.
     *
     * @throws TenantResolutionException
     */
    protected function resolveTenantIfNeeded(Request $request): ?array
    {
        $tenancy = app(TenantManager::class);
        if (! $tenancy->enabled()) {
            return null;
        }

        $licenseKey = $this->extractLicenseKey($request);
        if ($licenseKey === null) {
            throw new TenantResolutionException(
                'LICENSE_REQUIRED',
                'License key is required. Pass X-License-Key header or license_key / unique_id in the body (from admin-client).',
                403
            );
        }

        // Always resolve from admin-client so pull/push hit that client's database only.
        // Default fresh on sync so Desktop never sticks to a stale/wrong DB after license change.
        $fresh = $request->exists('fresh_license')
            ? (bool) $request->boolean('fresh_license')
            : true;

        $tenant = $tenancy->resolveByLicenseKey($licenseKey, $fresh);

        $dbName = (string) ($tenant['db']['resolved_database'] ?? $tenant['db']['database'] ?? '');
        if ($dbName === '') {
            throw new TenantResolutionException(
                'NOT_PROVISIONED',
                'License resolved but client database is not configured.',
                409
            );
        }

        // Hard guarantee: response/client payload license matches what Flutter sent
        $tenant['unique_id'] = strtoupper(trim($licenseKey));
        $tenant['license_key'] = $tenant['unique_id'];

        return $tenant;
    }

    /**
     * In-memory cache of existing database tables per database connection: [db_name => [table_name => true]]
     * @var array<string, array<string, bool>>
     */
    protected static $cachedExistingTables = [];

    /**
     * In-memory cache of columns per table: [db_name => [table_name => [column_name => true]]]
     * @var array<string, array<string, array<string, bool>>>
     */
    protected static $cachedTableColumns = [];

    /**
     * Check if a table exists quickly without querying heavy information_schema.tables.
     * Prevents SQLSTATE[HY000] [2002] Operation not permitted on shared hosting (Hostinger).
     */
    protected function fastHasTable(string $tableName): bool
    {
        $db = DB::getDatabaseName() ?? 'default';
        $lowTable = strtolower(trim($tableName));

        if (!isset(self::$cachedExistingTables[$db])) {
            try {
                $driver = DB::getDriverName();
                if ($driver === 'mysql') {
                    // SHOW TABLES is instant (<1ms) and bypasses information_schema lock/limits
                    $rows = DB::select('SHOW TABLES');
                    self::$cachedExistingTables[$db] = [];
                    foreach ($rows as $row) {
                        $arr = (array) $row;
                        $val = reset($arr);
                        if ($val !== false && $val !== null) {
                            self::$cachedExistingTables[$db][strtolower((string) $val)] = true;
                        }
                    }
                } else {
                    return Schema::hasTable($tableName);
                }
            } catch (\Throwable $e) {
                try {
                    return Schema::hasTable($tableName);
                } catch (\Throwable $ex) {
                    return false;
                }
            }
        }

        return isset(self::$cachedExistingTables[$db][$lowTable]);
    }

    /**
     * Check if a column exists in a table with per-request in-memory caching.
     * Executes SHOW COLUMNS at most once per table for the entire request.
     */
    protected function fastHasColumn(string $tableName, string $columnName): bool
    {
        $db = DB::getDatabaseName() ?? 'default';
        $lowTable = strtolower(trim($tableName));
        $lowCol = strtolower(trim($columnName));

        // Fast table existence check first
        if (!$this->fastHasTable($lowTable)) {
            return false;
        }

        if (!isset(self::$cachedTableColumns[$db][$lowTable])) {
            try {
                $driver = DB::getDriverName();
                if ($driver === 'mysql') {
                    $cleanTable = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
                    $columns = DB::select("SHOW COLUMNS FROM `{$cleanTable}`");
                    self::$cachedTableColumns[$db][$lowTable] = [];
                    foreach ($columns as $c) {
                        $arr = (array) $c;
                        $f = $arr['Field'] ?? $arr['field'] ?? null;
                        if ($f) {
                            self::$cachedTableColumns[$db][$lowTable][strtolower((string) $f)] = true;
                        }
                    }
                } else {
                    return Schema::hasColumn($tableName, $columnName);
                }
            } catch (\Throwable $e) {
                try {
                    return Schema::hasColumn($tableName, $columnName);
                } catch (\Throwable $ex) {
                    return false;
                }
            }
        }

        return isset(self::$cachedTableColumns[$db][$lowTable][$lowCol]);
    }

    // =============================================
    // DASHBOARD METRICS: POST /api/v1/sync/dashboard-metrics
    // Authoritative KPIs matching Web ERP (today food/liquor + volume)
    // =============================================
    public function dashboardMetrics(Request $request)
    {
        try {
            $this->resolveTenantIfNeeded($request);
        } catch (TenantResolutionException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->reason,
                'message' => $e->getMessage(),
                'expiry_date' => $e->context['expiry_date'] ?? null,
            ], 403);
        }

        $branchId = (int) ($request->input('branch_id') ?: 1);
        $dateFilter = (string) ($request->input('date_filter') ?: 'all_time');

        $data = app(DashboardMetricsService::class)->build([
            'branch_id' => $branchId,
            'date_filter' => $dateFilter,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);

        $tenant = null;
        try {
            $tenant = is_array(app()->bound('tenant') ? app('tenant') : null)
                ? app('tenant')
                : (function_exists('tenant') && is_array(tenant()) ? tenant() : null);
        } catch (\Throwable) {
            $tenant = null;
        }

        return response()->json([
            'status' => 'success',
            'server_time' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.u\Z'),
            'branch_id' => $branchId,
            'data' => $data,
            'client' => $this->clientPayloadFromTenant($tenant),
        ], 200);
    }

    // =============================================
    // VERIFY LICENSE: POST /api/v1/sync/verify-license
    // =============================================
    public function verifyLicense(Request $request)
    {
        $licenseKey = $this->extractLicenseKey($request);

        if (empty($licenseKey)) {
            return response()->json([
                'status' => false,
                'error' => 'LICENSE_REQUIRED',
                'message' => 'License key is required. Pass X-License-Key or license_key / unique_id.',
            ], 422);
        }

        $tenancy = app(TenantManager::class);
        if ($tenancy->enabled()) {
            try {
                $tenant = $tenancy->resolveByLicenseKey($licenseKey, true);
                return response()->json([
                    'status' => true,
                    'message' => 'License is active and valid.',
                    'client' => $this->clientPayloadFromTenant($tenant),
                    'tenancy_enabled' => true,
                    'server_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
                ], 200);
            } catch (TenantResolutionException $e) {
                return response()->json([
                    'status' => false,
                    'error' => $e->reason,
                    'message' => $e->getMessage(),
                    'expiry_date' => $e->context['expiry_date'] ?? null,
                ], 403);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'License verification failed: ' . $e->getMessage(),
                ], 500);
            }
        }

        // Single tenant fallback (check ProductLicense model if available)
        if (class_exists(\App\Models\ProductLicense::class)) {
            $license = \App\Models\ProductLicense::where('purchase_code', strtoupper(trim($licenseKey)))
                ->orWhere('purchase_code_hash', hash('sha256', strtoupper(trim($licenseKey))))
                ->first();

            if ($license && $license->isActive()) {
                return response()->json([
                    'status' => true,
                    'message' => 'License is active.',
                    'client' => [
                        'buyer' => $license->buyer,
                        'license_type' => $license->license_type,
                        'status' => $license->status,
                    ],
                    'server_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
                ], 200);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid or inactive license key.',
        ], 403);
    }

    // =============================================
    // AUTH: POST /api/v1/sync/login
    // Get Token & Auth for Sync APIs
    // =============================================
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
            'license_key' => 'nullable|string',
            'unique_id' => 'nullable|string',
        ]);

        $tenant = null;
        try {
            $tenant = $this->resolveTenantIfNeeded($request);
        } catch (TenantResolutionException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->reason,
                'message' => $e->getMessage(),
                'expiry_date' => $e->context['expiry_date'] ?? null,
            ], 403);
        }

        $login = trim($request->email);
        $user = \App\Models\User::where(function ($q) use ($login) {
                    $q->where('email', $login)
                      ->orWhere('username', $login)
                      ->orWhere('phone', $login);
                })
                ->where(function ($q) {
                    $q->where('isDeleted', '!=', 1)->orWhereNull('isDeleted');
                })
                ->orderByDesc('id')
                ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Invalid credentials.'], 401);
        }

        if (isset($user->status) && (int)$user->status === 0) {
            return response()->json(['status' => false, 'message' => 'Account is inactive.'], 403);
        }

        // Token generation
        $token = null;
        if (method_exists($user, 'createToken')) {
            try {
                $token = $user->createToken('HotelSyncToken')->accessToken;
            } catch (\Throwable $e) {
                $token = Str::random(60);
            }
        } else {
            $token = Str::random(60);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => [
                'id' => $user->id,
                'client_uuid' => $user->client_uuid ?? null,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'branch_id' => $user->branch_id ?? 1,
            ],
            'client' => $this->clientPayloadFromTenant($tenant),
            'tenancy_enabled' => app(TenantManager::class)->enabled(),
            'server_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
        ], 200);
    }

    // =============================================
    // DELTA PULL: POST /api/v1/sync/pull
    // Delta Pull (Server -> Flutter Drift DB)
    // =============================================
    public function pull(Request $request)
    {
        $tenant = null;
        try {
            $tenant = $this->resolveTenantIfNeeded($request);
        } catch (TenantResolutionException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->reason,
                'message' => $e->getMessage(),
                'expiry_date' => $e->context['expiry_date'] ?? null,
            ], 403);
        }

        $branchId = $request->input('branch_id');
        $lastSync = $request->input('last_sync_timestamp');
        $tablesFilter = $request->input('tables', []);

        $payloadData = [];
        $deletedIds = [];
        $deletedRecords = [];

        foreach ($this->modelMap as $tableKey => $modelClass) {
            if (!class_exists($modelClass)) {
                continue;
            }

            if (!empty($tablesFilter) && is_array($tablesFilter) && !in_array($tableKey, $tablesFilter, true)) {
                continue;
            }

            $modelInstance = new $modelClass();
            $tableName = $modelInstance->getTable();

            if (!$this->fastHasTable($tableName)) {
                continue;
            }

            // table_no: include soft-deleted so Flutter gets deleted_ids / deleted_records
            $query = ($tableKey === 'table_no')
                ? $modelClass::withoutGlobalScope('active')
                : $modelClass::query();

            $hasUpdated = $this->fastHasColumn($tableName, 'updated_at');
            $hasIsDeleted = $this->fastHasColumn($tableName, 'isDeleted');
            $hasIsDelete = $this->fastHasColumn($tableName, 'is_delete');
            $hasSoftDeletes = $this->fastHasColumn($tableName, 'deleted_at');
            $hasBranch = $this->fastHasColumn($tableName, 'branch_id');

            // Delta time filter
            if ($lastSync && $hasUpdated) {
                $query->where('updated_at', '>', $lastSync);
            }

            // Branch filter if applicable
            if ($branchId && $hasBranch && !in_array($tableKey, ['settings', 'taxes', 'expense_types', 'quantity_types'])) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }

            // Eager load relations for clean nested structures
            if ($tableKey === 'products') {
                $query->with(['category', 'brand', 'quantityType']);
            }
            if ($tableKey === 'orders') {
                $query->with([
                    'order_items' => function ($q) {
                        $q->with('product:id,name,client_uuid,category_id');
                    },
                    'user',
                    'payments'
                ]);
            }
            if ($tableKey === 'order_items') {
                $query->with(['product:id,name,client_uuid,category_id', 'order:id,client_uuid,order_number']);
            }
            if ($tableKey === 'payment_store') {
                $query->with(['order:id,client_uuid']);
            }
            if ($tableKey === 'table_no') {
                $query->with(['order']);
            }
            if ($tableKey === 'table_reservations') {
                $query->with(['table', 'order']);
            }

            try {
                $records = $query->get();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Sync pull query failed for table [{$tableName} / {$tableKey}]: " . $e->getMessage());
                continue;
            }

            $active = [];
            $deleted = [];
            $deletedRecs = [];

            foreach ($records as $r) {
                $isDel = false;
                if ($hasIsDeleted && isset($r->isDeleted)) {
                    $isDel = (int)$r->isDeleted === 1;
                }
                if ($hasIsDelete && isset($r->is_delete)) {
                    $isDel = (int)$r->is_delete === 1;
                }
                if ($hasSoftDeletes && isset($r->deleted_at)) {
                    $isDel = !is_null($r->deleted_at);
                }

                if ($isDel) {
                    $deleted[] = $r->id;
                    $deletedRecs[] = [
                        'remote_id' => $r->id,
                        'client_uuid' => $r->client_uuid ?? null,
                    ];
                } else {
                    if ($tableKey === 'order_items') {
                        if (empty($r->product_name) || trim((string) $r->product_name) === '') {
                            $r->product_name = optional($r->product)->name ?? 'Item';
                        }
                    } elseif ($tableKey === 'orders' && !empty($r->order_items)) {
                        foreach ($r->order_items as $item) {
                            if (empty($item->product_name) || trim((string) $item->product_name) === '') {
                                $item->product_name = optional($item->product)->name ?? 'Item';
                            }
                        }
                    } elseif ($tableKey === 'settings') {
                        // Normalize GST / Service Tax so Desktop always gets stable defaults
                        if ($this->fastHasColumn($tableName, 'service_tax_enabled')) {
                            $r->setAttribute('service_tax_enabled', (int) ($r->service_tax_enabled ?? 0));
                        }
                        if ($this->fastHasColumn($tableName, 'service_tax_rate') && $r->service_tax_rate !== null && $r->service_tax_rate !== '') {
                            $r->setAttribute('service_tax_rate', number_format((float) $r->service_tax_rate, 2, '.', ''));
                        }
                        if ($this->fastHasColumn($tableName, 'service_tax_name') && empty($r->service_tax_name) && (int) ($r->service_tax_enabled ?? 0) === 1) {
                            $r->setAttribute('service_tax_name', 'Service Tax');
                        }
                        if ($this->fastHasColumn($tableName, 'gst_apply_mode') && empty($r->gst_apply_mode)) {
                            $r->setAttribute('gst_apply_mode', 'product');
                        }
                        if ($this->fastHasColumn($tableName, 'overall_gst_rate') && $r->overall_gst_rate !== null && $r->overall_gst_rate !== '') {
                            $r->setAttribute('overall_gst_rate', number_format((float) $r->overall_gst_rate, 2, '.', ''));
                        }
                    } elseif ($tableKey === 'products') {
                        // Force integer category_id for Desktop KPI food/liquor split
                        if (empty($r->category_id) && $r->relationLoaded('category') && $r->category) {
                            $r->setAttribute('category_id', (int) $r->category->id);
                        } elseif (! empty($r->category_id)) {
                            $r->setAttribute('category_id', (int) $r->category_id);
                        }
                        $itemType = method_exists($r, 'resolveItemType') ? $r->resolveItemType() : 'food';
                        $r->setAttribute('item_type', $itemType);
                        $r->setAttribute('resolved_item_type', $itemType);
                        $r->setAttribute('is_liquor', in_array($itemType, ['liquor', 'beverage', 'tobacco', 'bar'], true) ? 1 : 0);
                        if ($r->relationLoaded('category') && $r->category) {
                            $catType = method_exists($r->category, 'resolveItemType')
                                ? $r->category->resolveItemType()
                                : (string) ($r->category->item_type ?? 'food');
                            $r->category->setAttribute('item_type', $catType);
                            $r->category->setAttribute('is_liquor', in_array($catType, ['liquor', 'beverage', 'tobacco', 'bar'], true) ? 1 : 0);
                        }
                    } elseif ($tableKey === 'categories') {
                        $catType = method_exists($r, 'resolveItemType') ? $r->resolveItemType() : 'food';
                        $r->setAttribute('item_type', $catType);
                        $r->setAttribute('is_liquor', in_array($catType, ['liquor', 'beverage', 'tobacco', 'bar'], true) ? 1 : 0);
                    } elseif ($tableKey === 'orders') {
                        // Ensure lifecycle fields always present for Sales Bills / dashboard parity
                        $r->setAttribute('invoice_generated', (int) ($r->invoice_generated ?? 0));
                        $r->setAttribute('invoice_number', $r->invoice_number);
                        $r->setAttribute('order_lifecycle_status', (string) ($r->order_lifecycle_status ?? 'settled_invoice'));
                        if (! empty($r->order_items)) {
                            foreach ($r->order_items as $item) {
                                if (empty($item->category_id) && $item->relationLoaded('product') && $item->product && ! empty($item->product->category_id)) {
                                    $item->setAttribute('category_id', (int) $item->product->category_id);
                                }
                            }
                        }
                    } elseif ($tableKey === 'users') {
                        // Always include admin/staff profile photo for Desktop My Profile sync
                        $rel = $this->normalizeSyncedImagePath((string) ($r->profile_image ?? ''));
                        $r->setAttribute('profile_image', $rel);
                        $r->setAttribute(
                            'profile_image_url',
                            $rel
                                ? (function_exists('tenant_image_url') ? tenant_image_url($rel) : asset('storage/' . $rel))
                                : ($r->profile_image_url ?? null)
                        );
                        $r->setAttribute('remote_id', (int) $r->id);
                        // Never send password hash to Desktop
                        $r->makeHidden(['password', 'remember_token', 'face_descriptor']);
                    }
                    $active[] = $r;
                }
            }

            $payloadData[$tableKey] = $active;
            if (count($deleted) > 0) {
                $deletedIds[$tableKey] = $deleted;
                $deletedRecords[$tableKey] = $deletedRecs;
            }
        }

        $payloadData['deleted_ids'] = (object)$deletedIds;
        $payloadData['deleted_records'] = (object)$deletedRecords;

        return response()->json([
            'status' => 'success',
            'server_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
            'client' => $this->clientPayloadFromTenant($tenant),
            'tenancy_enabled' => app(TenantManager::class)->enabled(),
            'data' => $payloadData,
        ], 200);
    }

    // =============================================
    // FULL PULL: GET /api/sync/full-pull
    // =============================================
    public function fullPull(Request $request)
    {
        $request->merge(['last_sync_timestamp' => null]);
        return $this->pull($request);
    }

    // =============================================
    // PUSH: POST /api/v1/sync/push
    // Offline Batches (Flutter Drift DB -> Server)
    // =============================================
    public function push(Request $request)
    {
        $tenant = null;
        try {
            $tenant = $this->resolveTenantIfNeeded($request);
        } catch (TenantResolutionException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->reason,
                'message' => $e->getMessage(),
                'expiry_date' => $e->context['expiry_date'] ?? null,
            ], 403);
        }

        $branchId = $request->input('branch_id', 1);
        $payload = $request->input('payload', []);

        $syncedMappings = [
            'users' => [],
            'categories' => [],
            'brands' => [],
            'quantity_types' => [],
            'products' => [],
            'table_no' => [],
            'table_reservations' => [],
            'orders' => [],
            'order_items' => [],
            'payment_store' => [],
            'expenses' => [],
            'purchases' => [],
            'purchase_invoice' => [],
        ];

        // Desktop local order PK → MySQL orders.id (same push batch)
        $localToRemoteOrderMap = [];
        $uuidToRemoteOrderMap = [];

        try {
            DB::beginTransaction();

            // 1. Users (Admin, Staff, Customers, Waiters) — bi-directional profile + avatar
            if (!empty($payload['users'])) {
                foreach ($payload['users'] as $u) {
                    $user = $this->resolveSyncedUser($u);
                    $uuid = $u['client_uuid']
                        ?? $user->client_uuid
                        ?? (string) Str::uuid();

                    // Never blank out an existing admin name/email with empty payload noise
                    if (! empty($u['name'])) {
                        $user->name = $u['name'];
                    } elseif (! $user->exists) {
                        $user->name = 'Guest Customer';
                    }
                    if (array_key_exists('email', $u)) {
                        $user->email = ! empty($u['email']) ? $u['email'] : $user->email;
                    }
                    if (array_key_exists('phone', $u)) {
                        $user->phone = $u['phone'];
                    }
                    if (! empty($u['role'])) {
                        $user->role = $u['role'];
                    } elseif (! $user->exists) {
                        $user->role = 'customer';
                    }
                    if (isset($u['branch_id'])) {
                        $user->branch_id = $u['branch_id'];
                    } elseif (! $user->exists) {
                        $user->branch_id = $branchId;
                    }
                    if (! empty($u['password'])) {
                        $user->password = Hash::make($u['password']);
                    } elseif (! $user->exists && empty($user->password)) {
                        // New synced customer/staff without password — unusable login until set
                        $user->password = Hash::make(Str::random(32));
                    }
                    if (isset($u['status']) && $this->fastHasColumn('users', 'status')) {
                        $user->status = $u['status'];
                    }
                    if (isset($u['account_status']) && $this->fastHasColumn('users', 'account_status')) {
                        $user->account_status = $u['account_status'];
                    }
                    if (isset($u['isDeleted'])) {
                        $user->isDeleted = (int) $u['isDeleted'];
                    }
                    // Profile photo relative path from upload-image (e.g. profile_images/xyz.jpg)
                    if ($this->fastHasColumn('users', 'profile_image') && array_key_exists('profile_image', $u)) {
                        $img = $this->normalizeSyncedImagePath((string) ($u['profile_image'] ?? ''));
                        if ($img !== null) {
                            $user->profile_image = $img;
                        }
                    }
                    if ($this->fastHasColumn('users', 'client_uuid') && empty($user->client_uuid)) {
                        $user->client_uuid = $uuid;
                    }
                    $user->save();

                    // Optional User Detail
                    if (!empty($u['user_detail'])) {
                        $ud = $u['user_detail'];
                        $detailUuid = $ud['client_uuid'] ?? (string) Str::uuid();
                        $userDetail = \App\Models\UserDetail::firstOrNew(['client_uuid' => $detailUuid]);
                        $userDetail->user_id = $user->id;
                        $userDetail->address = $ud['address'] ?? null;
                        $userDetail->city = $ud['city'] ?? null;
                        $userDetail->gst_number = $ud['gst_number'] ?? null;
                        $userDetail->pan_number = $ud['pan_number'] ?? null;
                        $userDetail->client_uuid = $detailUuid;
                        $userDetail->save();
                    }

                    $syncedMappings['users'][] = [
                        'local_id' => $u['local_id'] ?? null,
                        'remote_id' => $user->id,
                        'client_uuid' => $user->client_uuid ?? $uuid,
                        'profile_image' => $user->profile_image ?? null,
                        'profile_image_url' => $user->profile_image_url ?? null,
                    ];
                }
            }

            // 2. Categories
            if (!empty($payload['categories'])) {
                foreach ($payload['categories'] as $c) {
                    $uuid = $c['client_uuid'] ?? (string) Str::uuid();
                    $cat = \App\Models\Category::firstOrNew(['client_uuid' => $uuid]);
                    $cat->name = $c['name'];
                    if (!empty($c['image'])) {
                        $cat->image = $c['image'];
                    }
                    if (array_key_exists('gst_applicable', $c)) {
                        $cat->gst_applicable = filter_var($c['gst_applicable'], FILTER_VALIDATE_BOOLEAN);
                    } elseif (! $cat->exists) {
                        $cat->gst_applicable = true;
                    }
                    if ($this->fastHasColumn('categories', 'item_type') && array_key_exists('item_type', $c)) {
                        $type = strtolower(trim((string) $c['item_type']));
                        $cat->item_type = in_array($type, ['food', 'beverage', 'liquor', 'tobacco', 'general'], true)
                            ? $type
                            : ((isset($c['is_liquor']) && (int) $c['is_liquor'] === 1) ? 'liquor' : 'food');
                    } elseif ($this->fastHasColumn('categories', 'item_type') && array_key_exists('is_liquor', $c)) {
                        $cat->item_type = ((int) $c['is_liquor'] === 1) ? 'liquor' : 'food';
                    }
                    $cat->branch_id = $c['branch_id'] ?? $branchId;
                    if (isset($c['isDeleted'])) {
                        $cat->isDeleted = (int)$c['isDeleted'];
                    }
                    $cat->client_uuid = $uuid;
                    $cat->save();

                    $syncedMappings['categories'][] = [
                        'local_id' => $c['local_id'] ?? null,
                        'remote_id' => $cat->id,
                        'client_uuid' => $uuid,
                    ];
                }
            }

            // 3. Brands
            if (!empty($payload['brands'])) {
                foreach ($payload['brands'] as $b) {
                    $uuid = $b['client_uuid'] ?? (string) Str::uuid();
                    $brand = \App\Models\Brand::firstOrNew(['client_uuid' => $uuid]);
                    $brand->name = $b['name'];
                    if (!empty($b['logo'])) {
                        $brand->logo = $b['logo'];
                    }
                    $brand->branch_id = $b['branch_id'] ?? $branchId;
                    if (isset($b['isDeleted'])) {
                        $brand->isDeleted = (int)$b['isDeleted'];
                    }
                    $brand->client_uuid = $uuid;
                    $brand->save();

                    $syncedMappings['brands'][] = [
                        'local_id' => $b['local_id'] ?? null,
                        'remote_id' => $brand->id,
                        'client_uuid' => $uuid,
                    ];
                }
            }

            // 4. Quantity Types (Units) — accept both quantity_types and units keys from desktop
            $quantityTypesPayload = [];
            if (!empty($payload['quantity_types']) && is_array($payload['quantity_types'])) {
                $quantityTypesPayload = array_merge($quantityTypesPayload, $payload['quantity_types']);
            }
            if (!empty($payload['units']) && is_array($payload['units'])) {
                $quantityTypesPayload = array_merge($quantityTypesPayload, $payload['units']);
            }
            if (!empty($quantityTypesPayload)) {
                foreach ($quantityTypesPayload as $qt) {
                    $uuid = $qt['client_uuid'] ?? (string) Str::uuid();
                    $unit = \App\Models\QuantityType::firstOrNew(['client_uuid' => $uuid]);
                    // DB column is quantity_type (not type/name)
                    $unitName = trim((string) ($qt['quantity_type'] ?? $qt['type'] ?? $qt['name'] ?? $qt['unit_name'] ?? ''));
                    if ($unitName === '') {
                        $unitName = 'Pcs';
                    }
                    $unit->quantity_type = $unitName;
                    $unit->branch_id = $qt['branch_id'] ?? $branchId;
                    if (isset($qt['isDeleted'])) {
                        $unit->isDeleted = (int) $qt['isDeleted'];
                    }
                    $unit->client_uuid = $uuid;
                    $unit->save();

                    $syncedMappings['quantity_types'][] = [
                        'local_id' => $qt['local_id'] ?? null,
                        'remote_id' => $unit->id,
                        'client_uuid' => $uuid,
                    ];
                }
            }

            // 5. Restaurant Tables (table_no) — same fields as web / get-all-tables (incl. Rooftop + printed)
            if (!empty($payload['table_no'])) {
                foreach ($payload['table_no'] as $t) {
                    $uuid = $t['client_uuid'] ?? (string) Str::uuid();
                    $table = \App\Models\Table_no::withoutGlobalScope('active')
                        ->firstOrNew(['client_uuid' => $uuid]);
                    $table->table_name = $t['table_name'];
                    // Free-text type; web + Flutter use AC | Non AC | Premium | Rooftop
                    $table->table_type = $t['table_type'] ?? 'AC';

                    $rawStatus = strtolower(trim((string) ($t['status'] ?? '')));
                    if (in_array($rawStatus, [
                        \App\Models\Table_no::STATUS_BLANK,
                        \App\Models\Table_no::STATUS_RUNNING,
                        \App\Models\Table_no::STATUS_RESERVED,
                        \App\Models\Table_no::STATUS_PRINTED,
                    ], true)) {
                        $table->status = $rawStatus;
                        $table->table_status = \App\Models\Table_no::statusToInt($rawStatus);
                    } else {
                        $tableStatusInt = (int) ($t['table_status'] ?? 0);
                        $table->table_status = $tableStatusInt;
                        $table->status = match ($tableStatusInt) {
                            1 => \App\Models\Table_no::STATUS_RUNNING,
                            2 => \App\Models\Table_no::STATUS_RESERVED,
                            3 => \App\Models\Table_no::STATUS_PRINTED,
                            default => \App\Models\Table_no::STATUS_BLANK,
                        };
                    }

                    $table->branch_id = $t['branch_id'] ?? $branchId;

                    // Resolve order if linked
                    // Resolve order FK via UUID / batch map — never raw Desktop local order_id
                    if (! empty($t['order_client_uuid']) || isset($t['order_id']) || isset($t['local_order_id'])) {
                        $table->order_id = $this->resolveSyncedOrderId($t, $localToRemoteOrderMap, $uuidToRemoteOrderMap);
                    }

                    if (isset($t['isDeleted'])) {
                        $table->isDeleted = (int)$t['isDeleted'];
                    }
                    $table->client_uuid = $uuid;
                    $table->save();

                    $syncedMappings['table_no'][] = [
                        'local_id' => $t['local_id'] ?? null,
                        'remote_id' => $table->id,
                        'client_uuid' => $uuid,
                    ];
                }
            }

            // 6. Products / Menu Items
            if (!empty($payload['products']) && is_array($payload['products'])) {
                foreach ($payload['products'] as $p) {
                    $uuid = $p['client_uuid'] ?? (string) Str::uuid();
                    $prodBranchId = $p['branch_id'] ?? $branchId;

                    // Resolve FKs: remote ID → client UUID → name (auto-create masters when needed)
                    $categoryId = $this->resolveSyncedCategoryId($p, $prodBranchId);
                    $brandId = $this->resolveSyncedBrandId($p, $prodBranchId);
                    $quantityTypeId = $this->resolveSyncedQuantityTypeId($p, $prodBranchId);

                    // Prefer client_uuid; fall back to remote_id / SKU for older desktop payloads
                    $product = \App\Models\Product::firstOrNew(['client_uuid' => $uuid]);
                    if (!$product->exists) {
                        $remoteId = $p['id'] ?? $p['remote_id'] ?? null;
                        if ($remoteId) {
                            $byRemote = \App\Models\Product::find($remoteId);
                            if ($byRemote) {
                                $product = $byRemote;
                            }
                        }
                    }
                    if (!$product->exists) {
                        $sku = trim((string) ($p['sku'] ?? $p['SKU'] ?? ''));
                        if ($sku !== '') {
                            $bySku = \App\Models\Product::where('SKU', $sku)->first();
                            if ($bySku) {
                                $product = $bySku;
                            }
                        }
                    }

                    $product->name = $p['name'] ?? $product->name;
                    $product->SKU = $p['sku'] ?? $p['SKU'] ?? $product->SKU;
                    $product->barcode = $p['barcode'] ?? $product->barcode;
                    $product->description = $p['description'] ?? $product->description;
                    $product->price = $p['price'] ?? $product->price ?? 0;
                    if (array_key_exists('purchase_price', $p) || array_key_exists('cost_price', $p)) {
                        $product->cost_price = $p['purchase_price'] ?? $p['cost_price'] ?? $product->cost_price;
                    }
                    $product->quantity = $p['quantity'] ?? $product->quantity ?? 0;
                    $product->status = $p['status'] ?? $product->status ?? 'active';
                    $product->availablility = $p['availablility'] ?? $p['availability'] ?? $product->availablility ?? 'in_stock';
                    $product->category_id = $categoryId;
                    $product->brand_id = $brandId;
                    $product->quantity_type_id = $quantityTypeId;
                    $product->branch_id = $prodBranchId;

                    if ($this->fastHasColumn('products', 'unit_id')) {
                        $product->unit_id = $quantityTypeId;
                    }
                    if ($this->fastHasColumn('products', 'availability') && isset($p['availability'])) {
                        $product->availability = $p['availability'];
                    }

                    if (!empty($p['images'])) {
                        $product->images = is_array($p['images']) ? json_encode($p['images']) : $p['images'];
                    }

                    if (isset($p['isDeleted'])) {
                        $product->isDeleted = (int) $p['isDeleted'];
                    }
                    if ($this->fastHasColumn('products', 'item_type')) {
                        if (! empty($p['item_type']) || ! empty($p['resolved_item_type'])) {
                            $type = strtolower(trim((string) ($p['item_type'] ?? $p['resolved_item_type'])));
                            $product->item_type = in_array($type, ['food', 'beverage', 'liquor', 'tobacco', 'general'], true)
                                ? $type
                                : null;
                        } elseif (array_key_exists('is_liquor', $p)) {
                            $product->item_type = ((int) $p['is_liquor'] === 1) ? 'liquor' : 'food';
                        }
                    }
                    $product->client_uuid = $uuid;
                    $product->save();

                    $syncedMappings['products'][] = [
                        'local_id' => $p['local_id'] ?? null,
                        'remote_id' => $product->id,
                        'client_uuid' => $uuid,
                    ];
                }
            }

            // 7. Orders & Order Items
            if (!empty($payload['orders'])) {
                foreach ($payload['orders'] as $o) {
                    $orderUuid = $o['client_uuid'] ?? (string) Str::uuid();
                    $order = \App\Models\Order::firstOrNew(['client_uuid' => $orderUuid]);
                    // Prefer existing remote row when Desktop re-pushes with remote_id
                    if (! $order->exists) {
                        $remoteOrderId = $o['remote_id'] ?? $o['id'] ?? null;
                        if ($remoteOrderId) {
                            $byRemote = \App\Models\Order::find($remoteOrderId);
                            if ($byRemote) {
                                $order = $byRemote;
                                if (empty($order->client_uuid)) {
                                    $order->client_uuid = $orderUuid;
                                } else {
                                    $orderUuid = $order->client_uuid;
                                }
                            }
                        }
                    }

                    // Resolve customer / user
                    $userId = $o['user_id'] ?? $o['customer_id'] ?? null;
                    if (!$userId && !empty($o['user_client_uuid'])) {
                        $user = \App\Models\User::where('client_uuid', $o['user_client_uuid'])->first();
                        $userId = $user ? $user->id : null;
                    }
                    if (!$userId) {
                        try {
                            $userId = auth('api')->id() ?? auth()->id();
                        } catch (\Throwable $e) {
                            $userId = null;
                        }
                    }
                    if (!$userId) {
                        $userId = \App\Models\User::where('branch_id', $branchId)->value('id') ?? \App\Models\User::value('id') ?? 1;
                    }

                    // Avoid duplicate order number conflicts
                    $orderNumber = $o['order_number'] ?? null;
                    if ($orderNumber) {
                        $existing = \App\Models\Order::where('order_number', $orderNumber)->first();
                        if ($existing && $existing->client_uuid !== $orderUuid) {
                            $orderNumber = $orderNumber . '-' . strtoupper(Str::random(4));
                        }
                    }

                    // Resolve table_no safely (table_no in DB is BIGINT)
                    $tableId = null;
                    if (isset($o['table_no'])) {
                        if (is_numeric($o['table_no'])) {
                            $tableId = (int)$o['table_no'];
                        } else {
                            $foundTable = \App\Models\Table_no::where('table_name', $o['table_no'])->first();
                            if (!$foundTable && preg_match('/\d+/', (string)$o['table_no'], $matches)) {
                                $tableId = (int)$matches[0];
                            } else {
                                $tableId = $foundTable ? $foundTable->id : 0;
                            }
                        }
                    } elseif (!empty($o['table_client_uuid'])) {
                        $tbl = \App\Models\Table_no::where('client_uuid', $o['table_client_uuid'])->first();
                        $tableId = $tbl ? $tbl->id : 0;
                    }

                    $order->order_number = $orderNumber;
                    $order->user_id = $userId;
                    $order->customer_name = $o['customer_name'] ?? null;
                    $order->customer_phone = $o['customer_phone'] ?? null;
                    $order->table_no = $tableId ?? 0;
                    $order->order_type = $o['order_type'] ?? 'Dine-In';
                    $order->type = $o['type'] ?? 'POS';
                    $order->discount = $o['discount'] ?? 0;
                    $order->total_amount = $o['total_amount'] ?? 0;
                    $order->remaining_amount = $o['remaining_amount'] ?? 0;
                    $order->payment_status = $o['payment_status'] ?? 'completed';
                    $order->payment_method = $o['payment_method'] ?? 'cash';
                    $order->delivery_status = $o['delivery_status'] ?? $order->delivery_status ?? 'running';
                    if (isset($o['table_status'])) {
                        $order->table_status = (int)$o['table_status'];
                    }
                    // Persist GST + Service Tax from Desktop (must match Web POS columns)
                    $this->applyOrderTaxFieldsFromSync($order, $o);
                    // Petpooja-style KOT fire ticket (latest Update delta) — one sync API carries everything
                    if (Schema::hasColumn('orders', 'kot_pending_cancels') && array_key_exists('kot_pending_cancels', $o)) {
                        $order->kot_pending_cancels = $o['kot_pending_cancels'];
                    } elseif (Schema::hasColumn('orders', 'kot_pending_cancels') && array_key_exists('kot_fire_ticket', $o)) {
                        $order->kot_pending_cancels = $o['kot_fire_ticket'];
                    }
                    $order->branch_id = $o['branch_id'] ?? $branchId;
                    if (!empty($o['created_at'])) {
                        $order->created_at = $o['created_at'];
                    }
                    if (isset($o['isDeleted'])) {
                        $order->isDeleted = (int)$o['isDeleted'];
                    }
                    $order->client_uuid = $orderUuid;
                    $order->save();

                    // Map Desktop local order PK / UUID → MySQL id for payment_store / order_items
                    if (! empty($o['local_id'])) {
                        $localToRemoteOrderMap[(string) $o['local_id']] = (int) $order->id;
                        $localToRemoteOrderMap[(int) $o['local_id']] = (int) $order->id;
                    }
                    // Also map payload "id" when it is the desktop local PK (not yet a server remote_id)
                    if (! empty($o['id']) && empty($o['remote_id'])) {
                        $localToRemoteOrderMap[(string) $o['id']] = (int) $order->id;
                        $localToRemoteOrderMap[(int) $o['id']] = (int) $order->id;
                    }
                    $uuidToRemoteOrderMap[$orderUuid] = (int) $order->id;

                    // Process Order Items
                    $itemMappings = [];
                    if (!empty($o['items']) || !empty($o['order_items'])) {
                        $itemsList = !empty($o['items']) ? $o['items'] : $o['order_items'];
                        $syncedItemUuids = [];

                        foreach ($itemsList as $it) {
                            $itemUuid = $it['client_uuid'] ?? (string) Str::uuid();
                            $syncedItemUuids[] = $itemUuid;

                            // Find existing item by client_uuid or create new
                            $orderItem = \App\Models\OrderItem::firstOrNew(['client_uuid' => $itemUuid]);

                            // Resolve product_id
                            $productId = $it['product_id'] ?? null;
                            $prd = null;
                            if (!$productId && !empty($it['product_client_uuid'])) {
                                $prd = \App\Models\Product::where('client_uuid', $it['product_client_uuid'])->first();
                                $productId = $prd ? $prd->id : null;
                            } elseif ($productId) {
                                $prd = \App\Models\Product::find($productId);
                            }
                            if (!$productId) {
                                $productId = \App\Models\Product::where('branch_id', $branchId)->value('id') ?? \App\Models\Product::value('id') ?? 1;
                            }

                            // Resolve user_id (fixes Field 'user_id' doesn't have a default value)
                            $itemUserId = $it['user_id'] 
                                ?? $it['customer_id'] 
                                ?? $order->user_id 
                                ?? $userId 
                                ?? 1;

                            // Resolve category_id (NOT NULL constraint safe)
                            $categoryId = $it['category_id'] 
                                ?? $prd?->category_id 
                                ?? \App\Models\Category::where('branch_id', $branchId)->value('id') 
                                ?? \App\Models\Category::value('id') 
                                ?? 1;

                            $price = floatval($it['price'] ?? $it['unit_price'] ?? ($prd?->price ?? 0.0));
                            $qty = intval($it['quantity'] ?? 1);
                            $totalAmount = floatval($it['total_amount'] ?? ($qty * $price));

                            $orderItem->order_id = $order->id;
                            $orderItem->user_id = $itemUserId;
                            $orderItem->product_id = $productId;
                            $orderItem->category_id = $categoryId;
                            $orderItem->quantity_type_id = $it['quantity_type_id'] ?? $prd?->quantity_type_id ?? null;
                            $orderItem->price = $price;
                            $orderItem->unit_price = floatval($it['unit_price'] ?? $price);
                            $orderItem->quantity = $qty;
                            $orderItem->total_amount = $totalAmount;
                            $orderItem->branch_id = $it['branch_id'] ?? $o['branch_id'] ?? $branchId;
                            $orderItem->created_by = $it['created_by'] ?? $order->user_id ?? $userId;
                            $orderItem->client_uuid = $itemUuid;

                            $resolvedProductName = !empty($it['product_name'])
                                ? trim((string) $it['product_name'])
                                : (optional($prd)->name ?? 'Item');
                            $orderItem->product_name = $resolvedProductName;

                            if (isset($it['variant_id'])) $orderItem->variant_id = $it['variant_id'];
                            if (isset($it['addons'])) $orderItem->addons = $it['addons'];
                            if (isset($it['isDeleted'])) $orderItem->isDeleted = (int)$it['isDeleted'];
                            // KOT printed qty travels in the same sync push (no separate mark-printed API needed for Flutter)
                            if (Schema::hasColumn('order_items', 'kot_printed_qty') && array_key_exists('kot_printed_qty', $it)) {
                                $orderItem->kot_printed_qty = max(0, (int) $it['kot_printed_qty']);
                            }

                            $orderItem->save();

                            $mapping = [
                                'local_id' => $it['local_id'] ?? null,
                                'remote_id' => $orderItem->id,
                                'client_uuid' => $itemUuid,
                            ];
                            $itemMappings[] = $mapping;
                            $syncedMappings['order_items'][] = $mapping;
                        }

                        // Remove items that were deleted from client order
                        if (!empty($syncedItemUuids)) {
                            \App\Models\OrderItem::where('order_id', $order->id)
                                ->whereNotIn('client_uuid', $syncedItemUuids)
                                ->delete();
                        }
                    }

                    $syncedMappings['orders'][] = [
                        'local_id' => $o['local_id'] ?? null,
                        'remote_id' => $order->id,
                        'client_uuid' => $orderUuid,
                        'items' => $itemMappings,
                    ];
                }
            }

            // 7.5 Standalone Order Items (if pushed directly outside orders)
            if (!empty($payload['order_items'])) {
                foreach ($payload['order_items'] as $it) {
                    $itemUuid = $it['client_uuid'] ?? (string) Str::uuid();
                    $orderItem = \App\Models\OrderItem::firstOrNew(['client_uuid' => $itemUuid]);

                    $orderId = $this->resolveSyncedOrderId($it, $localToRemoteOrderMap, $uuidToRemoteOrderMap);
                    $parentOrd = $orderId ? \App\Models\Order::find($orderId) : null;

                    $productId = $it['product_id'] ?? null;
                    $prd = null;
                    if (!$productId && !empty($it['product_client_uuid'])) {
                        $prd = \App\Models\Product::where('client_uuid', $it['product_client_uuid'])->first();
                        $productId = $prd ? $prd->id : null;
                    } elseif ($productId) {
                        $prd = \App\Models\Product::find($productId);
                    }
                    if (!$productId) {
                        $productId = \App\Models\Product::where('branch_id', $branchId)->value('id') ?? \App\Models\Product::value('id') ?? 1;
                    }

                    $itemUserId = $it['user_id'] 
                        ?? $it['customer_id'] 
                        ?? ($parentOrd ? $parentOrd->user_id : null) 
                        ?? 1;

                    $categoryId = $it['category_id'] 
                        ?? $prd?->category_id 
                        ?? \App\Models\Category::where('branch_id', $branchId)->value('id') 
                        ?? \App\Models\Category::value('id') 
                        ?? 1;

                    $price = floatval($it['price'] ?? $it['unit_price'] ?? ($prd?->price ?? 0.0));
                    $qty = intval($it['quantity'] ?? 1);
                    $totalAmount = floatval($it['total_amount'] ?? ($qty * $price));

                    // Never invent order_id=1 — that was a silent FK/wrong-parent bug
                    if (! $orderId) {
                        continue;
                    }
                    $orderItem->order_id = $orderId;
                    $orderItem->user_id = $itemUserId;
                    $orderItem->product_id = $productId;
                    $orderItem->category_id = $categoryId;
                    $orderItem->quantity_type_id = $it['quantity_type_id'] ?? $prd?->quantity_type_id ?? null;
                    $orderItem->price = $price;
                    $orderItem->unit_price = floatval($it['unit_price'] ?? $price);
                    $orderItem->quantity = $qty;
                    $orderItem->total_amount = $totalAmount;
                    $orderItem->branch_id = $it['branch_id'] ?? $branchId;
                    $orderItem->created_by = $it['created_by'] ?? $itemUserId;
                    $orderItem->client_uuid = $itemUuid;

                    $resolvedProductName = !empty($it['product_name'])
                        ? trim((string) $it['product_name'])
                        : (optional($prd)->name ?? 'Item');
                    $orderItem->product_name = $resolvedProductName;

                    if (isset($it['variant_id'])) $orderItem->variant_id = $it['variant_id'];
                    if (isset($it['addons'])) $orderItem->addons = $it['addons'];
                    if (isset($it['isDeleted'])) $orderItem->isDeleted = (int)$it['isDeleted'];
                    if (Schema::hasColumn('order_items', 'kot_printed_qty') && array_key_exists('kot_printed_qty', $it)) {
                        $orderItem->kot_printed_qty = max(0, (int) $it['kot_printed_qty']);
                    }

                    $orderItem->save();

                    $syncedMappings['order_items'][] = [
                        'local_id' => $it['local_id'] ?? null,
                        'remote_id' => $orderItem->id,
                        'client_uuid' => $itemUuid,
                    ];
                }
            }

            // 8. Payment Store
            if (!empty($payload['payment_store'])) {
                $hasAmountCol = $this->fastHasColumn('payment_store', 'amount');
                $hasBranchCol = $this->fastHasColumn('payment_store', 'branch_id');
                $hasClientUuidCol = $this->fastHasColumn('payment_store', 'client_uuid');

                foreach ($payload['payment_store'] as $p) {
                    $uuid = $p['client_uuid'] ?? (string) Str::uuid();

                    if ($hasClientUuidCol) {
                        $payment = \App\Models\PaymentStore::firstOrNew(['client_uuid' => $uuid]);
                    } else {
                        $payment = new \App\Models\PaymentStore();
                    }

                    // 1. Resolve Amount (support both payment_amount and amount)
                    $amount = floatval($p['payment_amount'] ?? $p['amount'] ?? 0.0);

                    // 2. Resolve Order ID — NEVER trust Desktop local SQLite order_id as MySQL FK
                    $orderId = $this->resolveSyncedOrderId($p, $localToRemoteOrderMap, $uuidToRemoteOrderMap);
                    $ord = $orderId ? \App\Models\Order::find($orderId) : null;

                    // 3. Resolve User ID (user_id is NOT NULL in database)
                    $userId = $p['user_id'] ?? null;
                    if (!$userId && !empty($p['user_client_uuid'])) {
                        $usr = \App\Models\User::where('client_uuid', $p['user_client_uuid'])->first();
                        $userId = $usr ? $usr->id : null;
                    }
                    if (!$userId && $ord) {
                        $userId = $ord->user_id;
                    }
                    if (!$userId) {
                        try {
                            $userId = auth('api')->id() ?? auth()->id();
                        } catch (\Throwable $e) {
                            $userId = null;
                        }
                    }
                    if (!$userId) {
                        $userId = \App\Models\User::where('branch_id', $branchId)->value('id') ?? \App\Models\User::value('id') ?? 1;
                    }

                    // 4. Resolve Payment Method & Amounts
                    $method = strtolower($p['payment_method'] ?? 'cash');
                    $cashAmount = isset($p['cash_amount']) ? floatval($p['cash_amount']) : ($method === 'cash' ? $amount : 0.0);
                    $upiAmount = isset($p['upi_amount']) ? floatval($p['upi_amount']) : (in_array($method, ['upi', 'online', 'card', 'bank_transfer', 'qr']) ? $amount : 0.0);

                    // 5. Populate attributes safely
                    $payment->user_id = $userId;
                    // NULL is valid (FK ON DELETE SET NULL). Unverified local IDs are NOT.
                    $payment->order_id = $orderId;
                    $payment->payment_amount = $amount;
                    if ($hasAmountCol) {
                        $payment->amount = $amount;
                    }
                    $payment->payment_date = !empty($p['payment_date']) ? Carbon::parse($p['payment_date'])->toDateString() : Carbon::now('Asia/Kolkata')->toDateString();
                    $payment->payment_type = $p['payment_type'] ?? 'fully';
                    $payment->payment_method = $method;
                    $payment->cash_amount = $cashAmount;
                    $payment->upi_amount = $upiAmount;
                    $payment->remaining_amount = floatval($p['remaining_amount'] ?? 0.0);
                    $payment->status = $p['status'] ?? 'paid';

                    if ($hasBranchCol) {
                        $payment->branch_id = $p['branch_id'] ?? $branchId;
                    }
                    if ($hasClientUuidCol) {
                        $payment->client_uuid = $uuid;
                    }
                    if (!empty($p['bank_id'])) {
                        $payment->bank_id = $p['bank_id'];
                    }
                    if (!empty($p['custom_invoice_id'])) {
                        $payment->custom_invoice_id = $p['custom_invoice_id'];
                    }
                    if (!empty($p['purchase_id'])) {
                        $payment->purchase_id = $p['purchase_id'];
                    }
                    if (isset($p['remarks'])) {
                        $payment->remarks = $p['remarks'];
                    }
                    if (isset($p['isDeleted'])) {
                        $payment->isDeleted = (int)$p['isDeleted'];
                    }

                    $payment->save();

                    // 6. Recalculate Order remaining_amount and payment_status if applicable
                    if ($ord && $amount > 0) {
                        $totalPaid = \App\Models\PaymentStore::where('order_id', $ord->id)->where('isDeleted', 0)->sum('payment_amount');
                        $remaining = max(0, ($ord->total_amount ?? 0) - $totalPaid);
                        $ord->remaining_amount = $remaining;
                        if ($remaining <= 0) {
                            $ord->payment_status = 'completed';
                        } elseif ($remaining < ($ord->total_amount ?? 0)) {
                            $ord->payment_status = 'partially';
                        }
                        $ord->save();
                    }

                    $syncedMappings['payment_store'][] = [
                        'local_id' => $p['local_id'] ?? null,
                        'remote_id' => $payment->id,
                        'client_uuid' => $uuid,
                    ];
                }
            }

            // 9. Table Reservations
            if (!empty($payload['table_reservations'])) {
                foreach ($payload['table_reservations'] as $tr) {
                    $uuid = $tr['client_uuid'] ?? (string) Str::uuid();
                    $res = \App\Models\TableReservation::firstOrNew(['client_uuid' => $uuid]);

                    $tableId = $tr['table_id'] ?? null;
                    if (!$tableId && !empty($tr['table_client_uuid'])) {
                        $tbl = \App\Models\Table_no::where('client_uuid', $tr['table_client_uuid'])->first();
                        $tableId = $tbl ? $tbl->id : null;
                    }

                    $res->table_id = $tableId;
                    $res->branch_id = $tr['branch_id'] ?? $branchId;
                    $res->customer_name = $tr['customer_name'] ?? 'Guest';
                    $res->customer_phone = $tr['customer_phone'] ?? null;
                    $res->reservation_date = $tr['reservation_date'] ?? date('Y-m-d');
                    $res->reservation_time = $tr['reservation_time'] ?? date('H:i:s');
                    $res->guests = $tr['guests'] ?? 2;
                    $res->status = $tr['status'] ?? 'pending';
                    $res->notes = $tr['notes'] ?? null;
                    $res->client_uuid = $uuid;
                    $res->save();

                    $syncedMappings['table_reservations'][] = [
                        'local_id' => $tr['local_id'] ?? null,
                        'remote_id' => $res->id,
                        'client_uuid' => $uuid,
                    ];
                }
            }

            // 10. Expenses
            if (!empty($payload['expenses'])) {
                foreach ($payload['expenses'] as $e) {
                    $uuid = $e['client_uuid'] ?? (string) Str::uuid();
                    $expense = \App\Models\Expense::firstOrNew(['client_uuid' => $uuid]);
                    $expense->expense_type_id = $e['expense_type_id'] ?? 1;
                    $expense->expense_name = $e['expense_name'] ?? 'General Expense';
                    $expense->amount = $e['amount'] ?? 0;
                    $expense->expense_date = $e['expense_date'] ?? date('Y-m-d');
                    $expense->note = $e['note'] ?? null;
                    $expense->branch_id = $e['branch_id'] ?? $branchId;
                    $expense->client_uuid = $uuid;
                    $expense->save();

                    $syncedMappings['expenses'][] = [
                        'local_id' => $e['local_id'] ?? null,
                        'remote_id' => $expense->id,
                        'client_uuid' => $uuid,
                    ];
                }
            }

            // 11. Dynamic Universal Push for all remaining ERP tables & modules
            $handledManually = [
                'users', 'user_details', 'categories', 'brands', 'quantity_types',
                'table_no', 'products', 'orders', 'order_items', 'payment_store',
                'table_reservations', 'expenses', 'purchases', 'purchase_invoice'
            ];

            foreach ($this->modelMap as $tableKey => $modelClass) {
                if (in_array($tableKey, $handledManually, true)) {
                    continue;
                }

                if (!empty($payload[$tableKey]) && is_array($payload[$tableKey])) {
                    if (!isset($syncedMappings[$tableKey])) {
                        $syncedMappings[$tableKey] = [];
                    }

                    $modelInstance = new $modelClass();
                    $fillable = $modelInstance->getFillable();

                    foreach ($payload[$tableKey] as $recordData) {
                        $clientUuid = $recordData['client_uuid'] ?? (string) Str::uuid();
                        $model = $modelClass::firstOrNew(['client_uuid' => $clientUuid]);

                        foreach ($recordData as $key => $value) {
                            if (in_array($key, $fillable, true)) {
                                $model->{$key} = $value;
                            }
                        }

                        $model->client_uuid = $clientUuid;
                        if (in_array('branch_id', $fillable, true) && empty($model->branch_id)) {
                            $model->branch_id = $recordData['branch_id'] ?? $branchId;
                        }
                        if (isset($recordData['isDeleted']) && in_array('isDeleted', $fillable, true)) {
                            $model->isDeleted = (int)$recordData['isDeleted'];
                        }

                        $model->save();

                        $syncedMappings[$tableKey][] = [
                            'local_id' => $recordData['local_id'] ?? null,
                            'remote_id' => $model->id,
                            'client_uuid' => $clientUuid,
                        ];
                    }
                }
            }

            // 12. Handle Offline Deletions (deleted_records and deleted_ids)
            if (!empty($payload['deleted_records'])) {
                foreach ($payload['deleted_records'] as $tableName => $records) {
                    $modelClass = $this->modelMap[$tableName] ?? null;
                    if ($modelClass && is_array($records)) {
                        foreach ($records as $record) {
                            $remoteId = $record['remote_id'] ?? null;
                            $clientUuid = $record['client_uuid'] ?? null;
                            if ($remoteId || $clientUuid) {
                                $query = $modelClass::query();
                                if ($remoteId && $clientUuid) {
                                    $query->where('id', $remoteId)->orWhere('client_uuid', $clientUuid);
                                } elseif ($remoteId) {
                                    $query->where('id', $remoteId);
                                } elseif ($clientUuid) {
                                    $query->where('client_uuid', $clientUuid);
                                }
                                $query->delete();
                            }
                        }
                    }
                }
            }

            if (!empty($payload['deleted_ids']) && is_array($payload['deleted_ids'])) {
                foreach ($payload['deleted_ids'] as $tableName => $ids) {
                    $modelClass = $this->modelMap[$tableName] ?? null;
                    if ($modelClass && is_array($ids)) {
                        $modelClass::whereIn('id', $ids)->delete();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data synchronized successfully.',
                'server_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
                'client' => $this->clientPayloadFromTenant($tenant),
                'tenancy_enabled' => app(TenantManager::class)->enabled(),
                'synced_mappings' => $syncedMappings,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Sync push failed: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    /**
     * Map Flutter Desktop GST / Service Tax fields onto Order (create + update).
     * Desktop may send aliases: service_tax_percent, service_tax_percentage, service_tax_label.
     */
    protected function applyOrderTaxFieldsFromSync(\App\Models\Order $order, array $orderData): void
    {
        $serviceTaxRate = $orderData['service_tax_rate']
            ?? $orderData['service_tax_percent']
            ?? $orderData['service_tax_percentage']
            ?? null;

        $serviceTaxAmount = array_key_exists('service_tax_amount', $orderData)
            ? $orderData['service_tax_amount']
            : null;

        $serviceTaxName = $orderData['service_tax_name']
            ?? $orderData['service_tax_label']
            ?? null;
        if (
            ($serviceTaxName === null || $serviceTaxName === '')
            && $serviceTaxRate !== null
            && (float) $serviceTaxRate > 0
        ) {
            $serviceTaxName = 'Service Tax';
        }

        $taxBreakdown = $orderData['tax_breakdown'] ?? null;
        if (is_string($taxBreakdown) && $taxBreakdown !== '') {
            $decoded = json_decode($taxBreakdown, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $taxBreakdown = $decoded;
            }
        }
        if (empty($taxBreakdown) && $serviceTaxRate !== null && (float) $serviceTaxRate > 0) {
            $taxBreakdown = [
                [
                    'id' => 'service_tax',
                    'name' => $serviceTaxName ?: 'Service Tax',
                    'rate' => (float) $serviceTaxRate,
                    'amount' => round((float) ($serviceTaxAmount ?? 0), 2),
                    'kind' => 'service',
                ],
            ];
        }

        $rawGstOption = strtolower(trim((string) ($orderData['gst_option'] ?? ($order->gst_option ?? 'without_gst'))));
        $gstOption = ($rawGstOption === 'with_gst' || $rawGstOption === 'with')
            ? 'with_gst'
            : 'without_gst';

        $gstApplyMode = strtolower(trim((string) ($orderData['gst_apply_mode'] ?? ($order->gst_apply_mode ?? 'product'))));
        if ($gstApplyMode !== 'overall') {
            $gstApplyMode = 'product';
        }

        $overallGstRate = array_key_exists('overall_gst_rate', $orderData)
            ? $orderData['overall_gst_rate']
            : ($order->overall_gst_rate ?? null);

        // tax_id: Web stores JSON "[]"; Desktop may send "0.00" / numeric GST total — accept safely
        $taxId = $orderData['tax_id'] ?? null;
        if ($taxId === null || $taxId === '' || $taxId === '0.00' || $taxId === 0 || $taxId === 0.0) {
            $taxId = ($gstOption === 'without_gst') ? '[]' : ($order->tax_id ?? '[]');
        } elseif (is_array($taxId)) {
            $taxId = json_encode($taxId);
        }

        if (Schema::hasColumn('orders', 'gst_option')) {
            $order->gst_option = $gstOption;
        }
        if (Schema::hasColumn('orders', 'gst_apply_mode')) {
            $order->gst_apply_mode = $gstApplyMode;
        }
        if (Schema::hasColumn('orders', 'overall_gst_rate')) {
            $order->overall_gst_rate = ($overallGstRate === null || $overallGstRate === '')
                ? null
                : round((float) $overallGstRate, 2);
        }
        if (Schema::hasColumn('orders', 'service_tax_rate')) {
            $order->service_tax_rate = ($serviceTaxRate === null || $serviceTaxRate === '')
                ? null
                : round((float) $serviceTaxRate, 2);
        }
        if (Schema::hasColumn('orders', 'service_tax_amount')) {
            $order->service_tax_amount = ($serviceTaxAmount === null || $serviceTaxAmount === '')
                ? null
                : round((float) $serviceTaxAmount, 2);
        }
        if (Schema::hasColumn('orders', 'service_tax_name')) {
            $order->service_tax_name = $serviceTaxName !== null && $serviceTaxName !== ''
                ? (string) $serviceTaxName
                : null;
        }
        if (Schema::hasColumn('orders', 'tax_breakdown')) {
            $order->tax_breakdown = is_array($taxBreakdown) ? $taxBreakdown : null;
        }
        if (Schema::hasColumn('orders', 'tax_id') && $taxId !== null) {
            $order->tax_id = is_scalar($taxId) ? (string) $taxId : json_encode($taxId);
        }
    }

    /**
     * Resolve category_id for a synced product: valid remote ID → client UUID → name (auto-create).
     */
    protected function resolveSyncedCategoryId(array $data, $branchId = null): ?int
    {
        $categoryId = $data['category_id'] ?? null;
        if ($categoryId && !\App\Models\Category::where('id', $categoryId)->exists()) {
            $categoryId = null; // local/stale desktop ID
        }

        if (!$categoryId && !empty($data['category_client_uuid'])) {
            $cat = \App\Models\Category::where('client_uuid', $data['category_client_uuid'])->first();
            $categoryId = $cat ? (int) $cat->id : null;
        }

        $catName = trim((string) ($data['category_name'] ?? $data['category'] ?? ''));
        if (!$categoryId && $catName !== '') {
            $query = \App\Models\Category::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($catName)]);
            if ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }
            $cat = $query->first();
            if (!$cat) {
                $cat = new \App\Models\Category();
                $cat->name = $catName;
                $cat->branch_id = $branchId;
                if (!empty($data['category_client_uuid'])) {
                    $cat->client_uuid = $data['category_client_uuid'];
                }
                $cat->isDeleted = 0;
                $cat->save();
            } elseif (!empty($data['category_client_uuid']) && empty($cat->client_uuid)) {
                $cat->client_uuid = $data['category_client_uuid'];
                $cat->save();
            }
            $categoryId = (int) $cat->id;
        }

        return $categoryId ? (int) $categoryId : null;
    }

    /**
     * Resolve quantity_type_id: valid remote ID / unit_id → client UUID → name (auto-create).
     */
    protected function resolveSyncedQuantityTypeId(array $data, $branchId = null): ?int
    {
        $quantityTypeId = $data['quantity_type_id'] ?? $data['unit_id'] ?? null;
        if ($quantityTypeId && !\App\Models\QuantityType::where('id', $quantityTypeId)->exists()) {
            $quantityTypeId = null; // local/stale desktop ID
        }

        $qtUuid = $data['quantity_type_client_uuid'] ?? $data['unit_client_uuid'] ?? null;
        if (!$quantityTypeId && !empty($qtUuid)) {
            $qt = \App\Models\QuantityType::where('client_uuid', $qtUuid)->first();
            $quantityTypeId = $qt ? (int) $qt->id : null;
        }

        $qtName = trim((string) ($data['quantity_type'] ?? $data['unit_name'] ?? $data['quantity_type_name'] ?? ''));
        if (!$quantityTypeId && $qtName !== '') {
            $query = \App\Models\QuantityType::query()
                ->whereRaw('LOWER(quantity_type) = ?', [mb_strtolower($qtName)]);
            if ($branchId && $this->fastHasColumn('quantity_types', 'branch_id')) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }
            $qt = $query->first();
            if (!$qt) {
                $qt = new \App\Models\QuantityType();
                $qt->quantity_type = $qtName;
                if ($this->fastHasColumn('quantity_types', 'branch_id')) {
                    $qt->branch_id = $branchId;
                }
                if (!empty($qtUuid)) {
                    $qt->client_uuid = $qtUuid;
                }
                $qt->isDeleted = 0;
                $qt->save();
            } elseif (!empty($qtUuid) && empty($qt->client_uuid)) {
                $qt->client_uuid = $qtUuid;
                $qt->save();
            }
            $quantityTypeId = (int) $qt->id;
        }

        return $quantityTypeId ? (int) $quantityTypeId : null;
    }

    /**
     * Resolve brand_id: valid remote ID → client UUID → name (lookup only, no auto-create).
     */
    protected function resolveSyncedBrandId(array $data, $branchId = null): ?int
    {
        $brandId = $data['brand_id'] ?? null;
        if ($brandId && !\App\Models\Brand::where('id', $brandId)->exists()) {
            $brandId = null;
        }

        if (!$brandId && !empty($data['brand_client_uuid'])) {
            $brand = \App\Models\Brand::where('client_uuid', $data['brand_client_uuid'])->first();
            $brandId = $brand ? (int) $brand->id : null;
        }

        $brandName = trim((string) ($data['brand_name'] ?? $data['brand'] ?? ''));
        if (
            !$brandId
            && $brandName !== ''
            && strcasecmp($brandName, 'N/A') !== 0
            && strcasecmp($brandName, 'All Brands') !== 0
        ) {
            $query = \App\Models\Brand::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($brandName)]);
            if ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }
            $brand = $query->first();
            if ($brand) {
                if (!empty($data['brand_client_uuid']) && empty($brand->client_uuid)) {
                    $brand->client_uuid = $data['brand_client_uuid'];
                    $brand->save();
                }
                $brandId = (int) $brand->id;
            }
        }

        return $brandId ? (int) $brandId : null;
    }

    /**
     * Resolve existing user for sync push (admin/staff/customer) without creating duplicates.
     * Match order: remote_id/id → client_uuid → email → admin role.
     *
     * @param  array<string, mixed>  $u
     */
    protected function resolveSyncedUser(array $u): \App\Models\User
    {
        $user = null;

        $remoteId = $u['remote_id'] ?? null;
        if ($remoteId === null || $remoteId === '' || $remoteId === 0 || $remoteId === '0') {
            // Desktop often sends id=1 for local admin; only trust when remote_id present OR role admin
            if (! empty($u['id']) && (! empty($u['remote_id']) || (($u['role'] ?? '') === 'admin'))) {
                $remoteId = $u['id'];
            }
        }
        if (! empty($remoteId) && is_numeric($remoteId)) {
            $user = \App\Models\User::find((int) $remoteId);
        }

        if (! $user && ! empty($u['client_uuid']) && $this->fastHasColumn('users', 'client_uuid')) {
            $user = \App\Models\User::where('client_uuid', $u['client_uuid'])->first();
        }

        if (! $user && ! empty($u['email'])) {
            $user = \App\Models\User::where('email', $u['email'])->first();
        }

        if (! $user && isset($u['role']) && strtolower((string) $u['role']) === 'admin') {
            $user = \App\Models\User::where('role', 'admin')
                ->where(function ($q) {
                    $q->where('isDeleted', '!=', 1)->orWhereNull('isDeleted');
                })
                ->orderBy('id')
                ->first();
        }

        return $user ?: new \App\Models\User();
    }

    /**
     * Normalize uploaded / pushed image path to a relative public-disk path.
     */
    protected function normalizeSyncedImagePath(string $img): ?string
    {
        $img = trim($img);
        if ($img === '') {
            return null;
        }
        $img = preg_replace('#^public/#', '', $img);
        $img = preg_replace('#^storage/#', '', $img);
        if (preg_match('#/storage/tenants/\d+/app/public/(.+)$#', $img, $m)) {
            $img = $m[1];
        } elseif (preg_match('#/storage/(.+)$#', $img, $m)) {
            $img = $m[1];
        }
        $img = ltrim((string) $img, '/');

        return $img !== '' ? $img : null;
    }

    /**
     * Map Desktop order references to MySQL orders.id for the current push batch.
     *
     * Accepts (in priority order):
     * - local_order_id / order local map from this push's orders[]
     * - order_client_uuid / client_uuid of the parent order
     * - order_id only when it already exists as a MySQL orders.id
     *
     * Returns null when unresolved (safe for nullable FKs). Never returns an unverified local SQLite id.
     *
     * @param  array<string, mixed>  $row
     * @param  array<int|string, int>  $localToRemoteOrderMap
     * @param  array<string, int>  $uuidToRemoteOrderMap
     */
    protected function resolveSyncedOrderId(array $row, array $localToRemoteOrderMap, array $uuidToRemoteOrderMap): ?int
    {
        // Only order-specific local keys (never this row's own local_id — that is the payment/item PK)
        $candidates = [];
        foreach (['local_order_id', 'order_local_id'] as $key) {
            if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                $candidates[] = $row[$key];
            }
        }
        // Desktop often still sends order_id = SQLite orders.id; try batch map first
        if (isset($row['order_id']) && $row['order_id'] !== '' && $row['order_id'] !== null) {
            $candidates[] = $row['order_id'];
        }

        foreach ($candidates as $localKey) {
            if (array_key_exists($localKey, $localToRemoteOrderMap)) {
                return (int) $localToRemoteOrderMap[$localKey];
            }
            $asString = (string) $localKey;
            if (array_key_exists($asString, $localToRemoteOrderMap)) {
                return (int) $localToRemoteOrderMap[$asString];
            }
            if (is_numeric($localKey)) {
                $asInt = (int) $localKey;
                if (array_key_exists($asInt, $localToRemoteOrderMap)) {
                    return (int) $localToRemoteOrderMap[$asInt];
                }
            }
        }

        $orderUuid = $row['order_client_uuid'] ?? $row['order_uuid'] ?? null;
        if (is_string($orderUuid) && $orderUuid !== '') {
            if (isset($uuidToRemoteOrderMap[$orderUuid])) {
                return (int) $uuidToRemoteOrderMap[$orderUuid];
            }
            $existingId = \App\Models\Order::where('client_uuid', $orderUuid)->value('id');
            if ($existingId) {
                return (int) $existingId;
            }
        }

        $rawOrderId = $row['order_id'] ?? $row['remote_order_id'] ?? null;
        if ($rawOrderId !== null && $rawOrderId !== '' && is_numeric($rawOrderId)) {
            $rawOrderId = (int) $rawOrderId;
            if ($rawOrderId > 0 && \App\Models\Order::where('id', $rawOrderId)->exists()) {
                return $rawOrderId;
            }
        }

        return null;
    }

    // =============================================
    // FULL PUSH: POST /api/sync/full-push
    // =============================================
    public function fullPush(Request $request)
    {
        return $this->push($request);
    }

    // =============================================
    // IMAGE UPLOAD: POST /api/v1/sync/upload-image
    // Supports folder=profile_images|img/product|...
    // Files land in tenant public disk: storage/tenants/{id}/app/public/{folder}/
    // =============================================
    public function uploadImage(Request $request)
    {
        try {
            $this->resolveTenantIfNeeded($request);
        } catch (TenantResolutionException $e) {
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->reason,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Throwable) {
            // Tenancy off / optional license on local
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'folder' => 'nullable|string|max:100',
        ]);

        if (! $request->hasFile('image')) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'No image uploaded',
            ], 400);
        }

        $folder = trim((string) ($request->input('folder') ?: 'img/product'), '/');
        // Allow only safe relative folders (no traversal)
        $folder = str_replace('\\', '/', $folder);
        $folder = preg_replace('#\.\.+#', '', $folder);
        $allowed = [
            'profile_images',
            'img/product',
            'img/category',
            'img/brand',
            'img/sync',
            'products',
            'categories',
        ];
        $folderLower = strtolower($folder);
        if (! in_array($folderLower, $allowed, true) && ! str_starts_with($folderLower, 'img/')) {
            $folder = 'profile_images';
        }

        $file = $request->file('image');
        $fileName = Str::random(40) . '.' . strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $imagePath = $file->storeAs($folder, $fileName, 'public');
        $fullUrl = function_exists('tenant_image_url') ? tenant_image_url($imagePath) : asset('storage/' . $imagePath);

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'path' => $imagePath,
            'url' => $fullUrl,
            'data' => [
                'path' => $imagePath,
                'relative_path' => $imagePath,
                'url' => $fullUrl,
                'folder' => $folder,
            ],
        ], 200);
    }

    // =============================================
    // BATCH IMAGES: POST /api/sync/images-batch
    // =============================================
    public function imagesBatch(Request $request)
    {
        $uploaded = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('img/sync', 'public');
                $fullUrl = function_exists('tenant_image_url') ? tenant_image_url($path) : asset('storage/' . $path);
                $uploaded[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => $fullUrl,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'uploaded' => $uploaded,
        ], 200);
    }
}
