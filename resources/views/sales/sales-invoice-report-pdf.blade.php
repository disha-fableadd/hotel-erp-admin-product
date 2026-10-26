{{-- <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #000;
    }
    .invoice_pdf td, .invoice_pdf th {
        padding: 6px;
        border: 1px solid #ccc;
        vertical-align: middle;
    }
    .invoice_pdf {
        border-collapse: collapse;
        width: 100%;
    }
    .no-border td {
        border: none !important;
    }
    .section-title {
        background: #f3f3f3;
        font-weight: bold;
    }
    .right { text-align: right; }
    .center { text-align: center; }
</style>

<div class="download_pdf">

    <!-- Header -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:5px;">
        <tr>
            <!-- Logo -->
            <td style="width:50%; text-align:left; vertical-align:top;">
                <img src="{{ $setting->logo ? storage_path('app/public/' . $setting->logo) : public_path('/admin/assets/img/weblogo.jpeg') }}" 
                     style="height:60px; max-width:120px;">
            </td>

            <!-- Company Info -->
            <td style="width:50%; text-align:right; vertical-align:top; font-size:13px; line-height:1.4;">
                <b>{{ $setting->name ?? '' }}</b><br>
                {{ $setting->address ?? '' }}<br>
                Phone : {{ $setting->phone ?? '' }} | Email : {{ $setting->email ?? '' }}
            </td>
        </tr>
    </table>

    <!-- Separator line -->
    <hr style="border:0; border-top:1px solid #000; margin:5px 0;">

    <!-- Bill of Supply Title -->
    <div style="text-align:center; font-weight:bold; font-size:16px; margin:5px 0;">
        Bill of Supply
    </div>

   <!-- Invoice No / GST No -->
<table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
    <tr>
        <td style="width:50%; text-align:left; font-size:13px; padding:4px;">
            <b>INVOICE NO :</b> {{ $invoice->invoice_number ?? '' }}
        </td>
        <td style="width:50%; text-align:right; font-size:13px; padding:4px;">
            <b>GST NO :</b> {{ $setting->gst_num ?? '' }}
        </td>
    </tr>
</table>


    <!-- Info Section -->
    <table class="invoice_pdf" style="width:100%; border-collapse:collapse; border:1px solid #000;">
        <tr>
            <!-- Vendor/Customer Details -->
            <td style="width:33%; padding:10px; vertical-align:top; border-right:1px solid #000;">
                <b>{{ $partyDetails['role'] }} Details:</b><br>
                Name : {{ $partyDetails['name'] }}<br>
                Email : {{ $partyDetails['email'] }}<br>
                Phone : {{ $partyDetails['phone'] }}<br>
                Address : {{ $partyDetails['address'] }}, {{ $partyDetails['city'] }}, {{ $partyDetails['country'] }}<br>
                GST No : {{ $partyDetails['gst_number'] }}<br>
                PAN No : {{ $partyDetails['pan_number'] }}
            </td>

            <!-- Company Info -->
            <td style="width:33%; padding:10px; vertical-align:top; border-right:1px solid #000;">
                <b>Company Info:</b><br>
                {{ $setting->name ?? '' }}<br>
                {{ $setting->email ?? '' }}<br>
                {{ $setting->phone ?? '' }}<br>
                {{ $setting->address ?? '' }}<br>
                GST : {{ $setting->gst_num ?? '' }}
            </td>

            <!-- Invoice Info -->
            <td style="width:34%; padding:10px; vertical-align:top;">
                <b>Invoice Info:</b><br>
                Order Number: {{ $invoice->order_number ?? '' }}<br>
                Order Date: {{ $invoice->created_at ? date('d M Y', strtotime($invoice->created_at)) : '' }}<br>
                Order Status: {{ ucfirst($invoice->status ?? '') }}<br>
                Payment Status: {{ ucfirst($invoice->payment_status ?? 'Pending') }}<br>
                Payment Method: {{ ucfirst($invoice->payment_method ?? 'Pending') }}<br> 
            </td>
        </tr>
    </table>

    <br>

    <!-- Item Table -->
    <table class="invoice_pdf">
        <tr class="section-title">
            <td>Sr No</td>
            <td>Item Name</td>
            <td class="center">Price</td>
            <td class="center">QTY</td>
            <td class="right">Subtotal</td>
        </tr>
        @foreach ($orderItems as $i => $item) 
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item->product->name ?? 'Item' }}</td>
            <td class="center">
                @if ($setting->currency_position === 'right')
                    {{ $item->price }}{{ $setting->currency_symbol }}
                @else
                    {{ $setting->currency_symbol }}{{ $item->price }}
                @endif
            </td>
            <td class="center">{{ $item->quantity }}</td>
            <td class="right">
                @if ($setting->currency_position === 'right')
                        {{ $item->total_amount }}{{ $setting->currency_symbol }}
                        @else
                        {{ $setting->currency_symbol }}{{ $item->total_amount }}
                        @endif
            </td>
        </tr>
        @endforeach
    </table>

    <br>

    <!-- Totals -->
<table class="invoice_pdf" style="width: 300px; margin-left:auto;">
    <tr>
        <td>Subtotal</td>
        <td class="right">{{ number_format($subtotal, 2) }}</td>
    </tr>
    <tr>
        <td>Discount</td>
        <td class="right">{{ $discountPercent }}% ({{ number_format($discountAmount, 2) }})</td>
    </tr>
    <tr>
        <td>After Discount</td>
        <td class="right">{{ number_format($afterDiscount, 2) }}</td>
    </tr>
    @foreach ($taxDetails as $tax)
    <tr>
        <td>{{ $tax['name'] }} ({{ $tax['rate'] }}%)</td>
        <td class="right">{{ $setting->currency_symbol }}{{ number_format($tax['amount'], 2) }}</td>
    </tr>
    @endforeach
    <tr class="section-title">
        <td>Total</td>
        <td class="right">{{ $setting->currency_symbol }}{{ number_format($finalTotal, 2) }}</td>
    </tr>
</table>


    <br>

    <!-- Footer -->
    <table class="no-border" style="width:100%; margin-top:50px;">
        <tr>
            <td>
                <b>Remarks :</b> {{ $invoice->remarks ?? 'N/A' }}
            </td>
            <td class="right">
                For, {{ $setting->name ?? 'Company' }}<br><br><br>
                (Authorized Signatory)
            </td>
        </tr>
    </table>

</div> --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice PDF</title>
    <style>
        @page {
            size: A4;
            margin: 1mm 1mm;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            /* Set base font size here */
            margin: 0;
            padding: 0;
            background: white;
        }

        .pdf-wrapper {
            margin-top: 3mm;
            /* ensures top border is visible */
        }

        .card-body {
            width: 95%;
            min-height: 95%;
            padding: 3mm;
            margin: auto;
            box-sizing: border-box;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 1px solid black;
            font-size: 12px;
        }

        table,
        table td,
        table th {
            font-size: inherit;
            /* Make sure tables inherit the font size */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 20px; */
        }

        .header-table {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            margin-bottom: 30px;
        }

        .header-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 4px 8px;
        }

        .table-bordered thead tr {
            background-color: #e9ecf0ff;
            color: #333;
        }

        .table-bordered tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table-bordered tfoot tr {
            font-weight: bold;
            background-color: #e9ecef;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        h3,
        h4 {
            margin: 0 0 10px 0;
            color: #343a40;
        }

        .invoice-title {
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 2px solid #e5e7ebff;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        .logo-container {
            position: relative;
            min-height: 50px;
            margin-bottom: 10px;
        }

        .logo-container .qr-code {
            height: 60px;
            position: absolute;
            /* top: 0;
            left: 0; */
        }

        .logo-container .company-logo {
            height: 50px;
            position: absolute;
            top: 0;
            left: 0;
        }

        .logo-container .company-details {
            text-align: center;
        }


        .signature-section img {
            height: 50px;
            margin-top: 5px;
        }

        .signature-section {
            margin-top: 50px;
            text-align: right;
        }

        .footer-section {
            width: 95%;
            position: fixed;
            bottom: 45px;
            left: 20px;
            right: 0;
        }
    </style>
</head>

<body>

    <div class="card-body">
        <table style="width:100%; margin-bottom: 10px; border-collapse: collapse;">
            <tr>
                <td style="width: 150px; vertical-align: top;">
                    @if (isset($setting->logo) && file_exists(storage_path('app/public/' . $setting->logo)))
                        @php
                            $logoPath = storage_path('app/public/' . $setting->logo);
                            $logoData = base64_encode(file_get_contents($logoPath));
                            $logoMime = mime_content_type($logoPath);
                        @endphp
                        <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo"
                            style="height: 100px; width: auto;"> {{-- adjust height as needed --}}
                    @endif
                </td>
                <td style="vertical-align: middle; padding-left: 15px; text-align: right;">
                    <h3 style="margin: 0; text-transform: uppercase;">{{ $setting->name ?? '' }}</h3>
                    <small style="text-transform: uppercase;">
                        {{ $setting->address ?? '' }}<br>
                        Phone: {{ $setting->phone ?? '' }} |
                        Email: <span style="text-transform: none;">{{ $setting->email ?? '' }}</span>
                    </small>
                </td>
            </tr>
        </table>



        <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 20px;">


        {{-- <div class="text-center mb-1">
            <!-- <h3 class="mb-3" style="text-transform: uppercase;">Bill of Supply</h3> -->
            <div style="margin-bottom: 10px; width: 100%; text-align: center;">
                <div style="display: inline-block; width: 49%; text-align: left;">
                    <strong>INVOICE NO : {{ $sales->order_number ?? '-' }}</strong>
                </div>
                <div style="display: inline-block; width: 49%; text-align: right;">
                    <strong>GST NO : {{ $setting->gst_num ?? ' -- ' }}</strong>
                </div>
            </div>
        </div> --}}

        <table style="width:100%; border-collapse: collapse; font-size: 12px; margin-bottom: 10px;">
            <tr>
                {{-- <td
                    style="width:33%; position: relative; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Customer
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 12px; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 8px 0;">Name :</td>
                            <td style="text-align: right; padding: 0;">
                                {{ $customer['name'] ?? 'walk-in-customer' }}
                            </td>
                        </tr>
                        @if (!empty($customer['phone']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Phone :</td>
                                <td style="text-align: right; padding: 0;">{{ $customer['phone'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($customer['email']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Email :</td>
                                <td style="text-align: right; padding: 0;">{{ $customer['email'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($customer['address']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Address :</td>
                                <td style="text-align: right; padding: 0;">{{ $customer['address'] }}</td>
                            </tr>
                        @endif
                    </table>

                    <!-- Half vertical line -->
                    <div style="position: absolute; right: 0; top: 2%; height: 13%; border-right: 1px solid #ff9f43;">
                    </div>
                </td> --}}

                <!-- Vehicle Details -->
                <td
                    style="width:33%; position: relative; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Company
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 12px; color: inherit;">
                        @if (!empty($setting->name))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Name :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->name }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->email))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Email :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->email }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->phone))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Phone :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->phone }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->address))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Address :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->address }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->gst_num))
                            <tr>
                                <td style="padding: 0 0 8px 0;">GST :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->gst_num }}</td>
                            </tr>
                        @endif
                    </table>

                    <div style="position: absolute; right: 0; top: 2%; height: 11%; border-right: 1px solid #ff9f43;">
                    </div>
                </td>


                <!-- Invoice Details -->
                <td
                    style="width:34%; border: 0px solid #dee2e6; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Order
                        Report Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 12px; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 8px 0;">Total Orders: :</td>
                            <td style="text-align: right; padding: 0;">{{ count($sales) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0 0 8px 0;">Report Date :</td>
                            <td style="text-align: right; padding: 0;">
                                {{ \Carbon\Carbon::now()->format('d M Y') }}
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>


        <div class="text-center">
            <h4 style="text-transform: uppercase;">Items</h4>
        </div>

        <table class="table-bordered"
            style="width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 10px 0;">
            <thead>

                <tr class="heading" style="background-color:#ff9f43; color:#fff;">
                    <td style="padding: 10px;"><strong>Item</strong></td>
                    <td style="padding: 10px;"><strong>Category</strong></td>
                    <td style="padding: 10px;"><strong>Original Price</strong></td>
                    <td style="padding: 10px;"><strong>Discount</strong></td>
                    <td style="padding: 10px;"><strong>Final Unit Price</strong></td>
                    <td style="padding: 10px;"><strong>Quantity</strong></td>
                    <td style="padding: 10px;"><strong>Quantity Type</strong></td>
                    <td style="padding: 10px;"><strong>Taxes</strong></td>
                    <td style="padding: 10px;"><strong>Total</strong></td>
                </tr>
                @php $subtotal = 0; @endphp
                @foreach ($sales as $sale)
                    @php
                        $discountPercent = $sale->invoice->discount ?? 0;
                        $originalUnitPrice = $sale->quantity ? $sale->total_amount / $sale->quantity : 0;
                        $discountPerUnit = ($originalUnitPrice * $discountPercent) / 100;
                        $finalUnitPrice = $originalUnitPrice - $discountPerUnit;
                        $finalTotal = $finalUnitPrice * $sale->quantity;

                        $subtotal += $finalTotal;

                        $images = json_decode($sale->product->images, true);
                        $imagePath =
                            isset($images[0]) && file_exists(public_path('storage/' . $images[0]))
                                ? env('ImagePath') . 'storage/' . $images[0]
                                : env('ImagePath') . 'admin/assets/img/product/noimage.png';
                    @endphp

                    <tr class="details" style="border-bottom: 1px solid #E9ECEF;">
                        <td style="padding: 10px; white-space: normal;">
                            <a href="{{ url('product-view/' . ($sale->product->id ?? '')) }}"
                                style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                                <img src="{{ $imagePath }}" alt="Item Image" style="width: 50px; height: 50px;">
                                <div style="font-weight: 500;">{{ $sale->product->name ?? '-' }}</div>
                            </a>
                        </td>
                        <td style="padding: 10px; white-space: normal;">
                            {{ $sale->product->category->name ?? 'N/A' }}</td>
                        <td style="padding: 10px;">
                            {{ $currencyPosition === 'left' ? $currencySymbol . number_format($originalUnitPrice, 2) : number_format($originalUnitPrice, 2) . $currencySymbol }}
                        </td>
                        <td style="padding: 10px;">{{ $discountPercent }}%</td>
                        <td style="padding: 10px;">
                            {{ $currencyPosition === 'left' ? $currencySymbol . number_format($finalUnitPrice, 2) : number_format($finalUnitPrice, 2) . $currencySymbol }}
                        </td>
                        <td style="padding: 10px;">{{ $sale->quantity }}</td>

                        <td style="padding: 10px;">{{ $sale->quantityType->quantity_type }}</td>

                        <td style="padding: 10px;">
                            @if ($sale->rowGSTOption === 'with_gst' && !empty($sale->rowTaxes))
                                @foreach ($sale->rowTaxes as $t)
                                    <div>
                                        {{ $t['name'] }} ({{ $t['rate'] }}%) :
                                        {{ $currencyPosition === 'left' ? $currencySymbol . number_format($t['amount'], 2) : number_format($t['amount'], 2) . $currencySymbol }}
                                    </div>
                                @endforeach
                            @else
                                <span>N/A</span>
                            @endif
                        </td>
                        <td style="padding: 10px;">
                            {{ $currencyPosition === 'left' ? $currencySymbol . number_format($sale->rowFinalTotal, 2) : number_format($sale->rowFinalTotal, 2) . $currencySymbol }}
                        </td>

                    </tr>
                @endforeach
            </thead>
            <table style="width: 300px; margin-left: auto; border-collapse: collapse; font-size: 12px; color: #333;">
                <tr>
                    <td
                        style="padding: 6px 12px; text-align: left; background-color: #ff9f43; color:#fff; font-weight:bold; border: 1px solid #e0e0e0;">
                        Total Amount
                    </td>
                    <td style="padding: 6px 12px; text-align: right; border: 1px solid #e0e0e0;">
                        {{ $currencyPosition === 'left'
                            ? $currencySymbol . number_format($totalAmount, 2)
                            : number_format($totalAmount, 2) . $currencySymbol }}
                    </td>
                </tr>


            </table>

        </table>

    </div>

</body>

</html>
