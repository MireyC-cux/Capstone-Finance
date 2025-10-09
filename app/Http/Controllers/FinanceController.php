<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Finance;

class FinanceController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function payroll()
    {
        // Example: fetch payroll data
        $payrolls = Finance::where('type', 'payroll')->get();
        return view('payroll', compact('payrolls'));
    }

    public function billing(Request $request)
    {
        // Get status filter from request, default to 'Completed'
        $status = $request->get('status', 'Completed');
        
        // Get service requests with their items and related data
        $serviceRequests = \App\Models\ServiceRequest::with([
                'customer',
                'items.airconType',
                'billings'  // Changed from 'billing' to 'billings'
            ])
            ->whereHas('items', function($query) use ($status) {
                $query->where('status', $status);
            })
            ->get();
            
        // Get all statuses for the filter dropdown
        $statuses = ['Pending', 'In Progress', 'Completed'];
        
        return view('billing', compact('serviceRequests', 'status', 'statuses'));
    }

    public function payments()
    {
        $payments = Finance::where('type', 'payment')->get();
        return view('payments', compact('payments'));
    }

    public function expenses()
    {
        $expenses = Finance::where('type', 'expense')->get();
        return view('expenses', compact('expenses'));
    }

    public function accountsReceivable()
    {
        $accountsReceivables = Finance::where('type', 'accounts-receivable')->get();
        return view('accounts-receivable', compact('accountsReceivables'));
    }

    public function accountsPayable()
    {
        $accountsPayables = Finance::where('type', 'accounts-payable')->get();
        return view('accounts-payable', compact('accountsPayables'));
    }

    public function reports()
    {
        // Add your reports logic here
        return view('reports');
    }

    public function inventory()
    {
        // Add your inventory logic here
        return view('inventory');
    }

    public function serviceRequests()
    {
        $serviceRequests = Finance::where('type', 'service-request')->get();
        return view('service-requests', compact('serviceRequests'));
    }

    public function invoices()
    {
        $invoices = Finance::where('type', 'invoice')->get();
        return view('invoices', compact('invoices'));
    }

}
