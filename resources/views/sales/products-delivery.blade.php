@extends('layout.app')

@section('title', 'Orders Delivery')

@section('content')
<style>
    /* Styling for the table and elements */
    .status-dropdown {
        min-width: 130px;
    }
    
    /* Compact layout to remove blank space */
    .delivery-page-shell {
        padding: 24px;
    }
    .delivery-page-title {
        font-size: 18px;
        font-weight: 600;
        color: #212B36;
        margin-bottom: 4px;
        margin-top: 0;
    }
    .delivery-page-subtitle {
        font-size: 14px;
        color: #637381;
        margin-bottom: 24px;
        margin-top: 0;
        font-weight: normal;
    }
    .delivery-filter-card {
        background: #fff;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 24px;
        border: 1px solid #e5e5e5;
    }
    .delivery-filter-card form {
        margin-bottom: 0 !important;
    }
    .delivery-table-card {
        background: #fff;
        border-radius: 5px;
        padding: 20px;
        border: 1px solid #e5e5e5;
    }
    
    /* Desktop: hide details column */
    @media (min-width: 768px) {
        table.datanew thead th.details-column,
        table.datanew tbody td:nth-child(2) {
            display: none !important;
        }
    }

    @media (max-width: 767.98px) {
        .delivery-page-shell {
            padding: 15px;
        }

        .table-responsive {
            overflow-x: hidden !important;
        }

        table.datanew {
            table-layout: fixed !important;
            width: 100% !important;
        }

        table.datanew thead th:first-child,
        table.datanew tbody td:first-child {
            word-wrap: break-word;
            white-space: normal;
        }

        /* Hide all except first (Order) + Details */
        table.datanew thead th:nth-child(n+3),
        table.datanew tbody td:nth-child(n+3) {
            display: none !important;
        }

        /* Fix toggle icon position on mobile (same as saleslist/quantity types) */
        table.datanew thead th.details-column,
        table.datanew tbody td:nth-child(2) {
            display: table-cell !important;
            width: 50px;
            text-align: center;
            vertical-align: top !important;
            padding-top: 25px !important;
        }
    }
</style>

<div class="delivery-page-shell">
    <h4 class="delivery-page-title">Orders Delivery</h4>
    <h6 class="delivery-page-subtitle">View and manage delivery status for all sales orders</h6>

    <div class="delivery-filter-card">
        <!-- Filters -->
        <form method="GET" action="{{ route('sales.products-delivery') }}" id="filter-form">
                    <div class="row align-items-end">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Order No</label>
                                <input type="text" name="order_number" class="form-control" placeholder="Search Order No" value="{{ request('order_number') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-select" onchange="document.getElementById('filter-form').submit();">
                                    <option value="All" {{ request('status') == 'All' ? 'selected' : '' }}>All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="partially_delivered" {{ request('status') == 'partially_delivered' ? 'selected' : '' }}>Partially Delivered</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-6 col-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary w-100"><i class="fa fa-filter"></i> Filter</button>
                            </div>
                        </div>
                    </div>
                </form>
    </div>

    <div class="delivery-table-card">
        <!-- Table -->
        <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>Order No.</th>
                                <th class="details-column">Details</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Assigned Staff</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>
                                    <div>
                                        <div class="d-md-none mb-1">
                                            <strong>{{ $order->order_number }}</strong><br>
                                            <span class="text-muted" style="font-size:0.95rem;">{{ $order->user ? $order->user->name : ($order->customer_name ?? 'Walk-in Customer') }}</span>
                                        </div>
                                        <div class="d-none d-md-block">
                                            <strong>{{ $order->order_number }}</strong>
                                        </div>
                                        
                                        <!-- Mobile collapse -->
                                        <div class="collapse d-md-none mt-2" id="details-{{ $order->id }}">
                                            <div style="background: #f8f9fa; border-radius: 6px; padding: 8px; font-size: 14px;">
                                                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y H:i A') }}</p>
                                                <p><strong>Customer:</strong> {{ $order->user ? $order->user->name : ($order->customer_name ?? 'Walk-in Customer') }}</p>
                                                <p><strong>Total:</strong> {{ number_format($order->total_amount, 2) }}</p>
                                                <p><strong>Assigned Staff:</strong> {{ $order->assignedStaff ? $order->assignedStaff->name : 'N/A' }}</p>
                                                <p><strong>Status:</strong></p>
                                                <select class="form-select form-select-sm status-dropdown mb-2" data-order-id="{{ $order->id }}">
                                                    <option value="pending" {{ $order->delivery_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="partially_delivered" {{ $order->delivery_status == 'partially_delivered' ? 'selected' : '' }}>Partially Delivered</option>
                                                    <option value="delivered" {{ $order->delivery_status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                    <option value="cancelled" {{ $order->delivery_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                                <div class="mt-2">
                                                    <a class="me-3 d-inline-flex align-items-center" href="{{ route('sales.invoice.pdf', $order->id) }}" target="_blank" title="Download PDF" style="background: #fff; border: 1px solid #dee2e6; border-radius: 6px; padding: 6px 10px; color: #dc3545; text-decoration: none;">
                                                        <i class="fas fa-file-pdf me-2"></i> PDF Invoice
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="#details-{{ $order->id }}" class="toggle-details" data-bs-toggle="collapse">
                                        <i class="fas fa-plus-circle" style="font-size: 18px; color: #ff9f43;"></i>
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y H:i A') }}</td>
                                <td>{{ $order->user ? $order->user->name : ($order->customer_name ?? 'Walk-in Customer') }}</td>
                                <td>{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    {{ $order->assignedStaff ? $order->assignedStaff->name : 'N/A' }}
                                </td>
                                <td>
                                    <select class="form-select form-select-sm status-dropdown" data-order-id="{{ $order->id }}">
                                        <option value="pending" {{ $order->delivery_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="partially_delivered" {{ $order->delivery_status == 'partially_delivered' ? 'selected' : '' }}>Partially Delivered</option>
                                        <option value="delivered" {{ $order->delivery_status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ $order->delivery_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <!-- @if($order->delivery_status !== 'delivered' && $order->delivery_status !== 'cancelled')
                                    <span class="me-3" title="Update Delivery">
                                        <i class="fas fa-truck text-info"></i>
                                    </span>
                                    @endif -->
                                    <a class="me-3" href="{{ route('sales.invoice.pdf', $order->id) }}" target="_blank" title="Download PDF">
                                        <img src="{{ asset('admin/assets/img/icons/pdf.svg') }}" alt="pdf">
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle details icon
        $(document).on('click', '.toggle-details', function() {
            let icon = $(this).find('i');
            if (icon.hasClass('fa-plus-circle')) {
                icon.removeClass('fa-plus-circle')
                    .addClass('fa-minus-circle')
                    .css('color', 'red');
            } else {
                icon.removeClass('fa-minus-circle')
                    .addClass('fa-plus-circle')
                    .css('color', '#ff9f43');
            }
        });

        // Store original value on focus
        $('.status-dropdown').on('focus', function () {
            $(this).data('original-value', $(this).val());
        });

        $('.status-dropdown').on('change', function() {
            let dropdown = $(this);
            let status = dropdown.val();
            let orderId = dropdown.data('order-id');
            let originalValue = dropdown.data('original-value') || dropdown.find('option[selected]').val() || 'pending';

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to change the delivery status?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff9f43',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/sales/products-delivery/${orderId}/status`,
                        type: 'PATCH',
                        data: { status: status },
                        success: function(response) {
                            dropdown.data('original-value', status);
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: response.message || 'Delivery status has been updated.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            dropdown.val(originalValue);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update status.'
                            });
                        }
                    });
                } else {
                    // Revert to original value if cancelled
                    dropdown.val(originalValue);
                }
            });
        });
    });
</script>
@endpush
