@extends('layout.app')

@section('title', 'Cashbook')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        .nav-tabs-solid .nav-link {
            background-color: transparent !important;
            color: #6c757d !important;
        }

        .nav-tabs-solid .nav-link.active {
            background-color: #ff9b44 !important;
            border-color: #ff9b44 !important;
            color: #fff !important;
        }

        .form-control,
        .select2-container .select2-selection--single {
            height: 30px !important;
            font-size: 13px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 28px !important;
        }

        .form-group label {
            font-size: 13px !important;
            margin-bottom: 2px !important;
        }

        .btn-md {
            height: 30px !important;
            padding: 0 10px !important;
            font-size: 13px !important;
        }

        .cashbook-mobile-party {
            display: none;
        }

        /* Responsive Table Styling */
        @media screen and (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch !important;
            }
        }

        @media (min-width: 769px) {

            .datatable-cashbook thead th.details-column,
            .datatable-cashbook tbody td.details-control,
            .datatable-cashbook thead th:nth-child(5),
            .datatable-cashbook tbody td:nth-child(5) {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .datatable-cashbook {
                width: 100% !important;
                table-layout: fixed;
            }

            .datatable-cashbook thead th:nth-child(2),
            .datatable-cashbook tbody td:nth-child(2),
            .datatable-cashbook thead th:nth-child(3),
            .datatable-cashbook tbody td:nth-child(3),
            .datatable-cashbook thead th:nth-child(4),
            .datatable-cashbook tbody td:nth-child(4),
            .datatable-cashbook thead th:nth-child(6),
            .datatable-cashbook tbody td:nth-child(6) {
                display: none !important;
            }

            .datatable-cashbook thead th:first-child,
            .datatable-cashbook tbody td:first-child {
                display: table-cell !important;
            }

            .datatable-cashbook thead th:nth-child(5),
            .datatable-cashbook tbody td:nth-child(5) {
                display: table-cell !important;
                text-align: center;
                vertical-align: top !important;
                width: 56px !important;
                min-width: 56px !important;
                max-width: 56px !important;
                padding: 12px 6px !important;
            }

            .toggle-details {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                margin-left: auto;
            }

            .datatable-cashbook tbody td:first-child {
                width: calc(100% - 56px) !important;
                max-width: calc(100vw - 96px) !important;
                vertical-align: top !important;
            }

            .datatable-cashbook tbody td:first-child>div {
                width: 100%;
            }

            .cashbook-mobile-ref {
                display: block;
                font-weight: 500;
            }

            .cashbook-mobile-party {
                display: block;
                margin-top: 4px;
                font-size: 14px;
                line-height: 1.4;
                color: #495057;
                font-weight: 500;
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
            }
        }

        .toggle-details i {
            font-size: 18px;
            color: #ff9f43;
            cursor: pointer;
        }

        /* .collapse-details {
                                margin-top: 10px;
                                padding: 10px;
                                background-color: #f8f9fa;
                                border-radius: 5px;
                                border-left: 3px solid #ff9b44;
                            } */

        .detail-item {
            display: flex;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .detail-label {
            font-weight: 600;
            min-width: 100px;
            color: #495057;
        }

        .detail-value {
            color: #212529;
            flex: 1;
        }

        /* Particulars column proper word wrapping */
        .datatable-cashbook th:nth-child(3),
        .datatable-cashbook td:nth-child(3) {
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.4;
            max-width: 260px;
        }

        /* Custom Pagination Styling */
        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            color: #fff;
            border: none;
            margin: 0 3px;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: bold;
        }

        @media (max-width: 576px) {
            .pagination .page-item .page-link {
                padding: 4px 10px;
                margin: 0 3px;
                font-size: 12px;
            }
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            background-color: #fff;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .pagination .page-item:first-child .page-link:hover,
        .pagination .page-item:last-child .page-link:hover {
            background-color: #f8f9fa;
            color: #495057;
            border-color: #dee2e6;
        }

        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            color: #fff;
        }

        .pagination .page-item .page-link:hover {
            background-color: #4a5766;
            color: #fff;
        }

        .pagination .page-item.active .page-link:hover {
            background-color: #e68a35 !important;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #fff !important;
            color: #dee2e6 !important;
            border: 1px solid #dee2e6 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        @media (max-width: 767px) {
            .pagination-controls {
                align-items: stretch !important;
                gap: 12px;
            }

            .pagination-controls>div,
            .pagination-controls nav {
                width: 100%;
            }

            .pagination-controls>div {
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 8px;
            }

            .pagination-controls nav .pagination {
                justify-content: center;
                flex-wrap: wrap;
            }
        }

        .me-1 {
            margin-right: 1.25rem !important;
        }

        .download-loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 1060;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .download-loader-overlay.d-none {
            display: none !important;
        }

        .download-loader-box {
            width: min(460px, 100%);
            background: #fff;
            border-radius: 8px;
            padding: 24px 20px;
            text-align: center;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2);
        }

        .download-loader-box h4 {
            margin: 0 0 18px 0;
            font-size: 34px;
            color: #2c3e50;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            #cashbookTable td.details-control {
                vertical-align: top !important;
            }

            .download-loader-box h4 {
                font-size: 28px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Cashbook</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-solid mb-3">
                    <li class="nav-item"><a class="nav-link active" href="#receipts" data-bs-toggle="tab"
                            data-status="credit">Receipts</a></li>
                    <li class="nav-item"><a class="nav-link" href="#payments" data-bs-toggle="tab"
                            data-status="debit">Payments</a></li>
                    <li class="nav-item"><a class="nav-link" href="#expenses" data-bs-toggle="tab"
                            data-status="expense">Expenses</a></li>
                </ul>

                <div class="row ">
                    <div class="col-lg-2 col-md-3 col-6">
                        <div class="form-group">
                            <label style="font-weight: bold !important;">From Date</label>
                            <input type="date" id="from_date" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <div class="form-group">
                            <label style="font-weight: bold !important;">To Date</label>
                            <input type="date" id="to_date" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <div class="form-group">
                            <label style="font-weight: bold !important;">Year</label>
                            <select id="year" class="form-control select2">
                                <option value="">Select Year</option>
                                @for ($i = date('Y'); $i >= 2020; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 d-none">
                        <div class="form-group">
                            <label style="font-weight: bold !important;">Status</label>
                            <select id="status" class="form-control select2">
                                <option value="credit">Credit</option>
                                <option value="debit">Debit</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <div class="form-group">
                            <label style="font-weight: bold !important;">Total</label>
                            <input type="text" id="total" class="form-control bg-light fw-bold" placeholder="Total Amount"
                                readonly style="border:1px solid #dcdcdc; color:#ff9f43;">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="form-group">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <div class="row g-1">
                                <div class="col-6">
                                    <button id="customExportExcel" class="btn btn-success btn-md w-100">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button id="customExportPdf" class="btn btn-danger btn-md w-100">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-12 d-flex flex-column  mb-2">
                    <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Search...">
                </div>

                <div class="table-responsive">
                    {{-- <table class="table datatable-cashbook"> --}}
                        <table class="table datatable-cashbook" id="cashbookTable">
                            <thead>
                                <tr>
                                    <th id="refColumnTitle">Order No</th>
                                    <th>Date</th>
                                    <th>Particulars</th>
                                    <th>Amount</th>
                                    <th class="details-column">Details</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                </div>


                <!-- Pagination controls (initially hidden) -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3"
                    style="display: none;">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="per-page-select" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="pagination-from">0</span> - <span id="pagination-to">0</span> of <span
                                id="pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-numbers">
                            <!-- page numbers inserted here -->
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    <div id="downloadLoaderOverlay" class="download-loader-overlay d-none" aria-live="polite" aria-busy="true">
        <div class="download-loader-box">
            <h4 id="downloadLoaderText">Generating PDF...</h4>
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        </div>
    </div>

    {{-- Edit Payment Modal --}}
    <div class="modal fade" id="cashbookEditModal" tabindex="-1" aria-labelledby="cashbookEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background:#ff9f43;">
                    <h5 class="modal-title text-white" id="cashbookEditLabel"><i class="fas fa-edit me-2"></i>Edit Payment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cb_edit_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" id="cb_edit_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (₹)</label>
                        <input type="number" id="cb_edit_amount" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea id="cb_edit_remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="cb_save_edit_btn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    {{--
    <script>
        $(document).ready(function () {
            let authToken = localStorage.getItem("authToken");
            let selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const $downloadLoader = $("#downloadLoaderOverlay");
            const $downloadLoaderText = $("#downloadLoaderText");
            const $exportButtons = $("#customExportExcel, #customExportPdf");

            function toggleDownloadLoader(isLoading, message) {
                if (isLoading) {
                    $downloadLoaderText.text(message || "Generating report...");
                    $downloadLoader.removeClass("d-none");
                    $exportButtons.prop("disabled", true).addClass("disabled").attr("aria-disabled", "true");
                } else {
                    $downloadLoader.addClass("d-none");
                    $exportButtons.prop("disabled", false).removeClass("disabled").removeAttr("aria-disabled");
                }
            }

            // Initialize select2
            // $('.select2').select2({
            //     width: '100%'
            // });

            let table = $('.datatable-cashbook').DataTable({
                order: [
                    [1, "desc"]
                ],
                pageLength: 10,
                destroy: true,
                dom: 'lfrtip',
                autoWidth: false,
                buttons: [{
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                }
                ],
                columnDefs: [{
                    targets: 4,
                    className: 'details-control',
                    orderable: false
                }]
            });

            function indianCurrency(amount) {
                return Number(amount).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            $('#customExportExcel').on('click', function () {
                table.button('.buttons-excel').trigger();
            });

            $('#customExportPdf').on('click', function () {
                let data = {
                    selectedSubAdminId: selectedSubAdminId,
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    year: $('#year').val(),
                    status: $('#status').val(),
                };

                $.ajax({
                    url: "{{ url('/api/export-cashbook-pdf') }}",
                    type: "GET",
                    data: data,
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function (response) {
                        if (response.success || response.status) {
                            window.open(response.file_url, '_blank');
                        }
                    },
                    error: function (xhr) {
                        // console.error(xhr.responseText);
                    }
                });
            });

            // Tab switching logic
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                let status = $(e.target).data('status');
                $('#status').val(status).trigger('change');

                if (status === 'debit') {
                    $('#refColumnTitle').text('Bill No');
                } else if (status === 'expense') {
                    $('#refColumnTitle').text('Expense No');
                } else {
                    $('#refColumnTitle').text('Order No');
                }

                fetchCashbook();
            });

            fetchCashbook();

            $('#from_date, #to_date, #year').on('change', function () {
                fetchCashbook();
            });

            // Toggle details icon
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

            function fetchCashbook() {
                let data = {
                    selectedSubAdminId: selectedSubAdminId,
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    year: $('#year').val(),
                    status: $('#status').val(),
                };

                $.ajax({
                    url: "{{ url('/api/cashbook/data') }}",
                    type: "GET",
                    data: data,
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function (response) {
                        if (response.status) {
                            let rows = [];
                            let totalAmount = 0;

                            // Sort data by date descending
                            response.data.sort((a, b) => new Date(b.payment_date) - new Date(a
                                .payment_date));

                            response.data.forEach((item, index) => {
                                let amount = parseFloat(item.payment_amount) || 0;
                                totalAmount += amount;

                                let detailsId = `details-${index}`;

                                rows.push([
                                    `<div>
                                                            <span>${item.order_number || '-'}</span>
                                                            <div class="collapse mt-2 d-lg-none" id="${detailsId}">
                                                                <div class="collapse-details">
                                                                    <div class="detail-item">
                                                                        <span class="detail-label">Date:</span>
                                                                        <span class="detail-value">${item.payment_date}</span>
                                                                    </div>
                                                                    <div class="detail-item">
                                                                        <span class="detail-label">Particulars:</span>
                                                                        <span class="detail-value">${item.user_name || 'N/A'}</span>
                                                                    </div>
                                                                    <div class="detail-item">
                                                                        <span class="detail-label">Amount:</span>
                                                                        <span class="detail-value">₹${indianCurrency(amount)}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>`,
                                    item.payment_date,
                                    item.user_name || 'N/A',
                                    `₹${indianCurrency(amount)}`,
                                    `<a href="#${detailsId}" class="toggle-details" data-bs-toggle="collapse">
                                                            <i class="fas fa-plus-circle"></i>
                                                        </a>`
                                ]);
                            });
                            table.clear().rows.add(rows).draw();
                            // $('#total').val('₹' + totalAmount.toFixed(2));
                            $('#total').val('₹' + indianCurrency(totalAmount));
                        } else {
                            table.clear().draw();
                            $('#total').val('₹0.00');
                        }
                    },
                    error: function (xhr) {
                        // console.error(xhr.responseText);
                        table.clear().draw();
                    }
                });
            }
        });
    </script> --}}
    <script>
        $(document).ready(function () {
            let authToken = localStorage.getItem("authToken");
            let selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const $downloadLoader = $("#downloadLoaderOverlay");
            const $downloadLoaderText = $("#downloadLoaderText");
            const $exportButtons = $("#customExportExcel, #customExportPdf");

            function toggleDownloadLoader(isLoading, message) {
                if (isLoading) {
                    $downloadLoaderText.text(message || "Generating report...");
                    $downloadLoader.removeClass("d-none");
                    $exportButtons.prop("disabled", true).addClass("disabled").attr("aria-disabled", "true");
                } else {
                    $downloadLoader.addClass("d-none");
                    $exportButtons.prop("disabled", false).removeClass("disabled").removeAttr("aria-disabled");
                }
            }

            // State variables
            let currentStatus = 'credit'; // from active tab
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';

            // Helper to format Indian currency
            function indianCurrency(amount) {
                return Number(amount).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Initialize DataTable as a display-only table (no paging/search)
            let table = $('#cashbookTable').DataTable({
                destroy: true,
                ordering: false,
                searching: false,
                paging: false,
                info: false,
                lengthChange: false,
                autoWidth: false,
                dom: 't',
                columnDefs: [
                    { targets: 4, className: 'details-control', orderable: false },
                    { targets: 5, orderable: false, className: 'text-center' }
                ]
            });

            // Tab switching
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                currentStatus = $(e.target).data('status');
                $('#status').val(currentStatus).trigger('change');
                if (currentStatus === 'debit') {
                    $('#refColumnTitle').text('Bill No');
                } else if (currentStatus === 'expense') {
                    $('#refColumnTitle').text('Expense No');
                } else {
                    $('#refColumnTitle').text('Order No');
                }
                fetchCashbook(1); // reset to page 1
            });

            // Filter change events (date, year) -> reset to page 1
            $('#from_date, #to_date, #year').on('change', function () {
                fetchCashbook(1);
            });

            // Search input with debounce
            let searchTimer;
            $('#search-input').on('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    searchQuery = $(this).val();
                    fetchCashbook(1);
                }, 500);
            });

            // Per-page change
            $('#per-page-select').on('change', function () {
                perPage = $(this).val();
                fetchCashbook(1);
            });

            // Pagination click
            $(document).on('click', '#pagination-numbers .page-link', function (e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    fetchCashbook(page);
                }
            });

            // Export buttons (keep existing logic, but they will use current filters)
            // $('#customExportExcel').on('click', function() {
            //     // Build export URL with all filters
            //     let params = {
            //         from_date: $('#from_date').val(),
            //         to_date: $('#to_date').val(),
            //         year: $('#year').val(),
            //         status: currentStatus,
            //         search: searchQuery,
            //         selectedSubAdminId: selectedSubAdminId
            //     };
            //     let queryString = $.param(params);
            //     window.location.href = "{{ url('/api/export-cashbook-excel') }}?" + queryString;
            // });
            $('#customExportExcel').on('click', function () {
                if ($(this).prop("disabled")) {
                    return;
                }
                let params = {
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    year: $('#year').val(),
                    status: currentStatus,
                    search: searchQuery,
                    selectedSubAdminId: selectedSubAdminId
                };

                let queryString = $.param(params);
                let url = "{{ url('/api/export-cashbook-excel') }}?" + queryString;

                $.ajax({
                    url: url,
                    method: 'GET',
                    beforeSend: function () {
                        toggleDownloadLoader(true, "Generating Excel...");
                    },
                    headers: {
                        Authorization: "Bearer " + authToken
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (blob, status, xhr) {
                        // ✅ Get filename from response header or use default
                        let filename = 'cashbook_export.xls';
                        let contentDisposition = xhr.getResponseHeader('Content-Disposition');
                        if (contentDisposition) {
                            let match = contentDisposition.match(
                                /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                            if (match && match[1]) {
                                filename = match[1].replace(/['"]/g, '');
                            }
                        }
                        // ✅ Trigger download
                        let downloadUrl = window.URL.createObjectURL(blob);
                        let a = document.createElement('a');
                        a.href = downloadUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Export Failed',
                            text: xhr.responseJSON?.message ||
                                'Could not download file',
                            confirmButtonColor: '#ff9f43'
                        });
                    },
                    complete: function () {
                        toggleDownloadLoader(false);
                    }
                });
            });

            $('#customExportPdf').on('click', function () {
                if ($(this).prop("disabled")) {
                    return;
                }
                let data = {
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    year: $('#year').val(),
                    status: currentStatus,
                    search: searchQuery,
                    selectedSubAdminId: selectedSubAdminId
                };
                $.ajax({
                    url: "{{ url('/api/export-cashbook-pdf') }}",
                    type: "GET",
                    beforeSend: function () {
                        toggleDownloadLoader(true, "Generating PDF...");
                    },
                    data: data,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function (response) {
                        if (response.success || response.status) {
                            window.open(response.file_url, '_blank');
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Export Failed',
                            text: xhr.responseJSON?.message || 'Could not generate PDF',
                            confirmButtonColor: '#ff9f43'
                        });
                    },
                    complete: function () {
                        toggleDownloadLoader(false);
                    }
                });
            });

            // Initial load
            fetchCashbook(1);

            // Toggle details icon (for mobile)
            $(document).on('click', '.toggle-details', function () {
                let icon = $(this).find('i');
                if (icon.hasClass('fa-plus-circle')) {
                    icon.removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color', 'red');
                } else {
                    icon.removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color', '#ff9f43');
                }
            });

            // Main function to fetch data
            function fetchCashbook(page = 1) {
                let params = {
                    selectedSubAdminId: selectedSubAdminId,
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    year: $('#year').val(),
                    status: currentStatus,
                    search: searchQuery,
                    page: page,
                    per_page: perPage
                };

                $.ajax({
                    url: "{{ url('/api/cashbook/data') }}",
                    type: "GET",
                    data: params,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function (response) {
                        if (response.status) {
                            // Update pagination info
                            currentPage = response.pagination.current_page;
                            lastPage = response.pagination.last_page;
                            updatePaginationUI(response.pagination);

                            // Build table rows
                            let rows = [];
                            response.data.forEach((item, index) => {
                                let amount = parseFloat(item.payment_amount) || 0;
                                let detailsId = `details-${currentPage}-${index}`;
                                let partyName = item.user_name || 'N/A';

                                rows.push([
                                    `<div>
                                                            <span class="cashbook-mobile-party">${partyName}</span>
                                                        <span class="cashbook-mobile-ref">${item.order_number || '-'}</span>
                                                        <div class="collapse mt-2 d-lg-none" id="${detailsId}">
                                                            <div class="collapse-details">
                                                                <div class="detail-item">
                                                                    <span class="detail-label">Date:</span>
                                                                    <span class="detail-value">${item.payment_date}</span>
                                                                </div>
                                                                <div class="detail-item">
                                                                    <span class="detail-label">Particulars:</span>
                                                                    <span class="detail-value">${item.user_name || 'N/A'}</span>
                                                                </div>
                                                                <div class="detail-item">
                                                                    <span class="detail-label">Amount:</span>
                                                                    <span class="detail-value">₹${indianCurrency(amount)}</span>
                                                                </div>
                                                                <div class="detail-item">
                                                                    <span class="detail-label">Action:</span>
                                                                    <span class="detail-value">
                                                                        <div class="d-flex gap-1">
                                                                            <button class="btn btn-sm btn-warning py-0 px-2 cashbook-edit-btn"
                                                                                data-id="${item.id}"
                                                                                data-amount="${item.payment_amount}"
                                                                                data-date="${item.payment_date}"
                                                                                data-remarks="${item.remarks || ''}"
                                                                                title="Edit">
                                                                                <i class="fas fa-edit"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-danger py-0 px-2 cashbook-delete-btn"
                                                                                data-id="${item.id}"
                                                                                title="Delete">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>`,
                                    item.payment_date,
                                    item.user_name || 'N/A',
                                    `₹${indianCurrency(amount)}`,
                                    `<a href="#${detailsId}" class="toggle-details" data-bs-toggle="collapse">
                                                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                                    </a>`,
                                    `<div class="d-flex gap-1 justify-content-center">
                                                            <button class="btn btn-sm btn-warning py-0 px-2 cashbook-edit-btn"
                                                                data-id="${item.id}"
                                                                data-amount="${item.payment_amount}"
                                                                data-date="${item.payment_date}"
                                                                data-remarks="${item.remarks || ''}"
                                                                title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-danger py-0 px-2 cashbook-delete-btn"
                                                                data-id="${item.id}"
                                                                title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>`
                                ]);
                            });

                            table.clear().rows.add(rows).draw();

                            // Update total field (use the total from backend for the whole filtered set)
                            $('#total').val('₹' + indianCurrency(response.total_amount || 0));

                            // Show pagination controls
                            $('.pagination-controls').show();
                        } else {
                            table.clear().draw();
                            $('#total').val('₹0.00');
                            $('.pagination-controls').hide();
                        }
                    },
                    error: function (xhr) {
                        table.clear().draw();
                        $('#total').val('₹0.00');
                        $('.pagination-controls').hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load cashbook data',
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                });
            }

            function updatePaginationUI(pagination) {
                $('#pagination-from').text(pagination.from || 0);
                $('#pagination-to').text(pagination.to || 0);
                $('#pagination-total').text(pagination.total);

                let paginationHtml = '';
                let currentPage = pagination.current_page || 1;
                let lastPage = pagination.last_page || 1;
                paginationHtml += `
                                        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                                            <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}">Previous</a>
                                        </li>
                                    `;

                const visiblePageCount = 2;
                let startPage = Math.floor((currentPage - 1) / visiblePageCount) * visiblePageCount + 1;
                let endPage = Math.min(lastPage, startPage + visiblePageCount - 1);

                if (startPage > 1) {
                    paginationHtml += `
                                            <li class="page-item">
                                                <a class="page-link" href="javascript:void(0);" data-page="${startPage - 1}" data-action="prev-group">..</a>
                                            </li>
                                        `;
                }

                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                                            <li class="page-item ${i === currentPage ? 'active' : ''}">
                                                <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                                            </li>
                                        `;
                }

                if (endPage < lastPage) {
                    paginationHtml += `
                                            <li class="page-item">
                                                <a class="page-link" href="javascript:void(0);" data-page="${endPage + 1}" data-action="next-group">..</a>
                                            </li>
                                        `;
                }

                paginationHtml += `
                                        <li class="page-item ${currentPage === lastPage || lastPage === 0 ? 'disabled' : ''}">
                                            <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}">Next</a>
                                        </li>
                                    `;

                $('#pagination-numbers').html(paginationHtml);
            }

            // ── Edit button click ──
            $(document).on('click', '.cashbook-edit-btn', function () {
                let id = $(this).data('id');
                let amount = $(this).data('amount');
                let dateRaw = $(this).data('date');   // dd-mm-yyyy from API
                let remarks = $(this).data('remarks');

                // Convert dd-mm-yyyy → yyyy-mm-dd for date input
                let parts = String(dateRaw).split('-');
                let isoDate = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateRaw;

                $('#cb_edit_id').val(id);
                $('#cb_edit_amount').val(amount);
                $('#cb_edit_date').val(isoDate);
                $('#cb_edit_remarks').val(remarks);
                $('#cashbookEditModal').modal('show');
            });

            // ── Save edit ──
            $('#cb_save_edit_btn').on('click', function () {
                let id = $('#cb_edit_id').val();
                let amount = $('#cb_edit_amount').val();
                let date = $('#cb_edit_date').val();
                let remark = $('#cb_edit_remarks').val();

                if (!amount || !date) {
                    Swal.fire({ icon: 'warning', title: 'Required', text: 'Date and Amount are required.', confirmButtonColor: '#ff9f43' });
                    return;
                }

                $.ajax({
                    url: `{{ url('/api/payment-store') }}/${id}/update`,
                    method: 'POST',
                    headers: { Authorization: 'Bearer ' + authToken },
                    data: { payment_amount: amount, payment_date: date, remarks: remark, type: currentStatus },
                    success: function (res) {
                        if (res.status) {
                            $('#cashbookEditModal').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Updated!', text: res.message, confirmButtonColor: '#ff9f43', timer: 1500, showConfirmButton: false });
                            fetchCashbook(currentPage);
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Update failed.', confirmButtonColor: '#ff9f43' });
                    }
                });
            });

            // ── Delete button click ──
            $(document).on('click', '.cashbook-delete-btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Payment?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74c3c',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('/api/payment-store') }}/${id}/delete`,
                            method: 'POST',
                            headers: { Authorization: 'Bearer ' + authToken },
                            data: { type: currentStatus },
                            success: function (res) {
                                if (res.status) {
                                    Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, confirmButtonColor: '#ff9f43', timer: 1500, showConfirmButton: false });
                                    fetchCashbook(currentPage);
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Delete failed.', confirmButtonColor: '#ff9f43' });
                            }
                        });
                    }
                });
            });

        });
    </script>
@endpush