@extends('layout.app')

@section('title', 'Expense Edit')

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
            <h4>Edit Expense</h4>

        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                {{-- Sr No --}}
                <div class="col-lg-4 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Sr No</label>
                        <div class="input-groupicon">
                            <input type="text" id="sr_no" placeholder="sr_no" readonly>
                        </div>
                    </div>
                </div>

                {{-- Expense Name --}}
                <div class="col-lg-4 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Expense Name</label>
                        <div class="input-groupicon">
                            <input type="text" id="expense_name" placeholder="Choose Expense Name">
                        </div>
                    </div>
                </div>

                {{-- Expense Date --}}
                <div class="col-lg-4 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Expense Date</label>
                        <div class="input-groupicon">
                            <input type="text" id="expense_date" placeholder="Choose Date" class="datetimepicker">
                            <div class="addonset">
                                <img src="{{ env('ImagePath').'/admin/assets/img/icons/calendars.svg' }}" alt="img">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Amount --}}
                <div class="col-lg-4 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Amount</label>
                        <div class="input-groupicon">
                            <input type="text" id="amount">

                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-6">
                    <div class="form-group">
                        <label>Expense Type</label>
                        <select class="select" name="expense_type_id" id="expense_type_id">
                            <option value="">Select Expense Type</option>

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
                        <textarea id="description" name="description" class="form-control" placeholder="Purpose of Expense" rows="3"></textarea>
                        <span class="text-danger error" id="description_error"></span>
                    </div>
                </div>


                {{-- Hidden ID --}}
                <input type="hidden" id="expense_id">

                {{-- Buttons --}}
                <div class="col-lg-12">
                    <a class="btn btn-submit me-2" id="updateExpense">Update</a>
                    <a href="{{ route('expense.list') }}" class="btn btn-cancel">Cancel</a>
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
        let expenseId = url.split("/").pop();
        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");


        // Fetch and populate data
        $.ajax({
            url: `/api/expenses/${expenseId}/edit`,
            method: 'GET',
            headers: {
                Authorization: `Bearer ${authToken}`,
            },
            data: { selectedSubAdminId: selectedSubAdminId },
            success: function(response) {
                const expense = response.data;
                const expenseTypes = response.expenseTypes;

                // Build the options list with Expense Type names
                let options = '<option value="">Select Expense Type</option>';
                expenseTypes.forEach(function(type) {
                    // Convert both to numbers before comparing
                    const selected = (Number(type.id) === Number(expense.expense_type_id)) ? 'selected' : '';
                    options += `<option value="${type.id}" ${selected}>${type.type}</option>`;
                });
                $('#expense_type_id').html(options);
                $('#sr_no').val(expense.sr_no);
                $('#expense_name').val(expense.expense_name);
                $('#expense_date').val(formatDate(expense.expense_date));
                $('#amount').val(expense.amount);
                $('#payment_mode').val(expense.payment_mode || '').trigger('change');
                $('#description').val(expense.description);
                $('#expense_id').val(expense.id);
            },
            // error: function() {
            //     window.location.href = "{{ route('expense.list') }}";
            // }
        });

        // Format date from yyyy-mm-dd to dd-mm-yyyy
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}-${month}-${year}`;
        }

        // Update logic
        $('#updateExpense').click(function() {
            const id = $('#expense_id').val();
             const amount = parseFloat($('#amount').val()) || 0;

            // Validation: Amount cannot be negative
            if (amount < 0) {
                // Show error message below input
                if ($("#amountError").length === 0) {
                    $('#amount').after('<small id="amountError" class="text-danger">Amount cannot be negative.</small>');
                }
                $('#amount').val(0); // reset to 0
                return; // stop form submission
            } else {
                $("#amountError").remove(); // remove error if valid
            }

            const $btn = $(this);
            const originalText = $btn.html();

            // Show loader and disable button
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop('disabled', true);

            $.ajax({
                url: `/api/expenses/${id}`,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    sr_no: $('#sr_no').val(),
                    expense_name: $('#expense_name').val(),
                    expense_date: $('#expense_date').val(),
                    amount: $('#amount').val(),
                    payment_mode: $('#payment_mode').val(),
                    description: $('#description').val(),
                    expense_type_id: $('#expense_type_id').val(), // <-- this was missing!
                },
                headers: {
                    Authorization: `Bearer ${authToken}`,
                },
                success: function(res) {
                    // Restore original button
                    $btn.html(originalText).prop('disabled', false);

                    Swal.fire({
                        title: "Success!",
                        text: "Expense updated successfully!",
                        icon: "success",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#ff9f43" // your custom color
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('expense.list') }}";
                        }
                    });
                },
                error: function(xhr) {
                    $btn.html(originalText).prop('disabled', false);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let messages = Object.values(errors).map(msg => msg[0]).join("\n");
                        Swal.fire("Validation Error", messages, "error"); // show proper error instead of redirect
                    } else {
                        Swal.fire("Error", "Something went wrong. Try again.", "error");
                    }
                }
            });
        });
    });
</script>

@endpush