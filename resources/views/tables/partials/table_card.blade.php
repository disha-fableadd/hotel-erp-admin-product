@php
    $statusClass = $statusClass ?? 'blank-table';
    $statusKey = match (true) {
        str_contains($statusClass, 'running') => 'running',
        str_contains($statusClass, 'reserved') => 'reserved',
        default => 'blank',
    };
    $statusLabel = match ($statusKey) {
        'running' => 'Occupied',
        'reserved' => 'Reserved',
        default => 'Available',
    };
@endphp

<article class="table-card-wrap">
    <div class="table-box table-box123 table-card {{ $statusClass }}"
        data-table-id="{{ $table->id }}"
        data-order-id="{{ $table->order_id }}"
        data-table-name="{{ $table->table_name }}"
        data-table-type="{{ $table->table_type ?? '' }}"
        data-table-status="{{ $table->resolved_status }}">

        <div class="table-card__top">
            <span class="table-card__badge">{{ $statusLabel }}</span>
            <div class="table-card__signals">
                @if($table->has_bell ?? false)
                    <span class="table-card__signal" title="Call"><i class="fa-solid fa-bell"></i></span>
                @endif
                @if($table->has_food ?? false)
                    <span class="table-card__signal" title="Food ready"><i class="fa-solid fa-utensils"></i></span>
                @endif
            </div>
        </div>

        <div class="table-card__visual" aria-hidden="true">
            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="14" y="22" width="36" height="20" rx="6" stroke="currentColor" stroke-width="2.5"/>
                <rect x="6" y="26" width="6" height="12" rx="2" fill="currentColor" opacity="0.35"/>
                <rect x="52" y="26" width="6" height="12" rx="2" fill="currentColor" opacity="0.35"/>
                <rect x="24" y="8" width="16" height="6" rx="2" fill="currentColor" opacity="0.35"/>
                <rect x="24" y="50" width="16" height="6" rx="2" fill="currentColor" opacity="0.35"/>
            </svg>
        </div>

        <div class="table-card__name">{{ $table->table_name }}</div>
        <div class="table-card__hint">Tap to open</div>
    </div>

    <a href="javascript:void(0);" class="print-table-order table-card__print"
        data-table_no_invoice="{{ $table->id }}" title="Print / view orders">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M19 7V4a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1h-2zM7 4h10v3H7V4zm10 16H7v-4h10v4zm3-6a1 1 0 0 1-1 1h-2v-2H7v2H5a1 1 0 0 1-1-1V9h16v5z"></path>
        </svg>
    </a>
</article>
