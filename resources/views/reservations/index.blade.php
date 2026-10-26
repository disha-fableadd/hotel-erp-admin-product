@extends('layout.app')

@section('title', 'Reservations')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Table Reservations</h4>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reservations.index') }}" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="{{ request('date') }}" placeholder="Filter by date">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach(['pending','confirmed','completed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('reservations.index') }}" class="btn btn-secondary ms-1">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Table</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservations as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ $r->table->table_name ?? '—' }}</td>
                            <td>{{ $r->customer_name }}</td>
                            <td>{{ $r->customer_phone ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->reservation_date)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->reservation_time)->format('h:i A') }}</td>
                            <td>{{ $r->guests }}</td>
                            <td>
                                @php
                                    $badge = match($r->status) {
                                        'pending'   => 'warning',
                                        'confirmed' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default     => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($r->status) }}</span>
                            </td>
                            <td>
                                @if(in_array($r->status, ['pending','confirmed']))
                                    <button class="btn btn-sm btn-outline-danger btn-cancel-reservation"
                                        data-id="{{ $r->id }}"
                                        title="Cancel">
                                        Cancel
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No reservations found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $reservations->links() }}</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const token = document.querySelector('meta[name="api-token"]')?.content ?? '';

    document.querySelectorAll('.btn-cancel-reservation').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Cancel this reservation?')) return;
            const id = this.dataset.id;
            fetch(`/api/reservations/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    location.reload();
                } else {
                    alert(data.message || 'Could not cancel reservation.');
                }
            })
            .catch(() => alert('Request failed. Please try again.'));
        });
    });
});
</script>
@endsection
