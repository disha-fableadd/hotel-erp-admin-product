@extends('layout.app')

@section('title', ' Purchase Return')

@section('content')
<style>
    @media screen and (max-width: 768px) {
        span.pro_purhcaes {
            white-space: normal;
        }
        .form-group {
            margin-bottom: 15px !important
        }
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Purchase Return</h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- Invoice Number Select -->
                <div class="col-lg-6 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Invoice Number</label>
                        <select class="form-control select2 invoice_id" name="invoice_id" id="invoice_id" required>
                            <option value="">Select Invoice Number</option>
                            @foreach ($invoices as $invoice)
                            <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }}</option>
                            @endforeach
                        </select>

                    </div>
                </div>

                <!-- Vendor Name Input -->
                <div class="col-lg-6 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Vendor Name</label>
                        <input type="text" name="vendor_name" id="vendor_name" class="form-control"
                            placeholder="Vendor Name" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Category </th>
                                <th>Grand Total (₹)</th>
                                <th>QTY </th>
                                <th>Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- <tr>
                                                        <td class="productimgname">
                                                            <a class="product-img">
                                                                <img src="admin/assets/img/product/product7.jpg" alt="product">
                                                            </a>
                                                            <a href="javascript:void(0);">Apple Earpods</a>
                                                        </td>
                                                        <td>Tops</td>
                                                        <td>10.00</td>
                                                        <td>2000.00</td>
                                                        <td>500.00</td>
                                                    </tr> -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 float-md-right">
                    <div class="total-order" id="tax-details">
                        <ul>
                            <li>
                                <h4>Sub Total</h4>
                                <h5>
                                    @if ($currencyPosition === 'left')
                                    {{ $currencySymbol }} <span id="subtotal">0.00</span>
                                    @else
                                    <span id="subtotal">0.00</span> {{ $currencySymbol }}
                                    @endif
                                </h5>
                            </li>

                            <li>
                                <h4>Shipping</h4>
                                <h5>
                                    @if ($currencyPosition === 'left')
                                    {{ $currencySymbol }} <span id="shipping">0.00</span>
                                    @else
                                    <span id="shipping">0.00</span> {{ $currencySymbol }}
                                    @endif
                                </h5>
                            </li>

                            <li class="total">
                                <h4>Grand Total</h4>
                                <h5>
                                    @if ($currencyPosition === 'left')
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
            <div class="row">
                <div class="col-lg-12">
                    <a href="javascript:void(0);" class="btn btn-submit me-2">Submit</a>
                    <a href="{{ route('purchase.lists') }}" class="btn btn-cancel">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        $('.invoice_id').select2({ 
            placeholder: "Select Invoice Number",
            allowClear: true,
            width: '100%' // Ensures it fits the container
        });
    });
</script>

<script>
    let currentTaxDetails = [];

    let currencySymbol = '₹';
    let currencyPosition = 'left';
            var authToken = localStorage.getItem("authToken");

    $('#invoice_id').on('change', function() {
        let invoiceId = $(this).val();
        if (invoiceId) {
            $.ajax({
                url: '{{ url("api/get-vendor-name") }}/' + invoiceId,
                type: 'GET',
                headers: {
                        "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    $('#vendor_name').val(response.vendor_name || '');
                }
            });

            $.ajax({
                url: '{{ url("api/get-invoice-products") }}/' + invoiceId,
                type: 'GET',
                headers: {
                        "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    currentTaxDetails = response.taxDetails; // store globally
                    currencySymbol = response.currencySymbol;
                    currencyPosition = response.currencyPosition;
                    updateProductTable(response.products);
                    updateTaxDetails(currentTaxDetails);
                    updateTotals(response.totals);
                }
            });
        } else {
            $('#vendor_name').val('');
            clearProductTable();
        }
    });

    function formatCurrency(amount) {
        amount = parseFloat(amount).toFixed(2);
        return currencyPosition === 'left' ?
            `${currencySymbol} ${amount}` :
            `${amount} ${currencySymbol}`;
    }

    function updateProductTable(products) {
        let tableBody = '';
        products.forEach((item, index) => {
            const cleanSubtotal = parseFloat((item.subtotal + '').replace(/,/g, '')) || 0;
            const cleanPrice = parseFloat((item.price + '').replace(/,/g, '')) || 0;

            tableBody += `
                <tr data-purchase-id="${item.purchase_id}" data-product-id="${item.product_id}">
                    <td class="productimgname">
                            <a href="/product-view/${item.product_id}" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                                <img src="${item.image}" alt="${item.product_name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
                                <span class="pro_purhcaes">${item.product_name || 'N/A'}</span>
                            </a>
                        </td>

                    <td>${item.category}</td>
                    <td class="price">${cleanPrice}</td> 
                    <td>
                        <input type="number" class="form-control quantity-input" style="width: 80px;" step="1" min="0" max="${item.quantity}" value="${item.quantity}" />
                    </td>
                    <td class="subtotal">${formatCurrency(cleanSubtotal)}</td>
                </tr> `;
        });
        $('table tbody').html(tableBody);
        attachQuantityListeners();
    }

    function attachQuantityListeners() {
        $('.quantity-input').on('input', function() {
            let subtotal = 0;
            $('table tbody tr').each(function() {
                const $row = $(this);
                const quantity = parseInt($row.find('.quantity-input').val()) || 0; 
                $row.next(".quantity-error").remove();
                $row.find(".quantity-error1").remove();

                if (quantity < 0) {
                    // Reset to 0
                    $row.val(0);

                    // Show error message just below the input
                    $row.after('<small class="text-danger quantity-error">Quantity cannot be negative.</small>');
                }
                const price = parseFloat($row.find('.price').text()) || 0;
                const itemSubtotal = price * quantity;

                $row.find('.subtotal').text(formatCurrency(itemSubtotal));
                subtotal += itemSubtotal;
            });

            // Recalculate tax and grand total
            let taxDetails = currentTaxDetails.map(tax => {
                const taxAmount = (subtotal * parseFloat(tax.rate)) / 100;
                return {
                    name: tax.name,
                    rate: tax.rate,
                    amount: taxAmount.toFixed(2)
                };
            });

            updateTaxDetails(taxDetails);

            const taxTotal = taxDetails.reduce((sum, t) => sum + parseFloat(t.amount), 0);
            const shipping = parseFloat($('.total-order li:contains("Shipping") h5').text().replace(currencySymbol, '').trim()) || 0;
            const grandTotal = subtotal + taxTotal + shipping;

            updateTotals({
                subtotal: subtotal.toFixed(2),
                taxes: taxTotal.toFixed(2),
                shipping: shipping.toFixed(2),
                grand_total: grandTotal.toFixed(2)
            });
        });
    }

    function updateTaxDetails(taxDetails) {
        $('.total-order ul li').each(function() {
            const label = $(this).find('h4').text();
            if (!['Sub Total', 'Shipping', 'Grand Total'].includes(label)) {
                $(this).remove();
            }
        });

        let taxHtml = '';
        taxDetails.forEach(tax => {
            taxHtml += `
                                <li>
                                    <h4>${tax.name} (${tax.rate}%)</h4>
                                    <h5>${formatCurrency(tax.amount)}</h5>
                                </li>`;
        });

        $('.total-order ul li:contains("Sub Total")').after(taxHtml);
    }

    // function updateTotals(totals) {
    //     $('.total-order li').each(function() {
    //         const label = $(this).find('h4').text().trim();
    //         switch (label) {
    //             case 'Sub Total':
    //                 $(this).find('h5').text(formatCurrency(totals.subtotal));
    //                 break;
    //             case 'Shipping':
    //                 $(this).find('h5').text(formatCurrency(totals.shipping));
    //                 break;
    //             case 'Grand Total':
    //                 $(this).find('h5').text(formatCurrency(totals.grand_total));
    //                 break;
    //         }
    //     });
    // }
    function updateTotals(totals) {
        $('#subtotal').text(totals.subtotal);
        $('#shipping').text(totals.shipping);
        $('#grand-total').text(totals.grand_total);
    }

    function clearProductTable() {
        $('table tbody').html('');
        currentTaxDetails = [];
        updateTotals({
            subtotal: '0.00',
            taxes: '0.00',
            shipping: '0.00',
            grand_total: '0.00'
        });
        $('.total-order ul li').not(':contains("Sub Total"), :contains("Shipping"), :contains("Grand Total")').remove();
    }

    $('.btn-submit').on('click', function() {
        let updates = [];
         let hasNegative = false; 

        $('table tbody tr').each(function() {
            const $row = $(this);
            const purchaseId = $row.data('purchase-id');
            const productId = $row.data('product-id');
            const quantity = parseInt($row.find('.quantity-input').val()) || 0;

            // Check for negative quantity
            if (quantity < 0) {
                hasNegative = true;

                // Show error message under this input (only once)
                if ($row.find(".quantity-error1").length === 0) {
                    $row.find('.quantity-input').after('<small class="text-danger quantity-error1">Quantity cannot be negative.</small>');
                }
            } else {
                $row.find(".quantity-error1").remove(); // remove error if fixed
            }

            // if (purchaseId && productId && quantity > 0) {
            if (purchaseId && productId && !isNaN(quantity)) {
                updates.push({
                    purchase_id: purchaseId,
                    product_id: productId,
                    quantity: quantity
                });
            }
        });

        if (hasNegative) {
            Swal.fire({
                title: 'Invalid Quantity!',
                text: 'One or more quantities are negative. Please correct them before saving.',
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
            return; // ❌ stop ajax
        }

        if (updates.length === 0) {
            Swal.fire({
                title: 'Warning!',
                text: 'Please select purchase data.',
                icon: 'warning',
                confirmButtonColor: '#ff9f43',
                confirmButtonText: 'OK'
            });
            return;
        }

        $.ajax({
            url: '{{ url("api/update-quantity") }}',
            method: 'POST',
            headers: {
                        "Authorization": "Bearer " + authToken,
            },
            data: {
                _token: '{{ csrf_token() }}',
                updates: updates
            },
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Quantities updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'Failed to update quantities.',
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating quantities.',
                });
            }
        });
    });
</script>

@endpush