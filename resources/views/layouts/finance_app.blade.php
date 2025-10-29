<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Finance Dashboard')</title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Alpine.js for sidebar collapsible groups -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Finance Color Scheme -->
    <link href="{{ asset('css/finance_colors.css') }}" rel="stylesheet">
    
    <!-- Finance Sidebar CSS -->
    <link href="{{ asset('css/finance_sidebar.css') }}" rel="stylesheet">

    <!-- Tailwind CDN removed for production safety -->

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    @stack('styles')
</head>
<body class="has-sidebar font-[Inter,ui-sans-serif,system-ui]" data-theme="modern">

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">

            <img src="{{ asset('images/finance_logo.svg') }}" class="brand-logo animate-logo" alt="Finance Logo">
            <h4 class="logo-text">Finance</h4>
        </div>

        <nav class="sidebar-menu" aria-label="Finance navigation">
            <ul>
                <!-- DASHBOARD -->
                <li class="menu-title"><i class="fa-solid fa-gauge"></i> Dashboard</li>
                <hr class="section-divider" />
                <li>
                    <a class="menu-item {{ request()->routeIs('finance.home') ? 'active' : '' }}" href="{{ route('finance.home') }}">
                        <i class="fas fa-home"></i><span class="label">Finance Dashboard</span>
                    </a>
                </li>

                <!-- BILLING & INVOICES -->
                <li class="menu-group" x-data="{ open: {{ (request()->routeIs('finance.billing.*') || request()->routeIs('invoices.*') || request()->routeIs('finance.accounts-receivable') || request()->routeIs('finance.ar.aging')) ? 'true' : 'false' }} }">
                    <button class="menu-header" @click="open = !open">
                        <i class="fa-solid fa-file-invoice-dollar"></i><span class="label">Billing </span>
                        <i class="fa-solid fa-chevron-down caret" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <hr class="section-divider" />
                    <div class="submenu" x-show="open" x-transition>
                        <a class="menu-item {{ request()->routeIs('finance.billing.index') ? 'active' : '' }}" href="{{ route('finance.billing.index') }}"><i class="fa-solid fa-file-invoice"></i><span class="label">Billing</span></a>
                        <a class="menu-item {{ request()->routeIs('finance.accounts-receivable') ? 'active' : '' }}" href="{{ route('finance.accounts-receivable') }}"><i class="fa-solid fa-hand-holding-dollar"></i><span class="label">Accounts Receivable</span></a>
                        <a class="menu-item {{ request()->routeIs('invoices.index') ? 'active' : '' }}" href="{{ route('invoices.index') }}"><i class="fa-solid fa-clock-rotate-left"></i><span class="label">Billing History</span></a>
                    </div>
                </li>


                <!-- PURCHASE ORDERS -->
                <li class="menu-group" x-data="{ open: {{ (request()->routeIs('purchase-orders.*')) ? 'true' : 'false' }} }">
                    <button class="menu-header" @click="open = !open">
                        <i class="fa-solid fa-file-signature"></i><span class="label">Purchase Orders</span>
                        <i class="fa-solid fa-chevron-down caret" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <hr class="section-divider" />
                    <div class="submenu" x-show="open" x-transition>
                        <a class="menu-item {{ request()->routeIs('purchase-orders.index') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}"><i class="fa-solid fa-list"></i><span class="label">All POs</span></a>
                        <a class="menu-item {{ request()->routeIs('accounts-payable.index') ? 'active' : '' }}" href="{{ route('accounts-payable.index') }}"><i class="fa-solid fa-credit-card"></i><span class="label">Accounts Payable</span></a>
                    </div>
                </li>

                <!-- PAYROLL -->
                <li class="menu-group" x-data="{ open: {{ (request()->routeIs('finance.payroll*')) ? 'true' : 'false' }} }">
                    <button class="menu-header" @click="open = !open">
                        <i class="fa-solid fa-people-carry-box"></i><span class="label">Payroll Management</span>
                        <i class="fa-solid fa-chevron-down caret" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <hr class="section-divider" />
                    <div class="submenu" x-show="open" x-transition>
                        <a class="menu-item {{ request()->routeIs('finance.payroll') ? 'active' : '' }}" href="{{ route('finance.payroll') }}"><i class="fa-solid fa-money-bill-wave"></i><span class="label">Payroll</span></a>
                        <a class="menu-item {{ request()->routeIs('finance.disbursement.index') ? 'active' : '' }}" href="{{ route('finance.disbursement.index') }}"><i class="fa-solid fa-wallet"></i><span class="label">Disbursement History</span></a>
                    </div>
                </li>

                <!-- CASH FLOW & EXPENSES -->
                <li class="menu-group" x-data="{ open: {{ (request()->routeIs('finance.cashflow') || request()->routeIs('finance.expenses')) ? 'true' : 'false' }} }">
                    <button class="menu-header" @click="open = !open">
                        <i class="fa-solid fa-chart-pie"></i><span class="label">Finance Overview</span>
                        <i class="fa-solid fa-chevron-down caret" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <hr class="section-divider" />
                    <div class="submenu" x-show="open" x-transition>
                        <a class="menu-item {{ request()->routeIs('finance.cashflow') ? 'active' : '' }}" href="{{ route('finance.cashflow') }}"><i class="fa-solid fa-sack-dollar"></i><span class="label">Cash Flow</span></a>
                        <a class="menu-item {{ request()->routeIs('finance.inventory.dashboard') ? 'active' : '' }}" href="{{ route('finance.inventory.dashboard') }}"><i class="fa-solid fa-chart-bar"></i><span class="label">Inventory Dashboard</span></a>
                        <a class="menu-item {{ request()->routeIs('finance.reports') ? 'active' : '' }}" href="{{ route('finance.reports') }}"><i class="fa-solid fa-coins"></i><span class="label">Finance Reports Hub</span></a>
                    </div>
                </li>


                <!-- ACCOUNT -->
                <li class="menu-group" x-data="{ open: false }">
                    <button class="menu-header" @click="open = !open">
                        <i class="fa-solid fa-user-lock"></i><span class="label">Account</span>
                        <i class="fa-solid fa-chevron-down caret" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <hr class="section-divider" />
                    <div class="submenu" x-show="open" x-transition>
                        <a class="menu-item" href=""><i class="fa-solid fa-right-from-bracket"></i><span class="label">Logout</span></a>
                    </div>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Topbar -->
    <div class="topbar gradient-bar">
        <div class="topbar-left">
            <div class="logo-container">
                <img src="{{ asset('images/3Rs_logo.png') }}" alt="3R's Logo" class="logo-3rs">
            </div>

            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>
        </div>

        <div class="topbar-right">
            <div class="topbar-icons">
                <div class="topbar-item notification-item" data-count="3">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="topbar-item"><i class="fas fa-cog"></i></div>
            </div>

            <div class="account-menu" id="accountMenu">
                <div class="account-avatar">F</div>
                <span class="account-name">Finance</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="content main-content">
        @yield('content')
    </main>

    <!-- Modern Interactive Scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Sidebar Toggle with smooth animation (guard if toggle not present)
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const topbar = document.querySelector('.topbar');
            const mainContent = document.querySelector('.main-content');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    sidebar?.classList.toggle('collapsed');
                    topbar?.classList.toggle('collapsed');
                    mainContent?.classList.toggle('expanded');
                    // Save state to localStorage
                    localStorage.setItem('sidebarCollapsed', sidebar?.classList.contains('collapsed'));
                });
            }
            
            // Restore sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar?.classList.add('collapsed');
                topbar?.classList.add('collapsed');
                mainContent?.classList.add('expanded');
            }

            // Active menu item animation
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Notification pulse animation
            const notificationItem = document.querySelector('.notification-item');
            if (notificationItem) {
                setInterval(() => {
                    notificationItem.classList.add('pulse');
                    setTimeout(() => notificationItem.classList.remove('pulse'), 1000);
                }, 5000);
            }
            
            // Account menu dropdown
            const accountMenu = document.getElementById('accountMenu');
            accountMenu.addEventListener('click', function() {
                this.classList.toggle('active');
            });
            
            // Smooth scroll for page transitions
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
            
            // Add loading animation on page transitions
            window.addEventListener('beforeunload', function() {
                document.body.classList.add('page-transition');
            });
            
            // Parallax effect on scroll
            let lastScroll = 0;
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;
                if (currentScroll > lastScroll) {
                    topbar.style.transform = 'translateY(-5px)';
                } else {
                    topbar.style.transform = 'translateY(0)';
                }
                lastScroll = currentScroll;
            });
        });
    </script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
<style>
:root{
  --sb-grad-top: #e06b48;
  --sb-grad-mid: #d05837;
  --sb-grad-bot: #bd553b;
  --sb-highlight: #d76242;
  --sb-active-bg: rgba(255,255,255,0.14);
  --sb-divider: rgba(255,255,255,0.12);
  --sb-text: #ffffff;
  --sb-w: 220px;
  --sb-w-collapsed: 64px;
  --topbar-h: 56px;
  /* user-specified theme colors */
  --clr-topbar-bg: #2C2C2C;
  --clr-sidebar-grad-start: #E65C33;
  --clr-sidebar-grad-end: #B53D20;
}
/* Sidebar */
.sidebar {
    position: fixed;
    top: var(--topbar-h);
    left: 0;
    width: 260px;
    height: calc(100vh - var(--topbar-h));
    background: linear-gradient(180deg, var(--clr-sidebar-grad-start) 0%, var(--clr-sidebar-grad-end) 100%);
    color: var(--sb-text);
    font-family: "Poppins", "Montserrat", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto; /* allow vertical scroll when content exceeds */
    overflow-x: hidden;
    z-index: 1000;
    box-shadow: inset -2px 0 6px rgba(0,0,0,0.12);
    padding: 10px 8px;
    /* Firefox scrollbar fallback: keep thumb default, track transparent to reveal gradient */
    scrollbar-width: thin;
    scrollbar-color: auto transparent;
}

.sidebar.collapsed {
    width: var(--sb-w-collapsed);
}

/* Sidebar scrollbar styling (WebKit/Blink) */
.sidebar::-webkit-scrollbar {
    width: 10px;
}
.sidebar::-webkit-scrollbar-track {
    background: linear-gradient(180deg, var(--clr-sidebar-grad-start) 0%, var(--clr-sidebar-grad-end) 100%);
}
/* Keep thumb color as-is (no override) */
.sidebar::-webkit-scrollbar-button {
    display: none;
    width: 0;
    height: 0;
}

.sidebar-header {
    display: flex;
    align-items: center;
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    flex-direction: column;
    gap: 0.5rem;
    background: rgba(0, 0, 0, 0.1);
}

.sidebar-header .brand-logo {
    height: 45px;
    width: 45px;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
    transition: transform 0.3s ease;
}

.sidebar-header .brand-logo:hover {
    transform: scale(1.1) rotate(5deg);
}

.sidebar-header .logo-text {
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    letter-spacing: 0.5px;
}

.sidebar-menu {
    padding: 1.5rem 0;
}

.sidebar-menu ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.sidebar-menu ul li {
    margin-bottom: 0.25rem;
    padding: 0 0.5rem;
}

.sidebar-menu ul li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 12px;
    color: var(--sb-text);
    text-decoration: none;
    transition: background .18s ease, transform .12s ease;
    font-weight: 600;
    border-radius: 8px;
    position: relative;
    justify-content: flex-start;
    text-align: left;
}

.sidebar-menu ul li a::before {
    content: '';
    display: none; /* remove left accent line for a cleaner, formal look */
}

.sidebar-menu ul li a:hover {
    background: rgba(255, 255, 255, 0.06);
    transform: translateX(3px);
}

.sidebar-menu ul li a:hover::before {}

/* Active menu item state: soft pill highlight */
.sidebar-menu ul li a.active {
    background: var(--sb-active-bg);
    color: #fff;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08) inset;
}
.sidebar-menu ul li a.active::before { display: none; }

.sidebar-menu i {
    font-size: 16px;
    min-width: 18px;
    width: 18px;
    text-align: center;
}

.sidebar.collapsed .label {
    display: none;
}

.sidebar.collapsed .logo-text {
    display: none;
}

/* Topbar */
.topbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--topbar-h);
    background: var(--clr-topbar-bg);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 999;
    box-shadow: 0 4px 20px rgba(44, 44, 44, 0.15);
    backdrop-filter: blur(10px);
}

.topbar.collapsed { left: 0; }

.topbar-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.topbar-left .logo {
    height: 40px;
    width: 40px;
    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.2));
}

/* 3Rs logo in topbar */
.logo-3rs {
    height: 36px;
    width: auto;
    display: block;
}

.system-name {
    font-weight: 700;
    font-size: 1.15rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    letter-spacing: 0.3px;
}

.search-container {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    min-width: 280px;
}

.search-container:hover {
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.search-container i {
    color: rgba(255, 255, 255, 0.9);
}

.search-container input {
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    width: 100%;
    font-size: 0.95rem;
}

.search-container input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

/* Topbar Right */
.topbar-right {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.topbar-icons {
    display: flex;
    gap: 1rem;
}

.topbar-item {
    cursor: pointer;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.topbar-item:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.topbar-item i {
    font-size: 1.1rem;
}

.account-menu {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    background: rgba(255, 255, 255, 0.15);
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.account-menu:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}

.account-avatar {
    background: linear-gradient(135deg, #fff 0%, #fef3c7 100%);
    color: #E65C33;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 0.95rem;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.account-name {
    font-weight: 600;
    font-size: 0.95rem;
}

/* Main Content */
.main-content {
    margin-left: var(--sb-w);
    margin-top: var(--topbar-h);
    padding: 2rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #F4F7FA;
    min-height: calc(100vh - 70px);
}

.main-content.expanded { margin-left: var(--sb-w-collapsed); }

/* Content Styling */
.content h1 {
    font-size: 2.5rem;
    background: linear-gradient(135deg, #E65C33 0%, #F57C42 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
    margin-bottom: 0.5rem;
    letter-spacing: -0.5px;
}

.content p {
    color: #5A6B7A;
    font-size: 1.1rem;
    line-height: 1.7;
}

/* Toggle Button */
.toggle-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    padding: 0.5rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.toggle-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.toggle-btn i {
    font-size: 1.2rem;
}

/* Sidebar Grouped Menu */
.menu-title {
    padding: 12px 14px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 1px;
    color: var(--sb-text);
    display: flex;
    align-items: center;
    gap: .5rem;
}

/* Ensure single-line labels and left alignment */
.sidebar .label, .menu-header .label {
    flex: 1 1 auto;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.menu-title { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.menu-group {
    margin-bottom: 1.5rem;
}

.menu-header {
    width: 100%;
    background: transparent !important;
    border: 0 !important;
    color: rgba(255,255,255,0.95);
    padding: 12px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;
    cursor: pointer;
    transition: background .18s ease;
    appearance: none;
    -webkit-appearance: none;
    box-shadow: none !important;
    text-align: left;
}
.menu-header:hover { background: transparent !important; }
.menu-header:focus,
.menu-header:focus-visible,
.menu-header:active {
    outline: none !important;
    border: 0 !important;
    box-shadow: none !important;
    background: transparent !important;
}

.submenu {
    margin-left: 1.5rem;
    padding-left: 8px;
}

.submenu .menu-item {
    padding: .65rem 1rem;
    text-align: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.submenu .menu-item i {
    margin-right: .6rem;
}

.menu-header .caret {
    margin-left: auto;
    transition: transform .18s ease;
}

.rotate-180 {
    transform: rotate(180deg);
}

.section-divider {
    border: none; 
    height: 1px; 
    margin: 8px 0; 
    background: var(--sb-divider);
}
</style>

</html>
