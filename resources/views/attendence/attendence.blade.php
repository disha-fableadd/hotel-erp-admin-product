@extends('layout.app')
@section('title', 'Manage Attendance')
@section('content')
<style>
    .form-control {
        height: 2.3rem;
    }
    .employee-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .employee-row:hover {
        background-color: #f8f9fa;
    }

    .employee-row.selected {
        background-color: #e3f2fd;
    }

    .history-row {
        display: none;
        background-color: #f8f9fa;
    }

    .history-row.show {
        display: table-row;
    }

    .history-content {
        padding: 20px;
    }

    .history-table {
        width: 100%;
        margin-top: 10px;
    }

    .history-table th {
        background-color: #e9ecef;
        padding: 8px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
    }

    .history-table td {
        padding: 8px;
        border-bottom: 1px solid #dee2e6;
        font-size: 13px;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-present {
        background-color: #d4edda;
        color: #155724;
    }

    .status-absent {
        background-color: #f8d7da;
        color: #721c24;
    }

    .status-half-day {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-leave {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .loading-spinner {
        text-align: center;
        padding: 20px;
    }

    /* Mobile Expand Styles */
    .desktop-only-col {
        display: table-cell;
    }

    .mobile-expand-col {
        display: none;
    }

    .expanded-details {
        display: none;
        margin-top: 12px;
        padding: 12px;
        background-color: #f8f9fa;
        border-radius: 6px;
        border-left: 3px solid #007bff;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 600;
        color: #495057;
        font-size: 13px;
    }

    .detail-value {
        color: #212529;
        font-size: 13px;
        text-align: right;
    }

    .detail-actions {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 2px solid #dee2e6;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .detail-actions .btn {
        flex: 1;
        min-width: 80px;
    }

    .expand-toggle {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #fff;
        font-size: 20px;
        font-weight: 400;
        line-height: 1;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #FF9F43;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .expand-toggle.active {
        background-color: #e05c00;
    }

    .expand-toggle i {
        transition: transform 0.3s ease;
        display: inline-block;
        line-height: 1;
    }

    .expand-toggle.active i {
        transform: rotate(180deg);
    }

    .mobile-expand-col {
        vertical-align: top !important;
        padding-top: 10px !important;
    }

    .filter-row {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    .filter-row .form-select {
        flex: 1;
        min-width: 120px;
    }

    .filter-row .btn {
        white-space: nowrap;
    }

    @media (max-width: 767px) {
        .desktop-only-col {
            display: none !important;
        }

        .mobile-expand-col {
            display: table-cell !important;
        }

        .expanded-details.show {
            display: block;
        }

        .attendenceall {
            font-size: 8px !important;
            padding: 12px !important;
        }

        .btnpdingam {
            margin: 0px !important;
        }

        .cart-sm-title {
            font-size: 12px !important;
            margin-bottom: 5px !important;
        }

        .history-table {
            font-size: 11px;
        }

        .history-table th,
        .history-table td {
            padding: 6px 4px;
        }

        /* Prevent row click on mobile */
        .employee-row {
            cursor: default;
        }

        /* Filter row mobile adjustments */
        .filter-row {
            margin-top: 8px;
        }

        .filter-row .form-select {
            font-size: 14px;
        }

        .filter-row .btn {
            flex: 1 1 100%;
        }
    }
    .hr-btnbg {
            background-color: #FF9F43;
            color: white;
        }

        button.btn.btn-sm.view-history-btn {
    background-color: #FF9F43;
    color: white;
}
</style>
<div class="content">
        <!-- Desktop: Title and Button in same row -->
        <div class="page-header d-none d-md-flex">
            <div class="page-title">
                <h4 class="card-title">Staff Attendance</h4>
            </div>
            <div>
                <a href="/view" class="btn hr-btnbg btnpdingam text-nowrap">
                    All Attendance
                </a>
            </div>
        </div>

        <!-- Mobile: Title and Button in same row -->
        <div class="d-md-none mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="card-title mb-0">Staff Attendance</h4>
                <a href="/view" class="btn hr-btnbg btnpdingam text-nowrap" style="font-size: 12px; padding: 6px 12px;">
                    All Attendance
                </a>
            </div>
        </div>
        
        <!-- Desktop: Filter Row (Month/Year selectors) -->
        <div class="filter-row mb-3 d-none d-md-flex">
            <select id="month-selector" class="form-select">
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <select id="year-selector" class="form-select">
                <!-- Will be populated by JS -->
            </select>
        </div>

        <!-- Mobile: Filter Row (Month/Year selectors) -->
        <div class="filter-row mb-3 d-md-none">
            <select id="month-selector-mobile" class="form-select">
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <select id="year-selector-mobile" class="form-select">
                <!-- Will be populated by JS -->
            </select>
        </div>

<!-- <div class="col-lg-12 grid-margin stretch-card"> -->
    <div class="card">
        <div class="card-body">
            <!-- <div class="d-md-flex justify-content-between align-items-center mb-3"> -->
                
                <!-- <div class="d-md-flex gap-2">
                    <select id="month-selector" class="form-select">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <select id="year-selector" class="form-select">
                         Will be populated by JS 
                    </select>
                    <a href="/view" class="btn hr-btnbg btnpdingam text-nowrap">
                        All Attendance
                    </a>
                </div> -->
            <!-- </div> -->

            <div class="table-responsive">
                <table class="table" id="employee-attendance-table">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th class="desktop-only-col">Total Days</th>
                            <th class="desktop-only-col">Present Days</th>
                            <th class="desktop-only-col">Work Hours</th>
                            <th class="desktop-only-col">Overtime</th>
                            <th class="desktop-only-col">Late Hours</th>
                            <th class="desktop-only-col">Leaves</th>
                            <th class="desktop-only-col">Absent</th>
                            <th class="desktop-only-col">Action</th>
                            <th class="mobile-expand-col" style="width: 50px;">Details</th>
                        </tr>
                    </thead>
                    <tbody id="employee-attendance-body">
                        <tr>
                            <td colspan="10" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
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

        const toLocalDateString = (date = new Date()) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const normalizeDateString = (value) => {
            if (!value) return '';
            return String(value).substring(0, 10);
        };

        const getDayOfWeekFromDateString = (dateStr) => {
            const normalized = normalizeDateString(dateStr);
            if (!normalized) return null;
            const [year, month, day] = normalized.split('-').map(Number);
            return new Date(year, month - 1, day).getDay();
        };

        let effectiveTodayStr = toLocalDateString();

        // Initialize month and year selectors
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1;
        const currentYear = currentDate.getFullYear();

        // Desktop selectors
        const monthSelector = document.getElementById('month-selector');
        const yearSelector = document.getElementById('year-selector');
        
        // Mobile selectors
        const monthSelectorMobile = document.getElementById('month-selector-mobile');
        const yearSelectorMobile = document.getElementById('year-selector-mobile');

        if (monthSelector) monthSelector.value = currentMonth;
        if (monthSelectorMobile) monthSelectorMobile.value = currentMonth;

        // Populate year selectors (last 5 years)
        for (let i = 0; i < 5; i++) {
            const year = currentYear - i;
            
            if (yearSelector) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === currentYear) option.selected = true;
                yearSelector.appendChild(option);
            }
            
            if (yearSelectorMobile) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === currentYear) option.selected = true;
                yearSelectorMobile.appendChild(option);
            }
        }

        // Load attendance data
        const loadAttendanceData = () => {
            const monthSelector = document.getElementById('month-selector');
            const yearSelector = document.getElementById('year-selector');
            const monthSelectorMobile = document.getElementById('month-selector-mobile');
            const yearSelectorMobile = document.getElementById('year-selector-mobile');
            
            const month = (monthSelector && monthSelector.value) || (monthSelectorMobile && monthSelectorMobile.value);
            const year = (yearSelector && yearSelector.value) || (yearSelectorMobile && yearSelectorMobile.value);
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';
            const branchParam = selectedSubAdminId && selectedSubAdminId !== 'null'
                ? `?selectedSubAdminId=${selectedSubAdminId}` : '';

            fetch(`/api/attendance/getAttendance/${month}/${year}${branchParam}`, { headers })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        effectiveTodayStr = normalizeDateString(data?.data?.serverToday) || toLocalDateString();
                        renderEmployeeTable(data.data, month, year);
                    }
                })
                .catch(error => {
                    console.error('Error loading attendance:', error);
                    document.getElementById('employee-attendance-body').innerHTML =
                        '<tr><td colspan="10" class="text-center text-danger">Error loading data</td></tr>';
                });
        };

        const renderEmployeeTable = (attendanceData, month, year) => {
            const tbody = document.getElementById('employee-attendance-body');
            tbody.innerHTML = '';

            const users = attendanceData.users;
            const holidays = attendanceData.holidays || [];
            const saturdayOffDates = attendanceData.saturdayOffDates || [];
            console.log(saturdayOffDates);
            // Calculate total working days (excluding future dates for current month/year)
            const totalDaysInMonth = new Date(year, month, 0).getDate();
            let workingDays = 0;
            const todayStr = effectiveTodayStr;
            const [currentYearNow, currentMonthNow] = todayStr.split('-').map(Number);
            const isCurrentMonthYear = parseInt(month) === currentMonthNow && parseInt(year) === currentYearNow;

            for (let day = 1; day <= totalDaysInMonth; day++) {
                const date = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                // For the current month/year, do not count future dates as working days
                if (isCurrentMonthYear && date > todayStr) {
                    continue;
                }
                const dayOfWeek = getDayOfWeekFromDateString(date);
                const isHoliday = holidays.some(h => h.holiday_date === date);
                const isSaturdayOff = saturdayOffDates.includes(date);
                if(attendanceData.isIncludedHoliday == "1"){
                    workingDays++;
                }else if (dayOfWeek !== 0 && !isHoliday && !isSaturdayOff) {
                    workingDays++;
                }
            }

            users.forEach(user => {
                const attendance = user.attendance || [];

                // For the current month/year, ignore future dates in stats so they are not counted as absent
                const effectiveAttendance = isCurrentMonthYear
                    ? attendance.filter(a => {
                        const d = normalizeDateString(a.date);
                        return d && d <= todayStr;
                    })
                    : attendance;

                // Calculate stats based on effective attendance only
                var presentDays = effectiveAttendance.filter(a => a.status === 'present').length;
                var holidayDays = effectiveAttendance.filter(a => a.status === 'holiday').length;
                var weekOffDays = effectiveAttendance.filter(a => a.status === 'Week Off').length;
                var presentDays = presentDays + holidayDays + weekOffDays;
                const halfDays = effectiveAttendance.filter(a => a.status === 'half-day').length;
                const leaveDays = effectiveAttendance.filter(a => a.status === 'leave').length;
                const absentDays = workingDays - presentDays - halfDays - leaveDays;

                // Calculate total work hours
                let totalSeconds = 0;
                let totalOvertimeSeconds = 0;
                let totalLateMinutes = 0;

                attendance.forEach(record => {
                    if (record.check_in_time && record.check_out_time) {
                        const workHours = calculateWorkHours(record);
                        totalSeconds += workHours;
                    }

                    if (record.overtime) {
                        totalOvertimeSeconds += timeToSeconds(record.overtime);
                    }

                    if (record.is_late && record.late_minutes) {
                        totalLateMinutes += parseInt(record.late_minutes);
                    }
                });

                const totalWorkHours = formatSeconds(totalSeconds);
                const totalOvertime = formatSeconds(totalOvertimeSeconds);
                const totalLateTime = formatMinutesToHours(totalLateMinutes);

                const profileImage = getProfileImageUrl(user.profile_image);

                // Employee row - Following the exact pattern from the second file
                const row = document.createElement('tr');
                row.className = 'employee-row';
                row.dataset.userId = user.user_id;
                row.innerHTML = `
                    <td class="py-1">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="${profileImage}" alt="Profile" onerror="this.onerror=null;this.src='${defaultImagePath}'"
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                            <span>${user.employee_name}</span>
                        </div>
                    </td>
                    <td class="desktop-only-col">${workingDays}</td>
                    <td class="desktop-only-col">${presentDays + (halfDays * 0.5)}</td>
                    <td class="desktop-only-col">${totalWorkHours}</td>
                    <td class="desktop-only-col">${totalOvertime || '0h 0m'}</td>
                    <td class="desktop-only-col">${totalLateTime || '0h 0m'}</td>
                    <td class="desktop-only-col">${leaveDays}</td>
                    <td class="desktop-only-col">${absentDays > 0 ? absentDays : 0}</td>
                    <td class="desktop-only-col">
                        <button class="btn btn-sm view-history-btn" data-user-id="${user.user_id}">
                            View History
                        </button>
                    </td>
                    <td class="mobile-expand-col text-center">
                        <button type="button" class="expand-toggle" aria-label="Expand details">
                            <span class="toggle-icon">+</span>
                        </button>
                    </td>
                `;

                // Expand details row (mobile only, shown when toggle clicked)
                const expandRow = document.createElement('tr');
                expandRow.className = 'expand-details-row d-md-none';
                expandRow.id = `expand-row-${user.user_id}`;
                expandRow.style.display = 'none';
                expandRow.innerHTML = `
                    <td colspan="2" style="padding: 0 12px 12px 12px; background: #f8f9fa;">
                        <div class="expanded-details show" id="employee-details-${user.user_id}">
                            <div class="detail-row">
                                <span class="detail-label">Total Days:</span>
                                <span class="detail-value">${workingDays}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Present Days:</span>
                                <span class="detail-value">${presentDays + (halfDays * 0.5)}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Work Hours:</span>
                                <span class="detail-value">${totalWorkHours}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Overtime:</span>
                                <span class="detail-value">${totalOvertime || '0h 0m'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Late Hours:</span>
                                <span class="detail-value">${totalLateTime || '0h 0m'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Leaves:</span>
                                <span class="detail-value">${leaveDays}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Absent:</span>
                                <span class="detail-value">${absentDays > 0 ? absentDays : 0}</span>
                            </div>
                            <div class="detail-actions">
                                <button class="btn btn-sm view-history-btn-mobile" data-user-id="${user.user_id}">
                                    <i class="mdi mdi-history"></i> View History
                                </button>
                            </div>
                        </div>
                    </td>
                `;

                // History row (hidden by default)
                const historyRow = document.createElement('tr');
                historyRow.className = 'history-row';
                historyRow.id = `history-${user.user_id}`;
                historyRow.innerHTML = `
                    <td colspan="10">
                        <div class="history-content">
                            <h6>Check-in History for ${user.employee_name}</h6>
                            <div id="history-data-${user.user_id}">
                                <div class="loading-spinner">Loading history...</div>
                            </div>
                        </div>
                    </td>
                `;

                tbody.appendChild(row);
                tbody.appendChild(expandRow);
                tbody.appendChild(historyRow);
            });

            // Add click event listeners for desktop view history buttons
            document.querySelectorAll('.view-history-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const userId = btn.dataset.userId;
                    toggleHistory(userId, month, year);
                });
            });

            // Add click event listeners for mobile view history buttons
            document.querySelectorAll('.view-history-btn-mobile').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const userId = btn.dataset.userId;
                    toggleHistory(userId, month, year);
                });
            });

            // Desktop row click for history (only on desktop)
            document.querySelectorAll('.employee-row').forEach(row => {
                row.addEventListener('click', (e) => {
                    // Only trigger on desktop and not when buttons are clicked
                    if (!e.target.closest('.expand-toggle') && 
                        !e.target.closest('.view-history-btn-mobile') && 
                        window.innerWidth >= 768) {
                        const userId = row.dataset.userId;
                        toggleHistory(userId, month, year);
                    }
                });
            });
        };

        const toggleHistory = (userId, month, year) => {
            const historyRow = document.getElementById(`history-${userId}`);
            const employeeRow = document.querySelector(`tr[data-user-id="${userId}"]`);

            // Close other open histories
            document.querySelectorAll('.history-row').forEach(row => {
                if (row.id !== `history-${userId}`) {
                    row.classList.remove('show');
                }
            });
            document.querySelectorAll('.employee-row').forEach(row => {
                if (row.dataset.userId !== userId) {
                    row.classList.remove('selected');
                }
            });

            // Toggle current history
            if (historyRow.classList.contains('show')) {
                historyRow.classList.remove('show');
                employeeRow.classList.remove('selected');
            } else {
                historyRow.classList.add('show');
                employeeRow.classList.add('selected');
                loadEmployeeHistory(userId, month, year);
            }
        };

        const loadEmployeeHistory = (userId, month, year) => {
            const historyContainer = document.getElementById(`history-data-${userId}`);
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';
            const branchParam = selectedSubAdminId && selectedSubAdminId !== 'null'
                ? `?selectedSubAdminId=${selectedSubAdminId}` : '';

            fetch(`/api/attendance/getAttendance/${month}/${year}${branchParam}`, { headers })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const user = data.data.users.find(u => u.user_id == userId);
                        if (user) {
                            renderHistory(historyContainer, user.attendance, data.data, month, year);
                        }
                    }
                })
                .catch(error => {
                    historyContainer.innerHTML = '<p class="text-danger">Error loading history</p>';
                });
        };

        const renderHistory = (container, attendance, attendanceData, month, year) => {
            const holidays = attendanceData.holidays || [];
            const saturdayOffDates = attendanceData.saturdayOffDates || [];
            const totalDaysInMonth = new Date(year, month, 0).getDate();
            const todayStr = effectiveTodayStr;

            let html = '<table class="history-table"><thead><tr>';
            html += '<th>Date</th><th>Check In</th><th>Check Out</th><th>Work Hours</th><th>Overtime</th><th>Late</th><th>Status</th>';
            html += '</tr></thead><tbody>';

            // Iterate backwards from today (or end of month) to the 1st
            const [currentYearNow, currentMonthNow] = todayStr.split('-').map(Number);
            const isCurrentMonthYear = parseInt(month) === currentMonthNow && parseInt(year) === currentYearNow;
            
            const startDay = isCurrentMonthYear ? parseInt(todayStr.split('-')[2]) : totalDaysInMonth;

            for (let day = startDay; day >= 1; day--) {
                const date = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const record = attendance.find(r => normalizeDateString(r.date) === date);
                
                let status = 'absent';
                let statusClass = 'status-absent';
                let checkIn = '-';
                let checkOut = '-';
                let workHours = '-';
                let overtime = '-';
                let lateInfo = '-';

                const dayOfWeek = getDayOfWeekFromDateString(date);
                const isHoliday = holidays.find(h => h.holiday_date === date);
                const isSaturdayOff = saturdayOffDates.includes(date);

                if (record) {
                    status = record.status;
                    statusClass = `status-${record.status}`;
                    checkIn = record.check_in_time || '-';
                    checkOut = record.check_out_time || '-';
                    workHours = calculateWorkHoursDisplay(record);
                    overtime = record.overtime ? formatTimeString(record.overtime) : '-';
                    lateInfo = record.is_late ? formatMinutesToHours(record.late_minutes) : '-';
                } else if (isHoliday) {
                    status = 'Holiday';
                    statusClass = 'status-leave'; // Or a separate holiday style if available
                } else if (dayOfWeek === 0 || isSaturdayOff) {
                    status = 'Week Off';
                    statusClass = 'status-leave'; // Using leave style for week off as well
                }

                html += `<tr>
                    <td>${formatDate(date)}</td>
                    <td>${checkIn}</td>
                    <td>${checkOut}</td>
                    <td>${workHours}</td>
                    <td>${overtime}</td>
                    <td>${lateInfo}</td>
                    <td><span class="status-badge ${statusClass}">${status}</span></td>
                </tr>`;
            }

            html += '</tbody></table>';
            container.innerHTML = html;
        };

        // Helper functions
        const calculateWorkHours = (record) => {
            if (!record.check_in_time || !record.check_out_time) return 0;
            const checkIn = new Date(`2000-01-01 ${record.check_in_time}`);
            const checkOut = new Date(`2000-01-01 ${record.check_out_time}`);
            return (checkOut - checkIn) / 1000;
        };

        const formatSeconds = (seconds) => {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return `${hours}h ${minutes}m`;
        };

        const formatMinutesToHours = (minutes) => {
            if (!minutes || minutes === 0) return '0h 0m';
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours}h ${mins}m`;
        };

        const timeToSeconds = (timeString) => {
            if (!timeString) return 0;
            const parts = timeString.split(':');
            const hours = parseInt(parts[0]) || 0;
            const minutes = parseInt(parts[1]) || 0;
            const seconds = parseInt(parts[2]) || 0;
            return (hours * 3600) + (minutes * 60) + seconds;
        };

        const formatTimeString = (timeString) => {
            if (!timeString || timeString === '00:00:00') return '0h 0m';
            const parts = timeString.split(':');
            const hours = parseInt(parts[0]) || 0;
            const minutes = parseInt(parts[1]) || 0;
            return `${hours}h ${minutes}m`;
        };

        const calculateWorkHoursDisplay = (record) => {
            if (!record.check_in_time || !record.check_out_time) return '-';
            const checkIn = new Date(`2000-01-01 ${record.check_in_time}`);
            const checkOut = new Date(`2000-01-01 ${record.check_out_time}`);
            const seconds = (checkOut - checkIn) / 1000;
            return formatSeconds(seconds);
        };

        const formatDate = (dateStr) => {
            const normalized = normalizeDateString(dateStr);
            if (!normalized) return '-';
            const [year, month, day] = normalized.split('-').map(Number);
            const date = new Date(year, month - 1, day);
            const options = { day: '2-digit', month: 'short', year: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        };

        // Event listeners for month/year change
        if (monthSelector) monthSelector.addEventListener('change', loadAttendanceData);
        if (yearSelector) yearSelector.addEventListener('change', loadAttendanceData);
        if (monthSelectorMobile) {
            monthSelectorMobile.addEventListener('change', () => {
                if (monthSelector) monthSelector.value = monthSelectorMobile.value;
                loadAttendanceData();
            });
        }
        if (yearSelectorMobile) {
            yearSelectorMobile.addEventListener('change', () => {
                if (yearSelector) yearSelector.value = yearSelectorMobile.value;
                loadAttendanceData();
            });
        }

        // Add expand/collapse functionality for mobile
        document.addEventListener('click', (e) => {
            if (e.target.closest('.expand-toggle')) {
                const button = e.target.closest('.expand-toggle');
                const userId = button.closest('tr').dataset.userId;
                const expandRow = document.getElementById(`expand-row-${userId}`);
                const icon = button.querySelector('.toggle-icon');

                if (expandRow) {
                    const isVisible = expandRow.style.display !== 'none';
                    expandRow.style.display = isVisible ? 'none' : 'table-row';
                    button.classList.toggle('active', !isVisible);
                    if (icon) icon.textContent = isVisible ? '+' : '−';
                }
            }
        });

        // Initial load
        loadAttendanceData();
    });
</script>

@endsection
