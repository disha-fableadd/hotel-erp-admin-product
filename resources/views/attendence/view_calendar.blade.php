@extends('layout.app')
@section('title', 'Calender View')
@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .capitalize-text {
        text-transform: capitalize;
    }

    .attendance-cell {
        cursor: pointer;
    }

    .bg-saturday-off {
        background-color: #fff3cd;
    }

    .calendar-container {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background-color: #dee2e6;
        border: 1px solid #dee2e6;
    }

    .calendar-day {
        background-color: white;
        min-height: 120px;
        max-height: 200px;
        padding: 8px;
        position: relative;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .calendar-day-header {
        font-weight: bold;
        margin-bottom: 8px;
        padding-bottom: 4px;
        border-bottom: 1px solid #e9ecef;
    }

    .calendar-day-number {
        font-size: 14px;
        color: #333;
    }

    .calendar-day-name {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
    }

    .calendar-day.sunday {
        background-color: #f8d7da;
    }

    .calendar-day.saturday-off {
        background-color: #fff3cd;
    }

    .calendar-day.holiday {
        background-color: #d1ecf1;
    }

    .calendar-day.today {
        border: 2px solid #FF9F43;
    }

    .calendar-day.future {
        background-color: #f8f9fa;
    }

    .employee-attendance-item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 2px 4px;
        margin-bottom: 4px;
        font-size: 11px;
        border-radius: 3px;
        background-color: #f8f9fa;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .employee-attendance-item img {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        object-fit: cover;
    }

    .attendance-status {
        font-weight: bold;
        font-size: 10px;
    }

    .attendance-status.present {
        color: #28a745;
    }

    .attendance-status.absent {
        color: #dc3545;
    }

    .attendance-status.half-day {
        color: #ffc107;
    }

    .attendance-status.working {
        color: #007bff;
    }

    .attendance-status.late {
        color: #1B2850;
    }

    .weekday-header {
        background-color: #000;
        color: white;
        padding: 10px;
        text-align: center;
        font-weight: bold;
        font-size: 12px;
        text-transform: uppercase;
    }

    .holiday-label {
        background-color: #17a2b8;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        margin-bottom: 4px;
        display: block;
    }

    .saturday-off-label {
        background-color: #ffc107;
        color: #000;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        margin-bottom: 4px;
        display: block;
    }

    .sunday-label {
        background-color: #dc3545;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        margin-bottom: 4px;
        display: block;
    }

    .overtime-badge {
        background-color: #ffc107;
        color: #000;
        padding: 1px 3px;
        border-radius: 2px;
        font-size: 8px;
        margin-left: 4px;
    }

    .calendar-scroll {
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Mobile Horizontal Scroll Calendar Styles */
    .mobile-date-scroll {
        display: block;
        margin-bottom: 20px;
    }

    .date-scroll-container {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 15px 0;
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
        border-color: #007bff;
        background: #e7f3ff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
    }

    .date-card.today {
        border-color: #28a745;
    }

    .date-card.sunday {
        background-color: #ffe5e5;
    }

    .date-card.saturday-off {
        background-color: #fff8e1;
    }

    .date-card.holiday {
        background-color: #e0f7fa;
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

    /* .date-card-month {
        font-size: 11px;
        color: #888;
    } */

    .date-card-label {
        margin-top: 5px;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
    }

    .date-card-label.holiday {
        background-color: #17a2b8;
        color: white;
    }

    .date-card-label.saturday-off {
        background-color: #ffc107;
        color: #000;
    }

    .date-card-label.sunday {
        background-color: #dc3545;
        color: white;
    }

    .mobile-attendance-list {
        margin-top: 20px;
    }

    .mobile-employee-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mobile-employee-card img {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
    }

    .mobile-employee-info {
        flex: 1;
    }

    .mobile-employee-name {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .mobile-employee-times {
        font-size: 12px;
        color: #666;
    }

    .mobile-employee-status {
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: bold;
    }

    .mobile-employee-status.present {
        background-color: #28a745;
        color: white;
    }

    .mobile-employee-status.absent {
        background-color: #dc3545;
        color: white;
    }

    .mobile-employee-status.half-day {
        background-color: #ffc107;
        color: #000;
    }

    .mobile-employee-status.working {
        background-color: #007bff;
        color: white;
    }

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

    /* Button Styles */
    .btn {
        font-size: 13px;
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 5px;
        text-align: center;
        display: inline-block;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-family: inherit;
        white-space: nowrap;
    }

    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-color: #1B2850;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #1B2850;
        color: white;
    }

    .hr-btnbg {
        background-color: #FF9F43 !important;
        color: white !important;
        border-radius: 8px !important;
        padding: 10px 24px !important;
        font-weight: 600 !important;
        border: none !important;
        box-shadow: 0 4px 6px -1px rgba(230, 97, 54, 0.2), 0 2px 4px -1px rgba(230, 97, 54, 0.1) !important;
        transition: all 0.2s ease !important;
    }

    .hr-btnbg:hover {
        background-color: #FF9F43 !important;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(230, 97, 54, 0.3), 0 4px 6px -2px rgba(230, 97, 54, 0.15) !important;
    }

    /* View Toggle Buttons */
    .view-toggle-container {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        justify-content: center;
    }

    .view-toggle-btn {
        padding: 8px 20px;
        border: 2px solid #FF9F43;
        background: white;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .view-toggle-btn.active {
        background: #FF9F43;
        color: white;
    }

    .view-toggle-btn:hover {
        background: #FF9F43;
        color: white;
        border-color: #FF9F43;
    }

    .calendar-view-section {
        margin-bottom: 20px;
    }

    .view-section-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .card-title {
            font-size: 18px !important;
        }

        .btn {
            font-size: 13px;
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 5px;
            white-space: nowrap;
        }

        .btn-secondary {
            background-color: #1B2850;
            color: white;
        }

        .hr-btnbg {
            background-color: #FF9F43;
            color: white;
        }

        .view-toggle-btn {
            padding: 6px 12px;
            font-size: 12px;
        }

        .view-toggle-btn i {
            font-size: 11px;
        }

        /* Hide Legend section on mobile */
        #legend-section,
        .collapsible-toggle[onclick*="legend"] {
            display: none !important;
        }

        /* Statistics Cards - Display in 2x2 grid on mobile */
        .row.mb-4 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            /* gap: 10px; */
            margin-bottom: 12px;
        }

        .row.mb-4 .col-md-3 {
            margin-bottom: 0;
            padding-left: 5px;
            padding-right: 5px;
        }

        .row.mb-4 .card {
            margin-bottom: 0;
        }

        .row.mb-4 .card-body {
            padding: 12px 10px;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .row.mb-4 .card-body h6 {
            font-size: 12px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .row.mb-4 .card-body h3 {
            font-size: 24px;
            margin-bottom: 0;
            font-weight: bold;
            line-height: 1.2;
        }

        /* Legend - Smaller text and wrap */
        .d-flex.flex-wrap.gap-3 {
            gap: 6px !important;
        }

        .d-flex.flex-wrap.gap-3 p {
            font-size: 10px;
            margin-bottom: 3px !important;
        }

        .d-flex.flex-wrap.gap-3 p .fw-bold {
            font-size: 9px;
        }

        /* Reduce spacing between sections on mobile */
        .row.mb-3 {
            margin-bottom: 10px !important;
        }

        /* Filter Section - Stack vertically on mobile */
        .row.mb-3 .col-md-4,
        .row.mb-3 .col-md-6 {
            margin-bottom: 10px;
        }

        .row.mb-3 .d-flex.gap-2 {
            flex-wrap: wrap;
        }

        .row.mb-3 label {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .row.mb-3 .form-control,
        .row.mb-3 select {
            font-size: 12px;
            padding: 6px;
        }
    }

    /* Tablet Styles */
    @media (min-width: 769px) and (max-width: 1024px) {
        .calendar-day {
            min-height: 100px;
            max-height: 150px;
            padding: 6px;
        }

        .calendar-day-number {
            font-size: 12px;
        }

        .calendar-day-name {
            font-size: 10px;
        }

        .employee-attendance-item {
            font-size: 10px;
            padding: 2px 3px;
        }

        .employee-attendance-item img {
            width: 18px;
            height: 18px;
        }

        .weekday-header {
            padding: 8px;
            font-size: 11px;
        }
    }

    /* Small Mobile (Portrait) */
    @media (max-width: 480px) {
        .card-body {
            padding: 10px;
        }

        /* Title and buttons container - stack vertically */
        .d-flex.justify-content-between.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 10px;
        }

        /* Buttons container - keep side by side */
        .d-flex.justify-content-between.align-items-center>div {
            width: 100%;
            display: flex;
            gap: 10px;
        }

        .d-flex.justify-content-between.align-items-center .btn {
            flex: 1;
            margin-bottom: 0;
            font-size: 13px;
            padding: 8px 16px;
            font-weight: 500;
        }

        .d-flex.justify-content-between.align-items-center .btn.me-2 {
            margin-right: 0 !important;
        }

        /* Keep statistics cards in 2x2 grid on small mobile too */
        .row.mb-4 {
            grid-template-columns: repeat(2, 1fr);
            /* gap: 8px; */
        }

        .row.mb-4 .card-body {
            padding: 10px 8px;
            min-height: 75px;
        }

        .row.mb-4 .card-body h6 {
            font-size: 11px;
            margin-bottom: 6px;
        }

        .row.mb-4 .card-body h3 {
            font-size: 22px;
        }

        /* Calendar extends to edges on small mobile too */
        .card>.card-body {
            padding-left: 10px;
            padding-right: 10px;
        }

        .calendar-scroll {
            width: calc(100% + 20px);
            margin-left: -10px;
            margin-right: -10px;
            padding: 0;
        }

        .calendar-container {
            width: 100%;
        }

        .calendar-day {
            min-height: 70px;
            max-height: 100px;
            padding: 1px;
        }

        .weekday-header {
            padding: 3px 1px;
            font-size: 8px;
        }

        .calendar-day-number {
            font-size: 9px;
        }

        .calendar-day-name {
            font-size: 7px;
        }

        .employee-attendance-item {
            font-size: 7px;
            padding: 1px;
        }

        .employee-attendance-item img {
            width: 10px;
            height: 10px;
        }

        .attendance-status {
            font-size: 7px;
        }

        .holiday-label,
        .saturday-off-label,
        .sunday-label {
            font-size: 6px;
            padding: 1px;
        }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
        .calendar-container {
            grid-template-columns: repeat(7, 1fr);
        }

        .weekday-header {
            display: block;
            padding: 6px;
            font-size: 10px;
        }

        .calendar-day {
            min-height: 80px;
            max-height: 120px;
            padding: 4px;
        }

        .calendar-day-header {
            display: block;
            margin-bottom: 4px;
            padding-bottom: 4px;
        }

        .calendar-day-number {
            font-size: 12px;
        }

        .calendar-day-name {
            font-size: 9px;
        }

        .employee-attendance-item {
            font-size: 9px;
            padding: 2px 3px;
            margin-bottom: 2px;
        }

        .employee-attendance-item img {
            width: 16px;
            height: 16px;
        }
    }
    @media (max-width: 768px) {
    
    .page-header {
        display: flex;
        flex-direction: column;
        align-items: flex-start; /* 👈 LEFT align title */
        gap: 10px;
    }

    .page-title h4 {
        text-align: left; /* 👈 ensure not centered */
        width: 100%;
    }

    .page-btn {
        width: 100%;
        display: flex;
        gap: 10px;
    }

    .page-btn .btn {
        flex: 1;
        text-align: center;
    }
}
</style>
 <div class="content">
        <!-- <div class="page-header">
            <div class="page-title">
                <h4>Attendance Calendar</h4>
            </div>
            <div class="page-btn">
                <a href="/view" class="btn btn-secondary me-2">
                            List View
                        </a>
                        <a href="/attendence" class="btn hr-btnbg">
                            Manage Attendance
                        </a>
            </div>
        </div> -->
        <div class="page-header">
    <div class="page-title">
        <h4>Attendance Calendar</h4>
    </div>
    <div class="page-btn">
        <a href="/view" class="btn btn-secondary me-2">
            List View
        </a>
        <a href="/attendence" class="btn hr-btnbg">
            Manage Attendance
        </a>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <!-- Statistics - Only show for HR/Admin users -->
                <?php if (isset($role) && ($role === 'hr' || $role === 'admin')): ?>
                    <div class="collapsible-toggle collapsed" onclick="toggleSection('statistics')">
                        <i class="fas fa-chevron-down"></i>
                        <strong>Statistics</strong>
                    </div>
                    <div id="statistics-section" class="collapsible-content collapsed">
                        <div class="row mb-4">
                            <!-- <div class="col-lg-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6>Total Employees</h6>
                                    <h3 id="total-employees"><?= $totalEmployees ?? 0 ?></h3>
                                </div>
                            </div>
                        </div> -->
                            <div class="col-lg-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6>Present Today</h6>
                                        <h3 id="present-employees"><?= $presentEmployees ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6>Absent Today</h6>
                                        <h3 id="absent-employees"><?= $absentEmployees ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-lg-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6>Working Days</h6>
                                    <h3><?= $workingDays ?? 0 ?></h3>
                                </div>
                            </div>
                        </div> -->
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Legend -->
                <div class="collapsible-toggle collapsed" onclick="toggleSection('legend')">
                    <i class="fas fa-chevron-down"></i>
                    <strong>Legend</strong>
                </div>
                <div id="legend-section" class="collapsible-content collapsed">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-3">
                                <p class="me-3 mb-2"><span
                                        class="text-success fw-bold">P</span><b>&nbsp;:&nbsp;Present</b></p>
                                <p class="me-3 mb-2"><span
                                        class="text-danger fw-bold">A</span><b>&nbsp;:&nbsp;Absent</b></p>
                                <p class="me-3 mb-2"><span
                                        class="text-warning fw-bold">H</span><b>&nbsp;:&nbsp;Half-day</b></p>
                                <p class="me-3 mb-2"><span class="text-primary fw-bold"
                                        style="cursor:pointer">W</span><b>&nbsp;:&nbsp;Working</b></p>
                                <p class="me-3 mb-2"><span class="text-success fw-bold">P (L)</span> OR
                                    <span class="text-warning fw-bold">H (L)</span> OR
                                    <span class="text-primary fw-bold">W (L)</span><b>&nbsp;:&nbsp;Late</b>
                                </p>
                                <p class="me-3 mb-2"><span class="text-danger fw-bold">Sunday</b></p>
                                <p class="me-3 mb-2"><span class="text-info fw-bold">Holiday</b></p>
                                <p class="me-3 mb-2"><span class="bg-saturday-off text-dark fw-bold">Saturday Off</b>
                                </p>
                                <p class="me-3 mb-2"><span class="badge bg-warning text-dark"
                                        style="font-size: 10px;">OT</span><b>&nbsp;:&nbsp;Overtime</b></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters - Collapsible Section -->
                <div class="collapsible-toggle collapsed" onclick="toggleSection('filters')">
                    <i class="fas fa-chevron-down"></i>
                    <strong>Filters</strong>
                </div>
                <div id="filters-section" class="collapsible-content collapsed">
                    <div class="row px-1 mb-3">

                        <!-- Month -->
                        <div class="col-12 col-md-2 mb-2">
                            <label class="form-label">Month</label>
                            <select id="month-select" class="form-select select2 select-month">
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

                        <!-- Year -->
                        <div class="col-12 col-md-2 mb-2">
                            <label class="form-label">Year</label>
                            <select id="year-select" class="form-select select2 select-year">
                                <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Employee -->
                        <?php if (isset($role) && ($role === 'hr' || $role === 'admin')): ?>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="form-label">Staff</label>
                                <select id="user-filter" class="form-select select2 select-user">
                                    <option value="">Select Staff</option>
                                </select>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>


                <!-- View Toggle Buttons -->
                <div class="view-toggle-container">
                    <button class="view-toggle-btn active" onclick="toggleView('horizontal', event)">
                        <i class="fas fa-stream"></i> Date View
                    </button>
                    <button class="view-toggle-btn" onclick="toggleView('calendar', event)">
                        <i class="fas fa-calendar-alt"></i> Calendar View
                    </button>
                </div>

                <!-- Horizontal Date Scroll View -->
                <div id="horizontal-view" class="calendar-view-section mobile-date-scroll">
                    <div id="date-scroll-container" class="date-scroll-container">
                        <!-- Horizontal scrolling dates will be rendered here -->
                    </div>

                    <div id="mobile-attendance-list" class="mobile-attendance-list">
                        <!-- Employee attendance cards for selected date will be rendered here -->
                    </div>
                </div>

                <!-- Calendar Grid View -->
                <div id="calendar-view" class="calendar-view-section desktop-calendar" style="display: none;">
                    <div class="view-section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Calendar Grid View
                    </div>
                    <div class="calendar-scroll">
                        <div id="calendar-container" class="calendar-container">
                            <!-- Calendar will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="attendanceEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">Attendance Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="absent">Absent</option>
                        <option value="present">Present</option>
                        <option value="half-day">Half Day</option>
                    </select>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Check In</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Check In</th>
                                    <th>Check In Location</th>
                                    <th>Check Out</th>
                                    <th>Check Out Location</th>
                                    <th>Duration</th>
                                    <th>Overtime</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-modal-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-end gap-2">
                <button class="btn hr-btnbg" data-bs-dismiss="modal">Back</button>
                <button class="btn hr-btnbg" onclick="saveAttendanceEdits()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="attendanceBulkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Date Range -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">From Date</label>
                        <input type="date" id="from_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">To Date</label>
                        <input type="date" id="to_date" class="form-control">
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="fw-bold">Attendance Status</label>
                    <select id="bulk_status" class="form-select">
                        <option value="present">Present</option>
                        <option value="half-day">Half Day</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>

                <!-- Time -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Check In Time</label>
                        <input type="time" id="check_in_time" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Check Out Time</label>
                        <input type="time" id="check_out_time" class="form-control">
                    </div>
                </div>

                <div class="alert alert-info small">
                    ⚠️ This action will update attendance for all dates between selected range.
                </div>
            </div>

            <div class="modal-footer justify-content-end gap-2">
                <button class="btn hr-btnbg" data-bs-dismiss="modal">Back</button>
                <button class="btn hr-btnbg" onclick="saveBulkAttendance()">Save Changes</button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('js')
<script>
    /**
     * resolveAttendance(records, dateStr)
     * Merges ALL attendance records for a given date into one summary object.
     *
     * Why: The DB can store multiple rows per day (e.g. two check-in sessions).
     * The API groups them, but in edge cases duplicate rows can slip through.
     * This helper always picks:
     *   - Earliest check_in_time  (first arrival)
     *   - Latest  check_out_time  (last departure, or null if still active)
     *   - Highest-rank status     (present > half-day > leave > absent)
     *   - Max is_late / late_minutes
     */
    function resolveAttendance(records, dateStr) {
        const matches = records.filter(r => {
            const d = r.date ? String(r.date).substring(0, 10) : '';
            return d === dateStr;
        });

        if (matches.length === 0) return null;
        if (matches.length === 1) return matches[0];

        // Merge multiple records for the same date
        const statusRank = { 'present': 3, 'half-day': 2, 'leave': 1, 'absent': 0 };

        return matches.reduce((merged, r) => {
            const currentCheckInLocation = r.check_in_location_name || r.check_in_location || r.location_name || r.location || null;
            const currentCheckOutLocation = r.check_out_location_name || r.check_out_location || r.location_name || r.location || null;

            // Earliest check-in
            if (r.check_in_time && (!merged.check_in_time || r.check_in_time < merged.check_in_time)) {
                merged.check_in_time = r.check_in_time;
                merged.check_in_location_name = currentCheckInLocation;
            }
            // Latest check-out (null means still active — keep null only if NO record has a checkout)
            if (r.check_out_time) {
                if (!merged.check_out_time || r.check_out_time > merged.check_out_time) {
                    merged.check_out_time = r.check_out_time;
                    merged.check_out_location_name = currentCheckOutLocation;
                }
            }

            // Backfill location if the chosen earliest/latest session has blank location.
            if (!merged.check_in_location_name && currentCheckInLocation) {
                merged.check_in_location_name = currentCheckInLocation;
            }
            if (!merged.check_out_location_name && currentCheckOutLocation) {
                merged.check_out_location_name = currentCheckOutLocation;
            }
            // Highest status
            const rRank   = statusRank[String(r.status || '').toLowerCase()]   ?? 0;
            const mRank   = statusRank[String(merged.status || '').toLowerCase()] ?? 0;
            if (rRank > mRank) merged.status = r.status;

            // Max late
            if ((r.is_late || 0) > (merged.is_late || 0)) {
                merged.is_late     = r.is_late;
                merged.late_minutes = r.late_minutes;
            }

            // Best work_hours / overtime
            const toSec = (t) => { if (!t) return 0; const p = t.split(':').map(Number); return p[0]*3600 + p[1]*60 + (p[2]||0); };
            if (toSec(r.work_hours) > toSec(merged.work_hours)) merged.work_hours = r.work_hours;
            if (toSec(r.overtime)   > toSec(merged.overtime))   merged.overtime   = r.overtime;

            return merged;
        }, {
            date:           dateStr,
            check_in_time:  null,
            check_out_time: null,
            status:         'absent',
            is_late:        0,
            late_minutes:   0,
            work_hours:     '00:00:00',
            overtime:       '00:00:00',
            check_in_location_name: null,
            check_out_location_name: null,
        });
    }

    function formatTime12Hour(timeString) {
        if (!timeString) return '-';

        const parts = String(timeString).split(':');
        if (parts.length < 2) return timeString;

        const [hourStr, minuteStr, secondStr = '00'] = parts;
        let hour = parseInt(hourStr, 10);
        const minute = minuteStr.padStart(2, '0');
        const second = secondStr.padStart(2, '0');

        if (Number.isNaN(hour)) {
            return timeString;
        }

        const period = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;

        return `${hour.toString().padStart(2, '0')}:${minute}:${second} ${period}`;
    }

    function formatLocationName(locationName) {
        const normalized = (locationName || '').toString().trim();
        return normalized !== '' ? normalized : '-';
    }

    window.toggleSection = function (sectionName) {
        const section = document.getElementById(sectionName + '-section');
        const toggle = section.previousElementSibling;

        section.classList.toggle('collapsed');
        toggle.classList.toggle('collapsed');
    };
    $(document).ready(function () {
        $(".select-month, .select-year, .select-user").select2({
            tags: true,
        });
    });

    // Prevent ReferenceError before the real renderer is attached.
    window.renderDesktopCalendar = window.renderDesktopCalendar || function () { };

    window.toggleView = function (viewType, event) {
        const horizontalView = document.getElementById('horizontal-view');
        const calendarView = document.getElementById('calendar-view');
        const buttons = document.querySelectorAll('.view-toggle-btn');

        // Remove active class from all buttons
        buttons.forEach(btn => btn.classList.remove('active'));

        // Set active button based on the button clicked
        const clickedButton = event?.target?.closest('.view-toggle-btn');
        if (clickedButton) {
            clickedButton.classList.add('active');
        } else if (viewType === 'horizontal' && buttons[0]) {
            buttons[0].classList.add('active');
        } else if (viewType === 'calendar' && buttons[1]) {
            buttons[1].classList.add('active');
        }

        if (viewType === 'horizontal') {
            horizontalView.style.display = 'block';
            calendarView.style.display = 'none';
        } else if (viewType === 'calendar') {
            horizontalView.style.display = 'none';
            calendarView.style.display = 'block';
            // Re-render calendar when switching to calendar view
            window.renderDesktopCalendar();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const token = (typeof window.getAuthToken === 'function')
            ? window.getAuthToken()
            : (localStorage.getItem('authToken') || localStorage.getItem('token') || '');
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        let currentMonth = new Date().getMonth() + 1;
        let currentYear = new Date().getFullYear();
        let users = [];
        let holidays = [];
        let saturdayOffDates = [];
        const imageBasePath = "{{ rtrim(env('ImagePath', '/'), '/') }}";
        const defaultImagePath = `${imageBasePath}/admin/assets/img/customer/customer5.jpg`;
        const getProfileImageUrl = (profileImage) => {
            if (!profileImage) return defaultImagePath;
            const cleanPath = String(profileImage).replace(/^\/+/, '');
            if (/^(https?:)?\/\//i.test(cleanPath)) {
                return cleanPath;
            }
            if (cleanPath.startsWith('storage/') || cleanPath.startsWith('upload/')) {
                return `${imageBasePath}/${cleanPath}`;
            }
            return `${imageBasePath}/storage/${cleanPath}`;
        };
        let isMobile = window.innerWidth <= 768;
        let selectedDate = new Date().toISOString().split('T')[0];

        // Set current month/year
        document.getElementById('month-select').value = String(currentMonth).padStart(2, '0');
        document.getElementById('year-select').value = currentYear;

        // Check if mobile on resize
        window.addEventListener('resize', () => {
            const wasMobile = isMobile;
            isMobile = window.innerWidth <= 768;
            if (wasMobile !== isMobile) {
                renderCalendar();
            }
        });

        const populateUserFilter = () => {
            const userFilter = document.getElementById('user-filter');
            if (!userFilter) return; // Filter doesn't exist for employee users

            userFilter.innerHTML = '<option value="">Select Staff</option>';

            const sortedUsers = [...users].sort((a, b) =>
                a.employee_name.localeCompare(b.employee_name)
            );

            sortedUsers.forEach(user => {
                const option = document.createElement('option');
                option.value = user.user_id;
                option.textContent = user.employee_name;
                userFilter.appendChild(option);
            });
        };

        const loadAttendance = async (month, year) => {
            try {
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';
                const branchParam = selectedSubAdminId && selectedSubAdminId !== 'null'
                    ? `?selectedSubAdminId=${selectedSubAdminId}` : '';
                const response = await fetch(`/api/attendance/getAttendance/${month}/${year}${branchParam}`, { headers });
                const data = await response.json();

                if (data.status === 'success') {
                    users = data.data.users || [];
                    holidays = data.data.holidays || [];
                    saturdayOffDates = data.data.saturdayOffDates || [];
                    populateUserFilter();
                    renderCalendar();
                }
            } catch (error) {
                console.error('Error loading attendance:', error);
            }
        };

        const renderMobileDateScroll = () => {
            const todayDate = new Date().toISOString().split('T')[0];
            const selectedMonth = parseInt(document.getElementById('month-select').value);
            const selectedYear = parseInt(document.getElementById('year-select').value);
            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
            const dateScrollContainer = document.getElementById('date-scroll-container');
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            dateScrollContainer.innerHTML = '';

            // Show 2 days before today, today (active), and 1 day after
            const today = new Date();
            const currentDay = today.getDate();
            const currentMonthNum = today.getMonth() + 1;
            const currentYearNum = today.getFullYear();

            // Determine which days to show
            let displayDays = [];

            if (selectedMonth === currentMonthNum && selectedYear === currentYearNum) {
                // Current month - show 2 days before today, today, and tomorrow
                for (let i = -2; i <= 1; i++) {
                    const targetDate = new Date(today);
                    targetDate.setDate(today.getDate() + i);
                    const day = targetDate.getDate();
                    const month = targetDate.getMonth() + 1;
                    const year = targetDate.getFullYear();

                    if (month === selectedMonth && year === selectedYear && day >= 1 && day <= daysInMonth) {
                        displayDays.push(day);
                    }
                }
            } else {
                // Other months - show first 4 days
                displayDays = [1, 2, 3, 4];
            }

            // If we have fewer than 4 days, add more from the start of the month
            if (displayDays.length < 4) {
                for (let day = 1; day <= daysInMonth && displayDays.length < 4; day++) {
                    if (!displayDays.includes(day)) {
                        displayDays.push(day);
                    }
                }
            }

            displayDays.sort((a, b) => a - b);

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dateObj = new Date(selectedYear, selectedMonth - 1, day);
                const dayOfWeek = dateObj.getDay();
                const isToday = dateStr === todayDate;
                const isFuture = new Date(dateStr) > new Date(todayDate);

                const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const holiday = holidays.find(h => h.holiday_date === dateStr);

                const dateCard = document.createElement('div');
                dateCard.className = 'date-card';

                if (dateStr === selectedDate) dateCard.classList.add('active');
                if (isToday) dateCard.classList.add('today');
                if (holiday) dateCard.classList.add('holiday');
                else if (saturdayOffDates.includes(dateStr)) dateCard.classList.add('saturday-off');
                else if (dayOfWeek === 0) dateCard.classList.add('sunday');

                dateCard.innerHTML = `
                <div class="date-card-day">${weekdays[dayOfWeek]}</div>
                <div class="date-card-number">${day}</div>
                <!-- <div class="date-card-month">${monthNames[selectedMonth - 1]}</div> -->
            `;

                if (holiday) {
                    const label = document.createElement('div');
                    label.className = 'date-card-label holiday';
                    label.textContent = 'Holiday';
                    dateCard.appendChild(label);
                } else if (saturdayOffDates.includes(dateStr)) {
                    const label = document.createElement('div');
                    label.className = 'date-card-label saturday-off';
                    label.textContent = 'Sat Off';
                    dateCard.appendChild(label);
                } else if (dayOfWeek === 0) {
                    const label = document.createElement('div');
                    label.className = 'date-card-label sunday';
                    label.textContent = 'Sunday';
                    dateCard.appendChild(label);
                }

                dateCard.addEventListener('click', () => {
                    selectedDate = dateStr;
                    renderMobileDateScroll();
                    renderMobileAttendanceList();
                });

                dateScrollContainer.appendChild(dateCard);
            }

            // Scroll to active date
            setTimeout(() => {
                const activeCard = dateScrollContainer.querySelector('.date-card.active');
                if (activeCard) {
                    activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            }, 100);
        };

        const renderMobileAttendanceList = () => {
            const userFilterEl = document.getElementById('user-filter');
            const selectedUserId = userFilterEl ? userFilterEl.value : '';
            const mobileList = document.getElementById('mobile-attendance-list');
            const todayDate = new Date().toISOString().split('T')[0];

            // Filter users by dropdown selection
            let filteredUsers = users;
            if (selectedUserId) {
                filteredUsers = filteredUsers.filter(user => user.user_id == selectedUserId);
            }

            mobileList.innerHTML = '';

            const selectedDateObj = new Date(selectedDate);
            const dayOfWeek = selectedDateObj.getDay();
            const holiday = holidays.find(h => h.holiday_date === selectedDate);
            const isFuture = new Date(selectedDate) > new Date(todayDate);

            // Check if it's a non-working day
            // if (holiday || saturdayOffDates.includes(selectedDate) || dayOfWeek === 0 || isFuture)
            if (holiday || saturdayOffDates.includes(selectedDate) || isFuture){
                mobileList.innerHTML = `
                <div class="alert alert-info text-center">
                    ${holiday ? `<strong>Holiday:</strong> ${holiday.title}` :
                        saturdayOffDates.includes(selectedDate) ? '<strong>Saturday Off</strong>' :
                            dayOfWeek === 0 ? '<strong>Sunday</strong>' :
                                '<strong>Future Date</strong>'}
                </div>
            `;
                return;
            }

            if (filteredUsers.length === 0) {
                mobileList.innerHTML = '<div class="alert alert-warning">No staff found</div>';
                return;
            }

            filteredUsers.forEach(user => {
                // Skip rendering if the day is before the user's joining date
                if (user.joining_date && selectedDate < user.joining_date) {
                    return;
                }

                // Merge ALL records for this date so duplicate DB rows don't hide check-in data
                const attendance = resolveAttendance(user.attendance || [], selectedDate);

                const profileImage = getProfileImageUrl(user.profile_image);

                let statusText = 'Absent';
                let statusClass = 'absent';
                const status = attendance?.status ? attendance.status.toLowerCase() : '';

                // "Working" = checked in but not yet checked out (any date, not just today)
                if (attendance?.check_in_time && !attendance?.check_out_time) {
                    statusText = attendance.is_late == 1 ? 'Working (Late)' : 'Working';
                    statusClass = 'working';
                } else if (status === 'present') {
                    statusText = attendance.is_late == 1 ? 'Present (Late)' : 'Present';
                    statusClass = 'present';
                } else if (status === 'half-day') {
                    statusText = attendance.is_late == 1 ? 'Half-day (Late)' : 'Half-day';
                    statusClass = 'half-day';
                }

                const checkInTime  = formatTime12Hour(attendance?.check_in_time)  || '-';
                const checkOutTime = formatTime12Hour(attendance?.check_out_time) || '-';
                const checkInLocation = formatLocationName(attendance?.check_in_location_name);
                const checkOutLocation = formatLocationName(attendance?.check_out_location_name);
                const overtime = attendance?.overtime && attendance.overtime !== '00:00:00' ? ' (OT)' : '';

                const employeeCard = document.createElement('div');
                employeeCard.className = 'mobile-employee-card';
                employeeCard.dataset.userId = user.user_id;
                employeeCard.dataset.date = selectedDate;

                employeeCard.innerHTML = `
                <img src="${profileImage}" alt="${user.employee_name}" onerror="this.onerror=null;this.src='${defaultImagePath}'">
                <div class="mobile-employee-info">
                    <div class="mobile-employee-name">${user.employee_name}</div>
                    <div class="mobile-employee-times">
                        In: ${checkInTime} | 📍 ${checkInLocation}<br>
                        Out: ${checkOutTime} | 📍 ${checkOutLocation}${overtime}
                    </div>
                </div>
                <div class="mobile-employee-status ${statusClass}">
                    ${statusText}
                </div>
            `;

                <?php if (isset($role) && in_array($role, ['hr', 'admin'])): ?>
                    employeeCard.addEventListener('click', () => {
                        openAttendanceModal(user.user_id, selectedDate);
                    });
                <?php endif; ?>

                mobileList.appendChild(employeeCard);
            });

            // Update statistics
            let presentCount = 0;
            let absentCount = 0;
            filteredUsers.forEach(user => {
                const todayAttendance = resolveAttendance(user.attendance || [], todayDate);
                if (todayAttendance && (todayAttendance.status === 'present' || todayAttendance.check_in_time)) {
                    presentCount++;
                } else {
                    absentCount++;
                }
            });

            if (document.getElementById('total-employees')) {
                document.getElementById('total-employees').innerText = filteredUsers.length;
            }
            if (document.getElementById('present-employees')) {
                document.getElementById('present-employees').innerText = presentCount;
            }
            if (document.getElementById('absent-employees')) {
                document.getElementById('absent-employees').innerText = absentCount;
            }
        };

        const renderCalendar = () => {
            // Always render horizontal date scroll by default
            renderMobileDateScroll();
            renderMobileAttendanceList();
            // Also render desktop calendar but it will be hidden initially
            if (typeof window.renderDesktopCalendar === 'function') {
                window.renderDesktopCalendar();
            }
        };

        const renderDesktopCalendar = () => {
            const todayDate = new Date().toISOString().split('T')[0];
            const selectedMonth = parseInt(document.getElementById('month-select').value);
            const selectedYear = parseInt(document.getElementById('year-select').value);
            const userFilterEl = document.getElementById('user-filter');
            const selectedUserId = userFilterEl ? userFilterEl.value : '';

            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
            const firstDay = new Date(selectedYear, selectedMonth - 1, 1).getDay();
            const calendarContainer = document.getElementById('calendar-container');

            // Filter users by dropdown selection (only if filter exists)
            let filteredUsers = users;

            if (selectedUserId) {
                filteredUsers = filteredUsers.filter(user => user.user_id == selectedUserId);
            }

            // Clear container
            calendarContainer.innerHTML = '';

            // Weekday headers (always show for calendar grid)
            const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            weekdays.forEach(day => {
                const header = document.createElement('div');
                header.className = 'weekday-header';
                header.textContent = day;
                calendarContainer.appendChild(header);
            });

            // Empty cells for days before month starts (always show for grid layout)
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day';
                emptyDay.style.backgroundColor = '#f8f9fa';
                calendarContainer.appendChild(emptyDay);
            }

            // Days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dateObj = new Date(selectedYear, selectedMonth - 1, day);
                const dayOfWeek = dateObj.getDay();
                const isToday = dateStr === todayDate;
                const isFuture = new Date(dateStr) > new Date(todayDate);

                const dayDiv = document.createElement('div');
                dayDiv.className = 'calendar-day';

                if (isToday) dayDiv.classList.add('today');
                if (isFuture) dayDiv.classList.add('future');

                const holiday = holidays.find(h => h.holiday_date === dateStr);
                if (holiday) {
                    dayDiv.classList.add('holiday');
                } else if (saturdayOffDates.includes(dateStr)) {
                    dayDiv.classList.add('saturday-off');
                } else if (dayOfWeek === 0) {
                    dayDiv.classList.add('sunday');
                }

                // Day header
                const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const dayHeader = document.createElement('div');
                dayHeader.className = 'calendar-day-header';
                dayHeader.innerHTML = `
                <div class="calendar-day-number">${day}</div>
                <div class="calendar-day-name">${weekdays[dayOfWeek]}</div>
            `;
                dayDiv.appendChild(dayHeader);

                // Holiday/Saturday Off/Sunday label
                if (holiday) {
                    const holidayLabel = document.createElement('span');
                    holidayLabel.className = 'holiday-label';
                    holidayLabel.textContent = holiday.title || 'Holiday';
                    dayDiv.appendChild(holidayLabel);
                } else if (saturdayOffDates.includes(dateStr)) {
                    const satLabel = document.createElement('span');
                    satLabel.className = 'saturday-off-label';
                    satLabel.textContent = 'Sat Off';
                    dayDiv.appendChild(satLabel);
                } else if (dayOfWeek === 0) {
                    const sunLabel = document.createElement('span');
                    sunLabel.className = 'sunday-label';
                    sunLabel.textContent = 'Sunday';
                    dayDiv.appendChild(sunLabel);
                }

                // Employee attendance for this day
                // if (!holiday && !saturdayOffDates.includes(dateStr) && dayOfWeek !== 0 && !isFuture)
                if (!holiday && !saturdayOffDates.includes(dateStr) && !isFuture) {
                    filteredUsers.forEach(user => {
                        // Skip rendering if the day is before the user's joining date
                        if (user.joining_date && dateStr < user.joining_date) {
                            return;
                        }

                        // Merge ALL records for this date
                        const attendance = resolveAttendance(user.attendance || [], dateStr);

                        const profileImage = getProfileImageUrl(user.profile_image);
                        const employeeItem = document.createElement('div');
                        employeeItem.className = 'employee-attendance-item attendance-cell';
                        employeeItem.dataset.userId = user.user_id;
                        employeeItem.dataset.date = dateStr;

                        let statusLetter = 'A';
                        let statusClass = 'absent';
                        const status = attendance?.status ? attendance.status.toLowerCase() : '';

                        // Checked in but not out (any date) = Working
                        if (attendance?.check_in_time && !attendance?.check_out_time) {
                            statusLetter = attendance.is_late == 1 ? 'W (L)' : 'W';
                            statusClass = 'working';
                        } else if (status === 'present') {
                            statusLetter = attendance.is_late == 1 ? 'P (L)' : 'P';
                            statusClass = 'present';
                        } else if (status === 'half-day') {
                            statusLetter = attendance.is_late == 1 ? 'H (L)' : 'H';
                            statusClass = 'half-day';
                        } else {
                            statusLetter = 'A';
                            statusClass = 'absent';
                        }

                        employeeItem.innerHTML = `
                        <img src="${profileImage}" alt="${user.employee_name}" onerror="this.onerror=null;this.src='${defaultImagePath}'">
                        <span class="attendance-status ${statusClass}">${statusLetter}</span>
                        ${attendance?.overtime && attendance.overtime !== '00:00:00' ? '<span class="overtime-badge">OT</span>' : ''}
                    `;
                        dayDiv.appendChild(employeeItem);
                    });
                }

                calendarContainer.appendChild(dayDiv);
            }

            // Update statistics
            let presentCount = 0;
            let absentCount = 0;
            filteredUsers.forEach(user => {
                const todayAttendance = resolveAttendance(user.attendance || [], todayDate);
                if (todayAttendance && (todayAttendance.status === 'present' || todayAttendance.check_in_time)) {
                    presentCount++;
                } else {
                    absentCount++;
                }
            });

            if (document.getElementById('total-employees')) {
                document.getElementById('total-employees').innerText = filteredUsers.length;
            }
            if (document.getElementById('present-employees')) {
                document.getElementById('present-employees').innerText = presentCount;
            }
            if (document.getElementById('absent-employees')) {
                document.getElementById('absent-employees').innerText = absentCount;
            }
        };

        // Expose for inline toggle button handlers.
        window.renderDesktopCalendar = renderDesktopCalendar;

        // Event listeners
        $('#month-select').on('change', function () {
            currentMonth = parseInt(this.value);
            loadAttendance(currentMonth, currentYear);
        });

        $('#year-select').on('change', function () {
            currentYear = parseInt(this.value);
            loadAttendance(currentMonth, currentYear);
        });

        const userFilterEl = $('#user-filter');
        if (userFilterEl.length) {
            userFilterEl.on('change', function () {
                if (isMobile) {
                    renderMobileAttendanceList();
                } else {
                    renderCalendar();
                }
            });
        }

        // Load initial data
        loadAttendance(currentMonth, currentYear);

        document.addEventListener('click', function (e) {
            const cell = e.target.closest('.attendance-cell');
            if (!cell) return;

            <?php if (!isset($role) || !in_array($role, ['hr', 'admin'])): ?>
                return;
            <?php endif; ?>

            const userId = cell.dataset.userId;
            const date = cell.dataset.date;

            openAttendanceModal(userId, date);
        });

        let currentEditUser = null;
        let currentEditDate = null;
        let currentAttendanceRecords = [];

        window.openAttendanceModal = function (userId, date) {
            currentEditUser = userId;
            currentEditDate = date;

            fetch(`/api/attendance/day-records?user_id=${userId}&date=${date}`, { headers })
                .then(res => res.json())
                .then(res => {
                    currentAttendanceRecords = res.data || [];
                    let rows = '';

                    if (currentAttendanceRecords.length === 0) {
                        rows = `
                        <tr>
                            <td>
                                <input type="time" class="form-control checkin" data-id="new">
                            </td>
                            <td><span class="text-muted small">-</span></td>
                            <td>
                                <input type="time" class="form-control checkout" data-id="new">
                            </td>
                            <td><span class="text-muted small">-</span></td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    `;
                    } else {
                        currentAttendanceRecords.forEach((r, i) => {
                            // Duration: use backend-computed duration field; null means still active
                            const durationDisplay = r.duration
                                ? r.duration
                                : '<span style="color:#FF9F43;font-weight:600;">Active…</span>';

                            const checkInLoc = r.check_in_location_name || '-';
                            const checkOutLoc = r.check_out_location_name || '-';

                            rows += `
                            <tr>
                                <td>
                                    <input type="time" class="form-control checkin"
                                        data-id="${r.id}"
                                        value="${r.check_in_time || ''}">
                                </td>
                                <td><span class="badge bg-light text-dark border" style="font-size:0.8rem;">📍 ${checkInLoc}</span></td>
                                <td>
                                    <input type="time" class="form-control checkout"
                                        data-id="${r.id}"
                                        value="${r.check_out_time || ''}">
                                </td>
                                <td><span class="badge bg-light text-dark border" style="font-size:0.8rem;">📍 ${checkOutLoc}</span></td>
                                <td>${durationDisplay}</td>
                                <td>${r.overtime && r.overtime !== '00:00:00' ? r.overtime : '-'}</td>
                            </tr>
                        `;
                        });
                    }

                    document.getElementById('attendance-modal-body').innerHTML = rows;

                    const currentStatus = currentAttendanceRecords.length > 0 ?
                        (currentAttendanceRecords[0].status || 'absent') : 'absent';
                    document.getElementById('status').value = currentStatus;

                    new bootstrap.Modal(document.getElementById('attendanceEditModal')).show();
                });
        }

        window.saveAttendanceEdits = function () {
            const selectedStatus = document.getElementById('status').value;
            const records = [];

            document.querySelectorAll('#attendance-modal-body tr').forEach(tr => {
                const checkIn = tr.querySelector('.checkin');
                const checkOut = tr.querySelector('.checkout');
                const recordId = checkIn.dataset.id;

                records.push({
                    id: recordId,
                    check_in_time: checkIn.value,
                    check_out_time: checkOut.value
                });
            });

            fetch('/api/attendance/update-day-records', {
                method: 'POST',
                headers,
                body: JSON.stringify({
                    user_id: currentEditUser,
                    date: currentEditDate,
                    status: selectedStatus,
                    records
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadAttendance(currentMonth, currentYear);
                        bootstrap.Modal
                            .getInstance(document.getElementById('attendanceEditModal'))
                            .hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Attendance updated successfully!',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error updating attendance: ' + (data.message || 'Unknown error'),
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update attendance',
                        confirmButtonColor: '#ff9f43'
                    });
                });
        }

        window.saveBulkAttendance = function () {
            const fromDate = document.getElementById('from_date').value;
            const toDate = document.getElementById('to_date').value;
            const status = document.getElementById('bulk_status').value;
            const checkIn = document.getElementById('check_in_time').value;
            const checkOut = document.getElementById('check_out_time').value;

            if (!fromDate || !toDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required',
                    text: 'Please select date range',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            fetch('/api/attendance/bulk-update', {
                method: 'POST',
                headers,
                body: JSON.stringify({
                    user_id: currentEditUser,
                    from_date: fromDate,
                    to_date: toDate,
                    status,
                    check_in_time: checkIn,
                    check_out_time: checkOut
                })
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        loadAttendance(currentMonth, currentYear);
                        bootstrap.Modal
                            .getInstance(document.getElementById('attendanceBulkModal'))
                            .hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Attendance updated successfully',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message || 'Failed',
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                });
        }
    });
</script>

@endpush
