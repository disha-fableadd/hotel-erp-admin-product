@extends('layout.app')

@section('title', 'Add Invoice')

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
        $user = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && !empty($selectedSubAdminId)) {
            $branchIdToUse = $selectedSubAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        $taxes = App\Models\TaxRate::where('status', 'active')->where('isDeleted', 0)->where('branch_id', $branchIdToUse)->get();
        $products = App\Models\Product::where('isDeleted', 0)->where('branch_id', $branchIdToUse)->get();

        $settings = \DB::table('settings')->where('branch_id', $branchIdToUse)->first();
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
                <h4>Add Invoice</h4>
            </div>
            <div class="gst-header" style="display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex">
                    <label class="custom-radio-label" style="margin-right: 1rem;">
                        <input type="radio" name="gst_option" id="without_gst" value="without_gst" checked />
                        Without GST
                    </label>

                    <label class="custom-radio-label">
                        <input type="radio" name="gst_option" id="with_gst" value="with_gst" />
                        With GST
                    </label>
                </div>
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
                                    placeholder="Enter Price" oninput="this.value = this.value < 0 ? 0 : this.value" min="0">
                                <span class="error text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-12 col-6">
                            <div class="form-group">
                                <label>Product Quantity</label>
                                <input type="number" name="quantity[]" class="form-control quantity-input"
                                    placeholder="Enter Quantity" value="0" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                <span class="error text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Total Amount</label>
                                <input type="text" name="total[]" class="form-control total-input" placeholder="0"
                                    readonly>
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
                            <span class="error text-danger" id="discount-error"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Order Status</label>
                            <select name="status" class="form-control">
                                <option value="">Choose Status</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-12 col-lg-2">
                        <div class="form-group">
                            <label>Payment Mode</label>
                            <select name="payment_mode" id="payment_mode" class="form-control">
                                <option value="">Select Payment Mode</option>
                                <option value="pending">Pending</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="cashonline">Cash + Online</option>
                            </select>
                            <div class="error text-danger"></div>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 col-lg-2 d-none" id="paid_type_container">
                        <div class="form-group">
                            <label>Paid Type</label>
                            <select id="paid_type" name="paid_type" class="form-control">
                                <option value="">Select Paid Type</option>
                                <option value="full">Fully Paid</option>
                                <option value="partial">Partially Paid</option>
                            </select>
                            <div class="error text-danger"></div>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 col-lg-2 d-none" id="cash_amount_input_container">
                        <div class="form-group">
                            <label>Cash Amount</label>
                            <input type="number" id="cash_amount_input" name="cash_amount" class="form-control"
                                placeholder="Enter Cash Amount">
                            <span class="error text-danger" id="cash_amount_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 col-lg-2 d-none" id="amount_input_container">
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" id="amount_input" name="amount" class="form-control"
                                placeholder="Enter Amount">
                            <span class="error text-danger" id="amount_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 col-lg-2 d-none" id="upi_amount_input_container">
                        <div class="form-group">
                            <label>Online Amount</label>
                            <input type="number" id="upi_amount_input" name="upi_amount" class="form-control"
                                placeholder="Enter Online Amount">
                            <span class="error text-danger" id="upi_amount_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 col-lg-2 d-none" id="pending_amount_container">
                        <div class="form-group">
                            <label>Pending Amount</label>
                            <input type="text" id="pending_amount" name="pending_amount" class="form-control"
                                readonly>
                            <span id="pending_error" style="color:red; font-size:12px;"></span>
                        </div>
                    </div>
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

                                <div id="tax-section" style="display: none;">
                                    @foreach ($taxes as $tax)
                                        <li class="tax-row">
                                            <h4>{{ $tax->tax_name }}</h4>
                                            <h5 style="color: green;">
                                                @if ($currencyPosition === 'right')
                                                    <span class="tax-amount" data-id="{{ $tax->id }}"
                                                        data-rate="{{ $tax->tax_rate }}">0.00</span>{{ $currencySymbol }}
                                                @else
                                                    {{ $currencySymbol }}<span class="tax-amount"
                                                        data-id="{{ $tax->id }}"
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

            $(".vendor-select,.category-select,.customer-select,.product-select").select2({
                tags: true,
            });

            let invalid = false;

            $('#upi_amount_input').on('keyup input', function() {
                let value = parseFloat($(this).val());
                let $errorSpan = $('#upi_amount_error');

                if (!isNaN(value) && value < 0) {
                    invalid = true;
                    $errorSpan.text('Negative amount is not valid');
                } else {
                    invalid = false;
                    $errorSpan.text('');
                }
            });
            $('#amount_input').on('keyup input', function() {
                let value = parseFloat($(this).val());
                let $errorSpan = $('#amount_error');

                if (!isNaN(value) && value < 0) {
                    invalid = true;
                    $errorSpan.text('Negative amount is not valid');
                } else {
                    invalid = false;
                    $errorSpan.text('');
                }
            });
            $('#cash_amount_input').on('keyup input', function() {
                let value = parseFloat($(this).val());
                let $errorSpan = $('#cash_amount_error');

                if (!isNaN(value) && value < 0) {
                    invalid = true;
                    $errorSpan.text('Negative amount is not valid');
                } else {
                    invalid = false;
                    $errorSpan.text('');
                }
            });

            $('#payment_mode').prop('disabled', true);

            // Watch for changes in the payable amount field
            const target = document.getElementById('grand-total');

            const observer = new MutationObserver(() => {
                let amount = parseFloat($('#grand-total').text());
                if (!isNaN(amount) && amount > 0) {
                    $('#payment_mode').prop('disabled', false);
                } else {
                    $('#payment_mode').val('').prop('disabled', true);
                }
            });

            observer.observe(target, {
                childList: true,
                characterData: true,
                subtree: true
            });

            $("#payment_mode").change(function() {
                const selectedMode = $(this).val();

                // Reset all
                $("#paid_type").val("");
                $("#amount_input").val("");
                $("#cash_amount_input").val("");
                $("#upi_amount_input").val("");
                $("#pending_amount").val("");

                if (selectedMode === "pending") {
                    // If pending, hide everything related to payment
                    $("#paid_type_container, #method_container, #amount_input_container, #cash_amount_input_container, #upi_amount_input_container, #pending_amount_container, #emi_container")
                        .addClass("d-none");
                } else {


                    if (selectedMode === "cash" || selectedMode === "online" || selectedMode ===
                        "cashonline") {
                        $("#paid_type_container").removeClass("d-none");
                        $("#emi_container").addClass("d-none");
                    } else {
                        $("#paid_type_container, #method_container, #amount_input_container, #cash_amount_input_container, #upi_amount_input_container, #pending_amount_container, #emi_container")
                            .addClass("d-none");
                    }

                    if (selectedMode === "emi") {
                        $("#emi_container").removeClass("d-none").show();
                    }
                }
                // Hide all specific inputs initially
                $("#cash_amount_input_container").addClass("d-none");
                $("#upi_amount_input_container").addClass("d-none");
                $("#amount_input_container").addClass("d-none");
                $("#pending_amount_container").addClass("d-none");
            });

            $("#paid_type").change(function() {
                const type = $(this).val();
                const selectedMode = $("#payment_mode").val();

                // Clear all fields and errors
                $("#amount_input, #cash_amount_input, #upi_amount_input, #pending_amount").val("");
                $("#cash_amount_error, #upi_amount_error").text("");

                // Hide all input containers first
                $("#amount_input_container, #pending_amount_container, #cash_amount_input_container, #upi_amount_input_container")
                    .addClass("d-none");

                // Remove required attributes initially
                $("#amount_input, #cash_amount_input, #upi_amount_input, #pending_amount").prop("required",
                    false);

                if (type === "full") {
                    invalid1 = false;
                    if (selectedMode === "cash") {
                        // $("#amount_input_container").removeClass("d-none");
                        // $("#amount_input").prop("required", true);

                    } else if (selectedMode === "online") {
                        // $("#amount_input_container").removeClass("d-none");
                        // $("#amount_input").prop("required", true);

                    } else if (selectedMode === "cashonline") {
                        // Show both if cash + online
                        $("#cash_amount_input_container").removeClass("d-none");
                        $("#upi_amount_input_container").removeClass("d-none");

                        $("#cash_amount_input").prop("required", true);
                        $("#upi_amount_input").prop("required", true);
                    }

                } else if (type === "partial") {
                    if (selectedMode === "cash" || selectedMode === "online") {
                        // Normal partial
                        $("#amount_input_container").removeClass("d-none");
                        $("#pending_amount_container").removeClass("d-none");

                        $("#amount_input").prop("required", true);
                        $("#pending_amount").prop("required", true);

                    } else if (selectedMode === "cashonline") {
                        // Partial with cash + online
                        $("#cash_amount_input_container").removeClass("d-none");
                        $("#upi_amount_input_container").removeClass("d-none");
                        $("#pending_amount_container").removeClass("d-none");

                        $("#cash_amount_input").prop("required", true);
                        $("#upi_amount_input").prop("required", true);
                        $("#pending_amount").prop("required", true);
                    }
                }
            });

            $("#amount_input").on("input", function() {
                const partialPaid = parseFloat($(this).val()) || 0;
                const payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, '')) || 0;
                const pending = payable - partialPaid;

                $("#pending_amount").val(pending > 0 ? pending.toFixed(2) : "0.00");
            });
            let invalid1 = false;
            $("#cash_amount_input, #upi_amount_input").on("input", function() {
                const cashPaid = parseFloat($("#cash_amount_input").val()) || 0;
                const onlinePaid = parseFloat($("#upi_amount_input").val()) || 0;
                const payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, '')) || 0;

                const totalPaid = cashPaid + onlinePaid;
                const pending = payable - totalPaid;

                // Update pending field
                $("#pending_amount").val(pending >= 0 ? pending.toFixed(2) : "0.00");


                // Validation
                if (pending < 0 || isNaN(pending)) {
                    invalid1 = true;
                    $("#pending_error").text("Enter correct amount cash + online"); // <-- your error span
                } else {
                    invalid1 = false;
                    $("#pending_error").text("");
                }
            });

            $("#amount_input").on("input", function() {
                const cashPaid = parseFloat($("#amount_input").val()) || 0;

                const payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, '')) || 0;

                const totalPaid = cashPaid;
                const pending = payable - totalPaid;

                // Update pending field
                $("#pending_amount").val(pending > 0 ? pending.toFixed(2) : "0.00");

                // Validation
                if (pending <= 0 || isNaN(pending)) {
                    invalid1 = true;
                    $("#pending_error").text(
                        "Cannot enter more than payable amount"); // <-- your error span
                } else {
                    invalid1 = false;
                    $("#pending_error").text("");
                }
            });

            function validateFullPayment() {
                const payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, '')) || 0;
                const cash = parseFloat($("#cash_amount_input").val()) || 0;
                const upi = parseFloat($("#upi_amount_input").val()) || 0;
                const total = cash + upi;

                // Clear previous messages
                $("#cash_amount_error").text("");
                $("#upi_amount_error").text("");

                if ($("#paid_type").val() === "full") {
                    if (total !== payable) {
                        const msg = `Cash + UPI must equal payable amount (₹${payable})`;
                        $("#cash_amount_error").text(msg);
                        $("#upi_amount_error").text(msg);
                        return false;
                    }
                }

                return true;
            }


            // Bind validation on input
            $("#cash_amount_input, #upi_amount_input").on("input", function() {
                validateFullPayment();
            });

            // Initially hide both
            $("#shipping-section").hide();
            $("#discount-section").hide();

            $("#choose_people").change(function() {
                let selected = $(this).val();

                if (selected === "vendor") {
                    // Show shipping, hide discount
                    $("#shipping-section").show();
                    $("#discount-section").hide();
                } else if (selected === "customer") {
                    // Show discount, hide shipping
                    $("#discount-section").show();
                    $("#shipping-section").hide();
                } else {
                    // Hide both if no selection
                    $("#shipping-section").hide();
                    $("#discount-section").hide();
                }
            });

            let discountInput = $("#discount");
            let shippingInput = $("#shipping");

            // Validate Discount if visible
            if ($("#discount-section").is(":visible")) {
                let discount = parseFloat(discountInput.val()) || 0;
                if (discount < 0 || discount > 100) {
                    discountInput.closest(".form-group").find(".error").text("Discount must be between 0-100%.");
                    isValid = false;
                }
            }

            // Validate Shipping if visible
            if ($("#shipping-section").is(":visible")) {
                let shipping = parseFloat(shippingInput.val()) || 0;
                if (shipping < 0) {
                    shippingInput.closest(".form-group").find(".error").text("Shipping cost cannot be negative.");
                    isValid = false;
                }
            }
        });
        $(document).on("input", "#discount", function() {
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
            // Initialize Select2
            // $(".select2, .category-select").select2({
            //     tags: true,
            // });

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
                                    <input type="number" name="price[]" class="form-control price-input" placeholder="Enter Price" oninput="this.value = this.value < 0 ? 0 : this.value">
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

            // Update Vendor Phone when Vendor is selected
            $("#vendor_name").on("change", function() {
                let phone = $(this).find(":selected").data("phone");
                $("#vendor_phone").val(phone ? phone : "");
            });
            $(document).on("input", ".price-input, .quantity-input", function () {
                let value = parseInt($(this).val()) || 0;
                if (value < 0) value = 0; // no negatives
                $(this).val(value);
            });


            // Update Product Price when selecting a Product
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

            // Calculate Total when Price or Quantity is updated
            $(document).on("input", ".price-input, .quantity-input", function() {
                let row = $(this).closest(".form-row");
                let price = parseFloat(row.find(".price-input").val()) || 0;
                let quantity = parseInt(row.find(".quantity-input").val()) || 0;
                let total = price * quantity;
                row.find(".total-input").val(total.toFixed(2));
                calculateTotal();
            });

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
                    $(".tax-amount").text("0.00");
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
            
            $(document).ready(function() {
                // ✅ Handle initial state on page load
                let gstOption = $("input[name='gst_option']:checked").val();
                if (gstOption === "with_gst") {
                    $("#tax-section").show();
                } else {
                    $("#tax-section").hide();
                }

                // ✅ Handle state change when user clicks
                $(document).on("change", "input[name='gst_option']", function() {
                    let gstOption = $("input[name='gst_option']:checked").val();

                    if (gstOption === "with_gst") {
                        $("#tax-section").show(); // show all taxes
                    } else {
                        $("#tax-section").hide(); // hide tax section
                    }

                    calculateTotal();
                });
            });


            // Update calculation when any relevant input changes
            $(document).on("input", "#discount, #shipping, .price-input, .quantity-input", function() {
                calculateTotal();
            });

            // Form Validation
            function validateForm() {
                let isValid = true;

                // Clear previous errors
                $(".error").text("");

                // === Choose People Validation ===
                let choosePeople = $("#choose_people").val();
                if (choosePeople === "") {
                    $("#choose_people").closest(".form-group").find(".error").text(
                        "Please select Customer/Vendor.");
                    isValid = false;
                } else if (choosePeople === "vendor" && $("#vendor_name").val() === "") {
                    $("#vendor_name").closest(".form-group").find(".error").text("Vendor is required.");
                    isValid = false;
                } else if (choosePeople === "customer" && $("#customer_name").val() === "") {
                    $("#customer_name").closest(".form-group").find(".error").text("Customer is required.");
                    isValid = false;
                }

                // === Product Validation ===
                $(".form-row").each(function() {
                    let productSelect = $(this).find(".product-select");
                    let categorySelect = $(this).find(".category-select");
                    let priceInput = $(this).find(".price-input");
                    let quantityInput = $(this).find(".quantity-input");

                    if (productSelect.val() === "") {
                        productSelect.closest(".form-group").find(".error").text("Product is required.");
                        isValid = false;
                    }
                    if (categorySelect.val() === "") {
                        categorySelect.closest(".form-group").find(".error").text("Category is required.");
                        isValid = false;
                    }
                    if (priceInput.val().trim() === "" || parseFloat(priceInput.val()) <= 0) {
                        priceInput.closest(".form-group").find(".error").text("Valid price is required.");
                        isValid = false;
                    }
                    if (quantityInput.val().trim() === "" || parseInt(quantityInput.val()) <= 0) {
                        quantityInput.closest(".form-group").find(".error").text(
                            "Valid quantity is required.");
                        isValid = false;
                    }
                });

                // === Discount / Shipping ===
                let discount = parseFloat($("#discount").val()) || 0;
                let shipping = parseFloat($("#shipping").val()) || 0;

                if ($("#discount-section").is(":visible") && (discount < 0 || discount > 100)) {
                    $("#discount").closest(".form-group").find(".error").text("Discount must be between 0-100%.");
                    isValid = false;
                }
                if ($("#shipping-section").is(":visible") && shipping < 0) {
                    $("#shipping").closest(".form-group").find(".error").text("Shipping cost cannot be negative.");
                    isValid = false;
                }

                // === Status Validation ===
                if ($("select[name='status']").val() === "") {
                    $("select[name='status']").closest(".form-group").find(".error").text(
                        "Please select a status.");
                    isValid = false;
                }

                // === Payment Mode Validation ===
                let paymentMode = $("#payment_mode").val();
                let paidType = $("#paid_type").val();
                let payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, '')) || 0;

                if (paymentMode === "") {
                    $("#payment_mode").closest(".form-group").find(".error").text("Please select payment mode.");
                    isValid = false;
                } else {
                    $("#payment_mode").closest(".form-group").find(".error").text("");
                    if (paymentMode != "pending") {
                        if (paidType === "") {
                            $("#paid_type").closest(".form-group").find(".error").text("Please select Paid Type.");
                            isValid = false;
                        }

                    }


                    if (paidType === "full") {
                        if (paymentMode === "cash" || paymentMode === "online") {
                            // let amt = parseFloat($("#amount_input").val()) || 0;
                            // if (amt !== payable) {
                            //     $("#amount_error").text(`Must be exactly ₹${payable}`);
                            //     isValid = false;
                            // }
                        } else if (paymentMode === "cashonline") {
                            let cash = parseFloat($("#cash_amount_input").val()) || 0;
                            let upi = parseFloat($("#upi_amount_input").val()) || 0;
                            if (cash + upi !== payable) {
                                $("#cash_amount_error, #upi_amount_error").text(
                                    `Cash + Online must equal ₹${payable}`);
                                isValid = false;
                            }
                        }
                    }

                    if (paidType === "partial") {
                        if (paymentMode === "cash" || paymentMode === "online") {
                            let amt = parseFloat($("#amount_input").val()) || 0;
                            if (amt <= 0 || amt >= payable) {
                                $("#amount_error").text("Partial must be less than payable amount.");
                                isValid = false;
                            }
                        } else if (paymentMode === "cashonline") {
                            let cash = parseFloat($("#cash_amount_input").val()) || 0;
                            let upi = parseFloat($("#upi_amount_input").val()) || 0;
                            let total = cash + upi;
                            if (total <= 0 || total >= payable) {
                                $("#pending_error").text("Partial cash + online must be less than payable.");
                                isValid = false;
                            }
                        }
                    }
                }

                return isValid;
            }


            // Submit Form via AJAX
            $(".btn-submit").click(function(e) {
                e.preventDefault();

                let $btn = $(this);
                let originalContent = $btn.html();

                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                    )
                    .css("pointer-events", "none");

                if (!validateForm()) {
                    $btn.html(originalContent).css("pointer-events", "auto");
                    return;
                }

                let formData = calculateTotal();
                formData.status = $("select[name='status']").val();

                // Determine if vendor or customer is selected
                let selectedType = $("#choose_people").val(); // e.g., "vendor" or "customer"
                if (selectedType === "vendor") {
                    formData.vendor_id = $("#vendor_name").val();
                    formData.vendor_phone = $("#vendor_phone").val();
                    formData.customer_id = null; // optional, explicitly set to null
                } else if (selectedType === "customer") {
                    formData.customer_id = $("#customer_name").val();
                    formData.vendor_id = null; // optional, explicitly set to null
                }

                // Collect tax details
                // let taxes = [];
                // $(".tax-amount").each(function() {
                //     let taxItem = {
                //         id: $(this).data("id"),
                //         name: $(this).closest("li").find("h4").text(),
                //         rate: $(this).data("rate"),
                //         amount: parseFloat($(this).text()) 
                //     };
                //     taxes.push(taxItem);
                // });
                // Collect tax details only if GST option is "with"
                let taxes = [];
                let gstOption = $("input[name='gst_option']:checked").val();

                if (gstOption === "with_gst") {
                    $(".tax-amount").each(function() {
                        let taxItem = {
                            id: $(this).data("id"),
                            name: $(this).closest("li").find("h4").text(),
                            rate: $(this).data("rate"),
                            amount: parseFloat($(this).text())
                        };
                        taxes.push(taxItem);
                    });
                }

                let paymentData = {
                    payment_mode: $("#payment_mode").val(),
                    paid_type: $("#paid_type").val(),
                    cash_amount: parseFloat($("#cash_amount_input").val()) || 0,
                    amount: parseFloat($("#amount_input").val()) || 0,
                    upi_amount: parseFloat($("#upi_amount_input").val()) || 0,
                    pending_amount: parseFloat($("#pending_amount").val()) || 0
                };
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: "/api/customer_invoice/store", // Adjust as needed
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    contentType: "application/json",
                    data: JSON.stringify({
                        status: formData.status,
                        discount: formData.discount,
                        shipping: formData.shipping,
                        grand_total: formData.grandTotal,
                        products: formData.products,
                        taxes: taxes,
                        gst_option: gstOption,
                        vendor_id: formData.vendor_id,
                        vendor_phone: formData.vendor_phone,
                        customer_id: formData.customer_id,
                        selectedSubAdminId: selectedSubAdminId,
                        payment: paymentData
                    }),
                    success: function(response) {
                        var invoice_id = response.invoice_id;
                        $btn.html(originalContent).css("pointer-events", "auto");
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open("/custom-invoice/pdf/" + invoice_id,
                                        "_blank");
                                    window.location.href =
                                        "{{ route('custom_invoice.lists') }}";
                                }
                            });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.html(originalContent).css("pointer-events", "auto");
                        $(".error").text(""); // Clear previous

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            let message = xhr.responseJSON.message;

                            if (message.includes("phone number")) {
                                $("#vendor_phone").closest(".form-group").find(".error").text(
                                    message);
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: message,
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        } else {
                            alert("Something went wrong!");
                        }

                        console.error(xhr.responseText);
                    }
                });
            });

        });
    </script>
@endpush
