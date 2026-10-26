@extends('layout.app')
@section('title', 'HR Dashboard')

@section('content')
<style>
    :root {
        --primary-orange: #FF6B35;
        --secondary-orange: #FF9F43;
        --light-orange: #FFF5EC;
        --text-muted: #888;
        --success-green: #28a745;
    }

    .dashboard-container {
        padding: 24px;
        background-color: #f9f9f9;
        min-height: 100vh;
    }

    .stat-card {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        position: relative;
        margin-bottom: 24px;
        border: 1px solid #f0f0f0;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .stat-card .stat-title {
        font-size: 14px;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
    }

    .stat-card .stat-value {
        font-size: 32px;
        font-weight: 800;
        color: #000;
    }

    .stat-card .stat-icon-wrapper {
        position: absolute;
        bottom: 20px;
        right: 20px;
        width: 44px;
        height: 44px;
        background: var(--light-orange);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card .stat-icon-wrapper i {
        color: var(--primary-orange);
        font-size: 22px;
    }

    .panel-card {
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        border: 1px solid #f0f0f0;
        height: 100%;
        overflow: hidden;
    }

    .panel-header {
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .panel-title {
        font-size: 14px;
        font-weight: 800;
        color: #333;
        margin: 0;
    }

    .btn-view-all {
        background-color: #e65b2e;
        border: none;
        color: white;
        padding: 5px 14px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .list-container {
        max-height: 400px;
        overflow-y: auto;
    }

    .list-item {
        padding: 12px 20px;
        border-bottom: 1px solid #f3f3f3;
        display: flex;
        align-items: center;
    }

    .list-item:last-child {
        border-bottom: none;
    }

    .avatar-wrapper {
        position: relative;
        margin-right: 12px;
    }

    .avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #eee;
    }

    .item-details {
        flex-grow: 1;
    }

    .item-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #333;
        margin-bottom: 1px;
    }

    .item-info {
        font-size: 12px;
        color: #777;
        margin: 0;
        line-height: normal;
    }

    .working-label {
        color: var(--success-green);
        font-weight: 700;
    }

    .progress-container {
        margin-top: 6px;
        height: 5px;
        background-color: #f0f0f0;
        border-radius: 10px;
        width: 85%;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: #f89d13;
        border-radius: 10px;
    }

    .item-action-icon {
        color: var(--success-green);
        font-size: 20px;
        margin-left: 10px;
    }

    .leave-icon {
        color: var(--primary-orange);
        font-size: 18px;
        background: #fff5ec;
        padding: 6px;
        border-radius: 4px;
        border: 1px solid #ffe8d6;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 80px 20px;
        color: #999;
    }

    .empty-icon {
        font-size: 30px;
        margin-bottom: 20px;
    }

    .panel-body-with-scroll {
        max-height: 450px;
        overflow-y: auto;
    }

    /* Custom Scrollbar */
    .panel-body-with-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .panel-body-with-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .panel-body-with-scroll::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
</style>

<div class="content dashboard-container">
    <!-- Stat Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">All Employees</div>
                <div class="stat-value">34</div>
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-account-group-outline"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">All Leaves</div>
                <div class="stat-value">14</div>
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-calendar-text-outline"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">All Attendance</div>
                <div class="stat-value">331</div>
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-account-check-outline"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">All Tasks</div>
                <div class="stat-value">0</div>
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-clipboard-check-outline"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Panels -->
    <div class="row">
        <!-- Today's Present -->
        <div class="col-md-4">
            <div class="panel-card">
                <div class="panel-header">
                    <h5 class="panel-title">Today's Present (32)</h5>
                    <button class="btn btn-view-all">View All</button>
                </div>
                <div class="panel-body panel-body-with-scroll p-0">
                    <div class="list-item">
                        <div class="avatar-wrapper">
                            <img src="https://i.pravatar.cc/150?u=anjali" class="avatar" alt="User">
                        </div>
                        <div class="item-details">
                            <div class="item-name">Anjali Varlekar</div>
                            <p class="item-info">Check-in: <span style="font-weight: 500; color: #555;">09:31:04</span></p>
                            <p class="item-info">Working: <span class="working-label">01:54:24</span></p>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 70%;"></div>
                            </div>
                        </div>
                        <i class="mdi mdi-login item-action-icon"></i>
                    </div>

                    <div class="list-item">
                        <div class="avatar-wrapper">
                            <img src="https://i.pravatar.cc/150?u=haresh" class="avatar" alt="User">
                        </div>
                        <div class="item-details">
                            <div class="item-name">Haresh Mangukiya</div>
                            <p class="item-info">Check-in: <span style="font-weight: 500; color: #555;">09:31:35</span></p>
                            <p class="item-info">Working: <span class="working-label">01:53:53</span></p>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 65%;"></div>
                            </div>
                        </div>
                        <i class="mdi mdi-login item-action-icon"></i>
                    </div>

                    <div class="list-item">
                        <div class="avatar-wrapper">
                            <img src="https://i.pravatar.cc/150?u=vatsal" class="avatar" alt="User">
                        </div>
                        <div class="item-details">
                            <div class="item-name">Vatsal Navdiwala</div>
                            <p class="item-info">Check-in: <span style="font-weight: 500; color: #555;">09:31:56</span></p>
                            <p class="item-info">Working: <span class="working-label">01:53:32</span></p>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 60%;"></div>
                            </div>
                        </div>
                        <i class="mdi mdi-login item-action-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Absent Or Leave -->
        <div class="col-md-4">
            <div class="panel-card">
                <div class="panel-header">
                    <h5 class="panel-title">Today's Absent Or Leave (2)</h5>
                    <button class="btn btn-view-all">View All</button>
                </div>
                <div class="panel-body panel-body-with-scroll p-0">
                    <div class="list-item">
                        <div class="avatar-wrapper">
                            <img src="https://i.pravatar.cc/150?u=bhavik" class="avatar" alt="User">
                        </div>
                        <div class="item-details">
                            <div class="item-name">Bhavik Vedchhawala</div>
                            <p class="item-info">2026-04-14 to 2026-04-14</p>
                        </div>
                        <i class="mdi mdi-calendar-blank-outline leave-icon"></i>
                    </div>

                    <div class="list-item">
                        <div class="avatar-wrapper">
                            <img src="https://i.pravatar.cc/150?u=aangi" class="avatar" alt="User">
                        </div>
                        <div class="item-details">
                            <div class="item-name">Aangi Brahmakshatriya</div>
                            <p class="item-info">2026-04-14 to 2026-04-14</p>
                        </div>
                        <i class="mdi mdi-calendar-blank-outline leave-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Week Birthday -->
        <div class="col-md-4">
            <div class="panel-card">
                <div class="panel-header">
                    <h5 class="panel-title">This Week Birthday (0) 🎂</h5>
                    <button class="btn btn-view-all">View All</button>
                </div>
                <div class="panel-body">
                    <div class="empty-state">
                        <p style="font-size: 14px; color: #666; font-weight: 500;">No birthdays this week</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
