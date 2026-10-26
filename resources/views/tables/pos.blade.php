@extends('layout.app')

@section('title', 'Add Sale')

@section('content')
<style>
    .setvaluecash ul li a {
        border: 1px solid #e9ecef;
        color: #000;
        font-size: 12px;
        font-weight: 600;
        min-height: 59px;
        border-radius: 5px;
        padding: 8px 7px;
    }

    .disabled-product {
        opacity: 0.6;
        pointer-events: none;
        /* disables clicking inside */
    }

    .paymentmethod.active {
        /* background-color: #1b2850; */
        color: white;
        /* border-radius: 5px; */
    }

    .paymentmethod.active:hover {
        /* background-color: white; */
        color: white;
        /* Dark text for contrast */
        /* border: 1px solid #1b2850; */
        /* Optional: border to define shape */
    }

    .tabs_wrapper ul.tabs {
        display: flex;
        overflow-x: auto;
        gap: 10px;
        padding: 0;
        margin: 0;
        list-style: none;
        scrollbar-width: thin;
        width: auto;
        scrollbar-color: #ff9f43 #f1f1f1;
    }

    .tabs_wrapper ul.tabs::-webkit-scrollbar {
        display: none;
    }

    .tabs_wrapper ul.tabs li {
        flex: 0 0 auto;
        cursor: pointer;
        white-space: nowrap;
        width: auto;
        /* ðŸ‘ˆ Prevent tab name from wrapping */
        padding: 0;
    }

    .product-details {
        background: #fff;
        padding: 10px 16px;
        box-shadow: none;
        border: none !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: 0.3s ease;
        min-width: max-content;
        /* ðŸ‘ˆ Ensure box width fits content */
    }

    .product-details h6 {
        font-size: 14px;
        color: #000;
        margin: 0;
        text-align: center;
        white-space: nowrap;
        /* ðŸ‘ˆ Prevent text wrap */
        overflow: hidden;
        text-overflow: ellipsis;
        /* ðŸ‘ˆ Optional: show ... if too long */
        max-width: 100%;
        /* ðŸ‘ˆ Avoid overflow outside parent */
    }

    .tabs_wrapper ul.tabs li.active .product-details {
        background: transparent;
        /* border-bottom: 2px solid #ff9f43 !important; */
    }

    .tabs_wrapper ul.tabs li.active .product-details h6 {
        color: #ff9f43;
    }

    .payment_panel {
        position: fixed;
        bottom: 0;
        background-color: white;
        width: 27.5%;
    }

    .body_space {
        /*margin-bottom: 2rem;*/
    }

    .paymentmethod.active {
        background-color: #1b2850;
        color: #ffffff;
    }

    .paymentmethod.active svg {
        fill: #ffffff;
    }

    .productsetimgin {
        height: 100px;
        object-fit: contain;
        margin: 1rem auto 0;
        display: block;
    }

    .productsetbtn button {
        display: none;
    }

    .search_custom_product {
        width: 100%;
    }

    .payment_panel {
        z-index: 999;
    }

    @media screen and (max-width: 768px) {
        .payment_panel {
            position: fixed;
            bottom: 50px;
            background-color: white;
            width: 80%;
            margin-left: -6px;
        }

        .productset {
            display: flex;
        }

        .productset .productsetimg {

            width: 30%;
        }

        .productsetimgin {
            object-fit: cover;
            height: 90px;
            margin: 0;
            max-width: 110px;
        }

        .productsetcontent {
            text-align: left !important;
        }



        .productsetbtn {
            position: absolute;
            right: 10px;
            top: 43px;
        }

        .productsetbtn button {
            display: block;
            width: 100%;
            height: 33px;
            background-color: #1b2850;
            color: white !important;
            border: none;
            border-radius: 5px;
        }

        .productsetbtn button:active {
            color: white;
        }

        .product-lists {
            flex-direction: row !important;
        }

        .body_space_two {
            margin-bottom: 4rem;
        }

        a.confirm-text.remove-item {
            /* font-size: 116px; */
            margin: 6px;
        }
    }
</style>
<div class="content">

    <div class="row">
        <div class="col-lg-7 col-sm-12 tabs_wrapper">

            @php
            $hasProducts = false; // start as false
            @endphp

            <ul class="tabs border-0 mb-4">
                @foreach ($categories as $cat)
                @php
                $availableProducts = $cat->products->where('quantity', '>', 0);
                @endphp

                @if ($availableProducts->count() > 0)
                @php
                $hasProducts = true; // at least one product exists
                @endphp
                <li id="{{ $cat->id }}">
                    <div class="product-details">
                        <h6 style="text-transform: capitalize;">{{ $cat->name }}</h6>
                    </div>
                </li>
                @endif
                @endforeach
            </ul>

            @if (!$hasProducts)
            <div class="product-details text-center">
                <h6>No data available</h6>
                <a href="{{ url('add-product') }}" class="btn btn-primary mt-2">
                    + Add Product
                </a>
            </div>
            @endif
            <div class="tabs_container">

            </div>
        </div>
        <div class="col-lg-5 col-sm-12 ">

            <div class="card card-order">
                <div class="card-body body_space">
                    <div class="gst-header mb-2"
                        style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="d-flex">
                            <label class="custom-radio-label" style="margin-right: 1rem;">
                                <input type="radio" name="gst_option" id="without_gst" value="without" checked />
                                Without GST
                            </label>

                            <label class="custom-radio-label">
                                <input type="radio" name="gst_option" id="with_gst" value="with" />
                                With GST
                            </label>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-lg-12">
                            <div
                                class="header-search d-flex align-items-center position-relative me-3 mb-3 search_custom_product">
                                <!-- Search Icon -->
                                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/search.svg' }}" alt="Search"
                                    style="position: absolute; left: 12px; width: 18px; height: 18px; z-index: 10; opacity: 0.6;">

                                <!-- Input Field -->
                                <input type="text" id="customerSearch1"
                                    class="form-control form-control-sm rounded px-3 ps-5" placeholder="Search..."
                                    autocomplete="off" style="height: 38px; font-size: 14px;">

                                <!-- Search Results -->
                                <div id="searchResults1"
                                    class="list-group bg-white position-absolute rounded shadow mt-1 w-100"
                                    style="z-index: 1050; max-height: 300px; overflow-y: auto; display: none; top: 100%; left: 0;">
                                </div>
                            </div>
                            <div class="row">
                                <!-- Customer Name -->
                                <div class="col-md-6">
                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <label>Table No</label>
                                            <select id="customer_name" name="customer_name" style="z-index:1;"
                                                class="form-control select2">
                                                <option value="">Select Table No</option>
                                                @foreach ($customers as $username)
                                                <option value="{{ $username->id }}"
                                                    data-phone="{{ $username->phone }}">
                                                    {{ $username->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <span class="error_customername text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Phone -->
                                <div class="col-md-6">
                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <label>Order Code</label>
                                            <input type="tel" id="customer_phone" class="form-control"
                                                placeholder="Order Code" name="customer_phone">
                                            <span class="error_customerphone text-danger"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.getElementById('customer_name').addEventListener('change', function() {
                            var phone = this.options[this.selectedIndex].getAttribute('data-phone');
                            document.getElementById('customer_phone').value = phone || '';
                        });
                    </script>
                    <div class="split-card">

                    </div>
                    <div class="card-body pt-0">
                        <div class="totalitem">
                            <h4>Total items : 4</h4>
                            <a href="javascript:void(0);" class="clear_items">Clear all</a>
                        </div>
                        <div class="product-table">

                        </div>
                    </div>
                    <div class="split-card">
                    </div>
                    <div class="card-body pt-0 pb-2 body_space_two">
                        <div class="col-lg-12">
                            <div class="select-split ">
                                <div class="select-group w-100">
                                    <label for="discount">Discount (%)</label>
                                    <input type="number" class="form-control" placeholder="Discount %" name="discount"
                                        id="discount">
                                    <span class="error_discount"></span>
                                </div>
                            </div>
                        </div>
                        <div class="setvalue">
                            <ul>
                                <li>

                                    <h5>Subtotal</h5>
                                    <h6 style="color: green;">
                                        @if ($currency_position === 'right')
                                        <span class="subtotal-value">0.00</span>{{ $currency_symbol }}
                                        @else
                                        {{ $currency_symbol }}<span class="subtotal-value">0.00</span>
                                        @endif
                                    </h6>
                                    <input type="hidden" name="subtotal" value="" class="tax-hidden">
                                </li>

                                <li>
                                    <h5>Discount Amount</h5>
                                    <h6 class="discount-amount">
                                        @if ($currency_position === 'right')
                                        <span>0.00</span>{{ $currency_symbol }}
                                        @else
                                        {{ $currency_symbol }}<span>0.00</span>
                                        @endif
                                    </h6>
                                    <input type="hidden" name="discount_amount" value="0.00" class="tax-hidden">
                                </li>

                                <li>
                                    <h5>Price After Discount</h5>
                                    <h6 class="price-after-discount">
                                        @if ($currency_position === 'right')
                                        <span>0.00</span>{{ $currency_symbol }}
                                        @else
                                        {{ $currency_symbol }}<span>0.00</span>
                                        @endif
                                    </h6>
                                    <input type="hidden" name="price_after_discount" value="0.00" class="tax-hidden">
                                </li>

                                @foreach ($taxRates as $tax)
                                <li class="taxList">
                                    <h5>{{ $tax->tax_name }} ({{ $tax->tax_rate }}%) Tax</h5>
                                    <h6 class="tax-value" data-rate="{{ $tax->tax_rate }}"
                                        data-symbol="{{ $currency_symbol }}"
                                        data-position="{{ $currency_position }}">
                                        @if ($currency_position === 'right')
                                        <span>0.00</span>{{ $currency_symbol }}
                                        @else
                                        {{ $currency_symbol }}<span>0.00</span>
                                        @endif
                                    </h6>
                                </li>
                                @endforeach

                                <li class="total-value">
                                    <h5>Total</h5>
                                    <h6>
                                        @if ($currency_position === 'right')
                                        <span class="total-amount">0.00</span>{{ $currency_symbol }}
                                        @else
                                        {{ $currency_symbol }}<span class="total-amount">0.00</span>
                                        @endif
                                    </h6>
                                    <input type="hidden" name="total" value="" class="tax-hidden">
                                </li>

                                <li class="error-message-container">
                                    <span class="error_total" style="color:red; display:none;"></span>
                                </li>
                            </ul>
                        </div>
                        <div class="payment_panel">
                            <div class="setvaluecash d-none">
                                <ul>
                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="pending" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                <path
                                                    d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                            </svg>
                                            Pay Later
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="cash" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                <path
                                                    d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                            </svg>
                                            Cash
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="debit card" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-credit-card" viewBox="0 0 16 16">
                                                <path
                                                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z" />
                                                <path
                                                    d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z" />
                                            </svg>
                                            Debit
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="cash+online" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                <path
                                                    d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                            </svg>
                                            Cash+Online
                                        </a>
                                    </li>

                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="scan" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-qr-code-scan" viewBox="0 0 16 16">
                                                <path
                                                    d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z" />
                                                <path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z" />
                                                <path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z" />
                                                <path
                                                    d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z" />
                                                <path d="M12 9h2V8h-2z" />
                                            </svg>
                                            Scan
                                        </a>
                                    </li>

                                </ul>
                                <div id="cashOnlineBox" style="display:none; margin-top:10px;">
                                    <div class="form-group">
                                        <label>Cash Amount</label>
                                        <input type="number" class="form-control" id="cashAmount"
                                            placeholder="Enter Cash Amount" placeholder="0">
                                    </div>
                                    <div class="form-group">
                                        <label>Online Amount</label>
                                        <input type="number" class="form-control" id="onlineAmount"
                                            placeholder="Enter Online Amount" value="0" readonly>
                                    </div>
                                </div>
                                <span class="error_peymentmethod"></span>
                            </div>

                            <div class="btn-totallabel">
                                <h6>Total Amount : 60.00$</h6>
                                <h5>Pay Now <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"
                                        style="margin-bottom: 0.1rem;">
                                        <path fill-rule="evenodd"
                                            d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" />
                                    </svg></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection
    @push('js')
    <script>
        let selectedItems = new Map();
        document.querySelectorAll('.paymentmethod').forEach(el => {
            el.addEventListener('click', function() {
                document.querySelectorAll('input[name="payment_method"]').forEach(radio => radio.checked =
                    false);
                this.querySelector('input[type="radio"]').checked = true;
                document.querySelectorAll('.paymentmethod').forEach(item => item.classList.remove(
                    'active'));
                this.classList.add('active');
            });
        });
        $(document).ready(function() {
            $(".paymentmethod").on("click", function() {
                let value = $(this).find("input[name='payment_method']").val();

                $("input[name='payment_method']").prop("checked", false);
                $(this).find("input[name='payment_method']").prop("checked", true);

                if (value === "cash+online") {
                    $("#cashOnlineBox").slideDown(); // show textboxes
                } else {
                    $("#cashOnlineBox").slideUp(); // hide textboxes
                }
            });

        });

        $(document).ready(function() {
            selectedItems = new Map();
            var authToken = localStorage.getItem("authToken");

            // Initialize tax rows as hidden on page load
            $(".taxList").hide();

            function formatCurrency(amount, symbol, position) {
                return position === 'right' ? amount + symbol : symbol + amount;
            }

            // function calculateTotals() {
            //     const firstTaxElement = $(".tax-value").first();
            //     const currencySymbol = firstTaxElement.data("symbol") || '{{ $currency_symbol }}'; // fallback
            //     const currencyPosition = firstTaxElement.data("position") || '{{ $currency_position }}'; // fallback

            //     let subtotal = 0;

            //     // Calculate subtotal from selected items
            //     selectedItems.forEach(function (item) {
            //         const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
            //         subtotal += price * item.quantity;
            //     });

            //     // Update subtotal display
            //     $(".setvalue li:nth-child(1) h6").html(formatCurrency(subtotal.toFixed(2), currencySymbol, currencyPosition));
            //     $("input[name='subtotal']").val(subtotal.toFixed(2));

            //     if (subtotal > 0) {
            //         let totalTax = 0;
            //         let gstOption = $("input[name='gst_option']:checked").val();

            //         // $(".taxList").each(function () {
            //         //     const taxRate = parseFloat($(this).find(".tax-value").data("rate")) / 100;
            //         //     const taxAmount = subtotal * taxRate;

            //         //     const taxEl = $(this).find(".tax-value");
            //         //     const symbol = taxEl.data("symbol") || currencySymbol;
            //         //     const position = taxEl.data("position") || currencyPosition;

            //         //     taxEl.html(formatCurrency(taxAmount.toFixed(2), symbol, position));
            //         //     $(this).show();

            //         //     totalTax += taxAmount;
            //         // });
            //         if (gstOption === "with") {
            //             $(".taxList").each(function () {
            //                 const taxRate = parseFloat($(this).find(".tax-value").data("rate")) / 100;
            //                 const taxAmount = subtotal * taxRate;

            //                 const taxEl = $(this).find(".tax-value");
            //                 const symbol = taxEl.data("symbol") || currencySymbol;
            //                 const position = taxEl.data("position") || currencyPosition;

            //                 taxEl.html(formatCurrency(taxAmount.toFixed(2), symbol, position));
            //                 $(this).show();

            //                 totalTax += taxAmount;
            //             });
            //         } else {
            //             // Hide and reset tax values if GST = without
            //             $(".taxList").hide().find(".tax-value").html(formatCurrency("0.00", currencySymbol, currencyPosition));
            //             totalTax = 0;
            //         }

            //         const discountPercentage = parseFloat($("#discount").val()) || 0;
            //         const discountAmount = subtotal * (discountPercentage / 100);
            //         const priceAfterDiscount = subtotal - discountAmount;
            //         const total = subtotal + totalTax - discountAmount;
            //         $("#onlineAmount").val(total.toFixed(2));

            //         // $("input[name='payment_method']").on("change", function(){
            //         //     if($(this).val() === "cash+online"){
            //         //         $("#onlineAmount").prop("disabled", true).val(total.toFixed(2)); // default full total
            //         //         $("#cashAmount").val(""); // reset cash
            //         //     }else{
            //         //         $("#onlineAmount").prop("disabled", false).val("");
            //         //         $("#cashAmount").val("");
            //         //     }
            //         // });

            //         $("#cashAmount").on("input", function(){
            //             let cash = parseFloat($(this).val()) || 0;
            //             let remaining = total - cash;

            //             $("#paymentError").remove();

            //             if(remaining < 0){
            //                 $("#cashOnlineBox").append(
            //                     '<span id="paymentError" style="color:red; font-size:13px;">' +
            //                     'Cash cannot be greater than ' +
            //                     formatCurrency(total.toFixed(2), currencySymbol, currencyPosition) +
            //                     '</span>'
            //                 );
            //                 $("#onlineAmount").val(0);
            //             }else{
            //                 $("#onlineAmount").val(remaining.toFixed(2));
            //             }
            //         });

            //         $(".discount-amount").html(formatCurrency(discountAmount.toFixed(2), currencySymbol, currencyPosition));
            //         $("input[name='discount_amount']").val(discountAmount.toFixed(2));

            //         $(".price-after-discount").html(formatCurrency(priceAfterDiscount.toFixed(2), currencySymbol, currencyPosition));
            //         $("input[name='price_after_discount']").val(priceAfterDiscount.toFixed(2));

            //         $(".total-value h6").html(formatCurrency(total.toFixed(2), currencySymbol, currencyPosition));
            //         $(".btn-totallabel h6").html('Total Amount : ' + formatCurrency(total.toFixed(2), currencySymbol, currencyPosition));
            //         $("input[name='total']").val(total.toFixed(2));
            //     } else {
            //         $(".taxList").hide().find("h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
            //         $(".discount-amount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
            //         $(".price-after-discount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
            //         $(".total-value h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
            //         $(".btn-totallabel h6").html('Total Amount : ' + formatCurrency("0.00", currencySymbol, currencyPosition));

            //         $("input[name='discount_amount']").val("0.00");
            //         $("input[name='price_after_discount']").val("0.00");
            //         $("input[name='total']").val("0.00");
            //     }

            // }
            function calculateTotals() {
                const firstTaxElement = $(".tax-value").first();
                const currencySymbol = firstTaxElement.data("symbol") || '{{ $currency_symbol }}'; // fallback
                const currencyPosition = firstTaxElement.data("position") ||
                    '{{ $currency_position }}'; // fallback

                let subtotal = 0;

                // Calculate subtotal from selected items
                selectedItems.forEach(function(item) {
                    const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                    subtotal += price * item.quantity;
                });

                // Update subtotal display
                $(".setvalue li:nth-child(1) h6").html(
                    formatCurrency(subtotal.toFixed(2), currencySymbol, currencyPosition)
                );
                $("input[name='subtotal']").val(subtotal.toFixed(2));

                if (subtotal > 0) {
                    // ✅ Calculate discount & price after discount FIRST
                    const discountPercentage = parseFloat($("#discount").val()) || 0;
                    const discountAmount = subtotal * (discountPercentage / 100);
                    const priceAfterDiscount = subtotal - discountAmount;

                    let totalTax = 0;
                    const gstOption = $("input[name='gst_option']:checked").val();

                    if (gstOption === "with") {
                        $(".taxList").each(function() {
                            const taxRate = parseFloat($(this).find(".tax-value").data("rate")) / 100;

                            // ✅ Tax on price after discount
                            const taxAmount = priceAfterDiscount * taxRate;

                            const taxEl = $(this).find(".tax-value");
                            const symbol = taxEl.data("symbol") || currencySymbol;
                            const position = taxEl.data("position") || currencyPosition;

                            taxEl.html(formatCurrency(taxAmount.toFixed(2), symbol, position));
                            $(this).show();

                            totalTax += taxAmount;
                        });
                    } else {
                        // Hide and reset tax values if GST = without
                        $(".taxList")
                            .hide()
                            .find(".tax-value")
                            .html(formatCurrency("0.00", currencySymbol, currencyPosition));
                        totalTax = 0;
                    }

                    // ✅ Final total (price after discount + tax + shipping)
                    const shipping = parseFloat($("#shipping").val()) || 0;
                    const total = priceAfterDiscount + totalTax + shipping;
                    $("#cashAmount").on("input", function() {
                        let cash = parseFloat($(this).val()) || 0;
                        let remaining = total - cash;

                        $("#paymentError").remove();

                        if (remaining < 0) {
                            $("#cashOnlineBox").append(
                                '<span id="paymentError" style="color:red; font-size:13px;">' +
                                'Cash cannot be greater than ' +
                                formatCurrency(total.toFixed(2), currencySymbol, currencyPosition) +
                                '</span>'
                            );
                            $("#onlineAmount").val(0);
                        } else {
                            $("#onlineAmount").val(remaining.toFixed(2));
                        }
                    });
                    // Update values
                    $(".discount-amount").html(formatCurrency(discountAmount.toFixed(2), currencySymbol,
                        currencyPosition));
                    $("input[name='discount_amount']").val(discountAmount.toFixed(2));

                    $(".price-after-discount").html(formatCurrency(priceAfterDiscount.toFixed(2), currencySymbol,
                        currencyPosition));
                    $("input[name='price_after_discount']").val(priceAfterDiscount.toFixed(2));

                    $(".total-value h6").html(formatCurrency(total.toFixed(2), currencySymbol, currencyPosition));
                    $(".btn-totallabel h6").html('Total Amount : ' + formatCurrency(total.toFixed(2),
                        currencySymbol, currencyPosition));
                    $("input[name='total']").val(total.toFixed(2));
                    $("#onlineAmount").val(total.toFixed(2));
                } else {
                    // Reset everything if no subtotal
                    $(".taxList").hide().find("h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                    $(".discount-amount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                    $(".price-after-discount").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                    $(".total-value h6").html(formatCurrency("0.00", currencySymbol, currencyPosition));
                    $(".btn-totallabel h6").html('Total Amount : ' + formatCurrency("0.00", currencySymbol,
                        currencyPosition));

                    $("input[name='discount_amount']").val("0.00");
                    $("input[name='price_after_discount']").val("0.00");
                    $("input[name='total']").val("0.00");
                    $("#onlineAmount").val("0.00");
                }
            }



            window.updateTotalItems = function() {
                $(".totalitem h4").text("Total items : " + selectedItems.size);
                renderSelectedItems();
                calculateTotals();
            }

            function renderSelectedItems() {
                var $productTable = $(".product-table");
                var productHtml = "";

                selectedItems.forEach(function(item) {

                    productHtml += `
                        <ul class="product-lists">
                            <li>
                                <div class="productimg">
                                    <div class="productimgs">
                                        <img src="${item.image}" alt="${item.name}">
                                    </div>
                                    <div class="productcontet">
                                        <h4 style="text-transform: capitalize;">${item.name}
                                            <a href="javascript:void(0);" class="ms-2 edit-item" data-id="${item.id}">
                                                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit-5.svg' }}" alt="edit">
                                            </a>
                                        </h4>

                                        <div class="increment-decrement">
                                            <div class="input-groups">
                                                <input type="button" value="-" class="button-minus dec button" data-id="${item.id}">
                                                <input type="text" name="quantity" value="${item.quantity}" class="quantity-field" data-id="${item.id}" data-stock="${item.stock}">
                                                <input type="button" value="+" class="button-plus inc button" data-id="${item.id}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="quantity_type text-uppercase">${item.quantity_type || item.quantity_type_id}</li>

                            <li class="price">${item.price}</li>
                            <li>
                                <a class="confirm-text remove-item" href="javascript:void(0);" data-id="${item.id}">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete-2.svg' }}" alt="delete">
                                </a>
                            </li>
                        </ul>`;
                });

                $productTable.html(productHtml);
            }

            $(document).on("click", ".productset", function() {
                var $product = $(this);
                var productId = $product.data("id");
                var productName = $product.find("h4").text().trim();
                var productPrice = $product.find(".productsetcontent h6").text().trim();
                var categoryId = $product.find(".productsetcontent h1").text().trim();
                var quantity_type = $product.find(".productsetcontent h2").text().trim();
                var quantity_type123 = $product.find(".productsetcontent h3").text().trim();

                var productImage = $product.find("img").attr("src");
                var productCode = "PT001";
                var $checkIcon = $product.find(".check-product i");
                var productStock = parseInt($product.data("stock")); // assuming data-stock is set
                var quantity_type_id = $product.data("quantity_type_id");
                console.log(quantity_type_id);
                console.log(quantity_type);
                
                var uniqueId = productId;
                selectedItems.set(uniqueId, {
                    id: uniqueId,
                    productId: productId,
                    categoryId: categoryId,
                    name: productName,
                    price: productPrice,
                    image: productImage,
                    code: productCode,
                    quantity_type: quantity_type,
                    quantity_type_id: quantity_type_id,
                    quantity_type123: quantity_type123,
                    quantity: 1,
                    stock: productStock // <-- Add stock

                });

                $checkIcon.addClass("selected");
                updateTotalItems();
            });

            $(document).on("click", ".button-plus", function() {
                var itemId = $(this).data("id");
                if (selectedItems.has(itemId)) {
                    var item = selectedItems.get(itemId);
                    if (item.quantity < item.stock) {
                        item.quantity++;
                        selectedItems.set(itemId, item);
                        updateTotalItems();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock Limit Reached',
                            text: 'Cannot exceed available stock (' + item.stock + ')',
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });

            $(document).on("change", ".quantity-field", function() {
                var itemId = $(this).data("id");
                var newQty = parseInt($(this).val());

                if (selectedItems.has(itemId)) {
                    var item = selectedItems.get(itemId);

                    if (newQty > item.stock) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock Quantity Exceeded',
                            text: 'Only ' + item.stock + ' quantity are available.',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                        newQty = item.stock;
                    } else if (newQty < 1 || isNaN(newQty)) {
                        newQty = 1;
                    }

                    item.quantity = newQty;
                    selectedItems.set(itemId, item);
                    updateTotalItems();
                    $(this).val(newQty); // update input box with valid value
                }
            });

            $(document).on("click", ".button-minus", function() {
                var itemId = $(this).data("id");
                if (selectedItems.has(itemId) && selectedItems.get(itemId).quantity > 1) {
                    selectedItems.get(itemId).quantity--;
                    updateTotalItems();
                }
            });

            $(document).on("click", ".remove-item", function() {
                var itemId = $(this).data("id");
                var item = selectedItems.get(itemId);
                if (item) {
                    $(".productset[data-id='" + item.productId + "'] .check-product i").removeClass(
                        "selected");
                    selectedItems.delete(itemId);
                    updateTotalItems();
                }
            });

            $(".clear_items").click(function() {
                selectedItems.clear();
                $(".check-product i").removeClass("selected");
                updateTotalItems();
            });

            $("#discount").on("input", function() {
                const discountValue = parseFloat($(this).val());

                if ($(this).val() === "") {
                    // If empty, treat as 0 and remove error
                    $(".error_discount").text("");
                    $("#discount").val(""); // keep it blank for UX
                    calculateTotals(); // recalculate with 0 discount
                    return;
                }

                if (discountValue < 0 || discountValue > 100) {
                    $(".error_discount").text("Discount must be between 0-100%").css("color", "red");
                    $(this).val("");
                } else if (isNaN(discountValue)) {
                    $(".error_discount").text("");
                } else {
                    $(".error_discount").text("Discount Applied").css("color", "green");
                    calculateTotals();
                }
            });
            $(document).on("change", "input[name='gst_option']", function() {
                let gstOption = $("input[name='gst_option']:checked").val();

                if (gstOption === "with") {
                    $(".taxList").show();
                } else {
                    $(".taxList").hide();
                }

                calculateTotals(); // recalc totals with/without tax
            });

            $(".btn-totallabel").click(function(event) {
                event.preventDefault();
                var $btn = $(this); // cache the button
                var originalText = $btn.html();
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                // Show loading text and disable the button
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Placing Order...'
                ).prop('disabled', true);

                // Reset errors
                $(".error_customername, .error_customerphone, .error_peymentmethod, .error_discount").text(
                    "");
                $(".total-value .error_total").remove();

                // Get form values
                // let name = $("input[name='customer_name']").val().trim();
                let name = $("#customer_name option:selected").val();
                let phone = $("input[name='customer_phone']").val().trim();
                let selectedPayment = $("input[name='payment_method']:checked").val();
                let subtotal = parseFloat($("input[name='subtotal']").val()) || 0;
                let total = parseFloat($("input[name='total']").val()) || 0;
                let discount = parseFloat($("#discount").val()) || 0;

                // Validation
                let isValid = true;

                // if (name === "") {
                //     $(".error_customername").text("Customer name is required").css("color", "red");
                //     isValid = false;
                // }

                if (phone === "") {
                    // $(".error_customerphone").text("Customer number is required").css("color", "red");
                    // isValid = false;
                } else if (!/^\d{10}$/.test(phone)) {
                    $(".error_customerphone").text("Enter a valid 10-digit phone number").css("color",
                        "red");
                    isValid = false;
                }

                if (!selectedPayment) {
                    $(".error_peymentmethod").text("Please select a payment method").css("color", "red");
                    isValid = false;
                }

                // if (subtotal <= 0) {
                //     $(".total-value").append("<span class='error_total' style='color:red; display:block;'>Please select at least one product</span>");
                //     isValid = false;
                // }
                if (subtotal <= 0) {
                    $(".error_total")
                        .text("Please select at least one product")
                        .show();
                    isValid = false;
                } else {
                    $(".error_total").hide().text("");
                }


                // if (!isValid) return;
                if (!isValid) {
                    $btn.html(originalText).prop('disabled', false); // <-- important line
                    return;
                }


                // Prepare order items data
                let orderItems = [];
                selectedItems.forEach(function(item) {


                    const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                    orderItems.push({
                        product_id: item.productId,
                        quantity: item.quantity,
                        quantity_type_id : item.quantity_type,
                        quantity_type123 : item.quantity_type123,

                        categoryId: item.categoryId,
                        price: price,
                        total: price * item.quantity
                    });
                });

                // Prepare tax data
                // let taxes = [];
                // $(".taxList").each(function () {

                //     const taxName = $(this).find("h5").text().replace(" Tax", "");
                //     const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g, ""));


                //     const taxRate = parseFloat($(this).find(".tax-value").data("rate"));
                //     taxes.push({
                //         tax_name: taxName,
                //         rate: taxRate,
                //         amount: taxAmount
                //     });
                // });
                // Prepare tax data
                let taxes = [];
                let gstOption = $("input[name='gst_option']:checked").val();

                if (gstOption === "with") {
                    $(".taxList").each(function() {
                        const taxName = $(this).find("h5").text().replace(" Tax", "");
                        const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g,
                            "")) || 0;
                        const taxRate = parseFloat($(this).find(".tax-value").data("rate")) || 0;

                        taxes.push({
                            tax_name: taxName,
                            rate: taxRate,
                            amount: taxAmount
                        });
                    });
                } else {
                    taxes = []; // ✅ explicitly empty when "without GST"
                }


                let cash = parseFloat($("#cashAmount").val()) || 0;
                let online = parseFloat($("#onlineAmount").val()) || 0;


                // Prepare order data
                let orderData = {
                    selectedSubAdminId: selectedSubAdminId,
                    customer_id: name,
                    customer_phone: phone,
                    payment_method: selectedPayment,
                    subtotal: subtotal,
                    discount: discount,
                    discount_amount: subtotal * (discount / 100),
                    tax: taxes,
                    gst_option: gstOption,
                    total: total,
                    cash_amount: cash,
                    online_amount: online,
                    items: orderItems
                };

                // Submit order via AJAX
                $.ajax({
                    url: "/api/order_sale",
                    type: "POST",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify(orderData),
                    success: function(response) {
                        // Re-enable and reset the button
                        $btn.html(originalText).prop('disabled', false);
                        if (response.status) {
                            orderId = response.order_id;
                            // alert("Order placed successfully!");
                            // location.reload();
                            Swal.fire({
                                title: "Success",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43" // Set custom button color
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open("/sales/invoice/pdf/" + orderId,
                                        "_blank");
                                    window.location.href = "/sales-invoice/" + orderId;
                                }
                            });
                            // Reset form
                            selectedItems.clear();
                            $(".check-product i").removeClass("selected");
                            $("input[name='customer_name'], input[name='customer_phone']").val(
                                "");
                            $("input[name='payment_method']").prop("checked", false);
                            $(".paymentmethod").removeClass("active");
                            $("#discount").val("");
                            updateTotalItems();
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    // error: function(xhr, status, error) {
                    //     // Re-enable and reset the button
                    //     $btn.html(originalText).prop('disabled', false);

                    //     alert("Error placing order. Please try again.");
                    // }
                    error: function(xhr, status, error) {
                        $btn.html(originalText).prop('disabled', false);

                        // Try to extract error message from response
                        let response = xhr.responseJSON;

                        if (response && response.message && response.message.includes("Duplicate entry")) {
                            // Detect duplicate phone number error
                            $(".error_customerphone").text("This phone number is already registered. Please use another one.").css("color", "red");
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response && response.message ? response.message : "Error placing order. Please try again.",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    }
                });
            });

            $(".owl-carousel").owlCarousel({
                items: 5,
                loop: false,
                nav: true,
                dots: false,
                margin: 10
            });

            // Handle category click
            $(".tabs li").click(function() {
                var categoryId = $(this).attr('id');


                // Remove active class from all tabs and tab contents
                $(".tabs li").removeClass('active');
                $(".tab_content").removeClass('active');

                // Add active class to clicked tab
                $(this).addClass('active');

                // Find the corresponding tab content
                var tabContentSelector = '.tab_content[data-tab="' + categoryId + '"]';
                var $tabContent = $(tabContentSelector);

                if ($tabContent.length) {
                    $tabContent.addClass('active');


                    // Check if content already loaded
                    if ($tabContent.find('.productset').length === 0) {
                        loadProductsByCategory(categoryId);
                    }
                } else {

                    // Create new tab content if not found
                    $('.tabs_container').append('<div class="tab_content active" data-tab="' + categoryId +
                        '"></div>');
                    loadProductsByCategory(categoryId);
                }
            });

            // Function to load products by category
            function loadProductsByCategory(categoryId) {

                $.ajax({
                    url: "/api/getProductsByCategory/" + categoryId,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        const currencySymbol = response.currency_symbol || 'Ã¢â€šÂ¹';
                        const currencyPosition = response.currency_position || 'left';


                        if (response.status) {
                            renderProducts(response.data, categoryId, currencySymbol, currencyPosition);
                        } else {
                            renderProducts([], categoryId, currencySymbol, currencyPosition);
                        }
                    },
                    error: function(xhr, status, error) {

                        renderProducts([], categoryId);
                    }
                });
            }

            // Function to render products
            function renderProducts(products, categoryId, currencySymbol, currencyPosition) {

                var productsHtml = '';

                if (products.length === 0) {
                    productsHtml = '<div class="col-12"><p>No products found in this category</p></div>';
                } else {
                    products.forEach(function(product) {
                        // Default image
                        let productImage =
                            '{{ env('
                        ImagePath ') . ' / admin / assets / img / product / noimage.png ' }}';

                        // Handle different image formats
                        if (product.image) {
                            try {
                                // Clean the image string
                                let cleanImageString = product.image.replace(/\\/g, '');
                                cleanImageString = cleanImageString.replace(/^"(.*)"$/, '$1');

                                let imageBasePath =
                                    '{{ env('
                                ImagePath ') }}'; // or config('app.url') if you prefer

                                if (cleanImageString.startsWith('[')) {
                                    let imagesArray = JSON.parse(cleanImageString);
                                    if (Array.isArray(imagesArray) && imagesArray.length > 0) {
                                        productImage = `${imageBasePath}/storage/${imagesArray[0]}`;
                                    }
                                } else {
                                    productImage = `${imageBasePath}/storage/${cleanImageString}`;
                                }
                            } catch (e) {

                            }
                        }

                        // Format price
                        let price = parseFloat(product.price || 0).toFixed(2);
                        let displayPrice = currencyPosition === 'right' ? `${price}${currencySymbol}` :
                            `${currencySymbol}${price}`;

                        // Check if product is out of stock
                        let outOfStockBadge = '';
                        let disabledClass = '';
                        let addButtonDisabled = '';

                        if (Number(product.quantity) === 0) {
                            outOfStockBadge = `<div style="
                                position: absolute;
                                top: 10px;
                                left: 10px;
                                background: rgba(255,0,0,0.85);
                                color: white;
                                font-weight: 700;
                                padding: 2px 8px;
                                border-radius: 4px;
                                font-size: 12px;
                                z-index: 10;
                                ">Out of Stock</div>`;

                            disabledClass = 'disabled-product'; // optional styling class
                            addButtonDisabled = 'disabled'; // disable button
                        }

                        productsHtml += `
                                    <div class="col-lg-3 col-sm-6 d-flex position-relative">
                                        <div class="productset flex-fill text-center ${disabledClass}" data-id="${product.id}" data-stock="${product.quantity}" style="position: relative;">
                                            ${outOfStockBadge}
                                            <div class="productsetimg">
                                                <img src="${productImage}" alt="${product.name}" class="productsetimgin">
                                                <h6>Qty: ${product.quantity || '0'}</h6>
                                            </div>
                                            <div class="productsetcontent" style="padding: 0.5rem;">
                                                <h4 style="font-size: 14px; font-weight: 600; margin: 0.5rem 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    ${product.name}
                                                </h4>
                                                <h1 class="d-none">${product.categoryId || 'Uncategorized'}</h5>
                                                <h2 class="d-none">${product.quantity_type || 'pcs'}</h5>
                                                <h3 class="d-none">${product.quantity_type_id || 'pcs'}</h5>
                                                
                                                <h5 style="text-transform: capitalize;">${product.category_name || 'Uncategorized'}</h5>
                                                <h6>${displayPrice}</h6>
                                            </div>
                                            <div class="productsetbtn">
                                                <button class="btn btn-added" ${addButtonDisabled}>+ Add</button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                    });
                }

                // Update the tab content
                var $tabContent = $('.tab_content[data-tab="' + categoryId + '"]');
                if ($tabContent.length) {
                    $tabContent.html('<div class="row">' + productsHtml + '</div>');

                } else {

                }
            }



            // Load products for the first category by default
            var firstCategory = $(".tabs li:first");
            if (firstCategory.length) {
                var firstCategoryId = firstCategory.attr('id');
                firstCategory.addClass('active');
                var firstTabContent = $('.tab_content[data-tab="' + firstCategoryId + '"]');

                if (firstTabContent.length) {
                    firstTabContent.addClass('active');
                } else {
                    // Create tab content if it doesn't exist
                    $('.tabs_container').append('<div class="tab_content active" data-tab="' + firstCategoryId +
                        '"></div>');
                }

                // Load data for first category
                loadProductsByCategory(firstCategoryId);
            }


            // Initialize with empty state
            updateTotalItems();
        });
        $(".select2").select2({
            tags: true,
        });
        // $(document).ready(function() {
        $('#customer_name').on('change', function() {
            var phone = $(this).find(':selected').data('phone');
            $('#customer_phone').val(phone || '');
        });
        // });
        $(document).ready(function() {
            const $searchInput = $('#customerSearch1');
            const $resultBox = $('#searchResults1');

            $searchInput.on('input', function() {
                const query = $(this).val().trim();

                if (query.length < 1) {
                    $resultBox.hide();
                    return;
                }

                $.ajax({
                    url: `/search-users`,
                    method: 'GET',
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function(data) {
                        $resultBox.empty();
                        console.log(data);
                        

                        let hasResults = false;

                        // Users section
                        // (add user search results here if needed)

                        // Products section
                        if (data.products && data.products.length > 0) {
                            hasResults = true;
                            $resultBox.append(
                                `<div class="list-group-item fw-bold mt-2">Products</div>`);
                            data.products.forEach(product => {
                                $resultBox.append(`
                                            <div class="list-group-item d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <img src="${product.image}" alt="Product Image" class="rounded me-2" style="width:30px; height:30px; object-fit: cover;">
                                                    <div>
                                                        <strong>${product.name ?? 'N/A'}</strong><br>
                                                        <small>Price: ${product.price ?? 'N/A'}</small>
                                                    </div>
                                                </div>
                                                <button class="btn btn-sm btn-primary add-product"
                                                    data-id="${product.id}"
                                                    data-name="${product.name}"
                                                    data-price="${product.price}"
                                                    data-quantity_type_id="${product.quantity_type_id}"
                                                    data-quantity_type123="${product.quantity_type123}"
                                                    data-image="${product.image}"
                                                    data-stock="${product.stock ?? 10}"
                                                    data-category="${product.category_id ?? 0}">+ Add</button>
                                            </div>
                                        `);
                            });
                        }

                        // Orders section
                        // (add orders section if needed)

                        if (!hasResults) {
                            $resultBox.html(
                                '<div class="list-group-item">No results found</div>');
                        }

                        $resultBox.show();
                    },
                    error: function(xhr, status, error) {

                        $resultBox.hide();
                    }
                });
            });

            $(document).on("click", ".add-product", function() {
                const $btn = $(this);
                const productId = $btn.data("id");
                const productName = $btn.data("name");
                const productPrice = $btn.data("price");
                const productImage = $btn.data("image");
                const productStock = parseInt($btn.data("stock"));
                const categoryId = $btn.data("category");
                
                const quantity_type1 = $btn.data("quantity_type_id");
                const quantity_type123 = $btn.data("quantity_type123");

                console.log(quantity_type1);
                

                // Check if product is already selected
                if (!selectedItems.has(productId)) {
                    selectedItems.set(productId, {
                        id: productId,
                        productId: productId,
                        categoryId: categoryId,
                        name: productName,
                        price: productPrice,
                        image: productImage,
                        quantity_type_id: quantity_type1,
                        quantity_type123: quantity_type123,

                        // quantity_type: product.quantity_type, // <-- store actual name ✔️
                        code: "PT001", // Default code
                        quantity: 1,
                        stock: productStock
                    });

                    // Optional: highlight or disable the Add button
                    $btn.prop("disabled", true).text("Added");

                    // Call existing function to update UI
                    updateTotalItems();
                } else {
                    // Optional: Notify user
                    Swal.fire({
                        icon: 'info',
                        title: 'Already Added',
                        text: 'This product is already in the list!',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            });

            // Optional: Hide dropdown on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest($searchInput).length && !$(e.target).closest($resultBox).length) {
                    $resultBox.hide();
                }
            });
        });
    </script>
    @endpush