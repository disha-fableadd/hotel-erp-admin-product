<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchaseInvoice;
use App\Models\Setting;
use App\Models\Table_no;
use App\Models\User;
use App\Services\PosPricingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Authoritative dashboard / today-sales metrics (same rules as Web ERP).
 * Used by tables floor + Flutter POST /api/v1/sync/dashboard-metrics.
 */
class DashboardMetricsService
{
    /**
     * Today's sales summary for tables floor / Desktop KPI cards.
     * Same as Web: branch + DATE(created_at)=today (IST), all payment statuses.
     * Categories with Apply GST OFF = Liquor; GST ON = Food.
     *
     * @return array{total_sales: float, food_sales: float, liquor_sales: float, currency_symbol: string, currency_position: string, date: string}
     */
    public function todaySalesSummary($branchIdToUse, ?Setting $setting = null): array
    {
        $setting = $setting ?: Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol = $setting->currency_symbol ?? '₹';
        $currencyPosition = $setting->currency_position ?? 'left';

        $today = Carbon::now('Asia/Kolkata')->format('Y-m-d');

        $ordersQuery = Order::query()
            ->where('isDeleted', 0)
            ->whereRaw('DATE(created_at) = ?', [$today]);

        if (! empty($branchIdToUse)) {
            $ordersQuery->where('branch_id', $branchIdToUse);
        }

        $orders = (clone $ordersQuery)->get(['id', 'total_amount']);
        $totalSales = round((float) $orders->sum('total_amount'), 2);

        $foodSales = 0.0;
        $liquorSales = 0.0;

        $orderIds = $orders->pluck('id');
        if ($orderIds->isNotEmpty() && $totalSales > 0) {
            $items = OrderItem::query()
                ->whereIn('order_id', $orderIds)
                ->where(function ($q) {
                    $q->where('isDeleted', 0)->orWhereNull('isDeleted');
                })
                ->select([
                    'id',
                    'order_id',
                    'category_id',
                    'product_id',
                    'price',
                    'quantity',
                    'total_amount',
                    'item_discount_amount',
                ])
                ->get();

            $categoryIds = $items->pluck('category_id')->filter()->unique()->values();
            $gstByCategory = [];
            $itemTypeByCategory = [];
            if ($categoryIds->isNotEmpty()) {
                if (Schema::hasColumn('categories', 'gst_applicable')) {
                    $gstByCategory = Category::whereIn('id', $categoryIds)
                        ->pluck('gst_applicable', 'id')
                        ->map(fn ($v) => $v === null ? true : (bool) $v)
                        ->all();
                }
                if (Schema::hasColumn('categories', 'item_type')) {
                    $itemTypeByCategory = Category::whereIn('id', $categoryIds)
                        ->pluck('item_type', 'id')
                        ->map(fn ($v) => strtolower(trim((string) $v)))
                        ->all();
                }
            }

            $missingCatProductIds = $items
                ->filter(fn ($i) => empty($i->category_id))
                ->pluck('product_id')
                ->filter()
                ->unique()
                ->values();
            $productCategoryMap = [];
            $productItemTypeMap = [];
            if ($missingCatProductIds->isNotEmpty()) {
                $productRows = DB::table('products')->whereIn('id', $missingCatProductIds)->get();
                foreach ($productRows as $pr) {
                    $productCategoryMap[$pr->id] = $pr->category_id ?? null;
                    if (Schema::hasColumn('products', 'item_type') && ! empty($pr->item_type ?? null)) {
                        $productItemTypeMap[$pr->id] = strtolower(trim((string) $pr->item_type));
                    }
                }
                $extraCatIds = collect($productCategoryMap)->filter()->unique()->diff(array_keys($gstByCategory));
                if ($extraCatIds->isNotEmpty() && Schema::hasColumn('categories', 'gst_applicable')) {
                    $extra = Category::whereIn('id', $extraCatIds)
                        ->pluck('gst_applicable', 'id')
                        ->map(fn ($v) => $v === null ? true : (bool) $v)
                        ->all();
                    $gstByCategory += $extra;
                }
                if ($extraCatIds->isNotEmpty() && Schema::hasColumn('categories', 'item_type')) {
                    $extraTypes = Category::whereIn('id', $extraCatIds)
                        ->pluck('item_type', 'id')
                        ->map(fn ($v) => strtolower(trim((string) $v)))
                        ->all();
                    $itemTypeByCategory += $extraTypes;
                }
            }

            $lineFoodByOrder = [];
            $lineLiquorByOrder = [];
            foreach ($items as $item) {
                $line = (float) ($item->total_amount ?? 0);
                if ($line <= 0) {
                    $unit = (float) ($item->price ?? 0);
                    $qty = (float) ($item->quantity ?? 0);
                    $line = max(0, ($unit * $qty) - (float) ($item->item_discount_amount ?? 0));
                }

                $catId = $item->category_id ?: ($productCategoryMap[$item->product_id] ?? null);
                $isFood = true;

                $explicitType = null;
                if (! empty($productItemTypeMap[$item->product_id] ?? null)) {
                    $explicitType = $productItemTypeMap[$item->product_id];
                } elseif ($catId !== null && $catId !== '' && array_key_exists((int) $catId, $itemTypeByCategory)) {
                    $explicitType = $itemTypeByCategory[(int) $catId];
                }

                if ($explicitType !== null && $explicitType !== '') {
                    $isFood = ! in_array($explicitType, ['liquor', 'beverage', 'tobacco', 'bar'], true);
                } elseif ($catId !== null && $catId !== '') {
                    $cid = (int) $catId;
                    if (array_key_exists($cid, $gstByCategory)) {
                        $isFood = (bool) $gstByCategory[$cid];
                    } else {
                        $isFood = PosPricingService::categoryGstApplicable($catId);
                    }
                }

                $oid = (int) $item->order_id;
                if ($isFood) {
                    $lineFoodByOrder[$oid] = ($lineFoodByOrder[$oid] ?? 0) + $line;
                } else {
                    $lineLiquorByOrder[$oid] = ($lineLiquorByOrder[$oid] ?? 0) + $line;
                }
            }

            foreach ($orders as $order) {
                $oid = (int) $order->id;
                $billTotal = (float) ($order->total_amount ?? 0);
                if ($billTotal <= 0) {
                    continue;
                }
                $f = (float) ($lineFoodByOrder[$oid] ?? 0);
                $l = (float) ($lineLiquorByOrder[$oid] ?? 0);
                $net = $f + $l;
                if ($net <= 0) {
                    $foodSales += $billTotal;
                    continue;
                }
                $foodPart = round($billTotal * ($f / $net), 2);
                $liquorPart = round($billTotal - $foodPart, 2);
                $foodSales += $foodPart;
                $liquorSales += $liquorPart;
            }
        }

        $foodSales = round($foodSales, 2);
        $liquorSales = round($liquorSales, 2);
        $partsSum = round($foodSales + $liquorSales, 2);
        if ($totalSales > 0 && abs($partsSum - $totalSales) <= 0.05) {
            $liquorSales = round($totalSales - $foodSales, 2);
        }

        return [
            'total_sales' => $totalSales,
            'food_sales' => $foodSales,
            'liquor_sales' => $liquorSales,
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'date' => $today,
        ];
    }

    /**
     * Full dashboard metrics payload for Flutter Desktop.
     *
     * @param  array{branch_id?: int|string, date_filter?: string, start_date?: string, end_date?: string}  $options
     */
    public function build(array $options = []): array
    {
        $branchId = (int) ($options['branch_id'] ?? 1);
        $dateFilter = strtolower(trim((string) ($options['date_filter'] ?? 'all_time')));
        if ($dateFilter === '' || $dateFilter === 'all') {
            $dateFilter = 'all_time';
        }

        [$filterStart, $filterEnd] = $this->resolveDateRange(
            $dateFilter,
            $options['start_date'] ?? null,
            $options['end_date'] ?? null
        );

        $setting = Setting::where('branch_id', $branchId)->first();
        $today = $this->todaySalesSummary($branchId, $setting);

        $salesBase = Order::query()->where('isDeleted', '!=', 1);
        if ($branchId > 0) {
            $salesBase->where('branch_id', $branchId);
        }
        if ($filterStart) {
            $salesBase->whereBetween('created_at', [$filterStart, $filterEnd]);
        }

        $totalSalesAmount = round((float) (clone $salesBase)->sum('total_amount'), 2);
        $salesInvoicesCount = (int) (clone $salesBase)->count();

        $completedCount = (int) (clone $salesBase)->whereIn('payment_status', ['Completed', 'Paid', 'completed', 'paid'])->count();
        $partiallyCount = (int) (clone $salesBase)->whereIn('payment_status', ['Partially', 'partially', 'Partially Paid'])->count();
        $pendingCount = (int) (clone $salesBase)->whereIn('payment_status', ['Pending', 'pending', 'Unpaid', 'unpaid'])->count();

        $pct = function (int $n) use ($salesInvoicesCount): float {
            if ($salesInvoicesCount <= 0) {
                return 0.0;
            }

            return round(($n / $salesInvoicesCount) * 100, 1);
        };

        $purchaseQuery = PurchaseInvoice::query()->where('isDeleted', '!=', 1);
        $expenseQuery = Expense::query()->where('isDeleted', '!=', 1);
        if ($branchId > 0) {
            $purchaseQuery->where('branch_id', $branchId);
            $expenseQuery->where('branch_id', $branchId);
        }
        if ($filterStart) {
            $purchaseQuery->whereBetween('created_at', [$filterStart, $filterEnd]);
            $expenseQuery->whereBetween('created_at', [$filterStart, $filterEnd]);
        }

        $thisMonthStart = Carbon::now('Asia/Kolkata')->startOfMonth();
        $thisMonthEnd = Carbon::now('Asia/Kolkata')->endOfDay();
        $thisMonthCount = (int) Order::query()
            ->where('isDeleted', '!=', 1)
            ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $runningTables = 0;
        if (Schema::hasTable('table_no')) {
            $tq = Table_no::query();
            if (Schema::hasColumn('table_no', 'isDeleted')) {
                $tq->where(function ($q) {
                    $q->where('isDeleted', '!=', 1)->orWhereNull('isDeleted');
                });
            }
            if ($branchId > 0 && Schema::hasColumn('table_no', 'branch_id')) {
                $tq->where('branch_id', $branchId);
            }
            if (Schema::hasColumn('table_no', 'status')) {
                $runningTables = (int) (clone $tq)->whereIn('status', [1, '1', 'occupied', 'Occupied', 'running', 'Running'])->count();
            }
            // Fallback: active dine-in sessions on orders
            if ($runningTables === 0 && Schema::hasColumn('orders', 'table_status')) {
                $runningTables = (int) Order::query()
                    ->where('isDeleted', '!=', 1)
                    ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('table_status', 1)
                    ->where(function ($q) {
                        $q->where('delivery_status', 'running')
                            ->orWhereNull('delivery_status')
                            ->orWhere('delivery_status', '');
                    })
                    ->count();
            }
        }

        $tablesCount = 0;
        if (Schema::hasTable('table_no')) {
            $tablesCount = (int) Table_no::query()
                ->when(Schema::hasColumn('table_no', 'isDeleted'), function ($q) {
                    $q->where(function ($qq) {
                        $qq->where('isDeleted', '!=', 1)->orWhereNull('isDeleted');
                    });
                })
                ->when($branchId > 0 && Schema::hasColumn('table_no', 'branch_id'), fn ($q) => $q->where('branch_id', $branchId))
                ->count();
        }

        $vendorsCount = (int) User::query()
            ->where('role', 'vendor')
            ->where('isDeleted', '!=', 1)
            ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        $purchaseInvoicesCount = (int) (clone $purchaseQuery)->count();

        return [
            'today_metrics' => [
                'today_total_sales' => $today['total_sales'],
                'today_food_sales' => $today['food_sales'],
                'today_liquor_share' => $today['liquor_sales'],
                'date' => $today['date'],
                'currency_symbol' => $today['currency_symbol'],
                'currency_position' => $today['currency_position'],
            ],
            'dashboard_metrics' => [
                'total_sales_amount' => $totalSalesAmount,
                'total_purchase_amount' => round((float) (clone $purchaseQuery)->sum('grand_total'), 2),
                'total_expense_amount' => round((float) (clone $expenseQuery)->sum('amount'), 2),
                'running_tables_count' => $runningTables,
                'sales_invoices_count' => $salesInvoicesCount,
                'purchase_invoices_count' => $purchaseInvoicesCount,
                'tables_count' => $tablesCount,
                'vendors_count' => $vendorsCount,
                'date_filter' => $dateFilter,
                'sales_volume_breakdown' => [
                    'total_orders' => $salesInvoicesCount,
                    'this_month' => $thisMonthCount,
                    'completed_count' => $completedCount,
                    'completed_percent' => $pct($completedCount),
                    'partially_paid_count' => $partiallyCount,
                    'partially_paid_percent' => $pct($partiallyCount),
                    'pending_count' => $pendingCount,
                    'pending_percent' => $pct($pendingCount),
                ],
            ],
        ];
    }

    /**
     * @return array{0: ?Carbon, 1: Carbon}
     */
    protected function resolveDateRange(string $dateFilter, $startDate = null, $endDate = null): array
    {
        $now = Carbon::now('Asia/Kolkata');
        $end = $now->copy()->endOfDay();
        $start = null;

        switch ($dateFilter) {
            case 'today':
                $start = $now->copy()->startOfDay();
                break;
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                break;
            case 'this_week':
                $start = $now->copy()->startOfWeek();
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                break;
            case 'custom':
                if (! empty($startDate)) {
                    $start = Carbon::parse($startDate, 'Asia/Kolkata')->startOfDay();
                }
                if (! empty($endDate)) {
                    $end = Carbon::parse($endDate, 'Asia/Kolkata')->endOfDay();
                }
                break;
            case 'all_time':
            default:
                $start = null;
                break;
        }

        return [$start, $end];
    }
}
