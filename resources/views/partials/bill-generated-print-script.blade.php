{{-- Shared Bill Generated print handlers (POS + Dine In). Requires #billGeneratedModal markup + InvoiceQzPrint. --}}
<script>
(function($) {
    'use strict';
    if (window.__BILL_GEN_PRINT_BOUND) return;
    window.__BILL_GEN_PRINT_BOUND = true;
    window._billReloadOnClose = false;

    function shortPrinterName(name) {
        name = String(name || '').trim();
        if (!name) return '';
        return name.length > 28 ? (name.slice(0, 26) + '…') : name;
    }

    function updateBillPrintUi() {
        var alsoKot = $('#posPrintKotWithBill').is(':checked');
        $('#printBillBtnLabel').text(alsoKot ? 'Print Bill + KOT' : 'Print Bill');
        $('#posKotPrinterRow').css('opacity', alsoKot ? 1 : 0.45);
        $('#posKotPrinterSelect, #posKotPrinterRow .desktop-bt-print-btn').prop('disabled', !alsoKot);

        var billName = ($('#posPrinterSelect option:selected').text() || '').trim();
        var billVal = ($('#posPrinterSelect').val() || '').trim();
        if (!billVal) billName = 'Not set';
        else billName = shortPrinterName(billName);

        var summary = 'Bill → ' + billName;
        if (alsoKot) {
            var kotVal = ($('#posKotPrinterSelect').val() || '').trim();
            var kotName = kotVal
                ? shortPrinterName(($('#posKotPrinterSelect option:selected').text() || '').trim())
                : 'Same as bill';
            summary += '  ·  KOT → ' + kotName;
        }
        $('#posPrintSummaryLabel').text(summary);
    }

    function syncKotPrinterOptionsFromBill() {
        var $bill = $('#posPrinterSelect');
        var $kot = $('#posKotPrinterSelect');
        if (!$bill.length || !$kot.length) return;
        var saved = '';
        try { saved = localStorage.getItem('posSelectedKotPrinter') || ''; } catch (e) {}
        var current = $kot.val() || '';
        $kot.empty();
        $kot.append($('<option></option>').val('').text('Same as bill'));
        $bill.find('option').each(function() {
            var v = $(this).val();
            if (!v) return;
            $kot.append($('<option></option>').val(v).text($(this).text()));
        });
        function hasVal(v) {
            var found = false;
            $kot.find('option').each(function() {
                if ($(this).val() === v) found = true;
            });
            return found;
        }
        var pick = '';
        if (saved && hasVal(saved)) pick = saved;
        else if (current && hasVal(current)) pick = current;
        $kot.val(pick);
        updateBillPrintUi();
    }

    function refreshPosPrinterList() {
        if (!window.InvoiceQzPrint) {
            $('#posQzStatus').text('QZ print loading…').css('color', '#6c757d');
            return Promise.resolve();
        }
        return Promise.resolve(InvoiceQzPrint.refreshPrinters({
            select: '#posPrinterSelect',
            status: '#posQzStatus',
            installLink: '#posQzInstallLink',
            storageKey: 'posSelectedPrinter',
            list: '#posBtPrinterList',
            wifiList: '#posWifiPrinterList'
        })).then(function() {
            syncKotPrinterOptionsFromBill();
        }).catch(function() {
            syncKotPrinterOptionsFromBill();
        });
    }

    function resolveKotPrinterName() {
        var kot = ($('#posKotPrinterSelect').val() || '').trim();
        if (kot) return kot;
        return ($('#posPrinterSelect').val() || '').trim();
    }

    function openKotPrintWindow(orderId) {
        if (!orderId) return;
        var kotUrl = "/kitchen/kot/" + orderId + "/print";
        var w = window.open(kotUrl, "kot_print_" + orderId, "width=420,height=640,scrollbars=yes");
        if (!w) {
            var $frame = $('#posKotSilentFrame');
            if (!$frame.length) {
                $frame = $('<iframe id="posKotSilentFrame" title="KOT print" style="position:absolute;width:0;height:0;border:0;left:-9999px;"></iframe>');
                $('body').append($frame);
            }
            $frame.attr('src', kotUrl);
        }
    }

    function printKotPdfToPrinter(orderId, pdfUrl, printerName, storageKey, label) {
        var doAutoCut = $('#posThermalAutoCut').is(':checked');
        if (!window.InvoiceQzPrint || !printerName) {
            window.open(pdfUrl, '_blank');
            return Promise.resolve();
        }
        try { localStorage.setItem(storageKey, printerName); } catch (e) {}
        $('#posKotQzStatus').text('Printing ' + (label || 'KOT') + '…').css('color', '#6c757d');
        return Promise.resolve(InvoiceQzPrint.printInvoice({
            printerName: printerName,
            pdfUrl: pdfUrl,
            autoCut: doAutoCut,
            status: '#posKotQzStatus',
            button: '#printKotOnlyBtn',
            select: '#posKotPrinterSelect',
            storageKey: storageKey
        }));
    }

    function printKotToKitchen(orderId) {
        if (!orderId) return Promise.resolve();
        var kotPrinter = resolveKotPrinterName();
        var billPrinter = ($('#posPrinterSelect').val() || '').trim() || kotPrinter;
        var kitchenPdf = "/kitchen/kot/" + orderId + "/pdf?print_target=kitchen";
        var billingPdf = "/kitchen/kot/" + orderId + "/pdf?print_target=billing";

        // Kitchen categories → kitchen printer; billing categories → bill/counter printer
        return printKotPdfToPrinter(orderId, kitchenPdf, kotPrinter, 'posSelectedKotPrinter', 'kitchen KOT')
            .then(function() {
                return printKotPdfToPrinter(orderId, billingPdf, billPrinter, 'posSelectedPrinter', 'counter KOT');
            })
            .then(function() {
                $('#posKotQzStatus').text('KOT printed').css('color', '#28c76f');
            })
            .catch(function(err) {
                console.warn('KOT QZ print failed', err);
                $('#posKotQzStatus').text('KOT fallback…').css('color', '#ea5455');
                openKotPrintWindow(orderId);
            });
    }

    function printBillFromModal() {
        var orderId = window._billOrderId;
        if (!orderId) return;

        var frame = document.getElementById('billPreviewIframe');
        var pdfUrl = frame && frame.src ? frame.src : ("/sales/invoice/pdf/" + orderId);
        var printerName = ($('#posPrinterSelect').val() || '').trim();
        var doAutoCut = $('#posThermalAutoCut').is(':checked');
        var alsoKot = $('#posPrintKotWithBill').is(':checked');

        function afterBillPrint() {
            if (!alsoKot) return Promise.resolve();
            return printKotToKitchen(orderId);
        }

        if (!window.InvoiceQzPrint) {
            window.open(pdfUrl, "_blank");
            afterBillPrint();
            return;
        }

        if (!printerName) {
            $('#posQzStatus').text('Select a bill printer').css('color', '#ea5455');
            window.open(pdfUrl, "_blank");
            afterBillPrint();
            return;
        }

        $('#printBillBtn').prop('disabled', true);
        Promise.resolve(InvoiceQzPrint.printInvoice({
            printerName: printerName,
            pdfUrl: pdfUrl,
            autoCut: doAutoCut,
            frame: frame,
            status: '#posQzStatus',
            button: '#printBillBtn',
            select: '#posPrinterSelect',
            list: '#posBtPrinterList',
            storageKey: 'posSelectedPrinter'
        })).then(function() {
            return afterBillPrint();
        }).catch(function() {
            return afterBillPrint();
        }).then(function() {
            $('#printBillBtn').prop('disabled', false);
        });
    }

    // Print KOT only (used by dine-in Place/Update — kitchen ticket, not bill)
    window.printKotForOrder = function(orderId) {
        return printKotToKitchen(orderId);
    };

    window.showBillGeneratedModal = function(orderId, opts) {
        opts = opts || {};
        window._billOrderId = orderId;
        window._billReloadOnClose = !!opts.reloadOnClose;

        // opts.printKot === false → dine-in checkout (KOT already printed on Update)
        if (opts.printKot === false) {
            $('#posPrintKotWithBill').prop('checked', false);
        } else if (opts.printKot === true) {
            $('#posPrintKotWithBill').prop('checked', true);
        } else {
            try {
                var kotPref = localStorage.getItem('posPrintKotWithBill');
                $('#posPrintKotWithBill').prop('checked', kotPref !== '0');
            } catch (e) {}
        }

        // Petpooja-style: mark bill printed → floor map table turns green (still occupied until settle)
        if (opts.markBillPrinted !== false && orderId) {
            try {
                var authToken = localStorage.getItem('authToken') || '';
                $.ajax({
                    url: '/api/orders/' + orderId + '/mark-bill-printed',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (!res || !res.status) return;
                        var tid = opts.tableId || res.table_id || window.selectedTableId;
                        if (!tid || typeof window.updateTableCardStatus !== 'function') return;
                        var cls = res.table_status_class || 'printed-table';
                        var st = res.table_status || 'printed';
                        var keep = (cls === 'running-table' || cls === 'printed-table');
                        window.updateTableCardStatus(
                            tid,
                            cls,
                            st,
                            keep ? (res.order_id || orderId) : '',
                            keep ? res.total_amount : '',
                            res
                        );
                    }
                });
            } catch (e) {}
        }

        var billUrl = "/sales/invoice/pdf/" + orderId;
        var $iframe = $("#billPreviewIframe");
        var $loader = $("#billIframeLoader");
        $loader.show();
        $iframe.attr("src", "");
        setTimeout(function() {
            $iframe.attr("src", billUrl);
        }, 50);
        $iframe.off("load.billGen").on("load.billGen", function() {
            $loader.hide();
            if (window.InvoiceQzPrint && InvoiceQzPrint.preparePhonePrint && this.src) {
                InvoiceQzPrint.preparePhonePrint(this.src);
            }
        });

        var billModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("billGeneratedModal"), {
            backdrop: "static",
            keyboard: false
        });
        billModal.show();
        updateBillPrintUi();
    };

    $(document).on('click', '#posRefreshPrintersBtn', function() {
        refreshPosPrinterList();
    });

    $(document).on('click', '#posPrinterSettingsToggle', function() {
        var $panel = $('#posPrinterSettingsPanel');
        var open = !$panel.is(':visible');
        $panel.slideToggle(160);
        $(this).html(open
            ? '<i class="fas fa-times"></i> Hide settings'
            : '<i class="fas fa-cog"></i> Printer settings');
        if (open) refreshPosPrinterList();
    });

    $(document).on('change', '#posPrintKotWithBill', function() {
        try { localStorage.setItem('posPrintKotWithBill', $(this).is(':checked') ? '1' : '0'); } catch (e) {}
        updateBillPrintUi();
    });

    $(document).on('change', '#posKotPrinterSelect', function() {
        try { localStorage.setItem('posSelectedKotPrinter', $(this).val() || ''); } catch (e) {}
        updateBillPrintUi();
    });

    $(document).on('change', '#posPrinterSelect', function() {
        try { localStorage.setItem('posSelectedPrinter', $(this).val() || ''); } catch (e) {}
        syncKotPrinterOptionsFromBill();
    });

    $(document).on('click', '#printBillBtn', function() {
        printBillFromModal();
    });

    $(document).on('click', '#printKotOnlyBtn', function() {
        if (!window._billOrderId) return;
        $('#printKotOnlyBtn').prop('disabled', true);
        printKotToKitchen(window._billOrderId).then(function() {
            $('#printKotOnlyBtn').prop('disabled', false);
        });
    });

    $(document).on('click', '#closeBillBtn, #closeBillModalBtn', function() {
        var modalEl = document.getElementById("billGeneratedModal");
        var bsModal = bootstrap.Modal.getInstance(modalEl);
        if (bsModal) bsModal.hide();
        $("#billPreviewIframe").attr("src", "");
        $('#posPrinterSettingsPanel').hide();
        $('#posPrinterSettingsToggle').html('<i class="fas fa-cog"></i> Printer settings');
        if (window._billReloadOnClose) {
            window._billReloadOnClose = false;
            location.reload();
        }
    });

    $(document).on('shown.bs.modal', '#billGeneratedModal', function() {
        $('#posPrinterSettingsPanel').hide();
        $('#posPrinterSettingsToggle').html('<i class="fas fa-cog"></i> Printer settings');
        updateBillPrintUi();
        var tries = 0;
        (function waitQz() {
            tries++;
            if (window.InvoiceQzPrint || tries > 40) {
                refreshPosPrinterList().then(function() {
                    updateBillPrintUi();
                    var billVal = ($('#posPrinterSelect').val() || '').trim();
                    var saved = '';
                    try { saved = localStorage.getItem('posSelectedPrinter') || ''; } catch (e) {}
                    if (!billVal && !saved) {
                        $('#posPrinterSettingsPanel').show();
                        $('#posPrinterSettingsToggle').html('<i class="fas fa-times"></i> Hide settings');
                    }
                });
                if (window.InvoiceQzPrint && InvoiceQzPrint.preparePhonePrint) {
                    var frame = document.getElementById('billPreviewIframe');
                    if (frame && frame.src) InvoiceQzPrint.preparePhonePrint(frame.src);
                }
                return;
            }
            setTimeout(waitQz, 150);
        })();
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'F10' || e.keyCode === 121) {
            var modalEl = document.getElementById("billGeneratedModal");
            if (modalEl && modalEl.classList.contains('show')) {
                e.preventDefault();
                printBillFromModal();
            }
        }
    });

    window.updateBillPrintUi = updateBillPrintUi;
})(jQuery);
</script>
