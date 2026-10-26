@extends('layout.app')

@section('title', 'Edit Order')

@section('content')
    <style>
        .d-none {
            display: none !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            padding-top: 4px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .img-flag {
            vertical-align: middle;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 29px !important;
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Order</h4>
                <!-- <h6>Edit your sale details</h6> -->
            </div>
            <div class="gst-header mb-2" style="display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex">
                    <label class="custom-radio-label" style="margin-right: 1rem;">
                        <input type="radio" name="gst_option" id="without_gst" value="without_gst"
                            {{ $sales->gst_option === 'without_gst' ? 'checked' : '' }} />
                        Without GST
                    </label>

                    <label class="custom-radio-label">
                        <input type="radio" name="gst_option" id="with_gst" value="with_gst"
                            {{ $sales->gst_option === 'with_gst' ? 'checked' : '' }} />
                        With GST
                    </label>
                </div>
            </div>

        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-sm-6 col-6">
                        <input type="hidden" name="update_selse_id" id="update_selse_id" value="{{ $update_id }}">
                        <div class="form-group">
                            <label>Customer</label>
                            <!-- <div class="row"> -->
                            <div class="col-lg-12 col-sm-10 col-12">
                                <select name="user_id" class="form-control" disabled>
                                    <option value="">Select Customer</option>
                                    @foreach ($usernames as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $sales->user_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- </div> -->
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Customer Number</label>
                            <div class="input-groupicon">
                                <input type="tel" id="customer_phone" class="form-control" placeholder="Customer number"
                                    name="customer_phone" value="{{ $sales->user->phone ?? '' }}" readonly>
                                <span class="error_customerphone"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Order Type</label>
                            <select name="order_type" id="order_type_select" class="form-control">
                                <option value="Delivery" {{ $sales->order_type == 'Delivery' ? 'selected' : '' }}>Delivery</option>
                                <option value="Self Pickup" {{ $sales->order_type == 'Self Pickup' ? 'selected' : '' }}>Self Pickup</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Assigned Staff</label>
                            <select name="assigned_staff_id" id="assigned_staff_id" class="form-control">
                                <option value="">- Unassigned -</option>
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}" {{ $sales->assigned_staff_id == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-12 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Item Name</label>
                            <div class="input-groupicon">
                                <!-- Item Select Dropdown -->

                                <select name="product_id[]" class="form-control product-select" multiple="multiple">
                                    @php
                                        $user = auth()->user();
                                        $branchIdToUse = $user->role === 'staff' ? $user->branch_id : $user->id;
                                        $settings = \DB::table('settings')->where('branch_id', $branchIdToUse)->first();
                                        $currencySymbol = $settings->currency_symbol ?? '?';
                                        $currencyPosition = $settings->currency_position ?? 'left';
                                        // Collect selected product IDs from sales items or old input
                                        $selectedProductIds = old(
                                            'product_id',
                                            $sales->order_items->pluck('product_id')->toArray(),
                                        );
                                        $selectedProductIds = array_map('strval', $selectedProductIds); // Ensure all are strings
                                    @endphp

                                    @foreach ($products as $product)
                                        @php
                                            $images = json_decode($product->images ?? '', true);
                                            $imageUrl =
                                                !empty($images) && isset($images[0])
                                                    ? env('ImagePath') . 'storage/' . $images[0]
                                                    : env('ImagePath') . '/admin/assets/img/product/noimage.png';
                                            $priceFormatted = number_format($product->price, 2);
                                            $displayPrice =
                                                $currencyPosition === 'right'
                                                    ? $priceFormatted . ' ' . $currencySymbol
                                                    : $currencySymbol . $priceFormatted;
                                        @endphp

                                        <option value="{{ $product->id }}" data-image="{{ $imageUrl }}"
                                            data-price="{{ $product->price }}" data-name="{{ $product->name }}"
                                              data-qtytype="{{ $product->quantityType->quantity_type ?? 'N/A' }}"
                                              data-qtytype-id="{{ $product->quantity_type_id ?? '' }}"
                                            {{ in_array((string) $product->id, $selectedProductIds) ? 'selected' : '' }}>
                                            {{ $product->name }} - {{ $displayPrice }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="addonset">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/scanner.svg' }}"
                                        alt="img">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="table-responsive mb-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Name</th>
                                    <th>QTY</th>
                                    <th>QTY Type</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                                <!-- Existing items (if any) -->
                                @forelse ($sales->order_items as $index => $item)
                                    <tr data-product-id="{{ $item->product_id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="">
                                            @php
                                                $images = json_decode($item->product->images ?? '', true);
                                            @endphp
                                            <a class="product-img">
                                                @if (!empty($images) && isset($images[0]))
                                                    <img src="{{ env('ImagePath') . 'storage/' . $images[0] }}"
                                                        alt="product" width="40">
                                                @else
                                                    <img src="{{ env('ImagePath') . '/admin/assets/img/product/noimage.png' }}"
                                                        alt="No image" width="40">
                                                @endif
                                            </a>
                                            <a href="javascript:void(0);">{{ $item->product->name ?? 'N/A' }}</a>
                                        </td>
                                        <td>
                                            <input type="number" name="quantities[{{ $item->product_id }}]"
                                                class="form-control quantity-input"
                                                value="{{ number_format($item->quantity, 2, '.', '') }}" step="1"
                                                min="0" data-price="{{ $item->price }}" style="width: 80px;">
                                        </td>
                                         <td class="text-uppercase">
                                            {{ $item->product->quantityType->quantity_type ?? 'N/A' }}
                                            <!-- ADD THIS HIDDEN INPUT -->
                                            <input type="hidden" name="quantity_type_id[{{ $item->product_id }}]" value="{{ $item->product->quantity_type_id ?? '' }}">
                                        </td>
                                        <!-- <td class="text-uppercase" data-qtytype_id="{{ $item->product->quantity_type_id ?? 'N/A' }}">{{ $item->product->quantityType->quantity_type ?? 'N/A' }}</td> -->
                                        <td>{{ number_format($item->price, 2) }}</td>
                                        <td>
                                            <span class="total-amount">{{ number_format($item->total_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="delete-set">
                                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}"
                                                    alt="svg">
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no-products-row">
                                        <td colspan="6" class="text-center">No items selected</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <!-- <tr>
                                                <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                                <td colspan="2">
                                                    <span id="order-subtotal">{{ number_format($sales->order_items->sum('total_amount'), 2) }}</span>
                                                </td>
                                            </tr> -->
                                </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Discount(%)</label>
                            <input type="number" class="form-control" name="discount" id="discount-input"
                                value="{{ number_format($sales->discount, 2) }}" min="0">
                            <div id="discount-error" class="text-danger mt-1" style="display:none;"></div>
                        </div>
                    </div>
                    {{-- <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select class="select" name="payment_method">
                                <option value="cash" {{ $sales->payment_method == 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="debit" {{ $sales->payment_method == 'debit' ? 'selected' : '' }}>Debit
                                </option>
                                <option value="scan" {{ $sales->payment_method == 'scan' ? 'selected' : '' }}>Scan
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select class="select" name="status">
                                <option value="completed" {{ $sales->status == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="pending" {{ $sales->status == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="failed" {{ $sales->status == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                    </div> --}}
                    <div class="row justify-content-end">
                        <div class="col-lg-6">
                            @php
                                $subtotal = $sales->order_items->sum('total_amount');
                                $discountPercent = $sales->discount ?? 0;
                                $discountAmount = ($subtotal * $discountPercent) / 100;

                                // ? Apply discount first
                                $discountedSubtotal = $subtotal - $discountAmount;

                                $taxRates = $TaxRate;

                                // Calculate tax on discounted subtotal
                                $totalTaxAmount = 0;
                                $taxDetails = [];

                                foreach ($taxRates as $tax) {
                                    $taxAmount = ($discountedSubtotal * $tax->tax_rate) / 100;
                                    $taxDetails[] = [
                                        'name' => $tax->tax_name,
                                        'rate' => $tax->tax_rate,
                                        'amount' => $taxAmount,
                                    ];
                                    $totalTaxAmount += $taxAmount;
                                }

                                // ? Grand total = discounted subtotal + tax
                                $grandTotal = $discountedSubtotal + $totalTaxAmount;
                            @endphp


                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li class="subtotal">
                                        <h4>Subtotal</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="subtotal-display">{{ number_format($sales->order_items->sum('total_amount'), 2) }}</span>{{ $setting->currency_symbol ?? '?' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '?' }}<span
                                                    id="subtotal-display">{{ number_format($sales->order_items->sum('total_amount'), 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="discount">
                                        <h4>Discount</h4>
                                        <h5>
                                            <span
                                                id="discount-percent">{{ number_format($sales->discount ?? 0, 2) }}</span>%
                                            (
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="discount-amount">0.00</span>{{ $setting->currency_symbol ?? '?' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '?' }}<span
                                                    id="discount-amount">0.00</span>
                                            @endif
                                            )
                                        </h5>
                                    </li>

                                    <li class="after-discount">
                                        <h4>After Discount</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="after-discount-display">0.00</span>{{ $setting->currency_symbol ?? '?' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '?' }}<span
                                                    id="after-discount-display">0.00</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <div class="tax-section">
                                        @foreach ($TaxRate as $tax)
                                            <li>
                                                <h4>{{ $tax->tax_name }}</h4>
                                                <h5>{{ number_format($tax->tax_rate, 2) }}% (
                                                    @if ($setting->currency_position === 'right')
                                                        <span class="tax-amount"
                                                            data-rate="{{ $tax->tax_rate }}">0.00</span>{{ $setting->currency_symbol ?? '?' }}
                                                    @else
                                                        {{ $setting->currency_symbol ?? '?' }}<span class="tax-amount"
                                                            data-rate="{{ $tax->tax_rate }}">0.00</span>
                                                    @endif
                                                    )
                                                </h5>
                                            </li>
                                        @endforeach
                                    </div>


                                    <li class="total">
                                        <h4>Grand Total</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span id="grand-total">0.00</span>{{ $setting->currency_symbol ?? '?' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '?' }}<span id="grand-total">0.00</span>
                                            @endif
                                        </h5>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">Update Order</button>
                        <a href="{{ route('sales.list') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).on("input", "#discount-input", function () {
            let value = parseFloat($(this).val());
            let errorDiv = $("#discount-error");

            if (value > 100) {
                $(this).val(100);
                errorDiv.text("Discount cannot be greater than 100%").show();
            } else if (value < 0) {
                $(this).val(0);
                errorDiv.text("Discount cannot be less than 0%").show();
            } else {
                errorDiv.hide(); // hide error when value is valid
            }
        });

        $(".select2").select2({
            tags: true,
        });
        $('#customer_select').on('change', function() {
            var phone = $(this).find(':selected').data('phone');
            $('#customer_phone').val(phone || '');
        });
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('input', function() {
                    const quantity = parseFloat(this.value) || 0;
                    const price = parseFloat(this.dataset.price) || 0;
                    const total = quantity * price;

                    // Find the closest row
                    const row = this.closest('tr');
                    if (row) {
                        const totalSpan = row.querySelector('.total-amount');
                        if (totalSpan) {
                            totalSpan.textContent = total.toFixed(2);
                        }
                    }
                });
            });
        });

        function updateSubtotal() {
            let subtotal = 0;
            document.querySelectorAll('.quantity-input').forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                const price = parseFloat(input.dataset.price) || 0;
                subtotal += quantity * price;

                const row = input.closest('tr');
                const totalSpan = row.querySelector('.total-amount');
                if (totalSpan) {
                    totalSpan.textContent = (quantity * price).toFixed(2);
                }
            });

            // Update the order subtotal
            const subtotalSpan = document.getElementById('order-subtotal');
            if (subtotalSpan) {
                subtotalSpan.textContent = subtotal.toFixed(2);
            }
        }

        // Add event listener to all quantity inputs
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('input', updateSubtotal);
            });
        });
        $(document).ready(function() {
            // Initialize Select2 with images
            function formatProduct(product) {
                if (!product.id) return product.text;
                var $product = $(
                    '<span><img src="' + $(product.element).data('image') +
                    '" class="img-flag" style="width: 20px; margin-right: 10px;" />' + product.text + '</span>'
                );
                return $product;
            }

            $('.product-select').select2({
                templateResult: formatProduct,
                templateSelection: formatProduct,
                closeOnSelect: false
            });

            // Handle customer selection change
            $('#customer_name').on('change', function() {
                var phone = $(this).find(':selected').data('phone');
                $('#customer_phone').val(phone || '');
            });

            // Add product to table
            function addProductToTable(productId, productName, productImage, productPrice, productQtyType, productQtyTypeId) {
                const rowCount = $('tbody tr[data-product-id]').length + 1;
                const newRow = `
            <tr data-product-id="${productId}">
                <td>${rowCount}</td>
                <td class="">
                    <a class="product-img">
                        <img src="${productImage}" alt="product" width="40">
                    </a>
                    <a href="javascript:void(0);">${productName}</a>
                </td>
                <td>
                    <input type="number"
                           name="quantities[${productId}]"
                           class="form-control quantity-input"
                           value="1"
                           step="1"
                           min="0"
                           data-price="${productPrice}"
                           style="width: 80px;">
                </td>

                 <!-- QTY TYPE (display) -->
                <td class="text-uppercase">${productQtyType}
                  <input type="hidden" name="quantity_type_id[${productId}]" value="${productQtyTypeId}">
                </td>
                <td>${parseFloat(productPrice).toFixed(2)}</td>
                <td>
                    <span class="total-amount">${parseFloat(productPrice).toFixed(2)}</span>
                </td>
                <td>
                    <a href="javascript:void(0);" class="delete-set">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="svg">
                    </a>
                </td>
            </tr>
        `;

                $('#no-products-row').remove();
                $('tbody').append(newRow);

                // Add event listeners to the new row
                $('tbody tr[data-product-id="' + productId + '"] .quantity-input').on('input', calculateAllTotals);
                $('tbody tr[data-product-id="' + productId + '"] .delete-set').on('click', function() {
                    $(this).closest('tr').remove();
                    updateProductSelection();
                    toggleNoProductsMessage();
                    calculateAllTotals();
                });
            }

            // Update product selection in dropdown
            function updateProductSelection() {
                const selectedProducts = $('tbody tr[data-product-id]').map(function() {
                    return $(this).data('product-id').toString();
                }).get();
                $('.product-select').val(selectedProducts).trigger('change');
            }

            // Toggle "no products" message
            function toggleNoProductsMessage() {
                if ($('tbody tr[data-product-id]').length === 0) {
                    $('tbody').append(
                        '<tr id="no-products-row"><td colspan="6" class="text-center">No items selected</td></tr>'
                    );
                }
            }

            // Calculate all totals including discount and taxes
            function calculateAllTotals() {
                let subtotal = 0;

                // Subtotal from products
                $('tbody tr[data-product-id]').each(function() {
                    const $row = $(this);
                    const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                    const price = parseFloat($row.find('.quantity-input').data('price')) || 0;
                    const rowTotal = quantity * price;

                    $row.find('.total-amount').text(rowTotal.toFixed(2));
                    subtotal += rowTotal;
                });

                // Subtotal display
                $('#subtotal-display').text(subtotal.toFixed(2));

                // Discount
                const discountPercentage = parseFloat($('#discount-input').val()) || 0;
                const discountAmount = subtotal * (discountPercentage / 100);
                $('#discount-percent').text(discountPercentage.toFixed(2));
                $('#discount-amount').text(discountAmount.toFixed(2));

                // After discount
                const afterDiscount = subtotal - discountAmount;
                $('#after-discount-display').text(afterDiscount.toFixed(2));

                // GST Option
                const gstOption = $('input[name="gst_option"]:checked').val();
                let totalTax = 0;

                if (gstOption === 'with_gst') {
                    // ? Show tax section
                    $('.tax-section').show();

                    // Calculate tax on after-discount
                    $('.tax-amount').each(function() {
                        const taxRate = parseFloat($(this).data('rate')) || 0;
                        const taxAmount = afterDiscount * (taxRate / 100);
                        $(this).text(taxAmount.toFixed(2));
                        totalTax += taxAmount;
                    });
                } else {
                    // ? Hide tax section & reset values
                    $('.tax-section').hide();
                    totalTax = 0;
                }

                // ? Grand Total
                const grandTotal = afterDiscount + totalTax;
                $('#grand-total').text(grandTotal.toFixed(2));
            }

            // Bind events for recalculation
            $(document).on('input', '.quantity-input, #discount-input', function() {
                calculateAllTotals();
            });
            $(document).on('change', 'input[name="gst_option"]', function() {
                calculateAllTotals();
            });

            // Initial calculation on page load
            calculateAllTotals();

            // Handle product selection changes
            $('.product-select').on('change', function() {
                const selectedProducts = $(this).val() || [];
                const existingProducts = $('tbody tr[data-product-id]').map(function() {
                    return $(this).data('product-id').toString();
                }).get();

                // Add new products
                selectedProducts.forEach(productId => {
                    if (!existingProducts.includes(productId)) {
                        const option = $(this).find('option[value="' + productId + '"]');
                        addProductToTable(
                            productId,
                            option.data('name'),
                            option.data('image'),
                            option.data('price'),
                            option.data('qtytype'),
                            option.data('qtytype-id')
                        );
                    }
                });

                // Remove unselected products
                $('tbody tr[data-product-id]').each(function() {
                    const rowProductId = $(this).data('product-id').toString();
                    if (!selectedProducts.includes(rowProductId)) {
                        $(this).remove();
                    }
                });

                toggleNoProductsMessage();
                calculateAllTotals();
            });

            // Initialize existing quantity input events
            $('.quantity-input').on('input', calculateAllTotals);

            // Initialize existing delete buttons
            $('.delete-set').on('click', function() {
                $(this).closest('tr').remove();
                updateProductSelection();
                toggleNoProductsMessage();
                calculateAllTotals();
            });

            // Handle discount input change
            $('#discount-input').on('input', calculateAllTotals);

            // Form submission handler
            $(document).on("click", '.btn-submit', function(e) {
                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                 const createdBy = "{{ auth()->id() }}";
                e.preventDefault();

                // Collect all form data
                const formData = {
                    customer_id: $('#customer_name').val(),
                    update_id: $('#update_selse_id').val(),
                    customer_phone: $('#customer_phone').val(),
                    product_ids: $('.product-select').val(),
                    quantities: {},
                    quantity_type_id : {},
                    discount: $('#discount-input').val(),
                    payment_method: $('select[name="payment_method"]').val(),
                    status: $('select[name="status"]').val(),
                    grand_total: $('#grand-total').text().replace('$', '').trim(),
                    gst_option: $('input[name="gst_option"]:checked').val(),
                    selectedSubAdminId: selectedSubAdminId || null,
                     created_by: createdBy,
                };
                // console.log(formData);


                // Collect quantities
                // $('.quantity-input').each(function() {
                //     const productId = $(this).closest('tr').data('product-id');
                //     formData.quantities[productId] = $(this).val();
                //     const qtyType = $(this).closest('tr').find('td:eq(3)').data('qtytype_id'); 
                //     console.log(qtyType);
                    
                //     formData.quantity_type_id[productId] = qtyType;
                // });
                    // Collect QTY + QTY TYPE
                
                $('.quantity-input').each(function() {
                const $row = $(this).closest('tr');
                const productId = $row.data('product-id');
                
                formData.quantities[productId] = $(this).val();
                
                // Get quantity_type_id from the hidden input
                const qtyTypeId = $row.find('input[name^="quantity_type_id"]').val();
                formData.quantity_type_id[productId] = qtyTypeId;
            });


          

                // Send AJAX request to update order
                $.ajax({
                    url: `/api/update_sale`,
                    type: "POST",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success!",
                                text: "Order updated successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('sales.list') }}";
                                }
                            });
                        } else {
                            // ? SweetAlert error
                            Swal.fire({
                                title: "Error",
                                text: response.message,
                                icon: "error",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    },
                    error: function(xhr) {
                        let message = 'An error occurred while updating the order';
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.message) {
                                message = res.message;
                            }
                        } catch (e) {
                            console.error('Failed to parse error message:', e);
                        }

                        Swal.fire({
                            title: "Error",
                            text: message,
                            icon: "error",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43"
                        });
                    }
                });
            });
            // Initial calculation
            calculateAllTotals();
        });
        // Initialize other select2 elements
        $(".select2").select2({
            tags: true,
        });

        // Handle customer name change
        $('#customer_name').on('change', function() {
            var phone = $(this).find(':selected').data('phone');
            $('#customer_phone').val(phone || '');
        });
    </script>
@endpush
