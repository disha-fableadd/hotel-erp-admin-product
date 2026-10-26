@extends('layout.app')

@section('title', 'Tables List')

@section('content')
<style>
    #DataTables_Table_0_info {
        float: left;
    }

    /* Fix Select2 width inside modals */
    .modal .select2-container {
        width: 100% !important;
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

    /* Desktop view – show all */
    @media (min-width: 768px) {

        table.datanew thead th,
        table.datanew tbody td {
            display: table-cell !important;
        }

        table.datanew thead th.details-column,
        table.datanew tbody td:nth-child(2) {
            display: none !important;
        }
    }
    /* Mobile table name */
    @media (max-width: 767px) {
        .table-name-truncated {
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;   /* max 2 lines */
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            word-break: break-word !important;
            line-height: 1.4;
        }
    }

    /* Mobile view */
    @media (max-width: 767px) {

        /* Hide all except name + details icon */
        table.datanew thead th:nth-child(n+3),
        table.datanew tbody td:nth-child(n+3) {
            display: none !important;
        }

        table.datanew thead th.details-column,
        table.datanew tbody td:nth-child(2) {
            display: table-cell !important;
            width: 50px;
            text-align: center;
            vertical-align: top !important;
            padding-top: 25px !important;
        }

        .toggle-details i {
            font-size: 18px;
            color: #ff9f43;
        }

        .mobile-details-card {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 8px;
            margin-top: 6px;
            font-size: 14px;
        }
    }

    @media screen and (max-width: 767px) {
        .table-scroll-top {
            display: block;
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


    }


    /* Hide details column + rows on desktop */
    .details-col,
    .details-row {
        display: none;
    }

    /* Mobile-only view adjustments */
    @media screen and (max-width: 767px) {

        p {
            margin-bottom: 5px;
        }

        /* Only show name + details column */
        table.datanew thead th:not(:first-child):not(.details-col),
        table.datanew tbody td:not(:first-child):not(.details-col) {
            display: none;
        }

        /* Show details column */
        .details-col {
            display: table-cell !important;
            width: 60px;
        }

        /* Style expanded rows */
        .details-row {
            display: none;
            background-color: #fafafa;
            border-top: 1px solid #eee;
        }

        .details-row td {
            font-size: 14px;
            line-height: 1.6;
        }

        .toggle-details-btn {
            border-radius: 50%;
            padding: 1px 6px;
        }
    }
    @media (max-width: 767px) {

    /* Force width constraint inside DataTable cell */
    table.datanew tbody td:first-child {
        max-width: calc(100vw - 90px) !important; /* space for + icon */
    }

    .table-name-truncated {
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        word-break: break-word !important;
        white-space: normal !important;
        max-width: 100% !important;   /* 🔥 THIS FIXES IT */
    }
}

</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>All Tables</h4>

        </div>
        <div class="page-btn">
            @if (app('hasPermission')(9, 'add'))
            <button class="btn btn-added btn-sm" id="openAddTableModal">
                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img"> New Table
            </button>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-scroll-top">
                <div></div>
            </div>
            <div class="table-responsive">
                <table class="table datanew">
                    <!-- <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Table Type</th>
                            <th>Action</th>
                        </tr>
                    </thead> -->
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th class="details-column">Details</th>
                            <th>Table Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Edit Table Modal -->
<div class="modal fade" id="editTableModal" tabindex="-1" aria-labelledby="editTableModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="edit_table_id">

                <div class="mb-3">
                    <label class="form-label">Table Name</label>
                    <input type="text" id="edit_table_name" class="form-control">
                    <span class="text-danger error-edit-name"></span>
                </div>

                <div class="mb-3">
                    <label class="form-label">Table Type</label>
                    <select id="edit_table_type" class="form-control select22">
                        <option value="">Select Table Type</option>
                        <option value="AC">AC</option>
                        <option value="Non AC">Non AC</option>
                        <option value="Premium">Premium</option>
                    </select>
                    <span class="text-danger error-edit-type"></span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="updateTableBtn">Update</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Table Modal -->
<div class="modal fade" id="addTableModal" tabindex="-1" aria-labelledby="addTableModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Table Name</label>
                    <input type="text" id="add_table_name" class="form-control">
                    <span class="text-danger error-add-name"></span>
                </div>

                <div class="mb-3">
                    <label class="form-label">Table Type</label>
                    <select id="add_table_type" class="form-control select22">
                        <option value="">Select Table Type</option>
                        <option value="AC">AC</option>
                        <option value="Non AC">Non AC</option>
                        <option value="Premium">Premium</option>
                    </select>
                    <span class="text-danger error-add-type"></span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveTableBtn">Save</button>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('.select22').select2();
        var authToken = localStorage.getItem("authToken");
        var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

        // Initialize DataTables with empty data first
        var table = $('.datanew').DataTable({
            destroy: true,
            retrieve: true,
            searching: true,
            ordering: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "",
                searchPlaceholder: "Search tables..."
            }
        });

        // Load data initially
        loadTables(table);

        function clearTableErrors(prefix) {
            $(`.error-${prefix}-name`).text("");
            $(`.error-${prefix}-type`).text("");
        }

        function applyValidationErrors(xhr, prefix) {
            const response = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            const errors = response && response.errors ? response.errors : {};

            if (errors.table_name && errors.table_name.length) {
                $(`.error-${prefix}-name`).text(errors.table_name[0]);
            }

            if (errors.table_type && errors.table_type.length) {
                $(`.error-${prefix}-type`).text(errors.table_type[0]);
            }
        }

        $("#add_table_name, #add_table_type").on("input change", function() {
            clearTableErrors("add");
        });

        $("#edit_table_name, #edit_table_type").on("input change", function() {
            clearTableErrors("edit");
        });

        // Open Add Modal
        $("#openAddTableModal").on("click", function() {
            $("#addTableModal").modal("show");
            $("#add_table_name").val("");
            $("#add_table_type").val("").trigger('change');
            clearTableErrors("add");
        });

        // Save Table
        $("#saveTableBtn").on("click", function() {
            let authToken = localStorage.getItem("authToken");

            clearTableErrors("add");

            let table_name = $("#add_table_name").val().trim();
            let table_type = $("#add_table_type").val();

            let hasError = false;

            if (table_name === "") {
                $(".error-add-name").text("Table name is required.");
                hasError = true;
            }

            if (table_type === "") {
                $(".error-add-type").text("Table type is required.");
                hasError = true;
            }

            if (hasError) return;

            $.ajax({
                url: "/api/table/create",
                type: "POST",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                data: {
                    table_name: table_name,
                    table_type: table_type,
                    selectedSubAdminId: selectedSubAdminId
                },
                success: function(response) {
                    if (response.status) {
                        $("#addTableModal").modal("hide");
                        Swal.fire("Success!", response.message, "success");
                        // Reload the table data
                        loadTables(table);
                    } else {
                        if (response.errors) {
                            applyValidationErrors({
                                responseJSON: response
                            }, "add");
                        } else {
                            Swal.fire("Error!", response.message, "error");
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        applyValidationErrors(xhr, "add");
                        return;
                    }

                    Swal.fire("Error!", "Something went wrong!", "error");
                }
            });
        });

        // 💾 Update Table
        $("#updateTableBtn").on("click", function() {
            let authToken = localStorage.getItem("authToken");

            // Clear previous errors
            clearTableErrors("edit");

            let table_name = $("#edit_table_name").val().trim();
            let table_type = $("#edit_table_type").val();

            let hasError = false;

            if (table_name === "") {
                $(".error-edit-name").text("Table name field is required.");
                hasError = true;
            }

            if (table_type === "") {
                $(".error-edit-type").text("Table type field is required.");
                hasError = true;
            }

            if (hasError) return;

            let tableId = $("#edit_table_id").val();

            $.ajax({
                url: `/api/table/update/${tableId}`,
                type: "PUT",
                data: {
                    table_name: table_name,
                    table_type: table_type
                },
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    if (response.status) {
                        $("#editTableModal").modal("hide");
                        Swal.fire("Updated!", response.message, "success");
                        // Reload the table data
                        loadTables(table);
                    } else {
                        if (response.errors) {
                            applyValidationErrors({
                                responseJSON: response
                            }, "edit");
                        } else {
                            Swal.fire("Error!", response.message, "error");
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        applyValidationErrors(xhr, "edit");
                        return;
                    }

                    Swal.fire("Error!", "Something went wrong!", "error");
                }
            });
        });

        // Use event delegation for dynamically added rows
        $(document).on("click", ".delete-btn", function(e) {
            e.preventDefault();
            let tableId = $(this).data("id");
            let authToken = localStorage.getItem("authToken");

            Swal.fire({
                title: "Are you sure?",
                text: "This table will be permanently deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/api/table/delete/${tableId}`,
                        type: "DELETE",
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        success: function(response) {
                            if (response.status === true) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted",
                                    text: response.message || "Table deleted successfully."
                                });
                                // Reload the table data
                                loadTables(table);
                            } else {
                                Swal.fire({
                                    icon: "warning",
                                    title: "Cannot Delete",
                                    text: response.message || "Failed to delete table"
                                });
                            }
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message || "API request failed!";
                            Swal.fire({
                                icon: "warning",
                                title: "Cannot Delete",
                                text: message
                            });
                        }
                    });
                }
            });
        });

        // ✏️ Open Edit Modal
        $(document).on("click", ".edit-btn", function(e) {
            e.preventDefault();
            let tableId = $(this).data("id");
            let authToken = localStorage.getItem("authToken");

            $.ajax({
                url: `/api/table/${tableId}`,
                type: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    if (response.status === true) {
                        let table = response.data;
                        $("#edit_table_id").val(table.id);
                        $("#edit_table_name").val(table.table_name);
                        $("#edit_table_type").val(table.table_type).trigger('change');
                        $("#editTableModal").modal("show");
                    } else {
                        Swal.fire({
                            icon: "warning",
                            title: "Cannot Edit",
                            text: response.message || "This table has been deleted."
                        });
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || "Failed to load table data";
                    Swal.fire({
                        icon: "warning",
                        title: "Cannot Edit",
                        text: message
                    });
                }
            });
        });
        function getActionButtons(id) {
            return `
                <a class="me-2 edit-btn" data-id="${id}">
                    <img src="{{ env('ImagePath') }}admin/assets/img/icons/edit.svg">
                </a>
                <a class="delete-btn" data-id="${id}">
                    <img src="{{ env('ImagePath') }}admin/assets/img/icons/delete.svg">
                </a>
            `;
        }
        $(document).on('click', '.toggle-details', function () {
            let icon = $(this).find('i');
            if (icon.hasClass('fa-plus-circle')) {
                icon.removeClass('fa-plus-circle')
                    .addClass('fa-minus-circle')
                    .css('color', 'red');
            } else {
                icon.removeClass('fa-minus-circle')
                    .addClass('fa-plus-circle')
                    .css('color', '#ff9f43');
            }
        });

        function loadTables(tableInstance) {
            $.ajax({
                url: "/api/get-all-tables",
                type: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                data: {
                    selectedSubAdminId: selectedSubAdminId
                },
                success: function(response) {
                    if (response.status && response.data.length > 0) {
                        // Clear the existing DataTable data
                        tableInstance.clear();

                        // Add new data to DataTable
                        response.data.forEach(function(row) {
                            // tableInstance.row.add([
                            //     row.table_name ?? '-',
                            //     row.table_type ?? '-',
                            //     `<a class="me-2 text-primary edit-btn" data-id="${row.id}">
                            //         <img src="{{ env('ImagePath') }}admin/assets/img/icons/edit.svg" alt="edit" />
                            //     </a>
                            //     <a class="text-danger delete-btn" data-id="${row.id}">
                            //         <img src="{{ env('ImagePath') }}admin/assets/img/icons/delete.svg" alt="delete" />
                            //     </a>`
                            // ]);
                            // <strong>${row.table_name ?? '-'}</strong>
                           tableInstance.row.add([
                                `
                                <div>
                                    <span class="table-name-truncated">
                                        ${row.table_name ?? '-'}
                                    </span>

                                    <!-- Mobile collapse -->
                                    <div class="collapse d-md-none mt-2" id="details-${row.id}">
                                        <div class="mobile-details-card">
                                            <p><strong>Table Type:</strong> ${row.table_type ?? '-'}</p>
                                            <div>
                                                ${getActionButtons(row.id)}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                `,

                                `
                                <a href="#details-${row.id}" class="toggle-details" data-bs-toggle="collapse">
                                    <i class="fas fa-plus-circle"></i>
                                </a>
                                `,

                                row.table_type ?? '-',

                                getActionButtons(row.id)
                            ]);


                        });

                        // Draw the table
                        tableInstance.draw();
                    } else {
                        // Clear and show "No data" message
                        tableInstance.clear().draw();
                    }
                },
                error: function(xhr) {
                    console.log("Error:", xhr.responseText);
                    tableInstance.clear().draw();
                }
            });
        }
    });
</script>
@endpush
