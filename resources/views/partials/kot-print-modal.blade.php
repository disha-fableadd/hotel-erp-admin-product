{{-- KOT Print modal (Dine In Place/Update) — same simple print UX as Bill Generated --}}
<style>
    .kot-gen-dialog { max-width: 640px; margin-top: 0 !important; }
    #kotPrintModal { z-index: 1085; }
    #kotPrintModal .modal-content.kot-gen-content {
        border-radius: 16px;
        overflow: hidden;
        margin: 0 !important;
        margin-top: 0 !important;
    }
    .kot-gen-header { background: #fff; padding: 16px 20px 8px; }
    .kot-gen-preview {
        height: min(420px, 48vh);
        background: #e8eaed;
        position: relative;
    }
    .kot-gen-loader {
        position: absolute; inset: 0;
        background: rgba(255,255,255,.85);
        display: flex; align-items: center; justify-content: center; z-index: 10;
    }
    .kot-gen-footer {
        background: #fff;
        padding: 12px 16px 16px !important;
        display: flex !important;
        flex-direction: column;
        gap: 10px;
        align-items: stretch !important;
    }
    .kot-gen-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }
    .kot-gen-summary-text {
        font-size: 12.5px;
        color: #64748b;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px 10px;
    }
    #kotPrintSummaryLabel { color: #334155; font-weight: 600; }
    .kot-gen-status:empty { display: none; }
    .kot-gen-settings {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        display: none;
    }
    .kot-gen-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 4px;
    }
    .kot-gen-field-row {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .kot-gen-field-row .form-select { flex: 1; min-width: 0; }
    .kot-gen-settings-opts {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
    }
    .kot-gen-check {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin: 0;
        cursor: pointer;
    }
    .kot-gen-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }
    .kot-gen-btn-secondary {
        font-weight: 600;
        border-radius: 10px;
        padding: 9px 16px;
    }
    .kot-gen-btn-primary {
        background: #1b2850 !important;
        color: #fff !important;
        font-weight: 700;
        border: none !important;
        border-radius: 10px;
        padding: 10px 22px;
        box-shadow: 0 4px 12px rgba(27, 40, 80, 0.28);
    }
    .kot-gen-btn-primary:hover {
        background: #152040 !important;
        color: #fff !important;
    }
    .kot-gen-settings-toggle {
        color: #64748b !important;
        text-decoration: none !important;
        font-weight: 600;
        padding: 0 !important;
    }
    .kot-gen-settings-toggle:hover { color: #ff9f43 !important; }
</style>

<div class="modal fade" id="kotPrintModal" tabindex="-1" aria-labelledby="kotPrintModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered kot-gen-dialog">
        <div class="modal-content kot-gen-content">
            <div class="modal-header border-0 kot-gen-header">
                <div>
                    <h5 class="modal-title fw-bold fs-5 mb-0" id="kotPrintModalLabel">
                        <i class="fas fa-utensils me-1" style="color:#ff9f43;"></i> Kitchen KOT
                    </h5>
                    <small class="text-muted" id="kotPrintModalHint">Only new / changed items — Press <kbd>F10</kbd> or Print</small>
                </div>
                <button type="button" class="btn-close" id="closeKotModalBtn" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="background:#f8f9fa;">
                <div class="kot-gen-preview">
                    <iframe id="kotPreviewIframe" src="" style="width:100%; height:100%; border:none; display:block;" title="KOT Preview"></iframe>
                    <div id="kotIframeLoader" class="kot-gen-loader">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 kot-gen-footer">
                <div class="kot-gen-summary">
                    <div class="kot-gen-summary-text">
                        <span id="kotPrintSummaryLabel">Ready to print KOT</span>
                        <span class="kot-gen-status" id="kotQzStatus"></span>
                    </div>
                    <button type="button" class="btn btn-link btn-sm kot-gen-settings-toggle" id="kotPrinterSettingsToggle">
                        <i class="fas fa-cog"></i> Printer settings
                    </button>
                </div>

                <div id="kotPrinterSettingsPanel" class="kot-gen-settings">
                    <div class="kot-gen-field">
                        <label for="kotPrinterSelect">Kitchen printer</label>
                        <div class="kot-gen-field-row">
                            <select id="kotPrinterSelect" class="form-select form-select-sm">
                                <option value="">Connecting…</option>
                            </select>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="kotRefreshPrintersBtn" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm d-none d-md-inline-block desktop-bt-print-btn"
                                data-select="#kotPrinterSelect" data-status="#kotQzStatus" data-storage="posSelectedKotPrinter"
                                title="Bluetooth kitchen printer">
                                <i class="fab fa-bluetooth-b"></i>
                            </button>
                        </div>
                    </div>
                    <div class="kot-gen-settings-opts">
                        <label class="kot-gen-check">
                            <input type="checkbox" id="kotThermalAutoCut" checked>
                            <span>Auto cut</span>
                        </label>
                    </div>
                </div>

                <div class="kot-gen-actions">
                    <button type="button" id="closeKotBtn" class="btn btn-light kot-gen-btn-secondary">Close</button>
                    <button type="button" id="printKotModalBtn" class="btn kot-gen-btn-primary">
                        <i class="fas fa-print me-1"></i> Print KOT
                        <small class="ms-1 opacity-75">(F10)</small>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
