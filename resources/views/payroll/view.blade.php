@extends('layout.app')
@section('title', 'Payroll List')
@section('content')
@push('css')
    <style>
        /* .sorting_1 {
                    display: flex !important;
                    align-items: center !important;
                    gap: 5px !important;
                } */

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

        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table.datanew {
            width: 100%;
        }

        table.datanew td.sorting_1 {
            display: table-cell !important;
        }


        /* Desktop and general rules for the first column */
        /* table.datanew tbody td:first-child {
            max-width: 260px;
            width: 260px;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
        } */

        /* The anchor containing image and name */
        table.datanew tbody td:nth-child(2) a.d-flex {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }

        .staff-name {
            display: block;
            flex: 1;
            min-width: 80px;
            max-width: 200px;
            word-break: break-word;
            line-height: 1.3;
        }

          /* Add styles for search input and pagination (from category list) */
        .search-input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input input {
            padding-left: 35px !important;
            border-radius: 5px;
        }

        .btn-searchset {            position: absolute;
            left: 10px;
            z-index: 10;
            padding: 0;
            top: 7px !important;
        }

        /* Custom Pagination Styling */
        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            color: #fff;
            border: none;
            margin: 0 4px;
            padding: 6px 15px;
            border-radius: 6px;
            font-weight: bold;
        }

        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            color: #fff;
        }

        .pagination .page-item .page-link:hover {
            background-color: #4a5766;
            color: #fff;
        }

        .pagination .page-item.active .page-link:hover {
            background-color: #e68a35 !important;
        }

        /* Previous and Next buttons */
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            background-color: #fff;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .pagination .page-item:first-child .page-link:hover,
        .pagination .page-item:last-child .page-link:hover {
            background-color: #f8f9fa;
            color: #495057;
            border-color: #dee2e6;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #fff !important;
            color: #dee2e6 !important;
            border: 1px solid #dee2e6 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        table.datanew thead th.details-column,
        table.datanew tbody td.details-column-cell {
            display: none !important;
        }


        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
                -webkit-overflow-scrolling: touch !important;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch !important;
            }

            .search-set {
                margin-right: 1rem !important;
            }
        }

        /* Desktop: show all columns normally */
        @media (min-width: 769px) {
            table.datanew {
                min-width: 1100px;
            }

            .staff-name {
                display: block !important;
                max-width: 250px;
                /* adjust as needed */
                margin-left: 8px !important;
                font-size: 14px !important;
                word-break: break-word !important;
                hyphens: auto !important;
            }

            table.datanew thead th,
            table.datanew tbody td {
                display: table-cell !important;
            }

            /* Hide the Details toggle column on desktop */
            table.datanew thead th.details-column,
            table.datanew tbody td.details-column-cell {
                display: none !important;
            }

            .staff-name {
                display: block !important;
                max-width: 250px;
                /* Adjust based on your layout */
                margin-left: 8px !important;
                font-size: 14px !important;
                word-break: break-word !important;
                hyphens: auto !important;
            }
        }

        /* Mobile: hide non-essential columns, show Details toggle */
        @media (max-width: 768px) {

            table.datanew thead th:nth-child(n+3),
            table.datanew tbody td:nth-child(n+3) {
                display: none !important;
            }

            /* Show only Checkbox, Staff Name and Details columns on mobile */
            table.datanew thead th:first-child,
            table.datanew tbody td:first-child,
            table.datanew thead th:nth-child(2),
            table.datanew tbody td:nth-child(2) {
                display: table-cell !important;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td.details-column-cell {
                display: table-cell !important;
                text-align: center;
                vertical-align: top !important;
                width: 44px;
                min-width: 44px;
                padding-top: 14px !important;
                padding-left: 4px !important;
                padding-right: 4px !important;
            }

            table.datanew tbody tr {
                position: relative;
            }

            table.datanew tbody td.details-column-cell .toggle-details {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                line-height: 1;
            }

            .toggle-details i {
                font-size: 24px;
            }

            /* Second column (Staff Name column) */
            table.datanew tbody td:nth-child(2) {
                max-width: 260px;
                /* control column width */
                white-space: normal !important;
                word-break: break-word !important;
                overflow-wrap: anywhere !important;
            }

            /* Image + name container */
            table.datanew tbody td:nth-child(2) a.d-flex {
                display: flex;
                align-items: center;
                gap: 2px;
                width: 100%;
            }

            /* Staff name text */
            table.datanew tbody td:nth-child(2) .staff-name {
                display: block;
                flex: 1;
                min-width: 0;
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
                line-height: 1.3;
                font-size: 14px;
            }

            .collapse-details {
                margin-top: 8px;
                margin-right: 4px;
                padding: 10px 12px;
            }

            .detail-item {
                align-items: flex-start;
                gap: 8px;
                margin-bottom: 8px;
            }

            .detail-label {
                flex: 0 0 95px;
                min-width: 95px;
            }

            .detail-value {
                min-width: 0;
                overflow-wrap: anywhere;
            }

            .mobile-actions {
                display: block;
                gap: 0;
                margin-top: 12px;
                padding-top: 12px;
            }

            .mobile-actions .detail-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .mobile-actions .detail-actions a.btn {
                width: auto;
                height: auto;
                min-height: 31px;
                border-radius: 4px;
                padding: 5px 9px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
                transform: none;
            }

            /* Limit to 2 lines with ellipsis */
            /* .staff-name.truncated {
                            display: -webkit-box !important;
                            -webkit-line-clamp: 2 !important;
                            -webkit-box-orient: vertical !important;
                            overflow: hidden !important;
                            text-overflow: ellipsis !important;
                        } */
        }

        /* Tablet specific fixes */
        @media screen and (width: 768px) {
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td.details-column-cell {
                display: table-cell !important;
                width: 60px !important;
                min-width: 60px !important;
                max-width: 60px !important;
            }

            .toggle-details {
                display: inline-block !important;
                padding: 8px !important;
                z-index: 10 !important;
            }

            .toggle-details i {
                font-size: 20px !important;
                width: 24px !important;
                height: 24px !important;
                line-height: 24px !important;
            }
        }

        /* Fade out animation for error messages */
        .fade-out {
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .fade-out.hidden {
            opacity: 0;
        }

        @media (max-width: 767.98px) {
            .table-top .search-set {
                display: flex;
                justify-content: flex-start !important;
                width: 100%;
            }

            .table-top .search-input {
                margin-left: 0 !important;
            }
        }

        /* Staff specific styling */
        .staff-image {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 10px;

        }

        /* Collapsible details styling */
        .collapse-details {
            /* margin-top: 10px; */
            padding: 10px;
            /* background-color: #f8f9fa; */
            /* border-radius: 5px; */
            /* border-left: 3px solid #ff9f43; */
            display: none;
        }

        .collapse-details.show {
            display: block;
        }

        .detail-item {
            display: flex;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .detail-label {
            font-weight: 600;
            min-width: 100px;
            color: #495057;
        }

        .detail-value {
            color: #212529;
            flex: 1;
        }

        .mobile-actions {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .mobile-actions a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .mobile-actions a:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }

        .mobile-actions svg {
            width: 18px;
            height: 18px;
        }
         /* ✅ Hide default DataTables search box completely */
        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

    .hr-btnbg {
        background: #ff9f43 !important;
        color: #fff !important;
        border-radius: 4px;
        font-weight: 500;
        padding: 8px 15px;
        transition: all 0.3s ease;
    }

    .hr-btnbg:hover {
        background: #ff9f43;
        color: #fff !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .managepayrol {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 3px;
    }

    .capitalize-text {
        text-transform: capitalize;
    }
    </style>
@endpush


<!-- Add Salary Modal -->
<div class="modal fade" id="addSalaryModal" tabindex="-1" aria-labelledby="addSalaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSalaryLabel">Add Employee Salaries</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <div class="modal-body">
                <!-- Month Select -->
                <div class="mb-3">
                    <label for="salaryMonth" class="form-label">Select Month</label>
                    <input type="month" id="salaryMonth" class="form-control" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn hr-btnbg" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn hr-btnbg" id="openSalaryPage">OK</button>
            </div>

        </div>
    </div>
</div>
 <div class="content">
        <!-- Desktop header -->
        <div class="page-header d-none d-md-flex">
            <div class="page-title" style="font-weight: 600;">
                <h4>Manage Payrolls</h4>
            </div>
            <div class="page-btn">
                @if (in_array(auth()->user()->role, ['hr', 'admin']))
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        @php
                            $now = new DateTime();
                            $now->modify("-1 month");
                            $lastMonth = $now->format("Y-m");
                            $currentMonth = date("Y-m");
                        @endphp
                        <input type="month" id="payrollMonthFilter" name="payrollMonth"
                            class="form-control w-auto"
                            value="{{ request('month', $currentMonth) }}"
                            max="{{ $currentMonth }}" />
                        <button type="button" id="downloadMultipleBtn"
                            class="btn hr-btnbg" style="white-space:nowrap;">
                            <i class="mdi mdi-download"></i> Download Selected
                        </button>
                        <a href="/payroll" class="btn hr-btnbg">
                            <i class="fa fa-plus"></i> Add Payroll
                        </a>
                        <a href="{{ route('payroll.salary-details') }}" class="btn hr-btnbg">
                            <i class="fas fa-plus"></i> Group Salary
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Mobile header -->
        <div class="d-md-none mb-3">
            @if (in_array(auth()->user()->role, ['hr', 'admin']))
                @php
                    $now = new DateTime();
                    $now->modify("-1 month");
                    $lastMonth = $now->format("Y-m");
                    $currentMonth = date("Y-m");
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-2 fw-600">
                    <h4 class="mb-0" style="font-weight: 600">Manage Payrolls</h4>
                </div>
                <div class="mb-2">
                    <input type="month" id="payrollMonthFilterMobile" name="payrollMonth"
                        class="form-control"
                        value="{{ request('month', $currentMonth) }}"
                        max="{{ $currentMonth }}" />
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" id="downloadMultipleBtnMobile"
                        class="btn hr-btnbg flex-fill" style="font-size:13px; white-space:nowrap;">
                        <i class="mdi mdi-download"></i> Download
                    </button>
                    <a href="/payroll" class="btn hr-btnbg flex-fill" style="font-size:13px;">
                        <i class="fa fa-plus"></i> Add Payroll
                    </a>
                    <a href="{{ route('payroll.salary-details') }}" class="btn hr-btnbg flex-fill" style="font-size:13px;">
                        <i class="fas fa-plus"></i> Group Salary
                    </a>
                </div>
            @else
                <div class="mb-2">
                    <h4 class="mb-0">Manage Payrolls</h4>
                </div>
            @endif
        </div>
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">


                <div class="mb-2">
                    <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
                        <div class="search-path"></div>
                        <div class="search-input">
                            <a class="btn btn-searchset">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                    alt="img">
                            </a>
                            <input type="text" id="search-input" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                </div>


                <div class="table-scroll-top">
                    <div></div>
                </div>
                <div class="table-container table-responsive">
                    <table class="table datanew w-100" id="payroll-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                <th>Staff Name</th>
                                <th class="desktop-only-col">Base Salary</th>
                                <th class="desktop-only-col">Month & Year</th>
                                <th class="desktop-only-col">Net Salary</th>
                                <th class="desktop-only-col">Payment Date</th>
                                <th style="display: none;">Created At</th>
                                <th class="desktop-only-col">Actions</th>
                                <th class="details-column">Details</th>
                            </tr>
                        </thead>
                        <tbody id="payroll-table-tbody">

                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="per-page-select" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="pagination-from">0</span> - <span id="pagination-to">0</span> of <span
                                id="pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-numbers">
                            <!-- page numbers will be inserted here -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deduction breakdown modal (same as Add Payroll / Profile) -->
<div class="modal fade" id="listDeductionBreakdownModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="listDeductionBreakdownModalLabel"><i class="mdi mdi-information-outline me-1"></i> Why was this amount deducted?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
            </div>
            <div class="modal-body">
                <div id="listDeductionBreakdownLoading" style="display:block;">Loading...</div>
                <div id="listDeductionBreakdownContent" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>
 </div>

 <div class="loginIdName" id="userRole" data-role="{{ auth()->user()->role }}">
</div>
@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Get current month as default
        const currentMonth = new Date().toISOString().slice(0, 7); // "YYYY-MM" format

        // Get month from URL or use current month as default
        const urlParams = new URLSearchParams(window.location.search);
        const selectedMonth = urlParams.get('month') || currentMonth;

        // Set the month filter value
        const monthFilter = document.getElementById('payrollMonthFilter');
        if (monthFilter) {
            monthFilter.value = selectedMonth;
        }

        $('#groupsalary').on('click', function() {
            // Use selected month as default for Group Salary
            window.location.href = `/payroll/salary-details?month=${selectedMonth}`;
        });

        $('#yearlySlipBtn').on('click', function() {
            Swal.fire({
                icon: 'info',
                title: 'Yearly Slip',
                text: 'Select a year to download slips for that year.',
                showCancelButton: true,
                confirmButtonText: 'Download',
                cancelButtonText: 'Cancel'
            });
        });

        // Month filter change event
        if (monthFilter) {
            monthFilter.addEventListener('change', function() {
                const month = this.value;
                if (month) {
                    window.location.href = `/payrollview?month=${month}`;
                }
            });
        }

        // Mobile month filter change event
        const monthFilterMobile = document.getElementById('payrollMonthFilterMobile');
        if (monthFilterMobile) {
            monthFilterMobile.value = selectedMonth;
            monthFilterMobile.addEventListener('change', function() {
                const month = this.value;
                if (month) {
                    window.location.href = `/payrollview?month=${month}`;
                }
            });
        }


        const token = localStorage.getItem('token'); // JWT token

     function getUserRole() {
            if (!token || !token.includes('.')) return 'staff';
            try {
                let payload = JSON.parse(atob(token.split('.')[1]));
                return payload.role || 'staff';
            } catch (e) {
                return 'staff';
            }
}

        function fetchPayroll() {
            // Build URL with month filter
            let apiUrl = '<?= url("/api/payroll/getAll") ?>';
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const params = new URLSearchParams();
            if (selectedMonth) params.append('month', selectedMonth);
            if (selectedSubAdminId) params.append('selectedSubAdminId', selectedSubAdminId);
            if (params.toString()) apiUrl += '?' + params.toString();

            $.ajax({
                url: apiUrl,
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        const payrolls = response.data;
                        let tableRows = '';
                        let userRole = getUserRole(); // Get role from JWT
                        var baseImagePath = "<?= url(env("ImagePath", "")) ?>";

                        payrolls.forEach((payroll) => {
                            // let imageUrl = payroll.profile_image ?
                            //     `/upload/${payroll.profile_image}` :
                            //     `${baseImagePath}upload/default-profile.jpg`;
                            let imageUrl = payroll.profile_image
                    ? "{{ env('ImagePath') }}/storage/" + payroll.profile_image
                    : "/public/admin/assets/img/customer/customer5.jpg";
                            let actionButtons = '';
                            let mobileActionsHtml = '';

                            const employeeId = payroll.employee_id || payroll.user_id;
                            const monthYear = payroll.month_year || '';
                            const empName = (payroll.username || '').replace(/"/g, '&quot;');
                            const infoIcon = `<a href="javascript:void(0)" role="button" class="text-info-custom action-icons payroll-deduction-info" title="Why was this amount deducted?" data-user-id="${employeeId}" data-month-year="${monthYear}" data-name="${empName}"><i class="mdi mdi-information-outline"></i></a>`;

                            if (currentUserRole !== 'staff') {
                               actionButtons = `
                                    <a href="/payroll/profile/${payroll.id}" class="me-2" title="View">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"></path>
                                            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"></path>
                                        </svg>
                                    </a>
                                    <a href="/payroll/${payroll.id}" class="me-2" title="Edit">
                                        <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"></path>
                                        </svg>
                                    </a>
                                    <a href="#" class="delete-payroll me-2" data-id="${payroll.id}" title="Delete">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#dc3545"></path>
                                            <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#dc3545"></path>
                                        </svg>
                                    </a>
                                    <a href="/payroll/download-slip/${payroll.id}" class="me-2" title="Download">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 16L7 11H10V4H14V11H17L12 16Z" fill="#092C4C"></path>
                                            <path d="M20 18H4V20H20V18Z" fill="#092C4C"></path>
                                        </svg>
                                    </a>
                                `;
                                mobileActionsHtml = `
                                    <div class="detail-actions">
                                        <a href="/payroll/profile/${payroll.id}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> View</a>
                                        <a href="javascript:void(0)" role="button" class="btn btn-sm btn-info payroll-deduction-info" data-user-id="${employeeId}" data-month-year="${monthYear}" data-name="${empName}"><i class="fa fa-information-circle"></i> Deduction</a>
                                        <a href="/payroll/${payroll.id}" class="btn btn-sm btn-warning"><i class="fa fa-pen"></i> Edit</a>
                                        <a href="#" class="btn btn-sm btn-danger delete-payroll" data-id="${payroll.id}"><i class="fa fa-trash"></i> Delete</a>
                                        <a href="/payroll/download-slip/${payroll.id}" class="btn btn-sm btn-success" target="_blank"><i class="fa fa-download"></i> Download</a>
                                    </div>
                                `;
                            } else {
                                actionButtons = `
                                    <a href="/payroll/profile/${payroll.id}" class="text-primary me-2" title="View"><i class="fa fa-eye"></i></a>

                                `;
                                mobileActionsHtml = `
                                    <div class="detail-actions">
                                        <a href="/payroll/profile/${payroll.id}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> View</a>
                                        <a href="javascript:void(0)" role="button" class="btn btn-sm btn-info payroll-deduction-info" data-user-id="${employeeId}" data-month-year="${monthYear}" data-name="${empName}"><i class="fa fa-information-circle"></i> Deduction</a>
                                    </div>
                                `;
                            }

                            const detailsToggle = `
                                <a href="javascript:void(0);" class="toggle-details" data-target="details-${payroll.id}">
                                    <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                </a>
                            `;

                            const staffNameColumn = `
                                <div>
                                    <a href="javascript:void(0);" class="d-flex align-items-center">
                                        <img src="${imageUrl}" alt="staff" class="staff-image">
                                        <span class="staff-name capitalize-text">${payroll.username}</span>
                                    </a>
                                    <div class="collapse-details d-lg-none" id="details-${payroll.id}">
                                        <div class="detail-item">
                                            <span class="detail-label">Base Salary:</span>
                                            <span class="detail-value capitalize-text">${payroll.salary_amount}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Month & Year:</span>
                                            <span class="detail-value capitalize-text">${payroll.month_year}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Net Salary:</span>
                                            <span class="detail-value capitalize-text">${payroll.net_salary}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Payment Date:</span>
                                            <span class="detail-value capitalize-text">${payroll.payment_date}</span>
                                        </div>
                                        <div class="mobile-actions">
                                            ${mobileActionsHtml}
                                        </div>
                                    </div>
                                </div>
                            `;

                            tableRows += `
                                <tr data-id="${payroll.id}" data-employee-id="${payroll.employee_id || payroll.id}">
                                    <td>
                                        <input type="checkbox" class="payroll-checkbox form-check-input"
                                               data-id="${payroll.id}"
                                               data-employee-id="${payroll.employee_id || payroll.id}">
                                    </td>
                                    <td>${staffNameColumn}</td>
                                    <td class="desktop-only-col capitalize-text">${payroll.salary_amount}</td>
                                    <td class="desktop-only-col capitalize-text">${payroll.month_year}</td>
                                    <td class="desktop-only-col capitalize-text">${payroll.net_salary}</td>
                                    <td class="desktop-only-col capitalize-text">${payroll.payment_date}</td>
                                    <td class="capitalize-text" style="display: none;">${payroll.created_at}</td>
                                    <td class="desktop-only-col">
                                        <div class="d-flex align-items-center gap-2">
                                            ${actionButtons}
                                        </div>
                                    </td>
                                    <td class="text-center details-column-cell">${detailsToggle}</td>
                                </tr>
                            `;
                        });

                        // $('#payroll-table tbody').html(tableRows); // Update the table body

                        if ($.fn.DataTable.isDataTable('#payroll-table')) {
                                $('#payroll-table').DataTable().destroy();
                            }

                            $('#payroll-table tbody').html(tableRows);

                            const table = $('#payroll-table').DataTable({
                            order: [
                                [6, 'desc']
                            ],
                            columnDefs: [{
                                    targets: 0, // checkbox column
                                    orderable: false,
                                    searchable: false
                                },
                                {
                                    targets: 6,
                                    visible: false
                                },
                                {
                                    targets: 8, // details column - visibility handled by CSS
                                    orderable: false,
                                    searchable: false
                                }
                            ],
                            language: {
                                search: "",
                                searchPlaceholder: "Search"
                            },
                            pageLength: 10,
                            drawCallback: function(settings) {
                                updatePaginationUI(this.api());

                                // Sync top scrollbar
                                const topScroll = document.querySelector('.table-scroll-top');
                                const tableResponsive = document.querySelector('.table-responsive');
                                const tableElement = document.querySelector('.datanew');
                                if (topScroll && tableResponsive && tableElement) {
                                    const topInnerDiv = topScroll.querySelector('div');
                                    topInnerDiv.style.width = tableElement.scrollWidth + 'px';
                                    topScroll.onscroll = () => tableResponsive.scrollLeft = topScroll.scrollLeft;
                                    tableResponsive.onscroll = () => topScroll.scrollLeft = tableResponsive.scrollLeft;
                                }
                            }
                        });



                        // Custom Search
                        $('#search-input').on('keyup', function() {
                            table.search($(this).val()).draw();
                        });

                        // Per Page Change
                        $('#per-page-select').on('change', function() {
                            table.page.len($(this).val()).draw();
                        });

                        // Initialize checkbox functionality after DataTable
                        initCheckboxFunctionality();

                    } else {
                        Swal.fire('Error', 'Failed to load payroll records', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'Failed to fetch payroll records', 'error');
                }
            });
        }

        function updatePaginationUI(api) {
            const info = api.page.info();

            $('#pagination-from').text(info.recordsTotal === 0 ? 0 : info.start + 1);
            $('#pagination-to').text(info.end);
            $('#pagination-total').text(info.recordsTotal);

            const currentPage = info.page + 1;
            const totalPages = info.pages;

            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            let paginationHtml = '';

            // Previous button
            paginationHtml += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" onclick="${currentPage > 1 ? 'goToPage(' + (currentPage - 2) + ')' : 'void(0)'}">Previous</a>
                </li>
            `;

            // Prev ellipsis
            if (startPage > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="goToPage(${startPage - 2})">...</a></li>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="goToPage(${i - 1})">${i}</a>
                    </li>
                `;
            }

            // Next ellipsis
            if (endPage < totalPages) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="goToPage(${endPage})">...</a></li>`;
            }

            // Next button
            paginationHtml += `
                <li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" onclick="${currentPage < totalPages ? 'goToPage(' + currentPage + ')' : 'void(0)'}">Next</a>
                </li>
            `;

            $('#pagination-numbers').html(paginationHtml);
            $('.pagination-controls').show();
        }

        window.goToPage = function(pageIndex) {
            $('#payroll-table').DataTable().page(pageIndex).draw('page');
        };

        // Toggle details icon
        $(document).on('click', '.toggle-details', function() {
            const targetId = $(this).data('target');
            const icon = $(this).find('i');
            const collapseEl = document.getElementById(targetId);
            if (!collapseEl) return;

            const isVisible = collapseEl.classList.contains('show');
            // Close all others first
            document.querySelectorAll('.collapse-details.show').forEach(el => {
                if (el.id !== targetId) {
                    el.classList.remove('show');
                    const otherIcon = document.querySelector(`[data-target="${el.id}"] i`);
                    if (otherIcon) {
                        otherIcon.classList.remove('fa-minus-circle');
                        otherIcon.classList.add('fa-plus-circle');
                        otherIcon.style.color = '#ff9f43';
                    }
                }
            });

            if (isVisible) {
                collapseEl.classList.remove('show');
                icon.removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color', '#ff9f43');
            } else {
                collapseEl.classList.add('show');
                icon.removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color', 'red');
            }
        });

        function initCheckboxFunctionality() {
            const checkAllCheckbox = document.getElementById('checkAll');
            const downloadMultipleBtn = document.getElementById('downloadMultipleBtn');
            const downloadMultipleBtnMobile = document.getElementById('downloadMultipleBtnMobile');

            function doDownload() {
                const selectedCheckboxes = document.querySelectorAll('.payroll-checkbox:checked');
                const selectedPayrollIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.id);

                if (selectedPayrollIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No payrolls selected',
                        text: 'Please select at least one payroll record'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Preparing download...',
                    html: 'Generating salary slips for ' + selectedPayrollIds.length + ' record(s)',
                    icon: 'info',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch("<?= url("/api/payroll/downloadMultipleByIds",) ?>", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ payroll_ids: selectedPayrollIds, month: selectedMonth })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to download salary slips');
                    return response.blob();
                })
                .then(blob => {
                    Swal.close();
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `salary-slips-${selectedMonth}.pdf`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                    Swal.fire({
                        icon: 'success',
                        title: 'Downloaded successfully',
                        text: selectedPayrollIds.length + ' salary slip(s) combined in one PDF',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false
                    });
                })
                .catch(error => {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Download failed', text: error.message });
                });
            }

            function updateDownloadButtonVisibility() {
                // Keep visible as per design
            }

            if (checkAllCheckbox) {
                checkAllCheckbox.addEventListener('change', function() {
                    document.querySelectorAll('.payroll-checkbox').forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateDownloadButtonVisibility();
                });
            }

            $(document).on('change', '.payroll-checkbox', function() {
                const allCheckboxes = document.querySelectorAll('.payroll-checkbox');
                const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(allCheckboxes).some(cb => cb.checked);
                if (checkAllCheckbox) {
                    checkAllCheckbox.checked = allChecked;
                    checkAllCheckbox.indeterminate = someChecked && !allChecked;
                }
                updateDownloadButtonVisibility();
            });

            if (downloadMultipleBtn) {
                downloadMultipleBtn.addEventListener('click', doDownload);
            }
            if (downloadMultipleBtnMobile) {
                downloadMultipleBtnMobile.addEventListener('click', doDownload);
            }
        }

        // Call the fetchPayroll function on page load
        fetchPayroll();

        // Deduction breakdown popup – open modal (shared for click and touchend)
        function openListDeductionModal($el) {
            const userId = $el.data('user-id');
            const month = $el.data('month-year');
            const name = $el.data('name') || 'Employee';
            if (!userId || !month) return;
            const modal = document.getElementById('listDeductionBreakdownModal');
            const loading = document.getElementById('listDeductionBreakdownLoading');
            const content = document.getElementById('listDeductionBreakdownContent');
            if (!modal) return;
            document.getElementById('listDeductionBreakdownModalLabel').innerHTML = '<i class="mdi mdi-information-outline me-1"></i> Why was this amount deducted? – ' + name;
            loading.style.display = 'block';
            content.style.display = 'none';
            content.innerHTML = '';
            if (modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
            $.ajax({
                url: '<?= url("api/payroll/get-deduction-breakdown") ?>',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '<?= csrf_token() ?>'
                },
                data: { user_id: userId, month: month },
                dataType: 'json',
                success: function(res) {
                    loading.style.display = 'none';
                    if (res.status !== 'success' || !res.data) {
                        content.innerHTML = '<p class="text-muted">No breakdown available.</p>';
                        content.style.display = 'block';
                        return;
                    }
                    const d = res.data;
                    let html = '';
                    if (d.leaves && d.leaves.count > 0) {
                        html += '<div class="breakdown-section" style="border-left:3px solid #E66136;padding-left:0.75rem;margin-bottom:1rem;"><strong>Leaves</strong>';
                        if (d.leaves.dates && d.leaves.dates.length) {
                            html += '<ul class="list-unstyled small mb-1" style="max-height:120px;overflow-y:auto;">';
                            d.leaves.dates.forEach(function(l) { html += '<li>' + (l.label || l.date) + '</li>'; });
                            html += '</ul>';
                        }
                        html += '<span class="text-danger">Deduction: ₹' + (d.leaves.deduction_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.absent && d.absent.dates && d.absent.dates.length) {
                        html += '<div class="breakdown-section" style="border-left:3px solid #E66136;padding-left:0.75rem;margin-bottom:1rem;"><strong>Absent</strong><ul class="list-unstyled small mb-1" style="max-height:120px;overflow-y:auto;">';
                        d.absent.dates.forEach(function(a) { html += '<li>' + (a.label || a.date) + '</li>'; });
                        html += '</ul><span class="text-danger">Deduction: ₹' + (d.absent.deduction_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.half_day && d.half_day.count > 0) {
                        html += '<div class="breakdown-section" style="border-left:3px solid #E66136;padding-left:0.75rem;margin-bottom:1rem;"><strong>Half-day</strong>';
                        if (d.half_day.dates && d.half_day.dates.length) {
                            html += '<ul class="list-unstyled small mb-1" style="max-height:120px;overflow-y:auto;">';
                            d.half_day.dates.forEach(function(h) {
                                var baseLabel = (h.label || h.date);
                                var worked = h.worked_text ? (' – Worked: ' + h.worked_text) : '';
                                var missing = h.missing_text ? (' – Deduct: ' + h.missing_text) : '';
                                html += '<li>' + baseLabel + worked + missing + '</li>';
                            });
                            html += '</ul>';
                        }
                        html += '<span class="text-danger">Deduction: ₹' + (d.half_day.deduction_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.late && d.late.list && d.late.list.length) {
                        html += '<div class="breakdown-section" style="border-left:3px solid #E66136;padding-left:0.75rem;margin-bottom:1rem;"><strong>Late arrival</strong><ul class="list-unstyled small" style="max-height:120px;overflow-y:auto;">';
                        d.late.list.forEach(function(l) { html += '<li>' + (l.label || l.date) + ' – ' + (l.late_text || l.late_minutes + ' min') + '</li>'; });
                        html += '</ul><span class="text-danger">Deduction: ₹' + (d.late.deduction_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.overtime && d.overtime.list && d.overtime.list.length) {
                        html += '<div class="breakdown-section" style="border-left:3px solid #E66136;padding-left:0.75rem;margin-bottom:1rem;"><strong class="text-success">Overtime</strong><ul class="list-unstyled small" style="max-height:120px;overflow-y:auto;">';
                        d.overtime.list.forEach(function(o) { html += '<li>' + (o.label || o.date) + ' – ' + (o.overtime_text || o.overtime_hours + 'h') + '</li>'; });
                        html += '</ul><span class="text-success">Added to salary: ₹' + (d.overtime.pay_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.summary) {
                        html += '<hr><div class="fw-bold"><span>Total deduction (leaves + half-day + late):</span> <span class="text-danger">₹' + (d.summary.total_deduction || 0).toFixed(2) + '</span></div>';
                        if (d.summary.overtime_added > 0) html += '<div class="fw-bold"><span>Overtime added:</span> <span class="text-success">₹' + d.summary.overtime_added.toFixed(2) + '</span></div>';
                    }
                    if (!d.leaves?.count && !d.absent?.dates?.length && !d.half_day?.count && !d.late?.list?.length && !d.overtime?.list?.length) {
                        html += '<p class="text-muted">No leaves, absent, late, or overtime in this month.</p>';
                    }
                    content.innerHTML = html;
                    content.style.display = 'block';
                },
                error: function() {
                    loading.style.display = 'none';
                    content.innerHTML = '<p class="text-danger">Failed to load details.</p>';
                    content.style.display = 'block';
                }
            });
        }

        // Deduction: handle both touchend (mobile) and click (desktop), avoid double-open
        var listDeductionLastTouch = 0;
        $(document).on('touchend', '.payroll-deduction-info', function(e) {
            e.preventDefault();
            e.stopPropagation();
            listDeductionLastTouch = Date.now();
            var $el = $(e.target).closest('.payroll-deduction-info');
            if ($el.length) openListDeductionModal($el);
        });
        $(document).on('click', '.payroll-deduction-info', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (Date.now() - listDeductionLastTouch < 400) return; // already opened by touchend
            openListDeductionModal($(this));
        });

        // Handle delete action
        $(document).on('click', '.delete-payroll', function(e) {
            e.preventDefault();
            const payrollId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'hr-btnbg',
                    cancelButton: 'hr-btnbg',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/api/payroll/${payrollId}`,
                        type: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', 'The payroll record has been deleted.', 'success').then(() => {
                                    $(`tr[data-id="${payrollId}"]`).remove();
                                });
                            } else {
                                Swal.fire('Error!', 'Failed to delete the payroll record.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'There was an error deleting the payroll record.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush



@endsection
