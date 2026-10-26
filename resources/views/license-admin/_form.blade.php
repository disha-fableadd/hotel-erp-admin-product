@php
    $license = $license ?? null;
@endphp

<div class="mb-3">
    <label class="form-label">Purchase code <span class="text-danger">*</span></label>
    <input type="text" name="purchase_code" class="form-control @error('purchase_code') is-invalid @enderror"
        value="{{ old('purchase_code', $license?->purchase_code) }}"
        {{ $license ? 'readonly' : '' }} required placeholder="From Envato receipt">
    @error('purchase_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Envato username <span class="text-danger">*</span></label>
    <input type="text" name="buyer" class="form-control @error('buyer') is-invalid @enderror"
        value="{{ old('buyer', $license?->buyer) }}" required placeholder="Buyer CodeCanyon username">
    @error('buyer')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">License type</label>
        <select name="license_type" class="form-select" id="license_type">
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('license_type', $license?->license_type ?? 'regular') === $type)>
                    {{ ucfirst($type) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Max domains</label>
        <input type="number" name="max_domains" class="form-control" min="1" max="50"
            value="{{ old('max_domains', $license?->max_domains ?? 1) }}">
        <div class="form-text">Regular=1, Extended=5 (default), Development=localhost only</div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
        @foreach (['active', 'revoked', 'suspended'] as $status)
            <option value="{{ $status }}" @selected(old('status', $license?->status ?? 'active') === $status)>
                {{ ucfirst($status) }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Envato item ID (optional)</label>
    <input type="text" name="envato_item_id" class="form-control"
        value="{{ old('envato_item_id', $license?->envato_item_id) }}">
</div>

<div class="mb-3">
    <label class="form-label">Notes (optional)</label>
    <textarea name="notes" class="form-control" rows="2" placeholder="Buyer email, order date, etc.">{{ old('notes', $license?->notes) }}</textarea>
</div>

@push('scripts')
<script>
    document.getElementById('license_type')?.addEventListener('change', function () {
        const map = { regular: 1, extended: {{ (int) config('license.domain_limits.extended', 5) }}, development: 1 };
        const input = document.querySelector('[name=max_domains]');
        if (input && map[this.value]) input.value = map[this.value];
    });
</script>
@endpush

