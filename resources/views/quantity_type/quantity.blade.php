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
        display: none;
    }

    .table-scroll-top div {
        height: 1px;
    }

    /* Card representation default */
    .quantity-m-card {
        display: none;
    }
    .quantity-search-bar-wrap {
        display: none;
    }
    .quantity-count-sub {
        display: none;
    }

    /* ========================================================
       Desktop View (>= 1281px): UNCHANGED
       ======================================================== */
    @media (min-width: 1281px) {
        .table-scroll-top {
            display: none !important;
        }
        table.datanew thead th,
        table.datanew tbody td {
            display: table-cell !important;
        }
        /* Hide the Details toggle column on desktop */
        table.datanew thead th.details-column,
        table.datanew tbody td:nth-child(2) {
            display: none !important;
        }
        .quantity-m-card {
            display: none !important;
        }
        .quantity-desktop-head {
            display: flex !important;
            align-items: center;
        }
        .quantity-desktop-search {
            display: block !important;
        }
        .quantity-search-bar-wrap {
            display: none !important;
        }
        .quantity-count-sub {
            display: none !important;
        }
    }

    /* ========================================================
       Mobile, Tablet & Nest Hub Max View (<= 1280px): Pooja ERP Style
       ======================================================== */
    @media (max-width: 1280.98px) {
        .table-scroll-top {
            display: none !important;
        }
        .table-responsive {
            overflow: visible !important;
            border: 0 !important;
            min-height: auto;
        }

        /* Page Header */
        .quantity-page-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            margin-bottom: 16px !important;
        }
        .quantity-page-header .page-title h4 {
            font-size: 18px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin-bottom: 2px !important;
        }
        .quantity-count-sub {
            display: block !important;
            font-size: 12px !important;
            color: #64748b !important;
            font-weight: 400 !important;
        }
        .quantity-new-btn {
            background-color: #ff9f43 !important;
            border-color: #ff9f43 !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            padding: 7px 14px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            box-shadow: none !important;
        }
        .quantity-new-btn:hover {
            background-color: #f39c12 !important;
            border-color: #f39c12 !important;
            color: #ffffff !important;
        }
        .quantity-new-btn i, .quantity-new-btn img {
            margin-right: 5px !important;
        }

        /* Search Bar */
        .quantity-desktop-search {
            display: none !important;
        }
        .quantity-search-bar-wrap {
            display: block !important;
            margin-bottom: 14px !important;
        }
        .quantity-search-box {
            position: relative;
            width: 100%;
        }
        .quantity-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }
        .quantity-search-input {
            width: 100% !important;
            height: 42px !important;
            border-radius: 8px !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
            padding: 0 14px 0 38px !important;
            font-size: 13px !important;
            color: #334155 !important;
            font-weight: 500 !important;
            box-shadow: none !important;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .quantity-search-input::placeholder {
            color: #94a3b8 !important;
            letter-spacing: 0.5px !important;
            text-transform: uppercase !important;
            font-size: 12px !important;
        }
        .quantity-search-input:focus {
            border-color: #cbd5e1 !important;
            box-shadow: 0 0 0 3px rgba(241, 245, 249, 0.8) !important;
        }

        /* Table transform to Card Grid */
        table.datanew {
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            border: 0 !important;
        }
        table.datanew thead {
            display: none !important;
        }
        table.datanew tbody tr {
            display: block !important;
            width: 100% !important;
            padding: 12px 14px !important;
            border: 1px solid #edf2f7 !important;
            border-radius: 12px !important;
            background: #ffffff !important;
            box-shadow: 0 1px 3px rgba(16, 24, 40, 0.04) !important;
            margin-bottom: 0 !important;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        table.datanew tbody tr:hover {
            border-color: #e2e8f0 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
        }
        table.datanew tbody tr > td {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }
        table.datanew tbody tr > td:first-child {
            display: block !important;
            width: 100% !important;
            padding: 0 !important;
        }
        table.datanew tbody tr > td:not(:first-child) {
            display: none !important;
        }
        table.datanew tbody tr > td.dataTables_empty {
            display: block !important;
            width: 100% !important;
            text-align: center !important;
            padding: 24px 14px !important;
            color: #94a3b8 !important;
            font-size: 14px !important;
        }

        .quantity-desktop-head {
            display: none !important;
        }

        /* Quantity Card Component */
        .quantity-m-card {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
        }
        .quantity-m-left {
            display: flex !important;
            align-items: center !important;
            flex: 1 !important;
            min-width: 0 !important;
        }
        .quantity-m-avatar {
            width: 44px !important;
            height: 44px !important;
            min-width: 44px !important;
            border-radius: 10px !important;
            background-color: #fff1ea !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
            overflow: hidden !important;
        }
        .quantity-m-initials {
            color: #ea580c !important;
            font-weight: 700 !important;
            font-size: 15px !important;
            letter-spacing: 0.5px !important;
            text-transform: uppercase !important;
            line-height: 1 !important;
        }
        .quantity-m-info {
            margin-left: 12px !important;
            flex: 1 !important;
            min-width: 0 !important;
        }
        .quantity-m-name {
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            text-transform: uppercase !important;
            margin: 0 0 2px 0 !important;
            line-height: 1.25 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .quantity-m-date {
            font-size: 11.5px !important;
            color: #8c98a9 !important;
            font-weight: 400 !important;
            line-height: 1.2 !important;
            display: block !important;
        }
        .quantity-m-actions {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            flex-shrink: 0 !important;
            margin-left: 10px !important;
        }
        .quantity-btn-action {
            width: 32px !important;
            height: 32px !important;
            border-radius: 50% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none !important;
            transition: background-color 0.15s ease, transform 0.15s ease !important;
        }
        .quantity-btn-action:active {
            transform: scale(0.95);
        }
        .quantity-btn-edit {
            background-color: #eff6ff !important;
            color: #3b82f6 !important;
        }
        .quantity-btn-edit:hover {
            background-color: #dbeafe !important;
            color: #2563eb !important;
        }
        .quantity-btn-edit i {
            font-size: 13px !important;
            color: #3b82f6 !important;
        }
        .quantity-btn-delete {
            background-color: #fef2f2 !important;
            color: #ef4444 !important;
        }
        .quantity-btn-delete:hover {
            background-color: #fee2e2 !important;
            color: #dc2626 !important;
        }
        .quantity-btn-delete i {
            font-size: 13px !important;
            color: #ef4444 !important;
        }

        /* DataTable Bottom Pagination Styling */
        div.dataTables_wrapper div.dataTables_length,
        div.dataTables_wrapper div.dataTables_info {
            font-size: 13px !important;
            color: #64748b !important;
        }
        div.dataTables_wrapper div.dataTables_length select {
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 3px 8px !important;
            font-size: 13px !important;
            margin: 0 4px !important;
        }
        div.dataTables_wrapper div.dataTables_paginate {
            margin-top: 8px !important;
        }
        div.dataTables_wrapper div.dataTables_paginate ul.pagination {
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: flex-end !important;
            gap: 3px !important;
        }
        div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-item .page-link {
            border-radius: 6px !important;
            border: 1px solid #e2e8f0 !important;
            color: #475569 !important;
            font-size: 13px !important;
            padding: 4px 10px !important;
        }
        div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-item.active .page-link {
            background-color: #ff7a00 !important;
            border-color: #ff7a00 !important;
            color: #ffffff !important;
        }
    }

    /* ========================================================
       Tablet & Nest Hub Max View (768px to 1280.98px): 2 Column Grid
       ======================================================== */
    @media (min-width: 768px) and (max-width: 1280.98px) {
        table.datanew tbody {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
            width: 100% !important;
        }
        table.datanew tbody tr:has(.dataTables_empty) {
            grid-column: span 2 !important;
        }
        .quantity-new-btn {
            background-color: #ff9f43 !important;
            border-color: #ff9f43 !important;
        }
        .quantity-new-btn:hover {
            background-color: #f39c12 !important;
            border-color: #f39c12 !important;
        }
    }

    /* ========================================================
       Mobile View (< 768px): 1 Column Vertical Stack
       ======================================================== */
    @media (max-width: 767.98px) {
        table.datanew tbody {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            width: 100% !important;
        }
        .quantity-new-btn {
            background-color: #102a54 !important;
            border-color: #102a54 !important;
        }
        .quantity-new-btn:hover {
            background-color: #0b1d3a !important;
            border-color: #0b1d3a !important;
        }
        div.dataTables_wrapper div.row:last-child {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            margin-top: 12px !important;
        }
        div.dataTables_wrapper div.row:last-child > div {
            width: auto !important;
            max-width: 100% !important;
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
    <div class="page-header quantity-page-header">
        <div class="page-title">
            <h4>All Quantity Types</h4>
            <span class="quantity-count-sub" id="quantityCountSub">0 items</span>
        </div>
        <div class="page-btn">
            <button class="btn btn-sm btn-added quantity-new-btn" id="addQuantityTypeBtn">
                <i class="fa-solid fa-plus me-1"></i>New Quantity Type
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Mobile / Tablet Search Bar (Pooja / Dolphin ERP style) -->
            <div class="quantity-search-bar-wrap">
                <div class="quantity-search-box">
                    <i class="fa-solid fa-magnifying-glass quantity-search-icon"></i>
                    <input type="search" id="quantityMobileSearchInput" class="form-control quantity-search-input" placeholder="SEARCH QUANTITY TYPES...">
                </div>
            </div>

            <!-- Desktop Search Bar (Unchanged) -->
            <div class="mb-2 quantity-desktop-search">
                <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
                    <div class="search-path"></div>
                    <div class="search-input">
                        <a class="btn btn-searchset">
                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img">
                        </a>
                    </div>
                </div>
            </div>

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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="quantity_type_id">
                    <div class="form-group mb-3">
                        <label>Quantity Type Name <span class="text-danger field-required-mark">*</span></label>
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

        // Connect mobile/tablet search bar to DataTable
        $(document).on('keyup input', '#quantityMobileSearchInput', function() {
            table.search($(this).val()).draw();
        });

        // Calculate initials for Pooja ERP style avatar
        function getQuantityInitials(name) {
            if (!name) return 'QT';
            let words = name.trim().split(/\s+/).filter(Boolean);
            if (words.length === 0) return 'QT';
            if (words.length === 1) {
                return words[0].substring(0, 1).toUpperCase();
            }
            let firstChar = words[0].charAt(0).toUpperCase();
            let lastChar = words[words.length - 1].charAt(0).toUpperCase();
            return firstChar + lastChar;
        }

        // Format date to DD/MM/YYYY
        function formatCreatedDate(dateStr) {
            if (!dateStr || dateStr === 'N/A') return 'N/A';
            try {
                let cleanStr = dateStr.split("T")[0].split(" ")[0];
                let parts = cleanStr.split("-");
                if (parts.length === 3) {
                    return `${parts[2]}/${parts[1]}/${parts[0]}`;
                }
            } catch (e) {
                console.error(e);
            }
            return dateStr;
        }

        // Capitalize words for desktop view
        function capitalizeWords(str) {
            if (!str || str.trim() === '') return 'N/A';
            return str.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return $('<div>').text(text).html();
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

                        // Update count in header
                        $('#quantityCountSub').text(`${response.data.length} items`);

                        response.data.forEach(qt => {
                            let rawCreatedAt = qt.created_at ? qt.created_at.split("T")[0] : "N/A";
                            let formattedDate = formatCreatedDate(qt.created_at);
                            let quantityTypeName = capitalizeWords(qt.quantity_type);
                            let upperQuantityTypeName = (qt.quantity_type || 'N/A').toUpperCase();
                            let initials = getQuantityInitials(qt.quantity_type);

                            // Details toggle for legacy / desktop
                            let detailsToggle = `
                                <a href="#details-${qt.id}" class="toggle-details" data-bs-toggle="collapse">
                                    <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                </a>
                            `;

                            // Mobile / Tablet Card View (Pooja / Dolphin ERP layout)
                            let mobileCardHtml = `
                                <div class="quantity-m-card">
                                    <div class="quantity-m-left">
                                        <div class="quantity-m-avatar">
                                            <span class="quantity-m-initials">${initials}</span>
                                        </div>
                                        <div class="quantity-m-info">
                                            <div class="quantity-m-name" title="${escapeHtml(upperQuantityTypeName)}">${escapeHtml(upperQuantityTypeName)}</div>
                                            <div class="quantity-m-date">Created ${formattedDate}</div>
                                        </div>
                                    </div>
                                    <div class="quantity-m-actions">
                                        <a href="javascript:void(0)" class="quantity-btn-action quantity-btn-edit edit-quantity-type"
                                            data-id="${qt.id}"
                                            data-name="${escapeHtml(quantityTypeName)}"
                                            title="Edit">
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="quantity-btn-action quantity-btn-delete delete-quantity-type"
                                            data-id="${qt.id}"
                                            title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </div>
                            `;

                            // Desktop View First Cell (Unchanged layout for desktop)
                            let desktopHeadHtml = `
                                <div class="quantity-desktop-head" style="display: flex; align-items: center;">
                                    <span class="quantity-type-name truncated" style="color: #1b2850; font-weight: 500;">${escapeHtml(quantityTypeName)}</span>
                                </div>
                            `;

                            rows.push([
                                // Column 1: Mobile/Tablet Card + Desktop Head
                                `${mobileCardHtml}${desktopHeadHtml}`,

                                // Column 2: Details Toggle (legacy / hidden on desktop)
                                detailsToggle,

                                // Column 3: Created At (desktop view)
                                rawCreatedAt,

                                // Column 4: Action Buttons (desktop view)
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
                        $(".datanew tbody").html('<tr><td colspan="4" class="dataTables_empty">No quantity types found</td></tr>');
                        $('#quantityCountSub').text('0 items');
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        // Helper function to generate action buttons for desktop
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
                        type: 'POST',
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43"
                                });
                                fetchQuantityTypes();
                            } else {
                                Swal.fire("Error!", response.message, "error");
                            }
                        },
                        error: function(xhr) {
                            Swal.fire("Error!", "Something went wrong!", "error");
                        }
                    });
                }
            });
        });
    });
</script>
@endpush