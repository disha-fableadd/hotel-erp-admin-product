<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class KotController extends Controller
{
    protected function branchIdToUse()
    {
        $user = Auth::user();
        $role = $user->role ?? '';
        $subAdminId = session('selectedSubAdminId');

        if ($role === 'sub-admin') {
            return $user->id;
        }
        if ($role === 'admin' && !empty($subAdminId)) {
            return $subAdminId;
        }
        if ($role === 'staff') {
            return $user->branch_id;
        }
        return $user->id;
    }

    public function index(Request $request)
    {
        $branchId = $this->branchIdToUse();
        $setting = Setting::where('branch_id', $branchId)->first();
        $filterStation = $request->get('station');
        $filterType = $request->get('order_type');

        // Default: today (Asia/Kolkata). Pass date=all to see every open ticket.
        $today = Carbon::now('Asia/Kolkata')->toDateString();
        $rawDate = $request->get('date', $today);
        $filterDate = null;
        if ($rawDate !== null && strtolower((string) $rawDate) !== 'all' && trim((string) $rawDate) !== '') {
            try {
                $filterDate = Carbon::parse($rawDate, 'Asia/Kolkata')->toDateString();
            } catch (\Throwable $e) {
                $filterDate = $today;
            }
        }

        $allTickets = $this->buildTickets($branchId, null, $filterDate);
        $tickets = $allTickets
            ->when($filterStation, fn ($c) => $c->filter(
                fn ($t) => strcasecmp($t['station'], $filterStation) === 0
            )->values())
            ->when($filterType, fn ($c) => $c->filter(
                fn ($t) => ($t['service_key'] ?? '') === $this->normalizeServiceKey($filterType)
            )->values());

        // Stations for chips: from today's (or selected date) tickets
        $stations = $allTickets->pluck('station')->unique()->sort()->values();

        $orderTypeOptions = [
            'dine_in' => 'Dine In',
            'takeaway' => 'Takeaway',
            'self_pickup' => 'Self Pickup',
            'delivery' => 'Delivery',
        ];

        return view('sales.kot_board', [
            'tickets' => $tickets,
            'stations' => $stations,
            'orderTypeOptions' => $orderTypeOptions,
            'currency_symbol' => $setting->currency_symbol ?? '₹',
            'filterStation' => $filterStation,
            'filterType' => $filterType ? $this->normalizeServiceKey($filterType) : null,
            'filterDate' => $filterDate,
            'todayDate' => $today,
        ]);
    }

    public function print(Request $request, $orderId)
    {
        extract($this->kotPayload($request, $orderId));

        return view('sales.kot_print', compact('order', 'items', 'station', 'isDelta', 'kotTitle'));
    }

    /**
     * Thermal KOT PDF for QZ / Bluetooth kitchen printers.
     */
    public function printPdf(Request $request, $orderId)
    {
        extract($this->kotPayload($request, $orderId));

        $pdf = Pdf::loadView('sales.kot_print_pdf', compact('order', 'items', 'station', 'isDelta', 'kotTitle'))
            ->setPaper([0, 0, 226.77, max(280, 120 + (max(1, count($items)) * 28))], 'portrait');

        $filename = 'kot_' . $orderId . ($isDelta ? '_new' : '') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'X-Kot-Pending-Count' => (string) count($items),
        ]);
    }

    /**
     * How many unsent / changed lines kitchen still needs (Petpooja-style delta).
     */
    public function deltaSummary(Request $request, $orderId)
    {
        $request->query->set('delta', '1');
        // Full delta (both targets) unless client already filtered
        extract($this->kotPayload($request, $orderId));

        $mapped = collect($items)->map(function ($item) {
            $product = is_object($item->product ?? null) ? $item->product : null;
            $target = $item->print_target
                ?? (isset($item->product) ? $this->resolveItemPrintTarget($item) : Category::KOT_TARGET_KITCHEN);
            $station = $product->kitchen_station
                ?? ($this->resolveItemStation($item) ?? 'General');

            return [
                'name' => ($product->name ?? null) ?: ($item->product_name ?? 'Item'),
                'quantity' => (float) $item->quantity,
                'action' => $item->kot_action ?? 'new',
                'print_target' => $target,
                'station' => $station,
            ];
        })->values();

        $kitchenCount = $mapped->where('print_target', Category::KOT_TARGET_KITCHEN)->count();
        $billingCount = $mapped->where('print_target', Category::KOT_TARGET_BILLING)->count();

        return response()->json([
            'status' => true,
            'order_id' => (int) $orderId,
            'count' => $mapped->count(),
            'kitchen_count' => $kitchenCount,
            'billing_count' => $billingCount,
            'items' => $mapped,
        ]);
    }

    /**
     * After kitchen print succeeds: mark current qty as sent so next Update only prints new lines.
     */
    public function markPrinted(Request $request, $orderId)
    {
        $branchId = $this->branchIdToUse();
        $order = Order::with(['order_items.product.category'])
            ->where('id', $orderId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        $printTarget = $this->normalizePrintTarget($request->input('print_target') ?: $request->input('target'));
        $station = $request->input('station');

        if (Schema::hasColumn('order_items', 'kot_printed_qty')) {
            OrderItem::where('order_id', $order->id)
                ->where(function ($q) {
                    $q->where('isDeleted', 0)->orWhereNull('isDeleted');
                })
                ->with(['product.category'])
                ->get()
                ->each(function (OrderItem $item) use ($printTarget, $station) {
                    $itemStation = $this->resolveItemStation($item);
                    $itemTarget = $this->resolveItemPrintTarget($item);
                    if ($printTarget && $itemTarget !== $printTarget) {
                        return;
                    }
                    if ($station && strcasecmp($itemStation, $station) !== 0) {
                        return;
                    }
                    $item->kot_printed_qty = max(0, (int) $item->quantity);
                    $item->save();
                });
        }

        // Remove only lines that were just printed from the pending fire ticket
        if (Schema::hasColumn('orders', 'kot_pending_cancels') && is_array($order->kot_pending_cancels)) {
            if (! $printTarget && empty($station)) {
                $order->kot_pending_cancels = [];
            } else {
                $remaining = collect($order->kot_pending_cancels)->filter(function ($line) use ($printTarget, $station) {
                    $lineStation = $line['station'] ?? 'General';
                    $lineTarget = $this->normalizePrintTarget($line['print_target'] ?? null)
                        ?: ((strcasecmp((string) $lineStation, Category::billingKotStationLabel()) === 0)
                            ? Category::KOT_TARGET_BILLING
                            : Category::KOT_TARGET_KITCHEN);
                    if ($printTarget && $lineTarget !== $printTarget) {
                        return true; // keep other target lines
                    }
                    if ($station && strcasecmp((string) $lineStation, (string) $station) !== 0) {
                        return true;
                    }

                    return false; // drop printed lines
                })->values()->all();
                $order->kot_pending_cancels = $remaining;
            }
            $order->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'KOT marked as sent',
            'order_id' => (int) $order->id,
            'print_target' => $printTarget,
        ]);
    }

    protected function normalizePrintTarget($raw): ?string
    {
        $v = strtolower(trim((string) $raw));
        if ($v === '' || $v === 'all') {
            return null;
        }
        if (in_array($v, ['billing', 'bill', 'counter', 'billing_counter'], true)) {
            return Category::KOT_TARGET_BILLING;
        }
        if (in_array($v, ['kitchen', 'kot'], true)) {
            return Category::KOT_TARGET_KITCHEN;
        }

        return null;
    }

    protected function resolveItemPrintTarget($item): string
    {
        $product = is_object($item->product ?? null) ? $item->product : null;
        if ($product) {
            if (! $product->relationLoaded('category') && ! empty($product->category_id)) {
                $product->load('category');
            }

            return Category::resolveKotPrintTarget($product);
        }
        $catId = $item->category_id ?? null;

        return $catId ? Category::resolveKotPrintTarget($catId) : Category::KOT_TARGET_KITCHEN;
    }

    /**
     * Board/PDF station: billing-target categories always group under "Counter".
     */
    protected function resolveItemStation($item): string
    {
        if ($this->resolveItemPrintTarget($item) === Category::KOT_TARGET_BILLING) {
            return Category::billingKotStationLabel();
        }
        $product = is_object($item->product ?? null) ? $item->product : null;
        $station = trim((string) ($product->kitchen_station ?? ''));

        return $station !== '' ? $station : 'General';
    }

    protected function kotPayload(Request $request, $orderId): array
    {
        $branchId = $this->branchIdToUse();
        $order = Order::with(['order_items.product.category', 'order_items.variant', 'tableNo'])
            ->where('id', $orderId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        $station = $request->get('station');
        $printTarget = $this->normalizePrintTarget($request->get('print_target') ?: $request->get('target'));
        // Station "Counter" implies billing printer target
        if (! $printTarget && $station && strcasecmp((string) $station, Category::billingKotStationLabel()) === 0) {
            $printTarget = Category::KOT_TARGET_BILLING;
        }

        $isDelta = filter_var($request->get('delta', false), FILTER_VALIDATE_BOOLEAN)
            || $request->get('delta') === '1'
            || $request->get('delta') === 1;

        $items = $order->order_items->filter(function ($item) use ($station, $printTarget) {
            if ((int) ($item->isDeleted ?? 0) === 1) {
                return false;
            }
            $itemStation = $this->resolveItemStation($item);
            $itemTarget = $this->resolveItemPrintTarget($item);
            if ($printTarget && $itemTarget !== $printTarget) {
                return false;
            }
            return ! $station || strcasecmp($itemStation, $station) === 0;
        })->values();

        if ($isDelta) {
            $items = $this->buildDeltaItems($order, $items, $station, $printTarget);
            $kotTitle = $printTarget === Category::KOT_TARGET_BILLING
                ? 'COUNTER KOT - NEW / CHANGED'
                : 'KOT - NEW / CHANGED';
        } else {
            $kotTitle = $printTarget === Category::KOT_TARGET_BILLING ? 'COUNTER KOT' : 'KOT';
        }

        return compact('order', 'items', 'station', 'isDelta', 'kotTitle', 'printTarget');
    }

    /**
     * Petpooja-style: only what changed on the latest Update/Place fire ticket.
     * Prefers order.kot_pending_cancels (full fire ticket with action=new|cancel).
     * Falls back to quantity − kot_printed_qty.
     */
    protected function buildDeltaItems(Order $order, $activeItems, $station = null, $printTarget = null)
    {
        $rows = collect();
        $pending = $order->kot_pending_cancels ?? [];

        // Latest Update writes a full fire ticket (action=new|cancel). Use that only.
        $hasFireTicket = is_array($pending) && collect($pending)->contains(function ($row) {
            return isset($row['action']) && in_array($row['action'], ['new', 'cancel'], true);
        });

        if ($hasFireTicket) {
            foreach ($pending as $line) {
                $qty = (int) ($line['qty'] ?? $line['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $action = $line['action'] ?? 'new';
                $name = $line['name'] ?? $line['product_name'] ?? 'Item';
                $itemStation = $line['station'] ?? 'General';
                $lineTarget = $this->normalizePrintTarget($line['print_target'] ?? null)
                    ?: ((strcasecmp((string) $itemStation, Category::billingKotStationLabel()) === 0)
                        ? Category::KOT_TARGET_BILLING
                        : Category::KOT_TARGET_KITCHEN);
                if ($printTarget && $lineTarget !== $printTarget) {
                    continue;
                }
                if ($station && strcasecmp($itemStation, $station) !== 0) {
                    continue;
                }

                $rows->push((object) [
                    'quantity' => $qty,
                    'kot_action' => $action,
                    'variant_name' => $line['variant_name'] ?? null,
                    'addons_text' => $line['addons_text'] ?? null,
                    'print_target' => $lineTarget,
                    'product' => (object) [
                        'name' => $name,
                        'kitchen_station' => $itemStation,
                    ],
                    'product_name' => $name,
                ]);
            }

            return $rows->values();
        }

        foreach ($activeItems as $item) {
            $qty = (int) $item->quantity;
            $printed = (int) ($item->kot_printed_qty ?? 0);
            $diff = $qty - $printed;
            if ($diff === 0) {
                continue;
            }
            $itemTarget = $this->resolveItemPrintTarget($item);
            if ($printTarget && $itemTarget !== $printTarget) {
                continue;
            }

            $clone = clone $item;
            $clone->quantity = abs($diff);
            $clone->kot_action = $diff > 0 ? 'new' : 'cancel';
            $clone->print_target = $itemTarget;
            // Ensure variation / add-ons show on delta KOT when fire ticket is absent
            $clone->variant_name = method_exists($item, 'variantLabel')
                ? $item->variantLabel()
                : (optional($item->variant)->name ?? null);
            $addonLines = method_exists($item, 'addonLines') ? $item->addonLines() : [];
            $clone->addons_text = $addonLines ? implode(', ', $addonLines) : null;
            $rows->push($clone);
        }

        if (is_array($pending)) {
            foreach ($pending as $cancel) {
                $qty = (int) ($cancel['qty'] ?? $cancel['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $name = $cancel['name'] ?? $cancel['product_name'] ?? 'Item';
                $itemStation = $cancel['station'] ?? 'General';
                $lineTarget = $this->normalizePrintTarget($cancel['print_target'] ?? null)
                    ?: ((strcasecmp((string) $itemStation, Category::billingKotStationLabel()) === 0)
                        ? Category::KOT_TARGET_BILLING
                        : Category::KOT_TARGET_KITCHEN);
                if ($printTarget && $lineTarget !== $printTarget) {
                    continue;
                }
                if ($station && strcasecmp($itemStation, $station) !== 0) {
                    continue;
                }

                $rows->push((object) [
                    'quantity' => $qty,
                    'kot_action' => 'cancel',
                    'variant_name' => $cancel['variant_name'] ?? null,
                    'addons_text' => $cancel['addons_text'] ?? null,
                    'print_target' => $lineTarget,
                    'product' => (object) [
                        'name' => $name,
                        'kitchen_station' => $itemStation,
                    ],
                    'product_name' => $name,
                ]);
            }
        }

        return $rows->values();
    }

    /**
     * Normalize order_type / type into a stable service key for KOT filters & badges.
     */
    protected function normalizeServiceKey($raw): string
    {
        $v = strtolower(trim((string) $raw));
        $v = str_replace(['-', ' '], '_', $v);

        if (in_array($v, ['dinein', 'dine_in', 'dining', 'dine'], true)) {
            return 'dine_in';
        }
        if (in_array($v, ['takeaway', 'take_away', 'parcel'], true)) {
            return 'takeaway';
        }
        if (in_array($v, ['self_pickup', 'selfpickup', 'pickup', 'pick_up'], true)) {
            return 'self_pickup';
        }
        if (in_array($v, ['delivery', 'deliver'], true)) {
            return 'delivery';
        }

        return $v !== '' ? $v : 'dine_in';
    }

    protected function resolveServiceMeta(Order $order): array
    {
        // Prefer explicit order_type; fall back to type / table presence
        $raw = $order->order_type ?: $order->type;
        if (empty($raw) && !empty($order->table_no)) {
            $raw = 'dine_in';
        }
        if (empty($raw)) {
            $raw = 'takeaway';
        }

        $key = $this->normalizeServiceKey($raw);

        // Table dine-in wins when table is attached and type isn't clearly takeaway/delivery
        if (!empty($order->table_no) && !in_array($key, ['takeaway', 'self_pickup', 'delivery'], true)) {
            $key = 'dine_in';
        }

        $labels = [
            'dine_in' => 'Dine In',
            'takeaway' => 'Takeaway',
            'self_pickup' => 'Self Pickup',
            'delivery' => 'Delivery',
        ];

        $title = match ($key) {
            'dine_in' => optional($order->tableNo)->table_name
                ?: ($order->table_no ? ('Table #' . $order->table_no) : 'Dine In'),
            'takeaway' => 'Takeaway',
            'self_pickup' => 'Self Pickup',
            'delivery' => 'Delivery',
            default => $labels[$key] ?? ucwords(str_replace('_', ' ', $key)),
        };

        return [
            'service_key' => $key,
            'service_label' => $labels[$key] ?? ucwords(str_replace('_', ' ', $key)),
            'title' => $title,
        ];
    }

    protected function buildTickets($branchId, $filterStation = null, $filterDate = null)
    {
        $orders = Order::with(['order_items.product.category', 'order_items.variant', 'tableNo'])
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->where(function ($q) {
                $q->where('table_status', 1)
                    ->orWhereIn('payment_status', ['pending', 'partial', 'partially_paid']);
            })
            ->when($filterDate, function ($q) use ($filterDate) {
                // Orders store created_at in Asia/Kolkata wall time
                $q->whereBetween('created_at', [
                    $filterDate . ' 00:00:00',
                    $filterDate . ' 23:59:59',
                ]);
            })
            ->orderByDesc('id')
            ->limit(60)
            ->get();

        $rows = collect();
        foreach ($orders as $order) {
            $service = $this->resolveServiceMeta($order);
            $grouped = [];
            foreach ($order->order_items as $item) {
                if ((int) ($item->isDeleted ?? 0) === 1) {
                    continue;
                }
                $station = $this->resolveItemStation($item);
                $printTarget = $this->resolveItemPrintTarget($item);
                if ($filterStation && strcasecmp($station, $filterStation) !== 0) {
                    continue;
                }
                $groupKey = $station . '|' . $printTarget;
                if (! isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'station' => $station,
                        'print_target' => $printTarget,
                        'items' => [],
                    ];
                }
                $grouped[$groupKey]['items'][] = $item;
            }
            foreach ($grouped as $group) {
                $rows->push([
                    'order' => $order,
                    'station' => $group['station'],
                    'print_target' => $group['print_target'],
                    'items' => $group['items'],
                    'table' => $service['title'],
                    'service_key' => $service['service_key'],
                    'service_label' => $service['service_label'],
                    'title' => $service['title'],
                ]);
            }
        }

        return $rows;
    }
}
