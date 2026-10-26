@extends('layout.app')

@section('title', 'Expense Type List')

@section('content')
    <style>
        @media screen and (max-width: 768px) {
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
        }
    </style>
    @if(session('error'))
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

            .dataTables_info {
                float: left !important;
            }

            @media screen and (max-width: 768px) {
                input.form-control.form-control-sm {
                    margin-top: 10px;
                }
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
                <h4>All Expenses Type</h4>
                <!--<h6>Manage your purchases</h6>-->
            </div>
            <div class="page-btn">
                                @if (app('hasPermission')(5, 'view'))
                <a href="{{route('expensetype.add')}}" class="btn btn-sm btn-added"><img
                        src="{{ env('ImagePath').'admin/assets/img/icons/plus.svg' }}" class="me-2" alt="img">New Expense Type</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- <div class="table-top d-flex justify-content-between align-items-end mb-3">
                    Left: Search Button
                    <div class="search-set">
                        <div class="search-path"></div>
                        <div class="search-input d-flex align-items-center gap-2">
                            <input type="text" id="custom-search-input" class="form-control"
                                placeholder="Search expenses..." style="width: 200px;" />
                            <a class="btn btn-searchset" id="toggle-search">
                                <img src="admin/assets/img/icons/search-white.svg" alt="Search">
                            </a>
                        </div>
                    </div>

                    Right: Filters (Grouped)
                    <div class="d-flex gap-3">
                        <div class="date-filter">
                            <input type="date" id="filter-date" class="form-control" style="width: 200px;">
                        </div>
                    </div>
                </div> -->
                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Expense Type</th>
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
    <script>
        const userRole = "{{ auth()->user()->role }}";
    </script>

    <script>
        $(document).ready(function () {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            // Initialize DataTable with search logic enabled
            var table = $('.datanew').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                ordering: true,
                searching: true, // Enable search logic
                paging: true,
                destroy: true,
                language: {
                    emptyTable: "No expenses found.",
                    zeroRecords: "No expense record found.",
                },
            });

            // Hide default DataTable search box
            // $('.dataTables_filter').hide();

            // // Toggle visibility of the custom search input
            // $('#toggle-search').on('click', function() {
            //     $('#custom-search-input').toggle().focus();
            // });

            // // Link custom search box to DataTable search
            // $('#custom-search-input').on('keyup', function() {
            //     table.search(this.value).draw();
            // });
            fetchExpenses();

            


            function fetchExpenses() {
                $.ajax({
                    url: '/api/expense-types', // Make sure this returns only isDeleted = 0
                    method: 'GET',
                    data: { selectedSubAdminId: selectedSubAdminId },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function (response) {
                        let tableRows = [];

                        response.data.forEach(function (item, index) {
                               let deleteBtn = '';

                                if (
                                    userRole !== 'sales-manager' &&
                                    userRole !== 'purchase-manager' &&
                                    userRole !== 'inventory-manager'
                                ) {
                                        @if (app('hasPermission')(5, 'delete'))
                                    deleteBtn = `
                                        <a class="me-3 delete-btn" href="javascript:void(0);" data-id="${item.id}">
                                            <img src="{{ env('ImagePath').'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                        </a>`;
                                        @endif
                                }
                            tableRows.push([
                                index + 1, // S.No.
                                item.type,
                                `
                            @if (app('hasPermission')(5, 'view'))
                        <a class="me-3" href="/edit-expense-type/${item.id}">
                            <img src="{{ env('ImagePath').'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                        </a>
                        @endif
                       ${deleteBtn}
                        `
                            ]);
                        });

                        table.clear().rows.add(tableRows).draw();
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to fetch expense types!',
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                });
            }

            // $('#filter-date').on('change', function() {
            //     const selectedDate = $(this).val(); // format: YYYY-MM-DD
            //     fetchExpenses(selectedDate);
            // });


            // function formatDate(dateStr) {
            //     const date = new Date(dateStr);
            //     const options = {
            //         day: '2-digit',
            //         month: 'short',
            //         year: 'numeric'
            //     };
            //     return date.toLocaleDateString('en-GB', options); // e.g. 13 May 2025
            // }

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
                            url: `/api/expenses-type/${id}`,
                            type: "POST",
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
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
        });
    </script>
@endpush