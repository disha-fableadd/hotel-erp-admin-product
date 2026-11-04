{{-- Shared Bill Generated modal (POS + Dine In) — Petpooja-simple print UI --}}
<style>
    .bill-gen-dialog { max-width: 820px; margin-top: 0 !important; }
    #billGeneratedModal { z-index: 1080; }
    #billGeneratedModal .modal-content.bill-gen-content,
    .bill-gen-content {
        border-radius: 16px;
        overflow: hidden;
        margin: 0 !important;
        margin-top: 0 !important;
    }
    .bill-gen-header { background: #fff; padding: 16px 20px 8px; }
    .bill-gen-preview {
        height: min(480px, 52vh);
        background: #e8eaed;
        position: relative;
    }
    .bill-gen-loader {
        position: absolute; inset: 0;
        background: rgba(255,255,255,.85);
        display: flex; align-items: center; justify-content: center; z-index: 10;
    }
    .bill-gen-footer {
        background: #fff;
        padding: 12px 16px 16px !important;
        display: flex !important;
        flex-direction: column;
        gap: 10px;
        align-items: stretch !important;
    }
    .bill-gen-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }
    .bill-gen-summary-text {
        font-size: 12.5px;
        color: #64748b;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px 10px;
        min-width: 0;
    }
    #posPrintSummaryLabel { color: #334155; font-weight: 600; }
    .bill-gen-status:empty { display: none; }
    .bill-gen-settings-toggle {
        color: #64748b !important;
        text-decoration: none !important;
        font-weight: 600;
        padding: 0 !important;
        white-space: nowrap;
    }
    .bill-gen-settings-toggle:hover { color: #ff9f43 !important; }
    .bill-gen-settings {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .bill-gen-settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .bill-gen-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 4px;
    }
    .bill-gen-field-row {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .bill-gen-field-row .form-select { flex: 1; min-width: 0; }
    .bill-gen-settings-opts {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
    }
    .bill-gen-check {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin: 0;
        cursor: pointer;
    }
    .bill-gen-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }
    .bill-gen-btn-secondary {
        font-weight: 600;
        border-radius: 10px;
        padding: 9px 16px;
    }
    .bill-gen-btn-primary {
        background: #ff9f43 !important;
        color: #fff !important;
        font-weight: 700;
        border: none !important;
        border-radius: 10px;
        padding: 10px 22px;
        box-shadow: 0 4px 12px rgba(255, 159, 67, 0.28);
    }
    .bill-gen-btn-primary:hover {
        background: #f08c2e !important;
        color: #fff !important;
    }
    @media (max-width: 767px) {
        .bill-gen-settings-grid { grid-template-columns: 1fr; }
        .bill-gen-preview { height: min(380px, 48vh); }
        .bill-gen-actions { width: 100%; }
        .bill-gen-btn-primary { flex: 1 1 100%; }
        .bill-gen-btn-secondary { flex: 1 1 auto; }
    }
</style>

<div class="modal fade" id="billGeneratedModal" tabindex="-1" aria-labelledby="billGeneratedModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered bill-gen-dialog">
        <div class="modal-content bill-gen-content">
            <div class="modal-header border-0 bill-gen-header">
                <div>
                    <h5 class="modal-title fw-bold fs-5 mb-0" id="billGeneratedModalLabel">Bill Generated</h5>
                    <small class="text-muted">Press <kbd>F10</kbd> or Print — bill &amp; KOT together</small>
                </div>
                <button type="button" class="btn-close" id="closeBillModalBtn" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="background:#f8f9fa;">
                <div class="phone-print-panel d-md-none px-3 pt-2" data-select="#posPrinterSelect" data-status="#posQzStatus" data-storage="posSelectedPrinter" data-bt="#posBtPrinterList">
                    <div class="phone-print-tabs mb-2">
                        <button type="button" class="phone-print-tab is-active btn btn-sm btn-outline-primary me-1" data-tab="bt">Bluetooth</button>
                        <button type="button" class="phone-print-tab btn btn-sm btn-outline-secondary" data-tab="wifi">WIFI</button>
                    </div>
                    <div id="posBtPrinterList" class="bt-printer-list" data-print-pane="bt">
                        <div class="bt-printer-empty small text-muted">Tap Bluetooth and choose the receipt printer.</div>
                    </div>
                    <div id="posWifiPrinterList" class="bt-printer-list d-none" data-print-pane="wifi">
                        <div class="bt-printer-empty small text-muted">Looking for Wi‑Fi printers…</div>
                    </div>
                </div>
                <div class="bill-gen-preview">
                    <iframe id="billPreviewIframe" src="" style="width:100%; height:100%; border:none; display:block;" title="Bill Preview"></iframe>
                    <div id="billIframeLoader" class="bill-gen-loader">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bill-gen-footer">
                <div class="bill-gen-summary">
                    <div class="bill-gen-summary-text">
                        <span id="posPrintSummaryLabel">Ready to print</span>
                        <span class="bill-gen-status" id="posQzStatus"></span>
                        <span class="bill-gen-status" id="posKotQzStatus"></span>
                    </div>
                    <button type="button" class="btn btn-link btn-sm bill-gen-settings-toggle" id="posPrinterSettingsToggle">
                        <i class="fas fa-cog"></i> Printer settings
                    </button>
                </div>

                <div id="posPrinterSettingsPanel" class="bill-gen-settings" style="display:none;">
                    <div class="bill-gen-settings-grid">
                        <div class="bill-gen-field">
                            <label for="posPrinterSelect">Bill printer</label>
                            <div class="bill-gen-field-row">
                                <select id="posPrinterSelect" class="form-select form-select-sm">
                                    <option value="">Connecting…</option>
                                </select>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="posRefreshPrintersBtn" title="Refresh">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm d-none d-md-inline-block desktop-bt-print-btn"
                                    data-select="#posPrinterSelect" data-status="#posQzStatus" data-storage="posSelectedPrinter"
                                    title="Bluetooth bill printer">
                                    <i class="fab fa-bluetooth-b"></i>
                                </button>
                            </div>
                        </div>
                        <div class="bill-gen-field" id="posKotPrinterRow">
                            <label for="posKotPrinterSelect">Kitchen / KOT printer</label>
                            <div class="bill-gen-field-row">
                                <select id="posKotPrinterSelect" class="form-select form-select-sm">
                                    <option value="">Same as bill</option>
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-sm d-none d-md-inline-block desktop-bt-print-btn"
                                    data-select="#posKotPrinterSelect" data-status="#posKotQzStatus" data-storage="posSelectedKotPrinter"
                                    title="Bluetooth kitchen printer">
                                    <i class="fab fa-bluetooth-b"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="bill-gen-settings-opts">
                        <label class="bill-gen-check">
                            <input type="checkbox" id="posPrintKotWithBill" checked>
                            <span>Also print KOT</span>
                        </label>
                        <label class="bill-gen-check">
                            <input type="checkbox" id="posThermalAutoCut">
                            <span>Auto cut</span>
                        </label>
                        <a href="https://qz.io/download/" target="_blank" rel="noopener" class="small" id="posQzInstallLink" style="display:none;">Install QZ Tray</a>
                        <a href="{{ url('/qz/install-qz-trust.bat') }}" class="small d-none d-md-inline" id="posQzTrustLink">Fix Allow</a>
                    </div>
                </div>

                <div class="bill-gen-actions">
                    <button type="button" id="closeBillBtn" class="btn btn-light bill-gen-btn-secondary">Close</button>
                    <button type="button" id="detailsBillBtn" class="btn btn-outline-secondary bill-gen-btn-secondary"
                        onclick="if(window._billOrderId){ window.location.href='/sales-invoice/'+window._billOrderId; }">
                        Details
                    </button>
                    <button type="button" id="printKotOnlyBtn" class="btn btn-outline-dark bill-gen-btn-secondary" title="Kitchen ticket only">
                        Print KOT
                    </button>
                    <button type="button" id="printBillBtn" class="btn bill-gen-btn-primary">
                        <i class="fas fa-print me-1"></i>
                        <span id="printBillBtnLabel">Print Bill + KOT</span>
                        <small class="ms-1 opacity-75">(F10)</small>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
