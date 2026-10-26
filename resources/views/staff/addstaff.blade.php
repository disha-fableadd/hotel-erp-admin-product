@extends('layout.app')

@section('title', 'Add Staff')

@section('content')
<style>
    @media screen and (max-width: 768px) {
        .form-group {
            margin-bottom: 10px !important
        }
    }

    @media screen and (max-width: 375px) {
        .permission_class {
            font-size: 12px !important;
            font-weight: bold;
        }
    }
    @media screen and (max-width: 360px) {
        .permission_class {
            font-size: 11px !important;
            font-weight: bold;
        }
    }
    @media screen and (max-width: 344px) {
        .permission_class {
            font-size: 10px !important;
            font-weight: bold;
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
            <h4>Add Staff</h4>

        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="customerForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="face_descriptor" id="face_descriptor">
                <div class="row">
                    <!-- Customer Name -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Staff Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control">
                            <div class="text-danger error-customer_name"></div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" autocomplete="off">
                            <div class="text-danger error-email"></div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control" autocomplete="off">
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

                    <!-- Phone -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control">
                            <div class="text-danger error-phone"></div>
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="col-lg-3 col-sm-6 col-12">
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

                    <!-- PAN Number -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>PAN Number</label>
                            <input type="text" name="pan_number" id="pan_number" class="form-control">
                            <span class="text-danger error-pan_number"></span>
                        </div>
                    </div>



                    <!-- Address -->
                    <div class="col-lg-4 col-12">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" id="address" class="form-control" style="height: 120px;"></textarea>
                            <div class="text-danger error-address"></div>
                        </div>
                    </div>

                    <!-- Avatar Upload -->
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Photo</label>
                            <div class="image-upload" style="margin: 0; position: relative; min-height: 120px; display: flex; justify-content: center; align-items: center;">
                                <input type="file" name="avatar" id="avatar" class="form-control">
                                <div class="image-uploads" id="avatar-upload-content">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}"
                                        alt="Upload Icon">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                                <img id="avatar-preview" src="" alt="Avatar Preview" style="display: none; max-width: 100%; max-height: 120px; border-radius: 8px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;">
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

                    <!-- Submit & Cancel Buttons -->
                    <div class="col-lg-12">
                        <button type="submit" id="submitCustomerBtn" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ route('staff.list') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
                <hr>
                <div class="text-danger error-permissions mt-2"></div>

                <div class="d-flex align-items-center mt-4">
                    <h5 class="fw-bold mb-0 me-3">PERMISSION:</h5>

                    <div class="form-check form-check-inline mb-0 permission_class">
                        <input class="form-check-input" type="radio" name="permission_type" id="withPermission"
                            value="1">
                        <label class="form-check-label" for="withPermission">With Permission</label>
                    </div>

                    <div class="form-check form-check-inline mb-0 permission_class">
                        <input class="form-check-input" type="radio" name="permission_type" id="withoutPermission"
                            value="0">
                        <label class="form-check-label" for="withoutPermission">Without Permission</label>
                    </div>
                </div>

                <!-- Permissions Section -->
                <div id="permissionsSection" style="display:none; margin-top:15px;">
                    <div class="form-group mb-3">
                        <label class="fw-normal">
                            <input type="checkbox" id="select_all_module"> Select All Module
                        </label>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
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
                                @foreach($modules as $module)
                                @if($module->module !== 'Staff')
                                <tr>
                                    <td class="align-middle">
                                        <i class="fa fa-folder-open text-primary me-2"></i> {{ $module->module }}
                                        <input type="hidden" name="modules[{{ $module->id }}][module_id]"
                                            value="{{ $module->id }}">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="modules[{{ $module->id }}][view]"
                                            class="permission-checkbox form-check-input">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="modules[{{ $module->id }}][add]"
                                            class="permission-checkbox form-check-input">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="modules[{{ $module->id }}][edit]"
                                            class="permission-checkbox form-check-input">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="modules[{{ $module->id }}][delete]"
                                            class="permission-checkbox form-check-input">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="check-all-row form-check-input">
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
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

        // Handle form submission
        $("#customerForm").submit(function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            $(".text-danger").html(""); // clear previous errors
            $(".error-permissions").html("");

            let hasError = false;

            // ✅ Permission check
            if (!$('input[name="permission_type"]:checked').length) {
                $(".error-permissions").html("** Please select With or Without Permission. **");
                hasError = true;
            }

            // ✅ Staff Name validation
            let customer_name = $("#customer_name").val().trim();
            if (customer_name === "") {
                $(".error-customer_name").html(" Staff name is required. ");
                hasError = true;
            } else if (customer_name.length < 3) {
                $(".error-customer_name").html(" Staff name must be at least 3 characters. ");
                hasError = true;
            }

            // ✅ Email validation
            let email = $("#email").val().trim();
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === "") {
                $(".error-email").html(" Email is required. ");
                hasError = true;
            } else if (!emailPattern.test(email)) {
                $(".error-email").html(" Please enter a valid email address. ");
                hasError = true;
            }

            // ✅ Password validation
            let password = $("#password").val().trim();
            if (password === "") {
                $(".error-password").html(" Password is required. ");
                hasError = true;
            } else if (password.length < 6) {
                $(".error-password").html(" Password must be at least 6 characters. ");
                hasError = true;
            }

            // ✅ Phone validation
            let phone = $("#phone").val().trim();
            let phonePattern = /^[0-9]{10}$/; // only 10-digit numbers
            if (phone === "") {
                $(".error-phone").html(" Phone number is required. ");
                hasError = true;
            } else if (!phonePattern.test(phone)) {
                $(".error-phone").html(" Please enter a valid 10-digit phone number. ");
                hasError = true;
            }

            // Stop form submission if errors exist
            if (hasError) {
                return false;
            }

            const $btn = $("#submitCustomerBtn");
            const originalText = $btn.html();

            // Show spinner and disable button
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop("disabled", true);

            if ($('#withPermission').is(':checked')) {
                let permissionSelected = $('.permission-checkbox:checked').length > 0;

                if (!permissionSelected) {
                    $(".error-permissions").html("** Please select at least one permission. **");
                    $btn.html(originalText).prop("disabled", false);
                    return false;
                } else {
                    $(".error-permissions").html("");
                }
            } else {
                // If "Without Permission" is selected, clear error
                $(".error-permissions").html("");
            }

            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            if (selectedSubAdminId) {
                formData.append("sub_admin_id", selectedSubAdminId);
            }
            $.ajax({
                url: "/api/createStaff", // Ensure API route is correct
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
                            text: "Staff added successfully!",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.href = "/staff";
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

        // Remove error message when user starts typing
        // $("input, select").on("input", function() {
        //     let fieldName = $(this).attr("name");
        //     $(".error-" + fieldName).html("");
        // });

        // Handle Avatar Input Change (Live Preview)
        $("#avatar").on("change", function(e) {
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

    });
</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
@include('partials.face-recognition-script')
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
@endpush