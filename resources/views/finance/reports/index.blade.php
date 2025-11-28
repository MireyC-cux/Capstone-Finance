@extends('layouts.finance_app')

@section('title', 'Financial Report')

@section('content')
<div class="container-fluid py-3">
  <div class="d-flex align-items-end gap-3 flex-wrap mb-3">
    <div>
      <h1 class="h3 m-0">Financial Report Dashboard</h1>
      <small class="text-muted">KPIs and financial insights for revenue, expenses, payroll, and cash flow</small>
    </div>
    <form id="filters" class="ms-auto d-flex gap-2 align-items-end flex-wrap">
      <div>
        <label class="form-label mb-1">Year</label>
        <input type="number" name="year" id="year" class="form-control" value="{{ now()->year }}" min="2000" max="2100" />
      </div>
      <div>
        <label class="form-label mb-1">Month (optional)</label>
        <select name="month" id="month" class="form-select">
          <option value="">All</option>
          @for ($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
          @endfor
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Apply</button>
      <button type="button" id="resetFilters" class="btn btn-outline-secondary">Reset</button>
    </form>
  </div>

  <!-- KPI Cards -->
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-3 col-lg-2">
      <div class="card h-100 report-card" role="button" tabindex="0" data-report="revenue" title="View revenue summary">
        <div class="card-body">
          <div class="text-muted">Total Revenue</div>
          <div id="kpi-revenue" class="fs-4 fw-bold">₱0.00</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3 col-lg-2">
      <div class="card h-100 report-card" role="button" tabindex="0" data-report="expenses" title="View expenses summary">
        <div class="card-body">
          <div class="text-muted">Total Expenses</div>
          <div id="kpi-expenses" class="fs-4 fw-bold">₱0.00</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3 col-lg-2">
      <div class="card h-100 report-card" role="button" tabindex="0" data-report="payroll" title="View payroll summary">
        <div class="card-body">
          <div class="text-muted">Total Payroll</div>
          <div id="kpi-payroll" class="fs-4 fw-bold">₱0.00</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3 col-lg-2">
      <div class="card h-100 report-card" role="button" tabindex="0" data-report="net" title="View net profit summary">
        <div class="card-body">
          <div class="text-muted">Net Profit</div>
          <div id="kpi-net" class="fs-4 fw-bold">₱0.00</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3 col-lg-2">
      <div class="card h-100 report-card" role="button" tabindex="0" data-report="margin" title="View profit margin summary">
        <div class="card-body">
          <div class="text-muted">Profit Margin</div>
          <div id="kpi-margin" class="fs-4 fw-bold">0.00%</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header">Monthly Revenue Trend</div>
        <div class="card-body"><canvas id="revenueChart" height="140"></canvas></div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header">Expense Breakdown</div>
        <div class="card-body"><canvas id="expenseChart" height="140"></canvas></div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header">Payroll Summary</div>
        <div class="card-body"><canvas id="payrollChart" height="140"></canvas></div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header">Net Profit Analysis</div>
        <div class="card-body"><canvas id="profitChart" height="140"></canvas></div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header">Cash Flow Overview</div>
        <div class="card-body"><canvas id="cashFlowChart" height="140"></canvas></div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header">Tax Summary</div>
        <div class="card-body"><canvas id="taxChart" height="140"></canvas></div>
      </div>
    </div>

    
  </div>

  <div class="mt-3 d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary" disabled>Export PDF (coming soon)</button>
    <button type="button" class="btn btn-outline-secondary" disabled>Export Excel (coming soon)</button>
  </div>
</div>
<!-- Modal for quick KPI summary -->
<div class="modal fade" id="kpiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="kpiModalTitle">Summary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="kpiModalLoading" class="d-flex align-items-center gap-2">
          <div class="spinner-border text-primary" role="status" style="width: 1.5rem; height: 1.5rem;"></div>
          <span>Loading...</span>
        </div>
        <div id="kpiModalContent" class="d-none">
          <div class="row g-3 mb-2">
            <div class="col-6">
              <div class="text-muted">Current Value</div>
              <div id="kpiModalValue" class="fs-4 fw-bold">—</div>
            </div>
            <div class="col-6">
              <div class="text-muted">Change</div>
              <div id="kpiModalChange" class="fs-5">—</div>
            </div>
          </div>
          <div id="kpiModalTableWrap" class="table-responsive mb-2 d-none">
            <table class="table table-sm" id="kpiModalTable">
              <thead><tr><th>Label</th><th>Value</th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="border rounded p-2">
            <canvas id="kpiModalChart" height="130"></canvas>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex w-100 justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <select id="kpiExportFormat" class="form-select form-select-sm" style="width: auto;">
            <option value="csv" selected>CSV</option>
            <option value="print">PDF/Print</option>
          </select>
          <button type="button" id="kpiExportBtn" class="btn btn-outline-primary btn-sm">Export</button>
        </div>
        <a href="{{ route('admin.financial_report.index') }}" class="btn btn-primary btn-sm">View Full Report</a>
      </div>
    </div>
  </div>
 </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const fmtPeso = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
  const qs = (name) => document.getElementById(name);
  const ctx = (id) => qs(id).getContext('2d');

  let charts = {};
  const defaultLastMonths = 6;
  let rollingRangeActive = true; // default view uses last N months

  function params() {
    const y = qs('year').value;
    const m = qs('month').value;
    const search = new URLSearchParams();
    if (y) search.set('year', y);
    if (m) search.set('month', m);
    const s = search.toString();
    return s ? ('?' + s) : '';
  }

  function querySuffix(){
    const m = qs('month').value;
    if (rollingRangeActive && !m) {
      return '?' + new URLSearchParams({ last_months: String(defaultLastMonths) }).toString();
    }
    return params();
  }

  async function fetchJson(url){
    const res = await fetch(url + querySuffix());
    return await res.json();
  }

  async function loadKpis(){
    const data = await fetchJson("{{ route('admin.financial_report.data.kpis') }}");
    qs('kpi-revenue').textContent = fmtPeso.format(Number(data.revenue||0));
    qs('kpi-expenses').textContent = fmtPeso.format(Number(data.expenses||0));
    qs('kpi-payroll').textContent = fmtPeso.format(Number(data.payroll||0));
    qs('kpi-net').textContent = fmtPeso.format(Number(data.net_profit||0));
    qs('kpi-margin').textContent = (Number(data.profit_margin||0)).toFixed(2) + '%';
  }

  function tooltipPeso(){
    return {
      callbacks: {
        label: function(ctx){
          const v = Array.isArray(ctx.raw) ? ctx.raw[ctx.dataIndex] : ctx.raw;
          return (ctx.dataset.label ? ctx.dataset.label + ': ' : '') + fmtPeso.format(Number(v||0));
        }
      }
    };
  }

  function makeChart(id, type, data, options){
    if (charts[id]) charts[id].destroy();
    charts[id] = new Chart(ctx(id), { type, data, options });
  }
  function renderTable(rows){
    const wrap = document.getElementById('kpiModalTableWrap');
    const tbody = document.querySelector('#kpiModalTable tbody');
    tbody.innerHTML = '';
    if (!rows || !rows.length){ wrap.classList.add('d-none'); return; }
    rows.slice(0, 8).forEach(([l,v])=>{
      const tr = document.createElement('tr');
      const td1 = document.createElement('td'); td1.textContent = l;
      const td2 = document.createElement('td'); td2.textContent = v;
      tr.appendChild(td1); tr.appendChild(td2); tbody.appendChild(tr);
    });
    wrap.classList.remove('d-none');
  }

  async function loadRevenue(){
    const d = await fetchJson("{{ route('admin.financial_report.data.revenue_trend') }}");
    makeChart('revenueChart', 'line', {
      labels: d.labels,
      datasets: [{ label: 'Revenue', data: d.values, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.2)', fill: true }]
    }, { responsive: true, plugins: { tooltip: tooltipPeso(), legend: { display: true } } });
  }

  async function loadExpenses(){
    const d = await fetchJson("{{ route('admin.financial_report.data.expense_breakdown') }}");
    makeChart('expenseChart', 'pie', {
      labels: d.labels,
      datasets: [{ label: 'Expenses', data: d.values, backgroundColor: ['#f87171','#fb923c','#fbbf24','#34d399','#60a5fa','#a78bfa','#f472b6','#4ade80'] }]
    }, { responsive: true, plugins: { tooltip: tooltipPeso(), legend: { position: 'bottom' } } });
  }

  async function loadPayroll(){
    const d = await fetchJson("{{ route('admin.financial_report.data.payroll_summary') }}");
    makeChart('payrollChart', 'line', {
      labels: d.labels,
      datasets: [{ label: 'Payroll', data: d.values, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.2)', fill: true }]
    }, { responsive: true, plugins: { tooltip: tooltipPeso() } });
  }

  async function loadProfit(){
    const d = await fetchJson("{{ route('admin.financial_report.data.net_profit') }}");
    makeChart('profitChart', 'line', {
      labels: d.labels,
      datasets: [
        { label: 'Revenue', data: d.revenue, borderColor: '#2563eb', fill: false },
        { label: 'Expenses', data: d.expenses, borderColor: '#ef4444', fill: false },
        { label: 'Payroll', data: d.payroll, borderColor: '#059669', fill: false },
        { label: 'Profit', data: d.profit, borderColor: '#111827', fill: false }
      ]
    }, { responsive: true, plugins: { tooltip: tooltipPeso() } });
  }

  async function loadCashFlow(){
    const d = await fetchJson("{{ route('admin.financial_report.data.cash_flow') }}");
    makeChart('cashFlowChart', 'bar', {
      labels: d.labels,
      datasets: [
        { label: 'Inflows', data: d.inflows, backgroundColor: '#60a5fa' },
        { label: 'Outflows', data: d.outflows, backgroundColor: '#f87171' }
      ]
    }, { responsive: true, plugins: { tooltip: tooltipPeso() }, scales: { y: { beginAtZero: true } } });
  }

  async function loadTax(){
    const d = await fetchJson("{{ route('admin.financial_report.data.tax_summary') }}");
    makeChart('taxChart', 'bar', {
      labels: d.labels,
      datasets: [{ label: 'Tax', data: d.values, backgroundColor: '#a78bfa' }]
    }, { responsive: true, plugins: { tooltip: tooltipPeso() }, scales: { y: { beginAtZero: true } } });
  }

  

  async function refreshAll(){
    await loadKpis();
    await Promise.all([
      loadRevenue(),
      loadExpenses(),
      loadPayroll(),
      loadProfit(),
      loadCashFlow(),
      loadTax()
    ]);
  }

  // Modal interactions for KPI quick view
  const modalEl = document.getElementById('kpiModal');
  const modal = (()=>{ try { return new bootstrap.Modal(modalEl); } catch(e){ return null; } })();
  let modalChart;
  function setLoading(v){
    document.getElementById('kpiModalLoading').classList.toggle('d-none', !v);
    document.getElementById('kpiModalContent').classList.toggle('d-none', !!v);
  }
  function destroyModalChart(){ if (modalChart) { modalChart.destroy(); modalChart = null; } }
  function exportCsvFromSeries(filename, labels, series){
    const rows = [['Label','Value'], ...labels.map((l,i)=> [l, series[i] ?? ''])];
    const csv = rows.map(r=> r.map(x=> '"'+String(x).replaceAll('"','""')+'"').join(',')).join('\n');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = filename; a.click(); URL.revokeObjectURL(a.href);
  }
  function openKpiModal(kind, title, currentDisplay){
    document.getElementById('kpiModalTitle').textContent = title;
    document.getElementById('kpiModalValue').textContent = currentDisplay;
    document.getElementById('kpiModalChange').textContent = '—';
    setLoading(true);
    modal && modal.show();
    // choose endpoint + chart type
    const cfg = {
      revenue: { url: "{{ route('admin.financial_report.data.revenue_trend') }}", type: 'line', label: 'Revenue', fmt: (v)=>fmtPeso.format(v||0), table: (d)=> d.labels.map((l,i)=>[l, fmtPeso.format(Number(d.values?.[i]||0))]) },
      expenses: { url: "{{ route('admin.financial_report.data.expense_breakdown') }}", type: 'doughnut', label: 'Expenses', fmt: (v)=>fmtPeso.format(v||0), table: (d)=> (d.labels||[]).map((l,i)=>[l, fmtPeso.format(Number(d.values?.[i]||0))]) },
      payroll: { url: "{{ route('admin.financial_report.data.payroll_summary') }}", type: 'line', label: 'Payroll', fmt: (v)=>fmtPeso.format(v||0), table: (d)=> d.labels.map((l,i)=>[l, fmtPeso.format(Number(d.values?.[i]||0))]) },
      net:     { url: "{{ route('admin.financial_report.data.net_profit') }}", type: 'line', label: 'Profit', fmt: (v)=>fmtPeso.format(v||0), table: (d)=> d.labels.map((l,i)=>[l, fmtPeso.format(Number(d.profit?.[i]||0))]) },
      margin:  { url: "{{ route('admin.financial_report.data.net_profit') }}", type: 'line', label: 'Profit %', fmt: (v)=> (v??0).toFixed(2)+'%', table: (d)=> d.labels.map((l,i)=>[l, ((Number(d.profit?.[i]||0)) )]) },
      // Insight cards
      'fr.revenue_trend': { url: "{{ route('admin.financial_report.data.revenue_trend') }}", type: 'line', label: 'Revenue', table: (d)=> d.labels.map((l,i)=>[l, fmtPeso.format(Number(d.values?.[i]||0))]) },
      'fr.expense_breakdown': { url: "{{ route('admin.financial_report.data.expense_breakdown') }}", type: 'doughnut', label: 'Expenses', table: (d)=> (d.labels||[]).map((l,i)=>[l, fmtPeso.format(Number(d.values?.[i]||0))]) },
      'fr.payroll_summary': { url: "{{ route('admin.financial_report.data.payroll_summary') }}", type: 'line', label: 'Payroll', table: (d)=> d.labels.map((l,i)=>[l, fmtPeso.format(Number(d.values?.[i]||0))]) },
      'fr.net_profit': { url: "{{ route('admin.financial_report.data.net_profit') }}", type: 'line', label: 'Profit', table: (d)=> d.labels.map((l,i)=>[l, fmtPeso.format(Number(d.profit?.[i]||0))]), datasets: (d)=> [
          { label: 'Revenue', data: d.revenue, borderColor: '#2563eb', fill: false },
          { label: 'Expenses', data: d.expenses, borderColor: '#ef4444', fill: false },
          { label: 'Payroll', data: d.payroll, borderColor: '#059669', fill: false },
          { label: 'Profit', data: d.profit, borderColor: '#111827', fill: false }
        ] },
      'fr.cash_flow': { url: "{{ route('admin.financial_report.data.cash_flow') }}", type: 'bar', label: 'Cash Flow', table: (d)=> d.labels.map((l,i)=>[l, `${fmtPeso.format(Number(d.inflows?.[i]||0))} / ${fmtPeso.format(Number(d.outflows?.[i]||0))}`]), datasets: (d)=> [
          { label: 'Inflows', data: d.inflows, backgroundColor: '#60a5fa' },
          { label: 'Outflows', data: d.outflows, backgroundColor: '#f87171' }
        ] },
      'fr.tax_summary': { url: "{{ route('admin.financial_report.data.tax_summary') }}", type: 'bar', label: 'Tax', table: (d)=> d.labels.map((l,i)=>[l, fmtPeso.format(Number(d.values?.[i]||0))]) },
      'fr.payment_status': { url: "{{ route('admin.financial_report.data.payment_status') }}", type: 'doughnut', label: 'Count', table: (d)=> (d.labels||[]).map((l,i)=>[l, Number(d.values?.[i]||0)]) },
      'fr.supplier_spending': { url: "{{ route('admin.financial_report.data.supplier_spending') }}", type: 'bar', label: 'Spending', table: (d)=> (d.labels||[]).map((l,i)=>[l, fmtPeso.format(Number(d.values?.[i]||0))]) },
    }[kind];
    ;(async()=>{
      try{
        // Always fetch last 6 months for summary modal
        const url = cfg.url + (cfg.url.includes('?') ? '&' : '?') + 'last_months=6';
        const res = await fetch(url);
        const d = await res.json();
        let labels = d.labels || [];
        let values;
        if (kind === 'expenses') values = d.values || d.data || [];
        else if (kind === 'net' || kind === 'margin' || kind==='fr.net_profit') values = d.profit || [];
        else if (kind === 'fr.cash_flow') values = d.inflows || [];
        else values = d.values || d.data || [];
        // compute simple change vs previous point
        if (Array.isArray(values) && values.length > 1){
          const last = Number(values[values.length-1]||0);
          const prev = Number(values[values.length-2]||0);
          const delta = last - prev;
          const pct = prev !== 0 ? (delta/prev)*100 : 0;
          document.getElementById('kpiModalChange').textContent = (delta>=0?'+':'') + fmtPeso.format(delta) + ` (${pct.toFixed(1)}%)`;
        }
        renderTable(cfg.table ? cfg.table(d) : []);
        destroyModalChart();
        const ctx2 = document.getElementById('kpiModalChart');
        const datasets = cfg.datasets ? cfg.datasets(d) : [{ label: cfg.label, data: values, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.2)', fill: cfg.type==='line' }];
        const options = { responsive: true, maintainAspectRatio: false };
        if (kind === 'fr.supplier_spending') options.indexAxis = 'y';
        if (kind === 'fr.cash_flow') options.scales = { y: { beginAtZero: true } };
        modalChart = new Chart(ctx2, { type: cfg.type, data: { labels, datasets }, options });
        // attach export handler
        const exportBtn = document.getElementById('kpiExportBtn');
        exportBtn.onclick = ()=>{
          const fmt = document.getElementById('kpiExportFormat').value;
          if (fmt === 'csv') exportCsvFromSeries(`${kind}_summary.csv`, labels, Array.isArray(values)?values:[]);
          else window.print();
        };
      } catch(e){ console.error(e); }
      finally{ setLoading(false); }
    })();
  }

  // Delegate clicks on cards
  document.querySelectorAll('.report-card').forEach(card=>{
    card.addEventListener('click', ()=>{
      const kind = card.getAttribute('data-report');
      const title = card.querySelector('.text-muted')?.textContent?.trim() || 'Summary';
      const value = card.querySelector('.fw-bold')?.textContent?.trim() || '—';
      openKpiModal(kind, title, value);
    });
    card.addEventListener('keydown', (e)=>{ if (e.key==='Enter' || e.key===' ') { e.preventDefault(); card.click(); } });
  });

  // Keyboard shortcut E to export when modal is open
  document.addEventListener('keydown', (e)=>{
    if (e.key?.toLowerCase() === 'e' && modalEl.classList.contains('show')){
      e.preventDefault();
      document.getElementById('kpiExportBtn').click();
    }
  });

  function chartTitleById(id){
    return document.getElementById(id)?.closest('.card')?.querySelector('.card-header')?.textContent?.trim() || 'Summary';
  }
  function addCanvasTrigger(canvasId, kind, getDisplay){
    const el = document.getElementById(canvasId);
    if (!el) return;
    el.style.cursor = 'pointer';
    el.addEventListener('click', ()=>{
      const title = chartTitleById(canvasId);
      let display = '—';
      try { display = getDisplay() || '—'; } catch(e){}
      openKpiModal(kind, title, display);
    });
  }
  function wireChartModal(){
    addCanvasTrigger('revenueChart','revenue', ()=>{
      const arr = charts['revenueChart']?.data?.datasets?.[0]?.data || []; const v = Number(arr[arr.length-1]||0); return fmtPeso.format(v);
    });
    addCanvasTrigger('expenseChart','expenses', ()=>{
      const labels = charts['expenseChart']?.data?.labels || []; const arr = charts['expenseChart']?.data?.datasets?.[0]?.data || [];
      let i = arr.indexOf(Math.max(...(arr.length?arr:[0]))); if (i<0) i=0; return (labels[i]||'—')+': '+fmtPeso.format(Number(arr[i]||0));
    });
    addCanvasTrigger('payrollChart','payroll', ()=>{
      const arr = charts['payrollChart']?.data?.datasets?.[0]?.data || []; const v = Number(arr[arr.length-1]||0); return fmtPeso.format(v);
    });
    addCanvasTrigger('profitChart','fr.net_profit', ()=>{
      const ds = (charts['profitChart']?.data?.datasets || []).find(d=> String(d.label).toLowerCase()==='profit');
      const arr = ds?.data || []; const v = Number(arr[arr.length-1]||0); return fmtPeso.format(v);
    });
    addCanvasTrigger('cashFlowChart','fr.cash_flow', ()=>{
      const inflows = (charts['cashFlowChart']?.data?.datasets?.[0]?.data)||[]; const outflows=(charts['cashFlowChart']?.data?.datasets?.[1]?.data)||[];
      const i = Math.min(inflows.length, outflows.length) - 1; const net = Number((inflows[i]||0)) - Number((outflows[i]||0)); return `Net ${fmtPeso.format(net)}`;
    });
    addCanvasTrigger('taxChart','fr.tax_summary', ()=>{
      const arr = charts['taxChart']?.data?.datasets?.[0]?.data || []; const v = Number(arr[arr.length-1]||0); return fmtPeso.format(v);
    });
    
  }

  document.getElementById('filters').addEventListener('submit', function(e){
    e.preventDefault();
    // When user applies filters, switch to explicit year/month range
    rollingRangeActive = false;
    refreshAll();
  });

  document.getElementById('resetFilters').addEventListener('click', function(){
    qs('year').value = {{ now()->year }};
    qs('month').value = '';
    // Return to rolling last N months
    rollingRangeActive = true;
    refreshAll();
  });

  // initial
  wireChartModal();
  refreshAll();
})();
</script>
@endpush
