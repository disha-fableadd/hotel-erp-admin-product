@php
    $user = auth()->user();
    $isStaff = $user && $user->role === 'staff';
    $settings = \App\Models\Setting::first();

    $showErpSection = tenant_has_module(['Products', 'Categories & Brands', 'Orders', 'Purchases', 'Manage Inventory', 'Inventory', 'Tables', 'Vendors']) && (
        (tenant_has_module('Products') && (app('hasPermission')(1, 'view') || app('hasPermission')(1, 'add'))) ||
        (tenant_has_module('Categories & Brands') && app('hasPermission')(8, 'view')) ||
        (tenant_has_module('Orders') && (app('hasPermission')(2, 'view') || app('hasPermission')(2, 'add'))) ||
        (tenant_has_module('Purchases') && (app('hasPermission')(3, 'view') || app('hasPermission')(3, 'add'))) ||
        (tenant_has_module(['Inventory', 'Manage Inventory']) && app('hasPermission')(17, 'view')) ||
        (tenant_has_module('Tables') && (app('hasPermission')(9, 'view') || app('hasPermission')(9, 'add') || app('hasPermission')(2, 'view'))) ||
        (tenant_has_module('Vendors') && (app('hasPermission')(10, 'view') || app('hasPermission')(10, 'add')))
    );

    $showAccountingSection = tenant_has_module(['Accounting', 'Manage Accounting', 'Expenses', 'Cash & Bank', 'Credit/Debit Notes', 'GST Reports', 'Reports']) && (
        (tenant_has_module(['Accounting', 'Manage Accounting']) && app('hasPermission')(16, 'view')) ||
        (tenant_has_module('Expenses') && (app('hasPermission')(5, 'view') || app('hasPermission')(5, 'add'))) ||
        (tenant_has_module('Cash & Bank') && app('hasPermission')(27, 'view')) ||
        (tenant_has_module('Credit/Debit Notes') && app('hasPermission')(30, 'view')) ||
        (tenant_has_module('GST Reports') && app('hasPermission')(20, 'view')) ||
        (tenant_has_module('Reports') && (app('hasPermission')(2, 'view') || app('hasPermission')(3, 'view') || app('hasPermission')(5, 'view')))
    );

    $showHrSection = tenant_has_module(['Staff', 'Attendance', 'Leaves', 'Payroll', 'Advance Pay', 'HR']) && (
        (tenant_has_module('Staff') && (app('hasPermission')(11, 'view') || app('hasPermission')(11, 'add'))) ||
        (tenant_has_module('Attendance') && (app('hasPermission')(26, 'view') || app('hasPermission')(26, 'add'))) ||
        (tenant_has_module('Leaves') && (app('hasPermission')(28, 'view') || app('hasPermission')(28, 'add'))) ||
        (tenant_has_module('Payroll') && (app('hasPermission')(29, 'view') || app('hasPermission')(29, 'add'))) ||
        (tenant_has_module('Advance Pay') && (app('hasPermission')(23, 'view') || app('hasPermission')(23, 'add')))
    );

    $showSettingsSection = tenant_has_module(['General Settings', 'Tax Rates', 'Settings']) && (
        (tenant_has_module(['General Settings', 'Settings']) && app('hasPermission')(14, 'view')) ||
        (tenant_has_module('Tax Rates') && app('hasPermission')(15, 'view')) ||
        // tenant_has_sub_module(['Change Password', 'change-password'], ['Settings', 'General Settings']) ||
        tenant_has_sub_module(['My Plan Details', 'my-plan-details', 'Plan Details'], ['Settings', 'General Settings'])
        // tenant_has_sub_module(['Plans', 'plans'], ['Settings', 'General Settings'])
    );
@endphp
<style>
    body.mini-sidebar:not(.expand-menu) .sidebar .sidebar-menu>ul>li.menu-title {
        display: none !important;
    }

    #sidebar-menu .sidebar-search-wrap {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        width: 100%;
        height: 40px;
        margin: 0 0 18px;
        padding: 0 14px;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        box-shadow: none;
    }
    #sidebar-menu .sidebar-search-wrap:focus-within {
        border-color: #9ca3af;
    }
    #sidebar-menu .sidebar-search-icon {
        flex-shrink: 0;
        margin-right: 10px;
        color: #9ca3af;
        font-size: 14px;
        line-height: 1;
    }
    #sidebar-menu #sidebar-module-search {
        flex: 1;
        width: 100%;
        min-width: 0;
        height: 100%;
        margin: 0;
        padding: 0;
        border: none !important;
        outline: none !important;
        background: transparent !important;
        box-shadow: none !important;
        font-size: 14px;
        font-weight: 400;
        color: #374151;
        -webkit-appearance: none;
        appearance: none;
    }
    #sidebar-menu #sidebar-module-search::placeholder {
        color: #9ca3af;
        opacity: 1;
    }
    #sidebar-menu #sidebar-module-search:focus {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    /* --- Custom Main Menu Styling --- */
    #sidebar-menu .sidebar-nav-list > li > a {
        border-radius: 8px;
        margin-bottom: 8px;
        padding: 10px 15px;
        display: flex;
        align-items: center;
        font-weight: 600;
        transition: all 0.3s;
        border: 1px solid transparent;
    }

    /* Dashboard */
    #sidebar-menu .sidebar-nav-list > li:first-child > a {
        background-color: #1b2942 !important;
        color: #ffffff !important;
    }
    #sidebar-menu .sidebar-nav-list > li:first-child > a i,
    #sidebar-menu .sidebar-nav-list > li:first-child > a img,
    #sidebar-menu .sidebar-nav-list > li:first-child > a span {
        color: #ffffff !important;
    }

    /* ERP Category */
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-erp > a,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-erp > a:hover,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-erp > a.active {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #64748b;
        border-radius: 6px;
        color: #0f172a !important;
        margin-bottom: 5px;
    }
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-erp > a i,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-erp > a:hover i,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-erp > a.active i {
        color: #64748b !important;
    }

    /* Accounting Category */
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-accounting > a,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-accounting > a:hover,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-accounting > a.active {
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-left: 4px solid #22c55e;
        border-radius: 6px;
        color: #0f172a !important;
        margin-bottom: 5px;
    }
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-accounting > a i,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-accounting > a:hover i,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-accounting > a.active i {
        color: #22c55e !important;
    }

    /* HR Category */
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-hr > a,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-hr > a:hover,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-hr > a.active {
        background-color: #fff7ed;
        border: 1px solid #fed7aa;
        border-left: 4px solid #f97316;
        border-radius: 6px;
        color: #0f172a !important;
        margin-bottom: 5px;
    }
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-hr > a i,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-hr > a:hover i,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-hr > a.active i {
        color: #f97316 !important;
    }

    /* Settings Category */
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-settings > a,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-settings > a:hover,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-settings > a.active {
        background-color: #f0f9ff;
        border: 1px solid #bae6fd;
        border-left: 4px solid #0ea5e9;
        border-radius: 6px;
        color: #0f172a !important;
        margin-bottom: 5px;
    }
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-settings > a i,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-settings > a:hover i,
    #sidebar-menu .sidebar-nav-list > li.sidebar-category-settings > a.active i {
        color: #0ea5e9 !important;
    }

    /* Prevent span and arrow from turning white on hover */
    #sidebar-menu .sidebar-nav-list > li:not(:first-child) > a:hover span,
    #sidebar-menu .sidebar-nav-list > li:not(:first-child) > a:hover .menu-arrow,
    #sidebar-menu .sidebar-nav-list > li:not(:first-child) > a.active span,
    #sidebar-menu .sidebar-nav-list > li:not(:first-child) > a.active .menu-arrow {
        color: #0f172a !important;
    }

    /* Add some spacing for icons */
    #sidebar-menu .sidebar-nav-list > li > a i {
        margin-right: 12px;
        font-size: 16px;
        width: 20px;
        text-align: center;
    }
    
    /* Ensure submenu arrow is correctly positioned and visible */
    #sidebar-menu .sidebar-nav-list ul li a .menu-arrow {
        margin-left: auto;
    }

    /* --- Submenu Icons & Perfect Alignment (Global Fix) --- */
    
    /* 1. Completely hide the theme's messy default circle bullets */
    #sidebar-menu .sidebar-nav-list > li > ul > li > a::before,
    #sidebar-menu .sidebar-nav-list > li > ul > li > a::after,
    #sidebar-menu .sidebar-nav-list > li > ul > li > a.active::before,
    #sidebar-menu .sidebar-nav-list > li > ul > li > a.active::after,
    #sidebar-menu .sidebar-nav-list > li > ul > li > a:hover::before,
    #sidebar-menu .sidebar-nav-list > li > ul > li > a:hover::after {
        display: none !important;
        content: none !important;
        background: transparent !important;
    }

    /* 2. Standard Submenus (e.g. "All Products") - Align text with parent text */
    #sidebar-menu .sidebar-nav-list > li > ul > li > a {
        display: flex;
        align-items: center;
        padding-left: 45px !important; /* Perfectly align with parent's text */
        color: #64748b;
        font-weight: 500;
        position: relative;
        transition: all 0.2s ease;
    }
    
    /* 3. Add a sleek, modern dot directly under the parent's icon */
    #sidebar-menu .sidebar-nav-list > li > ul > li > a::before {
        content: "";
        display: block !important;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #cbd5e1;
        position: absolute;
        left: 21px; /* perfectly centered under the parent icon */
        transition: all 0.2s ease;
    }

    /* Hover & Active states for standard submenus */
    #sidebar-menu .sidebar-nav-list > li > ul > li > a:hover,
    #sidebar-menu .sidebar-nav-list > li > ul > li > a.active {
        color: #ff9f43 !important;
        padding-left: 49px !important; /* Slight nudge on hover for premium feel */
    }

    #sidebar-menu .sidebar-nav-list > li > ul > li > a:hover::before,
    #sidebar-menu .sidebar-nav-list > li > ul > li > a.active::before {
        background-color: #ff9f43 !important;
        transform: scale(1.3);
    }

    /* 4. Submenus WITH custom icons (like in Settings/Shop) */
    #sidebar-menu .sidebar-nav-list > li > ul > li.sidebar-module > a {
        padding-left: 45px !important;
    }
    #sidebar-menu .sidebar-nav-list > li > ul > li.sidebar-module > a::before {
        display: none !important; /* Hide dot since we have an icon */
    }
    #sidebar-menu .sidebar-nav-list > li > ul > li.sidebar-module > a img,
    #sidebar-menu .sidebar-nav-list > li > ul > li.sidebar-module > a i {
        width: 18px;
        height: auto;
        font-size: 15px;
        text-align: center;
        position: absolute;
        left: 15px; /* Align icon directly under parent icon */
        color: #64748b;
        transition: color 0.2s ease;
    }
</style>

<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <div class="sidebar-search-wrap">
                <i class="fas fa-search sidebar-search-icon"></i>
                <input type="text" id="sidebar-module-search" class="sidebar-search-input"
                    placeholder="Search..." autocomplete="off">
            </div>
            <ul class="sidebar-nav-list">

                {{-- Dashboard --}}
                @if(tenant_has_module('Dashboard') && ($isStaff || app('hasPermission')(0, 'view')))
                    <li>
                        @if($isStaff)
                            <a href="{{route('auth.staff-dashboard')}}"><img
                                    src="{{ env('ImagePath') . '/admin/assets/img/icons/dashboard.svg' }}" alt="img"><span>
                                    Dashboard</span> </a>
                        @else
                            <a href="{{route('auth.dashboard')}}"><img
                                    src="{{ env('ImagePath') . '/admin/assets/img/icons/dashboard.svg' }}" alt="img"><span>
                                    Dashboard</span> </a>
                        @endif
                    </li>
                @endif

                {{-- ERP CATEGORY --}}
                @if($showErpSection)
                    <li class="submenu sidebar-category sidebar-category-erp">
                        <a href="javascript:void(0);">
                            <i class="fas fa-industry"></i>
                            <span>ERP</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            {{-- Products --}}
                            @if(tenant_has_module('Products') && (app('hasPermission')(1, 'view') || app('hasPermission')(1, 'add')))
                                <li class="sidebar-module sidebar-module-link">
                                    <a href="{{route('product.list')}}"><img
                                            src="{{ env('ImagePath') . '/admin/assets/img/icons/product.svg' }}" alt="img"><span>
                                            Product</span></a>
                                </li>
                            @endif

                            {{-- Category Setup --}}
                            @if(tenant_has_module('Categories & Brands') && app('hasPermission')(8, 'view'))
                                @php
                                    $canAllCategories = tenant_has_sub_module(['All Categories', 'all-categories'], 'Categories & Brands');
                                    $canNewCategory = tenant_has_sub_module(['New Category', 'new-category'], 'Categories & Brands');
                                    $canAllBrands = tenant_has_sub_module(['All Brands', 'all-brands'], 'Categories & Brands');
                                    $canNewBrand = tenant_has_sub_module(['New Brand', 'new-brand'], 'Categories & Brands');
                                    $canQuantityType = tenant_has_sub_module(['Quantity Type', 'quantity-type'], 'Categories & Brands');
                                @endphp
                                @if($canAllCategories || $canNewCategory || $canAllBrands || $canNewBrand || $canQuantityType)
                                    <li class="submenu sidebar-module">
                                        <a href="javascript:void(0);">
                                            <i class="fas fa-tags"></i>
                                            <span>Category</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            @if($canAllCategories)
                                                <li><a href="{{route('category.list')}}">All Categories</a></li>
                                            @endif
                                            @if($canNewCategory)
                                                <li><a href="{{route('category.add')}}">New Category</a></li>
                                            @endif
                                            @if($canAllBrands)
                                                <li><a href="{{route('brand.list')}}">All Brands</a></li>
                                            @endif
                                            @if($canNewBrand)
                                                <li><a href="{{route('brand.add')}}">New Brand</a></li>
                                            @endif
                                            @if($canQuantityType)
                                                <li><a href="{{route('quantity_type.quantity')}}">Quantity Type</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            {{-- Orders --}}
                            @if(tenant_has_module('Orders') && (app('hasPermission')(2, 'view') || app('hasPermission')(2, 'add')))
                                <li class="sidebar-module sidebar-module-link">
                                    <a href="{{route('sales.list')}}"><img
                                            src="{{ env('ImagePath') . '/admin/assets/img/icons/sales1.svg' }}"
                                            alt="img"><span>Orders</span> <span class="menu-arrow"></span></a>
                                </li>
                            @endif

                            {{-- Products Delivery --}}
                            @if((tenant_has_module('Products Delivery') || (tenant_has_module('Orders') && tenant_has_sub_module(['Products Delivery', 'products-delivery'], 'Orders'))) && (app('hasPermission')(2, 'view') || app('hasPermission')(2, 'add')))
                                <li class="sidebar-module sidebar-module-link">
                                    <a href="{{route('sales.products-delivery')}}">
                                    <i class="fas fa-truck" style="color:#4748b; width: 20px; text-align: center; font-size: 16px;"></i>    
                                    <span>Products Delivery</span> <span class="menu-arrow"></span></a>
                                </li>
                            @endif

                            {{-- Purchases --}}
                            @if(tenant_has_module('Purchases') && (app('hasPermission')(3, 'view') || app('hasPermission')(3, 'add')))
                                <li class="sidebar-module sidebar-module-link">
                                    <a href="{{route('purchase.lists')}}"><img
                                            src="{{ env('ImagePath') . '/admin/assets/img/icons/purchase1.svg' }}"
                                            alt="img"><span>Purchases</span> <span class="menu-arrow"></span></a>
                                </li>
                            @endif

                            {{-- Manage Inventory --}}
                            @if(tenant_has_module(['Inventory', 'Manage Inventory']) && app('hasPermission')(17, 'view'))
                                <li class="sidebar-module sidebar-module-link">
                                    <a href="{{ route('inventory.list') }}">
                                        <i class="fa fa-book"></i>
                                        <span>Manage Inventory</span>
                                    </a>
                                </li>
                            @endif

                            {{-- Tables --}}
                            @if(tenant_has_module('Tables') && (app('hasPermission')(9, 'view') || app('hasPermission')(9, 'add') || app('hasPermission')(2, 'view')))
                                <li class="sidebar-module sidebar-module-link">
                                    <a href="{{route('customer.list')}}"><img src="{{ env('ImagePath') . '/admin/assets/img/icons/tab.svg' }}"
                                            alt="img"><span> Tables</span> <span class="menu-arrow"></span></a>
                                </li>
                            @endif

                            {{-- Vendors --}}
                            @if(tenant_has_module('Vendors') && (app('hasPermission')(10, 'view') || app('hasPermission')(10, 'add')))
                                <li class="sidebar-module sidebar-module-link">
                                    <a href="{{route('vendor.list')}}"><img
                                            src="{{ env('ImagePath') . '/admin/assets/img/icons/users1.svg' }}" alt="img"><span>
                                            Vendors</span> <span class="menu-arrow"></span></a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- ACCOUNTING CATEGORY --}}
                @if($showAccountingSection)
                    <li class="submenu sidebar-category sidebar-category-accounting">
                        <a href="javascript:void(0);">
                            <i class="fas fa-calculator"></i>
                            <span>Accounting</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            {{-- Manage Accounting --}}
                            @if(tenant_has_module(['Accounting', 'Manage Accounting']) && app('hasPermission')(16, 'view'))
                                @php
                                    $canAccountLedger = tenant_has_sub_module(['Account Ledger', 'account-ledger'], 'Accounting');
                                    $canIncomeStatement = tenant_has_sub_module(['Income Statement', 'income-statement'], 'Accounting');
                                    $canBalanceSheet = tenant_has_sub_module(['Balance Sheet', 'balance-sheet'], 'Accounting');
                                    $canBanks = tenant_has_sub_module(['Banks', 'banks'], 'Accounting');
                                @endphp
                                @if($canAccountLedger || $canIncomeStatement || $canBalanceSheet || $canBanks)
                                    <li class="submenu sidebar-module">
                                        <a href="javascript:void(0);">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/purchase1.svg' }}" alt="img">
                                            <span>Manage Accounting</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            @if($canAccountLedger && app('hasPermission')(16, 'view'))
                                                <li><a href="{{ route('account_ledger.add') }}">Account Ledger</a></li>
                                            @endif
                                            @if($canIncomeStatement && app('hasPermission')(16, 'view'))
                                                <li><a href="{{ route('income-statement.index') }}">Income Statement</a></li>
                                            @endif
                                            @if($canBalanceSheet && app('hasPermission')(16, 'view'))
                                                <li class="d-none"><a href="{{ route('accounting.balance-sheet') }}">Balance Sheet</a></li>
                                            @endif
                                            @if($canBanks && app('hasPermission')(16, 'view'))
                                                <li><a href="{{ route('banks.index') }}">Banks</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            {{-- Expenses --}}
                            @if(tenant_has_module('Expenses') && (app('hasPermission')(5, 'view') || app('hasPermission')(5, 'add')))
                                @php
                                    $canAllExpenses = tenant_has_sub_module(['All Expenses', 'all-expenses'], 'Expenses');
                                    $canNewExpense = tenant_has_sub_module(['New Expense', 'new-expense'], 'Expenses');
                                    $canAllExpenseType = tenant_has_sub_module(['All Expense Type', 'all-expense-type', 'expense-types'], 'Expenses');
                                @endphp
                                @if($canAllExpenses || $canNewExpense || $canAllExpenseType)
                                    <li class="submenu sidebar-module">
                                        <a href="javascript:void(0);"><img
                                                src="{{ env('ImagePath') . '/admin/assets/img/icons/expense1.svg' }}" alt="img"><span>
                                                Expenses</span> <span class="menu-arrow"></span></a>
                                        <ul>
                                            @if($canAllExpenses && app('hasPermission')(5, 'view'))
                                                <li><a href="{{route('expense.list')}}">All Expenses</a></li>
                                            @endif
                                            @if($canNewExpense && app('hasPermission')(5, 'add'))
                                                <li><a href="{{route('expense.add')}}">New Expense</a></li>
                                            @endif
                                            @if($canAllExpenseType && app('hasPermission')(5, 'view'))
                                                <li><a href="{{route('expensetype.list')}}">All Expense Type</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            {{-- Cash & Bank --}}
                            @if(tenant_has_module('Cash & Bank') && app('hasPermission')(27, 'view'))
                                @php
                                    $canBankBook = tenant_has_sub_module(['Bank Book', 'bank-book'], 'Cash & Bank');
                                    $canCashBook = tenant_has_sub_module(['Cash Book', 'cash-book'], 'Cash & Bank');
                                @endphp
                                @if($canBankBook || $canCashBook)
                                    <li class="submenu sidebar-module">
                                        <a href="javascript:void(0);">
                                            <i class="fas fa-book"></i>
                                            <span>Cash & Bank</span> <span class="menu-arrow"></span></a>
                                        <ul>
                                            @if($canBankBook && app('hasPermission')(27, 'view'))
                                                <li><a href="{{ route('transaction.bankbook') }}">Bank Book</a></li>
                                            @endif
                                            @if($canCashBook && app('hasPermission')(27, 'view'))
                                                <li><a href="{{ route('transaction.cashbook') }}">Cash Book</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            {{-- Credit / Debit Notes --}}
                            @if(tenant_has_module('Credit/Debit Notes') && app('hasPermission')(30, 'view'))
                                <li class="submenu sidebar-module">
                                    <a href="javascript:void(0);">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                        <span>Credit/Debit Notes</span> <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        @if(app('hasPermission')(30, 'view'))
                                            <li><a href="{{ route('credit-notes-items.index') }}">Credit Notes</a></li>
                                        @endif
                                        @if(app('hasPermission')(30, 'view'))
                                            <li><a href="{{ route('debit-notes-items.index') }}">Debit Notes</a></li>
                                        @endif
                                        @if(app('hasPermission')(30, 'view'))
                                            <li><a href="{{ route('credit-notes.index') }}">Credit/Debit Notes Type</a></li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            {{-- GST Reports --}}
                            @if(tenant_has_module('GST Reports') && app('hasPermission')(20, 'view'))
                                <li class="submenu sidebar-module">
                                    <a href="javascript:void(0);">
                                        <i class="fas fa-chart-line"></i>
                                        <span> GST Reports</span> <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        @if(app('hasPermission')(20, 'view'))
                                            <li><a href="{{ route('gst.reports.purchase') }}">GST Purchase Report</a></li>
                                        @endif
                                        @if(app('hasPermission')(20, 'view'))
                                            <li><a href="{{ route('gst.reports.sales') }}">GST Sales Report</a></li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            {{-- Reports --}}
                            @if(tenant_has_module('Reports') && (app('hasPermission')(2, 'view') || app('hasPermission')(3, 'view') || app('hasPermission')(5, 'view')))
                                <li class="submenu sidebar-module">
                                    <a href="javascript:void(0);">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/time.svg' }}" alt="img">
                                        <span> Reports</span> <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        @if(app('hasPermission')(2, 'view'))
                                            <li><a href="{{route('sales.report')}}">Orders Report</a></li>
                                        @endif
                                        @if(app('hasPermission')(3, 'view'))
                                            <li><a href="{{route('purchase.report')}}">Purchase Report</a></li>
                                        @endif
                                        @if(app('hasPermission')(5, 'view'))
                                            <li><a href="{{route('expense.report')}}">Expenses Report</a></li>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- HR CATEGORY --}}
                @if($showHrSection)
                    <li class="submenu sidebar-category sidebar-category-hr">
                        <a href="javascript:void(0);">
                            <i class="fas fa-id-badge"></i>
                            <span>HR</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            {{-- Staff --}}
                            @if(tenant_has_module('Staff') && app('hasPermission')(11, 'view'))
                                @php
                                    $canAllStaff = tenant_has_sub_module(['All Staff', 'all-staff'], 'Staff');
                                    $canNewStaff = tenant_has_sub_module(['New Staff', 'new-staff'], 'Staff');
                                @endphp
                                @if($canAllStaff || $canNewStaff)
                                    <li class="submenu sidebar-module">
                                        <a href="javascript:void(0);">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/users1.svg' }}" alt="img">
                                            <span> Staff</span> <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            @if($canAllStaff)
                                                <li><a href="{{route('staff.list')}}">All Staff</a></li>
                                            @endif
                                            @if($canNewStaff)
                                                <li><a href="{{route('staff.add')}}">New Staff</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            {{-- Attendance --}}
                            @if(tenant_has_module('Attendance') && (app('hasPermission')(26, 'view') || app('hasPermission')(26, 'add')))
                                @php
                                    $canManageAttendance = tenant_has_sub_module(['Manage Attendance', 'manage-attendance', 'mark-attendance'], 'Attendance');
                                    $canAttendanceSummary = tenant_has_sub_module(['Attendance', 'attendance-summary', 'attendance-list'], 'Attendance');
                                @endphp
                                @if($canManageAttendance || $canAttendanceSummary)
                                    <li class="submenu sidebar-module">
                                        <a href="javascript:void(0);">
                                            <i class="fa fa-clock"></i>
                                            <span> Attendance</span> <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            @if($canManageAttendance && (app('hasPermission')(26, 'add') || app('hasPermission')(26, 'view')))
                                                <li><a href="{{ route('attendence.calendar') }}">Manage Attendance</a></li>
                                            @endif
                                            @if($canAttendanceSummary && (app('hasPermission')(26, 'add') || app('hasPermission')(26, 'view')))
                                                <li><a href="{{ route('attendence.summary') }}">Attendance</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            {{-- Leaves --}}
                            @if(tenant_has_module('Leaves') && (app('hasPermission')(28, 'view') || app('hasPermission')(28, 'add')))
                                @php
                                    $canAllLeaves = tenant_has_sub_module(['All Leaves', 'all-leaves'], 'Leaves');
                                    $canLeaveRequest = tenant_has_sub_module(['Leave Request', 'leave-request'], 'Leaves');
                                    $canAddLeave = tenant_has_sub_module(['Add Leave', 'add-leave'], 'Leaves');
                                @endphp
                                @if($canAllLeaves || $canLeaveRequest || $canAddLeave)
                                    <li class="submenu sidebar-module">
                                        <a href="javascript:void(0);">
                                            <i class="fa fa-calendar-alt"></i>
                                            <span> Leaves</span> <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            @if(in_array(auth()->user()->role, ['admin', 'sub-admin', 'hr']))
                                                @if($canAllLeaves && app('hasPermission')(28, 'view'))
                                                    <li><a href="{{ route('leave.view') }}">All Leaves</a></li>
                                                @endif
                                            @endif
                                            @if($canLeaveRequest && app('hasPermission')(28, 'view'))
                                                <li><a href="{{ route('leave.request') }}">Leave Request</a></li>
                                            @endif
                                            @if($canAddLeave && app('hasPermission')(28, 'add'))
                                                <li><a href="{{ route('leave.add') }}">Add Leave </a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            {{-- Payroll --}}
                            @if(tenant_has_module('Payroll') && (app('hasPermission')(29, 'view') || app('hasPermission')(29, 'add')))
                                @php
                                    $canViewPayroll = tenant_has_sub_module(['View Payroll', 'view-payroll', 'payroll-list'], 'Payroll');
                                    $canManagePayroll = tenant_has_sub_module(['Manage Payroll', 'manage-payroll'], 'Payroll');
                                    $canAddPayroll = tenant_has_sub_module(['Add Payroll', 'add-payroll'], 'Payroll');
                                    $canGroupSalary = tenant_has_sub_module(['Group Salary', 'group-salary'], 'Payroll');
                                @endphp
                                @if($canViewPayroll || $canManagePayroll || $canAddPayroll || $canGroupSalary)
                                    <li class="submenu sidebar-module">
                                        <a href="javascript:void(0);">
                                            <i class="fa fa-money-check-alt"></i>
                                            <span> Payroll</span> <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            @if(in_array(auth()->user()->role, ['staff']) && $canViewPayroll)
                                                <li><a href="{{ route('payroll.list') }}">View Payroll</a></li>
                                            @endif
                                            @if(in_array(auth()->user()->role, ['hr', 'admin']))
                                                @if($canManagePayroll && app('hasPermission')(29, 'view'))
                                                    <li><a href="{{ route('payroll.list') }}">Manage Payroll</a></li>
                                                @endif
                                                @if($canAddPayroll && app('hasPermission')(29, 'add'))
                                                    <li><a href="{{ route('payroll.create') }}">Add Payroll</a></li>
                                                @endif
                                                @if($canGroupSalary && (app('hasPermission')(29, 'view') || app('hasPermission')(29, 'add')))
                                                    <li><a href="{{ route('payroll.salary-details') }}">Group Salary</a></li>
                                                @endif
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            {{-- Advance Pay --}}
                            @if(tenant_has_module('Advance Pay') && auth()->user()->role === 'admin')
                                @php
                                    $canAllAdvancePay = tenant_has_sub_module(['All Advance Pay', 'all-advance-pay'], 'Advance Pay');
                                    $canNewAdvancePay = tenant_has_sub_module(['New Advance Pay', 'new-advance-pay'], 'Advance Pay');
                                @endphp
                                @if($canAllAdvancePay || $canNewAdvancePay)
                                    <li class="submenu sidebar-module">
                                        <a href="javascript:void(0);">
                                            <i class="fa fa-money-bill-wave"></i>
                                            <span>Advance Pay</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            @if($canAllAdvancePay && app('hasPermission')(23, 'view'))
                                                <li><a href="{{ route('advance_pay.index') }}">All Advance Pay</a></li>
                                            @endif
                                            @if($canNewAdvancePay && app('hasPermission')(23, 'add'))
                                                <li><a href="{{ route('advance_pay.create') }}">New Advance Pay</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- SETTINGS CATEGORY --}}
                @if($showSettingsSection)
                    <li class="submenu sidebar-category sidebar-category-settings">
                        <a href="javascript:void(0);">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            {{-- @if(tenant_has_sub_module(['Plans', 'plans'], ['Settings', 'General Settings']) && in_array(auth()->user()->role ?? '', ['admin', 'sub-admin']))
                                <li><a href="{{ route('plans.planlist') }}" class="{{ request()->routeIs('plans.planlist') ? 'active' : '' }}">Plans</a></li>
                            @endif --}}
                            @if(tenant_has_sub_module(['My Plan Details', 'my-plan-details', 'Plan Details'], ['Settings', 'General Settings']) && in_array(auth()->user()->role ?? '', ['admin', 'sub-admin']))
                                <li><a href="{{ route('plans.myplan') }}" class="{{ request()->routeIs('plans.myplan') ? 'active' : '' }}">My Plan Details</a></li>
                            @endif
                            {{-- @if(tenant_has_sub_module(['Change Password', 'change-password'], ['Settings', 'General Settings']))
                                <li><a href="{{ route('auth.change-password') }}" class="{{ request()->routeIs('auth.change-password') ? 'active' : '' }}">Change Password</a></li>
                            @endif --}}
                            @if(tenant_has_module(['General Settings', 'Settings']) && app('hasPermission')(14, 'view'))
                                @if(tenant_has_sub_module(['Shop Settings', 'shop-settings', 'Settings'], 'General Settings'))
                                    <li><a href="{{route('setting.generalsettings')}}">Shop Settings</a></li>
                                @endif
                                {{-- @if(tenant_has_sub_module(['Smtp Settings', 'smtp-settings'], 'General Settings') && (!$settings || $settings->send_mail))
                                    <li><a href="{{ route('setting.smtpsettings') }}">Smtp Settings</a></li>
                                @endif --}}
                                @if(tenant_has_sub_module(['WhatsApp Configuration', 'whatsapp-configuration'], 'General Settings') && (!$settings || $settings->customer_whatsapp_message || $settings->admin_whatsapp_message))
                                    <li><a href="{{ route('setting.facebookappconfiguration') }}">WhatsApp Configuration</a></li>
                                @endif
                                @if(tenant_has_sub_module(['Departments', 'departments'], 'General Settings'))
                                    <li><a href="{{ route('department.view') }}">Departments</a></li>
                                @endif
                                @if(tenant_has_sub_module(['Designations', 'designations'], 'General Settings'))
                                    <li><a href="{{ route('designation.view') }}">Designations</a></li>
                                @endif
                                @if(tenant_has_sub_module(['Leave Types', 'leave-types'], 'General Settings'))
                                    <li><a href="{{ route('leave-type.view') }}">Leave Types</a></li>
                                @endif
                                @if(tenant_has_sub_module(['Holidays', 'holidays', 'Manage Holidays'], 'General Settings'))
                                    <li><a href="{{ route('holidays.index') }}">Manage Holidays</a></li>
                                    <li><a href="{{ route('holidays.calendar') }}">Holiday Calendar</a></li>
                                @endif
                            @endif
                           
                            @if(tenant_has_module('Tax Rates') && app('hasPermission')(15, 'view'))
                                <li><a href="{{route('auth.taxrates')}}">Tax Rates</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>