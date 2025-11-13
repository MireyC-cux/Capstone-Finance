@extends('layouts.finance_app')

@section('content')
<div class="container-xxl py-3">
  <div class="inv-hero p-4 p-md-5 mb-4 position-relative overflow-hidden">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h1 class="mb-1">Inventory Reports</h1>
        <p class="text-muted mb-0">Summary, usage, purchase history, and valuation.</p>
      </div>
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
          <label class="form-label small mb-1">Start</label>
          <input type="date" name="start" value="{{ $start }}" class="form-control" />
        </div>
        <div class="col-auto">
          <label class="form-label small mb-1">End</label>
          <input type="date" name="end" value="{{ $end }}" class="form-control" />
        </div>
        <div class="col-auto d-flex gap-2">
          <a href="{{ route('finance.inventory.dashboard') }}" class="btn btn-light border btn-ghost"><i class="fa fa-arrow-left me-2"></i><span>Dashboard</span></a>
          <button class="btn btn-gradient" type="submit"><i class="fa fa-filter me-2"></i><span>Apply</span></button>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3"><h2 class="h6 mb-0">Usage by Item</h2></div>
          <div class="w-100"><canvas id="usageChart" style="height: 18rem; width: 100%;"></canvas></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3"><h2 class="h6 mb-0">Valuation</h2></div>
          <div class="w-100"><canvas id="valuationChart" style="height: 18rem; width: 100%;"></canvas></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-3">
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3"><h2 class="h6 mb-0">Purchase History (by Supplier)</h2></div>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead class="table-light">
                <tr class="text-nowrap">
                  <th>Supplier</th>
                  <th class="text-end">Entries</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody>
                @forelse($purchases as $p)
                <tr>
                  <td>{{ $p->supplier_name ?? 'Unknown' }}</td>
                  <td class="text-end">{{ $p->entries }}</td>
                  <td class="text-end">₱{{ number_format($p->total,2) }}</td>
                </tr>
                @empty
                <tr><td class="py-4 text-muted" colspan="3">No purchase data.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3"><h2 class="h6 mb-0">Stock Summary</h2></div>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead class="table-light">
                <tr class="text-nowrap">
                  <th>Item</th>
                  <th>Category</th>
                  <th>Brand/Model</th>
                  <th class="text-end">Stock</th>
                  <th class="text-end">Reorder</th>
                </tr>
              </thead>
              <tbody>
                @forelse($summary as $r)
                <tr>
                  <td>{{ $r->item_name }}</td>
                  <td>{{ $r->category }}</td>
                  <td>{{ $r->brand }} {{ $r->model }}</td>
                  <td class="text-end">{{ $r->stock }}</td>
                  <td class="text-end">{{ $r->reorder_level }}</td>
                </tr>
                @empty
                <tr><td class="py-4 text-muted" colspan="5">No summary data.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>

@push('styles')
<style>
.btn-ghost{border-radius:12px;background:#fff;color:#0d6efd;border-color:#cfe2ff}
.btn-ghost:hover{background:#f1f6ff;color:#0a58ca;box-shadow:0 6px 18px rgba(13,110,253,.15)}
.btn-gradient{border-radius:12px;background:linear-gradient(90deg,#e65c33,#f57c42);color:#fff;border:0;padding:.5rem 1rem;box-shadow:0 4px 14px rgba(230,92,51,.3)}
.btn-gradient:hover{filter:brightness(.95);color:#fff}
.inv-hero{border-radius:24px;background:radial-gradient(700px 200px at 100% 0,rgba(59,130,246,.08),transparent),linear-gradient(180deg,#ffffff,#f8fafc);border:1px solid #e8ecf1;box-shadow:0 10px 26px rgba(2,6,23,.06)}
.inv-hero h1{font-weight:700}
</style>
@endpush

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
