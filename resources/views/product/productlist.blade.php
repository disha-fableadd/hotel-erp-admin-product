    @extends('layout.app')

@section('title', 'Product List')

@section('content')
    <style>
        .sorting_1 {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        /* --- Premium Dropdown Styling --- */
        .action-dropdown .dropdown-menu {
            border: 1px solid #f1f5f9 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
        }
        
        .action-dropdown .dropdown-item:hover {
            background-color: #f8fafc !important;
            color: #ff9f43 !important;
            transform: translateX(4px);
        }
        
        .action-dropdown .dropdown-item.text-danger:hover {
            background-color: #fef2f2 !important;
            color: #dc2626 !important;
        }
        
        .action-dropdown .btn:hover {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }

        /* Prevent vertical scrollbar caused by dropdown in table-responsive */
        @media (min-width: 992px) {
            .table-responsive {
                overflow: visible !important;
            }
        }
        
        .table-responsive {
            min-height: 250px;  
        }

        .table-scroll-top {
            overflow-x: auto;
            overflow-y: hidden;
            height: 20px;
            width: 100%;
            margin-bottom: 5px;
        }

        .table-scroll-top div {
            height: 1px;
        }

        .table-scroll-top {
            display: none;
        }

        .product-filter-wrap {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .product-filter-box {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .product-filter-box .form-group {
            margin-bottom: 0;
        }

        .product-filter-search {
            min-width: 260px;
        }

        .product-filter-select {
            min-width: 180px;
        }

        .product-filter-select .select2-container {
            width: 100% !important;
        }

        .product-filter-select .select2-container--default .select2-selection--single {
            height: 40px !important;
            border: 1px solid #dbe0e6 !important;
            border-radius: 5px !important;
            display: flex !important;
            align-items: center !important;
        }

        .product-filter-select .select2-container--default.select2-container--open .select2-selection--single,
        .product-filter-select .select2-container--default .select2-selection--single:focus {
            border-color: #ff9f43 !important;
        }

        .product-filter-select .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4b5b6b !important;
            line-height: 40px !important;
            padding-left: 10px !important;
            padding-right: 30px !important;
        }

        .product-filter-select .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            right: 8px !important;
        }

        .product-filter-dropdown .select2-search--dropdown {
            padding: 5px !important;
        }

        .product-filter-dropdown .select2-search--dropdown .select2-search__field {
            height: 30px !important;
            border: 1px solid #cfd6df !important;
            outline: none !important;
        }

        .product-filter-dropdown .select2-results__option {
            padding: 8px 10px !important;
            color: #2f3b48 !important;
            font-size: 14px !important;
        }

        .product-filter-dropdown .select2-results__option--highlighted[aria-selected],
        .product-filter-dropdown .select2-results__option[aria-selected=true] {
            background: #ff9f43 !important;
            color: #fff !important;
        }

        .product-search-input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .product-search-input i {
            position: absolute;
            top: 50%;
            left: 14px;
            color: #637381;
            transform: translateY(-50%);
            pointer-events: none;
            font-size: 14px;
            z-index: 2;
        }

        .product-search-input input,
        .product-search-input input.form-control,
        .product-search-input input.form-control-sm {
            height: 40px !important;
            padding-left: 38px !important;
            padding-right: 12px !important;
            border-color: #dbe0e6 !important;
            border-radius: 6px !important;
            font-size: 14px !important;
            width: 100%;
        }

        .product-search-input input:focus {
            border-color: #ff9f43 !important;
            box-shadow: none !important;
        }

        /* Page Header Layout matching pooja-erp */
        .page-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: nowrap !important;
            margin-bottom: 15px !important;
            gap: 8px !important;
        }

        .page-header .page-title {
            margin: 0 !important;
            flex: 1 1 auto !important;
            min-width: 0 !important;
        }

        .page-header .page-title h4 {
            margin: 0 !important;
            font-size: 18px !important;
            font-weight: 700 !important;
            color: #1b2850 !important;
            white-space: nowrap !important;
            line-height: 1.2 !important;
        }

        .product-page-actions {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            flex-wrap: nowrap !important;
            justify-content: flex-end !important;
            flex-shrink: 0 !important;
            margin: 0 !important;
        }

        .product-import-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            height: 34px !important;
            padding: 0 12px !important;
            border-radius: 8px !important;
            color: #fff !important;
            background: #ff9f43 !important;
            border: 1px solid #ff9f43 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
        }

        .product-import-btn:hover,
        .product-import-btn:focus {
            color: #fff !important;
            background: #f38e27 !important;
            border-color: #f38e27 !important;
        }

        .product-new-btn,
        .page-btn .btn-added.product-new-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            height: 34px !important;
            padding: 0 12px !important;
            border-radius: 8px !important;
            color: #fff !important;
            background: #1b2850 !important;
            border: 1px solid #1b2850 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
        }

        .product-new-btn:hover,
        .product-new-btn:focus {
            color: #fff !important;
            background: #101a3b !important;
            border-color: #101a3b !important;
        }

        /* Excel and PDF Export Buttons matching pooja-erp */
        #exportAllChallan {
            background: #e7f6ec !important;
            color: #198754 !important;
            border: 1px solid #d1f0db !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            height: 36px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }
        #exportAllChallan:hover,
        #exportAllChallan:focus {
            background: #d4eedd !important;
            color: #146c43 !important;
            border-color: #bee5cb !important;
        }

        #exportPdf {
            background: #fdecee !important;
            color: #dc3545 !important;
            border: 1px solid #fbd2d6 !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            height: 36px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }
        #exportPdf:hover,
        #exportPdf:focus {
            background: #fad7db !important;
            color: #b02a37 !important;
            border-color: #f7c1c7 !important;
        }

        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
                -webkit-overflow-scrolling: touch !important;
                /* smooth scrolling on iOS */
            }

            select#filterCategory {
                font-size: 12px !important;
                /* width: 168px !important; */
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch !important;
            }

            .form-control-sm {
                min-height: calc(1.5em + .5rem + 2px);
                padding: .25rem .5rem;
                font-size: 13px !important;
                border-radius: .2rem;
            }

            .search-set {
                margin-right: 1rem !important;
            }
            .product-filter-wrap,
            .product-filter-box {
                align-items: stretch;
                flex-direction: column;
            }

            .product-filter-search,
            .product-filter-select {
                width: 100%;
                min-width: 100%;
            }

            #exportAllChallan,
            #exportPdf {
                flex: 1 1 0 !important;
                width: auto !important;
                max-width: none !important;
                min-width: 0 !important;
                height: 36px !important;
                margin: 0 !important;
                padding: 0 10px !important;
                font-size: 13px !important;
            }

            #exportAllChallan i,
            #exportPdf i {
                font-size: 13px !important;
                margin-right: 4px !important;
            }
        }

        @media screen and (max-width: 576px) {
            .page-header .page-title h4 {
                font-size: 16px !important;
            }
            .product-page-actions {
                gap: 6px !important;
            }
            .product-import-btn,
            .product-new-btn {
                height: 32px !important;
                padding: 0 10px !important;
                font-size: 12px !important;
                border-radius: 7px !important;
            }
        }

        /* Hide duplicate DataTable default search input */
        .dataTables_filter {
            display: none !important;
        }

        /* ========================================================
           Mobile Filter Toggle & Bottom Sheet Modal (from pooja-erp)
           ======================================================== */
        .pl-filter-toggle {
            position: relative;
            flex: 0 0 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            padding: 0;
            border: 0 !important;
            border-radius: 50% !important;
            background: #f05a6a;
            color: #fff;
            box-shadow: 0 2px 6px rgba(240, 90, 106, .35);
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease;
        }
        .pl-filter-toggle:active {
            transform: scale(0.95);
        }
        .pl-filter-toggle.has-active {
            background: #e8334a;
        }
        .pl-filter-toggle i {
            color: #fff;
            font-size: 14px;
        }
        .pl-filter-count {
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
            font-weight: 700;
        }

        .pl-filter-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            z-index: 1050;
            background: rgba(16, 24, 40, .45);
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s ease;
        }
        .pl-filter-sheet {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            margin: 0 auto !important;
            max-width: 520px !important;
            width: 100% !important;
            z-index: 1055;
            display: flex;
            flex-direction: column;
            max-height: 85vh;
            background: #fff;
            border-radius: 24px 24px 0 0 !important;
            border: none !important;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.16) !important;
            transform: translate3d(0, 105%, 0) !important;
            transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1) !important;
        }
        body.pl-filter-open .pl-filter-backdrop {
            opacity: 1;
            pointer-events: auto;
        }
        body.pl-filter-open .pl-filter-sheet {
            transform: translate3d(0, 0, 0) !important;
        }
        body.pl-filter-open {
            overflow: hidden !important;
        }

        .pl-filter-sheet-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #eef0f4;
            font-size: 16px;
            font-weight: 700;
            color: #1b2850;
        }
        .pl-filter-sheet-close {
            width: 30px;
            height: 30px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color 0.15s, background-color 0.15s;
        }
        .pl-filter-sheet-close:hover {
            color: #1e293b;
            background-color: #f1f5f9;
        }
        .pl-filter-sheet-body {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 16px 20px 8px;
        }
        .pl-filter-sheet-body .form-label {
            display: block;
            margin: 0 0 6px !important;
            font-size: 13px !important;
            font-weight: 600;
            color: #344054 !important;
        }
        .filter-select-custom {
            border-radius: 8px !important;
            border: 1px solid #d0d5dd !important;
            padding: 9px 12px !important;
            font-size: 13.5px !important;
            background-color: #fff !important;
            width: 100% !important;
            height: 42px !important;
            color: #344054 !important;
            font-weight: 500 !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .filter-select-custom:focus {
            border-color: #ff9f43 !important;
            box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.15) !important;
        }
        .pl-filter-sheet-foot {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 14px 20px calc(14px + env(safe-area-inset-bottom, 0px));
            border-top: 1px solid #eef0f4;
        }
        .pl-filter-clear,
        .pl-filter-apply {
            height: 42px;
            border: 0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .pl-filter-clear {
            background: #f1f5f9;
            color: #475467;
        }
        .pl-filter-clear:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .pl-filter-apply {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.28);
        }
        .pl-filter-apply:hover {
            background: #dc2626;
        }
        body.pl-filter-open .select2-container--open .select2-dropdown {
            z-index: 1060 !important;
        }

        /* Card representation default */
        .pl-m-card {
            display: none;
        }

        /* ========================================================
           Desktop View (>= 1281px): Standard DataTable & Filter Selects
           ======================================================== */
        @media (min-width: 1281px) {
            table.datanew thead th,
            table.datanew tbody td {
                display: table-cell !important;
            }
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: none !important;
            }
            .pl-m-card {
                display: none !important;
            }
            .pl-desktop-head {
                display: flex !important;
            }
            .pl-filter-toggle,
            .pl-filter-backdrop,
            .pl-filter-sheet {
                display: none !important;
            }
        }

        /* ========================================================
           Mobile, Tablet & Nest Hub Max View (<= 1280.98px): Card UI
           ======================================================== */
        @media (max-width: 1280.98px) {
            .table-scroll-top {
                display: none !important;
            }
            .table-responsive {
                overflow: visible !important;
                border: 0 !important;
                min-height: auto;
            }
            table.datanew {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                border: 0 !important;
            }
            table.datanew thead {
                display: none !important;
            }
            table.datanew tbody tr {
                display: block !important;
                width: 100% !important;
                padding: 12px 14px !important;
                border: 1px solid #eceef3 !important;
                border-radius: 12px !important;
                background: #fff !important;
                box-shadow: 0 1px 3px rgba(16, 24, 40, 0.08) !important;
                margin-bottom: 0 !important;
                transition: box-shadow 0.2s ease, border-color 0.2s ease;
            }
            table.datanew tbody tr:hover {
                border-color: #e2e8f0 !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
            }
            table.datanew tbody tr > td {
                background: transparent !important;
                border: 0 !important;
                box-shadow: none !important;
            }
            table.datanew tbody tr > td:first-child {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
            }
            table.datanew tbody tr > td:not(:first-child) {
                display: none !important;
            }
            table.datanew tbody tr > td.dataTables_empty {
                display: block !important;
                width: 100% !important;
                text-align: center;
                padding: 24px 14px !important;
                color: #94a3b8 !important;
                font-size: 14px !important;
            }
            .pl-desktop-head {
                display: none !important;
            }
            .collapse.pl-details {
                display: none !important;
            }

            .pl-filter-toggle {
                display: inline-flex !important;
            }

            /* Product Card Layout inside first cell */
            .pl-m-card {
                display: grid !important;
                grid-template-columns: 48px minmax(0, 1fr);
                column-gap: 12px;
                row-gap: 8px;
                width: 100%;
            }
            .pl-m-img {
                width: 48px;
                height: 48px;
                border-radius: 8px;
                overflow: hidden;
                background: #f3f4f6;
                display: block;
                flex-shrink: 0;
            }
            .pl-m-img img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .pl-m-body {
                min-width: 0;
            }
            .pl-m-top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 8px;
            }
            .pl-m-name {
                font-size: 14px;
                font-weight: 600;
                color: #1b2850 !important;
                text-transform: uppercase;
                line-height: 1.3;
                min-width: 0;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .pl-m-amount {
                font-size: 14px;
                font-weight: 700;
                color: #1b2850;
                white-space: nowrap;
            }
            .pl-m-badges {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 4px 8px;
                margin-top: 5px;
                min-width: 0;
            }
            .pl-m-badge {
                font-size: 10px;
                font-weight: 600;
                padding: 2px 9px;
                border-radius: 12px;
                white-space: nowrap;
            }
            .pl-m-badge.is-ok {
                background: #e7f8ef;
                color: #12a150;
            }
            .pl-m-badge.is-low {
                background: #fdecec;
                color: #e5483d;
            }
            .pl-m-code {
                font-size: 11px;
                color: #8b93a7;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                max-width: 100%;
            }
            .pl-m-foot {
                grid-column: 1 / -1;
                display: flex;
                align-items: flex-end;
                gap: 16px;
                padding-top: 8px;
                border-top: 1px dashed #eceef3;
            }
            .pl-m-stat {
                min-width: 0;
            }
            .pl-m-stat span {
                display: block;
                font-size: 11px;
                color: #8b93a7;
            }
            .pl-m-stat strong {
                display: block;
                font-size: 13px;
                color: #1b2850;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .pl-m-stat.quantity-history {
                cursor: pointer;
            }
            .pl-m-cat {
                flex: 1 1 auto;
            }
            .pl-m-acts {
                margin-left: auto;
                display: flex;
                align-items: center;
                gap: 4px;
                flex-shrink: 0;
            }
            .pl-m-acts .action-dropdown .btn {
                border: 0 !important;
                background: transparent !important;
                color: #8b93a7 !important;
                padding: 0 !important;
                width: 32px !important;
                height: 32px !important;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
            }
            .pl-m-acts .action-dropdown .btn i {
                font-size: 16px;
            }

            /* Hide desktop filters on mobile/tablet */
            .product-filter-box > .product-filter-select {
                display: none !important;
            }
            .product-filter-wrap {
                margin-bottom: 12px;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
            }
            .product-filter-box {
                width: 100% !important;
            }
            .product-filter-search {
                width: 100% !important;
                min-width: 100% !important;
            }
            .product-export-btns {
                width: 100% !important;
                display: flex !important;
                flex-wrap: nowrap !important;
                gap: 8px !important;
            }
            .product-export-btns button,
            #exportAllChallan,
            #exportPdf {
                flex: 1 1 0 !important;
                width: 100% !important;
                max-width: none !important;
                min-width: 0 !important;
                height: 36px !important;
                margin: 0 !important;
                padding: 0 10px !important;
                font-size: 13px !important;
            }
            #exportAllChallan i,
            #exportPdf i {
                font-size: 13px !important;
                margin-right: 4px !important;
            }
        }

        /* ========================================================
           Tablet & Nest Hub Max View (768px to 1280.98px): 2 Column Grid
           ======================================================== */
        @media (min-width: 768px) and (max-width: 1280.98px) {
            table.datanew tbody {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 12px !important;
                width: 100% !important;
            }
            table.datanew tbody tr:has(.dataTables_empty) {
                grid-column: span 2 !important;
            }
        }

        /* ========================================================
           Mobile View (< 768px): 1 Column Vertical Stack
           ======================================================== */
        @media (max-width: 767.98px) {
            table.datanew tbody {
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
                width: 100% !important;
            }
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

    <!-- Quantity History Modal -->
    <div class="modal fade" id="quantityHistoryModal" tabindex="-1" aria-labelledby="quantityHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quantityHistoryLabel">Quantity History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="quantityHistoryContent">
                    <!-- History content will be injected here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bottom Sheet Modal for Mobile/Tablet -->
    <div class="pl-filter-backdrop" id="plFilterBackdrop"></div>
    <div class="pl-filter-sheet" id="plFilterSheet" role="dialog" aria-modal="true" aria-labelledby="plFilterSheetTitle">
        <div class="pl-filter-sheet-head">
            <span id="plFilterSheetTitle">Filter By</span>
            <button type="button" class="pl-filter-sheet-close" id="plFilterSheetClose" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="pl-filter-sheet-body" id="plFilterSheetBody">
            <div class="mb-3">
                <label for="filterCategoryMobile" class="form-label">Category</label>
                <select id="filterCategoryMobile" class="form-select filter-select-custom">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="filterBrandMobile" class="form-label">Brand</label>
                <select id="filterBrandMobile" class="form-select filter-select-custom">
                    <option value="">All Brands</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="pl-filter-sheet-foot">
            <button type="button" class="pl-filter-clear" id="plFilterClear">Clear</button>
            <button type="button" class="pl-filter-apply" id="plFilterApply">Apply</button>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Products</h4>
            </div>
            <div class="page-btn product-page-actions">
                <a href="{{ route('product.import') }}" class="product-import-btn">
                    <i class="fas fa-file-import me-1"></i> <span class="d-none d-sm-inline">Import Products</span><span class="d-sm-none">Import</span>
                </a>
                @if (app('hasPermission')(1, 'add'))
                    <a href="{{ route('product.add') }}" class="btn btn-sm btn-added product-new-btn">
                        <i class="fas fa-plus me-1"></i> New Product
                    </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="product-filter-wrap">
                    <div class="product-filter-box">
                        <div class="form-group product-filter-search">
                            <label for="productSearch" class="form-label mb-1">Search</label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="product-search-input flex-grow-1">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="productSearch" class="form-control form-control-sm" placeholder="Search...">
                                </div>
                                <button type="button" id="plFilterToggle" class="pl-filter-toggle" aria-label="Filters" aria-expanded="false">
                                    <i class="fas fa-filter"></i>
                                    <span class="pl-filter-count d-none" id="plFilterCount">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="form-group product-filter-select">
                            <label for="filterCategory" class="form-label mb-1">Category</label>
                            <select id="filterCategory" class="form-control form-control-sm">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group product-filter-select">
                            <label for="filterBrand" class="form-label mb-1">Brand</label>
                            <select id="filterBrand" class="form-control form-control-sm">
                                <option value="">All Brands</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="product-export-btns d-flex flex-wrap gap-2 col-12 col-md-auto">
                        <button id="exportAllChallan" class="btn btn-sm col-6 col-md-auto">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                        <button id="exportPdf" class="btn btn-sm col-6 col-md-auto">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </button>
                    </div>
                </div>


                <div class="table-scroll-top">
                    <div></div>
                </div>
                <div class="table-responsive">
                    <table class="table  datanew">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th class="details-column">Details</th>
                                <th>SKU</th>
                                <th>Category </th>
                                <th>Brand</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Quantity Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {

            $.fn.dataTable.ext.errMode = 'none';
            var authToken = localStorage.getItem("authToken");
            var table = $('.datanew').DataTable(); // Retrieve DataTable instance

            $('#filterCategory, #filterBrand').select2({
                width: '100%'
            });

            // Define the function FIRST
            function fetchProducts() {
                var search = $('#productSearch').val();
                var categoryId = $('#filterCategory').val();
                var brandId = $('#filterBrand').val();
                const subBranchId = localStorage.getItem('selectedSubAdminId');
                $.ajax({
                    url: "/api/getAllProduct",
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: {
                        search: search,
                        category_id: categoryId,
                        brand_id: brandId,
                        sub_branch_id: subBranchId
                    },
                    success: function(response) {
                        if (response.status) {
                            let products = response.data;
                            let currencySymbol = response.currencySymbol || '₹';
                            let currencyPosition = response.currencyPosition || 'left';
                            let tableBody = [];

                            let lowStockThreshold = response.lowStockThreshold || 0;
                            // Function to capitalize first letter of each word
                            function capitalizeWords(str) {
                                if (!str || str.trim() === '') return 'N/A';
                                return str.replace(/\b\w/g, function(char) {
                                    return char.toUpperCase();
                                });
                            }

                            function escapeHtmlText(str) {
                                if (str === null || str === undefined) return '';
                                return String(str)
                                    .replace(/&/g, '&amp;')
                                    .replace(/</g, '&lt;')
                                    .replace(/>/g, '&gt;')
                                    .replace(/"/g, '&quot;')
                                    .replace(/'/g, '&#039;');
                            }

                            $.each(products, function(index, product) {
                                let productName = capitalizeWords(product.name || 'N/A');
                                let sku = product.SKU || 'N/A';
                                let quantity = product.quantity ?? 'N/A';
                                let categoryName = capitalizeWords(product.category ? product.category.name : "N/A");
                                let brandName = capitalizeWords(product.brand ? product.brand.name : "N/A");
                                let qtyTypeName = capitalizeWords(product.quantity_type?.quantity_type ?? "N/A");                           

                                let detailsToggle = `
                                    <a href="#details-${product.id}" class="toggle-details d-md-none" data-bs-toggle="collapse">
                                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                    </a>
                                `;

                                let productImage = '{{ env('ImagePath') . 'admin/assets/img/product/noimage.png' }}';
                                const ImagePath = "{{ env('ImagePath') . 'storage/' }}";
                                if (product.images) {
                                    let imagesArray = typeof product.images === "string" ? JSON.parse(product.images) : product.images;
                                    if (Array.isArray(imagesArray) && imagesArray.length > 0) {
                                        productImage = `${ImagePath}${imagesArray[0]}`;
                                    }
                                }

                                let formattedPrice = 'N/A';
                                if (product.price !== undefined && product.price !== null && product.price !== '') {
                                    if (currencyPosition === 'left') {
                                        formattedPrice = currencySymbol + product.price;
                                    } else {
                                        formattedPrice = product.price + currencySymbol;
                                    }
                                }

                                let isLowStock = quantity !== 'N/A' && !isNaN(parseFloat(quantity)) && parseFloat(quantity) <= parseFloat(lowStockThreshold);

                                let quantityDisplay =
                                    `<span class="quantity-history" data-id="${product.id}" style="cursor:pointer; border: 1px solid #ececec; padding: 4px 8px; border-radius: 4px; display: inline-block;">${quantity}</span>`;
                                if (isLowStock) {
                                    quantityDisplay += ` <span class="badge bg-danger ms-1">Low Stock</span>`;
                                }

                                let actionDropdownHtml = `
                                    <div class="dropdown action-dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #e2e8f0; background: #fff; color: #64748b; border-radius: 6px; padding: 4px 12px; transition: all 0.2s;">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="border: none; border-radius: 12px; min-width: 160px; padding: 8px;">
                                            @if (app('hasPermission')(1, 'view')) 
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center" href="/inventory-View/${product.id}" style="border-radius: 8px; padding: 8px 12px; font-size: 14px; font-weight: 500; color: #475569; transition: all 0.2s;">
                                                    <i class="fa-solid fa-clock-rotate-left me-3" style="font-size: 14px; color: #94a3b8; width: 16px;"></i> History
                                                </a>
                                            </li>
                                            @endif
                                            @if (app('hasPermission')(1, 'view')) 
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center" href="/product-view/${product.id}" style="border-radius: 8px; padding: 8px 12px; font-size: 14px; font-weight: 500; color: #475569; transition: all 0.2s;">
                                                    <i class="fa-regular fa-eye me-3" style="font-size: 14px; color: #94a3b8; width: 16px;"></i> View
                                                </a>
                                            </li>
                                            @endif
                                            @if (app('hasPermission')(1, 'edit'))
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center" href="/edit-product/${product.id}" style="border-radius: 8px; padding: 8px 12px; font-size: 14px; font-weight: 500; color: #475569; transition: all 0.2s;">
                                                    <i class="fa-regular fa-pen-to-square me-3" style="font-size: 14px; color: #94a3b8; width: 16px;"></i> Edit
                                                </a>
                                            </li>
                                            @endif
                                            @if (app('hasPermission')(1, 'delete'))
                                            <li>
                                                <a class="dropdown-item confirm-text delete-product d-flex align-items-center" data-id="${product.id}" href="javascript:void(0);" style="border-radius: 8px; padding: 8px 12px; font-size: 14px; font-weight: 500; color: #ef4444; transition: all 0.2s;">
                                                    <i class="fa-regular fa-trash-can me-3" style="font-size: 14px; color: #ef4444; width: 16px;"></i> Delete
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                `;

                                let mobileCard = `
                                    <div class="pl-m-card">
                                        <a href="/product-view/${product.id}" class="pl-m-img">
                                            <img src="${productImage}" alt="" loading="lazy">
                                        </a>
                                        <div class="pl-m-body">
                                            <div class="pl-m-top">
                                                <a href="/product-view/${product.id}" class="pl-m-name">${escapeHtmlText(productName)}</a>
                                                <span class="pl-m-amount">${formattedPrice}</span>
                                            </div>
                                            <div class="pl-m-badges">
                                                <span class="pl-m-badge ${isLowStock ? 'is-low' : 'is-ok'}">${isLowStock ? 'LOW STOCK' : 'IN STOCK'}</span>
                                                ${sku !== 'N/A' && sku ? `<span class="pl-m-code">${escapeHtmlText(sku)}</span>` : ''}
                                            </div>
                                        </div>
                                        <div class="pl-m-foot">
                                            <div class="pl-m-stat quantity-history" data-id="${product.id}"><span>Qty</span><strong class="${isLowStock ? 'text-danger' : ''}">${quantity}</strong></div>
                                            ${brandName && brandName !== 'N/A' ? `<div class="pl-m-stat"><span>Brand</span><strong>${escapeHtmlText(brandName)}</strong></div>` : ''}
                                            <div class="pl-m-stat pl-m-cat"><span>Category</span><strong>${escapeHtmlText(categoryName)}</strong></div>
                                            <div class="pl-m-acts">
                                                ${actionDropdownHtml}
                                            </div>
                                        </div>
                                    </div>
                                `;

                                let desktopHead = `
                                    <div class="pl-desktop-head" style="display: flex; align-items: center;">
                                        <a href="/product-view/${product.id}" class="product-img mb-1">
                                            <img src="${productImage}" alt="product" style="max-width: 60px;">
                                        </a>
                                        <a href="/product-view/${product.id}" style="color: #1b2850; font-weight: 500; text-align: center; margin-left: 8px;">
                                            ${escapeHtmlText(productName)}
                                        </a>
                                    </div>
                                `;

                                tableBody.push([
                                    `${mobileCard}${desktopHead}`,
                                    detailsToggle,
                                    sku,
                                    categoryName,
                                    brandName,
                                    formattedPrice,
                                    quantityDisplay,
                                    qtyTypeName,
                                    actionDropdownHtml
                                ]);
                            });
                            table.clear().rows.add(tableBody).draw();

                            // Sync top scrollbar
                            const topScroll = document.querySelector('.table-scroll-top');
                            const tableResponsive = document.querySelector('.table-responsive');
                            const tableElement = document.querySelector('.datanew');

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

                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html('<tr><td colspan="8">No products found</td></tr>');
                        }
                    },
                    error: function() {
                        alert("Error fetching products.");
                    }
                });
            }
            $(document).on('click', '.quantity-history', function() {
                const productId = $(this).data('id');

                $.ajax({
                    url: `/api/product-quantity-history/${productId}`,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(res) {
                        if (res.status) {
                            let content = `<h5>${res.product}</h5><ul class="list-group">`;
                            res.history.forEach(item => {
                                content += `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${item.type}</strong> - ${item.note}<br>
                                                <small>${item.date}</small>
                                            </div>
                                        <span class="badge bg-${item.type === 'Added' ? 'success' : 'danger'} px-4 py-2 small">${item.quantity}</span>
                                        </li>
                                    `;
                            });
                            content += `</ul>`;
                            $('#quantityHistoryContent').html(content);
                            $('#quantityHistoryModal').modal('show');
                        } else {
                            alert(res.message || "Failed to load quantity history.");
                        }
                    },
                    error: function() {
                        alert("Error fetching quantity history.");
                    }
                });
            });

            // Mobile Filter Bottom Sheet handling (matching Dolphin ERP)
            (function initMobileFilterSheet() {
                const sheet = document.getElementById('plFilterSheet');
                const toggle = document.getElementById('plFilterToggle');
                if (!sheet || !toggle) return;

                function openSheet() {
                    $('#filterCategoryMobile').val($('#filterCategory').val() || '');
                    $('#filterBrandMobile').val($('#filterBrand').val() || '');
                    document.body.classList.add('pl-filter-open');
                    toggle.setAttribute('aria-expanded', 'true');
                }

                function closeSheet() {
                    document.body.classList.remove('pl-filter-open');
                    toggle.setAttribute('aria-expanded', 'false');
                }

                function updateCount() {
                    const n = ['#filterCategory', '#filterBrand'].filter(function(sel) {
                        return !!$(sel).val();
                    }).length;
                    $('#plFilterCount').text(n).toggleClass('d-none', n === 0);
                    $(toggle).toggleClass('has-active', n > 0);
                }

                toggle.addEventListener('click', openSheet);
                ['plFilterSheetClose', 'plFilterBackdrop'].forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener('click', closeSheet);
                });

                $('#plFilterApply').on('click', function() {
                    let catVal = $('#filterCategoryMobile').val() || '';
                    let brandVal = $('#filterBrandMobile').val() || '';
                    $('#filterCategory').val(catVal).trigger('change');
                    $('#filterBrand').val(brandVal).trigger('change');
                    updateCount();
                    closeSheet();
                });

                $('#plFilterClear').on('click', function() {
                    $('#filterCategoryMobile').val('');
                    $('#filterBrandMobile').val('');
                    $('#filterCategory').val('').trigger('change');
                    $('#filterBrand').val('').trigger('change');
                    updateCount();
                    closeSheet();
                });

                $('#filterCategory, #filterBrand').on('change', function() {
                    updateCount();
                });

                updateCount();
            })();

            // Initial fetch
            fetchProducts();

            // Filters: bind change events AFTER function is defined
            $('#filterCategory, #filterBrand, #filterSubBranch').on('change', function() {
                fetchProducts();
            });

            let productSearchTimer = null;
            $('#productSearch').on('input', function() {
                clearTimeout(productSearchTimer);
                productSearchTimer = setTimeout(fetchProducts, 350);
            });
        })


        $(document).on('click', '.delete-product', function() {
            var productId = $(this).data('id'); // Get product ID
            var authToken = localStorage.getItem("authToken");
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonColor: "#ff9f43", // Confirm button color (orange)
                cancelButtonColor: "#6c757d", // Cancel button color (gray)
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/api/deleteProduct/${productId}`, // Adjust based on your route
                        type: 'POST',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        data: {
                            _token: $('meta[name="csrf-token"]').attr(
                                'content') // CSRF token for security
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43",
                                    confirmButtonText: "OK"
                                });

                                $(`.delete-product[data-id="${productId}"]`).closest('tr')
                                    .remove();
                            } else {
                                // Use response.error here, fallback to response.message just in case
                                Swal.fire("Error!", response.error || response.message ||
                                    "Unknown error", "error");
                            }
                        },

                        error: function(xhr) {
                            // Parse the JSON error message sent by API in HTTP error response
                            let message = "Something went wrong!";
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                message = xhr.responseJSON.error;
                            }
                            // Swal.fire("Error!", message, "error");
                            Swal.fire({
                                title: "Error!",
                                text: message,
                                icon: "error",
                                confirmButtonColor: "#ff9f43", // custom orange color
                                confirmButtonText: "OK"
                            });

                        }
                    });
                }
            });
        });

      $('#exportAllChallan').click(function () {
    let selectedSearch = $('#productSearch').val() || '';
    let selectedCategory = $('#filterCategory').val() || '';
    let selectedBrand = $('#filterBrand').val() || '';
    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
    let authToken = localStorage.getItem("authToken");

    let url = `/api/export-product?search=${encodeURIComponent(selectedSearch)}&category_id=${selectedCategory}&brand_id=${selectedBrand}&selectedSubAdminId=${selectedSubAdminId}`;

    $.ajax({
        url: url,
        method: "GET",
        headers: {
            "Authorization": "Bearer " + authToken
        },

        beforeSend: function () {
            Swal.fire({
                title: "Exporting Excel...",
                text: "Please wait while we generate your file.",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        },

        success: function (response) {
            Swal.close();

            if (response.status && response.file_url) {
                Swal.fire({
                    icon: "success",
                    title: "Excel Exported!",
                    text: "Click the button below to download your Excel file.",
                    showConfirmButton: true,
                    confirmButtonText: "Download File"
                }).then(() => {
                    let link = document.createElement('a');
                    link.href = response.file_url;
                    link.download = response.file_name || "Products.xlsx";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Export Failed!",
                    text: "Unable to export Excel file. Please try again."
                });
            }
        },

        error: function (xhr, status, error) {
            Swal.close();
            console.error("Export failed:", error);

            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Export failed. Please try again."
            });
        }
    });
});


        $('#exportPdf').click(function() {
            let selectedSearch = $('#productSearch').val() || '';
            let selectedCategory = $('#filterCategory').val() || '';
            let selectedBrand = $('#filterBrand').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url =
                `/api/export-product-pdf?search=${encodeURIComponent(selectedSearch)}&category_id=${selectedCategory}&brand_id=${selectedBrand}&selectedSubAdminId=${selectedSubAdminId}`;

            $.ajax({
                url: url,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },

                beforeSend: function() {
                    Swal.fire({
                        title: "Generating PDF...",
                        text: "Please wait",
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },

                success: function(response) {
                    Swal.close();

                    if (response.status && response.file_url) {
                        Swal.fire({
                            icon: "success",
                            title: "PDF Generated Successfully!",
                            text: "Your product PDF is ready.",
                            showConfirmButton: true,
                            confirmButtonText: "Download PDF"
                        }).then(() => {
                            let link = document.createElement('a');
                            link.href = response.file_url;
                            link.download = response.file_name || "products.pdf";
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Failed!",
                            text: "Could not generate the PDF. Please try again."
                        });
                    }
                },

                error: function(xhr, status, error) {
                    Swal.close();
                    console.error("Export PDF failed:", error);

                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Export PDF failed. Please try again."
                    });
                }
            });
        });


        $(document).on('click', '.toggle-details', function() {
            let icon = $(this).find('i');
            if (icon.hasClass('fa-plus-circle')) {
                icon.removeClass('fa-plus-circle')
                    .addClass('fa-minus-circle')
                    .css('color', 'red'); // or any color you want for minus icon
            } else {
                icon.removeClass('fa-minus-circle')
                    .addClass('fa-plus-circle')
                    .css('color', '#ff9f43'); // your desired orange color
            }
        });
    </script>
@endpush
