@extends('layout.app')

@section('title', 'Brand List')

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
        .brand-m-card {
            display: none;
        }
        .brand-search-bar-wrap {
            display: none;
        }
        .brand-count-sub {
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
            .brand-m-card {
                display: none !important;
            }
            .brand-desktop-head {
                display: flex !important;
                align-items: center;
            }
            .brand-desktop-search {
                display: block !important;
            }
            .brand-search-bar-wrap {
                display: none !important;
            }
            .brand-count-sub {
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
            .brand-page-header {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                margin-bottom: 16px !important;
            }
            .brand-page-header .page-title h4 {
                font-size: 18px !important;
                font-weight: 700 !important;
                color: #1e293b !important;
                margin-bottom: 2px !important;
            }
            .brand-count-sub {
                display: block !important;
                font-size: 12px !important;
                color: #64748b !important;
                font-weight: 400 !important;
            }
            .brand-new-btn {
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
            .brand-new-btn:hover {
                background-color: #f39c12 !important;
                border-color: #f39c12 !important;
                color: #ffffff !important;
            }
            .brand-new-btn i, .brand-new-btn img {
                margin-right: 5px !important;
            }

            /* Search Bar */
            .brand-desktop-search {
                display: none !important;
            }
            .brand-search-bar-wrap {
                display: block !important;
                margin-bottom: 14px !important;
            }
            .brand-search-box {
                position: relative;
                width: 100%;
            }
            .brand-search-icon {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                font-size: 14px;
                pointer-events: none;
            }
            .brand-search-input {
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
            .brand-search-input::placeholder {
                color: #94a3b8 !important;
                letter-spacing: 0.5px !important;
                text-transform: uppercase !important;
                font-size: 12px !important;
            }
            .brand-search-input:focus {
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

            .brand-desktop-head {
                display: none !important;
            }

            /* Brand Card Component */
            .brand-m-card {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                width: 100% !important;
            }
            .brand-m-left {
                display: flex !important;
                align-items: center !important;
                flex: 1 !important;
                min-width: 0 !important;
            }
            .brand-m-avatar {
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
            .brand-m-avatar img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                border-radius: 10px !important;
            }
            .brand-m-initials {
                color: #ea580c !important;
                font-weight: 700 !important;
                font-size: 15px !important;
                letter-spacing: 0.5px !important;
                text-transform: uppercase !important;
                line-height: 1 !important;
            }
            .brand-m-info {
                margin-left: 12px !important;
                flex: 1 !important;
                min-width: 0 !important;
            }
            .brand-m-name {
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
            .brand-m-date {
                font-size: 11.5px !important;
                color: #8c98a9 !important;
                font-weight: 400 !important;
                line-height: 1.2 !important;
                display: block !important;
            }
            .brand-m-actions {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                flex-shrink: 0 !important;
                margin-left: 10px !important;
            }
            .brand-btn-action {
                width: 32px !important;
                height: 32px !important;
                border-radius: 50% !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
                transition: background-color 0.15s ease, transform 0.15s ease !important;
            }
            .brand-btn-action:active {
                transform: scale(0.95);
            }
            .brand-btn-edit {
                background-color: #eff6ff !important;
                color: #3b82f6 !important;
            }
            .brand-btn-edit:hover {
                background-color: #dbeafe !important;
                color: #2563eb !important;
            }
            .brand-btn-edit i {
                font-size: 13px !important;
                color: #3b82f6 !important;
            }
            .brand-btn-delete {
                background-color: #fef2f2 !important;
                color: #ef4444 !important;
            }
            .brand-btn-delete:hover {
                background-color: #fee2e2 !important;
                color: #dc2626 !important;
            }
            .brand-btn-delete i {
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
            .brand-new-btn {
                background-color: #ff9f43 !important;
                border-color: #ff9f43 !important;
            }
            .brand-new-btn:hover {
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
            .brand-new-btn {
                background-color: #102a54 !important;
                border-color: #102a54 !important;
            }
            .brand-new-btn:hover {
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

    @if (session('error'))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') }}
        </div>

        <script>
            setTimeout(function() {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden');
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }
            }, 4000);
        </script>
    @endif

    <div class="content">
        <div class="page-header brand-page-header">
            <div class="page-title">
                <h4>All Brands</h4>
                <span class="brand-count-sub" id="brandCountSub">0 items</span>
            </div>
            <div class="page-btn">
                <a href="{{ route('brand.add') }}" class="btn btn-sm btn-added brand-new-btn">
                    <i class="fa-solid fa-plus me-1"></i>New Brand
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Mobile / Tablet Search Bar (Pooja / Dolphin ERP style) -->
                <div class="brand-search-bar-wrap">
                    <div class="brand-search-box">
                        <i class="fa-solid fa-magnifying-glass brand-search-icon"></i>
                        <input type="search" id="brandMobileSearchInput" class="form-control brand-search-input" placeholder="SEARCH BRANDS...">
                    </div>
                </div>

                <!-- Desktop Search Bar (Unchanged) -->
                <div class="mb-2 brand-desktop-search">
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
                                <th>Image</th>
                                <th>Brand Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            var table = $('.datanew').DataTable(); // Initialize DataTable
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            let url = "/api/getAllBrand";
            if (selectedSubAdminId) {
                url += `?selectedSubAdminId=${selectedSubAdminId}`;
            }

            // Connect mobile/tablet search bar to DataTable
            $(document).on('keyup input', '#brandMobileSearchInput', function() {
                table.search($(this).val()).draw();
            });

            // Calculate initials for Pooja ERP style avatar
            function getBrandInitials(name) {
                if (!name) return 'BR';
                let words = name.trim().split(/\s+/).filter(Boolean);
                if (words.length === 0) return 'BR';
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

            fetchBrands(); // Call function to load data

            function fetchBrands() {
                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let brands = response.data;
                            let tableBody = [];

                            // Update count in header
                            $('#brandCountSub').text(`${brands.length} items`);

                            brands.forEach((brand) => {
                                let formattedDate = formatCreatedDate(brand.created_at);
                                let brandName = capitalizeWords(brand.name);
                                let upperBrandName = (brand.name || 'N/A').toUpperCase();
                                let initials = getBrandInitials(brand.name);
                                const imagePath = "{{ env('ImagePath') }}";
                                let imageUrl = brand.logo ?
                                    `${imagePath}/storage/${brand.logo}` :
                                    `${imagePath}admin/assets/img/product/noimage.png`;

                                // Avatar HTML: image with fallback to initials badge
                                let avatarHtml = '';
                                if (brand.logo) {
                                    avatarHtml = `
                                        <div class="brand-m-avatar">
                                            <img src="${imageUrl}" alt="${escapeHtml(upperBrandName)}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <span class="brand-m-initials" style="display:none;">${initials}</span>
                                        </div>
                                    `;
                                } else {
                                    avatarHtml = `
                                        <div class="brand-m-avatar">
                                            <span class="brand-m-initials">${initials}</span>
                                        </div>
                                    `;
                                }

                                // Mobile / Tablet Card View (Pooja / Dolphin ERP layout)
                                let mobileCardHtml = `
                                    <div class="brand-m-card">
                                        <div class="brand-m-left">
                                            ${avatarHtml}
                                            <div class="brand-m-info">
                                                <div class="brand-m-name" title="${escapeHtml(upperBrandName)}">${escapeHtml(upperBrandName)}</div>
                                                <div class="brand-m-date">Created ${formattedDate}</div>
                                            </div>
                                        </div>
                                        <div class="brand-m-actions">
                                            <a href="/edit-brand/${brand.id}" class="brand-btn-action brand-btn-edit" title="Edit">
                                                <i class="fa-solid fa-pencil"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="brand-btn-action brand-btn-delete confirm-text delete-brand" data-id="${brand.id}" title="Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </div>
                                `;

                                // Desktop View First Cell
                                let desktopHeadHtml = `
                                    <div class="brand-desktop-head" style="display: flex; align-items: center;">
                                        <a href="javascript:void(0);" class="product-img mb-1">
                                            <img src="${imageUrl}" alt="product" style="max-width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                        </a>
                                    </div>
                                `;

                                tableBody.push([
                                    // Column 1: Mobile/Tablet Card + Desktop Head
                                    `${mobileCardHtml}${desktopHeadHtml}`,

                                    // Column 2: Brand Name (desktop view)
                                    brandName,

                                    // Column 3: Action Buttons (desktop view)
                                    `
                                    <a class="me-3" href="/edit-brand/${brand.id}">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                                    </a>
                                    <a class="me-3 confirm-text delete-brand" data-id="${brand.id}" href="javascript:void(0);">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                    </a>
                                    `
                                ]);
                            });

                            table.clear().rows.add(tableBody).draw();
                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html('<tr><td colspan="3" class="dataTables_empty">No brands found</td></tr>');
                            $('#brandCountSub').text('0 items');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching brands:", error);
                    }
                });
            }

            $(document).on('click', '.delete-brand', function() {
                var brandId = $(this).data('id');
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
                            url: `/api/deleteBrand/${brandId}`,
                            type: 'POST',
                            headers: {
                                "Authorization": "Bearer " + authToken,
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
                                        confirmButtonColor: "#ff9f43",
                                        confirmButtonText: "OK"
                                    }).then(() => {
                                        fetchBrands();
                                    });
                                } else {
                                    Swal.fire("Error!", response.message, "error");
                                }
                            },
                            error: function(xhr) {
                                let message = "Something went wrong!";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    title: "Error!",
                                    text: message,
                                    icon: "error",
                                    confirmButtonColor: "#ff9f43",
                                    confirmButtonText: "OK"
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
