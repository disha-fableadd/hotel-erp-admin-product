@extends('layout.app')
@section('title', 'Attendance View')
@section('content')
    <style>
        @media (max-width: 767px) {
            .card-header {
                flex-direction: column;
                align-items: stretch !important;
                gap: 12px !important;
                padding: 15px !important;
            }
            .card-header > h5 {
                text-align: center;
            }
            .card-header > div.d-flex.align-items-center.flex-wrap {
                justify-content: center;
                margin-left: 0 !important;
                width: 100%;
            }
            .card-header > div.d-flex.align-items-center.gap-2 {
                justify-content: center;
                width: 100%;
            }
            .card-header > div.d-flex.align-items-center.gap-2 > div {
                flex: 1;
            }
            .card-header > div.d-flex.align-items-center.gap-2 > div > input {
                width: 100% !important;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Attendance</h4>
            </div>
            <div class="page-btn d-flex gap-2">
                @if(auth()->user()->role !== 'staff')
                <a href="{{ route('attendance.manage') }}" class="btn btn-primary" style="background:#e74c3c;border-color:#e74c3c;">
                    <i class="fa fa-chart-bar me-1"></i> Manage Attendance
                </a>
                @endif
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0" id="month-year-header">{{ date('F Y', strtotime($currentMonth)) }} Attendance</h5>
                <div class="d-flex align-items-center flex-wrap gap-3 ms-2 p-2 rounded shadow-sm border bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success px-3">P</span>
                        <span class="fw-semibold text-dark">Present</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-danger px-3">A</span>
                        <span class="fw-semibold text-dark">Absent</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success px-3">HP</span>
                        <span class="fw-semibold text-dark">Half Day Present</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success px-3">2P</span>
                        <span class="fw-semibold text-dark">Extra Day Present</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill bg-white border text-dark px-3" 
                        style="border-color: #6c757d !important; min-width: 40px; height: 18px; display: inline-flex; align-items: center; justify-content: center;">
                        </span>
                        <span class="fw-semibold text-dark">Weekday</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill text-white px-3" 
                        style="background-color: #f36774; min-width: 40px; height: 18px; display: inline-flex; align-items: center; justify-content: center;">
                        </span>
                        <span class="fw-semibold text-dark">Sunday</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2"><div style="width: 150px;"><input type="month" class="form-control form-control-sm" id="select-date" name="month" value="{{ $currentMonth }}"></div><div style="width: 146px;"><input type="text" id="searchStaff" class="form-control form-control-sm" placeholder="Search Staff..."></div></div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 600px;">
                <div id="attendance-table-wrapper">
                    {{-- <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="sticky-top"
                style="z-index:1 !important; top: 0; background-color: #1b2850; color: #fff;">
                <tr>
                    <th style="position: sticky; left: 0; z-index: 10; background-color: #1b2850; color: #fff;">
                        Staff Name
                    </th>

                    @for ($i = 1; $i <= $daysInMonth; $i++)
                        <th class="text-white">{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody id="attendance-table-body">
                @foreach ($staffUsers as $staff)
                    <tr>
                        <td style="position: sticky; left: 0; background-color: #fff;" class="text-start ps-2">
                            {{ ucwords($staff->name) }}
                        </td>
                        @for ($i = 1; $i <= $daysInMonth; $i++)
                            @php
                                $date = $year . '-' . $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                $key = $staff->id . '_' . $date;
                                $attendance = $attendances[$key][0] ?? null;
                                $status = $attendance->status ?? 'A';
                            @endphp
                            @php
                                $displayStatus = $status;
                                if ($status == 'P' && ($attendance->extraday ?? 0) == 1) {
                                    $displayStatus = '2P';
                                }
                                if ($status == 'H') {
                                    $displayStatus = 'H';
                                }
                            @endphp
                            <td class="attendance-cell text-center" data-user-id="{{ $staff->id }}"
                                data-date="{{ $date }}" data-status="{{ $status }}"
                                data-checkin="{{ $attendance->check_in_time ?? '' }}"
                                data-checkout="{{ $attendance->check_out_time ?? '' }}"
                                data-reason="{{ $attendance->reason ?? '' }}"
                                data-extraday="{{ $attendance->extraday ?? '0' }}" style="cursor:pointer;">
                                @if ($displayStatus == 'P' || $displayStatus == '2P')
                                    <strong class="text-success">{{ $displayStatus }}</strong>
                                @elseif ($displayStatus == 'H')
                                    <strong class="text-success">HP</strong>
                                @else
                                    <strong class="text-danger">A</strong>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div> --}}
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="sticky-top"
                                style="z-index:1 !important; top: 0; background-color: #1b2850; color: #fff;">
                                <thead class="sticky-top"
                                    style="z-index:1 !important; top: 0; background-color: #1b2850; color: #fff;">
                                    <tr>
                                        <th
                                            style="position: sticky; left: 0; z-index: 10; background-color: #1b2850; color: #fff;">
                                            Staff Name
                                        </th>

                                        @for ($i = 1; $i <= $daysInMonth; $i++)
                                            <th class="text-white">{{ $i }}</th>
                                        @endfor
                                    </tr>
                                </thead>
                            </thead>

                            <tbody id="attendance-table-body">
                                @foreach ($staffUsers as $staff)
                                    <tr>
                                        <td style="position: sticky; left: 0; background-color: #fff;"
                                            class="text-start ps-2">
                                            {{ ucwords($staff->name) }}
                                        </td>

                                        @for ($i = 1; $i <= $daysInMonth; $i++)
                                            @php
                                                $date = $year . '-' . $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                                $dayName = \Carbon\Carbon::parse($date)->format('D');
                                                $key = $staff->id . '_' . $date;
                                                $attendance = $attendances[$key][0] ?? null;
                                                $status = $attendance->status ?? 'A';
                                                $displayStatus = $status;

                                                if ($status == 'P' && ($attendance->extraday ?? 0) == 1) {
                                                    $displayStatus = '2P';
                                                }
                                                if ($status == 'H') {
                                                    $displayStatus = 'H';
                                                }
                                            @endphp

                                            <td class="attendance-cell text-center" data-user-id="{{ $staff->id }}"
                                                data-date="{{ $date }}" data-status="{{ $status }}"
                                                data-checkin="{{ $attendance->check_in_time ?? '' }}"
                                                data-checkout="{{ $attendance->check_out_time ?? '' }}"
                                                data-reason="{{ $attendance->reason ?? '' }}"
                                                data-extraday="{{ $attendance->extraday ?? '0' }}"
                                                @if ($dayName === 'Sun' && $sundayOff === 'yes') data-disabled="true" @endif
                                                style="
                                                        cursor: {{ $dayName === 'Sun' && $sundayOff === 'yes' ? 'not-allowed' : 'pointer' }};
                                                        @if ($dayName === 'Sun' && $sundayOff === 'yes') background-color: #f36774; /* red */
                                                            color: #fff;
                                                        @elseif ($dayName === 'Sun' && $sundayOff === 'no')
                                                            background-color: #f36774; /* green */
                                                            color: #fff; @endif
                                                    ">
                                                {{-- ✅ Handle Sunday Display --}}
                                                @if ($dayName === 'Sun')
                                                    @if ($sundayOff === 'yes')
                                                        <strong>Sunday</strong>
                                                    @elseif ($displayStatus == 'P' || $displayStatus == '2P')
                                                        <strong class="text-success">{{ $displayStatus }}</strong>
                                                      @elseif ($displayStatus == 'H')
                                                        <strong class="text-success">HP</strong>
                                                    @else
                                                        <strong class="text-white">A</strong>
                                                    @endif
                                                @else
                                                    {{-- Normal attendance display --}}
                                                    @if ($displayStatus == 'P' || $displayStatus == '2P')
                                                        <strong class="text-success">{{ $displayStatus }}</strong>
                                                    @elseif ($displayStatus == 'H')
                                                        <strong class="text-success">HP</strong>
                                                    @else
                                                        <strong class="text-danger">A</strong>
                                                    @endif
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- Attendance Modal -->
    {{-- <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="attendanceForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Attendance</h5>
                        <div class="mb-2 d-flex fw-bold ">
                            <label class="me-2">Date:</label>
                            <div id="display_date" class="fw-bold"></div>
                        </div>
                       
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="user_id">
                        <input type="hidden" name="date" id="date">
                        <div class="mb-2">
                            <label>Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="P">Present</option>
                                <option value="H">Half Day</option>
                                <option value="A">Absent</option>
                            </select>
                            <div class="text-danger" id="error_status"></div>

                        </div>
                        <div class="mb-2">
                            <label>Check-In Time</label>
                            <input type="time" class="form-control" name="check_in_time" id="check_in_time">
                            <div class="text-danger" id="error_check_in_time"></div>

                        </div>
                        <div class="mb-2">
                            <label>Check-Out Time</label>
                            <input type="time" class="form-control" name="check_out_time" id="check_out_time">
                            <div class="text-danger" id="error_check_out_time"></div>
                        </div>
                        <div class="mb-2" id="extraday-field" style="display: none;">
                            <label>Extra Day</label>
                            <select class="form-select" name="extraday" id="extraday">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                            <div class="text-danger" id="error_extraday"></div>
                        </div>
                        <div class="mb-2">
                            <label>Reason</label>
                            <textarea class="form-control" name="reason" id="reason"></textarea>
                            <div class="text-danger" id="error_reason"></div>
                        </div>
                    </div>
                    <div style="padding: 1rem;">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-submit">Save</button>
                    </div>
                    <div class="text-danger" id="error_date"></div> 
                </div>
            </form>
        </div>
    </div> --}}
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="attendanceForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">

                        <div class="mb-2 d-flex align-items-center justify-content-between w-100 fw-bold">
                            <h5 class="modal-title">Update Attendance</h5>

                            <div class="d-flex align-items-center">
                                <label class="me-2">Date:</label>
                                <div id="display_date" class="fw-bold"></div>
                            </div>
                            <button type="button" class="btn text-light" style="background-color: #ff9f43;"
                                id="viewHistoryBtn">
                                <i class="bi bi-eye"></i> Attendance History
                            </button>
                        </div>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="user_id">
                        <input type="hidden" name="date" id="date">

                        <!-- Attendance History Section -->
                        <div id="attendanceHistorySection" class="mb-3" style="display:none;">
                            <h6 class="fw-bold">Attendance History</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>#</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceHistoryBody"></tbody>
                            </table>
                        </div>

                        <div class="mb-2">
                            <label>Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="P">Present</option>
                                <option value="H">Half Day</option>
                                <option value="A">Absent</option>
                            </select>
                            <div class="text-danger" id="error_status"></div>
                        </div>

                        <div class="mb-2">
                            <label>Check-In Time</label>
                            <input type="time" class="form-control" name="check_in_time" id="check_in_time">
                            <div class="text-danger" id="error_check_in_time"></div>
                        </div>

                        <div class="mb-2">
                            <label>Check-Out Time</label>
                            <input type="time" class="form-control" name="check_out_time" id="check_out_time">
                            <div class="text-danger" id="error_check_out_time"></div>
                        </div>

                        <div class="mb-2" id="extraday-field" style="display:none;">
                            <label>Extra Day</label>
                            <select class="form-select" name="extraday" id="extraday">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Reason</label>
                            <textarea class="form-control" name="reason" id="reason"></textarea>
                            <div class="text-danger" id="error_reason"></div>
                        </div>
                    </div>

                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-submit">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            console.log(selectedSubAdminId);

            // Function to update the month-year header
            function updateMonthYearHeader() {
                const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August",
                    "September", "October", "November", "December"
                ];
                const selectedDate = $('#select-date').val();
                const [year, month] = selectedDate.split('-');
                const monthName = monthNames[parseInt(month, 10) - 1];
                $('#month-year-header').text(`${monthName} ${year} Attendance`);
            }

            // Initialize month-year header
            updateMonthYearHeader();

            // Function to fetch and update attendance data
            function fetchAttendanceData() {
                const month = $('#select-date').val();
                const search = $('#searchStaff').val();

                $.ajax({
                    url: "{{ route('attendance.list') }}",
                    method: 'GET',
                    data: {
                        month: month,
                        search: search
                    },
                    success: function(response) {
                        $('#attendance-table-wrapper').html($(response).find(
                            '#attendance-table-wrapper').html());
                        updateMonthYearHeader();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            }

            // Listen for changes on the month input and search input
            $('#select-date, #searchStaff').on('change input', function() {
                fetchAttendanceData();
            });

            // Function to toggle fields based on status
            function toggleAttendanceFields(status) {
                if (status === 'P' || status === 'H') {
                    $('#check_in_time').closest('.mb-2').show();
                    $('#check_out_time').closest('.mb-2').show();
                    $('#extraday-field').show(); // Show extraday field
                    $('#reason').closest('.mb-2').hide();
                } else {
                    $('#check_in_time').closest('.mb-2').hide();
                    $('#check_out_time').closest('.mb-2').hide();
                    $('#extraday-field').hide(); // Hide extraday field
                    $('#reason').closest('.mb-2').show();
                }
            }

            // When status changes in the modal
            $('#status').on('change', function() {
                toggleAttendanceFields($(this).val());
            });

            // When opening modal
            // $(document).on('click', '.attendance-cell', function() {
            //     // Replace 'staff' with whatever your staff role identifier is
            //     const currentUserRole = '{{ auth()->user()->role }}';

            //     if (currentUserRole === 'staff') {
            //         // Staff should not open modal
            //         return; // exit function, do nothing
            //     }

            //     const status = $(this).data('status');
            // $(document).on('click', '.attendance-cell', function() {
            //     const currentUserRole = '{{ auth()->user()->role }}';

            //     // 🚫 1. Prevent click if disabled (Sunday Off)
            //     if ($(this).data('disabled')) {
            //         return;
            //     }

            //     // 🚫 2. Prevent click if staff role
            //     if (currentUserRole === 'staff') {
            //         return;
            //     }

            //     // ✅ If not disabled and not staff, open modal
            //     const status = $(this).data('status');


            //     $('#user_id').val($(this).data('user-id'));
            //     $('#date').val($(this).data('date'));
            //     $('#status').val(status);
            //     $('#check_in_time').val($(this).data('checkin'));
            //     $('#check_out_time').val($(this).data('checkout'));
            //     $('#reason').val($(this).data('reason'));
            //     $('#extraday').val($(this).data('extraday'));

            //     toggleAttendanceFields(status);

            //     $('#status').prop('disabled', false);
            //     $('#check_in_time').prop('disabled', false);
            //     $('#check_out_time').prop('disabled', false);
            //     $('#reason').prop('disabled', false);
            //     $('#attendanceForm button[type="submit"]').show();
            //     $('#display_date').text($(this).data('date'));

            //     $('#attendanceModal').modal('show');
            // });

            $(document).on('click', '.attendance-cell', function() {
                const currentUserRole = '{{ auth()->user()->role }}';
                if ($(this).data('disabled') || currentUserRole === 'staff') return;

                const userId = $(this).data('user-id');
                const date = $(this).data('date');
                const status = $(this).data('status');

                $('#user_id').val(userId);
                $('#date').val(date);
                $('#status').val(status);
                $('#check_in_time').val($(this).data('checkin'));
                $('#check_out_time').val($(this).data('checkout'));
                $('#reason').val($(this).data('reason'));
                $('#extraday').val($(this).data('extraday'));
                $('#display_date').text(date);

                toggleAttendanceFields(status);

                // Hide history section by default
                $('#attendanceHistorySection').hide();

                // Store user/date for history use
                $('#viewHistoryBtn').data('user-id', userId);
                $('#viewHistoryBtn').data('date', date);

                $('#attendanceModal').modal('show');
            });

            // 🆕 When clicking “View Attendance History”
            $('#viewHistoryBtn').on('click', function() {
                var authToken = localStorage.getItem("authToken");

                const userId = $(this).data('user-id');
                const date = $(this).data('date');

                if (!userId || !date) {
                    Swal.fire('Error', 'User or date not found!', 'error');
                    return;
                }

                $('#attendanceHistoryBody').html(
                    '<tr><td colspan="4" class="text-center">Loading...</td></tr>');

                $.ajax({
                    url: "{{ route('attendance.history') }}",
                    method: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: {
                        user_id: userId,
                        date: date
                    },
                    success: function(res) {
                        const logs = res.logs;
                        let html = '';

                        if (logs.length > 0) {
                            logs.forEach((log, index) => {
                                html += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${log.check_in ?? '-'}</td>
                                        <td>${log.checkout_out ?? '-'}</td>
                                        <td>${new Date(log.created_at).toLocaleString()}</td>
                                    </tr>`;
                            });
                        } else {
                            html =
                                `<tr><td colspan="4" class="text-center">No history found</td></tr>`;
                        }

                        $('#attendanceHistoryBody').html(html);
                        $('#attendanceHistorySection').slideDown(); // show with animation
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Could not fetch history', 'error');
                    }
                });
            });



            // Submit attendance form
            $('#attendanceForm').on('submit', function(e) {
                e.preventDefault();

                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                let formData = new FormData($('#attendanceForm')[0]);
                formData.append('selectedSubAdminId', selectedSubAdminId);

                // Clear any inline errors before new request
                $('#attendanceForm .text-danger').html('');

                $.ajax({
                    url: "{{ route('attendance.store') }}",
                    method: 'POST',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: formData,
                    processData: false, // important for FormData
                    contentType: false, // important for FormData
                    success: function(res) {
                        $('#attendanceModal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // reload to show updated table
                        });
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;
                        let errors = response ? response.errors : null;

                        if (errors) {
                            // Combine all errors into one string for Swal
                            let allErrors = [];
                            for (let key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    allErrors.push(errors[key][0]);
                                }
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: allErrors.join('<br>'),
                                confirmButtonColor: '#ff9f43'
                            });
                        } else {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                        }
                    }
                });
            });

        });
    </script>
@endpush
