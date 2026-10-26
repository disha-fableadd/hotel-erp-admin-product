@extends('layout.app')

@section('title', 'Product List')

@section('content')
    <style>
        .sorting_1 {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        /* --- Premium Dropdown Styling --- */
        .action-dropdown .dropdown-menu {
            border: 1px solid #f1f5f9 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
        }
        
        .action-dropdown .dropdown-item:hover {
            background-color: #f8fafc !important;
            color: #ff9f43 !important;
            transform: translateX(4px);
        }
        
        .action-dropdown .dropdown-item.text-danger:hover {
            background-color: #fef2f2 !important;
            color: #dc2626 !important;
        }
        
        .action-dropdown .btn:hover {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }

        /* Prevent vertical scrollbar caused by dropdown in table-responsive */
        @media (min-width: 992px) {
            .table-responsive {
                overflow: visible !important;
            }
        }
        
        .table-responsive {
            min-height: 250px;
        }

        .table-scroll-top {
            overflow-x: auto;
            overflow-y: hidden;
            height: 20px;
            width: 100%;
            margin-bottom: 5px;
        }

        .table-scroll-top div {
            height: 1px;
        }

        .table-scroll-top {
            display: none;
        }

        .product-filter-wrap {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .product-filter-box {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .product-filter-box .form-group {
            margin-bottom: 0;
        }

        .product-filter-search {
            min-width: 260px;
        }

        .product-filter-select {
            min-width: 180px;
        }

        .product-filter-select .select2-container {
            width: 100% !important;
        }

        .product-filter-select .select2-container--default .select2-selection--single {
            height: 40px !important;
            border: 1px solid #dbe0e6 !important;
            border-radius: 5px !important;
            display: flex !important;
            align-items: center !important;
        }

        .product-filter-select .select2-container--default.select2-container--open .select2-selection--single,
        .product-filter-select .select2-container--default .select2-selection--single:focus {
            border-color: #ff9f43 !important;
        }

        .product-filter-select .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4b5b6b !important;
            line-height: 40px !important;
            padding-left: 10px !important;
            padding-right: 30px !important;
        }

        .product-filter-select .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            right: 8px !important;
        }

        .product-filter-dropdown .select2-search--dropdown {
            padding: 5px !important;
        }

        .product-filter-dropdown .select2-search--dropdown .select2-search__field {
            height: 30px !important;
            border: 1px solid #cfd6df !important;
            outline: none !important;
        }

        .product-filter-dropdown .select2-results__option {
            padding: 8px 10px !important;
            color: #2f3b48 !important;
            font-size: 14px !important;
        }

        .product-filter-dropdown .select2-results__option--highlighted[aria-selected],
        .product-filter-dropdown .select2-results__option[aria-selected=true] {
            background: #ff9f43 !important;
            color: #fff !important;
        }

        .product-search-input {
            position: relative;
        }

        .product-search-input i {
            position: absolute;
            top: 50%;
            left: 12px;
            color: #637381;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .product-search-input input {
            height: 40px;
            padding-left: 36px;
            border-color: #dbe0e6;
            border-radius: 5px;
            font-size: 14px;
        }

        .product-search-input input:focus {
            border-color: #ff9f43;
            box-shadow: none;
        }

        .product-page-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .product-import-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 34px;
            padding: 8px 13px;
            border-radius: 4px;
            color: #fff !important;
            background: #1b2850;
            font-size: 14px;
            font-weight: 600;
            line-height: 1;
        }

        .product-import-btn:hover,
        .product-import-btn:focus {
            color: #fff !important;
            background: #101a3b;
        }


        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
                -webkit-overflow-scrolling: touch !important;
                /* smooth scrolling on iOS */
            }

            select#filterCategory {
                font-size: 12px !important;
                /* width: 168px !important; */
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch !important;
            }

            .form-control-sm {
                min-height: calc(1.5em + .5rem + 2px);
                padding: .25rem .5rem;
                font-size: 13px !important;
                border-radius: .2rem;
            }

            .search-set {
                margin-right: 1rem !important;
            }
            .product-filter-wrap,
            .product-filter-box {
                align-items: stretch;
                flex-direction: column;
            }

            .product-filter-search,
            .product-filter-select {
                width: 100%;
                min-width: 100%;
            }

             #exportAllChallan,
            #exportPdf {
                width: 48% !important;
                flex: 0 0 48% !important;
                max-width: 48% !important;
                min-width: 48% !important;
                margin: 0 !important;
                padding: 6px 0px !important;
                font-size: 13px !important;
            }

            /* Optional: Adjust icon size */
            #exportAllChallan i,
            #exportPdf i {
                font-size: 12px !important;
                margin-right: 4px !important;
            }
        }

        /* Desktop: show all columns normally */
        @media (min-width: 769px) {

            table.datanew thead th,
            table.datanew tbody td {
                display: table-cell !important;
            }

            /* Hide the Details toggle column on desktop */
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: none !important;
            }
        }

        /* Mobile: hide non-essential columns, show Details toggle */
        @media (max-width: 768px) {

            table.datanew thead th:nth-child(n+3),
            table.datanew tbody td:nth-child(n+3) {
                display: none !important;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                vertical-align: top;
                padding-top: 25px !important;
                width: 50px;
            }

            .toggle-details i {
                font-size: 18px;
            }
        }
    </style>
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
                    alert.classList.add('hidden'); // Triggers the fade-out transition
                    // Remove the element from DOM after fadeout (optional)
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500); // match the CSS transition duration (0.5s)
                }
            }, 4000);
        </script>
    @endif

    <!-- Quantity History Modal -->
    <div class="modal fade" id="quantityHistoryModal" tabindex="-1" aria-labelledby="quantityHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quantityHistoryLabel">Quantity History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="quantityHistoryContent">
                    <!-- History content will be injected here -->
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Products</h4>
            </div>
            <div class="page-btn product-page-actions">
                <a href="{{ route('product.import') }}" class="product-import-btn">
                    <i class="fas fa-file-import"></i> Import Products
                </a>
                @if (app('hasPermission')(1, 'add'))
                    <a href="{{ route('product.add') }}" class="btn btn-sm btn-added"><img
                            src="{{ env('ImagePath') . '/admin/assets/img/icons/plus.svg' }}" alt="img"
                            class="me-1">New
                        Product</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="product-filter-wrap">
                    <div class="product-filter-box">
                        <div class="form-group product-filter-search">
                            <label for="productSearch" class="form-label mb-1">Search</label>
                            <div class="product-search-input">
                                <i class="fas fa-search"></i>
                                <input type="text" id="productSearch" class="form-control form-control-sm" placeholder="Search...">
                            </div>
                        </div>
                        <div class="form-group product-filter-select">
                            <label for="filterCategory" class="form-label mb-1">Category</label>
                            <select id="filterCategory" class="form-control form-control-sm">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group product-filter-select">
                            <label for="filterBrand" class="form-label mb-1">Brand</label>
                            <select id="filterBrand" class="form-control form-control-sm">
                                <option value="">All Brands</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 col-12 col-md-auto">
                        <button id="exportAllChallan" class="btn btn-sm btn-success col-6 col-md-auto">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                        <button id="exportPdf" class="btn btn-sm btn-danger col-6 col-md-auto">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </button>
                    </div>
                </div>


                <div class="table-scroll-top">
                    <div></div>
                </div>
                <div class="table-responsive">
                    <table class="table  datanew">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th class="details-column">Details</th>
                                <th>SKU</th>
                                <th>Category </th>
                                <th>Brand</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Quantity Type</th>
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

            $('#filterCategory, #filterBrand').select2({
                width: '100%'
            });

            // Define the function FIRST
            function fetchProducts() {
                var search = $('#productSearch').val();
                var categoryId = $('#filterCategory').val();
                var brandId = $('#filterBrand').val();
                const subBranchId = localStorage.getItem('selectedSubAdminId');
                $.ajax({
                    url: "/api/getAllProduct",
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: {
                        search: search,
                        category_id: categoryId,
                        brand_id: brandId,
                        sub_branch_id: subBranchId
                    },
                    success: function(response) {
                        if (response.status) {
                            let products = response.data;
                            let currencySymbol = response.currencySymbol || '₹';
                            let currencyPosition = response.currencyPosition || 'left';
                            let tableBody = [];

                            let lowStockThreshold = response.lowStockThreshold || 0;


                            // Function to capitalize first letter of each word
                            function capitalizeWords(str) {
                                if (!str || str.trim() === '') return 'N/A';
                                return str.replace(/\b\w/g, function(char) {
                                    return char.toUpperCase();
                                });
                            }
                            $.each(products, function(index, product) {
                                let productName = capitalizeWords(product.name || 'N/A');
                                let sku = product.SKU || 'N/A';
                                let quantity = product.quantity ?? 'N/A';
                                let categoryName = capitalizeWords(product.category ? product
                                    .category.name : "N/A");
                                let brandName = capitalizeWords(product.brand ? product
                                    .brand.name : "N/A");
                               let qtyTypeName = capitalizeWords(product.quantity_type?.quantity_type ?? "N/A");                           

                                let createdAt = capitalizeWords(product.product_type ? product
                                    .product_type
                                    .name : "N/A");
                                let detailsToggle = `
    <a href="#details-${product.id}" class="toggle-details d-md-none" data-bs-toggle="collapse">
        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
    </a>
`;

                                let productImage =
                                    '{{ env('ImagePath') . 'admin/assets/img/product/noimage.png' }}';
                                const ImagePath = "{{ env('ImagePath') . 'storage/' }}";
                                if (product.images) {
                                    let imagesArray = typeof product.images === "string" ? JSON
                                        .parse(product.images) : product.images;
                                    if (Array.isArray(imagesArray) && imagesArray.length > 0) {
                                        productImage = `${ImagePath}${imagesArray[0]}`;
                                    }
                                }

                                let formattedPrice = 'N/A';
                                if (product.price !== undefined && product.price !== null &&
                                    product.price !== '') {
                                    if (currencyPosition === 'left') {
                                        formattedPrice = currencySymbol + product.price;
                                    } else {
                                        formattedPrice = product.price + currencySymbol;
                                    }
                                }

                                let quantityDisplay =
                                    `<span class="quantity-history" data-id="${product.id}" style="cursor:pointer; border: 1px solid #ececec; padding: 4px 8px; border-radius: 4px; display: inline-block;">${quantity}</span>`;
                                if (quantity !== 'N/A' && parseInt(quantity) <=
                                    lowStockThreshold) {
                                    quantityDisplay +=
                                        ` <span class="badge bg-danger ms-1">Low Stock</span>`;
                                }

                                tableBody.push([
                                    `<div style="display: flex; align-items: center;">
                                    
                                    <a href="/product-view/${product.id}" class="product-img mb-1">
                                        <img src="${productImage}" alt="product" style="max-width: 60px;">
                                    </a>
                                    <a href="/product-view/${product.id}" style="color: #1b2850; font-weight: 500; text-align: center; margin-left: 8px;">
                                        ${productName}
                                    </a>
                                    
                                </div>
                                
                                  <!-- Collapsible Details (visible only on mobile) -->
    <div class="collapse mt-2 d-md-none" id="details-${product.id}">
        <div class=" card-body p-2 ">
            <p class="mb-1"><strong>SKU:</strong> ${sku}</p>
            <p class="mb-1"><strong>Category:</strong> ${categoryName}</p>
            <p class="mb-1"><strong>Price:</strong> ${formattedPrice}</p>
            <p class="mb-1"><strong>Quantity:</strong> ${quantityDisplay}</p>
            <p class="mb-1"><strong>Quantity Type:</strong> ${qtyTypeName}</p>
            <div class="mt-2">
                ${`@if (app('hasPermission')(1, 'view')) 
                                                                     <a class="me-3 btn btn-sm btn-primary" style="color:white; font-size: 13px;" href="/inventory-View/${product.id}">
                                                                        History    
                                                                    </a>
                                                                    @endif
                                                                    @if (app('hasPermission')(1, 'view')) 
                                                                    <a class="me-3" href="/product-view/${product.id}">
                                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
                            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
                            </svg>
                                                                    </a>
                                                                    @endif
                                                                    @if (app('hasPermission')(1, 'edit'))
                                                                    <a class="me-3" href="/edit-product/${product.id}">
                                                                        <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
                            </svg>
                                                                    </a>
                                                                    @endif
                                                                    @if (app('hasPermission')(1, 'delete'))
                                                                    <a class="confirm-text delete-product" data-id="${product.id}" href="javascript:void(0);">
                                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                                                <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                                            </svg>
                                                                    </a>
                                                                    @endif`}
            </div>
        </div>
    </div>`,
                                    detailsToggle,
                                    sku,
                                    categoryName,
                                    brandName,
                                    formattedPrice,
                                    quantityDisplay,
                                    qtyTypeName,
                                    `
                                        <div class="dropdown action-dropdown">
                                            <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #e2e8f0; background: #fff; color: #64748b; border-radius: 6px; padding: 4px 12px; transition: all 0.2s;">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="border: none; border-radius: 12px; min-width: 160px; padding: 8px;">
                                                @if (app('hasPermission')(1, 'view')) 
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="/inventory-View/${product.id}" style="border-radius: 8px; padding: 8px 12px; font-size: 14px; font-weight: 500; color: #475569; transition: all 0.2s;">
                                                        <i class="fa-solid fa-clock-rotate-left me-3" style="font-size: 14px; color: #94a3b8; width: 16px;"></i> History
                                                    </a>
                                                </li>
                                                @endif
                                                @if (app('hasPermission')(1, 'view')) 
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="/product-view/${product.id}" style="border-radius: 8px; padding: 8px 12px; font-size: 14px; font-weight: 500; color: #475569; transition: all 0.2s;">
                                                        <i class="fa-regular fa-eye me-3" style="font-size: 14px; color: #94a3b8; width: 16px;"></i> View
                                                    </a>
                                                </li>
                                                @endif
                                                @if (app('hasPermission')(1, 'edit'))
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="/edit-product/${product.id}" style="border-radius: 8px; padding: 8px 12px; font-size: 14px; font-weight: 500; color: #475569; transition: all 0.2s;">
                                                        <i class="fa-regular fa-pen-to-square me-3" style="font-size: 14px; color: #94a3b8; width: 16px;"></i> Edit
                                                    </a>
                                                </li>
                                                @endif
                                                @if (app('hasPermission')(1, 'delete'))
                                                <li>
                                                    <a class="dropdown-item confirm-text delete-product d-flex align-items-center" data-id="${product.id}" href="javascript:void(0);" style="border-radius: 8px; padding: 8px 12px; font-size: 14px; font-weight: 500; color: #ef4444; transition: all 0.2s;">
                                                        <i class="fa-regular fa-trash-can me-3" style="font-size: 14px; color: #ef4444; width: 16px;"></i> Delete
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    `
                                ]);
                            });
                            table.clear().rows.add(tableBody).draw();

                            // Sync top scrollbar
                            const topScroll = document.querySelector('.table-scroll-top');
                            const tableResponsive = document.querySelector('.table-responsive');
                            const tableElement = document.querySelector('.datanew');

                            if (topScroll && tableResponsive && tableElement) {
                                const topInnerDiv = topScroll.querySelector('div');
                                topInnerDiv.style.width = tableElement.scrollWidth + 'px';

                                topScroll.onscroll = () => {
                                    tableResponsive.scrollLeft = topScroll.scrollLeft;
                                };
                                tableResponsive.onscroll = () => {
                                    topScroll.scrollLeft = tableResponsive.scrollLeft;
                                };
                            }

                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html('<tr><td colspan="8">No products found</td></tr>');
                        }
                    },
                    error: function() {
                        alert("Error fetching products.");
                    }
                });
            }
            $(document).on('click', '.quantity-history', function() {
                const productId = $(this).data('id');

                $.ajax({
                    url: `/api/product-quantity-history/${productId}`,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(res) {
                        if (res.status) {
                            let content = `<h5>${res.product}</h5><ul class="list-group">`;
                            res.history.forEach(item => {
                                content += `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${item.type}</strong> - ${item.note}<br>
                                                <small>${item.date}</small>
                                            </div>
                                        <span class="badge bg-${item.type === 'Added' ? 'success' : 'danger'} px-4 py-2 small">${item.quantity}</span>
                                        </li>
                                    `;
                            });
                            content += `</ul>`;
                            $('#quantityHistoryContent').html(content);
                            $('#quantityHistoryModal').modal('show');
                        } else {
                            alert(res.message || "Failed to load quantity history.");
                        }
                    },
                    error: function() {
                        alert("Error fetching quantity history.");
                    }
                });
            });

            // Initial fetch
            fetchProducts();

            // Filters: bind change events AFTER function is defined
            $('#filterCategory, #filterBrand, #filterSubBranch').on('change', function() {
                fetchProducts();
            });

            let productSearchTimer = null;
            $('#productSearch').on('input', function() {
                clearTimeout(productSearchTimer);
                productSearchTimer = setTimeout(fetchProducts, 350);
            });
        })


        $(document).on('click', '.delete-product', function() {
            var productId = $(this).data('id'); // Get product ID
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
                        url: `/api/deleteProduct/${productId}`, // Adjust based on your route
                        type: 'POST',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        data: {
                            _token: $('meta[name="csrf-token"]').attr(
                                'content') // CSRF token for security
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43",
                                    confirmButtonText: "OK"
                                });

                                $(`.delete-product[data-id="${productId}"]`).closest('tr')
                                    .remove();
                            } else {
                                // Use response.error here, fallback to response.message just in case
                                Swal.fire("Error!", response.error || response.message ||
                                    "Unknown error", "error");
                            }
                        },

                        error: function(xhr) {
                            // Parse the JSON error message sent by API in HTTP error response
                            let message = "Something went wrong!";
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                message = xhr.responseJSON.error;
                            }
                            // Swal.fire("Error!", message, "error");
                            Swal.fire({
                                title: "Error!",
                                text: message,
                                icon: "error",
                                confirmButtonColor: "#ff9f43", // custom orange color
                                confirmButtonText: "OK"
                            });

                        }
                    });
                }
            });
        });

      $('#exportAllChallan').click(function () {
    let selectedSearch = $('#productSearch').val() || '';
    let selectedCategory = $('#filterCategory').val() || '';
    let selectedBrand = $('#filterBrand').val() || '';
    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
    let authToken = localStorage.getItem("authToken");

    let url = `/api/export-product?search=${encodeURIComponent(selectedSearch)}&category_id=${selectedCategory}&brand_id=${selectedBrand}&selectedSubAdminId=${selectedSubAdminId}`;

    $.ajax({
        url: url,
        method: "GET",
        headers: {
            "Authorization": "Bearer " + authToken
        },

        beforeSend: function () {
            Swal.fire({
                title: "Exporting Excel...",
                text: "Please wait while we generate your file.",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        },

        success: function (response) {
            Swal.close();

            if (response.status && response.file_url) {
                Swal.fire({
                    icon: "success",
                    title: "Excel Exported!",
                    text: "Click the button below to download your Excel file.",
                    showConfirmButton: true,
                    confirmButtonText: "Download File"
                }).then(() => {
                    let link = document.createElement('a');
                    link.href = response.file_url;
                    link.download = response.file_name || "Products.xlsx";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Export Failed!",
                    text: "Unable to export Excel file. Please try again."
                });
            }
        },

        error: function (xhr, status, error) {
            Swal.close();
            console.error("Export failed:", error);

            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Export failed. Please try again."
            });
        }
    });
});


        $('#exportPdf').click(function() {
            let selectedSearch = $('#productSearch').val() || '';
            let selectedCategory = $('#filterCategory').val() || '';
            let selectedBrand = $('#filterBrand').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url =
                `/api/export-product-pdf?search=${encodeURIComponent(selectedSearch)}&category_id=${selectedCategory}&brand_id=${selectedBrand}&selectedSubAdminId=${selectedSubAdminId}`;

            $.ajax({
                url: url,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },

                beforeSend: function() {
                    Swal.fire({
                        title: "Generating PDF...",
                        text: "Please wait",
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },

                success: function(response) {
                    Swal.close();

                    if (response.status && response.file_url) {
                        Swal.fire({
                            icon: "success",
                            title: "PDF Generated Successfully!",
                            text: "Your product PDF is ready.",
                            showConfirmButton: true,
                            confirmButtonText: "Download PDF"
                        }).then(() => {
                            let link = document.createElement('a');
                            link.href = response.file_url;
                            link.download = response.file_name || "products.pdf";
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Failed!",
                            text: "Could not generate the PDF. Please try again."
                        });
                    }
                },

                error: function(xhr, status, error) {
                    Swal.close();
                    console.error("Export PDF failed:", error);

                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Export PDF failed. Please try again."
                    });
                }
            });
        });


        $(document).on('click', '.toggle-details', function() {
            let icon = $(this).find('i');
            if (icon.hasClass('fa-plus-circle')) {
                icon.removeClass('fa-plus-circle')
                    .addClass('fa-minus-circle')
                    .css('color', 'red'); // or any color you want for minus icon
            } else {
                icon.removeClass('fa-minus-circle')
                    .addClass('fa-plus-circle')
                    .css('color', '#ff9f43'); // your desired orange color
            }
        });
    </script>
@endpush
