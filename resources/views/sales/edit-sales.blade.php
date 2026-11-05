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
        .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.gst-header .btn-back {
    background: #ff9f43;
    color: #fff;
    border: 0;
    border-radius: 7px;
    padding: 9px 18px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.gst-header .btn-back:hover {
    background: #ff8f1f;
    color: #fff;
}

.edit-pos-search {
    position: relative;
}
.edit-pos-search .pos-search-input {
    height: 42px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding-left: 14px;
    font-weight: 500;
}
.edit-pos-search .pos-search-results-box {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 4px);
    z-index: 1050;
    max-height: 320px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
}
.edit-order-table .quantity-input {
    min-width: 72px;
}
.edit-order-table .price-input {
    width: 100px;
    min-width: 90px;
}
    </style>
    <div class="content">
        <div class="page-header">
    <div class="page-title">
        <h4>Edit Order</h4>
    </div>
    <div class="gst-header mb-2 ms-auto" style="display: flex; align-items: center; gap: 25px;">
        <div class="d-flex">
            @php
                $editGstOn = in_array((string) ($sales->gst_option ?? ''), ['with', 'with_gst'], true);
            @endphp
            <label class="custom-radio-label" style="margin-right: 1rem;">
                <input type="radio" name="gst_option" id="without_gst" value="without_gst"
                    {{ !$editGstOn ? 'checked' : '' }} />
                Without GST
            </label>

            <label class="custom-radio-label">
                <input type="radio" name="gst_option" id="with_gst" value="with_gst"
                    {{ $editGstOn ? 'checked' : '' }} />
                With GST
            </label>
        </div>

        <a href="{{ route('sales.list') }}" class="btn btn-added">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
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
                    <div class="col-lg-12 col-sm-12 col-12">
                        <div class="form-group">
                            <label>Search &amp; Add Items</label>
                            <div class="edit-pos-search search_custom_product">
                                <input type="text"
                                    class="form-control pos-search-input pos-product-search-input"
                                    placeholder="Search dishes, drinks, items… (same as Order / Dine-in)"
                                    autocomplete="off">
                                <div class="pos-search-results-box list-group"></div>
                            </div>
                            <small class="text-muted">Type to search the POS catalog, then click <strong>+ Add</strong>. GST follows each category’s Apply GST setting.</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="table-responsive mb-3">
                        <table class="table edit-order-table">
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
                                    @php
                                        $rowProduct = $item->product;
                                        $rowGstApplicable = true;
                                        if ($rowProduct && $rowProduct->category && method_exists($rowProduct->category, 'appliesGst')) {
                                            $rowGstApplicable = $rowProduct->category->appliesGst();
                                        } elseif ($rowProduct && $rowProduct->category && isset($rowProduct->category->gst_applicable)) {
                                            $rowGstApplicable = (bool) $rowProduct->category->gst_applicable;
                                        }
                                        $rowTaxId = $item->tax_id ?? ($rowProduct->tax_id ?? null);
                                        $rowTaxRate = $item->tax_rate ?? optional(optional($rowProduct)->tax)->tax_rate;
                                        $rowTaxName = $item->tax_name ?? optional(optional($rowProduct)->tax)->tax_name;
                                        $rowTaxInclusive = $item->tax_inclusive ?? ($rowProduct->tax_inclusive ?? false);
                                        if (!$rowGstApplicable) {
                                            $rowTaxId = null;
                                            $rowTaxRate = null;
                                            $rowTaxName = null;
                                            $rowTaxInclusive = false;
                                        }
                                    @endphp
                                    <tr data-product-id="{{ $item->product_id }}"
                                        data-category-id="{{ $item->category_id ?? ($rowProduct->category_id ?? '') }}"
                                        data-tax-id="{{ $rowTaxId ?? '' }}"
                                        data-tax-rate="{{ $rowTaxRate !== null ? $rowTaxRate : '' }}"
                                        data-tax-name="{{ $rowTaxName ?? '' }}"
                                        data-tax-inclusive="{{ !empty($rowTaxInclusive) ? '1' : '0' }}"
                                        data-gst-applicable="{{ $rowGstApplicable ? '1' : '0' }}">
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
                                            <a href="javascript:void(0);">{{ $item->product_name ?: ($item->product->name ?? 'N/A') }}</a>
                                        </td>
                                        <td>
                                            @php $rowUnitPrice = (float) ($item->unit_price ?? $item->price ?? 0); @endphp
                                            <input type="number" name="quantities[{{ $item->product_id }}]"
                                                class="form-control quantity-input"
                                                value="{{ number_format($item->quantity, 2, '.', '') }}" step="1"
                                                min="0" data-price="{{ $rowUnitPrice }}" style="width: 80px;">
                                        </td>
                                         <td class="text-uppercase">
                                            {{ $item->product->quantityType->quantity_type ?? 'N/A' }}
                                            <input type="hidden" name="quantity_type_id[{{ $item->product_id }}]" value="{{ $item->quantity_type_id ?? ($item->product->quantity_type_id ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control price-input"
                                                value="{{ number_format($rowUnitPrice, 2, '.', '') }}" min="0" step="0.01">
                                        </td>
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
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li class="subtotal">
                                        <h4>Subtotal</h4>
                                        <h5>
                                            @if (($setting->currency_position ?? 'left') === 'right')
                                                <span
                                                    id="subtotal-display">{{ number_format($sales->order_items->sum('total_amount'), 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
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
                                            @if (($setting->currency_position ?? 'left') === 'right')
                                                <span
                                                    id="discount-amount">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="discount-amount">0.00</span>
                                            @endif
                                            )
                                        </h5>
                                    </li>

                                    <li class="after-discount">
                                        <h4>After Discount</h4>
                                        <h5>
                                            @if (($setting->currency_position ?? 'left') === 'right')
                                                <span
                                                    id="after-discount-display">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="after-discount-display">0.00</span>
                                            @endif
                                        </h5>
                                    </li>

                                    {{-- Dynamic CGST/SGST rows only for GST-applicable category lines --}}
                                    <div class="tax-section" id="edit-sales-tax-section"></div>

                                    <li class="total">
                                        <h4>Grand Total</h4>
                                        <h5>
                                            @if (($setting->currency_position ?? 'left') === 'right')
                                                <span id="grand-total">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span id="grand-total">0.00</span>
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
            const EDIT_NO_IMAGE = @json(rtrim((string) env('ImagePath'), '/') . '/admin/assets/img/product/noimage.png');
            const editGstApplyMode = @json(((optional($setting)->gst_apply_mode ?? 'product') === 'overall') ? 'overall' : 'product');
            const editOverallGstRate = @json((float) (optional($setting)->overall_gst_rate ?? 0));
            const editCurrencySymbol = @json($setting->currency_symbol ?? '₹');
            const editCurrencyPosition = @json($setting->currency_position ?? 'left');
            const editServiceTaxEnabled = @json((bool) (optional($setting)->service_tax_enabled ?? false));
            const editServiceTaxRate = @json((float) (optional($setting)->service_tax_rate ?? 0));
            const editServiceTaxName = @json((string) (optional($setting)->service_tax_name ?? 'Service Tax'));

            function escapeHtmlText(str) {
                return String(str == null ? '' : str)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }
            function escapeHtmlAttr(str) {
                return escapeHtmlText(str);
            }
            function renumberEditRows() {
                $('#product-table-body tr[data-product-id]').each(function (i) {
                    $(this).children('td').first().text(i + 1);
                });
            }
            function toggleNoProductsMessage() {
                $('#no-products-row').remove();
                if ($('#product-table-body tr[data-product-id]').length === 0) {
                    $('#product-table-body').append(
                        '<tr id="no-products-row"><td colspan="7" class="text-center">No items selected</td></tr>'
                    );
                }
            }
            function syncRowPriceFromInput($row) {
                const price = parseFloat($row.find('.price-input').val());
                if (!isNaN(price)) {
                    $row.find('.quantity-input').attr('data-price', price);
                }
            }

            // Add product to table (or bump qty if already present) — same behaviour as POS cart
            function addProductToTable(productId, productName, productImage, productPrice, productQtyType, productQtyTypeId, taxMeta) {
                taxMeta = taxMeta || {};
                productId = String(productId);
                const $existing = $('#product-table-body tr[data-product-id="' + productId + '"]');
                if ($existing.length) {
                    const $qty = $existing.find('.quantity-input');
                    $qty.val((parseFloat($qty.val()) || 0) + 1);
                    calculateAllTotals();
                    return;
                }

                const priceNum = parseFloat(productPrice) || 0;
                const gstOn = !(taxMeta.gst_applicable === false || taxMeta.gst_applicable === 0 || taxMeta.gst_applicable === '0');
                const newRow = `
            <tr data-product-id="${productId}"
                data-category-id="${taxMeta.category_id || ''}"
                data-tax-id="${taxMeta.tax_id || ''}"
                data-tax-rate="${taxMeta.tax_rate != null && taxMeta.tax_rate !== '' ? taxMeta.tax_rate : ''}"
                data-tax-name="${escapeHtmlAttr(taxMeta.tax_name || '')}"
                data-tax-inclusive="${taxMeta.tax_inclusive ? '1' : '0'}"
                data-gst-applicable="${gstOn ? '1' : '0'}">
                <td></td>
                <td class="">
                    <a class="product-img">
                        <img src="${escapeHtmlAttr(productImage || EDIT_NO_IMAGE)}" alt="product" width="40"
                            onerror="this.onerror=null;this.src='${escapeHtmlAttr(EDIT_NO_IMAGE)}';">
                    </a>
                    <a href="javascript:void(0);">${escapeHtmlText(productName)}</a>
                </td>
                <td>
                    <input type="number"
                           name="quantities[${productId}]"
                           class="form-control quantity-input"
                           value="1"
                           step="1"
                           min="0"
                           data-price="${priceNum}"
                           style="width: 80px;">
                </td>
                <td class="text-uppercase">${escapeHtmlText(productQtyType || 'N/A')}
                  <input type="hidden" name="quantity_type_id[${productId}]" value="${escapeHtmlAttr(productQtyTypeId || '')}">
                </td>
                <td>
                    <input type="number" class="form-control price-input" value="${priceNum.toFixed(2)}" min="0" step="0.01">
                </td>
                <td>
                    <span class="total-amount">${priceNum.toFixed(2)}</span>
                </td>
                <td>
                    <a href="javascript:void(0);" class="delete-set">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="svg">
                    </a>
                </td>
            </tr>`;

                $('#no-products-row').remove();
                $('#product-table-body').append(newRow);
                renumberEditRows();
                calculateAllTotals();
            }

            // ── POS-style live product search ─────────────────────────────
            var editSearchTimer = null;
            var editSearchReqId = 0;

            function renderEditSearchResults($resultBox, products) {
                $resultBox.empty();
                if (!products || !products.length) {
                    $resultBox.html(`
                        <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                            <span style="font-size:13px;color:#64748b;">No results found</span>
                            <button type="button" class="btn-close-search btn btn-sm btn-light" title="Close"><i class="fas fa-times"></i></button>
                        </div>`).show();
                    return;
                }
                $resultBox.append(`
                    <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3 bg-white" style="border-bottom:2px solid #f1f5f9;position:sticky;top:0;z-index:10;">
                        <span class="fw-bold" style="font-size:13.5px;color:#1e293b;">Products (${products.length})</span>
                        <button type="button" class="btn-close-search btn btn-sm btn-light" title="Close"><i class="fas fa-times"></i></button>
                    </div>`);
                products.forEach(function (product) {
                    var rawPrice = product.raw_price != null ? product.raw_price : (parseFloat(String(product.price).replace(/[^0-9.-]+/g, '')) || 0);
                    var displayPrice = product.price;
                    if (typeof displayPrice === 'number') {
                        displayPrice = editCurrencyPosition === 'right'
                            ? (Number(displayPrice).toFixed(2) + editCurrencySymbol)
                            : (editCurrencySymbol + Number(displayPrice).toFixed(2));
                    }
                    var img = product.image || product.image_url || EDIT_NO_IMAGE;
                    $resultBox.append(`
                        <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3" style="border-bottom:1px solid #f1f5f9;">
                            <div class="d-flex align-items-center gap-2" style="flex:1;min-width:0;">
                                <img src="${escapeHtmlAttr(img)}" alt="" class="rounded" style="width:34px;height:34px;object-fit:cover;flex-shrink:0;border:1px solid #f1f5f9;"
                                    onerror="this.onerror=null;this.src='${escapeHtmlAttr(EDIT_NO_IMAGE)}';">
                                <div style="min-width:0;">
                                    <strong style="font-size:13px;color:#1e293b;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtmlText(product.name || 'N/A')}</strong>
                                    <small style="font-size:11.5px;color:#64748b;font-weight:600;">Price: ${escapeHtmlText(displayPrice || 'N/A')}</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary js-edit-add-product ms-2"
                                style="border-radius:6px;padding:4px 14px;font-weight:600;font-size:12px;flex-shrink:0;"
                                data-id="${product.id}"
                                data-name="${escapeHtmlAttr(product.name)}"
                                data-raw-price="${rawPrice}"
                                data-image="${escapeHtmlAttr(img)}"
                                data-quantity_type="${escapeHtmlAttr(product.quantity_type || '')}"
                                data-quantity_type_id="${escapeHtmlAttr(product.quantity_type_id || '')}"
                                data-category-id="${product.category_id || product.categoryId || ''}"
                                data-tax-id="${product.tax_id || ''}"
                                data-tax-rate="${product.tax_rate != null ? product.tax_rate : ''}"
                                data-tax-name="${escapeHtmlAttr(product.tax_name || '')}"
                                data-tax-inclusive="${product.tax_inclusive ? '1' : '0'}"
                                data-gst-applicable="${product.gst_applicable === false || product.gst_applicable === 0 || product.gst_applicable === '0' ? '0' : '1'}">+ Add</button>
                        </div>`);
                });
                $resultBox.show();
            }

            function searchEditCatalogFallback(query, $resultBox, reqId) {
                var authToken = localStorage.getItem('authToken');
                $.ajax({
                    url: '/api/getProductsByCategory/all',
                    type: 'GET',
                    dataType: 'json',
                    headers: authToken ? { Authorization: 'Bearer ' + authToken } : {},
                    success: function (response) {
                        if (reqId !== editSearchReqId) return;
                        var q = String(query).toLowerCase();
                        var list = (response && response.status && Array.isArray(response.data)) ? response.data : [];
                        var matched = list.filter(function (p) {
                            return String(p.name || '').toLowerCase().indexOf(q) !== -1;
                        }).slice(0, 20);
                        renderEditSearchResults($resultBox, matched);
                    },
                    error: function () {
                        if (reqId !== editSearchReqId) return;
                        $resultBox.html('<div class="list-group-item py-2 px-3" style="font-size:13px;color:#ef4444;">Search failed. Please try again.</div>').show();
                    }
                });
            }

            function executeEditProductSearch($input, $resultBox) {
                const query = $input.val().trim();
                if (query.length < 1) {
                    $resultBox.hide().empty();
                    return;
                }
                var reqId = ++editSearchReqId;
                $resultBox.html('<div class="list-group-item py-2 px-3" style="font-size:13px;color:#64748b;">Searching…</div>').show();
                $.ajax({
                    url: @json(route('users.ajaxSearch')),
                    method: 'GET',
                    data: { query: query },
                    dataType: 'json',
                    success: function (data) {
                        if (reqId !== editSearchReqId) return;
                        var products = (data && data.products) ? data.products : [];
                        if (products.length) {
                            renderEditSearchResults($resultBox, products);
                            return;
                        }
                        searchEditCatalogFallback(query, $resultBox, reqId);
                    },
                    error: function () {
                        if (reqId !== editSearchReqId) return;
                        searchEditCatalogFallback(query, $resultBox, reqId);
                    }
                });
            }

            $(document).on('input', '.pos-product-search-input', function () {
                const $input = $(this);
                const $resultBox = $input.siblings('.pos-search-results-box');
                if (!$resultBox.length) return;
                clearTimeout(editSearchTimer);
                editSearchTimer = setTimeout(function () {
                    executeEditProductSearch($input, $resultBox);
                }, 250);
            });

            $(document).on('click', '.btn-close-search', function (e) {
                e.preventDefault();
                $(this).closest('.pos-search-results-box').hide().empty();
                $('.pos-product-search-input').val('');
            });

            $(document).on('click', '.js-edit-add-product', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const taxRateAttr = $btn.attr('data-tax-rate');
                addProductToTable(
                    $btn.data('id'),
                    $btn.attr('data-name') || $btn.data('name'),
                    $btn.attr('data-image'),
                    parseFloat($btn.attr('data-raw-price')) || 0,
                    $btn.attr('data-quantity_type') || '',
                    $btn.attr('data-quantity_type_id') || '',
                    {
                        category_id: $btn.attr('data-category-id') || '',
                        tax_id: $btn.attr('data-tax-id') || '',
                        tax_rate: (taxRateAttr === '' || taxRateAttr == null) ? null : parseFloat(taxRateAttr),
                        tax_name: $btn.attr('data-tax-name') || '',
                        tax_inclusive: $btn.attr('data-tax-inclusive') === '1',
                        gst_applicable: $btn.attr('data-gst-applicable') !== '0'
                    }
                );
                $('.pos-search-results-box').hide().empty();
                $('.pos-product-search-input').val('').focus();
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('.search_custom_product').length) {
                    $('.pos-search-results-box').hide();
                }
            });

            // Calculate all totals including discount and taxes
            // GST: only lines whose category has Apply GST On; product tax or overall rate (same as POS)
            function calculateAllTotals() {
                let subtotal = 0;
                const lines = [];

                $('#product-table-body tr[data-product-id]').each(function() {
                    const $row = $(this);
                    syncRowPriceFromInput($row);
                    const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                    const price = parseFloat($row.find('.quantity-input').attr('data-price')) || 0;
                    const rowTotal = quantity * price;

                    $row.find('.total-amount').text(rowTotal.toFixed(2));
                    subtotal += rowTotal;

                    lines.push({
                        line_total: rowTotal,
                        tax_id: $row.attr('data-tax-id') || null,
                        tax_rate: $row.attr('data-tax-rate'),
                        tax_name: $row.attr('data-tax-name') || 'GST',
                        tax_inclusive: $row.attr('data-tax-inclusive') === '1',
                        gst_applicable: !($row.attr('data-gst-applicable') === '0')
                    });
                });

                $('#subtotal-display').text(subtotal.toFixed(2));

                const discountPercentage = parseFloat($('#discount-input').val()) || 0;
                const discountAmount = subtotal * (discountPercentage / 100);
                $('#discount-percent').text(discountPercentage.toFixed(2));
                $('#discount-amount').text(discountAmount.toFixed(2));

                const afterDiscount = subtotal - discountAmount;
                $('#after-discount-display').text(afterDiscount.toFixed(2));

                const gstOption = $('input[name="gst_option"]:checked').val();
                let totalTax = 0;
                const $taxSection = $('#edit-sales-tax-section');
                $taxSection.empty();

                function appendTaxRow(label, amount) {
                    const amt = Math.round((parseFloat(amount) || 0) * 100) / 100;
                    if (amt <= 0) return;
                    const moneyHtml = editCurrencyPosition === 'right'
                        ? ('<span class="tax-amount">' + amt.toFixed(2) + '</span>' + editCurrencySymbol)
                        : (editCurrencySymbol + '<span class="tax-amount">' + amt.toFixed(2) + '</span>');
                    $taxSection.append(
                        '<li><h4>' + label + '</h4><h5>' + moneyHtml + '</h5></li>'
                    );
                    totalTax += amt;
                }

                function renderCgstSgstFromAgg(rateAmountMap) {
                    const splitAgg = {};
                    Object.keys(rateAmountMap).forEach(function (rateKey) {
                        const fullRate = parseFloat(rateKey) || 0;
                        const amount = parseFloat(rateAmountMap[rateKey]) || 0;
                        if (amount <= 0 && fullRate <= 0) return;
                        const halfRate = Math.round((fullRate / 2) * 10000) / 10000;
                        const cgstAmt = Math.round((amount / 2) * 100) / 100;
                        const sgstAmt = Math.round((amount - cgstAmt) * 100) / 100;
                        const hk = String(halfRate);
                        if (!splitAgg[hk]) splitAgg[hk] = { rate: halfRate, cgst: 0, sgst: 0 };
                        splitAgg[hk].cgst += cgstAmt;
                        splitAgg[hk].sgst += sgstAmt;
                    });
                    Object.keys(splitAgg).forEach(function (hk) {
                        const row = splitAgg[hk];
                        const rateLabel = String(row.rate).replace(/\.00$/, '').replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
                        appendTaxRow('CGST (' + rateLabel + '%)', row.cgst);
                        appendTaxRow('SGST (' + rateLabel + '%)', row.sgst);
                    });
                }

                if ((gstOption === 'with_gst' || gstOption === 'with') && subtotal > 0) {
                    $taxSection.show();
                    if (editGstApplyMode === 'overall') {
                        const rate = parseFloat(editOverallGstRate) || 0;
                        let gstBase = 0;
                        lines.forEach(function (line) {
                            if (!line.gst_applicable) return;
                            gstBase += line.line_total * (1 - (discountPercentage / 100));
                        });
                        const rateMap = {};
                        rateMap[String(rate)] = gstBase * (rate / 100);
                        renderCgstSgstFromAgg(rateMap);
                    } else {
                        const rateMap = {};
                        lines.forEach(function (line) {
                            if (!line.gst_applicable) return;
                            const taxId = line.tax_id;
                            const taxRatePct = parseFloat(line.tax_rate);
                            if (!taxId || isNaN(taxRatePct)) return;
                            const lineShare = line.line_total * (1 - (discountPercentage / 100));
                            let lineTax = 0;
                            if (line.tax_inclusive) {
                                const base = lineShare / (1 + (taxRatePct / 100));
                                lineTax = lineShare - base;
                            } else {
                                lineTax = lineShare * (taxRatePct / 100);
                            }
                            const rk = String(taxRatePct);
                            rateMap[rk] = (rateMap[rk] || 0) + lineTax;
                        });
                        renderCgstSgstFromAgg(rateMap);
                    }

                    if (editServiceTaxEnabled && (parseFloat(editServiceTaxRate) || 0) > 0 && afterDiscount > 0) {
                        const svcAmt = Math.round((afterDiscount * (parseFloat(editServiceTaxRate) / 100)) * 100) / 100;
                        const svcRateLabel = String(parseFloat(editServiceTaxRate)).replace(/\.00$/, '').replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
                        appendTaxRow((editServiceTaxName || 'Service Tax') + ' (' + svcRateLabel + '%)', svcAmt);
                    }
                } else {
                    $taxSection.hide();
                    totalTax = 0;
                }

                const grandTotal = afterDiscount + totalTax;
                $('#grand-total').text(grandTotal.toFixed(2));
            }

            // Bind events for recalculation
            $(document).on('input', '.quantity-input, .price-input, #discount-input', function() {
                calculateAllTotals();
            });
            $(document).on('change', 'input[name="gst_option"]', function() {
                calculateAllTotals();
            });

            $(document).on('click', '.delete-set', function() {
                $(this).closest('tr').remove();
                renumberEditRows();
                toggleNoProductsMessage();
                calculateAllTotals();
            });

            // Initial calculation on page load
            renumberEditRows();
            calculateAllTotals();

            // Form submission handler
            $(document).on("click", '.btn-submit', function(e) {
                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                 const createdBy = "{{ auth()->id() }}";
                e.preventDefault();

                const productIds = [];
                const quantities = {};
                const quantityTypeIds = {};
                const unitPrices = {};
                $('#product-table-body tr[data-product-id]').each(function () {
                    const $row = $(this);
                    const productId = String($row.data('product-id'));
                    if (!productId) return;
                    productIds.push(productId);
                    syncRowPriceFromInput($row);
                    quantities[productId] = $row.find('.quantity-input').val();
                    quantityTypeIds[productId] = $row.find('input[name^="quantity_type_id"]').val() || '';
                    unitPrices[productId] = parseFloat($row.find('.quantity-input').attr('data-price')) || 0;
                });

                if (!productIds.length) {
                    Swal.fire({
                        title: 'No items',
                        text: 'Add at least one product before updating the order.',
                        icon: 'warning',
                        confirmButtonColor: '#ff9f43'
                    });
                    return;
                }

                const formData = {
                    customer_id: $('#customer_name').val(),
                    update_id: $('#update_selse_id').val(),
                    customer_phone: $('#customer_phone').val(),
                    product_ids: productIds,
                    quantities: quantities,
                    quantity_type_id: quantityTypeIds,
                    unit_prices: unitPrices,
                    discount: $('#discount-input').val(),
                    payment_method: $('select[name="payment_method"]').val(),
                    status: $('select[name="status"]').val(),
                    order_type: $('#order_type_select').val(),
                    assigned_staff_id: $('#assigned_staff_id').val(),
                    grand_total: $('#grand-total').text().replace(/[^0-9.-]+/g, '').trim(),
                    gst_option: $('input[name="gst_option"]:checked').val(),
                    selectedSubAdminId: selectedSubAdminId || null,
                    created_by: createdBy,
                };


          

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
