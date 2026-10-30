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

            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $query = $modelClass::query();

            $hasUpdated = Schema::hasColumn($tableName, 'updated_at');
            $hasIsDeleted = Schema::hasColumn($tableName, 'isDeleted');
            $hasIsDelete = Schema::hasColumn($tableName, 'is_delete');
            $hasSoftDeletes = Schema::hasColumn($tableName, 'deleted_at');
            $hasBranch = Schema::hasColumn($tableName, 'branch_id');

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
                $query->with(['order_items', 'user', 'payments']);
            }
            if ($tableKey === 'table_no') {
                $query->with(['order']);
            }
            if ($tableKey === 'table_reservations') {
                $query->with(['table', 'order']);
            }

            $records = $query->get();

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

            // 4. Quantity Types (Units)
            if (!empty($payload['quantity_types'])) {
                foreach ($payload['quantity_types'] as $qt) {
                    $uuid = $qt['client_uuid'] ?? (string) Str::uuid();
                    $unit = \App\Models\QuantityType::firstOrNew(['client_uuid' => $uuid]);
                    $unit->type = $qt['type'] ?? ($qt['name'] ?? 'Plate');
                    $unit->branch_id = $qt['branch_id'] ?? $branchId;
                    if (isset($qt['isDeleted'])) {
                        $unit->isDeleted = (int)$qt['isDeleted'];
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

            // 5. Restaurant Tables (table_no)
            if (!empty($payload['table_no'])) {
                foreach ($payload['table_no'] as $t) {
                    $uuid = $t['client_uuid'] ?? (string) Str::uuid();
                    $table = \App\Models\Table_no::firstOrNew(['client_uuid' => $uuid]);
                    $table->table_name = $t['table_name'];
                    $table->table_type = $t['table_type'] ?? 'Dine-In';
                    $table->table_status = $t['table_status'] ?? 0;
                    $table->status = $t['status'] ?? 'blank';
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
            if (!empty($payload['products'])) {
                foreach ($payload['products'] as $p) {
                    $uuid = $p['client_uuid'] ?? (string) Str::uuid();
                    $product = \App\Models\Product::firstOrNew(['client_uuid' => $uuid]);

                    // Resolve foreign keys
                    $categoryId = $p['category_id'] ?? null;
                    if (!$categoryId && !empty($p['category_client_uuid'])) {
                        $cat = \App\Models\Category::where('client_uuid', $p['category_client_uuid'])->first();
                        $categoryId = $cat ? $cat->id : null;
                    }

                    $brandId = $p['brand_id'] ?? null;
                    if (!$brandId && !empty($p['brand_client_uuid'])) {
                        $brand = \App\Models\Brand::where('client_uuid', $p['brand_client_uuid'])->first();
                        $brandId = $brand ? $brand->id : null;
                    }

                    $unitId = $p['quantity_type_id'] ?? null;
                    if (!$unitId && !empty($p['quantity_type_client_uuid'])) {
                        $qt = \App\Models\QuantityType::where('client_uuid', $p['quantity_type_client_uuid'])->first();
                        $unitId = $qt ? $qt->id : null;
                    }

                    $product->name = $p['name'];
                    $product->SKU = $p['sku'] ?? $p['SKU'] ?? null;
                    $product->barcode = $p['barcode'] ?? null;
                    $product->description = $p['description'] ?? null;
                    $product->price = $p['price'] ?? 0;
                    $product->quantity = $p['quantity'] ?? 0;
                    $product->status = $p['status'] ?? 'active';
                    $product->availablility = $p['availablility'] ?? 'in_stock';
                    $product->category_id = $categoryId;
                    $product->brand_id = $brandId;
                    $product->quantity_type_id = $unitId;
                    $product->branch_id = $p['branch_id'] ?? $branchId;

                    if (!empty($p['images'])) {
                        $product->images = is_array($p['images']) ? json_encode($p['images']) : $p['images'];
                    }

                    if (isset($p['isDeleted'])) {
                        $product->isDeleted = (int)$p['isDeleted'];
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
                    $userId = $o['user_id'] ?? null;
                    if (!empty($o['user_client_uuid'])) {
                        $user = \App\Models\User::where('client_uuid', $o['user_client_uuid'])->first();
                        $userId = $user ? $user->id : null;
                    }

                    // Avoid duplicate order number conflicts
                    $orderNumber = $o['order_number'] ?? null;
                    if ($orderNumber) {
                        $existing = \App\Models\Order::where('order_number', $orderNumber)->first();
                        if ($existing && $existing->client_uuid !== $orderUuid) {
                            $orderNumber = $orderNumber . '-' . strtoupper(Str::random(4));
                        }
                    }

                    $order->order_number = $orderNumber;
                    $order->user_id = $userId;
                    $order->customer_name = $o['customer_name'] ?? null;
                    $order->customer_phone = $o['customer_phone'] ?? null;
                    $order->table_no = $o['table_no'] ?? null;
                    $order->order_type = $o['order_type'] ?? 'Dine-In';
                    $order->type = $o['type'] ?? 'POS';
                    $order->discount = $o['discount'] ?? 0;
                    $order->total_amount = $o['total_amount'] ?? 0;
                    $order->remaining_amount = $o['remaining_amount'] ?? 0;
                    $order->payment_status = $o['payment_status'] ?? 'completed';
                    $order->payment_method = $o['payment_method'] ?? 'cash';
                    $order->delivery_status = $o['delivery_status'] ?? null;
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

                        // Delete existing items if updating
                        \App\Models\OrderItem::where('order_id', $order->id)->delete();

                        foreach ($itemsList as $it) {
                            $itemUuid = $it['client_uuid'] ?? (string) Str::uuid();

                            // Resolve product_id
                            $productId = $it['product_id'] ?? null;
                            if (!$productId && !empty($it['product_client_uuid'])) {
                                $prd = \App\Models\Product::where('client_uuid', $it['product_client_uuid'])->first();
                                $productId = $prd ? $prd->id : null;
                            }

                            $orderItem = new \App\Models\OrderItem();
                            $orderItem->order_id = $order->id;
                            $orderItem->product_id = $productId;
                            $orderItem->quantity = $it['quantity'] ?? 1;
                            $orderItem->price = $it['price'] ?? 0;
                            $orderItem->total_amount = $it['total_amount'] ?? (($it['quantity'] ?? 1) * ($it['price'] ?? 0));
                            $orderItem->category_id = $it['category_id'] ?? null;
                            $orderItem->quantity_type_id = $it['quantity_type_id'] ?? null;
                            $orderItem->branch_id = $o['branch_id'] ?? $branchId;
                            $orderItem->client_uuid = $itemUuid;
                            $orderItem->save();

                            $itemMappings[] = [
                                'local_id' => $it['local_id'] ?? null,
                                'remote_id' => $orderItem->id,
                                'client_uuid' => $itemUuid,
                            ];
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

            // 8. Payment Store
            if (!empty($payload['payment_store'])) {
                foreach ($payload['payment_store'] as $p) {
                    $uuid = $p['client_uuid'] ?? (string) Str::uuid();
                    $payment = \App\Models\PaymentStore::firstOrNew(['client_uuid' => $uuid]);

                    $orderId = $p['order_id'] ?? null;
                    if (!$orderId && !empty($p['order_client_uuid'])) {
                        $ord = \App\Models\Order::where('client_uuid', $p['order_client_uuid'])->first();
                        $orderId = $ord ? $ord->id : null;
                    }

                    $payment->order_id = $orderId;
                    $payment->amount = $p['amount'] ?? 0;
                    $payment->payment_method = $p['payment_method'] ?? 'cash';
                    $payment->status = $p['status'] ?? 'completed';
                    $payment->branch_id = $p['branch_id'] ?? $branchId;
                    $payment->client_uuid = $uuid;
                    $payment->save();

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
            return response()->json([
                'status' => 'success',
                'path' => $imagePath,
                'url' => asset('storage/' . $imagePath),
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
                $uploaded[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'uploaded' => $uploaded,
        ], 200);
    }
}
