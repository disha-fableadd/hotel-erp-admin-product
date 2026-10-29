@extends('layout.app')

@section('title', 'Tables View')

@section('content')
    <style>
        /* --- Shared modal base --- */
        .modal-content {
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.28) !important;
            margin-top: 0rem !important;
            overflow: hidden;
        }

        .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.1rem 1.35rem;
        }

        .modal-header .modal-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.15rem;
        }

        .modal-body {
            background: #fff;
            padding: 1.25rem;
        }

        .modal-lg {
            max-width: 1150px !important;
        }

        .modal-xl {
            max-width: 1200px !important;
        }

        /* ===== Table Order Modal (POS) ===== */
        #tableOrderModal .modal-dialog {
            max-width: 1200px !important;
            margin: 1rem auto;
        }

        #tableOrderModal .modal-content {
            border-radius: 18px !important;
            background: #ffffff;
            max-height: calc(100vh - 2rem);
        }

        #tableOrderModal .modal-header {
            background: #ffffff;
            border-bottom: 1px solid #eef2f7;
            padding: 0.9rem 1.15rem;
            flex-shrink: 0;
        }

        #tableOrderModal .table-order-kicker {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #ff9f43;
            line-height: 1.2;
        }

        #tableOrderModal .modal-body {
            background: #f4f6f9;
            padding: 12px !important;
            overflow: hidden;
        }

        #tableOrderModal .table-order-layout {
            align-items: stretch;
            margin: 0 !important;
            height: min(78vh, 720px);
        }

        #tableOrderModal .table-order-layout > [class*="col-"] {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        #tableOrderModal .tabs_wrapper {
            background: #ffffff;
            border: 1px solid #e8edf3;
            border-radius: 14px;
            padding: 12px;
            min-height: 0 !important;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #tableOrderModal .table-order-menu-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            margin: 0 0 10px !important;
            padding: 0 0 10px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        #tableOrderModal .tabs_wrapper ul.tabs {
            gap: 8px;
            padding: 0 !important;
            margin: 0 !important;
            border-bottom: none !important;
            flex: 1 1 auto;
            min-width: 0;
        }

        #tableOrderModal .table-order-product-search {
            position: relative;
            flex: 0 0 210px;
            max-width: 42%;
        }

        #tableOrderModal .table-order-product-search input {
            height: 36px;
            font-size: 13px;
            font-weight: 500;
            border: 1.5px solid #e2e8f0;
            border-radius: 999px;
            padding: 0 34px 0 36px;
            background: #f8fafc;
            color: #334155;
            box-shadow: none;
        }

        #tableOrderModal .table-order-product-search input:focus {
            background: #fff;
            border-color: #ff9f43;
            box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.15);
            outline: none;
        }

        #tableOrderModal .table-order-product-search .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            opacity: 0.55;
            pointer-events: none;
        }

        #tableOrderModal .table-order-product-search .clear-product-search {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #94a3b8;
            font-size: 16px;
            line-height: 1;
            padding: 0;
            width: 20px;
            height: 20px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        #tableOrderModal .table-order-product-search.has-value .clear-product-search {
            display: inline-flex;
        }

        #tableOrderModal .product-search-empty {
            display: none;
            width: 100%;
            text-align: center;
            color: #64748b;
            font-size: 13px;
            padding: 28px 12px;
        }

        #tableOrderModal .tabs_container {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
            padding-right: 2px;
        }

        #tableOrderModal .tabs_container .tab_content {
            display: none;
        }

        #tableOrderModal .tabs_container .tab_content.active {
            display: block;
        }

        #tableOrderModal .tabs_container.is-searching {
            display: none;
        }

        #tableOrderModal #tableProductSearchResults {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
            padding-right: 2px;
            display: none;
        }

        #tableOrderModal #tableProductSearchResults.is-visible {
            display: block;
        }

        #tableOrderModal .product-card-col.product-search-hidden {
            display: none !important;
        }

        #tableOrderModal .product-details {
            background: #f8fafc;
            padding: 7px 14px;
            border-radius: 999px;
            border: 1px solid #e2e8f0 !important;
            box-shadow: none;
        }

        #tableOrderModal .product-details h6 {
            font-size: 12.5px !important;
            font-weight: 700 !important;
            color: #475569 !important;
        }

        #tableOrderModal .tabs_wrapper ul.tabs li.active .product-details {
            background: linear-gradient(135deg, #ff9f43 0%, #ff821c 100%);
            border-color: transparent !important;
            box-shadow: 0 6px 14px -8px rgba(255, 159, 67, 0.75);
        }

        #tableOrderModal .tabs_wrapper ul.tabs li.active .product-details h6 {
            color: #fff !important;
        }

        #tableOrderModal .tabs_container .row,
        #tableOrderModal #tableProductSearchResults .row {
            --bs-gutter-x: 0.65rem;
            --bs-gutter-y: 0.65rem;
        }

        #tableOrderModal .productset {
            background: #fff;
            border: 1px solid #e8edf3;
            border-radius: 12px;
            padding: 10px 8px 12px;
            margin-bottom: 0;
            box-shadow: none;
            height: 100%;
            transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        #tableOrderModal .productset:hover {
            transform: translateY(-2px);
            border-color: #fdba74;
            box-shadow: 0 10px 20px -16px rgba(15, 23, 42, 0.35);
        }

        #tableOrderModal .productsetimgin {
            height: 64px !important;
            margin: 0 auto 6px !important;
            background: #f8fafc;
            border-radius: 10px;
            object-fit: contain;
            max-width: 100%;
        }

        #tableOrderModal .productsetcontent {
            padding: 0 !important;
        }

        #tableOrderModal .productsetcontent h4 {
            font-size: 13px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin-bottom: 2px !important;
        }

        #tableOrderModal .productsetcontent h6 {
            font-size: 13.5px !important;
            font-weight: 800 !important;
            color: #ff9f43 !important;
        }

        #tableOrderModal .card-order {
            background: #ffffff;
            border: 1px solid #e8edf3;
            border-radius: 14px;
            box-shadow: none;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin: 0;
        }

        #tableOrderModal .card-order > .card-body.body_space {
            padding: 12px !important;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        #tableOrderModal .order-panel-title {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 8px;
            flex-shrink: 0;
        }

        #tableOrderModal .gst-header {
            margin-bottom: 10px !important;
            flex-shrink: 0;
        }

        #tableOrderModal .custom-gst-pill-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            background: #f1f5f9;
            border-radius: 10px;
            padding: 3px;
        }

        #tableOrderModal .custom-gst-pill {
            margin: 0;
            cursor: pointer;
        }

        #tableOrderModal .custom-gst-pill span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 8px 6px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            background: transparent;
            border: 0;
            transition: all 0.15s ease;
        }

        #tableOrderModal .custom-gst-pill input[type="radio"]:checked + span {
            background: #fff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
        }

        #tableOrderModal .order-scroll {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding-right: 2px;
        }

        #tableOrderModal .totalitem {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        #tableOrderModal .totalitem h4 {
            margin: 0;
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
        }

        #tableOrderModal .clear_items {
            font-size: 12px;
            font-weight: 700;
            color: #ef4444 !important;
            text-decoration: none;
        }

        #tableOrderModal .product-table {
            min-height: 70px !important;
            max-height: none !important;
            height: auto !important;
            overflow: visible !important;
            margin-bottom: 8px;
        }

        #tableOrderModal .product-lists {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            list-style: none;
            margin: 0 0 8px;
            padding: 8px 10px;
            background: #f8fafc;
            border: 1px solid #e8edf3;
            border-radius: 10px;
        }

        #tableOrderModal .select-group label {
            font-size: 11.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 3px;
        }

        #tableOrderModal #discount {
            border-radius: 9px;
            border: 1px solid #e2e8f0;
            height: 36px;
            font-size: 13px;
            padding: 6px 10px;
        }

        #tableOrderModal .error_discount {
            font-size: 11px;
            display: block;
            margin-top: 3px;
        }

        #tableOrderModal .setvalue {
            background: #f8fafc;
            border: 1px solid #e8edf3;
            border-radius: 10px;
            padding: 8px 10px;
            margin: 8px 0 0;
        }

        #tableOrderModal .setvalue ul {
            list-style: none;
            margin: 0;
            padding: 0;
            background: transparent;
            border: 0;
        }

        #tableOrderModal .setvalue ul li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 5px 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        #tableOrderModal .setvalue ul li:last-child,
        #tableOrderModal .setvalue ul li.error-message-container {
            border-bottom: 0;
        }

        #tableOrderModal .setvalue ul li h5 {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        #tableOrderModal .setvalue ul li h6 {
            margin: 0;
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
        }

        #tableOrderModal .setvalue ul li.total-value {
            margin-top: 2px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 0;
        }

        #tableOrderModal .setvalue ul li.total-value h5,
        #tableOrderModal .setvalue ul li.total-value h6 {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }

        #tableOrderModal .setvalue ul li.total-value h6 {
            color: #ff9f43;
        }

        #tableOrderModal .payment_panel {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eef2f7;
            flex-shrink: 0;
            background: #fff;
            position: relative !important;
            width: 100% !important;
            bottom: auto !important;
            z-index: 2;
        }

        #tableOrderModal .setvaluecash {
            margin-bottom: 12px;
        }

        #tableOrderModal .setvaluecash::before {
            content: "Payment Method";
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #64748b;
            margin-bottom: 8px;
        }

        #tableOrderModal .setvaluecash ul {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px !important;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        #tableOrderModal .setvaluecash ul li {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-width: 0 !important;
        }

        #tableOrderModal .setvaluecash ul li a,
        #tableOrderModal .setvaluecash ul li a.paymentmethod {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 10px !important;
            width: 100% !important;
            height: 44px !important;
            min-height: 44px !important;
            border-radius: 10px !important;
            border: 1.5px solid #e2e8f0 !important;
            background: #ffffff !important;
            color: #0f172a !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
            padding: 0 12px !important;
            text-decoration: none !important;
            text-align: left !important;
            box-shadow: none !important;
            transform: none !important;
            box-sizing: border-box !important;
            white-space: nowrap !important;
            overflow: visible !important;
        }

        #tableOrderModal .setvaluecash ul li a.paymentmethod i,
        #tableOrderModal .setvaluecash ul li a.paymentmethod svg {
            font-size: 14px !important;
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            border-radius: 8px;
            background: #f1f5f9;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            flex-shrink: 0 !important;
            color: #475569 !important;
            fill: currentColor !important;
            margin: 0 !important;
        }

        #tableOrderModal .setvaluecash ul li a.paymentmethod span {
            display: inline-block !important;
            max-width: none !important;
            overflow: visible !important;
            text-overflow: unset !important;
            white-space: nowrap !important;
            font-size: 12.5px !important;
            font-weight: 700 !important;
            color: inherit !important;
        }

        #tableOrderModal .setvaluecash ul li a.paymentmethod:hover {
            border-color: #fdba74 !important;
            background: #fff7ed !important;
            color: #9a3412 !important;
            transform: none !important;
        }

        #tableOrderModal .setvaluecash ul li a.paymentmethod:hover i {
            background: #ffedd5 !important;
            color: #c2410c !important;
        }

        #tableOrderModal .setvaluecash ul li a.paymentmethod.active {
            background: #fff7ed !important;
            border-color: #ff9f43 !important;
            color: #9a3412 !important;
            box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.15) !important;
            transform: none !important;
        }

        #tableOrderModal .setvaluecash ul li a.paymentmethod.active i,
        #tableOrderModal .setvaluecash ul li a.paymentmethod.active span {
            color: #c2410c !important;
        }

        #tableOrderModal .setvaluecash ul li a.paymentmethod.active i {
            background: #ff9f43 !important;
            color: #ffffff !important;
        }

        #tableOrderModal .setvaluecash ul li a.paymentmethod.active svg {
            fill: #c2410c !important;
        }

        #tableOrderModal .btn-totallabel,
        #tableOrderModal .btn-totallabel_1,
        #tableOrderModal .btn-totallabel_2 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            width: 100% !important;
            margin: 0 !important;
            padding: 12px 14px !important;
            border-radius: 11px !important;
            background: linear-gradient(135deg, #ff9f43 0%, #ff821c 100%) !important;
            color: #fff !important;
            border: 0 !important;
            cursor: pointer;
            box-shadow: 0 8px 18px -10px rgba(255, 159, 67, 0.85) !important;
        }

        #tableOrderModal .btn-totallabel h6,
        #tableOrderModal .btn-totallabel_1 h6,
        #tableOrderModal .btn-totallabel_2 h6,
        #tableOrderModal .btn-totallabel h5,
        #tableOrderModal .btn-totallabel_1 h5,
        #tableOrderModal .btn-totallabel_2 h5 {
            margin: 0 !important;
            color: #fff !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            white-space: nowrap;
        }

        #tableOrderModal .btn-totallabel_1 h6,
        #tableOrderModal .btn-totallabel_2 h6 {
            font-size: 12.5px !important;
        }

        #tableOrderModal .modal-order-edit {
            display: flex;
            gap: 8px !important;
            margin-top: 0 !important;
        }

        #tableOrderModal .btn-totallabel_1 {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
            box-shadow: 0 8px 18px -10px rgba(15, 23, 42, 0.55) !important;
        }

        #tableOrderModal .btn-totallabel_2 {
            background: linear-gradient(135deg, #ff9f43 0%, #ff821c 100%) !important;
        }

        @media (max-width: 991px) {
            #tableOrderModal .table-order-layout {
                height: auto;
                max-height: none;
            }

            #tableOrderModal .table-order-layout > [class*="col-"] {
                height: auto;
            }

            #tableOrderModal .tabs_wrapper {
                min-height: 280px;
                max-height: 360px;
            }

            #tableOrderModal .table-order-menu-toolbar {
                flex-wrap: wrap;
            }

            #tableOrderModal .table-order-product-search {
                flex: 1 1 100%;
                max-width: 100%;
            }

            #tableOrderModal .modal-body {
                overflow-y: auto;
            }
        }

        /* Mobile Responsive Table for Order List Modal */
        @media (max-width: 767px) {
            #orderListModal .table-responsive {
                overflow-x: hidden !important;
            }
            #orderListModal .table {
                table-layout: fixed !important;
                width: 100% !important;
            }
            #orderListModal .table thead th:first-child {
                white-space: nowrap !important;
                width: calc(100% - 60px) !important;
            }
            #orderListModal .table tbody td:first-child {
                word-wrap: break-word;
                white-space: normal;
                width: calc(100% - 60px) !important;
            }
            #orderListModal .table thead th:nth-child(n+3),
            #orderListModal .table tbody td:nth-child(n+3) {
                display: none !important;
            }
            #orderListModal .table thead th.details-column-modal,
            #orderListModal .table tbody td:nth-child(2) {
                display: table-cell !important;
                width: 50px;
                text-align: center;
                vertical-align: middle;
            }
            .toggle-details i {
                font-size: 18px;
                color: #ff9f43;
            }
            .mobile-details-card {
                background: #f8f9fa;
                border-radius: 6px;
                padding: 10px;
                margin-top: 8px;
                font-size: 14px;
                text-align: left;
                box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
            }
        }
        @media (min-width: 768px) {
            #orderListModal .table thead th.details-column-modal,
            #orderListModal .table tbody td:nth-child(2) {
                display: none !important;
            }
        }

        /* =========================================================
           HOTEL POS THEME VARIABLES & CORE STYLES
           ========================================================= */
        :root {
            --pos-primary: #ff9f43;
            --pos-primary-hover: #ff8510;
            --pos-primary-light: #fffaf5;
            --pos-primary-soft: #fff5eb;
            --pos-dark: #1b2850;
            --pos-slate: #0f172a;
            --pos-body: #334155;
            --pos-muted: #64748b;
            --pos-border: #e2e8f0;
            --pos-bg-card: #ffffff;
            --pos-bg-subtle: #f8fafc;
        }

        .disabled-product {
            opacity: 0.55;
            pointer-events: none;
            filter: grayscale(0.6);
        }

        /* --- Category Tabs (Premium Pill Design) --- */
        .tabs_wrapper {
            overflow: visible;
        }
        .tabs_wrapper ul.tabs {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: visible;
            gap: 10px;
            padding: 4px 2px 14px 2px;
            margin: 0 0 16px 0;
            list-style: none;
            scrollbar-width: thin;
            scrollbar-color: var(--pos-primary) #f1f5f9;
        }
        .tabs_wrapper ul.tabs::-webkit-scrollbar {
            height: 4px;
            display: block;
        }
        .tabs_wrapper ul.tabs::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 99px;
        }
        .tabs_wrapper ul.tabs::-webkit-scrollbar-thumb {
            background: var(--pos-primary);
            border-radius: 99px;
        }
        .tabs_wrapper ul.tabs li {
            flex: 0 0 auto;
            cursor: pointer;
            transition: transform 0.2s ease;
            width: max-content !important;
        }
        .tabs_wrapper ul.tabs li:active {
            transform: scale(0.96);
        }
        .product-details {
            background: #ffffff;
            padding: 8px 20px;
            border-radius: 30px;
            border: 1.5px solid var(--pos-border) !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            width: max-content !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        .product-details h6 {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--pos-slate) !important;
            margin: 0;
            white-space: nowrap;
            letter-spacing: 0.2px;
        }
        .tabs_wrapper ul.tabs li:hover .product-details {
            border-color: var(--pos-primary) !important;
            color: var(--pos-primary);
            background: var(--pos-primary-light);
            transform: translateY(-2px);
        }
        .tabs_wrapper ul.tabs li.active .product-details {
            background: linear-gradient(135deg, #ff9f43 0%, #ff8510 100%) !important;
            border-color: transparent !important;
            box-shadow: 0 4px 12px rgba(255, 159, 67, 0.35);
        }
        .tabs_wrapper ul.tabs li.active .product-details h6 {
            color: #ffffff !important;
        }

        /* --- Product Card (Modern Hotel Food Card Design) --- */
        .productset {
            background: #ffffff;
            border: 1.5px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 10px;
            margin-bottom: 16px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            text-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .productset:hover {
            border-color: var(--pos-primary);
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(255, 159, 67, 0.18);
        }
        .productset.active {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 2px rgba(255, 159, 67, 0.25);
        }
        .productset .productsetimg {
            background: #f8fafc;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            min-height: 90px;
        }
        .productsetimgin {
            max-height: 75px;
            max-width: 100%;
            object-fit: contain;
            transition: transform 0.25s ease;
            margin: 0 auto;
            display: block;
        }
        .productset:hover .productsetimgin {
            transform: scale(1.06);
        }
        .productset .productsetimg h6 {
            position: absolute;
            bottom: 4px;
            right: 6px;
            font-size: 10px;
            font-weight: 700;
            color: var(--pos-muted);
            background: rgba(255, 255, 255, 0.85);
            padding: 1px 5px;
            border-radius: 4px;
            margin: 0;
        }
        .productsetcontent h4 {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--pos-slate);
            margin: 4px 0 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .product-category-subtitle {
            font-size: 11px;
            font-weight: 500;
            color: var(--pos-muted);
            text-transform: capitalize;
            margin-bottom: 4px;
        }
        .productsetcontent h6 {
            font-size: 14.5px;
            font-weight: 800;
            color: var(--pos-primary);
            margin: 0;
        }
        .productsetbtn button {
            display: none;
        }

        /* --- Order Panel --- */
        .card-order {
            background: #ffffff;
            border: 1px solid var(--pos-border);
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .card-order .card-body {
            padding: 1.25rem;
        }

        /* Segmented GST Pill Switch */
        .custom-gst-pill-group {
            display: inline-flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 99px;
            gap: 4px;
            border: 1px solid var(--pos-border);
        }
        .custom-gst-pill {
            position: relative;
            margin: 0;
            cursor: pointer;
        }
        .custom-gst-pill input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .custom-gst-pill span {
            display: inline-block;
            padding: 6px 16px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--pos-muted);
            border-radius: 99px;
            transition: all 0.2s ease;
            user-select: none;
        }
        .custom-gst-pill input[type="radio"]:checked + span {
            background: #ffffff;
            color: var(--pos-primary);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            font-weight: 700;
        }

        /* Total Items Header */
        .totalitem {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            margin-bottom: 8px;
            border-bottom: 1px dashed var(--pos-border);
        }
        .totalitem h4 {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--pos-slate);
            margin: 0;
        }
        .totalitem .clear_items {
            font-size: 12px;
            font-weight: 600;
            color: #ef4444;
            text-decoration: none;
            padding: 3px 10px;
            border-radius: 6px;
            background: #fee2e2;
            transition: all 0.2s ease;
        }
        .totalitem .clear_items:hover {
            background: #fecaca;
            color: #dc2626;
        }

        /* Cart Table & Lists */
        .product-table {
            max-height: 220px;
            overflow-y: auto;
            padding-right: 4px;
            margin-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: var(--pos-primary) #f1f5f9;
        }
        .product-lists {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            margin-bottom: 8px;
            background: #ffffff;
            border: 1px solid var(--pos-border);
            border-radius: 10px;
            list-style: none;
            transition: all 0.2s ease;
            gap: 8px;
        }
        .product-lists:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }
        .product-lists > li:first-child {
            flex: 1;
            min-width: 0;
        }
        .product-lists .productimg {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .product-lists .productimgs img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
        }
        .product-lists .productcontet h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--pos-slate);
            margin: 0 0 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .product-lists .increment-decrement .input-groups {
            display: inline-flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--pos-border);
            border-radius: 6px;
            overflow: hidden;
        }
        .product-lists .increment-decrement .button {
            width: 22px;
            height: 22px;
            border: none;
            background: transparent;
            color: var(--pos-slate);
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .product-lists .increment-decrement .button:hover {
            background: var(--pos-primary);
            color: #ffffff;
        }
        .product-lists .increment-decrement .quantity-field {
            width: 30px;
            height: 22px;
            border: none;
            background: transparent;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--pos-slate);
        }
        .product-lists li.quantity_type {
            font-size: 11px;
            font-weight: 600;
            color: var(--pos-muted);
            background: #f1f5f9;
            padding: 3px 6px;
            border-radius: 4px;
        }
        .product-lists li.price {
            font-size: 13px;
            font-weight: 700;
            color: var(--pos-primary);
            min-width: 50px;
            text-align: right;
        }
        .product-lists .remove-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: #fee2e2;
            color: #ef4444;
            transition: all 0.2s ease;
        }
        .product-lists .remove-item:hover {
            background: #ef4444;
            color: #ffffff;
        }
        .product-lists .remove-item img {
            width: 13px;
            height: 13px;
        }

        /* Discount Input */
        .select-split .select-group label {
            font-size: 12px;
            font-weight: 700;
            color: var(--pos-slate);
            margin-bottom: 3px;
            display: block;
        }
        .select-split .select-group .form-control {
            border: 1.5px solid var(--pos-border);
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        .select-split .select-group .form-control:focus {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.2);
        }

        /* Total Section Button */
        .btn-total-amount {
            background: linear-gradient(135deg, #ff9f43 0%, #ff821c 100%) !important;
            color: white !important;
            border: none;
            border-radius: 12px;
            padding: 16px;
            width: 100%;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 159, 67, 0.3);
        }
        .btn-total-amount:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 159, 67, 0.4);
        }

        /* Pricing Summary */
        .setvalue {
            background: #f8fafc;
            border-radius: 10px;
            padding: 10px 12px;
            border: 1px solid var(--pos-border);
            margin: 8px 0 12px;
        }
        .setvalue ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .setvalue ul li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 12.5px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .setvalue ul li:last-child {
            border-bottom: none;
        }
        .setvalue ul li h5 {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--pos-muted);
            margin: 0;
        }
        .setvalue ul li h6 {
            font-size: 13px;
            font-weight: 700;
            color: var(--pos-slate);
            margin: 0;
        }
        .setvalue ul li.total-value {
            border-top: 1.5px solid var(--pos-border);
            border-bottom: none;
            margin-top: 5px;
            padding-top: 6px;
        }
        .setvalue ul li.total-value h5 {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--pos-slate);
        }
        .setvalue ul li.total-value h6 {
            font-size: 15px;
            font-weight: 800;
            color: var(--pos-primary);
        }

        /* Payment Methods Grid */
        .payment_panel {
            bottom: 0;
            background-color: white;
            z-index: 10;
        }
        .setvaluecash ul {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .setvaluecash ul li {
            margin: 0;
            padding: 0;
        }
        .setvaluecash ul li a.paymentmethod {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            border: 1.5px solid var(--pos-border);
            background: #ffffff;
            color: var(--pos-slate);
            font-size: 11px;
            font-weight: 700;
            min-height: 54px;
            border-radius: 8px;
            padding: 6px 4px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        .setvaluecash ul li a.paymentmethod:hover {
            border-color: var(--pos-primary);
            background: var(--pos-primary-light);
            color: var(--pos-primary);
            transform: translateY(-2px);
        }
        .setvaluecash ul li a.paymentmethod.active {
            background: var(--pos-dark) !important;
            border-color: var(--pos-dark) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(27, 40, 80, 0.3);
        }
        .setvaluecash ul li a.paymentmethod.active i {
            color: #ffffff !important;
        }
        .setvaluecash ul li a.paymentmethod.active svg {
            fill: #ffffff !important;
        }

        /* Action Buttons */
        .btn-totallabel {
            background: linear-gradient(135deg, #ff9f43 0%, #ff8510 100%) !important;
            color: #ffffff !important;
            border: none;
            border-radius: 12px;
            padding: 13px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            margin-top: 12px;
            box-shadow: 0 4px 14px rgba(255, 159, 67, 0.35);
            transition: all 0.25s ease;
        }
        .btn-totallabel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 159, 67, 0.45);
        }
        .btn-totallabel h6 {
            color: #ffffff !important;
            font-size: 14.5px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.3px;
        }
        .btn-totallabel h5 {
            color: #ffffff !important;
            margin: 0;
            display: flex;
            align-items: center;
            font-size: 16px;
        }

        .btn-totallabel_1 {
            background: linear-gradient(135deg, #1b2850 0%, #0f172a 100%) !important;
            color: #ffffff !important;
            border: none;
            border-radius: 10px;
            padding: 11px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(27, 40, 80, 0.25);
            transition: all 0.25s ease;
        }
        .btn-totallabel_1:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(27, 40, 80, 0.35);
        }
        .btn-totallabel_1 h6 {
            color: #ffffff !important;
            font-size: 13.5px;
            font-weight: 700;
            margin: 0;
        }
        .btn-totallabel_1 h5 {
            color: #ffffff !important;
            margin: 0;
        }

        .btn-totallabel_2 {
            background: linear-gradient(135deg, #ff9f43 0%, #ff8510 100%) !important;
            color: #ffffff !important;
            border: none;
            border-radius: 10px;
            padding: 11px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(255, 159, 67, 0.35);
            transition: all 0.25s ease;
        }
        .btn-totallabel_2:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(255, 159, 67, 0.45);
        }
        .btn-totallabel_2 h6 {
            color: #ffffff !important;
            font-size: 13.5px;
            font-weight: 700;
            margin: 0;
        }
        .btn-totallabel_2 h5 {
            color: #ffffff !important;
            margin: 0;
        }

        .search_custom_product {
            width: 100%;
        }

        .paymentmethod.active {
            background-color: #1b2850;
            color: #ffffff;
        }

        .paymentmethod.active svg {
            fill: #ffffff;
        }

        .productsetimgin {
            height: 100px;
            object-fit: contain;
            margin: 1rem auto 0;
            display: block;
        }

        .productsetbtn button {
            display: none;
        }

        .search_custom_product {
            width: 100%;
        }

        .payment_panel {
            z-index: 999;
        }

        @media screen and (max-width: 768px) {
            .payment_panel {
                position: fixed;
                bottom: 50px;
                background-color: white;
                width: 80%;
                margin-left: -6px;
            }

            .productset {
                display: flex;
            }

            .productset .productsetimg {

                width: 30%;
            }

            .productsetimgin {
                object-fit: cover;
                height: 90px;
                margin: 0;
                max-width: 110px;
            }

            .productsetcontent {
                text-align: left !important;
            }



            .productsetbtn {
                position: absolute;
                right: 10px;
                top: 43px;
            }

            .productsetbtn button {
                display: block;
                width: 100%;
                height: 33px;
                background-color: #1b2850;
                color: white !important;
                border: none;
                border-radius: 5px;
            }

            .productsetbtn button:active {
                color: white;
            }

            .product-lists {
                flex-direction: row !important;
            }

            .body_space_two {
                margin-bottom: 4rem;
            }

            a.confirm-text.remove-item {
                /* font-size: 116px; */
                margin: 6px;
            }
        }



        .modal-body {
            display: block;
            overflow-x: hidden;
            padding: 10px;
        }

        @media (max-width: 768px) {
            .modal-lg, .modal-xl {
                max-width: 100% !important;
                margin: 0 !important;
            }

            .modal-dialog {
                margin: 0.5rem !important;
                max-width: calc(100vw - 1rem) !important;
            }

            .modal-body .col-lg-7,
            .modal-body .col-lg-5,
            .modal-body .col-sm-12 {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
                overflow-x: hidden;
            }

            .modal-body .tabs_wrapper {
                max-width: 100%;
                overflow-x: hidden;
            }

            .modal-body .productset {
                width: 100%;
                box-sizing: border-box;
            }
        }

        .content {
            margin: 20px 0 !important;
        }

        .page-wrapper {
            padding: 20px;
        }

        /* ===== Tables Floor — modern seating board ===== */
        .tables-floor {
            --tf-ink: #0f172a;
            --tf-muted: #64748b;
            --tf-line: #e2e8f0;
            --tf-surface: #ffffff;
            --tf-soft: #f8fafc;
            --tf-accent: #ff9f43;
            --tf-accent-deep: #ff821c;
            background: transparent;
            border: 0;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
        }

        .tables-floor .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            padding-bottom: 0;
            border-bottom: 0;
        }

        .tables-floor .page-title h4 {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--tf-ink);
        }

        .tables-floor .page-title p {
            margin: 6px 0 0;
            color: var(--tf-muted);
            font-size: 0.92rem;
            font-weight: 500;
        }

        .tables-floor .btn-added {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--tf-accent) 0%, var(--tf-accent-deep) 100%) !important;
            border: 0 !important;
            border-radius: 12px !important;
            padding: 10px 16px !important;
            font-weight: 700 !important;
            box-shadow: 0 10px 22px -12px rgba(255, 159, 67, 0.8);
        }

        .tables-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .legend {
            display: flex;
            gap: 8px;
            margin: 0;
            flex-wrap: wrap;
        }

        .legend span {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            box-shadow: none;
            cursor: default;
        }

        .legend span::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #94a3b8;
        }

        .legend .blank-table::before { background: #94a3b8; }
        .legend .running-table::before { background: #22c55e; }
        .legend .reserved-table::before { background: #f59e0b; }

        .legend .blank-table,
        .legend .running-table,
        .legend .reserved-table,
        .legend .printed-table {
            background: #fff !important;
            color: #475569 !important;
            border-color: #e2e8f0 !important;
        }

        .blank-table {
            --table-accent: #94a3b8;
            --table-soft: #f8fafc;
            background: #ffffff;
            color: #0f172a;
            border-color: #e2e8f0;
        }

        .running-table {
            --table-accent: #22c55e;
            --table-soft: #f0fdf4;
            background: #ffffff;
            color: #0f172a;
            border-color: #bbf7d0;
        }

        .reserved-table,
        .printed-table {
            --table-accent: #f59e0b;
            --table-soft: #fffbeb;
            background: #ffffff;
            color: #0f172a;
            border-color: #fde68a;
        }

        .kot-table {
            background: #9BD4FF;
        }

        .tables-zone {
            margin-bottom: 28px;
        }

        .tables-zone:last-child {
            margin-bottom: 0;
        }

        .tables-zone__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .section-title {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .section-title::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 2px;
            background: var(--tf-accent);
        }

        .tables-zone__count {
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
        }

        .table-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 14px;
            align-items: stretch;
        }

        .table-card-wrap {
            position: relative;
            display: flex;
            height: 100%;
        }

        .table-box.table-card {
            width: 100%;
            min-height: 210px;
            height: 100%;
            border-radius: 16px;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            position: relative;
            text-transform: none;
            cursor: pointer;
            padding: 0;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .table-box.table-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
            box-shadow: 0 10px 24px -16px rgba(15, 23, 42, 0.28);
        }

        .table-box.table-card.running-table {
            border-color: #86efac;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 42%);
        }

        .table-box.table-card.running-table:hover {
            border-color: #4ade80;
            box-shadow: 0 12px 26px -14px rgba(22, 163, 74, 0.35);
        }

        .table-box.table-card.reserved-table {
            border-color: #fcd34d;
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 42%);
        }

        .table-box.table-card.reserved-table:hover {
            border-color: #fbbf24;
        }

        .table-card__accent {
            height: 4px;
            width: 100%;
            background: var(--table-accent, #94a3b8);
            flex-shrink: 0;
        }

        .table-card__body {
            padding: 14px 14px 10px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .table-card__meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-height: 22px;
        }

        .table-card__badge {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #64748b;
            border: 0;
        }

        .table-box.table-card.running-table .table-card__badge {
            background: #dcfce7;
            color: #15803d;
        }

        .table-box.table-card.reserved-table .table-card__badge {
            background: #fef3c7;
            color: #b45309;
        }

        .table-card__signals {
            display: inline-flex;
            gap: 4px;
        }

        .table-card__signal {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
        }

        .table-card__center {
            text-align: center;
            padding: 2px 0 4px;
        }

        .table-card__visual {
            width: 42px;
            height: 42px;
            margin: 0 auto 8px;
            color: #64748b;
            opacity: 1;
        }

        .table-box.table-card.running-table .table-card__visual {
            color: #16a34a;
        }

        .table-box.table-card.reserved-table .table-card__visual {
            color: #d97706;
        }

        .table-card__visual svg {
            width: 100%;
            height: 100%;
        }

        .table-card__name {
            margin: 0;
            text-align: center;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .table-card__bill {
            display: none;
            margin-top: 8px;
        }

        .table-card__bill.is-visible {
            display: block;
        }

        .table-card__bill-label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }

        .table-card__bill-total {
            display: block;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #15803d;
            line-height: 1.15;
        }

        .table-card__actions {
            display: flex;
            align-items: stretch;
            justify-content: stretch;
            gap: 8px;
            padding: 10px 12px;
            border-top: 1px solid #f1f5f9;
            background: #fafbfc;
            margin-top: auto;
        }

        .table-card__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex: 1 1 auto;
            width: 100%;
            min-height: 36px;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none !important;
            cursor: pointer;
            box-sizing: border-box;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .table-card__action--reserve:hover {
            background: #fff7ed;
            border-color: #fdba74;
            color: #c2410c;
        }

        .table-card__action--print:hover {
            background: #0f172a;
            border-color: #0f172a;
            color: #fff;
        }

        .table-card__action-spacer {
            flex: 1;
            min-height: 36px;
        }

        .table-icons {
            display: none;
        }

        .print-btn {
            background: white;
            padding: 5px;
            border-radius: 8px;
            font-size: 16px;
        }

        .table-choice-btn {
            min-width: 140px;
            padding: 12px 18px;
            border: 0;
            border-radius: 12px;
            font-weight: 700;
            color: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .table-choice-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 18px -10px rgba(15, 23, 42, 0.35);
        }

        .table-choice-btn.dinein {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .table-choice-btn.reservation {
            background: linear-gradient(135deg, #ff9f43 0%, #ff821c 100%);
        }

        .reservation-preview {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #fff7e8;
            color: #9a5b00;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .product-table {
            height: auto !important;
            min-height: 80px;
            max-height: 40vh;
            overflow-y: auto;
        }

        @media (max-width: 767px) {
            .tables-floor .page-title h4 {
                font-size: 1.25rem;
            }

            .table-container {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .table-card__bill-total {
                font-size: 14px;
            }
        }
    </style>

    <div class="content">
        @php
            $tableStatusClass = function ($table) {
                $status = strtolower(trim((string) ($table->status ?? '')));

                if ($status === '') {
                    $status = match ((int) ($table->table_status ?? 0)) {
                        1 => 'running',
                        2 => 'reserved',
                        default => 'blank',
                    };
                }

                return match ($status) {
                    'running' => 'running-table',
                    'reserved' => 'reserved-table',
                    default => 'blank-table',
                };
            };
        @endphp

        <div class="tables-floor">
            <div class="page-header">
                <div class="page-title">
                    <h4>Table Seating Arrangement</h4>
                    <p>Select a table to start dine-in, continue a running bill, or reserve a seat.</p>
                </div>
                <div class="page-btn">
                    @if (app('hasPermission')(2, 'add'))
                        <a href="{{ route('sales.add') }}" class="btn btn-sm btn-added">
                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="" class="me-1">Take Away
                        </a>
                    @endif
                </div>
            </div>

            <div class="tables-toolbar">
                <div class="legend">
                    <span class="blank-table">Available</span>
                    <span class="running-table">Occupied</span>
                    <span class="reserved-table">Reserved</span>
                </div>
            </div>

            {{-- ========================= A/C SECTION ========================= --}}
            @if(isset($tables['AC']) && $tables['AC']->count())
                <section class="tables-zone">
                    <div class="tables-zone__head">
                        <h5 class="section-title">A/C</h5>
                        <span class="tables-zone__count">{{ $tables['AC']->count() }} tables</span>
                    </div>
                    <div class="table-container">
                        @foreach($tables['AC'] as $table)
                            @include('tables.partials.table_card', [
                                'table' => $table,
                                'statusClass' => $tableStatusClass($table),
                            ])
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ========================= NON A/C SECTION ========================= --}}
            @if(isset($tables['Non AC']) && $tables['Non AC']->count())
                <section class="tables-zone">
                    <div class="tables-zone__head">
                        <h5 class="section-title">Non A/C</h5>
                        <span class="tables-zone__count">{{ $tables['Non AC']->count() }} tables</span>
                    </div>
                    <div class="table-container">
                        @foreach($tables['Non AC'] as $table)
                            @include('tables.partials.table_card', [
                                'table' => $table,
                                'statusClass' => $tableStatusClass($table),
                            ])
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ========================= Premium SECTION ========================= --}}
            @if(isset($tables['Premium']) && $tables['Premium']->count())
                <section class="tables-zone">
                    <div class="tables-zone__head">
                        <h5 class="section-title">Premium</h5>
                        <span class="tables-zone__count">{{ $tables['Premium']->count() }} tables</span>
                    </div>
                    <div class="table-container">
                        @foreach($tables['Premium'] as $table)
                            @include('tables.partials.table_card', [
                                'table' => $table,
                                'statusClass' => $tableStatusClass($table),
                            ])
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

    <div class="modal fade" id="tableOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255,159,67,0.15); display: flex; align-items: center; justify-content: center; color: #ff9f43; font-size: 16px;">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h5 class="modal-title m-0" style="font-weight: 800; color: #0f172a; font-size: 16px;">
                            <input type="hidden" name="table_id" id="table_id">
                            <span class="table-order-kicker d-block" style="font-size: 11px; letter-spacing: 0.06em;">Dine In Order</span>
                            Table: <span class="badge bg-warning text-dark px-2 py-1 js-table-name ms-1" style="font-size: 13px; font-weight: 700;"></span>
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3 table-order-layout">
                    <div class="col-lg-7 col-sm-12 tabs_wrapper">

                        @php
                            $hasProducts = false; // start as false
                        @endphp

                        <div class="table-order-menu-toolbar">
                            <ul class="tabs border-0 mb-0">
                                @foreach ($categories as $cat)
                                    @php
                                        $availableProducts = $cat->products->where('quantity', '>', 0);
                                    @endphp

                                    @if ($availableProducts->count() > 0)
                                        @php
                                            $hasProducts = true; // at least one product exists
                                        @endphp
                                        <li id="{{ $cat->id }}">
                                            <div class="product-details">
                                                <h6 style="text-transform: capitalize; color: #000;">{{ $cat->name }}</h6>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            <div class="table-order-product-search">
                                <img class="search-icon"
                                    src="{{ env('ImagePath') . '/admin/assets/img/icons/search.svg' }}"
                                    alt="">
                                <input type="text" id="tableProductSearch"
                                    class="form-control"
                                    placeholder="Search product..."
                                    autocomplete="off">
                                <button type="button" class="clear-product-search" aria-label="Clear search">&times;</button>
                            </div>
                        </div>

                        @if (!$hasProducts)
                            <div class="product-details text-center">
                                <h6>No data available</h6>
                                <a href="{{ url('add-product') }}" class="btn btn-primary mt-2">
                                    + Add Product
                                </a>
                            </div>
                        @endif
                        <div class="tabs_container">

                        </div>
                        <div id="tableProductSearchResults" aria-live="polite"></div>
                    </div>
                    <div class="col-lg-5 col-sm-12 ">

                        <div class="card card-order">
                            <div class="card-body body_space">
                                <div class="order-panel-title">Current Order</div>
                                <div class="gst-header mb-2">
                                    <div class="custom-gst-pill-group">
                                        <label class="custom-gst-pill">
                                            <input type="radio" name="gst_option" id="without_gst" value="without" checked />
                                            <span>Without GST</span>
                                        </label>
                                        <label class="custom-gst-pill">
                                            <input type="radio" name="gst_option" id="with_gst" value="with" />
                                            <span>With GST</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="header-search position-relative search_custom_product d-none">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/search.svg' }}"
                                        alt="Search"
                                        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; opacity: 0.55;">
                                    <input type="text" id="customerSearch1"
                                        class="form-control form-control-sm rounded px-3 ps-5"
                                        placeholder="Search..." autocomplete="off"
                                        style="height: 36px; font-size: 13px;">
                                    <div id="searchResults1"
                                        class="list-group bg-white position-absolute rounded shadow mt-1 w-100"
                                        style="z-index: 1050; max-height: 240px; overflow-y: auto; display: none; top: 100%; left: 0;">
                                    </div>
                                </div>

                                <div class="order-scroll">
                                    <div class="totalitem">
                                        <h4>Total items : 0</h4>
                                        <a href="javascript:void(0);" class="clear_items">Clear all</a>
                                    </div>
                                    <div class="product-table"></div>

                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <label for="discount">Discount (%)</label>
                                            <input type="number" class="form-control" placeholder="0"
                                                name="discount" id="discount">
                                            <span class="error_discount"></span>
                                        </div>
                                    </div>

                                    <div class="setvalue">
                                        <ul>
                                            <li>
                                                <h5>Subtotal</h5>
                                                <h6 style="color: #16a34a;">
                                                    @if ($currency_position === 'right')
                                                        <span class="subtotal-value">0.00</span>{{ $currency_symbol }}
                                                    @else
                                                        {{ $currency_symbol }}<span class="subtotal-value">0.00</span>
                                                    @endif
                                                </h6>
                                                <input type="hidden" name="subtotal" value="" class="tax-hidden">
                                            </li>
                                            <li>
                                                <h5>Discount Amount</h5>
                                                <h6 class="discount-amount">
                                                    @if ($currency_position === 'right')
                                                        <span>0.00</span>{{ $currency_symbol }}
                                                    @else
                                                        {{ $currency_symbol }}<span>0.00</span>
                                                    @endif
                                                </h6>
                                                <input type="hidden" name="discount_amount" value="0.00" class="tax-hidden">
                                            </li>
                                            <li>
                                                <h5>Price After Discount</h5>
                                                <h6 class="price-after-discount">
                                                    @if ($currency_position === 'right')
                                                        <span>0.00</span>{{ $currency_symbol }}
                                                    @else
                                                        {{ $currency_symbol }}<span>0.00</span>
                                                    @endif
                                                </h6>
                                                <input type="hidden" name="price_after_discount" value="0.00" class="tax-hidden">
                                            </li>
                                            @foreach ($taxRates as $tax)
                                                <li class="taxList">
                                                    <h5>{{ $tax->tax_name }} ({{ $tax->tax_rate }}%) Tax</h5>
                                                    <h6 class="tax-value" data-rate="{{ $tax->tax_rate }}"
                                                        data-symbol="{{ $currency_symbol }}"
                                                        data-position="{{ $currency_position }}">
                                                        @if ($currency_position === 'right')
                                                            <span>0.00</span>{{ $currency_symbol }}
                                                        @else
                                                            {{ $currency_symbol }}<span>0.00</span>
                                                        @endif
                                                    </h6>
                                                </li>
                                            @endforeach
                                            <li class="total-value">
                                                <h5>Total</h5>
                                                <h6>
                                                    @if ($currency_position === 'right')
                                                        <span class="total-amount">0.00</span>{{ $currency_symbol }}
                                                    @else
                                                        {{ $currency_symbol }}<span class="total-amount">0.00</span>
                                                    @endif
                                                </h6>
                                                <input type="hidden" name="total" value="" class="tax-hidden">
                                            </li>
                                            <li class="error-message-container">
                                                <span class="error_total" style="color:red; display:none;"></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="payment_panel">
                                    <div class="setvaluecash">
                                        <ul>
                                            <li class="d-none">
                                                <a href="javascript:void(0);" class="paymentmethod">
                                                    <input type="radio" name="payment_method" value="pending" checked hidden>
                                                    <i class="fas fa-history"></i>
                                                    <span>Pay Later</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="paymentmethod">
                                                    <input type="radio" name="payment_method" value="cash" hidden>
                                                    <i class="fas fa-money-bill-wave"></i>
                                                    <span>Cash</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="paymentmethod">
                                                    <input type="radio" name="payment_method" value="debit card" hidden>
                                                    <i class="fas fa-credit-card"></i>
                                                    <span>Debit Card</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="paymentmethod">
                                                    <input type="radio" name="payment_method" value="cash+online" hidden>
                                                    <i class="fas fa-money-check-alt"></i>
                                                    <span>Cash + Online</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="paymentmethod">
                                                    <input type="radio" name="payment_method" value="scan" hidden>
                                                    <i class="fas fa-qrcode"></i>
                                                    <span>Scan / UPI</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div id="cashOnlineBox" style="display:none; margin-top:8px;">
                                            <div class="form-group mb-2">
                                                <label style="font-size: 12px; font-weight: 600; color: #64748b;">Cash Amount</label>
                                                <input type="number" class="form-control form-control-sm" id="cashAmount" placeholder="Enter Cash Amount">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-size: 12px; font-weight: 600; color: #64748b;">Online Amount</label>
                                                <input type="number" class="form-control form-control-sm" id="onlineAmount" placeholder="Enter Online Amount" value="0" readonly>
                                            </div>
                                        </div>
                                        <span class="error_peymentmethod"></span>
                                    </div>

                                    <div class="btn-totallabel">
                                        <h6>Place Order · ₹0.00</h6>
                                        <h5><i class="fas fa-arrow-right"></i></h5>
                                    </div>

                                    <div class="modal-order-edit" style="display: none;">
                                        <div class="btn-totallabel_1" data-order_update_id="" style="width: 49%;">
                                            <h6>Update · {{ $currency_symbol }}0.00</h6>
                                            <h5><i class="fas fa-check"></i></h5>
                                        </div>
                                        <div class="btn-totallabel_2" data-order_payment_update_id="" style="width: 49%;">
                                            <h6>Checkout · {{ $currency_symbol }}0.00</h6>
                                            <h5><i class="fas fa-arrow-right"></i></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div><!-- end row -->
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="tableTypeChoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Choose Table Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-4 text-muted">How would you like to use <strong class="js-choice-table-name"></strong>?</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <button type="button" class="table-choice-btn dinein js-start-dinein">Dine In</button>
                        <button type="button" class="table-choice-btn reservation js-start-reservation">Reservation</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reservation Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="reservation-preview">
                        Table: <span class="js-reservation-table-name"></span>
                    </div>

                    <form id="reservationForm" class="row g-3">
                        <input type="hidden" id="reservation_table_id">
                        <div class="col-md-6">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="reservation_customer_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile No</label>
                            <input type="text" class="form-control" id="reservation_mobile">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" id="reservation_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" id="reservation_time" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Guests</label>
                            <input type="number" min="1" class="form-control" id="reservation_guests" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                            <textarea class="form-control" id="reservation_notes" rows="2" placeholder="Any special requestsâ€¦"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning fw-bold px-4">Save Reservation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="orderListModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Table Orders</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body table-responsive">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th class="details-column-modal">Details</th>
                                <th>Date</th>
                                <th>Customer Name</th>
                                <th>Payment Status</th>
                                <th>Payment Method</th>
                                <th>Total</th>
                                <th>Biller</th>
                            </tr>
                        </thead>
                        <tbody id="orderListBody">
                            <!-- Dynamic rows -->
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>

@endsection
@push('js')
    <script>
        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
        var authToken = localStorage.getItem("authToken");

        // Use event delegation to handle dynamically created elements
        $(document).ready(function () {
            $(document).on("click", ".paymentmethod", function () {
                let value = $(this).find("input[name='payment_method']").val();

                // Uncheck all payment methods
                $("input[name='payment_method']").prop("checked", false);

                // Check the selected one
                $(this).find("input[name='payment_method']").prop("checked", true);

                // Remove active class from all
                $(".paymentmethod").removeClass('active');

                // Add active class to clicked element
                $(this).addClass('active');

                // Show/hide cash+online box
                if (value === "cash+online") {
                    $("#cashOnlineBox").slideDown(); // show textboxes
                } else {
                    $("#cashOnlineBox").slideUp(); // hide textboxes
                }
            });
        });

        // Declare modal in outer scope so all jQuery handlers can access it
        var tableOrderModalEl = document.getElementById('tableOrderModal');
        var tableOrderModal = null;
        var tableTypeChoiceModalEl = document.getElementById('tableTypeChoiceModal');
        var tableTypeChoiceModal = null;
        var reservationModalEl = document.getElementById('reservationModal');
        var reservationModal = null;

        function applyRunningTableOrderResponse(response, salse_id, modalInstance) {
            if (typeof window.hydrateCartFromOrderItems === 'function') {
                window.hydrateCartFromOrderItems(response.order_items || []);
            }

            if (response.sales && response.sales.gst_option == 'with_gst') {
                $(`input[name="gst_option"][value="with"]`).prop("checked", true).trigger('change');
            } else {
                $(`input[name="gst_option"][value="without"]`).prop("checked", true).trigger('change');
            }
            if (response.sales && response.sales.discount !== null && response.sales.discount !== undefined) {
                $('#discount').val(response.sales.discount).trigger('input');
            }

            $('.btn-totallabel').hide();
            $('.modal-order-edit').css('display', 'flex');
            $('.btn-totallabel_1').css('display', 'flex').attr('data-order_update_id', salse_id);
            $('.btn-totallabel_2').css('display', 'flex').attr('data-order_payment_update_id', salse_id);
            $('.setvaluecash').show();
            (modalInstance || tableOrderModal).show();
        }

        function openDineInFlow(tableId, tableName, tableCard) {
            window.selectedTableId = tableId;
            window.selectedTableName = tableName;

            tableOrderModalEl.querySelector('.js-table-name').textContent = tableName;
            document.getElementById('table_id').value = tableId;

            if (tableCard && tableCard.classList.contains("running-table")) {
                const salse_id = tableCard.getAttribute('data-order-id');
                if (!salse_id) {
                    console.error("Running table has no order id");
                    return;
                }
                $.ajax({
                    url: `/api/getsalseById/${salse_id}`,
                    type: "GET",
                    dataType: "json",
                    data: { selectedSubAdminId: selectedSubAdminId },
                    headers: { "Authorization": "Bearer " + authToken },
                    success: function (response) {
                        applyRunningTableOrderResponse(response, salse_id, tableOrderModal);
                    },
                    error: function (xhr, status, error) {
                        console.error("Error fetching sales details:", error);
                    }
                });
                return;
            }

            $('.clear_items').trigger('click');
            $('.btn-totallabel').css('display', 'flex');
            $('.modal-order-edit').hide();
            $('.btn-totallabel_1').hide();
            $('.btn-totallabel_2').hide();
            $('.setvaluecash').hide();
            $('input[name="gst_option"]').prop("checked", false).trigger('change');
            $('#discount').val("").trigger('input');
            tableOrderModal.show();
        }

        // Blank tables open dine-in directly (no choose-type modal).
        // Reservation is started from the Reserve button on the card.

        function formatTableBillAmount(amount) {
            const firstTaxElement = $(".tax-value").first();
            const currencySymbol = firstTaxElement.data("symbol") || '{{ $currency_symbol }}';
            const currencyPosition = firstTaxElement.data("position") || '{{ $currency_position }}';
            const value = parseFloat(amount);
            if (isNaN(value)) return '';
            const fixed = value.toFixed(2);
            return currencyPosition === 'right' ? (fixed + currencySymbol) : (currencySymbol + fixed);
        }

        function updateTableCardStatus(tableId, statusClass, statusValue, orderId, billTotal) {
            const $card = $(".table-box123[data-table-id='" + tableId + "']");
            if (!$card.length) return;
            const nextClass = statusClass || 'blank-table';
            const nextStatus = (statusValue || 'blank').toLowerCase();
            const badgeLabel = nextStatus === 'running'
                ? 'Occupied'
                : (nextStatus === 'reserved' ? 'Reserved' : 'Available');

            $card.removeClass('blank-table running-table reserved-table printed-table')
                .addClass(nextClass)
                .attr('data-order-id', orderId || '')
                .attr('data-table-status', nextStatus);

            $card.find('.table-card__badge').text(badgeLabel);

            const $bill = $card.find('.table-card__bill');
            if (nextStatus === 'running' && billTotal !== undefined && billTotal !== null && billTotal !== '') {
                const formatted = formatTableBillAmount(billTotal);
                $card.attr('data-bill-total', parseFloat(billTotal).toFixed(2));
                $bill.addClass('is-visible');
                $bill.find('.table-card__bill-total').text(formatted);
            } else if (nextStatus !== 'running') {
                $card.attr('data-bill-total', '');
                $bill.removeClass('is-visible');
                $bill.find('.table-card__bill-total').text('');
            }

            const $actions = $card.find('.table-card__actions');
            const tableName = $card.data('table-name') || $card.attr('data-table-name') || '';
            $actions.empty();
            if (nextStatus === 'blank' || nextStatus === 'available') {
                $actions.append(
                    '<button type="button" class="table-card__action table-card__action--reserve js-reserve-table" title="Reserve this table"' +
                    ' data-table-id="' + tableId + '" data-table-name="' + String(tableName).replace(/"/g, '&quot;') + '">' +
                    '<i class="fa-regular fa-calendar-check"></i><span>Reserve</span></button>'
                );
            } else if (nextStatus === 'running') {
                $actions.append(
                    '<a href="javascript:void(0);" class="print-table-order table-card__action table-card__action--print"' +
                    ' data-table_no_invoice="' + tableId + '" title="Print / view orders">' +
                    '<i class="fa-solid fa-print"></i><span>Print</span></a>'
                );
            } else {
                $actions.append('<span class="table-card__action-spacer"></span>');
            }
        }

        function reserveSelectedTable(payload) {
            const tableCard = document.querySelector(
        `.table-box123[data-table-id="${payload.tableId}"]`
    );

    const tableType = tableCard ? tableCard.dataset.tableType : "";
            $.ajax({
                url: '/api/reservations',
                method: "POST",
                dataType: "json",
                data: {
                    table_id:          payload.tableId,
                    customer_name:     payload.customerName,
                    customer_phone:    payload.mobile,
                    reservation_date:  payload.date,
                    reservation_time:  payload.time,
                    guests:            payload.guests,
                    notes:             payload.notes || '',
                    selectedSubAdminId: selectedSubAdminId,
                },
                headers: { "Authorization": "Bearer " + authToken },
                success: function (response) {
                    updateTableCardStatus(payload.tableId, 'reserved-table', 'reserved', '');
                    if (reservationModal) reservationModal.hide();
                    if (tableTypeChoiceModal) tableTypeChoiceModal.hide();
                    // Show success toast if available
                    if (typeof showToast === 'function') {
                        showToast('Reservation saved successfully!', 'success');
                    }
                },
                error: function (xhr) {
                    console.error(xhr);
                    let msg = 'Failed to save reservation';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    alert(msg);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            tableOrderModal = new bootstrap.Modal(tableOrderModalEl);
            tableTypeChoiceModal = new bootstrap.Modal(tableTypeChoiceModalEl);
            reservationModal = new bootstrap.Modal(reservationModalEl);

            function openReservationForTable(tableId, tableName) {
                if (!tableId) return;
                if ($('#reservationForm').length) {
                    $('#reservationForm')[0].reset();
                }
                if (reservationModalEl) {
                    reservationModalEl.querySelector('.js-reservation-table-name').textContent = tableName || '';
                }
                $('#reservation_table_id').val(tableId);
                if (tableTypeChoiceModal) tableTypeChoiceModal.hide();
                if (tableOrderModal) tableOrderModal.hide();
                if (reservationModal) {
                    reservationModal.show();
                }
            }

            $(document).on('click', '.js-reserve-table', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const tableId = $(this).attr('data-table-id') || $(this).data('table-id');
                const tableName = $(this).attr('data-table-name') || $(this).data('table-name');
                openReservationForTable(tableId, tableName);
            });

            $(document).on('click', '.js-start-dinein', function () {
                const pending = window.pendingTableSelection;
                if (!pending) return;
                tableTypeChoiceModal.hide();
                const card = document.querySelector(`.table-box123[data-table-id="${pending.tableId}"]`);
                openDineInFlow(pending.tableId, pending.tableName, card);
            });

            $(document).on('click', '.js-start-reservation', function () {
                const pending = window.pendingTableSelection;
                if (!pending) return;
                openReservationForTable(pending.tableId, pending.tableName);
            });

            $('#reservationForm').on('submit', function (e) {
                e.preventDefault();
                const tableId = $('#reservation_table_id').val();
                const tableName = $('.js-reservation-table-name').text().trim();

                reserveSelectedTable({
                    tableId:      tableId,
                    tableName:    tableName,
                    customerName: $('#reservation_customer_name').val(),
                    mobile:       $('#reservation_mobile').val(),
                    date:         $('#reservation_date').val(),
                    time:         $('#reservation_time').val(),
                    guests:       $('#reservation_guests').val(),
                    notes:        $('#reservation_notes').val() || '',
                });
            });
            // keep local alias for DOMContentLoaded block
            var modal = tableOrderModal;

            document.querySelectorAll('.table-box123').forEach(function (box) {
                box.addEventListener('click', function (e) {
                    // Reserve / Print live inside the card — don't open dine-in for those
                    if (e.target.closest('.table-card__actions, .js-reserve-table, .print-table-order')) {
                        return;
                    }

                    const tableId = this.dataset.tableId;
                    const tableName = this.dataset.tableName;

                    // Store globally for order submit
                    window.selectedTableId = tableId;

                    tableOrderModalEl.querySelector('.js-table-name').textContent = tableName;
                    document.getElementById('table_id').value = tableId;

                    // Check class first
                    if (this.classList.contains("running-table")) {
                        const salse_id = this.getAttribute('data-order-id');
                        if (!salse_id) {
                            console.error("Running table has no order id");
                            return;
                        }
                        $.ajax({
                            url: `/api/getsalseById/${salse_id}`,
                            type: "GET",
                            dataType: "json",
                            data: {
                                selectedSubAdminId: selectedSubAdminId
                            },
                            headers: {
                                "Authorization": "Bearer " + authToken,
                            },
                            success: function (response) {
                                applyRunningTableOrderResponse(response, salse_id, modal);
                            },
                            error: function (xhr, status, error) {
                                console.error("Error fetching sales details:", error);
                            }
                        });
                    } else {
                        $('.clear_items').trigger('click');
                        $('.btn-totallabel').css('display', 'flex');
                        $('.modal-order-edit').hide();
                        $('.btn-totallabel_1').hide();
                        $('.btn-totallabel_2').hide();
                        $('.setvaluecash').hide();
                        $('input[name="gst_option"]').prop("checked", false).trigger('change');

                        // Clear discount
                        $('#discount').val("").trigger('input');

                        modal.show();

                    }
                    // Show modal only for blank-table        
                });
            });
        });






        $(document).ready(function () {
            selectedItems = new Map();
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId1 = localStorage.getItem("selectedSubAdminId");


            $(document).on("click", ".print-table-order", function (e) {
                e.preventDefault();
                e.stopPropagation();
                let tableId = $(this).data("table_no_invoice") || $(this).attr("data-table_no_invoice");

                $.ajax({
                    url: "/api/get_orders_invoice",
                    method: "GET",
                    data: {
                        selectedSubAdminId: selectedSubAdminId1,
                        table_id: tableId
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function (res) {

                        // CHECK IF NO DATA
                        if (!res.status || !Array.isArray(res.data) || res.data.length === 0) {

                            $("#orderListBody").html(`
                <tr>
                    <td colspan="8" class="text-center text-danger fw-bold py-3" style="width: 100% !important; display: table-cell !important;">
                        No orders found
                    </td>
                </tr>
            `);

                            $("#orderListModal").modal("show");
                            return;
                        }

                        let rows = "";

                        res.data.forEach(order => {

                            let paymentStatus = `<span class="badge bg-success text-white text-capitalize">${order.payment_status}</span>`;
                            let paymentMethod = `<span class="badge bg-success text-white text-capitalize">${order.payment_method}</span>`;

                            rows += `
                <tr>
                    <td>
                        ${order.order_number}
                        <div class="collapse d-md-none mt-2" id="details-order-${order.id}">
                            <div class="mobile-details-card">
                                <strong>Date:</strong> ${order.created_date} <br>
                                <strong>Customer:</strong> ${order.user?.name ?? '-'} <br>
                                <strong>Payment Status:</strong> ${paymentStatus} <br>
                                <strong>Payment Method:</strong> ${paymentMethod} <br>
                                <strong>Total:</strong> ${res.currency_symbol}${order.total_amount} <br>
                                <strong>Biller:</strong> ${order.creator?.name ?? '-'}
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="javascript:void(0);" class="toggle-details text-info" data-bs-toggle="collapse" data-bs-target="#details-order-${order.id}">
                            <i class="fas fa-plus-circle"></i>
                        </a>
                    </td>
                    <td>${order.created_date}</td>
                    <td>${order.user?.name ?? '-'}</td>
                    <td>${paymentStatus}</td>
                    <td>${paymentMethod}</td>
                    <td>${res.currency_symbol}${order.total_amount}</td>
                    <td>${order.creator?.name ?? '-'}</td>
                </tr>
            `;
                        });

                        $("#orderListBody").html(rows);

                        // OPEN MODAL
                        $("#orderListModal").modal("show");
                    },

                    error: function (err) {
                        console.log(err);
                        alert("Failed to load orders");
                    }
                });
            });

            // Initialize tax rows as hidden on page load
            $(".taxList").hide();

            function formatCurrency(amount, symbol, position) {
                return position === 'right' ? amount + symbol : symbol + amount;
            }

            function resolveProductImage(imageValue) {
                let productImage = '{{ env("ImagePath") . "/admin/assets/img/product/noimage.png" }}';
                if (!imageValue) {
                    return productImage;
                }
                try {
                    let cleanImageString = String(imageValue).replace(/\\/g, '');
                    cleanImageString = cleanImageString.replace(/^"(.*)"$/, '$1');
                    let imageBasePath = "{{ rtrim(env('ImagePath'), '/') }}";
                    if (cleanImageString.startsWith('[')) {
                        let imagesArray = JSON.parse(cleanImageString);
                        if (Array.isArray(imagesArray) && imagesArray.length > 0) {
                            productImage = `${imageBasePath}/storage/${imagesArray[0]}`;
                        }
                    } else {
                        productImage = `${imageBasePath}/storage/${cleanImageString}`;
                    }
                } catch (e) {
                    productImage = '{{ env("ImagePath") . "/admin/assets/img/product/noimage.png" }}';
                }
                return productImage;
            }

            // Load existing table order items into cart from API data.
            // Do NOT rely on .productset clicks — only the active category is in the DOM.
            window.hydrateCartFromOrderItems = function (orderItems) {
                if (typeof selectedItems === 'undefined' || !selectedItems) {
                    selectedItems = new Map();
                }
                selectedItems.clear();
                $(".check-product i").removeClass("selected");

                const firstTaxElement = $(".tax-value").first();
                const currencySymbol = firstTaxElement.data("symbol") || '{{ $currency_symbol }}';
                const currencyPosition = firstTaxElement.data("position") || '{{ $currency_position }}';

                (orderItems || []).forEach(function (item) {
                    const product = item.product;
                    if (!product || !product.id) {
                        return;
                    }

                    const productId = product.id;
                    const qtyType = item.quantity_type;
                    const qtyTypeName = (qtyType && qtyType.quantity_type)
                        ? qtyType.quantity_type
                        : (product.quantity_type || 'pcs');
                    const qtyTypeId = (qtyType && qtyType.id)
                        ? qtyType.id
                        : (product.quantity_type_id || null);
                    const priceNum = parseFloat(item.price != null ? item.price : product.price) || 0;
                    const availableStock = (parseInt(product.quantity, 10) || 0) + (parseInt(item.quantity, 10) || 0);

                    selectedItems.set(productId, {
                        id: productId,
                        productId: productId,
                        categoryId: item.category_id || product.category_id || '',
                        name: product.name || 'Item',
                        price: formatCurrency(priceNum.toFixed(2), currencySymbol, currencyPosition),
                        image: resolveProductImage(product.image),
                        code: product.product_code || 'PT001',
                        quantity_type: qtyTypeName,
                        quantity_type_id: qtyTypeId,
                        quantity_type123: qtyTypeId,
                        quantity: parseInt(item.quantity, 10) || 1,
                        stock: availableStock > 0 ? availableStock : 9999
                    });

                    $('.productset[data-id="' + productId + '"] .check-product i').addClass('selected');
                });

                updateTotalItems();
            };

            var tableOrderProductsCache = {};
            var tableOrderProductCurrency = {
                symbol: '{{ $currency_symbol }}',
                position: '{{ $currency_position }}'
            };
            var tableProductSearchTimer = null;
            var tableProductSearchRequestId = 0;

            function loadProductsByCategory(categoryId, options) {
                options = options || {};

                return $.ajax({
                    url: "/api/getProductsByCategory/" + categoryId,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function (response) {
                        const currencySymbol = response.currency_symbol || tableOrderProductCurrency.symbol || '₹';
                        const currencyPosition = response.currency_position || tableOrderProductCurrency.position || 'left';
                        tableOrderProductCurrency.symbol = currencySymbol;
                        tableOrderProductCurrency.position = currencyPosition;

                        var products = (response.status && Array.isArray(response.data)) ? response.data : [];
                        tableOrderProductsCache[String(categoryId)] = products;

                        if (!options.skipRender) {
                            renderProducts(products, categoryId, currencySymbol, currencyPosition);
                        }
                    },
                    error: function (xhr, status, error) {
                        tableOrderProductsCache[String(categoryId)] = [];
                        if (!options.skipRender) {
                            renderProducts([], categoryId);
                        }
                    }
                });
            }

            function ensureAllCategoryProductsLoaded() {
                var categoryIds = [];
                $('#tableOrderModal .tabs li').each(function () {
                    var id = $(this).attr('id');
                    if (id) {
                        categoryIds.push(String(id));
                    }
                });

                var pending = categoryIds.filter(function (id) {
                    return !Object.prototype.hasOwnProperty.call(tableOrderProductsCache, id);
                });

                if (!pending.length) {
                    return $.Deferred().resolve().promise();
                }

                var requests = pending.map(function (id) {
                    return loadProductsByCategory(id, { skipRender: true });
                });

                return $.when.apply($, requests);
            }

            $(".tabs li").click(function () {
                var categoryId = $(this).attr('id');

                // Category browse mode — clear product search
                $('#tableProductSearch').val('');
                $('.table-order-product-search').removeClass('has-value');
                exitTableProductSearchMode();

                // Remove active class from all tabs and tab contents
                $(".tabs li").removeClass('active');
                $(".tab_content").removeClass('active');

                // Add active class to clicked tab
                $(this).addClass('active');

                // Scroll clicked tab into view smoothly
                this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });

                // Find the corresponding tab content
                var tabContentSelector = '.tab_content[data-tab="' + categoryId + '"]';
                var $tabContent = $(tabContentSelector);

                if ($tabContent.length) {
                    $tabContent.addClass('active');


                    // Check if content already loaded
                    if ($tabContent.find('.productset').length === 0) {
                        loadProductsByCategory(categoryId);
                    }
                } else {

                    // Create new tab content if not found
                    $('.tabs_container').append('<div class="tab_content active" data-tab="' + categoryId +
                        '"></div>');
                    loadProductsByCategory(categoryId);
                }
            });

            var firstCategory = $(".tabs li:first");
            if (firstCategory.length) {
                var firstCategoryId = firstCategory.attr('id');
                firstCategory.addClass('active');
                var firstTabContent = $('.tab_content[data-tab="' + firstCategoryId + '"]');

                if (firstTabContent.length) {
                    firstTabContent.addClass('active');
                } else {
                    // Create tab content if it doesn't exist
                    $('.tabs_container').append('<div class="tab_content active" data-tab="' + firstCategoryId +
                        '"></div>');
                }

                // Load data for first category
                loadProductsByCategory(firstCategoryId);
            }


            function renderProducts(products, categoryId, currencySymbol, currencyPosition) {

                var productsHtml = '';

                if (products.length === 0) {
                    productsHtml = '<div class="col-12"><p>No products found in this category</p></div>';
                } else {
                    products.forEach(function (product) {
                        // Default image
                        let productImage = '{{ env("ImagePath") . "/admin/assets/img/product/noimage.png" }}';

                        // Handle different image formats
                        if (product.image) {
                            try {
                                // Clean the image string
                                let cleanImageString = product.image.replace(/\\/g, '');
                                cleanImageString = cleanImageString.replace(/^"(.*)"$/, '$1');

                                let imageBasePath = "{{ rtrim(env('ImagePath'), '/') }}"; // safe trailing slash removal

                                if (cleanImageString.startsWith('[')) {
                                    let imagesArray = JSON.parse(cleanImageString);
                                    if (Array.isArray(imagesArray) && imagesArray.length > 0) {
                                        productImage = `${imageBasePath}/storage/${imagesArray[0]}`;
                                    }
                                } else {
                                    productImage = `${imageBasePath}/storage/${cleanImageString}`;
                                }
                            } catch (e) {
                                productImage = '{{ env("ImagePath") . "/admin/assets/img/product/noimage.png" }}';
                            }
                        }

                        // Format price
                        let price = parseFloat(product.price || 0).toFixed(2);
                        let displayPrice = currencyPosition === 'right' ? `${price}${currencySymbol}` :
                            `${currencySymbol}${price}`;

                        // Check if product is out of stock
                        let outOfStockBadge = '';
                        let disabledClass = '';
                        let addButtonDisabled = '';

                        if (Number(product.quantity) === 0) {
                            outOfStockBadge = `<div style="
                                                position: absolute;
                                                top: 10px;
                                                left: 10px;
                                                background: rgba(255,0,0,0.85);
                                                color: white;
                                                font-weight: 700;
                                                padding: 2px 8px;
                                                border-radius: 4px;
                                                font-size: 12px;
                                                z-index: 10;
                                                ">Out of Stock</div>`;

                            disabledClass = 'disabled-product'; // optional styling class
                            addButtonDisabled = 'disabled'; // disable button
                        }

                        productsHtml += `
                                                    <div class="col-lg-4 col-md-6 col-sm-6 d-flex position-relative product-card-col">
                                                        <div class="productset flex-fill text-center ${disabledClass}" data-id="${product.id}" data-stock="${product.quantity}" data-quantity_type_id="${product.quantity_type_id || ''}" style="position: relative;">
                                                            ${outOfStockBadge}
                                                            <div class="productsetimg">
                                                                <img src="${productImage}" alt="${product.name}" class="productsetimgin">
                                                                <h6>Qty: ${product.quantity || '0'}</h6>
                                                            </div>
                                                            <div class="productsetcontent">
                                                                <h4>${product.name}</h4>
                                                                <h1 class="d-none">${product.categoryId || 'Uncategorized'}</h1>
                                                                <h2 class="d-none">${product.quantity_type || 'pcs'}</h2>
                                                                <h3 class="d-none">${product.quantity_type_id || 'pcs'}</h3>

                                                                <div class="product-category-subtitle">
                                                                    ${product.category_name || 'Uncategorized'}
                                                                </div>
                                                                <h6>${displayPrice}</h6>
                                                            </div>
                                                            <div class="productsetbtn">
                                                                <button class="btn btn-added" ${addButtonDisabled}>+ Add</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                    });
                }

                // Update the tab content
                var $tabContent = $('.tab_content[data-tab="' + categoryId + '"]');
                if ($tabContent.length) {
                    $tabContent.html('<div class="row">' + productsHtml + '</div>');
                } else {

                }
            }

            function exitTableProductSearchMode() {
                $('#tableOrderModal .tabs_container').removeClass('is-searching');
                $('#tableProductSearchResults').removeClass('is-visible').empty();
            }

            function getAllCachedProducts() {
                var all = [];
                var seen = {};

                Object.keys(tableOrderProductsCache).forEach(function (categoryId) {
                    (tableOrderProductsCache[categoryId] || []).forEach(function (product) {
                        var key = String(product.id);
                        if (!seen[key]) {
                            seen[key] = true;
                            all.push(product);
                        }
                    });
                });

                return all;
            }

            function applyTableProductSearch() {
                var $input = $('#tableProductSearch');
                if (!$input.length) {
                    return;
                }

                var query = ($input.val() || '').toString().trim().toLowerCase();
                var $wrap = $input.closest('.table-order-product-search');
                $wrap.toggleClass('has-value', query.length > 0);

                if (!query) {
                    exitTableProductSearchMode();
                    return;
                }

                var requestId = ++tableProductSearchRequestId;
                var $results = $('#tableProductSearchResults');
                $results.addClass('is-visible').html(
                    '<div class="product-search-empty" style="display:block;">Searching products...</div>'
                );
                $('#tableOrderModal .tabs_container').addClass('is-searching');

                ensureAllCategoryProductsLoaded().always(function () {
                    if (requestId !== tableProductSearchRequestId) {
                        return;
                    }

                    var currentQuery = ($input.val() || '').toString().trim().toLowerCase();
                    if (!currentQuery) {
                        exitTableProductSearchMode();
                        return;
                    }

                    var matched = getAllCachedProducts().filter(function (product) {
                        var name = (product.name || '').toString().toLowerCase();
                        var categoryName = (product.category_name || '').toString().toLowerCase();
                        return name.indexOf(currentQuery) !== -1 || categoryName.indexOf(currentQuery) !== -1;
                    });

                    if (!matched.length) {
                        $results.html('<div class="product-search-empty" style="display:block;">No products match your search</div>');
                        return;
                    }

                    // Reuse renderProducts card markup into a temporary tab, then move HTML
                    var tempTabId = 'search-results-temp';
                    var $temp = $('.tab_content[data-tab="' + tempTabId + '"]');
                    if (!$temp.length) {
                        $temp = $('<div class="tab_content" data-tab="' + tempTabId + '" style="display:none;"></div>');
                        $('#tableOrderModal .tabs_container').append($temp);
                    }

                    renderProducts(
                        matched,
                        tempTabId,
                        tableOrderProductCurrency.symbol,
                        tableOrderProductCurrency.position
                    );

                    $results.html($temp.html());
                    $temp.remove();
                });
            }

            $(document).on('input', '#tableProductSearch', function () {
                clearTimeout(tableProductSearchTimer);
                tableProductSearchTimer = setTimeout(function () {
                    applyTableProductSearch();
                }, 250);
            });

            $(document).on('click', '.clear-product-search', function (e) {
                e.preventDefault();
                clearTimeout(tableProductSearchTimer);
                $('#tableProductSearch').val('');
                $('.table-order-product-search').removeClass('has-value');
                exitTableProductSearchMode();
                $('#tableProductSearch').focus();
            });

            function calculateTotals() {
                const firstTaxElement = $(".tax-value").first();
                const currencySymbol = firstTaxElement.data("symbol") || '{{ $currency_symbol }}'; // fallback
                const currencyPosition = firstTaxElement.data("position") ||
                    '{{ $currency_position }}'; // fallback

                let subtotal = 0;

                // Calculate subtotal from selected items
                selectedItems.forEach(function (item) {
                    const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                    subtotal += price * item.quantity;
                });

                // Update subtotal display
                $(".setvalue li:nth-child(1) h6").html(
                    formatCurrency(subtotal.toFixed(2), currencySymbol, currencyPosition)
                );
                $("input[name='subtotal']").val(subtotal.toFixed(2));

                if (subtotal > 0) {
                    // ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â¦ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦ Calculate discount & price after discount FIRST
                    const discountPercentage = parseFloat($("#discount").val()) || 0;
                    const discountAmount = subtotal * (discountPercentage / 100);
                    const priceAfterDiscount = subtotal - discountAmount;

                    let totalTax = 0;
                    const gstOption = $("input[name='gst_option']:checked").val();

                    if (gstOption === "with") {
                        $(".taxList").each(function () {
                            const taxRate = parseFloat($(this).find(".tax-value").data("rate")) / 100;

                            // ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â¦ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦ Tax on price after discount
                            const taxAmount = priceAfterDiscount * taxRate;

                            const taxEl = $(this).find(".tax-value");
                            const symbol = taxEl.data("symbol") || currencySymbol;
                            const position = taxEl.data("position") || currencyPosition;

                            taxEl.html(formatCurrency(taxAmount.toFixed(2), symbol, position));
                            $(this).show();

                            totalTax += taxAmount;
                        });
                    } else {
                        // Hide and reset tax values if GST = without
                        $(".taxList")
                            .hide()
                            .find(".tax-value")
                            .html(formatCurrency("0.00", currencySymbol, currencyPosition));
                        totalTax = 0;
                    }

                    // ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â¦ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦ Final total (price after discount + tax + shipping)
                    const shipping = parseFloat($("#shipping").val()) || 0;
                    const total = priceAfterDiscount + totalTax + shipping;
                    $("#cashAmount").on("input", function () {
                        let cash = parseFloat($(this).val()) || 0;
                        let remaining = total - cash;

                        $("#paymentError").remove();

                        if (remaining < 0) {
                            $("#cashOnlineBox").append(
                                '<span id="paymentError" style="color:red; font-size:13px;">' +
                                'Cash cannot be greater than ' +
                                formatCurrency(total.toFixed(2), currencySymbol, currencyPosition) +
                                '</span>'
                            );
                            $("#onlineAmount").val(0);
                        } else {
                            $("#onlineAmount").val(remaining.toFixed(2));
                        }
                    });
                    // Update values
                    $(".discount-amount").html(formatCurrency(discountAmount.toFixed(2), currencySymbol,
                        currencyPosition));
                    $("input[name='discount_amount']").val(discountAmount.toFixed(2));

                    $(".price-after-discount").html(formatCurrency(priceAfterDiscount.toFixed(2), currencySymbol,
                        currencyPosition));
                    $("input[name='price_after_discount']").val(priceAfterDiscount.toFixed(2));

                    $(".total-value h6").html(formatCurrency(total.toFixed(2), currencySymbol, currencyPosition));
                    const payableText = formatCurrency(total.toFixed(2), currencySymbol, currencyPosition);
                    $(".btn-totallabel h6").html('Place Order · ' + payableText);
                    $(".btn-totallabel_1 h6").html('Update · ' + payableText);
                    $(".btn-totallabel_2 h6").html('Checkout · ' + payableText);
                    $("input[name='total']").val(total.toFixed(2));
                    $("#onlineAmount").val(total.toFixed(2));
                } else {
                    // Reset everything if no subtotal
                    $(".taxList").hide().find("h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                    $(".discount-amount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                    $(".price-after-discount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                    $(".total-value h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                    const zeroPayable = formatCurrency("0.00", currencySymbol, currencyPosition);
                    $(".btn-totallabel h6").html('Place Order · ' + zeroPayable);
                    $(".btn-totallabel_1 h6").html('Update · ' + zeroPayable);
                    $(".btn-totallabel_2 h6").html('Checkout · ' + zeroPayable);

                    $("input[name='discount_amount']").val("0.00");
                    $("input[name='price_after_discount']").val("0.00");
                    $("input[name='total']").val("0.00");
                    $("#onlineAmount").val("0.00");
                }
            }

            window.updateTotalItems = function () {
                $(".totalitem h4").text("Total items : " + selectedItems.size);
                renderSelectedItems();
                calculateTotals();
            }

            function renderSelectedItems() {
                var $productTable = $(".product-table");
                var productHtml = "";

                selectedItems.forEach(function (item) {

                    productHtml += `
                                        <ul class="product-lists">
                                            <li>
                                                <div class="productimg">
                                                    <div class="productimgs">
                                                        <img src="${item.image}" alt="${item.name}">
                                                    </div>
                                                    <div class="productcontet">
                                                        <h4 style="text-transform: capitalize;">${item.name}
                                                            <a href="javascript:void(0);" class="ms-2 edit-item" data-id="${item.id}">
                                                                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit-5.svg' }}" alt="edit">
                                                            </a>
                                                        </h4>

                                                        <div class="increment-decrement">
                                                            <div class="input-groups">
                                                                <input type="button" value="-" class="button-minus dec button" data-id="${item.id}">
                                                                <input type="text" name="quantity" value="${item.quantity}" class="quantity-field" data-id="${item.id}" data-stock="${item.stock}">
                                                                <input type="button" value="+" class="button-plus inc button" data-id="${item.id}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="quantity_type text-uppercase">${item.quantity_type || item.quantity_type_id}</li>

                                            <li class="price">${item.price}</li>
                                            <li>
                                                <a class="confirm-text remove-item" href="javascript:void(0);" data-id="${item.id}">
                                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete-2.svg' }}" alt="delete">
                                                </a>
                                            </li>
                                        </ul>`;
                });

                $productTable.html(productHtml);
            }

            function renderTableOrders() {
                var $product = $(this);
                var productId = $product.data("id");
                var productName = $product.find("h4").text().trim();
                var productPrice = $product.find(".productsetcontent h6").text().trim();
                var categoryId = $product.find(".productsetcontent h1").text().trim();
                var quantity_type = $product.find(".productsetcontent h2").text().trim();
                var quantity_type123 = $product.find(".productsetcontent h3").text().trim();

                var productImage = $product.find("img").attr("src");
                var productCode = "PT001";
                var $checkIcon = $product.find(".check-product i");
                var productStock = parseInt($product.data("stock")); // assuming data-stock is set
                var quantity_type_id = $product.data("quantity_type_id");
                console.log(quantity_type_id);
                console.log(quantity_type);

                var uniqueId = productId;
                selectedItems.set(uniqueId, {
                    id: uniqueId,
                    productId: productId,
                    categoryId: categoryId,
                    name: productName,
                    price: productPrice,
                    image: productImage,
                    code: productCode,
                    quantity_type: quantity_type,
                    quantity_type_id: quantity_type_id,
                    quantity_type123: quantity_type123,
                    quantity: 1,
                    stock: productStock // <-- Add stock

                });

                $checkIcon.addClass("selected");
                updateTotalItems();
            }

            $(document).on("click", ".productset", function () {
                var $product = $(this);
                var productId = $product.data("id");
                var productName = $product.find("h4").text().trim();
                var productPrice = $product.find(".productsetcontent h6").text().trim();
                var categoryId = $product.find(".productsetcontent h1").text().trim();
                var quantity_type = $product.find(".productsetcontent h2").text().trim();
                var quantity_type123 = $product.find(".productsetcontent h3").text().trim();

                var productImage = $product.find("img").attr("src");
                var productCode = "PT001";
                var $checkIcon = $product.find(".check-product i");
                var productStock = parseInt($product.data("stock")); // assuming data-stock is set
                var quantity_type_id = $product.data("quantity_type_id");


                //var apiQty = parseInt($product.attr("data-selected-qty")) || 1;

                //var uniqueId = productId;
                //selectedItems.set(uniqueId, {
                    // id: uniqueId,
                    // productId: productId,
                    // categoryId: categoryId,
                    // name: productName,
                    // price: productPrice,
                    // image: productImage,
                    // code: productCode,
                    // quantity_type: quantity_type,
                    // quantity_type_id: quantity_type_id,
                    // quantity_type123: quantity_type123,
                    // quantity: apiQty,
                   // stock: productStock // <-- Add stock

                //});
                var uniqueId = productId;
                var apiQtyAttr = $product.attr("data-selected-qty");
                var apiQty = apiQtyAttr !== undefined && apiQtyAttr !== null && apiQtyAttr !== ''
                    ? parseInt(apiQtyAttr, 10)
                    : NaN;

                if (!isNaN(apiQty) && apiQty > 0) {
                    // Restoring quantity from an existing order — set exact qty, do not increment
                    selectedItems.set(uniqueId, {
                        id: uniqueId,
                        productId: productId,
                        categoryId: categoryId,
                        name: productName,
                        price: productPrice,
                        image: productImage,
                        code: productCode,
                        quantity_type: quantity_type,
                        quantity_type_id: quantity_type_id,
                        quantity_type123: quantity_type123,
                        quantity: apiQty,
                        stock: productStock
                    });
                } else if (selectedItems.has(uniqueId)) {
                    let item = selectedItems.get(uniqueId);
                    item.quantity += 1;
                    selectedItems.set(uniqueId, item);
                } else {
                    selectedItems.set(uniqueId, {
                        id: uniqueId,
                        productId: productId,
                        categoryId: categoryId,
                        name: productName,
                        price: productPrice,
                        image: productImage,
                        code: productCode,
                        quantity_type: quantity_type,
                        quantity_type_id: quantity_type_id,
                        quantity_type123: quantity_type123,
                        quantity: 1,
                        stock: productStock
                    });
                }

                $product.removeAttr("data-selected-qty");
                $checkIcon.addClass("selected");
                updateTotalItems();
            });

            $(document).on("click", ".button-plus", function () {
                var itemId = $(this).data("id");
                if (selectedItems.has(itemId)) {
                    var item = selectedItems.get(itemId);
                    if (item.quantity < item.stock) {
                        item.quantity++;
                        selectedItems.set(itemId, item);
                        updateTotalItems();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock Limit Reached',
                            text: 'Cannot exceed available stock (' + item.stock + ')',
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });

            $(document).on("change", ".quantity-field", function () {
                var itemId = $(this).data("id");
                var newQty = parseInt($(this).val());

                if (selectedItems.has(itemId)) {
                    var item = selectedItems.get(itemId);

                    if (newQty > item.stock) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock Quantity Exceeded',
                            text: 'Only ' + item.stock + ' quantity are available.',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                        newQty = item.stock;
                    } else if (newQty < 1 || isNaN(newQty)) {
                        newQty = 1;
                    }

                    item.quantity = newQty;
                    selectedItems.set(itemId, item);
                    updateTotalItems();
                    $(this).val(newQty); // update input box with valid value
                }
            });

            $(document).on("click", ".button-minus", function () {
                var itemId = $(this).data("id");
                if (selectedItems.has(itemId) && selectedItems.get(itemId).quantity > 1) {
                    selectedItems.get(itemId).quantity--;
                    updateTotalItems();
                }
            });

            $(document).on("click", ".remove-item", function () {
                var itemId = $(this).data("id");
                var item = selectedItems.get(itemId);
                if (item) {
                    $(".productset[data-id='" + item.productId + "'] .check-product i").removeClass(
                        "selected");
                    selectedItems.delete(itemId);
                    updateTotalItems();
                }
            });

            $(".clear_items").click(function () {
                selectedItems.clear();
                $(".check-product i").removeClass("selected");
                updateTotalItems();
            });

            $("#discount").on("input", function () {
                const discountValue = parseFloat($(this).val());

                if ($(this).val() === "") {
                    // If empty, treat as 0 and remove error
                    $(".error_discount").text("");
                    $("#discount").val(""); // keep it blank for UX
                    calculateTotals(); // recalculate with 0 discount
                    return;
                }

                if (discountValue < 0 || discountValue > 100) {
                    $(".error_discount").text("Discount must be between 0-100%").css("color", "red");
                    $(this).val("");
                } else if (isNaN(discountValue)) {
                    $(".error_discount").text("");
                } else {
                    $(".error_discount").text("Discount Applied").css("color", "green");
                    calculateTotals();
                }
            });
            $(document).on("change", "input[name='gst_option']", function () {
                let gstOption = $("input[name='gst_option']:checked").val();

                if (gstOption === "with") {
                    $(".taxList").show();
                } else {
                    $(".taxList").hide();
                }

                calculateTotals(); // recalc totals with/without tax
            });

            $(".btn-totallabel").click(function (event) {
                event.preventDefault();
                var $btn = $(this); // cache the button
                var originalText = $btn.html();
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                var table_status = "dinein";
                // Show loading text and disable the button
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Placing Order...'
                ).prop('disabled', true);

                // Reset errors
                $(".error_customername, .error_customerphone, .error_peymentmethod, .error_discount").text(
                    "");
                $(".total-value .error_total").remove();

                // Get form values
                // let name = $("input[name='customer_name']").val().trim();
                let name = $("#customer_name option:selected").val();
                let selectedPayment = $("input[name='payment_method']:checked").val();
                let subtotal = parseFloat($("input[name='subtotal']").val()) || 0;
                let total = parseFloat($("input[name='total']").val()) || 0;
                let discount = parseFloat($("#discount").val()) || 0;

                // Validation
                let isValid = true;

                // if (name === "") {
                //     $(".error_customername").text("Customer name is required").css("color", "red");
                //     isValid = false;
                // }

                // if (!selectedPayment) {
                //     $(".error_peymentmethod").text("Please select a payment method").css("color", "red");
                //     isValid = false;
                // }

                // if (subtotal <= 0) {
                //     $(".total-value").append("<span class='error_total' style='color:red; display:block;'>Please select at least one product</span>");
                //     isValid = false;
                // }
                if (subtotal <= 0) {
                    $(".error_total")
                        .text("Please select at least one product")
                        .show();
                    isValid = false;
                } else {
                    $(".error_total").hide().text("");
                }


                // if (!isValid) return;
                if (!isValid) {
                    $btn.html(originalText).prop('disabled', false); // <-- important line
                    return;
                }


                // Prepare order items data
                let orderItems = [];
                selectedItems.forEach(function (item) {


                    const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                    orderItems.push({
                        product_id: item.productId,
                        quantity: item.quantity,
                        quantity_type_id: item.quantity_type,
                        quantity_type123: item.quantity_type123,

                        categoryId: item.categoryId,
                        price: price,
                        total: price * item.quantity
                    });
                });

                // Prepare tax data
                // let taxes = [];
                // $(".taxList").each(function () {

                //     const taxName = $(this).find("h5").text().replace(" Tax", "");
                //     const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g, ""));


                //     const taxRate = parseFloat($(this).find(".tax-value").data("rate"));
                //     taxes.push({
                //         tax_name: taxName,
                //         rate: taxRate,
                //         amount: taxAmount
                //     });
                // });
                // Prepare tax data
                let taxes = [];
                let gstOption = $("input[name='gst_option']:checked").val();

                if (gstOption === "with") {
                    $(".taxList").each(function () {
                        const taxName = $(this).find("h5").text().replace(" Tax", "");
                        const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g,
                            "")) || 0;
                        const taxRate = parseFloat($(this).find(".tax-value").data("rate")) || 0;

                        taxes.push({
                            tax_name: taxName,
                            rate: taxRate,
                            amount: taxAmount
                        });
                    });
                } else {
                    taxes = []; // ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â¦ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦ explicitly empty when "without GST"
                }


                let cash = parseFloat($("#cashAmount").val()) || 0;
                let online = parseFloat($("#onlineAmount").val()) || 0;


                // Prepare order data
                let orderData = {
                    selectedSubAdminId: selectedSubAdminId,
                    customer_id: '2',
                    payment_method: selectedPayment,
                    subtotal: subtotal,
                    discount: discount,
                    discount_amount: subtotal * (discount / 100),
                    tax: taxes,
                    gst_option: gstOption,
                    total: total,
                    cash_amount: cash,
                    online_amount: online,
                    items: orderItems,
                    table_type: table_status,
                    table_id: window.selectedTableId || null,

                };

                // Submit order via AJAX
                $.ajax({
                    url: "/api/order_sale",
                    type: "POST",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify(orderData),
                    success: function (response) {
                        $btn.html(originalText).prop('disabled', false);
                        if (response.status) {
                            orderId = response.order_id;
                            if (window.selectedTableId) {
                                updateTableCardStatus(
                                    window.selectedTableId,
                                    response.table_status_class || 'blank-table',
                                    response.table_status || 'blank',
                                    response.table_status_class === 'running-table' ? response.order_id : '',
                                    response.total_amount
                                );
                            }
                            // Reset form
                            selectedItems.clear();
                            $(".check-product i").removeClass("selected");
                            $("input[name='payment_method']").prop("checked", false);
                            $(".paymentmethod").removeClass("active");
                            $("#cashOnlineBox").hide();
                            $("#discount").val("");
                            updateTotalItems();
                            // Close modal immediately, then show toast
                            if (tableOrderModal) tableOrderModal.hide();
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Order placed successfully!',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            }).then(() => { location.reload(); });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    // error: function(xhr, status, error) {
                    //     // Re-enable and reset the button
                    //     $btn.html(originalText).prop('disabled', false);

                    //     alert("Error placing order. Please try again.");
                    // }
                    error: function (xhr, status, error) {
                        $btn.html(originalText).prop('disabled', false);

                        // Try to extract error message from response
                        let response = xhr.responseJSON;

                        if (response && response.message && response.message.includes("Duplicate entry")) {
                            // Detect duplicate phone number error
                            $(".error_customerphone").text("This phone number is already registered. Please use another one.").css("color", "red");
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response && response.message ? response.message : "Error placing order. Please try again.",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    }
                });
            });

            $(".btn-totallabel_1").click(function (event) {
                event.preventDefault();
                var $btn = $(this); // cache the button
                var originalText = $btn.html();
                var update_id = $(this).data("order_update_id");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                var table_status = "dinein";
                // Show loading text and disable the button
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Placing Order...'
                ).prop('disabled', true);

                // Reset errors
                $(".error_customername, .error_customerphone, .error_peymentmethod, .error_discount").text(
                    "");
                $(".total-value .error_total").remove();

                // Get form values
                // let name = $("input[name='customer_name']").val().trim();
                let name = $("#customer_name option:selected").val();
                let selectedPayment = $("input[name='payment_method']:checked").val() || "pending";
                let subtotal = parseFloat($("input[name='subtotal']").val()) || 0;
                let total = parseFloat($("input[name='total']").val()) || 0;
                let discount = parseFloat($("#discount").val()) || 0;

                // Validation
                let isValid = true;

                // if (name === "") {
                //     $(".error_customername").text("Customer name is required").css("color", "red");
                //     isValid = false;
                // }

                // if (!selectedPayment) {
                //     $(".error_peymentmethod").text("Please select a payment method").css("color", "red");
                //     isValid = false;
                // }

                // if (subtotal <= 0) {
                //     $(".total-value").append("<span class='error_total' style='color:red; display:block;'>Please select at least one product</span>");
                //     isValid = false;
                // }
                if (subtotal <= 0) {
                    $(".error_total")
                        .text("Please select at least one product")
                        .show();
                    isValid = false;
                } else {
                    $(".error_total").hide().text("");
                }


                // if (!isValid) return;
                if (!isValid) {
                    $btn.html(originalText).prop('disabled', false); // <-- important line
                    return;
                }


                // Prepare order items data
                let orderItems = [];
                selectedItems.forEach(function (item) {


                    const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                    orderItems.push({
                        product_id: item.productId,
                        quantity: item.quantity,
                        quantity_type_id: item.quantity_type,
                        quantity_type123: item.quantity_type123,

                        categoryId: item.categoryId,
                        price: price,
                        total: price * item.quantity
                    });
                });

                // Prepare tax data
                // let taxes = [];
                // $(".taxList").each(function () {

                //     const taxName = $(this).find("h5").text().replace(" Tax", "");
                //     const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g, ""));


                //     const taxRate = parseFloat($(this).find(".tax-value").data("rate"));
                //     taxes.push({
                //         tax_name: taxName,
                //         rate: taxRate,
                //         amount: taxAmount
                //     });
                // });
                // Prepare tax data
                let taxes = [];
                let gstOption = $("input[name='gst_option']:checked").val();

                if (gstOption === "with") {
                    $(".taxList").each(function () {
                        const taxName = $(this).find("h5").text().replace(" Tax", "");
                        const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g,
                            "")) || 0;
                        const taxRate = parseFloat($(this).find(".tax-value").data("rate")) || 0;

                        taxes.push({
                            tax_name: taxName,
                            rate: taxRate,
                            amount: taxAmount
                        });
                    });
                } else {
                    taxes = []; // ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â¦ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦ explicitly empty when "without GST"
                }


                let cash = parseFloat($("#cashAmount").val()) || 0;
                let online = parseFloat($("#onlineAmount").val()) || 0;


                // Prepare order data
                let orderData = {
                    selectedSubAdminId: selectedSubAdminId,
                    update_id: update_id,
                    customer_id: '2',
                    payment_method: selectedPayment,
                    subtotal: subtotal,
                    discount: discount,
                    discount_amount: subtotal * (discount / 100),
                    tax: taxes,
                    gst_option: gstOption,
                    total: total,
                    cash_amount: cash,
                    online_amount: online,
                    items: orderItems,
                    table_type: table_status,
                    table_id: window.selectedTableId || null,

                };

                // Submit order via AJAX
                $.ajax({
                    url: "/api/update_sale_order",
                    type: "POST",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify(orderData),
                    success: function (response) {
                        $btn.html(originalText).prop('disabled', false);
                        if (response.status) {
                            orderId = response.order_id;
                            if (window.selectedTableId) {
                                updateTableCardStatus(
                                    window.selectedTableId,
                                    response.table_status_class || 'blank-table',
                                    response.table_status || 'blank',
                                    response.table_status_class === 'running-table' ? response.order_id : '',
                                    response.total_amount
                                );
                            }
                            // Reset form
                            selectedItems.clear();
                            $(".check-product i").removeClass("selected");
                            $("input[name='payment_method']").prop("checked", false);
                            $(".paymentmethod").removeClass("active");
                            $("#cashOnlineBox").hide();
                            $("#discount").val("");
                            updateTotalItems();
                            // Close modal immediately, then show toast
                            if (tableOrderModal) tableOrderModal.hide();
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Order updated successfully!',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            }).then(() => { location.reload(); });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        $btn.html(originalText).prop('disabled', false);
                        let response = xhr.responseJSON;
                        if (response && response.message && response.message.includes("Duplicate entry")) {
                            $(".error_customerphone").text("This phone number is already registered. Please use another one.").css("color", "red");
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response && response.message ? response.message : "Error placing order. Please try again.",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    }
                });
            });

            $(".btn-totallabel_2").click(function (event) {
                event.preventDefault();
                var $btn = $(this); // cache the button
                var originalText = $btn.html();
                var update_id = $(this).data("order_payment_update_id");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                var table_status = "dinein";
                // Show loading text and disable the button
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Placing Order...'
                ).prop('disabled', true);

                // Reset errors
                $(".error_customername, .error_customerphone, .error_peymentmethod, .error_discount").text(
                    "");
                $(".total-value .error_total").remove();

                // Get form values
                // let name = $("input[name='customer_name']").val().trim();
                let name = $("#customer_name option:selected").val();
                let selectedPayment = $("input[name='payment_method']:checked").val();
                let subtotal = parseFloat($("input[name='subtotal']").val()) || 0;
                let total = parseFloat($("input[name='total']").val()) || 0;
                let discount = parseFloat($("#discount").val()) || 0;

                // Validation
                let isValid = true;

                // if (name === "") {
                //     $(".error_customername").text("Customer name is required").css("color", "red");
                //     isValid = false;
                // }
                // console.log(selectedPayment);


                if (!selectedPayment || selectedPayment === 'pending') {
                    $(".error_peymentmethod").text("Please select a payment method").css("color", "red");
                    isValid = false;
                }

                // return false;

                // if (subtotal <= 0) {
                //     $(".total-value").append("<span class='error_total' style='color:red; display:block;'>Please select at least one product</span>");
                //     isValid = false;
                // }
                if (subtotal <= 0) {
                    $(".error_total")
                        .text("Please select at least one product")
                        .show();
                    isValid = false;
                } else {
                    $(".error_total").hide().text("");
                }


                // if (!isValid) return;
                if (!isValid) {
                    $btn.html(originalText).prop('disabled', false); // <-- important line
                    return;
                }


                // Prepare order items data
                let orderItems = [];
                selectedItems.forEach(function (item) {


                    const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                    orderItems.push({
                        product_id: item.productId,
                        quantity: item.quantity,
                        quantity_type_id: item.quantity_type,
                        quantity_type123: item.quantity_type123,

                        categoryId: item.categoryId,
                        price: price,
                        total: price * item.quantity
                    });
                });

                // Prepare tax data
                // let taxes = [];
                // $(".taxList").each(function () {

                //     const taxName = $(this).find("h5").text().replace(" Tax", "");
                //     const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g, ""));


                //     const taxRate = parseFloat($(this).find(".tax-value").data("rate"));
                //     taxes.push({
                //         tax_name: taxName,
                //         rate: taxRate,
                //         amount: taxAmount
                //     });
                // });
                // Prepare tax data
                let taxes = [];
                let gstOption = $("input[name='gst_option']:checked").val();

                if (gstOption === "with") {
                    $(".taxList").each(function () {
                        const taxName = $(this).find("h5").text().replace(" Tax", "");
                        const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g,
                            "")) || 0;
                        const taxRate = parseFloat($(this).find(".tax-value").data("rate")) || 0;

                        taxes.push({
                            tax_name: taxName,
                            rate: taxRate,
                            amount: taxAmount
                        });
                    });
                } else {
                    taxes = []; // ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â¦ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦ explicitly empty when "without GST"
                }


                let cash = parseFloat($("#cashAmount").val()) || 0;
                let online = parseFloat($("#onlineAmount").val()) || 0;


                // Prepare order data
                let orderData = {
                    selectedSubAdminId: selectedSubAdminId,
                    update_id: update_id,
                    customer_id: '2',
                    payment_method: selectedPayment,
                    subtotal: subtotal,
                    discount: discount,
                    discount_amount: subtotal * (discount / 100),
                    tax: taxes,
                    gst_option: gstOption,
                    total: total,
                    cash_amount: cash,
                    online_amount: online,
                    items: orderItems,
                    table_type: table_status,
                    table_checkout: 'checkout',
                    table_id: window.selectedTableId || null,

                };

                // Submit order via AJAX
                $.ajax({
                    url: "/api/update_sale_order",
                    type: "POST",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify(orderData),
                    success: function (response) {
                        $btn.html(originalText).prop('disabled', false);
                        if (response.status) {
                            orderId = response.order_id;
                            if (window.selectedTableId) {
                                updateTableCardStatus(
                                    window.selectedTableId,
                                    response.table_status_class || 'blank-table',
                                    response.table_status || 'blank',
                                    response.table_status_class === 'running-table' ? response.order_id : '',
                                    response.total_amount
                                );
                            }
                            // Reset form
                            selectedItems.clear();
                            $(".check-product i").removeClass("selected");
                            $("input[name='payment_method']").prop("checked", false);
                            $(".paymentmethod").removeClass("active");
                            $("#cashOnlineBox").hide();
                            $("#discount").val("");
                            updateTotalItems();
                            // Close modal immediately, show toast, then open bill PDF
                            if (tableOrderModal) tableOrderModal.hide();
                            // Open the bill PDF in a new tab right away (before toast)
                            var billOrderId = response.order_id || orderId;
                            if (billOrderId) {
                                window.open('/table/bill/' + billOrderId, '_blank');
                            }
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Checkout successful!',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            }).then(() => { location.reload(); });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        $btn.html(originalText).prop('disabled', false);

                        // Try to extract error message from response
                        let response = xhr.responseJSON;

                        if (response && response.message && response.message.includes("Duplicate entry")) {
                            // Detect duplicate phone number error
                            $(".error_customerphone").text("This phone number is already registered. Please use another one.").css("color", "red");
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response && response.message ? response.message : "Error placing order. Please try again.",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    }
                });
            });

            $(".owl-carousel").owlCarousel({
                items: 5,
                loop: false,
                nav: true,
                dots: false,
                margin: 10
            });

            // Mobile Order Details Toggle Icon
            $(document).on('click', '.toggle-details', function() {
                var $icon = $(this).find('i');
                if ($icon.hasClass('fa-plus-circle')) {
                    $icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                } else {
                    $icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                }
            });

        });

    </script>
@endpush
