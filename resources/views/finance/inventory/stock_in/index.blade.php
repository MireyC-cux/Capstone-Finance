@extends('layouts.finance_app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6">
  <div class="flex items-end justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">Stock-In</h1>
      <p class="text-slate-600 mt-1">Record received items from purchase orders or direct receiving.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('finance.inventory.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-slate-700 hover:bg-slate-50 transition"><i class="fa fa-arrow-left"></i><span>Back to Dashboard</span></a>
    </div>
  </div>

  @if(session('success'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Success',text:'{{ session('success') }}',confirmButtonColor:'#06b6d4'}));</script>
  @endif

  <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50/80 backdrop-blur sticky top-0">
          <tr class="text-left text-slate-600">
            <th class="px-4 py-3 font-semibold">Date</th>
            <th class="px-4 py-3 font-semibold">Item</th>
            <th class="px-4 py-3 font-semibold text-right">Qty</th>
            <th class="px-4 py-3 font-semibold text-right">Unit Cost</th>
            <th class="px-4 py-3 font-semibold text-right">Total</th>
            <th class="px-4 py-3 font-semibold">PO</th>
            <th class="px-4 py-3 font-semibold">Remarks</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($rows as $r)
          <tr class="hover:bg-slate-50 transition">
            <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($r->received_date)->format('Y-m-d') }}</td>
            <td class="px-4 py-3">{{ $r->item->item_name ?? '—' }}</td>
            <td class="px-4 py-3 text-right">{{ $r->quantity }}</td>
            <td class="px-4 py-3 text-right">₱{{ number_format($r->unit_cost,2) }}</td>
            <td class="px-4 py-3 text-right">₱{{ number_format($r->quantity * $r->unit_cost,2) }}</td>
            <td class="px-4 py-3">{{ $r->purchase_order_id ? ('PO#'.$r->purchase_order_id) : '—' }}</td>
            <td class="px-4 py-3">{{ $r->remarks }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">{{ $rows->links() }}</div>
</div>
@endsection
