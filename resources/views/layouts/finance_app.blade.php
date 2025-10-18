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

    <!-- Finance Sidebar CSS -->
    <link href="{{ asset('css/finance_sidebar.css') }}" rel="stylesheet">

    <!-- Tailwind CSS (CDN for quick styling) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#06b6d4',
                            600: '#0891b2',
                            700: '#0e7490',
                        }
                    }
                }
            }
        }
    </script>

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    @stack('styles')
</head>
<body class="has-sidebar font-[Inter,ui-sans-serif,system-ui]" data-theme="modern">

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button id="sidebarToggle" class="toggle-btn" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <img src="{{ asset('images/finance_logo.svg') }}" class="brand-logo animate-logo" alt="Finance Logo">
            <h4 class="logo-text">Finance</h4>
        </div>

        <nav class="sidebar-menu" aria-label="Finance Navigation">
            <ul>
                <li><a href="{{ route('finance.home') }}" class="menu-item {{ request()->routeIs('finance.home') ? 'active' : '' }}"><i class="fas fa-home"></i> <span class="label">Home</span></a></li>
                <li><a href="{{ route('finance.billing.index') }}" class="menu-item {{ request()->routeIs('finance.billing.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> <span class="label">Billing & Invoicing</span></a></li>
                <li><a href="{{ route('finance.accounts-receivable') }}" class="menu-item {{ request()->routeIs('finance.accounts-receivable') ? 'active' : '' }}"><i class="fas fa-receipt"></i> <span class="label">Accounts Receivable (AR)</span></a></li>
                <li><a href="{{ route('finance.accounts-payable') }}" class="menu-item {{ request()->routeIs('finance.accounts-payable') ? 'active' : '' }}"><i class="fas fa-file-invoice"></i> <span class="label">Accounts Payable (AP)</span></a></li>
                <li><a href="{{ route('finance.payroll') }}" class="menu-item {{ request()->routeIs('finance.payroll') ? 'active' : '' }}"><i class="fas fa-money-check-alt"></i> <span class="label">Payroll Management</span></a></li>
                <li><a href="{{ route('finance.cashflow') }}" class="menu-item {{ request()->routeIs('finance.cashflow') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> <span class="label">Cash Flow & Expense Tracking</span></a></li>
                <li><a href="{{ route('finance.reports') }}" class="menu-item {{ request()->routeIs('finance.reports') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> <span class="label">Reporting</span></a></li>
                <li><a href="{{ route('finance.inventory.dashboard') }}" class="menu-item {{ request()->routeIs('finance.inventory.*') ? 'active' : '' }}"><i class="fas fa-boxes"></i> <span class="label">Inventory Management</span></a></li>
            </ul>
           <ul>

            <li>
                <a href="http://Humanresource.test/HR">Admin</a>
            </li>
           </ul>
           
        </nav>
    </div>

    <!-- Topbar -->
    <div class="topbar gradient-bar">
        <div class="topbar-left">
            <div class="logo-container">
                <img src="{{ asset('images/finance_logo.svg') }}" alt="Finance Logo" class="logo">
                <span class="system-name">Finance Management</span>
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
                <div class="account-avatar">AD</div>
                <span class="account-name">Admin</span>
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
            // Sidebar Toggle with smooth animation
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const topbar = document.querySelector('.topbar');
            const mainContent = document.querySelector('.main-content');
            
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                topbar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                
                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
            
            // Restore sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                topbar.classList.add('collapsed');
                mainContent.classList.add('expanded');
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

    @stack('scripts')
</body>
<style>
/* Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100%;
    background: linear-gradient(180deg, #dc2626 0%, #ea580c 50%, #f97316 100%);
    color: #fff;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    z-index: 1000;
    box-shadow: 4px 0 24px rgba(220, 38, 38, 0.15);
}

.sidebar.collapsed {
    width: 70px;
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
    padding: 0.9rem 1rem;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
}

.sidebar-menu ul li a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: #fff;
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.sidebar-menu ul li a:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.sidebar-menu ul li a:hover::before {
    transform: scaleY(1);
}

.sidebar-menu i {
    margin-right: 14px;
    font-size: 1.1rem;
    min-width: 20px;
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
    left: 260px;
    right: 0;
    height: 70px;
    background: linear-gradient(135deg, #dc2626 0%, #ea580c 50%, #f97316 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 999;
    box-shadow: 0 4px 20px rgba(220, 38, 38, 0.15);
    backdrop-filter: blur(10px);
}

.topbar.collapsed {
    left: 70px;
}

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
    color: #dc2626;
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
    margin-left: 260px;
    margin-top: 70px;
    padding: 2rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(135deg, #fef2f2 0%, #fff7ed 100%);
    min-height: calc(100vh - 70px);
}

.main-content.expanded {
    margin-left: 70px;
}

/* Content Styling */
.content h1 {
    font-size: 2.5rem;
    background: linear-gradient(135deg, #dc2626 0%, #ea580c 50%, #f97316 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
    margin-bottom: 0.5rem;
    letter-spacing: -0.5px;
}

.content p {
    color: #78716c;
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
</style>

</html>
