@extends('layouts.finance_app')

@section('content')
<div class="max-w-5xl mx-auto px-4 md:px-6 lg:px-8 py-6">
  <a href="{{ route('finance.accounts-payable') }}" class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-700 mb-4">
    <i class="fa fa-arrow-left"></i><span>Back to AP</span>
  </a>

  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">AP #{{ $ap->ap_id }}</h1>
      <p class="text-slate-600 mt-1">Invoice {{ $ap->invoice_number }} • Supplier {{ $ap->supplier->supplier_name ?? '—' }}</p>
    </div>
    <div class="flex gap-2">
      <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border
        @class([
          'bg-slate-100 text-slate-700 border-slate-200' => $ap->status==='Unpaid',
          'bg-amber-100 text-amber-800 border-amber-200' => $ap->status==='Partially Paid',
          'bg-emerald-100 text-emerald-800 border-emerald-200' => $ap->status==='Paid',
          'bg-rose-100 text-rose-800 border-rose-200' => $ap->status==='Overdue',
          'bg-slate-100 text-slate-500 border-slate-200' => $ap->status==='Cancelled',
        ])
      ">{{ $ap->status }}</span>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
      <div class="text-xs uppercase text-slate-500 mb-2">AP Details</div>
      <dl class="text-sm space-y-2">
        <div class="flex justify-between"><dt class="text-slate-600">Invoice Date</dt><dd class="font-medium">{{ \Illuminate\Support\Carbon::parse($ap->invoice_date)->format('Y-m-d') }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-600">Due Date</dt><dd class="font-medium">{{ \Illuminate\Support\Carbon::parse($ap->due_date)->format('Y-m-d') }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-600">PO #</dt><dd class="font-medium">{{ $ap->purchaseOrder->po_number ?? '—' }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-600">Payment Terms</dt><dd class="font-medium">{{ $ap->payment_terms ?? '—' }}</dd></div>
      </dl>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
      <div class="text-xs uppercase text-slate-500 mb-2">Amounts</div>
      <dl class="text-sm space-y-2">
        <div class="flex justify-between"><dt class="text-slate-600">Total Amount</dt><dd class="font-extrabold">₱{{ number_format($ap->total_amount,2) }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-600">Amount Paid</dt><dd class="font-bold text-emerald-700">₱{{ number_format($ap->amount_paid,2) }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-600">Balance</dt><dd class="font-extrabold text-rose-700">₱{{ number_format(max(0, $ap->total_amount - $ap->amount_paid),2) }}</dd></div>
      </dl>
    </div>
  </div>

  <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm mb-6">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold text-slate-800">Payment History</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr class="text-left text-slate-600">
            <th class="px-3 py-2 font-semibold">Date</th>
            <th class="px-3 py-2 font-semibold">Method</th>
            <th class="px-3 py-2 font-semibold">Reference</th>
            <th class="px-3 py-2 font-semibold">Amount</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($ap->payments as $p)
          <tr>
            <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($p->payment_date)->format('Y-m-d') }}</td>
            <td class="px-3 py-2">{{ $p->payment_method }}</td>
            <td class="px-3 py-2">{{ $p->reference_number ?? '—' }}</td>
            <td class="px-3 py-2 font-semibold">₱{{ number_format($p->amount,2) }}</td>
          </tr>
          @empty
          <tr><td class="px-3 py-4 text-slate-500" colspan="4">No payments yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div id="record-payment" class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
    <h2 class="font-semibold text-slate-800 mb-3">Record Payment</h2>
    @php($remaining = max(0, (float)$ap->total_amount - (float)$ap->amount_paid))
    @if($ap->status === 'Paid' || $remaining <= 0)
      <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 p-3 flex items-center gap-2">
        <i class="fa fa-circle-check"></i>
        <span>This AP is fully paid. No further payments are required.</span>
      </div>
    @else
    <form id="recordPaymentForm" method="POST" action="{{ route('payments-made.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3" data-remaining="{{ number_format($remaining,2,'.','') }}">
      @csrf
      <input type="hidden" name="ap_id" value="{{ $ap->ap_id }}" />
      <div>
        <label class="block text-xs text-slate-600 mb-1">Payment Date</label>
        <input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required />
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Method</label>
        <select name="payment_method" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required>
          <option>Cash</option>
          <option>Bank Transfer</option>
          <option>Check</option>
          <option>Other</option>
        </select>
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Amount</label>
        <input id="paymentAmount" type="number" step="0.01" min="0.01" name="amount" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required />
        <div class="text-xs text-slate-500 mt-1">Remaining balance: ₱{{ number_format($remaining,2) }}</div>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs text-slate-600 mb-1">Reference (optional)</label>
        <input type="text" name="reference_number" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
      </div>
      <div class="md:col-span-5 flex gap-2">
        <button class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition"><i class="fa fa-save"></i><span>Save Payment</span></button>
        <a href="{{ route('finance.accounts-payable') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200 transition"><i class="fa fa-xmark"></i><span>Cancel</span></a>
      </div>
    </form>
    @endif
  </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('recordPaymentForm');
  if(!form) return;
  form.addEventListener('submit', function(e){
    const remaining = parseFloat(form.dataset.remaining || '0');
    const amount = parseFloat((document.getElementById('paymentAmount')?.value || '0'));
    if (!isFinite(amount) || amount <= 0) {
      e.preventDefault();
      Swal.fire({icon:'warning', title:'Invalid amount', text:'Please enter a valid payment amount greater than 0.'});
      return;
    }
    if (amount - remaining > 1e-6) {
      e.preventDefault();
      Swal.fire({icon:'error', title:'Amount exceeds balance', text:`You entered ₱${amount.toFixed(2)} but the remaining balance is only ₱${remaining.toFixed(2)}.`});
      return;
    }
  });
});
</script>
@endpush
</div>
@endsection
