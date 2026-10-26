@extends('layout.app')
@section('title', 'Manage Attendance')

@section('content')
<style>
/* ====================================================
   PAGE LAYOUT
   ==================================================== */
.ma-page { padding: 20px 24px; }

.ma-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 22px;
}
.ma-header h4 {
    font-size: 20px;
    font-weight: 700;
    color: #1b2850;
    margin: 0;
}
.ma-header h4 i { color: #ff6b35; margin-right: 8px; }

.btn-back {
    background: #ff9f43;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 16px;
    font-size: 13px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .2s;
}
.btn-back:hover { background: #e68e35; color: #fff; }

/* ====================================================
   FILTER BAR
   ==================================================== */
.ma-filter-card {
    background: #fff;
    border: 1px solid #e5e8f0;
    border-radius: 10px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}
.ma-filter-card label {
    font-size: 13px;
    font-weight: 600;
    color: #1b2850;
    margin: 0;
    white-space: nowrap;
}
.ma-filter-card select {
    font-size: 13px;
    border-radius: 6px;
    border: 1px solid #ced4da;
    padding: 6px 10px;
    min-width: 130px;
}
.btn-apply {
    background: #ff6b35;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s;
    white-space: nowrap;
}
.btn-apply:hover { background: #e55a25; }

.ma-period-badge {
    margin-left: auto;
    background: #f0f3ff;
    color: #1b2850;
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #d0d8ff;
}

/* ====================================================
   SUMMARY TABLE
   ==================================================== */
.ma-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
}

.att-summary-table { width: 100%; border-collapse: collapse; }
.att-summary-table thead th {
    background: #1b2850;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .4px;
    padding: 13px 14px;
    white-space: nowrap;
    vertical-align: middle;
    text-transform: uppercase;
}
.att-summary-table tbody td {
    padding: 12px 14px;
    font-size: 13px;
    color: #3a3f5c;
    border-bottom: 1px solid #f0f3f8;
    vertical-align: middle;
}
.att-summary-table tbody tr.emp-summary-row:hover { background: #f8f9ff; }

.emp-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 10px;
    border: 2px solid #e0e6ff;
}
.emp-name { font-weight: 600; color: #1b2850; }

.stat-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.badge-present  { background: #e8f8ee; color: #27ae60; }
.badge-absent   { background: #fff0f0; color: #e74c3c; }
.badge-hours    { background: #eef4ff; color: #2c5fe3; }
.badge-ot       { background: #fff8e6; color: #e67e22; }
.badge-late     { background: #fdf0f0; color: #c0392b; }

.btn-view-history {
    background: #ff9f43;
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 6px 16px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all .2s;
}
.btn-view-history:hover { background: #e68e35; }
.btn-view-history.active { background: #e67e22; }

/* ====================================================
   HISTORY EXPAND ROW
   ==================================================== */
.history-expand-row { display: none; }
.history-expand-row td {
    padding: 0 !important;
    background: #f5f8ff;
    border-bottom: 2px solid #17a2b8 !important;
}
.history-expand-inner {
    padding: 20px;
    background: #f8f9fa;
    border-left: 4px solid #17a2b8;
    width: 100%;
    display: block;
    box-sizing: border-box;
}
.history-expand-inner .history-title {
    font-size: 14px;
    font-weight: 700;
    color: #1b2850;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.history-expand-inner .history-title i { color: #17a2b8; }

.att-history-table {
    width: 100% !important;
    border-collapse: collapse;
    table-layout: fixed;
}
.att-history-table thead th {
    background: #2c3e6e;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: 10px 12px;
    white-space: nowrap;
    text-align: center;
}
.att-history-table thead th:first-child { text-align: left; }
.att-history-table tbody td {
    font-size: 12px;
    padding: 10px 12px;
    color: #3a3f5c;
    white-space: nowrap;
    border-bottom: 1px solid #e8edf5;
    text-align: center;
}
.att-history-table tbody td:first-child { text-align: left; }
.att-history-table tbody tr:nth-child(even) { background: #eef2fb; }

.status-present  { background: #e8f8ee; color: #27ae60; padding: 3px 10px; border-radius: 4px; font-weight: 600; display: inline-block; }
.status-absent   { background: #fff0f0; color: #e74c3c; padding: 3px 10px; border-radius: 4px; font-weight: 600; display: inline-block; }
.status-weekoff  { background: #f1f5f9; color: #64748b; padding: 3px 10px; border-radius: 4px; font-weight: 600; display: inline-block; }
.status-halfday  { background: #fff8e6; color: #e67e22; padding: 3px 10px; border-radius: 4px; font-weight: 600; display: inline-block; }
.status-dash     { color: #adb5bd; }

/* ====================================================
   LOADING / EMPTY STATES
   ==================================================== */
.ma-state-row td {
    text-align: center;
    padding: 50px 20px !important;
    color: #adb5bd;
    font-size: 14px;
}
.ma-spinner {
    display: inline-block;
    width: 20px; height: 20px;
    border: 3px solid #e0e6ff;
    border-top-color: #1b2850;
    border-radius: 50%;
    animation: spin .7s linear infinite;
    vertical-align: middle;
    margin-right: 8px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ====================================================
   RESPONSIVE
   ==================================================== */
@media (max-width: 768px) {
    .ma-page { padding: 12px; }
    .att-summary-table, .att-history-table { font-size: 11px; }
}
</style>

<div class="content ma-page">

    {{-- Header --}}
    <div class="ma-header">
        <h4><i class="fas fa-chart-bar"></i>Employee Attendance Summary</h4>
        <a href="{{ route('attendance.list') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> All Attendance
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="ma-filter-card">
        <label><i class="fas fa-calendar-alt me-1"></i>Month:</label>
        <select id="ma-month">
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
        <label>Year:</label>
        <select id="ma-year">
            @for ($y = date('Y'); $y >= date('Y') - 3; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </select>
        <button class="btn-apply" id="ma-apply">
            <i class="fas fa-search me-1"></i> Apply
        </button>
        <span class="ma-period-badge" id="ma-period-label">Select a period and click Apply</span>
    </div>

    {{-- Summary Table --}}
    <div class="ma-card">
        <div class="table-responsive">
            <table class="att-summary-table" id="ma-summary-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Total Days</th>
                        <th>Present Days</th>
                        <th>Work Hours</th>
                        <th>Overtime</th>
                        <th>Late Hours</th>
                        <th>Leaves</th>
                        <th>Absent</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ma-summary-tbody">
                    <tr class="ma-state-row">
                        <td colspan="9">
                            Select a month and year above, then click <strong>Apply</strong> to load attendance data.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@php $fallbackAvatar = url(env('ImagePath', '/') . 'admin/assets/img/profiles/avatar-02.jpg'); @endphp
@push('js')
<script>
$(document).ready(function() {

    var fallbackAvatar = '{{ $fallbackAvatar }}';

    // Pre-select current month / year
    var now = new Date();
    $('#ma-month').val(now.getMonth() + 1);
    $('#ma-year').val(now.getFullYear());

    var monthNames = ['January','February','March','April','May','June',
                      'July','August','September','October','November','December'];

    /* --------------------------------------------------------
       LOAD SUMMARY
    -------------------------------------------------------- */
    function loadSummary() {
        var month = $('#ma-month').val();
        var year  = $('#ma-year').val();

        $('#ma-period-label').text(monthNames[month - 1] + ' ' + year);

        $('#ma-summary-tbody').html(
            '<tr class="ma-state-row"><td colspan="9">' +
            '<span class="ma-spinner"></span>Loading attendance data…</td></tr>'
        );

        $.ajax({
            url: '{{ route("attendance.manage.summary") }}',
            method: 'GET',
            data: { month: month, year: year },
            success: function(res) {
                if (!res.status || !res.summary || res.summary.length === 0) {
                    $('#ma-summary-tbody').html(
                        '<tr class="ma-state-row"><td colspan="9">' +
                        '<i class="fas fa-users-slash me-2"></i>No staff found for this period.</td></tr>'
                    );
                    return;
                }

                var html = '';
                $.each(res.summary, function(i, emp) {
                    html +=
                        '<tr class="emp-summary-row" data-emp-id="' + emp.id + '">' +
                        '<td>' +
                            '<div style="display:flex;align-items:center;">' +
                                '<img src="' + emp.photo + '" class="emp-avatar" ' +
                                     'onerror="this.onerror=null;this.src=\'' + fallbackAvatar + '\'">' +
                                '<span class="emp-name">' + emp.name + '</span>' +
                            '</div>' +
                        '</td>' +
                        '<td><span class="stat-badge badge-hours">' + emp.total_days + ' days</span></td>' +
                        '<td><span class="stat-badge badge-present">' + emp.present_days + '</span></td>' +
                        '<td><span class="stat-badge badge-hours">' + emp.work_hours + '</span></td>' +
                        '<td><span class="stat-badge badge-ot">' + emp.overtime + '</span></td>' +
                        '<td><span class="stat-badge badge-late">' + emp.late_hours + '</span></td>' +
                        '<td>' + emp.leaves + '</td>' +
                        '<td><span class="stat-badge badge-absent">' + emp.absent + '</span></td>' +
                        '<td>' +
                            '<button class="btn-view-history" ' +
                                'data-emp-id="' + emp.id + '" ' +
                                'data-emp-name="' + emp.name + '">' +
                                'View History' +
                            '</button>' +
                        '</td>' +
                        '</tr>' +
                        '<tr class="history-expand-row" id="history-row-' + emp.id + '">' +
                            '<td colspan="9" style="padding:0; margin:0;">' +
                                '<div class="history-expand-inner">' +
                                    '<div class="history-title">' +
                                        '<i class="fas fa-history"></i>' +
                                        'Check-in History for ' + emp.name +
                                    '</div>' +
                                    '<div id="history-content-' + emp.id + '" style="text-align:center;padding:20px;">' +
                                        '<span class="ma-spinner"></span>Loading…' +
                                    '</div>' +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                });

                $('#ma-summary-tbody').html(html);
            },
            error: function(xhr) {
                var msg = 'Failed to load data.';
                if (xhr.status === 403) msg = 'Access denied.';
                if (xhr.status === 500) msg = 'Server error. Check your PHP logs.';
                $('#ma-summary-tbody').html(
                    '<tr class="ma-state-row"><td colspan="9">' +
                    '<i class="fas fa-exclamation-circle me-2 text-danger"></i>' + msg + '</td></tr>'
                );
            }
        });
    }

    // Auto-load on page ready
    loadSummary();

    $('#ma-apply').on('click', function() { loadSummary(); });

    /* --------------------------------------------------------
       VIEW HISTORY — Expandable Row
    -------------------------------------------------------- */
    $(document).on('click', '.btn-view-history', function() {
        var empId   = $(this).data('emp-id');
        var empName = $(this).data('emp-name');
        var $row    = $('#history-row-' + empId);

        // Toggle off if already open
        if ($row.hasClass('is-expanded')) {
            $row.hide().removeClass('is-expanded');
            $(this).removeClass('active').text('View History');
            return;
        }

        // Collapse all others
        $('.history-expand-row').hide().removeClass('is-expanded');
        $('.btn-view-history').removeClass('active').text('View History');

        // Expand this one
        $row.css('display', 'table-row').addClass('is-expanded');
        $(this).addClass('active').text('Hide History');

        var month = $('#ma-month').val();
        var year  = $('#ma-year').val();
        var $container = $('#history-content-' + empId);

        $container.html('<span class="ma-spinner"></span>Loading history for ' + empName + '…');

        $.ajax({
            url: '{{ url("/attendance/manage/history") }}/' + empId,
            method: 'GET',
            data: { month: month, year: year },
            dataType: 'json',
            success: function(res) {
                if (!res) {
                    $container.html('<p style="color:#e74c3c;padding:16px 0;">Server returned an empty response.</p>');
                    return;
                }
                if (!res.status || !res.records || res.records.length === 0) {
                    $container.html('<p style="color:#adb5bd;padding:16px 0;">No records found for this period.</p>');
                    return;
                }

                var rows = '';
                $.each(res.records, function(i, r) {
                    var cls = 'status-dash';
                    if (r.status === 'present')  cls = 'status-present';
                    if (r.status === 'absent')   cls = 'status-absent';
                    if (r.status === 'Week Off') cls = 'status-weekoff';
                    if (r.status === 'Half Day') cls = 'status-halfday';

                    var checkIn   = r.check_in   ? r.check_in   : '-';
                    var checkOut  = r.check_out  ? r.check_out  : '-';
                    var workHours = r.work_hours  ? r.work_hours  : '-';
                    var overtime  = r.overtime   ? r.overtime   : '-';
                    var late      = r.late       ? r.late       : '-';
                    var stat      = r.status     ? r.status     : '-';

                    rows +=
                        '<tr>' +
                        '<td><strong>' + r.date + '</strong></td>' +
                        '<td>' + checkIn  + '</td>' +
                        '<td>' + checkOut + '</td>' +
                        '<td>' + workHours + '</td>' +
                        '<td>' + overtime  + '</td>' +
                        '<td>' + late      + '</td>' +
                        '<td><span class="' + cls + '">' + stat + '</span></td>' +
                        '</tr>';
                });

                $container.html(
                    '<div class="table-responsive">' +
                    '<table class="att-history-table">' +
                    '<thead><tr>' +
                        '<th>Date</th><th>Check In</th><th>Check Out</th>' +
                        '<th>Work Hours</th><th>Overtime</th><th>Late</th><th>Status</th>' +
                    '</tr></thead>' +
                    '<tbody>' + rows + '</tbody>' +
                    '</table></div>'
                );
            },
            error: function(xhr, status, error) {
                var msg = "Failed to load history. Please try again.";
                if (xhr.status === 404) msg = "Endpoint not found (404).";
                else if (xhr.status === 500) msg = "Server error (500). Please check PHP logs.";
                else if (error) msg = "Error: " + error;

                $container.html('<p style="color:#e74c3c;padding:16px 0;">' + msg + '</p>');
            }
        });
    });

});
</script>
@endpush
