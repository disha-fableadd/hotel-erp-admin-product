@extends('license-admin.layout')

@section('title', 'Purchase codes')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Purchase codes</h2>
            <p class="text-muted mb-0 small">Buyers activate on their ERP using code + Envato username.</p>
        </div>
        <a href="{{ route('license-admin.create') }}" class="btn btn-primary">+ Register new code</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3">
                <div class="text-muted small">Total codes</div>
                <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="text-muted small">Active</div>
                <div class="fs-4 fw-bold text-success">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="text-muted small">Domain activations</div>
                <div class="fs-4 fw-bold">{{ $stats['activations'] }}</div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Purchase code or Envato username"
                    value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach (['active', 'revoked', 'suspended'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Purchase code</th>
                        <th>Envato user</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Domains used</th>
                        <th>Connected domains</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($licenses as $license)
                        <tr>
                            <td><code>{{ $license->purchase_code }}</code></td>
                            <td>{{ $license->buyer }}</td>
                            <td>{{ $license->license_type }} <span class="text-muted">(max {{ $license->max_domains }})</span></td>
                            <td>
                                <span class="badge badge-{{ $license->status }}">{{ $license->status }}</span>
                            </td>
                            <td>{{ $license->domains->count() }} / {{ $license->max_domains }}</td>
                            <td class="small text-muted">
                                @if ($license->domains->isEmpty())
                                    —
                                @else
                                    {{ $license->domains->pluck('domain')->implode(', ') }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('license-admin.show', $license) }}" class="btn btn-sm btn-outline-primary">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No codes yet.
                                <a href="{{ route('license-admin.create') }}">Register the first one</a>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($licenses->hasPages())
            <div class="p-3">{{ $licenses->links() }}</div>
        @endif
    </div>
@endsection

