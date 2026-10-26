@extends('layout.app')

@section('title', 'Staff Dashboard')

@section('content')
<style>
    .staff-stat-card {
        border-radius: 16px;
        padding: 24px 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
        min-height: 110px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .12);
        margin-bottom: 20px;
    }

    .staff-stat-card .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 6px;
    }

    .staff-stat-card .stat-label {
        font-size: 1rem;
        font-weight: 600;
        opacity: .95;
        margin-bottom: 2px;
    }

    .staff-stat-card .stat-sub {
        font-size: .78rem;
        opacity: .8;
    }

    .staff-stat-card .stat-icon {
        position: absolute;
        right: 18px;
        bottom: 16px;
        font-size: 1.8rem;
        opacity: .25;
    }

    .card-attendance {
        background: linear-gradient(135deg, #ff9f43, #ff6b35);
    }

    .card-leave {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }

    .card-followup {
        background: linear-gradient(135deg, #3498db, #1b6ca8);
    }

    .card-meeting {
        background: linear-gradient(135deg, #2ecc71, #1a9e55);
    }

    .staff-panel {
        border: 1px solid #e9edf5;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        height: 100%;
    }

    .staff-panel .card-body {
        padding: 18px;
    }

    .attendance-overview {
        border: 1px solid #e9edf5;
        border-radius: 16px;
        background: linear-gradient(135deg, #fff 0%, #fff8f1 100%);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        padding: 18px 20px;
        margin-bottom: 20px;
    }

    .attendance-overview-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 14px;
    }

    .attendance-overview-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #1b2850;
    }

    .attendance-overview-sub {
        color: #64748b;
        font-size: 13px;
        margin-top: 4px;
    }

    .attendance-overview-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .attendance-pill {
        border-radius: 14px;
        padding: 14px 16px;
        background: #fff;
        border: 1px solid #edf1f7;
        min-height: 92px;
    }

    .attendance-pill-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .attendance-pill-value {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
    }

    .attendance-pill-value.timer {
        font-variant-numeric: tabular-nums;
        letter-spacing: 0.02em;
    }

    .attendance-pill-progress {
        margin-top: 10px;
        height: 7px;
        border-radius: 999px;
        background: #e9edf5;
        overflow: hidden;
    }

    .attendance-pill-progress > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #ff9f43, #f97316);
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1b2850;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #edf1f7;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .staff-table-wrap {
        overflow-x: auto;
    }

    .staff-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 620px;
    }

    .staff-table thead th {
        background: #fff;
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        padding: 12px 10px;
        border: 0;
        border-bottom: 1px solid #e9edf5;
    }

    .staff-table td {
        font-size: 13px;
        color: #475569;
        vertical-align: middle;
        padding: 14px 10px;
        border: 0;
        border-bottom: 1px solid #eef2f7;
        white-space: nowrap;
    }

    .staff-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .staff-table tbody tr:hover {
        background: #fafcff;
    }

    .staff-empty-state {
        min-height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #64748b;
        text-align: center;
    }

    .badge-pending {
        background: #fff3cd;
        color: #856404;
    }

    .badge-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-rescheduled,
    .badge-scheduled {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-present {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-half-day {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-absent,
    .badge-leave {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-high {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-medium {
        background: #fff3cd;
        color: #856404;
    }

    .badge-low {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge,
    .priority-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .mobile-toggle-btn,
    .mobile-detail-row {
        display: none;
    }

    @media (max-width: 575px) {
        .staff-stat-card .stat-number {
            font-size: 1.7rem;
        }

        .staff-stat-card {
            min-height: 95px;
            padding: 18px 16px;
        }

        .staff-panel .card-body {
            padding: 14px;
        }

        .attendance-overview-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .staff-table {
            min-width: 100%;
        }

        .staff-table thead th:not(:first-child):not(:last-child),
        .staff-row-summary td:not(:first-child):not(:last-child) {
            display: none;
        }

        .staff-table thead th:first-child {
            width: 44px;
            padding: 12px 8px;
        }

        .staff-table thead th:last-child,
        .staff-row-summary td:last-child {
            display: table-cell;
            width: 52px;
            text-align: right;
            padding: 10px 8px;
        }

        .staff-row-summary td {
            padding: 12px 8px;
            white-space: normal;
        }

        .staff-row-summary td:first-child {
            width: 44px;
            color: #0f172a;
            font-weight: 600;
        }

        .mobile-toggle-btn {
            width: 28px;
            height: 28px;
            border: 0;
            border-radius: 50%;
            background: #ff9f43;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
            padding: 0;
            box-shadow: 0 6px 12px rgba(239, 68, 68, 0.18);
        }

        .mobile-toggle-btn .toggle-minus {
            display: none;
        }

        .mobile-toggle-btn[aria-expanded="true"] .toggle-plus {
            display: none;
        }

        .mobile-toggle-btn[aria-expanded="true"] .toggle-minus {
            display: inline;
        }

        .mobile-detail-row.is-open {
            display: table-row;
        }

        .mobile-detail-row td {
            padding: 0 0 12px;
            border-bottom: 1px solid #eef2f7;
        }

        .mobile-detail-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border-bottom: 1px solid #eef2f7;
            font-size: 12px;
        }

        .mobile-detail-item:last-child {
            border-bottom: 0;
        }

        .mobile-detail-label {
            color: #0f172a;
            font-weight: 600;
        }

        .mobile-detail-value {
            color: #475569;
            text-align: right;
            word-break: break-word;
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
        .db-recent-row:hover {
            background-color: #f8fafc;
        }
    }
</style>

<div class="content db-page">
    <div class="page-header">
        <div class="page-title">
            <h4>Welcome, {{ ucwords($user->name) }} 👋</h4>
        </div>
    </div>

    <div class="attendance-overview">
        <div class="attendance-overview-head">
            <div>
                <h5 class="attendance-overview-title">Today's Attendance</h5>
                <div class="attendance-overview-sub">
                    {{ $todayAttendance ? 'Your attendance is recorded for today.' : 'No attendance record found for today.' }}
                    @if ($todayLeave)
                        You are on approved leave today.
                    @endif
                </div>
            </div>
            <span class="status-badge badge-{{ strtolower(str_replace(' ', '-', $todayStatusLabel)) }}">
                {{ $todayStatusLabel }}
            </span>
        </div>

        <div class="attendance-overview-grid">
            <div class="attendance-pill">
                <div class="attendance-pill-label">Check-in</div>
                <div class="attendance-pill-value">{{ $todayCheckInTime ?? '--' }}</div>
            </div>
            <div class="attendance-pill">
                <div class="attendance-pill-label">Check-out</div>
                <div class="attendance-pill-value">{{ $todayCheckOutTime ?? '--' }}</div>
            </div>
            <div class="attendance-pill">
                <div class="attendance-pill-label">Working Time</div>
                <div class="attendance-pill-value timer" data-live-timer="{{ $todayIsWorking ? 1 : 0 }}" data-checkin-ts="{{ $todayCheckInTimestamp ?? '' }}">{{ $todayWorkingHours }}</div>
                <div class="attendance-pill-progress">
                    <span id="todayWorkingProgress" style="width: {{ $todayWorkingProgress }}%;"></span>
                </div>
            </div>
            <div class="attendance-pill">
                <div class="attendance-pill-label">This Month</div>
                <div class="attendance-pill-value">{{ fmod((float) $totalPresent, 1.0) === 0.0 ? number_format($totalPresent, 0) : number_format($totalPresent, 1) }} present</div>
                <div class="attendance-overview-sub">{{ $totalLeave }} leave days</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6 col-md-3">
            <div class="staff-stat-card card-attendance">
                <div>
                    <div class="stat-number">
                        {{ fmod((float) $totalPresent, 1.0) === 0.0 ? number_format($totalPresent, 0) : number_format($totalPresent, 1) }}
                    </div>
                    <div class="stat-label">Total Attendance</div>
                    <div class="stat-sub">{{ $monthName }}</div>
                </div>
                <i class="fas fa-calendar-check stat-icon"></i>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="staff-stat-card card-leave">
                <div>
                    <div class="stat-number">{{ $totalLeave }}</div>
                    <div class="stat-label">Total Leave</div>
                    <div class="stat-sub">{{ $monthName }}</div>
                </div>
                <i class="fas fa-user-times stat-icon"></i>
            </div>
        </div>

        {{-- <div class="col-6 col-md-3">
            <div class="staff-stat-card card-meeting">
                <div>
                    <div class="stat-number">{{ $totalMeetings }}</div>
                    <div class="stat-label">Total Meetings</div>
                    <div class="stat-sub">Assigned to me</div>
                </div>
                <i class="fas fa-handshake stat-icon"></i>
            </div>
        </div> --}}
        @if(app('hasPermission')(2, 'view'))
        <div class="col-6 col-md-3">
            <div class="staff-stat-card" style="background: #00ace6;">
                <div>
                    <div class="stat-number" style="font-size: 1.8rem; color: #fff;">
                        @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($totalSalesAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                    </div>
                    <div class="stat-label" style="color: #fff;">Total Sales Volume</div>
                    <div class="stat-sub" style="color: #fff;">2026</div>
                </div>
                <i class="fas fa-chart-line stat-icon" style="color: #ff9f43; opacity: 0.2;"></i>
            </div>
        </div>
        @endif

        @if(app('hasPermission')(3, 'view'))
        <div class="col-6 col-md-3">
            <div class="staff-stat-card" style="background: #1e1e2d;">
                <div>
                    <div class="stat-number" style="font-size: 1.8rem; color: #fff;">
                        @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($totalPurchaseAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                    </div>
                    <div class="stat-label" style="color: #fff;">Total Purchase Volume</div>
                    <div class="stat-sub" style="color: #fff;">2026</div>
                </div>
                <i class="fas fa-shopping-cart stat-icon" style="color: #ff9f43; opacity: 0.2;"></i>
            </div>
        </div>
        @endif

        {{-- <div class="col-6 col-md-3">
            <div class="staff-stat-card card-meeting">
                <div>
                    <div class="stat-number">{{ $totalMeetings }}</div>
                    <div class="stat-label">Total Meetings</div>
                    <div class="stat-sub">Assigned to me</div>
                </div>
                <i class="fas fa-handshake stat-icon"></i>
            </div>
        </div> --}}
    </div>

    {{-- Follow-ups and Meetings Sections (Currently Disabled) --}}
    @if(false)
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card staff-panel">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-phone-alt text-primary"></i> My Follow-ups
                    </div>

                    @if ($recentFollowUps->isEmpty())
                        <div class="staff-empty-state">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No follow-ups assigned to you yet.
                        </div>
                    @else
                        <div class="staff-table-wrap">
                            <table class="table staff-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer Name</th>
                                        <th>Purpose</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Date &amp; Time</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                   @foreach ($recentFollowUps as $i => $fu)
                                        <tr class="staff-row-summary">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $fu->subject_name ?? '—' }}</td>
                                            <td>{{ $fu->purpose }}</td>
                                            <td>
                                                <span class="priority-badge badge-{{ strtolower($fu->priority) }}">
                                                    {{ $fu->priority }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge badge-{{ strtolower($fu->status) }}">
                                                    {{ $fu->status }}
                                                </span>
                                            </td>
                                            <td>{{ $fu->follow_up_datetime ? $fu->follow_up_datetime->format('d M Y, h:i A') : '—' }}</td>
                                            <td>
                                                <button type="button" class="mobile-toggle-btn" aria-expanded="false">
                                                    <span class="toggle-plus">+</span>
                                                    <span class="toggle-minus">-</span>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="mobile-detail-row">
                                            <td colspan="7">
                                                <div class="mobile-detail-card">
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Customer Name</span>
                                                        <span class="mobile-detail-value">{{ $fu->subject_name ?? '—' }}</span>
                                                    </div>
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Purpose</span>
                                                        <span class="mobile-detail-value">{{ $fu->purpose }}</span>
                                                    </div>
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Priority</span>
                                                        <span class="mobile-detail-value">
                                                            <span class="priority-badge badge-{{ strtolower($fu->priority) }}">
                                                                {{ $fu->priority }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Status</span>
                                                        <span class="mobile-detail-value">
                                                            <span class="status-badge badge-{{ strtolower($fu->status) }}">
                                                                {{ $fu->status }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Date &amp; Time</span>
                                                        <span class="mobile-detail-value">{{ $fu->follow_up_datetime ? $fu->follow_up_datetime->format('d M Y, h:i A') : '—' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach 
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card staff-panel">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-handshake text-success"></i> My Meetings
                    </div>

                   @if ($recentMeetings->isEmpty())
                        <div class="staff-empty-state">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No meetings assigned to you yet.
                        </div>
                    @else
                        <div class="staff-table-wrap">
                            <table class="table staff-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Customer Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Scheduled On</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentMeetings as $i => $meeting)
                                        <tr class="staff-row-summary">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $meeting->meeting_title }}</td>
                                            <td>{{ $meeting->customer->name ?? '—' }}</td>
                                            <td>{{ $meeting->meeting_type }}</td>
                                            <td>
                                                <span class="status-badge badge-{{ strtolower($meeting->status) }}">
                                                    {{ $meeting->status }}
                                                </span>
                                            </td>
                                            <td>{{ $meeting->scheduled_on ? $meeting->scheduled_on->format('d M Y, h:i A') : '—' }}</td>
                                            <td>
                                                <button type="button" class="mobile-toggle-btn" aria-expanded="false">
                                                    <span class="toggle-plus">+</span>
                                                    <span class="toggle-minus">-</span>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="mobile-detail-row">
                                            <td colspan="7">
                                                <div class="mobile-detail-card">
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Title</span>
                                                        <span class="mobile-detail-value">{{ $meeting->meeting_title }}</span>
                                                    </div>
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Customer Name</span>
                                                        <span class="mobile-detail-value">{{ $meeting->customer->name ?? '—' }}</span>
                                                    </div>
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Type</span>
                                                        <span class="mobile-detail-value">{{ $meeting->meeting_type }}</span>
                                                    </div>
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Status</span>
                                                        <span class="mobile-detail-value">
                                                            <span class="status-badge badge-{{ strtolower($meeting->status) }}">
                                                                {{ $meeting->status }}
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="mobile-detail-item">
                                                        <span class="mobile-detail-label">Scheduled On</span>
                                                        <span class="mobile-detail-value">{{ $meeting->scheduled_on ? $meeting->scheduled_on->format('d M Y, h:i A') : '—' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($totalMeetings > 10)
                            <div class="text-end mt-3">
                                <a href="{{ route('meeting.list') }}" class="btn btn-sm btn-outline-success">
                                    View All Meetings <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="row">
        @if(app('hasPermission')(2, 'view'))
        <div class="col-md-6 col-12">
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
                                @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($totalSalesAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                            </div>
                        </div>
                        <div style="background-color: rgba(255, 159, 67, 0.2); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line" style="color: #ff9f43; font-size: 20px;"></i>
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
                    <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0;">
                        <div style="margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                <span style="color: #667085;">Completed</span>
                                <span style="color: #1f2937;">@if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($completedSalesAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif <span style="color: #999; margin-left: 5px;">({{ $completedSalesCount ?? 0 }})</span></span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                <div class="progress-bar" style="width: {{ $salesInvoiceCount > 0 ? round(($completedSalesCount / $salesInvoiceCount) * 100) : 0 }}%; background-color: #28c76f; border-radius: 3px;"></div>
                            </div>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                <span style="color: #667085;">Partially Paid</span>
                                <span style="color: #1f2937;">@if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($partiallyPaidSalesAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif <span style="color: #999; margin-left: 5px;">({{ $partiallyPaidSalesCount ?? 0 }})</span></span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                <div class="progress-bar" style="width: {{ $salesInvoiceCount > 0 ? round(($partiallyPaidSalesCount / $salesInvoiceCount) * 100) : 0 }}%; background-color: #00cfe8; border-radius: 3px;"></div>
                            </div>
                        </div>
                        <div style="margin-bottom: 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                <span style="color: #667085;">Pending</span>
                                <span style="color: #1f2937;">@if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($pendingSalesAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif <span style="color: #999; margin-left: 5px;">({{ $pendingSalesCount ?? 0 }})</span></span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                <div class="progress-bar" style="width: {{ $salesInvoiceCount > 0 ? round(($pendingSalesCount / $salesInvoiceCount) * 100) : 0 }}%; background-color: #ff9f43; border-radius: 3px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Latest Sale Entry -->
                    @if(isset($latestSales) && $latestSales->isNotEmpty())
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
                    @else
                        <div style="text-align: center; padding: 20px;">No sales found.</div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if(app('hasPermission')(3, 'view'))
        <div class="col-md-6 col-12">
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
                                @if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($totalPurchaseAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif
                            </div>
                        </div>
                        <div style="background-color: rgba(255, 159, 67, 0.2); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shopping-cart" style="color: #ff9f43; font-size: 20px;"></i>
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
                    <div style="background-color: white; border-radius: 8px; padding: 15px; border: 1px solid #f0f0f0;">
                        <div style="margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                <span style="color: #667085;">Completed</span>
                                <span style="color: #1f2937;">@if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($completedPurchaseAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif <span style="color: #999; margin-left: 5px;">({{ $completedPurchaseCount ?? 0 }})</span></span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                <div class="progress-bar" style="width: {{ $purchaseInvoiceCount > 0 ? round(($completedPurchaseCount / $purchaseInvoiceCount) * 100) : 0 }}%; background-color: #28c76f; border-radius: 3px;"></div>
                            </div>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                <span style="color: #667085;">Partially Paid</span>
                                <span style="color: #1f2937;">@if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($partiallyPaidPurchaseAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif <span style="color: #999; margin-left: 5px;">({{ $partiallyPaidPurchaseCount ?? 0 }})</span></span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                <div class="progress-bar" style="width: {{ $purchaseInvoiceCount > 0 ? round(($partiallyPaidPurchaseCount / $purchaseInvoiceCount) * 100) : 0 }}%; background-color: #00cfe8; border-radius: 3px;"></div>
                            </div>
                        </div>
                        <div style="margin-bottom: 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">
                                <span style="color: #667085;">Pending</span>
                                <span style="color: #1f2937;">@if ($currencyPosition === 'left'){{ $currencySymbol }}@endif{{ number_format($pendingPurchaseAmount ?? 0, 2) }}@if ($currencyPosition === 'right'){{ $currencySymbol }}@endif <span style="color: #999; margin-left: 5px;">({{ $pendingPurchaseCount ?? 0 }})</span></span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px; background-color: #f3f3f3;">
                                <div class="progress-bar" style="width: {{ $purchaseInvoiceCount > 0 ? round(($pendingPurchaseCount / $purchaseInvoiceCount) * 100) : 0 }}%; background-color: #ff9f43; border-radius: 3px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Latest Purchase Entry -->
                    @if(isset($latestPurchases) && $latestPurchases->isNotEmpty())
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
                                <p style="color: {{ $purchaseStatusColor }}; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-bottom: 0; letter-spacing: 0.5px;">
                                    {{ $latestPurchaseItem->purchase_status ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        </a>
                    @else
                        <div style="text-align: center; padding: 20px;">No purchases found.</div>
                    @endif
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
            @if (app('hasPermission')(2, 'view'))
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
            @if (app('hasPermission')(3, 'view'))
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
            

            @if (app('hasPermission')(2, 'view'))
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
            @if (app('hasPermission')(3, 'view'))
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
            @if (app('hasPermission')(2, 'view'))
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

</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const LIVE_BREAK_SECONDS = 30 * 60;
        const STANDARD_SECONDS = 8.5 * 3600;

        function formatSeconds(totalSeconds) {
            totalSeconds = Math.max(0, Math.floor(totalSeconds));
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }

        function tickAttendanceTimer() {
            const now = Math.floor(Date.now() / 1000);
            document.querySelectorAll('[data-live-timer="1"]').forEach(function (el) {
                const checkinTs = parseInt(el.dataset.checkinTs || '', 10);
                if (!checkinTs) return;

                const elapsed = Math.max(0, now - checkinTs);
                const worked = Math.max(0, elapsed - (elapsed >= LIVE_BREAK_SECONDS ? LIVE_BREAK_SECONDS : 0));
                el.textContent = formatSeconds(worked);

                const progress = Math.min(100, Math.round((worked / STANDARD_SECONDS) * 100));
                const progressBar = document.getElementById('todayWorkingProgress');
                if (progressBar) {
                    progressBar.style.width = progress + '%';
                }
            });
        }

        document.querySelectorAll('.mobile-toggle-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const summaryRow = button.closest('tr');
                const detailRow = summaryRow ? summaryRow.nextElementSibling : null;

                if (!detailRow || !detailRow.classList.contains('mobile-detail-row')) {
                    return;
                }

                const isOpen = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                detailRow.classList.toggle('is-open', !isOpen);
            });
        });

        tickAttendanceTimer();
        setInterval(tickAttendanceTimer, 1000);
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


        $(document).ready(function() {
            @if (app('hasPermission')(2, 'view'))
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

            @if (app('hasPermission')(3, 'view'))
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
        });

</script>
@endpush
@endsection
