@extends('layout.app')

@section('title', 'Sales List')

@section('content')
    <style>
        .table-scroll-top {
            display: none;
        }

        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
            }
        }

        .form-control {
            color: #595b5d !important;
            /* Bootstrap's default placeholder/input text color */
        }
        .form-group {
    margin-bottom: 8px !important;
}
    </style>
    @if (session('error'))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') }}
        </div>

        <style>
            .fade-out {
                opacity: 1;
                transition: opacity 0.5s ease-out;
            }

            .fade-out.hidden {
                opacity: 0;
            }
        </style>

        <script>
            setTimeout(function() {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden'); // Triggers the fade-out transition
                    // Remove the element from DOM after fadeout (optional)
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500); // match the CSS transition duration (0.5s)
                }
            }, 4000);
        </script>
    @endif
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Sales</h4>
                <!-- <h6>Manage your sales</h6> -->
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(2, 'add'))
                    <a href="{{ route('sales.add') }}" class="btn btn-sm btn-added"><img
                            src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img" class="me-1">New
                        Sales</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- Top Search and Date Filter Row -->

                <div class="table-top mb-3">
                    <div class="row w-100 align-items-center">
                        <!-- Search -->
                        <div class="col-md-2 col-12 d-flex align-items-center mb-1 mb-md-0">
                            <div class="search-set w-100">
                                <div class="search-path"></div>
                                <div class="search-input d-flex align-items-center">
                                    <a class="btn btn-searchset">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                            alt="img">
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Month Filter -->
                        <div class="col-md-2 col-6">
                            <!-- Total (only for admin/sub-admin) -->
                            @if (in_array(auth()->user()->role, ['admin', 'sub-admin']))
                                <div class="mb-1"
                                    style="color: #1b2850;border: 1px solid #0d1b3e;border-radius: 5px;padding: 4px;font-size: 14px;     font-weight: bold;">
                                    Total: <span style="color: #ff9f43" id="filtered-total">₹0.00</span>
                                </div>
                            @endif

                        </div>


                        <!-- Month Filter -->
                        <div class="col-md-2 col-6">
                            <div class="mb-1">
                                <!-- <label for="filter-month" class="form-label">Month</label> -->
                                <select id="filter-month" class="form-control form-control-sm">
                                    <option value="">All Months</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Year Filter -->
                        <div class="col-md-2 col-6">
                            <div class="mb-1">
                                <!-- <label for="filter-year" class="form-label">Year</label> -->
                                <select id="filter-year" class="form-control form-control-sm">
                                    <option value="">All Years</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Date Filter -->
                        <!-- <div class="col-md-2 col-6">
                                                            <div class="form-group mb-0">
                                                                <label for="filter-date" class="form-label">Date</label>
                                                                <input type="text" id="filter-date" placeholder="Choose Date"
                                                                    class="datetimepicker form-control form-control-sm">
                                                            </div>
                                                        </div> -->
                        <!-- Date -->
                        <div class="col-md-2 col-6">
                            <!-- <div class="form-group mb-0"> -->
                            <!-- <label for="filter-date" class="form-label">Date</label> -->
                            <input type="text" id="filter-date" placeholder="Choose Date"
                                class="datetimepicker form-control form-control-sm">
                            <!-- </div> -->
                        </div>

                        <!-- Export Buttons -->
                        <div class="col-md-2 col-12 d-flex justify-content-md-end gap-2 mb-1">
                            <button id="exportAllChallan" class="btn btn-sm btn-success w-100">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button id="exportPdf" class="btn btn-sm btn-danger w-100">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Inputs Card -->
                <div class="card" id="filter_inputs">
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Name" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Reference No" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <select class="form-select">
                                        <option>Completed</option>
                                        <option>Paid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group d-flex justify-content-end">
                                    <a class="btn btn-filters ms-auto">
                                        <img src="admin/assets/img/icons/search-whites.svg" alt="img">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-scroll-top"
                    style="overflow-x: auto; overflow-y: hidden; height: 20px; margin-bottom: 5px;">
                    <div style="height: 1px;"></div> <!-- Adjust width to match your table width -->
                </div>
                <!-- Orders Table -->
                <div class="table-responsive mt-3" style="overflow-x: auto;">
                    <table class="table datanew" id="order-table" style="max-width: 2000px;">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Date</th>
                                <th>Customer Name</th>
                                <th>Payment Status</th>
                                <th>Payment Method</th>
                                <th>Total</th>
                                <th>Biller</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS will populate this -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
    <form id="makePaymentForm">
        <div class="modal fade" id="makePaymentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">

                        <div class="text-end mb-3">
                            <button type="button" class="btn btn-sm btn-cancel text-white" id="viewHistoryBtn">View
                                Payment History</button>
                        </div>

                        <!-- ✅ Payment History Container -->
                        <div id="paymentHistoryBox" class="border p-2 rounded bg-white d-none mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Payment History</h6>

                                <!-- 👇 Visible Close Button -->
                                <button type="button" class="btn btn-sm btn-danger" id="closeHistoryBtn">
                                    &times; <!-- This renders an “×” symbol -->
                                </button>
                            </div>

                            <ul id="paymentHistoryList" class="list-unstyled mb-0"
                                style="max-height: 200px; overflow-y: auto;">
                                <!-- Populated via JavaScript -->
                            </ul>
                        </div>



                        <div class="border p-2 rounded bg-light">
                            <strong>Total Amount:</strong> ₹<span id="emiTotal"></span><br>
                            <strong>Remaining Amount:</strong> ₹<span id="remainingAmountDisplay">0.00</span>

                        </div>

                        <!-- ✅ View Payment History Button -->



                        <br>

                        <!-- Payment Method -->
                        <div class="mb-3" id="paymentMethodDiv">
                            <label for="paymentMethodSelect" class="form-label">Select Payment Method</label>
                            <select class="form-select" id="paymentMethodSelect" name="payment_method">
                                <option value="" selected disabled>Select</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="cash_online">Cash + Online</option>

                            </select>
                            <div class="text-danger" id="paymentMethodError"></div>
                        </div>

                        <div class="mb-3 d-none" id="cashOnlineTypeDiv">
                            <label for="cashOnlineTypeSelect" class="form-label">Select Cash + Online Type</label>
                            <select class="form-select" id="cashOnlineTypeSelect" name="cash_online_type">
                                <option value="" selected disabled>Select</option>
                                <option value="cash_online_fully">Cash + Online Fully</option>
                                <option value="cash_online_partially">Cash + Online Partially</option>
                            </select>
                            <div class="text-danger" id="cashOnlineTypeError"></div>
                        </div>

                        <!-- Fully Cash + Online -->
                        <!-- Fully Cash + Online -->
                        <div class="mb-3 d-none" id="fullyCashOnlineFields">
                            <label>Cash Amount</label>
                            <input type="number" id="cashOnlineFullAmount" name="fully_cash_amount"
                                class="form-control" min="0" step="0.01">
                            <div class="text-danger" id="cashOnlineFullAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="number" id="upiOnlineFullAmount" name="full_online_amount"
                                class="form-control" readonly>
                            <div class="text-danger" id="upiOnlineFullAmountError"></div>
                        </div>

                        <!-- Partial Cash + Online -->
                        <div class="mb-3 d-none" id="partialCashOnlineFields">
                            <label>Cash Amount</label>
                            <input type="number" id="cashOnlinePartialAmount" name="cash_amount" class="form-control"
                                min="0" step="0.01">
                            <div class="text-danger" id="cashOnlinePartialAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="number" id="upiOnlinePartialAmount" name="online_amount" class="form-control"
                                min="0" step="0.01">
                            <div class="text-danger" id="upiOnlinePartialAmountError"></div>
                            <label class="mt-2">Remaining Amount</label>
                            <input type="number" id="remainingCashOnlineAmount" name="remaining_amount"
                                class="form-control" readonly>
                        </div>







                        <div class="mb-3 d-none" id="onlineTypeDiv">
                            <label for="onlineTypeSelect" class="form-label">Select Online Type</label>
                            <select class="form-select" id="onlineTypeSelect" name="online_type">
                                <option value="" selected disabled>Select</option>
                                <option value="online_fully">Online Fully</option>
                                <option value="online_partially">Online Partially</option>
                            </select>
                            <div class="text-danger" id="onlineTypeError"></div>
                        </div>

                        <!-- Paid Type Dropdown (Hidden by default) -->
                        <div class="mb-3 d-none" id="paidTypeDiv">
                            <label for="paidTypeSelect" class="form-label">Paid Type</label>
                            <select class="form-select" id="paidTypeSelect" name="paid_type">
                                <option value="" selected disabled>Select</option>
                                <option value="cash_partially">Cash Partially</option>
                                <option value="cash_fully">Cash Fully</option>
                            </select>
                            <div class="text-danger" id="paidTypeError"></div>
                        </div>

                        <!-- UPI Amount Input -->
                        <div class="mb-3 d-none" id="upiAmountDiv">
                            <label for="upiAmountInput" class="form-label">Online Amount</label>
                            <input type="number" class="form-control" id="upiAmountInput" name="upi_online_amount"
                                readonly>
                            <div class="text-danger" id="upiAmountError"></div>
                        </div>



                        <!-- Partially Paid Fields -->
                        <div class="mb-3 d-none" id="partialPaidFields">
                            <label for="partialAmount" class="form-label">Enter Amount</label>
                            <input type="number" class="form-control mb-2" id="partialAmount" name="amount"
                                min="1" step="0.01">
                            <div class="text-danger" id="partialAmountError"></div>

                            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                <div style="flex: 1;">
                                    <label for="pendingAmount" class="form-label">Pending Amount</label>
                                    <input type="number" class="form-control" id="pendingAmount"
                                        name="cash_pending_amount" readonly>
                                </div>

                            </div>
                        </div>

                        <!-- Fully Paid Fields -->
                        <div class="mb-3 d-none" id="fullyPaidFields">
                            <label class="form-label">Cash Amount</label>
                            <input type="number" class="form-control" id="cashAmount" name="cashAmount" min="0"
                                step="0.01">
                            <div class="text-danger" id="cashAmountError"></div>
                        </div>













                        <!-- Cleaned Hidden Inputs (no duplicate name attributes) -->
                        <input type="hidden" id="paymentJobCardId" name="order_id">
                        <input type="hidden" id="remainingAmountHidden" name="remaining_amount">
                        <input type="hidden" id="paymentMethodHidden" name="payment_type">




                        <div class="text-end">
                            <button type="submit" class="btn btn-submit text-white"
                                style="background-color: #ff9f43;">Submit Payment</button>
                            <button type="button" class="btn btn-secondary btn-cancel"
                                data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- Global Payment History Modal -->
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentHistoryLabel">Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="globalPaymentHistoryList" class="list-group"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const userRole = "{{ auth()->user()->role }}";
    </script>

    <script>
        $(document).ready(function() {

            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            var table = $('.datanew').DataTable();

            $(document).on('click', '.make-payment-btn', function() {
                let jobCardId = $(this).data('id');
                let totalAmount = $(this).data('total-amount');
                let remainingAmount = $(this).data('remaining-amount');
                let method = $(this).data('method') || '';

                // ✅ Fill modal hidden inputs + text spans
                $('#paymentJobCardId').val(jobCardId);
                $('#emiTotal').text(parseFloat(totalAmount).toFixed(2));
                $('#remainingAmountDisplay').text(parseFloat(remainingAmount).toFixed(2));
                $('#remainingAmountHidden').val(remainingAmount);
                $('#paymentMethodHidden').val(method);

                // ✅ Reset payment method dropdown to default
                $('#paymentMethodSelect').val('');

                // ✅ Hide history box initially
                $('#paymentHistoryBox').addClass('d-none');
                $('#paymentHistoryList').html('');

                // ✅ Bind View History button
                $('#viewHistoryBtn').off('click').on('click', function() {
                    $.ajax({
                        url: '/api/sales/payment-history/' + jobCardId,
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        success: function(response) {
                            const history = response.data;

                            if (!history || history.length === 0) {
                                $('#paymentHistoryList').html(
                                    '<li>No payment history found.</li>');
                            } else {
                                let historyHtml = '';
                                history.forEach(function(payment) {
                                    historyHtml += `
                                                            <li class="mb-2">
                                                                <strong>Amount:</strong> ₹${parseFloat(payment.payment_amount).toFixed(2)}<br>
                                                                <strong>Date:</strong> ${payment.payment_date}<br>
                                                                <strong>Method:</strong> ${payment.payment_method}<br>
                                                                <strong>Payment Type:</strong> ${payment.payment_type ? payment.payment_type : 'N/A'}<br>
                                                                ${payment.payment_type === 'emi' ? `<strong>EMI Months:</strong> ${payment.emi_month || 0}<br>` : ''}
                                                            </li>
                                                            <hr class="my-1"/>
                                                        `;
                                });
                                $('#paymentHistoryList').html(historyHtml);
                            }

                            $('#paymentHistoryBox').removeClass('d-none');
                        },
                        error: function() {
                            $('#paymentHistoryList').html(
                                '<li class="text-danger">Failed to load payment history.</li>'
                            );
                            $('#paymentHistoryBox').removeClass('d-none');
                        }
                    });
                });

                // ✅ Close history
                $('#closeHistoryBtn').off('click').on('click', function() {
                    $('#paymentHistoryBox').addClass('d-none');
                });
            });

            $('#paymentMethodSelect').on('change', function() {
                let method = $(this).val();

                // Hide all optional sections first
                $('#cashOnlineTypeDiv, #fullyCashOnlineFields, #partialCashOnlineFields, #onlineTypeDiv, #paidTypeDiv, #upiAmountDiv, #partialPaidFields, #fullyPaidFields')
                    .addClass('d-none');

                if (method === 'cash') {
                    $('#paidTypeDiv').removeClass('d-none'); // Show paid type options

                } else if (method === 'online') {
                    $('#onlineTypeDiv').removeClass('d-none'); // Show online type dropdown

                } else if (method === 'cash_online') {
                    $('#cashOnlineTypeDiv').removeClass('d-none'); // Show Cash + Online type dropdown
                }
            });

            // ✅ Handle Paid Type (when Payment Method = Cash)
            // $('#paidTypeSelect').on('change', function() {
            //     let type = $(this).val();
            //     let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

            //     $('#partialPaidFields, #fullyPaidFields').addClass('d-none');

            //     if (type === 'cash_partially') {
            //         $('#partialPaidFields').removeClass('d-none');
            //         $('#partialAmount').val(''); // clear input
            //         $('#pendingAmount').val(remaining); // show full pending initially

            //         // When user types partial amount → auto update pending
            //         $('#partialAmount').off('input').on('input', function() {
            //             let entered = parseFloat($(this).val()) || 0;
            //             let newPending = Math.max(remaining - entered, 0);
            //             $('#pendingAmount').val(newPending.toFixed(2));
            //         });
            //     } else if (type === 'cash_fully') {
            //         $('#fullyPaidFields').removeClass('d-none');
            //         $('#cashAmount').val(remaining.toFixed(2)).prop('readonly', true);
            //     }
            // });
            $('#paidTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#partialPaidFields, #fullyPaidFields').addClass('d-none');

                // Disable all inputs first
                $('#partialPaidFields input, #fullyPaidFields input').prop('disabled', true);

                if (type === 'cash_partially') {
                    // Show partial fields
                    $('#partialPaidFields').removeClass('d-none');
                    $('#partialPaidFields input').prop('disabled', false);

                    // Clear & reset values
                    $('#partialAmount').val('');
                    $('#pendingAmount').val(remaining.toFixed(2));

                    // Remove any full cash amount
                    $('#cashAmount').val('').prop('readonly', false).prop('disabled', true);

                    // Live calculation for pending
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = parseFloat($(this).val()) || 0;
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(newPending.toFixed(2));
                    });

                } else if (type === 'cash_fully') {
                    // Show fully fields
                    $('#fullyPaidFields').removeClass('d-none');
                    $('#fullyPaidFields input').prop('disabled', false);

                    // Fill full amount & disable editing
                    $('#cashAmount').val(remaining.toFixed(2)).prop('readonly', true);

                    // Reset partial fields
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });


            // ✅ Handle Online Type (when Payment Method = Online)
            // $('#onlineTypeSelect').on('change', function() {
            //     let type = $(this).val();
            //     let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

            //     $('#upiAmountDiv,#partialPaidFields').addClass('d-none');

            //     if (type === 'online_partially') {
            //         $('#partialPaidFields').removeClass('d-none');
            //         $('#partialAmount').val(''); // clear input
            //         $('#pendingAmount').val(remaining); // show full pending initially

            //         // When user types partial amount → auto update pending
            //         $('#partialAmount').off('input').on('input', function() {
            //             let entered = parseFloat($(this).val()) || 0;
            //             let newPending = Math.max(remaining - entered, 0);
            //             $('#pendingAmount').val(newPending.toFixed(2));
            //         });

            //     } else if (type === 'online_fully') {
            //         $('#upiAmountDiv').removeClass('d-none');
            //         $('#upiAmountInput').val(remaining.toFixed(2)).prop('readonly', true);
            //     }
            // });
            $('#onlineTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#upiAmountDiv, #partialPaidFields').addClass('d-none');
                // Disable all inputs first
                $('#upiAmountDiv input, #partialPaidFields input').prop('disabled', true);

                if (type === 'online_partially') {
                    // Show partial fields
                    $('#partialPaidFields').removeClass('d-none');
                    $('#partialPaidFields input').prop('disabled', false);

                    // Reset values
                    $('#partialAmount').val('');
                    $('#pendingAmount').val(remaining.toFixed(2));

                    // Clear and disable UPI field
                    $('#upiAmountInput').val('').prop('readonly', false).prop('disabled', true);

                    // Live pending update
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = parseFloat($(this).val()) || 0;
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(newPending.toFixed(2));
                    });

                } else if (type === 'online_fully') {
                    // Show fully online section
                    $('#upiAmountDiv').removeClass('d-none');
                    $('#upiAmountDiv input').prop('disabled', false);

                    // Fill with remaining and lock editing
                    $('#upiAmountInput').val(remaining.toFixed(2)).prop('readonly', true);

                    // Reset partial section
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });

            // ✅ Handle Cash + Online Type
            // $('#cashOnlineTypeSelect').on('change', function() {
            //     let type = $(this).val();
            //     let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

            //     // Hide both sections initially
            //     $('#fullyCashOnlineFields, #partialCashOnlineFields').addClass('d-none');

            //     if (type === 'cash_online_fully') {
            //         // Show fully section
            //         $('#fullyCashOnlineFields').removeClass('d-none');

            //         // Set initial values
            //         $('#cashOnlineFullAmount').val('');
            //         $('#upiOnlineFullAmount').val(remaining.toFixed(2));

            //         // When user types cash → reduce online amount live
            //         $('#cashOnlineFullAmount').off('input').on('input', function() {
            //             let cash = parseFloat($(this).val()) || 0;
            //             let online = Math.max(remaining - cash, 0);
            //             $('#upiOnlineFullAmount').val(online.toFixed(2));
            //         });

            //     } else if (type === 'cash_online_partially') {
            //         // Show partial section
            //         $('#partialCashOnlineFields').removeClass('d-none');

            //         // Clear inputs
            //         $('#cashOnlinePartialAmount, #upiOnlinePartialAmount').val('');
            //         $('#remainingCashOnlineAmount').val(remaining.toFixed(2));

            //         // When user types cash → only update remaining
            //         $('#cashOnlinePartialAmount').off('input').on('input', function() {
            //             let cash = parseFloat($(this).val()) || 0;
            //             let newRemaining = Math.max(remaining - cash, 0);
            //             $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
            //         });

            //         // Online amount can be typed manually if needed
            //         $('#upiOnlinePartialAmount').off('input').on('input', function() {
            //             let online = parseFloat($(this).val()) || 0;
            //             let cash = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
            //             let newRemaining = Math.max(remaining - cash - online, 0);
            //             $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
            //         });
            //     }
            // });
            $('#cashOnlineTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#fullyCashOnlineFields, #partialCashOnlineFields').addClass('d-none');
                // Disable all inputs first
                $('#fullyCashOnlineFields input, #partialCashOnlineFields input').prop('disabled', true);

                if (type === 'cash_online_fully') {
                    // Show fully section
                    $('#fullyCashOnlineFields').removeClass('d-none');
                    $('#fullyCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlineFullAmount').val('');
                    $('#upiOnlineFullAmount').val(remaining.toFixed(2));

                    // Live adjustment of online amount
                    $('#cashOnlineFullAmount').off('input').on('input', function() {
                        let cash = parseFloat($(this).val()) || 0;
                        let online = Math.max(remaining - cash, 0);
                        $('#upiOnlineFullAmount').val(online.toFixed(2));
                    });

                    // Disable partial fields
                    $('#partialCashOnlineFields input').prop('disabled', true);

                } else if (type === 'cash_online_partially') {
                    // Show partial section
                    $('#partialCashOnlineFields').removeClass('d-none');
                    $('#partialCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlinePartialAmount, #upiOnlinePartialAmount').val('');
                    $('#remainingCashOnlineAmount').val(remaining.toFixed(2));

                    // Live update on cash input
                    $('#cashOnlinePartialAmount').off('input').on('input', function() {
                        let cash = parseFloat($(this).val()) || 0;
                        let newRemaining = Math.max(remaining - cash, 0);
                        $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
                    });

                    // Live update on online input
                    $('#upiOnlinePartialAmount').off('input').on('input', function() {
                        let online = parseFloat($(this).val()) || 0;
                        let cash = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
                        let newRemaining = Math.max(remaining - cash - online, 0);
                        $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
                    });

                    // Disable fully fields
                    $('#fullyCashOnlineFields input').prop('disabled', true);
                }
            });

            $('#makePaymentForm').on('submit', function(e) {
                e.preventDefault();


                let isValid = true;



                // Clear all previous errors
                $('.text-danger').text('');

                // Get values
                let paymentMethod = $('#paymentMethodSelect').val();
                let paymentTypeemionly = $('#paymentType').val();
                let paidType = $('#paidTypeSelect').val();
                let onlineType = $('#onlineTypeSelect').val();
                let cashOnlineType = $('#cashOnlineTypeSelect').val();



                // EMI Validation



                // Payment Method validation


                if (!paymentMethod) {
                    isValid = false;
                    console.log("Validation failed: Payment method not selected");
                    $('#paymentMethodError').text("Please select a payment method.");
                    return false;
                } else {
                    console.log("Payment method selected:", paymentMethod);
                }


                // Cash Payment Validation
                if (paymentMethod === 'cash') {
                    console.log("Cash payment selected");

                    if (!paidType) {
                        isValid = false;
                        console.log("Validation failed: Paid type not selected");
                        $('#paidTypeError').text("Please select paid type.");
                        return false;
                    } else {
                        console.log("Paid type selected:", paidType);
                    }

                    // if (paidType === 'cash_partially') {
                    //     let partialAmount = $('#partialAmount').val();
                    //     console.log("Cash partially selected, amount:", partialAmount);
                    //     if (!partialAmount || parseFloat(partialAmount) <= 0) {
                    //         isValid = false;
                    //         console.log("Validation failed: Invalid partial cash amount");
                    //         $('#partialAmountError').text("Enter a valid partial cash amount.");
                    //         return false;
                    //     } else {
                    //         console.log("Partial cash amount valid");
                    //     }
                    // }
                    if (paidType === 'cash_partially') {
                        let partialAmount = parseFloat($('#partialAmount').val()) || 0;
                        let remainingAmount = parseFloat($('#remainingAmountHidden').val()) || 0;

                        console.log("Cash partially selected, entered amount:", partialAmount, "Remaining:",
                            remainingAmount);

                        if (!partialAmount || isNaN(partialAmount) || partialAmount <= 0) {
                            isValid = false;
                            $('#partialAmountError').text("Enter a valid positive partial cash amount.");
                            return false;
                        }

                        if (partialAmount > remainingAmount) {
                            isValid = false;
                            $('#partialAmountError').text(
                                "Partial cash amount cannot exceed remaining amount (" + remainingAmount
                                .toFixed(2) + ")."
                            );
                            return false;
                        }

                        if (partialAmount < 0) {
                            isValid = false;
                            $('#partialAmountError').text("Amount cannot be negative.");
                            return false;
                        }

                        console.log("Partial cash amount valid");
                    } else {
                        let cashAmount = $('#cashAmount').val();
                        console.log("Cash fully selected, amount:", cashAmount);
                        if (!cashAmount || parseFloat(cashAmount) <= 0) {
                            isValid = false;
                            console.log("Validation failed: Invalid full cash amount");
                            $('#cashAmountError').text("Enter a valid cash amount.");
                            return false;
                        } else {
                            console.log("Full cash amount valid");
                        }
                    }
                }

                // Online Payment Validation
                if (paymentMethod === 'online') {
                    console.log("Online payment selected");

                    if (!onlineType) {
                        isValid = false;
                        console.log("Validation failed: Online type not selected");
                        $('#onlineTypeError').text("Please select online type.");
                        return false;
                    } else {
                        console.log("Online type selected:", onlineType);
                    }

                    let onlineAmount = parseFloat($('#partialAmount').val()) || parseFloat($(
                        '#upiAmountInput').val()) || 0;
                    let remainingAmount = parseFloat($('#remainingAmountHidden').val()) || 0;

                    console.log("Online amount entered:", onlineAmount, "Remaining:", remainingAmount);

                    // ✅ Check 1: Must be a valid positive number
                    if (!onlineAmount || isNaN(onlineAmount) || onlineAmount <= 0) {
                        isValid = false;
                        if (onlineType === 'online_partially') {
                            console.log("Validation failed: Invalid partial online amount");
                            $('#partialAmountError').text("Enter a valid positive online partial amount.");
                        } else {
                            console.log("Validation failed: Invalid full online amount");
                            $('#upiAmountError').text("Enter a valid positive online amount.");
                        }
                        return false;
                    }

                    // ✅ Check 2: Cannot exceed remaining
                    if (onlineType === 'online_partially' && onlineAmount > remainingAmount) {
                        // console.log(onlineType);
                        isValid = false;
                        console.log("Validation failed: Partial online amount exceeds remaining");
                        $('#partialAmountError').text(
                            "Partial online amount cannot exceed remaining amount (" + remainingAmount
                            .toFixed(2) + ")."
                        );
                        return false;
                    }

                    // ✅ Check 3: Cannot be negative
                    if (onlineAmount < 0) {
                        isValid = false;
                        console.log("Validation failed: Negative online amount");
                        $('#partialAmountError').text("Amount cannot be negative.");
                        return false;
                    }

                    console.log("Online amount valid");
                }


                // Cash + Online Validation
                if (paymentMethod === 'cash_online') {
                    console.log("Cash + Online payment selected");

                    if (!cashOnlineType) {
                        isValid = false;
                        console.log("Validation failed: Cash + Online type not selected");
                        $('#cashOnlineTypeError').text("Please select Cash + Online type.");
                        return false;
                    } else {
                        console.log("Cash + Online type selected:", cashOnlineType);
                    }

                    if (cashOnlineType === 'cash_online_fully') {
                        let cashAmt = $('#cashOnlineFullAmount').val();
                        let onlineAmt = $('#upiOnlineFullAmount').val();
                        console.log("Cash+Online fully amounts:", cashAmt, onlineAmt);

                        if (!cashAmt || parseFloat(cashAmt) <= 0 || !onlineAmt || parseFloat(onlineAmt) <=
                            0) {
                            isValid = false;
                            console.log("Validation failed: Invalid fully cash + online amounts");
                            $('#cashOnlineFullAmountError').text("Enter a valid cash amount.");
                            $('#upiOnlineFullAmountError').text("Enter a valid online amount.");
                            return false;
                        } else {
                            console.log("Fully cash + online amounts valid");
                        }
                    }

                    if (cashOnlineType === 'cash_online_partially') {
                        let cashAmt = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
                        let onlineAmt = parseFloat($('#upiOnlinePartialAmount').val()) || 0;

                        // Clean pending amount
                        let rawPending = $('#remainingCashOnlineAmount').val() || "0";
                        rawPending = rawPending.replace(/[₹,]/g, '').trim();
                        let pendingAmt = parseFloat(rawPending) || 0;

                        console.log("Cash+Online partially amounts:", cashAmt, onlineAmt, "Pending:",
                            pendingAmt);

                        // ✅ Check for invalid or negative input
                        if ((cashAmt <= 0 && onlineAmt <= 0)) {
                            isValid = false;
                            console.log("Validation failed: Invalid partially cash + online amounts");
                            $('#cashOnlinePartialAmountError').text("Enter at least one valid amount.");
                            $('#upiOnlinePartialAmountError').text("Enter at least one valid amount.");
                            return false;
                        }

                        if (cashAmt < 0 || onlineAmt < 0) {
                            isValid = false;
                            console.log("Validation failed: Negative amounts are not allowed");
                            $('#cashOnlinePartialAmountError').text("Negative amount not allowed.");
                            $('#upiOnlinePartialAmountError').text("Negative amount not allowed.");
                            return false;
                        }

                        // ✅ Total should not exceed pending amount
                        let total = cashAmt + onlineAmt;
                        console.log("Total payment:", total, "Pending amount:", pendingAmt);

                        if (total > pendingAmt) {
                            isValid = false;
                            console.log("Validation failed: Total exceeds pending amount");
                            $('#cashOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (" + pendingAmt + ").");
                            $('#upiOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (" + pendingAmt + ").");
                            return false;
                        }

                        console.log("Partially cash + online amounts valid");
                    }

                }



                console.log('done pay');


                if (isValid) {
                    // this.submit(); // submit the form
                }
                let selectedPaymentType = $('#paymentType').val();
                $('#paymentMethodHidden').val(selectedPaymentType);

                if ($("#paymentMethodDiv").hasClass("d-none")) {
                    $("#newEmiHidden").prop("disabled", true).val("");
                }



                let formElement = $(this)[0];
                let formData = new FormData(formElement);

                let submitButton = $(this).find('button[type="submit"]');
                submitButton.prop('disabled', true).text('Processing...');
                if (paymentMethodSelect === 'emi') {
                    let emiTotal = $('#emiTotalCalculated').val();
                    formData.append('emi_paid_value', emiTotal);
                }

                if (selectedPaymentType === 'emi') {
                    let emi_val = $('#emiTotalCalculated').val();
                    formData.append('amount', emi_val);
                }

                $.ajax({
                    url: "/api/sales/make-payment",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $('#makePaymentModal').modal('hide');
                        submitButton.prop('disabled', false).text('Submit Payment');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Payment submitted successfully.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        submitButton.prop('disabled', false).text('Submit Payment');

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMsg = '';
                            $.each(errors, function(key, value) {
                                errorMsg += value + '\n';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errorMsg
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong. Please try again.'
                            });
                        }
                    }
                });
            });


            $.ajax({
                url: "/api/get_orders",
                method: "GET",
                data: selectedSubAdminId ? {
                    selectedSubAdminId: selectedSubAdminId
                } : {},
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {


                    if (response.status) {
                        let tableBody = [];
                        const currencySymbol = response.currency_symbol || '₹';
                        const currencyPosition = response.currency_position || 'left';


                        response.data.forEach(order => {


                            // let createdAt = "N/A";
                            // if (order.order_items && order.order_items.length > 0) {
                            //     // Use created_at of the first order item
                            //     let date = new Date(order.created_date);

                            //     let day = String(date.getDate()).padStart(2, '0');
                            //     let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                            //         "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                            //     ];
                            //     let month = monthNames[date.getMonth()];
                            //     let year = date.getFullYear();

                            //     let hours = date.getHours();
                            //     let minutes = String(date.getMinutes()).padStart(2, '0');
                            //     let ampm = hours >= 12 ? 'PM' : 'AM';
                            //     hours = hours % 12 || 12;

                            //     createdAt =
                            //         `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;
                            // }

                            let amount = parseFloat(order.total_amount).toFixed(2);
                            let displayAmount = currencyPosition === 'right' ?
                                amount + currencySymbol // No space before/after
                                :
                                currencySymbol + amount;

                            let actionBtns = ``;

                            if (parseFloat(order.remaining_amount) > 0) {
                                console.log(order.remaining_amount);
                                actionBtns += `<a href="javascript:void(0);" class="me-3 make-payment-btn" data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                        data-id="${order.id}" data-amount="${order.remaining_amount}" data-method="${order.payment_method || ''}"
                                        data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                                        data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                                        data-remaining-emi-months="${order.remaining_emi_months}" title="Make Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#092C4C" viewBox="0 0 24 24">
                                            <path d="M21 7H3V5h18v2zm0 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9h18zm-2 4H5v6h14v-6zM8 12h2v2H8v-2zm6 0h2v2h-2v-2z"/>
                                        </svg>
                                    </a>`;
                            }
                            actionBtns += `<button class="btn open-history" data-id="${order.id}" title="Payment History">
                                                <i class="fas fa-history" style="font-size: 16px;"></i>
                                            </button>`;
                            @if (app('hasPermission')(2, 'view'))
                                actionBtns += `<a class="me-3" href="/sales-details/${order.id}">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
            </svg>


                                        </a>`;
                            @endif
                            if (!order.has_payment || order.has_payment === 0) {
                                @if (app('hasPermission')(2, 'edit'))
                                    actionBtns += `
                                        <a class="me-3" href="/edit-sales/${order.id}">
                                            <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
            </svg>

                                        </a>`;
                                @endif
                            }
                            @if (app('hasPermission')(2, 'view'))
                                actionBtns += `
                                        <a class="me-3" href="/sales-invoice/${order.id}">


            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 32 32" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M28 24v-4a1 1 0 0 0-2 0v4a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h18a3 3 0 0 0 3-3zm-6.38-5.22-5 4a1 1 0 0 1-1.24 0l-5-4a1 1 0 0 1 1.24-1.56l3.38 2.7V6a1 1 0 0 1 2 0v13.92l3.38-2.7a1 1 0 1 1 1.24 1.56z" data-name="Download" fill="#092C4C" opacity="1" data-original="#092C4C" class=""></path></g></svg>
                                        </a>
                                        <a class="me-3" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});"  title="Print Invoice">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#092C4C" viewBox="0 0 24 24">
                <path d="M19 7V4a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1h-2zM7 4h10v3H7V4zm10 16H7v-4h10v4zm3-6a1 1 0 0 1-1 1h-2v-2H7v2H5a1 1 0 0 1-1-1V9h16v5z"/>
            </svg>
        </a>`;
                            @endif
                            let deleteButton = '';

                            if (
                                userRole !== 'sales-manager' &&
                                userRole !== 'purchase-manager' &&
                                userRole !== 'inventory-manager'
                            ) {
                                @if (app('hasPermission')(2, 'delete'))
                                    actionBtns += `
                                        <a class="me-3 delete-order" href="javascript:void(0);" data-id="${order.id}">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                                <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                            </svg>
                                        </a>
                                    `;
                                @endif
                            }
                            tableBody.push([
                                `<a href="/sales-details/${order.id}" class="">${order.order_number || ''}</a>`,
                                order.created_date,
                                order.user?.name || '',
                                `<span class="badges bg-lightgreen" style="text-transform: capitalize;">${order.payment_status || ''}</span>`,
                                `<span class="badges bg-lightgreen" style="text-transform: capitalize;">${order.payment_method || ''}</span>`,
                                displayAmount || '0.00',
                                order.biller || 'Admin',
                                actionBtns
                            ]);

                        });


                        table.clear().rows.add(tableBody).draw();
                        calculateFilteredTotal();
                        setTimeout(() => {
                            const topScroll = document.querySelector('.table-scroll-top');
                            const tableResponsive = document.querySelector('.table-responsive');
                            const orderTable = document.getElementById('order-table');

                            if (topScroll && tableResponsive && orderTable) {
                                const topInnerDiv = topScroll.querySelector('div');

                                if (topInnerDiv) {
                                    topInnerDiv.style.width = orderTable.scrollWidth + 'px';

                                    // Avoid duplicate listeners
                                    topScroll.onscroll = () => {
                                        tableResponsive.scrollLeft = topScroll.scrollLeft;
                                    };
                                    tableResponsive.onscroll = () => {
                                        topScroll.scrollLeft = tableResponsive.scrollLeft;
                                    };
                                }
                            }
                        }, 100);
                    } else {
                        table.clear().draw();
                        $(".datanew tbody").html('<tr><td colspan="9">No order found</td></tr>');
                        $('#filtered-total').text('₹0.00');
                    }
                },
                error: function() {
                    alert("Failed to load orders.");
                }
            });

            // Function to calculate the total for visible (filtered) rows

            // Month short names
            const monthNames = {
                "01": "Jan",
                "02": "Feb",
                "03": "Mar",
                "04": "Apr",
                "05": "May",
                "06": "Jun",
                "07": "Jul",
                "08": "Aug",
                "09": "Sep",
                "10": "Oct",
                "11": "Nov",
                "12": "Dec"
            };


            // Function to calculate total for visible rows
            function calculateFilteredTotal() {
                let total = 0;

                table.rows({
                    filter: 'applied'
                }).every(function() {
                    const row = this.data();
                    const amountText = row[5]; // column index of amount
                    const rawAmount = parseFloat(amountText.replace(/[^0-9.-]+/g, '')) || 0;
                    total += rawAmount;
                });

                const currencySymbol = "₹";
                $('#filtered-total').text(`${currencySymbol}${total.toFixed(2)}`);
            }

            // Apply month/year filter
            $("#filter-month, #filter-year").on("change", function() {
                const selectedMonth = $("#filter-month").val();
                const selectedYear = $("#filter-year").val();

                let regex = "";
                if (selectedMonth && selectedYear) regex = `${monthNames[selectedMonth]}.*${selectedYear}`;
                else if (selectedMonth) regex = `${monthNames[selectedMonth]}`;
                else if (selectedYear) regex = `${selectedYear}`;

                table.column(1).search(regex, true, false).draw(); // date column index = 1
            });

            // Recalculate total after table redraw
            table.on('draw', function() {
                calculateFilteredTotal();
            });


            $('#filter-date').on('dp.change', function() {
                let selectedDate = $(this).val().trim(); // e.g. DD-MM-YYYY

                // Clear month & year dropdowns
                $('#filter-month').val('');
                $('#filter-year').val('');

                if (selectedDate === '') {
                    fetchAllOrders();
                    return;
                }

                // Convert to YYYY-MM-DD
                if (selectedDate.includes('-')) {
                    let parts = selectedDate.split('-');
                    if (parts.length === 3) {
                        selectedDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                    }
                }

                $.ajax({
                    url: '/api/orders/filter',
                    method: 'GET',
                    data: {
                        date: selectedDate,
                        selectedSubAdminId: selectedSubAdminId
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            renderOrders(response.data, selectedDate);
                            calculateFilteredTotal();
                        }
                    }
                });
            });

            $('#filter-month, #filter-year').on('change', function() {
                const selectedMonth = $('#filter-month').val();
                const selectedYear = $('#filter-year').val();

                if (!selectedMonth && !selectedYear) {
                    fetchAllOrders(); // fallback if no filters
                    return;
                }

                $.ajax({
                    url: '/api/orders/filter',
                    method: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    data: {
                        month: selectedMonth,
                        year: selectedYear,
                        selectedSubAdminId: selectedSubAdminId
                    },
                    success: function(response) {
                        if (response.status) {
                            renderOrdersByMonthAndYear(response.data); // render table
                            // updateTotalFromData(response.data); // calculate total from response
                        }
                    }
                });
            });

            // Function to calculate total from server response
            // function updateTotalFromData(data) {
            //     let total = 0;
            //     data.forEach(row => {
            //         total += parseFloat(row.amount) || 0; // adjust key if your amount field differs
            //     });

            //     const currencySymbol = "₹";
            //     $('#filtered-total').text(`${currencySymbol}${total.toFixed(2)}`);
            // }



            function fetchAllOrders() {
                $.ajax({
                    url: '/api/orders/filter', // Or '/api/orders/all' if available
                    method: 'GET',
                    dataType: 'json',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            renderOrders(response.data, null); // no filter
                        }
                    }
                });
            }
        });

        function renderOrdersByMonthAndYear(data, selectedMonth, selectedYear) {
            let tableBody = [];
            const currencySymbol = '₹';
            const currencyPosition = 'left';

            data.forEach(order => {
                if (!order.created_at) return;

                const date = new Date(order.created_at);
                const orderMonth = String(date.getMonth() + 1).padStart(2, '0'); // "01" to "12"
                const orderYear = String(date.getFullYear());

                if ((selectedMonth && orderMonth !== selectedMonth) || (selectedYear && orderYear !==
                        selectedYear)) {
                    return;
                }

                const day = String(date.getDate()).padStart(2, '0');
                const year = date.getFullYear();
                const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                ];
                const monthText = monthNames[date.getMonth()];
                const hours = date.getHours() % 12 || 12;
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const ampm = date.getHours() >= 12 ? 'PM' : 'AM';

                const formattedDate = `${day}-${monthText}-${year} ${hours}:${minutes} ${ampm}`;
                const amount = parseFloat(order.total_amount).toFixed(2);
                const displayAmount = currencyPosition === 'right' ? amount + currencySymbol : currencySymbol +
                    amount;

                let actionBtns = ``;

                if (parseFloat(order.remaining_amount) > 0) {
                    actionBtns += `<a href="javascript:void(0);" class="me-3 make-payment-btn"
                                data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                data-id="${order.id}" data-amount="${order.remaining_amount}"
                                data-method="${order.payment_method || ''}"
                                data-emi-months="${order.remaining_emi_months}"
                                data-emi-duration="${order.emi_duration || 0}"
                                data-total-amount="${order.total_amount || 0}"
                                data-remaining-amount="${order.remaining_amount}"
                                data-remaining-emi-months="${order.remaining_emi_months}"
                                title="Make Payment">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#092C4C" viewBox="0 0 24 24">
                                    <path d="M21 7H3V5h18v2zm0 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9h18zm-2 4H5v6h14v-6zM8 12h2v2H8v-2zm6 0h2v2h-2v-2z"/>
                                </svg>
                            </a>`;
                }
                actionBtns += `<button class="btn open-history" data-id="${order.id}" title="Payment History">
                                                <i class="fas fa-history" style="font-size: 16px;"></i>
                                            </button>`;
                @if (app('hasPermission')(2, 'view'))

                    actionBtns += `
                                        <a class="me-3" href="/sales-details/${order.id}">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
            </svg>
                                        </a>`;
                @endif
                if (!order.has_payment || order.has_payment === 0) {
                    @if (app('hasPermission')(2, 'edit'))
                        actionBtns += `
                                        <a class="me-3" href="/edit-sales/${order.id}">
                                            <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
            </svg>

                                        </a>`;
                    @endif
                }
                actionBtns += `
                @if (app('hasPermission')(2, 'view'))

                                        <a class="me-3" href="/sales-invoice/${order.id}">


            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 32 32" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M28 24v-4a1 1 0 0 0-2 0v4a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h18a3 3 0 0 0 3-3zm-6.38-5.22-5 4a1 1 0 0 1-1.24 0l-5-4a1 1 0 0 1 1.24-1.56l3.38 2.7V6a1 1 0 0 1 2 0v13.92l3.38-2.7a1 1 0 1 1 1.24 1.56z" data-name="Download" fill="#092C4C" opacity="1" data-original="#092C4C" class=""></path></g></svg>
                                        </a>
                                        <a class="me-3" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});"  title="Print Invoice">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#092C4C" viewBox="0 0 24 24">
                <path d="M19 7V4a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1h-2zM7 4h10v3H7V4zm10 16H7v-4h10v4zm3-6a1 1 0 0 1-1 1h-2v-2H7v2H5a1 1 0 0 1-1-1V9h16v5z"/>
            </svg>
        </a>
                                       
                @endif
                                        ${!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole) ? `
                                        @if (app('hasPermission')(2, 'delete'))
                                        <a class="me-3 delete-order" href="javascript:void(0);" data-id="${order.id}">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                                <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                            </svg>
                                        </a>
                                        @endif
                                        
                                    ` : ''}
    `
                // Your existing HTML table rendering here...
                tableBody.push([
                    `<a href="/sales-details/${order.id}" class="">${order.order_number || ''}</a>`,
                    order.created_date || 'N/A',
                    order.user?.name || 'N/A',
                    `<span class="badges bg-lightgreen" style="text-transform: capitalize;">${order.payment_status || ''}</span>`,
                    `<span class="badges bg-lightgreen" style="text-transform: capitalize;">${order.payment_method || ''}</span>`,
                    displayAmount || '0.00',
                    order.biller || 'Admin',
                    actionBtns
                ]);
            });

            const table = $('#order-table').DataTable();
            table.clear().rows.add(tableBody).draw();
        }

        function renderOrders(data, selectedDate) {
            let tableBody = [];
            const currencySymbol = '₹';
            const currencyPosition = 'left';

            data.forEach(order => {
                if (!order.created_date) return;

                const orderDate = new Date(order.created_date).toISOString().split('T')[0]; // 'YYYY-MM-DD'

                // If selectedDate exists and doesn't match, skip this
                if (selectedDate && selectedDate !== orderDate) return;

                // Format and push to table
                let date = new Date(order.created_date);
                let day = String(date.getDate()).padStart(2, '0');
                let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                ];
                let month = monthNames[date.getMonth()];
                let year = date.getFullYear();

                let hours = date.getHours();
                let minutes = String(date.getMinutes()).padStart(2, '0');
                let ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;

                let amount = parseFloat(order.total_amount).toFixed(2);
                let displayAmount = currencyPosition === 'right' ?
                    amount + currencySymbol : currencySymbol + amount;

                const formattedDate = `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;

                // 🔹 Build action buttons properly
                let actionBtns = ``;

                if (parseFloat(order.remaining_amount) > 0) {
                    actionBtns += `<a href="javascript:void(0);" class="me-3 make-payment-btn"
                                data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                data-id="${order.id}" data-amount="${order.remaining_amount}"
                                data-method="${order.payment_method || ''}"
                                data-emi-months="${order.remaining_emi_months}"
                                data-emi-duration="${order.emi_duration || 0}"
                                data-total-amount="${order.total_amount || 0}"
                                data-remaining-amount="${order.remaining_amount}"
                                data-remaining-emi-months="${order.remaining_emi_months}"
                                title="Make Payment">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#092C4C" viewBox="0 0 24 24">
                                    <path d="M21 7H3V5h18v2zm0 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9h18zm-2 4H5v6h14v-6zM8 12h2v2H8v-2zm6 0h2v2h-2v-2z"/>
                                </svg>
                            </a>`;
                }
                actionBtns += `<button class="btn open-history" data-id="${order.id}" title="Payment History">
                                                <i class="fas fa-history" style="font-size: 16px;"></i>
                                            </button>`;

                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<a class="me-3" href="/sales-details/${order.id}">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
            </svg>


                                        </a>`;
                @endif
                if (!order.has_payment || order.has_payment === 0) {
                    @if (app('hasPermission')(2, 'edit'))
                        actionBtns += `
                                        <a class="me-3" href="/edit-sales/${order.id}">
                                            <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
            </svg>

                                        </a>`;
                    @endif
                }
                actionBtns += `
                @if (app('hasPermission')(2, 'view'))
                                        <a class="me-3" href="/sales-invoice/${order.id}">


            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 32 32" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M28 24v-4a1 1 0 0 0-2 0v4a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h18a3 3 0 0 0 3-3zm-6.38-5.22-5 4a1 1 0 0 1-1.24 0l-5-4a1 1 0 0 1 1.24-1.56l3.38 2.7V6a1 1 0 0 1 2 0v13.92l3.38-2.7a1 1 0 1 1 1.24 1.56z" data-name="Download" fill="#092C4C" opacity="1" data-original="#092C4C" class=""></path></g></svg>
                                        </a>
                                        <a class="me-3" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});"  title="Print Invoice">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#092C4C" viewBox="0 0 24 24">
                <path d="M19 7V4a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1h-2zM7 4h10v3H7V4zm10 16H7v-4h10v4zm3-6a1 1 0 0 1-1 1h-2v-2H7v2H5a1 1 0 0 1-1-1V9h16v5z"/>
            </svg>
        </a>
                        @endif

                                         ${!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole) ? `
                                                                                 @if (app('hasPermission')(2, 'delete'))
                                        <a class="me-3 delete-order" href="javascript:void(0);" data-id="${order.id}">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                                <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                            </svg>
                                        </a>
                                        @endif
                                    ` : ''}
    `

                tableBody.push([
                    `<a href="/sales-details/${order.id}" class="">${order.order_number || ''}</a>`,
                    // formattedDate,
                    order.created_date || 'N/A',
                    order.user?.name || 'N/A',
                    `<span class="badges bg-lightgreen" style="text-transform: capitalize;">${order.payment_status || ''}</span>`,
                    `<span class="badges bg-lightgreen" style="text-transform: capitalize;">${order.payment_method || ''}</span>`,
                    displayAmount || '0.00',
                    order.biller || 'Admin',
                    actionBtns
                ]);
            });

            const table = $('#order-table').DataTable();
            table.clear().rows.add(tableBody).draw();
        }
        // Global history modal open
        $(document).on('click', '.open-history', function() {
            var authToken = localStorage.getItem("authToken");

            const jobCardId = $(this).data('id');
            $('#globalPaymentHistoryList').html('<li class="list-group-item">Loading...</li>');
            $.ajax({
                url: '/api/order/payment-history/' + jobCardId,
                method: 'GET',
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    console.log(response);

                    const history = response.data || [];
                    if (history.length === 0) {
                        $('#globalPaymentHistoryList').html(
                            '<li class="list-group-item">No payment history found.</li>'
                        );
                    } else {
                        const items = history.map(p => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Payment Date: ${(p.payment_date || p.created_at)}</span>
                                    <span><strong>${parseFloat(p.payment_amount || 0).toFixed(2)}</strong> via ${p.payment_method || ''}</span>
                                </li>
                            `).join('');
                        $('#globalPaymentHistoryList').html(items);
                    }
                    const modal = new bootstrap.Modal(document.getElementById(
                        'paymentHistoryModal'));
                    modal.show();
                },
                error: function() {
                    $('#globalPaymentHistoryList').html(
                        '<li class="list-group-item text-danger">Failed to load payment history.</li>'
                    );
                    const modal = new bootstrap.Modal(document.getElementById(
                        'paymentHistoryModal'));
                    modal.show();
                }
            });
        });
        $(document).on('click', '.delete-order', function() {
            var orderId = $(this).data('id'); // ✅ Correct usage
            var authToken = localStorage.getItem("authToken");

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ff9f43",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/delete/' + orderId,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            "Authorization": "Bearer " + authToken,
                        },
                        success: function(response) {
                            if (response.status === true) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: '#ff9f43', // Set OK button color here
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: response.error,
                                    icon: "error",
                                    confirmButtonColor: '#ff9f43',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = "An error occurred while deleting the order";
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }

                            Swal.fire({
                                title: "Error",
                                text: errorMessage,
                                icon: "error",
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }

                    });
                }
            });
        });
        // $('#exportPdf').click(function () {
        //     var authToken = localStorage.getItem("authToken");
        //     let selectedYear = $('#filter-year').val() || '';
        //     let selectedMonth = $('#filter-month').val() || '';
        //     let selectedDate = $('#filter-date').val() || '';
        //     let selectedCustomerId = $('#filter-customer').val() || '';
        //     const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

        //     $.ajax({
        //         url: `/api/pdf-orders`,
        //         type: 'GET',
        //         headers: {
        //             "Authorization": "Bearer " + authToken
        //         },
        //         data: {
        //             year: selectedYear,
        //             month: selectedMonth,
        //             date: selectedDate,
        //             customerId: selectedCustomerId,
        //             selectedSubAdminId: selectedSubAdminId,
        //             type: 'pdf'
        //         },
        //         xhrFields: {
        //             responseType: 'blob'
        //         },
        //         success: function (data, status, xhr) {
        //             let disposition = xhr.getResponseHeader('Content-Disposition');
        //             let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
        //             let filename = (matches != null && matches[1]) ? matches[1].replace(/['"]/g, '') : 'Export.pdf';

        //             let blob = new Blob([data], { type: 'application/pdf' });
        //             let link = document.createElement('a');
        //             link.href = window.URL.createObjectURL(blob);
        //             link.download = filename;
        //             document.body.appendChild(link);
        //             link.click();
        //             document.body.removeChild(link);
        //         },
        //         error: function (xhr) {
        //             alert("PDF export failed: " + (xhr.responseJSON?.message ?? "Unknown error"));
        //         }
        //     });
        // });
        $('#exportPdf').click(function() {
            var authToken = localStorage.getItem("authToken");
            let selectedYear = $('#filter-year').val() || '';
            let selectedMonth = $('#filter-month').val() || '';
            let selectedDate = $('#filter-date').val() || '';
            let selectedCustomerId = $('#filter-customer').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            $.ajax({
                url: `/api/pdf-orders`,
                type: 'GET',
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                data: {
                    year: selectedYear,
                    month: selectedMonth,
                    date: selectedDate,
                    customerId: selectedCustomerId,
                    selectedSubAdminId: selectedSubAdminId,
                    type: 'pdf'
                },
                success: function(response) {
                    if (response.status && response.file_url) {
                        // Open PDF in new tab or trigger download
                        const link = document.createElement('a');
                        link.href = response.file_url;
                        link.download = response.file_name || 'sales_report.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert("Failed to generate PDF: " + response.message);
                    }
                },
                error: function(xhr) {
                    alert("PDF export failed: " + (xhr.responseJSON?.message ?? "Unknown error"));
                }
            });
        });
        $('#exportAllChallan').click(function() {
            let selectedYear = $('#filter-year').val() || '';
            let selectedMonth = $('#filter-month').val() || '';
            let selectedDate = $('#filter-date').val() || '';
            let selectedVendorId = $('#filter-customer').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url =
                `/api/export-order?year=${selectedYear}&month=${selectedMonth}&date=${selectedDate}&customer_id=${selectedVendorId}&selectedSubAdminId=${selectedSubAdminId}`;

            $.ajax({
                url: url,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    if (response.status && response.file_url) {
                        // Trigger download via file_url
                        const link = document.createElement('a');
                        link.href = response.file_url;
                        link.download = response.file_name; // optional
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert("Export failed: " + (response.message || "Unknown error"));
                    }
                },
                error: function(xhr) {
                    console.error("Export failed:", xhr.responseText);
                    alert("Export failed. Please try again.");
                }
            });
        });
    </script>
@endpush
