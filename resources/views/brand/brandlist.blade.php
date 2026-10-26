@extends('layout.app')

@section('title', 'Brand List')

@section('content')
    @if (session('error'))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') }}
        </div>

        <style>
            .fade-out {
                opacity: 1;
                transition: opacity 0.5s ease-out;
            }

            .fade-out.hidden {
                opacity: 0;
            }
        </style>

        <script>
            setTimeout(function() {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden');
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }
            }, 4000);
        </script>
    @endif
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Brands</h4>
            </div>
            <div class="page-btn">
                    <a href="{{ route('brand.add') }}" class="btn btn-sm btn-added"><img
                            src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-2" alt="img">New
                        Brand</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
                        <div class="search-path"></div>
                        <div class="search-input">
                            <a class="btn btn-searchset">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img">
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card" id="filter_inputs">
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Brand Name">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Brand Description">
                                </div>
                            </div>
                            <div class="col-lg-1 col-sm-6 col-12 ms-auto">
                                <div class="form-group">
                                    <a class="btn btn-filters ms-auto"><img
                                            src="{{ env('ImagePath') . 'admin/assets/img/icons/search-whites.svg' }}"
                                            alt="img"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <!-- <th>
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th> -->
                                <th>Image</th>
                                <th>Brand Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            var table = $('.datanew').DataTable(); // Initialize DataTable
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            let url = "/api/getAllBrand";
            if (selectedSubAdminId) {
                url += `?selectedSubAdminId=${selectedSubAdminId}`;
            }
            fetchBrands(); // Call function to load data

            function fetchBrands() {
                $.ajax({
                    url: url, // Your API route
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let brands = response.data;
                            let tableBody = [];
                            // Function to capitalize first letter of each word
                            function capitalizeWords(str) {
                                if (!str || str.trim() === '') return 'N/A';
                                return str.replace(/\b\w/g, function(char) {
                                    return char.toUpperCase();
                                });
                            }
                            brands.forEach((brand) => {
                                let createdAt = brand.created_at ? brand.created_at.split("T")[
                                    0] : "N/A";
                                const imagePath = "{{ env('ImagePath') }}";
                                let imageUrl = brand.logo ?
                                    `${imagePath}/storage/${brand.logo}` :
                                    `${imagePath}admin/assets/img/product/noimage.png`;

                                tableBody.push([
                                    //     `<label class="checkboxs">
                                //     <input type="checkbox">
                                //     <span class="checkmarks"></span>
                                // </label>`,
                                    `<a class="product-img">
                                <img src="${imageUrl}" alt="product">
                            </a>`,
                                    capitalizeWords(brand.name),
                                    `
                                
                                 
                                <a class="me-3" href="/edit-brand/${brand.id}">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                            </a>
                             
                            <a class="me-3 confirm-text delete-brand" data-id="${brand.id}" href="javascript:void(0);">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                            </a>
                            `
                                ]);
                            });

                            table.clear().rows.add(tableBody)
                        .draw(); // Clear, add data, and redraw the table
                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html('<tr><td colspan="5">No brands found</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching brands:", error);
                    }
                });
            }
        });

        $(document).on('click', '.delete-brand', function() {
            var brandId = $(this).data('id');
            var authToken = localStorage.getItem("authToken");
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
                        url: `/api/deleteBrand/${brandId}`,
                        type: 'POST',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status) {
                                // Swal.fire("Deleted!", response.message, "success");
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43", // Custom OK button color
                                    confirmButtonText: "OK"
                                });
                                $(`.delete-brand[data-id="${brandId}"]`).closest('tr').remove();
                            } else {
                                Swal.fire("Error!", response.message, "error");
                            }
                        },
                        error: function(xhr) {
                            let message = "Something went wrong!";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                title: "Error!",
                                text: message,
                                icon: "error",
                                confirmButtonColor: "#ff9f43", // Orange OK button
                                confirmButtonText: "OK"
                            });
                        }

                    });
                }
            });
        });
    </script>
@endpush
