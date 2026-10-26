@extends('layout.app')

@section('title', 'Holidays')

@section('content')
<style>
    .btn-secondary{
        background-color: #1B2850;
        color: #fff;
    }

    .table-search-wrap {
        max-width: 280px;
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

        .holiday-card {
            background: #fff;
            border: 1px solid #e0e6f1;
            border-radius: 8px;
            margin-bottom: 12px;
            position: relative;
            transition: all 0.3s ease;
        }

        .holiday-card.expanded {
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
            max-height: 250px;
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
            word-break: break-word;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
            min-width: 100px;
        }

        .detail-value {
            color: #333;
            font-weight: 600;
            text-align: right;
            flex: 1;
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

        .holiday-pagination-controls {
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
                <h4>Manage Holidays</h4>
            </div>
            <div class="page-btn d-flex gap-2">
                <a href="{{ route('holidays.calendar') }}" class="btn btn-secondary">Calendar View</a>
                <a href="{{ route('holidays.create') }}" class="btn btn-added">
                    <i class="fa fa-plus me-1"></i> Add Holiday
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-search-wrap mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="holidaySearch" placeholder="Search...">
                    </div>
                </div>
                <!-- Desktop table view -->
                <div class="table-responsive">
                    <table class="table" id="holidayTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Mobile card view -->
                <div class="mobile-card-view" id="holidayCardView">
                    <!-- Cards will be populated by JS -->
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 holiday-pagination-controls" id="holidayPaginationWrapper" style="display:none;">
                    <div class="d-flex align-items-center gap-2">
                        <label for="holidayPerPage" class="mb-0">Rows:</label>
                        <select id="holidayPerPage" class="form-select form-select-sm" style="width: auto;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary table-pagination-btn" id="holidayPrevPage">Previous</button>
                        <span id="holidayPageInfo">Page 1 of 1</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary table-pagination-btn" id="holidayNextPage">Next</button>
                    </div>
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
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const state = {
                page: 1,
                perPage: 10,
                lastPage: 1,
                total: 0,
                search: '',
            };
            let searchDebounceTimer = null;

            if (!token) {
                Swal.fire('Unauthorized', 'Please login again to continue.', 'warning');
                return;
            }

            $('#holidayPerPage').on('change', function() {
                state.perPage = Number($(this).val()) || 10;
                state.page = 1;
                loadHolidays();
            });

            $('#holidayPrevPage').on('click', function() {
                if (state.page <= 1) {
                    return;
                }
                state.page -= 1;
                loadHolidays();
            });

            $('#holidayNextPage').on('click', function() {
                if (state.page >= state.lastPage) {
                    return;
                }
                state.page += 1;
                loadHolidays();
            });

            $('#holidaySearch').on('input', function() {
                const value = $(this).val().trim();
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    if (state.search === value) {
                        return;
                    }
                    state.search = value;
                    state.page = 1;
                    loadHolidays();
                }, 350);
            });

            function loadHolidays() {
                $.ajax({
                    url: '/api/get_holidays',
                    method: 'GET',
                    data: {
                        page: state.page,
                        per_page: state.perPage,
                        search: state.search,
                        ...(selectedSubAdminId ? { selectedSubAdminId } : {}),
                    },
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                }).done((response) => {
                    const holidays = response.data || [];
                    const pagination = response.pagination || {};

                    state.page = Number(pagination.current_page || state.page || 1);
                    state.lastPage = Number(pagination.last_page || 1);
                    state.perPage = Number(pagination.per_page || state.perPage || 10);
                    state.total = Number(pagination.total ?? holidays.length);

                    if (holidays.length === 0 && state.page > 1 && state.page > state.lastPage) {
                        state.page = state.lastPage;
                        loadHolidays();
                        return;
                    }

                    const startIndex = state.total === 0 ? 0 : ((state.page - 1) * state.perPage) + 1;
                    
                    // Desktop table view
                    const rows = holidays.map((holiday, index) => `
                        <tr>
                            <td>${startIndex + index}</td>
                            <td>${holiday.title ?? '-'}</td>
                            <td>${holiday.holiday_date ?? '-'}</td>
                            <td>${holiday.description ?? '-'}</td>
                            <td class="text-end">
                                <a href="/holidays/edit/${holiday.id}" class="btn btn-sm btn-warning me-1">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger deleteHoliday" data-id="${holiday.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');

                    $('#holidayTable tbody').html(rows || '<tr><td colspan="5" class="text-center">No records found</td></tr>');

                    // Mobile card view
                    const cards = holidays.map((holiday, index) => `
                        <div class="holiday-card" data-id="${holiday.id}">
                            <div class="card-header-mobile toggle-card-header">
                                <div class="card-title-section">
                                    <p class="title">${holiday.title ?? '-'}</p>
                                    <p>Date: <strong>${holiday.holiday_date ?? '-'}</strong></p>
                                </div>
                                <button type="button" class="expand-btn">
                                    <span class="toggle-icon">+</span>
                                </button>
                            </div>
                            <div class="card-details-mobile">
                                <div class="card-details-content">
                                    <div class="detail-row">
                                        <span class="detail-label">Date:</span>
                                        <span class="detail-value">${holiday.holiday_date ?? '-'}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Description:</span>
                                        <span class="detail-value">${holiday.description ?? '-'}</span>
                                    </div>
                                    <div class="card-actions">
                                        <button type="button" class="btn-edit" onclick="window.location.href='/holidays/edit/${holiday.id}'">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </button>
                                        <button type="button" class="btn-delete deleteHoliday" data-id="${holiday.id}">
                                            <i class="fa fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    $('#holidayCardView').html(cards || '<div style="text-align:center; padding:20px; color:#999;">No records found</div>');

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
                const card = $(this).closest('.holiday-card')[0];
                window.toggleCard(card);
            });

            $(document).on('click', '.deleteHoliday', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Holiday?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/holidays/delete/${id}`,
                        method: 'DELETE',
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }).done((response) => {
                        Swal.fire('Deleted', response.message, 'success');
                        loadHolidays();
                    }).fail(handleError);
                });
            });

            function updatePaginationUI() {
                $('#holidayPageInfo').text(`Page ${state.page} of ${state.lastPage} (${state.total} total)`);
                $('#holidayPrevPage').prop('disabled', state.page <= 1);
                $('#holidayNextPage').prop('disabled', state.page >= state.lastPage);
                $('#holidayPaginationWrapper').toggle(state.total > 0);
            }

            function handleError(xhr) {
                const message = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Something went wrong. Please try again.';
                Swal.fire('Error', message, 'error');
            }

            loadHolidays();
        });
    </script>
@endpush
