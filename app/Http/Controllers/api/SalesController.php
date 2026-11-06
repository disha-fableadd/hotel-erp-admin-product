<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\Table_no;
use App\Models\User;
use App\Models\UserDetail;
use App\Services\PosPricingService;
use App\Services\RecipeStockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SalesController extends Controller
{
    protected function posPricing(): PosPricingService
    {
        return app(PosPricingService::class);
    }

    /**
     * Shop GST mode for a branch (product-wise vs overall bill rate).
     */
    protected function gstOptionsForBranch($branchId): array
    {
        $setting = Setting::where('branch_id', $branchId)->first();

        return PosPricingService::gstOptionsFromSetting($setting);
    }

    /**
     * Allow a per-bill Service Tax % override from POS / dine-in (Discount-style).
     */
    protected function applyBillServiceTaxOverride(array $gstOpts, Request $request): array
    {
        if (! $request->exists('service_tax_rate')) {
            return $gstOpts;
        }

        $rate = max(0, min(100, (float) $request->input('service_tax_rate', 0)));
        $gstOpts['service_tax_rate'] = $rate;
        $gstOpts['service_tax_enabled'] = $rate > 0;
        if ($request->filled('service_tax_name')) {
            $name = trim((string) $request->input('service_tax_name'));
            if ($name !== '') {
                $gstOpts['service_tax_name'] = $name;
            }
        }

        return $gstOpts;
    }

    /**
     * Frozen GST mode options from a saved order (falls back to product-wise).
     */
    protected function gstOptionsFromOrder($order): array
    {
        $svcRate = (float) ($order->service_tax_rate ?? 0);
        $svcName = trim((string) ($order->service_tax_name ?? 'Service Tax')) ?: 'Service Tax';
        $svc = [
            'service_tax_enabled' => $svcRate > 0,
            'service_tax_rate' => max(0, $svcRate),
            'service_tax_name' => $svcName,
        ];

        $mode = (string) ($order->gst_apply_mode ?? '');
        if ($mode === 'overall') {
            return array_merge([
                'gst_apply_mode' => 'overall',
                'overall_gst_rate' => (float) ($order->overall_gst_rate ?? 0),
                'overall_gst_name' => 'GST',
            ], $svc);
        }
        if ($mode === 'product') {
            return array_merge([
                'gst_apply_mode' => 'product',
                'overall_gst_rate' => 0,
                'overall_gst_name' => 'GST',
            ], $svc);
        }
        // Legacy orders: treat GST as product-wise; still honour frozen service tax if present
        return array_merge([
            'gst_apply_mode' => 'product',
            'overall_gst_rate' => 0,
            'overall_gst_name' => 'GST',
        ], $svc);
    }

    protected function allowsItemLevelDiscount($branchId): bool
    {
        $setting = Setting::where('branch_id', $branchId)->first();
        return (bool) ($setting->allow_item_level_discount ?? false);
    }

    protected function sanitizeItemDiscounts(array $item, bool $allowed): array
    {
        if ($allowed) {
            return $item;
        }
        $item['item_discount_percent'] = 0;
        $item['item_discount_amount'] = 0;
        return $item;
    }

    /**
     * Force the active MySQL schema to the one sticky for this browser session.
     * Stops Place Order validating product_ids against a different DB than product browse.
     */
    protected function ensureTenantDatabaseFromSession(Request $request): void
    {
        if (! config('tenant.enabled') || ! $request->hasSession()) {
            return;
        }

        $tenant = $request->session()->get('tenant');
        if (! is_array($tenant) || empty($tenant['db']['database'])) {
            return;
        }

        $stickyDb = (string) $request->session()->get('tenant_resolved_database', '');
        if ($stickyDb !== '') {
            $tenant['db']['resolved_database'] = $stickyDb;
        }

        try {
            app(\App\Services\Tenancy\TenantManager::class)->activate($tenant);
            $request->session()->put('tenant', app(\App\Services\Tenancy\TenantManager::class)->current() ?? $tenant);
        } catch (\Throwable $e) {
            Log::warning('ensureTenantDatabaseFromSession failed: ' . $e->getMessage());
        }
    }

    /**
     * Coerce POS cart line IDs before validation.
     * Drops lines whose product_id is missing (order_items.product_id has an FK),
     * and nulls orphaned category_id values (order_items.category_id FK).
     */
    protected function normalizeSaleOrderItems(Request $request): void
    {
        $items = $request->input('items');
        if (!is_array($items)) {
            return;
        }

        $candidateIds = [];
        $parsed = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $pid = $item['product_id'] ?? null;
            if (is_string($pid) && preg_match('/^(\d+)/', trim($pid), $m)) {
                $pid = (int) $m[1];
            } elseif (is_numeric($pid)) {
                $pid = (int) $pid;
            } else {
                $pid = null;
            }
            if (!$pid) {
                continue;
            }

            $cat = $item['categoryId'] ?? $item['category_id'] ?? null;
            if ($cat === '' || $cat === null || !is_numeric($cat)) {
                $cat = null;
            } else {
                $cat = (int) $cat;
            }

            $variantId = $item['variant_id'] ?? null;
            if ($variantId === '' || $variantId === null || !is_numeric($variantId)) {
                $variantId = null;
            } else {
                $variantId = (int) $variantId;
            }

            $item['product_id'] = $pid;
            $item['categoryId'] = $cat;
            $item['category_id'] = $cat;
            $item['variant_id'] = $variantId;
            $parsed[] = $item;
            $candidateIds[] = $pid;
        }

        $existingIds = [];
        if ($candidateIds) {
            $existingIds = Product::whereIn('id', array_values(array_unique($candidateIds)))
                ->pluck('id')
                ->all();
            $existingIds = array_fill_keys(array_map('intval', $existingIds), true);
        }

        $normalized = [];
        foreach ($parsed as $item) {
            $pid = (int) $item['product_id'];
            if (!isset($existingIds[$pid])) {
                continue;
            }
            $cat = PosPricingService::safeCategoryId($item['categoryId'] ?? null);
            $item['categoryId'] = $cat;
            $item['category_id'] = $cat;
            $normalized[] = $item;
        }

        $request->merge(['items' => $normalized]);
    }

    protected function recipeStock(): RecipeStockService
    {
        return app(RecipeStockService::class);
    }

    private function normalizeTableStatus(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        return in_array($status, [
            Table_no::STATUS_BLANK,
            Table_no::STATUS_RUNNING,
            Table_no::STATUS_RESERVED,
            Table_no::STATUS_PRINTED,
        ], true)
            ? $status
            : Table_no::STATUS_BLANK;
    }

    private function tableStatusClass(?string $status): string
    {
        return match ($this->normalizeTableStatus($status)) {
            Table_no::STATUS_RUNNING => 'running-table',
            Table_no::STATUS_RESERVED => 'reserved-table',
            Table_no::STATUS_PRINTED => 'printed-table',
            default => 'blank-table',
        };
    }

    private function syncTableStatus(?int $tableId): array
    {
        if (! $tableId) {
            return [
                'status' => Table_no::STATUS_BLANK,
                'class' => 'blank-table',
            ];
        }

        $activeOrder = Order::where('table_no', $tableId)
            ->where('isDeleted', 0)
            ->where('table_status', 1)
            ->orderByDesc('id')
            ->first(['id', 'bill_printed', 'payment_status', 'remaining_amount']);

        $activeOrderId = $activeOrder?->id;

        if (! $activeOrderId) {
            $status = Table_no::STATUS_BLANK;
        } elseif (! empty($activeOrder->bill_printed)) {
            // Bill printed / awaiting settlement → yellow (Petpooja-style)
            $status = Table_no::STATUS_PRINTED;
        } else {
            $status = Table_no::STATUS_RUNNING;
        }

        $payload = [
            'table_status' => Table_no::statusToInt($status),
            'order_id' => $activeOrderId,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('table_no', 'status')) {
            $payload['status'] = $status;
        }

        Table_no::where('id', $tableId)->update($payload);

        return [
            'status' => $status,
            'class' => $this->tableStatusClass($status),
            'order_id' => $activeOrderId,
            'bill_total' => $activeOrderId
                ? (float) Order::where('id', $activeOrderId)->value('total_amount')
                : null,
        ];
    }

    /**
     * Mark dine-in customer bill as printed → yellow while awaiting settlement.
     * Fully paid / already closed table orders stay free (do not re-occupy).
     */
    public function markBillPrinted(Request $request, $id)
    {
        $order = Order::find($id);
        if (! $order || (int) ($order->isDeleted ?? 0) === 1) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $isSettled = in_array(strtolower((string) ($order->payment_status ?? '')), ['completed', 'paid'], true)
            && (float) ($order->remaining_amount ?? 0) <= 0.00001;

        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'bill_printed')) {
            $order->bill_printed = 1;
            if (! empty($order->table_no)) {
                // Only keep table occupied (yellow) when bill is unpaid / partial
                $order->table_status = $isSettled ? 0 : 1;
            }
            $order->save();
        }

        $tableSync = ['status' => null, 'class' => null, 'order_id' => null, 'bill_total' => null];
        if (! empty($order->table_no)) {
            $tableSync = $this->syncTableStatus((int) $order->table_no);
        }

        return response()->json([
            'status' => true,
            'order_id' => $order->id,
            'table_id' => $order->table_no,
            'table_status' => $tableSync['status'] ?? Table_no::STATUS_BLANK,
            'table_status_class' => $tableSync['class'] ?? 'blank-table',
            'total_amount' => $order->total_amount,
        ]);
    }

    // public function getAllSales(Request $request)
    // {
    //     $settings = DB::table('settings')->first();
    //     $currencySymbol = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     $query = Auth::guard('api')->user()
    //         ->sales()
    //         ->where('isDeleted', 0)
    //         ->with('customer');

    //     if ($request->has('date') && !empty($request->date)) {
    //         // 🟩 Only filter by date
    //         $query->whereDate('created_at', $request->date);
    //     } else {
    //         // 🟦 If date is not selected, apply month/year
    //         if ($request->has('year') && !empty($request->year)) {
    //             $query->whereYear('created_at', $request->year);
    //         }

    //         if ($request->has('month') && !empty($request->month)) {
    //             $query->whereMonth('created_at', $request->month);
    //         }
    //     }

    //     $sales = $query->get();

    //     if ($sales->isEmpty()) {
    //         return response()->json(['status' => false, 'message' => 'No sales found'], 404);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'data' => $sales,
    //         'currency_symbol' => $currencySymbol,
    //         'currency_position' => $currencyPosition,
    //     ], 200);
    // }

    public function getHistory($order_id)
    {
        // $history = PaymentStore::where('order_id', $job_card_id)
        $history = PaymentStore::where('order_id', $order_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history,
        ]);
    }
    public function makePaymentSubmit(Request $request)
    {
        // dd($request->all());
        // $user_id = Auth::id();
        $user_id = Auth::guard('api')->user()->id;

        $request->validate([

            'order_id' => 'nullable|integer',
            'payment_amount' => 'nullable|numeric',
            'payment_date' => 'nullable|date',
            'payment_type' => 'nullable|string',
            'emi_month' => 'nullable|integer',
            'pending_date' => 'nullable|date',
            'new_emi_value' => 'nullable',
            'emi_paid_value' => 'nullable|numeric',
        ]);

        if ($request->filled('order_id')) {
            // Determine payment amount
            $paymentAmount = $request->emi_total_new ?? $request->emi_total ?? $request->amount ?? $request->upi_online_amount ?? 0;

            if ($request->filled('cash_amount') && $request->filled('online_amount')) {
                $paymentAmount = (float) $request->cash_amount + (float) $request->online_amount;
            } elseif ($request->filled('fully_cash_amount') && $request->filled('full_online_amount')) {
                $paymentAmount = (float) $request->fully_cash_amount + (float) $request->full_online_amount;
            } elseif ($request->cashAmount) {
                $paymentAmount = $request->cashAmount;
            } elseif ($request->upi_online_amount) {
                $paymentAmount = $request->upi_online_amount;
            } elseif ($request->emi_monthly) {
                $paymentAmount = $request->emi_monthly;
            }

            // Determine payment type
            if (
                in_array($request->paid_type, ['cash_partially']) ||
                in_array($request->online_type, ['online_partially']) ||
                in_array($request->cash_online_type, ['cash_online_partially'])
            ) {
                $type = 'partially';
            } elseif (
                in_array($request->paid_type, ['cash_fully']) ||
                in_array($request->online_type, ['online_fully']) ||
                in_array($request->cash_online_type, ['cash_online_fully']) ||
                $request->payment_type === 'fully'

            ) {
                $type = 'fully';
            } elseif (
                in_array($request->emi_type, ['emi'])
            ) {
                $type = 'emi';
            } else {
                $type = 'fully';
            }

            // Fetch Invoice if exists
            $invoice = $request->order_id ? Order::find($request->order_id) : null;

            // Calculate remaining amount
            $currentRemaining = $invoice->remaining_amount ?? 0;
            $newRemaining = max(0, $currentRemaining - $paymentAmount);
            // dd($currentRemaining,$newRemaining);

            // $payment = PaymentStore::create([
            //     'user_id'           => $user_id,
            //     'order_id'          => $request->order_id,
            //     'custom_invoice_id' => null,
            //     'payment_amount'    => $paymentAmount,
            //     'remaining_amount'  => $newRemaining,
            //     'payment_method'    => $request->payment_method ?? $request->payment_type ?? '',
            //     'payment_date'      => \Carbon\Carbon::now(),
            //     'payment_type'      => $type,
            //     'cash_amount'       => $request->cash_amount,
            //     'upi_amount'        => $request->online_amount,
            //     'emi_month'         => $request->emi_month ?? 1,
            //     'isDeleted'         => 0,
            // ]);
            // Handle cash_online_partially separately
            // ✅ Handle cash_online cases separately
            if ($request->cash_online_type === 'cash_online_partially' || $request->cash_online_type === 'cash_online_fully') {
                $payments = [];

                // 1️⃣ Handle Cash entry (partially or fully)
                $cashValue = $request->cash_online_type === 'cash_online_partially'
                    ? $request->cash_amount
                    : $request->fully_cash_amount;

                if (!empty($cashValue) && $cashValue > 0) {
                    $cashPayment = PaymentStore::create([
                        'user_id' => $user_id,
                        'order_id' => $request->order_id,
                        'custom_invoice_id' => null,
                        'payment_amount' => $cashValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method' => 'cash',
                        'payment_date' => \Carbon\Carbon::now(),
                        'payment_type' => ($request->cash_online_type === 'cash_online_partially') ? 'partially' : 'fully',
                        'cash_amount' => $cashValue,
                        'upi_amount' => 0,
                        'emi_month' => $request->emi_month ?? 1,
                        'isDeleted' => 0,
                        'status' => 'credit'
                    ]);
                    $payments[] = $cashPayment;
                }

                // 2️⃣ Handle Online entry (partially or fully)
                $onlineValue = $request->cash_online_type === 'cash_online_partially'
                    ? $request->online_amount
                    : $request->full_online_amount;

                if (!empty($onlineValue) && $onlineValue > 0) {
                    $onlinePayment = PaymentStore::create([
                        'user_id' => $user_id,
                        'order_id' => $request->order_id,
                        'custom_invoice_id' => null,
                        'payment_amount' => $onlineValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method' => 'online',
                        'payment_date' => \Carbon\Carbon::now(),
                        'payment_type' => ($request->cash_online_type === 'cash_online_partially') ? 'partially' : 'fully',
                        'cash_amount' => 0,
                        'upi_amount' => $onlineValue,
                        'emi_month' => $request->emi_month ?? 1,
                        'isDeleted' => 0,
                        'status' => 'credit'
                    ]);
                    $payments[] = $onlinePayment;
                }

                $payment = $payments; // return both records as array
            } else {
                // 🟢 Default single payment record for all other cases
                $payment = PaymentStore::create([
                    'user_id' => $user_id,
                    'order_id' => $request->order_id,
                    'custom_invoice_id' => null,
                    'payment_amount' => $paymentAmount,
                    'remaining_amount' => $newRemaining,
                    'payment_method' => $request->payment_method ?? $request->payment_type ?? '',
                    'payment_date' => \Carbon\Carbon::now(),
                    'payment_type' => $type,
                    'cash_amount' => $request->cash_amount ?? 0,
                    'upi_amount' => $request->online_amount ?? 0,
                    'emi_month' => $request->emi_month ?? 1,
                    'isDeleted' => 0,
                    'status' => 'credit'
                ]);
            }

            // Update invoice
            if ($invoice) {
                $updateData = ['remaining_amount' => $newRemaining];

                // ✅ Auto set payment_status when fully paid or partially paid
                if ($newRemaining <= 0) {
                    $updateData['payment_status'] = 'completed';
                } elseif ($newRemaining > 0 && $newRemaining < ($invoice->total_amount ?? 0)) {
                    $updateData['payment_status'] = 'partially';
                } else {
                    $updateData['payment_status'] = 'Pending';
                }
                // If new EMI is being set
                if ($request->filled('new_emi_value') && $request->payment_method == 'emi' || $request->filled('emi_paid_value')) {
                    //  dd($request->all());
                    $updateData['payment_method'] = 'EMI';
                    $updateData['emi_duration'] = $request->emi_month_new ?? $request->emi_month;
                    $updateData['emi_months'] = $request->emi_total_new ?? 1;
                } else {
                    // dd($request->filled('emi_paid_value'));
                    // Otherwise, keep the actual payment method (cash, online, cash_online)
                    $method = $request->payment_method ?? $request->payment_type ?? $updateData['payment_method'] ?? 'Cash';

                    // Normalize to proper values
                    if (strtolower($method) === 'online' || strtolower($method) === 'upi') {
                        $method = 'Online';
                    } elseif (strtolower($method) === 'cash') {
                        $method = 'Cash';
                    } elseif (strtolower($method) === 'emi') {
                        $method = 'EMI';
                    } else {
                        $method = 'Cash';
                    }

                    $updateData['payment_method'] = $method;
                }

                if ($request->emi_duration) {
                    $updateData['emi_duration'] = $request->emi_duration;
                }

                // Update next pending date if provided
                if ($request->pending_date) {
                    $updateData['next_pending_date'] = $request->pending_date;
                }

                $invoice->update($updateData);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Payment submitted successfully.',
            'data' => $payment,
        ]);
    }

    // In your controller
    public function getProductsByCategory(Request $request, $categoryId)
    {
        $this->ensureTenantDatabaseFromSession($request);

        $settings = DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $user = Auth::guard('api')->user();

        $query = Product::with(['category', 'quantityType', 'variants', 'addons', 'tax'])
            ->where('isDeleted', 0)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('is_pos_available', 1)->orWhereNull('is_pos_available');
            });

        if ($user && ($user->role === 'staff' || $user->role === 'sub-admin')) {
            $branchId = ($user->role === 'staff' && $user->branch_id) ? $user->branch_id : $user->id;
            $query->where('branch_id', $branchId);
        }

        if ($categoryId !== 'all' && $categoryId !== '0' && $categoryId != 0) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('display_order')->orderBy('name')->get();

        return response()->json([
            'status' => true,
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'quantity_type_id' => $product->quantity_type_id,
                    'quantity_type' => $product->quantityType ? $product->quantityType->quantity_type : null,
                    'image' => $product->images,
                    'image_url' => $product->image_url,
                    'categoryId' => $product->category_id,
                    'category_name' => $product->category ? $product->category->name : 'Uncategorized',
                    'gst_applicable' => $product->category
                        ? (method_exists($product->category, 'appliesGst') ? $product->category->appliesGst() : true)
                        : true,
                    'product_kind' => $product->product_kind ?? 'general',
                    'food_type' => $product->food_type ?? 'na',
                    'track_inventory' => $product->tracksInventory(),
                    'allow_oversell' => $product->allowsOversell(),
                    'show_out_of_stock' => $product->showOutOfStockBadge(),
                    'tax_id' => $product->tax_id ? (int) $product->tax_id : null,
                    'tax_inclusive' => (bool) ($product->tax_inclusive ?? false),
                    'tax_rate' => $product->tax ? (float) $product->tax->tax_rate : null,
                    'tax_name' => $product->tax ? (string) $product->tax->tax_name : null,
                    'kitchen_station' => $product->kitchen_station,
                    'variants' => ($product->variants ?? collect())->map(function ($v) {
                        return [
                            'id' => (int) $v->id,
                            'name' => (string) $v->name,
                            'sku' => $v->sku,
                            'price' => (float) $v->price,
                        ];
                    })->values()->all(),
                    'addons' => ($product->addons ?? collect())->map(function ($a) {
                        return [
                            'id' => (int) $a->id,
                            'name' => (string) $a->name,
                            'price' => (float) $a->price,
                        ];
                    })->values()->all(),
                ];
            })->values(),
        ]);
    }

    public function order_sale(Request $request)
    {
        // dd($request->all());

        // Re-bind tenant DB from session before validating products (avoids cross-DB product_id mismatch).
        $this->ensureTenantDatabaseFromSession($request);

        $this->normalizeSaleOrderItems($request);

        if (empty($request->input('items'))) {
            $dbName = config('database.connections.' . config('database.default') . '.database');

            return response()->json([
                'status' => false,
                'message' => 'No valid products in the cart for this hotel database (' . $dbName . '). Refresh the page and try again.',
                'tenant_db' => $dbName,
            ], 422);
        }

        // Validate the request data
        $request->validate([
            'customer_id' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'payment_method' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|array',
            'tax.*.tax_name' => 'nullable|string',
            'tax.*.rate' => 'nullable|numeric|min:0',
            'tax.*.amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            // product_id existence already enforced in normalizeSaleOrderItems()
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            // Category may be missing / orphaned; do not block placing the order
            'items.*.categoryId' => 'nullable|integer',
            'total' => 'required|numeric|min:0',
        ]);

        // Safely cast table_id before starting a transaction
        $tableNoInt = null;
        if (!empty($request->table_id) && is_numeric($request->table_id)) {
            $tableNoInt = (int) $request->table_id;
        }

        // If this table already has an active running order, merge new items into it
        // instead of creating a second order (which would hide the first items on the table).
        if ($tableNoInt) {
            $existingActiveOrderId = Order::where('table_no', $tableNoInt)
                ->where('isDeleted', 0)
                ->where('table_status', 1)
                ->orderByDesc('id')
                ->value('id');

            if ($existingActiveOrderId) {
                $lineKey = static function ($productId, $variantId = null, $addons = null): string {
                    $addonIds = [];
                    foreach ((array) $addons as $a) {
                        if (is_array($a) && isset($a['id'])) {
                            $addonIds[] = (int) $a['id'];
                        } elseif (is_numeric($a)) {
                            $addonIds[] = (int) $a;
                        }
                    }
                    $addonIds = array_values(array_unique($addonIds));
                    sort($addonIds);
                    return ((int) $productId) . ':v' . ((int) ($variantId ?? 0)) . ':a' . implode('_', $addonIds);
                };

                $mergedByLine = [];

                $oldProductIds = OrderItem::where('order_id', $existingActiveOrderId)
                    ->pluck('product_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $aliveOldProductIds = $oldProductIds
                    ? array_fill_keys(
                        Product::whereIn('id', $oldProductIds)->pluck('id')->map(fn ($id) => (int) $id)->all(),
                        true
                    )
                    : [];

                foreach (OrderItem::where('order_id', $existingActiveOrderId)->get() as $old) {
                    // Skip lines whose product was deleted — product_id FK would block the update.
                    if (!isset($aliveOldProductIds[(int) $old->product_id])) {
                        continue;
                    }
                    $key = $lineKey($old->product_id, $old->variant_id, $old->addons);
                    $mergedByLine[$key] = [
                        'product_id' => $old->product_id,
                        'variant_id' => $old->variant_id,
                        'addon_ids' => collect((array) $old->addons)->map(function ($a) {
                            return is_array($a) ? (int) ($a['id'] ?? 0) : (int) $a;
                        })->filter()->values()->all(),
                        'addons' => $old->addons,
                        'quantity' => (int) $old->quantity,
                        'quantity_type_id' => $old->quantity_type_id,
                        'quantity_type123' => $old->quantity_type_id,
                        'categoryId' => PosPricingService::safeCategoryId($old->category_id),
                        'price' => (float) $old->price,
                        'total' => (float) $old->total_amount,
                    ];
                }

                foreach ($request->items ?? [] as $item) {
                    $pid = $item['product_id'] ?? null;
                    if (!$pid) {
                        continue;
                    }
                    $variantId = $item['variant_id'] ?? null;
                    $addonIds = $item['addon_ids'] ?? $item['addons'] ?? [];
                    $key = $lineKey($pid, $variantId, $addonIds);
                    $price = (float) ($item['price'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 0);

                    if (isset($mergedByLine[$key])) {
                        $mergedByLine[$key]['quantity'] += $qty;
                        $unit = (float) ($mergedByLine[$key]['price'] ?: $price);
                        $mergedByLine[$key]['price'] = $unit;
                        $mergedByLine[$key]['total'] = $mergedByLine[$key]['quantity'] * $unit;
                    } else {
                        $mergedByLine[$key] = [
                            'product_id' => $pid,
                            'variant_id' => $variantId,
                            'addon_ids' => $addonIds,
                            'addons' => $addonIds,
                            'quantity' => $qty,
                            'quantity_type_id' => $item['quantity_type_id'] ?? null,
                            'quantity_type123' => $item['quantity_type123'] ?? null,
                            'categoryId' => $item['categoryId'] ?? null,
                            'price' => $price,
                            'total' => (float) ($item['total'] ?? ($price * $qty)),
                        ];
                    }
                }

                $mergedItems = array_values($mergedByLine);
                $mergedSubtotal = array_sum(array_map(static fn ($row) => (float) $row['total'], $mergedItems));
                $discountPct = (float) ($request->discount ?? 0);
                $discountAmount = $mergedSubtotal * ($discountPct / 100);
                $mergedTotal = $mergedSubtotal - $discountAmount;

                // Keep tax contribution from the request totals when GST is applied
                $requestSubtotal = (float) ($request->subtotal ?? 0);
                $requestTotal = (float) ($request->total ?? 0);
                if ($requestSubtotal > 0 && $requestTotal > $requestSubtotal) {
                    $taxRatio = ($requestTotal - ($requestSubtotal * (1 - ($discountPct / 100)))) / max($requestSubtotal, 0.01);
                    $mergedTotal = ($mergedSubtotal - $discountAmount) + ($mergedSubtotal * max($taxRatio, 0));
                }

                $request->merge([
                    'update_id' => $existingActiveOrderId,
                    'items' => $mergedItems,
                    'subtotal' => $mergedSubtotal,
                    'total' => round($mergedTotal, 2),
                    'table_checkout' => $request->table_checkout ?? '',
                ]);

                return $this->update_sale_order($request);
            }
        }

        DB::beginTransaction();
        $userData = Auth::guard('api')->user();
        $userRole = $userData->role;
        if ($userRole == 'staff') {
            $branchIdToUse = $userData->branch_id;
        } elseif (!empty($request->selectedSubAdminId) && $userRole == 'admin') {
            $branchIdToUse = $request->selectedSubAdminId ?? null;
        } else {
            $branchIdToUse = $userData->id;
        }
        try {
            // Stock check (prepared food / negative selling never blocks)
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if (! $product) {
                    return response()->json([
                        'status' => false,
                        'message' => "Product not found: {$item['product_id']}",
                    ], 400);
                }
                if ($product->shouldBlockSaleForQty($item['quantity'] ?? 0)) {
                    return response()->json([
                        'status' => false,
                        'message' => "Insufficient stock for product ID: {$item['product_id']}",
                    ], 400);
                }
            }

            // Generate unique order number
            $orderNumber = now()->format('Ymd') . str_pad((Order::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);

            // tax_id on order is set from PosPricingService (product taxes only)
            $taxJson = json_encode([]);

            // Resolve customer ID
            $customer_id = null;
            $cust_name = trim($request->customer_id ?? '');
            $cust_phone = trim($request->customer_phone ?? '');

            if (!empty($cust_phone)) {
                // Try to find customer by phone first
                $existingCust = User::where('phone', $cust_phone)
                    ->where('isDeleted', 0)
                    ->first();
                if ($existingCust) {
                    $customer_id = $existingCust->id;
                    if (!empty($cust_name) && $existingCust->name === 'Default Customer') {
                        $existingCust->name = $cust_name;
                        $existingCust->save();
                    }
                }
            }

            if (!$customer_id && !empty($cust_name)) {
                if (is_numeric($cust_name)) {
                    $existingCust = User::where('id', $cust_name)->first();
                    if ($existingCust) {
                        $customer_id = $existingCust->id;
                    }
                }

                if (!$customer_id && !is_numeric($cust_name)) {
                    $newCust = User::create([
                        'name' => $cust_name,
                        'phone' => !empty($cust_phone) ? $cust_phone : null,
                        'role' => 'customer',
                        'status' => 1,
                        'branch_id' => $branchIdToUse,
                        'created_by' => $userData->id,
                    ]);
                    $customer_id = $newCust->id;

                    UserDetail::create([
                        'user_id' => $customer_id,
                        'address' => null,
                        'city' => null,
                        'country' => null,
                    ]);
                }
            }

            if (!$customer_id) {
                $defaultCustomer = User::where('role', 'customer')
                    ->where(function ($q) {
                        $q->where('name', 'Unregistered Customer')
                            ->orWhere('name', 'Default Customer');
                    })
                    ->orderByRaw("CASE WHEN name = 'Unregistered Customer' THEN 0 ELSE 1 END")
                    ->first();

                if (!$defaultCustomer) {
                    $defaultCustomer = User::create([
                        'name' => 'Unregistered Customer',
                        'role' => 'customer',
                        'status' => 1,
                        'branch_id' => $branchIdToUse,
                        'created_by' => $userData->id,
                    ]);
                    UserDetail::create([
                        'user_id' => $defaultCustomer->id,
                        'address' => null,
                        'city' => null,
                        'country' => null,
                    ]);
                }
                $customer_id = $defaultCustomer->id;
                if ($cust_name === '') {
                    $cust_name = $defaultCustomer->name ?: 'Unregistered Customer';
                }
            }

            if ($request->payment_method != 'pending') {
                // Check if partial payment
                if ($request->paid_type === 'partially' && isset($request->pending_amount) && $request->pending_amount > 0) {
                    $remain = $request->pending_amount;
                    $payment_status = 'partial';
                } else {
                    $remain = 0;
                    $payment_status = 'completed';
                }
            } else {
                $remain = $request->total;
                $payment_status = 'pending';
            }

            // Resolve trusted prices from DB (variants + add-ons)
            $allowItemDisc = $this->allowsItemLevelDiscount($branchIdToUse);
            $resolvedLines = [];
            foreach ($request->items as $item) {
                $resolvedLines[] = $this->posPricing()->resolveLine(
                    $this->sanitizeItemDiscounts((array) $item, $allowItemDisc)
                );
            }
            $withGst = ($request->gst_option === 'with' || $request->gst_option === 'with_gst');
            $gstOpts = $this->applyBillServiceTaxOverride(
                $this->gstOptionsForBranch($branchIdToUse),
                $request
            );
            $money = $this->posPricing()->calculateOrderTotals(
                $resolvedLines,
                (float) ($request->discount ?? 0),
                $withGst,
                $branchIdToUse,
                $gstOpts
            );
            $taxJson = json_encode($money['tax_ids'] ?? []);
            $trustedTotal = $money['total'];
            if ($request->payment_method != 'pending') {
                if ($request->paid_type === 'partially' && isset($request->pending_amount) && $request->pending_amount > 0) {
                    $remain = $request->pending_amount;
                } else {
                    $remain = 0;
                }
            } else {
                $remain = $trustedTotal;
            }

            // Create order
            $orderPayload = [
                'order_number'    => $orderNumber,
                'user_id'         => $customer_id,
                'customer_name'   => $cust_name ?: null,
                'customer_phone'  => $cust_phone ?: null,
                'payment_method'  => $request->payment_method,
                'discount'        => $request->discount ?? 0,
                'tax_id'          => $taxJson,
                'gst_option'      => $request->gst_option === 'with' ? 'with_gst' : 'without_gst',
                'total_amount'    => $trustedTotal,
                'remaining_amount' => $remain,
                'payment_status'  => $payment_status,
                'table_no'        => $tableNoInt,
                'type'            => $request->table_type ?? 'dinein',
                'order_type'      => $request->order_type ?? 'Delivery',
                'assigned_staff_id' => $request->assigned_staff_id,
                // Keep the order marked active while it is attached to a table.
                'table_status'    => $tableNoInt ? 1 : 0,
                'branch_id'       => $branchIdToUse,
                'created_by'      => $userData->id,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'tax_breakdown')) {
                $orderPayload['tax_breakdown'] = $money['tax_breakdown'] ?? [];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'gst_apply_mode')) {
                $orderPayload['gst_apply_mode'] = $gstOpts['gst_apply_mode'];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'overall_gst_rate')) {
                $orderPayload['overall_gst_rate'] = ($gstOpts['gst_apply_mode'] === 'overall')
                    ? $gstOpts['overall_gst_rate']
                    : null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'service_tax_rate')) {
                $orderPayload['service_tax_rate'] = $money['service_tax_rate'] ?? null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'service_tax_amount')) {
                $orderPayload['service_tax_amount'] = $money['service_tax_amount'] ?? 0;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'service_tax_name')) {
                $orderPayload['service_tax_name'] = $money['service_tax_name'] ?? null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'kot_remarks')) {
                $kotRemarks = trim((string) ($request->kot_remarks ?? $request->kot_remark ?? ''));
                $orderPayload['kot_remarks'] = $kotRemarks !== '' ? $kotRemarks : null;
            }
            $order = Order::create($orderPayload);

            // Insert order items and reduce finished-good stock when track_inventory is on
            foreach ($resolvedLines as $line) {
                $product = $line['product'];
                $trackInventory = $product->tracksInventory();

                if ($trackInventory) {
                    $beforeQty = (int) $product->quantity;
                    // Negative selling: allow quantity to go below zero
                    $product->quantity = $beforeQty - (int) $line['quantity'];
                    if (! $product->isPreparedFood() && (int) $product->quantity <= 0) {
                        $product->availablility = 'out_stock';
                    } elseif ((int) $product->quantity > 0) {
                        $product->availablility = 'in_stock';
                    }
                    $product->save();

                    $lastInventory = ProductInventory::where('product_id', $product->id)
                        ->orderBy('id', 'desc')
                        ->first();
                    ProductInventory::create([
                        'product_id' => $product->id,
                        'initial_stock' => $lastInventory->initial_stock ?? $beforeQty,
                        'current_stock' => $product->quantity,
                        'branch_id' => $branchIdToUse,
                        'create_by' => $userData->id,
                        'type' => 'Sale',
                        'date' => now(),
                    ]);
                    try {
                        app(\App\Services\StockLedgerService::class)->record(
                            $product, 'out', (float) $line['quantity'], 'Sale', 'order', $order->id,
                            $branchIdToUse, $userData->id
                        );
                    } catch (\Throwable $e) {
                    }
                }

                OrderItem::create(array_merge([
                    'order_id' => $order->id,
                    'user_id' => $customer_id ?? null,
                    'category_id' => PosPricingService::safeCategoryId($line['category_id'] ?? null),
                    'product_id' => $product->id,
                    'variant_id' => $line['variant_id'],
                    'addons' => $line['addons'] ?: null,
                    'quantity' => $line['quantity'],
                    'quantity_type_id' => $line['quantity_type_id'] ?? null,
                    'price' => $line['unit_price'],
                    'unit_price' => $line['unit_price'],
                    'item_discount_percent' => $line['item_discount_percent'] ?? 0,
                    'item_discount_amount' => $line['item_discount_amount'] ?? 0,
                    'total_amount' => $line['line_total'],
                    'branch_id' => $branchIdToUse,
                    'created_by' => $userData->id,
                ], PosPricingService::applyOverallSnapshotToLineFields(
                    PosPricingService::orderItemSnapshotFields($line),
                    $gstOpts
                )));
            }
            if ($tableNoInt) {
                $this->syncTableStatus($tableNoInt);
            }
            // dd($request->type);
            if ($request->payment_method != 'pending') {

                if ($request->payment_method === 'cash+online') {

                    // Cash entry
                    if ($request->cash_amount > 0) {
                        PaymentStore::create([
                            'user_id' => $customer_id,
                            'order_id' => $order->id,
                            'payment_amount' => $request->cash_amount,
                            'payment_date' => now(),
                            'payment_method' => 'cash',
                            'payment_type' => $request->paid_type ?? 'full',
                            'cash_amount' => $request->cash_amount,
                            'upi_amount' => 0,
                            'remaining_amount' => $request->pending_amount ?? 0,
                            'bank_id' => $request->bank_id ?? null,
                            'remarks' => $request->debit_remark ?? null,
                            'emi_month' => null,
                            'jobcard_id' => 0,
                            'isDeleted' => 0,
                            'created_at' => now(),
                            'status' => 'credit'
                        ]);
                    }

                    // Online entry
                    if ($request->online_amount > 0) {
                        PaymentStore::create([
                            'user_id' => $customer_id,
                            'order_id' => $order->id,
                            'payment_amount' => $request->online_amount,
                            'payment_date' => now(),
                            'payment_method' => 'online',
                            'payment_type' => $request->paid_type ?? 'full',
                            'cash_amount' => 0,
                            'upi_amount' => $request->online_amount,
                            'remaining_amount' => $request->pending_amount ?? 0,
                            'bank_id' => $request->bank_id ?? null,
                            'remarks' => $request->debit_remark ?? null,
                            'emi_month' => null,
                            'jobcard_id' => 0,
                            'isDeleted' => 0,
                            'created_at' => now(),
                            'status' => 'credit'
                        ]);
                    }
                } else {
                    // Normal single payment entry
                    $payAmt = $request->paid_type === 'partially'
                        ? ($request->enter_amount ?? $request->total ?? 0)
                        : ($request->total ?? 0);

                    // For a plain cash payment, cash_amount should equal the payment amount
                    $cashAmt = ($request->payment_method === 'cash')
                        ? ($request->cash_amount > 0 ? $request->cash_amount : $payAmt)
                        : ($request->cash_amount ?? 0);

                    $bankIdForPayment = $request->bank_id ?? null;
                    if ($request->payment_method === 'scan') {
                        $shopSetting = \App\Models\Setting::where('branch_id', $branchIdToUse)->first();
                        if ($shopSetting) {
                            $matchingBank = \App\Models\BankMaster::where('branch_id', $branchIdToUse)
                                ->where(function($q) use ($shopSetting) {
                                    if ($shopSetting->ac_no) {
                                        $q->where('account_number', $shopSetting->ac_no);
                                    }
                                    if ($shopSetting->bank_name) {
                                        $q->orWhere('bank_name', $shopSetting->bank_name);
                                    }
                                })
                                ->first();
                            if ($matchingBank) {
                                $bankIdForPayment = $matchingBank->id;
                            }
                        }
                    }

                    PaymentStore::create([
                        'user_id' => $customer_id,
                        'order_id' => $order->id,
                        'payment_amount' => $payAmt,
                        'payment_date' => now(),
                        'payment_method' => $request->payment_method,
                        'payment_type' => $request->paid_type ?? 'fully',
                        'cash_amount' => $cashAmt,
                        'upi_amount' => $request->upi_amount ?? 0,
                        'remaining_amount' => $request->pending_amount ?? 0,
                        'bank_id' => $bankIdForPayment,
                        'remarks' => $request->debit_remark ?? null,
                        'emi_month' => null,
                        'jobcard_id' => 0,
                        'isDeleted' => 0,
                        'created_at' => now(),
                        'status' => 'credit'
                    ]);
                }
            }

            DB::commit();

            try {
                $this->recipeStock()->consumeForOrder($order->fresh());
            } catch (\Throwable $e) {
                Log::error('Recipe consume error: ' . $e->getMessage());
            }
            
            try {
                $shopSetting = \App\Models\Setting::where('branch_id', $branchIdToUse)->first();
                if ($shopSetting && $shopSetting->send_mail) {
                    if ($customer_id) {
                        $customer = \App\Models\User::find($customer_id);
                        if ($customer && $customer->email) {
                            $smtpSetting = \App\Models\SmtpSetting::where('branch_id', $branchIdToUse)->where('status', 1)->first();
                            if (!$smtpSetting) {
                                // fallback to main branch if sub-admin has none
                                $smtpSetting = \App\Models\SmtpSetting::where('branch_id', $userData->id)->where('status', 1)->first();
                            }
                            
                            if ($smtpSetting && $smtpSetting->password) {
                                config([
                                    'mail.mailers.smtp.host'       => $smtpSetting->host,
                                    'mail.mailers.smtp.port'       => $smtpSetting->port,
                                    'mail.mailers.smtp.username'   => $smtpSetting->username,
                                    'mail.mailers.smtp.password'   => decrypt($smtpSetting->password),
                                    'mail.mailers.smtp.encryption' => $smtpSetting->encryption,
                                    'mail.from.address'            => $smtpSetting->from_address ?? $smtpSetting->username,
                                    'mail.from.name'               => $smtpSetting->from_name ?? config('app.name'),
                                ]);
                            }

                            \Illuminate\Support\Facades\Mail::to($customer->email)->send(new \App\Mail\OrderCreatedMail($order, $customer->name));
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Mail sending error: ' . $e->getMessage());
            }

            try {
                // WhatsApp Message Logic
                $shopSetting = \App\Models\Setting::where('branch_id', $branchIdToUse)->first();
                if ($shopSetting && $shopSetting->customer_whatsapp_message) {
                    $phone = $customer_phone ?? ($customer_id ? \App\Models\User::find($customer_id)->phone ?? null : null);
                    if ($phone) {
                        $fbConfig = \App\Models\FacebookAppConfiguration::where('branch_id', $branchIdToUse)
                            ->where('isDeleted', 0)
                            ->first();
                        
                        if (!$fbConfig) {
                            $fbConfig = \App\Models\FacebookAppConfiguration::where('branch_id', $userData->id)
                                ->where('isDeleted', 0)
                                ->first();
                        }

                        if ($fbConfig && $fbConfig->phone_number_id && $fbConfig->access_token) {
                            $phoneNumber = preg_replace('/[^0-9]/', '', $phone);
                            if (strlen($phoneNumber) == 10) {
                                $phoneNumber = '91' . $phoneNumber; // default to India if 10 digits
                            }

                            $template = \App\Models\WhatsAppMessageTemplate::where('name', 'subscription_invoice_pdf')
                                ->where('on_off', 'active')
                                ->where('isDeleted', 0)
                                ->first();

                            if ($template) {
                                $businessName = $shopSetting->name ?? config('app.name');
                                $custName = $cust_name ?? ($customer_id ? \App\Models\User::find($customer_id)->name ?? 'Customer' : 'Customer');
                                // GENERATE PDF INTERNALLY FOR LOCALHOST UPLOAD
                                $orderItems = \App\Models\OrderItem::with('product', 'quantityType')->where('order_id', $order->id)->get();
                                $subtotal = $orderItems->sum('total_amount');
                                $discountPercent = $order->discount ?? 0;
                                $discountAmount = ($discountPercent / 100) * $subtotal;
                                $afterDiscount = $subtotal - $discountAmount;
                                
                                $taxDetails = [];
                                if ($order->gst_option === 'with_gst') {
                                    $taxIds = is_string($order->tax_id) ? json_decode($order->tax_id, true) : $order->tax_id;
                                    if (is_array($taxIds) && !empty($taxIds)) {
                                        $taxes = \App\Models\TaxRate::whereIn('id', $taxIds)->get();
                                        foreach ($taxes as $tax) {
                                            $taxAmount = ($tax->tax_rate / 100) * $afterDiscount;
                                            $taxDetails[] = [
                                                'name' => $tax->tax_name,
                                                'rate' => $tax->tax_rate,
                                                'amount' => $taxAmount,
                                                'formatted_amount' => number_format($taxAmount, 2)
                                            ];
                                        }
                                    }
                                }
                                $finalTotal = $afterDiscount + collect($taxDetails)->sum('amount');
                                $paidAmount = \App\Models\PaymentStore::where('order_id', $order->id)->where('isDeleted', 0)->sum('payment_amount');
                                $pendingAmount = $order->remaining_amount ?? 0;

                                $formatCurrency = function ($amount) use ($shopSetting) {
                                    $amount = number_format((float)$amount, 2);
                                    $symbol = $shopSetting->currency_symbol ?? '₹';
                                    $position = $shopSetting->currency_position ?? 'left';
                                    return $position === 'right' ? $amount . $symbol : $symbol . $amount;
                                };

                                $pdfData = [
                                    'view_id' => $order->id,
                                    'sales' => $order,
                                    'setting' => $shopSetting,
                                    'orderItems' => $orderItems,
                                    'salesItems' => $orderItems,
                                    'customer' => ['name' => $custName, 'email' => '', 'phone' => $phoneNumber, 'address' => 'arga'],
                                    'subtotal' => $formatCurrency($subtotal),
                                    'discountPercent' => $discountPercent,
                                    'discountAmount' => $formatCurrency($discountAmount),
                                    'afterDiscount' => (float) $afterDiscount,
                                    'formattedAfterDiscount' => $formatCurrency($afterDiscount),
                                    'finalTotal' => $formatCurrency($finalTotal),
                                    'taxDetails1' => $taxDetails,
                                    'paidAmount' => $paidAmount,
                                    'pendingAmount' => $pendingAmount,
                                ];

                                if (isset($shopSetting->invoice_size) && $shopSetting->invoice_size === 'small') {
                                    $pdf = make_thermal_invoice_pdf($viewName, $pdfData, $orderItems ?? 1, 226.77);
                                } else {
                                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $pdfData);
                                    $pdf->setPaper('A4', 'portrait');
                                }
                                $pdfContent = $pdf->output();
                                $fileName = 'invoice_' . $order->id . '_' . date('Ymd_His') . '.pdf';

                                // Upload to WhatsApp Media API
                                $mediaUrl = "https://graph.facebook.com/v23.0/{$fbConfig->phone_number_id}/media";
                                $mediaResponse = \Illuminate\Support\Facades\Http::withToken($fbConfig->access_token)
                                    ->attach('file', $pdfContent, $fileName)
                                    ->post($mediaUrl, [
                                        'messaging_product' => 'whatsapp'
                                    ]);

                                if ($mediaResponse->successful()) {
                                    $mediaId = $mediaResponse->json('id');
                                    $pdfLink = url('/sales/invoice/pdf/' . $order->id); // For text body display

                                    $payload = [
                                        "messaging_product" => "whatsapp",
                                        "to" => $phoneNumber,
                                        "type" => "template",
                                        "template" => [
                                            "name" => $template->name,
                                            "language" => [
                                                "code" => $template->language ?? "en_US"
                                            ],
                                            "components" => [
                                                [
                                                    "type" => "header",
                                                    "parameters" => [
                                                        [
                                                            "type" => "document",
                                                            "document" => [
                                                                "id" => $mediaId,
                                                                "filename" => $fileName
                                                            ]
                                                        ]
                                                    ]
                                                ],
                                            [
                                                "type" => "body",
                                                "parameters" => [
                                                    [ "type" => "text", "text" => $custName ],
                                                    [ "type" => "text", "text" => $order->order_number ],
                                                    [ "type" => "text", "text" => $businessName ],
                                                    [ "type" => "text", "text" => $order->order_number ],
                                                    [ "type" => "text", "text" => strval($order->total_amount) ],
                                                    [ "type" => "text", "text" => ucfirst($order->payment_status) ],
                                                    [ "type" => "text", "text" => $pdfLink ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ];

                                $url = "https://graph.facebook.com/v23.0/{$fbConfig->phone_number_id}/messages";
                                $response = \Illuminate\Support\Facades\Http::withToken($fbConfig->access_token)->post($url, $payload);
                                
                                if ($response->failed()) {
                                    Log::error('WhatsApp API Error: ' . $response->body() . ' | Payload: ' . json_encode($payload));
                                } else {
                                    Log::info('WhatsApp API Success: ' . $response->body());
                                }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('WhatsApp sending error: ' . $e->getMessage());
            }

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order->id,
                'total_amount' => $order->total_amount,
                'table_id' => $tableNoInt,
                'table_status' => $tableNoInt ? Table_no::STATUS_RUNNING : null,
                'table_status_class' => $tableNoInt ? 'running-table' : null,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order placement error: ' . $e->getMessage());

            $friendly = 'Failed to place order. Please try again.';
            if (str_contains($e->getMessage(), 'order_items_category_id_foreign')) {
                $friendly = 'Failed to place order: a product category is missing. Refresh and try again.';
            } elseif (str_contains($e->getMessage(), 'order_items_product_id_foreign')) {
                $friendly = 'Failed to place order: a product was deleted. Remove it from the cart and try again.';
            }

            return response()->json([
                'status' => false,
                'message' => $friendly,
            ], 500);
        }
    }

    public function update_sale_order(Request $request)
    {
        // dd($request->all());
        $order_id = $request->update_id;

        $this->ensureTenantDatabaseFromSession($request);
        $this->normalizeSaleOrderItems($request);

        if (empty($request->input('items'))) {
            $dbName = config('database.connections.' . config('database.default') . '.database');

            return response()->json([
                'status' => false,
                'message' => 'No valid products in the cart for this hotel database (' . $dbName . '). Refresh the page and try again.',
                'tenant_db' => $dbName,
            ], 422);
        }

        // Validate request.
        // product_id / category_id FK safety handled in normalizeSaleOrderItems().
        $request->validate([
            'customer_id' => 'nullable|string|max:255',
            'payment_method' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|array',
            'tax.*.tax_name' => 'nullable|string',
            'tax.*.rate' => 'nullable|numeric|min:0',
            'tax.*.amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'items.*.categoryId' => 'nullable|integer',
            'total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $userData = Auth::guard('api')->user();
            $userRole = $userData->role;

            if ($userRole == 'staff') {
                $branchIdToUse = $userData->branch_id;
            } elseif (!empty($request->selectedSubAdminId) && $userRole == 'admin') {
                $branchIdToUse = $request->selectedSubAdminId;
            } else {
                $branchIdToUse = $userData->id;
            }

            // Resolve customer ID
            $customer_id = null;
            $cust_name = trim($request->customer_id ?? '');
            $cust_phone = trim($request->customer_phone ?? '');

            if (!empty($cust_phone)) {
                // Try to find customer by phone first
                $existingCust = User::where('role', 'customer')
                    ->where('phone', $cust_phone)
                    ->where('isDeleted', 0)
                    ->first();
                if ($existingCust) {
                    $customer_id = $existingCust->id;
                    if (!empty($cust_name) && $existingCust->name === 'Default Customer') {
                        $existingCust->name = $cust_name;
                        $existingCust->save();
                    }
                }
            }

            if (!$customer_id && !empty($cust_name)) {
                if (is_numeric($cust_name)) {
                    $existingCust = User::where('id', $cust_name)->where('role', 'customer')->first();
                    if ($existingCust) {
                        $customer_id = $existingCust->id;
                    }
                }

                if (!$customer_id && !is_numeric($cust_name)) {
                    $newCust = User::create([
                        'name' => $cust_name,
                        'phone' => !empty($cust_phone) ? $cust_phone : null,
                        'role' => 'customer',
                        'status' => 1,
                        'branch_id' => $branchIdToUse,
                        'created_by' => $userData->id,
                    ]);
                    $customer_id = $newCust->id;

                    UserDetail::create([
                        'user_id' => $customer_id,
                        'address' => null,
                        'city' => null,
                        'country' => null,
                    ]);
                }
            }

            if (!$customer_id) {
                $defaultCustomer = User::where('role', 'customer')
                    ->where(function ($q) {
                        $q->where('name', 'Unregistered Customer')
                            ->orWhere('name', 'Default Customer');
                    })
                    ->orderByRaw("CASE WHEN name = 'Unregistered Customer' THEN 0 ELSE 1 END")
                    ->first();

                if (!$defaultCustomer) {
                    $defaultCustomer = User::create([
                        'name' => 'Unregistered Customer',
                        'role' => 'customer',
                        'status' => 1,
                        'branch_id' => $branchIdToUse,
                        'created_by' => $userData->id,
                    ]);
                    UserDetail::create([
                        'user_id' => $defaultCustomer->id,
                        'address' => null,
                        'city' => null,
                        'country' => null,
                    ]);
                }
                $customer_id = $defaultCustomer->id;
                if ($cust_name === '') {
                    $cust_name = $defaultCustomer->name ?: 'Unregistered Customer';
                }
            }

            $order = Order::findOrFail($order_id);

            // Reverse any prior recipe consumption before rewriting lines
            try {
                $this->recipeStock()->reverseForOrder($order);
            } catch (\Throwable $e) {
                Log::error('Recipe reverse error: ' . $e->getMessage());
            }

            // Restore stock first from OLD items
            $oldItems = OrderItem::with(['product', 'variant'])->where('order_id', $order_id)->get();

            // Petpooja-style KOT: this Update ticket = only qty changes vs previous table order
            $hasKotPrintedCol = \Illuminate\Support\Facades\Schema::hasColumn('order_items', 'kot_printed_qty');
            $oldKotPrinted = [];
            $oldKotQty = [];
            $oldKotMeta = [];
            foreach ($oldItems as $old) {
                $kotKey = ((int) $old->product_id) . ':' . ((int) ($old->variant_id ?? 0));
                $printedQty = $hasKotPrintedCol ? (int) ($old->kot_printed_qty ?? 0) : 0;
                $oldQty = (int) ($old->quantity ?? 0);
                $oldKotPrinted[$kotKey] = $printedQty;
                $oldKotQty[$kotKey] = $oldQty;
                $oldAddonLines = method_exists($old, 'addonLines') ? $old->addonLines() : [];
                $oldKotMeta[$kotKey] = [
                    'product_id' => (int) $old->product_id,
                    'variant_id' => $old->variant_id ? (int) $old->variant_id : null,
                    'name' => optional($old->product)->name ?? 'Item',
                    'variant_name' => method_exists($old, 'variantLabel')
                        ? $old->variantLabel()
                        : (optional($old->variant)->name ?? null),
                    'addons_text' => $oldAddonLines ? implode(', ', $oldAddonLines) : null,
                    'station' => optional($old->product)->kitchen_station ?? 'General',
                    'qty_printed' => $printedQty,
                    'qty' => $oldQty,
                ];
            }

            foreach ($oldItems as $old) {
                $product = Product::find($old->product_id);
                $trackInventory = $product && array_key_exists('track_inventory', $product->getAttributes())
                    ? (bool) $product->track_inventory
                    : true;
                if ($product && $trackInventory) {
                    $product->quantity += $old->quantity;
                    $product->save();

                    ProductInventory::create([
                        'product_id' => $product->id,
                        'initial_stock' => $product->quantity + $old->quantity,
                        'current_stock' => $product->quantity,
                        'branch_id' => $branchIdToUse,
                        'create_by' => $userData->id,
                        'type' => 'Order Updated (Restore)',
                        'date' => now(),
                    ]);
                }
            }

            // Now delete old order items
            OrderItem::where('order_id', $order_id)->delete();

            $allowItemDisc = $this->allowsItemLevelDiscount($branchIdToUse);
            $resolvedLines = [];
            foreach ($request->items as $item) {
                $resolvedLines[] = $this->posPricing()->resolveLine(
                    $this->sanitizeItemDiscounts((array) $item, $allowItemDisc)
                );
            }

            // Check updated stock availability (prepared food / oversell never blocks)
            foreach ($resolvedLines as $line) {
                $p = $line['product'];
                if ($p->shouldBlockSaleForQty($line['quantity'] ?? 0)) {
                    return response()->json([
                        'status' => false,
                        'message' => "Insufficient stock for Product ID: {$p->id}",
                    ], 400);
                }
            }

            $withGst = ($request->gst_option === 'with' || $request->gst_option === 'with_gst');
            $gstOpts = $this->applyBillServiceTaxOverride(
                $this->gstOptionsForBranch($branchIdToUse),
                $request
            );
            $money = $this->posPricing()->calculateOrderTotals(
                $resolvedLines,
                (float) ($request->discount ?? 0),
                $withGst,
                $branchIdToUse,
                $gstOpts
            );
            $taxJson = !empty($money['tax_ids']) ? json_encode($money['tax_ids']) : json_encode([]);
            $trustedTotal = $money['total'];

            if ($request->payment_method != 'pending') {
                $remain = 0;
                $payment_status = 'completed';
            } else {
                $remain = $trustedTotal;
                $payment_status = 'pending';
            }

            $isCheckout = ($request->table_checkout ?? '') === 'checkout';
            $fullySettled = $isCheckout && $payment_status === 'completed' && (float) $remain <= 0;

            // Update order
            $orderUpdate = [
                'user_id' => $customer_id,
                'payment_method' => $request->payment_method,
                'discount' => $request->discount ?? 0,
                'tax_id' => $taxJson,
                'gst_option' => $request->gst_option === 'with' ? 'with_gst' : 'without_gst',
                'total_amount' => $trustedTotal,
                'remaining_amount' => $remain,
                'payment_status' => $payment_status,
                'table_no' => $request->table_id ?? null,
                'type' => $request->table_type ?? 'dinein',
                // Free table only when checkout is fully paid; otherwise keep active (bill-in-progress = yellow).
                'table_status' => $fullySettled ? 0 : 1,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'bill_printed')) {
                // Checkout / print bill → yellow; adding items again → green occupied
                $orderUpdate['bill_printed'] = $isCheckout ? 1 : 0;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'tax_breakdown')) {
                $orderUpdate['tax_breakdown'] = $money['tax_breakdown'] ?? [];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'gst_apply_mode')) {
                $orderUpdate['gst_apply_mode'] = $gstOpts['gst_apply_mode'];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'overall_gst_rate')) {
                $orderUpdate['overall_gst_rate'] = ($gstOpts['gst_apply_mode'] === 'overall')
                    ? $gstOpts['overall_gst_rate']
                    : null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'service_tax_rate')) {
                $orderUpdate['service_tax_rate'] = $money['service_tax_rate'] ?? null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'service_tax_amount')) {
                $orderUpdate['service_tax_amount'] = $money['service_tax_amount'] ?? 0;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'service_tax_name')) {
                $orderUpdate['service_tax_name'] = $money['service_tax_name'] ?? null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'kot_remarks') && $request->exists('kot_remarks')) {
                $kotRemarks = trim((string) ($request->kot_remarks ?? ''));
                $orderUpdate['kot_remarks'] = $kotRemarks !== '' ? $kotRemarks : null;
            }
            $order->update($orderUpdate);

            // Insert updated order items & reduce stock
            $keptKotKeys = [];
            $fireTicket = []; // exact lines for THIS Update's KOT (Petpooja: only what changed now)
            foreach ($resolvedLines as $line) {
                $product = $line['product'];
                $trackInventory = $product->tracksInventory();

                if ($trackInventory) {
                    $beforeQty = (int) $product->quantity;
                    // Negative selling: allow quantity to go below zero
                    $product->quantity = $beforeQty - (int) $line['quantity'];
                    if (! $product->isPreparedFood() && (int) $product->quantity <= 0) {
                        $product->availablility = 'out_stock';
                    } elseif ((int) $product->quantity > 0) {
                        $product->availablility = 'in_stock';
                    }
                    $product->save();

                    ProductInventory::create([
                        'product_id' => $product->id,
                        'initial_stock' => $beforeQty,
                        'current_stock' => $product->quantity,
                        'branch_id' => $branchIdToUse,
                        'create_by' => $userData->id,
                        'type' => 'Order Updated (Sale)',
                        'date' => now(),
                    ]);
                    try {
                        app(\App\Services\StockLedgerService::class)->record(
                            $product, 'out', (float) $line['quantity'], 'Sale', 'order', $order->id,
                            $branchIdToUse, $userData->id
                        );
                    } catch (\Throwable $e) {
                    }
                }

                $kotKey = ((int) $product->id) . ':' . ((int) ($line['variant_id'] ?? 0));
                $keptKotKeys[$kotKey] = true;
                $newQty = (int) $line['quantity'];
                $prevQty = (int) ($oldKotQty[$kotKey] ?? 0);
                $prevPrinted = (int) ($oldKotPrinted[$kotKey] ?? 0);
                // Items already on the table before this save are treated as already ticketed
                // so KOT only shows what changed on THIS Update (e.g. only kadhi-khichdi).
                $printedCarry = max($prevPrinted, $prevQty);

                $itemPayload = array_merge([
                    'order_id' => $order->id,
                    'user_id' => $customer_id,
                    'category_id' => PosPricingService::safeCategoryId($line['category_id'] ?? null),
                    'product_id' => $product->id,
                    'variant_id' => $line['variant_id'],
                    'addons' => $line['addons'] ?: null,
                    'quantity' => $line['quantity'],
                    'quantity_type_id' => $line['quantity_type_id'] ?? null,
                    'price' => $line['unit_price'],
                    'unit_price' => $line['unit_price'],
                    'item_discount_percent' => $line['item_discount_percent'] ?? 0,
                    'item_discount_amount' => $line['item_discount_amount'] ?? 0,
                    'total_amount' => $line['line_total'],
                    'branch_id' => $branchIdToUse,
                    'created_by' => $userData->id,
                ], PosPricingService::applyOverallSnapshotToLineFields(
                    PosPricingService::orderItemSnapshotFields($line),
                    $gstOpts
                ));
                if ($hasKotPrintedCol) {
                    $itemPayload['kot_printed_qty'] = $printedCarry;
                }
                OrderItem::create($itemPayload);

                $diff = $newQty - $prevQty;
                if ($diff !== 0) {
                    $addonNames = [];
                    foreach (($line['addons'] ?? []) as $a) {
                        if (is_array($a) && ! empty($a['name'])) {
                            $addonNames[] = (string) $a['name'];
                        }
                    }
                    $fireTicket[] = [
                        'product_id' => (int) $product->id,
                        'variant_id' => $line['variant_id'] ? (int) $line['variant_id'] : null,
                        'variant_name' => $line['variant_name'] ?? null,
                        'addons_text' => $addonNames ? implode(', ', $addonNames) : null,
                        'name' => $product->name ?? 'Item',
                        'station' => $product->kitchen_station ?? 'General',
                        'qty' => abs($diff),
                        'action' => $diff > 0 ? 'new' : 'cancel',
                    ];
                }
            }

            // Fully removed items → CANCEL on this Update's KOT
            foreach ($oldKotMeta as $kotKey => $meta) {
                if (!empty($keptKotKeys[$kotKey])) {
                    continue;
                }
                $removedQty = (int) ($meta['qty'] ?? 0);
                if ($removedQty <= 0) {
                    continue;
                }
                $fireTicket[] = [
                    'product_id' => $meta['product_id'],
                    'variant_id' => $meta['variant_id'],
                    'variant_name' => $meta['variant_name'] ?? null,
                    'addons_text' => $meta['addons_text'] ?? null,
                    'name' => $meta['name'],
                    'station' => $meta['station'] ?? 'General',
                    'qty' => $removedQty,
                    'action' => 'cancel',
                ];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'kot_pending_cancels')) {
                $order->kot_pending_cancels = $fireTicket;
                $order->save();
            }

            // Delete old payment entries
            PaymentStore::where('order_id', $order->id)->delete();

            // Insert updated payment entries
            if ($isCheckout) {
                $paymentType = $request->paid_type ?? (($request->payment_method === 'cash+online') ? 'partially' : 'fully');
                if ($request->payment_method != 'pending') {

                    if ($request->payment_method === 'cash+online') {

                        if ($request->cash_amount > 0) {
                            PaymentStore::create([
                                'user_id' => $customer_id,
                                'order_id' => $order->id,
                                'payment_amount' => $request->cash_amount,
                                'payment_method' => 'cash',
                                'payment_type' => $paymentType,
                                'cash_amount' => $request->cash_amount,
                                'upi_amount' => 0,
                                'payment_date' => now(),
                                'status' => 'credit'
                            ]);
                        }

                        if ($request->online_amount > 0) {
                            PaymentStore::create([
                                'user_id' => $customer_id,
                                'order_id' => $order->id,
                                'payment_amount' => $request->online_amount,
                                'payment_method' => 'online',
                                'payment_type' => $paymentType,
                                'cash_amount' => 0,
                                'upi_amount' => $request->online_amount,
                                'payment_date' => now(),
                                'status' => 'credit'
                            ]);
                        }

                    } else {
                        // For a plain cash payment, cash_amount should equal the payment amount
                        $updateCashAmt = ($request->payment_method === 'cash')
                            ? ($request->cash_amount > 0 ? $request->cash_amount : $request->total)
                            : ($request->cash_amount ?? 0);

                        PaymentStore::create([
                            'user_id' => $customer_id,
                            'order_id' => $order->id,
                            'payment_amount' => $request->total,
                            'payment_method' => $request->payment_method,
                            'payment_type' => $paymentType,
                            'cash_amount' => $updateCashAmt,
                            'upi_amount' => $request->upi_amount ?? 0,
                            'payment_date' => now(),
                            'status' => 'credit'
                        ]);
                    }
                }
            }

            // Sync floor map colour (green occupied / yellow bill printed / blank free)
            $tableSync = ['status' => null, 'class' => null];
            if (! empty($request->table_id) && is_numeric($request->table_id)) {
                $tableSync = $this->syncTableStatus((int) $request->table_id);
            }

            DB::commit();

            try {
                $this->recipeStock()->consumeForOrder($order->fresh());
            } catch (\Throwable $e) {
                Log::error('Recipe consume on update error: ' . $e->getMessage());
            }

            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully!',
                'order_id' => $order->id,
                'total_amount' => $order->total_amount,
                'table_id' => $request->table_id ?? null,
                'table_status' => $tableSync['status'] ?? null,
                'table_status_class' => $tableSync['class'] ?? null,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update order. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function get_orders(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;

        // ✅ Prefer request input over route param
        $selectedSubAdminID = $request->input('selectedSubAdminId');

        // ✅ Build the query
        $query = Order::with([
            'user:id,name,phone',
            'orderItems:id,order_id', // Only fetch id and order_id from orderItems (no created_at)
            'creator:id,name',
        ])
            ->where('isDeleted', 0)
            // ✅ Add subquery to check if payments exist
            ->withCount([
                'payments as has_payment' => function ($q) {
                    $q->where('isDeleted', 0);
                },
            ]);
        // Staff see the same branch-wide order list as admin (not only own created_by)
        if ($role === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif ($role === 'admin' && $selectedSubAdminID) {
            $query->where('branch_id', $selectedSubAdminID);
        } elseif ($role === 'staff') {
            $query->where('branch_id', $user->branch_id ?: $userId);
        } else {
            $query->where('branch_id', $userId);
        }
        // ✅ Apply date filter
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('created_at', $request->month);
            }
        }

        // ✅ Apply sorting
        $query->orderBy('created_at', 'desc');

        // ✅ Now get the results
        $get_orders = $query->get();

        // ✅ Add created_date (formatted date from orders.created_at)
        $get_orders->transform(function ($order) {
            $order->created_date = $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : null;
            return $order;
        });
        if ($role === 'staff') {
            $userId = $user->branch_id;
        }
        // ✅ Currency settings
        $settings = DB::table('settings')->where('branch_id', $userId)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // $get_orders->transform(function ($order) {
        //     $order->created_date    = $order->created_at ? $order->created_at->format('Y-m-d') : null;
        //     $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);
        //     return $order;
        // });

        $get_orders->transform(function ($order) {
            // Convert UTC timestamp to IST and format
            $order->created_date = $order->created_at
                ? $order->created_at->format('d-M-Y h:i A')
                : null;

            $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);
            return $order;
        });
        // dd($get_orders);
        return response()->json([
            'status' => true,
            'message' => 'Orders fetched successfully',
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data' => $get_orders,
        ]);
    }

    public function get_orders_invoice(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;

        // ✅ Prefer request input over route param
        $selectedSubAdminID = $request->input('selectedSubAdminId');

        // ✅ Build the query
        $query = Order::with([
            'user:id,name,phone',
            'orderItems:id,order_id', // Only fetch id and order_id from orderItems (no created_at)
            'creator:id,name',
        ])
            ->where('isDeleted', 0)
            ->where('table_no', $request->table_id ?? 0)
            // ✅ Add subquery to check if payments exist
            ->withCount([
                'payments as has_payment' => function ($q) {
                    $q->where('isDeleted', 0);
                },
            ]);
        if ($role === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif ($role === 'admin' && $selectedSubAdminID) {
            $query->where('branch_id', $selectedSubAdminID);
        } elseif ($role === 'staff') {
            $query->where('branch_id', $user->branch_id ?: $userId);
        } else {
            $query->where('branch_id', $userId);
        }
        // ✅ Apply date filter
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('created_at', $request->month);
            }
        }

        // ✅ Apply sorting
        $query->orderBy('created_at', 'desc');

        // ✅ Now get the results
        $get_orders = $query->get();

        // ✅ Add created_date (formatted date from orders.created_at)
        $get_orders->transform(function ($order) {
            $order->created_date = $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : null;
            return $order;
        });
        if ($role === 'staff') {
            $userId = $user->branch_id;
        }
        // ✅ Currency settings
        $settings = DB::table('settings')->where('branch_id', $userId)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // $get_orders->transform(function ($order) {
        //     $order->created_date    = $order->created_at ? $order->created_at->format('Y-m-d') : null;
        //     $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);
        //     return $order;
        // });

        $get_orders->transform(function ($order) {
            // Convert UTC timestamp to IST and format
            $order->created_date = $order->created_at
                ? $order->created_at->format('d-M-Y h:i A')
                : null;

            $order->invoice_pdf_url = url("/sales/invoice/pdf/" . $order->id);
            return $order;
        });

        return response()->json([
            'status' => true,
            'message' => 'Orders fetched successfully',
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data' => $get_orders,
        ]);
    }

    public function get_salse_detail(Request $request)
    {
    }

    public function getsalseById($id, Request $request)
    {

        $authUser = Auth::guard('api')->user();
        // $subAdminId = session('selectedSubAdminId') ?? $authUser->id;
        $selectedSubAdminID = $request->input('selectedSubAdminId');

        // $setting    = Setting::where('branch_id', $subAdminId)->first();

        $sales = Order::with(['order_items.product.quantityType', 'order_items.product.tax', 'user.userDetail'])->find($id);

        if (!$sales) {
            return response()->json(['status' => false, 'error' => 'Sale not found'], 404);
        }

        $companyInfo = Setting::where('branch_id', $selectedSubAdminID)->first()
            ?? Setting::where('branch_id', $authUser->id)->first();
        $paidAmount = PaymentStore::where('order_id', $sales->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // Pending amount comes directly from orders table
        $pendingAmount = $sales->remaining_amount ?? 0;

        $formattedCreatedAt = $sales->created_at
            ? $sales->created_at->format('Y-m-d h:i A')
            : null;

        $withGst = in_array((string) ($sales->gst_option ?? ''), ['with', 'with_gst'], true);
        $orderItemModels = OrderItem::where('order_id', $id)
            ->with(['product.tax', 'product.category', 'quantityType', 'variant'])
            ->get();
        $frozenBreakdown = is_array($sales->tax_breakdown ?? null) ? $sales->tax_breakdown : [];
        if (! empty($frozenBreakdown)) {
            $money = [
                'tax_breakdown' => PosPricingService::expandCgstSgstBreakdown($frozenBreakdown),
            ];
        } else {
            $money = $this->posPricing()->calculateFromOrderItems(
                $orderItemModels,
                (float) ($sales->discount ?? 0),
                $withGst,
                $this->gstOptionsFromOrder($sales)
            );
        }
        $taxDetails = collect($money['tax_breakdown'] ?? [])->map(function ($row) {
            return [
                'id' => $row['id'] ?? null,
                'tax_name' => $row['name'] ?? 'GST',
                'tax_rate' => $row['rate'] ?? 0,
                'amount' => $row['amount'] ?? 0,
            ];
        })->values();

        $orderItems = $orderItemModels->map(function ($item) use ($withGst, $sales) {
                $addonRows = is_array($item->addons) ? $item->addons : [];
                $addonIds = [];
                $addonNames = [];
                foreach ($addonRows as $a) {
                    if (is_array($a)) {
                        if (!empty($a['id'])) {
                            $addonIds[] = (int) $a['id'];
                        }
                        if (!empty($a['name'])) {
                            $addonNames[] = (string) $a['name'];
                        }
                    } elseif (is_numeric($a)) {
                        $addonIds[] = (int) $a;
                    }
                }
                $variantName = optional($item->variant)->name;
                $displayName = trim((string) ($item->product_name ?? ''));
                if ($displayName === '') {
                    $displayName = (string) (optional($item->product)->name ?? 'Item');
                }
                if ($variantName) {
                    $displayName .= ' (' . $variantName . ')';
                }
                if ($addonNames) {
                    $displayName .= ' + ' . implode(', ', $addonNames);
                }
                // Overall-bill GST: do not show per-item "(GST %)" — tax is only in totals
                $gstLabel = (($sales->gst_apply_mode ?? '') === 'overall')
                    ? ''
                    : PosPricingService::itemGstLabel($item, $withGst);

                $catId = $item->category_id
                    ?? optional($item->product)->category_id;
                $gstApplicable = PosPricingService::categoryGstApplicable($catId);

                return [
                    'id' => $item->id,
                    'product' => $item->product,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'variant_name' => $variantName,
                    'addon_ids' => $addonIds,
                    'addons' => $addonRows,
                    'addon_names' => $addonNames,
                    'display_name' => $displayName,
                    'gst_label' => $gstLabel,
                    'gst_applicable' => $gstApplicable,
                    'category_id' => $item->category_id,
                    'quantity' => $item->quantity,
                    'quantity_type' => $item->quantityType,
                    'price' => $item->unit_price ?? $item->price,
                    'unit_price' => $item->unit_price ?? $item->price,
                    'total_amount' => $item->total_amount,
                    'date' => $item->created_at
                        ? $item->created_at->timezone('Asia/Kolkata')->format('Y-m-d h:i A')
                        : null,
                ];
            });

        return response()->json([
            'status' => true,
            'sales' => [
                ...$sales->toArray(),
                'created_at' => $formattedCreatedAt,
                'user_name' => $sales->user->name ?? '',
                'user_phone' => $sales->user->phone ?? '',
                'user_email' => $sales->user->email ?? '',
                'user_address' => optional($sales->user->userDetail ?? null)->address ?? '',
                'taxes' => $taxDetails,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
            ],
            'order_items' => $orderItems,
            'company_info' => $companyInfo,
            'currency_symbol' => $companyInfo?->currency_symbol ?? '₹',
            'currency_position' => $companyInfo?->currency_position ?? 'left',
        ], 200);
    }

    public function delete($id)
    {
        // Find the order
        $order = Order::find($id);

        // If order not found, return error
        if (!$order) {
            return response()->json([
                'status' => false,
                'error' => 'Order not found',
            ], 404);
        }

        // ❌ Prevent deletion if payment is pending
        if ($order->payment_status === 'pending') {
            return response()->json([
                'status' => false,
                'error' => 'This order cannot be deleted because its payment status is pending.',
            ], 400);
        }
        // Soft delete: set isDeleted = 1
        $order->isDeleted = 1;
        $order->save();

        // Soft delete related order items
        OrderItem::where('order_id', $order->id)->update(['isDeleted' => 1]);

        if (!empty($order->table_no) && is_numeric($order->table_no)) {
            $this->syncTableStatus((int) $order->table_no);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order deleted successfully.',
        ]);
    }

    /**
     * Re-attach a closed/paid dine-in bill to its table (mistaken checkout recovery).
     * Puts the table back to Occupied / Running so staff can continue add/edit in dine-in.
     */
    public function reoccupyDineInOrder(Request $request, $id)
    {
        $this->ensureTenantDatabaseFromSession($request);

        $order = Order::where('id', $id)->where('isDeleted', 0)->first();
        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $tableId = (int) ($request->input('table_id') ?: ($order->table_no ?? 0));
        if ($tableId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'This is not a dine-in table order.',
            ], 422);
        }

        // Order must belong to this table (or have no table_no yet — bind it)
        if (! empty($order->table_no) && (int) $order->table_no !== $tableId) {
            return response()->json([
                'status' => false,
                'message' => 'This bill belongs to a different table.',
            ], 422);
        }

        // Another active bill already on this table?
        $otherActive = Order::where('table_no', $tableId)
            ->where('isDeleted', 0)
            ->where('table_status', 1)
            ->where('id', '!=', $order->id)
            ->orderByDesc('id')
            ->first(['id']);

        if ($otherActive) {
            return response()->json([
                'status' => false,
                'message' => 'This table already has a running bill. Clear or checkout that bill first.',
            ], 422);
        }

        // Already the active bill — just return sync (idempotent)
        if ((int) ($order->table_status ?? 0) === 1 && (int) ($order->table_no ?? 0) === $tableId) {
            $tableSync = $this->syncTableStatus($tableId);

            return response()->json([
                'status' => true,
                'message' => 'Bill is already active on this table.',
                'order_id' => $order->id,
                'table_id' => $tableId,
                'table_status' => $tableSync['status'] ?? Table_no::STATUS_RUNNING,
                'table_status_class' => $tableSync['class'] ?? 'running-table',
                'total_amount' => $order->total_amount,
                'already_active' => true,
            ]);
        }

        DB::beginTransaction();
        try {
            $order->table_no = $tableId;
            $order->table_status = 1;
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'bill_printed')) {
                // Back to green occupied (not yellow bill-printed)
                $order->bill_printed = 0;
            }
            // Re-open payment so staff can keep adding / settle again after mistaken checkout
            $paidSoFar = 0.0;
            try {
                $paidQuery = PaymentStore::where('order_id', $order->id);
                if (\Illuminate\Support\Facades\Schema::hasColumn('payment_stores', 'isDeleted')) {
                    $paidQuery->where('isDeleted', 0);
                }
                $paidSoFar = (float) $paidQuery->sum('payment_amount');
            } catch (\Throwable $e) {
                $paidSoFar = 0.0;
            }
            $totalAmt = (float) ($order->total_amount ?? 0);
            $remain = max(0, round($totalAmt - $paidSoFar, 2));
            $order->remaining_amount = $remain;
            if ($remain > 0.00001) {
                $order->payment_status = 'pending';
                if (strtolower((string) ($order->payment_method ?? '')) === 'completed'
                    || in_array(strtolower((string) ($order->payment_method ?? '')), ['', 'null'], true)) {
                    $order->payment_method = 'pending';
                }
            }
            $order->save();

            $tableSync = $this->syncTableStatus($tableId);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Last bill re-occupied on this table. You can continue adding items.',
                'order_id' => $order->id,
                'table_id' => $tableId,
                'table_status' => $tableSync['status'] ?? Table_no::STATUS_RUNNING,
                'table_status_class' => $tableSync['class'] ?? 'running-table',
                'total_amount' => $order->total_amount,
                'already_active' => false,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reoccupy dine-in order failed: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to re-occupy table bill. Please try again.',
            ], 500);
        }
    }

    /**
     * Clear / void the current dine-in table bill (running or bill-printed).
     * Restores stock, soft-deletes the order, and frees the table.
     */
    public function clearDineInOrder(Request $request, $id)
    {
        $this->ensureTenantDatabaseFromSession($request);

        $order = Order::where('id', $id)->where('isDeleted', 0)->first();
        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if (empty($order->table_no)) {
            return response()->json([
                'status' => false,
                'message' => 'This is not a dine-in table order.',
            ], 422);
        }

        // Only clear active table bills (occupied / bill printed)
        if ((int) ($order->table_status ?? 0) !== 1) {
            return response()->json([
                'status' => false,
                'message' => 'Only the current running table bill can be cleared. Use Orders → Update Bill for closed bills.',
            ], 422);
        }

        $userData = Auth::guard('api')->user() ?: Auth::guard('web')->user();
        $branchIdToUse = $userData
            ? (($userData->role === 'staff' && $userData->branch_id) ? $userData->branch_id : $userData->id)
            : ($order->branch_id ?? null);

        DB::beginTransaction();
        try {
            try {
                $this->recipeStock()->reverseForOrder($order);
            } catch (\Throwable $e) {
                Log::error('Clear dine-in recipe reverse: ' . $e->getMessage());
            }

            $items = OrderItem::where('order_id', $order->id)->where('isDeleted', 0)->get();
            foreach ($items as $item) {
                $product = Product::find($item->product_id);
                if (! $product) {
                    continue;
                }
                if ($product->tracksInventory()) {
                    $beforeQty = (float) $product->quantity;
                    $product->quantity = $beforeQty + (float) $item->quantity;
                    if ((float) $product->quantity > 0) {
                        $product->availablility = 'in_stock';
                    }
                    $product->save();

                    ProductInventory::create([
                        'product_id' => $product->id,
                        'initial_stock' => $beforeQty,
                        'current_stock' => $product->quantity,
                        'branch_id' => $branchIdToUse,
                        'create_by' => $userData->id ?? 0,
                        'type' => 'Dine-in Bill Cleared (Restore)',
                        'date' => now(),
                    ]);
                }
            }

            OrderItem::where('order_id', $order->id)->update(['isDeleted' => 1]);

            if (\Illuminate\Support\Facades\Schema::hasColumn('payment_stores', 'isDeleted')) {
                PaymentStore::where('order_id', $order->id)->update(['isDeleted' => 1]);
            }

            $order->isDeleted = 1;
            $order->table_status = 0;
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'bill_printed')) {
                $order->bill_printed = 0;
            }
            $order->save();

            $tableId = (int) $order->table_no;
            $tableSync = $this->syncTableStatus($tableId);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Current bill cleared. Table is now available.',
                'order_id' => $order->id,
                'table_id' => $tableId,
                'table_status' => $tableSync['status'] ?? Table_no::STATUS_BLANK,
                'table_status_class' => $tableSync['class'] ?? 'blank-table',
                'total_amount' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Clear dine-in order failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to clear bill. Please try again.',
            ], 500);
        }
    }

    public function update_sale(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'update_id' => 'required|exists:orders,id',
            'customer_id' => 'nullable',
            'customer_phone' => 'nullable|string',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:0',
            'discount' => 'numeric|min:0|max:100',
            // 'payment_method' => 'required|in:cash,debit,scan',
            // 'status' => 'required|in:completed,pending,failed',
            'grand_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }
        // dd($request->all());
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($request->update_id);

            // Prefer line items (supports variants / add-ons like POS). Fall back to product_ids map.
            $rawItems = $request->input('items');
            $lineItems = [];
            if (is_array($rawItems) && count($rawItems)) {
                foreach ($rawItems as $row) {
                    if (! is_array($row) || empty($row['product_id'])) {
                        continue;
                    }
                    $lineItems[] = [
                        'product_id' => (int) $row['product_id'],
                        'quantity' => (float) ($row['quantity'] ?? 0),
                        'unit_price' => isset($row['unit_price']) && is_numeric($row['unit_price'])
                            ? (float) $row['unit_price']
                            : null,
                        'quantity_type_id' => $row['quantity_type_id'] ?? null,
                        'variant_id' => ! empty($row['variant_id']) ? (int) $row['variant_id'] : null,
                        'addon_ids' => array_values(array_filter(array_map('intval', (array) ($row['addon_ids'] ?? [])))),
                        'display_name' => trim((string) ($row['display_name'] ?? '')),
                    ];
                }
            }
            if (! $lineItems) {
                $unitPricesFallback = $request->input('unit_prices', []);
                foreach ($request->product_ids ?? [] as $productId) {
                    $lineItems[] = [
                        'product_id' => (int) $productId,
                        'quantity' => (float) ($request->quantities[$productId] ?? 0),
                        'unit_price' => isset($unitPricesFallback[$productId]) && is_numeric($unitPricesFallback[$productId])
                            ? (float) $unitPricesFallback[$productId]
                            : null,
                        'quantity_type_id' => $request->quantity_type_id[$productId] ?? null,
                        'variant_id' => null,
                        'addon_ids' => [],
                        'display_name' => '',
                    ];
                }
            }

            $productIds = array_values(array_unique(array_column($lineItems, 'product_id')));
            $products = Product::whereIn('id', $productIds)->get();

            // Fetch old order items
            $oldItems = OrderItem::where('order_id', $order->id)->get();
            $oldQuantities = $oldItems->groupBy('product_id')->map(function ($group) {
                return (float) $group->sum('quantity');
            })->toArray();

            // Aggregate new qty by product for stock
            $newQtyByProduct = [];
            foreach ($lineItems as $line) {
                $pid = $line['product_id'];
                $newQtyByProduct[$pid] = ($newQtyByProduct[$pid] ?? 0) + $line['quantity'];
            }

            $subtotal = 0;
            foreach ($lineItems as $line) {
                $product = $products->firstWhere('id', $line['product_id']);
                if (! $product) {
                    continue;
                }
                $unitPrice = $line['unit_price'] !== null ? $line['unit_price'] : (float) $product->price;
                $subtotal += $unitPrice * $line['quantity'];
            }

            foreach ($newQtyByProduct as $productId => $quantity) {
                $product = $products->firstWhere('id', $productId);
                if (! $product) {
                    continue;
                }
                $oldQty = $oldQuantities[$productId] ?? 0;
                $difference = $quantity - $oldQty;

                if ($difference > 0 && $product->shouldBlockSaleForQty($difference)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stock Quantity Exceeded. Only {$product->quantity} quantity are available for '{$product->name}'.",
                    ], 422);
                }

                if ($difference > 0 && $product->tracksInventory()) {
                    $product->decrement('quantity', $difference);
                } elseif ($difference < 0 && $product->tracksInventory()) {
                    $product->increment('quantity', abs($difference));
                }
            }

            $userData = Auth::guard('api')->user();
            $userRole = $userData->role;
            if ($userRole == 'staff') {
                $branchIdToUse = $userData->branch_id;
            } elseif (!empty($request->selectedSubAdminId) && $userRole == 'admin') {
                $branchIdToUse = $request->selectedSubAdminId ?? null;
            } else {
                $branchIdToUse = $userData->id;
            }
            // Tax & discount calculations
            $discountAmount = $subtotal * ($request->discount / 100);
            $taxableAmount = $subtotal - $discountAmount;

            $totalTax = 0;
            $taxJson = null;
            $moneyTax = ['tax_ids' => [], 'tax_total' => 0, 'tax_breakdown' => []];

            // Product-level / overall-bill taxes (same rules as PosPricingService)
            $gstOptsLegacy = $this->applyBillServiceTaxOverride(
                $this->gstOptionsForBranch($branchIdToUse),
                $request
            );
            if ($request->gst_option === 'with_gst' || $request->gst_option === 'with') {
                $products->loadMissing(['tax', 'category']);
                $resolvedForTax = [];
                foreach ($lineItems as $line) {
                    $product = $products->firstWhere('id', $line['product_id']);
                    if (! $product) {
                        continue;
                    }
                    $qty = (float) $line['quantity'];
                    $unitPrice = $line['unit_price'] !== null ? $line['unit_price'] : (float) $product->price;
                    $catId = PosPricingService::safeCategoryId($product->category_id ?? null);
                    $gstApplicable = PosPricingService::categoryGstApplicable($catId);
                    $snap = $gstApplicable
                        ? PosPricingService::taxSnapshotFromProduct($product)
                        : [
                            'tax_id' => null,
                            'tax_name' => null,
                            'tax_rate' => null,
                            'tax_inclusive' => false,
                        ];
                    $resolvedForTax[] = [
                        'product' => $product,
                        'line_total' => round($unitPrice * $qty, 2),
                        'category_id' => $catId,
                        'gst_applicable' => $gstApplicable,
                        'tax_id' => $snap['tax_id'],
                        'tax_name' => $snap['tax_name'],
                        'tax_rate' => $snap['tax_rate'],
                        'tax_inclusive' => $snap['tax_inclusive'],
                    ];
                }
                $moneyTax = $this->posPricing()->calculateOrderTotals(
                    $resolvedForTax,
                    (float) ($request->discount ?? 0),
                    true,
                    $branchIdToUse,
                    $gstOptsLegacy
                );
                $taxJson = json_encode($moneyTax['tax_ids'] ?? []);
                $totalTax = (float) ($moneyTax['tax_total'] ?? 0);
            } else {
                $taxJson = json_encode([]);
            }

            $grandTotal = $taxableAmount + $totalTax;

            // If you track payments, subtract paid_amount; if not, just set = grandTotal
            $remainingAmount = $grandTotal;

            // Update Order
            $legacyOrderUpdate = [
                'user_name' => $request->customer_id,
                'user_phone' => $request->customer_phone,
                'payment_method' => 'pending',
                'discount' => $request->discount,
                'discount_amount' => $discountAmount,
                'gst_option' => $request->gst_option === 'with_gst' ? 'with_gst' : 'without_gst',
                'tax_id' => $taxJson,
                'tax_amount' => $totalTax,
                'subtotal' => $subtotal,
                'total_amount' => $grandTotal,
                'remaining_amount' => $remainingAmount,
                'payment_status' => 'pending',
                'order_type' => $request->order_type ?? $order->order_type,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'tax_breakdown')) {
                $legacyOrderUpdate['tax_breakdown'] = $moneyTax['tax_breakdown'] ?? [];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'gst_apply_mode')) {
                $legacyOrderUpdate['gst_apply_mode'] = $gstOptsLegacy['gst_apply_mode'];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'overall_gst_rate')) {
                $legacyOrderUpdate['overall_gst_rate'] = ($gstOptsLegacy['gst_apply_mode'] === 'overall')
                    ? $gstOptsLegacy['overall_gst_rate']
                    : null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'service_tax_rate')) {
                $legacyOrderUpdate['service_tax_rate'] = $moneyTax['service_tax_rate'] ?? null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'service_tax_amount')) {
                $legacyOrderUpdate['service_tax_amount'] = $moneyTax['service_tax_amount'] ?? 0;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'service_tax_name')) {
                $legacyOrderUpdate['service_tax_name'] = $moneyTax['service_tax_name'] ?? null;
            }
            $order->update($legacyOrderUpdate);

            // Remove old order items
            OrderItem::where('order_id', $order->id)->delete();

            // Reinsert updated order items (supports variants / add-ons like POS)
            $inventoryLogged = [];
            foreach ($lineItems as $line) {
                $product = $products->firstWhere('id', $line['product_id']);
                if (! $product) {
                    continue;
                }
                $quantity = $line['quantity'];
                $quantity_type_id = $line['quantity_type_id'];
                $price = $line['unit_price'] !== null ? $line['unit_price'] : (float) $product->price;
                $total = $price * $quantity;
                $catId = PosPricingService::safeCategoryId($product->category_id ?? null);
                $gstApplicable = PosPricingService::categoryGstApplicable($catId);
                $taxSnap = $gstApplicable
                    ? PosPricingService::taxSnapshotFromProduct($product)
                    : [
                        'tax_id' => null,
                        'tax_name' => null,
                        'tax_rate' => null,
                        'tax_inclusive' => false,
                    ];
                $displayName = $line['display_name'] !== ''
                    ? $line['display_name']
                    : (string) ($product->name ?? 'Item');

                try {
                    $snapLine = [
                        'product' => $product,
                        'product_name' => $displayName,
                        'unit_price' => $price,
                        'gst_applicable' => $gstApplicable,
                    ] + $taxSnap;
                    $itemPayload = [
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? null,
                        'category_id' => $catId,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'quantity_type_id' => $quantity_type_id,
                        'price' => $price,
                        'total_amount' => $total,
                        'branch_id' => $branchIdToUse,
                        'created_by' => $userData->id,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('order_items', 'unit_price')) {
                        $itemPayload['unit_price'] = $price;
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('order_items', 'variant_id') && ! empty($line['variant_id'])) {
                        $itemPayload['variant_id'] = $line['variant_id'];
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('order_items', 'addons') && ! empty($line['addon_ids'])) {
                        $itemPayload['addons'] = array_map(function ($id) {
                            return ['id' => (int) $id];
                        }, $line['addon_ids']);
                    }
                    OrderItem::create(array_merge(
                        $itemPayload,
                        PosPricingService::applyOverallSnapshotToLineFields(
                            PosPricingService::orderItemSnapshotFields($snapLine),
                            $gstOptsLegacy
                        )
                    ));
                } catch (\Exception $e) {
                    \Log::error('Failed to insert OrderItem for product ID: '.$line['product_id'].' error: '.$e->getMessage());
                    throw $e;
                }

                if (! isset($inventoryLogged[$product->id])) {
                    $lastInventory = ProductInventory::where('product_id', $product->id)
                        ->orderBy('id', 'desc')
                        ->first();

                    ProductInventory::create([
                        'product_id' => $product->id,
                        'initial_stock' => $lastInventory->initial_stock ?? 0,
                        'current_stock' => $product->quantity, // already adjusted above
                        'branch_id' => $order->branch_id,
                        'create_by' => Auth::id(),
                        'type' => 'Update Sale',
                        'date' => now(),
                    ]);
                    $inventoryLogged[$product->id] = true;
                }
            }

            $paymentMethod = $request->payment_method ?? $order->payment_method ?? 'cash';

            if ($paymentMethod != 'pending') {

                if ($paymentMethod === 'cash+online') {

                    // Cash entry
                    if ($request->cash_amount > 0) {
                        PaymentStore::create([
                            'user_id' => $request->customer_id ?? ($order->user_id ?? 1),
                            'order_id' => $order->id,
                            'payment_amount' => $request->cash_amount,
                            'payment_date' => now(),
                            'payment_method' => 'cash',
                            'payment_type' => $request->paid_type ?? 'full',
                            'cash_amount' => $request->cash_amount,
                            'upi_amount' => 0,
                            'remaining_amount' => $request->pending_amount ?? 0,
                            'emi_month' => null,
                            'jobcard_id' => 0,
                            'isDeleted' => 0,
                            'created_at' => now(),
                        ]);
                    }

                    // Online entry
                    if ($request->online_amount > 0) {
                        PaymentStore::create([
                            'user_id' => $request->customer_id ?? ($order->user_id ?? 1),
                            'order_id' => $order->id,
                            'payment_amount' => $request->online_amount,
                            'payment_date' => now(),
                            'payment_method' => 'online',
                            'payment_type' => $request->paid_type ?? 'full',
                            'cash_amount' => 0,
                            'upi_amount' => $request->online_amount,
                            'remaining_amount' => $request->pending_amount ?? 0,
                            'emi_month' => null,
                            'jobcard_id' => 0,
                            'isDeleted' => 0,
                            'created_at' => now(),
                        ]);
                    }
                } else {
                    // Normal single payment entry
                    // For a plain cash payment, cash_amount should equal the payment amount
                    $editCashAmt = ($paymentMethod === 'cash')
                        ? ($request->cash_amount > 0 ? $request->cash_amount : ($request->total ?? 0))
                        : ($request->cash_amount ?? 0);

                    PaymentStore::create([
                        'user_id' => $request->customer_id ?? ($order->user_id ?? 1),
                        'order_id' => $order->id,
                        'payment_amount' => $request->total ?? 0,
                        'payment_date' => now(),
                        'payment_method' => $paymentMethod,
                        'payment_type' => $request->paid_type ?? 'full',
                        'cash_amount' => $editCashAmt,
                        'upi_amount' => $request->upi_amount ?? 0,
                        'remaining_amount' => $request->pending_amount ?? 0,
                        'emi_month' => null,
                        'jobcard_id' => 0,
                        'isDeleted' => 0,
                        'created_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function orderReport(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated access'], 401);
        }

        $userId = $user->id;
        $role = $user->role;
        $branchId = $user->branch_id;

        $effectiveBranchId = ($role === 'staff')
            ? $branchId
            : ($request->input('selectedSubAdminId') ?? $userId);

        $filter = $request->query('filter');
        $month = $request->query('month');
        $year = $request->query('year');
        $customerId = $request->query('customer_id');
        $categoryId = $request->query('category_id');
        $reportType = strtolower(trim((string) $request->query('report_type', 'all')));
        if (! in_array($reportType, ['all', 'food', 'liquor'], true)) {
            $reportType = 'all';
        }

        $query = OrderItem::with(['product.category', 'product.brand', 'order'])
            ->where(function ($q) {
                $q->where('isDeleted', 0)->orWhereNull('isDeleted');
            })
            ->whereHas('order', function ($q) use ($filter, $month, $year, $customerId, $role, $effectiveBranchId, $userId) {
                // Align with All Orders list totals: include paid / partial (not only exact "completed")
                $q->where('isDeleted', 0)
                    ->where(function ($pay) {
                        $pay->whereRaw(
                            "LOWER(TRIM(COALESCE(payment_status, ''))) IN ('completed','paid','partial','partially','extra paid','extra_paid')"
                        )->orWhereNull('payment_status');
                    });

                // Staff + admin/sub-admin: same branch-wide order list
                $q->where('branch_id', $effectiveBranchId);

                // ✅ Date filters
                if ($filter) {
                    $today = Carbon::today();
                    switch ($filter) {
                        case 'this_week':
                            $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                            break;
                        case 'this_month':
                            $q->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year);
                            break;
                        case 'last_6_months':
                            $q->whereBetween('created_at', [Carbon::now()->subMonths(6), Carbon::now()]);
                            break;
                        case 'this_year':
                            $q->whereYear('created_at', $today->year);
                            break;
                        case 'previous_year':
                            $q->whereYear('created_at', Carbon::now()->subYear()->year);
                            break;
                    }
                }

                // ✅ Month filter
                if ($month) {
                    $q->whereMonth('created_at', $month);
                }

                // ✅ Year filter
                if ($year) {
                    $q->whereYear('created_at', $year);
                }

                // ✅ Customer filter
                if ($customerId) {
                    $q->where('user_id', $customerId);
                }

            });

        // ✅ Category filter (via product relation)
        if ($categoryId) {
            $query->whereHas('product', function ($p) use ($categoryId) {
                $p->where('category_id', $categoryId);
            });
        }

        $rawItems = $query->get();

        // Food = category Apply GST ON; Liquor = Apply GST OFF (same as tables floor)
        $resolveIsFood = static function ($item, array $gstByCategory): bool {
            $catId = $item->category_id
                ?: ($item->product->category_id ?? null);
            if ($catId === null || $catId === '') {
                return true;
            }
            $cid = (int) $catId;
            if (array_key_exists($cid, $gstByCategory)) {
                return (bool) $gstByCategory[$cid];
            }

            return PosPricingService::categoryGstApplicable($catId);
        };

        $gstByCategory = [];
        if ($rawItems->isNotEmpty() && Schema::hasColumn('categories', 'gst_applicable')) {
            $catIds = $rawItems->map(function ($item) {
                return $item->category_id ?: ($item->product->category_id ?? null);
            })->filter()->unique()->values();
            if ($catIds->isNotEmpty()) {
                $gstByCategory = Category::whereIn('id', $catIds)
                    ->pluck('gst_applicable', 'id')
                    ->map(fn ($v) => $v === null ? true : (bool) $v)
                    ->all();
            }
        }

        if (in_array($reportType, ['food', 'liquor'], true)) {
            $rawItems = $rawItems->filter(function ($item) use ($reportType, $gstByCategory, $resolveIsFood) {
                $isFood = $resolveIsFood($item, $gstByCategory);

                return $reportType === 'food' ? $isFood : ! $isFood;
            })->values();
        }

        // Order grand totals (tax/discount included) — same basis as All Orders list
        $orderIds = $rawItems->pluck('order_id')->unique()->filter()->values();
        $ordersById = $orderIds->isEmpty()
            ? collect()
            : Order::whereIn('id', $orderIds)->get()->keyBy('id');

        // Full line subtotals per order (all items) so tax/discount allocation stays correct
        // even when the report is filtered to one category / food-liquor tab.
        $lineBaseOf = static function ($row): float {
            $line = (float) ($row->total_amount ?? 0);
            if ($line <= 0) {
                $line = (float) ($row->price ?? 0) * (float) ($row->quantity ?? 0);
            }
            return $line;
        };

        $itemsSumByOrder = $orderIds->isEmpty()
            ? collect()
            : OrderItem::query()
                ->whereIn('order_id', $orderIds)
                ->where(function ($q) {
                    $q->where('isDeleted', 0)->orWhereNull('isDeleted');
                })
                ->get(['id', 'order_id', 'total_amount', 'price', 'quantity'])
                ->groupBy('order_id')
                ->map(fn ($rows) => (float) $rows->sum($lineBaseOf));

        $orderItems = $rawItems->map(function ($item) use ($ordersById, $itemsSumByOrder, $lineBaseOf, $gstByCategory, $resolveIsFood) {
            $product = $item->product;
            $soldQty = (float) ($item->quantity ?? 0);

            $lineBase = $lineBaseOf($item);

            // Allocate order-level tax/discount so product amounts sum to order.total_amount
            $order = $ordersById->get($item->order_id) ?? $item->order;
            $itemsSum = (float) ($itemsSumByOrder[$item->order_id] ?? 0);
            $orderTotal = (float) ($order->total_amount ?? $lineBase);
            $soldAmount = ($itemsSum > 0.0001)
                ? ($lineBase / $itemsSum) * $orderTotal
                : $lineBase;

            $decodedImages = $product ? json_decode($product->images ?? '[]', true) : [];
            $firstImage = is_array($decodedImages)
                ? ($decodedImages[0] ?? 'admin/assets/img/product/noimage.png')
                : 'admin/assets/img/product/noimage.png';

            $isFood = $resolveIsFood($item, $gstByCategory);

            return [
                'id' => $item->id,
                'product_id' => $product->id ?? $item->product_id,
                'name' => $product->name ?? ($item->product_name ?? 'Unknown'),
                'SKU' => $product->SKU ?? '',
                'category' => $product->category->name ?? 'N/A',
                'brand' => $product->brand->name ?? 'N/A',
                'sold_qty' => $soldQty,
                'sold_amount' => number_format(round($soldAmount, 2), 2, '.', ''),
                'sold_amount_raw' => round($soldAmount, 2),
                'report_kind' => $isFood ? 'food' : 'liquor',
                'order_id' => $item->order_id,
                'image' => 'storage/' . $firstImage,
                'image_url' => $product->image_url ?? null,
            ];
        });

        // Full order totals only for "All" with no category filter; otherwise sum allocated lines
        if ($categoryId || in_array($reportType, ['food', 'liquor'], true)) {
            $ordersTotal = (float) $orderItems->sum('sold_amount_raw');
        } else {
            $ordersTotal = (float) $ordersById->sum(function ($order) {
                return (float) ($order->total_amount ?? 0);
            });
        }

        return response()->json([
            'status' => true,
            'data' => $orderItems->values(),
            'summary' => [
                'total_amount' => round($ordersTotal, 2),
                'total_amount_formatted' => number_format($ordersTotal, 2, '.', ''),
                'orders_count' => $ordersById->count(),
                'report_type' => $reportType,
            ],
        ]);
    }

    // public function orderReport(Request $request)
    // {
    //     $user = Auth::guard('api')->user();

    //     if (!$user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Unauthenticated access',
    //         ], 401);
    //     }

    //     $userId  = $user->id;
    //     $role    = $user->role;
    //     $branchId = $user->branch_id;

    //     // ✅ Determine effective branch/sub-admin
    //     if ($role === 'staff') {
    //         $effectiveBranchId = $branchId; // staff belongs to branch_id
    //     } else {
    //         $effectiveBranchId = $request->input('selectedSubAdminId') ?? $userId;
    //     }

    //     // ✅ Fetch branch-specific settings
    //     $settings = DB::table('settings')
    //         ->where('branch_id', $effectiveBranchId)
    //         ->first();

    //     $currencySymbol   = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     $filter = $request->query('filter');

    //     // ✅ Main Query
    //     $orderItems = OrderItem::with(['product.category', 'product.brand', 'order'])
    //         ->whereHas('order', function ($query) use ($filter, $role, $effectiveBranchId, $userId) {
    //             $query->where('payment_status', 'completed')
    //                 ->where('isDeleted', 0);

    //             // ✅ Role-based filtering
    //             if ($role === 'staff') {
    //                 $query->where('created_by', $userId);
    //             } else {
    //                 $query->where('branch_id', $effectiveBranchId);
    //             }

    //             // ✅ Date range filters
    //             if ($filter) {
    //                 $today = Carbon::today();
    //                 switch ($filter) {
    //                     case 'this_week':
    //                         $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    //                         break;
    //                     case 'this_month':
    //                         $query->whereMonth('created_at', $today->month)
    //                             ->whereYear('created_at', $today->year);
    //                         break;
    //                     case 'last_6_months':
    //                         $query->whereBetween('created_at', [Carbon::now()->subMonths(6), Carbon::now()]);
    //                         break;
    //                     case 'this_year':
    //                         $query->whereYear('created_at', $today->year);
    //                         break;
    //                     // case 'previous_year':
    //                     //     $query->whereYear('created_at', $today->subYear()->year);
    //                     //     break;
    //                     case 'previous_year':
    //                         $query->whereYear('created_at', Carbon::now()->subYear()->year);
    //                         break;
    //                 }
    //             }
    //         })
    //         ->select('id', 'product_id', 'quantity', 'price', 'order_id')
    //         ->get()
    //         ->map(function ($orderItem) use ($currencySymbol, $currencyPosition) {
    //             $product = $orderItem->product;

    //             $soldQty    = $orderItem->quantity;
    //             $soldAmount = $orderItem->price * $soldQty;
    //             $created_at = optional($orderItem->order)->created_at;

    //             $imageList = json_decode($product->images);
    //             $imagePath = $imageList[0] ?? null;
    //             $imageUrl  = $imagePath ? 'storage/' . $imagePath : 'admin/assets/img/product/noimage.png';

    //             $formattedAmount     = number_format($soldAmount, 2);
    //             $soldAmountFormatted = $currencyPosition === 'left'
    //                 ? $currencySymbol . $formattedAmount
    //                 : $formattedAmount . $currencySymbol;

    //             return [
    //                 'id'          => $orderItem->id,
    //                 'product_id'  => $product->id,
    //                 'name'        => $product->name,
    //                 'SKU'         => $product->SKU,
    //                 'category'    => $product->category->name ?? 'N/A',
    //                 'brand'       => $product->brand->name ?? 'N/D',
    //                 'sold_amount' => $soldAmountFormatted,
    //                 'sold_qty'    => $soldQty,
    //                 'instock_qty' => $product->quantity,
    //                 'image'       => $imageUrl,
    //                 'image_url'   => $product->image_url,
    //                 'created_at'  => $created_at,
    //             ];
    //         });

    //     return response()->json([
    //         'status'           => true,
    //         'data'             => $orderItems,
    //         'currencySymbol'   => $currencySymbol,
    //         'currencyPosition' => $currencyPosition,
    //     ]);
    // }

    // public function getFilteredOrders(Request $request)
    // {
    //     $user = Auth::guard('api')->user();

    //     if (! $user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Unauthenticated access',
    //         ], 401);
    //     }

    //     $role   = $user->role;
    //     $userId = $user->id;

    //     // If staff, use branch_id instead of own id
    //     if ($role === 'staff') {
    //         $branchId = $user->branch_id;
    //     } elseif ($role === 'admin' && $request->filled('selectedSubAdminId')) {
    //         $branchId = $request->query('selectedSubAdminId');
    //     } else {
    //         $branchId = $userId; // sub-admin or fallback
    //     }

    //     // ✅ Branch-specific settings
    //     $settings         = DB::table('settings')->where('branch_id', $branchId)->first();
    //     $currencySymbol   = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     try {
    //         $query = Order::with('user')
    //             ->withCount([
    //                 'payments as has_payment' => function ($q) {
    //                     $q->where('isDeleted', 0);
    //                 },
    //             ])
    //             ->where('isDeleted', 0);

    //         // ✅ Always filter by branch
    //         $query->where('branch_id', $branchId);

    //         // ✅ Filter by selected date (format: YYYY-MM-DD)
    //         if ($request->filled('date')) {
    //             $query->whereDate('created_at', $request->date);
    //         }

    //         // ✅ Apply sorting
    //         $orders = $query->orderBy('created_at', 'desc')->get();

    //         // ✅ Add formatted date
    //         $orders->transform(function ($order) {
    //             $order->created_date = optional($order->created_at)->format('Y-m-d');
    //             return $order;
    //         });

    //         return response()->json([
    //             'status'            => true,
    //             'data'              => $orders,
    //             'currency_symbol'   => $currencySymbol,
    //             'currency_position' => $currencyPosition,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'            => false,
    //             'currency_symbol'   => $currencySymbol,
    //             'currency_position' => $currencyPosition,
    //             'message'           => 'Something went wrong',
    //             'error'             => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function getFilteredOrders(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $role = $user->role;
        $userId = $user->id;
        // Same branch resolution as get_orders() so filter totals never exceed load totals
        $selectedSubAdminID = $request->input('selectedSubAdminId');

        $query = Order::with([
            'user:id,name,phone',
            'creator:id,name',
        ])
            ->withCount([
                'payments as has_payment' => function ($q) {
                    $q->where('isDeleted', 0);
                },
            ])
            ->where('isDeleted', 0);

        if ($role === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif ($role === 'admin' && $selectedSubAdminID) {
            $query->where('branch_id', $selectedSubAdminID);
        } elseif ($role === 'staff') {
            $query->where('branch_id', $user->branch_id ?: $userId);
        } else {
            $query->where('branch_id', $userId);
        }

        // Filter by selected date if provided
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        try {
            $orders = $query->orderBy('created_at', 'desc')->get();

            // Add formatted date
            $orders->transform(function ($order) {
                $order->created_date = optional($order->created_at)->format('d-M-Y h:i A');
                return $order;
            });

            // Get branch-specific settings
            $settings = DB::table('settings')->where('branch_id', $user->branch_id ?? $userId)->first();
            $currencySymbol = $settings->currency_symbol ?? '₹';
            $currencyPosition = $settings->currency_position ?? 'left';

            return response()->json([
                'status' => true,
                'data' => $orders,
                'currency_symbol' => $currencySymbol,
                'currency_position' => $currencyPosition,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'currency_symbol' => $currencySymbol ?? '₹',
                'currency_position' => $currencyPosition ?? 'left',
            ], 500);
        }
    }

    public function getHistory1($order_id)
    {
        $history = PaymentStore::where('order_id', $order_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history,
        ]);
    }

    public function exportOrders(Request $request)
    {
        // $user = auth()->user();
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;
        // Sub-admin id (from dropdown or session fallback)
        $selectedSubAdminId = $request->query('selectedSubAdminId');
        if ($selectedSubAdminId === 'null' || trim($selectedSubAdminId) === '') {
            $selectedSubAdminId = null;
        }
        $selectedSubAdminId = $selectedSubAdminId ?? session('selectedSubAdminId') ?? $user->id;

        $year = $request->query('year');
        $month = $request->query('month');
        $date = $request->query('date');
        $customerId = $request->query('customerId');

        $settings = DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        try {
            $query = Order::with(['user:id,name,phone'])
                ->where('isDeleted', 0);
            // ->where('type', 'Sales');

            // 🔹 Year filter
            if (!empty($year)) {
                $query->whereYear('created_at', $year);
            }

            // 🔹 Month filter
            if (!empty($month)) {
                $query->whereMonth('created_at', $month);
            }

            // 🔹 Exact Date filter
            if (!empty($date)) {
                // Convert DD-MM-YYYY to YYYY-MM-DD
                $dateParts = explode('-', $date);
                if (count($dateParts) === 3) {
                    $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                    $query->whereDate('created_at', $formattedDate);
                }
            }

            // 🔹 Customer filter
            if ($request->filled('customerId')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', $request->customerId);
                });
            }

            // 🔹 Role wise filter — staff see full branch orders (same as admin)
            if ($role === 'sub-admin') {
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin' && $selectedSubAdminId) {
                $query->where('branch_id', $selectedSubAdminId);
            } elseif ($role === 'staff') {
                $query->where('branch_id', $user->branch_id ?: $userId);
            } else {
                $query->where('branch_id', $userId);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            // Generate Excel same as before...
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Order Number');
            $sheet->setCellValue('B1', 'Date');
            $sheet->setCellValue('C1', 'Customer Name');
            $sheet->setCellValue('D1', 'Total Amount');
            $sheet->setCellValue('E1', 'Remaining Amount');
            $sheet->setCellValue('F1', 'Payment Status');
            $sheet->setCellValue('G1', 'Payment Method');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);

            $row = 2;
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $order->order_number ?? 'N/A');
                $sheet->setCellValue('B' . $row, $order->created_at->format('Y-m-d'));
                $sheet->setCellValue('C' . $row, $order->user->name ?? 'N/A');
                $sheet->setCellValue('D' . $row, $order->total_amount ?? 0);
                $sheet->setCellValue('E' . $row, $order->remaining_amount ?? 0);
                $sheet->setCellValue('F' . $row, $order->payment_status ?? 'N/A');
                $sheet->setCellValue('G' . $row, $order->payment_method ?? 'N/A');
                $row++;
            }

            // $writer   = new Xlsx($spreadsheet);
            // $fileName = 'Sales_' . date('Ymd_His') . '.xlsx';

            // return response()->streamDownload(function () use ($writer) {
            //     $writer->save('php://output');
            // }, $fileName, [
            //     'Content-Type'                  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //     'Access-Control-Expose-Headers' => 'Content-Disposition',
            // ]);

            // Save to public storage
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'Sales_' . date('Ymd_His') . '.xlsx';
            $relativePath = 'exports/' . $filename;

            // Save temporary file
            $temp_file = tempnam(sys_get_temp_dir(), 'excel');
            $writer->save($temp_file);
            \Storage::disk('public')->put($relativePath, file_get_contents($temp_file));
            unlink($temp_file);

            // Generate public URL

            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status' => true,
                'message' => 'Sales Excel generated successfully.',
                'file_url' => $fileUrl,
                'file_name' => $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function salse_invoice_pdf_download($id)
    {
        $view_id = $id;
        $sales = Order::find($view_id);
        $authUser = Auth::guard('api')->user();
        $subAdminId = session('selectedSubAdminId') ?? $authUser->id;
        $setting = Setting::where('branch_id', $subAdminId)->first();

        if (!$sales) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Fetch user data (order belongs to which user)
        $user = $sales->user_id ? User::find($sales->user_id) : null;

        // Helper for currency formatting
        $formatCurrency = function ($amount) use ($setting) {
            $amount = number_format($amount, 2);
            return $setting->currency_position === 'right'
                ? $amount . $setting->currency_symbol
                : $setting->currency_symbol . $amount;
        };

        // ✅ Subtotal
        $orderItems = OrderItem::where('order_id', $view_id)->get();
        $subtotal = $orderItems->sum('total_amount');

        // ✅ Discount
        $discountPercent = $sales->discount ?? 0;
        $discountAmount = ($discountPercent / 100) * $subtotal;
        $afterDiscount = $subtotal - $discountAmount;

        // ✅ Tax calculation
        $taxDetails = [];
        if ($sales->gst_option === 'with_gst') {
            $taxIds = json_decode($sales->tax_id, true) ?? [];
            if (!empty($taxIds)) {
                $taxes = TaxRate::whereIn('id', $taxIds)->get();
                foreach ($taxes as $tax) {
                    $taxAmount = ($tax->tax_rate / 100) * $afterDiscount;
                    $taxDetails[] = [
                        'name' => $tax->tax_name,
                        'rate' => $tax->tax_rate,
                        'amount' => $taxAmount,
                        'formatted_amount' => $formatCurrency($taxAmount),
                    ];
                }
            }
        }

        // ✅ Final total
        $finalTotal = $afterDiscount + collect($taxDetails)->sum('amount');

        // ✅ Prepare formatted values
        $formattedSubtotal = $formatCurrency($subtotal);
        $formattedDiscountAmount = $formatCurrency($discountAmount);
        $formattedAfterDiscount = $formatCurrency($afterDiscount);
        $formattedFinalTotal = $formatCurrency($finalTotal);

        // ✅ Prepare data for PDF view
        $pdfData = [
            'view_id' => $view_id,
            'sales' => $sales,
            'setting' => $setting,
            'orderItems' => $orderItems,
            'salesItems' => $orderItems,
            'customer' => [
                'name' => $user->name ?? 'walk-in-customer',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'address' => optional($user->userDetail)->address ?? 'arga',
            ],
            'user' => $user ? $user->toArray() : null,
            'subtotal' => $formattedSubtotal,
            'discountPercent' => $discountPercent,
            'discountAmount' => $formattedDiscountAmount,
            'afterDiscount' => (float) $afterDiscount,
            'formattedAfterDiscount' => $formattedAfterDiscount,
            'finalTotal' => $formattedFinalTotal,
            'taxDetails1' => $taxDetails,
        ];

        // ✅ Choose PDF view based on setting
        $viewName = (isset($setting->invoice_size) && $setting->invoice_size === 'small') 
            ? 'sales.salse-invoice-small-pdf' 
            : 'sales.salse-invoice-pdf';

        // ✅ Generate PDF with content-matching height for thermal bills
        if (isset($setting->invoice_size) && $setting->invoice_size === 'small') {
            $pdf = make_thermal_invoice_pdf($viewName, $pdfData, $orderItems ?? 1, 226.77);
        } else {
            $pdf = PDF::loadView($viewName, $pdfData);
            $pdf->setPaper('A4', 'portrait');
        }

        // ✅ Save PDF to storage (public folder)
        $fileName = 'invoice_' . $view_id . '.pdf';
        // $filePath = '/storage/app/public/sales-invoices/' . $fileName;

        // Ensure directory exists
        // if (!file_exists(storage_path('/storage/app/public/sales-invoices/'))) {
        //     mkdir(storage_path('/storage/app/public/sales-invoices/'), 0777, true);
        // }

        $relativePath = 'sales-invoices/' . $fileName;
        \Storage::disk('public')->put($relativePath, $pdf->output());

        // Public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status' => true,
            'message' => 'Sales Invoice PDF generated successfully.',
            'file_url' => $fileUrl,
            'file_name' => $fileName,
        ]);
    }
    // public function exportOrdersPDF(Request $request)
    // {
    //     // $user = auth()->user();
    //     $user = Auth::guard('api')->user();

    //     if (! $user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Unauthenticated access',
    //         ], 401);
    //     }

    //     $userId = $user->id;
    //     $role   = $user->role;

    //     // Sub-admin id (from dropdown or session fallback)
    //     $selectedSubAdminId = $request->query('selectedSubAdminId') ?? session('selectedSubAdminId') ?? $user->id;

    //     // ✅ branch-wise setting
    //     $setting = Setting::where('branch_id', $selectedSubAdminId)->first();

    //     try {
    //         $query = Order::with(['user:id,name,phone'])
    //             ->where('isDeleted', 0);
    //         // ->where('type', 'Sales');

    //         // 🔹 Year filter
    //         if ($request->filled('year')) {
    //             $query->whereYear('created_at', $request->year);
    //         }

    //         // 🔹 Month filter
    //         if ($request->filled('month')) {
    //             $query->whereMonth('created_at', $request->month);
    //         }

    //         // 🔹 Exact Date filter (DD-MM-YYYY or YYYY-MM-DD)
    //         if ($request->filled('date')) {
    //             $inputDate = trim($request->date);
    //             if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $inputDate)) {
    //                 [$day, $month, $year] = explode('-', $inputDate);
    //                 $formattedDate        = "$year-$month-$day";
    //             } else {
    //                 $formattedDate = $inputDate;
    //             }
    //             $query->whereDate('created_at', $formattedDate);
    //         }

    //         // 🔹 Customer filter
    //         if ($request->filled('customerId')) {
    //             $query->whereHas('user', function ($q) use ($request) {
    //                 $q->where('name', $request->customerId);
    //             });
    //         }

    //         if ($role === 'sub-admin') {
    //             $query->where('branch_id', $userId);
    //         } elseif ($role === 'admin' && $selectedSubAdminId) {
    //             $query->where('branch_id', $selectedSubAdminId);
    //         } elseif ($role === 'staff') {
    //             $query->where('created_by', $userId);
    //         } else {
    //             $query->where('branch_id', $userId);
    //         }
    //         // dd( $selectedSubAdminId);

    //         $orders = $query->orderBy('created_at', 'desc')->get();

    //         // ✅ load PDF view
    //         $pdf = Pdf::loadView('sales.orders_pdf', [
    //             'orders'  => $orders,
    //             'setting' => $setting,
    //         ])->setPaper('A4', 'landscape');

    //         return $pdf->download('Sales_' . date('Ymd_His') . '.pdf');
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Something went wrong',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function exportOrdersPDF(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role = $user->role;
        $userBranchId = $user->branch_id;
        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userId;

        // 🔹 Fetch branch-wise setting
        // $setting = Setting::where('branch_id', $selectedSubAdminId)->first();
        if ($role === 'staff' && $userBranchId) {
            $branchIdToUse = $userBranchId;
        } elseif ($role === 'admin' && !empty($selectedSubAdminId)) {
            $branchIdToUse = $selectedSubAdminId;
        } elseif ($role === 'sub-admin') {
            $branchIdToUse = $userId;
        } else {
            $branchIdToUse = $userId;
        }

        // 🔹 Fetch settings branch-wise
        $setting = Setting::where('branch_id', $branchIdToUse)->first();

        try {
            $query = Order::with(['user:id,name,phone'])
                ->where('isDeleted', 0);

            // 🔹 Apply filters
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            if ($request->filled('date')) {
                $inputDate = trim($request->date);
                $formattedDate = preg_match('/^\d{2}-\d{2}-\d{4}$/', $inputDate)
                    ? implode('-', array_reverse(explode('-', $inputDate)))
                    : $inputDate;
                $query->whereDate('created_at', $formattedDate);
            }
            if ($request->filled('customerId')) {
                $query->whereHas('user', fn($q) => $q->where('name', $request->customerId));
            }

            // 🔹 Role-based branch filtering — staff see full branch orders (same as admin)
            if ($role === 'sub-admin') {
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin' && $selectedSubAdminId) {
                $query->where('branch_id', $selectedSubAdminId);
            } elseif ($role === 'staff') {
                $query->where('branch_id', $userBranchId ?: $userId);
            } else {
                $query->where('branch_id', $userId);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No orders found for the given filters.',
                ], 404);
            }

            // 🔹 Generate PDF
            $pdf = Pdf::loadView('sales.orders_pdf', [
                'orders' => $orders,
                'setting' => $setting,
            ])->setPaper('A4', 'landscape');

            // 🔹 Save PDF to storage
            $fileName = 'sales_report_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'sales-reports/' . $fileName;
            \Storage::disk('public')->put($relativePath, $pdf->output());

            // 🔹 Generate full URL

            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            // 🔹 Return JSON response
            return response()->json([
                'status' => true,
                'message' => 'Sales report PDF generated successfully.',
                'file_url' => $fileUrl,
                'file_name' => $fileName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while generating PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function export_sales_report_pdf_api(Request $request)
    {
        try {
            $authUser = Auth::guard('api')->user();
            $selectedSubAdminId = $request->selectedSubAdminId ?? null;

            // 🔹 Determine branch_id based on role
            if ($authUser->role === 'staff' && $authUser->branch_id) {
                $branchIdToUse = $authUser->branch_id;
            } elseif ($authUser->role === 'admin' && !empty($selectedSubAdminId)) {
                $branchIdToUse = $selectedSubAdminId;
            } else {
                $branchIdToUse = $authUser->id;
            }

            // 🔹 Get all IDs from form-data (ids[] = 1, ids[] = 2, etc.)
            $idsArray = $request->input('ids', []);

            // 🔹 Store all IDs as comma-separated string (for logging/debugging)
            $idsString = implode(',', $idsArray);

            if (empty($idsArray)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sales IDs provided.',
                ]);
            }

            // 🔹 Load related models
            $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
                ->whereIn('id', $idsArray)
                ->get();

            if ($sales->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sales data found.',
                ]);
            }

            // 🔹 Settings and currency info
            $setting = Setting::where('branch_id', $branchIdToUse)->first();
            $currencySymbol = $setting->currency_symbol ?? '₹';
            $currencyPosition = $setting->currency_position ?? 'left';

            $subtotal = $sales->sum('total_amount');
            $discountAmount = 0;

            // 🔹 Discount calculation
            foreach ($sales as $sale) {
                if ($sale->invoice && $sale->invoice->discount) {
                    $discountPercent = $sale->invoice->discount;
                    $discountAmount += ($sale->total_amount * $discountPercent) / 100;
                }
            }

            $subtotalAfterDiscount = $subtotal - $discountAmount;

            // 🔹 Tax calculation
            $taxDetails = [];
            $totalTaxAmount = 0;

            foreach ($sales as $sale) {
                $rowTaxes = [];
                $rowTaxAmount = 0;
                $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
                $rowTaxIds = $sale->invoice->tax_id ?? '[]';
                $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

                $rowTaxRates = collect();
                if ($rowGSTOption === 'with_gst' && !empty($rowTaxIdsArray)) {
                    $rowTaxRates = TaxRate::where('status', 'active')
                        ->where('branch_id', $branchIdToUse)
                        ->where('isDeleted', 0)
                        ->whereIn('id', $rowTaxIdsArray)
                        ->get();
                }

                $unitPrice = $sale->price;

                // Apply discount per unit
                if ($sale->invoice && $sale->invoice->discount) {
                    $discountPercent = $sale->invoice->discount;
                    $unitPrice -= ($unitPrice * $discountPercent) / 100;
                }

                foreach ($rowTaxRates as $tax) {
                    $taxBase = $unitPrice * $sale->quantity;
                    $amount = $taxBase * ($tax->tax_rate / 100);

                    $rowTaxes[] = [
                        'name' => $tax->tax_name,
                        'rate' => $tax->tax_rate,
                        'amount' => $amount,
                    ];

                    $rowTaxAmount += $amount;

                    if (!isset($taxDetails[$tax->id])) {
                        $taxDetails[$tax->id] = [
                            'name' => $tax->tax_name,
                            'rate' => $tax->tax_rate,
                            'amount' => 0,
                        ];
                    }
                    $taxDetails[$tax->id]['amount'] += $amount;
                }

                $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;
                $totalTaxAmount += $rowTaxAmount;
            }

            $totalAmount = $sales->sum('rowFinalTotal');

            // 🔹 Prepare PDF data
            $pdfData = [
                'sales' => $sales,
                'setting' => $setting,
                'currencySymbol' => $currencySymbol,
                'currencyPosition' => $currencyPosition,
                'subtotal' => $subtotal,
                'discountAmount' => $discountAmount,
                'taxDetails' => $taxDetails,
                'totalTaxAmount' => $totalTaxAmount,
                'totalAmount' => $totalAmount,
            ];

            // 🔹 Generate PDF
            $pdf = PDF::loadView('sales.sales-invoice-report-pdf', $pdfData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                ]);

            // 🔹 Save PDF file in storage
            $fileName = 'sales_report_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'sales-reports/' . $fileName;

            \Storage::disk('public')->put($relativePath, $pdf->output());

            // 🔹 Get full URL
            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status' => true,
                'message' => 'Sales Report PDF generated successfully.',
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'ids_used' => $idsString, // 👈 for debug
            ]);

        } catch (\Throwable $e) {
            \Log::error('Sales PDF Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate Sales Report PDF.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function view_sales_report(Request $request)
    {
        try {
            $ids = $request->input('ids'); // can be array or comma-separated string
            $selectedSubAdminId = $request->input('selectedSubAdminId');

            if (empty($ids)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sales selected for report view.',
                ], 400);
            }

            // Convert to array if comma-separated
            $idsArray = is_array($ids) ? $ids : explode(',', $ids);

            // 🔹 Store IDs in a comma-separated string for URL
            $idsString = implode(',', $idsArray);

            // 🔹 Determine branch_id (safe fallback)
            // If user is logged in, we can still read role info
            $authUser = Auth::guard('api')->user();
            if ($authUser) {
                if ($authUser->role === 'staff' && $authUser->branch_id) {
                    $branchIdToUse = $authUser->branch_id;
                } elseif ($authUser->role === 'admin' && !empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } else {
                    $branchIdToUse = $authUser->id;
                }
            } else {
                // 🔹 No authentication — require branch ID from frontend
                if (!empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Branch ID missing (unauthenticated request).',
                    ], 400);
                }
            }

            // ✅ Generate the report URL
            // $reportUrl = url('sales/report/view-page?ids=' . urlencode($idsString) . '&branch=' . $branchIdToUse);
            $reportUrl = url('sales/report/view-page?ids=' . $idsString . '&branch=' . $branchIdToUse);

            return response()->json([
                'status' => true,
                'message' => 'Sales report link generated successfully.',
                'view_link' => $reportUrl,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate sales report link.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update_order_type(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_type' => 'required|in:Delivery,Self Pickup'
        ]);
        
        $order = Order::find($request->order_id);
        if ($order) {
            $order->order_type = $request->order_type;
            $order->save();
            return response()->json(['status' => true, 'message' => 'Order type updated successfully.']);
        }
        return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
    }
}
