<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Expenses;
use App\Models\CashFlow;
use App\Models\ActivityLog;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $q = Expenses::query()->orderByDesc('expense_date')->orderByDesc('expense_id');
        if ($request->filled('category')) {
            $q->where('category', $request->category);
        }
        if ($request->filled('start')) {
            $q->where('expense_date', '>=', Carbon::parse($request->start)->toDateString());
        }
        if ($request->filled('end')) {
            $q->where('expense_date', '<=', Carbon::parse($request->end)->toDateString());
        }
        $expenses = $q->paginate(25);
        return view('finance.expenses.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_name' => 'required|string|max:255',
            'category' => 'required|in:Utilities,Maintenance,Transportation,Office Supplies,Other',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'paid_to' => 'nullable|string|max:255',
            'payment_method' => 'required|in:Cash,Bank Transfer,GCash,Check,Other',
            'reference_number' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'description' => 'nullable|string',
            'employeeprofiles_id' => 'nullable|integer|exists:employeeprofiles,employeeprofiles_id',
            'supplier_id' => 'nullable|integer|exists:suppliers,supplier_id',
            'account_id' => 'nullable|integer|exists:cash_accounts,account_id',
        ]);

        return DB::transaction(function () use ($data) {
            $expense = Expenses::create([
                'employeeprofiles_id' => $data['employeeprofiles_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'expense_name' => $data['expense_name'],
                'category' => $data['category'],
                'amount' => $data['amount'],
                'expense_date' => Carbon::parse($data['expense_date'])->toDateString(),
                'paid_to' => $data['paid_to'] ?? null,
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            CashFlow::create([
                'transaction_type' => 'Outflow',
                'source_type' => 'Expense',
                'source_id' => $expense->expense_id,
                'account_id' => $data['account_id'] ?? null,
                'amount' => $expense->amount,
                'transaction_date' => $expense->expense_date,
                'description' => 'Expense: ' . $expense->expense_name,
            ]);

            ActivityLog::create([
                'event_type' => 'expense_recorded',
                'title' => 'Expense recorded: '.$expense->expense_name.' (₱'.number_format((float)$expense->amount, 2).')',
                'context_type' => 'Expense',
                'context_id' => $expense->expense_id,
                'amount' => $expense->amount,
                'meta' => [
                    'category' => $expense->category,
                    'payment_method' => $expense->payment_method,
                    'reference_number' => $expense->reference_number,
                ],
            ]);

            return back()->with('success', 'Expense recorded successfully.');
        });
    }
}
