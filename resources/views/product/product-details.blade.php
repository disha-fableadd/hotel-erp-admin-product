@extends('layout.app')

@section('title', 'Category List')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Product Details</h4>
            <h6>Full details of a product</h6>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <!-- <div class="bar-code-view"> -->
                    <input type="hidden" name="product_id" id="product_id" value="{{ $view_id }}">
                    <!-- <img src="{{ url('admin/assets/img/barcode1.png') }}" alt="barcode">
                        <a class="printimg">
                            <img src="{{ url('admin/assets/img/icons/printer.svg') }}" alt="print">
                        </a> -->
                    <!-- </div> -->
                    <div class="productdetails">
                        <ul class="product-bar">

                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="slider-product-details">
                        <div class="owl-carousel owl-theme product-slide">


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection