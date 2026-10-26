@extends('layout.app')

@section('title', 'Bank Book')

@section('content')

    <style>
        .nav-tabs .nav-item.show .nav-link,
        .nav-tabs .nav-link.active {
            color: white;
            background-color: #ff9f43;
            border-color: #dee2e6 #dee2e6 #fff;
        }

        .nav-link {
            display: block;
            padding: .5rem 1rem;
            color: #637381;
            text-decoration: none;
            transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out;
        }

        .form-control-sm {
            height: 32px;
            font-size: 13px;
        }

        .btn-sm {
            height: 32px;
            font-size: 16px;
        }

        /* DataTables Styling from Staff List */
        .sorting_1 {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        .bankbook-mobile-party {
            display: none;
        }

        .table-scroll-top {
            overflow-x: auto;
            overflow-y: hidden;
            height: 20px;
            width: 100%;
            margin-bottom: 5px;
            display: none;
        }

        .table-scroll-top div {
            height: 1px;
        }

        /* Bank Name column word break properly */
        #bankBookTable th:nth-child(3),
        #bankBookTable td:nth-child(3),
        #bankBookTable th:nth-child(4),
        #bankBookTable td:nth-child(4) {
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.4;
            max-width: 140px;
        }

        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
                -webkit-overflow-scrolling: touch !important;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch !important;
            }
        }

        /* Desktop: show all columns normally */
        @media (min-width: 769px) {

            table.datanew thead th,
            table.datanew tbody td {
                display: table-cell !important;
            }

            /* Hide the Details toggle column (col 6) on desktop — Action (col 7) remains visible */
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(6) {
                display: none !important;
            }
        }

        /* Mobile: hide non-essential columns, show Details toggle (col 6) only */
        @media (max-width: 768px) {
            table.datanew {
                width: 100% !important;
                table-layout: fixed;
            }

            table.datanew thead th:nth-child(n+2),
            table.datanew tbody td:nth-child(n+2) {
                display: none !important;
            }

            /* Show only Column 1 and Details toggle (col 6) on mobile */
            table.datanew thead th:first-child,
            table.datanew tbody td:first-child {
                display: table-cell !important;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(6) {
                display: table-cell !important;
                text-align: center;
                vertical-align: top !important;
                width: 56px !important;
                min-width: 56px !important;
                max-width: 56px !important;
                padding: 12px 6px !important;
            }

            /* Keep Action column (col 7) hidden on mobile */
            table.datanew thead th:nth-child(7),
            table.datanew tbody td:nth-child(7) {
                display: none !important;
            }

            .toggle-details {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                margin-left: auto;
            }

            .toggle-details i {
                font-size: 18px;
            }

            table.datanew tbody td:first-child {
                width: calc(100% - 56px) !important;
                max-width: calc(100vw - 96px) !important;
                vertical-align: top !important;
            }

            table.datanew tbody td:first-child > div {
                width: 100%;
            }

            .bankbook-mobile-ref {
                display: block;
                font-weight: 500;
            }

            .bankbook-mobile-party {
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

        /* Collapsible details styling */
        /* .collapse-details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #ff9f43;
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
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        /* Hide default DataTables buttons */
        .dt-buttons {
            display: none !important;
        }

        .dataTables_filter,
        .dataTables_length {
            display: none !important;
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

            .pagination-controls > div,
            .pagination-controls nav {
                width: 100%;
            }

            .pagination-controls > div {
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 8px;
            }

            .pagination-controls nav .pagination {
                justify-content: center;
                flex-wrap: wrap;
            }
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
            .download-loader-box h4 {
                font-size: 28px;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Bank Book</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-top mb-3">
                    <div class="row w-100 align-items-center">
                        {{-- Tabs --}}
                        <div class="col-md-12 mb-3">
                            <ul class="nav nav-tabs nav-tabs-solid" id="bankBookTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-status="credit" href="javascript:void(0)">Receipts</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-status="debit" href="javascript:void(0)">Payments</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-status="expense" href="javascript:void(0)">Expenses</a>
                                </li>
                            </ul>
                        </div>

                        <div class="row g-2 align-items-center bankbook-filter-row" style="padding-left: 13px;">

                            <!-- Start Date -->
                            <div class="col-md-2 col-6">
                                <label for="fromDate" class="form-label fw-bold">From Date</label>
                                <input type="date" id="fromDate" class="form-control form-control-sm">
                            </div>

                            <!-- End Date -->
                            <div class="col-md-2 col-6">
                                <label for="toDate" class="form-label fw-bold">To Date</label>
                                <input type="date" id="toDate" class="form-control form-control-sm">
                            </div>

                            <!-- Year -->
                            <div class="col-md-2 col-6">
                                <label for="filterYear" class="form-label fw-bold">Year</label>
                                <select id="filterYear" class="form-control form-control-sm">
                                    <option value="">-- Select Year --</option>
                                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Bank Filter -->
                            <div class="col-md-2 col-6">
                                <label for="bankFilter" class="form-label fw-bold">Bank</label>
                                <select id="bankFilter" class="form-control form-control-sm">
                                    <option value="">-- All Banks --</option>
                                </select>
                            </div>

                            <!-- Total -->
                            <div class="col-md-2 col-12 bankbook-total-col">
                                <label class="form-label fw-bold">Total</label>
                                <div class="form-control form-control-sm fw-bold bg-light"
                                    style="border:1px solid #dcdcdc; color:#ff9f43;">
                                    ₹<span id="grandClosingBalance">0.00</span>
                                </div>
                            </div>



                            <!-- Excel -->
                            <div class="col-md-1 col-6 d-flex flex-column bankbook-export-col">
                                <label class="form-label opacity-0 d-none d-md-block">Excel</label>
                                <button class="btn btn-success btn-sm w-100" id="exportExcel">
                                    <i class="fa fa-file-excel"></i> Excel
                                </button>
                            </div>

                            <!-- PDF -->
                            <div class="col-md-1 col-6 d-flex flex-column bankbook-export-col">
                                <label class="form-label opacity-0 d-none d-md-block">PDF</label>
                                <button class="btn btn-danger btn-sm w-100" id="exportPdf">
                                    <i class="fa fa-file-pdf"></i> PDF
                                </button>
                            </div>

                        </div>

                        {{-- Search input --}}
                        <div class="col-md-3 col-12 d-flex flex-column mt-2">
                            {{-- <label class="form-label fw-bold">Search</label> --}}
                            <input type="text" id="search-input" class="form-control form-control-sm"
                                placeholder="Search...">
                        </div>

                    </div>
                </div>

                {{-- <div class="table-scroll-top">
                    <div></div>
                </div> --}}
                <div class="table-responsive mt-3" style="overflow-x: auto;">
                    <table class="table datanew" id="bankBookTable">
                        <thead>
                            <tr>
                                <th id="refColumnTitle">Order No</th>
                                <th>Date</th>
                                <th>Particulars</th>
                                <th>Bank Name</th>
                                {{-- <th>Type</th> --}}
                                <th>Amount</th>
                                <th class="details-column">Details</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>

                {{-- Pagination Controls --}}
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
                            {{-- Page numbers inserted here --}}
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

    {{-- Edit Payment Modal (Bankbook) --}}
    <div class="modal fade" id="bankbookEditModal" tabindex="-1" aria-labelledby="bankbookEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background:#ff9f43;">
                    <h5 class="modal-title text-white" id="bankbookEditLabel"><i class="fas fa-edit me-2"></i>Edit Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="bb_edit_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" id="bb_edit_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (₹)</label>
                        <input type="number" id="bb_edit_amount" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea id="bb_edit_remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="bb_save_edit_btn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

    {{-- <script>
        $('#exportExcel').click(function() {
            $('.buttons-excel').click();
        });

        function formatINR(amount) {
            return Number(amount).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        $('#exportPdf').click(function() {
            let data = {
                from_date: $('#fromDate').val(),
                to_date: $('#toDate').val(),
                year: $('#filterYear').val(),
                bank_id: $('#bankFilter').val(),
                status: currentStatus,
                search: bankBookTable.search(),
                selectedSubAdminId: localStorage.getItem("selectedSubAdminId")
            };

            $.ajax({
                url: "{{ url('/api/export-bankbook-pdf') }}",
                headers: {
                    Authorization: "Bearer " + localStorage.getItem("authToken")
                },
                data: data,
                success: function(res) {
                    if (res.success) {
                        window.open(res.file_url, '_blank');
                    }
                }
            });
        });

        let bankBookTable;
        let currentStatus = 'credit';

        $(document).ready(function() {

            // Init DataTable ONCE
            bankBookTable = $('#bankBookTable').DataTable({
                destroy: true,
                ordering: false,
                searching: true,
                paging: true,
                info: true,
                lengthChange: true,
                autoWidth: false,
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excel',
                    className: 'buttons-excel',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
                        format: {
                            body: function(data, row, column, node) {
                                // Strip HTML from the first column (Invoice/Order No)
                                if (column === 0) {
                                    return $(node).find('span').first().text() || data;
                                }
                                return data;
                            }
                        }
                    }
                }]
            });

            // Trigger server-side search when DataTable search box is used
            let searchTimer;
            $('#bankBookTable_filter input').unbind().bind('keyup', function(e) {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    bankBookTable.search(this.value).draw();
                    loadBankBook();
                }, 500);
            });

            loadBanks();
            loadBankBook();

            // Filters
            $('#filterBtn').click(loadBankBook);
            $('#fromDate, #toDate, #bankFilter, #filterMonth, #filterYear').on('change', loadBankBook);

            // Tabs click
            $('#bankBookTabs .nav-link').on('click', function() {

                $('#bankBookTabs .nav-link').removeClass('active');
                $(this).addClass('active');

                currentStatus = $(this).data('status');

                // 🔁 Change column heading dynamically
                if (currentStatus === 'debit') {
                    $('#refColumnTitle').text('Bill No ');
                } else {
                    $('#refColumnTitle').text('Order No');
                }

                loadBankBook();
            });

            // Toggle details icon
            $(document).on('click', '.toggle-details', function() {
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

        });

        /* ===============================
           BANK DROPDOWN
        =============================== */
        function loadBanks() {

            $.ajax({
                url: "{{ url('/api/banks/data') }}",
                headers: {
                    Authorization: "Bearer " + localStorage.getItem("authToken")
                },
                data: {
                    selectedSubAdminId: localStorage.getItem("selectedSubAdminId")
                },
                success: function(res) {
                    let html = '<option value="">All Banks</option>';
                    if (res.status) {
                        res.data.forEach(bank => {
                            html += `<option value="${bank.id}">${bank.bank_name}</option>`;
                        });
                    }
                    $('#bankFilter').html(html);
                }
            });
        }

        /* ===============================
           BANK BOOK DATA
        =============================== */
        function loadBankBook() {

            bankBookTable.clear().draw();

            $.ajax({
                url: "{{ url('/api/bankbook') }}",
                headers: {
                    Authorization: "Bearer " + localStorage.getItem("authToken")
                },
                data: {
                    from_date: $('#fromDate').val(),
                    to_date: $('#toDate').val(),
                    month: $('#filterMonth').val(),
                    year: $('#filterYear').val(),
                    bank_id: $('#bankFilter').val(),
                    status: currentStatus,
                    search: bankBookTable.search(),
                    selectedSubAdminId: localStorage.getItem("selectedSubAdminId")
                },
                success: function(res) {

                    let grandTotal = 0;
                    let tableBody = [];

                    if (!res.data || res.data.length === 0) {
                        bankBookTable.clear().draw();
                        $('#grandClosingBalance').text('0.00');
                        return;
                    }

                    res.data.forEach((row, index) => {

                        let amount = parseFloat(row.payment_amount) || 0;
                        grandTotal += amount;

                        // 🔑 Decide number based on type
                        let refNo = row.status === 'debit' ?
                            row.order_no // Payment → Order No
                            :
                            row.invoice_no; // Receipt → Invoice No

                        let detailsId = `details-${index}`;

                        tableBody.push([
                            // Column 1: Invoice No / Order No + Mobile Details
                            `<div>
                                <div style="display: flex; align-items: center;">
                                    <span>${refNo || '-'}</span>
                                </div>

                                <!-- Collapsible Details (visible only on mobile) -->
                                <div class="collapse mt-2 d-lg-none" id="${detailsId}">
                                    <div class="collapse-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Date:</span>
                                            <span class="detail-value">${row.payment_date}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Particulars:</span>
                                            <span class="detail-value">${row.particulars}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Bank:</span>
                                            <span class="detail-value">${row.bank_name}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Amount:</span>
                                            <span class="detail-value">₹${formatINR(amount)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>`,
                            row.payment_date,
                            row.particulars,
                            row.bank_name,
                            `₹${formatINR(amount)}`,
                            // Details Toggle column
                            `<a href="#${detailsId}" class="toggle-details" data-bs-toggle="collapse">
                                <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                            </a>`
                        ]);
                    });

                    bankBookTable.clear().rows.add(tableBody).draw();

                    // Sync top scrollbar
                    const topScroll = document.querySelector('.table-scroll-top');
                    const tableResponsive = document.querySelector('.table-responsive');
                    const tableElement = document.querySelector('#bankBookTable');

                    if (topScroll && tableResponsive && tableElement) {
                        const topInnerDiv = topScroll.querySelector('div');
                        topInnerDiv.style.width = tableElement.scrollWidth + 'px';

                        topScroll.onscroll = () => {
                            tableResponsive.scrollLeft = topScroll.scrollLeft;
                        };
                        tableResponsive.onscroll = () => {
                            topScroll.scrollLeft = tableResponsive.scrollLeft;
                        };
                    }

                    // $('#grandClosingBalance').text(grandTotal.toFixed(2));
                    $('#grandClosingBalance').text(formatINR(grandTotal));
                },
                error: function() {
                    bankBookTable.clear().draw();
                    bankBookTable.row.add([
                        '-', '-', 'Failed to load data', '-', '-', '-'
                    ]).draw();
                }
            });
        }
    </script> --}}
    <script>
        let bankBookTable;
        let currentStatus = 'credit';
        let currentPage = 1;
        let lastPage = 1;
        let perPage = 10;
        let searchQuery = '';

        function formatINR(amount) {
            return Number(amount).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        $(document).ready(function() {
            const $downloadLoader = $("#downloadLoaderOverlay");
            const $downloadLoaderText = $("#downloadLoaderText");
            const $exportButtons = $("#exportExcel, #exportPdf");

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

            // Initialize DataTable as a display container only
            bankBookTable = $('#bankBookTable').DataTable({
                destroy: true,
                ordering: false,
                searching: false,
                paging: false,
                info: false,
                lengthChange: false,
                autoWidth: false,
                dom: 't',
                buttons: [], // buttons will be handled separately
                columnDefs: [
                    { targets: 5, orderable: false },
                    { targets: 6, orderable: false, className: 'text-center' }
                ]
            });

            loadBanks();
            loadBankBook(currentPage);

            // Search input handler
            let searchTimer;
            $('#search-input').on('keyup', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    searchQuery = $(this).val();
                    loadBankBook(1);
                }, 500);
            });

            // Per‑page change handler
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                loadBankBook(1);
            });

            // Page click handler
            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    loadBankBook(page);
                }
            });

            // Filters (date, year, bank) – reload page 1
            $('#fromDate, #toDate, #filterYear, #bankFilter').on('change', function() {
                loadBankBook(1);
            });

            // Tabs click
            $('#bankBookTabs .nav-link').on('click', function() {
                $('#bankBookTabs .nav-link').removeClass('active');
                $(this).addClass('active');
                currentStatus = $(this).data('status');
                if (currentStatus === 'debit') {
                    $('#refColumnTitle').text('Bill No');
                } else if (currentStatus === 'expense') {
                    $('#refColumnTitle').text('Expense No');
                } else {
                    $('#refColumnTitle').text('Order No');
                }
                loadBankBook(1);
            });

            $('#exportExcel').click(function() {
                if ($(this).prop("disabled")) {
                    return;
                }
                let params = {
                    from_date: $('#fromDate').val(),
                    to_date: $('#toDate').val(),
                    year: $('#filterYear').val(),
                    bank_id: $('#bankFilter').val(),
                    status: currentStatus,
                    search: searchQuery,
                    selectedSubAdminId: localStorage.getItem("selectedSubAdminId")
                    //  token: localStorage.getItem("authToken")
                };
                let queryString = $.param(params);
                let url = "{{ url('/api/export-bankbook-excel') }}?" + queryString;

                $.ajax({
                    url: url,
                    method: 'GET',
                    beforeSend: function() {
                        toggleDownloadLoader(true, "Generating Excel...");
                    },
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem("authToken")
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(blob, status, xhr) {
                        let filename = 'bankbook_export.xml'; // default (changed from .xlsx)
                        let contentDisposition = xhr.getResponseHeader('Content-Disposition');
                        if (contentDisposition) {
                            let match = contentDisposition.match(
                                /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                            if (match && match[1]) {
                                filename = match[1].replace(/['"]/g, '');
                            }
                        }
                        let downloadUrl = window.URL.createObjectURL(blob);
                        let a = document.createElement('a');
                        a.href = downloadUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Export Failed',
                            text: xhr.responseJSON?.message ||
                                'Could not download file',
                            confirmButtonColor: '#ff9f43'
                        });
                    },
                    complete: function() {
                        toggleDownloadLoader(false);
                    }
                });
            });


            $('#exportPdf').click(function() {
                if ($(this).prop("disabled")) {
                    return;
                }
                let data = {
                    from_date: $('#fromDate').val(),
                    to_date: $('#toDate').val(),
                    year: $('#filterYear').val(),
                    bank_id: $('#bankFilter').val(),
                    status: currentStatus,
                    search: searchQuery,
                    selectedSubAdminId: localStorage.getItem("selectedSubAdminId")
                };
                $.ajax({
                    url: "{{ url('/api/export-bankbook-pdf') }}",
                    beforeSend: function() {
                        toggleDownloadLoader(true, "Generating PDF...");
                    },
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem("authToken")
                    },
                    data: data,
                    success: function(res) {
                        if (res.success) window.open(res.file_url, '_blank');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Export Failed',
                            text: xhr.responseJSON?.message || 'Could not generate PDF',
                            confirmButtonColor: '#ff9f43'
                        });
                    },
                    complete: function() {
                        toggleDownloadLoader(false);
                    }
                });
            });

            // Toggle details icon
            $(document).on('click', '.toggle-details', function() {
                let icon = $(this).find('i');
                if (icon.hasClass('fa-plus-circle')) {
                    icon.removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color', 'red');
                } else {
                    icon.removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color', '#ff9f43');
                }
            });
        });

        function loadBanks() {
            $.ajax({
                url: "{{ url('/api/banks/data') }}",
                headers: {
                    Authorization: "Bearer " + localStorage.getItem("authToken")
                },
                data: {
                    selectedSubAdminId: localStorage.getItem("selectedSubAdminId")
                },
                success: function(res) {
                    let html = '<option value="">All Banks</option>';
                    if (res.status) {
                        res.data.forEach(bank => {
                            html += `<option value="${bank.id}">${bank.bank_name}</option>`;
                        });
                    }
                    $('#bankFilter').html(html);
                }
            });
        }

        function loadBankBook(page = 1) {
            bankBookTable.clear().draw();

            let params = {
                from_date: $('#fromDate').val(),
                to_date: $('#toDate').val(),
                year: $('#filterYear').val(),
                bank_id: $('#bankFilter').val(),
                status: currentStatus,
                search: searchQuery,
                page: page,
                per_page: perPage,
                selectedSubAdminId: localStorage.getItem("selectedSubAdminId")
            };

            $.ajax({
                url: "{{ url('/api/bankbook') }}",
                headers: {
                    Authorization: "Bearer " + localStorage.getItem("authToken")
                },
                data: params,
                success: function(res) {
                    if (!res.data || res.data.length === 0) {
                        bankBookTable.clear().draw();
                        $('#grandClosingBalance').text('0.00');
                        $('.pagination-controls').hide();
                        return;
                    }

                    // Update pagination info
                    currentPage = res.pagination.current_page;
                    lastPage = res.pagination.last_page;
                    updatePaginationUI(res.pagination);

                    // Build rows
                    let tableBody = [];
                    res.data.forEach((row, index) => {
                        let amount = parseFloat(row.payment_amount) || 0;
                        let refNo = (row.status === 'debit' || row.status === 'expense') ? row.order_no : row.invoice_no;
                        let detailsId = `details-${currentPage}-${index}`;
                        let partyName = row.particulars || 'N/A';

                        tableBody.push([
                            `<div>
                                <span class="bankbook-mobile-party">${partyName}</span>
                            <span class="bankbook-mobile-ref">${refNo || '-'}</span>
                            <div class="collapse mt-2 d-lg-none" id="${detailsId}">
                                <div class="collapse-details">
                                    <div class="detail-item"><span class="detail-label">Date:</span><span class="detail-value">${row.payment_date}</span></div>
                                    <div class="detail-item"><span class="detail-label">Particulars:</span><span class="detail-value">${row.particulars}</span></div>
                                    <div class="detail-item"><span class="detail-label">Bank:</span><span class="detail-value">${row.bank_name}</span></div>
                                    <div class="detail-item"><span class="detail-label">Amount:</span><span class="detail-value">₹${formatINR(amount)}</span></div>
                                    <div class="detail-item"><span class="detail-label">Action:</span><span class="detail-value"><div class="d-flex gap-1"><button class="btn btn-sm btn-warning py-0 px-2 bb-edit-btn" data-id="${row.id}" data-amount="${row.payment_amount}" data-date="${row.payment_date}" data-remarks="${row.remarks || ''}" title="Edit"><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-danger py-0 px-2 bb-delete-btn" data-id="${row.id}" title="Delete"><i class="fas fa-trash"></i></button></div></span></div>
                                </div>
                            </div>
                        </div>`,
                            row.payment_date,
                            row.particulars,
                            row.bank_name,
                            `₹${formatINR(amount)}`,
                            `<a href="#${detailsId}" class="toggle-details" data-bs-toggle="collapse"><i class="fas fa-plus-circle" style="color:#ff9f43;"></i></a>`,
                            `<div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-warning py-0 px-2 bb-edit-btn"
                                    data-id="${row.id}"
                                    data-amount="${row.payment_amount}"
                                    data-date="${row.payment_date}"
                                    data-remarks="${row.remarks || ''}"
                                    title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger py-0 px-2 bb-delete-btn"
                                    data-id="${row.id}"
                                    title="Delete"><i class="fas fa-trash"></i></button>
                            </div>`
                        ]);
                    });

                    bankBookTable.clear().rows.add(tableBody).draw();
                    // $('#grandClosingBalance').text(formatINR(res.grand_closing_balance));
                    // ✅ New - shows total for current tab (Receipts or Payments) with filters applied
$('#grandClosingBalance').text(formatINR(res.current_tab_total));
                    $('.pagination-controls').show();

                    // Sync top scrollbar (if used)
                    const topScroll = document.querySelector('.table-scroll-top');
                    const tableResponsive = document.querySelector('.table-responsive');
                    const tableElement = document.querySelector('#bankBookTable');
                    if (topScroll && tableResponsive && tableElement) {
                        const topInnerDiv = topScroll.querySelector('div');
                        topInnerDiv.style.width = tableElement.scrollWidth + 'px';
                        topScroll.onscroll = () => tableResponsive.scrollLeft = topScroll.scrollLeft;
                        tableResponsive.onscroll = () => topScroll.scrollLeft = tableResponsive.scrollLeft;
                    }
                },
                error: function() {
                    bankBookTable.clear().draw();
                    $('.pagination-controls').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to load data!',
                        confirmButtonColor: '#ff9f43'
                    });
                }
            });
        }

        function updatePaginationUI(pagination) {
            let from = pagination.from || 0;
            let to = pagination.to || 0;
            $('#pagination-from').text(from);
            $('#pagination-to').text(to);
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
        $(document).on('click', '.bb-edit-btn', function () {
            let id      = $(this).data('id');
            let amount  = $(this).data('amount');
            let dateRaw = $(this).data('date');   // dd-mm-yyyy from API
            let remarks = $(this).data('remarks') || '';

            // Convert dd-mm-yyyy → yyyy-mm-dd for date input
            let parts   = String(dateRaw).split('-');
            let isoDate = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateRaw;

            $('#bb_edit_id').val(id);
            $('#bb_edit_amount').val(amount);
            $('#bb_edit_date').val(isoDate);
            $('#bb_edit_remarks').val(remarks);
            $('#bankbookEditModal').modal('show');
        });

        // ── Save edit ──
        $('#bb_save_edit_btn').on('click', function () {
            let id     = $('#bb_edit_id').val();
            let amount = $('#bb_edit_amount').val();
            let date   = $('#bb_edit_date').val();
            let remark = $('#bb_edit_remarks').val();

            if (!amount || !date) {
                Swal.fire({ icon: 'warning', title: 'Required', text: 'Date and Amount are required.', confirmButtonColor: '#ff9f43' });
                return;
            }

            $.ajax({
                url: `{{ url('/api/bankbook-payment') }}/${id}/update`,
                method: 'POST',
                headers: { Authorization: 'Bearer ' + localStorage.getItem('authToken') },
                data: { payment_amount: amount, payment_date: date, remarks: remark, type: currentStatus },
                success: function (res) {
                    if (res.status) {
                        $('#bankbookEditModal').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Updated!', text: res.message, confirmButtonColor: '#ff9f43', timer: 1500, showConfirmButton: false });
                        loadBankBook(currentPage);
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Update failed.', confirmButtonColor: '#ff9f43' });
                }
            });
        });

        // ── Delete button click ──
        $(document).on('click', '.bb-delete-btn', function () {
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
                        url: `{{ url('/api/bankbook-payment') }}/${id}/delete`,
                        method: 'POST',
                        headers: { Authorization: 'Bearer ' + localStorage.getItem('authToken') },
                        data: { type: currentStatus },
                        success: function (res) {
                            if (res.status) {
                                Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, confirmButtonColor: '#ff9f43', timer: 1500, showConfirmButton: false });
                                loadBankBook(currentPage);
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Delete failed.', confirmButtonColor: '#ff9f43' });
                        }
                    });
                }
            });
        });

    </script>
@endpush

