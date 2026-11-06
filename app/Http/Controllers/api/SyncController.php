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
     * Resolve and activate tenant database using license_key if multi-tenancy is active.
     */
    protected function resolveTenantIfNeeded(Request $request): ?array
    {
        $tenancy = app(TenantManager::class);
        if (!$tenancy->enabled()) {
            return null;
        }

        $licenseKey = $request->header('X-License-Key')
            ?? $request->header('X-Tenant-Key')
            ?? $request->input('license_key')
            ?? $request->input('tenant_license');

        if (!empty($licenseKey)) {
            return $tenancy->resolveByLicenseKey($licenseKey);
        }

        return $tenancy->current();
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
    // VERIFY LICENSE: POST /api/v1/sync/verify-license
    // =============================================
    public function verifyLicense(Request $request)
    {
        $licenseKey = $request->header('X-License-Key')
            ?? $request->input('license_key')
            ?? $request->input('unique_id');

        if (empty($licenseKey)) {
            return response()->json([
                'status' => false,
                'message' => 'License key is required.',
            ], 422);
        }

        $tenancy = app(TenantManager::class);
        if ($tenancy->enabled()) {
            try {
                $tenant = $tenancy->resolveByLicenseKey($licenseKey, true);
                return response()->json([
                    'status' => true,
                    'message' => 'License is active and valid.',
                    'client' => [
                        'id' => $tenant['client_id'] ?? $tenant['id'] ?? null,
                        'client_name' => $tenant['client_name'] ?? null,
                        'brand_name' => $tenant['brand_name'] ?? null,
                        'domain' => $tenant['domain'] ?? null,
                        'plan_name' => $tenant['plan_name'] ?? 'Standard',
                        'status' => $tenant['status'] ?? 'Active',
                        'expiry_date' => $tenant['expiry_date'] ?? null,
                        'max_user' => $tenant['max_user'] ?? 10,
                        'max_branch' => $tenant['max_branch'] ?? 1,
                    ],
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
        ]);

        try {
            $this->resolveTenantIfNeeded($request);
        } catch (TenantResolutionException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->reason,
                'message' => $e->getMessage(),
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
            'server_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
        ], 200);
    }

    // =============================================
    // DELTA PULL: POST /api/v1/sync/pull
    // Delta Pull (Server -> Flutter Drift DB)
    // =============================================
    public function pull(Request $request)
    {
        try {
            $this->resolveTenantIfNeeded($request);
        } catch (TenantResolutionException $e) {
            return response()->json(['status' => false, 'error' => $e->reason, 'message' => $e->getMessage()], 403);
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
        try {
            $this->resolveTenantIfNeeded($request);
        } catch (TenantResolutionException $e) {
            return response()->json(['status' => false, 'error' => $e->reason, 'message' => $e->getMessage()], 403);
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

        try {
            DB::beginTransaction();

            // 1. Users (Customers, Waiters, Staff)
            if (!empty($payload['users'])) {
                foreach ($payload['users'] as $u) {
                    $uuid = $u['client_uuid'] ?? (string) Str::uuid();
                    $user = \App\Models\User::firstOrNew(['client_uuid' => $uuid]);

                    $user->name = $u['name'] ?? 'Guest Customer';
                    $user->email = !empty($u['email']) ? $u['email'] : null;
                    $user->phone = $u['phone'] ?? null;
                    $user->role = $u['role'] ?? 'customer';
                    $user->branch_id = $u['branch_id'] ?? $branchId;
                    if (!empty($u['password'])) {
                        $user->password = Hash::make($u['password']);
                    }
                    if (isset($u['isDeleted'])) {
                        $user->isDeleted = (int)$u['isDeleted'];
                    }
                    $user->client_uuid = $uuid;
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
                        'client_uuid' => $uuid,
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
                    if (!empty($t['order_client_uuid'])) {
                        $ord = \App\Models\Order::where('client_uuid', $t['order_client_uuid'])->first();
                        $table->order_id = $ord ? $ord->id : null;
                    } elseif (isset($t['order_id'])) {
                        $table->order_id = $t['order_id'];
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

                    $orderId = $it['order_id'] ?? null;
                    $parentOrd = null;
                    if (!$orderId && !empty($it['order_client_uuid'])) {
                        $parentOrd = \App\Models\Order::where('client_uuid', $it['order_client_uuid'])->first();
                        $orderId = $parentOrd ? $parentOrd->id : null;
                    } elseif ($orderId) {
                        $parentOrd = \App\Models\Order::find($orderId);
                    }

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

                    $orderItem->order_id = $orderId ?? 1;
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

                    // 2. Resolve Order ID
                    $orderId = $p['order_id'] ?? null;
                    $ord = null;
                    if (!$orderId && !empty($p['order_client_uuid'])) {
                        $ord = \App\Models\Order::where('client_uuid', $p['order_client_uuid'])->first();
                        $orderId = $ord ? $ord->id : null;
                    } elseif ($orderId) {
                        $ord = \App\Models\Order::find($orderId);
                    }

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

    // =============================================
    // FULL PUSH: POST /api/sync/full-push
    // =============================================
    public function fullPush(Request $request)
    {
        return $this->push($request);
    }

    // =============================================
    // IMAGE UPLOAD: POST /api/v1/sync/upload-image
    // =============================================
    public function uploadImage(Request $request)
    {
        try {
            $this->resolveTenantIfNeeded($request);
        } catch (\Throwable $e) {
            // Proceed with upload
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('img/product', 'public');
            $fullUrl = function_exists('tenant_image_url') ? tenant_image_url($imagePath) : asset('storage/' . $imagePath);
            return response()->json([
                'status' => 'success',
                'path' => $imagePath,
                'url' => $fullUrl,
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No image uploaded',
        ], 400);
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
