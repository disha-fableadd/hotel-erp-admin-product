@extends('layout.app')

@section('title', 'Order Invoice')

@section('content')
    <style>
        .logo_img {
            max-width: 150px;
            height: auto;
        }

        .restaurant-invoice {
            border: 1px solid #f0e6dc;
            border-radius: 8px;
            overflow: hidden;
            background: #fffaf5;
        }

        .restaurant-invoice .invoice-box {
            background: #fff;
            border: 1px solid #f3d7b8;
            border-radius: 8px;
            padding: 18px !important;
        }

        .invoice-brand-strip {
            background: #ff9f43;
            color: #fff;
            padding: 10px 14px;
            font-weight: 700;
            letter-spacing: .2px;
            border-radius: 6px;
            margin-bottom: 12px;
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
            .invoice-box {
                overflow-x: auto;
            }

            .card-sales-split {
                flex-direction: row;
            }

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

            .logo_img {
                max-width: 130px;
                height: auto;
            }

            font {
                line-height: 22px !important;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Order Invoice</h4>

            </div>

            <div class="page-btn d-flex gap-2">
                <!-- ? Add Order -->
                <a href="{{ route('sales.add') }}" class="btn btn-added">
                    <i class="fa fa-plus me-1"></i> Add Order
                </a>
                @if (!$hasPaymentStarted)
                    <a href="{{ route('sales.edit', $view_id) }}" class="btn btn-primary">
                        <i class="fa fa-edit me-1"></i> Edit
                    </a>
                @endif
                <!-- ? Print Invoice -->
                <a href="{{ route('sales.invoice.pdf', $view_id) }}" target="_blank" class="btn btn-primary">
                    <i class="fa fa-print me-1"></i> Print
                </a>

                <!-- Back -->
                <a href="{{ route('sales.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                    Back
                </a>
            </div>
        </div>


        <div class="card restaurant-invoice">
            <div class="card-body">
                <input type="hidden" name="selse_id" id="selse_id" value="{{ $view_id }}">

                <div class="download_pdf">
                    <div class="invoice-box table-height"
                        style="max-width: 1600px;width:100%;margin:15px auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                        <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left;">
                            <tbody id="product-details">

                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="total-order w-100 max-widthauto m-auto mb-4">
                                </div>
                            </div>
                            @php
                                $subtotal = $totalAmount;
                                $discountPercentage = $sales->discount; // e.g., 10 for 10%
                                $discountAmount = ($subtotal * $discountPercentage) / 100;

                                // ? Discounted subtotal
                                $afterDiscount = $subtotal - $discountAmount;

                                $taxDetails = [];
                                $totalTaxAmount = 0;

                                // ? Apply tax on discounted subtotal
                                foreach ($taxes as $tax) {
                                    $taxAmount = ($afterDiscount * $tax->tax_rate) / 100;
                                    $taxDetails[] = [
                                        'name' => $tax->tax_name,
                                        'rate' => $tax->tax_rate,
                                        'amount' => $taxAmount,
                                    ];
                                    $totalTaxAmount += $taxAmount;
                                }

                                // ? Final total
                                $finalTotal = $afterDiscount + $totalTaxAmount;
                            @endphp


                            <div class="col-lg-6">
                                <div class="total-order w-100 max-widthauto m-auto mb-4">
                                    <ul>
                                        @php
                                            if (!function_exists('formatCurrency')) {
                                                function formatCurrency($amount, $symbol, $position = 'left')
                                                {
                                                    return $position === 'right'
                                                        ? number_format($amount, 2) . $symbol
                                                        : $symbol . number_format($amount, 2);
                                                }
                                            }
                                        @endphp

                                        <li>
                                            <h4>Subtotal</h4>
                                            <h5>{{ formatCurrency($subtotal, $setting?->currency_symbol ?? '₹', $setting?->currency_position ?? 'left') }}
                                            </h5>
                                        </li>

                                        <li>
                                            <h4>Discount</h4>
                                            <h5>{{ $discountPercentage }}%
                                                ({{ formatCurrency($discountAmount, $setting?->currency_symbol ?? '₹', $setting?->currency_position ?? 'left') }})
                                            </h5>
                                        </li>

                                        <li>
                                            <h4>After Discount</h4>
                                            <h5>{{ formatCurrency($afterDiscount, $setting?->currency_symbol ?? '₹', $setting?->currency_position ?? 'left') }}
                                            </h5>
                                        </li>

                                        @foreach ($taxDetails as $tax)
                                            <li>
                                                <h4>{{ $tax['name'] }} Tax</h4>
                                                <h5>{{ $tax['rate'] }}%
                                                    ({{ formatCurrency($tax['amount'], $setting?->currency_symbol ?? '₹', $setting?->currency_position ?? 'left') }})
                                                </h5>
                                            </li>
                                        @endforeach

                                        <li class="total">
                                            <h4>Total</h4>
                                            <h5>{{ formatCurrency($finalTotal, $setting?->currency_symbol ?? '₹', $setting?->currency_position ?? 'left') }}
                                            </h5>
                                        </li>
                                         <li style="border-top:1px dashed #ddd; padding-top:10px;">
                                            <h4 style="color:#2E7D32;">Paid Amount</h4>
                                            <h5 id="paidAmountText" style="color:#2E7D32;font-weight:600;">
                                                ?0.00
                                            </h5>
                                        </li>

                                        <li>
                                            <h4 style="color:#C62828;">Pending Amount</h4>
                                            <h5 id="pendingAmountText" style="color:#C62828;font-weight:600;">
                                                ?0.00
                                            </h5>
                                        </li>

                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 60px;">
                        <h2 style="color: #ff9f43; font-size: 24px; margin-bottom: 10px;">Thank You for Dining With Us!</h2>
                        <p style="font-size: 16px; color: #555;">We appreciate your order and hope to serve you again soon.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>


@endsection
@push('js')
    <script>
        const userAddress = @json($userAddress);
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            // Get the element you want to convert to PDF
            const element = document.querySelector('.download_pdf');

            // Options for the PDF
            const opt = {
                margin: 10,
                filename: 'invoice_SL0101.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            // Generate the PDF
            html2pdf().set(opt).from(element).save();
        }

        function formatCurrency(amount, symbol = '{{ $setting?->currency_symbol ?? '₹' }}', position = '{{ $setting?->currency_position ?? 'left' }}') {
            amount = parseFloat(amount).toFixed(2);
            symbol = symbol || '{{ $setting?->currency_symbol ?? '₹' }}';
            return position === 'left' ? `${symbol}${amount}` : `${amount}${symbol}`;
        }
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            let salse_id = $("#selse_id").val();
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            if (salse_id) {
                fetchsalseDetails(salse_id);
            }

            function fetchsalseDetails(salse_id) {
                $.ajax({
                    url: `/api/getsalseById/${salse_id}`,
                    type: "GET",
                     data: {
                        selectedSubAdminId: selectedSubAdminId // ? fixed object syntax
                    },
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.error || !response.sales) {
                            return;
                        }

                        const sale = response.sales;
                        const company_info = response.company_info || {};
                        const paymentStatus = sale.payment_status || 'pending';

                        // Update customer and invoice info
                        $(".customer-name").text(sale.user_name || "walk-in-customer");
                        $(".customer-phone").text(sale.user_phone || "N/A");
                        $(".invoice-id").text(sale.order_number);
                        $(".payment-status").text(paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1));
                        $(".order-status").text("Completed"); // or dynamically from status if available

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

                        const items = (response.order_items && response.order_items.length)
                            ? response.order_items
                            : (sale.order_items || []);
                        let productRows = `
                                                <tr class="top">
                                                    <td colspan="6" style="padding: 5px;vertical-align: top;">
                                                        <div class="invoice-brand-strip">Restaurant Order Invoice</div>
                                                        <table style="width: 100%;line-height: inherit;text-align: left;" class="product-list">
                                                        <div class="row">
                                                                <div class="col-6">
                                                                    <img
                                            src="{{ ($setting?->logo ?? null) ? env('ImagePath') . 'storage/' . $setting?->logo : env('ImagePath') . '/admin/assets/img/logso.png' }}"
                                            alt="logo"
                                            class="logo_img">
                                                                </div>
                                                                <div class="col-6 mt-4" style=" text-align: end; ">
                                                                    <h1>Order Invoice</h1>
                                                                    <h4 class="mt-3">#${sale.order_number}</h4>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                        <tbody>
                                                                <tr>
                                                                   <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                                    <font style="vertical-align: inherit; margin-bottom:25px;">
                                                                        <font style="vertical-align: inherit; font-size:14px; color:#ff9f43; font-weight:600; line-height:35px;">
                                                                            Customer Info
                                                                        </font>
                                                                    </font><br>

                                                                    ${sale.user_name ? `
                                                                    <font style="vertical-align: inherit;">
                                                                        <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-name">
                                                                            ${sale.user_name}
                                                                        </font>
                                                                    </font><br>` : ''}

                                                                    ${sale.user_email ? `
                                                                    <font style="vertical-align: inherit;">
                                                                        <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-email">
                                                                            ${sale.user_email}
                                                                        </font>
                                                                    </font><br>` : ''}

                                                                    ${sale.user_address ? `
                                                                    <font style="vertical-align: inherit;">
                                                                        <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-address">
                                                                            ${sale.user_address}
                                                                        </font>
                                                                    </font><br>` : ''}

                                                                    ${sale.user_phone ? `
                                                                    <font style="vertical-align: inherit;">
                                                                        <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-phone">
                                                                            ${sale.user_phone}
                                                                        </font>
                                                                    </font><br>` : ''}

                                                                    ${sale.user?.gst_number ? `
                                                                    <font style="vertical-align: inherit;">
                                                                        <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">
                                                                          <strong>GST No :</strong>  ${sale.user.gst_number}
                                                                        </font>
                                                                    </font><br>` : ''}

                                                                    ${sale.user?.pan_number ? `
                                                                    <font style="vertical-align: inherit;">
                                                                        <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">
                                                                          <strong>PAN No :</strong>  ${sale.user.pan_number}
                                                                        </font>
                                                                    </font><br>` : ''}
                                                                </td>

                                                                   <td style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px">
                                                                    <font style="vertical-align: inherit; margin-bottom:25px;">
                                                                        <font style="vertical-align: inherit; font-size:14px; color:#ff9f43; font-weight:600; line-height: 35px;">
                                                                            Company Info
                                                                        </font>
                                                                    </font><br>

                                                                    ${company_info.name ? `
                                                                                <font style="vertical-align: inherit;">
                                                                                    <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">
                                                                                        ${company_info.name}
                                                                                    </font>
                                                                                </font><br>` : ''}

                                                                    <!-- Email (only if exists) -->
                                                                    ${company_info.email ? `
                                                                                <font style="vertical-align: inherit;">
                                                                                    <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">
                                                                                        ${company_info.email}
                                                                                    </font>
                                                                                </font><br>` : ''}

                                                                    <!-- Phone (only if exists) -->
                                                                    ${company_info.phone ? `
                                                                                <font style="vertical-align: inherit;">
                                                                                    <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">
                                                                                        ${company_info.phone}
                                                                                    </font>
                                                                                </font><br>` : ''}

                                                                    <!-- Address (only if exists) -->
                                                                    ${company_info.address ? `
                                                                                <font style="vertical-align: inherit;">
                                                                                    <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">
                                                                                        ${company_info.address}
                                                                                    </font>
                                                                                </font><br>` : ''}

                                                                        ${company_info.gst_num ? `
                                                                                <font style="vertical-align: inherit;">
                                                                                    <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">
                                                                                       GST: ${company_info.gst_num}
                                                                                    </font>
                                                                                </font><br>` : ''}
                                                                </td>

                                                                    <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                                        <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                            <font style="vertical-align: inherit;font-size:14px;color:#ff9f43;font-weight:600;line-height: 35px; ">Order Info</font>
                                                                        </font><br>
                                                                        ${sale.order_number ? `<font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;"> Order Number  </font>
                                                                        </font><br>` : ''}
                                                                        ${sale.created_at ? `<font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;"> Order Date  </font>
                                                                        </font><br>` : ''}
                                                                        ${sale.payment_status ? `<font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;"> Payment Status</font>
                                                                        </font><br>` : ''}
                                                                        ${sale.payment_method ? `<font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">Payment Method</font>
                                                                        </font><br>` : ''}
                                                                    </td>
                                                                    <td style="padding:5px;vertical-align:top;text-align:right;padding-bottom:20px">
                                                                        <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                            <font style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">&nbsp;</font>
                                                                        </font><br>
                                                                        ${sale.order_number ? `<font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="invoice-id">#${sale.order_number}</font>
                                                                        </font><br>` : ''}
                                                                        ${sale.created_at ? `<font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="invoice-id">${sale.created_at}</font>
                                                                        </font><br>` : ''}
                                                                        ${sale.payment_status ? `<font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;color:#2E7D32;font-weight: 400;" class="payment-status">${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}</font>
                                                                        </font><br>` : ''}
                                                                        ${sale.payment_method ? `<font style="vertical-align: inherit;">
                                                                            <font style="text-transform: capitalize; vertical-align: inherit;font-size: 14px;color:#2E7D32;font-weight: 400;" class="order-method">${sale.payment_method}</font>
                                                                        </font><br>` : ''}
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
                            const product = item.product || {};
                            const taxAmount =
                                0; // You can calculate tax if required from tax_ids
                            const discount = sale.discount || 0;

                            let imagePath = '';
                            let imageBasePath = '{{ env('ImagePath') }}'; // inject from .env

                            try {
                                const images = JSON.parse(product.images || '[]');

                                if (images && images.length > 0 && images[0]) {
                                    imagePath = `${imageBasePath}/storage/${images[0]}`;
                                } else {
                                    imagePath =
                                        `${imageBasePath}/admin/assets/img/product/noimage.png`;
                                }
                            } catch (e) {
                                imagePath =
                                    `${imageBasePath}/admin/assets/img/product/noimage.png`;
                            }
                            const currencySymbol = response.currency_symbol || company_info.currency_symbol || '{{ $setting?->currency_symbol ?? '₹' }}';
                            const currencyPosition = response.currency_position || company_info.currency_position || '{{ $setting?->currency_position ?? 'left' }}';
                            const unitPrice = formatCurrency(item.price, currencySymbol, currencyPosition);
                            const lineTotal = item.total_amount ?? (parseFloat(item.price) * parseFloat(item.quantity));
                            const totalAmount = formatCurrency(lineTotal, currencySymbol, currencyPosition);
                            // let orderSubtotal = 0;
                            productRows += `
                                            <tr class="details" style="border-bottom:1px solid #E9ECEF;">
                                               <td style="padding: 10px; vertical-align: top;">
                                                <a href="/product-view/${product.id || ''}" class="d-flex align-items-center text-decoration-none text-dark">
                                                    <img src="${imagePath}" alt="img" class="me-2" style="width:40px; height:40px;">
                                                    ${product.name || 'N/A'}
                                                </a>
                                                </td>
                                                <td style="padding: 10px;vertical-align: top;">${item.quantity}</td>
                                                <td style="padding: 10px;vertical-align: top;" class="text-uppercase">${item.quantity_type?.quantity_type || product.quantity_type?.quantity_type || 'N/A'}</td>
                                                    <td style="padding: 10px;vertical-align: top;">${unitPrice}</td>

                                                    <td style="padding: 10px;vertical-align: top;">${totalAmount}</td>
                                            </tr>`;
                        });
                        $("#product-details").html(productRows);
                    },
                    error: function(xhr) {
                        console.error('Failed to load invoice details', xhr);
                        $("#product-details").html(
                            '<tr><td colspan="5" class="text-center p-4 text-danger">Unable to load invoice details. Please refresh or log in again.</td></tr>'
                        );
                    }
                });
            }
        });
    </script>
@endpush
