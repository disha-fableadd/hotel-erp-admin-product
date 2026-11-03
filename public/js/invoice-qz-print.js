/**
 * Shared QZ Tray invoice printing — list PC printers, print PDF, thermal auto-cut.
 * Kept for older deploys that still load this file via asset('js/invoice-qz-print.js').
 * Prefer the Blade partial: resources/views/partials/invoice-qz-print-script.blade.php
 */
(function(window) {
    'use strict';
    window.__INVOICE_QZ_PRINT_VER = '2026-09-18c';

    function boot($) {
        if (!$ || window.InvoiceQzPrint) {
            return;
        }

        var state = {
            securityReady: false,
            connecting: null
        };

        function setStatus($el, text, isError) {
            if (!$el || !$el.length) return;
            $el.text(text || '');
            $el.css('color', isError ? '#ea5455' : '#28c76f');
        }

        function isThermalPrinterName(name) {
            var n = String(name || '').toLowerCase();
            return /thermal|receipt|pos[\s_-]?printer|tvs|snbc|lp46|dlite|epson\s*tm|tm-t|star\s*tsp|bixolon|citizen|rongta|xprinter|gprinter|80\s*mm|58\s*mm|billing|godex|tsc\s*t|zebra\s*zd/.test(n);
        }

        function setupSecurity() {
            if (!window.qz || state.securityReady) return;

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

            function run() {
                return ensureConnected()
                    .then(function() {
                        return qz.printers.find();
                    })
                    .then(function(printers) {
                        fillPrinterSelect($sel, printers || [], storageKey);
                        $sel.prop('disabled', false);
                        setStatus($status, 'Ready · ' + (printers ? printers.length : 0) + ' printer(s)', false);
                        return printers;
                    })
                    .catch(function(err) {
                        console.warn('QZ printers error', err);
                        $sel.html('<option value="">QZ Tray not connected</option>');
                        $sel.prop('disabled', true);
                        if ($install.length) $install.show();
                        setStatus($status, !window.qz ? 'qz-tray.js missing' : 'Open QZ Tray & Allow this site', true);
                        throw err;
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
                        '<ol style="padding-left:18px;margin:0 0 8px;">' +
                        '<li>Open <b>QZ Tray</b> (green Q in system tray).</li>' +
                        '<li>Click <b>Allow</b> for <b>' + window.location.host + '</b>.</li>' +
                        '<li>Or: QZ → Site Manager → Allow this site.</li>' +
                        '<li>Refresh printer list, select SNBC TVS LP46, Print.</li>' +
                        '</ol>' + extra +
                        '</div>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ff9f43'
                });
            } else {
                alert('Open QZ Tray, Allow this website, then refresh the printer list.');
            }
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

            if (!window.qz) {
                showQzRequired('Upload public/js/qz-tray.js to the server.');
                return Promise.reject(new Error('QZ not loaded'));
            }

            if (!printerName) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Select a printer',
                        text: 'Choose a connected printer from the list first.',
                        confirmButtonColor: '#ff9f43'
                    });
                }
                return Promise.reject(new Error('No printer selected'));
            }

            $btn.prop('disabled', true);
            setStatus($status, 'Printing…', false);

            var config = qz.configs.create(printerName, {
                rasterize: true,
                scaleContent: true,
                margins: 0,
                interpolation: 'nearest-neighbor'
            });

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
                        data: pdfUrl
                    }]);
                })
                .then(function() {
                    if (!doAutoCut) return;
                    return qz.print(config, [
                        '\x0A\x0A\x0A\x0A',
                        '\x1D\x56\x00'
                    ]);
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
                        showQzRequired(msg);
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

        window.InvoiceQzPrint = {
            isThermalPrinterName: isThermalPrinterName,
            refreshPrinters: refreshPrinters,
            printInvoice: printInvoice,
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
