(function () {
    'use strict';
    const config = window.aiBillConfig;
    const byId = id => document.getElementById(id);
    const itemsEl = byId('aiItems');
    const input = byId('aiBillFile');
    let draftToken = null;
    let originalHeader = {};
    let previewUrl;
    const number = value => Number(value || 0);
    const money = value => number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const normalize = value => String(value || '').toLocaleLowerCase().replace(/[^\p{L}\p{N}]+/gu, ' ').trim();
    const unit = value => ({kgs:'kg',kilogram:'kg',kilograms:'kg',l:'litre',ltr:'litre',ltrs:'litre',litres:'litre',liters:'litre',liter:'litre',pcs:'piece',pc:'piece',pieces:'piece',nos:'piece',no:'piece',each:'piece',ea:'piece',g:'gram',grams:'gram',ml:'millilitre',millilitres:'millilitre'}[normalize(value)] || normalize(value));

    function fieldRow(container, key = '', value = '') {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 ai-extra-field';
        row.dataset.original = JSON.stringify(value);
        const friendlyLabel = config.fieldLabels?.[String(key).trim().toLowerCase()]
            || (/^\d+$/.test(key) ? 'Detail ' + (Number(key) + 1)
                : String(key).replace(/([a-z])([A-Z])/g, '$1 $2').replace(/[_-]+/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
        row.dataset.key = key;
        row.dataset.label = friendlyLabel;
        if (value !== null && typeof value === 'object') {
            row.innerHTML = `<div class="col-md-11"><input class="form-control js-field-key" aria-label="Field label" value="${escape(key)}"></div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-danger js-remove-field" aria-label="Remove field">×</button></div>
                <div class="col-12"><div class="js-field-group border-start ps-3"></div><button type="button" class="btn btn-sm btn-outline-secondary js-add-child">+ Add detail</button></div>`;
            const group = row.querySelector('.js-field-group');
            group.dataset.kind = Array.isArray(value) ? 'array' : 'object';
            Object.entries(value).forEach(([childKey, childValue]) => fieldRow(group, childKey, childValue));
            row.querySelector('.js-add-child').onclick = () => {
                const next = group.dataset.kind === 'array' ? String(Math.max(-1, ...[...group.children].map(child => Number(child.dataset.key) || 0)) + 1) : '';
                const child = key === 'taxes' && group.dataset.kind === 'array' ? {name:'',rate:0,amount:0}
                    : (group.dataset.kind === 'array' && value[0] !== null && value.length && typeof value[0] === 'object' ? Object.fromEntries(Object.keys(value[0]).map(k => [k, ''])) : '');
                fieldRow(group, next, child);
            };
            row.querySelector('.js-remove-field').onclick = () => row.remove();
            row.querySelector('.js-field-key').value = friendlyLabel;
            container.appendChild(row);
            return;
        }
        row.innerHTML = `<div class="col-md-4"><input class="form-control js-field-key" aria-label="Field label" placeholder="Field label" value="${escape(key)}"></div>
            <div class="col-md-7"><textarea rows="1" class="form-control js-field-value" aria-label="Field value">${escape(typeof value === 'object' ? JSON.stringify(value) : value)}</textarea></div>
            <div class="col-md-1"><button type="button" class="btn btn-outline-danger" aria-label="Remove field">×</button></div>`;
        row.querySelector('button').onclick = () => row.remove();
        row.querySelector('.js-field-key').value = friendlyLabel;
        container.appendChild(row);
    }

    function readFields(container) {
        const result = Object.create(null);
        [...container.children].filter(row => row.classList.contains('ai-extra-field')).forEach(row => {
            const enteredLabel = row.querySelector('.js-field-key').value.trim();
            const key = enteredLabel === row.dataset.label ? row.dataset.key : enteredLabel;
            const group = row.querySelector('.js-field-group');
            const value = group ? readFields(group) : row.querySelector('.js-field-value').value;
            if (!key && typeof value === 'string' && !value.trim()) return;
            if (!key) throw new Error('Enter a label for every additional field.');
            if (Object.hasOwn(result, key)) throw new Error('Additional field labels must be unique: ' + key);
            const original = JSON.parse(row.dataset.original);
            result[key] = group ? value : (typeof original === 'number' && value.trim() !== '' && Number.isFinite(Number(value)) ? Number(value) : value);
        });
        return container.dataset.kind === 'array' ? Object.values(result) : result;
    }

    function vendorOptions(selected) {
        byId('aiVendor').innerHTML = '<option value="">Create / match vendor by name</option>' + config.vendors.map(v =>
            `<option value="${v.id}" ${String(v.id) === String(selected) ? 'selected' : ''}>${escape(v.name)}</option>`).join('');
    }
    vendorOptions();

    function productOptions(selected, name) {
        return `<option value="">Match by name or create: ${escape(name || 'new item')}</option>` + config.products.map(p =>
            `<option value="${p.id}" ${String(p.id) === String(selected) ? 'selected' : ''}>${escape(p.name)} (stock ${escape(p.quantity)} ${escape(p.unit)})</option>`).join('');
    }

    function rowMatch(row) {
        const name = row.querySelector('.js-name').value;
        const itemUnit = row.querySelector('.js-unit').value;
        const matches = config.products.filter(p => normalize(p.name) === normalize(name) && (!p.unit || unit(p.unit) === unit(itemUnit)));
        row.querySelector('.js-product').value = matches.length === 1 ? matches[0].id : '';
        updateStock(row);
    }

    function updateStock(row) {
        const product = config.products.find(p => String(p.id) === row.querySelector('.js-product').value);
        const track = row.querySelector('.js-track').checked;
        const before = number(product?.quantity);
        const after = before + (track ? number(row.querySelector('.js-qty').value) : 0);
        row.querySelector('.js-stock').textContent = `${before} → ${after} ${product?.unit || row.querySelector('.js-unit').value}`;
        const badge = row.querySelector('.js-action-badge');
        badge.className = 'ai-badge js-action-badge ' + (product ? 'ai-badge-update' : 'ai-badge-create');
        badge.textContent = (product ? 'Existing product' : 'Match on save / new product') + (track ? ' · adds stock' : ' · no stock change');
    }

    function addItem(item = {}) {
        const row = document.createElement('div');
        row.className = 'ai-item-row';
        row.billItem = item;
        row.innerHTML = `<div class="d-flex justify-content-between mb-2"><span class="js-action-badge"></span><button type="button" class="btn btn-sm btn-outline-danger js-remove">Remove line</button></div>
            <div class="row g-2">
            <div class="col-md-6"><label class="form-label">Existing product / create new</label><select class="form-control js-product">${productOptions(item.product_id, item.name)}</select></div>
            <div class="col-md-6"><label class="form-label">Bill item name</label><input class="form-control js-name" value="${escape(item.name || '')}"></div>
            <div class="col-md-4"><label class="form-label">Hotel category</label><input class="form-control js-category" list="aiHotelCategories" value="${escape(item.category || 'Other')}"></div>
            <div class="col-md-2"><label class="form-label">HSN / SAC</label><input class="form-control js-hsn" value="${escape(item.hsn || '')}"></div>
            <div class="col-md-2"><label class="form-label">Quantity</label><input type="number" step="0.0001" min="0.0001" class="form-control js-qty" value="${escape(item.quantity ?? '')}"></div>
            <div class="col-md-2"><label class="form-label">Unit</label><input class="form-control js-unit" value="${escape(item.unit || 'piece')}"></div>
            <div class="col-md-2"><label class="form-label">Printed rate</label><input type="number" step="0.0001" min="0" class="form-control js-rate" value="${escape(item.rate || 0)}"></div>
            <div class="col-md-3"><label class="form-label">Line amount</label><input type="number" step="0.01" min="0" class="form-control js-amount" value="${escape(item.amount || 0)}"></div>
            <div class="col-md-4"><label class="form-label d-block">Stock before → after save</label><strong class="js-stock"></strong></div>
            <div class="col-md-5"><label class="mt-4"><input type="checkbox" class="js-track" ${item.track_stock === false ? '' : 'checked'}> Increase stock (uncheck for service / utility)</label></div>
            </div><details class="mt-3" open><summary>Additional line details</summary><div class="js-extra mt-2"></div><button type="button" class="btn btn-sm btn-outline-secondary js-add-field">+ Add line field</button></details>`;
        Object.entries(item.extra_fields || {}).forEach(([key, value]) => fieldRow(row.querySelector('.js-extra'), key, value));
        ['bags', 'weight'].forEach(key => { if (number(item[key]) > 0 && !Object.hasOwn(item.extra_fields || {}, key)) fieldRow(row.querySelector('.js-extra'), key, item[key]); });
        row.querySelector('.js-add-field').onclick = () => fieldRow(row.querySelector('.js-extra'));
        row.querySelector('.js-remove').onclick = () => { row.remove(); totals(); };
        row.querySelector('.js-product').onchange = () => updateStock(row);
        row.querySelector('.js-name').onchange = () => rowMatch(row);
        row.querySelector('.js-unit').onchange = () => rowMatch(row);
        row.querySelector('.js-qty').oninput = () => updateStock(row);
        row.querySelector('.js-track').onchange = () => updateStock(row);
        row.querySelector('.js-amount').oninput = totals;
        itemsEl.appendChild(row);
        updateStock(row);
        totals();
    }

    function totals() {
        const rows = [...itemsEl.querySelectorAll('.ai-item-row')];
        const total = rows.reduce((sum, row) => sum + number(row.querySelector('.js-amount').value), 0);
        const printed = number(byId('aiPrintedTotal').value);
        byId('aiItemsCount').textContent = rows.length;
        byId('aiItemsTotal').textContent = money(total);
        byId('aiGrandTotal').textContent = money(printed);
        byId('aiTotalDifference').textContent = Math.abs(printed - total) > 0.01
            ? `Printed total differs from line amounts by ${money(printed - total)}. Verify taxes, discounts, charges and rounding in the additional fields. The printed total will be saved.` : '';
    }

    async function responseJson(response) {
        let data;
        try { data = await response.json(); } catch (_) {
            throw new Error('The server returned an unexpected response. Refresh the page and try again.');
        }
        if (!response.ok || !data.success) {
            const errors = Object.values(data.errors || {}).flat();
            throw new Error(errors[0] || data.message || 'The request could not be completed.');
        }
        return data;
    }

    function showPreview() {
        const file = input.files[0];
        if (!file) return;
        draftToken = null;
        byId('aiReview').style.display = 'none';
        if (!['image/jpeg','image/png','image/webp'].includes(file.type) || file.size > 10 * 1024 * 1024) {
            input.value = '';
            byId('aiPreview').style.display = 'none';
            Swal.fire('Choose a JPG, PNG or WEBP up to 10 MB.');
            return;
        }
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        byId('aiFileName').textContent = file.name;
        byId('aiPreview').querySelector('img').src = previewUrl;
        byId('aiPreview').style.display = 'block';
    }
    input.onchange = showPreview;
    ['dragenter','dragover'].forEach(event => byId('aiDropzone').addEventListener(event, e => {e.preventDefault(); byId('aiDropzone').classList.add('is-dragover');}));
    ['dragleave','drop'].forEach(event => byId('aiDropzone').addEventListener(event, e => {e.preventDefault(); byId('aiDropzone').classList.remove('is-dragover');}));
    byId('aiDropzone').addEventListener('drop', e => { if (e.dataTransfer.files.length) {input.files = e.dataTransfer.files; showPreview();} });
    byId('aiExtractBtn').onclick = async function () {
        if (!input.files[0]) return Swal.fire('Select a bill image first.');
        const original = this.innerHTML;
        this.disabled = true;
        this.textContent = 'Reading bill…';
        byId('aiSaveBtn').disabled = true;
        try {
            const sourceFile = input.files[0];
            const form = new FormData();
            form.append('bill', sourceFile);
            const data = await responseJson(await fetch(config.parseUrl, {method:'POST', headers:{'X-CSRF-TOKEN':config.csrf, Accept:'application/json'}, body:form}));
            if (input.files[0] !== sourceFile) return;
            draftToken = data.draft_token;
            originalHeader = data.header;
            vendorOptions(originalHeader.vendor_id);
            byId('aiVendorName').value = originalHeader.vendor_name || '';
            byId('aiVendorPhone').value = originalHeader.vendor_phone || '';
            byId('aiBillNo').value = originalHeader.bill_no || '';
            byId('aiBillDate').value = originalHeader.bill_date || '';
            byId('aiPrintedTotal').value = originalHeader.grand_total ?? '';
            byId('aiCurrency').value = originalHeader.currency || 'INR';
            byId('aiDocumentType').value = originalHeader.document_type || 'purchase';
            byId('aiRemark').value = '';
            byId('aiWarnings').textContent = ['Check the original bill carefully. Add or correct any missed information before saving.', ...(data.warnings || [])].join(' ');
            byId('aiExtraFields').innerHTML = '';
            const extra = Object.assign(Object.create(null), originalHeader.extra_fields || {});
            ['vendor_address','customer_name','amount_in_words','previous_balance'].forEach(key => {
                if (originalHeader[key] !== undefined && originalHeader[key] !== '') extra[key] = originalHeader[key];
            });
            Object.entries(extra).forEach(([key,value]) => fieldRow(byId('aiExtraFields'), key,value));
            itemsEl.innerHTML = '';
            (data.items || []).forEach(addItem);
            byId('aiReview').style.display = 'block';
            totals();
            byId('aiReview').scrollIntoView({behavior:'smooth',block:'start'});
        } catch (error) {
            Swal.fire({icon:'error',title:'Could not read bill',text:error.message});
        } finally {
            this.innerHTML = original;
            this.disabled = false;
            byId('aiSaveBtn').disabled = false;
        }
    };
    byId('aiAddRow').onclick = () => addItem({quantity:1, unit:'piece'});
    byId('aiAddField').onclick = () => fieldRow(byId('aiExtraFields'));
    byId('aiPrintedTotal').oninput = totals;
    byId('aiResetBtn').onclick = () => {
        draftToken = null; input.value = ''; byId('aiReview').style.display = 'none';
        byId('aiPreview').style.display = 'none'; byId('aiFileName').textContent = '';
        if (previewUrl) URL.revokeObjectURL(previewUrl);
    };
    byId('aiSaveBtn').onclick = async function () {
        if (!draftToken) return Swal.fire('Read the bill before saving.');
        const original = this.innerHTML;
        this.disabled = true;
        try {
            const header = Object.assign({}, originalHeader, {
                vendor_id: byId('aiVendor').value || null,
                vendor_name: byId('aiVendorName').value.trim(), vendor_phone: byId('aiVendorPhone').value.trim(),
                bill_no: byId('aiBillNo').value.trim(), bill_date: byId('aiBillDate').value,
                document_type: byId('aiDocumentType').value, currency: byId('aiCurrency').value.trim().toUpperCase(),
                grand_total: number(byId('aiPrintedTotal').value),
                extra_fields: readFields(byId('aiExtraFields')), remark: byId('aiRemark').value.trim(),
            });
            const items = [...itemsEl.querySelectorAll('.ai-item-row')].map(row => Object.assign({}, row.billItem, {
                name: row.querySelector('.js-name').value.trim(), product_id: row.querySelector('.js-product').value || null,
                category_id: null, category: row.querySelector('.js-category').value.trim(),
                hsn: row.querySelector('.js-hsn').value.trim(), quantity: number(row.querySelector('.js-qty').value),
                unit: row.querySelector('.js-unit').value.trim(), rate: number(row.querySelector('.js-rate').value),
                amount: number(row.querySelector('.js-amount').value), track_stock: row.querySelector('.js-track').checked,
                extra_fields: readFields(row.querySelector('.js-extra')),
            }));
            if (!header.bill_no || !header.bill_date || (!header.vendor_id && !header.vendor_name)) throw new Error('Enter the vendor, bill number and purchase date.');
            if (!items.length || items.some(item => !item.name || !item.unit || item.quantity <= 0 || item.amount < 0 || item.rate < 0)) throw new Error('Every line needs a name, unit and positive quantity. Rate and amount cannot be negative.');
            const confirmation = await Swal.fire({icon:'question',title:'Save reviewed bill?',text:'Stock-tracked lines increase inventory. Services are saved without adding stock. Confirm the printed total and all details.',showCancelButton:true,confirmButtonText:'Save bill'});
            if (!confirmation.isConfirmed) return;
            this.textContent = 'Saving…';
            const data = await responseJson(await fetch(config.saveUrl, {method:'POST',headers:{'X-CSRF-TOKEN':config.csrf,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify({draft_token:draftToken,header,items,status:byId('aiStatus').value})}));
            draftToken = null;
            await Swal.fire({icon:'success',title:'Bill saved',text:data.message});
            window.location.href = data.redirect;
        } catch (error) {
            Swal.fire({icon:'error',title:'Could not save bill',text:error.message});
        } finally {this.innerHTML = original; this.disabled = false;}
    };
})();
