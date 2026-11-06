<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KOT #{{ $order->order_number ?? $order->id }}</title>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif !important;
        }
        body {
            font-family: Arial, Helvetica, sans-serif !important;
            padding: 12px;
            color: #111;
            font-size: 13px;
            font-weight: normal;
        }
        .kot-title {
            margin: 0 0 4px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif !important;
        }
        .meta {
            font-size: 12px;
            margin-bottom: 10px;
            text-align: center;
            line-height: 1.35;
            font-weight: normal;
            font-family: Arial, Helvetica, sans-serif !important;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            text-align: left;
            padding: 6px 0;
            border-bottom: 1px dashed #ccc;
            font-size: 13px;
            font-weight: normal;
            font-family: Arial, Helvetica, sans-serif !important;
        }
        th { font-weight: bold; }
        .qty { text-align: right; font-weight: bold; }
        .cancel { text-decoration: line-through; color: #c00; }
        strong { font-weight: bold; font-family: Arial, Helvetica, sans-serif !important; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    @php
        $titleText = $kotTitle ?? ('KOT' . (!empty($station) ? ' - '.$station : ''));
        $titleText = str_replace(['·', '•', '–', '—'], '-', (string) $titleText);
    @endphp
    <button class="no-print" onclick="window.print()">Print</button>
    <div class="kot-title">{{ $titleText }}</div>
    <div class="meta">
        Order #{{ $order->order_number ?? $order->id }}<br>
        {{ optional($order->tableNo)->table_name ? 'Table: '.optional($order->tableNo)->table_name : ($order->type ?? 'Counter') }}
        @if(!empty($order->order_type)) - {{ $order->order_type }}@endif
        <br>
        {{ optional($order->created_at)->format('d M Y, h:i A') ?: now('Asia/Kolkata')->format('d M Y, h:i A') }}
        @if(!empty($isDelta))
            <br><strong>Only new / changed items</strong>
        @endif
        @if(!empty($order->kot_remarks))
            <br><strong>Remarks:</strong> {{ $order->kot_remarks }}
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
                    $vName = $item->variant_name
                        ?? (method_exists($item, 'variantLabel') ? $item->variantLabel() : (optional($item->variant ?? null)->name ?? null));
                    $aLines = !empty($item->addons_text)
                        ? [ $item->addons_text ]
                        : (method_exists($item, 'addonLines') ? $item->addonLines() : []);
                @endphp
                <tr class="{{ $isCancel ? 'cancel' : '' }}">
                    <td>
                        @if(!empty($isDelta))
                            <strong>{{ $isCancel ? '[CANCEL]' : '[NEW]' }}</strong>
                        @endif
                        {{ $name }}
                        @if(!empty($vName))
                            <br><small>{{ $vName }}</small>
                        @endif
                        @if(!empty($aLines))
                            <br><small>+ {{ is_array($aLines) ? implode(', ', $aLines) : $aLines }}</small>
                        @endif
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
