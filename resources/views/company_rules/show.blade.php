@extends('layout.app')
@section('content')
<style>
    .info-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .section-header {
        background: linear-gradient(135deg, #ea6161 0%, #d44848 100%);
        color: #ffffff;
        padding: 15px 20px;
        border-radius: 8px;
        margin: 25px 0 15px 0;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-value {
        color: #6c757d;
        font-weight: 500;
    }
    
    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-enabled {
        background-color: #d4edda;
        color: #155724;
    }
    
    .badge-disabled {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
    }
    
    @media (max-width: 768px) {
        .info-row {
            flex-direction: column;
            gap: 5px;
        }
        
        .section-header {
            font-size: 14px;
            padding: 12px 15px;
        }
    }
</style>

<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">Company Rules & Policies</h4>
                        <p class="text-muted mb-0">View your company's HR policies and attendance rules</p>
                    </div>
                </div>

                <div id="rulesContent">
                    <!-- ATTENDANCE & WORKING HOURS -->
                    <div class="section-header">
                        <i class="mdi mdi-calendar-clock"></i>
                        <span>Attendance & Working Hours</span>
                    </div>
                    <div class="info-card">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-clock-start text-primary"></i>
                                Office Start Time
                            </div>
                            <div class="info-value" id="display_start_time"></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-clock-end text-primary"></i>
                                Office End Time
                            </div>
                            <div class="info-value" id="display_end_time"></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-food text-primary"></i>
                                Lunch Break Duration
                            </div>
                            <div class="info-value" id="display_lunch_break"></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-timer text-primary"></i>
                                Grace Period
                            </div>
                            <div class="info-value" id="display_grace_period"></div>
                        </div>
                    </div>

                    <!-- WEEKEND CONFIGURATION -->
                    <div class="section-header">
                        <i class="mdi mdi-calendar-week"></i>
                        <span>Weekend Configuration</span>
                    </div>
                    <div class="info-card">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-calendar-weekend text-primary"></i>
                                Sunday
                            </div>
                            <div class="info-value">
                                <span class="badge-status badge-info">Weekly Off</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-calendar-week text-primary"></i>
                                Saturday Status
                            </div>
                            <div class="info-value" id="display_saturday_status"></div>
                        </div>
                        <div class="info-row" id="saturday_type_row" style="display:none;">
                            <div class="info-label">
                                <i class="mdi mdi-calendar-text text-primary"></i>
                                Saturday Off
                            </div>
                            <div class="info-value" id="display_saturday_type"></div>
                        </div>
                        <div class="info-row" id="saturday_half_day_row" style="display:none;">
                            <div class="info-label">
                                <i class="mdi mdi-calendar-clock text-primary"></i>
                                Saturday Half Day
                            </div>
                            <div class="info-value" id="display_saturday_half_day"></div>
                        </div>
                    </div>
                    
                    <!-- PAYROLL CONFIGURATION -->
                    <div class="section-header">
                        <i class="mdi mdi-cash-multiple"></i>
                        <span>Payroll Configuration</span>
                    </div>
                    <div class="info-card">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-briefcase text-primary"></i>
                                Payroll Type
                            </div>
                            <div class="info-value" id="display_payroll_type"></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-clock-outline text-primary"></i>
                                Working Hours Per Day
                            </div>
                            <div class="info-value" id="display_working_hours"></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-clock-outline text-primary"></i>
                                Half Day Hours
                            </div>
                            <div class="info-value" id="display_half_day_hours"></div>
                        </div>
                    </div>

                    <!-- OVERTIME CONFIGURATION -->
                    <div class="section-header">
                        <i class="mdi mdi-clock-plus"></i>
                        <span>Overtime Configuration</span>
                    </div>
                    <div class="info-card">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-toggle-switch text-primary"></i>
                                Overtime Status
                            </div>
                            <div class="info-value" id="display_overtime_status"></div>
                        </div>
                        <!-- <div class="info-row" id="overtime_details" style="display:none;">
                            <div class="info-label">
                                <i class="mdi mdi-multiplication text-primary"></i>
                                Overtime Multiplier
                            </div>
                            <div class="info-value" id="display_overtime_multiplier"></div>
                        </div> -->
                        <div class="info-row" id="min_overtime_row" style="display:none;">
                            <div class="info-label">
                                <i class="mdi mdi-timer text-primary"></i>
                                Minimum Overtime Count
                            </div>
                            <div class="info-value" id="display_min_overtime"></div>
                        </div>
                    </div>
                    

                    <!-- TAX & DEDUCTIONS -->
                    <div class="section-header">
                        <i class="mdi mdi-calculator"></i>
                        <span>Tax & Deductions</span>
                    </div>
                    <div class="info-card">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-toggle-switch text-primary"></i>
                                Tax Status
                            </div>
                            <div class="info-value" id="display_tax_status"></div>
                        </div>
                        <div id="tax_details" style="display:none;">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="mdi mdi-calculator text-primary"></i>
                                    Tax Type
                                </div>
                                <div class="info-value" id="display_tax_type"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="mdi mdi-cash text-primary"></i>
                                    Tax Value
                                </div>
                                <div class="info-value" id="display_tax_value"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="mdi mdi-cash text-primary"></i>
                                    Taxable Salary Threshold
                                </div>
                                <div class="info-value" id="display_tax_threshold"></div>
                            </div>
                        </div>
                    </div>

                    <!-- WORKING DAYS CALCULATION -->
                    <div class="section-header">
                        <i class="mdi mdi-calendar-check"></i>
                        <span>Working Days Calculation</span>
                    </div>
                    <div class="info-card">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="mdi mdi-calendar-multiple text-primary"></i>
                                Holiday Treatment
                            </div>
                            <div class="info-value" id="display_holiday_treatment"></div>
                        </div>
                    </div>
                </div>

                <div id="loadingMessage" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading company rules...</p>
                </div>

                <div id="errorMessage" style="display:none;" class="alert alert-danger"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Hide content initially
    $('#rulesContent').hide();
    
    // Load company rules
    var authToken = localStorage.getItem("authToken");
    const headers = {
        "Authorization": "Bearer " + authToken,
        'Content-Type': 'application/json'
    };

    fetch('/api/rules_get', { headers })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const rules = data.data;
                
                // Payroll Configuration
                const payrollTypes = {
                    'monthly': 'Monthly',
                    'daily': 'Per Day',
                    'hourly': 'Hourly'
                };
                $('#display_payroll_type').html(`<span class="badge-status badge-info">${payrollTypes[rules.payroll_type] || 'Monthly'}</span>`);
                
                // Convert working hours from decimal to hours and minutes (8.5 = 8h 30m)
                const hoursDecimal = parseFloat(rules.working_hours_per_day);
                const hours = Math.floor(hoursDecimal);
                const minutes = Math.round((hoursDecimal - hours) * 60);
                const workingHoursDisplay = minutes > 0 
                    ? `${hours}h ${minutes}m` 
                    : `${hours}h`;
                $('#display_working_hours').text(workingHoursDisplay);
                
                // Format half day hours
                if (rules.half_day_hours) {
                    const halfDayDecimal = parseFloat(rules.half_day_hours);
                    const halfHours = Math.floor(halfDayDecimal);
                    const halfMinutes = Math.round((halfDayDecimal - halfHours) * 60);
                    const halfDayDisplay = halfMinutes > 0 
                        ? `${halfHours}h ${halfMinutes}m` 
                        : `${halfHours}h`;
                    $('#display_half_day_hours').text(halfDayDisplay);
                } else {
                    $('#display_half_day_hours').text('Not Set');
                }
                
                // Overtime Configuration
                if (rules.enable_overtime == 1) {
                    $('#display_overtime_status').html('<span class="badge-status badge-enabled">Enabled</span>');
                    $('#overtime_details').show();
                    $('#min_overtime_row').show();
                    // let multiplierText = '';

                    // if (rules.overtime_multiplier == 1 || rules.overtime_multiplier == 1.0) {
                    //     multiplierText = 'Paid at regular hourly rate (1 hour overtime = 1 hour pay)';
                    // } else if (rules.overtime_multiplier > 1) {
                    //     multiplierText = `Paid at ${rules.overtime_multiplier} times the regular rate`;
                    // } else {
                    //     multiplierText = 'No additional overtime pay';
                    // }
                    // $('#display_overtime_multiplier').text(multiplierText);
                    $('#display_min_overtime').text(rules.min_overtime_count_in_minutes + ' minutes');
                } else {
                    $('#display_overtime_status').html('<span class="badge-status badge-disabled">Disabled</span>');
                }
                
                // Attendance & Working Hours
                $('#display_start_time').text(formatTime(rules.start_time) || 'Not Set');
                $('#display_end_time').text(formatTime(rules.end_time) || 'Not Set');
                $('#display_lunch_break').text(rules.lunch_break || 'Not Set');
                $('#display_grace_period').text(rules.grace_period + ' minutes');
                
                // Saturday Configuration
                const ordinalMap = {
                    '1': '1st Saturday',
                    '2': '2nd Saturday',
                    '3': '3rd Saturday',
                    '4': '4th Saturday',
                    '5': '5th Saturday'
                };
                
                if (rules.saturday_off_enabled == 1) {
                    $('#saturday_type_row').show();
                    
                    if (rules.saturday_off_type === 'all') {
                        $('#display_saturday_status').html('<span class="badge-status badge-enabled">All Saturdays Off</span>');
                        $('#display_saturday_type').text('Every Saturday Off');
                    } else {
                        $('#display_saturday_status').html('<span class="badge-status badge-enabled">Partial Saturday Off</span>');
                        
                        // Display which Saturdays are off
                        if (rules.saturday_off_pattern) {
                            const saturdayOffList = rules.saturday_off_pattern
                                .split(',')
                                .map(num => ordinalMap[num.trim()])
                                .filter(Boolean)
                                .join(', ');
                            $('#display_saturday_type').text(saturdayOffList + ' Off');
                        } else {
                            const saturdayTypes = {
                                'alternate-even': 'Alternate Saturday Off (Even weeks - 2nd & 4th)',
                                'alternate-odd': 'Alternate Saturday Off (Odd weeks - 1st, 3rd & 5th)'
                            };
                            $('#display_saturday_type').text(saturdayTypes[rules.saturday_off_type] || 'Custom Pattern');
                        }
                    }
                } else {
                    $('#display_saturday_status').html('<span class="badge-status badge-disabled">All Saturdays Working</span>');
                }
                
                // Saturday Half Day Pattern
                if (rules.saturday_half_day_pattern) {
                    $('#saturday_half_day_row').show();
                    const saturdayHalfDayList = rules.saturday_half_day_pattern
                        .split(',')
                        .map(num => ordinalMap[num.trim()])
                        .filter(Boolean)
                        .join(', ');
                    $('#display_saturday_half_day').html(`<span class="badge-status badge-info">${saturdayHalfDayList}</span>`);
                }
                
                // Tax Configuration
                if (rules.enable_tax == 1) {
                    $('#display_tax_status').html('<span class="badge-status badge-enabled">Enabled</span>');
                    $('#tax_details').show();
                    
                    const taxTypes = {
                        'fixed': 'Fixed Amount',
                        'percentage': 'Percentage',
                        'slab': 'Slab Based'
                    };
                    $('#display_tax_type').text(taxTypes[rules.tax_type] || 'Fixed Amount');
                    
                    if (rules.tax_type === 'percentage') {
                        $('#display_tax_value').text(rules.tax + '%');
                    } else {
                        $('#display_tax_value').text('Rs ' + rules.tax);
                    }
                    
                    $('#display_tax_threshold').text('Rs ' + (rules.salary_above_tax || '0'));
                } else {
                    $('#display_tax_status').html('<span class="badge-status badge-disabled">Disabled</span>');
                }
                
                // Working Days Calculation
                if (rules.include_holidays_in_working_days == 1) {
                    $('#display_holiday_treatment').html('<span class="badge-status badge-enabled">Holidays counted as working days</span>');
                } else {
                    $('#display_holiday_treatment').html('<span class="badge-status badge-info">Holidays excluded from working days</span>');
                }
                
                // Show content and hide loading
                $('#loadingMessage').hide();
                $('#rulesContent').fadeIn();
                
            } else {
                showError('No company rules configured yet.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to load company rules. Please try again later.');
        });
    
    function formatTime(time) {
        if (!time) return null;
        
        // Convert 24-hour to 12-hour format
        const parts = time.split(':');
        let hours = parseInt(parts[0]);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12
        
        return hours + ':' + minutes + ' ' + ampm;
    }
    
    function showError(message) {
        $('#loadingMessage').hide();
        $('#rulesContent').hide();
        $('#errorMessage').text(message).show();
    }
});
</script>

@endsection
