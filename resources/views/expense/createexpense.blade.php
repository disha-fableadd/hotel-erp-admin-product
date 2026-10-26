@extends('layout.app')

@section('title', 'Expense Add')

@section('content')
<style>
    @media screen and (max-width: 768px) {
        .form-group {
            margin-bottom: 10px !important
        }
        
        /* Mobile card layout */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 15px;
        }
        
        .row {
            display: block;
        }
        
        .col-lg-4,
        .col-lg-6,
        .col-lg-12,
        .col-sm-6,
        .col-sm-12 {
            width: 100% !important;
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            font-weight: 600;
            font-size: 13px;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .input-groupicon {
            position: relative;
        }
        
        .input-groupicon .addonset {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        
        .input-groupicon input {
            padding-right: 40px;
        }
        
        /* Button styling for mobile */
        .btn {
            width: 48%;
            padding: 10px 15px;
            margin: 5px 1%;
            font-size: 13px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }
        
        .btn-submit {
            background-color: #ff9f43;
            color: white;
        }
        
        .btn-cancel {
            background-color: #2d2e3f;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        /* Error message styling */
        .error {
            font-size: 12px;
            margin-top: 3px;
            display: block;
        }
    }
    
    /* Fix for datepicker calendar display */
    .card {
        overflow: visible !important;
    }
    
    .card-body {
        overflow: visible !important;
    }
    
    .row {
        overflow: visible !important;
    }
    
    .form-group {
        overflow: visible !important;
    }
    
    .input-groupicon {
        overflow: visible !important;
    }
    
    /* Ensure datepicker popup appears on top */
    .datetimepicker-popup,
    .ui-datepicker,
    .xdsoft_datetimepicker {
        z-index: 9999 !important;
    }
    
    /* Style readonly inputs to look disabled */
    input[readonly],
    textarea[readonly],
    select[readonly] {
        background-color: #f5f5f5 !important;
        color: #000 !important;
        pointer-events: none !important;
        cursor: not-allowed !important;
        opacity: 0.7 !important;
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Expense Add</h4>
            <!-- <h6>Add/Update Expenses</h6> -->
        </div>
    </div>
    <form id="expenseForm">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Sr No</label>
                            <div class="input-groupicon">
                                <input type="text" name="sr_no" id="sr_no" placeholder="sr_no" readonly>
                                <span class="text-danger error" id="sr_no_error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Name</label>
                            <div class="input-groupicon">
                                <input type="text" name="expense_name" placeholder="Expense Name">
                                <span class="text-danger error" id="expense_name_error"></span>

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Date </label>
                            <div class="input-groupicon">
                                <input type="text" name="expense_date" placeholder="Choose Date" class="datetimepicker">
                                <span class="text-danger error" id="expense_date_error"></span>
                                <div class="addonset">
                                    <img src="{{ env('ImagePath').'admin/assets/img/icons/calendars.svg' }}" alt="img">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Amount</label>
                            <div class="input-groupicon">
                                <input type="number" class="form-control" name="amount" placeholder="Enter Amount">
                                <span class="text-danger error" id="amount_error"></span>
                                <!-- <div class="addonset">
                                    <img src="admin/assets/img/icons/dollar.svg" alt="img">
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Type</label>
                            <select class="select" name="expense_type_id" id="expense_type_id">
                                <option value="">Select Expense Type</option>
                                @foreach($expenseTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->type }}</option>
                                @endforeach
                            </select>
                            <span class="error text-danger" id="expense_type_id_error"></span>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Payment Mode</label>
                            <select class="select" name="payment_mode" id="payment_mode">
                                <option value="">Select Payment Mode</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank">Bank</option>
                            </select>
                            <span class="error text-danger" id="payment_mode_error"></span>
                        </div>
                    </div>

                    <div class="col-lg-12 col-sm-12 col-12">
                        <div class="form-group">
                            <label>Expense for</label>
                            <textarea name="description" id="description" class="form-control" placeholder="Purpose of Expense" rows="3"></textarea>
                            <span class="text-danger error" id="description_error"></span>
                        </div>
                    </div>


                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ route('expense.list') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')

<script>
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");
        var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

        // ── 1. Fetch next Sr No ──────────────────────────────────────────────
        $.ajax({
            url: '{{ route("expenses.getNextSrNo") }}',
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + authToken },
            data: { selectedSubAdminId: selectedSubAdminId },
            success: function(response) {
                $('#sr_no').val(response.next_sr_no || 1);
            },
            error: function() {
                $('#sr_no').val(1);
            }
        });

        // ── 2. Reload Expense Type dropdown for the selected branch ──────────
        // The server-rendered list is based on the PHP session which can be
        // out of sync with localStorage. We overwrite it via the API so the
        // correct branch's types are always shown.
        loadExpenseTypes(authToken, selectedSubAdminId);
    });

    function loadExpenseTypes(authToken, selectedSubAdminId) {
        $.ajax({
            url: '/api/expense-types',
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + authToken },
            data: { selectedSubAdminId: selectedSubAdminId },
            success: function(response) {
                var $select = $('#expense_type_id');

                // Remove all options except the placeholder
                $select.find('option:not(:first)').remove();

                if (response.status && response.data && response.data.length > 0) {
                    $.each(response.data, function(i, type) {
                        $select.append($('<option>', {
                            value: type.id,
                            text:  type.type
                        }));
                    });
                }

                // Re-render Select2 if it has been initialised on this element
                if ($.fn.select2 && $select.data('select2')) {
                    $select.trigger('change.select2');
                }
            },
            error: function() {
                // Silent fallback — server-rendered options remain visible
                console.warn('Could not reload expense types from API.');
            }
        });
    }

    // ── Form submit ──────────────────────────────────────────────────────────
    $('#expenseForm').on('submit', function(e) {
        e.preventDefault();
        var authToken = localStorage.getItem("authToken");

        var $btn = $('#expenseForm button[type="submit"]');
        var originalText = $btn.html();

        $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
            .prop('disabled', true);

        $('.error').text('');
        var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

        $.ajax({
            url: '{{ route("expenses.store") }}',
            method: 'POST',
            data: $(this).serialize() + "&selectedSubAdminId=" + (selectedSubAdminId || ''),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Authorization': 'Bearer ' + authToken,
            },
            success: function(response) {
                $btn.html(originalText).prop('disabled', false);
                Swal.fire({
                    title: "Success",
                    text: "Expense Added successfully",
                    icon: "success",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#ff9f43"
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('expense.list') }}";
                    }
                });
            },
            error: function(xhr) {
                $btn.html(originalText).prop('disabled', false);
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    for (var field in errors) {
                        $('#' + field + '_error').text(errors[field][0]);
                    }
                }
            }
        });
    });
</script>

@endpush
