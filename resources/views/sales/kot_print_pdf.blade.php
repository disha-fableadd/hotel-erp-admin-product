<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KOT #{{ $order->order_number ?? $order->id }}</title>
    <style>
        @page { margin: 6mm 4mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111; font-size: 11px; margin: 0; }
        h2 { margin: 0 0 4px; font-size: 15px; text-align: center; }
        .meta { font-size: 10px; margin-bottom: 8px; text-align: center; line-height: 1.35; }
        .line { border-top: 1px dashed #333; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 4px 0; border-bottom: 1px dashed #999; font-size: 11px; }
        th { font-size: 10px; }
        .qty { text-align: right; font-weight: 700; width: 28%; }
        .foot { margin-top: 8px; text-align: center; font-size: 9px; }
    </style>
</head>
<body>
    <h2>KOT{{ !empty($station) ? ' · '.$station : '' }}</h2>
    <div class="meta">
        Order #{{ $order->order_number ?? $order->id }}<br>
        {{ optional($order->tableNo)->table_name ? 'Table: '.optional($order->tableNo)->table_name : ($order->type ?? 'Counter') }}
        @if(!empty($order->order_type)) · {{ $order->order_type }}@endif
        <br>
        {{ optional($order->created_at)->format('d M Y, h:i A') }}
    </div>
    <div class="line"></div>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="qty">Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>
                        {{ $item->product->name ?? 'Item' }}
                        @if(!empty($item->variant_name))
                            <br><span style="font-size:9px;">{{ $item->variant_name }}</span>
                        @endif
                        @if(!empty($item->addons_text))
                            <br><span style="font-size:9px;">{{ $item->addons_text }}</span>
                        @endif
                    </td>
                    <td class="qty">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="2">No items</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="line"></div>
    <div class="foot">Kitchen Order Ticket</div>
</body>
</html>
