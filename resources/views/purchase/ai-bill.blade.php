@extends('layout.app')

@section('title', 'AI Bill Upload')

@section('content')
    <style>
        .ai-bill-page { max-width: 1180px; }
        .ai-bill-hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }
        .ai-bill-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff4e8;
            color: #c56a12;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .ai-bill-hero h4 { margin: 8px 0 4px; }
        .ai-bill-hero p { margin: 0; color: #6b7280; max-width: 640px; }
        .ai-bill-hero .ai-back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-width: auto;
            padding: 6px 12px;
            font-size: 13px;
            line-height: 20px;
            flex-shrink: 0;
        }
        .ai-dropzone {
            border: 2px dashed #ffc48a;
            background: linear-gradient(180deg, #fffaf4 0%, #ffffff 100%);
            border-radius: 16px;
            padding: 36px 20px;
            text-align: center;
            cursor: pointer;
            transition: .2s ease;
        }
        .ai-dropzone.is-dragover,
        .ai-dropzone:hover { border-color: #ff9f43; background: #fff7ee; }
        .ai-dropzone input { display: none; }
        .ai-drop-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 12px;
            border-radius: 16px;
            background: #ff9f43;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .ai-preview {
            display: none;
            margin-top: 16px;
            border-radius: 12px;
            overflow: hidden;
            max-height: 280px;
            border: 1px solid #eee;
        }
        .ai-preview img { width: 100%; height: 280px; object-fit: contain; background: #111827; }
        .ai-review { display: none; }
        .ai-card {
            border: 1px solid #f0e6d8;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(255, 159, 67, .06);
        }
        .ai-card .card-body { padding: 18px; }
        .ai-section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #8a6a4a;
            margin-bottom: 14px;
        }
        .ai-item-row {
            border: 1px solid #f3e7d8;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 12px;
            background: #fff;
        }
        .ai-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
        }
        .ai-badge-update { background: #e8f8ef; color: #18794e; }
        .ai-badge-create { background: #fff3d6; color: #9a6700; }
        .ai-totals {
            display: flex;
            justify-content: flex-end;
            gap: 24px;
            flex-wrap: wrap;
            padding: 12px 4px 0;
            font-weight: 600;
        }
        .ai-totals span { color: #ff9f43; }
        .ai-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; margin-top: 16px; }
        .btn-ai-primary {
            background: #ff9f43;
            border-color: #ff9f43;
            color: #fff;
            font-weight: 600;
        }
        .btn-ai-primary:hover { background: #f08f2e; color: #fff; }
        .ai-empty-note { color: #6b7280; font-size: 13px; }
        @media (max-width: 767px) {
            .ai-bill-hero { flex-direction: column; }
        }
    </style>

    <div class="content ai-bill-page">
        <div class="ai-bill-hero">
            <div>
                <div class="ai-bill-kicker"><i class="fas fa-magic"></i> Purchase AI</div>
                <h4>Upload bill & review stock</h4>
                <p>Upload hotel supplies, kitchen purchases, housekeeping, linen, equipment or service bills. Review every extracted detail before saving products and stock.</p>
            </div>
            <a href="{{ route('purchase.lists') }}" class="btn btn-sm btn-cancel ai-back-btn">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>

        @if (! $aiReady)
            <div class="alert alert-warning">
                Add <code>GEMINI_API_KEY</code> or <code>OPENAI_API_KEY</code> to <code>.env</code>, then run
                <code>php artisan config:clear</code>. Without a vision key the bill image cannot be read.
            </div>
        @endif

        <div class="card ai-card mb-3" id="aiUploadCard">
            <div class="card-body">
                <div class="ai-section-title">1. Bill image</div>
                <label class="ai-dropzone" id="aiDropzone" for="aiBillFile">
                    <input type="file" id="aiBillFile" accept="image/jpeg,image/png,image/webp">
                    <div class="ai-drop-icon"><i class="fas fa-file-invoice"></i></div>
                    <h5 class="mb-1">Drop bill photo here</h5>
                    <p class="mb-0 ai-empty-note">JPG, PNG or WEBP · up to 10 MB · keep the item table in frame</p>
                    <div id="aiFileName" class="mt-2 fw-semibold text-dark"></div>
                </label>
                <div class="ai-preview" id="aiPreview"><img alt="Bill preview"></div>
                <div class="ai-actions">
                    <button type="button" class="btn btn-ai-primary" id="aiExtractBtn" {{ $aiReady ? '' : 'disabled' }}>
                        <i class="fas fa-brain me-1"></i> Read bill
                    </button>
                </div>
            </div>
        </div>

        <div class="ai-review" id="aiReview">
            <div class="alert alert-warning" id="aiWarnings">Check the original bill carefully. AI can miss or misread information; add or correct fields before saving.</div>
            <div class="card ai-card mb-3">
                <div class="card-body">
                    <div class="ai-section-title">2. Bill details — edit if needed</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Vendor</label>
                            <select id="aiVendor" class="form-control">
                                <option value="">Create from bill name</option>
                            </select>
                            <input type="text" id="aiVendorName" class="form-control mt-2" placeholder="New vendor name">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Phone</label>
                            <input type="text" id="aiVendorPhone" class="form-control" placeholder="Vendor phone">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bill no.</label>
                            <input type="text" id="aiBillNo" class="form-control">
                            <div class="text-danger small mt-1" id="aiBillNoError"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Purchase date</label>
                            <input type="date" id="aiBillDate" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Remark</label>
                            <input type="text" id="aiRemark" class="form-control" placeholder="Optional note from bill">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select id="aiStatus" class="form-control">
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bill type</label>
                            <select id="aiDocumentType" class="form-control">
                                <option value="purchase">Supplies / equipment purchase</option>
                                <option value="service">Service / maintenance</option>
                                <option value="utility">Utility bill</option>
                                <option value="credit_note">Credit note (use purchase returns)</option>
                                <option value="other">Other (review type before saving)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Printed grand total</label>
                            <input id="aiPrintedTotal" type="number" step="0.01" min="0" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <input id="aiCurrency" class="form-control" value="INR">
                        </div>
                    </div>
                    <h6 class="mt-3">Additional bill details</h6>
                    <p class="ai-empty-note">Fields found on this bill appear below. Add any missing tax, payment, reference or other detail.</p>
                    <div id="aiExtraFields"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="aiAddField">+ Add bill field</button>
                </div>
            </div>

            <div class="card ai-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="ai-section-title mb-0">3. Materials & stock</div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="aiAddRow">+ Add line</button>
                    </div>
                    <p class="ai-empty-note mb-3">Confirm quantities and units. Supplies increase stock; services and utilities create catalog entries without increasing stock. Convert units before matching an existing product.</p>
                    <div id="aiItems"></div>
                    <div class="ai-totals">
                        <div>Lines: <span id="aiItemsCount">0</span></div>
                        <div>Items total: <span id="aiItemsTotal">0.00</span></div>
                        <div>Grand total: <span id="aiGrandTotal">0.00</span></div>
                    </div>
                    <p id="aiTotalDifference" class="text-warning mt-2"></p>
                    <div class="ai-actions">
                        <button type="button" class="btn btn-cancel" id="aiResetBtn">Upload another</button>
                        <button type="button" class="btn btn-ai-primary" id="aiSaveBtn">
                            Save purchase & update stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @if ($recentBills->isNotEmpty())
            <div class="card ai-card mt-3"><div class="card-body">
                <h5>Recently saved bills</h5>
                @foreach ($recentBills as $recent)
                    <p class="mb-2"><a href="{{ route('purchase.ai-bill.saved', $recent->purchase_invoice_id) }}">{{ $recent->reviewed_data['header']['bill_no'] ?? $recent->original_name }}</a> · {{ $recent->created_at->format('d M Y') }}</p>
                @endforeach
            </div></div>
        @endif
    </div>
@endsection

@push('js')
<datalist id="aiHotelCategories">
    @foreach (['Kitchen', 'Beverages', 'Housekeeping', 'Toiletries', 'Linen', 'Equipment', 'Maintenance', 'Services', 'Utilities', 'Other'] as $category)
        <option value="{{ $category }}">
    @endforeach
</datalist>
<script>
window.aiBillConfig = {
    fieldLabels: @json(\App\Support\BillFieldLabel::labels()),
    csrf: @json(csrf_token()),
    vendors: @json($vendors),
    products: @json($products),
    parseUrl: @json(route('purchase.ai-bill.parse')),
    saveUrl: @json(route('purchase.ai-bill.save'))
};
</script>
<script src="{{ asset('admin/assets/js/ai-purchase-bill.js') }}?v=20261030-labels"></script>
@endpush
