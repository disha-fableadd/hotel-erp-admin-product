<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KOT #{{ $order->order_number ?? $order->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 12px; color: #111; }
        h2 { margin: 0 0 4px; font-size: 18px; }
        .meta { font-size: 12px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 6px 0; border-bottom: 1px dashed #ccc; font-size: 13px; }
        .qty { text-align: right; font-weight: 700; }
        .cancel { text-decoration: line-through; color: #c00; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <button class="no-print" onclick="window.print()">Print</button>
    <h2>{{ $kotTitle ?? ('KOT ' . ($station ? '· '.$station : '')) }}</h2>
    <div class="meta">
        Order #{{ $order->order_number ?? $order->id }}<br>
        {{ optional($order->created_at)->format('d M Y, h:i A') }}
        @if(!empty($isDelta))
            <br><strong>Only new / changed items</strong>
        @endif
    </div>
    <table>
        <thead>
            <tr><th>Item</th><th class="qty">Qty</th></tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php
                    $action = $item->kot_action ?? 'new';
                    $isCancel = $action === 'cancel';
                    $name = $item->product->name ?? ($item->product_name ?? 'Item');
                @endphp
                <tr class="{{ $isCancel ? 'cancel' : '' }}">
                    <td>
                        @if(!empty($isDelta))
                            <strong>{{ $isCancel ? '[CANCEL]' : '[NEW]' }}</strong>
                        @endif
                        {{ $name }}
                    </td>
                    <td class="qty">{{ $isCancel ? '-' : '' }}{{ $item->quantity }}</td>
                </tr>
            @empty
                <tr><td colspan="2">No new / changed items for kitchen</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
