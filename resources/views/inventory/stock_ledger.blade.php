@extends('layout.app')
@section('title', 'Stock Ledger')
@section('content')
<div class="content">
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="page-title"><h4>Stock Ledger</h4></div>
        <a href="{{ route('inventory.list') }}" class="btn btn-sm btn-outline-secondary">Inventory</a>
    </div>
    <p class="text-muted small mb-3">
        Movement history (in/out). On-hand quantity still lives on <code>products.quantity</code>; this ledger is the audit trail.
    </p>

    <form method="get" class="row g-2 mb-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select">
                <option value="">All products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ (string)$productId === (string)$p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning text-white" type="submit">Filter</button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Product</th>
                        <th>Dir</th>
                        <th>Qty</th>
                        <th>Unit cost</th>
                        <th>Balance</th>
                        <th>Type</th>
                        <th>Ref</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $e)
                        <tr>
                            <td>{{ optional($e->created_at)->format('d M Y H:i') }}</td>
                            <td>{{ $e->product->name ?? ('#'.$e->product_id) }}</td>
                            <td>
                                <span class="badge {{ $e->direction === 'in' ? 'bg-success' : 'bg-danger' }}">
                                    {{ strtoupper($e->direction) }}
                                </span>
                            </td>
                            <td>{{ $e->quantity }}</td>
                            <td>{{ $e->unit_cost !== null ? number_format((float)$e->unit_cost, 2) : '—' }}</td>
                            <td>{{ $e->balance_after !== null ? $e->balance_after : '—' }}</td>
                            <td>{{ $e->entry_type }}</td>
                            <td>{{ $e->reference_type }} {{ $e->reference_id ? '#'.$e->reference_id : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No ledger entries yet. Run migrations and post a purchase/sale.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $entries->links() }}</div>
    </div>
</div>
@endsection
