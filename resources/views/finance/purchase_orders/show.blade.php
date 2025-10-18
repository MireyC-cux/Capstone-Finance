@extends('layouts.finance_app')

@section('content')
<div class="max-w-6xl mx-auto px-4 md:px-6 lg:px-8 py-6">
  <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-700 mb-4">
    <i class="fa fa-arrow-left"></i><span>Back to POs</span>
  </a>

  <div class="flex items-end justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">PO {{ $po->po_number }}</h1>
      <p class="text-slate-600 mt-1">Supplier {{ $po->supplier->supplier_name ?? '—' }} • Date {{ \Illuminate\Support\Carbon::parse($po->po_date)->format('Y-m-d') }}</p>
    </div>
    <div class="flex gap-2">
      <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border
        @class([
          'bg-slate-100 text-slate-700 border-slate-200' => $po->status==='Pending',
          'bg-emerald-100 text-emerald-800 border-emerald-200' => $po->status==='Approved',
          'bg-rose-100 text-rose-800 border-rose-200' => $po->status==='Rejected',
          'bg-sky-100 text-sky-800 border-sky-200' => $po->status==='Completed',
        ])
      ">{{ $po->status }}</span>
      @if($po->status==='Pending')
        <form method="POST" action="{{ route('purchase-orders.approve',$po->po_id) }}">@csrf
          <button class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-white shadow hover:bg-emerald-700 transition"><i class="fa fa-check"></i><span>Approve</span></button>
        </form>
        <form method="POST" action="{{ route('purchase-orders.reject',$po->po_id) }}">@csrf
          <button class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-white shadow hover:bg-rose-700 transition"><i class="fa fa-xmark"></i><span>Reject</span></button>
        </form>
      @endif
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
      <div class="text-xs uppercase text-slate-500 mb-2">PO Details</div>
      <dl class="text-sm space-y-2">
        <div class="flex justify-between"><dt class="text-slate-600">PO Number</dt><dd class="font-medium">{{ $po->po_number }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-600">Supplier</dt><dd class="font-medium">{{ $po->supplier->supplier_name ?? '—' }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-600">Service Request</dt><dd class="font-medium">{{ $po->service_request_id ?? '—' }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-600">Remarks</dt><dd class="font-medium">{{ $po->remarks ?? '—' }}</dd></div>
      </dl>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
      <div class="text-xs uppercase text-slate-500 mb-2">Amounts</div>
      <dl class="text-sm space-y-2">
        <div class="flex justify-between"><dt class="text-slate-600">Total Amount</dt><dd class="font-extrabold">₱{{ number_format($po->total_amount,2) }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-600">Computed (Items)</dt><dd class="font-medium">₱{{ number_format($po->items->sum(fn($i)=>$i->quantity*$i->unit_price),2) }}</dd></div>
      </dl>
    </div>
  </div>

  <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold text-slate-800">Items</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr class="text-left text-slate-600">
            <th class="px-3 py-2 font-semibold">Description</th>
            <th class="px-3 py-2 font-semibold">Qty</th>
            <th class="px-3 py-2 font-semibold">Unit Price</th>
            <th class="px-3 py-2 font-semibold">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($po->items as $it)
          <tr>
            <td class="px-3 py-2">{{ $it->description ?? '—' }}</td>
            <td class="px-3 py-2">{{ $it->quantity }}</td>
            <td class="px-3 py-2">₱{{ number_format($it->unit_price,2) }}</td>
            <td class="px-3 py-2 font-semibold">₱{{ number_format($it->quantity * $it->unit_price,2) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
