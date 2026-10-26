@extends('layout.app')

@section('title', 'Sales Report')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
    <style>
        .dataTables_info {
            float: left;
            padding-right: 15px;
            font-size: 12px;
            color: #5e5873;
            font-weight: 600;
        }

        tbody tr td:nth-child(2) {
            display: flex;
            align-items: center;
        }

        #DataTables_Table_0_info {
            float: left;
        }

        .table-scroll-top {
            display: none;
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

        /* Dropdown fixes for GST menu: closed by default, vertical list, proper alignment */
        .wordset .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            left: auto;
            flex-direction: column;
            z-index: 2000;
        }

        .wordset .dropdown-menu.show {
            display: block;
        }

        .wordset .dropdown-menu li {
            display: block;
            float: none;
        }

        .wordset .dropdown-menu .dropdown-item {
            display: block;
            white-space: nowrap;
        }

        .total_expense {
            font-weight: 600;
            color: #1b2850;
            border: 1px solid #1b2850;
        }



        @media screen and (max-width: 767px) {
            .table-scroll-top {
                display: block;
            }

            .table-top {
                flex-direction: row;
                margin-bottom: 0 !important;
            }

            .table-top .wordset {
                margin-top: 0 !important;
            }

            .dataTables_length {
                margin-left: .8rem !important;
                margin-bottom: 1rem !important;
            }

            .dataTables_filter {
                text-align: left !important;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Sales Report</h4>

            </div>
            <div class="total-expense-summary">
                <h5 id="total-expense-amount" class="mb-0 px-3 py-1 mx-3 rounded bg-light total_expense">
                    <!-- Gets updated by JS -->Total:
                </h5>
            </div>

        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between flex-wrap">
                <h6 class="fw-bold mb-0 text-secondary d-flex align-items-center">
                    <i class="fa-solid fa-chart-column me-2 text-primary"></i>
                    Top Sold Products
                </h6>

                <!-- 🔹 Summary inline with title -->
                <div id="sales-filter-summary"
                    style="display:none;
                   background: #f9fafb;
                   border: 1px solid #e5e7eb;
                   border-radius: 6px;
                   padding: 6px 10px;
                   font-size: 14px;
                   font-weight: 500;
                   color: #374151;
                   display: flex;
                   align-items: center;
                   justify-content: center;
                   gap: 6px;
                   white-space: nowrap;
                   transition: all 0.3s ease;">
                </div>
            </div>

            <div class="card-body d-flex align-items-center justify-content-center" style="height: 350px;">
                <canvas id="salesProductChart" style="max-height: 320px; width: 100%;"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-top">
                    <div class="search-set">
                        <select id="date-filter" class="form-select form-select-sm">
                            <option value="">All Time</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_6_months">Last 6 Months</option>
                            <option value="this_year">This Year</option>
                            <option value="previous_year">Previous Year</option>
                        </select>

                        <select id="month-filter" class="form-select form-select-sm"
                            style="margin-right:8px; margin-left: 8px;">
                            <option value="">All Months</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>

                        <select id="year-filter" class="form-select form-select-sm" style="margin-right:8px;">
                            <option value="">All Years</option>
                            @for ($y = date('Y'); $y >= date('Y') - 10; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>

                        <select id="vendor-filter" class="form-select form-select-sm"
                            style="margin-right:8px; min-width: 180px;">
                            <option value="">All Customers</option>
                            @isset($customers)
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            @endisset
                        </select>

                        <select id="category-filter" class="form-select form-select-sm"
                            style="margin-right:8px; min-width: 180px;">
                            <option value="">All Categories</option>
                            @isset($categories)
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            @endisset
                        </select>

                    </div>
                    <div class="wordset">
                        <ul>
                            <li>
                                <a id="generate-pdf" href="javascript:void(0);" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Download PDF">

                                    <button class="btn btn-primary btn-sm"><i class="fa-solid fa-file-pdf"></i> View
                                        PDF</button>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="table-scroll-top">
                    <div></div>
                </div>
                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Sold amount</th>
                                <th>Sold qty</th>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const ImagePath = "{{ env('ImagePath') }}";

            // 🔹 Initial Load
            fetchOrderReport();
            loadSalesProductChart();

            // 🔹 Trigger filters
            $('#date-filter, #month-filter, #year-filter, #vendor-filter, #category-filter').on('change',
                function() {
                    fetchOrderReport();
                    loadSalesProductChart();
                });

            /** ----------------------------------------------
             * 📋 Fetch Order Report Table
             * ---------------------------------------------- */
            function fetchOrderReport() {
                const filters = {
                    filter: $('#date-filter').val(),
                    month: $('#month-filter').val(),
                    year: $('#year-filter').val(),
                    customer_id: $('#vendor-filter').val(),
                    category_id: $('#category-filter').val(),
                    selectedSubAdminId: selectedSubAdminId
                };

                $.ajax({
                    url: "/api/order-report",
                    type: "GET",
                    data: filters,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        console.log(response);

                        if (!response.status) return alert("No orders found.");
                        updateSalesSummary(response);

                        let tbody = "";
                        $.each(response.data, function(index, item) {
                            tbody += `
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" name="item_ids[]" value="${item.id}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td class="productimgname">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <a href="/product-view/${item.product_id}" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                                        <img src="${ImagePath}${item.image}" alt="${item.name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                        <span style="margin-left: 10px;">${item.name}</span>
                                    </a>
                                </div>
                            </td>
                            <td>${item.SKU}</td>
                            <td>${item.category}</td>
                            <td>${item.brand ?? 'N/A'}</td>
                            <td>${item.sold_amount}</td>
                            <td>${item.sold_qty}</td>
                        </tr>
                    `;
                        });

                        let table = $('.datanew').DataTable();
                        table.destroy();
                        $(".datanew tbody").html(tbody);
                        $('.datanew').DataTable({
                            responsive: true
                        });
                    },
                    error: function() {
                        alert("Failed to load order report.");
                    }
                });
            }
            let allSelectedIds = []; // Global array to store selected IDs across pages

            // 🔹 Handle Select-All checkbox
            $(document).on('change', '#select-all', function() {
                const table = $('.datanew').DataTable();
                const isChecked = $(this).is(':checked');

                // ✅ Get all checkboxes across all pages
                table.$('input[name="item_ids[]"]').prop('checked', isChecked);

                if (isChecked) {
                    // Add all IDs from all pages
                    allSelectedIds = table.$('input[name="item_ids[]"]').map(function() {
                        return $(this).val();
                    }).get();
                } else {
                    // Clear all selections
                    allSelectedIds = [];
                }

                console.log("✅ All Selected IDs (All Pages):", allSelectedIds);
            });

            // 🔹 Handle individual checkbox changes
            $(document).on('change', 'input[name="item_ids[]"]', function() {
                const table = $('.datanew').DataTable();
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    if (!allSelectedIds.includes(id)) allSelectedIds.push(id);
                } else {
                    allSelectedIds = allSelectedIds.filter(x => x !== id);
                }

                // ✅ Update select-all checkbox based on all pages
                const totalCheckboxes = table.$('input[name="item_ids[]"]').length;
                const checkedCheckboxes = table.$('input[name="item_ids[]"]:checked').length;
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);

                console.log("🟢 Currently Selected IDs (All Pages):", allSelectedIds);
            });


            /** ----------------------------------------------
             * 📊 Load Sales Product Chart
             * ---------------------------------------------- */
            function loadSalesProductChart() {
                const filters = {
                    filter: $('#date-filter').val(),
                    month: $('#month-filter').val(),
                    year: $('#year-filter').val(),
                    customer_id: $('#vendor-filter').val(),
                    category_id: $('#category-filter').val(),
                    selectedSubAdminId: selectedSubAdminId
                };

                const chartContainer = $("#salesProductChart").parent();

                $.ajax({
                    url: "/api/order-report",
                    type: "GET",
                    data: filters,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $("#noSalesMessage").remove();
                        updateSalesSummary(response);
                        if (!response.status || !response.data.length) {
                            if (window.salesChart) window.salesChart.destroy();
                            chartContainer.append(`
                                <div id="noSalesMessage" 
                                    style="
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                height: 100%;
                                                width: 100%;
                                                color: #6b7280;
                                                font-size: 16px;
                                                text-align: center;
                                                margin-left: -1212px
                                            ">
                                    No sales found.
                                </div>
                            `);
                            return;
                        }

                        // ✅ Aggregate by product
                        const productTotals = {};
                        const productAmounts = {};
                        response.data.forEach(item => {
                            const name = item.name || "Unknown";
                            const qty = parseFloat(item.sold_qty);
                            const amount = parseFloat(item.sold_amount.replace(/[₹,]/g, "")) ||
                                0;
                            productTotals[name] = (productTotals[name] || 0) + qty;
                            productAmounts[name] = (productAmounts[name] || 0) + amount;
                        });

                        const sortedEntries = Object.entries(productTotals)
                            .sort((a, b) => b[1] - a[1])
                            .slice(0, 15);

                        const labels = sortedEntries.map(entry => entry[0]);
                        const values = sortedEntries.map(entry => entry[1]);

                        if (window.salesChart) window.salesChart.destroy();

                        const ctx = document.getElementById("salesProductChart").getContext("2d");
                        window.salesChart = new Chart(ctx, {
                            type: "bar",
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: "Total Quantity Sold",
                                    data: values,
                                    backgroundColor: [
                                        "#4e73df", "#1cc88a", "#36b9cc",
                                        "#f6c23e", "#e74a3b", "#858796",
                                        "#5a5c69", "#20c997", "#6610f2", "#fd7e14"
                                    ],
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    barThickness: 20, // 🔹 Consistent bar width
                                    maxBarThickness: 30
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label;
                                                const qty = context.formattedValue;
                                                const amt = productAmounts[label] || 0;
                                                return `${label}: ${qty} pcs | ₹${amt.toFixed(2)}`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false
                                        }
                                    },
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error("Chart load failed:", xhr);
                    }
                });
            }

            $('#generate-pdf').on('click', function() {
                if (allSelectedIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select at least one product to generate the PDF.',
                        confirmButtonColor: '#ff9f43',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const url = `/sales/report/${allSelectedIds.join(',')}`;
                window.open(url, '_blank');
            });


            function updateSalesSummary(response) {
                const customerText = $('#vendor-filter option:selected').text();
                const categoryText = $('#category-filter option:selected').text();
                const monthText = $('#month-filter option:selected').text();
                const yearText = $('#year-filter option:selected').text();
                const dateText = $('#date-filter option:selected').text();

                const hasCustomer = $('#vendor-filter').val() !== "";
                const hasCategory = $('#category-filter').val() !== "";
                const hasMonth = $('#month-filter').val() !== "";
                const hasYear = $('#year-filter').val() !== "";
                const hasDate = $('#date-filter').val() !== "";

                // ✅ Calculate total sold amount
                let totalAmount = 0;
                response.data.forEach(item => {
                    totalAmount += parseFloat(item.sold_amount.replace(/,/g, '')) || 0;
                });

                // ✅ Update total on top
                $('#total-expense-amount').html(
                    `Total: <span style="color:#ff9f43;">₹${totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>`
                );

                // Hide summary when no filters selected
                if (!hasCustomer && !hasCategory && !hasMonth && !hasYear && !hasDate) {
                    $('#sales-filter-summary').fadeOut(200);
                    return;
                }

                // Build readable parts
                let parts = [];
                if (hasCustomer) parts.push(`<strong>${customerText}</strong>`);
                if (hasCategory) parts.push(`in <strong>${categoryText}</strong>`);
                if (hasMonth || hasYear) {
                    let timeText = [];
                    if (hasMonth) timeText.push(monthText);
                    if (hasYear) timeText.push(yearText);
                    parts.push(`at <strong>${timeText.join(' ')}</strong>`);
                }
                if (hasDate && dateText !== "All Time") parts.push(`(${dateText})`);

                const message = `Showing sales for ${parts.join(' ')}`;

                // ✅ Show summary beautifully
                $('#sales-filter-summary')
                    .html(`<span>${message}</span>`)
                    .hide()
                    .fadeIn(300);
            }


        });
    </script>
@endpush
