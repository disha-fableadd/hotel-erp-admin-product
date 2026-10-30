@extends('layout.app')
@section('title', 'Saved hotel purchase bill')
@section('content')
@php($review = $bill->reviewed_data)
<div class="content">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <div><h4>Bill {{ $review['header']['bill_no'] }}</h4><p>Saved purchase, original document and all reviewed fields.</p></div>
        <div class="d-flex gap-2">
            <a class="btn btn-primary" href="{{ route('purchase.ai-bill.original', $bill->id) }}">Download original bill</a>
            <a class="btn btn-secondary" href="{{ route('purchase.print', $bill->purchase_invoice_id) }}">Purchase invoice</a>
            <a class="btn btn-light" href="{{ route('purchase.ai-bill') }}">Upload another</a>
        </div>
    </div>
    <div class="card"><div class="card-body">
        <h5>Bill details</h5>
        <dl class="row">
            @foreach ($review['header'] as $label => $value)
                @if ($label !== 'extra_fields')
                    <dt class="col-sm-4">{{ \App\Support\BillFieldLabel::format($label) }}</dt>
                    <dd class="col-sm-8" style="white-space:pre-wrap;overflow-wrap:anywhere">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $value }}</dd>
                @endif
            @endforeach
            @foreach ($review['header']['extra_fields'] ?? [] as $label => $value)
                <dt class="col-sm-4">{{ \App\Support\BillFieldLabel::format($label) }}</dt>
                <dd class="col-sm-8" style="white-space:pre-wrap;overflow-wrap:anywhere">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $value }}</dd>
            @endforeach
        </dl>
    </div></div>
    @foreach ($review['items'] as $item)
        <div class="card"><div class="card-body">
            <h5>{{ $item['name'] }}</h5>
            <p>{{ $item['quantity'] }} {{ $item['unit'] }} · Line amount: {{ number_format($item['amount'], 2) }}</p>
            <p>{{ $item['track_stock'] ? 'Stock updated' : 'Service / non-stock entry' }}: {{ $item['stock_before'] }} → {{ $item['stock_after'] }} {{ $item['unit'] }} · Product #{{ $item['product_id'] }}</p>
            <dl class="row">
                @foreach ($item as $label => $value)
                    @if (!in_array($label, ['extra_fields', 'name', 'stock_before', 'stock_after']))
                        <dt class="col-sm-4">{{ \App\Support\BillFieldLabel::format($label) }}</dt>
                        <dd class="col-sm-8">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</dd>
                    @endif
                @endforeach
                @foreach ($item['extra_fields'] ?? [] as $label => $value)
                    <dt class="col-sm-4">{{ \App\Support\BillFieldLabel::format($label) }}</dt>
                    <dd class="col-sm-8" style="white-space:pre-wrap;overflow-wrap:anywhere">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $value }}</dd>
                @endforeach
            </dl>
        </div></div>
    @endforeach
    <details class="card"><summary class="card-body">Original AI extraction (before review)</summary>
        <pre class="card-body" style="white-space:pre-wrap">{{ json_encode($bill->extraction, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
    </details>
</div>
@endsection
