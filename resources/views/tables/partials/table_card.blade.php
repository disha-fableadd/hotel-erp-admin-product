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
    $isAvailable = $statusKey === 'blank';
    $isOccupied = $statusKey === 'running';
    $billTotal = $isOccupied ? (float) optional($table->order)->total_amount : null;
    $currencySymbol = $currencySymbol ?? ($currency_symbol ?? '₹');
    $currencyPosition = $currencyPosition ?? ($currency_position ?? 'left');
    $formattedBill = $billTotal !== null
        ? ($currencyPosition === 'right'
            ? number_format($billTotal, 2) . $currencySymbol
            : $currencySymbol . number_format($billTotal, 2))
        : '';
@endphp

<article class="table-card-wrap">
    <div class="table-box table-box123 table-card {{ $statusClass }}"
        data-table-id="{{ $table->id }}"
        data-order-id="{{ $table->order_id }}"
        data-table-name="{{ $table->table_name }}"
        data-table-type="{{ $table->table_type ?? '' }}"
        data-table-status="{{ $table->resolved_status }}"
        data-bill-total="{{ $billTotal !== null ? number_format($billTotal, 2, '.', '') : '' }}">

        <div class="table-card__accent" aria-hidden="true"></div>

        <div class="table-card__body">
            <div class="table-card__meta">
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

            <div class="table-card__center">
                <div class="table-card__visual" aria-hidden="true">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="16" y="24" width="32" height="16" rx="5" stroke="currentColor" stroke-width="2.2"/>
                        <rect x="8" y="28" width="5" height="8" rx="1.5" fill="currentColor" opacity="0.28"/>
                        <rect x="51" y="28" width="5" height="8" rx="1.5" fill="currentColor" opacity="0.28"/>
                        <rect x="26" y="12" width="12" height="5" rx="1.5" fill="currentColor" opacity="0.28"/>
                        <rect x="26" y="47" width="12" height="5" rx="1.5" fill="currentColor" opacity="0.28"/>
                    </svg>
                </div>
                <div class="table-card__name">{{ $table->table_name }}</div>
                <div class="table-card__bill {{ $isOccupied && $billTotal !== null ? 'is-visible' : '' }}">
                    <span class="table-card__bill-label">Total</span>
                    <strong class="table-card__bill-total">{{ $formattedBill }}</strong>
                </div>
            </div>
        </div>

        <div class="table-card__actions" onclick="event.stopPropagation();">
            @if($isAvailable)
                <button type="button"
                    class="table-card__action table-card__action--reserve js-reserve-table"
                    title="Reserve this table"
                    data-table-id="{{ $table->id }}"
                    data-table-name="{{ $table->table_name }}">
                    <i class="fa-regular fa-calendar-check"></i>
                    <span>Reserve</span>
                </button>
            @elseif($isOccupied)
                <a href="javascript:void(0);"
                    class="print-table-order table-card__action table-card__action--print table-card__action--print-full"
                    data-table_no_invoice="{{ $table->id }}"
                    title="Print / view orders">
                    <i class="fa-solid fa-print"></i>
                    <span>Print</span>
                </a>
            @else
                <span class="table-card__action-spacer"></span>
            @endif
        </div>
    </div>
</article>
