    @extends('layout.app')

    @section('title', 'Add Order')

    @section('content')
    <style>
        /* =========================================================
           MODERN HOTEL ERP POS DESIGN SYSTEM (Main-ERP Inspired)
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

        /* Container Layout */
        .page-header {
            background: #ffffff;
            border-radius: 10px;
            padding: 8px 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--pos-border);
            margin-bottom: 8px;
            min-height: 46px;
            gap: 10px;
            overflow: visible !important;
            position: relative;
            z-index: 30;
        }
        .page-header .pos-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            min-width: 0;
        }
        .page-header .pos-header-divider {
            width: 1px;
            height: 22px;
            background: #e2e8f0;
            flex-shrink: 0;
        }
        .pos-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1.5px solid var(--pos-primary);
            background: var(--pos-primary);
            color: #ffffff !important;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            height: 30px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(255, 159, 67, 0.25);
            text-decoration: none;
        }
        .pos-back-btn:hover {
            background: var(--pos-primary-hover);
            border-color: var(--pos-primary-hover);
            transform: translateX(-2px);
            box-shadow: 0 4px 10px rgba(255, 159, 67, 0.35);
        }

        /* --- Category Tabs (Single Row with Horizontal Scrollbar) --- */
        .tabs_wrapper {
            overflow: visible;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 0;
            max-height: calc(100vh - 88px);
        }
        .tabs_wrapper ul.tabs {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 10px !important;
            /* Extra bottom padding so scrollbar sits UNDER pills, not through them */
            padding: 6px 4px 12px !important;
            margin: 0 0 8px 0 !important;
            list-style: none !important;
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin !important;
            scrollbar-color: #cbd5e1 transparent !important;
            scrollbar-gutter: stable;
        }
        .tabs_wrapper ul.tabs::-webkit-scrollbar {
            display: block !important;
            height: 4px !important;
            background: transparent !important;
        }
        .tabs_wrapper ul.tabs::-webkit-scrollbar-button {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .tabs_wrapper ul.tabs::-webkit-scrollbar-track {
            background: #f1f5f9 !important;
            border-radius: 10px !important;
            margin: 0 4px;
        }
        .tabs_wrapper ul.tabs::-webkit-scrollbar-thumb {
            background: #cbd5e1 !important;
            border-radius: 10px !important;
        }
        .tabs_wrapper ul.tabs::-webkit-scrollbar-thumb:hover {
            background: #ff9f43 !important;
        }
        .tabs_wrapper ul.tabs li {
            width: auto !important;
            max-width: max-content !important;
            flex: 0 0 auto !important;
            cursor: pointer !important;
            white-space: nowrap !important;
            padding: 0 !important;
            margin: 0 !important;
            transition: transform 0.2s ease !important;
            align-self: center !important;
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            width: auto !important;
            min-width: max-content;
        }
        .product-details h6 {
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            margin: 0;
            text-align: center;
            white-space: nowrap;
            transition: color 0.2s ease;
        }
        .tabs_wrapper ul.tabs li:hover .product-details {
            border-color: var(--pos-primary) !important;
            background: var(--pos-primary-light);
        }
        .tabs_wrapper ul.tabs li:hover .product-details h6 {
            color: var(--pos-primary);
        }
        .tabs_wrapper ul.tabs li.active .product-details {
            background: linear-gradient(135deg, #ff9f43 0%, #ff8510 100%) !important;
            border-color: transparent !important;
            box-shadow: 0 4px 12px rgba(255, 159, 67, 0.35);
            transform: translateY(-1px);
        }
        .tabs_wrapper ul.tabs li.active .product-details h6 {
            color: #ffffff !important;
            font-weight: 700;
        }

        /* Override theme inline-block (clips product rows mid-card) */
        .tabs_wrapper .tabs_container,
        .tabs_container {
            display: block !important;
            width: 100% !important;
            flex: 1 1 auto;
            max-height: calc(100vh - 180px);
            min-height: 240px;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding-right: 4px;
            padding-bottom: 28px;
            -webkit-overflow-scrolling: touch;
        }
        /* Inactive tabs must stay hidden — only .active is shown */
        .tabs_wrapper .tabs_container .tab_content,
        .tabs_container .tab_content {
            display: none !important;
            width: 100% !important;
            float: none !important;
        }
        .tabs_wrapper .tabs_container .tab_content.active,
        .tabs_container .tab_content.active {
            display: block !important;
            width: 100% !important;
            float: none !important;
        }
        .tabs_container .tab_content > .row {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            margin-left: -6px;
            margin-right: -6px;
        }
        .tabs_container .tab_content > .row > [class*="col-"] {
            padding-left: 6px;
            padding-right: 6px;
            display: flex;
        }
        .tabs_container .tab_content > .row > [class*="col-"] > .productset {
            width: 100%;
            min-height: 168px;
        }
        .tabs_container::-webkit-scrollbar {
            width: 6px;
        }
        .tabs_container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .tabs_container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .tabs_container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* --- Food / Product Card --- */
        /* --- Food / Product Card --- */
        .productset {
            background: #ffffff;
            border: 1.5px solid #edf2f7;
            border-radius: 12px;
            padding: 8px !important;
            margin-bottom: 12px !important;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            text-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            height: auto !important;
        }
        .productset:hover {
            transform: translateY(-3px);
            border-color: var(--pos-primary);
            box-shadow: 0 8px 24px rgba(255, 159, 67, 0.18);
        }
        .productset.active {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 2px rgba(255, 159, 67, 0.25);
        }
        .productset .productsetimg {
            background: var(--pos-bg-subtle);
            border-radius: 8px;
            height: 90px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 4px !important;
            overflow: hidden;
        }
        .productsetimgin {
            max-height: 80px !important;
            max-width: 80px !important;
            width: auto;
            height: auto;
            object-fit: contain;
            margin: 0 auto;
            transition: transform 0.3s ease;
        }
        .productset:hover .productsetimgin {
            transform: scale(1.06);
        }
        .productset .productsetimg h6,
        .productset:hover .productsetimg h6 {
            position: absolute !important;
            top: 5px !important;
            right: 5px !important;
            bottom: auto !important;
            left: auto !important;
            width: auto !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            transform: none !important;
            background: rgba(15, 23, 42, 0.78) !important;
            color: #ffffff !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            margin: 0 !important;
            line-height: 1.2 !important;
            z-index: 5 !important;
            backdrop-filter: blur(4px) !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2) !important;
            transition: all 0.2s ease !important;
        }
        .productset:hover .productsetimg h6 {
            background: #ff9f43 !important;
            color: #ffffff !important;
            transform: scale(1.04) !important;
        }
        .productsetcontent,
        .productset .productsetcontent {
            padding: 2px !important;
            text-align: center !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
        }
        .productsetcontent h4,
        .productset .productsetcontent h4 {
            font-size: 13px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin: 2px 0 !important;
            line-height: 1.25 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            text-align: center !important;
            width: 100% !important;
        }
        .productsetcontent h5,
        .productset .productsetcontent h5 {
            font-size: 11px !important;
            color: #94a3b8 !important;
            font-weight: 500 !important;
            text-transform: capitalize !important;
            margin: 0 0 3px !important;
            line-height: 1.2 !important;
            text-align: center !important;
            width: 100% !important;
        }
        .productsetcontent h6,
        .productset .productsetcontent h6 {
            font-size: 14px !important;
            font-weight: 700 !important;
            color: var(--pos-primary) !important;
            margin: 2px auto 0 !important;
            display: block !important;
            text-align: center !important;
            width: 100% !important;
            float: none !important;
        }
        .productsetbtn,
        .productsetbtn button {
            display: none !important;
        }
        .disabled-product {
            opacity: 0.55;
            pointer-events: none;
            filter: grayscale(40%);
        }

        /* --- Right Order / Billing Card (viewport-locked, internal scroll) --- */
        .card.card-order {
            background: #ffffff;
            border: 1px solid var(--pos-border);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 8px;
            max-height: calc(100vh - 88px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .card.card-order > .card-body.body_space.pos-order-body,
        .card.card-order > .card-body.body_space {
            padding: 10px 12px 8px !important;
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto;
            min-height: 0 !important;
            max-height: 100%;
            overflow: hidden !important;
        }
        .card.card-order .card-body {
            padding: 10px 12px !important;
        }
        .card.card-order .card-body .card-body {
            padding: 0 !important;
        }
        .pos-order-scroll {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding-right: 2px;
            margin-bottom: 6px;
        }
        .pos-order-scroll > .card-body.pt-0:first-child {
            flex: 0 0 auto;
            display: block;
            overflow: visible;
            padding-bottom: 4px !important;
        }
        .pos-order-scroll .totalitem {
            flex-shrink: 0;
            margin-bottom: 4px !important;
        }
        /*
         * Cart items scroller — fixed comfortable height (not flex-squeezed).
         * Theme uses height:34vh; we replace with a clamped max-height.
         */
        .card.card-order .product-table,
        .pos-order-scroll .product-table,
        .product-table {
            height: auto !important;
            max-height: clamp(160px, 28vh, 300px) !important;
            min-height: 0 !important;
            flex: none !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch;
            padding: 2px 4px 8px 0 !important;
            margin: 0 0 4px 0 !important;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            background: #fcfcfd;
        }
        .pos-order-scroll .product-table::-webkit-scrollbar,
        .product-table::-webkit-scrollbar {
            width: 6px;
        }
        .pos-order-scroll .product-table::-webkit-scrollbar-track,
        .product-table::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .pos-order-scroll .product-table::-webkit-scrollbar-thumb,
        .product-table::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .pos-order-scroll .product-table::-webkit-scrollbar-thumb:hover,
        .product-table::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .pos-order-scroll .body_space_two {
            flex-shrink: 0;
            position: relative;
            z-index: 2;
            background: #ffffff;
            padding-top: 8px !important;
            padding-bottom: 4px !important;
            border-top: 1px solid #eef2f7;
            margin-top: 4px;
        }
        .split-card {
            display: none !important;
        }

        /* Segmented GST Control */
        .custom-gst-pill-group {
            display: inline-flex;
            background: #f1f5f9;
            padding: 2px;
            border-radius: 20px;
            border: 1px solid var(--pos-border);
        }
        .custom-gst-btn {
            margin: 0;
            cursor: pointer;
        }
        .custom-gst-btn input {
            display: none;
        }
        .custom-gst-btn span {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 11.5px;
            font-weight: 600;
            color: #64748b;
            transition: all 0.2s ease;
            user-select: none;
        }
        .custom-gst-btn input:checked + span {
            background-color: #ffffff;
            color: #1e293b;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            font-weight: 700;
        }

        /* Dine In Button */
        .dine-in-btn {
            background: linear-gradient(135deg, #ff9f43 0%, #ff8510 100%);
            color: #ffffff !important;
            font-weight: 600;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 12px;
            box-shadow: 0 2px 6px rgba(255, 159, 67, 0.25);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
            text-decoration: none;
            height: 30px;
        }
        .dine-in-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(255, 159, 67, 0.35);
        }

        /* Search Bar */
        .search_custom_product {
            width: 100%;
            margin-bottom: 6px !important;
        }
        .pos-search-input {
            height: 32px !important;
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            padding-left: 32px !important;
            font-size: 12px !important;
            background: var(--pos-bg-subtle) !important;
            transition: all 0.2s ease !important;
        }
        .pos-search-input:focus {
            background: #ffffff !important;
            border-color: var(--pos-primary) !important;
            box-shadow: 0 0 0 2px rgba(255, 159, 67, 0.15) !important;
        }
        .top-pos-search-wrapper {
            flex: 1 1 auto;
            max-width: 480px;
            min-width: 160px;
            margin: 0;
            position: relative;
            z-index: 40;
            overflow: visible !important;
        }
        .top-pos-search-wrapper .search_custom_product {
            position: relative;
            overflow: visible !important;
        }
        .top-pos-search-wrapper .pos-search-results-box {
            display: none;
            position: absolute !important;
            top: calc(100% + 4px) !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 2000 !important;
            background: #fff;
            max-height: 360px;
            overflow-y: auto;
        }
        .btn-close-search {
            transition: all 0.2s ease;
        }
        .btn-close-search:hover {
            border-color: #ef4444 !important;
            color: #ef4444 !important;
            background: #fef2f2 !important;
        }
        .pos-search-results-box {
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
        }
        @media (max-width: 768px) {
            .page-header {
                flex-wrap: wrap;
                gap: 8px;
            }
            .page-header .pos-header-left {
                width: 100%;
                justify-content: space-between;
            }
            .page-header .pos-header-divider {
                display: none;
            }
            .page-header .page-title {
                display: none;
            }
            .top-pos-search-wrapper {
                order: 3;
                width: 100%;
                max-width: 100%;
                flex: 1 1 100%;
                margin: 0;
            }
            .pos-header-actions {
                order: 2;
            }
        }

        /* Customer Section & Details Box — compact for more cart space */
        .customer-section-title {
            display: none !important; /* merged into customer-details-box header */
        }
        .pos-order-badge {
            background: #fff7ed;
            border: 1px solid #ffedd5;
            border-radius: 12px;
            padding: 1px 7px;
            font-size: 10px;
            font-weight: 700;
            color: var(--pos-primary);
            display: inline-flex;
            align-items: center;
            gap: 3px;
            flex-shrink: 0;
            white-space: nowrap;
            max-width: 55%;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pos-order-label {
            color: #9a3412;
            font-weight: 500;
            font-size: 9.5px;
        }
        .pos-order-val {
            color: var(--pos-primary);
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .customer-details-box {
            border: 1px solid #fed7aa !important;
            border-radius: 8px !important;
            padding: 0 !important;
            background: #fffaf5 !important;
            margin-bottom: 4px !important;
            box-shadow: none !important;
            flex-shrink: 0;
            overflow: hidden;
        }
        .customer-details-box .customer-box-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            padding: 4px 8px;
            background: #fff7ed;
            border-bottom: 1px solid #ffedd5;
            min-height: 26px;
        }
        .customer-details-box .customer-box-head .customer-box-title {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .customer-details-box .customer-box-head .customer-box-title i {
            color: #ff9f43;
            font-size: 11px;
        }
        .customer-details-box .customer-box-body {
            padding: 5px 6px 6px !important;
            background: #ffffff;
        }
        .search_custom_product {
            flex-shrink: 0;
            margin-bottom: 4px !important;
        }
        .search_custom_product .pos-search-input {
            height: 32px !important;
            min-height: 32px !important;
            font-size: 12px !important;
            padding-left: 34px !important;
        }
        .gst-header {
            display: none !important;
        }
        .pos-header-actions {
            flex-wrap: nowrap;
        }
        .pos-header-gst .custom-gst-btn span {
            padding: 3px 8px;
            font-size: 11px;
        }
        .page-header .dine-in-btn {
            height: 30px;
            white-space: nowrap;
        }
        @media (max-width: 1100px) {
            .pos-header-gst .custom-gst-btn span {
                padding: 3px 6px;
                font-size: 10px;
            }
        }
        @media (max-width: 768px) {
            .pos-header-actions {
                width: 100%;
                justify-content: flex-end;
                flex-wrap: wrap;
                gap: 6px !important;
            }
        }
        .customer-details-box .row.g-2 {
            --bs-gutter-y: 4px !important;
            --bs-gutter-x: 6px !important;
            margin: 0 !important;
        }
        .customer-details-box .col-6 {
            padding-left: 3px !important;
            padding-right: 3px !important;
        }
        .customer-details-box .select-split {
            margin-bottom: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .customer-details-box .select-group {
            margin-bottom: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .customer-details-box label {
            font-size: 9.5px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            margin-bottom: 1px !important;
            line-height: 1.1 !important;
            display: flex !important;
            align-items: center !important;
            gap: 3px !important;
        }
        .customer-details-box label i {
            font-size: 9px !important;
            margin-right: 0 !important;
        }
        .customer-details-box .form-control,
        .customer-details-box .select2-container .select2-selection--single {
            border-radius: 5px !important;
            font-size: 11.5px !important;
            height: 28px !important;
            min-height: 28px !important;
            max-height: 28px !important;
            padding: 1px 6px !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            color: #1e293b !important;
        }
        .customer-details-box .select2-container--default .select2-selection--single {
            border: 1px solid #e2e8f0 !important;
        }
        .customer-details-box .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            font-size: 11.5px !important;
            padding-left: 2px !important;
            padding-right: 18px !important;
            color: #1e293b !important;
        }
        .customer-details-box .select2-container .select2-selection--single .select2-selection__arrow {
            height: 26px !important;
            top: 1px !important;
            right: 3px !important;
        }
        .customer-details-box span[class^="error_"] {
            display: none;
            font-size: 9px !important;
            line-height: 1 !important;
            margin-top: 0 !important;
        }
        .customer-details-box span[class^="error_"]:not(:empty) {
            display: block !important;
        }

        /* Items List Header */
        .totalitem {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 0 !important;
            border-top: none;
            border-bottom: 1px dashed var(--pos-border);
            margin-bottom: 4px !important;
        }
        .totalitem h4 {
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #1e293b;
            margin: 0;
        }
        .clear_items {
            color: #ef4444 !important;
            font-weight: 600;
            font-size: 12px !important;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .clear_items:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        /* Cart Product Table Rows */
        .product-table {
            margin-bottom: 0;
            padding-right: 4px;
        }
        .product-lists {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 6px;
            border-bottom: 1px solid #f1f5f9;
            list-style: none;
            margin: 0;
            gap: 8px;
        }
        .product-lists:hover {
            background: var(--pos-bg-subtle);
            border-radius: 8px;
        }
        .productimg {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }
        .productimgs {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .productimgs img {
            max-width: 38px;
            max-height: 38px;
            object-fit: contain;
        }
        .productcontet {
            flex: 1;
            min-width: 0;
        }
        .productcontet h4 {
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 4px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .product-lists .price {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--pos-primary);
            white-space: nowrap;
        }

        /* Modern Steppers & Cart Row Controls */
        .increment-decrement {
            display: inline-flex !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            gap: 6px !important;
        }
        .increment-decrement .input-groups {
            display: inline-flex !important;
            align-items: center !important;
            border: 1px solid var(--pos-border) !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            width: 84px !important;
            height: 30px !important;
            background: #ffffff !important;
            flex-shrink: 0 !important;
        }
        .increment-decrement .input-groups .button-minus,
        .increment-decrement .input-groups .button-plus {
            width: 26px !important;
            height: 100% !important;
            border: none !important;
            background: #f1f5f9 !important;
            font-weight: 700 !important;
            cursor: pointer !important;
            padding: 0 !important;
            text-align: center !important;
            line-height: 28px !important;
            font-size: 14px !important;
            color: #1e293b !important;
            transition: background 0.2s;
        }
        .increment-decrement .input-groups .button-minus:hover,
        .increment-decrement .input-groups .button-plus:hover {
            background: #e2e8f0 !important;
            color: var(--pos-primary) !important;
        }
        .increment-decrement .input-groups .quantity-field {
            width: 32px !important;
            height: 100% !important;
            border: none !important;
            border-left: 1px solid var(--pos-border) !important;
            border-right: 1px solid var(--pos-border) !important;
            text-align: center !important;
            padding: 0 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #000 !important;
            background: #ffffff !important;
        }
        .price-field {
            width: 65px !important;
            height: 30px !important;
            border-radius: 6px !important;
            border: 1px solid var(--pos-border) !important;
            padding: 2px 6px !important;
            font-size: 12px !important;
            margin: 0 !important;
            font-weight: 600 !important;
            flex-shrink: 0 !important;
        }
        .cart-quantity-type {
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #475569 !important;
            background: #f1f5f9 !important;
            border: 1px solid #cbd5e1 !important;
            padding: 0 7px !important;
            height: 30px !important;
            border-radius: 6px !important;
            line-height: 28px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            white-space: nowrap !important;
            text-transform: uppercase !important;
            margin: 0 !important;
            flex-shrink: 0 !important;
            box-sizing: border-box !important;
        }
        .confirm-text.remove-item {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: #ef4444;
            transition: background 0.2s;
        }
        .confirm-text.remove-item:hover {
            background: #fee2e2;
        }

        /* Totals / Financial Rows */
        .body_space_two {
            padding-top: 4px !important;
        }
        .body_space_two .select-split {
            margin-bottom: 4px !important;
        }
        .body_space_two label {
            font-size: 11px !important;
            font-weight: 600 !important;
            margin-bottom: 2px !important;
            color: #475569 !important;
        }
        #discount {
            height: 31px !important;
            min-height: 31px !important;
            font-size: 12px !important;
            padding: 3px 8px !important;
            border-radius: 6px !important;
        }
        .setvalue ul {
            list-style: none;
            padding: 0;
            margin: 4px 0 !important;
        }
        .setvalue ul li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2.5px 0 !important;
            font-size: 12.5px !important;
        }
        .setvalue ul li h5 {
            font-size: 12px !important;
            font-weight: 500;
            color: var(--pos-muted);
            margin: 0;
        }
        .setvalue ul li h6 {
            font-size: 12.5px !important;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        .setvalue ul li.total-value {
            border-top: 1.5px solid var(--pos-border);
            padding-top: 6px !important;
            margin-top: 4px !important;
        }
        .setvalue ul li.total-value h5 {
            font-size: 14px !important;
            font-weight: 700;
            color: #1e293b;
        }
        .setvalue ul li.total-value h6 {
            font-size: 15px !important;
            font-weight: 800;
            color: #10b981 !important;
        }

        /* --- Payment Methods (pinned to bottom of order card, not page) --- */
        .payment_panel {
            position: relative !important;
            bottom: auto !important;
            left: auto !important;
            right: auto !important;
            width: 100% !important;
            flex-shrink: 0;
            background-color: #ffffff !important;
            z-index: 5;
            box-shadow: 0 -6px 14px rgba(15, 23, 42, 0.06);
            border: 1px solid var(--pos-border);
            border-radius: 10px;
            padding: 8px 10px !important;
            margin: 0 !important;
            box-sizing: border-box;
            isolation: isolate;
        }

        .card.card-order .card-body.body_space {
            padding-bottom: 8px !important;
        }
        .payment_panel_header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px !important;
        }
        .payment_panel_title {
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .setvaluecash {
            display: block !important;
            margin: 0 0 6px !important;
        }
        .setvaluecash ul {
            display: grid !important;
            grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
            gap: 6px !important;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .setvaluecash ul li {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            list-style: none !important;
        }
        .setvaluecash ul li a,
        .setvaluecash ul li a.paymentmethod {
            border: 1.5px solid #e2e8f0 !important;
            background: #ffffff !important;
            color: #475569 !important;
            font-size: 10.5px !important;
            font-weight: 600 !important;
            min-height: 40px !important;
            height: 40px !important;
            border-radius: 7px !important;
            padding: 3px 2px !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 3px !important;
            text-decoration: none !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            cursor: pointer !important;
            width: 100% !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            text-align: center !important;
            box-sizing: border-box !important;
            user-select: none !important;
        }
        .setvaluecash ul li a i,
        .setvaluecash ul li a.paymentmethod i {
            font-size: 15px !important;
            margin: 0 !important;
            display: block !important;
            color: #64748b !important;
            transition: color 0.2s ease !important;
        }
        .setvaluecash ul li a span,
        .setvaluecash ul li a.paymentmethod span {
            font-size: 10.5px !important;
            font-weight: 600 !important;
            line-height: 1.1 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            max-width: 100% !important;
            display: block !important;
            color: inherit !important;
        }
        .setvaluecash ul li a:hover,
        .setvaluecash ul li a.paymentmethod:hover {
            border-color: #ff9f43 !important;
            color: #ff9f43 !important;
            background: #fffaf5 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 3px 8px rgba(255, 159, 67, 0.15) !important;
        }
        .setvaluecash ul li a:hover i,
        .setvaluecash ul li a.paymentmethod:hover i {
            color: #ff9f43 !important;
        }
        .setvaluecash ul li a.paymentmethod.active {
            border-color: #1b2850 !important;
            background: #1b2850 !important;
            color: #ffffff !important;
            box-shadow: 0 3px 10px rgba(27, 40, 80, 0.25) !important;
            transform: translateY(-1px) !important;
        }
        .setvaluecash ul li a.paymentmethod.active i {
            color: #ffffff !important;
        }
        .setvaluecash ul li a.paymentmethod.active span {
            color: #ffffff !important;
            font-weight: 700 !important;
        }
        .setvaluecash ul li a.paymentmethod.active svg {
            fill: #ffffff !important;
        }

        /* --- Bottom Order Button Bar (Image 3 Style) --- */
        .btn-totallabel {
            background: linear-gradient(135deg, #ff9f43 0%, #ff8510 100%) !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            padding: 8px 14px !important;
            min-height: 40px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            cursor: pointer !important;
            box-shadow: 0 4px 14px rgba(255, 159, 67, 0.3) !important;
            transition: all 0.2s ease !important;
            margin-top: 6px !important;
            border: none !important;
        }
        .btn-totallabel:hover {
            box-shadow: 0 6px 18px rgba(255, 159, 67, 0.4) !important;
            transform: translateY(-1px) !important;
        }
        .btn-totallabel h6 {
            font-size: 13.5px !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            margin: 0 !important;
            letter-spacing: 0.3px !important;
        }
        .btn-totallabel h5 {
            font-size: 13.5px !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        .btn-totallabel h5 svg {
            fill: #ffffff !important;
            transition: transform 0.2s ease !important;
        }
        .btn-totallabel:hover h5 svg {
            transform: translateX(3px) !important;
        }

        /* Tablet / mobile — page scrolls; cart list keeps a clear scroller */
        @media screen and (max-width: 991px) {
            .tabs_wrapper {
                max-height: none;
            }
            .tabs_wrapper ul.tabs {
                padding: 6px 2px 14px !important;
                margin-bottom: 10px !important;
                gap: 8px !important;
            }
            .product-details {
                padding: 7px 14px;
            }
            .product-details h6 {
                font-size: 12.5px;
            }
            .tabs_wrapper .tabs_container,
            .tabs_container {
                max-height: none;
                min-height: 0;
                overflow: visible !important;
            }
            .card.card-order {
                position: static;
                margin-top: 16px;
                max-height: none;
                overflow: visible;
            }
            .card.card-order > .card-body.body_space.pos-order-body,
            .card.card-order > .card-body.body_space {
                overflow: visible !important;
                max-height: none;
            }
            .pos-order-scroll {
                max-height: none;
                overflow: visible;
            }
            .card.card-order .product-table,
            .pos-order-scroll .product-table,
            .product-table {
                max-height: min(280px, 40vh) !important;
                overflow-y: auto !important;
            }
            .setvaluecash ul {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }
        @media screen and (max-width: 576px) {
            .tabs_wrapper ul.tabs {
                gap: 6px !important;
                padding-bottom: 14px !important;
            }
            .product-details {
                padding: 6px 12px;
            }
            .card.card-order .product-table,
            .pos-order-scroll .product-table,
            .product-table {
                max-height: min(240px, 38vh) !important;
            }
            .setvaluecash ul {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
            .payment_panel {
                padding: 10px 8px !important;
            }
        }
        /* Short viewports (laptops with low height) */
        @media screen and (max-height: 760px) {
            .card.card-order {
                max-height: calc(100vh - 64px);
            }
            .card.card-order .product-table,
            .pos-order-scroll .product-table,
            .product-table {
                max-height: clamp(120px, 22vh, 200px) !important;
            }
            .tabs_wrapper .tabs_container,
            .tabs_container {
                max-height: calc(100vh - 160px);
                min-height: 180px;
            }
        }
   /* ===== Cart row: name on top, everything else on ONE aligned line ===== */
.product-lists {
    display: block !important;
    padding: 8px 6px !important;
    margin: 0 0 4px 0 !important;
    border: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
    box-shadow: none !important;
    background: transparent;
}
.product-lists > li.cart-row {
    display: block !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    list-style: none;
}

/* Name row */
.cart-item-name {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0 0 4px 0 !important;
    padding: 0 !important;
    line-height: 1.25 !important;
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
    text-transform: capitalize;
    white-space: normal;
    overflow: visible;
}
.cart-item-title {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Controls row: image + qty + price + delete on one aligned line */
.cart-item-controls {
    display: flex !important;
    align-items: center !important;
    flex-wrap: nowrap !important;
    gap: 6px !important;
    width: 100%;
    min-height: 32px;
}
.cart-item-controls > * {
    margin: 0 !important;
    flex-shrink: 0;
}

/* Image */
.cart-item-controls .productimgs {
    width: 32px !important;
    height: 32px !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}
.cart-item-controls .productimgs img {
    width: 100% !important;
    height: 100% !important;
    max-width: 32px;
    max-height: 32px;
    object-fit: cover;
    display: block;
    margin: 0 !important;
    /* Hide broken-image alt text fragments inside the thumb */
    color: transparent !important;
    font-size: 0 !important;
    text-indent: -9999px;
}

/* Stepper + price + unit */
.cart-item-controls .increment-decrement {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    flex: 1;
    min-width: 0;
    height: 28px;
    margin: 0 !important;
    padding: 0 !important;
}
.cart-item-controls .increment-decrement > * {
    margin: 0 !important;
}
.cart-item-controls .input-groups {
    height: 28px !important;
    margin: 0 !important;
}
.cart-item-controls .price-field {
    height: 28px !important;
    margin: 0 !important;
    line-height: 1 !important;
}
.cart-item-controls .cart-quantity-type {
    height: 28px !important;
    line-height: 1 !important;
}

/* Delete icon — keep vertically centered with qty/price row */
.cart-item-controls .remove-item {
    margin-left: auto !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    align-self: center !important;
}
.cart-item-controls .remove-item img {
    width: 16px;
    height: 16px;
    display: block;
    margin: 0 !important;
}

        /* —— Bill Generated modal (Petpooja-simple) —— */
        .bill-gen-dialog { max-width: 820px; margin-top: 0 !important; }
        #billGeneratedModal .modal-content.bill-gen-content,
        .bill-gen-content {
            border-radius: 16px;
            overflow: hidden;
            margin: 0 !important;
            margin-top: 0 !important;
        }
        .bill-gen-header { background: #fff; padding: 16px 20px 8px; }
        .bill-gen-preview {
            height: min(480px, 52vh);
            background: #e8eaed;
            position: relative;
        }
        .bill-gen-loader {
            position: absolute; inset: 0;
            background: rgba(255,255,255,.85);
            display: flex; align-items: center; justify-content: center; z-index: 10;
        }
        .bill-gen-footer {
            background: #fff;
            padding: 12px 16px 16px !important;
            display: flex !important;
            flex-direction: column;
            gap: 10px;
            align-items: stretch !important;
        }
        .bill-gen-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }
        .bill-gen-summary-text {
            font-size: 12.5px;
            color: #64748b;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px 10px;
            min-width: 0;
        }
        #posPrintSummaryLabel { color: #334155; font-weight: 600; }
        .bill-gen-status:empty { display: none; }
        .bill-gen-settings-toggle {
            color: #64748b !important;
            text-decoration: none !important;
            font-weight: 600;
            padding: 0 !important;
            white-space: nowrap;
        }
        .bill-gen-settings-toggle:hover { color: #ff9f43 !important; }
        .bill-gen-settings {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
        }
        .bill-gen-settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .bill-gen-field label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 4px;
        }
        .bill-gen-field-row {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .bill-gen-field-row .form-select { flex: 1; min-width: 0; }
        .bill-gen-settings-opts {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
        }
        .bill-gen-check {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin: 0;
            cursor: pointer;
        }
        .bill-gen-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }
        .bill-gen-btn-secondary {
            font-weight: 600;
            border-radius: 10px;
            padding: 9px 16px;
        }
        .bill-gen-btn-primary {
            background: #ff9f43 !important;
            color: #fff !important;
            font-weight: 700;
            border: none !important;
            border-radius: 10px;
            padding: 10px 22px;
            box-shadow: 0 4px 12px rgba(255, 159, 67, 0.28);
        }
        .bill-gen-btn-primary:hover {
            background: #f08c2e !important;
            color: #fff !important;
        }
        @media (max-width: 767px) {
            .bill-gen-settings-grid { grid-template-columns: 1fr; }
            .bill-gen-preview { height: min(380px, 48vh); }
            .bill-gen-actions { width: 100%; }
            .bill-gen-btn-primary { flex: 1 1 100%; }
            .bill-gen-btn-secondary { flex: 1 1 auto; }
        }

        /* Multi-bill tabs inside combined header */
        .pos-bill-tabs-bar {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 6px;
            width: auto;
            max-width: min(42vw, 360px);
            margin: 0;
            min-height: 28px;
            flex-shrink: 1;
            min-width: 0;
        }
        #posBillTabsBar #posBillTabs.pos-bill-tabs {
            display: inline-flex !important;
            align-items: center;
            gap: 6px;
            overflow-x: auto;
            flex: 1 1 auto !important;
            width: max-content !important;
            max-width: 100%;
            min-width: 0;
            scrollbar-width: thin;
        }
        .pos-bill-tabs::-webkit-scrollbar { height: 3px; }
        #posBillTabsBar .pos-bill-tab {
            display: inline-flex !important;
            align-items: center;
            justify-content: flex-start;
            gap: 4px;
            flex: 0 0 auto !important;
            width: max-content !important;
            max-width: 140px !important;
            height: 28px !important;
            min-height: 28px !important;
            min-width: 0 !important;
            border: 0;
            background: #3a3f4b;
            color: #fff;
            border-radius: 999px;
            padding: 0 6px 0 10px !important;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            line-height: 1;
            white-space: nowrap;
            box-sizing: border-box;
            overflow: hidden;
        }
        #posBillTabsBar .pos-bill-tab.active {
            background: #ff9f43;
            color: #fff;
        }
        .pos-bill-tab-name {
            flex: 0 1 auto;
            max-width: 80px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: left;
        }
        .pos-bill-tab-count {
            display: none;
            min-width: 14px;
            height: 14px;
            border-radius: 50%;
            background: rgba(255,255,255,0.22);
            font-size: 9px;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            line-height: 1;
        }
        .pos-bill-tab.has-items .pos-bill-tab-count { display: inline-flex; }
        #posBillTabsBar .pos-bill-tab-close {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            flex: 0 0 14px !important;
            width: 14px !important;
            height: 14px !important;
            min-width: 14px !important;
            min-height: 14px !important;
            border-radius: 50%;
            border: 0;
            background: rgba(255,255,255,0.22);
            color: #fff;
            font-size: 9px !important;
            font-weight: 700;
            line-height: 1 !important;
            padding: 0 !important;
            margin: 0 !important;
            cursor: pointer;
        }
        #posBillTabsBar .pos-bill-tab-close:hover { background: rgba(0,0,0,0.28); }
        .pos-add-bill-tab {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 0;
            background: #1b2850;
            color: #fff;
            font-size: 16px;
            line-height: 1;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .pos-add-bill-tab:hover { background: #ff9f43; }
        @media (max-width: 992px) {
            .pos-bill-tabs-bar { max-width: min(55vw, 280px); }
        }
    </style>
        <div class="content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div class="pos-header-left">
                <div class="pos-bill-tabs-bar" id="posBillTabsBar">
                    <div class="pos-bill-tabs" id="posBillTabs"></div>
                    <button type="button" class="pos-add-bill-tab" id="posAddBillTabBtn" title="New bill (Shift + T)">+</button>
                </div>
                <span class="pos-header-divider" aria-hidden="true"></span>
                <div class="page-title d-flex align-items-center gap-2 m-0" style="flex-shrink: 0;">
                    <i class="fas fa-utensils" style="color: var(--pos-primary); font-size: 16px;"></i>
                    <h4 style="margin-bottom: 0; font-weight: 700; color: #1e293b; font-size: 15px;">Add Order</h4>
                </div>
            </div>

            <!-- Top Center Product Search Bar -->
            <div class="top-pos-search-wrapper position-relative">
                <div class="header-search position-relative search_custom_product w-100">
                    <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; pointer-events: none; z-index: 5;"></i>
                    <input type="text" id="topProductSearch"
                        class="form-control pos-search-input pos-product-search-input" placeholder="Search dishes, drinks, items..."
                        autocomplete="off" style="height: 34px; font-size: 13px; border-radius: 8px;">
                    <div id="topSearchResults"
                        class="list-group bg-white position-absolute rounded-3 shadow mt-1 w-100 pos-search-results-box"
                        style="z-index: 1050; max-height: 350px; overflow-y: auto; display: none; top: 100%; left: 0;">
                    </div>
                </div>
            </div>

            <div class="pos-header-actions d-flex align-items-center" style="flex-shrink: 0; gap: 8px;">
                <div class="custom-gst-pill-group pos-header-gst">
                    <label class="custom-gst-btn">
                        @php $defaultGst = ((optional($setting)->default_gst_option ?? 'without') === 'with') ? 'with' : 'without'; @endphp
                        <input type="radio" name="gst_option" id="without_gst" value="without" {{ $defaultGst === 'without' ? 'checked' : '' }} />
                        <span>Without GST</span>
                    </label>
                    <label class="custom-gst-btn">
                        <input type="radio" name="gst_option" id="with_gst" value="with" {{ $defaultGst === 'with' ? 'checked' : '' }} />
                        <span>With GST</span>
                    </label>
                </div>
                @if (app('hasPermission')(2, 'add'))
                    <a href="{{ route('tables.create') }}" class="dine-in-btn">
                        <i class="fas fa-chair"></i> Dine In
                    </a>
                @endif
                <a href="{{ route('sales.list') }}" class="pos-back-btn">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 col-sm-12 tabs_wrapper">

                @php
                // Show categories with active POS products (include qty 0 — negative selling / prepared food)
                $posCategoryProductCount = function ($cat) {
                    if (!$cat->products) {
                        return 0;
                    }
                    return $cat->products->filter(function ($p) {
                        if ((int) ($p->isDeleted ?? 0) !== 0) {
                            return false;
                        }
                        if (($p->status ?? 'active') !== 'active') {
                            return false;
                        }
                        $posOk = $p->is_pos_available ?? true;
                        return $posOk === true || $posOk === 1 || $posOk === '1' || $posOk === null;
                    })->count();
                };
                $hasProducts = false;
                foreach ($categories as $cat) {
                    if ($posCategoryProductCount($cat) > 0) {
                        $hasProducts = true;
                        break;
                    }
                }
                @endphp

                @if ($hasProducts)
                <ul class="tabs border-0 mb-3">
                    <li id="all">
                        <div class="product-details">
                            <h6>All Products</h6>
                        </div>
                    </li>
                    @foreach ($categories as $cat)
                    @if ($posCategoryProductCount($cat) > 0)
                    <li id="{{ $cat->id }}">
                        <div class="product-details">
                            <h6 style="text-transform: capitalize;">{{ $cat->name }}</h6>
                        </div>
                    </li>
                    @endif
                    @endforeach
                </ul>
                @else
                <div class="product-details text-center">
                    <h6>No data available</h6>
                    <a href="{{ url('add-product') }}?redirect_to=add-sales" class="btn btn-primary mt-2">
                        + Add Item
                    </a>
                </div>
                @endif
                <div class="tabs_container">

                </div>
            </div>
            <div class="col-lg-5 col-sm-12 ">

                <div class="card card-order">
                    <div class="card-body body_space pos-order-body">
                        <!-- Compact Customer Details -->
                        <div class="customer-details-box">
                            <div class="customer-box-head">
                                <div class="customer-box-title">
                                    <i class="fas fa-user-friends"></i>
                                    <span>Customer</span>
                                </div>
                                <div class="pos-order-badge">
                                    <span class="pos-order-label">Type:</span>
                                    <span class="pos-order-val" id="orderTypeBadge">Self Pickup</span>
                                </div>
                            </div>
                            <div class="customer-box-body">
                            <div class="row g-2">
                                <!-- Customer Name -->
                                <div class="col-6">
                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <label><i class="far fa-user-circle"></i> Name</label>
                                            <select id="customer_name_input" class="form-control select2_customer" name="customer_name">
                                                <option value="">Select or Type Customer</option>
                                                @php
                                                    $hasUnregistered = collect($customers)->contains(function ($c) {
                                                        return in_array(strtolower(trim((string) ($c->name ?? ''))), ['unregistered customer', 'default customer'], true);
                                                    });
                                                @endphp
                                                @unless($hasUnregistered)
                                                <option value="Unregistered Customer" data-phone="" selected>Unregistered Customer</option>
                                                @endunless
                                                @foreach ($customers as $cust)
                                                @php
                                                    $isDefaultCust = in_array(strtolower(trim((string) ($cust->name ?? ''))), ['unregistered customer', 'default customer'], true);
                                                @endphp
                                                <option value="{{ $cust->name }}" data-phone="{{ $cust->phone }}" {{ $isDefaultCust ? 'selected' : '' }}>{{ $cust->name }}@if(!empty($cust->phone)) ({{ $cust->phone }})@endif</option>
                                                @endforeach
                                            </select>
                                            <span class="error_customername" style="color: red; font-size: 11px;"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Phone -->
                                <div class="col-6">
                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <label><i class="fas fa-phone-alt"></i> Phone</label>
                                            <input type="tel" id="customer_phone" class="form-control"
                                                placeholder="Phone" name="customer_phone"
                                                maxlength="10" inputmode="numeric" pattern="[0-9]{10}"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                                onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            <span class="error_customerphone" style="color: red; font-size: 11px;"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Type -->
                                <div class="col-6">
                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <label><i class="fas fa-concierge-bell"></i> Order Type</label>
                                            <select id="order_type_select" name="order_type" class="form-control select2-fixed">
                                                <option value="Delivery">Delivery</option>
                                                <option value="Self Pickup" selected>Self Pickup</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assigned Staff -->
                                <div class="col-6">
                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <label><i class="fas fa-user-tie"></i> Staff</label>
                                            <select name="assigned_staff_id" id="assigned_staff_id" class="form-control select2-fixed">
                                                <option value="">- Unassigned -</option>
                                                @foreach($staffList as $staff)
                                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                        <script>
                            // document.getElementById('customer_name').addEventListener('change', function() {
                            //     var phone = this.options[this.selectedIndex].getAttribute('data-phone');
                            //     document.getElementById('customer_phone').value = phone || '';
                            // });
                        </script>
                        <div class="pos-order-scroll">
                        <div class="card-body pt-0">
                            <div class="totalitem">
                                <h4>Total items : 4</h4>
                                <a href="javascript:void(0);" class="clear_items">Clear all</a>
                            </div>
                            <div class="product-table">

                            </div>
                        </div>
                        <div class="card-body pt-0 pb-2 body_space_two">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="select-split ">
                                        <div class="select-group w-100">
                                            <label for="discount">Discount (%)</label>
                                            <input type="number" class="form-control" placeholder="Discount %" name="discount"
                                                id="discount">
                                            <span class="error_discount"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="setvalue">
                                <ul>
                                    <li>

                                        <h5>Subtotal</h5>
                                        <h6 style="color: green;">
                                            @if ($currency_position === 'right')
                                            <span class="subtotal-value">0.00</span>{{ $currency_symbol }}
                                            @else
                                            {{ $currency_symbol }}<span class="subtotal-value">0.00</span>
                                            @endif
                                        </h6>
                                        <input type="hidden" name="subtotal" value="" class="tax-hidden">
                                    </li>

                                    <li class="discount-summary-row" style="display: none;">
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

                                    <li class="price-after-discount-row" style="display: none;">
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
                                    <li class="taxList" data-tax-id="{{ $tax->id }}" style="display:none;">
                                        <h5>{{ $tax->tax_name }} ({{ $tax->tax_rate }}%) Tax</h5>
                                        <h6 class="tax-value" data-tax-id="{{ $tax->id }}" data-rate="{{ $tax->tax_rate }}"
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
                            {{-- <div class="table_type">
                                <div class="setvaluecash1">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0);" class="typemethod active">
                                                <input type="radio" name="type" value="dinein" checked hidden>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-door-open" viewBox="0 0 16 16">
                                                <path d="M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1z"/>
                                                <path d="M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H1V2.5A1.5 1.5 0 0 1 2.5 1H3V.5a.5.5 0 0 1 .5-.5h7.328zM3 2v13h8V2H3z"/>
                                                </svg>
                                                Dine In
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="typemethod">
                                                <input type="radio" name="type" value="delivery" hidden>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                    <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                    <path
                                                        d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                                </svg>
                                                Delivery
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="typemethod">
                                                <input type="radio" name="type" value="take_away" hidden>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                    <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                    <path
                                                        d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                                </svg>
                                                Take Away
                                            </a>
                                        </li>
                                    </ul>
                                    <span class="error_typemethod"></span>
                                </div>
                            </div> --}}
                            {{-- Default hidden type = takeaway so API still receives a value --}}
                            <input type="radio" name="type" value="takeaway" checked hidden>
                            </div>{{-- /.body_space_two --}}
                            </div>{{-- /.pos-order-scroll --}}
                            <div class="payment_panel">
                                <div class="payment_panel_header">
                                    <span class="payment_panel_title">
                                        <i class="fas fa-wallet" style="color: var(--pos-primary);"></i> Payment Method
                                    </span>
                                </div>
                                @php
                                    $defaultPayMethod = optional($setting)->default_payment_method ?: 'cash';
                                    $allowedPayMethods = ['pending', 'cash', 'debit card', 'cash+online', 'scan'];
                                    if (!in_array($defaultPayMethod, $allowedPayMethods, true)) {
                                        $defaultPayMethod = 'cash';
                                    }
                                    $defaultPaidType = (optional($setting)->default_paid_type === 'partially') ? 'partially' : 'fully';
                                @endphp
                                <div class="setvaluecash">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0);" class="paymentmethod {{ $defaultPayMethod === 'pending' ? 'active' : '' }}">
                                                <input type="radio" name="payment_method" value="pending" {{ $defaultPayMethod === 'pending' ? 'checked' : '' }} hidden>
                                                <i class="fas fa-history"></i>
                                                <span>Pay Later</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="paymentmethod {{ $defaultPayMethod === 'cash' ? 'active' : '' }}">
                                                <input type="radio" name="payment_method" value="cash" {{ $defaultPayMethod === 'cash' ? 'checked' : '' }} hidden>
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span>Cash</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="paymentmethod {{ $defaultPayMethod === 'debit card' ? 'active' : '' }}">
                                                <input type="radio" name="payment_method" value="debit card" {{ $defaultPayMethod === 'debit card' ? 'checked' : '' }} hidden>
                                                <i class="fas fa-credit-card"></i>
                                                <span>Debit</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="paymentmethod {{ $defaultPayMethod === 'cash+online' ? 'active' : '' }}">
                                                <input type="radio" name="payment_method" value="cash+online" {{ $defaultPayMethod === 'cash+online' ? 'checked' : '' }} hidden>
                                                <i class="fas fa-money-check-alt"></i>
                                                <span>Cash+Online</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="paymentmethod {{ $defaultPayMethod === 'scan' ? 'active' : '' }}">
                                                <input type="radio" name="payment_method" value="scan" {{ $defaultPayMethod === 'scan' ? 'checked' : '' }} hidden>
                                                <i class="fas fa-qrcode"></i>
                                                <span>Scan</span>
                                            </a>
                                        </li>
                                    </ul>
                                    {{-- ── Paid Type sub-panel (Cash / Debit / Scan) ────────── --}}
                                    <div id="paidTypeBox" style="{{ in_array($defaultPayMethod, ['cash', 'debit card', 'scan'], true) ? '' : 'display:none;' }} margin-top:10px;">
                                        <div class="form-group mb-2">
                                            <label style="font-weight:600;">Paid Type</label>
                                            <select id="paidTypeSelect" class="form-control" name="paid_type">
                                                <option value="">Select Paid Type</option>
                                                <option value="fully" {{ $defaultPaidType === 'fully' ? 'selected' : '' }}>Fully</option>
                                                <option value="partially" {{ $defaultPaidType === 'partially' ? 'selected' : '' }}>Partially</option>
                                            </select>
                                            <small id="paidTypeError" style="font-size:14px; color: red;"></small>
                                        </div>
                                        <div id="paidAmountBox" style="display:none;">
                                            <div id="paidAmountRow" class="row g-2 align-items-start mt-1">
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label>Enter Amount</label>
                                                        <input type="number" class="form-control" id="enterAmount"
                                                            placeholder="0.00" min="0" step="0.01">
                                                        <small id="enterAmountError" style="min-height:18px; display:block; color: red;"></small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label>Pending Amount</label>
                                                        <input type="number" class="form-control" id="pendingAmount"
                                                            placeholder="0.00" readonly>
                                                        <small style="min-height:18px; display:block;"></small>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- ── Debit-only fields ──────────────────────── --}}
                                            <div id="debitExtraFields" style="display:none;">
                                                <div class="row mt-2 align-items-start">
                                                    <div class="col-8" style="display:none;">
                                                        <div class="form-group mb-0">
                                                            <label>Select Bank</label>
                                                            <select id="debitBankSelect" class="form-control" name="bank_id">
                                                                <option value="">Select Bank</option>
                                                            </select>
                                                            <small id="debitBankError" style="min-height:18px; display:block; color: red;"></small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 d-flex align-items-start pt-4 mt-1" style="display:none !important;">
                                                        <div class="form-group w-100 mb-0">
                                                            <button type="button" id="addBankBtn"
                                                                class="btn btn-sm w-100"
                                                                style="background:#ff9f43;color:#fff;font-weight:600;border-radius:6px;height:38px;">
                                                                + Add Bank
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Remark</label>
                                                            <input type="text" class="form-control" id="debitRemark"
                                                                name="debit_remark" placeholder="Enter remark">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- ── Scan-only fields ───────────────────────── --}}
                                            <div id="scanExtraFields" style="display:none;">
                                                <div class="row mt-2 align-items-start">
                                                    <div class="col-8" style="display:none;">
                                                        <div class="form-group mb-0">
                                                            <label>Select Bank</label>
                                                            <select id="scanBankSelect" class="form-control" name="scan_bank_id">
                                                                <option value="">Select Bank</option>
                                                            </select>
                                                            <small id="scanBankError" style="min-height:18px; display:block; color: red;"></small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 d-flex align-items-start pt-4 mt-1" style="display:none !important;">
                                                        <div class="form-group w-100 mb-0">
                                                            <button type="button" class="btn btn-sm w-100 addBankBtnScan"
                                                                style="background:#ff9f43;color:#fff;font-weight:600;border-radius:6px;height:38px;">
                                                                + Add Bank
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Remark</label>
                                                            <input type="text" class="form-control" id="scanRemark"
                                                                name="scan_remark" placeholder="Enter remark">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ── Cash+Online split box ─────────────────────────── --}}
                                    <div id="cashOnlineBox" style="display:none; margin-top:10px;">
                                        {{-- Paid Type (same as debit/cash/scan) --}}
                                        <div class="form-group mb-2">
                                            <label style="font-weight:600;">Paid Type</label>
                                            <select id="cashOnlinePaidTypeSelect" class="form-control" name="paid_type_cash_online">
                                                <option value="">Select Paid Type</option>
                                                <option value="fully" selected>Fully</option>
                                                <option value="partially">Partially</option>
                                            </select>
                                            <span id="cashOnlinePaidTypeError" style="color:red; font-size:14px;"></span>
                                        </div>
                                        <div id="cashOnlinePaidAmountBox" style="display:none;">
                                            <div id="cashOnlinePaidAmountRow" class="row g-2 align-items-start mt-1">
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label>Enter Amount</label>
                                                        <input type="number" class="form-control" id="cashOnlineEnterAmount"
                                                            placeholder="0.00" min="0" step="0.01">
                                                        <small id="cashOnlineEnterAmountError" style="min-height:18px; display:block; color: red;"></small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label>Pending Amount</label>
                                                        <input type="number" class="form-control" id="cashOnlinePendingAmount"
                                                            placeholder="0.00" readonly>
                                                        <small style="min-height:18px; display:block;"></small>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Cash + Online amounts --}}
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label>Cash Amount</label>
                                                        <input type="number" class="form-control" id="cashAmount"
                                                            placeholder="Enter Cash Amount" min="0" step="0.01">
                                                        <small id="cashAmountError" style="color: red;"></small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label>Online Amount</label>
                                                        <input type="number" class="form-control" id="onlineAmount"
                                                            placeholder="Enter Online Amount" value="0" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Select Bank + Remark --}}
                                            <div class="row mt-2 align-items-start">
                                                <div class="col-8" style="display:none;">
                                                    <div class="form-group mb-0">
                                                        <label>Select Bank</label>
                                                        <select id="cashOnlineBankSelect" class="form-control" name="bank_id_cash_online">
                                                            <option value="">Select Bank</option>
                                                        </select>
                                                        <small id="cashOnlineBankError" style="min-height:18px; display:block; color: red;"></small>
                                                    </div>
                                                </div>
                                                <div class="col-4 d-flex align-items-start pt-4 mt-1" style="display:none !important;">
                                                    <div class="form-group w-100 mb-0">
                                                        <button type="button" class="btn btn-sm w-100 addBankBtnCashOnline"
                                                            style="background:#ff9f43;color:#fff;font-weight:600;border-radius:6px;height:38px;">
                                                            + Add Bank
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Remark</label>
                                                        <input type="text" class="form-control" id="cashOnlineRemark"
                                                            name="cash_online_remark" placeholder="Enter remark">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="error_peymentmethod"></span>
                                </div>

                                <div class="btn-totallabel">
                                    <h6>Total Amount : 60.00$</h6>
                                    <h5>Order Now<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"
                                            style="margin-bottom: 0.1rem;">
                                            <path fill-rule="evenodd"
                                                d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" />
                                        </svg></h5>
                                </div>
                            </div>{{-- /.payment_panel --}}
                    </div>{{-- /.pos-order-body --}}
                </div>{{-- /.card-order --}}
            </div>{{-- /.col-lg-5 --}}
        </div>{{-- /.row --}}

        {{-- ── Add Bank Modal ───────────────────────────────────────── --}}
        <div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius:16px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="addBankModalLabel">Add Bank</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div id="addBankAlert" style="display:none;" class="alert alert-danger py-2 px-3 mb-3"></div>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" id="newBankName" class="form-control" placeholder="e.g. HDFC Bank">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Account Number <span class="text-danger">*</span></label>
                                <input type="text" id="newBankAccount" class="form-control" placeholder="Account number">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">IFSC Code <span class="text-danger">*</span></label>
                                <input type="text" id="newBankIfsc" class="form-control" placeholder="IFSC code">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                                <input type="text" id="newBankBranch" class="form-control" placeholder="Branch name">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Opening Balance <span class="text-danger">*</span></label>
                                <input type="number" id="newBankBalance" class="form-control" placeholder="0.00" value="0" min="0" step="0.01">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select id="newBankStatus" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="saveBankBtn" class="btn btn-sm px-4"
                            style="background:#ff9f43;color:#fff;font-weight:600;border-radius:8px;">
                            Save Bank
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ── Add Table Modal ───────────────────────────────────────── --}}
        <div class="modal fade" id="addTableModal" tabindex="-1" aria-labelledby="addTableModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius:16px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="addTableModalLabel">Add New Table</h5>
                        <button type="button" class="btn-close" id="closeTableModalBtn" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div id="addTableAlert" style="display:none;" class="alert alert-danger py-2 px-3 mb-3"></div>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Table Name <span class="text-danger">*</span></label>
                                <input type="text" id="newTableName" class="form-control" placeholder="Enter table name" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Table Type <span class="text-danger">*</span></label>
                                <select id="newTableType" class="form-control select2-modal" style="width: 100%;">
                                    <option value="">Select Table Type</option>
                                    <option value="AC">AC</option>
                                    <option value="Non AC">Non AC</option>
                                    <option value="Premium">Premium</option>
                                    <option value="Rooftop">Rooftop</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary btn-sm px-4" id="cancelTableModalBtn">Cancel</button>
                        <button type="button" id="saveTableBtn" class="btn btn-sm px-4"
                            style="background:#ff9f43;color:#fff;font-weight:600;border-radius:8px;">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- 🧾 Bill Generated Modal (Petpooja-simple: Print first, settings hidden) --}}
        <div class="modal fade" id="billGeneratedModal" tabindex="-1" aria-labelledby="billGeneratedModalLabel"
            aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered bill-gen-dialog">
                <div class="modal-content bill-gen-content">
                    <div class="modal-header border-0 bill-gen-header">
                        <div>
                            <h5 class="modal-title fw-bold fs-5 mb-0" id="billGeneratedModalLabel">Bill Generated</h5>
                            <small class="text-muted">Press <kbd>F10</kbd> or Print — bill &amp; KOT together</small>
                        </div>
                        <button type="button" class="btn-close" id="closeBillModalBtn" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0" style="background:#f8f9fa;">
                        <div class="phone-print-panel d-md-none px-3 pt-2" data-select="#posPrinterSelect" data-status="#posQzStatus" data-storage="posSelectedPrinter" data-bt="#posBtPrinterList">
                            <div class="phone-print-tabs mb-2">
                                <button type="button" class="phone-print-tab is-active btn btn-sm btn-outline-primary me-1" data-tab="bt">Bluetooth</button>
                                <button type="button" class="phone-print-tab btn btn-sm btn-outline-secondary" data-tab="wifi">WIFI</button>
                            </div>
                            <div id="posBtPrinterList" class="bt-printer-list" data-print-pane="bt">
                                <div class="bt-printer-empty small text-muted">Tap Bluetooth and choose the receipt printer.</div>
                            </div>
                            <div id="posWifiPrinterList" class="bt-printer-list d-none" data-print-pane="wifi">
                                <div class="bt-printer-empty small text-muted">Looking for Wi‑Fi printers…</div>
                            </div>
                        </div>
                        <div class="bill-gen-preview">
                            <iframe id="billPreviewIframe"
                                src=""
                                style="width:100%; height:100%; border:none; display:block;"
                                title="Bill Preview">
                            </iframe>
                            <div id="billIframeLoader" class="bill-gen-loader">
                                <div class="spinner-border text-warning" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bill-gen-footer">
                        <div class="bill-gen-summary">
                            <div class="bill-gen-summary-text">
                                <span id="posPrintSummaryLabel">Ready to print</span>
                                <span class="bill-gen-status" id="posQzStatus"></span>
                                <span class="bill-gen-status" id="posKotQzStatus"></span>
                            </div>
                            <button type="button" class="btn btn-link btn-sm bill-gen-settings-toggle" id="posPrinterSettingsToggle">
                                <i class="fas fa-cog"></i> Printer settings
                            </button>
                        </div>

                        <div id="posPrinterSettingsPanel" class="bill-gen-settings" style="display:none;">
                            <div class="bill-gen-settings-grid">
                                <div class="bill-gen-field">
                                    <label for="posPrinterSelect">Bill printer</label>
                                    <div class="bill-gen-field-row">
                                        <select id="posPrinterSelect" class="form-select form-select-sm">
                                            <option value="">Connecting…</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="posRefreshPrintersBtn" title="Refresh">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm d-none d-md-inline-block desktop-bt-print-btn"
                                            data-select="#posPrinterSelect" data-status="#posQzStatus" data-storage="posSelectedPrinter"
                                            title="Bluetooth bill printer">
                                            <i class="fab fa-bluetooth-b"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="bill-gen-field" id="posKotPrinterRow">
                                    <label for="posKotPrinterSelect">Kitchen / KOT printer</label>
                                    <div class="bill-gen-field-row">
                                        <select id="posKotPrinterSelect" class="form-select form-select-sm">
                                            <option value="">Same as bill</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary btn-sm d-none d-md-inline-block desktop-bt-print-btn"
                                            data-select="#posKotPrinterSelect" data-status="#posKotQzStatus" data-storage="posSelectedKotPrinter"
                                            title="Bluetooth kitchen printer">
                                            <i class="fab fa-bluetooth-b"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="bill-gen-settings-opts">
                                <label class="bill-gen-check">
                                    <input type="checkbox" id="posPrintKotWithBill" checked>
                                    <span>Also print KOT</span>
                                </label>
                                <label class="bill-gen-check">
                                    <input type="checkbox" id="posThermalAutoCut">
                                    <span>Auto cut</span>
                                </label>
                                <a href="https://qz.io/download/" target="_blank" rel="noopener" class="small" id="posQzInstallLink" style="display:none;">Install QZ Tray</a>
                                <a href="{{ url('/qz/install-qz-trust.bat') }}" class="small d-none d-md-inline" id="posQzTrustLink">Fix Allow</a>
                            </div>
                        </div>

                        <div class="bill-gen-actions">
                            <button type="button" id="closeBillBtn" class="btn btn-light bill-gen-btn-secondary">Close</button>
                            <button type="button" id="detailsBillBtn" class="btn btn-outline-secondary bill-gen-btn-secondary"
                                onclick="if(window._billOrderId){ window.location.href='/sales-invoice/'+window._billOrderId; }">
                                Details
                            </button>
                            <button type="button" id="printKotOnlyBtn" class="btn btn-outline-dark bill-gen-btn-secondary" title="Kitchen ticket only">
                                Print KOT
                            </button>
                            <button type="button" id="printBillBtn" class="btn bill-gen-btn-primary">
                                <i class="fas fa-print me-1"></i>
                                <span id="printBillBtnLabel">Print Bill + KOT</span>
                                <small class="ms-1 opacity-75">(F10)</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="posVariantAddonModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Choose options — <span id="posOptionProductName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="posOptionProductId">
                        <div id="posVariantOptions" class="mb-3"></div>
                        <div id="posAddonOptions" class="mb-2"></div>
                        <div class="fw-bold">Line price: <span id="posOptionLinePrice">0.00</span></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning text-white" id="posOptionConfirmAdd">Add to order</button>
                    </div>
                </div>
            </div>
        </div>
        @endsection
        @push('js')
        <script>
            let selectedItems = new Map();
            window.posProductOptionsById = window.posProductOptionsById || {};
            const canItemDiscount = {{ !empty($can_item_discount) ? 'true' : 'false' }};
            const defaultGstOption = @json(((optional($setting)->default_gst_option ?? 'without') === 'with') ? 'with' : 'without');
            const gstApplyMode = @json(((optional($setting)->gst_apply_mode ?? 'product') === 'overall') ? 'overall' : 'product');
            const overallGstRate = @json((float) (optional($setting)->overall_gst_rate ?? 0));
            const overallGstName = @json(trim((string) (optional($setting)->overall_gst_name ?? 'GST')) ?: 'GST');
            const serviceTaxEnabled = @json((bool) (optional($setting)->service_tax_enabled ?? false));
            const serviceTaxRate = @json((float) (optional($setting)->service_tax_rate ?? 0));
            const serviceTaxName = @json(trim((string) (optional($setting)->service_tax_name ?? 'Service Tax')) ?: 'Service Tax');
            const defaultPaymentMethod = @json($defaultPayMethod ?? 'cash');
            const defaultPaidType = @json($defaultPaidType ?? 'fully');
            const posCurrencySymbol = @json($currency_symbol ?? '₹');
            const posCurrencyPosition = @json($currency_position ?? 'left');
            const POS_NO_IMAGE = @json(rtrim((string) env('ImagePath'), '/') . '/admin/assets/img/product/noimage.png');
            const POS_IMAGE_BASE = @json(rtrim((string) env('ImagePath'), '/'));
            const POS_DELETE_ICON = @json(rtrim((string) env('ImagePath'), '/') . '/admin/assets/img/icons/delete-2.svg');
            window.POS_NO_IMAGE = POS_NO_IMAGE;

            function escapeHtmlAttr(str) {
                return String(str == null ? '' : str)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            function escapeHtmlText(str) {
                return String(str == null ? '' : str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            // Global POS stock helpers (used by grid + search add handlers)
            function posIsPreparedFood(product) {
                return String(product && product.product_kind ? product.product_kind : '') === 'prepared_food';
            }
            function posAllowsOversell(product) {
                if (!product) return true;
                if (product.allow_oversell === false || product.allow_oversell === 0) return false;
                return true;
            }
            function posShowOutOfStock(product) {
                if (!product) return false;
                if (typeof product.show_out_of_stock !== 'undefined') {
                    return !!product.show_out_of_stock;
                }
                if (posIsPreparedFood(product) || product.track_inventory === false || product.track_inventory === 0) {
                    return false;
                }
                return Number(product.quantity) <= 0;
            }
            function posEffectiveStock(productOrStock, productMeta) {
                if (productMeta) {
                    if (posIsPreparedFood(productMeta) || posAllowsOversell(productMeta) || productMeta.track_inventory === false || productMeta.track_inventory === 0) {
                        return 999999;
                    }
                }
                var n = parseInt(productOrStock, 10);
                return isNaN(n) ? 999999 : (Math.max(n, 0) || 999999);
            }
            window.posIsPreparedFood = posIsPreparedFood;
            window.posAllowsOversell = posAllowsOversell;
            window.posShowOutOfStock = posShowOutOfStock;
            window.posEffectiveStock = posEffectiveStock;

            function resolveProductImage(imageValue) {
                var fallback = POS_NO_IMAGE;
                var imageBasePath = POS_IMAGE_BASE || '';
                if (imageValue == null || imageValue === '') {
                    return fallback;
                }
                try {
                    var path = imageValue;
                    while (Array.isArray(path)) {
                        if (!path.length) return fallback;
                        path = path[0];
                    }
                    if (typeof path !== 'string') path = String(path);
                    // Strip accidental HTML attribute leftovers from older buggy cart markup
                    path = path.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                    var clean = path.replace(/\\/g, '').trim().replace(/^["']+|["']+$/g, '');
                    if (clean.charAt(0) === '[') {
                        var parsed = JSON.parse(clean);
                        while (Array.isArray(parsed)) {
                            if (!parsed.length) return fallback;
                            parsed = parsed[0];
                        }
                        clean = String(parsed || '').replace(/\\/g, '').trim().replace(/^["']+|["']+$/g, '');
                    }
                    if (!clean || clean === 'null' || clean === 'undefined') return fallback;
                    // Reject values that look like broken attribute fragments
                    if (/alt\s*=|onerror\s*=|javascript:/i.test(clean)) return fallback;
                    if (/^https?:\/\//i.test(clean) || clean.indexOf('data:') === 0) return clean;
                    if (clean.indexOf('/storage/') !== -1 || clean.indexOf('admin/assets/') !== -1) {
                        if (/^https?:\/\//i.test(clean) || clean.charAt(0) === '/') return clean;
                        return imageBasePath.replace(/\/$/, '') + '/' + clean.replace(/^\//, '');
                    }
                    clean = clean.replace(/^\/?storage\//, '');
                    return imageBasePath + '/storage/' + clean;
                } catch (e) {
                    return fallback;
                }
            }

            function storePosProductOptions(productId, variants, addons) {
                window.posProductOptionsById[String(productId)] = {
                    variants: Array.isArray(variants) ? variants : [],
                    addons: Array.isArray(addons) ? addons : []
                };
            }

            function getPosProductOptions(productId, $el) {
                var cached = window.posProductOptionsById[String(productId)];
                if (cached && ((cached.variants && cached.variants.length) || (cached.addons && cached.addons.length))) {
                    return cached;
                }
                var variants = [];
                var addons = [];
                if ($el && $el.length) {
                    // Prefer jQuery auto-parsed data-* first
                    var dv = $el.data('variants');
                    var da = $el.data('addons');
                    if (Array.isArray(dv)) variants = dv;
                    if (Array.isArray(da)) addons = da;
                    if (!variants.length) {
                        try {
                            var rawV = $el.attr('data-variants') || '[]';
                            rawV = rawV.replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&amp;/g, '&');
                            var parsedV = JSON.parse(rawV);
                            if (Array.isArray(parsedV)) variants = parsedV;
                        } catch (e) {}
                    }
                    if (!addons.length) {
                        try {
                            var rawA = $el.attr('data-addons') || '[]';
                            rawA = rawA.replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&amp;/g, '&');
                            var parsedA = JSON.parse(rawA);
                            if (Array.isArray(parsedA)) addons = parsedA;
                        } catch (e) {}
                    }
                }
                storePosProductOptions(productId, variants, addons);
                return window.posProductOptionsById[String(productId)];
            }

            // ── Bank loader (global scope so all handlers can access it) ──
            function loadBanksInto(selector) {
                var authToken = localStorage.getItem("authToken");
                var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                $.ajax({
                    url: '/api/banks/data',
                    method: 'GET',
                    headers: { "Authorization": "Bearer " + authToken },
                    data: { selectedSubAdminId: selectedSubAdminId, per_page: 100 },
                    success: function (res) {
                        var $sel = $(selector);
                        var currentVal = $sel.val();
                        $sel.empty().append('<option value="">Select Bank</option>');
                        if (res.status && res.data) {
                            $.each(res.data, function (i, bank) {
                                $sel.append('<option value="' + bank.id + '">' + bank.bank_name + '</option>');
                            });
                        }
                        if (currentVal) $sel.val(currentVal);
                    }
                });
            }
            function loadBanks() { loadBanksInto("#debitBankSelect"); }

            // Cash+Online: sync Enter/Online amounts when total changes (GST, items, etc.)
            function syncCashOnlineAmounts() {
                if ($("input[name='payment_method']:checked").val() !== "cash+online") {
                    return;
                }
                let coType = $("#cashOnlinePaidTypeSelect").val();
                let total = parseFloat($("input[name='total']").val()) || 0;
                let cash = parseFloat($("#cashAmount").val()) || 0;

                if (!coType) {
                    return;
                }

                if (coType === "fully") {
                    let online = Math.max(0, total - cash);
                    $("#onlineAmount").val(online.toFixed(2));
                    $("#cashOnlineEnterAmount").val(total.toFixed(2));
                    $("#cashOnlinePendingAmount").val("0.00");
                } else if (coType === "partially") {
                    let online = parseFloat($("#onlineAmount").val()) || 0;
                    $("#cashOnlineEnterAmount").val((cash + online).toFixed(2));
                    $("#cashOnlinePendingAmount").val(Math.max(0, total - cash - online).toFixed(2));
                }
            }

            function updateCashOnlinePending() {
                let coType = $("#cashOnlinePaidTypeSelect").val();
                let total = parseFloat($("input[name='total']").val()) || 0;
                let cash = parseFloat($("#cashAmount").val()) || 0;
                let online = parseFloat($("#onlineAmount").val()) || 0;
                $("#paymentError").remove();

                if (coType === "fully") {
                    let remaining = Math.max(0, total - cash);
                    $("#onlineAmount").val(remaining.toFixed(2));
                    $("#cashOnlinePendingAmount").val("0.00");
                } else if (coType === "partially") {
                    if (cash + online > total) {
                        const currencySymbol = '{{ $currency_symbol }}';
                        const currencyPosition = '{{ $currency_position }}';
                        let formatted = currencyPosition === 'right' ? total.toFixed(2) + currencySymbol : currencySymbol + total.toFixed(2);
                        if (!$("#paymentError").length) {
                            $("#cashOnlineBox").append('<span id="paymentError" style="color:red;font-size:13px;">Cash + Online cannot exceed Total Amount ' + formatted + '</span>');
                        }
                    }
                    $("#cashOnlineEnterAmount").val((cash + online).toFixed(2));
                    $("#cashOnlinePendingAmount").val(Math.max(0, total - (cash + online)).toFixed(2));
                }
            }

            // Active class handled by delegated $(document).on("click", ".paymentmethod") below
            document.querySelectorAll('.typemethod').forEach(el => {
                el.addEventListener('click', function() {
                    document.querySelectorAll('input[name="type"]').forEach(radio => radio.checked =
                        false);
                    this.querySelector('input[type="radio"]').checked = true;
                    document.querySelectorAll('.typemethod').forEach(item => item.classList.remove(
                        'active'));
                    this.classList.add('active');
                });
            });
            $(document).ready(function() {
                $(document).on("click", ".paymentmethod", function() {
                    let value = $(this).find("input[name='payment_method']").val();

                    $("input[name='payment_method']").prop("checked", false);
                    $(this).find("input[name='payment_method']").prop("checked", true);
                    $(".paymentmethod").removeClass("active");
                    $(this).addClass("active");

                    // Methods that need Paid Type sub-panel
                    const paidTypeMethods = ['cash', 'debit card', 'scan'];

                    if (value === "cash+online") {
                        $("#cashOnlineBox").slideDown();
                        $("#paidTypeBox").slideUp();
                        // Clear all errors when switching
                        $("#paidTypeError, #enterAmountError, #debitBankError, #scanBankError, #cashOnlinePaidTypeError, #cashOnlineEnterAmountError, #cashAmountError, #cashOnlineBankError").text("");
                        // Reset cash+online fields
                        // $("#cashOnlinePaidTypeSelect").val("");
                        // $("#cashOnlinePaidAmountBox").hide();
                        // $("#cashOnlineEnterAmount").val("");
                        // $("#cashOnlinePendingAmount").val("");
                        // let total = parseFloat($("input[name='total']").val()) || 0;
                        // $("#cashAmount").val("");
                        // syncCashOnlineAmounts();
                        $("#cashAmount").val("");
                        try { loadBanksInto("#cashOnlineBankSelect"); } catch(e) { console.warn("loadBanksInto error:", e); }
                        $("#cashOnlinePaidTypeSelect").val(defaultPaidType || "fully").trigger("change");
                        // Load banks (non-blocking)
                        try { loadBanksInto("#cashOnlineBankSelect"); } catch(e) { console.warn("loadBanksInto error:", e); }
                    } else if (paidTypeMethods.includes(value)) {
                        $("#cashOnlineBox").slideUp();
                        $("#paidTypeBox").slideDown();
                        // Clear all errors when switching
                        $("#paidTypeError, #enterAmountError, #debitBankError, #scanBankError, #cashOnlinePaidTypeError, #cashOnlineEnterAmountError, #cashAmountError, #cashOnlineBankError").text("");
                        // Reset to "Select Paid Type" — hide amount+extra fields until user selects
                        // $("#paidTypeSelect").val("");
                        // $("#paidAmountBox").hide();
                        // $("#debitExtraFields").hide();
                        // $("#scanExtraFields").hide();
                        // $("#enterAmount").prop("readonly", false).val("");
                        // $("#pendingAmount").prop("readonly", true).val("");

                        // Default paid type from Shop Settings
                        $("#debitBankSelect").val(""); $("#debitRemark").val("");
                        $("#scanBankSelect").val(""); $("#scanRemark").val("");
                        if (value === "debit card") {
                            try { loadBanks(); } catch(e) {}
                        } else if (value === "scan") {
                            try { loadBanksInto("#scanBankSelect"); } catch(e) {}
                        }
                        $("#paidTypeSelect").val(defaultPaidType || "fully").trigger("change");
                        // Pre-load banks silently for debit/scan
                        if (value === "debit card") {
                            try { loadBanks(); } catch(e) {}
                        } else if (value === "scan") {
                            try { loadBanksInto("#scanBankSelect"); } catch(e) {}
                        }
                        // Hide the other type's extra fields and clear
                        $("#debitBankSelect").val(""); $("#debitRemark").val("");
                        $("#scanBankSelect").val(""); $("#scanRemark").val("");
                    } else {
                        // Pay Later — hide everything
                        $("#cashOnlineBox").slideUp();
                        $("#paidTypeBox").slideUp();
                        $("#paidTypeError, #enterAmountError, #debitBankError, #scanBankError, #cashOnlinePaidTypeError, #cashOnlineEnterAmountError, #cashAmountError, #cashOnlineBankError").text("");
                    }
                });

                // Paid Type dropdown change
               /* $("#paidTypeSelect").on("change", function() {
                    let val = $(this).val();
                    let currentPayment = $("input[name='payment_method']:checked").val();

                    if (val === "") {
                        // "Select Paid Type" — hide amount fields
                        $("#paidAmountBox").slideUp();
                        $("#enterAmount").val("");
                        $("#pendingAmount").val("");
                        return;
                    }
                    // Always show amount fields for both Fully and Partially
                    $("#paidAmountBox").slideDown();
                    // Show debit/scan extra fields based on current payment method
                    if (currentPayment === "debit card") {
                        $("#debitExtraFields").show();
                        $("#scanExtraFields").hide();
                    } else if (currentPayment === "scan") {
                        $("#debitExtraFields").hide();
                        $("#scanExtraFields").show();
                    } else {
                        $("#debitExtraFields").hide();
                        $("#scanExtraFields").hide();
                    }
                    let total = parseFloat($("input[name='total']").val()) || 0;
                    if (val === "partially") {
                        $("#enterAmount").prop("readonly", false).val(total.toFixed(2));
                        $("#pendingAmount").prop("readonly", true).val("0.00");
                    } else {
                        // Fully — both readonly
                        $("#enterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#pendingAmount").prop("readonly", true).val("0.00");
                    }
                }); */

                $("#paidTypeSelect").on("change", function() {
                    let val = $(this).val();
                    let currentPayment = $("input[name='payment_method']:checked").val();

                    if (val === "") {
                        $("#paidAmountBox").slideUp();
                        $("#enterAmount").val("");
                        $("#pendingAmount").val("");
                        return;
                    }

                    // paidAmountBox always shown (bank/remark fields andar hoy che)
                    $("#paidAmountBox").slideDown();

                    // Enter Amount / Pending Amount nu row FULLY ma hide, PARTIALLY ma show
                    if (val === "partially") {
                        $("#paidAmountRow").slideDown();
                    } else {
                        $("#paidAmountRow").slideUp();
                    }

                    if (currentPayment === "debit card") {
                        $("#debitExtraFields").show();
                        $("#scanExtraFields").hide();
                    } else if (currentPayment === "scan") {
                        $("#debitExtraFields").hide();
                        $("#scanExtraFields").show();
                    } else {
                        $("#debitExtraFields").hide();
                        $("#scanExtraFields").hide();
                    }

                    let total = parseFloat($("input[name='total']").val()) || 0;
                    if (val === "partially") {
                        $("#enterAmount").prop("readonly", false).val(total.toFixed(2));
                        $("#pendingAmount").prop("readonly", true).val("0.00");
                    } else {
                        $("#enterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#pendingAmount").prop("readonly", true).val("0.00");
                    }
                });

                // Cash+Online Paid Type dropdown change
                /* $("#cashOnlinePaidTypeSelect").on("change", function () {
                    let val = $(this).val();
                    if (val === "") {
                        $("#cashOnlinePaidAmountBox").slideUp();
                        $("#cashOnlineEnterAmount").val("");
                        $("#cashOnlinePendingAmount").val("");
                        return;
                    }
                    $("#cashOnlinePaidAmountBox").slideDown();
                    let total = parseFloat($("input[name='total']").val()) || 0;
                    if (val === "partially") {
                        $("#cashOnlineEnterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#cashOnlinePendingAmount").prop("readonly", true);
                        $("#onlineAmount").prop("readonly", false);
                        syncCashOnlineAmounts();
                        updateCashOnlinePending();
                    } else {
                        // Fully — Enter Amount and Online Amount readonly; Online defaults to full total
                        $("#cashOnlineEnterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#cashOnlinePendingAmount").prop("readonly", true).val("0.00");
                        $("#onlineAmount").prop("readonly", true);
                        syncCashOnlineAmounts();
                    }
                }); */
                $("#cashOnlinePaidTypeSelect").on("change", function () {
                    let val = $(this).val();
                    if (val === "") {
                        $("#cashOnlinePaidAmountBox").slideUp();
                        $("#cashOnlineEnterAmount").val("");
                        $("#cashOnlinePendingAmount").val("");
                        return;
                    }

                    // Box (Cash/Online amount, Bank, Remark) hammesha batavo
                    $("#cashOnlinePaidAmountBox").slideDown();

                    // Enter Amount / Pending Amount nu row FULLY ma hide, PARTIALLY ma show
                    if (val === "partially") {
                        $("#cashOnlinePaidAmountRow").slideDown();
                    } else {
                        $("#cashOnlinePaidAmountRow").slideUp();
                    }

                    let total = parseFloat($("input[name='total']").val()) || 0;
                    if (val === "partially") {
                        $("#cashAmount").val("");
                        $("#onlineAmount").val("");
                        $("#cashOnlineEnterAmount").prop("readonly", true).val("0.00");
                        $("#cashOnlinePendingAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#onlineAmount").prop("readonly", false);
                        syncCashOnlineAmounts();
                        updateCashOnlinePending();
                    } else {
                        // Fully — Enter Amount and Online Amount readonly; Online defaults to full total
                        $("#cashOnlineEnterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#cashOnlinePendingAmount").prop("readonly", true).val("0.00");
                        $("#onlineAmount").prop("readonly", true);
                        syncCashOnlineAmounts();
                    }
                });

                // Bind events
                $(document).on("input", "#cashAmount", updateCashOnlinePending);
                $(document).on("input", "#onlineAmount", updateCashOnlinePending);

                // Enter Amount → auto-calculate Pending Amount
                $("#enterAmount").on("input", function() {
                    let total = parseFloat($("input[name='total']").val()) || 0;
                    let entered = parseFloat($(this).val()) || 0;
                    let pending = Math.max(0, total - entered);
                    $("#pendingAmount").val(pending.toFixed(2));
                });

            });
            // $(document).ready(function() {
            //     $(".typemethod").on("click", function() {
            //         let value = $(this).find("input[name='type']").val();
            //         $("input[name='type']").prop("checked", false);
            //         $(this).find("input[name='type']").prop("checked", true);
            //         if (value === "dinein") {
            //             $("#table_no_container").slideDown();
            //         } else {
            //             $("#table_no_container").slideUp();
            //             $("#table_no_select").val("").trigger("change");
            //         }
            //     });
            // });

            function applyDefaultPosPayment() {
                var pay = defaultPaymentMethod || 'cash';
                var paid = defaultPaidType || 'fully';
                var $method = $(".paymentmethod").has("input[name='payment_method'][value='" + pay + "']").first();
                if (!$method.length) {
                    $method = $(".paymentmethod").has("input[name='payment_method'][value='cash']").first();
                    pay = 'cash';
                }
                if ($method.length) {
                    $method.trigger('click');
                } else {
                    $("input[name='payment_method']").prop('checked', false);
                    $("input[name='payment_method'][value='" + pay + "']").prop('checked', true);
                    $(".paymentmethod").removeClass('active');
                    $("input[name='payment_method'][value='" + pay + "']").closest('.paymentmethod').addClass('active');
                }
                if ($("#paidTypeSelect").length) {
                    $("#paidTypeSelect").val(paid === 'partially' ? 'partially' : 'fully').trigger('change');
                }
                if ($("#cashOnlinePaidTypeSelect").length && pay === 'cash+online') {
                    $("#cashOnlinePaidTypeSelect").val(paid === 'partially' ? 'partially' : 'fully').trigger('change');
                }
            }
            window.applyDefaultPosPayment = applyDefaultPosPayment;

            $(document).ready(function() {
                selectedItems = new Map();
                var authToken = localStorage.getItem("authToken");

                // Apply default GST from settings (shows/hides tax rows)
                $("input[name='gst_option']").prop('checked', false);
                $("input[name='gst_option'][value='" + (defaultGstOption || 'without') + "']").prop('checked', true).trigger('change');

                // Default payment + paid type from Shop Settings
                applyDefaultPosPayment();

                function formatCurrency(amount, symbol, position) {
                    return position === 'right' ? amount + symbol : symbol + amount;
                }

                function formatStockQty(qty) {
                    if (qty === null || qty === undefined || qty === '') return '0';
                    let num = parseFloat(qty);
                    if (isNaN(num)) return qty;
                    if (num % 1 === 0) {
                        return num.toString();
                    }
                    let fixed = num.toFixed(2);
                    if (parseFloat(fixed) === num) {
                        return fixed;
                    }
                    return parseFloat(num.toFixed(4)).toString();
                }

                // function calculateTotals() {
                //     const firstTaxElement = $(".tax-value").first();
                //     const currencySymbol = firstTaxElement.data("symbol") || '{{ $currency_symbol }}'; // fallback
                //     const currencyPosition = firstTaxElement.data("position") || '{{ $currency_position }}'; // fallback

                //     let subtotal = 0;

                //     // Calculate subtotal from selected items
                //     selectedItems.forEach(function (item) {
                //         const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                //         subtotal += price * item.quantity;
                //     });

                //     // Update subtotal display
                //     $(".setvalue li:nth-child(1) h6").html(formatCurrency(subtotal.toFixed(2), currencySymbol, currencyPosition));
                //     $("input[name='subtotal']").val(subtotal.toFixed(2));

                //     if (subtotal > 0) {
                //         let totalTax = 0;
                //         let gstOption = $("input[name='gst_option']:checked").val();

                //         // $(".taxList").each(function () {
                //         //     const taxRate = parseFloat($(this).find(".tax-value").data("rate")) / 100;
                //         //     const taxAmount = subtotal * taxRate;

                //         //     const taxEl = $(this).find(".tax-value");
                //         //     const symbol = taxEl.data("symbol") || currencySymbol;
                //         //     const position = taxEl.data("position") || currencyPosition;

                //         //     taxEl.html(formatCurrency(taxAmount.toFixed(2), symbol, position));
                //         //     $(this).show();

                //         //     totalTax += taxAmount;
                //         // });
                //         if (gstOption === "with") {
                //             $(".taxList").each(function () {
                //                 const taxRate = parseFloat($(this).find(".tax-value").data("rate")) / 100;
                //                 const taxAmount = subtotal * taxRate;

                //                 const taxEl = $(this).find(".tax-value");
                //                 const symbol = taxEl.data("symbol") || currencySymbol;
                //                 const position = taxEl.data("position") || currencyPosition;

                //                 taxEl.html(formatCurrency(taxAmount.toFixed(2), symbol, position));
                //                 $(this).show();

                //                 totalTax += taxAmount;
                //             });
                //         } else {
                //             // Hide and reset tax values if GST = without
                //             $(".taxList").hide().find(".tax-value").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                //             totalTax = 0;
                //         }

                //         const discountPercentage = parseFloat($("#discount").val()) || 0;
                //         const discountAmount = subtotal * (discountPercentage / 100);
                //         const priceAfterDiscount = subtotal - discountAmount;
                //         const total = subtotal + totalTax - discountAmount;
                //         $("#onlineAmount").val(total.toFixed(2));

                //         // $("input[name='payment_method']").on("change", function(){
                //         //     if($(this).val() === "cash+online"){
                //         //         $("#onlineAmount").prop("disabled", true).val(total.toFixed(2)); // default full total
                //         //         $("#cashAmount").val(""); // reset cash
                //         //     }else{
                //         //         $("#onlineAmount").prop("disabled", false).val("");
                //         //         $("#cashAmount").val("");
                //         //     }
                //         // });

                //         $("#cashAmount").on("input", function(){
                //             let cash = parseFloat($(this).val()) || 0;
                //             let remaining = total - cash;

                //             $("#paymentError").remove();

                //             if(remaining < 0){
                //                 $("#cashOnlineBox").append(
                //                     '<span id="paymentError" style="color:red; font-size:13px;">' +
                //                     'Cash cannot be greater than ' +
                //                     formatCurrency(total.toFixed(2), currencySymbol, currencyPosition) +
                //                     '</span>'
                //                 );
                //                 $("#onlineAmount").val(0);
                //             }else{
                //                 $("#onlineAmount").val(remaining.toFixed(2));
                //             }
                //         });

                //         $(".discount-amount").html(formatCurrency(discountAmount.toFixed(2), currencySymbol, currencyPosition));
                //         $("input[name='discount_amount']").val(discountAmount.toFixed(2));

                //         $(".price-after-discount").html(formatCurrency(priceAfterDiscount.toFixed(2), currencySymbol, currencyPosition));
                //         $("input[name='price_after_discount']").val(priceAfterDiscount.toFixed(2));

                //         $(".total-value h6").html(formatCurrency(total.toFixed(2), currencySymbol, currencyPosition));
                //         $(".btn-totallabel h6").html('Total Amount : ' + formatCurrency(total.toFixed(2), currencySymbol, currencyPosition));
                //         $("input[name='total']").val(total.toFixed(2));
                //     } else {
                //         $(".taxList").hide().find("h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                //         $(".discount-amount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                //         $(".price-after-discount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                //         $(".total-value h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                //         $(".btn-totallabel h6").html('Total Amount : ' + formatCurrency("0.00", currencySymbol, currencyPosition));

                //         $("input[name='discount_amount']").val("0.00");
                //         $("input[name='price_after_discount']").val("0.00");
                //         $("input[name='total']").val("0.00");
                //     }

                // }
                function calculateTotals() {
                    const firstTaxElement = $(".tax-value").first();
                    const currencySymbol = firstTaxElement.data("symbol") || '{{ $currency_symbol }}'; // fallback
                    const currencyPosition = firstTaxElement.data("position") ||
                        '{{ $currency_position }}'; // fallback

                    let subtotal = 0;

                    // Calculate subtotal from selected items (after item-level discount when allowed)
                    selectedItems.forEach(function(item) {
                        const price = parseFloat(String(item.price).replace(/[^0-9.-]+/g, "")) || 0;
                        const gross = price * item.quantity;
                        const discPct = canItemDiscount ? (parseFloat(item.item_discount_percent) || 0) : 0;
                        subtotal += gross - (gross * discPct / 100);
                    });

                    // Update subtotal display
                    $(".setvalue li:nth-child(1) h6").html(
                        formatCurrency(subtotal.toFixed(2), currencySymbol, currencyPosition)
                    );
                    $("input[name='subtotal']").val(subtotal.toFixed(2));

                    if (subtotal > 0) {
                        // ✅ Calculate discount & price after discount FIRST
                        const discountPercentage = parseFloat($("#discount").val()) || 0;
                        const discountAmount = subtotal * (discountPercentage / 100);
                        const priceAfterDiscount = subtotal - discountAmount;

                        let totalTax = 0;
                        const gstOption = $("input[name='gst_option']:checked").val();

                        // Reset static tax-master rows; CGST/SGST rows are rendered dynamically
                        $(".taxList").not('[data-tax-id="cgst"], [data-tax-id="sgst"], [data-dynamic-gst="1"]').hide().each(function() {
                            const taxEl = $(this).find(".tax-value");
                            taxEl.html(formatCurrency("0.00", taxEl.data("symbol") || currencySymbol, taxEl.data("position") || currencyPosition));
                        });
                        $('.taxList[data-dynamic-gst="1"]').remove();

                        function upsertSplitGstRow(key, label, amount) {
                            let $row = $('.taxList[data-tax-id="' + key + '"]');
                            if (!$row.length) {
                                const $anchor = $(".setvalue").find(".taxList").first();
                                const html = `<li class="taxList" data-tax-id="${key}" data-dynamic-gst="1">
                                    <h5>${label}</h5>
                                    <h6 class="tax-value" data-symbol="${currencySymbol}" data-position="${currencyPosition}">${formatCurrency("0.00", currencySymbol, currencyPosition)}</h6>
                                </li>`;
                                if ($anchor.length) {
                                    $anchor.before(html);
                                } else {
                                    $(".setvalue").append(html);
                                }
                                $row = $('.taxList[data-tax-id="' + key + '"]');
                            } else {
                                $row.attr('data-dynamic-gst', '1');
                                $row.find('h5').text(label);
                            }
                            const taxEl = $row.find(".tax-value");
                            taxEl.html(formatCurrency(amount.toFixed(2), taxEl.data("symbol") || currencySymbol, taxEl.data("position") || currencyPosition));
                            $row.show();
                        }

                        function renderCgstSgstFromAgg(rateAmountMap) {
                            // rateAmountMap: { "5": 34.95, "18": 100 }
                            const splitAgg = {};
                            Object.keys(rateAmountMap).forEach(function(rateKey) {
                                const fullRate = parseFloat(rateKey) || 0;
                                const amount = parseFloat(rateAmountMap[rateKey]) || 0;
                                if (amount <= 0 && fullRate <= 0) return;
                                const halfRate = Math.round((fullRate / 2) * 10000) / 10000;
                                const cgstAmt = Math.round((amount / 2) * 100) / 100;
                                const sgstAmt = Math.round((amount - cgstAmt) * 100) / 100;
                                const hk = String(halfRate);
                                if (!splitAgg[hk]) splitAgg[hk] = { rate: halfRate, cgst: 0, sgst: 0 };
                                splitAgg[hk].cgst += cgstAmt;
                                splitAgg[hk].sgst += sgstAmt;
                            });
                            Object.keys(splitAgg).forEach(function(hk) {
                                const row = splitAgg[hk];
                                const rateLabel = String(row.rate).replace(/\.00$/, '').replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
                                upsertSplitGstRow('cgst-' + hk, 'CGST (' + rateLabel + '%)', row.cgst);
                                upsertSplitGstRow('sgst-' + hk, 'SGST (' + rateLabel + '%)', row.sgst);
                                totalTax += row.cgst + row.sgst;
                            });
                        }

                        if (gstOption === "with" && subtotal > 0) {
                            if (gstApplyMode === 'overall') {
                                const rate = parseFloat(overallGstRate) || 0;
                                const amount = priceAfterDiscount * (rate / 100);
                                const rateMap = {};
                                rateMap[String(rate)] = amount;
                                renderCgstSgstFromAgg(rateMap);
                            } else {
                                const rateMap = {};
                                selectedItems.forEach(function(item) {
                                    const taxId = item.tax_id || null;
                                    const taxRatePct = parseFloat(item.tax_rate);
                                    if (!taxId || isNaN(taxRatePct)) {
                                        return; // no product tax → 0%
                                    }
                                    const price = parseFloat(String(item.price).replace(/[^0-9.-]+/g, "")) || 0;
                                    const gross = price * item.quantity;
                                    const discPct = canItemDiscount ? (parseFloat(item.item_discount_percent) || 0) : 0;
                                    const lineNet = gross - (gross * discPct / 100);
                                    const lineShare = lineNet * (1 - (discountPercentage / 100));
                                    let lineTax = 0;
                                    if (item.tax_inclusive) {
                                        const base = lineShare / (1 + (taxRatePct / 100));
                                        lineTax = lineShare - base;
                                    } else {
                                        lineTax = lineShare * (taxRatePct / 100);
                                    }
                                    const rk = String(taxRatePct);
                                    rateMap[rk] = (rateMap[rk] || 0) + lineTax;
                                });
                                renderCgstSgstFromAgg(rateMap);
                            }
                        } else {
                            totalTax = 0;
                        }

                        // Whole-bill service tax (settings %) — after discount, not split into CGST/SGST
                        let serviceTaxAmt = 0;
                        if (serviceTaxEnabled && (parseFloat(serviceTaxRate) || 0) > 0 && priceAfterDiscount > 0) {
                            serviceTaxAmt = Math.round((priceAfterDiscount * (parseFloat(serviceTaxRate) / 100)) * 100) / 100;
                            const svcRateLabel = String(parseFloat(serviceTaxRate)).replace(/\.00$/, '').replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
                            upsertSplitGstRow('service-tax', (serviceTaxName || 'Service Tax') + ' (' + svcRateLabel + '%)', serviceTaxAmt);
                            totalTax += serviceTaxAmt;
                        }

                        // ✅ Final total (price after discount + tax + shipping)
                        const shipping = parseFloat($("#shipping").val()) || 0;
                        const total = priceAfterDiscount + totalTax + shipping;
                        // Update values
                        $(".discount-amount").html(formatCurrency(discountAmount.toFixed(2), currencySymbol,
                            currencyPosition));
                        $("input[name='discount_amount']").val(discountAmount.toFixed(2));

                        $(".price-after-discount").html(formatCurrency(priceAfterDiscount.toFixed(2), currencySymbol,
                            currencyPosition));
                        $("input[name='price_after_discount']").val(priceAfterDiscount.toFixed(2));

                        if (discountAmount > 0) {
                            $(".discount-summary-row, .price-after-discount-row").show();
                        } else {
                            $(".discount-summary-row, .price-after-discount-row").hide();
                        }

                        $(".total-value h6").html(formatCurrency(total.toFixed(2), currencySymbol, currencyPosition));
                        $(".btn-totallabel h6").html('Total Amount : ' + formatCurrency(total.toFixed(2),
                            currencySymbol, currencyPosition));
                        $("input[name='total']").val(total.toFixed(2));

                        // Keep pending amount in sync when on "partially"
                        if ($("#paidTypeSelect").val() === "partially") {
                            let entered = parseFloat($("#enterAmount").val()) || 0;
                            let pending = Math.max(0, total - entered);
                            $("#enterAmount").val(total.toFixed(2));
                            $("#pendingAmount").val("0.00");
                        } else if ($("#paidTypeSelect").val() === "fully") {
                            // Fully — keep enter amount = total, pending = 0
                            $("#enterAmount").prop("readonly", true).val(total.toFixed(2));
                            $("#pendingAmount").val("0.00");
                        }

                        // Sync Cash+Online when total changes (GST, discount, items, etc.)
                        syncCashOnlineAmounts();
                        updateCashOnlinePending();
                    } else {
                        // Reset everything if no subtotal
                        $(".taxList").hide().find("h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                        $(".discount-summary-row, .price-after-discount-row").hide();
                        $(".discount-amount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                        $(".price-after-discount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                        $(".total-value h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                        $(".btn-totallabel h6").html('Total Amount : ' + formatCurrency("0.00", currencySymbol,
                            currencyPosition));

                        $("input[name='discount_amount']").val("0.00");
                        $("input[name='price_after_discount']").val("0.00");
                        $("input[name='total']").val("0.00");
                        $("#onlineAmount").val("0.00");
                    }
                }



                window.updateTotalItems = function() {
                    $(".totalitem h4").text("Total items : " + selectedItems.size);
                    renderSelectedItems();
                    calculateTotals();
                    if (typeof window.refreshActivePosBillTab === 'function') {
                        window.refreshActivePosBillTab();
                    }
                }

                function formatPosMoney(amount) {
                    var price = parseFloat(amount || 0).toFixed(2);
                    return posCurrencyPosition === 'right' ? (price + posCurrencySymbol) : (posCurrencySymbol + price);
                }

                function addProductToCart(meta) {
                    var uniqueId = meta.uniqueId || meta.productId;
                    if (selectedItems.has(uniqueId)) {
                        var existing = selectedItems.get(uniqueId);
                        existing.quantity += 1;
                        selectedItems.set(uniqueId, existing);
                    } else {
                        if (meta.item_discount_percent == null) meta.item_discount_percent = 0;
                        selectedItems.set(uniqueId, meta);
                    }
                    updateTotalItems();
                }
                window.addProductToCart = addProductToCart;

                var pendingPosProduct = null;

                function refreshPosOptionPrice() {
                    if (!pendingPosProduct) return;
                    var unit = parseFloat(pendingPosProduct.basePrice) || 0;
                    var $checkedVariant = $('#posVariantOptions input[name="pos_variant"]:checked');
                    if ($checkedVariant.length) {
                        unit = parseFloat($checkedVariant.data('price')) || unit;
                    }
                    $('#posAddonOptions input.pos-addon-check:checked').each(function () {
                        unit += parseFloat($(this).data('price')) || 0;
                    });
                    $('#posOptionLinePrice').text(formatPosMoney(unit));
                    pendingPosProduct.computedUnit = unit;
                }

                function openPosOptionModal(meta, variants, addons) {
                    pendingPosProduct = meta;
                    $('#posOptionProductName').text(meta.name);
                    $('#posOptionProductId').val(meta.productId);

                    var $v = $('#posVariantOptions').empty();
                    if (variants.length) {
                        $v.append('<label class="form-label fw-bold">Size / Variant</label>');
                        variants.forEach(function (v, idx) {
                            var vName = escapeHtmlAttr(v.name || '');
                            $v.append(
                                '<div class="form-check">' +
                                '<input class="form-check-input" type="radio" name="pos_variant" id="pos_var_' + v.id + '" value="' + v.id + '" data-price="' + v.price + '" data-name="' + vName + '" ' + (idx === 0 ? 'checked' : '') + '>' +
                                '<label class="form-check-label" for="pos_var_' + v.id + '">' + (v.name || '') + ' — ' + formatPosMoney(v.price) + '</label>' +
                                '</div>'
                            );
                        });
                    }

                    var $a = $('#posAddonOptions').empty();
                    if (addons.length) {
                        $a.append('<label class="form-label fw-bold">Add-ons</label>');
                        addons.forEach(function (ad) {
                            var aName = escapeHtmlAttr(ad.name || '');
                            $a.append(
                                '<div class="form-check">' +
                                '<input class="form-check-input pos-addon-check" type="checkbox" id="pos_ad_' + ad.id + '" value="' + ad.id + '" data-price="' + ad.price + '" data-name="' + aName + '">' +
                                '<label class="form-check-label" for="pos_ad_' + ad.id + '">' + (ad.name || '') + ' (+' + formatPosMoney(ad.price) + ')</label>' +
                                '</div>'
                            );
                        });
                    }

                    refreshPosOptionPrice();
                    var modalEl = document.getElementById('posVariantAddonModal');
                    if (!modalEl) {
                        console.error('posVariantAddonModal missing');
                        return;
                    }
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
                window.openPosOptionModal = openPosOptionModal;

                $(document).on('change', '#posVariantOptions input, #posAddonOptions input', refreshPosOptionPrice);

                $(document).on('click', '#posOptionConfirmAdd', function () {
                    if (!pendingPosProduct) return;
                    var variantId = null;
                    var variantName = '';
                    var $v = $('#posVariantOptions input[name="pos_variant"]:checked');
                    if ($v.length) {
                        variantId = parseInt($v.val(), 10);
                        variantName = $v.data('name') || '';
                    }
                    var addonIds = [];
                    var addonNames = [];
                    $('#posAddonOptions input.pos-addon-check:checked').each(function () {
                        addonIds.push(parseInt($(this).val(), 10));
                        addonNames.push($(this).data('name'));
                    });

                    var unit = pendingPosProduct.computedUnit || pendingPosProduct.basePrice;
                    var displayName = pendingPosProduct.name;
                    if (variantName) displayName += ' (' + variantName + ')';
                    if (addonNames.length) displayName += ' + ' + addonNames.join(', ');

                    var uniqueId = String(pendingPosProduct.productId) + '-v' + (variantId || 0) + '-a' + addonIds.join('_');
                    addProductToCart({
                        id: uniqueId,
                        productId: pendingPosProduct.productId,
                        variant_id: variantId,
                        addon_ids: addonIds,
                        categoryId: pendingPosProduct.categoryId,
                        name: displayName,
                        price: formatPosMoney(unit),
                        image: pendingPosProduct.image,
                        code: pendingPosProduct.code,
                        quantity_type: pendingPosProduct.quantity_type,
                        quantity_type_id: pendingPosProduct.quantity_type_id,
                        quantity_type123: pendingPosProduct.quantity_type123,
                        quantity: 1,
                        stock: pendingPosProduct.stock,
                        item_discount_percent: 0,
                        tax_id: pendingPosProduct.tax_id || null,
                        tax_rate: pendingPosProduct.tax_rate != null ? pendingPosProduct.tax_rate : null,
                        tax_name: pendingPosProduct.tax_name || null,
                        tax_inclusive: !!pendingPosProduct.tax_inclusive,
                        uniqueId: uniqueId
                    });

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('posVariantAddonModal')).hide();
                    pendingPosProduct = null;
                });

                function renderSelectedItems() {
                    var $productTable = $(".product-table");
                    var productHtml = "";

                    selectedItems.forEach(function(item) {
                        // Extract numeric price for input
                        let numericPrice = parseFloat(String(item.price).replace(/[^0-9.-]+/g, "")) || 0;
                        // Get clean, valid quantity type (exclude null, empty, N/A, undefined, pure numbers)
                        let rawQty = (item.quantity_type || item.quantity_type_id || "").toString().trim();
                        let qtyType = "";
                        if (rawQty && !['undefined', 'null', 'n/a', 'none', '0'].includes(rawQty.toLowerCase()) && isNaN(rawQty)) {
                            qtyType = rawQty;
                        }
                        var discPct = parseFloat(item.item_discount_percent || 0) || 0;
                        var discHtml = canItemDiscount
                            ? `<input type="number" class="form-control form-control-sm item-disc-field" data-id="${item.id}" value="${discPct}" min="0" max="100" step="0.01" title="Item discount %" placeholder="Disc %" style="width:70px;">`
                            : '';
                        var resolvedCartImage = resolveProductImage(item.image);
                        var safeCartImage = escapeHtmlAttr(resolvedCartImage);
                        var safeCartName = escapeHtmlAttr(item.name || 'Product');
                        var safeCartTitle = escapeHtmlText(item.name);
                        var gstCartSuffix = '';
                        // Overall GST: show tax only in totals (CGST/SGST), not after item name
                        if ($("input[name='gst_option']:checked").val() === 'with' && gstApplyMode !== 'overall') {
                            var cartRate = item.tax_rate != null && item.tax_rate !== '' ? parseFloat(item.tax_rate) : NaN;
                            var cartTaxName = (item.tax_name && String(item.tax_name).trim()) ? String(item.tax_name).trim() : 'GST';
                            var cartRateStr = isNaN(cartRate) ? '0' : String(cartRate).replace(/\.00$/, '').replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
                            gstCartSuffix = ` <small class="text-muted">(${escapeHtmlText(cartTaxName)} ${escapeHtmlText(cartRateStr)}%)</small>`;
                        }
                        var safeQtyType = escapeHtmlText(qtyType);
                        var safeNoImage = escapeHtmlAttr(POS_NO_IMAGE);
                        var safeDeleteIcon = escapeHtmlAttr(POS_DELETE_ICON);

                        productHtml += `
    <ul class="product-lists">
        <li class="cart-row">
            <h4 class="cart-item-name">
                <span class="cart-item-title">${safeCartTitle}${gstCartSuffix}</span>
            </h4>

            <div class="cart-item-controls">
                <div class="productimgs">
                    <img src="${safeCartImage}" alt="${safeCartName}" data-fallback="${safeNoImage}" onerror="this.onerror=null;this.src=this.getAttribute('data-fallback')||window.POS_NO_IMAGE;">
                </div>

                <div class="increment-decrement">
                    <div class="input-groups">
                        <input type="button" value="-" class="button-minus dec button" data-id="${item.id}">
                        <input type="text" name="quantity" value="${item.quantity}" class="quantity-field" data-id="${item.id}" data-stock="${item.stock}">
                        <input type="button" value="+" class="button-plus inc button" data-id="${item.id}">
                    </div>
                    <input type="number" class="price-field form-control form-control-sm" data-id="${item.id}" value="${numericPrice}" min="0" step="0.01">
                    ${discHtml}
                    ${qtyType ? `<span class="cart-quantity-type">${safeQtyType}</span>` : ''}
                </div>

                <a class="confirm-text remove-item" href="javascript:void(0);" data-id="${item.id}">
                    <img src="${safeDeleteIcon}" alt="delete">
                </a>
            </div>
        </li>
    </ul>`;
                    });

                    $productTable.html(productHtml);
                }

                $(document).on("input", ".item-disc-field", function () {
                    var itemId = $(this).data("id");
                    var pct = Math.max(0, Math.min(100, parseFloat($(this).val()) || 0));
                    if (selectedItems.has(itemId)) {
                        var item = selectedItems.get(itemId);
                        item.item_discount_percent = pct;
                        selectedItems.set(itemId, item);
                        calculateTotals();
                    }
                });

                $(document).on("click", ".productset", function() {
                    var $product = $(this);
                    if ($product.hasClass('disabled-product')) return;
                    var productId = $product.data("id");
                    var productName = $product.find("h4").text().trim();
                    var productPrice = $product.find(".productsetcontent h6").text().trim();
                    var categoryId = $product.find(".productsetcontent h1").text().trim();
                    var quantity_type = ($product.find(".productsetcontent h2").text() || "").trim();
                    var quantity_type123 = ($product.find(".productsetcontent h3").text() || "").trim();
                    if (quantity_type && ['undefined', 'null'].includes(quantity_type.toLowerCase())) {
                        quantity_type = "";
                    }

                    var productImage = resolveProductImage(
                        $product.find("img.productsetimgin").attr("src") || $product.find("img").first().attr("src")
                    );
                    var productCode = "PT001";
                    var $checkIcon = $product.find(".check-product i");
                    var productStock = posEffectiveStock($product.data("stock"), {
                        product_kind: $product.data("product-kind"),
                        track_inventory: $product.data("track-inventory") !== 0 && $product.data("track-inventory") !== '0',
                        allow_oversell: $product.data("allow-oversell") !== 0 && $product.data("allow-oversell") !== '0'
                    });
                    var productTaxId = $product.data("tax-id") || null;
                    var productTaxRate = $product.attr("data-tax-rate");
                    productTaxRate = (productTaxRate === '' || productTaxRate == null) ? null : parseFloat(productTaxRate);
                    var productTaxName = ($product.attr("data-tax-name") || '').toString().trim() || null;
                    var productTaxInclusive = $product.data("tax-inclusive") === 1 || $product.data("tax-inclusive") === '1';
                    var quantity_type_id = $product.data("quantity_type_id") || quantity_type123 || quantity_type;
                    if (quantity_type_id && ['undefined', 'null'].includes(quantity_type_id.toString().toLowerCase())) {
                        quantity_type_id = "";
                    }
                    var basePrice = parseFloat($product.attr('data-base-price')) || parseFloat(String(productPrice).replace(/[^0-9.-]+/g, '')) || 0;
                    var opts = getPosProductOptions(productId, $product);
                    var variants = opts.variants || [];
                    var addons = opts.addons || [];

                    var meta = {
                        productId: productId,
                        categoryId: categoryId,
                        name: productName,
                        image: productImage,
                        code: productCode,
                        quantity_type: quantity_type,
                        quantity_type_id: quantity_type_id,
                        quantity_type123: quantity_type123,
                        stock: productStock,
                        basePrice: basePrice,
                        tax_id: productTaxId,
                        tax_rate: productTaxRate,
                        tax_name: productTaxName,
                        tax_inclusive: productTaxInclusive
                    };

                    function addPlainOrIncrease() {
                        var uniqueId = productId;
                        if (selectedItems.has(uniqueId)) {
                            var existingItem = selectedItems.get(uniqueId);
                            // Negative selling: always allow increase
                            existingItem.quantity++;
                            selectedItems.set(uniqueId, existingItem);
                            updateTotalItems();
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
                                stock: productStock,
                                item_discount_percent: 0,
                                tax_id: productTaxId,
                                tax_rate: productTaxRate,
                                tax_name: productTaxName,
                                tax_inclusive: productTaxInclusive
                            });
                            $checkIcon.addClass("selected");
                            updateTotalItems();
                        }
                    }

                    if ((variants && variants.length) || (addons && addons.length)) {
                        openPosOptionModal(meta, variants || [], addons || []);
                        $checkIcon.addClass("selected");
                        return;
                    }

                    // Fallback: load variants from product API if grid cache missed them
                    var authToken = localStorage.getItem("authToken");
                    $.ajax({
                        url: "/api/edit_product/" + productId,
                        type: "GET",
                        headers: { Authorization: "Bearer " + authToken },
                        success: function (res) {
                            var p = res.product || res.data || res;
                            var loadedVariants = (p && p.variants) ? p.variants : (res.variants || []);
                            var loadedAddons = (p && p.addons) ? p.addons : (res.addons || []);
                            if (!Array.isArray(loadedVariants)) loadedVariants = [];
                            if (!Array.isArray(loadedAddons)) loadedAddons = [];
                            loadedVariants = loadedVariants.filter(function (v) { return !v.isDeleted; }).map(function (v) {
                                return { id: v.id, name: v.name, sku: v.sku, price: parseFloat(v.price) || 0 };
                            });
                            loadedAddons = loadedAddons.filter(function (a) { return !a.isDeleted; }).map(function (a) {
                                return { id: a.id, name: a.name, price: parseFloat(a.price) || 0 };
                            });
                            storePosProductOptions(productId, loadedVariants, loadedAddons);
                            if (loadedVariants.length || loadedAddons.length) {
                                openPosOptionModal(meta, loadedVariants, loadedAddons);
                                $checkIcon.addClass("selected");
                            } else {
                                addPlainOrIncrease();
                            }
                        },
                        error: function () {
                            addPlainOrIncrease();
                        }
                    });
                });

                $(document).on("click", ".button-plus", function() {
                    var itemId = $(this).data("id");
                    if (selectedItems.has(itemId)) {
                        var item = selectedItems.get(itemId);
                        // Negative selling: no hard stock cap
                        item.quantity++;
                        selectedItems.set(itemId, item);
                        updateTotalItems();
                    }
                });

                $(document).on("change", ".quantity-field", function() {
                    var itemId = $(this).data("id");
                    var newQty = parseInt($(this).val());

                    if (selectedItems.has(itemId)) {
                        var item = selectedItems.get(itemId);

                        if (newQty < 1 || isNaN(newQty)) {
                            newQty = 1;
                        }

                        item.quantity = newQty;
                        selectedItems.set(itemId, item);
                        updateTotalItems();
                        $(this).val(newQty); // update input box with valid value
                    }
                });

                $(document).on("click", ".button-minus", function() {
                    var itemId = $(this).data("id");
                    if (selectedItems.has(itemId) && selectedItems.get(itemId).quantity > 1) {
                        selectedItems.get(itemId).quantity--;
                        updateTotalItems();
                    }
                });

                $(document).on("click", ".remove-item", function() {
                    var itemId = $(this).data("id");
                    var item = selectedItems.get(itemId);
                    if (item) {
                        $(".productset[data-id='" + item.productId + "'] .check-product i").removeClass(
                            "selected");
                        selectedItems.delete(itemId);
                        updateTotalItems();
                    }
                });

                // Handle price field changes
                $(document).on("input", ".price-field", function() {
                    var itemId = $(this).data("id");
                    var newPrice = parseFloat($(this).val()) || 0;
                    const currencySymbol = '{{ $currency_symbol }}';
                    const currencyPosition = '{{ $currency_position }}';
                    
                    if (selectedItems.has(itemId)) {
                        var item = selectedItems.get(itemId);
                        // Format price with currency symbol
                        if (currencyPosition === 'right') {
                            item.price = newPrice.toFixed(2) + currencySymbol;
                        } else {
                            item.price = currencySymbol + newPrice.toFixed(2);
                        }
                        selectedItems.set(itemId, item);
                        calculateTotals();
                    }
                });

                $(".clear_items").click(function() {
                    selectedItems.clear();
                    $(".check-product i").removeClass("selected");
                    updateTotalItems();
                });

                $("#discount").on("input", function() {
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
                        calculateTotals();
                    } else if (isNaN(discountValue)) {
                        $(".error_discount").text("");
                        calculateTotals();
                    } else {
                        $(".error_discount").text("Discount Applied").css("color", "green");
                        calculateTotals();
                    }
                });
                $(document).on("change", "input[name='gst_option']", function() {
                    // Refresh cart labels (GST % after name) + totals for product-applied tax
                    if (typeof updateTotalItems === 'function') {
                        updateTotalItems();
                    } else {
                        calculateTotals();
                    }
                });

                $(".btn-totallabel").click(function(event) {
                    event.preventDefault();
                    var $btn = $(this); // cache the button
                    var originalText = $btn.html();
                    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                    // Show loading text and disable the button
                    $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Placing Order...'
                    ).prop('disabled', true);

                    // Reset errors
                    $(".error_customername, .error_customerphone, .error_peymentmethod, .error_discount").text("");
                    $(".total-value .error_total").remove();
                    $("#enterAmountError, #debitBankError, #scanBankError, #cashOnlineEnterAmountError, #cashAmountError, #cashOnlineBankError, #paidTypeError, #cashOnlinePaidTypeError").text("");

                    // Get form values — empty customer → Unregistered Customer (walk-in)
                    let name = ($("#customer_name_input").val() || "").toString().trim();
                    if (!name || ['select or type customer', 'choose', 'select'].includes(name.toLowerCase())) {
                        name = "Unregistered Customer";
                        var $custSel = $("#customer_name_input");
                        if ($custSel.find('option[value="Unregistered Customer"]').length === 0) {
                            $custSel.append(new Option("Unregistered Customer", "Unregistered Customer", true, true));
                        }
                        $custSel.val("Unregistered Customer").trigger("change.select2");
                    }
                    let phone = ($("input[name='customer_phone']").val() || "").toString().trim();
                    let table_id = $("#table_no_select").val();
                    let selectedPayment = $("input[name='payment_method']:checked").val();
                    let selectedTableType = $("input[name='type']:checked").val();
                    let subtotal = parseFloat($("input[name='subtotal']").val()) || 0;
                    let total = parseFloat($("input[name='total']").val()) || 0;
                    let discount = parseFloat($("#discount").val()) || 0;

                    // Validation
                    let isValid = true;
                    $(".error_customername").text("");

                    if (phone !== "" && !/^\d{10}$/.test(phone)) {
                        $(".error_customerphone").text("Enter a valid 10-digit phone number").css("color", "red");
                        isValid = false;
                    }

                    if (!selectedPayment) {
                        $(".error_peymentmethod").text("Please select a payment method").css("color", "red");
                        isValid = false;
                    }

                    // if (!selectedTableType) {
                    //     $(".error_typemethod").text("Please select a Table Type").css("color", "red");
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

                    // ── Payment-specific inline validations ───────────────
                    const paidTypeMethods = ['cash', 'debit card', 'scan'];
                    const activePaidType  = $("#paidTypeSelect").val();
                    const activeCOType    = $("#cashOnlinePaidTypeSelect").val();

                    // For Cash / Debit / Scan — validate Paid Type selected
                    if (paidTypeMethods.includes(selectedPayment)) {
                        if (!activePaidType) {
                            $("#paidTypeError").text("Please select a paid type.").css("color", "red");
                            isValid = false;
                        }
                    }

                    // For Cash / Debit / Scan — validate Enter Amount if Partially
                    if (paidTypeMethods.includes(selectedPayment) && activePaidType === 'partially') {
                        let entered = parseFloat($("#enterAmount").val()) || 0;
                        if (entered <= 0) {
                            $("#enterAmountError").text("Enter amount is required.").css("color", "red");
                            isValid = false;
                        } else if (entered >= total) {
                            $("#enterAmountError").text("Partial amount must be less than total amount.").css("color", "red");
                            isValid = false;
                        } else {
                            $("#enterAmountError").text("");
                        }
                    }

                    // Debit — bank required (REMOVED as it should use shop settings bank automatically)
                    // if (selectedPayment === 'debit card' && activePaidType && !$("#debitBankSelect").val()) {
                    //     $("#debitBankError").text("Please select a bank.").css("color", "red");
                    //     isValid = false;
                    // }

                    // Scan — bank required (REMOVED as it should use shop settings bank automatically)
                    // if (selectedPayment === 'scan' && activePaidType && !$("#scanBankSelect").val()) {
                    //     $("#scanBankError").text("Please select a bank.").css("color", "red");
                    //     isValid = false;
                    // }

                    // Cash+Online validations
                    if (selectedPayment === 'cash+online') {
                        if (!activeCOType) {
                            // Use same paidTypeError span but inside cashOnlineBox
                            $("#cashOnlinePaidTypeError").text("Please select a Paid Type.").css("color", "red");
                            isValid = false;
                        } else {
                            if (activeCOType === 'partially') {
                                let entered = parseFloat($("#cashOnlineEnterAmount").val()) || 0;
                                if (entered <= 0) {
                                    $("#cashOnlineEnterAmountError").text("Enter amount is required.").css("color", "red");
                                    isValid = false;
                                }
                            }
                            let cashAmt = parseFloat($("#cashAmount").val()) || 0;
                            if (cashAmt <= 0) {
                                $("#cashAmountError").text("Cash amount is required.").css("color", "red");
                                isValid = false;
                            } else if (cashAmt > total) {
                                $("#cashAmountError").text("Cash amount cannot exceed total.").css("color", "red");
                                isValid = false;
                            }
                            // if (!$("#cashOnlineBankSelect").val()) {
                            //     $("#cashOnlineBankError").text("Please select a bank.").css("color", "red");
                            //     isValid = false;
                            // }
                        }
                    }
                    // ─────────────────────────────────────────────────────


                    // if (!isValid) return;
                    if (!isValid) {
                        $btn.html(originalText).prop('disabled', false); // <-- important line
                        return;
                    }


                    // Prepare order items data
                    let orderItems = [];
                    selectedItems.forEach(function(item) {


                        const price = parseFloat(item.price.replace ? item.price.replace(/[^0-9.-]+/g, "") : item.price);
                        // Only send numeric quantity_type_id (never unit labels like ML/PCS)
                        let qtId = item.quantity_type123 || item.quantity_type_id || null;
                        if (qtId !== null && qtId !== undefined && qtId !== '') {
                            qtId = String(qtId).trim();
                            qtId = /^\d+$/.test(qtId) ? parseInt(qtId, 10) : null;
                        } else {
                            qtId = null;
                        }
                        var discPct = canItemDiscount ? (parseFloat(item.item_discount_percent) || 0) : 0;
                        var lineGross = price * item.quantity;
                        var lineTotal = lineGross - (lineGross * discPct / 100);
                        orderItems.push({
                            product_id: item.productId,
                            variant_id: item.variant_id || null,
                            addon_ids: item.addon_ids || [],
                            addons: item.addon_ids || [],
                            quantity: item.quantity,
                            quantity_type_id: qtId,
                            quantity_type123: qtId,
                            categoryId: item.categoryId,
                            price: price,
                            item_discount_percent: discPct,
                            total: lineTotal
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
                        $(".taxList").each(function() {
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
                        taxes = []; // ✅ explicitly empty when "without GST"
                    }


                    let cash = parseFloat($("#cashAmount").val()) || 0;
                    let online = parseFloat($("#onlineAmount").val()) || 0;

                    // Paid type sub-fields
                    let paidType, enterAmount, pendingAmt;
                    if (selectedPayment === 'cash+online') {
                        paidType    = $("#cashOnlinePaidTypeSelect").val() || 'fully';
                        enterAmount = parseFloat($("#cashOnlineEnterAmount").val()) || total;
                        pendingAmt  = parseFloat($("#cashOnlinePendingAmount").val()) || 0;
                    } else {
                        paidType    = $("#paidTypeSelect").val() || 'fully';
                        enterAmount = parseFloat($("#enterAmount").val()) || total;
                        pendingAmt  = parseFloat($("#pendingAmount").val()) || 0;
                    }

                    // For "Fully" paid — enter_amount = total, pending = 0
                    if (paidType === 'fully' || selectedPayment === 'pending') {
                        enterAmount = total;
                        pendingAmt = 0;
                    }

                    // Debit-specific / Cash+Online / Scan fields
                    let bankId = selectedPayment === 'debit card'
                        ? ($("#debitBankSelect").val() || null)
                        : (selectedPayment === 'cash+online'
                            ? ($("#cashOnlineBankSelect").val() || null)
                            : (selectedPayment === 'scan' ? ($("#scanBankSelect").val() || null) : null));
                    let debitRemark = selectedPayment === 'debit card'
                        ? ($("#debitRemark").val() || null)
                        : (selectedPayment === 'cash+online'
                            ? ($("#cashOnlineRemark").val() || null)
                            : (selectedPayment === 'scan' ? ($("#scanRemark").val() || null) : null));

                    // Prepare order data
                    let orderData = {
                        selectedSubAdminId: selectedSubAdminId,
                        customer_id: name,
                        customer_phone: phone,
                        table_id: table_id,
                        payment_method: selectedPayment,
                        table_type: selectedTableType,
                        subtotal: subtotal,
                        discount: discount,
                        discount_amount: subtotal * (discount / 100),
                        tax: taxes,
                        gst_option: gstOption,
                        total: total,
                        cash_amount: cash,
                        online_amount: online,
                        paid_type: paidType,
                        enter_amount: enterAmount,
                        pending_amount: pendingAmt,
                        bank_id: bankId,
                        debit_remark: debitRemark,
                        order_type: $('#order_type_select').val() || 'Self Pickup',
                        assigned_staff_id: $('#assigned_staff_id').val() || null,
                        items: orderItems
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
                        success: function(response) {
                            // Re-enable and reset the button
                            $btn.html(originalText).prop('disabled', false);
                            if (response.status) {
                                orderId = response.order_id;
                                // alert("Order placed successfully!");
                                // location.reload();
                                // Show Bill Generated modal with embedded preview
                                window._billOrderId = orderId;
                                var billUrl = "/sales/invoice/pdf/" + orderId;
                                var $iframe = $("#billPreviewIframe");
                                var $loader = $("#billIframeLoader");

                                // Show loader while iframe loads
                                $loader.show();
                                $iframe.attr("src", "");
                                setTimeout(function() {
                                    $iframe.attr("src", billUrl);
                                }, 50);

                                $iframe.off("load").on("load", function() {
                                    $loader.hide();
                                    if (window.InvoiceQzPrint && InvoiceQzPrint.preparePhonePrint && this.src) {
                                        InvoiceQzPrint.preparePhonePrint(this.src);
                                    }
                                });

                                var billModal = new bootstrap.Modal(document.getElementById("billGeneratedModal"), {
                                    backdrop: "static",
                                    keyboard: false
                                });
                                billModal.show();
                                // Restore Print KOT preference
                                try {
                                    var kotPref = localStorage.getItem('posPrintKotWithBill');
                                    if (kotPref === '0') {
                                        $('#posPrintKotWithBill').prop('checked', false);
                                    } else {
                                        $('#posPrintKotWithBill').prop('checked', true);
                                    }
                                } catch (e) {}
                                if (typeof updateBillPrintUi === 'function') {
                                    updateBillPrintUi();
                                }
                                // Close current bill tab (or clear cart if tabs unavailable)
                                $("#debitBankSelect").val("");
                                $("#debitRemark").val("");
                                $("#scanBankSelect").val("");
                                $("#scanRemark").val("");
                                $("#cashAmount").val("");
                                $("#onlineAmount").val("0");
                                $("#cashOnlineBankSelect").val("");
                                $("#cashOnlineRemark").val("");
                                if (typeof window.applyDefaultPosPayment === 'function') {
                                    window.applyDefaultPosPayment();
                                }
                                if (typeof window.posBillTabsCloseCurrentAfterSave === 'function') {
                                    window.posBillTabsCloseCurrentAfterSave();
                                } else {
                                    selectedItems.clear();
                                    $(".check-product i").removeClass("selected");
                                    $("input[name='customer_name'], input[name='customer_phone']").val("");
                                    $("#discount").val("");
                                    updateTotalItems();
                                }
                            } else {
                                alert("Error: " + response.message);
                            }
                        },
                        // error: function(xhr, status, error) {
                        //     // Re-enable and reset the button
                        //     $btn.html(originalText).prop('disabled', false);

                        //     alert("Error placing order. Please try again.");
                        // }
                        error: function(xhr, status, error) {
                            $btn.html(originalText).prop('disabled', false);

                            // Try to extract error message from response
                            let response = xhr.responseJSON;

                            if (response && response.message) {
                                Swal.fire({
                                    title: "Error",
                                    text: response.message,
                                    icon: "error",
                                    confirmButtonColor: "#ff9f43"
                                });
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: "Error placing order. Please try again.",
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

                // Handle category click
                $(".tabs li").click(function() {
                    var categoryId = $(this).attr('id');


                    // Remove active class from all tabs and tab contents
                    $(".tabs li").removeClass('active');
                    $(".tab_content").removeClass('active');

                    // Add active class to clicked tab
                    $(this).addClass('active');

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

                // Function to load products by category
                function loadProductsByCategory(categoryId) {

                    $.ajax({
                        url: "/api/getProductsByCategory/" + categoryId,
                        type: "GET",
                        dataType: "json",
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        success: function(response) {
                            const currencySymbol = response.currency_symbol || 'Ã¢â€šÂ¹';
                            const currencyPosition = response.currency_position || 'left';


                            if (response.status) {
                                renderProducts(response.data, categoryId, currencySymbol, currencyPosition);
                            } else {
                                renderProducts([], categoryId, currencySymbol, currencyPosition);
                            }
                        },
                        error: function(xhr, status, error) {

                            renderProducts([], categoryId);
                        }
                    });
                }

                // Function to render products
                function renderProducts(products, categoryId, currencySymbol, currencyPosition) {

                    var productsHtml = '';

                    if (products.length === 0) {
                        productsHtml = '<div class="col-12"><p>' + (categoryId === 'all' ? 'No items found' : 'No items found in this category') + '</p></div>';
                    } else {
                        products.forEach(function(product) {
                            let productImage = escapeHtmlAttr(resolveProductImage(
                                product.image_url || product.image || product.images || ''
                            ));

                            // Format price
                            let price = parseFloat(product.price || 0).toFixed(2);
                            let displayPrice = currencyPosition === 'right' ? `${price}${currencySymbol}` :
                                `${currencySymbol}${price}`;

                            // Prepared food never OOS; negative selling keeps card clickable
                            let outOfStockBadge = '';
                            let disabledClass = '';
                            let addButtonDisabled = '';
                            var effectiveStock = posEffectiveStock(product.quantity, product);

                            if (posShowOutOfStock(product) && !posAllowsOversell(product)) {
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
                                disabledClass = 'disabled-product';
                                addButtonDisabled = 'disabled';
                            }

                            var variantsList = product.variants || [];
                            var addonsList = product.addons || [];
                            storePosProductOptions(product.id, variantsList, addonsList);
                            var variantsJson = escapeHtmlAttr(JSON.stringify(variantsList));
                            var addonsJson = escapeHtmlAttr(JSON.stringify(addonsList));
                            var basePriceNum = parseFloat(product.price || 0) || 0;
                            var hasOptionsBadge = (variantsList.length || addonsList.length)
                                ? `<div style="position:absolute;bottom:8px;left:8px;background:rgba(27,40,80,0.9);color:#fff;font-weight:600;padding:2px 7px;border-radius:4px;font-size:10px;z-index:10;">${variantsList.length ? variantsList.length + ' sizes' : 'Options'}</div>`
                                : '';

                            var safeNoImage = escapeHtmlAttr(POS_NO_IMAGE);
                            productsHtml += `
                                        <div class="col-lg-3 col-sm-6 d-flex position-relative">
                                            <div class="productset flex-fill text-center ${disabledClass}" data-id="${product.id}" data-stock="${effectiveStock}" data-raw-stock="${product.quantity}" data-product-kind="${escapeHtmlAttr(product.product_kind || 'general')}" data-track-inventory="${product.track_inventory === false || product.track_inventory === 0 ? '0' : '1'}" data-allow-oversell="1" data-tax-id="${product.tax_id || ''}" data-tax-rate="${product.tax_rate != null ? product.tax_rate : ''}" data-tax-name="${escapeHtmlAttr(product.tax_name || '')}" data-tax-inclusive="${product.tax_inclusive ? '1' : '0'}" data-quantity_type_id="${product.quantity_type_id || ''}" data-base-price="${basePriceNum}" data-variants="${variantsJson}" data-addons="${addonsJson}" style="position: relative;">
                                                ${outOfStockBadge}
                                                ${hasOptionsBadge}
                                                <div class="productsetimg">
                                                    <img src="${productImage}" alt="${escapeHtmlAttr(product.name)}" class="productsetimgin" data-fallback="${safeNoImage}" onerror="this.onerror=null;this.src=this.getAttribute('data-fallback')||window.POS_NO_IMAGE;">
                                                    <h6>Qty: ${formatStockQty(product.quantity)}</h6>
                                                </div>
                                                <div class="productsetcontent">
                                                    <h4>${escapeHtmlText(product.name)}</h4>
                                                    <h1 class="d-none">${escapeHtmlText(product.categoryId || product.category_id || '')}</h1>
                                                    <h2 class="d-none">${escapeHtmlText(product.quantity_type || '')}</h2>
                                                    <h3 class="d-none">${escapeHtmlText(product.quantity_type_id || '')}</h3>
                                                    <h5>${escapeHtmlText(product.category_name || 'Uncategorized')}</h5>
                                                    <h6>${escapeHtmlText(displayPrice)}</h6>
                                                </div>
                                                <div class="productsetbtn d-none">
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



                // Load products for the first category by default
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


                // Initialize with empty state
                updateTotalItems();
            });
            $(".select2").select2({
                tags: true,
            });
            $(".select2_customer").select2({
                tags: true,
                placeholder: "Select or Type Customer",
            }).on('change', function() {
                var phone = $(this).find(':selected').data('phone');
                if (phone) {
                    var cleaned = String(phone).replace(/\D/g, '').slice(0, 10);
                    $('#customer_phone').val(cleaned).trigger('input');
                } else {
                    $('#customer_phone').val('').trigger('input');
                }
            });

            $(document).on('input', '#customer_phone', function () {
                var val = $(this).val().replace(/\D/g, '').slice(0, 10);
                if ($(this).val() !== val) {
                    $(this).val(val);
                }
                if (val.length === 10 || val.length === 0) {
                    $('.error_customerphone').text('');
                }
            });
            $(".select2-fixed").select2({
                minimumResultsForSearch: -1 // fixed options chhe, tethi search box nathi joito — remove this line if search joiye
            }).on('change', function() {
                if ($(this).attr('id') === 'order_type_select') {
                    $('#orderTypeBadge').text($(this).val() || 'Self Pickup');
                }
            });

            $(document).on('change', '#order_type_select', function() {
                $('#orderTypeBadge').text($(this).val() || 'Self Pickup');
            });
            if ($('#order_type_select').length && $('#order_type_select').val()) {
                $('#orderTypeBadge').text($('#order_type_select').val());
            }
            
            // Trigger change to populate phone if default customer is selected
            setTimeout(function() {
                if ($('#customer_name_input').val()) {
                    $('#customer_name_input').trigger('change');
                }
            }, 100);
            $(document).ready(function() {
                var posSearchTimer = null;
                var posSearchReqId = 0;

                function renderPosSearchResults($resultBox, products) {
                    $resultBox.empty();
                    if (!products || !products.length) {
                        $resultBox.html(`
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                                <span style="font-size: 13px; color: #64748b;">No results found</span>
                                <button type="button" class="btn-close-search" style="background: none; border: 1.5px solid #cbd5e1; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 13px; cursor: pointer;" title="Close">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `).show();
                        return;
                    }

                    $resultBox.append(`
                        <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3 bg-white" style="border-bottom: 2px solid #f1f5f9; position: sticky; top: 0; z-index: 10;">
                            <span class="fw-bold" style="font-size: 13.5px; color: #1e293b;">Products (${products.length})</span>
                            <button type="button" class="btn-close-search" style="background: none; border: 1.5px solid #cbd5e1; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 13px; cursor: pointer;" title="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `);

                    products.forEach(function(product) {
                        storePosProductOptions(product.id, product.variants || [], product.addons || []);
                        var rawPrice = product.raw_price != null ? product.raw_price : (parseFloat(String(product.price).replace(/[^0-9.-]+/g, '')) || 0);
                        var displayPrice = product.price;
                        if (typeof displayPrice === 'number') {
                            displayPrice = (posCurrencyPosition === 'right')
                                ? (Number(displayPrice).toFixed(2) + posCurrencySymbol)
                                : (posCurrencySymbol + Number(displayPrice).toFixed(2));
                        }
                        var optHint = (product.variants && product.variants.length)
                            ? (' · ' + product.variants.length + ' sizes')
                            : '';
                        var searchImg = escapeHtmlAttr(resolveProductImage(product.image || product.image_url || product.images));
                        var searchNoImg = escapeHtmlAttr(POS_NO_IMAGE);
                        var stockVal = (typeof posEffectiveStock === 'function')
                            ? posEffectiveStock(product.stock != null ? product.stock : product.quantity, product)
                            : 999999;
                        $resultBox.append(`
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3" style="border-bottom: 1px solid #f1f5f9;">
                                <div class="d-flex align-items-center gap-2" style="flex: 1; min-width: 0;">
                                    <img src="${searchImg}" alt="" class="rounded" style="width: 34px; height: 34px; object-fit: cover; flex-shrink: 0; border: 1px solid #f1f5f9;" data-fallback="${searchNoImg}" onerror="this.onerror=null;this.src=this.getAttribute('data-fallback')||window.POS_NO_IMAGE;">
                                    <div style="min-width: 0;">
                                        <strong style="font-size: 13px; color: #1e293b; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtmlText(product.name ?? 'N/A')}</strong>
                                        <small style="font-size: 11.5px; color: #64748b; font-weight: 600;">Price: ${escapeHtmlText(displayPrice ?? 'N/A')}${optHint}</small>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-primary add-product ms-2"
                                    style="border-radius: 6px; padding: 4px 14px; font-weight: 600; font-size: 12px; flex-shrink: 0;"
                                    data-id="${product.id}"
                                    data-name="${escapeHtmlAttr(product.name)}"
                                    data-price="${escapeHtmlAttr(displayPrice)}"
                                    data-raw-price="${rawPrice}"
                                    data-quantity_type="${product.quantity_type || ''}"
                                    data-quantity_type_id="${product.quantity_type_id || ''}"
                                    data-quantity_type123="${product.quantity_type_id || ''}"
                                    data-image="${searchImg}"
                                    data-stock="${stockVal}"
                                    data-product-kind="${escapeHtmlAttr(product.product_kind || 'general')}"
                                    data-allow-oversell="1"
                                    data-tax-id="${product.tax_id || ''}"
                                    data-tax-rate="${product.tax_rate != null ? product.tax_rate : ''}"
                                    data-tax-name="${escapeHtmlAttr(product.tax_name || '')}"
                                    data-tax-inclusive="${product.tax_inclusive ? '1' : '0'}"
                                    data-category="${product.category_id || product.categoryId || 0}">+ Add</button>
                            </div>
                        `);
                    });
                    $resultBox.show();
                }

                function executeProductSearch($input, $resultBox) {
                    const query = $input.val().trim();
                    if (query.length < 1) {
                        $resultBox.hide().empty();
                        return;
                    }

                    var reqId = ++posSearchReqId;
                    $resultBox.html('<div class="list-group-item py-2 px-3" style="font-size:13px;color:#64748b;">Searching…</div>').show();

                    $.ajax({
                        url: @json(route('users.ajaxSearch')),
                        method: 'GET',
                        data: { query: query },
                        dataType: 'json',
                        success: function(data) {
                            if (reqId !== posSearchReqId) return;
                            var products = (data && data.products) ? data.products : [];
                            if (products.length) {
                                renderPosSearchResults($resultBox, products);
                                return;
                            }
                            // Fallback: filter currently loaded POS catalog
                            searchLoadedPosCatalog(query, $resultBox, reqId);
                        },
                        error: function() {
                            if (reqId !== posSearchReqId) return;
                            searchLoadedPosCatalog(query, $resultBox, reqId);
                        }
                    });
                }

                function searchLoadedPosCatalog(query, $resultBox, reqId) {
                    var authToken = localStorage.getItem('authToken');
                    $.ajax({
                        url: '/api/getProductsByCategory/all',
                        type: 'GET',
                        dataType: 'json',
                        headers: authToken ? { Authorization: 'Bearer ' + authToken } : {},
                        success: function(response) {
                            if (reqId !== posSearchReqId) return;
                            var q = String(query).toLowerCase();
                            var list = (response && response.status && Array.isArray(response.data)) ? response.data : [];
                            var matched = list.filter(function(p) {
                                return String(p.name || '').toLowerCase().indexOf(q) !== -1;
                            }).slice(0, 20);
                            renderPosSearchResults($resultBox, matched);
                        },
                        error: function() {
                            if (reqId !== posSearchReqId) return;
                            $resultBox.html(`
                                <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                                    <span style="font-size: 13px; color: #ef4444;">Search failed. Please try again.</span>
                                    <button type="button" class="btn-close-search" style="background: none; border: 1.5px solid #cbd5e1; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 13px; cursor: pointer;" title="Close">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `).show();
                        }
                    });
                }

                // Handle typing in both search bars (top center or side)
                $(document).on('input', '.pos-product-search-input', function() {
                    const $input = $(this);
                    const $resultBox = $input.siblings('.pos-search-results-box');
                    if (!$resultBox.length) return;
                    clearTimeout(posSearchTimer);
                    posSearchTimer = setTimeout(function() {
                        executeProductSearch($input, $resultBox);
                    }, 250);
                });

                // Close cross button click (Like Image 3)
                $(document).on('click', '.btn-close-search', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('.pos-search-results-box').hide().empty();
                    $('.pos-product-search-input').val('');
                });

                $(document).on("click", ".add-product", function() {
                    const $btn = $(this);
                    const productId = $btn.data("id");
                    const productName = $btn.data("name");
                    const productPrice = $btn.data("price");
                    const productImage = resolveProductImage($btn.attr("data-image") || $btn.data("image"));
                    const stockFn = window.posEffectiveStock || posEffectiveStock;
                    const productStock = (typeof stockFn === 'function')
                        ? stockFn($btn.data("stock"), {
                            product_kind: $btn.data("product-kind"),
                            allow_oversell: true
                        })
                        : (parseInt($btn.data("stock"), 10) || 999999);
                    const categoryId = $btn.data("category");
                    const basePrice = parseFloat($btn.attr('data-raw-price')) || parseFloat(String(productPrice).replace(/[^0-9.-]+/g, '')) || 0;

                    const quantity_type = ($btn.data("quantity_type") || "").toString().trim();
                    const quantity_type1 = ($btn.data("quantity_type_id") || "").toString().trim();
                    const quantity_type123 = ($btn.data("quantity_type123") || "").toString().trim();

                    const opts = getPosProductOptions(productId);
                    const variants = opts.variants || [];
                    const addons = opts.addons || [];

                    $('.pos-search-results-box').hide().empty();
                    $('.pos-product-search-input').val('');

                    var searchTaxId = $btn.data("tax-id") || null;
                    var searchTaxRate = $btn.attr("data-tax-rate");
                    searchTaxRate = (searchTaxRate === '' || searchTaxRate == null) ? null : parseFloat(searchTaxRate);
                    var searchTaxName = ($btn.attr("data-tax-name") || '').toString().trim() || null;
                    var searchTaxInclusive = $btn.data("tax-inclusive") === 1 || $btn.data("tax-inclusive") === '1';

                    const meta = {
                        productId: productId,
                        categoryId: categoryId,
                        name: productName,
                        image: productImage,
                        code: "PT001",
                        quantity_type: (quantity_type && !['undefined', 'null'].includes(quantity_type.toLowerCase())) ? quantity_type : '',
                        quantity_type_id: (quantity_type1 && !['undefined', 'null'].includes(quantity_type1.toLowerCase())) ? quantity_type1 : '',
                        quantity_type123: (quantity_type123 && !['undefined', 'null'].includes(quantity_type123.toLowerCase())) ? quantity_type123 : '',
                        stock: productStock,
                        basePrice: basePrice,
                        tax_id: searchTaxId,
                        tax_rate: searchTaxRate,
                        tax_name: searchTaxName,
                        tax_inclusive: searchTaxInclusive
                    };

                    if ((variants.length || addons.length) && typeof window.openPosOptionModal === 'function') {
                        window.openPosOptionModal(meta, variants, addons);
                        return;
                    }

                    if (selectedItems.has(productId)) {
                        var existingItem = selectedItems.get(productId);
                        existingItem.quantity++;
                        selectedItems.set(productId, existingItem);
                        updateTotalItems();
                    } else {
                        selectedItems.set(productId, {
                            id: productId,
                            productId: productId,
                            categoryId: categoryId,
                            name: productName,
                            price: productPrice,
                            image: productImage,
                            quantity_type: meta.quantity_type,
                            quantity_type_id: meta.quantity_type_id,
                            quantity_type123: meta.quantity_type123,
                            code: "PT001",
                            quantity: 1,
                            stock: productStock,
                            item_discount_percent: 0,
                            tax_id: searchTaxId,
                            tax_rate: searchTaxRate,
                            tax_name: searchTaxName,
                            tax_inclusive: searchTaxInclusive
                        });
                        updateTotalItems();
                    }
                });

                // Hide dropdown on outside click
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.search_custom_product').length) {
                        $('.pos-search-results-box').hide();
                    }
                });
            });

            {{-- ── Bank Management JS ────────────────────────────────── --}}
            $(document).ready(function () {

                var authToken = localStorage.getItem("authToken");
                var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                // Open Add Bank modal — track which selector to update after save
                var _addBankTargetSelector = "#debitBankSelect";

                $(document).on('click', '#addBankBtn', function () {
                    _addBankTargetSelector = "#debitBankSelect";
                    openAddBankModal();
                });

                $(document).on('click', '.addBankBtnCashOnline', function () {
                    _addBankTargetSelector = "#cashOnlineBankSelect";
                    openAddBankModal();
                });

                $(document).on('click', '.addBankBtnScan', function () {
                    _addBankTargetSelector = "#scanBankSelect";
                    openAddBankModal();
                });

                function openAddBankModal() {
                    $("#newBankName, #newBankAccount, #newBankIfsc, #newBankBranch, #newBankBalance").val("");
                    $("#newBankStatus").val("1");
                    $("#addBankAlert").hide();
                    var modal = new bootstrap.Modal(document.getElementById('addBankModal'));
                    modal.show();
                }

                // Load banks when debit is selected — handled by main paymentmethod handler above

                // Save Bank
                $("#saveBankBtn").on("click", function () {
                    var bankName    = $.trim($("#newBankName").val());
                    var account     = $.trim($("#newBankAccount").val());
                    var ifsc        = $.trim($("#newBankIfsc").val());
                    var branchName  = $.trim($("#newBankBranch").val());
                    var balance     = $("#newBankBalance").val() || 0;
                    var status      = $("#newBankStatus").val();

                    if (!bankName || !account || !ifsc || !branchName) {
                        $("#addBankAlert").text("Please fill all required fields.").show();
                        return;
                    }

                    var $btn = $(this);
                    $btn.prop("disabled", true).text("Saving...");

                    $.ajax({
                        url: '/api/banks',
                        method: 'POST',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify({
                            bank_name: bankName,
                            account_number: account,
                            ifsc_code: ifsc,
                            branch_name: branchName,
                            opening_balance: balance,
                            status: status,
                            selectedSubAdminId: selectedSubAdminId
                        }),
                        success: function (res) {
                            if (res.status) {
                                // Close modal
                                bootstrap.Modal.getInstance(document.getElementById('addBankModal')).hide();
                                // Reload banks into the correct select and select the new one
                                loadBanksInto(_addBankTargetSelector);
                                setTimeout(function () {
                                    $(_addBankTargetSelector).val(res.data.id);
                                }, 400);
                                Swal.fire({ toast: true, position: 'top', icon: 'success', title: 'Bank added!', showConfirmButton: false, timer: 2000 });
                            } else {
                                $("#addBankAlert").text(res.message || "Failed to add bank.").show();
                            }
                        },
                        error: function (xhr) {
                            var msg = xhr.responseJSON?.message || "Failed to add bank.";
                            // Show validation errors if present
                            if (xhr.responseJSON?.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join(" ");
                            }
                            $("#addBankAlert").text(msg).show();
                        },
                        complete: function () {
                            $btn.prop("disabled", false).text("Save Bank");
                        }
                    });
                });
            });
            {{-- ── Add Table Management JS ─────────────────────────────── --}}
            $(document).ready(function () {
                var authToken = localStorage.getItem("authToken");
                var addTableModal = new bootstrap.Modal(document.getElementById('addTableModal'));

                // Listen for new tag selection in Select2
                $('#table_no_select').on('select2:select', function (e) {
                    var data = e.params.data;
                    // If it's a newly created tag, the id will equal the text
                    if (data.id === data.text) {
                        // Open the modal
                        $("#newTableName").val(data.text);
                        $("#newTableType").val("").trigger("change");
                        $("#addTableAlert").hide();
                        
                        // Temporarily clear selection so the unsaved tag isn't active
                        $(this).val(null).trigger('change');
                        
                        addTableModal.show();
                    }
                });

                // Initialize select2 inside modal for Table Type
                $("#newTableType").select2({
                    dropdownParent: $("#addTableModal"),
                    minimumResultsForSearch: -1 // Disable search if you only have a few options
                });
                
                // Handle cancel/close buttons
                $('#closeTableModalBtn, #cancelTableModalBtn').on('click', function() {
                    addTableModal.hide();
                });

                // Save Table
                $("#saveTableBtn").on("click", function () {
                    var tableName = $.trim($("#newTableName").val());
                    var tableType = $("#newTableType").val();

                    if (!tableName || !tableType) {
                        $("#addTableAlert").text("Please fill all required fields (Table Name and Table Type).").show();
                        return;
                    }

                    var $btn = $(this);
                    $btn.prop("disabled", true).text("Saving...");

                    $.ajax({
                        url: '/api/table/create',
                        method: 'POST',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify({
                            table_name: tableName,
                            table_type: tableType
                        }),
                        success: function (res) {
                            if (res.status && res.data) {
                                addTableModal.hide();
                                
                                // Remove any temporary dummy option with the same text
                                $('#table_no_select option').filter(function() {
                                    return $(this).val() === tableName;
                                }).remove();
                                
                                // Add new proper option to the dropdown and select it
                                var newOption = new Option(res.data.table_name, res.data.id, true, true);
                                $('#table_no_select').append(newOption).trigger('change');
                                
                                Swal.fire({ toast: true, position: 'top', icon: 'success', title: 'Table added successfully!', showConfirmButton: false, timer: 2000 });
                            } else {
                                $("#addTableAlert").text(res.message || "Failed to add table.").show();
                            }
                        },
                        error: function (xhr) {
                            var msg = xhr.responseJSON?.message || "Failed to add table.";
                            if (xhr.responseJSON?.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join(" ");
                            }
                            $("#addTableAlert").text(msg).show();
                        },
                        complete: function () {
                            $btn.prop("disabled", false).text("Save");
                        }
                    });
                });
            });

            // Payment panel stays inside the order card (internal scroll). Clear any old fixed styles.
            function updatePosPaymentPanelPosition() {
                var $panel = $('.payment_panel');
                if (!$panel.length) return;
                $panel.css({
                    position: 'relative',
                    bottom: 'auto',
                    left: 'auto',
                    right: 'auto',
                    width: '100%'
                });
            }

            $(window).on('resize', updatePosPaymentPanelPosition);
            $(document).ready(function() {
                updatePosPaymentPanelPosition();
                setTimeout(updatePosPaymentPanelPosition, 100);
                setTimeout(updatePosPaymentPanelPosition, 300);
                setTimeout(updatePosPaymentPanelPosition, 800);
            });

            // ── Bill Generated Modal: simple Print UI + hidden printer settings ─
            function shortPrinterName(name) {
                name = String(name || '').trim();
                if (!name) return '';
                return name.length > 28 ? (name.slice(0, 26) + '…') : name;
            }

            function updateBillPrintUi() {
                var alsoKot = $('#posPrintKotWithBill').is(':checked');
                $('#printBillBtnLabel').text(alsoKot ? 'Print Bill + KOT' : 'Print Bill');
                $('#posKotPrinterRow').css('opacity', alsoKot ? 1 : 0.45);
                $('#posKotPrinterSelect, #posKotPrinterRow .desktop-bt-print-btn').prop('disabled', !alsoKot);

                var billName = ($('#posPrinterSelect option:selected').text() || '').trim();
                var billVal = ($('#posPrinterSelect').val() || '').trim();
                if (!billVal) billName = 'Not set';
                else billName = shortPrinterName(billName);

                var summary = 'Bill → ' + billName;
                if (alsoKot) {
                    var kotVal = ($('#posKotPrinterSelect').val() || '').trim();
                    var kotName = kotVal
                        ? shortPrinterName(($('#posKotPrinterSelect option:selected').text() || '').trim())
                        : 'Same as bill';
                    summary += '  ·  KOT → ' + kotName;
                }
                $('#posPrintSummaryLabel').text(summary);
            }

            function syncKotPrinterRowVisibility() {
                updateBillPrintUi();
            }

            function syncKotPrinterOptionsFromBill() {
                var $bill = $('#posPrinterSelect');
                var $kot = $('#posKotPrinterSelect');
                if (!$bill.length || !$kot.length) return;
                var saved = '';
                try { saved = localStorage.getItem('posSelectedKotPrinter') || ''; } catch (e) {}
                var current = $kot.val() || '';
                $kot.empty();
                $kot.append($('<option></option>').val('').text('Same as bill'));
                $bill.find('option').each(function() {
                    var v = $(this).val();
                    if (!v) return;
                    $kot.append($('<option></option>').val(v).text($(this).text()));
                });
                function hasVal(v) {
                    var found = false;
                    $kot.find('option').each(function() {
                        if ($(this).val() === v) found = true;
                    });
                    return found;
                }
                var pick = '';
                if (saved && hasVal(saved)) pick = saved;
                else if (current && hasVal(current)) pick = current;
                $kot.val(pick);
                updateBillPrintUi();
            }

            function refreshPosPrinterList() {
                if (!window.InvoiceQzPrint) {
                    $('#posQzStatus').text('QZ print loading…').css('color', '#6c757d');
                    return Promise.resolve();
                }
                return Promise.resolve(InvoiceQzPrint.refreshPrinters({
                    select: '#posPrinterSelect',
                    status: '#posQzStatus',
                    installLink: '#posQzInstallLink',
                    storageKey: 'posSelectedPrinter',
                    list: '#posBtPrinterList',
                    wifiList: '#posWifiPrinterList'
                })).then(function() {
                    syncKotPrinterOptionsFromBill();
                }).catch(function() {
                    syncKotPrinterOptionsFromBill();
                });
            }

            function resolveKotPrinterName() {
                var kot = ($('#posKotPrinterSelect').val() || '').trim();
                if (kot) return kot;
                return ($('#posPrinterSelect').val() || '').trim();
            }

            function openKotPrintWindow(orderId) {
                if (!orderId) return;
                var kotUrl = "/kitchen/kot/" + orderId + "/print";
                var w = window.open(kotUrl, "kot_print_" + orderId, "width=420,height=640,scrollbars=yes");
                if (!w) {
                    var $frame = $('#posKotSilentFrame');
                    if (!$frame.length) {
                        $frame = $('<iframe id="posKotSilentFrame" title="KOT print" style="position:absolute;width:0;height:0;border:0;left:-9999px;"></iframe>');
                        $('body').append($frame);
                    }
                    $frame.attr('src', kotUrl);
                }
            }

            function printKotToKitchen(orderId) {
                if (!orderId) return Promise.resolve();
                var kotPdfUrl = "/kitchen/kot/" + orderId + "/pdf";
                var kotPrinter = resolveKotPrinterName();
                var doAutoCut = $('#posThermalAutoCut').is(':checked');

                if (!window.InvoiceQzPrint || !kotPrinter) {
                    openKotPrintWindow(orderId);
                    return Promise.resolve();
                }

                try { localStorage.setItem('posSelectedKotPrinter', kotPrinter); } catch (e) {}
                $('#posKotQzStatus').text('Printing KOT…').css('color', '#6c757d');

                return Promise.resolve(InvoiceQzPrint.printInvoice({
                    printerName: kotPrinter,
                    pdfUrl: kotPdfUrl,
                    autoCut: doAutoCut,
                    status: '#posKotQzStatus',
                    button: '#printKotOnlyBtn',
                    select: '#posKotPrinterSelect',
                    storageKey: 'posSelectedKotPrinter'
                })).then(function() {
                    $('#posKotQzStatus').text('KOT printed').css('color', '#28c76f');
                }).catch(function(err) {
                    console.warn('KOT QZ print failed', err);
                    $('#posKotQzStatus').text('KOT fallback…').css('color', '#ea5455');
                    openKotPrintWindow(orderId);
                });
            }

            function printBillFromModal() {
                var orderId = window._billOrderId;
                if (!orderId) return;

                var frame = document.getElementById('billPreviewIframe');
                var pdfUrl = frame && frame.src ? frame.src : ("/sales/invoice/pdf/" + orderId);
                var printerName = ($('#posPrinterSelect').val() || '').trim();
                var doAutoCut = $('#posThermalAutoCut').is(':checked');
                var alsoKot = $('#posPrintKotWithBill').is(':checked');

                function afterBillPrint() {
                    if (!alsoKot) return Promise.resolve();
                    return printKotToKitchen(orderId);
                }

                if (!window.InvoiceQzPrint) {
                    window.open(pdfUrl, "_blank");
                    afterBillPrint();
                    return;
                }

                if (!printerName) {
                    $('#posQzStatus').text('Select a bill printer').css('color', '#ea5455');
                    window.open(pdfUrl, "_blank");
                    afterBillPrint();
                    return;
                }

                $('#printBillBtn').prop('disabled', true);
                Promise.resolve(InvoiceQzPrint.printInvoice({
                    printerName: printerName,
                    pdfUrl: pdfUrl,
                    autoCut: doAutoCut,
                    frame: frame,
                    status: '#posQzStatus',
                    button: '#printBillBtn',
                    select: '#posPrinterSelect',
                    list: '#posBtPrinterList',
                    storageKey: 'posSelectedPrinter'
                })).then(function() {
                    return afterBillPrint();
                }).catch(function() {
                    return afterBillPrint();
                }).then(function() {
                    $('#printBillBtn').prop('disabled', false);
                });
            }

            $(document).on('click', '#posRefreshPrintersBtn', function() {
                refreshPosPrinterList();
            });

            $(document).on('click', '#posPrinterSettingsToggle', function() {
                var $panel = $('#posPrinterSettingsPanel');
                var open = !$panel.is(':visible');
                $panel.slideToggle(160);
                $(this).html(open
                    ? '<i class="fas fa-times"></i> Hide settings'
                    : '<i class="fas fa-cog"></i> Printer settings');
                if (open) {
                    refreshPosPrinterList();
                }
            });

            $(document).on('change', '#posPrintKotWithBill', function() {
                try {
                    localStorage.setItem('posPrintKotWithBill', $(this).is(':checked') ? '1' : '0');
                } catch (e) {}
                updateBillPrintUi();
            });

            $(document).on('change', '#posKotPrinterSelect', function() {
                try {
                    localStorage.setItem('posSelectedKotPrinter', $(this).val() || '');
                } catch (e) {}
                updateBillPrintUi();
            });

            $(document).on('change', '#posPrinterSelect', function() {
                try {
                    localStorage.setItem('posSelectedPrinter', $(this).val() || '');
                } catch (e) {}
                syncKotPrinterOptionsFromBill();
            });

            $(document).on('click', '#printBillBtn', function() {
                printBillFromModal();
            });

            $(document).on('click', '#printKotOnlyBtn', function() {
                if (!window._billOrderId) return;
                $('#printKotOnlyBtn').prop('disabled', true);
                printKotToKitchen(window._billOrderId).then(function() {
                    $('#printKotOnlyBtn').prop('disabled', false);
                });
            });

            $(document).on('click', '#closeBillBtn, #closeBillModalBtn', function() {
                var modalEl = document.getElementById("billGeneratedModal");
                var bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();
                $("#billPreviewIframe").attr("src", "");
                $('#posPrinterSettingsPanel').hide();
                $('#posPrinterSettingsToggle').html('<i class="fas fa-cog"></i> Printer settings');
            });

            // When bill modal opens, refresh QZ printers quietly
            $(document).on('shown.bs.modal', '#billGeneratedModal', function() {
                $('#posPrinterSettingsPanel').hide();
                $('#posPrinterSettingsToggle').html('<i class="fas fa-cog"></i> Printer settings');
                updateBillPrintUi();
                var tries = 0;
                (function waitQz() {
                    tries++;
                    if (window.InvoiceQzPrint || tries > 40) {
                        refreshPosPrinterList().then(function() {
                            updateBillPrintUi();
                            // First-time setup: open settings if no bill printer saved
                            var billVal = ($('#posPrinterSelect').val() || '').trim();
                            var saved = '';
                            try { saved = localStorage.getItem('posSelectedPrinter') || ''; } catch (e) {}
                            if (!billVal && !saved) {
                                $('#posPrinterSettingsPanel').show();
                                $('#posPrinterSettingsToggle').html('<i class="fas fa-times"></i> Hide settings');
                            }
                        });
                        if (window.InvoiceQzPrint && InvoiceQzPrint.preparePhonePrint) {
                            var frame = document.getElementById('billPreviewIframe');
                            if (frame && frame.src) {
                                InvoiceQzPrint.preparePhonePrint(frame.src);
                            }
                        }
                        return;
                    }
                    setTimeout(waitQz, 150);
                })();
            });

            // F10 → Print bill (+ KOT if checked) when Bill modal is open
            $(document).on('keydown', function(e) {
                if (e.key === 'F10' || e.keyCode === 121) {
                    var modalEl = document.getElementById("billGeneratedModal");
                    if (modalEl && modalEl.classList.contains('show')) {
                        e.preventDefault();
                        printBillFromModal();
                    }
                }
            });

            // ── Multi-bill tabs (Bill 1 / +) ──────────────────────────────────
            window.initPosBillTabs = function() {
                const MAX_TABS = 12;
                let tabs = [];
                let activeId = null;
                let nextNum = 1;
                let switching = false;

                function cloneItems(mapRef) {
                    try {
                        return JSON.parse(JSON.stringify(Array.from(mapRef.entries())));
                    } catch (e) {
                        return [];
                    }
                }

                function emptyState() {
                    return {
                        items: [],
                        customerName: '',
                        customerPhone: '',
                        gstOption: defaultGstOption || 'without',
                        paymentMethod: defaultPaymentMethod || 'cash',
                        discount: '',
                        orderType: 'Self Pickup',
                        staffId: ''
                    };
                }

                function currentCustomerName() {
                    const $sel = $('#customer_name_input');
                    if (!$sel.length) return '';
                    const val = $sel.val();
                    if (!val) return '';
                    const $opt = $sel.find('option:selected');
                    let text = ($opt.text() || String(val)).trim();
                    const lower = text.toLowerCase();
                    if (lower === 'select or type customer' || lower === 'choose' || lower === 'select') return '';
                    if (lower.indexOf('default customer') === 0) return '';
                    const phone = String($('#customer_phone').val() || '').trim();
                    if (phone && text.indexOf('(' + phone + ')') !== -1) {
                        text = text.replace('(' + phone + ')', '').trim();
                    }
                    return text;
                }

                function resetPaymentExtras() {
                    $("#paidTypeBox").hide();
                    $("#cashOnlineBox").hide();
                    $("#paidTypeSelect").val("");
                    $("#paidAmountBox").hide();
                    $("#enterAmount").prop("readonly", false).val("");
                    $("#pendingAmount").prop("readonly", true).val("");
                    $("#debitExtraFields").hide();
                    $("#debitBankSelect").val("");
                    $("#debitRemark").val("");
                    $("#scanExtraFields").hide();
                    $("#scanBankSelect").val("");
                    $("#scanRemark").val("");
                    $("#cashOnlinePaidTypeSelect").val("");
                    $("#cashOnlinePaidAmountBox").hide();
                    $("#cashOnlineEnterAmount").val("");
                    $("#cashOnlinePendingAmount").val("");
                    $("#cashAmount").val("");
                    $("#onlineAmount").val("0");
                    $("#cashOnlineBankSelect").val("");
                    $("#cashOnlineRemark").val("");
                }

                function snapshotState() {
                    return {
                        items: cloneItems(selectedItems),
                        customerName: $('#customer_name_input').val() || '',
                        customerPhone: $('#customer_phone').val() || '',
                        gstOption: $("input[name='gst_option']:checked").val() || 'without',
                        paymentMethod: $("input[name='payment_method']:checked").val() || defaultPaymentMethod || 'cash',
                        discount: $('#discount').val() || '',
                        orderType: $('#order_type_select').val() || 'Self Pickup',
                        staffId: $('#assigned_staff_id').val() || ''
                    };
                }

                function snapshotActive() {
                    if (switching || !activeId) return;
                    const tab = tabs.find(t => t.id === activeId);
                    if (!tab) return;
                    tab.state = snapshotState();
                }

                function itemCount(state) {
                    return (state && state.items) ? state.items.length : 0;
                }

                function tabTitle(tab) {
                    const name = (tab.state && tab.state.customerName) ? String(tab.state.customerName).trim() : '';
                    if (name && name.toLowerCase().indexOf('default customer') !== 0) {
                        return name;
                    }
                    return 'Bill ' + tab.number;
                }

                function renderTabs() {
                    const $wrap = $('#posBillTabs');
                    if (!$wrap.length) return;
                    $wrap.empty();
                    tabs.forEach(function(tab) {
                        const count = itemCount(tab.state);
                        const title = tabTitle(tab);
                        const safeTitle = $('<div>').text(title).html();
                        const $btn = $(`
                            <div class="pos-bill-tab${tab.id === activeId ? ' active' : ''}${count ? ' has-items' : ''}" role="button" tabindex="0" data-bill-id="${tab.id}" title="${safeTitle}">
                                <span class="pos-bill-tab-name">${safeTitle}</span>
                                <span class="pos-bill-tab-count">${count}</span>
                                <span class="pos-bill-tab-close" data-close-bill="${tab.id}" title="Close">&times;</span>
                            </div>
                        `);
                        $wrap.append($btn);
                    });
                }

                function restoreState(state) {
                    switching = true;
                    const data = state || emptyState();
                    selectedItems.clear();
                    (data.items || []).forEach(function(entry) {
                        if (Array.isArray(entry) && entry.length >= 2) {
                            selectedItems.set(String(entry[0]), entry[1]);
                        }
                    });

                    const custVal = data.customerName || '';
                    const $cust = $('#customer_name_input');
                    if ($cust.length) {
                        if (custVal && !$cust.find('option').filter(function() { return $(this).val() === custVal; }).length) {
                            const opt = new Option(custVal, custVal, true, true);
                            $cust.append(opt);
                        }
                        $cust.val(custVal).trigger('change');
                    }
                    $('#customer_phone').val(data.customerPhone || '');

                    const gstVal = data.gstOption || 'without';
                    $("input[name='gst_option'][value='" + gstVal + "']").prop('checked', true).trigger('change');

                    resetPaymentExtras();
                    const payVal = data.paymentMethod || defaultPaymentMethod || 'cash';
                    $("input[name='payment_method']").prop('checked', false);
                    $('.paymentmethod').removeClass('active');
                    const $pay = $("input[name='payment_method'][value='" + payVal + "']");
                    if ($pay.length) {
                        $pay.closest('a.paymentmethod, .paymentmethod').trigger('click');
                    } else if (typeof window.applyDefaultPosPayment === 'function') {
                        window.applyDefaultPosPayment();
                    }
                    if ($("#paidTypeSelect").length && (payVal === 'cash' || payVal === 'debit card' || payVal === 'scan')) {
                        $("#paidTypeSelect").val(defaultPaidType || 'fully').trigger('change');
                    }

                    $('#discount').val(data.discount || '');
                    if ($('#order_type_select').length) {
                        $('#order_type_select').val(data.orderType || 'Self Pickup').trigger('change');
                    }
                    if ($('#assigned_staff_id').length) {
                        $('#assigned_staff_id').val(data.staffId || '').trigger('change');
                    }

                    $('.check-product i').removeClass('selected');
                    selectedItems.forEach(function(item) {
                        if (item && item.productId) {
                            $(".productset[data-id='" + item.productId + "'] .check-product i").addClass('selected');
                        }
                    });

                    if (typeof window.updateTotalItems === 'function') {
                        window.updateTotalItems();
                    }
                    switching = false;
                }

                function addTab(makeActive, options) {
                    if (tabs.length >= MAX_TABS) {
                        Swal.fire('Limit reached', 'You can keep up to ' + MAX_TABS + ' bills open.', 'info');
                        return;
                    }
                    snapshotActive();
                    const keepCurrent = !!(options && options.keepCurrent);
                    const tab = {
                        id: 'bill_' + Date.now() + '_' + nextNum,
                        number: nextNum++,
                        state: keepCurrent ? snapshotState() : emptyState()
                    };
                    tabs.push(tab);
                    if (makeActive !== false) {
                        activeId = tab.id;
                        if (!keepCurrent) {
                            restoreState(tab.state);
                        }
                    }
                    renderTabs();
                }

                function switchTab(id) {
                    if (!id || id === activeId) return;
                    snapshotActive();
                    const tab = tabs.find(t => t.id === id);
                    if (!tab) return;
                    activeId = tab.id;
                    restoreState(tab.state);
                    renderTabs();
                }

                function closeTab(id) {
                    const idx = tabs.findIndex(t => t.id === id);
                    if (idx === -1) return;
                    if (tabs.length === 1) {
                        tabs[0].state = emptyState();
                        restoreState(tabs[0].state);
                        renderTabs();
                        return;
                    }
                    const wasActive = tabs[idx].id === activeId;
                    tabs.splice(idx, 1);
                    if (wasActive) {
                        const next = tabs[Math.max(0, idx - 1)];
                        activeId = next.id;
                        restoreState(next.state);
                    }
                    renderTabs();
                }

                window.refreshActivePosBillTab = function() {
                    if (switching || !activeId) return;
                    snapshotActive();
                    renderTabs();
                };

                window.posBillTabsCloseCurrentAfterSave = function() {
                    snapshotActive();
                    closeTab(activeId);
                    if (!tabs.length) {
                        addTab(true);
                    }
                };

                $('#posAddBillTabBtn').on('click', function() {
                    addTab(true);
                });
                $(document).on('click', '.pos-bill-tab', function(e) {
                    if ($(e.target).closest('.pos-bill-tab-close').length) return;
                    switchTab(String($(this).attr('data-bill-id') || ''));
                });
                $(document).on('click', '.pos-bill-tab-close', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeTab(String($(this).data('close-bill')));
                });
                $(document).on('keydown', function(e) {
                    if (!e.shiftKey || (e.key !== 'T' && e.key !== 't')) return;
                    if ($(e.target).is('input, textarea, select, [contenteditable="true"]')) return;
                    e.preventDefault();
                    addTab(true);
                });

                $(document).on('change input', '#customer_name_input, #customer_phone, #discount, #order_type_select, #assigned_staff_id, input[name="gst_option"], input[name="payment_method"]', function() {
                    if (typeof window.refreshActivePosBillTab === 'function') {
                        window.refreshActivePosBillTab();
                    }
                });

                addTab(true, { keepCurrent: true });
            };

            $(function() {
                if ($('#posBillTabs').length) {
                    window.initPosBillTabs();
                }
            });
        </script>
        @include('partials.invoice-qz-print-script')
        @endpush
