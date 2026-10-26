@extends('layout.app')

@section('title', 'Debit Notes Add')

@push('css')
<style>
@media (max-width: 768px) {
    .select2-container {
        width: 100% !important;
    }
}
 a.btn.back-button {
    background: #ff9f43;
    color: #fff;
}

.note-type-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.note-type-toolbar .btn {
    white-space: nowrap;
}

@media (max-width: 575.98px) {
    .note-type-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endpush

@section('content')
<div class="content">
    {{-- <div class="page-header">
        <div class="page-title">
            <h4>Create Debit Note</h4>
        </div>
    </div> --}}
    <div class="page-header ">
            <div class="page-title">
                <h4>Create Debit Note</h4>
            </div>
             <div class="back-button">
                <a href="{{ route('debit-notes-items.index') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>
    <div class="card">
        <div class="card-body">
            <form id="debit-note-item-form">
                @csrf
                <input type="hidden" name="order_id" id="order_id">
                <input type="hidden" name="purchase_id" id="purchase_id">
                <input type="hidden" name="user_id" id="user_id">

                <div class="row">
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select name="transaction_type" id="transaction_type" class="form-control transaction_type-select2">
                                <option value="payment">Payment</option>
                                <option value="receipt">Receipt</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6" id="invoice_number_div">
                        <div class="form-group">
                            <label>Bill No <span class="manitory">*</span></label>
                            <select name="invoice_number" id="invoice_number" class="form-control invoice_number-select2">
                                <option value="">Select Bill No</option>
                                {{-- Data will be loaded via AJAX --}}
                            </select>
                            <div class="text-danger error-purchase_id"></div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6" id="order_number_div" style="display: none;">
                        <div class="form-group">
                            <label>Order Number <span class="manitory">*</span></label>
                            <select name="order_number" id="order_number" class="form-control order_number-select2">
                                <option value="">Select Order Number</option>
                                {{-- Data will be loaded via AJAX --}}
                            </select>
                            <div class="text-danger error-order_id"></div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label id="user_label">Vendor Name</label>
                            <input type="text" id="user_name" class="form-control" readonly style="background-color: #e9ecef;">
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <div class="note-type-toolbar">
                                <label class="mb-0">Debit Note Type <span class="manitory">*</span></label>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#addDebitNoteTypeModal" title="Add Debit Note Type">
                                    Add Type
                                </button>
                            </div>
                            <select name="credit_note_type" id="credit_note_type" class="form-control credit_note_type-select2">
                                <option value="">Select Type</option>
                                {{-- Data will be loaded via AJAX --}}
                            </select>
                            <div class="text-danger error-credit_note_type"></div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Total Amount</label>
                            <input type="text" name="total_amount" id="total_amount" class="form-control" readonly style="background-color: #e9ecef;">
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Paid Amount</label>
                            <input type="text" name="paid_amount" id="paid_amount" class="form-control" readonly style="background-color: #e9ecef;">
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Remaining Amount</label>
                            <input type="text" name="remaining_amount" id="remaining_amount" class="form-control" readonly style="background-color: #e9ecef;">
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Settlement Amount <span class="manitory">*</span></label>
                            <input type="number" step="0.01" name="settlement_amount" id="settlement_amount" class="form-control" value="0">
                            <div class="text-danger error-settlement_amount"></div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Final Total</label>
                            <input type="text" name="final_total" id="final_total" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Reason <span class="manitory">*</span></label>
                            <textarea name="reason" id="reason" class="form-control"></textarea>
                            <div class="text-danger error-reason"></div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2" id="submit-btn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Submit
                        </button>
                        <a href="{{ route('debit-notes-items.index') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addDebitNoteTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Debit Note Type</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label>Type Name <span class="manitory">*</span></label>
                    <input type="text" id="debit_note_type_name" class="form-control" placeholder="Enter type name">
                    <div class="text-danger mt-1" id="debit_note_type_modal_error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-submit" id="save-debit-note-type-btn">Save</button>
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");
        var selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

        $('.transaction_type-select2, .invoice_number-select2, .order_number-select2, .credit_note_type-select2').select2({
            placeholder: "Select option",
            allowClear: true,
            width: '100%'
        });

        function appendDebitNoteTypeOption(typeId, typeName) {
            const optionExists = $('#credit_note_type option[value="' + typeId + '"]').length > 0;

            if (!optionExists) {
                $('#credit_note_type').append(new Option(typeName, typeId, false, false));
            }

            $('#credit_note_type').val(typeId).trigger('change');
        }

        function toggleLoading(isLoading) {
            const btn = $('#submit-btn');
            const spinner = btn.find('.spinner-border');
            if (isLoading) {
                btn.attr('disabled', true);
                spinner.removeClass('d-none');
            } else {
                btn.attr('disabled', false);
                spinner.addClass('d-none');
            }
        }

        $('#transaction_type').on('change', function() {
            let type = $(this).val();
            if (type === 'receipt') {
                $('#order_number_div').show();
                $('#invoice_number_div').hide();
                $('#user_label').text('Customer Name');
            } else {
                $('#order_number_div').hide();
                $('#invoice_number_div').show();
                $('#user_label').text('Vendor Name');
            }
            // Clear fields
            $('#order_id, #purchase_id, #user_id, #user_name, #total_amount, #paid_amount, #remaining_amount, #final_total').val('');
            $('#order_number, #invoice_number').val('').trigger('change');
        });

        function loadCreateData() {
            let url = "{{ route('debit-note-items.create-data') }}";
            if (selectedSubAdminId && selectedSubAdminId !== "null") {
                url += `?selectedSubAdminId=${selectedSubAdminId}`;
            }

            $.ajax({
                url: url,
                type: "GET",
                headers: { "Authorization": "Bearer " + authToken },
                success: function(response) {
                    if (response.status === 'success') {
                        let invoiceOptions = '<option value="">Select Bill No</option>';
                        response.invoices.forEach(function(invoice) {
                            const billNo = invoice.bill_no || invoice.invoice_number;
                            invoiceOptions += `<option value="${billNo}">${billNo}</option>`;
                        });
                        $('#invoice_number').html(invoiceOptions).trigger('change');

                        let orderOptions = '<option value="">Select Order Number</option>';
                        response.orders.forEach(function(order) {
                            orderOptions += `<option value="${order.order_number}">${order.order_number}</option>`;
                        });
                        $('#order_number').html(orderOptions).trigger('change');

                        let typeOptions = '<option value="">Select Type</option>';
                        response.creditNoteTypes.forEach(function(type) {
                            typeOptions += `<option value="${type.id}">${type.type_name}</option>`;
                        });
                        $('#credit_note_type').html(typeOptions).trigger('change');
                    }
                }
            });
        }

        loadCreateData();

        $('#invoice_number').on('change', function() {
            var invoiceNumber = $(this).val();
            if (invoiceNumber) {
                let url = `/api/getPurchaseDetails/${invoiceNumber}`;
                if (selectedSubAdminId && selectedSubAdminId !== "null") {
                    url += `?selectedSubAdminId=${selectedSubAdminId}`;
                }
                $.ajax({
                    url: url,
                    type: "GET",
                    headers: { 'Authorization': 'Bearer ' + authToken },
                    success: function(response) {
                        if (response.purchase) {
                            var data = response.purchase;
                            $('#purchase_id').val(data.id);
                            $('#user_id').val(data.vendor_id);
                            $('#user_name').val(data.vendor_name);
                            $('#total_amount').val(data.total_amount);
                            $('#paid_amount').val(data.paid_amount);
                            $('#remaining_amount').val(data.remaining_amount);
                            $('#settlement_amount').val(0);
                            calculateFinalTotal();
                        }
                    }
                });
            }
        });

        $('#order_number').on('change', function() {
            var orderNumber = $(this).val();
            if (orderNumber) {
                let url = `/api/getSaleDetails/${orderNumber}`;
                if (selectedSubAdminId && selectedSubAdminId !== "null") {
                    url += `?selectedSubAdminId=${selectedSubAdminId}`;
                }
                $.ajax({
                    url: url,
                    type: "GET",
                    headers: { 'Authorization': 'Bearer ' + authToken },
                    success: function(response) {
                        if (response.order) {
                            var data = response.order;
                            $('#order_id').val(data.id);
                            $('#user_id').val(data.user_id);
                            $('#user_name').val(data.user_name);
                            $('#total_amount').val(data.total_amount);
                            $('#paid_amount').val(data.paid_amount);
                            $('#remaining_amount').val(data.remaining_amount);
                            $('#settlement_amount').val(0);
                            calculateFinalTotal();
                        }
                    }
                });
            }
        });

        $('#settlement_amount').on('input', function() {
            calculateFinalTotal();
        });

        function calculateFinalTotal() {
            var totalAmt = parseFloat($('#total_amount').val()) || 0;
            var settlement = parseFloat($('#settlement_amount').val()) || 0;
            $('#final_total').val((totalAmt - settlement).toFixed(2));
        }

        $('#save-debit-note-type-btn').on('click', function() {
            const $btn = $(this);
            const typeName = $('#debit_note_type_name').val().trim();
            $('#debit_note_type_modal_error').text('');

            if (!typeName) {
                $('#debit_note_type_modal_error').text('Type name is required.');
                return;
            }

            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            $.ajax({
                url: "{{ route('credit-notes-types.store') }}",
                type: "POST",
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                data: {
                    type_name: typeName,
                    sub_admin_id: selectedSubAdminId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);

                    if (response.status && response.data) {
                        appendDebitNoteTypeOption(response.data.id, response.data.type_name);
                        $('#debit_note_type_name').val('');
                        $('#addDebitNoteTypeModal').modal('hide');
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonColor: "#ff9f43",
                        });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        $('#debit_note_type_modal_error').text(xhr.responseJSON?.errors?.type_name?.[0] ||
                            'Type name is required.');
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: xhr.responseJSON?.message || "Unable to save note type.",
                            icon: "error",
                            confirmButtonColor: "#ff9f43",
                        });
                    }
                }
            });
        });

        $('#addDebitNoteTypeModal').on('hidden.bs.modal', function() {
            $('#debit_note_type_name').val('');
            $('#debit_note_type_modal_error').text('');
            $('#save-debit-note-type-btn').prop('disabled', false).html('Save');
        });

        $('#debit-note-item-form').on('submit', function(e) {
            e.preventDefault();
            $('.text-danger').text('');
            toggleLoading(true);

            let formData = $(this).serialize();
            if (selectedSubAdminId) {
                formData += `&selectedSubAdminId=${selectedSubAdminId}`;
            }

            $.ajax({
                url: "{{ url('api/debit-note-items/store') }}",
                type: 'POST',
                headers: { "Authorization": "Bearer " + authToken },
                data: formData,
                success: function(response) {
                    toggleLoading(false);
                    if (response.status === 'success') {
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.href = "{{ route('debit-notes-items.index') }}";
                        });
                    }
                },
                error: function(xhr) {
                    toggleLoading(false);
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(key => {
                            $(`.error-${key}`).text(errors[key][0]);
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Something went wrong. Please try again.",
                            icon: "error",
                            confirmButtonColor: "#ff9f43",
                        });
                    }
                }
            });
        });
    });
</script>
@endpush
