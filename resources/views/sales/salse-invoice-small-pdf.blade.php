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
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            background: #fff;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'DejaVu Sans', monospace;
            font-size: 8px;
            line-height: 1.25;
            color: #000;
        }

        .invoice-wrapper {
            width: 100%;
            padding: 4mm;
            box-sizing: border-box;
        }

        .payment-mode-line {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            margin: 6px 0 4px 0;
        }

        .inv-qr {
            margin: 6px 0 4px 0;
            text-align: center;
        }

        .inv-qr img {
            width: 75px;
            height: 75px;
            display: inline-block;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .company-name {
            font-size: 13px;
            margin-bottom: 2px;
            display: block;
        }

        .company-info {
            font-size: 8px;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .invoice-title {
            font-size: 11px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            margin: 8px 0;
            padding: 4px 0;
        }

        .info-table {
            margin-bottom: 8px;
        }

        .info-table td {
            font-size: 8px;
            padding: 1px 0;
            vertical-align: top;
        }

        .info-table td:first-child { width: 30%; }
        .info-table td:last-child { width: 70%; }

        .item-table {
            margin-bottom: 5px;
        }

        .item-table th {
            font-size: 8px;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            text-transform: uppercase;
        }

        .item-table td {
            padding: 5px 0;
            vertical-align: top;
            font-size: 8px;
            word-wrap: break-word;
        }

        .item-table th:first-child, .item-table td:first-child { width: 52%; }
        .item-table th:nth-child(2), .item-table td:nth-child(2) { width: 16%; text-align: center; }
        .item-table th:last-child, .item-table td:last-child { width: 32%; text-align: right; }

        .totals-table td {
            padding: 2px 0;
            font-size: 9px;
        }
        .totals-table td:first-child { width: 62%; }
        .totals-table td:last-child { width: 38%; }

        .grand-total table td:first-child { width: 55%; }
        .grand-total table td:last-child { width: 45%; }

        .grand-total {
            border-top: 1px double #000;
            border-bottom: 1px double #000;
            font-size: 11px;
            margin: 6px 0;
            padding: 5px 0;
        }

        .footer {
            margin-top: 15px;
            font-size: 8px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
    </style>
</head>

<body>
    <div class="invoice-wrapper">
        <div class="text-center">
            <span class="company-name bold uppercase">{{ $setting->name ?? '' }}</span>
            <div class="company-info">
                {{ $setting->address ?? '' }}<br>
                Ph: {{ $setting->phone ?? '' }} | GST: {{ $setting->gst_num ?? '' }}
            </div>
        </div>

        <div class="invoice-title text-center bold uppercase">
            Tax Invoice
        </div>

        <table class="info-table">
            <tr>
                <td>Bill No:</td>
                <td class="bold text-right">{{ $sales->order_number ?? $view_id }}</td>
            </tr>
            <tr>
                <td>Date:</td>
                <td class="text-right">{{ date('d-m-Y H:i', strtotime($sales->created_at)) }}</td>
            </tr>
            @if (!empty($customer['name']))
                <tr>
                    <td>Customer:</td>
                    <td class="text-right bold">{{ $customer['name'] }}</td>
                </tr>
            @endif
            @if (!empty($customer['phone']))
                <tr>
                    <td>Phone:</td>
                    <td class="text-right">{{ $customer['phone'] }}</td>
                </tr>
            @endif
            @if (!empty($customer['email']))
                <tr>
                    <td>Email:</td>
                    <td class="text-right">{{ $customer['email'] }}</td>
                </tr>
            @endif
        </table>

        @php
            $subTotal = $orderItems->sum(function ($item) {
                return (float) ($item->total_amount ?? ($item->price * $item->quantity));
            });
            $discountPercent = (float) ($sales->discount ?? 0);
            $storedDiscount = (float) ($sales->discount_amount ?? 0);
            $totalDiscount = $storedDiscount > 0
                ? $storedDiscount
                : (($discountPercent / 100) * $subTotal);
            $totalGst = 0;
            $labourTotal = 0;

            if (!empty($taxDetails1)) {
                foreach ($taxDetails1 as $taxDetail) {
                    $totalGst += (float) ($taxDetail['amount'] ?? 0);
                }
            } else {
                foreach ($orderItems as $item) {
                    $totalGst += (float) ($item->product_gst_total ?? 0);
                }
            }

            if (isset($labourItems)) {
                foreach ($labourItems as $labour) {
                    $labourTotal += $labour->price * $labour->qty;
                }
            }

            $shippingCharge = (float) ($sales->shipping ?? 0);
            
            $baseSubtotal = $subTotal;
            $afterDiscount = max(0, $baseSubtotal - $totalDiscount);
            $calculatedGrandTotal = $afterDiscount + $totalGst + $labourTotal + $shippingCharge;
            $grandTotal = isset($sales->total_amount) ? (float) $sales->total_amount : $calculatedGrandTotal;
            $paidAmount = (float) ($paidAmount ?? 0);
            $pendingAmount = max(0, (float) ($pendingAmount ?? ($grandTotal - $paidAmount)));
            $currencySymbol = $setting->currency_symbol ?? '₹';
            $currencyPosition = $setting->currency_position ?? 'left';
            $formatMoney = function ($amount) use ($currencySymbol, $currencyPosition) {
                $formatted = number_format((float) $amount, 2);
                return $currencyPosition === 'right'
                    ? $formatted . $currencySymbol
                    : $currencySymbol . $formatted;
            };
        @endphp

        <table class="item-table">
            <thead>
                <tr>
                    <th class="text-left">Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderItems as $item)
                    @php $amount = $item->total_amount ?? ($item->price * $item->quantity); @endphp
                    <tr>
                        <td class="text-left">
                            {{ $item->product->name ?? 'Product' }}
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ $formatMoney($amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($labourTotal > 0)
            <div class="divider"></div>
            <table class="item-table">
                <thead>
                    <tr>
                        <th class="text-left">Labour Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($labourItems as $labour)
                        <tr>
                            <td class="text-left">{{ $labour->labourItem->item_name }}</td>
                            <td class="text-center">{{ $labour->qty }}</td>
                            <td class="text-right">{{ $formatMoney($labour->price * $labour->qty) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="divider"></div>

        <table class="totals-table">
            <tr>
                <td class="text-left">Subtotal</td>
                <td class="text-right">{{ $formatMoney($baseSubtotal) }}</td>
            </tr>
            @if ($totalDiscount > 0)
                <tr>
                    <td class="text-left">Discount</td>
                    <td class="text-right">-{{ $formatMoney($totalDiscount) }}</td>
                </tr>
                <tr>
                    <td class="text-left">Price After Discount</td>
                    <td class="text-right">{{ $formatMoney($afterDiscount) }}</td>
                </tr>
            @endif
            @if ($totalGst > 0)
                <tr>
                    <td class="text-left">Tax (GST)</td>
                    <td class="text-right">{{ $formatMoney($totalGst) }}</td>
                </tr>
            @endif
            @if ($labourTotal > 0)
                <tr>
                    <td class="text-left">Labour Charges</td>
                    <td class="text-right">{{ $formatMoney($labourTotal) }}</td>
                </tr>
            @endif
            @if ($shippingCharge > 0)
                <tr>
                    <td class="text-left">Shipping</td>
                    <td class="text-right">{{ $formatMoney($shippingCharge) }}</td>
                </tr>
            @endif
        </table>

        <div class="grand-total bold">
            <table width="100%">
                <tr>
                    <td class="text-left uppercase">Grand Total</td>
                    <td class="text-right">{{ $formatMoney($grandTotal) }}</td>
                </tr>
            </table>
        </div>

        <table class="totals-table">
            @if(isset($allPayments) && count($allPayments) > 0)
                @foreach($allPayments as $index => $payment)
                <tr>
                    <td class="text-left">
                        @if($index === 0 && count($allPayments) > 1)
                            Advance Paid
                        @else
                            Paid Amount
                        @endif
                    </td>
                    <td class="text-right">{{ $formatMoney($payment->payment_amount) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-left">Paid Amount</td>
                    <td class="text-right">{{ $formatMoney($paidAmount) }}</td>
                </tr>
            @endif
            @if ($pendingAmount > 0)
                <tr class="bold">
                    <td class="text-left">Balance Due</td>
                    <td class="text-right">{{ $formatMoney($pendingAmount) }}</td>
                </tr>
            @endif
        </table>

        @php
            $payMode = '';
            if (isset($allPayments) && count($allPayments) > 0) {
                $mList = [];
                foreach ($allPayments as $p) {
                    $m = trim((string) ($p->payment_method ?? ''));
                    $label = ucwords(str_replace(['_', '-'], ' ', $m));
                    if ($m !== '' && !in_array($label, $mList, true)) { $mList[] = $label; }
                }
                $payMode = implode(', ', $mList);
            }
            if ($payMode === '' && !empty($sales->payment_method) && strtolower($sales->payment_method) !== 'pending') {
                $payMode = ucwords(str_replace(['_', '-'], ' ', $sales->payment_method));
            }
            if ($payMode === '') {
                $payMode = $paidAmount > 0 ? 'Paid' : 'Due';
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
        @endphp

        <div class="payment-mode-line">
            Payment Mode: {{ $payMode }} : {{ $formatMoney($paidAmount) }}
        </div>

        @if (!empty($qrBase64))
            <div class="inv-qr">
                <div class="bold" style="font-size: 8px; margin-bottom: 2px;">Scan QR to Pay Bill:</div>
                <img src="{{ $qrBase64 }}" alt="QR Code">
            </div>
        @endif

        <div class="footer text-center bold">
            {{ !empty($setting->footer_text) ? $setting->footer_text : 'Thanks for business with us!' }}
        </div>
    </div>
</body>

</html>
