<!-- Group Payroll Management -->
@extends('layout.app')
@section('title', 'Salary Details')
@section('content')
    <style>
        .capitalize-text {
            text-transform: capitalize;
        }

        .salary-manage {
            display: flex;
        }

        .save-all {
            position: absolute;
            top: 22px;
            right: 29px;
        }

        .slarypadding {
            padding: 2px;
        }

        @media (max-width: 767px) {
            .card-body {
                padding: 12px !important;
            }

            .cart-sm-title {
                font-size: 12px !important;
                margin-bottom: 5px !important;
            }

            .attendenceall {
                font-size: 13px !important;
                padding: 8px 14px !important;
            }

            .slarypadding {
                padding: 0px !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }

            .iconfontsize {
                font-size: 13px !important;
            }

            /* .salary-manage {
          display: unset;
        } */

            .salary-manage {
                display: grid;
                grid-template-columns: 1fr auto;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }

            .salary-manage .card-title {
                font-size: 14px !important;
                line-height: 1.3;
                margin: 0 !important;
                white-space: normal;
            }

            .salary-manage #salaryMonth {
                min-width: 128px;
                height: 34px;
                font-size: 13px;
            }

            .salary-back-wrap {
                grid-column: 1 / 2;
            }

            .salary-manage a.btn-added {
                margin-right: 0 !important;
                white-space: nowrap;
            }

            /* .salary-control-size {
          height: 27px !important;
        } */

            .save-all {
                position: static !important;
                white-space: nowrap;
                font-size: 13px !important;
                padding: 8px 14px !important;
            }

            .individual-save-form>.text-end {
                justify-content: flex-end !important;
                margin-top: -44px;
                margin-bottom: 12px;
                min-height: 34px;
            }

            .table-responsive {
                overflow: visible !important;
            }

            #payroll-table {
                display: block;
                width: 100% !important;
                min-width: 0 !important;
                border-collapse: separate;
                border-spacing: 0;
            }

            #payroll-table thead {
                display: none;
            }

            #payroll-table tbody,
            #payroll-table tr,
            #payroll-table td {
                display: block;
                width: 100% !important;
            }

            #payroll-table tr {
                background: #fff;
                padding: 12px;
                margin-bottom: 14px;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            #payroll-table td {
                border: 0 !important;
                /* padding: 7px 0 !important; */
            }

            #payroll-table td[data-label] {
                display: grid;
                grid-template-columns: 128px minmax(0, 1fr);
                gap: 10px;
                align-items: center;
            }

            #payroll-table td[data-label]::before {
                content: attr(data-label);
                color: #1b2850;
                font-size: 13px;
                font-weight: 600;
            }

            #payroll-table td.salary-staff-cell {
                padding-bottom: 12px !important;
                margin-bottom: 6px;
                border-bottom: 0 !important;
            }

            #payroll-table td.salary-staff-cell a>div {
                align-items: center !important;
            }

            #payroll-table .form-control-sm {
                min-height: 35px;
                font-size: 13px;
            }

            #payroll-table td:last-child {
                display: flex;
                justify-content: flex-end;
                padding-top: 12px !important;
                border-top: 0 !important;
            }
        }

        .hr-btnbg {
            background-color: #FF9F43;
            color: white;
        }

        .btn-added {
            background: #ff9f43;
            /* padding: 7px 15px; */
            color: #fff;
            font-weight: 700;
            font-size: 14px;
        }

        #payroll-table td:nth-child(2),
        #payroll-table td:nth-child(3),
        #payroll-table td:nth-child(4) {
            width: 100px;
        }

        .btn-added:hover {
            color: #fff !important;
        }

        #deductionBreakdownModal .breakdown-section {
            border-left: 3px solid #FF9F43;
            padding-left: 0.75rem;
            margin-bottom: 1rem;
        }

        #deductionBreakdownModal .breakdown-list {
            max-height: 120px;
            overflow-y: auto;
        }

        #deductionBreakdownModal .summary-row {
            font-weight: 600;
        }
    </style>
    <!-- Deduction breakdown modal -->
    <div class="modal fade" id="deductionBreakdownModal" tabindex="-1" aria-labelledby="deductionBreakdownModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background:#FF9F43;color:white;">
                    <h5 class="modal-title" id="deductionBreakdownModalLabel">
                        <i class="mdi mdi-information-outline me-1"></i> Why was this amount deducted?
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="deductionBreakdownBody">
                    <div class="text-center py-4" id="deductionBreakdownLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 mb-0">Loading details...</p>
                    </div>
                    <div id="deductionBreakdownContent" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="salary-manage">
                        <div class="slarypadding flex-grow-1">
                            <h4 class="card-title mb-0">Manage Salary of Staff</h4>
                        </div>

                        <?php $role = session()->get('role'); ?>
                        <?php if ($role !== "staff"): ?>
                        <div class="slarypadding">
                            <?php
                            $currentMonth = date('Y-m');
                            // Default to last month
                            $lastMonth = date('Y-m', strtotime('first day of last month'));
                            ?>
                            <input type="month" id="salaryMonth" name="salaryMonth"
                                class="form-control form-control-sm salary-control-size"
                                value="<?= e($month ?? $lastMonth) ?>" max="<?= $currentMonth ?>" />
                        </div>
                        <div class="slarypadding salary-back-wrap">
                            <a href="/payrollview" class="btn btn-added" style="margin-right: 6rem;white-space:nowrap">
                                <i class="mdi mdi-arrow-left iconfontsize"></i>
                                Back
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Save All Form -->
                    <form action="<?= url('/api/payroll/saveAll') ?>" method="post" class="individual-save-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="month" value="<?= $month ?>">
                        <div class="text-end d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-added save-all">
                                <i class="mdi mdi-content-save-all-outline me-1 iconfontsize"></i>Save All
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped slalary-detail" id="payroll-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Present Days</th>
                                        <th>Leaves</th>
                                        <th>Half-day</th>
                                        <!-- <th>Per-Day Salary</th> -->
                                        <th>Overtime Hours</th>
                                        <!-- <th>Overtime Rate</th> -->
                                        <th>Overtime Pay</th>
                                        <th>Tax</th>
                                        <th>Advance Payment</th>
                                        <th>Salary Deduction</th>
                                        <th>Net Salary</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $index => $emp): ?>
                                    <tr data-overtime-pay="<?= e($emp['overtime_pay'] ?? 0) ?>"
                                        data-late-deduction="<?= e($emp['late_deduction'] ?? 0) ?>"
                                        data-base-deduction="<?= e($emp['base_deduction'] ?? $emp['salary_deduction']) ?>"
                                        data-per-day="<?= e($emp['per_day']) ?>"
                                        data-overtime-multiplier="<?= e($emp['overtime_multiplier'] ?? 1) ?>"
                                        data-overtime-hours="<?= e($emp['total_overtime_hours'] ?? 0) ?>"
                                        data-advance-payment="<?= e($emp['advance_payment'] ?? 0) ?>">
                                        <td class="salary-staff-cell" style="min-width: 180px; max-width: 250px;">
                                            <a href="/employee/profile/<?= $emp['id'] ?>"
                                                class="text-decoration-none text-dark">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <?php if (!empty($emp["profile_image"])) { ?>
                                                    <img src="{{ env('ImagePath') }}/storage/{{ $emp['profile_image'] ?: 'customer/customer5.jpg' }}"
                                                        alt="Profile"
                                                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                                    <div style="display:flex;flex-direction:column;">
                                                        <span class="capitalize-text"
                                                            style="
                              word-break: break-word;
                              white-space: normal;
                              overflow-wrap: break-word;
                            "><?= e($emp['firstname']) ?></span>
                                                        <small class="text-muted"
                                                            style="font-size:12px;">₹<?= number_format($emp['salary'], 2) ?></small>
                                                    </div>
                                                    <?php } else { ?>
                                                    <img src="<?= !empty($emp['profile_image']) ? e($emp['profile_image']) : '/public/admin/assets/img/customer/customer5.jpg' ?>"
                                                        alt="Profile"
                                                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                                    <div style="display:flex;flex-direction:column;">
                                                        <span
                                                            style="
                              word-break: break-word;
                              white-space: normal;
                              overflow-wrap: break-word;
                            "><?= e($emp['firstname']) ?></span>
                                                        <small class="text-muted"
                                                            style="font-size:12px;">₹<?= number_format($emp['salary'], 2) ?></small>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </a>
                                        </td>
                                        <td data-label="Present Days">
                                            <div class="form-control form-control-sm"
                                                style="background-color:#f8f9fa;border:1px solid #dee2e6;cursor:default;min-width:70px;">
                                                <?= e($emp['present_days'] ?? 0) ?></div>
                                        </td>
                                        <td data-label="Leaves">
                                            <!-- <div class="form-control form-control-sm" style="background-color: #f8f9fa; border: 1px solid #dee2e6; cursor: default;"> -->

                                            <input type="number" name="leaves[]"
                                                class="form-control form-control-sm leave-input"
                                                value="<?= $emp['leaves'] ?>" min="0"
                                                max="<?= $emp['days_in_month'] ?>" step="1" data-index="<?= $index ?>"
                                                data-salary="<?= $emp['salary'] ?>"
                                                data-tax="<?= $emp['tax_amount'] ?? 0 ?>"
                                                data-days="<?= $emp['days_in_month'] ?>"
                                                data-month="<?= date('Y-m', strtotime($month)) ?>">
                                            <small class="leave-error text-danger"
                                                style="display:none;font-size:11px;">Leaves cannot be greater than the
                                                total days in the selected month.</small>
                                            <!-- </div> -->
                                        </td>
                                        <td data-label="Half-day">
                                            <!-- <div class="form-control form-control-sm" style="background-color: #f8f9fa; border: 1px solid #dee2e6; cursor: default;"> -->

                                            <input type="number" name="half_day[]"
                                                class="form-control form-control-sm half_day-input"
                                                value="<?= $emp['half_days'] ?>" data-index="<?= $index ?>"
                                                data-salary="<?= $emp['salary'] ?>"
                                                data-tax="<?= $emp['tax_amount'] ?? 0 ?>"
                                                data-days="<?= $emp['days_in_month'] ?>"
                                                data-month="<?= date('Y-m', strtotime($month)) ?>">
                                            <!-- </div> -->
                                        </td>
                                        <!-- <td>
                          <input type="number" name="paid_leave[]" class="form-control form-control-sm paid-leave-input"
                            value="<?= $emp['used_paid_leaves'] ?? 0 ?>" min="0" step="0.5"
                            data-index="<?= $index ?>"
                            data-salary="<?= $emp['salary'] ?>"
                            data-tax="<?= $emp['tax_amount'] ?? 0 ?>"
                            data-days="<?= $emp['days_in_month'] ?>"
                            data-leaves="<?= $emp['leaves'] ?>"
                            data-halfdays="<?= $emp['half_days'] ?>"
                            data-month="<?= date('Y-m', strtotime($month)) ?>">
                        </td> -->
                                        <!-- <td data-label="Per-Day Salary">₹<?= number_format($emp['per_day'], 2) ?><br><small class="text-muted">₹<?= number_format($emp['per_hour'] ?? $emp['per_day'] / 8, 2) ?>/hr</small></td> -->
                                        <td data-label="Overtime Hours">
                                            <div class="form-control form-control-sm"
                                                style="background-color:#f8f9fa;border:1px solid #dee2e6;min-width:80px;">
                                                <?= number_format((float) ($emp['total_overtime_hours'] ?? 0), 2) ?></div>
                                        </td>
                                        <!-- <td data-label="Overtime Rate">
                          <div class="form-control form-control-sm" style="background-color:#f8f9fa;border:1px solid #dee2e6;min-width:60px;"><?= number_format((float) ($emp['overtime_multiplier'] ?? 1), 2) ?>x</div>
                        </td> -->
                                        <td data-label="Overtime Pay">
                                            <div class="form-control form-control-sm ot-pay-cell"
                                                style="background-color:#f8f9fa;border:1px solid #dee2e6;min-width:80px;">
                                                ₹<?= number_format((float) ($emp['overtime_pay'] ?? 0), 2) ?></div>
                                        </td>
                                        <td data-label="Tax"><?= e($emp['tax']) ?></td>
                                        <td data-label="Advance Payment">
                                            <input type="number" name="advance_payment[]"
                                                class="form-control form-control-sm advance-input"
                                                value="<?= $emp['advance_payment'] ?? 0 ?>" step="0.01"
                                                data-index="<?= $index ?>">
                                        </td>
                                        <td class="deduction-cell" data-label="Salary Deduction">
                                            <span>₹<?= number_format($emp['salary_deduction'], 2) ?></span>
                                            <button type="button" class="btn btn-sm btn-link p-0 ms-1 btn-deduction-info"
                                                title="Why is this amount deducted?" data-user-id="<?= $emp['user_id'] ?>"
                                                data-month="<?= e($month) ?>"
                                                data-name="<?= e($emp['firstname'] ?? '') ?>">
                                                <i class="mdi mdi-information-outline text-primary"
                                                    style="font-size:1.1rem;"></i>
                                            </button>
                                        </td>
                                        <td class="net-salary-cell" data-label="Net Salary">
                                            ₹<?= number_format($emp['net_salary'], 2) ?></td>

                                        <!-- Hidden Inputs -->
                                        <input type="hidden" name="employee_id[]" value="<?= $emp['id'] ?>">
                                        <input type="hidden" name="salary[]" value="<?= $emp['salary'] ?>">
                                        <input type="hidden" name="deduction[]" class="deduction-input"
                                            value="<?= $emp['salary_deduction'] ?>">
                                        <input type="hidden" name="net_salary[]" class="net-salary-input"
                                            value="<?= $emp['net_salary'] ?>">
                                        <input type="hidden" name="overtime_pay[]" class="overtime-pay-input"
                                            value="<?= $emp['overtime_pay'] ?? 0 ?>">
                                        <input type="hidden" name="total_overtime_hours[]"
                                            class="total-overtime-hours-input"
                                            value="<?= $emp['total_overtime_hours'] ?? 0 ?>">
                                        <input type="hidden" name="half_day[]" value="<?= $emp['half_days'] ?>">

                                        <!-- Per Row Save -->
                                        <td data-label="Action">
                                            <input type="hidden" class="single-employee-id" value="<?= $emp['id'] ?>">
                                            <input type="hidden" class="single-salary" value="<?= $emp['salary'] ?>">
                                            <input type="hidden" class="single-leave-input"
                                                value="<?= $emp['leaves'] ?>">
                                            <input type="hidden" class="single-halfday-input"
                                                value="<?= $emp['half_days'] ?>">
                                            <!-- <input type="hidden" class="single-paid-leave-input" value="<?= $emp['used_paid_leaves'] ?? 0 ?>"> -->
                                            <input type="hidden" class="single-deduction-input"
                                                value="<?= $emp['salary_deduction'] ?>">
                                            <input type="hidden" class="single-net-salary-input"
                                                value="<?= $emp['net_salary'] ?>">
                                            <input type="hidden" class="single-month" value="<?= $month ?>">

                                            <?php if (!empty($emp["is_saved"])): ?>
                                            <button type="button" class="btn btn-sm btn-update-row"
                                                style="background:#ff9f43;border:none;color:white;">Update</button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-save-single"
                                                data-id="<?= $emp['user_id'] ?>"
                                                style="background-color:#FF9F43;color:white">
                                                <i class="mdi mdi-content-save"></i> Save
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            // Set to last month by default
            const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const currentMonth = `${lastMonth.getFullYear()}-${String(lastMonth.getMonth() + 1).padStart(2, '0')}`;

            // Helper: Make row editable
            function setRowEditable(row, editable) {
                // Editable fields: Leaves, Half-day, Overtime Hours, Overtime Rate, Tax, Advance Payment
                const fields = [{
                        selector: '.leave-input',
                        name: 'leaves[]',
                        type: 'number'
                    },
                    {
                        selector: '.half_day-input',
                        name: 'half_day[]',
                        type: 'number'
                    },
                    {
                        selector: '.advance-input',
                        name: 'advance_payment[]',
                        type: 'number'
                    },
                    {
                        selector: '.total-overtime-hours-input',
                        name: 'total_overtime_hours[]',
                        type: 'number'
                    },
                    {
                        selector: null,
                        name: 'overtime_multiplier',
                        type: 'number'
                    },
                    {
                        selector: null,
                        name: 'tax',
                        type: 'number'
                    }
                ];

                // Overtime Rate and Tax are not direct inputs, so we need to find their cells
                // Columns: 0=Name, 1=Present Days, 2=Leaves, 3=Half-day, 4=OT Hours, 5=OT Pay, 6=Tax, 7=Advance, 8=Deduction, 9=Net, 10=Action
                const cells = row.querySelectorAll('td');
                if (editable) {
                    // Tax (column index 6)
                    let taxCell = cells[6];
                    let taxVal = taxCell.textContent.trim();
                    taxCell.innerHTML =
                        `<input type="number" step="0.01" min="0" class="form-control form-control-sm tax-edit" value="${taxVal}">`;
                } else {
                    // Tax (column index 6)
                    let taxCell = cells[6];
                    let taxInput = taxCell.querySelector('.tax-edit');
                    if (taxInput) {
                        let val = parseFloat(taxInput.value) || 0;
                        taxCell.innerHTML = `${val}`;
                    }
                }
                // Leaves, Half-day, Advance Payment, Overtime Hours
                fields.forEach(f => {
                    if (f.selector) {
                        let input = row.querySelector(f.selector);
                        if (input) input.readOnly = !editable;
                    }
                });
            }

            // Handle Update button click
            document.querySelectorAll('.btn-update-row').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    setRowEditable(row, true);
                    // Change Update button to Save
                    this.textContent = 'Save';
                    this.classList.remove('btn-update-row', 'btn-primary');
                    this.classList.add('btn-save-row', 'btn-success');
                    // Remove Saved badge
                    const badge = row.querySelector('.badge.bg-success');
                    if (badge) badge.style.display = 'none';
                });
            });

            // Delegate Save button click for edited row
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-save-row')) {
                    const btn = e.target;
                    const row = btn.closest('tr');
                    // Collect values
                    const employeeId = row.querySelector('input[name="employee_id[]"]').value;
                    const salary = row.querySelector('input[name="salary[]"]').value;
                    const leaves = row.querySelector('.leave-input').value;
                    const halfDay = row.querySelector('.half_day-input').value;
                    const advancePayment = row.querySelector('.advance-input').value;
                    const overtimePay = (row.querySelector('.overtime-pay-input') && row.querySelector(
                        '.overtime-pay-input').value) || 0;
                    const totalOvertimeHours = (row.querySelector('.total-overtime-hours-input') && row
                        .querySelector('.total-overtime-hours-input').value) || 0;
                    const month = row.querySelector('.single-month').value;
                    // Overtime Rate and Tax from new inputs
                    const tax = row.querySelector('.tax-edit') ? row.querySelector('.tax-edit').value : row
                        .cells[6].textContent.trim();
                    // Deductions and net salary
                    const deduction = row.querySelector('.deduction-input').value;
                    const netSalary = row.querySelector('.net-salary-input').value;

                    // AJAX save (reuse your existing logic)
                    fetch("<?= url('/api/payroll/save') ?>", {
                            method: "POST",
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': "<?= csrf_token() ?>"
                            },
                            body: new URLSearchParams({
                                employee_id: employeeId,
                                month: month,
                                salary: salary,
                                leaves: leaves,
                                half_day: halfDay,
                                deduction: deduction,
                                net_salary: netSalary,
                                advance_payment: advancePayment,
                                overtime_pay: overtimePay,
                                total_overtime_hours: totalOvertimeHours,
                                tax: tax
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // Set row back to readonly
                                setRowEditable(row, false);
                                // Change Save button back to Update
                                btn.textContent = 'Update';
                                btn.classList.remove('btn-save-row', 'btn-success');
                                btn.classList.add('btn-update-row');
                                btn.style.background = '#ff9f43';
                                btn.style.border = 'none';
                                btn.style.color = 'white';
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message || 'Row updated',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: data.message || 'Failed to update row'
                                });
                            }
                        })
                        .catch(err => Swal.fire('Error', 'Error: ' + err, 'error'));
                }
            });



            // Month filter event listener
            const salaryMonthInput = document.getElementById('salaryMonth');
            if (salaryMonthInput) {
                salaryMonthInput.addEventListener('change', function() {
                    const selectedMonth = this.value;
                    if (selectedMonth) {
                        window.location.href =
                            `<?= url('/payroll/salary-details') ?>?month=${selectedMonth}`;
                    }
                });
            }

            const leaveInputs = document.querySelectorAll('.leave-input');
            const halfDayInputs = document.querySelectorAll('.half_day-input');
            const advanceInputs = document.querySelectorAll('.advance-input');
            // const paidLeaveInputs = document.querySelectorAll('.paid-leave-input');

            function updateRowCalculations(row) {
                const leaveInput = row.querySelector('.leave-input');
                const halfInput = row.querySelector('.half_day-input');

                const leaves = parseFloat(leaveInput.value) || 0;
                const halfDays = parseFloat(halfInput.value) || 0;
                const advancePayment = parseFloat(row.querySelector('.advance-input').value) || 0;
                const salary = parseFloat(leaveInput.dataset.salary) || 0;
                const tax = parseFloat(leaveInput.dataset.tax) || 0;
                const daysInMonth = parseInt(leaveInput.dataset.days) || 30;

                // Per Day Salary = Monthly Salary / Total Days of the Selected Month
                const perDay = salary / daysInMonth;
                const hourlyRate = perDay / 8;
                const overtimeHours = parseFloat(row.dataset.overtimeHours) || 0;
                const overtimeMultiplier = parseFloat(row.dataset.overtimeMultiplier) || 1;
                const overtimePay = overtimeHours * hourlyRate * overtimeMultiplier;

                // Salary Deduction = Leaves × Per Day Salary
                const salaryDeduction = leaves * perDay;
                // Net Salary = Monthly Salary + Overtime Pay - Salary Deduction - Tax - Advance Payment
                const netSalary = salary + overtimePay - salaryDeduction - tax - advancePayment;

                // Update visible cells
                const deductionSpan = row.querySelector('.deduction-cell span');
                if (deductionSpan) deductionSpan.textContent = '₹' + salaryDeduction.toFixed(2);

                const netSalaryCell = row.querySelector('.net-salary-cell');
                if (netSalaryCell) netSalaryCell.textContent = '₹' + netSalary.toFixed(2);

                const otPayCell = row.querySelector('.ot-pay-cell');
                if (otPayCell) otPayCell.textContent = '₹' + overtimePay.toFixed(2);

                // Update hidden form inputs
                row.querySelector('.deduction-input').value = salaryDeduction.toFixed(2);
                row.querySelector('.net-salary-input').value = netSalary.toFixed(2);

                const opInput = row.querySelector('.overtime-pay-input');
                if (opInput) opInput.value = overtimePay.toFixed(2);

                row.querySelector('.single-leave-input').value = leaves;
                row.querySelector('.single-halfday-input').value = halfDays;
                row.querySelector('.single-deduction-input').value = salaryDeduction.toFixed(2);
                row.querySelector('.single-net-salary-input').value = netSalary.toFixed(2);
            }

            // Validate leave input: no negatives, no decimals, no non-numeric, cap at days in month
            function validateLeaveInput(input) {
                const daysInMonth = parseInt(input.dataset.days) || 30;
                const errorEl = input.parentElement.querySelector('.leave-error');
                let val = input.value;

                // Strip non-numeric characters (allow only digits)
                val = val.replace(/[^0-9]/g, '');

                // Parse as integer
                let num = parseInt(val, 10);

                // Prevent negative (shouldn't happen after regex, but safety)
                if (isNaN(num) || num < 0) num = 0;

                // Cap at days in month
                if (num > daysInMonth) {
                    num = daysInMonth;
                    // Show error message
                    if (errorEl) errorEl.style.display = 'block';
                    // Show toast
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Leaves cannot be greater than the total days in the selected month.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    if (errorEl) errorEl.style.display = 'none';
                }

                input.value = num;
            }

            leaveInputs.forEach(input => {
                const row = input.closest('tr');
                input.addEventListener('input', function() {
                    validateLeaveInput(this);
                    updateRowCalculations(row);
                });
                input.addEventListener('change', function() {
                    validateLeaveInput(this);
                    updateRowCalculations(row);
                });
                // Prevent typing 'e', '.', '-', '+' keys
                input.addEventListener('keydown', function(e) {
                    if (['e', 'E', '.', '-', '+'].includes(e.key)) {
                        e.preventDefault();
                    }
                });
            });

            halfDayInputs.forEach(input => {
                const row = input.closest('tr');
                input.addEventListener('input', () => updateRowCalculations(row));
                input.addEventListener('change', () => updateRowCalculations(row));
            });

            advanceInputs.forEach(input => {
                const row = input.closest('tr');
                input.addEventListener('input', () => updateRowCalculations(row));
                input.addEventListener('change', () => updateRowCalculations(row));
            });

            // Recalculate all rows on page load so deduction = tax only, net = salary + overtime - tax
            document.querySelectorAll('#payroll-table tbody tr').forEach(row => {
                if (row.querySelector('.leave-input')) {
                    updateRowCalculations(row);
                }
            });

            // paidLeaveInputs.forEach(input => {
            //   const row = input.closest('tr');
            //   if (input.dataset.month !== currentMonth) input.disabled = true;
            //   input.addEventListener('input', () => updateRowCalculations(row));
            // });

            document.querySelectorAll('.btn-save-single').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const employeeId = this.dataset.id;
                    const salary = row.querySelector('input[name="salary[]"]').value;
                    const leaves = row.querySelector('.leave-input').value;
                    const halfDay = row.querySelector('.half_day-input').value;
                    const paidLeaveInput = row.querySelector('.paid-leave-input');
                    const paidLeave = paidLeaveInput ? paidLeaveInput.value : 0;
                    const deduction = row.querySelector('.deduction-input').value;
                    const netSalary = row.querySelector('.net-salary-input').value;
                    const advancePayment = row.querySelector('.advance-input').value;
                    const overtimePay = (row.querySelector('.overtime-pay-input') && row
                        .querySelector('.overtime-pay-input').value) || 0;
                    const totalOvertimeHours = (row.querySelector('.total-overtime-hours-input') &&
                        row.querySelector('.total-overtime-hours-input').value) || 0;
                    const month = row.querySelector('.single-month').value;

                    fetch("<?= url('/api/payroll/save') ?>", {
                            method: "POST",
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': "<?= csrf_token() ?>"
                            },
                            body: new URLSearchParams({
                                employee_id: employeeId,
                                month: month,
                                salary: salary,
                                leaves: leaves,
                                half_day: halfDay,
                                // paid_leave: paidLeave,
                                deduction: deduction,
                                net_salary: netSalary,
                                advance_payment: advancePayment,
                                overtime_pay: overtimePay,
                                total_overtime_hours: totalOvertimeHours
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const updateBtn = document.createElement('button');
                                updateBtn.type = 'button';
                                updateBtn.className = 'btn btn-sm btn-update-row';
                                updateBtn.style.cssText =
                                    'background:#ff9f43;border:none;color:white;';
                                updateBtn.textContent = 'Update';
                                this.parentNode.replaceChild(updateBtn, this);
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    toast: true,
                                    position: 'top-end',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: data.message
                                });
                            }
                        })
                        .catch(err => Swal.fire('Error', 'Error: ' + err, 'error'));
                });
            });

            // Add AJAX submission listener for Save All
            const saveAllForm = document.querySelector('.individual-save-form');
            if (saveAllForm) {
                saveAllForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const btn = this.querySelector('.save-all');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1 iconfontsize"></i>Saving...';
                    btn.disabled = true;

                    const formData = new FormData(this);

                    fetch("<?= url('/api/payroll/saveAll') ?>", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': "<?= csrf_token() ?>"
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                            if (data.status === 'success') {
                                // Change single save buttons to 'Update'
                                document.querySelectorAll('.btn-save-single').forEach(singleBtn => {
                                    const updateBtn = document.createElement('button');
                                    updateBtn.type = 'button';
                                    updateBtn.className = 'btn btn-sm btn-update-row';
                                    updateBtn.style.cssText =
                                        'background:#ff9f43;border:none;color:white;';
                                    updateBtn.textContent = 'Update';
                                    singleBtn.parentNode.replaceChild(updateBtn, singleBtn);
                                });
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message || 'All salaries saved successfully',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: data.message || 'Failed to save all salaries'
                                });
                            }
                        })
                        .catch(err => {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                            Swal.fire('Error', 'An error occurred: ' + err, 'error');
                        });
                });
            }

            // Deduction info modal – event delegation + touchend for mobile
            function openSalaryDetailsDeductionModal(btn) {
                const userId = btn.dataset.userId;
                const month = btn.dataset.month;
                const name = btn.dataset.name || 'Employee';
                const modal = document.getElementById('deductionBreakdownModal');
                const loading = document.getElementById('deductionBreakdownLoading');
                const content = document.getElementById('deductionBreakdownContent');
                if (!modal || !userId || !month) return;
                document.getElementById('deductionBreakdownModalLabel').innerHTML =
                    '<i class="mdi mdi-information-outline me-1"></i> Why was this amount deducted? – ' + name;
                loading.style.display = 'block';
                content.style.display = 'none';
                content.innerHTML = '';
                if (modal.parentNode !== document.body) {
                    document.body.appendChild(modal);
                }
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
                fetch("<?= url('api/payroll/get-deduction-breakdown') ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': "<?= csrf_token() ?>"
                        },
                        body: new URLSearchParams({
                            user_id: userId,
                            month: month
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        loading.style.display = 'none';
                        if (res.status !== 'success' || !res.data) {
                            content.innerHTML = '<p class="text-danger">Could not load deduction details.</p>';
                            content.style.display = 'block';
                            return;
                        }
                        const d = res.data;
                        let html = '<p class="text-muted small mb-3">' + d.employee_name + ' – ' + d
                            .month_label + '</p>';
                        if (d.leaves && (d.leaves.count > 0 || (d.leaves.dates && d.leaves.dates.length))) {
                            html += '<div class="breakdown-section"><strong class="text-danger">Leaves (' + (d
                                .leaves.count || 0) + ' day(s))</strong>';
                            if (d.leaves.dates && d.leaves.dates.length) {
                                html += '<ul class="breakdown-list list-unstyled small mb-1">';
                                d.leaves.dates.forEach(l => {
                                    html += '<li>' + (l.label || l.date) + (l.reason ? ' – ' + l
                                        .reason : '') + '</li>';
                                });
                                html += '</ul>';
                            }
                            html += '<span class="text-danger">Deduction: ₹' + (d.leaves.deduction_amount || 0)
                                .toFixed(2) + '</span></div>';
                        }
                        if (d.absent && d.absent.dates && d.absent.dates.length) {
                            html += '<div class="breakdown-section"><strong class="text-warning">Absent (' + d
                                .absent.dates.length +
                                ' day(s))</strong><ul class="breakdown-list list-unstyled small">';
                            d.absent.dates.forEach(a => {
                                html += '<li>' + (a.label || a.date) + '</li>';
                            });
                            html += '</ul></div>';
                        }
                        if (d.half_day && (d.half_day.count > 0 || (d.half_day.dates && d.half_day.dates
                                .length))) {
                            html += '<div class="breakdown-section"><strong>Half-day (' + (d.half_day.count ||
                                0) + ')</strong>';
                            if (d.half_day.dates && d.half_day.dates.length) {
                                html += '<ul class="breakdown-list list-unstyled small mb-1">';
                                d.half_day.dates.forEach(h => {
                                    const baseLabel = (h.label || h.date);
                                    const worked = h.worked_text ? (' – Worked: ' + h.worked_text) : '';
                                    const missing = h.missing_text ? (' – Deduct: ' + h.missing_text) :
                                        '';
                                    html += '<li>' + baseLabel + worked + missing + '</li>';
                                });
                                html += '</ul>';
                            }
                            html += '<span class="text-danger">Deduction: ₹' + (d.half_day.deduction_amount ||
                                0).toFixed(2) + '</span></div>';
                        }
                        if (d.late && d.late.list && d.late.list.length) {
                            html +=
                                '<div class="breakdown-section"><strong>Late arrival</strong><ul class="breakdown-list list-unstyled small">';
                            d.late.list.forEach(l => {
                                html += '<li>' + (l.label || l.date) + ' – ' + (l.late_text || l
                                    .late_minutes + ' min') + '</li>';
                            });
                            html += '</ul><span class="text-danger">Deduction: ₹' + (d.late.deduction_amount ||
                                0).toFixed(2) + '</span></div>';
                        }
                        if (d.overtime && d.overtime.list && d.overtime.list.length) {
                            html +=
                                '<div class="breakdown-section"><strong class="text-success">Overtime</strong><ul class="breakdown-list list-unstyled small">';
                            d.overtime.list.forEach(o => {
                                html += '<li>' + (o.label || o.date) + ' – ' + (o.overtime_text || o
                                    .overtime_hours + 'h') + '</li>';
                            });
                            html += '</ul><span class="text-success">Added to salary: ₹' + (d.overtime
                                .pay_amount || 0).toFixed(2) + '</span></div>';
                        }
                        if (d.summary) {
                            html +=
                                '<hr><div class="summary-row"><span>Total deduction (leaves + half-day + late):</span> <span class="text-danger">₹' +
                                (d.summary.total_deduction || 0).toFixed(2) + '</span></div>';
                            if (d.summary.overtime_added > 0) html +=
                                '<div class="summary-row"><span>Overtime added:</span> <span class="text-success">₹' +
                                d.summary.overtime_added.toFixed(2) + '</span></div>';
                        }
                        if (!d.leaves?.count && !d.absent?.dates?.length && !d.half_day?.count && !d.late?.list
                            ?.length && !d.overtime?.list?.length) {
                            html +=
                                '<p class="text-muted">No leaves, absent, late, or overtime in this month.</p>';
                        }
                        content.innerHTML = html;
                        content.style.display = 'block';
                    })
                    .catch(() => {
                        loading.style.display = 'none';
                        content.innerHTML = '<p class="text-danger">Failed to load details.</p>';
                        content.style.display = 'block';
                    });
            }

            var salaryDetailsDeductionLastTouch = 0;
            document.addEventListener('touchend', function(e) {
                const btn = e.target.closest('.btn-deduction-info');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();
                salaryDetailsDeductionLastTouch = Date.now();
                openSalaryDetailsDeductionModal(btn);
            }, {
                passive: false
            });
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-deduction-info');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();
                if (Date.now() - salaryDetailsDeductionLastTouch < 400) return;
                openSalaryDetailsDeductionModal(btn);
            });
        });
    </script>

@endsection
