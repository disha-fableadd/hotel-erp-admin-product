<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $tickets = $this->buildTickets($branchId, $request->get('station'));

        return view('sales.kot_board', [
            'tickets' => $tickets,
            'stations' => $tickets->pluck('station')->unique()->values(),
            'currency_symbol' => $setting->currency_symbol ?? '₹',
            'filterStation' => $request->get('station'),
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
        extract($this->kotPayload($request, $orderId));

        return response()->json([
            'status' => true,
            'order_id' => (int) $orderId,
            'count' => count($items),
            'items' => collect($items)->map(function ($item) {
                $product = is_object($item->product ?? null) ? $item->product : null;
                return [
                    'name' => ($product->name ?? null) ?: ($item->product_name ?? 'Item'),
                    'quantity' => (float) $item->quantity,
                    'action' => $item->kot_action ?? 'new',
                ];
            })->values(),
        ]);
    }

    /**
     * After kitchen print succeeds: mark current qty as sent so next Update only prints new lines.
     */
    public function markPrinted(Request $request, $orderId)
    {
        $branchId = $this->branchIdToUse();
        $order = Order::where('id', $orderId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        if (Schema::hasColumn('order_items', 'kot_printed_qty')) {
            OrderItem::where('order_id', $order->id)
                ->where(function ($q) {
                    $q->where('isDeleted', 0)->orWhereNull('isDeleted');
                })
                ->get()
                ->each(function (OrderItem $item) {
                    $item->kot_printed_qty = max(0, (int) $item->quantity);
                    $item->save();
                });
        }

        if (Schema::hasColumn('orders', 'kot_pending_cancels')) {
            $order->kot_pending_cancels = [];
            $order->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'KOT marked as sent to kitchen',
            'order_id' => (int) $order->id,
        ]);
    }

    protected function kotPayload(Request $request, $orderId): array
    {
        $branchId = $this->branchIdToUse();
        $order = Order::with(['order_items.product', 'tableNo'])
            ->where('id', $orderId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        $station = $request->get('station');
        $isDelta = filter_var($request->get('delta', false), FILTER_VALIDATE_BOOLEAN)
            || $request->get('delta') === '1'
            || $request->get('delta') === 1;

        $items = $order->order_items->filter(function ($item) use ($station) {
            if ((int) ($item->isDeleted ?? 0) === 1) {
                return false;
            }
            $itemStation = $item->product->kitchen_station ?? 'General';
            return !$station || strcasecmp($itemStation, $station) === 0;
        })->values();

        if ($isDelta) {
            $items = $this->buildDeltaItems($order, $items, $station);
            $kotTitle = 'KOT · NEW / CHANGED';
        } else {
            $kotTitle = 'KOT';
        }

        return compact('order', 'items', 'station', 'isDelta', 'kotTitle');
    }

    /**
     * Petpooja-style: only qty increases (NEW) and decreases / removals (CANCEL).
     */
    protected function buildDeltaItems(Order $order, $activeItems, $station = null)
    {
        $rows = collect();

        foreach ($activeItems as $item) {
            $qty = (int) $item->quantity;
            $printed = (int) ($item->kot_printed_qty ?? 0);
            $diff = $qty - $printed;
            if ($diff === 0) {
                continue;
            }

            $clone = clone $item;
            $clone->quantity = abs($diff);
            $clone->kot_action = $diff > 0 ? 'new' : 'cancel';
            $rows->push($clone);
        }

        $pending = $order->kot_pending_cancels ?? [];
        if (is_array($pending)) {
            foreach ($pending as $cancel) {
                $qty = (int) ($cancel['qty'] ?? $cancel['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $name = $cancel['name'] ?? $cancel['product_name'] ?? 'Item';
                $itemStation = $cancel['station'] ?? 'General';
                if ($station && strcasecmp($itemStation, $station) !== 0) {
                    continue;
                }

                $rows->push((object) [
                    'quantity' => $qty,
                    'kot_action' => 'cancel',
                    'variant_name' => $cancel['variant_name'] ?? null,
                    'addons_text' => $cancel['addons_text'] ?? null,
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

    protected function buildTickets($branchId, $filterStation = null)
    {
        $orders = Order::with(['order_items.product', 'tableNo'])
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->where(function ($q) {
                $q->where('table_status', 1)
                    ->orWhereIn('payment_status', ['pending', 'partial', 'partially_paid']);
            })
            ->orderByDesc('id')
            ->limit(80)
            ->get();

        $rows = collect();
        foreach ($orders as $order) {
            $grouped = [];
            foreach ($order->order_items as $item) {
                if ((int) ($item->isDeleted ?? 0) === 1) {
                    continue;
                }
                $station = trim((string) ($item->product->kitchen_station ?? '')) ?: 'General';
                if ($filterStation && strcasecmp($station, $filterStation) !== 0) {
                    continue;
                }
                $grouped[$station][] = $item;
            }
            foreach ($grouped as $station => $items) {
                $rows->push([
                    'order' => $order,
                    'station' => $station,
                    'items' => $items,
                    'table' => optional($order->tableNo)->table_name ?? ($order->type ?? 'Counter'),
                ]);
            }
        }

        return $rows;
    }
}
