@extends('layout.app')

@section('title', 'Category Edit')

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
            <h4>Edit Product Category</h4>
            <!-- <h6>Edit a product Category</h6> -->
        </div>
        <div class="page-btn">
        <a href="{{ route('category.list') }}" class="btn btn-added">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="categoryForm">
                <input type="hidden" name="category_id" id="category_id" value="{{ $category->id ?? '' }}">
                <div class="row">
                    <div class="col-12">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="category_name">Category Name <span class="text-danger field-required-mark">*</span></label>
                                <input type="text" name="name" id="name" value="{{ $category->name }}" class="form-control" required>
                                <span class="error_name text-danger"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="category_image">Category Image</label>
                                <div class="image-upload">
                                    <input type="file" name="image" id="image" class="form-control">
                                    <div class="image-uploads">
                                        <img src="{{ env('ImagePath').'admin/assets/img/icons/upload.svg' }}" alt="Upload Icon">
                                        <h4>Drag and drop a file to upload</h4>
                                    </div>
                                </div>
                                @if($category->image)
                                <div class="product-list mt-3">
                                    <ul class="row">
                                        <li class="ps-0">
                                            <div class="productviews">
                                                <div class="productviewsimg">
                                                    <img src="{{ env('ImagePath').'storage/' . $category->image }}" alt="Category Image" width="100">
                                                </div>
                                                <!-- <div class="productviewscontent"> -->
                                                <!-- <div class="productviewsname">
                                                        <h2>{{ $category->image }}</h2>
                                                    </div> -->
                                                <!-- <a href="javascript:void(0);" class="hideset remove-image">x</a> -->
                                                <!-- </div> -->
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit submit me-2">Submit</button>
                        <a href="{{ route('category.list') }}" class="btn btn-cancel">Cancel</a><br>
                        <span class="success_submit text-danger"></span>
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
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let formData = new FormData($('#categoryForm')[0]);
            if (selectedSubAdminId) {
                formData.append("sub_admin_id", selectedSubAdminId);
            }

            $('.error_name').text('');

            $.ajax({
                url: "/api/updatecategory", // Update this with your actual route
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
                            text: "Category update successfully!",
                            icon: "success",
                            confirmButtonColor: "#ff9f43",
                            confirmButtonText: "OK"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('category.list') }}";
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