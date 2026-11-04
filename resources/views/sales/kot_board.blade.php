@extends('layout.app')
@section('title', 'Kitchen KOT')
@section('content')
<style>
    .kot-board { padding: 12px; }
    .kot-filters {
        display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; align-items: center;
    }
    .kot-filter-label {
        font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;
        letter-spacing: .04em; margin-right: 2px;
    }
    .kot-chip {
        border: 1px solid #e2e8f0; background: #fff; border-radius: 999px; padding: 6px 12px;
        font-size: 12px; font-weight: 600; color: #475569; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .kot-chip:hover { border-color: #ff9f43; color: #c2410c; }
    .kot-chip.active { background: #ff9f43; border-color: #ff9f43; color: #fff; }
    .kot-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
    .kot-card {
        background: #fff; border: 1px solid #e8edf3; border-radius: 12px; padding: 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .04); border-top: 3px solid #cbd5e1;
        position: relative;
    }
    .kot-card.svc-dine_in { border-top-color: #ff9f43; }
    .kot-card.svc-takeaway { border-top-color: #3b82f6; }
    .kot-card.svc-self_pickup { border-top-color: #8b5cf6; }
    .kot-card.svc-delivery { border-top-color: #10b981; }
    .kot-badges { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
    .kot-station {
        display: inline-block; background: #fff7ed; color: #c2410c; font-size: 11px; font-weight: 700;
        padding: 3px 8px; border-radius: 999px;
    }
    .kot-service {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 999px;
    }
    .kot-service.svc-dine_in { background: #fff7ed; color: #c2410c; }
    .kot-service.svc-takeaway { background: #eff6ff; color: #1d4ed8; }
    .kot-service.svc-self_pickup { background: #f5f3ff; color: #6d28d9; }
    .kot-service.svc-delivery { background: #ecfdf5; color: #047857; }
    .kot-card h5 { margin: 0 0 6px; font-size: 16px; font-weight: 800; color: #0f172a; }
    .kot-meta { font-size: 12px; color: #64748b; margin-bottom: 10px; }
    .kot-card ul { list-style: none; padding: 0; margin: 0 0 12px; }
    .kot-card li {
        display: flex; justify-content: space-between; gap: 8px; padding: 6px 0;
        border-bottom: 1px dashed #f1f5f9; font-size: 13px;
    }
    .kot-item-extra { display: block; font-size: 11px; color: #94a3b8; font-weight: 500; }
    .kot-actions { display: flex; gap: 8px; }
    .kot-actions .btn { font-size: 12px; font-weight: 600; }
    .kot-empty { background: #fff; border: 1px dashed #e2e8f0; border-radius: 12px; padding: 28px; text-align: center; color: #64748b; }
</style>
<div class="content kot-board">
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="page-title">
            <h4>Kitchen Order Tickets (KOT)</h4>
        </div>
        <a href="{{ route('tables.create') }}" class="btn btn-sm" style="background:#ff9f43;color:#fff;">Tables</a>
    </div>

    {{-- Order type filters --}}
    <div class="kot-filters">
        <span class="kot-filter-label">Type</span>
        <a class="kot-chip {{ empty($filterType) ? 'active' : '' }}"
           href="{{ route('kot.board', array_filter(['station' => $filterStation])) }}">All</a>
        @foreach($orderTypeOptions as $key => $label)
            <a class="kot-chip {{ ($filterType ?? '') === $key ? 'active' : '' }}"
               href="{{ route('kot.board', array_filter(['order_type' => $key, 'station' => $filterStation])) }}">
                @if($key === 'dine_in') <i class="fa fa-utensils"></i>
                @elseif($key === 'takeaway') <i class="fa fa-bag-shopping"></i>
                @elseif($key === 'self_pickup') <i class="fa fa-hand-holding"></i>
                @elseif($key === 'delivery') <i class="fa fa-motorcycle"></i>
                @endif
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Kitchen station filters --}}
    <div class="kot-filters">
        <span class="kot-filter-label">Station</span>
        <a class="kot-chip {{ empty($filterStation) ? 'active' : '' }}"
           href="{{ route('kot.board', array_filter(['order_type' => $filterType])) }}">All stations</a>
        @foreach($stations as $st)
            <a class="kot-chip {{ $filterStation === $st ? 'active' : '' }}"
               href="{{ route('kot.board', array_filter(['station' => $st, 'order_type' => $filterType])) }}">{{ $st }}</a>
        @endforeach
    </div>

    @if($tickets->isEmpty())
        <div class="kot-empty">No open kitchen tickets right now.</div>
    @else
        <div class="kot-grid">
            @foreach($tickets as $ticket)
                @php
                    $svcKey = $ticket['service_key'] ?? 'dine_in';
                    $svcLabel = $ticket['service_label'] ?? 'Dine In';
                    $title = $ticket['title'] ?? ($ticket['table'] ?? 'Order');
                @endphp
                <div class="kot-card svc-{{ $svcKey }}">
                    <div class="kot-badges">
                        <span class="kot-service svc-{{ $svcKey }}">{{ $svcLabel }}</span>
                        <span class="kot-station">{{ $ticket['station'] }}</span>
                    </div>
                    <h5>{{ $title }}</h5>
                    <div class="kot-meta">
                        Order #{{ $ticket['order']->order_number ?? $ticket['order']->id }}
                        · {{ optional($ticket['order']->created_at)->format('d M, h:i A') }}
                    </div>
                    <ul>
                        @foreach($ticket['items'] as $item)
                            @php
                                $vName = method_exists($item, 'variantLabel') ? $item->variantLabel() : optional($item->variant)->name;
                                $aLines = method_exists($item, 'addonLines') ? $item->addonLines() : [];
                            @endphp
                            <li>
                                <span>
                                    {{ $item->product->name ?? 'Item' }}
                                    @if($vName)
                                        <span class="kot-item-extra">{{ $vName }}</span>
                                    @endif
                                    @if(!empty($aLines))
                                        <span class="kot-item-extra">+ {{ implode(', ', $aLines) }}</span>
                                    @endif
                                </span>
                                <strong>× {{ $item->quantity }}</strong>
                            </li>
                        @endforeach
                    </ul>
                    <div class="kot-actions">
                        <button type="button"
                                class="btn btn-dark btn-sm js-kot-board-print"
                                data-order-id="{{ $ticket['order']->id }}"
                                data-station="{{ $ticket['station'] }}">
                            <i class="fas fa-print me-1"></i> Print KOT
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@include('partials.kot-print-modal')
@endsection

@push('js')
@include('partials.invoice-qz-print-script')
@include('partials.kot-print-modal-script')
<script>
(function ($) {
    $(document).on('click', '.js-kot-board-print', function () {
        var orderId = $(this).data('order-id');
        var station = $(this).data('station') || '';
        if (!orderId) return;
        if (typeof window.showKotPrintModal === 'function') {
            // Kitchen board: same-page print modal (full ticket for this station)
            window.showKotPrintModal(orderId, {
                delta: false,
                station: station,
                reloadOnClose: false,
                skipIfEmpty: false
            });
            return;
        }
        var url = '/kitchen/kot/' + orderId + '/print';
        if (station) url += '?station=' + encodeURIComponent(station);
        window.open(url, '_blank');
    });
})(jQuery);
</script>
@endpush
