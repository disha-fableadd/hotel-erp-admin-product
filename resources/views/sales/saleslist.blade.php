@extends('layout.app')

@section('title', 'Orders List')

@section('content')
<style>
    .table-scroll-top {
        display: none;
    }

    /* @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
            }
        } */
    /* ===== Pooja ERP Mobile & Tablet Card UI Styles ===== */
    .sl-card,
    .sl-m-only,
    .sl-quick-links,
    .sl-filter-backdrop,
    .sl-filter-sheet,
    .sl-fab-new-sale,
    .sales-filter-toolbar {
        display: none !important;
    }

    /* Desktop: hide details column */
    @media (min-width: 1281px) {
        table.datanew thead th.details-column,
        table.datanew tbody td:nth-child(2) {
            display: none !important;
        }
    }

    /* Mobile & Tablet view (<= 1280.98px) */
    @media (max-width: 1280.98px) {
        .page-wrapper:has(.sl-quick-links) {
            padding-top: 0 !important;
        }
        .content:has(.sl-quick-links) {
            padding-left: 8px !important;
            padding-right: 8px !important;
            padding-top: 6px !important;
        }
        .content:has(.sl-quick-links) > .page-header {
            display: none !important;
        }
        .content:has(.sl-quick-links) > .card {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            margin: 0 !important;
        }
        .content:has(.sl-quick-links) > .card > .card-body {
            padding: 0 !important;
        }
        .content:has(.sl-quick-links) .table-scroll-top,
        .purchase-filter-card {
            display: none !important;
        }

        /* Quick Links Card (Header + Summary Badges) */
        .sl-quick-links {
            display: block !important;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, 0.08);
            padding: 12px 10px 10px;
            margin-bottom: 8px;
        }
        .sl-quick-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }
        .sl-quick-title {
            font-size: 16px;
            font-weight: 700;
            color: #1b2850;
            margin: 0 0 0 2px;
            white-space: nowrap;
        }
        .sl-quick-actions {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }
        .sl-quick-actions .btn {
            display: inline-flex;
            align-items: center;
            height: 32px;
            padding: 0 12px !important;
            font-size: 13px !important;
            font-weight: 600;
            white-space: nowrap;
            margin: 0 !important;
        }

        /* Filter Toolbar (Search + Red Filter Icon) */
        .sales-filter-toolbar {
            display: flex !important;
            align-items: center;
            gap: 8px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, 0.08);
            padding: 8px 10px !important;
            margin: 0 0 10px !important;
        }
        .sales-filter-toolbar .filter-search {
            display: flex !important;
            align-items: center;
            gap: 8px;
            flex: 1 1 auto;
            min-width: 0;
        }
        .sales-filter-toolbar .filter-search .search-input {
            position: relative;
            flex: 1 1 auto;
            min-width: 0;
        }
        .sales-filter-toolbar .filter-search .search-input input {
            height: 40px !important;
            border-radius: 8px !important;
            border: 1px solid #d1d5db !important;
            padding-left: 38px !important;
            padding-right: 12px !important;
            font-size: 14px !important;
            width: 100% !important;
            background: #fff;
        }
        .sales-filter-toolbar .filter-search .search-input .btn-searchset {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            padding: 0;
            border: 0;
            background: transparent;
            color: #9ca3af;
            font-size: 15px;
            pointer-events: none;
        }
        .sales-filter-toggle {
            display: inline-flex !important;
            position: relative;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: #f05a6a;
            color: #fff;
            font-size: 16px;
            box-shadow: 0 2px 6px rgba(240, 90, 106, 0.35);
            cursor: pointer;
        }
        .sales-filter-toggle.has-active {
            background: #e8334a;
        }
        .sales-filter-count {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #1b2850;
            color: #fff;
            font-size: 11px;
            line-height: 18px;
            text-align: center;
        }

        /* Filter Bottom Sheet (Modal matching Dolphin & Pooja ERP) */
        .sl-filter-backdrop {
            display: block !important;
            position: fixed;
            inset: 0;
            z-index: 1040;
            background: rgba(16, 24, 40, 0.45);
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
        }
        .sl-filter-sheet {
            display: flex !important;
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1045;
            flex-direction: column;
            max-height: 85vh;
            max-width: 520px;
            margin: 0 auto;
            background: #fff;
            border-radius: 24px 24px 0 0;
            box-shadow: 0 -8px 24px rgba(16, 24, 40, 0.18);
            transform: translateY(105%);
            transition: transform .25s ease;
        }
        body.sl-filter-open .sl-filter-backdrop {
            opacity: 1;
            pointer-events: auto;
        }
        body.sl-filter-open .sl-filter-sheet {
            transform: translateY(0);
        }
        body.sl-filter-open {
            overflow: hidden;
        }
        body.sl-filter-open .mobile-bottom-nav,
        body.sl-filter-open .floating-calculator-wrapper,
        body.sl-filter-open .sl-fab-new-sale {
            display: none !important;
        }
        .sl-filter-sheet-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #eef0f4;
            font-size: 18px;
            font-weight: 700;
            color: #1b2850;
        }
        .sl-filter-sheet-close {
            border: 0;
            background: transparent;
            color: #2f6fd8;
            font-size: 20px;
            width: 32px;
            height: 32px;
            padding: 0;
            cursor: pointer;
        }
        .sl-filter-sheet-body {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 10px 20px;
        }
        .sl-filter-sheet-body .filter-field {
            display: block !important;
            width: 100% !important;
            padding: 10px 0 !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .sl-sheet-label {
            display: block;
            margin: 0 0 6px;
            font-size: 13px;
            font-weight: 600;
            color: #475467;
        }
        .sl-filter-sheet-body .form-select,
        .sl-filter-sheet-body .form-control {
            height: 42px !important;
            border-radius: 8px !important;
            border: 1px solid #d1d5db;
            font-size: 14px;
            color: #1b2850 !important;
        }
        .sl-filter-sheet-foot {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 14px 20px calc(14px + env(safe-area-inset-bottom, 0px));
            border-top: 1px solid #eef0f4;
            background: #fff;
        }
        .sl-filter-clear,
        .sl-filter-apply {
            height: 46px;
            border: 0;
            border-radius: 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }
        .sl-filter-clear {
            background: #eef0f4;
            color: #475467;
        }
        .sl-filter-apply {
            background: #e8334a;
            color: #fff;
        }

        /* Summary Badges Row (Mobile & Tablet) */
        .summary-badges-row {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) auto !important;
            align-items: stretch;
            gap: 6px !important;
            margin: 0 !important;
        }
        .summary-badges-row .summary-badge-box {
            display: flex !important;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            gap: 2px;
            min-width: 0;
            width: auto !important;
            margin: 0 !important;
            padding: 5px 8px !important;
            border-radius: 10px !important;
            border: 1px solid transparent;
            line-height: 1.2;
        }
        .summary-badge-box.pending {
            background: #fff0f1 !important;
            border-color: #ffd6d9 !important;
            color: #e5483d !important;
        }
        .summary-badge-box.paid {
            background: #edfbf3 !important;
            border-color: #c7f3d9 !important;
            color: #12a150 !important;
        }
        .summary-badge-box.total {
            background: #f4f7fb !important;
            border-color: #dce4f0 !important;
            color: #3b5998 !important;
        }
        .summary-badges-row .summary-badge-box span:first-child {
            font-size: 10px;
            font-weight: 600;
            opacity: .85;
            white-space: nowrap;
        }
        .summary-badges-row .summary-badge-box span:last-child {
            margin: 0 !important;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .summary-badges-row .summary-export-group {
            display: flex !important;
            flex-direction: row;
            align-items: stretch;
            gap: 4px;
            margin: 0 !important;
            width: auto !important;
        }
        .summary-badges-row .summary-export-group #exportAllChallan,
        .summary-badges-row .summary-export-group #exportPdf {
            width: 36px !important;
            min-width: 0 !important;
            height: auto !important;
            min-height: 36px;
            padding: 0 !important;
            margin: 0 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            font-size: 0 !important;
            border-radius: 8px !important;
        }
        .summary-badges-row .summary-export-group #exportAllChallan i,
        .summary-badges-row .summary-export-group #exportPdf i {
            font-size: 15px !important;
            margin: 0 !important;
        }

        /* Floating Add Sale Button */
        .sl-fab-new-sale {
            position: fixed;
            left: 50%;
            bottom: 74px;
            transform: translateX(-50%);
            z-index: 1030;
            display: inline-flex !important;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 30px;
            background: #e8334a;
            color: #fff !important;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 6px 16px rgba(232, 51, 74, 0.35);
            white-space: nowrap;
        }
        .sl-fab-new-sale i {
            width: 24px;
            height: 24px;
            border: 2px solid #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }
        .content:has(.sl-fab-new-sale) {
            padding-bottom: 90px !important;
        }

        /* Table transformation to Pooja ERP Cards */
        .table-responsive {
            overflow: visible !important;
            border: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #order-table,
        #order-table tbody {
            display: block !important;
            width: 100% !important;
            border: 0 !important;
        }
        #order-table thead,
        #order-table tbody tr.order-details-row,
        #order-table tbody .sl-desktop-head,
        #order-table tbody td:nth-child(n+2):nth-child(-n+10) {
            display: none !important;
        }
        #order-table tbody tr:not(.order-details-row) {
            display: block !important;
            position: relative;
            background: #fff;
            border: 1px solid #eef0f4;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, 0.06);
            margin: 0 0 10px;
            padding: 12px 14px;
        }
        #order-table tbody tr > td:first-child {
            display: block !important;
            width: 100% !important;
            max-width: none !important;
            min-width: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
        }
        #order-table tbody tr > td:last-child:not(.dataTables_empty) {
            display: flex !important;
            align-items: center;
            position: absolute;
            right: 8px;
            bottom: 10px;
            width: auto !important;
            max-width: none !important;
            min-width: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            gap: 4px;
        }
        #order-table tbody td.dataTables_empty {
            display: block !important;
            text-align: center;
            padding: 24px !important;
            background: #fff;
            border-radius: 10px;
        }

        /* Card Styles */
        .sl-card {
            display: block !important;
            cursor: pointer;
        }
        .sl-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }
        .sl-card-name {
            font-size: 15px;
            font-weight: 700;
            color: #1b2850;
            text-transform: uppercase;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }
        .sl-card-date {
            font-size: 12px;
            color: #8b93a7;
            white-space: nowrap;
            padding-top: 2px;
        }
        .sl-card-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 6px 0 8px;
            flex-wrap: wrap;
        }
        .sl-card-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.2px;
            padding: 3px 10px;
            border-radius: 12px;
            background: #e7f8ef;
            color: #12a150;
            text-transform: uppercase;
        }
        .sl-card-badge.is-unpaid {
            background: #fff3df;
            color: #e8960c;
        }
        .sl-card-badge.is-partial {
            background: #e6f0ff;
            color: #2f6fd8;
        }
        .sl-card-badge.is-other {
            background: #eef0f4;
            color: #475467;
        }
        .sl-card-no {
            font-size: 11px;
            color: #8b93a7;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sl-card-type-badge {
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #475467;
            font-weight: 600;
        }
        .sl-card-amounts {
            display: grid;
            grid-template-columns: auto auto;
            justify-content: start;
            column-gap: 24px;
            max-width: calc(100% - 136px);
        }
        .sl-card-amounts small {
            display: block;
            font-size: 12px;
            color: #8b93a7;
        }
        .sl-card-amounts b {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #1b2850;
            white-space: nowrap;
        }

        /* Quick Action Buttons on Card */
        .sl-m-only {
            display: inline-flex !important;
        }
        #order-table .sl-m-print,
        #order-table .sl-m-share,
        #order-table .action-ellipsis-btn {
            width: 32px;
            height: 32px;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            border: 0 !important;
            background: transparent !important;
            color: #8b93a7 !important;
            font-size: 16px;
            box-shadow: none !important;
            cursor: pointer;
        }
        #order-table .sl-m-print:hover,
        #order-table .sl-m-share:hover,
        #order-table .action-ellipsis-btn:hover {
            color: #1b2850 !important;
        }
        #order-table .sl-m-pdf {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            margin: 0 2px;
        }
        #order-table .sl-m-pdf span {
            background: #e5483d;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            border-radius: 4px;
            padding: 4px 6px;
            line-height: 1;
        }
        #order-table .action-ellipsis-btn i {
            transform: rotate(90deg);
        }
    }

    /* Tablet View (768px to 1280.98px) - 2 Column Grid */
    @media screen and (min-width: 768px) and (max-width: 1280.98px) {
        #order-table tbody {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            align-items: start;
            gap: 12px !important;
        }
        #order-table tbody > tr:not(.order-details-row) {
            min-width: 0;
            margin: 0 !important;
        }
        #order-table tbody > tr:has(> td.dataTables_empty) {
            grid-column: 1 / -1;
        }
        .sl-card {
            padding: 2px;
        }
        .sl-fab-new-sale {
            bottom: 86px;
            font-size: 16px;
        }
        .content:has(.sl-fab-new-sale) {
            padding-bottom: 100px !important;
        }
    }

    .form-control {
        color: #595b5d !important;
        /* Bootstrap's default placeholder/input text color */
    }

    .form-group {
        margin-bottom: 8px !important;
    }

    /* ===== Pill Badge Styles ===== */
    .status-pill {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 13px;
        font-weight: 600;
        color: #fff !important;
        text-transform: capitalize;
        border: none;
        white-space: nowrap;
        cursor: default;
    }
    /* Payment Status colors */
    .pill-pending    { background-color: #f6850d; }
    .pill-partial    { background-color: #e0a800; }
    .pill-completed  { background-color: #28a745; }
    .pill-extra-paid { background-color: #dc3545; }
    .pill-default-status { background-color: #6c757d; }

    /* Payment Method colors */
    .pill-scan          { background-color: #007bff; }
    .pill-cash          { background-color: #17a2b8; }
    .pill-online        { background-color: #6f42c1; }
    .pill-cash-online   { background-color: #9c27b0; }
    .pill-method-pending { background-color: #f6850d; }
    .pill-method-default { background-color: #6c757d; }

    /* Biller pill */
    .pill-biller { background-color: #20c997; }

    /* ===== Advanced Filters Card ===== */
    .card-header {
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    .card-header:hover {
        background-color: #e9ecef;
    }
    #filter-toggle-icon {
        transition: transform 0.3s ease;
    }
    #advancedFilters.collapse:not(.show) ~ .card-header #filter-toggle-icon {
        transform: rotate(0deg);
    }
    #advancedFilters.show ~ .card-header #filter-toggle-icon {
        transform: rotate(180deg);
    }

    /* ===== Compact Filter Bar Styles ===== */
    /* .filter-bar-container {
        padding: 4px 0 !important;
        margin-bottom: 4px !important;
    } */

    /* Support for equal-width filter grid */
    /* .filter-bar-container .row.filter-row {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 6px !important;
        margin-bottom: 6px;
    }

    .filter-bar-container .row.filter-row-2 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px !important;
        margin-bottom: 6px;
    }

    .filter-bar-container .row.totals-footer {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    .filter-bar-container .form-control,
    .filter-bar-container .form-select {
        height: 32px !important;
        font-size: 12px !important;
        border-color: #d1d5db !important;
        border-radius: 3px !important;
        padding: 5px 8px !important;
        background-color: #fff !important;
    }
    
    .filter-bar-container .form-control::placeholder {
        color: #9ca3af;
        font-size: 12px;
    }

    .filter-bar-container .input-group {
        gap: 0;
        position: relative;
    } */

    /* Search field with icon inside */
    /* .filter-bar-container .input-group .form-control {
        padding-right: 28px !important;
        padding-left: 8px !important;
    }

    .filter-bar-container .input-group .btn {
        position: absolute;
        right: 1px;
        top: 50%;
        transform: translateY(-50%);
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
        color: #6b7280;
        font-size: 12px;
        cursor: pointer;
    }

    .filter-bar-container .input-group .btn:hover {
        color: #374151;
    } */

    /* ===== Compact Totals Badges ===== */
    .totals-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding: 6px 0;
        margin: 4px 0 0 0 !important;
        border-top: none;
    }

    .total-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 3px 9px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        border: none;
        background-color: #f3f4f6;
        color: #374151;
    }

    .total-badge.pending {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .total-badge.paid {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .total-badge.amount {
        background-color: #dbeafe;
        color: #2563eb;
    }

    .total-badge.orders {
        background-color: #f3f4f6;
        color: #6b7280;
    }

    /* Hide badge labels for smaller badges */
    .total-badge span {
        font-weight: 700;
    }

    /* ===== Export Buttons ===== */
    .export-buttons-group {
        display: flex;
        gap: 4px;
        margin-left: 8px;
        align-items: center;
    }

    .export-btn {
        padding: 4px 8px !important;
        font-size: 11px !important;
        height: 28px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        border-radius: 3px;
        border: none;
        font-weight: 600;
        color: white !important;
        cursor: pointer;
        transition: opacity 0.2s;
        white-space: nowrap;
    }

    .export-btn:hover {
        opacity: 0.9;
    }

    .export-btn-excel {
        background-color: #16a34a;
    }

    .export-btn-pdf {
        background-color: #dc2626;
    }

    .export-btn i {
        font-size: 11px;
    }

    /* Responsive adjustments for filter bar */
    /* @media (max-width: 1200px) {
        .filter-bar-container .row.filter-row {
            grid-template-columns: repeat(3, 1fr);
        }

        .filter-bar-container .row.filter-row-2 {
            grid-template-columns: 1fr;
        }
    } */

    @media (max-width: 768px) {
        /* .filter-bar-container {
            padding: 4px 0 !important;
        }

        .filter-bar-container .form-control,
        .filter-bar-container .form-select {
            height: 30px !important;
            font-size: 11px !important;
            padding: 4px 6px !important;
        }

        .filter-bar-container .row.filter-row,
        .filter-bar-container .row.filter-row-2 {
            grid-template-columns: 1fr;
        } */

        .totals-row {
            gap: 6px;
            padding: 4px 0;
        }

        .total-badge {
            padding: 3px 8px;
            font-size: 10px;
            flex: 1 0 calc(50% - 3px);
            justify-content: center;
        }

        .export-buttons-group {
            flex: 1 0 100%;
            margin-left: 0;
            margin-top: 4px;
            gap: 3px;
        }

        .export-btn {
            flex: 1;
            justify-content: center;
            padding: 3px 6px !important;
            height: 26px;
            font-size: 10px !important;
        }

        .export-btn i {
            font-size: 10px;
        }
    }

    /* Totals Cards */
    .totals-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .totals-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* ===== Action Dropdown ===== */
    #order-table .order-print-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        margin-right: 4px;
        padding: 0;
        color: #495057;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        text-decoration: none;
    }
    #order-table .order-print-btn:hover {
        color: #212529;
        background: #f1f3f5;
    }
    .action-dropdown-wrap {
        position: relative;
        display: inline-block;
    }
    .ellipsis-btn {
        background: #f1f3f5;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        width: 34px;
        height: 34px;
        font-size: 18px;
        font-weight: 700;
        color: #495057;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.18s;
        line-height: 1;
    }
    .ellipsis-btn:hover {
        background: #e2e6ea;
        color: #212529;
    }
    .action-dropdown-menu {
        display: none;
        position: fixed;
        z-index: 99999;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        min-width: 190px;
        padding: 6px 0;
        border: 1px solid #f0f0f0;
    }
    .action-dropdown-menu.show { display: block; }
    .action-dropdown-menu a,
    .action-dropdown-menu button.dd-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 18px;
        font-size: 13.5px;
        color: #333;
        text-decoration: none;
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        transition: background 0.13s;
        font-weight: 500;
    }
    .action-dropdown-menu a:hover,
    .action-dropdown-menu button.dd-item:hover {
        background: #f8f9fa;
        color: #111;
    }
    .action-dropdown-menu .dd-item-delete {
        color: #dc3545 !important;
    }
    .action-dropdown-menu .dd-item-delete:hover {
        background: #fff5f5;
    }
    .dd-icon {
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .page-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
}

.page-btn {
    display: flex;
    flex-wrap: nowrap;
    gap: 8px;
    align-items: center;
}

.page-btn .btn-added {
    white-space: nowrap;
}

@media (max-width: 576px) {
    .page-btn {
        flex-wrap: wrap;
    }
}
.purchase-filter-card {
    background: #fff;
    padding: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.purchase-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}

.purchase-filter-row > div {
    flex: 1 1 180px;
    min-width: 150px;
}

.purchase-filter-row .form-control,
.purchase-filter-row .form-select {
    height: 40px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    box-shadow: none;
    font-size: 14px;
    color: #111827 !important;
    background: #fff;
}

.purchase-filter-row .form-control::placeholder {
    color: #9ca3af;
}

.summary-badges-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    justify-content: flex-start;
    margin-top: 14px;
}

.summary-badge-box {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 10px;
    border: 1px solid;
    font-size: 14px;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
}

.summary-badge-box.pending {
    background: #fee2e2;
    border-color: #fecaca;
    color: #dc2626;
}

.summary-badge-box.paid {
    background: #dcfce7;
    border-color: #bbf7d0;
    color: #16a34a;
}

.summary-badge-box.total {
    background: #f1f5f9;
    border-color: #e2e8f0;
    color: #1e293b;
}

.summary-export-group {
    display: inline-flex;
    gap: 10px;
    margin-left: auto;
}

.summary-export-group .btn {
    height: 40px;
    border-radius: 8px;
    min-width: 86px;
    font-weight: 600;
}
.dataTables_filter {
    display: none !important;
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
            <h4>All Orders</h4>
            <!-- <h6>Manage your sales</h6> -->
        </div>
        <div class="page-btn">
            @if (app('hasPermission')(2, 'add'))
            <a href="{{ route('sales.add') }}" class="btn btn-sm btn-added"><img
                    src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img" class="me-1">Take Away</a>
                    <a href="{{ route('tables.create') }}" class="btn btn-sm btn-added"><img
                    src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img" class="me-1">Dine In</a>
            @endif
        </div>
        
    </div>

    <!-- Mobile & Tablet Quick Header and Summary Slot -->
    <div class="sl-quick-links d-none">
        <div class="sl-quick-head">
            <div class="sl-quick-title">All Orders</div>
            @if (app('hasPermission')(2, 'add'))
                <div class="sl-quick-actions">
                    <a href="{{ route('sales.add') }}" class="btn btn-sm btn-added">
                        <i class="fas fa-plus me-1"></i> Take Away
                    </a>
                    <a href="{{ route('tables.create') }}" class="btn btn-sm btn-added">
                        <i class="fas fa-plus me-1"></i> Dine In
                    </a>
                </div>
            @endif
        </div>
        <div id="slSummarySlot"></div>
    </div>

    <!-- Floating Add New Sale Button (Mobile & Tablet) -->
    @if (app('hasPermission')(2, 'add'))
        <a href="{{ route('sales.add') }}" class="sl-fab-new-sale d-none">
            <i class="fas fa-rupee-sign"></i> Add New Sale
        </a>
    @endif

    <!-- Mobile Filter Backdrop and Bottom Sheet -->
    <div class="sl-filter-backdrop d-none" id="slFilterBackdrop"></div>
    <div class="sl-filter-sheet d-none" id="slFilterSheet" role="dialog" aria-modal="true" aria-labelledby="slFilterSheetTitle">
        <div class="sl-filter-sheet-head">
            <span id="slFilterSheetTitle">Filter By</span>
            <button type="button" class="sl-filter-sheet-close" id="slFilterSheetClose" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="sl-filter-sheet-body" id="slFilterSheetBody">
            <div class="filter-field">
                <label class="sl-sheet-label" for="filter-month-mobile">Month</label>
                <select id="filter-month-mobile" class="form-select">
                    <option value="">All Months</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="filter-field">
                <label class="sl-sheet-label" for="filter-year-mobile">Year</label>
                <select id="filter-year-mobile" class="form-select">
                    <option value="">All Years</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label class="sl-sheet-label" for="filter-financial-year-mobile">Financial Year</label>
                <select id="filter-financial-year-mobile" class="form-select">
                    <option value="">All Financial Years</option>
                    <option value="2024-25">2024-25</option>
                    <option value="2025-26">2025-26</option>
                    <option value="2026-27">2026-27</option>
                </select>
            </div>
            @if(isset($staffList) && count($staffList))
            <div class="filter-field">
                <label class="sl-sheet-label" for="filter-staff-mobile">Staff</label>
                <select id="filter-staff-mobile" class="form-select">
                    <option value="">All Staff</option>
                    @foreach ($staffList as $id => $name)
                        <option value="{{ is_object($name) ? $name->id : $id }}">{{ is_object($name) ? $name->name : $name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="filter-field">
                <label class="sl-sheet-label" for="filter-date-mobile">Date</label>
                <input type="text" id="filter-date-mobile" placeholder="Choose Date" class="datetimepicker form-control">
            </div>
            <div class="filter-field">
                <label class="sl-sheet-label" for="filter-payment-status-mobile">Status</label>
                <select id="filter-payment-status-mobile" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="partial">Partial</option>
                    <option value="completed">Completed</option>
                    <option value="extra_paid">Extra Paid</option>
                </select>
            </div>
            <div class="filter-field">
                <label class="sl-sheet-label" for="filter-sort-mobile">Sort By</label>
                <select id="filter-sort-mobile" class="form-select">
                    <option value="">Latest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>
        </div>
        <div class="sl-filter-sheet-foot">
            <button type="button" class="sl-filter-clear" id="slFilterClear">Clear</button>
            <button type="button" class="sl-filter-apply" id="slFilterApply">Apply</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <!-- Filter Bar and Totals Section -->
            <div class="filter-bar-container">
                <!-- Filter Row 1: 6 equal-width columns -->
                <!-- <div class="row filter-row"> -->
                    <!-- Search Box with Search Icon -->
                    <!-- <div>
                        <div class="input-group">
                            <input type="text" id="search-box" placeholder="Search..." 
                                class="form-control">
                            <button class="btn" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div> -->

                    <!-- Month Filter -->
                    <!-- <div>
                        <select id="filter-month" class="form-select">
                            <option value="">All Months</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                {{ DateTime::createFromFormat('!m', $m)->format('M') }}
                                </option>
                            @endfor
                        </select>
                    </div> -->

                    <!-- Year Filter -->
                    <!-- <div>
                        <select id="filter-year" class="form-select">
                            <option value="">All Years</option>
                            @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div> -->

                    <!-- Financial Year Filter -->
                    <!-- <div>
                        <select id="filter-financial-year" class="form-select">
                            <option value="">All Financial Years</option>
                            <option value="2024-25">2024-25</option>
                            <option value="2025-26">2025-26</option>
                            <option value="2026-27">2026-27</option>
                        </select>
                    </div> -->

                    <!-- Staff Filter -->
                    <!-- <div>
                        <select id="filter-staff" class="form-select">
                            <option value="">All Staff</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div> -->

                    <!-- Date Picker -->
                    <!-- <div>
                        <input type="text" id="filter-date" placeholder="Choose Date"
                            class="datetimepicker form-control">
                    </div>
                </div> -->

                <!-- Filter Row 2: 3 equal-width columns -->
                <!-- <div class="row filter-row-2"> -->
                    <!-- Order Type Filter -->
                    <!-- <div>
                        <select id="filter-order-type" class="form-select">
                            <option value="">All Order Types</option>
                            <option value="sales">Sales</option>
                            <option value="rental">Rental</option>
                        </select>
                    </div> -->

                    <!-- Payment Status Filter -->
                    <!-- <div>
                        <select id="filter-payment-status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="completed">Completed</option>
                            <option value="extra_paid">Extra Paid</option>
                        </select>
                    </div> -->

                    <!-- Sort Filter -->
                    <!-- <div>
                        <select id="filter-sort" class="form-select">
                            <option value="">Latest First</option>
                            <option value="oldest">Oldest First</option>
                        </select>
                    </div>
                </div> -->

                <!-- Totals Row with Export Buttons -->
                <!-- <div class="totals-row">
                    <div class="total-badge pending">
                        <span>Pending:</span> ₹<span id="total-pending">0.00</span>
                    </div>
                    <div class="total-badge paid">
                        <span>Paid:</span> ₹<span id="total-paid">0.00</span>
                    </div>
                    <div class="total-badge amount">
                        <span>Total:</span> ₹<span id="total-amount">0.00</span>
                    </div>
                    <div class="total-badge orders">
                        <span>Orders:</span> <span id="total-orders">0</span>
                    </div> -->

                    <!-- Export Buttons -->
                    <!-- <div class="export-buttons-group">
                        <button id="exportAllChallan" class="export-btn export-btn-excel" title="Export Excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button id="exportPdf" class="export-btn export-btn-pdf" title="Export PDF">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div> -->
                <div class="purchase-filter-card mb-3">
    <div class="purchase-filter-row">
        <div>
            <input type="text" id="search-box" placeholder="Search..." class="form-control">
        </div>
        <div>
            <select id="filter-month" class="form-select">
                <option value="">All Months</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                        {{ DateTime::createFromFormat('!m', $m)->format('M') }}
                    </option>
                @endfor
            </select>
        </div>
        <div>
            <select id="filter-year" class="form-select">
                <option value="">All Years</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select id="filter-financial-year" class="form-select">
                <option value="">All Financial Years</option>
                <option value="2024-25">2024-25</option>
                <option value="2025-26">2025-26</option>
                <option value="2026-27">2026-27</option>
            </select>
        </div>
        <!-- <div>
            <select id="filter-staff" class="form-select">
                <option value="">All Staff</option>
                @foreach ($staffList as $id => $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
            </select>
        </div> -->
        <div>
            <input type="text" id="filter-date" placeholder="Choose Date" class="datetimepicker form-control">
        </div>
        <div>
            <select id="filter-payment-status" class="form-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="partial">Partial</option>
                <option value="completed">Completed</option>
                <option value="extra_paid">Extra Paid</option>
            </select>
        </div>
        <div>
            <select id="filter-sort" class="form-select">
                <option value="">Latest First</option>
                <option value="oldest">Oldest First</option>
            </select>
        </div>
    </div>

    <div class="summary-badges-row">
        <div class="summary-badge-box pending">
            <span>Total Pending:</span>
            <span>₹<span id="total-pending">0.00</span></span>
        </div>
        <div class="summary-badge-box paid">
            <span>Total Paid:</span>
            <span>₹<span id="total-paid">0.00</span></span>
        </div>
        <div class="summary-badge-box total">
            <span>Total:</span>
            <span>₹<span id="total-amount">0.00</span></span>
        </div>
        <div class="summary-export-group">
            <button id="exportAllChallan" class="btn btn-sm btn-success" title="Excel">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button id="exportPdf" class="btn btn-sm btn-danger" title="PDF">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>
    </div>
</div>
            </div>

            <!-- Mobile & Tablet Search Bar with Filter Button -->
            <div class="sales-filter-toolbar d-none">
                <div class="filter-search">
                    <div class="search-input">
                        <a class="btn btn-searchset"><i class="fas fa-search"></i></a>
                        <input type="text" id="sales-search-mobile" class="form-control" placeholder="SEARCH...">
                    </div>
                    <button type="button" id="salesFilterToggle" class="sales-filter-toggle" aria-expanded="false" aria-label="Filters" title="Filters">
                        <i class="fas fa-filter"></i>
                        <span class="sales-filter-count d-none" id="salesFilterCount">0</span>
                    </button>
                </div>
            </div>

            <!-- Advanced Filters Card (Collapsible) -->
            <div class="card mb-3" id="filter_inputs">
                <div class="card-header" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                    <i class="fas fa-filter"></i> Advanced Filters <span id="filter-toggle-icon" class="float-end"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="collapse" id="advancedFilters">
                    <div class="card-body pb-2">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group mb-2">
                                    <label class="form-label mb-1" for="filter-customer-name">Customer Name</label>
                                    <input type="text" id="filter-customer-name" placeholder="Enter customer name" 
                                        class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group mb-2">
                                    <label class="form-label mb-1" for="filter-order-number">Order Number</label>
                                    <input type="text" id="filter-order-number" placeholder="Enter order number" 
                                        class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group mb-2">
                                    <label class="form-label mb-1" for="filter-biller">Biller</label>
                                    <select id="filter-biller" class="form-select form-select-sm">
                                        <option value="">All Billers</option>
                                        @foreach ($staffList as $id => $name)
                                            <option value="{{ $name }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group mb-2">
                                    <label class="form-label mb-1" for="filter-payment-method">Payment Method</label>
                                    <select id="filter-payment-method" class="form-select form-select-sm">
                                        <option value="">All Methods</option>
                                        <option value="cash">Cash</option>
                                        <option value="online">Online</option>
                                        <option value="cash_online">Cash + Online</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 d-flex gap-2 justify-content-end">
                                <button class="btn btn-sm btn-primary" id="apply-filters">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <button class="btn btn-sm btn-secondary" id="reset-filters">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-scroll-top"
                style="overflow-x: auto; overflow-y: hidden; height: 20px; margin-bottom: 5px;">
                <div style="height: 1px;"></div> <!-- Adjust width to match your table width -->
            </div>
            <!-- Orders Table -->
            <div class="table-responsive mt-3" style="overflow-x: auto;">
                <table class="table datanew" id="order-table" style="max-width: 2000px;">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th class="details-column">Details</th>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Payment Status</th>
                            <th>Payment Method</th>
                            <th>Total</th>
                            <th>Assigned Staff</th>
                            <th>Order Type</th>
                            <th>Biller</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- JS will populate this -->
                    </tbody>
                </table>
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

                    <!-- ✅ Payment History Container -->
                    <div id="paymentHistoryBox" class="border p-2 rounded bg-white d-none mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Payment History</h6>

                            <!-- 👇 Visible Close Button -->
                            <button type="button" class="btn btn-sm btn-danger" id="closeHistoryBtn">
                                &times; <!-- This renders an “×” symbol -->
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

                    <!-- ✅ View Payment History Button -->



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
                    <input type="hidden" id="paymentJobCardId" name="order_id">
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
    const userRole = "{{ auth()->user()->role }}";
    const staffList = {!! json_encode($staffList ?? []) !!};
</script>

<script>

    function getOrderTypeBadge(order) {
        if (order.type === 'dinein') {
            return `<span class="status-pill" style="background-color:#20c997; margin-top:4px; display:inline-block;">
                <i class="fas fa-utensils"></i> Dine In - Table ${order.table_no || 'N/A'}
            </span>`;
        } else {
            return `<span class="status-pill" style="background-color:#6c757d; margin-top:4px; display:inline-block;">
                <i class="fas fa-shopping-bag"></i> Take Away
            </span>`;
        }
    }
    // ===== HELPER FUNCTION TO CALCULATE TOTALS =====
    function calculateTotals() {
        let totalPending = 0;
        let totalPaid = 0;
        let totalAmount = 0;
        let totalOrders = 0;

        const table = $('#order-table').DataTable();
        table.rows({filter: 'applied'}).every(function() {
            const row = this.data();
            totalOrders++;
            
            // Total is in column 6
            const amountText = row[6] || '0';
            const rawAmount = parseFloat(amountText.replace(/[^0-9.-]+/g, '')) || 0;
            totalAmount += rawAmount;

            // Get payment status from column 4 (Payment Status pill)
            const statusHtml = row[4] || '';
            const statusMatch = statusHtml.match(/>(.*?)<\/span>/);
            const status = statusMatch ? statusMatch[1].toLowerCase().trim() : '';

            // Calculate pending vs paid
            if (status === 'pending') {
                totalPending += rawAmount;
            } else if (status === 'completed' || status === 'partial' || status === 'extra paid' || status === 'extra_paid') {
                totalPaid += rawAmount;
            }
        });

        // Update totals display
        $('#total-pending').text(totalPending.toFixed(2));
        $('#total-paid').text(totalPaid.toFixed(2));
        $('#total-amount').text(totalAmount.toFixed(2));
        $('#total-orders').text(totalOrders);
    }

    // ===== POOJA ERP CARD & FILTER HELPERS =====
    function formatMoney(amount) {
        let n = parseFloat(amount || 0);
        if (!isFinite(n)) n = 0;
        return '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getCardDate(dateStr) {
        if (!dateStr) return '';
        try {
            let d = new Date(dateStr);
            if (isNaN(d.getTime())) {
                return dateStr;
            }
            let months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            let day = ('0' + d.getDate()).slice(-2);
            let month = months[d.getMonth()];
            let year = String(d.getFullYear()).slice(-2);
            let hours = d.getHours() % 12 || 12;
            let minutes = ('0' + d.getMinutes()).slice(-2);
            let ampm = d.getHours() >= 12 ? 'PM' : 'AM';
            return `${day} ${month}, ${year} ${hours}:${minutes} ${ampm}`;
        } catch(e) {
            return dateStr;
        }
    }

    function getCardBadge(order) {
        let pay = String(order.payment_status || '').toLowerCase().trim();
        if (pay === 'pending' || pay === 'unpaid') {
            return { text: 'SALE : PENDING', cls: 'is-unpaid' };
        } else if (pay === 'partial' || pay === 'partially') {
            return { text: 'SALE : PARTIAL', cls: 'is-partial' };
        } else if (pay === 'completed' || pay === 'paid') {
            return { text: 'SALE : PAID', cls: '' };
        }
        return { text: 'SALE : ' + (order.payment_status || 'OTHER').toUpperCase(), cls: 'is-other' };
    }

    function buildCard(order, customerNameHtml) {
        let b = getCardBadge(order);
        let total = formatMoney(order.total_amount);
        let balance = formatMoney(Math.max(0, parseFloat(order.remaining_amount || 0)));
        let custName = (order.user && order.user.name) ? order.user.name : (order.customer_name || 'DEFAULT CUSTOMER');
        let dateFormatted = getCardDate(order.created_date || order.created_at);
        let typeBadge = '';
        if (order.type === 'dinein' || order.order_type === 'dinein' || order.order_type === 'Dine In') {
            typeBadge = `<span class="sl-card-type-badge"><i class="fas fa-utensils me-1"></i> Dine In ${order.table_no ? '#' + order.table_no : ''}</span>`;
        } else if (order.type === 'delivery' || order.order_type === 'delivery' || order.order_type === 'Delivery') {
            typeBadge = `<span class="sl-card-type-badge"><i class="fas fa-truck me-1"></i> Delivery</span>`;
        } else {
            typeBadge = `<span class="sl-card-type-badge"><i class="fas fa-shopping-bag me-1"></i> Take Away</span>`;
        }

        return `
        <div class="sl-card" data-order-id="${order.id}">
            <div class="sl-card-top">
                <span class="sl-card-name">${custName}</span>
                <span class="sl-card-date">${dateFormatted}</span>
            </div>
            <div class="sl-card-meta">
                <span class="sl-card-badge ${b.cls}">${b.text}</span>
                <span class="sl-card-no">${order.order_number || ''}</span>
                ${typeBadge}
            </div>
            <div class="sl-card-amounts">
                <div><small>Total</small><b>${total}</b></div>
                <div><small>Balance</small><b>${balance}</b></div>
            </div>
        </div>
        `;
    }

    function shareInvoice(id) {
        var url = '/sales/invoice/pdf/' + encodeURIComponent(id);
        var name = 'Invoice-' + id + '.pdf';
        fetch(url, { credentials: 'same-origin' }).then(function(res) {
            if (!res.ok) throw new Error('download failed');
            return res.blob();
        }).then(function(blob) {
            var file = new File([blob], name, { type: 'application/pdf' });
            if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
                return navigator.share({ files: [file], title: name }).catch(function() {});
            }
            window.open(url, '_blank');
        }).catch(function() {
            window.open(url, '_blank');
        });
    }

    function placeSummary() {
        var slot = document.getElementById('slSummarySlot');
        var row = document.querySelector('.summary-badges-row');
        if (!slot || !row) return;
        if (!placeSummary.home) {
            placeSummary.home = document.createComment('summary-badges-row-placeholder');
            row.parentNode.insertBefore(placeSummary.home, row);
        }
        var isMobileOrTablet = window.matchMedia('(max-width: 1280.98px)').matches;
        if (isMobileOrTablet && row.parentNode !== slot) {
            slot.appendChild(row);
        } else if (!isMobileOrTablet && row.parentNode === slot) {
            placeSummary.home.parentNode.insertBefore(row, placeSummary.home);
        }
    }

    function openFilterSheet() {
        $('#filter-month-mobile').val($('#filter-month').val() || '');
        $('#filter-year-mobile').val($('#filter-year').val() || '');
        $('#filter-financial-year-mobile').val($('#filter-financial-year').val() || '');
        if ($('#filter-staff').length) {
            $('#filter-staff-mobile').val($('#filter-staff').val() || '');
        }
        $('#filter-date-mobile').val($('#filter-date').val() || '');
        $('#filter-payment-status-mobile').val($('#filter-payment-status').val() || '');
        $('#filter-sort-mobile').val($('#filter-sort').val() || '');

        $('body').addClass('sl-filter-open');
        $('#salesFilterToggle').attr('aria-expanded', 'true');
    }

    function closeFilterSheet() {
        $('body').removeClass('sl-filter-open');
        $('#salesFilterToggle').attr('aria-expanded', 'false');
    }

    function updateFilterCount() {
        var n = 0;
        if ($('#filter-month').val()) n++;
        if ($('#filter-year').val()) n++;
        if ($('#filter-financial-year').val()) n++;
        if ($('#filter-staff').length && $('#filter-staff').val()) n++;
        if ($('#filter-date').val() && $('#filter-date').val().trim()) n++;
        if ($('#filter-payment-status').val()) n++;
        if ($('#filter-sort').val()) n++;

        if (n > 0) {
            $('#salesFilterCount').text(n).removeClass('d-none');
            $('#salesFilterToggle').addClass('has-active');
        } else {
            $('#salesFilterCount').text('0').addClass('d-none');
            $('#salesFilterToggle').removeClass('has-active');
        }
    }

    $(document).ready(function() {

        var authToken = localStorage.getItem("authToken");
        const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
        
        // Check if table is already initialized, if so, get reference; otherwise initialize
        var table;
        if ($.fn.DataTable.isDataTable('.datanew')) {
            table = $('.datanew').DataTable();
        } else {
            table = $('.datanew').DataTable({
                columnDefs: [
                    {
                        targets: 2, // Date column
                        render: function(data, type, row) {
                            if (type === 'sort') {
                                // Extract the timestamp from data-sort attribute
                                const match = data.match(/data-sort="(\d+)"/);
                                return match ? parseInt(match[1]) : 0;
                            }
                            return data;
                        }
                    }
                ]
            });
        }

        // ===== MOBILE & TABLET INTERACTIONS =====
        placeSummary();
        updateFilterCount();
        $(window).on('resize', placeSummary);

        // Sync mobile search with desktop search
        $('#sales-search-mobile').on('input', function() {
            var val = $(this).val();
            $('#search-box').val(val);
            table.search(val).draw();
            calculateTotals();
        });
        $('#search-box').on('input', function() {
            var val = $(this).val();
            $('#sales-search-mobile').val(val);
            table.search(val).draw();
            calculateTotals();
        });

        // Filter toggle & bottom sheet
        $('#salesFilterToggle').on('click', function(e) {
            e.preventDefault();
            openFilterSheet();
        });
        $('#slFilterSheetClose, #slFilterBackdrop').on('click', function(e) {
            e.preventDefault();
            closeFilterSheet();
        });

        $('#slFilterApply').on('click', function(e) {
            e.preventDefault();
            $('#filter-month').val($('#filter-month-mobile').val()).trigger('change');
            $('#filter-year').val($('#filter-year-mobile').val()).trigger('change');
            $('#filter-financial-year').val($('#filter-financial-year-mobile').val()).trigger('change');
            if ($('#filter-staff').length) {
                $('#filter-staff').val($('#filter-staff-mobile').val()).trigger('change');
            }
            $('#filter-payment-status').val($('#filter-payment-status-mobile').val()).trigger('change');
            $('#filter-sort').val($('#filter-sort-mobile').val()).trigger('change');
            
            var mobileDate = $('#filter-date-mobile').val();
            if (mobileDate !== $('#filter-date').val()) {
                $('#filter-date').val(mobileDate).trigger('dp.change');
            }

            updateFilterCount();
            closeFilterSheet();
        });

        $('#slFilterClear').on('click', function(e) {
            e.preventDefault();
            $('#filter-month-mobile, #filter-year-mobile, #filter-financial-year-mobile, #filter-staff-mobile, #filter-payment-status-mobile, #filter-sort-mobile, #filter-date-mobile').val('');
            $('#filter-month, #filter-year, #filter-financial-year, #filter-staff, #filter-payment-status, #filter-sort').val('').trigger('change');
            $('#filter-date').val('').trigger('dp.change');

            table.search('').columns().search('').order([2, 'desc']).draw();
            calculateTotals();

            updateFilterCount();
            closeFilterSheet();
        });

        // Click card to open sales details
        $(document).on('click', '#order-table .sl-card', function(e) {
            if ($(e.target).closest('a, button, input, select').length) return;
            var orderId = $(this).data('order-id');
            if (orderId) {
                window.location.href = '/sales-details/' + encodeURIComponent(orderId);
            }
        });

        // Share invoice
        $(document).on('click', '#order-table .sl-m-share', function(e) {
            e.preventDefault();
            e.stopPropagation();
            shareInvoice($(this).data('order-id'));
        });

        // ===== FILTER COLLAPSE ICON TOGGLE =====
        $('#advancedFilters').on('show.bs.collapse', function() {
            $('#filter-toggle-icon i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        });
        $('#advancedFilters').on('hide.bs.collapse', function() {
            $('#filter-toggle-icon i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        });

        // ===== APPLY FILTERS BUTTON =====
        $('#apply-filters').on('click', function() {
            const customerName = $('#filter-customer-name').val() || '';
            const orderNumber = $('#filter-order-number').val() || '';
            const biller = $('#filter-biller').val() || '';
            const paymentMethod = $('#filter-payment-method').val() || '';

            // Apply custom search
            table.columns(0).search(orderNumber, true, false);
            table.columns(3).search(customerName, true, false);
            table.columns(7).search(biller, true, false);
            table.draw();

            // Recalculate totals
            calculateTotals();
        });

        // ===== RESET FILTERS BUTTON =====
        $('#reset-filters').on('click', function() {
            $('#filter-customer-name, #filter-order-number, #filter-biller, #filter-payment-method').val('');
            $('#filter-month, #filter-year, #filter-financial-year, #filter-date, #filter-payment-status, #filter-order-type').val('');
            $('#search-box').val('');
            table.search('').columns().search('').draw();
            calculateTotals();
        });

        // ===== SEARCH BOX GLOBAL SEARCH =====
        $('#search-box').on('keyup', function() {
            table.search($(this).val()).draw();
            calculateTotals();
        });



        $(document).on('click', '.make-payment-btn', function() {
            let jobCardId = $(this).data('id');
            let totalAmount = $(this).data('total-amount');
            let remainingAmount = $(this).data('remaining-amount');
            let method = $(this).data('method') || '';

            // ✅ Fill modal hidden inputs + text spans
            $('#paymentJobCardId').val(jobCardId);
            $('#emiTotal').text(parseFloat(totalAmount).toFixed(2));
            $('#remainingAmountDisplay').text(parseFloat(remainingAmount).toFixed(2));
            $('#remainingAmountHidden').val(remainingAmount);
            $('#paymentMethodHidden').val(method);

            // ✅ Reset payment method dropdown to default
            $('#paymentMethodSelect').val('');

            // ✅ Hide history box initially
            $('#paymentHistoryBox').addClass('d-none');
            $('#paymentHistoryList').html('');

            // ✅ Bind View History button
            $('#viewHistoryBtn').off('click').on('click', function() {
                $.ajax({
                    url: '/api/sales/payment-history/' + jobCardId,
                    method: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken,
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
                                                                <strong>Amount:</strong> ₹${parseFloat(payment.payment_amount).toFixed(2)}<br>
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

            // ✅ Close history
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

        // ✅ Handle Paid Type (when Payment Method = Cash)
        // $('#paidTypeSelect').on('change', function() {
        //     let type = $(this).val();
        //     let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

        //     $('#partialPaidFields, #fullyPaidFields').addClass('d-none');

        //     if (type === 'cash_partially') {
        //         $('#partialPaidFields').removeClass('d-none');
        //         $('#partialAmount').val(''); // clear input
        //         $('#pendingAmount').val(remaining); // show full pending initially

        //         // When user types partial amount → auto update pending
        //         $('#partialAmount').off('input').on('input', function() {
        //             let entered = parseFloat($(this).val()) || 0;
        //             let newPending = Math.max(remaining - entered, 0);
        //             $('#pendingAmount').val(newPending.toFixed(2));
        //         });
        //     } else if (type === 'cash_fully') {
        //         $('#fullyPaidFields').removeClass('d-none');
        //         $('#cashAmount').val(remaining.toFixed(2)).prop('readonly', true);
        //     }
        // });
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


        // ✅ Handle Online Type (when Payment Method = Online)
        // $('#onlineTypeSelect').on('change', function() {
        //     let type = $(this).val();
        //     let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

        //     $('#upiAmountDiv,#partialPaidFields').addClass('d-none');

        //     if (type === 'online_partially') {
        //         $('#partialPaidFields').removeClass('d-none');
        //         $('#partialAmount').val(''); // clear input
        //         $('#pendingAmount').val(remaining); // show full pending initially

        //         // When user types partial amount → auto update pending
        //         $('#partialAmount').off('input').on('input', function() {
        //             let entered = parseFloat($(this).val()) || 0;
        //             let newPending = Math.max(remaining - entered, 0);
        //             $('#pendingAmount').val(newPending.toFixed(2));
        //         });

        //     } else if (type === 'online_fully') {
        //         $('#upiAmountDiv').removeClass('d-none');
        //         $('#upiAmountInput').val(remaining.toFixed(2)).prop('readonly', true);
        //     }
        // });
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

        // ✅ Handle Cash + Online Type
        // $('#cashOnlineTypeSelect').on('change', function() {
        //     let type = $(this).val();
        //     let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

        //     // Hide both sections initially
        //     $('#fullyCashOnlineFields, #partialCashOnlineFields').addClass('d-none');

        //     if (type === 'cash_online_fully') {
        //         // Show fully section
        //         $('#fullyCashOnlineFields').removeClass('d-none');

        //         // Set initial values
        //         $('#cashOnlineFullAmount').val('');
        //         $('#upiOnlineFullAmount').val(remaining.toFixed(2));

        //         // When user types cash → reduce online amount live
        //         $('#cashOnlineFullAmount').off('input').on('input', function() {
        //             let cash = parseFloat($(this).val()) || 0;
        //             let online = Math.max(remaining - cash, 0);
        //             $('#upiOnlineFullAmount').val(online.toFixed(2));
        //         });

        //     } else if (type === 'cash_online_partially') {
        //         // Show partial section
        //         $('#partialCashOnlineFields').removeClass('d-none');

        //         // Clear inputs
        //         $('#cashOnlinePartialAmount, #upiOnlinePartialAmount').val('');
        //         $('#remainingCashOnlineAmount').val(remaining.toFixed(2));

        //         // When user types cash → only update remaining
        //         $('#cashOnlinePartialAmount').off('input').on('input', function() {
        //             let cash = parseFloat($(this).val()) || 0;
        //             let newRemaining = Math.max(remaining - cash, 0);
        //             $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
        //         });

        //         // Online amount can be typed manually if needed
        //         $('#upiOnlinePartialAmount').off('input').on('input', function() {
        //             let online = parseFloat($(this).val()) || 0;
        //             let cash = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
        //             let newRemaining = Math.max(remaining - cash - online, 0);
        //             $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
        //         });
        //     }
        // });
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

                // if (paidType === 'cash_partially') {
                //     let partialAmount = $('#partialAmount').val();
                //     console.log("Cash partially selected, amount:", partialAmount);
                //     if (!partialAmount || parseFloat(partialAmount) <= 0) {
                //         isValid = false;
                //         console.log("Validation failed: Invalid partial cash amount");
                //         $('#partialAmountError').text("Enter a valid partial cash amount.");
                //         return false;
                //     } else {
                //         console.log("Partial cash amount valid");
                //     }
                // }
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


                // ✅ Check 1: Must be a valid positive number
                if (!onlineAmount || isNaN(onlineAmount) || onlineAmount <= 0) {
                    isValid = false;
                    if (onlineType === 'online_partially') {
                        $('#partialAmountError').text("Enter a valid positive online partial amount.");
                    } else {
                        $('#upiAmountError').text("Enter a valid positive online amount.");
                    }
                    return false;
                }

                // ✅ Check 2: Cannot exceed remaining
                if (onlineType === 'online_partially' && onlineAmount > remainingAmount) {
                    // console.log(onlineType);
                    isValid = false;
                    $('#partialAmountError').text(
                        "Partial online amount cannot exceed remaining amount (" + remainingAmount
                        .toFixed(2) + ")."
                    );
                    return false;
                }

                // ✅ Check 3: Cannot be negative
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
                    rawPending = rawPending.replace(/[₹,]/g, '').trim();
                    let pendingAmt = parseFloat(rawPending) || 0;


                    // ✅ Check for invalid or negative input
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

                    // ✅ Total should not exceed pending amount
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
                url: "/api/sales/make-payment",
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


        $.ajax({
            url: "/api/get_orders",
            method: "GET",
            data: selectedSubAdminId ? {
                selectedSubAdminId: selectedSubAdminId
            } : {},
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                "Authorization": "Bearer " + authToken,
            },
            success: function(response) {


                if (response.status) {
                    let tableBody = [];
                    const currencySymbol = response.currency_symbol || '₹';
                    const currencyPosition = response.currency_position || 'left';


                    // Helper to get static dummy source for Zomato/Swiggy orders
                    let zomatoCounter = 0;
                    let swiggyCounter = 0;
                    const zomatoIds = ['ZOM-1001', 'ZOM-1002', 'ZOM-1003', 'ZOM-1004', 'ZOM-1005', 'ZOM-1006'];
                    const swiggyIds = ['SWG-2001', 'SWG-2002', 'SWG-2003', 'SWG-2004', 'SWG-2005', 'SWG-2006'];

                    function getStaticOrderSource(order, index) {
                        let source = null;
                        let overrideId = null;
                        let pos = index + 1;
                        if (pos % 4 === 0) {
                            source = 'Swiggy';
                            overrideId = swiggyIds[swiggyCounter % swiggyIds.length];
                            swiggyCounter++;
                        } else if (pos % 3 === 0) {
                            source = 'Zomato';
                            overrideId = zomatoIds[zomatoCounter % zomatoIds.length];
                            zomatoCounter++;
                        }
                        return { source, orderId: overrideId };
                    }

                    response.data.forEach((order, index) => {
                        let staticSource = getStaticOrderSource(order, index);
                        if (staticSource.source && staticSource.orderId) {
                            order.order_number = staticSource.orderId;
                        }

                        // Check POS Integration setting
                        if (staticSource.source === 'Zomato' && typeof posIntegration !== 'undefined' && !posIntegration.isPlatformEnabled('zomato')) {
                            return; // Skip rendering this order
                        }
                        if (staticSource.source === 'Swiggy' && typeof posIntegration !== 'undefined' && !posIntegration.isPlatformEnabled('swiggy')) {
                            return; // Skip rendering this order
                        }

                        let customerNameHtml = order.user?.name || '-';
                        if (staticSource.source === 'Zomato') {
                            customerNameHtml += `<br><span class="badge" style="background-color: #E23744; color: white; padding: 2px 6px; font-size: 11px; border-radius: 12px; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; font-weight: normal; line-height: 1;"><img src="{{ env('ImagePath').'/admin/assets/img/product/zomato.png' }}" alt="Zomato" style="width: 12px; height: 12px; border-radius: 2px; object-fit: cover;"> Zomato</span>`;
                        } else if (staticSource.source === 'Swiggy') {
                            customerNameHtml += `<br><span class="badge" style="background-color: #FC8019; color: white; padding: 2px 6px; font-size: 11px; border-radius: 12px; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; font-weight: normal; line-height: 1;"><img src="{{ env('ImagePath').'/admin/assets/img/product/swiggy.jpg' }}" alt="Swiggy" style="width: 12px; height: 12px; border-radius: 2px; object-fit: cover;"> Swiggy</span>`;
                        }


                        // let createdAt = "N/A";
                        // if (order.order_items && order.order_items.length > 0) {
                        //     // Use created_at of the first order item
                        //     let date = new Date(order.created_date);

                        //     let day = String(date.getDate()).padStart(2, '0');
                        //     let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                        //         "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                        //     ];
                        //     let month = monthNames[date.getMonth()];
                        //     let year = date.getFullYear();

                        //     let hours = date.getHours();
                        //     let minutes = String(date.getMinutes()).padStart(2, '0');
                        //     let ampm = hours >= 12 ? 'PM' : 'AM';
                        //     hours = hours % 12 || 12;

                        //     createdAt =
                        //         `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;
                        // }

                        let amount = parseFloat(order.total_amount).toFixed(2);
                        let displayAmount = currencyPosition === 'right' ?
                            amount + currencySymbol // No space before/after
                            :
                            currencySymbol + amount;

                        // ---- Build ellipsis dropdown action button ----
                        let payBtnHtml = '';
                        if (parseFloat(order.remaining_amount) > 0) {
                            payBtnHtml = `<a href="javascript:void(0);" class="dd-item make-payment-btn"
                                data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                data-id="${order.id}" data-amount="${order.remaining_amount}" data-method="${order.payment_method || ''}"
                                data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                                data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                                data-remaining-emi-months="${order.remaining_emi_months}">
                                <span class="dd-icon"><i class="fas fa-credit-card" style="color:#007bff"></i></span> Pay
                            </a>`;
                        }

                        let historyBtnHtml = `<button class="dd-item open-history" data-id="${order.id}">
                            <span class="dd-icon"><i class="fas fa-history" style="color:#6c757d"></i></span> History
                        </button>`;

                        let viewBtnHtml = '';
                        @if(app('hasPermission')(2, 'view'))
                        viewBtnHtml = `<a class="dd-item" href="/sales-details/${order.id}">
                            <span class="dd-icon"><i class="fas fa-eye" style="color:#20c997"></i></span> View
                        </a>`;
                        @endif

                        let editBtnHtml = '';
                        if (!order.has_payment || order.has_payment === 0) {
                            @if(app('hasPermission')(2, 'edit'))
                            editBtnHtml = `<a class="dd-item" href="/edit-sales/${order.id}">
                                <span class="dd-icon"><i class="fas fa-edit" style="color:#fd7e14"></i></span> Edit
                            </a>`;
                            @endif
                        }

                        let invoiceBtnHtml = '';
                        @if(app('hasPermission')(2, 'view'))
                        invoiceBtnHtml = `<a class="dd-item" href="/sales-invoice/${order.id}">
                            <span class="dd-icon"><i class="fas fa-file-download" style="color:#6f42c1"></i></span> Invoice
                        </a>
                        <a class="dd-item" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id} + '?size=small');">
                            <span class="dd-icon"><i class="fas fa-print" style="color:#495057"></i></span> Print Invoice
                        </a>`;
                        @endif

                        let deleteBtnHtml = '';
                        if (
                            userRole !== 'sales-manager' &&
                            userRole !== 'purchase-manager' &&
                            userRole !== 'inventory-manager'
                        ) {
                            @if(app('hasPermission')(2, 'delete'))
                            deleteBtnHtml = `<button class="dd-item dd-item-delete delete-order" data-id="${order.id}">
                                <span class="dd-icon"><i class="fas fa-trash"></i></span> Delete
                            </button>`;
                            @endif
                        }

                        let deliveryBtnHtml = '';
                        if ((order.order_type === 'Delivery' || order.order_type === 'delivery') && order.delivery_status !== 'delivered') {
                            deliveryBtnHtml = `<a class="dd-item" href="/sales/delivery/${order.id}">
                                <span class="dd-icon"><i class="fas fa-truck" style="color:#17a2b8"></i></span> Delivery
                            </a>`;
                        }

                        let quickBtns = `
                            
                            
                            <a class="sl-m-only sl-m-pdf" href="/sales/invoice/pdf/${order.id}" target="_blank" rel="noopener" title="PDF">
                                <span>Pdf</span>
                            </a>
                        `;

                        let actionBtns = `
                            <a class="order-print-btn" href="/sales/invoice/pdf/${order.id}?size=small" target="_blank" rel="noopener" title="Print small receipt">
                                <i class="fas fa-print"></i>
                            </a>
                            ${quickBtns}
                            <div class="action-dropdown-wrap">
                                <button class="ellipsis-btn action-ellipsis-btn" type="button" title="Actions"><i class="fa-solid fa-ellipsis"></i></button>
                                <div class="action-dropdown-menu-src" style="display:none">
                                    ${payBtnHtml}
                                    ${historyBtnHtml}
                                    ${viewBtnHtml}
                                    ${deliveryBtnHtml}
                                    ${editBtnHtml}
                                    ${invoiceBtnHtml}
                                    ${deleteBtnHtml}
                                </div>
                            </div>`;

                        // Pill helpers
                        function getStatusPill(status) {
                            const s = (status || '').toLowerCase().trim();
                            let cls = 'pill-default-status';
                            if (s === 'pending')       cls = 'pill-pending';
                            else if (s === 'partial')  cls = 'pill-partial';
                            else if (s === 'completed') cls = 'pill-completed';
                            else if (s === 'extra paid' || s === 'extra_paid') cls = 'pill-extra-paid';
                            return `<span class="status-pill ${cls}">${status || ''}</span>`;
                        }
                        function getMethodPill(method) {
                            const m = (method || '').toLowerCase().trim();
                            let cls = 'pill-method-default';
                            if (m === 'scan')                          cls = 'pill-scan';
                            else if (m === 'cash')                     cls = 'pill-cash';
                            else if (m === 'online')                   cls = 'pill-online';
                            else if (m === 'cash+online' || m === 'cash_online' || m === 'cash + online') cls = 'pill-cash-online';
                            else if (m === 'pending')                  cls = 'pill-method-pending';
                            return `<span class="status-pill ${cls}">${method || ''}</span>`;
                        }
                        function getBillerPill(biller) {
                            return `<span>${biller || 'Admin'}</span>`;
                        }

                        // Generate assigned staff dropdown
                        let staffOptions = '<option value="">- Unassigned -</option>';
                        let assignedStaffName = '- Unassigned -';
                        
                        staffList.forEach(staff => {
                            // Filter by order's branch_id if order has one
                            if (!order.branch_id || staff.branch_id == order.branch_id) {
                                let selected = (order.assigned_staff_id == staff.id) ? 'selected' : '';
                                if(order.assigned_staff_id == staff.id) {
                                    assignedStaffName = staff.name;
                                }
                                staffOptions += `<option value="${staff.id}" ${selected}>${staff.name}</option>`;
                            }
                        });

                        let assignedStaffDropdown = `<select class="form-select form-select-sm assign-staff-select" data-id="${order.id}">
                            ${staffOptions}
                        </select>`;

                        tableBody.push([
                            /* Order column: desktop header + mobile/tablet Pooja ERP card */
                            `
                            <div class="sl-desktop-head">
                                <strong>${order.order_number}</strong><br>
                                ${getOrderTypeBadge(order)}
                            </div>
                            ${buildCard(order, customerNameHtml)}
                            `,

                            /* Details column (hidden) */
                            "",

                            /* Desktop-only columns - Date is sortable */
                            `<span data-sort="${new Date(order.created_date).getTime()}">${order.created_date}</span>`,
                            customerNameHtml,
                            getStatusPill(order.payment_status),
                            getMethodPill(order.payment_method),
                            displayAmount,
                            assignedStaffDropdown,
                            `<select class="form-select form-select-sm order-type-update" data-id="${order.id}">
                                <option value="Delivery" ${order.order_type === 'Delivery' ? 'selected' : ''}>Delivery</option>
                                <option value="Self Pickup" ${order.order_type === 'Self Pickup' ? 'selected' : ''}>Self Pickup</option>
                            </select>`,
                            getBillerPill(order.biller),
                            actionBtns
                        ]);
                    });


                    table.clear().rows.add(tableBody).draw();
                    calculateTotals();
                    setTimeout(() => {
                        const topScroll = document.querySelector('.table-scroll-top');
                        const tableResponsive = document.querySelector('.table-responsive');
                        const orderTable = document.getElementById('order-table');

                        if (topScroll && tableResponsive && orderTable) {
                            const topInnerDiv = topScroll.querySelector('div');

                            if (topInnerDiv) {
                                topInnerDiv.style.width = orderTable.scrollWidth + 'px';

                                // Avoid duplicate listeners
                                topScroll.onscroll = () => {
                                    tableResponsive.scrollLeft = topScroll.scrollLeft;
                                };
                                tableResponsive.onscroll = () => {
                                    topScroll.scrollLeft = tableResponsive.scrollLeft;
                                };
                            }
                        }
                    }, 100);
                } else {
                    table.clear().draw();
                    $(".datanew tbody").html('<tr><td colspan="9">No order found</td></tr>');
                    $('#filtered-total').text('₹0.00');
                    $('#total-pending, #total-paid, #total-amount').text('0.00');
                    $('#total-orders').text('0');
                }
            },
            error: function() {
                alert("Failed to load orders.");
            }
        });

        // Handle Order Type change
        $(document).on('change', '.order-type-update', function() {
            var orderId = $(this).data('id');
            var type = $(this).val(); // 'Delivery' or 'Self Pickup'
            
            // Map to the enum values
            var mappedType = type === 'Delivery' ? 'delivery' : 'self_pickup';

            $.ajax({
                url: '/sales/' + orderId + '/type',
                type: 'POST',
                data: {
                    order_type: mappedType
                },
                success: function(response) {
                    if(response.success) {
                        alertify.success(response.message);
                    } else {
                        alertify.error('Error updating order type');
                    }
                },
                error: function(xhr) {
                    alertify.error('Something went wrong. Please try again.');
                }
            });
        });

        // Store previous value on focus
        $(document).on('focus', '.assign-staff-select', function() {
            $(this).data('prev', $(this).val());
        });

        // Handle Staff Assignment change
        $(document).on('change', '.assign-staff-select', function() {
            var dropdown = $(this);
            var orderId = dropdown.data('id');
            var staffId = dropdown.val();
            var prevVal = dropdown.data('prev');

            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure you want to assign this staff?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff9f43',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Yes, assign!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    dropdown.prop('disabled', true);
                    $.ajax({
                        url: '/sales/' + orderId + '/assign-staff',
                        type: 'PATCH',
                        data: {
                            assigned_staff_id: staffId
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            dropdown.prop('disabled', false);
                            if(response.success) {
                                dropdown.data('prev', staffId);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message || 'Staff assigned successfully.',
                                    confirmButtonColor: '#ff9f43',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            } else {
                                dropdown.val(prevVal);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Error assigning staff',
                                    confirmButtonColor: '#ff9f43'
                                });
                            }
                        },
                        error: function(xhr) {
                            dropdown.prop('disabled', false);
                            dropdown.val(prevVal);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong. Please try again.',
                                confirmButtonColor: '#ff9f43'
                            });
                        }
                    });
                } else {
                    dropdown.val(prevVal);
                }
            });
        });

        // Function to calculate the total for visible (filtered) rows

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


        // Function to calculate total for visible rows
        function calculateFilteredTotal() {
            let total = 0;

            table.rows({
                filter: 'applied'
            }).every(function() {
                const row = this.data();
                const amountText = row[6]; // column index 6 = Total amount
                const rawAmount = parseFloat(amountText.replace(/[^0-9.-]+/g, '')) || 0;
                total += rawAmount;
            });

            const currencySymbol = "₹";
            $('#filtered-total').text(`${currencySymbol}${total.toFixed(2)}`);
        }

        // Apply month/year filter
        $("#filter-month, #filter-year").on("change", function() {
            const selectedMonth = $("#filter-month").val();
            const selectedYear = $("#filter-year").val();

            let regex = "";
            if (selectedMonth && selectedYear) regex = `${monthNames[selectedMonth]}.*${selectedYear}`;
            else if (selectedMonth) regex = `${monthNames[selectedMonth]}`;
            else if (selectedYear) regex = `${selectedYear}`;

            table.column(2).search(regex, true, false).draw(); // date column index = 2
            calculateTotals();
        });

        // Apply payment status filter
        $("#filter-payment-status").on("change", function() {
            const selectedStatus = $(this).val();
            
            if (selectedStatus === "") {
                table.column(4).search("").draw(); // Clear payment status column search
            } else {
                // Search for the exact status in the status pill
                table.column(4).search(selectedStatus, true, false).draw();
            }
            calculateTotals();
        });

        // Apply sort filter
        $("#filter-sort").on("change", function() {
            const sortOrder = $(this).val();
            
            if (sortOrder === "oldest") {
                table.order([2, 'asc']).draw(); // Sort by date column ascending (oldest first)
            } else if (sortOrder === "") {
                // Default to latest first
                table.order([2, 'desc']).draw(); // Sort by date column descending (latest first)
            } else {
                table.order([2, 'desc']).draw(); // Sort by date column descending (latest first)
            }
            calculateTotals();
        });

        // Apply financial year filter
        $("#filter-financial-year").on("change", function() {
            const financialYear = $(this).val();
            
            if (!financialYear) {
                table.column(2).search("").draw(); // Clear search
                calculateTotals();
                return;
            }
            
            // Parse financial year (e.g., "2024-25" -> start: 2024-04-01, end: 2025-03-31)
            const [startYear, endYear] = financialYear.split('-').map(y => parseInt(y));
            const startDate = new Date(startYear, 3, 1); // April 1st
            const endDate = new Date(startYear + 1, 2, 31); // March 31st next year
            
            // Filter rows by date within financial year
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'order-table') return true;
                
                const dateText = data[2]; // Date column
                if (!dateText) return false;
                
                try {
                    // Parse date format: "DD-MMM-YYYY HH:MM AM/PM"
                    const dateParts = dateText.split(' ');
                    const date = new Date(dateParts[0]); // Will parse "DD-MMM-YYYY"
                    
                    return date >= startDate && date <= endDate;
                } catch(e) {
                    return false;
                }
            });
            
            table.draw();
            calculateTotals();
        });

        // Recalculate total after table redraw
        table.on('draw', function() {
            calculateTotals();
        });


        $('#filter-date').on('dp.change', function() {
            let selectedDate = $(this).val().trim(); // e.g. DD-MM-YYYY

            // Clear month & year dropdowns
            $('#filter-month').val('');
            $('#filter-year').val('');

            if (selectedDate === '') {
                fetchAllOrders();
                return;
            }

            // Convert to YYYY-MM-DD
            if (selectedDate.includes('-')) {
                let parts = selectedDate.split('-');
                if (parts.length === 3) {
                    selectedDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                }
            }

            $.ajax({
                url: '/api/orders/filter',
                method: 'GET',
                data: {
                    date: selectedDate,
                    selectedSubAdminId: selectedSubAdminId
                },
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    if (response.status) {
                        renderOrders(response.data, selectedDate);
                        calculateTotals();
                    }
                }
            });
        });

        // Month/Year filter handled by column search above

        // Function to calculate total from server response
        // function updateTotalFromData(data) {
        //     let total = 0;
        //     data.forEach(row => {
        //         total += parseFloat(row.amount) || 0; // adjust key if your amount field differs
        //     });

        //     const currencySymbol = "₹";
        //     $('#filtered-total').text(`${currencySymbol}${total.toFixed(2)}`);
        // }



        function fetchAllOrders() {
            $.ajax({
                url: '/api/orders/filter', // Or '/api/orders/all' if available
                method: 'GET',
                dataType: 'json',
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    if (response.status) {
                        renderOrders(response.data, null); // no filter
                    }
                }
            });
        }
        
        $(document).on('change', '.order-type-update', function() {
            let orderId = $(this).data('id');
            let orderType = $(this).val();
            
            $.ajax({
                url: '/api/update_order_type',
                method: 'POST',
                data: {
                    order_id: orderId,
                    order_type: orderType
                },
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update order type.'
                    });
                }
            });
        });

        $(document).on('change', '.assign-staff-select', function() {
            let orderId = $(this).data('id');
            let staffId = $(this).val();
            let $select = $(this);
            
            $.ajax({
                url: `/sales/${orderId}/assign-staff`,
                method: 'PATCH',
                data: {
                    assigned_staff_id: staffId
                },
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to assign staff.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while assigning staff.'
                    });
                }
            });
        });
    });

    function renderOrdersByMonthAndYear(data, selectedMonth, selectedYear) {
        let tableBody = [];
        const currencySymbol = '₹';
        const currencyPosition = 'left';

        data.forEach(order => {
            if (!order.created_at) return;

            const date = new Date(order.created_at);
            const orderMonth = String(date.getMonth() + 1).padStart(2, '0'); // "01" to "12"
            const orderYear = String(date.getFullYear());

            if ((selectedMonth && orderMonth !== selectedMonth) || (selectedYear && orderYear !==
                    selectedYear)) {
                return;
            }

            const day = String(date.getDate()).padStart(2, '0');
            const year = date.getFullYear();
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
            ];
            const monthText = monthNames[date.getMonth()];
            const hours = date.getHours() % 12 || 12;
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const ampm = date.getHours() >= 12 ? 'PM' : 'AM';

            const formattedDate = `${day}-${monthText}-${year} ${hours}:${minutes} ${ampm}`;
            const amount = parseFloat(order.total_amount).toFixed(2);
            const displayAmount = currencyPosition === 'right' ? amount + currencySymbol : currencySymbol +
                amount;

            // ---- Build ellipsis dropdown (renderOrdersByMonthAndYear) ----
            let payBtnHtml2 = '';
            if (parseFloat(order.remaining_amount) > 0) {
                payBtnHtml2 = `<a href="javascript:void(0);" class="dd-item make-payment-btn"
                    data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                    data-id="${order.id}" data-amount="${order.remaining_amount}"
                    data-method="${order.payment_method || ''}"
                    data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                    data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                    data-remaining-emi-months="${order.remaining_emi_months}">
                    <span class="dd-icon"><i class="fas fa-credit-card" style="color:#007bff"></i></span> Pay
                </a>`;
            }
            let histBtnHtml2 = `<button class="dd-item open-history" data-id="${order.id}">
                <span class="dd-icon"><i class="fas fa-history" style="color:#6c757d"></i></span> History
            </button>`;
            let viewBtnHtml2 = '';
            @if(app('hasPermission')(2, 'view'))
            viewBtnHtml2 = `<a class="dd-item" href="/sales-details/${order.id}">
                <span class="dd-icon"><i class="fas fa-eye" style="color:#20c997"></i></span> View
            </a>`;
            @endif
            let editBtnHtml2 = '';
            if (!order.has_payment || order.has_payment === 0) {
                @if(app('hasPermission')(2, 'edit'))
                editBtnHtml2 = `<a class="dd-item" href="/edit-sales/${order.id}">
                    <span class="dd-icon"><i class="fas fa-edit" style="color:#fd7e14"></i></span> Edit
                </a>`;
                @endif
            }
            let invBtnHtml2 = '';
            @if(app('hasPermission')(2, 'view'))
            invBtnHtml2 = `<a class="dd-item" href="/sales-invoice/${order.id}">
                <span class="dd-icon"><i class="fas fa-file-download" style="color:#6f42c1"></i></span> Invoice
            </a>
            <a class="dd-item" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id} + '?size=small');">
                <span class="dd-icon"><i class="fas fa-print" style="color:#495057"></i></span> Print Invoice
            </a>`;
            @endif
            let delBtnHtml2 = '';
            if (!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole)) {
                @if(app('hasPermission')(2, 'delete'))
                delBtnHtml2 = `<button class="dd-item dd-item-delete delete-order" data-id="${order.id}">
                    <span class="dd-icon"><i class="fas fa-trash"></i></span> Delete
                </button>`;
                @endif
            }
            let quickBtns2 = `
                
                
                <a class="sl-m-only sl-m-pdf" href="/sales/invoice/pdf/${order.id}" target="_blank" rel="noopener" title="PDF">
                    <span>Pdf</span>
                </a>
            `;
            let actionBtns = `
                <a class="order-print-btn" href="/sales/invoice/pdf/${order.id}?size=small" target="_blank" rel="noopener" title="Print small receipt">
                    <i class="fas fa-print"></i>
                </a>
                ${quickBtns2}
                <div class="action-dropdown-wrap">
                    <button class="ellipsis-btn action-ellipsis-btn" type="button" title="Actions"><i class="fa-solid fa-ellipsis"></i></button>
                    <div class="action-dropdown-menu-src" style="display:none">
                        ${payBtnHtml2}
                        ${histBtnHtml2}
                        <!-- <a class="dd-item" href="javascript:void(0);">
                            <span class="dd-icon"><i class="fas fa-table" style="color:#17a2b8"></i></span> EMI Details
                        </a> -->
                        ${viewBtnHtml2}
                        <!-- <a class="dd-item" href="javascript:void(0);">
                            <span class="dd-icon"><i class="fas fa-ticket-alt" style="color:#e83e8c"></i></span> Raise Ticket
                        </a> -->
                        ${editBtnHtml2}
                        ${invBtnHtml2}
                        ${delBtnHtml2}
                    </div>
                </div>`;

            // ---- Generate Assigned Staff Dropdown ----
            let assignedStaffOptions = `<option value="">— Unassigned —</option>`;
            for (const [staffId, staffName] of Object.entries(staffList)) {
                let selected = (order.assigned_staff_id == staffId) ? 'selected' : '';
                assignedStaffOptions += `<option value="${staffId}" ${selected}>${staffName}</option>`;
            }
            let assignedStaffHtml = `
                <select class="form-select assign-staff-select" data-id="${order.id}">
                    ${assignedStaffOptions}
                </select>
            `;

            // ---- Generate Order Type Dropdown ----
            let orderTypeHtml = `
                <select class="form-select order-type-update" data-id="${order.id}">
                    <option value="delivery" ${order.order_type === 'delivery' ? 'selected' : ''}>Delivery</option>
                    <option value="self_pickup" ${order.order_type === 'self_pickup' ? 'selected' : ''}>Self Pickup</option>
                </select>
            `;

            tableBody.push([
                /* Order column: desktop header + mobile/tablet Pooja ERP card */
                `
                <div class="sl-desktop-head">
                    <strong>${order.order_number || ''}</strong>
                </div>
                ${buildCard(order, order.user?.name || 'N/A')}
                `,
                /* Details column (hidden) */
                "",
                /* Desktop-only columns */
                order.created_date || 'N/A',
                order.user?.name || 'N/A',
                (function(s){ const sl=(s||'').toLowerCase().trim(); let c='pill-default-status'; if(sl==='pending')c='pill-pending'; else if(sl==='partial')c='pill-partial'; else if(sl==='completed')c='pill-completed'; else if(sl==='extra paid'||sl==='extra_paid')c='pill-extra-paid'; return `<span class="status-pill ${c}">${s||''}</span>`; })(order.payment_status),
                (function(m){ const ml=(m||'').toLowerCase().trim(); let c='pill-method-default'; if(ml==='scan')c='pill-scan'; else if(ml==='cash')c='pill-cash'; else if(ml==='online')c='pill-online'; else if(ml==='cash+online'||ml==='cash_online'||ml==='cash + online')c='pill-cash-online'; else if(ml==='pending')c='pill-method-pending'; return `<span class="status-pill ${c}">${m||''}</span>`; })(order.payment_method),
                displayAmount || '0.00',
                assignedStaffHtml,
                orderTypeHtml,
                `<span class="status-pill pill-biller">${order.biller || 'Admin'}</span>`,
                actionBtns
            ]);
        });

        if ($.fn.DataTable.isDataTable('#order-table')) {
            const table = $('#order-table').DataTable();
            table.clear().rows.add(tableBody).draw();
        }
    }

    function renderOrders(data, selectedDate) {
        let tableBody = [];
        const currencySymbol = '₹';
        const currencyPosition = 'left';

        data.forEach(order => {
            if (!order.created_date) return;

            const orderDate = new Date(order.created_date).toISOString().split('T')[0]; // 'YYYY-MM-DD'

            // If selectedDate exists and doesn't match, skip this
            if (selectedDate && selectedDate !== orderDate) return;

            // Format and push to table
            let date = new Date(order.created_date);
            let day = String(date.getDate()).padStart(2, '0');
            let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
            ];
            let month = monthNames[date.getMonth()];
            let year = date.getFullYear();

            let hours = date.getHours();
            let minutes = String(date.getMinutes()).padStart(2, '0');
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;

            let amount = parseFloat(order.total_amount).toFixed(2);
            let displayAmount = currencyPosition === 'right' ?
                amount + currencySymbol : currencySymbol + amount;

            const formattedDate = `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;

            // 🔹 Build ellipsis dropdown (renderOrders)
            let payBtnHtml3 = '';
            if (parseFloat(order.remaining_amount) > 0) {
                payBtnHtml3 = `<a href="javascript:void(0);" class="dd-item make-payment-btn"
                    data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                    data-id="${order.id}" data-amount="${order.remaining_amount}"
                    data-method="${order.payment_method || ''}"
                    data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                    data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                    data-remaining-emi-months="${order.remaining_emi_months}">
                    <span class="dd-icon"><i class="fas fa-credit-card" style="color:#007bff"></i></span> Pay
                </a>`;
            }
            let histBtnHtml3 = `<button class="dd-item open-history" data-id="${order.id}">
                <span class="dd-icon"><i class="fas fa-history" style="color:#6c757d"></i></span> History
            </button>`;
            let viewBtnHtml3 = '';
            @if(app('hasPermission')(2, 'view'))
            viewBtnHtml3 = `<a class="dd-item" href="/sales-details/${order.id}">
                <span class="dd-icon"><i class="fas fa-eye" style="color:#20c997"></i></span> View
            </a>`;
            @endif
            let editBtnHtml3 = '';
            if (!order.has_payment || order.has_payment === 0) {
                @if(app('hasPermission')(2, 'edit'))
                editBtnHtml3 = `<a class="dd-item" href="/edit-sales/${order.id}">
                    <span class="dd-icon"><i class="fas fa-edit" style="color:#fd7e14"></i></span> Edit
                </a>`;
                @endif
            }
            let invBtnHtml3 = '';
            @if(app('hasPermission')(2, 'view'))
            invBtnHtml3 = `<a class="dd-item" href="/sales-invoice/${order.id}">
                <span class="dd-icon"><i class="fas fa-file-download" style="color:#6f42c1"></i></span> Invoice
            </a>
            <a class="dd-item" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id} + '?size=small');">
                <span class="dd-icon"><i class="fas fa-print" style="color:#495057"></i></span> Print Invoice
            </a>`;
            @endif
            let delBtnHtml3 = '';
            if (!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole)) {
                @if(app('hasPermission')(2, 'delete'))
                delBtnHtml3 = `<button class="dd-item dd-item-delete delete-order" data-id="${order.id}">
                    <span class="dd-icon"><i class="fas fa-trash"></i></span> Delete
                </button>`;
                @endif
            }
            let quickBtns3 = `
                
                <a class="sl-m-only sl-m-pdf" href="/sales/invoice/pdf/${order.id}" target="_blank" rel="noopener" title="PDF">
                    <span>Pdf</span>
                </a>
            `;
            let actionBtns = `
                <a class="order-print-btn" href="/sales/invoice/pdf/${order.id}?size=small" target="_blank" rel="noopener" title="Print small receipt">
                    <i class="fas fa-print"></i>
                </a>
                ${quickBtns3}
                <div class="action-dropdown-wrap">
                    <button class="ellipsis-btn action-ellipsis-btn" type="button" title="Actions"><i class="fa-solid fa-ellipsis"></i></button>
                    <div class="action-dropdown-menu-src" style="display:none">
                        ${payBtnHtml3}
                        ${histBtnHtml3}
                        <a class="dd-item" href="javascript:void(0);">
                            <span class="dd-icon"><i class="fas fa-table" style="color:#17a2b8"></i></span> EMI Details
                        </a>
                        ${viewBtnHtml3}
                        <a class="dd-item" href="javascript:void(0);">
                            <span class="dd-icon"><i class="fas fa-ticket-alt" style="color:#e83e8c"></i></span> Raise Ticket
                        </a>
                        ${editBtnHtml3}
                        ${invBtnHtml3}
                        ${delBtnHtml3}
                    </div>
                </div>`;

            // ---- Generate Assigned Staff Dropdown ----
            let assignedStaffOptions = `<option value="">— Unassigned —</option>`;
            for (const [staffId, staffName] of Object.entries(staffList)) {
                let selected = (order.assigned_staff_id == staffId) ? 'selected' : '';
                assignedStaffOptions += `<option value="${staffId}" ${selected}>${staffName}</option>`;
            }
            let assignedStaffHtml = `
                <select class="form-select assign-staff-select" data-id="${order.id}">
                    ${assignedStaffOptions}
                </select>
            `;

            // ---- Generate Order Type Dropdown ----
            let orderTypeHtml = `
                <select class="form-select order-type-update" data-id="${order.id}">
                    <option value="delivery" ${order.order_type === 'delivery' ? 'selected' : ''}>Delivery</option>
                    <option value="self_pickup" ${order.order_type === 'self_pickup' ? 'selected' : ''}>Self Pickup</option>
                </select>
            `;

            tableBody.push([
                /* Order column: desktop header + mobile/tablet Pooja ERP card */
                `
                <div class="sl-desktop-head">
                    <strong>${order.order_number || ''}</strong>
                </div>
                ${buildCard(order, order.user?.name || 'N/A')}
                `,
                /* Details column (hidden) */
                "",
                /* Desktop-only columns */
                order.created_date || 'N/A',
                order.user?.name || 'N/A',
                (function(s){ const sl=(s||'').toLowerCase().trim(); let c='pill-default-status'; if(sl==='pending')c='pill-pending'; else if(sl==='partial')c='pill-partial'; else if(sl==='completed')c='pill-completed'; else if(sl==='extra paid'||sl==='extra_paid')c='pill-extra-paid'; return `<span class="status-pill ${c}">${s||''}</span>`; })(order.payment_status),
                (function(m){ const ml=(m||'').toLowerCase().trim(); let c='pill-method-default'; if(ml==='scan')c='pill-scan'; else if(ml==='cash')c='pill-cash'; else if(ml==='online')c='pill-online'; else if(ml==='cash+online'||ml==='cash_online'||ml==='cash + online')c='pill-cash-online'; else if(ml==='pending')c='pill-method-pending'; return `<span class="status-pill ${c}">${m||''}</span>`; })(order.payment_method),
                displayAmount || '0.00',
                assignedStaffHtml,
                orderTypeHtml,
                `<span class="status-pill pill-biller">${order.biller || 'Admin'}</span>`,
                actionBtns
            ]);
        });

        if ($.fn.DataTable.isDataTable('#order-table')) {
            const table = $('#order-table').DataTable();
            table.clear().rows.add(tableBody).draw();
        }
    }
    // ===== Ellipsis dropdown toggle logic (fixed positioning to escape overflow:auto) =====
    // Create a single floating dropdown container appended to <body>
    var $floatingDropdown = $('<div id="floating-action-dropdown" class="action-dropdown-menu"></div>').appendTo('body');
    var $activeBtn = null;

    $(document).on('click', '.action-ellipsis-btn', function(e) {
        e.stopPropagation();
        var $btn = $(this);
        var $wrap = $btn.closest('.action-dropdown-wrap');
        var $srcMenu = $wrap.find('.action-dropdown-menu-src');

        // If clicking the same button that's already open, close it
        if ($activeBtn && $activeBtn.is($btn) && $floatingDropdown.hasClass('show')) {
            $floatingDropdown.removeClass('show');
            $activeBtn = null;
            return;
        }

        // Copy content from the hidden source menu
        $floatingDropdown.html($srcMenu.html());
        $activeBtn = $btn;

        // Calculate position
        var rect = this.getBoundingClientRect();
        var dropW = 190;
        var dropH = $floatingDropdown.outerHeight(true) || 320; // estimate
        var left = rect.right - dropW;
        var top  = rect.bottom + 4;

        // Flip upward if too close to bottom
        if (top + dropH > window.innerHeight - 10) {
            top = rect.top - dropH - 4;
        }
        // Keep within left edge
        if (left < 4) left = 4;

        $floatingDropdown
            .css({ top: top + 'px', left: left + 'px' })
            .addClass('show');
    });

    // Close when clicking anywhere else
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.action-ellipsis-btn').length && !$(e.target).closest('#floating-action-dropdown').length) {
            $floatingDropdown.removeClass('show');
            $activeBtn = null;
        }
    });

    // Close when scrolling the table
    $(document).on('scroll', '.table-responsive', function() {
        $floatingDropdown.removeClass('show');
        $activeBtn = null;
    });

    // Global history modal open
    $(document).on('click', '.open-history', function() {
        var authToken = localStorage.getItem("authToken");

        const jobCardId = $(this).data('id');
        $('#globalPaymentHistoryList').html('<li class="list-group-item">Loading...</li>');
        $.ajax({
            url: '/api/order/payment-history/' + jobCardId,
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

    $(document).on('click', '.delete-order', function() {
        var orderId = $(this).data('id'); // ✅ Correct usage
        var authToken = localStorage.getItem("authToken");

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
                    url: 'api/delete/' + orderId,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status === true) {
                            Swal.fire({
                                title: "Deleted!",
                                text: response.message,
                                icon: "success",
                                confirmButtonColor: '#ff9f43', // Set OK button color here
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response.error,
                                icon: "error",
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = "An error occurred while deleting the order";
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }

                        Swal.fire({
                            title: "Error",
                            text: errorMessage,
                            icon: "error",
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }

                });
            }
        });
    });
    // $('#exportPdf').click(function () {
    //     var authToken = localStorage.getItem("authToken");
    //     let selectedYear = $('#filter-year').val() || '';
    //     let selectedMonth = $('#filter-month').val() || '';
    //     let selectedDate = $('#filter-date').val() || '';
    //     let selectedCustomerId = $('#filter-customer').val() || '';
    //     const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

    //     $.ajax({
    //         url: `/api/pdf-orders`,
    //         type: 'GET',
    //         headers: {
    //             "Authorization": "Bearer " + authToken
    //         },
    //         data: {
    //             year: selectedYear,
    //             month: selectedMonth,
    //             date: selectedDate,
    //             customerId: selectedCustomerId,
    //             selectedSubAdminId: selectedSubAdminId,
    //             type: 'pdf'
    //         },
    //         xhrFields: {
    //             responseType: 'blob'
    //         },
    //         success: function (data, status, xhr) {
    //             let disposition = xhr.getResponseHeader('Content-Disposition');
    //             let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
    //             let filename = (matches != null && matches[1]) ? matches[1].replace(/['"]/g, '') : 'Export.pdf';

    //             let blob = new Blob([data], { type: 'application/pdf' });
    //             let link = document.createElement('a');
    //             link.href = window.URL.createObjectURL(blob);
    //             link.download = filename;
    //             document.body.appendChild(link);
    //             link.click();
    //             document.body.removeChild(link);
    //         },
    //         error: function (xhr) {
    //             alert("PDF export failed: " + (xhr.responseJSON?.message ?? "Unknown error"));
    //         }
    //     });
    // });
    $('#exportPdf').click(function() {
        var authToken = localStorage.getItem("authToken");
        let selectedYear = $('#filter-year').val() || '';
        let selectedMonth = $('#filter-month').val() || '';
        let selectedDate = $('#filter-date').val() || '';
        let selectedCustomerId = $('#filter-customer').val() || '';
        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

        $.ajax({
            url: `/api/pdf-orders`,
            type: 'GET',
            headers: {
                "Authorization": "Bearer " + authToken
            },
            data: {
                year: selectedYear,
                month: selectedMonth,
                date: selectedDate,
                customerId: selectedCustomerId,
                selectedSubAdminId: selectedSubAdminId,
                type: 'pdf'
            },
            success: function(response) {
                if (response.status && response.file_url) {
                    // Open PDF in new tab or trigger download
                    const link = document.createElement('a');
                    link.href = response.file_url;
                    link.download = response.file_name || 'sales_report.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert("Failed to generate PDF: " + response.message);
                }
            },
            error: function(xhr) {
                alert("PDF export failed: " + (xhr.responseJSON?.message ?? "Unknown error"));
            }
        });
    });
    $('#exportAllChallan').click(function() {
        let selectedYear = $('#filter-year').val() || '';
        let selectedMonth = $('#filter-month').val() || '';
        let selectedDate = $('#filter-date').val() || '';
        let selectedVendorId = $('#filter-customer').val() || '';
        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
        let authToken = localStorage.getItem("authToken");

        let url =
            `/api/export-order?year=${selectedYear}&month=${selectedMonth}&date=${selectedDate}&customer_id=${selectedVendorId}&selectedSubAdminId=${selectedSubAdminId}`;

        $.ajax({
            url: url,
            method: "GET",
            headers: {
                "Authorization": "Bearer " + authToken
            },
            success: function(response) {
                if (response.status && response.file_url) {
                    // Trigger download via file_url
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
</script>
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
    });
</script>
@endif
@endpush
