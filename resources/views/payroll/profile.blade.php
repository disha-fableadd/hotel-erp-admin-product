@extends('layout.app')
@section('title', 'Payroll Profile')
@section('content')
<style>
    :root {
        --payroll-primary: #ff9f43;
        --payroll-primary-soft: #fff1eb;
        --payroll-text: #1f2937;
        --payroll-muted: #6b7280;
        --payroll-border: #edf0f4;
        --payroll-card-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    }

    .capitalize-text {
        text-transform: capitalize;
    }

    .payroll-profile-card {
        border: 1px solid var(--payroll-border);
        border-radius: 8px;
        box-shadow: var(--payroll-card-shadow);
    }

    .payroll-profile-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 0 0 18px;
        background: transparent;
        border-bottom: 1px solid var(--payroll-border);
        margin-bottom: 18px;
    }

    .payroll-eyebrow {
        color: var(--payroll-primary);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .payroll-profile-title {
        color: var(--payroll-text);
        font-size: 28px;
        font-weight: 800;
        margin: 0;
    }

    .payroll-profile-subtitle {
        color: var(--payroll-muted);
        margin: 7px 0 0;
        font-size: 14px;
    }

    .payroll-tab-wrap {
        padding: 0;
        background: #fff;
    }

    .payroll-tabs {
        gap: 10px;
        border: 0;
    }

    .payroll-tabs .nav-link {
        border: 1px solid var(--payroll-border);
        border-radius: 999px;
        color: var(--payroll-muted);
        font-weight: 700;
        padding: 10px 18px;
        background: #fff;
    }

    .payroll-tabs .nav-link.active {
        background: var(--payroll-primary);
        border-color: var(--payroll-primary);
        color: #fff;
        /* box-shadow: 0 10px 20px rgba(255, 159, 67, 0.22); */
    }

    .payroll-content {
        padding: 20px 0 0;
        background: #fff;
    }

    .payroll-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--payroll-text);
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 18px;
    }

    .payroll-section-title i,
    .payroll-info-icon {
        color: var(--payroll-primary);
    }

    .employee-profile-card {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr);
        gap: 24px;
        align-items: center;
        padding: 24px;
        border: 1px solid var(--payroll-border);
        border-radius: 20px;
        background: #fff;
        /* box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05); */
    }

    .employee-photo-wrap {
        position: relative;
        width: 126px;
        height: 126px;
        border-radius: 32px;
        padding: 8px;
        background: linear-gradient(135deg, var(--payroll-primary), #ffb36b);
        /* box-shadow: 0 16px 28px rgba(255, 159, 67, 0.2); */
    }

    .employee-photo {
        width: 110px !important;
        height: 110px !important;
        border-radius: 26px !important;
        object-fit: cover !important;
        background: #f5f6f8;
        border: 4px solid #fff;
    }

    .employee-name {
        font-size: 24px;
        font-weight: 800;
        color: var(--payroll-text);
        margin-bottom: 4px;
    }

    .employee-designation {
        color: var(--payroll-muted);
        margin-bottom: 18px;
    }

    .employee-info-grid,
    .salary-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .info-tile {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px;
        border: 1px solid var(--payroll-border);
        border-radius: 16px;
        background: #fbfcfe;
    }

    .info-tile-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: var(--payroll-primary-soft);
        color: var(--payroll-primary);
        flex: 0 0 auto;
    }

    .info-label {
        display: block;
        color: var(--payroll-muted);
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 3px;
    }

    .info-value {
        color: var(--payroll-text);
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .salary-summary-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        padding: 20px;
        border-radius: 20px;
        background: linear-gradient(135deg, #172033 0%, #28344f 100%);
        color: #fff;
        /* box-shadow: 0 16px 34px rgba(23, 32, 51, 0.18); */
    }

    .salary-summary-card .summary-label {
        color: rgba(255, 255, 255, 0.72);
        font-size: 13px;
        font-weight: 700;
    }

    .salary-summary-card .summary-value {
        display: block;
        font-size: 30px;
        font-weight: 900;
        line-height: 1.1;
        margin-top: 4px;
    }

    .salary-summary-card i {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.12);
        color: #ffb36b;
        font-size: 24px;
    }

    .salary-info-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .salary-info-grid .info-tile {
        min-height: 86px;
    }

    @media (max-width: 767px) {
        .payroll-profile-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 16px;
        }

        .payroll-profile-title {
            font-size: 22px;
        }

        .payroll-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .payroll-tabs .nav-link {
            width: 100%;
            padding: 9px 8px;
            font-size: 13px;
        }

        .employee-profile-card {
            grid-template-columns: 1fr;
            text-align: center;
            padding: 18px;
        }

        .employee-photo-wrap {
            margin: 0 auto;
        }

        .employee-info-grid,
        .salary-info-grid {
            grid-template-columns: 1fr;
            text-align: left;
        }

        .salary-summary-card {
            align-items: flex-start;
            padding: 18px;
        }

        .salary-summary-card .summary-value {
            font-size: 24px;
        }

        .interviewsmbtn {
            font-size: 13px !important;
            padding: 9px 14px !important;
            margin-top: 10px !important;
        }

        .sm-margins-size {
            font-size: 13px !important;
        }

        .tab-content {
            overflow: hidden;
        }

        .tab-pane {
            overflow-x: auto;

        }

        .fontsize-sm-payr {
            font-size: 13px !important;
        }

        .emp-photo-profile {
            width: 110px !important;
            height: 110px !important;
        }

    }

    .hr-btnbg {
        background: #ff9f43 !important;
        border-color: #ff9f43 !important;
        color: #fff !important;
        border-radius: 4px;
        padding: 8px 15px;
        font-weight: 500;
        box-shadow: none;
    }

    .hr-btnbg:hover {
        background: #ff9f43 !important;
        border-color: #ff9f43 !important;
        color: #fff !important;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="{{ url(env('ImagePath') . 'assets/css/payroll.css') }}">
<div class="content">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card payroll-profile-card">
                <div class="card-body">
                    <div class="payroll-profile-hero">
                        <div>
                            <div class="payroll-eyebrow"><i class="fas fa-money-check me-1"></i> Payroll Details</div>
                            <h4 class="payroll-profile-title">Employee Payroll Profile</h4>
                            <p class="payroll-profile-subtitle">Review employee information, attendance impact, and salary summary.</p>
                        </div>
                        <a href="{{ url('/payrollview') }}" class="btn hr-btnbg interviewsmbtn">
                            <i class="fas fa-arrow-left me-1 iconfontsize"></i> Back
                        </a>
                    </div>

                    <!-- Tabs -->
                    <div class="payroll-tab-wrap">
                        <ul class="nav nav-tabs payroll-tabs sm-margins-size" id="payrollTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button" role="tab">
                                <i class="mdi mdi-account me-1"></i> Employee Info
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab">
                                <i class="mdi mdi-cash-multiple me-1"></i> Salary
                            </button>
                        </li>
                    </ul>
                </div>


                <!-- Tab Content -->
                <div class="tab-content payroll-content">
                    <!-- Employee Info Tab -->
                    <div class="tab-pane fade show active" id="employee" role="tabpanel">
                        <div class="payroll-section-title"><i class="fas fa-user-circle"></i> Employee Information</div>
                        <div class="employee-profile-card">
                            <div class="employee-photo-wrap">
                                <img id="employeePhoto" class="employee-photo shadow emp-photo-profile" alt="Employee photo">
                            </div>
                            <div>
                                <div class="employee-name capitalize-text" id="employeeName"></div>
                                <div class="employee-designation capitalize-text" id="employeeDesignation"></div>
                                <div class="employee-info-grid">
                                    <div class="info-tile">
                                        <span class="info-tile-icon"><i class="fas fa-id-badge"></i></span>
                                        <div>
                                            <span class="info-label">Employee ID</span>
                                            <span class="info-value capitalize-text">EMP# <span id="employee_id"></span></span>
                                        </div>
                                    </div>
                                    <div class="info-tile">
                                        <span class="info-tile-icon"><i class="fas fa-envelope"></i></span>
                                        <div>
                                            <span class="info-label">Email Address</span>
                                            <span class="info-value capitalize-text" id="employeeEmail"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Salary Breakdown Tab -->
                    <div class="tab-pane fade" id="salary" role="tabpanel">
                        <div class="payroll-section-title"><i class="fas fa-file-invoice-dollar"></i> Salary</div>
                        <div class="salary-summary-card">
                            <div>
                                <span class="summary-label">Net Salary</span>
                                <span class="summary-value" id="net_salary"></span>
                            </div>
                            <i class="fas fa-wallet"></i>
                        </div>

                        <div class="salary-info-grid">
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-cash"></i></span>
                                <div><span class="info-label">Base Salary</span><span id="salary_amount" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-calendar-month"></i></span>
                                <div><span class="info-label">Month & Year</span><span id="month_year" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-calendar-remove"></i></span>
                                <div><span class="info-label">Total Applied Leaves</span><span id="total_leaves" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-calendar-check"></i></span>
                                <div><span class="info-label">Total Half-day Leaves</span><span id="total_hald_day_leaves" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-calendar-week"></i></span>
                                <div><span class="info-label">Working Days</span><span id="working_days" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-clock-outline"></i></span>
                                <div><span class="info-label">Worked Hours</span><span id="worked_hours" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-clock-plus-outline"></i></span>
                                <div><span class="info-label">Overtime Hours</span><span id="overtime_hours" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-cash-plus"></i></span>
                                <div><span class="info-label">Overtime Pay</span><span id="overtime_pay" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-gift-outline"></i></span>
                                <div><span class="info-label">Bonuses</span><span id="bonuses" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-percent"></i></span>
                                <div><span class="info-label">Tax Deduction</span><span id="tax_deduction" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-percent"></i></span>
                                <div>
                                    <span class="info-label">Salary Deduction</span>
                                    <span id="salary_deduction" class="info-value capitalize-text"></span>
                                    <button type="button" class="btn btn-sm btn-link text-primary ms-1 p-0 align-middle" id="profileDeductionInfoBtn" title="Why is this amount deducted?" style="display:none;">
                                        <i class="mdi mdi-information-outline" style="font-size:1.2rem;"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-check-circle-outline"></i></span>
                                <div><span class="info-label">Payment Status</span><span id="payment_status" class="info-value capitalize-text"></span></div>
                            </div>
                            <div class="info-tile">
                                <span class="info-tile-icon"><i class="mdi mdi-calendar-outline"></i></span>
                                <div><span class="info-label">Payment Date</span><span id="payment_date" class="info-value capitalize-text"></span></div>
                            </div>
                        </div>

                        <!-- Deduction breakdown modal (same calculation as Manage Salary & Add Payroll) -->
                        <div class="modal fade" id="profileDeductionBreakdownModal" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="profileDeductionBreakdownModalLabel"><i class="mdi mdi-information-outline me-1"></i> Why was this amount deducted?</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="profileDeductionBreakdownLoading" style="display:block;">Loading...</div>
                                        <div id="profileDeductionBreakdownContent" style="display:none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="error-message" class="text-danger text-center mb-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
<script>
    $(document).ready(function() {
        const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
        const payrollId = '{{ $payrollId ?? '' }}';
        const userId = payrollId || window.location.pathname.split('/').filter(Boolean).pop();
        console.log("Payroll Record ID:", userId);

        if (!userId) {
            $("#error-message").text("Payroll ID is missing or invalid.").show();
            return;
        }

        const headers = { 'Accept': 'application/json' };
        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        const apiUrl = '{{ url('/api/payroll/details') }}' + '/' + encodeURIComponent(userId);
        console.log('Payroll details API URL:', apiUrl);

        $.ajax({
            url: apiUrl,
            type: "GET",
            headers: headers,
            success: function(response) {
                if (response.status === 'success') {
                    const d = response.data;
                    const salary_above_tax = response.salary_above_tax;
                    const tax = response.tax;
                    // console.log(d);


                    // Employee Details
                    $("#employeeName").text(d.firstname + ' ' + d.lastname);
                    $("#employee_id").text(d.employee_id ?? 'N/A');
                    $("#employeeEmail").text(d.email ?? 'N/A');
                    $("#employeeDesignation").text(d.designation ?? 'N/A');

                    const profileImg = d.profile_image ?
                        "{{ env('ImagePath') . '/storage/' }}" + d.profile_image :
                        "{{ env('ImagePath') . '/admin/assets/img/customer/customer5.jpg' }}";

                    $("#employeePhoto").attr("src", profileImg);

                    // Salary Details
                    $("#salary_amount").text(`\u20B9${parseFloat(d.salary_amount || 0).toFixed(2)}`);
                    $("#month_year").text(d.month_year ?? 'N/A');
                    $("#total_leaves").text(d.total_leaves ?? '0');
                    $("#total_paid_leaves").text(d.total_paid_leaves ?? '0');
                    $("#total_hald_day_leaves").text(d.total_half_day ?? '0');
                    $("#leave_type").text(d.leave_type ?? 'N/A');
                    $("#remaining_paid_leaves").text(d.remaining_paid_leaves ?? '0');
                    $("#used_paid_leaves").text(d.used_paid_leaves ?? '0');
                    $("#unpaid_leaves").text(d.unpaid_leaves ?? 'N/A');
                    $("#working_days").text(typeof d.working_days !== 'undefined' && d.working_days !== null ? d.working_days : 'N/A');
                    $("#worked_hours").text(typeof d.worked_hours !== 'undefined' && d.worked_hours !== null ? d.worked_hours + ' hours' : 'N/A');
                    $("#overtime_hours").text(d.total_overtime_hours ? d.total_overtime_hours + ' hours' : 'N/A');
                    $("#overtime_pay").text(d.overtime_pay ? `+\u20B9${parseFloat(d.overtime_pay).toFixed(2)}` : 'N/A');
                    $("#bonuses").text(d.bonuses ? `+\u20B9${parseFloat(d.bonuses).toFixed(2)}` : 'N/A');

                    let taxDeduction = d.tax_deduction || 0;
                    $("#tax_deduction").text(taxDeduction ? `-\u20B9${parseFloat(taxDeduction).toFixed(2)}` : 'N/A');
                    $("#salary_deduction").text(d.salary_deduction ? `-\u20B9${parseFloat(d.salary_deduction).toFixed(2)}` : 'N/A');
                    // $("#tax_deduction").text(d.tax_deduction ? `-\u20B9${parseFloat(d.tax_deduction).toFixed(2)}` : 'N/A');
                    $("#payment_status").text(d.payment_status ?? 'Pending');
                    $("#payment_date").text(d.payment_date ?? 'N/A');
                    $("#net_salary").text(`\u20B9${parseFloat(d.net_salary || 0).toFixed(2)}`);

                    // Show deduction breakdown button and store ids for same calculation as group/single
                    if (d.user_id && d.month_year) {
                        $('#profileDeductionInfoBtn').data('user-id', d.user_id).data('month-year', d.month_year).show();
                    }

                    // Bank Details
                    $("#acc_in_name").text(d.acc_in_name ?? 'N/A');
                    $("#bank_name").text(d.bank_name ?? 'N/A');
                    $("#ifsc_code").text(d.ifsc_code ?? 'N/A');
                    $("#acc_number").text(d.acc_number ?? 'N/A');
                    $("#branch_name").text(d.branch_name ?? 'N/A');
                    $("#branch_code").text(d.branch_code ?? 'N/A');
                } else {
                    $("#error-message").text("Error fetching payroll details.").show();
                }
            },
            error: function() {
                $("#error-message").text("An error occurred while fetching data.").show();
            }
        });

        // Deduction breakdown – touchend (mobile) and click (desktop)
        var profileDeductionLastTouch = 0;
        function openProfileDeductionModal() {
            const userId = $('#profileDeductionInfoBtn').data('user-id');
            const month = $('#profileDeductionInfoBtn').data('month-year');
            if (!userId || !month) return;
            const modal = document.getElementById('profileDeductionBreakdownModal');
            const loading = document.getElementById('profileDeductionBreakdownLoading');
            const content = document.getElementById('profileDeductionBreakdownContent');
            if (!modal) return;
            document.getElementById('profileDeductionBreakdownModalLabel').innerHTML = '<i class="mdi mdi-information-outline me-1"></i> Why was this amount deducted?';
            loading.style.display = 'block';
            content.style.display = 'none';
            content.innerHTML = '';
            if (modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
            $.ajax({
                url: '{{ url("api/payroll/get-deduction-breakdown") }}',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                        html += '<span class="text-danger">Deduction: &#8377;' + (d.leaves.deduction_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.absent && d.absent.dates && d.absent.dates.length) {
                        html += '<div class="breakdown-section" style="border-left:3px solid #E66136;padding-left:0.75rem;margin-bottom:1rem;"><strong>Absent</strong><ul class="list-unstyled small mb-1" style="max-height:120px;overflow-y:auto;">';
                        d.absent.dates.forEach(function(a) { html += '<li>' + (a.label || a.date) + '</li>'; });
                        html += '</ul><span class="text-danger">Deduction: &#8377;' + (d.absent.deduction_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.half_day && d.half_day.count > 0) {
                        html += '<div class="breakdown-section" style="border-left:3px solid #E66136;padding-left:0.75rem;margin-bottom:1rem;"><strong>Half-day</strong>';
                        if (d.half_day.dates && d.half_day.dates.length) {
                            html += '<ul class="list-unstyled small mb-1" style="max-height:120px;overflow-y:auto;">';
                            d.half_day.dates.forEach(function(h) { html += '<li>' + (h.label || h.date) + '</li>'; });
                            html += '</ul>';
                        }
                        html += '<span class="text-danger">Deduction: &#8377;' + (d.half_day.deduction_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.late && d.late.list && d.late.list.length) {
                        html += '<div class="breakdown-section" style="border-left:3px solid #E66136;padding-left:0.75rem;margin-bottom:1rem;"><strong>Late arrival</strong><ul class="list-unstyled small" style="max-height:120px;overflow-y:auto;">';
                        d.late.list.forEach(function(l) { html += '<li>' + (l.label || l.date) + ' – ' + (l.late_text || l.late_minutes + ' min') + '</li>'; });
                        html += '</ul><span class="text-danger">Deduction: &#8377;' + (d.late.deduction_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.overtime && d.overtime.list && d.overtime.list.length) {
                        html += '<div class="breakdown-section" style="border-left:3px solid #E66136;padding-left:0.75rem;margin-bottom:1rem;"><strong class="text-success">Overtime</strong><ul class="list-unstyled small" style="max-height:120px;overflow-y:auto;">';
                        d.overtime.list.forEach(function(o) { html += '<li>' + (o.label || o.date) + ' – ' + (o.overtime_text || o.overtime_hours + 'h') + '</li>'; });
                        html += '</ul><span class="text-success">Added to salary: &#8377;' + (d.overtime.pay_amount || 0).toFixed(2) + '</span></div>';
                    }
                    if (d.summary) {
                        html += '<hr><div class="fw-bold"><span>Total deduction (leaves + half-day + late):</span> <span class="text-danger">&#8377;' + (d.summary.total_deduction || 0).toFixed(2) + '</span></div>';
                        if (d.summary.overtime_added > 0) html += '<div class="fw-bold"><span>Overtime added:</span> <span class="text-success">&#8377;' + d.summary.overtime_added.toFixed(2) + '</span></div>';
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
        $(document).on('touchend', '#profileDeductionInfoBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            profileDeductionLastTouch = Date.now();
            openProfileDeductionModal();
        });
        $(document).on('click', '#profileDeductionInfoBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (Date.now() - profileDeductionLastTouch < 400) return;
            openProfileDeductionModal();
        });
    });
</script>
@endpush

@endsection


