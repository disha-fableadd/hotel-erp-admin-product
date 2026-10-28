@extends('layout.app')

@section('title', 'Edit Branch')

@section('content')
@php
    $adminPrefix = \App\Http\Controllers\api\StaffController::getAdminPrefix(auth()->user());
@endphp
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
            <h4>Edit Branch</h4>

        </div>
        <div class="page-btn">
            <a href="{{ route('subbranch.list') }}" class="btn btn-added">
                Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <input type="hidden" name="id" id="id">
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Branch Name</label>
                        <input type="text" id="customer_name" class="form-control">
                        <div class="text-danger error-customer_name"></div>

                    </div>
                </div>
                <!-- Username -->
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            @if(!empty($adminPrefix))
                                <span class="input-group-text bg-light text-muted fw-bold" id="admin_prefix_addon">{{ $adminPrefix }}_</span>
                            @endif
                            <input type="text" name="username" maxlength="60" id="username"
                                class="form-control" placeholder="e.g. branch1" autocomplete="off" style="text-transform: lowercase;">
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Prefix will be maintained for login.</small>
                        <div class="text-danger error-username"></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="email" class="form-control">
                        <div class="text-danger error-email"></div>

                    </div>
                </div>
                <!-- Password -->
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" id="password" class="form-control">
                        <div class="text-danger error-password"></div>
                    </div>
                </div>

                <!-- Roles -->

                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="phone" class="form-control">
                        <div class="text-danger error-phone"></div>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" id="country" name="country" class="form-control">
                        <div class="text-danger error-country"></div>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" id="city" name="city" class="form-control">
                        <div class="text-danger error-city"></div>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="address" name="address" class="form-control"></textarea>
                        <div class="text-danger error-address"></div>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Photo</label>
                        <div class="image-upload">
                            <input type="file" id="avatar-input" name="avatar">
                            <div class="image-uploads">
                                <img src="/admin/assets/img/icons/upload.svg" alt="Upload Icon" onerror="this.onerror=null; this.src='/admin/assets/img/icons/upload.svg';">
                                <h4>Drag and drop a file to upload</h4>
                            </div>
                        </div>
                        <div class="text-danger error-avatar"></div>

                        <!-- Profile Image Preview -->
                        <div id="avatar-preview-container" style="display: none; margin-top: 10px;">
                            <img id="avatar-preview" src="" alt="Profile Image"
                                style="max-width: 100px; border-radius: 8px;"
                                onerror="this.onerror=null; this.src='/admin/assets/img/customer/customer5.jpg';">
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <button type="submit" class="btn btn-submit me-2" id="updateCustomer">Update</button>
                    <a class="btn btn-cancel" href="{{ route('subbranch.list') }}">Cancel</a>
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

        $.ajax({
            url: `/api/getSubbranch/${customerId}`,
            type: "GET",
            headers: {
                Authorization: `Bearer ${authToken}`,
            },
            success: function(response) {
                if (response.status && response.data) {
                    const customer = response.data;
                    const details = customer.user_detail ?? {};

                    // Populate form fields
                    $("#customer_name").val(customer.name || "");
                    let rawUsername = customer.username || "";
                    let prefix = "{{ $adminPrefix ? $adminPrefix . '_' : '' }}";
                    if (prefix && rawUsername.toLowerCase().startsWith(prefix.toLowerCase())) {
                        $("#username").val(rawUsername.substring(prefix.length));
                    } else {
                        $("#username").val(rawUsername);
                    }
                    $("#id").val(customer.id || "");
                    $("#email").val(customer.email || "");
                    $("#phone").val(customer.phone || "");
                    $("#country").val(details.country || "");
                    $("#city").val(details.city || "");
                    $("#address").val(details.address || "");

                    // Show profile image if it exists
                    let rawAvatar = customer.profile_image_url || customer.profile_image;
                    if (rawAvatar) {
                        let clean = (window.resolveImageUrl ? window.resolveImageUrl(rawAvatar) : ('/storage/' + String(rawAvatar).replace(/^https?:\/\/[^\/]+/, '').replace(/^\/+/, '').replace(/^storage\//, '')));
                        $("#avatar-preview").attr("src", clean);
                        $("#avatar-preview-container").show();
                    } else {
                        $("#avatar-preview-container").hide();
                    }
                } else {
                    Swal.fire("Error!", "Branch not found!", "error").then(() => {
                        window.location.href = "{{ route('subbranch.list') }}";
                    });
                }
            },
            error: function() {
                Swal.fire("Error!", "Branch not found!", "error").then(() => {
                    window.location.href = "{{ route('subbranch.list') }}";
                });
            },
        });

        // Handle File Input Change (Live Preview)
        $("#avatar-input").change(function(event) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#avatar-preview").attr("src", e.target.result);
                $("#avatar-preview-container").show();
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        $("#updateCustomer").on("click", function(e) {
            e.preventDefault();
            const $btn = $(this);
            const originalText = $btn.html();

            // Disable button and show loader
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop("disabled", true);
            console.log($("#country").val());

            let formData = new FormData();
            formData.append("password", $("#password").val());
            formData.append("id", $("#id").val());
            formData.append("customer_name", $("#customer_name").val());
            formData.append("username", ($("#username").val() || "").trim());
            formData.append("email", $("#email").val());
            formData.append("phone", $("#phone").val());
            formData.append("country", $("#country").val());
            formData.append("city", $("#city").val());
            formData.append("address", $("#address").val());

            let avatar = $("#avatar-input")[0].files[0];
            console.log(avatar);
            if (avatar) {
                formData.append("avatar", avatar);
            }

            $.ajax({
                url: `/api/updateSubbranch`,
                type: "POST", // Laravel doesn't support `PUT` directly with FormData, so use POST with `_method` as PUT
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    Authorization: `Bearer ${authToken}`,
                },
                success: function(response) {
                    $btn.html(originalText).prop("disabled", false); // Restore button
                    if (response.status) {
                        Swal.fire({
                            title: "Success!",
                            text: "Branch update successfully!",
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
