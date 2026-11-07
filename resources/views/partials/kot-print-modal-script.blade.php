{{-- KOT Print modal handlers (Dine In Place/Update) — Petpooja-style delta KOT --}}
<script>
(function($) {
    'use strict';
    if (window.__KOT_PRINT_MODAL_BOUND) return;
    window.__KOT_PRINT_MODAL_BOUND = true;
    window._kotReloadOnClose = false;
    window._kotOrderId = null;
    window._kotUseDelta = true;
    window._kotStation = '';
    window._kotPrintTarget = 'kitchen'; // kitchen | billing
    window._kotPendingCount = null;
    window._kotBillingCount = 0;
    window._kotKitchenCount = 0;

    function shortName(name) {
        name = String(name || '').trim();
        if (!name) return 'Not set';
        return name.length > 32 ? (name.slice(0, 30) + '…') : name;
    }

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function isBillingKotTarget() {
        return String(window._kotPrintTarget || '') === 'billing'
            || String(window._kotStation || '').toLowerCase() === 'counter';
    }

    function kotPrinterStorageKey() {
        return isBillingKotTarget() ? 'posSelectedPrinter' : 'posSelectedKotPrinter';
    }

    function resolveBillPrinterName() {
        var bill = '';
        try { bill = ($('#posPrinterSelect').val() || localStorage.getItem('posSelectedPrinter') || '').trim(); } catch (e) {}
        if (bill) return bill;
        try {
            var prefs = window.__INVOICE_PRINT_PREFS || {};
            if (prefs.defaultPrinter) return String(prefs.defaultPrinter);
        } catch (e2) {}
        return '';
    }

    function kotPdfUrl(orderId, useDelta, printTarget) {
        var url = '/kitchen/kot/' + orderId + '/pdf';
        var params = [];
        if (useDelta) params.push('delta=1');
        if (window._kotStation) params.push('station=' + encodeURIComponent(window._kotStation));
        var target = printTarget || window._kotPrintTarget || '';
        if (target) params.push('print_target=' + encodeURIComponent(target));
        if (params.length) url += '?' + params.join('&');
        return url;
    }

    function updateKotPrintUi() {
        var val = ($('#kotPrinterSelect').val() || '').trim();
        var label = val ? shortName(($('#kotPrinterSelect option:selected').text() || '').trim()) : 'Not set';
        var pending = window._kotPendingCount;
        var dest = isBillingKotTarget() ? 'Counter/Bill printer' : 'Kitchen';
        if (pending === 0) {
            $('#kotPrintSummaryLabel').text('No new items for ' + (isBillingKotTarget() ? 'counter' : 'kitchen'));
        } else if (pending > 0) {
            $('#kotPrintSummaryLabel').text(pending + ' new/changed · ' + dest + ' → ' + label);
        } else {
            $('#kotPrintSummaryLabel').text(dest + ' → ' + label);
        }
    }

    function refreshKotPrinterList() {
        if (!window.InvoiceQzPrint) {
            $('#kotQzStatus').text('QZ loading…').css('color', '#6c757d');
            return Promise.resolve();
        }
        var storageKey = kotPrinterStorageKey();
        return Promise.resolve(InvoiceQzPrint.refreshPrinters({
            select: '#kotPrinterSelect',
            status: '#kotQzStatus',
            storageKey: storageKey
        })).then(function() {
            try {
                var saved = localStorage.getItem(storageKey) || '';
                // Billing-target KOT prefers the bill printer
                if (isBillingKotTarget()) {
                    var billPrinter = resolveBillPrinterName();
                    if (billPrinter) saved = billPrinter;
                }
                if (saved) {
                    var found = false;
                    $('#kotPrinterSelect option').each(function() {
                        if ($(this).val() === saved) found = true;
                    });
                    if (found) $('#kotPrinterSelect').val(saved);
                }
            } catch (e) {}
            updateKotPrintUi();
        }).catch(function() {
            updateKotPrintUi();
        });
    }

    function markKotPrinted(orderId, printTarget, station) {
        if (!orderId) return Promise.resolve();
        return $.ajax({
            url: '/kitchen/kot/' + orderId + '/mark-printed',
            type: 'POST',
            dataType: 'json',
            data: {
                print_target: printTarget || window._kotPrintTarget || '',
                station: station || window._kotStation || ''
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            }
        }).catch(function(err) {
            console.warn('KOT mark-printed failed', err);
        });
    }

    function fetchDeltaSummary(orderId) {
        return $.ajax({
            url: '/kitchen/kot/' + orderId + '/delta-summary',
            type: 'GET',
            dataType: 'json',
            headers: { 'Accept': 'application/json' }
        });
    }

    function printOneKotPdf(orderId, pdfUrl, printerName, doAutoCut, storageKey, frame) {
        if (!window.InvoiceQzPrint || !printerName) {
            window.open(pdfUrl, '_blank');
            return Promise.resolve();
        }
        try { localStorage.setItem(storageKey, printerName); } catch (e) {}
        return Promise.resolve(InvoiceQzPrint.printInvoice({
            printerName: printerName,
            pdfUrl: pdfUrl,
            autoCut: doAutoCut,
            frame: frame || null,
            status: '#kotQzStatus',
            button: '#printKotModalBtn',
            select: '#kotPrinterSelect',
            storageKey: storageKey
        }));
    }

    function printKotFromModal() {
        var orderId = window._kotOrderId;
        if (!orderId) return;

        if (window._kotPendingCount === 0) {
            $('#kotQzStatus').text('Nothing new to send').css('color', '#ea5455');
            return;
        }

        var frame = document.getElementById('kotPreviewIframe');
        var pdfUrl = frame && frame.src ? frame.src : kotPdfUrl(orderId, window._kotUseDelta);
        var printerName = ($('#kotPrinterSelect').val() || '').trim();
        var doAutoCut = $('#kotThermalAutoCut').is(':checked');
        var storageKey = kotPrinterStorageKey();
        var target = window._kotPrintTarget || 'kitchen';

        if (!window.InvoiceQzPrint || !printerName) {
            window.open(pdfUrl, '_blank');
            $('#kotQzStatus').text(printerName ? 'Opened' : ('Select a ' + (isBillingKotTarget() ? 'bill/counter' : 'kitchen') + ' printer')).css('color', printerName ? '#28c76f' : '#ea5455');
            markKotPrinted(orderId, target, window._kotStation).then(function() {
                window._kotPendingCount = 0;
                updateKotPrintUi();
            });
            return;
        }

        $('#printKotModalBtn').prop('disabled', true);
        $('#kotQzStatus').text(isBillingKotTarget() ? 'Printing counter KOT…' : 'Printing kitchen KOT…').css('color', '#6c757d');

        printOneKotPdf(orderId, pdfUrl, printerName, doAutoCut, storageKey, frame).then(function() {
            $('#kotQzStatus').text(isBillingKotTarget() ? 'Counter KOT printed' : 'Kitchen KOT printed').css('color', '#28c76f');
            return markKotPrinted(orderId, target, window._kotStation);
        }).then(function() {
            // After kitchen print from Place/Update, also send billing-category items to bill printer
            if (window._kotUseDelta && !isBillingKotTarget() && (window._kotBillingCount || 0) > 0) {
                var billPrinter = resolveBillPrinterName() || printerName;
                var billingPdf = kotPdfUrl(orderId, true, 'billing');
                $('#kotQzStatus').text('Printing counter items on bill printer…').css('color', '#6c757d');
                return printOneKotPdf(orderId, billingPdf, billPrinter, doAutoCut, 'posSelectedPrinter', null)
                    .then(function() {
                        return markKotPrinted(orderId, 'billing', 'Counter');
                    })
                    .then(function() {
                        $('#kotQzStatus').text('Kitchen + counter KOT printed').css('color', '#28c76f');
                    })
                    .catch(function(err) {
                        console.warn('Counter KOT print failed', err);
                        window.open(billingPdf, '_blank');
                        return markKotPrinted(orderId, 'billing', 'Counter');
                    });
            }
            return null;
        }).then(function() {
            window._kotPendingCount = 0;
            window._kotBillingCount = 0;
            window._kotKitchenCount = 0;
            updateKotPrintUi();
        }).catch(function(err) {
            console.warn('KOT modal print failed', err);
            $('#kotQzStatus').text('Print failed — opened preview').css('color', '#ea5455');
            window.open(pdfUrl, '_blank');
            return markKotPrinted(orderId, target, window._kotStation).then(function() {
                window._kotPendingCount = 0;
                updateKotPrintUi();
            });
        }).then(function() {
            $('#printKotModalBtn').prop('disabled', false);
        });
    }

    function closeKotModal() {
        var modalEl = document.getElementById('kotPrintModal');
        var bsModal = bootstrap.Modal.getInstance(modalEl);
        if (bsModal) bsModal.hide();
        $('#kotPreviewIframe').attr('src', '');
        $('#kotPrinterSettingsPanel').hide();
        $('#kotPrinterSettingsToggle').html('<i class="fas fa-cog"></i> Printer settings');
        if (window._kotReloadOnClose) {
            window._kotReloadOnClose = false;
            location.reload();
        }
    }

    function openKotModalUi(orderId, useDelta) {
        var kotUrl = kotPdfUrl(orderId, useDelta);
        var $iframe = $('#kotPreviewIframe');
        var $loader = $('#kotIframeLoader');
        $loader.show();
        $iframe.attr('src', '');
        setTimeout(function() {
            $iframe.attr('src', kotUrl);
        }, 50);
        $iframe.off('load.kotGen').on('load.kotGen', function() {
            $loader.hide();
        });

        $('#kotPrinterSettingsPanel').hide();
        $('#kotPrinterSettingsToggle').html('<i class="fas fa-cog"></i> Printer settings');
        $('#kotQzStatus').text('');
        $('#printKotModalBtn').prop('disabled', window._kotPendingCount === 0);
        $('#kotPrintModalHint').html(useDelta
            ? (isBillingKotTarget()
                ? 'Counter categories → bill printer — Press <kbd>F10</kbd> or Print'
                : 'Kitchen categories → kitchen printer (counter categories auto-print on bill printer) — Press <kbd>F10</kbd>')
            : (isBillingKotTarget()
                ? 'Press <kbd>F10</kbd> or Print to send to bill/counter printer'
                : 'Press <kbd>F10</kbd> or Print to send to kitchen'));

        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('kotPrintModal'), {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();
        updateKotPrintUi();
    }

    /**
     * opts.delta !== false → Petpooja delta (default true for dine-in Place/Update)
     * opts.station → kitchen station filter for PDF
     * opts.printTarget → kitchen | billing (billing uses bill/counter printer)
     * opts.reloadOnClose → reload page after close
     * opts.skipIfEmpty → if no new items, toast + reload without modal (default true when delta)
     */
    window.showKotPrintModal = function(orderId, opts) {
        opts = opts || {};
        window._kotOrderId = orderId;
        window._kotReloadOnClose = !!opts.reloadOnClose;
        window._kotUseDelta = opts.delta !== false;
        window._kotStation = opts.station ? String(opts.station) : '';
        window._kotPrintTarget = opts.printTarget
            ? String(opts.printTarget)
            : (String(window._kotStation).toLowerCase() === 'counter' ? 'billing' : 'kitchen');
        window._kotPendingCount = null;
        window._kotBillingCount = 0;
        window._kotKitchenCount = 0;

        var skipIfEmpty = opts.skipIfEmpty !== false && window._kotUseDelta;

        if (!window._kotUseDelta) {
            openKotModalUi(orderId, false);
            return;
        }

        fetchDeltaSummary(orderId).then(function(res) {
            var count = (res && typeof res.count === 'number') ? res.count : 0;
            window._kotPendingCount = count;
            window._kotKitchenCount = (res && typeof res.kitchen_count === 'number') ? res.kitchen_count : 0;
            window._kotBillingCount = (res && typeof res.billing_count === 'number') ? res.billing_count : 0;

            // Preview kitchen PDF by default; if only billing items pending, open billing target
            if (!opts.printTarget && window._kotKitchenCount === 0 && window._kotBillingCount > 0) {
                window._kotPrintTarget = 'billing';
                window._kotStation = 'Counter';
            } else if (!opts.printTarget && window._kotKitchenCount > 0) {
                // Modal preview = kitchen only; billing auto-prints after
                window._kotPrintTarget = 'kitchen';
            }

            if (skipIfEmpty && count === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'No new kitchen items',
                        text: 'Everything on this order was already sent to kitchen/counter.',
                        icon: 'info',
                        confirmButtonColor: '#ff9f43',
                        timer: 2200,
                        showConfirmButton: true
                    }).then(function() {
                        if (window._kotReloadOnClose) {
                            window._kotReloadOnClose = false;
                            location.reload();
                        }
                    });
                } else {
                    alert('No new kitchen items — already sent to kitchen.');
                    if (window._kotReloadOnClose) {
                        window._kotReloadOnClose = false;
                        location.reload();
                    }
                }
                return;
            }
            openKotModalUi(orderId, true);
        }).catch(function() {
            // If summary fails, still open full delta PDF preview
            window._kotPendingCount = null;
            openKotModalUi(orderId, true);
        });
    };

    window.printKotForOrder = function(orderId) {
        if (typeof window.showKotPrintModal === 'function') {
            window.showKotPrintModal(orderId, { reloadOnClose: false, delta: true });
            return Promise.resolve();
        }
        window.open('/kitchen/kot/' + orderId + '/print?delta=1', '_blank');
        return Promise.resolve();
    };

    $(document).on('click', '#kotRefreshPrintersBtn', function() {
        refreshKotPrinterList();
    });

    $(document).on('click', '#kotPrinterSettingsToggle', function() {
        var $panel = $('#kotPrinterSettingsPanel');
        var open = !$panel.is(':visible');
        $panel.slideToggle(160);
        $(this).html(open
            ? '<i class="fas fa-times"></i> Hide settings'
            : '<i class="fas fa-cog"></i> Printer settings');
        if (open) refreshKotPrinterList();
    });

    $(document).on('change', '#kotPrinterSelect', function() {
        try { localStorage.setItem(kotPrinterStorageKey(), $(this).val() || ''); } catch (e) {}
        updateKotPrintUi();
    });

    $(document).on('click', '#printKotModalBtn', function() {
        printKotFromModal();
    });

    $(document).on('click', '#closeKotBtn, #closeKotModalBtn', function() {
        closeKotModal();
    });

    $(document).on('shown.bs.modal', '#kotPrintModal', function() {
        var tries = 0;
        (function waitQz() {
            tries++;
            if (window.InvoiceQzPrint || tries > 40) {
                refreshKotPrinterList().then(function() {
                    var val = ($('#kotPrinterSelect').val() || '').trim();
                    var saved = '';
                    try { saved = localStorage.getItem('posSelectedKotPrinter') || ''; } catch (e) {}
                    if (!val && !saved) {
                        $('#kotPrinterSettingsPanel').show();
                        $('#kotPrinterSettingsToggle').html('<i class="fas fa-times"></i> Hide settings');
                    }
                });
                return;
            }
            setTimeout(waitQz, 150);
        })();
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'F10' || e.keyCode === 121) {
            var modalEl = document.getElementById('kotPrintModal');
            if (modalEl && modalEl.classList.contains('show')) {
                e.preventDefault();
                printKotFromModal();
            }
        }
    });
})(jQuery);
</script>
