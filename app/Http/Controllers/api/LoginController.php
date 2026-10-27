<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomInvoice;
use App\Models\LogAttendance;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{PurchaseInvoice, Order, Expense, Product};
use App\Models\FaceLoginAudit;
use App\Services\FaceRecognitionService;
use App\Services\AttendanceLocationService;
use App\Exceptions\TenantResolutionException;
use App\Services\Tenancy\TenantManager;
use App\Support\AppViewData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|string|email',
    //         'password' => 'required|string',
    //     ]);

    //     if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
    //         $user = Auth::user();

    //         $token = $user->createToken('LaravelPassportToken')->accessToken;

    //         return response()->json(['status' => true,'token' => $token], 200);
    //     } else {
    //         return response()->json(['status' => false,'error' => 'Login Credential Wrong'], 401);
    //     }
    // }
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'nullable|string',
            'email'    => 'nullable|string',
            'password' => 'required|string',
        ]);

        $loginIdentifier = trim((string)($request->input('username') ?? $request->input('email') ?? ''));
        if (empty($loginIdentifier)) {
            return response()->json([
                'status' => false,
                'error'  => 'Username or email is required.',
            ], 422);
        }

        // Auto-ensure username column exists in users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('username')->nullable()->after('name');
            });
        }

        if ($denied = $this->ensureTenantAllowsLogin($request)) {
            return $denied;
        }

        $force = filter_var($request->input('force', false), FILTER_VALIDATE_BOOLEAN);

        $authResult = $this->authenticateByEmailPassword($loginIdentifier, $request->password);
        if ($authResult['error']) {
            $statusCode = $authResult['error'] === 'Wrong Login credentials entered.' ? 401 : 403;

            return response()->json([
                'status' => false,
                'error'  => $authResult['error'],
            ], $statusCode);
        }

        $user = $authResult['user'];

        if ($request->hasSession()) {
            $tenancy = app(TenantManager::class);
            if ($tenancy->enabled() && $tenancy->current()) {
                $request->session()->put('tenant_id', $tenancy->current()['id']);
                $request->session()->put('tenant', $tenancy->current());
            }
            Auth::login($user);
            AppViewData::share();

            // If sub-admin (sub-branch) or staff with branch_id, initialize selectedSubAdminId
            if ($user->role === 'sub-admin') {
                $request->session()->put('selectedSubAdminId', $user->id);
            } elseif ($user->role === 'staff' && !empty($user->branch_id)) {
                $request->session()->put('selectedSubAdminId', $user->branch_id);
            }
        }

        $token = $user->createToken('LaravelPassportToken')->accessToken;

        // Get permissions
        $permissions = $user->permissions()->with('module')->get();
        $formattedPermissions = [];
        foreach ($permissions as $permission) {
            $moduleName = $permission->module->module ?? 'Unknown';
            $formattedPermissions[$moduleName] = [
                'id' => $permission->id,
                'user_id' => $permission->user_id,
                'module_id' => $permission->module_id,
                'view' => $permission->view,
                'create' => $permission->add,
                'update' => $permission->edit,
                'delete' => $permission->delete,
                'created_at' => $permission->created_at,
            ];
        }

        $redirect = route('auth.dashboard');

        if (strtolower($user->role) === 'staff') {
            $redirect = route('auth.staff-dashboard');

            return response()->json([
                'status' => true,
                'token' => $token,
                'user' => $user,
                'redirect' => $redirect,
                'permissions' => $formattedPermissions,
                'showAppointments' => false,
            ]);
        } elseif (strtolower($user->role) === 'hr') {
            $redirect = route('hr.dashboard');

            return response()->json([
                'status' => true,
                'token' => $token,
                'user' => $user,
                'redirect' => $redirect,
                'permissions' => $formattedPermissions,
                'showAppointments' => false,
            ]);
        }

        // Return response for non-staff logins (admin, sub-admin, etc.)
        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => $user,
            'redirect' => $redirect,
            'permissions' => $formattedPermissions,
            'showAppointments' => true,
        ]);
    }

    /**
     * Authenticate by username/email/password.
     */
    protected function authenticateByEmailPassword(string $login, string $password): array
    {
        $cleanLogin = strtolower(trim($login));
        $candidates = User::where(function ($q) use ($cleanLogin) {
            if (Schema::hasColumn('users', 'username')) {
                $q->whereRaw('LOWER(TRIM(username)) = ?', [$cleanLogin])
                  ->orWhereRaw('LOWER(TRIM(email)) = ?', [$cleanLogin]);
                if (!str_contains($cleanLogin, '_')) {
                    $q->orWhereRaw('LOWER(TRIM(username)) LIKE ?', ['%_' . $cleanLogin]);
                }
            } else {
                $q->whereRaw('LOWER(TRIM(email)) = ?', [$cleanLogin]);
            }
        })
        ->where('isDeleted', 0)
        ->latest('id')
        ->get();

        foreach ($candidates as $user) {
            $matched = Hash::check($password, $user->password);
            if (! $matched && $user->password === $password) {
                $matched = true;
                $user->password = Hash::make($password);
                $user->save();
            }

            if (! $matched) {
                continue;
            }

            if ((int) ($user->status ?? 1) === 0) {
                return ['user' => null, 'error' => 'Your account has been deactivated. Please contact the administrator.'];
            }

            return ['user' => $user, 'error' => null];
        }

        return ['user' => null, 'error' => 'Wrong Login credentials entered.'];
    }

    /**
     * Ensures the tenant allows login via central admin_erp.
     */
    protected function ensureTenantAllowsLogin(Request $request): ?\Illuminate\Http\JsonResponse
    {
        $tenancy = app(TenantManager::class);
        if (! $tenancy->enabled()) {
            return null;
        }

        $login = trim((string)($request->input('username') ?? $request->input('email') ?? ''));

        $buildTenantErrorResponse = function (TenantResolutionException $ex) {
            $ctx = $ex->context ?? [];
            $isExpired = in_array(strtoupper((string) $ex->reason), ['EXPIRED', 'INACTIVE'], true)
                || str_contains(strtolower($ex->getMessage()), 'expired');
            $rawDate = $ctx['end_date'] ?? ($ctx['expiry_date'] ?? null);
            $formattedDate = $rawDate;
            if ($rawDate && $rawDate !== 'N/A') {
                try {
                    $formattedDate = \Carbon\Carbon::parse($rawDate)->format('d-m-Y');
                } catch (\Throwable) {}
            }

            return response()->json([
                'status'       => false,
                'is_expired'   => $isExpired,
                'tenant_error' => $ex->reason,
                'error'        => $ex->getMessage(),
                'plan_name'    => $ctx['plan_name'] ?? 'Starter',
                'end_date'     => $formattedDate ?: 'N/A',
                'expiry_date'  => $formattedDate ?: 'N/A',
            ], in_array($ex->status, [403, 404, 409, 503], true) ? $ex->status : 403);
        };

        // On central/localhost domain, identify tenant directly by user credentials
        if (\App\Http\Middleware\IdentifyTenant::isCentralHost($request->getHost())) {
            if (!empty($login)) {
                try {
                    $tenant = $tenancy->resolveByEmail($login, true);
                    if ($request->hasSession()) {
                        $request->session()->put('tenant_id', $tenant['id']);
                        $request->session()->put('tenant', $tenant);
                    }
                    return null;
                } catch (TenantResolutionException $userTenantError) {
                    return $buildTenantErrorResponse($userTenantError);
                }
            }
            return null;
        }

        try {
            // Check if request host matches a specific tenant subdomain
            $tenancy->resolveByHost($request->getHost(), true);
            return null;
        } catch (TenantResolutionException $e) {
            // If domain not registered, try resolving tenant by user's username or email
            if ($e->reason === 'NOT_FOUND' && !empty($login)) {
                try {
                    $tenant = $tenancy->resolveByEmail($login, true);
                    if ($request->hasSession()) {
                        $request->session()->put('tenant_id', $tenant['id']);
                        $request->session()->put('tenant', $tenant);
                    }
                    return null;
                } catch (TenantResolutionException $userTenantError) {
                    return $buildTenantErrorResponse($userTenantError);
                }
            }

            return $buildTenantErrorResponse($e);
        }
    }

    protected function extractLoginLocation(Request $request): array
    {
        $latitude = $request->input('latitude', $request->input('check_in_latitude'));
        $longitude = $request->input('longitude', $request->input('check_in_longitude'));
        $locationName = $request->input('location_name', $request->input('check_in_location_name'));

        return [
            'latitude' => is_numeric($latitude) ? (float) $latitude : null,
            'longitude' => is_numeric($longitude) ? (float) $longitude : null,
            'location_name' => is_string($locationName) && trim($locationName) !== '' ? trim($locationName) : null,
            'ip_address' => $request->ip(),
        ];
    }

    // public function login(Request $request)

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|string|email',
    //         'password' => 'required|string',
    //     ]);

    //     if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
    //         $user = Auth::user();

    //         $token = $user->createToken('LaravelPassportToken')->accessToken;

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Login successful',
    //             'token'   => $token,
    //             'user'    => [
    //                 'id'    => $user->id,
    //                 'name'  => $user->name,
    //                 'email' => $user->email,
    //                 'role'  => $user->role ?? null, // if you have role column
    //             ]
    //         ], 200);
    //     } else {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Invalid login credentials'
    //         ], 401);
    //     }
    // }

    // Logout User
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->role === 'staff') {
            $today = now('Asia/Kolkata')->format('Y-m-d');
            $time = now('Asia/Kolkata')->format('H:i:s');

            // ✅ Update the last user_attendance record for this user (if not checked out yet)
            $lastAttendance = LogAttendance::where('user_id', $user->id)
                ->where('check_date', $today)
                ->whereNull('checkout_out')
                ->latest('id')
                ->first();

            if ($lastAttendance) {
                $lastAttendance->update([
                    'checkout_out' => $time,
                ]);
            }

            // ✅ Update today's main attendance record
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();

            if ($attendance) {
                $attendance->update([
                    'check_out_time' => $time,
                ]);
                try {
                    \App\Models\Notification::create([
                        'user_id' => $user->id,
                        'type' => 'attendance',
                        'title' => 'Staff Checked Out',
                        'message' => $user->name . ' has checked out.',
                        'link' => '/staff/attendance/summary',
                        'branch_id' => $user->branch_id ?? '0',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Check-out notification failed: ' . $e->getMessage());
                }
            }
        }

        // ✅ Standard logout process
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function logoutapi(Request $request)
    {
        $user = $request->user('api');

        if ($user && $user->role === 'staff') {
            $today = now('Asia/Kolkata')->format('Y-m-d');
            $time = now('Asia/Kolkata')->format('H:i:s');

            // ✅ Update last login record in user_attendance
            $lastAttendance = LogAttendance::where('user_id', $user->id)
                ->where('check_date', $today)
                ->whereNull('checkout_out')
                ->latest('id')
                ->first();

            if ($lastAttendance) {
                $lastAttendance->update([
                    'checkout_out' => $time,
                ]);
            }

            // ✅ Update today's main attendance record
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();

            if ($attendance) {
                $attendance->update([
                    'check_out_time' => $time,
                ]);
                try {
                    \App\Models\Notification::create([
                        'user_id' => $user->id,
                        'type' => 'attendance',
                        'title' => 'Staff Checked Out',
                        'message' => $user->name . ' has checked out.',
                        'link' => '/staff/attendance/summary',
                        'branch_id' => $user->branch_id ?? '0',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Check-out api notification failed: ' . $e->getMessage());
                }
            }
        }

        // ✅ Revoke access token (Passport)
        $request->user('api')->token()->revoke();

        return response()->json([
            'status' => true,
            'message' => 'Logout Successfully',
        ], 200);
    }


    public function dashboardApi(Request $request)
    {
        // $user = auth()->user();
        // // dd($user);
        // $selectedSubAdminId = session('selectedSubAdminId');

        //   // ✅ Decide BranchID based on role/session
        // if (!empty($selectedSubAdminId)) {
        //     $BranchID = $selectedSubAdminId;
        // } elseif ($user->role === 'staff' && $user->branch_id) {
        //     $BranchID = $user->branch_id;
        // } else {
        //     $BranchID = $user->id;
        // }
        $user = Auth::guard('api')->user();
        $role = $user->role;

        // Default branch is the logged-in user's branch
        $BranchID = $user->id;

        // Admin can override with selected sub-admin
        $selectedSubAdminId = $request->query('selectedSubAdminId');

        if ($role === 'admin' && !empty($selectedSubAdminId)) {
            $subAdmin = User::where('id', $selectedSubAdminId)
                ->where('role', 'sub-admin')
                ->first();

            if ($subAdmin) {
                $BranchID = $subAdmin->id; // ✅ use branch_id, not id
            }
        } elseif ($role === 'sub-admin') {
            // Sub-admin: only their branch
            $BranchID = $user->id;
        } elseif ($role === 'staff') {
            // Staff: only their branch
            $BranchID = $user->id;
        }
        //  dd($BranchID);
        // dd($selectedSubAdminId);

        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;
        $currentMonth = Carbon::now()->month;

        // ✅ Charts
        $salesChartThisMonth = $this->getSalesDataByMonth($currentMonth, $BranchID);
        $purchaseChartThisMonth = $this->getPurchaseDataByMonth($currentMonth, $BranchID);

        $salesChartThisYear = $this->getSalesDataByYear($currentYear, $BranchID);
        $salesChartPreviousYear = $this->getSalesDataByYear($previousYear, $BranchID);

        $purchaseChartThisYear = $this->getPurchaseDataByYear($currentYear, $BranchID);
        $purchaseChartPreviousYear = $this->getPurchaseDataByYear($previousYear, $BranchID);

        // ✅ Totals
        $totalPurchaseAmount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('grand_total');

        $totalSalesAmount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('total_amount');

        $totalExpenseAmount = Expense::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('amount');


        $totalInvoiceAmount = CustomInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('grand_total');

        // ✅ Counts
        $customerCount = User::where('role', 'customer')
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();


        $vendorCount = User::where('role', 'vendor')
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        $purchaseInvoiceCount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        $salesInvoiceCount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        // ✅ Recent Products
        $recentProducts = Product::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->latest()
            ->take(4)
            ->get();

        // ✅ Latest Sales
        $latestSales = DB::table('order_items')
            ->join('products', function ($join) {
                $join->on('order_items.product_id', '=', 'products.id')
                    ->where('products.isDeleted', '!=', 1);
            })
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', function ($join) use ($BranchID) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.isDeleted', '!=', 1)
                    ->where('orders.branch_id', '=', $BranchID);
            })
            ->where('order_items.isDeleted', '!=', 1)
            ->select(
                'order_items.*',
                'products.id as product_id',
                'products.SKU as product_code',
                'products.name as product_name',
                'products.images',
                'categories.name as category_name',
                'orders.id as order_id',
                'orders.created_at as order_date',
                'orders.total_amount',
                'orders.payment_method',
                'orders.order_number'
            )
            ->orderBy('order_items.created_at', 'desc')
            ->limit(4)
            ->get();

        // ✅ Append image_url to each item
        $basePath = env('ImagePath', '/');

        $latestSales = $latestSales->map(function ($item) use ($basePath) {
            $decoded = json_decode($item->images, true);

            if (is_array($decoded)) {
                $item->image_url = array_map(function ($img) use ($basePath) {
                    return url($basePath . 'storage/' . $img);
                }, $decoded);
            } elseif ($item->images) {
                $item->image_url = [url($basePath . 'storage/' . $item->images)];
            } else {
                $item->image_url = [url($basePath . 'admin/assets/img/product/noimage.png')];
            }

            return $item;
        });

        // ✅ Latest Purchases
        $latestPurchases = DB::table('purchases as pur')
            ->leftJoin('products as pr', function ($join) {
                $join->on('pr.id', '=', 'pur.item')
                    ->where('pr.isDeleted', '!=', 1);
            })
            ->leftJoin('purchase_invoice as pi', 'pi.id', '=', 'pur.invoice_id')
            ->leftJoin('categories as cat', 'pr.category_id', '=', 'cat.id')
            ->where('pur.isDeleted', '!=', 1)
            ->where('pi.isDeleted', '!=', 1)
            ->where('pi.branch_id', '=', $BranchID)
            ->select(
                'pi.id as invoice_id',
                'pur.created_at as purchase_date',
                'pr.id as product_id',
                'pr.name as product_name',
                'pr.SKU as product_code',
                'pr.images',
                'cat.name as category_name',
                'pur.amount_total',
                'pi.invoice_number',
                'pi.grand_total'
            )
            ->orderBy('pur.created_at', 'desc')
            ->limit(4)
            ->get();
            
        // ✅ Append image_url to each purchase record
        $basePath = env('ImagePath', '/');

        $latestPurchases = $latestPurchases->map(function ($item) use ($basePath) {
            $decoded = json_decode($item->images, true);

            if (is_array($decoded)) {
                $item->image_url = array_map(function ($img) use ($basePath) {
                    return url($basePath . 'storage/' . $img);
                }, $decoded);
            } elseif ($item->images) {
                $item->image_url = [url($basePath . 'storage/' . $item->images)];
            } else {
                $item->image_url = [url($basePath . 'admin/assets/img/product/noimage.png')];
            }

            return $item;
        });
        // ✅ Monthly Sales Chart
        $salesData = DB::table('order_items')
            ->join('orders', function ($join) use ($BranchID) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.isDeleted', '!=', 1)
                    ->where('orders.branch_id', '=', $BranchID);
            })
            ->where('order_items.isDeleted', '!=', 1)
            ->select(DB::raw("MONTH(order_items.created_at) as month"), DB::raw("SUM(order_items.total_amount) as total"))
            ->groupBy(DB::raw("MONTH(order_items.created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $purchasesData = DB::table('purchases')
            ->leftJoin('purchase_invoice', function ($join) use ($BranchID) {
                $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                    ->where('purchase_invoice.isDeleted', '!=', 1)
                    ->where('purchase_invoice.branch_id', '=', $BranchID);
            })
            ->where('purchases.isDeleted', '!=', 1)
            ->select(DB::raw("MONTH(purchases.created_at) as month"), DB::raw("SUM(purchases.amount_total) as total"))
            ->groupBy(DB::raw("MONTH(purchases.created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $salesChart = [];
        $purchaseChart = [];
        for ($m = 1; $m <= 12; $m++) {
            $salesChart[] = (float) ($salesData[$m] ?? 0);
            $purchaseChart[] = (float) ($purchasesData[$m] ?? 0);
        }

        // ✅ Settings
        $settings = DB::table('settings')->where('branch_id', $BranchID)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return response()->json([
            'status' => true,
            'branch_id' => $BranchID,
            'data' => [
                'totals' => [
                    'purchase' => $totalPurchaseAmount,
                    'sales' => $totalSalesAmount,
                    'expense' => $totalExpenseAmount,
                ],
                'counts' => [
                    'customers' => $customerCount,
                    'vendors' => $vendorCount,
                    'purchaseInvoices' => $purchaseInvoiceCount,
                    'salesInvoices' => $salesInvoiceCount,
                ],
                'recentProducts' => $recentProducts,
                'latestSales' => $latestSales,
                'latestPurchases' => $latestPurchases,
                'charts' => [
                    'sales' => $salesChart,
                    'purchases' => $purchaseChart,
                    'salesThisYear' => $salesChartThisYear,
                    'salesPreviousYear' => $salesChartPreviousYear,
                    'purchaseThisYear' => $purchaseChartThisYear,
                    'purchasePreviousYear' => $purchaseChartPreviousYear,
                    'salesThisMonth' => $salesChartThisMonth,
                    'purchaseThisMonth' => $purchaseChartThisMonth,
                ],
                'currency' => [
                    'symbol' => $currencySymbol,
                    'position' => $currencyPosition,
                ],
            ]
        ], 200);
    }
    private function getSalesDataByMonth($month, $BranchID)
    {
        $salesData = DB::table('order_items')
            ->join('orders', function ($join) use ($BranchID) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.isDeleted', '!=', 1)
                    ->where('orders.branch_id', '=', $BranchID);
            })
            ->whereMonth('orders.created_at', $month)
            ->where('order_items.isDeleted', '!=', 1)
            ->select(
                DB::raw("DAY(orders.created_at) as day"),
                DB::raw("SUM(order_items.total_amount) as total")
            )
            ->groupBy(DB::raw("DAY(orders.created_at)"))
            ->pluck('total', 'day')
            ->toArray();

        $chartData = [];
        for ($d = 1; $d <= 31; $d++) {
            $chartData[] = (float) ($salesData[$d] ?? 0);
        }
        return $chartData;
    }

    private function getPurchaseDataByMonth($month, $BranchID)
    {
        $purchaseData = DB::table('purchases')
            ->join('purchase_invoice', function ($join) use ($BranchID) {
                $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                    ->where('purchase_invoice.isDeleted', '!=', 1)
                    ->where('purchase_invoice.branch_id', '=', $BranchID);
            })
            ->whereMonth('purchases.created_at', $month)
            ->where('purchases.isDeleted', '!=', 1)
            ->select(
                DB::raw("DAY(purchases.created_at) as day"),
                DB::raw("SUM(purchases.amount_total) as total")
            )
            ->groupBy(DB::raw("DAY(purchases.created_at)"))
            ->pluck('total', 'day')
            ->toArray();

        $chartData = [];
        for ($d = 1; $d <= 31; $d++) {
            $chartData[] = (float) ($purchaseData[$d] ?? 0);
        }
        return $chartData;
    }

    private function getSalesDataByYear($year, $BranchID)
    {
        $salesData = DB::table('order_items')
            ->join('orders', function ($join) use ($BranchID) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.isDeleted', '!=', 1)
                    ->where('orders.branch_id', '=', $BranchID);
            })
            ->whereYear('orders.created_at', $year)
            ->where('order_items.isDeleted', '!=', 1)
            ->select(
                DB::raw("MONTH(orders.created_at) as month"),
                DB::raw("SUM(order_items.total_amount) as total")
            )
            ->groupBy(DB::raw("MONTH(orders.created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $chart = [];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = (float) ($salesData[$m] ?? 0);
        }
        return $chart;
    }

    private function getPurchaseDataByYear($year, $BranchID)
    {
        $purchaseData = DB::table('purchases')
            ->join('purchase_invoice', function ($join) use ($BranchID) {
                $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                    ->where('purchase_invoice.isDeleted', '!=', 1)
                    ->where('purchase_invoice.branch_id', '=', $BranchID);
            })
            ->whereYear('purchases.created_at', $year)
            ->where('purchases.isDeleted', '!=', 1)
            ->select(
                DB::raw("MONTH(purchases.created_at) as month"),
                DB::raw("SUM(purchases.amount_total) as total")
            )
            ->groupBy(DB::raw("MONTH(purchases.created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $chart = [];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = (float) ($purchaseData[$m] ?? 0);
        }
        return $chart;
    }

    /**
     * Login with Face Recognition.
     */
    public function faceLogin(Request $request)
    {
        $request->validate([
            'face_descriptor'   => 'required|array|size:128',
            'face_descriptor.*' => 'numeric',
        ]);

        $descriptor = $this->faceRecognition()->normalizeDescriptor($request->input('face_descriptor'));
        if (!$descriptor) {
            $this->logFaceAttempt($request, null, 'failed', null, null, 'Invalid face descriptor payload.');
            return response()->json([
                'status' => false,
                'error'  => 'Invalid face descriptor provided.',
            ], 422);
        }

        $faceRecognition = $this->faceRecognition();
        $staffCandidates = User::query()
            ->where('isDeleted', 0)
            ->whereNotNull('face_descriptor')
            ->orderBy('id')
            ->get(['id', 'name', 'branch_id', 'role', 'isDeleted', 'face_descriptor']);

        $matchResult = $faceRecognition->resolveLoginMatch($descriptor, $staffCandidates);
        $bestMatch   = $matchResult['match'] ?? null;

        if (($matchResult['reason'] ?? null) !== 'matched' || !$bestMatch) {
            $this->logFaceAttempt(
                $request,
                $bestMatch['user'] ?? null,
                'failed',
                $bestMatch['distance'] ?? null,
                $bestMatch['confidence'] ?? null,
                $this->faceLoginFailureMessage($matchResult['reason'] ?? null)
            );
            return response()->json([
                'status' => false,
                'error'  => $this->faceLoginFailureMessage($matchResult['reason'] ?? null),
            ], 401);
        }

        $staff = $bestMatch['user'];
        $location = $this->extractLoginLocation($request);
        // Location check bypassed for face login as requested
        /*
        $locationCheck = app(AttendanceLocationService::class)->validateForUser(
            $staff,
            $location['latitude'],
            $location['longitude']
        );

        if (! $locationCheck['allowed']) {
            Auth::guard('web')->logout();

            return response()->json([
                'status' => false,
                'error'  => $locationCheck['message'],
            ], 422);
        }
        */

        $staff = User::find($staff->id);
        Auth::guard('web')->login($staff, true);
        $request->session()->regenerate();

        $this->logFaceAttempt(
            $request, $staff, 'success',
            $bestMatch['distance'], $bestMatch['confidence'],
            'Staff face login successful.'
        );

        if ((int) $staff->isDeleted === 1) {
            Auth::guard('web')->logout();
            return response()->json([
                'status' => false,
                'error'  => 'Your account is inactive. Please contact admin.',
            ], 403);
        }

        $token = $staff->createToken('LaravelPassportToken')->accessToken;

        // Get permissions
        $permissions = $staff->permissions()->with('module')->get();
        $formattedPermissions = [];
        foreach ($permissions as $permission) {
            $moduleName = $permission->module->module ?? 'Unknown';
            $formattedPermissions[$moduleName] = [
                'id' => $permission->id,
                'user_id' => $permission->user_id,
                'module_id' => $permission->module_id,
                'view' => $permission->view,
                'create' => $permission->add,
                'update' => $permission->edit,
                'delete' => $permission->delete,
                'created_at' => $permission->created_at,
            ];
        }

        $redirect = route('auth.dashboard');

        if (strtolower($staff->role) === 'staff') {
            $redirect = route('auth.staff-dashboard');
        } elseif (strtolower($staff->role) === 'hr') {
            $redirect = route('hr.dashboard');
        }

        return response()->json([
            'status'      => true,
            'token'       => $token,
            'user'        => $staff,
            'redirect'    => $redirect,
            'permissions' => $formattedPermissions,
            'login_method' => 'face',
            'showAppointments' => (strtolower($staff->role) !== 'staff' && strtolower($staff->role) !== 'hr'),
        ]);
    }

    protected function logFaceAttempt(
        Request $request,
        ?User $user,
        string $status,
        ?float $distance,
        ?float $confidence,
        string $message
    ): void {
        try {
            FaceLoginAudit::create([
                'user_id'    => $user?->id,
                'branch_id'  => $user?->branch_id,
                'status'     => $status,
                'method'     => 'face',
                'distance'   => $distance,
                'confidence' => $confidence,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'message'    => $message,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Face login audit skipped: ' . $e->getMessage());
        }
    }

    protected function faceRecognition(): FaceRecognitionService
    {
        return app(FaceRecognitionService::class);
    }

    protected function faceLoginFailureMessage(?string $reason): string
    {
        return match ($reason) {
            'ambiguous_match' => 'Face verification is too close to another staff record. Please contact admin and re-register your face.',
            default           => 'Face not recognized. Please try again or use password.',
        };
    }
}
