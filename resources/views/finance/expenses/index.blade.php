@extends('layouts.finance_app')

@section('title', 'Expenses')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="mb-6">Expenses</h1>

    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 p-4 bg-white rounded-lg shadow">
            <h2 class="font-semibold mb-3">Add Expense</h2>
            <form method="post" action="{{ route('finance.expenses.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm text-gray-600">Expense Name</label>
                    <input type="text" name="expense_name" class="border rounded px-2 py-1 w-full" required>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Category</label>
                    <select name="category" class="border rounded px-2 py-1 w-full" required>
                        <option>Utilities</option>
                        <option>Maintenance</option>
                        <option>Transportation</option>
                        <option>Office Supplies</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm text-gray-600">Amount</label>
                        <input type="number" step="0.01" name="amount" class="border rounded px-2 py-1 w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Date</label>
                        <input type="date" name="expense_date" value="{{ now()->toDateString() }}" class="border rounded px-2 py-1 w-full" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm text-gray-600">Payment Method</label>
                        <select name="payment_method" class="border rounded px-2 py-1 w-full" required>
                            <option>Cash</option>
                            <option>Bank Transfer</option>
                            <option>GCash</option>
                            <option>Check</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Reference No.</label>
                        <input type="text" name="reference_number" class="border rounded px-2 py-1 w-full" placeholder="Optional">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm text-gray-600">Paid To</label>
                        <input type="text" name="paid_to" class="border rounded px-2 py-1 w-full" placeholder="Optional">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Cash Account ID</label>
                        <input type="text" name="account_id" class="border rounded px-2 py-1 w-full" placeholder="Optional">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Employee ID</label>
                    <input type="text" name="employeeprofiles_id" class="border rounded px-2 py-1 w-full" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Supplier ID</label>
                    <input type="text" name="supplier_id" class="border rounded px-2 py-1 w-full" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Description</label>
                    <textarea name="description" class="border rounded px-2 py-1 w-full" rows="2" placeholder="Optional"></textarea>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Remarks</label>
                    <textarea name="remarks" class="border rounded px-2 py-1 w-full" rows="2" placeholder="Optional"></textarea>
                </div>
                <div>
                    <button class="px-4 py-2 bg-amber-600 text-white rounded">Save Expense</button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 p-4 bg-white rounded-lg shadow">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">Recent Expenses</h2>
                <a class="text-sm text-blue-600 hover:underline" href="{{ route('finance.cashflow') }}">Go to Cash Flow Dashboard</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="py-2 pr-4">Date</th>
                            <th class="py-2 pr-4">Name</th>
                            <th class="py-2 pr-4">Category</th>
                            <th class="py-2 pr-4 text-right">Amount</th>
                            <th class="py-2 pr-4">Method</th>
                            <th class="py-2 pr-4">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $e)
                            <tr class="border-b last:border-0">
                                <td class="py-2 pr-4">{{ \Illuminate\Support\Carbon::parse($e->expense_date)->format('Y-m-d') }}</td>
                                <td class="py-2 pr-4">{{ $e->expense_name }}</td>
                                <td class="py-2 pr-4">{{ $e->category }}</td>
                                <td class="py-2 pr-4 text-right">₱ {{ number_format((float)$e->amount, 2) }}</td>
                                <td class="py-2 pr-4">{{ $e->payment_method }}</td>
                                <td class="py-2 pr-4">{{ $e->reference_number }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $expenses->links() }}</div>
        </div>
    </div>
</div>
@endsection
