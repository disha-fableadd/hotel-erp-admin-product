<?php

namespace App\Http\Controllers\admin;

use App\Models\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    private function ensurePlanColumns(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('plans')) {
                \Illuminate\Support\Facades\Schema::create('plans', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->decimal('price', 10, 2)->nullable();
                    $table->decimal('discount_percent', 5, 2)->nullable()->default(0.00);
                    $table->decimal('final_price', 10, 2)->nullable();
                    $table->decimal('total_amount', 10, 2)->nullable();
                    $table->decimal('total_price', 10, 2)->nullable();
                    $table->decimal('renewal_price', 10, 2)->nullable();
                    $table->string('duration')->nullable()->default('month');
                    $table->date('start_date')->nullable();
                    $table->date('end_date')->nullable();
                    $table->string('subtitle')->nullable();
                    $table->integer('user_limit')->nullable();
                    $table->integer('branch_limit')->nullable();
                    $table->integer('storage_limit')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->unsignedBigInteger('sub_branch_id')->nullable();
                    $table->json('features')->nullable();
                    $table->json('sub_modules')->nullable();
                    $table->timestamps();
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('plan_features')) {
                \Illuminate\Support\Facades\Schema::create('plan_features', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('plan_id');
                    $table->string('feature');
                    $table->timestamps();
                });
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('users') && !\Illuminate\Support\Facades\Schema::hasColumn('users', 'plan_id')) {
                \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->unsignedBigInteger('plan_id')->nullable()->after('branch_id');
                });
            }
        } catch (\Throwable) {}
    }

    public function index()
    {
        $this->ensurePlanColumns();
        $plans = Plan::latest()->get();

        return view('plans.planlist', compact('plans'));
    }

    public function create()
    {
        $this->ensurePlanColumns();
        return view('plans.addplan');
    }

    protected function validatePlan(Request $request, $id = null): array
    {
        return $request->validate([
            'name'             => 'required|string|max:255',
            'price'            => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'final_price'      => 'nullable|numeric|min:0',
            'total_amount'     => 'nullable|numeric|min:0',
            'total_price'      => 'nullable|numeric|min:0',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'duration'         => 'nullable|string',
            'subtitle'         => 'nullable|string',
            'user_limit'       => 'nullable|integer|min:0',
            'branch_limit'     => 'nullable',
            'storage_limit'    => 'nullable|integer|min:0',
            'is_active'        => 'required|boolean',
            'features'         => 'nullable|string',
            'sub_branch_id'    => 'nullable|integer',
            'renewal_price'    => 'nullable|numeric|min:0',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);
        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $validated['features']))));
        }

        DB::transaction(function () use ($validated, $featuresArray) {
            Plan::create([
                'name'             => $validated['name'],
                'price'            => $validated['price']            ?? null,
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'final_price'      => $validated['final_price']      ?? null,
                'renewal_price'    => $validated['renewal_price']    ?? null,
                'total_amount'     => $validated['total_amount']     ?? null,
                'total_price'      => $validated['total_price']      ?? ($validated['total_amount'] ?? null),
                'duration'         => $validated['duration']         ?? 'month',
                'start_date'       => $validated['start_date']       ?? null,
                'end_date'         => $validated['end_date']         ?? null,
                'subtitle'         => $validated['subtitle']         ?? null,
                'user_limit'       => $validated['user_limit']       ?? null,
                'branch_limit'     => $validated['branch_limit']     ?? null,
                'storage_limit'    => $validated['storage_limit']    ?? null,
                'is_active'        => $validated['is_active'],
                'sub_branch_id'    => $validated['sub_branch_id']    ?? null,
                'features'         => $featuresArray,
            ]);
        });

        return redirect()->route('plans.planlist')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        $this->ensurePlanColumns();
        return view('plans.editplan', compact('plan'));
    }

    public function show(Plan $plan)
    {
        return redirect()->route('plans.edit', $plan);
    }

    public function update(Request $request, Plan $plan)
    {
        $this->ensurePlanColumns();
        $validated = $this->validatePlan($request, $plan->id);
        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $validated['features']))));
        }

        DB::transaction(function () use ($plan, $validated, $featuresArray) {
            $updateData = $validated;
            $updateData['features'] = $featuresArray;
            $plan->update($updateData);
        });

        return redirect()->route('plans.planlist')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $this->ensurePlanColumns();
        DB::transaction(function () use ($plan) {
            $plan->delete();
        });

        return redirect()->route('plans.planlist')->with('success', 'Plan deleted successfully.');
    }

    public function myplan()
    {
        $this->ensurePlanColumns();
        $user = auth()->user();
        $plan = $user->getActivePlan();

        if (!$plan) {
            return redirect()->route('auth.profile')->with('error', 'No active plan found.');
        }

        $tenant = tenant();
        $clientId = $tenant['client_id'] ?? null;
        $apiUrl = config('tenant.api_url');
        $superAdminBase = preg_replace('#/api/tenant/?$#i', '', $apiUrl);

        // Sync latest validated subscription dates from Super Admin tenant payload if available
        if ($tenant && !empty($tenant['expiry_date']) && $plan) {
            $tenantExpiry = \Carbon\Carbon::parse($tenant['expiry_date'])->format('Y-m-d');
            $tenantStart = !empty($tenant['start_date']) ? \Carbon\Carbon::parse($tenant['start_date'])->format('Y-m-d') : null;
            
            $needsUpdate = false;
            $updateData = [];

            if ($plan->end_date !== $tenantExpiry) {
                $updateData['end_date'] = $tenantExpiry;
                $needsUpdate = true;
            }
            if ($tenantStart && $plan->start_date !== $tenantStart) {
                $updateData['start_date'] = $tenantStart;
                $needsUpdate = true;
            }
            if (!empty($tenant['billing_cycle']) && $plan->duration !== (str_contains(strtolower($tenant['billing_cycle']), 'month') ? 'month' : 'year')) {
                $updateData['duration'] = str_contains(strtolower($tenant['billing_cycle']), 'month') ? 'month' : 'year';
                $needsUpdate = true;
            }

            $tenantUserLimit = $tenant['user_limit'] ?? ($tenant['max_user'] ?? null);
            $tenantBranchLimit = $tenant['branch_limit'] ?? ($tenant['max_branch'] ?? null);

            if ($tenantUserLimit !== null && (int)$plan->user_limit !== (int)$tenantUserLimit) {
                $updateData['user_limit'] = (int)$tenantUserLimit;
                $needsUpdate = true;
            }
            if ($tenantBranchLimit !== null && (int)$plan->branch_limit !== (int)$tenantBranchLimit) {
                $updateData['branch_limit'] = (int)$tenantBranchLimit;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $plan->update($updateData);
                $plan->refresh();
            }
        }

        $paymentHistory = [];

        // 1. Fetch official transactions/payments from Super Admin for this client
        if ($clientId) {
            try {
                $resp = \Illuminate\Support\Facades\Http::timeout(6)->withHeaders([
                    'X-Tenant-Secret' => config('tenant.secret'),
                ])->post("{$superAdminBase}/api/tenant/billing-history", [
                    'client_id' => $clientId,
                ]);

                if ($resp->successful() && !empty($resp->json('data'))) {
                    $paymentHistory = $resp->json('data');
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed fetching billing history from Super Admin: ' . $e->getMessage());
            }
        }

        // 2. Local fallback if Super Admin transactions could not be fetched
        if (empty($paymentHistory)) {
            if (!empty($user->branch_id)) {
                $planHistory = \App\Models\Plan::where('sub_branch_id', $user->branch_id)->latest('id')->get();
            } else {
                $planHistory = \App\Models\Plan::whereNull('sub_branch_id')->orWhere('sub_branch_id', $user->id)->latest('id')->get();
            }
        } else {
            $planHistory = [];
        }

        return view('plans.myplan', compact('user', 'plan', 'planHistory', 'paymentHistory'));
    }

    /**
     * Get available upgrade plans from Super Admin (or local plans fallback).
     */
    public function getUpgradePlans(Request $request)
    {
        $tenant = tenant();
        $apiUrl = config('tenant.api_url');
        $superAdminBase = preg_replace('#/api/tenant/?$#i', '', $apiUrl);
        $memberships = [];

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(6)->get("{$superAdminBase}/api/memberships", [
                'status' => 'Active',
            ]);

            if ($resp->successful() && !empty($resp->json('data'))) {
                $memberships = $resp->json('data');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed fetching memberships from Super Admin: ' . $e->getMessage());
        }

        // Current active plan
        $currentPlan = auth()->user()->getActivePlan();

        return response()->json([
            'success' => true,
            'current_plan' => $currentPlan,
            'tenant' => $tenant,
            'available_plans' => $memberships,
        ]);
    }

    /**
     * Create Razorpay order via Super Admin's Razorpay API.
     */
    public function createRazorpayOrder(Request $request)
    {
        $validated = $request->validate([
            'payment_type' => 'required|in:renewal,upgrade',
            'amount' => 'required|numeric|min:1',
            'membership_id' => 'nullable|integer',
            'billing_cycle' => 'nullable|string',
        ]);

        $tenant = tenant();
        $clientId = $tenant['client_id'] ?? null;
        $apiUrl = config('tenant.api_url');
        $superAdminBase = preg_replace('#/api/tenant/?$#i', '', $apiUrl);

        $user = auth()->user();
        $payload = [
            'amount' => (float) $validated['amount'],
            'client_id' => $clientId,
            'payment_type' => $validated['payment_type'],
            'name' => $tenant['client_name'] ?? $user->name,
            'email' => $user->email,
            'contact' => $user->mobile ?? $user->phone ?? '',
            'description' => $validated['payment_type'] === 'renewal' 
                ? 'Plan renewal payment for ' . ($tenant['client_name'] ?? $user->name)
                : 'Plan upgrade payment for ' . ($tenant['client_name'] ?? $user->name),
        ];

        if ($validated['payment_type'] === 'upgrade' && !empty($validated['membership_id'])) {
            $payload['membership_id'] = $validated['membership_id'];
            $payload['billing_cycle'] = $validated['billing_cycle'] ?? 'yearly';
        }

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(10)->post("{$superAdminBase}/api/razorpay/create-order", $payload);

            if ($resp->successful() && !empty($resp->json('data'))) {
                return response()->json([
                    'success' => true,
                    'order' => $resp->json('data'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $resp->json('message') ?? 'Failed to initialize payment gateway order.',
                'error' => $resp->json(),
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to connect to Super Admin payment gateway: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify payment with Super Admin and synchronously sync client's local DB.
     */
    public function verifyRazorpayPayment(Request $request)
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'payment_type' => 'required|in:renewal,upgrade',
            'amount' => 'required|numeric',
            'membership_id' => 'nullable|integer',
            'billing_cycle' => 'nullable|string',
        ]);

        $tenant = tenant();
        $clientId = $tenant['client_id'] ?? null;
        $apiUrl = config('tenant.api_url');
        $superAdminBase = preg_replace('#/api/tenant/?$#i', '', $apiUrl);

        try {
            // 1. Verify payment with Super Admin API
            $verifyResp = \Illuminate\Support\Facades\Http::timeout(12)->post("{$superAdminBase}/api/razorpay/verify-payment", [
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
                'client_id' => $clientId,
                'payment_type' => $validated['payment_type'],
                'amount' => $validated['amount'],
                'billing_cycle' => $validated['billing_cycle'] ?? null,
            ]);

            if (!$verifyResp->successful() || !$verifyResp->json('success')) {
                \Illuminate\Support\Facades\Log::warning('Super Admin Razorpay verification rejected', [
                    'status' => $verifyResp->status(),
                    'response' => $verifyResp->json() ?? $verifyResp->body(),
                    'payload' => [
                        'order_id' => $validated['razorpay_order_id'],
                        'payment_id' => $validated['razorpay_payment_id'],
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $verifyResp->json('message') ?? 'Payment verification failed at Super Admin.',
                ], 400);
            }

            // 2. Clear tenant lookup cache so next request fetches fresh dates from Super Admin
            if ($tenant && !empty($tenant['domain'])) {
                app(\App\Services\Tenancy\TenantManager::class)->forgetCachedHost($tenant['domain']);
            }

            // 3. Update the local client tenant database immediately & accurately
            $currentPlan = auth()->user()->getActivePlan() 
                ?? auth()->user()->getLatestPlan() 
                ?? \App\Models\Plan::latest('id')->first();

            if ($validated['payment_type'] === 'renewal') {
                $cycle = $currentPlan ? strtolower(trim((string)$currentPlan->duration)) : 'year';
                if (!empty($validated['billing_cycle'])) {
                    $cycle = strtolower(trim((string)$validated['billing_cycle']));
                }

                $today = \Carbon\Carbon::today();
                $currentEndDate = ($currentPlan && !empty($currentPlan->end_date)) 
                    ? \Carbon\Carbon::parse($currentPlan->end_date)->startOfDay() 
                    : null;

                $isActivePlan = ($currentEndDate && $currentEndDate->greaterThanOrEqualTo($today));

                $renewalStartDate = $isActivePlan
                    ? $currentEndDate->toDateString()
                    : $today->toDateString();

                $baseDateForExpiry = \Carbon\Carbon::parse($renewalStartDate);

                $newEndDate = (str_contains($cycle, 'month')) 
                    ? $baseDateForExpiry->copy()->addMonth()->toDateString()
                    : $baseDateForExpiry->copy()->addYear()->toDateString();

                if ($currentPlan) {
                    $currentPlan->update([
                        'is_active' => 1,
                        'duration' => str_contains($cycle, 'month') ? 'month' : 'year',
                        'start_date' => $renewalStartDate,
                        'end_date' => $newEndDate,
                        'total_amount' => $validated['amount'],
                    ]);

                    \App\Models\User::where('role', 'admin')->orWhere('id', auth()->id())->update([
                        'plan_id' => $currentPlan->id,
                    ]);
                }
            } elseif ($validated['payment_type'] === 'upgrade' && !empty($validated['membership_id'])) {
                $membershipResp = \Illuminate\Support\Facades\Http::timeout(6)->get("{$superAdminBase}/api/memberships/{$validated['membership_id']}");
                $membershipData = $membershipResp->successful() ? $membershipResp->json('data') : null;

                $cycle = !empty($validated['billing_cycle']) ? strtolower(trim((string)$validated['billing_cycle'])) : 'year';
                $today = \Carbon\Carbon::today();
                $startDate = $today->toDateString();
                $endDate = str_contains($cycle, 'month') 
                    ? $today->copy()->addMonth()->toDateString() 
                    : $today->copy()->addYear()->toDateString();

                $planName = $membershipData['name'] ?? 'Upgraded Plan';
                $features = !empty($membershipData['features']) && is_array($membershipData['features'])
                    ? $membershipData['features']
                    : ($currentPlan ? $currentPlan->features : []);

                // Deactivate current active plans
                \App\Models\Plan::query()->update(['is_active' => 0]);

                // Create new plan
                $newPlan = \App\Models\Plan::create([
                    'name' => $planName,
                    'price' => (float)$validated['amount'],
                    'discount_percent' => 0,
                    'final_price' => (float)$validated['amount'],
                    'total_amount' => (float)$validated['amount'],
                    'total_price' => (float)$validated['amount'],
                    'renewal_price' => $membershipData['renewal_price'] ?? null,
                    'duration' => str_contains($cycle, 'month') ? 'month' : 'year',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => 1,
                    'user_limit' => $membershipData['user_limit'] ?? ($currentPlan->user_limit ?? null),
                    'branch_limit' => $membershipData['branch_limit'] ?? ($currentPlan->branch_limit ?? null),
                    'storage_limit' => $membershipData['storage_limit'] ?? ($currentPlan->storage_limit ?? null),
                    'features' => $features,
                ]);

                \App\Models\User::where('role', 'admin')->orWhere('id', auth()->id())->update([
                    'plan_id' => $newPlan->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and plan updated successfully!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during payment processing: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate Tax Invoice / Receipt PDF for a subscription plan matching the design.
     */
    public function downloadInvoicePdf($id = null)
    {
        $user = auth()->user();
        $plan = $user->getActivePlan() ?? \App\Models\Plan::latest('id')->first();

        if (!$plan) {
            return redirect()->back()->with('error', 'Plan not found.');
        }

        $apiUrl = config('tenant.api_url');
        $superAdminBase = preg_replace('#/api/tenant/?$#i', '', $apiUrl);
        $tenant = tenant() ?? [];
        $productId = $tenant['product_id'] ?? null;
        $projectId = $tenant['project_id'] ?? null;
        $clientId = $tenant['client_id'] ?? null;

        $companyDetails = [];
        $termsList = [];

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(5)->get($superAdminBase . '/api/invoice-details', [
                'product_id' => $productId,
                'project_id' => $projectId,
            ]);
            if ($resp->successful() && !empty($resp->json('data'))) {
                $data = $resp->json('data');
                $companyDetails = $data['company_details'] ?? [];
                $termsList = $data['terms'] ?? [];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Invoice details fetch error: ' . $e->getMessage());
        }

        $transactionId = request('transaction_id') ?: ($id && is_numeric($id) && $id > 10 ? $id : null);
        $transactionData = null;

        if ($clientId && $transactionId) {
            try {
                $txResp = \Illuminate\Support\Facades\Http::timeout(5)->withHeaders([
                    'X-Tenant-Secret' => config('tenant.secret'),
                ])->post("{$superAdminBase}/api/tenant/billing-history", [
                    'client_id' => $clientId,
                    'transaction_id' => $transactionId,
                ]);

                if ($txResp->successful() && !empty($txResp->json('data'))) {
                    $transactionData = $txResp->json('data')[0] ?? null;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed fetching specific transaction: ' . $e->getMessage());
            }
        }

        if (empty($companyDetails['company_name']) || $companyDetails['company_name'] === 'Fablead Developers') {
            $companyDetails['company_name'] = 'FABLEAD DEVELOPERS TECHNOLAB';
        }
        if (empty($companyDetails['address'])) {
            $companyDetails['address'] = 'Ascon Plaza, A/5001, nr. bhulka bhavan school, Behind Bulka Bhavan School, Adajan Gam, Adajan, Surat, Gujarat 395009';
        }
        if (empty($companyDetails['contact'])) {
            $companyDetails['contact'] = '1111111111';
        }
        if (empty($companyDetails['email'])) {
            $companyDetails['email'] = 'info@fableadtechnolabs.com';
        }

        if (empty($termsList)) {
            $termsList = [
                'This invoice is valid for the period mentioned in the order details.',
                'Payment is non-refundable once the service is activated.',
                'Renewal will be applicable as per the mentioned amount and validity.',
                'For any support or query, please contact us at the provided email or phone number.',
            ];
        }

        $customerName = $tenant['client_name'] ?? $user->name;
        $companyName = $tenant['brand_name'] ?? $tenant['client_name'] ?? $user->company_name ?? $customerName;
        $customerPhone = $user->mobile ?? $user->phone ?? ($tenant['phone'] ?? '');
        $customerEmail = $user->email;

        $invoiceYear = date('Y');
        $invoiceNo = $transactionData && !empty($transactionData['id'])
            ? 'INV-' . \Carbon\Carbon::parse($transactionData['created_at'])->format('Y') . '-' . str_pad((string)$transactionData['id'], 4, '0', STR_PAD_LEFT)
            : 'INV-' . $invoiceYear . '-' . str_pad((string)$plan->id, 4, '0', STR_PAD_LEFT);

        $orderId = $transactionData['razorpay_order_id'] ?? ($transactionData['id'] ?? (string)$plan->id);
        $orderDate = $transactionData && !empty($transactionData['created_at'])
            ? \Carbon\Carbon::parse($transactionData['created_at'])->format('d M Y')
            : ($plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->format('d M Y') : date('d M Y'));

        $paymentMethod = $transactionData['payment_method'] ?? 'Razorpay';
        $paymentStatus = 'Completed (Paid)';

        if ($transactionData) {
            $plan = clone $plan;
            if (!empty($transactionData['amount'])) {
                $plan->total_amount = (float) $transactionData['amount'];
                $plan->price = (float) $transactionData['amount'];
            }
            if (!empty($transactionData['plan_name'])) {
                $plan->name = $transactionData['plan_name'];
            }
            if (!empty($transactionData['billing_cycle'])) {
                $plan->duration = $transactionData['billing_cycle'];
            }
            if (!empty($transactionData['period_start'])) {
                $plan->start_date = $transactionData['period_start'];
            }
            if (!empty($transactionData['period_end'])) {
                $plan->end_date = $transactionData['period_end'];
            }
            if (!empty($transactionData['payment_type'])) {
                $plan->subtitle = ucfirst($transactionData['payment_type']) . ' Payment';
            }
        }

        $validity = ($plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->format('Y-m-d') : date('Y-m-d')) . ' to ' . ($plan->end_date ? \Carbon\Carbon::parse($plan->end_date)->format('Y-m-d') : date('Y-m-d'));

        $featuredModules = [];
        if (!empty($tenant['modules']) && is_array($tenant['modules'])) {
            $featuredModules = array_values($tenant['modules']);
        } elseif (!empty($plan->features) && is_array($plan->features)) {
            $featuredModules = array_values($plan->features);
        } else {
            $featuredModules = ['Rooms & Tables', 'Products', 'Orders', 'Invoices', 'Purchases', 'Staff', 'Settings'];
        }

        $productTitle = $tenant['product_code'] ?? 'HOTEL';

        $pdfData = [
            'company' => $companyDetails,
            'customer' => [
                'name' => $customerName,
                'company' => $companyName,
                'phone' => $customerPhone,
                'email' => $customerEmail,
            ],
            'order' => [
                'status' => $paymentStatus,
                'invoice_no' => $invoiceNo,
                'order_id' => $orderId,
                'date' => $orderDate,
                'payment_method' => $paymentMethod,
                'validity' => $validity,
            ],
            'plan' => $plan,
            'product_title' => $productTitle,
            'featured_modules' => $featuredModules,
            'terms' => $termsList,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('plans.invoice_pdf', $pdfData)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        return $pdf->stream('Invoice_' . $invoiceNo . '.pdf');
    }
}
