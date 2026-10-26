@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
    <div class="content">
        <style>
            .hr-dashboard {
                display: grid;
                gap: 24px;
            }

            .hr-dashboard-head {
                display: flex;
                justify-content: flex-end;
                align-items: center;
            }

            .hr-filter-wrap {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                background: #fff;
                border: 1px solid #e8edf4;
                border-radius: 12px;
                /* padding: 8px 12px; */
                box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
            }

            .hr-filter-label {
                color: #4d6783;
                font-size: 13px;
                font-weight: 600;
                margin: 0;
            }

            .hr-filter-select {
                border: 1px solid #d8e2ef;
                border-radius: 8px;
                height: 34px;
                min-width: 140px;
                padding: 0 10px;
                font-size: 13px;
                font-weight: 600;
                color: #1b2850;
                background: #fff;
                outline: none;
            }

            .hr-panel {
                background: #fff;
                border: 1px solid #e8edf4;
                border-radius: 18px;
                box-shadow: 0 14px 35px rgba(15, 23, 42, 0.06);
                overflow: hidden;
            }

            .hr-panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 18px 22px;
                border-bottom: 1px solid #eef2f7;
            }

            .hr-panel-title {
                display: flex;
                align-items: center;
                gap: 10px;
                margin: 0;
                font-size: 18px;
                font-weight: 700;
                color: #1b2850;
            }

            .hr-panel-title i {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: rgba(255, 159, 67, 0.15);
                color: #ff8b29;
            }

            .hr-action-link {
                color: #ff8b29;
                font-weight: 600;
                font-size: 13px;
                text-decoration: none;
            }

            .hr-announcement-list {
                padding: 18px 22px 22px;
            }

            .hr-announcement-slider {
                position: relative;
            }

            .hr-announcement-card {
                position: relative;
                min-height: 188px;
                height: 188px;
                padding: 18px;
                border-radius: 16px;
                border: 1px solid #8fd0ff;
                background: linear-gradient(180deg, #dff2ff 0%, #c7e8ff 100%);
                display: flex;
                flex-direction: column;
                gap: 10px;
                overflow: hidden;
            }

            .hr-announcement-badge {
                position: absolute;
                top: 10px;
                right: 12px;
                padding: 4px 10px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.75);
                color: #2785c7;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 0.08em;
            }

            .hr-announcement-title {
                margin: 0;
                font-size: 15px;
                font-weight: 700;
                color: #12365b;
                line-height: 1.35;
                padding-right: 58px;
            }

            .hr-announcement-meta,
            .hr-announcement-text,
            .hr-announcement-link {
                color: #4d6783;
                font-size: 12px;
            }

            .hr-announcement-text {
                line-height: 1.55;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
                overflow: hidden;
                margin: 0;
                min-height: 56px;
            }

            .hr-announcement-link {
                margin-top: auto;
                text-decoration: none;
                font-weight: 600;
                color: #1b2850;
            }

            .hr-announcement-meta-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
            }

            .hr-announcement-icon {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
                background: rgba(255, 255, 255, 0.45);
                color: #2785c7;
                font-size: 14px;
            }

            .hr-announcement-card::after {
                content: '';
                position: absolute;
                top: -28px;
                right: -12px;
                width: 92px;
                height: 92px;
                border-radius: 50%;
                background: rgba(39, 133, 199, 0.12);
            }

            .hr-announcement-card > * {
                position: relative;
                z-index: 1;
            }

            .hr-announcement-count {
                min-width: 22px;
                height: 22px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #ffe8dd;
                color: #ff8b29;
                font-size: 11px;
                font-weight: 700;
            }

            .hr-announcement-headline {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .hr-announcement-list .owl-stage-outer {
                padding: 2px 0;
            }

            .hr-announcement-list .owl-dots {
                display: none;
            }

            .hr-announcement-list .owl-nav {
                display: none !important;
            }

            .hr-announcement-slider .owl-stage {
                display: flex;
            }

            .hr-announcement-slider .owl-item {
                display: flex;
            }

            .hr-announcement-slider .owl-item .hr-announcement-card {
                width: 100%;
            }

            .hr-stats-grid,
            .hr-bottom-grid {
                display: grid;
                gap: 18px;
            }

            .hr-stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .hr-bottom-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: stretch;
            }

            .hr-stat-card,
            .hr-info-card {
                background: #fff;
                border: 1px solid #e8edf4;
                border-radius: 18px;
                box-shadow: 0 14px 35px rgba(15, 23, 42, 0.05);
                padding: 20px;
                height: 100%;
            }

            .hr-stat-link {
                text-decoration: none;
                color: inherit;
                display: block;
                height: 100%;
            }

            .hr-stat-link .hr-stat-card {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .hr-stat-link:hover .hr-stat-card {
                transform: translateY(-2px);
                box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
            }

            .hr-stat-top,
            .hr-info-top,
            .hr-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            .hr-stat-label,
            .hr-info-title {
                margin: 0;
                color: #1b2850;
                font-weight: 700;
            }

            .hr-stat-label {
                font-size: 16px;
            }

            .hr-stat-value {
                margin: 18px 0 0;
                font-size: 34px;
                font-weight: 800;
                color: #0f172a;
                line-height: 1;
            }

            .hr-icon {
                width: 46px;
                height: 46px;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #f6f8fb;
                color: #ff8b29;
                font-size: 18px;
                flex-shrink: 0;
            }

            .hr-info-body {
                margin-top: 18px;
            }

            .hr-check-box {
                border: 1px solid #d8e2ef;
                border-radius: 12px;
                padding: 14px;
                background: #fbfdff;
            }

            .hr-date {
                color: #5c7088;
                font-size: 13px;
                margin-bottom: 10px;
            }

            .hr-pill {
                display: inline-flex;
                align-items: center;
                padding: 5px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
                color: #fff;
            }

            .hr-pill-success {
                background: #49b266;
            }

            .hr-pill-info {
                background: #4ca7d9;
            }

            .hr-divider {
                height: 1px;
                background: #e6edf5;
                margin: 14px 0;
            }

            .hr-progress-block {
                display: grid;
                gap: 14px;
            }

            .hr-progress-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-bottom: 6px;
                font-size: 13px;
                color: #42556b;
            }

            .hr-progress-track {
                height: 8px;
                border-radius: 999px;
                background: #e8eef5;
                overflow: hidden;
            }

            .hr-progress-fill {
                height: 100%;
                border-radius: 999px;
            }

            .hr-fill-worked {
                background: linear-gradient(90deg, #00a3ad, #0fc2c9);
            }

            .hr-fill-remaining {
                background: linear-gradient(90deg, #ff7b6b, #ff5f5f);
            }

            .hr-status-badge {
                padding: 6px 10px;
                border-radius: 8px;
                font-size: 11px;
                font-weight: 700;
                color: #fff;
                background: #49b266;
            }

            .hr-empty-state {
                min-height: 220px;
                display: grid;
                place-items: center;
                color: #708399;
                font-size: 15px;
                text-align: center;
            }

            .hr-muted {
                color: #708399;
                font-size: 13px;
            }

            .hr-staff-list {
                display: grid;
                gap: 14px;
                max-height: 320px;
                overflow-y: auto;
                padding-right: 6px;
            }

            .hr-staff-item {
                display: grid;
                grid-template-columns: 48px minmax(0, 1fr) 24px;
                align-items: start;
                gap: 12px;
                padding-bottom: 14px;
                border-bottom: 1px solid #e6edf5;
            }

            .hr-staff-item-clickable {
                cursor: pointer;
            }

            .hr-staff-item:last-child {
                border-bottom: 0;
                padding-bottom: 0;
            }

            .hr-staff-avatar {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                object-fit: cover;
            }

            .hr-staff-name {
                margin: 0 0 4px;
                font-size: 14px;
                font-weight: 700;
                color: #1b2850;
            }

            .hr-staff-line {
                font-size: 13px;
                color: #42556b;
                line-height: 1.5;
            }

            .hr-staff-line strong {
                color: #2f9e44;
            }

            .hr-staff-progress {
                margin-top: 8px;
                height: 6px;
                border-radius: 999px;
                background: #e8eef5;
                overflow: hidden;
            }

            .hr-staff-progress > span {
                display: block;
                height: 100%;
                background: linear-gradient(90deg, #00a3ad, #0fc2c9);
                border-radius: 999px;
            }

            .hr-staff-action {
                color: #39b54a;
                font-size: 20px;
                padding-top: 10px;
            }

            .hr-staff-action.absent {
                color: #ff8b29;
            }

            .hr-staff-empty {
                min-height: 220px;
                display: grid;
                place-items: center;
                text-align: center;
                color: #708399;
                font-size: 14px;
            }

            @media (max-width: 1199px) {
                .hr-stats-grid,
                .hr-bottom-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 767px) {
                .hr-panel-header,
                .hr-row,
                .hr-stat-top,
                .hr-info-top {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .hr-stats-grid,
                .hr-bottom-grid {
                    grid-template-columns: 1fr;
                }

                .hr-dashboard-head {
                    justify-content: stretch;
                }

                .hr-filter-wrap {
                    width: 100%;
                    justify-content: space-between;
                }

                .hr-filter-select {
                    min-width: 0;
                    width: 56%;
                }

                .hr-panel-header,
                .hr-announcement-list,
                .hr-stat-card,
                .hr-info-card {
                    padding-left: 16px;
                    padding-right: 16px;
                }

                .hr-stat-value {
                    font-size: 30px;
                }

            }
        </style>

        <div class="hr-dashboard">
            <section class="hr-dashboard-head">
                <div class="hr-filter-wrap">
                    {{-- <p class="hr-filter-label">Filter By</p> --}}
                    <select id="hrStatsFilter" class="hr-filter-select">
                        <option value="this_week">This Week</option>
                        <option value="this_month" selected>This Month</option>
                        <option value="this_year">This Year</option>
                    </select>
                </div>
            </section>

            {{-- <section class="hr-panel">
                <div class="hr-panel-header">
                    <div class="hr-announcement-headline">
                        <h3 class="hr-panel-title">
                            <i class="fa-solid fa-bullhorn"></i>
                            Latest Announcements
                        </h3>
                        <span class="hr-announcement-count" id="hrAnnouncementCount">0</span>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="hr-action-link">View All</a>
                </div>

                <div class="hr-announcement-list">
                    <div class="hr-announcement-slider owl-carousel" id="hrAnnouncementSlider"></div>
                    <div class="hr-empty-state" id="hrAnnouncementsEmpty" style="min-height: 140px;">
                        Loading announcements...
                    </div>
                </div>
            </section> --}}

            <section class="hr-stats-grid">
                <a href="{{ route('staff.list') }}" class="hr-stat-link">
                    <article class="hr-stat-card">
                        <div class="hr-stat-top">
                            <h4 class="hr-stat-label">All Staff</h4>
                            <span class="hr-icon"><i class="fa-solid fa-user-check"></i></span>
                        </div>
                        <p class="hr-stat-value" id="hrStaffCount">0</p>
                    </article>
                </a>

                <a href="{{ route('attendence.summary') }}" class="hr-stat-link">
                    <article class="hr-stat-card">
                        <div class="hr-stat-top">
                            <h4 class="hr-stat-label">All Attendances</h4>
                            <span class="hr-icon"><i class="fa-regular fa-calendar-check"></i></span>
                        </div>
                        <p class="hr-stat-value" id="hrAttendanceCount">0</p>
                    </article>
                </a>

                <a href="{{ route('leave.view') }}" class="hr-stat-link">
                    <article class="hr-stat-card">
                        <div class="hr-stat-top">
                            <h4 class="hr-stat-label">All Leaves</h4>
                            <span class="hr-icon"><i class="fa-solid fa-list-check"></i></span>
                        </div>
                        <p class="hr-stat-value" id="hrLeaveCount">0</p>
                    </article>
                </a>

            </section>

            <section class="hr-bottom-grid">
                <article class="hr-info-card">
                    <div class="hr-info-top">
                        <h4 class="hr-info-title" id="hrPresentTitle">Today's Presences Staff (0)</h4>
                        <a href="{{ route('attendence.summary') }}" class="hr-action-link">View All</a>
                    </div>

                    <div class="hr-info-body">
                        <div class="hr-staff-list" id="hrPresentStaffList">
                            <div class="hr-staff-empty">Loading present staff...</div>
                        </div>
                    </div>
                </article>

                <article class="hr-info-card">
                    <div class="hr-info-top">
                        <h4 class="hr-info-title" id="hrAbsentTitle">Today Absent Staff (0)</h4>
                        <span class="hr-icon"><i class="fa-regular fa-clock"></i></span>
                    </div>

                    <div class="hr-info-body">
                        <div class="hr-staff-list" id="hrAbsentStaffList">
                            <div class="hr-staff-empty">Loading absent staff...</div>
                        </div>
                    </div>
                </article>

            </section>
        </div>
    </div>

@endsection

@push('js')
    <script>
        $(document).ready(function() {
            const authToken = localStorage.getItem('authToken') || localStorage.getItem('token');
            const fallbackDate = "{{ now('Asia/Kolkata')->format('d M Y') }}";
            const leaveViewUrl = "{{ route('leave.view') }}";
            const $statsFilter = $('#hrStatsFilter');
            let currentRange = $statsFilter.val() || 'this_month';

            function escapeHtml(text) {
                return $('<div>').text(text || '').html();
            }

            function renderAnnouncements(items) {
                const $slider = $('#hrAnnouncementSlider');
                const $empty = $('#hrAnnouncementsEmpty');
                const itemsCount = Array.isArray(items) ? items.length : 0;

                $('#hrAnnouncementCount').text(itemsCount);

                if ($slider.hasClass('owl-loaded')) {
                    $slider.trigger('destroy.owl.carousel');
                    $slider.removeClass('owl-loaded owl-hidden');
                }

                $slider.empty();

                if (!itemsCount) {
                    $slider.hide();
                    $empty.show().text('No announcements available right now.');
                    return;
                }

                $empty.hide();
                $slider.show();

                items.forEach(function(item) {
                    $slider.append(`
                        <article class="hr-announcement-card">
                            <div class="hr-announcement-meta-row">
                                <span class="hr-announcement-icon"><i class="fa-solid fa-circle-info"></i></span>
                                <span class="hr-announcement-badge">INFO</span>
                            </div>
                            <h4 class="hr-announcement-title">${escapeHtml(item.title || 'Company Update')}</h4>
                            <div class="hr-announcement-meta">
                                <i class="fa-regular fa-calendar"></i>
                                ${escapeHtml(item.formatted_date || '')}
                            </div>
                            <p class="hr-announcement-text">${escapeHtml(item.message || 'No message available.')}</p>
                            <a href="${item.link || '#'}" class="hr-announcement-link">
                                <i class="fa-regular fa-eye"></i> Click to read
                            </a>
                        </article>
                    `);
                });

                $slider.owlCarousel({
                    loop: itemsCount > 3,
                    margin: 16,
                    nav: false,
                    dots: false,
                    autoplay: itemsCount > 1,
                    autoplayTimeout: 2800,
                    autoplayHoverPause: true,
                    smartSpeed: 700,
                    responsive: {
                        0: {
                            items: 1
                        },
                        768: {
                            items: 2
                        },
                        1200: {
                            items: 3
                        }
                    }
                });
            }

            function renderDashboard(data) {
                $('#hrStaffCount').text(data.stats?.staff_count ?? 0);
                $('#hrAttendanceCount').text(data.stats?.attendance_count ?? 0);
                $('#hrLeaveCount').text(data.stats?.leave_count ?? 0);
                renderPresentStaff(data.today_present_staff || []);
                renderAbsentStaff(data.today_absent_or_leave_staff || []);
                renderAnnouncements(data.announcements || []);
            }

            function setFilterLoadingState(isLoading) {
                $statsFilter.prop('disabled', isLoading);
            }

            function loadDashboard(rangeKey) {
                setFilterLoadingState(true);

                $.ajax({
                    url: "{{ url('/api/hr/dashboard-data') }}",
                    method: 'GET',
                    headers: {
                        Authorization: 'Bearer ' + authToken
                    },
                    data: {
                        range: rangeKey
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status && response.data) {
                            currentRange = response.data.stats?.filter_key || rangeKey;
                            $statsFilter.val(currentRange);
                            renderDashboard(response.data);
                        } else {
                            $('#hrAnnouncementsEmpty').text('Unable to load dashboard data.');
                        }
                    },
                    error: function() {
                        $('#hrAnnouncementsEmpty').text('Unable to load dashboard data.');
                    },
                    complete: function() {
                        setFilterLoadingState(false);
                    }
                });
            }

            function getWorkedPercent(timeText) {
                const parts = (timeText || '00:00:00').split(':').map(Number);
                const totalSeconds = ((parts[0] || 0) * 3600) + ((parts[1] || 0) * 60) + (parts[2] || 0);
                const standardSeconds = (8 * 3600) + (30 * 60);
                return Math.min(100, Math.round((totalSeconds / standardSeconds) * 100));
            }

            // ── Live working-time timer ───────────────────────────────────────
            let _workTimerInterval = null;
            const LUNCH_BREAK_SECS = 30 * 60; // 30 minutes

            function secsToHHMMSS(totalSecs) {
                totalSecs = Math.max(0, Math.floor(totalSecs));
                const h = Math.floor(totalSecs / 3600);
                const m = Math.floor((totalSecs % 3600) / 60);
                const s = totalSecs % 60;
                return String(h).padStart(2, '0') + ':' +
                       String(m).padStart(2, '0') + ':' +
                       String(s).padStart(2, '0');
            }

            function tickWorkTimers() {
                const nowSecs = Math.floor(Date.now() / 1000);
                document.querySelectorAll('.hr-live-timer').forEach(function(el) {
                    const checkInTs = parseInt(el.dataset.checkinTs, 10);
                    if (!checkInTs) return;
                    const elapsed  = Math.max(0, nowSecs - checkInTs);
                    // Deduct lunch break only after at least 30 min have elapsed
                    const lunch    = elapsed >= LUNCH_BREAK_SECS ? LUNCH_BREAK_SECS : 0;
                    const worked   = Math.max(0, elapsed - lunch);
                    el.textContent = secsToHHMMSS(worked);
                    // Also update progress bar
                    const progressEl = document.getElementById('progress-' + el.dataset.staffId);
                    if (progressEl) {
                        const standardSecs = 8.5 * 3600; // 8h 30m
                        const pct = Math.min(100, Math.round((worked / standardSecs) * 100));
                        progressEl.style.width = pct + '%';
                    }
                });
            }

            function startWorkTimers() {
                if (_workTimerInterval) clearInterval(_workTimerInterval);
                // Run immediately then every second
                tickWorkTimers();
                _workTimerInterval = setInterval(tickWorkTimers, 1000);
            }
            // ── End live timer ────────────────────────────────────────────────

            function renderPresentStaff(items) {
                const $list = $('#hrPresentStaffList');
                $('#hrPresentTitle').text(`Today's Presences Staff (${items.length})`);
                $list.empty();

                if (!items.length) {
                    $list.html('<div class="hr-staff-empty">No present staff found for today.</div>');
                    return;
                }

                items.forEach(function(item) {
                    const isLive      = !item.is_checked_out && item.check_in_timestamp;
                    const initialTime = escapeHtml(item.working_hours || '00:00:00');
                    const progress    = getWorkedPercent(item.working_hours);

                    // Build the working-time element — live timer if still checked in
                    const timerAttrs = isLive
                        ? `class="hr-live-timer" data-checkin-ts="${item.check_in_timestamp}" data-staff-id="${item.id}"`
                        : '';

                    $list.append(`
                        <div class="hr-staff-item">
                            <img src="${escapeHtml(item.profile_image_url || '')}" alt="${escapeHtml(item.name || 'Staff')}" class="hr-staff-avatar">
                            <div>
                                <h5 class="hr-staff-name">${escapeHtml(item.name || 'Staff')}</h5>
                                <div class="hr-staff-line">Check-in: ${escapeHtml(item.check_in_time || '--')}</div>
                                <div class="hr-staff-line"><strong>Working: <span ${timerAttrs}>${initialTime}</span></strong></div>
                                <div class="hr-staff-progress"><span id="progress-${item.id}" style="width: ${progress}%;"></span></div>
                            </div>
                            <div class="hr-staff-action">
                                <i class="fa-solid fa-right-to-bracket"></i>
                            </div>
                        </div>
                    `);
                });

                // Kick off / restart the live ticker after DOM is updated
                startWorkTimers();
            }

            function renderAbsentStaff(items) {
                const $list = $('#hrAbsentStaffList');
                $('#hrAbsentTitle').text(`Today Absent Or Leave (${items.length})`);
                $list.empty();

                if (!items.length) {
                    $list.html('<div class="hr-staff-empty">No absent or leave staff found for today.</div>');
                    return;
                }

                items.forEach(function(item) {
                    $list.append(`
                        <div class="hr-staff-item hr-staff-item-clickable" data-url="${leaveViewUrl}">
                            <img src="${escapeHtml(item.profile_image_url || '')}" alt="${escapeHtml(item.name || 'Staff')}" class="hr-staff-avatar">
                            <div>
                                <h5 class="hr-staff-name">${escapeHtml(item.name || 'Staff')}</h5>
                                <div class="hr-staff-line">${escapeHtml(item.date_label || fallbackDate)}</div>
                                <div class="hr-staff-line"><strong>${escapeHtml(item.type || 'Absent')}</strong></div>
                            </div>
                            <div class="hr-staff-action absent">
                                <i class="fa-regular fa-calendar-check"></i>
                            </div>
                        </div>
                    `);
                });
            }

            $(document).on('click', '.hr-staff-item-clickable', function() {
                const url = $(this).data('url') || leaveViewUrl;
                window.location.href = url;
            });

            if (!authToken) {
                $('#hrAnnouncementsEmpty').text('Login token not found.');
                return;
            }

            $statsFilter.on('change', function() {
                loadDashboard($(this).val() || 'this_month');
            });

            loadDashboard(currentRange);
        });
    </script>
@endpush
