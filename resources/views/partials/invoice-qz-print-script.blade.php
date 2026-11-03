{{-- Prefer local public/js/qz-tray.js; CDN is fallback only --}}
<style>
    .invoice-print-preview {
        position: relative;
        height: 100%;
        min-height: 100%;
    }
    .bt-printer-heading {
        font-size: 13px;
        font-weight: 700;
        color: #ff9f43;
        margin: 2px 0 6px;
    }
    .bt-printer-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        background: #fff;
        border: 1px solid #e6e8ee;
        border-radius: 10px;
        padding: 8px 10px;
        margin-bottom: 6px;
    }
    .bt-printer-row.is-selected {
        border-color: #ff9f43;
        background: #fff8f1;
    }
    .bt-printer-name {
        font-size: 14px;
        font-weight: 600;
        color: #1b2850;
    }
    .bt-printer-id {
        font-size: 11px;
        color: #8b93a7;
        word-break: break-all;
    }
    .bt-printer-select {
        border-radius: 20px;
        color: #3b6fd8;
        border: 1px solid #c5d4f7;
        background: #fff;
        white-space: nowrap;
        min-width: 76px;
        font-weight: 600;
    }
    .bt-printer-row.is-selected .bt-printer-select {
        background: #3b6fd8;
        color: #fff;
        border-color: #3b6fd8;
    }
    .bt-printer-actions {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 12px;
    }
    .bt-wifi-ip-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .bt-wifi-ip-row label {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        color: #667085;
        white-space: nowrap;
    }
    .bt-wifi-ip {
        flex: 1 1 auto;
        min-width: 0;
    }
    .bt-printer-note {
        font-size: 12px;
        color: #667085;
        margin: 0 0 8px;
        line-height: 1.35;
    }
    .phone-print-panel {
        background: #f4f7fb;
        padding: 0 12px 10px;
    }
    .phone-print-tabs {
        display: flex;
        border-bottom: 1px solid #e6e8ee;
        margin: 0 -12px 10px;
        background: #fff;
    }
    .phone-print-tab {
        flex: 1 1 0;
        border: 0;
        background: transparent;
        padding: 12px 8px 10px;
        font-weight: 700;
        color: #98a2b3;
    }
    .phone-print-tab.is-active {
        color: #e25555;
        box-shadow: inset 0 -2px 0 #e25555;
    }
    @media (min-width: 1025px) {
        .phone-print-panel,
        .invoice-preview-toggle {
            display: none !important;
        }
    }
    @media (max-width: 1024.98px) {
        #salesPrinterSelect,
        #posPrinterSelect,
        label[for="salesPrinterSelect"],
        label[for="posPrinterSelect"] {
            display: none !important;
        }
        #salesThermalAutoCut,
        #posThermalAutoCut,
        #salesBtFastPrint,
        #posBtFastPrint {
            width: 18px;
            height: 18px;
            margin-top: 0;
        }
        #salesInvoicePrintModal .modal-header,
        #posInvoicePrintModal .modal-header {
            flex-wrap: wrap;
            align-items: center;
            gap: 6px 8px;
            padding: 12px 12px 8px;
        }
        #salesInvoicePrintModal .modal-header > .pe-2,
        #posInvoicePrintModal .modal-header > .pe-2 {
            flex: 1 1 auto;
            min-width: 0;
            padding-right: 0 !important;
        }
        #salesInvoicePrintModal .modal-title,
        #posInvoicePrintModal .modal-title {
            font-size: 18px;
            line-height: 1.2;
        }
        #salesInvoicePrintModal .modal-header small.d-md-none,
        #posInvoicePrintModal .modal-header small.d-md-none {
            display: block !important;
            margin-top: 2px;
            line-height: 1.3;
        }
        #pdfTemplateButtons,
        #posPdfTemplateButtons {
            display: none !important;
        }
        .phone-print-panel {
            display: none;
        }
        .modal-body.is-printer-pick .phone-print-panel {
            display: block !important;
            min-height: 0;
            flex: 1 1 auto;
            max-height: none;
            overflow: auto;
        }
        .invoice-print-preview {
            display: flex;
            flex: 1 1 auto;
            height: auto;
            min-height: 280px;
            position: relative;
        }
        .modal-body.is-printer-pick .invoice-print-preview {
            display: none;
        }
        #salesInvoicePrintFrame,
        #posInvoicePrintFrame {
            height: 100% !important;
            min-height: 280px !important;
            flex: 1 1 auto;
        }
        .invoice-preview-toggle {
            display: none !important;
        }
        #salesInvoicePrintModal:has(.is-printer-pick) .invoice-preview-toggle,
        #posInvoicePrintModal:has(.is-printer-pick) .invoice-preview-toggle {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 36px;
            padding: 0 10px;
            border-radius: 18px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }
        #salesInvoicePrintModal .modal-header small.d-none.d-md-inline,
        #posInvoicePrintModal .modal-header small.d-none.d-md-inline {
            display: none !important;
        }
        #salesInvoicePrintModal .modal-header small.d-md-none,
        #posInvoicePrintModal .modal-header small.d-md-none {
            display: none !important;
        }
        #salesInvoicePrintModal:has(.is-printer-pick) .modal-header small.d-md-none,
        #posInvoicePrintModal:has(.is-printer-pick) .modal-header small.d-md-none {
            display: block !important;
        }
        .bt-printer-row {
            padding: 12px;
            margin-bottom: 8px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        }
        #salesInvoicePrintModal .sales-invoice-print-body,
        #posInvoicePrintModal .pos-invoice-print-body {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #salesInvoicePrintFrame,
        #posInvoicePrintFrame {
            flex: 1 1 auto;
        }
        #salesInvoicePrintModal .sales-invoice-print-footer-controls,
        #posInvoicePrintModal .pos-invoice-print-footer-controls {
            display: none !important;
            width: 100%;
            justify-content: flex-start;
        }
        #salesInvoicePrintModal:has(.is-printer-pick) .sales-invoice-print-footer-controls,
        #posInvoicePrintModal:has(.is-printer-pick) .pos-invoice-print-footer-controls {
            display: flex !important;
        }
        body:has(#salesInvoicePrintModal.show) .floating-calculator-wrapper,
        body:has(#posInvoicePrintModal.show) .floating-calculator-wrapper,
        body:has(#salesInvoicePrintModal.show) .mobile-bottom-nav,
        body:has(#posInvoicePrintModal.show) .mobile-bottom-nav,
        body:has(#salesInvoicePrintModal.show) .sl-fab-new-sale {
            display: none !important;
        }
        #salesInvoicePrintModal,
        #posInvoicePrintModal {
            top: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        #salesInvoicePrintModal.show,
        #posInvoicePrintModal.show {
            display: flex !important;
            align-items: flex-end;
        }
        #salesInvoicePrintModal .modal-dialog,
        #posInvoicePrintModal .modal-dialog {
            margin: 0 !important;
            flex: 1 1 100%;
            width: 100% !important;
            max-width: none !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: 92dvh !important;
            display: block !important;
            transform: none !important;
        }
        #salesInvoicePrintModal .modal-content,
        #posInvoicePrintModal .modal-content {
            margin: 0 !important;
            width: 100% !important;
            max-width: none !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: 92dvh !important;
            display: flex;
            flex-direction: column;
            border: 0;
            border-radius: 18px 18px 0 0 !important;
            overflow: hidden;
        }
        #salesInvoicePrintModal .modal-content,
        #posInvoicePrintModal .modal-content {
            height: 92dvh !important;
        }
        #salesInvoicePrintModal .modal-header,
        #posInvoicePrintModal .modal-header {
            padding: 16px 16px 10px !important;
            border-bottom: 1px solid #f0f1f4;
        }
        #salesInvoicePrintModal .modal-title,
        #posInvoicePrintModal .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: #1b2850;
        }
        #salesInvoicePrintModal .modal-header small.d-md-none,
        #posInvoicePrintModal .modal-header small.d-md-none {
            font-size: 12px;
            color: #8b93a7 !important;
        }
        #salesInvoicePrintModal .phone-print-panel,
        #posInvoicePrintModal .phone-print-panel {
            min-height: 180px;
        }
        #salesInvoicePrintModal .sales-invoice-print-footer,
        #posInvoicePrintModal .pos-invoice-print-footer {
            padding: 10px 16px calc(14px + env(safe-area-inset-bottom, 0px)) !important;
            border-top: 1px solid #f0f1f4 !important;
            background: #fff;
        }
        #salesInvoicePrintModal .sales-invoice-print-footer-actions {
            display: grid !important;
            grid-template-columns: 1fr 1.4fr;
            gap: 10px;
        }
        #salesInvoicePrintModal .sales-invoice-print-footer-actions .btn,
        #posInvoicePrintModal .pos-invoice-print-footer-actions .btn,
        #posInvoicePrintModal .pos-invoice-print-footer .btn-primary,
        #posInvoicePrintModal .pos-invoice-print-footer .btn-secondary {
            min-height: 46px;
            border-radius: 10px !important;
            font-size: 15px;
            font-weight: 600;
            margin: 0;
        }
        #salesInvoicePrintModal .sales-invoice-print-footer .btn-secondary,
        #posInvoicePrintModal .pos-invoice-print-footer .btn-secondary {
            background: #f2f4f7 !important;
            border-color: #f2f4f7 !important;
            color: #344054 !important;
        }
        #salesInvoicePrintModal .sales-invoice-print-footer-controls,
        #posInvoicePrintModal .pos-invoice-print-footer-controls {
            flex: 0 0 auto !important;
        }
        #posInvoicePrintModal .pos-invoice-print-footer-actions {
            display: flex !important;
            width: 100%;
            margin-left: 0;
        }
        #posInvoicePrintModal .pos-invoice-print-footer-actions .btn {
            flex: 1 1 0;
        }
        #salesInvoicePrintModal .modal-header,
        #posInvoicePrintModal .modal-header,
        #salesInvoicePrintModal .modal-footer,
        #posInvoicePrintModal .modal-footer {
            flex-shrink: 0;
        }
        #salesInvoicePrintModal .modal-body,
        #posInvoicePrintModal .modal-body {
            flex: 1 1 auto !important;
            height: auto !important;
            max-height: none !important;
            min-height: 0 !important;
        }
    }
</style>
<script src="{{ asset('js/qz-tray.js') }}?v=20260918"></script>
<script>
(function loadQzTrayLibFallback() {
    if (window.qz) return;
    var urls = [
        'https://cdn.jsdelivr.net/npm/qz-tray@2.2.5/qz-tray.js',
        'https://cdnjs.cloudflare.com/ajax/libs/qz-tray/2.2.4/qz-tray.js'
    ];
    var i = 0;
    function next() {
        if (window.qz || i >= urls.length) return;
        var s = document.createElement('script');
        s.src = urls[i++];
        s.onload = function () {};
        s.onerror = next;
        document.head.appendChild(s);
    }
    next();
})();
</script>
<script>
/**
 * Shared QZ Tray invoice printing — list PC printers, print PDF, thermal auto-cut.
 */
(function(window) {
    'use strict';
    window.__INVOICE_QZ_PRINT_VER = '2026-10-16h';
    window.__QZ_CERT_URL = @json(url('/qz/certificate'));
    window.__QZ_SIGN_URL = @json(url('/qz/sign'));
    window.__QZ_INSTALL_BAT_URL = @json(url('/qz/install-qz-trust.bat'));
    window.__INVOICE_PRINT_PREFS = @json(invoice_print_js_prefs());

    function boot($) {
        if (!$ || window.InvoiceQzPrint) {
            return;
        }

        var BROWSER_PRINTER = '__browser__';
        var state = {
            securityReady: false,
            connecting: null,
            qzDown: false,
            btDevices: {},
            scanMap: {},
            btScan: null,
            phonePrintCache: null,
            billCanvas: null,
            pdfJsLoading: null
        };

        var BT_SERVICES = [
            '000018f0-0000-1000-8000-00805f9b34fb',
            '0000ff00-0000-1000-8000-00805f9b34fb',
            '0000fee7-0000-1000-8000-00805f9b34fb',
            '0000ae30-0000-1000-8000-00805f9b34fb',
            '49535343-fe7d-4ae5-8fa9-9fafd205e455',
            'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
        ];

        // Chrome only reveals nearby devices through its own chooser; filters keep earbuds and phones out of it.
        var PRINTER_NAME_PREFIXES = [
            'Printer', 'printer', 'PRINTER', 'BlueTooth Printer', 'Bluetooth Printer', 'POS', 'Pos', 'MPT', 'MTP', 'RPP',
            'PT-', 'PT2', 'PT3', 'TVS', 'SNBC', 'BPLE', 'XP-', 'Xprinter', 'XPrinter', 'RP', 'Rongta', 'BT-', 'BT58', 'BT80',
            'TM-', 'Epson', 'EPSON', 'Hoin', 'HOIN', 'Gprinter', 'GP-', 'Sewoo', 'SEWOO', 'Zebra', 'ZQ', 'Bixolon', 'SPP-',
            'Inner', 'P58', 'P80', 'M58', 'M80', 'Q2', 'MP', 'Milestone', 'Retsol', 'Atpos', 'ATPOS', 'Everycom', 'EC-', 'TP-',
            'KPC', 'Kpc'
        ];

        function printerRequestOptions() {
            return {
                filters: BT_SERVICES.map(function(uuid) { return { services: [uuid] }; })
                    .concat(PRINTER_NAME_PREFIXES.map(function(prefix) { return { namePrefix: prefix }; })),
                optionalServices: BT_SERVICES
            };
        }

        function isAndroidPhone() {
            var ua = navigator.userAgent || '';
            if (/Android/i.test(ua)) return true;
            try {
                var data = navigator.userAgentData;
                if (data && /Android/i.test(String(data.platform || ''))) return true;
            } catch (e) {}
            if (/iPhone|iPad|iPod/i.test(ua)) return false;
            return navigator.maxTouchPoints > 0 && window.innerWidth <= 900;
        }

        function isIosPhone() {
            var ua = navigator.userAgent || '';
            if (/Android/i.test(ua)) return false;
            if (/iPhone|iPad|iPod/i.test(ua)) return true;
            return navigator.maxTouchPoints > 1 && /Macintosh/i.test(ua);
        }

        function isMobileDevice() {
            var ua = navigator.userAgent || '';
            if (/Android|iPhone|iPad|iPod|Mobile|webOS|BlackBerry|IEMobile|Opera Mini/i.test(ua)) {
                return true;
            }
            return navigator.maxTouchPoints > 1 && /Macintosh/i.test(ua);
        }

        function useBrowserPrint(printerName) {
            if (String(printerName || '').indexOf('bt:') === 0) {
                return false;
            }
            if (printerName && printerName !== BROWSER_PRINTER && window.qz && qz.websocket && qz.websocket.isActive && qz.websocket.isActive()) {
                return false;
            }
            return printerName === BROWSER_PRINTER || state.qzDown || isMobileDevice() || !window.qz;
        }

        function setStatus($el, text, isError) {
            if (!$el || !$el.length) return;
            $el.text(text || '');
            $el.css('color', isError ? '#ea5455' : '#28c76f');
        }

        var BT_MEMORY_FLAG = 'chrome://flags/#enable-web-bluetooth-new-permissions-backend';

        // Without Chrome's Bluetooth permissions backend every page load must ask for the printer again.
        function showBluetoothMemoryHint($status, value) {
            if (!$status || !$status.length) return;
            $status.siblings('.bt-memory-hint').remove();
            if (String(value || '').indexOf('bt:') !== 0 || canListGrantedBluetooth() || state.btDevices[String(value).slice(3)]) return;
            if (!navigator.bluetooth || isAndroidPhone()) return;
            setStatus($status, 'Not connected yet. Chrome asks once to connect when you press Print.', false);
            $status.css('color', '#ff9f43');
            var $link = $('<a href="#" class="small text-nowrap bt-memory-hint ms-1" style="color:#ff9f43;"></a>').text('Stop pair pop-up');
            $link.on('click', function(e) {
                e.preventDefault();
                explainBluetoothMemory();
            });
            $status.after($link);
        }

        function explainBluetoothMemory() {
            var html = '<div style="text-align:left;font-size:14px;line-height:1.6;">'
                + '<p>Chrome forgets Bluetooth printer access every time the page reloads, so it asks again when you press Print. '
                + 'Turn on this one-time Chrome setting so it remembers the printer:</p>'
                + '<ol style="padding-left:18px;margin:0 0 10px;">'
                + '<li>Open a <b>normal</b> Chrome window (not Incognito).</li>'
                + '<li>Paste this in the address bar and press Enter:<br>'
                + '<code id="btMemoryFlag" style="display:inline-block;margin-top:4px;padding:4px 6px;background:#f3f4f6;border-radius:4px;word-break:break-all;">' + BT_MEMORY_FLAG + '</code></li>'
                + '<li>Set <b>Use the new permissions backend for Web Bluetooth</b> to <b>Enabled</b> and click <b>Relaunch</b>.</li>'
                + '<li>Open the print window, tap <b>Bluetooth</b>, pick the printer once.</li>'
                + '</ol>'
                + '<p style="margin:0;">After that the printer is selected automatically and prints without the pop-up.</p>'
                + '</div>';
            if (typeof Swal === 'undefined') {
                window.prompt('Paste this in the Chrome address bar, set it to Enabled, then relaunch Chrome:', BT_MEMORY_FLAG);
                return;
            }
            Swal.fire({
                title: 'Stop the Bluetooth pop-up',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Copy setting link',
                cancelButtonText: 'Close',
                confirmButtonColor: '#ff9f43'
            }).then(function(res) {
                if (!res.isConfirmed) return;
                var copied = navigator.clipboard && navigator.clipboard.writeText
                    ? navigator.clipboard.writeText(BT_MEMORY_FLAG)
                    : Promise.reject();
                copied.then(function() {
                    Swal.fire({ icon: 'success', title: 'Copied', text: 'Paste it in the Chrome address bar.', timer: 1800, showConfirmButton: false });
                }, function() {
                    window.prompt('Copy this:', BT_MEMORY_FLAG);
                });
            });
        }

        function isThermalPrinterName(name) {
            var n = String(name || '').toLowerCase();
            return /thermal|receipt|pos[\s_-]?printer|tvs|snbc|lp46|dlite|epson\s*tm|tm-t|star\s*tsp|bixolon|citizen|rongta|xprinter|gprinter|80\s*mm|58\s*mm|billing|godex|tsc\s*t|zebra\s*zd/.test(n);
        }

        function setupSecurity() {
            if (!window.qz || state.securityReady) return;

            // Signed requests — stops "anonymous request" Allow/Block every print
            qz.security.setCertificatePromise(function(resolve, reject) {
                fetch(window.__QZ_CERT_URL || '/qz/certificate', {
                    cache: 'no-store',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'text/plain' }
                }).then(function(res) {
                    if (!res.ok) throw new Error('Certificate HTTP ' + res.status);
                    return res.text();
                }).then(resolve).catch(reject);
            });

            if (typeof qz.security.setSignatureAlgorithm === 'function') {
                qz.security.setSignatureAlgorithm('SHA512');
            }

            qz.security.setSignaturePromise(function(toSign) {
                return function(resolve, reject) {
                    var csrf = '';
                    try {
                        var meta = document.querySelector('meta[name="csrf-token"]');
                        csrf = meta ? (meta.getAttribute('content') || '') : '';
                    } catch (e) {}
                    fetch(window.__QZ_SIGN_URL || '/qz/sign', {
                        method: 'POST',
                        cache: 'no-store',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'Accept': 'text/plain',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: 'request=' + encodeURIComponent(toSign) + (csrf ? ('&_token=' + encodeURIComponent(csrf)) : '')
                    }).then(function(res) {
                        if (!res.ok) throw new Error('Sign HTTP ' + res.status);
                        return res.text();
                    }).then(resolve).catch(reject);
                };
            });

            state.securityReady = true;
        }

        function ensureConnected() {
            if (!window.qz) {
                return Promise.reject(new Error('QZ library not loaded. Upload public/js/qz-tray.js'));
            }
            setupSecurity();
            if (qz.websocket.isActive()) {
                return Promise.resolve();
            }
            if (state.connecting) {
                return state.connecting;
            }
            state.connecting = qz.websocket.connect({
                retries: 5,
                delay: 1
            }).then(function() {
                state.connecting = null;
            }).catch(function(err) {
                state.connecting = null;
                throw err;
            });
            return state.connecting;
        }

        function fillPrinterSelect($sel, printers, storageKey) {
            var saved = '';
            try {
                saved = localStorage.getItem(storageKey || 'posSelectedPrinter') || '';
            } catch (e) {}
            $sel.empty();
            if (!printers || !printers.length) {
                $sel.append($('<option></option>').val('').text('No printers found'));
                return;
            }
            $sel.append($('<option></option>').val('').text('Select printer…'));
            var preferred = '';
            printers.forEach(function(name) {
                var label = isThermalPrinterName(name) ? (name + ' (thermal)') : name;
                $sel.append($('<option></option>').val(name).text(label));
                if (saved && saved === name) {
                    preferred = name;
                } else if (!preferred && isThermalPrinterName(name)) {
                    preferred = name;
                }
            });
            if (preferred) {
                $sel.val(preferred);
            } else if (saved && printers.indexOf(saved) >= 0) {
                $sel.val(saved);
            }
            syncAutoCutForPrinter($sel.val());
        }

        function printPrefs() {
            return window.__INVOICE_PRINT_PREFS || {};
        }

        function printCopies() {
            // Always 1 for invoice thermal/Bluetooth/QZ — live "Number of copies = 2" was printing two bills.
            return 1;
        }

        // A newly saved default replaces this device's last printer once; later picks in the print window still stick.
        (function seedDefaultPrinter() {
            var preferred = String(printPrefs().defaultPrinter || '').trim();
            if (!preferred) return;
            try {
                if (localStorage.getItem('posDefaultPrinterApplied') !== preferred || !localStorage.getItem('posSelectedPrinter')) {
                    localStorage.setItem('posSelectedPrinter', preferred);
                    localStorage.setItem('posDefaultPrinterApplied', preferred);
                }
            } catch (e) {}
        })();

        function defaultPrinterValue() {
            return String(printPrefs().defaultPrinter || '').trim();
        }

        function hasOption($sel, value) {
            return $sel.find('option').filter(function() { return this.value === value; }).length > 0;
        }

        // Resolves true when the printer is advertising nearby; browsers without the check count a granted printer as nearby.
        function bluetoothDeviceNearby(device, ms) {
            if (!device || typeof device.watchAdvertisements !== 'function') return Promise.resolve(true);
            return new Promise(function(resolve) {
                var done = false;
                var abort = typeof AbortController === 'function' ? new AbortController() : null;
                function finish(value) {
                    if (done) return;
                    done = true;
                    try { device.removeEventListener('advertisementreceived', onAdvert); } catch (e) {}
                    try { if (abort) abort.abort(); else if (device.unwatchAdvertisements) device.unwatchAdvertisements(); } catch (e2) {}
                    resolve(value);
                }
                function onAdvert() { finish(true); }
                try { device.addEventListener('advertisementreceived', onAdvert); } catch (e) {}
                Promise.resolve(device.watchAdvertisements(abort ? { signal: abort.signal } : undefined)).catch(function() { finish(true); });
                setTimeout(function() { finish(!!(device.gatt && device.gatt.connected)); }, ms || 3000);
            });
        }

        // The saved default wins whenever it is listed and reachable; otherwise the current pick stays.
        function canListGrantedBluetooth() {
            return !!(navigator.bluetooth && typeof navigator.bluetooth.getDevices === 'function');
        }

        function applyDefaultPrinter($sel) {
            var def = defaultPrinterValue();
            if (!def || !$sel || !$sel.length) return Promise.resolve(false);
            // Without getDevices() the browser cannot list an earlier-picked printer after a reload, so list the
            // saved default by name; pressing Print reconnects to it.
            if (!hasOption($sel, def) && def.indexOf('bt:') === 0 && !canListGrantedBluetooth()
                && navigator.bluetooth && typeof navigator.bluetooth.requestDevice === 'function') {
                var label = String(printPrefs().defaultPrinterLabel || '').trim() || 'Bluetooth printer';
                $sel.append($('<option></option>').val(def).text(label + ' (Bluetooth · connects on Print)'));
                $sel.prop('disabled', false).val(def);
                return Promise.resolve(true);
            }
            if (!hasOption($sel, def)) return Promise.resolve(false);
            if (def.indexOf('bt:') !== 0) {
                $sel.val(def);
                return Promise.resolve(true);
            }
            var row = state.btDevices[def.slice(3)];
            var device = row && (row.device || row);
            if (row && row.transport === 'bridge') {
                $sel.val(def);
                return Promise.resolve(true);
            }
            return bluetoothDeviceNearby(device, 3000).then(function(nearby) {
                if (nearby) {
                    $sel.val(def);
                } else if ($sel.val() === def) {
                    var fallback = '';
                    $sel.find('option').each(function() {
                        var v = this.value;
                        if (!v || v === def || /^(bt|serial):/.test(v)) return;
                        if (!fallback || (isThermalPrinterName(v) && !isThermalPrinterName(fallback))) fallback = v;
                    });
                    $sel.val(fallback);
                }
                return nearby;
            });
        }

        function syncAutoCutForPrinter(value) {
            var on = /^(bt|serial):/.test(String(value || '')) || !!printPrefs().autoCut;
            $('#salesThermalAutoCut, #posThermalAutoCut').prop('checked', on);
            try { localStorage.setItem('posThermalAutoCut', on ? '1' : '0'); } catch (e) {}
        }

        function focusWifiTab(listSel) {
            var $list = listSel ? $(listSel) : $();
            var $panel = $list.closest('.phone-print-panel');
            if (!$panel.length) return;
            $panel.find('.phone-print-tab').removeClass('is-active');
            $panel.find('[data-tab="wifi"]').addClass('is-active');
            $panel.find('[data-print-pane]').addClass('d-none');
            $panel.find('[data-print-pane="wifi"]').removeClass('d-none');
        }

        function paintWifiPrinters(listSel, printers, selected, onSelect) {
            var $list = listSel ? $(listSel) : $();
            if (!$list.length) return;
            $list.empty();
            if (!printers || !printers.length) {
                $list.append('<div class="bt-printer-empty">No WIFI printer found.</div>');
                return;
            }
            printers.forEach(function(name) {
                var $row = $('<div class="bt-printer-row"></div>');
                if (selected === name) $row.addClass('is-selected');
                $row.append('<div class="bt-printer-meta"><div class="bt-printer-name"></div><div class="bt-printer-id"></div></div>');
                $row.find('.bt-printer-name').text(name);
                $row.find('.bt-printer-id').text('WIFI');
                var $btn = $('<button type="button" class="btn btn-sm bt-printer-select"></button>');
                $btn.text(selected === name ? 'Selected' : 'Select');
                $btn.on('click', function() { onSelect(name); });
                $row.append($btn);
                $list.append($row);
            });
        }

        function refreshPrinters(opts) {
            opts = opts || {};
            var $sel = $(opts.select);
            var $status = $(opts.status);
            var $install = $(opts.installLink);
            var storageKey = opts.storageKey || 'posSelectedPrinter';

            $sel.prop('disabled', true);
            $sel.html('<option value="">Loading printers…</option>');
            if ($install.length) $install.hide();
            setStatus($status, 'Connecting…', false);

            if (isAndroidPhone()) {
                return refreshMobilePrinters($sel, $status, $install, storageKey, !!opts.pick, opts.list || '', opts.wifiList || '');
            }

            if (isMobileDevice()) {
                var qzReady = window.qz && qz.websocket && typeof qz.websocket.isActive === 'function' && qz.websocket.isActive();
                if (!qzReady) {
                    return withTimeout(run(), 1800, []).then(function(printers) {
                        if (printers && printers.length) return printers;
                        return refreshMobilePrinters($sel, $status, $install, storageKey, !!opts.pick, opts.list || '', opts.wifiList || '');
                    });
                }
            }

            function run() {
                return ensureConnected()
                    .then(function() {
                        return qz.printers.find();
                    })
                    .then(function(printers) {
                        state.qzDown = false;
                        fillPrinterSelect($sel, printers || [], storageKey);
                        $sel.prop('disabled', false);
                        function chooseWifi(name) {
                            $sel.val(name);
                            try { localStorage.setItem(storageKey, name); } catch (e) {}
                            paintWifiPrinters(opts.wifiList, printers || [], name, chooseWifi);
                            syncAutoCutForPrinter(name);
                            setStatus($status, 'Selected ' + name, false);
                        }
                        paintWifiPrinters(opts.wifiList, printers || [], $sel.val(), chooseWifi);
                        if ((printers || []).length) focusWifiTab(opts.wifiList);
                        restoreSessionBluetooth($sel, storageKey);
                        return mergeGrantedBluetooth($sel, storageKey).then(function() {
                            return applyDefaultPrinter($sel);
                        }).then(function() {
                            syncAutoCutForPrinter($sel.val());
                            if (/^(bt|serial):/.test(String($sel.val() || ''))) {
                                setStatus($status, 'Selected ' + ($sel.find('option:selected').text() || 'Bluetooth printer') + '. Press Print.', false);
                                showBluetoothMemoryHint($status, $sel.val());
                            } else {
                                setStatus($status, 'Ready · ' + (printers ? printers.length : 0) + ' printer(s)', false);
                            }
                            return printers;
                        });
                    })
                    .catch(function(err) {
                        console.warn('QZ printers error', err);
                        state.qzDown = true;
                        $sel.empty();
                        $sel.append($('<option></option>').val(BROWSER_PRINTER).text(isMobileDevice() ? 'Phone print' : 'This device'));
                        $sel.val(BROWSER_PRINTER);
                        $sel.prop('disabled', false);
                        if ($install.length) {
                            if (isMobileDevice()) $install.hide();
                            else $install.show();
                        }
                        if (!isMobileDevice()) {
                            setStatus($status, 'Browser print', false);
                        }
                        restoreSessionBluetooth($sel, storageKey);
                        return mergeGrantedBluetooth($sel, storageKey).then(function() {
                            return applyDefaultPrinter($sel);
                        }).then(function() {
                            syncAutoCutForPrinter($sel.val());
                            return [];
                        });
                    });
            }

            if (!window.qz) {
                return new Promise(function(resolve, reject) {
                    var tries = 0;
                    var t = setInterval(function() {
                        tries++;
                        if (window.qz || tries > 50) {
                            clearInterval(t);
                            run().then(resolve).catch(reject);
                        }
                    }, 100);
                });
            }
            return run();
        }

        var PHONE_BRIDGE = 'http://127.0.0.1:17845';

        function rememberBluetoothDevice(device) {
            if (!device || !device.id) return;
            state.btDevices[device.id] = device;
        }

        function isBridgePrinterId(id) {
            return /^[0-9A-Fa-f]{2}(:[0-9A-Fa-f]{2}){5}$/.test(String(id || ''));
        }

        function withTimeout(promise, ms, fallback) {
            return new Promise(function(resolve) {
                var settled = false;
                var timer = setTimeout(function() {
                    if (settled) return;
                    settled = true;
                    resolve(fallback);
                }, ms);
                Promise.resolve(promise).then(function(value) {
                    if (settled) return;
                    settled = true;
                    clearTimeout(timer);
                    resolve(value);
                }, function() {
                    if (settled) return;
                    settled = true;
                    clearTimeout(timer);
                    resolve(fallback);
                });
            });
        }

        function setBluetoothListMessage(listSel, text) {
            var $list = listSel ? $(listSel) : $();
            if (!$list.length) return;
            $list.empty();
            $list.append($('<div class="bt-printer-empty"></div>').text(text));
        }

        function fetchBridgePrinters() {
            var controller = typeof AbortController === 'function' ? new AbortController() : null;
            var timer = controller ? setTimeout(function() { controller.abort(); }, 8000) : null;
            return fetch(PHONE_BRIDGE + '/printers', {
                method: 'GET',
                signal: controller ? controller.signal : undefined
            }).then(function(res) {
                if (!res.ok) return [];
                return res.json();
            }).then(function(body) {
                var list = (body && body.printers) || [];
                return list.map(function(item) {
                    return {
                        id: item.id,
                        name: item.name || 'Bluetooth device',
                        transport: 'bridge'
                    };
                }).filter(function(item) { return item.id; });
            }).catch(function() {
                return [];
            }).then(function(list) {
                if (timer) clearTimeout(timer);
                return list;
            });
        }

        function bytesToBase64(bytes) {
            var text = '';
            for (var i = 0; i < bytes.length; i++) text += String.fromCharCode(bytes[i]);
            return btoa(text);
        }

        function printViaBridge(deviceId, pdfUrl, autoCut, $status, $btn, storageKey) {
            var device = state.btDevices[deviceId];
            if ($btn && $btn.length) $btn.prop('disabled', true);
            setStatus($status, 'Printing to ' + ((device && device.name) || 'printer') + '…', false);
            try { localStorage.setItem(storageKey || 'posSelectedPrinter', 'bt:' + deviceId); } catch (e) {}
            return fetch(receiptJsonUrl(pdfUrl), { credentials: 'same-origin' }).then(function(res) {
                if (!res.ok) throw new Error('Could not load the bill');
                return res.json();
            }).then(function(receipt) {
                return fetch(PHONE_BRIDGE + '/print', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: deviceId,
                        data: bytesToBase64(buildEscPos(receipt, autoCut))
                    })
                });
            }).then(function(res) {
                return res.json().catch(function() { return {}; }).then(function(body) {
                    if (!res.ok || (body && body.ok === false)) {
                        throw new Error((body && body.error) || 'Bluetooth print failed');
                    }
                    setStatus($status, 'Printed', false);
                    if ($btn && $btn.length) $btn.prop('disabled', false);
                });
            }).catch(function(err) {
                setStatus($status, (err && err.message) ? err.message : 'Bluetooth print failed', true);
                if ($btn && $btn.length) $btn.prop('disabled', false);
                return Promise.reject(err);
            });
        }

        function phoneTransportOption() {
            // Android: never use RawBT (bt:phone). Use Web Bluetooth device picker instead.
            if (isIosPhone()) {
                return {
                    value: 'ios:print',
                    name: 'Bluetooth printer',
                    hint: 'Opens the iPhone print list'
                };
            }
            return null;
        }

        function rememberPrinterValue($sel, storageKey, value, label) {
            if ($sel && $sel.length && value) {
                var exists = false;
                $sel.find('option').each(function() {
                    if (this.value === value) exists = true;
                });
                if (!exists) {
                    $sel.append($('<option></option>').val(value).text(label || value));
                }
                $sel.val(value);
                $sel.prop('disabled', false);
            }
            try { localStorage.setItem(storageKey || 'posSelectedPrinter', value); } catch (e) {}
            syncAutoCutForPrinter(value);
        }

        function savedWifiHost() {
            var host = '';
            try { host = localStorage.getItem('posWifiPrinterHost') || ''; } catch (e) {}
            if (!/^\d{1,3}(\.\d{1,3}){3}$/.test(host)) host = '192.168.4.1';
            return host;
        }

        function renderBluetoothRows(listSel, devices, selectedValue, onSelect) {
            var $list = listSel ? $(listSel) : $();
            if (!$list.length) return;
            $list.empty();
            var $panel = $list.closest('.phone-print-panel');
            var storageKey = $panel.attr('data-storage') || 'posSelectedPrinter';
            var $sel = $($panel.attr('data-select') || '');
            var phoneOpt = phoneTransportOption();
            if (phoneOpt) {
                var phoneSelected = selectedValue === phoneOpt.value;
                var $phone = $('<div class="bt-printer-row"></div>');
                if (phoneSelected) $phone.addClass('is-selected');
                $phone.append('<div class="bt-printer-meta"><div class="bt-printer-name"></div><div class="bt-printer-id"></div></div>');
                $phone.find('.bt-printer-name').text(phoneOpt.name);
                $phone.find('.bt-printer-id').text(phoneOpt.hint);
                var $phoneBtn = $('<button type="button" class="btn btn-sm bt-printer-select"></button>');
                $phoneBtn.text(phoneSelected ? 'Selected' : 'Select');
                $phoneBtn.on('click', function() {
                    rememberPrinterValue($sel, storageKey, phoneOpt.value, phoneOpt.name);
                    renderBluetoothRows(listSel, devices, phoneOpt.value, onSelect);
                });
                $phone.append($phoneBtn);
                $list.append($phone);
            }
            if (!devices.length) {
                if (isAndroidPhone()) {
                    $list.append('<div class="bt-printer-note">Tap Choose nearby to open the Bluetooth list and pick your receipt printer. Leave the printer Wi‑Fi off.</div>');
                    var $actions = $('<div class="bt-printer-actions"></div>');
                    if (navigator.bluetooth && typeof navigator.bluetooth.requestDevice === 'function') {
                        var $near = $('<button type="button" class="btn btn-sm bt-printer-select"></button>').text('Choose nearby');
                        $near.on('click', function() {
                            pickNearbyPrinter({
                                select: $panel.attr('data-select'),
                                status: $panel.attr('data-status'),
                                storageKey: storageKey,
                                list: listSel
                            });
                        });
                        $actions.append($near);
                    } else {
                        $list.append('<div class="bt-printer-note">Use Chrome to choose a Bluetooth printer.</div>');
                    }
                    $list.append($actions);
                } else if (isIosPhone()) {
                    $list.append('<div class="bt-printer-note">Tap Select, then Print. Choose the printer in the iPhone list.</div>');
                }
            }
            devices.forEach(function(device) {
                var id = device.id || '';
                var name = device.name || 'Bluetooth printer';
                var value = 'bt:' + id;
                var selected = selectedValue === value;
                var $row = $('<div class="bt-printer-row"></div>');
                if (selected) $row.addClass('is-selected');
                $row.append(
                    '<div class="bt-printer-meta"><div class="bt-printer-name"></div><div class="bt-printer-id"></div></div>'
                );
                $row.find('.bt-printer-name').text(name);
                $row.find('.bt-printer-id').text(id);
                var $btn = $('<button type="button" class="btn btn-sm bt-printer-select"></button>');
                $btn.text(selected ? 'Selected' : 'Select');
                $btn.on('click', function() {
                    onSelect(device);
                });
                $row.append($btn);
                $list.append($row);
            });
        }

        function fillBluetoothSelect($sel, devices, storageKey, $status, listSel) {
            var saved = '';
            try {
                saved = localStorage.getItem(storageKey || 'posSelectedPrinter') || '';
            } catch (e) {}
            $sel.empty();
            devices.forEach(function(device) {
                rememberBluetoothDevice(device);
                var label = device.name || 'Bluetooth printer';
                $sel.append($('<option></option>').val('bt:' + device.id).text(label));
            });
            if (!devices.length) {
                $sel.empty();
                var phoneOpt = phoneTransportOption();
                var keepWifi = String(saved).indexOf('wifi:') === 0;
                // Clear old RawBT selection so Print opens the Bluetooth picker instead.
                if (saved === 'bt:phone') {
                    try { localStorage.removeItem(storageKey || 'posSelectedPrinter'); } catch (eClr) {}
                    saved = '';
                }
                if (keepWifi) {
                    if (phoneOpt) $sel.append($('<option></option>').val(phoneOpt.value).text(phoneOpt.name));
                    rememberPrinterValue($sel, storageKey, 'wifi:' + savedWifiHost(), 'Wi‑Fi printer');
                } else if (phoneOpt) {
                    rememberPrinterValue($sel, storageKey, phoneOpt.value, phoneOpt.name);
                } else {
                    $sel.append($('<option></option>').val('').text('Choose a Bluetooth printer'));
                }
                $sel.prop('disabled', false);
                renderBluetoothRows(listSel, [], keepWifi ? '' : (phoneOpt ? phoneOpt.value : ''), function() {});
                if (!keepWifi) {
                    setStatus($status, 'Tap Choose nearby / Bluetooth to pick your printer.', false);
                }
                return;
            }
            // Do not add RawBT (bt:phone) into the printer list.
            if (saved) {
                var hasSaved = false;
                $sel.find('option').each(function() {
                    if (this.value === saved) hasSaved = true;
                });
                if (hasSaved) $sel.val(saved);
            }
            $sel.prop('disabled', false);
            function choose(device) {
                rememberBluetoothDevice(device);
                $sel.val('bt:' + device.id);
                try { localStorage.setItem(storageKey || 'posSelectedPrinter', 'bt:' + device.id); } catch (e) {}
                syncAutoCutForPrinter($sel.val());
                renderBluetoothRows(listSel, devices, $sel.val(), choose);
            }
            renderBluetoothRows(listSel, devices, $sel.val(), choose);
            syncAutoCutForPrinter($sel.val());
            setStatus($status, 'Ready · ' + devices.length + ' Bluetooth printer(s). Tap Select, then Print.', false);
        }

        function paintPhoneWifiPrinters(listSel, storageKey, $sel) {
            var $list = listSel ? $(listSel) : $();
            if (!$list.length) return;
            var host = savedWifiHost();
            var value = 'wifi:' + host;
            var saved = '';
            try { saved = localStorage.getItem(storageKey || 'posSelectedPrinter') || ''; } catch (e) {}
            var selected = String(saved).indexOf('wifi:') === 0;
            $list.empty();
            var $ip = $('<div class="bt-wifi-ip-row"></div>');
            $ip.append('<label for="' + String(listSel).replace('#', '') + 'Ip">Printer IP</label>');
            var $input = $('<input type="text" class="form-control form-control-sm bt-wifi-ip" inputmode="decimal" autocomplete="off">');
            $input.attr('id', String(listSel).replace('#', '') + 'Ip');
            $input.val(host);
            $input.on('change blur', function() {
                var next = String($input.val() || '').trim();
                if (!/^\d{1,3}(\.\d{1,3}){3}$/.test(next)) {
                    $input.val(savedWifiHost());
                    return;
                }
                try { localStorage.setItem('posWifiPrinterHost', next); } catch (e) {}
                if (selected) {
                    rememberPrinterValue($sel, storageKey, 'wifi:' + next, 'Wi‑Fi printer');
                }
                paintPhoneWifiPrinters(listSel, storageKey, $sel);
            });
            $ip.append($input);
            $list.append($ip);
            var note = isIosPhone() && !isAndroidPhone()
                ? 'Tap Select, then Print. Choose the Wi‑Fi printer in the iPhone list.'
                : 'The printer Wi‑Fi turns the internet off. Use Bluetooth unless this phone is already on the printer Wi‑Fi.';
            $list.append($('<div class="bt-printer-note"></div>').text(note));
            var $row = $('<div class="bt-printer-row"></div>');
            if (selected) $row.addClass('is-selected');
            $row.append('<div class="bt-printer-meta"><div class="bt-printer-name"></div><div class="bt-printer-id"></div></div>');
            $row.find('.bt-printer-name').text('Wi‑Fi printer');
            $row.find('.bt-printer-id').text(host + ' · port 9100');
            var $btn = $('<button type="button" class="btn btn-sm bt-printer-select"></button>');
            $btn.text(selected ? 'Selected' : 'Select');
            $btn.on('click', function() {
                var next = String($input.val() || '').trim();
                if (!/^\d{1,3}(\.\d{1,3}){3}$/.test(next)) next = savedWifiHost();
                try { localStorage.setItem('posWifiPrinterHost', next); } catch (e) {}
                rememberPrinterValue($sel, storageKey, 'wifi:' + next, 'Wi‑Fi printer');
                paintPhoneWifiPrinters(listSel, storageKey, $sel);
            });
            $row.append($btn);
            $list.append($row);
        }

        function refreshMobilePrinters($sel, $status, $install, storageKey, pick, listSel, wifiList) {
            state.qzDown = true;
            if ($install.length) $install.hide();
            var phoneOpt = phoneTransportOption();
            if (phoneOpt) {
                rememberPrinterValue($sel, storageKey, phoneOpt.value, phoneOpt.name);
                renderBluetoothRows(listSel, [], phoneOpt.value, function() {});
                setStatus($status, 'Bluetooth is ready. Leave the printer Wi‑Fi off.', false);
            }
            paintPhoneWifiPrinters(wifiList, storageKey, $sel);

            var found = {};
            function addDevices(list) {
                (list || []).forEach(function(device) {
                    if (!device || !device.id) return;
                    found[device.id] = device;
                });
                var rows = Object.keys(found).map(function(id) { return found[id]; });
                if (rows.length) fillBluetoothSelect($sel, rows, storageKey, $status, listSel);
                return rows;
            }

            function loadGranted() {
                if (!navigator.bluetooth || typeof navigator.bluetooth.getDevices !== 'function') {
                    return Promise.resolve([]);
                }
                return navigator.bluetooth.getDevices().then(function(devices) {
                    return (devices || []).map(function(device) {
                        return {
                            id: device.id,
                            name: device.name || 'Bluetooth device',
                            device: device,
                            transport: 'web'
                        };
                    });
                }).catch(function() { return []; });
            }

            var canScanInPage = !!(navigator.bluetooth && typeof navigator.bluetooth.requestLEScan === 'function');
            var chooser = Promise.resolve([]);
            if (pick && !canScanInPage && navigator.bluetooth && typeof navigator.bluetooth.requestDevice === 'function') {
                setStatus($status, 'Choose a nearby printer', false);
                chooser = navigator.bluetooth.requestDevice(printerRequestOptions()).then(function(device) {
                    var row = {
                        id: device.id,
                        name: device.name || 'Bluetooth device',
                        device: device,
                        transport: 'web'
                    };
                    try { localStorage.setItem(storageKey || 'posSelectedPrinter', 'bt:' + device.id); } catch (e) {}
                    return addDevices([row]);
                }).catch(function() {
                    return [];
                });
            }

            var bridgeDone = fetchBridgePrinters().then(addDevices);
            var scanDone = withTimeout(startInPageScan(), 7000, []).then(addDevices);
            var grantedDone = loadGranted().then(addDevices);

            return Promise.all([bridgeDone, scanDone, grantedDone, chooser]).then(function() {
                var rows = Object.keys(found).map(function(id) { return found[id]; });
                if (!rows.length) {
                    fillBluetoothSelect($sel, [], storageKey, $status, listSel);
                }
                paintPhoneWifiPrinters(wifiList, storageKey, $sel);
                return rows;
            });
        }

        function startInPageScan() {
            if (!navigator.bluetooth || typeof navigator.bluetooth.requestLEScan !== 'function') {
                return Promise.resolve([]);
            }
            var map = {};
            var handler = function(event) {
                var device = event && event.device;
                if (!device || !device.id) return;
                map[device.id] = {
                    id: device.id,
                    name: device.name || event.name || 'Unknown device',
                    device: device,
                    transport: 'web'
                };
            };
            var scanPromise;
            try {
                scanPromise = navigator.bluetooth.requestLEScan({
                    acceptAllAdvertisements: true,
                    keepRepeatedDevices: true
                });
            } catch (e) {
                return Promise.resolve([]);
            }
            try { navigator.bluetooth.addEventListener('advertisementreceived', handler); } catch (e2) {}
            return Promise.resolve(scanPromise).then(function(scan) {
                return new Promise(function(resolve) {
                    setTimeout(function() {
                        try { if (scan && scan.stop) scan.stop(); } catch (e) {}
                        try { navigator.bluetooth.removeEventListener('advertisementreceived', handler); } catch (e2) {}
                        resolve(Object.keys(map).map(function(id) { return map[id]; }));
                    }, 6000);
                });
            }).catch(function() {
                try { navigator.bluetooth.removeEventListener('advertisementreceived', handler); } catch (e) {}
                return [];
            });
        }

        function asciiText(value) {
            return String(value == null ? '' : value).replace(/[^\x20-\x7E]/g, '').trim();
        }

        function moneyText(amount) {
            var n = Number(amount);
            if (!isFinite(n)) n = 0;
            return n.toFixed(2);
        }

        function buildEscPos(receipt, autoCut) {
            var bytes = [];
            function raw() {
                for (var i = 0; i < arguments.length; i++) bytes.push(arguments[i] & 255);
            }
            function text(line) {
                var s = asciiText(line);
                for (var i = 0; i < s.length; i++) bytes.push(s.charCodeAt(i));
                bytes.push(10);
            }
            raw(0x1B, 0x40);
            raw(0x1B, 0x61, 0x01);
            raw(0x1B, 0x45, 0x01);
            text(receipt.shop || 'Invoice');
            raw(0x1B, 0x45, 0x00);
            if (receipt.phone) text('Ph: ' + receipt.phone);
            if (receipt.gst) text('GST: ' + receipt.gst);
            text('Invoice ' + (receipt.invoice || ''));
            if (receipt.date) text(receipt.date);
            raw(0x1B, 0x61, 0x00);
            text('--------------------------------');
            (receipt.items || []).forEach(function(item, index) {
                text((index + 1) + '. ' + (item.name || 'Item'));
                var qty = String(item.qty);
                if (item.unit) qty += item.unit;
                var left = qty + ' x ' + moneyText(item.price);
                var right = moneyText(item.amount);
                var gap = 32 - left.length - right.length;
                if (gap < 1) gap = 1;
                text(left + new Array(gap + 1).join(' ') + right);
            });
            text('--------------------------------');
            text('Total' + new Array(32 - 5 - moneyText(receipt.total).length + 1).join(' ') + moneyText(receipt.total));
            text('Received' + new Array(32 - 8 - moneyText(receipt.paid).length + 1).join(' ') + moneyText(receipt.paid));
            if (receipt.payment) text('Payment: ' + receipt.payment);
            text('');
            text('Thanks for business with us!');
            appendAutoCut(bytes, autoCut);
            return new Uint8Array(bytes);
        }

        function appendAutoCut(out, enabled) {
            out.push(0x1B, 0x32);
            out.push(0x1B, 0x64, 0x06);
            if (printPrefs().cashDrawer) out.push(0x1B, 0x70, 0x00, 0x19, 0xFA);
            if (!enabled) return;
            // One cut only — full + partial (and ESC i) was cutting the paper twice.
            out.push(0x1D, 0x56, 0x00);
        }

        function phoneCutBytes() {
            return new Uint8Array([
                0x1B, 0x32,
                0x0A, 0x0A, 0x0A, 0x0A, 0x0A,
                0x1D, 0x56, 0x00
            ]);
        }

        function wantsAutoCut(passed) {
            if (passed) return true;
            return $('#salesThermalAutoCut, #posThermalAutoCut').is(':checked');
        }

        function findWritableCharacteristic(server) {
            var i = 0;
            function next() {
                if (i >= BT_SERVICES.length) return Promise.resolve(null);
                var uuid = BT_SERVICES[i++];
                return server.getPrimaryService(uuid).then(function(service) {
                    return service.getCharacteristics();
                }).then(function(chars) {
                    for (var c = 0; c < chars.length; c++) {
                        var props = chars[c].properties || {};
                        if (props.write || props.writeWithoutResponse) return chars[c];
                    }
                    return next();
                }).catch(function() {
                    return next();
                });
            }
            return next();
        }

        function writePrinterBytes(characteristic, bytes) {
            function writePart(part) {
                var props = characteristic.properties || {};
                var write = (props.writeWithoutResponse && characteristic.writeValueWithoutResponse)
                    ? characteristic.writeValueWithoutResponse(part)
                    : characteristic.writeValue(part);
                return Promise.resolve(write).catch(function() {
                    if (part.length <= 20) {
                        throw new Error('The printer rejected the bill');
                    }
                    var mid = Math.ceil(part.length / 2);
                    return writePart(part.slice(0, mid)).then(function() {
                        return writePart(part.slice(mid));
                    });
                });
            }
            var props = characteristic.properties || {};
            var noResponse = !!(props.writeWithoutResponse && characteristic.writeValueWithoutResponse);
            // Larger chunks = faster Bluetooth transfer (QR raster especially).
            var size = noResponse ? 180 : 120;
            var offset = 0;
            var sent = 0;
            function next() {
                if (offset >= bytes.length) return Promise.resolve();
                var part = bytes.slice(offset, offset + size);
                offset += size;
                sent++;
                return writePart(part).then(function() {
                    // Acknowledged writes pace themselves; unacknowledged ones get a short breather.
                    if (noResponse && sent % 20 === 0) {
                        return new Promise(function(resolve) { setTimeout(resolve, 6); });
                    }
                }).then(next);
            }
            return next();
        }

        function cleanPdfUrl(pdfUrl) {
            var url = new URL(absoluteUrl(pdfUrl));
            url.searchParams.delete('bt_receipt');
            url.hash = '';
            return url.href;
        }

        function loadPdfJs() {
            function readyLib() {
                var lib = window.pdfjsLib;
                if ((!lib || !lib.getDocument) && window.module && window.module.exports && window.module.exports.getDocument) {
                    lib = window.module.exports;
                    window.pdfjsLib = lib;
                }
                return (lib && lib.getDocument) ? lib : null;
            }
            var existing = readyLib();
            if (existing) return Promise.resolve(existing);
            if (state.pdfJsLoading) return state.pdfJsLoading;
            var localJs = "{{ asset('js/pdfjs/pdf.min.js') }}?v=20261006b";
            var localWorker = "{{ asset('js/pdfjs/pdf.worker.min.js') }}?v=20261006b";
            var cdnJs = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
            var cdnWorker = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            function loadScript(src) {
                return new Promise(function(resolve, reject) {
                    var script = document.createElement('script');
                    script.src = src;
                    script.onload = function() { resolve(); };
                    script.onerror = function() { reject(new Error('missing')); };
                    document.head.appendChild(script);
                });
            }
            function useLib(workerSrc) {
                var lib = readyLib();
                if (!lib) throw new Error('The selected template could not be prepared');
                lib.GlobalWorkerOptions.workerSrc = workerSrc;
                return lib;
            }
            state.pdfJsLoading = loadScript(localJs).then(function() {
                return useLib(localWorker);
            }).catch(function() {
                return loadScript(cdnJs).then(function() {
                    return useLib(cdnWorker);
                });
            }).catch(function() {
                state.pdfJsLoading = null;
                throw new Error('The selected template could not be prepared');
            });
            return state.pdfJsLoading;
        }

        function trimReceiptCanvas(canvas) {
            var width = canvas.width;
            var height = canvas.height;
            var ctx = canvas.getContext('2d', { willReadFrequently: true });
            var img = ctx.getImageData(0, 0, width, height);
            var last = 0;
            for (var y = height - 1; y >= 0; y--) {
                var ink = false;
                for (var x = 0; x < width; x += 4) {
                    var i = (y * width + x) * 4;
                    if (img.data[i + 3] > 30 && img.data[i] < 245) {
                        ink = true;
                        break;
                    }
                }
                if (ink) {
                    last = y;
                    break;
                }
            }
            var trimmed = Math.min(height, last + 30);
            if (trimmed >= height - 4) return canvas;
            var out = document.createElement('canvas');
            out.width = width;
            out.height = Math.max(1, trimmed);
            var outCtx = out.getContext('2d');
            outCtx.fillStyle = '#ffffff';
            outCtx.fillRect(0, 0, out.width, out.height);
            outCtx.drawImage(canvas, 0, 0);
            return out;
        }

        function canvasToEscPosRaster(canvas, autoCut) {
            var width = canvas.width - (canvas.width % 8);
            var height = canvas.height;
            var ctx = canvas.getContext('2d', { willReadFrequently: true });
            var data = ctx.getImageData(0, 0, width, height).data;
            var bytesPerRow = width / 8;
            var packed = new Uint8Array(bytesPerRow * height);
            var ink = new Uint8Array(height);
            for (var y = 0; y < height; y++) {
                var rowBase = y * bytesPerRow;
                for (var xb = 0; xb < bytesPerRow; xb++) {
                    var bits = 0;
                    for (var bit = 0; bit < 8; bit++) {
                        var px = (y * width + xb * 8 + bit) * 4;
                        if (data[px + 3] > 40 && (data[px] * 0.299 + data[px + 1] * 0.587 + data[px + 2] * 0.114) < 175) {
                            bits |= (0x80 >> bit);
                        }
                    }
                    if (bits) {
                        packed[rowBase + xb] = bits;
                        ink[y] = 1;
                    }
                }
            }

            // Blank rows go out as a 1-byte-wide raster (exact dot spacing, ~1 byte per row instead of a full row),
            // and each ink band is cut at its right-most inked byte. Layout stays identical to the full image.
            var parts = [new Uint8Array([0x1B, 0x40, 0x1B, 0x33, 0x00])];
            var total = parts[0].length;
            function push(arr) {
                parts.push(arr);
                total += arr.length;
            }
            y = 0;
            while (y < height) {
                if (!ink[y]) {
                    var run = 0;
                    while (y < height && !ink[y]) { run++; y++; }
                    while (run > 0) {
                        var n = Math.min(240, run);
                        push(new Uint8Array([0x1D, 0x76, 0x30, 0x00, 1, 0, n & 255, (n >> 8) & 255]));
                        push(new Uint8Array(n));
                        run -= n;
                    }
                    continue;
                }
                var start = y;
                var lastInk = y;
                while (y < height && (y - start) < 240) {
                    if (ink[y]) {
                        lastInk = y;
                    } else if (y - lastInk >= 3) {
                        break;
                    }
                    y++;
                }
                var end = lastInk + 1;
                var band = end - start;
                var bandBytes = 1;
                for (var by = start; by < end; by++) {
                    for (var bx = bytesPerRow - 1; bx >= bandBytes; bx--) {
                        if (packed[by * bytesPerRow + bx]) {
                            bandBytes = bx + 1;
                            break;
                        }
                    }
                }
                push(new Uint8Array([0x1D, 0x76, 0x30, 0x00, bandBytes & 255, (bandBytes >> 8) & 255, band & 255, (band >> 8) & 255]));
                if (bandBytes === bytesPerRow) {
                    push(packed.subarray(start * bytesPerRow, end * bytesPerRow));
                } else {
                    var slice = new Uint8Array(bandBytes * band);
                    for (var ry = 0; ry < band; ry++) {
                        slice.set(packed.subarray((start + ry) * bytesPerRow, (start + ry) * bytesPerRow + bandBytes), ry * bandBytes);
                    }
                    push(slice);
                }
                y = end;
            }
            var tail = [];
            appendAutoCut(tail, autoCut);
            push(new Uint8Array(tail));

            var out = new Uint8Array(total);
            var offset = 0;
            parts.forEach(function(part) {
                out.set(part, offset);
                offset += part.length;
            });
            return out;
        }

        function pdfToCanvas(pdfUrl) {
            return loadPdfJs().then(function(pdfjsLib) {
                return fetch(pdfUrl, { credentials: 'same-origin' }).then(function(res) {
                    if (!res.ok) throw new Error('Could not load the bill');
                    return res.arrayBuffer();
                }).then(function(buf) {
                    var bytes = new Uint8Array(buf);
                    if (bytes.length < 5 || String.fromCharCode(bytes[0], bytes[1], bytes[2], bytes[3]) !== '%PDF') {
                        throw new Error('The selected template did not load');
                    }
                    return pdfjsLib.getDocument({ data: bytes }).promise;
                }).then(function(pdf) {
                    var pageNo = 1;
                    var parts = [];
                    function nextPage() {
                        if (pageNo > pdf.numPages) return Promise.resolve(parts);
                        return pdf.getPage(pageNo).then(function(page) {
                            var base = page.getViewport({ scale: 1 });
                            var targetW = base.width < 180 ? 384 : 576;
                            var viewport = page.getViewport({ scale: targetW / base.width });
                            var canvas = document.createElement('canvas');
                            canvas.width = targetW;
                            canvas.height = Math.max(1, Math.ceil(viewport.height));
                            var ctx = canvas.getContext('2d', { willReadFrequently: true });
                            ctx.fillStyle = '#ffffff';
                            ctx.fillRect(0, 0, canvas.width, canvas.height);
                            return page.render({ canvasContext: ctx, viewport: viewport }).promise.then(function() {
                                parts.push(canvas);
                                pageNo += 1;
                                return nextPage();
                            });
                        });
                    }
                    return nextPage();
                }).then(function(parts) {
                    var height = 0;
                    parts.forEach(function(part) { height += part.height; });
                    var sheet = document.createElement('canvas');
                    sheet.width = parts[0] ? parts[0].width : 576;
                    sheet.height = Math.max(1, height);
                    var ctx = sheet.getContext('2d', { willReadFrequently: true });
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, sheet.width, sheet.height);
                    var top = 0;
                    parts.forEach(function(part) {
                        ctx.drawImage(part, 0, top);
                        top += part.height;
                    });
                    return trimReceiptCanvas(sheet);
                });
            });
        }

        // Rendered bill is cached per URL so the preview can prepare it before Print is tapped.
        function billCanvas(pdfUrl) {
            var key = cleanPdfUrl(pdfUrl);
            var cached = state.billCanvas;
            if (cached && cached.key === key && (Date.now() - cached.at) < 120000) {
                return cached.promise;
            }
            var promise = pdfToCanvas(key).catch(function(err) {
                if (state.billCanvas && state.billCanvas.key === key) state.billCanvas = null;
                throw err;
            });
            state.billCanvas = { key: key, at: Date.now(), promise: promise };
            return promise;
        }

        function rasterBillBytes(pdfUrl, autoCut) {
            return billCanvas(pdfUrl).then(function(canvas) {
                return canvasToEscPosRaster(canvas, autoCut);
            });
        }

        function fastPrintEnabled() {
            var $box = $('.bt-fast-print-toggle');
            if ($box.length) return $box.first().is(':checked');
            try { return localStorage.getItem('btFastPrint') !== '0'; } catch (e) { return true; }
        }

        function normalizeReceiptText(value) {
            return String(value == null ? '' : value)
                .replace(/\u20B9/g, 'Rs')
                .replace(/\u2026/g, '...')
                .replace(/[\u2018\u2019]/g, "'")
                .replace(/[\u201C\u201D]/g, '"')
                .replace(/[\u2013\u2014]/g, '-')
                .replace(/[\u00A0\t\r\n]+/g, ' ');
        }

        // Printer fonts are ASCII only; Hindi or other scripts must use the image print.
        function fastReceiptPrintable(fast) {
            if (!fast || !fast.items) return false;
            var texts = [fast.shop, fast.phone, fast.gst, fast.fssai, fast.heading, fast.saleLabel, fast.date, fast.time,
                fast.priceLabel, fast.totalLabel, fast.savings, fast.paymentLine, fast.outstanding, fast.thanks, fast.terms]
                .concat(fast.customer || []);
            fast.items.forEach(function(item) {
                texts.push(item.name, item.qty, item.disc);
                (item.extra || []).forEach(function(x) { texts.push(x); });
            });
            for (var i = 0; i < texts.length; i++) {
                if (/[^\x20-\x7E]/.test(normalizeReceiptText(texts[i]))) return false;
            }
            return true;
        }

        var qrPayloadCache = {};
        var jsQrLoader = null;

        function loadJsQr() {
            if (window.jsQR) return Promise.resolve(window.jsQR);
            if (jsQrLoader) return jsQrLoader;
            jsQrLoader = new Promise(function(resolve, reject) {
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
                s.async = true;
                s.onload = function() {
                    if (window.jsQR) resolve(window.jsQR);
                    else reject(new Error('jsQR missing'));
                };
                s.onerror = function() { reject(new Error('jsQR load failed')); };
                document.head.appendChild(s);
            }).catch(function(err) {
                jsQrLoader = null;
                throw err;
            });
            return jsQrLoader;
        }

        // Decode uploaded UPI QR once → print with printer-native QR (tiny + fast).
        function decodeQrPayload(src) {
            if (!src) return Promise.resolve(null);
            if (Object.prototype.hasOwnProperty.call(qrPayloadCache, src)) {
                return Promise.resolve(qrPayloadCache[src]);
            }
            return loadJsQr().then(function(jsQR) {
                return new Promise(function(resolve) {
                    var img = new Image();
                    img.onload = function() {
                        try {
                            var maxSide = 640;
                            var scale = Math.min(1, maxSide / Math.max(img.naturalWidth, img.naturalHeight, 1));
                            var w = Math.max(1, Math.round(img.naturalWidth * scale));
                            var h = Math.max(1, Math.round(img.naturalHeight * scale));
                            var canvas = document.createElement('canvas');
                            canvas.width = w;
                            canvas.height = h;
                            var ctx = canvas.getContext('2d', { willReadFrequently: true });
                            ctx.imageSmoothingEnabled = false;
                            ctx.drawImage(img, 0, 0, w, h);
                            var data = ctx.getImageData(0, 0, w, h);
                            var code = jsQR(data.data, data.width, data.height, { inversionAttempts: 'attemptBoth' });
                            var payload = (code && code.data) ? String(code.data) : null;
                            qrPayloadCache[src] = payload;
                            resolve(payload);
                        } catch (e) {
                            qrPayloadCache[src] = null;
                            resolve(null);
                        }
                    };
                    img.onerror = function() {
                        qrPayloadCache[src] = null;
                        resolve(null);
                    };
                    img.src = src;
                });
            }).catch(function() {
                qrPayloadCache[src] = null;
                return null;
            });
        }

        // Epson-compatible GS ( k QR — a few hundred bytes instead of a full bitmap.
        function escPosNativeQr(payload, moduleSize) {
            var out = [];
            if (!payload) return out;
            moduleSize = Math.max(4, Math.min(10, parseInt(moduleSize, 10) || 6));
            var data = [];
            for (var i = 0; i < payload.length; i++) data.push(payload.charCodeAt(i) & 255);
            // Model 2
            out.push(0x1D, 0x28, 0x6B, 4, 0, 49, 65, 50, 0);
            // Module size
            out.push(0x1D, 0x28, 0x6B, 3, 0, 49, 67, moduleSize);
            // Error correction M (49)
            out.push(0x1D, 0x28, 0x6B, 3, 0, 49, 69, 49);
            // Store data
            var storeLen = data.length + 3;
            out.push(0x1D, 0x28, 0x6B, storeLen & 255, (storeLen >> 8) & 255, 49, 80, 48);
            for (var d = 0; d < data.length; d++) out.push(data[d]);
            // Print
            out.push(0x1D, 0x28, 0x6B, 3, 0, 49, 81, 48);
            return out;
        }

        function loadReceiptImage(src, maxW, targetH) {
            if (!src) return Promise.resolve(null);
            return new Promise(function(resolve) {
                var img = new Image();
                img.onload = function() {
                    var nw = Math.max(1, img.naturalWidth);
                    var nh = Math.max(1, img.naturalHeight);
                    var w = targetH ? Math.min(maxW, Math.round(nw * targetH / nh)) : maxW;
                    w = Math.max(8, w - (w % 8));
                    var h = Math.max(1, Math.round(nh * (w / nw)));
                    var canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    var ctx = canvas.getContext('2d', { willReadFrequently: true });
                    ctx.imageSmoothingEnabled = false;
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, w, h);
                    ctx.drawImage(img, 0, 0, w, h);
                    resolve(canvas);
                };
                img.onerror = function() { resolve(null); };
                img.src = src;
            });
        }

        function centeredImageBytes(canvas, paperDots) {
            var out = [];
            if (!canvas) return out;
            var width = canvas.width;
            var height = canvas.height;
            var data = canvas.getContext('2d', { willReadFrequently: true }).getImageData(0, 0, width, height).data;
            var imgBytes = width / 8;
            var pad = paperDots ? Math.max(0, Math.floor((paperDots / 8 - imgBytes) / 2)) : 0;
            var rowBytes = pad + imgBytes;
            for (var top = 0; top < height; top += 240) {
                var rows = Math.min(240, height - top);
                out.push(0x1D, 0x76, 0x30, 0x00, rowBytes & 255, (rowBytes >> 8) & 255, rows & 255, (rows >> 8) & 255);
                for (var y = top; y < top + rows; y++) {
                    for (var p = 0; p < pad; p++) out.push(0);
                    for (var xb = 0; xb < imgBytes; xb++) {
                        var bits = 0;
                        for (var bit = 0; bit < 8; bit++) {
                            var px = (y * width + xb * 8 + bit) * 4;
                            if (data[px + 3] > 40 && (data[px] * 0.299 + data[px + 1] * 0.587 + data[px + 2] * 0.114) < 160) {
                                bits |= (0x80 >> bit);
                            }
                        }
                        out.push(bits);
                    }
                }
            }
            return out;
        }

        // Sizes follow the small thermal template: ~2 printer dots per CSS px, so 8px body text = Font B (9x17),
        // 11-12px headings = Font A (12x24). Line spacing stays at the printer default because motion units vary.
        function buildFastEscPos(fast, autoCut) {
            var narrow = fast.cols === 32;
            var dots = narrow ? 384 : 576;
            // Small side inset only — keeps the old layout, stops left/right edge clipping.
            var sidePad = 1;
            var colsA = (narrow ? 32 : 48) - sidePad * 2;
            var colsB = (narrow ? 42 : 64) - sidePad * 2;
            var bodyFont = (parseInt(fast.fontPx, 10) || 8) >= 11 ? 'A' : 'B';
            var logoDots = Math.round((parseInt(fast.logoPx, 10) || 28) * 2);
            var ruleBytes = Math.max(8, Math.floor(dots / 8) - 2);
            // Prefer native printer QR (fast). Raster only if decode fails.
            var qrDots = narrow ? 168 : 200;
            return Promise.all([
                loadReceiptImage(fast.logo, Math.round((ruleBytes * 8) * 0.9), logoDots),
                decodeQrPayload(fast.qr).then(function(payload) {
                    if (payload) {
                        return { type: 'native', payload: payload };
                    }
                    return loadReceiptImage(fast.qr, qrDots).then(function(canvas) {
                        return canvas ? { type: 'raster', canvas: canvas } : null;
                    });
                })
            ]).then(function(images) {
                var out = [];
                var cols = colsB;
                function raw() {
                    for (var i = 0; i < arguments.length; i++) out.push(arguments[i] & 255);
                }
                function font(name) {
                    if (name === 'body') name = bodyFont;
                    raw(0x1B, 0x4D, name === 'B' ? 1 : 0);
                    cols = name === 'B' ? colsB : colsA;
                }
                var printedRows = 0;
                function align(center) {
                    raw(0x1B, 0x61, center ? 1 : 0);
                }
                // Printers centre raster images with ESC a 1, so no padding bytes are sent.
                function image(canvas) {
                    if (!canvas) return;
                    raw(0x1B, 0x61, 1);
                    out.push.apply(out, centeredImageBytes(canvas, 0));
                    printedRows += canvas.height;
                }
                function clean(value) {
                    return normalizeReceiptText(value).replace(/[^\x20-\x7E]/g, '').trim();
                }
                function spaces(n) {
                    return n > 0 ? new Array(n + 1).join(' ') : '';
                }
                function padR(s, w) {
                    s = String(s);
                    return s.length >= w ? s.slice(0, w) : s + spaces(w - s.length);
                }
                function padL(s, w) {
                    s = String(s);
                    return s.length >= w ? s : spaces(w - s.length) + s;
                }
                function wrap(s, w) {
                    s = clean(s);
                    if (!s) return [];
                    var lines = [];
                    var line = '';
                    s.split(/\s+/).forEach(function(word) {
                        while (word.length > w) {
                            if (line) { lines.push(line); line = ''; }
                            lines.push(word.slice(0, w));
                            word = word.slice(w);
                        }
                        if (!word) return;
                        if (!line) line = word;
                        else if (line.length + 1 + word.length <= w) line += ' ' + word;
                        else { lines.push(line); line = word; }
                    });
                    if (line) lines.push(line);
                    return lines;
                }
                function println(s) {
                    s = spaces(sidePad) + String(s || '');
                    for (var i = 0; i < s.length; i++) out.push(s.charCodeAt(i) & 255);
                    out.push(10);
                    printedRows += 30;
                }
                function para(s) {
                    wrap(s, cols).forEach(println);
                }
                function lr(left, right) {
                    left = clean(left);
                    right = clean(right);
                    var gap = cols - left.length - right.length;
                    if (gap < 1) {
                        println(left);
                        println(padL(right, cols));
                    } else {
                        println(left + spaces(gap) + right);
                    }
                }
                function gap(rows) {
                    raw(0x1D, 0x76, 0x30, 0x00, 1, 0, rows, 0);
                    for (var i = 0; i < rows; i++) out.push(0);
                    printedRows += rows;
                }
                function solidRule() {
                    var w = ruleBytes;
                    raw(0x1D, 0x76, 0x30, 0x00, w, 0, 2, 0);
                    for (var i = 0; i < w * 2; i++) out.push(0xFF);
                    gap(4);
                }

                var boldOn = fast.bold !== false ? 0x01 : 0x00;
                raw(0x1B, 0x40);
                raw(0x1B, 0x45, boldOn);
                raw(0x1B, 0x47, boldOn);
                align(true);
                if (images[0]) {
                    image(images[0]);
                    gap(6);
                }
                font('A');
                if (clean(fast.shop)) para(clean(fast.shop).toUpperCase());
                font('B');
                para(fast.address);
                para(fast.phone);
                para(fast.email);
                para(fast.gst);
                para(fast.fssai);
                gap(8);
                font('A');
                para(fast.heading || 'Invoice');
                gap(8);
                align(false);

                font('body');
                lr(fast.saleLabel, fast.date);
                lr('', fast.time);
                if ((fast.customer || []).length) {
                    gap(16);
                    fast.customer.forEach(function(lineText) { para(lineText); });
                }
                gap(8);

                var show = fast.show || {};
                var items = fast.items || [];
                var numCols = [];
                if (show.mrp) numCols.push({ key: 'mrp', label: 'MRP' });
                if (show.sp) numCols.push({ key: 'sp', label: clean(fast.priceLabel) || 'Price' });
                if (show.total) numCols.push({ key: 'amount', label: 'Amount' });
                var weightSum = (show.serial ? 6 : 0) + 40 + numCols.length * 18;
                var serialW = show.serial ? Math.max(String(items.length).length + 1, Math.round(cols * 6 / weightSum)) : 0;
                numCols.forEach(function(col) {
                    var need = col.label.length;
                    items.forEach(function(item) {
                        need = Math.max(need, clean(item[col.key]).length);
                        if (col.key === 'amount') need = Math.max(need, clean(item.discAmt).length);
                    });
                    col.width = Math.max(need + 1, Math.round(cols * 18 / weightSum));
                });
                var numW = numCols.reduce(function(sum, col) { return sum + col.width; }, 0);
                var nameW = Math.max(1, cols - serialW - numW);
                var indent = spaces(serialW);

                var head = padR(show.serial ? '#' : '', serialW) + padR('Item Name', nameW);
                numCols.forEach(function(col) { head += padL(col.label, col.width); });
                println(head);
                if (show.qty) println(indent + 'Quantity');
                solidRule();

                items.forEach(function(item, index) {
                    if (index > 0) gap(6);
                    var nameLines = wrap(item.name, cols - serialW);
                    if (!nameLines.length) nameLines = [''];
                    nameLines.forEach(function(text, i) {
                        println((i === 0 && show.serial ? padR(String(item.no), serialW) : indent) + text);
                    });
                    (item.extra || []).forEach(function(extra) {
                        wrap(extra, cols - serialW).forEach(function(text) { println(indent + text); });
                    });
                    if (show.qty || numCols.length) {
                        var row = indent + padR(show.qty ? clean(item.qty) : '', nameW);
                        numCols.forEach(function(col) { row += padL(clean(item[col.key]), col.width); });
                        println(row);
                    }
                    if (item.disc) {
                        var discAmt = show.total ? clean(item.discAmt) : '';
                        println(indent + padR(clean(item.disc), cols - serialW - discAmt.length) + discAmt);
                    }
                });
                gap(6);
                solidRule();
                lr(fast.totalLabel || 'Total', fast.subTotal);
                lr('Total', ': ' + clean(fast.grandTotal));
                if (clean(fast.paid)) lr('Received', ': ' + clean(fast.paid));
                if (clean(fast.balance)) lr('Balance', ': ' + clean(fast.balance));
                (fast.taxLines || []).forEach(function(t) { lr(t.label, t.value); });
                if (clean(fast.amountWords)) {
                    gap(8);
                    para(fast.amountWords);
                }
                if (clean(fast.remarks)) {
                    gap(8);
                    para(fast.remarks);
                }

                align(true);
                if (clean(fast.savings)) {
                    gap(12);
                    para(fast.savings);
                }
                if (clean(fast.paymentLine)) {
                    gap(16);
                    para(fast.paymentLine);
                }
                if (clean(fast.outstanding)) {
                    gap(6);
                    para(fast.outstanding);
                }
                if (images[1]) {
                    gap(12);
                    println('Scan QR to Pay Bill:');
                    align(true);
                    if (images[1].type === 'native') {
                        out.push.apply(out, escPosNativeQr(images[1].payload, narrow ? 5 : 6));
                        printedRows += narrow ? 180 : 220;
                    } else if (images[1].canvas) {
                        image(images[1].canvas);
                    }
                }
                align(false);
                if (clean(fast.terms)) {
                    gap(12);
                    println('Terms & Conditions:');
                    para(fast.terms);
                }
                gap(16);
                para(fast.thanks);
                var extraLines = Math.max(0, Math.min(10, parseInt(fast.extraLines, 10) || 0));
                for (var el = 0; el < extraLines; el++) println('');
                font('A');
                raw(0x1B, 0x45, 0x00);
                raw(0x1B, 0x47, 0x00);
                appendAutoCut(out, autoCut);
                printedRows += 200;
                var copies = printCopies();
                if (copies > 1) {
                    var single = out.slice();
                    for (var cp = 1; cp < copies; cp++) {
                        for (var bi = 0; bi < single.length; bi++) out.push(single[bi]);
                    }
                    printedRows *= copies;
                }
                // Some printers drop unprinted data when Bluetooth disconnects. Trailing NUL bytes (ignored by
                // the printer) keep the link open until printing and the cut have finished.
                var tail = Math.min(32768, Math.max(4096, printedRows * 20));
                var bytes = new Uint8Array(out.length + tail);
                bytes.set(out, 0);
                return bytes;
            });
        }

        // Fast print sends printer text (a few KB) instead of a full-page image, which is what makes Bluetooth slow.
        function bluetoothBillBytes(pdfUrl, autoCut) {
            if (!fastPrintEnabled()) return rasterBillBytes(pdfUrl, autoCut);
            return loadPhoneReceipt(pdfUrl).then(function(receipt) {
                var fast = receipt && receipt.fast;
                if (!fastReceiptPrintable(fast)) return rasterBillBytes(pdfUrl, autoCut);
                return buildFastEscPos(fast, autoCut).then(function(bytes) {
                    bytes.cutIncluded = true;
                    return bytes;
                });
            }, function() {
                return rasterBillBytes(pdfUrl, autoCut);
            });
        }

        function receiptJsonUrl(pdfUrl) {
            var url = new URL(pdfUrl, window.location.origin);
            url.searchParams.set('bt_receipt', '1');
            return url.href;
        }

        // Must run straight from the Print click: Chrome only opens its chooser during a user gesture.
        function reconnectBluetoothPrinter(deviceId) {
            if (!navigator.bluetooth || typeof navigator.bluetooth.requestDevice !== 'function') {
                return Promise.reject(new Error('Printer not found. Tap refresh and choose it again.'));
            }
            var label = 'bt:' + deviceId === defaultPrinterValue() ? String(printPrefs().defaultPrinterLabel || '').trim() : '';
            var options = label
                ? { filters: [{ name: label }], optionalServices: BT_SERVICES }
                : printerRequestOptions();
            return navigator.bluetooth.requestDevice(options).then(function(device) {
                var row = { id: device.id, name: device.name || label || 'Bluetooth printer', device: device, transport: 'web' };
                rememberBluetoothDevice(row);
                state.btDevices[deviceId] = row;
                return row;
            }).catch(function(err) {
                if (err && (err.name === 'NotFoundError' || err.name === 'AbortError')) {
                    throw new Error('Printer not connected. Turn it on and press Print again.');
                }
                throw err;
            });
        }

        function printToBluetoothPrinter(deviceId, pdfUrl, autoCut, $status, $btn, storageKey) {
            var device = state.btDevices[deviceId];
            if (isBridgePrinterId(deviceId) || (device && device.transport === 'bridge')) {
                return printViaBridge(deviceId, pdfUrl, autoCut, $status, $btn, storageKey);
            }
            if ($btn && $btn.length) $btn.prop('disabled', true);
            autoCut = wantsAutoCut(autoCut);
            setStatus($status, 'Printing the selected bill to ' + ((device && device.name) || 'printer') + '…', false);

            var billSent = false;
            var ready = device ? Promise.resolve(device) : (navigator.bluetooth && navigator.bluetooth.getDevices
                ? navigator.bluetooth.getDevices().then(function(devices) {
                    var found = null;
                    (devices || []).forEach(function(item) {
                        if (item.id === deviceId) found = item;
                    });
                    if (!found) throw new Error('Printer not found. Tap refresh and choose it again.');
                    rememberBluetoothDevice(found);
                    return found;
                })
                : reconnectBluetoothPrinter(deviceId));

            return ready.then(function(printer) {
                device = printer.device && printer.device.gatt ? printer.device : printer;
                try {
                    localStorage.setItem(storageKey || 'posSelectedPrinter', 'bt:' + (printer.id || device.id));
                } catch (e) {}
                if (!device.gatt) throw new Error('Select the printer again, then tap Print');
                return device.gatt.connect();
            }).then(function(server) {
                return findWritableCharacteristic(server).then(function(characteristic) {
                    if (!characteristic) {
                        throw new Error('This Bluetooth device is not a receipt printer the browser can print to');
                    }
                    return bluetoothBillBytes(pdfUrl, autoCut).then(function(bytes) {
                        billSent = true;
                        return writePrinterBytes(characteristic, bytes).then(function() {
                            if (!autoCut || bytes.cutIncluded) return;
                            return new Promise(function(resolve) { setTimeout(resolve, 700); }).then(function() {
                                return writePrinterBytes(characteristic, phoneCutBytes());
                            }).catch(function(cutErr) {
                                console.warn('Bluetooth cut', cutErr);
                            });
                        });
                    });
                }).then(function() {
                    try { device.gatt.disconnect(); } catch (e) {}
                    setStatus($status, 'Printed', false);
                    if ($btn && $btn.length) $btn.prop('disabled', false);
                });
            }).catch(function(err) {
                console.warn('Bluetooth print', err);
                if (billSent) {
                    err = err instanceof Error ? err : new Error(String(err || 'Bluetooth print failed'));
                    err.billSent = true;
                }
                try { if (device && device.gatt) device.gatt.disconnect(); } catch (e2) {}
                setStatus($status, (err && err.message) ? err.message : 'Bluetooth print failed', true);
                if ($btn && $btn.length) $btn.prop('disabled', false);
                return Promise.reject(err);
            });
        }

        function absoluteUrl(url) {
            try {
                return new URL(url, window.location.origin).href;
            } catch (e) {
                return url;
            }
        }

            function showQzRequired(detail) {
            var extra = detail ? ('<p class="mt-2 mb-0" style="font-size:12px;color:#ea5455;">' + detail + '</p>') : '';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Connect QZ Tray',
                    html: '<div style="text-align:left;font-size:14px;line-height:1.5;">' +
                        '<p class="mb-2"><b>If Allow is grey when Remember is ticked:</b> that is normal until the PC trusts this site.</p>' +
                        '<ol style="padding-left:18px;margin:0 0 8px;">' +
                        '<li><b>Print now:</b> uncheck <b>Remember this decision</b>, then click <b>Allow</b>.</li>' +
                        '<li><b>Stop popup forever:</b> download <a href="' + (window.__QZ_INSTALL_BAT_URL || '/qz/install-qz-trust.bat') + '">install-qz-trust.bat</a>, Run as administrator, then restart QZ Tray.</li>' +
                        '<li>Print again → tick Remember → Allow will work.</li>' +
                        '</ol>' + extra +
                        '</div>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ff9f43'
                });
            } else {
                alert('Uncheck Remember to enable Allow. Or run install-qz-trust.bat as Administrator, restart QZ Tray.');
            }
        }

        function rawBtIntentUrl(bytes) {
            var b64 = bytesToBase64(bytes).replace(/\s+/g, '').replace(/\+/g, '%2B').replace(/\//g, '%2F');
            var fallback = 'https://play.google.com/store/apps/details?id=ru.a402d.rawbtprinter';
            return 'intent:base64,' + b64 + '#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;S.browser_fallback_url=' + encodeURIComponent(fallback) + ';end;';
        }

        function openRawBt(bytes) {
            window.location.href = rawBtIntentUrl(bytes);
        }

        function queueRawBt(bytes) {
            var link = document.createElement('a');
            link.href = rawBtIntentUrl(bytes);
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            setTimeout(function() {
                try { link.remove(); } catch (e) {}
            }, 1500);
        }

        function openPhonePrinterPicker(statusSel, opts) {
            opts = opts || {};
            var $status = statusSel ? $(statusSel) : $();
            return pickNearbyPrinter({
                select: opts.select || '',
                status: statusSel || '',
                storageKey: opts.storageKey || 'posSelectedPrinter',
                list: opts.list || ''
            });
        }

        function storePhoneReceipt(pdfUrl, receipt) {
            var key = receiptJsonUrl(absoluteUrl(pdfUrl));
            var at = Date.now();
            state.phonePrintCache = { url: key, receipt: receipt, at: at };
            try { sessionStorage.setItem('posBtReceipt:' + key, JSON.stringify({ at: at, receipt: receipt })); } catch (e) {}
            return receipt;
        }

        function readPhoneReceipt(pdfUrl) {
            var key = receiptJsonUrl(absoluteUrl(pdfUrl));
            var maxAge = 120000;
            var cache = state.phonePrintCache;
            if (cache && cache.url === key && cache.receipt && (Date.now() - (cache.at || 0)) < maxAge) {
                return cache.receipt;
            }
            try {
                var raw = sessionStorage.getItem('posBtReceipt:' + key);
                if (!raw) return null;
                var saved = JSON.parse(raw);
                if (!saved || !saved.receipt || (Date.now() - (saved.at || 0)) >= maxAge) return null;
                state.phonePrintCache = { url: key, receipt: saved.receipt, at: saved.at };
                return saved.receipt;
            } catch (e) {
                return null;
            }
        }

        function loadPhoneReceipt(pdfUrl) {
            var cached = readPhoneReceipt(pdfUrl);
            if (cached) return Promise.resolve(cached);
            return fetch(receiptJsonUrl(absoluteUrl(pdfUrl)), { credentials: 'same-origin' }).then(function(res) {
                if (!res.ok) throw new Error('Could not load the bill');
                return res.json();
            }).then(function(receipt) {
                return storePhoneReceipt(pdfUrl, receipt);
            });
        }

        function preparePhonePrint(pdfUrl) {
            if (!pdfUrl) return Promise.resolve(null);
            state.phonePdfUrl = pdfUrl;
            if (!isAndroidPhone() && !isIosPhone()) return Promise.resolve(null);
            var android = isAndroidPhone();
            if (android && !fastPrintEnabled()) {
                billCanvas(pdfUrl).catch(function() {});
            }            return loadPhoneReceipt(pdfUrl).then(function(receipt) {
                if (android && fastPrintEnabled() && !fastReceiptPrintable(receipt && receipt.fast)) {
                    billCanvas(pdfUrl).catch(function() {});
                }
                return receipt;
            }).catch(function() {
                if (android) billCanvas(pdfUrl).catch(function() {});
                return null;
            });
        }

        function discoverWifiHosts() {
            var hosts = ['192.168.4.1', '192.168.1.1', '192.168.0.1', '192.168.1.100', '192.168.0.100', '192.168.123.100', '192.168.10.1', '192.168.8.1'];
            return new Promise(function(resolve) {
                var found = '';
                var done = false;
                function finish() {
                    if (done) return;
                    done = true;
                    if (found) {
                        var parts = found.split('.');
                        if (parts.length === 4) hosts.unshift(parts[0] + '.' + parts[1] + '.' + parts[2] + '.1');
                    }
                    resolve(hosts);
                }
                setTimeout(finish, 1200);
                try {
                    var pc = new RTCPeerConnection({ iceServers: [] });
                    pc.createDataChannel('x');
                    pc.onicecandidate = function(event) {
                        var text = event && event.candidate && event.candidate.candidate;
                        var match = text && /([0-9]{1,3}(?:\.[0-9]{1,3}){3})/.exec(text);
                        if (match && match[1] && match[1] !== '0.0.0.0') {
                            found = match[1];
                            finish();
                        }
                    };
                    pc.createOffer().then(function(offer) {
                        return pc.setLocalDescription(offer);
                    }).catch(function() { finish(); });
                } catch (e) {
                    finish();
                }
            });
        }

        function tryWifiHost(host, bytes) {
            if (typeof TCPSocket !== 'function') return Promise.resolve(false);
            var socket;
            try {
                socket = new TCPSocket(host, { remotePort: 9100 });
            } catch (e) {
                return Promise.resolve(false);
            }
            return Promise.race([
                socket.opened.then(function(conn) {
                    var writer = conn.writable.getWriter();
                    return writer.write(bytes).then(function() { return true; });
                }),
                new Promise(function(resolve) { setTimeout(function() { resolve(false); }, 700); })
            ]).catch(function() { return false; });
        }

        function printViaWifiPrinter(pdfUrl, autoCut, $status, $btn, storageKey) {
            var host = savedWifiHost();
            try {
                var savedName = localStorage.getItem(storageKey || 'posSelectedPrinter') || '';
                var fromName = savedName.indexOf('wifi:') === 0 ? savedName.slice(5) : '';
                if (/^\d{1,3}(\.\d{1,3}){3}$/.test(fromName)) host = fromName;
            } catch (e) {}
            if (isIosPhone() && !isAndroidPhone()) {
                return printViaPhoneShare(pdfUrl, $status, $btn, 'Wi‑Fi');
            }
            if ($btn && $btn.length) $btn.prop('disabled', true);
            setStatus($status, 'Sending to ' + host + '…', false);
            try { localStorage.setItem(storageKey || 'posSelectedPrinter', 'wifi:' + host); } catch (e2) {}
            var key = receiptJsonUrl(absoluteUrl(pdfUrl));
            function send(receipt) {
                var bytes = buildEscPos(receipt, autoCut);
                return tryWifiHost(host, bytes).then(function(ok) {
                    if (ok) {
                        setStatus($status, 'Printed to ' + host, false);
                        if ($btn && $btn.length) $btn.prop('disabled', false);
                        return;
                    }
                    setStatus($status, 'Could not reach Wi‑Fi printer ' + host + '. Use the Bluetooth tab and choose a nearby printer.', true);
                    if ($btn && $btn.length) $btn.prop('disabled', false);
                    return Promise.reject(new Error('Wi‑Fi printer not reachable'));
                });
            }
            if (state.phonePrintCache && state.phonePrintCache.url === key && state.phonePrintCache.receipt) {
                return Promise.resolve(send(state.phonePrintCache.receipt));
            }
            return fetch(key, { credentials: 'same-origin' }).then(function(res) {
                if (!res.ok) throw new Error('Could not load the bill. Stay connected to the internet, then print.');
                return res.json();
            }).then(function(receipt) {
                state.phonePrintCache = { url: key, receipt: receipt };
                return send(receipt);
            }).catch(function(err) {
                setStatus($status, (err && err.message) ? err.message : 'Could not reach the Wi‑Fi printer', true);
                if ($btn && $btn.length) $btn.prop('disabled', false);
                return Promise.reject(err);
            });
        }

        function printViaPhoneBluetooth(pdfUrl, autoCut, $status, $btn, opts) {
            opts = opts || {};
            autoCut = wantsAutoCut(autoCut);
            var storageKey = opts.storageKey || 'posSelectedPrinter';
            if ($btn && $btn.length) $btn.prop('disabled', true);

            function printToDevice(deviceId) {
                return printToBluetoothPrinter(deviceId, pdfUrl, autoCut, $status, $btn, storageKey);
            }

            var saved = '';
            try { saved = localStorage.getItem(storageKey) || ''; } catch (e) {}
            if (saved.indexOf('bt:') === 0 && saved !== 'bt:phone') {
                return printToDevice(saved.slice(3));
            }

            if (!navigator.bluetooth || typeof navigator.bluetooth.requestDevice !== 'function') {
                setStatus($status, 'Use Chrome and tap Bluetooth to choose a printer.', true);
                if ($btn && $btn.length) $btn.prop('disabled', false);
                return Promise.reject(new Error('Web Bluetooth not available'));
            }

            setStatus($status, 'Choose a nearby Bluetooth printer…', false);
            return pickNearbyPrinter({
                select: opts.select || '',
                status: opts.status || '',
                storageKey: storageKey,
                list: opts.list || ''
            }).then(function(row) {
                if (!row || !row.id) {
                    if ($btn && $btn.length) $btn.prop('disabled', false);
                    setStatus($status, 'No printer selected.', true);
                    return Promise.reject(new Error('No printer selected'));
                }
                return printToDevice(row.id);
            });
        }

        function appendBluetoothPrinter($sel, row, storageKey, selectIt) {
            if (!$sel || !$sel.length || !row || !row.id) return;
            rememberBluetoothDevice(row);
            var value = 'bt:' + row.id;
            var label = (row.name || 'Bluetooth printer') + ' (Bluetooth)';
            var exists = false;
            $sel.find('option').each(function() {
                if (this.value === value) exists = true;
            });
            if (!exists) {
                $sel.append($('<option></option>').val(value).text(label));
            }
            if (selectIt) {
                $sel.prop('disabled', false);
                $sel.val(value);
                try { localStorage.setItem(storageKey || 'posSelectedPrinter', value); } catch (e) {}
                syncAutoCutForPrinter(value);
            }
        }

        function restoreSessionBluetooth($sel, storageKey) {
            if (!$sel || !$sel.length) return;
            var saved = '';
            try { saved = localStorage.getItem(storageKey || 'posSelectedPrinter') || ''; } catch (e) {}
            Object.keys(state.btDevices || {}).forEach(function(id) {
                var row = state.btDevices[id];
                if (!row || !row.id || row.transport === 'bridge') return;
                appendBluetoothPrinter($sel, row, storageKey, saved === ('bt:' + id));
            });
        }

        function mergeGrantedBluetooth($sel, storageKey) {
            if (!$sel || !$sel.length || !navigator.bluetooth || typeof navigator.bluetooth.getDevices !== 'function') {
                return Promise.resolve();
            }
            var saved = '';
            try { saved = localStorage.getItem(storageKey || 'posSelectedPrinter') || ''; } catch (e) {}
            return navigator.bluetooth.getDevices().then(function(devices) {
                (devices || []).forEach(function(device) {
                    if (!device || !device.id) return;
                    appendBluetoothPrinter($sel, {
                        id: device.id,
                        name: device.name || 'Bluetooth printer',
                        device: device,
                        transport: 'web'
                    }, storageKey, saved === ('bt:' + device.id));
                });
            }).catch(function() {});
        }

        function chooseDesktopBluetooth(opts) {
            opts = opts || {};
            var $status = opts.status ? $(opts.status) : $();
            var $sel = $(opts.select);
            if (!navigator.bluetooth || typeof navigator.bluetooth.requestDevice !== 'function') {
                setStatus($status, 'Use Chrome on this computer to print by Bluetooth.', true);
                return Promise.resolve(null);
            }
            setStatus($status, 'Choose a nearby printer', false);
            return navigator.bluetooth.requestDevice(printerRequestOptions()).then(function(device) {
                var row = {
                    id: device.id,
                    name: device.name || 'Bluetooth printer',
                    device: device,
                    transport: 'web'
                };
                appendBluetoothPrinter($sel, row, opts.storageKey, true);
                setStatus($status, 'Selected ' + row.name + '. Press Print.', false);
                return row;
            }).catch(function(err) {
                if (!(err && (err.name === 'NotFoundError' || err.name === 'AbortError'))) {
                    setStatus($status, (err && err.message) ? err.message : 'Could not open Bluetooth', true);
                }
                return null;
            });
        }

        function pickNearbyPrinter(opts) {
            opts = opts || {};
            var $status = opts.status ? $(opts.status) : $();
            var listSel = opts.list || '';
            if (!navigator.bluetooth || typeof navigator.bluetooth.requestDevice !== 'function') {
                setStatus($status, 'Bluetooth picker needs Chrome or Edge on this device.', true);
                return Promise.resolve(null);
            }
            setStatus($status, 'Choose a nearby printer', false);
            return navigator.bluetooth.requestDevice(printerRequestOptions()).then(function(device) {
                var row = {
                    id: device.id,
                    name: device.name || 'Bluetooth device',
                    device: device,
                    transport: 'web'
                };
                rememberBluetoothDevice(row);
                try { localStorage.setItem(opts.storageKey || 'posSelectedPrinter', 'bt:' + device.id); } catch (e) {}
                if (opts.select) {
                    fillBluetoothSelect($(opts.select), [row], opts.storageKey, $status, listSel);
                }
                setStatus($status, 'Selected ' + row.name + '. Tap Print.', false);
                return row;
            }).catch(function(err) {
                if (!(err && (err.name === 'NotFoundError' || err.name === 'AbortError'))) {
                    setStatus($status, (err && err.message) ? err.message : 'Could not open nearby printers', true);
                }
                return null;
            });
        }

        function autoPickOnOpen(opts) {
            opts = opts || {};
            if (!window.matchMedia || !window.matchMedia('(max-width: 1024.98px)').matches) return null;
            if (!navigator.bluetooth || typeof navigator.bluetooth.requestDevice !== 'function') return null;
            if (navigator.userActivation && !navigator.userActivation.isActive) return null;
            if (opts.list && $(opts.list).hasClass('d-none')) return null;
            var saved = '';
            try { saved = localStorage.getItem(opts.storageKey || 'posSelectedPrinter') || ''; } catch (e) {}
            if (saved.indexOf('bt:') === 0 && saved !== 'bt:phone') return null;
            return pickNearbyPrinter(opts);
        }

        function printInvoice(opts) {
            opts = opts || {};
            var printerName = String(opts.printerName || '').trim();
            var pdfUrl = absoluteUrl(opts.pdfUrl || '');
            var doAutoCut = !!opts.autoCut;
            var $status = opts.status ? $(opts.status) : $();
            var $btn = opts.button ? $(opts.button) : $();
            var storageKey = opts.storageKey || 'posSelectedPrinter';

            if (!pdfUrl || pdfUrl.indexOf('about:blank') === 0) {
                return Promise.reject(new Error('Invoice not ready'));
            }

            if (printerName.indexOf('wifi:') === 0) {
                return printViaWifiPrinter(pdfUrl, doAutoCut, $status, $btn, storageKey);
            }

            if (printerName.indexOf('serial:') === 0) {
                return printViaSerial(printerName.slice(7), pdfUrl, doAutoCut, $status, $btn, storageKey);
            }

            if (printerName.indexOf('ios:') === 0) {
                return printViaPhoneShare(pdfUrl, $status, $btn, 'Bluetooth');
            }

            // Old RawBT selection — open Bluetooth device modal instead of RawBT app.
            if (printerName === 'bt:phone') {
                if (isIosPhone() && !isAndroidPhone()) {
                    return printViaPhoneShare(pdfUrl, $status, $btn, 'Bluetooth');
                }
                return printViaPhoneBluetooth(pdfUrl, doAutoCut, $status, $btn, {
                    select: opts.select || '',
                    status: opts.status || '',
                    storageKey: storageKey,
                    list: opts.list || ''
                });
            }

            if (printerName.indexOf('bt:') === 0) {
                return printToBluetoothPrinter(printerName.slice(3), pdfUrl, doAutoCut, $status, $btn, storageKey).catch(function(err) {
                    if (err && err.billSent) return Promise.reject(err);
                    var msg = String((err && err.message) || '');
                    // Re-open Bluetooth picker instead of RawBT when the saved device is gone.
                    if (/not found|not connected|Select the printer again|not a receipt printer/i.test(msg) && isAndroidPhone()) {
                        try { localStorage.removeItem(storageKey); } catch (eClr) {}
                        return printViaPhoneBluetooth(pdfUrl, doAutoCut, $status, $btn, {
                            select: opts.select || '',
                            status: opts.status || '',
                            storageKey: storageKey,
                            list: opts.list || ''
                        });
                    }
                    return Promise.reject(err);
                });
            }

            var qzReady = printerName && printerName !== BROWSER_PRINTER && window.qz && qz.websocket && typeof qz.websocket.isActive === 'function' && qz.websocket.isActive();
            if (!qzReady && isAndroidPhone()) {
                return printViaPhoneBluetooth(pdfUrl, doAutoCut, $status, $btn, {
                    select: opts.select || '',
                    status: opts.status || '',
                    storageKey: storageKey,
                    list: opts.list || ''
                });
            }
            if (!qzReady && isIosPhone()) {
                return printViaPhoneShare(pdfUrl, $status, $btn, 'printer');
            }

            if (!printerName || useBrowserPrint(printerName)) {
                return printWithBrowser(opts, pdfUrl, $status, $btn);
            }

            $btn.prop('disabled', true);
            setStatus($status, 'Printing…', false);

            var config = qz.configs.create(printerName, {
                rasterize: true,
                scaleContent: true,
                margins: 0,
                interpolation: 'nearest-neighbor',
                copies: 1
            });
            var openDrawer = !!printPrefs().cashDrawer && isThermalPrinterName(printerName);
            // USB / QZ only: request a slightly larger font PDF (Bluetooth fast print is unchanged).
            var usbPdfUrl = (function() {
                try {
                    var u = new URL(absoluteUrl(pdfUrl));
                    u.searchParams.set('usb_print', '1');
                    return u.href;
                } catch (e) {
                    return pdfUrl + (String(pdfUrl).indexOf('?') >= 0 ? '&' : '?') + 'usb_print=1';
                }
            })();

            return ensureConnected()
                .then(function() {
                    try {
                        localStorage.setItem(storageKey, printerName);
                        localStorage.setItem('posThermalAutoCut', doAutoCut ? '1' : '0');
                    } catch (e) {}
                    return qz.print(config, [{
                        type: 'pixel',
                        format: 'pdf',
                        flavor: 'file',
                        data: usbPdfUrl
                    }]);
                })
                .then(function() {
                    if (!doAutoCut && !openDrawer) return;
                    var rawConfig = qz.configs.create(printerName);
                    var cmds = [];
                    if (openDrawer) cmds.push('\x1B\x70\x00\x19\xFA');
                    if (doAutoCut) cmds.push('\x0A\x0A\x0A\x0A', '\x1D\x56\x00');
                    return qz.print(rawConfig, cmds);
                })
                .then(function() {
                    setStatus($status, 'Printed' + (doAutoCut ? ' + cut' : ''), false);
                    $btn.prop('disabled', false);
                })
                .catch(function(err) {
                    console.warn('QZ print failed', err);
                    $btn.prop('disabled', false);
                    setStatus($status, 'Print failed', true);
                    var msg = (err && err.message) ? String(err.message) : 'Print failed';
                    var qzDown = !window.qz || !qz.websocket || !qz.websocket.isActive();
                    if (qzDown || /connect|websocket|refused|blocked|certificate|sign/i.test(msg)) {
                        state.qzDown = true;
                        return printWithBrowser(opts, pdfUrl, $status, $btn);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Print failed',
                            text: msg,
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                    throw err;
                });
        }

        function openPdfForPhonePrint(pdfUrl, $status, kind) {
            var opened = window.open(pdfUrl, '_blank');
            if (!opened) {
                window.location.assign(pdfUrl);
            }
            setStatus($status, 'Bill opened. Tap Share, then Print, and choose the ' + (kind || 'printer') + '.', false);
        }

        function sharePdfForPhonePrint(pdfUrl, $status, kind) {
            kind = kind || 'printer';
            return fetch(pdfUrl, { credentials: 'same-origin' })
                .then(function(res) {
                    if (!res.ok) {
                        throw new Error('Invoice download failed');
                    }
                    return res.blob();
                })
                .then(function(blob) {
                    var file = new File([blob], 'invoice.pdf', { type: blob.type || 'application/pdf' });
                    if (navigator.share && typeof navigator.canShare === 'function' && navigator.canShare({ files: [file] })) {
                        return navigator.share({
                            files: [file],
                            title: 'Invoice'
                        }).then(function() {
                            setStatus($status, 'Choose Print, then the ' + kind + ' printer.', false);
                        }).catch(function(err) {
                            if (err && err.name === 'AbortError') {
                                setStatus($status, 'Print cancelled', false);
                                return;
                            }
                            openPdfForPhonePrint(pdfUrl, $status, kind);
                        });
                    }
                    openPdfForPhonePrint(pdfUrl, $status, kind);
                })
                .catch(function() {
                    openPdfForPhonePrint(pdfUrl, $status, kind);
                });
        }

        function printViaPhoneShare(pdfUrl, $status, $btn, kind) {
            if ($btn && $btn.length) $btn.prop('disabled', true);
            setStatus($status, 'Opening the phone print list…', false);
            return sharePdfForPhonePrint(pdfUrl, $status, kind).finally(function() {
                if ($btn && $btn.length) $btn.prop('disabled', false);
            });
        }

        function printWithBrowser(opts, pdfUrl, $status, $btn) {
            pdfUrl = String(pdfUrl || '').split('#')[0];
            var frame = null;
            if (opts.frame) {
                frame = typeof opts.frame === 'string' ? document.querySelector(opts.frame) : opts.frame;
            }
            if ($btn && $btn.length) $btn.prop('disabled', true);

            if (isMobileDevice()) {
                setStatus($status, 'Opening phone printer list…', false);
                return sharePdfForPhonePrint(pdfUrl, $status).finally(function() {
                    if ($btn && $btn.length) $btn.prop('disabled', false);
                });
            }

            try {
                if (frame && frame.contentWindow && frame.src && frame.src.indexOf('about:blank') !== 0) {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                    setStatus($status, 'Choose the printer in the print window.', false);
                    if ($btn && $btn.length) $btn.prop('disabled', false);
                    return Promise.resolve();
                }
            } catch (e) {}

            var popup = window.open(pdfUrl, '_blank');
            if (!popup) {
                setStatus($status, 'Allow popups to print', true);
                if ($btn && $btn.length) $btn.prop('disabled', false);
                return Promise.reject(new Error('Popup blocked'));
            }
            setTimeout(function() {
                try {
                    popup.focus();
                    popup.print();
                } catch (e2) {}
            }, 700);
            setStatus($status, 'Choose the printer in the print window.', false);
            if ($btn && $btn.length) $btn.prop('disabled', false);
            return Promise.resolve();
        }

        $(document).on('change', '#salesPrinterSelect, #posPrinterSelect', function() {
            syncAutoCutForPrinter($(this).val());
        });

        $(function restoreFastPrint() {
            var on = true;
            try { on = localStorage.getItem('btFastPrint') !== '0'; } catch (e) {}
            $('.bt-fast-print-toggle').prop('checked', on);
        });
        $(function moveInvoicePrintModalsToBody() {
            $('#salesInvoicePrintModal, #posInvoicePrintModal').each(function() {
                if (this.parentNode !== document.body) document.body.appendChild(this);
            });
        });
        $(document).on('change', '.bt-fast-print-toggle', function() {
            var on = $(this).is(':checked');
            $('.bt-fast-print-toggle').prop('checked', on);
            try { localStorage.setItem('btFastPrint', on ? '1' : '0'); } catch (e) {}
        });

        $(document).on('click', '.desktop-bt-print-btn', function() {
            chooseDesktopBluetooth({
                select: $(this).attr('data-select'),
                status: $(this).attr('data-status'),
                storageKey: $(this).attr('data-storage') || 'posSelectedPrinter'
            });
        });

        $(document).on('click', '.invoice-preview-toggle', function() {
            var $modal = $(this).closest('.modal');
            var $body = $modal.find('.modal-body').first();
            if ($body.hasClass('is-printer-pick')) {
                $body.removeClass('is-printer-pick');
                return;
            }
            var open = !$body.hasClass('is-preview-open');
            $body.toggleClass('is-preview-open', open);
            $(this).attr('aria-pressed', open ? 'true' : 'false');
            $(this).find('i').attr('class', open ? 'fa fa-eye-slash' : 'fa fa-eye');
        });
        $('#salesInvoicePrintModal, #posInvoicePrintModal').on('hidden.bs.modal', function() {
            $(this).find('.modal-body').first().removeClass('is-preview-open is-printer-pick');
            $(this).find('.invoice-preview-toggle').attr('aria-pressed', 'false').find('i').attr('class', 'fa fa-eye');
        });

        function isCompactPrintUi() {
            return window.matchMedia && window.matchMedia('(max-width: 1024.98px)').matches;
        }

        function shouldDeferPrintForPicker(buttonEl) {
            if (!isCompactPrintUi()) return false;
            var $modal = $(buttonEl).closest('.modal');
            if (!$modal.length) {
                $modal = $('#salesInvoicePrintModal:visible, #posInvoicePrintModal:visible').first();
            }
            if (!$modal.length) return false;
            var $body = $modal.find('.modal-body').first();
            if ($body.hasClass('is-printer-pick')) return false;
            $body.addClass('is-printer-pick').removeClass('is-preview-open');
            var $panel = $modal.find('.phone-print-panel');
            if ($panel.length && typeof autoPickOnOpen === 'function') {
                autoPickOnOpen({
                    select: $panel.attr('data-select'),
                    status: $panel.attr('data-status'),
                    storageKey: $panel.attr('data-storage') || 'posSelectedPrinter',
                    list: $panel.attr('data-bt')
                });
            }
            return true;
        }

        $(document).on('click', '.phone-print-tab', function() {
            var $btn = $(this);
            var $panel = $btn.closest('.phone-print-panel');
            var tab = String($btn.attr('data-tab') || '');
            var storageKey = $panel.attr('data-storage') || 'posSelectedPrinter';
            var $sel = $($panel.attr('data-select') || '');
            var $status = $($panel.attr('data-status') || '');
            $panel.find('.phone-print-tab').removeClass('is-active');
            $btn.addClass('is-active');
            $panel.find('[data-print-pane]').addClass('d-none');
            $panel.find('[data-print-pane="' + tab + '"]').removeClass('d-none');
            if (tab === 'wifi') {
                rememberPrinterValue($sel, storageKey, 'wifi:' + savedWifiHost(), 'Wi‑Fi printer');
                var wifiEl = $panel.find('[data-print-pane="wifi"]')[0];
                if (wifiEl && wifiEl.id) paintPhoneWifiPrinters('#' + wifiEl.id, storageKey, $sel);
                setStatus($status, 'Wi‑Fi printer selected. Tap Print.', false);
                return;
            }
            // Prefer a real Web Bluetooth device; never fall back to RawBT (bt:phone).
            var savedBt = '';
            try { savedBt = localStorage.getItem(storageKey) || ''; } catch (eBt) {}
            if (savedBt === 'bt:phone') {
                try { localStorage.removeItem(storageKey); } catch (eClr) {}
                savedBt = '';
            }
            if (savedBt.indexOf('bt:') === 0) {
                rememberPrinterValue($sel, storageKey, savedBt, $sel.find('option[value="' + savedBt.replace(/"/g, '') + '"]').text() || 'Bluetooth printer');
                setStatus($status, 'Bluetooth printer selected. Tap Print.', false);
                return;
            }
            if (navigator.bluetooth && typeof navigator.bluetooth.requestDevice === 'function') {
                setStatus($status, 'Tap Choose nearby to pick your Bluetooth printer.', false);
                var btEl = $panel.find('[data-print-pane="bt"]')[0];
                pickNearbyPrinter({
                    select: $panel.attr('data-select'),
                    status: $panel.attr('data-status'),
                    storageKey: storageKey,
                    list: btEl && btEl.id ? ('#' + btEl.id) : ''
                });
                return;
            }
            var phoneOpt = phoneTransportOption();
            if (phoneOpt) {
                rememberPrinterValue($sel, storageKey, phoneOpt.value, phoneOpt.name);
            }
            setStatus($status, 'Use Chrome to choose a Bluetooth printer.', false);
        });

        function waitForQzLib(ms) {
            if (window.qz) return Promise.resolve(true);
            return new Promise(function(resolve) {
                var waited = 0;
                var t = setInterval(function() {
                    waited += 100;
                    if (window.qz || waited >= ms) {
                        clearInterval(t);
                        resolve(!!window.qz);
                    }
                }, 100);
            });
        }

        function printViaSerial(port, pdfUrl, autoCut, $status, $btn, storageKey) {
            autoCut = wantsAutoCut(autoCut);
            if ($btn && $btn.length) $btn.prop('disabled', true);
            setStatus($status, 'Printing to ' + port + '…', false);
            try { localStorage.setItem(storageKey || 'posSelectedPrinter', 'serial:' + port); } catch (e) {}
            var opened = false;
            return ensureConnected().then(function() {
                return bluetoothBillBytes(pdfUrl, autoCut);
            }).then(function(bytes) {
                var end = bytes.length;
                while (end > 0 && bytes[end - 1] === 0) end--;
                var payload = new Uint8Array(end + 16);
                payload.set(bytes.subarray(0, end), 0);
                if (autoCut && !bytes.cutIncluded) {
                    var cut = phoneCutBytes();
                    var withCut = new Uint8Array(payload.length + cut.length);
                    withCut.set(payload, 0);
                    withCut.set(cut, payload.length);
                    payload = withCut;
                }
                return qz.serial.openPort(port, { baudRate: 9600, dataBits: 8, stopBits: 1, parity: 'NONE', flowControl: 'NONE' }).then(function() {
                    opened = true;
                    return qz.serial.sendData(port, { type: 'BASE64', data: bytesToBase64(payload) });
                });
            }).then(function() {
                // Give the Bluetooth link time to drain before the port closes.
                return new Promise(function(resolve) { setTimeout(resolve, 1500); });
            }).then(function() {
                setStatus($status, 'Printed' + (autoCut ? ' + cut' : ''), false);
            }).catch(function(err) {
                var msg = (err && err.message) ? String(err.message) : 'Print failed';
                setStatus($status, msg, true);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Print failed', text: 'Could not print to ' + port + '. Make sure the printer is on and paired in Windows Bluetooth settings.', confirmButtonColor: '#ff9f43' });
                }
                throw err;
            }).then(function() {
                if (opened) return qz.serial.closePort(port).catch(function() {});
            }, function(err) {
                if (opened) qz.serial.closePort(port).catch(function() {});
                throw err;
            }).then(function() {
                if ($btn && $btn.length) $btn.prop('disabled', false);
            }, function(err) {
                if ($btn && $btn.length) $btn.prop('disabled', false);
                throw err;
            });
        }

        function listAllPrinters() {
            var pc = waitForQzLib(3000).then(function(ok) {
                if (!ok) return [];
                return withTimeout(ensureConnected().then(function() { return qz.printers.find(); }), 7000, []);
            }).then(function(names) {
                return (names || []).map(function(name) {
                    return { value: name, label: name, kind: isThermalPrinterName(name) ? 'thermal' : 'pc' };
                });
            }, function() { return []; });

            var granted = (navigator.bluetooth && typeof navigator.bluetooth.getDevices === 'function')
                ? navigator.bluetooth.getDevices().then(function(devices) {
                    return (devices || []).filter(function(d) { return d && d.id; }).map(function(d) {
                        rememberBluetoothDevice({ id: d.id, name: d.name || 'Bluetooth printer', device: d, transport: 'web' });
                        return { value: 'bt:' + d.id, label: d.name || 'Bluetooth printer', kind: 'bluetooth' };
                    });
                }).catch(function() { return []; })
                : Promise.resolve([]);

            var bridge = isAndroidPhone()
                ? fetchBridgePrinters().then(function(list) {
                    return (list || []).map(function(row) {
                        rememberBluetoothDevice(row);
                        return { value: 'bt:' + row.id, label: row.name || 'Bluetooth printer', kind: 'bluetooth' };
                    });
                })
                : Promise.resolve([]);

            return Promise.all([pc, granted, bridge]).then(function(groups) {
                var seen = {};
                var rows = [];
                Object.keys(state.btDevices || {}).forEach(function(id) {
                    var row = state.btDevices[id];
                    groups[1].push({ value: 'bt:' + id, label: (row && row.name) || 'Bluetooth printer', kind: 'bluetooth' });
                });
                groups.forEach(function(list) {
                    list.forEach(function(row) {
                        if (seen[row.value]) return;
                        seen[row.value] = true;
                        rows.push(row);
                    });
                });
                return { printers: rows, qzConnected: !!(window.qz && qz.websocket && qz.websocket.isActive()) };
            });
        }

        function scanBluetoothPrinter() {
            if (!navigator.bluetooth || typeof navigator.bluetooth.requestDevice !== 'function') {
                return Promise.reject(new Error('Bluetooth search needs Chrome or Edge on this device.'));
            }
            return navigator.bluetooth.requestDevice(printerRequestOptions()).then(function(device) {
                rememberBluetoothDevice({ id: device.id, name: device.name || 'Bluetooth printer', device: device, transport: 'web' });
                return { value: 'bt:' + device.id, label: device.name || 'Bluetooth printer', kind: 'bluetooth' };
            }).catch(function(err) {
                if (err && (err.name === 'NotFoundError' || err.name === 'AbortError')) return null;
                throw err;
            });
        }

        window.InvoiceQzPrint = {
            listAllPrinters: listAllPrinters,
            scanBluetoothPrinter: scanBluetoothPrinter,
            isMobileDevice: isMobileDevice,
            isThermalPrinterName: isThermalPrinterName,
            browserPrinter: BROWSER_PRINTER,
            refreshPrinters: refreshPrinters,
            printInvoice: printInvoice,
            preparePhonePrint: preparePhonePrint,
            pickNearbyPrinter: pickNearbyPrinter,
            autoPickOnOpen: autoPickOnOpen,
            shouldDeferPrintForPicker: shouldDeferPrintForPicker,
            ensureConnected: ensureConnected,
            showQzRequired: showQzRequired,
            absoluteUrl: absoluteUrl
        };
    }

    function startBoot() {
        if (window.jQuery) {
            boot(window.jQuery);
            return;
        }
        var tries = 0;
        var timer = setInterval(function() {
            tries++;
            if (window.jQuery) {
                clearInterval(timer);
                boot(window.jQuery);
            } else if (tries > 100) {
                clearInterval(timer);
                console.error('InvoiceQzPrint: jQuery not found');
            }
        }, 50);
    }

    startBoot();
})(window);
</script>
