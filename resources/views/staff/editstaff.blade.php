@extends('layout.app')

@section('title', 'Edit Staff')

@section('content')
<style>
    @media screen and (max-width: 768px) {
        .form-group {
            margin-bottom: 10px !important
        }
    }

    @media screen and (max-width: 375px) {
        .permission_class {
            font-size: 13px !important;
        }
    }

    @media screen and (max-width: 360px) {
        .permission_class {
            font-size: 12px !important;
        }
    }

    @media screen and (max-width: 344px) {
        .permission_class {
            font-size: 11px !important;
        }
    }

    .btn-capture-face {
        background: #1b2850;
        color: #fff;
        width: 100%;
        font-weight: 600;
        padding: 8px;
        border-radius: 5px;
    }

    .btn-capture-face:hover {
        background: #2a3a6a;
        color: #fff;
    }

    .btn-register-face {
        border: 1px solid #ff9f43;
        color: #ff9f43;
        background: #fff;
    }

    .btn-register-face:hover {
        background: #fff7ef;
        color: #e8892f;
    }

    .face-recognition-video-shell {
        border: 2px solid #ffedd5;
        border-radius: 18px;
        overflow: hidden;
        background: linear-gradient(135deg, #111827, #374151);
    }

    .face-recognition-video {
        width: 100%;
        min-height: 340px;
        object-fit: cover;
        display: block;
        background: #111827;
    }

    .staff-submit-loader {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .staff-submit-loader .loader-box {
        background: #fff;
        border-radius: 8px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        color: #1b2850;
        font-weight: 600;
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Edit Staff</h4>

        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <input type="hidden" name="face_descriptor" id="face_descriptor">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" id="customer_name" class="form-control">
                        <div class="text-danger error-customer_name"></div>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" class="form-control">
                        <div class="text-danger error-email"></div>

                    </div>
                </div>
                <!-- Password -->
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control">
                        <div class="text-danger error-password"></div>
                    </div>
                </div>

                <!-- Roles -->
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-control">
                            <option value="" disabled selected>Select Role</option>
                            <option value="staff">Staff</option>
                            <option value="hr">HR</option>
                        </select>
                        <div class="text-danger error-role"></div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Phone <span class="text-danger">*</span></label>
                        <input type="text" id="phone" class="form-control">
                        <div class="text-danger error-phone"></div>

                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
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
                        <label>PAN Number</label>
                        <input type="text" id="pan_number" class="form-control">
                        <span class="text-danger error-pan_number"></span>

                    </div>
                </div>

                <div class="col-lg-4 col-12">
                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="address" name="address" class="form-control" style="height: 120px;"></textarea>
                        <div class="text-danger error-address"></div>
                    </div>
                </div>

                <!-- Avatar Upload -->
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Photo</label>
                        <div class="image-upload" style="margin: 0; position: relative; min-height: 120px; display: flex; justify-content: center; align-items: center;">
                            <input type="file" id="avatar-input" name="avatar" class="form-control">
                            <div class="image-uploads" id="avatar-upload-content">
                                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}"
                                    alt="Upload Icon">
                                <h4>Drag and drop a file to upload</h4>
                            </div>
                            <img id="avatar-preview" src="" alt="Profile Image" style="display: none; max-width: 100%; max-height: 120px; border-radius: 8px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;">
                        </div>
                        <div class="text-danger error-avatar"></div>
                    </div>
                </div>

                <!-- Face Photo Upload/Capture -->
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Face Photo <span class="text-muted small">(upload or use camera)</span></label>
                        <div class="image-upload" id="face-photo-upload-box" style="margin: 0; position: relative; overflow: hidden; display: flex; justify-content: center; align-items: center; min-height: 120px;">
                            <input type="file" name="face_photo" id="face_photo_input" class="form-control" accept="image/*">
                            <div class="image-uploads" id="face-upload-content">
                                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}" alt="Upload Icon">
                                <h4 id="face-photo-text">Drag and drop a file to upload</h4>
                            </div>
                            <img id="face-preview" src="" alt="Face Preview" style="display: none; max-width: 100%; max-height: 120px; border-radius: 8px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;">
                        </div>
                        <div class="text-danger error-face_photo"></div>
                        
                        <button type="button" class="btn btn-capture-face mt-2" id="captureFaceBtn">
                            <i class="fa-solid fa-camera me-1"></i>Capture Face via Camera
                        </button>
                        
                        <input type="hidden" name="captured_photo" id="captured_photo">
                    </div>
                </div>
                <div class="col-lg-12">
                    <button type="submit" class="btn btn-submit me-2" id="updateCustomer">Update</button>
                    <a class="btn btn-cancel" href="{{ route('staff.list') }}">Cancel</a>
                </div>
            </div>
            <hr>
            <div class="text-danger error-permissions mt-2"></div>

            <h5 class="mt-4 fw-bold d-flex align-items-center">
                PERMISSION
                <div class="form-check form-check-inline ms-3 mb-0 permission_class">
                    <input class="form-check-input" type="radio" name="permission_type" id="withPermission"
                        value="1" required>
                    <label class="form-check-label" for="withPermission">With Permission</label>
                </div>

                <div class="form-check form-check-inline mb-0 permission_class">
                    <input class="form-check-input" type="radio" name="permission_type" id="withoutPermission"
                        value="0">
                    <label class="form-check-label" for="withoutPermission">Without Permission</label>
                </div>
            </h5>

            <div id="permissionsSection" style="display:none; margin-top:15px;">
                <div class="form-group mb-3">
                    <label class="fw-normal">
                        <input type="checkbox" id="select_all_module"> Select All Module
                    </label>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="module-permission-table">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th class="text-start">Module Name</th>
                                <th>View</th>
                                <th>Insert</th>
                                <th>Edit</th>
                                <th>Delete</th>
                                <th>All</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Filled dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div id="staffSubmitLoader" class="staff-submit-loader">
        <div class="loader-box">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span>Please wait...</span>
        </div>
    </div>
</div>
@include('partials.face-recognition-modal')
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Show/hide permission section based on radio selection
        $('input[name="permission_type"]').on('change', function() {
            if ($('#withPermission').is(':checked')) {
                $('#permissionsSection').slideDown();
            } else {
                $('#permissionsSection').slideUp();
                // Optional: uncheck all checkboxes when hiding
                $('#permissionsSection').find('input[type="checkbox"]').prop('checked', false);
            }
        });
    });
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");
        let url = window.location.pathname;
        let customerId = url.split("/").pop();

        // AJAX call to get customer data
        $.ajax({
            url: `/api/getStaff/${customerId}`,
            type: "GET",
            headers: {
                Authorization: `Bearer ${authToken}`,
            },
            success: function(response) {
                if (response.status) {
                    let customer = response.customer;

                    // Populate form fields
                    $("#role").val(customer.role || "");
                    // if (customer.haspermission) {
                    console.log(customer.haspermission);
                    console.log(customer.haspermission);

                    $('input[name="permission_type"][value="' + String(customer.haspermission) +
                        '"]').prop('checked', true);
                    // Use the shared change handler to toggle UI consistently
                    $('input[name="permission_type"]').trigger('change');
                    // }
                    $("#customer_name").val(customer.name);
                    $("#email").val(customer.email || ""); // Use empty string if email is null
                    $("#phone").val(customer.phone);
                    $("#country").val(customer.details.country ||
                        ""); // Use empty string if country is null
                    $("#city").val(customer.details.city || ""); // Use empty string if city is null
                    $("#address").val(customer.details.address ||
                        ""); // Use empty string if address is null

                    // Show profile image if exists
                    if (customer.profile_image) {
                        $("#avatar-preview").attr("src", `/storage/${customer.profile_image}`).show();
                        $("#avatar-upload-content").hide();
                    }

                    // Show face photo if exists
                    if (customer.details && customer.details.face_photo) {
                        $("#face-preview").attr("src", `/storage/${customer.details.face_photo}`).show();
                        $("#face-upload-content").hide();
                    }

                    if (customer.face_descriptor) {
                        $("#face_descriptor").val(JSON.stringify(customer.face_descriptor));
                    }

                    let modules = response.modules;
                    let permissions = response.permissions;

                    let tbody = "";
                    modules.forEach(module => {
                        let perm = permissions.find(p => p.module_id === module.id) || {};
                        if (module.module === "Staff") {
                            return; // continue to next loop
                        }
                        // Check if all 4 permissions are enabled
                        let isAllChecked = perm.view && perm.add && perm.edit && perm
                            .delete;

                        tbody += `
                                        <tr>
                            <td class="align-middle">
                                <i class="fa fa-folder-open text-primary me-2"></i> ${module.module}
                                <input type="hidden" name="modules[${module.id}][module_id]" value="${module.id}">
                            </td>

                            <td class="text-center">
                                <input type="hidden" name="modules[${module.id}][view]" value="0">
                                <input type="checkbox" name="modules[${module.id}][view]" value="1"
                                    data-module="${module.id}" data-type="view"
                                    class="permission-checkbox form-check-input" ${perm.view ? 'checked' : ''}>
                            </td>

                            <td class="text-center">
                                <input type="hidden" name="modules[${module.id}][add]" value="0">
                                <input type="checkbox" name="modules[${module.id}][add]" value="1"
                                    data-module="${module.id}" data-type="add"
                                    class="permission-checkbox form-check-input" ${perm.add ? 'checked' : ''}>
                            </td>

                            <td class="text-center">
                                <input type="hidden" name="modules[${module.id}][edit]" value="0">
                                <input type="checkbox" name="modules[${module.id}][edit]" value="1"
                                    data-module="${module.id}" data-type="edit"
                                    class="permission-checkbox form-check-input" ${perm.edit ? 'checked' : ''}>
                            </td>

                            <td class="text-center">
                                <input type="hidden" name="modules[${module.id}][delete]" value="0">
                                <input type="checkbox" name="modules[${module.id}][delete]" value="1"
                                    data-module="${module.id}" data-type="delete"
                                    class="permission-checkbox form-check-input" ${perm.delete ? 'checked' : ''}>
                            </td>

                            <td class="text-center">
                                <input type="checkbox" class="check-all-row form-check-input" ${isAllChecked ? 'checked' : ''}>
                            </td>
                        </tr>`;
                    });


                    $("#module-permission-table tbody").html(tbody);

                    // Select all module checkboxes
                    $(document).on("change", "#select_all_module", function() {
                        $(".permission-checkbox").prop("checked", $(this).is(":checked"));
                    });

                    // Row-level "All" checkbox
                    $(document).on("change", ".check-all-row", function() {
                        const row = $(this).closest("tr");
                        const isChecked = $(this).is(":checked");
                        row.find(".permission-checkbox").prop("checked", isChecked);
                    });

                } else {
                    Swal.fire("Error!", "Customer not found!", "error");

                }
            },
            error: function() {
                Swal.fire("Error!", "Customer not found!", "error");

            },
        });

        // Handle File Input Change (Live Preview)
        $("#avatar-input").on("change", function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    $('#avatar-preview').attr('src', event.target.result).show();
                    $('#avatar-upload-content').hide();
                };
                reader.readAsDataURL(file);
            } else {
                $('#avatar-preview').attr('src', '').hide();
                $('#avatar-upload-content').show();
            }
        });

        // Face photo file input change
        $('#face_photo_input').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    $('#face-preview').attr('src', event.target.result).show();
                    $('#face-upload-content').hide();
                    $('#captured_photo').val('');
                };
                reader.readAsDataURL(file);
            } else {
                $('#face-preview').attr('src', '').hide();
                $('#face-upload-content').show();
            }
        });

        if (typeof window.OmsaiFaceRecognition !== 'undefined') {
            const faceRecognition = window.OmsaiFaceRecognition.init();

            $('#captureFaceBtn').on('click', function() {
                const staffName = $('#customer_name').val().trim() || 'New Staff';

                faceRecognition.open({
                    title: `Register Face for ${staffName}`,
                    subtitle: 'Ask the staff member to face the camera and capture one clear frame.',
                    captureLabel: 'Capture & Set',
                    onCapture: async function(descriptor, modal, imageDataUrl) {
                        $('#face_descriptor').val(JSON.stringify(descriptor));
                        
                        if (imageDataUrl) {
                            $('#face-preview').attr('src', imageDataUrl).show();
                            $('#face-upload-content').hide();
                            $('#captured_photo').val(imageDataUrl);
                        }

                        modal.showMatchInfo('Face captured successfully.');

                        setTimeout(function() {
                            modal.close();
                        }, 600);
                    }
                });
            });
        }

        // $("#updateCustomer").on("click", function(e) {
        //     e.preventDefault();
        //     const $btn = $(this);
        //     const originalText = $btn.html();

        //     let permissionSelected = $('.permission-checkbox:checked').length > 0;
        //     if (!permissionSelected) {
        //         $(".error-permissions").html("** Please select at least one permission. **");
        //         $btn.html(originalText).prop("disabled", false);
        //         return false;
        //     } else {
        //         $(".error-permissions").html("");
        //     }

        //     // Disable button and show loader
        //     $btn.html(
        //             '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
        //         )
        //         .prop("disabled", true);
        //     console.log($("#country").val());

        //     let formData = new FormData();
        //     formData.append("password", $("#password").val());
        //     formData.append("role", $("#role").val());
        //     formData.append("customer_name", $("#customer_name").val());
        //     formData.append("email", $("#email").val());
        //     formData.append("phone", $("#phone").val());
        //     formData.append("country", $("#country").val());
        //     formData.append("city", $("#city").val());
        //     formData.append("address", $("#address").val());

        //     let avatar = $("#avatar-input")[0].files[0];
        //     console.log(avatar);
        //     if (avatar) {
        //         formData.append("avatar", avatar);
        //     }

        //     $.ajax({
        //         url: `/api/updateStaff/${customerId}`,
        //         type: "POST", // Laravel doesn't support `PUT` directly with FormData, so use POST with `_method` as PUT
        //         data: formData,
        //         processData: false,
        //         contentType: false,
        //         headers: {
        //             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        //             Authorization: `Bearer ${authToken}`,
        //         },
        //         success: function(response) {
        //             $btn.html(originalText).prop("disabled", false); // Restore button
        //             if (response.status) {
        //                 Swal.fire({
        //                     title: "Success!",
        //                     text: "Customer update successfully!",
        //                     icon: "success",
        //                     confirmButtonText: "OK",
        //                     confirmButtonColor: "#ff9f43",
        //                 }).then(() => {
        //                     window.location.href = "/staff";
        //                 });
        //             }
        //         },
        //         error: function(xhr) {
        //             $btn.html(originalText).prop("disabled", false); // Restore button
        //             if (xhr.status === 422) {
        //                 let errors = xhr.responseJSON.errors;
        //                 $.each(errors, function(key, value) {
        //                     $(".error-" + key).html(value[
        //                         0]); // Show error below each field
        //                 });
        //             } else {
        //                 Swal.fire({
        //                     title: "Error!",
        //                     text: "Something went wrong. Please try again.",
        //                     icon: "error",
        //                     confirmButtonText: "OK"
        //                 });
        //             }
        //         },
        //     });
        // });


        $("#updateCustomer").on("click", function(e) {
            e.preventDefault();

            const $btn = $(this);
            const originalText = $btn.html();

            // let permissionSelected = $('.permission-checkbox:checked').length > 0;
            // if (!permissionSelected) {
            //     $(".error-permissions").html("** Please select at least one permission. **");
            //     $btn.html(originalText).prop("disabled", false);
            //     return false;
            // } else {
            //     $(".error-permissions").html("");
            // }

            $btn.html(
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
            ).prop("disabled", true);

            let formData = new FormData();
            formData.append("password", $("#password").val());
            formData.append("role", $("#role").val());
            formData.append("staff_name", $("#customer_name").val());
            formData.append("email", $("#email").val());
            formData.append("phone", $("#phone").val());
            formData.append("country", $("#country").val());
            formData.append("city", $("#city").val());
            formData.append("address", $("#address").val());
            let permissionType = $('input[name="permission_type"]:checked').val();

            // Append to FormData
            formData.append("permission_type", permissionType);

            let avatar = $("#avatar-input")[0].files[0];
            if (avatar) {
                formData.append("avatar", avatar);
            }

            let facePhoto = $("#face_photo_input")[0].files[0];
            if (facePhoto) {
                formData.append("face_photo", facePhoto);
            }
            if ($("#captured_photo").val()) {
                formData.append("captured_photo", $("#captured_photo").val());
            }
            if ($("#face_descriptor").val()) {
                formData.append("face_descriptor", $("#face_descriptor").val());
            }

            // Collect modules and permissions
            // let modules = {};
            // $(".permission-checkbox:checked").each(function() {
            //     let moduleId = $(this).data("module");
            //     let type = $(this).data("type");

            //     if (!modules[moduleId]) {
            //         modules[moduleId] = {
            //             module_id: moduleId,
            //             view: false,
            //             add: false,
            //             edit: false,
            //             delete: false
            //         };
            //     }

            //     if (type === 'view') modules[moduleId].view = true;
            //     if (type === 'add') modules[moduleId].add = true;
            //     if (type === 'edit') modules[moduleId].edit = true;
            //     if (type === 'delete') modules[moduleId].delete = true;
            // });

            // // Append module permissions to formData
            // let i = 0;
            // Object.values(modules).forEach((mod) => {
            //     formData.append(`modules[${i}][module_id]`, mod.module_id);
            //     if (mod.view) formData.append(`modules[${i}][view]`, 1);
            //     if (mod.add) formData.append(`modules[${i}][add]`, 1);
            //     if (mod.edit) formData.append(`modules[${i}][edit]`, 1);
            //     if (mod.delete) formData.append(`modules[${i}][delete]`, 1);
            //     i++;
            // });
            // Collect modules and permissions
            let modules = {};
            $(".permission-checkbox").each(function() { // remove :checked
                let moduleId = $(this).data("module");
                let type = $(this).data("type");

                if (!modules[moduleId]) {
                    modules[moduleId] = {
                        module_id: moduleId,
                        view: 0,
                        add: 0,
                        edit: 0,
                        delete: 0
                    };
                }

                // Always set 1 if checked, 0 if unchecked
                modules[moduleId][type] = $(this).is(":checked") ? 1 : 0;
            });

            // Append module permissions to formData
            let i = 0;
            Object.values(modules).forEach((mod) => {
                formData.append(`modules[${i}][module_id]`, mod.module_id);
                formData.append(`modules[${i}][view]`, mod.view);
                formData.append(`modules[${i}][add]`, mod.add);
                formData.append(`modules[${i}][edit]`, mod.edit);
                formData.append(`modules[${i}][delete]`, mod.delete);
                i++;
            });

            $.ajax({
                url: `/api/updateStaff/${customerId}`,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    Authorization: `Bearer ${authToken}`,
                },
                success: function(response) {
                    $btn.html(originalText).prop("disabled", false);
                    if (response.status) {
                        Swal.fire({
                            title: "Success!",
                            text: "Staff updated successfully!",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.href = "/staff";
                        });
                    }
                },
                error: function(xhr) {
                    $btn.html(originalText).prop("disabled", false);
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $(".error-" + key).html(value[0]);
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


<script>
    $(document).ready(function() {
        // When 'Select All Module' is clicked
        $('#select_all_module').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.permission-checkbox, .check-all-row').prop('checked', isChecked);
        });

        // When 'All' checkbox in each row is clicked
        $('.check-all-row').on('change', function() {
            const isChecked = $(this).is(':checked');
            const row = $(this).closest('tr');
            row.find('.permission-checkbox').prop('checked', isChecked);
        });

        // If any permission checkbox is changed manually, update the 'All' checkbox in that row
        $('.permission-checkbox').on('change', function() {
            const row = $(this).closest('tr');
            const allChecked = row.find('.permission-checkbox').length === row.find(
                '.permission-checkbox:checked').length;
            row.find('.check-all-row').prop('checked', allChecked);
        });

        // Sync master checkbox if all checkboxes are checked or unchecked
        function syncSelectAllModule() {
            const totalPermissions = $('.permission-checkbox').length;
            const totalChecked = $('.permission-checkbox:checked').length;
            $('#select_all_module').prop('checked', totalPermissions === totalChecked);
        }

        // Call sync function when any permission checkbox is changed
        $('.permission-checkbox, .check-all-row').on('change', syncSelectAllModule);
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
@include('partials.face-recognition-script')
@endpush