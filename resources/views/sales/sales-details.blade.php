@extends('layout.app')

@section('title', 'Order Details')

@section('content')
<style>
    .total-order ul li h5 {
        font-size: 12px;
    }

    .invoice-box {
        overflow-x: auto;
    }

    .invoice-box table {
        width: 100%;
        border-collapse: collapse;
    }

    .invoice-box table td {
        white-space: nowrap;
    }

    /* Adjust columns for mobile, keep same layout */
    @media (max-width: 767px) {
        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .col-lg-6.table_view {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .col-lg-6.total-order {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .invoice-box,
        .invoice-box table,
        .invoice-box td,
        .invoice-box h4,
        .invoice-box h5 {
            font-size: 12px;
            line-height: 1.2;
        }

        font {
            line-height: 22px !important;
        }
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Order Details</h4>

        </div>
        <div class="page-btn d-flex gap-2">
            <!-- ✅ Add Order -->
            <a href="{{ route('sales.add') }}" class="btn btn-added">
                <i class="fa fa-plus me-1"></i> Add Order
            </a>
            @if (!$hasPaymentStarted)
            <a href="{{ route('sales.edit', $view_id) }}" class="btn btn-primary">
                <i class="fa fa-edit me-1"></i> Edit
            </a>
            @endif
            <!-- ✅ Print Invoice -->
            <a href="{{ route('sales.invoice.pdf', $view_id) }}" target="_blank" class="btn btn-primary">
                <i class="fa fa-print me-1"></i> Print
            </a>

            <!-- Back -->
            <a href="{{ route('sales.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                Back
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <input type="hidden" name="selse_id" id="selse_id" value="{{ $view_id }}">

            <div class="invoice-box table-height"
                style="max-width: 1600px;width:100%;overflow: auto;margin:15px auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left;">
                    <tbody id="product-details">
                        <tr class="top">
                            <td colspan="6" style="padding: 5px;vertical-align: top;">
                                <table style="width: 100%;line-height: inherit;text-align: left;" class="product-list">
                                    <tbody>
                                        <tr>
                                            <td
                                                style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                <font style="vertical-align: inherit;margin-bottom:25px;">
                                                    <font
                                                        style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">
                                                        Customer Info</font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;font-weight: 400;"
                                                        class="customer-name"> walk-in-customer</font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;font-weight: 400;">
                                                        <a href="/cdn-cgi/l/email-protection" class="__cf_email__"
                                                            data-cfemail="3a4d5b565117535417594f494e55575f487a5f425b574a565f14595557">[email&#160;protected]</a>
                                                    </font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;font-weight: 400;"
                                                        class="customer-phone"> 123456780</font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;font-weight: 400;">
                                                        N45 , Dhaka</font>
                                                </font><br>
                                            </td>
                                            <td
                                                style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                <font style="vertical-align: inherit;margin-bottom:25px;">
                                                    <font
                                                        style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">
                                                        Company Info</font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;font-weight: 400;"
                                                        class="company-info-display"> -</font>
                                                </font><br>
                                            </td>
                                            <td
                                                style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                <font style="vertical-align: inherit;margin-bottom:25px;">
                                                    <font
                                                        style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">
                                                        Invoice Info</font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;font-weight: 400;">
                                                        Order Number </font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;font-weight: 400;">
                                                        Payment Status</font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;font-weight: 400;">
                                                        Payment Methods</font>
                                                </font><br>
                                            </td>
                                            <td
                                                style="padding:5px;vertical-align:top;text-align:right;padding-bottom:20px">
                                                <font style="vertical-align: inherit;margin-bottom:25px;">
                                                    <font
                                                        style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">
                                                        &nbsp;</font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;font-weight: 400;"
                                                        class="invoice-id">SL0101 </font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;color:#2E7D32;font-weight: 400;"
                                                        class="payment-status"> Paid</font>
                                                </font><br>
                                                <font style="vertical-align: inherit;">
                                                    <font
                                                        style="vertical-align: inherit;font-size: 14px;color:#2E7D32;font-weight: 400;"
                                                        class="order-method"> -</font>
                                                </font><br>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr class="heading " style="background: #F3F2F7;">
                            <td
                                style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                Item Name
                            </td>
                            <td
                                style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                QTY
                            </td>
                            <td
                                style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                Price
                            </td>
                            <td
                                style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                Discount
                            </td>
                            <td
                                style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                TAX
                            </td>
                            <td
                                style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                Subtotal
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="row">
                    <div class="col-lg-6 table_view">
                        <div class="total-order w-100 max-widthauto m-auto mb-4">
                        </div>
                    </div>
                    @php
                    $subtotal = $totalAmount;
                    $discountPercentage = $sales->discount ?? 0;
                    $discountAmount = ($subtotal * $discountPercentage) / 100;

                    // ✅ Apply discount before tax
                    $taxableAmount = $subtotal - $discountAmount;

                    $taxDetails = [];
                    $totalTaxAmount = 0;

                    foreach ($taxes as $tax) {
                    $taxAmount = ($taxableAmount * $tax->tax_rate) / 100;
                    $taxDetails[] = [
                    'name' => $tax->tax_name,
                    'rate' => $tax->tax_rate,
                    'amount' => $taxAmount,
                    ];
                    $totalTaxAmount += $taxAmount;
                    }

                    $finalTotal = $taxableAmount + $totalTaxAmount;
                    @endphp

                    <div class="col-lg-6">
                        <div class="total-order w-100 max-widthauto m-auto mb-4">
                            <ul>
                                <li>
                                    <h4>Subtotal</h4>
                                    <h5>
                                        @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}{{ number_format($subtotal, 2) }}
                                        @else
                                        {{ number_format($subtotal, 2) }}{{ $currencySymbol }}
                                        @endif
                                    </h5>
                                </li>

                                <li>
                                    <h4>Discount</h4>
                                    <h5>
                                        {{ $discountPercentage }}%
                                        (
                                        @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}{{ number_format($discountAmount, 2) }}
                                        @else
                                        {{ number_format($discountAmount, 2) }}{{ $currencySymbol }}
                                        @endif
                                        )
                                    </h5>
                                </li>

                                <li>
                                    <h4>After Discount</h4>
                                    <h5>
                                        @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}{{ number_format($subtotal - $discountAmount, 2) }}
                                        @else
                                        {{ number_format($subtotal - $discountAmount, 2) }}{{ $currencySymbol }}
                                        @endif
                                    </h5>
                                </li>

                                @foreach ($taxDetails as $tax)
                                <li>
                                    <h4>{{ $tax['name'] }} Tax</h4>
                                    <h5>
                                        {{ $tax['rate'] }}%
                                        (@if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}{{ number_format($tax['amount'], 2) }}
                                        @else
                                        {{ number_format($tax['amount'], 2) }}{{ $currencySymbol }}
                                        @endif)
                                    </h5>
                                </li>
                                @endforeach

                                <li class="total">
                                    <h4>Total</h4>
                                    <h5>
                                        @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}{{ number_format($finalTotal, 2) }}
                                        @else
                                        {{ number_format($finalTotal, 2) }}{{ $currencySymbol }}
                                        @endif
                                    </h5>
                                </li>
                                <li style="border-top:1px dashed #ddd; padding-top:10px;">
                                    <h4 style="color:#2E7D32;">Paid Amount</h4>
                                    <h5 id="paidAmountText" style="color:#2E7D32;font-weight:600;">
                                        ₹0.00
                                    </h5>
                                </li>

                                <li>
                                    <h4 style="color:#C62828;">Pending Amount</h4>
                                    <h5 id="pendingAmountText" style="color:#C62828;font-weight:600;">
                                        ₹0.00
                                    </h5>
                                </li>

                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");
        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

        let salse_id = $("#selse_id").val();

        if (salse_id) {
            fetchsalseDetails(salse_id);
        }

        function formatCurrency(amount, symbol = '₹', position = 'left') {
            amount = parseFloat(amount).toFixed(2);
            return position === 'left' ? `${symbol}${amount}` : `${amount}${symbol}`;
        }

        function fetchsalseDetails(salse_id) {
            $.ajax({
                url: `/api/getsalseById/${salse_id}`,
                type: "GET",
                dataType: "json",
                data: {
                    selectedSubAdminId: selectedSubAdminId // ✅ fixed object syntax
                },
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    const dateStr = response.order_items[0].date;
                    const dateObj = new Date(dateStr);

                    // Options for the formatter
                    const options = {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    };

                    // Using Intl.DateTimeFormat
                    // const formattedDate = new Intl.DateTimeFormat('en-GB', options).format(dateObj);
                    // if (response.error) {
                    //     console.error(response.error);
                    //     return;
                    // }

                    const sale = response.sales;
                    // console.log(sale.user.gst_number);
                    const company_info = response.company_info;



                    // Update customer and invoice info
                    $(".customer-name").text(sale.user_name || "walk-in-customer");
                    $(".customer-phone").text(sale.user_phone || "N/A");
                    $(".invoice-id").text(sale.order_number);
                    $(".payment-status").text(sale.payment_status.charAt(0).toUpperCase() + sale
                        .payment_status.slice(1));
                    $(".order-method").text((sale.payment_method || "N/A").charAt(0).toUpperCase() + (sale.payment_method || "N/A").slice(1));

                     const paidAmount = formatCurrency(
                                sale.paid_amount ?? 0,
                                response.currency_symbol,
                                response.currency_position
                            );

                            const pendingAmount = formatCurrency(
                                sale.pending_amount ?? 0,
                                response.currency_symbol,
                                response.currency_position
                            );
                    $("#paidAmountText").text(paidAmount);
                    $("#pendingAmountText").text(pendingAmount);
                    // Populate product rows
                    const items = sale.order_items;

                    // Check if each section has any real data
                    const hasCustomerData = sale.user_name || sale.user_phone || sale.user_email || sale.user_address || sale.user?.gst_number || sale.user?.pan_number;
                    const hasCompanyData = company_info?.name || company_info?.email || company_info?.phone || company_info?.address;
                    const hasOrderData = sale.order_number || sale.created_at || sale.payment_status || sale.payment_method;

                    // Build section HTML conditionally
                    const customerSection = hasCustomerData ? `
                        <font style="vertical-align: inherit;margin-bottom:25px;">
                            <font style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">Customer Info</font>
                        </font><br>
                        ${sale.user_name ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="customer-name">${sale.user_name}</font></font><br>` : ''}
                        ${sale.user_email ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="customer-email">${sale.user_email}</font></font><br>` : ''}
                        ${sale.user_phone ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="customer-phone">${sale.user_phone}</font></font><br>` : ''}
                        ${sale.user_address ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="customer-address">${sale.user_address}</font></font><br>` : ''}
                        ${sale.user?.gst_number ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="gst-no"><strong>GST No:</strong> ${sale.user.gst_number}</font></font><br>` : ''}
                        ${sale.user?.pan_number ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="pan-no"><strong>PAN No:</strong> ${sale.user.pan_number}</font></font><br>` : ''}
                    ` : `<font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">N/A</font>`;

                    const companySection = hasCompanyData ? `
                        <font style="vertical-align: inherit;margin-bottom:25px;">
                            <font style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">Company Info</font>
                        </font><br>
                        ${company_info?.name ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">${company_info.name}</font></font><br>` : ''}
                        ${company_info?.email ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">${company_info.email}</font></font><br>` : ''}
                        ${company_info?.phone ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">${company_info.phone}</font></font><br>` : ''}
                        ${company_info?.address ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">${company_info.address}</font></font><br>` : ''}
                    ` : `<font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">N/A</font>`;

                    const orderSection = hasOrderData ? `
                        <font style="vertical-align: inherit;margin-bottom:25px;">
                            <font style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">Order Info</font>
                        </font><br>
                        ${sale.order_number ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;"> Order Number  </font></font><br>` : ''}
                        ${sale.created_at ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;"> Order Date  </font></font><br>` : ''}
                        ${sale.payment_status ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;"> Payment Status</font></font><br>` : ''}
                        ${sale.payment_method ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">Payment Method</font></font><br>` : ''}
                    ` : `<font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">N/A</font>`;

                    const orderSectionValues = hasOrderData ? `
                        <font style="vertical-align: inherit;margin-bottom:25px;">
                            <font style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">&nbsp;</font>
                        </font><br>
                        ${sale.order_number ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="invoice-id">#${sale.order_number}</font></font><br>` : ''}
                        ${sale.created_at ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="invoice-id">${sale.created_at}</font></font><br>` : ''}
                        ${sale.payment_status ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit;font-size: 14px;color:#2E7D32;font-weight: 400;" class="payment-status">${sale.payment_status.charAt(0).toUpperCase() + sale.payment_status.slice(1)}</font></font><br>` : ''}
                        ${sale.payment_method ? `<font style="vertical-align: inherit;"><font style="text-transform: capitalize; vertical-align: inherit;font-size: 14px;color:#2E7D32;font-weight: 400;" class="order-method">${sale.payment_method}</font></font><br>` : ''}
                    ` : `<font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">&nbsp;</font>`;

                    let productRows = `
                                    <tr class="top">
                                        <td colspan="6" style="padding: 5px;vertical-align: top;">
                                            <table style="width: 100%;line-height: inherit;text-align: left;" class="product-list">
                                                <tbody>
                                                    <tr>
                                                        <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                            ${customerSection}
                                                        </td>
                                                        <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                            ${companySection}
                                                        </td>
                                                        <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                            ${orderSection}
                                                        </td>
                                                        <td style="padding:5px;vertical-align:top;text-align:right;padding-bottom:20px">
                                                            ${orderSectionValues}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="heading " style="background: #F3F2F7;">
                                        <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                            Item Name
                                        </td>
                                        <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                            QTY
                                        </td>
                                        <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                            QTY Type
                                        </td>
                                        <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                            Price
                                        </td>

                                        <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                            Subtotal
                                        </td>
                                    </tr>`;

                    items.forEach((item) => {
                        // console.log(item);
                        const product = item.product || {};
                        // console.log(product.quantity_type);

                        const taxAmount =
                            0; // You can calculate tax if required from tax_ids
                        const discount = sale.discount || 0;

                        let defaultProductImage = '{{ image_path('admin/assets/img/product/noimage.png') }}';
                        let imagePath = defaultProductImage;

                        if (product.image_url && Array.isArray(product.image_url) && product.image_url.length > 0 && product.image_url[0]) {
                            imagePath = product.image_url[0];
                        } else if (product.images) {
                            let raw = product.images;
                            if (typeof raw === 'string') {
                                try {
                                    raw = JSON.parse(raw);
                                    if (typeof raw === 'string') {
                                        raw = JSON.parse(raw);
                                    }
                                } catch (e) {}
                            }
                            let firstImg = Array.isArray(raw) ? raw[0] : raw;
                            if (firstImg && typeof firstImg === 'string') {
                                if (firstImg.startsWith('http://') || firstImg.startsWith('https://')) {
                                    imagePath = firstImg;
                                } else {
                                    let cleanImg = firstImg.replace(/^\/+/, '').replace(/^storage\//, '');
                                    imagePath = '{{ image_path('storage/') }}' + cleanImg;
                                }
                            }
                        }

                        const unitPrice = formatCurrency(item.price, response
                            .currency_symbol, response.currency_position);
                        const totalAmount = formatCurrency(item.total_amount, response
                            .currency_symbol, response.currency_position);


                        // let orderSubtotal = 0;
                        productRows += `
                                <tr class="details" style="border-bottom:1px solid #E9ECEF;">
                                   <td style="padding: 10px; vertical-align: top;">
                                        <a href="/product-view/${product.id || ''}" class="d-flex align-items-center text-decoration-none text-dark">
                                            <img src="${imagePath}" alt="${product.name || 'product'}" class="me-2" style="width:40px; height:40px; object-fit: cover; border-radius: 4px;" onerror="this.onerror=null; this.src='${defaultProductImage}';">
                                            ${product.name || 'N/A'}
                                        </a>
                                    </td>
                                    <td style="padding: 10px;vertical-align: top;">${item.quantity}</td>
                                    <td style="padding: 10px;vertical-align: top; text-transform: uppercase;">${item.quantity_type?.quantity_type || product.quantity_type?.quantity_type || 'N/A'}</td>
                                    <td style="padding: 10px;vertical-align: top;">${unitPrice}</td>

                                    <td style="padding: 10px;vertical-align: top;">${totalAmount}</td>
                                </tr>`;
                    });
                    $("#product-details").html(productRows);
                },
                error: function() {}
            });
        }
    });
</script>
@endpush
