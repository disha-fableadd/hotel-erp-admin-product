@extends('layout.app')
@section('title', 'Kitchen KOT')
@section('content')
<style>
    .kot-board { padding: 12px; }
    .kot-filters { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; align-items:center; }
    .kot-chip {
        border:1px solid #e2e8f0; background:#fff; border-radius:999px; padding:6px 12px;
        font-size:12px; font-weight:600; color:#475569; text-decoration:none;
    }
    .kot-chip.active { background:#ff9f43; border-color:#ff9f43; color:#fff; }
    .kot-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; }
    .kot-card {
        background:#fff; border:1px solid #e8edf3; border-radius:12px; padding:14px;
        box-shadow:0 4px 14px rgba(15,23,42,.04);
    }
    .kot-card h5 { margin:0 0 6px; font-size:15px; font-weight:700; color:#0f172a; }
    .kot-meta { font-size:12px; color:#64748b; margin-bottom:10px; }
    .kot-station {
        display:inline-block; background:#fff7ed; color:#c2410c; font-size:11px; font-weight:700;
        padding:3px 8px; border-radius:999px; margin-bottom:8px;
    }
    .kot-card ul { list-style:none; padding:0; margin:0 0 12px; }
    .kot-card li {
        display:flex; justify-content:space-between; gap:8px; padding:6px 0;
        border-bottom:1px dashed #f1f5f9; font-size:13px;
    }
    .kot-actions { display:flex; gap:8px; }
    .kot-actions .btn { font-size:12px; font-weight:600; }
</style>
<div class="content kot-board">
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="page-title"><h4>Kitchen Order Tickets (KOT)</h4></div>
        <a href="{{ route('tables.create') }}" class="btn btn-sm" style="background:#ff9f43;color:#fff;">Tables</a>
    </div>

    <div class="kot-filters">
        <a class="kot-chip {{ empty($filterStation) ? 'active' : '' }}" href="{{ route('kot.board') }}">All stations</a>
        @foreach($stations as $st)
            <a class="kot-chip {{ $filterStation === $st ? 'active' : '' }}"
               href="{{ route('kot.board', ['station' => $st]) }}">{{ $st }}</a>
        @endforeach
    </div>

    @if($tickets->isEmpty())
        <div class="alert alert-light border">No open kitchen tickets right now.</div>
    @else
        <div class="kot-grid">
            @foreach($tickets as $ticket)
                <div class="kot-card">
                    <span class="kot-station">{{ $ticket['station'] }}</span>
                    <h5>{{ $ticket['table'] }}</h5>
                    <div class="kot-meta">
                        Order #{{ $ticket['order']->order_number ?? $ticket['order']->id }}
                        · {{ optional($ticket['order']->created_at)->format('d M, h:i A') }}
                    </div>
                    <ul>
                        @foreach($ticket['items'] as $item)
                            <li>
                                <span>{{ $item->product->name ?? 'Item' }}</span>
                                <strong>× {{ $item->quantity }}</strong>
                            </li>
                        @endforeach
                    </ul>
                    <div class="kot-actions">
                        <a class="btn btn-dark btn-sm"
                           target="_blank"
                           href="{{ route('kot.print', ['orderId' => $ticket['order']->id, 'station' => $ticket['station']]) }}">
                            Print KOT
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
