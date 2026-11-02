<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $branchId = $this->branchIdToUse();
        $order = Order::with(['order_items.product', 'tableNo'])
            ->where('id', $orderId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        $station = $request->get('station');
        $items = $order->order_items->filter(function ($item) use ($station) {
            if ((int) ($item->isDeleted ?? 0) === 1) {
                return false;
            }
            $itemStation = $item->product->kitchen_station ?? 'General';
            return !$station || strcasecmp($itemStation, $station) === 0;
        });

        return view('sales.kot_print', compact('order', 'items', 'station'));
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
