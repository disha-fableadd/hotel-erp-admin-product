@extends('layout.app')

@section('title', 'Edit Invoice')

@section('content')
    <style>
        .add-row-btn {
            margin-top: 2rem;
        }

        @media screen and (max-width: 768px) {
            .add-row-btn {
                margin-top: 0;
                margin-bottom: 1rem;
            }

            .add-row {
                width: 100%;
                text-align: center;
            }

            .remove-row {
                width: 100%;
                text-align: center;
            }

            .form-group {
                margin-bottom: 10px !important
            }
        }
    </style>
    @php
        $settings = \DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';
        $productsArray = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'price' => $product->price,
            ];
        });
        $productsJson = json_encode($productsArray);
    @endphp
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Invoice</h4>
            </div>
            <div class="gst-header">

                <label class="custom-radio-label">
                    <input type="radio" name="gst_option" id="without_gst" value="without_gst" />
                    Without GST
                </label>

                <label class="custom-radio-label">
                    <input type="radio" name="gst_option" id="with_gst" value="with_gst" />
                    With GST
                </label>

            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>Choose Customer/Vendor</label>
                            <select id="choose_people" name="choose_people" class="form-control">
                                <option value="">Select Customer/Vendor</option>
                                <option value="customer">Customer</option>
                                <option value="vendor">Vendor</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-12 vendor-section" style="display: none;">
                        <div class="form-group">
                            <label>Vendor Name</label>
                            <select id="vendor_name" name="vendor_id" class="form-control select2 vendor-select">
                                <option value="">Select Vendor</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" data-phone="{{ $vendor->phone }}">
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-12 customer-section" style="display: none;">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <select id="customer_name" name="customer_id" class="form-control select2 customer-select">
                                <option value="">Select Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-phone="{{ $customer->phone }}">
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="row form-row">
                        <div class="col-lg-3 col-sm-12 col-6">
                            <div class="form-group">
                                <label>Category Name</label>
                                <select id="category_name" name="category_name[]"
                                    class="form-control select2 category-select">
                                    <option value="">Category Name</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" data-price="{{ $category->price }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="error text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-12 col-6">
                            <div class="form-group">
                                <label>Product Name</label>
                                <select id="product_name" name="product_name[]" class="form-control select2 product-select"
                                    disabled>
                                    <option value="">Product Name</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                            data-category="{{ $product->category_id }}">
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="error text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-12 col-6">
                            <div class="form-group">
                                <label>Product Price</label>
                                <input type="number" name="price[]" class="form-control price-input"
                                    placeholder="Enter Price" min="0"
                                    oninput="this.value = this.value < 0 ? 0 : this.value">
                                <span class="error text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-12 col-6">
                            <div class="form-group">
                                <label>Product Quantity</label>
                                <input type="number" name="quantity[]" class="form-control quantity-input"
                                    placeholder="Enter Quantity" value="0" min="0"
                                    oninput="this.value = this.value < 0 ? 0 : this.value">
                                <span class="error text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Total Amount</label>
                                <input type="text" name="total[]" class="form-control total-input" placeholder="0"
                                    min="0" readonly>
                            </div>
                        </div>
                        <div class="col-lg-1 col-sm-12 add-row-btn">
                            <button type="button" class="btn btn-success add-row">+</button>
                        </div>
                    </div>

                    <!-- Placeholder for additional rows -->
                    <div id="form-container"></div>
                </div>

                <div class="row">
                    <!-- <div class="col-lg-3 col-sm-6 col-12">
                                                                                                                                                                                            <div class="form-group">
                                                                                                                                                                                                <label>Discount</label>
                                                                                                                                                                                                <input type="number" name="discount" id="discount" class="form-control" placeholder="0.00%">
                                                                                                                                                                                                <span class="error text-danger"></span>
                                                                                                                                                                                            </div>
                                                                                                                                                                                        </div> -->
                    <div class="col-lg-3 col-sm-6 col-12" id="shipping-section">
                        <div class="form-group">
                            <label>Shipping</label>
                            <input type="number" name="shipping" id="shipping" class="form-control"
                                placeholder="0.00" min="0">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-12" id="discount-section">
                        <div class="form-group">
                            <label>Discount</label>
                            <input type="number" name="discount" id="discount" class="form-control"
                                placeholder="0.00" min="0">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    {{-- <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Purchase Status</label>
                            <select name="status" class="form-control">
                                <option value="">Choose Status</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-control payment_status">
                                <option value="">Choose Status</option>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div> --}}
                </div>

                <div class="row">
                    <div class="col-lg-12 float-md-right">
                        <div class="total-order">
                            <ul>
                                <li>
                                    <h4>Total Product Amount</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="total-product-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="total-product-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>
                                <div id="tax-section">
                                    @foreach ($taxes as $tax)
                                        <li>
                                            <h4>{{ $tax->tax_name }}</h4>
                                            <h5 style="color: green;">
                                                @if ($currencyPosition === 'right')
                                                    <span class="tax-amount" id="tax-{{ $tax->id }}"
                                                        data-rate="{{ $tax->tax_rate }}">0.00</span>{{ $currencySymbol }}
                                                @else
                                                    {{ $currencySymbol }}<span class="tax-amount"
                                                        id="tax-{{ $tax->id }}"
                                                        data-rate="{{ $tax->tax_rate }}">0.00</span>
                                                @endif
                                                ({{ $tax->tax_rate }}%)
                                            </h5>
                                        </li>
                                    @endforeach
                                </div>

                                <li id="discount-amount-li" style="display: none;">
                                    <h4>Discount</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            -<span id="discount-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            -{{ $currencySymbol }}<span id="discount-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li id="shipping-amount-li" style="display: none;">
                                    <h4>Shipping</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="shipping-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="shipping-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li class="total">
                                    <h4>Grand Total</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="grand-total">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="grand-total">0.00</span>
                                        @endif
                                    </h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <a href="javascript:void(0);" class="btn btn-submit me-2">Submit</a>
                    <a href="{{ route('custom_invoice.lists') }}" class="btn btn-cancel">Cancel</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const products = @json($productsArray);
    </script>
    <script>
        $(document).ready(function() {
            $("#choose_people").on("change", function() {
                let selected = $(this).val();

                if (selected === "vendor") {
                    $(".vendor-section").show();
                    $(".customer-section").hide();
                } else if (selected === "customer") {
                    $(".vendor-section").hide();
                    $(".customer-section").show();
                } else {
                    $(".vendor-section").hide();
                    $(".customer-section").hide();
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            // ✅ Extract invoice ID from URL
            let urlSegments = window.location.pathname.split("/");
            let invoiceId = urlSegments[urlSegments.length - 1]; // Get last segment

            if (!isNaN(invoiceId) && invoiceId > 0) {
                loadPurchaseData(invoiceId); // Fetch purchase details
            }


            // Initialize Select2
            // $(".select2, .category-select").select2({
            //     tags: true,
            // });
            $(".vendor-select,.customer-select,.product-select,.category-select").select2({
                tags: true,
            });

            // Add new form row dynamically
            $(document).on("click", ".add-row", function() {
                let row = `
                            <div class="row form-row">

                                <div class="col-lg-3 col-sm-12 col-6">
                                    <div class="form-group">
                                        <label>Category Name</label>
                                        <select name="category_name[]" class="form-control category-select">
                                            <option value="">Category Name</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" data-price="{{ $category->price }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="error text-danger"></span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-6">
                                    <div class="form-group">
                                        <label>Product Name</label>
                                        <select name="product_name[]" class="form-control select2 product-select" disabled>
                                            <option value="">Product Name</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-category="{{ $product->category_id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="error text-danger"></span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-6">
                                    <div class="form-group">
                                        <label>Product Price</label>
                                        <input type="number" name="price[]" class="form-control price-input" placeholder="Enter Price" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                        <span class="error text-danger"></span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-6">
                                    <div class="form-group">
                                        <label>Product Quantity</label>
                                        <input type="number" name="quantity[]" class="form-control quantity-input" placeholder="Enter Quantity" value="0" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                        <span class="error text-danger"></span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-12">
                                    <div class="form-group">
                                        <label>Total Amount</label>
                                        <input type="text" name="total[]" class="form-control total-input" placeholder="0" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-1 col-sm-12 add-row-btn">
                                    <button type="button" class="btn btn-danger remove-row">-</button>
                                </div>
                            </div>`;

                let newRow = $(row).appendTo("#form-container");

                // Reinitialize Select2 for dynamically added elements
                newRow.find(".select2, .category-select").select2({
                    tags: true
                });
            });

            // Remove form row dynamically
            $(document).on("click", ".remove-row", function() {
                $(this).closest(".form-row").remove();
                calculateTotal();
            });

            $(document).ready(function() {

                $('.category-select').on('change', function() {
                    var selectedCategory = $(this).val();
                    var $productDropdown = $(this).closest('.col-lg-3, .col-sm-12').siblings().find(
                        '.product-select');

                    // Clear current options
                    $productDropdown.empty();

                    if (selectedCategory) {
                        // Enable dropdown when category is selected
                        $productDropdown.prop('disabled', false);

                        // Add default placeholder
                        $productDropdown.append('<option value="">Product Name</option>');

                        // Filter products by category
                        var filteredProducts = products.filter(p => p.category_id ==
                            selectedCategory);

                        filteredProducts.forEach(function(product) {
                            $productDropdown.append(
                                `<option value="${product.id}" data-price="${product.price}">${product.name}</option>`
                            );
                        });
                    } else {
                        // No category selected, disable product dropdown and add placeholder
                        $productDropdown.prop('disabled', true);
                        $productDropdown.append('<option value="">Product Name</option>');
                    }

                    // Refresh Select2
                    $productDropdown.select2('destroy').select2();
                });
            });

            $(document).on("change", ".product-select", function() {
                let selectedOption = $(this).find("option:selected");
                let price = selectedOption.data("price") || 0;
                let categoryId = selectedOption.data("category") || ""; // Get category ID

                let row = $(this).closest(".form-row");

                // Set price in input field
                row.find(".price-input").val(parseInt(price)).trigger("input");

            });

            $(document).ready(function() {
                // Delegated change event for category-select
                $(document).on('change', '.category-select', function() {
                    var selectedCategory = $(this).val();

                    var $row = $(this).closest('.form-row'); // find the row of this select
                    var $productDropdown = $row.find('.product-select');

                    // Clear current options
                    $productDropdown.empty();

                    if (selectedCategory) {
                        $productDropdown.prop('disabled', false);

                        $productDropdown.append('<option value="">Product Name</option>');

                        // Filter products by category
                        var filteredProducts = products.filter(p => p.category_id ==
                            selectedCategory);

                        filteredProducts.forEach(function(product) {
                            $productDropdown.append(
                                `<option value="${product.id}" data-price="${product.price}">${product.name}</option>`
                            );
                        });
                    } else {
                        $productDropdown.prop('disabled', true);
                        $productDropdown.append('<option value="">Product Name</option>');
                    }

                    // Refresh Select2
                    $productDropdown.select2('destroy').select2();
                });

                // Delegated change event for product-select
                $(document).on('change', '.product-select', function() {
                    let selectedOption = $(this).find("option:selected");
                    let price = selectedOption.data("price") || 0;

                    let $row = $(this).closest('.form-row');
                    $row.find(".price-input").val(parseInt(price)).trigger("input");
                });
            });

            $(document).on("input", ".price-input, .quantity-input", function() {
                let row = $(this).closest(".form-row");
                let price = parseFloat(row.find(".price-input").val()) || 0;
                let quantity = parseInt(row.find(".quantity-input").val()) || 0;
                let total = price * quantity;
                row.find(".total-input").val(total.toFixed(2));
                calculateTotal();
            });

            // function calculateTotal() {
            //     let totalAmount = 0;
            //     let products = [];

            //     $(".form-row").each(function () {
            //         let productId = $(this).find(".product-select").val();
            //         let categoryId = $(this).find(".category-select").val();
            //         let price = parseFloat($(this).find(".price-input").val()) || 0;
            //         let quantity = parseInt($(this).find(".quantity-input").val()) || 0;
            //         let total = price * quantity;

            //         if (productId) {
            //             products.push({
            //                 id: productId,
            //                 category_id: categoryId,
            //                 price: price,
            //                 quantity: quantity,
            //                 total: total,
            //             });
            //         }

            //         totalAmount += total;
            //         $(this).find(".total-input").val(total.toFixed(2));
            //     });

            //     // **Update Total Product Amount**
            //     $("#total-product-amount").text(totalAmount.toFixed(2));

            //     // ✅ Only calculate discount if visible
            //     let discountAmount = 0;
            //     let discountPercent = 0;
            //     if ($("#discount-section").is(":visible")) {
            //         discountPercent = parseFloat($("#discount").val()) || 0;
            //         discountAmount = (totalAmount * discountPercent) / 100;
            //         $("#discount-amount").text(discountAmount.toFixed(2)); // update UI
            //     }

            //     // ✅ Only calculate shipping if visible
            //     let shipping = 0;
            //     if ($("#shipping-section").is(":visible")) {
            //         shipping = parseFloat($("#shipping").val()) || 0;
            //         $("#shipping-amount").text(shipping.toFixed(2)); // update UI
            //     }

            //     // **Calculate Multiple Taxes**
            //     let totalTaxAmount = 0;
            //     $(".tax-amount").each(function () {
            //         let taxRate = parseFloat($(this).data("rate")) || 0;
            //         let taxAmount = (totalAmount * taxRate) / 100;
            //         totalTaxAmount += taxAmount;
            //         $(this).text(taxAmount.toFixed(2)); // update UI
            //     });

            //     // ✅ Calculate grand total with dynamic discount & shipping
            //     let grandTotal = totalAmount + shipping - discountAmount + totalTaxAmount;
            //     $("#grand-total").text(grandTotal.toFixed(2));

            //     return {
            //         products: products,
            //         totalProductAmount: totalAmount,
            //         totalAmount: totalAmount,
            //         discount: discountPercent,
            //         discountAmount: discountAmount,
            //         taxAmount: totalTaxAmount,
            //         shipping: shipping,
            //         grandTotal: grandTotal,
            //     };
            // }
            function calculateTotal() {
                let totalAmount = 0;
                let products = [];

                // Loop through each product row
                $(".form-row").each(function() {
                    let productId = $(this).find(".product-select").val();
                    let categoryId = $(this).find(".category-select").val();
                    let price = parseFloat($(this).find(".price-input").val()) || 0;
                    let quantity = parseInt($(this).find(".quantity-input").val()) || 0;
                    let total = price * quantity;

                    if (productId) {
                        products.push({
                            id: productId,
                            category_id: categoryId,
                            price: price,
                            quantity: quantity,
                            total: total,
                        });
                    }

                    totalAmount += total;
                    $(this).find(".total-input").val(total.toFixed(2));
                });

                // Update total product amount
                $("#total-product-amount").text(totalAmount.toFixed(2));

                // Discount & shipping
                let discountPercent = 0;
                let discountAmount = 0;
                let shipping = 0;

                if ($("#discount-section").is(":visible")) {
                    discountPercent = parseFloat($("#discount").val()) || 0;
                    discountAmount = (totalAmount * discountPercent) / 100;
                    $("#discount-amount-li").show();
                    $("#discount-amount").text(discountAmount.toFixed(2));
                } else {
                    discountAmount = 0;
                    $("#discount-amount").text("0.00");
                    $("#discount-amount-li").hide();
                }

                if ($("#shipping-section").is(":visible")) {
                    shipping = parseFloat($("#shipping").val()) || 0;
                    $("#shipping-amount-li").show();
                } else {
                    shipping = 0;
                    $("#shipping-amount-li").hide();
                }

                // After discount (this is the taxable base)
                let afterDiscount = totalAmount - discountAmount;

                // GST Option
                let gstOption = $("input[name='gst_option']:checked").val();
                let totalTaxAmount = 0;
                console.log(gstOption);


                if (gstOption === "with_gst") {
                    // show tax rows
                    $("#tax-section").show();

                    // calculate tax on afterDiscount
                    $(".tax-amount").each(function() {
                        let taxRate = parseFloat($(this).data("rate")) || 0;
                        let taxAmount = (afterDiscount * taxRate) / 100; // <-- key fix: use afterDiscount
                        $(this).text(taxAmount.toFixed(2));
                        totalTaxAmount += taxAmount;
                    });
                } else {
                    // hide tax rows and reset values
                    $("#tax-section").hide();
                    // $(".tax-amount").text("0.00");
                    totalTaxAmount = 0;
                }

                // Grand total = after-discount + shipping + taxes
                let grandTotal = afterDiscount + shipping + totalTaxAmount;

                $("#grand-total").text(grandTotal.toFixed(2));
                $("#shipping-amount").text(shipping.toFixed(2));

                return {
                    products: products,
                    totalProductAmount: totalAmount,
                    discount: discountPercent,
                    discountAmount: discountAmount,
                    taxAmount: totalTaxAmount,
                    shipping: shipping,
                    grandTotal: grandTotal,
                    gstOption: gstOption
                };
            }

            // $(document).ready(function() {
            //     // ✅ Handle initial state on page load
            //     // let gstOption = $("input[name='gst_option']:checked").val();
            //     // if (gstOption === "with_gst") {
            //     //     $("#tax-section").show();
            //     // } else {
            //     //     $("#tax-section").hide();
            //     // }

            //     // ✅ Handle state change when user clicks
            //     $(document).on("change", "input[name='gst_option']", function() {
            //         let gstOption = $("input[name='gst_option']:checked").val();

            //         if (gstOption === "with_gst") {
            //              calculateTotal();
            //             $("#tax-section").show(); // show all taxes
            //         } else {
            //             $("#tax-section").hide(); // hide tax section
            //         }


            //     });
            // });
            $(document).on("change", "input[name='gst_option']", function() {
                calculateTotal(); // this will already handle showing/hiding & calculation
            });

            // Update calculation when any relevant input changes
            $(document).on("input", "#discount, #shipping, .price-input, .quantity-input", function() {
                calculateTotal();
            });
            calculateTotal();



            function validateForm() {
                let isValid = true;

                // Clear previous errors
                $(".error").text("");



                // Validate Product Fields only if category is selected
                $(".form-row").each(function() {
                    let productSelect = $(this).find(".product-select");
                    let categorySelect = $(this).find(".category-select");
                    let priceInput = $(this).find(".price-input");
                    let quantityInput = $(this).find(".quantity-input");

                    let categoryVal = categorySelect.val();

                    // 🌟 If category is selected, validate product, price, and quantity
                    if (categoryVal !== "") {
                        if (productSelect.val() === "") {
                            productSelect.closest(".form-group").find(".error").text(
                                "Product is required.");
                            isValid = false;
                        }
                        if (priceInput.val().trim() === "" || parseFloat(priceInput.val()) <= 0) {
                            priceInput.closest(".form-group").find(".error").text(
                                "Valid price is required.");
                            isValid = false;
                        }
                        if (quantityInput.val().trim() === "" || parseInt(quantityInput.val()) <= 0) {
                            quantityInput.closest(".form-group").find(".error").text(
                                "Valid quantity is required.");
                            isValid = false;
                        }
                    }
                });

                // Validate Discount & Shipping
                let discount = parseFloat($("#discount").val()) || 0;
                let shipping = parseFloat($("#shipping").val()) || 0;

                if (discount < 0 || discount > 100) {
                    $("#discount").closest(".form-group").find(".error").text("Discount must be between 0-100%.");
                    isValid = false;
                }
                if (shipping < 0) {
                    $("#shipping").closest(".form-group").find(".error").text("Shipping cost cannot be negative.");
                    isValid = false;
                }

                // Validate Status
                if ($("select[name='status']").val() === "") {
                    $("select[name='status']").closest(".form-group").find(".error").text(
                        "Please select a status.");
                    isValid = false;
                }

                return isValid;
            }
            // ✅ Function to load purchase data
            function loadPurchaseData(invoiceId) {
                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: "/api/custom_invoice_get/" + invoiceId,
                    type: "GET",
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        console.log(response);
                        let vendorId = response.data.vendor_id ?? '';
                        let customerId = response.data.customer_id ?? '';
                        let taxes = response.taxes;
                        // ✅ If vendor
                        if (vendorId) {
                            $('#choose_people').val('vendor').trigger('change');
                            $('.vendor-section').show();
                            $('.customer-section').hide();

                            $('#vendor_name').val(vendorId).trigger('change').prop('disabled', true);
                            $('#choose_people').prop('disabled', true);

                            // Show shipping section, hide discount section
                            $('#shipping-section').show();
                            $('#discount-section').hide();

                            // Show shipping in summary, hide discount in summary
                            $('#shipping-amount-li').show();
                            let shippingcost = parseFloat(response.data.shipping) || 0;
                            $('#shipping').val(shippingcost.toFixed(2));
                            $('#discount-amount-li').hide();

                            if (response.data.gst_option) {
                                $("input[name='gst_option'][value='" + response.data.gst_option + "']")
                                    .prop(
                                        "checked",
                                        true).trigger('change');
                            }
                            taxes.forEach(function(tax) {
                                $("#tax-" + tax.tax_id).text(parseFloat(tax.amount).toFixed(2));
                            });
                        } else if (customerId) {
                            $('#choose_people').val('customer').trigger('change');
                            $('.customer-section').show();
                            $('.vendor-section').hide();

                            $('#customer_name').val(customerId).trigger('change').prop('disabled',
                            true);
                            $('#choose_people').prop('disabled', true);

                            // Show discount section, hide shipping section
                            $('#discount-section').show();
                            $('#shipping-section').hide();

                            // Show discount in summary, hide shipping in summary
                            $('#discount-amount-li').show();
                            $('#shipping-amount-li').hide();

                            // ✅ Calculate and show discount amount as number
                            let discountPercent = parseFloat(response.data.discount) || 0;

                            // ✅ Show discount amount in the discount input field
                            $('#discount').val(discountPercent.toFixed(2));

                            if (response.data.gst_option) {
                                $("input[name='gst_option'][value='" + response.data.gst_option + "']")
                                    .prop(
                                        "checked",
                                        true).trigger('change');
                            }
                            taxes.forEach(function(tax) {
                                $("#tax-" + tax.tax_id).text(parseFloat(tax.amount).toFixed(2));
                            });
                        } else {
                            $('.vendor-section').hide();
                            $('.customer-section').hide();
                            $('#shipping-section').hide();
                            $('#discount-section').hide();
                            $('#discount-amount-li').hide();
                            $('#shipping-amount-li').hide();
                        }

                        // User-initiated changes (optional)
                        $('#choose_people').on('change', function() {
                            let selected = $(this).val();
                            if (selected === 'vendor') {
                                $('.vendor-section').show();
                                $('.customer-section').hide();
                            } else if (selected === 'customer') {
                                $('.customer-section').show();
                                $('.vendor-section').hide();
                            } else {
                                $('.vendor-section').hide();
                                $('.customer-section').hide();
                            }
                        });

                        if (response.success) {
                            let taxes = response.taxes;
                            let data = response.data;

                            // Set phone, status, etc.
                            $("#vendor_phone").val(data.vendor_phone);
                            $("select[name='status']").val(data.status);
                            $("#total-product-amount").text(data.total_amount);
                            $(".payment_status").val(data.payment_status).trigger("change");

                            // Convert to numbers
                            let totalAmount = parseFloat(data.total_amount);
                            let discountPercentage = parseFloat(data.discount) || 0;
                            let shipping = parseFloat(data.shipping) || 0;

                            // ✅ Calculate discount amount (percentage-based)
                            let discountAmount = (totalAmount * discountPercentage) / 100;

                            // ✅ Subtotal after discount
                            let subtotalAfterDiscount = totalAmount - discountAmount;

                            // ✅ Show calculated discount & shipping
                            $("#discount-amount").text(discountAmount.toFixed(2));
                            $("#shipping-amount").text(shipping.toFixed(2));

                            // ✅ Calculate taxes on discounted subtotal
                            let totalTaxAmount = 0;
                            taxes.forEach(function(tax) {
                                let taxRate = parseFloat(tax.rate);
                                let taxAmount = (totalAmount * taxRate) / 100;
                                totalTaxAmount += taxAmount;
                                $("#tax-" + tax.id).text(taxAmount.toFixed(2));
                            });

                            // ✅ Calculate final grand total
                            let grandTotalfinal = subtotalAfterDiscount + shipping + totalTaxAmount;
                            $("#grand-total").text(grandTotalfinal.toFixed(2));

                            // ✅ Split product data
                            let productIds = data.product_ids.split(", ");
                            let productNames = data.product_names.split(", ");
                            let productPrices = data.product_prices.split(", ");
                            let productQuantities = data.product_quantities.split(", ");
                            let categoryIds = data.category_ids.split(", ");
                            let categoryNames = data.category_names.split(", ");

                            // ✅ Clear previous product rows
                            $("#form-container").empty();

                            // ✅ Loop through products & add rows
                            for (let i = 0; i < productIds.length; i++) {
                                let rowHtml = `
                                                                                                <div class="row form-row">

                                                                                                    <div class="col-lg-3 col-sm-12 col-6">
                                                                                                        <div class="form-group">
                                                                                                            <label>Category Name</label>
                                                                                                            <select name="category_name[]" class="form-control select2 category-select" disabled>
                                                                                                                <option value="${categoryIds[i]}" selected>${categoryNames[i]}</option>
                                                                                                            </select>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-lg-2 col-sm-12 col-6">
                                                                                                        <div class="form-group">
                                                                                                            <label>Product Name</label>
                                                                                                            <select name="product_name[]" class="form-control select2 product-select" disabled>
                                                                                                                <option value="${productIds[i]}" selected>${productNames[i]}</option>
                                                                                                            </select>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-lg-2 col-sm-12 col-6">
                                                                                                        <div class="form-group">
                                                                                                            <label>Product Price</label>
                                                                                                            <input type="number" name="price[]" class="form-control price-input"
                                                                                                                value="${parseInt(productPrices[i])}" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-lg-2 col-sm-12 col-6">
                                                                                                        <div class="form-group">
                                                                                                            <label>Product Quantity</label>
                                                                                                            <input type="number" name="quantity[]" class="form-control quantity-input"
                                                                                                                value="${productQuantities[i]}" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-lg-2 col-sm-12 col-12">
                                                                                                        <div class="form-group">
                                                                                                            <label>Total Amount</label>
                                                                                                            <input type="text" name="total[]" class="form-control total-input"
                                                                                                                value="${(productPrices[i] * productQuantities[i]).toFixed(2)}" readonly>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>`;

                                $("#form-container").append(rowHtml);
                            }
                            calculateTotal();
                        } else {
                            Swal.fire("Error!", "Invoice not found!", "error");
                        }
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", "Invoice not found!", "error");

                    }
                });
            }

            $(document).on("click", ".btn-submit", function(e) {
                e.preventDefault();

                const $btn = $(this);
                const originalText = $btn.html();


                // Show loader
                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                    )
                    .prop('disabled', true);

                if (!validateForm()) {
                    $btn.html(originalText).prop('disabled', false);

                    return;
                }

                let formData = calculateTotal();
                let selectedType = $("#choose_people").val(); // e.g., "vendor" or "customer"
                if (selectedType === "vendor") {
                    formData.vendor_id = $("#vendor_name").val();
                    formData.vendor_phone = $("#vendor_phone").val();
                    formData.customer_id = null; // optional, explicitly set to null
                } else if (selectedType === "customer") {
                    formData.customer_id = $("#customer_name").val();
                    formData.vendor_id = null; // optional, explicitly set to null
                }
                formData.status = $("select[name='status']").val();
                formData.payment_status = $("select[name='payment_status']").val();

                // ✅ Collect Tax Data
                let taxArray = [];
                $(".tax-amount").each(function() {
                    let taxId = $(this).attr("id").split("-")[1];
                    let name = $(this).closest("li").find("h4").text();
                    let taxRate = parseFloat($(this).data("rate"));
                    let taxAmount = parseFloat($(this).text());

                    taxArray.push({
                        id: taxId,
                        name: name,
                        rate: taxRate,
                        amount: taxAmount,
                    });
                });

                formData.taxes = taxArray;

                $.ajax({
                    url: "/api/custom_invoice_update/" + invoiceId,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    contentType: "application/json",
                    data: JSON.stringify({
                        vendor_id: formData.vendor_id,
                        customer_id: formData.customer_id,
                        vendor_phone: formData.vendor_phone,
                        status: formData.status,
                        payment_status: formData.payment_status,
                        discount: formData.discount,
                        shipping: formData.shipping,
                        grand_total: formData.grandTotal,
                        taxes: formData.taxes,
                        products: formData.products,
                        gst_option: formData.gstOption
                    }),
                    success: function(response) {
                        // Reset button
                        $btn.html(originalText).prop('disabled', false);

                        if (response.success) {
                            Swal.fire({
                                title: "Success!",
                                text: "Purchase updated successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43" // your custom color
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href =
                                        "{{ route('custom_invoice.lists') }}";
                                }
                            });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.html(originalText).prop('disabled', false);

                        alert("Something went wrong!");
                        console.error(xhr.responseText);
                    }
                });
            });
            $(document).on("input", ".price-input, .quantity-input", function() {
                let value = parseInt($(this).val()) || 0;
                if (value < 0) value = 0; // no negatives
                $(this).val(value);
            });
        });
    </script>
@endpush
