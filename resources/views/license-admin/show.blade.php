@extends('license-admin.layout')

@section('title', $license->purchase_code)

@section('content')
    <div class="mb-4">
        <a href="{{ route('license-admin.dashboard') }}" class="text-decoration-none">&larr; All codes</a>
        <h2 class="mt-2"><code>{{ $license->purchase_code }}</code></h2>
        <p class="text-muted mb-0">Envato user: <strong>{{ $license->buyer }}</strong></p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card p-4">
                <h5>Edit license</h5>
                <form method="POST" action="{{ route('license-admin.update', $license) }}">
                    @csrf
                    @method('PUT')
                    @include('license-admin._form', ['license' => $license])
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card p-4 mb-3">
                <h5>Quick status</h5>
                <p class="mb-2">Current: <span class="badge badge-{{ $license->status }}">{{ $license->status }}</span></p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach (['active', 'suspended', 'revoked'] as $status)
                        @if ($status !== $license->status)
                            <form method="POST" action="{{ route('license-admin.status', $license) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Mark {{ $status }}</button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="card p-4">
                <h5>Activated domains ({{ $license->domains->count() }}/{{ $license->max_domains }})</h5>
                @if ($license->domains->isEmpty())
                    <p class="text-muted small mb-0">Not activated yet. Buyer will appear here after they use /license.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach ($license->domains as $domain)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong>{{ $domain->domain }}</strong><br>
                                    @if(!empty($domain->product_code))
                                        <span class="text-muted small">
                                            Product: <strong>{{ config('license.product_names.'.$domain->product_code, $domain->product_code) }}</strong>
                                        </span><br>
                                    @endif
                                    <span class="text-muted small">Last check: {{ $domain->last_verified_at?->diffForHumans() ?? '—' }}</span>
                                </div>
                                <form method="POST"
                                    action="{{ route('license-admin.domains.destroy', [$license, $domain]) }}"
                                    onsubmit="return confirm('Remove this domain binding?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection

