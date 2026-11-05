<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customize Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ci-primary: #ff9f43;
            --ci-primary-dark: #e8892d;
            --ci-ink: #1f2937;
            --ci-muted: #6b7280;
            --ci-line: #e5e7eb;
            --ci-bg: #f8f9fb;
            --ci-soft: #fff7ed;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--ci-bg);
            color: var(--ci-ink);
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            display: flex;
            flex-direction: column;
        }
        .ci-top {
            background: linear-gradient(135deg, #ff9f43 0%, #f97316 100%);
            color: #fff;
            text-align: center;
            padding: 14px 16px;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.02em;
            box-shadow: 0 2px 10px rgba(255, 159, 67, 0.35);
        }
        .ci-shell {
            flex: 1;
            display: grid;
            grid-template-columns: minmax(280px, 400px) 1fr;
            gap: 0;
            min-height: 0;
        }
        .ci-preview-pane {
            background: #f1f3f6;
            border-right: 1px solid var(--ci-line);
            padding: 18px 14px 100px;
            overflow: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .ci-preview-label {
            font-size: 11px;
            color: var(--ci-muted);
            margin-bottom: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .ci-tpl-badge {
            display: inline-block;
            background: var(--ci-soft);
            color: var(--ci-primary-dark);
            border: 1px solid #fed7aa;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            margin-bottom: 10px;
        }
        .ci-receipt {
            background: #fff;
            width: 100%;
            max-width: 320px;
            padding: 12px 10px 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            font-size: 11px;
            line-height: 1.35;
            color: #111;
            border-radius: 4px;
            border-top: 3px solid var(--ci-primary);
        }
        .ci-receipt.paper-2inch { max-width: 230px; font-size: 10px; }
        .ci-receipt .shop { text-align: center; font-weight: 800; font-size: 13px; text-transform: uppercase; }
        .ci-receipt .meta { text-align: center; color: #333; margin-bottom: 6px; font-size: 10px; }
        .ci-receipt .dash { border-top: 1px dashed #333; margin: 7px 0; }
        .ci-receipt .dbl { border-top: 2px solid #111; margin: 7px 0; }
        .ci-receipt .title { text-align: center; font-weight: 800; letter-spacing: 0.1em; }
        .ci-receipt table { width: 100%; border-collapse: collapse; }
        .ci-receipt th, .ci-receipt td { padding: 2px 1px; vertical-align: top; font-size: inherit; }
        .ci-receipt th { border-bottom: 1px solid #111; font-size: 9px; text-transform: uppercase; }
        .ci-receipt .tax-line { font-size: 8px; color: #444; font-weight: 500; }
        .ci-receipt .save-box {
            background: #f3f4f6;
            padding: 5px 6px;
            margin: 7px 0;
            font-weight: 700;
            text-align: center;
        }
        .ci-receipt .net-box {
            border: 2px solid #111;
            padding: 6px 8px;
            margin: 8px 0;
            font-weight: 800;
            text-align: center;
            font-size: 13px;
        }
        .ci-receipt .qr {
            width: 78px; height: 78px; margin: 8px auto 0;
            background:
                repeating-linear-gradient(90deg,#111 0 3px,#fff 3px 6px),
                repeating-linear-gradient(0deg,#111 0 3px,#fff 3px 6px);
            background-blend-mode: multiply;
            border: 1px solid #000;
        }
        .ci-receipt .row-flex { display: flex; justify-content: space-between; gap: 6px; }
        .ci-receipt .muted { color: #555; font-size: 9px; }
        .ci-paper-btns {
            display: flex;
            gap: 8px;
            margin-top: 14px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .ci-paper-btns button {
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            color: var(--ci-ink);
            cursor: pointer;
        }
        .ci-paper-btns button.active {
            background: var(--ci-primary);
            border-color: var(--ci-primary);
            color: #fff;
        }
        .ci-settings {
            background: #fff;
            overflow: auto;
            padding: 0 0 100px;
        }
        .ci-tabs {
            display: flex;
            gap: 0;
            border-bottom: 1px solid var(--ci-line);
            padding: 0 6px;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 5;
        }
        .ci-tabs button {
            flex: 1;
            border: 0;
            background: transparent;
            padding: 14px 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--ci-muted);
            border-bottom: 2px solid transparent;
            cursor: pointer;
        }
        .ci-tabs button:hover { color: var(--ci-primary); }
        .ci-tabs button.active {
            color: var(--ci-primary);
            border-bottom-color: var(--ci-primary);
        }
        .ci-panel { display: none; padding: 18px 22px; }
        .ci-panel.active { display: block; }
        .ci-section-title {
            font-size: 13px;
            font-weight: 700;
            margin: 4px 0 12px;
            color: var(--ci-ink);
            padding-left: 8px;
            border-left: 3px solid var(--ci-primary);
        }
        .ci-card {
            border: 1px solid var(--ci-line);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 16px;
            background: #fff;
        }
        .ci-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .ci-row:last-child { border-bottom: 0; }
        .ci-row .lbl { font-size: 13px; font-weight: 600; }
        .ci-row .hint, .ci-field .hint { font-size: 11px; color: var(--ci-muted); margin-top: 2px; }
        .form-switch .form-check-input { width: 2.4em; height: 1.25em; cursor: pointer; }
        .form-switch .form-check-input:checked {
            background-color: var(--ci-primary);
            border-color: var(--ci-primary);
        }
        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25);
        }
        .ci-field { margin-bottom: 14px; }
        .ci-field label { font-size: 12px; font-weight: 600; margin-bottom: 4px; display: block; }
        .form-control:focus, .form-select:focus {
            border-color: var(--ci-primary);
            box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.2);
        }
        .form-range::-webkit-slider-thumb { background: var(--ci-primary); }
        .ci-footer {
            position: fixed;
            left: 0; right: 0; bottom: 0;
            background: #fff;
            border-top: 1px solid var(--ci-line);
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            z-index: 20;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.04);
        }
        .ci-footer .btn-save {
            background: var(--ci-primary);
            color: #fff;
            border: 0;
            min-width: 140px;
            font-weight: 700;
            border-radius: 8px;
            padding: 10px 18px;
        }
        .ci-footer .btn-save:hover { background: var(--ci-primary-dark); color: #fff; }
        .ci-footer .btn-outline-secondary {
            border-radius: 8px;
            padding: 10px 18px;
            font-weight: 600;
        }
        @media (max-width: 900px) {
            .ci-shell { grid-template-columns: 1fr; }
            .ci-preview-pane { max-height: 420px; }
        }
    </style>
</head>
<body>
    <div class="ci-top">Customize Invoice</div>

    <div class="ci-shell">
        <aside class="ci-preview-pane">
            <div class="ci-preview-label">Live Preview</div>
            <div id="tplBadge" class="ci-tpl-badge">Template 1</div>
            <div id="receiptPreview" class="ci-receipt"></div>
            <div class="ci-paper-btns">
                <button type="button" data-paper="2inch">2 inch</button>
                <button type="button" data-paper="3inch" class="active">3 inch</button>
            </div>
        </aside>

        <section class="ci-settings">
            <div class="ci-tabs">
                <button type="button" class="active" data-tab="basic"><i class="fas fa-store"></i> Basic</button>
                <button type="button" data-tab="template"><i class="fas fa-file-alt"></i> Template</button>
                <button type="button" data-tab="items"><i class="fas fa-list"></i> Items</button>
                <button type="button" data-tab="footer"><i class="fas fa-qrcode"></i> Footer</button>
                <button type="button" data-tab="advanced"><i class="fas fa-sliders-h"></i> Advanced</button>
            </div>

            <form id="invoiceCustomForm">
                <div class="ci-panel active" data-panel="basic">
                    <div class="ci-card">
                        <div class="ci-section-title">Header Settings</div>
                        <div class="ci-row"><div><div class="lbl">Shop Logo</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_logo"></div></div>
                        <div class="ci-field"><label>Logo Size</label><input type="range" class="form-range inv-num" name="logo_size" min="0.5" max="2" step="0.1"><div class="hint"><span id="logoSizeVal">1.00</span>x</div></div>
                        <div class="ci-row"><div><div class="lbl">Shop GST Number</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_shop_gst"></div></div>
                        <div class="ci-row"><div><div class="lbl">FSSAI Number</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_fssai"></div></div>
                        <div class="ci-row"><div><div class="lbl">Customer GST</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_customer_gst"></div></div>
                        <div class="ci-row"><div><div class="lbl">Show Salesman</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_salesman"></div></div>
                        <div class="ci-row"><div><div class="lbl">Print Customer Name</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_customer_name"></div></div>
                        <div class="ci-row"><div><div class="lbl">Print Customer Phone</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_customer_phone"></div></div>
                        <div class="ci-row"><div><div class="lbl">Print Customer Address</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_customer_address"></div></div>
                        <div class="ci-row"><div><div class="lbl">Show Customer Message</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_customer_message"></div></div>
                    </div>
                    <div class="ci-card">
                        <div class="ci-section-title">Custom Heading</div>
                        <div class="ci-row"><div><div class="lbl">Enable Custom Heading</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="enable_custom_heading"></div></div>
                        <div class="ci-field"><label>Custom Heading Text</label><input type="text" class="form-control form-control-sm inv-text" name="custom_heading_text" placeholder="e.g. TAX INVOICE"></div>
                    </div>
                </div>

                <div class="ci-panel" data-panel="template">
                    <div class="ci-card">
                        <div class="ci-section-title">Template Selection</div>
                        <div class="ci-field">
                            <label>Template</label>
                            <select class="form-select form-select-sm inv-num" name="preferred_template">
                                <option value="1">Template 1 — Cash sale bill</option>
                                <option value="2">Template 2 — Tax detail (long)</option>
                                <option value="3">Template 3 — MRP + SP + tax</option>
                                <option value="4">Template 4 — Net total box</option>
                                <option value="5">Template 5 — Payment split</option>
                            </select>
                            <div class="hint">Each template has a different print layout. Preview updates instantly.</div>
                        </div>
                        <div class="ci-field">
                            <label>Language</label>
                            <select class="form-select form-select-sm inv-text" name="language">
                                <option value="english">English</option>
                                <option value="hindi">Hindi</option>
                            </select>
                        </div>
                        <div class="ci-field">
                            <label>Font Size (px)</label>
                            <select class="form-select form-select-sm inv-num" name="font_size">
                                @foreach([6,7,8,9,10,11,12] as $fs)
                                    <option value="{{ $fs }}">{{ $fs }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="ci-card">
                        <div class="ci-section-title">Margins &amp; Spacing</div>
                        <div class="ci-field">
                            <label>Left Margin (mm)</label>
                            <input type="number" class="form-control form-control-sm inv-num" name="left_margin_mm" min="0" max="10" step="0.5">
                            <div class="hint">Extra space to prevent cutting on thermal printers.</div>
                        </div>
                    </div>
                    <div class="ci-card">
                        <div class="ci-section-title">Print Output</div>
                        <div class="ci-field">
                            <label>Number of Copies</label>
                            <select class="form-select form-select-sm inv-num" name="print_copies">
                                <option value="1">1 (Default)</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <input type="hidden" class="inv-text" name="paper_size" value="3inch">
                    </div>
                </div>

                <div class="ci-panel" data-panel="items">
                    <div class="ci-card">
                        <div class="ci-section-title">Item Display</div>
                        <div class="ci-row"><div><div class="lbl">Serial Number</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_serial"></div></div>
                        <div class="ci-row"><div><div class="lbl">Item Barcodes</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_item_barcode"></div></div>
                        <div class="ci-row"><div><div class="lbl">Item Quantity</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_item_qty"></div></div>
                        <div class="ci-row"><div><div class="lbl">Item Description</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_item_description"></div></div>
                        <div class="ci-row"><div><div class="lbl">Item GST %</div><div class="hint">Show tax name + % after each item (e.g. GST 28%)</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_item_gst"></div></div>
                        <div class="ci-row"><div><div class="lbl">HSN Code</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_hsn"></div></div>
                    </div>
                    <div class="ci-card">
                        <div class="ci-section-title">Pricing Display</div>
                        <div class="ci-row"><div><div class="lbl">Item MRP</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_item_mrp"></div></div>
                        <div class="ci-row"><div><div class="lbl">Item Retail Price</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_item_sp"></div></div>
                        <div class="ci-field"><label>Retail Price Column Label</label><input type="text" class="form-control form-control-sm inv-text" name="sp_column_label" placeholder="SP"></div>
                        <div class="ci-field">
                            <label>SP Column Price</label>
                            <select class="form-select form-select-sm inv-text" name="sp_price_source">
                                <option value="selling_price">Selling Price</option>
                                <option value="mrp">MRP</option>
                            </select>
                        </div>
                        <div class="ci-row"><div><div class="lbl">Item Total Price</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_item_total"></div></div>
                    </div>
                </div>

                <div class="ci-panel" data-panel="footer">
                    <div class="ci-card">
                        <div class="ci-section-title">Payment &amp; QR Code</div>
                        <div class="ci-row"><div><div class="lbl">Show Payment Mode</div><div class="hint">Display payment mode on invoice</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_payment_mode"></div></div>
                        <div class="ci-row"><div><div class="lbl">UPI QR Code</div><div class="hint">Display UPI payment QR</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_upi_qr"></div></div>
                        <div class="ci-row"><div><div class="lbl">Invoice Barcode</div><div class="hint">Scan to open order</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_invoice_barcode"></div></div>
                    </div>
                    <div class="ci-card">
                        <div class="ci-section-title">Customer Savings</div>
                        <div class="ci-row"><div><div class="lbl">Show Savings</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_savings"></div></div>
                        <div class="ci-field"><label>Savings Label</label><input type="text" class="form-control form-control-sm inv-text" name="savings_label"></div>
                    </div>
                    <div class="ci-card">
                        <div class="ci-section-title">Thank You Message</div>
                        <div class="ci-row"><div><div class="lbl">Show Thank You Line</div><div class="hint">Footer message at bottom of bill</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_thanks"></div></div>
                        <div class="ci-field">
                            <label>Custom Message</label>
                            <input type="text" class="form-control form-control-sm inv-text" name="thanks_message" maxlength="120" placeholder="Thanks! Visit again">
                            <div class="hint mt-1">Quick pick:</div>
                            <div class="d-flex flex-wrap gap-1 mt-1" id="thanksPresets">
                                <button type="button" class="btn btn-sm btn-outline-secondary thanks-preset" data-msg="Thanks! Visit again">Thanks! Visit again</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary thanks-preset" data-msg="Thanks for business with us!">Thanks for business</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary thanks-preset" data-msg="Thank you! Please visit again">Please visit again</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary thanks-preset" data-msg="Visit again soon!">Visit again soon</button>
                            </div>
                        </div>
                    </div>
                    <div class="ci-card">
                        <div class="ci-section-title">Terms &amp; Conditions</div>
                        <div class="ci-row"><div><div class="lbl">Enable Terms</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="enable_terms"></div></div>
                    </div>
                </div>

                <div class="ci-panel" data-panel="advanced">
                    <div class="ci-card">
                        <div class="ci-section-title">Advanced Display</div>
                        <div class="ci-row"><div><div class="lbl">Product Name Wrap</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="product_name_wrap"></div></div>
                        <div class="ci-row"><div><div class="lbl">Product Second Line</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="product_second_line"></div></div>
                        <div class="ci-row"><div><div class="lbl">Savings in Line Item</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="savings_in_line_item"></div></div>
                        <div class="ci-row"><div><div class="lbl">Total Item Count</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_total_item_count"></div></div>
                    </div>
                    <div class="ci-card">
                        <div class="ci-section-title">Performance</div>
                        <div class="ci-row"><div><div class="lbl">Merge Product &amp; Items</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="merge_product_items"></div></div>
                        <div class="ci-row"><div><div class="lbl">Truncate Product Names</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="truncate_product_names"></div></div>
                        <div class="ci-row"><div><div class="lbl">Instant Billing Mode</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="instant_billing_mode"></div></div>
                    </div>
                    <div class="ci-card">
                        <div class="ci-section-title">Tax &amp; Precision</div>
                        <div class="ci-row"><div><div class="lbl">Enable GST Billing</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="enable_gst_billing"></div></div>
                        <div class="ci-field">
                            <label>Decimal Places</label>
                            <select class="form-select form-select-sm inv-num" name="decimal_places">
                                @foreach([0,1,2,3,4] as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ci-row"><div><div class="lbl">Round Off Quantity</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="round_off_quantity"></div></div>
                        <div class="ci-row"><div><div class="lbl">Customer Outstanding Amount</div></div><div class="form-check form-switch m-0"><input class="form-check-input inv-bool" type="checkbox" name="show_customer_outstanding"></div></div>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <div class="ci-footer">
        <a href="{{ route('setting.generalsettings') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="button" id="btnSaveInvoiceCustom" class="btn btn-save">Save</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            var initial = @json($inv);
            var shopName = @json($setting->name ?? 'Hotel ERP');
            var shopPhone = @json($setting->phone ?? '');
            var shopGst = @json($setting->gst_num ?? '');
            var shopFssai = @json($setting->cin_no ?? ($setting->fssai_no ?? ''));
            var shopAddr = @json($setting->address ?? '');
            var state = Object.assign({}, initial);
            var products = [
                { n: 'Product A', q: 1, m: 120, s: 100, cgst: 9, sgst: 9, gst: 18 },
                { n: 'Product B', q: 2, m: 80, s: 70, cgst: 6.3, sgst: 6.3, gst: 18 },
                { n: 'Product C', q: 1, m: 200, s: 180, cgst: 16.2, sgst: 16.2, gst: 28 },
                { n: 'Product D', q: 3, m: 50, s: 45, cgst: 4.05, sgst: 4.05, gst: 5 },
                { n: 'Product E', q: 1, m: 150, s: 140, cgst: 12.6, sgst: 12.6, gst: 12 },
                { n: 'Product F', q: 2, m: 90, s: 85, cgst: 7.65, sgst: 7.65, gst: 18 }
            ];
            var tplNames = {
                1: 'Template 1 — Cash sale bill',
                2: 'Template 2 — Tax detail (long)',
                3: 'Template 3 — MRP + SP + tax',
                4: 'Template 4 — Net total box',
                5: 'Template 5 — Payment split'
            };

            function applyForm() {
                Object.keys(state).forEach(function(key) {
                    var $el = $('[name="' + key + '"]');
                    if (!$el.length) return;
                    if ($el.is(':checkbox')) {
                        $el.prop('checked', !!state[key]);
                    } else {
                        $el.val(state[key]);
                    }
                });
                $('#logoSizeVal').text(Number(state.logo_size || 1).toFixed(2));
                $('.ci-paper-btns button').removeClass('active');
                $('.ci-paper-btns button[data-paper="' + (state.paper_size || '3inch') + '"]').addClass('active');
            }

            function readForm() {
                $('#invoiceCustomForm').find('input, select').each(function() {
                    var $el = $(this);
                    var name = $el.attr('name');
                    if (!name) return;
                    if ($el.is(':checkbox')) {
                        state[name] = $el.is(':checked');
                    } else if ($el.hasClass('inv-num')) {
                        state[name] = parseFloat($el.val()) || 0;
                        if (name === 'preferred_template' || name === 'font_size' || name === 'print_copies' || name === 'decimal_places') {
                            state[name] = parseInt($el.val(), 10) || 0;
                        }
                    } else {
                        state[name] = $el.val();
                    }
                });
                state.paper_size = $('input[name="paper_size"]').val() || '3inch';
            }

            function esc(s) {
                return String(s == null ? '' : s)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }
            function money(n) {
                var dp = Math.max(0, Math.min(4, parseInt(state.decimal_places, 10) || 2));
                return Number(n).toFixed(dp);
            }
            function pname(n) {
                if (state.truncate_product_names && n.length > 12) return n.slice(0, 11) + '…';
                return n;
            }
            function wrapStyle() {
                return state.product_name_wrap ? 'white-space:normal;' : 'white-space:nowrap;overflow:hidden;';
            }
            function heading() {
                return (state.enable_custom_heading && state.custom_heading_text)
                    ? state.custom_heading_text
                    : 'INVOICE';
            }
            function shopBlock(opts) {
                opts = opts || {};
                var html = '';
                if (state.show_logo) {
                    html += '<div class="text-center mb-1" style="font-size:' + (16 * (state.logo_size || 1)) + 'px;">🏪</div>';
                }
                html += '<div class="shop">' + esc(shopName) + '</div><div class="meta">';
                if (opts.showAddr) html += esc(shopAddr) + '<br>';
                if (opts.showPhone) html += 'Ph: ' + esc(shopPhone) + '<br>';
                if (state.show_shop_gst && shopGst) html += 'GST: ' + esc(shopGst) + '<br>';
                if (state.show_fssai && shopFssai) html += 'FSSAI: ' + esc(shopFssai) + '<br>';
                html += '</div>';
                return html;
            }
            function customerBlock(layout) {
                if (!(state.show_customer_name || state.show_customer_phone || state.show_customer_address)) return '';
                var html = '';
                if (layout === 'center') {
                    html += '<div class="text-center" style="margin:6px 0;">';
                    if (state.show_customer_name) html += '<div><strong>John Doe</strong></div>';
                    if (state.show_customer_phone) html += '<div>9876543210</div>';
                    html += '</div>';
                    if (state.show_customer_address) html += '<div><strong>Bill To:</strong> Raipur, CG</div>';
                } else {
                    html += '<div style="margin-bottom:6px;">';
                    if (state.show_customer_name) html += '<div><strong>John Doe</strong></div>';
                    if (state.show_customer_phone) html += '<div>9876543210</div>';
                    if (state.show_customer_address) html += '<div>Raipur, CG</div>';
                    if (state.show_customer_gst) html += '<div>Cust GST: 22BBBBB0000B1Z5</div>';
                    if (state.show_salesman) html += '<div>Salesman: Staff</div>';
                    html += '</div>';
                }
                return html;
            }
            function thanksLine() {
                if (state.show_thanks === false) return '';
                var msg = String(state.thanks_message || '').trim() || 'Thanks! Visit again';
                return '<div class="muted" style="text-align:center;margin-top:6px;">' + esc(msg) + '</div>';
            }
            function footerExtras() {
                var html = '';
                if (state.show_savings) {
                    html += '<div class="save-box">' + esc(state.savings_label || 'You Saved') + ' Rs 50 (1.9%) on this purchase</div>';
                }
                if (state.show_payment_mode) html += '<div style="margin-top:5px;">Payment Mode: UPI + Cash</div>';
                if (state.show_customer_outstanding) html += '<div>You have an outstanding amount of Rs 90</div>';
                if (state.show_upi_qr) html += '<div class="text-center mt-2">Scan QR to Pay Bill:</div><div class="qr"></div>';
                if (state.show_invoice_barcode) html += '<div class="text-center mt-2" style="letter-spacing:2px;">||||| |||| |||||</div>';
                if (state.enable_terms) html += '<div class="dash"></div><div class="muted">Terms &amp; Conditions apply.</div>';
                html += thanksLine();
                return html;
            }
            function itemExtras(p, i) {
                var html = '';
                if (state.show_item_barcode) html += '<div class="tax-line">890100' + (i + 1) + '</div>';
                if (state.show_hsn) html += '<div class="tax-line">HSN 6109</div>';
                if (state.show_item_description) html += '<div class="tax-line">Sample desc</div>';
                if (state.product_second_line) html += '<div class="tax-line">Detail line</div>';
                if (state.savings_in_line_item && p.m > p.s) {
                    html += '<div class="tax-line">Saved ' + money((p.m - p.s) * p.q) + '</div>';
                }
                return html;
            }
            function itemNameWithGst(name, sampleRate) {
                var n = esc(pname(name));
                if (state.show_item_gst === false) return n;
                var rate = sampleRate != null ? sampleRate : 28;
                return n + ' <span style="font-weight:600;font-size:7.5px;">(GST ' + rate + '%)</span>';
            }

            /* ===== 5 DISTINCT TEMPLATE PREVIEWS ===== */
            function renderTpl1() {
                var html = shopBlock({ showPhone: true });
                html += customerBlock('left');
                html += '<div class="dash"></div><div class="title">' + esc(heading()) + '</div><div class="dash"></div>';
                html += '<div class="row-flex muted"><span>22-09-2026 01:09 PM</span><span>INV 1001</span></div>';
                html += '<table><thead><tr>';
                if (state.show_serial) html += '<th>#</th>';
                html += '<th class="text-start">ITEM</th>';
                if (state.show_item_qty) html += '<th>QTY</th>';
                if (state.show_item_mrp) html += '<th>MRP</th>';
                if (state.show_item_sp) html += '<th>' + esc(state.sp_column_label || 'SP') + '</th>';
                if (state.show_item_total) html += '<th>TOTAL</th>';
                html += '</tr></thead><tbody>';
                products.slice(0, 4).forEach(function(p, i) {
                    html += '<tr>';
                    if (state.show_serial) html += '<td>' + (i + 1) + '</td>';
                    html += '<td class="text-start" style="' + wrapStyle() + '">' + itemNameWithGst(p.n, p.gst) + itemExtras(p, i) + '</td>';
                    if (state.show_item_qty) html += '<td>' + p.q + '</td>';
                    if (state.show_item_mrp) html += '<td>' + money(p.m) + '</td>';
                    if (state.show_item_sp) html += '<td>' + money(state.sp_price_source === 'mrp' ? p.m : p.s) + '</td>';
                    if (state.show_item_total) html += '<td>' + money(p.s * p.q) + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table><div class="dash"></div>';
                html += '<div class="row-flex"><span>Sub Total</span><span>Rs 620.00</span></div>';
                if (state.show_total_item_count) html += '<div class="row-flex"><span>Items</span><span>7</span></div>';
                html += '<div class="row-flex"><strong>Total</strong><strong>Rs 620.00</strong></div>';
                html += footerExtras();
                return html;
            }

            function renderTpl2() {
                var html = shopBlock({ showAddr: true, showPhone: true });
                html += customerBlock('left');
                html += '<div class="dash"></div>';
                html += '<div class="row-flex"><span>Invoice Number</span><span><strong>1686</strong></span></div>';
                html += '<div class="row-flex"><span>Invoice Date</span><span>22-09-2026</span></div>';
                html += '<div class="row-flex"><span>Payment Status</span><span>UPI + Cash</span></div>';
                html += '<div class="dbl"></div>';
                html += '<table><thead><tr><th class="text-start">Item</th>';
                if (state.show_item_mrp) html += '<th>MRP</th>';
                if (state.show_item_qty) html += '<th>Qty</th>';
                if (state.show_item_total) html += '<th>Total</th>';
                html += '</tr></thead><tbody>';
                var totCgst = 0, totSgst = 0, totMrp = 0, totAmt = 0, totQty = 0;
                products.forEach(function(p, i) {
                    totCgst += p.cgst; totSgst += p.sgst;
                    totMrp += p.m * p.q; totAmt += p.s * p.q; totQty += p.q;
                    html += '<tr><td class="text-start" style="' + wrapStyle() + '"><strong>' + itemNameWithGst(p.n, p.gst) + '</strong>';
                    html += '<div class="tax-line">CGST: ' + money(p.cgst) + ' | SGST: ' + money(p.sgst) + ' | GST: ' + money(p.cgst + p.sgst) + '</div>';
                    html += itemExtras(p, i) + '</td>';
                    if (state.show_item_mrp) html += '<td>' + money(p.m) + '</td>';
                    if (state.show_item_qty) html += '<td>' + p.q + '</td>';
                    if (state.show_item_total) html += '<td>' + money(p.s * p.q) + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table><div class="dash"></div>';
                html += '<div class="row-flex"><span>Total CGST</span><span>' + money(totCgst) + '</span></div>';
                html += '<div class="row-flex"><span>Total SGST</span><span>' + money(totSgst) + '</span></div>';
                html += '<div class="row-flex"><span>Total Tax</span><span>' + money(totCgst + totSgst) + '</span></div>';
                html += '<div class="row-flex"><span>Total Qty</span><span>' + totQty + '</span></div>';
                html += '<div class="row-flex"><span>Total MRP</span><span>Rs ' + money(totMrp) + '</span></div>';
                html += '<div class="row-flex"><span>Delivery Charge</span><span>Rs 20.00</span></div>';
                html += '<div class="dbl"></div>';
                html += '<div class="row-flex"><strong>Total Bill Amount</strong><strong>Rs ' + money(totAmt + 20) + '</strong></div>';
                html += '<div class="row-flex"><span>Amount via Cash</span><span>Rs 500.00</span></div>';
                html += '<div class="row-flex"><span>Amount via UPI</span><span>Rs 500.00</span></div>';
                html += '<div class="row-flex"><span>Loyalty Points</span><span>0</span></div>';
                html += footerExtras();
                return html;
            }

            function renderTpl3() {
                var html = shopBlock({ showPhone: true });
                html += customerBlock('left');
                html += '<div class="dash"></div>';
                html += '<div class="row-flex"><span>Inv #1001</span><span>UPI - Cash</span></div>';
                html += '<div class="muted">22-09-2026 01:09 PM</div>';
                html += '<div class="dash"></div>';
                html += '<table><thead><tr>';
                if (state.show_serial) html += '<th>#</th>';
                html += '<th class="text-start">Item</th>';
                if (state.show_item_qty) html += '<th>Qty</th>';
                if (state.show_item_mrp) html += '<th>MRP</th>';
                if (state.show_item_sp) html += '<th>' + esc(state.sp_column_label || 'Sp') + '</th>';
                if (state.show_item_total) html += '<th>Total</th>';
                html += '</tr></thead><tbody>';
                products.forEach(function(p, i) {
                    html += '<tr>';
                    if (state.show_serial) html += '<td>' + (i + 1) + '</td>';
                    html += '<td class="text-start" style="' + wrapStyle() + '"><strong>' + itemNameWithGst(p.n, p.gst) + '</strong>';
                    html += '<div class="tax-line">CGST 9% | SGST 9% | GST ' + money(p.cgst + p.sgst) + '</div>';
                    html += itemExtras(p, i) + '</td>';
                    if (state.show_item_qty) html += '<td>' + p.q + '</td>';
                    if (state.show_item_mrp) html += '<td>' + money(p.m) + '</td>';
                    if (state.show_item_sp) html += '<td>' + money(p.s) + '</td>';
                    if (state.show_item_total) html += '<td>' + money(p.s * p.q) + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table><div class="dash"></div>';
                if (state.show_savings) {
                    html += '<div class="save-box">' + esc(state.savings_label || 'You Saved') + ' 1.9% : Rs 50</div>';
                }
                html += '<div class="row-flex"><span>Total CGST</span><span>55.80</span></div>';
                html += '<div class="row-flex"><span>Total SGST</span><span>55.80</span></div>';
                html += '<div class="row-flex"><span>Total Qty / MRP</span><span>10 / 690</span></div>';
                html += '<div class="row-flex"><strong>Total Bill</strong><strong>Rs 620.00</strong></div>';
                html += '<div class="row-flex"><span>Cash</span><span>Rs 300</span></div>';
                html += '<div class="row-flex"><span>UPI</span><span>Rs 320</span></div>';
                if (state.show_customer_outstanding) html += '<div class="text-center bold" style="margin-top:6px;">Outstanding: Rs 90</div>';
                if (state.show_upi_qr) html += '<div class="text-center mt-2">Scan QR to Pay Bill:</div><div class="qr"></div>';
                if (state.show_invoice_barcode) html += '<div class="text-center mt-2" style="letter-spacing:2px;">||||| |||| |||||</div>';
                if (state.enable_terms) html += '<div class="dash"></div><div class="muted">Terms &amp; Conditions apply.</div>';
                html += thanksLine();
                return html;
            }

            function renderTpl4() {
                var html = shopBlock({ showPhone: true });
                html += '<div class="dash"></div>';
                html += '<div class="row-flex muted"><span>22-09-2026</span><span>01:09 PM</span></div>';
                html += '<div class="row-flex"><span>Invoice No.</span><span><strong>1001</strong></span></div>';
                html += customerBlock('center');
                html += '<div class="dash"></div>';
                html += '<table><thead><tr><th class="text-start">Product</th>';
                if (state.show_item_mrp) html += '<th>MRP</th>';
                if (state.show_item_sp) html += '<th>' + esc(state.sp_column_label || 'Sp') + '</th>';
                if (state.show_item_qty) html += '<th>Qty</th>';
                if (state.show_item_total) html += '<th>Amt</th>';
                html += '</tr></thead><tbody>';
                var mrpTot = 0, billTot = 0, qtyTot = 0;
                products.slice(0, 5).forEach(function(p, i) {
                    mrpTot += p.m * p.q; billTot += p.s * p.q; qtyTot += p.q;
                    html += '<tr><td class="text-start" style="' + wrapStyle() + '">' + itemNameWithGst(p.n, p.gst) + itemExtras(p, i) + '</td>';
                    if (state.show_item_mrp) html += '<td>' + money(p.m) + '</td>';
                    if (state.show_item_sp) html += '<td>' + money(p.s) + '</td>';
                    if (state.show_item_qty) html += '<td>' + p.q + '</td>';
                    if (state.show_item_total) html += '<td>' + money(p.s * p.q) + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table><div class="dash"></div>';
                html += '<div class="row-flex"><span>Total Qty</span><span>' + qtyTot + '</span></div>';
                html += '<div class="row-flex"><span>MRP Total</span><span>Rs ' + money(mrpTot) + '</span></div>';
                html += '<div class="row-flex"><span>Bill Discount</span><span>- Rs 30.00</span></div>';
                html += '<div class="row-flex"><span>Grand Total</span><span>Rs ' + money(billTot - 30) + '</span></div>';
                if (state.show_savings) html += '<div class="save-box">' + esc(state.savings_label || 'You Saved') + ' Rs 50</div>';
                html += '<div class="net-box">Net Total: Rs ' + money(billTot - 30) + '</div>';
                if (state.show_customer_outstanding) html += '<div class="text-center">Outstanding amount of Rs 90</div>';
                if (state.show_upi_qr) html += '<div class="text-center mt-2">Scan QR to Pay Bill:</div><div class="qr"></div>';
                html += thanksLine();
                return html;
            }

            function renderTpl5() {
                var html = shopBlock({ showPhone: false });
                html += customerBlock('left');
                html += '<div class="dash"></div><div class="title">' + esc(heading()) + '</div>';
                html += '<div class="row-flex muted"><span>22-09-2026 01:09 PM</span><span>INV 1001</span></div><div class="dash"></div>';
                html += '<table><thead><tr><th class="text-start">ITEM</th>';
                if (state.show_item_qty) html += '<th>QTY</th>';
                if (state.show_item_mrp) html += '<th>MRP</th>';
                if (state.show_item_sp) html += '<th>' + esc(state.sp_column_label || 'SP') + '</th>';
                if (state.show_item_total) html += '<th>TOTAL</th>';
                html += '</tr></thead><tbody>';
                products.forEach(function(p, i) {
                    html += '<tr><td class="text-start" style="' + wrapStyle() + '">' + itemNameWithGst(p.n, p.gst);
                    if (state.show_item_mrp === false) html += '<div class="tax-line">MRP ' + money(p.m) + '</div>';
                    html += itemExtras(p, i) + '</td>';
                    if (state.show_item_qty) html += '<td>' + p.q + '</td>';
                    if (state.show_item_mrp) html += '<td>' + money(p.m) + '</td>';
                    if (state.show_item_sp) html += '<td>' + money(p.s) + '</td>';
                    if (state.show_item_total) html += '<td>' + money(p.s * p.q) + '</td></tr>';
                    else html += '</tr>';
                });
                html += '</tbody></table><div class="dash"></div>';
                html += '<div class="row-flex"><span>Items / Sub Total</span><span>10 / Rs 620</span></div>';
                html += '<div class="row-flex"><span>Delivery Charge</span><span>Rs 20</span></div>';
                html += '<div class="row-flex"><span>Cash</span><span>Rs 320</span></div>';
                html += '<div class="row-flex"><span>UPI</span><span>Rs 320</span></div>';
                html += '<div class="dbl"></div>';
                html += '<div class="row-flex"><strong>Total</strong><strong>Rs 640.00</strong></div>';
                html += footerExtras();
                return html;
            }

            function renderTpl6() {
                var priceLabel = (state.sp_column_label && state.sp_column_label !== 'SP') ? state.sp_column_label : 'Price';
                var title = (state.enable_custom_heading && state.custom_heading_text) ? state.custom_heading_text : 'Invoice';
                var html = '<div class="text-center">';
                if (state.show_logo) html += '<div class="text-center mb-1">🏪</div>';
                html += '<strong>' + esc(shopName) + '</strong><div>Ph.No.: ' + esc(shopPhone) + '</div>';
                if (state.show_shop_gst && shopGst) html += '<div>GST: ' + esc(shopGst) + '</div>';
                if (state.show_fssai && shopFssai) html += '<div>FSSAI: ' + esc(shopFssai) + '</div>';
                html += '<div class="title">' + esc(title) + '</div></div>';
                html += '<div class="row-flex"><span>Cash Sale</span><span>Date: 02/10/2026<br>Time: 03:43 PM</span></div>';
                html += customerBlock('left');
                html += '<table><thead><tr>';
                if (state.show_serial) html += '<th>#</th>';
                html += '<th class="text-start">Item Name' + (state.show_item_qty ? '<br>Quantity' : '') + '</th>';
                if (state.show_item_mrp) html += '<th>MRP</th>';
                if (state.show_item_sp) html += '<th>' + esc(priceLabel) + '</th>';
                if (state.show_item_total) html += '<th>Amount</th>';
                html += '</tr></thead><tbody>';
                products.slice(0, 4).forEach(function(p, i) {
                    var sp = state.sp_price_source === 'mrp' ? p.m : p.s;
                    html += '<tr>';
                    if (state.show_serial) html += '<td>' + (i + 1) + '</td>';
                    html += '<td class="text-start" style="' + wrapStyle() + '">' + itemNameWithGst(p.n, p.gst) + itemExtras(p, i) + '</td>';
                    if (state.show_item_qty) html += '<td>' + p.q + 'Pcs</td>';
                    if (state.show_item_mrp) html += '<td>' + money(p.m) + '</td>';
                    if (state.show_item_sp) html += '<td>' + money(sp) + '</td>';
                    if (state.show_item_total) html += '<td>' + money(sp * p.q) + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                html += '<div class="row-flex"><span>' + (state.show_total_item_count ? 'Total: 14' : 'Total') + '</span><span>2,465.00</span></div>';
                html += '<div class="row-flex"><span>Total</span><span>: 2,465.00</span></div>';
                html += '<div class="row-flex"><span>Received</span><span>: 2,465.00</span></div>';
                html += footerExtras();
                return html;
            }

            function renderPreview() {
                readForm();
                var tpl = parseInt(state.preferred_template, 10) || 1;
                tpl = Math.min(5, Math.max(1, tpl));
                $('#tplBadge').text(tplNames[tpl] || ('Template ' + tpl));
                var $r = $('#receiptPreview');
                $r.toggleClass('paper-2inch', state.paper_size === '2inch');
                $r.css('font-size', (state.font_size || 8) + 'px');
                $r.css('padding-left', state.left_margin_mm > 0 ? (10 + Number(state.left_margin_mm) * 2) + 'px' : '10px');

                var html;
                if (tpl === 2) html = renderTpl2();
                else if (tpl === 3) html = renderTpl3();
                else if (tpl === 4) html = renderTpl4();
                else if (tpl === 5) html = renderTpl5();
                else html = renderTpl6();

                $r.html(html);
            }

            $('.ci-tabs').on('click', 'button', function() {
                var tab = $(this).data('tab');
                $('.ci-tabs button').removeClass('active');
                $(this).addClass('active');
                $('.ci-panel').removeClass('active');
                $('.ci-panel[data-panel="' + tab + '"]').addClass('active');
            });

            $('.ci-paper-btns').on('click', 'button', function() {
                var paper = $(this).data('paper');
                $('input[name="paper_size"]').val(paper);
                $('.ci-paper-btns button').removeClass('active');
                $(this).addClass('active');
                renderPreview();
            });

            $('#invoiceCustomForm').on('change input', 'input, select', function() {
                if ($(this).attr('name') === 'logo_size') {
                    $('#logoSizeVal').text(Number($(this).val()).toFixed(2));
                }
                renderPreview();
            });

            $(document).on('click', '.thanks-preset', function() {
                var msg = $(this).data('msg') || '';
                $('input[name="thanks_message"]').val(msg);
                $('input[name="show_thanks"]').prop('checked', true);
                renderPreview();
            });

            $('#btnSaveInvoiceCustom').on('click', function() {
                readForm();
                var $btn = $(this);
                $btn.prop('disabled', true).text('Saving…');
                $.ajax({
                    url: @json(route('setting.customize-invoice.save')),
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        invoice_customization: state
                    },
                    success: function(res) {
                        $btn.prop('disabled', false).text('Save');
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved',
                                text: 'Applied on sales bill templates.',
                                timer: 1600,
                                showConfirmButton: false,
                                confirmButtonColor: '#ff9f43'
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Could not save', confirmButtonColor: '#ff9f43' });
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).text('Save');
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not save';
                        Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ff9f43' });
                    }
                });
            });

            applyForm();
            renderPreview();
        })();
    </script>
</body>
</html>
