@extends('layout.app')

@section('title', 'Purchase List')

@section('content')
    <style>
        #DataTables_Table_0_info {
            float: left;
        }

        .table-scroll-top {
            display: none;
        }

        .input-groupicon.me-2.dateFilterclass {
            width: 105px !important;    
        }

        .form-control {
            color: #595b5d !important;
        }

        .form-group {
            margin-bottom: 8px !important;
        }

        .purchase-filter-card {
            background: #fff;
            /* border: 1px solid #e5e7eb; */
            /* border-radius: 12px; */
            padding: 16px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .purchase-filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .purchase-filter-row .search-set,
        .purchase-filter-row .form-control,
        .purchase-filter-row .btn {
            height: 40px;
            border-radius: 8px;
        }

        .purchase-filter-row .form-control,
        .purchase-filter-row .search-input input {
            border: 1px solid #d1d5db;
            box-shadow: none;
            font-size: 14px;
            color: #111827 !important;
            background: #fff;
        }

        .purchase-filter-row .form-control::placeholder,
        .purchase-filter-row input::placeholder {
            color: #9ca3af;
        }

        .mobile-invoice-vendor {
            display: none;
        }

        .summary-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: flex-start;
            margin-top: 14px;
        }

        .summary-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid transparent;
            font-size: 14px;
            line-height: 1;
            white-space: nowrap;
        }

        .summary-pill strong {
            font-weight: 700;
        }

        .summary-pill-pending {
            background: #fee2e2;
            border-color: #fecaca;
            color: #dc2626;
        }

        .summary-pill-paid {
            background: #dcfce7;
            border-color: #bbf7d0;
            color: #16a34a;
        }

        .summary-pill-total {
            background: #ffffff;
            border-color: #d1d5db;
            color: #111827;
        }

        .summary-actions {
            display: inline-flex;
            gap: 10px;
            margin-left: auto;
        }

        .summary-actions .btn {
            height: 40px;
            border-radius: 8px;
            min-width: 86px;
            font-weight: 600;
        }

        .page-btn .btn + .btn {
            margin-left: 10px;
        }

        .page-btn .btn-warning {
            background: #ff9f43;
            border-color: #ff9f43;
            color: #fff;
        }

        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            /* Filter Section Mobile Styles */
            /* .table-top {
                        padding: 10px 0;
                    }

                    .table-top > div {
                        flex-direction: column !important;
                        gap: 10px !important;
                    } */

            .search-set {
                width: 100% !important;
                margin: 0 0 10px 0 !important;
            }

            .search-input {
                width: 100%;
            }

            .mobile-invoice-cell {
                display: flex;
                flex-direction: column;
                gap: 2px;
                line-height: 1.2;
            }

            .mobile-invoice-vendor {
                display: block;
                color: #6b7280;
                font-size: 12px;
                text-transform: capitalize;
                white-space: normal;
            }

            /* .search-input .btn-searchset {
                        width: 100%;
                        padding: 10px;
                    } */
            /*
                    .table-top .me-2 {
                        margin-right: 0 !important;
                        margin-bottom: 8px;
                    } */

            /* Total display on mobile */
            /* .table-top > div > div[style*="border"] {
                        width: 100% !important;
                        text-align: center;
                        padding: 8px !important;
                        margin: 0 0 10px 0 !important;
                    } */

            /* Filter dropdowns - full width on mobile */
            .input-groupicon {
                width: 100% !important;
                margin: 0 0 10px 0 !important;
            }

            .input-groupicon select,
            .input-groupicon input {
                width: 100% !important;
                font-size: 14px;
            }

            .dateFilterclass {
                width: 100% !important;
            }

            .dataTables_filter {
                display: none !important;
            }

            .purchase-filter-row > div {
                flex: 1 1 100% !important;
                min-width: auto !important;
            }

            .purchase-filter-row > div:nth-child(2),
            .purchase-filter-row > div:nth-child(3),
            .purchase-filter-row > div:nth-child(4),
            .purchase-filter-row > div:nth-child(5) {
                flex: 0 0 calc(50% - 6px) !important;
            }

            .purchase-filter-row > div:nth-child(1),
            .purchase-filter-row > div:nth-child(6) {
                flex: 1 1 100% !important;
            }

            .purchase-filter-row > div > * {
                width: 100% !important;
            }

            /* .summary-strip {
                justify-content: flex-start;
            } */
            .summary-strip {
    flex-direction: column;
    align-items: stretch;
}

.summary-pill {
    width: 100%;
    justify-content: space-between;
    box-sizing: border-box;
}

            .summary-actions {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .summary-actions .btn {
                flex: 1 1 calc(50% - 6px);
                min-width: 0;
            }

            .dataTables_filter {
                display: none !important;
            }

            .purchase-filter-row > div {
                flex: 1 1 100% !important;
                min-width: auto !important;
            }

            .purchase-filter-row > div:nth-child(2),
            .purchase-filter-row > div:nth-child(3),
            .purchase-filter-row > div:nth-child(4),
            .purchase-filter-row > div:nth-child(5) {
                flex: 0 0 calc(50% - 6px) !important;
            }

            .purchase-filter-row > div:nth-child(1),
            .purchase-filter-row > div:nth-child(6) {
                flex: 1 1 100% !important;
            }

            .purchase-filter-row > div > * {
                width: 100% !important;
            }

            .summary-strip {
                justify-content: flex-start;
            }

            .summary-actions {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .summary-actions .btn {
                flex: 1 1 calc(50% - 6px);
                min-width: 0;
            }

            .mobile-invoice-vendor {
                display: block;
            }

            /* Export buttons - full width on mobile */
            /* .table-top .d-flex.gap-2 {
                        width: 100% !important;
                        flex-direction: column;
                        gap: 8px !important;
                    }

                    .table-top .d-flex.gap-2 button {
                        width: 100% !important;
                        padding: 10px;
                        font-size: 14px;
                    } */

            .table-responsive {
                display: block !important;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                width: 100% !important;
            }

            .datanew {
                width: 100% !important;
                table-layout: fixed;
            }

            /* Make Invoice Number column take more space */
            .datanew th:nth-child(1),
            .datanew td:nth-child(1) {
                width: 70% !important;
                text-align: left;
            }

            /* Make Details column take less space */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                width: 30% !important;
                text-align: center;
                display: table-cell !important;
            }

            .mobile-order-card {
                display: none;
            }

            /* Hide filter inputs card on mobile */
            #filter_inputs {
                display: none;
            }

            .datanew {
                font-size: 11px;
            }

            .datanew th,
            .datanew td {
                padding: 6px 3px;
            }

            /* Show only Invoice Number and Details - hide all other columns including Action */
            .datanew th:nth-child(3),
            .datanew td:nth-child(3),
            .datanew th:nth-child(4),
            .datanew td:nth-child(4),
            .datanew th:nth-child(5),
            .datanew td:nth-child(5),
            .datanew th:nth-child(6),
            .datanew td:nth-child(6),
            .datanew th:nth-child(7),
            .datanew td:nth-child(7),
            .datanew th:nth-child(8),
            .datanew td:nth-child(8) {
                display: none;
            }

            /* Center Details column */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                text-align: center;
                display: table-cell !important;
            }
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            /* Filter Section Small Mobile Styles */
            /* .table-top {
                        padding: 12px 0;
                    }

                    .table-top > div {
                        flex-wrap: wrap !important;
                        gap: 10px !important;
                    } */

            .search-set {
                width: 100% !important;
                margin: 0 0 10px 0 !important;
            }

            /* Total and filters in a row */
            /* .table-top > div > div[style*="border"] {
                        flex: 0 0 auto;
                        margin: 0 0 10px 0 !important;
                    } */

            .input-groupicon {
                flex: 0 0 calc(50% - 5px);
                margin: 0 0 10px 0 !important;
            }

            .input-groupicon select,
            .input-groupicon input {
                width: 100% !important;
                font-size: 14px;
            }

            .dateFilterclass {
                width: 100% !important;
            }

            /* Export buttons side by side */
            /* .table-top .d-flex.gap-2 {
                        width: 100% !important;
                        flex-direction: row;
                        gap: 10px !important;
                    }

                    .table-top .d-flex.gap-2 button {
                        flex: 1;
                        padding: 10px;
                        font-size: 14px;
                    } */

            .table-responsive {
                display: block !important;
                overflow-x: auto;
                width: 100% !important;
            }

            .datanew {
                width: 100% !important;
                table-layout: fixed;
            }

            /* Make columns take appropriate space */
            .datanew th:nth-child(1),
            .datanew td:nth-child(1) {
                width: 50% !important;
            }

            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                width: 20% !important;
                text-align: center;
                display: table-cell !important;
            }

            .datanew th:nth-child(3),
            .datanew td:nth-child(3) {
                width: 30% !important;
            }

            .mobile-order-card {
                display: none;
            }

            /* Hide filter inputs card on small mobile */
            #filter_inputs {
                display: none;
            }

            .datanew {
                font-size: 12px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 4px;
            }

            /* Show Invoice Number, Details, Date, Vendor - hide Action and other columns */
            .datanew th:nth-child(5),
            .datanew td:nth-child(5),
            .datanew th:nth-child(6),
            .datanew td:nth-child(6),
            .datanew th:nth-child(7),
            .datanew td:nth-child(7),
            .datanew th:nth-child(8),
            .datanew td:nth-child(8) {
                display: none;
            }

            /* Center Details column */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                text-align: center;
                display: table-cell !important;
            }
        }

        /* Medium devices (tablets, 768px and up to 991px) */
        @media screen and (min-width: 768px) and (max-width: 991.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
                width: 100% !important;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 13px;
                width: 100% !important;
                table-layout: auto;
            }

            .datanew th,
            .datanew td {
                padding: 8px 6px;
            }

            /* Hide Details column on tablets */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none !important;
            }

            /* Show all other columns on tablets - no hiding */
            /* All columns (Invoice Number, Date, Vendor Name, Grand Total, Purchase Status, Payment Status, Action) will be visible */

            /* Hide expandable rows on tablets */
            .order-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 992px and up) */
        @media screen and (min-width: 992px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
                width: 100% !important;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 14px;
                width: 100% !important;
            }

            .datanew th,
            .datanew td {
                padding: 12px 10px;
            }

            /* Hide Details column (2nd column) on desktop (992px and above) */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none !important;
            }

            /* Hide expandable rows on larger screens */
            .order-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .order-details-row {
            display: none;
        }

        .order-details-row.show {
            display: table-row;
        }

        /* Expandable content styles - available for all screen sizes */
        .order-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .order-details-list {
            margin-bottom: 15px;
        }

        .order-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .order-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .order-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }

        .mobile-action-buttons-simple {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-icon-mobile,
        button.btn-icon-mobile {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #1b2850;
            background: transparent;
            transition: all 0.3s;
            border: 2px solid #1b2850;
            cursor: pointer;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        button.btn-icon-mobile {
            border: 2px solid #1b2850;
            background: transparent;
        }

        .btn-icon-mobile:hover {
            background: #1b2850;
            color: white;
            transform: scale(1.1);
        }

        .btn-icon-mobile i {
            font-size: 16px;
        }

        .btn-icon-mobile.btn-history i {
            font-size: 18px;
        }

        .mobile-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .btn-icon-mobile.btn-view i,
        .btn-icon-mobile.btn-edit i,
        .btn-icon-mobile.btn-download i,
        .btn-icon-mobile.btn-print i,
        .btn-icon-mobile.btn-delete i,
        .btn-icon-mobile.btn-payment i {
            font-size: 16px;
        }

        /* Toggle button styles - available for all screen sizes */
        .mobile-toggle-btn-table {
            background: #ff9f43;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .mobile-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .mobile-toggle-btn-table.minus {
            background: #dc3545;
        }

        .mobile-toggle-btn-table.minus:hover {
            background: #c82333;
        }

        /* Additional responsive adjustments for larger screens */
        @media screen and (min-width: 992px) {
            .order-details-content {
                padding: 20px;
            }

            .order-detail-label-simple,
            .order-detail-value-simple {
                font-size: 15px;
            }

            .btn-icon-mobile {
                width: 42px;
                height: 42px;
            }

            .btn-icon-mobile i {
                font-size: 17px;
            }
        }

        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
            }
        }
        .purchase-action-dropdown {
    display: inline-flex;
    justify-content: center;
    width: 100%;
}

.purchase-action-dropdown .action-menu-toggle {
    align-items: center;
    background: #fff;
    border: 1px solid #d7dde8;
    border-radius: 4px;
    color: #1b2850;
    display: inline-flex;
    height: 28px;
    justify-content: center;
    padding: 0;
    width: 34px;
}

.purchase-action-dropdown .action-menu-toggle:hover,
.purchase-action-dropdown .action-menu-toggle:focus {
    background: #f8fafc;
    border-color: #b8c2d2;
    color: #1b2850;
}

.purchase-action-menu {
    border: 1px solid #f1f5f9 !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
    border-radius: 12px;
    min-width: 160px;
    padding: 8px;
    z-index: 999999999999;
}

.purchase-action-menu-floating {
    position: fixed !important;
    z-index: 999999999999 !important;
    bottom: auto !important;
    right: auto !important;
    height: auto !important;
    min-height: 0 !important;
    margin: 0 !important;
}

.purchase-action-menu .dropdown-item:hover {
    background-color: #f8fafc !important;
    color: #ff9f43 !important;
    transform: translateX(4px);
}

.purchase-action-menu .dropdown-item {
    align-items: center;
    border-radius: 4px;
    color: #344054;
    display: flex;
    font-size: 12px;
    gap: 8px;
    padding: 7px 8px;
    width: 100%;
}

.purchase-action-menu .dropdown-item i {
    color: #667085;
    font-size: 13px;
    text-align: center;
    width: 15px;
}

.purchase-action-menu .dropdown-item.text-danger,
.purchase-action-menu .dropdown-item.text-danger i {
    color: #ea5455 !important;
}

.purchase-action-menu .dropdown-item:hover {
    background: #f4f6f8;
    color: #1b2850;
}

.datanew td:last-child {
    text-align: center;
    white-space: nowrap;
}
    </style>
    @if (session('error'))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') }}
        </div>

        <style>
            .fade-out {
                opacity: 1;
                transition: opacity 0.5s ease-out;
            }

            .fade-out.hidden {
                opacity: 0;
            }
            
        </style>

        <script>
            setTimeout(function() {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden'); // Triggers the fade-out transition
                    // Remove the element from DOM after fadeout (optional)
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500); // match the CSS transition duration (0.5s)
                }
            }, 4000);
        </script>
    @endif
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Purchases List</h4>
                <!-- <h6>Manage your purchases</h6> -->
            </div>
            <div class="page-btn">
                <!-- <a href="javascript:void(0);" class="btn btn-sm btn-warning text-white me-2" id="open-import-modal-top">
                    <i class="fas fa-upload"></i> Import
                </a> -->
                @if (app('hasPermission')(3, 'add'))
                    <a href="{{ route('purchase.add') }}" class="btn btn-sm btn-added">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img">New Purchases
                    </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="purchase-filter-card mb-3">
                    <div class="purchase-filter-row">
                        <div style="flex: 1 1 180px; min-width: 180px;">
                            <input type="text" id="purchase-search" class="form-control form-control-sm"
                                placeholder="Search..." autocomplete="off">
                        </div>
                        <div style="flex: 0 1 150px; min-width: 150px;">
                            <select id="filter-month" class="form-control form-control-sm">
                                <option value="">-- Select Month --</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div style="flex: 0 1 150px; min-width: 150px;">
                            <select id="filter-year" class="form-control form-control-sm">
                                <option value="">-- Select Year --</option>
                            </select>
                        </div>
                        <div style="flex: 0 1 170px; min-width: 170px;">
                            <select id="filter-financial-year" class="form-control form-control-sm">
                                <option value="">-- All Financial Year --</option>
                            </select>
                        </div>
                        <div style="flex: 0 1 150px; min-width: 150px;">
                            <input type="text" id="filter-date" placeholder="Choose Date"
                                class="datetimepicker form-control form-control-sm">
                        </div>
                        <div style="flex: 0 1 180px; min-width: 180px;">
                            <select id="filter-vendor" class="form-control form-control-sm">
                                <option value="">-- Select Vendor --</option>
                            </select>
                        </div>
                    </div>
                    <div class="summary-strip">
                        <span class="summary-pill summary-pill-pending">
                            <span>Total Pending:</span> <strong id="summary-pending">₹0.00</strong>
                        </span>
                        <span class="summary-pill summary-pill-paid">
                            <span>Total Paid:</span> <strong id="summary-paid">₹0.00</strong>
                        </span>
                        <span class="summary-pill summary-pill-total">
                            <span>Total:</span> <strong id="summary-total">₹0.00</strong>
                        </span>
                        <div class="summary-actions">
                            <button id="exportAllChallan" class="btn btn-sm btn-success">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button id="exportPdf" class="btn btn-sm btn-danger">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
                {{-- <div class="table-top mb-3">
                    <div class="align-items-center d-flex flex-wrap gap-0 justify-content-end w-100">

                        <!-- Search -->
                        <div class="align-items-center d-flex me-2 me-auto search-set">
                            <div class="search-path"></div>
                            <div class="search-input">
                                <a class="btn btn-searchset">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                        alt="img">
                                </a>
                            </div>
                        </div>

                        <!-- Total (only for admin/sub-admin) -->
                        @if (in_array(auth()->user()->role, ['admin', 'sub-admin']))
                            <div class="me-2"
                                style="color: #1b2850;border: 3px solid #0d1b3e;border-radius: 5px;padding: 4px;font-size: 14px;     font-weight: bold;">
                                Total: <span style="color: #ff9f43" id="filtered-total">â‚¹0.00</span>
                            </div>
                        @endif

                        <!-- Year Filter -->
                        <div class="input-groupicon me-2">
                            <select id="filter-year" class="form-control form-control-sm">
                                <option value="">-- Select Year --</option>
                            </select>
                        </div>

                        <!-- Month Filter -->
                        <div class="input-groupicon me-2">
                            <select id="filter-month" class="form-control form-control-sm">
                                <option value="">-- Select Month --</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div class="input-groupicon me-2 dateFilterclass">
                            <input type="text" id="filter-date" placeholder="Choose Date"
                                class="datetimepicker form-control form-control-sm">
                        </div>

                        <!-- Customer Filter -->
                        <div class="input-groupicon me-2">
                            <select id="filter-customer" class="form-control form-control-sm">
                                <option value="">-- Select Vendor --</option>
                            </select>
                        </div>

                        <!-- Export Buttons -->
                        <div class="d-flex align-items-center gap-2">
                            <button id="exportAllChallan" class="btn btn-sm btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button id="exportPdf" class="btn btn-sm btn-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </div>

                    </div>
                </div>
 --}}

                <!-- Filter Inputs Card -->
                <div class="card" id="filter_inputs">
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Name" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Reference No" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <select class="form-select">
                                        <option>Completed</option>
                                        <option>Paid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group d-flex justify-content-end">
                                    <a class="btn btn-filters ms-auto">
                                        <img src="admin/assets/img/icons/search-whites.svg" alt="img">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="table-scroll-top"
                    style="overflow-x: auto; overflow-y: hidden; height: 20px; margin-bottom: 5px;">
                    <div style="height: 1px;"></div> <!-- Adjust width to match your table width -->
                </div>

                <div class="table-responsive mt-3" style="overflow-x: auto;">
                    <table class="table datanew" id="order-table" style="max-width: 2000px;">
                        <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th class="text-center">Details</th>
                                <th>Date</th>
                                <th>Vendor Name</th>
                                <th>Grand Total</th>
                                <th>Purchase Status</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS will populate this -->
                        </tbody>

                    </table>
                </div>

                <!-- Mobile Order Cards -->
                <div class="mobile-order-card mt-3" id="mobile-order-container">
                    <!-- JS will populate this -->
                </div>


            </div>
        </div>

    </div>
    <form id="makePaymentForm">
        <div class="modal fade" id="makePaymentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">

                        <div class="text-end mb-3">
                            <button type="button" class="btn btn-sm btn-cancel text-white" id="viewHistoryBtn">View
                                Payment History</button>
                        </div>

                        <!-- âœ… Payment History Container -->
                        <div id="paymentHistoryBox" class="border p-2 rounded bg-white d-none mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Payment History</h6>

                                <!-- ðŸ‘‡ Visible Close Button -->
                                <button type="button" class="btn btn-sm btn-danger" id="closeHistoryBtn">
                                    &times; <!-- This renders an â€œÃ—â€ symbol -->
                                </button>
                            </div>

                            <ul id="paymentHistoryList" class="list-unstyled mb-0"
                                style="max-height: 200px; overflow-y: auto;">
                                <!-- Populated via JavaScript -->
                            </ul>
                        </div>



                        <div class="border p-2 rounded bg-light">
                            <strong>Total Amount:</strong> ₹<span id="emiTotal"></span><br>
                            <strong>Remaining Amount:</strong> ₹<span id="remainingAmountDisplay">0.00</span>

                        </div>

                        <!-- âœ… View Payment History Button -->



                        <br>

                        <!-- Payment Method -->
                        <div class="mb-3" id="paymentMethodDiv">
                            <label for="paymentMethodSelect" class="form-label">Select Payment Method</label>
                            <select class="form-select" id="paymentMethodSelect" name="payment_method">
                                <option value="" selected disabled>Select</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="cash_online">Cash + Online</option>

                            </select>
                            <div class="text-danger" id="paymentMethodError"></div>
                        </div>

                        <div class="mb-3 d-none" id="cashOnlineTypeDiv">
                            <label for="cashOnlineTypeSelect" class="form-label">Select Cash + Online Type</label>
                            <select class="form-select" id="cashOnlineTypeSelect" name="cash_online_type">
                                <option value="" selected disabled>Select</option>
                                <option value="cash_online_fully">Cash + Online Fully</option>
                                <option value="cash_online_partially">Cash + Online Partially</option>
                            </select>
                            <div class="text-danger" id="cashOnlineTypeError"></div>
                        </div>

                        <!-- Fully Cash + Online -->
                        <!-- Fully Cash + Online -->
                        <div class="mb-3 d-none" id="fullyCashOnlineFields">
                            <label>Cash Amount</label>
                            <input type="number" id="cashOnlineFullAmount" name="fully_cash_amount"
                                class="form-control" min="0" step="0.01">
                            <div class="text-danger" id="cashOnlineFullAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="number" id="upiOnlineFullAmount" name="full_online_amount"
                                class="form-control" readonly>
                            <div class="text-danger" id="upiOnlineFullAmountError"></div>
                        </div>

                        <!-- Partial Cash + Online -->
                        <div class="mb-3 d-none" id="partialCashOnlineFields">
                            <label>Cash Amount</label>
                            <input type="number" id="cashOnlinePartialAmount" name="cash_amount" class="form-control"
                                min="0" step="0.01">
                            <div class="text-danger" id="cashOnlinePartialAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="number" id="upiOnlinePartialAmount" name="online_amount" class="form-control"
                                min="0" step="0.01">
                            <div class="text-danger" id="upiOnlinePartialAmountError"></div>
                            <label class="mt-2">Remaining Amount</label>
                            <input type="number" id="remainingCashOnlineAmount" name="remaining_amount"
                                class="form-control" readonly>
                        </div>







                        <div class="mb-3 d-none" id="onlineTypeDiv">
                            <label for="onlineTypeSelect" class="form-label">Select Online Type</label>
                            <select class="form-select" id="onlineTypeSelect" name="online_type">
                                <option value="" selected disabled>Select</option>
                                <option value="online_fully">Online Fully</option>
                                <option value="online_partially">Online Partially</option>
                            </select>
                            <div class="text-danger" id="onlineTypeError"></div>
                        </div>

                        <!-- Paid Type Dropdown (Hidden by default) -->
                        <div class="mb-3 d-none" id="paidTypeDiv">
                            <label for="paidTypeSelect" class="form-label">Paid Type</label>
                            <select class="form-select" id="paidTypeSelect" name="paid_type">
                                <option value="" selected disabled>Select</option>
                                <option value="cash_partially">Cash Partially</option>
                                <option value="cash_fully">Cash Fully</option>
                            </select>
                            <div class="text-danger" id="paidTypeError"></div>
                        </div>

                        <!-- UPI Amount Input -->
                        <div class="mb-3 d-none" id="upiAmountDiv">
                            <label for="upiAmountInput" class="form-label">Online Amount</label>
                            <input type="number" class="form-control" id="upiAmountInput" name="upi_online_amount"
                                readonly>
                            <div class="text-danger" id="upiAmountError"></div>
                        </div>



                        <!-- Partially Paid Fields -->
                        <div class="mb-3 d-none" id="partialPaidFields">
                            <label for="partialAmount" class="form-label">Enter Amount</label>
                            <input type="number" class="form-control mb-2" id="partialAmount" name="amount"
                                min="1" step="0.01">
                            <div class="text-danger" id="partialAmountError"></div>

                            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                <div style="flex: 1;">
                                    <label for="pendingAmount" class="form-label">Pending Amount</label>
                                    <input type="number" class="form-control" id="pendingAmount"
                                        name="cash_pending_amount" readonly>
                                </div>

                            </div>
                        </div>

                        <!-- Fully Paid Fields -->
                        <div class="mb-3 d-none" id="fullyPaidFields">
                            <label class="form-label">Cash Amount</label>
                            <input type="number" class="form-control" id="cashAmount" name="cashAmount" min="0"
                                step="0.01">
                            <div class="text-danger" id="cashAmountError"></div>
                        </div>













                        <!-- Cleaned Hidden Inputs (no duplicate name attributes) -->
                        <input type="hidden" id="paymentJobCardId" name="purchase_id">
                        <input type="hidden" id="remainingAmountHidden" name="remaining_amount">
                        <input type="hidden" id="paymentMethodHidden" name="payment_type">




                        <div class="text-end">
                            <button type="submit" class="btn btn-submit text-white"
                                style="background-color: #ff9f43;">Submit Payment</button>
                            <button type="button" class="btn btn-secondary btn-cancel"
                                data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Global Payment History Modal -->
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentHistoryLabel">Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="globalPaymentHistoryList" class="list-group"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Global variables
        var purchaseTable;

        // Helper function to build expandable row content
        function buildPurchaseExpandableRowContent(purchase, currencySymbol, currencyPosition) {
            const amount = parseFloat(purchase.grand_total || 0).toFixed(2);
            const displayAmount = currencyPosition === 'right' ?
                amount + currencySymbol : currencySymbol + amount;

            let actionBtns = '';

            // Make Payment button (orange button) - only if there's remaining amount
            if (parseFloat(purchase.remaining_amount || 0) > 0) {
                actionBtns += `<button type="button" class="btn btn-sm btn-primary me-3 make-payment-btn" 
                    data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                    data-id="${purchase.id}" 
                    data-amount="${purchase.remaining_amount}" 
                    data-method="${purchase.payment_mode || ''}"
                    data-total-amount="${purchase.grand_total || 0}" 
                    data-remaining-amount="${purchase.remaining_amount}"
                    style="background-color: #ff9f43; border-color: #ff9f43; color: white;">
                    Make Payment
                </button>`;
            }

            // History icon button
            actionBtns += `<button class="btn-icon-mobile btn-history open-history" data-id="${purchase.id}" title="Payment History">
                <i class="fas fa-history"></i>
            </button>`;

            // Download icon button
            @if (app('hasPermission')(3, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-download" href="/print-purchase/${purchase.id}" title="Download Invoice">
                    <i class="fas fa-download"></i>
                </a>`;
            @endif

            // Print icon button
            @if (app('hasPermission')(3, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-print" href="javascript:void(0);" onclick="window.open('/purchase/invoice/pdf/' + ${purchase.id});" title="Print Invoice">
                    <i class="fas fa-print"></i>
                </a>`;
            @endif

            // Delete icon button
            @if (app('hasPermission')(3, 'delete'))
                actionBtns += `<a class="btn-icon-mobile btn-delete delete-order" href="javascript:void(0);" data-id="${purchase.id}" title="Delete">
                    <i class="fas fa-trash"></i>
                </a>`;
            @endif

            return `
                <td colspan="8" class="order-details-content">
                    <div class="order-details-list">
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Date:</span>
                            <span class="order-detail-value-simple">${purchase.invoice_date || purchase.date || purchase.created_at || 'N/A'}</span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Vendor Name:</span>
                            <span class="order-detail-value-simple">${purchase.vendor_name || 'N/A'}</span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Purchase Status:</span>
                            <span class="order-detail-value-simple">
                                <span class="mobile-badge bg-lightgreen">${purchase.purchase_status || purchase.status || 'N/A'}</span>
                            </span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Payment Status:</span>
                            <span class="order-detail-value-simple">
                                <span class="mobile-badge bg-lightgreen">${purchase.payment_status || 'N/A'}</span>
                            </span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Grand Total:</span>
                            <span class="order-detail-value-simple" style="font-weight: bold; color: #ff9f43;">${displayAmount}</span>
                        </div>
                        ${parseFloat(purchase.remaining_amount || 0) > 0 ? `
                                <div class="order-detail-row-simple">
                                    <span class="order-detail-label-simple">Remaining:</span>
                                    <span class="order-detail-value-simple" style="color: #dc3545; font-weight: bold;">
                                        ${currencySymbol}${parseFloat(purchase.remaining_amount || 0).toFixed(2)}
                                    </span>
                                </div>
                                ` : ''}
                    </div>
                    <div class="mobile-action-buttons-simple">
                        ${actionBtns}
                    </div>
                </td>
            `;
        }

        // Toggle function for table rows - must be global
        window.togglePurchaseRowDetails = function(purchaseId) {
            // Find the button that was clicked
            const btn = $(`.mobile-toggle-btn-table[data-purchase-id="${purchaseId}"]`);
            if (btn.length === 0) {
                console.error('Toggle button not found for purchase:', purchaseId);
                return;
            }

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.order-details-row[data-purchase-id="${purchaseId}"]`);
            const icon = btn.find('.toggle-icon');

            // If expandable row doesn't exist, create it
            if (detailsRow.length === 0) {
                const purchaseData = window.purchaseDataMap && window.purchaseDataMap[purchaseId];
                if (purchaseData) {
                    detailsRow = $('<tr>')
                        .addClass('order-details-row')
                        .attr('data-purchase-id', purchaseId)
                        .html(buildPurchaseExpandableRowContent(purchaseData, purchaseData.currencySymbol, purchaseData
                            .currencyPosition));
                    row.after(detailsRow);
                } else {
                    console.error('Purchase data not found for purchase:', purchaseId);
                    return;
                }
            }

            if (detailsRow.hasClass('show')) {
                detailsRow.removeClass('show');
                btn.removeClass('minus');
                icon.text('+');
            } else {
                detailsRow.addClass('show');
                btn.addClass('minus');
                icon.text('-');
            }
        }

        // Function to add expandable rows - must be global
        window.addPurchaseExpandableRows = function(dt) {
            if (!dt) return;

            const currentWidth = $(window).width();
            const isMobileOrTablet = currentWidth <= 1024;

            if (!isMobileOrTablet) {
                // Remove expandable rows on desktop
                $('tr.order-details-row').remove();
                return;
            }

            dt.rows().every(function() {
                const row = this.node();
                const toggleBtn = $(row).find('.mobile-toggle-btn-table');
                if (toggleBtn.length > 0) {
                    const purchaseId = toggleBtn.data('purchase-id');
                    const purchaseData = window.purchaseDataMap && window.purchaseDataMap[purchaseId];
                    if (purchaseData && !$(row).next('tr.order-details-row[data-purchase-id="' + purchaseId +
                            '"]').length) {
                        const expandableRow = $('<tr>')
                            .addClass('order-details-row')
                            .attr('data-purchase-id', purchaseId)
                            .html(buildPurchaseExpandableRowContent(purchaseData, purchaseData.currencySymbol,
                                purchaseData.currencyPosition));
                        $(row).after(expandableRow);
                    }
                }
            });
        };

        // Function to calculate total for visible rows - must be global
        function calculatePurchaseFilteredTotal() {
            if (!purchaseTable) {
                purchaseTable = $('.datanew').DataTable();
            }

            let total = 0;

            // Find the Grand Total column index by header name
            let totalColumnIndex = -1;
            purchaseTable.columns().every(function() {
                const header = $(this.header());
                if (header.text().trim() === 'Grand Total') {
                    totalColumnIndex = this.index();
                    return false; // break
                }
            });

            // If column not found by name, use index 4 as fallback
            if (totalColumnIndex === -1) {
                totalColumnIndex = 4;
            }

            purchaseTable.rows({
                filter: 'applied'
            }).every(function() {
                const row = this.data();
                if (row[totalColumnIndex]) {
                    const amountText = row[totalColumnIndex];
                    const rawAmount = parseFloat(amountText.replace(/[^0-9.-]+/g, '')) || 0;
                    total += rawAmount;
                }
            });

            const currencySymbol = "â‚¹";
            $('#filtered-total').text(`${currencySymbol}${total.toFixed(2)}`);
        }

        function updatePurchaseSummaryCards(summary, currencySymbol) {
            const symbol = currencySymbol || '₹';
            $('#summary-pending').text(symbol + parseFloat(summary?.pending || 0).toFixed(2));
            $('#summary-paid').text(symbol + parseFloat(summary?.paid || 0).toFixed(2));
            $('#summary-total').text(symbol + parseFloat(summary?.total || 0).toFixed(2));
            $('#filtered-total').text(symbol + parseFloat(summary?.total || 0).toFixed(2));
        }

        function populateYearAndVendorFilters(rows) {
            const years = new Set();
            const vendors = new Set();
            const fy = new Set();
            rows.forEach(r => {
                const dateStr = r.date || r.invoice_date || '';
                const parsed = dateStr ? new Date(dateStr) : null;
                if (parsed && !isNaN(parsed.getTime())) {
                    const year = parsed.getFullYear();
                    years.add(year);
                    const fyStart = parsed.getMonth() >= 3 ? year : year - 1;
                    const fyEnd = fyStart + 1;
                    fy.add(`${fyStart}-${String(fyEnd).slice(-2)}`);
                }
                if (r.vendor_name) vendors.add(r.vendor_name);
            });
            const $year = $('#filter-year');
            $year.find('option:not(:first)').remove();
            Array.from(years).sort((a, b) => b - a).forEach(y => $year.append(`<option value="${y}">${y}</option>`));
            const $fy = $('#filter-financial-year');
            $fy.find('option:not(:first)').remove();
            Array.from(fy).sort().forEach(v => $fy.append(`<option value="${v}">${v}</option>`));
            const $vendor = $('#filter-vendor');
            $vendor.find('option:not(:first)').remove();
            Array.from(vendors).sort().forEach(v => $vendor.append(`<option value="${v}">${v}</option>`));
        }
        $(document).ready(function() {
            // Initialize table after data is loaded
            let currentYear = new Date().getFullYear();
            for (let i = 0; i < 4; i++) {
                let year = currentYear - i;
                $("#filter-year").append(`<option value="${year}">${year}</option>`);
            }

            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(function() {
                        func.apply(context, args);
                    }, wait);
                };
            }

            // const debouncedLoadPurchases = debounce(loadPurchases, 250);

            // $('#purchase-search').off('input').on('input', debouncedLoadPurchases);
            // $('#filter-month, #filter-year, #filter-financial-year, #filter-vendor').off('change').on('change', loadPurchases);

            const debouncedLoadPurchases = debounce(function() { window.loadPurchases(); }, 250);

$('#purchase-search').off('input').on('input', debouncedLoadPurchases);
$('#filter-month, #filter-year, #filter-financial-year, #filter-vendor').off('change').on('change', function() { window.loadPurchases(); });


            // Month short names
            const monthNames = {
                "01": "Jan",
                "02": "Feb",
                "03": "Mar",
                "04": "Apr",
                "05": "May",
                "06": "Jun",
                "07": "Jul",
                "08": "Aug",
                "09": "Sep",
                "10": "Oct",
                "11": "Nov",
                "12": "Dec"
            };

            // Apply month/year filters via API reload
            // $("#filter-month, #filter-year, #filter-financial-year").off('change').on("change", loadPurchases);
            $("#filter-month, #filter-year, #filter-financial-year").off('change').on("change", function() { window.loadPurchases(); });

            function applyMonthYearFilter() {
                // kept for backward compatibility, but actual filters now use API reload
                loadPurchases();
            }

            // alert("Purchase List Page Loaded");
            const authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            // console.log("Selected Sub Admin ID:", selectedSubAdminId);




            $(document).on('click', '.make-payment-btn', function() {
                let jobCardId = $(this).data('id');
                let totalAmount = $(this).data('total-amount');
                let remainingAmount = $(this).data('remaining-amount');
                let method = $(this).data('method') || '';

                // âœ… Fill modal hidden inputs + text spans
                $('#paymentJobCardId').val(jobCardId);
                $('#emiTotal').text(parseFloat(totalAmount).toFixed(2));
                $('#remainingAmountDisplay').text(parseFloat(remainingAmount).toFixed(2));
                $('#remainingAmountHidden').val(remainingAmount);
                $('#paymentMethodHidden').val(method);

                // âœ… Reset payment method dropdown to default
                $('#paymentMethodSelect').val('');

                // âœ… Hide history box initially
                $('#paymentHistoryBox').addClass('d-none');
                $('#paymentHistoryList').html('');

                // âœ… Bind View History button
                $('#viewHistoryBtn').off('click').on('click', function() {
                    $.ajax({
                        url: '/api/purchase/payment-history/' + jobCardId,
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        success: function(response) {
                            const history = response.data;

                            if (!history || history.length === 0) {
                                $('#paymentHistoryList').html(
                                    '<li>No payment history found.</li>');
                            } else {
                                let historyHtml = '';
                                history.forEach(function(payment) {
                                    historyHtml += `
                                                                            <li class="mb-2">
                                                                                <strong>Amount:</strong> â‚¹${parseFloat(payment.payment_amount).toFixed(2)}<br>
                                                                                <strong>Date:</strong> ${payment.payment_date}<br>
                                                                                <strong>Method:</strong> ${payment.payment_method}<br>
                                                                                <strong>Payment Type:</strong> ${payment.payment_type ? payment.payment_type : 'N/A'}<br>
                                                                                ${payment.payment_type === 'emi' ? `<strong>EMI Months:</strong> ${payment.emi_month || 0}<br>` : ''}
                                                                            </li>
                                                                            <hr class="my-1"/>
                                                                        `;
                                });
                                $('#paymentHistoryList').html(historyHtml);
                            }

                            $('#paymentHistoryBox').removeClass('d-none');
                        },
                        error: function() {
                            $('#paymentHistoryList').html(
                                '<li class="text-danger">Failed to load payment history.</li>'
                            );
                            $('#paymentHistoryBox').removeClass('d-none');
                        }
                    });
                });

                // âœ… Close history
                $('#closeHistoryBtn').off('click').on('click', function() {
                    $('#paymentHistoryBox').addClass('d-none');
                });
            });

            $('#paymentMethodSelect').on('change', function() {
                let method = $(this).val();

                // Hide all optional sections first
                $('#cashOnlineTypeDiv, #fullyCashOnlineFields, #partialCashOnlineFields, #onlineTypeDiv, #paidTypeDiv, #upiAmountDiv, #partialPaidFields, #fullyPaidFields')
                    .addClass('d-none');

                if (method === 'cash') {
                    $('#paidTypeDiv').removeClass('d-none'); // Show paid type options

                } else if (method === 'online') {
                    $('#onlineTypeDiv').removeClass('d-none'); // Show online type dropdown

                } else if (method === 'cash_online') {
                    $('#cashOnlineTypeDiv').removeClass('d-none'); // Show Cash + Online type dropdown
                }
            });

            $('#paidTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#partialPaidFields, #fullyPaidFields').addClass('d-none');

                // Disable all inputs first
                $('#partialPaidFields input, #fullyPaidFields input').prop('disabled', true);

                if (type === 'cash_partially') {
                    // Show partial fields
                    $('#partialPaidFields').removeClass('d-none');
                    $('#partialPaidFields input').prop('disabled', false);

                    // Clear & reset values
                    $('#partialAmount').val('');
                    $('#pendingAmount').val(remaining.toFixed(2));

                    // Remove any full cash amount
                    $('#cashAmount').val('').prop('readonly', false).prop('disabled', true);

                    // Live calculation for pending
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = parseFloat($(this).val()) || 0;
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(newPending.toFixed(2));
                    });

                } else if (type === 'cash_fully') {
                    // Show fully fields
                    $('#fullyPaidFields').removeClass('d-none');
                    $('#fullyPaidFields input').prop('disabled', false);

                    // Fill full amount & disable editing
                    $('#cashAmount').val(remaining.toFixed(2)).prop('readonly', true);

                    // Reset partial fields
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });


            // âœ… Handle Online Type (when Payment Method = Online)
            $('#onlineTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#upiAmountDiv, #partialPaidFields').addClass('d-none');
                // Disable all inputs first
                $('#upiAmountDiv input, #partialPaidFields input').prop('disabled', true);

                if (type === 'online_partially') {
                    // Show partial fields
                    $('#partialPaidFields').removeClass('d-none');
                    $('#partialPaidFields input').prop('disabled', false);

                    // Reset values
                    $('#partialAmount').val('');
                    $('#pendingAmount').val(remaining.toFixed(2));

                    // Clear and disable UPI field
                    $('#upiAmountInput').val('').prop('readonly', false).prop('disabled', true);

                    // Live pending update
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = parseFloat($(this).val()) || 0;
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(newPending.toFixed(2));
                    });

                } else if (type === 'online_fully') {
                    // Show fully online section
                    $('#upiAmountDiv').removeClass('d-none');
                    $('#upiAmountDiv input').prop('disabled', false);

                    // Fill with remaining and lock editing
                    $('#upiAmountInput').val(remaining.toFixed(2)).prop('readonly', true);

                    // Reset partial section
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });

            // âœ… Handle Cash + Online Type
            $('#cashOnlineTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#fullyCashOnlineFields, #partialCashOnlineFields').addClass('d-none');
                // Disable all inputs first
                $('#fullyCashOnlineFields input, #partialCashOnlineFields input').prop('disabled', true);

                if (type === 'cash_online_fully') {
                    // Show fully section
                    $('#fullyCashOnlineFields').removeClass('d-none');
                    $('#fullyCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlineFullAmount').val('');
                    $('#upiOnlineFullAmount').val(remaining.toFixed(2));

                    // Live adjustment of online amount
                    $('#cashOnlineFullAmount').off('input').on('input', function() {
                        let cash = parseFloat($(this).val()) || 0;
                        let online = Math.max(remaining - cash, 0);
                        $('#upiOnlineFullAmount').val(online.toFixed(2));
                    });

                    // Disable partial fields
                    $('#partialCashOnlineFields input').prop('disabled', true);

                } else if (type === 'cash_online_partially') {
                    // Show partial section
                    $('#partialCashOnlineFields').removeClass('d-none');
                    $('#partialCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlinePartialAmount, #upiOnlinePartialAmount').val('');
                    $('#remainingCashOnlineAmount').val(remaining.toFixed(2));

                    // Live update on cash input
                    $('#cashOnlinePartialAmount').off('input').on('input', function() {
                        let cash = parseFloat($(this).val()) || 0;
                        let newRemaining = Math.max(remaining - cash, 0);
                        $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
                    });

                    // Live update on online input
                    $('#upiOnlinePartialAmount').off('input').on('input', function() {
                        let online = parseFloat($(this).val()) || 0;
                        let cash = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
                        let newRemaining = Math.max(remaining - cash - online, 0);
                        $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
                    });

                    // Disable fully fields
                    $('#fullyCashOnlineFields input').prop('disabled', true);
                }
            });

            $('#makePaymentForm').on('submit', function(e) {
                e.preventDefault();


                let isValid = true;


                // Clear all previous errors
                $('.text-danger').text('');

                // Get values
                let paymentMethod = $('#paymentMethodSelect').val();
                let paymentTypeemionly = $('#paymentType').val();
                let paidType = $('#paidTypeSelect').val();
                let onlineType = $('#onlineTypeSelect').val();
                let cashOnlineType = $('#cashOnlineTypeSelect').val();
                let pendingAmount = $('#pendingAmount').val();


                // return false;
                // EMI Validation



                // Payment Method validation


                if (!paymentMethod) {
                    isValid = false;
                    $('#paymentMethodError').text("Please select a payment method.");
                    return false;
                } else {
                }


                // Cash Payment Validation
                if (paymentMethod === 'cash') {

                    if (!paidType) {
                        isValid = false;
                        $('#paidTypeError').text("Please select paid type.");
                        return false;
                    } else {
                    }

                    if (paidType === 'cash_partially') {
                        let partialAmount = parseFloat($('#partialAmount').val()) || 0;
                        let remainingAmount = parseFloat($('#remainingAmountHidden').val()) || 0;


                        if (!partialAmount || isNaN(partialAmount) || partialAmount <= 0) {
                            isValid = false;
                            $('#partialAmountError').text("Enter a valid positive partial cash amount.");
                            return false;
                        }

                        if (partialAmount > remainingAmount) {
                            isValid = false;
                            $('#partialAmountError').text(
                                "Partial cash amount cannot exceed remaining amount (" + remainingAmount
                                .toFixed(2) + ")."
                            );
                            return false;
                        }

                        if (partialAmount < 0) {
                            isValid = false;
                            $('#partialAmountError').text("Amount cannot be negative.");
                            return false;
                        }

                    } else {
                        let cashAmount = $('#cashAmount').val();
                        if (!cashAmount || parseFloat(cashAmount) <= 0) {
                            isValid = false;
                            $('#cashAmountError').text("Enter a valid cash amount.");
                            return false;
                        } else {
                        }
                    }
                }

                // Online Payment Validation
                if (paymentMethod === 'online') {

                    if (!onlineType) {
                        isValid = false;
                        $('#onlineTypeError').text("Please select online type.");
                        return false;
                    } else {
                    }

                    let onlineAmount = parseFloat($('#partialAmount').val()) || parseFloat($(
                        '#upiAmountInput').val()) || 0;
                    let remainingAmount = parseFloat($('#remainingAmountHidden').val()) || 0;


                    // âœ… Check 1: Must be a valid positive number
                    if (!onlineAmount || isNaN(onlineAmount) || onlineAmount <= 0) {
                        isValid = false;
                        if (onlineType === 'online_partially') {
                            $('#partialAmountError').text("Enter a valid positive online partial amount.");
                        } else {
                            $('#upiAmountError').text("Enter a valid positive online amount.");
                        }
                        return false;
                    }

                    // âœ… Check 2: Cannot exceed remaining
                    if (onlineType === 'online_partially' && onlineAmount > remainingAmount) {
                        // console.log(onlineType);
                        isValid = false;
                        $('#partialAmountError').text(
                            "Partial online amount cannot exceed remaining amount (" + remainingAmount
                            .toFixed(2) + ")."
                        );
                        return false;
                    }

                    // âœ… Check 3: Cannot be negative
                    if (onlineAmount < 0) {
                        isValid = false;
                        $('#partialAmountError').text("Amount cannot be negative.");
                        return false;
                    }

                }


                // Cash + Online Validation
                if (paymentMethod === 'cash_online') {

                    if (!cashOnlineType) {
                        isValid = false;
                        $('#cashOnlineTypeError').text("Please select Cash + Online type.");
                        return false;
                    } else {
                    }

                    if (cashOnlineType === 'cash_online_fully') {
                        let cashAmt = $('#cashOnlineFullAmount').val();
                        let onlineAmt = $('#upiOnlineFullAmount').val();

                        if (!cashAmt || parseFloat(cashAmt) <= 0 || !onlineAmt || parseFloat(onlineAmt) <=
                            0) {
                            isValid = false;
                            $('#cashOnlineFullAmountError').text("Enter a valid cash amount.");
                            $('#upiOnlineFullAmountError').text("Enter a valid online amount.");
                            return false;
                        } else {
                        }
                    }

                    if (cashOnlineType === 'cash_online_partially') {
                        let cashAmt = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
                        let onlineAmt = parseFloat($('#upiOnlinePartialAmount').val()) || 0;

                        // Clean pending amount
                        let rawPending = $('#remainingCashOnlineAmount').val() || "0";
                        rawPending = rawPending.replace(/[â‚¹,]/g, '').trim();
                        let pendingAmt = parseFloat(rawPending) || 0;


                        // âœ… Check for invalid or negative input
                        if ((cashAmt <= 0 && onlineAmt <= 0)) {
                            isValid = false;
                            $('#cashOnlinePartialAmountError').text("Enter at least one valid amount.");
                            $('#upiOnlinePartialAmountError').text("Enter at least one valid amount.");
                            return false;
                        }

                        if (cashAmt < 0 || onlineAmt < 0) {
                            isValid = false;
                            $('#cashOnlinePartialAmountError').text("Negative amount not allowed.");
                            $('#upiOnlinePartialAmountError').text("Negative amount not allowed.");
                            return false;
                        }

                        // âœ… Total should not exceed pending amount
                        let total = cashAmt + onlineAmt;

                        if (total > pendingAmt) {
                            isValid = false;
                            $('#cashOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (" + pendingAmt + ").");
                            $('#upiOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (" + pendingAmt + ").");
                            return false;
                        }

                    }

                }




                if (isValid) {
                    // this.submit(); // submit the form
                }
                let selectedPaymentType = $('#paymentType').val();
                $('#paymentMethodHidden').val(selectedPaymentType);

                if ($("#paymentMethodDiv").hasClass("d-none")) {
                    $("#newEmiHidden").prop("disabled", true).val("");
                }



                let formElement = $(this)[0];
                let formData = new FormData(formElement);

                let submitButton = $(this).find('button[type="submit"]');
                submitButton.prop('disabled', true).text('Processing...');
                if (paymentMethodSelect === 'emi') {
                    let emiTotal = $('#emiTotalCalculated').val();
                    formData.append('emi_paid_value', emiTotal);
                }

                if (selectedPaymentType === 'emi') {
                    let emi_val = $('#emiTotalCalculated').val();
                    formData.append('amount', emi_val);
                }

                $.ajax({
                    url: "{{ route('make-payment.purchase') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $('#makePaymentModal').modal('hide');
                        submitButton.prop('disabled', false).text('Submit Payment');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Payment submitted successfully.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        submitButton.prop('disabled', false).text('Submit Payment');

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMsg = '';
                            $.each(errors, function(key, value) {
                                errorMsg += value + '\n';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errorMsg
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong. Please try again.'
                            });
                        }
                    }
                });
            });


            $(function() {

                function formatDate(dateStr) {
                    if (!dateStr) return 'N/A';
                    const d = new Date(dateStr);
                    const day = String(d.getDate()).padStart(2, '0');
                    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct",
                        "Nov", "Dec"
                    ];
                    const month = months[d.getMonth()];
                    const year = d.getFullYear();
                    let hours = d.getHours();
                    const minutes = String(d.getMinutes()).padStart(2, '0');
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    return `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;
                }

                function badge(text, okClass, warnClass) {
                    const positiveStatuses = ['paid', 'completed', 'delivered', 'success'];
                    const isPositive = positiveStatuses.includes(String(text).toLowerCase());
                    const cls = isPositive ? okClass : warnClass;
                    return `<span class="badges ${cls}" style="text-transform:capitalize;">${text}</span>`;
                }

                function actionLinks(o) {
                    let menuItems = '';

                    if (parseFloat(o.remaining_amount) > 0) {
                        menuItems += `<button type="button" class="dropdown-item make-payment-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#makePaymentModal"
                            data-id="${o.id}"
                            data-amount="${o.remaining_amount}"
                            data-method="${o.payment_mode || ''}"
                            data-total-amount="${o.grand_total || 0}"
                            data-remaining-amount="${o.remaining_amount}">
                            <i class="fas fa-money-bill"></i><span>Pay</span>
                        </button>`;
                    }

                    menuItems += `
                        <button type="button" class="dropdown-item open-history" data-id="${o.id}">
                            <i class="fas fa-history"></i><span>History</span>
                        </button>
                        <a class="dropdown-item" href="/print-purchase/${o.id}">
                            <i class="fas fa-eye"></i><span>View</span>
                        </a>`;

                    @if (app('hasPermission')(3, 'edit'))
                        menuItems += `<a class="dropdown-item" href="/edit-purchase/${o.id}">
                            <i class="fas fa-edit"></i><span>Edit</span>
                        </a>`;
                    @endif

                    @if (app('hasPermission')(3, 'view'))
                        menuItems += `<a class="dropdown-item" href="javascript:void(0);" onclick="window.open('/purchase/invoice/pdf/' + ${o.id});">
                            <i class="fas fa-print"></i><span>Print</span>
                        </a>`;
                    @endif

                    @if (app('hasPermission')(3, 'delete'))
                        menuItems += `<a class="dropdown-item text-danger confirm-text delete-order" data-id="${o.id}" href="javascript:void(0);">
                            <i class="fas fa-trash"></i><span>Delete</span>
                        </a>`;
                    @endif

                    return `<div class="dropdown purchase-action-dropdown">
                        <button class="btn action-menu-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end purchase-action-menu">
                            ${menuItems || '<span class="dropdown-item text-muted">No actions</span>'}
                        </div>
                    </div>`;
                }

                // function loadPurchases() {
                window.loadPurchases = function loadPurchases() {
                    const rawDate = $('#filter-date').val();
                    const [day, month, year] = rawDate ? rawDate.split('-') : [null, null, null];
                    const formattedDate = rawDate ? `${year}-${month}-${day}` : '';

                    const filters = {
                        date: formattedDate,
                        selectedSubAdminId: selectedSubAdminId || null,
                        search: $('#purchase-search').val().trim(),
                        month: $('#filter-month').val() || '',
                        year: $('#filter-year').val() || '',
                        financial_year: $('#filter-financial-year').val() || '',
                        vendor_id: $('#filter-vendor').val() || ''
                    };

                    $.ajax({
                        url: "/api/purchase_list",
                        type: "GET",
                        dataType: "json",
                        data: filters,
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        success: res => {
                            const rows = [];

                            if (res.success && Array.isArray(res.data)) {
                                const currencySymbol = res.currency_symbol || 'â‚¹';
                                const currencyPosition = res.currency_position || 'left';

                                const summary = {
                                    pending: 0,
                                    paid: 0,
                                    total: 0
                                };

                                // Store purchase data for expandable rows
                                if (!window.purchaseDataMap) {
                                    window.purchaseDataMap = {};
                                }

                                res.data.forEach(o => {
                                    const amountValue = parseFloat(o.grand_total || 0) || 0;
                                    const remainingValue = parseFloat(o.remaining_amount || 0) || 0;
                                    const paidValue = Math.max(amountValue - remainingValue, 0);
                                    summary.total += amountValue;
                                    summary.pending += remainingValue;
                                    summary.paid += paidValue;
                                    const amount = parseFloat(o.grand_total || 0)
                                        .toFixed(2);
                                    const formattedAmount = currencyPosition ===
                                        'right' ?
                                        `${amount}${currencySymbol}` :
                                        `${currencySymbol}${amount}`;

                                    // Store purchase data for expandable row
                                    const purchaseData = {
                                        ...o,
                                        invoice_date: formatDate(o.invoice_date || o
                                            .date || o.created_at),
                                        displayAmount: formattedAmount,
                                        currencySymbol: currencySymbol,
                                        currencyPosition: currencyPosition
                                    };
                                    window.purchaseDataMap[o.id] = purchaseData;

                                    rows.push([
                                        `<div class="mobile-invoice-cell" data-purchase-id="${o.id}">
                                            <span class="mobile-invoice-vendor">${o.vendor_name || ''}</span>
                                            <a href="/purchase-view/${o.id}" style="text-decoration: none;">${o.invoice_number || ''}</a>
                                        </div>`,
                                        `<button class="mobile-toggle-btn-table" onclick="togglePurchaseRowDetails('${o.id}')" data-purchase-id="${o.id}">
                                            <span class="toggle-icon">+</span>
                                        </button>`,
                                        formatDate(o.invoice_date || o.date || o
                                            .created_at),
                                        `<span style="text-transform:capitalize;">${o.vendor_name || ''}</span>`,
                                        formattedAmount,
                                        badge((o.purchase_status || o.status ||
                                                ''), 'bg-lightgreen',
                                            'bg-lightyellow'),
                                        badge(o.payment_status, 'bg-lightgreen',
                                            'bg-lightred'),
                                        actionLinks(o)
                                        
                                    ]);
                                });

                                const $tbl = $('#order-table');

                                function updateTotal(dt) {
                                    setTimeout(() => calculatePurchaseFilteredTotal(), 100);
                                }

                                if ($.fn.DataTable.isDataTable($tbl)) {
                                    const dt = $tbl.DataTable();
                                    dt.clear().rows.add(rows).draw();
                                    dt.off('draw').on('draw', () => {
                                        updateTotal(dt);
                                        if (window.addPurchaseExpandableRows) {
                                            window.addPurchaseExpandableRows(dt);
                                        }
                                    });
                                    updateTotal(dt);
                                    setTimeout(() => {
                                        if (window.addPurchaseExpandableRows) {
                                            window.addPurchaseExpandableRows(dt);
                                        }
                                    }, 100);
                                } else {
                                    const dt = $tbl.DataTable({
                                        data: rows,
                                        responsive: true,
                                        autoWidth: false,
                                        pageLength: 10,
                                        ordering: true,
                                        searching: true,
                                        dom: 'lrtip',
                                        language: {
                                            searchPlaceholder: "Search purchases...",
                                            search: ""
                                        }
                                    });
                                    dt.on('draw', () => {
                                        updateTotal(dt);
                                        if (window.addPurchaseExpandableRows) {
                                            window.addPurchaseExpandableRows(dt);
                                        }
                                    });
                                    updateTotal(dt);
                                    setTimeout(() => {
                                        if (window.addPurchaseExpandableRows) {
                                            window.addPurchaseExpandableRows(dt);
                                        }
                                    }, 100);
                                }

                                updatePurchaseSummaryCards(summary, currencySymbol);

                                // Update global purchaseTable reference
                                purchaseTable = $tbl.DataTable();

                                // Populate customer dropdown after first draw
                                purchaseTable.one('draw', function() {
                                    let customers = new Set();
                                    purchaseTable.column(3, {
                                        search: 'applied'
                                    }).data().each(function(d) {
                                        let name = $('<div>').html(d).text()
                                            .trim();
                                        customers.add(name);
                                    });

                                    let $filter = $("#filter-vendor");
                                    $filter.empty().append(
                                        '<option value="">-- Select Vendor --</option>'
                                    );
                                    customers.forEach(function(name) {
                                        $filter.append('<option value="' +
                                            name + '">' + name + '</option>'
                                        );
                                    });
                                });

                                // Scroll sync
                                const topScroll = document.querySelector('.table-scroll-top');
                                const tableResponsive = document.querySelector(
                                    '.table-responsive');
                                const table = document.getElementById('order-table');
                                topScroll.querySelector('div').style.width = table.scrollWidth +
                                    'px';

                                topScroll.onscroll = () => {
                                    tableResponsive.scrollLeft = topScroll.scrollLeft;
                                };
                                tableResponsive.onscroll = () => {
                                    topScroll.scrollLeft = tableResponsive.scrollLeft;
                                };
                            }
                        },
                        error: xhr => console.error('purchase_list error:', xhr.responseText)
                    });
                }

                // Init
                loadPurchases();

                $('#filter-date').on('dp.change', loadPurchases);
                $('.btn-filters').on('click', e => {
                    e.preventDefault();
                    loadPurchases();
                });
            });


            // Handle delete action
            $(document).on("click", ".delete-order", function() {
                let orderId = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff9f43", // Orange confirm button
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/api/purchase_delete",
                            type: "POST",
                            data: {
                                id: orderId,
                                _token: "{{ csrf_token() }}"
                            },
                            headers: {
                                "Authorization": "Bearer " + authToken,
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: "Deleted!",
                                        text: response.message,
                                        icon: "success",
                                        confirmButtonColor: "#ff9f43"
                                    }).then(() => {
                                        location
                                            .reload(); // âœ… Reload page after user clicks OK
                                    });
                                } else {
                                    // âŒ Show backend error if success = false
                                    Swal.fire({
                                        title: "Error!",
                                        text: response.message,
                                        icon: "error",
                                        confirmButtonColor: "#ff9f43"
                                    });
                                }
                            },
                            error: function(xhr) {
                                // Try to show backend message from response JSON
                                let errorMessage = "Something went wrong. Try again.";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    title: "Error!",
                                    text: errorMessage,
                                    icon: "error",
                                    confirmButtonColor: "#ff9f43"
                                });

                                console.error(xhr.responseText); // For debugging
                            }
                        });
                    }
                });
            });

            // Global history modal open
            $(document).on('click', '.open-history', function() {
                const jobCardId = $(this).data('id');
                $('#globalPaymentHistoryList').html('<li class="list-group-item">Loading...</li>');
                $.ajax({
                    url: '/api/purchase/payment-history/' + jobCardId,
                    method: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        const history = response.data || [];
                        if (history.length === 0) {
                            $('#globalPaymentHistoryList').html(
                                '<li class="list-group-item">No payment history found.</li>'
                            );
                        } else {
                            const items = history.map(p => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Payment Date: ${(p.payment_date || p.created_at)}</span>
                                    <span><strong>${parseFloat(p.payment_amount || 0).toFixed(2)}</strong> via ${p.payment_method || ''}</span>
                                </li>
                            `).join('');
                            $('#globalPaymentHistoryList').html(items);
                        }
                        const modal = new bootstrap.Modal(document.getElementById(
                            'paymentHistoryModal'));
                        modal.show();
                    },
                    error: function() {
                        $('#globalPaymentHistoryList').html(
                            '<li class="list-group-item text-danger">Failed to load payment history.</li>'
                        );
                        const modal = new bootstrap.Modal(document.getElementById(
                            'paymentHistoryModal'));
                        modal.show();
                    }
                });
            });

            // Function to force CSS media query recalculation
            function forceCSSRecalculation() {
                // Create a temporary element to force reflow
                const temp = document.createElement('div');
                temp.style.width = '1px';
                temp.style.height = '1px';
                temp.style.position = 'absolute';
                temp.style.visibility = 'hidden';
                document.body.appendChild(temp);
                void temp.offsetWidth;
                void temp.offsetHeight;
                document.body.removeChild(temp);

                // Force reflow on viewport
                void window.innerWidth;
                void window.innerHeight;

                // Force reflow on document
                void document.documentElement.offsetWidth;
                void document.documentElement.offsetHeight;
            }

            // Resize handler for responsive behavior
            let resizeTimer;
            let lastWidth = $(window).width();

            function handlePurchaseResize() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();

                    // Always process resize to ensure CSS updates
                    lastWidth = currentWidth;

                    // Force CSS media query recalculation first
                    forceCSSRecalculation();

                    // Force CSS recalculation by triggering multiple reflows
                    const table = document.getElementById('order-table');
                    const tableResponsive = document.querySelector('.table-responsive');
                    const tableTop = document.querySelector('.table-top');
                    const cardBody = document.querySelector('.card-body');
                    const card = document.querySelector('.card');

                    // Method 1: Force reflow on all key elements
                    [table, tableResponsive, tableTop, cardBody, card].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            // Force style recalculation
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    // Method 2: Force CSS media query recalculation
                    if (document.body) {
                        const originalDisplay = document.body.style.display;
                        document.body.style.display = 'none';
                        void document.body.offsetHeight;
                        document.body.style.display = originalDisplay;
                    }

                    // Method 3: Force table and DataTables recalculation
                    if (purchaseTable && table) {
                        // Remove all existing expandable rows first
                        $('tr.order-details-row').remove();

                        // Force DataTables to recalculate
                        try {
                            // Multiple adjustments to ensure columns update
                            purchaseTable.columns.adjust();
                            void table.offsetHeight;

                            purchaseTable.draw(false);
                            void table.offsetHeight;

                            // Additional adjustment after draw
                            setTimeout(function() {
                                purchaseTable.columns.adjust();
                                purchaseTable.draw(false);

                                // Force one more reflow
                                void table.offsetHeight;

                                // Re-add expandable rows if needed
                                setTimeout(function() {
                                    if (window.addPurchaseExpandableRows) {
                                        window.addPurchaseExpandableRows(purchaseTable);
                                    }
                                    calculatePurchaseFilteredTotal();

                                    // Final CSS recalculation
                                    forceCSSRecalculation();
                                    void table.offsetHeight;
                                }, 100);
                            }, 100);
                        } catch (e) {
                            console.error('DataTables adjustment error:', e);
                            // Fallback: just redraw
                            purchaseTable.draw(false);
                            setTimeout(function() {
                                if (window.addPurchaseExpandableRows) {
                                    window.addPurchaseExpandableRows(purchaseTable);
                                }
                                calculatePurchaseFilteredTotal();
                                forceCSSRecalculation();
                            }, 150);
                        }
                    } else {
                        // Even if no table, force CSS recalculation
                        forceCSSRecalculation();
                    }
                }, 50);
            }

            // Window resize handler with throttling - multiple listeners for reliability
            $(window).off('resize.purchase').on('resize.purchase', handlePurchaseResize);

            // Also add native window resize listener (remove old one if exists, then add new)
            if (window.purchaseResizeHandler) {
                window.removeEventListener('resize', window.purchaseResizeHandler);
            }
            window.purchaseResizeHandler = handlePurchaseResize;
            window.addEventListener('resize', window.purchaseResizeHandler, {
                passive: true
            });

            // Orientation change handler
            $(window).off('orientationchange.purchase').on('orientationchange.purchase', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handlePurchaseResize();
                }, 300);
            });

            // Also add native orientation change listener
            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handlePurchaseResize();
                }, 500);
            });

            // MatchMedia listeners for all breakpoint changes
            const queries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            queries.forEach(function(query) {
                // Modern browsers
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handlePurchaseResize, 100);
                    });
                }
                // Legacy browsers
                else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handlePurchaseResize, 100);
                    });
                }
            });

            // Initial width set
            lastWidth = $(window).width();

            // Call on initial load to ensure correct state
            $(window).on('load', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handlePurchaseResize();
                }, 500);
            });

            // Also call after a short delay to ensure DOM is ready
            setTimeout(function() {
                if (purchaseTable) {
                    handlePurchaseResize();
                }
            }, 1000);

            // Make handler globally accessible for debugging
            window.handlePurchaseResize = handlePurchaseResize;

        });
        const topScroll = document.querySelector('.table-scroll-top');
        const tableResponsive = document.querySelector('.table-responsive');
        const table = document.getElementById('order-table');

        // Set top scrollbar inner div width to match table scroll width
        function updateTopScrollbarWidth() {
            const topInnerDiv = topScroll.querySelector('div');
            topInnerDiv.style.width = table.scrollWidth + 'px';
        }

        // Update on load and window resize
        window.addEventListener('load', updateTopScrollbarWidth);
        window.addEventListener('resize', updateTopScrollbarWidth);

        // Sync scrolling
        topScroll.addEventListener('scroll', function() {
            tableResponsive.scrollLeft = topScroll.scrollLeft;
        });
        tableResponsive.addEventListener('scroll', function() {
            topScroll.scrollLeft = tableResponsive.scrollLeft;
        });
        $('#exportAllChallan').click(function() {
            let selectedYear = $('#filter-year').val() || '';
            let selectedMonth = $('#filter-month').val() || '';
            let selectedDate = $('#filter-date').val() || '';
            let selectedVendorId = $('#filter-vendor').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url =
                `/api/export-purchase?year=${selectedYear}&month=${selectedMonth}&date=${selectedDate}&customer_id=${selectedVendorId}&selectedSubAdminId=${selectedSubAdminId}`;

            $.ajax({
                url: url,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    if (response.status && response.file_url) {
                        // Trigger download
                        const link = document.createElement('a');
                        link.href = response.file_url;
                        link.download = response.file_name; // optional
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert("Export failed: " + (response.message || "Unknown error"));
                    }
                },
                error: function(xhr) {
                    console.error("Export failed:", xhr.responseText);
                    alert("Export failed. Please try again.");
                }
            });
        });

        $('#exportPdf').click(function() {
            let selectedYear = $('#filter-year').val() || '';
            let selectedMonth = $('#filter-month').val() || '';
            let selectedDate = $('#filter-date').val() || '';
            let selectedVendorId = $('#filter-vendor').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            $.ajax({
                url: `/api/export-purchase-pdf`,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                data: {
                    year: selectedYear,
                    month: selectedMonth,
                    date: selectedDate,
                    customer_id: selectedVendorId,
                    selectedSubAdminId: selectedSubAdminId
                },
                success: function(response) {
                    if (response.status && response.file_url) {
                        // Download the PDF
                        let link = document.createElement('a');
                        link.href = response.file_url;
                        link.download = response.file_name || 'Purchases.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert("PDF generation failed.");
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert("Failed to generate PDF. Please try again.");
                }
            });
        });

        function positionPurchaseActionMenu(dropdown) {
            const $dropdown = $(dropdown);
            const $menu = $dropdown.data('floating-menu') || $dropdown.find('.purchase-action-menu');
            const button = $dropdown.find('.action-menu-toggle')[0];
            if (!$menu.length || !button) return;

            const rect = button.getBoundingClientRect();
            const menuEl = $menu[0];
            const menuWidth = 168;
            const menuHeight = menuEl.offsetHeight || 220;
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            const gap = 4;

            let left = rect.right - menuWidth;
            left = Math.max(8, Math.min(left, viewportWidth - menuWidth - 8));

            let top = rect.bottom + gap;
            if (top + menuHeight > viewportHeight - 8) {
                top = Math.max(8, rect.top - menuHeight - gap);
            }

            menuEl.style.setProperty('left', `${left}px`, 'important');
            menuEl.style.setProperty('position', 'fixed', 'important');
            menuEl.style.setProperty('top', `${top}px`, 'important');
            menuEl.style.setProperty('transform', 'none', 'important');
            menuEl.style.setProperty('width', `${menuWidth}px`, 'important');
            menuEl.style.setProperty('min-width', `${menuWidth}px`, 'important');
            menuEl.style.setProperty('max-width', `${menuWidth}px`, 'important');
        }

        $(document).on('show.bs.dropdown', '.purchase-action-dropdown', function() {
            const $dropdown = $(this);
            const $menu = $dropdown.find('.purchase-action-menu');
            if (!$menu.length) return;

            $dropdown.data('floating-menu', $menu);
            $dropdown.data('menu-parent', $menu.parent());
            $dropdown.data('menu-next', $menu.next());
            $menu.addClass('purchase-action-menu-floating').appendTo('body');
        });

        $(document).on('shown.bs.dropdown', '.purchase-action-dropdown', function() {
            const dropdown = this;
            positionPurchaseActionMenu(dropdown);
            requestAnimationFrame(function() { positionPurchaseActionMenu(dropdown); });
            setTimeout(function() { positionPurchaseActionMenu(dropdown); }, 50);
        });

        $(document).on('hidden.bs.dropdown', '.purchase-action-dropdown', function() {
            const $dropdown = $(this);
            const $menu = $dropdown.data('floating-menu');
            const $parent = $dropdown.data('menu-parent');
            const $next = $dropdown.data('menu-next');

            if ($menu && $menu.length && $parent && $parent.length) {
                $menu.removeClass('purchase-action-menu-floating').removeAttr('style');
                if ($next && $next.length && $.contains($parent[0], $next[0])) {
                    $menu.insertBefore($next);
                } else {
                    $parent.append($menu);
                }
            }
            $dropdown.removeData('floating-menu menu-parent menu-next');
        });

        $(window).on('scroll resize', function() {
            $('.purchase-action-dropdown.show').each(function() {
                positionPurchaseActionMenu(this);
            });
        });
    </script>
@endpush



