@extends('layout.app')

@section('title', 'Add Vendor')

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
            <h4>Add Vendor</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="customerForm">
                <div class="row">
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Supplier Name <span class="text-danger field-required-mark">*</span></label>
                            <input type="text" name="name" id="supplier_name" class="form-control">
                            <span class="text-danger error-name"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Email <span class="text-danger field-required-mark">*</span></label>
                            <input type="email" name="email" id="email" class="form-control">
                            <span class="text-danger error-email"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Phone <span class="text-danger field-required-mark">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control">
                            <span class="text-danger error-phone"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" name="country" id="country" class="form-control">
                            <span class="text-danger error-country"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" id="city" class="form-control">
                            <span class="text-danger error-city"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>PAN Number</label>
                            <input type="text" name="pan_number" id="pan_number" class="form-control">
                            <span class="text-danger error-pan_number"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>GST Number</label>
                            <input type="text" name="gst_number" id="gst_number" class="form-control">
                            <span class="text-danger error-gst_number"></span>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" id="address" class="form-control"></textarea>
                            <span class="text-danger error-address"></span>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Photo</label>
                            <div class="image-upload">
                                <input type="file" name="avatar" id="avatar-input" class="form-control">
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath').'/admin/assets/img/icons/upload.svg' }}" alt="Upload Icon">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                            <!-- Profile Image Preview -->
                            <div id="avatar-preview-container" style="display: none; margin-top: 10px;">
                                <img id="avatar-preview" src="" alt="Profile Image" style="max-width: 150px; border-radius: 8px;">
                            </div>
                            <span class="text-danger error-avatar"></span>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ route('vendor.list') }}" class="btn btn-cancel">Cancel</a>
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
        $("#customerForm").submit(function(e) {
            e.preventDefault(); // Prevent default form submission

            // Target the submit button
            const $btn = $("#submitButton"); // Make sure your submit button has id="submitButton"
            const originalText = $btn.html();

            
            // Show loader and disable button
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop("disabled", true);

            let formData = new FormData(this);
            $(".text-danger").html(""); // Clear previous error messages
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            if (selectedSubAdminId && selectedSubAdminId !== 'null' && selectedSubAdminId !== 'undefined') {
                formData.append("selectedSubAdminId", selectedSubAdminId);
            }

            $.ajax({
                url: "/api/createSupplier", // Ensure API route is correct
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
                            text: "Vendor added successfully!",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.href = "/vendors";
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
                        Swal.fire("Error!", "Something went wrong. Please try again.", "error");
                    }
                },
            });
        });

        // Remove error message when user starts typing
        $("input, select").on("input", function() {
            let fieldName = $(this).attr("name");
            $(".error-" + fieldName).html("");
        });

        // Handle File Input Change (Live Preview)
        $("#avatar-input").change(function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $("#avatar-preview").attr("src", e.target.result);
                $("#avatar-preview-container").show();
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    });
</script>
@endpush