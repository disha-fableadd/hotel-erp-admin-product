<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KOT #{{ $order->order_number ?? $order->id }}</title>
    <style>
        @page { margin: 6mm 4mm; }
        * {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif !important;
        }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif !important;
            color: #111;
            font-size: 11px;
            font-weight: normal;
            margin: 0;
        }
        .kot-title {
            margin: 0 0 4px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif !important;
        }
        .meta {
            font-size: 10px;
            margin-bottom: 8px;
            text-align: center;
            line-height: 1.35;
            font-weight: normal;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif !important;
        }
        .line { border-top: 1px dashed #333; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            text-align: left;
            padding: 4px 0;
            border-bottom: 1px dashed #999;
            font-size: 11px;
            font-weight: normal;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif !important;
        }
        th { font-size: 10px; font-weight: bold; }
        .qty { text-align: right; font-weight: bold; width: 28%; }
        .foot {
            margin-top: 8px;
            text-align: center;
            font-size: 9px;
            font-weight: normal;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif !important;
        }
        .badge-new { font-size: 9px; font-weight: bold; }
        .badge-cancel { font-size: 9px; font-weight: bold; text-decoration: line-through; }
        strong { font-weight: bold; font-family: DejaVu Sans, Arial, Helvetica, sans-serif !important; }
    </style>
</head>
<body>
    @php
        $titleText = $kotTitle ?? ('KOT' . (!empty($station) ? ' - '.$station : ''));
        $titleText = str_replace(['·', '•', '–', '—'], '-', (string) $titleText);
    @endphp
    <div class="kot-title">{{ $titleText }}</div>
    <div class="meta">
        Order #{{ $order->order_number ?? $order->id }}<br>
        {{ optional($order->tableNo)->table_name ? 'Table: '.optional($order->tableNo)->table_name : ($order->type ?? 'Counter') }}
        @if(!empty($order->order_type)) - {{ $order->order_type }}@endif
        <br>
        {{ now('Asia/Kolkata')->format('d M Y, h:i A') }}
        @if(!empty($isDelta))
            <br><strong>Only new / changed items</strong>
        @endif
        @if(!empty($order->kot_remarks))
            <br><strong>Remarks:</strong> {{ $order->kot_remarks }}
        @endif
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
                <tr>
                    <td class="{{ $isCancel ? 'badge-cancel' : '' }}">
                        @if(!empty($isDelta))
                            <span class="badge-new">{{ $isCancel ? '[CANCEL]' : '[NEW]' }}</span>
                        @endif
                        {{ $name }}
                        @if(!empty($vName))
                            <br><span style="font-size:9px;">{{ $vName }}</span>
                        @endif
                        @if(!empty($aLines))
                            <br><span style="font-size:9px;">+ {{ is_array($aLines) ? implode(', ', $aLines) : $aLines }}</span>
                        @endif
                    </td>
                    <td class="qty {{ $isCancel ? 'badge-cancel' : '' }}">
                        {{ $isCancel ? '-' : '' }}{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="2" style="text-align:center;">No new / changed items for kitchen</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="line"></div>
    <div class="foot">Kitchen Order Ticket{{ !empty($isDelta) ? ' - Delta' : '' }}</div>
</body>
</html>
