/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // Core Brand Colors
        brand: {
          primary: '#E65C33',
          'primary-hover': '#F57C42',
          premium: '#D9532A',
          'premium-hover': '#E36A3A',
          'sidebar-end': '#B53D20',
        },
        
        // Topbar & Sidebar
        topbar: '#2C2C2C',
        
        // Background & Layout
        'bg-main': '#F4F7FA',
        'bg-card': '#FAFAFA',
        
        // Text Colors
        'text-primary': '#212121',
        'text-secondary': '#5A6B7A',
        'text-muted': '#6B7280',
        
        // PDF Text Colors
        pdf: {
          primary: '#111',
          label: '#333',
          muted: '#555',
        },
        
        // Semantic Colors
        success: '#00897B',
        info: '#3A7CA5',
        warning: '#FFB300',
        danger: '#D32F2F',
        
        // Service Type Colors
        service: {
          cleaning: '#00897B',
          installation: '#3A7CA5',
          repair: '#D32F2F',
        },
        
        // Payment Status Colors
        status: {
          paid: '#10B981',
          'partially-paid': '#F59E0B',
          unpaid: '#DC3545',
        },
        
        // Chart Colors - Financial Reports
        chart: {
          'revenue-trend': '#2563EB',
          revenue: '#16A34A',
          payroll: '#059669',
          profit: '#111827',
          expense: '#EF4444',
          'expense-1': '#F87171',
          'expense-2': '#FB923C',
          'expense-3': '#FBBF24',
          'expense-4': '#34D399',
          'expense-5': '#60A5FA',
          'expense-6': '#A78BFA',
          'expense-7': '#F472B6',
          'expense-8': '#4ADE80',
          outflow: '#F87171',
          inflow: '#60A5FA',
          tax: '#A78BFA',
        },
        
        // Chart Colors - Service Reports
        'chart-service': {
          'monthly-revenue': '#0D6EFD',
          type: '#6F42C1',
          'aircon-type': '#20C997',
          status: '#FFC107',
          completed: '#198754',
          ongoing: '#0DCAF0',
          'weekly-frequency': '#0DCAF0',
          'top-suppliers': '#0DCAF0',
          'expense-trend': '#DC3545',
          'avg-duration': '#FD7E14',
          generic: '#6C757D',
        },
        
        // Interactive Elements
        interactive: {
          'btn-disabled-bg': '#E0E0E0',
          'btn-disabled-text': '#9E9E9E',
          'event-service': '#107C10',
          'link-active': '#3A7CA5',
        },
        
        // Borders & Dividers
        border: {
          card: '#D9E2EC',
          default: '#E5E7EB',
          light: '#E9ECEF',
          pdf: '#444',
          'pdf-underline': '#999',
        },
        
        // Badge Colors
        badge: {
          bg: '#F3F4F6',
          text: '#374151',
        },
        
        // PDF Special Colors
        'pdf-special': {
          'table-header': '#F9FAFB',
          'table-header-alt': '#EFEFEF',
          'signature-line': '#000',
          'company-highlight': '#B91C1C',
        },
      },
      
      // Gradient Utilities
      backgroundImage: {
        'gradient-brand': 'linear-gradient(135deg, #E65C33 0%, #F57C42 100%)',
        'gradient-sidebar': 'linear-gradient(180deg, #E65C33 0%, #F57C42 50%, #B53D20 100%)',
        'gradient-topbar': 'linear-gradient(135deg, #2C2C2C 0%, #2C2C2C 100%)',
        'gradient-main': 'linear-gradient(135deg, #F4F7FA 0%, #FAFAFA 100%)',
      },
      
      // Box Shadow with brand colors
      boxShadow: {
        'brand': '0 4px 12px rgba(230, 92, 51, 0.3)',
        'brand-lg': '0 8px 24px rgba(230, 92, 51, 0.25)',
        'card': '0 4px 16px rgba(0, 0, 0, 0.04)',
        'card-hover': '0 8px 24px rgba(0, 0, 0, 0.08)',
      },
      
      // Border Radius
      borderRadius: {
        'card': '0.875rem',
      },
      
      // Font Family
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [
    // Custom plugin for component classes
    function({ addComponents }) {
      addComponents({
        // Button Components
        '.btn': {
          '@apply px-4 py-2 rounded-lg font-semibold transition-all duration-300 ease-in-out': {},
        },
        '.btn-primary': {
          '@apply bg-brand-primary text-white hover:bg-brand-primary-hover hover:-translate-y-0.5': {},
          'box-shadow': '0 4px 12px rgba(230, 92, 51, 0.3)',
        },
        '.btn-success': {
          '@apply bg-success text-white hover:bg-[#00796B] hover:-translate-y-0.5': {},
        },
        '.btn-info': {
          '@apply bg-info text-white hover:bg-[#2F6690] hover:-translate-y-0.5': {},
        },
        '.btn-warning': {
          '@apply bg-warning text-text-primary hover:bg-[#FFA000] hover:-translate-y-0.5': {},
        },
        '.btn-danger': {
          '@apply bg-danger text-white hover:bg-[#C62828] hover:-translate-y-0.5': {},
        },
        
        // Badge Components
        '.badge': {
          '@apply px-3 py-1.5 rounded-md font-semibold text-sm': {},
        },
        '.badge-paid': {
          '@apply bg-[rgba(16,185,129,0.1)] text-status-paid border border-status-paid': {},
        },
        '.badge-unpaid': {
          '@apply bg-[rgba(220,53,69,0.1)] text-status-unpaid border border-status-unpaid': {},
        },
        
        // Card Components
        '.card': {
          '@apply bg-bg-card border border-border-card rounded-card shadow-card transition-all duration-300': {},
        },
        '.card:hover': {
          '@apply shadow-card-hover -translate-y-0.5': {},
        },
      })
    },
  ],
}
