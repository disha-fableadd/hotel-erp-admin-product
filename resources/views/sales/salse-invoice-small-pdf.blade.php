<!DOCTYPE html>
<html>
<head>
    <title>Invoice PDF</title>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
            background: #fff;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            font-weight: bold;
            color: #000;
            margin: 0 auto;
            padding: 2.5mm 1.5mm 4mm 1.5mm;
            width: 95%;
            max-width: 95%;
            line-height: 1.25;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .divider {
            border-top: 1px solid #000;
            margin: 3.5px 0;
            width: 100%;
        }

        .double-divider {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 3.5px 0;
            width: 100%;
        }

        .header-section {
            text-align: center;
            margin-bottom: 3px;
        }

        .restaurant-icon {
            width: 44px;
            height: 44px;
            display: inline-block;
            margin: 0 auto 2px auto;
        }

        .restaurant-title {
            font-size: 13px;
            font-weight: bold;
            line-height: 1.2;
            margin: 0;
        }

        .restaurant-tagline {
            font-size: 8.5px;
            font-weight: bold;
            line-height: 1.2;
            margin: 1px 0 2px 0;
        }

        .shop-name {
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.25;
            margin: 2px 0 0 0;
        }

        .shop-gst {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 1px 0 0 0;
            line-height: 1.2;
        }

        .shop-address {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 1px 0 0 0;
            line-height: 1.25;
        }

        .shop-phone {
            font-size: 8px;
            font-weight: bold;
            margin: 1px 0 0 0;
            line-height: 1.2;
        }

        .customer-row {
            font-size: 8.5px;
            font-weight: bold;
            padding: 1px 0;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td, th {
            vertical-align: top;
            word-wrap: break-word;
            overflow: hidden;
        }

        .meta-table td {
            font-size: 8px;
            font-weight: bold;
            line-height: 1.35;
            padding: 0.5px 0;
        }

        .item-table {
            margin-top: 1px;
            margin-bottom: 1px;
        }

        .item-table th {
            font-size: 8.5px;
            font-weight: bold;
            padding: 2px 0;
            border-bottom: 1px solid #000;
        }

        .item-table td {
            font-size: 8px;
            font-weight: bold;
            padding: 2.5px 0;
            line-height: 1.25;
        }

        .c-no { width: 7%; text-align: left; }
        .c-item { width: 45%; text-align: left; }
        .c-qty { width: 12%; text-align: right; }
        .c-price { width: 18%; text-align: right; }
        .c-amt { width: 18%; text-align: right; }

        .item-name {
            font-weight: bold;
            word-wrap: break-word;
        }

        .item-extra {
            font-size: 7px;
            font-weight: normal;
            color: #333;
        }

        .totals-table td {
            font-size: 8.5px;
            font-weight: bold;
            padding: 1px 0;
            line-height: 1.25;
        }

        .t-left { width: 42%; text-align: left; }
        .t-mid { width: 33%; text-align: right; }
        .t-right { width: 25%; text-align: right; }

        .grand-total-table td {
            font-size: 11px;
            font-weight: bold;
            padding: 2px 0;
        }

        .pay-summary-table td {
            font-size: 7.5px;
            font-weight: bold;
            padding: 0.5px 0;
        }

        .fssai-row {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            margin-top: 2px;
            line-height: 1.2;
        }

        .qr-section {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 2px;
        }

        .qr-title {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .qr-section img {
            width: 70px;
            height: 70px;
            display: inline-block;
        }

        .footer-thanks {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            margin-top: 4px;
        }
    </style>
</head>
<body>
@php
    $subTotal = 0;
    $totalQty = 0;
    foreach ($orderItems as $item) {
        $subTotal += (float) ($item->total_amount ?? ($item->price * $item->quantity));
        $totalQty += (float) ($item->quantity ?? 1);
    }

    $labourTotal = 0;
    if (isset($labourItems)) {
        foreach ($labourItems as $labour) {
            $labourTotal += ((float) $labour->price) * ((float) $labour->qty);
            $totalQty += (float) $labour->qty;
        }
    }

    $discountPercent = (float) ($sales->discount ?? 0);
    $storedDiscount = (float) ($sales->discount_amount ?? 0);
    $totalDiscount = $storedDiscount > 0 ? $storedDiscount : (($discountPercent / 100) * $subTotal);

    $totalGst = 0;
    if (!empty($taxDetails1)) {
        foreach ($taxDetails1 as $t) {
            $totalGst += (float) ($t['amount'] ?? 0);
        }
    } else {
        foreach ($orderItems as $item) {
            $totalGst += (float) ($item->product_gst_total ?? 0);
        }
    }

    $shippingCharge = (float) ($sales->shipping ?? 0);
    $afterDiscount = max(0, $subTotal - $totalDiscount);
    $calcGrandTotal = $afterDiscount + $totalGst + $labourTotal + $shippingCharge;
    $grandTotal = isset($sales->total_amount) ? (float) $sales->total_amount : $calcGrandTotal;
    $paidAmount = (float) ($paidAmount ?? 0);
    $pendingAmount = max(0, (float) ($pendingAmount ?? ($grandTotal - $paidAmount)));
    $totalQtyFormatted = fmod($totalQty, 1.0) == 0.0 ? number_format($totalQty, 0) : number_format($totalQty, 2);

    $currencySymbol = !empty($setting->currency_symbol) ? $setting->currency_symbol : '₹';

    $payMode = !empty($sales->payment_method) ? ucfirst($sales->payment_method) : '';
    if ($payMode === '' && isset($allPayments) && count($allPayments) > 0) {
        $mList = [];
        foreach ($allPayments as $p) {
            $m = trim((string) ($p->payment_type ?? $p->payment_mode ?? ''));
            if ($m !== '' && !in_array($m, $mList, true)) { $mList[] = ucfirst($m); }
        }
        $payMode = implode(', ', $mList);
    }
    if ($payMode === '') {
        $payMode = ($paidAmount >= $grandTotal && $grandTotal > 0) ? 'Fully Paid' : ($paidAmount > 0 ? 'Partially Paid' : 'Due');
    }

    $logoBase64 = null;
    if (!empty($setting->logo) && function_exists('resolve_local_image_base64')) {
        $logoBase64 = resolve_local_image_base64($setting->logo);
    }

    // Default restaurant fork & knife circular emblem if no logo uploaded
    $forkKnifeSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 100 100"><circle cx="50" cy="50" r="48" fill="#111111" /><path d="M 33 24 v 14 c 0 4.5 2.5 8 6.5 8.5 v 28 c 0 1.5 1 2.5 2.5 2.5 s 2.5 -1 2.5 -2.5 v -28 c 4 -0.5 6.5 -4 6.5 -8.5 V 24 h -2.5 v 12 h -3.5 V 24 h -2.5 v 12 h -3.5 V 24 Z" fill="#ffffff" /><path d="M 57 24 c 0 0 8 1 9 10 c 1 9 -2 16 -9 17 v 24 c 0 1.5 1 2.5 2.5 2.5 s 2.5 -1 2.5 -2.5 V 24 Z" fill="#ffffff" /></svg>';
    $forkKnifeIconDataUri = 'data:image/svg+xml;base64,' . base64_encode($forkKnifeSvg);

    $qrBase64 = null;
    $rawQr = $sales->custom_qr_code ?? ($setting->qr_code ?? null);
    if (!empty($rawQr)) {
        if (function_exists('resolve_local_image_base64')) {
            $qrBase64 = resolve_local_image_base64($rawQr);
        }
        if (!$qrBase64) {
            $cleanQr = ltrim(preg_replace('#^https?://[^/]+/#', '', preg_replace('#^storage/#', '', preg_replace('#^public/#', '', trim($rawQr)))), '/');
            $cands = [
                storage_path('app/public/' . $cleanQr),
                storage_path('app/public/qr_codes/' . basename($cleanQr)),
                public_path('storage/' . $cleanQr),
                public_path($cleanQr),
                base_path('storage/app/public/' . $cleanQr),
            ];
            foreach ($cands as $c) {
                if (file_exists($c) && is_file($c)) {
                    $mime = function_exists('mime_content_type') ? (@mime_content_type($c) ?: 'image/png') : 'image/png';
                    $qrBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($c));
                    break;
                }
            }
        }
    }

    $thanks = trim((string) ($setting->footer_text ?? ''));
    if ($thanks === '') {
        $thanks = 'Thanks for business with us!';
    }

    $custName = !empty($customer['name']) ? $customer['name'] : '';
    if (in_array(strtolower($custName), ['walk-in', 'walk-in-customer', 'default-customer', 'walk-in customer'])) {
        $custName = '';
    }

    $cashierName = $sales->creator->name ?? (Auth::user()->name ?? 'Biller');

    // --- Order type label (Petpooja-style) ---
    // Normalise the stored order_type value
    // $rawOrderType = strtolower(trim((string) ($sales->order_type ?? '')));

    // // Resolve table number (strip any "Table #" prefix so we get a plain number)
    // $displayTable = $tableName ?? (!empty($tableNo) ? $tableNo : (!empty($sales->table_no) ? $sales->table_no : ''));
    // $cleanTable   = $displayTable !== '' && $displayTable !== null
    //                 ? preg_replace('/^Table\s*#?\s*/i', '', (string) $displayTable)
    //                 : '';

    // // Build the top-right meta label exactly like Petpooja:
    // //   "Dine In: 4"  /  "Take Away"  /  "Delivery"
    // // Values stored in DB: 'dinein', 'Delivery', 'Self Pickup', 'self_pickup', 'dine_in', etc.
    // if (in_array($rawOrderType, ['dine_in', 'dine in', 'dinein', 'dine-in', ''])) {
    //     // Dine-In — always show table number when available
    //     $dineInText = 'Dine In' . ($cleanTable !== '' ? ': ' . $cleanTable : '');
    // } elseif (in_array($rawOrderType, ['take_away', 'takeaway', 'take away', 'take-away', 'self pickup', 'self_pickup', 'pickup'])) {
    //     $dineInText = 'Take Away';
    // } elseif (in_array($rawOrderType, ['delivery', 'home delivery', 'home_delivery'])) {
    //     $dineInText = 'Delivery';
    // } else {
    //     // Fallback: humanise whatever value is stored
    //     $dineInText = ucwords(str_replace(['_', '-'], ' ', $rawOrderType));
    //     if ($cleanTable !== '') {
    //         $dineInText .= ': ' . $cleanTable;
    //     }
    // }
        // --- Order type label ---
    $rawOrderType = strtolower(trim((string) ($sales->order_type ?? '')));
    $rawOrderSourceType = strtolower(trim((string) ($sales->type ?? '')));

    // Resolve table number from every place it could be stored
    $displayTable = !empty($tableName)
        ? $tableName
        : (!empty($tableNo) ? $tableNo : ($sales->table_no ?? ''));

    $cleanTable = $displayTable !== '' && $displayTable !== null
        ? preg_replace('/^Table\s*#?\s*/i', '', (string) $displayTable)
        : '';

    // A table is attached, so it is always Dine In (takes priority over order_type)
    if (
        $cleanTable !== ''
        || in_array($rawOrderSourceType, ['dine_in', 'dine in', 'dinein', 'dine-in'], true)
        || in_array($rawOrderType, ['dine_in', 'dine in', 'dinein', 'dine-in'], true)
    ) {
        $dineInText = 'Dine In' . ($cleanTable !== '' ? ': ' . $cleanTable : '');
    } elseif (
        in_array($rawOrderSourceType, ['take_away', 'takeaway', 'take away', 'take-away'], true)
        || in_array($rawOrderType, ['take_away', 'takeaway', 'take away', 'take-away', 'self pickup', 'self_pickup', 'pickup'], true)
    ) {
        $dineInText = 'Take Away';
    } elseif (
        in_array($rawOrderSourceType, ['delivery', 'home delivery', 'home_delivery'], true)
        || in_array($rawOrderType, ['delivery', 'home delivery', 'home_delivery'], true)
    ) {
        $dineInText = 'Delivery';
    } else {
        $dineInText = ucwords(str_replace(['_', '-'], ' ', $rawOrderType));
    }

    $fssaiNo = $setting->cin_no ?? ($setting->fssai_no ?? '');
@endphp

    <div class="header-section">
        @if (!empty($logoBase64))
            <div style="margin-bottom: 2px;">
                <img src="{{ $logoBase64 }}" style="max-height: 42px; max-width: 140px; display: inline-block;" alt="Logo">
            </div>
        @else
            <div>
                <img src="{{ $forkKnifeIconDataUri }}" class="restaurant-icon" alt="Restaurant">
            </div>
            <div class="restaurant-title">Restaurant</div>
            <div class="restaurant-tagline">Food &amp; Drinks</div>
        @endif

        <div class="shop-name">{{ $setting->name ?? 'ABC CAFE & RESTRO' }}</div>
        @if (!empty($setting->gst_num))
            <div class="shop-gst">{{ $setting->gst_num }}</div>
        @endif
        @if (!empty($setting->address))
            <div class="shop-address">{!! nl2br(e($setting->address)) !!}</div>
        @endif
        @if (!empty($setting->phone))
            <div class="shop-phone">Ph: {{ $setting->phone }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <div class="customer-row">
        Name: {{ $custName !== '' ? $custName : '' }}
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td style="width: 55%; text-align: left;">Date: {{ date('d/m/y', strtotime($sales->created_at)) }}</td>
            <td style="width: 45%; text-align: right; font-weight: bold;">{{ $dineInText }}</td>
        </tr>
        <tr>
            <td style="width: 55%; text-align: left;">Cashier: {{ $cashierName }}</td>
            <td style="width: 45%; text-align: right;">Bill No.: {{ $sales->order_number ?? $view_id }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="item-table">
        <thead>
            <tr>
                <th class="c-no">No.</th>
                <th class="c-item">Item</th>
                <th class="c-qty">Qty.</th>
                <th class="c-price">Price</th>
                <th class="c-amt">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orderItems as $index => $item)
                @php
                    $qty = (float) $item->quantity;
                    $qtyLabel = fmod($qty, 1.0) == 0.0 ? number_format($qty, 0) : number_format($qty, 2);
                    $pName = $item->product->name ?? 'Product';
                    $price = (float) $item->price;
                    $lineTotal = (float) ($item->total_amount ?? ($price * $qty));
                    $unit = $item->quantityType->name ?? ($item->product->unit->short_name ?? ($item->product->unit ?? ''));
                @endphp
                <tr>
                    <td class="c-no">{{ $index + 1 }}</td>
                    <td class="c-item">
                        <div class="item-name">{{ $pName }}</div>
                        @if (!empty($unit) && !in_array(strtolower($unit), ['pcs', 'piece', 'pieces']))
                            <div class="item-extra">({{ $unit }})</div>
                        @endif
                    </td>
                    <td class="c-qty">{{ $qtyLabel }}</td>
                    <td class="c-price">{{ number_format($price, 2) }}</td>
                    <td class="c-amt">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
            @if (isset($labourItems) && count($labourItems) > 0)
                @foreach ($labourItems as $lIndex => $labour)
                    @php
                        $lQty = (float) $labour->qty;
                        $lQtyLabel = fmod($lQty, 1.0) == 0.0 ? number_format($lQty, 0) : number_format($lQty, 2);
                        $lPrice = (float) $labour->price;
                        $lAmt = $lPrice * $lQty;
                    @endphp
                    <tr>
                        <td class="c-no">{{ count($orderItems) + $lIndex + 1 }}</td>
                        <td class="c-item">
                            <div class="item-name">{{ $labour->labourItem->item_name ?? 'Labour' }}</div>
                        </td>
                        <td class="c-qty">{{ $lQtyLabel }}</td>
                        <td class="c-price">{{ number_format($lPrice, 2) }}</td>
                        <td class="c-amt">{{ number_format($lAmt, 2) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="totals-table">
        <tr>
            <td class="t-left">Total Qty: {{ $totalQtyFormatted }}</td>
            <td class="t-mid">Sub Total</td>
            <td class="t-right">{{ number_format($subTotal + $labourTotal, 2) }}</td>
        </tr>
        @if ($totalDiscount > 0)
            <tr>
                <td class="t-left"></td>
                <td class="t-mid">Discount @if($discountPercent > 0)({{ rtrim(rtrim(number_format($discountPercent, 2), '0'), '.') }}%)@endif</td>
                <td class="t-right">-{{ number_format($totalDiscount, 2) }}</td>
            </tr>
        @endif
        @if (!empty($taxDetails1) && count($taxDetails1) > 0)
            @foreach ($taxDetails1 as $tax)
                @php
                    $tRate = !empty($tax['rate']) ? (rtrim(rtrim(number_format((float)$tax['rate'], 2), '0'), '.') . '%') : '';
                @endphp
                <tr>
                    <td class="t-left"></td>
                    <td class="t-mid">{{ $tax['name'] ?? 'GST' }} &nbsp; {{ $tRate }}</td>
                    <td class="t-right">{{ number_format((float)($tax['amount'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        @elseif ($totalGst > 0)
            <tr>
                <td class="t-left"></td>
                <td class="t-mid">GST</td>
                <td class="t-right">{{ number_format($totalGst, 2) }}</td>
            </tr>
        @endif
        @if ($shippingCharge > 0)
            <tr>
                <td class="t-left"></td>
                <td class="t-mid">Shipping</td>
                <td class="t-right">{{ number_format($shippingCharge, 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    <table class="grand-total-table">
        <tr>
            <td style="width: 50%; text-align: right;">Grand Total</td>
            <td style="width: 50%; text-align: right;">{{ $currencySymbol }} {{ number_format($grandTotal, 2) }}</td>
        </tr>
    </table>

    <div class="double-divider"></div>

    @if ($paidAmount > 0 || $pendingAmount > 0 || !empty($payMode))
        <table class="pay-summary-table">
            @if ($paidAmount > 0)
                <tr>
                    <td style="text-align: left;">Paid:</td>
                    <td style="text-align: right;">{{ $currencySymbol }} {{ number_format($paidAmount, 2) }}</td>
                </tr>
            @endif
            @if ($pendingAmount > 0)
                <tr>
                    <td style="text-align: left;">Balance Due:</td>
                    <td style="text-align: right;">{{ $currencySymbol }} {{ number_format($pendingAmount, 2) }}</td>
                </tr>
            @endif
            @if (!empty($payMode))
                <tr>
                    <td style="text-align: left;">Payment Mode:</td>
                    <td style="text-align: right;">{{ $payMode }}</td>
                </tr>
            @endif
        </table>
        <div class="divider"></div>
    @endif

    @if (!empty($fssaiNo))
        <div class="fssai-row">
            FSSAI Lic No. {{ $fssaiNo }}
        </div>
    @endif

    @if (!empty($qrBase64))
        <div class="qr-section">
            <div class="qr-title">Scan QR to Pay Bill:</div>
            <img src="{{ $qrBase64 }}" alt="QR Code">
        </div>
    @endif

    <div class="footer-thanks">
        {{ $thanks }}
    </div>
</body>
</html>