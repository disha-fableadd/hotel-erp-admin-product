<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px; margin: 0; }
        .container { max-width: 700px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header-top { background-color: #f39c12; height: 10px; }
        .header { padding: 20px 30px; display: flex; justify-content: space-between; border-bottom: 1px solid #eeeeee; }
        .logo-container { width: 40%; }
        .logo-container img { max-width: 150px; }
        .company-info { width: 60%; text-align: right; font-size: 13px; color: #333; line-height: 1.6; }
        .company-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .order-badge { background-color: #f39c12; color: #fff; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; margin-bottom: 10px; text-transform: uppercase; float: right; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .content { padding: 30px; }
        .greeting { font-size: 14px; margin-bottom: 15px; color: #333; line-height: 1.5; }
        .order-meta { background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px 20px; display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 20px; font-weight: 500; }
        table.items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        table.items-table th { background-color: #f39c12; color: white; padding: 12px 10px; text-align: center; border: 1px solid #e0e0e0; font-weight: bold; }
        table.items-table th:nth-child(2) { text-align: left; }
        table.items-table td { padding: 12px 10px; border: 1px solid #e0e0e0; color: #333; text-align: center; }
        table.items-table td:nth-child(2) { text-align: left; font-weight: bold; }
        .totals-wrapper { display: flex; justify-content: flex-end; }
        .totals-box { width: 350px; border: 1px solid #2ecc71; border-radius: 6px; overflow: hidden; margin-left: auto; }
        .totals-header { background-color: #e8f8f5; color: #2ecc71; padding: 12px 15px; font-weight: bold; font-size: 14px; letter-spacing: 1px; }
        .totals-row { display: flex; justify-content: space-between; padding: 10px 15px; font-size: 13px; border-bottom: 1px solid #f0f0f0; color: #555; }
        .totals-row:last-child { border-bottom: none; }
        .totals-row.bold { font-weight: bold; color: #333; font-size: 14px; }
        .totals-row.green { color: #2ecc71; font-weight: bold; font-size: 14px; }
        .totals-row.red { color: #e74c3c; font-weight: bold; font-size: 14px; }
        .grand-total-bar { background-color: #e67e22; color: white; text-align: right; padding: 15px 20px; font-size: 16px; font-weight: bold; border-radius: 0 0 6px 6px; }
        .footer { text-align: center; padding: 15px; font-size: 12px; color: #777; background-color: #f9f9f9; }
        
        /* Fallbacks for older email clients (using table layouts where flex is unsupported) */
        .w-100 { width: 100%; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-top"></div>
        
        <table class="w-100" style="padding: 20px 30px; border-bottom: 1px solid #eeeeee;">
            <tr>
                <td style="width: 40%; vertical-align: top;">
                    @if(isset($shopSetting) && $shopSetting->logo)
                        <img src="{{ asset('storage/' . $shopSetting->logo) }}" alt="Logo" style="max-width: 150px;">
                    @else
                        <!-- Placeholder if no logo -->
                        <h2 style="color: #f39c12; margin:0;">{{ $shopSetting->name ?? config('app.name') }}</h2>
                    @endif
                </td>
                <td style="width: 60%; text-align: right; vertical-align: top; font-size: 13px; color: #333; line-height: 1.6;">
                    <div class="clearfix">
                        <span class="order-badge">ORDER CONFIRMATION</span>
                    </div>
                    <div style="clear: both; margin-top: 15px;">
                        <div class="company-name">{{ $shopSetting->name ?? config('app.name') }}</div>
                        @if(isset($shopSetting->phone))
                            <div>Phone: {{ $shopSetting->phone }}</div>
                        @endif
                        @if(isset($shopSetting->email))
                            <div>Email: <a href="mailto:{{ $shopSetting->email }}" style="color: #3498db; text-decoration: none;">{{ $shopSetting->email }}</a></div>
                        @endif
                        @if(isset($shopSetting->address))
                            <div>Address: {{ $shopSetting->address }}</div>
                        @endif
                        @if(isset($shopSetting->gst_no))
                            <div>GST: {{ $shopSetting->gst_no }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="content">
            <div class="greeting">
                Hello {{ $customerName }},<br><br>
                Your order has been created successfully. Please find the details below.
            </div>

            <table class="w-100" style="background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: 500;">
                <tr>
                    <td style="padding: 15px 20px; text-align: left;">Order No: {{ $order->order_number }}</td>
                    <td style="padding: 15px 20px; text-align: right;">Date: {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y h:i A') }}</td>
                </tr>
            </table>

            <div style="text-align: center; font-weight: bold; margin-bottom: 10px; color: #333; font-size: 14px;">PRODUCT</div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Sr No</th>
                        <th>Product Name</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Discount Amount</th>
                        <th>Total (Excl.GST)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = 1; @endphp
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td>{{ $item->product->name ?? 'Product' }}</td>
                        <td>{{ $item->quantityType->quantity_type ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format($item->price, 2) }}</td>
                        <td>₹0.00</td>
                        <td>₹{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="w-100">
                <tr>
                    <td style="width: 40%;"></td>
                    <td style="width: 60%; vertical-align: top;">
                        <div class="totals-box">
                            <div class="totals-header">TOTALS</div>
                            
                            <table class="w-100" style="font-size: 13px; color: #555;">
                                <tr>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0;">Total Amount</td>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; text-align: right; color: #333; font-weight: bold;">₹{{ number_format($order->total_amount + ($order->discount ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0;">Discount Amount</td>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; text-align: right; color: #333; font-weight: bold;">₹{{ number_format($order->discount ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0;">After Discount Amount</td>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; text-align: right; color: #333; font-weight: bold;">₹{{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                              
                                <tr>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; font-weight: bold; color: #333; font-size: 14px;">Grand Total</td>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; text-align: right; font-weight: bold; color: #333; font-size: 14px;">₹{{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; font-weight: bold; color: #2ecc71; font-size: 14px;">Paid Amount</td>
                                    <td style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; text-align: right; font-weight: bold; color: #2ecc71; font-size: 14px;">₹{{ number_format($order->total_amount - $order->remaining_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 15px; font-weight: bold; color: #e74c3c; font-size: 14px;">Pending Amount</td>
                                    <td style="padding: 10px 15px; text-align: right; font-weight: bold; color: #e74c3c; font-size: 14px;">₹{{ number_format($order->remaining_amount, 2) }}</td>
                                </tr>
                            </table>

                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="grand-total-bar">
            Grand Total : ₹{{ number_format($order->total_amount, 2) }}
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} Copyright - {{ $shopSetting->name ?? config('app.name') }}
        </div>
    </div>
</body>
</html>
