@extends('layout.app')
@section('title', 'Staff Leave Requests')

@section('content')
    @php
        $canManageLeaveRequests = in_array(optional(auth()->user())->role, ['admin', 'sub-admin', 'hr'], true);
    @endphp

    <style>
        /* Desktop: show all columns */
        .leave-desktop-col {
            display: table-cell;
        }

        .leave-mobile-col {
            display: none;
        }

        /* Hide expanded details on desktop always */
        .leave-expanded-details {
            display: none;
        }

        /* Mobile: hide extra columns, show toggle */
        @media (max-width: 767px) {
            .leave-desktop-col {
                display: none !important;
            }

            .leave-mobile-col {
                display: none !important;
            }

            .leave-name-col {
                vertical-align: top !important;
                position: relative;
            }

            .leave-name-wrapper {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
            }

            .leave-name-info {
                display: flex;
                align-items: center;
                gap: 8px;
                flex: 1;
                min-width: 0;
            }

            .leave-expand-toggle {
                background-color: #FF9F43;
                border: none;
                padding: 0;
                cursor: pointer;
                color: #fff;
                font-size: 20px;
                font-weight: 300;
                line-height: 1;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                align-self: flex-start;
                margin-top: 6px;
            }

            .leave-expand-toggle.active {
                background-color: #e05c00;
            }

            .leave-expanded-details.show {
                display: block;
                /* margin-top: 10px;
                padding: 10px;
                background-color: #f8f9fa;
                border-radius: 6px;
                border-left: 3px solid #FF9F43; */
            }

            .leave-detail-row {
                display: flex;
                justify-content: space-between;
                padding: 5px 0;
                border-bottom: 1px solid #e9ecef;
                font-size: 13px;
            }

            .leave-detail-row:last-child {
                border-bottom: none;
            }

            .leave-detail-label {
                font-weight: 600;
                color: #495057;
            }

            .leave-detail-value {
                color: #212529;
                text-align: right;
            }

            .leave-detail-actions {
                margin-top: 10px;
                padding-top: 8px;
                border-top: 1px solid #dee2e6;
                display: flex;
                gap: 10px;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Staff Leave Requests</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('leave.add') }}" class="btn btn-added" style="background: #FF9F43; color: #fff;">
                    <i class="fa fa-plus me-1"></i> Add Leave
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-top">
                    <div class="search-set">
                        <div class="search-input">
                            <a class="btn btn-searchset"><img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img"></a>
                        </div>
                    </div>
                    <div class="wordset">
                        <div class="d-flex align-items-center">
                            <select id="department_filter" class="form-select me-2">
                                <option value="">All Departments</option>
                            </select>
                            <select id="employee_filter" class="form-select">
                                <option value="">All Staff</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="overflow-x: unset;">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="leave-desktop-col">Start Date</th>
                                <th class="leave-desktop-col">End Date</th>
                                <th class="leave-desktop-col">Days</th>
                                <th class="leave-desktop-col">Reason</th>
                                <th class="leave-desktop-col">Status</th>
                                @if ($canManageLeaveRequests)
                                    <th class="leave-desktop-col">Action</th>
                                @endif
                                <th class="leave-mobile-col">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaves as $leave)
                                @php
                                    $status = strtolower($leave->status);
                                    $bgColor = '#FF9F43';
                                    if ($status == 'approved') $bgColor = '#28a745';
                                    elseif ($status == 'rejected') $bgColor = '#dc3545';
                                    $profileImage = $leave->user && $leave->user->profile_image
                                        ? env('ImagePath', '/') . 'storage/' . $leave->user->profile_image
                                        : env('ImagePath', '/') . 'admin/assets/img/customer/customer5.jpg';
                                    
                                    // Remove double slashes just in case
                                    $profileImage = str_replace('//storage', '/storage', $profileImage);
                                    $profileImage = str_replace('//admin', '/admin', $profileImage);
                                    
                                    $staffName = $leave->user ? $leave->user->name : $leave->firstname;
                                @endphp
                                <tr>
                                    <td class="leave-name-col">
                                        {{-- Desktop: plain flex row. Mobile: flex row with toggle on right --}}
                                        <div class="leave-name-wrapper">
                                            <div class="leave-name-info">
                                                <img src="{{ $profileImage }}" alt="" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; flex-shrink: 0;">
                                                <span>{{ $staffName }}</span>
                                            </div>
                                            {{-- Mobile toggle button inside name cell, top-right --}}
                                            <button type="button" class="leave-expand-toggle d-md-none" data-target="leave-details-{{ $leave->id }}">
                                                <span class="leave-toggle-icon" style="line-height:1; display:block;">+</span>
                                            </button>
                                        </div>

                                        {{-- Mobile expanded details --}}
                                        <div class="leave-expanded-details" id="leave-details-{{ $leave->id }}">
                                            <div class="leave-detail-row">
                                                <span class="leave-detail-label">Start Date:</span>
                                                <span class="leave-detail-value">{{ $leave->start_date->format('Y-m-d') }}</span>
                                            </div>
                                            <div class="leave-detail-row">
                                                <span class="leave-detail-label">End Date:</span>
                                                <span class="leave-detail-value">{{ $leave->end_date->format('Y-m-d') }}</span>
                                            </div>
                                            <div class="leave-detail-row">
                                                <span class="leave-detail-label">Days:</span>
                                                <span class="leave-detail-value">{{ $leave->no_of_day }}</span>
                                            </div>
                                            <div class="leave-detail-row">
                                                <span class="leave-detail-label">Reason:</span>
                                                <span class="leave-detail-value">{{ $leave->reason }}</span>
                                            </div>
                                            <div class="leave-detail-row">
                                                <span class="leave-detail-label">Status:</span>
                                                <span class="leave-detail-value">
                                                    <span class="badge" style="background: {{ $bgColor }}; color: #fff; padding: 5px 10px; border-radius: 20px;">{{ ucfirst($leave->status) }}</span>
                                                </span>
                                            </div>
                                            @if ($canManageLeaveRequests)
                                            <div class="leave-detail-actions">
                                                <form action="{{ route('leave.request.status', $leave->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="btn p-0 border-0 bg-transparent" title="Approve" {{ $status === 'approved' ? 'disabled' : '' }}>
                                                        <i class="fa fa-check-circle" style="color: #28a745; font-size: 22px; opacity: {{ $status === 'approved' ? '0.4' : '1' }};"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('leave.request.status', $leave->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn p-0 border-0 bg-transparent" title="Reject" {{ $status === 'rejected' ? 'disabled' : '' }}>
                                                        <i class="fa fa-times-circle" style="color: #dc3545; font-size: 22px; opacity: {{ $status === 'rejected' ? '0.4' : '1' }};"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Desktop columns --}}
                                    <td class="leave-desktop-col">{{ $leave->start_date->format('Y-m-d') }}</td>
                                    <td class="leave-desktop-col">{{ $leave->end_date->format('Y-m-d') }}</td>
                                    <td class="leave-desktop-col">{{ $leave->no_of_day }}</td>
                                    <td class="leave-desktop-col">{{ $leave->reason }}</td>
                                    <td class="leave-desktop-col">
                                        <span class="badge" style="background: {{ $bgColor }}; color: #fff; padding: 10px 15px; border-radius: 20px;">{{ ucfirst($leave->status) }}</span>
                                    </td>
                                    @if ($canManageLeaveRequests)
                                        <td class="leave-desktop-col">
                                            <div class="d-flex align-items-center gap-2">
                                                <form action="{{ route('leave.request.status', $leave->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="btn p-0 border-0 bg-transparent" title="Approve leave" {{ $status === 'approved' ? 'disabled' : '' }}>
                                                        <i class="fa fa-check-circle" style="color: #28a745; font-size: 20px; opacity: {{ $status === 'approved' ? '0.5' : '1' }};"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('leave.request.status', $leave->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn p-0 border-0 bg-transparent" title="Reject leave" {{ $status === 'rejected' ? 'disabled' : '' }}>
                                                        <i class="fa fa-times-circle" style="color: #dc3545; font-size: 20px; opacity: {{ $status === 'rejected' ? '0.5' : '1' }};"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif

                                    {{-- Mobile toggle column hidden (button moved inside name cell) --}}
                                    <td class="leave-mobile-col"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.leave-expand-toggle');
            if (!btn) return;

            const targetId = btn.dataset.target;
            const detailsDiv = document.getElementById(targetId);
            const icon = btn.querySelector('.leave-toggle-icon');

            if (!detailsDiv) return;

            const isVisible = detailsDiv.classList.contains('show');
            detailsDiv.classList.toggle('show', !isVisible);
            btn.classList.toggle('active', !isVisible);
            if (icon) icon.textContent = isVisible ? '+' : '−';
        });
    </script>
@endsection

@push('js')
@endpush
