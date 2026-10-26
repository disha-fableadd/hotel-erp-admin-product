@extends('layout.app')
@section('title', 'Salary List')
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Salaries</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-4">
                    <div class="card p-4 shadow-sm">
                        <div class="row g-3 align-items-end">

                            <!-- Staff Filter -->
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <label for="filterStaff" class="form-label">Staff</label>
                                <select class="form-select form-select-sm" id="filterStaff">
                                    <option value="">All Staff</option>
                                    <!-- Filled via JS -->
                                </select>
                            </div>

                            <!-- Month Filter -->
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <label for="filterMonth" class="form-label">Month</label>
                                <select class="form-select form-select-sm" id="filterMonth">
    @for ($m = 1; $m <= 12; $m++)
        <option value="{{ $m }}" {{ $m == now()->subMonth()->month ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
        </option>
    @endfor
</select>
                            </div>

                            <!-- Year Filter -->
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <label for="filterYear" class="form-label">Year</label>
                                <select class="form-select form-select-sm" id="filterYear">
                                    <option value="">Select Year</option>
                                    <!-- Filled via JS -->
                                </select>
                            </div>

                            <!-- Month-Year Display -->
                            {{-- <div class="col-lg-3 col-md-6 col-sm-12 text-md-start text-lg-end">
                                <label class="form-label d-none d-lg-block">&nbsp;</label>
                                <div>
                                    <strong id="selectedMonthYearDisplay" class="text-dark">
                                        Salaries for {{ \Carbon\Carbon::now()->format('F Y') }}
                                    </strong>
                                </div>
                            </div> --}}

                            <!-- Export Buttons -->
                            <div class="col-lg-6 col-md-6 col-sm-12 text-lg-end text-md-start">
                                <strong id="selectedMonthYearDisplay" class="text-dark">
                                    Salaries for {{ \Carbon\Carbon::now()->format('F Y') }}
                                </strong>
                                <label class="form-label d-none d-lg-block">&nbsp;</label>
                                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                    <button id="exportCSV" class="btn btn-sm btn-success">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>
                                    <button id="exportPDF" class="btn btn-sm btn-danger">
                                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                                    </button>
                                    <button id="advanceHistory" class="btn btn-sm btn-primary">
                                        <i class="fas fa-history me-1"></i> Staff Advance Payment History
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <!-- Bootstrap Nav Tabs -->
                <ul class="nav nav-tabs mb-3" id="salaryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                            type="button" role="tab">Pending Salary</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="paid-tab" data-bs-toggle="tab" data-bs-target="#paid" type="button"
                            role="tab">Paid Salary</button>
                    </li>
                </ul>


                <div class="tab-content pt-3">
                    <!-- Pending Salary Tab -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table datanew" id="pendingTable">
                                <thead>
                                    <tr>
                                        <th>Staff Name</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Extra Present</th>
                                        <th>Pending Advance Pay</th>
                                        <th>Paid Advance Pay</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated by AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paid Salary Tab -->
                    <div class="tab-pane fade" id="paid" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table datanew" id="paidTable">
                                <thead>
                                    <tr>
                                        <th>Staff Name</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Extra Present</th>
                                        <th>Pending Advance Pay</th>
                                        <th>Paid Advance Pay</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated by AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Modal -->
    <!-- Salary Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-dark">
                    <h5 class="modal-title" id="paymentModalLabel">Make Salary Payment</h5>
                </div>
                <div class="modal-body">
                    <!-- Display Staff Info -->
                    <div class="d-flex justify-content-between mb-3 gap-3">
                        <div class="flex-fill">
                            <label class="mb-1">Staff Name:</label>
                            <div class="bg-light p-2 rounded border" id="staffNameDisplay"></div>
                        </div>
                        <div class="flex-fill">
                            <label class="mb-1">Advance Payment:</label>
                            <div class="bg-light p-2 rounded border" id="advancePaymentDisplay"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label class="mb-1">Present Days:</label>
                            <div class="bg-light p-2 rounded border" id="presentDaysDisplay"></div>
                        </div>
                        <div class="col">
                            <label class="mb-1">Absent Days:</label>
                            <div class="bg-light p-2 rounded border" id="absentDaysDisplay"></div>
                        </div>
                        <div class="col">
                            <label class="mb-1">Extra Days:</label>
                            <div class="bg-light p-2 rounded border" id="extraDaysDisplay"></div>
                        </div>
                    </div>

                    <!-- Input Fields -->
                    <form id="paymentForm">
                        <input type="hidden" id="staffId">
                        <input type="hidden" id="old_advance_pay">

                        <div class="d-flex justify-content-between mb-3 gap-3">
                            <div class="flex-fill">
                                <label for="salaryAmount" class="form-label">Salary Amount</label>
                                <input type="number" class="form-control" id="salaryAmount"
                                    placeholder="Enter salary amount">
                                <div class="text-danger small" id="errorSalaryAmount"></div>
                            </div>
                            <div class="flex-fill">
                                <label for="extraAmount" class="form-label">Extra Amount</label>
                                <input type="number" class="form-control" id="extraAmount" value="0">
                                <div class="text-danger small" id="errorExtraAmount"></div>
                            </div>
                            <div class="flex-fill">
                                <label for="advancePaid" class="form-label">Advance Paid</label>
                                <input type="number" class="form-control" id="advancePaid" value="0">
                                <div class="text-danger small" id="errorAdvancePaid"></div>
                            </div>
                            <div class="flex-fill">
                                <label class="mb-1">Pending Advance</label>
                                <input type="number" class="form-control" id="pendingAdvance" value="0" disabled>
                            </div>

                        </div>

                        <div class="mb-3">
                            <label for="grandTotal" class="form-label">Grand Total</label>
                            <input type="number" class="form-control" id="grandTotal" readonly>
                            <div class="text-danger small" id="errorGrandTotal"></div>
                        </div>
                    </form>
                </div>
                <div style="padding: 1rem;">
                    {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-submit" id="savePayment">Save</button>
                    {{-- <button type="button" class="btn btn-danger" id="printPayment">Print</button> --}}
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="paymentModal2" tabindex="-1" aria-labelledby="paymentModalLabel2" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-dark">
                <h5 class="modal-title" id="paymentModalLabel2">Edit Salary</h5>
            </div>
            <div class="modal-body">
                <!-- Display Staff Info -->
                <div class="d-flex justify-content-between mb-3 gap-3">
                    <div class="flex-fill">
                        <label class="mb-1">Staff Name:</label>
                        <div class="bg-light p-2 rounded border" id="staffNameDisplay2"></div>
                    </div>
                    <div class="flex-fill">
                        <label class="mb-1">Advance Payment:</label>
                        <div class="bg-light p-2 rounded border" id="advancePaymentDisplay2"></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="mb-1">Present Days:</label>
                        <div class="bg-light p-2 rounded border" id="presentDaysDisplay2"></div>
                    </div>
                    <div class="col">
                        <label class="mb-1">Absent Days:</label>
                        <div class="bg-light p-2 rounded border" id="absentDaysDisplay2"></div>
                    </div>
                    <div class="col">
                        <label class="mb-1">Extra Days:</label>
                        <div class="bg-light p-2 rounded border" id="extraDaysDisplay2"></div>
                    </div>
                </div>

                <!-- Input Fields -->
                <form id="paymentForm2">
                    <input type="hidden" id="staffId2">
                    <input type="hidden" id="salaryId2">

                    <div class="d-flex justify-content-between mb-3 gap-3">
                        <div class="flex-fill">
                            <label for="salaryAmount2" class="form-label">Salary Amount</label>
                            <input type="number" class="form-control" id="salaryAmount2"
                                placeholder="Enter salary amount">
                            <div class="text-danger small" id="errorSalaryAmount2"></div>
                        </div>
                        <div class="flex-fill">
                            <label for="extraAmount2" class="form-label">Extra Amount</label>
                            <input type="number" class="form-control" id="extraAmount2" value="0">
                            <div class="text-danger small" id="errorExtraAmount2"></div>
                        </div>
                        <div class="flex-fill">
                            <label for="advancePaid2" class="form-label">Advance Paid</label>
                            <input type="number" class="form-control" id="advancePaid2" value="0">
                            <div class="text-danger small" id="errorAdvancePaid2"></div>
                        </div>
                        <div class="flex-fill">
                            <label class="mb-1">Pending Advance</label>
                            <input type="number" class="form-control" id="pendingAdvance2" value="0" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="grandTotal2" class="form-label">Grand Total</label>
                        <input type="number" class="form-control" id="grandTotal2" readonly>
                        <div class="text-danger small" id="errorGrandTotal2"></div>
                    </div>
                </form>
            </div>
            <div style="padding: 1rem;">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-submit" id="savePayment2">Save</button>
            </div>
        </div>
    </div>
</div>



    <!-- Advance Payment History Modal -->
    <div class="modal fade" id="advanceHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Staff Advance Payment History</h5>
                    {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
                </div>

                <div class="modal-body">
                    <!-- Staff Dropdown -->
                    <div class="mb-3">
                        <label for="historyStaff" class="form-label">Select Staff</label>
                        <select class="form-select" id="historyStaff">
                            <option value="">-- Select Staff --</option>
                        </select>
                    </div>

                    <!-- History Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="historyTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Pending Amount</th>
                                    <th>Method</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No history available</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                    {{-- <button type="button" class="btn btn-submit" id="savePayment">Save</button> --}}
                    {{-- <button type="button" class="btn btn-danger" id="printPayment">Print</button> --}}
                </div>
            </div>
        </div>
    </div>

@endsection
@push('js')
    <script>
        $(document).ready(function() {
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            console.log(selectedSubAdminId);
            // Handle payment action
            $(document).on('click', '.make-payment', function() {
                const staffId = $(this).data('staff-id');
                const staffName = $(this).data('staff-name');
                const presentDays = $(this).data('present');
                const absentDays = $(this).data('absent');
                const extraDays = $(this).data('extra-present');
                const salary = $(this).data('salary');
                const extra_amount = $(this).data('extra-amount');
                const advance_pay = $(this).data('advance-paid');
                const pendingAdvance = $(this).data('pending-advance');

                // Display values in modal
                $('#staffNameDisplay').text(staffName);
                $('#presentDaysDisplay').text(`${presentDays} day(s)`).data('value', presentDays);
                $('#absentDaysDisplay').text(`${absentDays} day(s)`).data('value', absentDays);
                $('#extraDaysDisplay').text(`${extraDays} day(s)`).data('value', extraDays);
                
                $('#advancePaymentDisplay').text(pendingAdvance);
                $('#advancePaid').val(0).prop('disabled', pendingAdvance == 0);
                $('#pendingAdvance').val(pendingAdvance);
                $('#old_advance_pay').val(pendingAdvance);

                // ✅ Disable Extra Amount input if no extra days
                if (extraDays == 0) {
                     $('#extraAmount').val(0).prop('readonly', true);
                } else {
                    $('#extraAmount').prop('disabled', false);
                }

                // Reset others
                $('#staffId').val(staffId);
                $('#salaryAmount').val('');
                $('#grandTotal').val('');

                $('#paymentModal').modal('show');
            });

            $(document).on('click', '.make-bonus-payment', function() {
    const staffId = $(this).data('staff-id');
    const salaryId = $(this).data('salary-id');
    const staffName = $(this).data('staff-name');
    const presentDays = $(this).data('present');
    const absentDays = $(this).data('absent');
    const extraDays = $(this).data('extra-present');
    const salary = parseFloat($(this).data('salary')) || 0;
    const extra_amount = parseFloat($(this).data('extra-amount')) || 0;
    const advance_pay = parseFloat($(this).data('advance-paid')) || 0;
    const pendingAdvance = parseFloat($(this).data('pending-advance')) || 0;

    // Display info
    $('#staffNameDisplay2').text(staffName);
    $('#presentDaysDisplay2').text(`${presentDays} day(s)`);
    $('#absentDaysDisplay2').text(`${absentDays} day(s)`);
    $('#extraDaysDisplay2').text(`${extraDays} day(s)`);

    $('#advancePaymentDisplay2').text(pendingAdvance.toFixed(2));
    $('#pendingAdvance2').val(pendingAdvance.toFixed(2));

    // Set input values
    $('#salaryAmount2').val(salary);
    $('#advancePaid2').val(advance_pay);
    $('#extraAmount2').val(extra_amount);

    // Disable if needed
    $('#advancePaid2').prop('disabled', pendingAdvance == 0);
    $('#extraAmount2').prop('readonly', extraDays == 0);

    // Hidden ID
    $('#staffId2').val(staffId);
    $('#salaryId2').val(salaryId);

    // ✅ Trigger calculation once when modal opens
    calculateGrandTotal2();

    $('#paymentModal2').modal('show');
});

// ✅ Function to calculate totals for modal 2
function calculateGrandTotal2() {
    const salaryAmount = parseFloat($('#salaryAmount2').val()) || 0;
    const extraAmount = parseFloat($('#extraAmount2').val()) || 0;
    const advancePaid = parseFloat($('#advancePaid2').val()) || 0;
    const advancePayment = parseFloat($('#advancePaymentDisplay2').text()) || 0;

    const grandTotal = salaryAmount + extraAmount - advancePaid;
    $('#grandTotal2').val(grandTotal.toFixed(2));

    const pendingAdvance = advancePayment - advancePaid;
    $('#pendingAdvance2').val(pendingAdvance >= 0 ? pendingAdvance.toFixed(2) : 0);
}

// ✅ Live update when typing
$(document).on('input', '#salaryAmount2, #extraAmount2, #advancePaid2', function() {
    calculateGrandTotal2();
});




            // Calculate grand total
            // Calculate grand total and pending advance
            // $('#salaryAmount, #extraAmount, #advancePaid').on('input', function() {
            //     const salaryAmount = parseFloat($('#salaryAmount').val()) || 0;
            //     const extraAmount = parseFloat($('#extraAmount').val()) || 0;
            //     const advancePaid = parseFloat($('#advancePaid').val()) || 0;
            //     const advancePayment = parseFloat($('#advancePaymentDisplay').text()) || 0;

            //     const grandTotal = salaryAmount + extraAmount - advancePaid;
            //     $('#grandTotal').val(grandTotal);

            //     const pendingAdvance = advancePayment - advancePaid;
            //     $('#pendingAdvance').val(pendingAdvance >= 0 ? pendingAdvance : 0);
            // });

            $(document).on('input', '#salaryAmount, #extraAmount, #advancePaid', function() {
                const salaryAmount = parseFloat($('#salaryAmount').val()) || 0;
                const extraAmount = parseFloat($('#extraAmount').val()) || 0;
                const advancePaid = parseFloat($('#advancePaid').val()) || 0;
                const advancePayment = parseFloat($('#advancePaymentDisplay').text()) || 0;

                const grandTotal = salaryAmount + extraAmount - advancePaid;
                $('#grandTotal').val(grandTotal);

                const pendingAdvance = advancePayment - advancePaid;
                $('#pendingAdvance').val(pendingAdvance >= 0 ? pendingAdvance : 0);
            });

           


            $('#savePayment').on('click', function() {
                // ✅ Clear previous errors
                $('.text-danger.small').text('');
                $('.form-control').removeClass('is-invalid');

                const $btn = $(this);

                const staffId = $('#staffId').val();
                const salaryAmount = parseFloat($('#salaryAmount').val()) || 0;
                const extraAmount = parseFloat($('#extraAmount').val()) || 0;
                const advancePaid = parseFloat($('#advancePaid').val()) || 0;
                const total = parseFloat($('#grandTotal').val()) || 0;
                const present = $('#presentDaysDisplay').data('value');
                const absent = $('#absentDaysDisplay').data('value');
                const extraPresent = $('#extraDaysDisplay').data('value');
                
                const monthselected = $('#filterMonth').val();
                let selectedYear1 = $('#filterYear').val();
                let old_advance_pay = $('#old_advance_pay').val();

                console.log(old_advance_pay);
                

                // ✅ Check if present days = 0
                // if (present === 0) {
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Error',
                //         text: 'Payment cannot be processed because present days are 0.',
                //     });
                //     return;
                // }
                if (present === 0 && extraPresent === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Payment cannot be processed because both present days and extra present days are 0.',
                    });
                    return;
                }

                // Show loading spinner
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                ).prop('disabled', true);

                $.ajax({
                    url: "/api/salaries/pay",
                    type: "POST",
                    headers: {
                        "Authorization": "Bearer " + localStorage.getItem("authToken"),
                    },
                    data: {
                        staff_id: staffId,
                        present,
                        absent,
                        extra_present: extraPresent,
                        salary: salaryAmount,
                        extra_amount: extraAmount,
                        advance_pay: advancePaid,
                        total_salary: total,
                        selectedSubAdminId: selectedSubAdminId,
                        monthselected: monthselected,
                        selectedYear1: selectedYear1,
                        old_advance_pay: old_advance_pay,
                    },
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                if (res.pdf) {
                                    const byteCharacters = atob(res.pdf);
                                    const byteNumbers = new Array(byteCharacters
                                        .length);
                                    for (let i = 0; i < byteCharacters.length; i++) {
                                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                                    }
                                    const byteArray = new Uint8Array(byteNumbers);
                                    const blob = new Blob([byteArray], {
                                        type: 'application/pdf'
                                    });
                                    const blobUrl = URL.createObjectURL(blob);
                                    const a = document.createElement('a');
                                    a.href = blobUrl;
                                    a.download =
                                        `salary_slip_${staffId}_${new Date().getMonth() + 1}_${new Date().getFullYear()}.pdf`;
                                    document.body.appendChild(a);
                                    a.click();
                                    document.body.removeChild(a);
                                    URL.revokeObjectURL(blobUrl);
                                }
                                location.reload();
                            });
                            $('#paymentModal').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message,
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;

                        if (xhr.status === 422 && response.errors) {
                            const errors = response.errors;

                            if (errors.salary) {
                                $('#errorSalaryAmount').text(errors.salary[0]);
                                $('#salaryAmount').addClass('is-invalid');
                            }
                            if (errors.extra_amount) {
                                $('#errorExtraAmount').text(errors.extra_amount[0]);
                                $('#extraAmount').addClass('is-invalid');
                            }
                            if (errors.advance_pay) {
                                $('#errorAdvancePaid').text(errors.advance_pay[0]);
                                $('#advancePaid').addClass('is-invalid');
                            }
                            if (errors.total_salary) {
                                $('#errorGrandTotal').text(errors.total_salary[0]);
                                $('#grandTotal').addClass('is-invalid');
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response?.message ||
                                    'Error occurred while saving salary.',
                            });
                        }
                    },
                    complete: function() {
                        $btn.html('Save').prop('disabled', false);
                    }
                });
            });

            $('#savePayment2').on('click', function() {
    const $btn = $(this);
    const salaryId = $('#salaryId2').val(); // Hidden field for ID

    // Clear previous errors
    $('.text-danger.small').text('');
    $('.form-control').removeClass('is-invalid');

    const staffId = $('#staffId2').val();
    const salaryAmount = parseFloat($('#salaryAmount2').val()) || 0;
    const extraAmount = parseFloat($('#extraAmount2').val()) || 0;
    const advancePaid = parseFloat($('#advancePaid2').val()) || 0;
    const total = parseFloat($('#grandTotal2').val()) || 0;
    const present = $('#presentDaysDisplay2').data('value') || 0;
    const absent = $('#absentDaysDisplay2').data('value') || 0;
    const extraPresent = $('#extraDaysDisplay2').data('value') || 0;
    
    const monthselected = $('#filterMonth').val();
    const selectedYear1 = $('#filterYear').val();

    $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Updating...')
        .prop('disabled', true);

    $.ajax({
        url: `/api/salaries/update/${salaryId}`,
        type: "PUT",
        headers: {
            "Authorization": "Bearer " + localStorage.getItem("authToken"),
        },
        data: {
            staff_id: staffId,
            present: present,
            absent: absent,
            extra_present: extraPresent,
            salary: salaryAmount,
            extra_amount: extraAmount,
            advance_pay: advancePaid,
            total_salary: total,
            selectedSubAdminId: selectedSubAdminId,
            monthselected: monthselected,
            selectedYear1: selectedYear1,
        },
        success: function(res) {
            if (res.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // if (res.pdf) {
                    //     const byteCharacters = atob(res.pdf);
                    //     const byteArray = new Uint8Array([...byteCharacters].map(c => c.charCodeAt(0)));
                    //     const blob = new Blob([byteArray], { type: 'application/pdf' });
                    //     const blobUrl = URL.createObjectURL(blob);
                    //     const a = document.createElement('a');
                    //     a.href = blobUrl;
                    //     a.download = `salary_slip_${staffId}_${monthselected}_${selectedYear1}.pdf`;
                    //     document.body.appendChild(a);
                    //     a.click();
                    //     document.body.removeChild(a);
                    //     URL.revokeObjectURL(blobUrl);
                    // }
                    location.reload();
                });
                $('#paymentModal2').modal('hide');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message,
                });
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (xhr.status === 422 && response.errors) {
                const errors = response.errors;
                for (const key in errors) {
                    const inputId = `#${key}2`;
                    $(`${inputId}`).addClass('is-invalid');
                    $(`#error${key.charAt(0).toUpperCase() + key.slice(1)}2`).text(errors[key][0]);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response?.message || 'Error occurred while updating salary.',
                });
            }
        },
        complete: function() {
            $btn.html('Update').prop('disabled', false);
        }
    });
});




            $('#paymentModal').on('hidden.bs.modal', function() {
                // Clear input values
                $('#paymentForm')[0].reset();

                // Remove validation error messages
                $('#errorSalaryAmount').text('');
                $('#errorExtraAmount').text('');
                $('#errorAdvancePaid').text('');
                $('#errorGrandTotal').text('');

                // Remove 'is-invalid' class from inputs
                $('#salaryAmount').removeClass('is-invalid');
                $('#extraAmount').removeClass('is-invalid');
                $('#advancePaid').removeClass('is-invalid');
                $('#grandTotal').removeClass('is-invalid');

                // Reset read-only fields
                $('#grandTotal').val('');
            });

            $(document).ready(function() {
                const authToken = localStorage.getItem("authToken");
                const pendingTable = $('#pendingTable').DataTable();
                const paidTable = $('#paidTable').DataTable();
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                console.log(selectedSubAdminId);

                // Default to current month/year
                const currentDate = new Date();
                const currentMonth = currentDate.getMonth();
                const currentYear = currentDate.getFullYear();

                $('#filterMonth').val(currentMonth);
                loadSalaryYears();
                populateStaffDropdown();

                function fetchSalaries() {
                    let selectedMonth = $('#filterMonth').val() || (new Date().getMonth());
                    
                    

                    let selectedYear = $('#filterYear').val() || new Date().getFullYear(); // current year
                    const selectedStaffId = $('#filterStaff').val();

                    let url = "/api/salaries";
                    if (selectedSubAdminId) {
                        url += `?selectedSubAdminId=${selectedSubAdminId}`;
                    }

                    const monthName = new Date(selectedYear, selectedMonth - 1).toLocaleString('default', {
                        month: 'long'
                    });
                    $('#selectedMonthYearDisplay').text(`Salaries for ${monthName} ${selectedYear}`);


                    $.ajax({
                        url: url,
                        method: "GET",
                        headers: {
                            Authorization: "Bearer " + authToken,
                        },
                        data: {
                            month: selectedMonth,
                            year: selectedYear,
                            staff_id: selectedStaffId,
                        },
                        success: function(response) {
                            if (response.status) {
                                const salaries = response.data;
                                const pendingRows = [];
                                const paidRows = [];

                                salaries.forEach((salary) => {
                                    const staffName = salary.staff_name ?
                                        salary.staff_name.replace(/\b\w/g, c => c
                                            .toUpperCase()) :
                                        "";

                                    const row = [
                                        staffName,
                                        `${salary.present} Day(s) Present`,
                                        `${salary.absent} Day(s) Absent`,
                                        `${salary.extra_present} Extra Day(s)`,
                                        "₹" + parseFloat(salary.pending_advance || 0).toFixed(2), // ✅ Pending Advance
                                        "₹" + parseFloat(salary.paid_advance || 0).toFixed(2),    // ✅ Paid Advance
                                        salary.status,
                                        salary.status === 'Pending' ?
                                        `<button class="btn btn-sm btn-primary make-payment"
                                            data-staff-id="${salary.staff_id}"
                                            data-staff-name="${salary.staff_name}"
                                            data-present="${salary.present}"
                                            data-absent="${salary.absent}"
                                            data-extra-present="${salary.extra_present}"
                                            data-pending-advance="${salary.pending_advance}">
                                            Make Payment
                                        </button>` :
                                        `<button class="btn btn-sm btn-success make-bonus-payment mx-2"
    data-staff-id="${salary.staff_id}"
     data-salary-id="${salary.salary_id}"
    data-staff-name="${salary.staff_name}"
    data-present="${salary.present}"
    data-absent="${salary.absent}"
    data-extra-present="${salary.extra_present}"
    data-salary="${salary.monthly_salary}"
    data-advance-paid="${salary.paid_advance}"
    data-extra-amount="${salary.extra_amount}"
    data-pending-advance="${salary.old_advance_pay}"
    data-bs-toggle="modal"
    data-bs-target="#paymentModal2">
    Edit Salary
</button><button class="btn btn-sm btn-outline-danger download-slip"
                                            data-staff-id="${salary.staff_id}"
                                            data-month="${$('#filterMonth').val()}"
                                            data-year="${$('#filterYear').val()}"
                                            title="Download Salary Slip">
                                            <i class="fas fa-download"></i>
                                        </button>`,
                                    ];

                                    if (salary.status === 'Pending') {
                                        pendingRows.push(row);
                                    } else {
                                        paidRows.push(row);
                                    }
                                });


                                pendingTable.clear().rows.add(pendingRows).draw();
                                paidTable.clear().rows.add(paidRows).draw();
                            }
                        },
                        error: function(xhr) {
                            console.error("Error fetching salaries", xhr);
                        }
                    });
                }


                // Load all staff
                function populateStaffDropdown() {
                    $.ajax({
                        url: "/api/staff",
                        method: "GET",
                        headers: {
                            Authorization: "Bearer " + authToken,
                        },
                        data: {
                            selectedSubAdminId: selectedSubAdminId || '', // Pass if available
                        },
                        success: function(response) {
                            if (response.status) {
                                const staffSelect = $('#filterStaff');
                                staffSelect.empty().append(
                                    '<option value="">All Staff</option>');
                                response.staff.forEach(function(staff) {
                                    // Capitalize each word in staff name
                                    const staffName = staff.name ?
                                        staff.name.replace(/\b\w/g, c => c
                                            .toUpperCase()) :
                                        "";

                                    staffSelect.append(
                                        `<option value="${staff.id}">${staffName}</option>`
                                    );
                                });
                                console.log('asd');

                                // Default fetch after populating
                                fetchSalaries();
                            }
                        },
                        error: function(xhr) {
                            console.error("Error loading staff list", xhr);
                        }
                    });
                }

                // Load salary years
                function loadSalaryYears() {
                    $.ajax({
                        url: "/api/salaries/years",
                        method: "GET",
                        headers: {
                            Authorization: "Bearer " + authToken,
                        },
                        success: function(response) {
                            if (response.status) {
                                const yearSelect = $('#filterYear');
                                yearSelect.empty();
                                response.years.forEach(function(year) {
                                    const selected = year == currentYear ? 'selected' :
                                        '';
                                    yearSelect.append(
                                        `<option value="${year}" ${selected}>${year}</option>`
                                    );
                                });
                            }
                        },
                        error: function() {
                            console.error("Error loading years");
                        }
                    });
                }

                // On filter change
                $('#filterMonth, #filterYear, #filterStaff').on('change', fetchSalaries);
            });
           $('#exportCSV').on('click', function() {
                const selectedMonth = $('#filterMonth').val();
                const selectedYear = $('#filterYear').val();
                const selectedStaffId = $('#filterStaff').val();
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                const authToken = localStorage.getItem("authToken");

                let url = `/api/salaries/export`;
                const params = new URLSearchParams();
                if (selectedMonth) params.append('month', selectedMonth);
                if (selectedYear) params.append('year', selectedYear);
                if (selectedStaffId) params.append('staff_id', selectedStaffId);
                if (selectedSubAdminId) params.append('selectedSubAdminId', selectedSubAdminId);

                if (params.toString()) url += `?${params.toString()}`;

                $('#exportCSV').attr('disabled', true).html(
                    '<i class="spinner-border spinner-border-sm"></i> Exporting...');

                fetch(url, {
                        method: 'GET',
                        headers: {
                            Authorization: "Bearer " + authToken
                        }
                    })
                    .then(async (response) => {
                        const contentType = response.headers.get('content-type');

                        if (contentType && contentType.includes('application/json')) {
                            const res = await response.json();
                            if (!res.status) throw new Error(res.message);

                            // ✅ Trigger file download
                            const a = document.createElement('a');
                            a.href = res.file_url;
                            a.download = res.file_name || 'salary_export.xlsx';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message
                            });
                        } else {
                            // fallback if API returns blob
                            const blob = await response.blob();
                            const blobUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = blobUrl;
                            a.download = `salary_export.xlsx`;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(blobUrl);
                        }
                    })
                    .catch(error => Swal.fire('Error', error.message, 'error'))
                    .finally(() => {
                        $('#exportCSV').attr('disabled', false).html(
                            '<i class="fas fa-file-excel"></i> Export Excel');
                    });
            });


            $('#exportPDF').on('click', function() {
                const selectedMonth = $('#filterMonth').val();
                const selectedYear = $('#filterYear').val();
                const selectedStaffId = $('#filterStaff').val();
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                const authToken = localStorage.getItem("authToken");

                if (!selectedMonth || !selectedYear) {
                    Swal.fire('Error', 'Please select both month and year to export data.', 'error');
                    return;
                }

                $('#exportPDF').attr('disabled', true).html(
                    '<i class="spinner-border spinner-border-sm"></i> Exporting...');

                // ✅ Step 1: Check salaries exist
                let checkUrl = `/api/salaries?month=${selectedMonth}&year=${selectedYear}`;
                if (selectedStaffId) checkUrl += `&staff_id=${selectedStaffId}`;
                if (selectedSubAdminId) checkUrl += `&selectedSubAdminId=${selectedSubAdminId}`;

                fetch(checkUrl, {
                        method: 'GET',
                        headers: {
                            Authorization: "Bearer " + authToken
                        },
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (!response.status || !response.data || response.data.length === 0) {
                            Swal.fire('Error', 'Salary not generated yet.', 'error');
                            $('#exportPDF').attr('disabled', false).text('Export PDF');
                            throw new Error('No salary data');
                        }

                        // ✅ Step 2: Request PDF
                        let pdfUrl = `/api/salaries/pdf?month=${selectedMonth}&year=${selectedYear}`;
                        if (selectedStaffId) pdfUrl += `&staff_id=${selectedStaffId}`;
                        if (selectedSubAdminId) pdfUrl += `&selectedSubAdminId=${selectedSubAdminId}`;

                        return fetch(pdfUrl, {
                            method: 'GET',
                            headers: {
                                Authorization: "Bearer " + authToken
                            },
                        });
                    })
                    .then(async (response) => {
                        // ✅ Handle both JSON or Blob response
                        const contentType = response.headers.get('content-type');

                        if (contentType && contentType.includes('application/json')) {
                            const json = await response.json();
                            if (json.status && json.file_url) {
                                // 🔽 Download PDF from file_url
                                const a = document.createElement('a');
                                a.href = json.file_url;
                                a.download = json.file_name ||
                                    `salary_export_${selectedMonth}_${selectedYear}.pdf`;
                                document.body.appendChild(a);
                                a.click();
                                a.remove();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: json.message,
                                });
                            } else {
                                throw new Error(json.message || 'Failed to generate PDF');
                            }
                        } else {
                            // 🔽 Binary response (PDF blob)
                            const blob = await response.blob();
                            const blobUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = blobUrl;
                            a.download = `salary_export_${selectedMonth}_${selectedYear}.pdf`;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(blobUrl);
                        }
                    })
                    .catch(err => Swal.fire('Error', err.message, 'error'))
                    .finally(() => {
                        $('#exportPDF').attr('disabled', false).html(
                            '<i class="fas fa-file-pdf"></i> Export PDF');
                    });
            });
            $('#printPayment').on('click', function() {
                const staffId = $('#staffId').val();
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();

                if (!staffId || !month || !year) {
                    Swal.fire('Error', 'Please ensure staff, month, and year are selected.', 'error');
                    return;
                }

                const authToken = localStorage.getItem("authToken");
                let url = `/api/salaries/staff/pdf?staff_id=${staffId}&month=${month}&year=${year}`;

                // Disable the button and show loading
                $('#printPayment').attr('disabled', true).text('Printing...');

                // Make fetch request with Authorization header
                fetch(url, {
                        method: 'GET',
                        headers: {
                            Authorization: "Bearer " + authToken,
                        },
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to download file.');
                        return response.blob();
                    })
                    .then(blob => {
                        const blobUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = `salary_slip_${month}_${year}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(blobUrl);
                    })
                    .catch(error => {
                        Swal.fire('Error', error.message, 'error');
                    })
                    .finally(() => {
                        $('#printPayment').attr('disabled', false).text('Print');
                    });
            });
            // Handle Download Slip for Paid Salaries
            // $(document).on('click', '.download-slip', function() {
            //     const staffId = $(this).data('staff-id');
            //     const month = $(this).data('month') || $('#filterMonth').val();
            //     const year = $(this).data('year') || $('#filterYear').val();
            //     const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            //     if (!staffId || !month || !year) {
            //         Swal.fire('Error', 'Invalid staff, month, or year.', 'error');
            //         return;
            //     }

            //     const authToken = localStorage.getItem("authToken");
            //     let url = `/api/salaries/staff/pdf?staff_id=${staffId}&month=${month}&year=${year}`;
            //     if (selectedSubAdminId) url += `&selectedSubAdminId=${selectedSubAdminId}`;

            //     // Show loading icon
            //     $(this).prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i>');

            //     fetch(url, {
            //             method: 'GET',
            //             headers: {
            //                 Authorization: "Bearer " + authToken,
            //             },
            //         })
            //         .then(response => {
            //             if (!response.ok) throw new Error('Failed to download salary slip.');
            //             return response.blob();
            //         })
            //         .then(blob => {
            //             const blobUrl = window.URL.createObjectURL(blob);
            //             const a = document.createElement('a');
            //             a.href = blobUrl;
            //             a.download = `salary_slip_${staffId}_${month}_${year}.pdf`;
            //             document.body.appendChild(a);
            //             a.click();
            //             a.remove();
            //             window.URL.revokeObjectURL(blobUrl);
            //         })
            //         .catch(error => {
            //             Swal.fire('Error', error.message, 'error');
            //         })
            //         .finally(() => {
            //             $(this).prop('disabled', false).html('<i class="fas fa-download"></i>');
            //         });
            // });
             $(document).on('click', '.download-slip', function() {
    const staffId = $(this).data('staff-id');
    const month = $(this).data('month') || $('#filterMonth').val();
    const year = $(this).data('year') || $('#filterYear').val();
    const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
    const authToken = localStorage.getItem("authToken");

    if (!staffId || !month || !year) {
        Swal.fire('Error', 'Invalid staff, month, or year.', 'error');
        return;
    }

    let url = `/api/salaries/staff/pdf?staff_id=${staffId}&month=${month}&year=${year}`;
    if (selectedSubAdminId) url += `&selectedSubAdminId=${selectedSubAdminId}`;

    $(this).prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i>');

    fetch(url, {
        method: 'GET',
        headers: { Authorization: "Bearer " + authToken }
    })
    .then(res => res.json())
    .then(res => {
        if (!res.status) throw new Error(res.message);

        // ✅ Create temporary link to download PDF
        const a = document.createElement('a');
        a.href = res.file_url;
        a.download = res.file_name; // Use the filename from API
        document.body.appendChild(a);
        a.click();
        a.remove();

        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Salary slip downloaded successfully.',
            showConfirmButton: true
        });
    })
    .catch(err => Swal.fire('Error', err.message, 'error'))
    .finally(() => {
        $(this).prop('disabled', false).html('<i class="fas fa-download"></i>');
    });
});


        });
        $(document).ready(function() {
            const authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            // Open Modal
            $("#advanceHistory").on("click", function() {
                $("#advanceHistoryModal").modal("show");

                // Load staff in dropdown
                $.ajax({
                    url: "/api/staff",
                    method: "GET",
                    headers: {
                        Authorization: "Bearer " + authToken
                    },
                    data: {
                        selectedSubAdminId: selectedSubAdminId || ''
                    },
                    success: function(response) {
                        if (response.status) {
                            const staffSelect = $("#historyStaff");
                            staffSelect.empty().append(
                                '<option value="">-- Select Staff --</option>');
                            response.staff.forEach(function(staff) {
                                const staffName = staff.name ? staff.name.replace(
                                    /\b\w/g, c => c.toUpperCase()) : "";
                                staffSelect.append(
                                    `<option value="${staff.id}">${staffName}</option>`
                                );
                            });
                        }
                    }
                });
            });

            // Fetch History when staff selected
            $("#historyStaff").on("change", function() {
                const staffId = $(this).val();
                if (!staffId) return;

                $.ajax({
                    url: "/api/advance-history",
                    method: "GET",
                    headers: {
                        Authorization: "Bearer " + authToken
                    },
                    data: {
                        staff_id: staffId
                    },
                    success: function(response) {
                        const tbody = $("#historyTable tbody");
                        tbody.empty();

                        if (response.status && response.history.length > 0) {
                            response.history.forEach(function(row) {
                                const amount = parseFloat(row.amount) || 0;
                                const paid = parseFloat(row.paid_amount) || 0;
                                const pending = amount - paid;

                                tbody.append(`
                            <tr>
                                <td>${row.date ?? '-'}</td>
                                <td>${row.amount ?? 0}</td>
                                <td>${row.paid_amount ?? 0}</td>
                                <td>${pending.toFixed(2)}</td>
                                <td>${row.method ?? '-'}</td>
                                <td>${row.reason ?? '-'}</td>
                            </tr>
                        `);
                            });
                        } else {
                            tbody.append(
                                `<tr><td colspan="6" class="text-center text-muted">No history found</td></tr>`
                            );
                        }
                    },
                    error: function() {
                        alert("Error loading advance history");
                    }
                });
            });
        });
    </script>
@endpush
