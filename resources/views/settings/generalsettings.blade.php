@extends('layout.app')

@section('title', 'Shop Settings')

@section('content')
    <style>
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 15px !important;
            }

            .image-upload input[type=file] {
                height: 115px !important;
            }
        }

        .rules-form .section-header {
            background: #f8f9fa;
            color: #495057;
            padding: 10px 14px;
            border-radius: 6px;
            margin: 16px 0 12px;
            font-weight: 600;
            border-left: 4px solid #ff9f43;
        }

        .rules-form .input-group-text {
            background: #fff6ee;
            border-color: #f3dfc8;
            color: #ff9f43;
        }

        .rules-form .input-group-prepend,
        .rules-form .input-group-text {
            display: none !important;
        }

        .rules-form .section-header i {
            display: none !important;
        }

        .rules-form .input-group>.form-control,
        .rules-form .input-group>.form-select {
            border-radius: 6px !important;
        }

        .rules-form .form-control,
        .rules-form .form-select {
            border-radius: 6px;
        }

        .rules-form .form-group.row {
            margin-bottom: 12px;
        }

        .dashboard-mobile-subsection-tabs {
            display: none;
        }

        @media screen and (max-width: 767px) {
            .dashboard-mobile-subsection-tabs {
                background: #f8fafc;
                border: 1px solid #e6eaf0;
                border-radius: 8px;
                display: grid;
                gap: 6px;
                grid-template-columns: repeat(3, 1fr);
                padding: 4px;
            }

            .dashboard-mobile-subsection-tab {
                background: transparent;
                border: 0;
                border-radius: 6px;
                color: #667085;
                font-size: 12px;
                font-weight: 600;
                min-height: 34px;
                padding: 7px 6px;
            }

            .dashboard-mobile-subsection-tab.active {
                background: #fff;
                box-shadow: 0 1px 3px rgba(16, 24, 40, 0.1);
                color: #ff9f43;
            }

            #dashboard-settings .dashboard-mobile-panel:not(.active) {
                display: none !important;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Shop Settings.</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('auth.profile') }}" class="btn btn-added">View Profile</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="shop-tab" data-bs-toggle="tab" data-bs-target="#shop-settings"
                            type="button" role="tab" aria-controls="shop-settings" aria-selected="true">
                            🏪 Shop Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rules-tab" data-bs-toggle="tab" data-bs-target="#company-rules"
                            type="button" role="tab" aria-controls="company-rules" aria-selected="false">
                            🏢 Company Rules
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-settings"
                            type="button" role="tab" aria-controls="dashboard-settings" aria-selected="false">
                            📊 Dashboard Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pos-integration-tab" data-bs-toggle="tab" data-bs-target="#pos-integration"
                            type="button" role="tab" aria-controls="pos-integration" aria-selected="false">
                            🔌 POS Integration
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-4" id="settingsTabsContent">
                    <!-- ================= SHOP SETTINGS TAB ================= -->
                    <div class="tab-pane fade show active" id="shop-settings" role="tabpanel" aria-labelledby="shop-tab">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Shop Name<span class="manitory">*</span></label>
                                    <input type="text" id="shop_name" placeholder="Enter Title">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Shop Email<span class="manitory">*</span></label>
                                    <input type="text" id="email" placeholder="Enter email">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Shop Phone<span class="manitory">*</span></label>
                                    <input type="text" id="phone" placeholder="Enter Phone">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>GST Number</label>
                                    <input type="text" id="gst_num" placeholder="Enter GST Number">
                                </div>
                            </div>

                                <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Bank Name<span class="manitory">*</span></label>
                            <input type="text" id="bank_name" placeholder="Enter Bank Name">
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Branch<span class="manitory">*</span></label>
                            <input type="text" id="branch" placeholder="Enter Branch">
                        </div>
                    </div>

                     <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>A/C No.<span class="manitory">*</span></label>
                            <input type="number" id="ac_no" class="form-control" placeholder="Enter A/C No.">
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>IFSC Code<span class="manitory">*</span></label>
                            <input type="text" id="ifsc_code" placeholder="Enter IFSC Code">
                        </div>
                    </div>

                            <div class="col-lg-3 col-sm-12">
                                <div class="form-group">
                                    <label>Shop Address<span class="manitory">*</span></label>
                                    <textarea id="address" placeholder="Enter Address" rows="3" class="form-control"></textarea>
                                    <div class="d-flex justify-content-between align-items-start mt-1">
                                        <small id="coordinateLookupStatus" class="text-muted" style="line-height:1.2;"></small>
                                        <button type="button" class="btn btn-sm btn-primary ms-2 flex-shrink-0" id="btnFetchCoordinates" style="padding: 2px 8px; font-size: 11px;">Get GPS from Address</button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-12 col-6">
                                <div class="form-group">
                                    <label>Shop Currency Symbol <span class="manitory">*</span></label>
                                    <select id="currency_symbol" class="form-select">
                                        <option value="₹" selected>₹ (Indian Rupee)</option>
                                        <option value="$">$ (US Dollar)</option>
                                        <option value="€">€ (Euro)</option>
                                        <option value="£">£ (British Pound)</option>
                                        <option value="¥">¥ (Japanese Yen)</option>
                                        <option value="₩">₩ (South Korean Won)</option>
                                        <option value="₽">₽ (Russian Ruble)</option>
                                        <option value="₺">₺ (Turkish Lira)</option>
                                        <option value="₫">₫ (Vietnamese Dong)</option>
                                        <option value="฿">฿ (Thai Baht)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-12 col-6">
                                <div class="form-group">
                                    <label for="currency_position" class="form-label">
                                        Currency Symbol Position
                                    </label>
                                    <select class="form-select" id="currency_position" name="currency_position" required>
                                        <option value="left">Left (e.g. ₹100 or $100)</option>
                                        <option value="right">Right (e.g. 100₹ or 100$)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Low Stock Warning Quantity</label>
                                    <input type="number" id="low_stock" class="form-control"
                                        placeholder="Enter Low Stock Warning Quantity">
                                    <small id="lowStockError" class="text-danger d-none">Low Stock Quantity cannot be
                                        negative.</small>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-12 col-6">
                                <div class="form-group">
                                    <label>Invoice Size</label>
                                    <select id="invoice_size" name="invoice_size" class="form-select">
                                        <option value="small" {{ old('invoice_size', $setting->invoice_size ?? 'big') == 'small' ? 'selected' : '' }}>Small Size Invoice</option>
                                        <option value="big" {{ old('invoice_size', $setting->invoice_size ?? 'big') == 'big' ? 'selected' : '' }}>Big Size Invoice</option>
                                    </select>
                                </div>
                            </div>
                              <div class="col-lg-3 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Send Mail</label>
                                    <select id="send_mail" class="form-select">
                                        <option value="1">On</option>
                                        <option value="0">Off</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Customer WhatsApp Message</label>
                                    <select id="customer_whatsapp_message" class="form-select">
                                        <option value="1">On</option>
                                        <option value="0">Off</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Admin WhatsApp Message</label>
                                    <select id="admin_whatsapp_message" class="form-select">
                                        <option value="1">On</option>
                                        <option value="0">Off</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-lg-4 col-6">
                                <div class="form-group">
                                    <label>Shop Logo</label>
                                    <div class="image-upload">
                                        <input type="file" id="logo" accept="image/*">
                                        <div class="image-uploads">
                                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/upload.svg' }}"
                                                alt="img">
                                            <h4>Drag and drop a file to upload</h4>
                                        </div>
                                    </div>
                                    <img id="logo_preview" src="" alt="Logo Preview"
                                        style="display:none; max-width: 100px; margin-top: 10px;">
                                </div>
                            </div>

                            <!-- <div class="col-lg-4 col-6">
                                <div class="form-group">
                                    <label>Shop Favicon</label>
                                    <div class="image-upload">
                                        <input type="file" id="favicon" accept="image/*">
                                        <div class="image-uploads">
                                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/upload.svg' }}"
                                                alt="img">
                                            <h4>Drag and drop a file to upload</h4>
                                        </div>
                                    </div>
                                    <img id="favicon_preview" src="" alt="Favicon Preview"
                                        style="display:none; max-width: 50px; margin-top: 10px;">
                                </div>
                            </div> -->

                            <div class="col-lg-4 col-6">
                                <div class="form-group">
                                    <label>Shop QR Code Image</label>
                                    <div class="image-upload">
                                        <input type="file" id="qr_code" accept="image/*">
                                        <div class="image-uploads">
                                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/upload.svg' }}"
                                                alt="img">
                                            <h4>Drag and drop a file to upload</h4>
                                        </div>
                                    </div>
                                    <img id="qr_preview" src="" alt="QR Preview"
                                        style="display:none; max-width: 50px; margin-top: 10px;">
                                </div>
                            </div>

                          
                            <div class="col-lg-12">
                                <a href="javascript:void(0);" class="btn btn-submit me-2"
                                    id="btn-setting-submit">Submit</a>
                            </div>
                        </div>
                    </div>

                    <!-- ================= COMPANY RULES TAB ================= -->
                    <div class="tab-pane fade" id="company-rules" role="tabpanel" aria-labelledby="rules-tab">
                        <form id="companyRulesForm" class="rules-form">
                            <input type="hidden" name="id" id="id">

                            <div class="section-header">
                                <i class="mdi mdi-cash-multiple"></i> Payroll Configuration
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Payroll Type</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-briefcase fs-5"></i></span>
                                                </div>
                                                <select class="form-control" name="payroll_type" id="payroll_type">
                                                    <option value="monthly">Monthly</option>
                                                    <option value="hourly">Hourly</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Working Hours/Day</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-clock-outline fs-5"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="working_hours_per_day" id="working_hours_per_day" step="0.5" placeholder="e.g. 8" />
                                            </div>
                                            <div id="display_working_hours" class="mt-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Half Day Hours</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-clock-outline fs-5"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="half_day_hours" id="half_day_hours" step="0.5" placeholder="e.g. 5 (5 hrs = half day)" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-header">
                                <i class="mdi mdi-clock-plus"></i> Overtime Configuration
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-5 col-form-label">Enable Overtime</label>
                                        <div class="col-sm-8 col-7">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enable_overtime" name="enable_overtime">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Overtime Rate Type</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-cash-plus fs-5"></i></span>
                                                </div>
                                                <select class="form-control" name="overtime_rate_type" id="overtime_rate_type">
                                                    <option value="multiplier">Multiplier (e.g. 1.5x)</option>
                                                    <option value="fixed">Fixed Rate per Hour</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Overtime Multiplier</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-multiplication fs-5"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="overtime_multiplier" id="overtime_multiplier" step="any" placeholder="e.g. 1.5" value="1.5" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Min OT Count(In Minutes)</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-clock-outline fs-5"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="min_overtime_count_in_minutes" id="min_overtime_count_in_minutes" step="any" placeholder="e.g. 30" value="0" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-header">
                                <i class="mdi mdi-calendar-clock"></i> Attendance & Working Days
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Start Time</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-clock-start fs-5"></i></span>
                                                </div>
                                                <input type="time" class="form-control" name="start_time" id="start_time" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Lunch Break</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-food fs-5"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="lunch_break" id="lunch_break" placeholder="HH:MM:SS (e.g. 01:00:00)" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">End Time</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-clock-end fs-5"></i></span>
                                                </div>
                                                <input type="time" class="form-control" name="end_time" id="end_time" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Grace Period (Min)</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-timer fs-5"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="grace_period" id="grace_period" placeholder="e.g. 15" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Location Check on Login</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-map-marker-radius fs-5"></i></span>
                                                </div>
                                                <select class="form-control" name="location_check_on_login" id="location_check_on_login">
                                                    <option value="1">Enable</option>
                                                    <option value="0">Disable</option>
                                                </select>
                                            </div>
                                            <small class="text-muted d-block mt-1">Require staff to be inside the configured office radius before login or face check-in.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Office Latitude</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="office_latitude" id="office_latitude" placeholder="e.g. 21.1268432" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Office Longitude</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="office_longitude" id="office_longitude" placeholder="e.g. 73.1051204" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Radius (m)</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="office_radius" id="office_radius" placeholder="e.g. 200" min="0" />
                                            </div>
                                            <small class="text-muted d-block mt-1">Staff must be within this distance from office GPS to clock in.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-header">
                                <i class="mdi mdi-calendar-week"></i> Saturday Configuration
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-5 col-form-label">Saturday Weekly Off</label>
                                        <div class="col-sm-8 col-7">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="saturday_off_enabled" name="saturday_off_enabled" checked>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Saturday Off Type</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-calendar-week fs-5"></i></span>
                                                </div>
                                                <select class="form-control" name="saturday_off_type" id="saturday_off_type">
                                                    <option value="all">Every Saturday Off</option>
                                                    <option value="alternate-even">Alternate Saturday Off (Even)</option>
                                                    <option value="alternate-odd">Alternate Saturday Off (Odd)</option>
                                                    <option value="custom">Custom Saturday Off</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Saturday Off Pattern</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-calendar-text fs-5"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="saturday_off_pattern" id="saturday_off_pattern" placeholder="e.g. 1,3,5 or 2,4" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Saturday Half Day Pattern</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-calendar-text fs-5"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="saturday_half_day_pattern" id="saturday_half_day_pattern" placeholder="e.g. 1,3,5 or 2,4" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-header">
                                <i class="mdi mdi-calculator"></i> Tax & Deductions
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-5 col-form-label">Enable Tax</label>
                                        <div class="col-sm-8 col-7">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enable_tax" name="enable_tax" checked>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Tax Type</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-calculator fs-5"></i></span>
                                                </div>
                                                <select class="form-control" name="tax_type" id="tax_type">
                                                    <option value="fixed">Fixed Amount</option>
                                                    <option value="percentage">Percentage</option>
                                                    <option value="slab">Slab Based</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Tax Value</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-cash fs-5"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="tax" id="tax" placeholder="e.g. 200 or 10" step="0.01" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Taxable Salary Threshold</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="mdi mdi-cash fs-5"></i></span>
                                                </div>
                                                <input type="number" class="form-control" name="salary_above_tax" id="salary_above_tax" placeholder="e.g. 12000" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-header">
                                <i class="mdi mdi-calendar-check"></i> Working Days Calculator
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-5 col-form-label">Include Holidays</label>
                                        <div class="col-sm-8 col-7">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="include_holidays_in_working_days" checked>
                                                <label class="form-check-label" for="include_holidays_in_working_days">Include holidays as working days</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-3">
                                <button type="submit" class="btn btn-submit me-2" id="saveCompanyRules">Save Rules</button>
                            </div>
                            <div id="responseMessage" class="mt-2"></div>
                        </form>
                    </div>

                    <!-- ================= DASHBOARD SETTINGS TAB ================= -->
                    <div class="tab-pane fade" id="dashboard-settings" role="tabpanel" aria-labelledby="dashboard-tab">
                        <div class="row">
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>HR Portal Section On Dashboard</label>
                                    <select id="show_hr_dashboard" name="show_hr_dashboard" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                    <small class="text-muted">When disabled, only the staff, attendance, and salary section is hidden.</small>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label>ERP Section On Dashboard</label>
                                    <select id="show_erp_dashboard" name="show_erp_dashboard" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                    <small class="text-muted">When disabled, the entire ERP section is hidden from the dashboard.</small>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-3 dashboard-mobile-subsection-tabs" role="tablist" aria-label="Dashboard subsections">
                                <button type="button" class="dashboard-mobile-subsection-tab active" data-dashboard-subsection="hr">HR</button>
                                <button type="button" class="dashboard-mobile-subsection-tab" data-dashboard-subsection="erp">ERP</button>
                            </div>

                            <div class="col-lg-12 mt-4 mb-3">
                                <h5 class="mb-3">HR Dashboard Subsections</h5>
                                <p class="text-muted small mb-3">Control visibility of individual HR dashboard components:</p>
                            </div>

                            <!-- HR Metric Boxes -->
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Staff Strength Box</label>
                                    <select id="show_hr_staff_strength" name="show_hr_staff_strength" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Active Staff Box</label>
                                    <select id="show_hr_active_staff" name="show_hr_active_staff" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Monthly Attendance Box</label>
                                    <select id="show_hr_monthly_attendance" name="show_hr_monthly_attendance" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Personal Progress Box</label>
                                    <select id="show_hr_personal_progress" name="show_hr_personal_progress" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>

                            <!-- HR Charts and Tables -->
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>7 Day Attendance Pattern Chart</label>
                                    <select id="show_hr_attendance_pattern" name="show_hr_attendance_pattern" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Salary Payroll Trend Chart</label>
                                    <select id="show_hr_salary_payroll_trend" name="show_hr_salary_payroll_trend" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Payroll Snapshot Table</label>
                                    <select id="show_hr_payroll_snapshot" name="show_hr_payroll_snapshot" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Attendance Watch Table</label>
                                    <select id="show_hr_attendance_watch" name="show_hr_attendance_watch" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Payroll Status Table</label>
                                    <select id="show_hr_payroll_status" name="show_hr_payroll_status" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-4 mb-3">
                                <h5 class="mb-3">ERP Dashboard Subsections</h5>
                                <p class="text-muted small mb-3">Control visibility of individual ERP dashboard components:</p>
                            </div>

                            <!-- ERP Top Metric Boxes -->
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Total Sales Amount Box</label>
                                    <select id="show_erp_total_sales" name="show_erp_total_sales" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Total Purchase Amount Box</label>
                                    <select id="show_erp_total_purchase" name="show_erp_total_purchase" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Total Expense Amount Box</label>
                                    <select id="show_erp_total_expense" name="show_erp_total_expense" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ERP Count Boxes -->
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Sales Invoice Count Box</label>
                                    <select id="show_erp_sales_invoice_count" name="show_erp_sales_invoice_count" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Purchase Invoice Count Box</label>
                                    <select id="show_erp_purchase_invoice_count" name="show_erp_purchase_invoice_count" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Customers Count Box</label>
                                    <select id="show_erp_customers_count" name="show_erp_customers_count" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Vendors Count Box</label>
                                    <select id="show_erp_vendors_count" name="show_erp_vendors_count" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ERP Charts -->
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Sales Chart</label>
                                    <select id="show_erp_sales_chart" name="show_erp_sales_chart" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Purchase Chart</label>
                                    <select id="show_erp_purchase_chart" name="show_erp_purchase_chart" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ERP Tables -->
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Recent Sales Table</label>
                                    <select id="show_erp_recent_sales" name="show_erp_recent_sales" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Recent Purchases Table</label>
                                    <select id="show_erp_recent_purchases" name="show_erp_recent_purchases" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-6 col-6">
                                <div class="form-group">
                                    <label>Recent Products Table</label>
                                    <select id="show_erp_recent_products" name="show_erp_recent_products" class="form-select">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-3">
                                <a href="javascript:void(0);" class="btn btn-submit me-2" id="saveDashboardSettings">
                                    Save Dashboard Settings
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ================= POS INTEGRATION TAB ================= -->
                    <div class="tab-pane fade" id="pos-integration" role="tabpanel" aria-labelledby="pos-integration-tab">
                        <div class="row">
                            <!-- Zomato Card -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; background: #fff;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ env('ImagePath').'/admin/assets/img/product/zomato.png' }}" alt="Zomato" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                                                <h5 class="mb-0 fw-bold" style="color: #1b2850; font-size: 16px;">Zomato</h5>
                                            </div>
                                            <span class="badge rounded-pill" id="zomatoStatusBadge" style="background: #f1f3f5; color: #6c757d; font-weight: 500; padding: 6px 12px;">Not Connected</span>
                                        </div>
                                        
                                        <div class="form-group mb-4 p-3 rounded" style="background: #f8f9fa;">
                                            <label class="d-flex justify-content-between align-items-center mb-0 cursor-pointer w-100" style="cursor: pointer;">
                                                <span style="font-weight: 600; color: #495057;">Enable Integration</span>
                                                <div class="form-check form-switch m-0" style="padding-left: 2.5em;">
                                                    <input class="form-check-input pos-toggle" type="checkbox" id="zomatoEnabled" data-platform="zomato" style="width: 40px; height: 20px; cursor: pointer;">
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div class="api-key-container" id="zomatoKeyContainer" style="display: none;">
                                            <div class="form-group mb-0">
                                                <label style="font-weight: 500; margin-bottom: 8px; display: block; color: #495057;">API Key <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="zomatoApiKey" placeholder="Enter Zomato API Key" style="height: 42px; border-radius: 6px 0 0 6px;">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1" style="border-radius: 0 6px 6px 0; border-color: #dee2e6; background: #fff;">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </div>
                                                <small class="text-danger d-none mt-2 d-block" id="zomatoKeyError"><i class="fa fa-exclamation-circle me-1"></i>API key is required to enable Zomato</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Swiggy Card -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; background: #fff;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ env('ImagePath').'/admin/assets/img/product/swiggy.jpg' }}" alt="Swiggy" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                                                <h5 class="mb-0 fw-bold" style="color: #1b2850; font-size: 16px;">Swiggy</h5>
                                            </div>
                                            <span class="badge rounded-pill" id="swiggyStatusBadge" style="background: #f1f3f5; color: #6c757d; font-weight: 500; padding: 6px 12px;">Not Connected</span>
                                        </div>
                                        
                                        <div class="form-group mb-4 p-3 rounded" style="background: #f8f9fa;">
                                            <label class="d-flex justify-content-between align-items-center mb-0 cursor-pointer w-100" style="cursor: pointer;">
                                                <span style="font-weight: 600; color: #495057;">Enable Integration</span>
                                                <div class="form-check form-switch m-0" style="padding-left: 2.5em;">
                                                    <input class="form-check-input pos-toggle" type="checkbox" id="swiggyEnabled" data-platform="swiggy" style="width: 40px; height: 20px; cursor: pointer;">
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div class="api-key-container" id="swiggyKeyContainer" style="display: none;">
                                            <div class="form-group mb-0">
                                                <label style="font-weight: 500; margin-bottom: 8px; display: block; color: #495057;">API Key <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="swiggyApiKey" placeholder="Enter Swiggy API Key" style="height: 42px; border-radius: 6px 0 0 6px;">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1" style="border-radius: 0 6px 6px 0; border-color: #dee2e6; background: #fff;">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </div>
                                                <small class="text-danger d-none mt-2 d-block" id="swiggyKeyError"><i class="fa fa-exclamation-circle me-1"></i>API key is required to enable Swiggy</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4 text-end">
                                <button type="button" class="btn btn-submit" id="savePosSettingsBtn" style="padding: 10px 24px; font-weight: 600;">Save Integration Settings</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const ImagePath = "{{ env('ImagePath') }}";
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let url = "{{ route('general-settings.show') }}";
            if (selectedSubAdminId) {
                url += "?selectedSubAdminId=" + selectedSubAdminId;
            }

            // =====================================================================
            // GEOCODING ENGINE  (Nominatim + Photon — free, no API key)
            // =====================================================================
            const $addressField = $('#address');
            const $officeLatitudeField = $('#office_latitude');
            const $officeLongitudeField = $('#office_longitude');
            const $coordinateLookupStatus = $('#coordinateLookupStatus');
            let lastGeocodedAddress = '';

            function setCoordinateStatus(message, type) {
                type = type || 'muted';
                $coordinateLookupStatus
                    .removeClass('text-muted text-success text-danger text-warning')
                    .addClass('text-' + type)
                    .text(message || '');
            }

            // Scoring helper — how many address keywords appear in Nominatim display_name
            function scoreResult(result, keywords) {
                var name = (result.display_name || '').toLowerCase();
                var score = 0;
                keywords.forEach(function(kw) {
                    if (kw.length > 2 && name.indexOf(kw.toLowerCase()) !== -1) score++;
                });
                var addr = result.address || {};
                var pc = addr.postcode || '';
                keywords.forEach(function(kw) {
                    if (/^\d{6}$/.test(kw) && pc === kw) score += 10;
                });
                return score;
            }

            function extractPincode(address) {
                var m = address.match(/\b(\d{6})\b/);
                return m ? m[1] : '';
            }

            function extractKeywords(address) {
                return address.split(',').map(function(p) { return p.trim(); }).filter(Boolean);
            }

            function extractBuildingName(address) {
                var SKIP = [
                    /^(shop|office|flat|unit|room|plot|door|house|floor|gf|ff)\b/i,
                    /^\d+(st|nd|rd|th)?\s*(floor|fl)?\.?$/i,
                    /^[a-z]?\d+$/i,
                    /^\d+[-–\/]\d+$/i
                ];
                var parts = address.split(',').map(function(p){ return p.trim(); }).filter(Boolean);
                for (var i = 0; i < parts.length; i++) {
                    var p = parts[i].replace(/^\d+[,\s]*/, '').trim();
                    var skip = false;
                    for (var s = 0; s < SKIP.length; s++) {
                        if (SKIP[s].test(parts[i]) || SKIP[s].test(p)) { skip = true; break; }
                    }
                    if (!skip && p.length > 3 && !/^\d/.test(p)) return p;
                }
                return '';
            }

            function extractCity(address) {
                var parts = address.split(',').map(function(p){ return p.trim().replace(/[-–]\s*\d{6}/, '').trim(); }).filter(Boolean);
                var states = ['gujarat','maharashtra','rajasthan','karnataka','tamil nadu','andhra pradesh',
                    'telangana','uttar pradesh','madhya pradesh','west bengal','kerala','punjab','haryana',
                    'bihar','odisha','goa','delhi','assam','himachal pradesh','uttarakhand'];
                for (var i = parts.length - 1; i >= 0; i--) {
                    var lower = parts[i].toLowerCase();
                    if (/^\d{6}$/.test(parts[i])) continue;
                    if (states.indexOf(lower) !== -1) continue;
                    if (/^india$/i.test(parts[i])) continue;
                    if (parts[i].length > 2) return parts[i];
                }
                return '';
            }

            function nominatimFetch(q, keywords) {
                var url = 'https://nominatim.openstreetmap.org/search?'
                    + 'q=' + encodeURIComponent(q)
                    + '&format=json&addressdetails=1&countrycodes=in&limit=10&accept-language=en';
                return fetch(url, { headers: { 'User-Agent': 'hotel-erp/1.0' } })
                    .then(function(r) { return r.ok ? r.json() : []; })
                    .then(function(data) {
                        if (!data || !data.length) return null;
                        var sorted = data.slice().sort(function(a, b) {
                            return scoreResult(b, keywords) - scoreResult(a, keywords);
                        });
                        var best = sorted[0];
                        return {
                            lat: parseFloat(best.lat).toFixed(7),
                            lon: parseFloat(best.lon).toFixed(7),
                            name: best.display_name.substring(0, 100),
                            attemptStr: 'Nominatim'
                        };
                    })
                    .catch(function() { return null; });
            }

            function photonFetch(q, keywords) {
                var url = 'https://photon.komoot.io/api/?q=' + encodeURIComponent(q)
                    + '&limit=5&lang=en&bbox=68.0,8.0,97.5,37.6';
                return fetch(url, { headers: { 'User-Agent': 'hotel-erp/1.0' } })
                    .then(function(r) { return r.ok ? r.json() : {}; })
                    .then(function(data) {
                        var features = (data && data.features) ? data.features : [];
                        if (!features.length) return null;
                        var scored = features.map(function(f) {
                            var props = f.properties || {};
                            var display = [props.name, props.street, props.city, props.state, props.postcode]
                                .filter(Boolean).join(', ');
                            return { feature: f, display: display, score: scoreResult({ display_name: display, address: { postcode: props.postcode || '' } }, keywords) };
                        }).sort(function(a, b) { return b.score - a.score; });
                        var best = scored[0];
                        var coords = best.feature.geometry.coordinates; // [lon, lat]
                        return {
                            lat: parseFloat(coords[1]).toFixed(7),
                            lon: parseFloat(coords[0]).toFixed(7),
                            name: best.display.substring(0, 100),
                            attemptStr: 'Photon'
                        };
                    })
                    .catch(function() { return null; });
            }

            function geocodeWaterfall(address) {
                var keywords = extractKeywords(address);
                var pincode  = extractPincode(address);
                var building = extractBuildingName(address);
                var city     = extractCity(address);

                var cleanParts = address.split(',')
                    .map(function(p) { return p.trim(); })
                    .filter(function(p) {
                        if (!p || p.length < 2) return false;
                        if (/^(nr\.?|near|behind|opp\.?|opposite|above|below|beside)\b/i.test(p)) return false;
                        if (/^[A-Za-z]?\/?[0-9]+/.test(p) && p.length < 8) return false;
                        return true;
                    });
                var shortQuery = cleanParts.slice(-4).join(', ');

                var p1 = pincode ? nominatimFetch(pincode + ', India', [pincode]) : Promise.resolve(null);
                var p2 = function() { return shortQuery ? nominatimFetch(shortQuery, keywords) : Promise.resolve(null); };
                var p3 = function() { return (building && city && pincode) ? nominatimFetch(building + ', ' + city + ', ' + pincode + ', India', keywords) : Promise.resolve(null); };
                var p4 = function() { return (building && city) ? nominatimFetch(building + ', ' + city + ', India', keywords) : Promise.resolve(null); };
                var p5 = function() { return nominatimFetch(address, keywords); };

                return p1
                    .then(function(r) { return r || p2(); })
                    .then(function(r) { return r || p3(); })
                    .then(function(r) { return r || p4(); })
                    .then(function(r) { return r || p5(); });
            }

            // "Get GPS from Address" button
            $(document).on('click', '#btnFetchCoordinates', function() {
                var address = $addressField.val().trim();
                if (!address) {
                    setCoordinateStatus('Please enter an address first.', 'danger');
                    return;
                }
                var $btn = $(this);
                $btn.prop('disabled', true).text('Searching…');
                setCoordinateStatus('🔍 Searching…');

                var keywords = extractKeywords(address);
                var pincode  = extractPincode(address);
                var building = extractBuildingName(address);
                var photonQ  = building ? (building + ', ' + (extractCity(address) || 'India')) : address;

                photonFetch(photonQ, keywords)
                    .then(function(result) {
                        return result || geocodeWaterfall(address);
                    })
                    .then(function(result) {
                        if (!result) {
                            setCoordinateStatus('❌ Address not found. Try entering the PIN code only (e.g. 395009).', 'danger');
                            return;
                        }
                        $officeLatitudeField.val(result.lat);
                        $officeLongitudeField.val(result.lon);
                        lastGeocodedAddress = address;
                        var via = result.attemptStr ? ' [via ' + result.attemptStr + ']' : '';
                        var displayLower = (result.name || '').toLowerCase();
                        var keywordHit = keywords.some(function(kw) {
                            return kw.length > 3 && displayLower.indexOf(kw.toLowerCase()) !== -1;
                        });
                        var pincodeOk = !pincode || displayLower.indexOf(pincode) !== -1;
                        if (!pincodeOk && !keywordHit) {
                            setCoordinateStatus('⚠️ Possible mismatch — verify manually: ' + result.name + via, 'warning');
                        } else {
                            setCoordinateStatus('✅ Found' + via + ': ' + result.name, 'success');
                        }
                    })
                    .catch(function() {
                        setCoordinateStatus('❌ Network error. Check your internet connection.', 'danger');
                    })
                    .finally(function() {
                        $btn.prop('disabled', false).text('Get GPS from Address');
                    });
            });

            function loadGeneralSettings() {
                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        const settings = response.settings;

                        // 🏢 Company Rules
                        $("#working_hours_per_day").val(settings.working_hours ? settings.working_hours
                            .substring(0, 5) : '');
                        $("#grace_period").val(settings.grace_period ? settings.grace_period.substring(
                            0, 5) : '');
                        $("#lunch_break").val(settings.lunch_break ?? '');
                        $("#start_time").val(settings.open_time ? settings.open_time.substring(0, 5) :
                            '');
                        $("#end_time").val(settings.close_time ? settings.close_time.substring(0, 5) :
                            '');
                        $("#location_check_on_login").val(
                            settings.location_check_on_login === null || settings.location_check_on_login === undefined
                            ? '0'
                            : String(Number(settings.location_check_on_login))
                        );
                        $("#office_latitude").val(settings.office_latitude ?? '');
                        $("#office_longitude").val(settings.office_longitude ?? '');
                        $("#office_radius").val(settings.office_radius ?? '200');

                        // Sunday Off
                        if (settings.sunday_off === "yes") {
                            $("#sunday_yes").prop("checked", true);
                        } else {
                            $("#sunday_no").prop("checked", true);
                        }

                        // 🏦 General Info (already working)
                        $("#low_stock").val(settings.low_stock || '');
                        $("#shop_name").val(settings.name);
                        $("#gst_num").val(settings.gst_num);
                        $("#email").val(settings.email);
                        $("#phone").val(settings.phone);
                        $("#address").val(settings.address);
                        $("#bank_name").val(settings.bank_name);
                        $("#branch").val(settings.branch);
                        $("#ac_no").val(settings.ac_no);
                        $("#ifsc_code").val(settings.ifsc_code);
                        $("#send_mail").val(settings.send_mail === null || settings.send_mail === undefined ? '1' : String(Number(settings.send_mail)));
                        $("#customer_whatsapp_message").val(settings.customer_whatsapp_message === null || settings.customer_whatsapp_message === undefined ? '1' : String(Number(settings.customer_whatsapp_message)));
                        $("#admin_whatsapp_message").val(settings.admin_whatsapp_message === null || settings.admin_whatsapp_message === undefined ? '1' : String(Number(settings.admin_whatsapp_message)));

                        if (settings.currency_position) {
                            $("#currency_position").val(settings.currency_position).trigger("change");
                        }
                        $("#currency_symbol").val(settings.currency_symbol);
                        $("#invoice_size").val(settings.invoice_size || 'big');

                        // Logos and Images
                        if (settings.logo) {
                            $("#logo_preview").attr("src", ImagePath + '/storage/' + settings.logo)
                                .show();
                        }
                        if (settings.favicon) {
                            $("#favicon_preview").attr("src", ImagePath + '/storage/' + settings
                                .favicon).show();
                        }
                        if (settings.qr_code) {
                            $("#qr_preview").attr("src", ImagePath + '/storage/' + settings.qr_code)
                                .show();
                        }

                        // Load Dashboard Toggles
                        $("#show_crm_dashboard").val(
                            settings.show_crm_dashboard === null || settings.show_crm_dashboard === undefined
                            ? '1'
                            : String(Number(settings.show_crm_dashboard))
                        );
                        $("#show_hr_dashboard").val(
                            settings.show_hr_dashboard === null || settings.show_hr_dashboard === undefined
                            ? '1'
                            : String(Number(settings.show_hr_dashboard))
                        );
                        $("#show_erp_dashboard").val(
                            settings.show_erp_dashboard === null || settings.show_erp_dashboard === undefined
                            ? '1'
                            : String(Number(settings.show_erp_dashboard))
                        );
                        
                        // CRM Subsection Settings
                        $("#show_crm_lead_pipeline").val(
                            settings.show_crm_lead_pipeline === null || settings.show_crm_lead_pipeline === undefined
                            ? '1'
                            : String(Number(settings.show_crm_lead_pipeline))
                        );
                        $("#show_crm_conversion").val(
                            settings.show_crm_conversion === null || settings.show_crm_conversion === undefined
                            ? '1'
                            : String(Number(settings.show_crm_conversion))
                        );
                        $("#show_crm_followup_load").val(
                            settings.show_crm_followup_load === null || settings.show_crm_followup_load === undefined
                            ? '1'
                            : String(Number(settings.show_crm_followup_load))
                        );
                        $("#show_crm_meeting_momentum").val(
                            settings.show_crm_meeting_momentum === null || settings.show_crm_meeting_momentum === undefined
                            ? '1'
                            : String(Number(settings.show_crm_meeting_momentum))
                        );
                        $("#show_crm_lead_status_mix").val(
                            settings.show_crm_lead_status_mix === null || settings.show_crm_lead_status_mix === undefined
                            ? '1'
                            : String(Number(settings.show_crm_lead_status_mix))
                        );
                        $("#show_crm_activity_trend").val(
                            settings.show_crm_activity_trend === null || settings.show_crm_activity_trend === undefined
                            ? '1'
                            : String(Number(settings.show_crm_activity_trend))
                        );
                        $("#show_crm_pipeline_quality").val(
                            settings.show_crm_pipeline_quality === null || settings.show_crm_pipeline_quality === undefined
                            ? '1'
                            : String(Number(settings.show_crm_pipeline_quality))
                        );
                        $("#show_crm_recent_leads").val(
                            settings.show_crm_recent_leads === null || settings.show_crm_recent_leads === undefined
                            ? '1'
                            : String(Number(settings.show_crm_recent_leads))
                        );
                        $("#show_crm_next_7_days").val(
                            settings.show_crm_next_7_days === null || settings.show_crm_next_7_days === undefined
                            ? '1'
                            : String(Number(settings.show_crm_next_7_days))
                        );

                        // HR Subsection Settings
                        $("#show_hr_staff_strength").val(
                            settings.show_hr_staff_strength === null || settings.show_hr_staff_strength === undefined
                            ? '1'
                            : String(Number(settings.show_hr_staff_strength))
                        );
                        $("#show_hr_active_staff").val(
                            settings.show_hr_active_staff === null || settings.show_hr_active_staff === undefined
                            ? '1'
                            : String(Number(settings.show_hr_active_staff))
                        );
                        $("#show_hr_monthly_attendance").val(
                            settings.show_hr_monthly_attendance === null || settings.show_hr_monthly_attendance === undefined
                            ? '1'
                            : String(Number(settings.show_hr_monthly_attendance))
                        );
                        $("#show_hr_personal_progress").val(
                            settings.show_hr_personal_progress === null || settings.show_hr_personal_progress === undefined
                            ? '1'
                            : String(Number(settings.show_hr_personal_progress))
                        );
                        $("#show_hr_attendance_pattern").val(
                            settings.show_hr_attendance_pattern === null || settings.show_hr_attendance_pattern === undefined
                            ? '1'
                            : String(Number(settings.show_hr_attendance_pattern))
                        );
                        $("#show_hr_salary_payroll_trend").val(
                            settings.show_hr_salary_payroll_trend === null || settings.show_hr_salary_payroll_trend === undefined
                            ? '1'
                            : String(Number(settings.show_hr_salary_payroll_trend))
                        );
                        $("#show_hr_payroll_snapshot").val(
                            settings.show_hr_payroll_snapshot === null || settings.show_hr_payroll_snapshot === undefined
                            ? '1'
                            : String(Number(settings.show_hr_payroll_snapshot))
                        );
                        $("#show_hr_attendance_watch").val(
                            settings.show_hr_attendance_watch === null || settings.show_hr_attendance_watch === undefined
                            ? '1'
                            : String(Number(settings.show_hr_attendance_watch))
                        );
                        $("#show_hr_payroll_status").val(
                            settings.show_hr_payroll_status === null || settings.show_hr_payroll_status === undefined
                            ? '1'
                            : String(Number(settings.show_hr_payroll_status))
                        );

                        // ERP Subsection Settings
                        $("#show_erp_total_sales").val(
                            settings.show_erp_total_sales === null || settings.show_erp_total_sales === undefined
                            ? '1'
                            : String(Number(settings.show_erp_total_sales))
                        );
                        $("#show_erp_total_purchase").val(
                            settings.show_erp_total_purchase === null || settings.show_erp_total_purchase === undefined
                            ? '1'
                            : String(Number(settings.show_erp_total_purchase))
                        );
                        $("#show_erp_total_expense").val(
                            settings.show_erp_total_expense === null || settings.show_erp_total_expense === undefined
                            ? '1'
                            : String(Number(settings.show_erp_total_expense))
                        );
                        $("#show_erp_sales_invoice_count").val(
                            settings.show_erp_sales_invoice_count === null || settings.show_erp_sales_invoice_count === undefined
                            ? '1'
                            : String(Number(settings.show_erp_sales_invoice_count))
                        );
                        $("#show_erp_purchase_invoice_count").val(
                            settings.show_erp_purchase_invoice_count === null || settings.show_erp_purchase_invoice_count === undefined
                            ? '1'
                            : String(Number(settings.show_erp_purchase_invoice_count))
                        );
                        $("#show_erp_customers_count").val(
                            settings.show_erp_customers_count === null || settings.show_erp_customers_count === undefined
                            ? '1'
                            : String(Number(settings.show_erp_customers_count))
                        );
                        $("#show_erp_vendors_count").val(
                            settings.show_erp_vendors_count === null || settings.show_erp_vendors_count === undefined
                            ? '1'
                            : String(Number(settings.show_erp_vendors_count))
                        );
                        $("#show_erp_sales_chart").val(
                            settings.show_erp_sales_chart === null || settings.show_erp_sales_chart === undefined
                            ? '1'
                            : String(Number(settings.show_erp_sales_chart))
                        );
                        $("#show_erp_purchase_chart").val(
                            settings.show_erp_purchase_chart === null || settings.show_erp_purchase_chart === undefined
                            ? '1'
                            : String(Number(settings.show_erp_purchase_chart))
                        );
                        $("#show_erp_recent_sales").val(
                            settings.show_erp_recent_sales === null || settings.show_erp_recent_sales === undefined
                            ? '1'
                            : String(Number(settings.show_erp_recent_sales))
                        );
                        $("#show_erp_recent_purchases").val(
                            settings.show_erp_recent_purchases === null || settings.show_erp_recent_purchases === undefined
                            ? '1'
                            : String(Number(settings.show_erp_recent_purchases))
                        );
                        $("#show_erp_recent_products").val(
                            settings.show_erp_recent_products === null || settings.show_erp_recent_products === undefined
                            ? '1'
                            : String(Number(settings.show_erp_recent_products))
                        );

                        // Load global company rules
                        $.ajax({
                            url: "/api/rules_get",
                            type: "GET",
                            headers: {
                                "Authorization": "Bearer " + authToken
                            },
                            success: function(rulesResponse) {
                                if (rulesResponse.status === 'success') {
                                    const rules = rulesResponse.data;
                                    Object.keys(rules).forEach(key => {
                                        const element = document.getElementById(key);
                                        if (element) {
                                            if (element.type === 'checkbox') {
                                                element.checked = rules[key] == 1 || rules[key] === true;
                                            } else {
                                                if (key === "working_hours_per_day") {
                                                    var hoursDecimal = parseFloat(rules[key]);
                                                    const hours = Math.floor(hoursDecimal);
                                                    const minutes = Math.round((hoursDecimal - hours) * 60);

                                                    const workingHoursDisplay = minutes > 0 ?
                                                        `${hours}h ${minutes}m` :
                                                        `${hours}h`;

                                                    $('#display_working_hours').text(
                                                        `Working time per day: ${workingHoursDisplay}`
                                                    );
                                                }
                                                element.value = rules[key] || '';
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }
                });
            }


            loadGeneralSettings(); // Load on page load

            $('#saturday_off_type').on('change', function(e) {
                const selectedType = $(this).val();
                if (selectedType === "alternate-even") {
                    $('#saturday_off_pattern').val('2,4');
                } else if (selectedType === "alternate-odd") {
                    $('#saturday_off_pattern').val('1,3,5');
                } else if (selectedType === "custom") {
                    $('#saturday_off_pattern').val('1');
                } else {
                    $('#saturday_off_pattern').val('0,0');
                }
            });

            $('#working_hours_per_day').on('change', function() {
                const selectedHours = $(this).val();
                if (!selectedHours) {
                    $('#display_working_hours').text('');
                    return;
                }

                const hoursDecimal = parseFloat(selectedHours);
                const hours = Math.floor(hoursDecimal);
                const minutes = Math.round((hoursDecimal - hours) * 60);

                const workingHoursDisplay = minutes > 0 ?
                    `${hours}h ${minutes}m` :
                    `${hours}h`;

                $('#display_working_hours').text(
                    `Working time per day: ${workingHoursDisplay}`
                );
            });

            // Show Image Preview
            function previewImage(input, previewId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewId).attr("src", e.target.result).show();
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#logo").change(function() {
                previewImage(this, "#logo_preview");
            });

            $("#favicon").change(function() {
                previewImage(this, "#favicon_preview");
            });

            $("#qr_code").change(function() {
                previewImage(this, "#qr_preview");
            });

            // Update General Settings
            $("#btn-setting-submit").on("click", function(e) {
                e.preventDefault(); // prevent form submission if there are errors

                let lowStock = parseFloat($('#low_stock').val()) || 0;

                if (lowStock < 0) {
                    console.log('asd');

                    $("#lowStockError").removeClass("d-none"); // show error
                    $('#low_stock').val(0); // reset to 0
                    return false;
                } else {
                    $("#lowStockError").addClass("d-none"); // hide error
                }

                $(".text-danger").remove(); // clear previous errors

                let hasError = false;
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                const fields = [{
                        id: "low_stock",
                        name: "Low Stock Warning Quantity"
                    },
                    {
                        id: "shop_name",
                        name: "Shop Name"
                    },
                    {
                        id: "email",
                        name: "Email"
                    },
                    {
                        id: "phone",
                        name: "Phone"
                    },
                    {
                        id: "address",
                        name: "Address"
                    },
                    // { id: "bank_name", name: "Bank Name" },
                    // { id: "branch", name: "Branch" },
                    // { id: "ac_no", name: "A/C No." },
                    // { id: "ifsc_code", name: "IFSC Code" },
                    {
                        id: "currency_position",
                        name: "Currency Position"
                    },
                    {
                        id: "currency_symbol",
                        name: "Currency Symbol"
                    },
                ];

                // Validate each required field
                fields.forEach(field => {
                    const value = $("#" + field.id).val();
                    if (!value) {
                        $("#" + field.id)
                            .after(`<div class="text-danger mt-1">${field.name} is required</div>`);
                        hasError = true;
                    }
                });



                // Check if logo and favicon are selected (optional: remove this if not mandatory)
                let logo = $("#logo")[0]?.files[0];
                let favicon = $("#favicon")[0]?.files[0];
                let qr_code = $("#qr_code")[0]?.files[0];

                if (hasError) return; // stop submission if errors found

                // Prepare FormData
                let formData = new FormData();
                formData.append("low_stock", $("#low_stock").val());
                formData.append("shop_name", $("#shop_name").val());
                formData.append("gst_num", $("#gst_num").val());
                formData.append("email", $("#email").val());
                formData.append("phone", $("#phone").val());
                formData.append("address", $("#address").val());
                formData.append("bank_name", $("#bank_name").val());
                formData.append("branch", $("#branch").val());
                formData.append("ac_no", $("#ac_no").val());
                formData.append("ifsc_code", $("#ifsc_code").val());
                formData.append("send_mail", $("#send_mail").val());
                formData.append("customer_whatsapp_message", $("#customer_whatsapp_message").val());
                formData.append("admin_whatsapp_message", $("#admin_whatsapp_message").val());
                formData.append("currency_position", $("#currency_position").val());
                formData.append("currency_symbol", $("#currency_symbol").val());
                formData.append("invoice_size", $("#invoice_size").val());
                formData.append("selectedSubAdminId", selectedSubAdminId);
                if (logo) formData.append("logo", logo);
                if (favicon) formData.append("favicon", favicon);
                if (qr_code) formData.append("qr_code", qr_code);
                formData.append("_token", "{{ csrf_token() }}");

                // Send AJAX
                $.ajax({
                    url: "{{ route('general-settings.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", xhr.responseJSON.message, "error");
                    }
                });
            });

            // ================== COMPANY RULES SAVE ==================
            $("#saveCompanyRules").on("click", function(e) {
                e.preventDefault();

                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                const authToken = localStorage.getItem("authToken");

                // Clear previous validation errors
                $(".text-danger").remove();

                let formData = new FormData();
                formData.append("working_hours", $("#working_hours_per_day").val());
                formData.append("sunday_off", "no");
                formData.append("grace_period", $("#grace_period").val());
                formData.append("lunch_break", $("#lunch_break").val());
                formData.append("open_time", $("#start_time").val());
                formData.append("close_time", $("#end_time").val());
                formData.append("location_check_on_login", $("#location_check_on_login").val());
                formData.append("office_latitude", $("#office_latitude").val());
                formData.append("office_longitude", $("#office_longitude").val());
                formData.append("office_radius", $("#office_radius").val() || 200);
                formData.append("selectedSubAdminId", selectedSubAdminId);
                formData.append("_token", "{{ csrf_token() }}");

                // Save branch location settings first
                $.ajax({
                    url: "{{ route('general-company-settings.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        // Gather and save advanced rules to global company_rules table
                        const companyRulesData = {
                            id: $('#id').val() || '',
                            payroll_type: $('#payroll_type').val(),
                            working_hours_per_day: $('#working_hours_per_day').val(),
                            half_day_hours: $('#half_day_hours').val(),
                            enable_overtime: $('#enable_overtime').is(':checked'),
                            overtime_rate_type: $('#overtime_rate_type').val(),
                            overtime_multiplier: $('#overtime_multiplier').val(),
                            min_overtime_count_in_minutes: $('#min_overtime_count_in_minutes').val(),
                            start_time: $('#start_time').val(),
                            end_time: $('#end_time').val(),
                            lunch_break: $('#lunch_break').val(),
                            grace_period: $('#grace_period').val(),
                            sunday_off: true,
                            sunday_pay_type: "unpaid",
                            saturday_off_enabled: $('#saturday_off_enabled').is(':checked'),
                            saturday_off_type: $('#saturday_off_type').val(),
                            saturday_off_pattern: $('#saturday_off_pattern').val(),
                            saturday_pay_type: "regular",
                            saturday_half_day_enabled: $('#saturday_half_day_enabled').is(':checked'),
                            saturday_half_day_pattern: $('#saturday_half_day_pattern').val(),
                            include_holidays_in_working_days: $('#include_holidays_in_working_days').is(':checked'),
                            enable_tax: $('#enable_tax').is(':checked'),
                            tax_type: $('#tax_type').val(),
                            tax: $('#tax').val(),
                            salary_above_tax: $('#salary_above_tax').val()
                        };

                        $.ajax({
                            url: '/api/company-rules/store',
                            type: 'POST',
                            headers: {
                                "Authorization": "Bearer " + authToken,
                                "Content-Type": "application/json"
                            },
                            data: JSON.stringify(companyRulesData),
                            success: function(rulesResponse) {
                                if (rulesResponse.status === 'success') {
                                    Swal.fire({
                                        title: "Success!",
                                        text: "Company rules saved successfully.",
                                        icon: "success",
                                        confirmButtonText: "OK",
                                        confirmButtonColor: "#ff9f43",
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire("Error!", rulesResponse.message || "Failed to save company rules", "error");
                                }
                            },
                            error: function(xhr) {
                                Swal.fire("Error!", xhr.responseJSON?.message || "Something went wrong saving rules!", "error");
                            }
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;

                            // Display validation messages below each input
                            $.each(errors, function(key, value) {
                                let fieldKey = key;
                                if (key === 'working_hours') fieldKey = 'working_hours_per_day';
                                if (key === 'open_time') fieldKey = 'start_time';
                                if (key === 'close_time') fieldKey = 'end_time';

                                const field = $("#" + fieldKey);
                                if (field.length) {
                                    field
                                        .closest(".form-group")
                                        .append('<div class="text-danger mt-1">' +
                                            value[0] + "</div>");
                                }
                            });
                        } else {
                            Swal.fire("Error!", xhr.responseJSON.message ||
                                "Something went wrong!", "error");
                        }
                    },
                });
            });

            function initDashboardMobileSubsectionTabs() {
                const $dashboardRow = $("#dashboard-settings > .row");
                if (!$dashboardRow.length) return;

                const findHeadingPanel = (title) => $dashboardRow.children(".col-lg-12").filter(function() {
                    return $(this).find("h5").first().text().trim() === title;
                }).first();

                const sections = [
                    { key: "hr", heading: findHeadingPanel("HR Dashboard Subsections") },
                    { key: "erp", heading: findHeadingPanel("ERP Dashboard Subsections") },
                ];
                const $savePanel = $("#saveDashboardSettings").closest(".col-lg-12");

                sections.forEach((section, index) => {
                    if (!section.heading.length) return;
                    const $nextBoundary = sections[index + 1]?.heading?.length ? sections[index + 1].heading : $savePanel;
                    section.items = section.heading.add(section.heading.nextUntil($nextBoundary));
                    section.items.addClass(`dashboard-mobile-panel dashboard-mobile-panel-${section.key}`);
                });

                function activateDashboardSubsection(key) {
                    $(".dashboard-mobile-subsection-tab").removeClass("active");
                    $(`.dashboard-mobile-subsection-tab[data-dashboard-subsection="${key}"]`).addClass("active");
                    $(".dashboard-mobile-panel").removeClass("active");
                    $(`.dashboard-mobile-panel-${key}`).addClass("active");
                }

                activateDashboardSubsection("hr");

                $(document).on("click", ".dashboard-mobile-subsection-tab", function() {
                    activateDashboardSubsection($(this).data("dashboard-subsection"));
                });
            }

            initDashboardMobileSubsectionTabs();

            // ================== DASHBOARD SETTINGS SAVE ==================
            $("#saveDashboardSettings").on("click", function(e) {
                e.preventDefault();

                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                const authToken = localStorage.getItem("authToken");

                let formData = new FormData();
                formData.append("show_crm_dashboard", $("#show_crm_dashboard").val());
                formData.append("show_hr_dashboard", $("#show_hr_dashboard").val());
                formData.append("show_erp_dashboard", $("#show_erp_dashboard").val());
                
                // CRM Subsection Settings
                formData.append("show_crm_lead_pipeline", $("#show_crm_lead_pipeline").val());
                formData.append("show_crm_conversion", $("#show_crm_conversion").val());
                formData.append("show_crm_followup_load", $("#show_crm_followup_load").val());
                formData.append("show_crm_meeting_momentum", $("#show_crm_meeting_momentum").val());
                formData.append("show_crm_lead_status_mix", $("#show_crm_lead_status_mix").val());
                formData.append("show_crm_activity_trend", $("#show_crm_activity_trend").val());
                formData.append("show_crm_pipeline_quality", $("#show_crm_pipeline_quality").val());
                formData.append("show_crm_recent_leads", $("#show_crm_recent_leads").val());
                formData.append("show_crm_next_7_days", $("#show_crm_next_7_days").val());
                
                // HR Dashboard Subsections
                formData.append("show_hr_staff_strength", $("#show_hr_staff_strength").val());
                formData.append("show_hr_active_staff", $("#show_hr_active_staff").val());
                formData.append("show_hr_monthly_attendance", $("#show_hr_monthly_attendance").val());
                formData.append("show_hr_personal_progress", $("#show_hr_personal_progress").val());
                formData.append("show_hr_attendance_pattern", $("#show_hr_attendance_pattern").val());
                formData.append("show_hr_salary_payroll_trend", $("#show_hr_salary_payroll_trend").val());
                formData.append("show_hr_payroll_snapshot", $("#show_hr_payroll_snapshot").val());
                formData.append("show_hr_attendance_watch", $("#show_hr_attendance_watch").val());
                formData.append("show_hr_payroll_status", $("#show_hr_payroll_status").val());
                
                // ERP Dashboard Subsections
                formData.append("show_erp_total_sales", $("#show_erp_total_sales").val());
                formData.append("show_erp_total_purchase", $("#show_erp_total_purchase").val());
                formData.append("show_erp_total_expense", $("#show_erp_total_expense").val());
                formData.append("show_erp_sales_invoice_count", $("#show_erp_sales_invoice_count").val());
                formData.append("show_erp_purchase_invoice_count", $("#show_erp_purchase_invoice_count").val());
                formData.append("show_erp_customers_count", $("#show_erp_customers_count").val());
                formData.append("show_erp_vendors_count", $("#show_erp_vendors_count").val());
                formData.append("show_erp_sales_chart", $("#show_erp_sales_chart").val());
                formData.append("show_erp_purchase_chart", $("#show_erp_purchase_chart").val());
                formData.append("show_erp_recent_sales", $("#show_erp_recent_sales").val());
                formData.append("show_erp_recent_purchases", $("#show_erp_recent_purchases").val());
                formData.append("show_erp_recent_products", $("#show_erp_recent_products").val());
                
                formData.append("selectedSubAdminId", selectedSubAdminId);
                formData.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('general-dashboard-settings.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", xhr.responseJSON.message || "Something went wrong!", "error");
                    }
                });
            });

            /* =========================================
               POS INTEGRATION SETTINGS LOGIC
               ========================================= */
            function loadPosSettings() {
                const settings = posIntegration.getSettings();
                
                // Zomato
                $('#zomatoEnabled').prop('checked', settings.zomato.enabled);
                $('#zomatoApiKey').val(settings.zomato.apiKey || '');
                if (settings.zomato.enabled) {
                    $('#zomatoKeyContainer').show();
                }
                updateStatusBadge('zomato');
                
                // Swiggy
                $('#swiggyEnabled').prop('checked', settings.swiggy.enabled);
                $('#swiggyApiKey').val(settings.swiggy.apiKey || '');
                if (settings.swiggy.enabled) {
                    $('#swiggyKeyContainer').show();
                }
                updateStatusBadge('swiggy');
            }
            
            function updateStatusBadge(platform) {
                const isEnabled = $('#' + platform + 'Enabled').is(':checked');
                const apiKey = $('#' + platform + 'ApiKey').val().trim();
                const badge = $('#' + platform + 'StatusBadge');
                
                if (isEnabled && apiKey) {
                    badge.removeClass('bg-secondary text-white').css({'background': '#d4edda', 'color': '#155724'}).text('Connected');
                } else {
                    badge.css({'background': '#f1f3f5', 'color': '#6c757d'}).text('Not Connected');
                }
            }
            
            $('.pos-toggle').on('change', function() {
                const platform = $(this).data('platform');
                const isChecked = $(this).is(':checked');
                const container = $('#' + platform + 'KeyContainer');
                
                if (isChecked) {
                    container.slideDown(300);
                } else {
                    container.slideUp(300);
                    $('#' + platform + 'KeyError').addClass('d-none');
                }
                updateStatusBadge(platform);
            });
            
            $('#zomatoApiKey, #swiggyApiKey').on('input', function() {
                const platform = this.id.replace('ApiKey', '');
                updateStatusBadge(platform);
                if ($(this).val().trim()) {
                    $('#' + platform + 'KeyError').addClass('d-none');
                }
            });
            
            $('.toggle-password').on('click', function() {
                const input = $(this).siblings('input');
                const icon = $(this).find('i');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            $('#savePosSettingsBtn').on('click', function() {
                let hasError = false;
                const settings = posIntegration.getSettings();
                
                ['zomato', 'swiggy'].forEach(function(platform) {
                    const isEnabled = $('#' + platform + 'Enabled').is(':checked');
                    const apiKey = $('#' + platform + 'ApiKey').val().trim();
                    
                    if (isEnabled && !apiKey) {
                        $('#' + platform + 'KeyError').removeClass('d-none');
                        hasError = true;
                    } else {
                        $('#' + platform + 'KeyError').addClass('d-none');
                        settings[platform].enabled = isEnabled;
                        if (isEnabled) {
                            settings[platform].apiKey = apiKey;
                        }
                    }
                });
                
                if (!hasError) {
                    posIntegration.saveSettings(settings);
                    
                    // Update header immediately
                    if (typeof applyPosHeaderVisibility === 'function') {
                        applyPosHeaderVisibility();
                    }
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'POS Integration settings saved successfully',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    
                    // Update badges just in case
                    updateStatusBadge('zomato');
                    updateStatusBadge('swiggy');
                }
            });
            
            loadPosSettings();
            /* ========================================= */
        });
    </script>
@endpush
