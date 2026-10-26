@extends('layout.app')

@section('title', 'Expense List')

@section('content')
    <style>
        .total_expense {
            font-weight: 600;
            color: #1b2850;
            border: 1px solid #1b2850;
        }

        .table-scroll-top {
            display: none;
        }

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

        .form-control {
            color: #595b5d !important;
        }

        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
            }

            .page-title {
                margin-right: 14rem;
            }

            .page-header {
                display: flex;
                flex-direction: column;
            }

            .total_expense {
                font-size: 12px;
                padding: 4px;
            }

            select#filter-expense-type {
                font-size: 11px;
            }

            select#filter-month {
                font-size: 11px;
            }

            select#filter-year {
                font-size: 11px;
            }

            input#filter-date {
                font-size: 11px;
            }

            .form-label {
                font-size: 12px !important;
            }

            div#DataTables_Table_0_filter {
                margin-top: 10px !important;
            }

            .table-top {
                flex-direction: row;
                margin-bottom: 0 !important;
            }

            .table-top .wordset {
                margin-top: 0 !important;
            }

            .dataTables_length {
                margin-left: .8rem !important;
                margin-bottom: .1rem !important;
            }

            .dataTables_filter {
                text-align: left !important;
            }

            .dataTables_wrapper .row:first-child {
                flex-direction: column !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .dataTables_length, .dataTables_filter {
                margin-left: 0 !important;
                padding-left: 0 !important;
                margin-top: 10px;
            }

            .form-control {
                color: #595b5d !important;
            }

        }

        /* Hide Details column on desktop */
        table.datanew thead th.details-column,
        table.datanew tbody td.details-column {
            display: none !important;
        }

        /* Desktop: Show all columns except Details */
        table.datanew thead th {
            display: table-cell !important;
        }

        table.datanew tbody td {
            display: table-cell !important;
        }

        @media (max-width: 768px) {

            /* Mobile: Hide all columns except Details and essential ones */
            table.datanew thead th:nth-child(n+3),
            table.datanew tbody td:nth-child(n+3) {
                display: none !important;
            }

            table.datanew thead th:first-child,
            table.datanew tbody td:first-child {
                display: none !important; /* Hide SR No */
            }

            table.datanew thead th:nth-child(2),
            table.datanew tbody td:nth-child(2) {
                width: calc(100% - 50px) !important;
                white-space: normal;
                word-wrap: break-word;
            }

            table.datanew {
                width: 100% !important;
                table-layout: fixed !important;
            }

            /* Mobile: Show Details column */
            table.datanew thead th.details-column,
            table.datanew tbody td.details-column {
                display: table-cell !important;
                text-align: center;
                vertical-align: top !important;
                padding-top: 18px !important;
                width: 50px;
            }

            .toggle-details i {
                font-size: 18px;
            }

            /* .collapse-details {
                margin-top: 10px;
                padding: 10px;
                background-color: #f8f9fa;
                border-radius: 5px;
                border-left: 3px solid #ff9f43;
                width: 100%;
                box-sizing: border-box;
            } */

            .detail-item {
                display: flex;
                margin-bottom: 8px;
                font-size: 14px;
                flex-wrap: wrap;
                align-items: center;
            }

            .detail-label {
                font-weight: 600;
                min-width: 90px;
                max-width: 110px;
                color: #495057;
            }

            .detail-value {
                color: #212529;
                flex: 1;
                word-break: break-word;
            }

            .mobile-action-btn img {
                width: 20px;
                height: 20px;
            }
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
            setTimeout(function () {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden'); // Triggers the fade-out transition
                    // Remove the element from DOM after fadeout (optional)
                    setTimeout(function () {
                        alert.style.display = 'none';
                    }, 500); // match the CSS transition duration (0.5s)
                }
            }, 4000);
        </script>
    @endif
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Expenses</h4>
                <!--<h6>Manage your purchases</h6>-->
            </div>


            <div>

                <div class="page-btn d-flex justify-content-between align-items-center">
                    <!-- Total Expense Amount (Left Side) -->
                    <div class="total-expense-summary">
                        <h5 id="total-expense-amount" class="mb-0 px-3 py-1 mx-3 rounded bg-light total_expense">
                            <!-- Gets updated by JS -->
                        </h5>
                    </div>
                    @if (app('hasPermission')(5, 'add'))
                        <!-- New Expense Button (Right Side) -->
                        <a href="{{ route('expense.add') }}" class="btn btn-added d-flex align-items-center btn-sm">
                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-2" alt="img">New
                            Expense
                        </a>
                    @endif
                </div>
            </div>

        </div>

        <div class="card">
            <div class="card-body px-3 px-md-4">
                <!-- <div class="table-top mb-3">
                                                                        <div class="row g-3 align-items-end">


                                                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                                                <label for="filter-expense-type" class="form-label">Expense Type</label>
                                                                                <select id="filter-expense-type" class="form-control">
                                                                                    <option value="">All Expense Types</option>
                                                                                </select>
                                                                            </div>


                                                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                                                <label for="filter-month" class="form-label">Month</label>
                                                                                <select id="filter-month" class="form-control">
                                                                                    <option value="">All Months</option>
                                                                                    @for ($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
            @endfor
                                                                                </select>
                                                                            </div>


                                                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                                                <label for="filter-year" class="form-label">Year</label>
                                                                                <select id="filter-year" class="form-control">
                                                                                    <option value="">All Years</option>
                                                                                    @foreach ($years as $year)
            <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
                                                                                </select>
                                                                            </div>


                                                                            <div class="col-lg-3 col-md-6 col-sm-6">
                                                                                <label for="filter-date" class="form-label">Date</label>
                                                                                <input type="date" id="filter-date" class="form-control" placeholder="dd-mm-yyyy">
                                                                            </div>

                                                                        </div>
                                                                    </div> -->
                <div class="table-top mb-3">
                    <div class="row g-2 align-items-end w-100">
                        <!-- Expense Type -->
                        <div class="col-6 col-sm-3 col-lg-3">
                            <div class="form-group mb-0">
                                <label for="filter-expense-type" class="form-label">Expense Type</label>
                                <select id="filter-expense-type" class="form-control form-control-md">
                                    <option value="">All Expense Types</option>
                                </select>
                            </div>
                        </div>

                        <!-- Month -->
                        <div class="col-6 col-sm-3 col-lg-3">
                            <div class="form-group mb-0">
                                <label for="filter-month" class="form-label">Month</label>
                                <select id="filter-month" class="form-control form-control-md">
                                    <option value="">All Months</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Year -->
                        <div class="col-6 col-sm-3 col-lg-3">
                            <div class="form-group mb-0">
                                <label for="filter-year" class="form-label">Year</label>
                                <select id="filter-year" class="form-control form-control-md">
                                    <option value="">All Years</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-6 col-sm-3 col-lg-3">
                            <!-- <div class="form-group mb-0"> -->
                            <label for="filter-date" class="form-label">Date</label>
                            <input type="text" id="filter-date" placeholder="Choose Date"
                                class="datetimepicker form-control form-control-md">
                            <!-- </div> -->
                        </div>
                    </div>
                </div>

                <!-- Right: Filters (Grouped) -->
                <div class="table-scroll-top">
                    <div></div>
                </div>
                <div class="table-responsive">
                    <table class="table datanew" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Expense name</th>
                                <th class="details-column">Details</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Expense For</th>
                                <th>Expense Type</th> <!-- Add this -->
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="expense-table-body">
                            <!-- Dynamic rows here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="{{ env('ImagePath') . 'admin/assets/plugins/moment/moment.min.js' }}"></script>
    <script src="{{ env('ImagePath') . 'admin/assets/js/bootstrap-datetimepicker.min.js' }}"></script>
    <script>
        const userRole = "{{ auth()->user()->role }}";
    </script>

    <script>
        $(document).ready(function () {
            const authToken = localStorage.getItem("authToken");

            // Initialize DataTable
            const table = $('.datanew').DataTable({
                responsive: false,
                autoWidth: false,
                pageLength: 10,
                ordering: false,
                searching: true,
                paging: true,
                destroy: true,
                language: {
                    emptyTable: "No expenses found.",
                    zeroRecords: "No expense record found.",
                },
            });

            // Load expense types for filter
            loadExpenseTypes();

            // Initial fetch
            fetchExpenses();

            // Trigger fetch on any filter change
            $('#filter-date').on('dp.change', function () {
                fetchExpenses();
            });

            // Trigger fetch when dropdowns change
            $('#filter-expense-type, #filter-month, #filter-year').on('change', function () {
                fetchExpenses();
            });




            // Fetch expenses from API
            function fetchExpenses() {
                function formatToYMD(dateStr) {
                    if (!dateStr || !dateStr.includes('-')) return '';
                    const [day, month, year] = dateStr.split('-');
                    return `${year}-${month}-${day}`;
                }

                const rawDate = $('#filter-date').val();
                const filterDate = formatToYMD(rawDate); // Convert for API

                const expenseTypeId = $('#filter-expense-type').val();
                const filterMonth = $('#filter-month').val();
                const filterYear = $('#filter-year').val();
                var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                let url = '/api/expenses/list?';

                // ✅ Only send `date` OR `month/year`, not both
                if (filterDate && !filterMonth && !filterYear) {
                    // Only date is selected → use date filter
                    url += `date=${filterDate}&`;
                } else {
                    // Otherwise apply month/year combo
                    if (filterMonth) url += `month=${filterMonth}&`;
                    if (filterYear) url += `year=${filterYear}&`;
                }


                if (expenseTypeId) url += `expense_type_id=${expenseTypeId}&`;

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        selectedSubAdminId: selectedSubAdminId,
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function (response) {
                        const currencySymbol = response.currency_symbol || '₹';
                        const currencyPosition = response.currency_position || 'left';

                        const total = parseFloat(response.total_amount || 0).toFixed(2);
                        const totalDisplay =
                            currencyPosition === 'right' ?
                                `${total}${currencySymbol}` :
                                `${currencySymbol}${total}`;
                        $('#total-expense-amount').html(
                            `Total Expenses: <span style="color:#ff9f43">${totalDisplay}</span>`
                        );

                        // Destroy DataTable before making DOM changes
                        if ($.fn.DataTable.isDataTable('.datanew')) {
                            $('.datanew').DataTable().destroy();
                        }

                        // Clear old data
                        const tableBody = $('#expense-table-body');
                        tableBody.empty();

                        response.data.forEach(function (item, index) {
                            const amount = parseFloat(item.amount).toFixed(2);
                            const displayAmount =
                                currencyPosition === 'right' ?
                                    `${amount}${currencySymbol}` :
                                    `${currencySymbol}${amount}`;
                            const expenseTypeName = item.expense_type ? item.expense_type.type :
                                'N/A';
                            const dateFormatted = formatDate(item.expense_date);
                            const description = item.description ?? 'N/A';
                            const srNo = index + 1;

                            // ===== Buttons =====
                            let deleteBtn = '';
                            if (
                                userRole !== 'sales-manager' &&
                                userRole !== 'purchase-manager' &&
                                userRole !== 'inventory-manager'
                            ) {
                                @if (app('hasPermission')(5, 'delete'))
                                    deleteBtn = `
                                            <a class="me-3 delete-btn" href="javascript:void(0);" data-id="${item.id}">
                                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                            </a>`;
                                @endif
                                    }

                            let editBtn = '';
                            @if (app('hasPermission')(5, 'edit'))
                                editBtn = `
                                        <a class="me-3" href="/edit-expense/${item.id}">
                                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                                        </a>`;
                            @endif

                            // ===== Toggle for Mobile =====
                            let detailsToggle = `
                    <a href="javascript:void(0);" class="toggle-details d-md-none" data-id="${item.id}">
                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                    </a>
                `;

                            // ===== Main Row (Always Visible) =====
                            const mainRow = `
                    <tr data-id="${item.id}">
                        <td>${item.sr_no || '-'}</td>
                        <td>
                            <div>
                                <span>${item.expense_name}</span>
                                <div class="details-row-content d-none mt-2 d-md-none collapse-details" id="details-${item.id}">
                                    <div class="detail-item">
                                        <span class="detail-label">Date:</span>
                                        <span class="detail-value">${dateFormatted}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Amount:</span>
                                        <span class="detail-value">${displayAmount}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Expense For:</span>
                                        <span class="detail-value">${description}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Expense Type:</span>
                                        <span class="detail-value">${expenseTypeName}</span>
                                    </div>
                                    <div class="detail-item mt-2 pt-2 border-top">
                                        <span class="detail-label">Action:</span>
                                        <span class="detail-value d-flex gap-2 align-items-center mobile-action-btn">
                                            ${editBtn}${deleteBtn}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="details-column">${detailsToggle}</td>
                        <td>${dateFormatted}</td>
                        <td>${displayAmount}</td>
                        <td>${description}</td>
                        <td>${expenseTypeName}</td>
                        <td>${editBtn}${deleteBtn}</td>
                    </tr>
                `;

                            tableBody.append(mainRow);
                        });

                        // Re-initialize DataTable so search and pagination work
                        $('.datanew').DataTable({
                            responsive: false,
                            autoWidth: false,
                            pageLength: 10,
                            ordering: false,
                            searching: true,
                            paging: true,
                            destroy: true,
                            language: {
                                emptyTable: "No expenses found.",
                                zeroRecords: "No expense record found.",
                            },
                        });
                    },
                    error: function () {
                        alert('Failed to fetch expenses.');
                    }
                });
            }

            // Load expense type dropdown options
            function loadExpenseTypes() {
                var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                
                $.ajax({
                    url: '/api/expense-types',
                    method: 'GET',
                    data: {
                        selectedSubAdminId: selectedSubAdminId,
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function (response) {
                        response.data.forEach(type => {
                            $('#filter-expense-type').append(
                                `<option value="${type.id}">${type.type}</option>`
                            );
                        });
                    }
                });
            }

            // Format date to readable format (e.g. 04 Jun 2025)
            function formatDate(dateStr) {
                const date = new Date(dateStr);
                const options = {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                };
                return date.toLocaleDateString('en-GB', options);
            }

            $(document).on("click", ".delete-btn", function () {
                let id = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    confirmButtonColor: "#ff9f43", // Confirm button color (orange)
                    cancelButtonColor: "#6c757d", // Cancel button color (gray)
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/expenses/${id}`,
                            type: "POST",
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                    "content"),
                                "Authorization": "Bearer " + authToken,
                            },
                            success: function (response) {
                                // Swal.fire("Deleted!", response.message, "success");
                                Swal.fire({
                                    title: "Deleted!",
                                    text: "Delete Record successfully",
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43", // Custom OK button color
                                    confirmButtonText: "OK"
                                });
                                fetchExpenses(); // Refresh the table after deletion
                            },
                            error: function (xhr) {
                                let errorMessage = "Failed to delete customer.";
                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON.error;
                                }
                                Swal.fire("Error!", errorMessage, "error");
                            },
                        });
                    }
                });
            });
            $(document).on('click', '.toggle-details', function () {
                const id = $(this).data('id');
                const icon = $(this).find('i');

                const detailsRow = $(`#details-${id}`);
                detailsRow.toggleClass('d-none');

                if (detailsRow.hasClass('d-none')) {
                    icon.removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color', '#ff9f43');
                } else {
                    icon.removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color', 'red');
                }
            });



        });
    </script>
@endpush