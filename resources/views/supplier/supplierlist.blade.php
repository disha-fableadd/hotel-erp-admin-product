@extends('layout.app')

@section('title', 'Vendor List')

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
                overflow-x: hidden;
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
            table.datanew tbody td:nth-child(9) {
                display: none !important;
            }
        }

        /* Mobile: hide non-essential columns, show Details toggle */
        @media (max-width: 768px) {
            table.datanew thead th:nth-child(n+2),
            table.datanew tbody td:nth-child(n+2) {
                display: none !important;
            }

            /* Show only Vendor Name and Details columns on mobile */
            table.datanew thead th:first-child,
            table.datanew tbody td:first-child {
                display: table-cell !important;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(9),
            table.datanew tbody td:last-child {
                display: table-cell !important;
                text-align: center;
                vertical-align: top !important;
                padding-top: 25px !important;
                width: 50px;
            }

            .toggle-details i {
                font-size: 18px;
            }

            /* Style for vendor name wrapping */
            table.datanew thead th:first-child,
            table.datanew tbody td:first-child {
                width: calc(100% - 50px) !important;
            }

            table.datanew {
                width: 100% !important;
                table-layout: fixed !important;
            }

            table.datanew tbody td:first-child a {
                align-items: center !important;
                text-align: left !important;
                max-width: 100% !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
                line-height: 1.3 !important;
            }

            .vendor-name {
                display: inline-block !important;
                /* max-width: calc(100% - 60px) !important; */
                margin-left: 8px !important;
                font-size: 14px !important;
                word-break: break-word !important;
                hyphens: auto !important;
                -webkit-hyphens: auto !important;
                -ms-hyphens: auto !important;
            }

            /* Limit to 2 lines with ellipsis */
            .vendor-name.truncated {
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
            table.datanew tbody td:nth-child(9) {
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

        /* Vendor specific styling */
        .vendor-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 10px;
        }

        /* Collapsible details styling */
        .collapse-details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            /* border-left: 3px solid #ff9f43; */
            width: 100%;
            box-sizing: border-box;
        }

        .detail-item {
            /* display: flex; */
            margin-bottom: 8px;
            font-size: 14px;
            flex-wrap: wrap;
        }

        .detail-label {
            font-weight: 600;
            min-width: 80px;
            max-width: 100px;
            color: #495057;
        }

        .detail-value {
            color: #212529;
            flex: 1;
            word-break: break-word;
        }

        .mobile-actions {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .mobile-actions a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .mobile-actions a:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }

        .mobile-actions img {
            width: 18px;
            height: 18px;
            object-fit: contain;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Vendors</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(10, 'add'))
                <a href="{{ route('vendor.add') }}" class="btn btn-added btn-sm">
                    <img src="{{ env('ImagePath').'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">Add Vendor
                </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body px-3 px-md-4">
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
                                <th>Vendor Name</th>
                                <th>Phone</th>
                                <th>GST Number</th>
                                <th>PAN Number</th>
                                <th>Email</th>
                                <th>Country</th>
                                <th>City</th>
                                <th>Action</th>
                                <th class="details-column">Details</th>
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
    const userRole = "{{ auth()->user()->role }}";
</script>

<script>
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");
        var table = $('.datanew').DataTable(); // Initialize DataTable
        const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
        let url = "/api/getAllSupplier";
        if (selectedSubAdminId) {
            url += `?selectedSubAdminId=${selectedSubAdminId}`;
        }
        
        fetchVendors(); // Call function to load data

        function fetchVendors() {
            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    if (response.status) {
                        let vendors = response.data;
                        let tableBody = [];
                        
                        // Function to capitalize first letter of each word
                        function capitalizeWords(str) {
                            if (!str || str.trim() === '') return 'N/A';
                            return str.replace(/\b\w/g, function(char) {
                                return char.toUpperCase();
                            });
                        }

                        vendors.forEach((vendor) => {
                            let vendorName = capitalizeWords(vendor.name);
                            
                            // Details toggle for mobile
                            let detailsToggle = `
                                <a href="#details-${vendor.id}" class="toggle-details" data-bs-toggle="collapse">
                                    <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                </a>
                            `;

                            // Image URL
                            let defaultVendorImage = '{{ image_path('admin/assets/img/customer/customer5.jpg') }}';
                            let imageUrl = vendor.profile_image_url || (vendor.profile_image ? 
                                '{{ image_path('storage/') }}' + String(vendor.profile_image).replace(/^\/+/, '') :
                                defaultVendorImage);

                            // Prepare delete button conditionally
                            let deleteBtn = '';
                            if (
                                userRole !== 'sales-manager' &&
                                userRole !== 'purchase-manager' &&
                                userRole !== 'inventory-manager'
                            ) {
                                @if (app('hasPermission')(10, 'delete'))
                                deleteBtn = `
                                    <a class="me-2 delete-vendor" data-id="${vendor.id}" href="javascript:void(0);">
                                        <img src="{{ env('ImagePath').'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                    </a>`;
                                @endif
                            }

                            // Prepare action buttons
                            let actionButtons = `
                                @if (app('hasPermission')(10, 'view'))
                                    <a class="me-2" href="/vendor-view/${vendor.id}">
                                        <img src="{{ env('ImagePath').'admin/assets/img/icons/eye.svg' }}" alt="View">
                                    </a>
                                @endif
                                @if (app('hasPermission')(10, 'edit'))
                                    <a class="me-2" href="/edit-vendor/${vendor.id}">
                                        <img src="{{ env('ImagePath').'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                                    </a>
                                @endif
                                ${deleteBtn}
                            `;

                            // Mobile actions for collapsible section
                            let mobileActions = `
                                <div class="mobile-actions">
                                    @if (app('hasPermission')(10, 'view'))
                                        <a href="/vendor-view/${vendor.id}">
                                            <img src="{{ env('ImagePath').'admin/assets/img/icons/eye.svg' }}" alt="View">
                                        </a>
                                    @endif
                                    @if (app('hasPermission')(10, 'edit'))
                                        <a href="/edit-vendor/${vendor.id}">
                                            <img src="{{ env('ImagePath').'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                                        </a>
                                    @endif
                                    ${deleteBtn}
                                </div>
                            `;

                            tableBody.push([
                                // Column 1: Vendor Name with image AND collapsible details
                                `<div>
                                    <div style="display: flex; align-items: center;">
                                        <a href="javascript:void(0);" class="d-flex align-items-center">
                                            <img src="${imageUrl}" 
                                                alt="vendor" 
                                                class="vendor-image"
                                                onerror="this.onerror=null; this.src='${defaultVendorImage}';">
                                            <span class="vendor-name truncated">${vendorName}</span>
                                        </a>
                                    </div>
                                    
                                    <!-- Collapsible Details (visible only on mobile) -->
                                    <div class="collapse mt-2 d-lg-none" id="details-${vendor.id}">
                                        <div class="collapse-details">
                                            <div class="detail-item">
                                                <span class="detail-label">Phone:</span>
                                                <span class="detail-value">${vendor.phone || 'N/A'}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Email:</span>
                                                <span class="detail-value">${vendor.email || 'N/A'}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Country:</span>
                                                <span class="detail-value">${vendor.country || 'N/A'}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">City:</span>
                                                <span class="detail-value">${vendor.city || 'N/A'}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">GST:</span>
                                                <span class="detail-value">${vendor.gst_number || 'N/A'}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">PAN:</span>
                                                <span class="detail-value">${vendor.pan_number || 'N/A'}</span>
                                            </div>
                                            
                                            ${mobileActions}
                                        </div>
                                    </div>
                                </div>`,
                                
                                // Column 2: Phone
                                vendor.phone || 'N/A',
                                
                                // Column 3: GST Number
                                vendor.gst_number || 'N/A',
                                
                                // Column 4: PAN Number
                                vendor.pan_number || 'N/A',
                                
                                // Column 5: Email
                                vendor.email || 'N/A',
                                
                                // Column 6: Country
                                vendor.country || 'N/A',
                                
                                // Column 7: City
                                vendor.city || 'N/A',
                                
                                // Column 8: Action Buttons (hidden on mobile)
                                actionButtons,
                                
                                // Column 9: Details Toggle (only for mobile)
                                detailsToggle
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
                        $(".datanew tbody").html('<tr><td colspan="9">No vendors found</td></tr>');
                    }
                },
                error: function(xhr) {
                    table.clear().draw();
                    $(".datanew tbody").html('<tr><td colspan="9">Error loading vendor data</td></tr>');
                },
            });
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

        // Delete vendor function
        $(document).on('click', '.delete-vendor', function() {
            var vendorId = $(this).data('id');
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
                        url: `/api/deleteSupplier/${vendorId}`,
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
                                    fetchVendors(); // Refresh the table
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
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                message = xhr.responseJSON.error;
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

        // Reset details when switching to desktop
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                // Close all collapsed sections
                $('.collapse').removeClass('show');
                
                // Reset all toggle icons to plus
                $('.toggle-details i')
                    .removeClass('fa-minus-circle')
                    .addClass('fa-plus-circle')
                    .css('color', '#ff9f43');
            }
        });
    });
</script>
@endpush
