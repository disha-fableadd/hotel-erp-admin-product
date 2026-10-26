@extends('layout.app')

@section('title', 'Departments')

@section('content')
    <style>
        .table-search-wrap {
            max-width: 280px;
        }

        .pagination-controls {
            gap: 12px;
        }

        .pagination-controls .page-link {
            color: #6c757d;
            border: 1px solid #ced4da;
            border-radius: 6px;
            margin: 0 2px;
        }

        .pagination-controls .page-item.active .page-link {
            background-color: #FF9F43;
            border-color: #FF9F43;
            color: #fff;
        }

        .pagination-controls .page-item.disabled .page-link {
            color: #adb5bd;
            pointer-events: none;
            background-color: #fff;
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Manage Departments</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('department.create') }}" class="btn btn-added">
                    <i class="fa fa-plus me-1"></i> Add Department
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-search-wrap mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="departmentSearch" placeholder="Search...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="departmentTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Department</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="departmentPerPage" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="departmentPaginationFrom">0</span> - <span id="departmentPaginationTo">0</span> of
                            <span id="departmentPaginationTotal">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="departmentPaginationNumbers">
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

            $('#departmentPerPage').on('change', function() {
                state.perPage = Number($(this).val()) || 10;
                state.page = 1;
                loadDepartments();
            });

            $('#departmentSearch').on('input', function() {
                const value = $(this).val().trim();
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    if (state.search === value) {
                        return;
                    }
                    state.search = value;
                    state.page = 1;
                    loadDepartments();
                }, 350);
            });

            function loadDepartments() {
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                $.ajax({
                    url: '/api/department',
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
                    const departments = response.departments || [];
                    const pagination = response.pagination || {};

                    state.page = Number(pagination.current_page || state.page || 1);
                    state.lastPage = Number(pagination.last_page || 1);
                    state.perPage = Number(pagination.per_page || state.perPage || 10);
                    state.total = Number(pagination.total ?? departments.length);

                    if (departments.length === 0 && state.page > 1 && state.page > state.lastPage) {
                        state.page = state.lastPage;
                        loadDepartments();
                        return;
                    }

                    const startIndex = state.total === 0 ? 0 : ((state.page - 1) * state.perPage) + 1;
                    const rows = departments.map((department, index) => `
                        <tr>
                            <td>${startIndex + index}</td>
                            <td>${department.department_name ?? '-'}</td>
                            <td class="text-end">
                                <a href="{{ url('/department') }}/${department.id}" class="btn btn-sm btn-warning me-1">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger deleteDepartment" data-id="${department.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');

                    $('#departmentTable tbody').html(rows || '<tr><td colspan="3" class="text-center">No records found</td></tr>');
                    updatePaginationUI(pagination);
                }).fail(handleError);
            }

            $(document).on('click', '.deleteDepartment', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Department?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/department/${id}`,
                        method: 'DELETE',
                        data: {
                            selectedSubAdminId: localStorage.getItem('selectedSubAdminId') || '',
                        },
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }).done((response) => {
                        Swal.fire('Deleted', response.message, 'success');
                        loadDepartments();
                    }).fail(handleError);
                });
            });

            function updatePaginationUI(pagination) {
                let from = (pagination.current_page - 1) * pagination.per_page + 1;
                let to = pagination.current_page * pagination.per_page;
                if (to > pagination.total) to = pagination.total;
                if (pagination.total === 0) from = 0;

                $('#departmentPaginationFrom').text(from);
                $('#departmentPaginationTo').text(to);
                $('#departmentPaginationTotal').text(pagination.total);

                let paginationHtml = '';
                const totalPages = pagination.last_page || 1;
                const currentPage = pagination.current_page || 1;
                const visiblePageCount = 2;
                let startPage = Math.floor((currentPage - 1) / visiblePageCount) * visiblePageCount + 1;
                let endPage = Math.min(totalPages, startPage + visiblePageCount - 1);

                paginationHtml += `
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link department-page-link" href="javascript:void(0);" data-page="${currentPage - 1}">Previous</a>
                    </li>
                `;

                if (startPage > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link department-page-link" href="javascript:void(0);" data-page="${startPage - 1}" data-action="prev-group">..</a>
                        </li>
                    `;
                }

                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link department-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                if (endPage < totalPages) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link department-page-link" href="javascript:void(0);" data-page="${endPage + 1}" data-action="next-group">..</a>
                        </li>
                    `;
                }

                paginationHtml += `
                    <li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
                        <a class="page-link department-page-link" href="javascript:void(0);" data-page="${currentPage + 1}">Next</a>
                    </li>
                `;

                $('#departmentPaginationNumbers').html(paginationHtml);
                $('.pagination-controls').toggle(pagination.total > 0);
            }

            $(document).on('click', '.department-page-link', function(e) {
                e.preventDefault();

                const page = Number($(this).data('page'));
                const action = $(this).data('action');

                if (action === 'next-group') {
                    if (page && page <= state.lastPage) {
                        state.page = page;
                        loadDepartments();
                    }
                    return;
                }

                if (action === 'prev-group') {
                    const prevStartPage = Math.max(1, page - 2);
                    if (prevStartPage >= 1 && prevStartPage <= state.lastPage) {
                        state.page = prevStartPage;
                        loadDepartments();
                    }
                    return;
                }

                if (!page || page < 1 || page > state.lastPage || page === state.page) {
                    return;
                }

                state.page = page;
                loadDepartments();
            });

            function handleError(xhr) {
                const message = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Something went wrong. Please try again.';
                Swal.fire('Error', message, 'error');
            }

            loadDepartments();
        });
    </script>
@endpush
