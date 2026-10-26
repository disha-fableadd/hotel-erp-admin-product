    @extends('layout.app')

    @section('title', 'Add Order')

    @section('content')
    <style>
        /* .setvaluecash{
            display: block !important;
        } */
        .setvaluecash {
            display: block !important;
        }
        .select-split1{
            display: block;
        }
        .increment-decrement {
            display: flex;
            align-items: center;
        }
        .increment-decrement .input-groups {
            display: inline-flex !important;
            align-items: center !important;
            border: 1px solid #e9ecef !important;
            border-radius: 4px !important;
            overflow: hidden !important;
            width: 100px !important;
            height: 35px !important;
            background: #fff !important;
        }
        .increment-decrement .input-groups .button-minus,
        .increment-decrement .input-groups .button-plus {
            width: 30px !important;
            height: 100% !important;
            border: none !important;
            background: #f8f9fa !important;
            font-weight: bold !important;
            cursor: pointer !important;
            padding: 0 !important;
            text-align: center !important;
            line-height: 33px !important;
            font-size: 16px !important;
            color: #1b2850 !important;
        }
        .increment-decrement .input-groups .quantity-field {
            width: 40px !important;
            height: 100% !important;
            border: none !important;
            border-left: 1px solid #e9ecef !important;
            border-right: 1px solid #e9ecef !important;
            text-align: center !important;
            padding: 0 !important;
            font-size: 14px !important;
            color: #000 !important;
            background: #fff !important;
        }
        .setvaluecash ul li a {
            border: 1px solid #e9ecef;
            color: #000;
            font-size: 12px;
            font-weight: 600;
            min-height: 59px;
            border-radius: 5px;
            padding: 8px 7px;
        }

        .setvaluecash1 ul li a {
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

        .typemethod.active {
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

        .typemethod.active:hover {
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
            position: sticky;
            bottom: 0;
            background-color: white;
            overflow: visible !important;
            padding-top: 8px;
            z-index: 10;
        }

        .card.card-order {
            position: sticky;
            top: 10px;
            overflow: visible;
        }

        .body_space {
        }

        li.quantity_type {
            display: none;
        }

        .paymentmethod.active {
            background-color: #1b2850;
            color: #ffffff;
        }

        .paymentmethod.active svg {
            fill: #ffffff;
        }

        .typemethod.active {
            background-color: #1b2850;
            color: #ffffff;
        }

        .typemethod.active svg {
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
            @media screen and (max-width: 768px) {
    /* ... existing rules ... */

    /* 🔧 Product row overflow fix */
    .product-table {
        overflow-x: hidden;
    }

    .product-lists {
        flex-wrap: wrap !important;
        display: flex !important;
        flex-direction: column !important;
        position: relative;
        padding-right: 30px;
        gap: 8px;
    }

    .productimg {
        flex-wrap: wrap;
        width: 100%;
        display: flex;
        flex-direction: row;
        gap: 8px;
        align-items: flex-start;
    }

    .productimgs {
        flex-shrink: 0;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .productimgs img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .productcontet {
        width: auto;
        flex: 1;
        min-width: 0;
    }

    .productcontet h4 {
        font-size: 13px;
        white-space: normal;
        margin: 0 0 6px 0;
        word-break: break-word;
    }

    .increment-decrement {
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
        align-items: center;
    }

    .increment-decrement .input-groups {
        width: 80px !important;
        height: 32px !important;
        flex-shrink: 0;
    }

    .price-field {
        width: 65px !important;
        height: 32px !important;
        margin-left: 0 !important;
        flex-shrink: 0;
        padding: 4px 4px !important;
    }

    .product-lists li:last-child {
        /* delete icon */
        position: absolute;
        top: 8px;
        right: 8px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .product-lists li:last-child a {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-lists li:last-child img {
        width: 18px;
        height: 18px;
    }
}
            .payment_panel {
                position: static !important;
                width: 100% !important;
                background-color: transparent !important;
                margin: 10px 0 !important;
            }

            .table_type {
                position: static !important;
                width: 100% !important;
                background-color: transparent !important;
                margin: 10px 0 !important;
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
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <div class="page-title" style="margin-bottom: 0;">
                <h4 style="margin-bottom: 0; font-weight: 700;">Add Order</h4>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('sales.list') }}" class="btn" style="background-color: #ff9f43; color: white; font-weight: 600; border-radius: 5px; padding: 6px 15px;">
                    &larr; Back
                </a>
            </div>
        </div>

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
                    <a href="{{ url('add-product') }}?redirect_to=add-sales" class="btn btn-primary mt-2">
                        + Add Item
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
                            <div class="page-btn">
                                @if (app('hasPermission')(2, 'add'))                                    
                                        <a href="{{ route('tables.create') }}" class="btn" style="background-color: #ff9f43; color: white; font-weight: 600; border-radius: 5px; padding: 4px 12px; font-size: 14px;">Dine In</a>
                                @endif
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
                                    <div class="col-6">
                                        <div class="select-split">
                                            <div class="select-group w-100">
                                                <label>Customer Name</label>
                                                <select id="customer_name_input" class="form-control select2_customer" name="customer_name">
                                                    <option value="">Select or Type Customer</option>
                                                    @foreach ($customers as $cust)
                                                    <option value="{{ $cust->name }}" data-phone="{{ $cust->phone }}" {{ strtolower($cust->name) == 'default customer' ? 'selected' : '' }}>{{ $cust->name }} ({{ $cust->phone }})</option>
                                                    @endforeach
                                                </select>
                                                <span class="error_customername" style="color: red;"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Customer Phone -->
                                    <div class="col-6">
                                        <div class="select-split">
                                            <div class="select-group w-100">
                                                <label>Customer Phone</label>
                                                <input type="tel" id="customer_phone" class="form-control"
                                                    placeholder="Customer Phone" name="customer_phone">
                                                <span class="error_customerphone" style="color: red;"></span>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Table No -->
                                    <!-- <div class="col-md-12" id="table_no_container">
                                        <div class="select-split">
                                            <div class="select-group w-100">
                                                <label>Table No</label>
                                                <select id="table_no_select" name="table_id" style="z-index:1;"
                                                    class="form-control select2">
                                                    <option value="">Select Table No</option>
                                                    @foreach ($tables as $table)
                                                    <option value="{{ $table->id }}">
                                                        {{ $table->table_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <div class="select-split">
                                            <div class="select-group w-100">
                                                <label>Order Type</label>
                                                <select id="order_type_select" name="order_type" class="form-control select2-fixed">
                                                    <option value="Delivery">Delivery</option>
                                                    <option value="Self Pickup">Self Pickup</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="select-split">
                                            <div class="select-group w-100">
                                                <label>Assigned Staff</label>
                                                <select name="assigned_staff_id" id="assigned_staff_id" class="form-control select2-fixed">
                                                    <option value="">- Unassigned -</option>
                                                    @foreach($staffList as $staff)
                                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            // document.getElementById('customer_name').addEventListener('change', function() {
                            //     var phone = this.options[this.selectedIndex].getAttribute('data-phone');
                            //     document.getElementById('customer_phone').value = phone || '';
                            // });
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
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="select-split ">
                                        <div class="select-group w-100">
                                            <label for="discount">Discount (%)</label>
                                            <input type="number" class="form-control" placeholder="Discount %" name="discount"
                                                id="discount">
                                            <span class="error_discount"></span>
                                        </div>
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
                            {{-- <div class="table_type">
                                <div class="setvaluecash1">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0);" class="typemethod active">
                                                <input type="radio" name="type" value="dinein" checked hidden>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-door-open" viewBox="0 0 16 16">
                                                <path d="M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1z"/>
                                                <path d="M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H1V2.5A1.5 1.5 0 0 1 2.5 1H3V.5a.5.5 0 0 1 .5-.5h7.328zM3 2v13h8V2H3z"/>
                                                </svg>
                                                Dine In
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="typemethod">
                                                <input type="radio" name="type" value="delivery" hidden>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                    <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                    <path
                                                        d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                                </svg>
                                                Delivery
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="typemethod">
                                                <input type="radio" name="type" value="take_away" hidden>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                    <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                    <path
                                                        d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                                </svg>
                                                Take Away
                                            </a>
                                        </li>
                                    </ul>
                                    <span class="error_typemethod"></span>
                                </div>
                            </div> --}}
                            {{-- Default hidden type = takeaway so API still receives a value --}}
                            <input type="radio" name="type" value="takeaway" checked hidden>
                            <div class="payment_panel">
                                <div class="setvaluecash">
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
                                    {{-- ── Paid Type sub-panel (Cash / Debit / Scan) ────────── --}}
                                    <div id="paidTypeBox" style="display:none; margin-top:10px;">
                                        <div class="form-group mb-2">
                                            <label style="font-weight:600;">Paid Type</label>
                                            <select id="paidTypeSelect" class="form-control" name="paid_type">
                                                <option value="">Select Paid Type</option>
                                                <option value="fully" selected>Fully</option>
                                                <option value="partially">Partially</option>
                                            </select>
                                            <small id="paidTypeError" style="font-size:14px; color: red;"></small>
                                        </div>
                                        <div id="paidAmountBox" style="display:none;">
                                            <div id="paidAmountRow" class="row g-2 align-items-start mt-1">
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label>Enter Amount</label>
                                                        <input type="number" class="form-control" id="enterAmount"
                                                            placeholder="0.00" min="0" step="0.01">
                                                        <small id="enterAmountError" style="min-height:18px; display:block; color: red;"></small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label>Pending Amount</label>
                                                        <input type="number" class="form-control" id="pendingAmount"
                                                            placeholder="0.00" readonly>
                                                        <small style="min-height:18px; display:block;"></small>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- ── Debit-only fields ──────────────────────── --}}
                                            <div id="debitExtraFields" style="display:none;">
                                                <div class="row mt-2 align-items-start">
                                                    <div class="col-8" style="display:none;">
                                                        <div class="form-group mb-0">
                                                            <label>Select Bank</label>
                                                            <select id="debitBankSelect" class="form-control" name="bank_id">
                                                                <option value="">Select Bank</option>
                                                            </select>
                                                            <small id="debitBankError" style="min-height:18px; display:block; color: red;"></small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 d-flex align-items-start pt-4 mt-1" style="display:none !important;">
                                                        <div class="form-group w-100 mb-0">
                                                            <button type="button" id="addBankBtn"
                                                                class="btn btn-sm w-100"
                                                                style="background:#ff9f43;color:#fff;font-weight:600;border-radius:6px;height:38px;">
                                                                + Add Bank
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Remark</label>
                                                            <input type="text" class="form-control" id="debitRemark"
                                                                name="debit_remark" placeholder="Enter remark">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- ── Scan-only fields ───────────────────────── --}}
                                            <div id="scanExtraFields" style="display:none;">
                                                <div class="row mt-2 align-items-start">
                                                    <div class="col-8" style="display:none;">
                                                        <div class="form-group mb-0">
                                                            <label>Select Bank</label>
                                                            <select id="scanBankSelect" class="form-control" name="scan_bank_id">
                                                                <option value="">Select Bank</option>
                                                            </select>
                                                            <small id="scanBankError" style="min-height:18px; display:block; color: red;"></small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 d-flex align-items-start pt-4 mt-1" style="display:none !important;">
                                                        <div class="form-group w-100 mb-0">
                                                            <button type="button" class="btn btn-sm w-100 addBankBtnScan"
                                                                style="background:#ff9f43;color:#fff;font-weight:600;border-radius:6px;height:38px;">
                                                                + Add Bank
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Remark</label>
                                                            <input type="text" class="form-control" id="scanRemark"
                                                                name="scan_remark" placeholder="Enter remark">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ── Cash+Online split box ─────────────────────────── --}}
                                    <div id="cashOnlineBox" style="display:none; margin-top:10px;">
                                        {{-- Paid Type (same as debit/cash/scan) --}}
                                        <div class="form-group mb-2">
                                            <label style="font-weight:600;">Paid Type</label>
                                            <select id="cashOnlinePaidTypeSelect" class="form-control" name="paid_type_cash_online">
                                                <option value="">Select Paid Type</option>
                                                <option value="fully" selected>Fully</option>
                                                <option value="partially">Partially</option>
                                            </select>
                                            <span id="cashOnlinePaidTypeError" style="color:red; font-size:14px;"></span>
                                        </div>
                                        <div id="cashOnlinePaidAmountBox" style="display:none;">
                                            <div id="cashOnlinePaidAmountRow" class="row g-2 align-items-start mt-1">
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label>Enter Amount</label>
                                                        <input type="number" class="form-control" id="cashOnlineEnterAmount"
                                                            placeholder="0.00" min="0" step="0.01">
                                                        <small id="cashOnlineEnterAmountError" style="min-height:18px; display:block; color: red;"></small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <label>Pending Amount</label>
                                                        <input type="number" class="form-control" id="cashOnlinePendingAmount"
                                                            placeholder="0.00" readonly>
                                                        <small style="min-height:18px; display:block;"></small>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Cash + Online amounts --}}
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label>Cash Amount</label>
                                                        <input type="number" class="form-control" id="cashAmount"
                                                            placeholder="Enter Cash Amount" min="0" step="0.01">
                                                        <small id="cashAmountError" style="color: red;"></small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label>Online Amount</label>
                                                        <input type="number" class="form-control" id="onlineAmount"
                                                            placeholder="Enter Online Amount" value="0" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Select Bank + Remark --}}
                                            <div class="row mt-2 align-items-start">
                                                <div class="col-8" style="display:none;">
                                                    <div class="form-group mb-0">
                                                        <label>Select Bank</label>
                                                        <select id="cashOnlineBankSelect" class="form-control" name="bank_id_cash_online">
                                                            <option value="">Select Bank</option>
                                                        </select>
                                                        <small id="cashOnlineBankError" style="min-height:18px; display:block; color: red;"></small>
                                                    </div>
                                                </div>
                                                <div class="col-4 d-flex align-items-start pt-4 mt-1" style="display:none !important;">
                                                    <div class="form-group w-100 mb-0">
                                                        <button type="button" class="btn btn-sm w-100 addBankBtnCashOnline"
                                                            style="background:#ff9f43;color:#fff;font-weight:600;border-radius:6px;height:38px;">
                                                            + Add Bank
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Remark</label>
                                                        <input type="text" class="form-control" id="cashOnlineRemark"
                                                            name="cash_online_remark" placeholder="Enter remark">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="error_peymentmethod"></span>
                                </div>

                                <div class="btn-totallabel">
                                    <h6>Total Amount : 60.00$</h6>
                                    <h5>Order Now<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
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

        {{-- ── Add Bank Modal ───────────────────────────────────────── --}}
        <div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius:16px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="addBankModalLabel">Add Bank</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div id="addBankAlert" style="display:none;" class="alert alert-danger py-2 px-3 mb-3"></div>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" id="newBankName" class="form-control" placeholder="e.g. HDFC Bank">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Account Number <span class="text-danger">*</span></label>
                                <input type="text" id="newBankAccount" class="form-control" placeholder="Account number">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">IFSC Code <span class="text-danger">*</span></label>
                                <input type="text" id="newBankIfsc" class="form-control" placeholder="IFSC code">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                                <input type="text" id="newBankBranch" class="form-control" placeholder="Branch name">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Opening Balance <span class="text-danger">*</span></label>
                                <input type="number" id="newBankBalance" class="form-control" placeholder="0.00" value="0" min="0" step="0.01">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select id="newBankStatus" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="saveBankBtn" class="btn btn-sm px-4"
                            style="background:#ff9f43;color:#fff;font-weight:600;border-radius:8px;">
                            Save Bank
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ── Add Table Modal ───────────────────────────────────────── --}}
        <div class="modal fade" id="addTableModal" tabindex="-1" aria-labelledby="addTableModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius:16px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="addTableModalLabel">Add New Table</h5>
                        <button type="button" class="btn-close" id="closeTableModalBtn" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div id="addTableAlert" style="display:none;" class="alert alert-danger py-2 px-3 mb-3"></div>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Table Name <span class="text-danger">*</span></label>
                                <input type="text" id="newTableName" class="form-control" placeholder="Enter table name" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Table Type <span class="text-danger">*</span></label>
                                <select id="newTableType" class="form-control select2-modal" style="width: 100%;">
                                    <option value="">Select Table Type</option>
                                    <option value="AC">AC</option>
                                    <option value="Non AC">Non AC</option>
                                    <option value="Premium">Premium</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary btn-sm px-4" id="cancelTableModalBtn">Cancel</button>
                        <button type="button" id="saveTableBtn" class="btn btn-sm px-4"
                            style="background:#ff9f43;color:#fff;font-weight:600;border-radius:8px;">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endsection
        @push('js')
        <script>
            let selectedItems = new Map();

            // ── Bank loader (global scope so all handlers can access it) ──
            function loadBanksInto(selector) {
                var authToken = localStorage.getItem("authToken");
                var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                $.ajax({
                    url: '/api/banks/data',
                    method: 'GET',
                    headers: { "Authorization": "Bearer " + authToken },
                    data: { selectedSubAdminId: selectedSubAdminId, per_page: 100 },
                    success: function (res) {
                        var $sel = $(selector);
                        var currentVal = $sel.val();
                        $sel.empty().append('<option value="">Select Bank</option>');
                        if (res.status && res.data) {
                            $.each(res.data, function (i, bank) {
                                $sel.append('<option value="' + bank.id + '">' + bank.bank_name + '</option>');
                            });
                        }
                        if (currentVal) $sel.val(currentVal);
                    }
                });
            }
            function loadBanks() { loadBanksInto("#debitBankSelect"); }

            // Cash+Online: sync Enter/Online amounts when total changes (GST, items, etc.)
            function syncCashOnlineAmounts() {
                if ($("input[name='payment_method']:checked").val() !== "cash+online") {
                    return;
                }
                let coType = $("#cashOnlinePaidTypeSelect").val();
                let total = parseFloat($("input[name='total']").val()) || 0;
                let cash = parseFloat($("#cashAmount").val()) || 0;

                if (!coType) {
                    return;
                }

                if (coType === "fully") {
                    let online = Math.max(0, total - cash);
                    $("#onlineAmount").val(online.toFixed(2));
                    $("#cashOnlineEnterAmount").val(total.toFixed(2));
                    $("#cashOnlinePendingAmount").val("0.00");
                } else if (coType === "partially") {
                    let online = parseFloat($("#onlineAmount").val()) || 0;
                    $("#cashOnlineEnterAmount").val((cash + online).toFixed(2));
                    $("#cashOnlinePendingAmount").val(Math.max(0, total - cash - online).toFixed(2));
                }
            }

            function updateCashOnlinePending() {
                let coType = $("#cashOnlinePaidTypeSelect").val();
                let total = parseFloat($("input[name='total']").val()) || 0;
                let cash = parseFloat($("#cashAmount").val()) || 0;
                let online = parseFloat($("#onlineAmount").val()) || 0;
                $("#paymentError").remove();

                if (coType === "fully") {
                    let remaining = Math.max(0, total - cash);
                    $("#onlineAmount").val(remaining.toFixed(2));
                    $("#cashOnlinePendingAmount").val("0.00");
                } else if (coType === "partially") {
                    if (cash + online > total) {
                        const currencySymbol = '{{ $currency_symbol }}';
                        const currencyPosition = '{{ $currency_position }}';
                        let formatted = currencyPosition === 'right' ? total.toFixed(2) + currencySymbol : currencySymbol + total.toFixed(2);
                        if (!$("#paymentError").length) {
                            $("#cashOnlineBox").append('<span id="paymentError" style="color:red;font-size:13px;">Cash + Online cannot exceed Total Amount ' + formatted + '</span>');
                        }
                    }
                    $("#cashOnlineEnterAmount").val((cash + online).toFixed(2));
                    $("#cashOnlinePendingAmount").val(Math.max(0, total - (cash + online)).toFixed(2));
                }
            }

            // Active class handled by delegated $(document).on("click", ".paymentmethod") below
            document.querySelectorAll('.typemethod').forEach(el => {
                el.addEventListener('click', function() {
                    document.querySelectorAll('input[name="type"]').forEach(radio => radio.checked =
                        false);
                    this.querySelector('input[type="radio"]').checked = true;
                    document.querySelectorAll('.typemethod').forEach(item => item.classList.remove(
                        'active'));
                    this.classList.add('active');
                });
            });
            $(document).ready(function() {
                $(document).on("click", ".paymentmethod", function() {
                    let value = $(this).find("input[name='payment_method']").val();

                    $("input[name='payment_method']").prop("checked", false);
                    $(this).find("input[name='payment_method']").prop("checked", true);
                    $(".paymentmethod").removeClass("active");
                    $(this).addClass("active");

                    // Methods that need Paid Type sub-panel
                    const paidTypeMethods = ['cash', 'debit card', 'scan'];

                    if (value === "cash+online") {
                        $("#cashOnlineBox").slideDown();
                        $("#paidTypeBox").slideUp();
                        // Clear all errors when switching
                        $("#paidTypeError, #enterAmountError, #debitBankError, #scanBankError, #cashOnlinePaidTypeError, #cashOnlineEnterAmountError, #cashAmountError, #cashOnlineBankError").text("");
                        // Reset cash+online fields
                        // $("#cashOnlinePaidTypeSelect").val("");
                        // $("#cashOnlinePaidAmountBox").hide();
                        // $("#cashOnlineEnterAmount").val("");
                        // $("#cashOnlinePendingAmount").val("");
                        // let total = parseFloat($("input[name='total']").val()) || 0;
                        // $("#cashAmount").val("");
                        // syncCashOnlineAmounts();
                        $("#cashAmount").val("");
                        try { loadBanksInto("#cashOnlineBankSelect"); } catch(e) { console.warn("loadBanksInto error:", e); }
                        $("#cashOnlinePaidTypeSelect").val("fully").trigger("change");
                        // Load banks (non-blocking)
                        try { loadBanksInto("#cashOnlineBankSelect"); } catch(e) { console.warn("loadBanksInto error:", e); }
                    } else if (paidTypeMethods.includes(value)) {
                        $("#cashOnlineBox").slideUp();
                        $("#paidTypeBox").slideDown();
                        // Clear all errors when switching
                        $("#paidTypeError, #enterAmountError, #debitBankError, #scanBankError, #cashOnlinePaidTypeError, #cashOnlineEnterAmountError, #cashAmountError, #cashOnlineBankError").text("");
                        // Reset to "Select Paid Type" — hide amount+extra fields until user selects
                        // $("#paidTypeSelect").val("");
                        // $("#paidAmountBox").hide();
                        // $("#debitExtraFields").hide();
                        // $("#scanExtraFields").hide();
                        // $("#enterAmount").prop("readonly", false).val("");
                        // $("#pendingAmount").prop("readonly", true).val("");

                        // Default to "Fully" — user manually switches to Partially if needed
                        $("#debitBankSelect").val(""); $("#debitRemark").val("");
                        $("#scanBankSelect").val(""); $("#scanRemark").val("");
                        if (value === "debit card") {
                            try { loadBanks(); } catch(e) {}
                        } else if (value === "scan") {
                            try { loadBanksInto("#scanBankSelect"); } catch(e) {}
                        }
                        $("#paidTypeSelect").val("fully").trigger("change");
                        // Pre-load banks silently for debit/scan
                        if (value === "debit card") {
                            try { loadBanks(); } catch(e) {}
                        } else if (value === "scan") {
                            try { loadBanksInto("#scanBankSelect"); } catch(e) {}
                        }
                        // Hide the other type's extra fields and clear
                        $("#debitBankSelect").val(""); $("#debitRemark").val("");
                        $("#scanBankSelect").val(""); $("#scanRemark").val("");
                    } else {
                        // Pay Later — hide everything
                        $("#cashOnlineBox").slideUp();
                        $("#paidTypeBox").slideUp();
                        $("#paidTypeError, #enterAmountError, #debitBankError, #scanBankError, #cashOnlinePaidTypeError, #cashOnlineEnterAmountError, #cashAmountError, #cashOnlineBankError").text("");
                    }
                });

                // Paid Type dropdown change
               /* $("#paidTypeSelect").on("change", function() {
                    let val = $(this).val();
                    let currentPayment = $("input[name='payment_method']:checked").val();

                    if (val === "") {
                        // "Select Paid Type" — hide amount fields
                        $("#paidAmountBox").slideUp();
                        $("#enterAmount").val("");
                        $("#pendingAmount").val("");
                        return;
                    }
                    // Always show amount fields for both Fully and Partially
                    $("#paidAmountBox").slideDown();
                    // Show debit/scan extra fields based on current payment method
                    if (currentPayment === "debit card") {
                        $("#debitExtraFields").show();
                        $("#scanExtraFields").hide();
                    } else if (currentPayment === "scan") {
                        $("#debitExtraFields").hide();
                        $("#scanExtraFields").show();
                    } else {
                        $("#debitExtraFields").hide();
                        $("#scanExtraFields").hide();
                    }
                    let total = parseFloat($("input[name='total']").val()) || 0;
                    if (val === "partially") {
                        $("#enterAmount").prop("readonly", false).val(total.toFixed(2));
                        $("#pendingAmount").prop("readonly", true).val("0.00");
                    } else {
                        // Fully — both readonly
                        $("#enterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#pendingAmount").prop("readonly", true).val("0.00");
                    }
                }); */

                $("#paidTypeSelect").on("change", function() {
                    let val = $(this).val();
                    let currentPayment = $("input[name='payment_method']:checked").val();

                    if (val === "") {
                        $("#paidAmountBox").slideUp();
                        $("#enterAmount").val("");
                        $("#pendingAmount").val("");
                        return;
                    }

                    // paidAmountBox always shown (bank/remark fields andar hoy che)
                    $("#paidAmountBox").slideDown();

                    // Enter Amount / Pending Amount nu row FULLY ma hide, PARTIALLY ma show
                    if (val === "partially") {
                        $("#paidAmountRow").slideDown();
                    } else {
                        $("#paidAmountRow").slideUp();
                    }

                    if (currentPayment === "debit card") {
                        $("#debitExtraFields").show();
                        $("#scanExtraFields").hide();
                    } else if (currentPayment === "scan") {
                        $("#debitExtraFields").hide();
                        $("#scanExtraFields").show();
                    } else {
                        $("#debitExtraFields").hide();
                        $("#scanExtraFields").hide();
                    }

                    let total = parseFloat($("input[name='total']").val()) || 0;
                    if (val === "partially") {
                        $("#enterAmount").prop("readonly", false).val(total.toFixed(2));
                        $("#pendingAmount").prop("readonly", true).val("0.00");
                    } else {
                        $("#enterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#pendingAmount").prop("readonly", true).val("0.00");
                    }
                });

                // Cash+Online Paid Type dropdown change
                /* $("#cashOnlinePaidTypeSelect").on("change", function () {
                    let val = $(this).val();
                    if (val === "") {
                        $("#cashOnlinePaidAmountBox").slideUp();
                        $("#cashOnlineEnterAmount").val("");
                        $("#cashOnlinePendingAmount").val("");
                        return;
                    }
                    $("#cashOnlinePaidAmountBox").slideDown();
                    let total = parseFloat($("input[name='total']").val()) || 0;
                    if (val === "partially") {
                        $("#cashOnlineEnterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#cashOnlinePendingAmount").prop("readonly", true);
                        $("#onlineAmount").prop("readonly", false);
                        syncCashOnlineAmounts();
                        updateCashOnlinePending();
                    } else {
                        // Fully — Enter Amount and Online Amount readonly; Online defaults to full total
                        $("#cashOnlineEnterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#cashOnlinePendingAmount").prop("readonly", true).val("0.00");
                        $("#onlineAmount").prop("readonly", true);
                        syncCashOnlineAmounts();
                    }
                }); */
                $("#cashOnlinePaidTypeSelect").on("change", function () {
                    let val = $(this).val();
                    if (val === "") {
                        $("#cashOnlinePaidAmountBox").slideUp();
                        $("#cashOnlineEnterAmount").val("");
                        $("#cashOnlinePendingAmount").val("");
                        return;
                    }

                    // Box (Cash/Online amount, Bank, Remark) hammesha batavo
                    $("#cashOnlinePaidAmountBox").slideDown();

                    // Enter Amount / Pending Amount nu row FULLY ma hide, PARTIALLY ma show
                    if (val === "partially") {
                        $("#cashOnlinePaidAmountRow").slideDown();
                    } else {
                        $("#cashOnlinePaidAmountRow").slideUp();
                    }

                    let total = parseFloat($("input[name='total']").val()) || 0;
                    if (val === "partially") {
                        $("#cashAmount").val("");
                        $("#onlineAmount").val("");
                        $("#cashOnlineEnterAmount").prop("readonly", true).val("0.00");
                        $("#cashOnlinePendingAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#onlineAmount").prop("readonly", false);
                        syncCashOnlineAmounts();
                        updateCashOnlinePending();
                    } else {
                        // Fully — Enter Amount and Online Amount readonly; Online defaults to full total
                        $("#cashOnlineEnterAmount").prop("readonly", true).val(total.toFixed(2));
                        $("#cashOnlinePendingAmount").prop("readonly", true).val("0.00");
                        $("#onlineAmount").prop("readonly", true);
                        syncCashOnlineAmounts();
                    }
                });

                // Bind events
                $(document).on("input", "#cashAmount", updateCashOnlinePending);
                $(document).on("input", "#onlineAmount", updateCashOnlinePending);

                // Enter Amount → auto-calculate Pending Amount
                $("#enterAmount").on("input", function() {
                    let total = parseFloat($("input[name='total']").val()) || 0;
                    let entered = parseFloat($(this).val()) || 0;
                    let pending = Math.max(0, total - entered);
                    $("#pendingAmount").val(pending.toFixed(2));
                });

            });
            // $(document).ready(function() {
            //     $(".typemethod").on("click", function() {
            //         let value = $(this).find("input[name='type']").val();
            //         $("input[name='type']").prop("checked", false);
            //         $(this).find("input[name='type']").prop("checked", true);
            //         if (value === "dinein") {
            //             $("#table_no_container").slideDown();
            //         } else {
            //             $("#table_no_container").slideUp();
            //             $("#table_no_select").val("").trigger("change");
            //         }
            //     });
            // });

            $(document).ready(function() {
                selectedItems = new Map();
                var authToken = localStorage.getItem("authToken");

                // Initialize tax rows as hidden on page load
                $(".taxList").hide();

                // No payment method selected by default

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

                        // Keep pending amount in sync when on "partially"
                        if ($("#paidTypeSelect").val() === "partially") {
                            let entered = parseFloat($("#enterAmount").val()) || 0;
                            let pending = Math.max(0, total - entered);
                            $("#enterAmount").val(total.toFixed(2));
                            $("#pendingAmount").val("0.00");
                        } else if ($("#paidTypeSelect").val() === "fully") {
                            // Fully — keep enter amount = total, pending = 0
                            $("#enterAmount").prop("readonly", true).val(total.toFixed(2));
                            $("#pendingAmount").val("0.00");
                        }

                        // Sync Cash+Online when total changes (GST, discount, items, etc.)
                        syncCashOnlineAmounts();
                        updateCashOnlinePending();
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
                        // Extract numeric price for input
                        let numericPrice = parseFloat(item.price.replace(/[^0-9.-]+/g, "")) || 0;
                        // Get valid quantity type (exclude null, empty, N/A)
                        let qtyType = (item.quantity_type && item.quantity_type !== 'N/A' && item.quantity_type.trim() !== '') ? item.quantity_type : ((item.quantity_type_id && item.quantity_type_id !== 'N/A' && item.quantity_type_id.toString().trim() !== '') ? item.quantity_type_id : '');

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
                                                ${qtyType ? `<span style="font-size:12px;color:#000;font-weight:600;margin-left:6px;text-transform:uppercase;">${qtyType}</span>` : ''}
                                                <input type="number" class="price-field form-control form-control-sm" data-id="${item.id}" value="${numericPrice}" min="0" step="0.01" style="width:80px;height:35px;margin-left:10px;font-size:13px;">
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                ${qtyType ? `<li class="quantity_type text-uppercase">${qtyType}</li>` : ''}

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
                    var productStock = parseInt($product.data("stock")) || 0;
                    var quantity_type_id = $product.data("quantity_type_id") || quantity_type123 || quantity_type;

                    var uniqueId = productId;
                    if (selectedItems.has(uniqueId)) {
                        var existingItem = selectedItems.get(uniqueId);
                        if (existingItem.quantity < productStock) {
                            existingItem.quantity++;
                            selectedItems.set(uniqueId, existingItem);
                            updateTotalItems();
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Stock Limit Reached',
                                text: 'Cannot exceed available stock (' + productStock + ')',
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }
                    } else {
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
                            stock: productStock
                        });
                        $checkIcon.addClass("selected");
                        updateTotalItems();
                    }
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

                // Handle price field changes
                $(document).on("input", ".price-field", function() {
                    var itemId = $(this).data("id");
                    var newPrice = parseFloat($(this).val()) || 0;
                    const currencySymbol = '{{ $currency_symbol }}';
                    const currencyPosition = '{{ $currency_position }}';
                    
                    if (selectedItems.has(itemId)) {
                        var item = selectedItems.get(itemId);
                        // Format price with currency symbol
                        if (currencyPosition === 'right') {
                            item.price = newPrice.toFixed(2) + currencySymbol;
                        } else {
                            item.price = currencySymbol + newPrice.toFixed(2);
                        }
                        selectedItems.set(itemId, item);
                        calculateTotals();
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
                    $(".error_customername, .error_customerphone, .error_peymentmethod, .error_discount").text("");
                    $(".total-value .error_total").remove();
                    $("#enterAmountError, #debitBankError, #scanBankError, #cashOnlineEnterAmountError, #cashAmountError, #cashOnlineBankError, #paidTypeError, #cashOnlinePaidTypeError").text("");

                    // Get form values
                    let name = $("#customer_name_input").val().trim();
                    let phone = $("input[name='customer_phone']").val().trim();
                    let table_id = $("#table_no_select").val();
                    let selectedPayment = $("input[name='payment_method']:checked").val();
                    let selectedTableType = $("input[name='type']:checked").val();
                    let subtotal = parseFloat($("input[name='subtotal']").val()) || 0;
                    let total = parseFloat($("input[name='total']").val()) || 0;
                    let discount = parseFloat($("#discount").val()) || 0;

                    // Validation
                    let isValid = true;

                    if (name === "") {
                        $(".error_customername").text("Customer name is required").css("color", "red");
                        isValid = false;
                    }

                    if (phone !== "" && !/^\d{10}$/.test(phone)) {
                        $(".error_customerphone").text("Enter a valid 10-digit phone number").css("color", "red");
                        isValid = false;
                    }

                    if (!selectedPayment) {
                        $(".error_peymentmethod").text("Please select a payment method").css("color", "red");
                        isValid = false;
                    }

                    // if (!selectedTableType) {
                    //     $(".error_typemethod").text("Please select a Table Type").css("color", "red");
                    //     isValid = false;
                    // }

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

                    // ── Payment-specific inline validations ───────────────
                    const paidTypeMethods = ['cash', 'debit card', 'scan'];
                    const activePaidType  = $("#paidTypeSelect").val();
                    const activeCOType    = $("#cashOnlinePaidTypeSelect").val();

                    // For Cash / Debit / Scan — validate Paid Type selected
                    if (paidTypeMethods.includes(selectedPayment)) {
                        if (!activePaidType) {
                            $("#paidTypeError").text("Please select a paid type.").css("color", "red");
                            isValid = false;
                        }
                    }

                    // For Cash / Debit / Scan — validate Enter Amount if Partially
                    if (paidTypeMethods.includes(selectedPayment) && activePaidType === 'partially') {
                        let entered = parseFloat($("#enterAmount").val()) || 0;
                        if (entered <= 0) {
                            $("#enterAmountError").text("Enter amount is required.").css("color", "red");
                            isValid = false;
                        } else if (entered >= total) {
                            $("#enterAmountError").text("Partial amount must be less than total amount.").css("color", "red");
                            isValid = false;
                        } else {
                            $("#enterAmountError").text("");
                        }
                    }

                    // Debit — bank required (REMOVED as it should use shop settings bank automatically)
                    // if (selectedPayment === 'debit card' && activePaidType && !$("#debitBankSelect").val()) {
                    //     $("#debitBankError").text("Please select a bank.").css("color", "red");
                    //     isValid = false;
                    // }

                    // Scan — bank required (REMOVED as it should use shop settings bank automatically)
                    // if (selectedPayment === 'scan' && activePaidType && !$("#scanBankSelect").val()) {
                    //     $("#scanBankError").text("Please select a bank.").css("color", "red");
                    //     isValid = false;
                    // }

                    // Cash+Online validations
                    if (selectedPayment === 'cash+online') {
                        if (!activeCOType) {
                            // Use same paidTypeError span but inside cashOnlineBox
                            $("#cashOnlinePaidTypeError").text("Please select a Paid Type.").css("color", "red");
                            isValid = false;
                        } else {
                            if (activeCOType === 'partially') {
                                let entered = parseFloat($("#cashOnlineEnterAmount").val()) || 0;
                                if (entered <= 0) {
                                    $("#cashOnlineEnterAmountError").text("Enter amount is required.").css("color", "red");
                                    isValid = false;
                                }
                            }
                            let cashAmt = parseFloat($("#cashAmount").val()) || 0;
                            if (cashAmt <= 0) {
                                $("#cashAmountError").text("Cash amount is required.").css("color", "red");
                                isValid = false;
                            } else if (cashAmt > total) {
                                $("#cashAmountError").text("Cash amount cannot exceed total.").css("color", "red");
                                isValid = false;
                            }
                            // if (!$("#cashOnlineBankSelect").val()) {
                            //     $("#cashOnlineBankError").text("Please select a bank.").css("color", "red");
                            //     isValid = false;
                            // }
                        }
                    }
                    // ─────────────────────────────────────────────────────


                    // if (!isValid) return;
                    if (!isValid) {
                        $btn.html(originalText).prop('disabled', false); // <-- important line
                        return;
                    }


                    // Prepare order items data
                    let orderItems = [];
                    selectedItems.forEach(function(item) {


                        const price = parseFloat(item.price.replace ? item.price.replace(/[^0-9.-]+/g, "") : item.price);
                        // Use numeric quantity_type_id: prefer quantity_type123 (numeric ID from data attribute),
                        // then quantity_type_id (set by search-add path), or fall back to quantity_type (text name)
                        const qtId = item.quantity_type123 || item.quantity_type_id || item.quantity_type || null;
                        orderItems.push({
                            product_id: item.productId,
                            quantity: item.quantity,
                            quantity_type_id: qtId,
                            quantity_type123: item.quantity_type123 || item.quantity_type_id,

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

                    // Paid type sub-fields
                    let paidType, enterAmount, pendingAmt;
                    if (selectedPayment === 'cash+online') {
                        paidType    = $("#cashOnlinePaidTypeSelect").val() || 'fully';
                        enterAmount = parseFloat($("#cashOnlineEnterAmount").val()) || total;
                        pendingAmt  = parseFloat($("#cashOnlinePendingAmount").val()) || 0;
                    } else {
                        paidType    = $("#paidTypeSelect").val() || 'fully';
                        enterAmount = parseFloat($("#enterAmount").val()) || total;
                        pendingAmt  = parseFloat($("#pendingAmount").val()) || 0;
                    }

                    // For "Fully" paid — enter_amount = total, pending = 0
                    if (paidType === 'fully' || selectedPayment === 'pending') {
                        enterAmount = total;
                        pendingAmt = 0;
                    }

                    // Debit-specific / Cash+Online / Scan fields
                    let bankId = selectedPayment === 'debit card'
                        ? ($("#debitBankSelect").val() || null)
                        : (selectedPayment === 'cash+online'
                            ? ($("#cashOnlineBankSelect").val() || null)
                            : (selectedPayment === 'scan' ? ($("#scanBankSelect").val() || null) : null));
                    let debitRemark = selectedPayment === 'debit card'
                        ? ($("#debitRemark").val() || null)
                        : (selectedPayment === 'cash+online'
                            ? ($("#cashOnlineRemark").val() || null)
                            : (selectedPayment === 'scan' ? ($("#scanRemark").val() || null) : null));

                    // Prepare order data
                    let orderData = {
                        selectedSubAdminId: selectedSubAdminId,
                        customer_id: name,
                        customer_phone: phone,
                        table_id: table_id,
                        payment_method: selectedPayment,
                        table_type: selectedTableType,
                        subtotal: subtotal,
                        discount: discount,
                        discount_amount: subtotal * (discount / 100),
                        tax: taxes,
                        gst_option: gstOption,
                        total: total,
                        cash_amount: cash,
                        online_amount: online,
                        paid_type: paidType,
                        enter_amount: enterAmount,
                        pending_amount: pendingAmt,
                        bank_id: bankId,
                        debit_remark: debitRemark,
                        order_type: $('#order_type_select').val() || 'Delivery',
                        assigned_staff_id: $('#assigned_staff_id').val() || null,
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
                                $("input[name='type']").prop("checked", false);
                                $(".paymentmethod").removeClass("active");
                                $(".typemethod").removeClass("active");
                                $("#discount").val("");
                                $("#paidTypeBox").hide();
                                $("#cashOnlineBox").hide();
                                $("#paidTypeSelect").val("");
                                $("#paidAmountBox").hide();
                                $("#enterAmount").prop("readonly", false).val("");
                                $("#pendingAmount").prop("readonly", true).val("");
                                $("#debitExtraFields").hide();
                                $("#debitBankSelect").val("");
                                $("#debitRemark").val("");
                                $("#scanExtraFields").hide();
                                $("#scanBankSelect").val("");
                                $("#scanRemark").val("");
                                // Reset Cash+Online fields
                                $("#cashOnlinePaidTypeSelect").val("");
                                $("#cashOnlinePaidAmountBox").hide();
                                $("#cashOnlineEnterAmount").val("");
                                $("#cashOnlinePendingAmount").val("");
                                $("#cashAmount").val("");
                                $("#onlineAmount").val("0");
                                $("#cashOnlineBankSelect").val("");
                                $("#cashOnlineRemark").val("");
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

                            if (response && response.message) {
                                Swal.fire({
                                    title: "Error",
                                    text: response.message,
                                    icon: "error",
                                    confirmButtonColor: "#ff9f43"
                                });
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: "Error placing order. Please try again.",
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
                    const fallbackImage = "{{ env('ImagePath') . 'admin/assets/img/product/noimage.png' }}";
                    const imageBasePath = "{{ rtrim(env('ImagePath'), '/') }}";

                    if (products.length === 0) {
                        productsHtml = '<div class="col-12"><p>No items found in this category</p></div>';
                    } else {
                        products.forEach(function(product) {
                            let productImage = fallbackImage;

                            // Handle different image formats
                            if (product.image) {
                                try {
                                    // Clean the image string
                                    let cleanImageString = product.image.replace(/\\/g, '');
                                    cleanImageString = cleanImageString.replace(/^"(.*)"$/, '$1');

                                    if (cleanImageString.startsWith('[')) {
                                        let imagesArray = JSON.parse(cleanImageString);
                                        if (Array.isArray(imagesArray) && imagesArray.length > 0) {
                                            productImage = `${imageBasePath}/storage/${imagesArray[0]}`;
                                        }
                                    } else {
                                        productImage = `${imageBasePath}/storage/${cleanImageString}`;
                                    }
                                } catch (e) {
                                    productImage = fallbackImage;
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
                                            <div class="productset flex-fill text-center ${disabledClass}" data-id="${product.id}" data-stock="${product.quantity}" data-quantity_type_id="${product.quantity_type_id || ''}" style="position: relative;">
                                                ${outOfStockBadge}
                                                <div class="productsetimg">
                                                    <img src="${productImage}" alt="${product.name}" class="productsetimgin">
                                                    <h6>Qty: ${product.quantity || '0'}</h6>
                                                </div>
                                                <div class="productsetcontent" style="padding: 0.5rem;">
                                                    <h4 style="font-size: 14px; font-weight: 600; margin: 0.5rem 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        ${product.name}
                                                    </h4>
                                                    <h1 class="d-none">${product.categoryId || product.category_id || ''}</h1>
                                                    <h2 class="d-none">${product.quantity_type || ''}</h2>
                                                    <h3 class="d-none">${product.quantity_type_id || ''}</h3>
                                                    
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
            $(".select2_customer").select2({
                tags: true,
                placeholder: "Select or Type Customer",
            }).on('change', function() {
                var phone = $(this).find(':selected').data('phone');
                if (phone) {
                    $('#customer_phone').val(phone);
                } else {
                    $('#customer_phone').val('');
                }
            });
            $(".select2-fixed").select2({
                minimumResultsForSearch: -1 // fixed options chhe, tethi search box nathi joito — remove this line if search joiye
            })
            
            // Trigger change to populate phone if default customer is selected
            setTimeout(function() {
                if ($('#customer_name_input').val()) {
                    $('#customer_name_input').trigger('change');
                }
            }, 100);
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


                            let hasResults = false;

                            // Users section
                            // (add user search results here if needed)

                            // Items section
                            if (data.products && data.products.length > 0) {
                                hasResults = true;
                                $resultBox.append(
                                    `<div class="list-group-item fw-bold mt-2">Items</div>`);
                                data.products.forEach(product => {
                                    $resultBox.append(`
                                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <img src="${product.image}" alt="Item Image" class="rounded me-2" style="width:30px; height:30px; object-fit: cover;">
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
                                                        data-quantity_type123="${product.quantity_type_id || ''}"
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
                    const productStock = parseInt($btn.data("stock")) || 0;
                    const categoryId = $btn.data("category");

                    const quantity_type1 = $btn.data("quantity_type_id");
                    const quantity_type123 = $btn.data("quantity_type123");

                    // Check if product is already selected
                    if (selectedItems.has(productId)) {
                        var existingItem = selectedItems.get(productId);
                        if (existingItem.quantity < productStock) {
                            existingItem.quantity++;
                            selectedItems.set(productId, existingItem);
                            updateTotalItems();
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Stock Limit Reached',
                                text: 'Cannot exceed available stock (' + productStock + ')',
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }
                    } else {
                        selectedItems.set(productId, {
                            id: productId,
                            productId: productId,
                            categoryId: categoryId,
                            name: productName,
                            price: productPrice,
                            image: productImage,
                            quantity_type_id: quantity_type1,
                            quantity_type123: quantity_type123,

                            code: "PT001", // Default code
                            quantity: 1,
                            stock: productStock
                        });

                        // Optional: highlight or disable the Add button
                        $btn.prop("disabled", true).text("Added");

                        // Call existing function to update UI
                        updateTotalItems();
                    }
                });

                // Optional: Hide dropdown on outside click
                $(document).on('click', function(e) {
                    if (!$(e.target).closest($searchInput).length && !$(e.target).closest($resultBox).length) {
                        $resultBox.hide();
                    }
                });
            });

            {{-- ── Bank Management JS ────────────────────────────────── --}}
            $(document).ready(function () {

                var authToken = localStorage.getItem("authToken");
                var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                // Open Add Bank modal — track which selector to update after save
                var _addBankTargetSelector = "#debitBankSelect";

                $(document).on('click', '#addBankBtn', function () {
                    _addBankTargetSelector = "#debitBankSelect";
                    openAddBankModal();
                });

                $(document).on('click', '.addBankBtnCashOnline', function () {
                    _addBankTargetSelector = "#cashOnlineBankSelect";
                    openAddBankModal();
                });

                $(document).on('click', '.addBankBtnScan', function () {
                    _addBankTargetSelector = "#scanBankSelect";
                    openAddBankModal();
                });

                function openAddBankModal() {
                    $("#newBankName, #newBankAccount, #newBankIfsc, #newBankBranch, #newBankBalance").val("");
                    $("#newBankStatus").val("1");
                    $("#addBankAlert").hide();
                    var modal = new bootstrap.Modal(document.getElementById('addBankModal'));
                    modal.show();
                }

                // Load banks when debit is selected — handled by main paymentmethod handler above

                // Save Bank
                $("#saveBankBtn").on("click", function () {
                    var bankName    = $.trim($("#newBankName").val());
                    var account     = $.trim($("#newBankAccount").val());
                    var ifsc        = $.trim($("#newBankIfsc").val());
                    var branchName  = $.trim($("#newBankBranch").val());
                    var balance     = $("#newBankBalance").val() || 0;
                    var status      = $("#newBankStatus").val();

                    if (!bankName || !account || !ifsc || !branchName) {
                        $("#addBankAlert").text("Please fill all required fields.").show();
                        return;
                    }

                    var $btn = $(this);
                    $btn.prop("disabled", true).text("Saving...");

                    $.ajax({
                        url: '/api/banks',
                        method: 'POST',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify({
                            bank_name: bankName,
                            account_number: account,
                            ifsc_code: ifsc,
                            branch_name: branchName,
                            opening_balance: balance,
                            status: status,
                            selectedSubAdminId: selectedSubAdminId
                        }),
                        success: function (res) {
                            if (res.status) {
                                // Close modal
                                bootstrap.Modal.getInstance(document.getElementById('addBankModal')).hide();
                                // Reload banks into the correct select and select the new one
                                loadBanksInto(_addBankTargetSelector);
                                setTimeout(function () {
                                    $(_addBankTargetSelector).val(res.data.id);
                                }, 400);
                                Swal.fire({ toast: true, position: 'top', icon: 'success', title: 'Bank added!', showConfirmButton: false, timer: 2000 });
                            } else {
                                $("#addBankAlert").text(res.message || "Failed to add bank.").show();
                            }
                        },
                        error: function (xhr) {
                            var msg = xhr.responseJSON?.message || "Failed to add bank.";
                            // Show validation errors if present
                            if (xhr.responseJSON?.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join(" ");
                            }
                            $("#addBankAlert").text(msg).show();
                        },
                        complete: function () {
                            $btn.prop("disabled", false).text("Save Bank");
                        }
                    });
                });
            });
            {{-- ── Add Table Management JS ─────────────────────────────── --}}
            $(document).ready(function () {
                var authToken = localStorage.getItem("authToken");
                var addTableModal = new bootstrap.Modal(document.getElementById('addTableModal'));

                // Listen for new tag selection in Select2
                $('#table_no_select').on('select2:select', function (e) {
                    var data = e.params.data;
                    // If it's a newly created tag, the id will equal the text
                    if (data.id === data.text) {
                        // Open the modal
                        $("#newTableName").val(data.text);
                        $("#newTableType").val("").trigger("change");
                        $("#addTableAlert").hide();
                        
                        // Temporarily clear selection so the unsaved tag isn't active
                        $(this).val(null).trigger('change');
                        
                        addTableModal.show();
                    }
                });

                // Initialize select2 inside modal for Table Type
                $("#newTableType").select2({
                    dropdownParent: $("#addTableModal"),
                    minimumResultsForSearch: -1 // Disable search if you only have a few options
                });
                
                // Handle cancel/close buttons
                $('#closeTableModalBtn, #cancelTableModalBtn').on('click', function() {
                    addTableModal.hide();
                });

                // Save Table
                $("#saveTableBtn").on("click", function () {
                    var tableName = $.trim($("#newTableName").val());
                    var tableType = $("#newTableType").val();

                    if (!tableName || !tableType) {
                        $("#addTableAlert").text("Please fill all required fields (Table Name and Table Type).").show();
                        return;
                    }

                    var $btn = $(this);
                    $btn.prop("disabled", true).text("Saving...");

                    $.ajax({
                        url: '/api/table/create',
                        method: 'POST',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify({
                            table_name: tableName,
                            table_type: tableType
                        }),
                        success: function (res) {
                            if (res.status && res.data) {
                                addTableModal.hide();
                                
                                // Remove any temporary dummy option with the same text
                                $('#table_no_select option').filter(function() {
                                    return $(this).val() === tableName;
                                }).remove();
                                
                                // Add new proper option to the dropdown and select it
                                var newOption = new Option(res.data.table_name, res.data.id, true, true);
                                $('#table_no_select').append(newOption).trigger('change');
                                
                                Swal.fire({ toast: true, position: 'top', icon: 'success', title: 'Table added successfully!', showConfirmButton: false, timer: 2000 });
                            } else {
                                $("#addTableAlert").text(res.message || "Failed to add table.").show();
                            }
                        },
                        error: function (xhr) {
                            var msg = xhr.responseJSON?.message || "Failed to add table.";
                            if (xhr.responseJSON?.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join(" ");
                            }
                            $("#addTableAlert").text(msg).show();
                        },
                        complete: function () {
                            $btn.prop("disabled", false).text("Save");
                        }
                    });
                });
            });
        </script>
        @endpush
