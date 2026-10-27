@extends('layout.app')

@section('title', 'Change Password')

@section('content')
    <style>
        .field-error {
            font-size: 13px;
            margin-top: 6px;
            line-height: 1.3;
            color: #dc3545;
        }

        .field-error:empty {
            display: none !important;
        }

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .strength-weak {
            color: #dc3545;
        }

        .strength-medium {
            color: #ffc107;
        }

        .strength-strong {
            color: #28a745;
        }

        .password-requirements {
            margin-top: 12px;
            padding: 12px;
            background-color: #ffff;
            border: 1px solid #575454ff;
            border-radius: 4px;
            font-size: 12px;
            color: #000000ff;
        }

        .requirement {
            margin: 6px 0;
            display: flex;
            align-items: center;
        }

        .requirement-icon {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 10px;
            font-weight: bold;
        }

        .requirement-icon.met {
            background-color: #28a745;
            color: white;
        }

        .requirement-icon.unmet {
            background-color: #e9ecef;
            color: #6c757d;
        }

        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .password-input-wrapper {
            position: relative;
        }

        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 15px !important;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Change Password</h4>
                <h6>Update your account password</h6>
            </div>
            <a href="{{ route('auth.dashboard') }}" class="btn" style="background: #1b2850; color: #fff;">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <form id="changePasswordForm" autocomplete="off" novalidate>
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 col-sm-12">
                            <!-- Current Password -->
                            <div class="form-group">
                                <label for="current_password">Current Password <span class="text-danger">*</span></label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="current_password" name="current_password"
                                        class="form-control" placeholder="Enter your current password">
                                    <i class="fa fa-eye toggle-password" data-target="current_password"></i>
                                </div>
                                <div class="invalid-feedback d-block field-error" data-field="current_password"></div>
                            </div>

                            <!-- New Password -->
                            <div class="form-group">
                                <label for="new_password">New Password <span class="text-danger">*</span></label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="new_password" name="new_password"
                                        class="form-control" placeholder="Enter new password">
                                    <i class="fa fa-eye toggle-password" data-target="new_password"></i>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                                <div class="password-requirements">
                                    <div class="requirement">
                                        <span class="requirement-icon unmet" id="req-length">✓</span>
                                        <span>At least 8 characters</span>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block field-error" data-field="new_password"></div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password"
                                        class="form-control" placeholder="Confirm new password">
                                    <i class="fa fa-eye toggle-password" data-target="confirm_password"></i>
                                </div>
                                <div id="passwordMatch" style="margin-top: 6px; font-size: 12px;"></div>
                                <div class="invalid-feedback d-block field-error" data-field="confirm_password"></div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-lock me-2"></i> Change Password
                                </button>
                                <a href="{{ route('auth.dashboard') }}" class="btn btn-secondary ms-2">
                                    Cancel
                                </a>
                                <div class="invalid-feedback d-block field-error" data-field="form"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('changePasswordForm');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');

        // Password strength checker
        function checkPasswordStrength(password) {
            const requirements = {
                length: password.length >= 8
            };

            // Update requirement indicator
            updateRequirement('length', requirements.length);

            // Calculate strength
            const strengthElement = document.getElementById('passwordStrength');

            if (password.length === 0) {
                strengthElement.textContent = '';
            } else if (requirements.length) {
                strengthElement.textContent = 'Valid';
                strengthElement.className = 'password-strength strength-strong';
            } else {
                strengthElement.textContent = 'Too short';
                strengthElement.className = 'password-strength strength-weak';
            }

            return requirements;
        }

        function updateRequirement(id, met) {
            const element = document.getElementById(`req-${id}`);
            if (met) {
                element.classList.remove('unmet');
                element.classList.add('met');
            } else {
                element.classList.remove('met');
                element.classList.add('unmet');
            }
        }

        // Check password match
        function checkPasswordMatch() {
            const matchElement = document.getElementById('passwordMatch');
            if (confirmPasswordInput.value === '') {
                matchElement.textContent = '';
                matchElement.className = '';
            } else if (newPasswordInput.value === confirmPasswordInput.value) {
                matchElement.textContent = '✓ Passwords match';
                matchElement.className = 'text-success';
            } else {
                matchElement.textContent = '✗ Passwords do not match';
                matchElement.className = 'text-danger';
            }
        }

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
        });

        // Event listeners
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });

        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            if (!data.current_password) {
                showError('current_password', 'Current password is required');
                return;
            }

            if (!data.new_password) {
                showError('new_password', 'New password is required');
                return;
            }

            if (!data.confirm_password) {
                showError('confirm_password', 'Confirm password is required');
                return;
            }

            // Validate password strength
            const requirements = checkPasswordStrength(data.new_password);
            if (!requirements.length) {
                showError('new_password', 'Password must be at least 8 characters');
                return;
            }

            // Validate password match
            if (data.new_password !== data.confirm_password) {
                showError('confirm_password', 'Passwords do not match');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Changing...';

            try {
                const authToken = localStorage.getItem('authToken');
                const response = await fetch('{{ route("change-password") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Authorization': authToken ? `Bearer ${authToken}` : ''
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.status) {
                    await showSuccess('Password changed successfully!');
                    window.location.href = '{{ route("auth.dashboard") }}';
                } else {
                    if (result.errors) {
                        Object.keys(result.errors).forEach(field => {
                            showError(field, result.errors[field][0]);
                        });
                    } else {
                        showError('form', result.message || 'An error occurred');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showError('form', 'An error occurred. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i> Change Password';
            }
        });

        function showError(field, message) {
            const errorElement = document.querySelector(`[data-field="${field}"]`);
            if (errorElement) {
                errorElement.textContent = message;
            } else {
                showAlert('Error', message, 'error');
            }
        }

        function showSuccess(message) {
            return showAlert('Success', message, 'success');
        }

        function showAlert(title, message, icon) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({
                    title: title,
                    text: message,
                    icon: icon,
                    confirmButtonText: 'OK'
                });
            }

            alert(message);
            return Promise.resolve();
        }
    </script>
@endsection
