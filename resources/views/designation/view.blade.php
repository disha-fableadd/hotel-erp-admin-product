@extends('layout.app')

@section('title', 'Designations')

@section('content')
    <style>
        .table-search-wrap {
            max-width: 280px;
        }

        .designation-pagination-controls {
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
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Manage Designations</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('designation.create') }}" class="btn btn-added">
                    <i class="fa fa-plus me-1"></i> Add Designation
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-search-wrap mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="designationSearch" placeholder="Search...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="designationTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="pagination-controls designation-pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3" id="designationPaginationWrapper" style="display:none;">
                    <div class="d-flex align-items-center mb-3 mb-md-0 flex-wrap gap-2">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="designationPerPage" class="form-select form-select-sm" style="width: auto; border: 1px solid #ddd;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="designationPaginationFrom">0</span> - <span id="designationPaginationTo">0</span> of <span id="designationPaginationTotal">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Designation pagination">
                        <ul class="pagination pagination-sm mb-0" id="designationPaginationNumbers">
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
            const editOpenUrl = "{{ route('designation.edit.open') }}";
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

            if (!token) {
                Swal.fire('Unauthorized', 'Please login again to continue.', 'warning');
                return;
            }

            $('#designationPerPage').on('change', function() {
                state.perPage = Number($(this).val()) || 10;
                state.page = 1;
                loadDesignations();
            });

            $(document).on('click', '.designation-page-link', function(e) {
                e.preventDefault();
                const page = Number($(this).data('page'));
                if (!page || page === state.page || page < 1 || page > state.lastPage) {
                    return;
                }
                state.page = page;
                loadDesignations();
            });

            $('#designationSearch').on('input', function() {
                const value = $(this).val().trim();
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    if (state.search === value) {
                        return;
                    }
                    state.search = value;
                    state.page = 1;
                    loadDesignations();
                }, 350);
            });

            function loadDesignations() {
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                $.ajax({
                    url: '/api/designation',
                    method: 'GET',
                    data: {
                        page: state.page,
                        per_page: state.perPage,
                        search: state.search,
                        selectedSubAdminId: selectedSubAdminId || '',
                    },
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                }).done((response) => {
                    const designations = response.designations || [];
                    const pagination = response.pagination || {};

                    state.page = Number(pagination.current_page || state.page || 1);
                    state.lastPage = Number(pagination.last_page || 1);
                    state.perPage = Number(pagination.per_page || state.perPage || 10);
                    state.total = Number(pagination.total ?? designations.length);

                    if (designations.length === 0 && state.page > 1 && state.page > state.lastPage) {
                        state.page = state.lastPage;
                        loadDesignations();
                        return;
                    }

                    const startIndex = state.total === 0 ? 0 : ((state.page - 1) * state.perPage) + 1;
                    const rows = designations.map((designation, index) => `
                        <tr>
                            <td>${startIndex + index}</td>
                            <td>${designation.designation_name ?? '-'}</td>
                            <td>${designation.department_name ?? '-'}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-warning me-1 openDesignationEdit" data-id="${designation.id}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger deleteDesignation" data-id="${designation.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');

                    $('#designationTable tbody').html(rows || '<tr><td colspan="4" class="text-center">No records found</td></tr>');
                    updatePaginationUI();
                }).fail(handleError);
            }

            $(document).on('click', '.deleteDesignation', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Designation?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/designation/${id}`,
                        method: 'DELETE',
                        data: {
                            selectedSubAdminId: localStorage.getItem('selectedSubAdminId') || '',
                        },
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }).done((response) => {
                        Swal.fire('Deleted', response.message, 'success');
                        loadDesignations();
                    }).fail(handleError);
                });
            });

            $(document).on('click', '.openDesignationEdit', function() {
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

                $('#designationPaginationFrom').text(from);
                $('#designationPaginationTo').text(to);
                $('#designationPaginationTotal').text(state.total);

                let paginationHtml = '';

                paginationHtml += `
                    <li class="page-item ${state.page <= 1 ? 'disabled' : ''}">
                        <a class="page-link designation-page-link" href="javascript:void(0);" data-page="${state.page - 1}">Previous</a>
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
                            <a class="page-link designation-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                paginationHtml += `
                    <li class="page-item ${state.page >= state.lastPage ? 'disabled' : ''}">
                        <a class="page-link designation-page-link" href="javascript:void(0);" data-page="${state.page + 1}">Next</a>
                    </li>
                `;

                $('#designationPaginationNumbers').html(paginationHtml);
                $('#designationPaginationWrapper').toggle(state.total > 0);
            }

            function handleError(xhr) {
                const message = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Something went wrong. Please try again.';
                Swal.fire('Error', message, 'error');
            }

            loadDesignations();
        });
    </script>
@endpush
        