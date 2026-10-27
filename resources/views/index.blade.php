@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $role = auth()->user()->role ?? '';
    @endphp
    {{-- <style>
        .color_box {
            display: block;
            width: 100%;
            text-decoration: none;
            color: inherit;
        }

        /* Dashboard widget styles remain the same */
        @media screen and (min-width: 768px) and (max-width: 1400px) {
            .dash-count .dash-imgs svg {
                width: 40px !important;
                height: 36px !important;
            }

            .dash-widgetcontent h6 {
                font-size: 10px !important;
            }

            .dash-counts h5 {
                font-size: 10.5px !important;
            }

            .dash-count {
                min-height: 90px !important;
                margin: 0 0 15px !important;
                padding: 17px;
            }
        }

        @media screen and (max-width: 767px) {
            .dash-widget {
                display: flex;
                flex-direction: column;
                align-items: center;
                margin-bottom: 15px !important;
                text-align: center;
                padding: 12px 20px !important;
            }

            .col-4 {
                display: flex;
                justify-content: center;
            }

            .dash-widgetcontent {
                margin-left: 0 !important;
                width: 68px !important;
            }

            .dash-widgetcontent h5 {
                margin-top: .5rem !important;
                font-size: 13px !important;
            }

            .dash-widgetcontent h6 {
                font-size: 10px !important;
            }

            .dash-count {
                min-height: 90px !important;
                margin: 0 0 15px !important;
                padding: 15px;
            }

            .dash-counts h5 {
                font-size: 10.5px !important;
            }

            .dash-count .dash-imgs svg {
                width: 37px !important;
                height: 37px !important;
            }

            .dash-count h4 {
                font-size: 19px !important;
            }

            .table-responsive {
                font-size: 12px !important;
            }

            /* NEW: Mobile table styles for sales/purchases */
            .dataview table thead th:nth-child(n+3),
            .dataview table tbody td:nth-child(n+3) {
                display: none !important;
            }

            .dataview table thead th.details-column,
            .dataview table tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 50px;
            }

            .dataview .toggle-details i {
                font-size: 18px;
                transition: transform 0.3s ease;
            }

            /* Order ID column styling */
            .dataview table tbody td:first-child {
                display: flex !important;
                align-items: center !important;
                max-width: calc(100vw - 100px) !important;
            }

            .dataview .order-id {
                display: inline-block !important;
                max-width: calc(100% - 50px) !important;
                margin-left: 8px !important;
                font-size: 14px !important;
                word-break: break-word !important;
            }
        }

        /* Desktop: hide details toggle column */
        @media (min-width: 769px) {

            .dataview table thead th.details-column,
            .dataview table tbody td:nth-child(2) {
                display: none !important;
            }
        }

        /* Mobile collapse styles */
        .mobile-details-collapse {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-top: 10px;
            padding: 12px;
            background-color: #f8f9fa;
        }

        .mobile-details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #dee2e6;
        }

        .mobile-details-label {
            font-weight: 600;
            color: #495057;
        }

        .mobile-details-value {
            color: #212529;
            text-align: right;
        }
    </style> --}}
    <style>
    /* Fix for profile image alignment in header */
    .header .user-img {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .header .user-img img {
        width: 35px !important;
        height: 35px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        margin-top: 0px !important;
    }
    .header .user-img .status {
    border: 2px solid #fff;
    height: 10px;
    width: 10px;
    margin: 0;
    position: absolute;
    right: 4px;
    bottom: 0px !important;
    border-radius: 50%;
    display: inline-block;
    background: #28c76f;
    }

    .color_box {
        display: block;
        width: 100%;
        text-decoration: none;
        color: inherit;
    }

    /* Dashboard widget styles */
    .dash-widget {
        transition: all 0.3s ease;
        border-radius: 8px !important;
    }

    .dash-widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Tablet view (768px - 1400px) */
    @media screen and (min-width: 768px) and (max-width: 1400px) {
        .dash-widget {
            padding: 15px !important;
        }
        
        .dash-widgetimg span img {
            width: 40px !important;
            height: 36px !important;
        }

        .dash-widgetcontent h6 {
            font-size: 10px !important;
            line-height: 1.2 !important;
        }

        .dash-widgetcontent h5 {
            font-size: 10.5px !important;
            margin-bottom: 5px !important;
        }

        .dash-widget {
            min-height: 90px !important;
            margin: 0 0 15px !important;
        }
        
        /* Ensure proper alignment in tablet */
        .col-lg-4.col-sm-6.col-4,
        .col-lg-4.col-md-4.col-sm-4.col-4 {
            padding-left: 7.5px !important;
            padding-right: 7.5px !important;
        }
    }

    /* Mobile view (max-width: 767px) */
    @media screen and (max-width: 767px) {

         .dash-count {
                min-height: 90px !important;
                margin: 0 0 15px !important;
                padding: 15px;
            }

            .dash-counts h5 {
                font-size: 10.5px !important;
            }

            .dash-count .dash-imgs svg {
                width: 37px !important;
                height: 37px !important;
            }

            .dash-count h4 {
                font-size: 19px !important;
            }

        /* Dashboard widgets container */
        .row {
            margin-left: -5px !important;
            margin-right: -5px !important;
            display: flex !important;
            flex-wrap: wrap !important;
        }
        
        /* First row (Purchase, Expense, Sales) - 3 in one row */
        .col-lg-4.col-sm-6.col-4:first-child,
        .col-lg-4.col-sm-6.col-4:nth-child(2),
        .col-lg-4.col-sm-6.col-4:nth-child(3) {
            width: 33.333% !important; /* Equal width for 3 widgets */
            padding-left: 5px !important;
            padding-right: 5px !important;
            margin-bottom: 10px !important;
            float: left !important;
        }
        
        /* Remaining widgets (Invoice counts, Vendors, Customers) - 2 per row */
        .col-lg-4.col-sm-6.col-4:nth-child(4),
        .col-lg-4.col-sm-6.col-4:nth-child(5),
        .col-lg-4.col-sm-6.col-4:nth-child(6),
        .col-lg-4.col-sm-6.col-4:nth-child(7) {
            width: 50% !important; 
            padding-left: 5px !important;
            padding-right: 5px !important;
            margin-bottom: 10px !important;
            float: left !important;
        }
        
        /* Clear floats */
        .row::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Dashboard widget styling */
        .dash-widget {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            padding: 12px 5px !important;
            margin-bottom: 0 !important;
            height: 110px !important; /* Slightly shorter for 3 in a row */
            justify-content: center !important;
        }

        .dash-widgetimg {
            margin-bottom: 6px !important;
        }

        .dash-widgetimg span img {
            width: 30px !important;
            height: 30px !important;
        }

        .dash-widgetcontent {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 3px !important;
        }

        .dash-widgetcontent h5 {
            margin-top: 0.2rem !important;
            margin-bottom: 0.2rem !important;
            font-size: 12px !important;
            line-height: 1.2 !important;
            word-break: break-word !important;
        }

        .dash-widgetcontent h6 {
            font-size: 10px !important;
            line-height: 1.2 !important;
            margin-bottom: 0 !important;
            color: #6c757d !important;
        }
        
        /* Currency symbol and amount styling */
        .dash-widgetcontent h5 .counters {
            font-size: 12px !important;
            font-weight: 600 !important;
        }
        
        /* Adjust for first row (financial amounts) */
        .col-lg-4.col-sm-6.col-4:first-child .dash-widgetcontent h5,
        .col-lg-4.col-sm-6.col-4:nth-child(2) .dash-widgetcontent h5,
        .col-lg-4.col-sm-6.col-4:nth-child(3) .dash-widgetcontent h5 {
            font-size: 11px !important; /* Slightly smaller for amounts */
        }
        
        .col-lg-4.col-sm-6.col-4:first-child .dash-widgetcontent h6,
        .col-lg-4.col-sm-6.col-4:nth-child(2) .dash-widgetcontent h6,
        .col-lg-4.col-sm-6.col-4:nth-child(3) .dash-widgetcontent h6 {
            font-size: 9px !important; /* Slightly smaller labels */
        }
        
        /* Adjust for count widgets (4th onwards) */
        .col-lg-4.col-sm-6.col-4:nth-child(n+4) .dash-widgetcontent h5 {
            font-size: 14px !important; /* Larger for counts */
        }
        
        .col-lg-4.col-sm-6.col-4:nth-child(n+4) .dash-widgetcontent h6 {
            font-size: 11px !important;
        }
        
        .col-lg-4.col-sm-6.col-4:nth-child(n+4) .dash-widget {
            height: 100px !important; /* Slightly shorter for count widgets */
        }

        /* Table responsive styles */
        .table-responsive {
            font-size: 12px !important;
        }

    }

    /* Dashboard data tables: compact row cards on mobile and tablets (matching Dolphin ERP design) */
    @media (max-width: 1199.98px) {
        .recent-erp-card {
            border: 0 !important;
            border-radius: 14px !important;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .08) !important;
            margin-bottom: 16px !important;
        }

        .recent-erp-card > .card-header {
            padding: 12px 14px !important;
            background: #fff !important;
            border-bottom: 1px solid #eef0f4 !important;
        }

        .recent-erp-card > .card-header .card-title {
            position: relative !important;
            padding-left: 10px !important;
            color: #1b2850 !important;
            font-size: 15px !important;
            font-weight: 700 !important;
        }

        .recent-erp-card > .card-header .card-title::before {
            content: "";
            position: absolute;
            left: 0;
            top: 2px;
            bottom: 2px;
            width: 3px;
            border-radius: 3px;
            background: #ff9f43;
        }

        .recent-erp-card > .card-header .view-all-btn {
            padding: 4px 12px !important;
            border-radius: 999px !important;
            background: #fff3e6 !important;
            color: #e07b14 !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            border: none !important;
        }

        .recent-erp-card > .card-body {
            border: 0 !important;
            padding: 10px 12px !important;
        }

        /* Card Scroll Container on Mobile & Tablets: vertical custom scrollbar */
        .db-recent-card-scroll {
            max-height: 380px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding-right: 4px !important;
            scrollbar-width: thin;
            scrollbar-color: #ff9f43 #f1f5f9;
        }

        .db-recent-card-scroll > .table {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            margin: -8px 0 0 !important;
            width: 100% !important;
        }

        .db-recent-card-scroll > .table thead {
            display: none !important;
        }

        .db-recent-card-scroll > .table tbody tr {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto !important;
            grid-template-rows: auto auto !important;
            align-items: center;
            margin: 0 0 8px !important;
            overflow: hidden;
            border: 1px solid #eef0f4 !important;
            border-radius: 10px;
            background: #fff !important;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .06);
            padding: 0 !important;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .db-recent-card-scroll > .table tbody tr:active {
            transform: scale(0.99);
        }

        .db-recent-card-scroll > .table tbody td {
            display: block !important;
            width: auto !important;
            max-width: none !important;
            padding: 10px 12px !important;
            border: 0 !important;
            background: transparent !important;
            text-align: left !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        /* Order ID / Bill No Column: Row 1, Column 1 */
        .db-recent-card-scroll > .table tbody tr > td:first-child {
            grid-column: 1 !important;
            grid-row: 1 !important;
            min-width: 0 !important;
            padding: 10px 10px 2px 12px !important;
            font-size: 13.5px !important;
            font-weight: 700 !important;
            color: #1b2850 !important;
        }

        /* Grand Total Column: Row 1, Column 2 */
        .db-recent-card-scroll > .table tbody tr > td:nth-child(3) {
            grid-column: 2 !important;
            grid-row: 1 !important;
            padding: 10px 12px 2px 0 !important;
            text-align: right !important;
            white-space: nowrap !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #111827 !important;
        }

        /* Product Image + Name Column: Row 2, Column 1 */
        .db-recent-card-scroll > .table tbody tr > td:nth-child(2) {
            grid-column: 1 !important;
            grid-row: 2 !important;
            padding: 2px 10px 10px 12px !important;
            min-width: 0 !important;
        }

        /* Sale Date / Purchase Date Column: Row 2, Column 2 */
        .db-recent-card-scroll > .table tbody tr > td:nth-child(4) {
            grid-column: 2 !important;
            grid-row: 2 !important;
            padding: 2px 12px 10px 0 !important;
            text-align: right !important;
            white-space: nowrap !important;
            font-size: 11.5px !important;
            color: #64748b !important;
        }

        .db-recent-card-scroll .dashboard-product-name {
            font-size: 12px !important;
            color: #374151 !important;
            font-weight: 500 !important;
            line-height: 1.35 !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
        }

        .db-recent-card-scroll > .table tbody td[colspan] {
            grid-column: 1 / -1 !important;
            padding: 14px 12px !important;
            text-align: center !important;
        }
    }

    /* Custom scrollbar for recent cards container */
    .db-recent-card-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .db-recent-card-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .db-recent-card-scroll::-webkit-scrollbar-thumb {
        background: #ff9f43;
        border-radius: 10px;
    }
    .db-recent-card-scroll::-webkit-scrollbar-thumb:hover {
        background: #e68a35;
    }

    /* Desktop View hover and scroll styling */
    @media (min-width: 1200px) {
        .db-recent-card-scroll {
            max-height: 400px;
            overflow-y: auto;
        }
        .db-recent-row {
            transition: background-color 0.15s ease;
        }
    }

    /* Tablet view: ensure HR snapshot, attendance watch and payroll status fit in a single row */
    @media (min-width: 768px) and (max-width: 1199.98px) {
        .hr-section .card-header {
            padding: 10px 10px !important;
        }
        .hr-section .card-header h4 {
            font-size: 13.5px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .hr-section .card-header .btn {
            padding: 2px 7px !important;
            font-size: 11px !important;
            white-space: nowrap !important;
        }
        .hr-section .card-body {
            padding: 10px 6px !important;
        }
        .hr-section .card-body .table th,
        .hr-section .card-body .table td {
            padding: 6px 4px !important;
            font-size: 11.5px !important;
        }
    }
    
        /* Clearfix for mobile grid */
        @media screen and (max-width: 767px) {
            .dashboard-widgets-row::after {
                content: "";
                display: table;
                clear: both;
            }
        }

        /* Latest Summary Cards Layout */
        .latest-summary-row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -10px;
            margin-right: -10px;
        }

        .latest-summary-col {
            width: 100%;
            padding-left: 10px;
            padding-right: 10px;
            display: flex;
            margin-bottom: 20px;
        }

        @media screen and (min-width: 768px) {
            .latest-summary-col {
                width: 50%;
            }
        }

        .hr-section .card {
            border: 1px solid #d9f0df;
            box-shadow: none;
        }

        .hr-metric {
            min-height: 118px;
            height: 80%;
        }

        .hr-metric-label {
            color: #667085;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .hr-metric-value {
            color: #1f2937;
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }

        .hr-metric-note {
            color: #667085;
            font-size: 12px;
            line-height: 1.35;
            margin: 0;
        }

        .hr-mini-badge {
            border-radius: 4px;
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 7px;
        }

        .hr-chart {
            min-height: 260px;
            width: 100%;
        }
        .dash-card-header-orange {
    background-color: #ff9f43;
    color: white;
    padding: 15px;
    border-bottom: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dash-card-header-orange h4 {
    color: white;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.dash-card-header-orange .btn {
    background-color: white;
    color: #ff9f43;
    border-radius: 4px;
    font-weight: 600;
    padding: 4px 10px;
}

@media (max-width: 767px) {
    .top-stat-card {
        flex-direction: column !important;
        justify-content: center !important;
        text-align: center;
        padding: 10px 5px !important;
        height: auto !important;
        min-height: 110px;
    }
    .top-stat-icon {
        margin-right: 0 !important;
        margin-bottom: 8px;
        width: 35px !important;
        height: 35px !important;
    }
    .top-stat-icon svg {
        width: 18px !important;
        height: 18px !important;
    }
    .top-stat-amount {
        font-size: 14px !important;
        margin-bottom: 2px !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
    }
    .top-stat-label {
        font-size: 11px !important;
        line-height: 1.2 !important;
    }
}

@media (min-width: 768px) and (max-width: 991.98px) {
    .top-stat-card {
        padding: 15px 10px !important;
    }
    .top-stat-icon {
        width: 42px !important;
        height: 42px !important;
        margin-right: 10px !important;
    }
    .top-stat-icon svg {
        width: 20px !important;
        height: 20px !important;
    }
    .top-stat-amount {
        font-size: 18px !important;
        margin-bottom: 2px !important;
    }
    .top-stat-label {
        font-size: 11.5px !important;
    }
}
    </style>
    <div class="content db-page">
        <div class="row dashboard-widgets-row">
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin', 'sales-manager']))
                <div class="col-lg-4 col-md-4 col-sm-4 col-4">
                    <a href="{{ route('sales.list') }}" style="text-decoration: none;">
                        <div class="top-stat-card" style="background-color: white; border-radius: 8px; padding: 20px; display: flex; align-items: center; border: 1px solid #f0f0f0; margin-bottom: 20px; height: 100px;">
                            <div class="top-stat-icon" style="background-color: rgba(255, 159, 67, 0.1); width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-right: 15px; flex-shrink: 0;">
                                <i data-feather="camera" style="color: #ff9f43; width: 24px; height: 24px;"></i>
                            </div>
                            <div style="width: 100%; overflow: hidden;">
                                <h4 class="top-stat-amount" style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px;">
                                    @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($totalSalesAmount, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                </h4>
                                <h6 class="top-stat-label" style="color: #667085; font-size: 13px; font-weight: 600; margin: 0;">Total Sales Amount</h6>
                            </div>
                            
                            <!-- Platform Orders Count -->
                            <div class="d-none d-md-flex align-items-center gap-3 ms-2 ms-md-3">
                                <div id="dashZomatoWidget" class="text-center" style="cursor: pointer; padding: 5px; border-radius: 6px; transition: 0.2s; display: none !important;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'" onclick="event.preventDefault(); openPlatformModal('Zomato')">
                                    <img src="{{ env('ImagePath').'/admin/assets/img/product/zomato.png' }}" alt="Zomato" style="width: 28px; height: 28px; border-radius: 6px; border: 1px solid #eee; margin-bottom: 3px;">
                                    <div style="font-size: 11px; font-weight: 700; color: #E23744;"><span id="dashZomatoCount">0</span> Orders</div>
                                </div>
                                <div id="dashSwiggyWidget" class="text-center" style="cursor: pointer; padding: 5px; border-radius: 6px; transition: 0.2s; display: none !important;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'" onclick="event.preventDefault(); openPlatformModal('Swiggy')">
                                    <img src="{{ env('ImagePath').'/admin/assets/img/product/swiggy.jpg' }}" alt="Swiggy" style="width: 28px; height: 28px; border-radius: 6px; border: 1px solid #eee; margin-bottom: 3px;">
                                    <div style="font-size: 11px; font-weight: 700; color: #FC8019;"><span id="dashSwiggyCount">0</span> Orders</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin', 'purchase-manager']))
                <div class="col-lg-4 col-md-4 col-sm-4 col-4">
                    <a href="{{ route('purchase.lists') }}" style="text-decoration: none;">
                        <div class="top-stat-card" style="background-color: white; border-radius: 8px; padding: 20px; display: flex; align-items: center; border: 1px solid #f0f0f0; margin-bottom: 20px; height: 100px;">
                            <div class="top-stat-icon" style="background-color: rgba(255, 159, 67, 0.1); width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-right: 15px; flex-shrink: 0;">
                                <i data-feather="shopping-bag" style="color: #ff9f43; width: 24px; height: 24px;"></i>
                            </div>
                            <div style="width: 100%; overflow: hidden;">
                                <h4 class="top-stat-amount" style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px;">
                                    @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($totalPurchaseAmount, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                </h4>
                                <h6 class="top-stat-label" style="color: #667085; font-size: 13px; font-weight: 600; margin: 0;">Total Purchase Amount</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin']))
                <div class="col-lg-4 col-md-4 col-sm-4 col-4">
                    <a href="{{ route('expense.list') }}" style="text-decoration: none;">
                        <div class="top-stat-card" style="background-color: white; border-radius: 8px; padding: 20px; display: flex; align-items: center; border: 1px solid #f0f0f0; margin-bottom: 20px; height: 100px;">
                            <div class="top-stat-icon" style="background-color: rgba(0, 207, 232, 0.1); width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-right: 15px; flex-shrink: 0;">
                                <i data-feather="arrow-down-circle" style="color: #00cfe8; width: 24px; height: 24px;"></i>
                            </div>
                            <div style="width: 100%; overflow: hidden;">
                                <h4 class="top-stat-amount" style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px;">
                                    @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($totalExpenseAmount, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                </h4>
                                <h6 class="top-stat-label" style="color: #667085; font-size: 13px; font-weight: 600; margin: 0;">Total Expense Amount</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        <div class="row dashboard-widgets-row">
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin', 'sales-manager']))
                <div class="col-lg col-md-4 col-sm-6 col-6">
                    <a href="{{ route('tables.create') }}" style="text-decoration: none;">
                        <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; position: relative; margin-bottom: 20px; height: 110px;">
                            <div style="position: absolute; top: 15px; right: 15px;">
                                <i data-feather="grid" style="color: #28c76f; width: 20px; height: 20px;"></i>
                            </div>
                            <h4 style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px; margin-top: 10px;">{{ $runningTableCount ?? 0 }}</h4>
                            <h6 style="font-size: 13px; font-weight: 600; color: #667085; margin: 0;">Running Tables</h6>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin', 'sales-manager']))
                <div class="col-lg col-md-4 col-sm-6 col-6">
                    <a href="{{ route('sales.list') }}" style="text-decoration: none;">
                        <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; position: relative; margin-bottom: 20px; height: 110px;">
                            <div style="position: absolute; top: 15px; right: 15px;">
                                <i data-feather="file-text" style="color: #28c76f; width: 20px; height: 20px;"></i>
                            </div>
                            <h4 style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px; margin-top: 10px;">{{ $salesInvoiceCount ?? 0 }}</h4>
                            <h6 style="font-size: 13px; font-weight: 600; color: #667085; margin: 0;">Sales Invoice</h6>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin', 'purchase-manager']))
                <div class="col-lg col-md-4 col-sm-6 col-6">
                    <a href="{{ route('purchase.lists') }}" style="text-decoration: none;">
                        <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; position: relative; margin-bottom: 20px; height: 110px;">
                            <div style="position: absolute; top: 15px; right: 15px;">
                                <i data-feather="file" style="color: #1e1e2d; width: 20px; height: 20px;"></i>
                            </div>
                            <h4 style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px; margin-top: 10px;">{{ $purchaseInvoiceCount ?? 0 }}</h4>
                            <h6 style="font-size: 13px; font-weight: 600; color: #667085; margin: 0;">Purchase Invoice</h6>
                        </div>
                    </a>
                </div>
            @endif
           {{-- @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin']))
                <div class="col-lg col-md-4 col-sm-6 col-6">
                    <a href="#" style="text-decoration: none;">
                        <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; position: relative; margin-bottom: 20px; height: 110px;">
                            <div style="position: absolute; top: 15px; right: 15px;">
                                <i data-feather="clipboard" style="color: #ff9f43; width: 20px; height: 20px;"></i>
                            </div>
                            <h4 style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px; margin-top: 10px;">0</h4>
                            <h6 style="font-size: 13px; font-weight: 600; color: #667085; margin: 0;">Total Quotations</h6>
                        </div>
                    </a>
                </div>
            @endif --}}
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin']))
                <div class="col-lg col-md-4 col-sm-6 col-6">
                    <a href="{{ route('tables.create') }}" style="text-decoration: none;">
                        <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; position: relative; margin-bottom: 20px; height: 110px;">
                            <div style="position: absolute; top: 15px; right: 15px;">
                                <i data-feather="grid" style="color: #00cfe8; width: 20px; height: 20px;"></i>
                            </div>
                            <h4 style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px; margin-top: 10px;">{{ $tableCount ?? 0 }}</h4>
                            <h6 style="font-size: 13px; font-weight: 600; color: #667085; margin: 0;">Tables </h6>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin', 'purchase-manager']))
                <div class="col-lg col-md-4 col-sm-6 col-6">
                    <a href="{{ route('vendor.list') }}" style="text-decoration: none;">
                        <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; position: relative; margin-bottom: 20px; height: 110px;">
                            <div style="position: absolute; top: 15px; right: 15px;">
                                <i data-feather="user-check" style="color: #ff9f43; width: 20px; height: 20px;"></i>
                            </div>
                            <h4 style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 5px; margin-top: 10px;">{{ $vendorCount ?? 0 }}</h4>
                            <h6 style="font-size: 13px; font-weight: 600; color: #667085; margin: 0;">Vendors</h6>
                        </div>
                    </a>
                </div>
            @endif
        </div>
        <div class="latest-summary-row">
            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                <div class="latest-summary-col">
                    <div class="card flex-fill" style="border: 1px solid #f0f0f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                        <div class="card-header" style="background-color: #ff9f43; color: white; padding: 15px; border-bottom: none; display: flex; justify-content: space-between; align-items: center;">
                            <h5 class="mb-0" style="color: white; font-size: 16px; font-weight: 600;">Latest Orders</h5>
                            <a href="{{ route('sales.list') }}" class="btn btn-sm bg-white" style="color: #ff9f43; border-radius: 4px; font-weight: 600; padding: 4px 10px;">View All</a>
                        </div>
                        <div class="card-body" style="padding: 20px; background-color: #f8f9fa;">
                            <div style="background-color: #1e1e2d; border-radius: 10px; padding: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="color: #ff9f43; font-size: 12px; font-weight: 600; letter-spacing: 1px; margin-bottom: 5px;">TOTAL SALES VOLUME</div>
                                    <div style="color: white; font-size: 24px; font-weight: 700;">
                                        @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($totalSalesAmount, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                    </div>
                                </div>
                                <div style="background-color: rgba(255, 159, 67, 0.2); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i data-feather="trending-up" style="color: #ff9f43; width: 22px; height: 22px;"></i>
                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 20px;">
                                <div class="col-6" style="padding-right: 7.5px;">
                                    <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; height: 100%;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                            <div style="color: #667085; font-size: 11px; font-weight: 600;">TOTAL ORDERS</div>
                                            <i data-feather="file-text" style="color: #00cfe8; width: 16px; height: 16px;"></i>
                                        </div>
                                        <div style="font-size: 20px; font-weight: 700; color: #1f2937;">{{ $salesInvoiceCount ?? 0 }}</div>
                                    </div>
                                </div>
                                <div class="col-6" style="padding-left: 7.5px;">
                                    <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; height: 100%;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                            <div style="color: #667085; font-size: 11px; font-weight: 600;">THIS MONTH</div>
                                            <i data-feather="calendar" style="color: #ff9f43; width: 16px; height: 16px;"></i>
                                        </div>
                                        <div style="font-size: 20px; font-weight: 700; color: #1f2937;">{{ $salesInvoiceCount ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $completedSalesPercentage = $salesInvoiceCount > 0 ? round(($completedSalesCount / $salesInvoiceCount) * 100) : 0;
                                $partiallyPaidSalesPercentage = $salesInvoiceCount > 0 ? round(($partiallyPaidSalesCount / $salesInvoiceCount) * 100) : 0;
                                $pendingSalesPercentage = $salesInvoiceCount > 0 ? round(($pendingSalesCount / $salesInvoiceCount) * 100) : 0;
                            @endphp
                            <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0;">
                                <div style="margin-bottom: 12px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                        <span style="color: #667085;">Completed</span>
                                        <span style="color: #1f2937;">{{ $completedSalesPercentage }}% <span style="color: #999; margin-left: 5px;">({{ $completedSalesCount ?? 0 }})</span></span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                        <div class="progress-bar" style="width: {{ $completedSalesPercentage }}%; background-color: #28c76f; border-radius: 3px;"></div>
                                    </div>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                        <span style="color: #667085;">Partially Paid</span>
                                        <span style="color: #1f2937;">{{ $partiallyPaidSalesPercentage }}% <span style="color: #999; margin-left: 5px;">({{ $partiallyPaidSalesCount ?? 0 }})</span></span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                        <div class="progress-bar" style="width: {{ $partiallyPaidSalesPercentage }}%; background-color: #00cfe8; border-radius: 3px;"></div>
                                    </div>
                                </div>
                                <div style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                        <span style="color: #667085;">Pending</span>
                                        <span style="color: #1f2937;">{{ $pendingSalesPercentage }}% <span style="color: #999; margin-left: 5px;">({{ $pendingSalesCount ?? 0 }})</span></span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                        <div class="progress-bar" style="width: {{ $pendingSalesPercentage }}%; background-color: #ff9f43; border-radius: 3px;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Latest Sale Entry -->
                            @if($latestSales->isNotEmpty())
                                @php
                                    $item = $latestSales->first();
                                    $statusColor = match(strtolower($item->payment_status ?? '')) {
                                        'completed', 'paid' => '#10b981',
                                        'partially'         => '#3b82f6',
                                        default             => '#e87b1e',
                                    };
                                @endphp
                                <a href="{{ route('sales.details', $item->order_id ?? $item->id ?? 0) }}" style="text-decoration: none; display: block; margin-top: 15px;">
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background-color: #ffffff; cursor: pointer; transition: border-color 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.borderColor='#e87b1e';this.style.boxShadow='0 2px 8px rgba(232,123,30,0.15)'" onmouseout="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'">
                                    <div>
                                        <h5 style="color: #111827; font-size: 14px; font-weight: 700; margin-bottom: 5px;">{{ $item->customer_name ?? 'N/A' }}</h5>
                                        <p style="color: #6b7280; font-size: 11px; font-weight: 500; margin-bottom: 0;">
                                            <strong style="color: #1f2937;">Invoice: {{ $item->order_number ?? 'N/A' }}</strong> &bull; {{ \Carbon\Carbon::parse($item->order_date)->format('d M, Y') }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <h5 style="color: #111827; font-size: 14px; font-weight: 700; margin-bottom: 5px;">
                                            @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif
                                            {{ number_format($item->total_amount, 2) }}
                                            @if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                        </h5>
                                        <p style="color: {{ $statusColor }}; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-bottom: 0; letter-spacing: 0.5px;">
                                            {{ $item->payment_status ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                </a>
                            @endif

                            <!-- Footer Note -->
                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 12px 15px; display: flex; align-items: center; margin-top: 10px;">
                                <i data-feather="info" style="width: 14px; height: 14px; color: #6b7280; margin-right: 10px; stroke-width: 2.5;"></i>
                                <p style="color: #6b7280; font-size: 11px; font-weight: 500; margin-bottom: 0;">Overview is based on all visible sales and their statuses.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                <div class="latest-summary-col">
                    <div class="card flex-fill" style="border: 1px solid #f0f0f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                        <div class="card-header" style="background-color: #ff9f43; color: white; padding: 15px; border-bottom: none; display: flex; justify-content: space-between; align-items: center;">
                            <h5 class="mb-0" style="color: white; font-size: 16px; font-weight: 600;">Latest Purchases</h5>
                            <a href="{{ route('purchase.lists') }}" class="btn btn-sm bg-white" style="color: #ff9f43; border-radius: 4px; font-weight: 600; padding: 4px 10px;">View All</a>
                        </div>
                        <div class="card-body" style="padding: 20px; background-color: #f8f9fa;">
                            <div style="background-color: #1e1e2d; border-radius: 10px; padding: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="color: #ff9f43; font-size: 12px; font-weight: 600; letter-spacing: 1px; margin-bottom: 5px;">TOTAL PURCHASE VOLUME</div>
                                    <div style="color: white; font-size: 24px; font-weight: 700;">
                                        @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($totalPurchaseAmount, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                    </div>
                                </div>
                                <div style="background-color: rgba(255, 159, 67, 0.2); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i data-feather="shopping-cart" style="color: #ff9f43; width: 22px; height: 22px;"></i>
                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 20px;">
                                <div class="col-6" style="padding-right: 7.5px;">
                                    <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; height: 100%;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                            <div style="color: #667085; font-size: 11px; font-weight: 600;">TOTAL PURCHASES</div>
                                            <i data-feather="file-text" style="color: #00cfe8; width: 16px; height: 16px;"></i>
                                        </div>
                                        <div style="font-size: 20px; font-weight: 700; color: #1f2937;">{{ $purchaseInvoiceCount ?? 0 }}</div>
                                    </div>
                                </div>
                                <div class="col-6" style="padding-left: 7.5px;">
                                    <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0; height: 100%;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                            <div style="color: #667085; font-size: 11px; font-weight: 600;">THIS MONTH</div>
                                            <i data-feather="calendar" style="color: #ff9f43; width: 16px; height: 16px;"></i>
                                        </div>
                                        <div style="font-size: 20px; font-weight: 700; color: #1f2937;">{{ $purchaseInvoiceCount ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $completedPurchasePercentage = $purchaseInvoiceCount > 0 ? round(($completedPurchaseCount / $purchaseInvoiceCount) * 100) : 0;
                                $partiallyPaidPurchasePercentage = $purchaseInvoiceCount > 0 ? round(($partiallyPaidPurchaseCount / $purchaseInvoiceCount) * 100) : 0;
                                $pendingPurchasePercentage = $purchaseInvoiceCount > 0 ? round(($pendingPurchaseCount / $purchaseInvoiceCount) * 100) : 0;
                            @endphp
                            <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0;">
                                <div style="margin-bottom: 12px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                        <span style="color: #667085;">Completed</span>
                                        <span style="color: #1f2937;">{{ $completedPurchasePercentage }}% <span style="color: #999; margin-left: 5px;">({{ $completedPurchaseCount ?? 0 }})</span></span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                        <div class="progress-bar" style="width: {{ $completedPurchasePercentage }}%; background-color: #28c76f; border-radius: 3px;"></div>
                                    </div>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                        <span style="color: #667085;">Partially Paid</span>
                                        <span style="color: #1f2937;">{{ $partiallyPaidPurchasePercentage }}% <span style="color: #999; margin-left: 5px;">({{ $partiallyPaidPurchaseCount ?? 0 }})</span></span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                        <div class="progress-bar" style="width: {{ $partiallyPaidPurchasePercentage }}%; background-color: #00cfe8; border-radius: 3px;"></div>
                                    </div>
                                </div>
                                <div style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                        <span style="color: #667085;">Pending</span>
                                        <span style="color: #1f2937;">{{ $pendingPurchasePercentage }}% <span style="color: #999; margin-left: 5px;">({{ $pendingPurchaseCount ?? 0 }})</span></span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                        <div class="progress-bar" style="width: {{ $pendingPurchasePercentage }}%; background-color: #ff9f43; border-radius: 3px;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Latest Purchase Entry -->
                            @if($latestPurchases->isNotEmpty())
                                @php
                                    $latestPurchaseItem = $latestPurchases->first();
                                    $purchaseStatusColor = match(strtolower($latestPurchaseItem->purchase_status ?? '')) {
                                        'completed' => '#10b981',
                                        'partially' => '#3b82f6',
                                        default     => '#e87b1e',
                                    };
                                @endphp
                                <a href="{{ route('purchase.viewData', $latestPurchaseItem->invoice_id ?? $latestPurchaseItem->id ?? 0) }}" style="text-decoration: none; display: block; margin-top: 15px;">
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background-color: #ffffff; cursor: pointer; transition: border-color 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.borderColor='#e87b1e';this.style.boxShadow='0 2px 8px rgba(232,123,30,0.15)'" onmouseout="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'">
                                    <div>
                                        <h5 style="color: #111827; font-size: 14px; font-weight: 700; margin-bottom: 5px;">{{ $latestPurchaseItem->vendor_name ?? 'N/A' }}</h5>
                                        <p style="color: #6b7280; font-size: 11px; font-weight: 500; margin-bottom: 0;">
                                            <strong style="color: #1f2937;">Invoice: {{ $latestPurchaseItem->bill_no ?? 'N/A' }}</strong> &bull; {{ $latestPurchaseItem->purchase_date ? \Carbon\Carbon::parse($latestPurchaseItem->purchase_date)->format('d M, Y') : 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <h5 style="color: #111827; font-size: 14px; font-weight: 700; margin-bottom: 5px;">
                                            @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif
                                            {{ number_format($latestPurchaseItem->grand_total ?? 0, 2) }}
                                            @if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                        </h5>
                                    </div>
                                </div>
                                </a>
                            @endif

                            <!-- Footer Note -->
                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 12px 15px; display: flex; align-items: center; margin-top: 10px;">
                                <i data-feather="info" style="width: 14px; height: 14px; color: #6b7280; margin-right: 10px; stroke-width: 2.5;"></i>
                                <p style="color: #6b7280; font-size: 11px; font-weight: 500; margin-bottom: 0;">Overview is based on all visible purchases and their current statuses.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @php
            use Carbon\Carbon;
            $currentYear = Carbon::now()->year;
            $previousYear = $currentYear - 1;
        @endphp

        <div class="row">
            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                <div class="col-lg-6 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill" style="border: 1px solid #f0f0f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #e87c1e; color: white; padding: 15px; border-bottom: none;">
                            <div class="card-title mb-0" style="color: white; font-size: 18px; font-weight: 600; margin: 0;">Sales</div>
                            <div>
                                <select class="form-select form-select-sm" id="salesYearSelect" style="width: auto; background-color: white; color: #555; border: 1px solid #ccc; border-radius: 4px; padding: 4px 8px; outline: none; box-shadow: none;">
                                    <option value="month">This month ({{ date('F') }})</option>
                                    <option value="{{ $previousYear }}">Previous year ({{ $previousYear }})</option>
                                    <option value="{{ $currentYear }}" selected>This year ({{ $currentYear }})</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body chart-set" style="padding: 20px; background-color: white;">
                            <div class="h-250" style="width: 100%; height: 250px; position: relative;" id="saleschart">
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #999; font-size: 14px;">Loading chart...</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                <div class="col-lg-6 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill" style="border: 1px solid #f0f0f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #e87c1e; color: white; padding: 15px; border-bottom: none;">
                            <div class="card-title mb-0" style="color: white; font-size: 18px; font-weight: 600; margin: 0;">Purchases</div>
                            <div>
                                <select class="form-select form-select-sm" id="purchaseYearSelect" style="width: auto; background-color: white; color: #555; border: 1px solid #ccc; border-radius: 4px; padding: 4px 8px; outline: none; box-shadow: none;">
                                    <option value="month">This month ({{ date('F') }})</option>
                                    <option value="{{ $previousYear }}">Previous year ({{ $previousYear }})</option>
                                    <option value="{{ $currentYear }}" selected>This year ({{ $currentYear }})</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body chart-set" style="padding: 20px; background-color: white;">
                            <div class="h-250" style="width: 100%; height: 250px; position: relative;" id="purchasechart">
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #999; font-size: 14px;">Loading chart...</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            

            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                <div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-4">
                    <div class="card mb-0 h-100 recent-erp-card" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <div class="card-header d-flex justify-content-between align-items-center recent-card-header"
                            style="background-color: #ff9f43; color: white; padding: 12px 16px; border-bottom: none;">
                            <h4 class="card-title mb-0" style="color: white; font-size: 16px; font-weight: 600;">Recent Sales</h4>
                            <a href="{{ route('sales.list') }}" class="btn btn-sm view-all-btn"
                            style="background-color: #ffffff; color: #ff9f43; border-radius: 4px; font-weight: 600; padding: 4px 14px; border: none;">View All</a>
                        </div>
                        <div class="card-body p-0" style="border: 1px solid #f0f0f0; border-top: none; border-radius: 0 0 8px 8px;">
                            <div class="table-responsive db-recent-card-scroll">
                                <table class="table mb-0" style="table-layout: auto;">
                                    <thead style="background-color: #f9fafb;">
                                        <tr>
                                            <th style="width: 25%; font-weight: 600; color: #374151; border-bottom: 1px solid #f0f0f0;">Order ID</th>
                                            <th style="width: 35%; font-weight: 600; color: #374151; border-bottom: 1px solid #f0f0f0;">Product Name</th>
                                            <th style="width: 20%; font-weight: 600; color: #374151; border-bottom: 1px solid #f0f0f0;">Grand Total</th>
                                            <th style="width: 20%; font-weight: 600; color: #374151; border-bottom: 1px solid #f0f0f0;">Sale Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($latestSales as $index => $item)
                                            @php
                                                $images = json_decode($item->images, true);
                                                $imgFile = (!empty($images) && isset($images[0])) ? $images[0] : null;
                                                $imagePath = 'admin/assets/img/product/noimage.png';
                                                if ($imgFile && file_exists(public_path('storage/' . $imgFile))) {
                                                    $imagePath = 'storage/' . $imgFile;
                                                }
                                            @endphp
                                            <tr class="db-recent-row" style="cursor: pointer;" onclick="window.location='/sales-details/{{ $item->order_id }}'">
                                                <td style="color: #4b5563; vertical-align: middle;">
                                                    <span class="order-id" style="font-weight: 700; color: #1b2850;">{{ $item->order_number ?? 'N/A' }}</span>
                                                    <div class="dashboard-customer-name" style="font-size: 11px; color: #6b7280; margin-top: 2px;">{{ $item->customer_name ?? 'N/A' }}</div>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <img src="{{ asset($imagePath) }}" alt="Product"
                                                            style="width: 36px; height: 36px; object-fit: cover; border-radius: 6px; flex-shrink: 0; border: 1px solid #e5e7eb;"
                                                            onerror="this.onerror=null;this.src='{{ asset('admin/assets/img/product/noimage.png') }}';">
                                                        <span class="dashboard-product-name"
                                                            style="color: #374151; font-weight: 500; font-size: 13px;">{{ $item->product_name ?? 'N/A' }}</span>
                                                    </div>
                                                </td>
                                                <td style="color: #111827; font-weight: 600; vertical-align: middle;">
                                                    @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($item->total_amount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                                </td>
                                                <td style="color: #6b7280; vertical-align: middle; font-size: 12px;">
                                                    {{ \Carbon\Carbon::parse($item->order_date)->format('d F Y') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted" style="padding: 20px;">No sales records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Latest Purchases Table - UPDATED -->
            @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                <div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-4">
                    <div class="card mb-0 h-100 recent-erp-card" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <div class="card-header d-flex justify-content-between align-items-center recent-card-header"
                            style="background-color: #ff9f43; color: white; padding: 12px 16px; border-bottom: none;">
                            <h4 class="card-title mb-0" style="color: white; font-size: 16px; font-weight: 600;">Recent Purchases</h4>
                            <a href="{{ route('purchase.lists') }}" class="btn btn-sm view-all-btn"
                            style="background-color: #ffffff; color: #ff9f43; border-radius: 4px; font-weight: 600; padding: 4px 14px; border: none;">View All</a>
                        </div>
                        <div class="card-body p-0" style="border: 1px solid #f0f0f0; border-top: none; border-radius: 0 0 8px 8px;">
                            <div class="table-responsive db-recent-card-scroll">
                                <table class="table mb-0" style="table-layout: auto;">
                                    <thead style="background-color: #f9fafb;">
                                        <tr>
                                            <th style="width: 25%; font-weight: 600; color: #374151; border-bottom: 1px solid #f0f0f0;">Purchase ID</th>
                                            <th style="width: 35%; font-weight: 600; color: #374151; border-bottom: 1px solid #f0f0f0;">Product Name</th>
                                            <th style="width: 20%; font-weight: 600; color: #374151; border-bottom: 1px solid #f0f0f0;">Grand Total</th>
                                            <th style="width: 20%; font-weight: 600; color: #374151; border-bottom: 1px solid #f0f0f0;">Purchase Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($latestPurchases as $index => $item)
                                            @php
                                                $images = json_decode($item->images, true);
                                                $imgFile = (!empty($images) && isset($images[0])) ? $images[0] : null;
                                                $imagePath = 'admin/assets/img/product/noimage.png';
                                                if ($imgFile && file_exists(public_path('storage/' . $imgFile))) {
                                                    $imagePath = 'storage/' . $imgFile;
                                                }
                                            @endphp
                                            <tr class="db-recent-row" style="cursor: pointer;" onclick="window.location='/print-purchase/{{ $item->invoice_id }}'">
                                                <td style="color: #4b5563; vertical-align: middle;">
                                                    <span class="order-id" style="font-weight: 700; color: #1b2850;">{{ $item->bill_no ?? $item->invoice_number ?? 'N/A' }}</span>
                                                    <div class="dashboard-vendor-name" style="font-size: 11px; color: #6b7280; margin-top: 2px;">{{ $item->vendor_name ?? 'N/A' }}</div>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <img src="{{ asset($imagePath) }}" alt="Product"
                                                            style="width: 36px; height: 36px; object-fit: cover; border-radius: 6px; flex-shrink: 0; border: 1px solid #e5e7eb;"
                                                            onerror="this.onerror=null;this.src='{{ asset('admin/assets/img/product/noimage.png') }}';">
                                                        <span class="dashboard-product-name"
                                                            style="color: #374151; font-weight: 500; font-size: 13px;">{{ $item->product_name ?? 'N/A' }}</span>
                                                    </div>
                                                </td>
                                                <td style="color: #111827; font-weight: 600; vertical-align: middle;">
                                                    @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($item->grand_total ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                                </td>
                                                <td style="color: #6b7280; vertical-align: middle; font-size: 12px;">
                                                    {{ $item->purchase_date ? \Carbon\Carbon::parse($item->purchase_date)->format('d F Y') : 'N/A' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted" style="padding: 20px;">No purchase records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Latest Products Delivery Table -->
            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                <div class="col-xl-4 col-lg-12 col-md-12 col-12 mb-4">
                    <div class="card mb-0 h-100" style="border-radius: 8px; overflow: hidden;">
                        <div class="card-header d-flex justify-content-between align-items-center"
                            style="background-color: #ff9f43; color: white; padding: 15px; border-bottom: none;">
                            <h4 class="card-title mb-0" style="color: white; font-size: 16px; font-weight: 600;">Products Delivery</h4>
                            <a href="{{ route('sales.products-delivery') }}" class="btn btn-sm bg-white"
                            style="color: #ff9f43; border-radius: 4px; font-weight: 600; padding: 4px 10px;">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order#</th>
                                            <th>Delivere...</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($latestDeliveries as $index => $item)
                                            <tr>
                                                <td>
                                                    <a href="/sales-details/{{ $item->order_id }}" class="text-decoration-none" style="display: block;">
                                                        <span class="order-id" style="display: block; margin-bottom: 2px;">{{ $item->order_number ?? 'N/A' }}</span>
                                                        <span style="color: #666; font-size: 0.85em; display: block; line-height: 1.2;">{{ $item->customer_name ?? 'Default Customer' }}</span>
                                                    </a>
                                                </td>
                                                <td>{{ !empty(trim($item->staff_name ?? '')) ? $item->staff_name : 'Not Assigned' }}</td>
                                                <td>
                                                    @if ($item->delivery_status == 'delivered')
                                                        <span class="badge bg-success">Delivered</span>
                                                    @elseif($item->delivery_status == 'partially_delivered')
                                                        <span class="badge bg-info">Partially Deliv</span>
                                                    @elseif($item->delivery_status == 'cancelled')
                                                        <span class="badge bg-danger">Cancelled</span>
                                                    @else
                                                        <span class="badge bg-warning">Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No deliveries found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($showHrDashboardSection && in_array($role, ['inventory-manager', 'admin', 'sub-admin']) && ($showHrStaffStrength || $showHrActiveStaff || $showHrMonthlyAttendance || $showHrPersonalProgress || $showHrAttendancePattern || $showHrSalaryPayrollTrend || $showHrPayrollSnapshot || $showHrAttendanceWatch || $showHrPayrollStatus))
            <div class="row">
                <div class="col-12 hr-section mt-0 pt-0">
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                        <div>
                            <h4 class="mb-1">HR Workforce Overview</h4>
                            <!-- <p class="text-muted mb-0">Staff strength, attendance performance, payroll progress, and advance exposure.</p> -->
                        </div>
                        <a href="{{ route('staff.list') }}" class="btn btn-sm btn-primary">View Staff</a>
                    </div>
                </div>

                @if ($showHrStaffStrength)
                <div class="col-lg-3 col-sm-6 col-6 d-flex hr-section">
                    <a href="{{ route('staff.list') }}" class="color_box w-100">
                    <div class="card flex-fill hr-metric" style="cursor:pointer;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="hr-metric-label">Staff Strength</div>
                            <div class="hr-metric-value">{{ number_format($totalStaff, 0) }}</div>
                            <p class="hr-metric-note">{{ number_format($activeStaff, 0) }} active, {{ number_format($newStaffThisMonth, 0) }} joined this month</p>
                        </div>
                        <div style="background-color: #fff3e8; border-radius: 8px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: 12px;">
                            <i data-feather="users" style="width: 20px; height: 20px; color: #e87b1e; stroke-width: 2;"></i>
                        </div>
                    </div>
                    </div>
                    </a>
                </div>
                @endif

                @if ($showHrActiveStaff)
                <div class="col-lg-3 col-sm-6 col-6 d-flex hr-section">
                    <a href="{{ route('attendence.summary') }}" class="color_box w-100">
                    <div class="card flex-fill hr-metric" style="cursor:pointer;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="hr-metric-label">Today Attendance</div>
                                <div class="hr-metric-value">{{ number_format($todayPresentStaff, 0) }}</div>
                                <p class="hr-metric-note">{{ number_format($todayAbsentStaff, 0) }} absent, {{ number_format($todayUnmarkedStaff, 0) }} unmarked</p>
                            </div>
                            <div style="background-color: #e8f8f0; border-radius: 8px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: 12px;">
                                <i data-feather="check-circle" style="width: 20px; height: 20px; color: #10b981; stroke-width: 2;"></i>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>
                @endif

                @if ($showHrMonthlyAttendance)
                <div class="col-lg-3 col-sm-6 col-6 d-flex hr-section">
                    <a href="{{ route('attendence.summary') }}" class="color_box w-100">
                    <div class="card flex-fill hr-metric" style="cursor:pointer;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="hr-metric-label">Monthly Attendance</div>
                                <div class="hr-metric-value">{{ $monthlyAttendanceRate }}%</div>
                                <p class="hr-metric-note">Based on marked present, half-day, and absent entries</p>
                            </div>
                            <div style="background-color: #eef2ff; border-radius: 8px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: 12px;">
                                <i data-feather="bar-chart-2" style="width: 20px; height: 20px; color: #6366f1; stroke-width: 2;"></i>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>
                @endif

                @if ($showHrPersonalProgress)
                <div class="col-lg-3 col-sm-6 col-6 d-flex hr-section">
                    <a href="{{ route('payroll.list') }}" class="color_box w-100">
                    <div class="card flex-fill hr-metric" style="cursor:pointer;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="hr-metric-label">Payroll Progress</div>
                            <div class="hr-metric-value">{{ number_format($salaryPaidCount, 0) }}/{{ number_format($totalStaff, 0) }}</div>
                            <p class="hr-metric-note">{{ number_format($salaryPendingCount, 0) }} pending this month</p>
                        </div>
                        <div style="background-color: #fef3c7; border-radius: 8px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: 12px;">
                            <i data-feather="credit-card" style="width: 20px; height: 20px; color: #f59e0b; stroke-width: 2;"></i>
                        </div>
                    </div>
                    </div>
                    </a>
                </div>
                @endif

                @if ($showHrPayrollSnapshot)
                <div class="col-lg-4 col-md-4 col-sm-12 col-12 d-flex hr-section">
                    <div class="card flex-fill" style="border-radius: 8px; overflow: hidden;">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #ff9f43; padding: 15px; border-bottom: none;">
                            <h4 class="mb-0" style="color: white; font-size: 16px; font-weight: 600;">Payroll Snapshot</h4>
                            <a href="{{ route('payroll.list') }}" class="btn btn-sm bg-white" style="color: #ff9f43; border-radius: 4px; font-weight: 600; padding: 4px 10px;">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td>Paid this month</td>
                                            <td class="text-end">
                                                @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif
                                                {{ number_format($salaryPaidAmount, 2) }}
                                                @if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Outstanding advances</td>
                                            <td class="text-end">
                                                @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif
                                                {{ number_format($advanceOutstanding, 2) }}
                                                @if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Salary pending staff</td>
                                            <td class="text-end">{{ number_format($salaryPendingCount, 0) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if ($showHrAttendanceWatch)
                <div class="col-lg-4 col-md-4 col-sm-12 col-12 d-flex hr-section">
                    <div class="card flex-fill" style="border-radius: 8px; overflow: hidden;">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #ff9f43; padding: 15px; border-bottom: none;">
                            <h4 class="mb-0" style="color: white; font-size: 16px; font-weight: 600;">Attendance Watch</h4>
                            <a href="{{ route('payroll.list') }}" class="btn btn-sm bg-white" style="color: #ff9f43; border-radius: 4px; font-weight: 600; padding: 4px 10px;">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Staff</th>
                                            <th>Present</th>
                                            <th>Absent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($attendanceWatch as $staffRow)
                                            <tr>
                                                <td>{{ $staffRow->name }}</td>
                                                <td>{{ number_format($staffRow->present_days, 0) }}</td>
                                                <td>
                                                    <span class="hr-mini-badge bg-light text-dark">{{ number_format($staffRow->absent_days, 0) }}</span>
                                                    @if ($staffRow->half_days > 0)
                                                        <div class="text-muted small">{{ number_format($staffRow->half_days, 0) }} half-day</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No attendance data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if ($showHrPayrollStatus)
                <div class="col-lg-4 col-md-4 col-sm-12 col-12 d-flex hr-section">
                    <div class="card flex-fill" style="border-radius: 8px; overflow: hidden;">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #ff9f43; padding: 15px; border-bottom: none;">
                            <h4 class="mb-0" style="color: white; font-size: 16px; font-weight: 600;">Payroll Status</h4>
                            <a href="{{ route('payroll.list') }}" class="btn btn-sm bg-white" style="color: #ff9f43; border-radius: 4px; font-weight: 600; padding: 4px 10px;">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Staff</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payrollStatusRows as $payrollRow)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('staff.view', $payrollRow['id']) }}">{{ $payrollRow['name'] }}</a>
                                                    <div class="text-muted small">{{ $payrollRow['phone'] ?? 'N/A' }}</div>
                                                </td>
                                                <td>
                                                    <span class="hr-mini-badge bg-light text-dark">{{ $payrollRow['status'] }}</span>
                                                </td>
                                                <td>
                                                    @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif
                                                    {{ number_format($payrollRow['amount'], 2) }}
                                                    @if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No payroll data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if ($showHrAttendancePattern)
                <div class="col-lg-7 col-sm-12 col-12 d-flex hr-section">
                    <div class="card flex-fill" style="border-radius: 8px; overflow: hidden;">
                        <div class="card-header" style="background-color: #ff9f43; padding: 15px; border-bottom: none;">
                            <h4 class="mb-0" style="color: white; font-size: 16px; font-weight: 600;">7 Day Attendance Pattern</h4>
                        </div>
                        <div class="card-body">
                            <div id="hrAttendanceChart" class="hr-chart"></div>
                        </div>
                    </div>
                </div>
                @endif

                @if ($showHrSalaryPayrollTrend)
                <div class="col-lg-5 col-sm-12 col-12 d-flex hr-section">
                    <div class="card flex-fill" style="border-radius: 8px; overflow: hidden;">
                        <div class="card-header" style="background-color: #ff9f43; padding: 15px; border-bottom: none;">
                            <h4 class="mb-0" style="color: white; font-size: 16px; font-weight: 600;">Salary Payout Trend</h4>
                        </div>
                        <div class="card-body">
                            <div id="hrSalaryChart" class="hr-chart"></div>
                        </div>
                    </div>
                </div>
                @endif

                
            </div>
        @endif
    </div>

@endsection

@push('js')
    <!-- Load ApexCharts AFTER jQuery is fully loaded -->
    <script>
        // Wait for jQuery to be ready
        if (typeof jQuery !== 'undefined') {
            // Load ApexCharts dynamically to avoid conflicts
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js';
            script.async = true;
            script.onload = function() {
                console.log('✓ ApexCharts loaded successfully');
            };
            script.onerror = function() {
                console.error('❌ Failed to load ApexCharts');
            };
            document.head.appendChild(script);
        }
    </script>

    <script>
        $(document).ready(function() {
            // Toggle details icon animation
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
    </script>
    <script>
        const currentYear = {{ $currentYear }};
        const previousYear = {{ $previousYear }};
        const salesThisMonth = @json($salesChartThisMonth);
        const purchaseThisMonth = @json($purchaseChartThisMonth);

        const salesChartData = {
            [currentYear]: @json($salesChartthisyear),
            [previousYear]: @json($salesChartpreviousyear),
            'thisMonth': salesThisMonth
        };

        const purchaseChartData = {
            [currentYear]: @json($purchaseChartthisyear),
            [previousYear]: @json($purchaseChartpreviousyear),
            'thisMonth': purchaseThisMonth
        };

        const hrAttendanceLabels = Object.values(@json($hrAttendanceLabels ?? []));
        const hrAttendancePresent = Object.values(@json($hrAttendancePresent ?? []));
        const hrAttendanceAbsent = Object.values(@json($hrAttendanceAbsent ?? []));
        const hrAttendanceHalfDay = Object.values(@json($hrAttendanceHalfDay ?? []));
        const hrSalaryLabels = Object.values(@json($hrSalaryLabels ?? []));
        const hrSalaryPaidAmounts = Object.values(@json($hrSalaryPaidAmounts ?? []));
        const hrSalaryPaidCounts = Object.values(@json($hrSalaryPaidCounts ?? []));
        
        // DEBUG: Log raw PHP variables before JSON conversion
        console.log('===== HR DATA DEBUG =====');
        console.log('PHP Attendance Labels Count:', {{ count($hrAttendanceLabels ?? []) }});
        console.log('PHP Salary Labels Count:', {{ count($hrSalaryLabels ?? []) }});

        // ✅ Updated chart options for Flot library
        const options = {
            grid: {
                borderWidth: 1,
                borderColor: 'rgba(67, 87, 133, .09)',
                hoverable: true
            },
            xaxis: {
                ticks: [],
                tickColor: 'rgba(67, 87, 133, .09)',
                font: {
                    color: '#8e9cad',
                    size: 9
                },
                autoscaleMargin: 0.02
            },
            yaxis: {
                tickColor: 'rgba(67, 87, 133, .09)',
                font: {
                    color: '#8e9cad',
                    size: 10
                }
            },
            legend: {
                show: true,
                position: "nw"
            },
            tooltip: true,
            tooltipOpts: {
                content: "%s: %y",
                shifts: { x: 10, y: 20 },
                defaultTheme: false
            }
        };

        function getDayTicks(length) {
            return Array.from({ length }, (_, i) => [i, (i + 1).toString()]);
        }

        function renderChart(containerId, label, data, barColor, isMonthly = true) {
            if (!document.querySelector(containerId)) return;
            const series = data.map((val, idx) => [idx, val]);

            options.xaxis.ticks = isMonthly ? [
                    [0, 'Jan'], [1, 'Feb'], [2, 'Mar'], [3, 'Apr'], [4, 'May'], [5, 'Jun'],
                    [6, 'Jul'], [7, 'Aug'], [8, 'Sep'], [9, 'Oct'], [10, 'Nov'], [11, 'Dec']
                ] :
                getDayTicks(data.length);

            $.plot(containerId, [{
                label,
                data: series,
                lines: {
                    show: true,
                    fill: true,
                    fillColor: { colors: [{ opacity: 0.2 }, { opacity: 0.1 }] }
                },
                points: {
                    show: true,
                    radius: 4,
                    fillColor: "#fff",
                    lineWidth: 2
                },
                color: barColor
            }], options);
        }

        // Function to render HR charts with ApexCharts
        function renderHRCharts() {
            // Wait for ApexCharts to be available (max 5 seconds)
            let attempts = 0;
            const maxAttempts = 50; // 50 * 100ms = 5 seconds

            const checkAndRender = setInterval(function() {
                attempts++;

                if (window.ApexCharts && typeof ApexCharts !== 'undefined') {
                    clearInterval(checkAndRender);
                    console.log('✓ ApexCharts is now available, rendering charts...');
                    
                    @if ($showHrDashboardSection && in_array($role, ['inventory-manager', 'admin', 'sub-admin']))
                        // Render Attendance Chart
                        if (document.querySelector('#hrAttendanceChart')) {
                            try {
                                if (hrAttendanceLabels && Array.isArray(hrAttendanceLabels) && hrAttendanceLabels.length > 0) {
                                    console.log('✓ Rendering Attendance Chart...');
                                    const attendanceChartOptions = {
                                        chart: {
                                            type: 'bar',
                                            height: 260,
                                            stacked: true,
                                            toolbar: { show: false },
                                            animations: { enabled: true }
                                        },
                                        series: [
                                            { name: 'Present', data: hrAttendancePresent },
                                            { name: 'Half-day', data: hrAttendanceHalfDay },
                                            { name: 'Absent', data: hrAttendanceAbsent }
                                        ],
                                        colors: ['#10b981', '#ff9f43', '#ef4444'],
                                        plotOptions: {
                                            bar: { columnWidth: '42%', borderRadius: 3 }
                                        },
                                        xaxis: { categories: hrAttendanceLabels },
                                        yaxis: {
                                            min: 0,
                                            max: Math.max(...hrAttendancePresent, ...hrAttendanceHalfDay, ...hrAttendanceAbsent, 10),
                                            forceNiceScale: true,
                                            labels: { formatter: (v) => Number.isFinite(v) ? Math.round(v) : '0' }
                                        },
                                        legend: { position: 'top', horizontalAlign: 'right' },
                                        grid: { borderColor: '#edf0f5' }
                                    };
                                    new ApexCharts(document.querySelector('#hrAttendanceChart'), attendanceChartOptions).render();
                                    console.log('✓ Attendance chart rendered');
                                } else {
                                    document.querySelector('#hrAttendanceChart').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 260px; color: #999;">No data available</div>';
                                }
                            } catch (e) {
                                console.error('Error rendering attendance chart:', e.message);
                            }
                        }

                        // Render Salary Chart
                        if (document.querySelector('#hrSalaryChart')) {
                            try {
                                if (hrSalaryLabels && Array.isArray(hrSalaryLabels) && hrSalaryLabels.length > 0) {
                                    console.log('✓ Rendering Salary Chart...');
                                    const salaryChartOptions = {
                                        chart: {
                                            height: 260,
                                            type: 'line',
                                            toolbar: { show: false },
                                            zoom: { enabled: false },
                                            animations: { enabled: true }
                                        },
                                        series: [
                                            { name: 'Payout', type: 'column', data: hrSalaryPaidAmounts },
                                            { name: 'Paid Staff', type: 'line', data: hrSalaryPaidCounts }
                                        ],
                                        colors: ['#4361ee', '#10b981'],
                                        stroke: { width: [0, 3], curve: 'smooth' },
                                        plotOptions: {
                                            bar: { columnWidth: '45%', borderRadius: 3 }
                                        },
                                        xaxis: { categories: hrSalaryLabels },
                                        yaxis: [
                                            {
                                                min: 0,
                                                labels: { formatter: (v) => Math.round(v).toLocaleString() },
                                                title: { text: 'Payout Amount' }
                                            },
                                            {
                                                opposite: true,
                                                min: 0,
                                                forceNiceScale: true,
                                                labels: { formatter: (v) => Math.round(v) },
                                                title: { text: 'Paid Staff Count' }
                                            }
                                        ],
                                        grid: { borderColor: '#edf0f5' },
                                        legend: { position: 'top', horizontalAlign: 'right' }
                                    };
                                    new ApexCharts(document.querySelector('#hrSalaryChart'), salaryChartOptions).render();
                                    console.log('✓ Salary chart rendered');
                                } else {
                                    document.querySelector('#hrSalaryChart').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 260px; color: #999;">No data available</div>';
                                }
                            } catch (e) {
                                console.error('Error rendering salary chart:', e.message);
                            }
                        }
                    @endif
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkAndRender);
                    console.warn('⚠️ ApexCharts failed to load after 5 seconds');
                }
            }, 100);
        }

        $(document).ready(function() {
            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                if ($('#saleschart').length > 0) {
                    renderChart('#saleschart', 'Orders', salesChartData[currentYear], '#44c4fa');
                    $('#salesYearSelect').on('change', function() {
                        const year = $(this).val();
                        if (year === 'month') {
                            renderChart('#saleschart', 'Orders (This Month)', salesChartData['thisMonth'], '#44c4fa', false);
                        } else {
                            renderChart('#saleschart', 'Orders', salesChartData[year], '#44c4fa');
                        }
                    });
                }
            @endif

            @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                if ($('#purchasechart').length > 0) {
                    renderChart('#purchasechart', 'Purchases', purchaseChartData[currentYear], '#fa6c7c');
                    $('#purchaseYearSelect').on('change', function() {
                        const year = $(this).val();
                        if (year === 'month') {
                            renderChart('#purchasechart', 'Purchases (This Month)', purchaseChartData['thisMonth'], '#fa6c7c', false);
                        } else {
                            renderChart('#purchasechart', 'Purchases', purchaseChartData[year], '#fa6c7c');
                        }
                    });
                }
            @endif

            @if ($showHrDashboardSection && in_array($role, ['inventory-manager', 'admin', 'sub-admin']))
                // Trigger HR charts rendering after ApexCharts loads
                renderHRCharts();
            @endif
        });
    </script>
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            let url = "{{ url('/api/dashboard-api') }}";

            if (selectedSubAdminId) {
                url += `?selectedSubAdminId=${selectedSubAdminId}`;
            }
            $.ajax({
                url: url,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                dataType: "json",
                success: function(response) {
                    console.log("Branch:", response.branch_id);
                    if (response.status) {
                        console.log(response.data);

                    // Example: Update totals
                    $('#totalPurchase').text(response.data.totals.purchase);
                    $('#totalSales').text(response.data.totals.sales);
                    $('#totalExpense').text(response.data.totals.expense);
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    // Update Platform Order Counts on Dashboard Load
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof dummyPlatformData !== 'undefined') {
            const zomatoCount = dummyPlatformData['Zomato'] ? dummyPlatformData['Zomato'].length : 0;
            const swiggyCount = dummyPlatformData['Swiggy'] ? dummyPlatformData['Swiggy'].length : 0;
            
            const zEl = document.getElementById('dashZomatoCount');
            if (zEl) zEl.textContent = zomatoCount;
            
            const sEl = document.getElementById('dashSwiggyCount');
            if (sEl) sEl.textContent = swiggyCount;
        }
    });
</script>
@endpush

<!-- Debug: Show data status -->
<script>
    // Debug script to check if data is being passed properly
    console.log('=== DASHBOARD DATA DEBUG ===');
    console.log('HR Section Enabled:', {{ $showHrDashboardSection ? 'true' : 'false' }});
    console.log('User Role:', '{{ $role }}');
    console.log('Attendance Pattern Days:', {{ count($hrAttendanceLabels ?? []) }});
    console.log('Salary Data Months:', {{ count($hrSalaryLabels ?? []) }});
    
    // If data is empty, provide helpful information
    @if (empty($hrAttendanceLabels) || count($hrAttendanceLabels) === 0)
        console.warn('⚠️ WARNING: No attendance data found. This could mean:');
        console.warn('   1. No attendance records exist for the current branch');
        console.warn('   2. Attendance data is not being fetched correctly');
        console.warn('   3. Branch ID filter may be excluding all data');
        console.warn('   Current Branch ID being used:', {{ $user->id ?? "unknown" }});
    @endif
    
    @if (empty($hrSalaryLabels) || count($hrSalaryLabels) === 0)
        console.warn('⚠️ WARNING: No salary/payroll data found. This could mean:');
        console.warn('   1. No payroll records exist for the current branch');
        console.warn('   2. Payroll data is not being fetched correctly');
        console.warn('   3. Branch ID filter may be excluding all data');
    @endif
    
    // Additional system info
    console.log('📋 SYSTEM INFO:');
    console.log('  Total Staff:', {{ $totalStaff ?? 0 }});
    console.log('  Active Staff:', {{ $activeStaff ?? 0 }});
    console.log('  Today Present:', {{ $todayPresentStaff ?? 0 }});
    console.log('  Today Absent:', {{ $todayAbsentStaff ?? 0 }});
</script>
