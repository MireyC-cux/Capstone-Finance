@extends('layouts.finance_app')

@section('title', 'Payroll Approvals')

@section('content')
<div class="relative">
  <div class="absolute inset-0 -z-10 bg-gradient-to-r from-indigo-50 via-cyan-50 to-emerald-50"></div>
  <div class="max-w-7xl mx-auto">
    <div class="mb-6">
      <div class="rounded-3xl p-6 bg-white/70 backdrop-blur shadow-sm border border-white/50">
        <h1 class="text-3xl font-extrabold tracking-tight">Payroll Approvals</h1>
        <p class="text-slate-600 mt-1">Approve or reject pending payrolls for the selected period.</p>
      </div>
    </div>

    <div class="flex items-center justify-between mb-4 sticky top-3 z-10">
      <div></div>
      <div class="flex gap-2 bg-white/80 backdrop-blur px-3 py-2 rounded-2xl shadow border border-white/50">
        <a href="{{ route('finance.payroll') }}" class="px-4 py-2 bg-slate-900/90 hover:bg-slate-900 text-white rounded-2xl shadow">Back to Dashboard</a>
      </div>
    </div>

    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-white/90 backdrop-blur p-5 rounded-2xl shadow border border-white/60 mb-6">
      <div>
        <label class="block text-sm text-slate-600">Period Start</label>
        <input type="date" name="period_start" value="{{ $period_start }}" class="w-full mt-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-cyan-500">
      </div>
      <div>
        <label class="block text-sm text-slate-600">Period End</label>
        <input type="date" name="period_end" value="{{ $period_end }}" class="w-full mt-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-cyan-500">
      </div>
      <div class="md:col-span-2 flex items-end justify-end">
        <button class="px-4 py-2 bg-slate-900/90 text-white rounded-2xl hover:bg-slate-900">Apply Filters</button>
      </div>
    </form>

    <div class="bg-white/95 backdrop-blur rounded-2xl shadow-lg overflow-hidden border border-white/60">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gradient-to-r from-slate-50 to-slate-100 text-left text-slate-700">
                    <th class="px-4 py-3">Employee</th>
                    <th class="px-4 py-3">Pay Period</th>
                    <th class="px-4 py-3">Net Pay</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($payrolls as $p)
                <tr class="odd:bg-white even:bg-slate-50/50">
                    <td class="px-4 py-3">{{ $p->employeeProfile->last_name }}, {{ $p->employeeProfile->first_name }}</td>
                    <td class="px-4 py-3">{{ $p->pay_period }}</td>
                    <td class="px-4 py-3">₱ {{ number_format($p->net_pay,2) }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $p->status==='Pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700' }}">{{ $p->status }}</span>
                    </td>
                    <td class="px-4 py-3 space-x-3">
                        <form method="POST" action="{{ route('finance.payroll.approve', $p->payroll_id) }}" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button class="text-green-700 hover:underline">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('finance.payroll.approve', $p->payroll_id) }}" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button class="text-red-700 hover:underline">Reject</button>
                        </form>
                        <a href="{{ route('finance.payroll.payslip', $p->payroll_id) }}" class="text-brand-700 hover:underline">Preview Payslip</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">No pending or rejected payrolls for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payrolls->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
