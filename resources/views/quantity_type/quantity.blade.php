@extends('layout.app')

@section('title', 'Quantity Type')

@section('content')
<style>
    .sorting_1 {
        display: flex !important;
        align-items: center !important;
        gap: 5px !important;
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

    .table-scroll-top {
        display: none;
    }

    .quantity-type-name {
        display: block !important;
        white-space: normal !important;
        /* allow new line */
        word-break: break-word !important;
        /* break long words */
        overflow-wrap: anywhere !important;
        /* modern browsers */
        max-width: 350px;
        /* adjust as needed */
        line-height: 1.4;
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

        table.datanew thead th,
        table.datanew tbody td {
            display: table-cell !important;
        }

        /* Hide the Details toggle column on desktop */
        table.datanew thead th.details-column,
        table.datanew tbody td:nth-child(2) {
            display: none !important;
        }
    }

    /* Mobile: hide non-essential columns, show Details toggle */
    @media (max-width: 768px) {

        table.datanew thead th:nth-child(n+3),
        table.datanew tbody td:nth-child(n+3) {
            display: none !important;
        }

        table.datanew thead th.details-column,
        table.datanew tbody td:nth-child(2) {
            display: table-cell !important;
            text-align: center;
            vertical-align: top !important;
            padding-top: 18px !important;
            width: 50px;
        }

        .toggle-details i {
            font-size: 18px;
        }

        /* Style for quantity type name wrapping */
        table.datanew tbody td:first-child {
            display: flex !important;
            align-items: center !important;
            max-width: calc(100vw - 100px) !important;
        }

        table.datanew tbody td:first-child span {
            display: inline-block !important;
            /* max-width: calc(100% - 50px) !important; */
            margin-left: 8px !important;
            font-size: 14px !important;
            word-break: break-word !important;
            hyphens: auto !important;
            -webkit-hyphens: auto !important;
            -ms-hyphens: auto !important;
        }

        /* Limit to 2 lines with ellipsis */
        .quantity-type-name.truncated {
            display: -webkit-box !important;
            /* -webkit-line-clamp: 2 !important; */
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .quantity-type-name {
            display: block !important;
            white-space: normal !important;
            /* allow new line */
            word-break: break-word !important;
            /* break long words */
            overflow-wrap: anywhere !important;
            /* modern browsers */
            max-width: 350px;
            /* adjust as needed */
            line-height: 1.4;
        }
    }

    /* Tablet specific fixes */
    @media screen and (width: 768px) {
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }

        table.datanew thead th.details-column,
        table.datanew tbody td:nth-child(2) {
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
</style>

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>All Quantity Types</h4>
        </div>
        <div class="page-btn">
            <button class="btn btn-sm btn-added" id="addQuantityTypeBtn">
                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">
                New Quantity Type
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-scroll-top">
                <div></div>
            </div>
            <div class="table-responsive">
                <table class="table datanew">
                    <thead>
                        <tr>
                            <th>Quantity Type Name</th>
                            <th class="details-column">Details</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="quantitytypeModal" tabindex="-1" aria-labelledby="qauntitytypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="quantitytypeForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="qauntitytypeModalLabel">Add Quantity Type</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="quantity_type_id">
                    <div class="form-group mb-3">
                        <label>Quantity Type Name</label>
                        <input type="text" name="quantity_type" id="quantity_type" class="form-control" placeholder="Enter quantity type">
                        <span class="text-danger" id="quantity_type_error"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");
        var table = $('.datanew').DataTable();
        const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

        let url = "/api/get-all-quantity-types";

        if (selectedSubAdminId) {
            url += `?selectedSubAdminId=${selectedSubAdminId}`;
        }

        fetchQuantityTypes();

        // Fetch Quantity Types
        function fetchQuantityTypes() {
            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    if (response.status) {
                        let rows = [];

                        // Function to capitalize first letter of each word
                        function capitalizeWords(str) {
                            if (!str || str.trim() === '') return 'N/A';
                            return str.replace(/\b\w/g, function(char) {
                                return char.toUpperCase();
                            });
                        }

                        response.data.forEach(qt => {
                            let createdAt = qt.created_at ? qt.created_at.split("T")[0] : "N/A";
                            let quantityTypeName = capitalizeWords(qt.quantity_type);

                            // Details toggle for mobile
                            let detailsToggle = `
                                    <a href="#details-${qt.id}" class="toggle-details" data-bs-toggle="collapse">
                                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                    </a>
                                `;

                            rows.push([
                                // Column 1: Quantity Type Name with collapsible details
                                `<div>
                                        <div style="display: flex; align-items: center;">
                                            <span class="quantity-type-name truncated">${quantityTypeName}</span>
                                        </div>
                                        
                                        <!-- Collapsible Details (visible only on mobile) -->
                                        <div class="collapse mt-2 d-lg-none" id="details-${qt.id}">
                                            <div class="card card-body p-2 bg-light border">
                                                <p class="mb-1"><strong>Created At:</strong> ${createdAt}</p>
                                                <div class="mt-2">
                                                    ${getActionButtons(qt.id, quantityTypeName)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>`,

                                // Column 2: Details Toggle (only for mobile)
                                detailsToggle,

                                // Column 3: Created At (hidden on mobile)
                                createdAt,

                                // Column 4: Action Buttons (hidden on mobile)
                                getActionButtons(qt.id, quantityTypeName)
                            ]);
                        });

                        table.clear().rows.add(rows).draw();

                        // Sync top scrollbar
                        const topScroll = document.querySelector('.table-scroll-top');
                        const tableResponsive = document.querySelector('.table-responsive');
                        const tableElement = document.querySelector('.datanew');

                        if (topScroll && tableResponsive && tableElement) {
                            const topInnerDiv = topScroll.querySelector('div');
                            topInnerDiv.style.width = tableElement.scrollWidth + 'px';

                            topScroll.onscroll = () => {
                                tableResponsive.scrollLeft = topScroll.scrollLeft;
                            };
                            tableResponsive.onscroll = () => {
                                topScroll.scrollLeft = tableResponsive.scrollLeft;
                            };
                        }
                    } else {
                        table.clear().draw();
                        $(".datanew tbody").html('<tr><td colspan="4">No quantity types found</td></tr>');
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        // Helper function to generate action buttons
        function getActionButtons(id, name) {
            return `
                    <a href="javascript:void(0)" class="me-2 edit-quantity-type"
                        data-id="${id}"
                        data-name="${name}">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                    </a>
                    <a href="javascript:void(0)" class="delete-quantity-type" data-id="${id}">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                    </a>
                `;
        }

        // Toggle details icon
        $(document).on('click', '.toggle-details', function() {
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

        // Open Modal for Add
        $('#addQuantityTypeBtn').click(function() {
            $('#quantitytypeForm')[0].reset();
            $('#quantity_type_id').val('');
            $('#quantity_type_error').text('');
            $('#qauntitytypeModalLabel').text('Add Quantity Type');
            $('#quantitytypeModal').modal('show');
        });

        // Open Modal for Edit
        $(document).on('click', '.edit-quantity-type', function() {
            $('#quantity_type_id').val($(this).data('id'));
            $('#quantity_type').val($(this).data('name'));
            $('#qauntitytypeModalLabel').text('Edit Quantity Type');
            $('#quantitytypeModal').modal('show');
        });

        // Save or Update Quantity Type
        $('#quantitytypeForm').on('submit', function(e) {
            e.preventDefault();
            $('#quantity_type_error').text('');

            let id = $('#quantity_type_id').val();
            let formData = new FormData(this);

            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            if (selectedSubAdminId) {
                formData.append('selectedSubAdminId', selectedSubAdminId);
            }

            let apiUrl = id ? `/api/update-quantity-type/${id}` : "/api/add-quantity-type";
            let method = "POST";

            $.ajax({
                url: apiUrl,
                type: method,
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        confirmButtonColor: "#ff9f43"
                    });
                    $('#quantitytypeModal').modal('hide');
                    fetchQuantityTypes();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('#' + key + '_error').text(value[0]);
                        });
                    } else {
                        Swal.fire("Error!", "Something went wrong!", "error");
                    }
                }
            });
        });

        // Delete Quantity Type
        $(document).on('click', '.delete-quantity-type', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: "Are you sure?",
                text: "This will permanently delete the quantity type!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ff9f43",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, delete it!"
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/api/delete-quantity-type/${id}`,
                        type: "POST",
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        success: function(response) {
                            Swal.fire("Deleted!", response.message, "success");
                            fetchQuantityTypes();
                        },
                        error: function() {
                            Swal.fire("Error!", "Unable to delete!", "error");
                        }
                    });
                }
            });
        });
    });
</script>
@endpush