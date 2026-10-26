    @extends('layout.app')

    @section('title', 'Category List')

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

            /* Desktop: preserve the full category table above the responsive card range. */
            @media (min-width: 1281px) {
                table.datanew thead th,
                table.datanew tbody td {
                    display: table-cell !important;
                }

                /* Hide the mobile-only Details toggle column on desktop. */
                table.datanew thead th.details-column,
                table.datanew tbody td:nth-child(2) {
                    display: none !important;
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

            @media (max-width: 767.98px) {
                .table-top .search-set {
                    display: flex;
                    justify-content: flex-start !important;
                    width: 100%;
                }

                .table-top .search-input {
                    margin-left: 0 !important;
                }
            }

            @media (max-width: 768px) {
                .search-set {
                    margin-right: 1rem !important;
                }
            }

            .category-mobile-card {
                display: none;
            }

            .category-search-bar-wrap {
                display: none;
            }

            /* Match the brand card layout on phones and tablets without changing desktop. */
            @media (max-width: 1280.98px) {
                .category-desktop-search {
                    display: none !important;
                }

                .category-search-bar-wrap {
                    display: block !important;
                    width: 100%;
                    margin-bottom: 14px !important;
                }

                .category-search-box {
                    position: relative;
                    width: 100%;
                }

                .category-search-icon {
                    position: absolute;
                    top: 50%;
                    left: 14px;
                    transform: translateY(-50%);
                    color: #94a3b8;
                    font-size: 14px;
                    pointer-events: none;
                }

                .category-search-input {
                    width: 100% !important;
                    height: 42px !important;
                    padding: 0 14px 0 38px !important;
                    border: 1px solid #e2e8f0 !important;
                    border-radius: 8px !important;
                    background: #ffffff !important;
                    color: #334155 !important;
                    font-size: 13px !important;
                    font-weight: 500 !important;
                    box-shadow: none !important;
                    transition: border-color 0.2s, box-shadow 0.2s;
                }

                .category-search-input::placeholder {
                    color: #94a3b8 !important;
                    letter-spacing: 0.5px !important;
                    text-transform: uppercase !important;
                    font-size: 12px !important;
                }

                .category-search-input:focus {
                    border-color: #cbd5e1 !important;
                    box-shadow: 0 0 0 3px rgba(241, 245, 249, 0.8) !important;
                }

                .table-scroll-top {
                    display: none !important;
                }

                .table-responsive {
                    overflow: visible !important;
                    border: 0 !important;
                    min-height: auto;
                }

                table.datanew {
                    display: block !important;
                    width: 100% !important;
                    margin: 0 !important;
                    border: 0 !important;
                }

                table.datanew thead {
                    display: none !important;
                }

                table.datanew tbody {
                    display: flex !important;
                    flex-direction: column !important;
                    gap: 10px !important;
                    width: 100% !important;
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
                    color: #64748b !important;
                    font-size: 14px !important;
                }

                .category-desktop-content {
                    display: none !important;
                }

                .category-mobile-card {
                    display: flex !important;
                    align-items: center !important;
                    justify-content: space-between !important;
                    width: 100% !important;
                    min-width: 0 !important;
                }

                .category-mobile-left {
                    display: flex !important;
                    align-items: center !important;
                    flex: 1 !important;
                    min-width: 0 !important;
                }

                .category-mobile-avatar {
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 44px !important;
                    height: 44px !important;
                    min-width: 44px !important;
                    flex-shrink: 0 !important;
                    overflow: hidden !important;
                    border-radius: 10px !important;
                    background-color: #fff1ea !important;
                }

                .category-mobile-avatar img {
                    width: 100% !important;
                    height: 100% !important;
                    object-fit: cover !important;
                    border-radius: 10px !important;
                }

                .category-mobile-initial {
                    color: #ea580c !important;
                    font-weight: 700 !important;
                    font-size: 15px !important;
                    letter-spacing: 0.5px !important;
                    text-transform: uppercase !important;
                    line-height: 1 !important;
                }

                .category-mobile-info {
                    flex: 1 !important;
                    min-width: 0 !important;
                    margin-left: 12px !important;
                }

                .category-mobile-name {
                    overflow: hidden !important;
                    margin: 0 0 2px 0 !important;
                    color: #1e293b !important;
                    font-size: 14px !important;
                    font-weight: 700 !important;
                    line-height: 1.25 !important;
                    text-overflow: ellipsis !important;
                    white-space: nowrap !important;
                    text-transform: uppercase !important;
                }

                .category-mobile-date {
                    display: block !important;
                    color: #64748b !important;
                    font-size: 11.5px !important;
                    font-weight: 400 !important;
                    line-height: 1.2 !important;
                }

                .category-mobile-actions {
                    display: flex !important;
                    align-items: center !important;
                    flex-shrink: 0 !important;
                    gap: 8px !important;
                    margin-left: 10px !important;
                }

                .category-mobile-actions a {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 32px !important;
                    height: 32px !important;
                    margin: 0 !important;
                    border-radius: 50% !important;
                    text-decoration: none !important;
                    transition: background-color 0.15s ease, transform 0.15s ease !important;
                }

                .category-mobile-actions a:active {
                    transform: scale(0.95);
                }

                .category-mobile-actions .edit-category {
                    background-color: #eff6ff !important;
                }

                .category-mobile-actions .delete-category {
                    background-color: #fef2f2 !important;
                }

                .category-mobile-actions img {
                    width: 14px !important;
                    height: 14px !important;
                }

                table.datanew tbody tr:has(.dataTables_empty) {
                    grid-column: 1 / -1 !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1280.98px) {
                table.datanew tbody {
                    display: grid !important;
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                    gap: 12px !important;
                }
            }

            @media (max-width: 767.98px) {
                table.datanew tbody {
                    display: flex !important;
                    flex-direction: column !important;
                    gap: 10px !important;
                }
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
            <div class="page-header">
                <div class="page-title">
                    <h4>All Categories</h4>
                </div>
                <div class="page-btn">
                    <a href="{{ route('category.add') }}" class="btn btn-sm btn-added">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">New Category
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="category-search-bar-wrap">
                        <div class="category-search-box">
                            <i class="fa-solid fa-magnifying-glass category-search-icon"></i>
                            <input type="search" id="categoryMobileSearchInput" class="form-control category-search-input" placeholder="SEARCH CATEGORIES...">
                        </div>
                    </div>

                    <div class="mb-2 category-desktop-search">
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
                                    <th>Category Name</th>
                                    <th class="details-column">Details</th>
                                    <th>Created At</th>
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
                $(document).on('keyup input', '#categoryMobileSearchInput', function() {
                    table.search($(this).val()).draw();
                });
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                let url = "/api/getAllCategory";
                if (selectedSubAdminId) {
                    url += `?selectedSubAdminId=${selectedSubAdminId}`;
                }

                fetchCategories(); // Call function to load data

                function fetchCategories() {
                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: "json",
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        success: function(response) {
                            if (response.status) {
                                let categories = response.data;
                                let tableBody = [];

                                // Function to capitalize first letter of each word
                                function capitalizeWords(str) {
                                    if (!str || str.trim() === '') return 'N/A';
                                    return str.replace(/\b\w/g, function(char) {
                                        return char.toUpperCase();
                                    });
                                }

                                categories.forEach((category) => {
                                    let createdAt = category.created_at ? category.created_at.split("T")[0] : "N/A";
                                    let categoryName = capitalizeWords(category.name);
                                    let escapedCategoryName = $('<div>').text(categoryName).html();
                                    let categoryInitial = $('<div>').text(categoryName.charAt(0) || 'C').html();

                                    // Details toggle for mobile
                                    let detailsToggle = `
                                        <a href="#details-${category.id}" class="toggle-details" data-bs-toggle="collapse">
                                            <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                        </a>
                                    `;

                                    // Image URL
                                    let imageUrl = category.image ?
                                        '{{ env('ImagePath') . 'storage/' }}' + category.image :
                                        '{{ env('ImagePath') . '/admin/assets/img/product/noimage.png' }}';

                                    let mobileCardHtml = `
                                        <div class="category-mobile-card">
                                            <div class="category-mobile-left">
                                                <div class="category-mobile-avatar">
                                                    <img src="${imageUrl}" alt="${escapedCategoryName}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <span class="category-mobile-initial" style="display:none;">${categoryInitial}</span>
                                                </div>
                                                <div class="category-mobile-info">
                                                    <div class="category-mobile-name" title="${escapedCategoryName}">${escapedCategoryName}</div>
                                                    <div class="category-mobile-date">Created ${createdAt}</div>
                                                </div>
                                            </div>
                                            <div class="category-mobile-actions">
                                                ${getActionButtons(category.id)}
                                            </div>
                                        </div>
                                    `;

                                    tableBody.push([
                                        // Column 1: Category Name with image AND collapsible details
                                        `<div class="category-desktop-content">
                                            <div style="display: flex; align-items: center;">
                                                <a href="javascript:void(0);" class="d-flex align-items-center">
                                                    <img src="${imageUrl}"
                                                        alt="category"
                                                        style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                                    <span class="category-name truncated">${categoryName}</span>
                                                </a>
                                            </div>

                                            <!-- Collapsible Details (visible only on mobile) - AFTER the category name -->
                                            <div class="collapse mt-2 d-lg-none" id="details-${category.id}">
                                                <div class=" card-body p-2 ">
                                                    <p class="mb-1"><strong>Created At:</strong> ${createdAt}</p>
                                                    <div class="mt-2">
                                                        ${getActionButtons(category.id)}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>${mobileCardHtml}`,

                                        // Column 2: Details Toggle (only for mobile)
                                        detailsToggle,

                                        // Column 3: Created At (hidden on mobile)
                                        createdAt,

                                        // Column 4: Action Buttons (hidden on mobile)
                                        getActionButtons(category.id)
                                    ]);
                                });

                                table.clear().rows.add(tableBody).draw();

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
                                $(".datanew tbody").html('<tr><td colspan="4" class="dataTables_empty">No categories found</td></tr>');
                            }
                        },
                        error: function(xhr) {
                            console.log("Error:", xhr);
                        },
                    });
                }

                // Helper function to generate action buttons
                function getActionButtons(categoryId) {
                    return `
                        <a class="me-3 edit-category" href="/edit-category/${categoryId}">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="img">
                        </a>
                        <a class="me-3 confirm-text delete-category" data-id="${categoryId}" href="javascript:void(0);">
                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="img">
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

                // Delete category function
                $(document).on('click', '.delete-category', function() {
                    var categoryId = $(this).data('id');
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
                                url: `/api/deleteCategory/${categoryId}`,
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
                                            fetchCategories(); // Refresh the table
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: response.message,
                                            icon: "error",
                                            confirmButtonColor: "#ff9f43",
                                            confirmButtonText: "OK"
                                        });
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