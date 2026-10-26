@extends('layout.app')

@section('title', 'Custom Invoice')

@section('content')

<style>
    .details td {
        vertical-align: middle !important
    }

    .product-list td {

        vertical-align: middle;

    }

    .logo_img {
        max-width: 150px;
        height: auto;
    }


    .invoice-box table {
        width: 100%;
        border-collapse: collapse;
    }

    .invoice-box table td {
        white-space: nowrap;
    }

    /* Adjust columns for mobile, keep same layout */
    @media (max-width: 767px) {
        .invoice-box {
            overflow-x: auto;
        }

        .card-sales-split {
            flex-direction: row;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .col-lg-6.table_view {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .col-lg-6.total-order {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .invoice-box,
        .invoice-box table,
        .invoice-box td,
        .invoice-box h4,
        .invoice-box h5 {
            font-size: 12px;
            line-height: 1.2;
        }

        .logo_img {
            max-width: 130px;
            height: auto;
        }

        font {
            line-height: 19px !important;
        }
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Invoice</h4>
            <!-- <h6>View purchase Invoice</h6> -->
        </div>
        <div class="page-btn d-flex">
            <a href="{{ route('custom_invoice.lists') }}" class="btn btn-added mx-3">Back</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">


            <div class="download_pdf">
                <div class="invoice-box table-height"
                    style="max-width: 1600px;width:100%;margin:15px auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                    <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left;">
                        <tbody id="product-details">

                            <tr class="top">
                                <td colspan="6" style="padding: 5px;vertical-align: top;">
                                    <table style="width: 100%;line-height: inherit;text-align: left;"
                                        class="product-list">
                                        <div class="row">
                                            <div class="col-6">
                                                <img src="{{ $companyInfo->logo ? env('ImagePath').'storage/' . $companyInfo->logo : env('ImagePath').'/admin/assets/img/logso.png' }}"
                                                    alt="logo" class="logo_img">
                                            </div>
                                            <div class="col-6 mt-4" style=" text-align: end; ">
                                                <h1>Invoice</h1>
                                                <h4 class="mt-3">#{{ $invoice->invoice_number }}</h4>
                                            </div>
                                        </div>
                                        <hr>
                                        <td colspan="6" style="padding: 5px;vertical-align: top;">
                                            <table style="width: 100%;line-height: inherit;text-align: left;"
                                                class="product-list">
                                                <tbody>
                                                    <tr>
                                                        <!-- Customer Info -->
                                                        <td style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px">
                                                            <font style="vertical-align: inherit; margin-bottom:25px;">
                                                                <font style="font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">
                                                                    {{ ucfirst($user->role) }} Info
                                                                </font>
                                                            </font><br>

                                                            @if (!empty($user->name))
                                                            <font>
                                                                <font class="customer-name">{{ $user->name }}</font>
                                                            </font><br>
                                                            @endif

                                                            @if (!empty($user->email))
                                                            <font>
                                                                <font>{{ $user->email }}</font>
                                                            </font><br>
                                                            @endif

                                                            @if (!empty($user->phone))
                                                            <font>
                                                                <font class="customer-phone">{{ $user->phone }}</font>
                                                            </font><br>
                                                            @endif

                                                             <font>
                                                                <font class="gst-number"><strong>GST No. : </strong>{{ $user->gst_number ?? '--' }}</font>
                                                            </font><br>
                                                            <font>
                                                                <font class="pan-number"><strong>PAN No. : </strong>{{ $user->pan_number ?? '--' }}</font>
                                                            </font><br>
                                                        </td>

                                                        <!-- Company Info -->
                                                        <td style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px">
                                                            <font style="vertical-align: inherit; margin-bottom:25px;">
                                                                <font style="font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">
                                                                    Company Info
                                                                </font>
                                                            </font><br>

                                                            @if (!empty($companyInfo->name))
                                                            <font>
                                                                <font>{{ $companyInfo->name }}</font>
                                                            </font><br>
                                                            @endif

                                                            @if (!empty($companyInfo->email))
                                                            <font>
                                                                <font>{{ $companyInfo->email }}</font>
                                                            </font><br>
                                                            @endif

                                                            @if (!empty($companyInfo->phone))
                                                            <font>
                                                                <font>{{ $companyInfo->phone }}</font>
                                                            </font><br>
                                                            @endif

                                                            @if (!empty($companyInfo->address))
                                                            <font>
                                                                <font>{{ $companyInfo->address }}</font>
                                                            </font><br>
                                                            @endif
                                                            @if (!empty($companyInfo->address))
                                                            <font>
                                                               <strong>GST No. : </strong> <font>{{ $companyInfo->gst_num }}</font>
                                                            </font><br>
                                                            @endif
                                                        </td>


                                                        <td
                                                            style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                            <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                <font
                                                                    style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px;">
                                                                    Invoice Info</font>
                                                            </font><br>
                                                            <font>
                                                                <font>Invoice Number</font>
                                                            </font><br>
                                                            <font>
                                                                <font>Purchase Date</font>
                                                            </font><br>
                                                            <font>
                                                                <font>Payment Status</font>
                                                            </font><br>
                                                            <font>
                                                                <font>Order Status</font>
                                                            </font><br>
                                                        </td>

                                                        <td
                                                            style="padding:5px;vertical-align:top;text-align:right;padding-bottom:20px">
                                                            <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                <font
                                                                    style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px;">
                                                                    &nbsp;</font>
                                                            </font><br>
                                                            <font>
                                                                <font class="invoice-id">{{ $invoice->invoice_number }}
                                                                </font>
                                                            </font><br>
                                                            <font>
                                                                <font class="invoice-id">{{ $invoice->created_at }}
                                                                </font>
                                                            </font><br>
                                                            <font>
                                                                <font class="payment-status">
                                                                    {{ $invoice->paid ? 'Paid' : 'Unpaid' }}
                                                                </font>
                                                            </font><br>
                                                            <font>
                                                                <font class="order-status">
                                                                    {{ ucfirst($invoice->status) }}
                                                                </font>
                                                            </font><br>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </table>
                                </td>
                            </tr>

                            <tr class="heading" style="background: #F3F2F7;">
                                <td style="padding: 10px;">Product Name</td>
                                <td style="padding: 10px;">QTY</td>
                                <td style="padding: 10px;">Price</td>

                                <td style="padding: 10px;">Subtotal</td>
                            </tr>

                            @foreach($invoice->products as $product)
                            <tr class="details" style="border-bottom:1px solid #E9ECEF;">
                                <!-- <td style="padding: 10px;vertical-align: top; display: flex;align-items: center;">
                                            <img src="{{ $product['product_image'] }}" alt="img" class="me-2"
                                                style="width:40px;height:40px;">
                                            {{ $product['product_name'] }}
                                        </td> -->
                                <td style="padding: 10px;vertical-align: top; display: flex;align-items: center;">
                                    <a href="{{ url('/product-view/' . $product['product_id']) }}" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                                        <img src="{{ $product['product_image'] }}" alt="img" class="me-2" style="width:40px;height:40px;">
                                        {{ $product['product_name'] }}
                                    </a>
                                </td>

                                <td>{{ $product['quantity'] }}</td>

                                <td>
                                    @if ($currencyPosition === 'right')
                                    {{ number_format($product['price'], 2) }}{{ $currencySymbol }}
                                    @else
                                    {{ $currencySymbol }}{{ number_format($product['price'], 2) }}
                                    @endif
                                </td>

                                <td style="padding: 10px;">
                                    @if ($currencyPosition === 'right')
                                    {{ number_format($product['total'], 2) }}{{ $currencySymbol }}
                                    @else
                                    {{ $currencySymbol }}{{ number_format($product['total'], 2) }}
                                    @endif
                                </td>
                            </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-lg-6"></div>
                    <div class="col-lg-6">
                        <div class="total-order w-100 max-widthauto m-auto mb-4">
                            <ul>
                                <li>
                                    <h4>Subtotal</h4>
                                    <h5>
                                        @if($currencyPosition === 'right')
                                        {{ number_format($invoice->total_amount, 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}
                                        @endif
                                    </h5>
                                </li>

                                @php
                                // Calculate discount amount (discount is percentage of total_amount)
                                $discountAmount = $invoice->discount ? ($invoice->total_amount * $invoice->discount / 100) : 0;
                                @endphp

                                @if(!empty($invoice->shipping) && $invoice->shipping > 0)
                                <li>
                                    <h4>Shipping</h4>
                                    <h5>
                                        @if($currencyPosition === 'right')
                                        {{ number_format($invoice->shipping, 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($invoice->shipping, 2) }}
                                        @endif
                                    </h5>
                                </li>
                                @elseif(!empty($invoice->discount) && $invoice->discount > 0)
                                <li>
                                    <h4>Discount ({{ $invoice->discount }}%)</h4>
                                    <h5>
                                        @if($currencyPosition === 'right')
                                        {{ number_format($discountAmount, 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($discountAmount, 2) }}
                                        @endif
                                    </h5>
                                </li>

                                <li>
                                    <h4>After Discount</h4>
                                    <h5>
                                        @php $afterDiscount = $invoice->total_amount - $discountAmount; @endphp
                                        @if($currencyPosition === 'right')
                                        {{ number_format($afterDiscount, 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($afterDiscount, 2) }}
                                        @endif
                                    </h5>
                                </li>
                                @endif

                                @if(!empty($invoice->taxes) && is_array($invoice->taxes))
                                @foreach($invoice->taxes as $tax)
                                <li>
                                    <h4>{{ $tax['name'] ?? 'Unknown Tax' }} ({{ $tax['rate'] }}%)</h4>
                                    <h5>
                                        @if($currencyPosition === 'right')
                                        {{ number_format($tax['amount'], 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($tax['amount'], 2) }}
                                        @endif
                                    </h5>
                                </li>
                                @endforeach
                                @endif

                                <li class="total">
                                    <h4>Total</h4>
                                    <h5>
                                        @if($currencyPosition === 'right')
                                        {{ number_format($invoice->grand_total, 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($invoice->grand_total, 2) }}
                                        @endif
                                    </h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 100px;">
                    <h2 style="color: #7367F0; font-size: 24px; margin-bottom: 10px;">Thank You for Your Business!</h2>
                    <p style="font-size: 16px; color: #555;">We appreciate your business and hope to serve you again
                        soon.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        const element = document.querySelector('.download_pdf');
        const opt = {
            margin: 10,
            filename: '{{ $invoice->invoice_number }}',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait'
            }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
@endpush