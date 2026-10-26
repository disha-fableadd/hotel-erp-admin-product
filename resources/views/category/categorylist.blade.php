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
                padding-top: 25px !important;
                width: 50px;
            }

            .toggle-details i {
                font-size: 18px;
            }

            /* Style for category name wrapping */
            table.datanew tbody td:first-child {
                display: flex !important;
                align-items: center !important;
                max-width: calc(100vw - 100px) !important;
            }

            table.datanew tbody td:first-child a {
                /* display: flex !important; */
                align-items: center !important;
                text-align: left !important;
                max-width: 100% !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
                line-height: 1.3 !important;
            }

            .category-name {
                display: inline-block !important;
                max-width: calc(100% - 50px) !important;
                margin-left: 8px !important;
                font-size: 14px !important;
                word-break: break-word !important;
                hyphens: auto !important;
                -webkit-hyphens: auto !important;
                -ms-hyphens: auto !important;
            }

            /* Limit to 2 lines with ellipsis */
            .category-name.truncated {
                display: -webkit-box !important;
                -webkit-line-clamp: 2 !important;
                -webkit-box-orient: vertical !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
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
                <div class="mb-2">
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

                                  tableBody.push([
                                    // Column 1: Category Name with image AND collapsible details
                                    `<div>
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
                                    </div>`,
                                    
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
                            $(".datanew tbody").html('<tr><td colspan="4">No categories found</td></tr>');
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