@php
    $statusClass = $statusClass ?? 'blank-table';
    $statusKey = match (true) {
        str_contains($statusClass, 'printed') => 'printed',
        str_contains($statusClass, 'running') => 'running',
        str_contains($statusClass, 'reserved') => 'reserved',
        default => 'blank',
    };
    $statusLabel = match ($statusKey) {
        'running' => 'Running',
        'printed' => 'Bill Printed',
        'reserved' => 'Reserved',
        default => 'Available',
    };
    $isAvailable = $statusKey === 'blank';
    $isOccupied = $statusKey === 'running';
    $isPrinted = $statusKey === 'printed';
    $showBill = ($isOccupied || $isPrinted);
    $order = optional($table->order);
    $billTotal = $showBill ? (float) $order->total_amount : null;
    $currencySymbol = $currencySymbol ?? ($currency_symbol ?? '₹');
    $currencyPosition = $currencyPosition ?? ($currency_position ?? 'left');
    $formattedBill = $billTotal !== null
        ? ($currencyPosition === 'right'
            ? number_format($billTotal, 2) . $currencySymbol
            : $currencySymbol . number_format($billTotal, 2))
        : '';
    $startedAt = $showBill
        ? ($table->order_started_at
            ?? (optional($order->created_at)->timezone('Asia/Kolkata')->toIso8601String()))
        : null;
    // Petpooja: freeze timer at bill-print time (do not keep ticking)
    $printedAt = null;
    if ($isPrinted) {
        $printedAt = $table->order_printed_at
            ?? (optional($order->bill_printed_at)->timezone('Asia/Kolkata')->toIso8601String()
                ?: null);
    }
    $elapsedLabel = '';
    if ($startedAt) {
        try {
            $start = \Carbon\Carbon::parse($startedAt)->timezone('Asia/Kolkata');
            $end = ($isPrinted && $printedAt)
                ? \Carbon\Carbon::parse($printedAt)->timezone('Asia/Kolkata')
                : ($isPrinted ? $start->copy() : now('Asia/Kolkata'));
            // Printed without printed_at: freeze at current page-load moment (JS will not tick further)
            if ($isPrinted && ! $printedAt) {
                $end = now('Asia/Kolkata');
                $printedAt = $end->toIso8601String();
            }
            $mins = max(0, (int) $start->diffInMinutes($end));
            if ($mins < 60) {
                $elapsedLabel = $mins . ' Min';
            } else {
                $h = intdiv($mins, 60);
                $m = $mins % 60;
                $elapsedLabel = $h . 'h' . ($m > 0 ? ' ' . $m . 'm' : '');
            }
        } catch (\Throwable $e) {
            $elapsedLabel = '';
        }
    }
    $customerName = $showBill
        ? trim((string) ($table->order_customer_name ?? $order->customer_name ?? ''))
        : '';
    if ($customerName === '' || strcasecmp($customerName, 'Unregistered Customer') === 0 || strcasecmp($customerName, 'Default Customer') === 0) {
        $customerName = '';
    }
    $orderNumber = $showBill ? ($table->order_number_display ?? $order->order_number ?? '') : '';
    $itemsCount = $showBill ? (int) ($table->order_items_count ?? 0) : 0;
@endphp

<article class="table-card-wrap">
    <div class="table-box table-box123 table-card {{ $statusClass }}"
        data-table-id="{{ $table->id }}"
        data-order-id="{{ $table->order_id }}"
        data-last-order-id="{{ $table->last_order_id ?? '' }}"
        data-table-name="{{ $table->table_name }}"
        data-table-type="{{ $table->table_type ?? '' }}"
        data-table-status="{{ $table->resolved_status }}"
        data-bill-total="{{ $billTotal !== null ? number_format($billTotal, 2, '.', '') : '' }}"
        data-started-at="{{ $startedAt ?? '' }}"
        data-printed-at="{{ $isPrinted ? ($printedAt ?? '') : '' }}"
        data-timer-frozen-label="{{ $isPrinted ? ($elapsedLabel ?? '') : '' }}"
        data-customer-name="{{ e($customerName) }}"
        data-order-number="{{ e($orderNumber) }}"
        data-items-count="{{ $itemsCount }}">

        <div class="table-card__accent" aria-hidden="true"></div>

        <div class="table-card__body">
            <div class="table-card__meta">
                <span class="table-card__badge">{{ $statusLabel }}</span>
                <div class="table-card__meta-right">
                    <span class="table-card__timer {{ $showBill && $elapsedLabel !== '' ? 'is-visible' : '' }} {{ $isPrinted ? 'is-frozen' : '' }}"
                          title="{{ $isPrinted ? 'Time until bill printed (stopped)' : 'Time since order started' }}">{{ $elapsedLabel !== '' ? $elapsedLabel : '—' }}</span>
                    <div class="table-card__signals">
                        @if($table->has_bell ?? false)
                            <span class="table-card__signal" title="Call"><i class="fa-solid fa-bell"></i></span>
                        @endif
                        @if($table->has_food ?? false)
                            <span class="table-card__signal" title="Food ready"><i class="fa-solid fa-utensils"></i></span>
                        @endif
                    </div>
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
                <div class="table-card__identity">
                    <div class="table-card__name">{{ $table->table_name }}</div>
                    <div class="table-card__bill {{ $showBill && $billTotal !== null ? 'is-visible' : '' }}">
                        <span class="table-card__bill-label">Total</span>
                        <strong class="table-card__bill-total">{{ $formattedBill }}</strong>
                    </div>
                    @php $hasExtra = $showBill && ($orderNumber !== '' || $itemsCount > 0 || $customerName !== ''); @endphp
                    <div class="table-card__extra {{ $hasExtra ? 'is-visible' : '' }}">
                        @if($orderNumber !== '')
                            <span class="table-card__extra-item table-card__order-no" title="Order number">#{{ $orderNumber }}</span>
                        @endif
                        @if($itemsCount > 0)
                            <span class="table-card__extra-item table-card__items-count" title="Items">{{ $itemsCount }} item{{ $itemsCount === 1 ? '' : 's' }}</span>
                        @endif
                        @if($customerName !== '')
                            <span class="table-card__extra-item table-card__customer" title="Customer">{{ \Illuminate\Support\Str::limit($customerName, 18) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card__actions {{ (!empty($table->last_order_id) && $isAvailable) || $isOccupied || $isPrinted ? 'is-multi' : 'is-single' }}">
            @if($isAvailable)
                @if(!empty($table->last_order_id) && app('hasPermission')(2, 'edit'))
                    <button type="button"
                        class="table-card__action table-card__action--reoccupy js-reoccupy-last-bill"
                        title="Put last bill back on this table (Occupied)"
                        data-order-id="{{ $table->last_order_id }}"
                        data-table-id="{{ $table->id }}"
                        data-table-name="{{ $table->table_name }}"
                        data-open-dine="0">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Re-occupy</span>
                    </button>
                    <button type="button"
                        class="table-card__action table-card__action--update-last js-reoccupy-last-bill"
                        title="Re-occupy last bill and continue add/edit in dine-in"
                        data-order-id="{{ $table->last_order_id }}"
                        data-table-id="{{ $table->id }}"
                        data-table-name="{{ $table->table_name }}"
                        data-open-dine="1">
                        <i class="fa-solid fa-utensils"></i>
                        <span>Continue</span>
                    </button>
                    <button type="button"
                        class="table-card__action table-card__action--reserve table-card__action--ghost js-reserve-table"
                        title="Reserve this table"
                        data-table-id="{{ $table->id }}"
                        data-table-name="{{ $table->table_name }}">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span>Reserve</span>
                    </button>
                @else
                    <button type="button"
                        class="table-card__action table-card__action--reserve js-reserve-table"
                        title="Reserve this table"
                        data-table-id="{{ $table->id }}"
                        data-table-name="{{ $table->table_name }}">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span>Reserve</span>
                    </button>
                @endif
            @elseif($isOccupied || $isPrinted)
                @if(app('hasPermission')(2, 'edit') && !empty($table->order_id))
                    <button type="button"
                        class="table-card__action table-card__action--update-last js-open-dine-bill"
                        title="Continue add/edit items in dine-in"
                        data-order-id="{{ $table->order_id }}"
                        data-table-id="{{ $table->id }}"
                        data-table-name="{{ $table->table_name }}">
                        <i class="fa-solid fa-utensils"></i>
                        <span>Continue</span>
                    </button>
                    <button type="button"
                        class="table-card__action table-card__action--clear-bill js-clear-dine-bill"
                        title="Clear current bill"
                        data-order-id="{{ $table->order_id }}"
                        data-table-id="{{ $table->id }}"
                        data-table-name="{{ $table->table_name }}">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Clear</span>
                    </button>
                @endif
                <a href="javascript:void(0);"
                    class="print-table-order table-card__action table-card__action--print"
                    data-table_no_invoice="{{ $table->id }}"
                    data-order-id="{{ $table->order_id ?? '' }}"
                    data-table-id="{{ $table->id }}"
                    title="Print bill">
                    <i class="fa-solid fa-print"></i>
                    <span>Print</span>
                </a>
            @else
                <span class="table-card__action-spacer"></span>
            @endif
        </div>
    </div>
</article>
