@extends('layout.app')
@section('title', 'Digital Menu + QR')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Digital Menu + QR</h4>
                <h6>Public category-wise menu guests can open by scanning a QR code</h6>
            </div>
        </div>

        @if(!($hasColumns ?? true))
            <div class="alert alert-warning">
                Database columns missing. Run SQL file
                <code>database/sql/2026_11_05_171800_add_public_menu.sql</code>
                then refresh this page.
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label">Public menu</label>
                            <select id="menu_public_enabled" class="form-select">
                                <option value="1" {{ !empty($enabled) ? 'selected' : '' }}>On — anyone with link/QR can view</option>
                                <option value="0" {{ empty($enabled) ? 'selected' : '' }}>Off — link shows unavailable</option>
                            </select>
                            <small class="text-muted">Shows active POS products grouped by category.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Share link</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="menuUrl" readonly value="{{ $menuUrl ?? '' }}">
                                <button type="button" class="btn btn-outline-primary" id="copyMenuUrl">Copy</button>
                                <a class="btn btn-primary" id="openMenuUrl" href="{{ $menuUrl ?? '#' }}" target="_blank" rel="noopener">Open</a>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary" id="saveMenuSettings">Save</button>
                            <button type="button" class="btn btn-outline-secondary" id="regenToken">Regenerate link</button>
                        </div>
                        <p class="text-muted mt-2 mb-0" style="font-size:12px;">
                            Regenerating creates a new QR/link. Old QR codes will stop working.
                        </p>
                    </div>

                    <div class="col-lg-6 text-center">
                        <label class="form-label d-block">QR code (scan to open menu)</label>
                        <div class="border rounded-3 p-3 d-inline-block bg-white">
                            @if(!empty($qrUrl))
                                <img id="menuQrImg" src="{{ $qrUrl }}" alt="Menu QR" width="240" height="240" style="display:block;">
                            @else
                                <div id="menuQrImg" style="width:240px;height:240px;display:grid;place-items:center;color:#999;">No link yet</div>
                            @endif
                        </div>
                        <div class="mt-3">
                            <a id="downloadQr" class="btn btn-sm btn-outline-dark" href="{{ $qrUrl ?? '#' }}" download="menu-qr.png" target="_blank" rel="noopener">Download QR</a>
                            <button type="button" class="btn btn-sm btn-outline-dark" id="printQr">Print QR</button>
                        </div>
                        <p class="text-muted mt-2 mb-0" style="font-size:12px;">
                            Print and place on tables / entrance. Guests scan → see full menu by category.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    var token = localStorage.getItem('authToken') || @json(session('token'));
    function toast(msg, ok) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: ok ? 'success' : 'error', title: msg, timer: 1800, showConfirmButton: false });
        } else {
            alert(msg);
        }
    }
    function applyResponse(data) {
        if (data.menu_url) {
            $('#menuUrl').val(data.menu_url);
            $('#openMenuUrl').attr('href', data.menu_url);
        }
        if (data.qr_url) {
            var $img = $('#menuQrImg');
            if ($img.is('img')) {
                $img.attr('src', data.qr_url);
            } else {
                $img.replaceWith('<img id="menuQrImg" src="' + data.qr_url + '" alt="Menu QR" width="240" height="240" style="display:block;">');
            }
            $('#downloadQr').attr('href', data.qr_url);
        }
        if (typeof data.enabled !== 'undefined') {
            $('#menu_public_enabled').val(data.enabled ? '1' : '0');
        }
    }
    function save(regenerate) {
        $.ajax({
            url: @json(route('setting.digital-menu.save')),
            method: 'POST',
            data: {
                menu_public_enabled: $('#menu_public_enabled').val(),
                regenerate_token: regenerate ? 1 : 0,
                _token: @json(csrf_token())
            },
            headers: token ? { Authorization: 'Bearer ' + token } : {},
            success: function (res) {
                if (!res.status) {
                    toast(res.message || 'Save failed', false);
                    return;
                }
                applyResponse(res);
                toast(regenerate ? 'New menu link created' : 'Saved', true);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Save failed';
                toast(msg, false);
            }
        });
    }
    $('#saveMenuSettings').on('click', function () { save(false); });
    $('#regenToken').on('click', function () {
        if (!confirm('Create a new link? Old QR codes will stop working.')) return;
        save(true);
    });
    $('#copyMenuUrl').on('click', function () {
        var el = document.getElementById('menuUrl');
        if (!el || !el.value) return;
        navigator.clipboard.writeText(el.value).then(function () {
            toast('Link copied', true);
        }).catch(function () {
            el.select();
            document.execCommand('copy');
            toast('Link copied', true);
        });
    });
    $('#printQr').on('click', function () {
        var src = $('#menuQrImg').attr('src');
        var url = $('#menuUrl').val() || '';
        if (!src) return;
        var w = window.open('', '_blank', 'width=420,height=560');
        if (!w) return;
        w.document.write('<html><head><title>Menu QR</title></head><body style="text-align:center;font-family:sans-serif;padding:24px;">');
        w.document.write('<h2 style="margin:0 0 12px;">{{ $setting->name ?? "Digital Menu" }}</h2>');
        w.document.write('<p style="margin:0 0 16px;">Scan for menu</p>');
        w.document.write('<img src="' + src + '" width="280" height="280">');
        w.document.write('<p style="font-size:11px;word-break:break-all;margin-top:16px;">' + url + '</p>');
        w.document.write('</body></html>');
        w.document.close();
        w.focus();
        setTimeout(function () { w.print(); }, 400);
    });
})();
</script>
@endpush
