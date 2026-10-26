@extends('layout.app')

@section('title', 'Edit Staff')

@section('content')
    <style>
        :root {
            --staff-accent: #ff9f43;
            --staff-accent-dark: #ff9f43;
            --staff-light-bg: #f5f6f8;
            --staff-border: #dbe0e6;
            --staff-text: #1b2850;
        }

        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 10px !important
            }

            .staff-form-card {
                padding: 14px !important;
            }

            .staff-step-tabs {
                grid-template-columns: 1fr;
            }

            .staff-step-actions {
                justify-content: flex-start;
            }
        }

        a.btn.back-button {
            background: #ff9f43;
            color: #fff;
        }

        .staff-form-card {
            background: var(--staff-light-bg);
            border-radius: 14px;
            padding: 20px;
        }

        .staff-step-progress {
            height: 14px;
            background: #e3e6ea;
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 14px;
        }

        .staff-step-progress-fill {
            width: 50%;
            height: 100%;
            border-radius: inherit;
            background: repeating-linear-gradient(-45deg,
                    var(--staff-accent) 0 8px,
                    #ff9f43 8px 16px);
            transition: width 0.25s ease-in-out;
        }

        .staff-step-tabs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .staff-step-tab {
            border: 1px solid var(--staff-border);
            background: #fff;
            color: var(--staff-text);
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .staff-step-tab.active {
            background: var(--staff-accent);
            border-color: var(--staff-accent);
            color: #fff;
            box-shadow: 0 8px 20px rgba(255, 159, 67, 0.24);
        }

        .staff-step-title {
            margin-bottom: 8px;
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
        }

        .staff-form-card .form-group label {
            color: #111827;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .staff-step-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 6px;
        }

        .staff-step-actions .btn {
            border-radius: 6px;
            min-width: 88px;
            font-weight: 600;
        }

        .staff-step-actions .btn-submit {
            background: var(--staff-accent);
            border-color: var(--staff-accent);
        }

        .staff-step-actions .btn-submit:hover {
            background: var(--staff-accent-dark);
            border-color: var(--staff-accent-dark);
        }

        .staff-step-actions .btn-cancel {
            background: #fff;
            border: 1px solid var(--staff-border);
            color: #475569;
        }

        .staff-step-actions .btn-cancel:hover {
            background: #eef2f7;
            color: #1e293b;
        }
    </style>
    <div class="content">
        {{-- <div class="page-header">
            <div class="page-title">
                <h4>Edit Staff</h4>

            </div>
        </div> --}}
         <div class="page-header ">
            <div class="page-title">
                <h4>Edit Staff</h4>
            </div>
             <div class="back-button">
                <a href="{{ route('staff.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body staff-form-card">
                <h5 class="staff-step-title mb-4">Staff Details</h5>
                <div class="row g-3">
                    <!-- Customer Name -->
                    <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                        <div class="form-group">
                            {{-- <label>Staff Name</label> --}}
                            <label>Staff Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" maxlength="80" id="customer_name"
                                class="form-control">
                            <div class="text-danger error-customer_name"></div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" autocomplete="off">
                            <div class="text-danger error-email"></div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                        <div class="form-group ">
                            <label>Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" name="password" id="password" class="form-control"
                                    autocomplete="new-password" placeholder="Leave blank to keep current">
                                <span class="position-absolute top-50 end-0 translate-middle-y me-2"
                                    style="cursor: pointer;" onclick="togglePasswordVisibility()">
                                    <i id="togglePasswordIcon" class="fa fa-eye"></i>
                                </span>
                            </div>
                            <div class="text-danger error-password"></div>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                        <div class="form-group">
                            <label>Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" maxlength="10" pattern="\d{10}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)" class="form-control">
                            <div class="text-danger error-phone"></div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" id="country" name="country" class="form-control">
                            <div class="text-danger error-country"></div>

                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" id="city" name="city" class="form-control">
                            <div class="text-danger error-city"></div>

                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                        <div class="form-group">
                            <label>Role <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-control">
                                <option value="">Select Role</option>
                                <option value="staff">Staff</option>
                                <option value="hr">HR</option>
                            </select>
                            <div class="text-danger error-role"></div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                        <div class="form-group">
                            <label>Department</label>
                            <select id="department_id" name="department_id" class="form-control">
                                <option value="">Select Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger error-department_id"></div>

                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                        <div class="form-group">
                            <label>Designation</label>
                            <select id="designation_id" name="designation_id" class="form-control" disabled>
                                <option value="">Select Designation</option>
                            </select>
                            <div class="text-danger error-designation_id"></div>

                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                        <div class="form-group">
                            <label>Joining Date <span class="text-danger">*</span></label>
                            <input type="date" id="joining_date" name="joining_date" class="form-control">
                            <div class="text-danger error-joining_date"></div>

                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                        <div class="form-group">
                            <label>Salary <span class="text-danger">*</span></label>
                            <input type="number" id="salary" name="salary" class="form-control" min="0"
                                step="0.01">
                            <div class="text-danger error-salary"></div>

                        </div>
                    </div>
                    <!-- <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                        <div class="form-group">
                            <label>PAN Number</label>
                            <input type="text" id="pan_number" class="form-control">
                            <span class="text-danger error-pan_number"></span>

                        </div>
                    </div> -->
                    <!-- <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                        <div class="form-group">
                            <label>GST Number</label>
                            <input type="text" id="gst_number" class="form-control">
                            <span class="text-danger error-gst_number"></span>

                        </div>
                    </div> -->
                    <div class="col-lg-4 col-12 staff-basic-step">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea id="address" name="address" class="form-control"></textarea>
                            <div class="text-danger error-address"></div>

                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                        <div class="form-group">
                            <label>Photo</label>
                            <div class="image-upload">
                                <input type="file" id="avatar-input" name="avatar" accept="image/*">
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}"
                                        alt="Upload Icon">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                            <div class="text-danger error-avatar"></div>

                            <!-- Profile Image Preview -->
                            <div id="avatar-preview-container" style="display: none; margin-top: 10px;">
                                <img id="avatar-preview" src="" alt="Profile Image"
                                    style="max-width: 100px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                        <div class="form-group">
                            <label>Face Photo <span class="text-muted" style="font-size:11px;font-weight:400;">(upload or use camera)</span></label>
                            <div class="image-upload">
                                <input type="file" id="face-photo-input" name="face_photo" accept="image/*">
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}" alt="Upload Icon">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                            <button type="button" id="openFaceCamBtn" class="btn btn-sm mt-2 w-100"
                                style="background:#1b2850;color:#fff;border-radius:6px;font-weight:600;">
                                <i class="fas fa-camera me-1"></i> Capture Face via Camera
                            </button>
                            <div class="text-danger error-face_photo"></div>
                            <!-- Face detection score badge -->
                            <div id="face-detection-badge" style="display:none;margin-top:6px;font-size:13px;padding:6px 10px;border-radius:6px;background:#f8f9fa;border:1px solid #e2e8f0;"></div>
                            <div id="face-photo-preview-container" style="display:none; margin-top:10px;">
                                <img id="face-photo-preview" src="" alt="Face Photo"
                                    style="max-width:120px;border-radius:8px;border:2px solid #ff9f43;padding:3px;">
                                <div style="font-size:11px;color:#28a745;margin-top:4px;" id="face-capture-label"></div>
                            </div>
                            <!-- Hidden canvas for camera capture -->
                            <canvas id="face-capture-canvas" style="display:none;"></canvas>
                        </div>
                    </div>
                    <!-- Submit & Cancel Buttons -->
                    <div class="col-lg-12 staff-step-actions mt-4">
                        <button type="submit" class="btn btn-submit me-2" id="updateCustomer">Update</button>
                        <a class="btn btn-cancel" href="{{ route('staff.list') }}">Cancel</a>
                    </div>
                </div>
                <div id="editStaffJobPermissionSection">
                    <hr>
                    <div class="text-danger error-permissions mt-2"></div>
                {{--
                <h5 class="mt-4 fw-bold d-flex align-items-center">
                    PERMISSION :
                    <div class="form-check form-check-inline ms-3 mb-0">
                        <input class="form-check-input" type="radio" name="permission_type" id="withPermission"
                            value="1" required>
                        <label class="form-check-label" for="withPermission">With Permission</label>
                    </div>

                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="permission_type" id="withoutPermission"
                            value="0">
                        <label class="form-check-label" for="withoutPermission">Without Permission</label>
                    </div>
                </h5> --}}

                    <div class="d-flex align-items-center mt-4">
                        <h5 class="fw-bold mb-0 me-3">PERMISSION:</h5>

                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="permission_type" id="withPermission"
                                value="1">
                            <label class="form-check-label" for="withPermission">With Permission</label>
                        </div>

                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="permission_type" id="withoutPermission"
                                value="0">
                            <label class="form-check-label" for="withoutPermission">Without Permission</label>
                        </div>
                    </div>

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
        </div>

    </div>
@endsection

@push('js')
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
    <script>
        const IMAGE_PATH = "{{ rtrim(env('ImagePath'), '/') }}";
        const designationData = @json($designations);
        const HR_PERMISSION_MODULES = ['attendance', 'leaves', 'payroll', 'staff'];

        function applyRoleBasedPermissionFilter() {
            const selectedRole = String($('#role').val() || '').toLowerCase();
            const showHrModulesOnly = selectedRole === 'hr';

            $('#module-permission-table tbody tr.permission-module-row').each(function() {
                const $row = $(this);
                const moduleName = String($row.data('module-name') || '').toLowerCase();
                const shouldShow = showHrModulesOnly
                    ? HR_PERMISSION_MODULES.includes(moduleName)
                    : moduleName !== 'staff';

                if (shouldShow) {
                    $row.show();
                } else {
                    $row.hide();
                    $row.find('input[type="checkbox"]').prop('checked', false);
                }
            });

            const $visiblePermissions = $('#module-permission-table tbody tr:visible .permission-checkbox');
            const visibleChecked = $visiblePermissions.filter(':checked').length;
            $('#select_all_module').prop('checked', $visiblePermissions.length > 0 && $visiblePermissions.length === visibleChecked);
        }

        function populateDesignationOptions(selectedDepartmentId = '', selectedDesignationId = '') {
            const $designation = $('#designation_id');
            const departmentId = String(selectedDepartmentId || '');
            const designationId = String(selectedDesignationId || '');

            $designation.empty().append('<option value="">Select Designation</option>');

            if (!departmentId) {
                $designation.prop('disabled', true);
                return;
            }

            const filteredDesignations = designationData.filter((designation) =>
                String(designation.department_id) === departmentId);

            filteredDesignations.forEach((designation) => {
                const isSelected = designationId && String(designation.id) === designationId;
                const option = new Option(designation.designation_name, designation.id, isSelected, isSelected);
                $designation.append(option);
            });

            $designation.prop('disabled', false);
        }

        $(document).ready(function() {
            $('#department_id').on('change', function() {
                populateDesignationOptions($(this).val());
            });
            $('#role').on('change', applyRoleBasedPermissionFilter);



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
            populateDesignationOptions($('#department_id').val(), $('#designation_id').val());

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
                        let details = customer.details || {};

                        // Populate form fields
                        $("#role").val(customer.role || "");
                        // if (customer.haspermission) {
                        // console.log(customer.haspermission);
                        // console.log(customer.haspermission);

                        $('input[name="permission_type"][value="' + String(customer.haspermission) +
                            '"]').prop('checked', true);
                        // Use the shared change handler to toggle UI consistently
                        $('input[name="permission_type"]').trigger('change');
                        // }
                        $("#customer_name").val(customer.name);
                        $("#email").val(customer.email || ""); // Use empty string if email is null
                        $("#phone").val(customer.phone);
                        $("#gst_number").val(customer.gst_number);
                        $("#pan_number").val(customer.pan_number);
                        $("#country").val(details.country ||
                            ""); // Use empty string if country is null
                        $("#city").val(details.city || ""); // Use empty string if city is null
                        $("#address").val(details.address ||
                            ""); // Use empty string if address is null
                        $("#department_id").val(details.department_id || "");
                        populateDesignationOptions(details.department_id || "", details.designation_id || "");

                        // Format joining_date using UTC components to avoid timezone issues
                       if (details.joining_date) {
                            $("#joining_date").val(details.joining_date.split('T')[0]);
                        } else {
                            $("#joining_date").val("");
                        }

                        if (details.shift_time) {
                            if ($("#shift_time option[value='" + details.shift_time + "']").length === 0) {
                                $("#shift_time").append(
                                    `<option value="${details.shift_time}">${details.shift_time}</option>`
                                );
                            }
                        }
                        $("#shift_time").val(details.shift_time || "");

                        $("#salary").val(details.salary || "");

                        if (details.working_location) {
                            if ($("#working_location option[value='" + details.working_location + "']").length ===
                                0) {
                                $("#working_location").append(
                                    `<option value="${details.working_location}">${details.working_location}</option>`
                                );
                            }
                        }
                        $("#working_location").val(details.working_location || "");

                        // Show profile image if exists
                        if (customer.profile_image) {
                            $("#avatar-preview").attr("src", `${IMAGE_PATH}/storage/${customer.profile_image}`);
                            $("#avatar-preview-container").show(); // Make sure preview is visible
                        }

                        if (details.face_photo) {
                            $("#face-photo-preview").attr("src",
                                `${IMAGE_PATH}/storage/${details.face_photo}`);
                            $("#face-photo-preview-container").show();
                        }

                        let modules = response.modules;
                        let permissions = response.permissions;

                        let tbody = "";
                        modules.forEach(module => {
                            let perm = permissions.find(p => p.module_id === module.id) || {};
                            // Check if all 4 permissions are enabled
                            let isAllChecked = perm.view && perm.add && perm.edit && perm
                                .delete;

                            tbody += `
                                        <tr class="permission-module-row" data-module-name="${String(module.module || '').toLowerCase()}">
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
                        applyRoleBasedPermissionFilter();

                    } else {
                        Swal.fire("Error!", "Customer not found!", "error");

                    }
                },
                error: function() {
                    Swal.fire("Error!", "Customer not found!", "error");

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

            $("#face-photo-input").change(function(event) {
                let file = event.target.files[0];
                let errorDiv = $(".error-face_photo");
                errorDiv.html("");

                if (!file) {
                    $("#face-photo-preview-container").hide();
                    return;
                }

                let allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    errorDiv.html(" Only image files (JPEG, PNG, JPG, WEBP, GIF) are allowed. ");
                    $(this).val("");
                    $("#face-photo-preview-container").hide();
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    errorDiv.html(" Image size must not exceed 2MB. ");
                    $(this).val("");
                    $("#face-photo-preview-container").hide();
                    return;
                }

                let reader = new FileReader();
                reader.onload = function(e) {
                    $("#face-photo-preview").attr("src", e.target.result);
                    $("#face-photo-preview-container").show();
                };
                reader.readAsDataURL(file);
            });

            $("#updateCustomer").on("click", function(e) {
                e.preventDefault();
                $(".text-danger").html("");
                $(".error-permissions").html("");

                let hasError = false;

                // Staff Name validation
                let staffName = $("#customer_name").val().trim();
                if (staffName === "") {
                    $(".error-customer_name").html(" Staff name is required. ");
                    hasError = true;
                } else if (staffName.length < 3) {
                    $(".error-customer_name").html(" Staff name must be at least 3 characters. ");
                    hasError = true;
                } else if (staffName.length > 80) {
                    $(".error-customer_name").html(" Staff name must not exceed 80 characters. ");
                    hasError = true;
                }

                // Password validation (optional)
                let password = $("#password").val().trim();
                if (password !== "") {
                    if (password.length < 8) {
                        $(".error-password").html(" Password must be at least 8 characters. ");
                        hasError = true;
                    }
                }

                // Phone validation
                let phone = $("#phone").val().trim();
                let phonePattern = /^[0-9]{10}$/;
                if (phone === "") {
                    $(".error-phone").html(" Phone number is required. ");
                    hasError = true;
                } else if (!phonePattern.test(phone)) {
                    $(".error-phone").html(" Please enter a valid 10-digit phone number. ");
                    hasError = true;
                }

                // Role validation
                let role = $("#role").val();
                if (!role) {
                    $(".error-role").html(" Role is required. ");
                    hasError = true;
                }

                // Joining Date validation
                let joining_date = $("#joining_date").val();
                if (!joining_date) {
                    $(".error-joining_date").html(" Joining date is required. ");
                    hasError = true;
                }

                // Salary validation
                let salary = $("#salary").val().trim();
                if (salary === "") {
                    $(".error-salary").html(" Salary is required. ");
                    hasError = true;
                } else if (isNaN(salary) || Number(salary) < 0) {
                    $(".error-salary").html(" Salary must be a valid non-negative number. ");
                    hasError = true;
                }


                // Permission check
                if (!$('input[name="permission_type"]:checked').length) {
                    $(".error-permissions").html("** Please select With or Without Permission. **");
                    hasError = true;
                }

                let facePhoto = $("#face-photo-input")[0].files[0];
                if (facePhoto) {
                    let allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                    if (!allowedTypes.includes(facePhoto.type)) {
                        $(".error-face_photo").html(
                            " Only image files (JPEG, PNG, JPG, WEBP, GIF) are allowed. ");
                        hasError = true;
                    } else if (facePhoto.size > 2 * 1024 * 1024) {
                        $(".error-face_photo").html(" Image size must not exceed 2MB. ");
                        hasError = true;
                    }
                }

                // If errors exist, stop
                if (hasError) {
                    return false;
                }

                // If "With Permission" selected, ensure at least one permission checkbox is checked
                if ($('#withPermission').is(':checked')) {
                    if ($('.permission-checkbox:checked').length === 0) {
                        $(".error-permissions").html("** Please select at least one permission. **");
                        return false;
                    }
                }


                const $btn = $(this);
                const originalText = $btn.html();

                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                ).prop("disabled", true);

                let formData = new FormData();
                if (password !== "") {
                    formData.append("password", password);
                }
                formData.append("password", $("#password").val());
                formData.append("role", $("#role").val());
                formData.append("staff_name", $("#customer_name").val());
                formData.append("email", $("#email").val());
                formData.append("phone", $("#phone").val());
                formData.append("gst_number", $("#gst_number").val());
                formData.append("pan_number", $("#pan_number").val());
                formData.append("country", $("#country").val());
                formData.append("city", $("#city").val());
                formData.append("address", $("#address").val());
                formData.append("department_id", $("#department_id").val());
                formData.append("designation_id", $("#designation_id").val());
                formData.append("joining_date", $("#joining_date").val());
                formData.append("salary", $("#salary").val());
                let permissionType = $('input[name="permission_type"]:checked').val();

                // Append to FormData
                formData.append("permission_type", permissionType);

                let avatar = $("#avatar-input")[0].files[0];
                if (avatar) {
                    formData.append("avatar", avatar);
                }

                // Inject camera-captured blob if available (takes priority over file input)
                if (window._faceCaptureBlob) {
                    formData.append('face_photo', window._faceCaptureBlob, 'face_capture.jpg');
                } else {
                    let facePhotoFile = $("#face-photo-input")[0].files[0];
                    if (facePhotoFile) {
                        formData.append("face_photo", facePhotoFile);
                    }
                }

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
            $(document).on('change', '#select_all_module', function() {
                const isChecked = $(this).is(':checked');
                $('#module-permission-table tbody tr:visible .permission-checkbox, #module-permission-table tbody tr:visible .check-all-row')
                    .prop('checked', isChecked);
            });

            // When 'All' checkbox in each row is clicked
            $(document).on('change', '.check-all-row', function() {
                const isChecked = $(this).is(':checked');
                const row = $(this).closest('tr');
                row.find('.permission-checkbox').prop('checked', isChecked);
            });

            // If any permission checkbox is changed manually, update the 'All' checkbox in that row
            $(document).on('change', '.permission-checkbox', function() {
                const row = $(this).closest('tr');
                const allChecked = row.find('.permission-checkbox').length === row.find(
                    '.permission-checkbox:checked').length;
                row.find('.check-all-row').prop('checked', allChecked);
            });

            // Sync master checkbox if all checkboxes are checked or unchecked
            function syncSelectAllModule() {
                const totalVisiblePermissions = $('#module-permission-table tbody tr:visible .permission-checkbox')
                    .length;
                const totalVisibleChecked = $('#module-permission-table tbody tr:visible .permission-checkbox:checked')
                    .length;
                $('#select_all_module').prop('checked', totalVisiblePermissions > 0 && totalVisiblePermissions ===
                    totalVisibleChecked);
            }

            // Call sync function when any permission checkbox is changed
            $(document).on('change', '.permission-checkbox, .check-all-row', syncSelectAllModule);
        });
    </script>

    {{-- ═══ Face Camera Capture Modal ═══ --}}
    <div class="modal fade" id="faceCamModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-header" style="background:#1b2850;border-radius:16px 16px 0 0;">
                    <h5 class="modal-title text-white"><i class="fas fa-camera me-2"></i>Capture Face Photo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        onclick="stopFaceCam()"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div style="position:relative;width:280px;height:280px;margin:0 auto;border-radius:50%;overflow:hidden;border:4px solid #ff9f43;box-shadow:0 6px 20px rgba(0,0,0,0.15);">
                        <video id="face-cam-video" autoplay muted playsinline
                            style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1);"></video>
                    </div>
                    <p class="mt-3 mb-0" style="color:#475569;font-size:13px;">Position your face clearly in the circle, then click <strong>Capture</strong>.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pb-4">
                    <button type="button" class="btn btn-success px-4" id="faceCamCaptureBtn"
                        style="border-radius:8px;font-weight:600;">
                        <i class="fas fa-camera me-1"></i> Capture
                    </button>
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"
                        style="border-radius:8px;font-weight:600;" onclick="stopFaceCam()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let _faceCamStream = null;
        window._faceCaptureBlob = null;

        document.getElementById('openFaceCamBtn').addEventListener('click', function () {
            startFaceCam();
            var modal = new bootstrap.Modal(document.getElementById('faceCamModal'));
            modal.show();
        });

        function startFaceCam() {
            const video = document.getElementById('face-cam-video');
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
            })
            .then(function (stream) {
                _faceCamStream = stream;
                video.srcObject = stream;
            })
            .catch(function (err) {
                Swal.fire('Camera Error', 'Could not access camera: ' + err.message, 'error');
            });
        }

        function stopFaceCam() {
            if (_faceCamStream) {
                _faceCamStream.getTracks().forEach(t => t.stop());
                _faceCamStream = null;
            }
        }

        document.getElementById('faceCamCaptureBtn').addEventListener('click', function () {
            const btn    = this;
            const video  = document.getElementById('face-cam-video');
            const canvas = document.getElementById('face-capture-canvas');

            // ── Show loader immediately so user knows click registered ──
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

            canvas.width  = video.videoWidth  || 640;
            canvas.height = video.videoHeight || 480;

            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(async function (blob) {
                // ── Validate face at 60% before accepting ───────────────────
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Validating...';
                if (typeof fuvShowBadge === 'function') fuvShowBadge('face-detection-badge', 0, 'loading');

                let valid = true;
                if (typeof fuvValidate === 'function') {
                    valid = await fuvValidate(blob, 'face-detection-badge');
                }

                if (!valid) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-camera me-1"></i> Capture';
                    const errDiv = document.querySelector('.error-face_photo');
                    if (errDiv) errDiv.innerHTML = '❌ Face not clearly detected (minimum 60% required). Please reposition and try again.';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Face Not Detected',
                        text: 'Minimum 60% face confidence required. Look directly at the camera in good lighting.',
                        confirmButtonColor: '#ff9f43'
                    });
                    return;
                }

                // ── Face passed ──────────────────────────────────────────────
                window._faceCaptureBlob = blob;

                const url = URL.createObjectURL(blob);
                document.getElementById('face-photo-preview').src = url;
                document.getElementById('face-photo-preview-container').style.display = 'block';
                document.getElementById('face-capture-label').textContent = '✅ Face captured & validated';
                const errDiv2 = document.querySelector('.error-face_photo');
                if (errDiv2) errDiv2.innerHTML = '';

                document.getElementById('face-photo-input').value = '';

                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-camera me-1"></i> Capture';

                stopFaceCam();
                bootstrap.Modal.getInstance(document.getElementById('faceCamModal')).hide();

                Swal.fire({ title: 'Captured!', text: 'Face photo validated ✅ Click Update to save.', icon: 'success', timer: 1800, showConfirmButton: false });
            }, 'image/jpeg', 0.92);
        });
    </script>
    {{-- face-api.js + upload validator --}}
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="{{ asset('admin/assets/js/face-upload-validator.js') }}?v={{ time() }}"></script>
@endpush
