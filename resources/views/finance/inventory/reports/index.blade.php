@extends('layouts.finance_app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6">
  <div class="flex items-end justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">Inventory Reports</h1>
      <p class="text-slate-600 mt-1">Summary, usage, purchase history, and valuation.</p>
    </div>
    <form method="GET" class="flex items-end gap-3">
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Start</label>
        <input type="date" name="start" value="{{ $start }}" class="rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">End</label>
        <input type="date" name="end" value="{{ $end }}" class="rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
      </div>
      <a href="{{ route('finance.inventory.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-slate-700 hover:bg-slate-50 transition"><i class="fa fa-arrow-left"></i><span>Back to Dashboard</span></a>
      <button class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition"><i class="fa fa-filter"></i><span>Apply</span></button>
    </form>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm lg:col-span-2">
      <div class="flex items-center justify-between mb-3"><h2 class="font-semibold text-slate-800">Usage by Item</h2></div>
      <div class="h-72"><canvas id="usageChart" style="height: 18rem; width: 100%;"></canvas></div>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
      <div class="flex items-center justify-between mb-3"><h2 class="font-semibold text-slate-800">Valuation</h2></div>
      <div class="h-72"><canvas id="valuationChart" style="height: 18rem; width: 100%;"></canvas></div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
      <div class="flex items-center justify-between mb-3"><h2 class="font-semibold text-slate-800">Purchase History (by Supplier)</h2></div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50">
            <tr class="text-left text-slate-600">
              <th class="px-3 py-2">Supplier</th>
              <th class="px-3 py-2 text-right">Entries</th>
              <th class="px-3 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($purchases as $p)
            <tr>
              <td class="px-3 py-2">{{ $p->supplier_name ?? 'Unknown' }}</td>
              <td class="px-3 py-2 text-right">{{ $p->entries }}</td>
              <td class="px-3 py-2 text-right">₱{{ number_format($p->total,2) }}</td>
            </tr>
            @empty
            <tr><td class="px-3 py-4 text-slate-500" colspan="3">No purchase data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
      <div class="flex items-center justify-between mb-3"><h2 class="font-semibold text-slate-800">Stock Summary</h2></div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50">
            <tr class="text-left text-slate-600">
              <th class="px-3 py-2">Item</th>
              <th class="px-3 py-2">Category</th>
              <th class="px-3 py-2">Brand/Model</th>
              <th class="px-3 py-2 text-right">Stock</th>
              <th class="px-3 py-2 text-right">Reorder</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($summary as $r)
            <tr>
              <td class="px-3 py-2">{{ $r->item_name }}</td>
              <td class="px-3 py-2">{{ $r->category }}</td>
              <td class="px-3 py-2">{{ $r->brand }} {{ $r->model }}</td>
              <td class="px-3 py-2 text-right">{{ $r->stock }}</td>
              <td class="px-3 py-2 text-right">{{ $r->reorder_level }}</td>
            </tr>
            @empty
            <tr><td class="px-3 py-4 text-slate-500" colspan="5">No summary data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const usage = @json($usage);
const usageLabels = usage.map(r => `${r.category} - ${r.item_name}`);
const usageData = usage.map(r => Number(r.qty));
new Chart(document.getElementById('usageChart'), {
  type: 'bar',
  data: { labels: usageLabels, datasets: [{ label: 'Qty Used', data: usageData, backgroundColor: '#06b6d4' }]},
  options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2, scales: { y: { beginAtZero: true } } }
});

const valuation = @json($valuation);
const valLabels = valuation.map(r => r.item_name);
const valData = valuation.map(r => Number(r.valuation));
new Chart(document.getElementById('valuationChart'), {
  type: 'doughnut',
  data: { labels: valLabels, datasets: [{ label: 'Value', data: valData, backgroundColor: valLabels.map(()=> '#0ea5e9') }]},
  options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
@endsection
