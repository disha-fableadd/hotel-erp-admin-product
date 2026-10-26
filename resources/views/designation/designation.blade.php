@extends('layout.app')

@section('title', 'Designation')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4 id="pageTitle">Add Designation</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="designationForm">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select id="department_id" name="department_id" class="form-select">
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="designation_name">Designation Name</label>
                                <input type="text" class="form-control" id="designation_name" name="designation_name"
                                    placeholder="Enter Designation Name">
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('designation.view') }}" class="btn btn-cancel">Cancel</a>
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
            const routeDesignationId = @json($designationId ?? null);
            const params = new URLSearchParams(window.location.search);
            const designationId = routeDesignationId || params.get('id');
            const isEditMode = Boolean(designationId);

            if (!token) {
                Swal.fire('Unauthorized', 'Please login again to continue.', 'warning');
                return;
            }

            if (isEditMode) {
                $('#pageTitle').text('Edit Designation');
                $('#submitBtn').text('Update');
                loadDesignation(designationId);
            }

            $('#designationForm').on('submit', function(e) {
                e.preventDefault();

                const payload = {
                    designation_name: $('#designation_name').val().trim(),
                    department_id: $('#department_id').val(),
                    selectedSubAdminId: localStorage.getItem('selectedSubAdminId') || null,
                };

                if (!payload.department_id) {
                    Swal.fire('Validation Error', 'Department is required.', 'error');
                    return;
                }
                if (!payload.designation_name) {
                    Swal.fire('Validation Error', 'Designation name is required.', 'error');
                    return;
                }

                const url = isEditMode ? `/api/designation/${designationId}` : '/api/designation';
                requestApi(url, 'POST', payload).done((response) => {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = "{{ route('designation.view') }}";
                    });
                }).fail(handleError);
            });

            function loadDesignation(id) {
                requestApi(`/api/designation/${id}`, 'GET').done((response) => {
                    $('#designation_name').val(response.data.designation_name);
                    $('#department_id').val(response.data.department_id);
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
