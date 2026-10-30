@extends('layout.app')

@section('title', 'Edit Brand')

@section('content')
<style>
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .page-header .page-btn .btn-back {
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

    .page-header .page-btn .btn-back:hover {
        background: #ff8f1f;
        color: #fff;
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Edit Brand</h4>
            <!-- <h6>Update your Brand</h6> -->
        </div>
        <div class="page-btn">
        <a href="{{ route('brand.list') }}" class="btn btn-added">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="editbrand">
                <input type="hidden" name="brand_id" id="brand_id" value="{{ $brand->id ?? '' }}">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Brand Name <span class="text-danger field-required-mark">*</span></label>
                            <input type="text" name="name" id="name" value="{{ $brand->name ?? '' }}">
                            <span class="error_name text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label> Brand Image</label>
                            <div class="image-upload">
                                <input type="file" name="image" id="image" class="form-control">
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath').'admin/assets/img/icons/upload.svg'}}" alt="img">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($brand->logo)
                    <div class="col-12">
                        <div class="product-list">
                            <ul class="row">
                                <li>
                                    <div class="productviews">
                                        <div class="productviewsimg">
                                            <img src="{{ env('ImagePath').'storage/' . $brand->logo }}" alt="Brand Image" width="100">
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    <div class="col-lg-12">
                        <a href="javascript:void(0);" class="btn btn-submit submit me-2">Submit</a>
                        <a href="{{route('brand.list')}}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        $(document).on('click', '.submit', function(e) {
            e.preventDefault();
            var authToken = localStorage.getItem("authToken");
            let formData = new FormData($('#editbrand')[0]);

            $('.error_name').text('');

            $.ajax({
                url: "/api/updateBrand", // Update this with your actual route
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    if (response.status) { // Check 'status' instead of 'success'
                       Swal.fire({
                            title: "Success!",
                            text: "Brand update successfully!",
                            icon: "success",
                            confirmButtonColor: "#ff9f43",
                            confirmButtonText: "OK"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('brand.list') }}";
                            }
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('.error_' + key).text(value[0]);
                        });
                    }
                }
            });
        });
    });
</script>
@endpush