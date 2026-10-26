@extends('layout.app')

@section('title', 'Product Edit')

@section('content')
<style>
    @media screen and (max-width: 768px) {
        .select2-container {
            width: 0 !important;
        }

        .form-group {
            margin-bottom: 10px !important
        }
    }
    
    .product-label-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    .product-label-action label {
        margin-bottom: 0;
    }

    .product-label-action .btn-add-inline {
        padding: 7px 13px;
        border: 0;
        border-radius: 7px;
        color: #fff;
        background: #ff9f43;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
    }

    .product-label-action .btn-add-inline:hover,
    .product-label-action .btn-add-inline:focus {
        color: #fff;
        background: #ff8f1f;
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Edit Product</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="productForm">
                <input type="hidden" name="product_id" id="product_id">
                <div class="row">
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="name" id="name">
                            <span class="error_name text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <div class="product-label-action">
                                <label>Category</label>
                                <button type="button" class="btn-add-inline open-custom-modal" data-type="category">Add Category</button>
                            </div>
                            <select class="select" name="category_id" id="category_id">
                                <option value="">Choose Category</option>
                            </select>
                            <span class="error_category_id text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <div class="product-label-action">
                                <label>Brand</label>
                                <button type="button" class="btn-add-inline open-custom-modal" data-type="brand">Add Brand</button>
                            </div>
                            <select class="select" name="brand_id" id="brand_id">
                                <option value="">Choose Brand</option>
                            </select>
                            <span class="error_brand_id text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>SKU</label>
                            <input type="number" name="SKU" id="SKU" class="form-control" step="1" min="0">
                            <span class="error_SKU text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" step="1" min="0">
                            <span class="error_quantity text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <div class="product-label-action">
                                <label>Quantity Type</label>
                                <button type="button" class="btn-add-inline open-custom-modal" data-type="quantity">Add Quantity Type</button>
                            </div>
                            <select class="select" name="quantity_type_id" id="quantity_type_id">
                                <option value="">Choose Quantity Type</option>
                            </select>
                            <span class="error_quantity_type text-danger"></span>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Price</label>
                            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0">
                            <span class="error_price text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="select" name="status" id="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <span class="error_status text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Stock</label>
                            <select class="select" name="availablility" id="availablility" disabled>
                                <option value="in_stock">In Stock</option>
                                <option value="out_stock">Out Of Stock</option>
                            </select>
                            <span class="error_availablility text-danger"></span>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const qtyInput = document.getElementById('quantity');

                            if (qtyInput) {
                                qtyInput.addEventListener('input', function() {
                                    const qty = parseInt(this.value);

                                    // Update using jQuery + Select2
                                    if (!isNaN(qty) && qty === 0) {
                                        $('#availablility').val('out_stock').trigger('change');
                                    } else {
                                        $('#availablility').val('in_stock').trigger('change');
                                    }

                                    console.log("Quantity changed to", qty);
                                });
                            }
                        });
                    </script>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Barcode</label>
                            <input type="text" name="barcode" id="barcode" class="form-control">
                            <span class="error_barcode text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" id="description"></textarea>
                            <span class="error_description text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Product Image</label>
                            <div class="image-upload">
                                <input type="file" name="images[]" id="images" multiple>
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath').'/admin/assets/img/icons/upload.svg' }}" alt="img">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>

                            <div class="image-preview" style="margin-top: 10px;">

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <a href="javascript:void(0);" class="btn btn-submit me-2 submit">Submit</a>
                        <a href="{{ route('product.list') }}" class="btn btn-cancel">Cancel</a><br>
                        <span class="success_submit text-danger"></span>
                    </div>
                </div>
            </form>

        </div>

    </div>
    <!-- Custom Modal -->
    <div class="modal fade" id="customModal" tabindex="-1" aria-labelledby="customModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customModalLabel">Add New</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="modalOpen=false;"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="custom_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="custom_name" placeholder="Enter name">
                        <span class="text-danger error_model d-block mt-1"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="modalOpen=false;">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCustomBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
    @endsection
    @push('js')
    <script>
        document.getElementById("images").addEventListener("change", function(event) {
            var files = event.target.files; // Get selected files
            var previewDiv = document.querySelector(".image-preview"); // Select preview div
            previewDiv.innerHTML = ""; // Clear existing previews

            Array.from(files).forEach((file, index) => {
                var reader = new FileReader(); // Create FileReader instance

                reader.onload = function(e) {
                    // Create image container
                    var imageContainer = document.createElement("div");
                    imageContainer.style.position = "relative";
                    imageContainer.style.display = "inline-block";

                    // Create image element
                    var img = document.createElement("img");
                    img.src = e.target.result;
                    img.alt = "Preview";
                    img.style.maxWidth = "100px";
                    img.style.maxHeight = "100px";
                    img.style.borderRadius = "5px";
                    img.style.boxShadow = "2px 2px 10px rgba(0,0,0,0.1)";
                    img.style.marginRight = "5px";

                    // Create remove button
                    var removeBtn = document.createElement("button");
                    removeBtn.innerHTML = "&times;";
                    removeBtn.style.position = "absolute";
                    removeBtn.style.top = "0";
                    removeBtn.style.right = "0";
                    removeBtn.style.background = "red";
                    removeBtn.style.color = "white";
                    removeBtn.style.border = "none";
                    removeBtn.style.borderRadius = "50%";
                    removeBtn.style.width = "20px";
                    removeBtn.style.height = "20px";
                    removeBtn.style.cursor = "pointer";
                    removeBtn.style.display = "flex";
                    removeBtn.style.alignItems = "center";
                    removeBtn.style.justifyContent = "center";
                    removeBtn.style.fontSize = "14px";

                    // Remove image on click
                    removeBtn.addEventListener("click", function() {
                        imageContainer.remove();
                    });

                    // Append elements
                    imageContainer.appendChild(img);
                    imageContainer.appendChild(removeBtn);
                    previewDiv.appendChild(imageContainer);
                };

                reader.readAsDataURL(file); // Read file as Data URL
            });
        });
        $(document).on("click", ".remove-image", function(e) {
            e.preventDefault(); // ❗ prevent page reload and form submission

            var authToken = localStorage.getItem("authToken");
            var productId = $(this).data("id");
            var imageName = $(this).data("image");
            var imageContainer = $(this).parent();

            Swal.fire({
                title: "Are you sure?",
                text: "Do you really want to delete this image?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#ff9f43",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/api/remove-product-image",
                        type: "POST",
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        data: {
                            product_id: productId,
                            image: imageName,
                            _token: $('meta[name="csrf-token"]').attr("content")
                        },
                        success: function(response) {
                            if (response.success) {
                                imageContainer.remove();
                                Swal.fire({
                                    title: "Deleted!",
                                    text: "Product image removed successfully!",
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43"
                                }).then(() => {

                                });
                            } else {
                                Swal.fire("Error", "Error removing image.", "error");
                            }
                        },
                        error: function() {
                            Swal.fire("Error", "Something went wrong.", "error");
                        }
                    });
                }
            });
        });
    $(document).on("input", "#price", function () {
    let value = parseFloat($(this).val());
    let errorSpan = $(".error_price");

    if (value < 0) {
        $(this).val(""); // clear invalid value
        errorSpan.text("Price cannot be negative.");
    } else {
        errorSpan.text(""); // clear error if valid
    }
    });

        $(document).ready(function() {
            $(document).on('click', '.submit', function(e) {
                e.preventDefault();

                var $btn = $(this); // cache the button
                var originalText = $btn.html();

                // Show loading text and disable the button
                $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...').prop('disabled', true);


                var authToken = localStorage.getItem("authToken");
                let formData = new FormData($('#productForm')[0]);

                $('.error_name, .error_category_id, .error_brand_id, .error_SKU, .error_quantity, .error_price, .error_status, .error_barcode, .error_description, .error_availablility').text('');

                $.ajax({
                    url: "/api/updateProduct",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        $btn.html(originalText).prop('disabled', false);

                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: "Product updated successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43" // your custom color
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('product.list') }}";
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        $btn.html(originalText).prop('disabled', false);

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.error_' + key).text(value[0]);
                            });
                        } else {
                            Swal.fire("Error", "Something went wrong.", "error");
                        }
                    }
                });
            });
        });

        $(document).ready(function() {
            const sub_branch_id = localStorage.getItem('selectedSubAdminId');
            var authToken = localStorage.getItem("authToken");
            let url = window.location.pathname;
            let productId = url.split("/").pop(); // e.g. /product/edit/5 -> 5

            // 🔹 Fetch Categories
            function get_category(selectedId = null) {
                let url = `/api/get-category`;
                if (sub_branch_id) {
                    url += `?sub_admin_id=${sub_branch_id}`;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#category_id");
                            select.empty().append('<option value="">Choose Category</option>');

                            $.each(response.data, function(key, item) {
                                select.append(`<option value="${item.id}">${item.name}</option>`);
                            });

                            if (selectedId) {
                                select.val(selectedId).trigger('change');
                            }
                        }
                    }
                });
            }

            // 🔹 Fetch Brands
            function get_brand(selectedId = null) {
                let url = `/api/get-brand`;
                if (sub_branch_id) {
                    url += `?sub_admin_id=${sub_branch_id}`;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#brand_id");
                            select.empty().append('<option value="">Choose Brand</option>');

                            $.each(response.data, function(key, item) {
                                select.append(`<option value="${item.id}">${item.name}</option>`);
                            });

                            if (selectedId) {
                                select.val(selectedId).trigger('change');
                            }
                        }
                    }
                });
            }

            function get_quantity_type(selectedId = null) {
                let authToken = localStorage.getItem("authToken");

                let url = `/api/get-quantity-type`;
                if (sub_branch_id) {
                    url += `?sub_admin_id=${sub_branch_id}`;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#quantity_type_id");
                            select.empty().append('<option value="">Choose Quantity Type</option>');

                            $.each(response.data, function(key, item) {
                                select.append(`<option value="${item.id}">${item.quantity_type}</option>`);
                            });

                            if (selectedId) {
                                select.val(selectedId).trigger('change');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching quantity types:", xhr.responseText);
                    }
                });
            }

            $('#category_id').select2({
                placeholder: "Select or Add Category",
                tags: true,
                width: '100%',
                allowClear: true
            });
            $('#brand_id').select2({
                placeholder: "Select or Add Brand",
                tags: true,
                width: '100%',
                allowClear: true
            });
            $('#quantity_type_id').select2({
                placeholder: "Select or Add Quantity Type",
                tags: true,
                width: '100%',
                allowClear: true
            });

            // 🔹 Load Product and prefill form
            $.ajax({
                url: `/api/edit_product/${productId}`,
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${authToken}`
                },
                success: function(response) {
                    if (!response.status) {
                        alert("Product not found!");
                        return;
                    }

                    const product = response.product;

                    // ✅ Populate form fields
                    $('#product_id').val(product.id);
                    $('#expiry_date').val(product.expiry_date);
                    $('#name').val(product.name);
                    $('#SKU').val(product.SKU);
                    $('#quantity').val(product.quantity);
                    $('#quantity_type_id').val(product.quantity_type_id).trigger('change');
                    $('#price').val(product.price);
                    $('#barcode').val(product.barcode);
                    $('#description').val(product.description);

                    $('#availablility').val(product.availablility).trigger('change');
                    $('#status').val(product.status).trigger('change');
                    $('#warranty').val(product.warranty).trigger('change');
                    $('#voltage').val(product.voltage).trigger('change');
                    $('#capacity').val(product.capacity).trigger('change');
                    
                    // Clear old preview first
                    $('.image-preview').empty();

                    // Check if product.image_url exists and is an array
                    if (product.image_url && Array.isArray(product.image_url)) {
                        product.image_url.forEach(function(url) {
                            $('.image-preview').append(
                                `<img src="${url}" alt="Product Image" style="width:100px;height:100px;object-fit:cover;margin:5px;border-radius:5px;border:1px solid #ddd;">`
                            );
                        });
                    }

                    // ✅ Call dropdown loaders with preselected value
                    get_category(product.category_id);
                    get_brand(product.brand_id);
                    get_quantity_type(product.quantity_type_id);

                    // ✅ Existing images show
                    if (product.images) {
                        let images = JSON.parse(product.images);
                        let html = "";
                        images.forEach(function(img) {
                            html += `
                        <div class="image-container" data-image="${img}" style="position: relative;">
                            <img src="/storage/${img}" width="100" height="100" style="border-radius: 5px; box-shadow: 2px 2px 10px rgba(0,0,0,0.1);">
                            <button class="remove-image" data-id="${product.id}" data-image="${img}" style="
                                position: absolute;
                                top: -5px;
                                right: -5px;
                                background: red;
                                color: white;
                                border: none;
                                border-radius: 50%;
                                width: 20px;
                                height: 20px;
                                cursor: pointer;
                                font-size: 14px;
                            ">&times;</button>
                        </div>
                    `;
                        });
                        $(".existing-images").html(html).show();
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });

            let modalOpen = false;
            let currentType = null;
            let currentCustomValue = null;

            $(document).on('click', '.open-custom-modal', function() {
                if (modalOpen) return;

                currentType = $(this).data('type');
                currentCustomValue = null;
                modalOpen = true;
                $(".error_model").text("");
                $('#custom_name').val('');
                $('#customModalLabel').text(currentType === "category" ? "Add New Category" : currentType === "quantity" ? "Add New Quantity Type" : "Add New Brand");
                $('#customModal').modal('show');
            });

            // 🔹 Listen for Select2 New Tag
            $('#category_id, #brand_id, #quantity_type_id').on('select2:select', function(e) {
                let data = e.params.data;
                let selectedValue = data.id;
                let selectedText = data.text;
                let targetId = $(this).attr('id');

                // ✅ If user typed new value (not a number id)
                if (isNaN(parseInt(selectedValue))) {
                    currentCustomValue = selectedValue;
                    modalOpen = true;

                    if (targetId === "category_id") {
                        currentType = "category";
                    } else if (targetId === "brand_id") {
                        currentType = "brand";
                    } else if (targetId === "quantity_type_id") {
                        currentType = "quantity";
                    }

                    // Open Modal
                    let modalTitle = "Add New " + currentType.charAt(0).toUpperCase() + currentType.slice(1);
                    if (currentType === "quantity") modalTitle = "Add New Quantity Type";
                    $('#customModalLabel').text(modalTitle);
                    $('#custom_name').val(selectedText);
                    $('#customModal').modal('show');
                }
            });
            $('#saveCustomBtn').on('click', function() {
                const name = $('#custom_name').val().trim();
                let sub_admin_id = sub_branch_id;

                $(".error_model").text(""); // reset error

                if (name === "") {
                    $(".error_model").text("Please enter a name");
                    return;
                }

                let url = "";
                let targetDropdown = "";
                let postData = {
                    sub_admin_id: sub_admin_id
                };

                if (currentType === "category") {
                    url = "/api/addcategory";
                    targetDropdown = "#category_id";
                    postData.name = name;
                } else if (currentType === "brand") {
                    url = "/api/addBrand";
                    targetDropdown = "#brand_id";
                    postData.name = name;
                } else if (currentType === "quantity") {
                    url = "/api/add-quantity-type";
                    targetDropdown = "#quantity_type_id";
                    postData.quantity_type = name;
                }

                // 🔹 Show loader
                let btn = $("#saveCustomBtn");
                btn.prop("disabled", true).html("Saving... <i class='fa fa-spinner fa-spin'></i>");

                $.ajax({
                    url: url,
                    type: "POST",
                    data: postData,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        let id, text;

                        if (currentType === "category") {
                            id = response.category.id;
                            text = response.category.name;
                        } else if (currentType === "brand" && response.brand) {
                            id = response.brand.id;
                            text = response.brand.name;
                        } else if (currentType === "quantity" && response.data) {
                            id = response.data.id;
                            text = response.data.quantity_type;
                        }

                        // remove temp option
                        if (currentCustomValue && isNaN(parseInt(currentCustomValue))) {
                            $(targetDropdown).find(`option[value="${currentCustomValue}"]`).remove();
                        }

                        // add new option
                        if ($(targetDropdown).find(`option[value="${id}"]`).length === 0) {
                            $(targetDropdown).append(new Option(text, id, true, true));
                        }

                        // select new
                        $(targetDropdown).val(id).trigger('change');

                        // refresh dropdown data
                        if (currentType === "category") {
                            get_category(id);
                        } else if (currentType === "brand") {
                            get_brand(id);
                        } else if (currentType === "quantity") {
                            get_quantity_type(id);
                        }

                        // ✅ Reset modal
                        btn.prop("disabled", false).text("Save");
                        $('#customModal').modal('hide');
                        modalOpen = false;
                        currentCustomValue = null;
                        currentType = null;
                        
                        Swal.fire({
                            title: "Success",
                            text: response.message || "Added successfully",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43"
                        });
                    },
                    error: function(xhr) {
                        let msg = "Error while saving " + currentType;
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $(".error_model").text(msg);
                        btn.prop("disabled", false).text("Save");
                        console.error(xhr.responseText);
                    }
                });
            });

            // Reset flag when modal closes
            $('#customModal').on('hidden.bs.modal', function() {
                modalOpen = false;
                currentCustomValue = null;
                currentType = null;
            });
        });
    </script>
    @endpush