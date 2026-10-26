@extends('layout.app')

@section('title', 'Return Sale')

@section('content')
    <style>
        .d-none {
            display: none !important;
        }

        /* .select2-container--default .select2-selection--single {
                height: 38px;
                padding-top: 4px;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
            } */

        .img-flag {
            vertical-align: middle;
        }
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 15px !important
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Create Return Sale</h4>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-sm-6 col-6">

                        <div class="form-group">
                            <label>Order ID</label>
                            <div class="row">
                                <div class="col-lg-12 col-sm-12 col-12">
                                    <select name="user_id" class="form-control select2-invoices">
                                        <option value="">Select Order ID</option>
                                        @foreach($invoiceNumbers as $invoiceNumber)
                                            <option value="{{ $invoiceNumber }}">{{ $invoiceNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <div class="input-groupicon">
                                <input type="tel" id="customer_phone" class="form-control" placeholder="Customer Name"
                                    name="customer_phone" value="{{ $sales->user->phone ?? '' }}" readonly>
                                <span class="error_customerphone"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-sm-6 col-12 d-none">
                        <div class="form-group">
                            <label>Product Name</label>
                            <div class="input-groupicon">
                                <!-- Product Select Dropdown -->


                                <div class="addonset">
                                    <img src="{{ env('ImagePath').'admin/assets/img/icons/scanner.svg'}}" alt="img">
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
                                    <th>Product Name</th>
                                    <th>QTY</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                                <!-- Existing items (if any) -->

                            </tbody>

                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Discount(%)</label>
                            <input type="number" class="form-control" name="discount" id="discount-input" value="" min="0"
                                max="100">
                            <div id="discount-error" class="text-danger mt-1" style="display:none;"></div>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-lg-6">
                            <!-- Tax and Grand Total (no checkboxes) -->
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li class="order-subtotal">
                                        <h4>Subtotal</h4>
                                        <h5>
                                            @if($currencyPosition == 'left')
                                                {{ $currencySymbol }} <span id="subtotal">0.00</span>
                                            @else
                                                <span id="subtotal">0.00</span> {{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="discount">
                                        <h4>Discount</h4>
                                        <h5>
                                            @if($currencyPosition == 'left')
                                                {{ $currencySymbol }} <span id="discount">0.00</span>
                                            @else
                                                <span id="discount">0.00</span> {{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="total">
                                        <h4>Grand Total</h4>
                                        <h5>
                                            @if($currencyPosition == 'left')
                                                {{ $currencySymbol }} <span id="grand-total">0.00</span>
                                            @else
                                                <span id="grand-total">0.00</span> {{ $currencySymbol }}
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

        const currencySymbol = @json($currencySymbol);
        const currencyPosition = @json($currencyPosition);
        $(document).ready(function () {

            $('.select2-invoices').select2({
                placeholder: 'Select Order ID',
                allowClear: true,
                width: '100%' // optional, to make sure it fits Bootstrap
            });
            var authToken = localStorage.getItem("authToken");

            function formatCurrency(amount) {
                return currencyPosition === 'left'
                    ? currencySymbol + amount
                    : amount + currencySymbol;
            }

            function calculateTotals() {
                let subtotal = 0;

                $('#product-table-body tr').each(function () {
                    let $row = $(this);
                    let quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                    let price = parseFloat($row.find('.quantity-input').data('price')) || 0;
                    let lineTotal = price * quantity;
                    subtotal += lineTotal;

                    $row.find('td:eq(4)').text(formatCurrency(lineTotal.toFixed(2)));
                });

                $('#subtotal').text(subtotal.toFixed(2));

                // // Recalculate tax
                // let totalTax = 0;
                // $('.total-order ul').find('li.tax-row').each(function () {
                //     let rate = parseFloat($(this).find('h5').text().replace('%', '')) || 0;
                //     let taxAmount = (subtotal * rate) / 100;
                //     totalTax += taxAmount;
                //     $(this).find('.tax-amount').text(taxAmount.toFixed(2)); // You can format tax with currency if needed
                // });

                // // Discount
                // let discountPercent = parseFloat($('#discount-input').val()) || 0;
                // let discountAmount = (subtotal * discountPercent) / 100;
                // $('#discount').text(discountAmount.toFixed(2));
                 // ✅ First apply discount
                let discountPercent = parseFloat($('#discount-input').val()) || 0;
                let discountAmount = (subtotal * discountPercent) / 100;
                $('#discount').text(discountAmount.toFixed(2));

                // ✅ Tax should be calculated on discounted subtotal
                let discountedSubtotal = subtotal - discountAmount;

                let totalTax = 0;
                $('.total-order ul').find('li.tax-row').each(function () {
                    let rate = parseFloat($(this).find('h5').text().replace('%', '')) || 0;
                    let taxAmount = (discountedSubtotal * rate) / 100;
                    totalTax += taxAmount;
                    $(this).find('.tax-amount').text(taxAmount.toFixed(2));
                });

                // Grand Total
                let grandTotal = subtotal - discountAmount + totalTax;
                $('#grand-total').text(grandTotal.toFixed(2));
            }

            $('#discount-input').on('input', function () {
                calculateTotals();
            });

            $(document).on('input', '.quantity-input', function () {
                calculateTotals();
            });

            $('select[name="user_id"]').on('change', function () {
                let invoiceNumber = $(this).val();
                if (!invoiceNumber) return;
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: `/api/getSaleDetails/${invoiceNumber}`,
                    type: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    success: function (data) {
                        console.log(data);

                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        // Currency Setup
                        let currencySymbol = data.currency_symbol || '₹';
                        let currencyPosition = data.currency_position || 'left';

                        function formatCurrency(amount) {
                            return currencyPosition === 'left'
                                ? `${currencySymbol}${amount}`
                                : `${amount}${currencySymbol}`;
                        }

                        // Fill customer and discount
                        $('#customer_phone').val(data.order.user_name);
                        $('#discount-input').val(data.order.discount ?? 0);

                        let $tableBody = $('#product-table-body').empty();
                        let subtotal = 0;

                        $.each(data.items, function (index, item) {
                            let price = parseFloat(item.price);
                            let quantity = parseFloat(item.quantity);
                            let lineTotal = price * quantity;
                            subtotal += lineTotal;

                            let row = `
                                        <tr data-product-id="${item.id}">
                                            <td>${index + 1}</td>
                                            <td class="productimgname" style="width: 100%;">
                                                <a href="/product-view/${item.product_id}" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                                                    <img
                                                    src="${item.product_image ? '{{ env('ImagePath').'/storage/' }}' + item.product_image : '{{ env('ImagePath').'/admin/assets/img/product/noimage.png' }}'}"
                                                    alt="${item.product_name}"
                                                    width="40"
                                                    height="40"
                                                    style="object-fit: cover; border-radius: 4px; margin-right: 10px; flex-shrink: 0;">
                                                    <span style="flex: 1;">${item.product_name || 'N/A'}</span>
                                                </a>
                                            </td>

                                            <td>
                                                <input type="number"
                                                    class="form-control quantity-input"
                                                    value="${quantity}"
                                                    step="1"
                                                    min="0"
                                                    max="${quantity}"
                                                    data-price="${price}"
                                                    style="width: 80px;">
                                            </td>
                                            <td>${formatCurrency(price.toFixed(2))}</td>
                                            <td>${formatCurrency(lineTotal.toFixed(2))}</td>
                                            <td></td>
                                        </tr>
                                    `;

                            $tableBody.append(row);
                        });

                        $('#subtotal').text(subtotal.toFixed(2));

                        // Apply discount
                        let discountPercentage = parseFloat(data.order.discount ?? 0);
                        let discountAmount = (subtotal * discountPercentage) / 100;
                        $('#discount').text(discountAmount.toFixed(2));

                        // Remove old tax rows and calculate new
                        $('.total-order ul').find('li.tax-row').remove();

                        let totalTax = 0;
                        $.each(data.taxes, function (index, tax) {
                            let taxAmount = ((subtotal - discountAmount) * tax.tax_rate) / 100;
                            totalTax += taxAmount;

                            let taxRow = `
                                        <li class="tax-row">
                                            <h4>${tax.tax_name}</h4>
                                            <h5>${tax.tax_rate}%</h5>
                                            (${formatCurrency(taxAmount.toFixed(2))})
                                        </li>
                                    `;
                            $('.total-order ul .discount').after(taxRow);
                        });

                        let grandTotal = subtotal - discountAmount + totalTax;
                        $('#grand-total').text(grandTotal.toFixed(2));
                    },
                    error: function (xhr) {
                        console.error('AJAX Error:', xhr.responseText);
                        alert('Something went wrong!');
                    }
                });
            });



            $(document).on("click", '.btn-submit', function (e) {
                e.preventDefault();

                const formData = {
                    products: [],
                    discount: $('#discount-input').val(),
                    grand_total: $('#grand-total').text().replace(/[^\d.]/g, '').trim()
                };

                $('#product-table-body tr').each(function () {
                    const $row = $(this);
                    const productId = $row.data('product-id');
                    const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                    const price = parseFloat($row.find('.quantity-input').data('price')) || 0;
                    const subtotal = (price * quantity).toFixed(2);

                    if (productId && !isNaN(quantity)) {
                        formData.products.push({
                            order_item_id: productId,
                            quantity: quantity,
                            price: price,
                            subtotal: subtotal
                        });
                    }
                });

                if (formData.products.length === 0) {

                    Swal.fire({
                        title: "Error",
                        text: "Please select at least one product.",
                        icon: "error",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#ffa957"
                    });
                    return;
                }

                $.ajax({
                    url: `/api/return_sale`,
                    type: "POST",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ffa957"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('salesreturn.add') }}";
                                }
                            });
                        } else {
                            alert(response.message || 'Something went wrong.');
                        }
                    },
                    error: function (xhr) {
                        alert('An error occurred while updating the order');
                        console.error(xhr.responseText);
                    }
                });
            });
        });


    </script>
@endpush
