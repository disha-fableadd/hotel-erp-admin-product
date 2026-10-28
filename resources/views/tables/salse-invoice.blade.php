@extends('layout.app')

@section('title', 'Sales Invoice')

@section('content')
    <style>
        .logo_img {
            max-width: 150px;
            height: auto;
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
                <h4>Sale Invoice</h4>

            </div>

            <div class="page-btn d-flex">

                <a href="{{ route('sales.list') }}" class="btn btn-added mx-3">
                    Back
                </a>
            </div>
        </div>


        <div class="card">
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

                                // ✅ Discounted subtotal
                                $afterDiscount = $subtotal - $discountAmount;

                                $taxDetails = [];
                                $totalTaxAmount = 0;

                                // ✅ Apply tax on discounted subtotal
                                foreach ($taxes as $tax) {
                                    $taxAmount = ($afterDiscount * $tax->tax_rate) / 100;
                                    $taxDetails[] = [
                                        'name' => $tax->tax_name,
                                        'rate' => $tax->tax_rate,
                                        'amount' => $taxAmount,
                                    ];
                                    $totalTaxAmount += $taxAmount;
                                }

                                // ✅ Final total
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
                                            <h5>{{ formatCurrency($subtotal, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }}
                                            </h5>
                                        </li>

                                        <li>
                                            <h4>Discount</h4>
                                            <h5>{{ $discountPercentage }}%
                                                ({{ formatCurrency($discountAmount, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }})
                                            </h5>
                                        </li>

                                        <li>
                                            <h4>After Discount</h4>
                                            <h5>{{ formatCurrency($afterDiscount, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }}
                                            </h5>
                                        </li>

                                        @foreach ($taxDetails as $tax)
                                            <li>
                                                <h4>{{ $tax['name'] }} Tax</h4>
                                                <h5>{{ $tax['rate'] }}%
                                                    ({{ formatCurrency($tax['amount'], $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }})
                                                </h5>
                                            </li>
                                        @endforeach

                                        <li class="total">
                                            <h4>Total</h4>
                                            <h5>{{ formatCurrency($finalTotal, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }}
                                            </h5>
                                        </li>

                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 100px;">
                        <h2 style="color: #7367F0; font-size: 24px; margin-bottom: 10px;">Thank You for Your Purchase!</h2>
                        <p style="font-size: 16px; color: #555;">We appreciate your business and hope to serve you again
                            soon.</p>
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

        function formatCurrency(amount, symbol = '₹', position = 'left') {
            amount = parseFloat(amount).toFixed(2);
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
                        selectedSubAdminId: selectedSubAdminId // ✅ fixed object syntax
                    },
                    dataType: "json",
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



                        if (response.error) {
                            console.error(response.error);
                            return;
                        }
                        // console.log(response.company_info);

                        const sale = response.sales;
                        const company_info = response.company_info;

                        // Update customer and invoice info
                        $(".customer-name").text(sale.user_name || "walk-in-customer");
                        $(".customer-phone").text(sale.user_phone || "N/A");
                        $(".invoice-id").text(sale.order_number);
                        $(".payment-status").text(sale.payment_status.charAt(0).toUpperCase() + sale
                            .payment_status.slice(1));
                        $(".order-status").text("Completed"); // or dynamically from status if available

                        const items = sale.order_items;
                        let productRows = `
                                                <tr class="top">
                                                    <td colspan="6" style="padding: 5px;vertical-align: top;">
                                                        <table style="width: 100%;line-height: inherit;text-align: left;" class="product-list">
                                                        <div class="row">
                                                                <div class="col-6">
                                                                    <img
                                            src="{{ ($setting?->logo ?? null) ? tenant_image_url($setting->logo, image_path('admin/assets/img/logo.png')) : image_path('admin/assets/img/logo.png') }}"
                                            alt="logo"
                                            class="logo_img"
                                            onerror="this.onerror=null; this.src='{{ image_path('admin/assets/img/logo.png') }}';">
                                                                </div>
                                                                <div class="col-6 mt-4" style=" text-align: end; ">
                                                                    <h1>Invoice</h1>
                                                                    <h4 class="mt-3">#${sale.order_number}</h4>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                        <tbody>
                                                                <tr>
                                                                   <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                                    <font style="vertical-align: inherit; margin-bottom:25px;">
                                                                        <font style="vertical-align: inherit; font-size:14px; color:#7367F0; font-weight:600; line-height:35px;">
                                                                            Customer Info
                                                                        </font>
                                                                    </font><br>

                                                                    <!-- Name (with fallback) -->
                                                                    <font style="vertical-align: inherit;">
                                                                        <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-name">
                                                                            ${sale.user_name || "walk-in-customer"}
                                                                        </font>
                                                                    </font><br>

                                                                    <!-- Email (show only if exists) -->
                                                                    ${sale.user_email ? `
                                                                                <font style="vertical-align: inherit;">
                                                                                    <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-email">
                                                                                        ${sale.user_email}
                                                                                    </font>
                                                                                </font><br>` : ''}

                                                                    <!-- Address (show only if exists) -->
                                                                    ${userAddress ? `
                                                                                <font style="vertical-align: inherit;">
                                                                                    <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-address">
                                                                                        ${userAddress}
                                                                                    </font>
                                                                                </font><br>` : ''
                                                                    }

                                                                    <!-- Phone (show only if exists) -->
                                                                    ${sale.user_phone ? `
                                                                                <font style="vertical-align: inherit;">
                                                                                    <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-phone">
                                                                                        ${sale.user_phone}
                                                                                    </font>
                                                                                </font><br>` : ''}


                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-phone">
                                                                              <strong>GST No :</strong>  ${sale.user?.gst_number ?? '--'}
                                                                            </font>
                                                                        </font><br>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-phone">
                                                                              <strong>PAN No :</strong>  ${sale.user?.pan_number ?? '--'}
                                                                            </font>
                                                                        </font><br>
                                                                </td>

                                                                   <td style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px">
                                                                    <font style="vertical-align: inherit; margin-bottom:25px;">
                                                                        <font style="vertical-align: inherit; font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">
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
                                                                            <font style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">Order Info</font>
                                                                        </font><br>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;"> Order Number  </font>
                                                                        </font><br>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;"> Order Date  </font>
                                                                        </font><br>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;"> Payment Status</font>
                                                                        </font><br>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;">Payment Method</font>
                                                                        </font><br>
                                                                    </td>
                                                                    <td style="padding:5px;vertical-align:top;text-align:right;padding-bottom:20px">
                                                                        <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                            <font style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px; ">&nbsp;</font>
                                                                        </font><br>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="invoice-id">#${sale.order_number}</font>
                                                                        </font><br>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;font-weight: 400;" class="invoice-id">${sale.created_at}</font>
                                                                        </font><br>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="vertical-align: inherit;font-size: 14px;color:#2E7D32;font-weight: 400;" class="payment-status">${sale.payment_status.charAt(0).toUpperCase() + sale.payment_status.slice(1)}</font>
                                                                        </font><br>
                                                                        <font style="vertical-align: inherit;">
                                                                            <font style="text-transform: capitalize; vertical-align: inherit;font-size: 14px;color:#2E7D32;font-weight: 400;" class="order-method">${sale.payment_method}</font>
                                                                        </font><br>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr class="heading " style="background: #F3F2F7;">
                                                    <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; ">
                                                        Product Name
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
                            const product = item.product;
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
                            const currencySymbol = response.currency_symbol || company_info.currency_symbol || '{{ $setting->currency_symbol ?? '₹' }}';
                            const currencyPosition = response.currency_position || company_info.currency_position || '{{ $setting->currency_position ?? 'left' }}';
                            const unitPrice = formatCurrency(item.price, currencySymbol, currencyPosition);
                            const lineTotal = item.total_amount ?? (parseFloat(item.price) * parseFloat(item.quantity));
                            const totalAmount = formatCurrency(lineTotal, currencySymbol, currencyPosition);
                            // let orderSubtotal = 0;
                            productRows += `
                                            <tr class="details" style="border-bottom:1px solid #E9ECEF;">
                                               <td style="padding: 10px; vertical-align: top;">
                                                <a href="/product-view/${product.id}" class="d-flex align-items-center text-decoration-none text-dark">
                                                    <img src="${imagePath}" alt="${product.name || 'product'}" class="me-2" style="width:40px; height:40px; object-fit: cover; border-radius: 4px;" onerror="this.onerror=null; this.src='${defaultProductImage}';">
                                                    ${product.name}
                                                </a>
                                                </td>
                                                <td style="padding: 10px;vertical-align: top;">${item.quantity}</td>
                                                <td style="padding: 10px;vertical-align: top;" class="text-uppercase">${item.product.quantity_type}</td>
                                                    <td style="padding: 10px;vertical-align: top;">${unitPrice}</td>

                                                    <td style="padding: 10px;vertical-align: top;">${totalAmount}</td>
                                            </tr>`;
                        });
                        $("#product-details").html(productRows);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching sales details:", error);
                    }
                });
            }
        });
    </script>
@endpush
