@extends('layout.app')
@section('title', 'Leave List')

@section('content')
    <style>
        :root {
            --leave-accent: #FF9F43;
            --leave-accent-soft: #fff3e6;
        }

        .fc-event-time {
            display: none;
        }

        .capitalize-text {
            text-transform: capitalize;
        }

        .fc-event-title {
            text-transform: capitalize;
        }

        /* Calendar View Redesign */
        .calendar-view-container .leave-legend {
            margin: 0 0 14px;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid #e5ebf3;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
        }

        .calendar-view-container .legend-item {
            font-size: 14px;
            font-weight: 500;
            color: #27364a;
            line-height: 1.2;
        }

        .calendar-view-container .legend-box {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            display: inline-block;
            flex-shrink: 0;
        }

        .calendar-view-container .legend-box.sick-leave {
            background: #28a745;
        }

        .calendar-view-container .legend-box.paid-leave {
            background: #ff6347;
        }

        .calendar-view-container .legend-box.vacation-leave {
            background: #d3c75e;
        }

        .calendar-view-container .legend-box.casual-leave {
            background: #FF9F43;
        }

        .calendar-view-container .team-members.team-margin {
            margin-top: 0;
            padding: 12px 14px;
            background: #ffffff;
            border: 1px solid #e5ebf3;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
        }

        .calendar-view-container .fc .fc-toolbar.fc-header-toolbar {
            margin: 0 0 12px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .calendar-view-container .fc .fc-toolbar-chunk {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .calendar-view-container .fc .fc-toolbar-title {
            font-size: 23px;
            font-weight: 700;
            color: #1e2e45;
            margin: 0;
            text-transform: capitalize;
            letter-spacing: 0.2px;
        }

        .calendar-view-container .fc .fc-button {
            border-radius: 6px !important;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 12px;
            text-transform: capitalize;
            box-shadow: none !important;
        }

        .calendar-view-container .fc .fc-fa-icon-btn {
            /* min-width: 34px; */
            padding: 6px 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .fc-fa-icon-btn {
    min-width: 40px;
    height: 36px;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.fc-fa-icon-btn i {
    font-size: 16px;
    color: #333;
}
.calendar-view-container .fc-toolbar-chunk {
    gap: 10px !important;
}
@media (max-width: 768px) {
    .fc-fa-icon-btn {
        min-width: 36px;
        height: 34px;
    }

    .calendar-view-container .fc-toolbar {
        flex-wrap: wrap;
        gap: 8px;
    }
}

        .calendar-view-container .fc .fc-fa-icon-btn i {
            font-size: 14px;
            line-height: 1;
        }

        .calendar-view-container .fc .fc-button-primary {
            color: #27364a;
            background: #ffffff;
            border: 1px solid #cfd8e3;
        }

        .calendar-view-container .fc .fc-button-primary:hover {
            color: #1b2838;
            background: #f8fafc;
            border-color: #b8c5d6;
        }

        .calendar-view-container .fc .fc-button-primary:not(:disabled).fc-button-active,
        .calendar-view-container .fc .fc-button-primary:not(:disabled):active {
            color: #ffffff;
            background: #1f2d3d;
            border-color: #1f2d3d;
        }

        .calendar-view-container .fc .fc-col-header-cell {
            background: #030811;
            border-color: #d8dee8;
        }

        .calendar-view-container .fc .fc-col-header-cell-cushion {
            display: block;
            width: 100%;
            padding: 9px 4px;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .calendar-view-container .fc .fc-scrollgrid {
            border-color: #d8dee8;
            border-radius: 8px;
            overflow: hidden;
        }

        .calendar-view-container .fc-theme-standard td,
        .calendar-view-container .fc-theme-standard th {
            border-color: #d8dee8;
        }

        .calendar-view-container .fc .fc-daygrid-day-frame {
            min-height: 84px;
            padding: 2px 4px;
        }

        .calendar-view-container .fc .fc-daygrid-day.fc-day-today {
            background: #f4efd9 !important;
        }

        .calendar-view-container .fc .fc-dayGridWeek-view .fc-daygrid-day-number {
            display: none;
        }

        .calendar-view-container .fc .fc-daygrid-day-number {
            font-size: 11px;
            color: #7b8796;
        }

        .calendar-view-container .fc .fc-daygrid-event {
            border: 0;
            background: transparent;
            margin-top: 4px;
        }

        .calendar-view-container .fc .fc-daygrid-dot-event {
            padding: 1px 0;
            border-radius: 4px;
        }

        .calendar-view-container .fc .fc-daygrid-dot-event:hover {
            background: #f3f7ff;
        }

        .calendar-view-container .fc .fc-daygrid-dot-event .fc-event-title {
            font-size: 13px;
            font-weight: 600;
            color: #1b66d1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .calendar-view-container .fc .fc-daygrid-dot-event .fc-event-dot {
            border-color: #f0644a;
        }

        /* Active employee highlighting */
        .employee-item {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .employee-item:hover {
            background-color: #f8f9fa;
        }

        .employee-item.active {
            background-color: var(--leave-accent-soft);
            border-left: 3px solid var(--leave-accent);
            font-weight: 600;
        }

        .employee-item.active .profile-pic {
            border: 2px solid var(--leave-accent);
        }

        .employee-item.active .employee-name {
            color: var(--leave-accent) !important;
        }

        /* Loading state */
        .calendar-loading {
            position: relative;
            min-height: 400px;
        }

        .calendar-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        /* Skeleton loading for employees */
        .skeleton {
            animation: skeleton-loading 1s linear infinite alternate;
        }

        @keyframes skeleton-loading {
            0% {
                background-color: hsl(200, 20%, 80%);
            }

            100% {
                background-color: hsl(200, 20%, 95%);
            }
        }

        .skeleton-item {
            height: 60px;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        /* View Mode Styles */
        .view-mode-toggle {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }

        .view-mode-btn {
            flex: 1;
            padding: 8px 16px;
            border: 2px solid #dee2e6;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-weight: 500;
        }

        .view-mode-btn:hover {
            border-color: var(--leave-accent);
            background: #f8f9fa;
        }

        .view-mode-btn.active {
            border-color: var(--leave-accent);
            background: var(--leave-accent);
            color: white;
        }

        .view-mode-btn i {
            font-size: 16px;
        }

        /* Unified View Containers */
        .calendar-view-container {
            display: none;
        }

        .calendar-view-container.active {
            display: block;
        }

        .datewise-view-container {
            display: none;
        }

        .datewise-view-container.active {
            display: block;
        }

        /* Employee Sidebar */
        .employee-sidebar {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .employee-sidebar h5 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
        }

        /* Employee list image layout (scoped to this page) */
        #employee-list .employee-item,
        #datewise-employee-list .employee-item {
            padding: 8px 10px !important;
        }

        #employee-list .team-member,
        #datewise-employee-list .team-member {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        #employee-list .profile-pic,
        #datewise-employee-list .profile-pic {
            width: 42px !important;
            height: 42px !important;
            min-width: 42px;
            border-radius: 50% !important;
            object-fit: cover;
            border: 1px solid #e2e8f0;
            background: #fff;
        }

        #employee-list .employee-name,
        #datewise-employee-list .employee-name {
            display: block;
            font-size: 14px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        /* Leave details modal */
        .leave-modal-dialog {
            max-width: 560px;
        }

        .leave-modal {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.18);
        }

        .leave-modal-header {
            border-bottom: 1px solid #eceff3;
            background: linear-gradient(135deg, #fff8ef 0%, #ffffff 55%);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .leave-modal-title-wrap {
            min-width: 0;
        }

        .leave-modal-title {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .leave-modal-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            color: #6b7280;
        }

        .leave-modal-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--leave-accent);
            box-shadow: 0 0 0 4px rgba(255, 159, 67, 0.2);
            flex-shrink: 0;
        }

        .leave-modal-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .leave-modal-close {
            opacity: .7;
        }

        .leave-modal-close:hover {
            opacity: 1;
        }

        .leave-modal-body {
            padding: 18px 20px 16px;
        }

        .leave-modal-staff {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e9eef5;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 14px;
        }

        .leave-modal-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12);
            background: #fff;
            flex-shrink: 0;
        }

        .leave-modal-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .leave-modal-value {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.3;
            word-break: break-word;
        }

        .leave-modal-reason {
            background: #ffffff;
            border: 1px solid #e9eef5;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 14px;
        }

        .leave-modal-reason-text {
            margin: 0;
            font-size: 14px;
            color: #334155;
            line-height: 1.45;
            word-break: break-word;
        }

        .leave-modal-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .leave-modal-meta-card {
            background: #f8fafc;
            border: 1px solid #e9eef5;
            border-radius: 10px;
            padding: 8px 10px;
            min-width: 160px;
            flex: 1;
        }

        .leave-modal-status-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .leave-modal-status-row #leaveStatus {
            border-radius: 10px;
            border: 1px solid #d6dde8;
            min-height: 40px;
        }

        .leave-status-pill {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .leave-status-pill.pending {
            background: #fff4cc;
            color: #9a6700;
        }

        .leave-status-pill.approved {
            background: #d7f3df;
            color: #1c7c3f;
        }

        .leave-status-pill.rejected {
            background: #fde2e2;
            color: #b91c1c;
        }

        .leave-modal-footer {
            border-top: 1px solid #eceff3;
            padding: 12px 20px 16px;
            gap: 8px;
        }

        .leave-modal-footer .btn {
            min-width: 120px;
            border-radius: 10px;
            font-weight: 600;
        }

        .leave-btn-outline {
            border: 1px solid #d6dde8;
            background: #fff;
            color: #334155;
        }

        .leave-btn-outline:hover {
            border-color: var(--leave-accent);
            color: var(--leave-accent);
            background: #fffaf3;
        }

        /* Unified Filter Section */
        .unified-filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .filter-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-col {
            flex: 1;
            min-width: 150px;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
            display: block;
        }

        /* Date Scroll Container */
        .date-scroll-wrapper {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .date-scroll-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .date-scroll-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .date-nav-buttons {
            display: flex;
            gap: 5px;
        }

        .date-nav-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }

        .date-nav-btn:hover {
            background: #f8f9fa;
            border-color: var(--leave-accent);
            color: var(--leave-accent);
        }

        .date-nav-btn i {
            font-size: 16px;
            line-height: 1;
        }

        @media (max-width: 575px) {
            .date-nav-btn {
                padding: 4px 8px;
                font-size: 11px;
                gap: 3px;
            }

            .date-nav-btn i {
                font-size: 14px;
            }
        }

        .date-scroll-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .date-scroll-container::-webkit-scrollbar {
            height: 6px;
        }

        .date-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .date-scroll-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .date-card {
            min-width: 60px;
            flex-shrink: 0;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 9px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .date-card.active {
            border-color: var(--leave-accent);
            background: var(--leave-accent-soft);
            box-shadow: 0 4px 8px rgba(255, 159, 67, 0.25);
        }

        .date-card.today {
            border-color: #28a745;
        }

        .date-card.has-leave {
            background-color: #fff8e1;
        }

        .date-card-day {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .date-card-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .date-card-indicator {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin: 3px auto 0;
        }

        /* Mobile Leave Cards */
        .mobile-leave-list {
            margin-top: 20px;
        }

        .mobile-leave-card {
            background: white;
            border: 1px solid #dee2e6;
            border-left: 4px solid;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .mobile-leave-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .mobile-leave-card.sick-leave {
            border-left-color: #28a745;
        }

        .mobile-leave-card.vacation-leave {
            border-left-color: #d3c75e;
        }

        .mobile-leave-card.casual-leave {
            border-left-color: var(--leave-accent);
        }

        .mobile-leave-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .mobile-leave-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mobile-leave-employee img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .mobile-leave-name {
            font-weight: bold;
            font-size: 14px;
        }

        .mobile-leave-type {
            font-size: 12px;
            /* color: #666; */
        }

        .mobile-leave-status {
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .mobile-leave-status.pending {
            background-color: #ffc107;
            color: #000;
        }

        .mobile-leave-status.approved {
            background-color: #28a745;
            color: white;
        }

        .mobile-leave-status.rejected {
            background-color: #dc3545;
            color: white;
        }

        .mobile-leave-info {
            font-size: 12px;
            /* color: #666; */
            margin-top: 5px;
        }

        .mobile-leave-reason {
            font-size: 13px;
            /* color: #333; */
            margin-top: 5px;
            font-style: italic;
        }

        /* Tablet Styles (768px - 1024px) */
        @media (max-width: 1024px) and (min-width: 769px) {
            .employee-sidebar {
                max-width: 250px;
            }

            .filter-col {
                min-width: 120px;
            }

            .date-card {
                min-width: 55px;
            }
        }

        /* Mobile Styles (max-width: 768px) */
        @media (max-width: 768px) {
            .card-body {
                padding: 12px;
            }

            .d-md-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }

            .d-md-flex.justify-content-between .card-title {
                font-size: 18px !important;
            }

            .d-md-flex.justify-content-between a {
                width: 100%;
            }

            .view-mode-btn {
                font-size: 13px;
                padding: 6px 12px;
            }

            .filter-row {
                gap: 8px;
            }

            .filter-col {
                min-width: 100%;
            }

            .employee-sidebar {
                padding: 12px;
            }

            .date-card {
                min-width: 50px;
                padding: 6px;
            }

            .date-card-number {
                font-size: 20px;
            }

            .leave-modal-title {
                font-size: 20px;
            }

            .leave-modal-meta {
                flex-direction: column;
                align-items: stretch;
            }

            .leave-modal-status-row {
                flex-wrap: wrap;
            }

            .leave-modal-footer .btn {
                flex: 1;
                min-width: 0;
            }

            .calendar-view-container .leave-legend {
                padding: 10px 12px;
            }

            .calendar-view-container .legend-item {
                font-size: 13px;
            }

            .calendar-view-container .fc .fc-toolbar-title {
                font-size: 24px;
            }

            .calendar-view-container .fc .fc-toolbar {
                justify-content: space-between;
            }
        }

        /* Small Mobile Styles (max-width: 480px) */
        @media (max-width: 480px) {
            .view-mode-btn {
                font-size: 12px;
                padding: 5px 8px;
            }

            .view-mode-btn span {
                display: none;
            }

            .date-card {
                min-width: 45px;
                padding: 5px;
            }

            .date-card-day {
                font-size: 10px;
            }

            .date-card-number {
                font-size: 18px;
            }

            .calendar-view-container .fc .fc-toolbar-title {
                font-size: 20px;
            }
        }

        /* Collapsible Toggle Styles */
        .collapsible-toggle {
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .collapsible-toggle i {
            transition: transform 0.3s ease;
        }

        .collapsible-toggle.collapsed i {
            transform: rotate(-90deg);
        }

        .collapsible-content {
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease;
            opacity: 1;
        }

        .collapsible-content.collapsed {
            max-height: 0;
            opacity: 0;
        }
        @media (max-width: 768px) {

        .view-mode-toggle {
            flex-direction: column; 
        }

        .view-mode-btn {
            width: 100%;
            justify-content: center;
            font-size: 14px;
            padding: 10px;
        }

        .view-mode-btn span {
            display: inline; 
        }
    }
        .team-member {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            overflow: hidden;
        }

        .team-member img {
            flex-shrink: 0;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .team-member > div {
            flex: 1;
            min-width: 0;
        }

        .employee-name {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }




        
    </style>
    <div class="content">

        <div class="page-header">
            <div class="page-title">
                <h4>Leave Calendar</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(28, 'add'))
                    <a href="{{ route('leave.add') }}" class="btn btn-sm btn-added">
                        <img src="{{ url(trim(env('ImagePath', ''), '/') . '/admin/assets/img/icons/plus.svg') }}" alt="img" class="me-1">New Leave
                    </a>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">


                        <div class="row">
                            <!-- View Mode Toggle -->
                            <div class="col-12 view-mode-toggle">
                                <button class="view-mode-btn active" data-view="datewise" id="datewise-view-btn">
                                    <i class="mdi mdi-view-list"></i>
                                    <span>Date-wise View</span>
                                </button>
                                <button class="view-mode-btn" data-view="calendar" id="calendar-view-btn">
                                    <i class="mdi mdi-calendar"></i>
                                    <span>Calendar View</span>
                                </button>
                            </div>

                            <!-- Unified Filters (for Date-wise view) - Collapsible -->
                            <div class="collapsible-toggle collapsed" onclick="toggleSection('leave-filters')">
                                <i class="fas fa-chevron-down"></i>
                                <strong>Filters</strong>
                            </div>
                            <div id="leave-filters-section" class="collapsible-content collapsed">
                                <div class="unified-filters" id="datewise-filters">
                                    <div class="filter-row">
                                        <div class="filter-col">
                                            <label class="filter-label">Month:</label>
                                            <select id="unified-month-select" class="form-select form-select-sm">
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
                                        <div class="filter-col">
                                            <label class="filter-label">Year:</label>
                                            <select id="unified-year-select" class="form-select form-select-sm">
                                                <!-- Years will be populated by JS -->
                                            </select>
                                        </div>
                                        <div class="filter-col" id="unified-employee-filter-container"
                                            style="display: none;">
                                            <label class="filter-label">Staff:</label>
                                            <select id="unified-employee-filter" class="form-select form-select-sm">
                                                <option value="">All Employees</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Calendar View Container -->
                            <div class="calendar-view-container" id="calendar-view">
                                <div class="row">
                                    <!-- Left Side: Employee Sidebar -->
                                    <div class="col-12 col-md-3 mb-3">
                                        <div class="employee-sidebar">
                                            <h5>Team Staffs</h5>
                                            <hr style="border-top: 1px solid #c9c4c1; width: 100%;">

                                            <div class="scrollable-container">
                                                <ul class="list-group" id="employee-list">
                                                    <!-- Loading skeleton -->
                                                    <li class="skeleton skeleton-item"></li>
                                                    <li class="skeleton skeleton-item"></li>
                                                    <li class="skeleton skeleton-item"></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Side: Calendar -->
                                    <div class="col-12 col-md-9">
                                        <!-- Leave Legend -->
                                         <div class="row g-2 mb-3 leave-legend sm-all-leave-top"> 
                                            <!--<div class="col-6 col-md-3">
                                                <div class="legend-item d-flex align-items-center">
                                                    <span class="legend-box sick-leave me-2"></span>
                                                    <span class="legend-label">Sick Leave</span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="legend-item d-flex align-items-center">
                                                    <span class="legend-box paid-leave me-2"></span>
                                                    <span class="legend-label">Paid Leave</span>
                                                </div>
                                            </div> -->
                                            <div class="col-6 col-md-3">
                                                <div class="legend-item d-flex align-items-center">
                                                    <span class="legend-box vacation-leave me-2"></span>
                                                    <span class="legend-label">Vacation Leave</span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="legend-item d-flex align-items-center">
                                                    <span class="legend-box casual-leave me-2"></span>
                                                    <span class="legend-label">Casual Leave</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Calendar -->
                                        <div class="team-members team-margin">
                                            <div id="calendar" class="full-calendar"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Date-wise View Container -->
                            <div class="datewise-view-container active" id="datewise-view">
                                <div class="row">
                                    <!-- Employee Sidebar (for larger screens in date-wise view) -->
                                    <div class="col-12 col-lg-3 mb-3 d-none d-lg-block">
                                        <div class="employee-sidebar">
                                            <h5>Team Staffs</h5>
                                            <hr style="border-top: 1px solid #c9c4c1; width: 100%;">

                                            <div class="scrollable-container">
                                                <ul class="list-group" id="datewise-employee-list">
                                                    <!-- Will be populated by JS -->
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Date Scroll and Leave List -->
                                    <div class="col-12 col-lg-9">
                                        <!-- Date Scroll -->
                                        <div class="date-scroll-wrapper">
                                            <div class="date-scroll-header">
                                                <div class="date-scroll-title" id="date-scroll-month-title">January 2026
                                                </div>
                                                <div class="date-nav-buttons">
                                                    <button class="date-nav-btn" id="date-nav-prev"
                                                        title="Previous Week">
                                                        <i class="mdi mdi-chevron-left"></i>
                                                        <span class="btn-text">Prev</span>
                                                    </button>
                                                    <button class="date-nav-btn" id="date-nav-today" title="Today">
                                                        <i class="mdi mdi-calendar-today"></i>
                                                        <span class="btn-text">Today</span>
                                                    </button>
                                                    <button class="date-nav-btn" id="date-nav-next" title="Next Week">
                                                        <i class="mdi mdi-chevron-right"></i>
                                                        <span class="btn-text">Next</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="unified-date-scroll" class="date-scroll-container">
                                                <!-- Dates will be rendered here -->
                                            </div>
                                        </div>

                                        <!-- Leave List -->
                                        <div id="unified-leave-list" class="mobile-leave-list">
                                            <!-- Leave cards will be rendered here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Details & Approval Modal -->
    <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered leave-modal-dialog">
            <div class="modal-content leave-modal">
                <div class="modal-header leave-modal-header">
                    <div class="leave-modal-title-wrap">
                        <h5 class="modal-title leave-modal-title" id="leaveModalLabel">Leave Details</h5>
                        <p class="leave-modal-subtitle">Review leave request and update status</p>
                    </div>

                </div>
                <div class="modal-body leave-modal-body">
                    <input type="hidden" id="csrf_token_name" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="leaveId">

                    <div class="leave-modal-staff">
                        <img id="leaveModalProfile"
                            src="{{ env('ImagePath') . '/admin/assets/img/customer/customer5.jpg' }}" alt="Staff Profile"
                            class="leave-modal-avatar">
                        <div>
                            <span class="leave-modal-label">Staff</span>
                            <span id="leaveUser" class="leave-modal-value capitalize-text"></span>
                        </div>
                    </div>

                    <div class="leave-modal-reason">
                        <span class="leave-modal-label">Reason</span>
                        <p id="leaveReason" class="leave-modal-reason-text capitalize-text"></p>
                    </div>

                    <div class="leave-modal-meta">
                        <div class="leave-modal-meta-card">
                            <span class="leave-modal-label">Created By</span>
                            <span id="created_by" class="leave-modal-value capitalize-text"></span>
                        </div>
                        <div class="leave-modal-status-row" id="statusContainer">
                            <span class="leave-modal-label mb-0">Status</span>
                            <select id="leaveStatus" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <span id="leaveStatusText" class="leave-status-pill capitalize-text mt-0"
                                style="display: none;"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="updateLeaveStatus" class="btn btn-submit" style="display: none;">Update
                        Status</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <script>
        // Toggle function for collapsible sections
        function toggleSection(sectionName) {
            const section = document.getElementById(sectionName + '-section');
            const toggle = section.previousElementSibling;

            section.classList.toggle('collapsed');
            toggle.classList.toggle('collapsed');
        }

        let calendar;
        let userRole = null;
        let cachedLeaveData = null;
        let currentEmployeeId = null;
        let isLoading = false;
        let selectedDate = new Date().toISOString().split('T')[0];
        let allLeaveEvents = [];
        let currentMonth = new Date().getMonth() + 1;
        let currentYear = new Date().getFullYear();
        let currentViewMode = 'datewise'; // 'calendar' or 'datewise'

        // View Mode Toggle Functions
        function switchViewMode(mode) {
            currentViewMode = mode;

            // Update button states
            document.querySelectorAll('.view-mode-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-view="${mode}"]`).classList.add('active');

            // Show/hide view containers
            document.getElementById('calendar-view').classList.remove('active');
            document.getElementById('datewise-view').classList.remove('active');
            document.getElementById('datewise-filters').style.display = 'none';

            if (mode === 'calendar') {
                document.getElementById('calendar-view').classList.add('active');
                // Refresh calendar
                if (calendar) {
                    calendar.render();
                    setTimeout(applyCalendarToolbarIcons, 0);
                }
            } else {
                document.getElementById('datewise-view').classList.add('active');
                document.getElementById('datewise-filters').style.display = 'block';
                renderDatewiseView();
            }
        }

        // function applyCalendarToolbarIcons() {
        //     const iconButtons = [{
        //             selector: '.fc-prevfa-button',
        //             iconClass: 'fa-chevron-left',
        //             label: 'Previous'
        //         },
        //         {
        //             selector: '.fc-nextfa-button',
        //             iconClass: 'fa-chevron-right',
        //             label: 'Next'
        //         },
        //         {
        //             selector: '.fc-todayfa-button',
        //             iconClass: 'fa-calendar',
        //             label: 'Today'
        //         }
        //     ];

        //     iconButtons.forEach(({
        //         selector,
        //         iconClass,
        //         label
        //     }) => {
        //         const button = document.querySelector(`#calendar-view ${selector}`);
        //         if (!button) return;

        //         button.classList.add('fc-fa-icon-btn');
        //         button.innerHTML = `<i class="fa fas ${iconClass}" aria-hidden="true"></i>`;
        //         button.setAttribute('aria-label', label);
        //         button.setAttribute('title', label);
        //     });
        // }

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridWeek',
                // customButtons: {
                //     prevfa: {
                //         text: 'chevron-left',
                //         click: () => calendar.prev()
                //     },
                //     nextfa: {
                //         text: 'chevron-right',
                //         click: () => calendar.next()
                //     },
                //     todayfa: {
                //         text: 'Today',
                //         click: () => calendar.today()
                //     }
                // },
                customButtons: {
    prevfa: {
        icon: 'chevron-left',
        click: () => calendar.prev()
    },
    nextfa: {
        icon: 'chevron-right',
        click: () => calendar.next()
    },
    todayfa: {
        text: 'Today',
        click: () => calendar.today()
    }
},
                headerToolbar: {
                    left: 'prevfa,nextfa,todayfa',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                buttonText: {
                    dayGridMonth: 'Month',
                    dayGridWeek: 'Week'
                },
                views: {
                    dayGridWeek: {
                        dayHeaderFormat: {
                            weekday: 'short',
                            month: 'numeric',
                            day: 'numeric',
                            omitCommas: true
                        }
                    },
                    dayGridMonth: {
                        dayHeaderFormat: {
                            weekday: 'short'
                        }
                    }
                },
                contentHeight: 'auto',
                locale: 'en',
                events: [],
                eventDisplay: 'list-item',
                dayMaxEventRows: 3,
                eventClick: function(info) {
                    showLeaveModal(info.event);
                },
                eventDidMount: function(info) {
                    tippy(info.el, {
                        content: `<strong>${info.event.title}</strong><br>${info.event.extendedProps.description}`,
                        placement: 'top',
                        animation: 'fade',
                        allowHTML: true
                    });
                },
                loading: function(isLoading) {
                    toggleCalendarLoading(isLoading);
                },
                // datesSet: function() {
                //     // applyCalendarToolbarIcons();
                // }
            });

            calendar.render();
            // setTimeout(applyCalendarToolbarIcons, 0);

            // Fetch data in parallel
            Promise.all([fetchLeaveData(), fetchEmployees()])
                .catch(error => console.error('Error loading initial data:', error));

            // Initialize view mode toggles
            document.getElementById('calendar-view-btn').addEventListener('click', () => switchViewMode(
                'calendar'));
            document.getElementById('datewise-view-btn').addEventListener('click', () => switchViewMode(
                'datewise'));

            // Initialize unified filters
            initializeUnifiedFilters();

            // Initialize date navigation
            initializeDateNavigation();
        });

        function initializeUnifiedFilters() {
            // Populate year dropdown
            const yearSelect = document.getElementById('unified-year-select');
            if (yearSelect) {
                for (let y = currentYear - 2; y <= currentYear + 1; y++) {
                    const option = document.createElement('option');
                    option.value = y;
                    option.textContent = y;
                    if (y === currentYear) option.selected = true;
                    yearSelect.appendChild(option);
                }
            }

            // Set current month
            const monthSelect = document.getElementById('unified-month-select');
            if (monthSelect) {
                monthSelect.value = String(currentMonth).padStart(2, '0');
            }

            // Show employee filter only for HR/Admin (will be updated when role is fetched)
            updateEmployeeFilterVisibility();

            // Add event listeners
            if (monthSelect) {
                monthSelect.addEventListener('change', (e) => {
                    currentMonth = parseInt(e.target.value);
                    if (calendar) calendar.gotoDate(new Date(currentYear, currentMonth - 1, 1));
                    renderDatewiseView();
                });
            }

            if (yearSelect) {
                yearSelect.addEventListener('change', (e) => {
                    currentYear = parseInt(e.target.value);
                    if (calendar) calendar.gotoDate(new Date(currentYear, currentMonth - 1, 1));
                    renderDatewiseView();
                });
            }
        }

        function updateEmployeeFilterVisibility() {
            const filterContainer = document.getElementById('unified-employee-filter-container');
            if (filterContainer && (userRole === 'admin' || userRole === 'hr')) {
                filterContainer.style.display = 'block';
            }
        }

        function initializeDateNavigation() {
            const prevBtn = document.getElementById('date-nav-prev');
            const nextBtn = document.getElementById('date-nav-next');
            const todayBtn = document.getElementById('date-nav-today');

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    const container = document.getElementById('unified-date-scroll');
                    if (container) {
                        container.scrollBy({
                            left: -300,
                            behavior: 'smooth'
                        });
                    }
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    const container = document.getElementById('unified-date-scroll');
                    if (container) {
                        container.scrollBy({
                            left: 300,
                            behavior: 'smooth'
                        });
                    }
                });
            }

            if (todayBtn) {
                todayBtn.addEventListener('click', () => {
                    selectedDate = new Date().toISOString().split('T')[0];
                    renderDatewiseView();

                    // Scroll to today's date
                    setTimeout(() => {
                        const activeCard = document.querySelector('.date-card.active');
                        if (activeCard) {
                            activeCard.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest',
                                inline: 'center'
                            });
                        }
                    }, 100);
                });
            }
        }

        function toStatusLabel(status) {
            if (!status) return 'Pending';
            return status.charAt(0).toUpperCase() + status.slice(1);
        }

        function applyStatusPill(status) {
            const statusText = $('#leaveStatusText');
            statusText.removeClass('pending approved rejected');
            if (status) {
                statusText.addClass(status.toLowerCase());
            }
            statusText.text(toStatusLabel(status));
        }

        function showLeaveModal(event) {
            $('#leaveModal').modal('show');
            $('#leaveId').val(event.id);
            $('#leaveUser').text(event.extendedProps.user);
            $('#leaveReason').text(event.extendedProps.description);
            $('#created_by').text(event.extendedProps.created_by || 'Unknown');
            $('#leaveStatus').val(event.extendedProps.status);
            $('#updateLeaveStatus').data('event', event);
            applyStatusPill(event.extendedProps.status);

            const profileImgEl = document.getElementById('leaveModalProfile');
            if (profileImgEl) {
                profileImgEl.src = getProfileImageUrl(event.extendedProps.profile_image);
                profileImgEl.onerror = function() {
                    this.onerror = null;
                    this.src = fallbackProfileImage;
                };
            }

            // Show/hide status controls based on user role
            if (userRole === 'employee' || userRole === 'staff') {
                $('#leaveStatus').hide();
                $('#leaveStatusText').show();
                $('#updateLeaveStatus').hide();
            } else {
                $('#leaveStatus').show();
                $('#leaveStatusText').hide();
                $('#updateLeaveStatus').show();
            }
        }

        function toggleCalendarLoading(loading) {
            const calendarEl = document.getElementById('calendar');
            if (loading) {
                calendarEl.classList.add('calendar-loading');
            } else {
                calendarEl.classList.remove('calendar-loading');
            }
        }

        function fetchLeaveData(employeeId = null) {
            if (isLoading) return Promise.resolve();

            const token = localStorage.getItem('token') || localStorage.getItem('authToken');
            let apiUrl = '/api/leave';

            if (employeeId) {
                apiUrl += `?user_id=${employeeId}`;
            }

            isLoading = true;

            return $.ajax({
                url: apiUrl,
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                success: function(response) {
                    if (response.status === 'success') {
                        cachedLeaveData = response.data; // Cache the data
                        userRole = response.role;
                        updateCalendarEvents(response.data);
                    }
                },
                error: function(error) {
                    console.error('Error fetching leaves:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load leave data',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    isLoading = false;
                }
            });
        }

        function updateCalendarEvents(leaveData) {
            // Clear existing events efficiently
            calendar.removeAllEvents();

            const events = leaveData.flatMap(leave =>
                leave.leaves.map(leaveItem => {
                    const todayDate = new Date().toISOString().split('T')[0];
                    const startDate = getDateOnlyString(leaveItem.start_date) || todayDate;
                    const endDate = getDateOnlyString(leaveItem.end_date) || startDate;

                    let backgroundColor = getLeaveTypeColor(leaveItem.leave_type);

                    return {
                        id: leaveItem.id.toString(),
                        title: `${leave.firstname || leaveItem.firstname} - ${leaveItem.status.charAt(0).toUpperCase() + leaveItem.status.slice(1)}`,
                        start: `${startDate}T00:00:00`,
                        end: `${endDate}T23:59:59`,
                        backgroundColor: backgroundColor,
                        extendedProps: {
                            user: leave.firstname || leaveItem.firstname,
                            user_id: leave.id || leave.user_id,
                            profile_image: leave.profile_image || leaveItem.profile_image || null,
                            description: leaveItem.reason || "No reason provided",
                            status: leaveItem.status,
                            created_by: leaveItem.created_by_username,
                            leave_type: leaveItem.leave_type
                        }
                    };
                })
            );

            allLeaveEvents = events;
            calendar.addEventSource(events);

            // Update datewise view if in that mode
            if (currentViewMode === 'datewise') {
                renderDatewiseView();
            }
        }

        function getDateOnlyString(value) {
            if (!value) return null;

            if (value instanceof Date) {
                if (isNaN(value.getTime())) return null;
                return value.toISOString().split('T')[0];
            }

            const rawValue = String(value).trim();
            if (!rawValue) return null;

            const ymdMatch = rawValue.match(/^(\d{4}-\d{2}-\d{2})/);
            if (ymdMatch) {
                return ymdMatch[1];
            }

            const normalized = rawValue.includes(' ') && !rawValue.includes('T') ?
                rawValue.replace(' ', 'T') :
                rawValue;
            const parsedDate = new Date(normalized);

            if (isNaN(parsedDate.getTime())) return null;
            return parsedDate.toISOString().split('T')[0];
        }

        function getLeaveTypeColor(leaveType) {
            const colorMap = {
                'Sick Leave': '#28a745',
                'Paid Leave': '#ff6347',
                'Vacation Leave': '#d3c75e',
                'Casual Leave': '#FF9F43'
            };
            return colorMap[leaveType] || '#FF9F43';
        }

        const storageProfileBase = "{{ rtrim(env('ImagePath', '/'), '/') }}/storage/";
        const fallbackProfileImage = "{{ rtrim(env('ImagePath', '/'), '/') }}/admin/assets/img/customer/customer5.jpg";

        function getProfileImageUrl(profileImage) {
            if (!profileImage) return fallbackProfileImage;

            const normalizedPath = String(profileImage).replace(/^\/+/, '').replace(/^storage\//, '');
            return `${storageProfileBase}${normalizedPath}`;
        }

        function fetchEmployees() {
            const token = localStorage.getItem('token') || localStorage.getItem('authToken');
            const selectedSubAdminId =
    localStorage.getItem('selectedSubAdminId') || '';
            return $.ajax({
                url: `/api/leave?selectedSubAdminId=${selectedSubAdminId}`,
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                success: function(response) {
                    if (response.status === 'success') {
                        userRole = response.role;
                        renderEmployeeList(response.data);
                        renderDatewiseEmployeeList(response.data);
                        updateEmployeeFilterVisibility();
                    }
                },
                error: function(error) {
                    console.error('Error fetching employees:', error);
                }
            });
        }

        function renderEmployeeList(employees) {
            let employeeList = $('#employee-list');
            employeeList.empty();
            const baseImagePath = "{{ rtrim(env('ImagePath', '/'), '/') }}/";

            // Show "All Employees" option for admin/hr
            if (userRole === 'admin' || userRole === 'hr') {
                let allItem = $(`
                <li class="list-group-item employee-item active" data-id="">
                    <div class="team-member">
                        <img src="${baseImagePath}admin/assets/img/customer/customer5.jpg" alt="All Employee" class="profile-pic bg-light" onerror="this.onerror=null;this.src='${fallbackProfileImage}'">
                        <div><strong class="employee-name">All</strong></div>
                    </div>
                </li>
            `);
                allItem.on('click', function() {
                    selectEmployee($(this), null);
                });
                employeeList.append(allItem);
            }

            // Render individual employees
            employees.forEach(function(employee) {
                let imageUrl = getProfileImageUrl(employee.profile_image);

                let employeeItem = $(`
                <li class="list-group-item employee-item p-1" data-id="${employee.id}">
                    <div class="team-member capitalize-text">
                        <img src="${imageUrl}" alt="${employee.firstname}" class="profile-pic" onerror="this.onerror=null;this.src='${fallbackProfileImage}'">
                        <div><strong class="employee-name">${employee.firstname}</strong></div>
                    </div>
                </li>
            `);

                employeeItem.on('click', function() {
                    selectEmployee($(this), employee.id);
                });

                employeeList.append(employeeItem);
            });

            // Populate unified employee filter
            populateUnifiedEmployeeFilter(employees);
        }

        function renderDatewiseEmployeeList(employees) {
            const employeeList = document.getElementById('datewise-employee-list');
            if (!employeeList) return;

            employeeList.innerHTML = '';
            const baseImagePath = "{{ rtrim(env('ImagePath', '/'), '/') }}/";

            // Show "All Employees" option for admin/hr
            if (userRole === 'admin' || userRole === 'hr') {
                const allItem = document.createElement('li');
                allItem.className = 'list-group-item employee-item active';
                allItem.dataset.id = '';
                allItem.innerHTML = `
                <div class="team-member">
                    <img src="${baseImagePath}admin/assets/img/customer/customer5.jpg" alt="All Employee" class="profile-pic bg-light" onerror="this.onerror=null;this.src='${fallbackProfileImage}'">
                    <div><strong class="employee-name">All</strong></div>
                </div>
            `;
                allItem.addEventListener('click', () => {
                    selectDatewiseEmployee(allItem, null);
                });
                employeeList.appendChild(allItem);
            }

            // Render individual employees
            employees.forEach(employee => {
                const imageUrl = getProfileImageUrl(employee.profile_image);

                const item = document.createElement('li');
                item.className = 'list-group-item employee-item p-1';
                item.dataset.id = employee.id;
                item.innerHTML = `
                <div class="team-member capitalize-text">
                    <img src="${imageUrl}" alt="${employee.firstname}" class="profile-pic" onerror="this.onerror=null;this.src='${fallbackProfileImage}'">
                    <div><strong class="employee-name">${employee.firstname}</strong></div>
                </div>
            `;
                item.addEventListener('click', () => {
                    selectDatewiseEmployee(item, employee.id);
                });
                employeeList.appendChild(item);
            });
        }

        function populateUnifiedEmployeeFilter(employees) {
            const unifiedFilter = document.getElementById('unified-employee-filter');
            if (!unifiedFilter) return;

            // Only populate if user is HR or Admin
            if (userRole !== 'admin' && userRole !== 'hr') {
                return;
            }

            unifiedFilter.innerHTML = '<option value="">All Employees</option>';

            employees.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = employee.firstname;
                unifiedFilter.appendChild(option);
            });

            // Add change event listener
            unifiedFilter.addEventListener('change', (e) => {
                currentEmployeeId = e.target.value || null;
                renderDatewiseLeavesForDate();
            });
        }

        function selectDatewiseEmployee(element, employeeId) {
            // Update active state
            document.querySelectorAll('#datewise-employee-list .employee-item').forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');

            currentEmployeeId = employeeId;
            renderDatewiseLeavesForDate();
        }

        function selectEmployee($element, employeeId) {
            // Update active state
            $('.employee-item').removeClass('active');
            $element.addClass('active');

            currentEmployeeId = employeeId;

            // Filter cached data if available, otherwise fetch
            if (cachedLeaveData && !employeeId) {
                updateCalendarEvents(cachedLeaveData);
            } else if (cachedLeaveData && employeeId) {
                const filteredData = cachedLeaveData.filter(emp => emp.id == employeeId);
                updateCalendarEvents(filteredData);
            } else {
                fetchLeaveData(employeeId);
            }
        }

        $('#updateLeaveStatus').click(function() {
            const token = localStorage.getItem('token') || localStorage.getItem('authToken');
            const leaveId = $('#leaveId').val();

            if (!leaveId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Leave ID',
                    text: 'Leave ID is required to update the leave status.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const newStatus = $('#leaveStatus').val();
            const event = $(this).data('event');
            const csrfTokenName = $('#csrf_token_name').attr('name');
            const csrfTokenValue = $('#csrf_token_name').val();

            const data = {
                status: newStatus
            };
            data[csrfTokenName] = csrfTokenValue;

            $.ajax({
                url: `/api/leave/update/${leaveId}`,
                method: 'POST',
                data: JSON.stringify(data),
                contentType: "application/json",
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            text: 'The leave status has been successfully updated.',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'hr-btnbg'
                            }
                        }).then(() => {
                            // Refresh data based on current filter
                            cachedLeaveData = null; // Clear cache
                            fetchLeaveData(currentEmployeeId);
                            $('#leaveModal').modal('hide');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to update',
                            text: response.message,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error updating leave status:', xhr);
                    let errorMessage = 'There was an error. Check console for details.';
                    if (xhr.responseJSON?.messages?.error) {
                        errorMessage = xhr.responseJSON.messages.error;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Date-wise view rendering functions
        function renderDatewiseView() {
            renderDatewiseDateScroll();
            renderDatewiseLeavesForDate();
        }

        function renderDatewiseDateScroll() {
            const container = document.getElementById('unified-date-scroll');
            if (!container) return;

            container.innerHTML = '';

            const today = new Date();
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();

            const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            const todayDate = today.toISOString().split('T')[0];

            // Update month title
            const monthTitle = document.getElementById('date-scroll-month-title');
            if (monthTitle) {
                monthTitle.textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
            }

            // Show all days of selected month
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentYear, currentMonth - 1, day);
                const dateStr = date.toISOString().split('T')[0];
                const dayOfWeek = date.getDay();

                // Check if there are leaves on this date
                const hasLeave = allLeaveEvents.some(event => {
                    const eventStart = getDateOnlyString(event.start);
                    const eventEnd = getDateOnlyString(event.end) || eventStart;
                    if (!eventStart || !eventEnd) return false;
                    return dateStr >= eventStart && dateStr <= eventEnd;
                });

                const dateCard = document.createElement('div');
                dateCard.className = 'date-card';
                if (dateStr === selectedDate) dateCard.classList.add('active');
                if (dateStr === todayDate) dateCard.classList.add('today');
                if (hasLeave) dateCard.classList.add('has-leave');

                dateCard.innerHTML = `
                <div class="date-card-day">${weekdays[dayOfWeek]}</div>
                <div class="date-card-number">${day}</div>
                ${hasLeave ? '<div class="date-card-indicator" style="background-color: #FF9F43;"></div>' : ''}
            `;

                dateCard.addEventListener('click', () => {
                    selectedDate = dateStr;
                    renderDatewiseDateScroll();
                    renderDatewiseLeavesForDate();
                });

                container.appendChild(dateCard);
            }

            // Scroll to active card
            setTimeout(() => {
                const activeCard = container.querySelector('.date-card.active');
                if (activeCard) {
                    activeCard.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                }
            }, 100);
        }

        function renderDatewiseLeavesForDate() {
            const container = document.getElementById('unified-leave-list');
            if (!container) return;

            container.innerHTML = '';

            // Filter events for selected date
            let filteredEvents = allLeaveEvents.filter(event => {
                const eventStart = getDateOnlyString(event.start);
                const eventEnd = getDateOnlyString(event.end) || eventStart;
                if (!eventStart || !eventEnd) return false;
                return selectedDate >= eventStart && selectedDate <= eventEnd;
            });

            // Filter by employee if selected
            if (currentEmployeeId) {
                filteredEvents = filteredEvents.filter(event => event.extendedProps.user_id == currentEmployeeId);
            }

            if (filteredEvents.length === 0) {
                container.innerHTML = '<div class="alert alert-info text-center">No leaves on this date</div>';
                return;
            }

            filteredEvents.forEach(event => {
                const props = event.extendedProps;
                const imageUrl = getProfileImageUrl(props.profile_image);

                // Determine leave type class
                let leaveTypeClass = 'casual-leave';
                if (props.leave_type === 'Sick Leave') leaveTypeClass = 'sick-leave';
                else if (props.leave_type === 'Paid Leave') leaveTypeClass = 'paid-leave';
                else if (props.leave_type === 'Vacation Leave') leaveTypeClass = 'vacation-leave';

                const card = document.createElement('div');
                card.className = `mobile-leave-card ${leaveTypeClass}`;

                // Calculate duration
                const startDateStr = getDateOnlyString(event.start);
                const endDateStr = getDateOnlyString(event.end) || startDateStr;
                const startDate = startDateStr ? new Date(`${startDateStr}T00:00:00`) : null;
                const endDate = endDateStr ? new Date(`${endDateStr}T00:00:00`) : null;
                let durationText = 'N/A';
                let dateRangeText = 'Invalid date';

                if (startDate && endDate && !isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                    const duration = Math.max(1, Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1);
                    durationText = duration === 1 ? '1 day' : `${duration} days`;
                    dateRangeText = `${startDate.toLocaleDateString()} - ${endDate.toLocaleDateString()}`;
                }

                card.innerHTML = `
                <div class="mobile-leave-header">
                    <div class="mobile-leave-employee">
                        <img src="${imageUrl}" alt="${props.user}" onerror="this.onerror=null;this.src='${fallbackProfileImage}'">
                        <div>
                            <div class="mobile-leave-name capitalize-text">${props.user}</div>
                            <div class="mobile-leave-type">${props.leave_type}</div>
                        </div>
                    </div>
                    <div class="mobile-leave-status ${props.status}">${props.status}</div>
                </div>
                <div class="mobile-leave-info">
                    <strong>Duration:</strong> ${durationText} (${dateRangeText})
                </div>
                <div class="mobile-leave-reason">
                    "${props.description}"
                </div>
            `;

                card.addEventListener('click', () => {
                    showLeaveModal(event);
                });

                container.appendChild(card);
            });
        }
    </script>
@endpush
