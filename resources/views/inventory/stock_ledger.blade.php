@extends('layout.app')
@section('title', 'Stock Ledger')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Stock Ledger</h4>
            <h6 class="text-muted mb-0">Stock in/out movement history</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('inventory.list') }}" class="btn btn-added">
                <i class="fa fa-boxes-stacked me-1"></i> Inventory
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-5 col-lg-4">
                    <label class="form-label mb-1">Product</label>
                    <select name="product_id" id="ledgerProductFilter" class="form-select">
                        <option value="">All products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ (string) $productId === (string) $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-warning text-white" type="submit">
                        <i class="fa fa-filter me-1"></i> Filter
                    </button>
                </div>
                @if($productId)
                    <div class="col-auto">
                        <a href="{{ route('inventory.stock_ledger') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">When</th>
                            <th>Product</th>
                            <th>Dir</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit cost</th>
                            <th class="text-end">Balance</th>
                            <th>Type</th>
                            <th class="pe-3">Ref</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $e)
                            <tr>
                                <td class="ps-3 text-nowrap">{{ optional($e->created_at)->format('d M Y H:i') }}</td>
                                <td>{{ $e->product->name ?? ('#'.$e->product_id) }}</td>
                                <td>
                                    <span class="badge {{ $e->direction === 'in' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($e->direction) }}
                                    </span>
                                </td>
                                <td class="text-end">{{ number_format((float) $e->quantity, 4) }}</td>
                                <td class="text-end">
                                    {{ $e->unit_cost !== null ? number_format((float) $e->unit_cost, 2) : '—' }}
                                </td>
                                <td class="text-end">
                                    {{ $e->balance_after !== null ? number_format((float) $e->balance_after, 4) : '—' }}
                                </td>
                                <td>{{ $e->entry_type }}</td>
                                <td class="pe-3">
                                    {{ $e->reference_type }}
                                    @if($e->reference_id)
                                        <span class="text-muted">#{{ $e->reference_id }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <div class="mb-2"><i class="fa fa-clipboard-list fa-2x opacity-50"></i></div>
                                    No ledger entries yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($entries->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                <div class="text-muted small">
                    Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ $entries->total() }}
                </div>
                <div class="mb-0">
                    {{ $entries->onEachSide(1)->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('js')
<script>
    $(function () {
        if ($.fn.select2) {
            $('#ledgerProductFilter').select2({
                width: '100%',
                placeholder: 'All products',
                allowClear: true
            });
        }
    });
</script>
@endpush
