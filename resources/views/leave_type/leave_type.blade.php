@extends('layout.app')

@section('title', 'Leave Type')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4 id="pageTitle">Add Leave Type</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="leaveTypeForm">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="leave_type">Leave Type</label>
                                <input type="text" class="form-control" id="leave_type" name="leave_type"
                                    placeholder="Enter Leave Type">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="number_of_leaves">Number of Leaves</label>
                                <input type="number" min="0" class="form-control" id="number_of_leaves"
                                    name="number_of_leaves" placeholder="Enter total leaves">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="allow_half_day">Allow Half Day?</label>
                                <select class="form-select" id="allow_half_day" name="allow_half_day">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('leave-type.view') }}" class="btn btn-cancel">Cancel</a>
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
            const token = (typeof window.getAuthToken === 'function') ? window.getAuthToken() : (localStorage.getItem('authToken') || localStorage.getItem('token') || '');
            const routeLeaveTypeId = @json($leaveTypeId ?? null);
            const params = new URLSearchParams(window.location.search);
            const leaveTypeId = routeLeaveTypeId || params.get('id');
            const isEditMode = Boolean(leaveTypeId);

            if (!token) {
                Swal.fire('Unauthorized', 'Please login again to continue.', 'warning');
                return;
            }

            if (isEditMode) {
                $('#pageTitle').text('Edit Leave Type');
                $('#submitBtn').text('Update');
                loadLeaveType(leaveTypeId);
            }

            $('#leaveTypeForm').on('submit', function(e) {
                e.preventDefault();

                const payload = {
                    leave_type: $('#leave_type').val().trim(),
                    number_of_leaves: $('#number_of_leaves').val(),
                    allow_half_day: $('#allow_half_day').val()
                };
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                if (selectedSubAdminId && selectedSubAdminId !== 'null' && selectedSubAdminId !== 'undefined') {
                    payload.selectedSubAdminId = selectedSubAdminId;
                }

                if (!payload.leave_type || payload.number_of_leaves === '') {
                    Swal.fire('Validation Error', 'Leave type and number of leaves are required.', 'error');
                    return;
                }

                const url = isEditMode ? `/api/leavetype/${leaveTypeId}` : '/api/leavetype';
                requestApi(url, 'POST', payload).done((response) => {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = "{{ route('leave-type.view') }}";
                    });
                }).fail(handleError);
            });

            function loadLeaveType(id) {
                requestApi(`/api/leavetype/${id}`, 'GET').done((response) => {
                    const leaveType = response.data;
                    $('#leave_type').val(leaveType.leave_type);
                    $('#number_of_leaves').val(leaveType.number_of_leaves);
                    $('#allow_half_day').val(String(leaveType.allow_half_day ? 1 : 0));
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
