@extends('layout.app')

@section('title', 'Add Purchase')

@section('content')
<style>
    .add-row-btn {
        margin-top: 2rem;
    }
    .form-row {
        border: 1px solid #fdc794;
        padding: 20px 10px 10px 10px;
        margin-bottom: 20px;
        border-radius: 10px;
        background: #fdfdfd;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        position: relative;
    }

    .form-row label {
        font-size: 13px;
        margin-bottom: 6px;
        white-space: nowrap;
    }

    .form-row .form-control,
    .form-row .select2-container .select2-selection--single {
        min-height: 40px;
        height: 40px;
    }

    .form-row .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-right: 30px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .form-row .add-row-btn .btn,
    .form-row .remove-row {
        min-width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        line-height: 1;
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

    @media screen and (width: 1024px) and (height: 1366px) {
        .qutity_type_class {
            width: 90px !important;
        }

        /* }element.style {
            width: 90px !important;
        } */

        input.form-control.quantity-input {
            width: 63px;
            margin-left: 44px;
        }

        label.qty_class {
            margin-left: 39px;
        }
    }


    .gst-header {
        display: flex;
        align-items: center;
        gap: 25px;
        padding: 12px 20px;
    }

    .gst-header .header-title {
        min-width: 140px;
        font-weight: bold;
        margin: 0;
        font-size: 18px;
        color: #444;
    }

    .custom-radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-weight: 600;
        color: #333;
        user-select: none;
        position: relative;
        padding-left: 28px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        transition: color 0.2s ease;
    }

    .custom-radio-label input[type="radio"] {
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border: 2.5px solid #888;
        border-radius: 50%;
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .custom-radio-label input[type="radio"]:checked {
        border-color: #0056b3;
        background-color: #0056b3;
    }

    .custom-radio-label input[type="radio"]:checked::after {
        content: "";
        display: block;
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .custom-radio-label:hover input[type="radio"] {
        border-color: #5a50cc;
    }

    .custom-radio-label:hover {
        color: #0056b3;
    }

    .manage_btn {
        color: #fff;
        background: #1b2850;
    }

    .manage_btn:hover {
        color: #e6e4e4ff;
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
    
</style>

<div class="content">
<div class="page-header purchase-entry-header">
    <div class="page-title">
        <h4>Add Purchase</h4>
    </div>
    <div class="gst-header ms-auto">
        @if (app('hasPermission')(3, 'add'))
            <a href="{{ route('purchase.ai-bill') }}" class="btn btn-sm ai-bill-upload-btn">
                <i class="fas fa-file-invoice" aria-hidden="true"></i> AI Bill Upload
            </a>
        @endif

        <label class="custom-radio-label">
            <input type="radio" name="gst_option" id="without_gst" value="without" checked />
            Without GST
        </label>

        <label class="custom-radio-label">
            <input type="radio" name="gst_option" id="with_gst" value="with" />
            With GST
        </label>

        <a href="{{ route('purchase.lists') }}" class="btn btn-back">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

        <label class="custom-radio-label">
            <input type="radio" name="gst_option" id="with_gst" value="with" />
            With GST
        </label>

        <a href="{{ route('purchase.lists') }}" class="btn btn-added">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>
    <div class="card">

        <div class="card-body">
            <div class="row">

                <div class="col-lg-6 col-sm-12 col-6">
                    <div class="form-group">
                        <label>Vendor Name <span class="text-danger field-required-mark">*</span></label>
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
                <div class="col-lg-6 col-sm-12 col-6 d-none">
                    <div class="form-group">
                        <label>Vendor Phone</label>
                        <input type="number" id="vendor_phone" name="phone" class="form-control"
                            placeholder="Enter Phone">
                        <span class="error text-danger"></span>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Bill No.</label>
                        <input type="text" name="bill_no" id="bill_no" class="form-control" placeholder="Bill No"
                            min="0">
                        <span class="error text-danger"></span>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <div class="row form-row">

                    <!-- <div class="col-lg-2 col-sm-12 col-6">
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
                    </div> -->
                    <div class="col-lg-3 col-sm-12 col-6">
                        <div class="form-group">
                            <label>Product Name <span class="text-danger field-required-mark">*</span></label>
                            <select id="product_name" name="product_name[]" class="form-control select2 product-select">
                                <option value="">Product Name</option>
                                @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                    data-quantity-type="{{ $product->quantity_type_id }}">
                                    {{ $product->name }}
                                </option>
                                @endforeach
                            </select>

                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-12 col-6">
                        <div class="form-group">
                            <label>Price <span class="text-danger field-required-mark">*</span></label>
                            <input type="number" name="price[]" class="form-control price-input"
                                placeholder="Enter Price" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-12 col-6">
                        <div class="form-group">
                            <label>Quantity Type</label>
                            <select class="select select2 quantity_type_id qutity_type_class" name="quantity_type_id[]">
                                <option value="">Choose Quantity Type</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-12 col-6">
                        <div class="form-group">
                            <label class="qty_class">Quantity</label>
                            <input type="number" name="quantity[]" class="form-control quantity-input"
                                placeholder="Enter Quantity" value="1" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
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
                <div id="form-container"></div>
            </div>

            <div class="row">
                
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Shipping</label>
                        <input type="number" name="shipping" id="shipping" class="form-control" placeholder="0.00"
                            min="0">
                        <span class="error text-danger"></span>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Purchase Status <span class="text-danger field-required-mark">*</span></label>
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
                        <label>Payment Mode</label>
                        <select name="payment_mode" id="payment_mode" class="form-control">
                            <option value="">Select Payment Mode</option>
                            <option value="">Pending</option>
                            <option value="cash">Cash</option>
                            <option value="online">Online</option>
                            <option value="cashonline">Cash + Online</option>
                        </select>
                        <div class="text-danger error-payment_mode"></div>
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
                        <div class="text-danger error-paid_type"></div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-12 col-lg-2 d-none" id="cash_amount_input_container">
                    <div class="form-group">
                        <label>Cash Amount</label>
                        <input type="number" id="cash_amount_input" name="cash_amount" class="form-control"
                            placeholder="Enter Cash Amount">
                        <span class="text-danger error-cash-amount" id="cash_amount_error"></span>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 col-lg-2 d-none" id="amount_input_container">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" id="amount_input" name="amount" class="form-control"
                            placeholder="Enter Amount">
                        <span class="text-danger error-cash-amount" id="amount_error"></span>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 col-lg-2 d-none" id="upi_amount_input_container">
                    <div class="form-group">
                        <label>Online Amount</label>
                        <input type="number" id="upi_amount_input" name="upi_amount" class="form-control"
                            placeholder="Enter Online Amount">
                        <span class="text-danger error-upi-amount" id="upi_amount_error"></span>
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

                            <div id="gst-section" style="display:none;">
                                @foreach ($taxes as $tax)
                                <li>
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

                            <li>
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
                <a href="{{ route('purchase.lists') }}" class="btn btn-cancel">Cancel</a>
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
        loadQuantityTypes();
        var authToken = localStorage.getItem("authToken");
        // Initialize Select2
        $(".vendor-select,.product-select,.category-select,.quantity_type_id").select2({
            tags: true,
        });
        //  $(".select2, .category-select").select2({
        //     tags: true,
        // });
        let invalid = false;

        function loadQuantityTypes() {
            let sub_branch_id = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url = "/api/get-quantity-type";
            if (sub_branch_id) url += `?sub_branch_id=${sub_branch_id}`;

            $.ajax({
                url: url,
                type: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    if (response.status && response.data.length > 0) {
                        // Get quantity types once
                        const quantityTypes = response.data;

                        // Apply to only NEW rows that don't have options yet
                        $(".quantity_type_id").each(function() {
                            let $el = $(this);

                            // Check if dropdown already has options beyond the default one
                            if ($el.find('option').length <= 1) {
                                let currentValue = $el.val();
                                let options = '<option value="">Choose Quantity Type</option>';

                                quantityTypes.forEach(type => {
                                    let isSelected = (currentValue == type.id) ? 'selected' : '';
                                    options += `<option value="${type.id}" ${isSelected}>
                                        ${type.quantity_type}
                                    </option>`;
                                });

                                $el.html(options);
                            }
                        });
                    } else {
                        // Only update empty dropdowns
                        $(".quantity_type_id").each(function() {
                            if ($(this).find('option').length <= 1) {
                                $(this).html('<option value="">No Quantity Type Available</option>');
                            }
                        });
                    }

                    // Trigger change to update Select2
                    $(".quantity_type_id").trigger('change');
                },
                error: function(error) {
                    console.error("Error loading Quantity Types:", error);
                }
            });
        }

        function loadQuantityTypesForNewRow(rowElement) {
            let sub_branch_id = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url = "/api/get-quantity-type";
            if (sub_branch_id) url += `?sub_branch_id=${sub_branch_id}`;

            $.ajax({
                url: url,
                type: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {
                    if (response.status && response.data.length > 0) {
                        let $quantitySelect = rowElement.find(".quantity_type_id");
                        let options = '<option value="">Choose Quantity Type</option>';

                        response.data.forEach(type => {
                            options += `<option value="${type.id}">
                                ${type.quantity_type}
                            </option>`;
                        });

                        $quantitySelect.html(options).trigger('change');
                    } else {
                        rowElement.find(".quantity_type_id").html('<option value="">No Quantity Type Available</option>');
                    }
                },
                error: function(error) {
                    console.error("Error loading Quantity Types for new row:", error);
                }
            });
        }



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

            if (selectedMode === "cash" || selectedMode === "online" || selectedMode === "cashonline" ||
                selectedMode === "pending") {
                $("#paid_type_container").removeClass("d-none");
                $("#emi_container").addClass("d-none");
            } else {
                $("#paid_type_container, #method_container, #amount_input_container, #cash_amount_input_container, #upi_amount_input_container, #pending_amount_container, #emi_container")
                    .addClass("d-none");
            }

            if (selectedMode === "emi") {
                $("#emi_container").removeClass("d-none").show();
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
        // Add new form row dynamically

        //                                      <div class="col-lg-2 col-sm-12 col-6">
        //                                             <div class="form-group">
        //                                                 <label>Category Name</label>
        //                                                 <select name="category_name[]" class="form-control category-select">
        //                                                     <option value="">Category Name</option>
        //                                                     @foreach ($categories as $category)
        //                                                         <option value="{{ $category->id }}" data-price="{{ $category->price }}">{{ $category->name }}</option>
        //                                                     @endforeach
        //                                                 </select>
        //                                                 <span class="error text-danger"></span>
        //                                             </div>
        //                                         </div>
        $(document).on("click", ".add-row", function() {
            let row = `
                                            <div class="row form-row">

                                                
                                                <div class="col-lg-3 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label>Product Name</label>
                                                        <select name="product_name[]" class="form-control select2 product-select">
                                                            <option value="">Product Name</option>
                                                            @foreach ($products as $product)
                                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-quantity-type="{{ $product->quantity_type_id }}">{{ $product->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label>Price</label>
                                                        <input type="number" name="price[]" class="form-control price-input" placeholder="Enter Price" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                 <div class="col-lg-2 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label>Quantity Type</label>
                                                        <select class="select select2 quantity_type_id" name="quantity_type_id[]">
                                                            <option value="">Choose Quantity Type</option>
                                                        </select>
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label class="qty_class">Quantity</label>
                                                        <input type="number" name="quantity[]" class="form-control quantity-input" placeholder="Enter Quantity" value="1" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
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
            newRow.find(".select2, .category-select, .quantity_type_id").select2({
                tags: true
            });
            loadQuantityTypesForNewRow(newRow);
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

                    // In your add-row function, update the product option format:
                    filteredProducts.forEach(function(product) {
                        $productDropdown.append(
                            `<option value="${product.id}" 
            data-price="${product.price}" 
            data-quantity-type="${product.quantity_type_id}">
            ${product.name}
        </option>`
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
            // let categoryId = selectedOption.data("category") || ""; // Get category ID
            let quantityTypeId = selectedOption.data("quantity-type") || null;

            let row = $(this).closest(".form-row");

            // Set price in input field
            row.find(".price-input").val(parseInt(price)).trigger("input");

            // ALWAYS set quantity type if available
            if (quantityTypeId) {
                row.find(".quantity_type_id").val(quantityTypeId).trigger('change');
            }

        });

        $(document).ready(function() {
            // Delegated change event for category-select
            $(document).on('change', '.category-select', function() {
                var selectedCategory = $(this).val();
                var $row = $(this).closest('.form-row');
                var $productDropdown = $row.find('.product-select');

                // Clear current options
                $productDropdown.empty();

                if (selectedCategory) {
                    $productDropdown.prop('disabled', false);
                    $productDropdown.append('<option value="">Product Name</option>');

                    // Filter products by category
                    var filteredProducts = products.filter(p => p.category_id == selectedCategory);

                    filteredProducts.forEach(function(product) {
                        $productDropdown.append(
                            `<option value="${product.id}" 
                    data-price="${product.price}" 
                    data-quantity-type="${product.quantity_type_id}">
                    ${product.name}
                </option>`
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
            $(document).on("change", ".product-select", function() {
                let selectedOption = $(this).find("option:selected");
                let price = selectedOption.data("price") || 0;
                let quantityTypeId = selectedOption.data("quantity-type") || null;

                let $row = $(this).closest(".form-row");

                // Set price in input field
                $row.find(".price-input").val(parseInt(price)).trigger("input");

                // Set quantity type in dropdown
                if (quantityTypeId) {
                    $row.find(".quantity_type_id").val(quantityTypeId).trigger('change');
                }
            });
        });

        $('input[name="gst_option"]').on('change', function() {
            calculateTotal();
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

            $(".form-row").each(function() {
                let productId = $(this).find(".product-select").val();
                let categoryId = $(this).find(".category-select").val();
                let price = parseFloat($(this).find(".price-input").val()) || 0;
                let quantity = parseInt($(this).find(".quantity-input").val()) || 0;
                let quantity_type_id = $(this).find(".quantity_type_id").val();
                let total = price * quantity;

                if (productId) {
                    products.push({
                        id: productId,
                        category_id: categoryId,
                        price: price,
                        quantity: quantity,
                        quantity_type_id: quantity_type_id,
                        total: total,
                    });
                }

                totalAmount += total;
                $(this).find(".total-input").val(total.toFixed(2));
            });

            // **Update Total Product Amount**
            $("#total-product-amount").text(totalAmount.toFixed(2));

            let discountPercent = parseFloat($("#discount").val()) || 0;
            let discountAmount = (totalAmount * discountPercent) / 100;
            let shipping = parseFloat($("#shipping").val()) || 0;

            let totalTaxAmount = 0;
            if ($('#with_gst').is(':checked')) {
                $('#gst-section').show();
                $(".tax-amount").each(function() {
                    let taxRate = parseFloat($(this).data("rate")) || 0;
                    let taxAmount = (totalAmount * taxRate) / 100;
                    totalTaxAmount += taxAmount;
                    $(this).text(taxAmount.toFixed(2));
                });
            } else {
                $('#gst-section').hide();
                $(".tax-amount").text('0.00');
            }
            let grandTotal = totalAmount + shipping - discountAmount + totalTaxAmount;

            $("#grand-total").text(grandTotal.toFixed(2));
            $("#shipping-amount").text(shipping.toFixed(2));

            return {
                products: products,
                totalProductAmount: totalAmount,
                totalAmount: totalAmount,
                discount: discountPercent,
                discountAmount: discountAmount,
                taxAmount: totalTaxAmount,
                shipping: shipping,
                grandTotal: grandTotal,
            };
        }

        // Update calculation when any relevant input changes
        $(document).on("input", "#discount, #shipping, .price-input, .quantity-input", function() {
            calculateTotal();
        });

        // Form Validation
        function validateForm() {
            let isValid = true;

            // Clear previous errors
            $(".error").text("");

            // Validate Vendor
            if ($("#vendor_name").val() === "") {
                $("#vendor_name").closest(".form-group").find(".error").text("Vendor is required.");
                isValid = false;
            }
            // if ($("#vendor_phone").val().trim() === "") {
            //     $("#vendor_phone").closest(".form-group").find(".error").text("Phone number is required.");
            //     isValid = false;
            // }

            // Validate Product Fields
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

        // Submit Form via AJAX
        $(".btn-submit").click(function(e) {
            e.preventDefault();

            let $btn = $(this);
            let originalContent = $btn.html();

            // Show spinner and disable button
            $btn.html(
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
            ).css("pointer-events", "none");

            if (!validateForm()) {
                $btn.html(originalContent).css("pointer-events", "auto");
                return;
            }

            let formData = calculateTotal();
            formData.vendor_id = $("#vendor_name").val();
            formData.vendor_phone = $("#vendor_phone").val();
            formData.status = $("select[name='status']").val();

            // Collect tax details
            // let taxes = [];
            // $(".tax-amount").each(function() {
            //     let taxItem = {
            //         id: $(this).data("id"), // Tax ID
            //         name: $(this).closest("li").find("h4").text(), // Tax Name
            //         rate: $(this).data("rate"), // Tax Rate
            //         amount: parseFloat($(this).text()) // Tax Amount
            //     };
            //     taxes.push(taxItem);
            // });

            let taxes = [];
            let gstOption = $("input[name='gst_option']:checked").val();

            if (gstOption === "with") {
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
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            // Get GST option (radio)
            let gst_option = $("input[name='gst_option']:checked").val();
            // Get payment details
            let bill_no = $("#bill_no").val();
            let payment_mode = $("#payment_mode").val();
            let paid_type = $("#paid_type").val();
            let cash_amount = $("#cash_amount_input").val();
            let upi_amount = $("#upi_amount_input").val();
            let amount = null;

            if (paid_type === 'full') {
                amount = formData.grandTotal;
            } else {
                amount = $("#amount_input").val();
            }

            console.log(amount);
            console.log(paid_type);
            console.log(formData.grandTotal);
            let remaining_amount = $("#pending_amount").val();

            $.ajax({
                url: "/api/purchase_order",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    "Authorization": "Bearer " + authToken,
                },
                contentType: "application/json",
                data: JSON.stringify({
                    vendor_id: formData.vendor_id,
                    vendor_phone: formData.vendor_phone,
                    status: formData.status,
                    discount: formData.discount,
                    shipping: formData.shipping,
                    grand_total: formData.grandTotal,
                    products: formData.products,
                    taxes: taxes,
                    gst_option: gst_option,
                    payment_mode: payment_mode,
                    bill_no: bill_no,
                    paid_type: paid_type,
                    cash_amount: cash_amount,
                    upi_amount: upi_amount,
                    amount: amount,
                    remaining_amount: remaining_amount,
                    selectedSubAdminId: selectedSubAdminId
                }),
                success: function(response) {
                    $btn.html(originalContent).css("pointer-events", "auto");

                    const purchaseId = response.purchase_id;

                    if (response.success) {
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43" // Set custom button color
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Open the PDF in a new tab
                                window.open("/purchase/invoice/pdf/" + purchaseId,
                                    "_blank");

                                // Redirect to purchase view page
                                window.location.href = "/print-purchase/" +
                                    purchaseId;
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

                    console.error(xhr.responseText); // For debugging
                }

            });
        });
    });
</script>
@endpush
