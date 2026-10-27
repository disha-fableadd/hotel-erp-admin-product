@extends('layout.app')
@section('title', 'Plan list')
@section('content')
    <style>
        .icon-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: #f5f5f5;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .icon-btn:hover {
            background: #ececec;
        }

        .action-buttons {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        .dataTables_wrapper .row:first-child {
            display: none !important;
        }

        .dataTables_wrapper {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* Pagination */
        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            color: #fff;
            border: none;
            margin: 0 3px;
            padding: 4px 10px;
            font-weight: bold;
        }

        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            color: #fff;
        }

        .pagination .page-item .page-link:hover {
            background-color: #4a5766;
            color: #fff;
        }

        .pagination .page-item.active .page-link:hover {
            background-color: #e68a35 !important;
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            background-color: #fff;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .pagination .page-item:first-child .page-link:hover,
        .pagination .page-item:last-child .page-link:hover {
            background-color: #f8f9fa;
            color: #495057;
            border-color: #dee2e6;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #fff !important;
            color: #dee2e6 !important;
            border: 1px solid #dee2e6 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Search bar */
        .plan-search-input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .plan-search-input input {
            padding-left: 35px !important;
            border-radius: 5px;
            width: 100%;
        }

        .plan-search-icon {
            position: absolute;
            left: 10px;
            z-index: 10;
            padding: 0;
            top: 50%;
            transform: translateY(-50%);
        }

        table.plan-table td,
        table.plan-table th {
            vertical-align: middle;
        }
    </style>
    <div class="content">
        <div class="">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1" style="color: #092C4C; font-weight: bold;">Plans</h4>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('plans.myplan') }}" class="btn btn-outline-primary"><i class="fa fa-gem me-1"></i> My Plan & Subscription</a>
                    <a href="{{ route('plans.addplan') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Add Plan</a>
                </div>
            </div>

            <!-- @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
    @endif -->

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    {{-- Toolbar --}}
                    <div class="d-flex align-items-end gap-3 mb-3 flex-wrap">
                        <div style="min-width:260px; flex:1 1 260px; max-width:360px;">
                            <label class="form-label mb-1">Search</label>
                            <div class="plan-search-input">
                                <span class="plan-search-icon">
                                    <i class="fas fa-search text-muted" style="font-size:14px;"></i>
                                </span>
                                <input type="text" id="plan-search" class="form-control form-control-sm"
                                    placeholder="Search plans...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0 plan-table datanew-plans">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Total Amount</th>
                                    <th>Renewal Price</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration</th>
                                    <th>User Limit</th>
                                    <th>Branch Limit</th>
                                    <th>Storage Limit</th>
                                    <th>Status</th>
                                    <th style="width:120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="plan-table-body">
                                <tr>
                                    <td colspan="8" class="text-center py-4">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination controls --}}
                    <div
                        class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <span class="me-2" style="font-size:14px;color:#555;">Show per page:</span>
                            <select id="plan-per-page" class="form-select form-select-sm"
                                style="width:auto;border:1px solid #ddd;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="ms-3" style="font-size:14px;color:#555;">
                                <span id="plan-from">0</span> – <span id="plan-to">0</span> of <span
                                    id="plan-total">0</span> items
                            </span>
                        </div>
                        <nav aria-label="Plan page navigation">
                            <ul class="pagination pagination-sm mb-0" id="plan-pagination"></ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem('authToken');
            var subBranchId = localStorage.getItem('selectedSubAdminId');

            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let searchTimer = null;

            // ── Fetch plans ──────────────────────────────────────────────────────────
            function fetchPlans(page) {
                page = page || 1;

                var requestData = {
                    page: page,
                    per_page: perPage,
                    search: searchQuery
                };
                if (subBranchId) {
                    requestData.sub_branch_id = subBranchId;
                }

                $.ajax({
                    url: '/api/getAllPlans',
                    type: 'GET',
                    dataType: 'json',
                    headers: {
                        'Authorization': 'Bearer ' + authToken
                    },
                    data: requestData,
                    success: function(response) {
                        if (response.status) {
                            var plans = response.data;
                            var pagination = response.pagination;

                            if (pagination) {
                                currentPage = pagination.current_page;
                                lastPage = pagination.last_page;
                                updatePagination(pagination);
                            }

                            renderTable(plans);
                        } else {
                            emptyTable();
                        }
                    },
                    error: function() {
                        $('#plan-table-body').html(
                            '<tr><td colspan="8" class="text-center text-danger py-4">Error loading plans.</td></tr>'
                            );
                    }
                });
            }

            // ── Render rows ──────────────────────────────────────────────────────────
            function renderTable(plans) {
                if (!plans || plans.length === 0) {
                    emptyTable();
                    return;
                }

                var html = '';
                $.each(plans, function(i, plan) {
                    var price = (plan.price === null || plan.price === undefined) ?
                        '0.00' :
                        parseFloat(plan.price).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                    var priceDisplay = '₹' + price;
                    if (plan.discount_percent && parseFloat(plan.discount_percent) > 0) {
                        var finalP = plan.final_price ? parseFloat(plan.final_price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : price;
                        priceDisplay = '<div><span class="text-decoration-line-through text-muted small">₹' + price + '</span> <strong class="text-success">₹' + finalP + '</strong> <span class="badge bg-warning text-dark ms-1" style="font-size:10px;">' + parseFloat(plan.discount_percent) + '% OFF</span></div>';
                    }
                    
                    var totalAmount = (plan.total_amount === null || plan.total_amount === undefined) ?
                        '0.00' :
                        parseFloat(plan.total_amount).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    var totalAmountDisplay = '₹' + totalAmount;
                    
                    var renPrice = (plan.renewal_price === null || plan.renewal_price === undefined) ?
                        'N/A' :
                        '₹' + parseFloat(plan.renewal_price).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                    var duration = plan.duration ? (plan.duration.charAt(0).toUpperCase() + plan.duration
                        .slice(1)) : '-';
                    var startDate = plan.start_date ?
                        new Date(plan.start_date).toLocaleDateString('en-GB') :
                        '-';

                    var endDate = plan.end_date ?
                        new Date(plan.end_date).toLocaleDateString('en-GB') :
                        '-';
                    var userLim = plan.user_limit ? plan.user_limit : 'N/A';
                    var bl = plan.branch_limit;
                    var branchLim;
                    if (bl === null || bl === undefined || bl === '') {
                        branchLim = 'Unlimited';
                    } else if (parseInt(bl) === 1) {
                        branchLim = 'Starter';
                    } else if (parseInt(bl) === 3) {
                        branchLim = 'Professional';
                    } else {
                        branchLim = bl;
                    }
                    var storage = plan.storage_limit ? plan.storage_limit + ' GB' : 'N/A';
                    var badge = plan.is_active ?
                        '<span class="badge bg-success">Active</span>' :
                        '<span class="badge bg-secondary">Inactive</span>';
                    var featCount = plan.features_count !== undefined ? plan.features_count : (plan
                        .features ? plan.features.length : 0);
                    var subtitle = plan.subtitle ? '<div class="small text-muted">' + $('<div>').text(plan
                        .subtitle).html() + '</div>' : '';

                    html += '<tr data-id="' + plan.id + '">' +
                        '<td><strong>' + $('<div>').text(plan.name).html() + '</strong>'    +
                        '</td>' +
                        '<td>' + priceDisplay + '</td>' +
                        '<td>' + totalAmountDisplay + '</td>' +
                        '<td>' + renPrice + '</td>' +
                        '<td>' + startDate + '</td>' +
                        '<td>' + endDate + '</td>' +
                        '<td>' + duration + '</td>' +
                        '<td>' + userLim + '</td>' +
                        '<td>' + branchLim + '</td>' +
                        '<td>' + storage + '</td>' +
                        '<td>' + badge + '</td>' +
                        '<td>' +
                        '<div class="action-buttons">'

                        // Edit (pencil)
                        +
                        '<a href="/edit-plan/' + plan.id + '" class="icon-btn" title="Edit Plan">' +
                        '<svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                        '<path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>' +
                        '</svg>' +
                        '</a>'
                        // Delete
                        +
                        '<button class="icon-btn delete-plan-btn" data-id="' + plan.id +
                        '" title="Delete">' +
                        '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                        '<path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>' +
                        '<path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>' +
                        '</svg>' +
                        '</button>' +
                        '</div>' +
                        '</td>' +
                        '</tr>';
                });

                $('#plan-table-body').html(html);
            }

            function emptyTable() {
                $('#plan-table-body').html(
                    '<tr><td colspan="8" class="text-center text-muted py-4">No plans found.</td></tr>');
                $('#plan-from').text(0);
                $('#plan-to').text(0);
                $('#plan-total').text(0);
                $('#plan-pagination').html('');
            }

            // ── Pagination UI ────────────────────────────────────────────────────────
            function updatePagination(p) {
                var from = p.total === 0 ? 0 : (p.current_page - 1) * p.per_page + 1;
                var to = Math.min(p.current_page * p.per_page, p.total);

                $('#plan-from').text(from);
                $('#plan-to').text(to);
                $('#plan-total').text(p.total);

                var html = '';

                // Previous
                html += '<li class="page-item ' + (p.current_page === 1 ? 'disabled' : '') + '">' +
                    '<a class="page-link" href="javascript:void(0);" data-page="' + (p.current_page - 1) +
                    '">Previous</a>' +
                    '</li>';

                var visibleCount = 2;
                var startPage = Math.floor((p.current_page - 1) / visibleCount) * visibleCount + 1;
                var endPage = Math.min(p.last_page, startPage + visibleCount - 1);

                if (startPage > 1) {
                    html += '<li class="page-item">' +
                        '<a class="page-link" href="javascript:void(0);" data-page="' + (startPage - 1) +
                        '" data-action="prev-group">..</a>' +
                        '</li>';
                }

                for (var i = startPage; i <= endPage; i++) {
                    html += '<li class="page-item ' + (i === p.current_page ? 'active' : '') + '">' +
                        '<a class="page-link" href="javascript:void(0);" data-page="' + i + '">' + i + '</a>' +
                        '</li>';
                }

                if (endPage < p.last_page) {
                    html += '<li class="page-item">' +
                        '<a class="page-link" href="javascript:void(0);" data-page="' + (endPage + 1) +
                        '" data-action="next-group">..</a>' +
                        '</li>';
                }

                // Next
                html += '<li class="page-item ' + (p.current_page === p.last_page || p.last_page === 0 ?
                        'disabled' : '') + '">' +
                    '<a class="page-link" href="javascript:void(0);" data-page="' + (p.current_page + 1) +
                    '">Next</a>' +
                    '</li>';

                $('#plan-pagination').html(html);
            }

            // ── Events ───────────────────────────────────────────────────────────────

            // Search (debounced)
            $('#plan-search').on('keyup', function() {
                clearTimeout(searchTimer);
                var val = $(this).val();
                searchTimer = setTimeout(function() {
                    searchQuery = val;
                    fetchPlans(1);
                }, 350);
            });

            // Per-page change
            $('#plan-per-page').on('change', function() {
                perPage = parseInt($(this).val());
                fetchPlans(1);
            });

            // Pagination click
            $(document).on('click', '#plan-pagination .page-link', function(e) {
                e.preventDefault();
                var page = parseInt($(this).data('page'));
                var action = $(this).data('action');

                if (action === 'next-group') {
                    if (page && page <= lastPage) fetchPlans(page);
                    return;
                }
                if (action === 'prev-group') {
                    var prev = Math.max(1, page - 1);
                    fetchPlans(prev);
                    return;
                }
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    fetchPlans(page);
                }
            });

            // Delete plan
            $(document).on('click', '.delete-plan-btn', function() {
                var planId = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff9f43',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/api/deletePlan/' + planId,
                            type: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + authToken
                            },
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonColor: '#ff9f43',
                                        confirmButtonText: 'OK'
                                    });
                                    fetchPlans(currentPage);
                                } else {
                                    Swal.fire('Error!', response.error || response
                                        .message || 'Unknown error', 'error');
                                }
                            },
                            error: function(xhr) {
                                var msg = 'Something went wrong!';
                                if (xhr.responseJSON && xhr.responseJSON.error) msg =
                                    xhr.responseJSON.error;
                                Swal.fire({
                                    title: 'Error!',
                                    text: msg,
                                    icon: 'error',
                                    confirmButtonColor: '#ff9f43',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });

            // ── Initial load ─────────────────────────────────────────────────────────
            fetchPlans(1);

            // ── Refresh when branch changes ──────────────────────────────────────────
            window.addEventListener('storage', function(e) {
                if (e.key === 'selectedSubAdminId') {
                    subBranchId = e.newValue;
                    fetchPlans(1);
                }
            });
        });
    </script>
@endpush
