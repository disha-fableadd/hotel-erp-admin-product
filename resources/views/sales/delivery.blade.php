@extends('layout.app')

@section('title', 'Delivery Details')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Delivery</h4>
            <h6>#{{ $order->order_number }}</h6>
        </div>
        <div class="page-btn d-flex gap-2">
            <a href="{{ route('sales.details', $order->id) }}" class="btn btn-warning text-white">Details</a>
            <a href="{{ route('sales.invoice', $order->id) }}" class="btn btn-info text-white">Invoice</a>
            <a href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/{{ $order->id }}');" class="btn btn-danger text-white">Print Invoice</a>
            <a href="{{ route('sales.list') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('sales.submitDelivery', $order->id) }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                
                <!-- Info Section -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6 class="mb-2 text-muted">Customer Info</h6>
                        <strong>{{ $order->user->name ?? $order->customer_name ?? 'Default Customer' }}</strong><br>
                        {{ $order->user->phone ?? $order->customer_phone ?? '' }}<br>
                        Address: {{ $order->user->address ?? 'N/A' }}<br>
                        GST: {{ $order->user->gst_number ?? 'N/A' }}
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-2 text-muted">Company Info</h6>
                        <strong>{{ $setting->company_name ?? 'Company Name' }}</strong><br>
                        {{ $setting->email ?? '' }}<br>
                        {{ $setting->phone ?? '' }}<br>
                        {{ $setting->address ?? '' }}
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-2 text-muted">Order Info</h6>
                        <table class="table-borderless">
                            <tr><td>Challan Number:</td><td class="text-end fw-bold">{{ $order->order_invoice ?? 'N/A' }}</td></tr>
                            <tr><td>Challan Date:</td><td class="text-end fw-bold">{{ $order->created_at ? $order->created_at->format('d-M-Y') : 'N/A' }}</td></tr>
                            <tr><td>Order Number:</td><td class="text-end fw-bold">#{{ $order->order_number }}</td></tr>
                            <tr><td>Order Date:</td><td class="text-end fw-bold">{{ $order->created_at ? $order->created_at->format('d-M-Y') : 'N/A' }}</td></tr>
                            <tr><td>Payment Status:</td><td class="text-end fw-bold text-capitalize">{{ $order->payment_status }}</td></tr>
                            <tr><td>Payment Method:</td><td class="text-end fw-bold text-capitalize">{{ $order->payment_method }}</td></tr>
                        </table>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Product Name</th>
                                <th>Ordered QTY</th>
                                <th style="width: 200px;">Deliver QTY</th>
                                <th>Price</th>
                                <th>Item Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->order_items as $index => $item)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" data-index="{{ $index }}">
                                    <input type="hidden" name="deliveries[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                </td>
                                <td class="text-start">{{ $item->product->name ?? 'Unknown Product' }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>
                                    <div class="input-group input-group-sm mb-1">
                                        <input type="number" 
                                               name="deliveries[{{ $index }}][qty]" 
                                               class="form-control deliver-qty-input" 
                                               value="0" 
                                               min="0" 
                                               max="{{ $item->remaining_qty }}"
                                               data-price="{{ $item->rate }}"
                                               data-remaining="{{ $item->remaining_qty }}"
                                               id="qty-input-{{ $index }}">
                                    </div>
                                    <small class="text-muted">Remaining: <span id="rem-{{ $index }}">{{ $item->remaining_qty }}</span></small>
                                </td>
                                <td>{{ number_format($item->rate, 2) }}</td>
                                <td><span id="subtotal-{{ $index }}">0.00</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Previous Deliveries -->
                <h5 class="mb-3">Previous Deliveries</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-striped table-sm text-center">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Delivered Qty</th>
                                <th>By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $deliveryCount = 1; @endphp
                            @forelse($order->deliveries as $delivery)
                                @foreach($delivery->items as $dItem)
                                    <tr>
                                        <td>{{ $deliveryCount++ }}</td>
                                        <td>{{ $dItem->product->name ?? 'Unknown' }}</td>
                                        <td>{{ number_format($dItem->delivered_qty, 2) }}</td>
                                        <td>{{ $delivery->user->name ?? 'System' }}</td>
                                        <td>{{ $delivery->delivered_at ? $delivery->delivered_at->format('Y-m-d H:i') : '' }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="5">No previous deliveries.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-borderless text-end">
                            <tr>
                                <td class="text-muted fw-bold">Subtotal</td>
                                <td class="fw-bold">{{ $currencySymbol }}{{ number_format($order->total_amount + $order->discount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-bold">Discount</td>
                                <td class="fw-bold text-danger">{{ $order->discount > 0 ? $currencySymbol . number_format($order->discount, 2) : '0.00' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-bold">Total</td>
                                <td class="fw-bold text-primary fs-5">{{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg" style="background-color: #ff9f43; border-color: #ff9f43;">Submit Delivery</button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const qtyInputs = document.querySelectorAll('.deliver-qty-input');

        // Toggle all checkboxes
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });

        // Subtotal calculation
        qtyInputs.forEach(input => {
            input.addEventListener('input', function() {
                let qty = parseFloat(this.value) || 0;
                let max = parseFloat(this.getAttribute('data-remaining')) || 0;
                let price = parseFloat(this.getAttribute('data-price')) || 0;
                let index = this.getAttribute('id').replace('qty-input-', '');
                
                if (qty > max) {
                    this.value = max;
                    qty = max;
                } else if (qty < 0) {
                    this.value = 0;
                    qty = 0;
                }

                document.getElementById('subtotal-' + index).innerText = (qty * price).toFixed(2);
                
                // Also check the row automatically if qty > 0
                if (qty > 0) {
                    document.querySelector('.row-checkbox[data-index="' + index + '"]').checked = true;
                }
            });
        });

        // On form submit, validate at least one checked item has qty > 0
        document.querySelector('form').addEventListener('submit', function(e) {
            let hasValidQty = false;
            qtyInputs.forEach(input => {
                let qty = parseFloat(input.value) || 0;
                if (qty > 0) {
                    hasValidQty = true;
                }
            });

            if (!hasValidQty) {
                e.preventDefault();
                alert('Please enter a delivery quantity greater than 0 for at least one item.');
            }
        });
    });
</script>
@endpush
