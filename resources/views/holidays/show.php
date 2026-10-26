<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>
<style>
    .holiday-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid #ea6161;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .holiday-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .holiday-date {
        background: linear-gradient(135deg, #ea6161 0%, #d44848 100%);
        color: #ffffff;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        min-width: 80px;
    }
    
    .holiday-day {
        font-size: 32px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 5px;
    }
    
    .holiday-month {
        font-size: 14px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .holiday-info {
        flex: 1;
        padding-left: 20px;
    }
    
    .holiday-title {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }
    
    .holiday-description {
        color: #6c757d;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .holiday-weekday {
        display: inline-block;
        padding: 4px 12px;
        background: #e3f2fd;
        color: #1976d2;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 8px;
    }
    
    .year-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #ffffff;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        text-align: center;
    }
    
    .year-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .year-subtitle {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .stats-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .stats-number {
        font-size: 32px;
        font-weight: 700;
        color: #ea6161;
        line-height: 1;
    }
    
    .stats-label {
        font-size: 14px;
        color: #6c757d;
        margin-top: 5px;
    }
    
    .month-divider {
        background: #e9ecef;
        padding: 10px 15px;
        margin: 25px 0 15px 0;
        border-radius: 5px;
        font-weight: 600;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .no-holidays {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .no-holidays i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .holiday-card {
            flex-direction: column;
        }
        
        .holiday-date {
            margin-bottom: 15px;
        }
        
        .holiday-info {
            padding-left: 0;
        }
        
        .year-title {
            font-size: 24px;
        }
    }
</style>

<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <!-- Year Header -->
                <div class="year-header">
                    <div class="year-title">
                        <i class="mdi mdi-calendar-star"></i>
                        Holiday Calendar <span id="currentYear"></span>
                    </div>
                    <div class="year-subtitle">Company observed holidays for the year</div>
                </div>

                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number" id="totalHolidays">0</div>
                            <div class="stats-label">Total Holidays</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number" id="upcomingHolidays">0</div>
                            <div class="stats-label">Upcoming</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number" id="remainingHolidays">0</div>
                            <div class="stats-label">Remaining</div>
                        </div>
                    </div>
                </div>

                <!-- Holidays List -->
                <div id="holidaysList"></div>

                <!-- Loading State -->
                <div id="loadingMessage" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading holidays...</p>
                </div>

                <!-- No Holidays Message -->
                <div id="noHolidaysMessage" style="display:none;" class="no-holidays">
                    <i class="mdi mdi-calendar-remove"></i>
                    <h5>No Holidays Found</h5>
                    <p>There are no holidays configured for this year.</p>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" style="display:none;" class="alert alert-danger"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const currentYear = new Date().getFullYear();
    $('#currentYear').text(currentYear);
    
    // Hide list initially
    $('#holidaysList').hide();
    
    // Load holidays
    fetch("<?= base_url('api/get_holidays') ?>")
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data.length > 0) {
                const holidays = data.data;
                
                // Filter holidays for current year
                const currentYearHolidays = holidays.filter(holiday => {
                    const holidayYear = new Date(holiday.holiday_date).getFullYear();
                    return holidayYear === currentYear;
                });
                
                if (currentYearHolidays.length === 0) {
                    showNoHolidays();
                    return;
                }
                
                // Calculate stats
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                let upcomingCount = 0;
                let remainingCount = 0;
                
                currentYearHolidays.forEach(holiday => {
                    const holidayDate = new Date(holiday.holiday_date);
                    holidayDate.setHours(0, 0, 0, 0);
                    
                    if (holidayDate >= today) {
                        upcomingCount++;
                        remainingCount++;
                    }
                });
                
                $('#totalHolidays').text(currentYearHolidays.length);
                $('#upcomingHolidays').text(upcomingCount);
                $('#remainingHolidays').text(remainingCount);
                
                // Group holidays by month
                const holidaysByMonth = {};
                currentYearHolidays.forEach(holiday => {
                    const date = new Date(holiday.holiday_date);
                    const monthYear = date.toLocaleString('default', { month: 'long', year: 'numeric' });
                    
                    if (!holidaysByMonth[monthYear]) {
                        holidaysByMonth[monthYear] = [];
                    }
                    holidaysByMonth[monthYear].push(holiday);
                });
                
                // Render holidays
                let htmlContent = '';
                
                Object.keys(holidaysByMonth).forEach(monthYear => {
                    htmlContent += `
                        <div class="month-divider">
                            <i class="mdi mdi-calendar-month"></i>
                            ${monthYear}
                        </div>
                    `;
                    
                    holidaysByMonth[monthYear].forEach(holiday => {
                        const date = new Date(holiday.holiday_date);
                        const day = date.getDate();
                        const month = date.toLocaleString('default', { month: 'short' });
                        const weekday = date.toLocaleString('default', { weekday: 'long' });
                        const isPast = date < today;
                        
                        htmlContent += `
                            <div class="holiday-card d-flex ${isPast ? 'opacity-75' : ''}">
                                <div class="holiday-date">
                                    <div class="holiday-day">${day}</div>
                                    <div class="holiday-month">${month}</div>
                                </div>
                                <div class="holiday-info">
                                    <div class="holiday-title">${holiday.title}</div>
                                    ${holiday.description ? `<div class="holiday-description">${holiday.description}</div>` : ''}
                                    <span class="holiday-weekday">${weekday}</span>
                                </div>
                            </div>
                        `;
                    });
                });
                
                $('#holidaysList').html(htmlContent);
                $('#loadingMessage').hide();
                $('#holidaysList').fadeIn();
                
            } else {
                showNoHolidays();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to load holidays. Please try again later.');
        });
    
    function showNoHolidays() {
        $('#loadingMessage').hide();
        $('#holidaysList').hide();
        $('#noHolidaysMessage').show();
        $('#totalHolidays').text('0');
        $('#upcomingHolidays').text('0');
        $('#remainingHolidays').text('0');
    }
    
    function showError(message) {
        $('#loadingMessage').hide();
        $('#holidaysList').hide();
        $('#errorMessage').text(message).show();
    }
});
</script>

<?= $this->endSection(); ?>