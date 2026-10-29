@extends('layout.app')

@section('title', 'Tables View')

@section('content')
    <style>
        /* --- Modern Premium Styling --- */
        .modal-content {
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 25px 50px -12px rgba(209, 193, 193, 0.25) !important;
            margin-top: 0rem !important;
            overflow: hidden;
        }

        .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
        }

        .modal-header .modal-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.25rem;
        }

        .modal-body {
            background: #fdfdfd;
            padding: 1.5rem;
        }

        .modal-lg {
            max-width: 1150px !important;
        }

        .modal-xl {
            max-width: 1200px !important;
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

        .setvaluecash ul li a {
            border: 1px solid #e9ecef;
            color: #000;
            font-size: 11px;
            font-weight: 600;
            min-height: 59px;
            border-radius: 5px;
            padding: 8px 7px;
        }

        .disabled-product {
            opacity: 0.6;
            pointer-events: none;
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
            padding: 6px 4px 10px 4px;
            margin: 0 0 16px 0;
            list-style: none;
            /* Custom slim scrollbar ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â visible but styled */
            scrollbar-width: thin;
            scrollbar-color: #ff9f43 #f1f5f9;
        }

        /* Chrome/Safari custom scrollbar */
        .tabs_wrapper ul.tabs::-webkit-scrollbar {
            height: 4px;
            display: block;
        }

        .tabs_wrapper ul.tabs::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 99px;
        }

        .tabs_wrapper ul.tabs::-webkit-scrollbar-thumb {
            background: #ff9f43;
            border-radius: 99px;
        }

        .tabs_wrapper ul.tabs::-webkit-scrollbar-thumb:hover {
            background: #ff821c;
        }

        .tabs_wrapper ul.tabs li {
            flex: 0 0 auto;
            cursor: pointer;
            transition: transform 0.2s ease;
            width: max-content !important;
        }
        
        .tabs_wrapper ul.tabs li:active {
            transform: scale(0.95);
        }

        .product-details {
            background: #f1f5f9;
            padding: 8px 20px;
            border-radius: 50px;
            border: 1px solid #e2e8f0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: max-content !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .product-details h6 {
            font-size: 14.5px;
            font-weight: 600;
            color: #475569;
            margin: 0;
            white-space: nowrap;
        }

        .tabs_wrapper ul.tabs li:hover .product-details {
            background: #e2e8f0;
            border-color: #cbd5e1 !important;
        }

        .tabs_wrapper ul.tabs li.active .product-details {
            background: linear-gradient(135deg, #ff9f43 0%, #ff821c 100%);
            border-color: transparent !important;
            box-shadow: 0 6px 12px rgba(255, 159, 67, 0.35);
        }

        .tabs_wrapper ul.tabs li.active .product-details h6 {
            color: #ffffff !important;
        }
        
        /* --- Product Card (Sleek Glassmorphic) --- */
        .productset {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            text-align: center;
            position: relative;
        }

        .productset:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -8px rgba(0,0,0,0.15);
            border-color: #cbd5e1;
        }

        .productset.active {
            border-color: #ff9f43;
            box-shadow: 0 0 0 2px rgba(255, 159, 67, 0.2);
        }

        .productsetimgin {
            height: 85px;
            width: 100%;
            object-fit: contain;
            border-radius: 8px;
            background: #f8fafc;
            padding: 8px;
            margin-bottom: 10px;
            transition: transform 0.3s ease;
        }
        
        .productset:hover .productsetimgin {
            transform: scale(1.05);
        }

        .productsetcontent {
            padding: 0.5rem;
        }

        .productsetcontent h4 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .productsetcontent h6 {
            font-size: 15px;
            font-weight: 800;
            color: #ff9f43;
            margin: 0;
        }

        /* --- Order Panel --- */
        .card-order {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-order .card-body {
            padding: 1.5rem;
        }

        /* GST Radio Styling */
        .custom-radio-label {
            font-weight: 600;
            color: #475569;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .custom-radio-label input[type="radio"] {
            accent-color: #ff9f43;
            width: 18px;
            height: 18px;
        }

        /* Total Section Button */
        .btn-total-amount {
            background: linear-gradient(135deg, #1b2850 0%, #0f172a 100%) !important;
            color: white !important;
            border: none;
            border-radius: 12px;
            padding: 16px;
            width: 100%;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(27, 40, 80, 0.2);
        }

        .btn-total-amount:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(27, 40, 80, 0.3);
        }

        .payment_panel {
            /* position: fixed; */
            bottom: 0;
            background-color: white;
            /* width: 27.5%; */
        }

        .body_space {
            /*margin-bottom: 2rem;*/
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
        }

        .table-card-wrap {
            position: relative;
        }

        .table-box.table-card {
            width: 100%;
            min-height: 0;
            height: auto;
            border-radius: 16px;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: stretch;
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
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 10px 12px;
            border-top: 1px solid #f1f5f9;
            background: #fafbfc;
        }

        .table-card__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .table-card__action--reserve,
        .table-card__action--print-full {
            padding: 8px 10px;
            flex: 1;
            width: 100%;
        }

        .table-card__action--reserve:hover {
            background: #fff7ed;
            border-color: #fdba74;
            color: #c2410c;
        }

        .table-card__action--print {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            padding: 0;
        }

        .table-card__action--print-full {
            height: auto;
            min-height: 34px;
        }

        .table-card__action--print:hover,
        .table-card__action--print-full:hover {
            background: #0f172a;
            border-color: #0f172a;
            color: #fff;
        }

        .table-card__action-spacer {
            flex: 1;
            min-height: 34px;
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

            .table-card__action--reserve span {
                display: none;
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <input type="hidden" name="table_id" id="table_id">
                        Table: <span class="js-table-name"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                    <div class="col-lg-7 col-sm-12 tabs_wrapper">

                        @php
                            $hasProducts = false; // start as false
                        @endphp

                        <ul class="tabs border-0 mb-4">
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
                    </div>
                    <div class="col-lg-5 col-sm-12 ">

                        <div class="card card-order">
                            <div class="card-body body_space">
                                <div class="gst-header mb-2"
                                    style="display: flex; align-items: center; justify-content: space-between;">
                                    <div class="d-flex">
                                        <label class="custom-radio-label" style="margin-right: 1rem;">
                                            <input type="radio" name="gst_option" id="without_gst" value="without"
                                                checked />
                                            Without GST
                                        </label>

                                        <label class="custom-radio-label">
                                            <input type="radio" name="gst_option" id="with_gst" value="with" />
                                            With GST
                                        </label>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-lg-12">
                                        <div
                                            class="header-search d-flex align-items-center position-relative me-3 mb-3 search_custom_product d-none">
                                            <!-- Search Icon -->
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/search.svg' }}"
                                                alt="Search"
                                                style="position: absolute; left: 12px; width: 18px; height: 18px; z-index: 10; opacity: 0.6;">

                                            <!-- Input Field -->
                                            <input type="text" id="customerSearch1"
                                                class="form-control form-control-sm rounded px-3 ps-5"
                                                placeholder="Search..." autocomplete="off"
                                                style="height: 38px; font-size: 14px;">

                                            <!-- Search Results -->
                                            <div id="searchResults1"
                                                class="list-group bg-white position-absolute rounded shadow mt-1 w-100"
                                                style="z-index: 1050; max-height: 300px; overflow-y: auto; display: none; top: 100%; left: 0;">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <!-- Customer Name -->

                                        </div>
                                    </div>
                                </div>

                                <div class="split-card">

                                </div>
                                <div class="card-body pt-0">
                                    <div class="totalitem">
                                        <h4>Total items : 4</h4>
                                        <a href="javascript:void(0);" class="clear_items">Clear all</a>
                                    </div>
                                    <div class="product-table">

                                    </div>
                                </div>
                                <div class="split-card">
                                </div>
                                <div class="card-body pt-0 pb-2 body_space_two">
                                    <div class="col-lg-12">
                                        <div class="select-split ">
                                            <div class="select-group w-100">
                                                <label for="discount">Discount (%)</label>
                                                <input type="number" class="form-control" placeholder="Discount %"
                                                    name="discount" id="discount">
                                                <span class="error_discount"></span>
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
                                                <input type="hidden" name="price_after_discount" value="0.00"
                                                    class="tax-hidden">
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
                                    <div class="payment_panel">
                                        <div class="setvaluecash">
                                            <ul>
                                                <li class="d-none">
                                                    <a href="javascript:void(0);" class="paymentmethod">
                                                        <input type="radio" name="payment_method" value="pending" checked
                                                            hidden>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                            <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                            <path
                                                                d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                                        </svg>
                                                        Pay Later
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" class="paymentmethod">
                                                        <input type="radio" name="payment_method" value="cash" hidden>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                            <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                            <path
                                                                d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                                        </svg>
                                                        Cash
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" class="paymentmethod">
                                                        <input type="radio" name="payment_method" value="debit card" hidden>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            fill="#1b2850" class="bi bi-credit-card" viewBox="0 0 16 16">
                                                            <path
                                                                d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z" />
                                                            <path
                                                                d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z" />
                                                        </svg>
                                                        Debit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" class="paymentmethod">
                                                        <input type="radio" name="payment_method" value="cash+online"
                                                            hidden>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                            <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                            <path
                                                                d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                                        </svg>
                                                        Cash+Online
                                                    </a>
                                                </li>

                                                <li>
                                                    <a href="javascript:void(0);" class="paymentmethod">
                                                        <input type="radio" name="payment_method" value="scan" hidden>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            fill="#1b2850" class="bi bi-qr-code-scan" viewBox="0 0 16 16">
                                                            <path
                                                                d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z" />
                                                            <path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z" />
                                                            <path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z" />
                                                            <path
                                                                d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z" />
                                                            <path d="M12 9h2V8h-2z" />
                                                        </svg>
                                                        Scan
                                                    </a>
                                                </li>

                                            </ul>
                                            <div id="cashOnlineBox" style="display:none; margin-top:10px;">
                                                <div class="form-group">
                                                    <label>Cash Amount</label>
                                                    <input type="number" class="form-control" id="cashAmount"
                                                        placeholder="Enter Cash Amount" placeholder="0">
                                                </div>
                                                <div class="form-group">
                                                    <label>Online Amount</label>
                                                    <input type="number" class="form-control" id="onlineAmount"
                                                        placeholder="Enter Online Amount" value="0" readonly>
                                                </div>
                                            </div>
                                            <span class="error_peymentmethod"></span>
                                        </div>

                                        <div class="btn-totallabel">
                                            <h6>Total Amount : 00.00$</h6>
                                            <h5> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"
                                                    style="margin-bottom: 0.1rem;">
                                                    <path fill-rule="evenodd"
                                                        d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" />
                                                </svg></h5>
                                        </div>

                                        <div class="modal-order-edit"
                                            style="display: flex; justify-content: space-between;">
                                            <div class="btn-totallabel_1" data-order_update_id="" style="width: 48%;">
                                                <h6>Update Order </h6>
                                                <h5> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"
                                                        style="margin-bottom: 0.1rem;">
                                                        <path fill-rule="evenodd"
                                                            d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8">
                                                        </path>
                                                    </svg></h5>
                                            </div>
                                            <div class="btn-totallabel_2" data-order_payment_update_id=""
                                                style="width: 48%;">
                                                <h6>Checkout</h6>
                                                <h5> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"
                                                        style="margin-bottom: 0.1rem;">
                                                        <path fill-rule="evenodd"
                                                            d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8">
                                                        </path>
                                                    </svg></h5>
                                            </div>
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
            $('.btn-totallabel_1').css('display', 'flex');
            $('.btn-totallabel_1').attr('data-order_update_id', salse_id);
            $('.btn-totallabel_2').attr('data-order_payment_update_id', salse_id);
            $('.btn-totallabel_2').css('display', 'flex');
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
            $('.btn-totallabel').show();
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
                    '<a href="javascript:void(0);" class="print-table-order table-card__action table-card__action--print table-card__action--print-full"' +
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
                $('#reservationForm')[0].reset();
                reservationModalEl.querySelector('.js-reservation-table-name').textContent = tableName || '';
                $('#reservation_table_id').val(tableId);
                if (tableTypeChoiceModal) tableTypeChoiceModal.hide();
                reservationModal.show();
            }

            $(document).on('click', '.js-reserve-table', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openReservationForTable($(this).data('table-id'), $(this).data('table-name'));
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
                        $('.btn-totallabel').show();
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
                let tableId = $(this).data("table_no_invoice");

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

            function loadProductsByCategory(categoryId) {

                $.ajax({
                    url: "/api/getProductsByCategory/" + categoryId,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function (response) {
                        const currencySymbol = response.currency_symbol || 'ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¹';
                        const currencyPosition = response.currency_position || 'left';


                        if (response.status) {
                            renderProducts(response.data, categoryId, currencySymbol, currencyPosition);
                        } else {
                            renderProducts([], categoryId, currencySymbol, currencyPosition);
                        }
                    },
                    error: function (xhr, status, error) {

                        renderProducts([], categoryId);
                    }
                });
            }

            $(".tabs li").click(function () {
                var categoryId = $(this).attr('id');


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
                                                    <div class="col-lg-3 col-sm-6 d-flex position-relative">
                                                        <div class="productset flex-fill text-center ${disabledClass}" data-id="${product.id}" data-stock="${product.quantity}" style="position: relative;">
                                                            ${outOfStockBadge}
                                                            <div class="productsetimg">
                                                                <img src="${productImage}" alt="${product.name}" class="productsetimgin">
                                                                <h6>Qty: ${product.quantity || '0'}</h6>
                                                            </div>
                                                            <div class="productsetcontent" style="padding: 0.5rem;">
                                                                <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 2px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                    ${product.name}
                                                                </h4>
                                                                <h1 class="d-none">${product.categoryId || 'Uncategorized'}</h1>
                                                                <h2 class="d-none">${product.quantity_type || 'pcs'}</h2>
                                                                <h3 class="d-none">${product.quantity_type_id || 'pcs'}</h3>

                                                                <div style="font-size: 12px; color: #64748b; font-weight: 500; text-transform: capitalize; margin-bottom: 6px;">
                                                                    ${product.category_name || 'Uncategorized'}
                                                                </div>
                                                                <h6 style="font-size: 15px; font-weight: 800; color: #ff9f43; margin: 0;">${displayPrice}</h6>
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
                    $(".btn-totallabel h6").html('Total Amount : ' + formatCurrency(total.toFixed(2),
                        currencySymbol, currencyPosition));
                    $("input[name='total']").val(total.toFixed(2));
                    $("#onlineAmount").val(total.toFixed(2));
                } else {
                    // Reset everything if no subtotal
                    $(".taxList").hide().find("h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
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
