@extends('layout.app')

@section('title', 'Edit Holiday')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Holiday</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="holidayForm">
                    <input type="hidden" id="holiday_id" value="{{ $holidayId }}">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Holiday title">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="holiday_date">Holiday Date</label>
                                <input type="date" class="form-control" id="holiday_date" name="holiday_date">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('holidays.index') }}" class="btn btn-cancel">Cancel</a>
                        <button type="submit" class="btn btn-submit">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const token = localStorage.getItem('token');
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const holidayId = $('#holiday_id').val();

            if (!token) {
                Swal.fire('Unauthorized', 'Please login again to continue.', 'warning');
                return;
            }

            loadHoliday();

            $('#holidayForm').on('submit', function(e) {
                e.preventDefault();

                const payload = {
                    id: holidayId,
                    title: $('#title').val().trim(),
                    holiday_date: $('#holiday_date').val(),
                    description: $('#description').val().trim()
                };

                if (!payload.title || !payload.holiday_date) {
                    Swal.fire('Validation Error', 'Title and holiday date are required.', 'error');
                    return;
                }

                $.ajax({
                    url: '/api/holidays/update',
                    method: 'POST',
                    headers: {
                        Authorization: `Bearer ${token}`
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        ...payload,
                        ...(selectedSubAdminId ? { selectedSubAdminId } : {})
                    })
                }).done((response) => {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = "{{ route('holidays.index') }}";
                    });
                }).fail((xhr) => {
                    const message = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
                    Swal.fire('Error', message, 'error');
                });
            });

            function loadHoliday() {
                $.ajax({
                    url: `/api/holidays/edit_holiday/${holidayId}`,
                    method: 'GET',
                    data: selectedSubAdminId ? { selectedSubAdminId } : {},
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                }).done((response) => {
                    const holiday = response.holiday;
                    $('#title').val(holiday.title);
                    $('#holiday_date').val(holiday.holiday_date);
                    $('#description').val(holiday.description);
                }).fail((xhr) => {
                    const message = xhr.responseJSON?.message || 'Failed to fetch holiday details.';
                    Swal.fire('Error', message, 'error');
                });
            }
        });
    </script>
@endpush
