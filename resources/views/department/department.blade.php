@extends('layout.app')

@section('title', 'Department')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4 id="pageTitle">Add Department</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="departmentForm">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="department_name">Department Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="department_name" name="department_name"
                                    placeholder="Enter Department Name">
                            </div>
                        </div>

                        <!-- OVERTIME CONFIGURATION -->
                        <div class="col-lg-8 mt-4">
                            <div class="section-header" style="background:#f8f9fa; padding:10px; border-radius:6px; font-weight:600; margin-bottom:12px; border-left:4px solid #ff9f43;">
                                <i class="fa-solid fa-clock"></i> Department Overtime Configuration
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="form-group row mb-3">
                                <label class="col-sm-4 col-form-label">Enable Overtime</label>
                                <div class="col-sm-8">
                                    <div class="form-check form-switch ps-0 pt-2">
                                        <input class="form-check-input" type="checkbox" id="enable_overtime" name="enable_overtime" style="margin-left:0;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="form-group row mb-3">
                                <label class="col-sm-4 col-form-label">Overtime Rate Type</label>
                                <div class="col-sm-8">
                                    <select class="form-select w-100" name="overtime_rate_type" id="overtime_rate_type">
                                        <option value="multiplier">Multiplier (e.g. 1.5x)</option>
                                        <option value="fixed">Fixed Rate per Hour</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="form-group row mb-3">
                                <label class="col-sm-4 col-form-label">Overtime Rate/Multiplier</label>
                                <div class="col-sm-8">
                                    <input type="number" class="form-control" name="overtime_multiplier" id="overtime_multiplier" step="any" placeholder="e.g. 1.5 or 200" value="1.00">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="form-group row mb-3">
                                <label class="col-sm-4 col-form-label">Min OT Count (Min)</label>
                                <div class="col-sm-8">
                                    <input type="number" class="form-control" name="min_overtime_count_in_minutes" id="min_overtime_count_in_minutes" step="any" placeholder="e.g. 30" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('department.view') }}" class="btn btn-cancel">Cancel</a>
                        <button type="submit" id="submitBtn" class="btn btn-submit">Submit</button>
                    </div>
                </form>
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
            const queryParams = new URLSearchParams(window.location.search);
            const routeDepartmentId = @json($departmentId ?? null);
            const departmentId = routeDepartmentId || queryParams.get('id');
            let isEditMode = Boolean(departmentId);

            if (!token) {
                Swal.fire('Unauthorized', 'Please login again to continue.', 'warning');
                return;
            }

            if (isEditMode) {
                $('#pageTitle').text('Edit Department');
                $('#submitBtn').text('Update');
                loadDepartment(departmentId);
            }

            $('#departmentForm').on('submit', function(e) {
                e.preventDefault();

                const departmentName = $('#department_name').val().trim();
                if (!departmentName) {
                    Swal.fire('Validation Error', 'Department name is required.', 'error');
                    return;
                }

                const payload = {
                    department_name: departmentName,
                    enable_overtime: $('#enable_overtime').is(':checked'),
                    overtime_rate_type: $('#overtime_rate_type').val() || 'multiplier',
                    overtime_multiplier: $('#overtime_multiplier').val() || 1,
                    min_overtime_count_in_minutes: $('#min_overtime_count_in_minutes').val() || 0,
                    selectedSubAdminId: localStorage.getItem('selectedSubAdminId') || null,
                };

                const url = isEditMode ? `/api/department/${departmentId}` : '/api/department';
                requestApi(url, 'POST', payload).done((response) => {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = "{{ route('department.view') }}";
                    });
                }).fail(handleError);
            });

            function loadDepartment(id) {
                requestApi(`/api/department/${id}`, 'GET').done((response) => {
                    const data = response.data;
                    $('#department_name').val(data.department_name);
                    $('#enable_overtime').prop('checked', !!data.enable_overtime);
                    if (data.overtime_rate_type) $('#overtime_rate_type').val(data.overtime_rate_type);
                    if (data.overtime_multiplier) $('#overtime_multiplier').val(data.overtime_multiplier);
                    if (data.min_overtime_count_in_minutes !== null) $('#min_overtime_count_in_minutes').val(data.min_overtime_count_in_minutes);
                }).fail(handleError);
            }

            function requestApi(url, method, data = null) {
                return $.ajax({
                    url,
                    method,
                    headers: {
                        Authorization: `Bearer ${token}`
                    },
                    contentType: 'application/json',
                    data: data ? JSON.stringify(data) : null
                });
            }

            function handleError(xhr) {
                const message = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
                Swal.fire('Error', message, 'error');
            }
        });
    </script>
@endpush
