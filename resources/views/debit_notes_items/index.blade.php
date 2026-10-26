@extends('layout.app')

@section('title', 'Debit Note List')

@push('css')
<style>
    .dataTables_filter,
    .dataTables_length,
    .dataTables_info,
    .dataTables_paginate {
        display: none !important;
    }

    .dataTables_wrapper .row:first-child {
        display: none !important;
    }

    .dataTables_wrapper {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    .table tbody tr td {
        /* white-space: normal !important; */
        word-break: break-word;
        word-wrap: break-word;
    }

    .search-input input {
        padding-left: 35px !important;
        border-radius: 5px;
    }

    .search-input {
        position: relative;
        display: flex;
        align-items: center;
    }

    .btn-searchset {
        position: absolute;
        left: 10px;
        z-index: 10;
        padding: 0;
        top: 7px !important;
    }

    .pagination .page-item .page-link {
        background-color: #5d6d7e;
        /* Dark gray for other pages */
        color: #fff;
        border: none;
        margin: 0 3px;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: bold;
    }

    .pagination .page-item.active .page-link {
        background-color: #ff9f43 !important;
        border-color: #ff9f43 !important;
        color: #fff !important;
    }

    /* Previous and Next buttons */
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

    .pagination .page-item.disabled .page-link {
        background-color: #fff !important;
        color: #dee2e6 !important;
        border: 1px solid #dee2e6 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
    }

    .pagination .page-item .page-link:hover {
        background-color: #4a5766;
        color: #fff;
    }

    .pagination .page-item.active .page-link:hover {
        background-color: #e68a35 !important;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-container table thead th {
        white-space: nowrap;
    }

    .datatable {
        width: 100% !important;
    }

    .credit-mobile {
        width: fit-content;
        max-width: 100%;
    }

    @media (min-width: 768px) {
        .credit-mobile {
            display: inline-flex !important;
            align-items: center;
        }
    }
    /* Mobile toggle button styles */
    .mobile-toggle-btn-table {
        background: #ff9f43;
        color: white;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        min-width: 32px;
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
        transition: all 0.3s ease;
        padding: 0;
        margin: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .mobile-toggle-btn-table:hover {
        background: #ff8c2e;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .mobile-toggle-btn-table.minus {
        background: #dc3545;
    }

    .mobile-toggle-btn-table.minus:hover {
        background: #c82333;
    }

    .details-row {
        display: none;
    }

    .details-row.show {
        display: table-row;
    }

    /* .details-content {
        padding: 15px;
        background: #f8f9fa;
        border-top: 2px solid #ff9f43;
    } */

    .details-content .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
        font-size: 14px;
        gap: 25px;
    }

    .details-content .detail-item:last-child {
        border-bottom: none;
    }

    .details-content .detail-label {
        font-weight: 600;
        color: #495057;
    }

    .details-content .detail-value {
        color: #212529;
    }

    .debit-note-mobile-ref {
        display: block;
        font-weight: 500;
    }

    .debit-note-mobile-party {
        display: none;
    }

    /* Responsive Table Styling */
    @media screen and (max-width: 767px) {
        .table-container {
            overflow-x: visible !important;
            -webkit-overflow-scrolling: touch !important;
        }

        .datatable {
            width: 100% !important;
            table-layout: auto !important;
        }

        .datatable thead th:nth-child(n+3),
        .datatable tbody td:nth-child(n+3) {
            display: none !important;
        }

        .datatable thead th:first-child,
        .datatable tbody td:first-child {
            display: table-cell !important;
            width: auto !important;
            word-wrap: break-word;
        }

        .datatable thead th.details-column,
        .datatable tbody td.details-control {
            display: table-cell !important;
            text-align: center;
            vertical-align: middle;
            width: 60px !important;
        }

        .debit-note-mobile-party {
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

    @media (min-width: 768px) {
        .datatable {
            min-width: 980px;
        }

        .datatable thead th.details-column,
        .datatable tbody td.details-control {
            display: none !important;
        }
    }

    @media (max-width: 575px) {
        .page-header .page-btn {
            margin-top: 0 !important;
        }
    }

    @media (max-width: 420px) {
        .page-actions {
            display: flex !important;
            flex-direction: row !important;
            justify-content: end !important;
            align-items: center !important;
        }

        .credit-mobile {
            background-color: #f8f9fa;
            color: #ff9f43;
            font-weight: bold;
            padding: 3px 10px;
            border: 1px solid #d1d1d1;
            border-radius: 8px;
            margin-right: 5px;
            margin-top: 8px;
        }

        .page-actions > div {
            margin-right: 0 !important;
        }

        .page-btn a {
            width: auto !important;
            padding: 8px 15px !important;
            height: auto !important;
            margin-bottom: 0 !important;
        }

        .page-header .page-title h4 {
            margin-bottom: 10px;
        }
    }

    .datatable th:last-child,
    .datatable td:last-child {
        width: 120px !important;
        min-width: 120px;
        max-width: 120px;
        text-align: center;
        vertical-align: middle;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }

    .action-buttons img {
        width: 18px;
        height: 18px;
    }

    @media (max-width: 767px) {
        .datatable td:last-child {
            width: 100px !important;
        }
    }


</style>
@endpush

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Debit Note List</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap page-actions">

            <div class="page-btn">
                @if (app('hasPermission')(27, 'add'))
                    <a href="{{ route('debit-notes-items.create') }}" class="btn btn-added">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img"
                            class="me-1">Add <span class="d-none d-sm-inline">Debit Note Item</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
    <div class="d-flex align-items-center credit-mobile mb-2" style="background-color: #f8f9fa; color: #ff9f43; font-weight: bold; padding: 7px 10px; border: 1px solid #d1d1d1; border-radius: 8px;">
                    <span style="color: #495057; margin-right: 8px; font-size: 14px; ">Total Settlement Amount:</span>
                    <span style="font-size: 16px;">₹<span id="total_settlement_amount">0.00</span></span>
                </div>
                </div>

    <div class="card">
        <div class="card-body">
            <div class="table-top mb-3">
                <div class="search-set">
                    <div class="search-input">
                        <a class="btn btn-searchset">
                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img">
                        </a>
                        <input type="text" id="debit-note-search-input" class="form-control" placeholder="Search...">
                    </div>
                </div>
            </div>
            <div class="table-container table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>Order / Bill Number</th>
                            <th class="details-column">Details</th>
                            <th>Customer / Vendor Name</th>
                            <th>Type</th>
                            <th>Total Amount</th>
                            <th>Settlement Amount</th>
                            <th>Final Total</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="debit_note_items_table_body">
                        {{-- Data will be loaded via AJAX --}}
                    </tbody>
                </table>
            </div>
            <div
                class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                <div class="d-flex align-items-center mb-3 mb-md-0">
                    <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                    <select id="debit-note-per-page-select" class="form-select form-select-sm"
                        style="width: auto; border: 1px solid #ddd;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="ms-3" style="font-size: 14px; color: #555;">
                        <span id="debit-note-pagination-from">0</span> - <span id="debit-note-pagination-to">0</span> of
                        <span id="debit-note-pagination-total">0</span> items
                    </span>
                </div>
                <nav aria-label="Debit note pagination">
                    <ul class="pagination pagination-sm mb-0" id="debit-note-pagination-numbers"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    var authToken = localStorage.getItem("authToken");
    var selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
    const canViewDebitNoteItem = @json(app('hasPermission')(27, 'view'));
    const canEditDebitNoteItem = @json(app('hasPermission')(27, 'edit'));
    const canDeleteDebitNoteItem = @json(app('hasPermission')(27, 'delete'));
    let currentPage = 1;
    let lastPage = 1;
    let perPage = 10;
    let searchQuery = '';
    let currencySymbol = "{{ $currencySymbol ?? '₹' }}";
    let currencyPosition = "{{ $currencyPosition ?? 'left' }}";

    // Global data map for expandable rows
    window.debitNoteDataMap = {};

    function formatCurrency(amount) {
        let formatted = parseFloat(amount || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        return currencyPosition === 'left' ? currencySymbol + formatted : formatted + currencySymbol;
    }

    function buildDebitNoteActionLinks(itemId) {
        let actions = '';

        if (canViewDebitNoteItem) {
            actions += `
                <a href="{{ url('debit-note-items/view') }}/${itemId}">
                    <img src="{{ env('ImagePath') . ('admin/assets/img/icons/eye.svg') }}">
                </a>
            `;
        }

        if (canEditDebitNoteItem) {
            actions += `
                <a href="{{ url('debit-note-items/edit') }}/${itemId}">
                    <img src="{{ env('ImagePath') . ('admin/assets/img/icons/edit.svg') }}">
                </a>
            `;
        }

        if (canDeleteDebitNoteItem) {
            actions += `
                <a class="confirm-text delete-item" data-id="${itemId}">
                    <img src="{{ env('ImagePath') . ('admin/assets/img/icons/delete.svg') }}">
                </a>
            `;
        }

        return actions || '<span class="text-muted">-</span>';
    }

    if ($.fn.DataTable.isDataTable('.datatable')) {
        $('.datatable').DataTable().destroy();
    }

    let table = $('.datatable').DataTable({
        bFilter: false,
        destroy: true,
        paging: false,
        info: false,
        searching: false,
        dom: 't',
        ordering: true,
        order: [],
        language: {
            emptyTable: "No records found.",
        },
        columnDefs: [
            { targets: 1, className: 'details-control', orderable: false }
        ]
    });

    $('#debit-note-search-input').on('keyup', function() {
        searchQuery = $(this).val();
        loadDebitNoteItems(1);
    });

    $('#debit-note-per-page-select').on('change', function() {
        perPage = $(this).val();
        loadDebitNoteItems(1);
    });

    /** ----------------------------------------------
     * 📱 Mobile Expandable Row Functions
     * ---------------------------------------------- */
    function buildDebitNoteExpandableRowContent(item) {
        let displayName = 'N/A';
        if (item.transaction_type === 'receipt') {
            displayName = (item.order && item.order.user) ? item.order.user.name : 'N/A';
        } else {
            displayName = (item.purchase_invoice && item.purchase_invoice.vendor)
                ? item.purchase_invoice.vendor.name
                : 'N/A';
        }

        let typeName = item.credit_note_type ? item.credit_note_type.type_name : 'N/A';

        return `
            <div class="details-content">
                <div class="detail-item">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">${displayName}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Type:</span>
                    <span class="detail-value">${typeName}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">${formatCurrency(item.grand_total)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Settlement Amount:</span>
                    <span class="detail-value" style="color: #ff9f43; font-weight: 600;">${formatCurrency(item.settlement_amount)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Final Total:</span>
                    <span class="detail-value" style="color: #28a745; font-weight: 600;">${formatCurrency(item.total)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Action:</span>
                    <span class="detail-value">${buildDebitNoteActionLinks(item.id)}</span>
                </div>
            </div>
        `;
    }

    window.toggleDebitNoteRowDetails = function(itemId) {
        // Only work on mobile/tablet (≤767px)
        if ($(window).width() > 767) {
            return;
        }

        const row = $(`tr[data-item-id="${itemId}"]`);
        if (row.length === 0) return;

        const detailsRow = row.next(`tr.details-row[data-details-id="${itemId}"]`);
        const toggleBtn = row.find('.mobile-toggle-btn-table');

        if (detailsRow.length === 0) {
            // Create expandable row if it doesn't exist
            const item = window.debitNoteDataMap[itemId];
            if (!item) return;

            const expandableContent = buildDebitNoteExpandableRowContent(item);
            const newRow = $(`
                <tr class="details-row" data-details-id="${itemId}">
                    <td colspan="100%">${expandableContent}</td>
                </tr>
            `);

            row.after(newRow);
            newRow.addClass('show');
            toggleBtn.text('-').addClass('minus');
        } else {
            // Toggle existing expandable row
            if (detailsRow.hasClass('show')) {
                detailsRow.removeClass('show');
                toggleBtn.text('+').removeClass('minus');
            } else {
                detailsRow.addClass('show');
                toggleBtn.text('-').addClass('minus');
            }
        }
    };

    function loadDebitNoteItems(page = 1) {
        let url = "{{ url('api/debit-note-items') }}" + `?page=${page}&per_page=${perPage}`;
        if (selectedSubAdminId && selectedSubAdminId !== 'null') {
            url += `&selectedSubAdminId=${encodeURIComponent(selectedSubAdminId)}`;
        }
        if (searchQuery) {
            url += `&search=${encodeURIComponent(searchQuery)}`;
        }

        $.ajax({
            url: url,
            type: "GET",
            headers: {
                "Authorization": "Bearer " + authToken
            },
            success: function (response) {
                if (response.status === 'success') {
                    table.clear();
                    currentPage = response.pagination?.current_page || 1;
                    lastPage = response.pagination?.last_page || 1;

                    // Clear and update data map
                    window.debitNoteDataMap = {};

                    response.data.forEach(function (item, index) {
                        // Store item in map for expansion
                        window.debitNoteDataMap[item.id] = item;

                        let displayName = 'N/A';
                        let transactionNumber = 'N/A';

                        if (item.transaction_type === 'receipt') {
                            displayName = (item.order && item.order.user) ? item.order.user.name : 'N/A';
                            transactionNumber = item.order ? item.order.order_number : 'N/A';
                        } else {
                            displayName = (item.purchase_invoice && item.purchase_invoice.vendor)
                                ? item.purchase_invoice.vendor.name
                                : 'N/A';
                            transactionNumber = item.purchase_invoice ? (item.purchase_invoice.bill_no || item.purchase_invoice.invoice_number) : 'N/A';
                        }

                        let typeName = item.credit_note_type
                            ? item.credit_note_type.type_name
                            : 'N/A';

                        let rowNode = table.row.add([
                            `
                            <span class="debit-note-mobile-party">${displayName}</span>
                                <span class="debit-note-mobile-ref">${transactionNumber}</span>
                            `,
                            `<button class="mobile-toggle-btn-table" onclick="toggleDebitNoteRowDetails('${item.id}')">+</button>`,
                            displayName,
                            typeName,
                            formatCurrency(item.grand_total),
                            formatCurrency(item.settlement_amount),
                            formatCurrency(item.total),
                            `<div class="action-buttons">${buildDebitNoteActionLinks(item.id)}</div>`
                        ]).node();

                        $(rowNode).attr('data-item-id', item.id);
                    });

                    updatePaginationUI(response.pagination || {
                        current_page: 1,
                        last_page: 1,
                        per_page: perPage,
                        total: response.data.length
                    });

                    $('#total_settlement_amount').text(parseFloat(response.total_settlement || 0).toFixed(2));
                    table.draw();
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    function updatePaginationUI(pagination) {
        let from = (pagination.current_page - 1) * pagination.per_page + 1;
        let to = pagination.current_page * pagination.per_page;

        if (to > pagination.total) {
            to = pagination.total;
        }

        if (pagination.total === 0) {
            from = 0;
        }

        $('#debit-note-pagination-from').text(from);
        $('#debit-note-pagination-to').text(to);
        $('#debit-note-pagination-total').text(pagination.total);

        let paginationHtml = '';

        // Previous Button
        paginationHtml += `
            <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link debit-note-page-link" href="javascript:void(0);" data-page="${pagination.current_page - 1}">Previous</a>
            </li>
        `;

        // Show only 2 page numbers at a time as in saleslist
        const visiblePageCount = 2;
        let startPage = Math.floor((pagination.current_page - 1) / visiblePageCount) * visiblePageCount + 1;
        let endPage = Math.min(pagination.last_page, startPage + visiblePageCount - 1);

        // Show previous ellipsis if there are pages before startPage
        if (startPage > 1) {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link debit-note-page-link" href="javascript:void(0);" data-page="${startPage - 1}" data-action="prev-group">..</a>
                </li>
            `;
        }

        // Generate page numbers
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link debit-note-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Show next ellipsis if there are more pages after endPage
        if (endPage < pagination.last_page) {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link debit-note-page-link" href="javascript:void(0);" data-page="${endPage + 1}" data-action="next-group">..</a>
                </li>
            `;
        }

        // Next Button
        paginationHtml += `
            <li class="page-item ${pagination.current_page === pagination.last_page || pagination.last_page === 0 ? 'disabled' : ''}">
                <a class="page-link debit-note-page-link" href="javascript:void(0);" data-page="${pagination.current_page + 1}">Next</a>
            </li>
        `;

        $('#debit-note-pagination-numbers').html(paginationHtml);
        // Always show the counter; only hide page buttons when there's no data
        $('.pagination-controls').show();
        $('#debit-note-pagination-numbers').toggle(pagination.total > 0);
    }

    $(document).on('click', '.debit-note-page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        let action = $(this).data('action');

        // Handle ellipsis clicks to load next/previous groups
        if (action === 'next-group') {
            if (page && page <= lastPage) {
                loadDebitNoteItems(page);
            }
            return;
        }

        if (action === 'prev-group') {
            let prevStartPage = Math.max(1, page - 2);
            if (prevStartPage >= 1 && prevStartPage <= lastPage) {
                loadDebitNoteItems(prevStartPage);
            }
            return;
        }

        if (page && page !== currentPage && page >= 1 && page <= lastPage) {
            loadDebitNoteItems(page);
        }
    });

    loadDebitNoteItems(currentPage);

    $(document).on('click', '.delete-item', function() {
        if (!canDeleteDebitNoteItem) return;

        let id = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ff9f43",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/debit-note-items/delete/${id}`,
                    type: "POST",
                    headers: { "Authorization": "Bearer " + authToken },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire("Deleted!", response.message, "success");
                            if (currentPage > 1 && table.rows().count() === 1) {
                                currentPage--;
                            }
                            loadDebitNoteItems(currentPage);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
