@extends('layout.app')

@section('title', 'Tax Rates')

@section('content')
    <style>
        #DataTables_Table_0_info {
            float: left;
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Tax Rates</h4>
                <h6>Manage Tax Rates</h6>
            </div>
            <div class="page-btn">
                <a class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addpayment"><img
                        src="admin/assets/img/icons/plus.svg" alt="img" class="me-1">Add New Tax Rates</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table  datanew">
                        <thead>
                            <tr>
                                <th>Currency Name</th>
                                <th>Currency Symbol</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>


    <div class="modal fade" id="addpayment" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add TAX </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tax Name<span class="manitory">*</span></label>
                                <input type="text">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tax Rate(%)<span class="manitory">*</span></label>
                                <input type="text">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label>Status</label>
                                <select class="select">
                                    <option value="active"> Active</option>
                                    <option value="inactive"> InActive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-submit btn-submit-add">Confirm</button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editpayment" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tax</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tax Name<span class="manitory">*</span></label>
                                <input type="text" value="SGST 4.5%	">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tax Rate(%)<span class="manitory">*</span></label>
                                <input type="text" value="4.50">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label>Status</label>
                                <select class="select">
                                    <option value="active"> Active</option>
                                    <option value="inactive"> InActive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-submit btn-submit-edit">Update</button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        $(document).ready(function () {
            var authToken = localStorage.getItem("authToken");
            var table = $("table.datanew").DataTable({ // Initialize DataTable
                searching: true,
                paging: true,
                ordering: true,
                destroy: true // Allow reinitialization
            });
            loadCurrencies(); // Load tax rates when page loads

            function loadCurrencies() {
                $.ajax({
                    url: "{{ route('currency.index') }}", // Make sure this route returns a JSON list of currencies
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function (response) {
                        let tableRows = "";
                        response.currencies.forEach(function (currency) {
                            tableRows += `
                            <tr>
                                <td>${currency.currency_name}</td>
                                <td>${currency.currency_symbol}</td>
                                <td class="text-end">
                                    <a href="javascript:void(0);" class="edit-currency" data-id="${currency.id}" data-bs-toggle="modal" data-bs-target="#editCurrencyModal">
                                        <img src="admin/assets/img/icons/edit.svg" alt="Edit">
                                    </a>
                                    <a href="javascript:void(0);" class="delete-currency mx-3" data-id="${currency.id}">
                                        <img src="admin/assets/img/icons/delete.svg" alt="Delete">
                                    </a>
                                </td>
                            </tr>`;
                        });

                        table.destroy();
                        $("table tbody").html(tableRows);
                        table = $("table.datanew").DataTable({
                            searching: true,
                            paging: true,
                            ordering: true
                        });
                    }
                });
            }

            function clearErrors() {
                $(".error-text").remove();
                $("input, select").removeClass("is-invalid");
            }

            function validateForm(modal) {
                clearErrors();
                let isValid = true;

                $(modal).find("input, select").each(function () {
                    let value = $(this).val().trim();
                    let label = $(this).prev("label").text().replace("*", "").trim();

                    if (value === "") {
                        $(this).addClass("is-invalid");
                        $(this).after(`<small class="text-danger error-text">${label} is required.</small>`);
                        isValid = false;
                    }
                });

                return isValid;
            }

            function resetForm(modal) {
                $(modal).find("input[type='text']").val(""); // Clear text inputs
                $(modal).find("select").val("active"); // Reset status to default
                clearErrors(); // Remove error messages
            }

            $(".btn-submit-add").on("click", function () {
                if (!validateForm("#addpayment")) return;

                let taxName = $("#addpayment input[type='text']").eq(0).val();
                let taxRate = $("#addpayment input[type='text']").eq(1).val();
                let taxStatus = $("#addpayment select").val();

                $.ajax({
                    url: "{{ route('tax-rates.store') }}",
                    type: "POST",
                    data: {
                        name: taxName,
                        rate: taxRate,
                        status: taxStatus,
                        _token: "{{ csrf_token() }}"
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function (response) {
                        $("#addpayment").modal("hide");
                        resetForm("#addpayment"); // Reset form after success
                        // Swal.fire("Success", response.message, "success");
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            // window.location.reload();
                        });
                        loadCurrencies();
                    }
                });
            });

            $(document).on("click", ".edit-tax", function () {
                let taxId = $(this).data("id");
                $.ajax({
                    url: `/api/tax-rates/${taxId}`,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function (response) {
                        console.log(response.taxRate.status);

                        $("#editpayment input[type='text']").eq(0).val(response.taxRate.tax_name);
                        $("#editpayment input[type='text']").eq(1).val(parseInt(response.taxRate.tax_rate));
                        $("#editpayment select").val(response.taxRate.status.toLowerCase()).trigger("change");
                        $("#editpayment").data("id", taxId);
                    }
                });
            });

            $(".btn-submit-edit").on("click", function () {
                if (!validateForm("#editpayment")) return;

                let taxId = $("#editpayment").data("id");
                let taxName = $("#editpayment input[type='text']").eq(0).val();
                let taxRate = $("#editpayment input[type='text']").eq(1).val();
                let taxStatus = $("#editpayment select").val();

                $.ajax({
                    url: `/api/tax-rates/update/${taxId}`,
                    type: "POST",
                    data: {
                        name: taxName,
                        rate: taxRate,
                        status: taxStatus,
                        _token: "{{ csrf_token() }}"
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function (response) {
                        $("#editpayment").modal("hide");
                        // Swal.fire("Success", response.message, "success");
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            // window.location.href = "/vendor";
                        });
                        loadCurrencies();
                    }
                });
            });

            $(document).on("click", ".delete-tax", function () {
                let taxId = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    confirmButtonColor: "#ff9f43", // Confirm button color (orange)
                    cancelButtonColor: "#6c757d", // Cancel button color (gray)
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/tax-rates/delete/${taxId}`,
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            headers: {
                                "Authorization": "Bearer " + authToken
                            },
                            success: function (response) {
                                // Swal.fire("Deleted!", response.message, "success");
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43", // Custom OK button color
                                    confirmButtonText: "OK"
                                });
                                loadCurrencies();
                            }
                        });
                    }
                });
            });

            // Reset form when modal is closed
            $("#addpayment, #editpayment").on("hidden.bs.modal", function () {
                resetForm(this);
            });

        });
    </script>
@endpush