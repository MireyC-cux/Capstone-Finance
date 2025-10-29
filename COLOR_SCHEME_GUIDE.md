# Finance Management System - Color Scheme Guide

This document outlines the complete color scheme implementation for the Finance Management System.

## 📋 Table of Contents
- [Core Brand Colors](#core-brand-colors)
- [Implementation](#implementation)
- [Usage Examples](#usage-examples)
- [Component Guidelines](#component-guidelines)

---

## 🎨 Core Brand Colors

### Primary Brand Colors
```css
--brand-primary: #E65C33        /* Main brand color - buttons, icons, active elements */
--brand-primary-hover: #F57C42  /* Hover state */
--brand-premium: #D9532A        /* Premium variant */
--brand-premium-hover: #E36A3A  /* Premium hover */
--brand-sidebar-gradient-end: #B53D20
```

### Topbar & Sidebar
```css
--topbar-bg: #2C2C2C           /* Dark topbar background */
```

**Sidebar Gradient:**
```css
background: linear-gradient(180deg, #E65C33 0%, #F57C42 50%, #B53D20 100%);
```

### Background & Layout
```css
--bg-main: #F4F7FA             /* Main page background */
--bg-card: #FAFAFA             /* Card backgrounds */
--border-card: #D9E2EC         /* Card borders */
--bg-white: #FFFFFF            /* Pure white */
```

### Text Colors
```css
--text-primary: #212121        /* Primary text */
--text-secondary: #5A6B7A      /* Secondary text */
--text-muted: #6B7280          /* Muted text in UI */
--text-pdf-primary: #111       /* PDF documents */
--text-pdf-label: #333         /* PDF labels */
--text-pdf-muted: #555         /* PDF muted text */
```

### Semantic Colors
```css
--success: #00897B             /* Success, Cleaning service */
--info: #3A7CA5                /* Info, Installation service, Active links */
--warning: #FFB300             /* Warning states */
--danger: #D32F2F              /* Danger/error, Repair service */
```

### Payment Status Colors
```css
--status-paid: #10B981
--status-partially-paid: #F59E0B
--status-unpaid: #DC3545
```

---

## 🔧 Implementation

### 1. CSS Files Structure

The color scheme is implemented across multiple CSS files:

```
resources/css/
├── finance_colors.css      # Main color system with CSS variables
├── finance_sidebar.css     # Sidebar and topbar styles
└── app.css                 # Tailwind imports

public/css/
├── finance_colors.css      # (Copy of above for production)
├── finance_sidebar.css     # Sidebar styles
└── finance_dashboard.css   # Dashboard-specific styles
```

### 2. Include in Layout

Add to your Blade layout (`resources/views/layouts/finance_app.blade.php`):

```html
<!-- Finance Color Scheme -->
<link href="{{ asset('css/finance_colors.css') }}" rel="stylesheet">

<!-- Finance Sidebar CSS -->
<link href="{{ asset('css/finance_sidebar.css') }}" rel="stylesheet">
```

---

## 💡 Usage Examples

### Buttons

```html
<!-- Primary Brand Button -->
<button class="btn btn-primary">Save</button>

<!-- Premium Button -->
<button class="btn btn-premium">Upgrade</button>

<!-- Semantic Buttons -->
<button class="btn btn-success">Approve</button>
<button class="btn btn-info">View Details</button>
<button class="btn btn-warning">Pending</button>
<button class="btn btn-danger">Delete</button>

<!-- Disabled Button -->
<button class="btn btn-disabled" disabled>Unavailable</button>
```

### Badges

```html
<!-- Payment Status Badges -->
<span class="badge badge-paid">Paid</span>
<span class="badge badge-partially-paid">Partially Paid</span>
<span class="badge badge-unpaid">Unpaid</span>

<!-- Service Type Badges -->
<span class="badge badge-cleaning">Cleaning</span>
<span class="badge badge-installation">Installation</span>
<span class="badge badge-repair">Repair</span>

<!-- Status Badges -->
<span class="badge badge-completed">Completed</span>
<span class="badge badge-ongoing">Ongoing</span>
<span class="badge badge-default">Default</span>
```

### Cards

```html
<div class="card">
    <div class="card-header">
        <h3>Financial Report</h3>
    </div>
    <div class="card-body">
        <!-- Card content -->
    </div>
    <div class="card-footer">
        <!-- Footer actions -->
    </div>
</div>
```

### Tables

```html
<table class="table">
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>INV-001</td>
            <td>$1,200.00</td>
            <td><span class="badge badge-paid">Paid</span></td>
        </tr>
    </tbody>
</table>
```

### Alerts

```html
<div class="alert alert-success">Operation completed successfully!</div>
<div class="alert alert-info">Here's some information.</div>
<div class="alert alert-warning">Please review this warning.</div>
<div class="alert alert-danger">Error occurred!</div>
```

### Background Utility Classes

```html
<div class="bg-brand-primary">Brand Primary Background</div>
<div class="bg-success">Success Background</div>
<div class="bg-card">Card Background</div>
<div class="bg-main">Main Background</div>
```

### Text Color Utility Classes

```html
<p class="text-brand-primary">Brand Primary Text</p>
<p class="text-success">Success Text</p>
<p class="text-danger">Danger Text</p>
<p class="text-muted">Muted Text</p>
```

### Gradient Utilities

```html
<!-- Brand Gradient Background -->
<div class="gradient-brand">...</div>

<!-- Gradient Text -->
<h1 class="gradient-text-brand">Finance Dashboard</h1>
```

---

## 📊 Component Guidelines

### Dashboard Stats Cards

Use brand gradient for stats values:

```html
<div class="card stats-card">
    <div class="card-icon bg-brand-primary">
        <i class="fas fa-dollar-sign"></i>
    </div>
    <div class="card-content">
        <h3>Total Revenue</h3>
        <div class="stats-value">$125,430</div>
        <div class="stats-change positive">+12.5%</div>
    </div>
</div>
```

### Chart Colors

#### Financial Reports
```javascript
const chartColors = {
    revenue: '#2563EB',      // Blue
    revenueGreen: '#16A34A', // Green (admin home)
    expense: '#EF4444',      // Red
    profit: '#111827',       // Dark
    payroll: '#059669',      // Teal
    
    // Expense segments (pie charts)
    segments: [
        '#F87171', '#FB923C', '#FBBF24', '#34D399',
        '#60A5FA', '#A78BFA', '#F472B6', '#4ADE80'
    ],
    
    // Cash flow
    inflow: '#60A5FA',
    outflow: '#F87171',
    tax: '#A78BFA'
};
```

#### Service Reports
```javascript
const serviceChartColors = {
    monthlyRevenue: '#0D6EFD',
    serviceType: '#6F42C1',
    airconType: '#20C997',
    serviceStatus: '#FFC107',
    completed: '#198754',
    ongoing: '#0DCAF0',
    unpaid: '#DC3545',
    avgDuration: '#FD7E14',
    generic: '#6C757D'
};
```

### Forms

```html
<form>
    <div class="form-group">
        <label class="form-label">Invoice Amount</label>
        <input type="text" class="form-control" placeholder="Enter amount">
        <span class="form-text">Amount in USD</span>
    </div>
    
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### PDF Documents

```html
<div class="pdf-container">
    <div class="pdf-header">
        <h2 class="pdf-company-name">Your Company Name</h2>
    </div>
    
    <table class="pdf-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="pdf-label">Service:</span> <span class="pdf-value">Cleaning</span></td>
                <td>$500.00</td>
            </tr>
        </tbody>
    </table>
    
    <div class="pdf-footer">
        <p class="pdf-muted">Terms and conditions apply</p>
        <div class="pdf-signature-line"></div>
    </div>
</div>
```

### Interactive Elements

```html
<!-- Icon Colors -->
<i class="fas fa-check icon-success"></i>
<i class="fas fa-times icon-danger"></i>
<i class="fas fa-info icon-info"></i>
<i class="fas fa-exclamation icon-warning"></i>
<i class="fas fa-star icon-brand"></i>

<!-- Notification Badge -->
<div class="topbar-item notification-item">
    <i class="fas fa-bell"></i>
    <span class="notification-badge">5</span>
</div>

<!-- Calendar Events -->
<div class="calendar-event-service">Service Appointment</div>
<div class="calendar-event-reminder">Payment Reminder</div>
<div class="calendar-event-generic">Meeting</div>
```

---

## 🎯 Best Practices

### 1. Consistency
- Always use CSS variables instead of hardcoded colors
- Use utility classes for quick styling
- Maintain consistent spacing and sizing

### 2. Accessibility
- Ensure sufficient contrast ratios (WCAG AA: 4.5:1 for normal text)
- Primary brand color (#E65C33) on white background: ✓ Pass
- Test with screen readers and keyboard navigation

### 3. Semantic Usage
- Use semantic colors appropriately:
  - `success` for confirmations, approvals, completed actions
  - `danger` for errors, deletions, critical warnings
  - `warning` for cautions, pending states
  - `info` for informational messages, help text

### 4. Brand Consistency
- Primary buttons should use `btn-primary` (brand color)
- Hover states automatically handled by CSS
- Active menu items use brand color highlighting

### 5. Chart Implementation
- Use the defined chart color arrays for consistency
- Financial charts: Use the financial color palette
- Service reports: Use the service color palette
- Maintain color meaning across all charts (green = revenue, red = expenses)

---

## 🔄 CSS Variables Usage

You can also use CSS variables directly in your custom styles:

```css
.custom-element {
    background-color: var(--brand-primary);
    color: var(--bg-white);
    border: 2px solid var(--border-card);
}

.custom-element:hover {
    background-color: var(--brand-primary-hover);
}

.custom-text {
    color: var(--text-secondary);
}
```

---

## 📱 Responsive Considerations

The color scheme includes responsive utilities:

```css
@media (max-width: 768px) {
    /* Automatically adjusts card spacing, table fonts, button sizes */
}
```

Cards, tables, and buttons automatically adapt to smaller screens while maintaining color consistency.

---

## 🚀 Quick Reference

### Most Common Classes

| Purpose | Class | Color |
|---------|-------|-------|
| Primary Action | `btn-primary` | #E65C33 |
| Success Action | `btn-success` | #00897B |
| Danger Action | `btn-danger` | #D32F2F |
| Card Container | `card` | #FAFAFA |
| Paid Status | `badge-paid` | #10B981 |
| Unpaid Status | `badge-unpaid` | #DC3545 |
| Primary Text | `text-primary` | #212121 |
| Muted Text | `text-muted` | #6B7280 |
| Brand Background | `bg-brand-primary` | #E65C33 |

---

## 📞 Support

For questions or issues with the color scheme:
1. Review this guide
2. Check `finance_colors.css` for available variables
3. Ensure proper CSS file inclusion in your layout

---

**Last Updated:** October 2025
**Version:** 1.0.0
