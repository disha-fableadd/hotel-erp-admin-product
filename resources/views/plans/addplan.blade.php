@extends('layout.app')

@section('title', 'Add Plan')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Add Plan</h4>
        </div>
        <div class="back-button">
            <a href="{{ route('plans.planlist') }}" class="btn btn-primary back-button">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <form id="planForm">
        <div class="card border-0 shadow-sm">
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
                            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0">
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
                        <input type="number" name="storage_limit" id="storage_limit" class="form-control" min="0">
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
            <a href="javascript:void(0);" class="btn btn-submit me-2 submit">Save Plan</a>
            <a href="{{ route('plans.planlist') }}" class="btn btn-cancel">Cancel</a>
            <br>
            <span class="success_submit text-danger"></span>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
    // Feature rows functionality removed as features is now a text area

    // ─── Submit via Ajax ─────────────────────────────────────────────────────
    $(document).ready(function () {
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

    if (start && end) {

        let startDate = new Date(start);
        let endDate = new Date(end);

        if (endDate < startDate) {
            $('#duration').val('');
            $('.error_end_date').text('End date must be greater than start date.');
            return;
        }

        $('.error_end_date').text('');

        let years = endDate.getFullYear() - startDate.getFullYear();
        let months = endDate.getMonth() - startDate.getMonth();
        let days = endDate.getDate() - startDate.getDate();

        if (days < 0) {
            months--;
            const prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0);
            days += prevMonth.getDate();
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
}

$('#start_date, #end_date, #price, #discount_percent').on('change keyup input', calculateDuration);
        $(document).on('click', '.submit', function (e) {
            e.preventDefault();

            var $btn         = $(this);
            var originalText = $btn.html();
            var authToken    = localStorage.getItem('authToken');

            // disable & show spinner
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop('disabled', true);

            // collect form data
            var formData = new FormData($('#planForm')[0]);

            // attach sub_branch_id from localStorage
            var subBranchId = localStorage.getItem('selectedSubAdminId');
            if (subBranchId) {
                formData.append('sub_branch_id', subBranchId);
            }

            // clear all previous errors
            $('.error_name, .error_price, .error_discount_percent, .error_final_price, .error_total_amount, .error_duration, .error_is_active, ' +
              '.error_user_limit, .error_branch_limit, .error_storage_limit, ' +
              '.error_subtitle, .error_features, .error_renewal_price').text('');

            $.ajax({
                url: '/api/createPlan',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': 'Bearer ' + authToken,
                },
                success: function (response) {
                    $btn.html(originalText).prop('disabled', false);

                    if (response.status) {
                        Swal.fire({
                            title: 'Success',
                            text: 'Plan created successfully',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ff9f43'
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                window.location.href = '{{ route('plans.planlist') }}';
                            }
                        });
                    }
                },
                error: function (xhr) {
                    $btn.html(originalText).prop('disabled', false);

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;

                        // clear first
                        $('.error_name, .error_price, .error_discount_percent, .error_final_price, .error_total_amount, .error_duration, .error_is_active, ' +
                          '.error_user_limit, .error_branch_limit, .error_storage_limit, ' +
                          '.error_subtitle, .error_features, .error_renewal_price').text('');

                        $.each(errors, function (key, value) {
                            // features.0, features.1 … → map to .error_features
                            var errorKey = key.split('.')[0];
                            var errorMsg = Array.isArray(value) ? value.join(' ') : value;
                            $('.error_' + errorKey).text(errorMsg);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON && xhr.responseJSON.message
                                    ? xhr.responseJSON.message
                                    : 'Something went wrong. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                }
            });
        });

        // ─── Branch Limit dropdown → hidden input ────────────────────────────
        $('#branch_limit_select').on('change', function () {
            var val = $(this).val();
            // 'unlimited' option → send empty string so backend stores null
            $('#branch_limit').val(val === 'unlimited' ? '' : val);
        });

        // prevent negative price
        $(document).on('input', '#price', function () {
            var value = parseFloat($(this).val());
            if (!isNaN(value) && value < 0) {
                $(this).val('');
                $('.error_price').text('Price cannot be negative.');
            } else {
                $('.error_price').text('');
            }
        });

        // validate discount percent
        $(document).on('input keyup', '#discount_percent', function () {
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
