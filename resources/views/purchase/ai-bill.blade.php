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
                <p>Photograph a bill of supply. We fill vendor, bill number, date and every line item. Recheck the fields, then save — existing products get stock, new ones are created.</p>
            </div>
            <a href="{{ route('purchase.lists') }}" class="btn btn-cancel">
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
                    </div>
                </div>
            </div>

            <div class="card ai-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="ai-section-title mb-0">3. Materials & stock</div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="aiAddRow">+ Add line</button>
                    </div>
                    <p class="ai-empty-note mb-3">Stock quantity defaults to weight (kg) when present, otherwise bags/boxes. Change anything before save.</p>
                    <div id="aiItems"></div>
                    <div class="ai-totals">
                        <div>Lines: <span id="aiItemsCount">0</span></div>
                        <div>Items total: <span id="aiItemsTotal">0.00</span></div>
                        <div>Grand total: <span id="aiGrandTotal">0.00</span></div>
                    </div>
                    <div class="ai-actions">
                        <button type="button" class="btn btn-cancel" id="aiResetBtn">Upload another</button>
                        <button type="button" class="btn btn-ai-primary" id="aiSaveBtn">
                            Save purchase & update stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
(function () {
    const csrf = '{{ csrf_token() }}';
    const vendors = @json($vendors);
    const categories = @json($categories);
    const products = @json($products);
    const dropzone = document.getElementById('aiDropzone');
    const fileInput = document.getElementById('aiBillFile');
    const preview = document.getElementById('aiPreview');
    const itemsEl = document.getElementById('aiItems');
    let rowIndex = 0;

    function authHeaders() {
        return {
            'X-CSRF-TOKEN': csrf,
            'Authorization': 'Bearer ' + (localStorage.getItem('authToken') || '')
        };
    }

    function money(n) {
        return Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fillVendorSelect(selectedId) {
        const select = document.getElementById('aiVendor');
        select.innerHTML = '<option value="">Create from bill name</option>';
        vendors.forEach(function (vendor) {
            const opt = document.createElement('option');
            opt.value = vendor.id;
            opt.textContent = vendor.name + (vendor.phone ? ' (' + vendor.phone + ')' : '');
            if (String(vendor.id) === String(selectedId || '')) opt.selected = true;
            select.appendChild(opt);
        });
        document.getElementById('aiVendorName').style.display = select.value ? 'none' : 'block';
    }

    document.getElementById('aiVendor').addEventListener('change', function () {
        document.getElementById('aiVendorName').style.display = this.value ? 'none' : 'block';
        const vendor = vendors.find(v => String(v.id) === String(this.value));
        if (vendor && vendor.phone) {
            document.getElementById('aiVendorPhone').value = vendor.phone;
        }
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
        });
    });
    dropzone.addEventListener('drop', function (e) {
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            showPreview();
        }
    });
    fileInput.addEventListener('change', showPreview);

    function showPreview() {
        const file = fileInput.files[0];
        if (!file) return;
        document.getElementById('aiFileName').textContent = file.name;
        const url = URL.createObjectURL(file);
        preview.style.display = 'block';
        preview.querySelector('img').src = url;
    }

    function categoryOptions(selected) {
        let html = '<option value="General">General</option>';
        categories.forEach(function (cat) {
            const sel = String(cat.id) === String(selected || '') ? ' selected' : '';
            html += `<option value="${cat.id}"${sel}>${escapeHtml(cat.name)}</option>`;
        });
        return html;
    }

    function productOptions(selectedId, extractedName) {
        let html = `<option value="">New: ${escapeHtml(extractedName || 'product')}</option>`;
        products.forEach(function (p) {
            const sel = String(p.id) === String(selectedId || '') ? ' selected' : '';
            html += `<option value="${p.id}"${sel}>${escapeHtml(p.name)} (stock ${p.quantity})</option>`;
        });
        return html;
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function addItemRow(item) {
        item = item || {};
        const idx = rowIndex++;
        const action = item.product_id ? 'update' : 'create';
        const wrap = document.createElement('div');
        wrap.className = 'ai-item-row';
        wrap.dataset.index = idx;
        wrap.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="ai-badge ${action === 'update' ? 'ai-badge-update' : 'ai-badge-create'} js-action-badge">
                    ${action === 'update' ? 'Existing · stock will increase' : 'New product will be created'}
                </span>
                <button type="button" class="btn btn-sm btn-outline-danger js-remove-row">Remove</button>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Match / create product</label>
                    <select class="form-control js-product">${productOptions(item.product_id, item.name)}</select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Product name</label>
                    <input type="text" class="form-control js-name" value="${escapeHtml(item.name || '')}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select class="form-control js-category">${categoryOptions(item.category_id)}</select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">HSN</label>
                    <input type="text" class="form-control js-hsn" value="${escapeHtml(item.hsn || '')}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bags / box</label>
                    <input type="number" step="0.01" min="0" class="form-control js-bags" value="${item.bags || 0}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Weight (kg)</label>
                    <input type="number" step="0.01" min="0" class="form-control js-weight" value="${item.weight || 0}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Stock qty</label>
                    <input type="number" step="0.01" min="0.01" class="form-control js-qty" value="${item.quantity || 1}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Rate</label>
                    <input type="number" step="0.01" min="0" class="form-control js-rate" value="${item.rate || 0}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" min="0" class="form-control js-amount" value="${item.amount || 0}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Current stock</label>
                    <input type="text" class="form-control js-stock" value="${item.current_stock || 0}" readonly>
                </div>
            </div>
        `;
        wrap.querySelector('.js-remove-row').addEventListener('click', function () {
            wrap.remove();
            recalcTotals();
        });
        wrap.querySelector('.js-product').addEventListener('change', function () {
            const product = products.find(p => String(p.id) === String(this.value));
            const badge = wrap.querySelector('.js-action-badge');
            if (product) {
                wrap.querySelector('.js-name').value = product.name;
                wrap.querySelector('.js-category').value = product.category_id || wrap.querySelector('.js-category').value;
                wrap.querySelector('.js-stock').value = product.quantity;
                badge.className = 'ai-badge ai-badge-update js-action-badge';
                badge.textContent = 'Existing · stock will increase';
            } else {
                wrap.querySelector('.js-stock').value = 0;
                badge.className = 'ai-badge ai-badge-create js-action-badge';
                badge.textContent = 'New product will be created';
            }
        });
        wrap.querySelector('.js-amount').addEventListener('input', recalcTotals);
        wrap.querySelector('.js-qty').addEventListener('input', recalcTotals);
        itemsEl.appendChild(wrap);
        recalcTotals();
    }

    function recalcTotals() {
        let total = 0;
        const rows = itemsEl.querySelectorAll('.ai-item-row');
        rows.forEach(function (row) {
            total += parseFloat(row.querySelector('.js-amount').value) || 0;
        });
        document.getElementById('aiItemsCount').textContent = rows.length;
        document.getElementById('aiItemsTotal').textContent = money(total);
        const grand = document.getElementById('aiGrandTotal');
        if (!grand.dataset.locked) {
            grand.textContent = money(total);
        }
    }

    document.getElementById('aiAddRow').addEventListener('click', function () {
        addItemRow({ name: '', quantity: 1, bags: 1, weight: 0, rate: 0, amount: 0, action: 'create' });
    });

    document.getElementById('aiExtractBtn').addEventListener('click', function () {
        const file = fileInput.files[0];
        if (!file) {
            Swal.fire({ icon: 'warning', title: 'Select a bill image first', confirmButtonColor: '#ff9f43' });
            return;
        }
        const btn = this;
        const original = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Reading bill...';
        btn.disabled = true;

        const form = new FormData();
        form.append('bill', file);
        form.append('_token', csrf);

        fetch('{{ route("purchase.ai-bill.parse") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: form
        }).then(async function (res) {
            const body = await res.text();
            let data;
            try {
                data = JSON.parse(body);
            } catch (_) {
                if (res.redirected || res.status === 401 || res.status === 419) {
                    throw new Error('Your session expired. Refresh the page and sign in again.');
                }
                throw new Error('The server could not finish reading the bill. Please try again. If it continues, contact your administrator.');
            }
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Could not read this bill.');
            }
            fillForm(data);
        }).catch(function (err) {
            Swal.fire({ icon: 'error', title: 'Could not read bill', text: err.message, confirmButtonColor: '#ff9f43' });
        }).finally(function () {
            btn.innerHTML = original;
            btn.disabled = false;
        });
    });

    function fillForm(data) {
        const header = data.header || {};
        fillVendorSelect(header.vendor_id);
        document.getElementById('aiVendorName').value = header.vendor_name || '';
        document.getElementById('aiVendorPhone').value = header.vendor_phone || '';
        document.getElementById('aiBillNo').value = header.bill_no || '';
        document.getElementById('aiBillDate').value = header.bill_date || '';
        document.getElementById('aiRemark').value = [
            header.customer_name ? 'Party: ' + header.customer_name : '',
            header.amount_in_words || ''
        ].filter(Boolean).join(' · ');
        itemsEl.innerHTML = '';
        rowIndex = 0;
        (data.items || []).forEach(addItemRow);
        document.getElementById('aiReview').style.display = 'block';
        const grand = document.getElementById('aiGrandTotal');
        grand.textContent = money(header.grand_total || 0);
        grand.dataset.locked = header.grand_total > 0 ? '1' : '';
        document.getElementById('aiReview').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    document.getElementById('aiResetBtn').addEventListener('click', function () {
        document.getElementById('aiReview').style.display = 'none';
        fileInput.value = '';
        preview.style.display = 'none';
        document.getElementById('aiFileName').textContent = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.getElementById('aiSaveBtn').addEventListener('click', async function () {
        const btn = this;
        const original = btn.innerHTML;
        const billNo = document.getElementById('aiBillNo').value.trim();
        const billDate = document.getElementById('aiBillDate').value;
        const vendorSelect = document.getElementById('aiVendor').value;
        const vendorName = document.getElementById('aiVendorName').value.trim();
        document.getElementById('aiBillNoError').textContent = '';

        if (!billNo) {
            document.getElementById('aiBillNoError').textContent = 'Bill number is required.';
            return;
        }
        if (!billDate) {
            Swal.fire({ icon: 'warning', title: 'Purchase date is required', confirmButtonColor: '#ff9f43' });
            return;
        }
        if (!vendorSelect && !vendorName) {
            Swal.fire({ icon: 'warning', title: 'Select or enter a vendor', confirmButtonColor: '#ff9f43' });
            return;
        }

        const productsPayload = [];
        itemsEl.querySelectorAll('.ai-item-row').forEach(function (row) {
            const qty = parseFloat(row.querySelector('.js-qty').value) || 0;
            const amount = parseFloat(row.querySelector('.js-amount').value) || 0;
            const name = row.querySelector('.js-name').value.trim();
            const productId = row.querySelector('.js-product').value;
            if (!name || qty <= 0) return;
            const unitPrice = qty > 0 ? +(amount / qty).toFixed(4) : 0;
            productsPayload.push({
                id: productId || name,
                category_id: row.querySelector('.js-category').value || 'General',
                price: unitPrice,
                quantity: qty,
                discount_percent: 0,
                discount_amount: 0,
                total: amount,
                hsn_code: row.querySelector('.js-hsn').value.trim(),
                imei_no: '[]'
            });
        });

        if (!productsPayload.length) {
            Swal.fire({ icon: 'warning', title: 'Add at least one product line', confirmButtonColor: '#ff9f43' });
            return;
        }

        const confirmSave = await Swal.fire({
            title: 'Save this purchase?',
            text: 'Existing products will get stock. Missing products will be created.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ff9f43',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, save'
        });
        if (!confirmSave.isConfirmed) return;

        const grandTotal = productsPayload.reduce((sum, row) => sum + Number(row.total || 0), 0);
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        btn.style.pointerEvents = 'none';

        fetch('/api/purchase_order', {
            method: 'POST',
            headers: Object.assign({ 'Content-Type': 'application/json', 'Accept': 'application/json' }, authHeaders()),
            body: JSON.stringify({
                vendor_id: vendorSelect || vendorName,
                vendor_phone: document.getElementById('aiVendorPhone').value.trim() || null,
                status: document.getElementById('aiStatus').value,
                discount: 0,
                shipping: 0,
                grand_total: grandTotal,
                products: productsPayload,
                taxes: [],
                gst_option: 'without',
                bill_no: billNo,
                purchase_date: billDate,
                remark: document.getElementById('aiRemark').value.trim(),
                selectedSubAdminId: localStorage.getItem('selectedSubAdminId') || null
            })
        }).then(async function (res) {
            const data = await res.json();
            if (!res.ok || !data.success) {
                const firstError = data.errors ? Object.values(data.errors)[0] : null;
                throw new Error((firstError && firstError[0]) || data.message || 'Save failed.');
            }
            await Swal.fire({ icon: 'success', title: 'Purchase saved', text: data.message || 'Stock updated.', confirmButtonColor: '#ff9f43' });
            window.location.href = data.purchase_id ? '/print-purchase/' + data.purchase_id : '{{ route("purchase.lists") }}';
        }).catch(function (err) {
            if (String(err.message).toLowerCase().includes('bill number')) {
                document.getElementById('aiBillNoError').textContent = err.message;
            }
            Swal.fire({ icon: 'error', title: 'Could not save', text: err.message, confirmButtonColor: '#ff9f43' });
        }).finally(function () {
            btn.innerHTML = original;
            btn.style.pointerEvents = 'auto';
        });
    });

    fillVendorSelect(null);
})();
</script>
@endpush
