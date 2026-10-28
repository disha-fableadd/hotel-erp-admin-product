
@extends('layout.app')

@section('title', 'Staff List')

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
            table.datanew tbody td:nth-child(10) {
                display: none !important;
            }
        }

        /* Mobile: hide non-essential columns, show Details toggle */
        @media (max-width: 768px) {
            table.datanew thead th:nth-child(n+2),
            table.datanew tbody td:nth-child(n+2) {
                display: none !important;
            }

            /* Show only Staff Name and Details columns on mobile */
            table.datanew thead th:first-child,
            table.datanew tbody td:first-child {
                display: table-cell !important;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(10),
            table.datanew tbody td:last-child {
                display: table-cell !important;
                text-align: center;
                vertical-align: top !important;
                padding-top: 25px !important;
                width: 50px;
            }

            .toggle-details {
                display: inline-block !important;
                position: sticky !important;
                top: 25px !important;
            }

            .toggle-details i {
                font-size: 18px;
            }

            /* Style for staff name wrapping */
            table.datanew thead th:first-child,
            table.datanew tbody td:first-child {
                width: calc(100% - 50px) !important;
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

            .staff-name {
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
            .staff-name.truncated {
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

        /* Staff specific styling */
        .staff-image {
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
            border-left: 3px solid #ff9f43;
        }

        .detail-item {
            display: flex;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .detail-label {
            font-weight: 600;
            min-width: 100px;
            color: #495057;
        }

        .detail-value {
            color: #212529;
            flex: 1;
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

        .mobile-actions svg {
            width: 18px;
            height: 18px;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Staffs</h4>
            </div>
            <div class="page-btn">
                @if (can_create_user())
                    <a href="{{ route('staff.add') }}" class="btn btn-added btn-sm">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">New Staff
                    </a>
                @else
                    <button type="button" class="btn btn-secondary btn-sm" onclick="Swal.fire({ icon: 'warning', title: 'User Limit Reached', text: 'You have reached the user limit ({{ plan_user_limit() }}) allowed by your active subscription plan. Please upgrade your plan to add more staff.', confirmButtonColor: '#ff9f43' });" title="Plan user limit reached">
                        <i data-feather="lock" class="me-1" style="width: 14px; height: 14px;"></i> New Staff (Limit Reached)
                    </button>
                @endif
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
                                <th>Staff Name</th>
                                <th>Staff Role</th>
                                <th>Phone</th>
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
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            var table = $('.datanew').DataTable(); // Initialize DataTable
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            let url = "/api/getAllStaff";
            if (selectedSubAdminId) {
                url += `?selectedSubAdminId=${selectedSubAdminId}`;
            }
            
            fetchStaff(); // Call function to load data

            function fetchStaff() {
                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let staffs = response.data;
                            let tableBody = [];
                            
                            // Function to capitalize first letter of each word
                            function capitalizeWords(str) {
                                if (!str || str.trim() === '') return 'N/A';
                                return str.replace(/\b\w/g, function(char) {
                                    return char.toUpperCase();
                                });
                            }

                            staffs.forEach((staff) => {
                                let staffName = capitalizeWords(staff.name);
                                
                                // Details toggle for mobile
                                let detailsToggle = `
                                    <a href="#details-${staff.id}" class="toggle-details" data-bs-toggle="collapse">
                                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                    </a>
                                `;

                                // Image URL
                                let defaultStaffImage = '/admin/assets/img/customer/customer5.jpg';
                                let rawStaffImg = staff.profile_image_url || staff.profile_image || '';
                                let imageUrl = window.resolveImageUrl ? window.resolveImageUrl(rawStaffImg, defaultStaffImage) : null;
                                if (!imageUrl) {
                                    if (rawStaffImg) {
                                        let clean = String(rawStaffImg).replace(/^https?:\/\/[^\/]+/, '').replace(/^\/+/, '').replace(/^storage\//, '');
                                        imageUrl = '/storage/' + clean;
                                    } else {
                                        imageUrl = defaultStaffImage;
                                    }
                                }

                                let staffUsername = staff.username ? (staff.username.startsWith('@') ? staff.username : '@' + staff.username) : '';

                                // Prepare action buttons
                                let actionButtons = `
                                    <a class="me-2" href="/staff-view/${staff.id}">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"></path>
                                            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"></path>
                                        </svg>
                                    </a>
                                    <a class="me-2" href="/edit-staff/${staff.id}">
                                        <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"></path>
                                        </svg>
                                    </a>
                                    <a class="me-2 confirm-text delete-staff" data-id="${staff.id}" href="javascript:void(0);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"></path>
                                            <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"></path>
                                        </svg>
                                    </a>
                                `;

                                tableBody.push([
                                    // Column 1: Staff Name with image AND collapsible details
                                    `<div>
                                        <div style="display: flex; align-items: center;">
                                            <a href="/staff-view/${staff.id}" class="d-flex align-items-center text-decoration-none" style="color: inherit;">
                                                <img src="${imageUrl}" 
                                                    alt="staff" 
                                                    class="staff-image"
                                                    onerror="if(this.src && !this.src.includes('customer5.jpg')){this.src='${defaultStaffImage}';}else{this.onerror=null;this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 40 40\'><rect fill=\'%23f3f4f6\' width=\'40\' height=\'40\'/><circle cx=\'20\' cy=\'15\' r=\'8\' fill=\'%23d1d5db\'/><path d=\'M10 34 C10 26, 15 24, 20 24 C25 24, 30 26, 30 34 Z\' fill=\'%23d1d5db\'/></svg>';}">
                                                <div class="d-flex flex-column ms-2">
                                                    <span class="staff-name font-weight-bold" style="color: #1b2850; font-size: 14px;">${staffName}</span>
                                                    ${staffUsername ? `<small class="text-muted"><i class="fas fa-user-tag me-1 text-warning"></i>${staffUsername}</small>` : ''}
                                                </div>
                                            </a>
                                        </div>
                                        
                                        <!-- Collapsible Details (visible only on mobile) -->
                                        <div class="collapse mt-2 d-lg-none" id="details-${staff.id}">
                                            <div class="collapse-details">
                                                <div class="detail-item">
                                                    <span class="detail-label">Username:</span>
                                                    <span class="detail-value">${staffUsername || 'N/A'}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Role:</span>
                                                    <span class="detail-value">${staff.role || 'N/A'}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Phone:</span>
                                                    <span class="detail-value">${staff.phone || 'N/A'}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Email:</span>
                                                    <span class="detail-value">${staff.email || 'N/A'}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Country:</span>
                                                    <span class="detail-value">${staff.country || 'N/A'}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">City:</span>
                                                    <span class="detail-value">${staff.city || 'N/A'}</span>
                                                </div>

                                                <div class="detail-item">
                                                    <span class="detail-label">PAN:</span>
                                                    <span class="detail-value">${staff.pan_number || 'N/A'}</span>
                                                </div>
                                                
                                                <div class="mobile-actions">
                                                    <a href="/staff-view/${staff.id}">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"></path>
                                                            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="/edit-staff/${staff.id}">
                                                        <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"></path>
                                                        </svg>
                                                    </a>
                                                    <a class="delete-staff" data-id="${staff.id}" href="javascript:void(0);">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"></path>
                                                            <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`,
                                    
                                    // Column 2: Staff Role
                                    staff.role || 'N/A',
                                    
                                    // Column 3: Phone
                                    staff.phone || 'N/A',
                                    
                                    // Column 4: PAN Number
                                    staff.pan_number || 'N/A',
                                    
                                    // Column 6: Email
                                    staff.email || 'N/A',
                                    
                                    // Column 7: Country
                                    staff.country || 'N/A',
                                    
                                    // Column 8: City
                                    staff.city || 'N/A',
                                    
                                    // Column 9: Action Buttons (hidden on mobile)
                                    actionButtons,
                                    
                                    // Column 10: Details Toggle (only for mobile)
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
                            $(".datanew tbody").html('<tr><td colspan="9">No staff found</td></tr>');
                        }
                    },
                    error: function(xhr) {
                        console.log("Error:", xhr);
                        table.clear().draw();
                        $(".datanew tbody").html('<tr><td colspan="9">Error loading staff data</td></tr>');
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

            // Delete staff function
            $(document).on('click', '.delete-staff', function() {
                var staffId = $(this).data('id');
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
                            url: `/api/deleteStaff/${staffId}`,
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
                                        fetchStaff(); // Refresh the table
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