@extends('layout.app')

@section('title', 'Income Statement')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Income Statement</h3>
                </div>

            </div>
        </div>

        <!-- Search Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('income-statement.index') }}" method="GET">
                    <div class="row g-3 align-items-end">

                        <!-- Start Date -->
                        <div class="col-md-3 col-sm-6 col-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control"
                                value="{{ request('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>

                        <!-- End Date -->
                        <div class="col-md-3 col-sm-6 col-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control"
                                value="{{ request('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')) }}">
                        </div>

                        <!-- Filter Button -->
                        <div class="col-md-2 col-sm-6 d-grid">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>

                        <!-- Download PDF Button -->
                        <div class="col-md-2 col-sm-6 col-6 d-grid">
                            <a href="{{ route('income-statement.pdf', request()->only(['start_date', 'end_date'])) }}" 
                            class="btn btn-danger w-100">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                        </div>
                        <!-- Download Excel Button -->
                        <div class="col-md-2 col-sm-6 col-6 d-grid">
                            <a href="{{ route('income-statement.excel',request()->only(['start_date', 'end_date'])) }}" class="btn btn-success w-100">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>


        <!-- Income Statement -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table">
                                <thead>
                                    <tr>
                                        <th class="text-left" colspan="2">
                                            For the Period:
                                            {{ \Carbon\Carbon::parse($data['period']['start'])->format('M d, Y') }}
                                            to {{ \Carbon\Carbon::parse($data['period']['end'])->format('M d, Y') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Revenue Section -->
                                    <tr class="table" style="background: #ff9f43 !important; color:white;">
                                        <td colspan="2"><strong style="color:white;">Revenue</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Sales in Cash</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['revenue']['sales_cash'], 2) }}
                                            @else
                                                {{ number_format($data['revenue']['sales_cash'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Sales in Online</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['revenue']['sales_online'], 2) }}
                                            @else
                                                {{ number_format($data['revenue']['sales_online'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Sales Revenue</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['revenue']['sales'], 2) }}
                                            @else
                                                {{ number_format($data['revenue']['sales'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Total Revenue</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['revenue']['total_revenue'], 2) }}
                                            @else
                                                {{ number_format($data['revenue']['total_revenue'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Cost of Goods Sold -->
                                    <tr class="table-info">
                                        <td colspan="2"><strong>Cost of Goods Sold</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Purchases in Cash</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['cost_of_goods_sold']['purchase_cash'], 2) }}
                                            @else
                                                {{ number_format($data['cost_of_goods_sold']['purchases'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Purchases in Online</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['cost_of_goods_sold']['purchase_online'], 2) }}
                                            @else
                                                {{ number_format($data['cost_of_goods_sold']['purchases'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Purchases</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['cost_of_goods_sold']['purchases'], 2) }}
                                            @else
                                                {{ number_format($data['cost_of_goods_sold']['purchases'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Total Cost of Goods Sold</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['cost_of_goods_sold']['total_cogs'], 2) }}
                                            @else
                                                {{ number_format($data['cost_of_goods_sold']['total_cogs'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Gross Profit -->
                                    <tr class="table-success font-weight-bold">
                                        <td>Gross Profit</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['gross_profit'], 2) }}
                                            @else
                                                {{ number_format($data['gross_profit'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Operating Expenses -->
                                    <tr class="table-info">
                                        <td colspan="2"><strong>Operating Expenses</strong></td>
                                    </tr>
                                    <tr>
                                        <td>General Expenses</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['operating_expenses']['general_expenses'], 2) }}
                                            @else
                                                {{ number_format($data['operating_expenses']['general_expenses'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Total Operating Expenses</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['operating_expenses']['total_operating_expenses'], 2) }}
                                            @else
                                                {{ number_format($data['operating_expenses']['total_operating_expenses'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Operating Income -->
                                    <tr class="table-primary font-weight-bold">
                                        <td>Operating Income (EBIT)</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['operating_income'], 2) }}
                                            @else
                                                {{ number_format($data['operating_income'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <script>
        $(document).ready(function() {
            const authToken = localStorage.getItem("authToken");
            var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            // Dynamically get start and end of the current month
            let now = new Date();
            let startDate = new Date(now.getFullYear(), now.getMonth(), 1); // first day of month
            let endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0); // last day of month

            // Format dates as YYYY-MM-DD
            const formatDate = (date) => {
                let month = (date.getMonth() + 1).toString().padStart(2, '0');
                let day = date.getDate().toString().padStart(2, '0');
                return `${date.getFullYear()}-${month}-${day}`;
            };

            startDate = formatDate(startDate);
            endDate = formatDate(endDate);

            // Make the API call immediately
            $.ajax({
                url: '/api/income-statement', // Your API route
                method: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    selectedSubAdminId: selectedSubAdminId,
                },
                headers: {
                    'Authorization': 'Bearer ' + authToken // if using auth
                },
                success: function(response) {
                    // Data is rendered server-side; API call retained for future client-side use
                },
                error: function(xhr, status, error) {
                    // Error logging removed for security
                }
            });
        });
    </script>
@endpush
