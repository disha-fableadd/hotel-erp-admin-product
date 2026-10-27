@extends('layout.app')

@section('title', 'Edit Plan')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Plan</h4>
            </div>
            <div class="back-button">
                <a href="{{ route('plans.planlist') }}" class="btn btn-primary back-button">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <form id="planForm">
            <input type="hidden" name="plan_id" id="plan_id">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Plan Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control">
                            <span class="error_name text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="price" class="form-control" step="0.01"
                                min="0">
                            <span class="error_price text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Discount (%)</label>
                            <input type="number" name="discount_percent" id="discount_percent" class="form-control" step="0.01" min="0" max="100" placeholder="0">
                            <span class="error_discount_percent text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Final Price</label>
                            <input type="number" name="final_price" id="final_price" class="form-control" step="0.01" min="0" readonly>
                            <span class="error_final_price text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" name="total_amount" id="total_amount" class="form-control" step="0.01" min="0" readonly>
                            <input type="hidden" name="total_price" id="total_price">
                            <span class="error_total_amount text-danger"></span>
                        </div>
                                                
                        <div class="col-md-3">
                            <label class="form-label">Renewal Price</label>
                            <input type="number" name="renewal_price" id="renewal_price" class="form-control" step="0.01" min="0">
                            <span class="error_renewal_price text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                            <span class="error_start_date text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                            <span class="error_end_date text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Duration</label>
                            <input type="text" name="duration" id="duration" class="form-control" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="is_active" id="is_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <span class="error_is_active text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">User Limit</label>
                            <input type="number" name="user_limit" id="user_limit" class="form-control" min="0">
                            <span class="error_user_limit text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Branch Limit</label>
                            <select id="branch_limit_select" class="form-select">
                                <option value="">-- Select --</option>
                                <option value="1">Starter</option>
                                <option value="3">Professional</option>
                                <option value="unlimited">Unlimited</option>
                            </select>
                            <input type="hidden" name="branch_limit" id="branch_limit">
                            <span class="error_branch_limit text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Storage Limit</label>
                            <input type="number" name="storage_limit" id="storage_limit" class="form-control"
                                min="0">
                            <span class="error_storage_limit text-danger"></span>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Features</label>
                            <textarea name="features" id="features" class="form-control" rows="3" placeholder="Enter features separated by new lines or commas"></textarea>
                            <span class="error_features text-danger"></span>
                        </div>

                        <div class="col-3">
                            <label class="form-label">Remark</label>
                            <textarea name="subtitle" id="subtitle" class="form-control" rows="3"></textarea>
                            <span class="error_subtitle text-danger"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <a href="javascript:void(0);" class="btn btn-submit me-2 submit">Update Plan</a>
                <a href="{{ route('plans.planlist') }}" class="btn btn-cancel">Cancel</a>
                <br>
                <span class="success_submit text-danger"></span>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script>
        // Feature dynamic rows removed, features is now a text area

        // ─── Main logic ──────────────────────────────────────────────────────────────
        $(document).ready(function() {
function calculateDuration() {
    let price = parseFloat($('#price').val()) || 0;
    let discountPercent = parseFloat($('#discount_percent').val()) || 0;
    if (discountPercent < 0) discountPercent = 0;
    if (discountPercent > 100) discountPercent = 100;

    let finalPrice = price - (price * (discountPercent / 100));
    if (finalPrice < 0) finalPrice = 0;

    if (price > 0) {
        $('#final_price').val(finalPrice.toFixed(2));
    } else {
        $('#final_price').val('');
    }

    let start = $('#start_date').val();
    let end = $('#end_date').val();

    if (!start || !end) return;

    let startDate = new Date(start);
    let endDate = new Date(end);

    if (endDate < startDate) {
        $('.error_end_date').text('End date must be after Start date.');
        $('#duration').val('');
        return;
    }

    $('.error_end_date').text('');

    let years = endDate.getFullYear() - startDate.getFullYear();
    let months = endDate.getMonth() - startDate.getMonth();
    let days = endDate.getDate() - startDate.getDate();

    if (days < 0) {
        months--;
        let lastMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0);
        days += lastMonth.getDate();
    }

    if (months < 0) {
        years--;
        months += 12;
    }

    let duration = '';

    if (years > 0) duration += years + ' Year ';
    if (months > 0) duration += months + ' Month ';
    if (days > 0) duration += days + ' Day';

    $('#duration').val(duration.trim());
    
    let totalMonths = (years * 12) + months + (days / 30);
    let payableRate = finalPrice > 0 ? finalPrice : price;
    let totalAmount = payableRate * totalMonths;
    let totalVal = totalAmount > 0 ? Math.round(totalAmount).toFixed(2) : '';
    $('#total_amount').val(totalVal);
    $('#total_price').val(totalVal);
}

$('#start_date, #end_date, #price, #discount_percent').on('change keyup input', calculateDuration);
            var authToken = localStorage.getItem('authToken');

            // Extract plan ID from the URL  e.g. /plans/edit/5 → 5
            var planId = window.location.pathname.split('/').pop();

            // ── Load plan data and pre-fill form ─────────────────────────────────────
            $.ajax({
                url: '/api/getPlanById/' + planId,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + authToken
                },
                success: function(response) {
                    if (!response.status) {
                        Swal.fire('Error', 'Plan not found.', 'error').then(function() {
                            window.location.href = '{{ route('plans.planlist') }}';
                        });
                        return;
                    }

                    var plan = response.plan;

                    $('#plan_id').val(plan.id);
                    $('#name').val(plan.name);
                    $('#price').val(plan.price);
                    $('#renewal_price').val(plan.renewal_price !== null && plan.renewal_price !== undefined ? plan.renewal_price : '');
                    $('#discount_percent').val(plan.discount_percent !== null && plan.discount_percent !== undefined ? plan.discount_percent : '');
                    $('#final_price').val(plan.final_price !== null && plan.final_price !== undefined ? plan.final_price : '');
                    var tot = plan.total_amount || plan.total_price || '';
                    $('#total_amount').val(tot);
                    $('#total_price').val(tot);
                    $('#start_date').val(plan.start_date ? plan.start_date.substring(0, 10) : '');
                    $('#end_date').val(plan.end_date ? plan.end_date.substring(0, 10) : '');
                    $('#duration').val(plan.duration);
                    $('#is_active').val(plan.is_active ? '1' : '0');
                    $('#user_limit').val(plan.user_limit);
                    // Populate branch_limit dropdown
                    var bl = plan.branch_limit;
                    if (bl === null || bl === '' || bl === undefined) {
                        $('#branch_limit_select').val('unlimited');
                        $('#branch_limit').val('');
                    } else {
                        $('#branch_limit_select').val(String(bl));
                        $('#branch_limit').val(bl);
                    }
                    $('#storage_limit').val(plan.storage_limit);
                    $('#subtitle').val(plan.subtitle);

                    // Populate features
                    if (plan.features && Array.isArray(plan.features)) {
                        // Features is an array, join by newline
                        $('#features').val(plan.features.join('\n'));
                    } else if (plan.features) {
                        $('#features').val(plan.features);
                    }
                    
                    // Auto-calculate the total amount using the fetched dates and price
                    calculateDuration();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire('Error', 'Failed to load plan data.', 'error');
                }
            });

            // ── Submit ────────────────────────────────────────────────────────────────
            $(document).on('click', '.submit', function(e) {
                e.preventDefault();

                var $btn = $(this);
                var originalText = $btn.html();

                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                        )
                    .prop('disabled', true);

                $('.error_name, .error_price, .error_discount_percent, .error_final_price, .error_total_amount, .error_start_date, .error_end_date,' +
                    '.error_duration, .error_is_active, .error_user_limit, ' +
                    '.error_branch_limit, .error_storage_limit,.error_subtitle, .error_features, .error_renewal_price').text('');

                var formData = new FormData($('#planForm')[0]);

                // attach sub_branch_id from localStorage
                var subBranchId = localStorage.getItem('selectedSubAdminId');
                if (subBranchId) {
                    formData.append('sub_branch_id', subBranchId);
                }

                $.ajax({
                    url: '/api/updatePlan',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'Authorization': 'Bearer ' + authToken
                    },
                    success: function(response) {
                        $btn.html(originalText).prop('disabled', false);

                        if (response.status) {
                            Swal.fire({
                                title: 'Success',
                                text: 'Plan updated successfully',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#ff9f43'
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    window.location.href =
                                        '{{ route('plans.planlist') }}';
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        $btn.html(originalText).prop('disabled', false);

                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;

                            $.each(errors, function(key, value) {
                                // features.0, features.1 … → .error_features
                                var errorKey = key.split('.')[0];
                                var errorMsg = Array.isArray(value) ? value.join(' ') :
                                    value;
                                $('.error_' + errorKey).text(errorMsg);
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: xhr.responseJSON && xhr.responseJSON.message ?
                                    xhr.responseJSON.message :
                                    'Something went wrong. Please try again.',
                                icon: 'error',
                                confirmButtonColor: '#ff9f43'
                            });
                        }
                    }
                });
            });

            // ── Branch Limit dropdown → hidden input ─────────────────────────────────
            $('#branch_limit_select').on('change', function() {
                var val = $(this).val();
                $('#branch_limit').val(val === 'unlimited' ? '' : val);
            });

            // ── Prevent negative price ────────────────────────────────────────────────
            $(document).on('input', '#price', function() {
                var value = parseFloat($(this).val());
                if (!isNaN(value) && value < 0) {
                    $(this).val('');
                    $('.error_price').text('Price cannot be negative.');
                } else {
                    $('.error_price').text('');
                }
            });

            // ── Validate discount percent ─────────────────────────────────────────────
            $(document).on('input keyup', '#discount_percent', function() {
                var value = parseFloat($(this).val());
                if (!isNaN(value) && (value < 0 || value > 100)) {
                    $('.error_discount_percent').text('Discount % must be between 0 and 100.');
                } else {
                    $('.error_discount_percent').text('');
                }
            });

        });
    </script>
@endpush
