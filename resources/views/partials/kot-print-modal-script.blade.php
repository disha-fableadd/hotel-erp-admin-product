{{-- KOT Print modal handlers (Dine In Place/Update) --}}
<script>
(function($) {
    'use strict';
    if (window.__KOT_PRINT_MODAL_BOUND) return;
    window.__KOT_PRINT_MODAL_BOUND = true;
    window._kotReloadOnClose = false;
    window._kotOrderId = null;

    function shortName(name) {
        name = String(name || '').trim();
        if (!name) return 'Not set';
        return name.length > 32 ? (name.slice(0, 30) + '…') : name;
    }

    function updateKotPrintUi() {
        var val = ($('#kotPrinterSelect').val() || '').trim();
        var label = val ? shortName(($('#kotPrinterSelect option:selected').text() || '').trim()) : 'Not set';
        $('#kotPrintSummaryLabel').text('Kitchen → ' + label);
    }

    function refreshKotPrinterList() {
        if (!window.InvoiceQzPrint) {
            $('#kotQzStatus').text('QZ loading…').css('color', '#6c757d');
            return Promise.resolve();
        }
        return Promise.resolve(InvoiceQzPrint.refreshPrinters({
            select: '#kotPrinterSelect',
            status: '#kotQzStatus',
            storageKey: 'posSelectedKotPrinter'
        })).then(function() {
            // Prefer saved kitchen printer; else leave first thermal if any
            try {
                var saved = localStorage.getItem('posSelectedKotPrinter') || '';
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

    function printKotFromModal() {
        var orderId = window._kotOrderId;
        if (!orderId) return;

        var frame = document.getElementById('kotPreviewIframe');
        var pdfUrl = frame && frame.src ? frame.src : ('/kitchen/kot/' + orderId + '/pdf');
        var printerName = ($('#kotPrinterSelect').val() || '').trim();
        var doAutoCut = $('#kotThermalAutoCut').is(':checked');

        if (!window.InvoiceQzPrint || !printerName) {
            window.open(pdfUrl, '_blank');
            $('#kotQzStatus').text(printerName ? 'Opened' : 'Select a kitchen printer').css('color', printerName ? '#28c76f' : '#ea5455');
            return;
        }

        try { localStorage.setItem('posSelectedKotPrinter', printerName); } catch (e) {}
        $('#printKotModalBtn').prop('disabled', true);
        $('#kotQzStatus').text('Printing KOT…').css('color', '#6c757d');

        Promise.resolve(InvoiceQzPrint.printInvoice({
            printerName: printerName,
            pdfUrl: pdfUrl,
            autoCut: doAutoCut,
            frame: frame,
            status: '#kotQzStatus',
            button: '#printKotModalBtn',
            select: '#kotPrinterSelect',
            storageKey: 'posSelectedKotPrinter'
        })).then(function() {
            $('#kotQzStatus').text('KOT printed').css('color', '#28c76f');
        }).catch(function(err) {
            console.warn('KOT modal print failed', err);
            $('#kotQzStatus').text('Print failed — opened preview').css('color', '#ea5455');
            window.open(pdfUrl, '_blank');
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

    window.showKotPrintModal = function(orderId, opts) {
        opts = opts || {};
        window._kotOrderId = orderId;
        window._kotReloadOnClose = !!opts.reloadOnClose;

        var kotUrl = '/kitchen/kot/' + orderId + '/pdf';
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

        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('kotPrintModal'), {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();
        updateKotPrintUi();
    };

    // Keep silent print helper for callers that still need it
    window.printKotForOrder = function(orderId) {
        if (typeof window.showKotPrintModal === 'function') {
            window.showKotPrintModal(orderId, { reloadOnClose: false });
            return Promise.resolve();
        }
        window.open('/kitchen/kot/' + orderId + '/print', '_blank');
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
        try { localStorage.setItem('posSelectedKotPrinter', $(this).val() || ''); } catch (e) {}
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
