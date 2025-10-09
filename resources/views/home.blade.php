@extends('layouts.finance_app')

@section('title', 'Finance Dashboard - Home')

@section('content')
    <div class="dashboard-header">
        <h1>Welcome Back! 👋</h1>
        <p>Here's what's happening with your finance today.</p>
    </div>

    <div class="dashboard-grid">
        <!-- Stats Cards -->
        <div class="card stats-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #dc2626, #ea580c);">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="card-content">
                <h3>Total Revenue</h3>
                <p class="stats-value">$125,430</p>
                <span class="stats-change positive">+12.5% from last month</span>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #ea580c, #f97316);">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="card-content">
                <h3>Total Expenses</h3>
                <p class="stats-value">$45,230</p>
                <span class="stats-change negative">+5.2% from last month</span>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #f97316, #fb923c);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="card-content">
                <h3>Net Profit</h3>
                <p class="stats-value">$80,200</p>
                <span class="stats-change positive">+18.3% from last month</span>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #fb923c, #fdba74);">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-content">
                <h3>Active Employees</h3>
                <p class="stats-value">142</p>
                <span class="stats-change positive">+3 new this month</span>
            </div>
        </div>
    </div>

    <div class="dashboard-row">
        <div class="card chart-card">
            <h2><i class="fas fa-chart-area"></i> Revenue Overview</h2>
            <p>Track your monthly revenue performance</p>
            <div class="chart-placeholder">
                <i class="fas fa-chart-line" style="font-size: 4rem; color: #ea580c; opacity: 0.3;"></i>
                <p style="color: #78716c; margin-top: 1rem;">Chart visualization coming soon</p>
            </div>
        </div>

        <div class="card activity-card">
            <h2><i class="fas fa-clock"></i> Recent Activity</h2>
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon" style="background: #fef3c7;">
                        <i class="fas fa-file-invoice" style="color: #ea580c;"></i>
                    </div>
                    <div class="activity-content">
                        <p class="activity-title">New invoice created</p>
                        <span class="activity-time">2 minutes ago</span>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon" style="background: #fef3c7;">
                        <i class="fas fa-money-bill-wave" style="color: #dc2626;"></i>
                    </div>
                    <div class="activity-content">
                        <p class="activity-title">Payment received</p>
                        <span class="activity-time">1 hour ago</span>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon" style="background: #fef3c7;">
                        <i class="fas fa-user-plus" style="color: #f97316;"></i>
                    </div>
                    <div class="activity-content">
                        <p class="activity-title">New employee added</p>
                        <span class="activity-time">3 hours ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem !important;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .card-content h3 {
            font-size: 0.9rem;
            color: #78716c;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .stats-value {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #dc2626, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.25rem;
        }

        .stats-change {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .stats-change.positive {
            color: #16a34a;
        }

        .stats-change.negative {
            color: #dc2626;
        }

        .dashboard-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .chart-card, .activity-card {
            padding: 2rem !important;
        }

        .chart-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 300px;
            background: linear-gradient(135deg, #fef2f2, #fff7ed);
            border-radius: 12px;
            margin-top: 1.5rem;
        }

        .activity-list {
            margin-top: 1.5rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-bottom: 0.75rem;
        }

        .activity-item:hover {
            background: linear-gradient(135deg, #fef2f2, #fff7ed);
            transform: translateX(5px);
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.85rem;
            color: #78716c;
        }

        @media (max-width: 1024px) {
            .dashboard-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
