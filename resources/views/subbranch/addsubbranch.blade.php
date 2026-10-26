@extends('layout.app')

@section('title', 'Sub Branch')

@section('content')
<style>
    @media screen and (max-width: 768px) {
        .form-group {
            margin-bottom: 10px !important
        }
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Add Sub Branch</h4>
        </div>
        <div class="breadcrumb">
            <a class="btn btn-primary btn-sm" href="{{ route('subbranch.list') }}">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="subbranchForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Customer Name -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Branch Name</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control">
                            <div class="text-danger error-customer_name"></div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" autocomplete="off">
                            <div class="text-danger error-email"></div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" id="password" class="form-control" autocomplete="off">
                            <div class="text-danger error-password"></div>
                        </div>
                    </div>


                    <!-- Phone -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control">
                            <div class="text-danger error-phone"></div>
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" name="country" id="country" class="form-control">
                            <div class="text-danger error-country"></div>
                        </div>
                    </div>

                    <!-- City -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" id="city" class="form-control">
                            <div class="text-danger error-city"></div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" id="address" class="form-control"></textarea>
                            <div class="text-danger error-address"></div>
                        </div>
                    </div>

                    <!-- Avatar Upload -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Photo</label>
                            <div class="image-upload">
                                <input type="file" name="avatar" id="avatar" class="form-control">
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}"
                                        alt="Upload Icon">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                            <div class="text-danger error-avatar"></div>

                            <!-- Avatar Preview -->
                            <div id="avatar-preview-container" style="display: none; margin-top: 10px;">
                                <img id="avatar-preview" src="" alt="Avatar Preview"
                                    style="max-width: 150px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>

                    <!-- Submit & Cancel Buttons -->
                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ route('subbranch.list') }}" class="btn btn-cancel">Cancel</a>
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
        var authToken = localStorage.getItem("authToken");

        // Handle form submission
        $("#subbranchForm").submit(function(e) {
            e.preventDefault(); // Prevent default form submission

            let formData = new FormData(this);
            $(".text-danger").html(""); // Clear previous error messages

            const $btn = $("#submitCustomerBtn"); // Replace with your actual button ID
            const originalText = $btn.html();

            // Show spinner and disable button
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop("disabled", true);

            $.ajax({
                url: "/api/createSubbranch", // Ensure API route is correct
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    $btn.html(originalText).prop("disabled", false); // Restore button
                    if (response.status) {
                        Swal.fire({
                            title: "Success!",
                            text: "Sub Branch added successfully!",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.href = "/sub-branch";
                        });
                    }
                },
                error: function(xhr) {
                    $btn.html(originalText).prop("disabled", false); // Restore button
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $(".error-" + key).html(value[0]); // Show error below each field
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Something went wrong. Please try again.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                },
            });
        });

    });
</script>
@endpush
