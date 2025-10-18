@extends('layouts.finance_app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6">

  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">Accounts Payable</h1>
      <p class="text-slate-600 mt-1">Track, filter, and settle supplier invoices.</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa fa-list"></i><span>View POs</span>
      </a>
      <form method="POST" action="{{ route('accounts-payable.mark-overdues') }}">@csrf
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-white shadow hover:bg-amber-600 transition">
          <i class="fa fa-triangle-exclamation"></i><span>Mark Overdues</span>
        </button>
      </form>
      <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition">
        <i class="fa fa-plus"></i><span>Create PO</span>
      </a>
    </div>
  </div>

  @if(session('success'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Success',text:'{{ session('success') }}',confirmButtonColor:'#06b6d4'}));</script>
  @endif
  @if(session('error'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'error',title:'Error',text:'{{ session('error') }}'}));</script>
  @endif

  <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6 p-4 bg-white/70 backdrop-blur rounded-2xl shadow-sm border border-slate-200">
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Supplier</label>
      <select name="supplier_id" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600">
        <option value="">All</option>
        @foreach($suppliers as $s)
          <option value="{{ $s->supplier_id }}" @selected(request('supplier_id')==$s->supplier_id)>{{ $s->supplier_name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
      <select name="status" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600">
        <option value="">All</option>
        @foreach(['Unpaid','Partially Paid','Paid','Overdue','Cancelled'] as $st)
          <option value="{{ $st }}" @selected(request('status')==$st)>{{ $st }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">PO#</label>
      <input type="text" name="po_number" value="{{ request('po_number') }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">From</label>
      <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">To</label>
      <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
    </div>
    <div class="md:col-span-5 flex gap-2">
      <button class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition"><i class="fa fa-filter"></i>Filter</button>
      <a href="{{ route('accounts-payable.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200 transition"><i class="fa fa-rotate"></i>Reset</a>
    </div>
  </form>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl bg-gradient-to-br from-white to-sky-50 border border-sky-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-sky-600">Total Payables</div>
      <div class="text-2xl font-extrabold mt-1">₱{{ number_format($stats['total'] ?? 0,2) }}</div>
    </div>
    <div class="rounded-2xl bg-gradient-to-br from-white to-emerald-50 border border-emerald-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-emerald-600">Paid</div>
      <div class="text-2xl font-extrabold mt-1">₱{{ number_format($stats['paid'] ?? 0,2) }}</div>
    </div>
    <div class="rounded-2xl bg-gradient-to-br from-white to-rose-50 border border-rose-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-rose-600">Overdue</div>
      <div class="text-2xl font-extrabold mt-1">{{ $stats['overdue'] ?? 0 }}</div>
    </div>
    <div class="rounded-2xl bg-gradient-to-br from-white to-amber-50 border border-amber-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-amber-600">Partially Paid</div>
      <div class="text-2xl font-extrabold mt-1">{{ $stats['partial'] ?? 0 }}</div>
    </div>
  </div>

  <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50/80 backdrop-blur sticky top-0">
          <tr class="text-left text-slate-600">
            <th class="px-4 py-3 font-semibold">PO#</th>
            <th class="px-4 py-3 font-semibold">Supplier</th>
            <th class="px-4 py-3 font-semibold">Invoice#</th>
            <th class="px-4 py-3 font-semibold">Due Date</th>
            <th class="px-4 py-3 font-semibold">Total</th>
            <th class="px-4 py-3 font-semibold">Paid</th>
            <th class="px-4 py-3 font-semibold">Balance</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($payables as $ap)
          <tr class="@if($ap->is_overdue && $ap->status!=='Paid') bg-rose-50/60 @endif hover:bg-slate-50 transition">
            <td class="px-4 py-3 font-mono text-slate-700">{{ $ap->purchaseOrder->po_number ?? '—' }}</td>
            <td class="px-4 py-3">{{ $ap->supplier->supplier_name ?? '—' }}</td>
            <td class="px-4 py-3">{{ $ap->invoice_number }}</td>
            <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($ap->due_date)->format('Y-m-d') }}</td>
            <td class="px-4 py-3">₱{{ number_format($ap->total_amount,2) }}</td>
            <td class="px-4 py-3">₱{{ number_format($ap->amount_paid,2) }}</td>
            <td class="px-4 py-3 font-semibold">₱{{ number_format(max(0, $ap->total_amount - $ap->amount_paid),2) }}</td>
            <td class="px-4 py-3">
              @php($badgeMap = [
                'Unpaid' => 'bg-slate-100 text-slate-700 border-slate-200',
                'Partially Paid' => 'bg-amber-100 text-amber-800 border-amber-200',
                'Paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'Overdue' => 'bg-rose-100 text-rose-800 border-rose-200',
                'Cancelled' => 'bg-slate-100 text-slate-500 border-slate-200',
              ])
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeMap[$ap->status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $ap->status }}</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap items-center gap-2">
                <a class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition" href="{{ route('accounts-payable.show',$ap->ap_id) }}"><i class="fa fa-eye"></i><span>View</span></a>
                <a class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 bg-brand-600 text-white hover:bg-brand-700 shadow-sm transition" href="{{ route('accounts-payable.show',$ap->ap_id) }}#record-payment"><i class="fa fa-coins"></i><span>Record Payment</span></a>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">{{ $payables->links() }}</div>
</div>
@endsection
