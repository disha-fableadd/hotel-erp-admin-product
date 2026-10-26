@extends('layout.app')

@section('title', 'Leave Types')

@section('content')
    <style>
        .table-search-wrap {
            max-width: 280px;
        }

        .leave-type-pagination-controls {
            border-top: 1px solid #eef0f3;
            padding-top: 14px;
        }

        .table-pagination-btn:not(:disabled) {
            background-color: #FF9F43;
            border-color: #FF9F43;
            color: #fff;
        }

        .table-pagination-btn:not(:disabled):hover,
        .table-pagination-btn:not(:disabled):focus {
            background-color: #f58f2f;
            border-color: #f58f2f;
            color: #fff;
            box-shadow: none;
        }

        /* Mobile card view styles */
        @media (max-width: 768px) {
            .table-responsive {
                display: none;
            }

            .mobile-card-view {
                display: block;
            }

            .leave-type-card {
                background: #fff;
                border: 1px solid #e0e6f1;
                border-radius: 8px;
                margin-bottom: 12px;
                position: relative;
                transition: all 0.3s ease;
            }

            .leave-type-card.expanded {
                border: 2px solid #FF9F43;
                box-shadow: 0 2px 8px rgba(255, 159, 67, 0.15);
            }

            .card-header-mobile {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px;
                cursor: pointer;
                user-select: none;
            }

            .card-header-mobile:hover {
                background-color: #f9f9f9;
            }

            .card-title-section {
                flex: 1;
            }

            .card-title-section p {
                margin: 4px 0;
                font-size: 14px;
                color: #333;
            }

            .card-title-section .title {
                font-weight: 600;
                font-size: 15px;
                color: #000;
            }

            .expand-btn {
                background: #FF9F43;
                color: #fff;
                border: none;
                border-radius: 50%;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 20px;
                font-weight: bold;
                transition: all 0.3s ease;
                margin-left: 12px;
                padding: 0;
            }

            .expand-btn:hover {
                background: #f58f2f;
                transform: scale(1.05);
            }

            .expand-btn.expanded {
                background: #dc3545;
            }

            .expand-btn.expanded:hover {
                background: #c82333;
            }

            .card-details-mobile {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
                border-top: 1px solid #e0e6f1;
            }

            .card-details-mobile.show {
                max-height: 200px;
            }

            .card-details-content {
                padding: 16px;
                background: #fafbfc;
            }

            .detail-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 12px;
                font-size: 14px;
            }

            .detail-row:last-child {
                margin-bottom: 0;
            }

            .detail-label {
                color: #666;
                font-weight: 500;
            }

            .detail-value {
                color: #333;
                font-weight: 600;
            }

            .card-actions {
                display: flex;
                gap: 8px;
                margin-top: 16px;
                padding-top: 16px;
                border-top: 1px solid #e0e6f1;
            }

            .card-actions button {
                flex: 1;
                padding: 8px 12px;
                font-size: 13px;
                border-radius: 6px;
                border: none;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .card-actions .btn-edit {
                background: #FFC107;
                color: #fff;
            }

            .card-actions .btn-delete {
                background: #DC3545;
                color: #fff;
            }

            .card-actions button:active {
                transform: scale(0.95);
            }

            .pagination-controls {
                margin-top: 20px;
            }
        }

        @media (min-width: 769px) {
            .mobile-card-view {
                display: none;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Manage Leave Types</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('leave-type.create') }}" class="btn btn-added">
                    <i class="fa fa-plus me-1"></i> Add Leave Type
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-search-wrap mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="leaveTypeSearch" placeholder="Search...">
                    </div>
                </div>
                <!-- Desktop table view -->
                <div class="table-responsive">
                    <table class="table" id="leaveTypeTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Leave Type</th>
                                <th>Number of Leaves</th>
                                <th>Allow Half Day</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Mobile card view -->
                <div class="mobile-card-view" id="leaveTypeCardView">
                    <!-- Cards will be populated by JS -->
                </div>

                <div class="pagination-controls leave-type-pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3" id="leaveTypePaginationWrapper" style="display:none;">
                    <div class="d-flex align-items-center mb-3 mb-md-0 flex-wrap gap-2">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="leaveTypePerPage" class="form-select form-select-sm" style="width: auto; border: 1px solid #ddd;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="leaveTypePaginationFrom">0</span> - <span id="leaveTypePaginationTo">0</span> of <span id="leaveTypePaginationTotal">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Leave type pagination">
                        <ul class="pagination pagination-sm mb-0" id="leaveTypePaginationNumbers">
                            <!-- page numbers will be populated by JS -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const token = (typeof window.getAuthToken === 'function'
                ? window.getAuthToken()
                : (localStorage.getItem('authToken') || localStorage.getItem('token') || ''));
            const editOpenUrl = "{{ route('leave-type.edit.open') }}";
            const csrfToken = "{{ csrf_token() }}";
            const state = {
                page: 1,
                perPage: 10,
                lastPage: 1,
                total: 0,
                search: '',
            };
            let searchDebounceTimer = null;
            const visiblePageCount = 5;

            function getSelectedBranchId() {
                return localStorage.getItem('selectedSubAdminId')
                    || localStorage.getItem('selectedBranchId')
                    || '{{ session('selectedSubAdminId') }}'
                    || '';
            }

            if (!token) {
                Swal.fire('Unauthorized', 'Please login again to continue.', 'warning');
                return;
            }

            $('#leaveTypePerPage').on('change', function() {
                state.perPage = Number($(this).val()) || 10;
                state.page = 1;
                loadLeaveTypes();
            });

            $(document).on('click', '.leaveType-page-link', function(e) {
                e.preventDefault();
                const page = Number($(this).data('page'));
                if (!page || page === state.page || page < 1 || page > state.lastPage) {
                    return;
                }
                state.page = page;
                loadLeaveTypes();
            });

            $('#leaveTypeSearch').on('input', function() {
                const value = $(this).val().trim();
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    if (state.search === value) {
                        return;
                    }
                    state.search = value;
                    state.page = 1;
                    loadLeaveTypes();
                }, 350);
            });

            function loadLeaveTypes() {
                $.ajax({
                    url: '/api/leavetype',
                    method: 'GET',
                    data: {
                        page: state.page,
                        per_page: state.perPage,
                        search: state.search,
                        selectedSubAdminId: getSelectedBranchId(),
                    },
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                }).done((response) => {
                    const records = response.data || [];
                    const pagination = response.pagination || {};

                    state.page = Number(pagination.current_page || state.page || 1);
                    state.lastPage = Number(pagination.last_page || 1);
                    state.perPage = Number(pagination.per_page || state.perPage || 10);
                    state.total = Number(pagination.total ?? records.length);

                    if (records.length === 0 && state.page > 1 && state.page > state.lastPage) {
                        state.page = state.lastPage;
                        loadLeaveTypes();
                        return;
                    }

                    const startIndex = state.total === 0 ? 0 : ((state.page - 1) * state.perPage) + 1;
                    
                    // Desktop table view
                    const rows = records.map((item, index) => `
                        <tr>
                            <td>${startIndex + index}</td>
                            <td>${item.leave_type ?? '-'}</td>
                            <td>${item.number_of_leaves ?? 0}</td>
                            <td>${Number(item.allow_half_day) === 1 ? 'Yes' : 'No'}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-warning me-1 openLeaveTypeEdit" data-id="${item.id}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger deleteLeaveType" data-id="${item.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');

                    $('#leaveTypeTable tbody').html(rows || '<tr><td colspan="5" class="text-center">No records found</td></tr>');

                    // Mobile card view
                    const cards = records.map((item, index) => `
                        <div class="leave-type-card" data-id="${item.id}">
                            <div class="card-header-mobile toggle-card-header">
                                <div class="card-title-section">
                                    <p class="title">${item.leave_type ?? '-'}</p>
                                    <p>Leaves: <strong>${item.number_of_leaves ?? 0}</strong></p>
                                </div>
                                <button type="button" class="expand-btn">
                                    <span class="toggle-icon">+</span>
                                </button>
                            </div>
                            <div class="card-details-mobile">
                                <div class="card-details-content">
                                    <div class="detail-row">
                                        <span class="detail-label">Number of Leaves:</span>
                                        <span class="detail-value">${item.number_of_leaves ?? 0}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Allow Half Day:</span>
                                        <span class="detail-value">${Number(item.allow_half_day) === 1 ? 'Yes' : 'No'}</span>
                                    </div>
                                    <div class="card-actions">
                                        <button type="button" class="btn-edit openLeaveTypeEdit" data-id="${item.id}">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </button>
                                        <button type="button" class="btn-delete deleteLeaveType" data-id="${item.id}">
                                            <i class="fa fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    $('#leaveTypeCardView').html(cards || '<div style="text-align:center; padding:20px; color:#999;">No records found</div>');

                    updatePaginationUI();
                }).fail(handleError);
            }

            window.toggleCard = function(card) {
                const details = card.querySelector('.card-details-mobile');
                const btn = card.querySelector('.expand-btn');
                const icon = btn.querySelector('.toggle-icon');
                
                card.classList.toggle('expanded');
                details.classList.toggle('show');
                btn.classList.toggle('expanded');
                
                // Toggle between + and − (minus)
                icon.textContent = icon.textContent === '+' ? '−' : '+';
            };

            // Event delegation for card header clicks
            $(document).on('click', '.toggle-card-header', function(e) {
                e.stopPropagation();
                const card = $(this).closest('.leave-type-card')[0];
                window.toggleCard(card);
            });

            $(document).on('click', '.deleteLeaveType', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Leave Type?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/leavetype/${id}`,
                        method: 'DELETE',
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }).done((response) => {
                        Swal.fire('Deleted', response.message, 'success');
                        loadLeaveTypes();
                    }).fail(handleError);
                });
            });

            $(document).on('click', '.openLeaveTypeEdit', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                const form = $('<form>', {
                    method: 'POST',
                    action: editOpenUrl,
                    style: 'display:none;'
                });

                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: csrfToken
                }));
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'id',
                    value: id
                }));

                $('body').append(form);
                form.trigger('submit');
            });

            function updatePaginationUI() {
                let from = state.total === 0 ? 0 : ((state.page - 1) * state.perPage) + 1;
                let to = state.page * state.perPage;
                if (to > state.total) {
                    to = state.total;
                }

                $('#leaveTypePaginationFrom').text(from);
                $('#leaveTypePaginationTo').text(to);
                $('#leaveTypePaginationTotal').text(state.total);

                let paginationHtml = '';

                paginationHtml += `
                    <li class="page-item ${state.page <= 1 ? 'disabled' : ''}">
                        <a class="page-link leaveType-page-link" href="javascript:void(0);" data-page="${state.page - 1}">Previous</a>
                    </li>
                `;

                let startPage = Math.max(1, state.page - 2);
                let endPage = Math.min(state.lastPage, startPage + visiblePageCount - 1);

                if (endPage - startPage + 1 < visiblePageCount) {
                    startPage = Math.max(1, endPage - visiblePageCount + 1);
                }

                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === state.page ? 'active' : ''}">
                            <a class="page-link leaveType-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                paginationHtml += `
                    <li class="page-item ${state.page >= state.lastPage ? 'disabled' : ''}">
                        <a class="page-link leaveType-page-link" href="javascript:void(0);" data-page="${state.page + 1}">Next</a>
                    </li>
                `;

                $('#leaveTypePaginationNumbers').html(paginationHtml);
                $('#leaveTypePaginationWrapper').toggle(state.total > 0);
            }

            function handleError(xhr) {
                const message = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Something went wrong. Please try again.';
                Swal.fire('Error', message, 'error');
            }

            loadLeaveTypes();
        });
    </script>
@endpush
