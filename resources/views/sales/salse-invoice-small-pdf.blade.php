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
            font-size: 8px;
            font-weight: bold;
            color: #000;
            margin: 0 auto;
            padding: 2mm 0 4mm 0;
            width: 94%;
            max-width: 94%;
            line-height: 1.25;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .shop-name {
            margin: 0;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .shop-phone {
            margin: 1px 0 0 0;
            font-size: 7.5px;
            font-weight: normal;
            line-height: 1.3;
        }

        .inv-title {
            margin: 4px 0;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2px 0;
            text-align: center;
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

        .item-table {
            margin-top: 4px;
        }

        .item-table th {
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 2px 1px;
        }

        .item-table td {
            font-size: 8px;
            padding: 1px 1px;
        }

        .name-row td {
            padding-top: 3px;
            padding-bottom: 0;
        }

        .c-no { width: 7%; }
        .c-name { width: 45%; }
        .c-sub { font-weight: normal; font-size: 7px; }
        .c-price { width: 24%; text-align: right; }
        .c-amt { width: 24%; text-align: right; }

        .item-name {
            font-weight: bold;
            word-wrap: break-word;
        }

        .item-extra {
            font-size: 7px;
            font-weight: normal;
            color: #333;
        }

        .sum-table {
            margin-top: 4px;
            border-top: 1px solid #000;
        }

        .sum-table td {
            font-size: 8.5px;
            padding: 1.5px 0;
            font-weight: bold;
        }

        .inv-qr {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .inv-qr img {
            width: 70px;
            height: 70px;
            margin-top: 2px;
            display: inline-block;
        }

        .terms {
            margin-top: 6px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
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
        $payMode = ($paidAmount >= $grandTotal && $grandTotal > 0) ? 'Fully' : ($paidAmount > 0 ? 'Partially' : 'Due');
    }

    $logoBase64 = null;
    if (!empty($setting->logo) && function_exists('resolve_local_image_base64')) {
        $logoBase64 = resolve_local_image_base64($setting->logo);
    }

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
    $custPhone = !empty($customer['phone']) ? $customer['phone'] : '';
@endphp

    <div class="text-center">
        @if (!empty($logoBase64))
            <div style="margin-bottom: 2px;">
                <img src="{{ $logoBase64 }}" style="max-height: 28px; width: auto; max-width: 80%; display: inline-block;" alt="Logo">
            </div>
        @endif
        <h3 class="shop-name">{{ $setting->name ?? '' }}</h3>
        <p class="shop-phone">
            @if (!empty($setting->address)) {{ $setting->address }}<br> @endif
            @if (!empty($setting->phone)) Ph: {{ $setting->phone }} @endif
            @if (!empty($setting->gst_num)) | GST: {{ $setting->gst_num }} @endif
        </p>
        <div class="inv-title">TAX INVOICE</div>
    </div>

    <table>
        <tr>
            <td class="text-left" style="width: 50%;">
                Bill No: {{ $sales->order_number ?? $view_id }}<br>
                @php
                    $displayTable = $tableName ?? (!empty($tableNo) ? ('Table #' . $tableNo) : (!empty($sales->table_no) ? ('Table #' . $sales->table_no) : ''));
                @endphp
                @if (!empty($displayTable))
                    Table: {{ $displayTable }}<br>
                @endif
                @if ($custName !== '')
                    Customer: {{ $custName }}<br>
                @endif
                @if ($custPhone !== '')
                    Phone: {{ $custPhone }}<br>
                @endif
            </td>
            <td class="text-right" style="width: 50%;">
                Date: {{ date('d/m/Y', strtotime($sales->created_at)) }}<br>
                Time: {{ date('h:i A', strtotime($sales->created_at)) }}
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th class="c-no">#</th>
                <th class="c-name">Item Name<br><span class="c-sub">Quantity</span></th>
                <th class="c-price">Price</th>
                <th class="c-amt">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orderItems as $index => $item)
                @php
                    $qty = (float) $item->quantity;
                    $qtyLabel = fmod($qty, 1.0) == 0.0 ? number_format($qty, 0) : number_format($qty, 2);
                    $unit = $item->quantityType->name ?? ($item->product->unit->short_name ?? ($item->product->unit ?? 'Pcs'));
                    $pName = $item->product->name ?? 'Product';
                    $price = (float) $item->price;
                    $lineTotal = (float) ($item->total_amount ?? ($price * $qty));
                    $sku = $item->product->SKU ?? ($item->product->barcode ?? '');
                @endphp
                <tr class="name-row">
                    <td class="c-no">{{ $index + 1 }}</td>
                    <td class="item-name" colspan="3">
                        {{ $pName }}
                        {{-- @if (!empty($sku))
                            <br><span class="item-extra">{{ $sku }}</span>
                        @endif --}}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="c-name">{{ $qtyLabel }} {{ $unit }}</td>
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
                    <tr class="name-row">
                        <td class="c-no">{{ count($orderItems) + $lIndex + 1 }}</td>
                        <td class="item-name" colspan="3">{{ $labour->labourItem->item_name ?? 'Labour' }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="c-name">{{ $lQtyLabel }}</td>
                        <td class="c-price">{{ number_format($lPrice, 2) }}</td>
                        <td class="c-amt">{{ number_format($lAmt, 2) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <table class="sum-table">
        <tr>
            <td class="text-left">Total: {{ $totalQtyFormatted }}</td>
            <td class="text-right">{{ number_format($subTotal + $labourTotal, 2) }}</td>
        </tr>
        @if ($totalDiscount > 0)
            <tr>
                <td class="text-left">Discount @if($discountPercent > 0)({{ rtrim(rtrim(number_format($discountPercent, 2), '0'), '.') }}%)@endif</td>
                <td class="text-right">-{{ number_format($totalDiscount, 2) }}</td>
            </tr>
        @endif
        @if ($totalGst > 0)
            <tr>
                <td class="text-left">Tax (GST)</td>
                <td class="text-right">{{ number_format($totalGst, 2) }}</td>
            </tr>
        @endif
        @if ($shippingCharge > 0)
            <tr>
                <td class="text-left">Shipping</td>
                <td class="text-right">{{ number_format($shippingCharge, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td class="text-left">Total</td>
            <td class="text-right">: {{ number_format($grandTotal, 2) }}</td>
        </tr>
        <tr>
            <td class="text-left">Received</td>
            <td class="text-right">: {{ number_format($paidAmount, 2) }}</td>
        </tr>
        @if ($pendingAmount > 0)
            <tr>
                <td class="text-left">Balance Due</td>
                <td class="text-right">: {{ number_format($pendingAmount, 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="text-center bold" style="margin: 5px 0 3px 0; font-size: 8px;">
        Payment Mode: {{ $payMode }} : Rs {{ number_format($paidAmount, 2) }}
    </div>

    @if (!empty($qrBase64))
        <div class="inv-qr">
            <div style="margin-bottom: 2px;">Scan QR to Pay Bill:</div>
            <img src="{{ $qrBase64 }}" alt="UPI QR">
        </div>
    @endif

    <div class="terms">
        <div>{{ $thanks }}</div>
    </div>
</body>
</html>
