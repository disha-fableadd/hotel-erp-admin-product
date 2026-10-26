@extends('layout.app')
@section('title', 'Account Ledger')
@section('content')
    <style>
        /* Desktop: hide details column */
        @media (min-width: 768px) {
            #paymentDetails table thead th.details-column,
            #paymentDetails table tbody td.details-column {
                display: none !important;
            }
        }

        /* Mobile view */
        @media (max-width: 767.98px) {
            #paymentDetails .card {
                margin-bottom: 16px;
                overflow: hidden;
            }

            #paymentDetails .card-body {
                overflow-x: hidden;
            }

            #paymentDetails .table-responsive {
                width: 100%;
                overflow-x: hidden !important;
            }

            #paymentDetails table {
                table-layout: fixed !important;
                width: 100% !important;
                min-width: auto;
                margin-bottom: 0;
            }

            #paymentDetails th,
            #paymentDetails td {
                font-size: 12px;
                padding: 8px;
            }
            
            #paymentDetails table thead th:first-child,
            #paymentDetails table tbody td:first-child {
                word-wrap: break-word;
                white-space: normal;
            }

            /* Hide all except first (Invoice) + Details */
            #paymentDetails table thead th:nth-child(n+3),
            #paymentDetails table tbody td:nth-child(n+3) {
                display: none !important;
            }

            #paymentDetails table thead th.details-column,
            #paymentDetails table tbody td.details-column {
                display: table-cell !important;
                width: 50px;
                text-align: center;
                vertical-align: top;
                padding-top: 15px !important;
            }

            .toggle-details i {
                font-size: 18px;
                color: #ff9f43;
            }

            .mobile-details-card {
                /* background: #f8f9fa;
                border-radius: 6px; */
                padding: 8px;
                margin-top: 6px;
                font-size: 12px;
            }

            .table-name-truncated {
                display: -webkit-box !important;
                -webkit-line-clamp: 2 !important;
                -webkit-box-orient: vertical !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                word-break: break-word !important;
                white-space: normal !important;
                line-height: 1.4;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Account Ledger</h4>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <!-- View PDF & Excel buttons -->
                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-sm me-2 text-white btn-danger" id="viewPdfBtn">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-sm text-white btn-success" id="viewCsvBtn">
                        <i class="fas fa-file-csv"></i> Export Excel
                    </button>
                </div>
                <form id="appointmentForm">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="customer">Customer</option>
                                    <option value="vendor">Vendor</option>
                                </select>
                            </div>
                        </div>

                        <!-- Customer Dropdown -->
                        <div class="col-lg-3 col-sm-6 col-6" id="customerDropdown" style="display: none;">
                            <div class="form-group">
                                <label>Customer Name</label>
                                <select name="customer_id" id="customer_id" class="form-control select2 Name">
                                    <option value="">Select Customer</option>
                                    <option value="all">All Customers</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id ?? 'null' }}">
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-customer_id"></span>
                            </div>
                        </div>

                        <!-- Vendor Dropdown -->
                        <div class="col-lg-3 col-sm-6 col-6" id="vendorDropdown" style="display: none;">
                            <div class="form-group">
                                <label>Vendor Name</label>
                                <select name="vendor_id" id="vendor_id" class="form-control select2 Name">
                                    <option value="">Select Vendor</option>
                                    <option value="all">All Vendors</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id ?? 'null' }}">
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-vendor_id"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Month</label>
                                <select name="month" id="month" class="form-control select2 Name">
                                    <option value="">Select Month</option>
                                    <option value="all">All Months</option> {{-- ✅ Added --}}
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}">
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                                <span class="text-danger error-month"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Year</label>
                                <select name="year" id="year" class="form-control select2 Name">
                                    <option value="">Select Year</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-year"></span>
                            </div>
                        </div>
                        {{-- <div class="col-lg-2 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Vehicle Number</label>
                                <input type="text" name="vehicle_number" id="vehicle_number" class="form-control">
                                <span class="text-danger error-vehicle_number"></span>
                            </div>
                        </div> --}}
                    </div>
                </form>
                <div id="paymentDetails">
                    <!-- Payment details will be displayed here -->
                </div>
            </div>
        </div>
        
        <!-- Products Modal -->
        <div class="modal fade" id="productsModal" tabindex="-1" aria-labelledby="productsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productsModalLabel">Products</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="modalProductsBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const typeSelect = document.getElementById("type");
            const customerDropdown = document.getElementById("customerDropdown");
            const vendorDropdown = document.getElementById("vendorDropdown");

            typeSelect.addEventListener("change", function() {
                if (this.value === "customer") {
                    customerDropdown.style.display = "block";
                    vendorDropdown.style.display = "none";
                } else if (this.value === "vendor") {
                    vendorDropdown.style.display = "block";
                    customerDropdown.style.display = "none";
                } else {
                    customerDropdown.style.display = "none";
                    vendorDropdown.style.display = "none";
                }
            });

        });
        $(document).ready(function() {
            $('.Name').select2({
                placeholder: 'Select an option',
                width: '100%'
            });

            // Trigger fetch when any filter is changed
            $('#customer_id, #vendor_id, #month, #year').on('change', fetchPayments);
            let debounceTimer;

            // $('#vehicle_number').on('input', function() {
            //     clearTimeout(debounceTimer);
            //     debounceTimer = setTimeout(fetchPayments, 500); // Debounce typing
            // });

            $('#customer_id, #vendor_id').on('change', function() {
                const selectedOption = $(this).find(':selected');
                const subbranchId = selectedOption.data('subbranch-id');

                if (subbranchId) {
                    localStorage.setItem("selectedSubBranchId", subbranchId);
                } else {
                    localStorage.removeItem("selectedSubBranchId");
                }
            });

            function fetchPayments() {
                var authToken = localStorage.getItem("authToken");
                var type = $('#type').val(); // customer | vendor
                var selectedId = type === 'customer' ? $('#customer_id').val() : $('#vendor_id').val();
                var month = $('#month').val();
                var year = $('#year').val();
                var selectedId = type === 'customer' ? $('#customer_id').val() : $('#vendor_id').val();
                if (!selectedId) {
                    $('#paymentDetails').html('<div class="text-danger">Please select an option.</div>');
                    return;
                }

                $.ajax({
                    url: '/api/get-payment-details',
                    type: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    data: {
                        type: type, // send this!
                        customer_id: type === 'customer' ? $('#customer_id').val() : null,
                        vendor_id: type === 'vendor' ? $('#vendor_id').val() : null,
                        month: month,
                        year: year,
                        // vehicle_number: vehicleNumber,
                        selectedSubAdminId: localStorage.getItem("selectedSubAdminId")
                    },
                    success: function(response) {
                        displayPaymentDetails(response);
                    },
                    error: function(xhr) {
                        $('#paymentDetails').html(
                            '<div class="text-danger">Failed to fetch payment details.</div>');
                    }
                });
            }

            window.currentCurrency = '₹';
            window.currentCurrencyPosition = 'left';

            function displayPaymentDetails(data) {
                const currency = data.settings.currency_symbol || '₹';
                const currencyPosition = data.settings.currency_position || 'left';
                window.currentCurrency = currency;
                window.currentCurrencyPosition = currencyPosition;

                function formatCurrency(amount) {
                    return currencyPosition === 'left' ? `${currency}${amount}` : `${amount}${currency}`;
                }

                function formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    }) + ' ' + date.toLocaleTimeString('en-GB', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                function capitalize(str) {
                    if (!str) return '-';
                    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
                }

                // ✅ Common product name extractor modified to return a button
                function getProductNames(order) {
                    let items = [];
                    if (order.items && order.items.length > 0) {
                        items = order.items.map(i => ({
                            name: i.product?.name || '-',
                            qty: i.quantity || i.qty || 1,
                            price: i.unit_price || i.price || 0,
                            total: i.total_price || i.total || 0
                        }));
                    } else if (order.products_with_names && order.products_with_names.length > 0) {
                        items = order.products_with_names.map(p => ({
                            name: p.product_name || '-',
                            qty: p.quantity || p.qty || 1,
                            price: p.price || p.unit_price || 0,
                            total: p.total || p.total_price || 0
                        }));
                    }
                    if (items.length === 0) return '-';
                    
                    const orderRef = order.order_number || order.invoice_number || order.id || '-';
                    const itemsJson = encodeURIComponent(JSON.stringify(items));
                    return `<button type="button" class="btn btn-sm  view-products-btn" data-items="${itemsJson}" data-order-ref="${orderRef}" style="border-color: #e87b1e; color: #1b2850; font-weight: 500;">View Products (${items.length})</button>`;
                }

                let html = '';

                // === Paid Payments ===
                html += `
    <div class="card shadow mb-4">
        <div class="card-header text-dark">
            <h5 class="mb-0 fw-bold">Paid Payments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Invoice No.</th>
                            <th class="details-column">Details</th>
                            <th>Product(s)</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Payment Date</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>`;
                let totalPaid = 0;
                if (data.paidPayments.length > 0) {
                    data.paidPayments.forEach(function(payment) {
                        const order = payment.order || payment;
                        const productNames = getProductNames(order);
                        const uniqueId = payment.id || order.id || Math.random().toString(36).substr(2, 9);
                        const invoiceNo = order.order_number || order.invoice_number || '-';
                        const totalAmt = formatCurrency(order.total_amount || order.grand_total || 0);
                        const paidAmt = formatCurrency(payment.total_amount || order.amount_total || 0);
                        const payDate = payment.payment_date || order.created_at || '-';
                        const payMethod = capitalize(payment.payment_method || order.payment_status || '-');

                        html += `
            <tr>
                <td>
                    <span class="table-name-truncated">${invoiceNo}</span>
                    <!-- Collapsible Details (visible only on mobile) -->
                    <div class="collapse d-md-none mt-2" id="details-paid-${uniqueId}">
                        <div class="mobile-details-card">
                            <p class="mb-1"><strong>Product(s):</strong> ${productNames}</p>
                            <p class="mb-1"><strong>Total Amount:</strong> ${totalAmt}</p>
                            <p class="mb-1"><strong>Paid Amount:</strong> ${paidAmt}</p>
                            <p class="mb-1"><strong>Payment Date:</strong> ${payDate}</p>
                            <p class="mb-1"><strong>Payment Method:</strong> ${payMethod}</p>
                        </div>
                    </div>
                </td>
                <td class="details-column">
                    <a href="#details-paid-${uniqueId}" class="toggle-details" data-bs-toggle="collapse">
                        <i class="fas fa-plus-circle"></i>
                    </a>
                </td>
                <td>${productNames}</td>
                <td>${totalAmt}</td>
                <td>${paidAmt}</td>
                <td>${payDate}</td>
                <td>${payMethod}</td>
            </tr>`;
                        totalPaid += parseFloat(payment.total_amount || order.amount_total || 0);
                    });
                } else {
                    html += `<tr><td colspan="6" class="text-center text-muted">No record available</td></tr>`;
                }
                html += `
    </tbody>
    </table>
    </div>
    <div class="d-flex justify-content-end pe-3 mt-2 mb-2">
        <div style="border: 1px solid #1b2850; border-radius: 5px; padding: 4px 10px; display: inline-block;">
            <span style="font-weight: bold; font-size: 15px; color: #1b2850;">
                Total Paid:
            </span>
            <span style="font-weight: bold; font-size: 15px; color: orange;">
                ${formatCurrency(totalPaid.toFixed(2))}
            </span>
        </div>
    </div>
    </div>`;

                // === Pending Payments ===
                html += `
    <div class="card shadow mb-4">
        <div class="card-header text-dark">
            <h5 class="mb-0 fw-bold">Pending Payments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Invoice No.</th>
                            <th class="details-column">Details</th>
                            <th>Product(s)</th>
                            <th>Total Amount</th>
                            <th>Remaining Amount</th>
                        </tr>
                    </thead>
                    <tbody>`;
                let totalPending = 0;
                if (data.pendingPayments.length > 0) {
                    data.pendingPayments.forEach(function(payment) {
                        const order = payment.order || payment;
                        const productNames = getProductNames(order);
                        const uniqueId = payment.id || order.id || Math.random().toString(36).substr(2, 9);
                        const invoiceNo = order.order_number || order.invoice_number || payment.invoice_id || '-';
                        const totalAmt = formatCurrency(order.total_amount || order.grand_total || 0);
                        const remainAmt = formatCurrency(payment.amount_total || order.total_amount || 0);

                        html += `
            <tr>
                <td>
                    <span class="table-name-truncated">${invoiceNo}</span>
                    <div class="collapse d-md-none mt-2" id="details-pending-${uniqueId}">
                        <div class="mobile-details-card">
                            <p class="mb-1"><strong>Product(s):</strong> ${productNames}</p>
                            <p class="mb-1"><strong>Total Amount:</strong> ${totalAmt}</p>
                            <p class="mb-1"><strong>Remaining Amount:</strong> ${remainAmt}</p>
                        </div>
                    </div>
                </td>
                <td class="details-column">
                    <a href="#details-pending-${uniqueId}" class="toggle-details" data-bs-toggle="collapse">
                        <i class="fas fa-plus-circle"></i>
                    </a>
                </td>
                <td>${productNames}</td>
                <td>${totalAmt}</td>
                <td>${remainAmt}</td>
            </tr>`;
                        totalPending += parseFloat(payment.amount_total || order.amount_total || 0);
                    });
                } else {
                    html += `<tr><td colspan="4" class="text-center text-muted">No record available</td></tr>`;
                }
                html += `
    </tbody>
    </table>
    </div>
    <div class="d-flex justify-content-end pe-3 mt-2 mb-2">
        <div style="border: 1px solid #1b2850; border-radius: 5px; padding: 4px 10px; display: inline-block;">
            <span style="font-weight: bold; font-size: 15px; color: #1b2850;">
                Total Pending:
            </span>
            <span style="font-weight: bold; font-size: 15px; color: orange;">
                ${formatCurrency(totalPending.toFixed(2))}
            </span>
        </div>
    </div>
    </div>`;

                $('#paymentDetails').html(html);
            }

            $(document).on('click', '.view-products-btn', function() {
                const itemsStr = $(this).attr('data-items');
                const orderRef = $(this).attr('data-order-ref');
                const items = JSON.parse(decodeURIComponent(itemsStr));
                
                $('#productsModalLabel').text('Products - ' + orderRef);
                
                let html = '';
                items.forEach((item, index) => {
                    const itemQty = parseFloat(item.qty) || 0;
                    const itemPrice = parseFloat(item.price) || 0;
                    const itemTotal = (itemQty * itemPrice);

                    const priceFormatted = window.currentCurrencyPosition === 'left' ? `${window.currentCurrency}${itemPrice.toFixed(2)}` : `${itemPrice.toFixed(2)}${window.currentCurrency}`;
                    const totalFormatted = window.currentCurrencyPosition === 'left' ? `${window.currentCurrency}${itemTotal.toFixed(2)}` : `${itemTotal.toFixed(2)}${window.currentCurrency}`;
                    html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.name}</td>
                        <td>${itemQty.toFixed(2)}</td>
                        <td>${priceFormatted}</td>
                        <td>${totalFormatted}</td>
                    </tr>`;
                });
                
                $('#modalProductsBody').html(html);
                $('#productsModal').modal('show');
            });
            
            $(document).on('click', '.toggle-details', function () {
                let icon = $(this).find('i');
                if (icon.hasClass('fa-plus-circle')) {
                    icon.removeClass('fa-plus-circle')
                        .addClass('fa-minus-circle')
                        .css('color', 'red');
                } else {
                    icon.removeClass('fa-minus-circle')
                        .addClass('fa-plus-circle')
                        .css('color', '#ff9f43');
                }
            });

            $('#viewCsvBtn').on('click', function() {
                const type = $('#type').val();
                const customer_id = $('#customer_id').val();
                const vendor_id = $('#vendor_id').val();
                const month = $('#month').val();
                const year = $('#year').val();
                // const vehicleNumber = $('#vehicle_number').val().trim();

                if (!type) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Type',
                        text: 'Please select a type before exporting.',
                    });
                    return;
                }

                if (type === 'customer') {
                    const selectedValue = $('#customer_id').val();
                    if (!selectedValue) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Customer',
                            text: 'Please select a customer before exporting.',
                        });
                        return;
                    }
                    userId = selectedValue === "all" ? "all" : selectedValue.split('|')[0];
                } else if (type === 'vendor') {
                    const selectedValue = $('#vendor_id').val();
                    if (!selectedValue) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Vendor',
                            text: 'Please select a vendor before exporting.',
                        });
                        return;
                    }
                    userId = selectedValue === "all" ? "all" : selectedValue.split('|')[0];
                }

                // const parts = selectedValue.split('|');
                // const userId = parts[0] !== 'null' ? parts[0] : '';
                // const customerName = parts[1] || '';

                const authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                Swal.fire({
                    title: 'Generating Excel...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '/api/export-account-ledger',
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + authToken
                    },  
                    data: {
                        type: type,
                        customer_id: customer_id,
                        vendor_id: vendor_id,
                        month: month,
                        year: year,
                        selectedSubAdminId: selectedSubAdminId,
                    },
                    success: function(res) {
                        Swal.close(); // ✅ close the loader
                        if (res.status) {
                            const a = document.createElement('a');
                            a.href = res.file_url;
                            a.download = res.file_name;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Failed to generate Excel file.', 'error');
                    }
                });

            });


            $('#viewPdfBtn').on('click', function() {
                const type = $('#type').val();
                const customer_id = $('#customer_id').val();
                const vendor_id = $('#vendor_id').val();
                const month = $('#month').val();
                const year = $('#year').val();
                const authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                if (!type) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Type',
                        text: 'Please select a type before exporting.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Generating PDF...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '/api/account-ledger/download-pdf',
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        type: type,
                        customer_id: customer_id,
                        vendor_id: vendor_id,
                        month: month,
                        year: year,
                        selectedSubAdminId: selectedSubAdminId,
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status && response.file_url) {
                            // ✅ Create temporary link to trigger download
                            const link = document.createElement('a');
                            link.href = response.file_url;
                            link.download = response.file_name || 'account_ledger.pdf';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Download Failed',
                                text: 'File URL not found in response.'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Download Failed',
                            text: 'Failed to generate PDF.'
                        });
                    }
                });
            });

        });
    </script>
@endpush
