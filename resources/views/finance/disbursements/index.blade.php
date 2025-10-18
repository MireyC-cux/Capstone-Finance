@extends('layouts.finance_app')

@section('title', 'Disbursed Payroll')

@section('content')
<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-bold">Disbursed Payroll</h1>
      <p class="text-slate-500">All payroll disbursements within the selected period.</p>
    </div>
    <div class="flex gap-2">
      <form method="GET" action="{{ route('finance.disbursement.export') }}">
        <input type="hidden" name="start" value="{{ $start }}">
        <input type="hidden" name="end" value="{{ $end }}">
        <button class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl shadow">Export PDF</button>
      </form>
      <a href="{{ route('finance.payroll') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-900 rounded-2xl shadow">Back to Payroll</a>
    </div>
  </div>

  <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-white p-4 rounded-xl shadow mb-6">
    <div>
      <label class="block text-sm text-slate-600">Start</label>
      <input type="date" name="start" value="{{ $start }}" class="w-full mt-1 border rounded-lg px-3 py-2">
    </div>
    <div>
      <label class="block text-sm text-slate-600">End</label>
      <input type="date" name="end" value="{{ $end }}" class="w-full mt-1 border rounded-lg px-3 py-2">
    </div>
    <div class="md:col-span-1 flex items-end">
      <button class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900">Apply Filters</button>
    </div>
  </form>

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="min-w-full">
      <thead>
        <tr class="bg-slate-50 text-left text-slate-600">
          <th class="px-4 py-3">Payment Date</th>
          <th class="px-4 py-3">Employee</th>
          <th class="px-4 py-3">Method</th>
          <th class="px-4 py-3">Reference</th>
          <th class="px-4 py-3 text-right">Amount</th>
          <th class="px-4 py-3">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($rows as $d)
        <tr>
          <td class="px-4 py-3">{{ $d->payment_date }}</td>
          <td class="px-4 py-3">{{ $d->employeeProfile->last_name }}, {{ $d->employeeProfile->first_name }}</td>
          <td class="px-4 py-3">{{ $d->payment_method }}</td>
          <td class="px-4 py-3">{{ $d->reference_number }}</td>
          <td class="px-4 py-3 text-right">₱ {{ number_format($d->payroll->net_pay ?? 0, 2) }}</td>
          <td class="px-4 py-3">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">{{ $d->status }}</span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="px-4 py-6 text-center text-slate-500">No disbursements found for this period.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $rows->withQueryString()->links() }}
  </div>
</div>
@endsection
