@extends('layout.app')

@section('title', 'Inventory List')

@section('content')
    <style>
        .sorting_1 {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        .table-scroll-top {
            overflow-x: auto;
            overflow-y: hidden;
            height: 20px;
            width: 100%;
            margin-bottom: 5px;
            display: none;
        }

        /* ✅ Hide "Details" column on desktop, show only on mobile */
        th.details-col,
        td.details-col {
            display: none !important;
        }

        @media screen and (max-width: 768px) {

            /* Hide all except Type and Details */
            .table thead th:not(:first-child):not(.details-col),
            .table tbody td:not(:first-child):not(.details-col) {
                display: none !important;
            }

            th.details-col,
            td.details-col {
                display: table-cell !important;
                vertical-align: middle !important;
            }

            .toggle-details {
                text-decoration: none;
                font-size: 18px;
                display: inline-block !important;
                visibility: visible !important;
            }
        }


        .modal-content {
            width: 80% !important;
        }
    </style>


    @if (session('error'))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') }}
        </div>
    @endif

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>View Inventory</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-scroll-top">
                    <div></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <!-- <th>Initial Stock</th> -->
                                <th>Current Stock</th>
                                <th>Quantity</th>
                                <th>Create By</th>
                                <th>Date</th>
                                <th class="details-col">Details</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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

            // Get current product ID from URL
            let url = window.location.href;
            let id = url.substring(url.lastIndexOf('/') + 1);

            // ✅ Initialize DataTable (no columns config)
            var table = $('.table').DataTable({
                responsive: true,
                searching: true,
                paging: true,
                info: true,
                autoWidth: false,
                ordering: false,
                createdRow: function(row, data, dataIndex) {
                    // Add details-col class to the last cell (Details column)
                    $('td:last', row).addClass('details-col');
                }
            });


            // ✅ Fetch Inventory History
            function fetchinventoryHistory(productId) {
                $.ajax({
                    url: "/api/getQuantityHistory_inventory/" + productId,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                            if (response.status) {
                            let history = response.history || [];
                            let tableBody = [];

                            $.each(history, function(index, item) {
                                let initialStock = item.initial_stock ?? '-';
                                let currentStock = item.current_stock ?? '-';
                                let type = item.type ?? 'System';
                                let createdBy = item.name ?? 'System';

                                // Parse numeric initial/current if present
                                let numInitial = (item.initial_stock !== undefined && item.initial_stock !== null && item.initial_stock !== '-') ? Number(item.initial_stock) : NaN;
                                let numCurrent = (item.current_stock !== undefined && item.current_stock !== null && item.current_stock !== '-') ? Number(item.current_stock) : NaN;

                                // Compute quantity: prefer difference between initial and current when both are numbers
                                let qtyNum = 0;
                                if (!isNaN(numInitial) && !isNaN(numCurrent)) {
                                    qtyNum = Math.abs(numCurrent - numInitial);
                                } else {
                                    // Fallback to explicit quantity fields if difference isn't available
                                    let rawQty = item.quantity ?? item.qty ?? item.change ?? item.qty_change ?? 0;
                                    qtyNum = Math.abs(Number(rawQty)) || 0;
                                }

                                // Decide sign based on type (sale => -, purchase => +). If type unknown, infer from initial/current.
                                let signedQty = '';
                                let typeLower = String(type).toLowerCase();
                                if (typeLower.includes('sale')) {
                                    signedQty = '-' + qtyNum;
                                } else if (typeLower.includes('purchase')) {
                                    signedQty = '+' + qtyNum;
                                } else if (!isNaN(numInitial) && !isNaN(numCurrent)) {
                                    if (numCurrent > numInitial) signedQty = '+' + qtyNum;
                                    else if (numCurrent < numInitial) signedQty = '-' + qtyNum;
                                    else signedQty = '+' + qtyNum;
                                } else {
                                    signedQty = '+' + qtyNum;
                                }
                                let date = item.date ?? '-';

                                // Unique collapse ID
                                let detailsId = `details-${index}`;

                                // + Icon toggle (visible only on mobile)
                                let detailsToggle = `
                                <a href="#${detailsId}" class="toggle-details d-md-none" data-bs-toggle="collapse">
                                    <i class="fas fa-plus-circle" style="color:#ff9f43;"></i>
                                </a>
                            `;

                                // Collapsed details content (mobile)
                                let detailsRow = `
                                <div class="collapse mt-2" id="${detailsId}">
                                    <div><strong>Quantity:</strong> ${signedQty}</div>
                                    <div><strong>Initial Stock:</strong> ${initialStock}</div>
                                    <div><strong>Current Stock:</strong> ${currentStock}</div>
                                    <div><strong>Create By:</strong> ${createdBy}</div>
                                    <div><strong>Date:</strong> ${date}</div>
                                </div>
                            `;

                                // Push each row (Quantity column added after Type)
                                tableBody.push([
                                    type,
                                    // initialStock,
                                    currentStock,
                                    signedQty,
                                    createdBy,
                                    date,
                                    `${detailsToggle} ${detailsRow}`
                                ]);

                            });

                            table.clear().rows.add(tableBody).draw();
                        } else {
                            table.clear().draw();
                            $('.table tbody').html(
                                '<tr><td colspan="7" class="text-center">No inventory history found</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        alert("Error fetching inventory history.");
                    }
                });
            }

            // Load data
            fetchinventoryHistory(id);

            // ✅ Toggle + / − icon color on click
            $(document).on('click', '.toggle-details', function() {
                let icon = $(this).find('i');
                if (icon.hasClass('fa-plus-circle')) {
                    icon.removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color', '#dc3545');
                } else {
                    icon.removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color', '#ff9f43');
                }
            });
        });
    </script>
@endpush
