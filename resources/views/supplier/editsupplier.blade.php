@extends('layout.app')

@section('title', 'Edit Vendor')

@section('content')
<style>
    @media screen and (max-width: 768px) {
        .form-group {
            margin-bottom: 10px !important
        }
    }
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
            <h4>Edit Vendor</h4>
        </div>
        <div class="page-btn">
        <a href="{{ route('vendor.list') }}" class="btn btn-added">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Vendor Name</label>
                        <input type="text" id="customer_name" class="form-control">
                        <span class="text-danger error-name"></span>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="email" class="form-control">
                        <span class="text-danger error-email"></span>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="phone" class="form-control">
                        <span class="text-danger error-phone"></span>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" id="country" class="form-control">
                        <span class="text-danger error-country"></span>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" id="city" class="form-control">
                        <span class="text-danger error-city"></span>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>PAN Number</label>
                        <input type="text" id="pan_number" class="form-control">
                        <span class="text-danger error-pan_number"></span>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>GST Number</label>
                        <input type="text" id="gst_number" class="form-control">
                        <span class="text-danger error-gst_number"></span>

                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="address" class="form-control"></textarea>
                        <span class="text-danger error-address"></span>

                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Photo</label>
                        <div class="image-upload">
                            <input type="file" id="avatar-input">
                            <div class="image-uploads">
                                <img src="{{ env('ImagePath').'/admin/assets/img/icons/upload.svg' }}" alt="Upload Icon">
                                <h4>Drag and drop a file to upload</h4>
                            </div>
                        </div>
                        <!-- Profile Image Preview -->
                        <div id="avatar-preview-container" style="display: none; margin-top: 10px;">
                            <img id="avatar-preview" src="" alt="Profile Image"
                                style="max-width: 150px; border-radius: 8px;">
                        </div>
                        <span class="text-danger error-avatar"></span>

                    </div>
                </div>
                <div class="col-lg-12">
                    <button type="submit" class="btn btn-submit me-2" id="updateCustomer">Update</button>
                    <a class="btn btn-cancel" href="{{ route('vendor.list') }}">Cancel</a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");
        let url = window.location.pathname;
        let customerId = url.split("/").pop();

        // AJAX call to get customer data
        $.ajax({
            url: `/api/getSupplier/${customerId}`,
            type: "GET",
            headers: {
                Authorization: `Bearer ${authToken}`,
            },
            success: function(response) {
                if (response.status) {
                    let customer = response.customer;

                    // Populate form fields
                    $("#customer_name").val(customer.name);
                    $("#email").val(customer.email);
                    $("#phone").val(customer.phone);
                    $("#country").val(customer.details.country);
                    $("#city").val(customer.details.city);
                    $("#address").val(customer.details.address);
                    $("#pan_number").val(customer.pan_number);
                    $("#gst_number").val(customer.gst_number);

                    // Show profile image if exists
                    if (customer.profile_image) {
                        $("#avatar-preview").attr("src", `{{ env('ImagePath').'/storage/' }}${customer.profile_image}`);
                        $("#avatar-preview-container").show(); // Make sure preview is visible
                    }
                } else {
                    Swal.fire("Error!", "Vendor not found!", "error");
                    window.location.href = "{{ route('vendor.list') }}";
                }
            },
            error: function() {
                Swal.fire("Error!", "Vendor not found!", "error");
                window.location.href = "{{ route('vendor.list') }}";
            },
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

        $("#updateCustomer").on("click", function(e) {
            e.preventDefault();

            // Reference the update button and store original HTML
            const $btn = $(this);
            const originalText = $btn.html();

            // Disable button and show spinner
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop("disabled", true);

            let formData = new FormData();
            formData.append("customer_name", $("#customer_name").val());
            formData.append("email", $("#email").val());
            formData.append("phone", $("#phone").val());
            formData.append("country", $("#country").val());
            formData.append("city", $("#city").val());
            formData.append("address", $("#address").val());
            formData.append("pan_number", $("#pan_number").val());
            formData.append("gst_number", $("#gst_number").val());

            let avatar = $("#avatar-input")[0].files[0]; // Corrected here
            if (avatar) {
                formData.append("avatar", avatar);
            }
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            formData.append("selectedSubAdminId", selectedSubAdminId);

            $.ajax({
                url: `/api/updateSupplier/${customerId}`,
                type: "POST", // Laravel doesn't support PUT with FormData directly
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    Authorization: `Bearer ${authToken}`,
                },
                success: function(response) {
                    $btn.html(originalText).prop("disabled", false); // Restore button

                    Swal.fire({
                        title: "Success!",
                        text: "Vendor update successfully!",
                        icon: "success",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#ff9f43",
                    }).then(() => {
                        window.location.href = "/vendors";
                    });
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
    });
</script>
@endpush