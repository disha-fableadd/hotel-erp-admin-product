@php
$settings = App\Models\Setting::first();
$admin = App\Models\User::where('role', 'admin')->first();
@endphp
<style>
    .select2-container--default .select2-selection--single .select2-selection__clear { display: none; }

    .web_button { width: 25px; height: 24px; cursor: pointer; display: flex; align-items: center; }
    .bar-icon span { display: block; height: 3px; width: 100%; background-color: #333; margin: 3px 0; transition: all 0.3s ease; pointer-events: none; }
    .search-view { width: 465px; }

    @media (max-width: 991px) { .web_button { display: none; } }
    @media (min-width: 768px) and (max-width: 1024px) {
        .web_button { display: flex !important; z-index: 1000; position: relative; }
        #toggle_btn1 { pointer-events: auto !important; cursor: pointer !important; }
    }
    @media screen and (width: 1024px) { .web_button { display: flex !important; } .mobile_btn { display: none !important; } }
    @media (max-width: 983px) and (min-width: 575px) { .logo-view { float: left; margin-left: 3rem; } .search-view { width: 366px; } }

    div#subBranchContainer { width: 200px !important; }

    /* Mobile Bottom Navigation */
    .mobile-bottom-nav {    
        position: fixed; bottom: 0; left: 0; width: 100%;
        background-color: #ff9f43; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        display: none; justify-content: space-around; align-items: center;
        padding: 8px 0; z-index: 1050;
    }
    .mobile-bottom-nav .nav-item {
        text-align: center; color: #fff; text-decoration: none; flex: 1;
        display: flex; flex-direction: column; align-items: center; transition: color 0.3s;
    }
    .mobile-bottom-nav .nav-item i { font-size: 18px; margin-bottom: 4px; }
    .mobile-bottom-nav .nav-item span { font-size: 11px; font-weight: 500; }
    .mobile-bottom-nav .nav-item.active { color: #1b2850; }

    /* Notification Wrapper */
    .notification-wrapper { position: relative; }
    .notification-bell { font-size: 20px; color: #1b2850; transition: all 0.3s ease; }
    .notification-bell:hover { color: #ff9f43; }
    .notification-badge {
        position: absolute; top: 12px; right: 4px;
        background: #ff3b30; color: #fff; font-size: 10px;
        padding: 0 5px; border-radius: 50px; min-width: 18px;
        height: 18px; display: inline-flex; align-items: center;
        justify-content: center; text-align: center; font-weight: bold;
        animation: pulse 2s infinite; line-height: 1; box-sizing: border-box;
    }
    
    /* Platform Badges */
    .platform-badge {
        position: absolute; top: -6px; right: -6px;
        background: #DC3545; color: #fff; font-size: 10px; font-weight: bold;
        padding: 0 4px; border-radius: 50px; min-width: 18px; height: 18px;
        display: inline-flex; align-items: center; justify-content: center;
        text-align: center; border: 2px solid #fff; box-sizing: border-box;
        line-height: 1; z-index: 10;
    }
    .platform-badge.pulse { animation: pulse 2s infinite; }
    .notification-dropdown {
        position: absolute; top: 45px; right: 0; width: 350px;
        background: #ffffff; border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15); display: none;
        overflow: hidden; z-index: 9999; border: 1px solid #eaeaea;
    }
    .notification-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 15px; font-weight: 600; border-bottom: 1px solid #eaeaea; background: #fff;
    }
    .notification-header a { font-size: 12px; color: #ff9f43; text-decoration: none; font-weight: 500; }
    .notification-body { max-height: 380px; overflow-y: auto; background: #fff; }
    .empty-notification { padding: 40px 20px; text-align: center; color: #8c8c8c; font-size: 14px; }
    .empty-notification i { font-size: 40px; margin-bottom: 10px; display: block; color: #ddd; }
    .notification-item {
        padding: 12px 15px; background: #fff; border-bottom: 1px solid #f0f0f0;
        cursor: pointer; transition: 0.2s; position: relative;
    }
    .notification-item:hover { background: #fef9f0; }
    .notification-item.unread-notification { background: #fff9f0; border-left: 3px solid #ff9f43; }
    .notification-dot { width: 8px; height: 8px; background-color: #ff3b30; border-radius: 50%; position: absolute; top: 15px; right: 15px; }
    .notification-title { font-size: 14px; font-weight: 600; color: #1b2850; display: block; margin-bottom: 4px; }
    .notification-message { font-size: 12px; color: #6c757d; display: block; margin-bottom: 6px; line-height: 1.4; }
    .notification-time { font-size: 11px; color: #adb5bd; }
    .notification-time i { font-size: 10px; margin-right: 3px; }
    .notification-footer { padding: 10px; border-top: 1px solid #eaeaea; text-align: center; background: #fff; position: sticky; bottom: 0; }
    .notification-footer a { color: #ff9f43; font-size: 12px; font-weight: 500; text-decoration: none; }

    @keyframes pulse {
        0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); }
    }

    @media (max-width: 991px) {
        .mobile-bottom-nav { display: flex; }
        body { padding-bottom: 60px !important; }
        .sidebar { bottom: 60px !important; }
        .sidebar-inner, .slimScrollDiv { height: calc(100vh - 120px) !important; }
        .notification-dropdown { width: 320px; right: -10px; }
    }
    .mobile-header-right {
        display: none !important;
    }
    @media (max-width: 575px) {
        .mobile-header-right {
            display: flex !important;
            position: absolute;
            right: 0;
            top: 0;
            height: 60px;
            align-items: center;
            justify-content: flex-end;
            gap: 20px;
            padding-right: 15px;
            z-index: 1000;
        }
        .nav.user-menu { display: none !important; }
        .header { min-height: 60px; padding: 0; }
        .header .header-left { position: absolute !important; left: 56px !important; right: auto !important; width: auto !important; height: 60px; padding: 0 6px !important; border-right: 0 !important; display: flex; align-items: center; justify-content: flex-start; z-index: 3; }
        .header .header-left .logo { width: auto !important; margin: 0 !important; text-align: left !important; display: inline-flex; align-items: center; }
        .header .header-left .logo .logo-view { max-width: 86px !important; margin: 0 !important; }

        .header .mobile-user-menu {
            position: relative !important;
            top: auto !important;
            right: auto !important;
            width: auto !important;
            height: auto !important;
            line-height: 1 !important;
            padding: 0 !important;
            display: block !important;
        }
    }
</style>
@php
$user = auth()->user();
@endphp

<div class="header">

    <div class="header-left active">
        <a href="{{ route('auth.dashboard') }}" class="logo">
            <img src="{{ !empty($settings) && !empty($settings->logo) ? tenant_image_url($settings->logo) : 'https://fableadtechnolabs.com/static/media/250x150%20(1).b3f5a4db48c7770366ef.webp' }}"
                alt="Logo" style="max-width: 80% !important;" class="logo-view"
                onerror="this.onerror=null; this.src='https://fableadtechnolabs.com/static/media/250x150%20(1).b3f5a4db48c7770366ef.webp';">
        </a>
        <a href="{{ route('auth.dashboard') }}" class="logo-small">
            <img src="{{ !empty($settings) && !empty($settings->favicon) ? tenant_image_url($settings->favicon) : 'https://fableadtechnolabs.com/favicon-192x192.webp' }}"
                alt="Logo"
                onerror="this.onerror=null; this.src='https://fableadtechnolabs.com/favicon-192x192.webp';">
        </a>

        <!-- <a id="toggle_btn" href="javascript:void(0);">
        </a> -->
        <div id="toggle_btn1" class="web_button">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </div>
    </div>

    <a id="mobile_btn" class="mobile_btn" href="#sidebar">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <ul class="nav user-menu">
        <div class="d-flex align-items-center header-search-container">
            <!-- Platform Orders Buttons -->
            <button id="zomatoHeaderBtn" class="btn btn-sm d-flex align-items-center justify-content-center platform-btn me-2 position-relative" onclick="openPlatformModal('Zomato')" style="display: none !important; height: 38px; background-color: #E23744; color: white; border: none; border-radius: 5px; padding: 0 15px; font-weight: 500; font-size: 14px; gap: 8px; box-shadow: 0 2px 6px rgba(226, 55, 68, 0.2);">
                <img src="{{ env('ImagePath').'/admin/assets/img/product/zomato.png' }}" alt="Zomato" style="width: 20px; height: 20px; border-radius: 4px; object-fit: cover;">
                <span class="d-none d-md-inline">Zomato</span>
                <span class="platform-badge d-none" id="zomatoBadgeCount">0</span>
            </button>
            <button id="swiggyHeaderBtn" class="btn btn-sm d-flex align-items-center justify-content-center platform-btn me-3 position-relative" onclick="openPlatformModal('Swiggy')" style="display: none !important; height: 38px; background-color: #FC8019; color: white; border: none; border-radius: 5px; padding: 0 15px; font-weight: 500; font-size: 14px; gap: 8px; box-shadow: 0 2px 6px rgba(252, 128, 25, 0.2);">
                <img src="{{ env('ImagePath').'/admin/assets/img/product/swiggy.jpg' }}" alt="Swiggy" style="width: 20px; height: 20px; border-radius: 4px; object-fit: cover;">
                <span class="d-none d-md-inline">Swiggy</span>
                <span class="platform-badge d-none" id="swiggyBadgeCount">0</span>
            </button>

            {{-- erp menu button --}}
            @php
                $hasErpAccess = app('hasPermission')(1, 'view') || app('hasPermission')(1, 'add')
                            || app('hasPermission')(2, 'view')
                            || app('hasPermission')(3, 'view')
                            || app('hasPermission')(17, 'view')
                            || app('hasPermission')(9, 'view')
                            || app('hasPermission')(10, 'view')
                            || app('hasPermission')(8, 'view');
            @endphp
            @if($hasErpAccess)
            <div class="dropdown me-3">
                <button class="btn btn-sm d-flex align-items-center justify-content-center" type="button" id="erpDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="height: 38px; background-color: #ff9f43; color: white; border: none; border-radius: 5px; padding: 0 15px; font-weight: 500; font-size: 14px; gap: 8px; box-shadow: 0 2px 6px rgba(255, 159, 67, 0.2);">
                    <i class="fas fa-industry"></i> ERP <i class="fas fa-chevron-down ms-1" style="font-size: 12px;"></i>
                </button>
                <ul class="dropdown-menu hr-dropdown-menu" aria-labelledby="erpDropdown">
                    @if(app('hasPermission')(1, 'view') || app('hasPermission')(1, 'add'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('product.list') }}">
                            <i class="fas fa-cubes"></i> Products
                        </a></li>
                    @endif
                    @if(app('hasPermission')(2, 'view') || app('hasPermission')(2, 'add'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('sales.list') }}">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a></li>
                    @endif
                    @if(app('hasPermission')(3, 'view') || app('hasPermission')(3, 'add'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('purchase.lists') }}">
                            <i class="fas fa-shopping-bag"></i> Purchases
                        </a></li>
                    @endif
                    @if(app('hasPermission')(17, 'view'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('inventory.list') }}">
                            <i class="fa fa-book"></i> Manage Inventory
                        </a></li>
                    @endif
                    @if(app('hasPermission')(9, 'view'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('customer.list') }}">
                            <i class="fas fa-th-large"></i> Tables
                        </a></li>
                    @endif
                    @if(app('hasPermission')(10, 'view'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('vendor.list') }}">
                            <i class="fas fa-users"></i> Vendors
                        </a></li>
                    @endif
                    @if(app('hasPermission')(8, 'view'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('category.list') }}">
                            <i class="fas fa-tags"></i> Category
                        </a></li>
                    @endif
                </ul>
            </div>
            @endif

            @if(in_array($user->role, ['admin']))
            <div class="me-3" id="subBranchContainer" style="display: none;">
                <div class="d-flex align-items-center">
                    <select id="subBrandSelect" class="form-select form-select-sm" style="width: 300px;" data-main-branch-id="{{ $user->branch_id ?? $user->id }}">

                    </select>
                    <div id="currentSelection" class="ms-2 text-muted d-none" style="font-size: 12px;"></div>
                </div>
            </div>
            @endif

            <!-- HR Dropdown -->
            @php
                $hasHrAccess = app('hasPermission')(11, 'view') || app('hasPermission')(26, 'view') || app('hasPermission')(28, 'view') || app('hasPermission')(29, 'view');
            @endphp
            @if($hasHrAccess)
            <style>
                .hr-dropdown-item {
                    padding: 10px 16px;
                    font-size: 14px;
                    font-weight: 500;
                    color: #4f5d77;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    transition: all 0.2s ease-in-out;
                    border-radius: 6px;
                    margin: 2px 8px;
                    width: calc(100% - 16px);
                }
                .hr-dropdown-item i {
                    font-size: 16px;
                    color: #ff9f43;
                    width: 20px;
                    text-align: center;
                    transition: all 0.2s ease-in-out;
                }
                .hr-dropdown-item:hover, .hr-dropdown-item:focus {
                    background-color: #fff4e6;
                    color: #ff9f43;
                }
                .hr-dropdown-item:hover i {
                    transform: scale(1.1);
                }
                .hr-dropdown-menu {
                    border-radius: 12px;
                    padding: 8px 0;
                    min-width: 190px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
                    border: 1px solid rgba(0,0,0,0.05) !important;
                    margin-top: 8px !important;
                }
            </style>
            <!-- <div class="dropdown me-3">
                <button class="btn btn-sm d-flex align-items-center justify-content-center" type="button" id="hrDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="height: 38px; background-color: #ff9f43; color: white; border: none; border-radius: 5px; padding: 0 15px; font-weight: 500; font-size: 14px; gap: 8px; box-shadow: 0 2px 6px rgba(255, 159, 67, 0.2);">
                    <i class="fa fa-book"></i> HR <i class="fas fa-chevron-down ms-1" style="font-size: 12px;"></i>
                </button>
                <ul class="dropdown-menu hr-dropdown-menu" aria-labelledby="hrDropdown">
                    @if(app('hasPermission')(26, 'view') || app('hasPermission')(26, 'add'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('attendence.summary') }}">
                            <i class="fas fa-calendar-check"></i> Attendance
                        </a></li>
                    @endif
                    @if(app('hasPermission')(29, 'view') || app('hasPermission')(29, 'add'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('payroll.list') }}">
                            <i class="fas fa-file-invoice-dollar"></i> Payroll
                        </a></li>
                    @endif
                    @if(app('hasPermission')(11, 'view'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('staff.list') }}">
                            <i class="fas fa-users"></i> Staff
                        </a></li>
                    @endif
                    @if(app('hasPermission')(28, 'view') || app('hasPermission')(28, 'add'))
                        <li><a class="dropdown-item hr-dropdown-item" href="{{ route('leave.request') }}">
                            <i class="fas fa-plane-departure"></i> Leaves
                        </a></li>
                    @endif
                </ul>
            </div> -->
            @endif
            
            
            @php
                $hasHrAccess = app('hasPermission')(11, 'view') || app('hasPermission')(26, 'view') || app('hasPermission')(28, 'view') || app('hasPermission')(29, 'view');
            @endphp
            {{--
            <!-- Search Field Container -->
            <div class="header-search d-flex align-items-center position-relative me-3 search-view">
                <!-- Search Icon -->
                <img src="{{ env('ImagePath').'/admin/assets/img/icons/search.svg' }}" alt="Search"
                    style="position: absolute; left: 12px; width: 18px; height: 18px; z-index: 10; opacity: 0.6;">

                <!-- Input Field -->
                <input type="text" id="customerSearch" class="form-control form-control-sm rounded px-3 ps-5"
                    placeholder="Search..." autocomplete="off" style="height: 38px; font-size: 14px;">

                <!-- Search Results -->
                <div id="searchResults" class="list-group bg-white position-absolute rounded shadow mt-1 w-100"
                    style="z-index: 1050; max-height: 300px; overflow-y: auto; display: none; top: 100%; left: 0;">
                </div>
            </div>
            --}}

            <!-- New Order Button -->
            @if(in_array($user->role, ['sales-manager', 'inventory-manager', 'admin']) || ($user->role === 'staff' && (app('hasPermission')(2, 'view') || app('hasPermission')(2, 'add'))))
            <div class="dropdown me-3">
                <button class="btn btn-sm d-flex align-items-center justify-content-center header-new-order-button" type="button" id="newOrderDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="height: 38px; background-color: #ff9f43; color: white; border: none; border-radius: 5px; padding: 0 15px; font-weight: 500; font-size: 14px; gap: 8px; box-shadow: 0 2px 6px rgba(255, 159, 67, 0.2);">
                    + New Order <i class="fas fa-chevron-down ms-1" style="font-size: 12px;"></i>
                </button>
                <ul class="dropdown-menu border-0 shadow" aria-labelledby="newOrderDropdown" style="border-radius: 12px; margin-top: 8px; min-width: 150px;">
                    <li><a class="dropdown-item d-flex align-items-center gap-2 py-2" href="/add-sales">
                        <i class="fas fa-shopping-cart" style="color: #ff9f43; width: 16px; text-align: center;"></i> Order now
                    </a></li>
                    <li><a class="dropdown-item d-flex align-items-center gap-2 py-2" href="/tables-view">
                        <i class="fas fa-utensils" style="color: #ff9f43; width: 16px; text-align: center;"></i> Dine in
                    </a></li>
                </ul>
            </div>
            @endif
        </div>



        <!-- Notifications Bell -->
        @if ($user->role !== 'staff')
        <li class="nav-item dropdown me-1 notification-wrapper">
            <a href="javascript:void(0);" class="nav-link position-relative" data-notification-toggle>
                <i class="fa fa-bell notification-bell"></i>
                <span class="notification-badge notification-count d-none" data-notification-count>0</span>
            </a>
            <!-- Notification Dropdown -->
            <div class="notification-dropdown" data-notification-menu>
                <div class="notification-header">
                    <span>Notifications</span>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('notifications.index') }}" class="text-decoration-none">View All</a>
                    </div>
                </div>
                <div class="notification-body" data-notification-list>
                    <div class="empty-notification"><i class="fa fa-bell-slash"></i>No notifications</div>
                </div>
                <div class="notification-footer">
                    <a href="{{ route('notifications.index') }}" class="text-decoration-none">View all notifications</a>
                </div>
            </div>
        </li>
        @endif

        @if (in_array($user->role, ['staff', 'hr'])) {{-- Staff & HR can check in/out via the header buttons --}}
        <li class="nav-item" id="headerAttendanceContainer"
            style="display:flex; align-items:center; gap:8px; margin-right:12px;">
            <button id="btnHeaderCheckIn" class="btn-check-in"
                style="display:flex; align-items:center; height:38px; background:#ff9f43; color:#fff;
                       border:none; border-radius:6px; padding:0 15px; font-weight:600;
                       font-size:13px; cursor:pointer; gap:6px; white-space:nowrap;">
                <i class="fa fa-sign-in-alt"></i>&nbsp;Check In
            </button>
            <button id="btnHeaderCheckOut" class="btn-check-out"
                style="display:none; align-items:center; height:38px; background:#ff9f43; color:#fff;
                       border:none; border-radius:6px; padding:0 15px; font-weight:600;
                       font-size:13px; cursor:pointer; gap:6px; white-space:nowrap;">
                <i class="fa fa-sign-out-alt"></i>&nbsp;Check Out
            </button>
        </li>
        @endif

        <li class="nav-item dropdown has-arrow main-drop">
            <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                <span class="user-img"><img
                        src="{{ !empty($admin) && !empty($admin->profile_image) ? env('ImagePath').'/storage/' . $admin->profile_image : env('ImagePath').'/admin/assets/img/customer/customer5.jpg' }}"
                        alt="">
                    <span class="status online"></span></span>
            </a>
            <div class="dropdown-menu menu-drop-user">
                <div class="profilename">
                    <div class="profileset">
                        <span class="user-img">
                            <img src="{{ !empty($user->profile_image) ? env('ImagePath').'/storage/' . $user->profile_image : env('ImagePath').'/admin/assets/img/customer/customer5.jpg' }}" alt="">
                        </span>
                        <span class="status online"></span>
                        <div class="profilesets">
                            <h6>{{ $user->name ?? 'User' }}</h6>
                            <h5>{{ ucfirst($user->role ?? 'user') }}</h5>
                        </div>
                    </div>
                    <hr class="m-0">
                    <a class="dropdown-item" href="{{ route('auth.profile') }}">
                        <i class="me-2" data-feather="user"></i> My Profile
                    </a>
                    @if(tenant_has_sub_module(['Change Password', 'change-password'], ['Settings', 'General Settings']))
                    <a class="dropdown-item" href="{{ route('auth.change-password') }}">
                        <i class="me-2" data-feather="lock"></i> Change Password
                    </a>
                    @endif
                    @if($user->role === 'admin')
                    <a class="dropdown-item" href="{{ route('setting.generalsettings') }}">
                        <i class="me-2" data-feather="settings"></i> Settings
                    </a>
                    <a class="dropdown-item new_branch" href="{{route('subbranch.list')}}"><i class="me-2"
                            data-feather="layers"></i>My Branch</a>

                    @endif
                    <hr class="m-0">
                    <a class="dropdown-item logout pb-0" href="{{ route('logout') }}">
                        <img src="{{ env('ImagePath').'/admin/assets/img/icons/log-out.svg' }}" class="me-2" alt="img"> Logout
                    </a>
                </div>

            </div>
        </li>
    </ul>

    {{-- Mobile header right actions --}}
    <div class="mobile-header-right">
        @if(in_array($user->role, ['admin']))
        <div class="" id="mobileSubBranchContainer" style="display: none;">
            <div class="d-flex align-items-center">
                <select id="mobileSubBrandSelect" class="form-select form-select-sm" style="width: 120px;" data-main-branch-id="{{ $user->branch_id ?? $user->id }}">
                </select>
            </div>
        </div>
        @endif

        @if (in_array($user->role, ['staff', 'hr']))
        <div class="mobile-attendance-btn">
            <button class="btn-check-in"
                style="display:flex; align-items:center; justify-content:center; height:34px; background:#ff9f43; color:#fff;
                       border:none; border-radius:6px; padding:0 10px; font-weight:600;
                       font-size:12px; cursor:pointer; gap:4px; white-space:nowrap;">
                <i class="fa fa-sign-in-alt"></i><span class="d-none d-sm-inline">&nbsp;Check In</span>
            </button>
            <button class="btn-check-out"
                style="display:none; align-items:center; justify-content:center; height:34px; background:#ff9f43; color:#fff;
                       border:none; border-radius:6px; padding:0 10px; font-weight:600;
                       font-size:12px; cursor:pointer; gap:4px; white-space:nowrap;">
                <i class="fa fa-sign-out-alt"></i><span class="d-none d-sm-inline">&nbsp;Check Out</span>
            </button>
        </div>
        @endif

        @if ($user->role !== 'staff')
        <div class="mobile-header-notification notification-wrapper">
            <a href="javascript:void(0);" class="nav-link position-relative" data-notification-toggle style="padding: 0; line-height: 1; display: block;">
                <i class="fa fa-bell notification-bell" style="font-size: 22px; color: #ff9f43;"></i>
                <span class="notification-badge notification-count d-none" data-notification-count style="top: -8px; right: -8px;">0</span>
            </a>
            <div class="notification-dropdown" data-notification-menu style="top: 40px; right: -10px;">
                <div class="notification-header">
                    <span>Notifications</span>
                    <a href="{{ route('notifications.index') }}" class="text-decoration-none">View All</a>
                </div>
                <div class="notification-body" data-notification-list>
                    <div class="empty-notification"><i class="fa fa-bell-slash"></i>No notifications</div>
                </div>
                <div class="notification-footer">
                    <a href="{{ route('notifications.index') }}" class="text-decoration-none">View all notifications →</a>
                </div>
            </div>
        </div>
        @endif

        <div class="dropdown mobile-user-menu">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="{{route('auth.profile')}}">My Profile</a>
                @if(tenant_has_sub_module(['Change Password', 'change-password'], ['Settings', 'General Settings']))
                <a class="dropdown-item" href="{{ route('auth.change-password') }}">Change Password</a>
                @endif
                @if($user->role === 'admin')
                <a class="dropdown-item" href="{{route('setting.generalsettings')}}">Settings</a>
                <a class="dropdown-item new_branch" href="{{route('subbranch.list')}}">My Branch</a>
                @endif
                <a class="dropdown-item" href="{{ route('logout') }}">Logout</a>
            </div>
        </div>
    </div>

</div>

<!-- Mobile Bottom Navigation Bar -->
<div class="mobile-bottom-nav">
    @php
        $isStaff = auth()->user() && auth()->user()->role === 'staff';
    @endphp
    <a href="{{ $isStaff ? route('auth.staff-dashboard') : route('auth.dashboard') }}" class="nav-item {{ request()->routeIs('auth.dashboard') || request()->routeIs('auth.staff-dashboard') ? 'active' : '' }}">
        <i class="fa fa-home"></i><span>Home</span>
    </a>
    @if(app('hasPermission')(1, 'view'))
    <a href="{{ route('product.list') }}" class="nav-item {{ request()->routeIs('product.*') ? 'active' : '' }}">
        <i class="fa fa-cubes"></i><span>Products</span>
    </a>
    @endif
    @if(app('hasPermission')(2, 'view'))
    <a href="{{ route('sales.list') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
        <i class="fa fa-shopping-cart"></i><span>Order</span>
    </a>
    @endif
    @if(app('hasPermission')(3, 'view'))
    <a href="{{ route('purchase.lists') }}" class="nav-item {{ request()->routeIs('purchase.*') ? 'active' : '' }}">
        <i class="fa fa-shopping-bag"></i><span>Purchase</span>
    </a>
    @endif
    @if(app('hasPermission')(9, 'view'))
    <a href="{{ route('customer.list') }}" class="nav-item {{ request()->routeIs('customer.*') ? 'active' : '' }}">
        <i class="fa fa-users"></i><span>Customer</span>
    </a>
    @endif
</div>
<!-- Platform Orders Modal -->
<div class="modal fade" id="platformOrdersModal" tabindex="-1" aria-labelledby="platformOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" id="platformModalHeader">
                <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2" id="platformOrdersModalLabel">
                    <span id="platformModalIcon"></span>
                    <span id="platformModalTitleText">Platform Orders</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light" style="min-height: 500px; padding: 15px;">
                <!-- Summary Cards -->
                <div class="row mb-2">
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Total Orders</h6>
                                <h3 class="mb-0 fw-bold" id="platformTotalCount">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center p-3">
                                <h6 class="text-warning mb-1 text-uppercase small fw-bold">Pending</h6>
                                <h3 class="mb-0 fw-bold text-warning" id="platformPendingCount">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center p-3">
                                <h6 class="text-success mb-1 text-uppercase small fw-bold">Approved</h6>
                                <h3 class="mb-0 fw-bold text-success" id="platformApprovedCount">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center p-3">
                                <h6 class="text-danger mb-1 text-uppercase small fw-bold">Rejected</h6>
                                <h3 class="mb-0 fw-bold text-danger" id="platformRejectedCount">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2 gap-2 bg-white p-2 rounded shadow-sm">
                    <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center w-100">
                        <div class="btn-group" role="group" id="platformStatusFilter">
                            <input type="radio" class="btn-check" name="statusFilter" id="filterAll" value="All" autocomplete="off" checked>
                            <label class="btn btn-outline-secondary btn-sm px-3" for="filterAll">All</label>
                            
                            <input type="radio" class="btn-check" name="statusFilter" id="filterPending" value="Pending" autocomplete="off">
                            <label class="btn btn-outline-warning btn-sm px-3" for="filterPending">Pending</label>
                            
                            <input type="radio" class="btn-check" name="statusFilter" id="filterApproved" value="Approved" autocomplete="off">
                            <label class="btn btn-outline-success btn-sm px-3" for="filterApproved">Approved</label>
                            
                            <input type="radio" class="btn-check" name="statusFilter" id="filterRejected" value="Rejected" autocomplete="off">
                            <label class="btn btn-outline-danger btn-sm px-3" for="filterRejected">Rejected</label>
                        </div>
                        
                        <div class="input-group input-group-sm" style="max-width: 200px;">
                            <span class="input-group-text bg-white"><i class="fa fa-calendar-alt text-muted"></i></span>
                            <input type="date" id="platformDateFilter" class="form-control" title="Filter by date">
                        </div>
                        
                        <div class="input-group input-group-sm ms-md-auto" style="max-width: 280px;">
                            <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" id="platformOrderSearch" class="form-control border-start-0 ps-0" placeholder="Search by order ID or customer...">
                        </div>
                    </div>
                </div>

                <!-- Order List -->
                <div id="platformOrdersContainer" class="d-flex flex-column gap-2 pb-2">
                    <!-- Orders injected here via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    const currentUserRole = "{{ auth()->user()->role }}";
    const currentUserId   = "{{ auth()->user()->id }}";

    /* =========================================
       POS INTEGRATION HELPER
       ========================================= */
    const posIntegration = {
        getSettings: function() {
            try {
                const data = localStorage.getItem('posIntegration');
                if (data) {
                    return JSON.parse(data);
                }
            } catch (e) {
                console.error('Error reading posIntegration settings from localStorage', e);
            }
            return {
                zomato: { enabled: false, apiKey: '' },
                swiggy: { enabled: false, apiKey: '' }
            };
        },
        saveSettings: function(settings) {
            try {
                localStorage.setItem('posIntegration', JSON.stringify(settings));
            } catch (e) {
                console.error('Error saving posIntegration settings to localStorage', e);
            }
        },
        isPlatformEnabled: function(platform) {
            const settings = this.getSettings();
            if (settings[platform] && settings[platform].enabled && (settings[platform].apiKey || '').trim() !== '') {
                return true;
            }
            return false;
        }
    };

    function applyPosHeaderVisibility() {
        const isZomato = posIntegration.isPlatformEnabled('zomato');
        const isSwiggy = posIntegration.isPlatformEnabled('swiggy');
        
        const zomatoBtn = document.getElementById('zomatoHeaderBtn');
        const swiggyBtn = document.getElementById('swiggyHeaderBtn');
        
        if (zomatoBtn) {
            zomatoBtn.style.setProperty('display', isZomato ? 'flex' : 'none', 'important');
        }
        if (swiggyBtn) {
            swiggyBtn.style.setProperty('display', isSwiggy ? 'flex' : 'none', 'important');
        }
        
        // Also toggle dashboard widgets if they exist
        const dashZomatoWidget = document.getElementById('dashZomatoWidget');
        const dashSwiggyWidget = document.getElementById('dashSwiggyWidget');
        
        if (dashZomatoWidget) {
            dashZomatoWidget.style.setProperty('display', isZomato ? 'block' : 'none', 'important');
        }
        if (dashSwiggyWidget) {
            dashSwiggyWidget.style.setProperty('display', isSwiggy ? 'block' : 'none', 'important');
        }
    }
    
    document.addEventListener('DOMContentLoaded', applyPosHeaderVisibility);
    /* ========================================= */
</script>

    {{-- ==================== ATTENDANCE CHECK IN/OUT ==================== --}}
    <script>
        (function() {
            // Guard: only run for staff or hr role
            if (typeof currentUserRole === 'undefined' || (currentUserRole !== 'staff' && currentUserRole !== 'hr')) return;

            var btnIns  = document.querySelectorAll('.btn-check-in');
            var btnOuts = document.querySelectorAll('.btn-check-out');

            if (btnIns.length === 0 || btnOuts.length === 0) return;

            var csrfMeta  = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            function showCheckIn() {
                btnIns.forEach(function(btn) { btn.style.display = 'flex'; });
                btnOuts.forEach(function(btn) { btn.style.display = 'none'; });
            }
            function showCheckOut() {
                btnIns.forEach(function(btn) { btn.style.display = 'none'; });
                btnOuts.forEach(function(btn) { btn.style.display = 'flex'; });
            }

            // Fetch today's status on page load
            function fetchStatus() {
                fetch("{{ route('staff.checkstatus', [], false) }}", {
                    method: 'GET',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.ok ? r.json() : Promise.reject('HTTP ' + r.status); })
                .then(function(data) {
                    if (data.status === 'checked_in') { showCheckOut(); } else { showCheckIn(); }
                })
                .catch(function(err) {
                    console.warn('[Attendance] status fetch failed:', err);
                    showCheckIn();
                });
            }

            fetchStatus();

            // Check In click
            btnIns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    btnIns.forEach(function(b) {
                        b.disabled = true;
                        b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Getting GPS...';
                    });

                    function resetBtnIns() {
                        btnIns.forEach(function(b) {
                            b.disabled = false;
                            b.innerHTML = '<i class="fa fa-sign-in-alt"></i>&nbsp;Check In';
                        });
                    }

                    async function doCheckIn(latitude, longitude) {
                        btnIns.forEach(function(b) {
                            b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Checking In...';
                        });

                        var body = {};
                        if (latitude !== undefined && longitude !== undefined) {
                            body.check_in_latitude  = latitude;
                            body.check_in_longitude = longitude;
                            try {
                                btnIns.forEach(function(b) { b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Getting Address...'; });
                                let geoRes = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                                let geoData = await geoRes.json();
                                if (geoData && geoData.display_name) { body.check_in_location_name = geoData.display_name; }
                            } catch (e) { console.warn('[CheckIn] Geocoding failed:', e); }
                            btnIns.forEach(function(b) { b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Checking In...'; });
                        }

                        fetch("{{ route('staff.checkin', [], false) }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(body)
                        })
                        .then(async function(r) {
                            var text = await r.text();
                            try { var data = JSON.parse(text); return { ok: r.ok, data: data }; }
                            catch (e) { throw new Error('Server returned an invalid response (HTTP ' + r.status + ')'); }
                        })
                        .then(function(res) {
                            var data = res.data;
                            if (res.ok || data.success) {
                                Swal.fire({ toast:true, position:'top', icon:'success', title: data.message || 'Checked In successfully.', showConfirmButton:false, timer:2000, timerProgressBar:true })
                                    .then(function() { window.location.reload(); });
                                showCheckOut();
                            } else {
                                Swal.fire({ toast:true, position:'top', icon:'error', title: data.error || 'Check In failed.', showConfirmButton:false, timer:5000 });
                            }
                        })
                        .catch(function(err) {
                            Swal.fire({ toast:true, position:'top', icon:'error', title: err.message || 'Check In failed. Please try again.', showConfirmButton:false, timer:5000 });
                        })
                        .finally(resetBtnIns);
                    }

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) { doCheckIn(position.coords.latitude, position.coords.longitude); },
                            function(error) {
                                if (error.code === 1) {
                                    Swal.fire({ icon: 'warning', title: 'Location Access Required', html: 'Please allow location access in your browser and try again.<br><br><small>On iPhone: Settings â†’ Safari â†’ Location â†’ Allow</small>', confirmButtonText: 'OK' });
                                    resetBtnIns();
                                } else { doCheckIn(); }
                            },
                            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                        );
                    } else { doCheckIn(); }
                });
            });

            // Check Out click
            btnOuts.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    btnOuts.forEach(function(b) {
                        b.disabled = true;
                        b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Getting GPS...';
                    });

                    async function doCheckOut(latitude, longitude) {
                        btnOuts.forEach(function(b) {
                            b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Checking Out...';
                        });

                        var body = {};
                        if (latitude !== undefined && longitude !== undefined) {
                            body.check_out_latitude  = latitude;
                            body.check_out_longitude = longitude;
                            try {
                                btnOuts.forEach(function(b) { b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Getting Address...'; });
                                let geoRes = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                                let geoData = await geoRes.json();
                                if (geoData && geoData.display_name) { body.check_out_location_name = geoData.display_name; }
                            } catch (e) { console.warn('[CheckOut] Geocoding failed:', e); }
                            btnOuts.forEach(function(b) { b.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;Checking Out...'; });
                        }

                        fetch("{{ route('staff.checkout', [], false) }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(body)
                        })
                        .then(async function(r) {
                            var text = await r.text();
                            try { var data = JSON.parse(text); return { ok: r.ok, data: data }; }
                            catch (e) { throw new Error('Server returned an invalid response (HTTP ' + r.status + ')'); }
                        })
                        .then(function(res) {
                            var data = res.data;
                            if (res.ok || data.success) {
                                Swal.fire({ toast:true, position:'top', icon:'success', title: data.message || 'Checked Out successfully.', showConfirmButton:false, timer:2000, timerProgressBar:true })
                                    .then(function() { window.location.reload(); });
                                showCheckIn();
                            } else {
                                Swal.fire({ toast:true, position:'top', icon:'error', title: data.error || 'Check Out failed.', showConfirmButton:false, timer:5000 });
                            }
                        })
                        .catch(function(err) {
                            Swal.fire({ toast:true, position:'top', icon:'error', title: err.message || 'Check Out failed. Please try again.', showConfirmButton:false, timer:5000 });
                        })
                        .finally(function() {
                            btnOuts.forEach(function(b) {
                                b.disabled = false;
                                b.innerHTML = '<i class="fa fa-sign-out-alt"></i>&nbsp;Check Out';
                            });
                        });
                    }

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) { doCheckOut(position.coords.latitude, position.coords.longitude); },
                            function(error) {
                                if (error.code === 1) {
                                    Swal.fire({ icon: 'warning', title: 'Location Access Required', html: 'Please allow location access in your browser and try again.', confirmButtonText: 'OK' });
                                    btnOuts.forEach(function(b) { b.disabled = false; b.innerHTML = '<i class="fa fa-sign-out-alt"></i>&nbsp;Check Out'; });
                                } else { doCheckOut(); }
                            },
                            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                        );
                    } else { doCheckOut(); }
                });
            });
        })();
    </script>

    <script>
        // ==================== SIDEBAR TOGGLE FUNCTIONALITY ====================
        $(document).on('mouseover', function(e) {
            e.stopPropagation();
            const $toggleBtn = $('#toggle_btn1');
            const isTabletSize = $(window).width() >= 768 && $(window).width() <= 1024;
            const isButtonAvailable = $toggleBtn.length > 0 && (isTabletSize || $toggleBtn.is(':visible'));

            if ($('body').hasClass('mini-sidebar') && isButtonAvailable) {
                const isInsideSidebar = $(e.target).closest('.sidebar, .header-left').length;
                if (isInsideSidebar) {
                    $('body').addClass('expand-menu');
                    $('.subdrop + ul').slideDown();
                } else {
                    $('body').removeClass('expand-menu');
                    $('.subdrop + ul').slideUp();
                }
                return false;
            }
        });

        $(document).on('click', '#toggle_btn1', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($(this).data('processing')) return false;
            $(this).data('processing', true);

            const body = $('body');
            const $btn = $(this);
            if (body.hasClass('mini-sidebar')) {
                body.removeClass('mini-sidebar');
                $btn.addClass('active');
                $('.subdrop + ul').slideDown();
                localStorage.setItem('screenModeNightTokenState', 'night');
                setTimeout(function() { body.removeClass('mini-sidebar'); $('.header-left').addClass('active'); }, 100);
            } else {
                body.addClass('mini-sidebar');
                $btn.removeClass('active');
                $('.subdrop + ul').slideUp();
                localStorage.removeItem('screenModeNightTokenState');
                setTimeout(function() { body.addClass('mini-sidebar'); $('.header-left').removeClass('active'); }, 100);
            }
            setTimeout(() => { $btn.data('processing', false); }, 300);
            return false;
        });

        // ==================== BRANCH DROPDOWN FUNCTIONALITY ====================
        (function initializeBranchDropdown() {
            const containers = [document.getElementById('subBranchContainer'), document.getElementById('mobileSubBranchContainer')];
            const selects = [document.getElementById('subBrandSelect'), document.getElementById('mobileSubBrandSelect')];
            
            containers.forEach(container => {
                if (container) container.style.display = 'block';
            });
            
            selects.forEach(select => {
                if (select) {
                    select.innerHTML = "";
                    const mainOption = document.createElement('option');
                    mainOption.value = "";
                    mainOption.textContent = 'Main Branch';
                    select.appendChild(mainOption);
                }
            });

            if (window.$ && $.fn && $.fn.select2) {
                $('#subBrandSelect').select2({ placeholder: 'Main Branch', allowClear: true });
                $('#mobileSubBrandSelect').select2({ placeholder: 'Main Branch', allowClear: true });
            }
        })();

        @if(in_array($user->role, ['admin']))
        fetch('/api/get_subadmin', {
            headers: {
                "Authorization": "Bearer " + localStorage.getItem("authToken"),
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .then(res => res.json())
        .then(response => {
            if (!response) { console.warn('No response from /api/get_subadmin'); return; }
            if (response.status === false) { console.warn('API status false', response); }

            const selects = [document.getElementById('subBrandSelect'), document.getElementById('mobileSubBrandSelect')];
            
            if (document.getElementById('subBranchContainer')) document.getElementById('subBranchContainer').style.display = 'block';
            if (document.getElementById('mobileSubBranchContainer')) document.getElementById('mobileSubBranchContainer').style.display = 'block';

            selects.forEach(select => {
                if (!select) return;
                select.innerHTML = "";
                const mainOption = document.createElement("option");
                mainOption.value = "";
                mainOption.textContent = "Main Branch";
                select.appendChild(mainOption);

                (response.data || []).forEach(function(item) {
                    const option = document.createElement("option");
                    option.value = item.id;
                    option.textContent = item.name;
                    select.appendChild(option);
                });
            });

            if (window.$ && $.fn && $.fn.select2) {
                $('#subBrandSelect').select2({ placeholder: 'Main Branch', allowClear: true });
                $('#mobileSubBrandSelect').select2({ placeholder: 'Main Branch', allowClear: true });
            }

            const savedId = localStorage.getItem('selectedSubAdminId');
            const mainBranchId = String(document.getElementById('subBrandSelect')?.dataset.mainBranchId || '');
            if (savedId) {
                const exists = (response.data || []).some(function(item) { return String(item.id) === String(savedId); });
                if (exists) {
                    $('#subBrandSelect').val(savedId).trigger('change.select2');
                    $('#mobileSubBrandSelect').val(savedId).trigger('change.select2');
                    setTimeout(function() { if (typeof loadFilteredDashboardData === 'function') loadFilteredDashboardData(savedId); }, 300);
                } else {
                    // savedId is stale (e.g. sub-admin was deleted) — reset to Main Branch
                    $.post('/clear-subadmin-session', { _token: $('meta[name="csrf-token"]').attr('content') }, function() {
                        if (mainBranchId) {
                            localStorage.setItem('selectedSubAdminId', mainBranchId);
                        } else {
                            localStorage.removeItem('selectedSubAdminId');
                        }
                    });
                    $('#subBrandSelect').val("").trigger('change.select2');
                    $('#mobileSubBrandSelect').val("").trigger('change.select2');
                }
            } else {
                // No selection stored — default to Main Branch and persist its ID
                if (mainBranchId) {
                    localStorage.setItem('selectedSubAdminId', mainBranchId);
                }
                $('#subBrandSelect').val("").trigger('change.select2');
                $('#mobileSubBrandSelect').val("").trigger('change.select2');
            }
        })
        .catch(function() { console.error('Failed to load sub-admin list'); });
        @endif

        $('#subBrandSelect, #mobileSubBrandSelect').on('change', function(e) {
            if (!e.originalEvent) return; // Prevent infinite loop when synced via JS
            const selectedId = $(this).val();
            const selectedText = $(this).find('option:selected').text() || '';
            // The admin's own branch/id used when Main Branch is selected
            const mainBranchId = $(this).data('main-branch-id') || '';
            $('#currentSelection').text(selectedId ? ('Selected: ' + selectedText) : '');

            // sync other select
            const otherSelectId = $(this).attr('id') === 'subBrandSelect' ? '#mobileSubBrandSelect' : '#subBrandSelect';
            if ($(otherSelectId).val() !== selectedId) {
                $(otherSelectId).val(selectedId).trigger('change.select2');
            }

            if (selectedId) {
                // A sub-admin branch is selected — store its ID
                localStorage.setItem('selectedSubAdminId', selectedId);
                $.post('/set-subadmin-session', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    subAdminId: selectedId
                }, function() {
                    window.location.href = "{{ route('auth.dashboard') }}";
                });
            } else {
                // Main Branch selected — store the admin's own branch ID (e.g. 1)
                // so every module sends the correct branch when no sub-admin is chosen
                if (mainBranchId) {
                    localStorage.setItem('selectedSubAdminId', String(mainBranchId));
                } else {
                    localStorage.removeItem('selectedSubAdminId');
                }
                $.post('/clear-subadmin-session', { _token: $('meta[name="csrf-token"]').attr('content') }, function() {
                    window.location.href = '/dashboard';
                });
            }
        });

        // ==================== NOTIFICATION FUNCTIONALITY ====================
        let notificationRefreshInterval = null;

        document.addEventListener('DOMContentLoaded', function() {
            initializeNotifications();

            if (notificationRefreshInterval) clearInterval(notificationRefreshInterval);
            notificationRefreshInterval = setInterval(() => {
                const hasOpenMenu = getNotificationInstances().some(instance => instance.menu.style.display === 'block');
                if (hasOpenMenu) loadNotifications();
            }, 30000);
        });

        function getNotificationInstances() {
            return Array.from(document.querySelectorAll('.notification-wrapper')).map(wrapper => ({
                wrapper,
                toggle: wrapper.querySelector('[data-notification-toggle]'),
                menu:   wrapper.querySelector('[data-notification-menu]'),
                list:   wrapper.querySelector('[data-notification-list]'),
                count:  wrapper.querySelector('[data-notification-count]')
            })).filter(instance => instance.toggle && instance.menu);
        }

        function closeNotificationMenus(exceptMenu = null) {
            getNotificationInstances().forEach(instance => {
                if (!exceptMenu || instance.menu !== exceptMenu) instance.menu.style.display = 'none';
            });
        }

        function setNotificationCounts(unreadCount) {
            getNotificationInstances().forEach(instance => {
                if (!instance.count) return;
                if (unreadCount > 0) {
                    instance.count.innerText = unreadCount > 99 ? '99+' : unreadCount;
                    instance.count.classList.remove('d-none');
                } else {
                    instance.count.classList.add('d-none');
                }
            });
        }

        function renderEmptyNotifications(message, isError = false) {
            const iconClass  = isError ? 'fa fa-exclamation-circle' : 'fa fa-bell-slash';
            const stateClass = isError ? 'empty-notification text-danger' : 'empty-notification';
            getNotificationInstances().forEach(instance => {
                if (!instance.list) return;
                instance.list.innerHTML = `<div class="${stateClass}"><i class="${iconClass}"></i>${message}</div>`;
            });
        }

        function initializeNotifications() {
            const notificationInstances = getNotificationInstances();
            notificationInstances.forEach(instance => {
                const newToggle = instance.toggle.cloneNode(true);
                instance.toggle.parentNode.replaceChild(newToggle, instance.toggle);
                instance.toggle = newToggle;

                newToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const isOpen = instance.menu.style.display === 'block';
                    closeNotificationMenus();
                    if (!isOpen) { loadNotifications(); instance.menu.style.display = 'block'; }
                });
            });

            document.addEventListener('click', function(e) {
                getNotificationInstances().forEach(instance => {
                    if (!instance.toggle.contains(e.target) && !instance.menu.contains(e.target)) {
                        instance.menu.style.display = 'none';
                    }
                });
            });

            loadNotifications();
        }

        function createNotificationItem(item) {
            const notificationItem = document.createElement('div');
            notificationItem.className = `notification-item ${!item.is_read ? 'unread-notification' : ''}`;
            notificationItem.setAttribute('data-id', item.id);
            notificationItem.onclick = function(e) {
                if (e.target.closest('.notification-action')) return;
                handleNotificationClick(item.id, item.link);
            };
            notificationItem.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong class="notification-title">${escapeHtml(item.title)}</strong>
                        <span class="notification-message">${escapeHtml(item.message)}</span>
                        <small class="notification-time"><i class="fa fa-clock-o"></i> ${formatDate(item.created_at)}</small>
                    </div>
                    ${!item.is_read ? '<span class="notification-dot"></span>' : ''}
                </div>
            `;
            return notificationItem;
        }

        function loadNotifications() {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) return;
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';

            fetch('/notifications?selectedSubAdminId=' + selectedSubAdminId, {
                headers: {
                    'X-CSRF-TOKEN': token.getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => { if (!response.ok) throw new Error('Network error'); return response.json(); })
            .then(response => {
                const notificationInstances = getNotificationInstances();
                if (notificationInstances.length === 0) return;
                notificationInstances.forEach(instance => { if (instance.list) instance.list.innerHTML = ''; });

                if (!response.status || !response.data || response.data.length === 0) {
                    renderEmptyNotifications('No notifications');
                    setNotificationCounts(0);
                    return;
                }

                const unreadCount = response.count || response.data.filter(n => !n.is_read).length;
                setNotificationCounts(unreadCount);

                response.data.forEach(item => {
                    notificationInstances.forEach(instance => {
                        if (instance.list) instance.list.appendChild(createNotificationItem(item));
                    });
                });
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                renderEmptyNotifications('Failed to load notifications', true);
            });
        }

        function handleNotificationClick(id, link) {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) return;
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token.getAttribute('content'), 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            })
            .then(() => {
                document.querySelectorAll(`.notification-item[data-id="${id}"]`).forEach(el => {
                    el.classList.remove('unread-notification');
                    const dot = el.querySelector('.notification-dot');
                    if (dot) dot.remove();
                });
                updateNotificationCount();
                if (link && link !== '#') window.location.href = link;
            })
            .catch(error => {
                if (link && link !== '#') window.location.href = link;
            });
        }

        function updateNotificationCount() {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) return;
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';
            fetch('/notifications?selectedSubAdminId=' + selectedSubAdminId, {
                headers: { 'X-CSRF-TOKEN': token.getAttribute('content'), 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(response => { setNotificationCounts(response.count || 0); })
            .catch(error => { console.error('Error updating count:', error); });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now  = new Date();
            const diffMs   = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHrs  = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
            if (diffHrs < 24)  return `${diffHrs} hour${diffHrs > 1 ? 's' : ''} ago`;
            if (diffDays < 7)  return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
            return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ==================== SEARCH FUNCTIONALITY ====================
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('customerSearch');
            const resultBox   = document.getElementById('searchResults');
            if (!searchInput) return;

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                if (query.length < 1) { resultBox.style.display = 'none'; return; }

                fetch(`/search-users?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultBox.innerHTML = '';
                        let hasResults = false;

                        // if (['admin', 'sales-manager', 'inventory-manager'].includes(currentUserRole) && data.users?.length > 0) {
                        //     hasResults = true;
                        //     resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light">Customers</div>`;
                        //     data.users.forEach(user => {
                        //         resultBox.innerHTML += `<a href="/customer-view/${user.id}" class="list-group-item list-group-item-action d-flex align-items-center"><img src="${user.profile_image}" alt="" class="rounded-circle me-2" style="width:35px;height:35px;object-fit:cover;"><div><strong>${escapeHtml(user.name) ?? 'N/A'}</strong><br><small class="text-muted">${escapeHtml(user.email) ?? 'N/A'}</small></div></a>`;
                        //     });
                        // }

                        if (['admin', 'sales-manager', 'inventory-manager'].includes(currentUserRole) && data.products?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Products</div>`;
                            data.products.forEach(product => {
                                resultBox.innerHTML += `<a href="/product-view/${product.id}" class="list-group-item list-group-item-action d-flex align-items-center"><img src="${product.image}" alt="" class="rounded me-2" style="width:35px;height:35px;object-fit:cover;"><div><strong>${escapeHtml(product.name) ?? 'N/A'}</strong><br><small class="text-muted">Price: ${product.price ?? 'N/A'}</small></div></a>`;
                            });
                        }

                        if (['admin', 'sales-manager', 'inventory-manager'].includes(currentUserRole) && data.orders?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Orders</div>`;
                            data.orders.forEach(order => {
                                resultBox.innerHTML += `<a href="/sales-details/${order.id}" class="list-group-item list-group-item-action"><div class="d-flex align-items-center mb-1"><strong>Order #: ${order.order_number ?? 'N/A'}</strong></div><div><small>Customer: ${escapeHtml(order.user_name) ?? 'N/A'}</small><br><small>Total: ${order.total_amount ?? 'N/A'} | Status: ${order.payment_status ?? 'N/A'}</small></div></a>`;
                            });
                        }

                        if (!hasResults) resultBox.innerHTML = '<div class="list-group-item text-center text-muted">No results found</div>';
                        resultBox.style.display = 'block';
                    })
                    .catch(error => {
                        resultBox.innerHTML = '<div class="list-group-item text-center text-danger">Error loading results</div>';
                        resultBox.style.display = 'block';
                    });
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultBox.contains(e.target)) resultBox.style.display = 'none';
            });
        });
    </script>

    <!-- Platform Orders JavaScript -->
    <script>
        // Dummy data for platforms
        const dummyPlatformData = {
            'Zomato': [
                { id: 'ZOM-1001', date: '28 Sep 2026, 11:30 AM', customer: 'Rahul Desai', phone: '+91 98765 43210', address: 'B-201, Shanti Niketan, Vesu, Surat', items: [{ name: 'Paneer Butter Masala', qty: 2, price: 250 }, { name: 'Butter Naan', qty: 4, price: 40 }], total: 660, payment: 'Online', status: 'Pending' },
                { id: 'ZOM-1002', date: '29 Sep 2026, 11:45 AM', customer: 'Sneha Patel', phone: '+91 87654 32109', address: '10, Adajan Patiya, Surat', items: [{ name: 'Veg Biryani', qty: 1, price: 200 }, { name: 'Raita', qty: 1, price: 50 }], total: 250, payment: 'COD', status: 'Approved' },
                { id: 'ZOM-1003', date: '30 Sep 2026, 12:00 PM', customer: 'Amit Shah', phone: '+91 76543 21098', address: '55, Piplod Main Road, Surat', items: [{ name: 'Masala Dosa', qty: 3, price: 120 }], total: 360, payment: 'Online', status: 'Pending' },
                { id: 'ZOM-1004', date: '01 Oct 2026, 12:15 PM', customer: 'Kavita Joshi', phone: '+91 65432 10987', address: 'Near Udhna Darwaja, Surat', items: [{ name: 'Pav Bhaji', qty: 2, price: 150 }], total: 300, payment: 'COD', status: 'Rejected' },
                { id: 'ZOM-1005', date: '01 Oct 2026, 01:30 PM', customer: 'Vikram Singh', phone: '+91 99887 76655', address: 'Katargam, Surat', items: [{ name: 'Chicken Tikka', qty: 1, price: 320 }, { name: 'Roomali Roti', qty: 3, price: 30 }], total: 410, payment: 'Online', status: 'Approved' },
                { id: 'ZOM-1006', date: '02 Oct 2026, 12:45 PM', customer: 'Pooja Mehta', phone: '+91 88776 65544', address: 'City Light, Surat', items: [{ name: 'Chole Bhature', qty: 2, price: 180 }], total: 360, payment: 'Online', status: 'Pending' },
            ],
            'Swiggy': [
                { id: 'SWG-2001', date: '28 Sep 2026, 11:20 AM', customer: 'Nisha Sharma', phone: '+91 90123 45678', address: 'Pal RTO, Surat', items: [{ name: 'Veg Hakka Noodles', qty: 1, price: 190 }, { name: 'Manchurian Dry', qty: 1, price: 210 }], total: 400, payment: 'Online', status: 'Pending' },
                { id: 'SWG-2002', date: '29 Sep 2026, 11:35 AM', customer: 'Rohan Trivedi', phone: '+91 81234 56789', address: 'Varachha, Surat', items: [{ name: 'Khamani', qty: 2, price: 80 }, { name: 'Locho', qty: 2, price: 60 }], total: 280, payment: 'Online', status: 'Approved' },
                { id: 'SWG-2003', date: '30 Sep 2026, 11:55 AM', customer: 'Anita Vaghela', phone: '+91 72345 67890', address: 'Rander Road, Surat', items: [{ name: 'Dal Makhani', qty: 1, price: 220 }, { name: 'Jeera Rice', qty: 1, price: 140 }], total: 360, payment: 'COD', status: 'Rejected' },
                { id: 'SWG-2004', date: '01 Oct 2026, 12:10 PM', customer: 'Gaurav Patel', phone: '+91 63456 78901', address: 'Athwalines, Surat', items: [{ name: 'Palak Paneer', qty: 2, price: 240 }, { name: 'Tandoori Roti', qty: 5, price: 20 }], total: 580, payment: 'Online', status: 'Pending' },
                { id: 'SWG-2005', date: '01 Oct 2026, 08:25 PM', customer: 'Bhavna Jani', phone: '+91 94567 89012', address: 'Bhatar, Surat', items: [{ name: 'Dabeli', qty: 4, price: 40 }], total: 160, payment: 'COD', status: 'Pending' },
                { id: 'SWG-2006', date: '02 Oct 2026, 12:40 PM', customer: 'Sanjay Choksi', phone: '+91 85678 90123', address: 'Dumas Road, Surat', items: [{ name: 'Margarita Pizza', qty: 1, price: 350 }, { name: 'Garlic Bread', qty: 1, price: 150 }], total: 500, payment: 'Online', status: 'Approved' },
            ]
        };

        let currentPlatform = '';
        let currentOrders = [];
        let platformModalInstance = null;

        function openPlatformModal(platform) {
            currentPlatform = platform;
            currentOrders = JSON.parse(JSON.stringify(dummyPlatformData[platform])); // Deep copy for resetting state if needed
            
            // Set modal header colors and titles
            const header = document.getElementById('platformModalHeader');
            const titleText = document.getElementById('platformModalTitleText');
            const modalIcon = document.getElementById('platformModalIcon');
            
            if (platform === 'Zomato') {
                header.style.backgroundColor = '#E23744';
                titleText.textContent = 'Zomato Orders';
                modalIcon.innerHTML = `<img src="{{ env('ImagePath').'/admin/assets/img/product/zomato.png' }}" alt="Zomato" style="width: 24px; height: 24px; border-radius: 4px; object-fit: cover;">`;
            } else {
                header.style.backgroundColor = '#FC8019';
                titleText.textContent = 'Swiggy Orders';
                modalIcon.innerHTML = `<img src="{{ env('ImagePath').'/admin/assets/img/product/swiggy.jpg' }}" alt="Swiggy" style="width: 24px; height: 24px; border-radius: 4px; object-fit: cover;">`;
            }

            // Reset filters
            document.getElementById('filterAll').checked = true;
            document.getElementById('platformOrderSearch').value = '';
            document.getElementById('platformDateFilter').value = '';

            // Render data
            renderPlatformOrders();

            // Show modal
            if (!platformModalInstance) {
                platformModalInstance = new bootstrap.Modal(document.getElementById('platformOrdersModal'));
            }
            platformModalInstance.show();
        }

        function renderPlatformOrders() {
            const filterValue = document.querySelector('input[name="statusFilter"]:checked').value;
            const searchValue = document.getElementById('platformOrderSearch').value.toLowerCase();
            const dateValue = document.getElementById('platformDateFilter').value;

            // Filter orders
            const filteredOrders = currentOrders.filter(order => {
                const matchesStatus = filterValue === 'All' || order.status === filterValue;
                const matchesSearch = order.id.toLowerCase().includes(searchValue) || order.customer.toLowerCase().includes(searchValue);
                
                let matchesDate = true;
                if (dateValue) {
                    const orderDateObj = new Date(order.date);
                    if (!isNaN(orderDateObj.getTime())) {
                        const orderYYYYMMDD = orderDateObj.getFullYear() + '-' + String(orderDateObj.getMonth() + 1).padStart(2, '0') + '-' + String(orderDateObj.getDate()).padStart(2, '0');
                        matchesDate = (orderYYYYMMDD === dateValue);
                    }
                }

                return matchesStatus && matchesSearch && matchesDate;
            });

            // Update Summary Counts
            document.getElementById('platformTotalCount').textContent = currentOrders.length;
            document.getElementById('platformPendingCount').textContent = currentOrders.filter(o => o.status === 'Pending').length;
            document.getElementById('platformApprovedCount').textContent = currentOrders.filter(o => o.status === 'Approved').length;
            document.getElementById('platformRejectedCount').textContent = currentOrders.filter(o => o.status === 'Rejected').length;

            // Render Orders HTML
            const container = document.getElementById('platformOrdersContainer');
            container.innerHTML = '';

            if (filteredOrders.length === 0) {
                container.innerHTML = `<div class="text-center py-5 text-muted">
                    <i class="fa fa-inbox fa-3x mb-3"></i>
                    <p class="mb-0">No orders found matching the criteria.</p>
                </div>`;
                return;
            }

            filteredOrders.forEach(order => {
                let badgeClass = 'bg-warning text-dark';
                if (order.status === 'Approved') badgeClass = 'bg-success';
                else if (order.status === 'Rejected') badgeClass = 'bg-danger';

                let itemsHtml = order.items.map(item => `
                    <div class="d-flex justify-content-between small border-bottom py-1">
                        <span>${item.qty} x ${item.name}</span>
                        <span>₹${(item.qty * item.price).toLocaleString('en-IN')}</span>
                    </div>
                `).join('');

                const actionsHtml = order.status === 'Pending' ? `
                    <button class="btn btn-sm btn-success px-3 me-2 mb-1" onclick="updatePlatformOrderStatus('${order.id}', 'Approved')">
                        <i class="fa fa-check me-1"></i> Approve & Generate Bill
                    </button>
                    <button class="btn btn-sm btn-danger px-3 mb-1" onclick="updatePlatformOrderStatus('${order.id}', 'Rejected')">
                        <i class="fa fa-times me-1"></i> Reject
                    </button>
                ` : `
                    <button class="btn btn-sm btn-secondary px-3" disabled>
                        <i class="fa ${order.status === 'Approved' ? 'fa-check' : 'fa-times'} me-1"></i> ${order.status}
                    </button>
                `;

                const orderCard = document.createElement('div');
                orderCard.className = 'card border-0 shadow-sm rounded-3 overflow-hidden';
                orderCard.innerHTML = `
                    <div class="card-header bg-white border-bottom-0 pt-2 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">${order.id}</h6>
                        <span class="badge ${badgeClass} px-2 py-1">${order.status}</span>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-md-5 mb-2 mb-md-0">
                                <div class="text-muted small mb-1"><i class="fa fa-calendar-alt me-1"></i> ${order.date}</div>
                                <div class="fw-bold text-dark mt-1">${order.customer}</div>
                                <div class="small text-muted mb-1"><i class="fa fa-phone-alt me-1"></i> ${order.phone}</div>
                                <div class="small text-muted"><i class="fa fa-map-marker-alt me-1"></i> ${order.address}</div>
                            </div>
                            <div class="col-md-7">
                                <div class="bg-light p-2 rounded mb-1">
                                    <div class="fw-bold small mb-1 text-muted">ORDER ITEMS</div>
                                    ${itemsHtml}
                                    <div class="d-flex justify-content-between fw-bold mt-1 pt-1 border-top border-dark">
                                        <span>Total Amount (${order.payment})</span>
                                        <span class="text-primary fs-6">₹${order.total.toLocaleString('en-IN')}</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    ${actionsHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(orderCard);
            });
        }

        function updatePlatformOrderStatus(orderId, newStatus) {
            if (newStatus === 'Rejected') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to reject this order?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, reject it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        applyStatusUpdate(orderId, newStatus);
                    }
                });
            } else if (newStatus === 'Approved') {
                Swal.fire({
                    title: 'Approve & Generate Bill?',
                    text: "Are you sure you want to approve this order and print the bill?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Approve & Print'
                }).then((result) => {
                    if (result.isConfirmed) {
                        applyStatusUpdate(orderId, newStatus);
                        printOrderBill(orderId);
                    }
                });
            } else {
                applyStatusUpdate(orderId, newStatus);
            }
        }

        function printOrderBill(orderId) {
            const order = currentOrders.find(o => o.id === orderId);
            if (!order) return;

            const printWindow = window.open('', '_blank', 'width=800,height=600');
            
            let itemsHtml = order.items.map(item => `
                <tr>
                    <td>${item.name}</td>
                    <td class="text-center">${item.qty}<br><small>Nos</small></td>
                    <td class="text-right">${(item.qty * item.price).toFixed(2)}</td>
                </tr>
            `).join('');

            const html = `
                <html>
                <head>
                    <title>Bill - ${order.id}</title>
                    <style>
                        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 20px; color: #000; background: #fff; }
                        .bill-container { max-width: 400px; margin: 0 auto; padding: 10px; font-size: 13px; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .fw-bold { font-weight: bold; }
                        .border-dashed { border-bottom: 1px dashed #000; margin-bottom: 15px; }
                        .border-dashed-top { border-top: 1px dashed #000; margin-top: 15px; }
                        h2 { margin: 0 0 5px 0; font-size: 18px; }
                        p { margin: 3px 0; font-size: 12px; }
                        .meta-grid { display: grid; grid-template-columns: 80px 1fr; gap: 4px; font-size: 12px; margin-bottom: 15px; }
                        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 15px; }
                        th { text-align: left; padding: 6px 0; border-bottom: 1px dashed #000; border-top: 1px dashed #000; text-transform: uppercase; }
                        td { padding: 8px 0; vertical-align: top; }
                        .summary-grid { display: grid; grid-template-columns: 1fr 100px; gap: 6px; font-size: 12px; margin-bottom: 10px; }
                        .grand-total { font-size: 15px; font-weight: bold; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; margin: 15px 0; }
                        .savings-box { border: 1px dashed #000; border-radius: 8px; text-align: center; padding: 12px; font-size: 11px; margin-bottom: 15px; }
                        .footer-text { text-align: center; font-size: 11px; font-weight: bold; margin-top: 30px; text-transform: uppercase; }
                    </style>
                </head>
                <body>
                    <div class="bill-container">
                        <div class="text-center" style="margin-bottom: 15px;">
                            <h2 class="fw-bold">FABLEAD DEVELOPER & TECHNOLA</h2>
                            <p>A-5001, Ascon Plaza, Adajan, Surat, Gujarat 395009 India</p>
                            <p>Ph: 9824734533</p>
                        </div>
                        
                        <div class="border-dashed"></div>
                        <div class="text-center fw-bold" style="margin: 15px 0;">TAX INVOICE</div>
                        <div class="border-dashed"></div>

                        <div class="meta-grid">
                            <div>Bill No:</div><div class="text-right fw-bold">${order.id}</div>
                            <div>Date:</div><div class="text-right">${order.date}</div>
                            <div>Customer:</div><div class="text-right">${order.customer}</div>
                            <div>Phone:</div><div class="text-right">${order.phone}</div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>ITEM</th>
                                    <th class="text-center">QTY</th>
                                    <th class="text-right">AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                            </tbody>
                        </table>
                        
                        <div class="border-dashed-top"></div>

                        <div class="summary-grid" style="margin-top: 15px;">
                            <div>Subtotal</div>
                            <div class="text-right">${order.total.toFixed(2)}</div>
                            <div>Tax (GST)</div>
                            <div class="text-right">0.00</div>
                        </div>

                        <div class="summary-grid grand-total">
                            <div>GRAND TOTAL</div>
                            <div class="text-right">₹${order.total.toFixed(2)}</div>
                        </div>

                        <div class="savings-box">
                            <div class="fw-bold">Payment Mode: ${order.payment}</div>
                            <div>Platform: ${currentPlatform}</div>
                            <div style="font-size: 10px; margin-top: 5px; color: #555;">Thank you for shopping with us!</div>
                        </div>

                        <div class="summary-grid" style="margin-bottom: 20px;">
                            <div>Paid Amount</div>
                            <div class="text-right">${order.payment === 'Online' ? order.total.toFixed(2) : '0.00'}</div>
                            <div class="fw-bold">Balance Due</div>
                            <div class="text-right fw-bold">${order.payment === 'COD' ? order.total.toFixed(2) : '0.00'}</div>
                        </div>

                        <div class="border-dashed-top"></div>

                        <div class="footer-text">
                            THANK YOU! VISIT AGAIN..
                        </div>
                    </div>
                    <` + `script>` + `
                        window.onload = function() {
                            window.print();
                            // Optional: auto-close window after print dialog is closed
                            // setTimeout(function() { window.close(); }, 500);
                        }
                    <` + `/script>
                </body>
                </html>
            `;

            printWindow.document.open();
            printWindow.document.write(html);
            printWindow.document.close();
        }

        function applyStatusUpdate(orderId, newStatus) {
            const orderIndex = currentOrders.findIndex(o => o.id === orderId);
            if (orderIndex > -1) {
                currentOrders[orderIndex].status = newStatus;
                
                // Also update the master static dummy data so changes persist across modal opens
                const masterIndex = dummyPlatformData[currentPlatform].findIndex(o => o.id === orderId);
                if (masterIndex > -1) {
                    dummyPlatformData[currentPlatform][masterIndex].status = newStatus;
                }

                renderPlatformOrders();
                updatePlatformBadges();

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `Order ${orderId} marked as ${newStatus}`,
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        }

        // Update Platform Badges
        function updatePlatformBadges() {
            const zOrders = dummyPlatformData['Zomato'] || [];
            const sOrders = dummyPlatformData['Swiggy'] || [];
            
            const zCount = zOrders.length;
            const zPending = zOrders.filter(o => o.status === 'Pending').length;
            
            const sCount = sOrders.length;
            const sPending = sOrders.filter(o => o.status === 'Pending').length;
            
            const zBadge = document.getElementById('zomatoBadgeCount');
            if (zBadge) {
                if (zCount > 0) {
                    zBadge.innerText = zCount > 99 ? '99+' : zCount;
                    zBadge.classList.remove('d-none');
                    if (zPending > 0) zBadge.classList.add('pulse');
                    else zBadge.classList.remove('pulse');
                } else {
                    zBadge.classList.add('d-none');
                }
            }
            
            const sBadge = document.getElementById('swiggyBadgeCount');
            if (sBadge) {
                if (sCount > 0) {
                    sBadge.innerText = sCount > 99 ? '99+' : sCount;
                    sBadge.classList.remove('d-none');
                    if (sPending > 0) sBadge.classList.add('pulse');
                    else sBadge.classList.remove('pulse');
                } else {
                    sBadge.classList.add('d-none');
                }
            }
        }

        // Event listeners for filters and search
        document.addEventListener('DOMContentLoaded', () => {
            const filterRadios = document.querySelectorAll('input[name="statusFilter"]');
            filterRadios.forEach(radio => {
                radio.addEventListener('change', renderPlatformOrders);
            });

            document.getElementById('platformOrderSearch')?.addEventListener('input', renderPlatformOrders);
            document.getElementById('platformDateFilter')?.addEventListener('change', renderPlatformOrders);
            
            updatePlatformBadges();
        });
    </script>
<style>
   @media only screen and (max-width: 767px) {
      .select2-container { min-width: unset !important; }
   }
   @media screen and (max-width: 340px) { .select2-container { width: 123px !important; } }
   @media screen and (min-width: 341px) and (max-width: 374px) { .select2-container { width: 140px !important; } }
   @media screen and (min-width: 375px) and (max-width: 390px) { .select2-container { width: 145px !important; } }
   @media screen and (min-width: 391px) and (max-width: 420px) { .select2-container { width: 165px !important; } }
   @media screen and (min-width: 430px) and (max-width: 480px) { .select2-container { width: 175px !important; } }
   @media screen and (min-width: 768px) and (max-width: 819px) {
       .select2-container { min-width: auto !important; width: 340px !important; }
   }
   @media screen and (min-width: 820px) and (max-width: 1023px) {
       .select2-container { min-width: auto !important; width: 368px !important; }
       .header-new-order-button { display: none !important; }
       #toggle_btn1 { display: none !important; }
       .header-search, .header-search-container { display: none !important; }
       .logo-view { float: none; }
   }
   @media screen and (width: 768px) and (height: 1024px) {
       .header-new-order-button { display: none !important; }
       .header-search, .header-search-container { display: none !important; }
       .logo-view { float: none; }
       #toggle_btn1 { display: none !important; }
   }
   @media screen and (width: 1024px) and (height: 1366px) {
       .select2-container { min-width: auto !important; width: 151px !important; }
       .search-view { width: 285px !important; }
       .logo-view { float: none; }
       .mini-sidebar .header-left .logo-small { display: none !important; }
   }
   @media screen and (width: 540px) and (height: 720px) {
       .select2-container { min-width: auto !important; width: 225px !important; }
   }
   @media screen and (width: 1024px) and (height: 600px) {
       .select2-container { min-width: auto !important; width: 155px !important; }
       .web_button { position: absolute; z-index: 9999; }
       .search-view { width: 285px !important; }
       .logo-view { float: none; }
       .mini-sidebar .header-left .logo-small { display: none !important; }
       div#toggle_btn1 { position: relative !important; }
   }
   @media screen and (width: 1280px) and (height: 800px) {
       .select2-container { min-width: auto !important; width: 215px !important; }
       .web_button { position: absolute; z-index: 9999; }
   }
   .search-view { width: 285px !important; }
   .logo-view { float: none; }
   .mini-sidebar .header-left .logo-small { display: none !important; }
   div#toggle_btn1 { position: relative !important; }
</style>

@endpush