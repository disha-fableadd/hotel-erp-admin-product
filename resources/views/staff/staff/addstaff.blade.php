@extends('layout.app')

@section('title', 'Add Staff')

@section('content')
@php
    $adminPrefix = \App\Http\Controllers\api\StaffController::getAdminPrefix(auth()->user());
@endphp
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
            box-shadow: 0 8px 20px rgba(255, 159, 67, 0.24)
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

        .gst-input-wrap {
            position: relative;
        }

        .gst-loader {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
            color: #ff9f43;
            pointer-events: none;
        }

        #gst_number.loading {
            padding-right: 36px;
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
            <h4>Add Staff</h4>

            </div>
        </div>
    </div> --}}
        <div class="page-header ">
            <div class="page-title">
                <h4>Add Staff</h4>
            </div>
            <div class="back-button">
                <a href="{{ route('staff.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i>
                    Back</a></br>
                <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body staff-form-card">
                <form id="customerForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h5 class="staff-step-title mb-4">Staff Details</h5>
                    <div class="row g-3">
                        <!-- Customer Name -->
                        <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                            <div class="form-group">
                                {{-- <label>Staff Name</label> --}}
                                <label>Staff Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" id="customer_name" maxlength="80"
                                    class="form-control">
                                <div class="text-danger error-customer_name"></div>
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                            <div class="form-group">
                                <label>Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    @if(!empty($adminPrefix))
                                        <span class="input-group-text bg-light text-muted fw-bold" id="admin_prefix_addon">{{ $adminPrefix }}_</span>
                                    @endif
                                    <input type="text" name="username" id="username" maxlength="60"
                                        class="form-control" placeholder="e.g. staff1" autocomplete="off"
                                        style="text-transform: lowercase;">
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Will be prefixed with brand prefix for login.</small>
                                <div class="text-danger error-username"></div>
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
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" name="password" id="password" class="form-control"
                                        autocomplete="off">
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
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)"
                                    class="form-control">
                                <div class="text-danger error-phone"></div>
                            </div>
                        </div>

                        <!-- Country -->
                        <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" name="country" id="country" class="form-control">
                                <div class="text-danger error-country"></div>
                            </div>
                        </div>

                        <!-- City -->
                        <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" id="city" class="form-control">
                                <div class="text-danger error-city"></div>
                            </div>
                        </div>

                         <!-- Role -->
                        <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                            <div class="form-group">
                                <label>Role <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-control select2 role-select">
                                    <option value="">Select Role</option>
                                    <option value="staff">Staff</option>
                                    <option value="hr">HR</option>
                                </select>
                                <div class="text-danger error-role"></div>
                            </div>
                        </div>

                        <!-- Department -->
                        <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" id="department_id" class="form-control select2 department-select">
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-department_id"></div>
                            </div>
                        </div>

                        <!-- Designation -->
                        <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                            <div class="form-group">
                                <label>Designation</label>
                                <select name="designation_id" id="designation_id" class="form-control select2 designation-select" disabled>
                                    <option value="">Select Designation</option>
                                </select>
                                <div class="text-danger error-designation_id"></div>
                            </div>
                        </div>


                        <!-- Joining Date -->
                        <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                            <div class="form-group">
                                <label>Joining Date <span class="text-danger">*</span></label>
                                <input type="date" name="joining_date" id="joining_date" class="form-control">
                                <div class="text-danger error-joining_date"></div>
                            </div>
                        </div>


                        <!-- Salary -->
                        <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                            <div class="form-group">
                                <label>Salary <span class="text-danger">*</span></label>
                                <input type="number" name="salary" id="salary" class="form-control" min="0"
                                    step="0.01">
                                <div class="text-danger error-salary"></div>
                            </div>
                        </div>

                        <!-- PAN Number -->
                        <!-- <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                            <div class="form-group">
                                <label>PAN Number</label>
                                <input type="text" name="pan_number" id="pan_number" class="form-control">
                                <span class="text-danger error-pan_number"></span>
                            </div>
                        </div> -->

                        <!-- GST Number -->
                        <!-- <div class="col-lg-4 col-md-6 col-12 staff-basic-step">
                            <div class="form-group">
                                <label>GST Number</label>
                                <div class="gst-input-wrap">
                                    <input type="text" name="gst_number" id="gst_number" class="form-control">
                                    <span id="gst-loader" class="gst-loader">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </div>
                                <span class="text-danger error-gst_number"></span>
                            </div>
                        </div> -->

                        <!-- Address -->
                        <div class="col-lg-4 col-12 staff-basic-step">
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" id="address" class="form-control"></textarea>
                                <div class="text-danger error-address"></div>
                            </div>
                        </div>

                        <!-- Avatar Upload -->
                        <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                            <div class="form-group">
                                <label>Photo</label>
                                <div class="image-upload">
                                    <input type="file" name="avatar" id="avatar" class="form-control"
                                        accept="image/*">
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
                                        style="max-width: 100px; border-radius: 8px;padding:4px;">
                                </div>
                            </div>
                        </div>

                        <!-- Face Photo Upload -->
                        <div class="col-lg-4 col-md-6 col-12 staff-job-step">
                            <div class="form-group">
                                <label>Face Photo <span class="text-muted" style="font-size:11px;font-weight:400;">(upload or use camera)</span></label>
                                <div class="image-upload">
                                    <input type="file" name="face_photo" id="face_photo" class="form-control" accept="image/*">
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
                                <div id="face-photo-preview-container" style="display:none;margin-top:10px;">
                                    <img id="face-photo-preview" src="" alt="Face Photo Preview"
                                        style="max-width:120px;border-radius:8px;border:2px solid #ff9f43;padding:3px;">
                                    <div style="font-size:11px;color:#28a745;margin-top:4px;" id="face-capture-label"></div>
                                </div>
                                <!-- Hidden canvas for camera capture -->
                                <canvas id="face-capture-canvas" style="display:none;"></canvas>
                            </div>
                        </div>

                        <!-- Submit & Cancel Buttons -->
                        <div class="col-lg-12 staff-step-actions mt-4">
                            <button type="submit" id="submitCustomerBtn" class="btn btn-submit me-2">Submit</button>
                            <a href="{{ route('staff.list') }}" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>
                    <div id="staffJobPermissionSection">
                        <hr>
                        <div class="text-danger error-permissions mt-2"></div>

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

                        <!-- Permissions Section -->
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
                                        @foreach ($modules as $module)
                                            <tr class="permission-module-row"
                                                data-module-name="{{ strtolower($module->module) }}">
                                                <td class="align-middle">
                                                    <i class="fa fa-folder-open text-primary me-2"></i>
                                                    {{ $module->module }}
                                                    <input type="hidden" name="modules[{{ $module->id }}][module_id]"
                                                        value="{{ $module->id }}">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="modules[{{ $module->id }}][view]" value="1"
                                                        class="permission-checkbox form-check-input">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="modules[{{ $module->id }}][add]" value="1"
                                                        class="permission-checkbox form-check-input">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="modules[{{ $module->id }}][edit]" value="1"
                                                        class="permission-checkbox form-check-input">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="modules[{{ $module->id }}][delete]" value="1"
                                                        class="permission-checkbox form-check-input">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="check-all-row form-check-input">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="staffSubmitLoader" class="staff-submit-loader">
            <div class="loader-box">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span>Please wait...</span>
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
        const designationData = @json($designations);

        $(document).ready(function() {

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

            const HR_PERMISSION_MODULES = ['attendance', 'leaves', 'payroll', 'staff'];

            function filterPermissionRowsByRole() {
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
                $('#select_all_module').prop('checked', $visiblePermissions.length > 0 && $visiblePermissions
                    .length === visibleChecked);
            }

            $('#department_id').on('change', function() {
                populateDesignationOptions($(this).val());
            });

            populateDesignationOptions($('#department_id').val());
            filterPermissionRowsByRole();
            $('#role').on('change', filterPermissionRowsByRole);



            $('#avatar').on('change', function(e) {
                const file = e.target.files[0];
                const $previewContainer = $('#avatar-preview-container');
                const $previewImage = $('#avatar-preview');
                const $errorDiv = $('.error-avatar');

                // Clear previous errors
                $errorDiv.html('');

                if (!file) {
                    $previewContainer.hide();
                    $previewImage.attr('src', '');
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    $errorDiv.html('Only image files (JPEG, PNG, JPG, WEBP, GIF) are allowed.');
                    $(this).val(''); // Clear the input
                    $previewContainer.hide();
                    $previewImage.attr('src', '');
                    return;
                }

                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    $errorDiv.html('Image must be less than 2MB.');
                    $(this).val('');
                    $previewContainer.hide();
                    $previewImage.attr('src', '');
                    return;
                }

                // Show preview using FileReader
                const reader = new FileReader();
                reader.onload = function(event) {
                    $previewImage.attr('src', event.target.result);
                    $previewContainer.fadeIn(); // or .show() if you prefer
                };
                reader.onerror = function() {
                    $errorDiv.html('Error reading file.');
                    $previewContainer.hide();
                };
                reader.readAsDataURL(file);
            });

            // Face photo file input → validate 60% min confidence before allowing save
            let _facePhotoValid = true;  // no photo = optional, no block

            $('#face_photo').on('change', function(e) {
                const file = e.target.files[0];
                const $previewContainer = $('#face-photo-preview-container');
                const $previewImage = $('#face-photo-preview');
                const $errorDiv = $('.error-face_photo');

                $errorDiv.html('');
                window._faceCaptureBlob = null;  // clear camera blob if file re-selected

                if (!file) {
                    $previewContainer.hide();
                    $previewImage.attr('src', '');
                    _facePhotoValid = true;
                    if (typeof fuvShowBadge === 'function') {
                        fuvShowBadge('face-detection-badge', 0, 'hidden');
                    }
                    return;
                }

                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    $errorDiv.html('Only image files (JPEG, PNG, JPG, WEBP, GIF) are allowed.');
                    $(this).val('');
                    $previewContainer.hide();
                    _facePhotoValid = false;
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    $errorDiv.html('Image must be less than 2MB.');
                    $(this).val('');
                    $previewContainer.hide();
                    _facePhotoValid = false;
                    return;
                }

                _facePhotoValid = false;  // block save until detection passes

                const reader = new FileReader();
                reader.onload = function(event) {
                    $previewImage.attr('src', event.target.result);
                    $previewContainer.fadeIn();
                    $('#face-capture-label').text('');
                };
                reader.readAsDataURL(file);

                // ── Step 1: Face detection (60% minimum) ──
                if (typeof fuvValidate === 'function') {
                    fuvValidate(file, 'face-detection-badge').then(async valid => {
                        if (!valid) {
                            _facePhotoValid = false;
                            $errorDiv.html('❌ Face not clearly detected (minimum 60% required). Please upload a clearer front-facing photo or use the camera.');
                            return;
                        }

                        // ── Step 2: Duplicate face check ──
                        $errorDiv.html('<span style="color:#1b2850;"><i class="fas fa-spinner fa-spin"></i> Checking for duplicate face...</span>');
                        if (typeof fuvCheckDuplicate === 'function') {
                            const dupResult = await fuvCheckDuplicate(file, null);

                            // ⚠ Check failed entirely (couldn't load existing faces, API down, etc)
                            if (dupResult.error) {
                                _facePhotoValid = false;
                                $errorDiv.html(
                                    '⚠️ Could not verify face uniqueness: ' + dupResult.error
                                    + '. Please try again or contact admin.'
                                );
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Face Check Failed',
                                    text: 'Could not verify this face against existing records. Please try again.',
                                    confirmButtonColor: '#ff9f43'
                                });
                                return;
                            }

                            if (dupResult.isDuplicate) {
                                _facePhotoValid = false;
                                $errorDiv.html(
                                    '⚠️ This face is already registered as <strong>' + dupResult.matchName + '</strong> '
                                    + '(ID: ' + dupResult.matchId + ', similarity: ' + dupResult.similarity + '%). '
                                    + 'Duplicate faces are not allowed.'
                                );
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Duplicate Face Detected',
                                    html: 'This face photo matches an existing staff member: <strong>' + dupResult.matchName + '</strong>.<br>'
                                        + 'Confidence: <strong>' + dupResult.confidence + '</strong> (' + dupResult.similarity + '% similar).<br><br>'
                                        + 'Please use a different photo.',
                                    confirmButtonColor: '#ff9f43'
                                });
                                return;
                            }
                        }

                        _facePhotoValid = true;
                        $errorDiv.html('');
                    });
                } else {
                    _facePhotoValid = true;
                    if (typeof fuvShowBadge === 'function') {
                        fuvShowBadge('face-detection-badge', 0, 'hidden');
                    }
                }
            });

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
            $(".role-select, .department-select, .working-location, .designation-select")
                .select2({
                    tags: true,
                });
            let $gstLoader = $('#gst-loader');

            const $loader = $("#staffSubmitLoader");

            function showSubmitLoader() {
                $loader.css("display", "flex");
            }

            function hideSubmitLoader() {
                $loader.hide();
            }

            $("#customerForm").submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $(".text-danger").html("");
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
                } else if (customer_name.length > 80) {
                    $(".error-customer_name").html(" Staff name must not exceed 80 characters. ");
                    hasError = true;
                }

                // ✅ Username validation
                let username = $("#username").val().trim();
                if (username === "") {
                    $(".error-username").html(" Username is required. ");
                    hasError = true;
                } else if (username.length < 3) {
                    $(".error-username").html(" Username must be at least 3 characters. ");
                    hasError = true;
                }

                // ✅ Role validation
                let role = $("#role").val();
                if (!role) {
                    $(".error-role").html(" Role is required. ");
                    hasError = true;
                }

                // ✅ Password validation
                let password = $("#password").val().trim();
                if (password === "") {
                    $(".error-password").html(" Password is required. ");
                    hasError = true;
                } else if (password.length < 8) {
                    $(".error-password").html(" Password must be at least 8 characters. ");
                    hasError = true;
                }

                // ✅ Phone validation
                let phone = $("#phone").val().trim();
                let phonePattern = /^[0-9]{10}$/; // exactly 10 digits
                if (phone === "") {
                    $(".error-phone").html(" Phone number is required. ");
                    hasError = true;
                } else if (!phonePattern.test(phone)) {
                    $(".error-phone").html(" Please enter a valid 10-digit phone number. ");
                    hasError = true;
                }

                // ✅ Joining Date validation
                let joining_date = $("#joining_date").val();
                if (!joining_date) {
                    $(".error-joining_date").html(" Joining date is required. ");
                    hasError = true;
                }

                // ✅ Salary validation
                let salary = $("#salary").val().trim();
                if (salary === "") {
                    $(".error-salary").html(" Salary is required. ");
                    hasError = true;
                } else if (isNaN(salary) || Number(salary) < 0) {
                    $(".error-salary").html(" Salary must be a valid non-negative number. ");
                    hasError = true;
                }

                // ✅ File upload validation
                let avatar = $("#avatar")[0].files[0];
                if (avatar) {
                    let allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                    if (!allowedTypes.includes(avatar.type)) {
                        $(".error-avatar").html(
                            " Only image files (JPEG, PNG, JPG, WEBP, GIF) are allowed. ");
                        hasError = true;
                    } else if (avatar.size > 2 * 1024 * 1024) { // 2MB
                        $(".error-avatar").html(" Image size must not exceed 2MB. ");
                        hasError = true;
                    }
                }

                // Inject camera-captured face blob into formData if available
                if (window._faceCaptureBlob) {
                    formData.delete('face_photo');
                    formData.append('face_photo', window._faceCaptureBlob, 'face_capture.jpg');
                    window._faceCaptureBlob = null; // ✅ Clear immediately so it can't bleed into next submit
                    _facePhotoValid = true;          // blob was already validated before being stored
                }

                let facePhoto = $("#face_photo")[0].files[0];
                if (facePhoto) {
                    let allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                    if (!allowedTypes.includes(facePhoto.type)) {
                        $(".error-face_photo").html(" Only image files (JPEG, PNG, JPG, WEBP, GIF) are allowed. ");
                        hasError = true;
                    } else if (facePhoto.size > 2 * 1024 * 1024) {
                        $(".error-face_photo").html(" Image size must not exceed 2MB. ");
                        hasError = true;
                    }
                }

                // ── Face-photo: block save if did not pass 60% face detection ──
                if (typeof _facePhotoValid !== 'undefined' && !_facePhotoValid &&
                    ($("#face_photo")[0].files.length > 0 || window._faceCaptureBlob)) {
                    $(".error-face_photo").html('❌ Face not clearly detected (minimum 60% required). Please upload a clearer photo or recapture via camera.');
                    hasError = true;
                }

                // Stop form submission if errors exist
                if (hasError) {
                    return false;
                }

                const $btn = $("#submitCustomerBtn");
                const originalText = $btn.html();

                // Show spinner and disable button
                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                    )
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
                            // ✅ Clean up face capture state on success
                            window._faceCaptureBlob = null;
                            _facePhotoValid = true;
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

              $('#gst_number').on('input', function() {
                let $gstInput = $(this);
                let $errorDiv = $('.error-gst_number');

                var normalizedGst = $(this).val().toUpperCase().replace(/[^0-9A-Z]/g, '').substring(0, 15);
                $(this).val(normalizedGst);

                var gst = normalizedGst.trim();
                if (!/^[0-9A-Z]{15}$/.test(gst)) {
                    $gstLoader.hide();
                    $gstInput.removeClass('loading');
                    return;
                }

                $.ajax({
                    url: '/api/fetch-gst-details',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        gst_number: gst
                    },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        "Authorization": "Bearer " + authToken,
                    },
                    beforeSend: function() {
                        $gstLoader.show();
                        $gstInput.addClass('loading');
                        $gstInput.prop('readonly', true);
                        $errorDiv.html(
                            '<span style="color: #1B2850;"><i class="fas fa-spinner fa-spin"></i> Fetching details...</span>'
                        );
                    },
                    success: function(res) {
                        $errorDiv.html('');
                        if (res.error) {
                            $errorDiv.html(
                                '<span class="text-danger">GST details not found.</span>');
                            return;
                        }

                        // Populate fields based on API response
                        $('#customer_name').val(res.legal_name || '');
                        $('#address').val(res.primary_address || '');
                        $('#city').val(res.city || '');
                        // $('#state_code').val(res.state || '');
                        $('#country').val(res.country || '');

                        // Extract PAN from GST (chars 3 to 12)
                        if (gst.length >= 12) {
                            const pan = gst.substring(2, 12);
                            $('#pan_number').val(pan);
                        }
                    },
                    error: function(xhr, status, error) {
                        $errorDiv.html(
                            '<span class="text-danger">Failed to fetch GST details.</span>');
                        console.error('GST fetch failed', xhr, status, error);
                    },
                    complete: function() {
                        $gstLoader.hide();
                        $gstInput.removeClass('loading');
                        $gstInput.prop('readonly', false);
                    }
                });
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            // When 'Select All Module' is clicked
            $('#select_all_module').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('#module-permission-table tbody tr:visible .permission-checkbox, #module-permission-table tbody tr:visible .check-all-row')
                    .prop('checked', isChecked);
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
                const totalVisiblePermissions = $('#module-permission-table tbody tr:visible .permission-checkbox')
                    .length;
                const totalVisibleChecked = $('#module-permission-table tbody tr:visible .permission-checkbox:checked')
                    .length;
                $('#select_all_module').prop('checked', totalVisiblePermissions > 0 && totalVisiblePermissions ===
                    totalVisibleChecked);
            }

            // Call sync function when any permission checkbox is changed
            $('.permission-checkbox, .check-all-row').on('change', syncSelectAllModule);
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
                    <button type="button" class="btn btn-success px-4" id="faceCamCaptureBtn" style="border-radius:8px;font-weight:600;">
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
        let _facePhotoValid = true;  // declared here so submit handler can read it
        window._faceCaptureBlob = null;

        document.getElementById('openFaceCamBtn').addEventListener('click', function () {
            startFaceCam();
            var modal = new bootstrap.Modal(document.getElementById('faceCamModal'));
            modal.show();
        });

        function startFaceCam() {
            const video = document.getElementById('face-cam-video');
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } })
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
            canvas.width  = video.videoWidth  || 640;
            canvas.height = video.videoHeight || 480;

            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(async function (blob) {
                // ── Validate face before accepting ───────────────────────────
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Validating face...';
                if (typeof fuvShowBadge === 'function') {
                    fuvShowBadge('face-detection-badge', 0, 'loading');
                }

                let valid = true;
                if (typeof fuvValidate === 'function') {
                    valid = await fuvValidate(blob, 'face-detection-badge');
                }
                _facePhotoValid = valid;

                if (!valid) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-camera me-1"></i> Capture';
                    document.querySelector('.error-face_photo').innerHTML =
                        '❌ Face not clearly detected (minimum 60% required). Please reposition and try again.';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Face Not Detected',
                        text: 'The camera could not clearly detect your face (minimum 60% confidence required). Please face the camera directly with good lighting and try again.',
                        confirmButtonColor: '#ff9f43'
                    });
                    return;
                }

                // ── Face passed detection — now check for duplicates ────────
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Checking duplicate...';

                if (typeof fuvCheckDuplicate === 'function') {
                    const dupResult = await fuvCheckDuplicate(blob, null);

                    // ⚠ Check failed entirely
                    if (dupResult.error) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-camera me-1"></i> Capture';
                        document.querySelector('.error-face_photo').innerHTML =
                            '⚠️ Could not verify face uniqueness: ' + dupResult.error;
                        _facePhotoValid = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Face Check Failed',
                            text: 'Could not verify this face against existing records. Please try again.',
                            confirmButtonColor: '#ff9f43'
                        });
                        return;
                    }

                    if (dupResult.isDuplicate) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-camera me-1"></i> Capture';
                        document.querySelector('.error-face_photo').innerHTML =
                            '⚠️ This face matches <strong>' + dupResult.matchName + '</strong> '
                            + '(ID: ' + dupResult.matchId + ', ' + dupResult.similarity + '% similar). '
                            + 'Duplicate faces are not allowed.';
                        _facePhotoValid = false;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Duplicate Face Detected',
                            html: 'This face matches an existing staff member: <strong>' + dupResult.matchName + '</strong>.<br>'
                                + 'Similarity: <strong>' + dupResult.similarity + '%</strong> (confidence: ' + dupResult.confidence + ').<br><br>'
                                + 'Please capture a different person.',
                            confirmButtonColor: '#ff9f43'
                        });
                        return;
                    }
                }

                // ── All checks passed — save blob ─────────────────────────────
                _facePhotoValid = true;
                window._faceCaptureBlob = blob;

                const url = URL.createObjectURL(blob);
                document.getElementById('face-photo-preview').src = url;
                document.getElementById('face-photo-preview-container').style.display = 'block';
                document.getElementById('face-capture-label').textContent = '✅ Face captured & validated';
                document.querySelector('.error-face_photo').innerHTML = '';

                document.getElementById('face_photo').value = '';

                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-camera me-1"></i> Capture';

                stopFaceCam();
                bootstrap.Modal.getInstance(document.getElementById('faceCamModal')).hide();

                Swal.fire({ title: 'Captured!', text: 'Face photo validated & no duplicate found ✅', icon: 'success', timer: 1800, showConfirmButton: false });
            }, 'image/jpeg', 0.92);
        });
    </script>
    {{-- face-api.js + upload validator (loaded at end to not block page render) --}}
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="{{ asset('admin/assets/js/face-upload-validator.js') }}?v={{ time() }}"></script>
@endpush

