@extends('layout.app')

@section('title', 'Holiday Calendar')

@section('content')
    <style>
        .holiday-calendar-wrap {
            padding: 6px 0 18px;
        }

        .holiday-calendar-shell {
            background: #f6f7fb;
            border: 1px solid #eceff5;
            border-radius: 14px;
            padding: 22px;
        }

        .holiday-hero {
            border-radius: 12px;
            padding: 22px 18px;
            text-align: center;
            color: #fff;
            background: linear-gradient(120deg, #dd975a 0%, #ec9b62 100%);
            box-shadow: 0 10px 24px rgba(46, 65, 124, 0.2);
        }

        .holiday-hero h2 {
            margin: 0;
            font-size: 38px;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: #fff;
        }

        .holiday-hero p {
            margin: 8px 0 0;
            font-size: 16px;
            color: rgba(255, 255, 255, 0.92);
        }

        .holiday-stats {
            margin-top: 18px;
        }

        .holiday-stat-card {
            background: #fff;
            border: 1px solid #edf0f6;
            border-radius: 10px;
            padding: 18px 12px;
            text-align: center;
            height: 100%;
            box-shadow: 0 5px 16px rgba(30, 50, 90, 0.08);
        }

        .holiday-stat-card h5 {
            margin: 0;
            font-size: 40px;
            line-height: 1;
            font-weight: 700;
            color: #FF9F43;
        }

        .holiday-stat-card small {
            display: inline-block;
            margin-top: 6px;
            color: #526072;
            font-size: 16px;
        }

        .holiday-month-section {
            margin-top: 22px;
        }

        .holiday-month-title {
            background: #dfe4ed;
            color: #2b3d52;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .holiday-month-title i {
            margin-right: 8px;
        }

        .holiday-item {
            border-left: 3px solid #ff6d72;
            background: #fff;
            border-radius: 10px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 8px 20px rgba(20, 36, 68, 0.08);
            margin-bottom: 12px;
        }

        .holiday-date-box {
            width: 88px;
            min-width: 88px;
            border-radius: 8px;
            background: linear-gradient(180deg, #d2762b 0%, #dd6b71 100%);
            color: #fff;
            text-align: center;
            padding: 10px 8px;
            line-height: 1.1;
        }

        .holiday-date-box .day {
            display: block;
            font-size: 44px;
            font-weight: 700;
        }

        .holiday-date-box .mon {
            display: block;
            font-size: 26px;
            font-weight: 700;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .holiday-info h5 {
            margin: 0;
            font-size: 34px;
            font-weight: 700;
            color: #4a5667;
        }

        .holiday-info p {
            margin: 4px 0 8px;
            font-size: 25px;
            color: #8a95a5;
        }

        .holiday-weekday {
            display: inline-block;
            background: #d7e9fb;
            color: #3f84cc;
            font-size: 18px;
            font-weight: 600;
            border-radius: 999px;
            padding: 4px 12px;
        }

        .holiday-empty {
            background: #fff;
            border: 1px dashed #cdd5e3;
            border-radius: 10px;
            color: #738298;
            font-size: 16px;
            text-align: center;
            padding: 20px;
            margin-top: 18px;
        }

        @media (max-width: 991px) {
            .holiday-hero h2 {
                font-size: 30px;
            }

            .holiday-hero p {
                font-size: 14px;
            }

            .holiday-stat-card h5 {
                font-size: 32px;
            }

            .holiday-stat-card small {
                font-size: 14px;
            }

            .holiday-month-title {
                font-size: 22px;
            }

            .holiday-date-box {
                width: 76px;
                min-width: 76px;
            }

            .holiday-date-box .day {
                font-size: 36px;
            }

            .holiday-date-box .mon {
                font-size: 20px;
            }

            .holiday-info h5 {
                font-size: 24px;
            }

            .holiday-info p {
                font-size: 18px;
            }

            .holiday-weekday {
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .holiday-calendar-shell {
                padding: 14px;
            }

            .holiday-item {
                align-items: flex-start;
                gap: 12px;
            }

            .holiday-date-box {
                width: 64px;
                min-width: 64px;
                padding: 8px 6px;
            }

            .holiday-date-box .day {
                font-size: 28px;
            }

            .holiday-date-box .mon {
                font-size: 15px;
            }

            .holiday-info h5 {
                font-size: 20px;
            }

            .holiday-info p {
                font-size: 14px;
            }
        }
         .btn-secondary{
            background-color: #1B2850;
            color: #fff;
        }
    </style>

    <div class="content">
        <div class="holiday-calendar-wrap">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('holidays.index') }}" class="btn btn-secondary">Manage Holidays</a>
            </div>

            <div class="holiday-calendar-shell">
                <div class="holiday-hero">
                    <h2><i class="fa fa-calendar-check-o"></i> Holiday Calendar <span id="calendarYear"></span></h2>
                    <p>Company observed holidays for the year</p>
                </div>

                <div class="row holiday-stats g-3">
                    <div class="col-md-4">
                        <div class="holiday-stat-card">
                            <h5 id="totalCount">0</h5>
                            <small>Total Holidays</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="holiday-stat-card">
                            <h5 id="upcomingCount">0</h5>
                            <small>Upcoming</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="holiday-stat-card">
                            <h5 id="remainingCount">0</h5>
                            <small>Remaining</small>
                        </div>
                    </div>
                </div>

                <div id="holidayMonths"></div>
                <div id="emptyState" class="holiday-empty d-none">No holidays found for this year.</div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const token = (typeof window.getAuthToken === 'function'
                ? window.getAuthToken()
                : (localStorage.getItem('authToken') || localStorage.getItem('token') || ''));
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const year = new Date().getFullYear();
            $('#calendarYear').text(year);

            if (!token) {
                Swal.fire('Unauthorized', 'Please login again to continue.', 'warning');
                return;
            }

            $.ajax({
                url: '/api/get_holidays',
                method: 'GET',
                data: selectedSubAdminId ? { selectedSubAdminId } : {},
                headers: {
                    Authorization: `Bearer ${token}`
                }
            }).done((response) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const allHolidays = response.data || [];
                const list = allHolidays
                    .map((holiday) => {
                        const date = parseHolidayDate(holiday.holiday_date);
                        return {
                            ...holiday,
                            _date: date
                        };
                    })
                    .filter((holiday) => holiday._date && holiday._date.getFullYear() === year)
                    .sort((a, b) => a._date - b._date);

                $('#totalCount').text(list.length);

                const upcoming = list.filter((holiday) => holiday._date >= today).length;
                $('#upcomingCount').text(upcoming);
                $('#remainingCount').text(upcoming);

                if (!list.length) {
                    $('#emptyState').removeClass('d-none');
                    return;
                }

                const groupedByMonth = list.reduce((acc, holiday) => {
                    const monthKey = `${holiday._date.getFullYear()}-${holiday._date.getMonth()}`;
                    if (!acc[monthKey]) {
                        acc[monthKey] = [];
                    }
                    acc[monthKey].push(holiday);
                    return acc;
                }, {});

                const monthHtml = Object.keys(groupedByMonth).map((key) => {
                    const monthItems = groupedByMonth[key];
                    const monthLabel = monthItems[0]._date.toLocaleDateString('en-US', {
                        month: 'long',
                        year: 'numeric'
                    });

                    const cards = monthItems.map((holiday) => {
                        const day = holiday._date.toLocaleDateString('en-US', {
                            day: '2-digit'
                        });
                        const mon = holiday._date.toLocaleDateString('en-US', {
                            month: 'short'
                        }).toUpperCase();
                        const weekDay = holiday._date.toLocaleDateString('en-US', {
                            weekday: 'long'
                        });
                        const title = escapeHtml(holiday.title || '-');
                        const description = escapeHtml(holiday.description || '');

                        return `
                            <div class="holiday-item">
                                <div class="holiday-date-box">
                                    <span class="day">${day}</span>
                                    <span class="mon">${mon}</span>
                                </div>
                                <div class="holiday-info">
                                    <h5>${title}</h5>
                                    <p>${description || 'Company holiday'}</p>
                                    <span class="holiday-weekday">${weekDay}</span>
                                </div>
                            </div>
                        `;
                    }).join('');

                    return `
                        <div class="holiday-month-section">
                            <div class="holiday-month-title"><i class="fa fa-calendar"></i> ${monthLabel}</div>
                            ${cards}
                        </div>
                    `;
                }).join('');

                $('#holidayMonths').html(monthHtml);
            }).fail((xhr) => {
                const message = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Failed to load holidays.';
                Swal.fire('Error', message, 'error');
            });

            function parseHolidayDate(dateString) {
                if (!dateString) {
                    return null;
                }
                const date = new Date(`${dateString}T00:00:00`);
                if (Number.isNaN(date.getTime())) {
                    return null;
                }
                date.setHours(0, 0, 0, 0);
                return date;
            }

            function escapeHtml(text) {
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }
        });
    </script>
@endpush
