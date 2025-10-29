<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
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
                'status' => 'Unpaid',
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

    public function summary($id)
    {
        $expense = Expenses::with('supplier')->findOrFail($id);
        $paid = (float) CashFlow::where('transaction_type','Outflow')
            ->where('source_type','Expense')
            ->where('source_id',$expense->expense_id)
            ->sum('amount');
        $total = (float) $expense->amount;
        $out = max(0.0, $total - $paid);
        return response()->json([
            'id' => $expense->expense_id,
            'name' => $expense->expense_name,
            'category' => $expense->category,
            'supplier' => $expense->supplier->supplier_name ?? null,
            'date' => Carbon::parse($expense->expense_date)->format('Y-m-d'),
            'status' => $expense->status ?? 'Unpaid',
            'total' => $total,
            'paid' => $paid,
            'outstanding' => $out,
        ]);
    }

    public function recordPayment(Request $request, $id)
    {
        $data = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:Cash,GCash,Bank Transfer,Check',
            'payment_type' => 'required|string|in:Full,Partial',
            'reference_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'or_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf',
            'account_id' => 'nullable|integer|exists:cash_accounts,account_id',
        ]);

        return DB::transaction(function () use ($data, $id, $request) {
            $expense = Expenses::lockForUpdate()->findOrFail($id);

            $paid = (float) CashFlow::where('transaction_type','Outflow')
                ->where('source_type','Expense')
                ->where('source_id',$expense->expense_id)
                ->sum('amount');
            $total = (float) $expense->amount;
            $outstanding = max(0.0, $total - $paid);

            if (($data['payment_type'] ?? 'Full') === 'Full') {
                if (abs(((float)$data['amount']) - $outstanding) > 0.009) {
                    return back()->with('error', 'Full payment must match the outstanding balance.');
                }
            } else {
                if (!(((float)$data['amount']) > 0 && ((float)$data['amount']) < $outstanding)) {
                    return back()->with('error', 'Partial payment must be greater than 0 and less than the outstanding balance.');
                }
            }

            $isCash = strtolower($data['payment_method']) === 'cash';
            if (!$isCash && empty($data['reference_number'])) {
                return back()->with('error', 'Reference number is required for non-cash payments.');
            }

            $orPath = null;
            if (!$isCash) {
                if (!$request->hasFile('or_file')) {
                    return back()->with('error', 'Official Receipt (image/PDF) is required for non-cash payments.');
                }
                $orPath = $request->file('or_file')->store('or_uploads', 'public');
            }

            $cf = [
                'transaction_type' => 'Outflow',
                'source_type' => 'Expense',
                'source_id' => (int)$expense->expense_id,
                'account_id' => $data['account_id'] ?? null,
                'amount' => number_format((float)$data['amount'], 2, '.', ''),
                'transaction_date' => Carbon::parse($data['payment_date'])->toDateString(),
                'description' => 'Expense payment: '.$expense->expense_name.(!empty($data['reference_number']) ? (' (Ref: '.$data['reference_number'].')') : ''),
            ];
            if ($orPath && Schema::hasColumn('cash_flow', 'or_file_path')) {
                $cf['or_file_path'] = $orPath;
            }
            CashFlow::create($cf);

            // Update expense status
            $newPaid = $paid + (float)$data['amount'];
            if ($newPaid + 0.0001 >= $total) {
                $expense->status = 'Paid';
            } else {
                $expense->status = Carbon::parse($expense->expense_date)->isPast() ? 'Overdue' : 'Unpaid';
            }
            $expense->save();

            return redirect()->back()->with('success', 'Expense payment recorded successfully.');
        });
    }
}
