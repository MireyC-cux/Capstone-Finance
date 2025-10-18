@extends('layouts.finance_app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6">
  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">AP Aging Report</h1>
      <p class="text-slate-600 mt-1">Outstanding payables grouped by supplier and aging buckets.</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('accounts-payable.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa fa-file-invoice"></i><span>Back to AP</span>
      </a>
    </div>
  </div>

  @if(session('success'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Success',text:'{{ session('success') }}',confirmButtonColor:'#06b6d4'}));</script>
  @endif
  @if(session('error'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'error',title:'Error',text:'{{ session('error') }}'}));</script>
  @endif

  <form method="GET" action="{{ route('reports.ap-aging') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6 p-4 bg-white/70 backdrop-blur rounded-2xl shadow-sm border border-slate-200">
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Supplier</label>
      <select name="supplier_id" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600">
        <option value="">All</option>
        @foreach($suppliers as $s)
          <option value="{{ $s->supplier_id }}" @selected(($filters['supplier_id'] ?? '')==$s->supplier_id)>{{ $s->supplier_name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
      <select name="status" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600">
        <option value="">All</option>
        @foreach(['Unpaid','Partially Paid','Paid','Overdue','Cancelled'] as $st)
          <option value="{{ $st }}" @selected(($filters['status'] ?? '')==$st)>{{ $st }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">From</label>
      <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">To</label>
      <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
    </div>
    <div class="md:col-span-5 flex gap-2">
      <button class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition"><i class="fa fa-filter"></i>Filter</button>
      <a href="{{ route('reports.ap-aging') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200 transition"><i class="fa fa-rotate"></i>Reset</a>
    </div>
  </form>

  @php
    $sumCurrent=0; $sum1_30=0; $sum31_60=0; $sum61_90=0; $sum91p=0; $sumTotal=0;
  @endphp

  <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50/80 backdrop-blur sticky top-0">
          <tr class="text-left text-slate-600">
            <th class="px-4 py-3 font-semibold">Supplier</th>
            <th class="px-4 py-3 font-semibold text-right">Current</th>
            <th class="px-4 py-3 font-semibold text-right">1–30</th>
            <th class="px-4 py-3 font-semibold text-right">31–60</th>
            <th class="px-4 py-3 font-semibold text-right">61–90</th>
            <th class="px-4 py-3 font-semibold text-right">91+</th>
            <th class="px-4 py-3 font-semibold text-right">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($rows as $r)
            @php
              $b = $r['buckets'];
              $sumCurrent += $b['current'];
              $sum1_30   += $b['d1_30'];
              $sum31_60  += $b['d31_60'];
              $sum61_90  += $b['d61_90'];
              $sum91p    += $b['d91p'];
              $sumTotal  += $b['total'];
            @endphp
            <tr class="hover:bg-slate-50 transition">
              <td class="px-4 py-3 font-medium">{{ $r['supplier_name'] }}</td>
              <td class="px-4 py-3 text-right">₱{{ number_format($b['current'],2) }}</td>
              <td class="px-4 py-3 text-right">₱{{ number_format($b['d1_30'],2) }}</td>
              <td class="px-4 py-3 text-right">₱{{ number_format($b['d31_60'],2) }}</td>
              <td class="px-4 py-3 text-right">₱{{ number_format($b['d61_90'],2) }}</td>
              <td class="px-4 py-3 text-right">₱{{ number_format($b['d91p'],2) }}</td>
              <td class="px-4 py-3 text-right font-semibold">₱{{ number_format($b['total'],2) }}</td>
            </tr>
          @empty
            <tr><td class="px-4 py-6 text-slate-500" colspan="7">No data for selected filters.</td></tr>
          @endforelse
        </tbody>
        <tfoot class="bg-slate-50/80">
          <tr class="text-left text-slate-700">
            <th class="px-4 py-3 font-semibold">Grand Total</th>
            <th class="px-4 py-3 font-semibold text-right">₱{{ number_format($sumCurrent,2) }}</th>
            <th class="px-4 py-3 font-semibold text-right">₱{{ number_format($sum1_30,2) }}</th>
            <th class="px-4 py-3 font-semibold text-right">₱{{ number_format($sum31_60,2) }}</th>
            <th class="px-4 py-3 font-semibold text-right">₱{{ number_format($sum61_90,2) }}</th>
            <th class="px-4 py-3 font-semibold text-right">₱{{ number_format($sum91p,2) }}</th>
            <th class="px-4 py-3 font-extrabold text-right">₱{{ number_format($sumTotal,2) }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endsection
