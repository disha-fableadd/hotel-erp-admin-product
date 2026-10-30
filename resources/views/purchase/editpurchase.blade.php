@extends('layout.app')

@section('title', 'Edit Purchase')

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
    @php
        $user = Auth()->user();
        $branchId = $user->id ?? null;
        $userRole = $user->role ?? '';
        $subAdminId = session('selectedSubAdminId');
        if ($userRole === 'sub-admin') {
            $branchId = $user->id; // sub-admin uses own id
        } elseif ($userRole === 'admin' && !empty($subAdminId)) {
            $branchId = $subAdminId; // admin chooses sub-admin
        } elseif ($userRole === 'staff') {
            $branchId = $user->branch_id; // admin with no sub-admins
        } else {
            $branchId = $user->id; // for other roles, use own id
        }
        $vendors = App\Models\User::where('role', 'vendor')
            ->where('isDeleted', 0) // Only non-deleted vendors
            ->where('branch_id', $branchId) // Filter by branch_id
            ->get();
        $taxes = App\Models\TaxRate::where('status', 'active')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->get();
        $products = App\Models\Product::where('isDeleted', 0)->where('branch_id', $branchId)->get();

        $settings = \DB::table('settings')->where('branch_id', $branchId)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';
        $productsArray = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
            ];
        });
        $productsJson = json_encode($productsArray);
    @endphp
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Purchase</h4>
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
<a href="{{ route('purchase.lists') }}" class="btn btn-added">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>Vendor Name</label>
                            <select id="vendor_name" name="vendor_id" class="form-control select2 vendor-select" disabled>
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
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" name="bill_no" id="bill_no" class="form-control" placeholder="Bill No">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 d-none">
                        <div class="form-group">
                            <label>Vendor Phone</label>
                            <input type="number" id="vendor_phone" name="phone" class="form-control"
                                placeholder="Enter Phone" readonly>
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
                                <label>Product Name</label>
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
                                <label>Price</label>
                                <input type="number" name="price[]" class="form-control price-input"
                                    placeholder="Enter Price" min="0">
                                <span class="error text-danger"></span>
                            </div>
                        </div>
                           <div class="col-lg-2 col-sm-12 col-6">
                             <div class="form-group">
                                <label>Quantity Type</label>
                                <select class="select select2 quantity_type_id" name="quantity_type_id[]">
                                <option value="">Choose Quantity Type</option>
                                 @foreach ($quantity_types as $qty_type)
                                 <option value="{{ $qty_type->id }}" data-price="{{ $qty_type->price }}">
                                            {{ $qty_type->quantity_type }}
                                        </option>
                                    @endforeach
                                </select>
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
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Shipping</label>
                            <input type="number" name="shipping" id="shipping" class="form-control" placeholder="0.00"
                                min="0">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
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
                                <!-- GST Section -->
                                <div class="tax-section">
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
        const quantityTypes = @json($quantity_types);
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
            function loadQuantityTypes(selectedId = null) {
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

                        // apply for ALL dropdowns including new rows
                        $(".quantity_type_id").each(function() {
                            let el = $(this);

                            let options = '<option value="">Choose Quantity Type</option>';

                            response.data.forEach(type => {
                                let isSelected = selectedId == type.id ? 'selected' : '';
                                options += `<option value="${type.id}" ${isSelected}>
                            ${type.quantity_type}
                        </option>`;
                            });

                            // el.html(options).trigger('change');
                        });
                    //     el.select2({
                    //     tags: true,
                    // });

                    } else {
                        $(".quantity_type_id").html('<option value="">No Quantity Type Available</option>');
                    }
                },
                error: function(error) {
                    console.error("Error loading Quantity Types:", error);
                }
            });
        }
        function loadProductsByCategory(categoryId, row) {
        let authToken = localStorage.getItem("authToken");
        let subBranchId = localStorage.getItem("selectedSubAdminId");
        
        $.ajax({
            url: "/api/get-products-by-category/" + categoryId,
            type: "GET",
            headers: {
                "Authorization": "Bearer " + authToken
            },
            data: {
                sub_branch_id: subBranchId
            },
            success: function(response) {
                if (response.status && response.data.length > 0) {
                    let productDropdown = row.find('.product-select');
                    
                    // Clear existing options
                    productDropdown.empty();
                    productDropdown.append('<option value="">Product Name</option>');
                    
                    // Add filtered products
                    response.data.forEach(product => {
                        productDropdown.append(
                            `<option value="${product.id}" 
                            data-price="${product.price}"
                            data-quantity-type-id="${product.quantity_type_id}"
                            data-quantity-type-name="${product.quantity_type_name}">
                                ${product.name}
                            </option>`
                        );
                    });
                    
                    // Enable dropdown and refresh Select2
                    productDropdown.prop('disabled', false);
                    productDropdown.select2('destroy').select2({
                        tags: true
                    });
                } else {
                    alert('No products found for this category');
                }
            },
            error: function(error) {
                console.error("Error loading products:", error);
            }
        });
    }


            // Initialize Select2
            // $(".select2, .category-select").select2({
            //     tags: true,
            // });
            $(".vendor-select,.product-select,.category-select,.quantity_type_id").select2({
                tags: true,
            });
            // Add new form row dynamically
                                                                                    // <div class="col-lg-2 col-sm-12 col-6">
                                                                                    //     <div class="form-group">
                                                                                    //         <label>Category Name</label>
                                                                                    //         <select name="category_name[]" class="form-control category-select">
                                                                                    //             <option value="">Category Name</option>
                                                                                    //             @foreach ($categories as $category)
                                                                                    //                 <option value="{{ $category->id }}" data-price="{{ $category->price }}">{{ $category->name }}</option>
                                                                                    //             @endforeach
                                                                                    //         </select>
                                                                                    //         <span class="error text-danger"></span>
                                                                                    //     </div>
                                                                                    // </div>
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
                                                                                            <input type="number" name="price[]" class="form-control price-input" placeholder="Enter Price" min="0">
                                                                                            <span class="error text-danger"></span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-2 col-sm-12 col-6">
                                                                                        <div class="form-group">
                                                                                        <label>Quantity Type</label>
                                                                                        <select class="select select2 quantity_type_id" name="quantity_type_id[]">
                                                                                        <option value="">Choose Quantity Type</option>
                                                                                        @foreach ($quantity_types as $qty_type)
                                                                                        <option value="{{ $qty_type->id }}" data-price="{{ $qty_type->price }}">
                                                                                                    {{ $qty_type->quantity_type }}
                                                                                                </option>
                                                                                            @endforeach
                                                                                        </select>
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

                loadQuantityTypes();
                 // Reinitialize Select2 for dynamically added elements
                newRow.find(".select2, .category-select,.quantity_type_id").select2({
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
                        // var filteredProducts = products.filter(p => p.category_id ==
                        //     selectedCategory);

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
                // let categoryId = selectedOption.data("category") || ""; // Get category ID
                  let quantityTypeId = selectedOption.data("quantity-type") || null;

                let row = $(this).closest(".form-row");

                // Set price in input field
                row.find(".price-input").val(parseInt(price)).trigger("input");

                 if (quantityTypeId) {
                    row.find(".quantity_type_id").val(quantityTypeId).trigger('change');
                }

            });

            // $(document).ready(function() {
            //     // Delegated change event for category-select
            //     $(document).on('change', '.category-select', function() {
            //         var selectedCategory = $(this).val();

            //         var $row = $(this).closest('.form-row'); // find the row of this select
            //         var $productDropdown = $row.find('.product-select');

            //         // Clear current options
            //         $productDropdown.empty();

            //         if (selectedCategory) {
            //             $productDropdown.prop('disabled', false);

            //             $productDropdown.append('<option value="">Product Name</option>');

            //             // Filter products by category
            //             var filteredProducts = products.filter(p => p.category_id ==
            //                 selectedCategory);

            //             filteredProducts.forEach(function(product) {
            //                 $productDropdown.append(
            //                     `<option value="${product.id}" data-price="${product.price}">${product.name}</option>`
            //                 );
            //             });
            //         } else {
            //             $productDropdown.prop('disabled', true);
            //             $productDropdown.append('<option value="">Product Name</option>');
            //         }

            //         // Refresh Select2
            //         $productDropdown.select2('destroy').select2();
            //     });

            //     // Delegated change event for product-select
            //     $(document).on('change', '.product-select', function() {
            //         let selectedOption = $(this).find("option:selected");
            //         let price = selectedOption.data("price") || 0;

            //         let $row = $(this).closest('.form-row');
            //         $row.find(".price-input").val(parseInt(price)).trigger("input");
            //     });
            // });
            $(document).ready(function() {
    // Delegated change event for category-select
    $(document).on('change', '.category-select', function() {
        var selectedCategory = $(this).val();
        var $row = $(this).closest('.form-row'); // find the row of this select
        var $productDropdown = $row.find('.product-select');
        var $quantityTypeDropdown = $row.find('.quantity_type_id');

        // Clear current options
        $productDropdown.empty();

        if (selectedCategory) {
            // Disable product dropdown until products are loaded
            $productDropdown.prop('disabled', true);
            $productDropdown.append('<option value="">Loading products...</option>');
            
            // Call API to get products by category
            loadProductsByCategory(selectedCategory, $row);
        } else {
            $productDropdown.prop('disabled', true);
            $productDropdown.append('<option value="">Product Name</option>');
            
            // Reset quantity type
            $quantityTypeDropdown.val('').trigger('change');
        }

        // Refresh Select2
        $productDropdown.select2('destroy').select2();
    });

    // Delegated change event for product-select
    $(document).on('change', '.product-select', function() {
        let selectedOption = $(this).find("option:selected");
        let price = selectedOption.data("price") || 0;
        let quantityTypeId = selectedOption.data("quantity-type-id") || "";
        let quantityTypeName = selectedOption.data("quantity-type-name") || "";

        let $row = $(this).closest('.form-row');
        
        // Set price in input field
        $row.find(".price-input").val(parseInt(price)).trigger("input");
        
        // Set quantity type if available
        if (quantityTypeId) {
            $row.find(".quantity_type_id").val(quantityTypeId).trigger('change');
        }
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
                            // category_id: categoryId,
                            price: price,
                            quantity: quantity,
                            quantity_type_id: quantity_type_id,
                            total: total,
                        });
                    }

                    totalAmount += total;
                    $(this).find(".total-input").val(total.toFixed(2));
                });

                // ✅ Update Total Product Amount
                $("#total-product-amount").text(totalAmount.toFixed(2));

                let discountPercent = parseFloat($("#discount").val()) || 0;
                let discountAmount = (totalAmount * discountPercent) / 100;
                let afterDiscount = totalAmount - discountAmount;

                let shipping = parseFloat($("#shipping").val()) || 0;

                // ✅ GST Option
                const gstOption = $('input[name="gst_option"]:checked').val();
                let totalTaxAmount = 0;

                if (gstOption === 'with_gst') {
                    // ✅ Show tax section
                    $('.tax-section').show();

                    // ✅ Calculate tax on after-discount
                    $('.tax-amount').each(function() {
                        const taxRate = parseFloat($(this).data('rate')) || 0;
                        const taxAmount = afterDiscount * (taxRate / 100);
                        $(this).text(taxAmount.toFixed(2));
                        totalTaxAmount += taxAmount;
                    });
                } else {
                    // ✅ Hide tax section & reset values
                    $('.tax-section').hide();
                    totalTaxAmount = 0;
                    // $('.tax-amount').text("0.00");
                }

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

            // ✅ Recalculate on GST toggle
            $(document).on('change', 'input[name="gst_option"]', function() {
                calculateTotal();
            });

            // ✅ Recalculate when inputs change
            $(document).on("input", "#discount, #shipping, .price-input, .quantity-input", function() {
                calculateTotal();
            });
            calculateTotal();


            function validateForm() {
                let isValid = true;

                // Clear previous errors
                $(".error").text("");

                // Validate Vendor
                if ($("#vendor_name").val() === "") {
                    $("#vendor_name").closest(".form-group").find(".error").text("Vendor is required.");
                    isValid = false;
                }

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
                    url: "/api/purchase_get/" + invoiceId,
                    type: "GET",
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.success) {
                            let taxes = response.taxes; // ✅ Get taxes from API response
                            let data = response.data;

                            $("#vendor_phone").val(data.vendor_phone);

                            // ✅ Set Shipping Cost
                            $("#shipping").val(parseInt(data.shipping));
                            $("#shipping-amount").text(data.shipping);

                            // ✅ Set Purchase Status
                            $("select[name='status']").val(data.status);

                            // ✅ Set Total Product Amount
                            $("#total-product-amount").text(data.total_amount);

                            // ✅ Populate vendor details
                            $("#bill_no").val(data.bill_no);
                            $("#vendor_name").val(data.vendor_id).trigger("change");
                            $(".payment_status").val(data.payment_status).trigger("change");
                            $("#vendor_phone").val(data.vendor_phone);
                            if (data.gst_option) {
                                $("input[name='gst_option'][value='" + data.gst_option + "']")
                                    .prop("checked", true)
                                    .trigger("change"); // 🔥 This triggers GST toggle
                            }


                            // let totalTaxAmount = 0; // Initialize total tax amount

                            // // ✅ Update tax amounts in the existing Blade template
                            // taxes.forEach(function(tax) {
                            //     let taxRate = parseFloat(tax.rate);
                            //     let taxAmount = (parseFloat(data.total_amount) * taxRate) / 100;
                            //     totalTaxAmount += taxAmount;

                            //     // ✅ Update tax amount in the respective span
                            //     $("#tax-" + tax.id).text(taxAmount.toFixed(2));
                            // });

                            // // ✅ Calculate Grand Total
                            // let grandTotalfinal = parseFloat(data.grand_total) + totalTaxAmount;
                            // $("#grand-total").text(grandTotalfinal.toFixed(2));
                            // ✅ Show Total Product Amount from DB
                            $("#total-product-amount").text(parseFloat(data.total_amount).toFixed(2));

                            // ✅ Show Tax Amounts from DB
                           // ✅ Show Tax Amounts from DB
                            if (data.gst_option === "with_gst") {
                                $(".tax-section").show();

                                taxes.forEach(function(tax) {
                                    let $el = $("#tax-" + tax.id);
                                    if ($el.length) {
                                        $el.text(parseFloat(tax.amount).toFixed(2));
                                        $el.attr("data-rate", tax.rate); // keep rate synced
                                    }
                                });
                            } else {
                                $(".tax-section").hide();
                            }


                            // ✅ Show Grand Total from DB (already includes taxes)
                            $("#grand-total").text(parseFloat(data.grand_total).toFixed(2));


                            // ✅ Split product data
                            let productIds = data.product_ids.split(", ");
                            let productNames = data.product_names.split(", ");
                            let productPrices = data.product_prices.split(", ");
                            let productQuantities = data.product_quantities.split(", ");
                            let quantityTypeIds = data.quantity_type_ids.split(", ");
                            // let categoryIds = data.category_ids.split(", ");
                            // let categoryNames = data.category_names.split(", ");
                            let qtNames = data.quantity_type_names.split(", ");



                            // ✅ Clear previous product rows
                            $("#form-container").empty();

                            // ✅ Loop through products & add rows
                                                                // <div class="col-lg-2 col-sm-12 col-6">
                                                                //     <div class="form-group">
                                                                //         <label>Category Name</label>
                                                                //         <select name="category_name[]" class="form-control select2 category-select" disabled>
                                                                //             <option value="${categoryIds[i]}" selected>${categoryNames[i]}</option>
                                                                //         </select>
                                                                //     </div>
                                                                // </div>
                            for (let i = 0; i < productIds.length; i++) {
                                let rowHtml = `
                                                            <div class="row form-row">                                                                
                                                                <div class="col-lg-3 col-sm-12 col-6">
                                                                    <div class="form-group">
                                                                        <label>Product Name</label>
                                                                        <select name="product_name[]" class="form-control select2 product-select">
                                                                            <option value="${productIds[i]}" selected>${productNames[i]}</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-2 col-sm-12 col-6">
                                                                    <div class="form-group">
                                                                        <label>Price</label>
                                                                        <input type="number" name="price[]" class="form-control price-input"
                                                                            value="${parseInt(productPrices[i])}" min="0">
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-2 col-sm-12 col-6">
                                                                    <div class="form-group">
                                                                        <label>Quantity Type</label>
                                                                            <select class="select select2 quantity_type_id" name="quantity_type_id[]">
                                                                                  <option value="${quantityTypeIds[i]}" selected>${qtNames[i]}</option>       
                                                                            </select>                                                                            
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
                                                                <div class="col-lg-1 col-sm-12 add-row-btn">
                                                                    <button type="button" class="btn btn-danger remove-row">-</button>
                                                                </div>
                                                            </div>`;

                                $("#form-container").append(rowHtml);
                                $("#form-container .select2, #form-container .quantity_type_id").select2({
                                    tags: true,
                                });
                                // Ensure disabled fields remain disabled after Select2 init
                                $("#form-container .quantity_type_id:disabled").each(function() {
                                    $(this).prop("disabled", true); // Keep in DOM
                                    $(this).next(".select2-container").addClass("select2-disabled"); // Disable UI
                                });
                            }
                            calculateTotal();

                        } else {
                            Swal.fire("Error!", "Purchase not found!", "error");
                        }
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", "Purchase not found!", "error");
                        // window.location.href = "{{ route('purchase.lists') }}";
                    }
                });
            }
// function loadPurchaseData(invoiceId) {
//     var authToken = localStorage.getItem("authToken");
//     const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

//     $.ajax({
//         url: "/api/purchase_get/" + invoiceId,
//         type: "GET",
//         data: {
//             selectedSubAdminId: selectedSubAdminId
//         },
//         dataType: "json",
//         headers: {
//             "Authorization": "Bearer " + authToken,
//         },
//         success: function(response) {
//             if (response.success) {
//                 console.log("Purchase Data:", response.data); // Debug log
//                 let data = response.data;

//                 // ✅ Set Vendor
//                 $("#vendor_name").val(data.vendor_id).trigger("change");
                
//                 // ✅ Set Vendor Phone
//                 if (data.vendor_phone) {
//                     $("#vendor_phone").val(data.vendor_phone);
//                 }
                
//                 // ✅ Set Payment Status
//                 if (data.payment_status) {
//                     $("select[name='payment_status']").val(data.payment_status);
//                 }
                
//                 // ✅ Set Shipping Cost
//                 $("#shipping").val(parseFloat(data.shipping));
//                 $("#shipping-amount").text(parseFloat(data.shipping).toFixed(2));
                
//                 // ✅ Set Purchase Status
//                 $("select[name='status']").val(data.status);
                
//                 // ✅ Set GST Option
//                 if (data.gst_option) {
//                     $("input[name='gst_option'][value='" + data.gst_option + "']")
//                         .prop("checked", true)
//                         .trigger("change");
//                 }

//                 // ✅ Set Total Product Amount
//                 $("#total-product-amount").text(parseFloat(data.total_amount).toFixed(2));
                
//                 // ✅ Set Grand Total
//                 $("#grand-total").text(parseFloat(data.grand_total).toFixed(2));

//                 // ✅ Split product data
//                 let productIds = data.product_ids.split(", ");
//                 let productNames = data.product_names.split(", ");
//                 let productPrices = data.product_prices.split(", ");
//                 let productQuantities = data.product_quantities.split(", ");
//                 let quantityTypeIds = data.quantity_type_ids.split(", ");
//                 let qtNames = data.quantity_type_names.split(", ");

//                 // ✅ Clear all existing rows except the first one
//                 $("#form-container").empty();
                
//                 // ✅ Update the FIRST row (the existing one in HTML)
//                 let firstRow = $(".form-row:first");
                
//                 // Check if we have at least one product
//                 if (productIds.length > 0 && productIds[0]) {
//                     // Set product - IMPORTANT: Don't clear options, just set value
//                     firstRow.find(".product-select").val(productIds[0]).trigger('change.select2');
                    
//                     // Set price
//                     firstRow.find(".price-input").val(parseFloat(productPrices[0])).trigger('input');
                    
//                     // Set quantity type
//                     firstRow.find(".quantity_type_id").val(quantityTypeIds[0]).trigger('change.select2');
                    
//                     // Set quantity
//                     firstRow.find(".quantity-input").val(productQuantities[0]).trigger('input');
                    
//                     // Set total (price * quantity)
//                     let total = (parseFloat(productPrices[0]) * parseInt(productQuantities[0])).toFixed(2);
//                     firstRow.find(".total-input").val(total);
//                 }

//                 // ✅ Add additional rows for remaining products (if any)
//                 for (let i = 1; i < productIds.length; i++) {
//                     let rowHtml = `
//                         <div class="row form-row">
//                             <div class="col-lg-3 col-sm-12 col-6">
//                                 <div class="form-group">
//                                     <label>Product Name</label>
//                                     <select name="product_name[]" class="form-control select2 product-select">
//                                         <option value="">Product Name</option>
//                                         @foreach ($products as $product)
//                                             <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-quantity-type="{{ $product->quantity_type_id }}">{{ $product->name }}</option>
//                                         @endforeach
//                                     </select>
//                                 </div>
//                             </div>
//                             <div class="col-lg-2 col-sm-12 col-6">
//                                 <div class="form-group">
//                                     <label>Price</label>
//                                     <input type="number" name="price[]" class="form-control price-input"
//                                         value="${parseFloat(productPrices[i])}" min="0">
//                                 </div>
//                             </div>
//                             <div class="col-lg-2 col-sm-12 col-6">
//                                 <div class="form-group">
//                                     <label>Quantity Type</label>
//                                     <select class="select select2 quantity_type_id" name="quantity_type_id[]">
//                                         <option value="">Choose Quantity Type</option>
//                                         @foreach ($quantity_types as $qty_type)
//                                             <option value="{{ $qty_type->id }}" data-price="{{ $qty_type->price }}">
//                                                 {{ $qty_type->quantity_type }}
//                                             </option>
//                                         @endforeach
//                                     </select>                                                                            
//                                 </div>
//                             </div>
//                             <div class="col-lg-2 col-sm-12 col-6">
//                                 <div class="form-group">
//                                     <label>Product Quantity</label>
//                                     <input type="number" name="quantity[]" class="form-control quantity-input"
//                                         value="${productQuantities[i]}" min="0">
//                                 </div>
//                             </div>                                                                 
//                             <div class="col-lg-2 col-sm-12 col-12">
//                                 <div class="form-group">
//                                     <label>Total Amount</label>
//                                     <input type="text" name="total[]" class="form-control total-input"
//                                         value="${(parseFloat(productPrices[i]) * parseInt(productQuantities[i])).toFixed(2)}" readonly>
//                                 </div>
//                             </div>
//                             <div class="col-lg-1 col-sm-12 add-row-btn">
//                                 <button type="button" class="btn btn-danger remove-row">-</button>
//                             </div>
//                         </div>`;

//                     $("#form-container").append(rowHtml);
                    
//                     // Set values for the newly added row
//                     let newRow = $("#form-container .form-row:last");
//                     newRow.find(".product-select").val(productIds[i]).trigger('change');
//                     newRow.find(".quantity_type_id").val(quantityTypeIds[i]).trigger('change');
//                 }
                
//                 // Reinitialize Select2 for all elements
//                 $(".product-select, .quantity_type_id").select2({
//                     tags: true
//                 });
                
//                 // Calculate totals
//                 calculateTotal();

//                 console.log("Form populated successfully");

//             } else {
//                 Swal.fire("Error!", "Purchase not found!", "error");
//             }
//         },
//         error: function(xhr) {
//             console.error("Error loading purchase:", xhr.responseText);
//             Swal.fire("Error!", "Failed to load purchase data!", "error");
//         }
//     });
// }

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
                    $btn.html(originalContent).css("pointer-events", "auto");
                    return;
                }
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                let formData = calculateTotal();
                formData.vendor_id = $("#vendor_name").val();
                formData.vendor_phone = $("#vendor_phone").val();
                formData.status = $("select[name='status']").val();
                formData.payment_status = $("select[name='payment_status']").val();

                // ✅ Collect Tax Data
                let taxArray = [];
                $(".tax-amount").each(function() {
                    let taxId = $(this).attr("id").split("-")[1];
                    let taxRate = parseFloat($(this).data("rate"));
                    let taxAmount = parseFloat($(this).text());

                    taxArray.push({
                        id: taxId,
                        rate: taxRate,
                        amount: taxAmount,
                    });
                });

                formData.taxes = taxArray;

                $.ajax({
                    url: "/api/purchase_update/" + invoiceId,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    contentType: "application/json",
                    data: JSON.stringify({
                        vendor_id: formData.vendor_id,
                        vendor_phone: formData.vendor_phone,
                        bill_no: $("#bill_no").val(),
                        status: formData.status,
                        payment_status: formData.payment_status,
                        discount: formData.discount,
                        shipping: formData.shipping,
                        grand_total: formData.grandTotal,
                        taxes: formData.taxes,
                        products: formData.products,
                        gst_option: formData.gstOption,
                        selectedSubAdminId: selectedSubAdminId
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
                                        "{{ route('purchase.lists') }}";
                                }
                            });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.html(originalText).prop('disabled', false);


                        console.error(xhr.responseText);
                    }
                });
            });



            $(document).on("input", ".price-input, .quantity-input", function() {
                let value = parseInt($(this).val()) || 0;
                if (value < 0) value = 0; // no negatives
                $(this).val(value);
            });

            calculateTotal();
        });
    </script>
   
</script>
@endpush
        